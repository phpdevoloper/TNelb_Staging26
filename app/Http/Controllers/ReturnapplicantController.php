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
            $application = DB::table('tnelb_ea_applications')->where('application_id', $application_id)->first();
            $proprietors = DB::table('proprietordetailsform_A')
                ->where('application_id', $application_id)
                ->where('proprietor_flag', '1')
                ->orderBy('id')->get();
            $draftCount = $proprietors->count();

            $staffs = DB::table('tnelb_applicant_cl_staffdetails')->where('application_id', $application_id)->orderBy('id', 'ASC')->get();
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

            // var_dump()

            $returnsection = json_decode($application->return_reason, true);
            // $returnsection = json_decode($application->return_reason, true);

        }

        return view('user_login.return.forma', compact('application', 'proprietors', 'draftCount', 'staffs', 'document', 'banksolvency', 'equipmentlist', 'equiplist', 'form_code', 'attachment_doc', 'Address_proof', 'equipmentDetails', 'returnsection'));
    }

    // ---------------------form ea   -----------------------------------------------------------------
    public function storereturn(Request $request)
    {

        // dd($request->type_doc);
        // exit;

        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);

        // dd($request->input('form_action'));exit;
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
                'authorised_name_designation' => 'required',
                'authorised_name' => 'nullable|string|max:255',
                'authorised_designation' => 'nullable|string|max:255',
                'previous_contractor_license' => 'required|string|max:10',
                'previous_application_number' => 'nullable|string|max:50',
                'previous_application_validity' => 'nullable',
                'previous_contractor_license_verify' => 'nullable|numeric',

                'bank_address' => 'required|string|max:500',
                'bank_validity' => 'required|date',
                'bank_amount' => 'required|numeric|min:0',

                'criminal_offence' => ['required', 'string', Rule::in(['yes', 'no'])],
                'consent_letter_enclose' => ['required', 'string', Rule::in(['yes', 'no'])],
                'cc_holders_enclosed' => ['required', 'string', Rule::in(['yes', 'no'])],
                'purchase_bill_enclose' => ['required', 'string', Rule::in(['yes', 'no'])],
                'test_reports_enclose' => ['required', 'string', Rule::in(['yes', 'no'])],
                'specimen_signature_enclose' => ['required', 'string', Rule::in(['yes', 'no'])],
                'separate_sheet' => ['required', 'string', Rule::in(['yes', 'no'])],
                'form_name' => 'required|string|max:255',
                'license_name' => 'required|string|max:255',

                'declaration1' => 'required|string|max:255',
                'declaration2' => 'required|string|max:255',
                'address_proof' => $request->hasFile('address_proof') ? 'required|file|max:2048' : 'nullable|string',



            ];
        }
        // dd($request->all());
        // exit;
        $validatedData = $request->validate($rules);

        $validatedData['name_of_authorised_to_sign'] = !empty($request->name_of_authorised_to_sign)
            ? json_encode($request->name_of_authorised_to_sign)
            : null;

        $validatedData['age_of_authorised_to_sign'] = !empty($request->age_of_authorised_to_sign)
            ? json_encode($request->age_of_authorised_to_sign)
            : null;

        $validatedData['qualification_of_authorised_to_sign'] = !empty($request->qualification_of_authorised_to_sign)
            ? json_encode($request->qualification_of_authorised_to_sign)
            : null;

        // Convert to uppercase for certain fields
        foreach (
            [
                'applicant_name',
                'business_address',
                'authorised_name',
                'authorised_designation',
                'bank_address',
                'form_name',
                'license_name',

            ] as $field
        ) {
            if (!empty($validatedData[$field])) {
                $validatedData[$field] = strtoupper($validatedData[$field]);
            }
        }




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
                $request->appl_type !== 'N',
                $request->form_name,
                $request->license_name
            );
        }

        // Final data to save
        $dataToSave = $validatedData;
        $dataToSave['application_id'] = $applicationId;
        $dataToSave['login_id'] = $request->login_id_store;

        $dataToSave['application_status'] = 'P';
        // $dataToSave['created_at'] = now();
        $dataToSave['updated_at'] = DB::raw('NOW()');


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







        if ($request->has('staff_name')) {
            $processedStaffIds = [];
            if ($request->appl_type === 'N') {
                $staffIdsFromForm = $request->staff_id ?? [];
                $existingStaffIds = TnelbApplicantStaffDetail::where('application_id', $applicationId)->pluck('id')->toArray();

                // $processedStaffIds = [];

                foreach ($request->staff_name as $index => $staffName) {
                    if (
                        !empty($staffName) ||
                        // !empty($request->staff_qualification[$index]) ||
                        !empty($request->cc_number[$index]) ||
                        !empty($request->cc_validity[$index]) ||
                        !empty($request->staff_category[$index])
                    ) {
                        $staffId = $staffIdsFromForm[$index] ?? null;
                        $validity = $request->cc_validity[$index] ?? null;

                        $staffData = [
                            'application_id'      => $applicationId,
                            'login_id'            => $request->login_id_store,
                            'staff_name'          => strtoupper($staffName),
                            'staff_qualification' => strtoupper($request->staff_qualification[$index] ?? ''),
                            'cc_number'           => strtoupper($request->cc_number[$index] ?? ''),
                            'cc_validity'         => $validity,
                            'staff_category'      => strtoupper($request->staff_category[$index] ?? ''),
                            'staff_cc_verify'     => $request->staff_cc_verify[$index]
                        ];

                        if ($staffId && in_array($staffId, $existingStaffIds)) {
                            $existingStaff = TnelbApplicantStaffDetail::find($staffId);

                            if (
                                strtoupper($existingStaff->staff_name) !== strtoupper($staffName) ||
                                strtoupper($existingStaff->staff_qualification) !== strtoupper($request->staff_qualification[$index] ?? '') ||
                                strtoupper($existingStaff->cc_number) !== strtoupper($request->cc_number[$index] ?? '') ||
                                $existingStaff->cc_validity !== $validity ||
                                strtoupper($existingStaff->staff_category) !== strtoupper($request->staff_category[$index] ?? '')
                            ) {
                                $existingStaff->update($staffData);
                            }

                            $processedStaffIds[] = $staffId;
                        } else {
                            // Create new entry
                            $newStaff = TnelbApplicantStaffDetail::create($staffData);
                            $processedStaffIds[] = $newStaff->id;
                        }
                    }
                }
            } elseif ($request->appl_type === 'R') {
                foreach ($request->staff_name as $index => $staffName) {
                    if (!empty($staffName) || !empty($request->cc_number[$index]) || !empty($request->cc_validity[$index]) || !empty($request->staff_category[$index])) {

                        $validity = $request->cc_validity[$index] ?? null;

                        $staffData = [
                            'application_id'      => $applicationId,
                            'login_id'            => $request->login_id_store,
                            'staff_name'          => strtoupper($staffName),
                            'staff_qualification' => strtoupper($request->staff_qualification[$index] ?? ''),
                            'cc_number'           => strtoupper($request->cc_number[$index] ?? ''),
                            'cc_validity'         => $validity,
                            'staff_category'      => strtoupper($request->staff_category[$index] ?? ''),
                            'staff_cc_verify'     => $request->staff_cc_verify[$index] ?? null
                        ];

                        TnelbApplicantStaffDetail::create($staffData);
                    }
                }
            }


            // Remove deleted staff
            TnelbApplicantStaffDetail::where('application_id', $applicationId)
                ->whereNotIn('id', $processedStaffIds)
                ->delete();
        }

        // Update only staff_cc_verify values by staff_id (if they exist)
        if ($request->has('staff_cc_verify') && $request->has('staff_id')) {
            foreach ($request->staff_cc_verify as $index => $verifyValue) {
                $staffId = $request->staff_id[$index] ?? null;

                if ($staffId) {
                    TnelbApplicantStaffDetail::where('id', $staffId)->update([
                        'staff_cc_verify' => $verifyValue
                    ]);
                }
            }
        }

        //    dd($request->all());
        // exit;

        $newProprietorIds = [];
        if ($request->has('proprietor_name')) {

            // dd($request->all());
            // exit;
            $count = 1;
            foreach ($request->proprietor_name as $index => $name) {

                // dd($count);exit;
                if (empty(trim($name))) continue;

                $competencyHolding = data_get($request->competency, $index);

                //                     // dd($competencyHolding);
                //                     // exit;
                $presently_employed = data_get($request->employed, $index);
                $previous_experience = data_get($request->experience, $index);
                //                     // Skip if no name (avoid empty row)
                //                     if (empty(trim($proprietor_name))) {
                //                         continue;
                //                     }


                $proprietorId = $request->proprietor_id[$index] ?? null;
                $data = [
                    'login_id' => $request->login_id_store,
                    'application_id' => $applicationId,
                    'proprietor_name' => strtoupper($name ?? ''),
                    'ownership_type' => $request->ownership_type[$index],
                    'proprietor_address' => strtoupper(data_get($request->proprietor_address, $index, '')),
                    'dob' => $request->dob[$index],
                    'age' => data_get($request->age, $index),
                    'qualification' => strtoupper(data_get($request->qualification, $index, '')),
                    'qualification_text' => strtoupper(data_get($request->qual_text, $index, '')),
                    'fathers_name' => strtoupper(data_get($request->fathers_name, $index, '')),
                    'present_business' => strtoupper(data_get($request->present_business, $index, '')),
                    'competency_certificate_holding' => $competencyHolding,
                    'competency_certificate_number' => $competencyHolding === 'yes' ? strtoupper(data_get($request->competency_certno, $index)) : null,
                    'competency_certificate_validity' => $competencyHolding === 'yes' ? data_get($request->competency_validity, $index) : null,
                    'proprietor_cc_verify' => $competencyHolding === 'yes' ? data_get($request->ccverify, $index) : null,


                    'presently_employed' => $presently_employed,

                    'presently_employed_name' => $presently_employed === 'yes' ? strtoupper(data_get($request->employer_name, $index)) : null,

                    'presently_employed_address' => $presently_employed === 'yes' ? strtoupper(data_get($request->employer_address, $index)) : null,

                    // 'presently_employed_name' => data_get($presently_employed, $index) === 'yes' ? strtoupper(data_get($request->employer_name, $index)) : null,
                    // 'presently_employed_address' => data_get($presently_employed, $index) === 'yes' ? strtoupper(data_get($request->employer_address, $index)) : null,

                    'previous_experience' => $previous_experience,

                    'previous_experience_name' => $previous_experience === 'yes' ? strtoupper(data_get($request->exp_name, $index)) : null,

                    'previous_experience_address' => $previous_experience === 'yes' ? strtoupper(data_get($request->exp_address, $index)) : null,


                    'previous_experience_lnumber' => $previous_experience === 'yes' ? strtoupper(data_get($request->exp_license, $index)) : null,

                    'previous_experience_lnumber_validity' => $previous_experience === 'yes' ? strtoupper(data_get($request->exp_validity, $index)) : null,



                    // 'previous_experience_name' => data_get($request->previous_experience, $index) === 'yes' ? strtoupper(data_get($request->previous_experience_name, $index)) : null,
                    // 'previous_experience_address' => data_get($request->previous_experience, $index) === 'yes' ? strtoupper(data_get($request->previous_experience_address, $index)) : null,
                    // 'previous_experience_lnumber' => data_get($request->previous_experience, $index) === 'yes' ? strtoupper(data_get($request->previous_experience_lnumber, $index)) : null,

                    // 'previous_experience_lnumber_validity' => data_get($request->previous_experience, $index) === 'yes' ? data_get($request->previous_experience_lnumber_validity, $index) : null,

                    'proprietor_contractor_verify' => $previous_experience === 'yes' ? data_get($request->expverify, $index) : null,
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
                ->update(['proprietor_flag' => 0]);

            // table move edu_file----------

            // $recordexists = DB::table('proprietordetailsform_A')
            //             ->where('application_id', $applicationId)
            //             ->get();
            // if ($recordexists) {

            //              DB::table('$proprietordetailsform_A')
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

                ->get();

            // dd($tempDocs);
            // exit;


            // dd($tempDocs->pluck('document_category'));
            // exit;



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

                // -----------------------------------------
                // 5️⃣ COPY FILE
                // -----------------------------------------
                if (File::exists($tempFullPath)) {
                    File::copy($tempFullPath, $proFullPath);
                } else {
                    continue;
                }

                // -----------------------------------------
                // 6️⃣ SAVE FILE NAME INTO MATCHED PARTNER
                // -----------------------------------------
                  $matchedPartner->educational_proof = $dbFilePath_all->filepath_pro . $tempDoc->file_name;
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
                        'updated_at' => now()
                    ]);
            }
        }

        // Partners
        $newPartnerIds = [];
        if ($request->has('partner_name')) {
            $count = 1;
            foreach ($request->partner_name as $index => $name) {
                if (empty(trim($name))) continue;

                $partnerId = $request->partner_id[$index] ?? null;
                if (empty(trim($name))) continue;

                $competencyHolding = data_get($request->partner_competency, $index);

                //                     // dd($competencyHolding);
                //                     // exit;
                $presently_employed = data_get($request->partner_employed, $index);
                $previous_experience = data_get($request->partner_experience, $index);
                //                     // Skip if no name (avoid empty row)
                //                     if (empty(trim($proprietor_name))) {
                //                         continue;
                //                     }


                // $proprietorId = $request->proprietor_id[$index] ?? null;
                $data = [
                    'login_id' => $request->login_id_store,
                    'application_id' => $applicationId,
                    'proprietor_name' => strtoupper($name ?? ''),
                    'ownership_type' => 'pt',
                    'proprietor_address' => strtoupper(data_get($request->partner_proprietor_address, $index, '')),

                    'dob' => $request->partner_dob[$index],
                    'age' => data_get($request->partner_age, $index),
                    'qualification' => strtoupper(data_get($request->partner_qualification, $index, '')),
                    'qualification_text' => strtoupper(data_get($request->partner_qual_text, $index, '')),

                    'fathers_name' => strtoupper(data_get($request->partner_fathers_name, $index, '')),
                    'present_business' => strtoupper(data_get($request->partner_present_business, $index, '')),
                    'competency_certificate_holding' => $competencyHolding,
                    'competency_certificate_number' => $competencyHolding === 'yes' ? strtoupper(data_get($request->partner_competency_certno, $index)) : null,
                    'competency_certificate_validity' => $competencyHolding === 'yes' ? data_get($request->partner_competency_validity, $index) : null,
                    'proprietor_cc_verify' => $competencyHolding === 'yes' ? data_get($request->partner_ccverify, $index) : null,


                    'presently_employed' => $presently_employed,

                    'presently_employed_name' => $presently_employed === 'yes' ? strtoupper(data_get($request->partner_employer_name, $index)) : null,

                    'presently_employed_address' => $presently_employed === 'yes' ? strtoupper(data_get($request->partner_employer_address, $index)) : null,

                    // 'presently_employed_name' => data_get($presently_employed, $index) === 'yes' ? strtoupper(data_get($request->employer_name, $index)) : null,
                    // 'presently_employed_address' => data_get($presently_employed, $index) === 'yes' ? strtoupper(data_get($request->employer_address, $index)) : null,

                    'previous_experience' => $previous_experience,

                    'previous_experience_name' => $previous_experience === 'yes' ? strtoupper(data_get($request->partner_exp_name, $index)) : null,

                    'previous_experience_address' => $previous_experience === 'yes' ? strtoupper(data_get($request->partner_exp_address, $index)) : null,


                    'previous_experience_lnumber' => $previous_experience === 'yes' ? strtoupper(data_get($request->partner_exp_license, $index)) : null,

                    'previous_experience_lnumber_validity' => $previous_experience === 'yes' ? strtoupper(data_get($request->partner_exp_validity, $index)) : null,



                    // 'previous_experience_name' => data_get($request->previous_experience, $index) === 'yes' ? strtoupper(data_get($request->previous_experience_name, $index)) : null,
                    // 'previous_experience_address' => data_get($request->previous_experience, $index) === 'yes' ? strtoupper(data_get($request->previous_experience_address, $index)) : null,
                    // 'previous_experience_lnumber' => data_get($request->previous_experience, $index) === 'yes' ? strtoupper(data_get($request->previous_experience_lnumber, $index)) : null,

                    // 'previous_experience_lnumber_validity' => data_get($request->previous_experience, $index) === 'yes' ? data_get($request->previous_experience_lnumber_validity, $index) : null,

                    'proprietor_contractor_verify' => $previous_experience === 'yes' ? data_get($request->partner_expverify, $index) : null,
                    'proprietor_flag' => 1,
                    'ownership_count' => $count,
                ];

                if ($partnerId) {
                    ProprietorformA::where('id', $partnerId)->update($data);
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
                ->where('ownership_type', 'partner') // optional if you differentiate ownership
                ->update(['proprietor_flag' => 0]);

            $tempDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'educ_qual_proof')
                ->where('ownership_type', 'pt')

                ->get();

            // dd($tempDocs);
            // exit;


            // dd($tempDocs->pluck('document_category'));
            // exit;



            foreach ($tempDocs as $tempDoc) {


                $matchedPartner = ProprietorformA::where('application_id', $applicationId)
                    ->where('ownership_type', 'pt')
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

                // -----------------------------------------
                // 5️⃣ COPY FILE
                // -----------------------------------------
                if (File::exists($tempFullPath)) {
                    File::copy($tempFullPath, $proFullPath);
                } else {
                    continue;
                }

                // -----------------------------------------
                // 6️⃣ SAVE FILE NAME INTO MATCHED PARTNER
                // -----------------------------------------
                  $matchedPartner->educational_proof = $dbFilePath_all->filepath_pro . $tempDoc->file_name;
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
                        'updated_at' => now()
                    ]);
            }
        }

        // ----------------director------------------------

        $newdirectorIds = [];
        $count = 1;
        if ($request->has('director_name')) {

            // dd($request->ownership_type);
            // dd($request->all());
            // exit;
            foreach ($request->director_name as $index => $name) {


                if (empty(trim($name))) continue;

                $directorId = $request->director_id[$index] ?? null;
                if (empty(trim($name))) continue;

                $competencyHolding = data_get($request->director_competency, $index);

                //                     // dd($competencyHolding);
                //                     // exit;
                $presently_employed = data_get($request->director_employed, $index);
                $previous_experience = data_get($request->director_experience, $index);
                //                     // Skip if no name (avoid empty row)
                //                     if (empty(trim($proprietor_name))) {
                //                         continue;
                //                     }


                // $proprietorId = $request->proprietor_id[$index] ?? null;
                $data = [
                    'login_id' => $request->login_id_store,
                    'application_id' => $applicationId,
                    'proprietor_name' => strtoupper($name ?? ''),
                    // 'ownership_type' => $request->director_ownership_type[$index],

                    'ownership_type' => 'dr',

                    'proprietor_address' => strtoupper(data_get($request->director_proprietor_address, $index, '')),

                    'dob' => $request->director_dob[$index],
                    'age' => data_get($request->director_age, $index),
                    'qualification' => strtoupper(data_get($request->director_qualification, $index, '')),
                    'qualification_text' => strtoupper(data_get($request->director_qual_text, $index, '')),

                    'fathers_name' => strtoupper(data_get($request->director_fathers_name, $index, '')),
                    'present_business' => strtoupper(data_get($request->director_present_business, $index, '')),
                    'competency_certificate_holding' => $competencyHolding,
                    'competency_certificate_number' => $competencyHolding === 'yes' ? strtoupper(data_get($request->director_competency_certno, $index)) : null,
                    'competency_certificate_validity' => $competencyHolding === 'yes' ? data_get($request->director_competency_validity, $index) : null,
                    'proprietor_cc_verify' => $competencyHolding === 'yes' ? data_get($request->director_ccverify, $index) : null,


                    'presently_employed' => $presently_employed,

                    'presently_employed_name' => $presently_employed === 'yes' ? strtoupper(data_get($request->director_employer_name, $index)) : null,

                    'presently_employed_address' => $presently_employed === 'yes' ? strtoupper(data_get($request->director_employer_address, $index)) : null,

                    // 'presently_employed_name' => data_get($presently_employed, $index) === 'yes' ? strtoupper(data_get($request->employer_name, $index)) : null,
                    // 'presently_employed_address' => data_get($presently_employed, $index) === 'yes' ? strtoupper(data_get($request->employer_address, $index)) : null,

                    'previous_experience' => $previous_experience,

                    'previous_experience_name' => $previous_experience === 'yes' ? strtoupper(data_get($request->director_exp_name, $index)) : null,

                    'previous_experience_address' => $previous_experience === 'yes' ? strtoupper(data_get($request->director_exp_address, $index)) : null,


                    'previous_experience_lnumber' => $previous_experience === 'yes' ? strtoupper(data_get($request->director_exp_license, $index)) : null,

                    'previous_experience_lnumber_validity' => $previous_experience === 'yes' ? strtoupper(data_get($request->director_exp_validity, $index)) : null,



                    // 'previous_experience_name' => data_get($request->previous_experience, $index) === 'yes' ? strtoupper(data_get($request->previous_experience_name, $index)) : null,
                    // 'previous_experience_address' => data_get($request->previous_experience, $index) === 'yes' ? strtoupper(data_get($request->previous_experience_address, $index)) : null,
                    // 'previous_experience_lnumber' => data_get($request->previous_experience, $index) === 'yes' ? strtoupper(data_get($request->previous_experience_lnumber, $index)) : null,

                    // 'previous_experience_lnumber_validity' => data_get($request->previous_experience, $index) === 'yes' ? data_get($request->previous_experience_lnumber_validity, $index) : null,

                    'proprietor_contractor_verify' => $previous_experience === 'yes' ? data_get($request->director_expverify, $index) : null,
                    'proprietor_flag' => 1,
                    'ownership_count' => $count,
                ];

                if ($directorId) {
                    ProprietorformA::where('id', $directorId)->update($data);
                    $newdirectorIds[] = $directorId;
                } else {
                    $new = ProprietorformA::create($data);
                    $newdirectorIds[] = $new->id;
                }
                $count++;
            }

            // Deactivate removed partner rows
            ProprietorformA::where('application_id', $applicationId)
                ->whereNotIn('id', $newdirectorIds)
                ->where('ownership_type', 'partner') // optional if you differentiate ownership
                ->update(['proprietor_flag' => 0]);


            $tempDocs = DB::table('tnelb_temp_uploaded_documents')
                ->where('login_id', $request->login_id_store)
                ->where('form_name', $request->form_name)
                ->where('license_name', $request->license_name)
                ->where('document_category', 'educ_qual_proof')
                ->where('ownership_type', 'dr')

                ->get();



            foreach ($tempDocs as $tempDoc) {


                $matchedPartner = ProprietorformA::where('application_id', $applicationId)
                    ->where('ownership_type', 'dr')
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

                // -----------------------------------------
                // 5️⃣ COPY FILE
                // -----------------------------------------
                if (File::exists($tempFullPath)) {
                    File::copy($tempFullPath, $proFullPath);
                } else {
                    continue;
                }

                // -----------------------------------------
                // 6️⃣ SAVE FILE NAME INTO MATCHED PARTNER
                // -----------------------------------------
                  $matchedPartner->educational_proof = $dbFilePath_all->filepath_pro . $tempDoc->file_name;
                $matchedPartner->row_index = $tempDoc->row_index;

                // dd( $matchedPartner->row_index );exit;
                $matchedPartner->save();

                // -----------------------------------------
                // 7️⃣ MARK TEMP DOC AS FINAL
                // -----------------------------------------
                DB::table('tnelb_temp_uploaded_documents')
                    ->where('id', $tempDoc->id)
                    ->update([
                        'is_final'   => '1',
                        'moved_as'   => $request->input('form_action'),
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

                DB::table('tnelb_ea_applications')
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
                            'login_id'            => $request->login_id_store ,
                            
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
                            'login_id'            => $request->login_id_store ,
                            
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
            //         DB::table('tnelb_ea_applications')
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

            DB::table('tnelb_ea_applications')
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
                'transaction_id' => $transactionId,
            ]);
        }

        //  DB::table('tnelb_ea_applications')
        //         ->where('application_id', $applicationId)
        //          ->update([
        //                     'payment_status'   => $request->input('form_action'),

        //                 ]);

        // dd('1111');exit;
        DB::table('tnelb_ea_applications')
            ->where('application_id', $applicationId)
            ->update([
                'application_status'   => 'RETD',

            ]);
        return response()->json([
            'message' => 'Draft',
            'actionType' => $request->input('form_action'),
            'login_id' => $applicationId,
            'transaction_id' => $transactionId,
            'draft_status' => $isDraft
        ]);
    }
}
