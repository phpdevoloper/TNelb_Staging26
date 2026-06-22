<?php

namespace App\Services\DocumentVersion;

use App\Models\DDocument;
use App\Models\DEducation;
use App\Models\DExperience;

class DocumentMasterTableService
{
    public function resolveFilePath(
        int $applicationId,
        string $moduleType,
        ?int $moduleRefId,
        string $documentType
    ): ?string {
        if ($moduleRefId === null) {
            return null;
        }

        if ($moduleType === 'education') {
            $path = DEducation::where('application_id', $applicationId)
                ->whereKey($moduleRefId)
                ->value('file_path');

            if ($path) {
                return $path;
            }
        }

        if ($moduleType === 'experience') {
            $path = DExperience::where('application_id', $applicationId)
                ->whereKey($moduleRefId)
                ->value('file_path');

            if ($path) {
                return $path;
            }
        }

        return DDocument::forGroup($applicationId, $moduleType, $moduleRefId, $documentType)
            ->active()
            ->value('file_path');
    }

    public function syncApprovedFilePath(DDocument $document): void
    {
        if ($document->module_ref_id === null) {
            return;
        }

        $masterApplicationId = $document->parent_application_id ?? $document->application_id;

        $this->updateMasterFilePath(
            $masterApplicationId,
            $document->module_type,
            $document->module_ref_id,
            $document->file_path
        );
    }

    protected function updateMasterFilePath(
        int $applicationId,
        string $moduleType,
        int $moduleRefId,
        string $filePath
    ): void {
        if ($moduleType === 'education') {
            DEducation::where('application_id', $applicationId)
                ->whereKey($moduleRefId)
                ->update(['file_path' => $filePath]);

            return;
        }

        if ($moduleType === 'experience') {
            DExperience::where('application_id', $applicationId)
                ->whereKey($moduleRefId)
                ->update(['file_path' => $filePath]);
        }
    }
}
