<?php

namespace App\Services\FormS;

/**
 * Form S questions 8 and 9: previously issued certificate numbers.
 *
 *  - Q8 supervisor certificate (`previously_number`, flag `previous_license`): 1 to 5 digits.
 *  - Q9 wireman certificate (`competency_certificate_no`, flag `previous_certificate`): 1 to 5 digits.
 *
 * Digits only. A "C" / "W" (or any other) prefix is rejected, never stripped.
 * The check runs only when the applicant chose "Yes" for that question; "No" clears the number on save.
 * When the flag is not posted at all (locked/disabled radios, legacy callers) nothing is checked,
 * so readonly legacy values such as "C1234" on alteration pages keep working.
 * Only Form S is checked: Form P reuses `previously_number` for a different question.
 *
 * Pure (no framework calls) so it can be unit-tested directly.
 */
final class FormSCertificateNumberRules
{
    public const MESSAGE = 'Invalid certificate number';

    public const SUPERVISOR_FIELD = 'previously_number';

    public const SUPERVISOR_FLAG = 'previous_license';

    // \A...\z (not ^...$): "$" would also accept a trailing newline.
    public const SUPERVISOR_PATTERN = '/\A[0-9]{1,5}\z/';

    public const WIREMAN_FIELD = 'competency_certificate_no';

    public const WIREMAN_FLAG = 'previous_certificate';

    public const WIREMAN_PATTERN = '/\A[0-9]{1,5}\z/';

    public static function appliesTo(mixed $formName): bool
    {
        return strtoupper(trim((string) $formName)) === 'S';
    }

    public static function isYes(mixed $flag): bool
    {
        return is_scalar($flag) && strtolower(trim((string) $flag)) === 'yes';
    }

    public static function isValidSupervisorNumber(mixed $value): bool
    {
        return self::matches(self::SUPERVISOR_PATTERN, $value);
    }

    public static function isValidWiremanNumber(mixed $value): bool
    {
        return self::matches(self::WIREMAN_PATTERN, $value);
    }

    /**
     * Field => message for every invalid certificate number in the posted input.
     *
     * @param  array<string, mixed>  $input  request input (e.g. $request->all())
     * @param  bool  $requireWhenYes  false for draft saves: an empty number is allowed, a wrong one is not
     * @return array<string, string>
     */
    public static function errors(array $input, bool $requireWhenYes = true): array
    {
        if (! self::appliesTo($input['form_name'] ?? null)) {
            return [];
        }

        $errors = [];
        $checks = [
            [self::SUPERVISOR_FLAG, self::SUPERVISOR_FIELD, self::SUPERVISOR_PATTERN],
            [self::WIREMAN_FLAG, self::WIREMAN_FIELD, self::WIREMAN_PATTERN],
        ];

        foreach ($checks as [$flag, $field, $pattern]) {
            if (! self::isYes($input[$flag] ?? null)) {
                continue;
            }

            $raw = $input[$field] ?? null;
            $value = is_scalar($raw) ? trim((string) $raw) : '';
            if ($value === '') {
                if ($requireWhenYes) {
                    $errors[$field] = self::MESSAGE;
                }

                continue;
            }

            if (! self::matches($pattern, $value)) {
                $errors[$field] = self::MESSAGE;
            }
        }

        return $errors;
    }

    private static function matches(string $pattern, mixed $value): bool
    {
        if (! is_scalar($value)) {
            return false;
        }

        // No trim here: callers pass the trimmed value; a raw " 12345" is still invalid input.
        return (bool) preg_match($pattern, (string) $value);
    }
}
