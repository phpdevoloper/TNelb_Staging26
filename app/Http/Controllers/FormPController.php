<?php

namespace App\Http\Controllers;

use App\Models\CC_Digitisation_Map;
use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Forms_Meta;
use App\Models\CC_Proof_doc;
use App\Models\Tnelb_CC_Digitization;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbApplicantsSign;
use App\Models\TnelbAppsInstitute;
use App\Models\TnelbFormP;
use App\Services\CcDigitizationLinkService;
use App\Services\Competency\CompetencyCertificateService;
use App\Services\Competency\CompetencyDocumentSupport;
use App\Services\Competency\FormPSchema;
use App\Services\FileUploadService;
use App\Services\FormS\FormSDocumentUploadHandler;
use App\Services\FormS\FormSAlterationService;
use App\Services\FormS\FormSProofDocumentService;
use App\Services\ReturnedApplicationEditScope;
use App\Services\ReturnedApplicationPayloadMerge;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Form P applicant facade: New, Renewal, Digitisation, Alteration.
 * Persist/pay stay on FormController + CC tables; institute rows stay on tnelb_applicant_institute.
 */
class FormPController extends BaseController
{
    private const FORM_LABEL = 'Power Generating Station Operation & Maintenance Competency Certificate [Form P]';

    protected $today;

    protected $dbNow;

    public function __construct(
        protected FileUploadService $fileUpload,
        protected FormSAlterationService $alterationService,
        protected FormSDocumentUploadHandler $documentHandler
    ) {
        parent::__construct();
        $this->middleware('web');
        $this->today = Carbon::today()->toDateString();
        $this->dbNow = DB::selectOne(
            "SELECT date_trunc('second', NOW()::timestamp) AS db_now"
        )->db_now;
    }

    public function apply_form_p()
    {
        if (! Auth::check()) {
            return redirect()->route('logout');
        }
        $authUser = Auth::user();
        $user = $this->formPUserPayload($authUser);

        return view('user_login.apply-form-p', compact('user'));
    }

    public function digitize(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('logout');
        }

        $request->merge(['form' => $request->input('form', FormPSchema::FORM_NAME)]);
        $request->validate([
            'form' => 'required|in:P',
        ]);

        $form = $request->form;
        $authUser = Auth::user();
        $user = $this->formPUserPayload($authUser);
        $cc_digitization_temp_id = (string) (app(CcDigitizationLinkService::class)->resolveTempAppId(
            null,
            (string) $authUser->login_id,
            null,
            FormPSchema::FORM_NAME
        ) ?? '');
        $contractorDetails = $this->getContractorDetails(
            $authUser->login_id,
            $cc_digitization_temp_id !== '' ? $cc_digitization_temp_id : null
        );
        $applicant_photo = null;
        $proof_doc = null;

