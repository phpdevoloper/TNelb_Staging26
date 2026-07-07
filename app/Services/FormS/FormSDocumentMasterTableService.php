<?php

namespace App\Services\FormS;

use App\Models\DocumentsLog;
use App\Models\Mst_education;
use App\Models\Mst_experience;
use App\Models\Mst_Form_s_w;
use App\Models\TnelbApplicantPhoto;
use App\Models\TnelbApplicantsSign;

class FormSDocumentMasterTableService
{
    public function __construct(
        protected FormSApplicationWorkflowService $workflowService
    ) {}

    public function resolveFilePath(
        Mst_Form_s_w $masterApplication,
        string $moduleType,
        ?int $moduleRefId,
        string $documentType
    ): ?string {
        if ($moduleRefId === null) {
            return null;
        }

        if ($moduleType === 'education') {
            $path = Mst_education::where('application_id', $masterApplication->application_id)
                ->whereKey($moduleRefId)
                ->value('upload_document');

            if ($path) {
                return $path;
            }
        }

        if ($moduleType === 'experience') {
            $path = Mst_experience::where('application_id', $masterApplication->application_id)
                ->whereKey($moduleRefId)
                ->value('support_document');

            if ($path) {
                return $path;
            }
        }

        if ($moduleType === 'photo') {
            $path = TnelbApplicantPhoto::where('application_id', $masterApplication->application_id)
                ->value('upload_path');

            if ($path) {
                return $path;
            }
        }

        if ($moduleType === 'signature') {
            $path = TnelbApplicantsSign::where('application_id', $masterApplication->application_id)
                ->value('uploaded_doc');

            if ($path) {
                return $path;
            }
        }

        return null;
    }

    public function syncApprovedFilePath(DocumentsLog $document): void
    {
        if ($document->module_ref_id === null) {
            return;
        }

        $masterApp = $this->resolveMasterApplicationForDocument($document);
        if (!$masterApp) {
            return;
        }

        $this->updateMasterFilePath(
            $masterApp,
            $document->module_type,
            $document->module_ref_id,
            $document->file_path
        );
    }

    protected function resolveMasterApplicationForDocument(DocumentsLog $document): ?Mst_Form_s_w
    {
        $workflowApp = Mst_Form_s_w::find($document->application_id);
        if (!$workflowApp) {
            return null;
        }

        if ($document->parent_application_id) {
            $parent = Mst_Form_s_w::find($document->parent_application_id);
            if ($parent) {
                return $parent;
            }
        }

        $refId = (int) $document->module_ref_id;
        if ($document->module_type === 'experience' && $refId > 0) {
            $onChild = Mst_experience::where('application_id', $workflowApp->application_id)
                ->whereKey($refId)
                ->exists();
            if ($onChild) {
                return $workflowApp;
            }
        }

        if ($document->module_type === 'education' && $refId > 0) {
            $onChild = Mst_education::where('application_id', $workflowApp->application_id)
                ->whereKey($refId)
                ->exists();
            if ($onChild) {
                return $workflowApp;
            }
        }

        return $this->workflowService->masterApplication($workflowApp);
    }

    protected function updateMasterFilePath(
        Mst_Form_s_w $masterApplication,
        string $moduleType,
        int $moduleRefId,
        string $filePath
    ): void {
        if ($moduleType === 'education') {
            Mst_education::where('application_id', $masterApplication->application_id)
                ->whereKey($moduleRefId)
                ->update(['upload_document' => $filePath]);

            return;
        }

        if ($moduleType === 'experience') {
            Mst_experience::where('application_id', $masterApplication->application_id)
                ->whereKey($moduleRefId)
                ->update(['support_document' => $filePath]);

            return;
        }

        if ($moduleType === 'photo') {
            TnelbApplicantPhoto::updateOrCreate(
                ['application_id' => $masterApplication->application_id],
                [
                    'login_id' => $masterApplication->login_id,
                    'upload_path' => $filePath,
                ]
            );

            return;
        }

        if ($moduleType === 'signature') {
            TnelbApplicantsSign::updateOrCreate(
                ['application_id' => $masterApplication->application_id],
                [
                    'login_id' => $masterApplication->login_id,
                    'uploaded_doc' => $filePath,
                ]
            );
        }
    }
}
