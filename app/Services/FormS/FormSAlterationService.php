<?php

namespace App\Services\FormS;

use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Forms_Meta;
use App\Models\Competency\CC_CompetencyMeta;
use App\Services\Competency\CompetencyDocumentReviewService;
use App\Models\Mst_experience;
use App\Services\Competency\CompetencyCertificateService;
use App\Services\Competency\CompetencyMetaService;
use App\Models\CC_Proof_doc;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use RuntimeException;

class FormSAlterationService
{
    private const FORM_S_CERT_TABLE = 'cc_forms_cert';

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

    /**
     * @param  array{
     *     certificate_no: string,
     *     date_of_issue: mixed,
     *     valid_from: mixed,
     *     valid_to: mixed,
     * }  $certificateDetails
     * @return array{ok: bool, message?: string, application?: CC_CompetencyMeta, certificate_not_found?: bool}
     */
    public function verifyLauncherRequest(string $loginId, string $formName, array $certificateDetails): array
    {
        $certificateNo = trim((string) ($certificateDetails['certificate_no'] ?? ''));
        $dateOfIssue = $certificateDetails['date_of_issue'] ?? null;
        $validFrom = $certificateDetails['valid_from'] ?? null;
        $validTo = $certificateDetails['valid_to'] ?? null;

        if ($certificateNo === '' || $dateOfIssue === null || $dateOfIssue === ''
            || $validFrom === null || $validFrom === ''
            || $validTo === null || $validTo === '') {
            return ['ok' => false, 'message' => 'Certificate number and validity dates are required.'];
        }

        $cert = app(CompetencyCertificateService::class)->findCertByDetailsInTable(
            self::FORM_S_CERT_TABLE,
            $certificateNo,
            $dateOfIssue,
            $validFrom,
            $validTo
        );

        if (!$cert) {
            return ['ok' => false, 'message' => 'Certificate Details Not Found.', 'certificate_not_found' => true];
        }

        $applicationId = trim((string) ($cert->application_id ?? ''));
        if ($applicationId === '') {
            return ['ok' => false, 'message' => 'Certificate Details Not Found.', 'certificate_not_found' => true];
        }

        $verify = $this->verifyParentApplication($applicationId, $loginId);
        if (!$verify['ok']) {
            return ['ok' => false, 'message' => 'Certificate Details Not Found.', 'certificate_not_found' => true];
        }

        return ['ok' => true, 'application' => $verify['application']];
    }

    public function hasAlterationDraftFor(string $parentApplicationId, string $loginId): bool
    {
        return CC_Forms_Meta::where('old_application', trim($parentApplicationId))
            ->where('appl_type', 'A')
            ->where('login_id', $loginId)
            ->where('payment_status', 'draft')
            ->exists();
    }

    public function markLauncherVerifiedForParent(string $formCode, string $applicationId): void
    {
        session([
            'competency_alt_verified' => [
                'form' => strtoupper(trim($formCode)),
                'application_id' => trim($applicationId),
                'verified_at' => time(),
            ],
        ]);
    }

    public function markLauncherVerified(string $formCode, string $applicationId, array $certificateDetails): void
    {
        session([
            'competency_alt_verified' => [
                'form' => strtoupper(trim($formCode)),
                'application_id' => trim($applicationId),
                'certificate_no' => trim((string) ($certificateDetails['certificate_no'] ?? '')),
                'date_of_issue' => $certificateDetails['date_of_issue'] ?? null,
                'valid_from' => $certificateDetails['valid_from'] ?? null,
                'valid_to' => $certificateDetails['valid_to'] ?? null,
                'verified_at' => time(),
            ],
        ]);
    }

    public function isLauncherVerifiedFor(string $formCode, string $applicationId): bool
    {
        $stored = session('competency_alt_verified');
        if (!is_array($stored)) {
            return false;
        }

        return strtoupper((string) ($stored['form'] ?? '')) === strtoupper(trim($formCode))
            && trim((string) ($stored['application_id'] ?? '')) === trim($applicationId);
    }

