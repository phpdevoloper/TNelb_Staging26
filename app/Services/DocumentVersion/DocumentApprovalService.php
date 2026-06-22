<?php

namespace App\Services\DocumentVersion;

use App\Enums\DocumentRequestType;
use App\Enums\DocumentStorageType;
use App\Enums\DocumentVersionStatus;
use App\Models\DDocument;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DocumentApprovalService
{
    public function __construct(
        protected DocumentVersionService $versionService,
        protected DocumentStorageService $storageService,
        protected DocumentMasterTableService $masterTableService
    ) {}

    public function approve(
        int $versionId,
        int $level,
        ?int $userId = null,
        ?string $remarks = null
    ): DDocument {
        return DB::transaction(function () use ($versionId, $level, $userId, $remarks) {
            $version = DDocument::lockForUpdate()->findOrFail($versionId);

            $this->assertPendingAtLevel($version, $level);

            $maxLevel = $this->getMaxApprovalLevel();

            if ($level >= $maxLevel) {
                DDocument::forGroup(
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
                    'remarks' => $this->appendActionRemark($version->remarks, "Approved at level {$level}", $remarks),
                ]);

                $this->masterTableService->syncApprovedFilePath($version->fresh());
            } else {
                $nextStatus = $version->status->nextPendingStatus($maxLevel);

                $version->update([
                    'status' => $nextStatus ?? DocumentVersionStatus::APPROVED,
                    'remarks' => $this->appendActionRemark($version->remarks, "Approved at level {$level}", $remarks),
                ]);
            }

            return $version->fresh();
        });
    }

    public function reject(
        int $versionId,
        int $level,
        ?int $userId = null,
        ?string $remarks = null
    ): DDocument {
        return DB::transaction(function () use ($versionId, $level, $userId, $remarks) {
            $version = DDocument::lockForUpdate()->findOrFail($versionId);

            $this->assertPendingAtLevel($version, $level);

            $version->update([
                'status' => DocumentVersionStatus::REJECTED,
                'is_active' => false,
                'remarks' => $this->appendActionRemark($version->remarks, "Rejected at level {$level}", $remarks),
            ]);

            return $version->fresh();
        });
    }

    public function getApprovalLevels(): array
    {
        return config('document_versioning.approval_levels', []);
    }

    public function getMaxApprovalLevel(): int
    {
        $levels = array_keys($this->getApprovalLevels());

        return empty($levels) ? 1 : (int) max($levels);
    }

    public function getStepperState(DDocument $pendingVersion): array
    {
        $levels = $this->getApprovalLevels();
        $currentLevel = $pendingVersion->currentApprovalLevel();
        $steps = [];

        foreach ($levels as $level => $config) {
            if ($pendingVersion->isRejected() && $currentLevel === null) {
                $rejectedLevel = $this->inferRejectedLevel($pendingVersion);
                if ($level === $rejectedLevel) {
                    $state = 'rejected';
                } elseif ($level < $rejectedLevel) {
                    $state = 'completed';
                } else {
                    $state = 'upcoming';
                }
            } elseif ($level < ($currentLevel ?? 0)) {
                $state = 'completed';
            } elseif ($pendingVersion->isPending() && $currentLevel === $level) {
                $state = 'current';
            } else {
                $state = 'upcoming';
            }

            $steps[] = [
                'level' => $level,
                'label' => $config['label'],
                'role' => $config['role'],
                'state' => $state,
            ];
        }

        return $steps;
    }

    protected function assertPendingAtLevel(DDocument $version, int $level): void
    {
        if (!$version->isPending()) {
            throw new RuntimeException('Only pending document versions can be approved or rejected.');
        }

        $currentLevel = $version->currentApprovalLevel();

        if ($currentLevel !== $level) {
            throw new RuntimeException(
                "This document is awaiting approval at level {$currentLevel}, not level {$level}."
            );
        }
    }

    protected function appendActionRemark(?string $existing, string $action, ?string $note): ?string
    {
        $line = '[' . now()->format('Y-m-d H:i') . "] {$action}";
        if ($note) {
            $line .= ": {$note}";
        }

        return trim(($existing ? $existing . "\n" : '') . $line);
    }

    protected function inferRejectedLevel(DDocument $version): int
    {
        if ($version->remarks && preg_match('/Rejected at level (\d+)/', $version->remarks, $matches)) {
            return (int) $matches[1];
        }

        return 1;
    }
}
