<?php

namespace App\Http\Controllers\FormS;

use App\Http\Controllers\Controller;
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
        $log = DocumentsLog::findOrFail($logId);

        return $this->storageService->download($log->file_path, $log->file_name);
    }
}
