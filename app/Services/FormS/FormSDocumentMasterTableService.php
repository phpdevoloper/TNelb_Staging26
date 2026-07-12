<?php

namespace App\Services\FormS;

use App\Models\CC_Doc_Log;
use App\Models\CC_Education;
use App\Models\CC_Experience;
use App\Models\CC_Forms_Meta;
use App\Models\CC_Proof_doc;

class FormSDocumentMasterTableService
{
    public function __construct(
        protected FormSApplicationWorkflowService $workflowService
    ) {}

    public function resolveFilePath(
        CC_Forms_Meta $masterApplication,
        string $moduleType,
        ?int $moduleRefId,
        string $documentType
    ): ?string {
        if ($moduleRefId === null) {
            return null;
        }

        if ($moduleType === 'education') {
            $path = CC_Education::where('application_id', $masterApplication->application_id)
                ->whereKey($moduleRefId)
                ->value('upload_document');

            if ($path) {
                return $path;
            }
        }

        if ($moduleType === 'experience') {
            $column = $documentType === 'relieving_doc' ? 'relieve_document' : 'support_document';
            $path = CC_Experience::where('application_id', $masterApplication->application_id)
                ->whereKey($moduleRefId)
                ->value($column);

            if ($path) {
                return $path;
            }
        }

        if (in_array($moduleType, ['photo', 'signature', 'aadhaar', 'pan'], true)) {
            $path = CC_Proof_doc::where('application_id', $masterApplication->application_id)
                ->whereKey($moduleRefId)
                ->value('proof_doc');

            if ($path) {
                return $path;
            }
        }

        if ($moduleType === 'alteration') {
            $path = CC_Proof_doc::whereKey($moduleRefId)->value('proof_doc');

            if ($path) {
                return $path;
            }
        }

        return null;
    }

    public function syncApprovedFilePath(CC_Doc_Log $document): void
    {
        if ($document->module_ref_id === null) {
            return;
        }

        if ($document->module_type === 'alteration') {
            CC_Proof_doc::whereKey((int) $document->module_ref_id)->update([
                'proof_doc' => $document->file_path,
                'updated_at' => now()->toDateString(),
            ]);

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
            $document->file_path,
            (string) ($document->document_type ?? '')
        );
    }

    protected function resolveMasterApplicationForDocument(CC_Doc_Log $document): ?CC_Forms_Meta
    {
        $workflowApp = $this->workflowService->findWorkflowByPk((int) $document->application_id);
        if (!$workflowApp) {
            return null;
        }

        if ($document->parent_application_id) {
            $parent = $this->workflowService->findWorkflowByPk((int) $document->parent_application_id);
            if ($parent) {
                return $parent;
            }
        }

        $refId = (int) $document->module_ref_id;
        if ($document->module_type === 'experience' && $refId > 0) {
            $onChild = CC_Experience::where('application_id', $workflowApp->application_id)
                ->whereKey($refId)
                ->exists();
            if ($onChild) {
                return $workflowApp;
            }
        }

        if ($document->module_type === 'education' && $refId > 0) {
            $onChild = CC_Education::where('application_id', $workflowApp->application_id)
                ->whereKey($refId)
                ->exists();
            if ($onChild) {
                return $workflowApp;
            }
        }

        if (in_array($document->module_type, ['photo', 'signature', 'aadhaar', 'pan'], true) && $refId > 0) {
            $onChild = CC_Proof_doc::where('application_id', $workflowApp->application_id)
                ->whereKey($refId)
                ->exists();
            if ($onChild) {
                return $workflowApp;
            }
        }

        if ($document->module_type === 'alteration' && $refId > 0) {
            $proof = CC_Proof_doc::whereKey($refId)->first();
            if ($proof && (string) $proof->application_id === (string) $workflowApp->application_id) {
                return $workflowApp;
            }
        }

        return $this->workflowService->masterApplication($workflowApp);
    }

    protected function updateMasterFilePath(
        CC_Forms_Meta $masterApplication,
        string $moduleType,
        int $moduleRefId,
        string $filePath,
        string $documentType = ''
    ): void {
        if ($moduleType === 'education') {
            CC_Education::where('application_id', $masterApplication->application_id)
                ->whereKey($moduleRefId)
                ->update(['upload_document' => $filePath]);

            return;
        }

        if ($moduleType === 'experience') {
            $column = $documentType === 'relieving_doc' ? 'relieve_document' : 'support_document';
            CC_Experience::where('application_id', $masterApplication->application_id)
                ->whereKey($moduleRefId)
                ->update([$column => $filePath]);

            return;
        }

        if (in_array($moduleType, ['photo', 'signature', 'aadhaar', 'pan'], true)) {
            CC_Proof_doc::where('application_id', $masterApplication->application_id)
                ->whereKey($moduleRefId)
                ->update([
                    'proof_doc' => $filePath,
                    'updated_at' => now()->toDateString(),
                ]);

            return;
        }

        if ($moduleType === 'alteration') {
            CC_Proof_doc::whereKey($moduleRefId)->update([
                'proof_doc' => $filePath,
                'updated_at' => now()->toDateString(),
            ]);
        }
    }
}
