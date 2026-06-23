<?php

namespace App\Services\WithoutTmp;

use App\Models\ScertApp;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class WithoutTmpStorageService
{
    public function disk(): string
    {
        return config('without_tmp.disk', 'without_tmp');
    }

    public function folderForUploadType(string $uploadType): string
    {
        $folder = config('without_tmp.upload_types.' . $uploadType . '.folder');

        if (!$folder) {
            throw new RuntimeException("Unknown upload type: {$uploadType}");
        }

        return $folder;
    }

    public function generateFileName(ScertApp $application, string $uploadType, string $extension): string
    {
        $datePart = now()->format('ymd');
        $sequence = $this->nextSequence($application, $uploadType);
        $ext = ltrim(strtolower($extension), '.');

        return sprintf(
            '%s_%s_%s_%03d.%s',
            $datePart,
            $application->application_code,
            $uploadType,
            $sequence,
            $ext
        );
    }

    public function storeUpload(
        UploadedFile $file,
        ScertApp $application,
        string $uploadType
    ): array {
        $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';
        $fileName = $this->generateFileName($application, $uploadType, $extension);
        $folder = $this->folderForUploadType($uploadType);
        $relativePath = $folder . '/' . $fileName;
        $disk = Storage::disk($this->disk());

        $disk->putFileAs($folder, $file, $fileName);

        return [
            'file_name' => $fileName,
            'file_path' => $relativePath,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ];
    }

    public function deleteFile(?string $relativePath): void
    {
        if (!$relativePath) {
            return;
        }

        $disk = Storage::disk($this->disk());

        if ($disk->exists($relativePath)) {
            $disk->delete($relativePath);
        }
    }

    public function download(string $relativePath, string $downloadName): Response
    {
        $disk = Storage::disk($this->disk());

        if (!$disk->exists($relativePath)) {
            abort(404, 'File not found.');
        }

        $safeName = str_replace(['"', '\\'], '', $downloadName);

        return $disk->response($relativePath, $safeName, [
            'Content-Type' => $this->mimeTypeForFile($safeName),
            'Content-Disposition' => 'inline; filename="' . $safeName . '"',
        ]);
    }

    public function physicalRootPath(): string
    {
        return config('filesystems.disks.' . $this->disk() . '.root', storage_path('app/without_tmp'));
    }

    /**
     * @return array{tree: array<int, array<string, mixed>>, stats: array<string, int|string>}
     */
    public function listStorageTree(): array
    {
        $disk = Storage::disk($this->disk());
        $tree = $this->buildStorageTree($disk, '');

        return [
            'tree' => $tree,
            'stats' => [
                'physical_root' => $this->physicalRootPath(),
                'total_files' => $this->countAllFiles($disk, ''),
            ],
        ];
    }

    protected function nextSequence(ScertApp $application, string $uploadType): int
    {
        $prefix = now()->format('ymd') . '_' . $application->application_code . '_' . $uploadType . '_';
        $disk = Storage::disk($this->disk());
        $folder = $this->folderForUploadType($uploadType);
        $max = 0;

        if (!$disk->exists($folder)) {
            return 1;
        }

        foreach ($disk->files($folder) as $file) {
            $name = basename($file);
            if (str_starts_with($name, $prefix)) {
                if (preg_match('/_(\d{3})\./', $name, $matches)) {
                    $max = max($max, (int) $matches[1]);
                }
            }
        }

        return $max + 1;
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

    protected function countAllFiles($disk, string $directory): int
    {
        $count = count($disk->files($directory));

        foreach ($disk->directories($directory) as $subdir) {
            $count += $this->countAllFiles($disk, $subdir);
        }

        return $count;
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
}
