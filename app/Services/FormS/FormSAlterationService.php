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

    /** Marker stored on copied parent experience rows (not a newly added alteration row). */
    public const ALT_SRC_EXP_PREFIX = '__ALT_SRC_EXP__:';

    public function __construct(
        protected FormSApplicationWorkflowService $workflowService,
        protected FormSDocumentUploadHandler $documentHandler,
        protected FormSDocumentVersionService $documentVersionService,
        protected FormSChildDocumentSnapshotService $childDocumentSnapshot
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
     * Issued certificates/licences for the logged-in applicant (alteration launcher dropdown).
     *
     * @return list<array{
     *     application_id: string,
     *     certificate_no: string,
     *     date_of_issue: string|null,
     *     valid_from: string|null,
     *     valid_to: string|null,
     *     appl_type: string,
     *     label: string
     * }>
     */
    public function listIssuedCertificatesForLogin(string $loginId, string $formCode = 'S'): array
    {
        $loginId = trim($loginId);
        if ($loginId === '') {
            return [];
        }

        $formCode = strtoupper(trim($formCode));
        $certForm = match ($formCode) {
            'H' => 'WH',
            default => $formCode,
        };

        $certService = app(CompetencyCertificateService::class);
        $metaService = app(CompetencyMetaService::class);
        $certTable = $certService->certTableForForm($certForm);        
        $metaTable = $metaService->tableForForm($certForm);
        if (! $certTable || ! $metaTable) {
            return [];
        }

        $paidStatuses = ['payment', 'paid', 'Y', 'y'];
        $rows = DB::table($certTable.' as c')
            ->join($metaTable.' as m', 'm.application_id', '=', 'c.application_id')
            ->where('m.login_id', $loginId)
            ->whereIn('m.appl_type', ['N', 'R', 'D'])
            ->where(function ($q) use ($paidStatuses) {
                $q->whereIn('m.payment_status', $paidStatuses)
                    ->orWhereRaw("LOWER(TRIM(COALESCE(m.payment_status, ''))) IN ('y','payment','paid')");
            })
            ->whereNotNull('c.certificate_no')
            ->where('c.certificate_no', '!=', '')
            ->orderByDesc('c.valid_to')
            ->orderByDesc('c.dateof_issue')
            ->select(
                'c.application_id',
                'c.certificate_no',
                'c.dateof_issue',
                'c.valid_from',
                'c.valid_to',
                'm.appl_type'
            )
            ->get();

        $fmt = static function ($value): ?string {
            if ($value === null || $value === '') {
                return null;
            }
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        };

        $fmtDisplay = static function (?string $iso): string {
            if ($iso === null || $iso === '') {
                return '—';
            }
            try {
                return Carbon::parse($iso)->format('d-m-Y');
            } catch (\Throwable $e) {
                return $iso;
            }
        };

        $applLabels = ['N' => 'New', 'R' => 'Renewal', 'D' => 'Digitization'];

        return $rows->map(function ($row) use ($fmt, $fmtDisplay, $applLabels) {
            $issue = $fmt($row->dateof_issue ?? null);
            $from = $fmt($row->valid_from ?? null);
            $to = $fmt($row->valid_to ?? null);
            $certNo = trim((string) ($row->certificate_no ?? ''));
            $appl = strtoupper(trim((string) ($row->appl_type ?? '')));
            $applTxt = $applLabels[$appl] ?? $appl;

            return [
                'application_id' => (string) ($row->application_id ?? ''),
                'certificate_no' => $certNo,
                'date_of_issue' => $issue,
                'valid_from' => $from,
                'valid_to' => $to,
                'appl_type' => $appl,
                'label' => trim(
                    $certNo
                    .' ('.$applTxt.')'
                    .' — Issue '.$fmtDisplay($issue)
                    .' | From '.$fmtDisplay($from)
                    .' | To '.$fmtDisplay($to)
                ),
            ];
        })->values()->all();
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
            $address = trim((string) ($draft->applicant_address ?? $draft->applicant_address ?? ''));
            $applicationDetails->applicant_address = $address;
            $applicationDetails->applicant_address = $address;
        }

        $applicationDetails = $this->enrichApplicationProofFields(
            $applicationDetails,
            $context['proofApplicationId'] ?? $masterApplicationId
        );

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
        $address = trim((string) ($normalized->applicant_address ?? $normalized->applicant_address ?? ''));
        $normalized->applicant_address = $address;
        $normalized->applicant_address = $address;

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
        $row->applicant_address = $row->applicant_address ?? $row->applicant_address ?? null;
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

        $alterationDraft = CC_Forms_Meta::where('old_application', $parent->application_id)
            ->where('appl_type', 'A')
            ->where('login_id', $parent->login_id)
            ->where('payment_status', 'draft')
            ->latest('app_id')
            ->first();

        $eduOwnerId = $masterId;
        $expOwnerId = $masterId;
        $proofOwnerId = $masterId;
        if ($alterationDraft) {
            $eduOwnerId = $this->childDocumentSnapshot->preferredEducationApplicationId($alterationDraft);
            $expOwnerId = $this->childDocumentSnapshot->preferredExperienceApplicationId($alterationDraft);
            $proofOwnerId = $this->childDocumentSnapshot->preferredIdentityProofApplicationId($alterationDraft);
        }

        $eduDetails = CC_Education::where('application_id', $eduOwnerId)
            ->orderByDesc('year_of_passing')
            ->get()
            ->map(function (CC_Education $edu) {
                $row = (object) $edu->toArray();
                $row->id = $edu->edu_id;

                return $row;
            });

        $expDetails = CC_Experience::where('application_id', $expOwnerId)
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

        $photoSource = $alterationDraft ?: $parent;
        $applicantPhoto = $this->resolveApplicantPhoto($photoSource);
        $proofDoc = $this->resolveApplicantSign($photoSource);

        return [
            'eduDetails' => $eduDetails,
            'expDetails' => $expDetails,
            'licenseDetails' => $licenseDetails,
            'alterationDraft' => $alterationDraft,
            'applicantPhoto' => $applicantPhoto,
            'proofDoc' => $proofDoc,
            'proofApplicationId' => $proofOwnerId,
        ];
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
        $parentAddress = trim((string) ($parent->applicant_address ?? $parent->applicant_address ?? ''));
        $newName = trim((string) $request->input('applicant_name', ''));
        $newAddress = trim((string) $request->input('applicant_address', $request->input('applicant_address', '')));

        $alterName = $request->input('alter_name') === '1' || ($newName !== '' && $newName !== $parentName);
        $alterAddress = $request->input('alter_address') === '1' || ($newAddress !== '' && $newAddress !== $parentAddress);
        $alterWork = $this->requestHasWorkAlteration($request);

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

            $this->snapshotUnchangedParentDocumentsOntoChild($child, $loginId);

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
                $this->assertFormSExperienceDateSequence($request);
                $this->assertFormSCountableExperienceMinimum($parent, $request);
                CC_Experience::where('application_id', $child->application_id)->delete();
                $this->storeWorkExperienceAlterationRows($request, $child, $loginId, $parent);
            } else {
                CC_Experience::where('application_id', $child->application_id)->delete();
                $this->childDocumentSnapshot->copyParentExperienceToChild($child, $loginId);
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
        $parentAddress = (string) ($parent->applicant_address ?? $parent->applicant_address ?? '');
        $newName = trim((string) $request->input('applicant_name', $parent->applicant_name));
        $newAddress = trim((string) $request->input('applicant_address', $request->input('applicant_address', $parentAddress)));

        return DB::transaction(function () use ($request, $parent, $loginId, $newName, $newAddress, $parentAddress) {
            $child = $this->findOrCreateAlterationDraftChild($parent, $loginId);

            $child->update([
                'applicant_name' => $newName !== '' ? $newName : $parent->applicant_name,
                'applicant_address' => $newAddress !== '' ? $newAddress : $parentAddress,
                'payment_status' => 'draft',
                'app_status' => 'P',
                'updated_at' => now(),
            ]);

            $this->snapshotUnchangedParentDocumentsOntoChild($child, $loginId);

            if ($request->hasFile('name_alteration_proof')) {
                $this->storeAlterationProof($child, $request->file('name_alteration_proof'), 'name_proof');
            }

            if ($request->hasFile('address_alteration_proof')) {
                $this->storeAlterationProof($child, $request->file('address_alteration_proof'), 'address_proof');
            }

            if ($this->requestHasWorkAlteration($request)) {
                CC_Experience::where('application_id', $child->application_id)->delete();
                try {
                    $this->storeWorkExperienceAlterationRows($request, $child, $loginId, $parent);
                } catch (RuntimeException $e) {
                    // Allow partial work rows on draft save.
                }
            } elseif (! CC_Experience::where('application_id', $child->application_id)->exists()) {
                $this->childDocumentSnapshot->copyParentExperienceToChild($child, $loginId);
            }

            return $child->fresh();
        });
    }

    /**
     * Copy parent education and identity proofs onto the alteration application.
     * Unchanged files keep the parent path; parent rows are not updated.
     */
    protected function snapshotUnchangedParentDocumentsOntoChild(CC_CompetencyMeta $child, string $loginId): void
    {
        CC_Education::where('application_id', $child->application_id)->delete();
        $this->childDocumentSnapshot->copyParentEducationToChild($child, $loginId);
        $this->childDocumentSnapshot->copyParentIdentityProofsToChild($child);
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
        $parentAddress = (string) ($parent->applicant_address ?? $parent->applicant_address ?? '');

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

    protected function requestHasWorkAlteration(Request $request): bool
    {
        return $this->collectNewWorkRowIndexes($request) !== []
            || $this->collectChangedExistingWorkRowIndexes($request) !== [];
    }

    /**
     * @return list<int|string>
     */
    protected function collectNewWorkRowIndexes(Request $request): array
    {
        $workIds = (array) $request->input('work_id', []);
        $existingFlags = (array) $request->input('fs_alt_existing_work', []);
        $employers = $this->requestEmployers($request);
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

    /**
     * @return list<int|string>
     */
    protected function collectExistingWorkRowIndexes(Request $request): array
    {
        $workIds = (array) $request->input('work_id', []);
        $existingFlags = (array) $request->input('fs_alt_existing_work', []);
        $employers = $this->requestEmployers($request);
        $designations = (array) $request->input('designation', []);
        $indexes = [];

        foreach (array_keys($employers) as $key) {
            if (! $this->isExistingWorkRowIndex($workIds, $existingFlags, $key)) {
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

    /**
     * Existing rows that differ from master (or include a new document upload).
     *
     * @return list<int|string>
     */
    protected function collectChangedExistingWorkRowIndexes(Request $request): array
    {
        $changed = [];
        foreach ($this->collectExistingWorkRowIndexes($request) as $key) {
            if ($this->existingWorkRowChangedFromMaster($request, $key)) {
                $changed[] = $key;
            }
        }

        return $changed;
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function requestEmployers(Request $request): array
    {
        $employers = (array) $request->input('work_employer_name', []);
        if ($employers === []) {
            $employers = (array) $request->input('work_level', []);
        }

        return $employers;
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function requestOrgAddresses(Request $request): array
    {
        $addresses = (array) $request->input('work_org_address', []);
        if ($addresses === []) {
            $addresses = (array) $request->input('work_organisation_address', []);
        }

        return $addresses;
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function requestNatures(Request $request): array
    {
        $natures = (array) $request->input('work_nature', []);
        if ($natures === []) {
            $natures = (array) $request->input('work_nature_of_work', []);
        }

        return $natures;
    }

    /**
     * @return array<int|string, mixed>
     */
    protected function requestVoltages(Request $request): array
    {
        $voltages = (array) $request->input('work_voltage_level', []);
        if ($voltages === []) {
            $voltages = (array) $request->input('work_voltage', []);
        }

        return $voltages;
    }

    protected function isExistingWorkRowIndex(array $workIds, array $existingFlags, int|string $key): bool
    {
        if (! empty($workIds[$key])) {
            return true;
        }

        return ! empty($existingFlags[$key]) && (string) $existingFlags[$key] === '1';
    }

    protected function existingWorkRowChangedFromMaster(Request $request, int|string $key): bool
    {
        $workIds = (array) $request->input('work_id', []);
        $expId = (int) ($workIds[$key] ?? 0);
        if ($expId <= 0) {
            return true;
        }

        $master = CC_Experience::find($expId);
        if (! $master) {
            return true;
        }

        if ($this->requestHasWorkDocumentUpload($request, $key)) {
            return true;
        }

        $employers = $this->requestEmployers($request);
        $designations = (array) $request->input('designation', []);
        $empTypes = (array) $request->input('work_employment_type', []);
        $orgAddresses = $this->requestOrgAddresses($request);
        $fromDates = (array) $request->input('work_date_from', []);
        $toDates = (array) $request->input('work_date_to', []);
        $natures = $this->requestNatures($request);
        $voltages = $this->requestVoltages($request);
        $kvas = (array) $request->input('work_transformer_kva', []);

        $norm = static function ($value): string {
            return trim((string) ($value ?? ''));
        };
        $normDate = static function ($value): string {
            $raw = trim((string) ($value ?? ''));
            if ($raw === '') {
                return '';
            }
            try {
                return Carbon::parse($raw)->toDateString();
            } catch (\Throwable $e) {
                return $raw;
            }
        };

        $masterFrom = $master->from_date ? Carbon::parse($master->from_date)->toDateString() : '';
        $masterTo = $master->to_date ? Carbon::parse($master->to_date)->toDateString() : '';

        return $norm($employers[$key] ?? '') !== $norm($master->org_name)
            || $norm($designations[$key] ?? '') !== $norm($master->designation)
            || $norm($empTypes[$key] ?? '') !== $norm($master->emp_type)
            || $norm($orgAddresses[$key] ?? '') !== $norm($master->org_address)
            || $normDate($fromDates[$key] ?? '') !== $masterFrom
            || $normDate($toDates[$key] ?? '') !== $masterTo
            || $norm($natures[$key] ?? '') !== $norm($master->nature_work)
            || $norm($voltages[$key] ?? '') !== $norm($master->voltage_level)
            || $norm($kvas[$key] ?? '') !== $norm($master->transformer_kva);
    }

    protected function requestHasWorkDocumentUpload(Request $request, int|string $key): bool
    {
        foreach (['work_document', 'work_relieving_letter'] as $field) {
            $file = $request->file($field);
            if (is_array($file) && isset($file[$key]) && $file[$key] instanceof UploadedFile && $file[$key]->isValid()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Form S §7a — consecutive experience periods in the posted previous rows must not overlap.
     * Each subsequent From date must be strictly after the previous row's To date.
     */
    protected function assertFormSExperienceDateSequence(Request $request): void
    {
        $fromDates = (array) $request->input('work_date_from', []);
        $toDates = (array) $request->input('work_date_to', []);
        $tillFlags = (array) $request->input('work_to_till_date', []);
        $sections = (array) $request->input('work_exp_section', []);
        $today = Carbon::now()->startOfDay();
        $periods = [];

        foreach (array_keys($fromDates) as $key) {
            if (strtolower(trim((string) ($sections[$key] ?? ''))) === 'current') {
                continue;
            }

            $fromRaw = trim((string) ($fromDates[$key] ?? ''));
            $toRaw = trim((string) ($toDates[$key] ?? ''));
            $tillRaw = $tillFlags[$key] ?? '0';
            $isTill = FormSWorkTillDate::isChecked($tillRaw);
            if ($fromRaw === '') {
                continue;
            }
            if ($toRaw === '' && ! $isTill) {
                continue;
            }

            try {
                $from = Carbon::parse($fromRaw)->startOfDay();
                $toEff = $toRaw !== ''
                    ? $toRaw
                    : (FormSWorkTillDate::toDateString($tillRaw, $today->toDateString()) ?? $today->toDateString());
                $to = Carbon::parse($toEff)->startOfDay();
            } catch (\Throwable $e) {
                continue;
            }

            if ($to->lt($from)) {
                continue;
            }

            $periods[] = [
                'from' => $from,
                'to' => $to,
            ];
        }

        for ($i = 1, $n = count($periods); $i < $n; $i++) {
            $prev = $periods[$i - 1];
            $curr = $periods[$i];
            if ($curr['from']->lte($prev['to'])) {
                throw new RuntimeException(
                    'From date must be after the previous row\'s To date ('.$prev['to']->format('d-M-Y').'). Experience periods must not overlap.'
                );
            }
        }
    }

    /**
     * Combined experience total for alteration: parent (master) rows + request overrides + new rows.
     * Voltage "Up to 650V" does not count; overall countable total must be ≥ 730 inclusive days.
     */
    protected function assertFormSCountableExperienceMinimum(CC_CompetencyMeta $parent, Request $request): void
    {
        $totalDays = 0;
        $anyCountable = false;
        $anyDatedExcluded650v = false;
        $today = Carbon::now()->startOfDay();

        $masterId = $this->workflowService->masterApplication($parent)->application_id;
        $existing = CC_Experience::where('application_id', $masterId)->orderBy('exp_id')->get();

        $workIds = (array) $request->input('work_id', []);
        $fromDates = (array) $request->input('work_date_from', []);
        $toDates = (array) $request->input('work_date_to', []);
        $tillFlags = (array) $request->input('work_to_till_date', []);
        $sections = (array) $request->input('work_exp_section', []);
        $voltages = $this->requestVoltages($request);
        $existingIndexes = $this->collectExistingWorkRowIndexes($request);
        $overrideByExpId = [];
        foreach ($existingIndexes as $key) {
            $expId = (int) ($workIds[$key] ?? 0);
            if ($expId > 0) {
                $overrideByExpId[$expId] = $key;
            }
        }

        foreach ($existing as $exp) {
            $expId = (int) ($exp->exp_id ?? 0);
            if (isset($overrideByExpId[$expId])) {
                $key = $overrideByExpId[$expId];
                if (strtolower(trim((string) ($sections[$key] ?? ''))) === 'current') {
                    continue;
                }
                $fromRaw = trim((string) ($fromDates[$key] ?? ''));
                $toRaw = trim((string) ($toDates[$key] ?? ''));
                $voltage = strtolower(trim((string) ($voltages[$key] ?? '')));
            } else {
                $fromRaw = trim((string) ($exp->from_date ?? ''));
                $toRaw = trim((string) ($exp->to_date ?? ''));
                $voltage = strtolower(trim((string) ($exp->voltage_level ?? '')));
            }

            if ($fromRaw === '') {
                continue;
            }

            try {
                $from = Carbon::parse($fromRaw)->startOfDay();
                $to = $toRaw !== ''
                    ? Carbon::parse($toRaw)->startOfDay()
                    : $today;
            } catch (\Throwable $e) {
                continue;
            }

            if ($to->lt($from)) {
                continue;
            }

            if ($voltage === 'up_to_650v') {
                $anyDatedExcluded650v = true;
                continue;
            }

            $anyCountable = true;
            $totalDays += $from->diffInDays($to) + 1;
        }

        $indexes = $this->collectNewWorkRowIndexes($request);

        foreach ($indexes as $key) {
            if (strtolower(trim((string) ($sections[$key] ?? ''))) === 'current') {
                continue;
            }

            $fromRaw = trim((string) ($fromDates[$key] ?? ''));
            $toRaw = trim((string) ($toDates[$key] ?? ''));
            $tillRaw = $tillFlags[$key] ?? '0';
            $isTill = FormSWorkTillDate::isChecked($tillRaw);
            if ($fromRaw === '') {
                continue;
            }
            if ($toRaw === '' && ! $isTill) {
                continue;
            }

            try {
                $from = Carbon::parse($fromRaw)->startOfDay();
                $toEff = $toRaw !== ''
                    ? $toRaw
                    : (FormSWorkTillDate::toDateString($tillRaw, $today->toDateString()) ?? $today->toDateString());
                $to = Carbon::parse($toEff)->startOfDay();
            } catch (\Throwable $e) {
                continue;
            }

            if ($to->lt($from)) {
                continue;
            }

            $voltage = strtolower(trim((string) ($voltages[$key] ?? '')));
            if ($voltage === 'up_to_650v') {
                $anyDatedExcluded650v = true;
                continue;
            }

            $anyCountable = true;
            $totalDays += $from->diffInDays($to) + 1;
        }

        $needsTwoYears = $anyCountable || $anyDatedExcluded650v;
        if ($needsTwoYears && $totalDays < 730) {
            throw new RuntimeException(
                (! $anyCountable && $anyDatedExcluded650v)
                    ? 'Experience with Voltage Level "Up to 650V" is not counted. Add experience above 650V totaling at least 2 years.'
                    : 'Minimum 2 Years Experience needed across all entries (Voltage up to 650V is not counted).'
            );
        }
    }

    /**
     * Persist a full experience snapshot on the alteration application:
     * copied parent rows (path-only) plus newly added rows (upload only if a new file was chosen).
     */
    protected function storeWorkExperienceAlterationRows(
        Request $request,
        CC_CompetencyMeta $child,
        string $loginId,
        CC_CompetencyMeta $parent
    ): void {
        $newIndexes = $this->collectNewWorkRowIndexes($request);
        $existingIndexes = $this->collectExistingWorkRowIndexes($request);
        $editedIndexes = $this->collectChangedExistingWorkRowIndexes($request);

        if ($newIndexes === [] && $editedIndexes === []) {
            throw new RuntimeException('Edit an existing work experience entry or add a new one.');
        }

        $created = 0;
        $copiedSourceIds = [];
        $masterId = (string) $this->workflowService->masterApplication($parent)->application_id;

        foreach ($existingIndexes as $key) {
            if ($this->createAlterationExperienceRow($request, $child, $loginId, $key, true)) {
                $created++;
                $postedId = (int) (($request->input('work_id', [])[$key] ?? 0));
                $found = $postedId > 0 ? CC_Experience::find($postedId) : null;
                $parentExp = $this->childDocumentSnapshot->resolveParentExperienceFromPostedId($found, $masterId);
                if ($parentExp) {
                    $copiedSourceIds[(int) $parentExp->exp_id] = true;
                }
            }
        }

        $parentRows = CC_Experience::where('application_id', $masterId)->orderBy('exp_id')->get();
        foreach ($parentRows as $parentExp) {
            $parentExpId = (int) ($parentExp->exp_id ?? 0);
            if ($parentExpId <= 0 || isset($copiedSourceIds[$parentExpId])) {
                continue;
            }
            if ($this->copyParentExperienceRowToChild($child, $loginId, $parentExp)) {
                $created++;
                $copiedSourceIds[$parentExpId] = true;
            }
        }

        foreach ($newIndexes as $key) {
            if ($this->createAlterationExperienceRow($request, $child, $loginId, $key, false)) {
                $created++;
            }
        }

        if ($created === 0) {
            throw new RuntimeException('Edit an existing work experience entry or add a new one.');
        }
    }

    /** @deprecated Use storeWorkExperienceAlterationRows */
    protected function storeNewExperienceRows(Request $request, CC_CompetencyMeta $child, string $loginId): void
    {
        $parent = CC_Forms_Meta::findByApplicationId((string) ($child->old_application ?? ''));
        if (! $parent) {
            throw new RuntimeException('Parent application not found for alteration.');
        }
        $this->storeWorkExperienceAlterationRows($request, $child, $loginId, $parent);
    }

    protected function copyParentExperienceRowToChild(
        CC_CompetencyMeta $child,
        string $loginId,
        CC_Experience $parentExp
    ): bool {
        $orgName = trim((string) ($parentExp->org_name ?? ''));
        $designation = trim((string) ($parentExp->designation ?? ''));
        if ($orgName === '' || $designation === '') {
            return false;
        }

        CC_Experience::create([
            'login_id' => $loginId,
            'application_id' => $child->application_id,
            'emp_type' => $parentExp->emp_type,
            'emp_cate' => $parentExp->emp_cate,
            'org_name' => $orgName,
            'org_address' => $parentExp->org_address,
            'designation' => $designation,
            'from_date' => $parentExp->from_date,
            'to_date' => $parentExp->to_date,
            'total_y' => $parentExp->total_y,
            'total_m' => $parentExp->total_m,
            'total_d' => $parentExp->total_d,
            'total_exp' => $parentExp->total_exp,
            'nature_work' => $parentExp->nature_work,
            'voltage_level' => $parentExp->voltage_level,
            'transformer_kva' => $parentExp->transformer_kva,
            'board_meeting_details' => $parentExp->board_meeting_details,
            'board_meeting_date' => $parentExp->board_meeting_date,
            'support_document' => $parentExp->support_document,
            'relieve_document' => $parentExp->relieve_document ?? $parentExp->releive_document,
        ]);

        return true;
    }

    protected function createAlterationExperienceRow(
        Request $request,
        CC_CompetencyMeta $child,
        string $loginId,
        int|string $key,
        bool $isExistingRow
    ): bool {
        $employers = $this->requestEmployers($request);
        $designations = (array) $request->input('designation', []);
        $orgName = trim((string) ($employers[$key] ?? ''));
        $designation = trim((string) ($designations[$key] ?? ''));
        if ($orgName === '' || $designation === '') {
            return false;
        }

        $empTypes = (array) $request->input('work_employment_type', []);
        $orgAddresses = $this->requestOrgAddresses($request);
        $fromDates = (array) $request->input('work_date_from', []);
        $toDates = (array) $request->input('work_date_to', []);
        $tillFlags = (array) $request->input('work_to_till_date', []);
        $durY = (array) $request->input('work_duration_y', []);
        $durM = (array) $request->input('work_duration_m', []);
        $durD = (array) $request->input('work_duration_d', []);
        $totals = (array) $request->input('work_experience_total', []);
        $natures = $this->requestNatures($request);
        $voltages = $this->requestVoltages($request);
        $kvas = (array) $request->input('work_transformer_kva', []);
        $workIds = (array) $request->input('work_id', []);
        $meetingDetails = (array) $request->input('work_board_meeting_details', []);
        $meetingDates = (array) $request->input('work_board_meeting_date', []);
        $contractorCats = (array) $request->input('work_contractor_category', []);
        $licenceNos = (array) $request->input('work_licence_number', []);

        $empCate = null;
        $cat = trim((string) ($contractorCats[$key] ?? ''));
        $licence = trim((string) ($licenceNos[$key] ?? ''));
        if ($cat !== '' || $licence !== '') {
            $empCate = $cat . ($licence !== '' ? '||' . $licence : '');
        }

        $postedExpId = $isExistingRow ? (int) ($workIds[$key] ?? 0) : 0;
        $postedExp = $postedExpId > 0 ? CC_Experience::find($postedExpId) : null;
        $parentId = (string) $this->workflowService->masterApplication($child)->application_id;
        $master = $this->childDocumentSnapshot->resolveParentExperienceFromPostedId($postedExp, $parentId);
        $boardDetails = trim((string) ($meetingDetails[$key] ?? ''));
        $totalY = (int) ($durY[$key] ?? 0);
        $totalM = (int) ($durM[$key] ?? 0);
        $totalD = (int) ($durD[$key] ?? 0);
        $totalExp = trim((string) ($totals[$key] ?? ''));
        if ($totalExp === '' && $master && $master->total_exp !== null && $master->total_exp !== '') {
            $totalExp = (string) $master->total_exp;
        }

        $toDate = trim((string) ($toDates[$key] ?? ''));
        $tillDate = FormSWorkTillDate::toDateString($tillFlags[$key] ?? '0', Carbon::today()->toDateString());
        if ($tillDate !== null) {
            $toDate = $tillDate;
        }

        $experience = CC_Experience::create([
            'login_id' => $loginId,
            'application_id' => $child->application_id,
            'emp_type' => $empTypes[$key] ?? null,
            'emp_cate' => $empCate,
            'org_name' => $orgName,
            'org_address' => $orgAddresses[$key] ?? null,
            'designation' => $designation,
            'from_date' => $fromDates[$key] ?? null,
            'to_date' => ($toDate !== '' ? $toDate : null),
            'total_y' => $totalY,
            'total_m' => $totalM,
            'total_d' => $totalD,
            'total_exp' => ($totalExp !== '' ? $totalExp : null),
            'nature_work' => $natures[$key] ?? null,
            'voltage_level' => $voltages[$key] ?? null,
            'transformer_kva' => $kvas[$key] ?? null,
            'board_meeting_details' => $boardDetails !== '' ? $boardDetails : null,
            'board_meeting_date' => $meetingDates[$key] ?? null,
            'support_document' => $master?->support_document,
            'relieve_document' => $master?->relieve_document ?? $master?->releive_document,
        ]);

        $supportFile = $this->uploadedFileAt($request, 'work_document', $key);
        if ($supportFile) {
            $path = $this->documentHandler->handleExperienceSupportUpload(
                $child,
                $experience,
                $supportFile
            );
            if ($path) {
                $experience->update(['support_document' => $path]);
            }
        }

        $relieveFile = $this->uploadedFileAt($request, 'work_relieving_letter', $key);
        if ($relieveFile) {
            $relievePath = $this->documentHandler->handleExperienceRelieveUpload(
                $child,
                $experience,
                $relieveFile
            );
            if ($relievePath) {
                $experience->update(['relieve_document' => $relievePath]);
            }
        }

        return true;
    }

    protected function uploadedFileAt(Request $request, string $field, int|string $key): ?UploadedFile
    {
        $file = $request->file($field);
        if (is_array($file) && isset($file[$key])) {
            $file = $file[$key];
        } elseif (! $file instanceof UploadedFile) {
            $file = null;
        }

        return ($file instanceof UploadedFile && $file->isValid()) ? $file : null;
    }

    /**
     * @return array{0: int|null, 1: string|null}
     */
    protected function decodeAltSourceExpId(?string $boardDetails): array
    {
        $raw = trim((string) $boardDetails);
        if ($raw === '' || ! str_starts_with($raw, self::ALT_SRC_EXP_PREFIX)) {
            return [null, $boardDetails];
        }

        $rest = substr($raw, strlen(self::ALT_SRC_EXP_PREFIX));
        $parts = preg_split("/\r\n|\n|\r/", $rest, 2) ?: [];
        $idPart = trim((string) ($parts[0] ?? ''));
        $details = isset($parts[1]) ? trim((string) $parts[1]) : '';
        $expId = ctype_digit($idPart) ? (int) $idPart : null;

        return [$expId, $details !== '' ? $details : null];
    }
    /**
     * Apply approved alteration name/address to the issued certificate application
     * and registration profile. Experience is not copied back — the alteration
     * application already holds the full snapshot from submit.
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

        $childAddress = trim((string) ($childRow->applicant_address ?? $childRow->applicant_address ?? ''));
        $parentAddress = trim((string) ($parentRow->applicant_address ?? $parentRow->applicant_address ?? ''));
        if ($childAddress !== '' && $childAddress !== $parentAddress) {
            $parentUpdates['applicant_address'] = $childAddress;
            $parentUpdates['applicant_address'] = $childAddress;
        }

        if (count($parentUpdates) > 1) {
            DB::table($parentTable)->where('application_id', $parentId)->update($parentUpdates);
        }

        $this->syncLegacyApplicationProfile($parentId, $parentUpdates);
        $this->syncRegistrationProfile((string) ($childRow->login_id ?? ''), $childRow);
        // Experience/education/proofs stay on the alteration application_id (full snapshot at submit).

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
        if (! DB::getSchemaBuilder()->hasTable('cc_form_s_meta')) {
            return;
        }

        $legacyUpdate = array_intersect_key(
            $parentUpdates,
            array_flip(['applicant_name', 'applicant_address', 'applicants_address', 'updated_at'])
        );

        if (count($legacyUpdate) <= 1) {
            return;
        }

        DB::table('cc_form_s_meta')
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

        $address = trim((string) ($childRow->applicant_address ?? $childRow->applicant_address ?? ''));
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

    /**
     * @deprecated Experience is stored on the alteration application at submit and is not merged back.
     */
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
            [$sourceExpId, $boardDetails] = $this->decodeAltSourceExpId($row->board_meeting_details ?? null);

            $payload = [
                'login_id' => $row->login_id ?: ($childRow->login_id ?? null),
                'emp_type' => $row->emp_type,
                'emp_cate' => $row->emp_cate,
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
                'board_meeting_details' => $boardDetails,
                'board_meeting_date' => $row->board_meeting_date,
            ];

            if ($sourceExpId) {
                $masterRow = CC_Experience::where('application_id', $masterApplicationId)
                    ->where('exp_id', $sourceExpId)
                    ->first();
                if ($masterRow) {
                    $masterRow->update($payload);
                    continue;
                }
            }

            CC_Experience::create(array_merge($payload, [
                'application_id' => $masterApplicationId,
            ]));
        }
    }
}
