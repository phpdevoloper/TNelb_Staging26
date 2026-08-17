<?php



namespace App\Http\Controllers;



use App\Services\FormS\FormSAlterationService;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Crypt;



class FormSAlteration extends BaseController

{

    private const FORM_LABELS = [

        'S' => 'Supervisor Competency Certificate [Form S]',

        'W' => 'Wireman Competency Certificate [Form W]',

        'H' => 'Wireman Helper Competency Certificate [Form H]',

        'P' => 'Power Generating Station Operation & Maintenance Competency Certificate [Form P]',

    ];



    public function __construct(

        protected FormSAlterationService $alterationService

    ) {

        parent::__construct();

        $this->middleware('web');

    }



    public function index(Request $request)

    {

        if (!Auth::check()) {

            return redirect()->route('logout');

        }

        $parentId = trim((string) $request->query('parent', ''));

        $formCode = $this->resolveFormCode($request);



        if ($parentId === '') {

            return view('user_login.alteration.form_s_launcher', [

                'form_code' => $formCode,

                'form_label' => self::FORM_LABELS[$formCode] ?? self::FORM_LABELS['S'],

            ]);

        }



        if (!$this->alterationService->isLauncherVerifiedFor($formCode, $parentId)
            && !$this->alterationService->hasAlterationDraftFor($parentId, (string) Auth::user()->login_id)) {

            return redirect()

                ->route('form_s_alt', ['form' => $formCode])

                ->with('alteration_error', 'Please verify your certificate details first.');

        }



        $verify = $this->alterationService->verifyParentApplication(

            $parentId,

            (string) Auth::user()->login_id

        );



        if (!$verify['ok']) {

            return redirect()

                ->route('form_s_alt', ['form' => $formCode])

                ->with('alteration_error', $verify['message'] ?? 'Invalid application.');

        }



        $viewData = $this->alterationService->buildAlterationFormViewData(

            $verify['application'],

            true

        );
        



        $this->decryptPanForDisplay($viewData['application_details']);



        return view('user_login.alteration.form_s', $viewData);

    }

    /**
     * Dropdown data: issued certificates/licences for the logged-in applicant.
     */
    public function listCertificates(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.', 'certificates' => []], 401);
        }

        $formCode = $this->resolveFormCode($request);
        $loginId = (string) Auth::user()->login_id;
        $certificates = $this->alterationService->listIssuedCertificatesForLogin($loginId, $formCode);

        return response()->json([
            'status' => 'success',
            'form' => $formCode,
            'certificates' => $certificates,
        ]);
    }

    public function verifyParent(Request $request)
    {
        $formCode = $this->resolveFormCode($request);

        if ($formCode !== 'S') {
            return response()->json([
                'status' => 'error',
                'message' => 'Alteration for this certificate type is not available yet.',
            ], 422);
        }



        $request->validate([

            'certificate_no' => 'required|string|max:80',

            'date_of_issue' => 'required|date',

            'valid_from' => 'required|date',

            'valid_to' => 'required|date|after_or_equal:valid_from',

        ]);



        $certificateDetails = [

            'certificate_no' => (string) $request->input('certificate_no'),

            'date_of_issue' => $request->input('date_of_issue'),

            'valid_from' => $request->input('valid_from'),

            'valid_to' => $request->input('valid_to'),

        ];



        $verify = $this->alterationService->verifyLauncherRequest(

            (string) Auth::user()->login_id,

            $formCode,

            $certificateDetails

        );



        if (!$verify['ok']) {

            $status = !empty($verify['certificate_not_found']) ? 'certificate_not_found' : 'error';



            return response()->json([

                'status' => $status,

                'message' => $verify['message'] ?? 'Certificate Details Not Found.',

            ], 422);

        }



        $applicationId = (string) $verify['application']->application_id;

        $this->alterationService->markLauncherVerified($formCode, $applicationId, $certificateDetails);



        return response()->json([

            'status' => 'success',

            'message' => 'Certificate verified successfully.',

            'application_id' => $applicationId,

            'redirect_url' => route('form_s_alt', [

                'parent' => $applicationId,

                'form' => $formCode,

            ]),

        ]);

    }



    public function store(Request $request)

    {

        $request->validate([

            'parent_application_id' => 'required|string|max:80',

            'login_id' => 'required|string',

            'alter_name' => 'nullable|in:0,1',

            'alter_address' => 'nullable|in:0,1',

            'alter_workexp' => 'nullable|in:0,1',

            'name_alteration_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',

            'address_alteration_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',

        ]);



        try {

            $child = $this->alterationService->storeAlterationRequest($request);



            return response()->json([

                'status' => 'success',

                'message' => 'Alteration request submitted successfully.',

                'application_id' => $child->application_id,

                'applicantName' => $child->applicant_name,

                'form_name' => $child->form_name,

                'licence_name' => $child->license_name,

                'type_of_apps' => 'Alteration',

                'form_type' => 'ALTERATION',

                'date_apps' => now()->format('d-m-Y'),

            ]);

        } catch (\Throwable $e) {

            return response()->json([

                'status' => 'error',

                'message' => $e->getMessage() ?: 'Unable to submit alteration request.',

            ], 422);

        }

    }



    public function saveDraft(Request $request)

    {

        $request->validate([

            'parent_application_id' => 'required|string|max:80',

            'login_id' => 'required|string',

            'name_alteration_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',

            'address_alteration_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',

        ]);



        try {

            $child = $this->alterationService->saveAlterationDraft($request);

            $this->alterationService->markLauncherVerifiedForParent(
                (string) ($child->form_name ?? 'S'),
                (string) $request->input('parent_application_id')
            );

            return response()->json([

                'status' => 'success',

                'message' => 'Alteration draft saved successfully.',

                'application_id' => $child->application_id,

            ]);

        } catch (\Throwable $e) {

            return response()->json([

                'status' => 'error',

                'message' => $e->getMessage() ?: 'Unable to save alteration draft.',

            ], 422);

        }

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



    private function resolveFormCode(Request $request): string

    {

        $formCode = strtoupper(trim((string) $request->query('form', $request->input('form', 'S'))));



        return array_key_exists($formCode, self::FORM_LABELS) ? $formCode : 'S';

    }

}


