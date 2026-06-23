<?php

namespace App\Services\DocumentVersion;

use App\Enums\DocumentRequestType;
use App\Models\DDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class DocumentStorageService
{
    public function disk(): string
    {
        return config('document_versioning.disk', 'private_documents');
    }

    public function buildRelativePath(
        DocumentRequestType $requestType,
        string $applicationNo,
        string $moduleType,
        string $documentType,
        int $sequenceNo,
        ?string $workflowStage,
        string $extension
    ): string {
        $mainFolder = $this->certificateFolder($applicationNo);
        $subFolder = $this->requestFolder($requestType, $workflowStage);
        $leafFolder = $this->leafFolder($moduleType, $documentType);
        $fileName = $this->buildFileName($applicationNo, $documentType, $sequenceNo, $extension);

        return sprintf('%s/%s/%s/%s', $mainFolder, $subFolder, $leafFolder, $fileName);
    }

    public function buildFileName(
        string $applicationNo,
        string $documentType,
        int $sequenceNo,
        string $extension
    ): string {
        $datePrefix = now()->format('ymd');
        $safeAppNo = strtoupper((string) preg_replace('/[^a-zA-Z0-9]/', '', $applicationNo));
        $typeCode = $this->documentTypeCode($documentType);
        $sequence = str_pad((string) max(1, $sequenceNo), 3, '0', STR_PAD_LEFT);
        $ext = ltrim(strtolower($extension), '.');

        return sprintf('%s_%s_%s_%s.%s', $datePrefix, $safeAppNo, $typeCode, $sequence, $ext);
    }

    public function nextSequenceNo(int $applicationId, string $documentType): int
    {
        $typeCode = $this->documentTypeCode($documentType);
        $pattern = '/_' . preg_quote($typeCode, '/') . '_(\d{3})\./';
        $max = 0;

        foreach (
            DDocument::forApplication($applicationId)
                ->where('document_type', $documentType)
                ->pluck('file_name') as $fileName
        ) {
            if (preg_match($pattern, $fileName, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $max + 1;
    }

    public function store(
        UploadedFile $file,
        string $applicationNo,
        int $applicationId,
        string $moduleType,
        string $documentType,
        DocumentRequestType $requestType,
        ?string $workflowStage = null
    ): array {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $sequenceNo = $this->nextSequenceNo($applicationId, $documentType);
        $relativePath = $this->buildRelativePath(
            $requestType,
            $applicationNo,
            $moduleType,
            $documentType,
            $sequenceNo,
            $workflowStage,
            $extension
        );
        $disk = Storage::disk($this->disk());

        if ($disk->exists($relativePath)) {
            $disk->delete($relativePath);
        }

        $directory = dirname($relativePath);
        if ($directory !== '.' && !$disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $disk->putFileAs(
            $directory === '.' ? '' : $directory,
            $file,
            basename($relativePath)
        );

        return [
            'file_name' => basename($relativePath),
            'file_path' => $relativePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'original_file_name' => $file->getClientOriginalName(),
        ];
    }

    public function promoteToPermanent(string $tempRelativePath): string
    {
        return $tempRelativePath;
    }

    /**
     * Overwrite an existing permanent file with a pending temp file (alteration approval).
     */
    public function replacePermanentFromTemp(string $tempRelativePath): string
    {
        return $tempRelativePath;
    }

    public function deleteTempFile(string $tempRelativePath): void
    {
        $disk = Storage::disk($this->disk());

        if ($disk->exists($tempRelativePath)) {
            $disk->delete($tempRelativePath);
        }
    }

    public function toPermanentPath(string $tempRelativePath): string
    {
        return $tempRelativePath;
    }

    public function isTempPath(string $relativePath): bool
    {
        return false;
    }

    public function exists(string $relativePath): bool
    {
        return Storage::disk($this->disk())->exists($relativePath);
    }

    public function duplicateFile(string $sourceRelativePath, string $destinationRelativePath): string
    {
        $disk = Storage::disk($this->disk());

        if (!$disk->exists($sourceRelativePath)) {
            throw new RuntimeException("Source file not found: {$sourceRelativePath}");
        }

        $directory = dirname($destinationRelativePath);
        if ($directory !== '.' && !$disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        if ($disk->exists($destinationRelativePath)) {
            $disk->delete($destinationRelativePath);
        }

        $copied = $disk->put($destinationRelativePath, $disk->get($sourceRelativePath));

        if (!$copied || !$disk->exists($destinationRelativePath)) {
            throw new RuntimeException("Failed to copy file to {$destinationRelativePath}");
        }

        return $destinationRelativePath;
    }

    public function download(string $relativePath, string $downloadName): Response
    {
        $disk = Storage::disk($this->disk());

        if (!$disk->exists($relativePath)) {
            abort(404, 'Document file not found.');
        }

        $safeName = str_replace(['"', '\\'], '', $downloadName);

        return $disk->response($relativePath, $safeName, [
            'Content-Type' => $this->mimeTypeForFile($safeName),
            'Content-Disposition' => 'inline; filename="' . $safeName . '"',
        ]);
    }

    public function physicalRootPath(): string
    {
        return config('filesystems.disks.' . $this->disk() . '.root', storage_path('app/documents'));
    }

    /**
     * @return array{tree: array<int, array<string, mixed>>, stats: array<string, int|string>}
     */
    public function listStorageTree(): array
    {
        $disk = Storage::disk($this->disk());
        $tree = $this->buildStorageTree($disk, '');
        $stats = $this->collectStorageStats($disk, '');

        return [
            'tree' => $tree,
            'stats' => array_merge($stats, [
                'physical_root' => $this->physicalRootPath(),
                'temp_prefix' => 'pending',
                'permanent_prefix' => 'approved',
                'temp_files' => 0,
                'permanent_files' => $stats['files'],
            ]),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildStorageTree($disk, string $directory): array
    {
        $nodes = [];

        foreach ($disk->directories($directory) as $dir) {
            $nodes[] = [
                'type' => 'dir',
                'name' => basename($dir),
                'path' => $dir,
                'children' => $this->buildStorageTree($disk, $dir),
            ];
        }

        foreach ($disk->files($directory) as $file) {
            $nodes[] = [
                'type' => 'file',
                'name' => basename($file),
                'path' => $file,
                'size' => $disk->size($file),
                'modified' => $disk->lastModified($file),
            ];
        }

        usort($nodes, function (array $a, array $b) {
            if ($a['type'] !== $b['type']) {
                return $a['type'] === 'dir' ? -1 : 1;
            }

            return strcasecmp($a['name'], $b['name']);
        });

        return $nodes;
    }

    /**
     * @return array<string, int>
     */
    protected function collectStorageStats($disk, string $directory): array
    {
        $fileCount = count($disk->files($directory));
        $dirCount = count($disk->directories($directory));

        foreach ($disk->directories($directory) as $subdir) {
            $sub = $this->collectStorageStats($disk, $subdir);
            $fileCount += $sub['files'];
            $dirCount += $sub['directories'];
        }

        return ['files' => $fileCount, 'directories' => $dirCount];
    }

    protected function countFilesUnderPrefix($disk, string $prefix): int
    {
        if (!$disk->exists($prefix)) {
            return 0;
        }

        return $this->collectStorageStats($disk, $prefix)['files'];
    }

    protected function mimeTypeForFile(string $fileName): string
    {
        return match (strtolower(pathinfo($fileName, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };
    }

    protected function certificateFolder(string $applicationNo): string
    {
        $uppercase = strtoupper($applicationNo);
        $configured = config('document_versioning.certificate_folders', []);

        foreach (array_keys($configured) as $folder) {
            if (str_contains($uppercase, (string) $folder)) {
                return $folder;
            }
        }

        return config('document_versioning.default_certificate_folder', 'FORM_S');
    }

    protected function requestFolder(DocumentRequestType $requestType, ?string $workflowStage = null): string
    {
        $stage = strtoupper(trim((string) $workflowStage));
        if (in_array($stage, ['NEW', 'RENEWAL', 'DIGITISATION', 'ALTERATION'], true)) {
            return $stage;
        }

        if ($requestType === DocumentRequestType::INITIAL) {
            return config('document_versioning.request_folders.INITIAL', 'NEW');
        }

        return config('document_versioning.request_folders.' . $requestType->value, 'NEW');
    }

    protected function leafFolder(string $moduleType, string $documentType): string
    {
        return config(
            'document_versioning.document_folders.' . $documentType,
            config('document_versioning.module_folders.' . $moduleType, strtoupper($moduleType))
        );
    }

    protected function documentTypeCode(string $documentType): string
    {
        return config('document_versioning.file_type_codes.' . $documentType, strtoupper($documentType));
    }
}
