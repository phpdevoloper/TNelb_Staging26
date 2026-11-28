<?php

namespace Tests\Unit;

use App\Services\FormS\FormSCertificateNumberRules as Rules;
use PHPUnit\Framework\TestCase;

class FormSCertificateNumberRulesTest extends TestCase
{
    private const SUP = 'previously_number';

    private const WIR = 'competency_certificate_no';

    /** @return array<string, mixed> */
    private function input(array $extra = []): array
    {
        return array_merge(['form_name' => 'S', 'previous_license' => 'yes', 'previous_certificate' => 'yes'], $extra);
    }

    public function test_message_text_is_exact(): void
    {
        $this->assertSame('Invalid certificate number', Rules::MESSAGE);
    }

    public function test_valid_numbers_pass(): void
    {
        // Both questions: 1 to 5 digits.
        foreach (['1', '0', '12', '123', '1234', '12345', '00000', '00001'] as $value) {
            $this->assertTrue(Rules::isValidSupervisorNumber($value), var_export($value, true));
            $this->assertTrue(Rules::isValidWiremanNumber($value), var_export($value, true));
        }
        $this->assertTrue(Rules::isValidSupervisorNumber(12345));
        $this->assertTrue(Rules::isValidSupervisorNumber(7));
        $this->assertTrue(Rules::isValidWiremanNumber(12345));
        $this->assertTrue(Rules::isValidWiremanNumber(7));

        $this->assertSame([], Rules::errors($this->input([self::SUP => '12345', self::WIR => '12345'])));
        $this->assertSame([], Rules::errors($this->input([self::SUP => '1', self::WIR => '9'])));
        $this->assertSame([], Rules::errors($this->input([self::SUP => '123', self::WIR => '4567'])));
        $this->assertSame([], Rules::errors($this->input([self::SUP => '00000', self::WIR => '00000'])));
        // Draft mode accepts the same values.
        $this->assertSame([], Rules::errors($this->input([self::SUP => '12', self::WIR => '345']), false));
    }

    /** @return list<string> */
    private function badValues(): array
    {
        return [
            '', 'C1234', 'C12345', 'W12345', 'W123456', 'c12345', 'w1', 'C1', '123456', '1234567', '000000',
            '١٢٣٤٥', '12345'."\n", '123 45', ' 12345', '12345 ', '12.34', '-1234', '+1234', '1e34', 'abcde', '12a',
        ];
    }

    public function test_invalid_supervisor_numbers_fail(): void
    {
        foreach ($this->badValues() as $value) {
            $this->assertFalse(Rules::isValidSupervisorNumber($value), var_export($value, true));
        }
        $this->assertFalse(Rules::isValidSupervisorNumber(123456));
        $this->assertFalse(Rules::isValidSupervisorNumber(null));
        $this->assertFalse(Rules::isValidSupervisorNumber(['12345']));
    }

    public function test_invalid_wireman_numbers_fail(): void
    {
        foreach ($this->badValues() as $value) {
            $this->assertFalse(Rules::isValidWiremanNumber($value), var_export($value, true));
        }
        // Q9 was exactly 6 digits before; now max 5.
        $this->assertFalse(Rules::isValidWiremanNumber('123456'));
        $this->assertFalse(Rules::isValidWiremanNumber(123456));
        $this->assertFalse(Rules::isValidWiremanNumber(null));
        $this->assertFalse(Rules::isValidWiremanNumber(['12345']));
    }

    public function test_errors_reports_invalid_values_with_exact_message(): void
    {
        $bad = ['C1234', 'C12345', 'W12345', 'W123456', 'W1', '123456', '1234567', '١٢٣٤٥', '123 45', '12a'];
        foreach ($bad as $value) {
            $this->assertSame(
                [self::SUP => 'Invalid certificate number'],
                Rules::errors($this->input([self::SUP => $value, self::WIR => '12345'])),
                'sup '.var_export($value, true)
            );
            $this->assertSame(
                [self::WIR => 'Invalid certificate number'],
                Rules::errors($this->input([self::SUP => '12345', self::WIR => $value])),
                'wir '.var_export($value, true)
            );
            // Draft (format-only) mode still rejects a wrong value.
            $this->assertSame(
                [self::SUP => 'Invalid certificate number', self::WIR => 'Invalid certificate number'],
                Rules::errors($this->input([self::SUP => $value, self::WIR => $value]), false),
                'draft '.var_export($value, true)
            );
        }

        $this->assertSame(
            [self::SUP => 'Invalid certificate number', self::WIR => 'Invalid certificate number'],
            Rules::errors($this->input([self::SUP => 'C1234', self::WIR => 'W12345']))
        );
    }

