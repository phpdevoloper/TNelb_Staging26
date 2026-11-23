<?php

namespace App\Services\Competency;

/**
 * Competency certificate table layout.
 *
 * Per-form (S / W / WH / P):
 * - Application meta: cc_form_s_meta, cc_form_w_meta, cc_form_wh_meta, cc_form_p_meta
 * - Workflow: cc_workflow_forms, cc_workflow_formw, cc_workflow_wh, cc_workflow_formp
 * - Issued certificate: cc_form_s_cert, cc_form_w_cert, cc_form_wh_cert, cc_form_p_cert
 *
 * Shared across ALL competency forms (keyed by application_id string):
 * - cc_edu, cc_exp, cc_proof_doc, cc_doc_log, cc_payments
 *
 * Do NOT create per-form copies of edu / exp / proof_doc tables.
 *
 * Form W identity and table contract: see FormWSchema
 * (cc_form_w_meta, cc_workflow_formw, cc_form_w_cert + shared tables + tnelb_cc_digitization).
 */
final class CompetencySchema
{
    /** @var list<string> Shared tables for every competency form (S, W, WH, P). */
    public const SHARED_TABLES = [
        'cc_edu',
        'cc_exp',
        'cc_proof_doc',
        'cc_doc_log',
        'cc_payments',
    ];

    /** @var array<string, string> form_name => meta table */
    public const META_TABLES = CompetencyMetaService::FORM_META_TABLES;

    /** @var array<string, string> form_name => workflow table */
    public const WORKFLOW_TABLES = [
        'S' => 'cc_workflow_forms',
        'W' => 'cc_workflow_formw',
        'WH' => 'cc_workflow_wh',
        'P' => 'cc_workflow_formp',
    ];

    /** @var array<string, string> form_name => issued certificate table */
    public const CERT_TABLES = [
        'S' => 'cc_form_s_cert',
        'W' => 'cc_form_w_cert',
        'WH' => 'cc_form_wh_cert',
        'P' => 'cc_form_p_cert',
    ];

    /** @var list<string> Competency forms whose work experience is optional (S requires it). */
    public const WORK_EXPERIENCE_OPTIONAL_FORMS = ['W', 'WH', 'P'];

    /**
     * Single source of truth for "is work experience optional for this form?".
     * Used by every FormController save path (store, draft_update, ...) so the lists cannot drift.
     */
    public static function isWorkExperienceOptional(mixed $formName): bool
    {
        return is_string($formName)
            && in_array(strtoupper(trim($formName)), self::WORK_EXPERIENCE_OPTIONAL_FORMS, true);
    }

    public static function isSharedTable(string $table): bool
    {
        return in_array(strtolower(trim($table)), self::SHARED_TABLES, true);
    }
}
