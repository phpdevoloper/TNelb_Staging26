<?php

namespace App\Services\Competency;

/**
 * Competency certificate table layout.
 *
 * Per-form (S / W / WH / P):
 * - Application meta: cc_form_s_meta, cc_form_w_meta, cc_form_wh_meta, cc_form_p_meta
 * - Workflow: cc_workflow_forms, cc_workflow_formw, cc_workflow_formwh, cc_workflow_formp
 * - Issued certificate: cc_form_s_cert, cc_form_w_cert, cc_form_wh_cert, cc_form_p_cert
 *
 * Shared across ALL competency forms (keyed by application_id string):
 * - cc_edu, cc_exp, cc_proof_doc, cc_doc_log, cc_payments
 *
 * Do NOT create per-form copies of edu / exp / proof_doc tables.
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
        'WH' => 'cc_workflow_formwh',
        'P' => 'cc_workflow_formp',
    ];

    /** @var array<string, string> form_name => issued certificate table */
    public const CERT_TABLES = [
        'S' => 'cc_form_s_cert',
        'W' => 'cc_form_w_cert',
        'WH' => 'cc_form_wh_cert',
        'P' => 'cc_form_p_cert',
    ];

    public static function isSharedTable(string $table): bool
    {
        return in_array(strtolower(trim($table)), self::SHARED_TABLES, true);
    }
}
