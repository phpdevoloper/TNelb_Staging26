<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\FeesValidity;
use App\Models\Admin\Mst_equipment_tbl;
use App\Models\Cl_Checklist_applicant;
use App\Models\MstLicence;
use App\Models\Tnelb_banksolvency_a;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Auth;


class generateLicencepdfController extends Controller
{


    public function application_preview($application_id)
    {
        $returnForwardUser = null;
        $nextForwardUser = null;

        $staff = Auth::user();

        if (!$staff || !$staff->roles_id) {
            abort(403, 'Unauthorized');
        }

        // =====================================================
        // APPLICANT DETAILS + LATEST PAYMENT
        // =====================================================
        $cl_digitization = DB::table('mapping_digi_cls')
            ->where('application_id', $application_id)
            ->first();
        $applicantQuery1 = DB::table('ccl_forma_meta')
            ->leftJoin('payments', function ($join) {
                $join->on(
                    'ccl_forma_meta.application_id',
                    '=',
                    'payments.application_id'
                )->whereRaw('payments.id = (
                SELECT MAX(p2.id)
                FROM payments p2
                WHERE p2.application_id = ccl_forma_meta.application_id
            )');
            })
            ->where('ccl_forma_meta.application_id', $application_id)
            ->select(
                'ccl_forma_meta.*',
                'payments.transaction_id',
                'payments.payment_status',
                'payments.amount',
                'payments.payment_mode',
                'payments.created_at as payment_date',
                'payments.application_fee',
                'payments.late_fee'
            );

        $applicant = $applicantQuery1
            ->orderByDesc('ccl_forma_meta.dt_submit')
            ->first();

        if (!$applicant) {
            abort(404, 'Applicant not found');
        }

        $appl_type = trim($applicant->appl_type ?? '');

        // =====================================================
        // OLD LICENCE DETAILS
        // =====================================================

        $old_issued_at_date = '0';

        if ($appl_type === 'R') {

            $old_issuedat = DB::table('cl_forma_lic')
                ->select(
                    'dateof_issue',
                    'created_at',
                    'valid_from',
                    'valid_to'
                )
                ->where('application_id', $applicant->old_application)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($old_issuedat) {
                $old_issued_at_date = $old_issuedat->valid_to;
            }
        }

        // =====================================================
        // LICENCE DETAILS
        // =====================================================

        $formname = $applicant->form_name;

        $license_name = DB::table('mst_licences')
            ->where('form_code', $formname)
            ->first();

        $license_name_data = DB::table('mst_licences')
            ->where('cert_licence_code', $applicant->license_name)
            ->first();

        // =====================================================
        // FORWARD / RETURN USER
        // =====================================================

        if (in_array($staff->name, ['Supervisor', 'Supervisor2'], true)) {

            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Assistant Secretary')
                ->select('name', 'roles_id')
                ->first();
        }

        if ($staff->name === 'Assistant Secretary') {

            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Secretary')
                ->select('name', 'roles_id')
                ->first();
        }

        if ($staff->name === 'Secretary') {

            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'President')
                ->select('name', 'roles_id')
                ->first();

            $returnForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->select('name', 'roles_id')
                ->first();
        }

        if ($staff->name === 'President') {

            $nextForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'President')
                ->select('name', 'roles_id')
                ->first();

