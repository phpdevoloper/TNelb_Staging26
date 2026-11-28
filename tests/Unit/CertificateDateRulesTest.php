<?php

namespace Tests\Unit;

use App\Services\Competency\CertificateDateRules as Rules;
use PHPUnit\Framework\TestCase;

class CertificateDateRulesTest extends TestCase
{
    private const TODAY = '2026-06-15';

    /** @return array<string, mixed> */
    private function sup(array $extra = []): array
    {
        return array_merge([
            'previous_license' => 'yes',
            'previously_issue_date' => '2010-01-01',
            'previously_valid_from' => '2010-01-01',
            'previously_valid_to' => '2015-01-01',
        ], $extra);
    }

    /** @return array<string, mixed> */
    private function wir(array $extra = []): array
    {
        return array_merge([
            'previous_certificate' => 'yes',
            'certificate_issue_date' => '2010-01-01',
            'certificate_valid_from' => '2010-01-01',
            'certificate_valid_to' => '2015-01-01',
        ], $extra);
    }

    public function test_valid_dates_pass_for_both_sections(): void
    {
        $this->assertSame([], Rules::errors($this->sup() + $this->wir(), self::TODAY));
    }

    public function test_flag_no_missing_null_is_never_rejected(): void
    {
        $bad = ['previously_issue_date' => '1800-01-01', 'previously_valid_from' => '2999-01-01', 'previously_valid_to' => '2000-01-01'];
        foreach (['no', 'NO', '', null] as $flag) {
            $this->assertSame([], Rules::errors(array_merge($bad, ['previous_license' => $flag]), self::TODAY));
        }
        $this->assertSame([], Rules::errors($bad, self::TODAY));
    }

    public function test_flag_yes_is_case_insensitive(): void
    {
        $bad = ['previously_issue_date' => '1800-01-01'];
        foreach (['yes', 'YES', 'Yes', ' yes '] as $flag) {
            $errors = Rules::errors($this->sup($bad + ['previous_license' => $flag]), self::TODAY);
            $this->assertSame(Rules::ISSUE_BEFORE_MIN, $errors['previously_issue_date'] ?? null, var_export($flag, true));
        }
    }

    public function test_issue_date_rules(): void
    {
        $this->assertSame(Rules::ISSUE_BEFORE_MIN, Rules::issueDateError('1899-12-31', self::TODAY));
        $this->assertNull(Rules::issueDateError('1900-01-01', self::TODAY));
        $this->assertSame(Rules::ISSUE_FUTURE, Rules::issueDateError('2026-06-16', self::TODAY));
        $this->assertNull(Rules::issueDateError(self::TODAY, self::TODAY));
    }

    public function test_from_date_rules(): void
    {
        $this->assertSame(Rules::FROM_BEFORE_ISSUE, Rules::fromDateError('2009-12-31', '2010-01-01', self::TODAY));
        $this->assertNull(Rules::fromDateError('2010-01-01', '2010-01-01', self::TODAY));
        $this->assertSame(Rules::FROM_FUTURE, Rules::fromDateError('2026-06-16', '2010-01-01', self::TODAY));
        $this->assertNull(Rules::fromDateError(self::TODAY, '2010-01-01', self::TODAY));
        $this->assertSame(Rules::FROM_BEFORE_MIN, Rules::fromDateError('1899-12-31', '', self::TODAY));
        $this->assertSame(Rules::FROM_BEFORE_MIN, Rules::fromDateError('1899-12-31', null, self::TODAY));
    }

    public function test_from_ignores_invalid_or_empty_issue(): void
    {
        $this->assertNull(Rules::fromDateError('2010-01-01', 'garbage', self::TODAY));
        $this->assertNull(Rules::fromDateError('2010-01-01', '', self::TODAY));
    }

    public function test_to_date_rules(): void
    {
        $this->assertSame(Rules::TO_NOT_AFTER_FROM, Rules::toDateError('2010-01-01', '2010-01-01'));
        $this->assertSame(Rules::TO_NOT_AFTER_FROM, Rules::toDateError('2009-12-31', '2010-01-01'));
        $this->assertSame(Rules::TO_AFTER_MAX, Rules::toDateError('2100-01-01', '2010-01-01'));
        $this->assertNull(Rules::toDateError('2099-12-31', '2010-01-01'));
        $this->assertNull(Rules::toDateError('2015-01-01', '2010-01-01'));
        $this->assertNull(Rules::toDateError('2015-01-01', ''));
    }

