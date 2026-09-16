<?php

namespace App\Services\Competency;

/**
 * Form WH (Wireman Helper / Certificate H) table and identity contract.
 *
 * Do NOT add cc_form_wh_edu / cc_form_wh_exp. Education, experience, proofs,
 * and payments stay on the shared competency tables keyed by application_id.
 */
final class FormWHSchema
{
    public const FORM_NAME = 'WH';

    public const LICENSE_NAME = 'H';

    public const FORM_ID = 3;

    public const META_TABLE = 'cc_form_wh_meta';

    public const WORKFLOW_TABLE = 'cc_workflow_wh';

    public const CERT_TABLE = 'cc_form_wh_cert';

    public const DIGITIZATION_TABLE = 'tnelb_cc_digitization';

    /** Request attribute: persist already entered via FormWHController. */
    public const VIA_CONTROLLER_ATTR = 'via_form_wh_controller';

    /** @var list<string> */
    public const SHARED_TABLES = CompetencySchema::SHARED_TABLES;

    public static function canonical(?string $formName): string
    {
        $code = strtoupper(trim((string) $formName));

        return $code === 'H' ? self::FORM_NAME : $code;
    }

    public static function isFormWH(?string $formName): bool
    {
        return self::canonical($formName) === self::FORM_NAME;
    }

    /**
     * @return array<string, string|int>
     */
    public static function identityPayload(): array
    {
        return [
            'form_name' => self::FORM_NAME,
            'license_name' => self::LICENSE_NAME,
            'form_id' => self::FORM_ID,
        ];
    }
}
