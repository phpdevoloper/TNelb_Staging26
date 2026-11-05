<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin\Mst_equipment_tbl;
use App\Models\EA_Application_model;
use App\Models\MstLicence;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


use App\Models\Equipmentforma_tbl;

use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Forms_cert;
use App\Models\CC_Forms_Meta;
use App\Models\CC_Proof_doc;

use App\Models\Equipment_storetmp_A;
use App\Models\mst_workflow;

use App\Models\Payment;
use App\Models\ProprietorformA;
use App\Models\Tnelb_Addressproof_cl;
use App\Models\Tnelb_Attachments_cl;
use App\Models\Tnelb_banksolvency_a;
use App\Models\Tnelb_Equimentsuser_cl;
use App\Models\TnelbApplicantStaffDetail;
// use Illuminate\Contracts\Validation\Rule;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbApplicantsSign;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\File;


use App\Models\Tnelb_cl_validitycheck;

class FormAAlteration extends BaseController
{

    protected $dbNow;
    public function __construct()
    {
        parent::__construct();
        $this->dbNow = DB::selectOne("SELECT date_trunc('second', NOW()::timestamp) AS db_now")->db_now;
    }
    public function index()
    {


        $loginId = Auth::user()->login_id;

        //    dd($loginId);exit;
        $licenseNumbers = DB::table('ccl_forma_meta as meta')
            ->join('cl_forma_lic as lic', 'lic.application_id', '=', 'meta.application_id')
            ->where('meta.login_id', $loginId)
            ->where('meta.payment_status', 'paid')
            ->where('meta.application_status', 'A')
            ->whereDate('lic.valid_to', '>', now()->toDateString())
            ->select(
                'meta.application_id',
                'meta.appl_type',
                'meta.form_name',
                'lic.license_number',
                'lic.valid_from',
                'lic.valid_to',
                'lic.dateof_issue'
            )
            ->groupBy(
                'meta.application_id',
                'meta.appl_type',
                'meta.form_name',
                'lic.license_number',
                'lic.valid_from',
                'lic.valid_to',
                'lic.dateof_issue'
            )
            ->get();
        // print_r($applicationIds); exit;



        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        $cert_licence_code = 'EA';

        $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();


        $form_code = MstLicence::where('cert_licence_code', $cert_licence_code)
            ->where('status', 1)
            ->orderBy('id')
            ->first();
        // $cert_licence_code = $form_code ? $form_code->cert_licence_code : null;



        $loginId = Auth::user()->login_id;




        $applicationIds = EA_Application_model::where('login_id', $loginId)
            ->pluck('application_id');




        $today = Carbon::today();

        $activeLicense = DB::table('cl_forma_lic')
            ->whereIn('application_id', $applicationIds)
            ->whereDate('valid_from', '<=', $today)
            ->whereDate('valid_to', '>=', $today)
            ->orderByDesc('valid_to')
            ->first();




        $previousLicenceNo = '';
        $previousValidityFirstIssue = '';
        $previousValidityFrom = '';
        $previousValidityTo = '';




        if ($activeLicense) {

            $previousLicenceNo = $activeLicense->license_number;

            $previousValidityFirstIssue = $activeLicense->valid_from;

            $previousValidityFrom = $activeLicense->valid_from;

            $previousValidityTo = $activeLicense->valid_to;
        }


        return view('user_login.alteration.alteration_cl_main', compact(
            'equiplist',
            'form_code',
            'previousLicenceNo',
            'previousValidityFirstIssue',
            'previousValidityFrom',
            'previousValidityTo',
            'licenseNumbers'
        ));
    }

    public function forma_alter_draft($application_id)
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

            $QCstaffs = DB::table('cl_staff_tbl')
                ->where('application_id', $application_id)
                ->whereIn('staff_category', ['QC', 'QSC'])
                ->where('staff_flag', '1')
                ->orderBy('id', 'ASC')
                ->get();

