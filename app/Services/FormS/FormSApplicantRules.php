<?php

namespace App\Services\FormS;

/**
 * Form S (New + Digitisation) applicant rules that must not trust the browser.
 *
 *  - Age is computed from the date of birth (never from the posted `age`): 18 or above, DOB not before 1935.
 *  - Q8 / Q9 certificate "Date of First Issue" and "From date" cannot be in the future. The "To date"
 *    (Date of Expiry / validity) is NOT restricted: a valid certificate expires in the future.
 *  - Father's name is title-cased per word with spaces collapsed (same casing as custom.js toNameCase).
 *
 * Pure (no framework calls): dates are passed as Y-m-d strings so it can be unit-tested directly.
 */
final class FormSApplicantRules
{
    public const MIN_DOB = '1935-01-01';

    public const MIN_AGE = 18;

    public const AGE_MESSAGE = 'Minimum age should be 18 years and above.';

    /** Q8 / Q9 date fields that cannot be in the future, grouped by the Yes/No flag that shows them. */
    public const NO_FUTURE_DATE_FIELDS = [
        FormSCertificateNumberRules::SUPERVISOR_FLAG => [
            'previously_issue_date' => 'Date of First Issue cannot be a future date.',
            'previously_valid_from' => 'From date cannot be a future date.',
        ],
        FormSCertificateNumberRules::WIREMAN_FLAG => [
            'certificate_issue_date' => 'Date of First Issue cannot be a future date.',
            'certificate_valid_from' => 'From date cannot be a future date.',
        ],
    ];

    /** Completed years between $dobYmd and $onYmd (both Y-m-d), or null when either is not a valid date. */
    public static function ageOn(?string $dobYmd, string $onYmd): ?int
    {
        $dob = self::parts($dobYmd);
        $on = self::parts($onYmd);
        if ($dob === null || $on === null) {
            return null;
        }

        $age = $on[0] - $dob[0];
        if ($on[1] < $dob[1] || ($on[1] === $dob[1] && $on[2] < $dob[2])) {
            $age--;
        }

        return $age;
    }

    /** AGE_MESSAGE when the DOB gives an age below MIN_AGE on $todayYmd (a future DOB included), else null. */
    public static function dobAgeError(?string $dobYmd, string $todayYmd): ?string
    {
        $age = self::ageOn($dobYmd, $todayYmd);

        return ($age !== null && $age < self::MIN_AGE) ? self::AGE_MESSAGE : null;
    }

    /**
     * Field => message for Q8 / Q9 issue / from dates after $todayYmd.
     * A question answered "No" (flag posted and not "yes") is skipped: its fields are hidden and cleared.
     *
     * @param  array<string, mixed>  $input  request input
     * @return array<string, string>
     */
    public static function futureDateErrors(array $input, string $todayYmd): array
    {
        if (! FormSCertificateNumberRules::appliesTo($input['form_name'] ?? null)) {
            return [];
        }

        $errors = [];
        foreach (self::NO_FUTURE_DATE_FIELDS as $flag => $fields) {
            if (array_key_exists($flag, $input) && ! FormSCertificateNumberRules::isYes($input[$flag])) {
                continue;
            }

            foreach ($fields as $field => $message) {
                $ymd = self::toYmd($input[$field] ?? null);
                if ($ymd !== null && $ymd > $todayYmd) {
                    $errors[$field] = $message;
                }
            }
        }

        return $errors;
    }

    /** "  aRUN   srinivasakannan g " => "Arun Srinivasakannan G"; null / blank stays as given. */
    public static function fathersName(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $collapsed = trim((string) preg_replace('/\s+/u', ' ', $value));
        if ($collapsed === '') {
            return $collapsed;
        }

        $words = explode(' ', mb_strtolower($collapsed, 'UTF-8'));
        foreach ($words as $i => $word) {
            $words[$i] = mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8').mb_substr($word, 1, null, 'UTF-8');
        }

        return implode(' ', $words);
    }

    /** Y-m-d from "YYYY-MM-DD" or "DD-MM-YYYY" / "DD/MM/YYYY"; null for anything else or an impossible date. */
    public static function toYmd(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $s = trim((string) $value);
        if (preg_match('/\A(\d{4})-(\d{2})-(\d{2})\z/', $s, $m)) {
            [$y, $mo, $d] = [(int) $m[1], (int) $m[2], (int) $m[3]];
        } elseif (preg_match('/\A(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})\z/', $s, $m)) {
            [$y, $mo, $d] = [(int) $m[3], (int) $m[2], (int) $m[1]];
        } else {
            return null;
        }

        return checkdate($mo, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $mo, $d) : null;
    }

    /** @return array{0:int,1:int,2:int}|null */
    private static function parts(?string $ymd): ?array
    {
        $ymd = self::toYmd($ymd);
        if ($ymd === null) {
            return null;
        }

        return [(int) substr($ymd, 0, 4), (int) substr($ymd, 5, 2), (int) substr($ymd, 8, 2)];
    }
}
