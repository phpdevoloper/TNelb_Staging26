<?php

namespace App\Services\DocumentVersion;

use App\Enums\DocumentApplicationType;
use App\Enums\DocumentRequestType;
use App\Enums\DocumentStorageType;
use App\Enums\DocumentVersionStatus;
use App\Models\DApplication;
use App\Models\DDocument;
use App\Services\DocumentVersion\DocumentGroupKey;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use RuntimeException;

class DocumentVersionService
{
    public function __construct(
        protected DocumentStorageService $storageService,
        protected DocumentMasterTableService $masterTableService
    ) {}

    public function createInitialUpload(
        UploadedFile $file,
        int $applicationId,
        string $moduleType,
        string $documentType,
        ?int $moduleRefId = null,
        ?string $remarks = null,
        ?string $workflowStage = null
    ): DDocument {
        if ($this->groupExists($applicationId, $moduleType, $moduleRefId, $documentType)) {
            throw new RuntimeException(
                'A document group already exists for this record and type. Use uploadNewVersion instead.'
            );
        }

        return $this->storeVersion(
            file: $file,
            applicationId: $applicationId,
            moduleType: $moduleType,
            documentType: $documentType,
            moduleRefId: $moduleRefId,
            versionNo: 1,
            requestType: DocumentRequestType::INITIAL,
            remarks: $remarks,
            workflowStage: $workflowStage
        );
    }

    public function uploadNewVersion(
        UploadedFile $file,
        int $applicationId,
        string $moduleType,
        string $documentType,
        ?int $moduleRefId = null,
        ?string $remarks = null,
        ?string $workflowStage = null
    ): DDocument {
        if (!$this->groupExists($applicationId, $moduleType, $moduleRefId, $documentType)) {
            return $this->createInitialUpload(
                $file,
                $applicationId,
                $moduleType,
                $documentType,
                $moduleRefId,
                $remarks,
                $workflowStage
            );
        }

        $pending = $this->getPendingVersion($applicationId, $moduleType, $moduleRefId, $documentType);
        if ($pending) {
            throw new RuntimeException(
                'A pending version already exists for this document. Approve or reject it before uploading a new version.'
            );
        }

        $nextVersion = $this->getMaxVersionNo($applicationId, $moduleType, $moduleRefId, $documentType) + 1;
        $hasActive = (bool) $this->getActiveVersion($applicationId, $moduleType, $moduleRefId, $documentType);

        if (!$workflowStage) {
            $application = DApplication::find($applicationId);
            if ($application?->isRenewalApplication() || $application?->request_context === 'RENEWAL') {
                $workflowStage = 'RENEWAL';
            } elseif ($application?->isAlterationApplication() || $application?->request_context === 'ALTERATION') {
                $workflowStage = 'ALTERATION';
            } elseif ($application?->request_context === 'DIGITISATION') {
                $workflowStage = 'DIGITISATION';
            }
        }

        $requestType = $hasActive
            ? $this->resolveFollowUpRequestType($workflowStage)
            : DocumentRequestType::INITIAL;

        return $this->storeVersion(
            file: $file,
            applicationId: $applicationId,
            moduleType: $moduleType,
            documentType: $documentType,
            moduleRefId: $moduleRefId,
            versionNo: $nextVersion,
            requestType: $requestType,
            remarks: $remarks,
            workflowStage: $workflowStage ?? ($requestType === DocumentRequestType::INITIAL ? 'NEW' : strtoupper($requestType->value))
        );
    }

    protected function resolveFollowUpRequestType(?string $workflowStage): DocumentRequestType
    {
        return match (strtoupper(trim((string) $workflowStage))) {
            'RENEWAL' => DocumentRequestType::RENEWAL,
            'ALTERATION' => DocumentRequestType::ALTERATION,
            default => DocumentRequestType::ALTERATION,
        };
    }

    public function requestAlteration(
        UploadedFile $file,
        int $applicationId,
        string $moduleType,
        string $documentType,
        ?int $moduleRefId,
        string $alterationReason
    ): DDocument {
        $reason = trim($alterationReason);

        if ($reason === '') {
            throw new RuntimeException('Alteration reason is required.');
        }

        if (!$this->getActiveVersion($applicationId, $moduleType, $moduleRefId, $documentType)) {
            throw new RuntimeException(
                'No approved document exists for this record. Use initial upload instead of alteration.'
            );
        }

        if ($this->getPendingVersion($applicationId, $moduleType, $moduleRefId, $documentType)) {
            throw new RuntimeException(
                'A pending alteration already exists. Approve or reject it before submitting another.'
            );
        }

        $nextVersion = $this->getMaxVersionNo($applicationId, $moduleType, $moduleRefId, $documentType) + 1;

        return $this->storeVersion(
            file: $file,
            applicationId: $applicationId,
            moduleType: $moduleType,
            documentType: $documentType,
            moduleRefId: $moduleRefId,
            versionNo: $nextVersion,
            requestType: DocumentRequestType::ALTERATION,
            remarks: 'Alteration request: ' . $reason,
            workflowStage: 'ALTERATION'
        );
    }

