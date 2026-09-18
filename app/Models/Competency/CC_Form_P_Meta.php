<?php

namespace App\Models\Competency;

/** Form P application master (`cc_form_p_meta`). */
class CC_Form_P_Meta extends CC_CompetencyMeta
{
    protected $table = 'cc_form_p_meta';

    /** @var list<string> */
    protected $fillable = [
        'app_id',
        'login_id',
        'application_id',
        'applicant_name',
        'fathers_name',
        'applicant_email',
        'applicant_address',
        'd_o_b',
        'age',
        'form_name',
        'certificate_name',
        'form_id',
        'appl_type',
        'app_status',
        'payment_status',
        'processed_by',
        'certificate_no',
        'old_application',
        'previous_scc_no',
        'first_issue_date',
        'scc_from_date',
        'scc_to_date',
        'wcc_no',
        'wcc_issue_date',
        'wcc_from',
        'wcc_to',
        'qc',
        'qsc',
        'submitted_date',
        'updated_at',
        'created_at',
        'employer_detail',
    ];
}