            $staffs = DB::table('cl_staff_tbl')
                ->where('application_id', $application_id)
                ->whereNotIn('staff_category', ['QC', 'QSC'])
                ->orderBy('id', 'ASC')
                ->get();

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
                ->where('application_id', $application_id) // IMPORTANT
                ->get();

            $cert_licence_code = 'EA';
            $form_code = MstLicence::where('cert_licence_code', $cert_licence_code)
                ->where('status', 1)
                ->orderBy('id')
                ->first();

            $authoritysignatory = DB::table('cl_forma_signs')
                ->where('login_id', Auth::user()->login_id)
                ->where('application_id', $application_id)
                ->where('flag', 1)
                ->orderBy('row_index', 'asc')
                ->get();

            // var_dump()
        }

        $isAlterDraft = 1;

        // dd($application->old_application); exit;
        // return view('user_login.apply-form-a', compact('application', 'proprietors', 'draftCount', 'staffs', 'document', 'banksolvency' , 'equipmentlist', 'equiplist', 'form_code', 'attachment_doc', 'Address_proof', 'equipmentDetails','Qcstaffs'));

        return view('user_login.alteration.EA.form_ea', compact('application', 'proprietors', 'draftCount', 'staffs', 'document', 'banksolvency', 'equipmentlist', 'equiplist', 'form_code', 'attachment_doc', 'Address_proof', 'equipmentDetails', 'QCstaffs', 'draftCounts', 'ownershipType', 'authoritysignatory', 'isAlterDraft'));
    }



   public function forma_alter(Request $request)
{
    // =====================================================
    // 1. OLD APPLICATION DETAILS
    // =====================================================

    $old_application_id = $request->old_applicationid;
    $license_number = $request->license_number;
    $login_id = Auth::user()->login_id;

    $old_applicationdetails = DB::table('ccl_forma_meta')
        ->where('application_id', $old_application_id)
        ->first();

    if (!$old_applicationdetails) {
        return back()->with('error', 'Old application details not found.');
    }

    $license_name = $old_applicationdetails->license_name;
    $form_name = $old_applicationdetails->form_name;

    $appl_type = 'A';

    // =====================================================
    // 2. CHECK EXISTING UNFINISHED ALTERATION APPLICATION
    // =====================================================

    $existingAlterApplication = DB::table('ccl_forma_meta')
        ->where('login_id', $login_id)
        ->where('appl_type', 'A')
        ->whereRaw(
            "LOWER(COALESCE(payment_status, '')) <> ?",
            ['paid']
        )
        ->whereRaw(
            "COALESCE(application_status, '') <> ?",
            ['A']
        )
        ->orderByDesc('id')
        ->first();

    if ($existingAlterApplication) {

        // Reuse existing unfinished application ID.
        // Do not generate another ID or copy records again.

        $applicationId = $existingAlterApplication->application_id;

    } else {

        // =====================================================
        // 3. GENERATE NEW APPLICATION ID
        // =====================================================

        $applicationId = $this->generateApplicationId(
            $appl_type,
            $form_name,
            $license_name
        );

        // Prevent accidental duplicate application ID
        if (
            DB::table('ccl_forma_meta')
                ->where('application_id', $applicationId)
                ->exists()
        ) {
            return back()->with(
                'error',
                'Generated application ID already exists.'
            );
        }

        // =====================================================
        // 4. COPY ALL RELATED TABLES
        // =====================================================

        try {

            DB::beginTransaction();

            // -------------------------------------------------
            // Copy application metadata
            // -------------------------------------------------

            $metaData = (array) $old_applicationdetails;

            unset($metaData['id']);

            $metaData['application_id'] = $applicationId;
            $metaData['license_number'] = $license_number;
            $metaData['payment_status'] = 'draft';
            $metaData['application_status'] = 'P';
            $metaData['appl_type'] = 'A';
            $metaData['login_id'] = $login_id;

            $metaData['old_application'] = $request->old_applicationid;

            $metaData['processed_by'] = null;

            

            DB::table('ccl_forma_meta')->insert($metaData);

            // -------------------------------------------------
            // Reusable function to copy table records
            // -------------------------------------------------

            $copyTableRecords = function ($table, $query) use ($applicationId) {

                $records = $query->get();

                foreach ($records as $record) {

                    $data = (array) $record;

                    // Remove old primary key
                    unset($data['id']);

                    // Assign new application ID
                    $data['application_id'] = $applicationId;

                    // Update timestamps if available
                    if (array_key_exists('created_at', $data)) {
                        $data['created_at'] = now();
                    }

                    if (array_key_exists('updated_at', $data)) {
                        $data['updated_at'] = now();
                    }

                    DB::table($table)->insert($data);
                }
            };

            // -------------------------------------------------
            // 1. Proprietors / Ownership
            // -------------------------------------------------

            $copyTableRecords(
                'cl_ownership_table',
                DB::table('cl_ownership_table')
                    ->where('application_id', $old_application_id)
            );

            // -------------------------------------------------
            // 2. All Staff (QC, QSC, B, C, etc.)
            // -------------------------------------------------

            $copyTableRecords(
                'cl_staff_tbl',
                DB::table('cl_staff_tbl')
                    ->where('application_id', $old_application_id)
            );

            // -------------------------------------------------
            // 3. Applicant Documents
            // -------------------------------------------------

            $copyTableRecords(
                'tnelb_applicant_doc_A',
                DB::table('tnelb_applicant_doc_A')
                    ->where('application_id', $old_application_id)
            );

            // -------------------------------------------------
            // 4. Bank Solvency
            // -------------------------------------------------

            $copyTableRecords(
                'tnelb_banksolvency_a',
                DB::table('tnelb_banksolvency_a')
                    ->where('application_id', $old_application_id)
            );

            // -------------------------------------------------
            // 5. Attachments
            // -------------------------------------------------

            $copyTableRecords(
                'tnelb_attachments_cl',
                DB::table('tnelb_attachments_cl')
                    ->where('application_id', $old_application_id)
            );

            // -------------------------------------------------
            // 6. Address Proof
            // -------------------------------------------------

            $copyTableRecords(
                'tnelb_addressproof_cl',
                DB::table('tnelb_addressproof_cl')
                    ->where('application_id', $old_application_id)
            );

            // -------------------------------------------------
            // 7. Equipment User Details
            // -------------------------------------------------

            $copyTableRecords(
                'tnelb_equimentsuser_cl',
                DB::table('tnelb_equimentsuser_cl')
                    ->where('application_id', $old_application_id)
            );

            // -------------------------------------------------
            // 8. Equipment Form Details
            // -------------------------------------------------

            $copyTableRecords(
                'equipmentforma_tbls',
                DB::table('equipmentforma_tbls')
                    ->where('application_id', $old_application_id)
            );

            // -------------------------------------------------
            // 9. Authority Signatories
            // -------------------------------------------------

            $copyTableRecords(
                'cl_forma_signs',
                DB::table('cl_forma_signs')
                    ->where('application_id', $old_application_id)
            );

            DB::commit();

        } catch (\Throwable $e) {

            DB::rollBack();

            \Log::error('Application alteration copy error', [
                'old_application_id' => $old_application_id,
                'new_application_id' => $applicationId,
                'error' => $e->getMessage()
            ]);

            return back()->with(
                'error',
                'Unable to copy application details: ' . $e->getMessage()
            );
        }
    }

    // =====================================================
    // 5. USE NEW / EXISTING APPLICATION ID FOR THE FORM
    // =====================================================

    $application_id = $applicationId;

    // =====================================================
    // 6. INITIALIZE VARIABLES
    // =====================================================

    $application = null;

    $proprietors = collect();
    $staffs = collect();
    $QCstaffs = collect();

    $document = null;
    $banksolvency = null;

    $equipmentlist = collect();
    $equiplist = collect();
    $attachment_doc = collect();

    $Address_proof = null;
    $equipmentDetails = collect();

    $authoritysignatory = collect();

    $draftCount = 0;
    $draftCounts = 0;

    $ownershipType = null;
    $license_details = null;
    $old_license_number = null;
    $form_code = null;

    // =====================================================
    // 7. FETCH APPLICATION DATA
    // =====================================================

    if ($application_id) {

        // -------------------------------------------------
        // Application metadata
        // -------------------------------------------------

        $application = DB::table('ccl_forma_meta')
            ->where('application_id', $application_id)
            ->first();

        // -------------------------------------------------
        // Proprietors
        // -------------------------------------------------

        $proprietors = DB::table('cl_ownership_table')
            ->where('application_id', $application_id)
            ->where('proprietor_flag', '1')
            ->orderBy('id')
            ->get();

        $draftCount = $proprietors->count();

        $draftCounts = ProprietorformA::where(
            'application_id',
            $application_id
        )->count();

        // -------------------------------------------------
        // Ownership type
        // -------------------------------------------------

        $ownershipType = ProprietorformA::where(
            'application_id',
            $application_id
        )
            ->where('proprietor_flag', 1)
            ->value('ownership_type');

        // -------------------------------------------------
        // QC / QSC Staff
        // -------------------------------------------------

        $QCstaffs = DB::table('cl_staff_tbl')
            ->where('application_id', $application_id)
            ->whereIn('staff_category', ['QC', 'QSC'])
            ->where('staff_flag', '1')
            ->orderBy('id', 'ASC')
            ->get();

        // -------------------------------------------------
        // Other Staff
        // -------------------------------------------------

        $staffs = DB::table('cl_staff_tbl')
            ->where('application_id', $application_id)
            ->whereNotIn('staff_category', ['QC', 'QSC'])
            ->where('staff_flag', '1')
            ->orderBy('id', 'DESC')
            ->get();

        // -------------------------------------------------
        // Old/current licence remains linked to old application
        // -------------------------------------------------

        $old_license_number = DB::table('cl_forma_lic')
            ->where('application_id', $old_application_id)
            ->where('license_number', $license_number)
            ->first();

        // -------------------------------------------------
        // Current active licence
        // -------------------------------------------------

        $today = Carbon::today();

        $license_details = DB::table('cl_forma_lic')
            ->where('application_id', $old_application_id)
            ->where('license_number', $license_number)
            ->whereDate('valid_from', '<=', $today)
            ->whereDate('valid_to', '>=', $today)
            ->orderByDesc('id')
            ->first();

        // -------------------------------------------------
        // Applicant document
        // -------------------------------------------------

        $document = DB::table('tnelb_applicant_doc_A')
            ->where('application_id', $application_id)
            ->first();

        // -------------------------------------------------
        // Bank solvency
        // -------------------------------------------------

        $banksolvency = Tnelb_banksolvency_a::where(
            'application_id',
            $application_id
        )
            ->where('status', '1')
            ->first();

        // -------------------------------------------------
        // Attachments
        // -------------------------------------------------

        $attachment_doc = Tnelb_Attachments_cl::where(
            'application_id',
            $application_id
        )->get();

        // -------------------------------------------------
        // Address proof
        // -------------------------------------------------

        $Address_proof = Tnelb_Addressproof_cl::where(
            'application_id',
            $application_id
        )->first();

        // -------------------------------------------------
        // Equipment details
        // -------------------------------------------------

        $equipmentDetails = Tnelb_Equimentsuser_cl::where(
            'application_id',
            $application_id
        )
            ->get()
            ->keyBy('equipment_id');

        // -------------------------------------------------
        // Equipment master list
        // -------------------------------------------------

        $equiplist = Mst_equipment_tbl::where(
            'equip_licence_name',
            8
        )
            ->where('status', 1)
            ->orderBy('id')
            ->get();

        // -------------------------------------------------
        // Equipment form list
        // -------------------------------------------------

        $equipmentlist = DB::table('equipmentforma_tbls')
            ->where('login_id', $login_id)
            ->where('application_id', $application_id)
            ->get();

        // -------------------------------------------------
        // Licence code
        // -------------------------------------------------

        $cert_licence_code = 'EA';

        $form_code = MstLicence::where(
            'cert_licence_code',
            $cert_licence_code
        )
            ->where('status', 1)
            ->orderBy('id')
            ->first();

        // -------------------------------------------------
        // Authority signatories
        // -------------------------------------------------

        $authoritysignatory = DB::table('cl_forma_signs')
            ->where('login_id', $login_id)
            ->where('application_id', $application_id)
            ->where('flag', 1)
            ->orderBy('row_index', 'asc')
            ->get();
    }

    // =====================================================
    // 8. RETURN VIEW
    // =====================================================

    $isAlterDraft = 0;

    return view(
        'user_login.alteration.EA.form_ea',
        compact(
            'application',
            'proprietors',
            'draftCount',
            'staffs',
            'document',
            'banksolvency',
            'equipmentlist',
            'equiplist',
            'form_code',
            'attachment_doc',
            'Address_proof',
            'equipmentDetails',
            'QCstaffs',
            'draftCounts',
            'ownershipType',
            'old_license_number',
            'license_details',
            'application_id',
            'old_application_id',
            'license_number',
            'authoritysignatory',
            'isAlterDraft'
        )
    );
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

    public function storeAlter(Request $request)
    {


        $sessionId = session()->getId();

        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);
        $isDraft = $request->input('form_action') === 'draft';
        $recordId = trim((string) $request->input('record_id', ''));

        // dd($request->appl_type); exit;
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

        // dd($request->application_ownershiptype); exit;
        $validatedData = $request->validate($rules);


        // Determine if record exists
        $applicationId = null;
        $existing = null;
        // dd($recordId); exit;
        if ($recordId) {
            $existing = EA_Application_model::where('application_id', $recordId)->first();

            // dd($existing); exit;
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

        // dd($request->license_number); exit;

        $dataToSave['license_number'] =
            $request->input('license_number');

        // dd($dataToSave['application_ownershiptype']); exit;

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


        if ($applicationId) {

            // Check whether sessionId exists in cl_staff_tbl
            $staffExists = DB::table('cl_staff_tbl')
                ->where('application_id', $sessionId)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('appl_type', trim($request->appl_type))
                ->exists();

            if ($staffExists) {

                $datastaffapp['application_id'] = $applicationId;

                // Update all matching staff records
                DB::table('cl_staff_tbl')
                    ->where('application_id', $sessionId)
                    ->update($datastaffapp);
            }
        }

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




        // ==========================================
        // PROPRIETORS
        // ==========================================

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

            // dd($existingAuthority); exit;


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

            // =======================================================
            // 5. EQUIPMENT DOCUMENTS
            // =======================================================

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
        });

        $transactionId = 'TXN' . rand(100000, 999999);

        $payment = $isDraft ? 'draft' : 'success';
        if (!$isDraft) {



            $form = DB::table('tnelb_forms')
                ->where('form_code', $request->form_name)
                ->where('status', '1')
                ->first();

            // if (!$form) {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Form not found or inactive.'
            //     ]);
            // }

            $appl_type = $request->appl_type;

            $form = DB::table('mst_licences')
                ->where('form_code', $request->form_name)
                // ->where('status', '1')
                ->first();

            $today = Carbon::today()->toDateString();

            $fees_form = DB::table('tnelb_fees')
                ->where('cert_licence_id', $form->id)
                ->where('fees_type', $appl_type)
                ->whereDate('start_date', '<=', $today)
                ->orderBy('start_date', 'desc')
                ->first();

            if (!$form) {
                return response()->json([
                    'instructions' => null,
                    'fees'         => null
                ], 404);
            }
            $formName  = $request->get('form_name');
            $appl_type = $request->get('appl_type');

            $issued_licence = $request->issued_licence ?? 0;


            $qcFee = 0;


            // ==========================================================
            // OLD APPLICATION QC / QSC
            // ==========================================================

            $qc_old = DB::table('cl_staff_tbl')
                ->where('application_id', $request->old_application)
                ->where('staff_flag', '1')
                ->whereIn('staff_category', ['QC', 'QSC'])
                ->orderBy('row_index', 'asc')
                ->get();


            // ==========================================================
            // NEW APPLICATION QC / QSC
            // ==========================================================

            $qc_new = DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->where('staff_flag', '1')
                ->whereIn('staff_category', ['QC', 'QSC'])
                ->orderBy('row_index', 'asc')
                ->get();


            // ==========================================================
            // OLD QC/QSC LOOKUP
            // ==========================================================

            $oldQCQSC = [];

            foreach ($qc_old as $oldStaff) {

                $category = strtoupper(trim($oldStaff->staff_category));
                $ccNo     = strtoupper(trim($oldStaff->staff_cc_no));

                $key = $category . '|' . $ccNo;

                $oldQCQSC[$key] = true;
            }


            // ==========================================================
            // FIND NEW QC/QSC NOT PRESENT IN OLD
            // ==========================================================

            foreach ($qc_new as $newStaff) {

                $category = strtoupper(trim($newStaff->staff_category));
                $ccNo     = strtoupper(trim($newStaff->staff_cc_no));

                $key = $category . '|' . $ccNo;


                // ------------------------------------------------------
                // Existing QC/QSC
                // No fee
                // ------------------------------------------------------

                if (isset($oldQCQSC[$key])) {
                    continue;
                }


                // ------------------------------------------------------
                // New QC/QSC
                // Calculate fee
                // ------------------------------------------------------

                $qclicence = DB::table('mst_licences')
                    ->where('cert_licence_code', $category)
                    ->first();

                if (!$qclicence) {
                    continue;
                }


                $fee = DB::table('tnelb_fees')
                    ->where('cert_licence_id', $qclicence->id)
                    // ->where('fees_type', $appl_type)
                    ->whereDate('start_date', '<=', $today)
                    ->orderBy('start_date', 'desc')
                    ->value('fees');

                // dd($fee); exit;


                if ($fee !== null) {
                    $qcFee += (float) $fee;
                }
            }

            //  dd($qcFee); exit;



            $instructions = $form->instructions;
            $licence_name = $form->licence_name;

            //   dd($licence_name);
            // exit;
            // HH24:MI:SS
            $dbNow  = DB::selectOne("SELECT TO_CHAR(NOW(), 'DD-MM-YYYY ') AS db_now")->db_now;
            $fees_details['dbNow'] = $dbNow;


            // dd($dbNow);exit;
            $fees_details['qcfee'] = $qcFee;

            $fees_details['total_fees'] =
                0 + $qcFee;
            $fees_details['lateFees'] = 0;
            $fees_details['late_months'] = 0;
            $fees_details['basic_fees'] = 0;

            // $fees_details['basic_fees'] = $paymentDetails[0]->base_fee;

            // dd($fees_details['qcfee']);
            // exit;



            Payment::create([
                'login_id'       => $request->login_id_store,
                'application_id' => $applicationId,
                'transaction_id' => $transactionId,
                'payment_status' => $payment,
                'payment_mode' => 'UPI',
                'amount'         => $fees_details['total_fees'],
                'late_fee'       => $fees_details['lateFees'] ?? 0,
                'late_months'    => $fees_details['late_months'] ?? 0,
                'application_fee'     => $fees_details['basic_fees'],
                // 'qcfees'     => $qcFee,
                'dbNow' => $dbNow,
                'form_name'      => $request->form_name,
                'license_name'   => $request->license_name,
            ]);

            mst_workflow::create([
                'login_id' => $request->login_id_store,
                'application_id' => $applicationId,
                'transaction_id' => $transactionId,
                'payment_status' => $payment,

                'formname_appliedfor' => $request->form_name,
                'license_name' => $request->license_name,
            ]);


             EA_Application_model::where('application_id', $applicationId)
                ->update(array_merge($updateData, [
                    'payment_status' => 'paid'
                ]));



            return response()->json([
                'draft_status' => $isDraft,
                'message' => 'Payment Processed!',
                'login_id' => $applicationId,
                'transaction_id' => $transactionId,
                 'qcfees'     => $qcFee,

                'dbNow' => $dbNow,

            ]);
        }

        return response()->json([
            'message' => 'Draft',
            'login_id' => $applicationId,
            'transaction_id' => $isDraft ? 'DRAFT' . rand(100000, 999999) : 'TXN' . rand(100000, 999999),
            'draft_status' => $isDraft,


        ]);
    }

    // ------------application id-----------------------
    private function generateApplicationId($appl_type, $formName, $licenseName)
    {
        $model = EA_Application_model::class;

        $prefix = ($appl_type === 'N') ? '' : $appl_type;
        $year = date('y');

        $applicationPrefix = strtoupper(
            $prefix . $formName . $licenseName . $year
        );

        // Check whether an application already exists
        $query = $model::where(
            'application_id',
            'LIKE',
            $applicationPrefix . '%'
        );

        // For alteration applications, check appl_type = A
        if ($appl_type === 'A') {
            $query->where('appl_type', 'A');
        }

        $lastApplication = $query
            ->orderByDesc('id')
            ->value('application_id');

        // Start from 000001 if no application exists
        $nextNumber = '000001';

        if ($lastApplication && preg_match('/(\d{6})$/', $lastApplication, $matches)) {

            $lastNumber = (int) $matches[1];

            $nextNumber = str_pad(
                $lastNumber + 1,
                6,
                '0',
                STR_PAD_LEFT
            );
        }

        return $applicationPrefix . $nextNumber;
    }

    // ----------------ownership type-------------------------

    public function saveTemp(Request $request)
    {
        $sessionId = session()->getId();
        $proprietors = $request->input('proprietors', []);

        $saved = []; // collect inserted/updated proprietors

        foreach ($proprietors as $p) {
            if (empty($p['proprietor_name'])) continue;

            $data = [
                'login_id' => $request->login_id_store,
                'application_id' => $sessionId,
                'ownership_type' => strtoupper($p['ownership_type'] ?? ''),
                'proprietor_name' => strtoupper($p['proprietor_name']),
                'fathers_name' => strtoupper($p['fathers_name'] ?? ''),
                'age' => $p['age'] ?? null,
                'proprietor_address' => strtoupper($p['proprietor_address'] ?? ''),
                'qualification' => strtoupper($p['qualification'] ?? ''),
                'present_business' => strtoupper($p['present_business'] ?? ''),
                'competency_certificate_holding' => $p['competency_certificate_holding'] ?? 'no',
                'competency_certificate_number' => $p['competency_certificate_number'] ?? null,
                'competency_certificate_validity' => $p['competency_certificate_validity'] ?? null,
                'presently_employed' => $p['presently_employed'] ?? 'no',
                'presently_employed_name' => strtoupper($p['presently_employed_name'] ?? ''),
                'presently_employed_address' => strtoupper($p['presently_employed_address'] ?? ''),
                'previous_experience' => $p['previous_experience'] ?? 'no',
                'previous_experience_name' => strtoupper($p['previous_experience_name'] ?? ''),
                'previous_experience_address' => strtoupper($p['previous_experience_address'] ?? ''),
                'previous_experience_lnumber' => $p['previous_experience_lnumber'] ?? null,
                'previous_experience_lnumber_validity' => $p['previous_experience_lnumber_validity'] ?? null,
                'proprietor_flag' => '1'
            ];


            // dd($p['proprietor_id']);
            // exit;
            // $record = ProprietorformA::updateOrCreate(
            //     ['id' => $p['proprietor_id'] ?? 0],
            //     $data
            // );

            // $saved[] = $record;
            // dd($p['proprietor_id']);
            // exit;

            if (!empty($p['proprietor_id'])) {
                $record = ProprietorformA::where('id', $p['proprietor_id'])->first();
                if ($record) {
                    $record->update($data);
                } else {
                    $record = ProprietorformA::create($data);
                }
            } else {
                $record = ProprietorformA::create($data);
            }

            $saved[] = $record;
        }

        return response()->json([
            'success' => true,
            'message' => 'Proprietor saved successfully',
            'proprietors' => $saved
        ]);
    }

    // data from table onwership-------------------------
    public function getProprietors(Request $request)
    {
        $sessionId = session()->getId();

        $proprietors = ProprietorformA::where('application_id', $sessionId)
            ->where('proprietor_flag', '1')
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'proprietors' => $proprietors
        ]);
    }


    // -----------instructions--------------------

    public function getFormInstructions_alter(Request $request)
    {

        // dd($request->all());exit;
        try {
            $formName  = $request->get('form_name');
            $appl_type = $request->get('appl_type');

            $issued_licence = $request->get('issued_licence');


            // dd($issued_licence);
            // exit;
            // $form = \DB::table('tnelb_forms')
            //     ->where('form_name', $formName)
            //     ->where('status', '1')
            //     ->first();

            $form = DB::table('mst_licences')
                ->where('form_code', $formName)
                // ->where('status', '1')
                ->first();

            // dd($form->id);
            // exit;

            $today = Carbon::today()->toDateString();

            $fees_form = DB::table('tnelb_fees')
                ->where('cert_licence_id', $form->id)
                ->where('fees_type', $appl_type)
                ->whereDate('start_date', '<=', $today)
                ->orderBy('start_date', 'desc')
                ->first();


            // dd($fees_form->fees_status);
            // exit;

            if (!$form) {
                return response()->json([
                    'instructions' => null,
                    'fees'         => null,
                    'fees_start_date' => null
                ], 404);
            }

            if ($appl_type === 'D') {

                $fees_details = [
                    'dbNow'       => Carbon::now()->format('d-m-Y'),
                    'total_fees'  => 0,
                    'lateFees'    => 0,
                    'late_months' => 0,
                    'basic_fees'  => 0,
                    'qcfee'       => 0,
                ];

                return response()->json([
                    'status'          => 'success',
                    'instructions'    => $form->instructions,
                    'licenseName'     => $form->licence_name,
                    'fees_details'    => $fees_details,
                    'fees_start_date' => null
                ], 200);
            }

            if ($appl_type === 'R') {

                // dd($appl_type, $issued_licence, $form->id); exit;
                // $issued_licence = 'LA20251000001';

                $paymentDetails = DB::select("
                SELECT * FROM calc_fees(:appl_type, :licence_id, :issued_licence)
                ", [
                    'appl_type' => $appl_type,
                    'licence_id' => $form->id,
                    'issued_licence' => $issued_licence,
                ]);


                // dd($paymentDetails);
                // exit;
            } else {

                // dd($request->all());
                // exit;


                $paymentDetails = DB::select("
                    SELECT * FROM calc_fees(:appl_type, :licence_id, :issued_licence)
                ", [
                    'appl_type' => $appl_type,
                    'licence_id' => $form->id,
                    'issued_licence' => null,
                ]);

                //         dd($paymentDetails);
                // exit;
            }

            if (!empty($paymentDetails)) {
                $instructions = $form->instructions;
                $licence_name = $form->licence_name;

                $fees_start_date = $fees_form->fees;

                //   dd($fees_form->fees);
                // exit;
                // HH24:MI:SS
                $dbNow  = DB::selectOne("SELECT TO_CHAR(NOW(), 'DD-MM-YYYY ') AS db_now")->db_now;
                $fees_details['dbNow'] = $dbNow;

                $fees_details['total_fees'] = $paymentDetails[0]->total_fee;
                $fees_details['lateFees'] = $paymentDetails[0]->late_fee;
                $fees_details['late_months'] = $paymentDetails[0]->late_months;
                $fees_details['basic_fees'] = $paymentDetails[0]->base_fee;

                // $fees_details['qcfee'] = $paymentDetails[0]->qcfee;

                // $fees_details['basic_fees'] = $paymentDetails[0]->base_fee;



            }

            // dd( $fees_details['total_fees']);
            // exit;

            return response()->json([
                'status' => 'success',
                'instructions' => $instructions,
                'licenseName' => $licence_name,
                'fees_details' => $fees_details,
                'fees_start_date' => $fees_form->start_date
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. ' . $e->getMessage(),
            ], 500);
        }
    }
}
