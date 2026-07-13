<?php

namespace App\Services\FormS;

use App\Enums\DocumentApplicationType;
use App\Enums\DocumentRequestType;
use App\Enums\DocumentStorageType;
use App\Enums\DocumentVersionStatus;
use App\Models\CC_Doc_Log;
use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Forms_Meta;
use App\Models\CC_Proof_doc;
use App\Models\Competency\CC_CompetencyMeta;
use App\Services\DocumentVersion\DocumentStorageService;
use App\Services\FormS\SensitiveProofCryptService;
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
        CC_CompetencyMeta $workflowApp,
        string $moduleType,
        int $moduleRefId,
        string $documentType
    ): array {
        $workflowPk = $this->workflowService->workflowPk($workflowApp);

        return [
            'active' => $this->getActiveVersion($workflowPk, $moduleType, $moduleRefId, $documentType),
            'pending' => $this->getPendingVersion($workflowPk, $moduleType, $moduleRefId, $documentType),
        ];
    }

    public function uploadNewVersion(
        UploadedFile $file,
        CC_CompetencyMeta $workflowApp,
        string $moduleType,
        string $documentType,
        int $moduleRefId,
        ?string $remarks = null,
        ?string $workflowStage = null
    ): CC_Doc_Log {
        $workflowPk = $this->workflowService->workflowPk($workflowApp);
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
        CC_CompetencyMeta $workflowApp,
        string $moduleType,
        string $documentType,
        int $moduleRefId,
        ?string $remarks = null,
        ?string $workflowStage = null
    ): CC_Doc_Log {
        $workflowPk = $this->workflowService->workflowPk($workflowApp);
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

    public function seedDocumentReferencesFromParent(CC_CompetencyMeta $parent, CC_CompetencyMeta $child): void
    {
        $applicationType = DocumentApplicationType::fromWorkflowStage(
            $this->workflowService->workflowStage($child)
        );
        $childPk = $this->workflowService->workflowPk($child);

        $parentEducations = CC_Education::where('application_id', $parent->application_id)->get();
        foreach ($parentEducations as $parentEducation) {
            $filePath = $parentEducation->upload_document;
            if (!$filePath) {
                continue;
            }

            $eduRefId = (int) $parentEducation->getKey();
            if (CC_Doc_Log::forGroup($childPk, 'education', $eduRefId, 'certificate')->exists()) {
                continue;
            }

            CC_Doc_Log::create([
                'application_id' => $childPk,
                'parent_application_id' => $this->workflowService->documentsLogParentApplicationId($child),
                'module_type' => 'education',
                'module_ref_id' => $eduRefId,
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

        $parentExperiences = CC_Experience::where('application_id', $parent->application_id)->get();
        foreach ($parentExperiences as $parentExperience) {
            $filePath = $parentExperience->support_document;
            if (!$filePath) {
                continue;
            }

            $refId = (int) $parentExperience->getKey();
            if (CC_Doc_Log::forGroup($childPk, 'experience', $refId, 'experience_doc')->exists()) {
                continue;
            }

            CC_Doc_Log::create([
                'application_id' => $childPk,
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

            $relievePath = $parentExperience->relieve_document;
            if ($relievePath && ! CC_Doc_Log::forGroup($childPk, 'experience', $refId, 'relieving_doc')->exists()) {
                CC_Doc_Log::create([
                    'application_id' => $childPk,
                    'parent_application_id' => $this->workflowService->documentsLogParentApplicationId($child),
                    'module_type' => 'experience',
                    'module_ref_id' => $refId,
                    'document_type' => 'relieving_doc',
                    'file_name' => basename($relievePath),
                    'file_path' => $relievePath,
                    'old_file_path' => $relievePath,
                    'storage_type' => DocumentStorageType::PERMANENT,
                    'request_type' => DocumentRequestType::INITIAL,
                    'application_type' => $applicationType,
                    'version_no' => 1,
                    'status' => DocumentVersionStatus::APPROVED,
                    'is_active' => true,
                    'remarks' => 'Carried forward from parent application ' . $parent->application_id,
                ]);
            }
        }

        $parentProofs = CC_Proof_doc::where('application_id', $parent->application_id)->get();
        foreach ($parentProofs as $parentProof) {
            $filePath = $parentProof->proof_doc;
            if (! $filePath) {
                continue;
            }

            $config = FormSProofDocumentService::configFor((string) $parentProof->proof_name);
            $refId = (int) $parentProof->getKey();
            if (CC_Doc_Log::forGroup($childPk, $config['module_type'], $refId, $config['document_type'])->exists()) {
                continue;
            }

            CC_Doc_Log::create([
                'application_id' => $childPk,
                'parent_application_id' => $this->workflowService->documentsLogParentApplicationId($child),
                'module_type' => $config['module_type'],
                'module_ref_id' => $refId,
                'document_type' => $config['document_type'],
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
    }

    public function ensureCarriedForwardDocuments(CC_CompetencyMeta $workflowApp): int
    {
        if (!$this->workflowService->isChildWorkflow($workflowApp)) {
            return 0;
        }

        $parent = $this->workflowService->masterApplication($workflowApp);
        $workflowPk = $this->workflowService->workflowPk($workflowApp);
        if ($this->workflowService->workflowPk($parent) === $workflowPk) {
            return 0;
        }

        $before = CC_Doc_Log::where('application_id', $workflowPk)->count();
        $this->seedDocumentReferencesFromParent($parent, $workflowApp);

        return CC_Doc_Log::where('application_id', $workflowPk)->count() - $before;
    }

    public function storeAlterationProofVersion(
        UploadedFile $file,
        object $alterationWorkflow,
        string $moduleType,
        string $documentType,
        int $proofId,
        ?string $remarks = null
    ): CC_Doc_Log {
        $workflowPk = (int) $alterationWorkflow->getKey();
        $stage = 'ALTERATION';

        if ($this->groupExists($workflowPk, $moduleType, $proofId, $documentType)) {
            if ($this->getPendingVersion($workflowPk, $moduleType, $proofId, $documentType)) {
                throw new RuntimeException(
                    'A pending version already exists for this alteration proof. Approve or reject it before uploading a new version.'
                );
            }

            $nextVersion = $this->getMaxVersionNo($workflowPk, $moduleType, $proofId, $documentType) + 1;
            $requestType = DocumentRequestType::ALTERATION;
        } else {
            $nextVersion = 1;
            $requestType = DocumentRequestType::INITIAL;
        }

        $oldFilePath = CC_Proof_doc::whereKey($proofId)->value('proof_doc');
        $parentPk = $this->resolveLegacyAlterationParentPk($alterationWorkflow);

        $stored = $this->storeUploadedFile(
            $file,
            (string) $alterationWorkflow->application_id,
            $workflowPk,
            $moduleType,
            $documentType,
            $requestType,
            $stage,
            true
        );

        return CC_Doc_Log::create([
            'application_id' => $workflowPk,
            'parent_application_id' => $parentPk,
            'module_type' => $moduleType,
            'module_ref_id' => $proofId,
            'document_type' => $documentType,
            'file_name' => $stored['file_name'],
            'file_path' => $stored['file_path'],
            'old_file_path' => $oldFilePath,
            'storage_type' => DocumentStorageType::PERMANENT,
            'request_type' => $requestType,
            'application_type' => DocumentApplicationType::fromWorkflowStage($stage),
            'version_no' => $nextVersion,
            'status' => DocumentVersionStatus::PENDING,
            'is_active' => false,
            'remarks' => $remarks,
        ]);
    }

    protected function resolveLegacyAlterationParentPk(object $alterationWorkflow): ?int
    {
        $oldApplicationId = trim((string) ($alterationWorkflow->old_application ?? ''));
        if ($oldApplicationId === '') {
            return null;
        }

        $ccParentPk = CC_Forms_Meta::findByApplicationId($oldApplicationId)?->app_id;
        if ($ccParentPk) {
            return (int) $ccParentPk;
        }

        $legacyParentPk = CC_Forms_meta::where('application_id', $oldApplicationId)->value('id');

        return $legacyParentPk ? (int) $legacyParentPk : null;
    }

    protected function storeVersion(
        UploadedFile $file,
        CC_CompetencyMeta $workflowApp,
        string $moduleType,
        string $documentType,
        int $moduleRefId,
        int $versionNo,
        DocumentRequestType $requestType,
        ?string $remarks,
        string $workflowStage
    ): CC_Doc_Log {
        $workflowPk = $this->workflowService->workflowPk($workflowApp);
        $masterApp = $this->workflowService->masterApplication($workflowApp);
        $oldFilePath = $this->masterTableService->resolveFilePath(
            $masterApp,
            $moduleType,
            $moduleRefId,
            $documentType
        );

        $stored = $this->storeUploadedFile(
            $file,
            (string) $workflowApp->application_id,
            $workflowPk,
            $moduleType,
            $documentType,
            $requestType,
            $workflowStage,
            true
        );

        $document = CC_Doc_Log::create([
            'application_id' => $workflowPk,
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

    protected function finalizeApprovedVersion(CC_Doc_Log $document): CC_Doc_Log
    {
        CC_Doc_Log::forGroup(
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
    ): ?CC_Doc_Log {
        return CC_Doc_Log::forGroup($workflowApplicationPk, $moduleType, $moduleRefId, $documentType)
            ->active()
            ->first();
    }

    public function getPendingVersion(
        int $workflowApplicationPk,
        string $moduleType,
        int $moduleRefId,
        string $documentType
    ): ?CC_Doc_Log {
        return CC_Doc_Log::forGroup($workflowApplicationPk, $moduleType, $moduleRefId, $documentType)
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
        return CC_Doc_Log::forGroup($workflowApplicationPk, $moduleType, $moduleRefId, $documentType)->exists();
    }

    protected function getMaxVersionNo(
        int $workflowApplicationPk,
        string $moduleType,
        int $moduleRefId,
        string $documentType
    ): int {
        return (int) CC_Doc_Log::forGroup($workflowApplicationPk, $moduleType, $moduleRefId, $documentType)
            ->max('version_no');
    }

    /**
     * @return array{file_name: string, file_path: string, mime_type: string, file_size: int, original_file_name: string}
     */
    protected function storeUploadedFile(
        UploadedFile $file,
        string $applicationNo,
        int $applicationId,
        string $moduleType,
        string $documentType,
        DocumentRequestType $requestType,
        ?string $workflowStage,
        bool $useProductionDocumentLog
    ): array {
        if (SensitiveProofCryptService::requiresEncryptionForModule($moduleType, $documentType)) {
            return $this->storageService->storeEncrypted(
                $file,
                $applicationNo,
                $applicationId,
                $moduleType,
                $documentType,
                $requestType,
                $workflowStage,
                $useProductionDocumentLog
            );
        }

        return $this->storageService->store(
            $file,
            $applicationNo,
            $applicationId,
            $moduleType,
            $documentType,
            $requestType,
            $workflowStage,
            $useProductionDocumentLog
        );
    }
}
