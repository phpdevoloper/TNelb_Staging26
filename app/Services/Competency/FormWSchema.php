<?php

namespace App\Services\Competency;

/**
 * Frozen Form W (Wireman / Certificate B) table and identity contract.
 *
 * Do NOT add cc_form_w_edu / cc_form_w_exp. Education, experience, proofs,
 * and payments stay on the shared competency tables keyed by application_id.
 */
final class FormWSchema
{
    public const FORM_NAME = 'W';

    public const LICENSE_NAME = 'B';

    public const FORM_ID = 2;

    public const META_TABLE = 'cc_form_w_meta';

    public const WORKFLOW_TABLE = 'cc_workflow_formw';

    public const CERT_TABLE = 'cc_form_w_cert';

    public const DIGITIZATION_TABLE = 'tnelb_cc_digitization';

    /** Request attribute: persist already entered via FormWController. */
    public const VIA_CONTROLLER_ATTR = 'via_form_w_controller';

    /** @var list<string> */
    public const SHARED_TABLES = CompetencySchema::SHARED_TABLES;

    public static function isFormW(?string $formName): bool
    {
        return strtoupper(trim((string) $formName)) === self::FORM_NAME;
    }

    /**
     * @return array<string, string>
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
