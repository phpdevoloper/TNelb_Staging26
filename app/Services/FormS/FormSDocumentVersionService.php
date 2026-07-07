<?php

namespace App\Services\FormS;

use App\Enums\DocumentApplicationType;
use App\Enums\DocumentRequestType;
use App\Enums\DocumentStorageType;
use App\Enums\DocumentVersionStatus;
use App\Models\DocumentsLog;
use App\Models\Mst_education;
use App\Models\Mst_experience;
use App\Models\Mst_Form_s_w;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbApplicantsSign;
use App\Services\DocumentVersion\DocumentStorageService;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class FormSDocumentVersionService
{
    public function __construct(
        protected DocumentStorageService $storageService,
        protected FormSDocumentMasterTableService $masterTableService,
        protected FormSApplicationWorkflowService $workflowService
    ) {}

    public function getDocumentSummaryForRef(
        Mst_Form_s_w $workflowApp,
        string $moduleType,
        int $moduleRefId,
        string $documentType
    ): array {
        return [
            'active' => $this->getActiveVersion($workflowApp->id, $moduleType, $moduleRefId, $documentType),
            'pending' => $this->getPendingVersion($workflowApp->id, $moduleType, $moduleRefId, $documentType),
        ];
    }

    public function uploadNewVersion(
        UploadedFile $file,
        Mst_Form_s_w $workflowApp,
        string $moduleType,
        string $documentType,
        int $moduleRefId,
        ?string $remarks = null,
        ?string $workflowStage = null
    ): DocumentsLog {
        $workflowPk = (int) $workflowApp->id;
        $stage = $workflowStage ?? $this->workflowService->workflowStage($workflowApp);

        if (!$this->groupExists($workflowPk, $moduleType, $moduleRefId, $documentType)) {
            return $this->createInitialUpload($file, $workflowApp, $moduleType, $documentType, $moduleRefId, $remarks, $stage);
        }

        if ($this->getPendingVersion($workflowPk, $moduleType, $moduleRefId, $documentType)) {
            throw new RuntimeException(
                'A pending version already exists for this document. Approve or reject it before uploading a new version.'
            );
        }

        $nextVersion = $this->getMaxVersionNo($workflowPk, $moduleType, $moduleRefId, $documentType) + 1;
        $hasActive = (bool) $this->getActiveVersion($workflowPk, $moduleType, $moduleRefId, $documentType);
        $requestType = $hasActive
            ? $this->resolveFollowUpRequestType($stage)
            : DocumentRequestType::INITIAL;

        return $this->storeVersion(
            $file,
            $workflowApp,
            $moduleType,
            $documentType,
            $moduleRefId,
            $nextVersion,
            $requestType,
            $remarks,
            $stage
        );
    }

    public function createInitialUpload(
        UploadedFile $file,
        Mst_Form_s_w $workflowApp,
        string $moduleType,
        string $documentType,
        int $moduleRefId,
        ?string $remarks = null,
        ?string $workflowStage = null
    ): DocumentsLog {
        $workflowPk = (int) $workflowApp->id;
        if ($this->groupExists($workflowPk, $moduleType, $moduleRefId, $documentType)) {
            throw new RuntimeException('Document group already exists. Use uploadNewVersion instead.');
        }

        return $this->storeVersion(
            $file,
            $workflowApp,
            $moduleType,
            $documentType,
            $moduleRefId,
            1,
            DocumentRequestType::INITIAL,
            $remarks,
            $workflowStage ?? $this->workflowService->workflowStage($workflowApp)
        );
    }

    public function seedDocumentReferencesFromParent(Mst_Form_s_w $parent, Mst_Form_s_w $child): void
    {
        $parent->loadMissing([]);
        $applicationType = DocumentApplicationType::fromWorkflowStage(
            $this->workflowService->workflowStage($child)
        );

        $parentEducations = Mst_education::where('application_id', $parent->application_id)->get();
        foreach ($parentEducations as $parentEducation) {
            $filePath = $parentEducation->upload_document;
            if (!$filePath) {
                continue;
            }

            if (DocumentsLog::forGroup($child->id, 'education', $parentEducation->id, 'certificate')->exists()) {
                continue;
            }

            DocumentsLog::create([
                'application_id' => $child->id,
                'parent_application_id' => $this->workflowService->documentsLogParentApplicationId($child),
                'module_type' => 'education',
                'module_ref_id' => $parentEducation->id,
                'document_type' => 'certificate',
                'file_name' => basename($filePath),
                'file_path' => $filePath,
                'old_file_path' => $filePath,
                'storage_type' => DocumentStorageType::PERMANENT,
                'request_type' => DocumentRequestType::INITIAL,
                'application_type' => $applicationType,
                'version_no' => 1,
                'status' => DocumentVersionStatus::APPROVED,
                'is_active' => true,
                'remarks' => 'Carried forward from parent application ' . $parent->application_id,
            ]);
        }

        $parentExperiences = Mst_experience::where('application_id', $parent->application_id)->get();
        foreach ($parentExperiences as $parentExperience) {
            $filePath = $parentExperience->support_document;
            if (!$filePath) {
                continue;
            }

            $refId = (int) $parentExperience->exp_id;
            if (DocumentsLog::forGroup($child->id, 'experience', $refId, 'experience_doc')->exists()) {
                continue;
            }

            DocumentsLog::create([
                'application_id' => $child->id,
                'parent_application_id' => $this->workflowService->documentsLogParentApplicationId($child),
                'module_type' => 'experience',
                'module_ref_id' => $refId,
                'document_type' => 'experience_doc',
                'file_name' => basename($filePath),
                'file_path' => $filePath,
                'old_file_path' => $filePath,
                'storage_type' => DocumentStorageType::PERMANENT,
                'request_type' => DocumentRequestType::INITIAL,
                'application_type' => $applicationType,
                'version_no' => 1,
                'status' => DocumentVersionStatus::APPROVED,
                'is_active' => true,
                'remarks' => 'Carried forward from parent application ' . $parent->application_id,
            ]);
        }

        $parentPhoto = TnelbApplicantPhoto::where('application_id', $parent->application_id)->first();
        if ($parentPhoto && $parentPhoto->upload_path) {
            $this->seedCarriedForwardMedia($parent, $child, 'photo', 'photo', $parentPhoto->upload_path, $applicationType);
        }

        $parentSign = TnelbApplicantsSign::where('application_id', $parent->application_id)->first();
        if ($parentSign && $parentSign->uploaded_doc) {
            $this->seedCarriedForwardMedia($parent, $child, 'signature', 'signature', $parentSign->uploaded_doc, $applicationType);
        }
    }

    protected function seedCarriedForwardMedia(
        Mst_Form_s_w $parent,
        Mst_Form_s_w $child,
        string $moduleType,
        string $documentType,
        string $filePath,
        DocumentApplicationType $applicationType
    ): void {
        $refId = (int) $child->id;
        if (DocumentsLog::forGroup($child->id, $moduleType, $refId, $documentType)->exists()) {
            return;
        }

        DocumentsLog::create([
            'application_id' => $child->id,
            'parent_application_id' => $this->workflowService->documentsLogParentApplicationId($child),
            'module_type' => $moduleType,
            'module_ref_id' => $refId,
            'document_type' => $documentType,
            'file_name' => basename($filePath),
            'file_path' => $filePath,
            'old_file_path' => $filePath,
            'storage_type' => DocumentStorageType::PERMANENT,
            'request_type' => DocumentRequestType::INITIAL,
            'application_type' => $applicationType,
            'version_no' => 1,
            'status' => DocumentVersionStatus::APPROVED,
            'is_active' => true,
            'remarks' => 'Carried forward from parent application ' . $parent->application_id,
        ]);
    }

    public function ensureCarriedForwardDocuments(Mst_Form_s_w $workflowApp): int
    {
        if (!$this->workflowService->isChildWorkflow($workflowApp)) {
            return 0;
        }

        $parent = $this->workflowService->masterApplication($workflowApp);
        if ($parent->id === $workflowApp->id) {
            return 0;
        }

        $before = DocumentsLog::where('application_id', $workflowApp->id)->count();
        $this->seedDocumentReferencesFromParent($parent, $workflowApp);

        return DocumentsLog::where('application_id', $workflowApp->id)->count() - $before;
    }

    protected function storeVersion(
        UploadedFile $file,
        Mst_Form_s_w $workflowApp,
        string $moduleType,
        string $documentType,
        int $moduleRefId,
        int $versionNo,
        DocumentRequestType $requestType,
        ?string $remarks,
        string $workflowStage
    ): DocumentsLog {
        $masterApp = $this->workflowService->masterApplication($workflowApp);
        $oldFilePath = $this->masterTableService->resolveFilePath(
            $masterApp,
            $moduleType,
            $moduleRefId,
            $documentType
        );

        $stored = $this->storageService->store(
            $file,
            (string) $workflowApp->application_id,
            (int) $workflowApp->id,
            $moduleType,
            $documentType,
            $requestType,
            $workflowStage,
            true
        );

        $document = DocumentsLog::create([
            'application_id' => $workflowApp->id,
            'parent_application_id' => $this->workflowService->documentsLogParentApplicationId($workflowApp),
            'module_type' => $moduleType,
            'module_ref_id' => $moduleRefId,
            'document_type' => $documentType,
            'file_name' => $stored['file_name'],
            'file_path' => $stored['file_path'],
            'old_file_path' => $oldFilePath,
            'storage_type' => DocumentStorageType::PERMANENT,
            'request_type' => $requestType,
            'application_type' => DocumentApplicationType::fromWorkflowStage($workflowStage),
            'version_no' => $versionNo,
            'status' => DocumentVersionStatus::PENDING,
            'is_active' => false,
            'remarks' => $remarks,
        ]);

        if (!$this->requiresStaffApproval($workflowStage)) {
            return $this->finalizeApprovedVersion($document);
        }

        return $document;
    }

    protected function finalizeApprovedVersion(DocumentsLog $document): DocumentsLog
    {
        DocumentsLog::forGroup(
            $document->application_id,
            $document->module_type,
            $document->module_ref_id,
            $document->document_type
        )->active()->update(['is_active' => false]);

        $document->update([
            'status' => DocumentVersionStatus::APPROVED,
            'is_active' => true,
            'approved_at' => now(),
        ]);

        $this->masterTableService->syncApprovedFilePath($document->fresh());

        return $document->fresh();
    }

    protected function requiresStaffApproval(string $workflowStage): bool
    {
        return in_array(strtoupper($workflowStage), ['RENEWAL', 'ALTERATION'], true);
    }

    protected function resolveFollowUpRequestType(string $workflowStage): DocumentRequestType
    {
        return match (strtoupper($workflowStage)) {
            'RENEWAL' => DocumentRequestType::RENEWAL,
            'ALTERATION' => DocumentRequestType::ALTERATION,
            default => DocumentRequestType::ALTERATION,
        };
    }

    public function getActiveVersion(
        int $workflowApplicationPk,
        string $moduleType,
        int $moduleRefId,
        string $documentType
    ): ?DocumentsLog {
        return DocumentsLog::forGroup($workflowApplicationPk, $moduleType, $moduleRefId, $documentType)
            ->active()
            ->first();
    }

    public function getPendingVersion(
        int $workflowApplicationPk,
        string $moduleType,
        int $moduleRefId,
        string $documentType
    ): ?DocumentsLog {
        return DocumentsLog::forGroup($workflowApplicationPk, $moduleType, $moduleRefId, $documentType)
            ->pending()
            ->orderByDesc('version_no')
            ->first();
    }

    protected function groupExists(
        int $workflowApplicationPk,
        string $moduleType,
        int $moduleRefId,
        string $documentType
    ): bool {
        return DocumentsLog::forGroup($workflowApplicationPk, $moduleType, $moduleRefId, $documentType)->exists();
    }

    protected function getMaxVersionNo(
        int $workflowApplicationPk,
        string $moduleType,
        int $moduleRefId,
        string $documentType
    ): int {
        return (int) DocumentsLog::forGroup($workflowApplicationPk, $moduleType, $moduleRefId, $documentType)
            ->max('version_no');
    }
}
