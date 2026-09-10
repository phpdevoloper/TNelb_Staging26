<?php

namespace App\Http\Controllers;

use App\Models\CC_Digitisation_Map;
use App\Models\Tnelb_CC_Digitization;
use App\Services\Competency\FormWSchema;
use App\Services\FileUploadService;
use App\Services\FormS\FormSAlterationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Form W applicant facade for New, Renewal, Digitisation, and Alteration.
 * Persist/pay/workflow stay on FormController and competency services.
 */
class FormWController extends BaseController
{
    private const FORM_LABEL = 'Wireman Competency Certificate [Form W]';

    protected $today;

    protected $dbNow;

    public function __construct(
        protected FileUploadService $fileUpload,
        protected FormSAlterationService $alterationService
    ) {
        parent::__construct();
        $this->middleware('web');
        $this->today = Carbon::today()->toDateString();
        $this->dbNow = DB::selectOne(
            "SELECT date_trunc('second', NOW()::timestamp) AS db_now"
        )->db_now;
    }

    public function create()
    {
        if (! Auth::check()) {
            return redirect()->route('logout');
        }

        $authUser = Auth::user();
        $user = [
            'user_id' => $authUser->login_id,
            'salutation' => $authUser->salutation,
            'applicant_name' => $authUser->first_name.' '.$authUser->last_name,
        ];

        return view('user_login.apply-form-w', compact('user'));
    }

    public function digitize(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('logout');
        }

        $request->merge(['form' => $request->input('form', FormWSchema::FORM_NAME)]);
        $request->validate([
            'form' => 'required|in:W',
        ]);

        $form = $request->form;
        $authUser = Auth::user();
        $user = [
            'user_id' => $authUser->login_id,
            'salutation' => $authUser->salutation,
            'applicant_name' => $authUser->first_name.' '.$authUser->last_name,
        ];
        $contractorDetails = $this->getContractorDetails($authUser->login_id);