            $returnForwardUser = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->select('name', 'roles_id')
                ->first();
        }

        // =====================================================
        // OWNERSHIP DETAILS
        // =====================================================

        $cl_ownership_table = DB::table('cl_ownership_table')
            ->where('application_id', $application_id)
            ->orderBy('id', 'desc')
            ->where('proprietor_flag', '1')
            ->get();

        // =====================================================
        // STAFF DETAILS
        // =====================================================

        $staffdetails = DB::table('cl_staff_tbl')
            ->where('application_id', $application_id)
            ->where('staff_flag', '1')
            ->orderBy('id')
            ->get();

        $otherstaffdetails = DB::table('cl_staff_tbl')
            ->where('application_id', $application_id)
            ->whereNotIn('staff_category', ['QC', 'QSC'])
            ->where('staff_flag', '1')
            ->orderBy('id')
            ->get();

        $authoritysignatory = DB::table('cl_forma_signs')
            ->where('application_id', $application_id)
            ->where('flag', 1)
            ->orderBy('row_index', 'asc')
            ->get();

        $qcQscStaffs_details = $staffdetails->filter(function ($staff) {
            return in_array(
                strtoupper(trim($staff->staff_category ?? '')),
                ['QC', 'QSC']
            );
        });

        // =====================================================
        // QC VALIDITY WARNING
        // =====================================================

        $showQcWarning = false;
        $licence_validitystaff = null;

        $licence_name_validitystaff = MstLicence::where(
            'form_code',
            $formname
        )->first();

        if ($licence_name_validitystaff && $staffdetails->count() > 0) {

            $today = Carbon::today()->toDateString();

            $licence_validitystaff = FeesValidity::where(
                'licence_id',
                $licence_name_validitystaff->id
            )
                ->whereIn('form_type', ['N', 'R', 'D', 'A'])
                ->where('status', 1)
                ->whereDate('validity_start_date', '<=', $today)
                ->orderBy('validity_start_date', 'desc')
                ->first();

            if ($licence_validitystaff && $licence_validitystaff->validity) {

                $qcQscStaff = $staffdetails->filter(function ($staff) {
                    return in_array(
                        strtoupper(trim($staff->staff_category ?? '')),
                        ['QC', 'QSC']
                    );
                });

                if ($qcQscStaff->count() > 0) {

                    $latestQcQscStaff = $qcQscStaff
                        ->filter(function ($staff) {
                            return !empty($staff->staff_cc_validity_to);
                        })
                        ->sortByDesc(function ($staff) {
                            return Carbon::parse($staff->staff_cc_validity_to);
                        })
                        ->first();

                    if ($latestQcQscStaff && !empty($latestQcQscStaff->staff_cc_validity_from)) {

                        $firstQcValidity = Carbon::parse(
                            $latestQcQscStaff->staff_cc_validity_from
                        );

                        $licencePeriodEnd = Carbon::now()->addMonths(
                            (int) $licence_validitystaff->validity
                        );

                        if ($firstQcValidity->lt($licencePeriodEnd)) {
                            $showQcWarning = true;
                        }
                    }
                }
            }
        }

        // =====================================================
        // APPLICATION DOCUMENTS / WORKFLOW
        // =====================================================

        $user_entry = DB::table('ccl_forma_meta')
            ->where('application_id', $application_id)
            ->select('*')
            ->first();

        $documents = DB::table('tnelb_applicant_doc_A')
            ->where('application_id', $application_id)
            ->select('*')
            ->first();

        $workflows = DB::table('tnelb_workflow_a')
            ->leftJoin(
                'ccl_forma_meta',
                'tnelb_workflow_a.application_id',
                '=',
                'ccl_forma_meta.application_id'
            )
            ->leftJoin(
                'mst__roles',
                'tnelb_workflow_a.forwarded_to',
                '=',
                'mst__roles.id'
            )
            ->where('tnelb_workflow_a.application_id', $application_id)
            ->select(
                'tnelb_workflow_a.*',
                'mst__roles.name',
                'ccl_forma_meta.form_name',
                'ccl_forma_meta.license_name'
            )
            ->orderBy('tnelb_workflow_a.id', 'desc')
            ->get();

        $queries = DB::table('tnelb_query_applicable as qa')
            ->leftJoin(
                'ccl_forma_meta as ta',
                'qa.application_id',
                '=',
                'ta.application_id'
            )
            ->where('qa.application_id', $application_id)
            ->where('qa.query_status', 'P')
            ->select('qa.*')
            ->first();

        // =====================================================
        // BANK SOLVENCY
        // =====================================================

        $showbankWarning = false;

        $banksolvency = Tnelb_banksolvency_a::where(
            'application_id',
            $application_id
        )
            ->where('status', '1')
            ->first();

        if (
            $banksolvency &&
            $banksolvency->bank_validity &&
            $licence_validitystaff &&
            $licence_validitystaff->validity
        ) {

            $bankValidity = Carbon::parse(
                $banksolvency->bank_validity
            );

            $bankvalidityend = Carbon::now()->addMonths(
                (int) $licence_validitystaff->validity
            );

            if ($bankValidity->lt($bankvalidityend)) {
                $showbankWarning = true;
            }
        }

        // =====================================================
        // EQUIPMENT
        // =====================================================

        $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        $equipmentlist = DB::table('tnelb_equimentsuser_cl')
            ->where('application_id', $application_id)
            ->get();

        // =====================================================
        // ATTACHMENTS / ADDRESS PROOF
        // =====================================================

        $attachments_cl = DB::table('tnelb_attachments_cl')
            ->where('application_id', $application_id)
            ->get();

        $addressproof = DB::table('tnelb_addressproof_cl')
            ->where('application_id', $application_id)
            ->first();

        // =====================================================
        // CHECKLIST
        // =====================================================

        $checklist = DB::table('mst_checklists as mc')
            ->join(
                'mst_licences as ml',
                'ml.id',
                '=',
                'mc.cert_license_id'
            )
            ->where('ml.cert_licence_code', $applicant->license_name)
            ->where('mc.appl_type', trim($applicant->appl_type))
            ->where('mc.status', 1)
            ->select(
                'mc.*',
                'ml.licence_name',
                'ml.cert_licence_code'
            )
            ->get();

        $Existingchecklist = Cl_Checklist_applicant::where(
            'applicant_id',
            $application_id
        )
            ->where('licence_name', $applicant->license_name)
            ->orderByDesc('id')
            ->first();

        $checkedList_1 = [];
        $verifyList = [];

        if ($Existingchecklist && $Existingchecklist->checklist_json) {

            $json = is_array($Existingchecklist->checklist_json)
                ? $Existingchecklist->checklist_json
                : json_decode($Existingchecklist->checklist_json, true);

            foreach ($json ?? [] as $row) {

                if (!is_array($row) || !isset($row['id'])) {
                    continue;
                }

                $checkedList_1[$row['id']] = (int) ($row['checked'] ?? 0);
                $verifyList[$row['id']] = (int) ($row['verify'] ?? 0);
            }
        }

        // =====================================================
        // QC STAFF / STAFF CHECKLIST
        // =====================================================

        $Qcstaffs = DB::table('tnelb_ea_qc_models')
            ->where('application_id', $application_id)
            ->orderBy('id', 'ASC')
            ->get();

        $staff_checklist = DB::table('cl_staff_detail_adminstore')
            ->where('application_id', $application_id)
            ->get();

        if (!in_array(
            $staff->name,
            ['President', 'Secretary', 'Supervisor', 'Assistant Secretary'],
            true
        )) {
            abort(403, 'Unauthorized');
        }

        $staffVerifyList = $staff_checklist
            ->whereNotNull('staff_cc_no')
            ->pluck('verify_flag', 'staff_cc_no')
            ->toArray();

        // =====================================================
        // MAXIMUM QC VALIDITY
        // =====================================================

        $maxQcStaff = $qcQscStaffs_details
            ->filter(function ($staff) {
                return !empty($staff->staff_cc_validity_to);
            })
            ->sortByDesc(function ($staff) {
                return Carbon::parse($staff->staff_cc_validity_to);
            })
            ->first();

        $maxQcCertNo = $maxQcStaff?->staff_cc_no;
        $maxQcValidityDate = $maxQcStaff?->staff_cc_validity_to;

        // =====================================================
        // DIGITIZATION
        // =====================================================

        $cl_digitization = DB::table('mapping_digi_cls')
            ->where('application_id', $application_id)
            ->first();

        $applicationType = match (trim($applicant->appl_type ?? '')) {
            'N' => 'New',
            'R' => 'Renewal',
            'D' => 'Digitisation',
            'A' => 'Alteration',
            default => $applicant->appl_type ?? 'NA',
        };

        // =====================================================
        // INLINE PDF HTML RENDERER
        // =====================================================

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
            'margin_top' => 12,
            'margin_bottom' => 12,
            'margin_left' => 10,
            'margin_right' => 10,
        ]);

        $mpdf->SetTitle('TNELB Application Preview ' . $application_id);
        $mpdf->SetAuthor('Tamil Nadu Electrical Licensing Board');

        /*
     * Convert values safely to printable HTML.
     */
        $formatValue = function ($value) {

            if ($value === null || $value === '') {
                return '-';
            }

            if ($value instanceof \DateTimeInterface) {
                return e($value->format('d-m-Y H:i:s'));
            }

            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }

            if (is_array($value) || is_object($value)) {
                return e(json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR
                ));
            }

            return e((string) $value);
        };

        /*
     * Print a single object or a collection of objects.
     * Actual database column names are used automatically.
     */
        $renderTable = function ($data) use ($formatValue) {

            if ($data === null) {
                return '<p>No records available.</p>';
            }

            if ($data instanceof \Illuminate\Support\Collection) {
                $rows = $data->values()->all();
            } elseif (is_array($data)) {
                $rows = array_is_list($data) ? $data : [$data];
            } else {
                $rows = [$data];
            }

            if (empty($rows)) {
                return '<p>No records available.</p>';
            }

            $normalizedRows = [];

            foreach ($rows as $row) {

                if (is_object($row)) {
                    $row = (array) $row;
                }

                if (!is_array($row)) {
                    $row = ['Value' => $row];
                }

                $normalizedRows[] = $row;
            }

            $columns = [];

            foreach ($normalizedRows as $row) {
                foreach (array_keys($row) as $column) {
                    if (!in_array($column, $columns, true)) {
                        $columns[] = $column;
                    }
                }
            }

            if (empty($columns)) {
                return '<p>No printable data available.</p>';
            }

            $html = '<table class="data-table"><thead><tr>';

            foreach ($columns as $column) {
                $html .= '<th>' . e(ucwords(str_replace('_', ' ', $column))) . '</th>';
            }

            $html .= '</tr></thead><tbody>';

            foreach ($normalizedRows as $row) {

                $html .= '<tr>';

                foreach ($columns as $column) {

                    $value = $row[$column] ?? null;

                    $html .= '<td>' . $formatValue($value) . '</td>';
                }

                $html .= '</tr>';
            }

            $html .= '</tbody></table>';

            return $html;
        };

        /*
     * Add a section with heading and table.
     */
        $addSection = function ($title, $data, $pageBreak = false) use (
            &$html,
            $renderTable
        ) {

            if ($pageBreak) {
                $html .= '<pagebreak />';
            }

            $html .= '<h3 class="section-title">' . e($title) . '</h3>';

            $html .= $renderTable($data);
        };

        $ownershipType = '-';

        if ($applicant->application_ownershiptype == 'pr') {
            $ownershipType = 'Proprietor';
        } elseif ($applicant->application_ownershiptype == 'pt') {
            $ownershipType = 'Partnership';
        } elseif ($applicant->application_ownershiptype == 'pvt') {
            $ownershipType = 'Private Limited (Pvt LTD)';
        } elseif ($applicant->application_ownershiptype == 'ltd') {
            $ownershipType = 'Limited (LTD)';
        }
        // =====================================================
        // PDF STYLES
        // =====================================================

        $html = '
    <html>
    <head>
        <meta charset="UTF-8">

        <style>

            body {
                font-family: dejavusans;
                font-size: 12px;
                color: #222222;
            }

            .header {
                text-align: center;
                margin-bottom: 12px;
            }

            .header h2 {
                font-size: 17px;
                margin: 3px 0;
            }

            .header h3 {
                font-size: 14px;
                margin: 3px 0;
            }

            .header p {
                font-size: 10px;
                margin: 3px 0;
            }

            .section-title {
                font-size: 12px;
                font-weight: bold;
                background-color: #eeeeee;
                border: 1px solid #cccccc;
                padding: 7px;
                margin-top: 12px;
                margin-bottom: 8px;
            }

            .data-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 12px;
            }

            .data-table th {
                text-align:left;
                background-color: #e9e9e9;
                font-weight: bold;
            }

            .data-table th,
            .data-table td {
                border: 1px solid #999999;
                padding: 5px;
                font-size: 10px;
                vertical-align: top;
                word-wrap: break-word;
            }

            .application-info {
                width: 100%;
                border-collapse: collapse;
                margin-top: 10px;
            }

            .application-info td {
                border: 1px solid #999999;
                padding: 6px;
            }

            .footer {
                text-align: center;
                font-size: 12px;
                margin-top: 15px;
            }

            .ownership-info {
                    width: 100%;
                    border-collapse: collapse;
                    border: none;
                    margin-bottom: 10px;
                }

                .ownership-info td {
                    border: none !important;
                    padding: 4px 6px;
                    vertical-align: top;
                }

              

        </style>
    </head>

    <body>

        <div class="header">
            <h2>GOVERNMENT OF TAMIL NADU</h2>

            <h3>THE ELECTRICAL LICENSING BOARD</h3>

            <p>
                Thiru.Vi.Ka. Industrial Estate,
                Guindy, Chennai – 600 032.
            </p>

            <hr>

            <h3>APPLICATION PREVIEW - ' . e($applicationType) . ' Application </h3>

            <p>
                <strong>Application ID:</strong>
                ' . e($application_id) . '
            </p>
        </div>
    ';

        if ($appl_type == 'D') {


            $fissue = !empty($cl_digitization->fissue)
                ? \Carbon\Carbon::parse($cl_digitization->fissue)->format('d-m-Y')
                : '';

            $from_date = !empty($cl_digitization->from_date)
                ? \Carbon\Carbon::parse($cl_digitization->from_date)->format('d-m-Y')
                : '';

            $to_date = !empty($cl_digitization->to_date)
                ? \Carbon\Carbon::parse($cl_digitization->to_date)->format('d-m-Y')
                : '';

            $html .= '
        <h3 class="section-title">Digitisation Details</h3>

        <table class="data-table">
            <tbody>
             <tr>
                    <th style="width:30%;">Certificate Number</th>
                    <td>' . e($cl_digitization->clnumber ?? '-') . '</td>
                </tr>
                <tr>
                    <th style="width:30%;">Date of First Issue</th>
                    <td>' . e($fissue ?? '-') . '</td>
                </tr>

                <tr>
                    <th>Validity From</th>
                    <td>' . e($from_date ?? '-') . '</td>
                </tr>
                  <tr>
                    <th>Validity To</th>
                    <td>' . e($to_date ?? '-') . '</td>
                </tr>
            </tbody>
        </table>
        ';
        }

        // =====================================================
        // APPLICATION BASIC DETAILS
        // =====================================================

        // =====================================================
        // 1. APPLICATION BASIC DETAILS
        // =====================================================

        $html .= '
