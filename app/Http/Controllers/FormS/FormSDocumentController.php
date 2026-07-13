<?php

namespace App\Http\Controllers\FormS;

use App\Http\Controllers\Controller;
use App\Models\CC_Doc_Log;
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
            return $this->storageService->download($ccLog->file_path, $ccLog->file_name);
        }

        $log = DocumentsLog::findOrFail($logId);

        return $this->storageService->download($log->file_path, $log->file_name);
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
