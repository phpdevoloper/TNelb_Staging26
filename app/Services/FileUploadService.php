<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use RuntimeException;

class FileUploadService
{
    /**
     * Store a file under DOCUMENT_STORAGE_ROOT/{directory} and return the relative path.
     *
     * Base is config('document_versioning.storage_root') from DOCUMENT_STORAGE_ROOT.
     * Pass $basePath to override for a single call.
     */
    public function upload(
        UploadedFile $file,
        string $directory,
        ?string $filename = null,
        ?string $basePath = null
    ): string {
        $directory = $this->normalizeDirectory($directory);
        $filename = $this->resolveFilename($file, $filename);
        $root = $this->resolveBasePath($basePath);

        $folderPath = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $directory);
        $this->assertPathIsInsideBase($folderPath, $root);

        if (! File::exists($folderPath)) {
            File::makeDirectory($folderPath, 0755, true);
        }

        if (! $file->move($folderPath, $filename)) {
            throw new RuntimeException('Unable to store the uploaded file.');
        }

        return $directory.'/'.$filename;
    }

    private function resolveBasePath(?string $basePath): string
    {
        if ($basePath !== null && trim($basePath) !== '') {
            return $this->normalizeConfiguredRoot(trim($basePath));
        }

        $root = rtrim((string) config('document_versioning.storage_root', ''), DIRECTORY_SEPARATOR);

        return $root !== '' ? $root : rtrim(public_path(), DIRECTORY_SEPARATOR);
    }

    private function normalizeConfiguredRoot(string $root): string
    {
        $root = str_replace('\\', '/', $root);

        if (str_starts_with($root, '/') || preg_match('/^[A-Za-z]:[\/\\\\]/', $root) === 1) {
            return rtrim(str_replace('/', DIRECTORY_SEPARATOR, $root), DIRECTORY_SEPARATOR);
        }

        return rtrim(base_path($root), DIRECTORY_SEPARATOR);
    }

    private function assertPathIsInsideBase(string $folderPath, string $root): void
    {
        $normalizedRoot = $this->normalizeAbsolute($root);
        $normalizedFolder = $this->normalizeAbsolute($folderPath);

        if (! str_starts_with($normalizedFolder, $normalizedRoot.DIRECTORY_SEPARATOR)
            && $normalizedFolder !== $normalizedRoot) {
            throw new InvalidArgumentException('Upload path must stay inside the configured base path.');
        }
    }

    private function normalizeAbsolute(string $path): string
    {
        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);

        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    private function normalizeDirectory(string $directory): string
    {
        $directory = str_replace('\\', '/', trim($directory));
        $directory = trim($directory, '/');

        if ($directory === '') {
            throw new InvalidArgumentException('Upload directory is required.');
        }

        foreach (explode('/', $directory) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new InvalidArgumentException('Invalid upload directory.');
            }
        }

        return $directory;
    }

    private function resolveFilename(UploadedFile $file, ?string $filename): string
    {
        if ($filename === null || trim($filename) === '') {
            $extension = $file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin';

            return time().'_'.uniqid().'.'.$extension;
        }

        $filename = basename(str_replace('\\', '/', trim($filename)));
        if ($filename === '' || $filename === '.' || $filename === '..') {
            throw new InvalidArgumentException('Invalid upload filename.');
        }

        return $filename;
    }
}
