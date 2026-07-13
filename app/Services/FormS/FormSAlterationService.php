<?php

namespace App\Services\FormS;

use App\Models\CC_Experience;
use App\Models\CC_Forms_Meta;
use App\Models\Competency\CC_CompetencyMeta;
use App\Services\Competency\CompetencyDocumentReviewService;
use App\Models\Mst_experience;
use App\Services\Competency\CompetencyCertificateService;
use App\Services\Competency\CompetencyMetaService;
use App\Models\Payment;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbApplicantsSign;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FormSAlterationService
{
    public function __construct(
        protected FormSApplicationWorkflowService $workflowService,
        protected FormSDocumentUploadHandler $documentHandler,
        protected FormSDocumentVersionService $documentVersionService
    ) {}

    /**
     * @return array{ok: bool, message?: string, application?: CC_CompetencyMeta}
     */
    public function verifyParentApplication(string $parentApplicationId, string $loginId): array
    {
        $parentApplicationId = trim($parentApplicationId);
        if ($parentApplicationId === '') {
            return ['ok' => false, 'message' => 'Application ID or Certificate Number is required.'];
        }

        $paidStatuses = ['payment', 'paid', 'Y', 'y'];

        $parent = CC_Forms_Meta::where('application_id', $parentApplicationId)
            ->where('login_id', $loginId)
            ->where('form_name', 'S')
            ->whereIn('appl_type', ['N', 'R', 'D'])
            ->where(function ($q) use ($paidStatuses) {
                $q->whereIn('payment_status', $paidStatuses)
                    ->orWhereRaw("LOWER(TRIM(COALESCE(payment_status, ''))) IN ('y','payment','paid')");
            })
            ->first();

        if (!$parent) {
            $parent = CC_Forms_Meta::where('certificate_no', $parentApplicationId)
                ->where('login_id', $loginId)
                ->where('form_name', 'S')
                ->whereIn('appl_type', ['N', 'R', 'D'])
                ->where(function ($q) use ($paidStatuses) {
                    $q->whereIn('payment_status', $paidStatuses)
                        ->orWhereRaw("LOWER(TRIM(COALESCE(payment_status, ''))) IN ('y','payment','paid')");
                })
                ->first();
        }

        if (!$parent) {
            return ['ok' => false, 'message' => 'No valid issued Form S application found for your account.'];
        }

        $pendingAlteration = CC_Forms_Meta::where('old_application', $parent->application_id)
            ->where('appl_type', 'A')
            ->where('login_id', $loginId)
            ->where(function ($q) {
                $q->whereIn('app_status', ['P', ''])
                    ->orWhereNull('app_status');
            })
            ->whereIn('payment_status', ['draft', 'payment', 'Y', 'y'])
            ->latest('app_id')
            ->first();

        if ($pendingAlteration && in_array(strtolower((string) $pendingAlteration->payment_status), ['payment', 'y'], true)) {
            return ['ok' => false, 'message' => 'An alteration request is already submitted for this certificate.'];
        }

        return ['ok' => true, 'application' => $parent];
    }

    public function loadParentContext(CC_CompetencyMeta $parent): array
    {
        $masterId = $this->workflowService->masterApplication($parent)->application_id;

        $eduDetails = DB::table('cc_edu')
            ->where('application_id', $masterId)
            ->orderBy('year_of_passing', 'desc')
            ->get();

        $expDetails = CC_Experience::where('application_id', $masterId)
            ->orderBy('exp_id')
            ->get();

        $licenseDetails = app(CompetencyCertificateService::class)->asLicenseDetails(
            (string) $parent->application_id,
            $parent->form_name ?? 'S'
        ) ?? DB::table('tnelb_license')
            ->where('application_id', $parent->application_id)
            ->first();

        $alterationDraft = CC_Forms_Meta::where('old_application', $parent->application_id)
            ->where('appl_type', 'A')
            ->where('login_id', $parent->login_id)
            ->where('payment_status', 'draft')
            ->latest('app_id')
            ->first();

        $applicantPhoto = $this->resolveApplicantPhoto($parent);
        $proofDoc = $this->resolveApplicantSign($parent);

        return compact('eduDetails', 'expDetails', 'licenseDetails', 'alterationDraft', 'applicantPhoto', 'proofDoc');
    }

    /**
     * Staff applicant detail: master education, merged work rows, media, alteration proofs.
     *
     * @return array{
     *     educationalQualifications: \Illuminate\Support\Collection,
     *     workExperience: Collection,
     *     uploadedPhoto: ?TnelbApplicantPhoto,
     *     uploadedSign: ?TnelbApplicantsSign,
     *     alterationProofs: Collection,
     *     parentApplication: CC_CompetencyMeta
     * }
     */
    public function buildStaffReviewContext(CC_CompetencyMeta $application): array
    {
        return app(CompetencyDocumentReviewService::class)->buildStaffReviewContext($application);
    }

    protected function resolveApplicantPhoto(CC_CompetencyMeta $parent): ?TnelbApplicantPhoto
    {
        foreach ($this->mediaApplicationIds($parent) as $applicationId) {
            $photo = TnelbApplicantPhoto::where('application_id', $applicationId)->first();
            if ($photo && trim((string) ($photo->upload_path ?? '')) !== '') {
                return $photo;
            }
        }

        return null;
    }

    protected function resolveApplicantSign(CC_CompetencyMeta $parent): ?TnelbApplicantsSign
    {
        foreach ($this->mediaApplicationIds($parent) as $applicationId) {
            $sign = TnelbApplicantsSign::where('application_id', $applicationId)->first();
            if ($sign && trim((string) ($sign->uploaded_doc ?? '')) !== '') {
                return $sign;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function mediaApplicationIds(CC_CompetencyMeta $parent): array
    {
        $ids = [];
        $seen = [];
        $current = $parent;

        while ($current) {
            $appId = trim((string) ($current->application_id ?? ''));
            if ($appId !== '' && !isset($seen[$appId])) {
                $ids[] = $appId;
                $seen[$appId] = true;
            }

            $oldId = trim((string) ($current->old_application ?? ''));
            if ($oldId === '' || isset($seen[$oldId])) {
                break;
            }

            $current = CC_Forms_Meta::findByApplicationId($oldId);
        }

        return $ids;
    }

    public function storeAlterationRequest(Request $request): CC_CompetencyMeta
    {
        $loginId = (string) $request->input('login_id');
        $parentId = trim((string) $request->input('parent_application_id'));

        $verify = $this->verifyParentApplication($parentId, $loginId);
        if (!$verify['ok']) {
            throw new RuntimeException($verify['message'] ?? 'Invalid parent application.');
        }

        /** @var CC_CompetencyMeta $parent */
        $parent = $verify['application'];
        $parentName = trim((string) $parent->applicant_name);
        $parentAddress = trim((string) ($parent->applicant_address ?? $parent->applicants_address ?? ''));
        $newName = trim((string) $request->input('applicant_name', ''));
        $newAddress = trim((string) $request->input('applicants_address', $request->input('applicant_address', '')));

        $alterName = $newName !== $parentName;
        $alterAddress = $newAddress !== $parentAddress;
        $alterWork = $this->requestHasNewWorkRows($request);

        if (!$alterName && !$alterAddress && !$alterWork) {
            throw new RuntimeException('Make at least one change before submitting the alteration.');
        }

        if ($alterName) {
            if ($newName === '') {
                throw new RuntimeException('Applicant name cannot be empty.');
            }
            if (! $this->alterationHasProof($request, $parent, FormSProofDocumentService::PROOF_NAME_CHANGE, 'name_alteration_proof')) {
                throw new RuntimeException('Supporting proof document is required for name alteration.');
            }
        }

        if ($alterAddress) {
            if ($newAddress === '') {
                throw new RuntimeException('Applicant address cannot be empty.');
            }
            if (! $this->alterationHasProof($request, $parent, FormSProofDocumentService::PROOF_ADDRESS, 'address_alteration_proof')) {
                throw new RuntimeException('Supporting proof document is required for address alteration.');
            }
        }

        return DB::transaction(function () use (
            $request,
            $parent,
            $loginId,
            $alterName,
            $alterAddress,
            $alterWork,
            $newName,
            $newAddress,
            $parentAddress
        ) {
            $child = CC_Forms_Meta::where('old_application', $parent->application_id)
                ->where('appl_type', 'A')
                ->where('login_id', $loginId)
                ->where('payment_status', 'draft')
                ->latest('app_id')
                ->first();

            $formName = (string) ($parent->form_name ?? 'S');
            $certName = (string) ($parent->certificate_name ?? $parent->license_name ?? '');

            $payload = [
                'login_id' => $loginId,
                'applicant_name' => $alterName ? $newName : $parent->applicant_name,
                'fathers_name' => $parent->fathers_name,
                'applicant_email' => $parent->applicant_email,
                'applicant_address' => $alterAddress ? $newAddress : $parentAddress,
                'd_o_b' => $parent->d_o_b,
                'age' => $parent->age,
                'previous_scc_no' => $parent->previous_scc_no ?? $parent->previously_number ?? null,
                'scc_to_date' => $parent->scc_to_date ?? $parent->previously_valid_to ?? null,
                'first_issue_date' => $parent->first_issue_date ?? $parent->previously_issue_date ?? null,
                'scc_from_date' => $parent->scc_from_date ?? $parent->previously_valid_from ?? null,
                'form_name' => $formName,
                'form_id' => $parent->form_id,
                'certificate_name' => $certName,
                'certificate_no' => $parent->certificate_no,
                'wcc_to' => $parent->wcc_to ?? $parent->certificate_valid_to ?? null,
                'wcc_issue_date' => $parent->wcc_issue_date ?? $parent->certificate_issue_date ?? null,
                'wcc_from' => $parent->wcc_from ?? $parent->certificate_valid_from ?? null,
                'appl_type' => 'A',
                'old_application' => $parent->application_id,
                'app_status' => 'P',
                'payment_status' => 'Y',
                'submitted_date' => now(),
                'updated_at' => now(),
            ];

            if ($child) {
                $child->update($payload);
            } else {
                $lastApplication = app(CompetencyMetaService::class)->latestApplicationId();
                $lastNumber = $lastApplication ? (int) substr($lastApplication, -7) : 1111110;
                $newApplicationId = 'A' . $formName . $certName . date('y')
                    . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);

                $child = CC_Forms_Meta::createForForm($formName, array_merge($payload, [
                    'application_id' => $newApplicationId,
                    'created_at' => now(),
                ]));
            }

            if ($alterName && $request->hasFile('name_alteration_proof')) {
                $this->storeAlterationProof($child, $request->file('name_alteration_proof'), 'name_proof');
            }

            if ($alterAddress && $request->hasFile('address_alteration_proof')) {
                $this->storeAlterationProof($child, $request->file('address_alteration_proof'), 'address_proof');
            }

            if ($alterWork) {
                $this->storeNewExperienceRows($request, $child, $loginId);
            }

            Payment::updateOrCreate(
                [
                    'login_id' => $loginId,
                    'application_id' => $child->application_id,
                ],
                [
                    'transaction_id' => 'ALT' . time(),
                    'payment_status' => 'success',
                    'amount' => 0,
                    'form_name' => $child->form_name,
                    'license_name' => $child->certificate_name ?? $child->license_name,
                    'payment_mode' => 'N/A',
                    'late_fees' => 0,
                    'late_months' => 0,
                    'transaction_date' => now()->toDateString(),
                ]
            );

            return $child->fresh();
        });
    }

    public function saveAlterationDraft(Request $request): CC_CompetencyMeta
    {
        $loginId = (string) $request->input('login_id');
        $parentId = trim((string) $request->input('parent_application_id'));

        $verify = $this->verifyParentApplication($parentId, $loginId);
        if (!$verify['ok']) {
            throw new RuntimeException($verify['message'] ?? 'Invalid parent application.');
        }

        /** @var CC_CompetencyMeta $parent */
        $parent = $verify['application'];
        $parentAddress = (string) ($parent->applicant_address ?? $parent->applicants_address ?? '');
        $newName = trim((string) $request->input('applicant_name', $parent->applicant_name));
        $newAddress = trim((string) $request->input('applicants_address', $request->input('applicant_address', $parentAddress)));

        return DB::transaction(function () use ($request, $parent, $loginId, $newName, $newAddress, $parentAddress) {
            $child = $this->findOrCreateAlterationDraftChild($parent, $loginId);

            $child->update([
                'applicant_name' => $newName !== '' ? $newName : $parent->applicant_name,
                'applicant_address' => $newAddress !== '' ? $newAddress : $parentAddress,
                'payment_status' => 'draft',
                'app_status' => 'P',
                'updated_at' => now(),
            ]);

            if ($request->hasFile('name_alteration_proof')) {
                $this->storeAlterationProof($child, $request->file('name_alteration_proof'), 'name_proof');
            }

            if ($request->hasFile('address_alteration_proof')) {
                $this->storeAlterationProof($child, $request->file('address_alteration_proof'), 'address_proof');
            }

            if ($this->requestHasNewWorkRows($request)) {
                CC_Experience::where('application_id', $child->application_id)->delete();
                try {
                    $this->storeNewExperienceRows($request, $child, $loginId);
                } catch (RuntimeException $e) {
                    // Allow partial work rows on draft save.
                }
            }

            return $child->fresh();
        });
    }

    protected function findOrCreateAlterationDraftChild(CC_CompetencyMeta $parent, string $loginId): CC_CompetencyMeta
    {
        $child = CC_Forms_Meta::where('old_application', $parent->application_id)
            ->where('appl_type', 'A')
            ->where('login_id', $loginId)
            ->where('payment_status', 'draft')
            ->latest('app_id')
            ->first();

        if ($child) {
            return $child;
        }

        $formName = (string) ($parent->form_name ?? 'S');
        $certName = (string) ($parent->certificate_name ?? $parent->license_name ?? '');
        $parentAddress = (string) ($parent->applicant_address ?? $parent->applicants_address ?? '');

        $lastApplication = app(CompetencyMetaService::class)->latestApplicationId();
        $lastNumber = $lastApplication ? (int) substr($lastApplication, -7) : 1111110;
        $newApplicationId = 'A' . $formName . $certName . date('y')
            . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);

        return CC_Forms_Meta::createForForm($formName, [
            'application_id' => $newApplicationId,
            'login_id' => $loginId,
            'applicant_name' => $parent->applicant_name,
            'fathers_name' => $parent->fathers_name,
            'applicant_email' => $parent->applicant_email,
            'applicant_address' => $parentAddress,
            'd_o_b' => $parent->d_o_b,
            'age' => $parent->age,
            'previous_scc_no' => $parent->previous_scc_no ?? $parent->previously_number ?? null,
            'scc_to_date' => $parent->scc_to_date ?? $parent->previously_valid_to ?? null,
            'first_issue_date' => $parent->first_issue_date ?? $parent->previously_issue_date ?? null,
            'scc_from_date' => $parent->scc_from_date ?? $parent->previously_valid_from ?? null,
            'form_name' => $formName,
            'form_id' => $parent->form_id,
            'certificate_name' => $certName,
            'certificate_no' => $parent->certificate_no,
            'wcc_to' => $parent->wcc_to ?? $parent->certificate_valid_to ?? null,
            'wcc_issue_date' => $parent->wcc_issue_date ?? $parent->certificate_issue_date ?? null,
            'wcc_from' => $parent->wcc_from ?? $parent->certificate_valid_from ?? null,
            'appl_type' => 'A',
            'old_application' => $parent->application_id,
            'app_status' => 'P',
            'payment_status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function storeAlterationProof(CC_CompetencyMeta $child, UploadedFile $file, string $documentType): void
    {
        app(FormSProofDocumentService::class)->saveAlterationProofUpload($child, $file, $documentType);
    }

    protected function alterationHasProof(
        Request $request,
        CC_CompetencyMeta $parent,
        string $proofName,
        string $uploadField
    ): bool {
        if ($request->hasFile($uploadField)) {
            return true;
        }

        $draft = CC_Forms_Meta::where('old_application', $parent->application_id)
            ->where('appl_type', FormSProofDocumentService::ALTERATION_APP_TYPE)
            ->where('login_id', $parent->login_id)
            ->where('payment_status', 'draft')
            ->latest('app_id')
            ->first();

        if (! $draft) {
            return false;
        }

        return app(FormSProofDocumentService::class)->hasProofDocument(
            $draft->application_id,
            $proofName,
            FormSProofDocumentService::ALTERATION_APP_TYPE
        );
    }

    protected function requestHasNewWorkRows(Request $request): bool
    {
        return $this->collectNewWorkRowIndexes($request) !== [];
    }

    /**
     * @return list<int|string>
     */
    protected function collectNewWorkRowIndexes(Request $request): array
    {
        $workIds = (array) $request->input('work_id', []);
        $existingFlags = (array) $request->input('fs_alt_existing_work', []);
        $employers = (array) $request->input('work_employer_name', []);
        if ($employers === []) {
            $employers = (array) $request->input('work_level', []);
        }

        $designations = (array) $request->input('designation', []);
        $indexes = [];

        foreach (array_keys($employers) as $key) {
            if ($this->isExistingWorkRowIndex($workIds, $existingFlags, $key)) {
                continue;
            }
            $orgName = trim((string) ($employers[$key] ?? ''));
            $designation = trim((string) ($designations[$key] ?? ''));
            if ($orgName !== '' && $designation !== '') {
                $indexes[] = $key;
            }
        }

        return $indexes;
    }

    protected function isExistingWorkRowIndex(array $workIds, array $existingFlags, int|string $key): bool
    {
        if (!empty($workIds[$key])) {
            return true;
        }

        return !empty($existingFlags[$key]) && (string) $existingFlags[$key] === '1';
    }

    protected function storeNewExperienceRows(Request $request, CC_CompetencyMeta $child, string $loginId): void
    {
        $indexes = $this->collectNewWorkRowIndexes($request);

        if ($indexes === []) {
            throw new RuntimeException('Add at least one new work experience entry.');
        }

        $workIds = (array) $request->input('work_id', []);
        $existingFlags = (array) $request->input('fs_alt_existing_work', []);
        $employers = (array) $request->input('work_employer_name', []);
        if ($employers === []) {
            $employers = (array) $request->input('work_level', []);
        }

        $designations = (array) $request->input('designation', []);
        $empTypes = (array) $request->input('work_employment_type', []);
        $orgAddresses = (array) $request->input('work_org_address', []);
        $fromDates = (array) $request->input('work_date_from', []);
        $toDates = (array) $request->input('work_date_to', []);
        $durY = (array) $request->input('work_duration_y', []);
        $durM = (array) $request->input('work_duration_m', []);
        $durD = (array) $request->input('work_duration_d', []);
        $natures = (array) $request->input('work_nature', []);
        $voltages = (array) $request->input('work_voltage', []);
        $kvas = (array) $request->input('work_transformer_kva', []);

        $created = 0;
        foreach ($indexes as $key) {
            $orgName = trim((string) ($employers[$key] ?? ''));
            $designation = trim((string) ($designations[$key] ?? ''));
            if ($orgName === '' || $designation === '') {
                continue;
            }

            $created++;

            $experience = CC_Experience::create([
                'login_id' => $loginId,
                'application_id' => $child->application_id,
                'emp_type' => $empTypes[$key] ?? null,
                'org_name' => $orgName,
                'org_address' => $orgAddresses[$key] ?? null,
                'designation' => $designation,
                'from_date' => $fromDates[$key] ?? null,
                'to_date' => $toDates[$key] ?? null,
                'total_y' => (int) ($durY[$key] ?? 0),
                'total_m' => (int) ($durM[$key] ?? 0),
                'total_d' => (int) ($durD[$key] ?? 0),
                'nature_work' => $natures[$key] ?? null,
                'voltage_level' => $voltages[$key] ?? null,
                'transformer_kva' => $kvas[$key] ?? null,
            ]);

            $supportFile = $request->file('work_document');
            if (is_array($supportFile) && isset($supportFile[$key])) {
                $supportFile = $supportFile[$key];
            } elseif (!$supportFile instanceof UploadedFile) {
                $supportFile = null;
            }
            if ($supportFile && $supportFile->isValid()) {
                $path = $this->documentHandler->handleExperienceSupportUpload(
                    $child,
                    $experience,
                    $supportFile
                );
                if ($path) {
                    $experience->update(['support_document' => $path]);
                }
            }
        }

        if ($created === 0) {
            throw new RuntimeException('Add at least one new work experience entry.');
        }
    }
}