<h3 class="section-title">Application Basic Details</h3>

<table class="data-table">
    <tbody>
        <tr>
            <th style="width:30%;">1. Name of the Applicant</th>
            <td>' . e($applicant->applicant_name ?? '-') . '</td>
        </tr>

        <tr>
            <th>2. Business Address</th>
            <td>' . e($applicant->business_address ?? '-') . '</td>
        </tr>
    </tbody>
</table>
';

        // =====================================================
        // 3. OWNERSHIP TYPE
        // =====================================================

        $html .= '
<table class="data-table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Father / Husband Name</th>
            <th>D.O.B, Age</th>
            <th>Address</th>
            <th>Qualifications</th>
            <th>Present Business</th>
            <th>Competency Certificate and Validity</th>
        </tr>
    </thead>
    <tbody>
';

        foreach ($cl_ownership_table as $owner) {

            $competencyDetails = '-';

            if (!empty($owner->competency_certificate_number)) {

                $ccFirstIssue = !empty($owner->competency_certificate_first_issue)
                    ? \Carbon\Carbon::parse($owner->competency_certificate_first_issue)->format('d-m-Y')
                    : '';

                $ccValidityFrom = !empty($owner->competency_certificate_validity_from)
                    ? \Carbon\Carbon::parse($owner->competency_certificate_validity_from)->format('d-m-Y')
                    : '';

                $ccValidityTo = !empty($owner->competency_certificate_validity_to)
                    ? \Carbon\Carbon::parse($owner->competency_certificate_validity_to)->format('d-m-Y')
                    : '';

                $competencyDetails =
                    'CC No: ' . e($owner->competency_certificate_number) . '<br>' .
                    'CC First Issue: ' . e($ccFirstIssue ?: '-') . '<br>' .
                    'CC Validity From: ' . e($ccValidityFrom ?: '-') . '<br>' .
                    'CC Validity To: ' . e($ccValidityTo ?: '-');
            }

            $html .= '
        <tr>
            <td>' . e($owner->proprietor_name ?? '-') . '</td>
            <td>' . e($owner->fathers_name ?? '-') . '</td>
            <td>' . e($owner->dob ?? '-') . ', ' . e($owner->age ?? '-') . '</td>
            <td>' . e($owner->proprietor_address ?? '-') . '</td>
            <td>' . e($owner->qualification ?? '-') . '</td>
            <td>' . e($owner->present_business ?? '-') . '</td>
            <td>' . $competencyDetails . '</td>
        </tr>
    ';
        }

        $html .= '
    </tbody>
</table>';

        $html .= '
        <h3 class="section-title">5A. QC / QSC Staff Details</h3>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Staff Name</th>
                    <th>Competency Certificate Number</th>
                    <th>Certificate First Issue,<br>Validity From<br>Validity To</th>
                
                </tr>
            </thead>
            <tbody>
        ';

        if ($qcQscStaffs_details && count($qcQscStaffs_details) > 0) {

            foreach ($qcQscStaffs_details as $staff) {

                $firstIssue = !empty($staff->staff_cc_first_issue)
                    ? \Carbon\Carbon::parse($staff->staff_cc_first_issue)->format('d-m-Y')
                    : '-';

                $validityFrom = !empty($staff->staff_cc_validity_from)
                    ? \Carbon\Carbon::parse($staff->staff_cc_validity_from)->format('d-m-Y')
                    : '-';

                $validityTo = !empty($staff->staff_cc_validity_to)
                    ? \Carbon\Carbon::parse($staff->staff_cc_validity_to)->format('d-m-Y')
                    : '-';

                $appDoc = !empty($staff->app_doc)
                    ? asset($staff->app_doc)
                    : '';

                $consDoc = !empty($staff->cons_doc)
                    ? asset($staff->cons_doc)
                    : '';

                $verifyFlag = isset($staffVerifyList[$staff->staff_cc_no])
                    ? (int) $staffVerifyList[$staff->staff_cc_no]
                    : 0;

                $verifyStatus = $staff->staff_category != 'OTHERS'
                    ? ($verifyFlag == 1 ? 'Correct' : 'Incorrect')
                    : '-';

                $html .= '
                <tr>
                    <td>' . e($staff->staff_name ?? '-') . '</td>

                    <td>' . e($staff->staff_cc_no ?? '-') . '</td>

                    <td>
                        ' . e($firstIssue) . '<br>
                        ' . e($validityFrom) . '<br>
                        ' . e($validityTo) . '
                    </td>

                    ';



                $html .= '
                
                </tr>';
            }
        } else {

            $html .= '
                <tr>
                    <td colspan="5" style="text-align:center;">
                        No Staffs available.
                    </td>
                </tr>';
        }

        $html .= '
            </tbody>
        </table>';

        // Sort other staff: B -> C -> OTHERS, then by row_index
        $sortedOtherStaff = collect($otherstaffdetails)
            ->sortBy(function ($staff) {
                $order = [
                    'B' => 1,
                    'C' => 2,
                    'OTHERS' => 3,
                ];

                return sprintf(
                    '%d-%010d',
                    $order[$staff->staff_category ?? ''] ?? 4,
                    (int) ($staff->row_index ?? 0)
                );
            })
            ->values();

        $html .= '
