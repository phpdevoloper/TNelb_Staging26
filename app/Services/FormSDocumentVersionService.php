<?php

namespace App\Services\FormS;

use App\Enums\DocumentApplicationType;
use App\Enums\DocumentRequestType;
use App\Enums\DocumentStorageType;
use App\Enums\DocumentVersionStatus;
use App\Models\CC_Doc_Log;
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
        $isChildWorkflow = $this->requiresStaffApproval($stage);

        /*
         * First upload on a NEW application still uses createInitialUpload.
         * Renewal/alteration always create a follow-up version row (no silent seed
         * of parent docs) — only when the applicant actually uploads a file.
         */
        if (! $this->groupExists($workflowPk, $moduleType, $moduleRefId, $documentType) && ! $isChildWorkflow) {
            return $this->createInitialUpload($file, $workflowApp, $moduleType, $documentType, $moduleRefId, $remarks, $stage);
        }

        /*
         * Renewal/alteration drafts create PENDING versions (staff must approve later).
         * Preview & Proceed / Save Draft can re-post the same file input; replace the
         * existing pending row instead of blocking with "pending version already exists".
         */
        $pending = $this->getPendingVersion($workflowPk, $moduleType, $moduleRefId, $documentType);
        if ($pending) {
            return $this->replacePendingVersionFile(
                $pending,
                $file,
                $workflowApp,
                $moduleType,
                $documentType,
                $remarks,
                $stage
            );
        }

        $nextVersion = $this->getMaxVersionNoAcrossParentAndChild(
            $workflowApp,
            $moduleType,
            $moduleRefId,
            $documentType
        ) + 1;

        $hasActiveOnChild = (bool) $this->getActiveVersion($workflowPk, $moduleType, $moduleRefId, $documentType);
        $hasMasterPath = (bool) $this->masterTableService->resolveFilePath(
            $this->workflowService->masterApplication($workflowApp),
            $moduleType,
            $moduleRefId,
            $documentType
        );
        $requestType = ($isChildWorkflow || $hasActiveOnChild || $hasMasterPath)
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

    /**
     * Applicant re-upload while a pending version is open (common on renewal draft re-save).
     */
    protected function replacePendingVersionFile(
        CC_Doc_Log $pending,
        UploadedFile $file,
        CC_CompetencyMeta $workflowApp,
        string $moduleType,
        string $documentType,
        ?string $remarks,
        string $workflowStage
    ): CC_Doc_Log {
        $workflowPk = $this->workflowService->workflowPk($workflowApp);
        $stored = $this->storeUploadedFile(
            $file,
            (string) $workflowApp->application_id,
            $workflowPk,
            $moduleType,
            $documentType,
            $pending->request_type instanceof DocumentRequestType
                ? $pending->request_type
                : DocumentRequestType::RENEWAL,
            $workflowStage,
            true
        );

        $pending->update([
            'file_name' => $stored['file_name'],
            'file_path' => $stored['file_path'],
            'remarks' => $remarks ?: ($pending->remarks ?: 'Document re-uploaded on draft save'),
        ]);

        return $pending->fresh();
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

    /**
     * @deprecated No longer seeds cc_doc_log. Unchanged renewal docs stay on master tables only.
     */
    public function seedDocumentReferencesFromParent(CC_CompetencyMeta $parent, CC_CompetencyMeta $child): void
    {
        // Intentionally empty — do not insert carried-forward rows into cc_doc_log.
    }

    /**
     * @deprecated No longer seeds cc_doc_log on renewal/alteration open.
     */
    public function ensureCarriedForwardDocuments(CC_CompetencyMeta $workflowApp): int
    {
        return 0;
    }

    /**
     * Version numbers continue across parent (NEW) + child (RENEWAL/ALTERATION) groups.
     */
    protected function getMaxVersionNoAcrossParentAndChild(
        CC_CompetencyMeta $workflowApp,
        string $moduleType,
        int $moduleRefId,
        string $documentType
    ): int {
        $childPk = $this->workflowService->workflowPk($workflowApp);
        $max = $this->getMaxVersionNo($childPk, $moduleType, $moduleRefId, $documentType);

        $parentPk = $this->workflowService->parentApplicationPk($workflowApp);
        if ($parentPk) {
            $max = max($max, $this->getMaxVersionNo($parentPk, $moduleType, $moduleRefId, $documentType));
        }

        return $max;
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
            $pending = $this->getPendingVersion($workflowPk, $moduleType, $proofId, $documentType);
            if ($pending) {
                $stored = $this->storeUploadedFile(
                    $file,
                    (string) $alterationWorkflow->application_id,
                    $workflowPk,
                    $moduleType,
                    $documentType,
                    DocumentRequestType::ALTERATION,
                    $stage,
                    true
                );
                $pending->update([
                    'file_name' => $stored['file_name'],
                    'file_path' => $stored['file_path'],
                    'remarks' => $remarks ?: ($pending->remarks ?: 'Alteration proof re-uploaded on draft save'),
                ]);

                return $pending->fresh();
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

        $legacyParentPk = CC_Forms_meta::where('application_id', $oldApplicationId)->value('app_id');

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
