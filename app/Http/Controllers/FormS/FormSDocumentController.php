<?php

namespace App\Http\Controllers\FormS;

use App\Http\Controllers\Controller;
use App\Models\CC_Doc_Log;
use App\Models\CC_Proof_doc;
use App\Models\DocumentsLog;
use App\Services\DocumentVersion\DocumentStorageService;
use Symfony\Component\HttpFoundation\Response;

class FormSDocumentController extends Controller
{
    public function __construct(
        protected DocumentStorageService $storageService
    ) {}

    public function download(int $logId): Response
    {
        $ccLog = CC_Doc_Log::find($logId);
        if ($ccLog) {
            $filePath = $this->resolveDownloadPath($ccLog->file_path, $ccLog);
            if ($filePath !== null) {
                return $this->storageService->download($filePath, $ccLog->file_name ?: basename($filePath));
            }
        }

        $log = DocumentsLog::findOrFail($logId);
        $filePath = $this->resolveDownloadPath($log->file_path, null, $log);
        if ($filePath === null) {
            abort(404, 'Document file not found.');
        }

        return $this->storageService->download($filePath, $log->file_name ?: basename($filePath));
    }

    /**
     * cc_doc_log paths can drift from cc_proof_doc; photo/signature live in proof_doc.
     */
    protected function resolveDownloadPath(
        ?string $filePath,
        ?CC_Doc_Log $ccLog = null,
        ?DocumentsLog $legacyLog = null
    ): ?string {
        $filePath = trim(str_replace('\\', '/', (string) $filePath));

        if ($filePath !== '' && $this->storageService->exists($filePath)) {
            return $filePath;
        }

        if ($ccLog) {
            $proofPath = $this->proofDocPathForLog($ccLog);
            if ($proofPath !== null && $this->storageService->exists($proofPath)) {
                return $proofPath;
            }
        }

        if ($filePath !== '' && is_file(public_path($filePath))) {
            return $filePath;
        }

        return null;
    }

    protected function proofDocPathForLog(CC_Doc_Log $log): ?string
    {
        if (! in_array((string) $log->module_type, ['photo', 'signature', 'aadhaar', 'pan'], true)) {
            return null;
        }

        if ($log->module_ref_id) {
            $fromRef = CC_Proof_doc::whereKey((int) $log->module_ref_id)->value('proof_doc');
            if ($fromRef) {
                return trim(str_replace('\\', '/', (string) $fromRef));
            }
        }

        if ($log->file_path) {
            $fromPath = CC_Proof_doc::query()
                ->where('proof_doc', $log->file_path)
                ->value('proof_doc');

            if ($fromPath) {
                return trim(str_replace('\\', '/', (string) $fromPath));
            }
        }

        return null;
    }

    /**
     * Stream a competency file when DOCUMENT_SERVE_VIA_LARAVEL=true.
     * URL: /{DOCUMENT_PUBLIC_URL_PREFIX}/FORM_S/NEW/...
     * Disk: DOCUMENT_STORAGE_ROOT (e.g. {project}/competency)
     */
    public function viewByPath(string $filePath): Response
    {
        $filePath = trim(str_replace('\\', '/', $filePath));

        if ($filePath === '' || str_contains($filePath, '..')) {
            abort(404);
        }

        $ccLog = CC_Doc_Log::query()
            ->where('is_active', true)
            ->where(function ($query) use ($filePath) {
                $query->where('file_path', $filePath)
                    ->orWhere('old_file_path', $filePath);
            })
            ->orderByDesc('doc_id')
            ->first();

        if ($ccLog) {
            return $this->storageService->download($ccLog->file_path, $ccLog->file_name);
        }

        $log = DocumentsLog::query()
            ->where('is_active', true)
            ->where(function ($query) use ($filePath) {
                $query->where('file_path', $filePath)
                    ->orWhere('old_file_path', $filePath);
            })
            ->orderByDesc('id')
            ->first();

        if ($log) {
            return $this->storageService->download($log->file_path, $log->file_name);
        }

        if ($this->storageService->exists($filePath)) {
            return $this->storageService->download($filePath, basename($filePath));
        }

        if (is_file(public_path($filePath))) {
            return response()->file(public_path($filePath));
        }

        abort(404, 'Document file not found.');
    }
}
