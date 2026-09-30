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
        // dd($application->old_application); exit;
        // return view('user_login.apply-form-a', compact('application', 'proprietors', 'draftCount', 'staffs', 'document', 'banksolvency' , 'equipmentlist', 'equiplist', 'form_code', 'attachment_doc', 'Address_proof', 'equipmentDetails','Qcstaffs'));

        return view('user_login.alteration.EA.form_ea', compact('application', 'proprietors', 'draftCount', 'staffs', 'document', 'banksolvency', 'equipmentlist', 'equiplist', 'form_code', 'attachment_doc', 'Address_proof', 'equipmentDetails', 'QCstaffs', 'draftCounts', 'ownershipType', 'authoritysignatory'));
    }



    public function forma_alter(Request $request)
    {
        $application_id = $request->application_id;
        $license_number = $request->license_number;

        $application = null;
        $proprietors = collect();
        $staffs = collect();
        $document = null;

        $QCstaffs = collect();
        $banksolvency = null;
        $equipmentlist = collect();
        $attachment_doc = collect();
        $Address_proof = null;
        $equipmentDetails = collect();

        $draftCount = 0;
        $draftCounts = 0;
        $ownershipType = null;
        $license_details = null;
        $old_license_number = null;

        if ($application_id) {

            // Old/current licence
            $old_license_number = DB::table('cl_forma_lic')
                ->where('application_id', $application_id)
                ->where('license_number', $license_number)
                ->first();

            // Application
            $application = DB::table('ccl_forma_meta')
                ->where('application_id', $application_id)
                ->first();

            // Proprietors
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

            // Ownership
            $ownershipType = ProprietorformA::where(
                'application_id',
                $application_id
            )
                ->where('proprietor_flag', 1)
                ->value('ownership_type');

            // QC / QSC Staff
            $QCstaffs = DB::table('cl_staff_tbl')
                ->where('application_id', $application_id)
                ->whereIn('staff_category', ['QC', 'QSC'])
                ->where('staff_flag', '1')
                ->orderBy('id', 'ASC')
                ->get();

            // Other Staff
            $staffs = DB::table('cl_staff_tbl')
                ->where('application_id', $application_id)
                ->whereNotIn('staff_category', ['QC', 'QSC'])
                ->where('staff_flag', '1')
                ->orderBy('id', 'ASC')
                ->get();

            // Current active licence
            $today = Carbon::today();

            $license_details = DB::table('cl_forma_lic')
                ->where('application_id', $application_id)
                ->where('license_number', $license_number)
                ->whereDate('valid_from', '<=', $today)
                ->whereDate('valid_to', '>=', $today)
                ->orderByDesc('id')
                ->first();

            // Applicant document
            $document = DB::table('tnelb_applicant_doc_A')
                ->where('application_id', $application_id)
                ->first();

            // Bank solvency
            $banksolvency = Tnelb_banksolvency_a::where(
                'application_id',
                $application_id
            )
                ->where('status', '1')
                ->first();

            // Attachments
            $attachment_doc = Tnelb_Attachments_cl::where(
                'application_id',
                $application_id
            )->get();

            // Address proof
            $Address_proof = Tnelb_Addressproof_cl::where(
                'application_id',
                $application_id
            )->first();

            // Equipment details
            $equipmentDetails = Tnelb_Equimentsuser_cl::where(
                'application_id',
                $application_id
            )
                ->get()
                ->keyBy('equipment_id');

            // Equipment list
            $equiplist = Mst_equipment_tbl::where(
                'equip_licence_name',
                8
            )
                ->where('status', 1)
                ->orderBy('id')
                ->get();

            $equipmentlist = DB::table('equipmentforma_tbls')
                ->where('login_id', Auth::user()->login_id)
                ->where('application_id', $application_id)
                ->get();

            // Licence code
            $cert_licence_code = 'EA';

            $form_code = MstLicence::where(
                'cert_licence_code',
                $cert_licence_code
            )
                ->where('status', 1)
                ->orderBy('id')
                ->first();
        }

         $authoritysignatory = DB::table('cl_forma_signs')
                ->where('login_id', Auth::user()->login_id)
                ->where('application_id', $application_id)
                  ->where('flag', 1)
                   ->orderBy('row_index', 'asc')
                ->get();
        // dd($license_number); exit;

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
                'license_number',  'authoritysignatory'
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

        // dd("111"); exit;

        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);
        $isDraft = $request->input('form_action') === 'draft';
        $recordId = $request->input('record_id');
        // dd($recordId); exit;
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

        $appl_type = $request->appl_type;


        // dd($request->old_application); exit;

        // Determine if record exists
        $applicationId = null;
        $existingcheck = null;

        // if ($recordId) {
        //     $existingcheck = EA_Application_model::where('application_id', $recordId)->first();
        //     if ($existingcheck) {
        //         $applicationId = $existingcheck->application_id;
        //     }
        // }
        // dd($applicationId); exit;
       if ($recordId) {
        // dd($recordId); exit;
          $existing = EA_Application_model::where('application_id', $recordId)
                ->where(function ($query) {
                    $query->where('application_status', '!=', 'A')
                        ->orWhereNull('application_status');
                })
                ->where(function ($query) {
                    $query->where('payment_status', '!=', 'paid')
                        ->orWhereNull('payment_status');
                })
                ->first();

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
        if ($request->appl_type === 'D') {
            $dataToSave['payment_status'] = $isDraft ? 'draft' : 'paid';
        } else {
            $dataToSave['payment_status'] = $isDraft ? 'draft' : 'pending';
        }
        $dataToSave['application_status'] = 'P';
        // $dataToSave['created_at'] = now();
        $dataToSave['updated_at'] = DB::raw('NOW()');

        $dataToSave['old_application'] = $request->old_application;

        $dataToSave['license_number'] = $request->license_number;

        // dd($dataToSave['license_number']); exit;

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
            ->where(
                'license_name',
                $request->input('license_name')
            )
            ->where(
                'document_category',
                'specimen_sign'
            )
            ->get()
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

        //         $processedStaffIdsQC = [];


        //         // QC/QSC----------------

        //         if ($request->has('staffqc_category')) {
        //             // dd('qc'); exit;

        //             $staffCategories = $request->input('staffqc_category', []);
        //             $staffCcNos = $request->input('staff_cc_no', []);
        //             $staffFirstIssues = $request->input('staff_cc_first_issue', []);
        //             $staffValidityFroms = $request->input('staff_cc_validity_from', []);
        //             $staffValidityTos = $request->input('staff_cc_validity_to', []);

        //             $staffQcIds = $request->input('staffqc_id', []);
        //             $rowIndexes = $request->input('row_index', []);

        //             /*
        //     |--------------------------------------------------------------------------
        //     | GET NEXT ROW INDEX
        //     |--------------------------------------------------------------------------
        //     */

        //             $lastRowIndex = DB::table('cl_staff_tbl')
        //                 ->where('application_id', $applicationId)
        //                 ->max('row_index');

        //             $nextRowIndex = ((int) $lastRowIndex) + 1;

        //             /*
        //     |--------------------------------------------------------------------------
        //     | ROW INDEXES PRESENT IN CURRENT FORM
        //     |--------------------------------------------------------------------------
        //     */

        //             $currentRowIndexes = [];

        //             foreach ($staffCategories as $index => $category) {

        //                 $category = strtoupper(trim((string) $category));

        //                 /*
        //         |--------------------------------------------------------------------------
        //         | Only QC / QSC
        //         |--------------------------------------------------------------------------
        //         */

        //                 if (!in_array($category, ['QC', 'QSC'], true)) {
        //                     continue;
        //                 }

        //                 $ccNo = trim((string) ($staffCcNos[$index] ?? ''));

        //                 /*
        //         |--------------------------------------------------------------------------
        //         | Ignore empty / undefined certificate number
        //         |--------------------------------------------------------------------------
        //         */

        //                 if (
        //                     $ccNo === '' ||
        //                     strtolower($ccNo) === 'undefined' ||
        //                     strtolower($ccNo) === 'null'
        //                 ) {
        //                     continue;
        //                 }

        //                 $firstIssue = trim((string) ($staffFirstIssues[$index] ?? ''));
        //                 $validityFrom = trim((string) ($staffValidityFroms[$index] ?? ''));
        //                 $validityTo = trim((string) ($staffValidityTos[$index] ?? ''));



        //                 $staffId = $staffQcIds[$index] ?? null;
        //                 $submittedRowIndex = $rowIndexes[$index] ?? null;

        //                 /*
        //         |--------------------------------------------------------------------------
        //         | EXISTING RECORD
        //         |--------------------------------------------------------------------------
        //         */

        //                 /*
        // |--------------------------------------------------------------------------
        // | EXISTING RECORD
        // |--------------------------------------------------------------------------
        // */

        //                 if (!empty($staffId)) {

        //                     $existing = DB::table('cl_staff_tbl')
        //                         ->where('id', $staffId)
        //                         ->where('application_id', $applicationId)
        //                         ->whereIn('staff_category', ['QC', 'QSC'])
        //                         ->first();

        //                     if ($existing) {

        //                         /*
        //         |--------------------------------------------------------------------------
        //         | Keep existing values if POST value is empty
        //         |--------------------------------------------------------------------------
        //         */

        //                         $updateData = [
        //                             'staff_category' => $category,
        //                             'staff_cc_no' => $ccNo,
        //                             'staff_flag' => '1',
        //                             'updated_at' => now(),
        //                         ];

        //                         if ($firstIssue !== '') {
        //                             $updateData['staff_cc_first_issue'] = $firstIssue;
        //                         }

        //                         if ($validityFrom !== '') {
        //                             $updateData['staff_cc_validity_from'] = $validityFrom;
        //                         }

        //                         if ($validityTo !== '') {
        //                             $updateData['staff_cc_validity_to'] = $validityTo;
        //                         }

        //                         DB::table('cl_staff_tbl')
        //                             ->where('id', $staffId)
        //                             ->where('application_id', $applicationId)
        //                             ->update($updateData);

        //                         $currentRowIndexes[] = (int) $existing->row_index;
        //                     }
        //                 }

        //                 /*
        //         |--------------------------------------------------------------------------
        //         | NEW RECORD
        //         |--------------------------------------------------------------------------
        //         */ else {

        //                     // dd('new qc'); exit;

        //                     $newRowIndex = $nextRowIndex;

        //                     DB::table('cl_staff_tbl')->insert([
        //                         'login_id' => $request->input('login_id_store'),
        //                         'application_id' => $applicationId,
        //                         'staff_category' => $category,
        //                         'staff_cc_no' => $ccNo,

        //                         'staff_cc_first_issue' =>
        //                         $firstIssue !== '' ? $firstIssue : null,

        //                         'staff_cc_validity_from' =>
        //                         $validityFrom !== '' ? $validityFrom : null,

        //                         'staff_cc_validity_to' =>
        //                         $validityTo !== '' ? $validityTo : null,

        //                         'row_index' => $newRowIndex,
        //                         'staff_flag' => '1',
        //                         'staff_status' => 'N',
        //                         'created_at' => now(),
        //                         'updated_at' => now(),
        //                     ]);

        //                     $currentRowIndexes[] = $newRowIndex;

        //                     $nextRowIndex++;
        //                 }
        //             }

        //             /*
        //     |--------------------------------------------------------------------------
        //     | SET MISSING QC / QSC RECORDS TO staff_flag = 0
        //     |--------------------------------------------------------------------------
        //     |
        //     | Existing DB records which are NOT present in the submitted form
        //     | will be marked as 0.
        //     |
        //     */

        //             DB::table('cl_staff_tbl')
        //                 ->where('application_id', $applicationId)
        //                 ->whereIn('staff_category', ['QC', 'QSC'])
        //                 ->where('staff_flag', '1')
        //                 ->whereNotIn('row_index', $currentRowIndexes)
        //                 ->update([
        //                     'staff_flag' => '0',
        //                     'updated_at' => now(),
        //                 ]);



        //         }


        $processedStaffIdsQC = [];


        // ==========================================================
        // QC / QSC
        // ==========================================================

        if ($request->has('staffqc_category')) {

            $staffCategories = $request->input(
                'staffqc_category',
                []
            );

            $staffCcNos = $request->input(
                'staff_cc_no',
                []
            );

            $staffFirstIssues = $request->input(
                'staff_cc_first_issue',
                []
            );

            $staffValidityFroms = $request->input(
                'staff_cc_validity_from',
                []
            );

            $staffValidityTos = $request->input(
                'staff_cc_validity_to',
                []
            );

            $staffQcIds = $request->input(
                'staffqc_id',
                []
            );

            $rowIndexes = $request->input(
                'row_index',
                []
            );


            // ==========================================================
            // GET CURRENT MAX ROW INDEX
            // ==========================================================

            $lastRowIndex = DB::table('cl_staff_tbl')
                ->where(
                    'application_id',
                    $applicationId
                )
                ->max('row_index');

            $nextRowIndex =
                ((int) $lastRowIndex) + 1;


            // ==========================================================
            // ROW INDEXES PRESENT IN CURRENT APPLICATION
            // ==========================================================

            $currentRowIndexes = [];


            // ==========================================================
            // CHECK CURRENT APPLICATION QC / QSC
            // IMPORTANT:
            // THIS CHECK IS OUTSIDE THE FOREACH
            // ==========================================================

            $existingCurrentQC = DB::table('cl_staff_tbl')
                ->where(
                    'application_id',
                    $applicationId
                )
                ->whereIn(
                    'staff_category',
                    ['QC', 'QSC']
                )
                ->where(
                    'staff_flag',
                    '1'
                )
                ->get();


            // ==========================================================
            // SCENARIO 1:
            // CURRENT APPLICATION HAS NO QC / QSC
            //
            // COPY ALL ACTIVE QC / QSC FROM OLD APPLICATION
            // ONLY ONCE
            // ==========================================================

            if ($existingCurrentQC->isEmpty()) {

                $qc_old = DB::table('cl_staff_tbl')
                    ->where(
                        'application_id',
                        $request->old_application
                    )
                    ->where(
                        'staff_flag',
                        '1'
                    )
                    ->whereIn(
                        'staff_category',
                        ['QC', 'QSC']
                    )
                    ->orderBy(
                        'row_index',
                        'asc'
                    )
                    ->get();


                // ======================================================
                // COPY OLD QC / QSC RECORDS
                // ======================================================

                foreach ($qc_old as $oldData) {

                    $newStaffId = DB::table('cl_staff_tbl')
                        ->insertGetId([

                            // ------------------------------------------
                            // OLD DATA
                            // ------------------------------------------

                            'login_id' =>
                            $oldData->login_id,

                            // ------------------------------------------
                            // ONLY APPLICATION ID CHANGES
                            // ------------------------------------------

                            'application_id' =>
                            $applicationId,

                            'staff_category' =>
                            $oldData->staff_category,

                            'staff_cc_no' =>
                            $oldData->staff_cc_no,

                            'staff_cc_first_issue' =>
                            $oldData->staff_cc_first_issue,

                            'staff_cc_validity_from' =>
                            $oldData->staff_cc_validity_from,

                            'staff_cc_validity_to' =>
                            $oldData->staff_cc_validity_to,

                            'appointment_letter' =>
                            $oldData->appointment_letter,

                            'consent_letter' =>
                            $oldData->consent_letter,

                            'app_doc' =>
                            $oldData->app_doc,

                            'cons_doc' =>
                            $oldData->cons_doc,

                            // ------------------------------------------
                            // KEEP OLD ROW INDEX
                            // ------------------------------------------

                            'row_index' =>
                            $oldData->row_index,

                            // ------------------------------------------
                            // ALWAYS ACTIVE
                            // ------------------------------------------

                            'staff_flag' =>
                            '1',

                            'staff_status' =>
                            $oldData->staff_status,

                            'staff_designation' =>
                            $oldData->staff_designation,

                            'created_at' =>
                            now(),

                            'updated_at' =>
                            now(),
                        ]);


                    // ==================================================
                    // IMPORTANT
                    // Add copied row index so final FLAG=0 query
                    // will NOT deactivate this record.
                    // ==================================================

                    $currentRowIndexes[] =
                        $oldData->row_index;


                    $processedStaffIdsQC[] =
                        $newStaffId;
                }


                // ======================================================
                // IMPORTANT:
                // DO NOT PROCESS THE FORM QC/QSC ROWS AGAIN HERE.
                //
                // The old records have already been copied.
                // ======================================================

            }


            // ==========================================================
            // SCENARIO 2:
            // CURRENT APPLICATION ALREADY HAS QC / QSC
            //
            // PROCESS FORM DATA
            // ==========================================================

            else {

                foreach (
                    $staffCategories
                    as $index => $category
                ) {

                    // --------------------------------------------------
                    // CATEGORY
                    // --------------------------------------------------

                    $category = strtoupper(
                        trim((string) $category)
                    );


                    // --------------------------------------------------
                    // ONLY QC / QSC
                    // --------------------------------------------------

                    if (
                        !in_array(
                            $category,
                            ['QC', 'QSC'],
                            true
                        )
                    ) {
                        continue;
                    }


                    // --------------------------------------------------
                    // CERTIFICATE NUMBER
                    // --------------------------------------------------

                    $ccNo = trim(
                        (string) (
                            $staffCcNos[$index] ?? ''
                        )
                    );


                    // --------------------------------------------------
                    // IGNORE EMPTY CERTIFICATE
                    // --------------------------------------------------

                    if (
                        $ccNo === '' ||
                        strtolower($ccNo) === 'undefined' ||
                        strtolower($ccNo) === 'null'
                    ) {
                        continue;
                    }


                    // --------------------------------------------------
                    // OTHER VALUES
                    // --------------------------------------------------

                    $firstIssue = trim(
                        (string) (
                            $staffFirstIssues[$index] ?? ''
                        )
                    );

                    $validityFrom = trim(
                        (string) (
                            $staffValidityFroms[$index] ?? ''
                        )
                    );

                    $validityTo = trim(
                        (string) (
                            $staffValidityTos[$index] ?? ''
                        )
                    );


                    // --------------------------------------------------
                    // STAFF ID
                    // --------------------------------------------------

                    $staffId =
                        $staffQcIds[$index] ?? null;


                    // --------------------------------------------------
                    // SUBMITTED ROW INDEX
                    // --------------------------------------------------

                    $submittedRowIndex =
                        $rowIndexes[$index] ?? null;


                    // ==================================================
                    // NORMALIZE STAFF ID
                    // ==================================================

                    if (
                        $staffId !== null &&
                        $staffId !== '' &&
                        strtolower(
                            trim((string) $staffId)
                        ) !== 'undefined' &&
                        strtolower(
                            trim((string) $staffId)
                        ) !== 'null'
                    ) {

                        $staffId =
                            (int) $staffId;
                    } else {

                        $staffId = null;
                    }


                    // ==================================================
                    // NORMALIZE ROW INDEX
                    // ==================================================

                    if (
                        $submittedRowIndex !== null &&
                        $submittedRowIndex !== '' &&
                        strtolower(
                            trim((string) $submittedRowIndex)
                        ) !== 'undefined' &&
                        strtolower(
                            trim((string) $submittedRowIndex)
                        ) !== 'null'
                    ) {

                        $submittedRowIndex =
                            (int) $submittedRowIndex;
                    } else {

                        $submittedRowIndex = null;
                    }


                    // ==================================================
                    // EXISTING QC / QSC RECORD
                    // ==================================================

                    if (!empty($staffId)) {

                        $existingqc = DB::table('cl_staff_tbl')
                            ->where(
                                'application_id',
                                $applicationId
                            )
                            ->where(
                                'id',
                                $staffId
                            )
                            ->whereIn(
                                'staff_category',
                                ['QC', 'QSC']
                            )
                            ->where(
                                'staff_flag',
                                '1'
                            )
                            ->first();


                        // ==================================================
                        // RECORD EXISTS
                        // ==================================================

                        if ($existingqc) {

                            // ----------------------------------------------
                            // KEEP EXISTING ROW INDEX
                            // ----------------------------------------------

                            $existingRowIndex =
                                $existingqc->row_index;


                            // ----------------------------------------------
                            // UPDATE DATA
                            // ----------------------------------------------

                            $updateqc = [

                                'staff_category' =>
                                $category,

                                'staff_cc_no' =>
                                $ccNo,

                                'staff_flag' =>
                                '1',

                                'updated_at' =>
                                now(),
                            ];


                            // ----------------------------------------------
                            // FIRST ISSUE
                            // ----------------------------------------------

                            if ($firstIssue !== '') {

                                $updateqc['staff_cc_first_issue'] = $firstIssue;
                            }


                            // ----------------------------------------------
                            // VALIDITY FROM
                            // ----------------------------------------------

                            if ($validityFrom !== '') {

                                $updateqc['staff_cc_validity_from'] = $validityFrom;
                            }


                            // ----------------------------------------------
                            // VALIDITY TO
                            // ----------------------------------------------

                            if ($validityTo !== '') {

                                $updateqc['staff_cc_validity_to'] = $validityTo;
                            }


                            // ----------------------------------------------
                            // UPDATE
                            // ----------------------------------------------

                            DB::table('cl_staff_tbl')
                                ->where(
                                    'id',
                                    $staffId
                                )
                                ->where(
                                    'application_id',
                                    $applicationId
                                )
                                ->update(
                                    $updateqc
                                );


                            // ----------------------------------------------
                            // CURRENT ROW
                            // ----------------------------------------------

                            $currentRowIndexes[] =
                                $existingRowIndex;


                            $processedStaffIdsQC[] =
                                $staffId;
                        }


                        // ==================================================
                        // STAFF ID SENT BUT RECORD NOT FOUND
                        // ==================================================

                        else {

                            // ----------------------------------------------
                            // USE SUBMITTED ROW INDEX
                            // ----------------------------------------------

                            if (
                                $submittedRowIndex !== null
                            ) {

                                $newRowIndex =
                                    $submittedRowIndex;
                            } else {

                                $newRowIndex =
                                    $nextRowIndex;
                            }


                            // ----------------------------------------------
                            // INSERT NEW RECORD
                            // ----------------------------------------------

                            $newStaffId =
                                DB::table('cl_staff_tbl')
                                ->insertGetId([

                                    'login_id' =>
                                    $request->input(
                                        'login_id_store'
                                    ),

                                    'application_id' =>
                                    $applicationId,

                                    'staff_category' =>
                                    $category,

                                    'staff_cc_no' =>
                                    $ccNo,

                                    'staff_cc_first_issue' =>
                                    $firstIssue !== ''
                                        ? $firstIssue
                                        : null,

                                    'staff_cc_validity_from' =>
                                    $validityFrom !== ''
                                        ? $validityFrom
                                        : null,

                                    'staff_cc_validity_to' =>
                                    $validityTo !== ''
                                        ? $validityTo
                                        : null,

                                    'row_index' =>
                                    $newRowIndex,

                                    'staff_flag' =>
                                    '1',

                                    'staff_status' =>
                                    'N',

                                    'created_at' =>
                                    now(),

                                    'updated_at' =>
                                    now(),
                                ]);


                            // ----------------------------------------------
                            // CURRENT ROW
                            // ----------------------------------------------

                            $currentRowIndexes[] =
                                $newRowIndex;


                            $processedStaffIdsQC[] =
                                $newStaffId;


                            // ----------------------------------------------
                            // UPDATE NEXT ROW INDEX
                            // ----------------------------------------------

                            if (
                                $newRowIndex >= $nextRowIndex
                            ) {

                                $nextRowIndex =
                                    $newRowIndex + 1;
                            }
                        }
                    }


                    // ==================================================
                    // NEW QC / QSC RECORD
                    // ==================================================

                    else {

                        // --------------------------------------------------
                        // USE SUBMITTED ROW INDEX
                        // --------------------------------------------------

                        if (
                            $submittedRowIndex !== null
                        ) {

                            $newRowIndex =
                                $submittedRowIndex;
                        } else {

                            $newRowIndex =
                                $nextRowIndex;
                        }


                        // --------------------------------------------------
                        // INSERT
                        // --------------------------------------------------

                        $newStaffId =
                            DB::table('cl_staff_tbl')
                            ->insertGetId([

                                'login_id' =>
                                $request->input(
                                    'login_id_store'
                                ),

                                'application_id' =>
                                $applicationId,

                                'staff_category' =>
                                $category,

                                'staff_cc_no' =>
                                $ccNo,

                                'staff_cc_first_issue' =>
                                $firstIssue !== ''
                                    ? $firstIssue
                                    : null,

                                'staff_cc_validity_from' =>
                                $validityFrom !== ''
                                    ? $validityFrom
                                    : null,

                                'staff_cc_validity_to' =>
                                $validityTo !== ''
                                    ? $validityTo
                                    : null,

                                'row_index' =>
                                $newRowIndex,

                                'staff_flag' =>
                                '1',

                                'staff_status' =>
                                'N',

                                'created_at' =>
                                now(),

                                'updated_at' =>
                                now(),
                            ]);


                        // --------------------------------------------------
                        // CURRENT ROW
                        // --------------------------------------------------

                        $currentRowIndexes[] =
                            $newRowIndex;


                        $processedStaffIdsQC[] =
                            $newStaffId;


                        // --------------------------------------------------
                        // UPDATE NEXT ROW INDEX
                        // --------------------------------------------------

                        if (
                            $newRowIndex >= $nextRowIndex
                        ) {

                            $nextRowIndex =
                                $newRowIndex + 1;
                        }
                    }
                }
            }


            // ==========================================================
            // SET MISSING QC / QSC RECORDS TO FLAG 0
            // ==========================================================

            if (!empty($currentRowIndexes)) {

                DB::table('cl_staff_tbl')
                    ->where(
                        'application_id',
                        $applicationId
                    )
                    ->whereIn(
                        'staff_category',
                        ['QC', 'QSC']
                    )
                    ->where(
                        'staff_flag',
                        '1'
                    )
                    ->whereNotIn(
                        'row_index',
                        $currentRowIndexes
                    )
                    ->update([

                        'staff_flag' =>
                        '0',

                        'updated_at' =>
                        now(),
                    ]);
            }


            // ==========================================================
            // SYNC TEMP UPLOADED DOCUMENTS
            // APP DOC / CONS DOC
            // ==========================================================

            $tempDocs = DB::table(
                'tnelb_temp_uploaded_documents'
            )
                ->where(
                    'login_id',
                    $request->input(
                        'login_id_store'
                    )
                )
                ->where(
                    'form_name',
                    $request->input(
                        'form_name'
                    )
                )
                ->where(
                    'license_name',
                    $request->input(
                        'license_name'
                    )
                )
                ->whereIn(
                    'document_category',
                    [
                        'app_doc',
                        'cons_doc'
                    ]
                )
                ->where(
                    'appl_type',
                    trim(
                        (string) $request->input(
                            'appl_type'
                        )
                    )
                )
                ->get();


            // ==========================================================
            // PROCESS EACH TEMP DOCUMENT
            // ==========================================================

            foreach ($tempDocs as $tempDoc) {

                // ------------------------------------------------------
                // ROW INDEX
                // ------------------------------------------------------

                $tempRowIndex = trim(
                    (string) (
                        $tempDoc->row_index ?? ''
                    )
                );


                if (
                    $tempRowIndex === '' ||
                    strtolower($tempRowIndex) === 'undefined' ||
                    strtolower($tempRowIndex) === 'null'
                ) {
                    continue;
                }


                $tempRowIndex =
                    (int) $tempRowIndex;


                // ------------------------------------------------------
                // BUILD DOCUMENT PATH
                // ------------------------------------------------------

                $documentPath = rtrim(
                    (string) (
                        $tempDoc->file_path ?? ''
                    ),
                    '/'
                );


                $fileName = ltrim(
                    (string) (
                        $tempDoc->file_name ?? ''
                    ),
                    '/'
                );


                if (
                    $documentPath !== '' &&
                    $fileName !== ''
                ) {

                    $documentPath .= '/' .
                        $fileName;
                } elseif (
                    $fileName !== ''
                ) {

                    $documentPath =
                        $fileName;
                }


                // ------------------------------------------------------
                // IGNORE EMPTY DOCUMENT
                // ------------------------------------------------------

                if (
                    trim($documentPath) === ''
                ) {
                    continue;
                }


                // ======================================================
                // FIND QC / QSC STAFF BY APPLICATION + ROW INDEX
                // ======================================================

                $staff = DB::table(
                    'cl_staff_tbl'
                )
                    ->where(
                        'application_id',
                        $applicationId
                    )
                    ->where(
                        'row_index',
                        $tempRowIndex
                    )
                    ->whereIn(
                        'staff_category',
                        ['QC', 'QSC']
                    )
                    ->where(
                        'staff_flag',
                        '1'
                    )
                    ->first();


                // ------------------------------------------------------
                // STAFF NOT FOUND
                // ------------------------------------------------------

                if (!$staff) {
                    continue;
                }


                // ======================================================
                // APP DOC
                // ======================================================

                if (
                    $tempDoc->document_category ===
                    'app_doc'
                ) {

                    DB::table(
                        'cl_staff_tbl'
                    )
                        ->where(
                            'id',
                            $staff->id
                        )
                        ->where(
                            'application_id',
                            $applicationId
                        )
                        ->where(
                            'row_index',
                            $tempRowIndex
                        )
                        ->update([

                            'app_doc' =>
                            $documentPath,

                            'updated_at' =>
                            now(),
                        ]);
                }


                // ======================================================
                // CONS DOC
                // ======================================================

                elseif (
                    $tempDoc->document_category ===
                    'cons_doc'
                ) {

                    DB::table(
                        'cl_staff_tbl'
                    )
                        ->where(
                            'id',
                            $staff->id
                        )
                        ->where(
                            'application_id',
                            $applicationId
                        )
                        ->where(
                            'row_index',
                            $tempRowIndex
                        )
                        ->update([

                            'cons_doc' =>
                            $documentPath,

                            'updated_at' =>
                            now(),
                        ]);
                }
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

                    $nextRowIndex++;
                }
            }
        }

        $proprietor_details = ProprietorformA::where('application_id', $applicationId)
            ->where('proprietor_flag', 1)
            ->get();

        if ($proprietor_details->isEmpty()) {

            $proprietor_old = ProprietorformA::where('application_id', $request->old_application)
                ->where('proprietor_flag', 1)
                ->get();

            if ($proprietor_old->isNotEmpty()) {

                foreach ($proprietor_old as $oldData) {

                    $newData = $oldData->toArray();

                    unset($newData['id']);

                    $newData['application_id'] = $applicationId;

                    ProprietorformA::create($newData);
                }
            }
        }


        $existing = EA_Application_model::where('application_id', $applicationId)->first();
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

        $payment = $isDraft ? 'draft' : 'success';

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
                ->whereIn('is_final', ['0', '2', '1'])
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
                ->whereIn('is_final', ['0', '2', '1'])
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
                ->whereIn('is_final', ['0', '2', '1'])
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
                ->whereIn('is_final', ['0', '2', '1'])
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

                // DB::table('tnelb_attachments_cl')->insert([
                //     'application_id' => $applicationId,
                //     'file_doc'       => $finalPath,
                //     'type'           => $doc->ownership_type,
                //     'created_at'     => now(),
                //     'updated_at'     => now(),
                // ]);

                DB::table('tnelb_attachments_cl')->updateOrInsert(
                    [
                        'application_id' => $applicationId,
                        'type' => $doc->ownership_type // important for unique condition
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
            // =======================================================
            // 8️⃣ MOVE EQUIPMENT FILES + INSERT/UPDATE INTO PERMANENT TABLE
            // =======================================================

            $dbFilePath_all = DocPathController::getPath($request);
            $proFolderPath  = public_path($dbFilePath_all->filepath_pro);

            // if (!File::exists($proFolderPath)) {
            //     File::makeDirectory($proFolderPath, 0755, true);
            // }

            // Fetch all temp docs grouped by equipment
            $allEquipmentDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('module', 'EQUIPMENTS DOCUMENT')
                ->where('document_sub_category', 'ED')
                ->whereIn('is_final', ['0', '2', '1'])
                ->get()
                ->groupBy('equip_code');

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

                // ===================================================
                // GET EXISTING RECORD TO PRESERVE OLD FILES
                // ===================================================

                $existingEquipment = DB::table('tnelb_equimentsuser_cl')
                    ->where('application_id', $applicationId)
                    ->where('equipment_id', $equipmentId)
                    ->first();

                $testReportPath = $existingEquipment->testreport_file ?? null;
                $purchaseReportPath = $existingEquipment->purchasereport_file ?? null;

                $equipmentDocs = $allEquipmentDocs[$equipmentId] ?? collect();

                // ===================================================
                // TEST REPORT
                // ===================================================

                $testDoc = $equipmentDocs
                    ->where('document_category', 'instrument_test_report')
                    ->sortByDesc('id')
                    ->first();

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

                // ===================================================
                // PURCHASE REPORT
                // ===================================================

                $purchaseDoc = $equipmentDocs
                    ->where('document_category', 'instrument_purchase_report')
                    ->sortByDesc('id')
                    ->first();

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

                // ===================================================
                // INSERT OR UPDATE RECORD
                // ===================================================

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
                        'updated_at'          => now(),
                        'created_at'          => now(),
                    ]
                );
            }
            // }


        });


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

            // dd($form->id);
            // exit;

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

        // dd($appl_type, $formName, $licenseName);exit;
        $model = $appl_type ? EA_Application_model::class : EA_Application_model::class;

        // $prefix = $appl_type;
        $prefix = ($appl_type === 'N') ? '' : $appl_type;
        $year = date('y');

        // Get last application for this specific prefix & year
        $lastApplication = $model::where('application_id', 'LIKE', $prefix . $formName . $licenseName . $year . '%')
            ->latest('id')
            ->value('application_id');

        $nextNumber = '000001';

        if ($lastApplication && preg_match('/(\d{6})$/', $lastApplication, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT);
        }

        return strtoupper($prefix . $formName . $licenseName . $year . $nextNumber);
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