    /**
     * Build view payload for the alteration form after certificate verification.
     *
     * @return array<string, mixed>
     */
    public function buildAlterationFormViewData(CC_CompetencyMeta $parent, bool $editableMode = true): array
    {
        $parent = $this->normalizeParentForDisplay($parent);
        $context = $this->loadParentContext($parent);
        $masterApplicationId = (string) $this->workflowService->masterApplication($parent)->application_id;

        $applicationDetails = $this->normalizeApplicationMetaForFormDisplay(
            (object) $parent->toArray()
        );

        if ($context['alterationDraft']) {
            $draft = $this->normalizeParentForDisplay($context['alterationDraft']);
            $applicationDetails->applicant_name = $draft->applicant_name;
            $address = trim((string) ($draft->applicant_address ?? $draft->applicants_address ?? ''));
            $applicationDetails->applicant_address = $address;
            $applicationDetails->applicants_address = $address;
        }

        $applicationDetails = $this->enrichApplicationProofFields($applicationDetails, $masterApplicationId);

        return [
            'applicationid' => $parent->application_id,
            'parent_application_id' => $parent->application_id,
            'application_details' => $applicationDetails,
            'parent_application_details' => $parent,
            'edu_details' => $context['eduDetails'],
            'exp_details' => $context['expDetails'],
            'license_details' => $context['licenseDetails'],
            'applicant_photo' => $context['applicantPhoto'] ?? null,
            'proof_doc' => $context['proofDoc'] ?? null,
            'is_alteration_mode' => true,
            'alteration_editable_mode' => $editableMode,
            'alteration_draft' => $context['alterationDraft'],
            'fees_details' => null,
            'form_details' => [],
            'licence_name' => null,
            'queries' => collect(),
        ];
    }

    public function normalizeParentForDisplay(CC_CompetencyMeta $application): CC_CompetencyMeta
    {
        $normalized = clone $application;
        $address = trim((string) ($normalized->applicant_address ?? $normalized->applicants_address ?? ''));
        $normalized->applicant_address = $address;
        $normalized->applicants_address = $address;

        return $normalized;
    }

    /**
     * Map cc_form_*_meta columns to legacy form field names used by Form S blades.
     * Q8 = previous supervisor cert (previous_scc_*), Q9 = wireman cert (wcc_*).
     */
    public function normalizeApplicationMetaForFormDisplay(object $row): object
    {
        $row = clone $row;

        $row->license_name = $row->license_name ?? $row->certificate_name ?? null;
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
        $row->license_verify = isset($row->license_verify)
            ? (int) $row->license_verify
            : (! empty($row->previously_number) ? 1 : 0);
        $row->cert_verify = isset($row->cert_verify)
            ? (int) $row->cert_verify
            : (! empty($row->certificate_no) ? 1 : 0);

        return $row;
    }

    public function enrichApplicationWithCertificateDetails(object $application, ?object $licenseDetails): object
    {
        $application = clone $application;

        if (!$licenseDetails) {
            return $application;
        }

        // Issued Form S certificate details are kept separate from Q8/Q9 answers.
        $certificateNo = trim((string) ($licenseDetails->license_number ?? $licenseDetails->certificate_no ?? ''));
        if ($certificateNo !== '') {
            $application->license_number = $certificateNo;
        }

        $issueDate = $licenseDetails->issued_at ?? $licenseDetails->dateof_issue ?? null;
        if ($issueDate) {
            $application->issued_certificate_issue_date = $this->formatDateValue($issueDate);
        }

        $validFrom = $licenseDetails->valid_from ?? $licenseDetails->issued_from ?? null;
        if ($validFrom) {
            $application->issued_certificate_valid_from = $this->formatDateValue($validFrom);
        }

        $validTo = $licenseDetails->valid_to ?? $licenseDetails->expires_at ?? null;
        if ($validTo) {
            $application->issued_certificate_valid_to = $this->formatDateValue($validTo);
        }

        return $application;
    }

    protected function enrichApplicationProofFields(object $application, string $masterApplicationId): object
    {
        $application = clone $application;

        $proofRows = CC_Proof_doc::where('application_id', $masterApplicationId)
            ->whereIn('proof_type', ['aadhaar', 'pan'])
            ->get();

        foreach ($proofRows as $proof) {
            $proofType = strtolower((string) ($proof->proof_type ?? ''));
            if ($proofType === 'aadhaar') {
                if (! empty($proof->proof_no)) {
                    $application->aadhaar = $proof->proof_no;
                }
                if (! empty($proof->proof_doc)) {
                    $application->aadhaar_doc = $proof->proof_doc;
                }
            } elseif ($proofType === 'pan') {
                if (! empty($proof->proof_no)) {
                    $application->pancard = $proof->proof_no;
                }
                if (! empty($proof->proof_doc)) {
                    $application->pan_doc = $proof->proof_doc;
                    $application->pancard_doc = $proof->proof_doc;
                }
            }
        }

        return $application;
    }

