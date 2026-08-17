<?php

namespace App\Services\FormS;

use App\Models\CC_Doc_Log;
use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Proof_doc;
use App\Models\Competency\CC_CompetencyMeta;
use App\Services\Competency\CompetencyDocumentSupport;
use Illuminate\Http\UploadedFile;

/**
 * Bridges competency form uploads to cc_doc_log + shared CC master tables.
 */
class FormSDocumentUploadHandler
{
    public function __construct(
        protected FormSDocumentVersionService $versionService,
        protected FormSApplicationWorkflowService $workflowService,
        protected FormSDocumentMasterTableService $masterTableService
    ) {}

    public function usesVersionedStorage(?string $formName): bool
    {
        return CompetencyDocumentSupport::usesVersionedStorage($formName);
    }

    /**
     * @return string|null Approved path for master upload_document (null if pending renewal)
     */
    public function handleEducationUpload(
        CC_CompetencyMeta $workflowApp,
        CC_Education $masterEducation,
        UploadedFile $file,
        ?string $replacementReason = null
    ): ?string {
        $log = $this->storeVersionedUpload(
            $workflowApp,
            $file,
            'education',
            'certificate',
            (int) $masterEducation->getKey(),
            $replacementReason,
            'Education document upload'
        );

        return $this->resolveMasterPathAfterUpload($log, $masterEducation);
    }

    /**
     * @return string|null Approved path for master support_document
     */
    public function handleExperienceSupportUpload(
        CC_CompetencyMeta $workflowApp,
        CC_Experience $masterExperience,
        UploadedFile $file,
        ?string $replacementReason = null
    ): ?string {
        $refId = (int) $masterExperience->getKey();
        $log = $this->storeVersionedUpload(
            $workflowApp,
            $file,
            'experience',
            'experience_doc',
            $refId,
            $replacementReason,
            'Experience document upload'
        );

        $masterExperience->refresh();

        return $this->resolveMasterPathAfterUpload($log, null, $masterExperience, 'support_document');
    }

    /**
     * @return string|null Approved path for master relieve_document
     */
    public function handleExperienceRelieveUpload(
        CC_CompetencyMeta $workflowApp,
        CC_Experience $masterExperience,
        UploadedFile $file,
        ?string $replacementReason = null
    ): ?string {
        $refId = (int) $masterExperience->getKey();
        $log = $this->storeVersionedUpload(
            $workflowApp,
            $file,
            'experience',
            'relieving_doc',
            $refId,
            $replacementReason,
            'Relieving letter upload'
        );

        $masterExperience->refresh();

        return $this->resolveMasterPathAfterUpload($log, null, $masterExperience, 'relieve_document');
    }

    /**
     * @return string|null Path stored on cc_proof_doc.proof_doc
     */
    public function handleProofUpload(
        CC_CompetencyMeta $workflowApp,
        CC_Proof_doc $proof,
        UploadedFile $file,
        ?string $replacementReason = null
    ): ?string {
        $config = FormSProofDocumentService::configFor((string) $proof->proof_name);

        $log = $this->storeVersionedUpload(
            $workflowApp,
            $file,
            $config['module_type'],
            $config['document_type'],
            (int) $proof->getKey(),
            $replacementReason,
            ucfirst(strtolower((string) $proof->proof_name)) . ' document upload'
        );

        return $this->resolveProofPathAfterUpload($log, $proof);
    }

    /**
     * @return string|null Path for cc_proof_doc.proof_doc (PHOTO)
     */
    public function handlePhotoUpload(
        CC_CompetencyMeta $workflowApp,
        CC_Proof_doc $proof,
        UploadedFile $file,
        ?string $replacementReason = null
    ): ?string {
        return $this->handleProofUpload($workflowApp, $proof, $file, $replacementReason);
    }

    /**
     * @return string|null Path for cc_proof_doc.proof_doc (SIGN)
     */
    public function handleSignatureUpload(
        CC_CompetencyMeta $workflowApp,
        CC_Proof_doc $proof,
        UploadedFile $file,
        ?string $replacementReason = null
    ): ?string {
        return $this->handleProofUpload($workflowApp, $proof, $file, $replacementReason);
    }

    public function handleAlterationProofUpload(
        object $workflowApp,
        CC_Proof_doc $proof,
        UploadedFile $file
    ): ?string {
        $config = FormSProofDocumentService::configFor((string) $proof->proof_name);

        $log = $this->versionService->storeAlterationProofVersion(
            $file,
            $workflowApp,
            $config['module_type'],
            $config['document_type'],
            (int) $proof->getKey(),
            ucfirst(strtolower((string) $proof->proof_name)) . ' alteration proof upload'
        );

        return $this->resolveProofPathAfterUpload($log, $proof);
    }

    public function seedCarriedForwardIfRenewal(CC_CompetencyMeta $workflowApp): void
    {
        // Intentionally no-op: cc_doc_log rows are created only when the applicant
        // uploads a replacement document. Unchanged docs copy the parent path onto
        // the child cc_edu / cc_exp / cc_proof_doc row (no re-upload).
    }

    /**
     * Resolve the education row to copy a path from (parent, or already-snapshotted child).
     */
    public function resolveMasterEducation(
        CC_CompetencyMeta $workflowApp,
        ?int $eduId,
        ?string $level = null
    ): ?CC_Education {
        if ($eduId) {
            $found = CC_Education::whereKey($eduId)->first();
            if ($found) {
                return $found;
            }
        }

        $childId = (string) $workflowApp->application_id;
        $masterId = (string) $this->workflowService->masterApplication($workflowApp)->application_id;

        if ($level) {
            $onChild = CC_Education::where('application_id', $childId)
                ->where('educational_level', $level)
                ->first();
            if ($onChild) {
                return $onChild;
            }

            return CC_Education::where('application_id', $masterId)
                ->where('educational_level', $level)
                ->first();
        }

        return null;
    }

