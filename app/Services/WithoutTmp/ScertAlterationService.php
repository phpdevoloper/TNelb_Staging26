<?php

namespace App\Services\WithoutTmp;

use App\Enums\ScertAlterationStatus;
use App\Enums\ScertAppStatus;
use App\Models\CAlterationRequest;
use App\Models\CExperience;
use App\Models\CPhoto;
use App\Models\CScertDocument;
use App\Models\CSignature;
use App\Models\CEducation;
use App\Models\ScertApp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ScertAlterationService
{
    public function __construct(
        protected WithoutTmpStorageService $storage
    ) {}

    public function resolveTarget(string $targetTable, int $targetRowId, int $applicationId): object
    {
        $model = match ($targetTable) {
            'c_education' => CEducation::class,
            'c_experience' => CExperience::class,
            'c_photo' => CPhoto::class,
            'c_signature' => CSignature::class,
            'c_documents' => CScertDocument::class,
            default => throw new RuntimeException('Invalid alteration target.'),
        };

        return $model::where('application_id', $applicationId)->findOrFail($targetRowId);
    }

    public function requestAlteration(
        ScertApp $application,
        string $targetTable,
        int $targetRowId,
        UploadedFile $file,
        string $reason
    ): CAlterationRequest {
        if (!$application->canRequestAlteration()) {
            throw new RuntimeException('Alteration is only allowed for submitted or digitization applications.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new RuntimeException('Alteration reason is required.');
        }

        $row = $this->resolveTarget($targetTable, $targetRowId, $application->id);

        if (!($row->file_path ?? null)) {
            throw new RuntimeException('No existing file to replace for this record.');
        }

        $uploadType = config('without_tmp.target_tables.' . $targetTable);
        if (!$uploadType) {
            throw new RuntimeException('Upload type not configured for target.');
        }

        $pendingExists = CAlterationRequest::query()
            ->where('target_table', $targetTable)
            ->where('target_row_id', $targetRowId)
            ->where('status', ScertAlterationStatus::PENDING)
            ->exists();

        if ($pendingExists) {
            throw new RuntimeException('A pending alteration already exists for this record.');
        }

        return DB::transaction(function () use ($application, $targetTable, $targetRowId, $file, $reason, $row, $uploadType) {
            $stored = $this->storage->storeUpload($file, $application, $uploadType);

            $request = CAlterationRequest::create([
                'application_id' => $application->id,
                'target_table' => $targetTable,
                'target_row_id' => $targetRowId,
                'upload_type' => $uploadType,
                'old_file_name' => $row->file_name,
                'old_file_path' => $row->file_path,
                'new_file_name' => $stored['file_name'],
                'new_file_path' => $stored['file_path'],
                'reason' => $reason,
                'status' => ScertAlterationStatus::PENDING,
            ]);

            if ($application->status !== ScertAppStatus::ALTERATION) {
                $application->update(['status' => ScertAppStatus::ALTERATION]);
            }

            return $request;
        });
    }

    public function approve(CAlterationRequest $request, ?string $remarks = null): CAlterationRequest
    {
        if (!$request->isPending()) {
            throw new RuntimeException('Only pending alteration requests can be approved.');
        }

        return DB::transaction(function () use ($request, $remarks) {
            $row = $this->resolveTarget(
                $request->target_table,
                $request->target_row_id,
                $request->application_id
            );

            $this->storage->deleteFile($row->file_path);

            $row->update([
                'file_name' => $request->new_file_name,
                'file_path' => $request->new_file_path,
                'is_active' => true,
            ]);

            $request->update([
                'status' => ScertAlterationStatus::APPROVED,
                'review_remarks' => $remarks,
                'reviewed_at' => now(),
            ]);

            $this->syncApplicationStatusAfterReview($request->application_id);

            return $request->fresh();
        });
    }

    public function reject(CAlterationRequest $request, ?string $remarks = null): CAlterationRequest
    {
        if (!$request->isPending()) {
            throw new RuntimeException('Only pending alteration requests can be rejected.');
        }

        return DB::transaction(function () use ($request, $remarks) {
            $this->storage->deleteFile($request->new_file_path);

            $request->update([
                'status' => ScertAlterationStatus::REJECTED,
                'review_remarks' => $remarks,
                'reviewed_at' => now(),
            ]);

            $this->syncApplicationStatusAfterReview($request->application_id);

            return $request->fresh();
        });
    }

    protected function syncApplicationStatusAfterReview(int $applicationId): void
    {
        $hasPending = CAlterationRequest::query()
            ->where('application_id', $applicationId)
            ->where('status', ScertAlterationStatus::PENDING)
            ->exists();

        if (!$hasPending) {
            ScertApp::whereKey($applicationId)
                ->where('status', ScertAppStatus::ALTERATION)
                ->update(['status' => ScertAppStatus::SUBMITTED]);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAlterableItems(ScertApp $application): array
    {
        $items = [];

        foreach ($application->educations()->where('is_active', true)->get() as $row) {
            if ($row->file_path && !$this->hasPendingFor('c_education', $row->id)) {
                $items[] = $this->formatAlterableItem('c_education', $row, 'EDU', 'Education: ' . $row->education_level);
            }
        }

        foreach ($application->experiences()->where('is_active', true)->get() as $row) {
            if ($row->file_path && !$this->hasPendingFor('c_experience', $row->id)) {
                $items[] = $this->formatAlterableItem('c_experience', $row, 'EXP', 'Experience: ' . $row->company_name);
            }
        }

        if ($application->photo?->file_path && !$this->hasPendingFor('c_photo', $application->photo->id)) {
            $items[] = $this->formatAlterableItem('c_photo', $application->photo, 'PHOTO', 'Photo');
        }

        if ($application->signature?->file_path && !$this->hasPendingFor('c_signature', $application->signature->id)) {
            $items[] = $this->formatAlterableItem('c_signature', $application->signature, 'SIGN', 'Signature');
        }

        foreach ($application->documents()->where('is_active', true)->get() as $row) {
            if ($row->file_path && !$this->hasPendingFor('c_documents', $row->id)) {
                $items[] = $this->formatAlterableItem('c_documents', $row, 'DOC', 'Document: ' . $row->document_label);
            }
        }

        return $items;
    }

    protected function hasPendingFor(string $table, int $rowId): bool
    {
        return CAlterationRequest::query()
            ->where('target_table', $table)
            ->where('target_row_id', $rowId)
            ->where('status', ScertAlterationStatus::PENDING)
            ->exists();
    }

    protected function formatAlterableItem(string $table, object $row, string $type, string $label): array
    {
        return [
            'target_table' => $table,
            'target_row_id' => $row->id,
            'upload_type' => $type,
            'label' => $label,
            'file_name' => $row->file_name,
            'file_path' => $row->file_path,
            'target_key' => base64_encode($table . ':' . $row->id),
        ];
    }

    public function decodeTargetKey(string $targetKey): array
    {
        $decoded = base64_decode($targetKey, true);
        if (!$decoded || !str_contains($decoded, ':')) {
            throw new RuntimeException('Invalid alteration target.');
        }

        [$table, $id] = explode(':', $decoded, 2);

        return ['target_table' => $table, 'target_row_id' => (int) $id];
    }

    public function uploadTypeLabel(string $uploadType): string
    {
        return config('without_tmp.upload_types.' . $uploadType . '.label', $uploadType);
    }

    public function describeTarget(CAlterationRequest $alteration): string
    {
        $row = $this->resolveTarget(
            $alteration->target_table,
            $alteration->target_row_id,
            $alteration->application_id
        );

        return match ($alteration->target_table) {
            'c_education' => 'Education: ' . ($row->education_level ?? 'Record'),
            'c_experience' => 'Experience: ' . ($row->company_name ?? 'Record'),
            'c_photo' => 'Photo',
            'c_signature' => 'Signature',
            'c_documents' => 'Document: ' . ($row->document_label ?? 'Record'),
            default => $alteration->target_table . ' #' . $alteration->target_row_id,
        };
    }
}