<h3 class="section-title">5B. Other Staff Details</h3>

<table class="data-table">
    <thead>
        <tr>
            <th>Staff Category</th>
            <th>Staff Name, CC Number</th>
            <th>CC First Issue,<br>Validity From<br>Validity To</th>
            
        </tr>
    </thead>
    <tbody>
';

        if ($sortedOtherStaff->isNotEmpty()) {

            foreach ($sortedOtherStaff as $staff) {

                $firstIssue = !empty($staff->staff_cc_first_issue)
                    ? \Carbon\Carbon::parse($staff->staff_cc_first_issue)->format('d-m-Y')
                    : '-';

                $validityFrom = !empty($staff->staff_cc_validity_from)
                    ? \Carbon\Carbon::parse($staff->staff_cc_validity_from)->format('d-m-Y')
                    : '-';

                $validityTo = !empty($staff->staff_cc_validity_to)
                    ? \Carbon\Carbon::parse($staff->staff_cc_validity_to)->format('d-m-Y')
                    : '-';

                // Staff name / designation and CC number
                if (($staff->staff_category ?? '') == 'OTHERS') {
                    $staffNameDetails = e($staff->staff_designation ?? '-');
                    $verifyStatus = '-';
                } else {
                    $staffNameDetails =
                        e($staff->staff_name ?? '-') . '<br>' .
                        e($staff->staff_cc_no ?? '-');

                    $verifyFlag = isset($staffVerifyList[$staff->staff_cc_no])
                        ? (int) $staffVerifyList[$staff->staff_cc_no]
                        : 0;

                    $verifyStatus = $verifyFlag == 1
                        ? 'Correct'
                        : 'Incorrect';
                }

                $html .= '
        <tr>
            <td>' . e($staff->staff_category ?? '-') . '</td>

            <td>' . $staffNameDetails . '</td>

            <td>
                ' . e($firstIssue) . '<br>
                ' . e($validityFrom) . '<br>
                ' . e($validityTo) . '
            </td>

           
        </tr>';
            }
        } else {

            $html .= '
        <tr>
            <td colspan="4" style="text-align:center;">
                No Staffs available.
            </td>
        </tr>';
        }



        $html .= '
    </tbody>
</table>';

        $html .= '
<h3 class="section-title">6. Bank Solvency Certificate Details</h3>

<table class="data-table">
    <tbody>
        <tr>
            <th style="width:25%;">Bank Name</th>
            <td>' . e($banksolvency->bank_address ?? '-') . '</td>
        </tr>

        <tr>
            <th>Validity</th>
            <td>' . (
            !empty($banksolvency->bank_validity)
            ? e(\Carbon\Carbon::parse($banksolvency->bank_validity)->format('d-m-Y'))
            : '-'
        ) . '
                
            </td>
        </tr>

        <tr>
            <th>Amount</th>
            <td>' . e($banksolvency->bank_amount ?? '-') . '</td>
        </tr>

        ';



        $html .= '
          
    </tbody>
</table>';

        $html .= '
<h3 class="section-title">
    7. Criminal Offence Details
</h3>

<table class="data-table">
    <tbody>
        <tr>
            <th style="width:75%;">
                Has the applicant or any of his/her staff referred to under item 6,
                been at any time convicted in any court of law or punished by any
                other authority for criminal offences?
            </th>

            <td style="width:25%;">'
            . e(strtoupper($applicant->criminal_offence ?? '-')) .
            '</td>

         ';


        $html .= '
                    </tr>
                </tbody>
            </table>';
        $html .= '
<h3 class="section-title">
    8. Authorised Signatory Details
</h3>

<table class="data-table">
    <thead>
        <tr>
            <th>Name of Signatory</th>
            <th>Age of Signatory</th>
            <th>Qualification of Signatory</th>
            <th>Designation</th>
            
        </tr>
    </thead>
    <tbody>
';

        if ($authoritysignatory && $authoritysignatory->count() > 0) {

            foreach ($authoritysignatory as $authority) {

                $html .= '
        <tr>
            <td>' . e($authority->name_of_authorised_to_sign ?? '-') . '</td>

            <td>' . e($authority->age_of_authorised_to_sign ?? '-') . '</td>

            <td>' . e($authority->qualification_of_authorised_to_sign ?? '-') . '</td>

            <td>' . e($authority->designation_of_authorised_to_sign ?? '-') . '</td>

           ';


                $html .= '
         
        </tr>';
            }
        } else {

            $html .= '
        <tr>
            <td colspan="5" style="text-align:center;">
                No signatory records found.
            </td>
        </tr>';
        }

        $html .= '
    </tbody>
</table>';


        // =====================================================
        // ADDRESS PROOF
        // =====================================================
        $html .= '
<h3 class="section-title">9. Address Proof</h3>

<table class="data-table">
    <tbody>
        <tr>
            <th style="width:30%;">Type of Address Proof</th>
            <td>' . e($addressproof->type_doc ?? '-') . '</td>
        </tr>

        <tr>
            <th>GST / Rental Agreement / Others No</th>
            <td>' . e($addressproof->addressproofno ?? '-') . '</td>
        </tr>

     
            </td>
        </tr>
    </tbody>
</table>';

        // =====================================================
        // EQUIPMENT
        // =====================================================

        // Create equipment map: equipment_id => equipment details
        $equipmentMap = collect($equipmentlist)->keyBy('equipment_id');

        // Get licence_id from stored equipment details
        $licenceId = optional(collect($equipmentlist)->first())->licence_id;

        $html .= '
<h3 class="section-title">10. Equipment Details</h3>

<table class="data-table">
    <thead>
        <tr>
            <th>S.No</th>
            <th>Equipment Name</th>
            <th>Equipment Type</th>
            <th>Serial No</th>
            <th>Make Model</th>
           
            <th>Date of Test</th>
        </tr>
    </thead>
    <tbody>
';

        $equipmentCount = 0;

        foreach ($equiplist as $equip) {

            if ($equip->equip_licence_name == $licenceId) {

                $equipmentCount++;

                $userEquip = $equipmentMap[$equip->id] ?? null;

                $serial = strtoupper($userEquip->serial_no ?? '');
                $model = strtoupper($userEquip->model_no ?? '');
                $date = $userEquip->dateoftest ?? '';

                $testfile = $userEquip->testreport_file ?? '';
                $purchasefile = $userEquip->purchasereport_file ?? '';

                $formattedDate = !empty($date)
                    ? \Carbon\Carbon::parse($date)->format('d-m-Y')
                    : '-';

                $html .= '
        <tr>
            <td style="text-align:center;">' . $equipmentCount . '</td>

            <td>' . e($equip->equip_name ?? '-') . '</td>

            <td>' . e($equip->equipment_type ?? '-') . '</td>

            <td>' . e($serial ?: '-') . '</td>

            <td>' . e($model ?: '-') . '</td>

        

            <td>' . e($formattedDate) . '</td>
        </tr>';
            }
        }

        if ($equipmentCount == 0) {
            $html .= '
        <tr>
            <td colspan="8" style="text-align:center;">
                No equipment details available.
            </td>
        </tr>';
        }

        $html .= '
    </tbody>
</table>';

        // =====================================================
        // ATTACHMENTS
        // =====================================================


        // =====================================================
        // PAYMENT DETAILS
        // =====================================================



        // Application Type

        $html .= '
<h3 class="section-title">Payment Details</h3>

