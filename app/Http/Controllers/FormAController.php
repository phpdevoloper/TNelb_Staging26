<?php

namespace App\Http\Controllers;

use App\Models\B_Application;
use App\Models\EA_Application_model;
use App\Models\Equipment_storetmp_A;
use App\Models\Equipmentforma_tbl;
use App\Models\ESA_Application_model;
use App\Models\ESB_Application_model;
use App\Models\mst_workflow;
use App\Models\Payment;
use App\Models\ProprietorformA;
use App\Models\Tnelb_Addressproof_cl;
use App\Models\Tnelb_banksolvency_a;
use App\Models\Tnelb_cl_validitycheck;
use App\Models\Tnelb_EA_QC_model;
use App\Models\TnelbApplicantStaffDetail;
use App\Services\Competency\CompetencyCertificateService;
use Carbon\Carbon;
use Exception;
// use Illuminate\Contracts\Validation\Rule;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\File;


use Illuminate\Support\Str;


class FormAController extends BaseController
{
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
    // QC check------------

    public function checkCCCertificate(Request $request)
    {

        //    dd($request->all());exit;
        // $dateofIssue = Carbon::createFromFormat(
        //     'd-m-Y',
        //     $request->dateof_issue
        // )->format('Y-m-d');

        // $validFrom = Carbon::createFromFormat(
        //     'd-m-Y',
        //     $request->valid_from
        // )->format('Y-m-d');

        // $validTo = Carbon::createFromFormat(
        //     'd-m-Y',
        //     $request->valid_to
        // )->format('Y-m-d');

        $dateofIssue = Carbon::createFromFormat(
            'Y-m-d',
            $request->dateof_issue
        )->format('Y-m-d');

        $validFrom = Carbon::createFromFormat(
            'Y-m-d',
            $request->valid_from
        )->format('Y-m-d');

        $validTo = Carbon::createFromFormat(
            'Y-m-d',
            $request->valid_to
        )->format('Y-m-d');


        // -------------------------------------------------
        // STEP 1: Check certificate details
        // -------------------------------------------------

        $certificate = DB::table('cc_forms_cert')
            ->where('certificate_no', $request->certificate_no)
            ->where('dateof_issue', $dateofIssue)
            ->where('valid_from', $validFrom)
            ->where('valid_to', $validTo)
            ->where('cert_status', 'A')
            ->first();

        // dd($certificate);exit;


        if (!$certificate) {
            return response()->json([
                'status' => false,
                'message' => 'Staff Certificate details are not valid.'
            ]);
        }


        // -------------------------------------------------
        // STEP 2: Check QC / QSC eligibility
        // -------------------------------------------------



        // -------------------------------------------------
        // STEP 3: Check certificate already mapped
        // -------------------------------------------------

        $existingStaff = DB::table('cl_staff_tbl')
            ->where('staff_cc_no', $request->certificate_no)
            ->where('staff_cc_first_issue', $dateofIssue)
            ->where('staff_cc_validity_from', $validFrom)
            ->where('staff_cc_validity_to', $validTo)
            // ->whereIn('staff_status', ['A', 'P'])
            ->where('staff_status', 'A')

            ->exists();



        $existingStaffpending = DB::table('cl_staff_tbl')
            ->where('staff_cc_no', $request->certificate_no)
            ->where('staff_cc_first_issue', $dateofIssue)
            ->where('staff_cc_validity_from', $validFrom)
            ->where('staff_cc_validity_to', $validTo)
            ->where('staff_status',  'P')
            ->exists();


        // dd($existingStaff);exit;

        if ($existingStaffpending) {
            return response()->json([
                'status' => false,
                'message' => 'Certificate is under process for another licence.'
            ]);
        }

        if ($existingStaff) {
            return response()->json([
                'status' => false,
                'message' => 'Certificate is already mapped with another licence.'
            ]);
        }


        // -------------------------------------------------
        // Certificate verified
        // -------------------------------------------------

        return response()->json([
            'status' => true,
            'message' => 'Certificate verified successfully.'
        ]);
    }
    public function checkQCCertificate(Request $request)
    {

        // $dateofIssue = Carbon::createFromFormat(
        //     'd-m-Y',
        //     $request->dateof_issue
        // )->format('Y-m-d');

        // $validFrom = Carbon::createFromFormat(
        //     'd-m-Y',
        //     $request->valid_from
        // )->format('Y-m-d');

        // $validTo = Carbon::createFromFormat(
        //     'd-m-Y',
        //     $request->valid_to
        // )->format('Y-m-d');

        $dateofIssue = Carbon::createFromFormat(
            'Y-m-d',
            $request->dateof_issue
        )->format('Y-m-d');

        $validFrom = Carbon::createFromFormat(
            'Y-m-d',
            $request->valid_from
        )->format('Y-m-d');

        $validTo = Carbon::createFromFormat(
            'Y-m-d',
            $request->valid_to
        )->format('Y-m-d');

        // dd($dateofIssue,$validFrom,$validTo);exit;
        // -------------------------------------------------
        // STEP 1: Check certificate details
        // -------------------------------------------------

        $certificate = DB::table('cc_forms_cert')
            ->where('certificate_no', $request->certificate_no)
            ->where('dateof_issue', $dateofIssue)
            ->where('valid_from', $validFrom)
            ->where('valid_to', $validTo)
            ->where('cert_status', 'A')
            ->orderBy('cc_id', 'desc')
            ->first();

        // dd($certificate);exit;


        if (!$certificate) {
            return response()->json([
                'status' => false,
                'message' => 'Staff Certificate details are not valid.'
            ]);
        }


        // -------------------------------------------------
        // STEP 2: Check QC / QSC eligibility
        // -------------------------------------------------

        if ($request->staffcategory === 'QC') {

            if ($certificate->qc != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'This certificate is not eligible for QC.'
                ]);
            }
        } elseif ($request->staffcategory === 'QSC') {

            if ($certificate->qsc != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'This certificate is not eligible for QSC.'
                ]);
            }
        } else {

            return response()->json([
                'status' => false,
                'message' => 'Invalid staff category.'
            ]);
        }


        // -------------------------------------------------
        // STEP 3: Check certificate already mapped
        // -------------------------------------------------

        $existingStaff = DB::table('cl_staff_tbl')
            ->where('staff_cc_no', $request->certificate_no)
            ->where('staff_cc_first_issue', $dateofIssue)
            ->where('staff_cc_validity_from', $validFrom)
            ->where('staff_cc_validity_to', $validTo)
            // ->whereIn('staff_status', ['A', 'P'])
            ->where('staff_status', 'A')

            ->exists();



        $existingStaffpending = DB::table('cl_staff_tbl')
            ->where('staff_cc_no', $request->certificate_no)
            ->where('staff_cc_first_issue', $dateofIssue)
            ->where('staff_cc_validity_from', $validFrom)
            ->where('staff_cc_validity_to', $validTo)
            ->where('staff_status',  'P')
            ->exists();


        // dd($existingStaff);exit;

        if ($existingStaffpending) {
            return response()->json([
                'status' => false,
                'message' => 'Certificate is under process for another licence.'
            ]);
        }

        if ($existingStaff) {
            return response()->json([
                'status' => false,
                'message' => 'Certificate is already mapped with another licence.'
            ]);
        }


        // -------------------------------------------------
        // Certificate verified
        // -------------------------------------------------

        return response()->json([
            'status' => true,
            'message' => 'Certificate verified successfully.'
        ]);
    }

    public function store(Request $request)
    {

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
        if ($request->appl_type === 'D') {
            $dataToSave['payment_status'] = $isDraft ? 'draft' : 'paid';
        } else {
            $dataToSave['payment_status'] = $isDraft ? 'draft' : 'pending';
        }
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

                if (!empty($partnerId)) {

                    $partner = ProprietorformA::where('id', $partnerId)
                        ->where('application_id', $applicationId)
                        ->where('ownership_type', 'pt')
                        ->first();

                    if ($partner) {

                        $partner->update($data);

                        $newPartnerIds[] = $partner->id;
                    }
                } else {

                    /*
            |--------------------------------------------------------------------------
            | INSERT NEW PARTNER
            |--------------------------------------------------------------------------
            */

                    $new = ProprietorformA::create($data);

                    $newPartnerIds[] = $new->id;
                }

                $count++;
            }

            // Deactivate removed partner rows
            ProprietorformA::where('application_id', $applicationId)
                ->where('ownership_type', 'pt')
                ->whereNotIn('id', $newPartnerIds)
                ->where('proprietor_flag', 1)
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

                    $director = ProprietorformA::where('id', $directorId)
                        ->where('application_id', $applicationId)
                        ->where('ownership_type', 'dr') // change if director uses another type
                        ->first();

                    if ($director) {

                        $director->update($data);

                        $newdirectorIds[] = $director->id;
                    }
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
                ->where('proprietor_flag', 1)
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
                ->whereIn('is_final', ['0', '2'])
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
                ->whereIn('is_final', ['0', '2'])
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
                ->whereIn('is_final', ['0', '2'])
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
                ->whereIn('is_final', ['0', '2'])
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
                ->whereIn('is_final', ['0', '2'])
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

            // dd($issued_licence);
            // exit;
            if ($appl_type === 'D') {
                EA_Application_model::where('application_id', $applicationId)
                    ->update([
                        'dt_submit'  => DB::raw('NOW()'),
                        'updated_at' => DB::raw('NOW()'),
                    ]);

                return response()->json([
                    'draft_status'    => false,
                    'message'         => 'Application Submitted Successfully!',
                    'login_id'        => $applicationId,
                    'transaction_id'  => null,
                    'application_type' => 'D',
                ]);
            }

            if ($appl_type === 'R') {




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
                // dd($form->id);
                //        exit;

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

                // $qclicence = DB::table('mst_licences')->where('cert_licence_code', $request->staffqc_category)->first();

                $applicantqfees = DB::table('cl_staff_tbl')
                    ->where('application_id', $applicationId)
                    ->whereIn('staff_category', ['QC', 'QSC'])
                    ->where('staff_flag', '1')
                    ->get();


                // dd($applicantqfees); exit;

                $qcFee = 0;

                foreach ($applicantqfees as $staff) {

                    // Get QC / QSC licence
                    $qclicence = DB::table('mst_licences')
                        ->where('cert_licence_code', $staff->staff_category)
                        ->first();

                    if (!$qclicence) {
                        continue;
                    }

                    // Get applicable fee for this QC / QSC
                    $fee = DB::table('tnelb_fees')
                        ->where('cert_licence_id', $qclicence->id)
                        ->where('fees_type', $appl_type)
                        ->whereDate('start_date', '<=', $today)
                        ->orderBy('start_date', 'desc')
                        ->value('fees');

                    if ($fee !== null) {
                        $qcFee += (float) $fee;
                    }
                }


                // dd($qcFee); exit;



                $instructions = $form->instructions;
                $licence_name = $form->licence_name;

                //   dd($licence_name);
                // exit;
                // HH24:MI:SS
                $dbNow  = DB::selectOne("SELECT TO_CHAR(NOW(), 'DD-MM-YYYY ') AS db_now")->db_now;
                $fees_details['dbNow'] = $dbNow;
                $fees_details['qcfee'] = $qcFee;

                $fees_details['total_fees'] =
                    (float) $paymentDetails[0]->total_fee + $qcFee;
                $fees_details['lateFees'] = $paymentDetails[0]->late_fee;
                $fees_details['late_months'] = $paymentDetails[0]->late_months;
                $fees_details['basic_fees'] = $paymentDetails[0]->base_fee;

                // $fees_details['basic_fees'] = $paymentDetails[0]->base_fee;

                // dd($fees_details['qcfee']);
                // exit;

            }

            // dd($form->license_name);
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

    // -------------------------------deleteProprietor----------------------




    // ------------------------renewal-----------------------------------



    public function storerenewal(Request $request)
    {

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

            // Search by application_id, not numeric id
            $existing = EA_Application_model::where('application_id', $recordId)->first();


            if ($existing) {
                if (!str_starts_with($existing->application_id, 'R')) {
                    $applicationId = $this->generateApplicationId(
                        $request->appl_type,
                        $request->form_name,
                        $request->license_name
                    );

                    // dd($applicationId); exit;
                } else {
                    $applicationId = $existing->application_id;
                }
            }
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

                    // dd($staffId);exit;

                    $existing = DB::table('cl_staff_tbl')
                        ->where('id', $staffId)
                        ->where('application_id', $applicationId)
                        ->whereIn('staff_category', ['QC', 'QSC'])
                        ->first();

                    // dd($applicationId); exit;

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
                    } else {

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
            }

            /*
        |--------------------------------------------------------------------------
        | NEW RECORD
        |--------------------------------------------------------------------------
        */

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


        $newProprietorIds = [];

        if ($request->has('proprietor_name')) {

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

                // ==================================================
                // EXISTING PROPRIETOR ID → UPDATE
                // ==================================================

                if (!empty($proprietorId)) {

                    $proprietor = ProprietorformA::where('id', $proprietorId)
                        ->where('application_id', $applicationId)
                        ->where('ownership_type', 'pr')
                        ->first();

                    if ($proprietor) {

                        $proprietor->update($data);

                        $newProprietorIds[] = $proprietor->id;
                    }
                } else {

                    // ==================================================
                    // NO ID → INSERT NEW PROPRIETOR
                    // ==================================================

                    $newProprietor = ProprietorformA::create($data);

                    $newProprietorIds[] = $newProprietor->id;
                }

                $count++;
            }

            // ==================================================
            // DEACTIVATE REMOVED PROPRIETOR RECORDS
            // ==================================================

            ProprietorformA::where('application_id', $applicationId)
                ->where('ownership_type', 'pr')
                ->where('proprietor_flag', 1)
                ->whereNotIn('id', $newProprietorIds)
                ->update([
                    'proprietor_flag' => 0
                ]);


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

                $competencyHolding = data_get(
                    $request->partner_competency,
                    $index,
                    'no'
                );

                $partnerId = data_get(
                    $request->partner_id,
                    $index
                );

                $data = [
                    'login_id' => $request->login_id_store,

                    'application_id' => $applicationId,

                    'proprietor_name' => strtoupper($name),

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

                    // ==========================================
                    // COMPETENCY CERTIFICATE
                    // ==========================================

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

                    'ownership_count' => $count,
                ];


                // =====================================================
                // EXISTING PARTNER → UPDATE
                // =====================================================

                if (!empty($partnerId)) {

                    $partner = ProprietorformA::where('id', $partnerId)
                        ->where('application_id', $applicationId)
                        ->where('ownership_type', 'pt')
                        ->first();

                    if ($partner) {

                        $partner->update($data);

                        $newPartnerIds[] = $partner->id;
                    }
                }

                // =====================================================
                // NEW PARTNER → INSERT
                // =====================================================

                else {

                    /*
             * Check whether this application already has
             * partner records.
             */
                    $existingPartner = ProprietorformA::where(
                        'application_id',
                        $applicationId
                    )
                        ->where('ownership_type', 'pt')
                        ->exists();


                    if ($existingPartner) {

                        // Application already has partners.
                        // Insert this new partner.

                        $newPartner = ProprietorformA::create($data);

                        $newPartnerIds[] = $newPartner->id;
                    } else {

                        /*
                 * No partner exists in ProprietorformA.
                 *
                 * Get all records from EA_Application_model.
                 */
                        $records = EA_Application_model::where(
                            'application_id',
                            $recordId
                        )->get();


                        if ($records->count() > 0) {

                            /*
                     * If you need to copy old partner information
                     * from EA_Application_model, do it here.
                     *
                     * Do NOT blindly create one row for every
                     * $record unless every record represents
                     * a partner.
                     */

                            $newPartner = ProprietorformA::create($data);

                            $newPartnerIds[] = $newPartner->id;
                        } else {

                            // No old EA record → normal new insert

                            $newPartner = ProprietorformA::create($data);

                            $newPartnerIds[] = $newPartner->id;
                        }
                    }
                }

                $count++;
            }


            // =========================================================
            // DEACTIVATE REMOVED PARTNERS
            // =========================================================

            ProprietorformA::where('application_id', $applicationId)
                ->where('ownership_type', 'pt')
                ->whereNotIn('id', $newPartnerIds)
                ->where('proprietor_flag', 1)
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

                $directorId = data_get($request->director_id, $index);

                $competencyHolding = data_get(
                    $request->director_competency,
                    $index,
                    'no'
                );

                $data = [

                    'login_id' => $request->login_id_store,

                    'application_id' => $applicationId,

                    'proprietor_name' => strtoupper($name),

                    'ownership_type' => 'dr',

                    'ownership_count' => $count,

                    'proprietor_flag' => 1,

                    // MANAGING DIRECTOR
                    'managing_director' => strtoupper(
                        data_get(
                            $request->director_managing_director,
                            $index,
                            'NO'
                        )
                    ),

                    // BASIC DETAILS
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

                    // QUALIFICATION
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

                    // BUSINESS
                    'present_business' => strtoupper(
                        data_get(
                            $request->director_present_business,
                            $index,
                            ''
                        )
                    ),

                    // COMPETENCY
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


                // =====================================================
                // UPDATE EXISTING DIRECTOR
                // =====================================================

                if (!empty($directorId)) {

                    $director = ProprietorformA::where('id', $directorId)
                        ->where('application_id', $applicationId)
                        ->where('ownership_type', 'dr')
                        ->first();

                    if ($director) {

                        $director->update($data);

                        $newdirectorIds[] = $director->id;
                    }
                }

                // =====================================================
                // NEW DIRECTOR
                // =====================================================

                else {

                    /*
             * Check whether this application already has
             * director records.
             */
                    $existingDirector = ProprietorformA::where(
                        'application_id',
                        $applicationId
                    )
                        ->where('ownership_type', 'dr')
                        ->exists();


                    if ($existingDirector) {

                        // Application has directors already
                        // So simply INSERT the new director.

                        $newDirector = ProprietorformA::create($data);

                        $newdirectorIds[] = $newDirector->id;
                    } else {

                        /*
                 * No director record exists for this application.
                 *
                 * Get existing EA application records.
                 */
                        $records = EA_Application_model::where(
                            'application_id',
                            $recordId
                        )->get();


                        /*
                 * If EA records are available, create the
                 * required director records from them.
                 */
                        if ($records->count() > 0) {

                            foreach ($records as $record) {

                                $insertData = $data;

                                /*
                         * If you need to copy values from
                         * EA_Application_model, assign them here.
                         *
                         * Example:
                         *
                         * $insertData['some_column'] =
                         *     $record->some_column;
                         */

                                $newDirector = ProprietorformA::create(
                                    $insertData
                                );

                                $newdirectorIds[] = $newDirector->id;
                            }
                        } else {

                            // No EA records → insert submitted director normally

                            $newDirector = ProprietorformA::create($data);

                            $newdirectorIds[] = $newDirector->id;
                        }
                    }
                }

                $count++;
            }


            // =========================================================
            // DEACTIVATE REMOVED DIRECTORS
            // =========================================================



            // ==========================================
            // DEACTIVATE REMOVED DIRECTORS
            // ==========================================

            ProprietorformA::where('application_id', $applicationId)
                ->whereNotIn('id', $newdirectorIds)
                ->where('ownership_type', 'dr')
                ->where('proprietor_flag', 1)
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




        // if ($existing) {

        //     $updateData = collect($dataToSave)
        //         ->except(['aadhaar_doc', 'pancard_doc', 'gst_doc'])
        //         ->toArray();



        //     EA_Application_model::where('application_id', $existing->application_id)
        //         ->update($updateData);
        // } else {
        //     $dataToSave['created_at'] = DB::raw('NOW()');

        //     $createData = collect($dataToSave)
        //         ->except(['aadhaar_doc', 'pancard_doc', 'gst_doc'])
        //         ->toArray();

        //     EA_Application_model::create($createData);
        //     $message = $isDraft ? 'Draft saved successfully!' : 'Application submitted successfully!';
        // }
        // dd($request->appl_type); exit;
        if ($existing) {
            // AEA
            // if (preg_match('/^AEA/i', trim($existing->application_id))) {
            //     // Generate a new RAEA ID
            //     $newApplicationId = $this->generateApplicationId(
            //         $request->appl_type,
            //         $request->form_name,
            //         $request->license_name,
            //         'RAEA'
            //     );

                // dd($newApplicationId);
                // exit;

                $formData['application_id'] = $newApplicationId;
                $formData['login_id'] = $request->login_id_store;
                $formData['payment_status'] = $isDraft ? 'draft' : 'pending';
                $formData['application_status'] = 'P';

                $formData['old_application'] = $recordId;

                $formData['license_number'] = $request->license_number;
                // $formData['created_at'] = now();
                $formData['updated_at'] = now();

                $formData['appl_type'] = $request->appl_type;

                // dd($formData['appl_type']);
                // exit;

                // Insert as new record
                EA_Application_model::create($formData);

                $message = $isDraft
                    ? 'Draft saved successfully with new RAEA ID!'
                    : 'Application submitted successfully with new RAEA ID!';
            } else {
                // Normal update


                EA_Application_model::where('application_id', $existing->application_id)
                    ->update($updateData);

                $message = $isDraft
                    ? 'Draft updated successfully!'
                    : 'Application updated successfully!';
            }
        } else {
            //    $dataToSave['old_application'] = $recordId ?? null;
            $dataToSave['created_at'] = DB::raw('NOW()');




            EA_Application_model::create($dataToSave);

            $message = $isDraft
                ? 'Draft saved successfully!'
                : 'Application submitted successfully!';
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
                ->whereIn('is_final', ['0', '2'])
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
                ->whereIn('is_final', ['0', '2'])
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
                ->whereIn('is_final', ['0', '2'])
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
                ->whereIn('is_final', ['0', '2'])
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
        });

        // =======================================================
        // 8️⃣ EQUIPMENT FILES + INSERT / UPDATE
        // =======================================================

        $dbFilePath_all = DocPathController::getPath($request);
        $proFolderPath  = public_path($dbFilePath_all->filepath_pro);

        if (!File::exists($proFolderPath)) {
            File::makeDirectory($proFolderPath, 0755, true);
        }


        // =======================================================
        // GET NEW APPLICATION TEMP EQUIPMENT DOCUMENTS
        // =======================================================

        $allEquipmentDocs = DB::table('tnelb_temp_uploaded_documents')
            ->where('login_id', $request->login_id_store)
            ->where('module', 'EQUIPMENTS DOCUMENT')
            ->where('document_sub_category', 'ED')
            ->whereIn('is_final', ['0', '2'])
            ->get()
            ->groupBy('equip_code');


        // =======================================================
        // CHECK WHETHER NEW APPLICATION ALREADY HAS EQUIPMENT
        // =======================================================

        $newApplicationHasEquipment = DB::table('tnelb_equimentsuser_cl')
            ->where('application_id', $applicationId)
            ->exists();


        // =======================================================
        // IF NEW APPLICATION HAS NO EQUIPMENT
        // COPY OLD APPLICATION EQUIPMENT
        // =======================================================

        if (!$newApplicationHasEquipment && !empty($recordId)) {

            $oldEquipmentRecords = DB::table('tnelb_equimentsuser_cl')
                ->where('application_id', $recordId)
                ->get();


            foreach ($oldEquipmentRecords as $oldEquipment) {

                // ---------------------------------------------------
                // OLD FILE PATHS
                // ---------------------------------------------------

                $testReportPath = $oldEquipment->testreport_file ?? null;

                $purchaseReportPath =
                    $oldEquipment->purchasereport_file ?? null;


                // ---------------------------------------------------
                // CHECK WHETHER NEW DOCUMENT WAS UPLOADED
                // ---------------------------------------------------

                $equipmentDocs =
                    $allEquipmentDocs[$oldEquipment->equipment_id]
                    ?? collect();


                // ===================================================
                // TEST REPORT
                // ===================================================

                $testDoc = $equipmentDocs
                    ->where(
                        'document_category',
                        'instrument_test_report'
                    )
                    ->sortByDesc('id')
                    ->first();


                if ($testDoc) {

                    $tempFullPath = public_path(
                        $testDoc->file_path . '/' . $testDoc->file_name
                    );

                    $proFullPath = $proFolderPath .
                        '/' .
                        $testDoc->file_name;


                    if (File::exists($tempFullPath)) {

                        File::copy(
                            $tempFullPath,
                            $proFullPath
                        );

                        $testReportPath =
                            $dbFilePath_all->filepath_pro .
                            '/' .
                            $testDoc->file_name;


                        DB::table('tnelb_temp_uploaded_documents')
                            ->where('id', $testDoc->id)
                            ->update([
                                'is_final'   => '1',
                                'moved_as'   => $request->input('form_action'),
                                'record_id_app' => $applicationId,
                                'updated_at' => now(),
                            ]);
                    }
                }


                // ===================================================
                // PURCHASE REPORT
                // ===================================================

                $purchaseDoc = $equipmentDocs
                    ->where(
                        'document_category',
                        'instrument_purchase_report'
                    )
                    ->sortByDesc('id')
                    ->first();


                if ($purchaseDoc) {

                    $tempFullPath = public_path(
                        $purchaseDoc->file_path . '/' . $purchaseDoc->file_name
                    );

                    $proFullPath = $proFolderPath .
                        '/' .
                        $purchaseDoc->file_name;


                    if (File::exists($tempFullPath)) {

                        File::copy(
                            $tempFullPath,
                            $proFullPath
                        );

                        $purchaseReportPath =
                            $dbFilePath_all->filepath_pro .
                            '/' .
                            $purchaseDoc->file_name;


                        DB::table('tnelb_temp_uploaded_documents')
                            ->where('id', $purchaseDoc->id)
                            ->update([
                                'is_final'   => '1',
                                'moved_as'   => $request->input('form_action'),
                                'record_id_app' => $applicationId,
                                'updated_at' => now(),
                            ]);
                    }
                }


                // ===================================================
                // INSERT OLD EQUIPMENT INTO NEW APPLICATION
                // ===================================================

                DB::table('tnelb_equimentsuser_cl')->insert([

                    'application_id' =>
                    $applicationId,

                    'login_id' =>
                    $request->login_id_store,

                    'form_name' =>
                    $request->form_name,

                    'license_name' =>
                    $request->license_name,

                    'licence_id' =>
                    $oldEquipment->licence_id,

                    'equipment_id' =>
                    $oldEquipment->equipment_id,

                    'serial_no' =>
                    $oldEquipment->serial_no,

                    'model_no' =>
                    $oldEquipment->model_no,

                    'testreport_file' =>
                    $testReportPath,

                    'purchasereport_file' =>
                    $purchaseReportPath,

                    'dateoftest' =>
                    $oldEquipment->dateoftest,

                    'ipaddress' =>
                    $request->ip(),

                    'created_at' =>
                    now(),

                    'updated_at' =>
                    now(),
                ]);
            }
        }


        // =======================================================
        // PROCESS CURRENT REQUEST EQUIPMENT
        // =======================================================

        if ($request->has('equipments') && is_array($request->equipments)) {

            foreach ($request->equipments as $index => $equipment) {

                // ---------------------------------------------------
                // IGNORE EMPTY EQUIPMENT ROW
                // ---------------------------------------------------

                if (
                    empty($equipment['equip_id']) &&
                    empty($request->serial_no[$index]) &&
                    empty($request->model[$index])
                ) {
                    continue;
                }


                $equipmentId = $equipment['equip_id'] ?? null;

                $licenceId = $equipment['licence_id'] ?? null;

                $serialNo =
                    $request->serial_no[$index] ?? null;

                $modelNo =
                    $request->model[$index] ?? null;

                $dateOfTest =
                    $request->date_of_test[$index] ?? null;


                // ===================================================
                // FIND EXISTING EQUIPMENT FOR NEW APPLICATION
                // ===================================================

                $existingEquipment = null;

                if (!empty($equipmentId)) {

                    $existingEquipment = DB::table(
                        'tnelb_equimentsuser_cl'
                    )
                        ->where(
                            'application_id',
                            $applicationId
                        )
                        ->where(
                            'equipment_id',
                            $equipmentId
                        )
                        ->first();
                }


                // ===================================================
                // PRESERVE EXISTING FILES
                // ===================================================

                $testReportPath =
                    $existingEquipment->testreport_file ?? null;

                $purchaseReportPath =
                    $existingEquipment->purchasereport_file ?? null;


                // ===================================================
                // GET TEMP DOCUMENTS
                // ===================================================

                $equipmentDocs =
                    $allEquipmentDocs[$equipmentId]
                    ?? collect();


                // ===================================================
                // TEST REPORT
                // ===================================================

                $testDoc = $equipmentDocs
                    ->where(
                        'document_category',
                        'instrument_test_report'
                    )
                    ->sortByDesc('id')
                    ->first();


                if ($testDoc) {

                    $tempFullPath = public_path(
                        $testDoc->file_path .
                            '/' .
                            $testDoc->file_name
                    );

                    $proFullPath =
                        $proFolderPath .
                        '/' .
                        $testDoc->file_name;


                    if (File::exists($tempFullPath)) {

                        File::copy(
                            $tempFullPath,
                            $proFullPath
                        );

                        $testReportPath =
                            $dbFilePath_all->filepath_pro .
                            '/' .
                            $testDoc->file_name;


                        DB::table(
                            'tnelb_temp_uploaded_documents'
                        )
                            ->where(
                                'id',
                                $testDoc->id
                            )
                            ->update([
                                'is_final' =>
                                '1',

                                'moved_as' =>
                                $request->input('form_action'),

                                'record_id_app' =>
                                $applicationId,

                                'updated_at' =>
                                now(),
                            ]);
                    }
                }


                // ===================================================
                // PURCHASE REPORT
                // ===================================================

                $purchaseDoc = $equipmentDocs
                    ->where(
                        'document_category',
                        'instrument_purchase_report'
                    )
                    ->sortByDesc('id')
                    ->first();


                if ($purchaseDoc) {

                    $tempFullPath = public_path(
                        $purchaseDoc->file_path .
                            '/' .
                            $purchaseDoc->file_name
                    );

                    $proFullPath =
                        $proFolderPath .
                        '/' .
                        $purchaseDoc->file_name;


                    if (File::exists($tempFullPath)) {

                        File::copy(
                            $tempFullPath,
                            $proFullPath
                        );

                        $purchaseReportPath =
                            $dbFilePath_all->filepath_pro .
                            '/' .
                            $purchaseDoc->file_name;


                        DB::table(
                            'tnelb_temp_uploaded_documents'
                        )
                            ->where(
                                'id',
                                $purchaseDoc->id
                            )
                            ->update([
                                'is_final' =>
                                '1',

                                'moved_as' =>
                                $request->input('form_action'),

                                'record_id_app' =>
                                $applicationId,

                                'updated_at' =>
                                now(),
                            ]);
                    }
                }


                // ===================================================
                // EQUIPMENT DATA
                // ===================================================

                $equipmentData = [

                    'login_id' =>
                    $request->login_id_store,

                    'form_name' =>
                    $request->form_name,

                    'license_name' =>
                    $request->license_name,

                    'licence_id' =>
                    $licenceId,

                    'serial_no' =>
                    $serialNo,

                    'model_no' =>
                    $modelNo,

                    'testreport_file' =>
                    $testReportPath,

                    'purchasereport_file' =>
                    $purchaseReportPath,

                    'dateoftest' =>
                    $dateOfTest,

                    'ipaddress' =>
                    $request->ip(),

                    'updated_at' =>
                    now(),
                ];


                // ===================================================
                // UPDATE EXISTING
                // ===================================================

                if ($existingEquipment) {

                    DB::table('tnelb_equimentsuser_cl')
                        ->where(
                            'id',
                            $existingEquipment->id
                        )
                        ->update($equipmentData);
                } else {

                    // =================================================
                    // INSERT NEW EQUIPMENT
                    // =================================================

                    DB::table('tnelb_equimentsuser_cl')
                        ->insert(
                            array_merge(
                                [
                                    'application_id' =>
                                    $applicationId,

                                    'equipment_id' =>
                                    $equipmentId,

                                    'created_at' =>
                                    now(),
                                ],
                                $equipmentData
                            )
                        );
                }
            }
        }


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

            // dd($issued_licence);
            // exit;
            if ($appl_type === 'D') {
                EA_Application_model::where('application_id', $applicationId)
                    ->update([
                        'dt_submit'  => DB::raw('NOW()'),
                        'updated_at' => DB::raw('NOW()'),
                    ]);

                return response()->json([
                    'draft_status'    => false,
                    'message'         => 'Application Submitted Successfully!',
                    'login_id'        => $applicationId,
                    'transaction_id'  => null,
                    'application_type' => 'D',
                ]);
            }

            if ($appl_type === 'R') {




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
                // dd($form->id);
                //        exit;

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

                // $qclicence = DB::table('mst_licences')->where('cert_licence_code', $request->staffqc_category)->first();

                $applicantqfees = DB::table('cl_staff_tbl')
                    ->where('application_id', $applicationId)
                    ->whereIn('staff_category', ['QC', 'QSC'])
                    ->where('staff_flag', '1')
                    ->get();


                // dd($applicantqfees); exit;

                $qcFee = 0;

                foreach ($applicantqfees as $staff) {

                    // Get QC / QSC licence
                    $qclicence = DB::table('mst_licences')
                        ->where('cert_licence_code', $staff->staff_category)
                        ->first();

                    if (!$qclicence) {
                        continue;
                    }

                    // Get applicable fee for this QC / QSC
                    $fee = DB::table('tnelb_fees')
                        ->where('cert_licence_id', $qclicence->id)
                        ->where('fees_type', $appl_type)
                        ->whereDate('start_date', '<=', $today)
                        ->orderBy('start_date', 'desc')
                        ->value('fees');

                    if ($fee !== null) {
                        $qcFee += (float) $fee;
                    }
                }


                // dd($qcFee); exit;



                $instructions = $form->instructions;
                $licence_name = $form->licence_name;

                //   dd($licence_name);
                // exit;
                // HH24:MI:SS
                $dbNow  = DB::selectOne("SELECT TO_CHAR(NOW(), 'DD-MM-YYYY ') AS db_now")->db_now;
                $fees_details['dbNow'] = $dbNow;
                $fees_details['qcfee'] = $qcFee;

                $fees_details['total_fees'] =
                    (float) $paymentDetails[0]->total_fee + $qcFee;
                $fees_details['lateFees'] = $paymentDetails[0]->late_fee;
                $fees_details['late_months'] = $paymentDetails[0]->late_months;
                $fees_details['basic_fees'] = $paymentDetails[0]->base_fee;

                // $fees_details['basic_fees'] = $paymentDetails[0]->base_fee;

                // dd($fees_details['qcfee']);
                // exit;

            }

            // dd($form->license_name);
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

            ]);
        }

        return response()->json([
            'message' => 'Draft',
            'login_id' => $applicationId,
            'transaction_id' => $isDraft ? 'DRAFT' . rand(100000, 999999) : 'TXN' . rand(100000, 999999),
            'draft_status' => $isDraft,

        ]);
    }
    // -----------instructions--------------------

    public function getFormInstructions(Request $request)
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


                dd($paymentDetails);
                exit;
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




    // -----------------A latest ---------end-------------

    public function store_bk(Request $request)
    {
        $isDraft = $request->input('form_action') === 'draft';
        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);
        // ✅ Validation Rules
        $rules = [
            'applicant_name' => 'required|string|max:255',
            'business_address' => 'required|string|max:500',
            'authorised_name_designation' => 'required',
            'authorised_name' => 'nullable|string|max:255',
            'authorised_designation' => 'nullable|string|max:255',
            'previous_contractor_license' => 'required|string|max:10',
            'previous_application_number' => 'nullable|string|max:50',
            'previous_application_validity' => 'nullable',
            'bank_address' => 'required|string|max:500',
            'bank_validity' => 'required|date',
            'bank_amount' => 'required|numeric|min:1',
            'criminal_offence' => ['required', 'string', Rule::in(['yes', 'no'])],
            'consent_letter_enclose' => ['required', 'string', Rule::in(['yes', 'no'])],
            'cc_holders_enclosed' => ['required', 'string', Rule::in(['yes', 'no'])],
            'purchase_bill_enclose' => ['required', 'string', Rule::in(['yes', 'no'])],
            'test_reports_enclose' => ['required', 'string', Rule::in(['yes', 'no'])],
            'specimen_signature_enclose' => ['required', 'string', Rule::in(['yes', 'no'])],
            'separate_sheet' => ['required', 'string', Rule::in(['yes', 'no'])],
            'form_name' => 'required|string|max:255',
            'license_name' => 'required|string|max:255',
            'aadhaar' => 'required|digits:12',
            'pancard' => 'required|alpha_num|size:10',
            'gst_number' => 'required|string|min:15',
            'declaration1' => 'required|string|max:255',
            'declaration2' => 'required|string|max:255',

            // ✅ Proprietor Validation Rules

            // 'proprietor_name' => 'required|string|max:255',
            // 'proprietor_address' => 'nullable|string|max:500',
            // 'age' => 'nullable|integer|min:18|max:100',
            // 'qualification' => 'nullable|string|max:255',
            // 'fathers_name' => 'nullable|string|max:255',
            // 'present_business' => 'nullable|string|max:500',

            // 'competency_certificate_holding' => ['required', Rule::in(['Y', 'N'])],
            // 'competency_certificate_number' => 'nullable|string|max:50',
            // 'competency_certificate_validity' => 'nullable|date',

            // 'presently_employed' => ['required', Rule::in(['Y', 'N'])],
            // 'presently_employed_name' => 'nullable|string|max:255',
            // 'presently_employed_address' => 'nullable|string|max:500',

            // 'previous_experience' => ['required', Rule::in(['Y', 'N'])],
            // 'previous_experience_name' => 'nullable|string|max:255',
            // 'previous_experience_address' => 'nullable|string|max:500',
            // 'previous_experience_lnumber' => 'nullable|string|max:50',
        ];

        // $rules += [
        //     'proprietor_name' => ['required', 'array', 'min:1'],
        //     // 'proprietor_name.*' => ['required', 'string', 'max:255'],

        //     'proprietor_address' => ['required', 'array', 'min:1'],
        //     'proprietor_address.*' => ['required', 'string', 'max:500'],

        //     'age' => ['required', 'array', 'min:1'],
        //     'age.*' => ['required', 'integer', 'min:18', 'max:100'],

        //     'qualification' => ['required', 'array', 'min:1'],
        //     'qualification.*' => ['required', 'string', 'max:255'],

        //     'fathers_name' => ['required', 'array', 'min:1'],
        //     'fathers_name.*' => ['required', 'string', 'max:255'],

        //     'present_business' => ['nullable', 'array'],
        //     'present_business.*' => ['nullable', 'string', 'max:255'],

        //     'competency_certificate_holding' => ['required', 'array'],
        //     'competency_certificate_holding.*' => ['required', 'in:yes,no'],

        //     'competency_certificate_number' => ['nullable', 'array'],
        //     'competency_certificate_number.*' => ['nullable', 'string', 'max:255'],

        //     'competency_certificate_validity' => ['nullable', 'array'],
        //     'competency_certificate_validity.*' => ['nullable', 'date'],

        //     'presently_employed' => ['required', 'array'],
        //     'presently_employed.*' => ['required', 'in:yes,no'],

        //     'presently_employed_name' => ['nullable', 'array'],
        //     'presently_employed_name.*' => ['nullable', 'string', 'max:255'],

        //     'presently_employed_address' => ['nullable', 'array'],
        //     'presently_employed_address.*' => ['nullable', 'string', 'max:500'],

        //     'previous_experience' => ['required', 'array'],
        //     'previous_experience.*' => ['required', 'in:yes,no'],

        //     'previous_experience_name' => ['nullable', 'array'],
        //     'previous_experience_name.*' => ['nullable', 'string', 'max:255'],

        //     'previous_experience_address' => ['nullable', 'array'],
        //     'previous_experience_address.*' => ['nullable', 'string', 'max:500'],

        //     'previous_experience_lnumber' => ['nullable', 'array'],
        //     'previous_experience_lnumber.*' => ['nullable', 'string', 'max:100'],
        // ];
        // $rules += [
        //     'staff_name' => 'required|string|max:255',
        //     'staff_qualification' => 'nullable|string|max:255',
        //     'cc_number' => 'nullable|string|max:50',
        //     'cc_validity' => 'nullable|date',
        // ];

        // ✅ Relax validation for Draft
        if ($isDraft) {
            foreach ($rules as $key => $rule) {
                $rules[$key] = str_replace('required', 'nullable', $rule);
            }
        }


        $validatedData = $request->validate($rules);

        $lastApplication = EA_Application_model::latest('id')->value('application_id');

        $nextNumber = '0000001';

        if ($lastApplication && preg_match('/(\d{7})$/', $lastApplication, $matches)) {
            $lastNumber = (int) $matches[1];
            $nextNumber = str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
        }


        $newApplicationId = $request->form_name . $request->license_name . date('y') . $nextNumber;




        $aadhaarFilename = null;
        if ($request->hasFile('aadhaar_doc')) {
            $aadhaarPath = 'documents/aadhaar_' . time() . '.' . $request->file('aadhaar_doc')->getClientOriginalExtension();
            $request->file('aadhaar_doc')->move(public_path('documents'), basename($aadhaarPath));
            $aadhaarFilename = Crypt::encryptString($aadhaarPath); // Encrypt file path
        }

        $panFilename = null;
        if ($request->hasFile('pancard_doc')) {
            $panPath = 'documents/pan_' . time() . '.' . $request->file('pancard_doc')->getClientOriginalExtension();
            $request->file('pancard_doc')->move(public_path('documents'), basename($panPath));
            $panFilename = Crypt::encryptString($panPath); // Encrypt file path
        }

        $gstFilename = null;
        if ($request->hasFile('gst_doc')) {
            $gstPath = 'documents/gst_' . time() . '.' . $request->file('gst_doc')->getClientOriginalExtension();
            $request->file('gst_doc')->move(public_path('documents'), basename($gstPath));
            $gstFilename = Crypt::encryptString($gstPath); // Encrypt file path
        }



        DB::table('tnelb_applicant_doc_A')->insert([
            'login_id'       => $request->login_id_store,
            'application_id' => $newApplicationId,
            'aadhaar_doc'    => $aadhaarFilename,
            'pancard_doc'    => $panFilename,
            'gst_doc'        => $gstFilename,
            'created_at'     => now(),
            'updated_at'     => now()
        ]);

        $validatedData['aadhaar'] = Crypt::encryptString($validatedData['aadhaar']);
        $validatedData['pancard'] = Crypt::encryptString($validatedData['pancard']);
        $validatedData['gst_number'] = Crypt::encryptString($validatedData['gst_number']);

        $form = EA_Application_model::create([
            'login_id' => $request->login_id_store,
            'application_id' => $newApplicationId,
            'application_status' => 'P',
            'license_number' => '',
            'payment_status' => $isDraft ? 'draft' : 'paid',
            'name_of_authorised_to_sign' => !empty($request->name_of_authorised_to_sign)
                ? json_encode($request->name_of_authorised_to_sign)
                : null,

            'enclosure' => '1',
            'previous_contractor_license' => $request->previous_contractor_license,
            'criminal_offence' => $request->criminal_offence,
            'consent_letter_enclose' => $request->consent_letter_enclose,
            'cc_holders_enclosed' => $request->cc_holders_enclosed,
            'purchase_bill_enclose' => $request->purchase_bill_enclose,
            'test_reports_enclose' => $request->test_reports_enclose,
            'specimen_signature_enclose' => $request->specimen_signature_enclose,


            'separate_sheet' => $request->separate_sheet,

        ] + $validatedData);





        if ($request->has('proprietor_name')) {

            foreach ($request->proprietor_name as $index => $proprietor_name) {

                $competencyHolding = $request->competency_certificate_holding[$index] ?? 'no';
                // dd($request->all());
                // die;
                $list = ProprietorformA::create([
                    'login_id' => $request->login_id_store,
                    'application_id' => $newApplicationId,
                    'proprietor_name' => $proprietor_name ?? null,

                    //         'proprietor_address' => '123 Test Street',
                    // 'age' => 35,
                    // 'qualification' => 'B.Tech',
                    // 'fathers_name' => 'John Doe',
                    // 'present_business' => 'Electrical Works',

                    // 'competency_certificate_holding' => 'yes',
                    // 'competency_certificate_number' => 'CC123456',
                    // 'competency_certificate_validity' => '2026-12-31',

                    // 'presently_employed' => 'no',
                    // 'presently_employed_name' => null,
                    // 'presently_employed_address' => null,

                    // 'previous_experience' => 'yes',
                    // 'previous_experience_name' => 'XYZ Pvt Ltd',
                    // 'previous_experience_address' => '456 Business Park',
                    // 'previous_experience_lnumber' => 'LN789012',

                    'proprietor_address' => data_get($request->proprietor_address, $index),
                    'age' => data_get($request->age, $index),
                    'qualification' => data_get($request->qualification, $index),
                    'fathers_name' => data_get($request->fathers_name, $index, 'Not Provided'),
                    'present_business' => data_get($request->present_business, $index),

                    'competency_certificate_holding' => data_get($request->competency_certificate_holding, $index, 'no'),
                    'competency_certificate_number' => data_get($request->competency_certificate_holding, $index) === 'yes'
                        ? data_get($request->competency_certificate_number, $index)
                        : null,
                    'competency_certificate_validity' => data_get($request->competency_certificate_holding, $index) === 'yes'
                        ? data_get($request->competency_certificate_validity, $index)
                        : null,

                    'presently_employed' => data_get($request->presently_employed, $index, 'no'),
                    'presently_employed_name' => data_get($request->presently_employed, $index) === 'yes'
                        ? data_get($request->presently_employed_name, $index)
                        : null,
                    'presently_employed_address' => data_get($request->presently_employed, $index) === 'yes'
                        ? data_get($request->presently_employed_address, $index)
                        : null,

                    'previous_experience' => data_get($request->previous_experience, $index, 'no'),
                    'previous_experience_name' => data_get($request->previous_experience, $index) === 'yes'
                        ? data_get($request->previous_experience_name, $index)
                        : null,
                    'previous_experience_address' => data_get($request->previous_experience, $index) === 'yes'
                        ? data_get($request->previous_experience_address, $index)
                        : null,
                    'previous_experience_lnumber' => data_get($request->previous_experience, $index) === 'yes'
                        ? data_get($request->previous_experience_lnumber, $index)
                        : null,

                    'previous_experience_lnumber_validity' => data_get($request->previous_experience, $index) === 'yes'
                        ? data_get($request->previous_experience_lnumber_validity, $index)
                        : null,






                    // 'competency_certificate_holding' => $request->competency_certificate_holding[$index] ?? 'no',
                    // 'competency_certificate_number' => $request->competency_certificate_number[$index] ?? null,
                    // 'competency_certificate_validity' => $request->competency_certificate_validity[$index] ?? null,
                    // 'competency_certificate_number' =>
                    //     ($request->competency_certificate_holding[$index] === 'yes') ?
                    //     ($request->competency_certificate_number[$index] ?? null) : null,
                    // 'competency_certificate_validity' =>
                    //     ($request->competency_certificate_holding[$index] === 'yes') ?
                    //     ($request->competency_certificate_validity[$index] ?? null) : null,
                    // 'competency_certificate_holding' => 'yes',
                    // 'competency_certificate_number' => 'CC123456',
                    // 'competency_certificate_validity' => '2026-12-31',

                    // 'presently_employed' => 'no',
                    // 'presently_employed_name' => null,
                    // 'presently_employed_address' => null,

                    // 'previous_experience' => 'yes',
                    // 'previous_experience_name' => 'XYZ Pvt Ltd',
                    // 'previous_experience_address' => '456 Business Park',
                    // 'previous_experience_lnumber' => 'LN789012',
                    // 'competency_certificate_number' => ($request->competency_certificate_holding[$index] === 'yes')
                    //     ? ($request->competency_certificate_number[$index] ?? null)
                    //     : null,
                    // 'competency_certificate_validity' => ($request->competency_certificate_holding[$index] === 'yes')
                    //     ? ($request->competency_certificate_validity[$index] ?? null)
                    //     : null,

                    // 'presently_employed' => $request->presently_employed[$index] ?? 'no',
                    // 'presently_employed_name' => ($request->presently_employed[$index] === 'yes')
                    //     ? ($request->presently_employed_name[$index] ?? null)
                    //     : null,
                    // 'presently_employed_address' => ($request->presently_employed[$index] === 'yes')
                    //     ? ($request->presently_employed_address[$index] ?? null)
                    //     : null,

                    // 'previous_experience' => $request->previous_experience[$index] ?? 'no',
                    // 'previous_experience_name' => ($request->previous_experience[$index] === 'yes')
                    //     ? ($request->previous_experience_name[$index] ?? null)
                    //     : null,
                    // 'previous_experience_address' => ($request->previous_experience[$index] === 'yes')
                    //     ? ($request->previous_experience_address[$index] ?? null)
                    //     : null,
                    // 'previous_experience_lnumber' => ($request->previous_experience[$index] === 'yes')
                    //     ? ($request->previous_experience_lnumber[$index] ?? null)
                    //     : null,
                ]);
            }
            // var_dump($list);
            // die;
        }



        if (!$isDraft) {
            $transactionId = 'TXN' . rand(100000, 999999);

            Payment::create([
                'login_id' => $request->login_id_store,
                'application_id' => $newApplicationId,
                'transaction_id' => $transactionId,
                'payment_status' => 'success',
                'amount' => $request->amount,
                'form_name' => $form->form_name,
                'license_name' => $form->license_name,
            ]);

            mst_workflow::create([
                'login_id' => $request->login_id_store,
                'application_id' => $newApplicationId,
                'transaction_id' => $transactionId,
                'payment_status' => 'success',
                'formname_appliedfor' => $form->form_name,
                'license_name' => $form->license_name,
            ]);

            return response()->json([
                'message' => 'Payment Processed!',
                'login_id' => $newApplicationId,
                'transaction_id' => $transactionId,
            ]);
        }

        // ✅ Return Draft Response
        return response()->json([
            'message' => 'Payment Processed!',
            'login_id' => $newApplicationId,
            'transaction_id' => '11111',
        ]);
    }

    public function update(Request $request, $id)
    {

        // dd($id);
        // exit;

        $isDraft = $request->input('form_action') === 'draft';


        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);

        $rules = [
            'applicant_name'                => 'required|string|max:255',
            'business_address'              => 'required|string|max:500',
            'authorised_name_designation'   => 'required',
            'authorised_name'               => 'nullable|string|max:255',
            'authorised_designation'        => 'nullable|string|max:255',
            'previous_contractor_license'   => 'required|string|max:10',
            'previous_application_number'   => 'nullable|string|max:50',
            'bank_address'                  => 'required|string|max:500',
            'bank_validity'                 => 'required|date',
            'bank_amount'                   => 'required|numeric|min:1',
            'criminal_offence'              => ['required', 'string', Rule::in(['yes', 'no'])],
            'consent_letter_enclose'        => ['required', 'string', Rule::in(['yes', 'no'])],
            'cc_holders_enclosed'           => ['required', 'string', Rule::in(['yes', 'no'])],
            'purchase_bill_enclose'         => ['required', 'string', Rule::in(['yes', 'no'])],
            'test_reports_enclose'          => ['required', 'string', Rule::in(['yes', 'no'])],
            'specimen_signature_enclose'    => ['required', 'string', Rule::in(['yes', 'no'])],
            'separate_sheet'                => ['required', 'string', Rule::in(['yes', 'no'])],
            'form_name'                     => 'required|string|max:255',
            'license_name'                  => 'required|string|max:255',
        ];

        if ($isDraft) {
            foreach ($rules as $key => $rule) {
                $rules[$key] = str_replace('required', 'nullable', $rule);
            }
        }

        $validatedData = $request->validate($rules);

        DB::beginTransaction();

        try {

            // $ApplicationId = $request->application_id;
            // dd($id);
            $form = EA_Application_model::where('application_id', $id)->firstOrFail();

            $appl_type = $request->appl_type ?? '';


            // generate Application ID
            $lastApplication = EA_Application_model::latest('id')->value('application_id');
            $nextNumber = $lastApplication ? ((int) substr($lastApplication, -7)) + 1 : 1111111;
            $newApplicationId = $appl_type . $request->form_name . $request->license_name . date('y') . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

            // file uploads
            $aadhaarFilename = null;
            if ($request->hasFile('aadhaar_doc')) {
                $aadhaarFilename = 'documents/aadhaar_' . time() . '.' . $request->file('aadhaar_doc')->getClientOriginalExtension();
                $request->file('aadhaar_doc')->move(public_path('documents'), $aadhaarFilename);
            }

            $panFilename = null;
            if ($request->hasFile('pancard_doc')) {
                $panFilename = 'documents/pan_' . time() . '.' . $request->file('pancard_doc')->getClientOriginalExtension();
                $request->file('pancard_doc')->move(public_path('documents'), $panFilename);
            }

            $gst_Filename = null;
            if ($request->hasFile('gst_card_doc')) {
                $gst_Filename = 'documents/gst__' . time() . '.' . $request->file('gst_card_doc')->getClientOriginalExtension();
                $request->file('gst_doc')->move(public_path('documents'), $gst_Filename);
            }



            $document_ea = DB::table('tnelb_applicant_doc_A')->insert([
                'login_id'       => $request->login_id_store,
                'application_id' => $newApplicationId,
                'aadhaar_doc'    => $aadhaarFilename,
                'pancard_doc'    => $panFilename,
                'gst_doc'        => $gst_Filename,
                'created_at'     => now(),
                'updated_at'     => now()
            ]);

            // dd($document_ea);
            // exit;


            // Main form insert
            $form = EA_Application_model::create([
                'login_id'                     => $request->login_id_store,
                'application_id'              => $newApplicationId,
                'application_status'          => 'P',
                'license_number'              => '',
                'payment_status'              => 'paid',  //: 'paid',
                'name_of_authorised_to_sign'  => !empty($request->name_of_authorised_to_sign) ? json_encode($request->name_of_authorised_to_sign) : null,
                'enclosure'                   => '1',
                'license_number'              => $request->previous_application_number,
                'criminal_offence'            => $request->criminal_offence,
                'consent_letter_enclose'      => $request->consent_letter_enclose,
                'cc_holders_enclosed'         => $request->cc_holders_enclosed,
                'purchase_bill_enclose'       => $request->purchase_bill_enclose,
                'test_reports_enclose'        => $request->test_reports_enclose,
                'specimen_signature_enclose'  => $request->specimen_signature_enclose,
                'separate_sheet'              => $request->separate_sheet,
                'aadhaar'                     => $request->aadhaar,
                'pancard'                     => $request->pancard,
                'gst_number'                  => $request->gst_number,
                'appl_type'                   => $appl_type,
                'aadhaar_doc'                 => $aadhaarFilename,
                'pan_doc'                     => $panFilename,
                'gst_doc'                     => $gst_Filename,
                'old_application'             => $id,
            ] + $validatedData);


            // Staff details
            if ($request->has('staff_name')) {
                foreach ($request->staff_name as $index => $staffName) {
                    TnelbApplicantStaffDetail::create([
                        'login_id'          => $request->login_id_store,
                        'application_id'    => $newApplicationId,
                        'staff_name'        => $staffName,
                        'staff_qualification' => $request->staff_qualification[$index] ?? null,
                        'cc_number'         => $request->cc_number[$index] ?? null,
                        'cc_validity'       => $request->cc_validity[$index] ?? null,
                    ]);
                }
            }

            // Proprietors
            if ($request->has('proprietor_name')) {
                foreach ($request->proprietor_name as $index => $proprietor_name) {
                    $competencyHolding = $request->competency_certificate_holding[$index] ?? 'no';

                    ProprietorformA::create([
                        'login_id'                      => $request->login_id_store,
                        'application_id'                => $newApplicationId,
                        'proprietor_name'               => $proprietor_name,
                        'proprietor_address'            => $request->proprietor_address[$index] ?? null,
                        'age'                           => $request->age[$index] ?? null,
                        'qualification'                 => $request->qualification[$index] ?? null,
                        'fathers_name'                  => $request->fathers_name[$index] ?? 'Not Provided',
                        'present_business'              => $request->present_business[$index] ?? null,
                        'competency_certificate_holding' => $competencyHolding,
                        'competency_certificate_number' => $competencyHolding === 'yes' ? ($request->competency_certificate_number[$index] ?? null) : null,
                        'competency_certificate_validity' => $competencyHolding === 'yes' ? ($request->competency_certificate_validity[$index] ?? null) : null,
                        'presently_employed'            => $request->presently_employed[$index] ?? 'no',
                        'presently_employed_name'       => ($request->presently_employed[$index] === 'yes') ? ($request->presently_employed_name[$index] ?? null) : null,
                        'presently_employed_address'    => ($request->presently_employed[$index] === 'yes') ? ($request->presently_employed_address[$index] ?? null) : null,
                        'previous_experience'           => $request->previous_experience[$index] ?? 'no',
                        'previous_experience_name'      => ($request->previous_experience[$index] === 'yes') ? ($request->previous_experience_name[$index] ?? null) : null,
                        'previous_experience_address'   => ($request->previous_experience[$index] === 'yes') ? ($request->previous_experience_address[$index] ?? null) : null,
                        'previous_experience_lnumber'   => ($request->previous_experience[$index] === 'yes') ? ($request->previous_experience_lnumber[$index] ?? null) : null,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => '200',
                'message' => 'Form saved as draft',
                'application_id' => $newApplicationId,
                'applicantName' => $form->applicant_name
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Form store failed: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'message' => 'Something went wrong. Please try again later.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    // public function store(Request $request)
    // {
    //     $isDraft = $request->input('form_action') === 'draft';

    //     // ✅ Validation Rules
    //     $rules = [
    //         'applicant_name'                => 'required|string|max:255',
    //         'business_address'              => 'required|string|max:500',
    //         'authorised_name_designation'   => 'required',
    //         'authorised_name'               => 'nullable|string|max:255',
    //         'authorised_designation'        => 'nullable|string|max:255',
    //         'previous_contractor_license'   => 'required|string|max:10',
    //         'previous_application_number'   => 'nullable|string|max:50',
    //         'bank_address'                  => 'required|string|max:500',
    //         'bank_validity'                 => 'required|date',
    //         'bank_amount'                   => 'required|numeric|min:1',
    //         'criminal_offence'              => ['required', 'string', Rule::in(['yes', 'no'])],
    //         'consent_letter_enclose'        => ['required', 'string', Rule::in(['yes', 'no'])],
    //         'cc_holders_enclosed'           => ['required', 'string', Rule::in(['yes', 'no'])],
    //         'purchase_bill_enclose'         => ['required', 'string', Rule::in(['yes', 'no'])],
    //         'test_reports_enclose'          => ['required', 'string', Rule::in(['yes', 'no'])],
    //         'specimen_signature_enclose'    => ['required', 'string', Rule::in(['yes', 'no'])],
    //         'separate_sheet'                => ['required', 'string', Rule::in(['yes', 'no'])],
    //         'form_name'                     => 'required|string|max:255',
    //         'license_name'                  => 'required|string|max:255',
    //         // 'aadhaar'                       => 'required|digits:12',
    //         // 'pancard'                       => 'required|string|size:10',
    //         // 'declaration1'                  => 'required|string|max:255',
    //         // 'declaration2'                  => 'required|string|max:255',
    //     ];

    //     // Relax validation for Draft
    //     if ($isDraft) {
    //         foreach ($rules as $key => $rule) {
    //             $rules[$key] = str_replace('required', 'nullable', $rule);
    //         }
    //     }

    //     // Validate Data
    //     $validatedData = $request->validate($rules);

    //     // Generate Application ID
    //     $lastApplication    = EA_Application_model::latest('id')->value('application_id');
    //     $nextNumber         = $lastApplication ? ((int) substr($lastApplication, -7)) + 1 : 1111111;
    //     $newApplicationId   = $request->form_name . $request->license_name . date('y') . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

    //       // Initialize paths
    //       $aadhaarFilename = null;
    //       $panFilename = null;

    //       // Aadhaar doc
    //       if ($request->hasFile('aadhaar_doc')) {
    //           $aadhaarFilename = 'documents/'.'aadhaar_' . time() . '.' . $request->file('aadhaar_doc')->getClientOriginalExtension();
    //           $destinationPath = public_path('documents');
    //           $request->file('aadhaar_doc')->move($destinationPath, $aadhaarFilename);
    //       }

    //       // PAN doc
    //       if ($request->hasFile('pancard_doc')) {
    //           $panFilename = 'documents/'.'pan_' . time() . '.' . $request->file('pancard_doc')->getClientOriginalExtension();
    //           $destinationPath = public_path('documents');
    //           $request->file('pancard_doc')->move($destinationPath, $panFilename);
    //       }

    //     // Save Main Form Data
    //     $form = EA_Application_model::create([
    //         'login_id' => $request->login_id_store,
    //         'application_id' => $newApplicationId,
    //         'application_status' => 'P',
    //         'license_number' => '',
    //         'payment_status' => $isDraft ? 'draft' : 'paid',
    //         'name_of_authorised_to_sign' => !empty($request->name_of_authorised_to_sign)? json_encode($request->name_of_authorised_to_sign): null,
    //         'enclosure' => '1',
    //         'previous_contractor_license' => $request->previous_contractor_license,
    //         'criminal_offence' => $request->criminal_offence,
    //         'consent_letter_enclose' => $request->consent_letter_enclose,
    //         'cc_holders_enclosed' => $request->cc_holders_enclosed,
    //         'purchase_bill_enclose' => $request->purchase_bill_enclose,
    //         'test_reports_enclose' => $request->test_reports_enclose,
    //         'specimen_signature_enclose' => $request->specimen_signature_enclose,
    //         'separate_sheet' => $request->separate_sheet,
    //         'aadhaar_doc'         => $aadhaarFilename,
    //         'pan_doc'             => $panFilename,


    //     ] + $validatedData);

    //     if ($request->has('staff_name')) {
    //         foreach ($request->staff_name as $index => $staffName) {
    //             TnelbApplicantStaffDetail::create([
    //                 'login_id' => $request->login_id_store,
    //                 'application_id' => $newApplicationId,
    //                 'staff_name' => $staffName,
    //                 'staff_qualification' => $request->staff_qualification[$index] ?? null,
    //                 'cc_number' => $request->cc_number[$index] ?? null,
    //                 'cc_validity' => $request->cc_validity[$index] ?? null,
    //             ]);
    //         }
    //     }


    //     if ($request->has('proprietor_name')) {

    //         foreach ($request->proprietor_name as $index => $proprietor_name) {

    //             $competencyHolding = $request->competency_certificate_holding[$index] ?? 'no';
    //             $list =ProprietorformA::create([
    //                 'login_id' => $request->login_id_store,
    //                 'application_id' => $newApplicationId,
    //                 'proprietor_name' => $proprietor_name,
    //                 'proprietor_address' => $request->proprietor_address[$index] ?? null,
    //                 'age' => $request->age[$index] ?? null,
    //                 'qualification' => $request->qualification[$index] ?? null,
    //                 'fathers_name' => $request->fathers_name[$index] ?? 'Not Provided',
    //                 'present_business' => $request->present_business[$index] ?? null,

    //                 'competency_certificate_holding' => $competencyHolding,
    //                 'competency_certificate_number' => ($competencyHolding === 'yes')
    //                     ? ($request->competency_certificate_number[$index] ?? null)
    //                     : null,
    //                 'competency_certificate_validity' => ($competencyHolding === 'yes')
    //                     ? ($request->competency_certificate_validity[$index] ?? null)
    //                     : null,

    //                 'presently_employed' => $request->presently_employed[$index] ?? 'no',

    //                 'presently_employed_name' => ($request->presently_employed[$index] === 'yes')
    //                     ? ($request->presently_employed_name[$index] ?? null)
    //                     : null,
    //                 'presently_employed_address' => ($request->presently_employed[$index] === 'yes')
    //                     ? ($request->presently_employed_address[$index] ?? null)
    //                     : null,
    //                 'previous_experience' => $request->previous_experience[$index] ?? 'no',
    //                 'previous_experience_name' => ($request->previous_experience[$index] === 'yes')
    //                     ? ($request->previous_experience_name[$index] ?? null)
    //                     : null,
    //                 'previous_experience_address' => ($request->previous_experience[$index] === 'yes')
    //                     ? ($request->previous_experience_address[$index] ?? null)
    //                     : null,
    //                 'previous_experience_lnumber' => ($request->previous_experience[$index] === 'yes')
    //                     ? ($request->previous_experience_lnumber[$index] ?? null)
    //                     : null,
    //             ]);

    //         }
    //     }

    //     if ($isDraft) {

    //         return response()->json([
    //             'message' => 'Form saved as draft',
    //             'login_id' => $newApplicationId,
    //         ], 200);
    //     }


    // }


    public function updatePaymentStatus(Request $request)
    {
        // dd('111');
        // exit;

        $request->validate([
            'application_id' => 'required|string',
            'payment_status' => 'required|in:draft,pending,paid',
        ]);

        $application =
            DB::table('ccl_forma_meta')->where('application_id', $request->application_id)->first()
            ?? DB::table('tnelb_esa_applications')->where('application_id', $request->application_id)->first()
            ?? DB::table('tnelb_esb_applications')->where('application_id', $request->application_id)->first()
            ?? DB::table('tnelb_eb_applications')->where('application_id', $request->application_id)->first();



        $formname = $application->form_name;

        $payment = $request->payment_status;



        // ALTER TABLE tnelb_eb_applications ADD COLUMN dt_submit date NULL ;
        if ($formname == 'SA') {
            ESA_Application_model::where('application_id', $request->application_id)
                ->update(['payment_status' => $request->payment_status]);
        } elseif ($formname == 'SB') {
            //                 dd($payment);
            // exit;
            ESB_Application_model::where('application_id', $request->application_id)
                ->update(['payment_status' => $request->payment_status]);
        } elseif ($formname == 'B') {
            //                 dd($payment);
            // exit;
            B_Application::where('application_id', $request->application_id)
                ->update(['payment_status' => $request->payment_status]);
        } else {
            EA_Application_model::where('application_id', $request->application_id)
                ->update(['payment_status' => $request->payment_status]);
        }

        if ($payment === 'paid') {

            if ($formname == 'SA') {
                ESA_Application_model::where('application_id', $request->application_id)
                    ->update([
                        'dt_submit'  => DB::raw('NOW()'),
                        'updated_at' => DB::raw('NOW()'),
                    ]);
            } elseif ($formname == 'SB') {
                //                 dd($payment);
                // exit;
                ESB_Application_model::where('application_id', $request->application_id)
                    ->update([
                        'dt_submit'  => DB::raw('NOW()'),
                        'updated_at' => DB::raw('NOW()'),
                    ]);
            } elseif ($formname == 'B') {
                //                 dd($payment);
                // exit;
                B_Application::where('application_id', $request->application_id)
                    ->update([
                        'dt_submit'  => DB::raw('NOW()'),
                        'updated_at' => DB::raw('NOW()'),
                    ]);
            } else {
                EA_Application_model::where('application_id', $request->application_id)
                    ->update([
                        'dt_submit'  => DB::raw('NOW()'),
                        'updated_at' => DB::raw('NOW()'),
                    ]);
            }

            // $paid = EA_Application_model::where('application_id', $request->application_id)
            //         ->update(['dt_submit' => DB::raw('NOW()') ]);
            // dd($paid);
            // exit;
        }



        return response()->json(['status' => 'updated']);
    }


    public function expiry_date_change()
    {
        return view('user_login.license_datechange.index');
    }

    public function getLicenseNumbersByType(string $certificateType)
    {
        $table = $this->certTableForLicenseDateChange($certificateType);
        if (! $table) {
            return response()->json(['certificates' => []]);
        }

        $certificates = DB::table($table)
            ->select('certificate_no', DB::raw('MAX(valid_to) as valid_to'))
            ->whereNotNull('certificate_no')
            ->where('certificate_no', '!=', '')
            ->groupBy('certificate_no')
            ->orderBy('certificate_no')
            ->get()
            ->map(function ($row) {
                $row->valid_to = $this->formatLicenseValidTo($row->valid_to ?? null);

                return $row;
            });

        return response()->json(['certificates' => $certificates]);
    }

    public function getLicenseExpiry(string $certificateType, string $licenseNumber)
    {
        $table = $this->certTableForLicenseDateChange($certificateType);
        if (! $table) {
            return response()->json(['valid_to' => null]);
        }

        $license = DB::table($table)
            ->where('certificate_no', $licenseNumber)
            ->orderByDesc('created_at')
            ->select('valid_to')
            ->first();

        return response()->json([
            'valid_to' => $this->formatLicenseValidTo($license->valid_to ?? null),
        ]);
    }

    public function updateLicenseExpiry(Request $request)
    {
        $request->validate([
            'certificate_type' => 'required|string|in:S,W,WH,P,H',
            'license_number' => 'required|string',
            'expires_at' => 'required|date',
        ], [
            'certificate_type.required' => 'Please select a certificate type.',
            'certificate_type.in' => 'Please select a valid certificate type.',
            'license_number.required' => 'Please select a certificate number.',
            'expires_at.required' => 'Please choose a valid to date.',
            'expires_at.date' => 'Please enter a valid date.',
        ]);

        $table = $this->certTableForLicenseDateChange($request->certificate_type);
        if (! $table) {
            return response()->json(['status' => 'error', 'message' => 'Invalid certificate type'], 422);
        }

        $updated = DB::table($table)
            ->where('certificate_no', $request->license_number)
            ->update([
                'valid_to' => $request->expires_at,
                'updated_at' => now(),
            ]);

        if ($updated) {
            return response()->json(['status' => 'success', 'message' => 'Expiry date updated successfully']);
        }

        return response()->json(['status' => 'error', 'message' => 'License not found'], 404);
    }

    private function certTableForLicenseDateChange(?string $certificateType): ?string
    {
        return app(CompetencyCertificateService::class)->certTableForForm($certificateType);
    }

    private function formatLicenseValidTo(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }


    // -------------------------------storevaliditycheck_cl----------------------------
    public function storevaliditycheck_cl(Request $request)
    {

        // dd('1111');
        // exit;
        DB::table('tnelb_cl_validitychecks')->insert([
            'login_id'      => auth()->id(),
            'application_id' => $request->application_id,
            'form_name'     => $request->form_name,
            'license_name'  => $request->license_name,
            'check_value'   => $request->check_value,
            'ipaddress'     => $request->ip(),
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        return response()->json(['status' => 'success']);
    }


    // ---------------------------exp retrival -----------------------
    public function checkCompetencyCertificate(Request $request)
    {
        // dd($request->all());exit;
        $request->validate([
            'certificate_no' => 'required',
            'dateof_issue'   => 'required|date',
            'valid_from'     => 'required|date',
            'valid_to'       => 'required|date',
        ]);

        $certificate = DB::table('cc_forms_cert')
            ->where('certificate_no', $request->certificate_no)
            ->whereDate('dateof_issue', $request->dateof_issue)
            ->whereDate('valid_from', $request->valid_from)
            ->whereDate('valid_to', $request->valid_to)
            ->first();

        // dd($certificate);exit;

        if (!$certificate) {

            return response()->json([
                'status' => false,
                'message' => 'Certificate details not found - Enter valid details.'
            ]);
        }



        $application_id = $certificate->application_id;



        $cc_exp = DB::table('cc_exp')
            ->where('application_id', $application_id)
            ->orderBy('exp_id', 'DESC')
            ->first();

        // dd($cc_exp);exit;

        if (!$cc_exp) {

            return response()->json([
                'status' => true,
                'message' => 'Certificate found, but no experience data found.',
                'application_id' => $application_id,
                'data' => null
            ]);
        }

        // Step 4:
        // Return latest cc_exp row

        return response()->json([
            'status' => true,
            'message' => 'Certificate and experience data found.',
            'application_id' => $application_id,
            'data' => $cc_exp
        ]);
    }
}
