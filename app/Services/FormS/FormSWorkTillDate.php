<?php

namespace App\Services\FormS;

/**
 * Posted work_to_till_date[]: "1" when Till date is checked, otherwise "0".
 *
 * Previous: Y-m-d was posted when checked (legacy "1" was also accepted).
 * isChecked() still accepts both "1" and a Y-m-d value.
 */
final class FormSWorkTillDate
{
    public static function isChecked(mixed $value): bool
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '0') {
            return false;
        }

        return $value === '1' || (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value);
    }

    public static function toDateString(mixed $value, ?string $today = null): ?string
    {
        if (! self::isChecked($value)) {
            return null;
        }

        $value = trim((string) $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        return $today ?? now()->toDateString();
    }
}
