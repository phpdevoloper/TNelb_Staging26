<?php

namespace App\Services\DocumentVersion;

use App\Enums\DocumentApplicationType;
use App\Enums\DocumentRequestType;
use App\Enums\DocumentStorageType;
use App\Enums\DocumentVersionStatus;
use App\Models\DApplication;
use App\Models\DDocument;
use App\Models\DEducation;
use App\Models\DExperience;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DocumentApplicationService
{
    public function __construct(
        protected DocumentStorageService $storageService
    ) {}

    public function isAlterationApplication(DApplication $application): bool
    {
        return str_starts_with(strtoupper($application->application_no), 'ALT-');
    }

    public function buildAlterationApplicationNo(DApplication $parent): string
    {
        $no = trim($parent->application_no);

        if (preg_match('/^APP[-_]?(.+)$/i', $no, $matches)) {
            return 'ALT-' . $matches[1];
        }

        return 'ALT-' . now()->format('YmdHis');
    }

    public function findAlterationApplication(DApplication $parent): ?DApplication
    {
        if ($this->isAlterationApplication($parent)) {
            return $parent;
        }

        return DApplication::query()
            ->where('parent_application_id', $parent->id)
            ->where('request_context', 'ALTERATION')
            ->orderByDesc('id')
            ->first();
    }

    public function isRenewalApplication(DApplication $application): bool
    {
        return str_starts_with(strtoupper($application->application_no), 'REN-');
    }

    public function isChildApplication(DApplication $application): bool
    {
        return $this->isAlterationApplication($application) || $this->isRenewalApplication($application);
    }

    /**
     * Master education/experience rows always belong to the parent APP application.
     */
    public function masterApplication(DApplication $application): DApplication
    {
        if ($application->parent_application_id && $this->isChildApplication($application)) {
            return DApplication::findOrFail($application->parent_application_id);
        }

        return $application;
    }

    public function buildRenewalApplicationNo(DApplication $parent): string
    {
        $no = trim($parent->application_no);

        if (preg_match('/^APP[-_]?(.+)$/i', $no, $matches)) {
            return 'REN-' . $matches[1];
        }

        return 'REN-' . now()->format('YmdHis');
    }

    public function findRenewalApplication(DApplication $parent): ?DApplication
    {
        if ($this->isRenewalApplication($parent)) {
            return $parent;
        }

        return DApplication::query()
            ->where('parent_application_id', $parent->id)
            ->where('request_context', 'RENEWAL')
            ->orderByDesc('id')
            ->first();
    }

    public function findOrCreateRenewalApplication(DApplication $parent): DApplication
    {
        if ($this->isRenewalApplication($parent)) {
            return $parent;
        }

        $existing = $this->findRenewalApplication($parent);

        if ($existing) {
            $this->realignChildApplicationToParentMaster($parent, $existing);
            $this->seedDocumentReferencesFromParent($parent, $existing, 'RENEWAL');

            return $existing->fresh(['educations', 'experiences']);
        }

        return DB::transaction(function () use ($parent) {
            $renewalNo = $this->buildRenewalApplicationNo($parent);

            if (DApplication::where('application_no', $renewalNo)->exists()) {
                throw new RuntimeException("Renewal application {$renewalNo} already exists but is not linked to this parent.");
            }

            $renewal = DApplication::create([
                'application_no' => $renewalNo,
                'applicant_name' => $parent->applicant_name,
                'status' => 'DRAFT',
                'parent_application_id' => $parent->id,
                'request_context' => 'RENEWAL',
            ]);

            $this->seedDocumentReferencesFromParent($parent, $renewal, 'RENEWAL');

            return $renewal->fresh(['educations', 'experiences']);
        });
    }

    public function findOrCreateAlterationApplication(DApplication $parent): DApplication
    {
        if ($this->isAlterationApplication($parent)) {
            return $parent;
        }

        $existing = $this->findAlterationApplication($parent);

        if ($existing) {
            $this->realignChildApplicationToParentMaster($parent, $existing);
            $this->seedDocumentReferencesFromParent($parent, $existing, 'ALTERATION');

            return $existing->fresh(['educations', 'experiences']);
        }

        return DB::transaction(function () use ($parent) {
            $altNo = $this->buildAlterationApplicationNo($parent);

            if (DApplication::where('application_no', $altNo)->exists()) {
                throw new RuntimeException("Alteration application {$altNo} already exists but is not linked to this parent.");
            }

            $alt = DApplication::create([
                'application_no' => $altNo,
                'applicant_name' => $parent->applicant_name,
                'status' => 'DRAFT',
                'parent_application_id' => $parent->id,
                'request_context' => 'ALTERATION',
            ]);

            $this->seedDocumentReferencesFromParent($parent, $alt, 'ALTERATION');

            return $alt->fresh(['educations', 'experiences']);
        });
    }

    /**
     * Child renewal/alteration apps reuse parent master rows — module_ref_id is the parent row id.
     */
    public function mapModuleRefId(
        DApplication $parent,
        DApplication $child,
        string $moduleType,
        ?int $parentRefId
    ): ?int {
        return $parentRefId;
    }

    public function resolveParentModuleRefId(
        int $childApplicationId,
        int $parentApplicationId,
        string $moduleType,
        int $moduleRefId
    ): int {
        if ($moduleType === 'education') {
            $parentRow = DEducation::where('application_id', $parentApplicationId)->find($moduleRefId);
            if ($parentRow) {
                return $parentRow->id;
            }

            $childRow = DEducation::where('application_id', $childApplicationId)->findOrFail($moduleRefId);
            $parentRow = DEducation::where('application_id', $parentApplicationId)
                ->where('education_level', $childRow->education_level)
                ->where('institution_name', $childRow->institution_name)
                ->where('certificate_no', $childRow->certificate_no)
                ->firstOrFail();

            return $parentRow->id;
        }

        if ($moduleType === 'experience') {
            $parentRow = DExperience::where('application_id', $parentApplicationId)->find($moduleRefId);
            if ($parentRow) {
                return $parentRow->id;
            }

            $childRow = DExperience::where('application_id', $childApplicationId)->findOrFail($moduleRefId);
            $parentRow = DExperience::where('application_id', $parentApplicationId)
                ->where('company_name', $childRow->company_name)
                ->where('designation', $childRow->designation)
                ->firstOrFail();

            return $parentRow->id;
        }

        return $moduleRefId;
    }

    /**
     * Remove duplicate master rows from legacy child apps and point d_documents at parent row ids.
     */
    public function realignChildApplicationToParentMaster(DApplication $parent, DApplication $child): void
    {
        if (!$this->isChildApplication($child)) {
            return;
        }

        $parent->loadMissing(['educations', 'experiences']);

        foreach (DEducation::where('application_id', $child->id)->get() as $childEducation) {
            $parentEducation = $parent->educations->first(function (DEducation $row) use ($childEducation) {
                return $row->education_level === $childEducation->education_level
                    && $row->institution_name === $childEducation->institution_name
                    && $row->certificate_no === $childEducation->certificate_no;
            });

            if ($parentEducation) {
                DDocument::query()
                    ->where('application_id', $child->id)
                    ->where('module_type', 'education')
                    ->where('module_ref_id', $childEducation->id)
                    ->update(['module_ref_id' => $parentEducation->id]);
            }
        }

        foreach (DExperience::where('application_id', $child->id)->get() as $childExperience) {
            $parentExperience = $parent->experiences->first(function (DExperience $row) use ($childExperience) {
                return $row->company_name === $childExperience->company_name
                    && $row->designation === $childExperience->designation;
            });

            if ($parentExperience) {
                DDocument::query()
                    ->where('application_id', $child->id)
                    ->where('module_type', 'experience')
                    ->where('module_ref_id', $childExperience->id)
                    ->update(['module_ref_id' => $parentExperience->id]);
            }
        }

        DEducation::where('application_id', $child->id)->delete();
        DExperience::where('application_id', $child->id)->delete();
    }

    /**
     * Create audit records for unchanged parent documents (no physical file copy).
     */
    public function seedDocumentReferencesFromParent(
        DApplication $parent,
        DApplication $child,
        string $workflowStage
    ): void {
        $parent->loadMissing(['educations', 'experiences']);
        $applicationType = DocumentApplicationType::fromWorkflowStage($workflowStage);

        foreach ($parent->educations as $parentEducation) {
            $filePath = $parentEducation->file_path
                ?: DDocument::forGroup($parent->id, 'education', $parentEducation->id, 'certificate')
                    ->active()
                    ->value('file_path');

            if (!$filePath) {
                continue;
            }

            if (DDocument::forGroup($child->id, 'education', $parentEducation->id, 'certificate')->exists()) {
                continue;
            }

            $this->seedDocumentReference(
                $parent,
                $child,
                'education',
                $parentEducation->id,
                'certificate',
                $filePath,
                $applicationType
            );
        }

        foreach ($parent->experiences as $parentExperience) {
            $filePath = $parentExperience->file_path
                ?: DDocument::forGroup($parent->id, 'experience', $parentExperience->id, 'experience_doc')
                    ->active()
                    ->value('file_path');

            if (!$filePath) {
                continue;
            }

            if (DDocument::forGroup($child->id, 'experience', $parentExperience->id, 'experience_doc')->exists()) {
                continue;
            }

            $this->seedDocumentReference(
                $parent,
                $child,
                'experience',
                $parentExperience->id,
                'experience_doc',
                $filePath,
                $applicationType
            );
        }
    }

    protected function seedDocumentReference(
        DApplication $parent,
        DApplication $child,
        string $moduleType,
        int $masterModuleRefId,
        string $documentType,
        string $filePath,
        DocumentApplicationType $applicationType
    ): void {
        DDocument::create([
            'application_id' => $child->id,
            'parent_application_id' => $parent->id,
            'module_type' => $moduleType,
            'module_ref_id' => $masterModuleRefId,
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
            'remarks' => 'Carried forward from parent application ' . $parent->application_no,
        ]);
    }

    /** @deprecated Use seedDocumentReferencesFromParent() — kept for backward compatibility in tests */
    public function copyParentDocumentsToAlteration(DApplication $parent, DApplication $alt): void
    {
        $this->seedDocumentReferencesFromParent($parent, $alt, 'ALTERATION');
    }
}
