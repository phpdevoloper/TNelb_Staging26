<?php

namespace App\Http\Controllers;

use App\Models\Admin\LicenceCategory;
use App\Models\Admin\TnelbFee;
use App\Models\Admin\SupervisorModel;
use App\Models\MstLicence;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbApplicantsSign;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Helpers\RoleHelper;
use App\Models\CC_Doc_Log;
use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Forms_Meta;
use App\Models\CC_Proof_doc;
use App\Models\Competency\CC_CompetencyMeta;
use App\Services\ReturnedApplicationEditScope;
use App\Services\ReturnedApplicationPayloadMerge;
use App\Services\CcDigitizationLinkService;
use App\Services\FormS\FormSDocumentUploadHandler;
use App\Services\FormS\FormSApplicationWorkflowService;
use App\Services\Competency\CompetencyCertificateService;
use App\Services\Competency\CompetencyMetaService;
use App\Services\FormS\FormSProofDocumentService;
use App\Services\FormS\SensitiveProofCryptService;
use App\Services\Competency\CompetencyApplicationService;
use App\Services\Competency\CompetencyWorkflowService;
use Illuminate\Http\UploadedFile;

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

    /** Competency forms S / W / WH / P — meta is per-form; edu/exp/proof use shared cc_* tables. */
    private function isCompetencyForm(?string $formName): bool
    {
        return in_array($formName, ['S', 'W', 'WH', 'P'], true);
    }

    private function formSDocumentHandler(): FormSDocumentUploadHandler
    {
        return app(FormSDocumentUploadHandler::class);
    }

    private function proofDocumentService(): FormSProofDocumentService
    {
        return app(FormSProofDocumentService::class);
    }

    private function loadApplicantPhotoForView(string $applicationId): ?object
    {
        return $this->proofDocumentService()->loadPhotoForView($applicationId);
    }

    private function loadApplicantSignForView(string $applicationId): ?object
    {
        return $this->proofDocumentService()->loadSignForView($applicationId);
    }

    private function saveCompetencyProofDocuments(
        Request $request,
        CC_CompetencyMeta $workflowForm,
        ?string $formName
    ): void {
        if (! $this->isCompetencyForm($formName)) {
            return;
        }

        $masterApplicationId = $this->resolveFormSMasterApplicationId($workflowForm, $formName);
        $appType = (string) ($workflowForm->appl_type ?? '');
        $proofService = $this->proofDocumentService();

        if ($request->filled('aadhaar')) {
            $proofService->syncProofNumber(
                $masterApplicationId,
                $appType,
                FormSProofDocumentService::PROOF_AADHAAR,
                $request->aadhaar
            );
        }

        if ($request->hasFile('aadhaar_doc')) {
            $proofService->saveProofUpload(
                $workflowForm,
                $masterApplicationId,
                $appType,
                FormSProofDocumentService::PROOF_AADHAAR,
                $request->file('aadhaar_doc'),
                $request->aadhaar,
                $formName
            );
        } elseif ($request->input('aadhaar_doc_removed') === '1') {
            $proofService->clearProofDocument($masterApplicationId, FormSProofDocumentService::PROOF_AADHAAR);
        }

        if ($this->isCompetencyForm($formName) && $request->filled('pancard')) {
            $proofService->syncProofNumber(
                $masterApplicationId,
                $appType,
                FormSProofDocumentService::PROOF_PAN,
                $request->pancard
            );
        }

        if ($this->isCompetencyForm($formName) && $request->hasFile('pancard_doc')) {
            $proofService->saveProofUpload(
                $workflowForm,
                $masterApplicationId,
                $appType,
                FormSProofDocumentService::PROOF_PAN,
                $request->file('pancard_doc'),
                $request->pancard,
                $formName
            );
        }

        if ($request->hasFile('upload_photo')) {
            $photoFile = $request->file('upload_photo');
            if (! $photoFile->isValid()) {
                throw new \RuntimeException('Photo upload failed: ' . $photoFile->getErrorMessage());
            }

            $sizeKb = $photoFile->getSize() / 1024;
            if ($sizeKb > 50) {
                throw new \RuntimeException('Photo size permitted up to 50 KB.');
            }

            $proofService->saveProofUpload(
                $workflowForm,
                $masterApplicationId,
                $appType,
                FormSProofDocumentService::PROOF_PHOTO,
                $photoFile,
                null,
                $formName
            );
        }

        if ($request->hasFile('upload_sign')) {
            $signFile = $request->file('upload_sign');
            if (! $signFile->isValid()) {
                throw new \RuntimeException('Signature upload failed: ' . $signFile->getErrorMessage());
            }

            $sizeKb = $signFile->getSize() / 1024;
            if ($sizeKb > 50) {
                throw new \RuntimeException('Signature size permitted up to 50 KB.');
            }

            $proofService->saveProofUpload(
                $workflowForm,
                $masterApplicationId,
                $appType,
                FormSProofDocumentService::PROOF_SIGN,
                $signFile,
                null,
                $formName
            );
        }

        if ($request->input('aadhaar_doc_removed') !== '1') {
            $proofService->ensureProofDocumentEncryptedAtRest(
                $masterApplicationId,
                FormSProofDocumentService::PROOF_AADHAAR
            );
        }

        $proofService->ensureProofDocumentEncryptedAtRest(
            $masterApplicationId,
            FormSProofDocumentService::PROOF_PAN
        );
    }

    private function buildCcFormsMetaPayload(
        Request $request,
        string $applicationId,
        ?CC_CompetencyMeta $existingForm = null,
        array $overrides = []
    ): array {
        return array_merge([
            'login_id'            => $request->login_id,
            'application_id'      => $applicationId,
            'applicant_name'      => $this->resolveApplicantName($request, $existingForm),
            'fathers_name'        => $request->fathers_name ?? $request->Fathers_Name ?? $existingForm?->fathers_name,
            'applicant_email'     => $request->input('applicant_email'),
            'applicant_address'   => $request->applicants_address ?? $existingForm?->applicant_address,
            'd_o_b'               => $request->d_o_b ?? $request->dob ?? $existingForm?->d_o_b,
            'age'                 => $request->age ?? $existingForm?->age,
            'previous_scc_no'     => $request->previously_number ?? $existingForm?->previous_scc_no ?? 0,
            'first_issue_date'    => $request->previously_issue_date ?: null,
            'scc_from_date'       => $request->previously_valid_from ?: null,
            'scc_to_date'         => $request->previously_valid_to ?: ($request->previously_date ?: null),
            'form_name'           => $request->form_name,
            'form_id'             => $request->form_id,
            'certificate_name'    => $request->license_name ?? $existingForm?->certificate_name,
            'wcc_no'              => $request->competency_certificate_no ?? $existingForm?->wcc_no,
            'wcc_to'              => $request->certificate_valid_to ?: ($request->certificate_date ?: null),
            'wcc_issue_date'      => $request->certificate_issue_date ?: null,
            'wcc_from'            => $request->certificate_valid_from ?: null,
            'appl_type'           => $request->appl_type ?? $existingForm?->appl_type,
            'app_status'          => 'P',
            'old_application'     => $existingForm?->old_application ?? $request->input('old_application'),
            'submitted_date'      => $this->dbNow,
            'updated_at'          => $this->dbNow,
        ], $overrides);
    }

    private function formSMasterApplicationId(CC_CompetencyMeta $workflowForm): string
    {
        return app(FormSApplicationWorkflowService::class)
            ->masterApplication($workflowForm)
            ->application_id;
    }

    private function resolveFormSMasterApplicationId(CC_CompetencyMeta $workflowForm, ?string $formName): string
    {
        if ($this->isCompetencyForm($formName)) {
            return $this->formSMasterApplicationId($workflowForm);
        }

        return $workflowForm->application_id;
    }

    private function resolveFormSMasterApplicationIdFromWorkflow(
        ?CC_CompetencyMeta $workflowForm,
        ?string $formName,
        string $fallbackApplicationId
    ): string {
        if ($workflowForm instanceof CC_CompetencyMeta) {
            return $this->resolveFormSMasterApplicationId($workflowForm, $formName);
        }

        return $fallbackApplicationId;
    }

    /**
     * @return class-string<CC_Experience>
     */
    private function resolveExperienceModelClass(?CC_CompetencyMeta $workflowForm, ?string $formName): string
    {
        return CC_Experience::class;
    }

    /**
     * Resolve education certificate upload for versioned document storage only.
     *
     * @return array{path: ?string, pending_file: ?UploadedFile}
     */
    private function resolveEducationDocumentForSave(
        Request $request,
        $key,
        ?CC_CompetencyMeta $workflowForm,
        ?string $formName,
        CC_Education|null $existingEducation,
        bool $isFileRemoved
    ): array {
        $file = $this->resolveEducationUploadFileFromRequest($request, $key);

        if ($isFileRemoved && ! $file) {
            return ['path' => null, 'pending_file' => null];
        }

        if ($file) {
            return [
                'path' => $existingEducation?->upload_document,
                'pending_file' => $file,
            ];
        }

        $existingInput = $request->input('existing_document.'.$key);
        if ($existingInput !== null && $existingInput !== ''
            && $this->isValidCompetencyEducationDocPath($existingInput)) {
            return ['path' => $existingInput, 'pending_file' => null];
        }

        return [
            'path' => $existingEducation?->upload_document,
            'pending_file' => null,
        ];
    }

    private function resolveEducationUploadFileFromRequest(Request $request, $key): ?UploadedFile
    {
        $directFile = $request->file('education_document.'.$key);
        if ($directFile && $directFile->isValid()) {
            return $directFile;
        }

        $indexed = $request->file('education_document');
        if (is_array($indexed) && isset($indexed[$key])) {
            $candidate = $indexed[$key];
            if ($candidate && $candidate->isValid()) {
                return $candidate;
            }
        }

        return null;
    }

    private function isValidCompetencyEducationDocPath(?string $path): bool
    {
        return $this->isValidCompetencyAjaxDocPath($path, 'education');
    }

    private function applyPendingFormSEducationUpload(
        Request $request,
        $key,
        CC_CompetencyMeta $workflowForm,
        CC_Education $education,
        UploadedFile $file
    ): ?string {
        $reasons = $request->input('education_replacement_reason');
        $reason = is_array($reasons)
            ? ($reasons[$key] ?? null)
            : $request->input('education_replacement_reason.' . $key);

        return $this->formSDocumentHandler()->handleEducationUpload(
            $workflowForm,
            $education,
            $file,
            $reason
        );
    }

    private function seedFormSDocumentsIfRenewal(CC_CompetencyMeta $workflowForm, ?string $formName): void
    {
        if (! $this->isCompetencyForm($formName)) {
            return;
        }

        $this->formSDocumentHandler()->seedCarriedForwardIfRenewal($workflowForm);
    }

    private function formErrorResponse(\Throwable $e, string $message = 'Something went wrong. Please try again!', int $status = 500)
    {
        Log::error($message, [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], $status);
    }

    private const FORM_S_BOARD_MEMBER_EMP_TYPE = 'board_member_tnelb';

    private function isFormSBoardMemberSwitchYes(Request $request): bool
    {
        if (strtoupper((string) ($request->form_name ?? '')) !== 'S') {
            return false;
        }

        return strtolower(trim((string) $request->input('current_work_board_member', 'no'))) === 'yes';
    }

    private function requestHasFormSBoardMemberWorkExperience(Request $request): bool
    {
        if (! $this->isFormSBoardMemberSwitchYes($request)) {
            return false;
        }

        $types = $request->input('work_employment_type', []);
        if (! is_array($types)) {
            return false;
        }

        foreach ($types as $type) {
            if (strtolower(trim((string) $type)) === self::FORM_S_BOARD_MEMBER_EMP_TYPE) {
                return true;
            }
        }

        return false;
    }

    private function isFormSBoardMemberFeeExempt(Request $request): bool
    {
        return $this->requestHasFormSBoardMemberWorkExperience($request);
    }

    /** @deprecated Use isFormSBoardMemberFeeExempt */
    private function isFormSRenewalBoardMemberFeeExempt(Request $request): bool
    {
        return $this->isFormSBoardMemberFeeExempt($request);
    }

    /**
     * Server-side validation for board member work rows (Form S).
     */
    private function validateFormSBoardMemberWorkRows(Request $request): ?string
    {
        if (! $this->isFormSBoardMemberSwitchYes($request)) {
            return null;
        }

        foreach ($this->getWorkRowIndexes($request) as $key) {
            $empType = strtolower(trim((string) ($request->work_employment_type[$key] ?? '')));
            if ($empType !== self::FORM_S_BOARD_MEMBER_EMP_TYPE) {
                continue;
            }

            if (trim((string) ($request->work_board_meeting_details[$key] ?? '')) === '') {
                return 'Details of the meeting is required when Board member employment type is selected.';
            }

            if (trim((string) ($request->work_board_meeting_date[$key] ?? '')) === '') {
                return 'Date of Meeting is required when Board member employment type is selected.';
            }

            $supportRemoved = isset($request->removed_document_work[$key])
                && (string) $request->removed_document_work[$key] === '1';
            $existingDoc = trim((string) ($request->existing_work_document[$key] ?? ''));
            $hasNewDoc = isset($request->file('work_document')[$key])
                && $request->file('work_document')[$key]->isValid();

            if ($supportRemoved || ($existingDoc === '' && ! $hasNewDoc)) {
                return 'Supporting document is required when Board member employment type is selected.';
            }
        }

        return null;
    }

    private function hasWorkExperiencePayload(Request $request): bool
    {
        return $request->has('work_level')
            || $request->has('work_employer_name')
            || $request->has('work_employment_type')
            || $request->has('designation');
    }

    /**
     * When Form S 7b switch is "No", hidden 7b/current row inputs may still post empty
     * legacy fields (work_level/experience/designation). Prune those indexes before
     * validator "required.*" rules run.
     */
    private function pruneHiddenFormSCurrentSectionLegacyRows(Request $request): void
    {
        if (strtoupper((string) ($request->form_name ?? '')) !== 'S') {
            return;
        }
        if ($this->isFormSBoardMemberSwitchYes($request)) {
            return;
        }

        $sections = $request->input('work_exp_section', []);
        if (!is_array($sections) || empty($sections)) {
            return;
        }

        $skipIndexes = [];
        foreach ($sections as $idx => $section) {
            if (strtolower(trim((string) $section)) === 'current') {
                $skipIndexes[] = $idx;
            }
        }
        if (empty($skipIndexes)) {
            return;
        }

        foreach (['work_level', 'experience', 'designation'] as $field) {
            $values = $request->input($field, null);
            if (!is_array($values)) {
                continue;
            }
            foreach ($skipIndexes as $idx) {
                unset($values[$idx]);
            }
            $request->merge([$field => array_values($values)]);
        }
    }

    private function getWorkRowIndexes(Request $request): array
    {
        $indexes = [];
        foreach ([
            'work_level',
            'work_employer_name',
            'work_employment_type',
            'work_organisation_address',
            'work_contractor_category',
            'work_nature_of_work',
            'work_board_meeting_details',
            'work_board_meeting_date',
            'work_date_from',
            'designation',
            'experience',
            'work_experience_total',
        ] as $field) {
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

    /**
     * Calendar-style years / months / days between two dates (Form S only; aligned with apply-form-s `calendarDiffYMD`).
     *
     * @return array{y:int,m:int,d:int}|null
     */
    private function workExperienceCalendarYmd(?string $fromRaw, ?string $toRaw): ?array
    {
        if ($fromRaw === null || $fromRaw === '' || $toRaw === null || $toRaw === '') {
            return null;
        }

        try {
            $from = Carbon::parse($fromRaw)->startOfDay();
            $to = Carbon::parse($toRaw)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }

        if ($to->lt($from)) {
            return null;
        }

        $y = $to->year - $from->year;
        $m = $to->month - $from->month;
        $d = $to->day - $from->day;

        if ($d < 0) {
            $m--;
            $d += Carbon::create($to->year, $to->month, 1)->subDay()->day;
        }
        if ($m < 0) {
            $y--;
            $m += 12;
        }
        if ($d < 0) {
            $m--;
            if ($m < 0) {
                $y--;
                $m += 12;
            }
            $d += Carbon::create($to->year, $to->month, 1)->subDay()->day;
        }

        return [
            'y' => (int) $y,
            'm' => (int) $m,
            'd' => (int) $d,
        ];
    }

    /**
     * Duration columns for `tnelb_applicants_exp`: Form S calendar Y/M/D (when flagged);
     * `total_exp` for Form S, W, WH, and P when flagged and the column exists.
     *
     * @param  array  $workRow  Output of mapWorkExperienceRow()
     * @return array<string, mixed>
     */
    private function mstExperienceDurationForDb(array $workRow): array
    {
        static $hasTotalExpColumn = null;
        if ($hasTotalExpColumn === null) {
            $hasTotalExpColumn = Schema::hasColumn('tnelb_applicants_exp', 'total_exp');
        }

        $out = [];
        if (!empty($workRow['store_work_duration_ymd'])) {
            $out['total_y'] = $workRow['total_y'];
            $out['total_m'] = $workRow['total_m'];
            $out['total_d'] = $workRow['total_d'];
        }
        if ($hasTotalExpColumn && !empty($workRow['store_total_exp'])) {
            $out['total_exp'] = format_total_exp_years($workRow['total_exp']);
        }

        return $out;
    }

    /**
     * Form S contractor category + licence share `emp_cate` (no separate licence column).
     *
     * @return array{category: ?string, licence: ?string}
     */
    private function decodeFormSContractorEmpCate(?string $stored): array
    {
        if ($stored === null || $stored === '') {
            return ['category' => null, 'licence' => null];
        }
        if (str_contains($stored, '||')) {
            $parts = explode('||', $stored, 2);

            return [
                'category' => (($parts[0] ?? '') !== '') ? $parts[0] : null,
                'licence' => (($parts[1] ?? '') !== '') ? $parts[1] : null,
            ];
        }

        return ['category' => $stored, 'licence' => null];
    }

    private function encodeFormSContractorEmpCate(?string $category, ?string $licence): ?string
    {
        $category = $category !== null ? trim($category) : '';
        $licence = $licence !== null ? trim($licence) : '';
        if ($category === '' && $licence === '') {
            return null;
        }
        if ($licence === '') {
            return $category;
        }
        if ($category === '') {
            return '||'.$licence;
        }

        return $category.'||'.$licence;
    }

    /**
     * @return array<string, mixed>
     */
    private function mstExperienceRowToDbPayload(array $workRow, array $documents = []): array
    {
        $orgName = $workRow['org_name'] ?? $workRow['company_name'] ?? null;

        $payload = [
            'emp_type' => $workRow['emp_type'] ?? null,
            'emp_cate' => $workRow['emp_cate'] ?? null,
            'org_name' => ($orgName !== null && $orgName !== '') ? $orgName : null,
            'org_address' => $workRow['org_address'] ?? null,
            'from_date' => $workRow['from_date'] ?? null,
            'to_date' => $workRow['to_date'] ?? null,
            'designation' => ($workRow['designation'] ?? '') !== '' ? $workRow['designation'] : null,
            'nature_work' => $workRow['nature_work'] ?? null,
            'voltage_level' => $workRow['voltage_level'] ?? null,
            'transformer_kva' => $workRow['transformer_kva'] ?? null,
            ...$this->mstExperienceDurationForDb($workRow),
        ];

        if (Schema::hasColumn('tnelb_applicants_exp', 'intimation_date')
            && array_key_exists('intimation_date', $workRow)) {
            $payload['intimation_date'] = $workRow['intimation_date'];
        }

        if (Schema::hasColumn('tnelb_applicants_exp', 'board_meeting_details')
            && array_key_exists('board_meeting_details', $workRow)) {
            $payload['board_meeting_details'] = $workRow['board_meeting_details'];
        }
        if (Schema::hasColumn('tnelb_applicants_exp', 'board_meeting_date')
            && array_key_exists('board_meeting_date', $workRow)) {
            $payload['board_meeting_date'] = $workRow['board_meeting_date'];
        }

        if (array_key_exists('support_document', $documents)) {
            $payload['support_document'] = $documents['support_document'];
        }
        if (array_key_exists('releive_document', $documents)) {
            $payload['releive_document'] = $documents['releive_document'];
        }

        return $payload;
    }

    /**
     * @return array{support_document: ?string, releive_document: ?string, pending_support_upload: ?UploadedFile, pending_relieve_upload: ?UploadedFile}
     */
    private function resolveWorkRowDocuments(
        Request $request,
        $key,
        CC_Experience|null $existing,
        bool $supportRemoved,
        bool $relieveRemoved,
        ?CC_CompetencyMeta $workflowForm = null,
        ?string $formName = null
    ): array {
        $useVersioned = $workflowForm
            && $this->formSDocumentHandler()->usesVersionedStorage($formName);
        $pendingSupportUpload = null;
        $pendingRelieveUpload = null;

        $support = null;
        $existingSupport = $request->existing_work_document[$key] ?? null;
        if (! $supportRemoved && $existingSupport !== null && $existingSupport !== ''
            && $this->isValidCompetencyAjaxDocPath($existingSupport, 'work')) {
            $support = $existingSupport;
        }
        if (isset($request->file('work_document')[$key])) {
            $file = $request->file('work_document')[$key];
            if ($file && $file->isValid()) {
                if ($useVersioned) {
                    $pendingSupportUpload = $file;
                    if ($support === null && $existing !== null && ! $supportRemoved) {
                        $support = $existing->support_document ?? $existing->upload_document;
                    }
                } else {
                    $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                    $file->move(public_path('work_experience'), $filename);
                    $support = 'work_experience/'.$filename;
                }
            }
        }
        if ($support === null && $existing !== null && ! $supportRemoved) {
            $support = $existing->support_document ?? $existing->upload_document;
        }

        $relieve = null;
        $existingRelieve = $request->existing_work_relieving_document[$key] ?? null;
        if (! $relieveRemoved && $existingRelieve !== null && $existingRelieve !== ''
            && $this->isValidCompetencyAjaxDocPath($existingRelieve, 'work')) {
            $relieve = $existingRelieve;
        }
        if (isset($request->file('work_relieving_letter')[$key])) {
            $file = $request->file('work_relieving_letter')[$key];
            if ($file && $file->isValid()) {
                if ($useVersioned) {
                    $pendingRelieveUpload = $file;
                    if ($relieve === null && $existing !== null && ! $relieveRemoved) {
                        $relieve = $existing->releive_document;
                    }
                } else {
                    $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
                    $file->move(public_path('work_experience'), $filename);
                    $relieve = 'work_experience/'.$filename;
                }
            }
        }
        if ($relieve === null && $existing !== null && ! $relieveRemoved) {
            $relieve = $existing->releive_document;
        }

        return [
            'support_document' => $supportRemoved ? null : $support,
            'releive_document' => $relieveRemoved ? null : $relieve,
            'pending_support_upload' => $pendingSupportUpload,
            'pending_relieve_upload' => $pendingRelieveUpload,
        ];
    }

    private function applyPendingFormSExperienceRelieveUpload(
        Request $request,
        $key,
        CC_CompetencyMeta $workflowForm,
        CC_Experience $experience,
        UploadedFile $file
    ): ?string {
        $reasons = $request->input('experience_replacement_reason');
        $reason = is_array($reasons)
            ? ($reasons[$key] ?? null)
            : $request->input('experience_replacement_reason.' . $key);

        return $this->formSDocumentHandler()->handleExperienceRelieveUpload(
            $workflowForm,
            $experience,
            $file,
            $reason
        );
    }

    private function applyPendingFormSExperienceDocumentUploads(
        Request $request,
        $key,
        CC_CompetencyMeta $workflowForm,
        CC_Experience $experience,
        array $documents
    ): void {
        if (! empty($documents['pending_support_upload'])) {
            $path = $this->applyPendingFormSExperienceSupportUpload(
                $request,
                $key,
                $workflowForm,
                $experience,
                $documents['pending_support_upload']
            );
            if ($path !== null) {
                $experience->update(['support_document' => $path]);
            }
        }

        if (! empty($documents['pending_relieve_upload'])) {
            $path = $this->applyPendingFormSExperienceRelieveUpload(
                $request,
                $key,
                $workflowForm,
                $experience,
                $documents['pending_relieve_upload']
            );
            if ($path !== null) {
                $experience->update(['releive_document' => $path]);
            }
        }
    }

    private function applyPendingFormSExperienceSupportUpload(
        Request $request,
        $key,
        CC_CompetencyMeta $workflowForm,
        CC_Experience $experience,
        UploadedFile $file
    ): ?string {
        $reasons = $request->input('experience_replacement_reason');
        $reason = is_array($reasons)
            ? ($reasons[$key] ?? null)
            : $request->input('experience_replacement_reason.' . $key);

        return $this->formSDocumentHandler()->handleExperienceSupportUpload(
            $workflowForm,
            $experience,
            $file,
            $reason
        );
    }

    private function mapWorkExperienceRow(Request $request, $key, ?string $formName): array
    {
        $normalizedForm = strtoupper((string) $formName);
        $isFormS = $normalizedForm === 'S';
        /** Form S, W, WH, P: persist decimal years to `total_exp` (uses `work_experience_total[]` first). */
        $storesTotalExp = in_array($normalizedForm, ['S', 'W', 'WH', 'P'], true);

        $orgName = $isFormS
            ? trim((string) ($request->work_employer_name[$key] ?? $request->work_level[$key] ?? ''))
            : trim((string) ($request->work_level[$key] ?? ''));

        $orgAddress = $isFormS
            ? trim((string) ($request->work_organisation_address[$key] ?? ''))
            : '';

        $experience = $storesTotalExp
            ? trim((string) ($request->work_experience_total[$key] ?? $request->experience[$key] ?? ''))
            : trim((string) ($request->experience[$key] ?? ''));

        $designation = trim((string) ($request->designation[$key] ?? ''));
        $empType = trim((string) ($request->work_employment_type[$key] ?? ''));
        $fromDate = trim((string) ($request->work_date_from[$key] ?? ''));
        $toDate = trim((string) ($request->work_date_to[$key] ?? ''));
        $intimationDate = trim((string) ($request->work_intimation_date[$key] ?? ''));
        $boardMeetingDetails = '';
        $boardMeetingDate = '';
        if ($isFormS && strtolower($empType) === self::FORM_S_BOARD_MEMBER_EMP_TYPE) {
            $boardMeetingDetails = trim((string) ($request->work_board_meeting_details[$key] ?? ''));
            $boardMeetingDate = trim((string) ($request->work_board_meeting_date[$key] ?? ''));
        }

        $natureWork = $isFormS ? trim((string) ($request->work_nature_of_work[$key] ?? '')) : '';
        $voltageLevel = $isFormS ? trim((string) ($request->work_voltage_level[$key] ?? '')) : '';
        $kvaRaw = $isFormS ? trim((string) ($request->work_transformer_kva[$key] ?? '')) : '';

        $empCate = null;
        if ($isFormS && strtolower($empType) === 'electrical_contractor') {
            $empCate = $this->encodeFormSContractorEmpCate(
                trim((string) ($request->work_contractor_category[$key] ?? '')),
                trim((string) ($request->work_licence_number[$key] ?? ''))
            );
        } elseif (! $isFormS && $orgName !== '') {
            $empCate = $orgName;
        }

        if ($isFormS) {
            $tillFlags = $request->work_to_till_date ?? [];
            $isTill = isset($tillFlags[$key]) && (string) $tillFlags[$key] === '1';
            if ($isTill) {
                $toDate = '';
            }
            $intimationDate = '';
        } elseif (strtolower($empType) !== 'contractor') {
            $intimationDate = '';
        }

        $ymd = $isFormS
            ? $this->workExperienceCalendarYmd(
                $fromDate !== '' ? $fromDate : null,
                $toDate !== '' ? $toDate : null
            )
            : null;

        return [
            'org_name' => $orgName,
            'org_address' => ($orgAddress !== '' ? $orgAddress : null),
            'company_name' => $orgName,
            'experience' => $experience,
            'designation' => $designation,
            'emp_type' => ($empType !== '' ? $empType : null),
            'emp_cate' => $empCate,
            'nature_work' => ($natureWork !== '' ? $natureWork : null),
            'voltage_level' => ($voltageLevel !== '' ? $voltageLevel : null),
            'transformer_kva' => ($kvaRaw !== '' ? $kvaRaw : null),
            'from_date' => ($fromDate !== '' ? $fromDate : null),
            'to_date' => ($toDate !== '' ? $toDate : null),
            'intimation_date' => ($intimationDate !== '' ? $intimationDate : null),
            'board_meeting_details' => ($boardMeetingDetails !== '' ? $boardMeetingDetails : null),
            'board_meeting_date' => ($boardMeetingDate !== '' ? $boardMeetingDate : null),
            'total_exp' => ($experience !== '' ? $experience : null),
            'total_y' => $isFormS ? ($ymd['y'] ?? null) : null,
            'total_m' => $isFormS ? ($ymd['m'] ?? null) : null,
            'total_d' => $isFormS ? ($ymd['d'] ?? null) : null,
            'store_work_duration_ymd' => $isFormS,
            'store_total_exp' => $storesTotalExp,
            'is_empty' => ($orgName === '' && $experience === '' && $designation === ''),
        ];
    }

    /**
     * Upsert one work-experience row for draft saves. When work_id[] is absent (apply-form-s after
     * preview), match an existing row by natural key so Save as Draft does not insert duplicates.
     */
    private function upsertWorkExperienceDraftRow(
        Request $request,
        $key,
        string $loginId,
        string $applicationId,
        array $workRow,
        array &$claimedWorkIds,
        bool $requireAllFields = true,
        ?CC_CompetencyMeta $workflowForm = null,
        ?string $formName = null
    ): void {
        $experienceModel = $this->resolveExperienceModelClass($workflowForm, $formName);
        $masterApplicationId = $this->resolveFormSMasterApplicationIdFromWorkflow(
            $workflowForm,
            $formName,
            $applicationId
        );

        $orgName = $workRow['org_name'] ?? $workRow['company_name'] ?? '';
        $expYears = $workRow['experience'];
        $designation = $workRow['designation'];

        if ($requireAllFields) {
            if ($orgName === '' || $expYears === '' || $designation === '') {
                return;
            }
        } elseif ($orgName === '' && $expYears === '' && $designation === '') {
            return;
        }

        $workId = trim((string) ($request->work_id[$key] ?? ''));
        $work = ($workId !== '') ? $experienceModel::find($workId) : null;
        if ($work && (string) $work->application_id !== (string) $masterApplicationId) {
            $work = null;
        }

        $supportRemoved = isset($request->removed_document_work[$key]) && $request->removed_document_work[$key] == '1';
        $relieveRemoved = isset($request->removed_document_work_relieving[$key])
            && $request->removed_document_work_relieving[$key] == '1';

        $documents = $this->resolveWorkRowDocuments(
            $request,
            $key,
            $work,
            $supportRemoved,
            $relieveRemoved,
            $workflowForm,
            $formName
        );
        $rowPayload = $this->mstExperienceRowToDbPayload($workRow);

        if ($work) {
            $claimedWorkIds[] = (int) $work->getKey();
            $updateDocs = [];
            if ($documents['support_document'] !== null || $supportRemoved) {
                $updateDocs['support_document'] = $documents['support_document'];
            }
            if ($documents['releive_document'] !== null || $relieveRemoved) {
                $updateDocs['releive_document'] = $documents['releive_document'];
            }
            $work->update(array_merge($rowPayload, $updateDocs));

            if (! empty($documents['pending_support_upload']) || ! empty($documents['pending_relieve_upload'])) {
                if ($workflowForm && $work instanceof CC_Experience) {
                    $this->applyPendingFormSExperienceDocumentUploads(
                        $request,
                        $key,
                        $workflowForm,
                        $work->fresh(),
                        $documents
                    );
                }
            }

            return;
        }

        $existing = null;
        $matchOrg = $workRow['org_name'] ?? $workRow['company_name'] ?? null;
        if ($matchOrg !== null && $matchOrg !== '') {
            $matchQuery = $experienceModel::where('login_id', $loginId)
                ->where('application_id', $masterApplicationId)
                ->where('org_name', $matchOrg);

            if ($workRow['emp_type'] !== null) {
                $matchQuery->where('emp_type', $workRow['emp_type']);
            }
            if ($workRow['from_date'] !== null) {
                $matchQuery->whereDate('from_date', $workRow['from_date']);
            }
            if ($workRow['to_date'] !== null) {
                $matchQuery->whereDate('to_date', $workRow['to_date']);
            }
            if (! empty($claimedWorkIds)) {
                $matchQuery->whereNotIn('exp_id', $claimedWorkIds);
            }

            $existing = $matchQuery->first();
        }

        if ($existing) {
            $claimedWorkIds[] = (int) $existing->getKey();
            $updateDocs = [];
            if ($documents['support_document'] !== null) {
                $updateDocs['support_document'] = $documents['support_document'];
            } elseif ($supportRemoved) {
                $updateDocs['support_document'] = null;
            } elseif (! $supportRemoved && $existing->support_document) {
                $updateDocs['support_document'] = $existing->support_document;
            } elseif (! $supportRemoved && $existing->upload_document) {
                $updateDocs['support_document'] = $existing->upload_document;
            }
            if ($documents['releive_document'] !== null) {
                $updateDocs['releive_document'] = $documents['releive_document'];
            } elseif ($relieveRemoved) {
                $updateDocs['releive_document'] = null;
            } elseif (! $relieveRemoved && $existing->releive_document) {
                $updateDocs['releive_document'] = $existing->releive_document;
            }

            $existing->update(array_merge($rowPayload, $updateDocs));

            if (! empty($documents['pending_support_upload']) || ! empty($documents['pending_relieve_upload'])) {
                if ($workflowForm && $existing instanceof CC_Experience) {
                    $this->applyPendingFormSExperienceDocumentUploads(
                        $request,
                        $key,
                        $workflowForm,
                        $existing->fresh(),
                        $documents
                    );
                }
            }

            return;
        }

        $created = $experienceModel::create(array_merge(
            $rowPayload,
            [
                'login_id' => $loginId,
                'application_id' => $masterApplicationId,
                'support_document' => $documents['support_document'],
                'releive_document' => $documents['releive_document'],
            ]
        ));

        $claimedWorkIds[] = (int) $created->getKey();

        if (! empty($documents['pending_support_upload']) || ! empty($documents['pending_relieve_upload'])) {
            if ($workflowForm && $created instanceof CC_Experience) {
                $this->applyPendingFormSExperienceDocumentUploads(
                    $request,
                    $key,
                    $workflowForm,
                    $created->fresh(),
                    $documents
                );
            }
        }
    }

    /**
     * Renewal / competency flows that upsert work rows by natural key (org + dates for Form S).
     */
    private function persistWorkExperienceUpdateOrCreate(
        Request $request,
        string $loginId,
        string $applicationId,
        ?string $formName,
        ?CC_CompetencyMeta $workflowForm = null
    ): void {
        if (! $this->hasWorkExperiencePayload($request)) {
            return;
        }

        $isFormS = strtoupper((string) $formName) === 'S';
        $experienceModel = $this->resolveExperienceModelClass($workflowForm, $formName);
        $masterApplicationId = $this->resolveFormSMasterApplicationIdFromWorkflow(
            $workflowForm,
            $formName,
            $applicationId
        );

        foreach ($this->getWorkRowIndexes($request) as $key) {
            $workRow = $this->mapWorkExperienceRow($request, $key, $formName);
            $orgName = $workRow['org_name'] ?? $workRow['company_name'] ?? '';
            $expYears = $workRow['experience'] ?? '';
            $designation = $workRow['designation'] ?? '';

            $supportRemoved = isset($request->removed_document_work[$key]) && $request->removed_document_work[$key] == '1';
            $relieveRemoved = isset($request->removed_document_work_relieving[$key])
                && $request->removed_document_work_relieving[$key] == '1';

            $existingRow = null;
            $workId = trim((string) ($request->work_id[$key] ?? ''));
            if ($workId !== '') {
                $existingRow = $experienceModel::find($workId);
                if ($existingRow && (string) $existingRow->application_id !== (string) $masterApplicationId) {
                    $existingRow = null;
                }
            }

            $documents = $this->resolveWorkRowDocuments(
                $request,
                $key,
                $existingRow,
                $supportRemoved,
                $relieveRemoved,
                $workflowForm,
                $formName
            );

            $hasAnyData = $orgName !== '' || $expYears !== '' || $designation !== ''
                || ! empty($documents['support_document']) || ! empty($documents['releive_document']);
            if (! $hasAnyData) {
                continue;
            }

            $identity = [
                'login_id' => $loginId,
                'application_id' => $masterApplicationId,
            ];

            if ($workId !== '' && $existingRow) {
                $identity = ['exp_id' => $existingRow->getKey()];
            } elseif ($isFormS && $orgName !== '') {
                $identity['org_name'] = $orgName;
                if (! empty($workRow['from_date'])) {
                    $identity['from_date'] = $workRow['from_date'];
                }
            } elseif ($orgName !== '') {
                $identity['emp_cate'] = $orgName;
            } elseif (! empty($workRow['emp_cate'])) {
                $identity['emp_cate'] = $workRow['emp_cate'];
            }

            $experience = $experienceModel::updateOrCreate(
                $identity,
                $this->mstExperienceRowToDbPayload($workRow, $documents)
            );

            if (! empty($documents['pending_support_upload']) || ! empty($documents['pending_relieve_upload'])) {
                if ($workflowForm && $experience instanceof CC_Experience) {
                    $this->applyPendingFormSExperienceDocumentUploads(
                        $request,
                        $key,
                        $workflowForm,
                        $experience->fresh(),
                        $documents
                    );
                }
            }
        }
    }

    /**
     * Form W / WH / P — work experience is optional; if any field in a row is filled, require the full row including dates.
     */
    private function validateOptionalCompetencyWorkRows(Request $request, \Illuminate\Validation\Validator $validator): void
    {
        if (($request->form_name ?? '') === 'W') {
            return;
        }

        $levels = is_array($request->work_level ?? null) ? $request->work_level : [];
        $exps = is_array($request->experience ?? null) ? $request->experience : [];
        $designations = is_array($request->designation ?? null) ? $request->designation : [];
        $fromDates = is_array($request->work_date_from ?? null) ? $request->work_date_from : [];
        $toDates = is_array($request->work_date_to ?? null) ? $request->work_date_to : [];

        $sections = is_array($request->work_exp_section ?? null) ? $request->work_exp_section : [];
        $boardMemberGate = strtolower((string) ($request->input('current_work_board_member') ?? 'no'));

        $max = max(
            count($levels),
            count($exps),
            count($designations),
            count($fromDates),
            count($toDates),
            count($sections)
        );

        for ($i = 0; $i < $max; $i++) {
            $section = strtolower(trim((string) ($sections[$i] ?? '')));
            // 7b row exists in DOM even when board-member gate is "No".
            // Ignore that hidden "current" row entirely in this mode.
            if (($request->form_name ?? '') === 'S' && $boardMemberGate !== 'yes' && $section === 'current') {
                continue;
            }

            $wl = trim((string) ($levels[$i] ?? ''));
            $ex = trim((string) ($exps[$i] ?? ''));
            $des = trim((string) ($designations[$i] ?? ''));
            $from = trim((string) ($fromDates[$i] ?? ''));
            $to = trim((string) ($toDates[$i] ?? ''));
            $isFormSCurrent = (($request->form_name ?? '') === 'S' && $section === 'current');

            $any = ($wl !== '' || $ex !== '' || $des !== '' || $from !== '' || $to !== '');
            if (! $any) {
                continue;
            }

            if ($wl === '') {
                $validator->errors()->add("work_level.$i", 'Work level is required.');
            }
            if ($des === '') {
                $validator->errors()->add("designation.$i", 'Designation is required.');
            }
            if (! $isFormSCurrent) {
                if ($from === '') {
                    $validator->errors()->add("work_date_from.$i", 'From date is required.');
                }
                if ($to === '') {
                    $validator->errors()->add("work_date_to.$i", 'To date is required.');
                }
            }
            if ($ex === '') {
                $validator->errors()->add("experience.$i", 'Experience (in years) is required.');
            }

            if (! $isFormSCurrent && $from !== '' && $to !== '') {
                try {
                    $fromC = Carbon::parse($from)->startOfDay();
                    $toC = Carbon::parse($to)->startOfDay();
                    if ($toC->lt($fromC)) {
                        $validator->errors()->add("work_date_to.$i", 'To date must be greater than or equal to From date.');
                    } else {
                        $minimumToDate = $fromC->copy()->addYears(2);
                        if ($toC->lt($minimumToDate)) {
                            $validator->errors()->add("work_date_to.$i", 'Minimum 2 Years Experience needed');
                        }
                    }
                } catch (\Throwable $e) {
                    // Other rules may handle invalid date formats.
                }
            }
        }
    }

    /**
     * Form S §7 — work experience: combined duration across ALL rows must be at least 2 calendar years (730 days).
     * (Per-row check was replaced with a combined-total check so that multiple short stints can add up.)
     *
     * "Till date" rows (work_to_till_date[$key] === '1') are evaluated against today's date when
     * the explicit To-date is blank, mirroring the front-end behaviour.
     */
    private function validateFormSWorkExperienceMinimumYears(Request $request, \Illuminate\Validation\Validator $validator): void
    {
        if (($request->form_name ?? '') !== 'S') {
            return;
        }

        $fromDates = $request->input('work_date_from', []);
        $toDates = $request->input('work_date_to', []);
        $tillFlags = $request->input('work_to_till_date', []);
        $sections = $request->input('work_exp_section', []);
        if (! is_array($fromDates)) {
            return;
        }
        if (! is_array($tillFlags)) {
            $tillFlags = [];
        }
        if (! is_array($sections)) {
            $sections = [];
        }

        $totalDays = 0;
        $anyFilled = false;
        $firstFilledKey = null;
        $today = Carbon::now()->startOfDay();

        foreach (array_keys($fromDates) as $key) {
            if (strtolower(trim((string) ($sections[$key] ?? ''))) === 'current') {
                continue;
            }

            $fromRaw = trim((string) ($fromDates[$key] ?? ''));
            $toRaw = trim((string) ($toDates[$key] ?? ''));
            $isTill = ((string) ($tillFlags[$key] ?? '0')) === '1';

            if ($fromRaw === '') {
                continue;
            }
            if ($toRaw === '' && ! $isTill) {
                continue;
            }

            try {
                $from = Carbon::parse($fromRaw)->startOfDay();
                $to = ($toRaw !== '')
                    ? Carbon::parse($toRaw)->startOfDay()
                    : $today;
            } catch (\Throwable $e) {
                continue;
            }

            // Skip invalid date ordering — caller / other rules report the row-level error.
            if ($to->lt($from)) {
                continue;
            }

            $anyFilled = true;
            $totalDays += $from->diffInDays($to);
            if ($firstFilledKey === null) {
                $firstFilledKey = $key;
            }
        }

        // 2 calendar years = 730 days (allows exact 2-year ranges without false negatives).
        if ($anyFilled && $totalDays < 730) {
            $validator->errors()->add(
                'work_date_to.'.($firstFilledKey ?? 0),
                'Minimum 2 Years Experience needed across all entries.'
            );
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

    /**
     * Merge locked parts of the competency payload from DB before draft_update (partial returned-application submit).
     *
     * @param  list<string>  $editableSections  Keys from ReturnedApplicationEditScope (never SECTION_FULL here)
     */
    private function mergeReturnedCompetencyRequestFromDb(Request $request, string $applicationId, array $editableSections): void
    {
        if (ReturnedApplicationEditScope::isFullUnlock($editableSections)) {
            return;
        }

        $existingForm = CC_Forms_Meta::findByApplicationId($applicationId);
        if (! $existingForm) {
            return;
        }

        $editable = array_flip($editableSections);
        $formName = strtoupper((string) ($request->input('form_name') ?: $existingForm->form_name));
        $masterAppId = $this->resolveFormSMasterApplicationId($existingForm, $formName);
        $proofCrypt = app(SensitiveProofCryptService::class);

        $aadhaarPlain = (string) ($proofCrypt->decryptProofNumber(
            CC_Proof_doc::where('application_id', $masterAppId)
                ->where('proof_name', FormSProofDocumentService::PROOF_AADHAAR)
                ->value('proof_no')
        ) ?? '');
        $panPlain = $proofCrypt->decryptProofNumber(
            CC_Proof_doc::where('application_id', $masterAppId)
                ->where('proof_name', FormSProofDocumentService::PROOF_PAN)
                ->value('proof_no')
        );

        $fmtDate = static function ($v): ?string {
            if ($v === null || $v === '') {
                return null;
            }
            try {
                return Carbon::parse($v)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $request->merge([
            'applicant_name' => $existingForm->applicant_name,
            'fathers_name' => $existingForm->fathers_name,
            'applicant_email' => $existingForm->applicant_email,
            'applicants_address' => $existingForm->applicant_address,
            'd_o_b' => $fmtDate($existingForm->d_o_b) ?? '',
            'age' => $existingForm->age,
            'previously_number' => $existingForm->previous_scc_no,
            'previously_valid_to' => $fmtDate($existingForm->scc_to_date ?? null),
            'previously_issue_date' => $fmtDate($existingForm->first_issue_date),
            'previously_valid_from' => $fmtDate($existingForm->scc_from_date ?? null),
            'aadhaar' => preg_replace('/\D/', '', (string) $aadhaarPlain),
            'pancard' => $panPlain !== null && $panPlain !== '' ? strtoupper(preg_replace('/\s+/', '', (string) $panPlain)) : null,
            'competency_certificate_no' => $existingForm->wcc_no,
            'certificate_valid_to' => $fmtDate($existingForm->wcc_to ?? null),
            'certificate_issue_date' => $fmtDate($existingForm->wcc_issue_date),
            'certificate_valid_from' => $fmtDate($existingForm->wcc_from ?? null),
        ]);

        if (! isset($editable[ReturnedApplicationEditScope::SECTION_EDUCATION])) {
            $request->files->remove('education_document');
            ReturnedApplicationPayloadMerge::mergeEducationArraysIntoRequest($request, $masterAppId);
        }

        if (! isset($editable[ReturnedApplicationEditScope::SECTION_EXPERIENCE])) {
            $request->files->remove('work_document');
            ReturnedApplicationPayloadMerge::mergeExperienceArraysIntoRequest($request, $masterAppId, $formName);
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

        if ($this->isCompetencyForm($formName)) {
            $request->files->remove('pancard_doc');
        }
    }

    private function competencyCertificateService(): CompetencyCertificateService
    {
        return app(CompetencyCertificateService::class);
    }

    private function loadIssuedCertificateForView(string $applicationId, ?string $formName = null): ?object
    {
        $cert = $this->competencyCertificateService()->asLicenseDetails($applicationId, $formName);
        if ($cert) {
            return $cert;
        }

        return DB::table('tnelb_license')
            ->where('application_id', $applicationId)
            ->select('*')
            ->first();
    }

    /**
     * Populate issued licence number for renewal fee AJAX when cert row has no number yet.
     */
    private function enrichLicenseDetailsForRenewal($appl_id, $application_details, $license_details)
    {
        if (!$application_details) {
            return $license_details;
        }
        $issued = $license_details ? trim((string) ($license_details->license_number ?? $license_details->certificate_no ?? '')) : '';
        if ($issued === '') {
            $issued = trim((string) ($application_details->license_number ?? ''));
        }
        if ($issued === '') {
            $compRow = CC_Forms_Meta::findByApplicationId($appl_id);
            if ($compRow) {
                $issued = trim((string) ($compRow->wcc_no ?? ''));
            }
        }
        if ($issued === '') {
            $cert = $this->competencyCertificateService()->asLicenseDetails(
                $appl_id,
                $application_details->form_name ?? null
            );
            if ($cert) {
                return $cert;
            }
        }
        if ($issued === '') {
            return $license_details;
        }
        if (!$license_details) {
            return (object) ['cert' => $issued];
        }
        if (trim((string) ($license_details->license_number ?? '')) === '') {
            $license_details->license_number = $issued;
        }

        return $license_details;
    }

    /** Dashboard/edit views expect draft|payment labels (not raw N/Y). */
    private function normalizeCcPaymentStatusForEdit(?string $status): string
    {
        $normalized = strtolower(trim((string) $status));

        return match ($normalized) {
            'n', 'draft', '' => 'draft',
            'y', 'payment', 'paid', 'success' => 'payment',
            default => $normalized !== '' ? $normalized : 'draft',
        };
    }

    /** Map cc_form_s_meta columns to legacy edit_application field names. */
    private function normalizeCcMetaRowForEdit(object $row): object
    {
        $row->license_name = $row->license_name ?? $row->certificate_name ?? null;
        $row->status = $row->status ?? $row->app_status ?? null;
        $row->applicants_address = $row->applicants_address ?? $row->applicant_address ?? null;
        $row->previously_number = $row->previously_number ?? $row->previous_scc_no ?? null;
        $row->previously_issue_date = $row->previously_issue_date ?? $row->first_issue_date ?? null;
        $row->previously_valid_from = $row->previously_valid_from ?? $row->scc_from_date ?? null;
        $row->previously_valid_to = $row->previously_valid_to ?? $row->scc_to_date ?? null;
        $row->previously_date = $row->previously_date ?? $row->scc_to_date ?? null;
        $row->competency_certificate_no = $row->competency_certificate_no ?? $row->wcc_no ?? null;
        $row->certificate_no = $row->certificate_no ?? $row->wcc_no ?? null;
        $row->certificate_valid_to = $row->certificate_valid_to ?? $row->wcc_to ?? null;
        $row->certificate_issue_date = $row->certificate_issue_date ?? $row->wcc_issue_date ?? null;
        $row->certificate_valid_from = $row->certificate_valid_from ?? $row->wcc_from ?? null;
        $row->certificate_date = $row->certificate_date ?? $row->wcc_to ?? null;
        $row->payment_status = $this->normalizeCcPaymentStatusForEdit($row->payment_status ?? null);
        $row->id = $row->id ?? $row->app_id ?? null;
        $row->form_id = $row->form_id ?? null;
        $row->license_verify = isset($row->license_verify)
            ? (int) $row->license_verify
            : (! empty($row->previously_number) ? 1 : 0);
        $row->cert_verify = isset($row->cert_verify)
            ? (int) $row->cert_verify
            : (! empty($row->certificate_no) ? 1 : 0);

        return $row;
    }

    private function enrichCcMetaProofFieldsForEdit(object $applicationDetails, string $masterApplicationId): object
    {
        $proofRows = CC_Proof_doc::where('application_id', $masterApplicationId)
            ->whereIn('proof_type', ['aadhaar', 'pan'])
            ->get();

        foreach ($proofRows as $proof) {
            $proofType = strtolower((string) ($proof->proof_type ?? ''));
            if ($proofType === 'aadhaar') {
                if (! empty($proof->proof_no)) {
                    $applicationDetails->aadhaar = $proof->proof_no;
                }
                if (! empty($proof->proof_doc)) {
                    $applicationDetails->aadhaar_doc = $proof->proof_doc;
                }
            } elseif ($proofType === 'pan') {
                if (! empty($proof->proof_no)) {
                    $applicationDetails->pancard = $proof->proof_no;
                }
                if (! empty($proof->proof_doc)) {
                    $applicationDetails->pan_doc = $proof->proof_doc;
                    $applicationDetails->pancard_doc = $proof->proof_doc;
                }
            }
        }

        return $applicationDetails;
    }

    /**
     * Load Form S / W / WH / P draft edit data from CC tables
     * (per-form cc_form_*_meta + shared cc_edu, cc_exp, cc_proof_doc).
     *
     * @return array{application_details: object, edu_details: \Illuminate\Support\Collection, exp_details: \Illuminate\Support\Collection, master_application_id: string}|null
     */
    private function loadCompetencyEditBundle(string $applicationId): ?array
    {
        $ccMeta = CC_Forms_Meta::findByApplicationId($applicationId);
        if (! $ccMeta || ! in_array($ccMeta->form_name, ['S', 'W', 'WH'], true)) {
            return null;
        }

        $masterMeta = app(FormSApplicationWorkflowService::class)->masterApplication($ccMeta);
        $masterApplicationId = (string) $masterMeta->application_id;

        $applicationDetails = $this->normalizeCcMetaRowForEdit((object) $ccMeta->toArray());
        $applicationDetails = $this->enrichCcMetaProofFieldsForEdit($applicationDetails, $masterApplicationId);

        $eduDetails = CC_Education::where('application_id', $masterApplicationId)
            ->orderByDesc('year_of_passing')
            ->get()
            ->map(function (CC_Education $edu) {
                $row = (object) $edu->toArray();
                $row->id = $edu->edu_id;

                return $row;
            });

        $expDetails = CC_Experience::where('application_id', $masterApplicationId)
            ->orderBy('exp_id')
            ->get()
            ->map(function (CC_Experience $exp) {
                $row = (object) $exp->toArray();
                $row->id = $exp->exp_id;
                $row->releive_document = $exp->relieve_document ?? $exp->releive_document ?? null;

                return $row;
            });

        return [
            'application_details' => $applicationDetails,
            'edu_details' => $eduDetails,
            'exp_details' => $expDetails,
            'master_application_id' => $masterApplicationId,
        ];
    }

    private function assertApplicantOwnsApplication(object $applicationDetails): ?\Illuminate\Http\RedirectResponse
    {
        $loginId = Auth::user()->login_id ?? session('login_id');
        if (! $loginId || (string) ($applicationDetails->login_id ?? '') !== (string) $loginId) {
            return redirect()->route('dashboard')->with('error', 'You can only edit your own application.');
        }

        return null;
    }


    public function editApplication($appl_id)
    {
        if (!Auth::check()) {
            return redirect()->route('logout');
        }

        if (!$appl_id) {
            return redirect()->route('dashboard')->with('error', 'Application ID is required.');
        }

        $proofApplicationId = $appl_id;
        $ccBundle = $this->loadCompetencyEditBundle($appl_id);

        if ($ccBundle) {
            $application_details = $ccBundle['application_details'];
            $edu_details = $ccBundle['edu_details'];
            $exp_details = $ccBundle['exp_details'];
            $proofApplicationId = $ccBundle['master_application_id'];
        } else {
            $application_details = DB::table('tnelb_application_tbl')
                ->where('application_id', $appl_id)
                ->select('*')
                ->first();

            if (! $application_details) {
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
                ->orderBy('exp_id', 'asc')
                ->get();
        }

        if ($redirect = $this->assertApplicantOwnsApplication($application_details)) {
            return $redirect;
        }

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

        $license_details = $this->loadIssuedCertificateForView(
            $appl_id,
            $application_details->form_name ?? null
        );
        $license_details = $this->enrichLicenseDetailsForRenewal($appl_id, $application_details, $license_details);

        $applicant_photo = $this->loadApplicantPhotoForView($proofApplicationId);

        $proof_doc = $this->loadApplicantSignForView($proofApplicationId);

        $applicationid = $appl_id;

        $queries = DB::table('tnelb_query_applicable')
            ->where('application_id', $appl_id)
            ->where('query_status', 'P')
            ->orderByDesc('id')
            ->get();

        $cc_digitization_temp_id = null;
        if (($application_details->appl_type ?? '') === 'D') {
            $cc_digitization_temp_id = app(CcDigitizationLinkService::class)->resolveTempAppId(
                null,
                (string) $application_details->login_id,
                $appl_id,
                $application_details->form_name ?? null
            );
        }

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
            'queries',
            'cc_digitization_temp_id'
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

        $proofApplicationId = $appl_id;
        $ccBundle = $this->loadCompetencyEditBundle($appl_id);

        if ($ccBundle) {
            $application_details = $ccBundle['application_details'];
            $edu_details = $ccBundle['edu_details'];
            $exp_details = $ccBundle['exp_details'];
            $proofApplicationId = $ccBundle['master_application_id'];
        } else {
            $application_details = DB::table('tnelb_application_tbl')
                ->where('application_id', $appl_id)
                ->select('*')
                ->first();

            if (! $application_details) {
                return redirect()->route('dashboard')->with('error', 'Application not found.');
            }

            $edu_details = DB::table('tnelb_applicants_edu')
                ->where('application_id', $appl_id)
                ->orderBy('year_of_passing', 'desc')
                ->get();

            $exp_details = DB::table('tnelb_applicants_exp')
                ->where('application_id', $appl_id)
                ->orderBy('exp_id', 'asc')
                ->get();
        }

        $this->decryptPanForDisplay($application_details);

        $returnStatus = strtoupper(trim((string) ($application_details->status ?? $application_details->app_status ?? '')));
        if ($returnStatus !== 'QU') {
            return redirect()->route('dashboard')->with('error', 'This page is only for applications returned with a query.');
        }

        if ($redirect = $this->assertApplicantOwnsApplication($application_details)) {
            return $redirect;
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

        $license_details = $this->loadIssuedCertificateForView(
            $appl_id,
            $application_details->form_name ?? null
        );
        $license_details = $this->enrichLicenseDetailsForRenewal($appl_id, $application_details, $license_details);

        $applicant_photo = $this->loadApplicantPhotoForView($proofApplicationId);
        $proof_doc = $this->loadApplicantSignForView($proofApplicationId);
        $applicationid = $appl_id;

        // Applicant-facing copy: only what was recorded in return-to-applicant log (not tnelb_query_applicable / internal staff queries)
        $queries = collect();
        $queryReasonsForValidation = [];
        $returnRemarks = '';

        if (Schema::hasTable('tnelb_return_to_applicant_log')) {
            $returnLogRow = ReturnedApplicationEditScope::latestReturnLogRow($appl_id);

            if ($returnLogRow) {
                $returnRemarks = trim((string) ($returnLogRow->remarks ?? ''));
                $queryReasonsForValidation = ReturnedApplicationEditScope::parseQueryTypesJson($returnLogRow->query_types ?? null);

                if ($queryReasonsForValidation !== [] || $returnRemarks !== '') {
                    $queries = collect([(object) [
                        'query_type' => json_encode($queryReasonsForValidation),
                        'raised_by' => $returnLogRow->returned_by_role ?? null,
                    ]]);
                }
            }
        }

        $returnedEditableSections = ReturnedApplicationEditScope::editableSectionsFromReasons($queryReasonsForValidation);

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
            'returnRemarks',
            'returnedEditableSections'
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

        $this->pruneHiddenFormSCurrentSectionLegacyRows($request);

        
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
            'previously_valid_to'  => 'nullable|date',
            'previously_issue_date' => 'nullable|date',
            'previously_valid_from' => 'nullable|date',
            'wireman_details'      => 'nullable|string|max:255',
            'aadhaar'              => 'required|string|digits:12',
            'form_name'            => 'required|string|max:2',
            'license_name'         => 'required|string|max:2',
            'form_id'              => 'required|integer',
            // 'amount'               => 'required|numeric|min:0',
            'competency_certificate_no' => 'nullable|string|max:80',
            'certificate_date'              => 'nullable|date',
            'certificate_issue_date'        => 'nullable|date',

            'applicant_email'      => ($request->form_name === 'S')
                ? 'required|email|max:191'
                : 'nullable|email|max:191',

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
            'work_relieving_letter' => 'nullable|array',
            'work_relieving_letter.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
            'existing_work_document' => 'nullable|array',
            'existing_work_document.*' => 'nullable|string|max:500',
            'existing_work_relieving_document' => 'nullable|array',
            'existing_work_relieving_document.*' => 'nullable|string|max:500',
            
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
            'applicant_email.required' => 'Email ID is required.',
            'applicant_email.email' => 'Enter a valid Email ID.',
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

            $this->validateOptionalCompetencyWorkRows($request, $validator);
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
                        'Please attach the education certificate document before submitting.'
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
        $validator->after(function ($validator) use ($request) {
            $this->validateFormSWorkExperienceMinimumYears($request, $validator);
        });
        $validator->validate();

        $action = $request->input('form_action', 'draft');
        if ($action !== 'draft') {
            $boardMemberErr = $this->validateFormSBoardMemberWorkRows($request);
            if ($boardMemberErr !== null) {
                return response()->json(['status' => 'error', 'message' => $boardMemberErr], 422);
            }
        }
        
        // Safety fallback: if client doesn't send form_action, keep first save as draft.
        $loginId = $request->login_id;

        // Idempotency guard: if the client already has an application_id, do not insert
        // a new application row. Route through draft_update so the same record is updated.
        $existingApplicationId = trim((string) $request->input('application_id', ''));
        if ($existingApplicationId !== '' && CC_Forms_Meta::existsByApplicationId($existingApplicationId)) {
            return $this->draft_update($request, $existingApplicationId);
        }

        if ($guard = $this->assertDigitizationCanSave($request)) {
            return $guard;
        }
        
        
        DB::beginTransaction();
        
        $encrypted_aadhaar = Crypt::encryptString($request->aadhaar);
        $encrypted_pancard = ($this->isCompetencyForm($request->form_name) && $request->filled('pancard'))
            ? Crypt::encryptString($request->pancard)
            : null;

        try {
            // Generate New Application ID
            $appl_type = $request->appl_type ?? '';
            if (in_array($appl_type, ['R', 'D'], true)) {
                $metaService = app(CompetencyMetaService::class);
        $lastApplication = $metaService->latestApplicationId();
                if ($lastApplication) {
                    $lastNumber = (int) substr($lastApplication, -7);
                    $newApplicationId = $appl_type.$request->form_name . $request->license_name . date('y') . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
                } else {
                    $newApplicationId = $appl_type.$request->form_name . $request->license_name . date('y') . '1111111';
                }     
            }else{
                $metaService = app(CompetencyMetaService::class);
        $lastApplication = $metaService->latestApplicationId();
                if ($lastApplication) {
                    $lastNumber = (int) substr($lastApplication, -7);
                    $newApplicationId = $request->form_name . $request->license_name . date('y') . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
                } else {
                    $newApplicationId = $request->form_name . $request->license_name . date('y') . '1111111';
                }
                
            }
            
            $aadhaarFilename = null;
            
            $form = CC_Forms_Meta::createForForm((string) ($request->form_name ?? 'S'), array_merge([
                'login_id'            => $loginId,
                'application_id'      => $newApplicationId,
                'applicant_name'      => $request->applicant_name ?? '',
                'fathers_name'        => $request->fathers_name ?? '',
                'applicant_email'     => $request->input('applicant_email'),
                'applicant_address'   => $request->applicants_address,
                'd_o_b'               => $request->dob ?? $request->d_o_b,
                'age'                 => $request->age,
                'previous_scc_no'   => $request->previously_number ?? 0,
                'previously_valid_to' => $request->previously_valid_to ?: ($request->previously_date ?: null),
                'first_issue_date'   => $request->previously_issue_date ?: null,
                'scc_from_date'     => $request->previously_valid_from ?: null,
                'wireman_details'     => $request->wireman_details,
                'form_name'           => $request->form_name,
                'form_id'             => $request->form_id,
                'certificate_name'        => $request->license_name,
                // 'aadhaar'             => $encrypted_aadhaar,
                // 'pancard'             => $encrypted_pancard,
                'app_status'              => 'P',
                'appl_type'           => $appl_type,
                'payment_status'      => ($action === 'draft') ? 'N' : 'Y',
                // 'aadhaar_doc'         => $aadhaarFilename,
                // 'pan_doc'             => $panFilename,
                'wcc_no'      => $request->competency_certificate_no,
                'wcc_to' => $request->certificate_valid_to ?: ($request->certificate_date ?: null),
                'wcc_issue_date' => $request->certificate_issue_date ?: null,
                'wcc_from' => $request->certificate_valid_from ?: null,
                'submitted_date'      => $this->dbNow,
                'updated_at'          => $this->dbNow,
                'created_at'          => $this->dbNow
            ]));


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
       
            $current_form = collect($form_details)->firstWhere('cert_licence_code', $form->certificate_name);
            $category_type = $current_form
                ? collect($form_category)->firstWhere('id', $current_form['category_id'] ?? null)
                : null;

            $certificate_details['licence_name'] = $current_form['licence_name'] ?? '';
            $certificate_details['category_name'] = $category_type['category_name'] ?? '';
            $certificate_details['form_type'] = $form->appl_type;
            
            // process education (upsert per level so duplicate DOM rows cannot create duplicate DB rows)
            if ($request->has('educational_level')) {
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
                    $pendingEduFile = null;
                    $existingByKey = CC_Education::where([
                        'login_id' => $loginId,
                        'application_id' => $newApplicationId,
                        'educational_level' => $level,
                    ])->first();

                    $docResolution = $this->resolveEducationDocumentForSave(
                        $request,
                        $key,
                        $form,
                        $request->form_name,
                        $existingByKey,
                        false
                    );
                    $filePath = $docResolution['path'];
                    $pendingEduFile = $docResolution['pending_file'];

                    $upsertAttrs = [
                        'login_id'           => $loginId,
                        'application_id'     => $this->resolveFormSMasterApplicationId($form, $request->form_name),
                        'educational_level'  => $level,
                    ];  

                    $existingByKey = CC_Education::where($upsertAttrs)->first();

                    $uploadToStore = $filePath;
                    if ($uploadToStore === null && $existingByKey && $existingByKey->upload_document) {
                        $uploadToStore = $existingByKey->upload_document;
                    }

                    $education = CC_Education::updateOrCreate(
                        $upsertAttrs,
                        [
                            'institute_name'    => $request->institute_name[$key],
                            'month_passing'     => $monthVal ?? $existingByKey?->month_passing ?? $monthRaw,
                            'year_of_passing'   => $request->year_of_passing[$key],
                            'certificate_no'    => $request->certificate_no[$key] ?? null,
                            'upload_document'       => $uploadToStore,
                        ]
                    );

                    if ($pendingEduFile) {
                        $approvedPath = $this->applyPendingFormSEducationUpload(
                            $request,
                            $key,
                            $form,
                            $education,
                            $pendingEduFile
                        );
                        if ($approvedPath !== null) {
                            $education->update(['upload_document' => $approvedPath]);
                        }
                    }
                }
            }

            
            // process experience
            if ($this->hasWorkExperiencePayload($request)) {
                foreach ($this->getWorkRowIndexes($request) as $key) {
                    $workRow = $this->mapWorkExperienceRow($request, $key, $request->form_name ?? null);
                    $orgName = $workRow['org_name'] ?? $workRow['company_name'] ?? '';
                    $expYears = $workRow['experience'];
                    $designation = $workRow['designation'];

                    if ($orgName === '' || $expYears === '' || $designation === '') {
                        continue;
                    }

                    $documents = $this->resolveWorkRowDocuments(
                        $request,
                        $key,
                        null,
                        false,
                        false,
                        $form,
                        $request->form_name ?? null
                    );

                        $experience = CC_Experience::create(array_merge(
                        $this->mstExperienceRowToDbPayload($workRow, $documents),
                        [
                            'login_id' => $loginId,
                            'application_id' => $this->resolveFormSMasterApplicationId($form, $request->form_name),
                        ]
                    ));

                    if (! empty($documents['pending_support_upload']) || ! empty($documents['pending_relieve_upload'])) {
                        $this->applyPendingFormSExperienceDocumentUploads(
                            $request,
                            $key,
                            $form,
                            $experience->fresh(),
                            $documents
                        );
                    }
                }
            }
            
            $this->saveCompetencyProofDocuments($request, $form, $request->form_name ?? null);
            
            $this->linkCcDigitizationIfNeeded($request, $applicationId, $loginId);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Form submitted successfully!',
                'application_id' => $applicationId,
                'applicantName' => $form->applicant_name,
                'form_name'    => $form->form_name,
                'licence_name' => $certificate_details['licence_name'],
                'type_of_apps' => $certificate_details['category_name'],
                'form_type'    => $certificate_details['form_type'] == 'N' ? 'FRESH' : 'RENEWAL',
                'date_apps'    => Carbon::parse($this->dbNow)->format('d-m-Y')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->formErrorResponse($e, 'Failed to save form. Please try again!');
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

        $this->pruneHiddenFormSCurrentSectionLegacyRows($request);

        $existingForm = CC_Forms_Meta::findByApplicationId($applicationId);
        $masterApplicationId = $existingForm
            ? $this->resolveFormSMasterApplicationId($existingForm, $request->form_name ?? null)
            : $applicationId;
        $proofService = $this->proofDocumentService();
        $existingPhoto = $proofService->loadPhotoForView($masterApplicationId);

        if (!$existingForm) {
            return response()->json(['status' => 'error', 'message' => 'Draft not found!'], 404);
        }

        if ($guard = $this->assertDigitizationCanSave($request, $applicationId)) {
            return $guard;
        }

        $uploadPhotoRule = (! $existingPhoto || empty($existingPhoto->upload_path))
            ? 'image|mimes:jpg,jpeg,png|max:50'
            : 'nullable|image|mimes:jpg,jpeg,png|max:50';
        $uploadSignRule = 'nullable|image|mimes:jpg,jpeg,png|max:50';

        $aadhaarDocRule = ! $proofService->hasProofDocument($masterApplicationId, FormSProofDocumentService::PROOF_AADHAAR)
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
            'previously_valid_to' => 'nullable|date',
            'previously_issue_date' => 'nullable|date',
            'previously_valid_from' => 'nullable|date',
            'certificate_date'   => 'nullable|date',
            'certificate_valid_to' => 'nullable|date',
            'certificate_issue_date' => 'nullable|date',
            'certificate_valid_from' => 'nullable|date',
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
            'work_relieving_letter' => 'nullable|array',
            'work_relieving_letter.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:200',
            'existing_work_document' => 'nullable|array',
            'existing_work_document.*' => 'nullable|string|max:500',
            'existing_work_relieving_document' => 'nullable|array',
            'existing_work_relieving_document.*' => 'nullable|string|max:500',

            'applicant_email'      => ($request->form_name === 'S')
                ? 'required|email|max:191'
                : 'nullable|email|max:191',
        ];

        $messages = [
            'education_document.*.max'    => 'Educational document size permitted only 5 KB to 200 KB.',
            'work_document.*.max'    => 'Experience document size permitted only 5 KB to 200 KB.',
            'applicant_email.required' => 'Email ID is required.',
            'applicant_email.email' => 'Enter a valid Email ID.',
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

            $this->validateOptionalCompetencyWorkRows($request, $validator);
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
                        'Please attach the education certificate document before submitting.'
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
        $validator->after(function ($validator) use ($request) {
            $this->validateFormSWorkExperienceMinimumYears($request, $validator);
        });
        $validator->validate();

        $action = $request->input('form_action', 'draft');
        $existingPaymentStatus = strtoupper(trim((string) ($existingForm->payment_status ?? '')));
        $paymentStatus = $action === 'draft'
            ? 'N'
            : (in_array($existingPaymentStatus, ['Y', 'PAYMENT', 'PAID'], true)
                ? $existingForm->payment_status
                : 'payment');

        DB::beginTransaction();

        $loginId = $request->login_id;

        try {

            // Update existing draft
            $existingForm->update(array_merge([
                'login_id'          => $request->login_id,
                'applicant_name'    => $request->applicant_name,
                'fathers_name'      => $request->fathers_name,
                'applicant_email'   => $request->input('applicant_email'),
                'applicant_address' => $request->applicants_address,
                'd_o_b'             => $request->d_o_b,
                'age'               => $request->age,
                'previous_scc_no'   => $request->previously_number,
                'first_issue_date'  => $request->previously_issue_date ?: null,
                'scc_from_date'     => $request->previously_valid_from ?: null,
                'scc_to_date'       => $request->previously_valid_to ?: ($request->previously_date ?: null),
                'wcc_no'            => $request->competency_certificate_no,
                'wcc_to'            => $request->certificate_valid_to ?: ($request->certificate_date ?: null),
                'wcc_issue_date'    => $request->certificate_issue_date ?: null,
                'wcc_from'          => $request->certificate_valid_from ?: null,
                'payment_status'    => $paymentStatus,
                'submitted_date'    => $this->dbNow,
                'updated_at'        => $this->dbNow,
            ]));




            if ($request->has('educational_level')) {
                foreach ($request->educational_level as $key => $level) {
                    if (
                        empty($level) ||
                        empty($request->institute_name[$key] ?? null) ||
                        empty($request->month_of_passing[$key] ?? null) ||
                        empty($request->year_of_passing[$key] ?? null)
                    ) {
                        continue;
                    }

                    $upsertAttrs = [
                        'login_id'          => $loginId,
                        'application_id'    => $this->resolveFormSMasterApplicationId($existingForm, $request->form_name),
                        'educational_level' => $level,
                    ];

                    $existingByKey = CC_Education::where($upsertAttrs)->first();

                    $docResolution = $this->resolveEducationDocumentForSave(
                        $request,
                        $key,
                        $existingForm,
                        $request->form_name,
                        $existingByKey,
                        false
                    );
                    $filePath = $docResolution['path'];
                    $pendingEduFile = $docResolution['pending_file'];

                    $uploadToStore = $filePath;
                    if ($uploadToStore === null && $existingByKey && $existingByKey->upload_document) {
                        $uploadToStore = $existingByKey->upload_document;
                    }

                    $education = CC_Education::updateOrCreate(
                        $upsertAttrs,
                        [
                            'institute_name'  => $request->institute_name[$key],
                            'month_passing'   => $request->month_of_passing[$key] ?? null,
                            'year_of_passing' => $request->year_of_passing[$key],
                            'certificate_no'  => $request->certificate_no[$key] ?? null,
                            'upload_document' => $uploadToStore,
                        ]
                    );

                    if ($pendingEduFile) {
                        $approvedPath = $this->applyPendingFormSEducationUpload(
                            $request,
                            $key,
                            $existingForm,
                            $education,
                            $pendingEduFile
                        );
                        if ($approvedPath !== null) {
                            $education->update(['upload_document' => $approvedPath]);
                        }
                    }
                }
            }

            $this->seedFormSDocumentsIfRenewal($existingForm->fresh(), $request->form_name);
            
            

            if ($this->hasWorkExperiencePayload($request)) {
                $claimedWorkIds = [];
                $masterApplicationId = $this->resolveFormSMasterApplicationId($existingForm, $request->form_name);

                foreach ($this->getWorkRowIndexes($request) as $key) {
                    $workRow = $this->mapWorkExperienceRow($request, $key, $request->form_name ?? null);
                    $this->upsertWorkExperienceDraftRow(
                        $request,
                        $key,
                        $loginId,
                        $applicationId,
                        $workRow,
                        $claimedWorkIds,
                        true,
                        $existingForm,
                        $request->form_name ?? null
                    );
                }

                if (! empty($claimedWorkIds)) {
                    $this->resolveExperienceModelClass($existingForm, $request->form_name)::where('application_id', $masterApplicationId)
                        ->whereNotIn('exp_id', $claimedWorkIds)
                        ->delete();
                }
            }

            $this->saveCompetencyProofDocuments($request, $existingForm, $request->form_name ?? null);

            $this->linkCcDigitizationIfNeeded($request, $applicationId, $loginId);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message'=> $action === 'draft'
                    ? 'Draft saved successfully!'
                    : 'Draft updated and submitted successfully!',
                'application_id' => $applicationId,
                'applicantName' => $existingForm->applicant_name
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->formErrorResponse($e, 'Update failed. Please try again!');
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

        $appService = app(CompetencyApplicationService::class);
        $workflowService = app(CompetencyWorkflowService::class);

        $ccApplicant = $appService->findApplicantWithPayment($appl_id);
        $legacyApp = DB::table('tnelb_application_tbl')->where('application_id', $appl_id)->first();

        if (! $ccApplicant && ! $legacyApp) {
            return response()->json(['status' => 'error', 'message' => 'Application not found.'], 404);
        }

        $returnStatus = '';
        if ($ccApplicant) {
            $returnStatus = strtoupper(trim((string) ($ccApplicant->status ?? $ccApplicant->app_status ?? '')));
        }
        if ($returnStatus !== 'QU' && $legacyApp) {
            $returnStatus = strtoupper(trim((string) ($legacyApp->status ?? '')));
        }
        if ($returnStatus !== 'QU') {
            return response()->json(['status' => 'error', 'message' => 'This application is not under query.'], 400);
        }

        $ownerLoginId = $ccApplicant->login_id ?? $legacyApp->login_id ?? null;
        $loginId = session('login_id');
        if (!$loginId || (string) $ownerLoginId !== (string) $loginId) {
            return response()->json(['status' => 'error', 'message' => 'You can only submit corrections for your own application.'], 403);
        }

        $originalPaymentStatus = $ccApplicant->payment_status ?? $legacyApp->payment_status ?? null;

        $queryReasonsForSubmit = [];
        $returnLogRow = ReturnedApplicationEditScope::latestReturnLogRow($appl_id);
        if ($returnLogRow) {
            $queryReasonsForSubmit = ReturnedApplicationEditScope::parseQueryTypesJson($returnLogRow->query_types ?? null);
        }
        $returnedEditableSections = ReturnedApplicationEditScope::editableSectionsFromReasons($queryReasonsForSubmit);
        $this->mergeReturnedCompetencyRequestFromDb($request, $appl_id, $returnedEditableSections);

        $response = $this->draft_update($request, $appl_id);
        $data = json_decode($response->getContent(), true);

        if (isset($data['status']) && $data['status'] === 'success') {
            if ($ccApplicant) {
                $appService->updateApplicationStatus($appl_id, [
                    'app_status'   => 'RE',
                    'processed_by' => 'AP',
                    'updated_at'   => $this->dbNow,
                ]);

                if ($originalPaymentStatus !== null) {
                    CC_Forms_Meta::updateByApplicationId($appl_id, [
                        'payment_status' => $originalPaymentStatus,
                    ]);
                }

                $supervisorRoleId = RoleHelper::supervisorWorkflowRoleId(Auth::user());
                if ($supervisorRoleId) {
                    $workflowTable = $appService->resolveWorkflowTable($appl_id, $ccApplicant);
                    $workflowService->record($workflowTable, [
                        'application_id' => $appl_id,
                        'appl_status'    => 'RE',
                        'processed_by'   => 'AP',
                        'forwarded_to'   => $supervisorRoleId,
                        'role_id'        => $supervisorRoleId,
                        'is_verified'    => 'Yes',
                        'query_status'   => null,
                        'remarks'        => 'Resubmitted by applicant after query.',
                        'queries'        => null,
                        'raised_by'      => 'AP',
                        'login_id'       => Auth::id(),
                        'created_at'     => $this->dbNow,
                    ]);
                }
            }

            if ($legacyApp) {
                DB::table('tnelb_application_tbl')
                    ->where('application_id', $appl_id)
                    ->update([
                        'status'         => 'RE',
                        'processed_by'   => 'AP',
                        'updated_at'     => $this->dbNow,
                        'payment_status' => $originalPaymentStatus ?? $legacyApp->payment_status,
                    ]);
            }

            if (! $ccApplicant) {
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
            }

            DB::table('tnelb_query_applicable')
                ->where('application_id', $appl_id)
                ->where('query_status', 'P')
                ->update(['query_status' => 'R', 'updated_at' => $this->dbNow]);

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

            $education = CC_Education::find($id);

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
            return $this->formErrorResponse($e, 'Failed to delete record!');
        }
    }

    public function delete_experience(Request $request)
    {
        try {
            $id = $request->input('exp_id'); // Get edu_id from AJAX request

            $experience = CC_Experience::find($id);

            if (!$experience) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Experience record not found!'
                ], 404);
            }

            foreach ([$experience->support_document, $experience->releive_document, $experience->upload_document] as $docPath) {
                if (empty($docPath)) {
                    continue;
                }
                $filePath = public_path($docPath);
                if (is_file($filePath)) {
                    unlink($filePath);
                }
            }

            $experience->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Experience record deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return $this->formErrorResponse($e, 'Failed to delete record!');
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
        $existingForm = $applicationId
            ? CC_Forms_Meta::findByApplicationId($applicationId)
            : null;
        $masterApplicationId = $existingForm
            ? $this->resolveFormSMasterApplicationId($existingForm, $request->form_name ?? null)
            : $applicationId;
        $proofService = $this->proofDocumentService();
        $existingPhoto = $masterApplicationId
            ? $proofService->loadPhotoForView($masterApplicationId)
            : null;

        if (!$existingForm && $applicationId) {
            return response()->json(['status' => 'error', 'message' => 'Draft not found!'], 404);
        }

        if ($guard = $this->assertDigitizationCanSave($request, $applicationId)) {
            return $guard;
        }

        $uploadPhotoRule = (! $existingPhoto || empty($existingPhoto->upload_path))
            ? 'image|mimes:jpg,jpeg,png|max:50'
            : 'nullable|image|mimes:jpg,jpeg,png|max:50';

        // Signature is optional for draft submit; file is validated only if present
        $uploadSignRule = 'nullable|image|mimes:jpg,jpeg,png|max:50';

        $aadhaarDocRule = ($existingForm && $masterApplicationId
            && ! $proofService->hasProofDocument($masterApplicationId, FormSProofDocumentService::PROOF_AADHAAR))
            ? 'mimes:pdf|max:250'
            : 'nullable|mimes:pdf|max:250';

            $educationLevelRuleDraft = ($request->form_name === 'S')
                ? 'nullable|string|in:DEE,BEE,MEE,AMIE|max:50'
                : 'nullable|string|max:50';

            $request->validate([
                'login_id'           => 'nullable|string',
                'applicant_name'     => 'nullable|string|max:255',
                'fathers_name'       => 'nullable|string|max:255',
                'applicant_email'    => 'nullable|email|max:191',
                'applicants_address' => 'nullable|string|max:500',
                'd_o_b'              => 'nullable|date',
                'age'                => 'nullable|integer|min:18|max:100',
                'previously_number'  => 'nullable|string',
                'previously_date'    => 'nullable|date',
                'previously_valid_to' => 'nullable|date',
                'previously_issue_date' => 'nullable|date',
                'previously_valid_from' => 'nullable|date',
                'certificate_date'   => 'nullable|date',
                'certificate_valid_to' => 'nullable|date',
                'certificate_issue_date' => 'nullable|date',
                'certificate_valid_from' => 'nullable|date',
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
        if ($action !== 'draft') {
            $boardMemberErr = $this->validateFormSBoardMemberWorkRows($request);
            if ($boardMemberErr !== null) {
                return response()->json(['status' => 'error', 'message' => $boardMemberErr], 422);
            }
        }
        $loginId = $this->resolveDigitizationLoginId($request, $request->login_id);
        $appl_type = $request->appl_type ?? '';

        DB::beginTransaction();

        try {
            $form = $id ? CC_Forms_Meta::findByApplicationId($id) : null;

            if ($form) {
                $applicationId = $form->application_id;
            } else {
                $applicationId = $this->generateCompetencyApplicationId($request);
            }

            $metaPayload = $this->buildCcFormsMetaPayload(
                $request,
                $applicationId,
                $form,
                [
                    'payment_status' => $action === 'draft' ? 'N' : 'payment',
                    'old_application' => $form?->old_application ?? $request->input('old_application'),
                ]
            );

            if ($form) {
                $form->update($metaPayload);
            } else {
                $metaPayload['created_at'] = $this->dbNow;
                $form = CC_Forms_Meta::createForForm((string) ($metaPayload['form_name'] ?? $request->form_name ?? 'S'), $metaPayload);
            }


            if ($request->has('educational_level')) {
                foreach ($request->educational_level as $key => $level) {
                    if (
                        empty($level) &&
                        empty($request->institute_name[$key] ?? null) &&
                        empty($request->year_of_passing[$key] ?? null) &&
                        empty($request->certificate_no[$key] ?? null)
                    ) {
                        continue;
                    }

                    $eduId = $request->edu_id[$key] ?? null;
                    $education = $eduId
                        ? $this->formSDocumentHandler()->resolveMasterEducation($form, (int) $eduId, $level)
                        : null;

                    $isFileRemoved = isset($request->removed_document[$key]) && $request->removed_document[$key] == '1';

                    $docResolution = $this->resolveEducationDocumentForSave(
                        $request,
                        $key,
                        $form,
                        $request->form_name,
                        $education,
                        $isFileRemoved
                    );
                    $filePath = $docResolution['path'];
                    $pendingEduFile = $docResolution['pending_file'];

                    $monthRaw = $request->month_of_passing[$key] ?? null;
                    $monthVal = null;
                    if ($monthRaw !== null && $monthRaw !== '') {
                        $m = (int) ltrim((string) $monthRaw, '0');
                        if ($m >= 1 && $m <= 12) {
                            $monthVal = $m;
                        }
                    }

                    if ($education) {
                        $education->update([
                            'educational_level' => $level ?? $education->educational_level,
                            'institute_name'  => ($request->institute_name[$key] ?? null) !== null && $request->institute_name[$key] !== ''
                                ? $request->institute_name[$key]
                                : $education->institute_name,
                            'month_passing'   => $monthVal !== null ? $monthVal : $education->month_passing,
                            'year_of_passing' => ($request->year_of_passing[$key] ?? null) !== null
                                && $request->year_of_passing[$key] !== ''
                                && $request->year_of_passing[$key] !== '0'
                                ? $request->year_of_passing[$key]
                                : $education->year_of_passing,
                            'certificate_no'  => ($request->certificate_no[$key] ?? null) !== null && $request->certificate_no[$key] !== ''
                                ? $request->certificate_no[$key]
                                : $education->certificate_no,
                            'upload_document' => $filePath ?? $education->upload_document,
                        ]);

                        if ($pendingEduFile) {
                            $approvedPath = $this->applyPendingFormSEducationUpload(
                                $request,
                                $key,
                                $form,
                                $education->fresh(),
                                $pendingEduFile
                            );
                            if ($approvedPath !== null) {
                                $education->update(['upload_document' => $approvedPath]);
                            }
                        }
                    } else {
                        $upsertAttrs = [
                            'login_id'          => $loginId,
                            'application_id'    => $this->resolveFormSMasterApplicationId($form, $request->form_name),
                            'educational_level' => $level,
                        ];

                        $existingByKey = CC_Education::where($upsertAttrs)->first();

                        $uploadToStore = $filePath;
                        if ($uploadToStore === null && ! $isFileRemoved && $existingByKey && $existingByKey->upload_document) {
                            $uploadToStore = $existingByKey->upload_document;
                        }

                        $education = CC_Education::updateOrCreate(
                            $upsertAttrs,
                            [
                                'institute_name'  => $request->institute_name[$key],
                                'month_passing'   => $monthVal,
                                'year_of_passing' => $request->year_of_passing[$key],
                                'certificate_no'  => $request->certificate_no[$key] ?? null,
                                'upload_document' => $uploadToStore,
                            ]
                        );

                        if ($pendingEduFile) {
                            $approvedPath = $this->applyPendingFormSEducationUpload(
                                $request,
                                $key,
                                $form,
                                $education,
                                $pendingEduFile
                            );
                            if ($approvedPath !== null) {
                                $education->update(['upload_document' => $approvedPath]);
                            }
                        }
                    }
                }
            }

            $this->seedFormSDocumentsIfRenewal($form->fresh(), $request->form_name);
            

            if ($this->hasWorkExperiencePayload($request)) {
                $claimedWorkIds = [];
                $masterApplicationId = $this->resolveFormSMasterApplicationId($form, $request->form_name);

                foreach ($this->getWorkRowIndexes($request) as $key) {
                    $workRow = $this->mapWorkExperienceRow($request, $key, $request->form_name ?? null);
                    $this->upsertWorkExperienceDraftRow(
                        $request,
                        $key,
                        $loginId,
                        $applicationId,
                        $workRow,
                        $claimedWorkIds,
                        false,
                        $form,
                        $request->form_name ?? null
                    );
                }

                if (! empty($claimedWorkIds)) {
                    $this->resolveExperienceModelClass($form, $request->form_name)::where('application_id', $masterApplicationId)
                        ->whereNotIn('exp_id', $claimedWorkIds)
                        ->delete();
                }
            }

            $this->saveCompetencyProofDocuments($request, $form, $request->form_name ?? null);

            $this->linkCcDigitizationIfNeeded($request, $applicationId, $loginId);

            DB::commit();

            $linkedTempId = null;
            if (($request->appl_type ?? '') === 'D') {
                $linkedTempId = app(CcDigitizationLinkService::class)->resolveTempAppId(
                    $request->input('cc_digitization_temp_id'),
                    $loginId,
                    $applicationId,
                    $request->input('form_name')
                );
            }

            return response()->json([
                'status' => 'success',
                'message' => $action === 'draft' ? 'Draft saved successfully!' : 'Form submitted successfully!',
                'application_id' => $applicationId,
                'applicantName' => $form->applicant_name,
                'cc_digitization_temp_id' => $linkedTempId,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
        
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
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

        $existingForm = $applicationId
            ? CC_Forms_Meta::findByApplicationId($applicationId)
            : null;
        $masterApplicationId = $existingForm
            ? $this->resolveFormSMasterApplicationId($existingForm, $request->form_name ?? null)
            : $applicationId;
        $proofService = $this->proofDocumentService();
        $existingPhoto = $masterApplicationId
            ? $proofService->loadPhotoForView($masterApplicationId)
            : null;

        if (!$existingForm && $applicationId) {
            return response()->json(['status' => 'error', 'message' => 'Draft not found!'], 404);
        }

        $uploadPhotoRule = (! $existingPhoto || empty($existingPhoto->upload_path))
            ? 'image|mimes:jpg,jpeg,png|max:50'
            : 'nullable|image|mimes:jpg,jpeg,png|max:50';
        $uploadSignRule = 'nullable|image|mimes:jpg,jpeg,png|max:50';

        $aadhaarDocRule = ($existingForm && $masterApplicationId
            && ! $proofService->hasProofDocument($masterApplicationId, FormSProofDocumentService::PROOF_AADHAAR))
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
            'previously_valid_to' => 'nullable|date',
            'previously_issue_date' => 'nullable|date',
            'previously_valid_from' => 'nullable|date',
            'certificate_date'   => 'nullable|date',
            'certificate_valid_to' => 'nullable|date',
            'certificate_issue_date' => 'nullable|date',
            'certificate_valid_from' => 'nullable|date',
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
        if ($action !== 'draft') {
            $boardMemberErr = $this->validateFormSBoardMemberWorkRows($request);
            if ($boardMemberErr !== null) {
                return response()->json(['status' => 'error', 'message' => $boardMemberErr], 422);
            }
        }
        $loginId   = $request->login_id;
        $appl_type = $request->appl_type ?? 'R'; // ensure renewal
        $nowTs     = $this->dbNow;

        DB::beginTransaction();

        try {
            $form = $id
                ? (($found = CC_Forms_Meta::findByApplicationId($id)) && strtoupper((string) ($found->appl_type ?? '')) === 'R' ? $found : null)
                : null;

            if ($form) {
                $applicationId = $form->application_id;
            } else {
                $request->merge(['appl_type' => $appl_type]);
                $applicationId = $this->generateCompetencyApplicationId($request);
            }

            $metaPayload = $this->buildCcFormsMetaPayload(
                $request,
                $applicationId,
                $form,
                [
                    'appl_type' => $appl_type,
                    'payment_status' => $action === 'draft' ? 'N' : 'payment',
                    'old_application' => $form?->old_application ?? $id,
                ]
            );

            if ($form) {
                $form->update($metaPayload);
            } else {
                $metaPayload['created_at'] = $nowTs;
                $form = CC_Forms_Meta::createForForm((string) ($metaPayload['form_name'] ?? $request->form_name ?? 'S'), $metaPayload);
            }

            $type_of_apps = MstLicence::where('form_code', $form->form_name)
                ->select('licence_name')
                ->first();

            $this->seedFormSDocumentsIfRenewal($form->fresh(), $request->form_name);

            $masterApplicationId = $this->resolveFormSMasterApplicationId($form, $request->form_name);

            if ($request->has('educational_level')) {
                foreach ($request->educational_level as $key => $level) {
                    $levelName = $level ?? null;
                    $institute = $request->institute_name[$key] ?? null;
                    $monthRaw = $request->month_of_passing[$key] ?? null;
                    $month = null;
                    if ($monthRaw !== null && $monthRaw !== '') {
                        $monthInt = (int) trim((string) $monthRaw);
                        $month = ($monthInt >= 1 && $monthInt <= 12) ? $monthInt : null;
                    }
                    $year = $request->year_of_passing[$key] ?? null;
                    $certificateNo = $request->certificate_no[$key] ?? null;

                    $removed = isset($request->removed_document[$key]) && $request->removed_document[$key] == '1';
                    $eduId = $request->edu_id[$key] ?? null;
                    $upsertAttrs = [
                        'login_id' => $loginId,
                        'application_id' => $masterApplicationId,
                        'educational_level' => $levelName,
                    ];
                    $existingEducation = $eduId
                        ? $this->formSDocumentHandler()->resolveMasterEducation($form, (int) $eduId, $levelName)
                        : CC_Education::where($upsertAttrs)->first();

                    $docResolution = $this->resolveEducationDocumentForSave(
                        $request,
                        $key,
                        $form,
                        $request->form_name,
                        $existingEducation,
                        $removed
                    );
                    $finalDoc = $docResolution['path'];
                    $pendingEduFile = $docResolution['pending_file'];

                    $hasAnyData = ! empty($levelName) || ! empty($institute) || ! empty($month) || ! empty($year)
                        || ! empty($certificateNo) || ! empty($finalDoc) || $pendingEduFile;
                    if (! $hasAnyData) {
                        continue;
                    }

                    $education = CC_Education::updateOrCreate(
                        $upsertAttrs,
                        [
                            'institute_name' => $institute,
                            'month_passing' => $month,
                            'year_of_passing' => $year,
                            'certificate_no' => $certificateNo,
                            'upload_document' => $finalDoc ?? $existingEducation?->upload_document,
                        ]
                    );

                    if ($pendingEduFile) {
                        $approvedPath = $this->applyPendingFormSEducationUpload(
                            $request,
                            $key,
                            $form,
                            $education,
                            $pendingEduFile
                        );
                        if ($approvedPath !== null) {
                            $education->update(['upload_document' => $approvedPath]);
                        }
                    }
                }
            }

            // -------------------------
            // Work experience (renewal / competency)
            // -------------------------
            $this->persistWorkExperienceUpdateOrCreate(
                $request,
                $loginId,
                $applicationId,
                $request->form_name ?? null,
                $form
            );


            $this->saveCompetencyProofDocuments($request, $form, $request->form_name ?? null);

            

            DB::commit();

            return response()->json([
                'status'         => 'success',
                'message'        => $action === 'draft' ? 'Draft saved successfully!' : 'Form submitted successfully!',
                'application_id' => $applicationId,
                'applicantName'  => $form->applicant_name,
                'form_name'      => $form->form_name,
                'licence_name'   => $type_of_apps->licence_name,
                'date_apps'      => Carbon::parse($this->dbNow)->format('d-m-Y'),
                'amount'         => (float) ($request->amount ?? 0),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->formErrorResponse($e);
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
        $existingForm = CC_Forms_Meta::findByApplicationId($applicationId);
        $masterApplicationId = $existingForm
            ? $this->resolveFormSMasterApplicationId($existingForm, $request->form_name ?? null)
            : $applicationId;
        $proofService = $this->proofDocumentService();
        $existingPhoto = $proofService->loadPhotoForView($masterApplicationId);

        if (!$existingForm && $applicationId) {
            return response()->json(['status' => 'error', 'message' => 'Draft not found!'], 404);
        }

        if ($guard = $this->assertDigitizationCanSave($request, $applicationId)) {
            return $guard;
        }

        $uploadPhotoRule = (! $existingPhoto || empty($existingPhoto->upload_path))
            ? 'image|mimes:jpg,jpeg,png|max:50'
            : 'nullable|image|mimes:jpg,jpeg,png|max:50';

        // Signature is optional on edit; existing signature is kept if no new file
        $uploadSignRule = 'nullable|image|mimes:jpg,jpeg,png|max:50';

        $aadhaarDocRule = ($existingForm && ! $proofService->hasProofDocument($masterApplicationId, FormSProofDocumentService::PROOF_AADHAAR))
            ? 'mimes:pdf|max:250'
            : 'nullable|mimes:pdf|max:250';
            $request->validate([
                'login_id'           => 'nullable|string',
                'applicant_name'     => 'nullable|string|max:255',
                'fathers_name'       => 'nullable|string|max:255',
                'applicant_email'    => 'nullable|email|max:191',
                'applicants_address' => 'nullable|string|max:500',
                'd_o_b'              => 'nullable|date',
                'age'                => 'integer|min:18|max:100',
                'previously_number'  => 'nullable|string',
                'previously_date'    => 'nullable|date',
                'previously_valid_to' => 'nullable|date',
                'previously_issue_date' => 'nullable|date',
                'previously_valid_from' => 'nullable|date',
                'certificate_date'   => 'nullable|date',
                'certificate_valid_to' => 'nullable|date',
                'certificate_issue_date' => 'nullable|date',
                'certificate_valid_from' => 'nullable|date',
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
        if ($action !== 'draft') {
            $boardMemberErr = $this->validateFormSBoardMemberWorkRows($request);
            if ($boardMemberErr !== null) {
                return response()->json(['status' => 'error', 'message' => $boardMemberErr], 422);
            }
        }
        $loginId = $request->login_id;

        DB::beginTransaction();

        try {

            
            $appl_type = $request->appl_type ?? '';
            $form = ($found = CC_Forms_Meta::findByApplicationId($id)) && strtoupper((string) ($found->appl_type ?? '')) === strtoupper((string) $appl_type)
                ? $found
                : null;

            if ($form) {
                $applicationId = $form->application_id;
            } else {

                $metaService = app(CompetencyMetaService::class);
        $lastApplication = $metaService->latestApplicationId();
                if ($lastApplication) {
                    $lastNumber = (int) substr($lastApplication, -7);
                    $applicationId = $appl_type . $request->form_name . $request->license_name . date('y') . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
                } else {
                    $applicationId = $appl_type . $request->form_name . $request->license_name . date('y') . '1111111';
                }
            }

            $renewalPayload = array_merge([
                    'login_id'           => $loginId,
                    'applicant_name'     => $request->applicant_name ?? $request->Applicant_Name,
                    'fathers_name'       => $request->fathers_name ?? $request->Fathers_Name,
                    'applicant_email'    => $request->input('applicant_email'),
                    'applicant_address'  => $request->applicants_address
                        ?? $request->applicant_address
                        ?? $form?->applicant_address
                        ?? '',
                    'd_o_b'              => $request->d_o_b ?? $request->dob ?? $form?->d_o_b,
                    'age'                => $request->age,
                    'app_status'         => 'P',
                    'previous_scc_no'    => $request->previously_number ?? $request->previous_scc_no ?? 0,
                    'first_issue_date'   => $request->previously_issue_date ?: null,
                    'scc_from_date'      => $request->previously_valid_from ?: null,
                    'scc_to_date'        => $request->previously_valid_to ?: ($request->previously_date ?: null),
                    'form_name'          => $request->form_name,
                    'form_id'            => $request->form_id,
                    'certificate_name'   => $request->license_name ?? $request->certificate_name ?? $form?->certificate_name,
                    'wcc_no'             => $request->competency_certificate_no ?? $request->wcc_no ?? null,
                    'wcc_to'             => $request->certificate_valid_to ?: ($request->certificate_date ?: null),
                    'wcc_issue_date'     => $request->certificate_issue_date ?: null,
                    'wcc_from'           => $request->certificate_valid_from ?: null,
                    'appl_type'          => $appl_type,
                    'payment_status'     => $request->payment_status ?? 'Y',
                    'submitted_date'     => $this->dbNow,
                    'updated_at'         => $this->dbNow,
            ]);

            $renewal_form = CC_Forms_Meta::updateOrCreateByApplicationId(
                $applicationId,
                $renewalPayload,
                (string) ($request->form_name ?? 'S')
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
        
            $current_form = collect($form_details)->firstWhere('cert_licence_code', $renewal_form->certificate_name);
            $category_type = collect($form_category)->firstWhere('id', $current_form['category_id']);

            $licence_details['licence_name'] = $current_form['licence_name'];
        
            $licence_details['category_name'] = $category_type['category_name'];
            $licence_details['form_type'] = $renewal_form->appl_type;

            $this->seedFormSDocumentsIfRenewal($renewal_form->fresh(), $request->form_name);

            $masterApplicationId = $this->resolveFormSMasterApplicationId($renewal_form, $request->form_name);

            // Update Education Records
            if ($request->has('educational_level')) {
                $lastEdu = CC_Education::whereNotNull('edu_id')->latest('edu_id')->value('edu_id');

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

                    $removed = isset($request->removed_document[$key]) && $request->removed_document[$key] == '1';
                    $existingEdu = CC_Education::where([
                        'login_id'          => $loginId,
                        'application_id'    => $masterApplicationId,
                        'educational_level' => $levelName,
                    ])->first();

                    $docResolution = $this->resolveEducationDocumentForSave(
                        $request,
                        $key,
                        $renewal_form,
                        $request->form_name,
                        $existingEdu,
                        $removed
                    );
                    $finalDoc = $docResolution['path'];
                    $pendingEduFile = $docResolution['pending_file'];

                    // skip only if EVERYTHING is empty (avoid junk rows)
                    $hasAnyData = !empty($levelName) || !empty($institute) || !empty($year)
                        || !empty($certificateNo) || !empty($finalDoc) || $monthVal !== null || $pendingEduFile;
                    if (!$hasAnyData) continue;


                    $monthToSave = $monthVal !== null
                        ? $monthVal
                        : ($existingEdu ? $existingEdu->month_passing : null);

                    $education = CC_Education::updateOrCreate(
                        [
                            'login_id'          => $loginId,
                            'application_id'    => $masterApplicationId,
                            'educational_level' => $levelName,
                        ],
                        [
                            'institute_name'    => $institute,
                            'month_passing'     => $monthToSave,
                            'year_of_passing'   => $year,
                            'certificate_no'    => $certificateNo,
                            'upload_document'   => $finalDoc ?? $existingEdu?->upload_document
                        ]
                    );

                    if ($pendingEduFile) {
                        $approvedPath = $this->applyPendingFormSEducationUpload(
                            $request,
                            $key,
                            $renewal_form,
                            $education,
                            $pendingEduFile
                        );
                        if ($approvedPath !== null) {
                            $education->update(['upload_document' => $approvedPath]);
                        }
                    }
                }
            }
            
            $this->persistWorkExperienceUpdateOrCreate(
                $request,
                $loginId,
                $applicationId,
                $request->form_name ?? null,
                $renewal_form
            );

            $this->saveCompetencyProofDocuments($request, $renewal_form, $request->form_name ?? null);

            $this->linkCcDigitizationIfNeeded($request, $applicationId, $loginId);

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

            return $this->formErrorResponse($e);
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
     * AJAX: upload a single work PDF for competency forms (legacy public path).
     * Education certificates use versioned storage on form save — not this endpoint.
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

        if ($request->kind === 'education') {
            return response()->json([
                'success' => false,
                'message' => 'Education certificates are stored through the application save flow. Attach the file and save or submit the form.',
            ], 422);
        }

        if ((string) Auth::user()->login_id !== (string) $request->login_id) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $dir = 'work_experience';
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
        if ($path === null || $path === '') {
            return false;
        }

        if ($kind === 'education') {
            if ($this->hasValidCompetencyEducationDocFormat($path)) {
                return Storage::disk(config('document_versioning.disk', 'private_documents'))->exists($path);
            }

            return CC_Doc_Log::query()
                ->where(function ($query) use ($path) {
                    $query->where('file_path', $path)
                        ->orWhere('old_file_path', $path);
                })
                ->where('module_type', 'education')
                ->exists();
        }

        if (str_starts_with($path, 'FORM_')) {
            return Storage::disk(config('document_versioning.disk', 'private_documents'))
                ->exists($path);
        }

        if (! $this->hasValidCompetencyAjaxDocFormat($path, $kind)) {
            return false;
        }

        return is_file(public_path($path));
    }

    private function hasValidCompetencyEducationDocFormat(?string $path): bool
    {
        if ($path === null || $path === '') {
            return false;
        }

        return (bool) preg_match('#^FORM_[A-Z]+/#', $path);
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

        if ($kind === 'education') {
            return $this->hasValidCompetencyEducationDocFormat($path);
        }

        if (str_starts_with($path, 'FORM_')) {
            return (bool) preg_match('/^[a-zA-Z0-9_\/.\-]+$/', $path);
        }

        $prefix = 'work_experience/';
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

    private function generateCompetencyApplicationId(Request $request): string
    {
        $applType = $request->appl_type ?? '';
        $metaService = app(CompetencyMetaService::class);
        $lastApplication = $metaService->latestApplicationId();
        if ($lastApplication) {
            $lastNumber = (int) substr($lastApplication, -7);
            $next = str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);
        } else {
            $next = '1111111';
        }

        $prefix = in_array($applType, ['R', 'D'], true) ? $applType : '';

        return $prefix . $request->form_name . $request->license_name . date('y') . $next;
    }

    private function resolveApplicantName(Request $request, ?CC_CompetencyMeta $existingForm = null): string
    {
        $name = trim((string) ($request->input('applicant_name', $request->input('Applicant_Name', ''))));
        if ($name !== '') {
            return $name;
        }

        if ($existingForm) {
            $existing = trim((string) ($existingForm->applicant_name ?? ''));
            if ($existing !== '') {
                return $existing;
            }
        }

        $user = Auth::user();
        if ($user) {
            $fromUser = trim(implode(' ', array_filter([
                $user->salutation ?? '',
                $user->first_name ?? '',
                $user->last_name ?? '',
            ])));
            if ($fromUser !== '') {
                return $fromUser;
            }
        }

        return 'Applicant';
    }

    private function linkCcDigitizationIfNeeded(Request $request, string $applicationId, string $loginId): void
    {
        if (($request->appl_type ?? '') !== 'D') {
            return;
        }

        $loginId = $this->resolveDigitizationLoginId($request, $loginId);
        if ($loginId === '') {
            return;
        }

        $linker = app(CcDigitizationLinkService::class);
        $tempAppId = $linker->resolveTempAppId(
            $request->input('cc_digitization_temp_id'),
            $loginId,
            $applicationId,
            $request->input('form_name')
        );

        $linker->linkToApplication($tempAppId, $applicationId, $loginId);
    }

    private function resolveDigitizationLoginId(Request $request, ?string $loginId = null): string
    {
        $id = trim((string) ($loginId ?? $request->input('login_id', '')));
        if ($id !== '') {
            return $id;
        }

        $user = Auth::user();

        return $user ? (string) $user->login_id : '';
    }

    private function assertDigitizationCanSave(Request $request, ?string $applicationId = null): ?\Illuminate\Http\JsonResponse
    {
        if (($request->appl_type ?? '') !== 'D') {
            return null;
        }

        $loginId = (string) $request->input('login_id', '');
        $existingId = trim((string) ($applicationId ?? $request->input('application_id', '')));

        // Draft resubmit / edit — application already exists; do not force the cert modal again.
        if ($existingId !== '' && CC_Forms_Meta::existsByApplicationId($existingId)) {
            return null;
        }

        $linker = app(CcDigitizationLinkService::class);

        $ok = $linker->assertValidForNewSave($request->input('cc_digitization_temp_id'), $loginId);

        if (!$ok) {
            return response()->json([
                'status' => 'error',
                'message' => 'Complete digitization certificate details first.',
            ], 422);
        }

        return null;
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