    public function test_impossible_date_is_invalid(): void
    {
        $this->assertSame('Enter a valid date', Rules::issueDateError('2020-02-30', self::TODAY));
        $this->assertSame('Enter a valid date', Rules::fromDateError('2020-02-30', null, self::TODAY));
        $this->assertSame('Enter a valid date', Rules::toDateError('2020-02-30', null));
        $this->assertSame('Enter a valid date', Rules::issueDateError('not a date', self::TODAY));
    }

    public function test_legacy_formats_accepted(): void
    {
        $this->assertNull(Rules::issueDateError('15-03-2010', self::TODAY));
        $this->assertNull(Rules::issueDateError('15/03/2010', self::TODAY));
        $this->assertSame('2010-03-15', Rules::toYmd('15/03/2010'));
        $this->assertNull(Rules::fromDateError('16-03-2010', '15/03/2010', self::TODAY));
        $this->assertSame(Rules::FROM_BEFORE_ISSUE, Rules::fromDateError('14-03-2010', '15-03-2010', self::TODAY));
        $this->assertNull(Rules::toDateError('01/01/2015', '16-03-2010'));
    }

    public function test_empty_values_are_skipped(): void
    {
        foreach ([null, '', '   '] as $v) {
            $this->assertNull(Rules::issueDateError($v, self::TODAY));
            $this->assertNull(Rules::fromDateError($v, '2010-01-01', self::TODAY));
            $this->assertNull(Rules::toDateError($v, '2010-01-01'));
        }
        $this->assertSame([], Rules::errors(['previous_license' => 'yes', 'previous_certificate' => 'yes'], self::TODAY));
    }

    public function test_errors_keyed_by_field_for_supervisor(): void
    {
        $errors = Rules::errors($this->sup([
            'previously_issue_date' => '2030-01-01',
            'previously_valid_from' => '2029-01-01',
            'previously_valid_to' => '2028-01-01',
        ]), self::TODAY);

        // From (2029) is after issue (2030)? No: it is before, so the before-issue message wins.
        $this->assertSame([
            'previously_issue_date' => Rules::ISSUE_FUTURE,
            'previously_valid_from' => Rules::FROM_BEFORE_ISSUE,
            'previously_valid_to' => Rules::TO_NOT_AFTER_FROM,
        ], $errors);
    }

    public function test_errors_keyed_by_field_for_wireman(): void
    {
        $errors = Rules::errors($this->wir([
            'certificate_issue_date' => '1800-01-01',
            'certificate_valid_from' => '1799-01-01',
            'certificate_valid_to' => '2100-01-01',
        ]), self::TODAY);

        $this->assertSame([
            'certificate_issue_date' => Rules::ISSUE_BEFORE_MIN,
            'certificate_valid_from' => Rules::FROM_BEFORE_MIN,
            'certificate_valid_to' => Rules::TO_AFTER_MAX,
        ], $errors);
    }

    public function test_sections_are_independent(): void
    {
        $input = $this->sup() + $this->wir(['certificate_issue_date' => '2030-01-01']);
        $errors = Rules::errors($input, self::TODAY);
        $this->assertSame(['certificate_issue_date', 'certificate_valid_from'], array_keys($errors));

        $errors = Rules::errors($input, self::TODAY, [Rules::SUPERVISOR]);
        $this->assertSame([], $errors);
    }

    public function test_section_errors_ignores_flag(): void
    {
        $input = ['previous_license' => 'no', 'previously_issue_date' => '1800-01-01'];
        $this->assertSame(
            ['previously_issue_date' => Rules::ISSUE_BEFORE_MIN],
            Rules::sectionErrors($input, Rules::SUPERVISOR, self::TODAY)
        );
        $this->assertSame([], Rules::errors($input, self::TODAY));

        $this->assertSame(
            ['certificate_issue_date' => Rules::ISSUE_FUTURE],
            Rules::sectionErrors(['certificate_issue_date' => '2030-01-01'], Rules::WIREMAN, self::TODAY)
        );
    }
}