        return view('user_login.digitization.apply-form-p_d', compact(
            'user',
            'form',
            'contractorDetails',
            'applicant_photo',
            'proof_doc',
            'cc_digitization_temp_id'
        ));
    }

    public function getContractorDetails($loginId, $tempAppId = null, $applicationId = null)
    {
        $query = Tnelb_CC_Digitization::where('login_id', $loginId);

        if (! empty($applicationId)) {
            $query->where('application_id', $applicationId);
        } elseif (! empty($tempAppId)) {
            $query->where('temp_app_id', $tempAppId);
        } else {
            $query->where('form_name', FormPSchema::FORM_NAME)
                ->whereNull('application_id');
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
            'form_name' => FormPSchema::FORM_NAME,
            'cert_name' => FormPSchema::LICENSE_NAME,
        ]);
        $request->validate([
            'ccnumber' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9][A-Za-z0-9\-\/]{0,19}$/'],
            'fissue' => 'required|date',
            'from_date' => 'required|date|after_or_equal:fissue',
            'to_date' => 'required|date|after_or_equal:from_date',
            'cc_doc' => 'required|mimes:pdf|max:250',
            'cl_det' => 'nullable|in:yes,no',
            'form_name' => 'required|in:P',
            'cert_name' => 'required|in:P',
        ], [
            'from_date.after_or_equal' => 'Date of First Issue must be less than or equal to Validity From date.',
        ]);

        $toDate = Carbon::parse($request->to_date);
        $allowedDate = $toDate->copy()->addYear();

        if (Carbon::today()->gt($allowedDate)) {
            return response()->json([
                'errors' => [
                    'to_date' => [
                        'Validity To cannot be more than one year before today.',
                    ],
                ],
            ], 422);
        }

        $workingWithContractor = $request->cl_det === 'yes';

        if ($workingWithContractor) {
            $request->validate([
                'cl_type' => 'required|in:EA,ESA',
                'licence_no' => 'required|digits_between:1,5',
                'contractor_name' => 'required|max:100',
                'qc_doc' => 'required|mimes:pdf|max:250',
            ]);
        }

        $now = db_now();
        $original_name = null;
        $fileName = 'pending';
        $qcFileName = null;

        $record = DB::transaction(function () use (
            $request,
            $now,
            $workingWithContractor,
            &$original_name,
            &$fileName,
            &$qcFileName
        ) {
            $qc = 0;
            $qsc = 0;
            $clType = $workingWithContractor ? $request->cl_type : null;
            $licenceNo = $workingWithContractor ? $request->licence_no : null;
            $contractorName = $workingWithContractor ? $request->contractor_name : null;

            if ($clType === 'EA') {
                $qc = 1;
            } elseif ($clType === 'ESA') {
                $qsc = 1;
            }

            $loginId = Auth::user()->login_id;
            $postedTemp = trim((string) (
                $request->input('cc_digitization_temp_id')
                ?: $request->input('temp_app_id')
                ?: ''
            ));
            $unlinked = Tnelb_CC_Digitization::where('login_id', $loginId)
                ->where('form_name', FormPSchema::FORM_NAME)
                ->whereNull('application_id');
            $row = $postedTemp !== ''
                ? (clone $unlinked)->where('temp_app_id', $postedTemp)->orderByDesc('id')->first()
                : (clone $unlinked)->orderByDesc('id')->first();

            if (! $row) {
                $row = Tnelb_CC_Digitization::create([
                    'login_id' => $loginId,
                    'temp_app_id' => 'TEMP'.date('Ymd').'0000',
                    'form_name' => FormPSchema::FORM_NAME,
                    'cert_name' => FormPSchema::LICENSE_NAME,
                    'ccnumber' => $request->ccnumber,
                    'fissue' => $request->fissue,
                    'from_date' => $request->from_date,
                    'to_date' => $request->to_date,
                    'qc' => $qc,
                    'qsc' => $qsc,
                    'cl_type' => $clType,
                    'licence_no' => $licenceNo,
                    'contractor_name' => $contractorName,
                    'cc_doc' => 'pending',
                    'created_at' => $now,
                    'updated_at' => $now,
                    'qc_det' => $workingWithContractor ? 1 : 0,
                    'cc_type' => FormPSchema::LICENSE_NAME,
                ]);
            }

            $temp_app_id = trim((string) ($row->temp_app_id ?? ''));
            if ($temp_app_id === '' || preg_match('/TEMP\d{8}0000$/', $temp_app_id)) {
                $temp_app_id = 'TEMP'.date('Ymd').str_pad((string) $row->id, 4, '0', STR_PAD_LEFT);
            }
            $digiDir = CompetencyDocumentSupport::digitizationUploadDirectory(FormPSchema::FORM_NAME);

            if ($request->hasFile('cc_doc')) {
                $file = $request->file('cc_doc');
                $original_name = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $fileName = $temp_app_id.'_'.time().'_'.$request->cert_name.'.'.$extension;
                $fileName = $this->fileUpload->upload($file, $digiDir, $fileName);
            } else {
                $fileName = $row->cc_doc;
                $original_name = $row->original_name;
            }

            if ($workingWithContractor && $request->hasFile('qc_doc')) {
                $qcFile = $request->file('qc_doc');
                $extension = $qcFile->getClientOriginalExtension();
                $qcFileName = $temp_app_id.'_QC_'.time().'.'.$extension;
                $qcFileName = $this->fileUpload->upload($qcFile, $digiDir, $qcFileName);
            } elseif ($workingWithContractor) {
                $qcFileName = $row->qc_doc;
            }

            $mapPayload = [
                'application_id' => $request->input('application_id') ?: null,
                'updated_at' => $now,
                'temp_id' => $temp_app_id,
                'cc_type' => FormPSchema::LICENSE_NAME,
            ];
            if (preg_match('/^\d+$/', (string) $request->ccnumber)) {
                $mapPayload['old_cc_no'] = $request->ccnumber;
            }
            $existingMap = CC_Digitisation_Map::where('temp_id', $temp_app_id)->first();
            if ($existingMap) {
                $existingMap->update($mapPayload);
            } else {
                CC_Digitisation_Map::create(array_merge($mapPayload, ['created_at' => $now]));
            }

            $row->update([
                'temp_app_id' => $temp_app_id,
                'form_name' => FormPSchema::FORM_NAME,
                'cert_name' => FormPSchema::LICENSE_NAME,
                'ccnumber' => $request->ccnumber,
                'fissue' => $request->fissue,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'qc' => $qc,
                'qsc' => $qsc,
                'cl_type' => $clType,
                'licence_no' => $licenceNo,
                'contractor_name' => $contractorName,
                'cc_doc' => $fileName,
                'original_name' => $original_name,
                'qc_doc' => $qcFileName,
                'qc_det' => $workingWithContractor ? 1 : 0,
                'cc_type' => FormPSchema::LICENSE_NAME,
                'updated_at' => $now,
            ]);

            return $row->fresh();
        });

        $contractorDetails = null;
        if (! empty($record->licence_no)) {
            $contractorDetails = [
                'cl_type' => $record->cl_type,
                'licence_no' => $record->licence_no,
                'contractor_name' => $record->contractor_name,
            ];
        }

        return response()->json([
            'status' => 200,
            'message' => 'Digitization details saved successfully.',
            'temp_app_id' => $record->temp_app_id,
            'digitization_id' => $record->id,
            'contractorDetails' => $contractorDetails,
        ]);
    }

    public function renew(string $appl_id)
    {
        return $this->renew_form_p($appl_id);
    }

    public function renew_form_p(string $appl_id)
    {
        
        if (! Auth::check()) {
            return redirect()->route('logout');
        }

        if (!$appl_id) {
            return redirect()->route('dashboard')->with('error', 'Application ID is required.');
        }

        $original = CC_Forms_Meta::findByApplicationId($appl_id);
        
        if (! $original) {
            return redirect()->route('dashboard')->with('error', 'Application not found.');
        }

        $loginId = Auth::user()->login_id;
        if (strtoupper((string) ($original->appl_type ?? '')) === 'R') {
            if (! empty($original->old_application)) {
                return redirect()->route('renew_form_p', ['application_id' => $original->old_application]);
            }

            return redirect()->route('dashboard')->with('error', 'Invalid renewal application.');
        }

        if ((string) ($original->login_id ?? '') !== (string) $loginId) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        if (strtoupper((string) ($original->form_name ?? '')) !== 'P') {
            return redirect()->route('dashboard')->with('error', 'Invalid form type for renewal.');
        }
        if (strtoupper((string) trim($original->app_status ?? '')) !== 'A') {
            return redirect()->route('dashboard')->with('error', 'Only approved applications can be renewed.');
        }
        if (app(FormController::class)->renewalAlreadyInProgress((string) $appl_id, (string) $loginId)) {
            return redirect()->route('dashboard')->with('error', 'A renewal application is already in progress for this certificate.');
        }

        $renewalDraft = $this->findFormPRenewalDraft((string) $appl_id, (string) $loginId);
        $dataSourceId = $renewalDraft ? (string) $renewalDraft->application_id : (string) $appl_id;

        $viewData = $this->loadFormPViewData($dataSourceId);
        if ($viewData === []) {
            return redirect()->route('dashboard')->with('error', 'Application not found.');
        }
        $this->markFormPRenewalInheritedRows(
            $viewData,
            (string) $appl_id,
            $renewalDraft !== null && (string) $dataSourceId !== (string) $appl_id
        );


        if ($renewalDraft && !empty($appl_id) && (string) $dataSourceId !== (string) $appl_id) {
            $parentPhoto = TnelbApplicantPhoto::where('application_id', $appl_id)->first();
            if ($parentPhoto && !empty($parentPhoto->upload_path)) {
                $currentPhoto = $viewData['applicant_photo'] ?? null;
                if (!$currentPhoto || empty($currentPhoto->upload_path)) {
                    $viewData['applicant_photo'] = $parentPhoto;
                }
            }

            $parentSign = TnelbApplicantsSign::where('application_id', $appl_id)->first();
            if ($parentSign && !empty($parentSign->uploaded_doc)) {
                $currentSign = $viewData['applicant_sign'] ?? null;
                if (!$currentSign || empty($currentSign->uploaded_doc)) {
                    $viewData['applicant_sign'] = $parentSign;
                }
            }

            $renewalForm = $viewData['application_details'] ?? null;
            $parentForm = TnelbFormP::where('application_id', $appl_id)->first();
            if ($renewalForm && $parentForm) {
                if (empty($renewalForm->aadhaar_doc) && !empty($parentForm->aadhaar_doc)) {
                    $renewalForm->aadhaar_doc = $parentForm->aadhaar_doc;
                }
                if (empty($renewalForm->pan_doc) && !empty($parentForm->pan_doc)) {
                    $renewalForm->pan_doc = $parentForm->pan_doc;
                }
                if (empty($renewalForm->applicant_email) && !empty($parentForm->applicant_email)) {
                    $renewalForm->applicant_email = $parentForm->applicant_email;
                }
            }

            $parentInstitutes = TnelbAppsInstitute::where('application_id', $appl_id)
                ->where('institute_status', 1)
                ->get();
            $renewalInstitutes = $viewData['institutes'] ?? collect([]);
            if ($renewalInstitutes->isNotEmpty() && $parentInstitutes->isNotEmpty()) {
                $viewData['institutes'] = $renewalInstitutes->map(function ($institute) use ($parentInstitutes) {
                    if (!empty($institute->upload_doc)) {
                        return $institute;
                    }
                    $parentMatch = $parentInstitutes->first(function ($parent) use ($institute) {
                        return trim((string) ($parent->institute_name_address ?? '')) === trim((string) ($institute->institute_name_address ?? ''));
                    });
                    if ($parentMatch && !empty($parentMatch->upload_doc)) {
                        $institute->upload_doc = $parentMatch->upload_doc;
                    }
                    return $institute;
                });
            }
        }

        // Issued number lives on the approved parent (N/D/A), never on the unpaid R draft.
        $issuedForRenew = $this->formPIssuedCertificateNo((string) $appl_id, $original);
        if ($issuedForRenew !== '') {
            if (! $viewData['license_details']) {
                $viewData['license_details'] = (object) ['license_number' => $issuedForRenew];
            } else {
                $viewData['license_details']->license_number = $issuedForRenew;
            }
        }

        $applicationid = $renewalDraft ? $renewalDraft->application_id : $appl_id;
        $old_application_id = $appl_id;
        $isRenewFormP = true;

        $old_application = $appl_id;
        $applicationid = $dataSourceId;


        return view('user_login.renew-form-p', array_merge($viewData, compact(
            'old_application',
            'old_application_id',
            'applicationid',
            'isRenewFormP'
        )));
    }

    public function store(Request $request)
    {
        return $this->persistViaFormController('store', $request);
    }

    public function saveDraft(Request $request)
    {
        $applicationId = trim((string) $request->input('application_id', ''));
        if ($applicationId !== '') {
            return $this->persistViaFormController('draft_submit', $request, $applicationId);
        }

        return $this->persistViaFormController('draft_submit', $request);
    }

    public function draftSubmit(Request $request, $id = null)
    {
        return $this->persistViaFormController('draft_submit', $request, $id);
    }

    public function draftUpdate(Request $request, $applicationId)
    {
        return $this->persistViaFormController('draft_update', $request, $applicationId);
    }

    public function draft_renewal_submit_p(Request $request, $id = null)
    {
        $request->merge(['appl_type' => 'R']);

        return $this->persistViaFormController('draft_renewal_submit', $request, $id);
    }

    public function draftRenewalSubmit(Request $request, $id = null)
    {
        return $this->draft_renewal_submit_p($request, $id);
    }

    public function update(Request $request)
    {
        $id = trim((string) $request->input('application_id', ''));

        return $this->persistViaFormController('update', $request, $id);
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

        $request->merge(['form' => FormPSchema::FORM_NAME]);
        $parentId = trim((string) $request->query('parent', ''));

        if ($parentId === '') {
            return view('user_login.alteration.form_p_launcher', [
                'form_code' => FormPSchema::FORM_NAME,
                'form_label' => self::FORM_LABEL,
                'alterVerifyUrl' => route('form_p_alt.verify'),
                'alterCertificatesUrl' => route('form_p_alt.certificates'),
            ]);
        }

        if (! $this->alterationService->isLauncherVerifiedFor(FormPSchema::FORM_NAME, $parentId)
            && ! $this->alterationService->hasAlterationDraftFor($parentId, (string) Auth::user()->login_id)) {
            return redirect()
                ->route('form_p_alt')
                ->with('alteration_error', 'Please verify your certificate details first.');
        }

        $verify = $this->alterationService->verifyParentApplication(
            $parentId,
            (string) Auth::user()->login_id
        );

        if (! $verify['ok'] || ! FormPSchema::isFormP($verify['application']->form_name ?? '')) {
            return redirect()
                ->route('form_p_alt')
                ->with('alteration_error', $verify['message'] ?? 'Invalid application.');
        }

        $viewData = $this->alterationService->buildAlterationFormViewData(
            $verify['application'],
            true
        );
        $this->decryptPanForDisplay($viewData['application_details']);
        $viewData['institute_details'] = $this->alterationInstituteRows($viewData);
        $viewData['alterStoreUrl'] = route('form_p_alt.store');
        $viewData['alterDraftUrl'] = route('form_p_alt.draft');

        return view('user_login.alteration.form_p', $viewData);
    }

    public function listCertificates(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.', 'certificates' => []], 401);
        }

        $loginId = (string) Auth::user()->login_id;
        $certificates = $this->alterationService->listIssuedCertificatesForLogin($loginId, FormPSchema::FORM_NAME);

        return response()->json([
            'status' => 'success',
            'form' => FormPSchema::FORM_NAME,
            'certificates' => $certificates,
        ]);
    }

    public function verifyParent(Request $request)
    {
        $request->merge(['form' => FormPSchema::FORM_NAME]);
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
            FormPSchema::FORM_NAME,
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
        $this->alterationService->markLauncherVerified(FormPSchema::FORM_NAME, $applicationId, $certificateDetails);

        return response()->json([
            'status' => 'success',
            'message' => 'Certificate verified successfully.',
            'application_id' => $applicationId,
            'redirect_url' => route('form_p_alt', [
                'parent' => $applicationId,
                'form' => FormPSchema::FORM_NAME,
            ]),
        ]);
    }

    public function storeAlteration(Request $request)
    {
        $request->merge(['form' => FormPSchema::FORM_NAME]);
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
            if (! $this->childLooksLikeFormP($child)) {
                throw new \RuntimeException('Alteration is not a Form P application.');
            }

            $this->persistAlterationInstitutes($request, $child);

            return response()->json([
                'status' => 'success',
                'message' => 'Alteration request submitted successfully.',
                'application_id' => $child->application_id,
                'applicantName' => $child->applicant_name,
                'form_name' => $child->form_name,
                'licence_name' => $child->license_name ?? $child->certificate_name,
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
        $request->merge(['form' => FormPSchema::FORM_NAME]);
        $request->validate([
            'parent_application_id' => 'required|string|max:80',
            'login_id' => 'required|string',
            'name_alteration_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
            'address_alteration_proof' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
        ]);

        try {
            $child = $this->alterationService->saveAlterationDraft($request);
            $this->alterationService->markLauncherVerifiedForParent(
                FormPSchema::FORM_NAME,
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

    public function editApplication($appl_id)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }
        if (!$appl_id) {
            return redirect()->route('dashboard')->with('error', 'Application ID is required.');
        }

        $viewData = $this->loadFormPViewData((string) $appl_id);
        if ($viewData === []) {
            return redirect()->route('dashboard')->with('error', 'Application not found.');
        }

        $application_details = $viewData['application_details'];
        if ((string) ($application_details->login_id ?? '') !== (string) Auth::user()->login_id) {
            return redirect()->route('dashboard')->with('error', 'Unauthorized access.');
        }

        $applicationid = $appl_id;
        $user = $this->formPUserPayload(Auth::user());
        $appStatusRaw = strtoupper(trim((string) ($application_details->app_status ?? '')));
        $legacyStatus = strtoupper(trim((string) ($application_details->status ?? '')));
        $appStatus = ($appStatusRaw === 'QU' || $legacyStatus === 'QU')
            ? 'QU'
            : ($appStatusRaw !== '' ? $appStatusRaw : $legacyStatus);
        $isReturnedRoute = request()->routeIs('edit_returned_application_p');

        $queries = collect();
        $applType = strtoupper(trim((string) ($application_details->appl_type ?? '')));
        $parentId = trim((string) ($application_details->old_application ?? ''));
        $pay = strtoupper(trim((string) ($application_details->payment_status ?? '')));
        $paidOrExempt = in_array($pay, ['Y', 'B', 'SUCCESS', 'PAID', 'S', 'PAYMENT'], true);
        if (
            ! $isReturnedRoute
            && $appStatus !== 'QU'
            && $applType === 'R'
            && $parentId !== ''
            && ! $paidOrExempt
        ) {
            return redirect()->route('renew_form_p', ['application_id' => $parentId]);
        }

        $queryReasonsForValidation = [];
        $returnRemarks = '';
        $returnedEditableSections = [ReturnedApplicationEditScope::SECTION_FULL];
        $returnedFormPSectionKeys = [];
        $returnedIsPartialEdit = false;

        if ($appStatus === 'QU' || $isReturnedRoute) {
            $returnLogRow = ReturnedApplicationEditScope::latestReturnLogRow($appl_id);
            if ($returnLogRow) {
                $returnRemarks = trim((string) ($returnLogRow->remarks ?? ''));
                $queryReasonsForValidation = ReturnedApplicationEditScope::parseQueryTypesJson($returnLogRow->query_types ?? null);
                if ($queryReasonsForValidation !== [] || $returnRemarks !== '') {
                    $queries = collect([
                        (object) [
                            'query_type' => json_encode($queryReasonsForValidation),
                            'raised_by' => $returnLogRow->returned_by_role ?? null,
                        ]
                    ]);
                }
            }
            if ($queries->isEmpty()) {
                $queries = DB::table('tnelb_query_applicable')
                    ->where('application_id', $appl_id)
                    ->where('query_status', 'P')
                    ->orderByDesc('id')
                    ->get();
            }
            $returnedEditableSections = ReturnedApplicationEditScope::editableSectionsFromReasons($queryReasonsForValidation);
            $returnedFormPSectionKeys = $this->mapReturnedFormPSectionKeys($returnedEditableSections);
            $returnedIsPartialEdit = $returnedFormPSectionKeys !== [];

            $isReturnedApplication = true;
            $application_details->app_status = 'QU';
            $viewData['application_details'] = $application_details;

            return view('user_login.edit_returned_application_p', array_merge($viewData, compact(
                'applicationid',
                'user',
                'queries',
                'queryReasonsForValidation',
                'returnRemarks',
                'returnedEditableSections',
                'returnedFormPSectionKeys',
                'returnedIsPartialEdit',
                'isReturnedApplication'
            )));
        }

        $queries = DB::table('tnelb_query_applicable')
            ->where('application_id', $appl_id)
            ->where('query_status', 'P')
            ->orderByDesc('id')
            ->get();

        return view('user_login.edit_application_p', array_merge($viewData, compact('applicationid', 'queries', 'user')));
    }

    public function mergeReturnedPartialSubmitFromDb(Request $request, string $applicationId, array $editableSections): void
    {
        if (ReturnedApplicationEditScope::isFullUnlock($editableSections)) {
            return;
        }

        $form = CC_Forms_Meta::findByApplicationId($applicationId)
            ?: TnelbFormP::where('application_id', $applicationId)->first();
        if (! $form) {
            return;
        }

        $editable = array_flip($editableSections);
        if (! isset($editable[ReturnedApplicationEditScope::SECTION_APPLICANT])) {
            ReturnedApplicationPayloadMerge::mergeFormPApplicantScalarsIntoRequest($request, $form);
        }

        if (! isset($editable[ReturnedApplicationEditScope::SECTION_EDUCATION])) {
            $request->files->remove('education_document');
            ReturnedApplicationPayloadMerge::mergeEducationArraysIntoRequest($request, $applicationId);
            ReturnedApplicationPayloadMerge::mergeFormPInstituteArraysIntoRequest($request, $applicationId);
            $request->files->remove('institute_document');
        }

        if (! isset($editable[ReturnedApplicationEditScope::SECTION_EXPERIENCE])) {
            $request->files->remove('work_document');
            ReturnedApplicationPayloadMerge::mergeExperienceArraysIntoRequest($request, $applicationId, 'W');
        }

        if (! isset($editable[ReturnedApplicationEditScope::SECTION_PHOTO])) {
            $request->files->remove('upload_photo');
        }
        if (! isset($editable[ReturnedApplicationEditScope::SECTION_SIGNATURE])) {
            $request->files->remove('upload_sign');
        }
        if (! isset($editable[ReturnedApplicationEditScope::SECTION_AADHAAR_DOC])) {
            $request->files->remove('aadhaar_doc');
            $request->merge(['aadhaar_doc_removed' => '0']);
        }
        if (! isset($editable[ReturnedApplicationEditScope::SECTION_PAN_DOC])) {
            $request->files->remove('pancard_doc');
            $request->merge(['pancard_doc_removed' => '0']);
        }
    }

    public function delete_institute(Request $request)
    {
        try {
            $id = $request->input('inst_id');
            $institute = TnelbAppsInstitute::find($id);
            if (! $institute) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Institute record not found!',
                ], 404);
            }

            $filePath = $institute->upload_doc ?? $institute->upload_document ?? null;
            if (! empty($filePath)) {
                $absolute = public_path($filePath);
                if (is_file($absolute)) {
                    unlink($absolute);
                }
            }

            $institute->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Institute record deleted successfully!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete institute record.',
            ], 500);
        }
    }

    /**
     * Map returned-edit section constants onto Form P blade `data-section-key` values.
     *
     * @param  list<string>  $editableSections
     * @return list<string>
     */
    private function mapReturnedFormPSectionKeys(array $editableSections): array
    {
        if (ReturnedApplicationEditScope::isFullUnlock($editableSections)) {
            return [];
        }

        $map = [
            ReturnedApplicationEditScope::SECTION_APPLICANT => ['personal', 'contact'],
            ReturnedApplicationEditScope::SECTION_EDUCATION => ['qualifications'],
            ReturnedApplicationEditScope::SECTION_EXPERIENCE => ['qualifications'],
            ReturnedApplicationEditScope::SECTION_PHOTO => ['uploads'],
            ReturnedApplicationEditScope::SECTION_SIGNATURE => ['uploads'],
            ReturnedApplicationEditScope::SECTION_AADHAAR_DOC => ['uploads'],
            ReturnedApplicationEditScope::SECTION_PAN_DOC => ['uploads'],
        ];

        $keys = [];
        foreach ($editableSections as $section) {
            foreach ($map[$section] ?? [] as $key) {
                $keys[] = $key;
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @return array{user_id: mixed, salutation: mixed, applicant_name: string}
     */
    private function formPUserPayload($authUser): array
    {
        return [
            'user_id' => $authUser->login_id,
            'salutation' => $authUser->salutation,
            'applicant_name' => trim((string) ($authUser->first_name.' '.$authUser->last_name)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function loadFormPViewData(string $appl_id): array
    {
        $ccMeta = CC_Forms_Meta::findByApplicationId($appl_id);
        $legacy = TnelbFormP::where('application_id', $appl_id)->first();
        if (! $ccMeta && ! $legacy) {
            return [];
        }
        $hasCcMeta = $ccMeta !== null;
        $application_details = $ccMeta;

        if ($application_details) {
            $application_details = (object) $application_details->toArray();
            $application_details->license_name = $application_details->license_name
                ?? $application_details->certificate_name
                ?? FormPSchema::LICENSE_NAME;
            $application_details->applicants_address = $application_details->applicants_address
                ?? $application_details->applicant_address
                ?? null;
            $application_details->previously_number = $application_details->previously_number
                ?? $application_details->previous_scc_no
                ?? null;
            $application_details->previously_date = $application_details->first_issue_date
                ?? $application_details->previously_date
                ?? null;
            $application_details->id = $application_details->id ?? $application_details->app_id ?? null;
            if ($legacy) {
                $application_details->employer_detail = $application_details->employer_detail ?? $legacy->employer_detail;
                $application_details->previously_date = $application_details->previously_date ?? $legacy->previously_date;
            }
            } else {
            $application_details = $legacy;
        }

        $edu_details = CC_Education::where('application_id', $appl_id)
            ->orderByDesc('year_of_passing')
            ->get();
        // Legacy edu only for old tnelb_form_p rows. CC apps stay on cc_edu (empty is valid).
        if (! $hasCcMeta && $edu_details->isEmpty() && Schema::hasTable('tnelb_applicants_edu')) {
            $edu_details = DB::table('tnelb_applicants_edu')
                ->where('application_id', $appl_id)
                ->orderBy('year_of_passing', 'desc')
                ->get();
        }
        $edu_details = $edu_details->map(function ($edu) {
            if (is_object($edu) && empty($edu->id) && ! empty($edu->edu_id)) {
                $edu->id = $edu->edu_id;
            }

            return $edu;
        });

        $exp_details = CC_Experience::where('application_id', $appl_id)
            ->orderBy('exp_id')
            ->get();
        // Work is optional on Form P. Do not fall back to tnelb_applicants_exp for CC applications.
        if (! $hasCcMeta && $exp_details->isEmpty() && Schema::hasTable('tnelb_applicants_exp')) {
            $expQuery = DB::table('tnelb_applicants_exp')->where('application_id', $appl_id);
            if (Schema::hasColumn('tnelb_applicants_exp', 'exp_id')) {
                $expQuery->orderBy('exp_id');
            }
            $exp_details = $expQuery->get();
        }
        $exp_details = $exp_details->map(function ($exp) {
            if (! is_object($exp)) {
                return $exp;
            }
            if (empty($exp->id) && ! empty($exp->exp_id)) {
                $exp->id = $exp->exp_id;
            }
            if (empty($exp->upload_document) && ! empty($exp->support_document)) {
                $exp->upload_document = $exp->support_document;
            }
            if (empty($exp->company_name) && ! empty($exp->org_name)) {
                $exp->company_name = $exp->org_name;
            }

            return $exp;
        });

        $apps_doc = CC_Proof_doc::where('application_id', $appl_id)->get();
        $certService = app(CompetencyCertificateService::class);
        $license_details = $certService->asLicenseDetails($appl_id, 'P');
        if (! $license_details) {
            $license_details = DB::table('tnelb_license')->where('application_id', $appl_id)->first();
        }

        $proofService = app(FormSProofDocumentService::class);
        $applicant_photo = $proofService->loadPhotoForView($appl_id)
            ?: TnelbApplicantPhoto::where('application_id', $appl_id)->first();
        $applicant_sign = $proofService->loadSignForView($appl_id)
            ?: TnelbApplicantsSign::where('application_id', $appl_id)->first();
        $proof_doc = $applicant_sign;

        if (is_object($application_details)) {
            $aadhaarPath = $proofService->resolveProofPath($appl_id, FormSProofDocumentService::PROOF_AADHAAR);
            if ($aadhaarPath) {
                $application_details->aadhaar_doc = $aadhaarPath;
            }
            $panPath = $proofService->resolveProofPath($appl_id, FormSProofDocumentService::PROOF_PAN);
            if ($panPath) {
                $application_details->pan_doc = $panPath;
                $application_details->pancard_doc = $panPath;
            }
            $aadhaarNo = CC_Proof_doc::where('application_id', $appl_id)->where('proof_type', 'aadhaar')->value('proof_no');
            if (! empty($aadhaarNo) && empty($application_details->aadhaar)) {
                $application_details->aadhaar = $aadhaarNo;
            }
            $panNo = CC_Proof_doc::where('application_id', $appl_id)->where('proof_type', 'pan')->value('proof_no');
            if (! empty($panNo) && empty($application_details->pancard)) {
                $application_details->pancard = $panNo;
            }
        }

        $institutes = Schema::hasTable('tnelb_applicant_institute')
            ? TnelbAppsInstitute::where('application_id', $appl_id)
                ->where(function ($q) {
                    $q->where('institute_status', 1)->orWhereNull('institute_status');
                })
                ->get()
                ->map(function ($row) {
                    $row->from_date = calendar_date_ymd($row->from_date);
                    $row->to_date = calendar_date_ymd($row->to_date);

                    return $row;
                })
            : collect();

        $application = $application_details;

        return compact(
            'application_details',
            'application',
            'edu_details',
            'exp_details',
            'apps_doc',
            'license_details',
            'applicant_photo',
            'applicant_sign',
            'proof_doc',
            'institutes'
        );
    }

    private function findFormPRenewalDraft(string $parentId, string $loginId)
    {
        $ccRows = DB::table(FormPSchema::META_TABLE)
            ->where('old_application', $parentId)
            ->where('appl_type', 'R')
            ->where('login_id', $loginId)
            ->orderByDesc('app_id')
            ->get();
        foreach ($ccRows as $cc) {
            if ($this->formPRenewalDraftIsOpen($cc)) {
                return $cc;
            }
        }

        $legacyRows = TnelbFormP::where('old_application', $parentId)
            ->where('appl_type', 'R')
            ->where('login_id', $loginId)
            ->orderByDesc('id')
            ->get();
        foreach ($legacyRows as $legacy) {
            if ($this->formPRenewalDraftIsOpen($legacy)) {
                return $legacy;
            }
        }

        return null;
    }

    private function formPRenewalDraftIsOpen(object $row): bool
    {
        $pay = strtoupper(trim((string) ($row->payment_status ?? '')));
        if (in_array($pay, ['Y', 'B', 'SUCCESS', 'PAID', 'S', 'PAYMENT'], true)) {
            return false;
        }

        $status = strtoupper(trim((string) ($row->app_status ?? '')));
        if (in_array($status, ['A', 'APPROVED', 'C', 'CANCELLED', 'R', 'REJECTED'], true)) {
            return false;
        }

        return true;
    }

    /**
     * Lock only rows copied from the approved parent. New draft rows stay editable.
     *
     * @param  array<string, mixed>  $viewData
     */
    private function markFormPRenewalInheritedRows(array &$viewData, string $parentId, bool $dataIsChild): void
    {
        $eduDetails = $viewData['edu_details'] ?? collect();
        $parentEdu = $dataIsChild
            ? CC_Education::where('application_id', $parentId)->get()
            : collect();
        foreach ($eduDetails as $edu) {
            $edu->renew_inherited = ! $dataIsChild || $this->formPEducationMatchesParent($edu, $parentEdu);
        }

        $institutes = $viewData['institutes'] ?? collect();
        $parentInstitutes = $dataIsChild && Schema::hasTable('tnelb_applicant_institute')
            ? TnelbAppsInstitute::where('application_id', $parentId)
                ->where(function ($q) {
                    $q->where('institute_status', 1)->orWhereNull('institute_status');
                })
                ->get()
            : collect();
        foreach ($institutes as $institute) {
            $institute->renew_inherited = ! $dataIsChild || $this->formPInstituteMatchesParent($institute, $parentInstitutes);
        }

        $expDetails = $viewData['exp_details'] ?? collect();
        $parentExp = $dataIsChild
            ? CC_Experience::where('application_id', $parentId)->get()
            : collect();
        foreach ($expDetails as $exp) {
            $exp->renew_inherited = ! $dataIsChild || $this->formPWorkMatchesParent($exp, $parentExp);
        }
    }

    private function formPEducationMatchesParent(object $edu, $parentRows): bool
    {
        $level = strtoupper(trim((string) ($edu->educational_level ?? '')));
        $institute = strtoupper(trim((string) ($edu->institute_name ?? '')));
        foreach ($parentRows as $parent) {
            if (
                strtoupper(trim((string) ($parent->educational_level ?? ''))) === $level
                && strtoupper(trim((string) ($parent->institute_name ?? ''))) === $institute
            ) {
                return true;
            }
        }

        return false;
    }

    private function formPInstituteMatchesParent(object $institute, $parentRows): bool
    {
        $name = strtoupper(trim((string) ($institute->institute_name_address ?? '')));
        $from = calendar_date_ymd($institute->from_date ?? null);
        $to = calendar_date_ymd($institute->to_date ?? null);
        foreach ($parentRows as $parent) {
            if (
                strtoupper(trim((string) ($parent->institute_name_address ?? ''))) === $name
                && calendar_date_ymd($parent->from_date ?? null) === $from
                && calendar_date_ymd($parent->to_date ?? null) === $to
            ) {
                return true;
            }
        }

        return false;
    }

    private function formPWorkMatchesParent(object $exp, $parentRows): bool
    {
        $org = strtoupper(trim((string) ($exp->org_name ?? $exp->company_name ?? $exp->emp_cate ?? '')));
        $designation = strtoupper(trim((string) ($exp->designation ?? '')));
        $from = calendar_date_ymd($exp->from_date ?? null);
        foreach ($parentRows as $parent) {
            $parentOrg = strtoupper(trim((string) ($parent->org_name ?? $parent->company_name ?? $parent->emp_cate ?? '')));
            if (
                $parentOrg === $org
                && strtoupper(trim((string) ($parent->designation ?? ''))) === $designation
                && calendar_date_ymd($parent->from_date ?? null) === $from
            ) {
                return true;
            }
        }

        return false;
    }

    private function persistViaFormController(string $method, Request $request, ...$args)
    {
        $months = (array) $request->input('month_of_passing', []);
        $altMonths = (array) $request->input('month_passing', []);
        if ($altMonths !== []) {
            foreach ($altMonths as $key => $value) {
                if (! isset($months[$key]) || $months[$key] === null || $months[$key] === '') {
                    $months[$key] = $value;
                }
            }
            $request->merge(['month_of_passing' => $months]);
        }

        $request->attributes->set('form_p_persist_method', $method);
        $this->lockFormPPersistIdentity($request, ...$args);

        $request->merge([
            'form_name' => FormPSchema::FORM_NAME,
            'license_name' => FormPSchema::LICENSE_NAME,
            'form_id' => FormPSchema::FORM_ID,
        ]);
        $this->fillFormPRenewalLicenseNumber($request, ...$args);
        $request->attributes->set(FormPSchema::VIA_CONTROLLER_ATTR, true);

        if ($reject = $this->rejectIfFormPDigitizationCaptureMissing($request)) {
            return $reject;
        }

        DB::beginTransaction();
        try {
            $response = app(FormController::class)->{$method}($request, ...$args);
            $response = $this->afterPersistSaveInstitutes($request, $response);
            if ($this->persistResponseFailed($response)) {
                DB::rollBack();

                return $response;
            }
            DB::commit();

            return $response;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function lockFormPPersistIdentity(Request $request, ...$args): void
    {
        $applicationId = trim((string) (
            $request->input('application_id')
            ?: ($args[0] ?? '')
        ));
        $existing = null;
        if ($applicationId !== '') {
            $existing = CC_Forms_Meta::findByApplicationId($applicationId, FormPSchema::FORM_NAME)
                ?: TnelbFormP::where('application_id', $applicationId)->first();
        }

        $posted = strtoupper(trim((string) $request->input('appl_type', '')));
        $stored = strtoupper(trim((string) ($existing->appl_type ?? '')));
        $persistMethod = (string) $request->attributes->get('form_p_persist_method', '');
        $isRenewalPersist = in_array($persistMethod, ['draft_renewal_submit', 'draftRenewalSubmit'], true);

        $merge = [];
        if ($isRenewalPersist) {
            $applType = 'R';
            $parentId = trim((string) $request->input('old_application', ''));
            if ($parentId === '' && $stored !== 'R' && $applicationId !== '') {
                $parentId = $applicationId;
            }
            if ($parentId !== '' && ! $request->filled('old_application')) {
                $merge['old_application'] = $parentId;
            }
        } elseif (in_array($stored, ['D', 'R', 'A'], true)) {
            $applType = $stored;
        } elseif ($posted !== '') {
            $applType = $posted;
        } else {
            $applType = $stored;
        }

        if ($applType !== '') {
            $merge['appl_type'] = $applType;
        }
        if ($applicationId !== '' && ! $request->filled('application_id')) {
            $merge['application_id'] = $applicationId;
        }
        if ($merge !== []) {
            $request->merge($merge);
        }
    }

    /**
     * Current issued Form P certificate number for the approved application (parent on renewal).
     */
    private function formPIssuedCertificateNo(string $applicationId, $meta = null): string
    {
        $applicationId = trim($applicationId);
        if ($applicationId === '') {
            return '';
        }

        $certService = app(CompetencyCertificateService::class);
        foreach ([
            $certService->asLicenseDetails($applicationId, FormPSchema::FORM_NAME),
            $certService->asWorkflowLicense($applicationId, FormPSchema::FORM_NAME),
        ] as $cert) {
            $number = trim((string) ($cert->license_number ?? $cert->certificate_no ?? ''));
            if ($number !== '' && $number !== '0') {
                return $number;
            }
        }

        $meta = $meta ?: CC_Forms_Meta::findByApplicationId($applicationId, FormPSchema::FORM_NAME);
        foreach ([
            $meta->certificate_no ?? null,
            $meta->license_number ?? null,
            $meta->wcc_no ?? null,
        ] as $candidate) {
            $number = trim((string) ($candidate ?? ''));
            if ($number !== '' && $number !== '0') {
                return $number;
            }
        }

        if (Schema::hasTable('tnelb_license')) {
            $number = trim((string) (DB::table('tnelb_license')->where('application_id', $applicationId)->value('license_number') ?? ''));
            if ($number !== '' && $number !== '0') {
                return $number;
            }
        }

        return '';
    }

    private function fillFormPRenewalLicenseNumber(Request $request, ...$args): void
    {
        $persistMethod = (string) $request->attributes->get('form_p_persist_method', '');
        if (! in_array($persistMethod, ['draft_renewal_submit', 'draftRenewalSubmit'], true)) {
            return;
        }

        $posted = trim((string) $request->input('license_number', ''));
        if ($posted !== '' && $posted !== '0') {
            return;
        }

        $parentId = trim((string) (
            $request->input('old_application')
            ?: ($args[0] ?? '')
        ));
        $existing = $parentId !== ''
            ? CC_Forms_Meta::findByApplicationId($parentId, FormPSchema::FORM_NAME)
            : null;
        if ($existing && strtoupper(trim((string) ($existing->appl_type ?? ''))) === 'R') {
            $parentId = trim((string) ($existing->old_application ?? ''));
        }

        $issued = $this->formPIssuedCertificateNo($parentId);
        if ($issued !== '') {
            $request->merge(['license_number' => $issued]);
        }
    }

    private function rejectIfFormPDigitizationCaptureMissing(Request $request)
    {
        if (strtoupper(trim((string) $request->input('appl_type', ''))) !== 'D') {
            return null;
        }

        $loginId = trim((string) (Auth::user()->login_id ?? $request->input('login_id', '')));
        $applicationId = trim((string) $request->input('application_id', ''));
        $linker = app(CcDigitizationLinkService::class);
        $tempAppId = $linker->resolveTempAppId(
            $request->input('cc_digitization_temp_id'),
            $loginId,
            $applicationId !== '' ? $applicationId : null,
            FormPSchema::FORM_NAME
        );
        if ($tempAppId) {
            $request->merge(['cc_digitization_temp_id' => $tempAppId]);
        }

        if ($linker->assertCanSave($tempAppId, $loginId, $applicationId !== '' ? $applicationId : null)) {
            return null;
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Please complete digitisation certificate details before saving this application.',
        ], 422);
    }

    private function persistResponseFailed($response): bool
    {
        if (! $response instanceof JsonResponse) {
            return false;
        }
        if ($response->getStatusCode() >= 400) {
            return true;
        }
        $payload = $response->getData(true);

        return ($payload['status'] ?? null) === 'error';
    }

    private function afterPersistSaveInstitutes(Request $request, $response)
    {
        if (! $response instanceof JsonResponse) {
            return $response;
        }

        $payload = $response->getData(true);
        $status = $payload['status'] ?? null;
        if ($status !== 'success' && (int) $status !== 200) {
            return $response;
        }

        $applicationId = trim((string) ($payload['application_id'] ?? $request->input('application_id') ?? ''));
        if ($applicationId === '') {
            return $response;
        }

        try {
            $this->saveInstituteRows($request, $applicationId);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Institute documents could not be saved, so the application was not saved.',
            ], 500);
        }

        return $response;
    }

    private function persistAlterationInstitutes(Request $request, $child): void
    {
        $applicationId = trim((string) ($child->application_id ?? ''));
        if ($applicationId === '') {
            return;
        }

        $this->alterationService->ensureFormPInstitutesOnAlterationChild(
            $child,
            trim((string) $request->input('parent_application_id'))
        );

        if ($this->formPApplicationHasInstitutes($applicationId)) {
            return;
        }

        $this->saveInstituteRows($request, $applicationId);
    }

    private function childLooksLikeFormP($child): bool
    {
        return FormPSchema::isFormP($child->form_name ?? null)
            || FormPSchema::isFormP($child->certificate_name ?? null)
            || (int) ($child->form_id ?? 0) === FormPSchema::FORM_ID;
    }

    private function formPApplicationHasInstitutes(string $applicationId): bool
    {
        if (! Schema::hasTable('tnelb_applicant_institute')) {
            return false;
        }

        return DB::table('tnelb_applicant_institute')
            ->where('application_id', $applicationId)
            ->where(function ($query) {
                $query->where('institute_status', 1)->orWhereNull('institute_status');
            })
            ->exists();
    }

    private function saveInstituteRows(Request $request, string $applicationId): void
    {
        if (! Schema::hasTable('tnelb_applicant_institute')) {
            return;
        }

        $names = (array) $request->input('institute_name_address', []);
        if ($names === []) {
            return;
        }

        $workflowApp = CC_Forms_Meta::findByApplicationId($applicationId);
        $loginId = (string) ($request->input('login_id') ?: Auth::user()->login_id ?? '');
        $parentApplicationId = trim((string) ($workflowApp->old_application ?? ''));
        $loginId = (string) ($request->input('login_id') ?: Auth::user()->login_id ?? '');
        $durations = (array) $request->input('duration', []);
        $fromDates = (array) $request->input('from_date', []);
        $toDates = (array) $request->input('to_date', []);
        $existingIds = (array) $request->input('institute_id', []);
        $existingDocs = (array) $request->input('exist_institute_document', []);
        $removedFlags = (array) $request->input('removed_document_inst', []);
        $keptIds = [];

        foreach ($names as $key => $institute) {
            $institute = trim((string) ($institute ?? ''));
            $from = $this->instituteDateYmd($fromDates[$key] ?? null);
            $to = $this->instituteDateYmd($toDates[$key] ?? null);
            $duration = ($from && $to)
                ? institute_calendar_ymd($from, $to)
                : trim((string) ($durations[$key] ?? ''));

            if ($institute === '' && $from === null && $to === null && $duration === '') {
                        continue;
                    }

            $rowId = isset($existingIds[$key]) ? (int) $existingIds[$key] : 0;
            $row = $rowId > 0
                ? TnelbAppsInstitute::where('id', $rowId)->where('application_id', $applicationId)->first()
                : null;
            if (! $row && $rowId > 0 && $parentApplicationId !== '') {
                $parentRow = TnelbAppsInstitute::where('id', $rowId)
                    ->where('application_id', $parentApplicationId)
                    ->first();
                if ($parentRow) {
                    $row = TnelbAppsInstitute::where('application_id', $applicationId)
                        ->where('institute_name_address', $institute !== '' ? $institute : $parentRow->institute_name_address)
                        ->where(function ($q) {
                            $q->where('institute_status', 1)->orWhereNull('institute_status');
                        })
                        ->where(function ($q) use ($from, $parentRow) {
                            $matchFrom = $from ?? $this->instituteDateYmd($parentRow->from_date);
                            if ($matchFrom) {
                                $q->whereDate('from_date', $matchFrom);
                            } else {
                                $q->whereNull('from_date');
                            }
                        })
                        ->first();
                    if (! $row && empty($existingDocs[$key]) && ! empty($parentRow->upload_doc)) {
                        $existingDocs[$key] = $parentRow->upload_doc;
                    }
                }
            }
            if (! $row) {
                $row = TnelbAppsInstitute::where('application_id', $applicationId)
                    ->where('institute_name_address', $institute)
                    ->where(function ($q) {
                        $q->where('institute_status', 1)->orWhereNull('institute_status');
                    })
                    ->when($from, fn ($q) => $q->whereDate('from_date', $from))
                    ->when($to, fn ($q) => $q->whereDate('to_date', $to))
                    ->first();
            }
            $file = $this->instituteUploadedFile($request, (int) $key);
            $removed = (string) ($removedFlags[$key] ?? '0') === '1';
            $filePath = $row->upload_doc ?? null;
            if ($removed && ! $file) {
                    $filePath = null;
            } elseif (! $file && ! $removed) {
                $keep = trim((string) ($existingDocs[$key] ?? ''));
                if ($keep !== '') {
                    $filePath = $keep;
                }
            }

            $payload = [
                'login_id' => $loginId !== '' ? $loginId : ($row->login_id ?? ''),
                'application_id' => $applicationId,
                'institute_name_address' => $institute,
                'duration' => $duration !== '' ? $duration : null,
                'from_date' => $from,
                'to_date' => $to,
                'institute_status' => 1,
                'upload_doc' => $filePath,
            ];

            if ($row) {
                $row->update($payload);
            } else {
                $row = TnelbAppsInstitute::create($payload);
            }

            if ($file) {
                if (! $workflowApp) {
                    throw new \RuntimeException('The application record was not found, so the institute document was not saved.');
                }
                $stored = $this->documentHandler->handleInstituteDocumentUpload(
                    $workflowApp,
                    (int) $row->id,
                    $file
                );
                if (! $stored) {
                    throw new \RuntimeException('The institute document was not saved.');
                }
                $row->update(['upload_doc' => $stored]);
            }

            $keptIds[] = (int) $row->id;
        }

        if ($keptIds !== []) {
            TnelbAppsInstitute::where('application_id', $applicationId)
                ->whereNotIn('id', $keptIds)
                ->update(['institute_status' => 0]);
        }
    }

    private function instituteDateYmd(mixed $value): ?string
    {
        $ymd = calendar_date_ymd($value);

        return $ymd !== '' ? $ymd : null;
    }

    private function instituteUploadedFile(Request $request, int $key): ?UploadedFile
    {
        if (! $request->hasFile('institute_document')) {
            return null;
        }
        $files = $request->file('institute_document');
        if (! is_array($files)) {
            return $files instanceof UploadedFile && $files->isValid() ? $files : null;
        }
        $uploaded = $files[$key] ?? null;

        return $uploaded instanceof UploadedFile && $uploaded->isValid() ? $uploaded : null;
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

    /**
     * @param  array<string, mixed>  $viewData
     */
    private function alterationInstituteRows(array $viewData)
    {
        if (! Schema::hasTable('tnelb_applicant_institute')) {
            return collect();
        }

        $ownerIds = array_values(array_unique(array_filter([
            $viewData['alteration_draft']->application_id ?? null,
            $viewData['application_details']->application_id ?? null,
        ])));

        foreach ($ownerIds as $ownerId) {
            $rows = DB::table('tnelb_applicant_institute')
                ->where('application_id', $ownerId)
                ->where(function ($query) {
                    $query->where('institute_status', 1)->orWhereNull('institute_status');
                })
                ->orderBy('id')
                ->get();
            if ($rows->isNotEmpty()) {
                return $rows;
            }
        }

        return collect();
    }
}