        return view('user_login.digitization.apply-form-w_d', compact('user', 'form', 'contractorDetails'));
    }

    public function getContractorDetails($loginId, $tempAppId = null, $applicationId = null)
    {
        $query = Tnelb_CC_Digitization::where('login_id', $loginId);

        if (! empty($applicationId)) {
            $query->where('application_id', $applicationId);
        } elseif (! empty($tempAppId)) {
            $query->where('temp_app_id', $tempAppId);
        } else {
            $query->where('form_name', FormWSchema::FORM_NAME);
        }

        $row = $query->orderByDesc('id')->first();

        if (! $row || $row->licence_no === null || $row->licence_no === '') {
            return null;
        }

        return [
            'cl_type' => $row->cl_type,
            'licence_no' => $row->licence_no,
            'contractor_name' => $row->contractor_name,
        ];
    }

    public function fetchContractorDetails(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['contractorDetails' => null], 401);
        }

        $contractorDetails = $this->getContractorDetails(
            Auth::user()->login_id,
            $request->query('temp_app_id'),
            $request->query('application_id')
        );

        return response()->json([
            'contractorDetails' => $contractorDetails,
        ]);
    }

    public function storeDigitization(Request $request)
    {
        $request->merge([
            'form_name' => FormWSchema::FORM_NAME,
            'cert_name' => FormWSchema::LICENSE_NAME,
        ]);
        $request->validate([
            'ccnumber' => 'required|digits_between:1,5',
            'fissue' => 'required|date',
            'from_date' => 'required|date|after_or_equal:fissue',
            'to_date' => 'required|date|after_or_equal:from_date',
            'cc_doc' => 'required|mimes:pdf|max:250',
            'form_name' => 'required|in:W',
            'cert_name' => 'required|in:B',
        ], [
            'from_date.after_or_equal' => 'Date of First Issue must be less than or equal to Validity From date.',
        ]);

        $toDate = Carbon::parse($request->to_date);
        $allowedDate = $toDate->copy()->addYear();

        if (Carbon::today()->gt($allowedDate)) {
            return response()->json([
                'errors' => [
                    'to_date' => [
                        'To date must be less than or equal to 1 year from today.',
                    ],
                ],
            ], 422);
        }

        $now = db_now();
        $original_name = null;
        $fileName = 'pending';

        $record = DB::transaction(function () use ($request, $now, &$original_name, &$fileName) {
            $row = Tnelb_CC_Digitization::create([
                'login_id' => Auth::user()->login_id,
                'temp_app_id' => 'TEMP'.date('Ymd').'0000',
                'form_name' => FormWSchema::FORM_NAME,
                'cert_name' => FormWSchema::LICENSE_NAME,
                'ccnumber' => $request->ccnumber,
                'fissue' => $request->fissue,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'qc' => 0,
                'qsc' => 0,
                'cl_type' => null,
                'licence_no' => null,
                'contractor_name' => null,
                'cc_doc' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
                'qc_det' => 0,
                'cc_type' => FormWSchema::LICENSE_NAME,
            ]);

            $temp_app_id = 'TEMP'.date('Ymd').str_pad($row->id, 4, '0', STR_PAD_LEFT);

            if ($request->hasFile('cc_doc')) {
                $file = $request->file('cc_doc');
                $original_name = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = $temp_app_id.'_'.time().'_'.$request->cert_name.'.'.$extension;
                $fileName = $this->fileUpload->upload($file, 'uploads/digitization/scc', $fileName);
            }

            CC_Digitisation_Map::create([
                'application_id' => $request->input('application_id') ?: null,
                'old_cc_no' => $request->ccnumber,
                'created_at' => $now,
                'temp_id' => $temp_app_id,
                'cc_type' => FormWSchema::LICENSE_NAME,
            ]);

            $row->update([
                'temp_app_id' => $temp_app_id,
                'cc_doc' => $fileName,
                'original_name' => $original_name,
                'updated_at' => $now,
            ]);

            return $row->fresh();
        });

        return response()->json([
            'status' => 200,
            'message' => 'Digitization details saved successfully.',
            'temp_app_id' => $record->temp_app_id,
            'digitization_id' => $record->id,
            'contractorDetails' => null,
        ]);
    }

    public function renew($appl_id)
    {
        $request = request();
        $request->attributes->set(FormWSchema::VIA_CONTROLLER_ATTR, true);

        return app(RegisterController::class)->cc_renew_form($appl_id);
    }

    public function store(Request $request)
    {
        return $this->persistViaFormController('store', $request);
    }

    public function draftSubmit(Request $request, $id = null)
    {
        return $this->persistViaFormController('draft_submit', $request, $id);
    }

    public function draftUpdate(Request $request, $applicationId)
    {
        return $this->persistViaFormController('draft_update', $request, $applicationId);
    }

    public function draftRenewalSubmit(Request $request, $id = null)
    {
        return $this->persistViaFormController('draft_renewal_submit', $request, $id);
    }

    public function updateApplication(Request $request, $id)
    {
        return $this->persistViaFormController('update', $request, $id);
    }

    public function alterIndex(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('logout');
        }

        $request->merge(['form' => FormWSchema::FORM_NAME]);
        $parentId = trim((string) $request->query('parent', ''));

        if ($parentId === '') {
            return view('user_login.alteration.form_s_launcher', [
                'form_code' => FormWSchema::FORM_NAME,
                'form_label' => self::FORM_LABEL,
                'alterVerifyUrl' => route('form_w_alt.verify'),
                'alterCertificatesUrl' => route('form_w_alt.certificates'),
            ]);
        }

        if (! $this->alterationService->isLauncherVerifiedFor(FormWSchema::FORM_NAME, $parentId)
            && ! $this->alterationService->hasAlterationDraftFor($parentId, (string) Auth::user()->login_id)) {
            return redirect()
                ->route('form_w_alt')
                ->with('alteration_error', 'Please verify your certificate details first.');
        }

        $verify = $this->alterationService->verifyParentApplication(
            $parentId,
            (string) Auth::user()->login_id
        );

        if (! $verify['ok'] || ! FormWSchema::isFormW($verify['application']->form_name ?? '')) {
            return redirect()
                ->route('form_w_alt')
                ->with('alteration_error', $verify['message'] ?? 'Invalid application.');
        }

        $viewData = $this->alterationService->buildAlterationFormViewData(
            $verify['application'],
            true
        );
        $this->decryptPanForDisplay($viewData['application_details']);
        $viewData['alterStoreUrl'] = route('form_w_alt.store');
        $viewData['alterDraftUrl'] = route('form_w_alt.draft');

        return view('user_login.alteration.form_s', $viewData);
    }

    public function listCertificates(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.', 'certificates' => []], 401);
        }

        $loginId = (string) Auth::user()->login_id;
        $certificates = $this->alterationService->listIssuedCertificatesForLogin($loginId, FormWSchema::FORM_NAME);

        return response()->json([
            'status' => 'success',
            'form' => FormWSchema::FORM_NAME,
            'certificates' => $certificates,
        ]);
    }

    public function verifyParent(Request $request)
    {
        $request->merge(['form' => FormWSchema::FORM_NAME]);
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
            FormWSchema::FORM_NAME,
            $certificateDetails
        );

        if (! $verify['ok']) {
            $status = ! empty($verify['certificate_not_found']) ? 'certificate_not_found' : 'error';

            return response()->json([
                'status' => $status,
                'message' => $verify['message'] ?? 'Certificate Details Not Found.',
            ], 422);
        }

        $applicationId = (string) $verify['application']->application_id;
        $this->alterationService->markLauncherVerified(FormWSchema::FORM_NAME, $applicationId, $certificateDetails);

        return response()->json([
            'status' => 'success',
            'message' => 'Certificate verified successfully.',
            'application_id' => $applicationId,
            'redirect_url' => route('form_w_alt', [
                'parent' => $applicationId,
                'form' => FormWSchema::FORM_NAME,
            ]),
        ]);
    }

    public function storeAlteration(Request $request)
    {
        $request->merge(['form' => FormWSchema::FORM_NAME]);
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
            if (! FormWSchema::isFormW($child->form_name ?? '')) {
                throw new \RuntimeException('Alteration is not a Form W application.');
            }

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

    public function saveAlterationDraft(Request $request)
    {
        $request->merge(['form' => FormWSchema::FORM_NAME]);
        $request->validate([
            'parent_application_id' => 'required|string|max:80',
            'login_id' => 'required|string',
            'name_alteration_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
            'address_alteration_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
        ]);

        try {
            $child = $this->alterationService->saveAlterationDraft($request);
            $this->alterationService->markLauncherVerifiedForParent(
                FormWSchema::FORM_NAME,
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

    private function persistViaFormController(string $method, Request $request, ...$args)
    {
        $request->merge(FormWSchema::identityPayload());
        $request->attributes->set(FormWSchema::VIA_CONTROLLER_ATTR, true);

        return app(FormController::class)->{$method}($request, ...$args);
    }

    private function decryptPanForDisplay($applicationDetails): void
    {
        if (! $applicationDetails || ! isset($applicationDetails->pancard) || $applicationDetails->pancard === null || $applicationDetails->pancard === '') {
            return;
        }

        try {
            $applicationDetails->pancard = Crypt::decryptString((string) $applicationDetails->pancard);
        } catch (\Throwable $e) {
            // Keep legacy/plain values as-is when not encrypted.
        }
    }
}
