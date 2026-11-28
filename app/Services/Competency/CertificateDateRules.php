<?php

namespace App\Services\Competency;

use App\Services\FormS\FormSApplicantRules;

/**
 * Previously issued certificate dates (Form S Q8 supervisor / Q9 wireman): Date of First Issue,
 * From date and To date. Mirrors fsCertDateMessage() in public/assets/js/custom.js.
 *
 *  - Date of First Issue: valid date, not before 1900-01-01, not after today.
 *  - From date: valid date, not before 1900-01-01, not before the Date of First Issue (when that is a
 *    valid date), not after today.
 *  - To date: valid date, after the From date (when that is a valid date), not after 2099-12-31.
 *    A To date in the past is allowed (an expired certificate is still a previous certificate).
 *
 * Empty values are skipped (required-ness is checked elsewhere). A question is only checked when its
 * Yes/No flag is "yes"; "no" or a missing flag (locked / legacy callers) is never rejected here.
 *
 * Pure (no framework calls): today is passed as Y-m-d so it can be unit-tested directly.
 */
final class CertificateDateRules
{
    public const MIN_DATE = '1900-01-01';

    public const MAX_TO_DATE = '2099-12-31';

    public const INVALID = 'Enter a valid date';

    public const ISSUE_BEFORE_MIN = 'Date of First Issue cannot be before 01-01-1900';

    public const ISSUE_FUTURE = 'Date of First Issue cannot be in the future';

    public const FROM_BEFORE_MIN = 'From date cannot be before 01-01-1900';

    public const FROM_BEFORE_ISSUE = 'From date cannot be before Date of First Issue';

    public const FROM_FUTURE = 'From date cannot be in the future';

    public const TO_NOT_AFTER_FROM = 'To date must be after From date';

    public const TO_AFTER_MAX = 'To date cannot be after 31-12-2099';

    /** Yes/No flag => [issue, from, to] request field names. */
    public const SUPERVISOR = [
        'flag' => 'previous_license',
        'issue' => 'previously_issue_date',
        'from' => 'previously_valid_from',
        'to' => 'previously_valid_to',
    ];

    public const WIREMAN = [
        'flag' => 'previous_certificate',
        'issue' => 'certificate_issue_date',
        'from' => 'certificate_valid_from',
        'to' => 'certificate_valid_to',
    ];

    /** Message for a Date of First Issue, or null when empty / fine. */
    public static function issueDateError(mixed $issue, string $todayYmd): ?string
    {
        if (self::isEmpty($issue)) {
            return null;
        }
        $ymd = self::toYmd($issue);
        if ($ymd === null) {
            return self::INVALID;
        }
        if ($ymd < self::MIN_DATE) {
            return self::ISSUE_BEFORE_MIN;
        }
        if ($ymd > $todayYmd) {
            return self::ISSUE_FUTURE;
        }

        return null;
    }

    /** Message for a From date, or null when empty / fine. $issue is only compared when it is a valid date. */
    public static function fromDateError(mixed $from, mixed $issue, string $todayYmd): ?string
    {
        if (self::isEmpty($from)) {
            return null;
        }
        $ymd = self::toYmd($from);
        if ($ymd === null) {
            return self::INVALID;
        }
        if ($ymd < self::MIN_DATE) {
            return self::FROM_BEFORE_MIN;
        }
        $issueYmd = self::toYmd($issue);
        if ($issueYmd !== null && $ymd < $issueYmd) {
            return self::FROM_BEFORE_ISSUE;
        }
        if ($ymd > $todayYmd) {
            return self::FROM_FUTURE;
        }

        return null;
    }

    /** Message for a To date, or null when empty / fine. $from is only compared when it is a valid date. */
    public static function toDateError(mixed $to, mixed $from): ?string
    {
        if (self::isEmpty($to)) {
            return null;
        }
        $ymd = self::toYmd($to);
        if ($ymd === null) {
            return self::INVALID;
        }
        $fromYmd = self::toYmd($from);
        if ($fromYmd !== null && $ymd <= $fromYmd) {
            return self::TO_NOT_AFTER_FROM;
        }
        if ($ymd > self::MAX_TO_DATE) {
            return self::TO_AFTER_MAX;
        }

        return null;
    }

    /**
     * Field => message for one question's three dates, regardless of its flag.
     *
     * @param  array<string, mixed>  $input
     * @param  array{flag:string, issue:string, from:string, to:string}  $fields  self::SUPERVISOR or self::WIREMAN
     * @return array<string, string>
     */
    public static function sectionErrors(array $input, array $fields, string $todayYmd): array
    {
        $issue = $input[$fields['issue']] ?? null;
        $from = $input[$fields['from']] ?? null;
        $to = $input[$fields['to']] ?? null;

        return array_filter([
            $fields['issue'] => self::issueDateError($issue, $todayYmd),
            $fields['from'] => self::fromDateError($from, $issue, $todayYmd),
            $fields['to'] => self::toDateError($to, $from),
        ], static fn ($message) => $message !== null);
    }

    /**
     * Field => message for every question in $sections whose flag is "yes".
     *
     * @param  array<string, mixed>  $input  request input (e.g. $request->all())
     * @param  list<array{flag:string, issue:string, from:string, to:string}>|null  $sections  default: Q8 + Q9
     * @return array<string, string>
     */
    public static function errors(array $input, string $todayYmd, ?array $sections = null): array
    {
        $errors = [];
        foreach ($sections ?? [self::SUPERVISOR, self::WIREMAN] as $fields) {
            if (! self::isYes($input[$fields['flag']] ?? null)) {
                continue;
            }
            $errors += self::sectionErrors($input, $fields, $todayYmd);
        }

        return $errors;
    }

    /** Y-m-d from "YYYY-MM-DD" (or the legacy "DD-MM-YYYY" / "DD/MM/YYYY"); null when not a real date. */
    public static function toYmd(mixed $value): ?string
    {
        return FormSApplicantRules::toYmd($value);
    }

    private static function isYes(mixed $flag): bool
    {
        return is_scalar($flag) && strtolower(trim((string) $flag)) === 'yes';
    }

    private static function isEmpty(mixed $value): bool
    {
        return $value === null || (is_scalar($value) && trim((string) $value) === '');
    }
}
