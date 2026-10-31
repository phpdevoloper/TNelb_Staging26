<?php

namespace App\Http\Controllers;

use App\Models\Admin\Mst_equipment_tbl;
use App\Models\Admin\WorkflowA;
use App\Models\EA_Application_model;
use App\Models\Equipment_storetmp_A;
use App\Models\Equipmentforma_tbl;
use App\Models\MstLicence;
use App\Models\ProprietorformA;
use App\Models\Tnelb_Addressproof_cl;
use App\Models\Tnelb_Attachments_cl;
use App\Models\Tnelb_banksolvency_a;
use App\Models\Tnelb_cl_validitycheck;
use App\Models\Tnelb_Equimentsuser_cl;
use App\Models\TnelbApplicantStaffDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\File;


use Illuminate\Support\Str;

class ReturnapplicantController extends BaseController
{
    protected $dbNow;
    public function __construct()
    {
        parent::__construct();
        $this->dbNow = DB::selectOne("SELECT date_trunc('second', NOW()::timestamp) AS db_now")->db_now;
    }
    private function toUpperCaseRecursive($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->toUpperCaseRecursive($value);
            } elseif (is_string($value)) {
                $data[$key] = strtoupper($value);
            }
        }
        return $data;
    }
    public function formatDatesToDMY(array $fields, Request $request)
    {
        foreach ($fields as $field) {
            $original = $request->input($field);

            if (is_array($original)) {
                $converted = [];

                foreach ($original as $index => $value) {
                    $converted[$index] = $value ? $this->convertToDMY($value) : null;
                }

                // Merge back into request
                $request->merge([
                    $field => $converted
                ]);
            } else {
                if ($original) {
                    $request->merge([
                        $field => $this->convertToDMY($original)
                    ]);
                }
            }
        }
    }

    private function convertToDMY($value)
    {
        try {
            // Ensure Carbon can handle the string, and return formatted date
            return Carbon::parse($value)->format('d/m/Y'); // or 'Y-m-d' based on DB expectations
        } catch (\Exception $e) {
            // Optional: log error for debugging
            // \log()::error("Date parse error for value: $value", ['exception' => $e]);
            return null;
        }
    }

    public function returnforma($application_id)
    {
        $application = null;
        $proprietors = collect();
        $staffs = collect();
        $document = collect();

        if ($application_id) {
            $application = DB::table('ccl_forma_meta')->where('application_id', $application_id)->first();
            $proprietors = DB::table('cl_ownership_table')
                ->where('application_id', $application_id)
                ->where('proprietor_flag', '1')
                ->orderBy('id')->get();
            $draftCount = $proprietors->count();

            $draftCounts = ProprietorformA::where('application_id', $application_id)
                ->count();

            $ownershipType = ProprietorformA::where('application_id', $application_id)
                ->where('proprietor_flag', 1)
                ->value('ownership_type');
            // dd($proprietors);exit;



            // dd($staffs);
            // exit;

            // $Qcstaffs = DB::table('tnelb_ea_qc_models')->where('application_id', $application_id)->orderBy('id', 'ASC')->get();
            $document = DB::table('tnelb_applicant_doc_A')->where('application_id', $application_id)->first();
            $banksolvency = Tnelb_banksolvency_a::where('application_id', $application_id)->where('status', '1')->first();

            $equipmentlist = Equipment_storetmp_A::where('application_id', $application_id)->first();


            $attachment_doc = Tnelb_Attachments_cl::where('application_id', $application_id)->get();

            $Address_proof = Tnelb_Addressproof_cl::where('application_id', $application_id)->first();

            $equipmentDetails = Tnelb_Equimentsuser_cl::where('application_id', $application_id)
                ->get()
                ->keyBy('equipment_id');



            $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
                ->where('status', 1)
                ->orderBy('id')
                ->get();

            $equipmentlist = DB::table('equipmentforma_tbls')
                ->where('login_id', Auth::user()->login_id)
                ->where('application_id', $application_id)
                ->get();

            $cert_licence_code = 'EA';
            $form_code = MstLicence::where('cert_licence_code', $cert_licence_code)
                ->where('status', 1)
                ->orderBy('id')
                ->first();

            $returnsection = json_decode($application->return_reason, true);

            $QCstaffs = DB::table('cl_staff_tbl')
                ->where('application_id', $application_id)
                ->whereIn('staff_category', ['QC', 'QSC'])
                ->where('staff_flag', '1')
                ->orderBy('id', 'ASC')
                ->get();

            $staff_store_tbl_0 = DB::table('cl_staff_detail_adminstore')
                ->where('application_id', $application_id)
                ->where('verify_flag', '0')
                ->pluck('staff_cc_no')
                ->toArray();

            $staffs = DB::table('cl_staff_tbl')
                ->where('application_id', $application_id)
                ->whereNotIn('staff_category', ['QC', 'QSC'])
                ->where('staff_flag', '1')
                ->orderBy('id', 'ASC')
                ->get();





            // dd($staff_store_tbl_0); exit;

            $staff_store_tbl_1 = DB::table('cl_staff_detail_adminstore')->where('application_id', $application_id)
                ->where('verify_flag', '1')
                ->get();


            $authoritysignatory = DB::table('cl_forma_signs')
                ->where('application_id', $application_id)
                ->where('flag', 1)
                ->orderBy('row_index', 'asc')
                ->get();



            // var_dump()
            // dd($application->appl_type); exit;
        }

        // return view('user_login.apply-form-a', compact('application', 'proprietors', 'draftCount', 'staffs', 'document', 'banksolvency' , 'equipmentlist', 'equiplist', 'form_code', 'attachment_doc', 'Address_proof', 'equipmentDetails','Qcstaffs'));

        return view('user_login.return.forma', compact('application', 'proprietors', 'draftCount', 'staffs', 'document', 'banksolvency', 'equipmentlist', 'equiplist', 'form_code', 'attachment_doc', 'Address_proof', 'equipmentDetails', 'QCstaffs', 'draftCounts', 'ownershipType', 'returnsection', 'staff_store_tbl_0', 'staff_store_tbl_1', 'authoritysignatory'));
    }




    public function storereturn(Request $request)
    {
        // dd($request->all()); exit;
        $sessionId = session()->getId();

        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);
        $isDraft = $request->input('form_action') === 'draft';
        $recordId = $request->input('record_id');


        // dd($recordId); exit;

        // Format date fields
        $this->formatDatesToDMY([], $request);

        if ($isDraft) {
            // Draft mode: minimal required fields, rest nullable
            $rules = [
                'applicant_name' => 'required|string|max:255',
                'business_address' => 'required|string|max:500',
                'form_name' => 'required|string|max:255',
                'license_name' => 'required|string|max:255',
                'appl_type' => 'required',

                'application_ownershiptype' => 'nullable|string',

                // Optional fields in draft mode
                'authorised_name_designation' => 'nullable|string|max:255',
                'authorised_name' => 'nullable|string|max:255',
                'authorised_designation' => 'nullable|string|max:255',

                'previous_contractor_license' => 'required|string|max:10',
                'previous_application_number' => 'nullable|string|max:50',
                'previous_validity_first_issue' => 'nullable',
                'previous_validity_from' => 'nullable',
                'previous_validity_to' => 'nullable',


                'bank_address' => 'nullable|string|max:500',
                'bank_validity' => 'nullable|date',
                'bank_amount' => 'nullable|numeric|min:0',

                'previous_contractor_license_verify' => 'nullable|numeric',
                'criminal_offence' => 'nullable|string|in:yes,no',
                'consent_letter_enclose' => 'nullable|string|in:yes,no',
                'cc_holders_enclosed' => 'nullable|string|in:yes,no',
                'purchase_bill_enclose' => 'nullable|string|in:yes,no',
                'test_reports_enclose' => 'nullable|string|in:yes,no',
                'specimen_signature_enclose' => 'nullable|string|in:yes,no',
                'separate_sheet' => 'nullable|string|in:yes,no',



                'declaration1' => 'nullable|string|max:255',
                'declaration2' => 'nullable|string|max:255',
            ];
        } else {
            // Final submission: all required fields
            $rules = [
                'applicant_name' => 'required|string|max:255',
                'business_address' => 'required|string|max:500',

                // 'application_ownershiptype' => 'required|string',

                'previous_contractor_license' => 'required|string|max:10',
                'previous_application_number' => 'nullable|string|max:50',
                'previous_validity_first_issue' => 'nullable',
                'previous_validity_from' => 'nullable',
                'previous_validity_to' => 'nullable',

                'bank_address' => 'required|string|max:500',
                'bank_validity' => 'required|date',
                'bank_amount' => 'required|numeric|min:0',

                'criminal_offence' => ['required', 'string', Rule::in(['yes', 'no'])],

                'form_name' => 'required|string|max:255',
                'license_name' => 'required|string|max:255',

                'declaration1' => 'required|string|max:255',
                'declaration2' => 'required|string|max:255',
                'address_proof' => $request->hasFile('address_proof') ? 'required|file|max:2048' : 'nullable|string',



            ];
        }

        // dd($request->application_ownershiptype); exit;
        $validatedData = $request->validate($rules);


        // Determine if record exists
        $applicationId = null;
        $existing = null;

        if ($recordId) {
            $existing = EA_Application_model::where('application_id', $recordId)->first();
            // dd($existing->application_id); exit;
            if ($existing) {
                $applicationId = $existing->application_id;

                // dd($applicationId); exit;
            }
        }
        if (!$applicationId) {
            $applicationId = $this->generateApplicationId(
                $request->appl_type,
                $request->form_name,
                $request->license_name
            );
        }



        $returnsection = json_decode($existing->return_reason, true);

        // dd($returnsection); exit;

        if (trim($request->appl_type) == 'D') {
            DB::table('mapping_digi_cls')
                ->where('temp_app_id', $request->temp_app_id)
                ->update([
                    'application_id' => $applicationId,
                    'updated_at' => now(),
                ]);
        }

        // Final data to save
        $dataToSave = $validatedData;
        $dataToSave['application_id'] = $applicationId;
        $dataToSave['login_id'] = $request->login_id_store;

        // $dataToSave['application_ownershiptype'] =
        //     $request->input('application_ownershiptype');

        // dd($dataToSave['application_ownershiptype']); exit;
        if (
            is_array($returnsection) &&
            in_array('basic_details', $returnsection, true)
        ) {
            $dataToSave['applicant_name'] =
                $request->input('applicant_name');
            $dataToSave['business_address'] =
                $request->input('business_address');
        }

        $dataToSave['previous_application_number'] =
            $request->input('previous_application_number');

        $dataToSave['previous_validity_first_issue'] =
            $request->input('previous_validity_first_issue');

        $dataToSave['previous_validity_from'] =
            $request->input('previous_validity_from');

        $dataToSave['previous_validity_to'] =
            $request->input('previous_validity_to');
        if ($request->appl_type === 'D') {
            $dataToSave['payment_status'] = $isDraft ? 'draft' : 'paid';
        } else {
            $dataToSave['payment_status'] = $isDraft ? 'draft' : 'pending';
        }
        $dataToSave['application_status'] = 'P';
        $dataToSave['updated_at'] = DB::raw('NOW()');

        $appl_type = preg_replace('/\s+/', '', $request->appl_type ?? '');

        $appl_type = trim($request->appl_type);

        // Addressproof----------------

        if (!empty($request->type_doc) || !empty($request->addressproofno)) {

            $addressproof = [
                'application_id' => $applicationId,
                'login_id'       => $request->login_id_store,
                'form_name'  => $request->form_name,
                'license_name'    => $request->license_name,
                'addressproofno'    => $request->addressproofno,
                'type_doc'    => $request->type_doc,
            ];

            $existingaddress = Tnelb_Addressproof_cl::where('application_id', $applicationId)->first();

            if ($existingaddress) {
                $existingaddress->update($addressproof);
            } else {
                Tnelb_Addressproof_cl::create($addressproof);
            }
        }

        if (
            is_array($returnsection) &&
            in_array('bank_solvency', $returnsection, true)
        ) {
            //  Save Bank Solvency data in separate table
            if (!empty($request->bank_address) || !empty($request->bank_validity) || !empty($request->bank_amount)) {


                $bankData = [
                    'application_id' => $applicationId,
                    'login_id'       => $request->login_id_store,
                    'bank_address'   => strtoupper($request->bank_address) ?? null,
                    'bank_validity'  => $request->bank_validity ?? null,
                    'bank_amount'    => $request->bank_amount ?? null,
                    'status'         => '1'
                ];

                $existingBank = Tnelb_banksolvency_a::where('application_id', $applicationId)->first();

                if ($existingBank) {
                    $existingBank->update($bankData);
                } else {
                    Tnelb_banksolvency_a::create($bankData);
                }
            }
        }


        // 1. Remove old rows for this application
        Equipmentforma_tbl::where('application_id', $applicationId)
            ->where('login_id', $request->login_id_store)
            ->delete();

        // 2. Insert fresh rows
        foreach ($request->equipments as $row) {

            Equipmentforma_tbl::create([
                'application_id'   => $applicationId,
                'login_id'         => $request->login_id_store,
                'equip_id'         => $row['equip_id'],
                'licence_id'       => $row['licence_id'],
                'form_name'        => $request->form_name,
                'equipment_value'  => $row['value'] ?? 'no',
                'ipaddress'        => $request->ip(),
            ]);
        }

        $Tnelb_cl_validitycheck_existing = Tnelb_cl_validitycheck::where('application_id', $applicationId)
            ->where('login_id', $request->login_id_store)
            ->first();

        $data = [
            'application_id' => $applicationId,
            'login_id'       => $request->login_id_store,
            'form_name'      => $request->form_name,
            'license_name'   => $request->license_name,
            'check_value'    => $request->check_value,
            'ipaddress'      => $request->ip(),
        ];

        if ($Tnelb_cl_validitycheck_existing) {

            $Tnelb_cl_validitycheck_existing->update($data);
        } else {

            Tnelb_cl_validitycheck::create($data);
        }


        unset($dataToSave['bank_address'], $dataToSave['bank_validity'], $dataToSave['bank_amount']);


        if (
            is_array($returnsection) &&
            in_array('qc_staff', $returnsection, true)
        ) {
            $processedStaffIdsQC = [];

            if ($request->has('staffqc_category')) {

                $staffCategories = $request->input('staffqc_category', []);

                $staffName = $request->input('certificate_holder_name', []);
                $staffNameQC = $request->input('certificate_holder_name_qc', []);

                $staffCcNos = $request->input('staff_cc_no', []);
                $staffFirstIssues = $request->input('staff_cc_first_issue', []);
                $staffValidityFroms = $request->input('staff_cc_validity_from', []);
                $staffValidityTos = $request->input('staff_cc_validity_to', []);

                $staffQcIds = $request->input('staffqc_id', []);
                $rowIndexes = $request->input('row_index', []);

                $removedStaffIds = array_filter(
                    (array) $request->input('removed_staffqc_ids', []),
                    fn($id) => is_numeric($id)
                );

                $now = now();

                // ==========================================================
                // 1. DEACTIVATE EXPLICITLY REMOVED STAFF
                // ==========================================================

                if (!empty($removedStaffIds)) {

                    DB::table('cl_staff_tbl')
                        ->where('application_id', $applicationId)
                        ->whereIn('staff_category', ['QC', 'QSC'])
                        ->whereIn('id', $removedStaffIds)
                        ->update([
                            'staff_flag' => '0',
                            'updated_at' => $now,
                        ]);
                }

                // ==========================================================
                // 2. GET CURRENT MAXIMUM ROW INDEX
                // ==========================================================

                $lastRowIndex = DB::table('cl_staff_tbl')
                    ->where('application_id', $applicationId)
                    ->max('row_index');

                $nextRowIndex = ((int) $lastRowIndex) + 1;


                $currentRowIndexes = $currentRowIndexes ?? [];

                foreach ($staffCategories as $index => $category) {

                    $category = strtoupper(trim((string) $category));

                    if (!in_array($category, ['QC', 'QSC'], true)) {
                        continue;
                    }

                    $ccNo = trim((string) ($staffCcNos[$index] ?? ''));

                    if (
                        $ccNo === '' ||
                        in_array(strtolower($ccNo), ['undefined', 'null'], true)
                    ) {
                        continue;
                    }

                    $ccName = $category === 'QC'
                        ? ($staffNameQC[$index] ?? $staffName[$index] ?? '')
                        : ($staffName[$index] ?? $staffNameQC[$index] ?? '');

                    $firstIssue = trim((string) ($staffFirstIssues[$index] ?? ''));
                    $validityFrom = trim((string) ($staffValidityFroms[$index] ?? ''));
                    $validityTo = trim((string) ($staffValidityTos[$index] ?? ''));

                    $staffId = $staffQcIds[$index] ?? null;
                    $submittedRowIndex = $rowIndexes[$index] ?? null;

                    // Normalize staff ID.
                    $staffId = is_numeric($staffId) ? (int) $staffId : null;

                    // Normalize submitted row index.
                    $submittedRowIndex = (
                        $submittedRowIndex !== null &&
                        $submittedRowIndex !== '' &&
                        is_numeric($submittedRowIndex)
                    ) ? (int) $submittedRowIndex : null;

                    // ------------------------------------------------------
                    // 1. FIND EXISTING ROW BY STAFF ID
                    // ------------------------------------------------------

                    $existing = null;

                    if ($staffId !== null && $staffId > 0) {
                        $existing = DB::table('cl_staff_tbl')
                            ->where('id', $staffId)
                            ->where('application_id', $applicationId)
                            ->where('staff_category', $category)
                            ->first();
                    }

                    // ------------------------------------------------------
                    // 2. FIND EXISTING ROW BY EXACT ROW INDEX + CATEGORY
                    // ------------------------------------------------------

                    if (!$existing && $submittedRowIndex !== null) {
                        $existing = DB::table('cl_staff_tbl')
                            ->where('application_id', $applicationId)
                            ->where('row_index', $submittedRowIndex)
                            ->where('staff_category', $category)
                            ->first();
                    }

                    // ------------------------------------------------------
                    // 3. UPDATE EXISTING ROW OR INSERT NEW ROW
                    // ------------------------------------------------------

                    $staffData = [
                        'staff_cc_no' => $ccNo,
                        'staff_flag' => '1',
                        'updated_at' => now(),
                    ];

                    if (trim((string) $ccName) !== '') {
                        $staffData['staff_name'] = trim((string) $ccName);
                    }

                    if ($firstIssue !== '') {
                        $staffData['staff_cc_first_issue'] = $firstIssue;
                    }

                    if ($validityFrom !== '') {
                        $staffData['staff_cc_validity_from'] = $validityFrom;
                    }

                    if ($validityTo !== '') {
                        $staffData['staff_cc_validity_to'] = $validityTo;
                    }

                    if ($existing) {

                        // Preserve row_index, app_doc and cons_doc.
                        DB::table('cl_staff_tbl')
                            ->where('id', $existing->id)
                            ->update($staffData);

                        $savedStaffId = $existing->id;
                        $savedRowIndex = (int) $existing->row_index;
                    } else {

                        // IMPORTANT:
                        // Use the submitted row_index first.
                        // Generate a new one only if the form supplied no row_index.

                        if ($submittedRowIndex !== null) {

                            $newRowIndex = $submittedRowIndex;
                        } else {

                            // Do not assume MAX(row_index) + 1 is the intended row.
                            // A missing row index must be corrected in the form payload
                            // when an existing document row is meant to be reused.

                            throw new \RuntimeException(
                                'Missing row_index for ' . $category .
                                    ' staff at form index ' . $index .
                                    '. Send the existing row_index from the frontend.'
                            );
                        }

                        // Check whether another QC/QSC row already occupies this index.
                        $rowAtIndex = DB::table('cl_staff_tbl')
                            ->where('application_id', $applicationId)
                            ->where('row_index', $newRowIndex)
                            ->whereIn('staff_category', ['QC', 'QSC'])
                            ->first();

                        if ($rowAtIndex) {

                            if ($rowAtIndex->staff_category !== $category) {
                                throw new \RuntimeException(
                                    'Row index ' . $newRowIndex .
                                        ' already belongs to ' . $rowAtIndex->staff_category .
                                        '; cannot save it as ' . $category . '.'
                                );
                            }

                            // Reuse the existing row instead of inserting a duplicate.
                            DB::table('cl_staff_tbl')
                                ->where('id', $rowAtIndex->id)
                                ->update($staffData);

                            $savedStaffId = $rowAtIndex->id;
                            $savedRowIndex = (int) $rowAtIndex->row_index;
                        } else {

                            $savedStaffId = DB::table('cl_staff_tbl')
                                ->insertGetId(array_merge([
                                    'login_id' => $request->input('login_id_store'),
                                    'application_id' => $applicationId,
                                    'staff_category' => $category,
                                    'row_index' => $newRowIndex,
                                    'staff_status' => 'N',
                                    'created_at' => now(),
                                ], $staffData));

                            $savedRowIndex = $newRowIndex;
                        }
                    }

                    $processedStaffIdsQC[] = $savedStaffId;
                    $currentRowIndexes[] = $savedRowIndex;
                }



                $tempDocs = DB::table('tnelb_temp_uploaded_documents')
                    ->where(
                        'login_id',
                        $request->input('login_id_store')
                    )
                    ->where(
                        'form_name',
                        $request->input('form_name')
                    )
                    ->where(
                        'license_name',
                        $request->input('license_name')
                    )
                    ->whereIn('document_category', [
                        'app_doc',
                        'cons_doc',
                    ])
                    ->where(
                        'appl_type',
                        trim((string) $request->input('appl_type'))
                    )
                    ->where('is_final', '1')
                    ->get();

                // ==========================================================
                // 8. PROCESS TEMPORARY DOCUMENTS
                // ==========================================================

                foreach ($tempDocs as $tempDoc) {

                    $tempRowIndex = trim(
                        (string) ($tempDoc->row_index ?? '')
                    );

                    if (
                        $tempRowIndex === '' ||
                        !is_numeric($tempRowIndex) ||
                        in_array(
                            strtolower($tempRowIndex),
                            ['undefined', 'null'],
                            true
                        )
                    ) {
                        continue;
                    }

                    $tempRowIndex = (int) $tempRowIndex;

                    // ------------------------------------------------------
                    // BUILD DOCUMENT PATH
                    // ------------------------------------------------------

                    $documentPath = rtrim(
                        (string) ($tempDoc->file_path ?? ''),
                        '/'
                    );

                    $fileName = ltrim(
                        (string) ($tempDoc->file_name ?? ''),
                        '/'
                    );

                    if ($documentPath !== '' && $fileName !== '') {
                        $documentPath .= '/' . $fileName;
                    } elseif ($fileName !== '') {
                        $documentPath = $fileName;
                    }

                    if (trim($documentPath) === '') {
                        continue;
                    }

                    // ------------------------------------------------------
                    // FIND EXISTING QC / QSC ROW
                    // ------------------------------------------------------

                    $staff = DB::table('cl_staff_tbl')
                        ->where('application_id', $applicationId)
                        ->where('row_index', $tempRowIndex)
                        ->whereIn('staff_category', ['QC', 'QSC'])
                        ->first();

                    // ------------------------------------------------------
                    // IF MISSING, CREATE A DOCUMENT-ONLY ROW
                    // ------------------------------------------------------
                    // Normally the staff loop above creates the row.
                    // This fallback prevents the uploaded document from being
                    // discarded if no staff row exists.
                    //
                    // The correct staff_category must be available from the
                    // temporary record or request. Do not guess QC versus QSC.

                    if (!$staff) {

                        $documentStaffCategory = strtoupper(trim(
                            (string) (
                                $tempDoc->staff_category
                                ?? $tempDoc->ownership_type
                                ?? ''
                            )
                        ));

                        if (!in_array(
                            $documentStaffCategory,
                            ['QC', 'QSC'],
                            true
                        )) {
                            // Cannot safely assign a document to QC or QSC.
                            continue;
                        }

                        $staffId = DB::table('cl_staff_tbl')
                            ->insertGetId([
                                'login_id' => $request->input('login_id_store'),
                                'application_id' => $applicationId,
                                'row_index' => $tempRowIndex,
                                'staff_category' => $documentStaffCategory,
                                $tempDoc->document_category => $documentPath,
                                'staff_flag' => '1',
                                'staff_status' => 'N',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ]);

                        $staff = DB::table('cl_staff_tbl')
                            ->where('id', $staffId)
                            ->first();
                    }

                    // ======================================================
                    // 9. UPDATE ONLY THE RELEVANT DOCUMENT FIELD
                    // ======================================================

                    if ($tempDoc->document_category === 'app_doc') {

                        DB::table('cl_staff_tbl')
                            ->where('id', $staff->id)
                            ->where('application_id', $applicationId)
                            ->where('row_index', $tempRowIndex)
                            ->update([
                                'app_doc' => $documentPath,
                                'updated_at' => $now,
                            ]);
                    } elseif ($tempDoc->document_category === 'cons_doc') {

                        DB::table('cl_staff_tbl')
                            ->where('id', $staff->id)
                            ->where('application_id', $applicationId)
                            ->where('row_index', $tempRowIndex)
                            ->update([
                                'cons_doc' => $documentPath,
                                'updated_at' => $now,
                            ]);
                    }
                }
            }
        }

        if (
            is_array($returnsection) &&
            in_array('staff_details', $returnsection, true)
        ) {

            if ($request->has('cc_number') || $request->filled('designation')) {

                $processedStaffIds = [];

                $staffIdsFromForm = $request->input('staff_id', []);
                $rowIndexesFromForm = $request->input('row_index', []);

                // ==========================================================
                // ROW INDEXES PRESENT IN CURRENT FORM
                // ==========================================================

                $submittedRowIndexes = [];

                foreach ($rowIndexesFromForm as $rowIndex) {

                    if (
                        $rowIndex !== null &&
                        $rowIndex !== '' &&
                        strtolower(trim((string) $rowIndex)) !== 'undefined' &&
                        strtolower(trim((string) $rowIndex)) !== 'null'
                    ) {
                        $submittedRowIndexes[] = (int) $rowIndex;
                    }
                }

                $submittedRowIndexes = array_values(
                    array_unique($submittedRowIndexes)
                );


                // ==========================================================
                // EXISTING B / C / OTHERS STAFF ONLY
                // QC / QSC WILL NOT BE TOUCHED
                // ==========================================================

                $existingStaffIds = DB::table('cl_staff_tbl')
                    ->where('application_id', $applicationId)
                    ->whereIn('staff_category', ['B', 'C', 'OTHERS'])
                    ->pluck('id')
                    ->toArray();


                // ==========================================================
                // MARK REMOVED B / C / OTHERS STAFF AS staff_flag = 0
                // QC / QSC WILL NOT BE UPDATED
                // ==========================================================

                DB::table('cl_staff_tbl')
                    ->where('application_id', $applicationId)
                    ->whereIn('staff_category', ['B', 'C', 'OTHERS'])
                    ->when(
                        !empty($submittedRowIndexes),
                        function ($query) use ($submittedRowIndexes) {
                            $query->whereNotIn(
                                'row_index',
                                $submittedRowIndexes
                            );
                        },
                        function ($query) {
                            $query->whereNotNull('row_index');
                        }
                    )
                    ->update([
                        'staff_flag' => '0',
                        'updated_at' => now(),
                    ]);


                // ==========================================================
                // CURRENT MAX ROW INDEX
                // ==========================================================

                $lastRowIndex = DB::table('cl_staff_tbl')
                    ->where('application_id', $applicationId)
                    ->max('row_index');

                $nextRowIndex = ((int) $lastRowIndex) + 1;


                // ==========================================================
                // LOOP CURRENT B / C / OTHERS STAFF
                // ==========================================================

                foreach ($request->input('staff_category', []) as $index => $category) {

                    // ----------------------------------------------------------
                    // CATEGORY
                    // ----------------------------------------------------------

                    $category = is_array($category)
                        ? ($category[0] ?? null)
                        : $category;

                    $category = strtoupper(trim((string) $category));


                    // ----------------------------------------------------------
                    // ONLY B / C / OTHERS
                    // QC / QSC WILL NOT BE PROCESSED HERE
                    // ----------------------------------------------------------

                    if (!in_array($category, ['B', 'C', 'OTHERS'], true)) {
                        continue;
                    }


                    // ----------------------------------------------------------
                    // GET VALUES USING SAME INDEX
                    // ----------------------------------------------------------

                    $ccNumber = $request->input("cc_number.$index");

                    $firstIssue = $request->input("cc_firstissue.$index");

                    $validityFrom = $request->input("cc_validity_from.$index");

                    $validityTo = $request->input("cc_validity_to.$index");

                    $ccName = $request->input("certificate_holder_name_others.$index");

                    $designation = $request->input("designation.$index");

                    $staffId = $staffIdsFromForm[$index] ?? null;

                    $rowIndex = $rowIndexesFromForm[$index] ?? null;



                    if (
                        $staffId !== null &&
                        $staffId !== '' &&
                        strtolower(trim((string) $staffId)) !== 'undefined' &&
                        strtolower(trim((string) $staffId)) !== 'null'
                    ) {
                        $staffId = (int) $staffId;
                    } else {
                        $staffId = null;
                    }


                    // ----------------------------------------------------------
                    // NORMALIZE ROW INDEX
                    // ----------------------------------------------------------

                    if (
                        $rowIndex !== null &&
                        $rowIndex !== '' &&
                        strtolower(trim((string) $rowIndex)) !== 'undefined' &&
                        strtolower(trim((string) $rowIndex)) !== 'null'
                    ) {
                        $rowIndex = (int) $rowIndex;
                    } else {
                        $rowIndex = null;
                    }


                    // ----------------------------------------------------------
                    // SKIP COMPLETELY EMPTY ROW
                    // ----------------------------------------------------------

                    if (
                        empty($category) &&
                        empty($ccNumber) &&
                        empty($firstIssue) &&
                        empty($validityFrom) &&
                        empty($validityTo) &&
                        empty($designation)
                    ) {
                        continue;
                    }


                    // ----------------------------------------------------------
                    // STAFF DATA
                    // ----------------------------------------------------------

                    $staffData = [

                        'application_id' =>
                        $applicationId,

                        'login_id' =>
                        $request->input('login_id_store'),

                        'staff_category' =>
                        $category,

                        'staff_cc_no' =>
                        strtoupper(trim((string) ($ccNumber ?? ''))),

                        'staff_cc_first_issue' =>
                        $firstIssue,

                        'staff_cc_validity_from' =>
                        $validityFrom,

                        'staff_cc_validity_to' =>
                        $validityTo,



                        'staff_name' =>
                        !empty($ccName)
                            ? trim((string) $ccName)
                            : null,



                        'staff_status' =>
                        'N',

                        'staff_flag' =>
                        '1',

                        'staff_designation' =>
                        !empty($designation)
                            ? trim((string) $designation)
                            : null,

                        'updated_at' =>
                        now(),
                    ];


                    // ----------------------------------------------------------
                    // OTHERS
                    // ----------------------------------------------------------

                    if ($category === 'OTHERS') {

                        $staffData['staff_designation'] =
                            trim((string) ($designation ?? ''));

                        $staffData['staff_cc_no'] = null;

                        $staffData['staff_cc_first_issue'] = null;

                        $staffData['staff_name'] = null;

                        $staffData['staff_cc_validity_from'] = null;

                        $staffData['staff_cc_validity_to'] = null;
                    }


                    // ----------------------------------------------------------
                    // EXISTING STAFF -> UPDATE
                    // ----------------------------------------------------------

                    if (
                        !empty($staffId) &&
                        in_array($staffId, $existingStaffIds, true)
                    ) {

                        // Existing row MUST retain its own row_index
                        $staffData['row_index'] = $rowIndex;

                        DB::table('cl_staff_tbl')
                            ->where('id', $staffId)
                            ->where('application_id', $applicationId)
                            ->whereIn(
                                'staff_category',
                                ['B', 'C', 'OTHERS']
                            )
                            ->update($staffData);

                        $processedStaffIds[] = $staffId;
                    }


                    // ----------------------------------------------------------
                    // NEW STAFF -> INSERT
                    // ----------------------------------------------------------

                    else {

                        $staffData['row_index'] = $nextRowIndex;

                        $staffData['staff_flag'] = '1';

                        $staffData['staff_status'] = 'NA';

                        $staffData['created_at'] = now();

                        $newStaffId = DB::table('cl_staff_tbl')
                            ->insertGetId($staffData);

                        $processedStaffIds[] = $newStaffId;

                        $nextRowIndex++;
                    }
                }
            }
        }




        // ==========================================
        // PROPRIETORS
        // ==========================================
        if (
            is_array($returnsection) &&
            in_array('ownership_details', $returnsection, true)
        ) {
            $newProprietorIds = [];

            if ($request->exists('proprietor_name')) {

                $count = 1;
                $newProprietorIds = [];

                // ==========================================
                // 1. GET PROPRIETOR NAMES SAFELY
                // ==========================================

                $proprietorNames = $request->input('proprietor_name', []);

                if (!is_array($proprietorNames)) {
                    $proprietorNames = [];
                }

                // Fallback: retrieve names from the proprietor JSON if needed
                if (
                    empty(array_filter($proprietorNames, function ($name) {
                        return trim((string) $name) !== '';
                    }))
                ) {
                    $proprietorJson = $request->input('proprietor', '[]');

                    $proprietorData = json_decode($proprietorJson, true);

                    if (is_array($proprietorData)) {
                        $proprietorNames = [];

                        foreach ($proprietorData as $index => $proprietorRow) {
                            $proprietorNames[$index] =
                                $proprietorRow['proprietor_name'] ?? '';
                        }
                    }
                }

                // ==========================================
                // 2. PROCESS PROPRIETOR ROWS
                // ==========================================

                foreach ($proprietorNames as $index => $name) {

                    // Skip empty proprietor rows
                    if (
                        empty(trim((string) $name)) ||
                        strtolower(trim((string) $name)) === 'undefined' ||
                        strtolower(trim((string) $name)) === 'null'
                    ) {
                        continue;
                    }

                    // ==========================================
                    // 3. GET CURRENT PROPRIETOR DATA
                    // ==========================================

                    $competencyHolding = data_get(
                        $request->competency,
                        $index
                    );

                    $proprietorId = data_get(
                        $request->proprietor_id,
                        $index
                    );

                    $rowIndex = $count;

                    // ==========================================
                    // 4. PREPARE PROPRIETOR DATA
                    // ==========================================

                    $data = [
                        'login_id' => $request->login_id_store,
                        'application_id' => $applicationId,

                        'proprietor_name' => strtoupper(trim($name)),
                        'ownership_type' => 'pr',

                        'proprietor_address' => strtoupper(
                            data_get(
                                $request->proprietor_address,
                                $index,
                                ''
                            )
                        ),

                        'dob' => data_get(
                            $request->dob,
                            $index
                        ),

                        'age' => data_get(
                            $request->age,
                            $index
                        ),

                        'qualification' => strtoupper(
                            data_get(
                                $request->qualification,
                                $index,
                                ''
                            )
                        ),

                        'qualification_text' => strtoupper(
                            data_get(
                                $request->qual_text,
                                $index,
                                ''
                            )
                        ),

                        'fathers_name' => strtoupper(
                            data_get(
                                $request->fathers_name,
                                $index,
                                ''
                            )
                        ),

                        'present_business' => strtoupper(
                            data_get(
                                $request->present_business,
                                $index,
                                ''
                            )
                        ),

                        // Preserve existing proprietor document fields
                        'educational_proof' => data_get(
                            $request->qual_proof,
                            $index
                        ),

                        'age_proof' => data_get(
                            $request->age_proof,
                            $index
                        ),

                        // Competency certificate
                        'competency_certificate_holding' => $competencyHolding,

                        'competency_certificate_number' =>
                        $competencyHolding === 'yes'
                            ? strtoupper(
                                data_get(
                                    $request->competency_certno,
                                    $index,
                                    ''
                                )
                            )
                            : null,

                        'competency_certificate_first_issue' =>
                        $competencyHolding === 'yes'
                            ? data_get(
                                $request->ccfirstissue,
                                $index
                            )
                            : null,

                        'competency_certificate_validity_from' =>
                        $competencyHolding === 'yes'
                            ? data_get(
                                $request->ccvalidityfrom,
                                $index
                            )
                            : null,

                        'competency_certificate_validity_to' =>
                        $competencyHolding === 'yes'
                            ? data_get(
                                $request->ccvalidityto,
                                $index
                            )
                            : null,

                        'proprietor_flag' => 1,
                        'ownership_count' => $rowIndex,
                        'row_index' => $rowIndex,
                    ];

                    // ==========================================
                    // 5. FIND EXISTING PROPRIETOR USING DATABASE ID
                    // ==========================================

                    $proprietor = null;

                    if (
                        !empty($proprietorId) &&
                        is_numeric($proprietorId)
                    ) {
                        $proprietor = ProprietorformA::where(
                            'id',
                            $proprietorId
                        )
                            ->where('application_id', $applicationId)
                            ->where('ownership_type', 'pr')
                            ->first();
                    }

                    // Fallback to existing record with the same row index
                    if (!$proprietor) {
                        $proprietor = ProprietorformA::where(
                            'application_id',
                            $applicationId
                        )
                            ->where('ownership_type', 'pr')
                            ->where('row_index', $rowIndex)
                            ->first();
                    }

                    // ==========================================
                    // 6. UPDATE EXISTING OR CREATE NEW PROPRIETOR
                    // ==========================================

                    if ($proprietor) {
                        $proprietor->update($data);
                    } else {
                        $proprietor = ProprietorformA::create($data);
                    }

                    // Save proprietor row information
                    $proprietor->row_index = $rowIndex;
                    $proprietor->ownership_count = $rowIndex;
                    $proprietor->proprietor_flag = 1;
                    $proprietor->save();

                    // Store actual database ID
                    $newProprietorIds[] = $proprietor->id;

                    // ==========================================
                    // 7. PROCESS TEMPORARY DOCUMENTS
                    // ==========================================

                    if (
                        $rowIndex >= 1 &&
                        (int) $proprietor->row_index === $rowIndex
                    ) {

                        $documentCategories = [
                            'educ_qual_proof' => 'educational_proof',
                            'age_proof'       => 'age_proof',
                        ];

                        foreach (
                            $documentCategories as $category => $proprietorColumn
                        ) {

                            $tempDocs = DB::table(
                                'tnelb_temp_uploaded_documents'
                            )
                                ->where(
                                    'login_id',
                                    $request->login_id_store
                                )
                                ->where(
                                    'form_name',
                                    $request->form_name
                                )
                                ->where(
                                    'application_id',
                                    $sessionId
                                )
                                ->where(
                                    'license_name',
                                    $request->license_name
                                )
                                ->where(
                                    'document_category',
                                    $category
                                )
                                ->where(
                                    'ownership_type',
                                    'pr'
                                )
                                ->where(
                                    'appl_type',
                                    trim(
                                        (string) $request->input(
                                            'appl_type',
                                            ''
                                        )
                                    )
                                )
                                ->where('row_index', $rowIndex)
                                ->get();

                            foreach ($tempDocs as $tempDoc) {

                                // Verify row index
                                if (
                                    !is_numeric($tempDoc->row_index) ||
                                    (int) $tempDoc->row_index !== $rowIndex ||
                                    (int) $proprietor->row_index !== $rowIndex
                                ) {
                                    continue;
                                }

                                // ==========================================
                                // 8. RESOLVE DESTINATION PATH
                                // ==========================================

                                $dynamicRequest = clone $request;

                                $dynamicRequest->merge([
                                    'module' => $tempDoc->module,
                                ]);

                                $pathData = DocPathController::getPath(
                                    $dynamicRequest
                                );

                                $destinationRelativePath =
                                    $pathData->filepath_pro;

                                $destinationFolder = public_path(
                                    rtrim(
                                        $destinationRelativePath,
                                        '/\\'
                                    ) . DIRECTORY_SEPARATOR
                                );

                                if (!File::exists($destinationFolder)) {
                                    File::makeDirectory(
                                        $destinationFolder,
                                        0755,
                                        true
                                    );
                                }

                                // ==========================================
                                // 9. CHECK TEMPORARY FILE
                                // ==========================================

                                $sourceRelativePath = rtrim(
                                    $tempDoc->file_path,
                                    '/\\'
                                ) . DIRECTORY_SEPARATOR . $tempDoc->file_name;

                                $tempFullPath = public_path(
                                    $sourceRelativePath
                                );

                                if (!File::exists($tempFullPath)) {
                                    \Log::warning(
                                        'Proprietor temporary document not found',
                                        [
                                            'temp_document_id' => $tempDoc->id,
                                            'row_index' => $rowIndex,
                                            'path' => $tempFullPath,
                                        ]
                                    );

                                    continue;
                                }

                                $destinationFullPath = $destinationFolder
                                    . $tempDoc->file_name;

                                // ==========================================
                                // 10. COPY FILE IF REQUIRED
                                // ==========================================

                                $sourceRealPath = realpath($tempFullPath);

                                $destinationRealPath = realpath(
                                    $destinationFullPath
                                );

                                $samePath = (
                                    $sourceRealPath !== false &&
                                    $destinationRealPath !== false &&
                                    $sourceRealPath === $destinationRealPath
                                );

                                if (
                                    !$samePath &&
                                    !File::exists($destinationFullPath)
                                ) {
                                    if (
                                        !File::copy(
                                            $tempFullPath,
                                            $destinationFullPath
                                        )
                                    ) {
                                        continue;
                                    }
                                }

                                if (!File::exists($destinationFullPath)) {
                                    continue;
                                }

                                // ==========================================
                                // 11. UPDATE PROPRIETOR DOCUMENT FIELD
                                // ==========================================

                                $proprietor->{$proprietorColumn} =
                                    rtrim(
                                        $destinationRelativePath,
                                        '/\\'
                                    ) . '/' . $tempDoc->file_name;

                                $proprietor->row_index = $rowIndex;
                                $proprietor->ownership_count = $rowIndex;

                                if (!$proprietor->save()) {
                                    continue;
                                }

                                // ==========================================
                                // 12. MARK TEMP DOCUMENT AS PROCESSED
                                // ==========================================

                                DB::table('tnelb_temp_uploaded_documents')
                                    ->where('id', $tempDoc->id)
                                    ->update([
                                        'is_final' => 1,

                                        'moved_as' => $request->input(
                                            'form_action'
                                        ),

                                        'record_id_app' => $applicationId,

                                        'updated_at' => now(),
                                    ]);
                            }
                        }
                    }

                    // ==========================================
                    // 13. INCREMENT ROW COUNT
                    // ==========================================

                    $count++;
                }

                // ==========================================
                // 14. DEACTIVATE REMOVED PROPRIETORS
                // ==========================================

                $deactivateQuery = ProprietorformA::where(
                    'application_id',
                    $applicationId
                )
                    ->where('ownership_type', 'pr')
                    ->where('proprietor_flag', 1);

                if (!empty($newProprietorIds)) {
                    $deactivateQuery->whereNotIn(
                        'id',
                        $newProprietorIds
                    );
                }

                $deactivateQuery->update([
                    'proprietor_flag' => 0,
                ]);
            }



            // Partners
            // dd($request->all()); exit;


            $newPartnerIds = [];

            if ($request->exists('partners')) {
                // dd('partners'); exit;
                $count = 1;
                $newPartnerIds = [];

                // ==========================================
                // 1. GET PARTNER NAMES SAFELY
                // ==========================================

                $partnerNames = $request->input('partner_name', []);

                if (!is_array($partnerNames)) {
                    $partnerNames = [];
                }

                // Fallback: retrieve names from the partners JSON if needed
                if (
                    empty(array_filter($partnerNames, function ($name) {
                        return trim((string) $name) !== '';
                    }))
                ) {
                    $partnersJson = $request->input('partners', '[]');

                    $partnersData = json_decode($partnersJson, true);

                    if (is_array($partnersData)) {
                        $partnerNames = [];

                        foreach ($partnersData as $index => $partnerRow) {
                            $partnerNames[$index] = $partnerRow['proprietor_name'] ?? '';
                        }
                    }
                }
                // dd($partnerNames); exit;
                if (!is_array($partnerNames)) {
                    $partnerNames = [];
                }

                // ==========================================
                // 2. PROCESS REMAINING PARTNER ROWS
                // ==========================================

                foreach ($partnerNames as $index => $name) {

                    // Skip empty partner rows
                    if (
                        empty($name) ||
                        strtolower(trim((string) $name)) === 'undefined' ||
                        strtolower(trim((string) $name)) === 'null'
                    ) {
                        continue;
                    }

                    // ==========================================
                    // 3. GET CURRENT PARTNER DATA
                    // ==========================================

                    $competencyHolding = data_get(
                        $request->partner_competency,
                        $index
                    );

                    $partnerId = data_get(
                        $request->partner_id,
                        $index
                    );

                    $rowIndex = $count;

                    // ==========================================
                    // 4. PREPARE PARTNER DATA
                    // ==========================================

                    $data = [
                        'login_id' => $request->login_id_store,
                        'application_id' => $applicationId,
                        'proprietor_name' => strtoupper(trim($name)),
                        'ownership_type' => 'pt',

                        'proprietor_address' => strtoupper(
                            data_get(
                                $request->partner_proprietor_address,
                                $index,
                                ''
                            )
                        ),

                        'dob' => data_get(
                            $request->partner_dob,
                            $index
                        ),

                        'age' => data_get(
                            $request->partner_age,
                            $index
                        ),

                        'qualification' => strtoupper(
                            data_get(
                                $request->partner_qualification,
                                $index,
                                ''
                            )
                        ),

                        'qualification_text' => strtoupper(
                            data_get(
                                $request->partner_qual_text,
                                $index,
                                ''
                            )
                        ),

                        'fathers_name' => strtoupper(
                            data_get(
                                $request->partner_fathers_name,
                                $index,
                                ''
                            )
                        ),

                        'present_business' => strtoupper(
                            data_get(
                                $request->partner_present_business,
                                $index,
                                ''
                            )
                        ),

                        'educational_proof' => data_get(
                            $request->partner_qual_proof,
                            $index
                        ),

                        'age_proof' => data_get(
                            $request->partner_age_proof,
                            $index
                        ),

                        // Competency certificate
                        'competency_certificate_holding' => $competencyHolding,

                        'competency_certificate_number' =>
                        $competencyHolding === 'yes'
                            ? strtoupper(
                                data_get(
                                    $request->partner_competency_certno,
                                    $index,
                                    ''
                                )
                            )
                            : null,

                        'competency_certificate_first_issue' =>
                        $competencyHolding === 'yes'
                            ? data_get(
                                $request->partner_ccfirstissue,
                                $index
                            )
                            : null,

                        'competency_certificate_validity_from' =>
                        $competencyHolding === 'yes'
                            ? data_get(
                                $request->partner_ccvalidityfrom,
                                $index
                            )
                            : null,

                        'competency_certificate_validity_to' =>
                        $competencyHolding === 'yes'
                            ? data_get(
                                $request->partner_ccvalidityto,
                                $index
                            )
                            : null,

                        'proprietor_flag' => 1,
                        'ownership_count' => $rowIndex,
                        'row_index' => $rowIndex,
                        'created_at' => now(),
                    ];


                    // dd($data); exit;

                    // ==========================================
                    // 5. FIND EXISTING PARTNER USING DATABASE ID
                    // ==========================================

                    $partner = null;

                    if (
                        !empty($partnerId) &&
                        is_numeric($partnerId)
                    ) {
                        $partner = ProprietorformA::where(
                            'id',
                            $partnerId
                        )
                            ->where('application_id', $applicationId)
                            ->where('ownership_type', 'pt')
                            ->first();
                    }

                    // ==========================================
                    // 6. UPDATE EXISTING OR CREATE NEW PARTNER
                    // ==========================================

                    if ($partner) {
                        $partner->update($data);
                    } else {
                        $partner = ProprietorformA::create($data);
                    }

                    // Save partner row information
                    $partner->row_index = $rowIndex;
                    $partner->ownership_count = $rowIndex;
                    $partner->proprietor_flag = 1;
                    $partner->save();

                    // Store actual database ID
                    $newPartnerIds[] = $partner->id;

                    // ==========================================
                    // 7. PROCESS TEMPORARY DOCUMENTS
                    // ==========================================

                    if (
                        $rowIndex >= 1 &&
                        (int) $partner->row_index === $rowIndex
                    ) {

                        $documentCategories = [
                            'educ_qual_proof' => 'educational_proof',
                            'age_proof'       => 'age_proof',
                        ];

                        foreach (
                            $documentCategories as $category => $partnerColumn
                        ) {

                            $tempDocs = DB::table(
                                'tnelb_temp_uploaded_documents'
                            )
                                ->where(
                                    'login_id',
                                    $request->login_id_store
                                )
                                ->where(
                                    'form_name',
                                    $request->form_name
                                )
                                ->where(
                                    'application_id',
                                    $sessionId
                                )
                                ->where(
                                    'license_name',
                                    $request->license_name
                                )
                                ->where(
                                    'document_category',
                                    $category
                                )
                                ->where(
                                    'ownership_type',
                                    'pt'
                                )
                                ->where(
                                    'appl_type',
                                    trim(
                                        (string) $request->input(
                                            'appl_type',
                                            ''
                                        )
                                    )
                                )
                                ->where('row_index', $rowIndex)
                                ->get();

                            foreach ($tempDocs as $tempDoc) {

                                // Verify row index
                                if (
                                    !is_numeric($tempDoc->row_index) ||
                                    (int) $tempDoc->row_index !== $rowIndex ||
                                    (int) $partner->row_index !== $rowIndex
                                ) {
                                    continue;
                                }

                                // ==========================================
                                // 8. RESOLVE DESTINATION PATH
                                // ==========================================

                                $dynamicRequest = clone $request;

                                $dynamicRequest->merge([
                                    'module' => $tempDoc->module,
                                ]);

                                $pathData = DocPathController::getPath(
                                    $dynamicRequest
                                );

                                $destinationRelativePath =
                                    $pathData->filepath_pro;

                                $destinationFolder = public_path(
                                    rtrim(
                                        $destinationRelativePath,
                                        '/\\'
                                    ) . DIRECTORY_SEPARATOR
                                );

                                if (!File::exists($destinationFolder)) {
                                    File::makeDirectory(
                                        $destinationFolder,
                                        0755,
                                        true
                                    );
                                }

                                // ==========================================
                                // 9. CHECK TEMPORARY FILE
                                // ==========================================

                                $sourceRelativePath = rtrim(
                                    $tempDoc->file_path,
                                    '/\\'
                                ) . DIRECTORY_SEPARATOR . $tempDoc->file_name;

                                $tempFullPath = public_path(
                                    $sourceRelativePath
                                );

                                if (!File::exists($tempFullPath)) {
                                    continue;
                                }

                                $destinationFullPath = $destinationFolder
                                    . $tempDoc->file_name;

                                // ==========================================
                                // 10. COPY FILE IF REQUIRED
                                // ==========================================

                                $sourceRealPath = realpath($tempFullPath);

                                $destinationRealPath = realpath(
                                    $destinationFullPath
                                );

                                $samePath = (
                                    $sourceRealPath !== false &&
                                    $destinationRealPath !== false &&
                                    $sourceRealPath === $destinationRealPath
                                );

                                if (
                                    !$samePath &&
                                    !File::exists($destinationFullPath)
                                ) {
                                    if (
                                        !File::copy(
                                            $tempFullPath,
                                            $destinationFullPath
                                        )
                                    ) {
                                        continue;
                                    }
                                }

                                if (!File::exists($destinationFullPath)) {
                                    continue;
                                }

                                // ==========================================
                                // 11. UPDATE PARTNER DOCUMENT FIELD
                                // ==========================================

                                $partner->{$partnerColumn} =
                                    rtrim(
                                        $destinationRelativePath,
                                        '/\\'
                                    ) . '/' . $tempDoc->file_name;

                                $partner->row_index = $rowIndex;

                                if (!$partner->save()) {
                                    continue;
                                }

                                // ==========================================
                                // 12. MARK TEMP DOCUMENT AS PROCESSED
                                // ==========================================

                                DB::table('tnelb_temp_uploaded_documents')
                                    ->where('id', $tempDoc->id)
                                    ->update([
                                        'is_final' => 1,

                                        'moved_as' => $request->input(
                                            'form_action'
                                        ),

                                        'application_id' => $applicationId,

                                        'updated_at' => now(),
                                    ]);
                            }
                        }
                    }

                    // ==========================================
                    // 13. INCREMENT ROW COUNT
                    // ==========================================

                    $count++;
                }

                // ==========================================
                // 14. DEACTIVATE REMOVED PARTNERS
                // ==========================================

                // $deactivateQuery = ProprietorformA::where(
                //     'application_id',
                //     $applicationId
                // )
                //     ->where('ownership_type', 'pt')
                //     ->where('proprietor_flag', 1);

                // if (!empty($newPartnerIds)) {
                //     $deactivateQuery->whereNotIn(
                //         'id',
                //         $newPartnerIds
                //     );
                // }

                // $deactivateQuery->update([
                //     'proprietor_flag' => 0,
                // ]);

                // Deactivate only existing active partners absent from this submission
                $deactivateQuery = ProprietorformA::where(
                    'application_id',
                    $applicationId
                )
                    ->where('ownership_type', 'pt')
                    ->where('proprietor_flag', 1);

                if (!empty($newPartnerIds)) {
                    $deactivateQuery->whereNotIn('id', $newPartnerIds);
                }

                $deactivateQuery->update([
                    'proprietor_flag' => 0,
                ]);
            }


            // ----------------director------------------------



            // ==========================================
            // DIRECTORS
            // ==========================================

            $newdirectorIds = [];

            if ($request->exists('directors')) {
                // dd('directors'); exit;
                $count = 1;
                $newdirectorIds = [];

                // ==========================================
                // 1. GET DIRECTOR NAMES SAFELY
                // ==========================================

                $directorNames = $request->input('director_name', []);

                if (!is_array($directorNames)) {
                    $directorNames = [];
                }

                // Fallback: retrieve names from the directors JSON if needed
                if (
                    empty(array_filter($directorNames, function ($name) {
                        return trim((string) $name) !== '';
                    }))
                ) {
                    $directorsJson = $request->input('directors', '[]');

                    $directorsData = json_decode($directorsJson, true);

                    if (is_array($directorsData)) {
                        $directorNames = [];

                        foreach ($directorsData as $index => $directorRow) {
                            $directorNames[$index] = $directorRow['proprietor_name'] ?? '';
                        }
                    }
                }

                // ==========================================
                // 2. PROCESS DIRECTOR ROWS
                // ==========================================

                foreach ($directorNames as $index => $name) {

                    // Skip empty director rows
                    if (
                        empty(trim((string) $name)) ||
                        strtolower(trim((string) $name)) === 'undefined' ||
                        strtolower(trim((string) $name)) === 'null'
                    ) {
                        continue;
                    }

                    // ==========================================
                    // 3. GET CURRENT DIRECTOR DATA
                    // ==========================================

                    $rowIndex = $count;

                    $directorId = data_get(
                        $request->director_id,
                        $index
                    );

                    $competencyHolding = data_get(
                        $request->director_competency,
                        $index,
                        'no'
                    );

                    // ==========================================
                    // 4. PREPARE DIRECTOR DATA
                    // ==========================================

                    $data = [
                        'login_id' => $request->login_id_store,
                        'application_id' => $applicationId,

                        'proprietor_name' => strtoupper(trim($name)),
                        'ownership_type' => 'dr',

                        'managing_director' => strtoupper(
                            data_get(
                                $request->director_managing_director,
                                $index,
                                'NO'
                            )
                        ),

                        'fathers_name' => strtoupper(
                            data_get(
                                $request->director_fathers_name,
                                $index,
                                ''
                            )
                        ),

                        'proprietor_address' => strtoupper(
                            data_get(
                                $request->director_proprietor_address,
                                $index,
                                ''
                            )
                        ),

                        'dob' => data_get(
                            $request->director_dob,
                            $index
                        ),

                        'age' => data_get(
                            $request->director_age,
                            $index
                        ),

                        'qualification' => strtoupper(
                            data_get(
                                $request->director_qualification,
                                $index,
                                ''
                            )
                        ),

                        'qualification_text' => strtoupper(
                            data_get(
                                $request->director_qual_text,
                                $index,
                                ''
                            )
                        ),

                        'present_business' => strtoupper(
                            data_get(
                                $request->director_present_business,
                                $index,
                                ''
                            )
                        ),

                        // Director educational and age proof fields
                        'educational_proof' => data_get(
                            $request->director_qual_proof,
                            $index
                        ),

                        'age_proof' => data_get(
                            $request->director_age_proof,
                            $index
                        ),

                        // Competency certificate
                        'competency_certificate_holding' => $competencyHolding,

                        'competency_certificate_number' =>
                        $competencyHolding === 'yes'
                            ? strtoupper(
                                data_get(
                                    $request->director_competency_certno,
                                    $index,
                                    ''
                                )
                            )
                            : null,

                        'competency_certificate_first_issue' =>
                        $competencyHolding === 'yes'
                            ? data_get(
                                $request->director_ccfirstissue,
                                $index
                            )
                            : null,

                        'competency_certificate_validity_from' =>
                        $competencyHolding === 'yes'
                            ? data_get(
                                $request->director_ccvalidityfrom,
                                $index
                            )
                            : null,

                        'competency_certificate_validity_to' =>
                        $competencyHolding === 'yes'
                            ? data_get(
                                $request->director_ccvalidityto,
                                $index
                            )
                            : null,

                        'proprietor_flag' => 1,
                        'ownership_count' => $rowIndex,
                        'row_index' => $rowIndex,
                    ];

                    // ==========================================
                    // 5. FIND EXISTING DIRECTOR USING DATABASE ID
                    // ==========================================

                    $director = null;

                    if (
                        !empty($directorId) &&
                        is_numeric($directorId)
                    ) {
                        $director = ProprietorformA::where(
                            'id',
                            $directorId
                        )
                            ->where('application_id', $applicationId)
                            ->where('ownership_type', 'dr')
                            ->first();
                    }

                    // Fallback to existing director with the same row index
                    if (!$director) {
                        $director = ProprietorformA::where(
                            'application_id',
                            $applicationId
                        )
                            ->where('ownership_type', 'dr')
                            ->where('row_index', $rowIndex)
                            ->first();
                    }

                    // ==========================================
                    // 6. UPDATE EXISTING OR CREATE NEW DIRECTOR
                    // ==========================================

                    if ($director) {

                        // Do not overwrite original creation timestamp
                        $director->update($data);
                    } else {

                        $data['created_at'] = now();

                        $director = ProprietorformA::create($data);
                    }

                    // Save director row information
                    $director->row_index = $rowIndex;
                    $director->ownership_count = $rowIndex;
                    $director->proprietor_flag = 1;
                    $director->save();

                    // Store actual database ID
                    $newdirectorIds[] = $director->id;

                    // ==========================================
                    // 7. PROCESS TEMPORARY DOCUMENTS
                    // ==========================================

                    if (
                        $rowIndex >= 1 &&
                        (int) $director->row_index === $rowIndex
                    ) {

                        $documentCategories = [
                            'educ_qual_proof' => 'educational_proof',
                            'age_proof'       => 'age_proof',
                        ];

                        foreach (
                            $documentCategories as $category => $directorColumn
                        ) {

                            $tempDocs = DB::table(
                                'tnelb_temp_uploaded_documents'
                            )
                                ->where(
                                    'login_id',
                                    $request->login_id_store
                                )
                                ->where(
                                    'form_name',
                                    $request->form_name
                                )
                                ->where(
                                    'application_id',
                                    $sessionId
                                )
                                ->where(
                                    'license_name',
                                    $request->license_name
                                )
                                ->where(
                                    'document_category',
                                    $category
                                )
                                ->where(
                                    'ownership_type',
                                    'dr'
                                )
                                ->where(
                                    'appl_type',
                                    trim(
                                        (string) $request->input(
                                            'appl_type',
                                            ''
                                        )
                                    )
                                )
                                ->where('row_index', $rowIndex)
                                ->get();

                            foreach ($tempDocs as $tempDoc) {

                                // Verify row index
                                if (
                                    !is_numeric($tempDoc->row_index) ||
                                    (int) $tempDoc->row_index !== $rowIndex ||
                                    (int) $director->row_index !== $rowIndex
                                ) {
                                    continue;
                                }

                                // ==========================================
                                // 8. RESOLVE DESTINATION PATH
                                // ==========================================

                                $dynamicRequest = clone $request;

                                $dynamicRequest->merge([
                                    'module' => $tempDoc->module,
                                ]);

                                $pathData = DocPathController::getPath(
                                    $dynamicRequest
                                );

                                $destinationRelativePath =
                                    $pathData->filepath_pro;

                                $destinationFolder = public_path(
                                    rtrim(
                                        $destinationRelativePath,
                                        '/\\'
                                    ) . DIRECTORY_SEPARATOR
                                );

                                if (!File::exists($destinationFolder)) {

                                    File::makeDirectory(
                                        $destinationFolder,
                                        0755,
                                        true
                                    );
                                }

                                // ==========================================
                                // 9. CHECK TEMPORARY FILE
                                // ==========================================

                                $sourceRelativePath = rtrim(
                                    $tempDoc->file_path,
                                    '/\\'
                                ) . DIRECTORY_SEPARATOR . $tempDoc->file_name;

                                $tempFullPath = public_path(
                                    $sourceRelativePath
                                );

                                if (!File::exists($tempFullPath)) {

                                    \Log::warning(
                                        'Director temporary document not found',
                                        [
                                            'temp_document_id' => $tempDoc->id,
                                            'row_index' => $rowIndex,
                                            'path' => $tempFullPath,
                                        ]
                                    );

                                    continue;
                                }

                                $destinationFullPath = $destinationFolder
                                    . $tempDoc->file_name;

                                // ==========================================
                                // 10. COPY FILE IF REQUIRED
                                // ==========================================

                                $sourceRealPath = realpath($tempFullPath);

                                $destinationRealPath = realpath(
                                    $destinationFullPath
                                );

                                $samePath = (
                                    $sourceRealPath !== false &&
                                    $destinationRealPath !== false &&
                                    $sourceRealPath === $destinationRealPath
                                );

                                if (
                                    !$samePath &&
                                    !File::exists($destinationFullPath)
                                ) {

                                    if (
                                        !File::copy(
                                            $tempFullPath,
                                            $destinationFullPath
                                        )
                                    ) {
                                        continue;
                                    }
                                }

                                if (!File::exists($destinationFullPath)) {
                                    continue;
                                }

                                // ==========================================
                                // 11. UPDATE DIRECTOR DOCUMENT FIELD
                                // ==========================================

                                $director->{$directorColumn} =
                                    rtrim(
                                        $destinationRelativePath,
                                        '/\\'
                                    ) . '/' . $tempDoc->file_name;

                                $director->row_index = $rowIndex;
                                $director->ownership_count = $rowIndex;

                                if (!$director->save()) {
                                    continue;
                                }

                                // ==========================================
                                // 12. MARK TEMP DOCUMENT AS PROCESSED
                                // ==========================================

                                DB::table('tnelb_temp_uploaded_documents')
                                    ->where('id', $tempDoc->id)
                                    ->update([
                                        'is_final' => 1,

                                        'moved_as' => $request->input(
                                            'form_action'
                                        ),

                                        'record_id_app' => $applicationId,

                                        'updated_at' => now(),
                                    ]);
                            }
                        }
                    }

                    // ==========================================
                    // 13. INCREMENT ROW COUNT
                    // ==========================================

                    $count++;
                }

                // ==========================================
                // 14. DEACTIVATE REMOVED DIRECTORS
                // ==========================================

                $deactivateQuery = ProprietorformA::where(
                    'application_id',
                    $applicationId
                )
                    ->where('ownership_type', 'dr')
                    ->where('proprietor_flag', 1);

                if (!empty($newdirectorIds)) {
                    $deactivateQuery->whereNotIn('id', $newdirectorIds);
                }

                $deactivateQuery->update([
                    'proprietor_flag' => 0,
                ]);
            }
        }

        // ==========================================================
        // 8. SIGNATORY / AUTHORITY
        // ==========================================================

        $authorityNames = $request->input(
            'name_of_authorised_to_sign',
            []
        );

        $authorityAges = $request->input(
            'age_of_authorised_to_sign',
            []
        );

        $authorityQualifications = $request->input(
            'qualification_of_authorised_to_sign',
            []
        );

        $authorityDesignations = $request->input(
            'designation_of_authorised_to_sign',
            []
        );


        // ==========================================================
        // GET CURRENT MAX ROW INDEX
        // ==========================================================

        $lastAuthorityRowIndex = DB::table('cl_forma_signs')
            ->where(
                'application_id',
                $applicationId
            )
            ->max('row_index');

        $nextAuthorityRowIndex =
            ((int) $lastAuthorityRowIndex) + 1;


        // ==========================================================
        // GET TEMPORARY SPECIMEN SIGN DOCUMENTS
        // ==========================================================

        $tempSpecimenSigns = DB::table(
            'tnelb_temp_uploaded_documents'
        )
            ->where(
                'login_id',
                $request->input('login_id_store')
            )
            ->where(
                'form_name',
                $request->input('form_name')
            )
            ->where('application_id', $sessionId)
            ->where(
                'license_name',
                $request->input('license_name')
            )
            ->where(
                'document_category',
                'specimen_sign'
            )->where(
                'appl_type',
                trim(
                    (string) $request->input(
                        'appl_type'
                    )
                )
            )->get()
            ->keyBy('row_index');


        // ==========================================================
        // CURRENT ROW INDEXES
        // ==========================================================

        $currentAuthorityRowIndexes = [];


        // ==========================================================
        // PROCESS AUTHORITY / SIGNATORY
        // ==========================================================

        foreach (
            $authorityNames as $index => $name
        ) {

            // ------------------------------------------------------
            // NAME
            // ------------------------------------------------------

            $name = trim(
                (string) $name
            );


            // ------------------------------------------------------
            // IGNORE COMPLETELY EMPTY ROW
            // ------------------------------------------------------

            if (
                $name === '' ||
                strtolower($name) === 'undefined' ||
                strtolower($name) === 'null'
            ) {
                continue;
            }


            // ------------------------------------------------------
            // AGE
            // ------------------------------------------------------

            $age = trim(
                (string) (
                    $authorityAges[$index] ?? ''
                )
            );


            // ------------------------------------------------------
            // QUALIFICATION
            // ------------------------------------------------------

            $qualification = trim(
                (string) (
                    $authorityQualifications[$index] ?? ''
                )
            );


            // ------------------------------------------------------
            // DESIGNATION
            // ------------------------------------------------------

            $designation = trim(
                (string) (
                    $authorityDesignations[$index] ?? ''
                )
            );


            // ======================================================
            // ROW INDEX
            // ======================================================

            $submittedRowIndex = $index + 1;


            if (
                $submittedRowIndex !== null &&
                $submittedRowIndex > 0
            ) {

                $rowIndex =
                    (int) $submittedRowIndex;
            } else {

                $rowIndex =
                    $nextAuthorityRowIndex;
            }


            // ======================================================
            // GET TEMP SPECIMEN SIGN
            // USING SAME ROW INDEX
            // ======================================================

            $specimenSign = null;

            $tempDoc = null;


            if (
                $tempSpecimenSigns->has(
                    $rowIndex
                )
            ) {

                $tempDoc =
                    $tempSpecimenSigns->get(
                        $rowIndex
                    );


                // --------------------------------------------------
                // FILE PATH + FILE NAME
                // --------------------------------------------------

                $tempFilePath =
                    trim(
                        (string) (
                            $tempDoc->file_path ?? ''
                        )
                    );


                $tempFileName =
                    trim(
                        (string) (
                            $tempDoc->file_name ?? ''
                        )
                    );


                // --------------------------------------------------
                // CREATE FINAL DATABASE PATH
                // --------------------------------------------------

                if (
                    $tempFilePath !== '' &&
                    $tempFileName !== ''
                ) {

                    $specimenSign =
                        rtrim(
                            $tempFilePath,
                            '/'
                        )
                        . '/'
                        .
                        ltrim(
                            $tempFileName,
                            '/'
                        );
                }
            }


            // ======================================================
            // CHECK EXISTING AUTHORITY RECORD
            // ======================================================

            $existingAuthority = DB::table(
                'cl_forma_signs'
            )
                ->where(
                    'application_id',
                    $applicationId
                )
                ->where(
                    'row_index',
                    $rowIndex
                )
                ->first();


            // ======================================================
            // UPDATE EXISTING RECORD
            // ======================================================

            if ($existingAuthority) {


                // --------------------------------------------------
                // UPDATE DATA
                // --------------------------------------------------

                $updateData = [

                    'login_id' =>
                    $request->input(
                        'login_id_store'
                    ),

                    'form_name' =>
                    $request->input(
                        'form_name'
                    ),

                    'cert_name' =>
                    $request->input(
                        'cert_name'
                    ),

                    'form_code' =>
                    $request->input(
                        'form_code'
                    ),

                    'name_of_authorised_to_sign' =>
                    $name,

                    'age_of_authorised_to_sign' =>
                    $age,

                    'qualification_of_authorised_to_sign' =>
                    $qualification,

                    'designation_of_authorised_to_sign' =>
                    $designation,

                    'row_index' =>
                    $rowIndex,

                    'flag' =>
                    1,

                    'updated_at' =>
                    now(),
                ];


                // --------------------------------------------------
                // UPDATE SPECIMEN SIGN ONLY WHEN NEW FILE EXISTS
                // --------------------------------------------------

                if (
                    !empty($specimenSign)
                ) {

                    $updateData['specimen_sign'] = $specimenSign;
                }


                // --------------------------------------------------
                // UPDATE DATABASE
                // --------------------------------------------------

                DB::table(
                    'cl_forma_signs'
                )
                    ->where(
                        'id',
                        $existingAuthority->id
                    )
                    ->where(
                        'application_id',
                        $applicationId
                    )
                    ->update(
                        $updateData
                    );


                // --------------------------------------------------
                // CURRENT ROW
                // --------------------------------------------------

                $currentAuthorityRowIndexes[] =
                    $rowIndex;
            } else {


                // ==================================================
                // INSERT NEW RECORD
                // ==================================================

                DB::table(
                    'cl_forma_signs'
                )
                    ->insert([

                        'login_id' =>
                        $request->input(
                            'login_id_store'
                        ),

                        'application_id' =>
                        $applicationId,

                        'form_name' =>
                        $request->input(
                            'form_name'
                        ),

                        'cert_name' =>
                        $request->input(
                            'cert_name'
                        ),

                        'form_code' =>
                        $request->input(
                            'form_code'
                        ),

                        'name_of_authorised_to_sign' =>
                        $name,

                        'age_of_authorised_to_sign' =>
                        $age,

                        'qualification_of_authorised_to_sign' =>
                        $qualification,

                        'designation_of_authorised_to_sign' =>
                        $designation,

                        // =========================================
                        // SPECIMEN SIGN
                        // =========================================

                        'specimen_sign' =>
                        $specimenSign,

                        'row_index' =>
                        $rowIndex,

                        'flag' =>
                        1,

                        'created_at' =>
                        now(),

                        'updated_at' =>
                        now(),
                    ]);


                // --------------------------------------------------
                // CURRENT ROW
                // --------------------------------------------------

                $currentAuthorityRowIndexes[] =
                    $rowIndex;


                // --------------------------------------------------
                // UPDATE NEXT ROW INDEX
                // --------------------------------------------------

                if (
                    $rowIndex >=
                    $nextAuthorityRowIndex
                ) {

                    $nextAuthorityRowIndex =
                        $rowIndex + 1;
                }
            }


            // ======================================================
            // MARK TEMP SPECIMEN SIGN AS FINAL
            // ======================================================

            if (
                $tempDoc !== null
            ) {

                DB::table(
                    'tnelb_temp_uploaded_documents'
                )
                    ->where(
                        'id',
                        $tempDoc->id
                    )
                    ->update([

                        'is_final' =>
                        '1',

                        'moved_as' =>
                        $request->input(
                            'form_action'
                        ),

                        'record_id_app' =>
                        $applicationId,

                        'updated_at' =>
                        now(),
                    ]);
            }
        }


        // ==========================================================
        // OPTIONAL: HANDLE DELETED / REMOVED SIGNATORY ROWS
        // ==========================================================
        //
        // Any existing cl_forma_signs row which is not submitted
        // in the current form will be set to flag = 0.
        //
        // ==========================================================

        if (
            !empty($currentAuthorityRowIndexes)
        ) {

            DB::table(
                'cl_forma_signs'
            )
                ->where(
                    'application_id',
                    $applicationId
                )
                ->whereNotIn(
                    'row_index',
                    $currentAuthorityRowIndexes
                )
                ->update([

                    'flag' =>
                    0,

                    'updated_at' =>
                    now(),
                ]);
        } else {

            DB::table(
                'cl_forma_signs'
            )
                ->where(
                    'application_id',
                    $applicationId
                )
                ->update([

                    'flag' =>
                    0,

                    'updated_at' =>
                    now(),
                ]);
        }


        if ($existing) {

            $updateData = collect($dataToSave)
                ->except(['aadhaar_doc', 'pancard_doc', 'gst_doc'])
                ->toArray();

            // dd('111'); exit;

            EA_Application_model::where('application_id', $existing->application_id)
                ->update($updateData);
        } else {
            $dataToSave['created_at'] = $this->dbNow;

            $createData = collect($dataToSave)
                ->except(['aadhaar_doc', 'pancard_doc', 'gst_doc'])
                ->toArray();

            EA_Application_model::create($createData);
            $message = $isDraft ? 'Draft saved successfully!' : 'Application submitted successfully!';
        }

        // ------------Temp Table move------------------------------------------
        $sessionId = session('application_id', session()->getId());

        DB::transaction(function () use ($applicationId, $request, $sessionId) {

            // =======================================================
            // GET PERMANENT FILE PATH AND CREATE DIRECTORY
            // =======================================================

            $pathData = DocPathController::getPath($request);

            $relativeProPath = trim($pathData->filepath_pro, '/\\');
            $proFolderPath = public_path($relativeProPath);

            if (!File::exists($proFolderPath)) {
                File::makeDirectory($proFolderPath, 0755, true);
            }

            // =======================================================
            // HELPER: COPY TEMP FILE TO PERMANENT DIRECTORY
            // =======================================================

            $copyTempDocument = function ($doc) use ($proFolderPath, $relativeProPath) {

                $tempRelativePath = trim($doc->file_path, '/\\');

                $tempFullPath = public_path(
                    $tempRelativePath . DIRECTORY_SEPARATOR . $doc->file_name
                );

                $proFullPath = $proFolderPath . DIRECTORY_SEPARATOR . $doc->file_name;

                if (File::exists($tempFullPath)) {

                    // Avoid deleting/copying the file onto itself.
                    if (realpath($tempFullPath) !== realpath($proFullPath)) {

                        if (File::exists($proFullPath)) {
                            File::delete($proFullPath);
                        }

                        if (!File::copy($tempFullPath, $proFullPath)) {
                            throw new \RuntimeException(
                                'Failed to copy document: ' . $doc->file_name
                            );
                        }
                    }
                } elseif (!File::exists($proFullPath)) {

                    throw new \RuntimeException(
                        'Document file not found: ' . $doc->file_name
                    );
                }

                return $relativeProPath . '/' . $doc->file_name;
            };

            // =======================================================
            // HELPER: MARK TEMP DOCUMENT AS PROCESSED
            // =======================================================

            $markTempDocument = function ($doc) use ($sessionId, $applicationId, $request) {

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $doc->id)
                    ->where('application_id', $sessionId)
                    ->update([
                        'is_final'      => '1',
                        'moved_as'      => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at'    => $this->dbNow,
                    ]);
            };

            // Common conditions for temporary documents.
            $applType = trim((string) $request->input('appl_type'));

            // =======================================================
            // 1. OWNERSHIP DOCUMENT
            // =======================================================

            $doc = DB::table('tnelb_temp_uploaded_documents')
                ->where('application_id', $sessionId)
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'ownership_doc')
                ->whereIn('is_final', ['0', '2', '1'])
                ->where('appl_type', $applType)
                ->latest('id')
                ->first();

            if ($doc) {

                $finalPath = $copyTempDocument($doc);

                DB::table('ccl_forma_meta')->updateOrInsert(
                    [
                        'application_id' => $applicationId,
                    ],
                    [
                        'ownership_doc' => $finalPath,
                        'updated_at'    => $this->dbNow,
                    ]
                );

                $markTempDocument($doc);
            }

            // =======================================================
            // 2. BANK SOLVENCY DOCUMENT
            // =======================================================

            $doc = DB::table('tnelb_temp_uploaded_documents')
                ->where('application_id', $sessionId)
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'bank_doc')
                ->whereIn('is_final', ['0', '2', '1'])
                ->where('appl_type', $applType)
                ->latest('id')
                ->first();

            if ($doc) {

                $finalPath = $copyTempDocument($doc);

                DB::table('tnelb_banksolvency_a')->updateOrInsert(
                    [
                        'application_id' => $applicationId,
                    ],
                    [
                        'login_id'     => $request->login_id_store,
                        'form_name'    => $request->form_name,
                        'license_name' => $request->license_name,
                        'bank_doc'     => $finalPath,
                        'updated_at'   => $this->dbNow,
                        'status'       => '1',
                    ]
                );

                $markTempDocument($doc);
            }

            // =======================================================
            // 3. ADDRESS PROOF
            // =======================================================

            $doc = DB::table('tnelb_temp_uploaded_documents')
                ->where('application_id', $sessionId)
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'Address_proof')
                ->whereIn('is_final', ['0', '2', '1'])
                ->where('appl_type', $applType)
                ->latest('id')
                ->first();

            if ($doc) {

                $finalPath = $copyTempDocument($doc);

                DB::table('tnelb_addressproof_cl')->updateOrInsert(
                    [
                        'application_id' => $applicationId,
                    ],
                    [
                        'login_id'     => $request->login_id_store,
                        'form_name'    => $request->form_name,
                        'license_name' => $request->license_name,
                        'file_doc'     => $finalPath,
                        'updated_at'   => $this->dbNow,
                    ]
                );

                $markTempDocument($doc);
            }

            // =======================================================
            // 4. OTHER DOCUMENTS
            // =======================================================

            $otherDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('application_id', $sessionId)
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'other_doc')
                // ->whereIn('is_final', ['0', '2', '1'])
                ->where('appl_type', $applType)
                ->get();

            foreach ($otherDocs as $doc) {

                $finalPath = $copyTempDocument($doc);

                DB::table('tnelb_attachments_cl')->updateOrInsert(
                    [
                        'application_id' => $applicationId,
                        'type'           => $doc->ownership_type,
                    ],
                    [
                        'login_id'     => $request->login_id_store,
                        'form_name'    => $request->form_name,
                        'license_name' => $request->license_name,
                        'file_doc'     => $finalPath,
                        'updated_at'   => $this->dbNow,
                        'created_at'   => $this->dbNow,
                    ]
                );

                $markTempDocument($doc);
            }

            $recordIdold = $request->input('record_id');
            // Determine if record exists
            $applicationIdold = null;
            $dataold = null;

            if ($recordIdold) {
                $dataold = EA_Application_model::where('application_id', $recordIdold)->first();
                // dd($existing->application_id); exit;
                //    $applicationIdold = $dataold->application_id;
            }

            $returnsection = json_decode($dataold->return_reason, true);

            // =======================================================
            // 5. EQUIPMENT DOCUMENTS
            // =======================================================
            if (
                is_array($returnsection) &&
                in_array('equipments_details', $returnsection, true)
            ) {
                $allEquipmentDocs = DB::table('tnelb_temp_uploaded_documents')
                    ->where('application_id', $sessionId)
                    ->where('login_id', $request->login_id_store)
                    ->where('module', 'EQUIPMENTS DOCUMENT')
                    ->where('document_sub_category', 'ED')
                    ->whereIn('is_final', ['0', '2', '1'])
                    ->where('appl_type', $applType)
                    ->get()
                    ->groupBy('equip_code');

                foreach ($request->input('equipments', []) as $index => $equipment) {

                    $equipmentId = $equipment['equip_id'] ?? null;

                    $serialNo = $request->serial_no[$index] ?? null;
                    $modelNo = $request->model[$index] ?? null;
                    $dateOfTest = $request->date_of_test[$index] ?? null;
                    $licenceId = $equipment['licence_id'] ?? null;

                    if (
                        empty($equipmentId) &&
                        empty($serialNo) &&
                        empty($modelNo)
                    ) {
                        continue;
                    }

                    if (empty($equipmentId)) {
                        continue;
                    }

                    // Preserve existing files if no new temporary document exists.
                    $existingEquipment = DB::table('tnelb_equimentsuser_cl')
                        ->where('application_id', $applicationId)
                        ->where('equipment_id', $equipmentId)
                        ->first();

                    $testReportPath = $existingEquipment->testreport_file ?? null;
                    $purchaseReportPath = $existingEquipment->purchasereport_file ?? null;

                    $equipmentDocs = $allEquipmentDocs[$equipmentId] ?? collect();

                    // ---------------------------------------------------
                    // TEST REPORT
                    // ---------------------------------------------------

                    $testDoc = $equipmentDocs
                        ->where('document_category', 'instrument_test_report')
                        ->sortByDesc('id')
                        ->first();

                    if ($testDoc) {

                        $testReportPath = $copyTempDocument($testDoc);

                        $markTempDocument($testDoc);
                    }

                    // ---------------------------------------------------
                    // PURCHASE REPORT
                    // ---------------------------------------------------

                    $purchaseDoc = $equipmentDocs
                        ->where('document_category', 'instrument_purchase_report')
                        ->sortByDesc('id')
                        ->first();

                    if ($purchaseDoc) {

                        $purchaseReportPath = $copyTempDocument($purchaseDoc);

                        $markTempDocument($purchaseDoc);
                    }

                    // ---------------------------------------------------
                    // INSERT OR UPDATE EQUIPMENT RECORD
                    // ---------------------------------------------------

                    DB::table('tnelb_equimentsuser_cl')->updateOrInsert(
                        [
                            'application_id' => $applicationId,
                            'equipment_id'   => $equipmentId,
                        ],
                        [
                            'login_id'            => $request->login_id_store,
                            'form_name'           => $request->form_name,
                            'license_name'        => $request->license_name,
                            'licence_id'          => $licenceId,
                            'serial_no'           => $serialNo,
                            'model_no'            => $modelNo,
                            'testreport_file'     => $testReportPath,
                            'purchasereport_file' => $purchaseReportPath,
                            'dateoftest'          => $dateOfTest,
                            'ipaddress'           => $request->ip(),
                            'updated_at'          => $this->dbNow,
                            'created_at'          => $this->dbNow,
                        ]
                    );
                }
            }
        });

        if (!$isDraft) {

            // dd('111');exit;

            DB::table('ccl_forma_meta')
                ->where('application_id', $applicationId)
                ->update([
                    'processed_by'   => null,
                    'return_submit' => DB::raw('NOW()')
                ]);

            $supervisorRoleId = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->value('roles_id');

            WorkflowA::create([
                'application_id' => $applicationId,
                'appl_status'    => 'RE',
                'processed_by'   => 'AP',
                'forwarded_to'   => $supervisorRoleId,
                'role_id'        => $supervisorRoleId,
                'is_verified'    => 'Yes',
                'query_status'   => null,
                'remarks'        => 'Resubmitted by applicant after query.',
                'queries'        => null,
                'raised_by'      => null,
            ]);

            WorkflowA::where('application_id', $applicationId)
                ->where('appl_status', 'RE')
                ->where('role_id', $supervisorRoleId,)
                ->orderByDesc('id')
                ->limit(1)
                ->update([
                    'created_at' => DB::raw('NOW()'),
                    'updated_at' => DB::raw('NOW()'),
                ]);


            return response()->json([
                'draft_status' => $isDraft,
                'actionType' => $request->input('form_action'),
                'message' => 'Application Submitted!',
                'login_id' => $applicationId,
                'transaction_id' => '',
            ]);
        }

        //  DB::table('ccl_forma_meta')
        //         ->where('application_id', $applicationId)
        //          ->update([
        //                     'payment_status'   => $request->input('form_action'),

        //                 ]);

        // dd('1111');exit;
        DB::table('ccl_forma_meta')
            ->where('application_id', $applicationId)
            ->update([
                'application_status'   => 'RETD',

            ]);
        return response()->json([
            'message' => 'Draft',
            'actionType' => $request->input('form_action'),
            'login_id' => $applicationId,
            'transaction_id' => '',
            'draft_status' => $isDraft
        ]);
    }
    // ---------------------form ea   -----------------------------------------------------------------
    public function storereturn_bk(Request $request)
    {

        // dd($request->type_doc);
        // exit;

        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);
        $isDraft = $request->input('form_action') === 'draft';
        $recordId = trim((string) $request->input('record_id', ''));
        // dd($request->input('form_action'));
        // dd($request->all());
        // exit;
        // Format date fields
        $this->formatDatesToDMY([
            // 'bank_validity',
            // 'cc_validity',
            // 'competency_certificate_validity',
            // 'previous_experience_lnumber_validity'
        ], $request);

        if ($isDraft) {
            // Draft mode: minimal required fields, rest nullable
            $rules = [
                'applicant_name' => 'required|string|max:255',
                'business_address' => 'required|string|max:500',
                'form_name' => 'required|string|max:255',
                'license_name' => 'required|string|max:255',
                'appl_type' => 'required',

                'application_ownershiptype' => 'nullable|string',

                // Optional fields in draft mode
                'authorised_name_designation' => 'nullable|string|max:255',
                'authorised_name' => 'nullable|string|max:255',
                'authorised_designation' => 'nullable|string|max:255',
                'previous_contractor_license' => 'nullable|string|max:10',
                'previous_application_number' => 'nullable|string|max:50',
                'previous_application_validity' => 'nullable',
                'bank_address' => 'nullable|string|max:500',
                'bank_validity' => 'nullable|date',
                'bank_amount' => 'nullable|numeric|min:0',

                'previous_contractor_license_verify' => 'nullable|numeric',
                'criminal_offence' => 'nullable|string|in:yes,no',
                'consent_letter_enclose' => 'nullable|string|in:yes,no',
                'cc_holders_enclosed' => 'nullable|string|in:yes,no',
                'purchase_bill_enclose' => 'nullable|string|in:yes,no',
                'test_reports_enclose' => 'nullable|string|in:yes,no',
                'specimen_signature_enclose' => 'nullable|string|in:yes,no',
                'separate_sheet' => 'nullable|string|in:yes,no',



                'declaration1' => 'nullable|string|max:255',
                'declaration2' => 'nullable|string|max:255',
                // 'aadhaar_doc' => 'nullable|file|max:2048',
                // 'pancard_doc' => 'nullable|file|max:2048',
                // 'gst_doc' => 'nullable|file|max:2048',
            ];
        } else {
            // Final submission: all required fields
            $rules = [
                'applicant_name' => 'required|string|max:255',
                'business_address' => 'required|string|max:500',

                'application_ownershiptype' => 'required|string',

                'previous_contractor_license' => 'required|string|max:10',
                'previous_application_number' => 'nullable|string|max:50',
                'previous_validity_first_issue' => 'nullable',
                'previous_validity_from' => 'nullable',
                'previous_validity_to' => 'nullable',

                'bank_address' => 'required|string|max:500',
                'bank_validity' => 'required|date',
                'bank_amount' => 'required|numeric|min:0',

                'criminal_offence' => ['required', 'string', Rule::in(['yes', 'no'])],

                'form_name' => 'required|string|max:255',
                'license_name' => 'required|string|max:255',

                'declaration1' => 'required|string|max:255',
                'declaration2' => 'required|string|max:255',
                'address_proof' => $request->hasFile('address_proof') ? 'required|file|max:2048' : 'nullable|string',



            ];
        }
        // dd($request->all());
        // dd($request->appl_type);
        // exit;
        $validatedData = $request->validate($rules);

        // $validatedData['name_of_authorised_to_sign'] = !empty($request->name_of_authorised_to_sign)
        //     ? json_encode($request->name_of_authorised_to_sign)
        //     : null;

        // $validatedData['age_of_authorised_to_sign'] = !empty($request->age_of_authorised_to_sign)
        //     ? json_encode($request->age_of_authorised_to_sign)
        //     : null;

        // $validatedData['qualification_of_authorised_to_sign'] = !empty($request->qualification_of_authorised_to_sign)
        //     ? json_encode($request->qualification_of_authorised_to_sign)
        //     : null;

        // // Convert to uppercase for certain fields
        // foreach (
        //     [
        //         'applicant_name',
        //         'business_address',
        //         'authorised_name',
        //         'authorised_designation',
        //         'bank_address',
        //         'form_name',
        //         'license_name',

        //     ] as $field
        // ) {
        //     if (!empty($validatedData[$field])) {
        //         $validatedData[$field] = strtoupper($validatedData[$field]);
        //     }
        // }




        // Determine if record exists
        $applicationId = null;
        $existing = null;

        if ($recordId) {
            $existing = EA_Application_model::where('application_id', $recordId)->first();

            dd($existing); exit;
            if ($existing) {
                $applicationId = $existing->application_id;
            }
        }
        // dd($applicationId); exit;
        if (!$applicationId) {
            $applicationId = $this->generateApplicationId(
                $request->appl_type,
                $request->form_name,
                $request->license_name
            );
        }


        DB::table('mapping_digi_cls')
            ->where('temp_app_id', $request->temp_app_id)
            ->update([
                'application_id' => $applicationId,
                'updated_at' => now(),
            ]);

        // Final data to save
        $dataToSave = $validatedData;
        $dataToSave['application_id'] = $applicationId;
        $dataToSave['login_id'] = $request->login_id_store;
        // if ($request->appl_type === 'D') {
        //     $dataToSave['payment_status'] = $isDraft ? 'draft' : 'paid';
        // } else {
        $dataToSave['payment_status'] = $isDraft ? 'draft' : 'paid';
        // }
        $dataToSave['application_status'] = 'P';
        // $dataToSave['created_at'] = now();
        $dataToSave['updated_at'] = DB::raw('NOW()');

        $appl_type = preg_replace('/\s+/', '', $request->appl_type ?? '');

        // dd($request->appl_type); exit;
        // Addressproof----------------

        if (!empty($request->type_doc) || !empty($request->addressproofno)) {

            $addressproof = [
                'application_id' => $applicationId,
                'login_id'       => $request->login_id_store,
                'form_name'  => $request->form_name,
                'license_name'    => $request->license_name,
                'addressproofno'    => $request->addressproofno,
                'type_doc'    => $request->type_doc,

                // 'status'         => '1'
            ];


            // dd([
            //     'login_id_store' => $request->login_id_store,
            //     'bank_address' => $request->bank_address,
            //     'bank_validity' => $request->bank_validity,
            //     'bank_amount' => $request->bank_amount,
            // ]);


            $existingaddress = Tnelb_Addressproof_cl::where('application_id', $applicationId)->first();

            // dd($existingBank);
            // exit;

            if ($existingaddress) {
                $existingaddress->update($addressproof);
            } else {
                Tnelb_Addressproof_cl::create($addressproof);
            }
        }





        // 🔹 Save Bank Solvency data in separate table
        if (!empty($request->bank_address) || !empty($request->bank_validity) || !empty($request->bank_amount)) {


            $bankData = [
                'application_id' => $applicationId,
                'login_id'       => $request->login_id_store,
                'bank_address'   => strtoupper($request->bank_address) ?? null,
                'bank_validity'  => $request->bank_validity ?? null,
                'bank_amount'    => $request->bank_amount ?? null,
                'status'         => '1'
            ];
            //             dd($request->login_id_store);
            // exit;

            // dd([
            //     'login_id_store' => $request->login_id_store,
            //     'bank_address' => $request->bank_address,
            //     'bank_validity' => $request->bank_validity,
            //     'bank_amount' => $request->bank_amount,
            // ]);


            $existingBank = Tnelb_banksolvency_a::where('application_id', $applicationId)->first();

            // dd($existingBank);
            // exit;

            if ($existingBank) {
                $existingBank->update($bankData);
            } else {
                Tnelb_banksolvency_a::create($bankData);
            }
        }


        // 1. Remove old rows for this application
        Equipmentforma_tbl::where('application_id', $applicationId)
            ->where('login_id', $request->login_id_store)
            ->delete();

        // 2. Insert fresh rows
        foreach ($request->equipments as $row) {

            Equipmentforma_tbl::create([
                'application_id'   => $applicationId,
                'login_id'         => $request->login_id_store,
                'equip_id'         => $row['equip_id'],
                'licence_id'       => $row['licence_id'],
                'form_name'        => $request->form_name,
                'equipment_value'  => $row['value'] ?? 'no',
                'ipaddress'        => $request->ip(),
            ]);
        }

        $Tnelb_cl_validitycheck_existing = Tnelb_cl_validitycheck::where('application_id', $applicationId)
            ->where('login_id', $request->login_id_store)
            ->first();

        $data = [
            'application_id' => $applicationId,
            'login_id'       => $request->login_id_store,
            'form_name'      => $request->form_name,
            'license_name'   => $request->license_name,
            'check_value'    => $request->check_value,
            'ipaddress'      => $request->ip(),
        ];

        if ($Tnelb_cl_validitycheck_existing) {

            $Tnelb_cl_validitycheck_existing->update($data);
        } else {

            Tnelb_cl_validitycheck::create($data);
        }


        //  dd($applicationId);
        //         exit;

        unset($dataToSave['bank_address'], $dataToSave['bank_validity'], $dataToSave['bank_amount']);


        // -----------QC process---------------

        $processedStaffIdsQC = [];

        //     if ($request->has('staffqc_name')) {


        //         $staffIdsFromForm = $request->staffqc_id ?? [];

        //         $existingStaffIds = Tnelb_EA_QC_model::where(
        //             'application_id',
        //             $applicationId
        //         )->pluck('id')->toArray();

        //         // Get the current maximum row_index for this application
        //         $maxRowIndex = Tnelb_EA_QC_model::where(
        //             'application_id',
        //             $applicationId
        //         )->max('row_index');

        //         $nextRowIndex = ((int) $maxRowIndex) + 1;

        //         $qc_code = 1;

        //         foreach ($request->staffqc_name as $index => $staffName) {

        //             if (
        //                 empty($staffName) &&
        //                 empty($request->cc_qc_number[$index] ?? null) &&
        //                 empty($request->cc_qc_validity[$index] ?? null)
        //             ) {
        //                 continue;
        //             }

        //             $staffId = $staffIdsFromForm[$index] ?? null;

        //             $validity = $request->cc_qc_validity[$index] ?? null;

        //             /*
        //     |--------------------------------------------------------------------------
        //     | Existing record
        //     |--------------------------------------------------------------------------
        //     */
        //             if ($staffId && in_array($staffId, $existingStaffIds)) {

        //                 $existingStaff = Tnelb_EA_QC_model::find($staffId);

        //                 $staffData = [
        //                     'application_id'    => $applicationId,
        //                     'login_id'          => $request->login_id_store,
        //                     'form_name'         => $request->form_name,
        //                     'license_name'      => $request->license_name,
        //                     'staffname'         => strtoupper($staffName),
        //                     'staffqc_category'  => strtoupper(
        //                         $request->staffqc_category[$index] ?? ''
        //                     ),
        //                     'cc_qc_number'      => strtoupper(
        //                         $request->cc_qc_number[$index] ?? ''
        //                     ),
        //                     'cc_qc_validity'    => $validity,
        //                     'qc_code'           => $qc_code,
        //                     'flag'              => '1',
        //                 ];

        //                 $existingStaff->update($staffData);

        //                 // Keep existing row_index unchanged
        //                 $processedStaffIdsQC[] = $staffId;
        //             }

        //             /*
        //     |--------------------------------------------------------------------------
        //     | New record
        //     |--------------------------------------------------------------------------
        //     */ else {

        //                 $staffData = [
        //                     'application_id'    => $applicationId,
        //                     'login_id'          => $request->login_id_store,
        //                     'form_name'         => $request->form_name,
        //                     'license_name'      => $request->license_name,
        //                     'staffname'         => strtoupper($staffName),
        //                     'staffqc_category'  => strtoupper(
        //                         $request->staffqc_category[$index] ?? ''
        //                     ),
        //                     'cc_qc_number'      => strtoupper(
        //                         $request->cc_qc_number[$index] ?? ''
        //                     ),
        //                     'cc_qc_validity'    => $validity,
        //                     'qc_code'           => $qc_code,
        //                     'row_index'         => $nextRowIndex,
        //                     'flag'              => '1',
        //                 ];

        //                 $newStaff = Tnelb_EA_QC_model::create($staffData);

        //                 $processedStaffIdsQC[] = $newStaff->id;

        //                 // Next new record gets next sequence number
        //                 $nextRowIndex++;
        //             }

        //             $qc_code++;
        //         }

        //         /*
        // |--------------------------------------------------------------------------
        // | Delete records removed from the form
        // |--------------------------------------------------------------------------
        // */
        //         Tnelb_EA_QC_model::where('application_id', $applicationId)
        //             ->whereNotIn('id', $processedStaffIdsQC)
        //             ->delete();



        //         $tempDocs = DB::table('tnelb_temp_uploaded_documents')
        //             ->where('login_id', $request->login_id_store)
        //             ->where('form_name', $request->form_name)
        //             ->where('license_name', $request->license_name)
        //             ->where('document_category', 'qc_doc')
        //             ->where('appl_type', 'N')
        //             // ->whereIn('is_final', ['0', '2'])

        //             ->get();

        //         // dd($tempDocs->pluck('qc_code')->toArray());

        //         foreach ($tempDocs as $tempDoc) {




        //             $matchedPartner = Tnelb_EA_QC_model::where('application_id', $applicationId)

        //                 ->where('qc_code', $tempDoc->qc_code)
        //                 ->first();

        //             // dd($matchedPartner); exit;

        //             if (!$matchedPartner) {
        //                 continue; // No match → skip
        //             }

        //             // -----------------------------------------
        //             // 4️⃣ GET FINAL PRO PATH
        //             // -----------------------------------------
        //             $dynamicRequest = clone $request;
        //             $dynamicRequest->merge([
        //                 'module' => $tempDoc->module
        //             ]);

        //             $dbFilePath_all = DocPathController::getPath($dynamicRequest);
        //             $dbFilePath     = $dbFilePath_all->filepath_pro;

        //             $tempFullPath = public_path(
        //                 $tempDoc->file_path . '/' . $tempDoc->file_name
        //             );

        //             $proFolderPath = public_path($dbFilePath);

        //             if (!File::exists($proFolderPath)) {
        //                 File::makeDirectory($proFolderPath, 0755, true);
        //             }

        //             $proFullPath = $proFolderPath . '/' . $tempDoc->file_name;

        //             // dd($dbFilePath);exit;

        //             // -----------------------------------------
        //             // 5️⃣ COPY FILE
        //             // -----------------------------------------
        //             if (File::exists($tempFullPath)) {
        //                 File::copy($tempFullPath, $proFullPath);
        //             } else {
        //                 continue;
        //             }

        //             // dd($tempDoc->file_path . '/' . $tempDoc->file_name);
        //             // exit;

        //             // -----------------------------------------
        //             // 6️⃣ SAVE FILE NAME INTO MATCHED PARTNER
        //             // -----------------------------------------
        //             $matchedPartner->qc_document = $dbFilePath_all->filepath_pro . $tempDoc->file_name;

        //             // dd($matchedPartner->qc_document);exit;
        //             // $matchedPartner->qc_code = $tempDoc->qc_code;
        //             $matchedPartner->save();

        //             // -----------------------------------------
        //             // 7️⃣ MARK TEMP DOC AS FINAL
        //             // -----------------------------------------
        //             DB::table('tnelb_temp_uploaded_documents')
        //                 ->where('id', $tempDoc->id)
        //                 ->update([
        //                     'is_final'   => '1',
        //                     'moved_as'   => $request->input('form_action'),
        //                     'record_id_app' => $applicationId,
        //                     'updated_at' => now()
        //                 ]);
        //         }
        //     }

        // QC/QSC----------------

        // QC/QSC

        if ($request->has('staffqc_category')) {

            $staffName = $request->input('certificate_holder_name', []);
            $staffNameQC = $request->input('certificate_holder_name_qc', []);

            $staffCategories    = $request->input('staffqc_category', []);
            $staffCcNos         = $request->input('staff_cc_no', []);
            $staffFirstIssues   = $request->input('staff_cc_first_issue', []);
            $staffValidityFroms = $request->input('staff_cc_validity_from', []);
            $staffValidityTos   = $request->input('staff_cc_validity_to', []);

            $staffQcIds = $request->input('staffqc_id', []);

            /*
    |--------------------------------------------------------------------------
    | GET NEXT ROW INDEX
    |--------------------------------------------------------------------------
    */

            $lastRowIndex = DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->max('row_index');

            $nextRowIndex = ((int) $lastRowIndex) + 1;

            /*
    |--------------------------------------------------------------------------
    | CURRENT ROW INDEXES
    |--------------------------------------------------------------------------
    */

            $currentRowIndexes = [];

            /*
    |--------------------------------------------------------------------------
    | PROCESS EACH QC / QSC STAFF
    |--------------------------------------------------------------------------
    */

            foreach ($staffCategories as $index => $category) {

                $category = strtoupper(trim((string) $category));

                // Process only QC / QSC
                if (!in_array($category, ['QC', 'QSC'], true)) {
                    continue;
                }

                $ccNo = strtoupper(trim((string) ($staffCcNos[$index] ?? '')));

                // Ignore empty / invalid certificate numbers
                if (
                    $ccNo === '' ||
                    in_array(strtolower($ccNo), ['undefined', 'null'], true)
                ) {
                    continue;
                }

                $ccName = trim((string) (
                    $staffName[$index]
                    ?? $staffNameQC[$index]
                    ?? ''
                ));

                $firstIssue = trim((string) ($staffFirstIssues[$index] ?? ''));
                $validityFrom = trim((string) ($staffValidityFroms[$index] ?? ''));
                $validityTo = trim((string) ($staffValidityTos[$index] ?? ''));

                $staffId = $staffQcIds[$index] ?? null;

                /*
        |--------------------------------------------------------------------------
        | FIND EXISTING STAFF BY ID WITHIN THIS APPLICATION
        |--------------------------------------------------------------------------
        */

                $existing = null;

                if (
                    $staffId !== null &&
                    $staffId !== '' &&
                    strtolower(trim((string) $staffId)) !== 'undefined' &&
                    strtolower(trim((string) $staffId)) !== 'null'
                ) {
                    $existing = DB::table('cl_staff_tbl')
                        ->where('id', $staffId)
                        ->where('application_id', $applicationId)
                        ->whereIn('staff_category', ['QC', 'QSC'])
                        ->first();
                }

                /*
        |--------------------------------------------------------------------------
        | CHECK CERTIFICATE IN cl_staff_tbl FOR THIS APPLICATION
        |--------------------------------------------------------------------------
        */

                $existingByCertificate = DB::table('cl_staff_tbl')
                    ->where('application_id', $applicationId)
                    ->whereIn('staff_category', ['QC', 'QSC'])
                    ->whereRaw('UPPER(TRIM(staff_cc_no)) = ?', [$ccNo])
                    ->first();

                if ($existing) {

                    // Update the submitted staff record
                    $updateData = [
                        'staff_category' => $category,
                        'staff_cc_no'    => $ccNo,
                        'staff_flag'     => '1',
                        'updated_at'     => now(),
                    ];

                    if ($firstIssue !== '') {
                        $updateData['staff_cc_first_issue'] = $firstIssue;
                    }

                    if ($validityFrom !== '') {
                        $updateData['staff_cc_validity_from'] = $validityFrom;
                    }

                    if ($validityTo !== '') {
                        $updateData['staff_cc_validity_to'] = $validityTo;
                    }

                    if ($ccName !== '') {

                        $updateData['staff_name'] = $ccName;
                    }

                    DB::table('cl_staff_tbl')
                        ->where('id', $existing->id)
                        ->where('application_id', $applicationId)
                        ->update($updateData);

                    $currentRowIndexes[] = (int) $existing->row_index;
                } elseif ($existingByCertificate) {

                    // Certificate already exists in cl_staff_tbl.
                    // Do not insert a duplicate staff record.
                    DB::table('cl_staff_tbl')
                        ->where('id', $existingByCertificate->id)
                        ->where('application_id', $applicationId)
                        ->update([
                            'staff_category' => $category,
                            'staff_flag'     => '1',
                            'updated_at'     => now(),
                        ]);

                    $currentRowIndexes[] = (int) $existingByCertificate->row_index;
                } else {

                    /*
            |--------------------------------------------------------------------------
            | CERTIFICATE MISSING IN cl_staff_tbl -> INSERT
            |--------------------------------------------------------------------------
            */

                    $newRowIndex = $nextRowIndex;

                    DB::table('cl_staff_tbl')->insert([
                        'login_id' => $request->input('login_id_store'),
                        'application_id' => $applicationId,
                        'staff_category' => $category,
                        'staff_cc_no' => $ccNo,

                        'staff_name' => $ccName,

                        'staff_cc_first_issue' =>
                        $firstIssue !== '' ? $firstIssue : null,

                        'staff_cc_validity_from' =>
                        $validityFrom !== '' ? $validityFrom : null,

                        'staff_cc_validity_to' =>
                        $validityTo !== '' ? $validityTo : null,

                        'row_index'   => $newRowIndex,
                        'staff_flag'  => '1',
                        'staff_status' => 'N',
                        'new_entry'   => '1',

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $currentRowIndexes[] = $newRowIndex;

                    $nextRowIndex++;
                }

                /*
        |--------------------------------------------------------------------------
        | CHECK CERTIFICATE IN cl_staff_detail_adminstore
        |--------------------------------------------------------------------------
        */

                $adminStoreExists = DB::table('cl_staff_detail_adminstore')
                    ->where('application_id', $applicationId)
                    ->whereRaw('UPPER(TRIM(staff_cc_no)) = ?', [$ccNo])
                    ->exists();

                /*
        |--------------------------------------------------------------------------
        | CERTIFICATE MISSING IN ADMIN STORE -> INSERT
        |--------------------------------------------------------------------------
        */

                if (!$adminStoreExists) {

                    DB::table('cl_staff_detail_adminstore')->insert([
                        'application_id' => $applicationId,
                        'staff_cc_no' => $ccNo,

                        'staff_cc_first_issue' =>
                        $firstIssue !== '' ? $firstIssue : null,

                        'staff_cc_validity_from' =>
                        $validityFrom !== '' ? $validityFrom : null,

                        'staff_cc_validity_to' =>
                        $validityTo !== '' ? $validityTo : null,

                        'verify_flag'  => 0,
                        'processed_by' => null,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            /*
    |--------------------------------------------------------------------------
    | MARK MISSING QC / QSC RECORDS AS INACTIVE
    |--------------------------------------------------------------------------
    */

            $inactiveQuery = DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->whereIn('staff_category', ['QC', 'QSC'])
                ->where('staff_flag', '1');

            if (!empty($currentRowIndexes)) {
                $inactiveQuery->whereNotIn('row_index', $currentRowIndexes);
            }

            $inactiveQuery->update([
                'staff_flag' => '0',
                'updated_at' => now(),
            ]);
        }



        if ($request->has('cc_number')) {

            $processedStaffIds = [];

            $staffIdsFromForm = $request->input('staff_id', []);
            $rowIndexesFromForm = $request->input('row_index', []);

            // ==========================================================
            // ROW INDEXES PRESENT IN CURRENT FORM
            // ==========================================================

            $submittedRowIndexes = [];

            foreach ($rowIndexesFromForm as $rowIndex) {

                if (
                    $rowIndex !== null &&
                    $rowIndex !== '' &&
                    strtolower(trim((string) $rowIndex)) !== 'undefined' &&
                    strtolower(trim((string) $rowIndex)) !== 'null'
                ) {
                    $submittedRowIndexes[] = (int) $rowIndex;
                }
            }

            $submittedRowIndexes = array_values(
                array_unique($submittedRowIndexes)
            );


            // ==========================================================
            // EXISTING B / C / OTHERS STAFF ONLY
            // QC / QSC WILL NOT BE TOUCHED
            // ==========================================================

            $existingStaffIds = DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->whereIn('staff_category', ['B', 'C', 'OTHERS'])
                ->pluck('id')
                ->toArray();


            // ==========================================================
            // MARK REMOVED B / C / OTHERS STAFF AS staff_flag = 0
            // QC / QSC WILL NOT BE UPDATED
            // ==========================================================

            DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->whereIn('staff_category', ['B', 'C', 'OTHERS'])
                ->when(
                    !empty($submittedRowIndexes),
                    function ($query) use ($submittedRowIndexes) {
                        $query->whereNotIn(
                            'row_index',
                            $submittedRowIndexes
                        );
                    },
                    function ($query) {
                        $query->whereNotNull('row_index');
                    }
                )
                ->update([
                    'staff_flag' => '0',
                    'updated_at' => now(),
                ]);


            // ==========================================================
            // CURRENT MAX ROW INDEX
            // ==========================================================

            $lastRowIndex = DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->max('row_index');

            $nextRowIndex = ((int) $lastRowIndex) + 1;


            // ==========================================================
            // LOOP CURRENT B / C / OTHERS STAFF
            // ==========================================================

            foreach ($request->input('staff_category', []) as $index => $category) {

                // ----------------------------------------------------------
                // CATEGORY
                // ----------------------------------------------------------

                $category = is_array($category)
                    ? ($category[0] ?? null)
                    : $category;

                $category = strtoupper(trim((string) $category));


                // ----------------------------------------------------------
                // ONLY B / C / OTHERS
                // QC / QSC WILL NOT BE PROCESSED HERE
                // ----------------------------------------------------------

                if (!in_array($category, ['B', 'C', 'OTHERS'], true)) {
                    continue;
                }


                // ----------------------------------------------------------
                // GET VALUES USING SAME INDEX
                // ----------------------------------------------------------

                $ccNumber = $request->input("cc_number.$index");

                $firstIssue = $request->input("cc_firstissue.$index");

                $validityFrom = $request->input("cc_validity_from.$index");

                $validityTo = $request->input("cc_validity_to.$index");

                $designation = $request->input("designation.$index");

                $staffId = $staffIdsFromForm[$index] ?? null;

                $ccName = $request->input("certificate_holder_name_others.$index");

                $rowIndex = $rowIndexesFromForm[$index] ?? null;


                // ----------------------------------------------------------
                // NORMALIZE STAFF ID
                // ----------------------------------------------------------

                if (
                    $staffId !== null &&
                    $staffId !== '' &&
                    strtolower(trim((string) $staffId)) !== 'undefined' &&
                    strtolower(trim((string) $staffId)) !== 'null'
                ) {
                    $staffId = (int) $staffId;
                } else {
                    $staffId = null;
                }


                // ----------------------------------------------------------
                // NORMALIZE ROW INDEX
                // ----------------------------------------------------------

                if (
                    $rowIndex !== null &&
                    $rowIndex !== '' &&
                    strtolower(trim((string) $rowIndex)) !== 'undefined' &&
                    strtolower(trim((string) $rowIndex)) !== 'null'
                ) {
                    $rowIndex = (int) $rowIndex;
                } else {
                    $rowIndex = null;
                }


                // ----------------------------------------------------------
                // SKIP COMPLETELY EMPTY ROW
                // ----------------------------------------------------------

                if (
                    empty($category) &&
                    empty($ccNumber) &&
                    empty($firstIssue) &&
                    empty($validityFrom) &&
                    empty($validityTo) &&
                    empty($designation)
                ) {
                    continue;
                }


                // ----------------------------------------------------------
                // STAFF DATA
                // ----------------------------------------------------------

                $staffData = [

                    'application_id' =>
                    $applicationId,

                    'login_id' =>
                    $request->input('login_id_store'),

                    'staff_category' =>
                    $category,

                    'staff_cc_no' =>
                    strtoupper(trim((string) ($ccNumber ?? ''))),

                    'staff_cc_first_issue' =>
                    $firstIssue,

                    'staff_cc_validity_from' =>
                    $validityFrom,

                    'staff_name' =>
                    !empty($ccName)
                        ? trim((string) $ccName)
                        : null,


                    'staff_cc_validity_to' =>
                    $validityTo,

                    'staff_status' =>
                    'N',

                    'staff_flag' =>
                    '1',

                    'staff_designation' =>
                    !empty($designation)
                        ? trim((string) $designation)
                        : null,

                    'updated_at' =>
                    now(),
                ];


                // ----------------------------------------------------------
                // OTHERS
                // ----------------------------------------------------------

                if ($category === 'OTHERS') {

                    $staffData['staff_designation'] =
                        trim((string) ($designation ?? ''));

                    $staffData['staff_cc_no'] = null;

                    $staffData['staff_cc_first_issue'] = null;

                    $staffData['staff_cc_validity_from'] = null;

                    $staffData['staff_name'] = null;

                    $staffData['staff_cc_validity_to'] = null;
                }


                // ----------------------------------------------------------
                // EXISTING STAFF -> UPDATE
                // ----------------------------------------------------------

                if (
                    !empty($staffId) &&
                    in_array($staffId, $existingStaffIds, true)
                ) {

                    // Existing row MUST retain its own row_index
                    $staffData['row_index'] = $rowIndex;

                    DB::table('cl_staff_tbl')
                        ->where('id', $staffId)
                        ->where('application_id', $applicationId)
                        ->whereIn(
                            'staff_category',
                            ['B', 'C', 'OTHERS']
                        )
                        ->update($staffData);

                    $processedStaffIds[] = $staffId;
                }


                // ----------------------------------------------------------
                // NEW STAFF -> INSERT
                // ----------------------------------------------------------

                else {

                    $staffData['row_index'] = $nextRowIndex;

                    $staffData['staff_flag'] = '1';

                    $staffData['staff_status'] = 'NA';

                    $staffData['created_at'] = now();

                    $newStaffId = DB::table('cl_staff_tbl')
                        ->insertGetId($staffData);

                    $processedStaffIds[] = $newStaffId;



                    DB::table('cl_staff_detail_adminstore')->insert([
                        'application_id' => $applicationId,

                        'staff_cc_no' => $staffData['staff_cc_no'],

                        'staff_cc_first_issue' =>
                        !empty($firstIssue) ? $firstIssue : null,

                        'staff_cc_validity_from' =>
                        !empty($validityFrom) ? $validityFrom : null,

                        'staff_cc_validity_to' =>
                        !empty($validityTo) ? $validityTo : null,

                        'verify_flag'  => 0,
                        'processed_by' => null,

                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);


                    $nextRowIndex++;
                }
            }
        }


        $newProprietorIds = [];
        if ($request->has('proprietor_name')) {

            // dd($request->all());
            // exit;
            $count = 1;
            foreach ($request->proprietor_name as $index => $name) {

                if (
                    empty($name) ||
                    strtolower(trim($name)) === 'undefined' ||
                    trim($name) === 'null'
                ) {
                    continue;
                }
                $competencyHolding = data_get($request->competency, $index);
                $proprietorId = $request->proprietor_id[$index] ?? null;
                // dd([
                //     'proprietor_name' => $request->proprietor_name,
                //     'dob' => $request->dob,
                //     'age' => $request->age,
                //     'qualification' => $request->qualification,
                //     'qual_text' => $request->qual_text,
                //     'proprietor_address' => $request->proprietor_address,
                //     'fathers_name' => $request->fathers_name,
                //     'present_business' => $request->present_business,
                //     'competency_certificate_holding' => $competencyHolding,
                //     'competency_certificate_number' => strtoupper(data_get($request->competency_certno, $index, '')),

                //    'competency_certificate_first_issue' => data_get($request->ccfirstissue, $index),


                //     'competency_certificate_validity_from' => data_get($request->ccvalidityfrom, $index),


                //     'competency_certificate_validity_to' => data_get($request->ccvalidityto, $index),

                // ]);
                // exit;



                $data = [
                    'login_id' => $request->login_id_store,
                    'application_id' => $applicationId,

                    'proprietor_name' => strtoupper($name ?? ''),

                    'ownership_type' => 'pr',

                    'proprietor_address' => strtoupper(
                        data_get($request->proprietor_address, $index, '')
                    ),

                    'dob' => data_get($request->dob, $index),

                    'age' => data_get($request->age, $index),

                    'qualification' => strtoupper(
                        data_get($request->qualification, $index, '')
                    ),

                    'qualification_text' => strtoupper(
                        data_get($request->qual_text, $index, '')
                    ),

                    'fathers_name' => strtoupper(
                        data_get($request->fathers_name, $index, '')
                    ),

                    'present_business' => strtoupper(
                        data_get($request->present_business, $index, '')
                    ),

                    // ==========================================
                    // COMPETENCY CERTIFICATE
                    // ==========================================

                    'competency_certificate_holding' => $competencyHolding,

                    'competency_certificate_number' =>
                    $competencyHolding === 'yes'
                        ? strtoupper(
                            data_get($request->competency_certno, $index, '')
                        )
                        : null,

                    'competency_certificate_first_issue' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->ccfirstissue, $index)
                        : null,

                    'competency_certificate_validity_from' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->ccvalidityfrom, $index)
                        : null,

                    'competency_certificate_validity_to' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->ccvalidityto, $index)
                        : null,

                    'proprietor_flag' => 1,

                    'ownership_count' => $count,
                ];


                if ($proprietorId) {
                    ProprietorformA::where('id', $proprietorId)->update($data);
                    $newProprietorIds[] = $proprietorId;
                } else {
                    $new = ProprietorformA::create($data);
                    $newProprietorIds[] = $new->id;
                }
            }
            $count++;

            // Deactivate removed rows
            ProprietorformA::where('application_id', $applicationId)
                ->whereNotIn('id', $newProprietorIds)
                ->where('ownership_type', 'pr')
                ->update(['proprietor_flag' => 0]);

            // table move edu_file----------

            // $recordexists = DB::table('cl_ownership_table')
            //             ->where('application_id', $applicationId)
            //             ->get();
            // if ($recordexists) {

            //              DB::table('$cl_ownership_table')
            //             ->where('application_id', $applicationId)
            //             ->update([
            //                 'educ_qual_proof' => $finalDbPath,
            //                 'updated_at'  => now()
            //             ]);
            //         }


            $tempDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'educ_qual_proof')
                ->where('ownership_type', 'pr')
                // ->whereIn('is_final', ['0', '2'])

                ->get();

            foreach ($tempDocs as $tempDoc) {


                $matchedPartner = ProprietorformA::where('application_id', $applicationId)
                    ->where('ownership_type', 'pr')
                    ->where('ownership_count', $tempDoc->row_index + 1)
                    ->first();

                if (!$matchedPartner) {
                    continue; // No match → skip
                }

                // -----------------------------------------
                // 4️⃣ GET FINAL PRO PATH
                // -----------------------------------------
                $dynamicRequest = clone $request;
                $dynamicRequest->merge([
                    'module' => $tempDoc->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath     = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDoc->file_path . '/' . $tempDoc->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory($proFolderPath, 0755, true);
                }

                $proFullPath = $proFolderPath . '/' . $tempDoc->file_name;

                // dd($dbFilePath);exit;

                // -----------------------------------------
                // 5️⃣ COPY FILE
                // -----------------------------------------
                if (File::exists($tempFullPath)) {
                    File::copy($tempFullPath, $proFullPath);
                } else {
                    continue;
                }

                // dd($tempDoc->file_path . '/' . $tempDoc->file_name);
                // exit;

                // -----------------------------------------
                // 6️⃣ SAVE FILE NAME INTO MATCHED PARTNER
                // -----------------------------------------
                $matchedPartner->educational_proof = $dbFilePath_all->filepath_pro . $tempDoc->file_name;

                // dd($matchedPartner->educational_proof);exit;
                $matchedPartner->row_index = $tempDoc->row_index;
                $matchedPartner->save();

                // -----------------------------------------
                // 7️⃣ MARK TEMP DOC AS FINAL
                // -----------------------------------------
                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDoc->id)
                    ->update([
                        'is_final'   => '1',
                        'moved_as'   => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }


            // ------Age Proof-------------------
            $tempDocsAge = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'age_proof')
                ->where('ownership_type', 'pr')
                // ->whereIn('is_final', ['0', '2'])

                ->get();

            foreach ($tempDocsAge as $tempDocAge) {


                $matchedPartner = ProprietorformA::where('application_id', $applicationId)
                    ->where('ownership_type', 'pr')
                    ->where('ownership_count', $tempDocAge->row_index + 1)
                    ->first();

                if (!$matchedPartner) {
                    continue; // No match → skip
                }

                // -----------------------------------------
                // 4️⃣ GET FINAL PRO PATH
                // -----------------------------------------
                $dynamicRequest = clone $request;
                $dynamicRequest->merge([
                    'module' => $tempDocAge->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath     = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDocAge->file_path . '/' . $tempDocAge->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory($proFolderPath, 0755, true);
                }

                $proFullPath = $proFolderPath . '/' . $tempDocAge->file_name;

                // dd($dbFilePath);exit;

                // -----------------------------------------
                // 5️⃣ COPY FILE
                // -----------------------------------------
                if (File::exists($tempFullPath)) {
                    File::copy($tempFullPath, $proFullPath);
                } else {
                    continue;
                }

                // dd($tempDocAge->file_path . '/' . $tempDocAge->file_name);
                // exit;

                // -----------------------------------------
                // 6️⃣ SAVE FILE NAME INTO MATCHED PARTNER
                // -----------------------------------------
                $matchedPartner->age_proof = $dbFilePath_all->filepath_pro . $tempDocAge->file_name;

                // dd($matchedPartner->educational_proof);exit;
                $matchedPartner->row_index = $tempDocAge->row_index;
                $matchedPartner->save();

                // -----------------------------------------
                // 7️⃣ MARK TEMP DOC AS FINAL
                // -----------------------------------------
                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDocAge->id)
                    ->update([
                        'is_final'   => '1',
                        'moved_as'   => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }
        }

        // Partners
        $newPartnerIds = [];

        if ($request->has('partner_name')) {

            $count = 1;

            foreach ($request->partner_name as $index => $name) {

                if (
                    empty($name) ||
                    strtolower(trim($name)) === 'undefined' ||
                    trim($name) === 'null'
                ) {
                    continue;
                }

                $competencyHolding = data_get($request->partner_competency, $index);
                $partnerId = $request->partner_id[$index] ?? null;

                $data = [
                    'login_id' => $request->login_id_store,
                    'application_id' => $applicationId,

                    'proprietor_name' => strtoupper($name ?? ''),

                    'ownership_type' => 'pt',

                    'proprietor_address' => strtoupper(
                        data_get($request->partner_proprietor_address, $index, '')
                    ),

                    'dob' => data_get($request->partner_dob, $index),

                    'age' => data_get($request->partner_age, $index),

                    'qualification' => strtoupper(
                        data_get($request->partner_qualification, $index, '')
                    ),

                    'qualification_text' => strtoupper(
                        data_get($request->partner_qual_text, $index, '')
                    ),

                    'fathers_name' => strtoupper(
                        data_get($request->partner_fathers_name, $index, '')
                    ),

                    'present_business' => strtoupper(
                        data_get($request->partner_present_business, $index, '')
                    ),

                    // ==========================================
                    // COMPETENCY CERTIFICATE
                    // ==========================================

                    'competency_certificate_holding' => $competencyHolding,

                    'competency_certificate_number' =>
                    $competencyHolding === 'yes'
                        ? strtoupper(
                            data_get($request->partner_competency_certno, $index, '')
                        )
                        : null,

                    'competency_certificate_first_issue' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->partner_ccfirstissue, $index)
                        : null,

                    'competency_certificate_validity_from' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->partner_ccvalidityfrom, $index)
                        : null,

                    'competency_certificate_validity_to' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->partner_ccvalidityto, $index)
                        : null,



                    'proprietor_flag' => 1,

                    'ownership_count' => $count,
                ];

                if ($partnerId) {

                    ProprietorformA::where('id', $partnerId)
                        ->update($data);

                    $newPartnerIds[] = $partnerId;
                } else {

                    $new = ProprietorformA::create($data);

                    $newPartnerIds[] = $new->id;
                }

                $count++;
            }

            // Deactivate removed partner rows
            ProprietorformA::where('application_id', $applicationId)
                ->whereNotIn('id', $newPartnerIds)
                ->where('ownership_type', 'pt')
                ->update([
                    'proprietor_flag' => 0
                ]);

            // ==========================================
            // EDUCATIONAL QUALIFICATION PROOF
            // ==========================================

            $tempDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'educ_qual_proof')
                ->where('ownership_type', 'pt')
                ->get();

            foreach ($tempDocs as $tempDoc) {

                $matchedPartner = ProprietorformA::where('application_id', $applicationId)
                    ->where('ownership_type', 'pt')
                    ->where('ownership_count', $tempDoc->row_index + 1)
                    ->first();

                if (!$matchedPartner) {
                    continue;
                }

                $dynamicRequest = clone $request;

                $dynamicRequest->merge([
                    'module' => $tempDoc->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDoc->file_path . '/' . $tempDoc->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory($proFolderPath, 0755, true);
                }

                $proFullPath = $proFolderPath . '/' . $tempDoc->file_name;

                if (File::exists($tempFullPath)) {
                    File::copy($tempFullPath, $proFullPath);
                } else {
                    continue;
                }

                $matchedPartner->educational_proof =
                    $dbFilePath_all->filepath_pro . $tempDoc->file_name;

                $matchedPartner->row_index = $tempDoc->row_index;

                $matchedPartner->save();

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDoc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }

            // ==========================================
            // AGE PROOF
            // ==========================================

            $tempDocsAge = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'age_proof')
                ->where('ownership_type', 'pt')
                ->get();

            foreach ($tempDocsAge as $tempDocAge) {

                $matchedPartner = ProprietorformA::where('application_id', $applicationId)
                    ->where('ownership_type', 'pt')
                    ->where('ownership_count', $tempDocAge->row_index + 1)
                    ->first();

                if (!$matchedPartner) {
                    continue;
                }

                $dynamicRequest = clone $request;

                $dynamicRequest->merge([
                    'module' => $tempDocAge->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDocAge->file_path . '/' . $tempDocAge->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory($proFolderPath, 0755, true);
                }

                $proFullPath = $proFolderPath . '/' . $tempDocAge->file_name;

                if (File::exists($tempFullPath)) {
                    File::copy($tempFullPath, $proFullPath);
                } else {
                    continue;
                }

                $matchedPartner->age_proof =
                    $dbFilePath_all->filepath_pro . $tempDocAge->file_name;

                $matchedPartner->row_index = $tempDocAge->row_index;

                $matchedPartner->save();

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDocAge->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }
        }
        // ----------------director------------------------
        $newdirectorIds = [];

        if ($request->has('director_name')) {

            $count = 1;

            foreach ($request->director_name as $index => $name) {

                if (
                    empty($name) ||
                    strtolower(trim($name)) === 'undefined' ||
                    trim($name) === 'null'
                ) {
                    continue;
                }

                $directorId = $request->director_id[$index] ?? null;

                $competencyHolding = data_get(
                    $request->director_competency,
                    $index,
                    'no'
                );

                $data = [

                    'login_id' => $request->login_id_store,

                    'application_id' => $applicationId,

                    'proprietor_name' => strtoupper($name ?? ''),

                    'ownership_type' => 'dr',

                    'ownership_count' => $count,

                    'proprietor_flag' => 1,

                    // ==========================================
                    // MANAGING DIRECTOR
                    // ==========================================

                    'managing_director' => strtoupper(
                        data_get(
                            $request->director_managing_director,
                            $index,
                            'NO'
                        )
                    ),

                    // ==========================================
                    // BASIC DETAILS
                    // ==========================================

                    'fathers_name' => strtoupper(
                        data_get(
                            $request->director_fathers_name,
                            $index,
                            ''
                        )
                    ),

                    'proprietor_address' => strtoupper(
                        data_get(
                            $request->director_proprietor_address,
                            $index,
                            ''
                        )
                    ),

                    'dob' => data_get(
                        $request->director_dob,
                        $index
                    ),

                    'age' => data_get(
                        $request->director_age,
                        $index
                    ),

                    // ==========================================
                    // QUALIFICATION
                    // ==========================================

                    'qualification' => strtoupper(
                        data_get(
                            $request->director_qualification,
                            $index,
                            ''
                        )
                    ),

                    'qualification_text' => strtoupper(
                        data_get(
                            $request->director_qual_text,
                            $index,
                            ''
                        )
                    ),

                    // ==========================================
                    // BUSINESS
                    // ==========================================

                    'present_business' => strtoupper(
                        data_get(
                            $request->director_present_business,
                            $index,
                            ''
                        )
                    ),

                    // ==========================================
                    // COMPETENCY CERTIFICATE
                    // ==========================================

                    'competency_certificate_holding' => $competencyHolding,

                    'competency_certificate_number' =>
                    $competencyHolding === 'yes'
                        ? strtoupper(
                            data_get(
                                $request->director_competency_certno,
                                $index,
                                ''
                            )
                        )
                        : null,

                    'competency_certificate_first_issue' =>
                    $competencyHolding === 'yes'
                        ? data_get(
                            $request->director_ccfirstissue,
                            $index
                        )
                        : null,

                    'competency_certificate_validity_from' =>
                    $competencyHolding === 'yes'
                        ? data_get(
                            $request->director_ccvalidityfrom,
                            $index
                        )
                        : null,

                    'competency_certificate_validity_to' =>
                    $competencyHolding === 'yes'
                        ? data_get(
                            $request->director_ccvalidityto,
                            $index
                        )
                        : null,


                ];

                // ==========================================
                // UPDATE / INSERT
                // ==========================================

                if (!empty($directorId)) {

                    ProprietorformA::where('id', $directorId)
                        ->update($data);

                    $newdirectorIds[] = $directorId;
                } else {

                    $newDirector = ProprietorformA::create($data);

                    $newdirectorIds[] = $newDirector->id;
                }

                $count++;
            }

            // ==========================================
            // DEACTIVATE REMOVED DIRECTORS
            // ==========================================

            ProprietorformA::where('application_id', $applicationId)
                ->whereNotIn('id', $newdirectorIds)
                ->where('ownership_type', 'dr')
                ->update([
                    'proprietor_flag' => 0
                ]);

            // ==========================================
            // EDUCATIONAL QUALIFICATION PROOF
            // ==========================================

            $tempDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'educ_qual_proof')
                ->where('ownership_type', 'dr')
                ->get();

            foreach ($tempDocs as $tempDoc) {

                $matchedDirector = ProprietorformA::where(
                    'application_id',
                    $applicationId
                )
                    ->where('ownership_type', 'dr')
                    ->where(
                        'ownership_count',
                        $tempDoc->row_index + 1
                    )
                    ->first();

                if (!$matchedDirector) {
                    continue;
                }

                $dynamicRequest = clone $request;

                $dynamicRequest->merge([
                    'module' => $tempDoc->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDoc->file_path . '/' . $tempDoc->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory(
                        $proFolderPath,
                        0755,
                        true
                    );
                }

                $proFullPath = $proFolderPath . '/' . $tempDoc->file_name;

                if (File::exists($tempFullPath)) {
                    File::copy(
                        $tempFullPath,
                        $proFullPath
                    );
                } else {
                    continue;
                }

                $matchedDirector->educational_proof =
                    $dbFilePath_all->filepath_pro .
                    $tempDoc->file_name;

                $matchedDirector->row_index =
                    $tempDoc->row_index;

                $matchedDirector->save();

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDoc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }

            // ==========================================
            // AGE PROOF
            // ==========================================

            $tempDocsAge = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'age_proof')
                ->where('ownership_type', 'dr')
                ->get();

            foreach ($tempDocsAge as $tempDocAge) {

                $matchedDirector = ProprietorformA::where(
                    'application_id',
                    $applicationId
                )
                    ->where('ownership_type', 'dr')
                    ->where(
                        'ownership_count',
                        $tempDocAge->row_index + 1
                    )
                    ->first();

                if (!$matchedDirector) {
                    continue;
                }

                $dynamicRequest = clone $request;

                $dynamicRequest->merge([
                    'module' => $tempDocAge->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDocAge->file_path . '/' . $tempDocAge->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory(
                        $proFolderPath,
                        0755,
                        true
                    );
                }

                $proFullPath = $proFolderPath . '/' . $tempDocAge->file_name;

                if (File::exists($tempFullPath)) {
                    File::copy(
                        $tempFullPath,
                        $proFullPath
                    );
                } else {
                    continue;
                }

                $matchedDirector->age_proof =
                    $dbFilePath_all->filepath_pro .
                    $tempDocAge->file_name;

                $matchedDirector->row_index =
                    $tempDocAge->row_index;

                $matchedDirector->save();

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDocAge->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }
        }




        if ($existing) {

            $updateData = collect($dataToSave)
                ->except(['aadhaar_doc', 'pancard_doc', 'gst_doc'])
                ->toArray();



            EA_Application_model::where('application_id', $existing->application_id)
                ->update($updateData);
        } else {
            $dataToSave['created_at'] = DB::raw('NOW()');

            $createData = collect($dataToSave)
                ->except(['aadhaar_doc', 'pancard_doc', 'gst_doc'])
                ->toArray();

            EA_Application_model::create($createData);
            $message = $isDraft ? 'Draft saved successfully!' : 'Application submitted successfully!';
        }
        $transactionId = 'TXN' . rand(100000, 999999);

        // $payment = $isDraft ? 'draft' : 'success';

        // ------------Temp Table move------------------------------------------
        DB::transaction(function () use ($applicationId, $request) {

            // dd('111');exit;


            $pathData = DocPathController::getPath($request);
            $proFolderPath = public_path($pathData->filepath_pro);

            if (!File::exists($proFolderPath)) {
                File::makeDirectory($proFolderPath, 0755, true);
            }

            $doc = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'ownership_doc')
                ->latest()
                ->first();

            if ($doc) {

                $tempFullPath = public_path($doc->file_path . '/' . $doc->file_name);
                $proFullPath  = $proFolderPath . '/' . $doc->file_name;

                if (File::exists($tempFullPath)) {

                    File::delete($proFullPath); // replace
                    File::copy($tempFullPath, $proFullPath);
                }

                $finalPath = $pathData->filepath_pro . '/' . $doc->file_name;

                DB::table('ccl_forma_meta')
                    ->updateOrInsert(
                        ['application_id' => $applicationId],
                        [
                            'ownership_doc' => $finalPath,
                            'updated_at' => now()
                        ]
                    );

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $doc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId
                    ]);
            }


            // ---------------------------bank doc------------------
            $doc = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'bank_doc')
                ->latest()
                ->first();

            if ($doc) {

                $tempFullPath = public_path($doc->file_path . '/' . $doc->file_name);
                $proFullPath  = $proFolderPath . '/' . $doc->file_name;

                if (File::exists($tempFullPath)) {

                    File::delete($proFullPath);
                    File::copy($tempFullPath, $proFullPath);
                }

                $finalPath = $pathData->filepath_pro . '/' . $doc->file_name;

                DB::table('tnelb_banksolvency_a')
                    ->updateOrInsert(
                        ['application_id' => $applicationId],
                        [
                            'login_id'            => $request->login_id_store,

                            'form_name'           => $request->form_name,
                            'license_name'        => $request->license_name,
                            'bank_doc' => $finalPath,
                            'updated_at' => now(),
                            'status' => '1'
                        ]
                    );

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $doc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId
                    ]);
            }

            // ------------------------Address proof--------------------
            $doc = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'Address_proof')
                ->latest()
                ->first();

            if ($doc) {

                $tempFullPath = public_path($doc->file_path . '/' . $doc->file_name);
                $proFullPath  = $proFolderPath . '/' . $doc->file_name;

                if (File::exists($tempFullPath)) {

                    File::delete($proFullPath);
                    File::copy($tempFullPath, $proFullPath);
                }

                $finalPath = $pathData->filepath_pro . '/' . $doc->file_name;

                DB::table('tnelb_addressproof_cl')
                    ->updateOrInsert(
                        ['application_id' => $applicationId],
                        [
                            'login_id'            => $request->login_id_store,

                            'form_name'           => $request->form_name,
                            'license_name'        => $request->license_name,
                            'file_doc' => $finalPath,
                            'updated_at' => now()
                        ]
                    );

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $doc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId
                    ]);
            }

            // ---------------other docs------------------

            $otherDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'other_doc')
                ->get();

            foreach ($otherDocs as $doc) {

                $pathData = DocPathController::getPath($request);
                $proFolderPath = public_path($pathData->filepath_pro);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory($proFolderPath, 0755, true);
                }

                $tempFullPath = public_path($doc->file_path . '/' . $doc->file_name);
                $proFullPath  = $proFolderPath . '/' . $doc->file_name;

                if (File::exists($tempFullPath)) {

                    File::delete($proFullPath);
                    File::copy($tempFullPath, $proFullPath);
                }

                $finalPath = $pathData->filepath_pro . '/' . $doc->file_name;

                DB::table('tnelb_attachments_cl')->updateOrInsert(
                    [
                        'application_id' => $applicationId,
                        'type' => $doc->ownership_type
                    ],
                    [
                        'login_id'            => $request->login_id_store,

                        'form_name'           => $request->form_name,
                        'license_name'        => $request->license_name,
                        'file_doc'   => $finalPath,
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );



                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $doc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId
                    ]);
            }




            // =======================================================
            // 8️⃣ MOVE EQUIPMENT FILES + INSERT INTO PERMANENT TABLE
            // =======================================================

            // if ($request->has('equipments')) {

            // -----------------------------------------
            // 1️⃣ GET PERMANENT PATH
            // -----------------------------------------
            $dbFilePath_all = DocPathController::getPath($request);
            $proFolderPath  = public_path($dbFilePath_all->filepath_pro);

            if (!File::exists($proFolderPath)) {
                File::makeDirectory($proFolderPath, 0755, true);
            }

            // Fetch all temp docs grouped by equipment
            $allEquipmentDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('module', 'EQUIPMENTS DOCUMENT')
                ->where('document_sub_category', 'ED')
                ->get()
                ->groupBy('equip_code');

            // Delete old records
            DB::table('tnelb_equimentsuser_cl')
                ->where('application_id', $applicationId)
                ->delete();

            // Loop equipments
            foreach ($request->equipments as $index => $equipment) {

                if (
                    empty($equipment['equip_id']) &&
                    empty($request->serial_no[$index]) &&
                    empty($request->model[$index])
                ) {
                    continue;
                }

                $equipmentId = $equipment['equip_id'];
                $licenceId   = $equipment['licence_id'];

                $serialNo   = $request->serial_no[$index] ?? null;
                $modelNo    = $request->model[$index] ?? null;
                $dateOfTest = $request->date_of_test[$index] ?? null;

                $equipmentDocs = $allEquipmentDocs[$equipmentId] ?? collect();

                // ✅ Get latest files only
                $testDoc = $equipmentDocs
                    ->where('document_category', 'instrument_test_report')
                    ->sortByDesc('id')
                    ->first();

                $purchaseDoc = $equipmentDocs
                    ->where('document_category', 'instrument_purchase_report')
                    ->sortByDesc('id')
                    ->first();

                $testReportPath = null;
                $purchaseReportPath = null;
                // dd($equipmentDocs->pluck('document_category'));exit;
                // ✅ Move and assign TEST REPORT
                if ($testDoc) {
                    $tempFullPath = public_path($testDoc->file_path . '/' . $testDoc->file_name);
                    $proFullPath  = $proFolderPath . '/' . $testDoc->file_name;

                    if (File::exists($tempFullPath)) {
                        File::copy($tempFullPath, $proFullPath);
                    }

                    $testReportPath = $dbFilePath_all->filepath_pro . '/' . $testDoc->file_name;

                    DB::table('tnelb_temp_uploaded_documents')
                        ->where('id', $testDoc->id)
                        ->update([
                            'is_final'   => '1',
                            'moved_as'   => $request->input('form_action'),
                            'updated_at' => now()
                        ]);
                }

                // ✅ Move and assign PURCHASE REPORT
                if ($purchaseDoc) {
                    $tempFullPath = public_path($purchaseDoc->file_path . '/' . $purchaseDoc->file_name);
                    $proFullPath  = $proFolderPath . '/' . $purchaseDoc->file_name;

                    if (File::exists($tempFullPath)) {
                        File::copy($tempFullPath, $proFullPath);
                    }

                    $purchaseReportPath = $dbFilePath_all->filepath_pro . '/' . $purchaseDoc->file_name;

                    DB::table('tnelb_temp_uploaded_documents')
                        ->where('id', $purchaseDoc->id)
                        ->update([
                            'is_final'   => '1',
                            'moved_as'   => $request->input('form_action'),
                            'updated_at' => now()
                        ]);
                }

                // ✅ FINAL INSERT (Correct)
                DB::table('tnelb_equimentsuser_cl')->insert([
                    'login_id'            => $request->login_id_store ?? null,
                    'application_id'      => $applicationId,
                    'form_name'           => $request->form_name,
                    'license_name'        => $request->license_name,
                    'licence_id'          => $licenceId,
                    'equipment_id'        => $equipmentId,
                    'serial_no'           => $serialNo,
                    'model_no'            => $modelNo,
                    'testreport_file'     => $testReportPath,
                    'purchasereport_file' => $purchaseReportPath,
                    'dateoftest'          => $dateOfTest,
                    'ipaddress'           => $request->ip(),
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            // }

            // var_dump($tempDocs);
            // exit;
            // foreach ($tempDocs as $doc) {

            //     // Decide which column to update in main table
            //     $updateColumn = null;

            //     if ($doc->document_category) {
            //         $updateColumn = 'ownership_doc';
            //     }

            //     // if ($doc->document_category === 'DIRECTOR_MOM') {
            //     //     $updateColumn = 'director_mom_doc';
            //     // }

            //     if ($updateColumn) {

            //         // 🔹 Update main application table
            //         DB::table('ccl_forma_meta')
            //             ->where('application_id', $applicationId)
            //             ->update([
            //                 $updateColumn => $doc->file_name,
            //                 'updated_at'  => DB::raw('NOW()')
            //             ]);

            //         // 🔹 Mark temp document as final
            //         DB::table('tnelb_temp_uploaded_documents')
            //             ->where('id', $doc->id)
            //             ->update([
            //                 'is_final'   => '1',
            //                 'updated_at'=> DB::raw('NOW()')
            //             ]);
            //     }
            // }
        });


        if (!$isDraft) {

            // dd('111');exit;

            DB::table('ccl_forma_meta')
                ->where('application_id', $applicationId)
                ->update([
                    'processed_by'   => null,
                    'return_submit' => DB::raw('NOW()')
                ]);

            $supervisorRoleId = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->value('roles_id');

            WorkflowA::create([
                'application_id' => $applicationId,
                'appl_status'    => 'RE',
                'processed_by'   => 'AP',
                'forwarded_to'   => $supervisorRoleId,
                'role_id'        => $supervisorRoleId,
                'is_verified'    => 'Yes',
                'query_status'   => null,
                'remarks'        => 'Resubmitted by applicant after query.',
                'queries'        => null,
                'raised_by'      => null,
            ]);

            WorkflowA::where('application_id', $applicationId)
                ->where('appl_status', 'RE')
                ->where('role_id', $supervisorRoleId,)
                ->orderByDesc('id')
                ->limit(1)
                ->update([
                    'created_at' => DB::raw('NOW()'),
                    'updated_at' => DB::raw('NOW()'),
                ]);


            return response()->json([
                'draft_status' => $isDraft,
                'actionType' => $request->input('form_action'),
                'message' => 'Application Submitted!',
                'login_id' => $applicationId,
                'transaction_id' => '',
            ]);
        }

        //  DB::table('ccl_forma_meta')
        //         ->where('application_id', $applicationId)
        //          ->update([
        //                     'payment_status'   => $request->input('form_action'),

        //                 ]);

        // dd('1111');exit;
        DB::table('ccl_forma_meta')
            ->where('application_id', $applicationId)
            ->update([
                'application_status'   => 'RETD',

            ]);
        return response()->json([
            'message' => 'Draft',
            'actionType' => $request->input('form_action'),
            'login_id' => $applicationId,
            'transaction_id' => '',
            'draft_status' => $isDraft
        ]);
    }



    // ----------forma renewal return------------------

    public function storerenewalreturn(Request $request)
    {

        // dd($request->type_doc);
        // exit;

        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);
        $isDraft = $request->input('form_action') === 'draft';
        $recordId = $request->input('record_id');
        // dd($request->input('form_action'));
        // dd($request->all());
        // exit;
        // Format date fields
        $this->formatDatesToDMY([
            // 'bank_validity',
            // 'cc_validity',
            // 'competency_certificate_validity',
            // 'previous_experience_lnumber_validity'
        ], $request);

        if ($isDraft) {
            // Draft mode: minimal required fields, rest nullable
            $rules = [
                'applicant_name' => 'required|string|max:255',
                'business_address' => 'required|string|max:500',
                'form_name' => 'required|string|max:255',
                'license_name' => 'required|string|max:255',
                'appl_type' => 'required',

                'application_ownershiptype' => 'nullable|string',

                // Optional fields in draft mode
                'authorised_name_designation' => 'nullable|string|max:255',
                'authorised_name' => 'nullable|string|max:255',
                'authorised_designation' => 'nullable|string|max:255',
                'previous_contractor_license' => 'nullable|string|max:10',
                'previous_application_number' => 'nullable|string|max:50',
                'previous_application_validity' => 'nullable',
                'bank_address' => 'nullable|string|max:500',
                'bank_validity' => 'nullable|date',
                'bank_amount' => 'nullable|numeric|min:0',

                'previous_contractor_license_verify' => 'nullable|numeric',
                'criminal_offence' => 'nullable|string|in:yes,no',
                'consent_letter_enclose' => 'nullable|string|in:yes,no',
                'cc_holders_enclosed' => 'nullable|string|in:yes,no',
                'purchase_bill_enclose' => 'nullable|string|in:yes,no',
                'test_reports_enclose' => 'nullable|string|in:yes,no',
                'specimen_signature_enclose' => 'nullable|string|in:yes,no',
                'separate_sheet' => 'nullable|string|in:yes,no',



                'declaration1' => 'nullable|string|max:255',
                'declaration2' => 'nullable|string|max:255',
                // 'aadhaar_doc' => 'nullable|file|max:2048',
                // 'pancard_doc' => 'nullable|file|max:2048',
                // 'gst_doc' => 'nullable|file|max:2048',
            ];
        } else {
            // Final submission: all required fields
            $rules = [
                'applicant_name' => 'required|string|max:255',
                'business_address' => 'required|string|max:500',

                'application_ownershiptype' => 'required|string',

                'previous_contractor_license' => 'required|string|max:10',
                'previous_application_number' => 'nullable|string|max:50',
                'previous_validity_first_issue' => 'nullable',
                'previous_validity_from' => 'nullable',
                'previous_validity_to' => 'nullable',

                'bank_address' => 'required|string|max:500',
                'bank_validity' => 'required|date',
                'bank_amount' => 'required|numeric|min:0',

                'criminal_offence' => ['required', 'string', Rule::in(['yes', 'no'])],

                'form_name' => 'required|string|max:255',
                'license_name' => 'required|string|max:255',

                'declaration1' => 'required|string|max:255',
                'declaration2' => 'required|string|max:255',
                'address_proof' => $request->hasFile('address_proof') ? 'required|file|max:2048' : 'nullable|string',



            ];
        }
        // dd($request->all());
        // dd($request->appl_type);
        // exit;
        $validatedData = $request->validate($rules);

        // $validatedData['name_of_authorised_to_sign'] = !empty($request->name_of_authorised_to_sign)
        //     ? json_encode($request->name_of_authorised_to_sign)
        //     : null;

        // $validatedData['age_of_authorised_to_sign'] = !empty($request->age_of_authorised_to_sign)
        //     ? json_encode($request->age_of_authorised_to_sign)
        //     : null;

        // $validatedData['qualification_of_authorised_to_sign'] = !empty($request->qualification_of_authorised_to_sign)
        //     ? json_encode($request->qualification_of_authorised_to_sign)
        //     : null;

        // // Convert to uppercase for certain fields
        // foreach (
        //     [
        //         'applicant_name',
        //         'business_address',
        //         'authorised_name',
        //         'authorised_designation',
        //         'bank_address',
        //         'form_name',
        //         'license_name',

        //     ] as $field
        // ) {
        //     if (!empty($validatedData[$field])) {
        //         $validatedData[$field] = strtoupper($validatedData[$field]);
        //     }
        // }




        // Determine if record exists
        $applicationId = null;
        $existing = null;

        if ($recordId) {
            $existing = EA_Application_model::where('application_id', $recordId)->first();
            if ($existing) {
                $applicationId = $existing->application_id;
            }
        }
        if (!$applicationId) {
            $applicationId = $this->generateApplicationId(
                $request->appl_type,
                $request->form_name,
                $request->license_name
            );
        }

        DB::table('mapping_digi_cls')
            ->where('temp_app_id', $request->temp_app_id)
            ->update([
                'application_id' => $applicationId,
                'updated_at' => now(),
            ]);

        // Final data to save
        $dataToSave = $validatedData;
        $dataToSave['application_id'] = $applicationId;
        $dataToSave['login_id'] = $request->login_id_store;
        // if ($request->appl_type === 'D') {
        //     $dataToSave['payment_status'] = $isDraft ? 'draft' : 'paid';
        // } else {
        $dataToSave['payment_status'] = $isDraft ? 'draft' : 'paid';
        // }
        $dataToSave['application_status'] = 'P';
        // $dataToSave['created_at'] = now();
        $dataToSave['updated_at'] = DB::raw('NOW()');

        $appl_type = preg_replace('/\s+/', '', $request->appl_type ?? '');

        // dd($request->appl_type); exit;
        // Addressproof----------------

        if (!empty($request->type_doc) || !empty($request->addressproofno)) {

            $addressproof = [
                'application_id' => $applicationId,
                'login_id'       => $request->login_id_store,
                'form_name'  => $request->form_name,
                'license_name'    => $request->license_name,
                'addressproofno'    => $request->addressproofno,
                'type_doc'    => $request->type_doc,

                // 'status'         => '1'
            ];


            // dd([
            //     'login_id_store' => $request->login_id_store,
            //     'bank_address' => $request->bank_address,
            //     'bank_validity' => $request->bank_validity,
            //     'bank_amount' => $request->bank_amount,
            // ]);


            $existingaddress = Tnelb_Addressproof_cl::where('application_id', $applicationId)->first();

            // dd($existingBank);
            // exit;

            if ($existingaddress) {
                $existingaddress->update($addressproof);
            } else {
                Tnelb_Addressproof_cl::create($addressproof);
            }
        }





        // 🔹 Save Bank Solvency data in separate table
        if (!empty($request->bank_address) || !empty($request->bank_validity) || !empty($request->bank_amount)) {


            $bankData = [
                'application_id' => $applicationId,
                'login_id'       => $request->login_id_store,
                'bank_address'   => strtoupper($request->bank_address) ?? null,
                'bank_validity'  => $request->bank_validity ?? null,
                'bank_amount'    => $request->bank_amount ?? null,
                'status'         => '1'
            ];
            //             dd($request->login_id_store);
            // exit;

            // dd([
            //     'login_id_store' => $request->login_id_store,
            //     'bank_address' => $request->bank_address,
            //     'bank_validity' => $request->bank_validity,
            //     'bank_amount' => $request->bank_amount,
            // ]);


            $existingBank = Tnelb_banksolvency_a::where('application_id', $applicationId)->first();

            // dd($existingBank);
            // exit;

            if ($existingBank) {
                $existingBank->update($bankData);
            } else {
                Tnelb_banksolvency_a::create($bankData);
            }
        }


        // 1. Remove old rows for this application
        Equipmentforma_tbl::where('application_id', $applicationId)
            ->where('login_id', $request->login_id_store)
            ->delete();

        // 2. Insert fresh rows
        foreach ($request->equipments as $row) {

            Equipmentforma_tbl::create([
                'application_id'   => $applicationId,
                'login_id'         => $request->login_id_store,
                'equip_id'         => $row['equip_id'],
                'licence_id'       => $row['licence_id'],
                'form_name'        => $request->form_name,
                'equipment_value'  => $row['value'] ?? 'no',
                'ipaddress'        => $request->ip(),
            ]);
        }

        $Tnelb_cl_validitycheck_existing = Tnelb_cl_validitycheck::where('application_id', $applicationId)
            ->where('login_id', $request->login_id_store)
            ->first();

        $data = [
            'application_id' => $applicationId,
            'login_id'       => $request->login_id_store,
            'form_name'      => $request->form_name,
            'license_name'   => $request->license_name,
            'check_value'    => $request->check_value,
            'ipaddress'      => $request->ip(),
        ];

        if ($Tnelb_cl_validitycheck_existing) {

            $Tnelb_cl_validitycheck_existing->update($data);
        } else {

            Tnelb_cl_validitycheck::create($data);
        }


        //  dd($applicationId);
        //         exit;

        unset($dataToSave['bank_address'], $dataToSave['bank_validity'], $dataToSave['bank_amount']);


        // -----------QC process---------------

        $processedStaffIdsQC = [];

        //     if ($request->has('staffqc_name')) {


        //         $staffIdsFromForm = $request->staffqc_id ?? [];

        //         $existingStaffIds = Tnelb_EA_QC_model::where(
        //             'application_id',
        //             $applicationId
        //         )->pluck('id')->toArray();

        //         // Get the current maximum row_index for this application
        //         $maxRowIndex = Tnelb_EA_QC_model::where(
        //             'application_id',
        //             $applicationId
        //         )->max('row_index');

        //         $nextRowIndex = ((int) $maxRowIndex) + 1;

        //         $qc_code = 1;

        //         foreach ($request->staffqc_name as $index => $staffName) {

        //             if (
        //                 empty($staffName) &&
        //                 empty($request->cc_qc_number[$index] ?? null) &&
        //                 empty($request->cc_qc_validity[$index] ?? null)
        //             ) {
        //                 continue;
        //             }

        //             $staffId = $staffIdsFromForm[$index] ?? null;

        //             $validity = $request->cc_qc_validity[$index] ?? null;

        //             /*
        //     |--------------------------------------------------------------------------
        //     | Existing record
        //     |--------------------------------------------------------------------------
        //     */
        //             if ($staffId && in_array($staffId, $existingStaffIds)) {

        //                 $existingStaff = Tnelb_EA_QC_model::find($staffId);

        //                 $staffData = [
        //                     'application_id'    => $applicationId,
        //                     'login_id'          => $request->login_id_store,
        //                     'form_name'         => $request->form_name,
        //                     'license_name'      => $request->license_name,
        //                     'staffname'         => strtoupper($staffName),
        //                     'staffqc_category'  => strtoupper(
        //                         $request->staffqc_category[$index] ?? ''
        //                     ),
        //                     'cc_qc_number'      => strtoupper(
        //                         $request->cc_qc_number[$index] ?? ''
        //                     ),
        //                     'cc_qc_validity'    => $validity,
        //                     'qc_code'           => $qc_code,
        //                     'flag'              => '1',
        //                 ];

        //                 $existingStaff->update($staffData);

        //                 // Keep existing row_index unchanged
        //                 $processedStaffIdsQC[] = $staffId;
        //             }

        //             /*
        //     |--------------------------------------------------------------------------
        //     | New record
        //     |--------------------------------------------------------------------------
        //     */ else {

        //                 $staffData = [
        //                     'application_id'    => $applicationId,
        //                     'login_id'          => $request->login_id_store,
        //                     'form_name'         => $request->form_name,
        //                     'license_name'      => $request->license_name,
        //                     'staffname'         => strtoupper($staffName),
        //                     'staffqc_category'  => strtoupper(
        //                         $request->staffqc_category[$index] ?? ''
        //                     ),
        //                     'cc_qc_number'      => strtoupper(
        //                         $request->cc_qc_number[$index] ?? ''
        //                     ),
        //                     'cc_qc_validity'    => $validity,
        //                     'qc_code'           => $qc_code,
        //                     'row_index'         => $nextRowIndex,
        //                     'flag'              => '1',
        //                 ];

        //                 $newStaff = Tnelb_EA_QC_model::create($staffData);

        //                 $processedStaffIdsQC[] = $newStaff->id;

        //                 // Next new record gets next sequence number
        //                 $nextRowIndex++;
        //             }

        //             $qc_code++;
        //         }

        //         /*
        // |--------------------------------------------------------------------------
        // | Delete records removed from the form
        // |--------------------------------------------------------------------------
        // */
        //         Tnelb_EA_QC_model::where('application_id', $applicationId)
        //             ->whereNotIn('id', $processedStaffIdsQC)
        //             ->delete();



        //         $tempDocs = DB::table('tnelb_temp_uploaded_documents')
        //             ->where('login_id', $request->login_id_store)
        //             ->where('form_name', $request->form_name)
        //             ->where('license_name', $request->license_name)
        //             ->where('document_category', 'qc_doc')
        //             ->where('appl_type', 'N')
        //             // ->whereIn('is_final', ['0', '2'])

        //             ->get();

        //         // dd($tempDocs->pluck('qc_code')->toArray());

        //         foreach ($tempDocs as $tempDoc) {




        //             $matchedPartner = Tnelb_EA_QC_model::where('application_id', $applicationId)

        //                 ->where('qc_code', $tempDoc->qc_code)
        //                 ->first();

        //             // dd($matchedPartner); exit;

        //             if (!$matchedPartner) {
        //                 continue; // No match → skip
        //             }

        //             // -----------------------------------------
        //             // 4️⃣ GET FINAL PRO PATH
        //             // -----------------------------------------
        //             $dynamicRequest = clone $request;
        //             $dynamicRequest->merge([
        //                 'module' => $tempDoc->module
        //             ]);

        //             $dbFilePath_all = DocPathController::getPath($dynamicRequest);
        //             $dbFilePath     = $dbFilePath_all->filepath_pro;

        //             $tempFullPath = public_path(
        //                 $tempDoc->file_path . '/' . $tempDoc->file_name
        //             );

        //             $proFolderPath = public_path($dbFilePath);

        //             if (!File::exists($proFolderPath)) {
        //                 File::makeDirectory($proFolderPath, 0755, true);
        //             }

        //             $proFullPath = $proFolderPath . '/' . $tempDoc->file_name;

        //             // dd($dbFilePath);exit;

        //             // -----------------------------------------
        //             // 5️⃣ COPY FILE
        //             // -----------------------------------------
        //             if (File::exists($tempFullPath)) {
        //                 File::copy($tempFullPath, $proFullPath);
        //             } else {
        //                 continue;
        //             }

        //             // dd($tempDoc->file_path . '/' . $tempDoc->file_name);
        //             // exit;

        //             // -----------------------------------------
        //             // 6️⃣ SAVE FILE NAME INTO MATCHED PARTNER
        //             // -----------------------------------------
        //             $matchedPartner->qc_document = $dbFilePath_all->filepath_pro . $tempDoc->file_name;

        //             // dd($matchedPartner->qc_document);exit;
        //             // $matchedPartner->qc_code = $tempDoc->qc_code;
        //             $matchedPartner->save();

        //             // -----------------------------------------
        //             // 7️⃣ MARK TEMP DOC AS FINAL
        //             // -----------------------------------------
        //             DB::table('tnelb_temp_uploaded_documents')
        //                 ->where('id', $tempDoc->id)
        //                 ->update([
        //                     'is_final'   => '1',
        //                     'moved_as'   => $request->input('form_action'),
        //                     'record_id_app' => $applicationId,
        //                     'updated_at' => now()
        //                 ]);
        //         }
        //     }

        // QC/QSC----------------

        if ($request->has('staffqc_category')) {
            // dd('qc'); exit;

            $staffCategories = $request->input('staffqc_category', []);
            $staffCcNos = $request->input('staff_cc_no', []);
            $staffFirstIssues = $request->input('staff_cc_first_issue', []);
            $staffValidityFroms = $request->input('staff_cc_validity_from', []);
            $staffValidityTos = $request->input('staff_cc_validity_to', []);

            $staffQcIds = $request->input('staffqc_id', []);
            $rowIndexes = $request->input('row_index', []);

            /*
    |--------------------------------------------------------------------------
    | GET NEXT ROW INDEX
    |--------------------------------------------------------------------------
    */

            $lastRowIndex = DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->max('row_index');

            $nextRowIndex = ((int) $lastRowIndex) + 1;

            /*
    |--------------------------------------------------------------------------
    | ROW INDEXES PRESENT IN CURRENT FORM
    |--------------------------------------------------------------------------
    */

            $currentRowIndexes = [];

            foreach ($staffCategories as $index => $category) {

                $category = strtoupper(trim((string) $category));

                /*
        |--------------------------------------------------------------------------
        | Only QC / QSC
        |--------------------------------------------------------------------------
        */

                if (!in_array($category, ['QC', 'QSC'], true)) {
                    continue;
                }

                $ccNo = trim((string) ($staffCcNos[$index] ?? ''));

                /*
        |--------------------------------------------------------------------------
        | Ignore empty / undefined certificate number
        |--------------------------------------------------------------------------
        */

                if (
                    $ccNo === '' ||
                    strtolower($ccNo) === 'undefined' ||
                    strtolower($ccNo) === 'null'
                ) {
                    continue;
                }

                $firstIssue = trim((string) ($staffFirstIssues[$index] ?? ''));
                $validityFrom = trim((string) ($staffValidityFroms[$index] ?? ''));
                $validityTo = trim((string) ($staffValidityTos[$index] ?? ''));



                $staffId = $staffQcIds[$index] ?? null;
                $submittedRowIndex = $rowIndexes[$index] ?? null;

                /*
        |--------------------------------------------------------------------------
        | EXISTING RECORD
        |--------------------------------------------------------------------------
        */

                /*
|--------------------------------------------------------------------------
| EXISTING RECORD
|--------------------------------------------------------------------------
*/

                if (!empty($staffId)) {

                    $existing = DB::table('cl_staff_tbl')
                        ->where('id', $staffId)
                        ->where('application_id', $applicationId)
                        ->whereIn('staff_category', ['QC', 'QSC'])
                        ->first();

                    if ($existing) {

                        /*
        |--------------------------------------------------------------------------
        | Keep existing values if POST value is empty
        |--------------------------------------------------------------------------
        */

                        $updateData = [
                            'staff_category' => $category,
                            'staff_cc_no' => $ccNo,
                            'staff_flag' => '1',
                            'updated_at' => now(),
                        ];

                        if ($firstIssue !== '') {
                            $updateData['staff_cc_first_issue'] = $firstIssue;
                        }

                        if ($validityFrom !== '') {
                            $updateData['staff_cc_validity_from'] = $validityFrom;
                        }

                        if ($validityTo !== '') {
                            $updateData['staff_cc_validity_to'] = $validityTo;
                        }

                        DB::table('cl_staff_tbl')
                            ->where('id', $staffId)
                            ->where('application_id', $applicationId)
                            ->update($updateData);

                        $currentRowIndexes[] = (int) $existing->row_index;
                    }
                }

                /*
        |--------------------------------------------------------------------------
        | NEW RECORD
        |--------------------------------------------------------------------------
        */ else {

                    // dd('new qc'); exit;

                    $newRowIndex = $nextRowIndex;

                    DB::table('cl_staff_tbl')->insert([
                        'login_id' => $request->input('login_id_store'),
                        'application_id' => $applicationId,
                        'staff_category' => $category,
                        'staff_cc_no' => $ccNo,

                        'staff_cc_first_issue' =>
                        $firstIssue !== '' ? $firstIssue : null,

                        'staff_cc_validity_from' =>
                        $validityFrom !== '' ? $validityFrom : null,

                        'staff_cc_validity_to' =>
                        $validityTo !== '' ? $validityTo : null,

                        'row_index' => $newRowIndex,
                        'staff_flag' => '1',
                        'staff_status' => 'N',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $currentRowIndexes[] = $newRowIndex;

                    $nextRowIndex++;
                }
            }

            /*
    |--------------------------------------------------------------------------
    | SET MISSING QC / QSC RECORDS TO staff_flag = 0
    |--------------------------------------------------------------------------
    |
    | Existing DB records which are NOT present in the submitted form
    | will be marked as 0.
    |
    */

            DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->whereIn('staff_category', ['QC', 'QSC'])
                ->where('staff_flag', '1')
                ->whereNotIn('row_index', $currentRowIndexes)
                ->update([
                    'staff_flag' => '0',
                    'updated_at' => now(),
                ]);
        }


        if ($request->has('cc_number')) {

            $processedStaffIds = [];

            $staffIdsFromForm = $request->input('staff_id', []);
            $rowIndexesFromForm = $request->input('row_index', []);

            // ==========================================================
            // ROW INDEXES PRESENT IN CURRENT FORM
            // ==========================================================

            $submittedRowIndexes = [];

            foreach ($rowIndexesFromForm as $rowIndex) {

                if (
                    $rowIndex !== null &&
                    $rowIndex !== '' &&
                    strtolower(trim((string) $rowIndex)) !== 'undefined' &&
                    strtolower(trim((string) $rowIndex)) !== 'null'
                ) {
                    $submittedRowIndexes[] = (int) $rowIndex;
                }
            }

            $submittedRowIndexes = array_values(
                array_unique($submittedRowIndexes)
            );


            // ==========================================================
            // EXISTING B / C / OTHERS STAFF ONLY
            // QC / QSC WILL NOT BE TOUCHED
            // ==========================================================

            $existingStaffIds = DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->whereIn('staff_category', ['B', 'C', 'OTHERS'])
                ->pluck('id')
                ->toArray();


            // ==========================================================
            // MARK REMOVED B / C / OTHERS STAFF AS staff_flag = 0
            // QC / QSC WILL NOT BE UPDATED
            // ==========================================================

            DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->whereIn('staff_category', ['B', 'C', 'OTHERS'])
                ->when(
                    !empty($submittedRowIndexes),
                    function ($query) use ($submittedRowIndexes) {
                        $query->whereNotIn(
                            'row_index',
                            $submittedRowIndexes
                        );
                    },
                    function ($query) {
                        $query->whereNotNull('row_index');
                    }
                )
                ->update([
                    'staff_flag' => '0',
                    'updated_at' => now(),
                ]);


            // ==========================================================
            // CURRENT MAX ROW INDEX
            // ==========================================================

            $lastRowIndex = DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->max('row_index');

            $nextRowIndex = ((int) $lastRowIndex) + 1;


            // ==========================================================
            // LOOP CURRENT B / C / OTHERS STAFF
            // ==========================================================

            foreach ($request->input('staff_category', []) as $index => $category) {

                // ----------------------------------------------------------
                // CATEGORY
                // ----------------------------------------------------------

                $category = is_array($category)
                    ? ($category[0] ?? null)
                    : $category;

                $category = strtoupper(trim((string) $category));


                // ----------------------------------------------------------
                // ONLY B / C / OTHERS
                // QC / QSC WILL NOT BE PROCESSED HERE
                // ----------------------------------------------------------

                if (!in_array($category, ['B', 'C', 'OTHERS'], true)) {
                    continue;
                }


                // ----------------------------------------------------------
                // GET VALUES USING SAME INDEX
                // ----------------------------------------------------------

                $ccNumber = $request->input("cc_number.$index");

                $firstIssue = $request->input("cc_firstissue.$index");

                $validityFrom = $request->input("cc_validity_from.$index");

                $validityTo = $request->input("cc_validity_to.$index");

                $designation = $request->input("designation.$index");

                $staffId = $staffIdsFromForm[$index] ?? null;

                $rowIndex = $rowIndexesFromForm[$index] ?? null;


                // ----------------------------------------------------------
                // NORMALIZE STAFF ID
                // ----------------------------------------------------------

                if (
                    $staffId !== null &&
                    $staffId !== '' &&
                    strtolower(trim((string) $staffId)) !== 'undefined' &&
                    strtolower(trim((string) $staffId)) !== 'null'
                ) {
                    $staffId = (int) $staffId;
                } else {
                    $staffId = null;
                }


                // ----------------------------------------------------------
                // NORMALIZE ROW INDEX
                // ----------------------------------------------------------

                if (
                    $rowIndex !== null &&
                    $rowIndex !== '' &&
                    strtolower(trim((string) $rowIndex)) !== 'undefined' &&
                    strtolower(trim((string) $rowIndex)) !== 'null'
                ) {
                    $rowIndex = (int) $rowIndex;
                } else {
                    $rowIndex = null;
                }


                // ----------------------------------------------------------
                // SKIP COMPLETELY EMPTY ROW
                // ----------------------------------------------------------

                if (
                    empty($category) &&
                    empty($ccNumber) &&
                    empty($firstIssue) &&
                    empty($validityFrom) &&
                    empty($validityTo) &&
                    empty($designation)
                ) {
                    continue;
                }


                // ----------------------------------------------------------
                // STAFF DATA
                // ----------------------------------------------------------

                $staffData = [

                    'application_id' =>
                    $applicationId,

                    'login_id' =>
                    $request->input('login_id_store'),

                    'staff_category' =>
                    $category,

                    'staff_cc_no' =>
                    strtoupper(trim((string) ($ccNumber ?? ''))),

                    'staff_cc_first_issue' =>
                    $firstIssue,

                    'staff_cc_validity_from' =>
                    $validityFrom,

                    'staff_cc_validity_to' =>
                    $validityTo,

                    'staff_status' =>
                    'N',

                    'staff_flag' =>
                    '1',

                    'staff_designation' =>
                    !empty($designation)
                        ? trim((string) $designation)
                        : null,

                    'updated_at' =>
                    now(),
                ];


                // ----------------------------------------------------------
                // OTHERS
                // ----------------------------------------------------------

                if ($category === 'OTHERS') {

                    $staffData['staff_designation'] =
                        trim((string) ($designation ?? ''));

                    $staffData['staff_cc_no'] = null;

                    $staffData['staff_cc_first_issue'] = null;

                    $staffData['staff_cc_validity_from'] = null;

                    $staffData['staff_cc_validity_to'] = null;
                }


                // ----------------------------------------------------------
                // EXISTING STAFF -> UPDATE
                // ----------------------------------------------------------

                if (
                    !empty($staffId) &&
                    in_array($staffId, $existingStaffIds, true)
                ) {

                    // Existing row MUST retain its own row_index
                    $staffData['row_index'] = $rowIndex;

                    DB::table('cl_staff_tbl')
                        ->where('id', $staffId)
                        ->where('application_id', $applicationId)
                        ->whereIn(
                            'staff_category',
                            ['B', 'C', 'OTHERS']
                        )
                        ->update($staffData);

                    $processedStaffIds[] = $staffId;
                }


                // ----------------------------------------------------------
                // NEW STAFF -> INSERT
                // ----------------------------------------------------------

                else {

                    $staffData['row_index'] = $nextRowIndex;

                    $staffData['staff_flag'] = '1';

                    $staffData['staff_status'] = 'NA';

                    $staffData['created_at'] = now();

                    $newStaffId = DB::table('cl_staff_tbl')
                        ->insertGetId($staffData);

                    $processedStaffIds[] = $newStaffId;

                    if (
                        in_array($category, ['B', 'C'], true) &&
                        !empty($staffData['staff_cc_no'])
                    ) {

                        DB::table('cl_staff_detail_adminstore')->insert([
                            'application_id' => $applicationId,

                            'staff_cc_no' => $staffData['staff_cc_no'],

                            'staff_cc_first_issue' =>
                            !empty($firstIssue) ? $firstIssue : null,

                            'staff_cc_validity_from' =>
                            !empty($validityFrom) ? $validityFrom : null,

                            'staff_cc_validity_to' =>
                            !empty($validityTo) ? $validityTo : null,

                            'verify_flag'  => 0,
                            'processed_by' => null,

                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    $nextRowIndex++;
                }
            }
        }


        $newProprietorIds = [];
        if ($request->has('proprietor_name')) {

            // dd($request->all());
            // exit;
            $count = 1;
            foreach ($request->proprietor_name as $index => $name) {

                if (
                    empty($name) ||
                    strtolower(trim($name)) === 'undefined' ||
                    trim($name) === 'null'
                ) {
                    continue;
                }
                $competencyHolding = data_get($request->competency, $index);
                $proprietorId = $request->proprietor_id[$index] ?? null;
                // dd([
                //     'proprietor_name' => $request->proprietor_name,
                //     'dob' => $request->dob,
                //     'age' => $request->age,
                //     'qualification' => $request->qualification,
                //     'qual_text' => $request->qual_text,
                //     'proprietor_address' => $request->proprietor_address,
                //     'fathers_name' => $request->fathers_name,
                //     'present_business' => $request->present_business,
                //     'competency_certificate_holding' => $competencyHolding,
                //     'competency_certificate_number' => strtoupper(data_get($request->competency_certno, $index, '')),

                //    'competency_certificate_first_issue' => data_get($request->ccfirstissue, $index),


                //     'competency_certificate_validity_from' => data_get($request->ccvalidityfrom, $index),


                //     'competency_certificate_validity_to' => data_get($request->ccvalidityto, $index),

                // ]);
                // exit;



                $data = [
                    'login_id' => $request->login_id_store,
                    'application_id' => $applicationId,

                    'proprietor_name' => strtoupper($name ?? ''),

                    'ownership_type' => 'pr',

                    'proprietor_address' => strtoupper(
                        data_get($request->proprietor_address, $index, '')
                    ),

                    'dob' => data_get($request->dob, $index),

                    'age' => data_get($request->age, $index),

                    'qualification' => strtoupper(
                        data_get($request->qualification, $index, '')
                    ),

                    'qualification_text' => strtoupper(
                        data_get($request->qual_text, $index, '')
                    ),

                    'fathers_name' => strtoupper(
                        data_get($request->fathers_name, $index, '')
                    ),

                    'present_business' => strtoupper(
                        data_get($request->present_business, $index, '')
                    ),

                    // ==========================================
                    // COMPETENCY CERTIFICATE
                    // ==========================================

                    'competency_certificate_holding' => $competencyHolding,

                    'competency_certificate_number' =>
                    $competencyHolding === 'yes'
                        ? strtoupper(
                            data_get($request->competency_certno, $index, '')
                        )
                        : null,

                    'competency_certificate_first_issue' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->ccfirstissue, $index)
                        : null,

                    'competency_certificate_validity_from' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->ccvalidityfrom, $index)
                        : null,

                    'competency_certificate_validity_to' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->ccvalidityto, $index)
                        : null,

                    'proprietor_flag' => 1,

                    'ownership_count' => $count,
                ];


                if ($proprietorId) {
                    ProprietorformA::where('id', $proprietorId)->update($data);
                    $newProprietorIds[] = $proprietorId;
                } else {
                    $new = ProprietorformA::create($data);
                    $newProprietorIds[] = $new->id;
                }
            }
            $count++;

            // Deactivate removed rows
            ProprietorformA::where('application_id', $applicationId)
                ->whereNotIn('id', $newProprietorIds)
                ->where('ownership_type', 'pr')
                ->update(['proprietor_flag' => 0]);

            // table move edu_file----------

            // $recordexists = DB::table('cl_ownership_table')
            //             ->where('application_id', $applicationId)
            //             ->get();
            // if ($recordexists) {

            //              DB::table('$cl_ownership_table')
            //             ->where('application_id', $applicationId)
            //             ->update([
            //                 'educ_qual_proof' => $finalDbPath,
            //                 'updated_at'  => now()
            //             ]);
            //         }


            $tempDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'educ_qual_proof')
                ->where('ownership_type', 'pr')
                // ->whereIn('is_final', ['0', '2'])

                ->get();

            foreach ($tempDocs as $tempDoc) {


                $matchedPartner = ProprietorformA::where('application_id', $applicationId)
                    ->where('ownership_type', 'pr')
                    ->where('ownership_count', $tempDoc->row_index + 1)
                    ->first();

                if (!$matchedPartner) {
                    continue; // No match → skip
                }

                // -----------------------------------------
                // 4️⃣ GET FINAL PRO PATH
                // -----------------------------------------
                $dynamicRequest = clone $request;
                $dynamicRequest->merge([
                    'module' => $tempDoc->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath     = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDoc->file_path . '/' . $tempDoc->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory($proFolderPath, 0755, true);
                }

                $proFullPath = $proFolderPath . '/' . $tempDoc->file_name;

                // dd($dbFilePath);exit;

                // -----------------------------------------
                // 5️⃣ COPY FILE
                // -----------------------------------------
                if (File::exists($tempFullPath)) {
                    File::copy($tempFullPath, $proFullPath);
                } else {
                    continue;
                }

                // dd($tempDoc->file_path . '/' . $tempDoc->file_name);
                // exit;

                // -----------------------------------------
                // 6️⃣ SAVE FILE NAME INTO MATCHED PARTNER
                // -----------------------------------------
                $matchedPartner->educational_proof = $dbFilePath_all->filepath_pro . $tempDoc->file_name;

                // dd($matchedPartner->educational_proof);exit;
                $matchedPartner->row_index = $tempDoc->row_index;
                $matchedPartner->save();

                // -----------------------------------------
                // 7️⃣ MARK TEMP DOC AS FINAL
                // -----------------------------------------
                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDoc->id)
                    ->update([
                        'is_final'   => '1',
                        'moved_as'   => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }


            // ------Age Proof-------------------
            $tempDocsAge = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'age_proof')
                ->where('ownership_type', 'pr')
                // ->whereIn('is_final', ['0', '2'])

                ->get();

            foreach ($tempDocsAge as $tempDocAge) {


                $matchedPartner = ProprietorformA::where('application_id', $applicationId)
                    ->where('ownership_type', 'pr')
                    ->where('ownership_count', $tempDocAge->row_index + 1)
                    ->first();

                if (!$matchedPartner) {
                    continue; // No match → skip
                }

                // -----------------------------------------
                // 4️⃣ GET FINAL PRO PATH
                // -----------------------------------------
                $dynamicRequest = clone $request;
                $dynamicRequest->merge([
                    'module' => $tempDocAge->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath     = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDocAge->file_path . '/' . $tempDocAge->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory($proFolderPath, 0755, true);
                }

                $proFullPath = $proFolderPath . '/' . $tempDocAge->file_name;

                // dd($dbFilePath);exit;

                // -----------------------------------------
                // 5️⃣ COPY FILE
                // -----------------------------------------
                if (File::exists($tempFullPath)) {
                    File::copy($tempFullPath, $proFullPath);
                } else {
                    continue;
                }

                // dd($tempDocAge->file_path . '/' . $tempDocAge->file_name);
                // exit;

                // -----------------------------------------
                // 6️⃣ SAVE FILE NAME INTO MATCHED PARTNER
                // -----------------------------------------
                $matchedPartner->age_proof = $dbFilePath_all->filepath_pro . $tempDocAge->file_name;

                // dd($matchedPartner->educational_proof);exit;
                $matchedPartner->row_index = $tempDocAge->row_index;
                $matchedPartner->save();

                // -----------------------------------------
                // 7️⃣ MARK TEMP DOC AS FINAL
                // -----------------------------------------
                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDocAge->id)
                    ->update([
                        'is_final'   => '1',
                        'moved_as'   => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }
        }

        // Partners
        $newPartnerIds = [];

        if ($request->has('partner_name')) {

            $count = 1;

            foreach ($request->partner_name as $index => $name) {

                if (
                    empty($name) ||
                    strtolower(trim($name)) === 'undefined' ||
                    trim($name) === 'null'
                ) {
                    continue;
                }

                $competencyHolding = data_get($request->partner_competency, $index);
                $partnerId = $request->partner_id[$index] ?? null;

                $data = [
                    'login_id' => $request->login_id_store,
                    'application_id' => $applicationId,

                    'proprietor_name' => strtoupper($name ?? ''),

                    'ownership_type' => 'pt',

                    'proprietor_address' => strtoupper(
                        data_get($request->partner_proprietor_address, $index, '')
                    ),

                    'dob' => data_get($request->partner_dob, $index),

                    'age' => data_get($request->partner_age, $index),

                    'qualification' => strtoupper(
                        data_get($request->partner_qualification, $index, '')
                    ),

                    'qualification_text' => strtoupper(
                        data_get($request->partner_qual_text, $index, '')
                    ),

                    'fathers_name' => strtoupper(
                        data_get($request->partner_fathers_name, $index, '')
                    ),

                    'present_business' => strtoupper(
                        data_get($request->partner_present_business, $index, '')
                    ),

                    // ==========================================
                    // COMPETENCY CERTIFICATE
                    // ==========================================

                    'competency_certificate_holding' => $competencyHolding,

                    'competency_certificate_number' =>
                    $competencyHolding === 'yes'
                        ? strtoupper(
                            data_get($request->partner_competency_certno, $index, '')
                        )
                        : null,

                    'competency_certificate_first_issue' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->partner_ccfirstissue, $index)
                        : null,

                    'competency_certificate_validity_from' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->partner_ccvalidityfrom, $index)
                        : null,

                    'competency_certificate_validity_to' =>
                    $competencyHolding === 'yes'
                        ? data_get($request->partner_ccvalidityto, $index)
                        : null,



                    'proprietor_flag' => 1,

                    'ownership_count' => $count,
                ];

                if ($partnerId) {

                    ProprietorformA::where('id', $partnerId)
                        ->update($data);

                    $newPartnerIds[] = $partnerId;
                } else {

                    $new = ProprietorformA::create($data);

                    $newPartnerIds[] = $new->id;
                }

                $count++;
            }

            // Deactivate removed partner rows
            ProprietorformA::where('application_id', $applicationId)
                ->whereNotIn('id', $newPartnerIds)
                ->where('ownership_type', 'pt')
                ->update([
                    'proprietor_flag' => 0
                ]);

            // ==========================================
            // EDUCATIONAL QUALIFICATION PROOF
            // ==========================================

            $tempDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'educ_qual_proof')
                ->where('ownership_type', 'pt')
                ->get();

            foreach ($tempDocs as $tempDoc) {

                $matchedPartner = ProprietorformA::where('application_id', $applicationId)
                    ->where('ownership_type', 'pt')
                    ->where('ownership_count', $tempDoc->row_index + 1)
                    ->first();

                if (!$matchedPartner) {
                    continue;
                }

                $dynamicRequest = clone $request;

                $dynamicRequest->merge([
                    'module' => $tempDoc->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDoc->file_path . '/' . $tempDoc->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory($proFolderPath, 0755, true);
                }

                $proFullPath = $proFolderPath . '/' . $tempDoc->file_name;

                if (File::exists($tempFullPath)) {
                    File::copy($tempFullPath, $proFullPath);
                } else {
                    continue;
                }

                $matchedPartner->educational_proof =
                    $dbFilePath_all->filepath_pro . $tempDoc->file_name;

                $matchedPartner->row_index = $tempDoc->row_index;

                $matchedPartner->save();

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDoc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }

            // ==========================================
            // AGE PROOF
            // ==========================================

            $tempDocsAge = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'age_proof')
                ->where('ownership_type', 'pt')
                ->get();

            foreach ($tempDocsAge as $tempDocAge) {

                $matchedPartner = ProprietorformA::where('application_id', $applicationId)
                    ->where('ownership_type', 'pt')
                    ->where('ownership_count', $tempDocAge->row_index + 1)
                    ->first();

                if (!$matchedPartner) {
                    continue;
                }

                $dynamicRequest = clone $request;

                $dynamicRequest->merge([
                    'module' => $tempDocAge->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDocAge->file_path . '/' . $tempDocAge->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory($proFolderPath, 0755, true);
                }

                $proFullPath = $proFolderPath . '/' . $tempDocAge->file_name;

                if (File::exists($tempFullPath)) {
                    File::copy($tempFullPath, $proFullPath);
                } else {
                    continue;
                }

                $matchedPartner->age_proof =
                    $dbFilePath_all->filepath_pro . $tempDocAge->file_name;

                $matchedPartner->row_index = $tempDocAge->row_index;

                $matchedPartner->save();

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDocAge->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }
        }
        // ----------------director------------------------
        $newdirectorIds = [];

        if ($request->has('director_name')) {

            $count = 1;

            foreach ($request->director_name as $index => $name) {

                if (
                    empty($name) ||
                    strtolower(trim($name)) === 'undefined' ||
                    trim($name) === 'null'
                ) {
                    continue;
                }

                $directorId = $request->director_id[$index] ?? null;

                $competencyHolding = data_get(
                    $request->director_competency,
                    $index,
                    'no'
                );

                $data = [

                    'login_id' => $request->login_id_store,

                    'application_id' => $applicationId,

                    'proprietor_name' => strtoupper($name ?? ''),

                    'ownership_type' => 'dr',

                    'ownership_count' => $count,

                    'proprietor_flag' => 1,

                    // ==========================================
                    // MANAGING DIRECTOR
                    // ==========================================

                    'managing_director' => strtoupper(
                        data_get(
                            $request->director_managing_director,
                            $index,
                            'NO'
                        )
                    ),

                    // ==========================================
                    // BASIC DETAILS
                    // ==========================================

                    'fathers_name' => strtoupper(
                        data_get(
                            $request->director_fathers_name,
                            $index,
                            ''
                        )
                    ),

                    'proprietor_address' => strtoupper(
                        data_get(
                            $request->director_proprietor_address,
                            $index,
                            ''
                        )
                    ),

                    'dob' => data_get(
                        $request->director_dob,
                        $index
                    ),

                    'age' => data_get(
                        $request->director_age,
                        $index
                    ),

                    // ==========================================
                    // QUALIFICATION
                    // ==========================================

                    'qualification' => strtoupper(
                        data_get(
                            $request->director_qualification,
                            $index,
                            ''
                        )
                    ),

                    'qualification_text' => strtoupper(
                        data_get(
                            $request->director_qual_text,
                            $index,
                            ''
                        )
                    ),

                    // ==========================================
                    // BUSINESS
                    // ==========================================

                    'present_business' => strtoupper(
                        data_get(
                            $request->director_present_business,
                            $index,
                            ''
                        )
                    ),

                    // ==========================================
                    // COMPETENCY CERTIFICATE
                    // ==========================================

                    'competency_certificate_holding' => $competencyHolding,

                    'competency_certificate_number' =>
                    $competencyHolding === 'yes'
                        ? strtoupper(
                            data_get(
                                $request->director_competency_certno,
                                $index,
                                ''
                            )
                        )
                        : null,

                    'competency_certificate_first_issue' =>
                    $competencyHolding === 'yes'
                        ? data_get(
                            $request->director_ccfirstissue,
                            $index
                        )
                        : null,

                    'competency_certificate_validity_from' =>
                    $competencyHolding === 'yes'
                        ? data_get(
                            $request->director_ccvalidityfrom,
                            $index
                        )
                        : null,

                    'competency_certificate_validity_to' =>
                    $competencyHolding === 'yes'
                        ? data_get(
                            $request->director_ccvalidityto,
                            $index
                        )
                        : null,


                ];

                // ==========================================
                // UPDATE / INSERT
                // ==========================================

                if (!empty($directorId)) {

                    ProprietorformA::where('id', $directorId)
                        ->update($data);

                    $newdirectorIds[] = $directorId;
                } else {

                    $newDirector = ProprietorformA::create($data);

                    $newdirectorIds[] = $newDirector->id;
                }

                $count++;
            }

            // ==========================================
            // DEACTIVATE REMOVED DIRECTORS
            // ==========================================

            ProprietorformA::where('application_id', $applicationId)
                ->whereNotIn('id', $newdirectorIds)
                ->where('ownership_type', 'dr')
                ->update([
                    'proprietor_flag' => 0
                ]);

            // ==========================================
            // EDUCATIONAL QUALIFICATION PROOF
            // ==========================================

            $tempDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'educ_qual_proof')
                ->where('ownership_type', 'dr')
                ->get();

            foreach ($tempDocs as $tempDoc) {

                $matchedDirector = ProprietorformA::where(
                    'application_id',
                    $applicationId
                )
                    ->where('ownership_type', 'dr')
                    ->where(
                        'ownership_count',
                        $tempDoc->row_index + 1
                    )
                    ->first();

                if (!$matchedDirector) {
                    continue;
                }

                $dynamicRequest = clone $request;

                $dynamicRequest->merge([
                    'module' => $tempDoc->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDoc->file_path . '/' . $tempDoc->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory(
                        $proFolderPath,
                        0755,
                        true
                    );
                }

                $proFullPath = $proFolderPath . '/' . $tempDoc->file_name;

                if (File::exists($tempFullPath)) {
                    File::copy(
                        $tempFullPath,
                        $proFullPath
                    );
                } else {
                    continue;
                }

                $matchedDirector->educational_proof =
                    $dbFilePath_all->filepath_pro .
                    $tempDoc->file_name;

                $matchedDirector->row_index =
                    $tempDoc->row_index;

                $matchedDirector->save();

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDoc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }

            // ==========================================
            // AGE PROOF
            // ==========================================

            $tempDocsAge = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'age_proof')
                ->where('ownership_type', 'dr')
                ->get();

            foreach ($tempDocsAge as $tempDocAge) {

                $matchedDirector = ProprietorformA::where(
                    'application_id',
                    $applicationId
                )
                    ->where('ownership_type', 'dr')
                    ->where(
                        'ownership_count',
                        $tempDocAge->row_index + 1
                    )
                    ->first();

                if (!$matchedDirector) {
                    continue;
                }

                $dynamicRequest = clone $request;

                $dynamicRequest->merge([
                    'module' => $tempDocAge->module
                ]);

                $dbFilePath_all = DocPathController::getPath($dynamicRequest);
                $dbFilePath = $dbFilePath_all->filepath_pro;

                $tempFullPath = public_path(
                    $tempDocAge->file_path . '/' . $tempDocAge->file_name
                );

                $proFolderPath = public_path($dbFilePath);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory(
                        $proFolderPath,
                        0755,
                        true
                    );
                }

                $proFullPath = $proFolderPath . '/' . $tempDocAge->file_name;

                if (File::exists($tempFullPath)) {
                    File::copy(
                        $tempFullPath,
                        $proFullPath
                    );
                } else {
                    continue;
                }

                $matchedDirector->age_proof =
                    $dbFilePath_all->filepath_pro .
                    $tempDocAge->file_name;

                $matchedDirector->row_index =
                    $tempDocAge->row_index;

                $matchedDirector->save();

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDocAge->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId,
                        'updated_at' => now()
                    ]);
            }
        }




        if ($existing) {

            $updateData = collect($dataToSave)
                ->except(['aadhaar_doc', 'pancard_doc', 'gst_doc'])
                ->toArray();



            EA_Application_model::where('application_id', $existing->application_id)
                ->update($updateData);
        } else {
            $dataToSave['created_at'] = DB::raw('NOW()');

            $createData = collect($dataToSave)
                ->except(['aadhaar_doc', 'pancard_doc', 'gst_doc'])
                ->toArray();

            EA_Application_model::create($createData);
            $message = $isDraft ? 'Draft saved successfully!' : 'Application submitted successfully!';
        }
        $transactionId = 'TXN' . rand(100000, 999999);

        // $payment = $isDraft ? 'draft' : 'success';

        // ------------Temp Table move------------------------------------------
        DB::transaction(function () use ($applicationId, $request) {

            // dd('111');exit;


            $pathData = DocPathController::getPath($request);
            $proFolderPath = public_path($pathData->filepath_pro);

            if (!File::exists($proFolderPath)) {
                File::makeDirectory($proFolderPath, 0755, true);
            }

            $doc = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'ownership_doc')
                ->latest()
                ->first();

            if ($doc) {

                $tempFullPath = public_path($doc->file_path . '/' . $doc->file_name);
                $proFullPath  = $proFolderPath . '/' . $doc->file_name;

                if (File::exists($tempFullPath)) {

                    File::delete($proFullPath); // replace
                    File::copy($tempFullPath, $proFullPath);
                }

                $finalPath = $pathData->filepath_pro . '/' . $doc->file_name;

                DB::table('ccl_forma_meta')
                    ->updateOrInsert(
                        ['application_id' => $applicationId],
                        [
                            'ownership_doc' => $finalPath,
                            'updated_at' => now()
                        ]
                    );

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $doc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId
                    ]);
            }


            // ---------------------------bank doc------------------
            $doc = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'bank_doc')
                ->latest()
                ->first();

            if ($doc) {

                $tempFullPath = public_path($doc->file_path . '/' . $doc->file_name);
                $proFullPath  = $proFolderPath . '/' . $doc->file_name;

                if (File::exists($tempFullPath)) {

                    File::delete($proFullPath);
                    File::copy($tempFullPath, $proFullPath);
                }

                $finalPath = $pathData->filepath_pro . '/' . $doc->file_name;

                DB::table('tnelb_banksolvency_a')
                    ->updateOrInsert(
                        ['application_id' => $applicationId],
                        [
                            'login_id'            => $request->login_id_store,

                            'form_name'           => $request->form_name,
                            'license_name'        => $request->license_name,
                            'bank_doc' => $finalPath,
                            'updated_at' => now(),
                            'status' => '1'
                        ]
                    );

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $doc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId
                    ]);
            }

            // ------------------------Address proof--------------------
            $doc = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'Address_proof')
                ->latest()
                ->first();

            if ($doc) {

                $tempFullPath = public_path($doc->file_path . '/' . $doc->file_name);
                $proFullPath  = $proFolderPath . '/' . $doc->file_name;

                if (File::exists($tempFullPath)) {

                    File::delete($proFullPath);
                    File::copy($tempFullPath, $proFullPath);
                }

                $finalPath = $pathData->filepath_pro . '/' . $doc->file_name;

                DB::table('tnelb_addressproof_cl')
                    ->updateOrInsert(
                        ['application_id' => $applicationId],
                        [
                            'login_id'            => $request->login_id_store,

                            'form_name'           => $request->form_name,
                            'license_name'        => $request->license_name,
                            'file_doc' => $finalPath,
                            'updated_at' => now()
                        ]
                    );

                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $doc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId
                    ]);
            }

            // ---------------other docs------------------

            $otherDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('document_category', 'other_doc')
                ->get();

            foreach ($otherDocs as $doc) {

                $pathData = DocPathController::getPath($request);
                $proFolderPath = public_path($pathData->filepath_pro);

                if (!File::exists($proFolderPath)) {
                    File::makeDirectory($proFolderPath, 0755, true);
                }

                $tempFullPath = public_path($doc->file_path . '/' . $doc->file_name);
                $proFullPath  = $proFolderPath . '/' . $doc->file_name;

                if (File::exists($tempFullPath)) {

                    File::delete($proFullPath);
                    File::copy($tempFullPath, $proFullPath);
                }

                $finalPath = $pathData->filepath_pro . '/' . $doc->file_name;

                DB::table('tnelb_attachments_cl')->updateOrInsert(
                    [
                        'application_id' => $applicationId,
                        'type' => $doc->ownership_type
                    ],
                    [
                        'login_id'            => $request->login_id_store,

                        'form_name'           => $request->form_name,
                        'license_name'        => $request->license_name,
                        'file_doc'   => $finalPath,
                        'updated_at' => now(),
                        'created_at' => now()
                    ]
                );



                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $doc->id)
                    ->update([
                        'is_final' => '1',
                        'moved_as' => $request->input('form_action'),
                        'record_id_app' => $applicationId
                    ]);
            }




            // =======================================================
            // 8️⃣ MOVE EQUIPMENT FILES + INSERT INTO PERMANENT TABLE
            // =======================================================

            // if ($request->has('equipments')) {

            // -----------------------------------------
            // 1️⃣ GET PERMANENT PATH
            // -----------------------------------------
            $dbFilePath_all = DocPathController::getPath($request);
            $proFolderPath  = public_path($dbFilePath_all->filepath_pro);

            if (!File::exists($proFolderPath)) {
                File::makeDirectory($proFolderPath, 0755, true);
            }

            // Fetch all temp docs grouped by equipment
            $allEquipmentDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('module', 'EQUIPMENTS DOCUMENT')
                ->where('document_sub_category', 'ED')
                ->get()
                ->groupBy('equip_code');

            // Delete old records
            DB::table('tnelb_equimentsuser_cl')
                ->where('application_id', $applicationId)
                ->delete();

            // Loop equipments
            foreach ($request->equipments as $index => $equipment) {

                if (
                    empty($equipment['equip_id']) &&
                    empty($request->serial_no[$index]) &&
                    empty($request->model[$index])
                ) {
                    continue;
                }

                $equipmentId = $equipment['equip_id'];
                $licenceId   = $equipment['licence_id'];

                $serialNo   = $request->serial_no[$index] ?? null;
                $modelNo    = $request->model[$index] ?? null;
                $dateOfTest = $request->date_of_test[$index] ?? null;

                $equipmentDocs = $allEquipmentDocs[$equipmentId] ?? collect();

                // ✅ Get latest files only
                $testDoc = $equipmentDocs
                    ->where('document_category', 'instrument_test_report')
                    ->sortByDesc('id')
                    ->first();

                $purchaseDoc = $equipmentDocs
                    ->where('document_category', 'instrument_purchase_report')
                    ->sortByDesc('id')
                    ->first();

                $testReportPath = null;
                $purchaseReportPath = null;
                // dd($equipmentDocs->pluck('document_category'));exit;
                // ✅ Move and assign TEST REPORT
                if ($testDoc) {
                    $tempFullPath = public_path($testDoc->file_path . '/' . $testDoc->file_name);
                    $proFullPath  = $proFolderPath . '/' . $testDoc->file_name;

                    if (File::exists($tempFullPath)) {
                        File::copy($tempFullPath, $proFullPath);
                    }

                    $testReportPath = $dbFilePath_all->filepath_pro . '/' . $testDoc->file_name;

                    DB::table('tnelb_temp_uploaded_documents')
                        ->where('id', $testDoc->id)
                        ->update([
                            'is_final'   => '1',
                            'moved_as'   => $request->input('form_action'),
                            'updated_at' => now()
                        ]);
                }

                // ✅ Move and assign PURCHASE REPORT
                if ($purchaseDoc) {
                    $tempFullPath = public_path($purchaseDoc->file_path . '/' . $purchaseDoc->file_name);
                    $proFullPath  = $proFolderPath . '/' . $purchaseDoc->file_name;

                    if (File::exists($tempFullPath)) {
                        File::copy($tempFullPath, $proFullPath);
                    }

                    $purchaseReportPath = $dbFilePath_all->filepath_pro . '/' . $purchaseDoc->file_name;

                    DB::table('tnelb_temp_uploaded_documents')
                        ->where('id', $purchaseDoc->id)
                        ->update([
                            'is_final'   => '1',
                            'moved_as'   => $request->input('form_action'),
                            'updated_at' => now()
                        ]);
                }

                // ✅ FINAL INSERT (Correct)
                DB::table('tnelb_equimentsuser_cl')->insert([
                    'login_id'            => $request->login_id_store ?? null,
                    'application_id'      => $applicationId,
                    'form_name'           => $request->form_name,
                    'license_name'        => $request->license_name,
                    'licence_id'          => $licenceId,
                    'equipment_id'        => $equipmentId,
                    'serial_no'           => $serialNo,
                    'model_no'            => $modelNo,
                    'testreport_file'     => $testReportPath,
                    'purchasereport_file' => $purchaseReportPath,
                    'dateoftest'          => $dateOfTest,
                    'ipaddress'           => $request->ip(),
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]);
            }

            // }

            // var_dump($tempDocs);
            // exit;
            // foreach ($tempDocs as $doc) {

            //     // Decide which column to update in main table
            //     $updateColumn = null;

            //     if ($doc->document_category) {
            //         $updateColumn = 'ownership_doc';
            //     }

            //     // if ($doc->document_category === 'DIRECTOR_MOM') {
            //     //     $updateColumn = 'director_mom_doc';
            //     // }

            //     if ($updateColumn) {

            //         // 🔹 Update main application table
            //         DB::table('ccl_forma_meta')
            //             ->where('application_id', $applicationId)
            //             ->update([
            //                 $updateColumn => $doc->file_name,
            //                 'updated_at'  => DB::raw('NOW()')
            //             ]);

            //         // 🔹 Mark temp document as final
            //         DB::table('tnelb_temp_uploaded_documents')
            //             ->where('id', $doc->id)
            //             ->update([
            //                 'is_final'   => '1',
            //                 'updated_at'=> DB::raw('NOW()')
            //             ]);
            //     }
            // }
        });


        if (!$isDraft) {

            // dd('111');exit;

            DB::table('ccl_forma_meta')
                ->where('application_id', $applicationId)
                ->update([
                    'processed_by'   => null,
                    'return_submit' => DB::raw('NOW()')
                ]);

            $supervisorRoleId = DB::table('mst__staffs__tbls')
                ->where('name', 'Supervisor')
                ->value('roles_id');

            WorkflowA::create([
                'application_id' => $applicationId,
                'appl_status'    => 'RE',
                'processed_by'   => 'AP',
                'forwarded_to'   => $supervisorRoleId,
                'role_id'        => $supervisorRoleId,
                'is_verified'    => 'Yes',
                'query_status'   => null,
                'remarks'        => 'Resubmitted by applicant after query.',
                'queries'        => null,
                'raised_by'      => null,
            ]);

            WorkflowA::where('application_id', $applicationId)
                ->where('appl_status', 'RE')
                ->where('role_id', $supervisorRoleId,)
                ->orderByDesc('id')
                ->limit(1)
                ->update([
                    'created_at' => DB::raw('NOW()'),
                    'updated_at' => DB::raw('NOW()'),
                ]);


            return response()->json([
                'draft_status' => $isDraft,
                'actionType' => $request->input('form_action'),
                'message' => 'Application Submitted!',
                'login_id' => $applicationId,
                'transaction_id' => '',
            ]);
        }

        //  DB::table('ccl_forma_meta')
        //         ->where('application_id', $applicationId)
        //          ->update([
        //                     'payment_status'   => $request->input('form_action'),

        //                 ]);

        // dd('1111');exit;
        DB::table('ccl_forma_meta')
            ->where('application_id', $applicationId)
            ->update([
                'application_status'   => 'RETD',

            ]);
        return response()->json([
            'message' => 'Draft',
            'actionType' => $request->input('form_action'),
            'login_id' => $applicationId,
            'transaction_id' => '',
            'draft_status' => $isDraft
        ]);
    }
}
