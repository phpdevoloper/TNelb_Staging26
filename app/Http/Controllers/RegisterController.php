<?php

namespace App\Http\Controllers;

use App\Models\Admin\TnelbFee;
use App\Models\admin\TnelbForms;
use App\Models\EA_Application_model;
use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Forms_Meta;
use App\Models\CC_Proof_doc;
use App\Models\MstLicence;
use App\Services\Competency\CompetencyCertificateService;
use App\Services\FormS\FormSApplicationWorkflowService;
use App\Services\FormS\FormSProofDocumentService;
use App\Services\FormS\SensitiveProofCryptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Register;

use App\Models\Admin\Mst_equipment_tbl;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use function PHPUnit\Framework\isNull;

class RegisterController extends BaseController
{
    protected $today;
    public function __construct()
    {
        parent::__construct();   
        $this->middleware('web');
        $this->today = Carbon::today()->toDateString();
    }

    public function register()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('register');
    }



   public function store(Request $request)
    {
        // var_dump($request->Address);die;

        // dd($request->input('last_name'));exit;
        // Validate Input
        $validator = Validator::make($request->all(), [
            'salutation' => 'required|in:Mr,Mrs,Ms,Dr',
            'first_name' => 'required|string|max:50',
            'last_name'  => 'required|string|max:50',
            'gender'     => 'required|string',
            'mobile'     => [
                'required',
                'digits:10',
                Rule::unique('tnelb_registers', 'mobile'),
            ],
            'email'      => [
                'nullable',
                'email',
                Rule::unique('tnelb_registers', 'email'),
            ],
            'Address'    => 'required|string',
            'state'      => 'required|string|max:255',
            'district' => 'nullable|required_if:state,Tamil Nadu|string|max:255',

            'pincode'    => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate Login ID
        $latestRecord = Register::latest('id')->first();
        if ($latestRecord && preg_match('/tnelb_(\d+)/', $latestRecord->login_id, $matches)) {
            $newRecord = (int) $matches[1] + 1;
        } else {
            $newRecord = 1120;
        }
        $newLoginId = 'tnelb_' . $newRecord;

        // Store Data in Database
        $register = Register::create([
            'salutation' => $request->input('salutation'),
            'first_name' => $request->input('first_name'),
            'last_name'  => $request->input('last_name'),
            'gender'     => $request->input('gender'),
            'mobile'     => $request->input('mobile'),
            'email'      => $request->input('email'),
            'address'    => $request->input('Address'),
            'state'      => $request->input('state'),
            // 'district'   => $request->input('district'),
            'district'   => $request->filled('district') ? $request->district : 0,
            'pincode'    => $request->input('pincode'),
            'login_id'   => $newLoginId,
            'created_at' => now()
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Registration successful!',
            'login_id' => $newLoginId,
        ], 200);
    }


    public function store_bkUP(Request $request)
    {
        // Validate Input
        $validator = Validator::make($request->all(), [
            'Name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female,Transgender',
            'PhoneNo' => 'required|digits:10|unique:tnelb_registers,mobile',
            'EmailAddress' => 'nullable|email|unique:tnelb_registers,email',
            'Address' => 'required|string',
            'state' => 'required|string',
            'district' => 'required|string',
            'pincode' => 'required|digits:6',
            'aadhaar' => 'required|digits:12',
            'pancard' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Generate Login ID
        $latestRecord = Register::latest('id')->first();

        if ($latestRecord && preg_match('/tnelb_(\d+)/', $latestRecord->login_id, $matches)) {
            $newRecord = (int) $matches[1] + 1;
        } else {
            $newRecord = 1120; // Start from 1120 if no previous records exist
        }

        $newLoginId = 'tnelb_' . $newRecord;

        // Store Data in Database
        $register = Register::create([
            'name' => $request->input('Name'),
            'gender' => $request->input('gender'),
            'mobile' => $request->input('PhoneNo'),
            'email' => $request->input('EmailAddress'),
            'address' => $request->input('Address'),
            'state' => $request->input('state'),
            'district' => $request->input('district'),
            'pincode' => $request->input('pincode'),
            'aadhaar' => $request->input('aadhaar'),
            'pancard' => $request->input('pancard'),
            'login_id' => $newLoginId,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful!',
            'login_id' => $newLoginId,
        ], 200);
    }

    public function user_login()
    {
        return view('user_login.index');
    }


    public function cc_renew_form($appl_id)
    {
        if (! Auth::check()) {
            return redirect()->route('logout');
        }

        if (! $appl_id) {
            return redirect()->route('dashboard')->with('error', 'Application ID is required.');
        }

        $application = CC_Forms_Meta::findByApplicationId((string) $appl_id);
        if (! $application) {
            return redirect()->route('dashboard')->with('error', 'Application not found.');
        }

        $loginId = Auth::user()->login_id ?? session('login_id');
        if ($loginId && (string) ($application->login_id ?? '') !== (string) $loginId) {
            return redirect()->route('dashboard')->with('error', 'You can only renew your own application.');
        }

        $master = app(FormSApplicationWorkflowService::class)->masterApplication($application);
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

        $proofCrypt = app(SensitiveProofCryptService::class);
        $proofRows = CC_Proof_doc::where('application_id', $masterApplicationId)
            ->whereIn('proof_name', [
                FormSProofDocumentService::PROOF_AADHAAR,
                FormSProofDocumentService::PROOF_PAN,
            ])
            ->get();
        foreach ($proofRows as $proof) {
            $proofName = strtoupper(trim((string) ($proof->proof_name ?? '')));
            $proofNo = $proofCrypt->decryptProofNumber($proof->proof_no ?? null);
            if ($proofName === FormSProofDocumentService::PROOF_AADHAAR) {
                if ($proofNo !== null && $proofNo !== '') {
                    $application_details->aadhaar = $proofNo;
                }
                if (! empty($proof->proof_doc)) {
                    $application_details->aadhaar_doc = $proof->proof_doc;
                }
            } elseif ($proofName === FormSProofDocumentService::PROOF_PAN) {
                if ($proofNo !== null && $proofNo !== '') {
                    $application_details->pancard = $proofNo;
                }
                if (! empty($proof->proof_doc)) {
                    $application_details->pan_doc = $proof->proof_doc;
                    $application_details->pancard_doc = $proof->proof_doc;
                }
            }
        }

        $licence_name = MstLicence::where('status', 1)
            ->where(function ($q) use ($formName, $application_details) {
                $q->where('form_code', $formName);
                if (! empty($application_details->certificate_name)) {
                    $q->orWhere('cert_licence_code', $application_details->certificate_name);
                }
            })
            ->first();

        if (! $licence_name) {
            abort(504, 'Form Not Found..');
        }

        $fees_details = TnelbFee::where('cert_licence_id', $licence_name->id)
            ->whereDate('start_date', '<=', $this->today)
            ->select('fees', 'start_date')
            ->orderByDesc('start_date')
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

        $certService = app(CompetencyCertificateService::class);
        $license_details = $certService->asLicenseDetails($masterApplicationId, $formName)
            ?? $certService->asLicenseDetails((string) $application->application_id, $formName);

        $issuedForRenew = $license_details
            ? trim((string) ($license_details->license_number ?? $license_details->certificate_no ?? ''))
            : '';
        if ($issuedForRenew === '') {
            $issuedForRenew = trim((string) ($application_details->certificate_no ?? $application_details->wcc_no ?? ''));
        }
        if ($issuedForRenew !== '') {
            if (! $license_details) {
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
            // Prefill Q8 (SCC being renewed) when meta has no previous_scc_no yet.
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

        $proofService = app(FormSProofDocumentService::class);
        $applicant_photo = $proofService->loadPhotoForView($masterApplicationId)
            ?? (object) ['upload_path' => ''];
        $proof_doc = $proofService->loadSignForView($masterApplicationId)
            ?? (object) ['uploaded_doc' => ''];

        $applicationid = $appl_id;

        return view('user_login.renew-form', compact(
            'applicationid',
            'application_details',
            'edu_details',
            'exp_details',
            'apps_doc',
            'license_details',
            'applicant_photo',
            'proof_doc',
            'licence_name',
            'fees_details'
        ));
    }


    public function apply_form_s()
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }
        $authUser = Auth::user();

        $user = [
            'user_id' => $authUser->login_id,
            'salutation' => $authUser->salutation,
            'applicant_name' => $authUser->first_name.' '.$authUser->last_name,
        ];
        // var_dump($user);die;
        
        // $check_applications = Mst_Form_s_w::where('login_id', $user_id)
        //         ->where('form_name', 'S')
        //         ->exists();

        // if ($check_applications) {
        //     return redirect()->route('dashboard')->with('already_applied', true);
        // }

        return view('user_login.apply-form-s', compact('user'));
    }



    public function apply_form_w()
    {

        if (!Auth::check()) {
            return redirect()->route('logout');
        }
        $authUser = Auth::user();

        $user = [
            'user_id' => $authUser->login_id,
            'salutation' => $authUser->salutation,
            'applicant_name' => $authUser->first_name.' '.$authUser->last_name,
        ];

        // $user_id = Auth::user()->login_id;
        // $check_applications = Mst_Form_s_w::where('login_id', $user_id)
        //         ->where('form_name', 'W')
        //         ->exists();

        // if ($check_applications) {
        //     return redirect()->route('dashboard')->with('already_applied', true);
        // }


        return view('user_login.apply-form-w', compact('user'));
    }


    public function apply_form_wh()
    {

        if (!Auth::check()) {
            return redirect()->route('logout');
        }
        $authUser = Auth::user();

        $user = [
            'user_id' => $authUser->login_id,
            'salutation' => $authUser->salutation,
            'applicant_name' => $authUser->first_name.' '.$authUser->last_name,
        ];

        // $user_id = Auth::user()->login_id;
        // $check_applications = Mst_Form_s_w::where('login_id', $user_id)
        //         ->where('form_name', 'WH')
        //         ->exists();

        // if ($check_applications) {
        //     return redirect()->route('dashboard')->with('already_applied', true);
        // }


        return view('user_login.apply-form-wh', compact('user'));
    }

    public function apply_form_a()
    {

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

        // $equipmentlist = DB::table('equipmentforma_tbls')
        //     ->where('login_id', Auth::user()->login_id)
        //     // ->where('application_id', $applicationId) // IMPORTANT
        //     ->get();

        // $user_id = Auth::user()->login_id;
        // $check_applications = EA_Application_model::where('login_id', $user_id)
        //         ->where('form_name', 'A')
        //         ->exists();

        // if ($check_applications) {
        //     return redirect()->route('dashboard')->with('already_applied', true);
        // }


        return view('user_login.apply-form-a', compact('equiplist', 'form_code'));
    }

    public function loginpage()
    {
        return view('loginpage');
    }

    public function apply_form($form_name, $application_id)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        
        $application = CC_Forms_Meta::findByApplicationId((string) $application_id)
            ?? DB::table('tnelb_application_tbl')->where('application_id', $application_id)->first();

        if (!$application) {
            return redirect()->back()->with('error', 'Application not found.');
        }

        // Return the view with the fetched data
        $viewName = ($form_name === 's') ? 'user_login.apply-form-s' : 'user_login.apply-form-w';

        // Return the dynamic view
        return view($viewName, compact('application', 'form_name'));
    }



    
}