    public function requestAlterationByGroupKey(
        string $groupKey,
        UploadedFile $file,
        string $alterationReason
    ): DDocument {
        $group = DocumentGroupKey::decode($groupKey);

        return $this->requestAlteration(
            $file,
            $group['application_id'],
            $group['module_type'],
            $group['document_type'],
            $group['module_ref_id'],
            $alterationReason
        );
    }

    public function listAlterableDocumentsForApplication(int $applicationId): Collection
    {
        return $this->listDocumentsForApplication($applicationId)
            ->filter(fn (array $summary) => $summary['active_version'] && !$summary['pending_version'])
            ->values();
    }

    public function getActiveVersion(
        int $applicationId,
        string $moduleType,
        ?int $moduleRefId,
        string $documentType
    ): ?DDocument {
        return DDocument::forGroup($applicationId, $moduleType, $moduleRefId, $documentType)
            ->active()
            ->first();
    }

    public function getDocumentSummaryForRef(
        int $applicationId,
        string $moduleType,
        int $moduleRefId,
        string $documentType
    ): array {
        $active = $this->getActiveVersion($applicationId, $moduleType, $moduleRefId, $documentType);
        $pending = $this->getPendingVersion($applicationId, $moduleType, $moduleRefId, $documentType);
        $groupKey = DocumentGroupKey::encode($applicationId, $moduleType, $moduleRefId, $documentType);

        return [
            'group_key' => $groupKey,
            'active' => $active,
            'pending' => $pending,
        ];
    }

    public function getPendingVersion(
        int $applicationId,
        string $moduleType,
        ?int $moduleRefId,
        string $documentType
    ): ?DDocument {
        return DDocument::forGroup($applicationId, $moduleType, $moduleRefId, $documentType)
            ->pending()
            ->orderByDesc('version_no')
            ->first();
    }

    public function getPendingVersionByGroupKey(string $groupKey): ?DDocument
    {
        $group = DocumentGroupKey::decode($groupKey);

        return $this->getPendingVersion(
            $group['application_id'],
            $group['module_type'],
            $group['module_ref_id'],
            $group['document_type']
        );
    }

    public function getVersionHistory(
        int $applicationId,
        string $moduleType,
        ?int $moduleRefId,
        string $documentType
    ): Collection {
        return DDocument::forGroup($applicationId, $moduleType, $moduleRefId, $documentType)
            ->orderByDesc('version_no')
            ->get();
    }

    public function getGroupSummary(string $groupKey): array
    {
        $group = DocumentGroupKey::decode($groupKey);
        $latest = DDocument::forGroup(
            $group['application_id'],
            $group['module_type'],
            $group['module_ref_id'],
            $group['document_type']
        )->orderByDesc('version_no')->first();

        if (!$latest) {
            return [];
        }

        return [
            'group_key' => $groupKey,
            'application_id' => $group['application_id'],
            'module_type' => $group['module_type'],
            'module_ref_id' => $group['module_ref_id'],
            'document_type' => $group['document_type'],
            'active_version' => $this->getActiveVersion(
                $group['application_id'],
                $group['module_type'],
                $group['module_ref_id'],
                $group['document_type']
            ),
            'pending_version' => $this->getPendingVersion(
                $group['application_id'],
                $group['module_type'],
                $group['module_ref_id'],
                $group['document_type']
            ),
            'latest_version' => $latest,
        ];
    }

    public function listDocumentsForApplication(int $applicationId): Collection
    {
        $groups = DDocument::forApplication($applicationId)
            ->select('application_id', 'module_type', 'module_ref_id', 'document_type')
            ->distinct()
            ->get();

        return $groups->map(function ($row) {
            $key = DocumentGroupKey::encode(
                $row->application_id,
                $row->module_type,
                $row->module_ref_id,
                $row->document_type
            );

            return $this->getGroupSummary($key);
        });
    }

    public function findVersionById(int $versionId): DDocument
    {
        return DDocument::findOrFail($versionId);
    }

    /**
     * Copy initial temp-stored documents into permanent storage on submit.
     * Alteration uploads stay in temp/ until a supervisor approves them.
     *
     * @return int Number of documents promoted
     */
    public function promoteApplicationTempDocumentsToPermanent(int $applicationId): int
    {
        $documents = DDocument::forApplication($applicationId)
            ->where('storage_type', DocumentStorageType::TEMP->value)
            ->get();

        $promoted = 0;

        foreach ($documents as $document) {
            if ($document->request_type === DocumentRequestType::ALTERATION) {
                continue;
            }

            if (!$this->storageService->isTempPath($document->file_path)) {
                continue;
            }

            $permanentPath = $this->storageService->promoteToPermanent($document->file_path);

            $document->update([
                'file_path' => $permanentPath,
                'storage_type' => DocumentStorageType::PERMANENT,
            ]);

            $promoted++;
        }

        return $promoted;
    }

