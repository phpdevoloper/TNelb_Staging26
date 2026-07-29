<?php

namespace App\Http\Controllers;


use App\Models\Admin\Mst_equipment_tbl;
use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Forms_cert;
use App\Models\CC_Forms_Meta;
use App\Models\CC_Proof_doc;
use App\Models\EA_Application_model;
use App\Models\Equipment_storetmp_A;
use App\Models\mst_workflow;
use App\Models\MstLicence;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class EA_RenewalController extends BaseController
{


// ------------form A draft and renew-------------
      public function renew_form($appl_id)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        if (!$appl_id) {
            return redirect()->route('dashboard')->with('error', 'Application ID is required.');
        }

        $application = CC_Forms_Meta::findByApplicationId((string) $appl_id);
        if (!$application) {
            return redirect()->route('dashboard')->with('error', 'Application not found.');
        }

        $loginId = Auth::user()->login_id ?? session('login_id');
        if ($loginId && (string) ($application->login_id ?? '') !== (string) $loginId) {
            return redirect()->route('dashboard')->with('error', 'You can only renew your own application.');
        }

        $master = app(\App\Services\FormS\FormSApplicationWorkflowService::class)->masterApplication($application);
        $masterApplicationId = (string) $master->application_id;
        $formName = (string) ($application->form_name ?? 'S');

        $application_details = (object) $application->toArray();
        $application_details->license_name = $application_details->license_name
            ?? $application_details->certificate_name
            ?? null;
        $application_details->applicants_address = $application_details->applicants_address
            ?? $application_details->applicant_address
            ?? null;
        $application_details->previously_number = $application_details->previously_number
            ?? $application_details->previous_scc_no
            ?? null;
        $application_details->previously_issue_date = $application_details->previously_issue_date
            ?? $application_details->first_issue_date
            ?? null;
        $application_details->previously_valid_from = $application_details->previously_valid_from
            ?? $application_details->scc_from_date
            ?? null;
        $application_details->previously_valid_to = $application_details->previously_valid_to
            ?? $application_details->scc_to_date
            ?? null;
        $application_details->previously_date = $application_details->previously_date
            ?? $application_details->previously_valid_to
            ?? $application_details->scc_to_date
            ?? null;
        $application_details->competency_certificate_no = $application_details->competency_certificate_no
            ?? $application_details->wcc_no
            ?? null;
        $application_details->certificate_no = $application_details->certificate_no
            ?? $application_details->wcc_no
            ?? null;
        $application_details->certificate_issue_date = $application_details->certificate_issue_date
            ?? $application_details->wcc_issue_date
            ?? null;
        $application_details->certificate_valid_from = $application_details->certificate_valid_from
            ?? $application_details->wcc_from
            ?? null;
        $application_details->certificate_valid_to = $application_details->certificate_valid_to
            ?? $application_details->wcc_to
            ?? null;
        $application_details->certificate_date = $application_details->certificate_date
            ?? $application_details->certificate_valid_to
            ?? $application_details->wcc_to
            ?? null;
        $application_details->id = $application_details->id ?? $application_details->app_id ?? null;
        $application_details->license_verify = isset($application_details->license_verify)
            ? (int) $application_details->license_verify
            : (! empty($application_details->previously_number) ? 1 : 0);
        $application_details->cert_verify = isset($application_details->cert_verify)
            ? (int) $application_details->cert_verify
            : (! empty($application_details->competency_certificate_no) ? 1 : 0);

        $proofService = app(\App\Services\FormS\FormSProofDocumentService::class);
        $proofCrypt = app(\App\Services\FormS\SensitiveProofCryptService::class);
        foreach (CC_Proof_doc::where('application_id', $masterApplicationId)
            ->whereIn('proof_name', [
                \App\Services\FormS\FormSProofDocumentService::PROOF_AADHAAR,
                \App\Services\FormS\FormSProofDocumentService::PROOF_PAN,
            ])
            ->get() as $proof) {
            $proofName = strtoupper(trim((string) ($proof->proof_name ?? '')));
            $proofNo = $proofCrypt->decryptProofNumber($proof->proof_no ?? null);
            if ($proofName === \App\Services\FormS\FormSProofDocumentService::PROOF_AADHAAR) {
                if ($proofNo !== null && $proofNo !== '') {
                    $application_details->aadhaar = $proofNo;
                }
                if (!empty($proof->proof_doc)) {
                    $application_details->aadhaar_doc = $proof->proof_doc;
                }
            } elseif ($proofName === \App\Services\FormS\FormSProofDocumentService::PROOF_PAN) {
                if ($proofNo !== null && $proofNo !== '') {
                    $application_details->pancard = $proofNo;
                }
                if (!empty($proof->proof_doc)) {
                    $application_details->pan_doc = $proof->proof_doc;
                    $application_details->pancard_doc = $proof->proof_doc;
                }
            }
        }

        $licence_name = DB::table('mst_licences')
            ->where(function ($q) use ($formName, $application_details) {
                $q->where('form_code', $formName);
                if (! empty($application_details->certificate_name)) {
                    $q->orWhere('cert_licence_code', $application_details->certificate_name);
                }
            })
            ->first();

        $edu_details = CC_Education::where('application_id', $masterApplicationId)
            ->orderByDesc('year_of_passing')
            ->get()
            ->map(function (CC_Education $edu) {
                $row = (object) $edu->toArray();
                $row->id = $edu->edu_id;
                $row->month_of_passing = $row->month_of_passing ?? $row->month_passing ?? null;

                return $row;
            });

        $exp_details = CC_Experience::where('application_id', $masterApplicationId)
            ->orderBy('exp_id')
            ->get()
            ->map(function (CC_Experience $exp) {
                $row = (object) $exp->toArray();
                $row->id = $exp->exp_id;
                $row->exp_id = $exp->exp_id;
                $row->support_document = $exp->support_document ?? $exp->upload_document ?? null;
                $row->upload_document = $row->support_document;
                $row->releive_document = $exp->relieve_document ?? $exp->releive_document ?? null;
                $row->relieve_document = $row->releive_document;

                return $row;
            });

        $apps_doc = CC_Proof_doc::where('application_id', $masterApplicationId)->get();

        $certService = app(\App\Services\Competency\CompetencyCertificateService::class);
        $license_details = $certService->asLicenseDetails($masterApplicationId, $formName)
            ?? $certService->asLicenseDetails((string) $application->application_id, $formName);

        $issuedForRenew = $license_details
            ? trim((string) ($license_details->license_number ?? $license_details->certificate_no ?? ''))
            : '';
        if ($issuedForRenew === '') {
            $issuedForRenew = trim((string) ($application_details->certificate_no ?? $application_details->wcc_no ?? ''));
        }
        if ($issuedForRenew !== '') {
            if (!$license_details) {
                $license_details = (object) [
                    'license_number' => $issuedForRenew,
                    'certificate_no' => $issuedForRenew,
                ];
            } else {
                if (trim((string) ($license_details->license_number ?? '')) === '') {
                    $license_details->license_number = $issuedForRenew;
                }
                if (trim((string) ($license_details->certificate_no ?? '')) === '') {
                    $license_details->certificate_no = $issuedForRenew;
                }
            }
            if (trim((string) ($application_details->previously_number ?? '')) === '') {
                $application_details->previously_number = $issuedForRenew;
                $application_details->previous_scc_no = $issuedForRenew;
                $application_details->license_verify = 1;
            }
            if (empty($application_details->previously_issue_date) && ! empty($license_details->issued_at ?? $license_details->valid_from ?? null)) {
                $application_details->previously_issue_date = $license_details->issued_at ?? $license_details->valid_from;
            }
            if (empty($application_details->previously_valid_from) && ! empty($license_details->valid_from)) {
                $application_details->previously_valid_from = $license_details->valid_from;
            }
            if (empty($application_details->previously_valid_to) && ! empty($license_details->valid_to ?? $license_details->expires_at ?? null)) {
                $application_details->previously_valid_to = $license_details->valid_to ?? $license_details->expires_at;
                $application_details->previously_date = $application_details->previously_valid_to;
            }
        }

        $applicant_photo = $proofService->loadPhotoForView($masterApplicationId)
            ?? (object) ['upload_path' => ''];
        $proof_doc = $proofService->loadSignForView($masterApplicationId)
            ?? (object) ['uploaded_doc' => ''];
        $uploadedPhotos = ! empty($applicant_photo->upload_path)
            ? collect([$applicant_photo->upload_path])
            : '';

        $applicationid = $appl_id;

        return view('user_login.renew-form', compact(
            'applicationid',
            'application_details',
            'edu_details',
            'exp_details',
            'apps_doc',
            'license_details',
            'uploadedPhotos',
            'applicant_photo',
            'proof_doc',
            'licence_name'
        ));
    }


     public function renew_form_ea($application_id){

   

        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        if (!$application_id) {
            return redirect()->route('dashboard')->with('error', 'Application ID is required.');
        }
        
        // $application = EA_Application_model::where('application_id', $application_id)->first();
        
        $old_license_number= DB::table('tnelb_license')->where('application_id', $application_id)->first();

        // var_dump($license_deatails->license_number);die;

        
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

            $today = \Carbon\Carbon::today();
            // dd($today);
            // exit;

            $license_details = DB::table('tnelb_license')
            ->where('application_id', $application_id)
            ->where('expires_at','<', $today)
            ->select('*')
            ->first();
            // dd($license_details->expires_at);
            // exit;

            // if($license_details){
            //    return redirect()
            //     ->route('apply-form-a')
            //     ->with('expired_license', true)
            //     ->with('expired_date', \Carbon\Carbon::parse($license_details->expires_at)->format('d-m-Y'));
            // }
            

            
        $banksolvency = Tnelb_banksolvency_a::where('application_id', $application_id)->where('status','1')->first();

        // $equipmentlist = Equipment_storetmp_A::where('application_id', $application_id)->first();

         

             $equiplist = Mst_equipment_tbl::where('equip_licence_name', 8)
            ->where('status', 1)
            ->orderBy('id')
            ->get();

            $equipmentlist = DB::table('equipmentforma_tbls')
            ->where('login_id', Auth::user()->login_id)
            ->where('application_id', $application_id) // IMPORTANT
            ->get();


        }

        return view('user_login.renew-form_ea', compact ('application', 'proprietors', 'staffs', 'draftCount', 'document','license_details', 'banksolvency', 'equiplist', 'equipmentlist', 'old_license_number'));
    }


     // ----------------------------draft
   public function edit($application_id)
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

            $Qcstaffs = DB::table('tnelb_ea_qc_models')->where('application_id', $application_id)->orderBy('id', 'ASC')->get();
            $document = DB::table('tnelb_applicant_doc_A')->where('application_id', $application_id)->first();
            $banksolvency = Tnelb_banksolvency_a::where('application_id', $application_id)->where('status','1')->first();

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
        }

        return view('user_login.apply-form-a', compact('application', 'proprietors', 'draftCount', 'staffs', 'document', 'banksolvency' , 'equipmentlist', 'equiplist', 'form_code', 'attachment_doc', 'Address_proof', 'equipmentDetails','Qcstaffs'));
    }


      public function edit_renewaldraft($application_id)
    {

        // dd($application_id);
        // exit;
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

            $license_details = DB::table('tnelb_license')
            ->where('application_id', $application_id)
            ->select('*')
            ->first();

              
        $banksolvency = Tnelb_banksolvency_a::where('application_id', $application_id)->where('status','1')->first();

        $equipmentlist = Equipment_storetmp_A::where('application_id', $application_id)->first();

            // dd($license_details);
            // exit;
        }

        return view('user_login.renew-form_ea', compact('application', 'proprietors', 'draftCount', 'staffs', 'document', 'license_details', 'banksolvency', 'equipmentlist'));
    }
    public function updatePaymentStatus(Request $request)
    {

        // dd($request->all())
        $request->validate([
            'application_id' => 'required|string',
            'payment_status' => 'required|in:draft,pending,paid',
        ]);

        EA_Application_model::where('application_id', $request->application_id)
            ->update(['payment_status' => $request->payment_status]);

        return response()->json(['status' => 'updated']);
    }

    // ----------------A draft and renew end---------------

    public function renew_form_ea_old($appl_id){

        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        if (!$appl_id) {
            return redirect()->route('dashboard')->with('error', 'Application ID is required.');
        }
        
        $application = EA_Application_model::where('application_id', $appl_id)->first();
        
        $license_deatails = DB::table('tnelb_license')->where('application_id', $appl_id)->first();

        // var_dump($license_deatails->license_number);die;

        $proprietors = DB::table('proprietordetailsform_A')
        ->where('application_id', $appl_id)
        ->get();

        $staffs = DB::table('tnelb_applicant_cl_staffdetails')
        ->where('application_id', $appl_id)
        ->get();

        
        $document = DB::table('tnelb_applicant_doc_A')
        ->where('application_id', $appl_id)
        ->first();

        return view('user_login.renew-form_ea', compact ('application', 'proprietors', 'staffs', 'document','license_deatails'));
    }

    public function store(Request $request)
    {
        $isDraft = $request->input('form_action') === 'draft';

        // ✅ Validation Rules
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
            'aadhaar'                       => 'required|digits:12',
            'pancard'                       => 'required|string|size:10',
            'declaration1'                  => 'required|string|max:255',
            'declaration2'                  => 'required|string|max:255',
        ];

        // Relax validation for Draft
        if ($isDraft) {
            foreach ($rules as $key => $rule) {
                $rules[$key] = str_replace('required', 'nullable', $rule);
            }
        }

        // Validate Data
        $validatedData = $request->validate($rules);

        // Generate Application ID
        $lastApplication    = EA_Application_model::latest('id')->value('application_id');
        $nextNumber         = $lastApplication ? ((int) substr($lastApplication, -7)) + 1 : 1111111;
        $newApplicationId   = $request->form_name . $request->license_name . date('y') . str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

        // Save Main Form Data
        $form = EA_Application_model::create([
            'login_id' => $request->login_id_store,
            'application_id' => $newApplicationId,
            'application_status' => 'P',
            'license_number' => '',
            'payment_status' => $isDraft ? 'draft' : 'paid',
            'name_of_authorised_to_sign' => !empty($request->name_of_authorised_to_sign)? json_encode($request->name_of_authorised_to_sign): null,
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
        
        if ($request->has('staff_name')) {
            foreach ($request->staff_name as $index => $staffName) {
                TnelbApplicantStaffDetail::create([
                    'login_id' => $request->login_id_store,
                    'application_id' => $newApplicationId,
                    'staff_name' => $staffName,
                    'staff_qualification' => $request->staff_qualification[$index] ?? null,
                    'cc_number' => $request->cc_number[$index] ?? null,
                    'cc_validity' => $request->cc_validity[$index] ?? null,
                ]);
            }
        }

        
        if ($request->has('proprietor_name')) {
           
            foreach ($request->proprietor_name as $index => $proprietor_name) {

                $competencyHolding = $request->competency_certificate_holding[$index] ?? 'no';
                $list =ProprietorformA::create([
                    'login_id' => $request->login_id_store,
                    'application_id' => $newApplicationId,
                    'proprietor_name' => $proprietor_name,
                    'proprietor_address' => $request->proprietor_address[$index] ?? null,
                    'age' => $request->age[$index] ?? null,
                    'qualification' => $request->qualification[$index] ?? null,
                    'fathers_name' => $request->fathers_name[$index] ?? 'Not Provided',
                    'present_business' => $request->present_business[$index] ?? null,

                    'competency_certificate_holding' => $competencyHolding,
                    'competency_certificate_number' => ($competencyHolding === 'yes')
                        ? ($request->competency_certificate_number[$index] ?? null)
                        : null,
                    'competency_certificate_validity' => ($competencyHolding === 'yes')
                        ? ($request->competency_certificate_validity[$index] ?? null)
                        : null,

                    'presently_employed' => $request->presently_employed[$index] ?? 'no',

                    'presently_employed_name' => ($request->presently_employed[$index] === 'yes')
                        ? ($request->presently_employed_name[$index] ?? null)
                        : null,
                    'presently_employed_address' => ($request->presently_employed[$index] === 'yes')
                        ? ($request->presently_employed_address[$index] ?? null)
                        : null,
                    'previous_experience' => $request->previous_experience[$index] ?? 'no',
                    'previous_experience_name' => ($request->previous_experience[$index] === 'yes')
                        ? ($request->previous_experience_name[$index] ?? null)
                        : null,
                    'previous_experience_address' => ($request->previous_experience[$index] === 'yes')
                        ? ($request->previous_experience_address[$index] ?? null)
                        : null,
                    'previous_experience_lnumber' => ($request->previous_experience[$index] === 'yes')
                        ? ($request->previous_experience_lnumber[$index] ?? null)
                        : null,
                ]);

            }
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

        // Return Draft Response
        return response()->json([
            'message' => 'Form saved as draft',
            'login_id' => $newApplicationId,
        ], 200);
    }
}
