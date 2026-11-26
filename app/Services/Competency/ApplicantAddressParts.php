<?php

namespace App\Services\Competency;

/**
 * Structured applicant address posted by user_login/partials/applicant-address-fields.blade.php
 * (address_line1, address_line2, city, state, district, pincode) next to the composed hidden
 * `applicants_address`, which stays the value old readers use.
 *
 * Parts are validated and saved only when at least one part was posted with a value. Callers that
 * post no parts (Form W / WH pages, locked returned-application sections, legacy pages) keep
 * working and their stored parts are left untouched.
 *
 * Pure (no framework calls) so it can be unit-tested directly.
 */
final class ApplicantAddressParts
{
    public const FIELDS = ['address_line1', 'address_line2', 'city', 'state', 'district', 'pincode'];

    /** District is a Tamil Nadu-only select in the partial. */
    public const DISTRICT_STATE = 'Tamil Nadu';

    /** Column sizes on cc_form_{s,w,wh,p}_meta. */
    private const MAX_LENGTH = [
        'address_line1' => 100,
        'address_line2' => 100,
        'city' => 50,
        'state' => 100,
        'district' => 100,
    ];

    private const LABELS = [
        'address_line1' => 'Address Line 1',
        'address_line2' => 'Address Line 2',
        'city' => 'Village / Town / City',
        'state' => 'State',
        'district' => 'District',
        'pincode' => 'Pin Code',
    ];

    /** @param array<string, mixed> $input */
    public static function isPresent(array $input): bool
    {
        foreach (self::FIELDS as $field) {
            if (self::value($input, $field) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Columns to write. Empty array when no part was posted (do not touch stored parts).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string|null>
     */
    public static function payload(array $input): array
    {
        if (! self::isPresent($input)) {
            return [];
        }

        $payload = [];
        foreach (self::FIELDS as $field) {
            $value = self::value($input, $field);
            $payload[$field] = $value === '' ? null : $value;
        }

        // District only belongs to a Tamil Nadu address (the partial disables it otherwise).
        if (($payload['state'] ?? null) !== self::DISTRICT_STATE) {
            $payload['district'] = null;
        }

        return $payload;
    }

    /**
     * Field => message. Format checks always run when parts are present; required checks only
     * when $requireComplete (final submit, not a draft save).
     *
     * @param  array<string, mixed>  $input
     * @return array<string, string>
     */
    public static function errors(array $input, bool $requireComplete = true): array
    {
        if (! self::isPresent($input)) {
            return [];
        }

        $errors = [];

        if ($requireComplete) {
            foreach (['address_line1', 'city', 'state', 'pincode'] as $field) {
                if (self::value($input, $field) === '') {
                    $errors[$field] = self::LABELS[$field].' is required.';
                }
            }
            if (self::value($input, 'state') === self::DISTRICT_STATE && self::value($input, 'district') === '') {
                $errors['district'] = 'District is required.';
            }
        }

        foreach (self::MAX_LENGTH as $field => $max) {
            if (isset($errors[$field])) {
                continue;
            }
            if (mb_strlen(self::value($input, $field)) > $max) {
                $errors[$field] = self::LABELS[$field].' may not be greater than '.$max.' characters.';
            }
        }

        $pincode = self::value($input, 'pincode');
        if (! isset($errors['pincode']) && $pincode !== '' && ! preg_match('/\A[0-9]{6}\z/', $pincode)) {
            $errors['pincode'] = 'Pin Code must be exactly 6 digits.';
        }

        return $errors;
    }

    /** @param array<string, mixed> $input */
    private static function value(array $input, string $field): string
    {
        $raw = $input[$field] ?? null;

        return is_scalar($raw) ? trim((string) $raw) : '';
    }
}
