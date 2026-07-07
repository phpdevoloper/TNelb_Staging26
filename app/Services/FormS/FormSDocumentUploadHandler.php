<?php

namespace App\Services\FormS;

use App\Models\DocumentsLog;
use App\Models\Mst_education;
use App\Models\Mst_experience;
use App\Models\Mst_Form_s_w;
use App\Services\Competency\CompetencyDocumentSupport;
use Illuminate\Http\UploadedFile;

/**
 * Bridges Form S controller uploads to documents_log + master tables.
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
        Mst_Form_s_w $workflowApp,
        Mst_education $masterEducation,
        UploadedFile $file,
        ?string $replacementReason = null
    ): ?string {
        $log = $this->storeVersionedUpload(
            $workflowApp,
            $file,
            'education',
            'certificate',
            (int) $masterEducation->id,
            $replacementReason,
            'Education document upload'
        );

        return $this->resolveMasterPathAfterUpload($log, $masterEducation);
    }

    /**
     * @return string|null Approved path for master support_document
     */
    public function handleExperienceSupportUpload(
        Mst_Form_s_w $workflowApp,
        Mst_experience $masterExperience,
        UploadedFile $file,
        ?string $replacementReason = null
    ): ?string {
        $refId = (int) $masterExperience->exp_id;
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

        return $this->resolveMasterPathAfterUpload($log, null, $masterExperience);
    }

    /**
     * @return string|null Path for tnelb_applicant_photos.upload_path
     */
    public function handlePhotoUpload(
        Mst_Form_s_w $workflowApp,
        UploadedFile $file,
        ?string $replacementReason = null
    ): ?string {
        $log = $this->handleApplicationMediaUpload(
            $workflowApp,
            $file,
            'photo',
            'photo',
            $replacementReason,
            'Applicant photo upload'
        );

        return $this->resolveApplicationMediaPath($log);
    }

    /**
     * @return string|null Path for tnelb_applicants_sign.uploaded_doc
     */
    public function handleSignatureUpload(
        Mst_Form_s_w $workflowApp,
        UploadedFile $file,
        ?string $replacementReason = null
    ): ?string {
        $log = $this->handleApplicationMediaUpload(
            $workflowApp,
            $file,
            'signature',
            'signature',
            $replacementReason,
            'Applicant signature upload'
        );

        return $this->resolveApplicationMediaPath($log);
    }

    public function handleAlterationProofUpload(
        Mst_Form_s_w $workflowApp,
        UploadedFile $file,
        string $documentType
    ): ?string {
        $log = $this->storeVersionedUpload(
            $workflowApp,
            $file,
            'alteration',
            $documentType,
            (int) $workflowApp->id,
            null,
            'Alteration supporting proof'
        );

        return $log->file_path ?? null;
    }

    public function seedCarriedForwardIfRenewal(Mst_Form_s_w $workflowApp): void
    {
        if (!$this->workflowService->isChildWorkflow($workflowApp)) {
            return;
        }

        $this->versionService->ensureCarriedForwardDocuments($workflowApp);
    }

    /**
     * Resolve master education row for a form row (renewal uses parent APP rows).
     */
    public function resolveMasterEducation(
        Mst_Form_s_w $workflowApp,
        ?int $eduId,
        ?string $level = null
    ): ?Mst_education {
        $masterApp = $this->workflowService->masterApplication($workflowApp);

        if ($eduId) {
            return Mst_education::where('application_id', $masterApp->application_id)
                ->whereKey($eduId)
                ->first();
        }

        if ($level) {
            return Mst_education::where('application_id', $masterApp->application_id)
                ->where('educational_level', $level)
                ->first();
        }

        return null;
    }

    protected function resolveMasterPathAfterUpload(
        DocumentsLog $log,
        ?Mst_education $education = null,
        ?Mst_experience $experience = null
    ): ?string {
        if ($log->isPending()) {
            return $education?->upload_document ?? $experience?->support_document;
        }

        $log->refresh();
        if ($education) {
            $education->refresh();

            return $education->upload_document;
        }
        if ($experience) {
            $experience->refresh();

            return $experience->support_document;
        }

        return null;
    }

    protected function handleApplicationMediaUpload(
        Mst_Form_s_w $workflowApp,
        UploadedFile $file,
        string $moduleType,
        string $documentType,
        ?string $replacementReason,
        string $initialRemarks
    ): DocumentsLog {
        return $this->storeVersionedUpload(
            $workflowApp,
            $file,
            $moduleType,
            $documentType,
            (int) $workflowApp->id,
            $replacementReason,
            $initialRemarks
        );
    }

    /**
     * First upload creates version 1; any later upload for the same document group
     * (e.g. preview → back to edit → submit again) stores a new version instead.
     */
    protected function storeVersionedUpload(
        Mst_Form_s_w $workflowApp,
        UploadedFile $file,
        string $moduleType,
        string $documentType,
        int $moduleRefId,
        ?string $replacementReason,
        string $initialRemarks
    ): DocumentsLog {
        $workflowPk = (int) $workflowApp->id;
        $stage = $this->workflowService->workflowStage($workflowApp);
        $groupExists = DocumentsLog::forGroup($workflowPk, $moduleType, $moduleRefId, $documentType)->exists();

        if ($groupExists) {
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

    protected function resolveApplicationMediaPath(DocumentsLog $log): ?string
    {
        if ($log->isPending()) {
            $workflowApp = Mst_Form_s_w::find($log->application_id);
            if (!$workflowApp) {
                return null;
            }

            $masterApp = $this->workflowService->masterApplication($workflowApp);

            return $this->masterTableService->resolveFilePath(
                $masterApp,
                $log->module_type,
                $log->module_ref_id,
                $log->document_type
            );
        }

        $log->refresh();

        return $log->file_path;
    }
}
