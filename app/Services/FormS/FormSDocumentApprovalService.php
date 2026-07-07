<?php

namespace App\Services\FormS;

use App\Enums\DocumentStorageType;
use App\Enums\DocumentVersionStatus;
use App\Models\DocumentsLog;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FormSDocumentApprovalService
{
    public function __construct(
        protected FormSDocumentMasterTableService $masterTableService
    ) {}

    public function approve(int $logId, ?int $userId = null, ?string $remarks = null): DocumentsLog
    {
        return DB::transaction(function () use ($logId, $userId, $remarks) {
            $version = DocumentsLog::lockForUpdate()->findOrFail($logId);

            if (!$version->isPending()) {
                throw new RuntimeException('Only pending documents can be approved.');
            }

            DocumentsLog::forGroup(
                $version->application_id,
                $version->module_type,
                $version->module_ref_id,
                $version->document_type
            )->active()->update(['is_active' => false]);

            $version->update([
                'status' => DocumentVersionStatus::APPROVED,
                'is_active' => true,
                'storage_type' => DocumentStorageType::PERMANENT,
                'approved_by' => $userId,
                'approved_at' => now(),
                'remarks' => $this->appendRemark($version->remarks, 'Approved', $remarks),
            ]);

            $this->masterTableService->syncApprovedFilePath($version->fresh());

            return $version->fresh();
        });
    }

    public function reject(int $logId, ?int $userId = null, ?string $remarks = null): DocumentsLog
    {
        return DB::transaction(function () use ($logId, $userId, $remarks) {
            $version = DocumentsLog::lockForUpdate()->findOrFail($logId);

            if (!$version->isPending()) {
                throw new RuntimeException('Only pending documents can be rejected.');
            }

            $version->update([
                'status' => DocumentVersionStatus::REJECTED,
                'is_active' => false,
                'approved_by' => $userId,
                'approved_at' => now(),
                'remarks' => $this->appendRemark($version->remarks, 'Rejected', $remarks),
            ]);

            return $version->fresh();
        });
    }

    protected function appendRemark(?string $existing, string $action, ?string $extra): ?string
    {
        $line = $action . ($extra ? ': ' . trim($extra) : '');

        return $existing ? trim($existing) . "\n" . $line : $line;
    }
}