    public function recordCarriedForwardDocument(
        DApplication $application,
        string $moduleType,
        int $moduleRefId,
        string $documentType,
        string $workflowStage
    ): ?DDocument {
        $applicationType = DocumentApplicationType::fromWorkflowStage($workflowStage);

        if (!$applicationType->isFollowUp()) {
            return null;
        }

        if (DDocument::forGroup($application->id, $moduleType, $moduleRefId, $documentType)->exists()) {
            return null;
        }

        $masterApplication = app(DocumentApplicationService::class)->masterApplication($application);
        $filePath = $this->masterTableService->resolveFilePath(
            $masterApplication->id,
            $moduleType,
            $moduleRefId,
            $documentType
        );

        if (!$filePath) {
            return null;
        }

        return DDocument::create([
            'application_id' => $application->id,
            'parent_application_id' => $application->parent_application_id,
            'module_type' => $moduleType,
            'module_ref_id' => $moduleRefId,
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
            'remarks' => 'Unchanged document carried forward from parent application.',
        ]);
    }

    public function ensureCarriedForwardDocuments(DApplication $application, string $workflowStage): int
    {
        $masterApplication = app(DocumentApplicationService::class)->masterApplication($application);
        $masterApplication->loadMissing(['educations', 'experiences']);
        $recorded = 0;

        foreach ($masterApplication->educations as $education) {
            if ($this->recordCarriedForwardDocument(
                $application,
                'education',
                $education->id,
                'certificate',
                $workflowStage
            )) {
                $recorded++;
            }
        }

        foreach ($masterApplication->experiences as $experience) {
            if ($this->recordCarriedForwardDocument(
                $application,
                'experience',
                $experience->id,
                'experience_doc',
                $workflowStage
            )) {
                $recorded++;
            }
        }

        return $recorded;
    }

    protected function storeVersion(
        UploadedFile $file,
        int $applicationId,
        string $moduleType,
        string $documentType,
        ?int $moduleRefId,
        int $versionNo,
        DocumentRequestType $requestType,
        ?string $remarks,
        ?string $workflowStage = null
    ): DDocument {
        $application = DApplication::findOrFail($applicationId);
        $applicationType = DocumentApplicationType::fromWorkflowStage($workflowStage);
        $masterApplication = app(DocumentApplicationService::class)->masterApplication($application);
        $oldFilePath = $this->masterTableService->resolveFilePath(
            $masterApplication->id,
            $moduleType,
            $moduleRefId,
            $documentType
        );

        $stored = $this->storageService->store(
            $file,
            $application->application_no,
            $applicationId,
            $moduleType,
            $documentType,
            $requestType,
            $workflowStage
        );

        $document = DDocument::create([
            'application_id' => $applicationId,
            'parent_application_id' => $application->parent_application_id,
            'module_type' => $moduleType,
            'module_ref_id' => $moduleRefId,
            'document_type' => $documentType,
            'file_name' => $stored['file_name'],
            'file_path' => $stored['file_path'],
            'old_file_path' => $oldFilePath,
            'storage_type' => DocumentStorageType::PERMANENT,
            'request_type' => $requestType,
            'application_type' => $applicationType,
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

    protected function requiresStaffApproval(?string $workflowStage): bool
    {
        $stage = strtoupper(trim((string) $workflowStage));

        return in_array($stage, ['RENEWAL', 'ALTERATION'], true);
    }

    protected function finalizeApprovedVersion(DDocument $document): DDocument
    {
        DDocument::forGroup(
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

    protected function resolveParentMasterFilePath(
        DApplication $application,
        string $moduleType,
        int $moduleRefId,
        string $documentType
    ): ?string {
        if (!$application->parent_application_id) {
            return null;
        }

        $parent = DApplication::find($application->parent_application_id);
        if (!$parent) {
            return null;
        }

        $parentRefId = app(DocumentApplicationService::class)->resolveParentModuleRefId(
            $application->id,
            $parent->id,
            $moduleType,
            $moduleRefId
        );

        return $this->masterTableService->resolveFilePath(
            $parent->id,
            $moduleType,
            $parentRefId,
            $documentType
        );
    }

    public function makeGroupKey(DDocument $document): string
    {
        return DocumentGroupKey::encode(
            $document->application_id,
            $document->module_type,
            $document->module_ref_id,
            $document->document_type
        );
    }

    protected function groupExists(
        int $applicationId,
        string $moduleType,
        ?int $moduleRefId,
        string $documentType
    ): bool {
        return DDocument::forGroup($applicationId, $moduleType, $moduleRefId, $documentType)->exists();
    }

    protected function getMaxVersionNo(
        int $applicationId,
        string $moduleType,
        ?int $moduleRefId,
        string $documentType
    ): int {
        return (int) DDocument::forGroup($applicationId, $moduleType, $moduleRefId, $documentType)
            ->max('version_no');
    }
}
