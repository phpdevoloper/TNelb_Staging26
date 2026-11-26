<?php

namespace App\Support;

/**
 * Display-only names for forms on admin screens.
 *
 * Stored values (form_name = 'S', license_name = 'C') are NOT changed; code that
 * branches on them must keep using the raw values. Use this class only for text shown to staff.
 */
class FormDisplayName
{
    public const FORM_S_ADMIN_TITLE = 'Form C: Supervisor Competency certificate (C)';

    /** Form P (stored form_name / license_name 'P') is issued as Certificate D (ELB wording). */
    public const FORM_P_ADMIN_TITLE = 'Form P/Certificate D';

    /** Display letter of the certificate issued under Form P. */
    public const FORM_P_CERTIFICATE_LETTER = 'D';

    /**
     * Admin-facing title for an application.
     *
     * Form S (certificate code C) and Form P (Certificate D) get the ELB wording; every other
     * form returns $default unchanged.
     *
     * @param  string|null  $formName  Stored form code, e.g. 'S', 'W', 'WH', 'P', 'A'.
     * @param  string|null  $default  Title the view showed before (returned as-is for other forms).
     */
    public static function adminTitle(?string $formName, ?string $default = null): string
    {
        if (self::isFormS($formName)) {
            return self::FORM_S_ADMIN_TITLE;
        }

        if (self::isFormP($formName)) {
            return self::FORM_P_ADMIN_TITLE;
        }

        return (string) $default;
    }

    /**
     * Accepts the stored code ('S') as well as labels such as 'FORM S' / 'Form S'.
     */
    public static function isFormS(?string $formName): bool
    {
        return self::normalise($formName) === 'S';
    }

    /**
     * Accepts the stored code ('P') as well as labels such as 'FORM P' / 'Form P'.
     */
    public static function isFormP(?string $formName): bool
    {
        return self::normalise($formName) === 'P';
    }

    private static function normalise(?string $formName): string
    {
        $code = strtoupper(trim((string) $formName));

        return trim((string) preg_replace('/^FORM\s*[-:]?\s*/', '', $code));
    }
}