<table class="data-table">
    <tbody>
      

        <tr>
            <th>Application Fees</th>
            <td>' . e($applicant->application_fee ?? '0') . '</td>
        </tr>

        <tr>
            <th>Total Amount</th>
            <td>' . e($applicant->amount ?? '0') . '.00</td>
        </tr>
          <tr>
            <th>Transaction ID</th>
            <td>' . e($applicant->transaction_id ?? '0') . '.00</td>
        </tr>
        ';

        if (($applicant->late_fee ?? 0) > 0) {
            $html .= '
        <tr>
            <th>Late Fees</th>
            <td>' . e($applicant->late_fee) . '</td>
        </tr>';
        }

        $html .= '
        <tr>
            <th>Date of Application</th>
            <td>' . e(
            !empty($applicant->dt_submit)
                ? format_date_other($applicant->dt_submit)
                : '-'
        ) . '</td>
        </tr>
    </tbody>
</table>';

        // =====================================================
        // GENERATE PDF
        // =====================================================

        $mpdf->WriteHTML($html);

        return response(
            $mpdf->Output(
                'application_preview_' . $application_id . '.pdf',
                'S'
            ),
            200
        )
            ->header('Content-Type', 'application/pdf')
            ->header(
                'Content-Disposition',
                'inline; filename="application_preview_' . $application_id . '.pdf"'
            );
    }

    public function licencepdf_cl($application_id, bool $returnBinary = false)
    {

        // dd('111'); exit;
        // ---------------------------------------
        // 1. DETECT WHICH TABLE HAS THE APPLICATION
        // ---------------------------------------
        $application = null;
        $table_name = null;

        $tables = [
            // 'tnelb_esa_applications',
            // 'tnelb_esb_applications',
            // 'tnelb_eb_applications',
            'ccl_forma_meta'
        ];

        foreach ($tables as $t) {
            $record = DB::table($t)
                ->where('application_id', $application_id)
                ->first();

            // dd($record); exit;

            if ($record) {
                $application = $record;
                $table_name = $t;
                break;
            }
        }

        if (!$application) {
            return $returnBinary ? null : back()->with('error', 'Application ID not found.');
        }

        // dd($application->license_name);exit;

        $licence_id = DB::table('mst_licences')
            ->where('cert_licence_code', $application->license_name)
            ->first();

        // Clean appl_type
        $appltype = strtoupper(trim($application->appl_type));


        // ---------------------------------------
        // 2. FRESH APPLICATION (appl_type = N)
        // ---------------------------------------


        $applicant = DB::table('cl_forma_lic')
            ->join($table_name, 'cl_forma_lic.application_id', '=', $table_name . '.application_id')
            ->where('cl_forma_lic.application_id', $application_id)
            ->select(
                'cl_forma_lic.application_id',
                'cl_forma_lic.issued_by',
                'cl_forma_lic.valid_from',
                'cl_forma_lic.valid_to',
                'cl_forma_lic.dateof_issue',

                $table_name . '.applicant_name AS name',
                $table_name . '.license_name',
                $table_name . '.form_name',

                'cl_forma_lic.license_number'
            )
            ->first();

        $QCstaffDetails = DB::table('cl_staff_tbl')
            ->where('application_id', $application_id)
            ->whereRaw("UPPER(TRIM(staff_category)) IN ('QC', 'QSC')")
            ->where('staff_flag', '1')
            ->orderBy('id')
            ->get();

        $staffDetails = DB::table('cl_staff_tbl')
            ->where('application_id', $application_id)
            ->whereRaw("UPPER(TRIM(staff_category)) NOT IN ('QC', 'QSC')")
            ->where('staff_flag', '1')
            ->orderByRaw("
        CASE
            WHEN UPPER(TRIM(staff_category)) = 'B' THEN 1
            WHEN UPPER(TRIM(staff_category)) = 'C' THEN 2
            WHEN UPPER(TRIM(staff_category)) = 'OTHERS' THEN 3
            ELSE 4
        END
    ")
            ->orderBy('id')
            ->get();


        // ---------------------------------------
        // 3. RENEWAL APPLICATION
        // ---------------------------------------
        // else {

        //     $applicant = DB::table('cl_forma_lic')
        //         ->join($table_name, 'cl_forma_lic.application_id', '=', $table_name . '.application_id')
        //         ->where('cl_forma_lic.application_id', $application_id)
        //         ->select(
        //             'cl_forma_lic.application_id',
        //             'cl_forma_lic.issued_by',
        //             'cl_forma_lic.valid_from',
        //             'cl_forma_lic.valid_to',

        //             $table_name . '.applicant_name AS name',
        //             $table_name . '.license_name',
        //             $table_name . '.form_name',

        //             'cl_forma_lic.license_number'
        //         )
        //         ->first();

        //     $QCstaffDetails = DB::table('cl_staff_tbl')
        //     ->where('application_id', $application_id)
        //     ->whereRaw("UPPER(TRIM(staff_category)) IN ('QC', 'QSC')")
        //     ->orderBy('id')
        //     ->get();

        //    $staffDetails = DB::table('cl_staff_tbl')
        //     ->where('application_id', $application_id)
        //     ->whereRaw("UPPER(TRIM(staff_category)) NOT IN ('QC', 'QSC')")
        //     ->orderBy('id')
        //     ->get();
        // }


        // ---------------------------------------
        // 4. RETURN OR LOAD PDF VIEW
        // ---------------------------------------

        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
            'default_font_size' => 11,
            'default_font' => 'Abyssinica_SIL'
        ]);

        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        $formname = $applicant->form_name;

        // dd($formname);
        // exit;

        $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();


        // dd($license_name);
        // exit;

        $mpdf->WriteHTML('<style>
        body { }
        p, td, th { padding: 0px; }
        p {font-size:15px;}
        .tbl_center { text-align: center;!important }
        .mt-2 { margin-top: 5; }
        table { border-collapse: collapse; width: 100%; }
        th, td {  padding: 8px; text-align:left; }
        .highlight { font-weight: bold; color: white; background-color: green; padding: 5px; text-align:center; font-size:16px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
        .highlight_text{color:green;}
        .staff_tbl td{text-align:center;}
        .license_name{ font-size:15px;text-align:center;font-weight:bold; }
        .font-weight{font-weight:bold;}
        .blue{color:#000;}
        .orange{color:#000;}
        .txt_uppercase{text-transform:uppercase;}
        .line-height-30{line-height:20px;}
        .text-indent-40 {text-indent: 40px;}
        .text-indent-60 {text-indent: 50px;}
        .text-justify {text-align: justify;}
        .font-size-14{ font-size:15px;}
        .mb-5{margin-bottom:5px;}
        .mb-1{margin-bottom:1px;}
        .mb-10{margin-bottom:10px;}
        .pb-10{padding: bottom 10px!important;}
        .mt-5{margin-top:5px;}
        .mt-1{margin-top:1px;}
        .text-black{color:black;}
        .text-uppercase{text-transform:uppercase;}
        .font-size-16{font-size:16px;}
        .font-weight-n{font-weight:normal}

    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
        $grade_name = $applicant->license_name;
        // dd($grade_name);
        // exit;

        if ($grade_name == 'EA') {
            $grade_name_txt = 'Electrical Contractors Licence Grade A';
        } elseif ($grade_name == 'ESA') {
            $grade_name_txt = 'ESA Grade Contractor Licence';
        } elseif ($grade_name == 'ESB') {
            $grade_name_txt = 'ESB Grade Contractor Licence';
        } elseif ($grade_name == 'EB') {
            $grade_name_txt = 'EB Grade Contractor Licence ';
        }


        if ($appltype == 'R') {
            $appl_txt = 'Renewal';
        } elseif ($appltype == 'A') {
            $appl_txt = 'Alteration';
        } elseif ($appltype == 'N') {
            $appl_txt = 'New';
        } else {
            $appl_txt = 'Digitisation';
        }


        $qrData = url('/verify-certificate/' . $applicant->application_id);



        // Start building the PDF content
        $html = '
<table width="100%" class="mt-1 mb-1">
    <tr>
        <!-- LEFT CONTENT -->
        <td  style="text-align:center; padding-top:10px;">

            <div class="header-block">
                <h3 class="blue mb-10 pb-10 ">GOVERNMENT OF TAMILNADU</h3>
                <br>

                <h3 class="blue mb-10 pb-10">ELECTRICAL LICENSING BOARD</h3>
                <br>

                <h3 class="blue mb-10 pb-10">
                    Thiru.Vi.Ka. Industrial Estate, Guindy, Chennai - 600 032.
                </h3>
                <br>

            <p class="license_name orange mt-10 text-uppercase">
                ' . $grade_name_txt . ' (' . $appl_txt . ')
            </p>
            </div>

        </td>




    </tr>
</table>';
        $bankvalidity = Tnelb_banksolvency_a::where('application_id', $application_id)->first();
        $proprietors = DB::table('cl_ownership_table')
            ->where('application_id', $application_id)
            ->where('proprietor_flag', '1')
            ->orderBy('id')
            ->pluck('proprietor_name')
            ->toArray();

        $proprietorNames = !empty($proprietors)
            ? implode('<br>', array_map('strtoupper', $proprietors))
            : '—';

        $html .= '
<table style="width:100%; border-collapse:collapse;" class="mt-1 mb-1">
    <tr>
        <!-- LEFT SIDE DATA -->
        <td style="width:80%; vertical-align:top;">
            <table style="width:100%;">
                <tr>
                    <td style="width:50%;"><b class="txt_uppercase">Licence No</b></td>
                    <td>: ' . $applicant->license_number . '</td>
                </tr>

                <tr>
                    <td><b class="txt_uppercase">D.O.I</b></td>
                    <td>: ' . format_date($applicant->dateof_issue) . '</td>
                </tr>

                <tr>
                    <td><b class="txt_uppercase">Validity</b></td>
                    <td>: ' . format_date($applicant->valid_from) . ' To ' . format_date($applicant->valid_to) . '</td>
                </tr>

                <tr>
                    <td><b class="txt_uppercase">Name</b></td>
                    <td>: ' . strtoupper($applicant->name) . '</td>
                </tr>


                 <tr>
                    <td><b class="txt_uppercase">Bank Solvency Validity</b></td>
                    <td>: ' . format_date($bankvalidity->bank_validity) . '</td>
                </tr>

                <tr>
                    <td><b>PROPRIETOR / PARTNER / DIRECTOR NAME</b></td>
                  <td style="line-height:20px;">: ' . $proprietorNames . '</td>
                </tr>
            </table>
        </td>

        <!-- RIGHT SIDE QR -->
        <td style="width:20%; text-align:right; vertical-align:top;">
            <barcode
                code="' . htmlspecialchars($qrData) . '"
                type="QR"
                size="1.5"
                error="H"
            />
        </td>
    </tr>
</table>';

        $html .= '
<h4 class="mt-2 orange font-size-14 font-weight txt_uppercase">QC Staff Details</h4>

<table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; text-align:center;">

   <tr style="background-color:#f2f2f2;">
        <th class="font-weight-n">S.No</th>
        <th class="font-weight-n">Staff Category</th>
         <th class="font-weight-n">Certificate Number</th>
         <th class="font-weight-n">Date Of First Issue</th>
        <th class="font-weight-n">Validity From</th>
        <th class="font-weight-n">Validity To</th>
    </tr>';

        if ($QCstaffDetails->count() > 0) {
            $i = 1;
            foreach ($QCstaffDetails as $staff) {

                $html .= '
        <tr>
            <td>' . $i++ . '</td>
            <td>' . strtoupper($staff->staff_category) . '</td>
            <td>' . $staff->staff_cc_no . '</td>
            <td>' . format_date($staff->staff_cc_first_issue) . '</td>
            <td>' . format_date($staff->staff_cc_validity_from) . '</td>
             <td>' . format_date($staff->staff_cc_validity_to) . '</td>
        </tr>';
            }
        } else {
            $html .= '
    <tr>
        <td colspan="6">No Staff Details Available</td>
    </tr>';
        }
        $html .= '</table>';
        $html .= '
<h4 class="mt-2 orange font-size-14 font-weight txt_uppercase">Staff Details</h4>

<table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; text-align:center;">

   <tr style="background-color:#f2f2f2;">
        <th class="font-weight-n">S.No</th>
        <th class="font-weight-n">Staff Category</th>
         <th class="font-weight-n">Certificate Number</th>
         <th class="font-weight-n">Date Of First Issue</th>
        <th class="font-weight-n">Validity From</th>
        <th class="font-weight-n">Validity To</th>
    </tr>';

        if ($staffDetails->count() > 0) {

            $i = 1;

            foreach ($staffDetails as $staff) {

                $category = strtoupper(trim((string) $staff->staff_category));

                if ($category === 'OTHERS') {

                    // OTHERS: show designation only
                    $html .= '
            <tr>
                <td>' . $i++ . '</td>
                <td>' . $category . '</td>
                <td>' . htmlspecialchars($staff->staff_designation ?? '-') . '</td>
                <td>-</td>
                <td>-</td>
                <td>-</td>
            </tr>';
                } else {

                    // Other categories: show CC number and validity
                    $staffNumber = $staff->staff_cc_no ?? '-';

                    $firstIssue = !empty($staff->staff_cc_first_issue)
                        ? format_date($staff->staff_cc_first_issue)
                        : '-';

                    $validityFrom = !empty($staff->staff_cc_validity_from)
                        ? format_date($staff->staff_cc_validity_from)
                        : '-';

                    $validityTo = !empty($staff->staff_cc_validity_to)
                        ? format_date($staff->staff_cc_validity_to)
                        : '-';

                    $html .= '
            <tr>
                <td>' . $i++ . '</td>
                <td>' . $category . '</td>
                <td>' . htmlspecialchars($staffNumber) . '</td>
                <td>' . htmlspecialchars($firstIssue) . '</td>
                <td>' . htmlspecialchars($validityFrom) . '</td>
                <td>' . htmlspecialchars($validityTo) . '</td>
            </tr>';
                }
            }
        } else {

            $html .= '
    <tr>
        <td colspan="6">No Staff Details Available</td>
    </tr>';
        }
        $html .= '</table>';


        $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        $equipmentlist = DB::table('tnelb_equimentsuser_cl')

            ->where('application_id', $application_id)
            ->get();

        /*
            Create map:
            equip_id => equipment_value
            */
        $equipmentMap = collect($equipmentlist)->keyBy('equipment_id');

        $html .= ' <h4 class="mt-2 orange font-size-14 font-weight txt_uppercase">Equipment / Instruments List</h4>';

        $html .= '
        <table border="1" cellpadding="4" cellspacing="0" width="100%">
            <thead>
                <tr style="font-weight:bold; background-color:#f2f2f2;">
                    <th >S.No</th>
                    <th >Equipment Name</th>
                    <th >Equipment Type</th>
                    <th  align="center">Serial No</th>

                    <th  align="center">Date of Test</th>
                </tr>
            </thead>
            <tbody>';

        $slno = 1;

        foreach ($equiplist as $equip) {

            $userEquip = $equipmentMap[$equip->id] ?? null;

            $serial = $userEquip->serial_no ?? '';
            $model  = $userEquip->model_no ?? '';
            $date   = $userEquip->dateoftest ?? '';

            $html .= '
            <tr>
                <td align="center">' . $slno . '</td>
                <td align="center">' . $equip->equip_name . '</td>
                <td align="center">' . $equip->equipment_type . '</td>
                <td align="center">' . $serial . '</td>

                <td align="center">' . $date . '</td>
            </tr>';
            $slno++;
        }

        $html .= '
    </tbody>
</table>';


        // $html .= '<h4 class="mt-2 highlight"> Payment Details</h4>
        // <table class="tbl_center bank">
        //     <tr>
        //         <th class="">Bank Name</th>
        //         <th class="">Mode of Payment</th>
        //         <th class="">Amount</th>
        //         <th class="">Payment Date</th>
        //         <th class="">Transaction ID</th>
        //     </tr>
        //     <tr>
        //         <td>State Bank of India</td>
        //         <td>UPI</td>
        //         <td>' . ($payment->amount ?? 'N/A') . '</td>
        //         <td>25-02-2025</td>
        //         <td>' . ($payment->transaction_id ?? 'N/A') . '</td>
        //     </tr>
        // </table>';

        // Declaration


        // Write HTML to PDF
        $mpdf->WriteHTML($html);



        // Footer section
        $mpdf->SetFooter('
        <table style="width:100%; font-size:12px;">
            <tr>
                <td style="text-align:left;">TNELB</td>
                <td class="label" style="text-align:right;">Date : ' . date('d-m-Y') . '</td>

            </tr>
        </table>
        ');

        if ($returnBinary) {
            return $mpdf->Output('', 'S');
        }

        return response($mpdf->Output('License_A_approval.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }



    // ----------------------Tamil-----------------------------------------

    public function licencepdf_cl_tamil($application_id, bool $returnBinary = false)
    {

        // dd('111');exit;
        // ---------------------------------------
        // 1. DETECT WHICH TABLE HAS THE APPLICATION
        // ---------------------------------------
        $application = null;
        $table_name = null;

        $tables = [
            // 'tnelb_esa_applications',
            // 'tnelb_esb_applications',
            // 'tnelb_eb_applications',
            'ccl_forma_meta'
        ];

        foreach ($tables as $t) {
            $record = DB::table($t)
                ->where('application_id', $application_id)
                ->first();

            if ($record) {
                $application = $record;
                $table_name = $t;
                break;
            }
        }

        if (!$application) {
            return $returnBinary ? null : back()->with('error', 'Application ID not found.');
        }

        $licence_id = DB::table('mst_licences')
            ->where('cert_licence_code', $application->license_name)
            ->first();

        // Clean appl_type
        $appltype = strtoupper(trim($application->appl_type));


        // ---------------------------------------
        // 2. FRESH APPLICATION (appl_type = N)
        // ---------------------------------------
        if ($appltype === 'N') {

            $applicant = DB::table('cl_forma_lic')
                ->join($table_name, 'cl_forma_lic.application_id', '=', $table_name . '.application_id')
                ->where('cl_forma_lic.application_id', $application_id)
                ->select(
                    'cl_forma_lic.application_id',
                    'cl_forma_lic.issued_by',
                    'cl_forma_lic.valid_from',
                    'cl_forma_lic.valid_to',

                    $table_name . '.applicant_name AS name',
                    $table_name . '.license_name',
                    $table_name . '.form_name',

                    'cl_forma_lic.license_number'
                )
                ->first();

            $staffDetails = DB::table('cl_staff_tbl')
                ->where('application_id', $application_id)
                ->orderBy('id')
                ->get();
        }

        // ---------------------------------------
        // 3. RENEWAL APPLICATION
        // ---------------------------------------
        else {

            $applicant = DB::table('cl_forma_lic')
                ->join($table_name, 'cl_forma_lic.application_id', '=', $table_name . '.application_id')
                ->where('cl_forma_lic.application_id', $application_id)
                ->select(
                    'cl_forma_lic.application_id',
                    'cl_forma_lic.issued_by',
                    'cl_forma_lic.valid_from',
                    'cl_forma_lic.valid_to',

                    $table_name . '.applicant_name AS name',
                    $table_name . '.license_name',
                    $table_name . '.form_name',

                    'cl_forma_lic.license_number'
                )
                ->first();

            $staffDetails = DB::table('cl_staff_tbl')
                ->where('application_id', $application_id)
                ->orderBy('id')
                ->get();
        }


        // ---------------------------------------
        // 4. RETURN OR LOAD PDF VIEW
        // ---------------------------------------
        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'format' => 'A4',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,

            'mode' => 'utf-8',

            'fontDir' => array_merge($fontDirs, [
                public_path('fonts'),
            ]),

            'fontdata' => $fontData + [
                'marutham' => [
                    'R' => 'Marutham.ttf',
                ],
            ],

            'default_font' => 'marutham',

            'autoScriptToLang' => true,
            'autoLangToFont'   => true,
        ]);


        $mpdf->SetTitle('TNELB Application License ' . $applicant->license_name);

        $formname = $applicant->form_name;

        // dd($formname);
        // exit;

        $license_name = DB::table('mst_licences')->where('form_code', $formname)->first();


        // dd($license_name);
        // exit;

        $mpdf->WriteHTML('<style>
        body { }
        p, td, th { padding: 0px; }
        p {font-size:15px;}
        .tbl_center { text-align: center;!important }
        .mt-2 { margin-top: 5; }
        table { border-collapse: collapse; width: 100%; }
        th, td {  padding: 8px; text-align:left; }
        .highlight { font-weight: bold; color: white; background-color: green; padding: 5px; text-align:center; font-size:16px; }
        .photo-container { text-align: right; padding-right: 10px; }
        .photo-container img { width: 132px; height: 170px; border: 1px solid #000; object-fit: cover; display: block; }
        .highlight_text{color:green;}
        .staff_tbl td{text-align:center;}
        .license_name{ font-size:18px;text-align:center;font-weight:bold; }
        .font-weight{font-weight:bold;}
        .blue{color:#000; font-size:18px; font-weight:bold;}
        .orange{color:#000;}
        .txt_uppercase{text-transform:uppercase;}
        .line-height-30{line-height:20px;}
        .text-indent-40 {text-indent: 40px;}
        .text-indent-60 {text-indent: 50px;}
        .text-justify {text-align: justify;}
        .font-size-14{ font-size:15px;}
        .mb-5{margin-bottom:5px;}
        .mb-1{margin-bottom:1px;}
        .mb-10{margin-bottom:10px;}
        .pb-10{padding: bottom 10px!important;}
        .mt-5{margin-top:5px;}
        .mt-1{margin-top:1px;}
        .text-black{color:black;}
        .text-uppercase{text-transform:uppercase;}
        .font-size-16{font-size:16px;}
        .font-weight-n{font-weight:normal}
        b{
        font-size:18px;
        }
        .ft-wt-18{font-size:18px;}

    </style>', \Mpdf\HTMLParserMode::HEADER_CSS);
        $grade_name = $applicant->license_name;
        // dd($grade_name);
        // exit;

        if ($grade_name == 'EA') {
            $grade_name_txt = '"இஏ" கிரேடு மின் ஒப்பந்தக்காரர் உரிமம்';
        } elseif ($grade_name == 'ESA') {
            $grade_name_txt = '"இஎஸ்ஏ" கிரேடு மின் ஒப்பந்தக்காரர் உரிமம்';
        } elseif ($grade_name == 'ESB') {
            $grade_name_txt = '"இஎஸ்பி" கிரேடு மின் ஒப்பந்தக்காரர் உரிமம்';
        } elseif ($grade_name == 'EB') {
            $grade_name_txt = '"இபி" கிரேடு மின் ஒப்பந்தக்காரர் உரிமம்';
        }
        $qrData = url('/verify-certificate/' . $applicant->application_id);



        // Start building the PDF content
        $html = '
<table width="100%" class="mt-1 mb-1">
    <tr>
        <!-- LEFT CONTENT -->
        <td  style="text-align:center; padding-top:10px;">

            <div class="header-block">
                <h3 class="blue mb-10 pb-10 ">தமிழ்நா அர</h3>
                <br>

                <h3 class="blue mb-10 pb-10">மின் உரிமம் வழங்ம் வாரியம் </h3>
                <br>

                <h3 class="blue mb-10 pb-10">
                    திரு.வி.க. தொழிற்பேட்டை, கிண்டி, சென்னை -600 032.
                </h3>
                <br>

                <p class="license_name orange mt-10 text-uppercase">
                    ' . $grade_name_txt . '
                </p>
            </div>

        </td>




    </tr>
</table>';

        $proprietors = DB::table('cl_ownership_table')
            ->where('application_id', $application_id)
            ->where('proprietor_flag', '1')
            ->orderBy('id')
            ->get();
        $proprietorNames = '';

        if ($proprietors->count() > 0) {
            foreach ($proprietors as $proprietor) {
                $proprietorNames .= strtoupper($proprietor->proprietor_name) . ', ';
            }

            // Remove last comma
            $proprietorNames = rtrim($proprietorNames, ', ');
        } else {
            $proprietorNames = '—';
        }



        $html .= '
<table style="width:100%; border-collapse:collapse;" class="mt-1 mb-1">
    <tr>
        <!-- LEFT SIDE DATA -->
        <td style="width:80%; vertical-align:top;">
            <table style="width:100%;">

    <tr>
        <td style="width:40%;"><b>உரிமம் எண்</b></td>
        <td>: ' . $applicant->license_number . '</td>
    </tr>

    <tr>
        <td><b>வழங்கிய நாள்</b></td>
        <td>: ' . format_date($applicant->valid_from) . '</td>
    </tr>

    <tr>
        <td><b>செல்லுபடியாகும் காலம்</b></td>
        <td>: ' . format_date($applicant->valid_from) . ' To ' . format_date($applicant->valid_to) . '</td>
    </tr>

    <tr>
        <td><b>பெயர்</b></td>
        <td>: ' . strtoupper($applicant->name) . '</td>
    </tr>

    <tr>
        <td><b>உரிமையாளர் / பங்குதாரர் / இயக்குநரின் பெயர்</b></td>
        <td>: ' . $proprietorNames . '</td>
    </tr>
            </table>
        </td>

        <!-- RIGHT SIDE QR -->
        <td style="width:20%; text-align:right; vertical-align:top;">
            <barcode
                code="' . htmlspecialchars($qrData) . '"
                type="QR"
                size="1.5"
                error="H"
            />
        </td>
    </tr>
</table>';

        $html .= '
<h4 class="mt-2 orange font-size-14 font-weight ft-wt-18">பணியாளர்கள் விவரங்கள்</h4>

<table border="1" cellpadding="6" cellspacing="0" style="width:100%; border-collapse:collapse; text-align:center;">

    <tr style="background-color:#f2f2f2;">
        <th class="font-weight-n">S.No</th>
        <th class="font-weight-n">Staff Category</th>
         <th class="font-weight-n">Certificate Number</th>
        <th class="font-weight-n">Validity From</th>
        <th class="font-weight-n">Validity To</th>
    </tr>';

        if ($staffDetails->count() > 0) {
            $i = 1;
            foreach ($staffDetails as $staff) {

                $html .= '
        <tr>
            <td>' . $i++ . '</td>
            <td>' . strtoupper($staff->staff_category) . '</td>
            <td>' . $staff->staff_cc_no . '</td>
            <td>' . format_date($staff->staff_cc_validity_from) . '</td>
             <td>' . format_date($staff->staff_cc_validity_to) . '</td>
        </tr>';
            }
        } else {
            $html .= '
    <tr>
        <td colspan="4">No Staff Details Available</td>
    </tr>';
        }
        $html .= '</table>';


        $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        $equipmentlist = DB::table('tnelb_equimentsuser_cl')

            ->where('application_id', $application_id)
            ->get();

        /*
            Create map:
            equip_id => equipment_value
            */
        $equipmentMap = collect($equipmentlist)->keyBy('equipment_id');

        $html .= ' <h4 class="ft-wt-18 mt-2 orange font-size-14 font-weight">உபகரணங்கள் / கருவிகளின் பட்டியல்</h4>';

        $html .= '
        <table border="1" cellpadding="4" cellspacing="0" width="100%">
            <thead>
                <tr style="font-weight:bold; background-color:#f2f2f2;">
                    <th class="font-weight-n">S.No</th>
                    <th class="font-weight-n">Equipment Name</th>
                    <th class="font-weight-n">Equipment Type</th>
                    <th  align="center" class="font-weight-n">Serial No</th>
                    <th align="center" class="font-weight-n">	Make Model</th>
                    <th  align="center" class="font-weight-n">Date of Test</th>
                </tr>
            </thead>
            <tbody>';

        $slno = 1;

        foreach ($equiplist as $equip) {

            $userEquip = $equipmentMap[$equip->id] ?? null;

            $serial = $userEquip->serial_no ?? '';
            $model  = $userEquip->model_no ?? '';
            $date   = $userEquip->dateoftest ?? '';

            $html .= '
            <tr>
                <td align="center">' . $slno . '</td>
                <td align="center">' . $equip->equip_name . '</td>
                <td align="center">' . $equip->equipment_type . '</td>
                <td align="center">' . $serial . '</td>
                <td align="center">' . $model . '</td>
                <td align="center">' . $date . '</td>
            </tr>';
            $slno++;
        }

        $html .= '
    </tbody>
</table>';


        // $html .= '<h4 class="mt-2 highlight"> Payment Details</h4>
        // <table class="tbl_center bank">
        //     <tr>
        //         <th class="">Bank Name</th>
        //         <th class="">Mode of Payment</th>
        //         <th class="">Amount</th>
        //         <th class="">Payment Date</th>
        //         <th class="">Transaction ID</th>
        //     </tr>
        //     <tr>
        //         <td>State Bank of India</td>
        //         <td>UPI</td>
        //         <td>' . ($payment->amount ?? 'N/A') . '</td>
        //         <td>25-02-2025</td>
        //         <td>' . ($payment->transaction_id ?? 'N/A') . '</td>
        //     </tr>
        // </table>';

        // Declaration


        // Write HTML to PDF
        $mpdf->WriteHTML($html);



        // Footer section
        $mpdf->SetFooter('
        <table style="width:100%; font-size:12px;">
            <tr>
                <td style="text-align:left;">TNELB</td>
                <td class="label" style="text-align:right;">Date : ' . date('d-m-Y') . '</td>

            </tr>
        </table>
        ');

        if ($returnBinary) {
            return $mpdf->Output('', 'S');
        }

        return response($mpdf->Output('License_A_approval.pdf', 'I'))
            ->header('Content-Type', 'application/pdf');
    }

    public function streamStoredLicenceA(string $application_id, string $lang = 'en')
    {
        $column = $lang === 'ta' ? 'lic_pdf' : 'cert_pdf';

        $row = DB::table('cl_forma_lic')
            ->where('application_id', $application_id)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->orderByDesc('id')
            ->first()
            ?? DB::table('cl_forma_lic')
                ->where('application_id', $application_id)
                ->orderByDesc('id')
                ->first();

        if (!$row) {
            return back()->with('error', 'Licence not found.');
        }

        $binary = null;

        if (!empty($row->{$column})) {
            try {
                $binary = Crypt::decryptString($row->{$column});
            } catch (\Throwable $e) {
                $binary = null;
            }
        }

        if (!$binary) {
            $binary = $lang === 'ta'
                ? $this->licencepdf_cl_tamil($application_id, true)
                : $this->licencepdf_cl($application_id, true);

            if (!$binary) {
                return back()->with('error', 'Licence not found.');
            }

            DB::table('cl_forma_lic')
                ->where('id', $row->id)
                ->update([
                    $column      => Crypt::encryptString($binary),
                    'updated_at' => now(),
                ]);
        }

        return response($binary)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="licence_' . $application_id . '.pdf"');
    }
}