    private function formatDateValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function loadParentContext(CC_CompetencyMeta $parent): array
    {
        $masterId = $this->workflowService->masterApplication($parent)->application_id;

        $eduDetails = CC_Education::where('application_id', $masterId)
            ->orderByDesc('year_of_passing')
            ->get()
            ->map(function (CC_Education $edu) {
                $row = (object) $edu->toArray();
                $row->id = $edu->edu_id;

                return $row;
            });

        $expDetails = CC_Experience::where('application_id', $masterId)
            ->orderBy('exp_id')
            ->get()
            ->map(function (CC_Experience $exp) {
                $row = (object) $exp->toArray();
                $row->id = $exp->exp_id;
                $row->releive_document = $exp->relieve_document ?? $exp->releive_document ?? null;

                return $row;
            });

        $licenseDetails = app(CompetencyCertificateService::class)->licenseDetailsFromCertTable(
            self::FORM_S_CERT_TABLE,
            (string) $parent->application_id
        );

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

    protected function resolveApplicantPhoto(CC_CompetencyMeta $parent): ?object
    {
        $proofService = app(FormSProofDocumentService::class);

        foreach ($this->mediaApplicationIds($parent) as $applicationId) {
            $photo = $proofService->loadPhotoForView($applicationId);
            if ($photo && trim((string) ($photo->upload_path ?? '')) !== '') {
                return $photo;
            }
        }

        return null;
    }

    protected function resolveApplicantSign(CC_CompetencyMeta $parent): ?object
    {
        $proofService = app(FormSProofDocumentService::class);

        foreach ($this->mediaApplicationIds($parent) as $applicationId) {
            $sign = $proofService->loadSignForView($applicationId);
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

        $alterName = $request->input('alter_name') === '1' || ($newName !== '' && $newName !== $parentName);
        $alterAddress = $request->input('alter_address') === '1' || ($newAddress !== '' && $newAddress !== $parentAddress);
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

            if ($alterName) {
                if ($request->hasFile('name_alteration_proof')) {
                    $this->storeAlterationProof($child, $request->file('name_alteration_proof'), 'name_proof');
                } else {
                    app(FormSProofDocumentService::class)->syncAlterationProofFromLog($child, 'name_proof');
                }
            }

            if ($alterAddress) {
                if ($request->hasFile('address_alteration_proof')) {
                    $this->storeAlterationProof($child, $request->file('address_alteration_proof'), 'address_proof');
                } else {
                    app(FormSProofDocumentService::class)->syncAlterationProofFromLog($child, 'address_proof');
                }
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

            $relieveFile = $request->file('work_relieving_letter');
            if (is_array($relieveFile) && isset($relieveFile[$key])) {
                $relieveFile = $relieveFile[$key];
            } elseif (! $relieveFile instanceof UploadedFile) {
                $relieveFile = null;
            }
            if ($relieveFile && $relieveFile->isValid()) {
                $relievePath = $this->documentHandler->handleExperienceRelieveUpload(
                    $child,
                    $experience,
                    $relieveFile
                );
                if ($relievePath) {
                    $experience->update(['relieve_document' => $relievePath]);
                }
            }
        }

        if ($created === 0) {
            throw new RuntimeException('Add at least one new work experience entry.');
        }
    }

    /**
     * Apply approved alteration changes to the parent application, registration profile,
     * and master work experience. Returns existing certificate details for PDF regeneration.
     *
     * @return array{
     *     parent_application_id: string,
     *     license_number: string,
     *     issued_at: mixed,
     *     expires_at: mixed
     * }
     */
    public function applyApprovedAlterationChanges(string $alterationApplicationId, string $alterationMetaTable): array
    {
        $alterationApplicationId = trim($alterationApplicationId);
        $childRow = DB::table($alterationMetaTable)
            ->where('application_id', $alterationApplicationId)
            ->first();

        if (! $childRow) {
            throw new RuntimeException('Alteration application not found.');
        }

        $parentId = trim((string) ($childRow->old_application ?? ''));
        if ($parentId === '') {
            throw new RuntimeException('Parent application not found for alteration.');
        }

        $metaService = app(CompetencyMetaService::class);
        $parentTable = $metaService->metaTableForApplicationId($parentId);
        if (! $parentTable) {
            throw new RuntimeException('Parent application meta table not found.');
        }

        $parentRow = DB::table($parentTable)->where('application_id', $parentId)->first();
        if (! $parentRow) {
            throw new RuntimeException('Parent application record not found.');
        }

        $parentUpdates = ['updated_at' => now()];

        $childName = trim((string) ($childRow->applicant_name ?? ''));
        $parentName = trim((string) ($parentRow->applicant_name ?? ''));
        if ($childName !== '' && $childName !== $parentName) {
            $parentUpdates['applicant_name'] = $childName;
        }

        $childAddress = trim((string) ($childRow->applicant_address ?? $childRow->applicants_address ?? ''));
        $parentAddress = trim((string) ($parentRow->applicant_address ?? $parentRow->applicants_address ?? ''));
        if ($childAddress !== '' && $childAddress !== $parentAddress) {
            $parentUpdates['applicant_address'] = $childAddress;
            $parentUpdates['applicants_address'] = $childAddress;
        }

        if (count($parentUpdates) > 1) {
            DB::table($parentTable)->where('application_id', $parentId)->update($parentUpdates);
        }

        $this->syncLegacyApplicationProfile($parentId, $parentUpdates);
        $this->syncRegistrationProfile((string) ($childRow->login_id ?? ''), $childRow);
        $this->mergeApprovedWorkExperienceToMaster($childRow, $parentId);

        $licenseDetails = app(CompetencyCertificateService::class)->asLicenseDetails(
            $parentId,
            $childRow->form_name ?? null
        );

        if (! $licenseDetails || trim((string) ($licenseDetails->license_number ?? '')) === '') {
            throw new RuntimeException('Issued certificate not found for parent application.');
        }

        return [
            'parent_application_id' => $parentId,
            'license_number' => (string) $licenseDetails->license_number,
            'issued_at' => $licenseDetails->issued_at,
            'expires_at' => $licenseDetails->expires_at,
        ];
    }

    protected function syncLegacyApplicationProfile(string $parentApplicationId, array $parentUpdates): void
    {
        if (! DB::getSchemaBuilder()->hasTable('tnelb_application_tbl')) {
            return;
        }

        $legacyUpdate = array_intersect_key(
            $parentUpdates,
            array_flip(['applicant_name', 'applicant_address', 'applicants_address', 'updated_at'])
        );

        if (count($legacyUpdate) <= 1) {
            return;
        }

        DB::table('tnelb_application_tbl')
            ->where('application_id', $parentApplicationId)
            ->update($legacyUpdate);
    }

    protected function syncRegistrationProfile(string $loginId, object $childRow): void
    {
        $loginId = trim($loginId);
        if ($loginId === '' || ! DB::getSchemaBuilder()->hasTable('tnelb_registers')) {
            return;
        }

        $register = DB::table('tnelb_registers')->where('login_id', $loginId)->first();
        if (! $register) {
            return;
        }

        $update = ['updated_at' => now()];

        $fullName = trim((string) ($childRow->applicant_name ?? ''));
        if ($fullName !== '') {
            [$salutation, $firstName, $lastName] = $this->parseApplicantNameForRegistration($fullName);
            if ($salutation !== null) {
                $update['salutation'] = $salutation;
            }
            if ($firstName !== '') {
                $update['first_name'] = $firstName;
            }
            if ($lastName !== '') {
                $update['last_name'] = $lastName;
            }
        }

        $address = trim((string) ($childRow->applicant_address ?? $childRow->applicants_address ?? ''));
        if ($address !== '') {
            $update['address'] = $address;
        }

        if (count($update) > 1) {
            DB::table('tnelb_registers')->where('login_id', $loginId)->update($update);
        }
    }

    /**
     * @return array{0: ?string, 1: string, 2: string}
     */
    protected function parseApplicantNameForRegistration(string $fullName): array
    {
        $salutations = ['Mr', 'Mrs', 'Ms', 'Dr'];
        $parts = preg_split('/\s+/', trim($fullName), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($parts === []) {
            return [null, '', ''];
        }

        $first = rtrim($parts[0], '.');
        if (in_array($first, $salutations, true)) {
            $salutation = $first;
            $nameParts = array_slice($parts, 1);
        } else {
            $salutation = null;
            $nameParts = $parts;
        }

        if ($nameParts === []) {
            return [$salutation, '', ''];
        }

        if (count($nameParts) === 1) {
            return [$salutation, $nameParts[0], ''];
        }

        $lastName = (string) array_pop($nameParts);

        return [$salutation, implode(' ', $nameParts), $lastName];
    }

    protected function mergeApprovedWorkExperienceToMaster(object $childRow, string $parentApplicationId): void
    {
        $childApplicationId = trim((string) ($childRow->application_id ?? ''));
        if ($childApplicationId === '') {
            return;
        }

        $parentMeta = CC_Forms_Meta::findByApplicationId($parentApplicationId);
        if (! $parentMeta) {
            return;
        }

        $masterApplicationId = (string) $this->workflowService->masterApplication($parentMeta)->application_id;
        $newRows = CC_Experience::where('application_id', $childApplicationId)->get();

        foreach ($newRows as $row) {
            CC_Experience::create([
                'login_id' => $row->login_id ?: ($childRow->login_id ?? null),
                'application_id' => $masterApplicationId,
                'emp_type' => $row->emp_type,
                'org_name' => $row->org_name,
                'org_address' => $row->org_address,
                'designation' => $row->designation,
                'from_date' => $row->from_date,
                'to_date' => $row->to_date,
                'total_y' => $row->total_y,
                'total_m' => $row->total_m,
                'total_d' => $row->total_d,
                'nature_work' => $row->nature_work,
                'voltage_level' => $row->voltage_level,
                'transformer_kva' => $row->transformer_kva,
                'support_document' => $row->support_document,
                'relieve_document' => $row->relieve_document ?? $row->releive_document ?? null,
            ]);
        }
    }
}
