<?php

namespace App\Http\Controllers;

use App\Models\Admin\LicenceCategory;
use App\Models\Admin\TnelbFee;
use App\Models\Mst_documents;
use App\Models\Mst_education;
use App\Models\Mst_experience;
use App\Models\Mst_Form_s_w;
use App\Models\mst_workflow;
use App\Models\Admin\SupervisorModel;
use App\Models\MstLicence;
use App\Models\Payment;
use App\Models\Tnelb_Renewals;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbApplicantsSign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Helpers\RoleHelper;

class FormController extends BaseController
{

    protected $today,$dbNow;
    public function __construct()
    {
        parent::__construct();   
        $this->middleware('web');
        $this->today = Carbon::today()->toDateString();
        $this->dbNow  = DB::selectOne("SELECT date_trunc('second', NOW()::timestamp) AS db_now")->db_now;


    }
    
    private function getApplicableFee($certLicenceId)
    {
        return TnelbFee::where('cert_licence_id', $certLicenceId)
        ->whereDate('start_date', '<=', $this->today)
        ->select('fees', 'start_date')
        ->orderBy('start_date', 'desc')
        ->first();
    }

    /** Competency certificate forms stored in Mst_Form_s_w (Form S, W, WH, P). */
    private function isCompetencyForm(?string $formName): bool
    {
        return in_array($formName, ['S', 'W', 'WH', 'P'], true);
    }

    private function hasWorkExperiencePayload(Request $request): bool
    {
        return $request->has('work_level')
            || $request->has('work_employer_name')
            || $request->has('designation');
    }

    private function getWorkRowIndexes(Request $request): array
    {
        $indexes = [];
        foreach (['work_level', 'work_employer_name', 'designation', 'experience', 'work_experience_total'] as $field) {
            $values = $request->input($field, []);
            if (is_array($values)) {
                $indexes = array_merge($indexes, array_keys($values));
            }
        }

        $indexes = array_values(array_unique($indexes, SORT_REGULAR));
        usort($indexes, static function ($a, $b) {
            return (int) $a <=> (int) $b;
        });

        return $indexes;
    }

    private function mapWorkExperienceRow(Request $request, $key, ?string $formName): array
    {
        $normalizedForm = strtoupper((string) $formName);
        $isFormS = $normalizedForm === 'S';

        $companyName = $isFormS
            ? trim((string) ($request->work_employer_name[$key] ?? $request->work_level[$key] ?? ''))
            : trim((string) ($request->work_level[$key] ?? ''));

        $experience = $isFormS
            ? trim((string) ($request->work_experience_total[$key] ?? $request->experience[$key] ?? ''))
            : trim((string) ($request->experience[$key] ?? ''));

        $designation = trim((string) ($request->designation[$key] ?? ''));
        $empType = trim((string) ($request->work_employment_type[$key] ?? ''));
        $fromDate = trim((string) ($request->work_date_from[$key] ?? ''));
        $toDate = trim((string) ($request->work_date_to[$key] ?? ''));
        $intimationDate = trim((string) ($request->work_intimation_date[$key] ?? ''));

        // Intimation letter date only applies to contractors. Clear it for any other
        // employment type to guard against accidental array-misalignment on submit
        // (older browsers / disabled inputs that skip from the POST payload).
        if (strtolower($empType) !== 'contractor') {
            $intimationDate = '';
        }

        return [
            'company_name' => $companyName,
            'experience' => $experience,
            'designation' => $designation,
            'emp_type' => ($empType !== '' ? $empType : null),
            'emp_cate' => ($companyName !== '' ? $companyName : null),
            'from_date' => ($fromDate !== '' ? $fromDate : null),
            'to_date' => ($toDate !== '' ? $toDate : null),
            'intimation_date' => ($intimationDate !== '' ? $intimationDate : null),
            'total_exp' => ($experience !== '' ? $experience : null),
            'is_empty' => ($companyName === '' && $experience === '' && $designation === ''),
        ];
    }

    private function decryptPanForDisplay($applicationDetails): void
    {
        if (!$applicationDetails || !isset($applicationDetails->pancard) || $applicationDetails->pancard === null || $applicationDetails->pancard === '') {
            return;
        }

        try {
            $applicationDetails->pancard = Crypt::decryptString((string) $applicationDetails->pancard);
        } catch (\Throwable $e) {
            // Keep legacy/plain values as-is when not encrypted.
        }
    }

    /**
     * Populate issued licence number for renewal fee AJAX when tnelb_license has no row or empty number.
     */
    private function enrichLicenseDetailsForRenewal($appl_id, $application_details, $license_details)
    {
        if (!$application_details) {
            return $license_details;
        }
        $issued = $license_details ? trim((string) ($license_details->license_number ?? '')) : '';
        if ($issued === '') {
            $issued = trim((string) ($application_details->license_number ?? ''));
        }
        if ($issued === '') {
            $compRow = DB::table('tnelb_application_tbl')->where('application_id', $appl_id)->first();
            if ($compRow) {
                $issued = trim((string) ($compRow->license_number ?? ''));
            }
        }
        if ($issued === '') {
            return $license_details;
        }
        if (!$license_details) {
            return (object) ['license_number' => $issued];
        }
        if (trim((string) ($license_details->license_number ?? '')) === '') {
            $license_details->license_number = $issued;
        }

        return $license_details;
    }


    public function editApplication($appl_id)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        if (!$appl_id) {
            return redirect()->route('dashboard')->with('error', 'Application ID is required.');
        }

        
        $application_details = DB::table('tnelb_application_tbl')
        ->where('application_id', $appl_id)
        ->select('*')
        ->first();

        $this->decryptPanForDisplay($application_details);

        
        $form_details = MstLicence::where('status', 1)
            ->select('*')
            ->get()
            ->toArray();
        
        $current_form = collect($form_details)->firstWhere('form_code', $application_details->form_name);

         $licence_name = DB::table('mst_licences')->where('form_code', $application_details->form_name)->first();

        if (!$current_form) {
            abort(504, 'Form Not Found..');
        }
        
        $fees_details = $this->getApplicableFee($current_form['id']);

        if (!$fees_details) {
            abort(505, 'The requested form details could not be found.');
        }


        if (!$application_details) {
            return redirect()->route('dashboard')->with('error', 'Application not found.');
        }

        $edu_details = DB::table('tnelb_applicants_edu')
            ->where('application_id', $appl_id)
            ->select('*')
            ->orderBy('year_of_passing', 'desc')
            ->get();

        $exp_details = DB::table('tnelb_applicants_exp')
            ->where('application_id', $appl_id)
            ->select('*')
            ->orderBy('id', 'asc')
            ->get();


        $license_details = DB::table('tnelb_license')
            ->where('application_id', $appl_id)
            ->select('*')
            ->first();
        $license_details = $this->enrichLicenseDetailsForRenewal($appl_id, $application_details, $license_details);

        $applicant_photo = TnelbApplicantPhoto::where('application_id', $appl_id)->first();

        $proof_doc = TnelbApplicantsSign::where('application_id', $appl_id)->first();

        $applicationid = $appl_id;

        $queries = DB::table('tnelb_query_applicable')
            ->where('application_id', $appl_id)
            ->where('query_status', 'P')
            ->orderByDesc('id')
            ->get();