    protected function resolveProofPathAfterUpload(CC_Doc_Log $log, CC_Proof_doc $proof): ?string
    {
        if ($log->isPending()) {
            $pendingPath = $this->pendingPathForChildOwnedProof($log, $proof);
            if ($pendingPath !== null) {
                return $pendingPath;
            }

            $proof->refresh();

            return $proof->proof_doc ?: null;
        }

        $log->refresh();
        $proof->refresh();

        return $proof->proof_doc;
    }

    protected function resolveMasterPathAfterUpload(
        CC_Doc_Log $log,
        ?CC_Education $education = null,
        ?CC_Experience $experience = null,
        string $experienceField = 'support_document'
    ): ?string {
        // Pending renewal/alteration replacements on the issued (master) row stay in cc_doc_log only.
        // Child-owned education/experience/proof rows persist the new RENEWAL/ALTERATION path.
        if ($log->isPending()) {
            if ($education) {
                $pendingPath = $this->pendingPathForChildOwnedEducation($log, $education);
                if ($pendingPath !== null) {
                    return $pendingPath;
                }

                $education->refresh();

                return $education->upload_document;
            }
            if ($experience) {
                $pendingPath = $this->pendingPathForAlterationExperience($log, $experience);
                if ($pendingPath !== null) {
                    return $pendingPath;
                }

                $experience->refresh();

                return $experienceField === 'relieve_document'
                    ? $experience->relieve_document
                    : $experience->support_document;
            }

            return null;
        }

        $log->refresh();
        if ($education) {
            $education->refresh();

            return $education->upload_document;
        }
        if ($experience) {
            $experience->refresh();

            return $experienceField === 'relieve_document'
                ? $experience->relieve_document
                : $experience->support_document;
        }

        return null;
    }

    /**
     * New files on a renewal/alteration experience row belong to that application — return the
     * pending child-workflow storage path so it can be stored on the child cc_exp row.
     */
    protected function pendingPathForAlterationExperience(CC_Doc_Log $log, CC_Experience $experience): ?string
    {
        return $this->pendingPathForChildOwnedRow($log, (string) ($experience->application_id ?? ''));
    }

    protected function pendingPathForChildOwnedEducation(CC_Doc_Log $log, CC_Education $education): ?string
    {
        return $this->pendingPathForChildOwnedRow($log, (string) ($education->application_id ?? ''));
    }

    protected function pendingPathForChildOwnedProof(CC_Doc_Log $log, CC_Proof_doc $proof): ?string
    {
        return $this->pendingPathForChildOwnedRow($log, (string) ($proof->application_id ?? ''));
    }

    protected function pendingPathForChildOwnedRow(CC_Doc_Log $log, string $rowApplicationId): ?string
    {
        $workflow = $this->workflowService->findWorkflowByPk((int) $log->application_id);
        if (! $workflow || ! $this->workflowService->isChildWorkflow($workflow)) {
            return null;
        }
        if ((string) $workflow->application_id !== $rowApplicationId) {
            return null;
        }

        $path = trim((string) ($log->file_path ?? ''));

        return $path !== '' ? $path : null;
    }

    protected function storeVersionedUpload(
        CC_CompetencyMeta $workflowApp,
        UploadedFile $file,
        string $moduleType,
        string $documentType,
        int $moduleRefId,
        ?string $replacementReason,
        string $initialRemarks
    ): CC_Doc_Log {
        $stage = $this->workflowService->workflowStage($workflowApp);

        // Renewal/alteration: always version via uploadNewVersion (creates cc_doc_log only
        // because a real file was uploaded). New applications still use initial upload.
        if ($this->workflowService->isChildWorkflow($workflowApp)) {
            $remarks = $this->resolveReplacementRemarks($stage, $replacementReason, $initialRemarks);

            return $this->versionService->uploadNewVersion(
                $file,
                $workflowApp,
                $moduleType,
                $documentType,
                $moduleRefId,
                $remarks,
                $stage
            );
        }

        $workflowPk = $this->workflowService->workflowPk($workflowApp);
        $groupExists = CC_Doc_Log::forGroup($workflowPk, $moduleType, $moduleRefId, $documentType)->exists();

        if ($groupExists) {
            return $this->versionService->uploadNewVersion(
                $file,
                $workflowApp,
                $moduleType,
                $documentType,
                $moduleRefId,
                $this->resolveReplacementRemarks($stage, $replacementReason, $initialRemarks),
                $stage
            );
        }

        return $this->versionService->createInitialUpload(
            $file,
            $workflowApp,
            $moduleType,
            $documentType,
            $moduleRefId,
            $initialRemarks,
            $stage
        );
    }

    protected function resolveReplacementRemarks(
        string $stage,
        ?string $replacementReason,
        string $fallback
    ): string {
        if (in_array($stage, ['RENEWAL', 'ALTERATION'], true)) {
            $reason = trim((string) $replacementReason);
            if ($reason === '') {
                $reason = 'Document replacement submitted with application';
            }

            return ($stage === 'RENEWAL' ? 'Renewal request: ' : 'Alteration request: ') . $reason;
        }

        return $fallback . ' (updated on re-save)';
    }
}
