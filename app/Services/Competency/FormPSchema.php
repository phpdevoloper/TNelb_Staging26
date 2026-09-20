<?php

namespace App\Services\Competency;

/**
 * Form P (Power Generating Station O&M) table and identity contract.
 *
 * Education, experience, proofs, and payments stay on shared competency tables.
 * Institute/training rows stay on tnelb_applicant_institute.
 */
final class FormPSchema
{
    public const FORM_NAME = 'P';

    public const LICENSE_NAME = 'P';

    public const FORM_ID = 6;

    public const META_TABLE = 'cc_form_p_meta';

    public const WORKFLOW_TABLE = 'cc_workflow_formp';

    public const CERT_TABLE = 'cc_form_p_cert';

    public const DIGITIZATION_TABLE = 'tnelb_cc_digitization';

    /** Request attribute: persist already entered via FormPController. */
    public const VIA_CONTROLLER_ATTR = 'via_form_p_controller';

    /** @var list<string> */
    public const SHARED_TABLES = CompetencySchema::SHARED_TABLES;

    public static function isFormP(?string $formName): bool
    {
        return strtoupper(trim((string) $formName)) === self::FORM_NAME;
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