        return view('user_login.edit_application', compact(
            'applicationid',
            'application_details',
            'edu_details',
            'exp_details',
            'license_details',
            'applicant_photo',
            'proof_doc',
            'fees_details',
            'form_details',
            'licence_name',
            'queries'
        ));

    }

    public function edit_application($application_id)
    {
        return $this->editApplication($application_id);
    }

    /**
     * Edit page for returned (QU) applications only. Same form as edit_application
     * but with only "Submit corrections" button (no Draft / Payment).
     */
    public function editReturnedApplication($appl_id)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }
        if (!$appl_id) {
            return redirect()->route('dashboard')->with('error', 'Application ID is required.');
        }

        $application_details = DB::table('tnelb_application_tbl')
            ->where('application_id', $appl_id)
            ->select('*')
            ->first();

        $this->decryptPanForDisplay($application_details);

        if (!$application_details) {
            return redirect()->route('dashboard')->with('error', 'Application not found.');
        }

        if ((string) $application_details->status !== 'QU') {
            return redirect()->route('dashboard')->with('error', 'This page is only for applications returned with a query.');
        }

        $loginId = session('login_id');
        if (!$loginId || (string) $application_details->login_id !== (string) $loginId) {
            return redirect()->route('dashboard')->with('error', 'You can only edit your own returned application.');
        }

        $form_details = MstLicence::where('status', 1)->select('*')->get()->toArray();
        $current_form = collect($form_details)->firstWhere('form_code', $application_details->form_name);
        $licence_name = DB::table('mst_licences')->where('form_code', $application_details->form_name)->first();

        if (!$current_form) {
            abort(504, 'Form Not Found.');
        }

        $fees_details = $this->getApplicableFee($current_form['id']);
        if (!$fees_details) {
            abort(505, 'The requested form details could not be found.');
        }

        $edu_details = DB::table('tnelb_applicants_edu')
            ->where('application_id', $appl_id)
            ->orderBy('year_of_passing', 'desc')
            ->get();

        $exp_details = DB::table('tnelb_applicants_exp')
            ->where('application_id', $appl_id)
            ->orderBy('id', 'asc')
            ->get();

        $license_details = DB::table('tnelb_license')
            ->where('application_id', $appl_id)
            ->first();
        $license_details = $this->enrichLicenseDetailsForRenewal($appl_id, $application_details, $license_details);

        $applicant_photo = TnelbApplicantPhoto::where('application_id', $appl_id)->first();
        $proof_doc = TnelbApplicantsSign::where('application_id', $appl_id)->first();
        $applicationid = $appl_id;

        // Applicant-facing copy: only what was recorded in return-to-applicant log (not tnelb_query_applicable / internal staff queries)
        $queries = collect();
        $queryReasonsForValidation = [];
        $returnRemarks = '';

        if (Schema::hasTable('tnelb_return_to_applicant_log')) {
            $returnLogRow = DB::table('tnelb_return_to_applicant_log')
                ->where('application_id', $appl_id)
                ->orderByDesc('id')
                ->first();

            if ($returnLogRow) {
                $returnRemarks = trim((string) ($returnLogRow->remarks ?? ''));
                $queryTypesRaw = $returnLogRow->query_types ?? null;
                $items = is_string($queryTypesRaw) ? json_decode($queryTypesRaw, true) : $queryTypesRaw;
                if (!is_array($items)) {
                    $items = ($queryTypesRaw !== null && $queryTypesRaw !== '' && is_string($queryTypesRaw))
                        ? [$queryTypesRaw]
                        : [];
                }
                foreach ($items as $item) {
                    if (is_string($item) && $item !== '') {
                        $queryReasonsForValidation[] = $item;
                    }
                }
                $queryReasonsForValidation = array_values(array_unique($queryReasonsForValidation));

                if ($queryReasonsForValidation !== [] || $returnRemarks !== '') {
                    $queries = collect([(object) [
                        'query_type' => json_encode($queryReasonsForValidation),
                        'raised_by' => $returnLogRow->returned_by_role ?? null,
                    ]]);
                }
            }
        }

        return view('user_login.edit_returned_application', compact(
            'applicationid',
            'application_details',
            'edu_details',
            'exp_details',
            'license_details',
            'applicant_photo',
            'proof_doc',
            'fees_details',
            'form_details',
            'licence_name',
            'queries',
            'queryReasonsForValidation',
            'returnRemarks'
        ));
    }

   public function store(Request $request)
    {
        
        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);

        if ($this->isCompetencyForm($request->form_name)) {
            $raw = $request->input('pancard');
            $pc = is_string($raw) ? strtoupper(preg_replace('/\s+/', '', $raw)) : '';
            $request->merge(['pancard' => $pc === '' ? null : $pc]);
        }

        
        $isWorkOptional = in_array($request->form_name, ['W', 'WH'], true);
        $educationLevelRule = ($request->form_name === 'S')
            ? 'required|string|in:DEE,BEE,MEE,AMIE|max:50'
            : 'required|string|max:50';

        $rules = [
            
            // basic fields
            'login_id'             => 'required|string',
            'applicant_name'       => 'required|string|max:80',
            'fathers_name'         => 'required|string|max:80',
            'applicants_address'   => 'required|string|max:255',
            'd_o_b'                => 'required|date',
            'age'                  => 'required|integer|min:18|max:100',
            'previously_number'    => 'nullable|string',
            'previously_date'      => 'nullable|date',
            'previously_issue_date' => 'nullable|date',
            'wireman_details'      => 'nullable|string|max:255',
            'aadhaar'              => 'required|string|digits:12',
            'form_name'            => 'required|string|max:2',
            'license_name'         => 'required|string|max:2',
            'form_id'              => 'required|integer',
            // 'amount'               => 'required|numeric|min:0',
            'competency_certificate_no' => 'nullable|string|max:80',
            'certificate_date'              => 'nullable|date',
            'certificate_issue_date'        => 'nullable|date',


            // education arrays
            'educational_level'    => 'required|array|min:1',
            'educational_level.*'  => $educationLevelRule,
            'institute_name'       => 'required|array|min:1',
            'institute_name.*'     => 'required|string|max:80',
            'month_of_passing'     => 'required|array|min:1',
            'month_of_passing.*'   => 'required|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'year_of_passing'      => 'required|array|min:1',
            'year_of_passing.*'    => 'required|digits:4',
            'certificate_no'       => 'required|array|min:1',
            'certificate_no.*'     => 'required|string|max:20',
            
            // work experience arrays
            'work_level'           => $isWorkOptional ? 'nullable|array' : 'required|array|min:1',
            'work_level.*'         => $isWorkOptional ? 'nullable|string|max:80' : 'required|string|max:80',
            'experience'           => $isWorkOptional ? 'nullable|array' : 'required|array|min:1',
            'experience.*'         => $isWorkOptional ? 'nullable|numeric|min:0|max:50' : 'required|numeric|min:0|max:50',
            'designation'          => $isWorkOptional ? 'nullable|array' : 'required|array|min:1',
            'designation.*'        => $isWorkOptional ? 'nullable|string|max:80' : 'required|string|max:80',
            
            // single files
            'upload_photo'         => 'required|image|mimes:jpg,jpeg,png|max:50',
            'upload_sign'          => 'required|image|mimes:jpg,jpeg,png|max:50',
            'aadhaar_doc'          => 'required|mimes:pdf|min:10|max:250',
            
            // multiple files (arrays) — file OR pre-uploaded path via existing_document / existing_work_document
            'education_document'   => 'nullable|array',
            'education_document.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
            'existing_document'    => 'nullable|array',
            'existing_document.*'    => 'nullable|string|max:500',
            'work_document'        => 'nullable|array',
            'work_document.*'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
            'existing_work_document' => 'nullable|array',
            'existing_work_document.*' => 'nullable|string|max:500',
            
        ];

        if ($this->isCompetencyForm($request->form_name)) {
            $rules['pancard'] = 'nullable|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/';
            $rules['pancard_doc'] = 'nullable|mimes:pdf|min:10|max:250';
        }

        $messages = [
            
            // education arrays
            'educational_level.required'    => 'Please add at least one educational qualification.',
            'educational_level.*.required'  => 'Educational level is required.',
            'educational_level.*.string'    => 'Educational level must be a valid string.',
            'educational_level.*.max'       => 'Educational level may not be greater than 50 characters.',

            'institute_name.required'       => 'Please add at least one educational qualification.',
            'institute_name.*.required'     => 'Institute name is required.',
            'institute_name.*.string'       => 'Institute name must be a valid string.',
            'institute_name.*.max'          => 'Institute name may not be greater than 80 characters.',
            
            'month_of_passing.required'     => 'Please add at least one educational qualification.',
            'month_of_passing.*.required'   => 'Month of passing is required.',
            'month_of_passing.*.in'         => 'Month of passing must be a valid month.',

            'year_of_passing.required'      => 'Please add at least one educational qualification.',
            'year_of_passing.*.required'    => 'Year of passing is required.',
            'year_of_passing.*.digits'      => 'Year of passing must be a 4-digit year.',
            
            'certificate_no.required'       => 'Please add at least one educational qualification.',
            'certificate_no.*.required'         => 'Certificate No is required.',
            'certificate_no.*.string'           => 'Certificate No must be a valid text value.',
            'certificate_no.*.max'              => 'Certificate No may not be greater than 20 characters.',

            // work experience arrays
            'work_level.required'           => 'Please add at least one work experience.',
            'work_level.*.required'         => 'Work level is required.',
            'work_level.*.string'           => 'Work level must be a valid string.',
            'work_level.*.max'              => 'Work level may not be greater than 80 characters.',
            
            'experience.required'           => 'Please add at least one work experience.',
            'experience.*.required'         => 'Experience (in years) is required.',
            'experience.*.numeric'          => 'Experience must be a valid number.',
            'experience.*.min'              => 'Experience cannot be negative.',
            'experience.*.max'              => 'Experience may not exceed 50 years.',

            'designation.required'          => 'Please add at least one work experience.',
            'designation.*.required'        => 'Designation is required.',
            'designation.*.string'          => 'Designation must be a valid string.',
            'designation.*.max'             => 'Designation may not be greater than 80 characters.',
            
            'aadhaar.digits' => 'Aadhaar number should be 12 digits.',
            'applicant_name.max' => 'Applicant name may not be greater than 80 characters.',
            'fathers_name.max' => 'Father\'s name may not be greater than 80 characters.',
            'applicants_address.max' => 'Address may not be greater than 255 characters.',
            'competency_certificate_no.max' => 'Certificate number may not be greater than 80 characters.',
            'educational_level.*.in' => 'For FORM S, only Diploma (EE), B.E (EE), M.E (EE), or A pass in AMIE options are allowed.',
            'pancard.required' => 'PAN card number is required.',
            'pancard.regex' => 'Enter a valid 10-character PAN (e.g. ABCDE1234F).',
            'pancard_doc.required' => 'PAN card document upload is required.',
            
             'education_document.*.max'    => 'Educational document must not be greater than 200 kilobytes.',
            'work_document.required'           => 'Please upload at least one experience document.',
            'work_document.*.required'         => 'Experience document is required.',
            'work_document.*.max'              => 'Experience document must not be greater than 200 kilobytes.',
            
        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function ($validator) use ($request, $isWorkOptional) {
            if (!$isWorkOptional) {
                return;
            }

            $levels = $request->work_level ?? [];
            $exps = $request->experience ?? [];
            $designations = $request->designation ?? [];

            $max = max(count($levels), count($exps), count($designations));
            for ($i = 0; $i < $max; $i++) {
                $wl = trim((string)($levels[$i] ?? ''));
                $ex = trim((string)($exps[$i] ?? ''));
                $des = trim((string)($designations[$i] ?? ''));

                $any = ($wl !== '' || $ex !== '' || $des !== '');
                if (!$any) {
                    continue;
                }

                if ($wl === '') {
                    $validator->errors()->add("work_level.$i", 'Work level is required.');
                }
                if ($ex === '') {
                    $validator->errors()->add("experience.$i", 'Experience (in years) is required.');
                }
                if ($des === '') {
                    $validator->errors()->add("designation.$i", 'Designation is required.');
                }
            }
        });
        $validator->after(function ($validator) use ($request, $isWorkOptional) {
            if (! $this->isCompetencyForm($request->form_name ?? null)) {
                return;
            }

            foreach ($request->educational_level ?? [] as $key => $level) {
                if (
                    empty($level)
                    || empty($request->institute_name[$key] ?? null)
                    || empty($request->month_of_passing[$key] ?? null)
                    || empty($request->year_of_passing[$key] ?? null)
                ) {
                    continue;
                }
                $hasFile = $request->hasFile('education_document.'.$key);
                $existing = $request->input('existing_document.'.$key);
                if (! $hasFile && ($existing === null || $existing === '')) {
                    $validator->errors()->add(
                        'education_document.'.$key,
                        'Please choose a PDF and click Upload, or attach the certificate document before submitting.'
                    );
                }
                if ($existing !== null && $existing !== '' && ! $this->isValidCompetencyAjaxDocPath($existing, 'education')) {
                    if ($this->hasValidCompetencyAjaxDocFormat($existing, 'education')) {
                        if (! $hasFile) {
                            $validator->errors()->add(
                                'education_document.'.$key,
                                'The previously uploaded certificate is missing on the server. Please upload the document again.'
                            );
                        }
                    } else {
                        $validator->errors()->add('existing_document.'.$key, 'Invalid uploaded document reference.');
                    }
                }
            }

            if ($isWorkOptional) {
                return;
            }

            foreach ($request->work_level ?? [] as $key => $company) {
                if (
                    empty($company)
                    || empty($request->experience[$key] ?? null)
                    || empty($request->designation[$key] ?? null)
                ) {
                    continue;
                }
                $hasFile = $request->hasFile('work_document.'.$key);
                $existing = $request->input('existing_work_document.'.$key);
                if (! $hasFile && ($existing === null || $existing === '')) {
                    $validator->errors()->add(
                        'work_document.'.$key,
                        'Please choose a PDF and click Upload, or attach the experience document before submitting.'
                    );
                }
                if ($existing !== null && $existing !== '' && ! $this->isValidCompetencyAjaxDocPath($existing, 'work')) {
                    if ($this->hasValidCompetencyAjaxDocFormat($existing, 'work')) {
                        if (! $hasFile) {
                            $validator->errors()->add(
                                'work_document.'.$key,
                                'The previously uploaded experience document is missing on the server. Please upload the document again.'
                            );
                        }
                    } else {
                        $validator->errors()->add('existing_work_document.'.$key, 'Invalid uploaded document reference.');
                    }
                }
            }
        });
        $validator->validate();
        
        
        // Safety fallback: if client doesn't send form_action, keep first save as draft.
        $action = $request->input('form_action', 'draft');
        $loginId = $request->login_id;

        // Idempotency guard: if the client already has an application_id, do not insert
        // a new application row. Route through draft_update so the same record is updated.
        $existingApplicationId = trim((string) $request->input('application_id', ''));
        if ($existingApplicationId !== '' && Mst_Form_s_w::where('application_id', $existingApplicationId)->exists()) {
            return $this->draft_update($request, $existingApplicationId);
        }
        
        
        DB::beginTransaction();
        
        $encrypted_aadhaar = Crypt::encryptString($request->aadhaar);
        $encrypted_pancard = ($this->isCompetencyForm($request->form_name) && $request->filled('pancard'))
            ? Crypt::encryptString($request->pancard)
            : null;

        try {
            // Generate New Application ID
            $appl_type = $request->appl_type ?? '';
            if ($appl_type == 'R') {
                $lastApplication = Mst_Form_s_w::latest('id')->value('application_id');
                if ($lastApplication) {
                    $lastNumber = (int) substr($lastApplication, -7);
                    $newApplicationId = $appl_type.$request->form_name . $request->license_name . date('y') . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
                } else {
                    $newApplicationId = $appl_type.$request->form_name . $request->license_name . date('y') . '1111111';
                }     
            }else{
                $lastApplication = Mst_Form_s_w::latest('id')->value('application_id');
                if ($lastApplication) {
                    $lastNumber = (int) substr($lastApplication, -7);
                    $newApplicationId = $request->form_name . $request->license_name . date('y') . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
                } else {
                    $newApplicationId = $request->form_name . $request->license_name . date('y') . '1111111';
                }
                
            }
            
            $aadhaarFilename = null;
            
            if ($request->hasFile('aadhaar_doc')) {
                $file = $request->file('aadhaar_doc');
                
                $contents = file_get_contents($file->getRealPath());
                
                $encrypted = Crypt::encrypt($contents);
                
                $aadhaarFilename = time() . '_' . rand(10000, 9999999) . '.bin';
                $destinationPath = storage_path('app/private_documents');
                
                
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                
                file_put_contents($destinationPath . '/' . $aadhaarFilename, $encrypted);
            }

            $panFilename = null;
            if ($this->isCompetencyForm($request->form_name) && $request->hasFile('pancard_doc')) {
                $panFile = $request->file('pancard_doc');
                $panContents = file_get_contents($panFile->getRealPath());
                $panEncrypted = Crypt::encrypt($panContents);
                $panFilename = time() . '_' . rand(10000, 9999999) . '_pan.bin';
                $destinationPath = storage_path('app/private_documents');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                file_put_contents($destinationPath . '/' . $panFilename, $panEncrypted);
            }
            
            $form = Mst_Form_s_w::create([
                'login_id'            => $loginId,
                'applicant_name'      => $request->applicant_name ?? '',
                'fathers_name'        => $request->fathers_name ?? '',
                'applicants_address'  => $request->applicants_address,
                'd_o_b'               => $request->dob ?? $request->d_o_b,
                'age'                 => $request->age,
                'previously_number'   => $request->previously_number ?? 0,
                'previously_date'     => $request->previously_date ?? 0,
                'previously_issue_date' => $request->previously_issue_date ?: null,
                'application_id'      => $newApplicationId,
                'wireman_details'     => $request->wireman_details,
                'form_name'           => $request->form_name,
                'form_id'             => $request->form_id,
                'license_name'        => $request->license_name,
                'aadhaar'             => $encrypted_aadhaar,
                'pancard'             => $encrypted_pancard,
                'status'              => 'P',
                'appl_type'           => $appl_type,
                'payment_status'      => ($action === 'draft') ? 'draft' : 'payment',
                'aadhaar_doc'         => $aadhaarFilename,
                'pan_doc'             => $panFilename,
                'certificate_no'      => $request->competency_certificate_no,
                'certificate_date'    => $request->certificate_date,
                'certificate_issue_date' => $request->certificate_issue_date ?: null,
                'cert_verify'         => $request->cert_verify ?? '0',
                'license_verify'      => $request->l_verify ?? '0',
                'submitted_date'      => $this->dbNow,
                'created_at'          => $this->dbNow,
            ]);


            $applicationId = $form->application_id;
            $loginId = $form->login_id;


            $form_details = MstLicence::where('status', 1)
            ->select('*')
            ->get()
            ->toArray();
            $form_category = LicenceCategory::where('status', 1)
            ->select('*')
            ->get()
            ->toArray();
       
            $current_form = collect($form_details)->firstWhere('cert_licence_code', $form->license_name);
            $category_type = collect($form_category)->firstWhere('id', $current_form['category_id']);

            $licence_details['licence_name'] = $current_form['licence_name'];
            // var_dump($licence_details);die;
            $licence_details['category_name'] = $category_type['category_name'];
            $licence_details['form_type'] = $form->appl_type;
            
            // process education (upsert per level so duplicate DOM rows cannot create duplicate DB rows)
            if ($request->has('educational_level')) {
                $lastEdu = Mst_education::whereNotNull('edu_serial')->latest('id')->value('edu_serial');
                $eduSeq = $lastEdu ? (int) str_replace('edu_', '', $lastEdu) : 0;

                foreach ($request->educational_level as $key => $level) {
                    // skip empty/incomplete rows
                    if (
                        empty($level)
                        || empty($request->institute_name[$key] ?? null)
                        || empty($request->month_of_passing[$key] ?? null)
                        || empty($request->year_of_passing[$key] ?? null)
                    ) {
                        continue;
                    }

                    $monthRaw = $request->month_of_passing[$key] ?? null;
                    $monthVal = null;
                    if ($monthRaw !== null && $monthRaw !== '') {
                        $m = (int) ltrim((string) $monthRaw, '0');
                        if ($m >= 1 && $m <= 12) {
                            $monthVal = $m;
                        }
                    }

                    $filePath = null;
                    if ($request->hasFile('education_document') && isset($request->file('education_document')[$key])) {
                        $file = $request->file('education_document')[$key];
                        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $destinationPath = public_path('education_document');
                        $file->move($destinationPath, $filename);
                        $filePath = 'education_document/' . $filename;
                    } elseif (! empty($request->existing_document[$key] ?? null)
                        && $this->isValidCompetencyAjaxDocPath($request->existing_document[$key], 'education')) {
                        $filePath = $request->existing_document[$key];
                    }

                    $upsertAttrs = [
                        'login_id'           => $loginId,
                        'application_id'     => $newApplicationId,
                        'educational_level'  => $level,
                    ];

                    $existingByKey = Mst_education::where($upsertAttrs)->first();

                    $newEduSerial = ($existingByKey && $existingByKey->edu_serial)
                        ? $existingByKey->edu_serial
                        : ('edu_' . (++$eduSeq));

                    $uploadToStore = $filePath;
                    if ($uploadToStore === null && $existingByKey && $existingByKey->upload_document) {
                        $uploadToStore = $existingByKey->upload_document;
                    }

                    Mst_education::updateOrCreate(
                        $upsertAttrs,
                        [
                            'institute_name'    => $request->institute_name[$key],
                            'month_passing'     => $monthVal ?? $existingByKey?->month_passing ?? $monthRaw,
                            'year_of_passing'   => $request->year_of_passing[$key],
                            'certificate_no'    => $request->certificate_no[$key] ?? null,
                            'edu_serial'        => $newEduSerial,
                            'upload_document'   => $uploadToStore,
                        ]
                    );
                }
            }
            
            // process experience
            if ($this->hasWorkExperiencePayload($request)) {
                foreach ($this->getWorkRowIndexes($request) as $key) {
                    $workRow = $this->mapWorkExperienceRow($request, $key, $request->form_name ?? null);
                    $company = $workRow['company_name'];
                    $expYears = $workRow['experience'];
                    $designation = $workRow['designation'];

                    if (empty($company) || empty($expYears) || empty($designation)) {
                        continue;
                    }
                    
                    // compute exp_serial safely
                    $lastExp = Mst_experience::whereNotNull('exp_serial')->latest('id')->value('exp_serial');
                    if ($lastExp) {
                        $lastNum = (int) str_replace('exp_', '', $lastExp);
                        $newExpSerial = 'exp_' . ($lastNum + 1);
                    } else {
                        $newExpSerial = 'exp_1';
                    }
                    
                    $filePath = null;
                    if ($request->hasFile("work_document") && isset($request->file("work_document")[$key])) {
                        $file = $request->file("work_document")[$key];
                        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $destinationPath = public_path('work_experience');
                        $file->move($destinationPath, $filename);
                        $filePath = 'work_experience/' . $filename;
                    } elseif (! empty($request->existing_work_document[$key] ?? null)
                        && $this->isValidCompetencyAjaxDocPath($request->existing_work_document[$key], 'work')) {
                        $filePath = $request->existing_work_document[$key];
                    }
                    
                    Mst_experience::create([
                        'login_id'        => $loginId,
                        'emp_type'        => $workRow['emp_type'],
                        'emp_cate'        => $workRow['emp_cate'],
                        'intimation_date' => $workRow['intimation_date'],
                        'from_date'       => $workRow['from_date'],
                        'to_date'         => $workRow['to_date'],
                        'total_exp'       => $workRow['total_exp'],
                        'designation'     => $designation,
                        'application_id'  => $newApplicationId,
                        'exp_serial'      => $newExpSerial,
                        'upload_document' => $filePath,
                    ]);
                }
            }
            
            // process photo
            if ($request->hasFile('upload_photo')) {
                $photoPath = 'user_' . time() . '.' . $request->file('upload_photo')->getClientOriginalExtension();
                $destinationPath = public_path('attached_documents');
                $request->file('upload_photo')->move($destinationPath, $photoPath);
                
                TnelbApplicantPhoto::create([
                    'login_id'       => $loginId,
                    'application_id' => $applicationId,
                    'upload_path'    => 'attached_documents/' . $photoPath,
                ]);
            }

            // process signature
            if ($request->hasFile('upload_sign')) {
                $signFile = $request->file('upload_sign');
                $signName = 'sign_' . time() . '.' . $signFile->getClientOriginalExtension();
                $signDestination = public_path('attached_documents');

                // Do NOT auto-create folder; fail with a clear message instead
                if (!is_dir($signDestination)) {
                    throw new \Exception('Signature upload folder "public/attached_documents" does not exist. Please contact the administrator.');
                }

                $signFile->move($signDestination, $signName);

                TnelbApplicantsSign::updateOrCreate(
                    ['application_id' => $applicationId],
                    [
                        'login_id'      => $loginId,
                        'uploaded_doc'  => 'attached_documents/' . $signName,
                    ]
                );
            }
            
            
            
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Form submitted successfully!',
                'application_id' => $applicationId,
                'applicantName' => $form->applicant_name,
                'form_name'    => $form->form_name,
                'licence_name' => $licence_details['licence_name'],
                'type_of_apps' => $licence_details['category_name'],
                'form_type'    => $licence_details['form_type'] == 'N' ? 'FRESH' : 'RENEWAL',
                'date_apps'    => Carbon::parse($this->dbNow)->format('d-m-Y')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save form: ' . $e->getMessage()
            ], 500);
        }
    }


    // DRAFT UPDATE
    public function draft_update(Request $request, $applicationId)
    {
        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);

        if ($this->isCompetencyForm($request->form_name ?? null)) {
            $raw = $request->input('pancard');
            $pc = is_string($raw) ? strtoupper(preg_replace('/\s+/', '', $raw)) : '';
            $request->merge(['pancard' => $pc === '' ? null : $pc]);
        }

        $existingForm = Mst_Form_s_w::where('application_id', $applicationId)->first();
        $existingPhoto = TnelbApplicantPhoto::where('application_id', $applicationId)->first();

        if (!$existingForm) {
            return response()->json(['status' => 'error', 'message' => 'Draft not found!'], 404);
        }

            $uploadPhotoRule = (!$existingPhoto || empty($existingPhoto->upload_path))
        ? 'image|mimes:jpg,jpeg,png|max:50'
        : 'nullable|image|mimes:jpg,jpeg,png|max:50';
            $uploadSignRule = 'nullable|image|mimes:jpg,jpeg,png|max:50';
        $uploadSignRule = 'nullable|image|mimes:jpg,jpeg,png|max:50';

        $aadhaarDocRule = (!$existingForm->aadhaar_doc)
            ? 'required|mimes:pdf|max:250'
            : 'nullable|mimes:pdf|max:250';

        $isWorkOptional = in_array($request->form_name, ['W', 'WH'], true);
        $educationLevelRule = ($request->form_name === 'S')
            ? 'required|string|in:DEE,BEE,MEE,AMIE|max:50'
            : 'required|string|max:50';

        $pancardRule = $this->isCompetencyForm($request->form_name)
            ? 'nullable|string|size:10|regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'
            : 'nullable';
        $pancardDocRule = $this->isCompetencyForm($request->form_name)
            ? 'nullable|mimes:pdf|max:250'
            : 'nullable';

        $rules = [
            'login_id'           => 'required|string',
            'applicant_name'     => 'required|string|max:255',
            'fathers_name'       => 'required|string|max:255',
            'applicants_address' => 'required|string|max:500',
            'd_o_b'              => 'required|date',
            'age'                => 'required|integer|min:18|max:100',
            'previously_number'  => 'nullable|string',
            'previously_date'    => 'nullable|date',
            'previously_issue_date' => 'nullable|date',
            'certificate_issue_date' => 'nullable|date',
            'wireman_details'    => 'nullable|string|max:255',
            'aadhaar'            => 'required|string|digits:12',
            'pancard'            => $pancardRule,
            'form_name'          => 'required|string|max:2',
            'license_name'       => 'required|string|max:2',
            'form_id'            => 'required|integer',
            'amount'             => 'required|numeric|min:0',

            'educational_level'    => 'required|array|min:1',
            'educational_level.*'  => $educationLevelRule,
            'institute_name'       => 'required|array|min:1',
            'institute_name.*'     => 'required|string|max:80',
            'month_of_passing'     => 'required|array|min:1',
            'month_of_passing.*'   => 'required|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'year_of_passing'      => 'required|array|min:1',
            'year_of_passing.*'    => 'required|digits:4',
            'certificate_no'       => 'required|array|min:1',
            'certificate_no.*'     => 'required|string|max:20',

            'work_level'           => $isWorkOptional ? 'nullable|array' : 'required|array|min:1',
            'work_level.*'         => $isWorkOptional ? 'nullable|string|max:80' : 'required|string|max:80',
            'experience'           => $isWorkOptional ? 'nullable|array' : 'required|array|min:1',
            'experience.*'         => $isWorkOptional ? 'nullable|numeric|min:0|max:50' : 'required|numeric|min:0|max:50',
            'designation'          => $isWorkOptional ? 'nullable|array' : 'required|array|min:1',
            'designation.*'        => $isWorkOptional ? 'nullable|string|max:80' : 'required|string|max:80',

            'upload_photo'   => $uploadPhotoRule,
            'upload_sign'    => $uploadSignRule,
            'aadhaar_doc'    => $aadhaarDocRule,
            'pancard_doc'    => $pancardDocRule,

            'education_document'   => 'nullable|array',
            'education_document.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
            'existing_document'    => 'nullable|array',
            'existing_document.*'  => 'nullable|string|max:500',

            'work_document'        => 'nullable|array',
            'work_document.*'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
            'existing_work_document' => 'nullable|array',
            'existing_work_document.*' => 'nullable|string|max:500',
        ];

        $messages = [
            'education_document.*.max'    => 'Educational document size permitted only 5 KB to 200 KB.',
            'work_document.*.max'    => 'Experience document size permitted only 5 KB to 200 KB.',
            'month_of_passing.required'     => 'Please add at least one educational qualification.',
            'month_of_passing.*.required'   => 'Month of passing is required.',
            'month_of_passing.*.in'         => 'Month of passing must be a valid month.',
            'd_o_b.after_or_equal' => 'Date of Birth must not be more than 100 years ago.',
            'd_o_b.before_or_equal' => 'Age must be at least 18 years.',
            'educational_level.*.in' => 'For FORM S, only Diploma (EE), B.E (EE), M.E (EE), or A pass in AMIE options are allowed.',
            'pancard.regex' => 'Enter a valid 10-character PAN (e.g. ABCDE1234F).',

        ];

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function ($validator) use ($request, $isWorkOptional) {
            if (!$isWorkOptional) {
                return;
            }

            $levels = $request->work_level ?? [];
            $exps = $request->experience ?? [];
            $designations = $request->designation ?? [];

            $max = max(count($levels), count($exps), count($designations));
            for ($i = 0; $i < $max; $i++) {
                $wl = trim((string)($levels[$i] ?? ''));
                $ex = trim((string)($exps[$i] ?? ''));
                $des = trim((string)($designations[$i] ?? ''));

                $any = ($wl !== '' || $ex !== '' || $des !== '');
                if (!$any) {
                    continue;
                }

                if ($wl === '') {
                    $validator->errors()->add("work_level.$i", 'Work level is required.');
                }
                if ($ex === '') {
                    $validator->errors()->add("experience.$i", 'Experience (in years) is required.');
                }
                if ($des === '') {
                    $validator->errors()->add("designation.$i", 'Designation is required.');
                }
            }
        });
        $validator->after(function ($validator) use ($request, $isWorkOptional) {
            if (! $this->isCompetencyForm($request->form_name ?? null)) {
                return;
            }

            foreach ($request->educational_level ?? [] as $key => $level) {
                if (
                    empty($level)
                    || empty($request->institute_name[$key] ?? null)
                    || empty($request->month_of_passing[$key] ?? null)
                    || empty($request->year_of_passing[$key] ?? null)
                ) {
                    continue;
                }
                $hasFile = $request->hasFile('education_document.'.$key);
                $existing = $request->input('existing_document.'.$key);
                if (! $hasFile && ($existing === null || $existing === '')) {
                    $validator->errors()->add(
                        'education_document.'.$key,
                        'Please choose a PDF and click Upload, or attach the certificate document before submitting.'
                    );
                }
                if ($existing !== null && $existing !== '' && ! $this->isValidCompetencyAjaxDocPath($existing, 'education')) {
                    if ($this->hasValidCompetencyAjaxDocFormat($existing, 'education')) {
                        if (! $hasFile) {
                            $validator->errors()->add(
                                'education_document.'.$key,
                                'The previously uploaded certificate is missing on the server. Please upload the document again.'
                            );
                        }
                } else {
                        $validator->errors()->add('existing_document.'.$key, 'Invalid uploaded document reference.');
                    }
                }
            }

            if ($isWorkOptional) {
                return;
            }

            foreach ($request->work_level ?? [] as $key => $company) {
                if (
                    empty($company)
                    || empty($request->experience[$key] ?? null)
                    || empty($request->designation[$key] ?? null)
                ) {
                    continue;
                }
                $hasFile = $request->hasFile('work_document.'.$key);
                $existing = $request->input('existing_work_document.'.$key);
                if (! $hasFile && ($existing === null || $existing === '')) {
                    $validator->errors()->add(
                        'work_document.'.$key,
                        'Please choose a PDF and click Upload, or attach the experience document before submitting.'
                    );
                }
                if ($existing !== null && $existing !== '' && ! $this->isValidCompetencyAjaxDocPath($existing, 'work')) {
                    if ($this->hasValidCompetencyAjaxDocFormat($existing, 'work')) {
                        if (! $hasFile) {
                            $validator->errors()->add(
                                'work_document.'.$key,
                                'The previously uploaded experience document is missing on the server. Please upload the document again.'
                            );
                        }
                    } else {
                        $validator->errors()->add('existing_work_document.'.$key, 'Invalid uploaded document reference.');
                    }
                }
            }
        });
        $validator->validate();

        DB::beginTransaction();

        $loginId = $request->login_id;

        try {

            $encrypted_aadhaar = Crypt::encryptString($request->aadhaar);
            $encrypted_pancard_update = ($this->isCompetencyForm($request->form_name ?? null) && $request->filled('pancard'))
                ? Crypt::encryptString($request->pancard)
                : $existingForm->pancard;

            $aadhaarFilename = $existingForm ? $existingForm->aadhaar_doc : null;

            if ($request->hasFile('aadhaar_doc')) {
            $file = $request->file('aadhaar_doc');

            $contents = file_get_contents($file->getRealPath());

            $encrypted = Crypt::encrypt($contents);

            $aadhaarFilename = time() . '_' . rand(10000, 9999999) . '.bin';
            $destinationPath = storage_path('app/private_documents');

            if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
            }

            file_put_contents($destinationPath . '/' . $aadhaarFilename, $encrypted);
            }

            $panFilenameUpdate = $existingForm->pan_doc;
            if ($this->isCompetencyForm($request->form_name ?? null) && $request->hasFile('pancard_doc')) {
                $pFile = $request->file('pancard_doc');
                $pContents = file_get_contents($pFile->getRealPath());
                $pEnc = Crypt::encrypt($pContents);
                $panFilenameUpdate = time() . '_' . rand(10000, 9999999) . '_pan.bin';
                $destinationPath = storage_path('app/private_documents');
                if (!is_dir($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                file_put_contents($destinationPath . '/' . $panFilenameUpdate, $pEnc);
            }


            // ✅ Update existing draft
            $existingForm->update([
                'login_id'          => $request->login_id,
                'applicant_name'    => $request->applicant_name,
                'fathers_name'      => $request->fathers_name,
                'applicants_address'=> $request->applicants_address,
                'd_o_b'             => $request->d_o_b,
                'age'               => $request->age,
                'previously_number' => $request->previously_number,
                'previously_date'   => $request->previously_date,
                'previously_issue_date' => $request->previously_issue_date ?: null,
                'wireman_details'   => $request->wireman_details,
                'aadhaar'           => $encrypted_aadhaar,
                'pancard'           => $encrypted_pancard_update,
                'aadhaar_doc'       => $aadhaarFilename,
                'pan_doc'           => $panFilenameUpdate,
                'payment_status'    => 'payment',
                'certificate_no'      => $request->competency_certificate_no,
                'certificate_date'    => $request->certificate_date,
                'certificate_issue_date' => $request->certificate_issue_date ?: null,
                'submitted_date'      => $this->dbNow,
                'updated_at'          => $this->dbNow,
            ]);




            if ($request->has('educational_level')) {

                // ✅ Fetch the last edu_serial from DB
                $lastEdu = Mst_education::whereNotNull('edu_serial')->latest('id')->value('edu_serial');
                $lastNum = $lastEdu ? (int) str_replace('edu_', '', $lastEdu) : 0;
                // education_document is indexed in the form (education_document[0], [1]...)
                // so files line up with educational_level[] indexes.
            
                foreach ($request->educational_level as $key => $level) {
            
                    // ✅ Validate required fields (skip incomplete rows)
                    if (
                        empty($level) ||
                        empty($request->institute_name[$key] ?? null) ||
                        empty($request->month_of_passing[$key] ?? null) ||
                        empty($request->year_of_passing[$key] ?? null)
                    ) {
                        continue;
                    }
            
                    // ✅ File Handling
                    $existingEdu = $request->existing_document[$key] ?? null;
                    $filePath = ($existingEdu !== null && $existingEdu !== ''
                        && $this->isValidCompetencyAjaxDocPath($existingEdu, 'education'))
                        ? $existingEdu
                        : null;

                    if (isset($request->file("education_document")[$key])) {
                        $file = $request->file("education_document")[$key];

                        if ($file && $file->isValid()) {
                            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $destinationPath = public_path('education_document');
                            $file->move($destinationPath, $filename);
                            $filePath = 'education_document/' . $filename;
                        }
                    }

                    // Match by (login_id, application_id, educational_level) so a row added in the
                    // UI with an empty edu_id[] but the same level as an existing row updates that
                    // row instead of inserting a duplicate. This mirrors what update() already does.
                    $lastNum++;
                    $newEduSerial = 'edu_' . $lastNum;

                    Mst_education::updateOrCreate(
                        [
                            'login_id'          => $loginId,
                            'application_id'    => $applicationId,
                            'educational_level' => $level,
                        ],
                        [
                            'institute_name'    => $request->institute_name[$key],
                            'month_passing'     => $request->month_of_passing[$key] ?? null,
                            'year_of_passing'   => $request->year_of_passing[$key],
                            'certificate_no'    => $request->certificate_no[$key] ?? null,
                            'edu_serial'        => $newEduSerial,
                            'upload_document'   => $filePath,
                        ]
                    );
                }
            }
            
            

            if ($this->hasWorkExperiencePayload($request)) {
                // ✅ Fetch last exp_serial from DB once
                $lastExp = Mst_experience::whereNotNull('exp_serial')->latest('id')->value('exp_serial');
                $lastNum = $lastExp ? (int) str_replace('exp_', '', $lastExp) : 0;
            
                foreach ($this->getWorkRowIndexes($request) as $key) {
                    $workRow = $this->mapWorkExperienceRow($request, $key, $request->form_name ?? null);
                    $company = $workRow['company_name'];
                    $expYears = $workRow['experience'];
                    $designation = $workRow['designation'];

                    // ✅ Skip empty rows
                    if (
                        empty($company) ||
                        empty($expYears) ||
                        empty($designation)
                    ) {
                        continue;
                    }
            
                    // ✅ Check if row already exists
                    $workId = $request->work_id[$key] ?? null;
                    $work = $workId ? Mst_experience::find($workId) : null;
            
                    // ✅ Handle file upload
                    $existingW = $request->existing_work_document[$key] ?? null;
                    $filePath = ($existingW !== null && $existingW !== ''
                        && $this->isValidCompetencyAjaxDocPath($existingW, 'work'))
                        ? $existingW
                        : null;
                    if ($request->hasFile("work_document.$key")) {
                        $file = $request->file("work_document")[$key];
                        if ($file && $file->isValid()) {
                            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $destinationPath = public_path('work_experience');
                            $file->move($destinationPath, $filename);
                            $filePath = 'work_experience/' . $filename;
                        }
                    }
            
                    if ($work) {
                        // 🔹 UPDATE existing record
                        $work->update([
                            'emp_type'        => $workRow['emp_type'],
                            'emp_cate'        => $workRow['emp_cate'],
                            'intimation_date' => $workRow['intimation_date'],
                            'from_date'       => $workRow['from_date'],
                            'to_date'         => $workRow['to_date'],
                            'total_exp'       => $workRow['total_exp'],
                            'designation'     => $designation,
                            'upload_document' => $filePath,
                        ]);
                    } else {
                        // 🔹 INSERT new record
                        $lastNum++;
                        $newExpSerial = 'exp_' . $lastNum;

                        Mst_experience::create([
                            'login_id'        => $loginId,
                            'emp_type'        => $workRow['emp_type'],
                            'emp_cate'        => $workRow['emp_cate'],
                            'intimation_date' => $workRow['intimation_date'],
                            'from_date'       => $workRow['from_date'],
                            'to_date'         => $workRow['to_date'],
                            'total_exp'       => $workRow['total_exp'],
                            'designation'     => $designation,
                            'application_id'  => $applicationId,
                            'exp_serial'      => $newExpSerial,
                            'upload_document' => $filePath,
                        ]);
                    }
                }
            }

            // 🔹 Save Photo if New Upload (with robust upload checks)
            if ($request->hasFile('upload_photo')) {
                $photoFile = $request->file('upload_photo');

                if (!$photoFile->isValid()) {
                    Log::warning('Photo upload failed in draft_update', [
                        'application_id' => $applicationId,
                        'login_id'       => $loginId,
                        'client_name'    => $photoFile->getClientOriginalName(),
                        'error_code'     => $photoFile->getError(),
                        'error_message'  => $photoFile->getErrorMessage(),
                    ]);
                    throw new \RuntimeException('Photo upload failed: ' . $photoFile->getErrorMessage());
                }

                // Enforce maximum 50 KB (sizes are in bytes)
                $sizeKb = $photoFile->getSize() / 1024;
                if ($sizeKb > 50) {
                    throw new \RuntimeException('Photo size permitted up to 50 KB.');
                }

                $photoName = 'user_' . time() . '.' . $photoFile->getClientOriginalExtension();
                $photoFile->move(public_path('attached_documents'), $photoName);

                TnelbApplicantPhoto::updateOrCreate(
                    ['application_id' => $applicationId],
                    [
                        'login_id'   => $loginId,
                        'upload_path'=> 'attached_documents/' . $photoName,
                    ]
                );
            }

            // 🔹 Save Signature if New Upload (with robust upload checks)
            if ($request->hasFile('upload_sign')) {
                $signFile = $request->file('upload_sign');

                if (!$signFile->isValid()) {
                    Log::warning('Signature upload failed in draft_update', [
                        'application_id' => $applicationId,
                        'login_id'       => $loginId,
                        'client_name'    => $signFile->getClientOriginalName(),
                        'error_code'     => $signFile->getError(),
                        'error_message'  => $signFile->getErrorMessage(),
                    ]);
                    throw new \RuntimeException('Signature upload failed: ' . $signFile->getErrorMessage());
                }

                // Enforce maximum 50 KB (sizes are in bytes)
                $signSizeKb = $signFile->getSize() / 1024;
                if ($signSizeKb > 50) {
                    throw new \RuntimeException('Signature size permitted up to 50 KB.');
                }

                $signName = 'sign_' . time() . '.' . $signFile->getClientOriginalExtension();
                $signFile->move(public_path('attached_documents'), $signName);

                TnelbApplicantsSign::updateOrCreate(
                    ['application_id' => $applicationId],
                    [
                        'login_id'     => $loginId,
                        'uploaded_doc' => 'attached_documents/' . $signName,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message'=> 'Draft updated and submitted successfully!',
                'application_id' => $applicationId,
                'applicantName' => $existingForm->applicant_name
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message'=> 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit corrections for a returned (QU) application. Runs same update as draft_update,
     * then sets status back to P and marks queries as resolved.
     */
    public function submitReturnedApplication(Request $request, $appl_id)
    {
        if (!Auth::check()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        $app = DB::table('tnelb_application_tbl')->where('application_id', $appl_id)->first();
        if (!$app) {
            return response()->json(['status' => 'error', 'message' => 'Application not found.'], 404);
        }
        if ((string) $app->status !== 'QU') {
            return response()->json(['status' => 'error', 'message' => 'This application is not under query.'], 400);
        }

        $loginId = session('login_id');
        if (!$loginId || (string) $app->login_id !== (string) $loginId) {
            return response()->json(['status' => 'error', 'message' => 'You can only submit corrections for your own application.'], 403);
        }

        $response = $this->draft_update($request, $appl_id);
        $data = json_decode($response->getContent(), true);

        if (isset($data['status']) && $data['status'] === 'success') {
            // Preserve original payment_status (draft_update sets it to 'payment'; do not change for returned-applicant submit)
            $updateData = [
                'status'       => 'RE',
                'processed_by' => 'AP',
                'updated_at'   => $this->dbNow,
                'payment_status' => $app->payment_status,
            ];
            DB::table('tnelb_application_tbl')
                ->where('application_id', $appl_id)
                ->update($updateData);

            DB::table('tnelb_query_applicable')
                ->where('application_id', $appl_id)
                ->where('query_status', 'P')
                ->update(['query_status' => 'R', 'updated_at' => $this->dbNow]);

            $supervisorRoleId = RoleHelper::supervisorWorkflowRoleId(Auth::user());

            if ($supervisorRoleId) {
                SupervisorModel::create([
                    'application_id' => $appl_id,
                    'appl_status'    => 'RE',
                    'processed_by'   => 'AP',
                    'forwarded_to'   => $supervisorRoleId,
                    'role_id'        => $supervisorRoleId,
                    'is_verified'    => 'Yes',
                    'query_status'   => null,
                    'remarks'        => 'Resubmitted by applicant after query.',
                    'queries'        => null,
                    'raised_by'      => null,
                    'created_at'     => $this->dbNow,
                ]);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Application Submitted',
                'redirect' => route('dashboard'),
            ]);
        }

        return $response;
    }


    public function delete_education(Request $request)
    {
        try {
            $id = $request->input('edu_id'); // Get edu_id from AJAX request

            $education = Mst_education::find($id);

            if (!$education) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Education record not found!'
                ], 404);
            }

            // Delete uploaded file if it exists
            if (!empty($education->upload_document)) {
                $filePath = public_path($education->upload_document);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $education->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Education record deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete record!',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function delete_experience(Request $request)
    {
        try {
            $id = $request->input('exp_id'); // Get edu_id from AJAX request

            $experience = Mst_experience::find($id);

            if (!$experience) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Experience record not found!'
                ], 404);
            }

            // Delete uploaded file if it exists
            if (!empty($experience->upload_document)) {
                $filePath = public_path($experience->upload_document);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $experience->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Experience record deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete record!',
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function draft_submit(Request $request, $id = null)
    {
        
        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);

        if ($this->isCompetencyForm($request->form_name ?? null) && $request->filled('pancard')) {
            $request->merge([
                'pancard' => strtoupper(preg_replace('/\s+/', '', $request->pancard)),
            ]);
        }

        $applicationId = $id;
        $existingForm = Mst_Form_s_w::where('application_id', $applicationId)->first();

        $existingPhoto = TnelbApplicantPhoto::where('application_id', $applicationId)->first();

        if (!$existingForm && $applicationId) {
            return response()->json(['status' => 'error', 'message' => 'Draft not found!'], 404);
        }

        $uploadPhotoRule = (!$existingPhoto || empty($existingPhoto->upload_path))
            ? 'image|mimes:jpg,jpeg,png|max:50'
            : 'nullable|image|mimes:jpg,jpeg,png|max:50';

        // Signature is optional for draft submit; file is validated only if present
        $uploadSignRule = 'nullable|image|mimes:jpg,jpeg,png|max:50';

        $aadhaarDocRule = ($existingForm && !$existingForm->aadhaar_doc)
            ? 'mimes:pdf|max:250'
            : 'nullable|mimes:pdf|max:250';

            $educationLevelRuleDraft = ($request->form_name === 'S')
                ? 'nullable|string|in:DEE,BEE,MEE,AMIE|max:50'
                : 'nullable|string|max:50';

            $request->validate([
                'login_id'           => 'nullable|string',
                'applicant_name'     => 'nullable|string|max:255',
                'fathers_name'       => 'nullable|string|max:255',
                'applicants_address' => 'nullable|string|max:500',
                'd_o_b'              => 'nullable|date',
                'age'                => 'nullable|integer|min:18|max:100',
                'previously_number'  => 'nullable|string',
                'previously_date'    => 'nullable|date',
                'previously_issue_date' => 'nullable|date',
                'certificate_issue_date' => 'nullable|date',
                'wireman_details'    => 'nullable|string|max:255',
                'form_name'          => 'nullable|string|max:2',
                'license_name'       => 'nullable|string|max:2',
                'form_id'            => 'nullable|integer',
                'amount'             => 'nullable|numeric|min:0',
    
                'educational_level'    => 'nullable|array|min:1',
                'educational_level.*'  => $educationLevelRuleDraft,
                'institute_name'       => 'nullable|array|min:1',
                'institute_name.*'     => 'nullable|string|max:80',
                'year_of_passing'      => 'nullable|array|min:1',
                'year_of_passing.*'    => 'nullable',
                'certificate_no'       => 'nullable|array|min:1',
                'certificate_no.*'     => 'nullable|string|max:20',
    
    
                'upload_photo'   => $uploadPhotoRule,
                'upload_sign'    => $uploadSignRule,
                'aadhaar_doc'    => $aadhaarDocRule,
    
                'education_document.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
    
                'work_document'        => 'nullable|array',
                'work_document.*'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
            ],[

            // education arrays
            'education_document.*'    => 'Educational document size permitted only 5 KB to 200 KB.',
            'work_document.*.max'    => 'Experience document size permitted only 5 KB to 200 KB.',


            'educational_level.*.string'    => 'Educational level must be a valid string.',
            'educational_level.*.max'       => 'Educational level may not be greater than 50 characters.',

            'institute_name.*.string'       => 'Institute name must be a valid string.',
            'institute_name.*.max'          => 'Institute name may not be greater than 80 characters.',


            'certificate_no.*.string'       => 'Certificate No must be a valid text value.',
            'certificate_no.*.max'          => 'Certificate No may not be greater than 20 characters.',

            // work experience arrays
            'work_level.*.string'           => 'Work level must be a valid string.',
            'work_level.*.max'              => 'Work level may not be greater than 80 characters.',

            'experience.*.numeric'          => 'Experience must be a valid number.',
            'experience.*.min'              => 'Experience cannot be negative.',
            'experience.*.max'              => 'Experience may not exceed 50 years.',

            'designation.*.string'          => 'Designation must be a valid string.',
            'designation.*.max'             => 'Designation may not be greater than 80 characters.',

            'aadhaar.digits' => 'Aadhaar number should be 12 digits.',
            'educational_level.*.in' => 'For FORM S, only Diploma (EE), B.E (EE), M.E (EE), or A pass in AMIE options are allowed.',

        ]);

        $action = $request->form_action; // "draft" or "submit"
        $loginId = $request->login_id;
        $appl_type = $request->appl_type ?? '';

        DB::beginTransaction();

        try {
            // 🔹 Find existing application if $id is passed
            
            $form = $id ? Mst_Form_s_w::where('application_id', $id)->first() : null;
            
            // 🔹 Determine Application ID
            if ($form) {
                $applicationId = $form->application_id;
            } else {

                // Create New Application ID
                $appl_type = $request->appl_type ?? '';
                
                $lastApplication = Mst_Form_s_w::latest('id')->value('application_id');
                if ($lastApplication) {
                    $lastNumber = (int) substr($lastApplication, -7);
                    $applicationId = $request->form_name . $request->license_name . date('y') . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
                } else {
                    $applicationId = $request->form_name . $request->license_name . date('y') . '1111111';
                }

            }



            $encrypted_aadhaar = Crypt::encryptString($request->aadhaar);
        
            
            if ($request->hasFile('aadhaar_doc')) {
                $file = $request->file('aadhaar_doc');

                $contents = file_get_contents($file->getRealPath());

                $encrypted = Crypt::encrypt($contents);

                $aadhaarFilename = time() . '_' . rand(10000, 9999999) . '.bin';
                $destinationPath = storage_path('app/private_documents');

                if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                }

                file_put_contents($destinationPath . '/' . $aadhaarFilename, $encrypted);
            }elseif ($request->input('aadhaar_doc_removed') == "1") {
                // ✅ Removed but not replaced
                $aadhaarFilename = null;
            }else {
                // ✅ Keep the old one
                $aadhaarFilename = $form?->aadhaar_doc ?? null;
            }

            $encrypted_pancard = null;
            $panFilename = null;
            if ($this->isCompetencyForm($request->form_name ?? null)) {
                if ($request->filled('pancard')) {
                    $encrypted_pancard = Crypt::encryptString($request->pancard);
                } elseif ($form && $form->pancard) {
                    $encrypted_pancard = $form->pancard;
                }
                if ($request->hasFile('pancard_doc')) {
                    $pFile = $request->file('pancard_doc');
                    $pContents = file_get_contents($pFile->getRealPath());
                    $pEnc = Crypt::encrypt($pContents);
                    $panFilename = time() . '_' . rand(10000, 9999999) . '_pan.bin';
                    $destinationPath = storage_path('app/private_documents');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    file_put_contents($destinationPath . '/' . $panFilename, $pEnc);
                } else {
                    $panFilename = $form?->pan_doc ?? null;
                }
            }
           
             
            // 🔹 Prepare Data
            $data = [
                'login_id'          => $loginId,
                'applicant_name'    => $request->applicant_name ?? $request->Applicant_Name,
                'fathers_name'      => $request->fathers_name ?? $request->Fathers_Name,
                'applicants_address'=> $request->applicants_address,
                'd_o_b'             => $request->d_o_b ?? null,
                'age'               => $request->age,
                'status'            => 'P', // Pending (for both draft/submit)
                'previously_number' => $request->previously_number ?? null,
                'previously_date'   => $request->previously_date ?? null,
                'previously_issue_date' => $request->previously_issue_date ?: null,
                'wireman_details'   => $request->wireman_details,
                'form_name'         => $request->form_name,
                'form_id'           => $request->form_id,
                'license_name'      => $request->license_name,
                'aadhaar'           => $encrypted_aadhaar ?? null,
                'pancard'           => $encrypted_pancard,
                'appl_type'         => $request->appl_type,
                'license_number'    => $request->license_number,
                'payment_status'    => $action === 'draft' ? 'draft' : 'payment',
                'aadhaar_doc'         => $aadhaarFilename,
                'pan_doc'             => $panFilename,
                'certificate_no'      => $request->competency_certificate_no ?? null,
                'certificate_date'   => $request->certificate_date ?? null,
                'certificate_issue_date' => $request->certificate_issue_date ?: null,
                'application_id'    => $applicationId,
                'cert_verify'    => $request->cert_verify ?? '0',
                'license_verify'    => $request->l_verify ?? '0',
                'old_application'=> $form->old_application ?? null
            ];



            // 🔹 Insert or Update
            if ($form) {
                $data['updated_at'] = $this->dbNow;
                $form->update($data); // ✅ Update existing
            } else {
                $data['created_at'] = $this->dbNow;
                $form = Mst_Form_s_w::create($data); // ✅ Insert new
            }


            if ($request->has('educational_level')) {

                // ✅ Fetch the last edu_serial from DB
                $lastEdu = Mst_education::whereNotNull('edu_serial')->latest('id')->value('edu_serial');
                $lastNum = $lastEdu ? (int) str_replace('edu_', '', $lastEdu) : 0;
            
                foreach ($request->educational_level as $key => $level) {
            
                    if (
                        empty($level) &&
                        empty($request->institute_name[$key] ?? null) &&
                        empty($request->year_of_passing[$key] ?? null) &&
                        empty($request->certificate_no[$key] ?? null)
                    ) {
                        continue; // skip empty row
                    }
            
                    $eduId = $request->edu_id[$key] ?? null;
                    $education = $eduId ? Mst_education::find($eduId) : null;
            
                    // ✅ Check if file is removed via JS (row-aligned flag)
                    $isFileRemoved = isset($request->removed_document[$key]) && $request->removed_document[$key] == '1';

                    // ✅ File Handling
                    // Priority order:
                    // 1) If a new file is uploaded -> replace (even if user clicked "Remove" first)
                    // 2) Else if removed -> null
                    // 3) Else keep existing_document
                    $filePath = null;

                    // Case 1: New file uploaded (highest priority)
                    // Try keyed access first; if missing due to re-indexing, fall back to next queued upload.
                    $directFile = $request->file("education_document.$key");
                    $file = ($directFile && $directFile->isValid()) ? $directFile : null;

                    if ($file) {
                        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                        $destinationPath = public_path('education_document');
                        $file->move($destinationPath, $filename);
                        $filePath = 'education_document/' . $filename;
                    }

                    // Case 2: File removed explicitly by user (only if no replacement uploaded)
                    elseif ($isFileRemoved) {
                        $filePath = null;
                    }

                    // Case 3: No new file, not removed, keep existing
                    elseif (!empty($request->existing_document[$key] ?? null)) {
                        $filePath = $request->existing_document[$key];
                    }
            
                    // Normalize month_of_passing: trim, accept "01"-"12" or "1"-"12",
                    // map to int 1-12, otherwise treat as missing.
                    $monthRaw = $request->month_of_passing[$key] ?? null;
                    $monthVal = null;
                    if ($monthRaw !== null && $monthRaw !== '') {
                        $m = (int) ltrim((string) $monthRaw, '0');
                        if ($m >= 1 && $m <= 12) {
                            $monthVal = $m;
                        }
                    }

                    // ✅ Update by primary id when the form sends edu_id[] (edit flows), else upsert
                    // by natural key so repeated "Save as Draft" after preview does not insert copies
                    // (apply WH/S/W do not post edu_id until they are edit views).
                    if ($education) {
                        // Defensive: keep existing values if request-side index is missing
                        // (avoids silently nulling previously-saved data on partial drafts).
                        $education->update([
                            'educational_level' => $level ?? $education->educational_level,
                            'institute_name'    => ($request->institute_name[$key] ?? null) !== null && $request->institute_name[$key] !== ''
                                ? $request->institute_name[$key]
                                : $education->institute_name,
                            'month_passing'     => $monthVal !== null ? $monthVal : $education->month_passing,
                            'year_of_passing'   => ($request->year_of_passing[$key] ?? null) !== null
                                && $request->year_of_passing[$key] !== ''
                                && $request->year_of_passing[$key] !== '0'
                                ? $request->year_of_passing[$key]
                                : $education->year_of_passing,
                            'certificate_no'    => ($request->certificate_no[$key] ?? null) !== null && $request->certificate_no[$key] !== ''
                                ? $request->certificate_no[$key]
                                : $education->certificate_no,
                            'upload_document'   => $filePath,
                        ]);
                    } else {
                        $upsertAttrs = [
                            'login_id'          => $loginId,
                            'application_id'    => $applicationId,
                            'educational_level' => $level,
                        ];

                        $existingByKey = Mst_education::where($upsertAttrs)->first();

                        if ($existingByKey && $existingByKey->edu_serial) {
                            $newEduSerial = $existingByKey->edu_serial;
                        } else {
                            $lastNum++;
                            $newEduSerial = 'edu_' . $lastNum;
                        }

                        $uploadToStore = $filePath;
                        if ($uploadToStore === null && ! $isFileRemoved && $existingByKey && $existingByKey->upload_document) {
                            $uploadToStore = $existingByKey->upload_document;
                        }

                        Mst_education::updateOrCreate(
                            $upsertAttrs,
                            [
                                'institute_name'    => $request->institute_name[$key],
                                'month_passing'     => $monthVal,
                                'year_of_passing'   => $request->year_of_passing[$key],
                                'certificate_no'    => $request->certificate_no[$key] ?? null,
                                'edu_serial'        => $newEduSerial,
                                'upload_document'   => $uploadToStore,
                            ]
                        );
                    }
                }
            }
            

            if ($this->hasWorkExperiencePayload($request)) {
                // ✅ Fetch last exp_serial from DB once
                $lastExp = Mst_experience::whereNotNull('exp_serial')->latest('id')->value('exp_serial');
                $lastNum = $lastExp ? (int) str_replace('exp_', '', $lastExp) : 0;
            
                foreach ($this->getWorkRowIndexes($request) as $key) {
                    $workRow = $this->mapWorkExperienceRow($request, $key, $request->form_name ?? null);
                    $company = $workRow['company_name'];
                    $expYears = $workRow['experience'];
                    $designation = $workRow['designation'];

                    // ✅ Skip empty rows
                    if (
                        empty($company) &&
                        empty($expYears) &&
                        empty($designation)
                    ) {
                        continue;
                    }
            
                    // ✅ Check if row already exists
                    $workId = $request->work_id[$key] ?? null;
                    $work = $workId ? Mst_experience::find($workId) : null;
            
                    // ✅ File Handling
                    // A new valid upload must be processed even when "Remove" was clicked first (replace flow).
                    $filePath = null;
                    $isFileRemoved = isset($request->removed_document_work[$key]) && $request->removed_document_work[$key] == '1';
            
                    if (isset($request->file('work_document')[$key])) {
                        $file = $request->file('work_document')[$key];
            
                        if ($file && $file->isValid()) {
                            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            $destinationPath = public_path('work_experience');
                            $file->move($destinationPath, $filename);
                            $filePath = 'work_experience/' . $filename;
                        }
                    }
            
                    if ($work) {
                        // 🔹 UPDATE existing record
                        $work->update([
                            'emp_type'        => $workRow['emp_type'],
                            'emp_cate'        => $workRow['emp_cate'],
                            'intimation_date' => $workRow['intimation_date'],
                            'from_date'       => $workRow['from_date'],
                            'to_date'         => $workRow['to_date'],
                            'total_exp'       => $workRow['total_exp'],
                            'designation'     => $designation ?: null,
                            'upload_document' => $filePath !== null
                                ? $filePath
                                : ($isFileRemoved ? null : $work->upload_document),
                        ]);
                    } else {
                        // 🔹 INSERT new record
                        $lastNum++;
                        $newExpSerial = 'exp_' . $lastNum;
            
                        Mst_experience::create([
                            'login_id'        => $loginId,
                            'emp_type'        => $workRow['emp_type'],
                            'emp_cate'        => $workRow['emp_cate'],
                            'intimation_date' => $workRow['intimation_date'],
                            'from_date'       => $workRow['from_date'],
                            'to_date'         => $workRow['to_date'],
                            'total_exp'       => $workRow['total_exp'],
                            'designation'     => $designation,
                            'application_id'  => $applicationId,
                            'exp_serial'      => $newExpSerial,
                            'upload_document' => $filePath,
                        ]);
                    }
                }
            }
            

            // 🔹 Save Photo if New Upload
            if ($request->hasFile('upload_photo')) {
                $photoName = 'user_' . time() . '.' . $request->file('upload_photo')->getClientOriginalExtension();
                $request->file('upload_photo')->move(public_path('attached_documents'), $photoName);

                TnelbApplicantPhoto::updateOrCreate(
                    ['application_id' => $applicationId],
                    [
                        'login_id'    => $loginId,
                        'upload_path' => 'attached_documents/' . $photoName,
                    ]
                );
            }

            // 🔹 Save Signature if New Upload (for all forms, including Form W)
            if ($request->hasFile('upload_sign')) {
                $signFile = $request->file('upload_sign');
                $signName = 'sign_' . time() . '.' . $signFile->getClientOriginalExtension();
                $signFile->move(public_path('attached_documents'), $signName);

                TnelbApplicantsSign::updateOrCreate(
                    ['application_id' => $applicationId],
                    [
                        'login_id'     => $loginId,
                        'uploaded_doc' => 'attached_documents/' . $signName,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $action === 'draft' ? 'Draft saved successfully!' : 'Form submitted successfully!',
                'application_id' => $applicationId,
                'applicantName' => $form->applicant_name
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function draft_renewal_submit(Request $request, $id = null)
    {

        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);

        if ($this->isCompetencyForm($request->form_name ?? null) && $request->filled('pancard')) {
            $request->merge([
                'pancard' => strtoupper(preg_replace('/\s+/', '', $request->pancard)),
            ]);
        }

        $applicationId = $id;

        $existingForm  = Mst_Form_s_w::where('application_id', $applicationId)->first();
        $existingPhoto = TnelbApplicantPhoto::where('application_id', $applicationId)->first();

        if (!$existingForm && $applicationId) {
            return response()->json(['status' => 'error', 'message' => 'Draft not found!'], 404);
        }

        $uploadPhotoRule = (!$existingPhoto || empty($existingPhoto->upload_path))
            ? 'image|mimes:jpg,jpeg,png|max:50'
            : 'nullable|image|mimes:jpg,jpeg,png|max:50';
        $uploadSignRule = 'nullable|image|mimes:jpg,jpeg,png|max:50';
        $uploadSignRule = 'nullable|image|mimes:jpg,jpeg,png|max:50';

        $aadhaarDocRule = ($existingForm && !$existingForm->aadhaar_doc)
            ? 'mimes:pdf|max:250'
            : 'nullable|mimes:pdf|max:250';

        $educationLevelRuleDraft = ($request->form_name === 'S')
            ? 'nullable|string|in:DEE,BEE,MEE,AMIE|max:50'
            : 'nullable|string|max:50';
      

        $request->validate([
            'login_id'           => 'nullable|string',
            'applicant_name'     => 'nullable|string|max:255',
            'fathers_name'       => 'nullable|string|max:255',
            'applicants_address' => 'nullable|string|max:500',
            'd_o_b'              => 'nullable|date',
            'age'                => 'nullable|integer|min:18|max:100',
            'previously_number'  => 'nullable|string',
            'previously_date'    => 'nullable|date',
            'previously_issue_date' => 'nullable|date',
            'certificate_issue_date' => 'nullable|date',
            'wireman_details'    => 'nullable|string|max:255',
            'form_name'          => 'nullable|string|max:2',
            'license_name'       => 'nullable|string|max:2',
            'form_id'            => 'nullable|integer',
            'amount'             => 'nullable|numeric|min:0',

            'educational_level'    => 'nullable|array|min:1',
            'educational_level.*'  => $educationLevelRuleDraft,
            'institute_name'       => 'nullable|array|min:1',
            'institute_name.*'     => 'nullable|string|max:80',
            'month_of_passing'     => 'nullable|array',
            'month_of_passing.*'   => 'nullable|in:01,02,03,04,05,06,07,08,09,10,11,12,1,2,3,4,5,6,7,8,9,10,11,12',
            'year_of_passing'      => 'nullable|array|min:1',
            'year_of_passing.*'    => 'nullable',
            'certificate_no'       => 'nullable|array|min:1',
            'certificate_no.*'     => 'nullable|string|max:20',
            'competency_certificate_no' => 'nullable|string|max:80',

            'upload_photo'   => $uploadPhotoRule,
            'upload_sign'    => $uploadSignRule,
            'aadhaar_doc'    => $aadhaarDocRule,

            'education_document.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',

            'work_document'        => 'nullable|array',
            'work_document.*'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
        ],[
            'education_document.*'   => 'Educational document size permitted only 5 KB to 200 KB.',
            'work_document.*.max'    => 'Experience document size permitted only 5 KB to 200 KB.',

            'educational_level.*.string' => 'Educational level must be a valid string.',
            'educational_level.*.max'    => 'Educational level may not be greater than 50 characters.',
            'institute_name.*.string'    => 'Institute name must be a valid string.',
            'institute_name.*.max'       => 'Institute name may not be greater than 80 characters.',
            'certificate_no.*.string'    => 'Certificate No must be a valid text value.',
            'certificate_no.*.max'       => 'Certificate No may not be greater than 20 characters.',

            'work_level.*.string'        => 'Work level must be a valid string.',
            'work_level.*.max'           => 'Work level may not be greater than 80 characters.',
            'experience.*.numeric'       => 'Experience must be a valid number.',
            'experience.*.min'           => 'Experience cannot be negative.',
            'experience.*.max'           => 'Experience may not exceed 50 years.',
            'designation.*.string'       => 'Designation must be a valid string.',
            'designation.*.max'          => 'Designation may not be greater than 80 characters.',

            'aadhaar.digits' => 'Aadhaar number should be 12 digits.',
            'educational_level.*.in' => 'For FORM S, only Diploma (EE), B.E (EE), M.E (EE), or A pass in AMIE options are allowed.',
        ]);

        $action    = $request->form_action; // "draft" or "submit"
        $loginId   = $request->login_id;
        $appl_type = $request->appl_type ?? 'R'; // ensure renewal
        $nowTs     = $this->dbNow;

        DB::beginTransaction();

        try {
            // find current renewal (editing same renewal draft) or create fresh ID
            $form = $id ? Mst_Form_s_w::where('application_id', $id)
                ->where('appl_type','R')
                ->first() : null;

            if ($form) {
                $applicationId = $form->application_id; // keep same ID while editing the renewal draft
            } else {
                // create new renewal application_id
                $lastApplication = Mst_Form_s_w::latest('id')->value('application_id');
                if ($lastApplication) {
                    $lastNumber    = (int) substr($lastApplication, -7);
                    $applicationId = $appl_type . ($request->form_name ?? '') . ($request->license_name ?? '')
                                . date('y') . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
                } else {
                    $applicationId = $appl_type . ($request->form_name ?? '') . ($request->license_name ?? '')
                                . date('y') . '1111111';
                }
            }

            // encrypt aadhaar
            $encrypted_aadhaar = $request->aadhaar ? Crypt::encryptString($request->aadhaar) : null;

            // helper to store encrypted private docs (aadhaar)
            $storeEncryptedPrivate = function(\Illuminate\Http\UploadedFile $file = null) {
                if (!$file) return null;
                $contents   = file_get_contents($file->getRealPath());
                $encrypted  = Crypt::encrypt($contents);
                $filename   = time() . '_' . rand(10000, 9999999) . '.bin';
                $dest       = storage_path('app/private_documents');
                if (!is_dir($dest)) mkdir($dest, 0755, true);
                file_put_contents($dest . '/' . $filename, $encrypted);
                return $filename;
            };

            // Aadhaar file: new / removed / keep (fallback to old app if needed)
            if ($request->hasFile('aadhaar_doc')) {
                $aadhaarFilename = $storeEncryptedPrivate($request->file('aadhaar_doc'));
            } elseif ($request->input('aadhaar_doc_removed') == "1") {
                $aadhaarFilename = null;
            } else {
                // keep existing (prefer current renewal draft else fall back to $id)
                $aadhaarFilename = $form?->aadhaar_doc
                    ?? Mst_Form_s_w::where('application_id', $id)->value('aadhaar_doc');
            }

            $encrypted_pancard_renewal = null;
            $panFilenameRenewal = null;
            if ($this->isCompetencyForm($request->form_name ?? null)) {
                if ($request->filled('pancard')) {
                    $encrypted_pancard_renewal = Crypt::encryptString($request->pancard);
                } elseif ($form && $form->pancard) {
                    $encrypted_pancard_renewal = $form->pancard;
                }
                if ($request->hasFile('pancard_doc')) {
                    $panFilenameRenewal = $storeEncryptedPrivate($request->file('pancard_doc'));
                } else {
                    $panFilenameRenewal = $form?->pan_doc
                        ?? Mst_Form_s_w::where('application_id', $id)->value('pan_doc');
                }
            }

            // assemble master application data (APPLICATION ENTRY ✅)
            $data = [
                'login_id'           => $loginId,
                'applicant_name'     => $request->applicant_name ?? $request->Applicant_Name,
                'fathers_name'       => $request->fathers_name ?? $request->Fathers_Name,
                'applicants_address' => $request->applicants_address,
                'd_o_b'              => $request->d_o_b ?? null,
                'age'                => $request->age,
                'status'             => 'P',
                'previously_number'  => $request->previously_number ?? null,
                'previously_date'    => $request->previously_date ?? null,
                'previously_issue_date' => $request->previously_issue_date ?: null,
                'wireman_details'    => $request->wireman_details,
                'form_name'          => $request->form_name,
                'form_id'            => $request->form_id,
                'license_name'       => $request->license_name,
                'aadhaar'            => $encrypted_aadhaar,
                'pancard'            => $encrypted_pancard_renewal,
                'appl_type'          => $appl_type,           // ensure 'R'
                'license_number'     => $request->license_number,
                'payment_status'     => 'draft',
                'aadhaar_doc'        => $aadhaarFilename ?? null,
                'pan_doc'            => $panFilenameRenewal,
                'certificate_no'     => $request->competency_certificate_no ?? null,
                'certificate_date'   => $request->certificate_date ?? null,
                'certificate_issue_date' => $request->certificate_issue_date ?: null,
                'application_id'     => $applicationId,
                'cert_verify'        => $request->cert_verify ?? '0',
                'license_verify'     => $request->l_verify ?? '0',
                'old_application'    => $form ? $form->old_application : $id,
                'submitted_date'     => $nowTs,
                'updated_at'         => $nowTs,
            ];

            // insert or update master application
            if ($form) {
                $form->update($data);      // editing same renewal draft
            } else {
                $data['created_at'] = $nowTs;
                $form = Mst_Form_s_w::create($data); // brand-new renewal application entry ✅
            }


            $type_of_apps = MstLicence::where('form_code', $form->form_name)
            ->select('licence_name')
            ->first();



            // var_dump($type_of_apps->licence_name);die;

            // -------------------------
            // ALWAYS-INSERT Education  ✅
            // -------------------------
            if ($request->has('educational_level')) {
                $lastEdu = Mst_education::whereNotNull('edu_serial')->latest('id')->value('edu_serial');
                $lastNum = $lastEdu ? (int) str_replace('edu_', '', $lastEdu) : 0;

                foreach ($request->educational_level as $key => $level) {
                    $levelName  = $level ?? null;
                    $institute  = $request->institute_name[$key] ?? null;
                    $monthRaw   = $request->month_of_passing[$key] ?? null;
                    $month      = null;
                    if ($monthRaw !== null && $monthRaw !== '') {
                        $monthInt = (int) trim((string) $monthRaw);
                        $month = ($monthInt >= 1 && $monthInt <= 12) ? $monthInt : null;
                    }
                    $year       = $request->year_of_passing[$key] ?? null;
                    $certificateNo = $request->certificate_no[$key] ?? null;

                    $removed    = isset($request->removed_document[$key]) && $request->removed_document[$key] == '1';
                    $newDoc     = (isset($request->file('education_document')[$key]) && $request->file('education_document')[$key]->isValid())
                                    ? $request->file('education_document')[$key]
                                    : null;
                    $oldDoc     = $request->existing_document[$key] ?? null;

                    // final doc path: removed => null, new => stored, else keep old path (view mode)
                    if ($removed) {
                        $finalDoc = null;
                    } elseif ($newDoc) {
                        $filename = time() . '_' . uniqid() . '.' . $newDoc->getClientOriginalExtension();
                        $newDoc->move(public_path('education_document'), $filename);
                        $finalDoc = 'education_document/' . $filename;
                    } else {
                        $finalDoc = $oldDoc ?: null;
                    }

                    // skip only if EVERYTHING is empty (avoid junk rows)
                    $hasAnyData = !empty($levelName) || !empty($institute) || !empty($month) || !empty($year) || !empty($certificateNo) || !empty($finalDoc);
                    if (!$hasAnyData) continue;

                    $lastNum++;
                    $newSerial = 'edu_' . $lastNum;

         
                    Mst_education::updateOrCreate(
                        [
                            'login_id'          => $loginId,
                            'application_id'    => $applicationId,
                            'educational_level' => $levelName,
                        ],
                        [
                            'institute_name'    => $institute,
                            'month_passing'     => $month,
                            'year_of_passing'   => $year,
                            'certificate_no'    => $certificateNo,
                            'upload_document'   => $finalDoc,
                            'edu_serial'        => $newSerial,
                        ]
                    );
                }
            }

            // -------------------------
            // ALWAYS-INSERT Work  ✅
            // -------------------------
            if ($this->hasWorkExperiencePayload($request)) {
                $lastExp = Mst_experience::whereNotNull('exp_serial')->latest('id')->value('exp_serial');
                $lastNum = $lastExp ? (int) str_replace('exp_', '', $lastExp) : 0;

                foreach ($this->getWorkRowIndexes($request) as $key) {
                    $workRow = $this->mapWorkExperienceRow($request, $key, $request->form_name ?? null);
                    $companyName = $workRow['company_name'] ?: null;
                    $expYears    = $workRow['experience'] ?: null;
                    $designation = $workRow['designation'] ?: null;

                    $removed     = isset($request->removed_document_work[$key]) && $request->removed_document_work[$key] == '1';
                    $newDoc      = (isset($request->file('work_document')[$key]) && $request->file('work_document')[$key]->isValid())
                                    ? $request->file('work_document')[$key]
                                    : null;
                    $oldDoc      = $request->existing_work_document[$key] ?? null;

                    if ($removed) {
                        $finalDoc = null;
                    } elseif ($newDoc) {
                        $filename = time() . '_' . uniqid() . '.' . $newDoc->getClientOriginalExtension();
                        $newDoc->move(public_path('work_experience'), $filename);
                        $finalDoc = 'work_experience/' . $filename;
                    } else {
                        $finalDoc = $oldDoc ?: null;
                    }

                    $hasAnyData = !empty($companyName) || !empty($expYears) || !empty($designation) || !empty($finalDoc);
                    if (!$hasAnyData) continue;

                    $lastNum++;
                    $newSerial = 'exp_' . $lastNum;

                    Mst_experience::updateOrCreate(
                        [
                            'login_id'       => $loginId,
                            'application_id' => $applicationId,
                            'emp_cate'       => $companyName,
                        ],
                        [
                            'emp_type'        => $workRow['emp_type'],
                            'intimation_date' => $workRow['intimation_date'],
                            'from_date'       => $workRow['from_date'],
                            'to_date'         => $workRow['to_date'],
                            'total_exp'       => $workRow['total_exp'],
                            'designation'     => $designation,
                            'upload_document' => $finalDoc,
                            'exp_serial'      => $newSerial,
                        ]
                    );
                }
            }


            // Photo (insert/update for this renewal app_id)
            if ($request->hasFile('upload_photo') && $request->file('upload_photo')->isValid()) {
                // ✅ New photo uploaded
                $photoName = 'user_' . time() . '.' . $request->file('upload_photo')->getClientOriginalExtension();
                $request->file('upload_photo')->move(public_path('attached_documents'), $photoName);

                TnelbApplicantPhoto::updateOrCreate(
                    ['application_id' => $applicationId], // New application ID
                    [
                        'login_id' => $loginId,
                        'upload_path' => 'attached_documents/' . $photoName,
                    ]
                );
            } else {
                // ✅ No new photo, try to fetch from old application
                $oldPhoto = TnelbApplicantPhoto::where('application_id', $id)->first();

                if ($oldPhoto) {
                    // Insert old photo path into the new application
                    TnelbApplicantPhoto::updateOrCreate(
                        ['application_id' => $applicationId], // New application ID
                        [
                            'login_id' => $loginId,
                            'upload_path' => $oldPhoto->upload_path,
                        ]
                    );
                }
            }

            // Signature (insert/update for this renewal app_id)
            if ($request->hasFile('upload_sign') && $request->file('upload_sign')->isValid()) {
                $signFile = $request->file('upload_sign');
                $signName = 'sign_' . time() . '.' . $signFile->getClientOriginalExtension();
                $signFile->move(public_path('attached_documents'), $signName);

                TnelbApplicantsSign::updateOrCreate(
                    ['application_id' => $applicationId],
                    [
                        'login_id'     => $loginId,
                        'uploaded_doc' => 'attached_documents/' . $signName,
                    ]
                );
            }

            

            DB::commit();

            return response()->json([
                'status'         => 'success',
                'message'        => $action === 'draft' ? 'Draft saved successfully!' : 'Form submitted successfully!',
                'application_id' => $applicationId,
                'applicantName'  => $form->applicant_name,
                'form_name'      => $form->form_name,
                'licence_name'   => $type_of_apps->licence_name,
                'date_apps'      => Carbon::parse($this->dbNow)->format('d-m-Y')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong. Please try again!',
                'error'   => $e->getMessage()
            ], 500);
        }
    }





    public function update(Request $request, $id)
    {
        $request->merge([
            'aadhaar' => preg_replace('/\D/', '', $request->aadhaar)
        ]);

        if ($this->isCompetencyForm($request->form_name ?? null) && $request->filled('pancard')) {
            $request->merge([
                'pancard' => strtoupper(preg_replace('/\s+/', '', $request->pancard)),
            ]);
        }

        $applicationId = $id;
        $existingForm = Mst_Form_s_w::where('application_id', $applicationId)->first();
        $existingPhoto = TnelbApplicantPhoto::where('application_id', $applicationId)->first();

        if (!$existingForm && $applicationId) {
            return response()->json(['status' => 'error', 'message' => 'Draft not found!'], 404);
        }
        $uploadPhotoRule = (!$existingPhoto || empty($existingPhoto->upload_path))
            ? 'image|mimes:jpg,jpeg,png|max:50'
            : 'nullable|image|mimes:jpg,jpeg,png|max:50';

        // Signature is optional on edit; existing signature is kept if no new file
        $uploadSignRule = 'nullable|image|mimes:jpg,jpeg,png|max:50';

        $aadhaarDocRule = ($existingForm && !$existingForm->aadhaar_doc)
            ? 'mimes:pdf|max:250'
            : 'nullable|mimes:pdf|max:250';
            $request->validate([
                'login_id'           => 'nullable|string',
                'applicant_name'     => 'nullable|string|max:255',
                'fathers_name'       => 'nullable|string|max:255',
                'applicants_address' => 'nullable|string|max:500',
                'd_o_b'              => 'nullable|date',
                'age'                => 'integer|min:18|max:100',
                'previously_number'  => 'nullable|string',
                'previously_date'    => 'nullable|date',
                'previously_issue_date' => 'nullable|date',
                'certificate_issue_date' => 'nullable|date',
                'wireman_details'    => 'nullable|string|max:255',
                'form_name'          => 'nullable|string|max:2',
                'license_name'       => 'nullable|string|max:2',
                'form_id'            => 'nullable|integer',
                // 'amount'             => 'nullable|numeric|min:0',
                'educational_level'    => 'nullable|array|min:1',
                'educational_level.*'  => 'nullable|string|max:50',
                'institute_name'       => 'nullable|array|min:1',
                'institute_name.*'     => 'nullable|string|max:80',
                'month_of_passing'     => 'nullable|array',
                'month_of_passing.*'   => 'nullable|in:01,02,03,04,05,06,07,08,09,10,11,12,1,2,3,4,5,6,7,8,9,10,11,12',
                'year_of_passing'      => 'nullable|array|min:1',
                'year_of_passing.*'    => 'nullable',
                'certificate_no'       => 'nullable|array|min:1',
                'certificate_no.*'     => 'nullable|string|max:20',
                'competency_certificate_no' => 'nullable|string|max:80',
                'upload_photo'   => $uploadPhotoRule,
                'upload_sign'    => $uploadSignRule,
                'aadhaar_doc'    => $aadhaarDocRule,
    
                'education_document.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
    
                'work_document'        => 'nullable|array',
                'work_document.*'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
            ],[

            // education arrays
            'education_document.*'    => 'Educational document size permitted only 5 KB to 200 KB.',
            'work_document.*.max'    => 'Experience document size permitted only 5 KB to 200 KB.',
            'educational_level.*.string'    => 'Educational level must be a valid string.',
            'educational_level.*.max'       => 'Educational level may not be greater than 50 characters.',
            'institute_name.*.string'       => 'Institute name must be a valid string.',
            'institute_name.*.max'          => 'Institute name may not be greater than 80 characters.',
            'month_of_passing.*.in'         => 'Month of passing must be a valid month.',
            'certificate_no.*.string'       => 'Certificate No must be a valid text value.',
            'certificate_no.*.max'          => 'Certificate No may not be greater than 20 characters.',
            // work experience arrays
            'work_level.*.string'           => 'Work level must be a valid string.',
            'work_level.*.max'              => 'Work level may not be greater than 80 characters.',
            'experience.*.numeric'          => 'Experience must be a valid number.',
            'experience.*.min'              => 'Experience cannot be negative.',
            'experience.*.max'              => 'Experience may not exceed 50 years.',
            'designation.*.string'          => 'Designation must be a valid string.',
            'designation.*.max'             => 'Designation may not be greater than 80 characters.',
            'aadhaar.digits' => 'Aadhaar number should be 12 digits.',
        ]);

        $action = $request->form_action;
        $loginId = $request->login_id;

        DB::beginTransaction();

        try {

            
            $appl_type = $request->appl_type ?? '';
            $form = Mst_Form_s_w::where('application_id', $id)
            ->where('appl_type', $appl_type)
            ->first();

            if ($form) {
                $applicationId = $form->application_id;
            } else {

                $lastApplication = Mst_Form_s_w::latest('id')->value('application_id');
                if ($lastApplication) {
                    $lastNumber = (int) substr($lastApplication, -7);
                    $applicationId = $appl_type . $request->form_name . $request->license_name . date('y') . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
                } else {
                    $applicationId = $appl_type . $request->form_name . $request->license_name . date('y') . '1111111';
                }
            }
            $encrypted_aadhaar = Crypt::encryptString($request->aadhaar);
            if ($request->hasFile('aadhaar_doc')) {
                $file = $request->file('aadhaar_doc');

                $contents = file_get_contents($file->getRealPath());

                $encrypted = Crypt::encrypt($contents);

                $aadhaarFilename = time() . '_' . rand(10000, 9999999) . '.bin';
                $destinationPath = storage_path('app/private_documents');

                if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                }

                file_put_contents($destinationPath . '/' . $aadhaarFilename, $encrypted);
            }elseif ($request->input('aadhaar_doc_removed') == "1") {
                // ✅ Removed but not replaced
                $aadhaarFilename = null;
            }else {
                // ✅ Keep the old one
                $aadhaarFilename = $form?->aadhaar_doc ?? null;

                if ($aadhaarFilename == null) {
                    $aadhaarFilename = Mst_Form_s_w::where('application_id', $id)->value('aadhaar_doc');
                }
            }

            $renewalPayload = [
                    'login_id'           => $loginId,
                    'applicant_name'     => $request->applicant_name ?? $request->Applicant_Name,
                    'fathers_name'       => $request->fathers_name ?? $request->Fathers_Name,
                    'applicants_address' => $request->applicants_address,
                    'd_o_b'              => $request->d_o_b ?? 0,
                    'age'                => $request->age,
                    'status'             => 'P',
                    'previously_number'  => $request->previously_number ?? 0,
                    'previously_date'    => $request->previously_date ?? 0,
                    'previously_issue_date' => $request->previously_issue_date ?: null,
                    'wireman_details'    => $request->wireman_details,
                    'form_name'          => $request->form_name,
                    'form_id'            => $request->form_id,
                    'license_name'       => $request->license_name,
                    'aadhaar'            => $encrypted_aadhaar,
                    'certificate_no'     => $request->competency_certificate_no ?? null,
                    'certificate_date'   => $request->certificate_date ?? null,
                    'certificate_issue_date' => $request->certificate_issue_date ?: null,
                    'appl_type'          => $appl_type,
                    'license_number'     => $request->license_number,
                    'payment_status'     => 'draft',
                    'aadhaar_doc'        => $aadhaarFilename ?? $form?->aadhaar_doc ?? null,
                    'cert_verify'        => $request->cert_verify ?? '0',
                    'license_verify'     => $request->l_verify ?? '0',
                    'old_application'    => $id ?? '',
            ];

            if ($this->isCompetencyForm($request->form_name ?? null)) {
                $encrypted_pancard_u = null;
                $panFilenameU = null;
                if ($request->filled('pancard')) {
                    $encrypted_pancard_u = Crypt::encryptString($request->pancard);
                } else {
                    $encrypted_pancard_u = $form?->pancard
                        ?? Mst_Form_s_w::where('application_id', $id)->value('pancard');
                }
                if ($request->hasFile('pancard_doc')) {
                    $pf = $request->file('pancard_doc');
                    $pContents = file_get_contents($pf->getRealPath());
                    $pEnc = Crypt::encrypt($pContents);
                    $panFilenameU = time() . '_' . rand(10000, 9999999) . '_pan.bin';
                    $destinationPath = storage_path('app/private_documents');
                    if (!is_dir($destinationPath)) {
                        mkdir($destinationPath, 0755, true);
                    }
                    file_put_contents($destinationPath . '/' . $panFilenameU, $pEnc);
                } else {
                    $panFilenameU = $form?->pan_doc
                        ?? Mst_Form_s_w::where('application_id', $id)->value('pan_doc');
                }
                $renewalPayload['pancard'] = $encrypted_pancard_u;
                $renewalPayload['pan_doc'] = $panFilenameU;
            }

            $renewal_form = Mst_Form_s_w::updateOrCreate(
                [
                    'application_id' => $applicationId
                ],
                $renewalPayload
            );

            $applicationId = $renewal_form->application_id;

            $form_details = MstLicence::where('status', 1)
            ->select('*')
            ->get()
            ->toArray();
            $form_category = LicenceCategory::where('status', 1)
            ->select('*')
            ->get()
            ->toArray();
        
            $current_form = collect($form_details)->firstWhere('cert_licence_code', $renewal_form->license_name);
            $category_type = collect($form_category)->firstWhere('id', $current_form['category_id']);

            $licence_details['licence_name'] = $current_form['licence_name'];
        
            $licence_details['category_name'] = $category_type['category_name'];
            $licence_details['form_type'] = $renewal_form->appl_type;

            // Update Education Records
            if ($request->has('educational_level')) {
                $lastEdu = Mst_education::whereNotNull('edu_serial')->latest('id')->value('edu_serial');
                $lastNum = $lastEdu ? (int) str_replace('edu_', '', $lastEdu) : 0;

                foreach ($request->educational_level as $key => $level) {
                    $levelName  = $level ?? null;
                    $institute  = $request->institute_name[$key] ?? null;
                    $year       = $request->year_of_passing[$key] ?? null;
                    $certificateNo = $request->certificate_no[$key] ?? null;

                    // Normalize month_of_passing: trim, accept "01"-"12" or "1"-"12",
                    // map to int 1-12, otherwise treat as missing.
                    $monthRaw = $request->month_of_passing[$key] ?? null;
                    $monthVal = null;
                    if ($monthRaw !== null && $monthRaw !== '') {
                        $m = (int) ltrim((string) $monthRaw, '0');
                        if ($m >= 1 && $m <= 12) {
                            $monthVal = $m;
                        }
                    }

                    $removed    = isset($request->removed_document[$key]) && $request->removed_document[$key] == '1';
                    $newDoc     = (isset($request->file('education_document')[$key]) && $request->file('education_document')[$key]->isValid())
                                    ? $request->file('education_document')[$key]
                                    : null;
                    $oldDoc     = $request->existing_document[$key] ?? null;

                    // final doc path: removed => null, new => stored, else keep old path (view mode)
                    if ($removed) {
                        $finalDoc = null;
                    } elseif ($newDoc) {
                        $filename = time() . '_' . uniqid() . '.' . $newDoc->getClientOriginalExtension();
                        $newDoc->move(public_path('education_document'), $filename);
                        $finalDoc = 'education_document/' . $filename;
                    } else {
                        $finalDoc = $oldDoc ?: null;
                    }

                    // skip only if EVERYTHING is empty (avoid junk rows)
                    $hasAnyData = !empty($levelName) || !empty($institute) || !empty($year)
                        || !empty($certificateNo) || !empty($finalDoc) || $monthVal !== null;
                    if (!$hasAnyData) continue;

                    $lastNum++;
                    $newSerial = 'edu_' . $lastNum;

                    // Look up the existing row so we can preserve its month_passing
                    // when the request does not supply a (valid) value.
                    $existingEdu = Mst_education::where([
                        'login_id'          => $loginId,
                        'application_id'    => $applicationId,
                        'educational_level' => $levelName,
                    ])->first();

                    $monthToSave = $monthVal !== null
                        ? $monthVal
                        : ($existingEdu ? $existingEdu->month_passing : null);

                    Mst_education::updateOrCreate(
                        [
                            'login_id'          => $loginId,
                            'application_id'    => $applicationId,
                            'educational_level' => $levelName,
                        ],
                        [
                            'institute_name'    => $institute,
                            'month_passing'     => $monthToSave,
                            'year_of_passing'   => $year,
                            'certificate_no'    => $certificateNo,
                            'upload_document'   => $finalDoc,
                            'edu_serial'        => $newSerial,
                        ]
                    );
                }
            }
            
            if ($this->hasWorkExperiencePayload($request)) {
                $lastExp = Mst_experience::whereNotNull('exp_serial')->latest('id')->value('exp_serial');
                $lastNum = $lastExp ? (int) str_replace('exp_', '', $lastExp) : 0;

                foreach ($this->getWorkRowIndexes($request) as $key) {
                    $workRow = $this->mapWorkExperienceRow($request, $key, $request->form_name ?? null);
                    $companyName = $workRow['company_name'] ?: null;
                    $expYears    = $workRow['experience'] ?: null;
                    $designation = $workRow['designation'] ?: null;

                    $removed     = isset($request->removed_document_work[$key]) && $request->removed_document_work[$key] == '1';
                    $newDoc      = (isset($request->file('work_document')[$key]) && $request->file('work_document')[$key]->isValid())
                                    ? $request->file('work_document')[$key]
                                    : null;
                    $oldDoc      = $request->existing_work_document[$key] ?? null;

                    if ($newDoc) {
                        $filename = time() . '_' . uniqid() . '.' . $newDoc->getClientOriginalExtension();
                        $newDoc->move(public_path('work_experience'), $filename);
                        $finalDoc = 'work_experience/' . $filename;
                    } elseif ($removed) {
                        $finalDoc = null;
                    } else {
                        $finalDoc = $oldDoc ?: null;
                    }

                    $hasAnyData = !empty($companyName) || !empty($expYears) || !empty($designation) || !empty($finalDoc);
                    if (!$hasAnyData) continue;

                    $lastNum++;
                    $newSerial = 'exp_' . $lastNum;

                    Mst_experience::updateOrCreate(
                        [
                            'login_id'       => $loginId,
                            'application_id' => $applicationId,
                            'emp_cate'       => $companyName,
                        ],
                        [
                            'emp_type'        => $workRow['emp_type'],
                            'intimation_date' => $workRow['intimation_date'],
                            'from_date'       => $workRow['from_date'],
                            'to_date'         => $workRow['to_date'],
                            'total_exp'       => $workRow['total_exp'],
                            'designation'     => $designation,
                            'upload_document' => $finalDoc,
                            'exp_serial'      => $newSerial,
                        ]
                    );
                }
            }

            // process photo
             if ($request->hasFile('upload_photo')) {
                $photoName = 'user_' . time() . '.' . $request->file('upload_photo')->getClientOriginalExtension();
                $request->file('upload_photo')->move(public_path('attached_documents'), $photoName);

                TnelbApplicantPhoto::updateOrCreate(
                    ['application_id' => $applicationId],
                    [
                        'login_id' => $loginId,
                        'upload_path' => 'attached_documents/' . $photoName,
                    ]
                );
            }

            

            // Process Payment for update
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Form submitted successfully!',
                'application_id' => $applicationId,
                'applicantName' => $renewal_form->applicant_name,
                'form_name'    => $renewal_form->form_name,
                'licence_name' => $licence_details['licence_name'],
                'type_of_apps' => $licence_details['category_name'],
                'form_type'    => $licence_details['form_type'] == 'N' ? 'FRESH' : 'RENEWAL',
                'date_apps'    => Carbon::parse($this->dbNow)->format('d-m-Y')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Something went wrong. Please try again!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function storeEducationDocument($file, $loginId, $eduSerial, $applicationId)
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('education', $filename, 'public');

        if (Schema::hasTable('mst_documents')) {
            DB::table('mst_documents')->insert([
            'login_id' => $loginId,
            'education_serial' => $eduSerial,
            'experience_serial' => null,
            'education_doc' => $filePath,
            'experience_doc' => null,
            'upload_photo' => null,
            'upload_sign' => null,
            'application_id' => $applicationId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        }
    }

    private function storeWorkDocument($file, $loginId, $expSerial, $applicationId)
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('work_experience', $filename, 'public');

        if (Schema::hasTable('mst_documents')) {
            DB::table('mst_documents')->insert([
            'login_id' => $loginId,
            'education_serial' => null,
            'experience_serial' => $expSerial,
            'education_doc' => null,
            'experience_doc' => $filePath,
            'upload_photo' => null,
            'upload_sign' => null,
            'application_id' => $applicationId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        }
    }

    private function storePhotoDocument($file, $loginId, $applicationId)
    {
        $filename = 'user' . $applicationId . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('attached_documents', $filename, 'public');

        if (Schema::hasTable('mst_documents')) {
            DB::table('mst_documents')->insert([
            'login_id' => $loginId,
            'education_serial' => null,
            'experience_serial' => null,
            'education_doc' => null,
            'experience_doc' => null,
            'upload_photo' => $filePath,
            'upload_sign' => null,
            'application_id' => $applicationId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        }
    }

    private function storeSignatureDocument($file, $loginId, $applicationId)
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('attached_documents', $filename, 'public');

        if (Schema::hasTable('mst_documents')) {
            DB::table('mst_documents')->insert([
            'login_id' => $loginId,
            'education_serial' => null,
            'experience_serial' => null,
            'education_doc' => null,
            'experience_doc' => null,
            'upload_photo' => null,
            'upload_sign' => $filePath,
            'application_id' => $applicationId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        }
    }

    public function showEncryptedDocument($type, $filename)
    {
        $allowedTypes = [
            'aadhaar' => ['folder' => 'private_documents', 'default_mime' => 'application/pdf'],
            'pan'     => ['folder' => 'private_documents', 'default_mime' => 'application/pdf'],
        ];

        if (!array_key_exists($type, $allowedTypes)) {
            abort(400, 'Invalid document type.');
        }

        $path = storage_path('app/' . $allowedTypes[$type]['folder'] . '/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }

        $encrypted = file_get_contents($path);

        try {
            $decrypted = Crypt::decrypt($encrypted);
        } catch (\Exception $e) {
            abort(500, 'Could not decrypt file.');
        }

        // Detect mime type by extension
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'pdf':
                $mime = 'application/pdf';
                break;
            case 'jpg':
            case 'jpeg':
                $mime = 'image/jpeg';
                break;
            case 'png':
                $mime = 'image/png';
                break;
            default:
                $mime = $allowedTypes[$type]['default_mime'];
        }

        return response($decrypted)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
    }


    /**
     * AJAX: upload a single education / work PDF for competency forms (Form S, etc.).
     * Stores under public/education_document or public/work_experience.
     */
    public function uploadCompetencyRowDocument(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf|min:5|max:200',
            'kind'     => 'required|in:education,work',
            'login_id' => 'required|string',
        ]);

        if ((string) Auth::user()->login_id !== (string) $request->login_id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $dir = $request->kind === 'education' ? 'education_document' : 'work_experience';
        $file = $request->file('document');
        $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
        $file->move(public_path($dir), $filename);
        $path = $dir.'/'.$filename;

        return response()->json([
            'success' => true,
            'path'    => $path,
        ]);
    }

    /**
     * Ensure a pre-uploaded relative path points to a real file under the expected folder.
     */
    private function isValidCompetencyAjaxDocPath(?string $path, string $kind): bool
    {
        if (! $this->hasValidCompetencyAjaxDocFormat($path, $kind)) {
            return false;
        }

        return is_file(public_path($path));
    }

    /**
     * Validate just the format of a pre-uploaded relative path (folder prefix + filename
     * shape) WITHOUT checking that the file physically exists on disk. Used to tell apart
     * a malformed/forged reference from a previously valid path whose file was lost.
     */
    private function hasValidCompetencyAjaxDocFormat(?string $path, string $kind): bool
    {
        if ($path === null || $path === '') {
            return false;
        }
        $prefix = $kind === 'education' ? 'education_document/' : 'work_experience/';
        if (! str_starts_with($path, $prefix)) {
            return false;
        }
        $base = basename($path);
        if ($base === '' || $base === '.' || $base === '..') {
            return false;
        }
        if (! preg_match('/^[a-zA-Z0-9_.-]+$/', $base)) {
            return false;
        }

        return true;
    }

      public function getFormCost(Request $request)
    {
        
        $applType = $request->input('appl_type'); // R = Renewal, N = New
        $formName = $request->input('form_name'); // e.g. S, W, WH
        $form = DB::table('tnelb_forms')
            ->where('form_name', 'FORM '.$formName)
            ->where('status', 1)
            ->first();

        if (!$form) {
            return response()->json(['form_cost' => null]);
        }

        $formCost = ($applType === 'R')
            ? $form->renewal_amount
            : $form->fresh_amount;
        
        return response()->json(['form_cost' => $formCost]);
    }
}
