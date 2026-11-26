<?php

namespace App\Services\Competency;

use App\Services\FormS\FormSApplicantRules;

/**
 * Form P (New + Digitisation) applicant input that must not trust the browser.
 *
 *  - `age` is recomputed from the date of birth (the posted, client-computed age is ignored);
 *  - `fathers_name` is title-cased per word with spaces collapsed (same casing as custom.js toNameCase).
 *
 * Renewal (R) and Alteration (A) keep the values exactly as recorded: nothing is rewritten for them.
 * The DOB limits themselves (not before 1935, 18 years and above) are the Form S ones
 * ({@see FormSApplicantRules::MIN_DOB}, {@see FormSApplicantRules::dobAgeError()}), applied in
 * FormController::dobMinYearRule for both S and P.
 *
 * Pure (no framework calls): dates are passed as Y-m-d strings so it can be unit-tested directly.
 */
final class FormPApplicantRules
{
    /** appl_type values whose applicant input is normalised. */
    public const NORMALISED_TYPES = ['N', 'D'];

    /** A posted appl_type that always wins over the stored one (never rewrite these). */
    public const PRESERVED_TYPES = ['R', 'A'];

    /**
     * True for New / Digitisation only.
     *
     * @param  mixed  $resolvedType  stored appl_type of the application, else the posted one
     * @param  mixed  $postedType  appl_type posted with this request (may be null)
     */
    public static function normalisesApplicant(mixed $resolvedType, mixed $postedType = null): bool
    {
        if (! in_array(self::type($resolvedType), self::NORMALISED_TYPES, true)) {
            return false;
        }

        // A renewal / alteration page posting against an N / D parent id is still a renewal / alteration.
        return ! in_array(self::type($postedType), self::PRESERVED_TYPES, true);
    }

    /**
     * Values to merge into the request (empty array = change nothing).
     *
     * @param  array<string, mixed>  $input  request input (fathers_name / appl_type read from here)
     * @param  mixed  $resolvedType  stored appl_type, else the posted one
     * @param  string|null  $dobYmd  date of birth already parsed to Y-m-d (null when missing / invalid)
     * @param  string  $todayYmd  Y-m-d
     * @return array<string, mixed>
     */
    public static function normalisedInput(array $input, mixed $resolvedType, ?string $dobYmd, string $todayYmd): array
    {
        if (! self::normalisesApplicant($resolvedType, $input['appl_type'] ?? null)) {
            return [];
        }

        $merge = [];
        // Only a name that was actually posted is touched (locked / partial sections do not post it).
        if (array_key_exists('fathers_name', $input)) {
            $merge['fathers_name'] = FormSApplicantRules::fathersName($input['fathers_name']);
        }

        $age = $dobYmd !== null ? FormSApplicantRules::ageOn($dobYmd, $todayYmd) : null;
        if ($age !== null && $age >= 0) {
            $merge['age'] = $age;
        }

        return $merge;
    }

    /**
     * DOB message for Form P, or null when the DOB is acceptable / not a date (the `date` rule reports that).
     * Same rules and wording as Form S.
     */
    public static function dobError(?string $dobYmd, string $todayYmd): ?string
    {
        $ymd = FormSApplicantRules::toYmd($dobYmd);
        if ($ymd === null) {
            return null;
        }
        if ($ymd < FormSApplicantRules::MIN_DOB) {
            return 'Year of birth cannot be earlier than 1935.';
        }

        return FormSApplicantRules::dobAgeError($ymd, $todayYmd);
    }

    private static function type(mixed $value): string
    {
        return is_scalar($value) ? strtoupper(trim((string) $value)) : '';
    }
}