    public function test_errors_trims_surrounding_whitespace_before_matching(): void
    {
        // errors() trims the raw value (as Laravel's TrimStrings middleware would), so a trailing
        // newline / outer spaces are accepted there; inner spaces are still rejected.
        $this->assertSame([], Rules::errors($this->input([self::SUP => "12345\n", self::WIR => ' 123 '])));
        $this->assertSame([self::SUP => Rules::MESSAGE], Rules::errors($this->input([self::SUP => "123\n45", self::WIR => '12345'])));
        // Trimming does not let a 6-digit value through.
        $this->assertSame([self::WIR => Rules::MESSAGE], Rules::errors($this->input([self::SUP => '1', self::WIR => ' 123456 '])));
    }

    public function test_non_scalar_value_after_yes_is_treated_as_empty(): void
    {
        $this->assertSame([self::SUP => Rules::MESSAGE], Rules::errors($this->input([self::SUP => ['12345'], self::WIR => '12345'])));
        $this->assertSame([], Rules::errors($this->input([self::SUP => ['12345'], self::WIR => '12345']), false));
    }

    public function test_empty_number_after_yes_required_only_on_final_submit(): void
    {
        foreach (['', '   ', null] as $empty) {
            $this->assertSame(
                [self::SUP => Rules::MESSAGE, self::WIR => Rules::MESSAGE],
                Rules::errors($this->input([self::SUP => $empty, self::WIR => $empty])),
                var_export($empty, true)
            );
            $this->assertSame([], Rules::errors($this->input([self::SUP => $empty, self::WIR => $empty]), false));
        }
        // Field missing entirely.
        $this->assertSame([self::SUP => Rules::MESSAGE, self::WIR => Rules::MESSAGE], Rules::errors($this->input()));
        $this->assertSame([], Rules::errors($this->input(), false));
    }

    public function test_no_check_when_flag_is_no_or_missing(): void
    {
        $values = [self::SUP => 'C1234', self::WIR => 'W12345'];
        foreach (['no', 'No', 'NO', '', 'y', 'true', '1'] as $flag) {
            $this->assertSame([], Rules::errors(array_merge($values, [
                'form_name' => 'S', 'previous_license' => $flag, 'previous_certificate' => $flag,
            ])), "flag {$flag}");
        }
        // Flags not posted (locked/disabled radios, legacy readonly "C1234").
        $this->assertSame([], Rules::errors(array_merge($values, ['form_name' => 'S'])));
        // Non-scalar flag.
        $this->assertSame([], Rules::errors(array_merge($values, ['form_name' => 'S', 'previous_license' => ['yes']])));
    }

    public function test_only_the_flagged_question_is_checked(): void
    {
        $this->assertSame([self::SUP => Rules::MESSAGE], Rules::errors([
            'form_name' => 'S', 'previous_license' => 'yes', 'previous_certificate' => 'no',
            self::SUP => 'C1234', self::WIR => 'W12345',
        ]));
        $this->assertSame([self::WIR => Rules::MESSAGE], Rules::errors([
            'form_name' => 'S', 'previous_license' => 'no', 'previous_certificate' => 'yes',
            self::SUP => 'C1234', self::WIR => 'W12345',
        ]));
    }

    public function test_mixed_case_yes_flag_still_triggers_check(): void
    {
        foreach (['Yes', 'YES', 'yEs', ' yes '] as $flag) {
            $this->assertTrue(Rules::isYes($flag), $flag);
            $this->assertSame(
                [self::SUP => Rules::MESSAGE, self::WIR => Rules::MESSAGE],
                Rules::errors(['form_name' => 'S', 'previous_license' => $flag, 'previous_certificate' => $flag, self::SUP => 'C1234', self::WIR => 'W12345']),
                $flag
            );
        }
        $this->assertFalse(Rules::isYes(null));
        $this->assertFalse(Rules::isYes(['yes']));
    }

    public function test_only_form_s_is_checked(): void
    {
        $this->assertTrue(Rules::appliesTo('S'));
        $this->assertTrue(Rules::appliesTo(' s '));
        foreach (['P', 'W', 'WH', 'A', '', null, 'SW'] as $form) {
            $this->assertFalse(Rules::appliesTo($form), var_export($form, true));
            $this->assertSame([], Rules::errors($this->input(['form_name' => $form, self::SUP => 'C1234', self::WIR => 'W12345'])), var_export($form, true));
        }
        // form_name missing.
        $this->assertSame([], Rules::errors(['previous_license' => 'yes', self::SUP => 'C1234']));
    }
}
