<?php

namespace App\Services\FormS;

use App\Services\Competency\CompetencyDocumentReviewService;
use App\Models\Mst_experience;
use App\Models\Mst_Form_s_w;
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
     * @return array{ok: bool, message?: string, application?: Mst_Form_s_w}
     */
    public function verifyParentApplication(string $parentApplicationId, string $loginId): array
    {
        $parentApplicationId = trim($parentApplicationId);
        if ($parentApplicationId === '') {
            return ['ok' => false, 'message' => 'Application ID or Certificate Number is required.'];
        }

        $parent = Mst_Form_s_w::where('application_id', $parentApplicationId)
            ->where('login_id', $loginId)
            ->where('form_name', 'S')
            ->whereIn('appl_type', ['N', 'R', 'D'])
            ->whereIn('payment_status', ['payment', 'paid'])
            ->first();

        if (!$parent) {
            $parent = Mst_Form_s_w::where('license_number', $parentApplicationId)
                ->where('login_id', $loginId)
                ->where('form_name', 'S')
                ->whereIn('appl_type', ['N', 'R', 'D'])
                ->whereIn('payment_status', ['payment', 'paid'])
                ->first();
        }

        if (!$parent) {
            return ['ok' => false, 'message' => 'No valid issued Form S application found for your account.'];
        }

        $pendingAlteration = Mst_Form_s_w::where('old_application', $parent->application_id)
            ->where('appl_type', 'A')
            ->where('login_id', $loginId)
            ->whereIn('status', ['P', ''])
            ->whereIn('payment_status', ['draft', 'payment'])
            ->latest('id')
            ->first();

        if ($pendingAlteration && strtolower((string) $pendingAlteration->payment_status) === 'payment') {
            return ['ok' => false, 'message' => 'An alteration request is already submitted for this certificate.'];
        }

        return ['ok' => true, 'application' => $parent];
    }

    public function loadParentContext(Mst_Form_s_w $parent): array
    {
        $masterId = $this->workflowService->masterApplication($parent)->application_id;

        $eduDetails = DB::table('tnelb_applicants_edu')
            ->where('application_id', $masterId)
            ->orderBy('year_of_passing', 'desc')
            ->get();

        $expDetails = Mst_experience::where('application_id', $masterId)
            ->orderBy('exp_id')
            ->get();

        $licenseDetails = DB::table('tnelb_license')
            ->where('application_id', $parent->application_id)
            ->first();

        $alterationDraft = Mst_Form_s_w::where('old_application', $parent->application_id)
            ->where('appl_type', 'A')
            ->where('login_id', $parent->login_id)
            ->where('payment_status', 'draft')
            ->latest('id')
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
     *     parentApplication: Mst_Form_s_w
     * }
     */
    public function buildStaffReviewContext(Mst_Form_s_w $application): array
    {
        return app(CompetencyDocumentReviewService::class)->buildStaffReviewContext($application);
    }

    protected function resolveApplicantPhoto(Mst_Form_s_w $parent): ?TnelbApplicantPhoto
    {
        foreach ($this->mediaApplicationIds($parent) as $applicationId) {
            $photo = TnelbApplicantPhoto::where('application_id', $applicationId)->first();
            if ($photo && trim((string) ($photo->upload_path ?? '')) !== '') {
                return $photo;
            }
        }

        return null;
    }

    protected function resolveApplicantSign(Mst_Form_s_w $parent): ?TnelbApplicantsSign
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
    protected function mediaApplicationIds(Mst_Form_s_w $parent): array
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

            $current = Mst_Form_s_w::where('application_id', $oldId)->first();
        }

        return $ids;
    }

    public function storeAlterationRequest(Request $request): Mst_Form_s_w
    {
        $loginId = (string) $request->input('login_id');
        $parentId = trim((string) $request->input('parent_application_id'));

        $verify = $this->verifyParentApplication($parentId, $loginId);
        if (!$verify['ok']) {
            throw new RuntimeException($verify['message'] ?? 'Invalid parent application.');
        }

        /** @var Mst_Form_s_w $parent */
        $parent = $verify['application'];
        $parentName = trim((string) $parent->applicant_name);
        $parentAddress = trim((string) $parent->applicants_address);
        $newName = trim((string) $request->input('applicant_name', ''));
        $newAddress = trim((string) $request->input('applicants_address', ''));

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
            $newAddress
        ) {
            $child = Mst_Form_s_w::where('old_application', $parent->application_id)
                ->where('appl_type', 'A')
                ->where('login_id', $loginId)
                ->where('payment_status', 'draft')
                ->latest('id')
                ->first();

            $payload = [
                'login_id' => $loginId,
                'applicant_name' => $alterName ? $newName : $parent->applicant_name,
                'fathers_name' => $parent->fathers_name,
                'applicant_email' => $parent->applicant_email,
                'applicants_address' => $alterAddress ? $newAddress : $parent->applicants_address,
                'd_o_b' => $parent->d_o_b,
                'age' => $parent->age,
                'previously_number' => $parent->previously_number,
                'previously_valid_to' => $parent->previously_valid_to,
                'previously_issue_date' => $parent->previously_issue_date,
                'previously_valid_from' => $parent->previously_valid_from,
                'wireman_details' => $parent->wireman_details,
                'form_name' => $parent->form_name,
                'form_id' => $parent->form_id,
                'license_name' => $parent->license_name,
                'aadhaar' => $parent->aadhaar,
                'pancard' => $parent->pancard,
                'aadhaar_doc' => $parent->aadhaar_doc,
                'pan_doc' => $parent->pan_doc,
                'certificate_no' => $parent->certificate_no,
                'certificate_valid_to' => $parent->certificate_valid_to,
                'certificate_issue_date' => $parent->certificate_issue_date,
                'certificate_valid_from' => $parent->certificate_valid_from,
                'cert_verify' => $parent->cert_verify,
                'license_verify' => $parent->license_verify,
                'license_number' => $parent->license_number,
                'appl_type' => 'A',
                'old_application' => $parent->application_id,
                'status' => 'P',
                'payment_status' => 'payment',
                'submitted_date' => now(),
                'updated_at' => now(),
            ];

            if ($child) {
                $child->update($payload);
            } else {
                $lastApplication = Mst_Form_s_w::latest('id')->value('application_id');
                $lastNumber = $lastApplication ? (int) substr($lastApplication, -7) : 1111110;
                $newApplicationId = 'A' . $parent->form_name . $parent->license_name . date('y')
                    . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);

                $child = Mst_Form_s_w::create(array_merge($payload, [
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
                    'license_name' => $child->license_name,
                    'payment_mode' => 'N/A',
                    'late_fees' => 0,
                    'late_months' => 0,
                    'transaction_date' => now()->toDateString(),
                ]
            );

            return $child->fresh();
        });
    }

    public function saveAlterationDraft(Request $request): Mst_Form_s_w
    {
        $loginId = (string) $request->input('login_id');
        $parentId = trim((string) $request->input('parent_application_id'));

        $verify = $this->verifyParentApplication($parentId, $loginId);
        if (!$verify['ok']) {
            throw new RuntimeException($verify['message'] ?? 'Invalid parent application.');
        }

        /** @var Mst_Form_s_w $parent */
        $parent = $verify['application'];
        $newName = trim((string) $request->input('applicant_name', $parent->applicant_name));
        $newAddress = trim((string) $request->input('applicants_address', $parent->applicants_address));

        return DB::transaction(function () use ($request, $parent, $loginId, $newName, $newAddress) {
            $child = $this->findOrCreateAlterationDraftChild($parent, $loginId);

            $child->update([
                'applicant_name' => $newName !== '' ? $newName : $parent->applicant_name,
                'applicants_address' => $newAddress !== '' ? $newAddress : $parent->applicants_address,
                'payment_status' => 'draft',
                'status' => 'P',
                'updated_at' => now(),
            ]);

            if ($request->hasFile('name_alteration_proof')) {
                $this->storeAlterationProof($child, $request->file('name_alteration_proof'), 'name_proof');
            }

            if ($request->hasFile('address_alteration_proof')) {
                $this->storeAlterationProof($child, $request->file('address_alteration_proof'), 'address_proof');
            }

            if ($this->requestHasNewWorkRows($request)) {
                Mst_experience::where('application_id', $child->application_id)->delete();
                try {
                    $this->storeNewExperienceRows($request, $child, $loginId);
                } catch (RuntimeException $e) {
                    // Allow partial work rows on draft save.
                }
            }

            return $child->fresh();
        });
    }

    protected function findOrCreateAlterationDraftChild(Mst_Form_s_w $parent, string $loginId): Mst_Form_s_w
    {
        $child = Mst_Form_s_w::where('old_application', $parent->application_id)
            ->where('appl_type', 'A')
            ->where('login_id', $loginId)
            ->where('payment_status', 'draft')
            ->latest('id')
            ->first();

        if ($child) {
            return $child;
        }

        $lastApplication = Mst_Form_s_w::latest('id')->value('application_id');
        $lastNumber = $lastApplication ? (int) substr($lastApplication, -7) : 1111110;
        $newApplicationId = 'A' . $parent->form_name . $parent->license_name . date('y')
            . str_pad($lastNumber + 1, 7, '0', STR_PAD_LEFT);

        return Mst_Form_s_w::create([
            'application_id' => $newApplicationId,
            'login_id' => $loginId,
            'applicant_name' => $parent->applicant_name,
            'fathers_name' => $parent->fathers_name,
            'applicant_email' => $parent->applicant_email,
            'applicants_address' => $parent->applicants_address,
            'd_o_b' => $parent->d_o_b,
            'age' => $parent->age,
            'previously_number' => $parent->previously_number,
            'previously_valid_to' => $parent->previously_valid_to,
            'previously_issue_date' => $parent->previously_issue_date,
            'previously_valid_from' => $parent->previously_valid_from,
            'wireman_details' => $parent->wireman_details,
            'form_name' => $parent->form_name,
            'form_id' => $parent->form_id,
            'license_name' => $parent->license_name,
            'aadhaar' => $parent->aadhaar,
            'pancard' => $parent->pancard,
            'aadhaar_doc' => $parent->aadhaar_doc,
            'pan_doc' => $parent->pan_doc,
            'certificate_no' => $parent->certificate_no,
            'certificate_valid_to' => $parent->certificate_valid_to,
            'certificate_issue_date' => $parent->certificate_issue_date,
            'certificate_valid_from' => $parent->certificate_valid_from,
            'cert_verify' => $parent->cert_verify,
            'license_verify' => $parent->license_verify,
            'license_number' => $parent->license_number,
            'appl_type' => 'A',
            'old_application' => $parent->application_id,
            'status' => 'P',
            'payment_status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function storeAlterationProof(Mst_Form_s_w $child, UploadedFile $file, string $documentType): void
    {
        app(FormSProofDocumentService::class)->saveAlterationProofUpload($child, $file, $documentType);
    }

    protected function alterationHasProof(
        Request $request,
        Mst_Form_s_w $parent,
        string $proofName,
        string $uploadField
    ): bool {
        if ($request->hasFile($uploadField)) {
            return true;
        }

        $draft = Mst_Form_s_w::where('old_application', $parent->application_id)
            ->where('appl_type', FormSProofDocumentService::ALTERATION_APP_TYPE)
            ->where('login_id', $parent->login_id)
            ->where('payment_status', 'draft')
            ->latest('id')
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

    protected function storeNewExperienceRows(Request $request, Mst_Form_s_w $child, string $loginId): void
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

            $experience = Mst_experience::create([
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
