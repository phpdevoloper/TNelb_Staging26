<?php

namespace App\Services\Competency;

/**
 * Shared rules for competency certificate uploads (cc_doc_log + FORM_* storage).
 *
 * Path / view URL are driven only by config/document_versioning.php (.env):
 * - DOCUMENT_STORAGE_ROOT
 * - DOCUMENT_PUBLIC_URL_PREFIX
 * - DOCUMENT_PUBLIC_BASE_URL
 */
class CompetencyDocumentSupport
{
    /**
     * Competency certificate forms (S, W, WH, P) store education/work documents
     * in cc_doc_log + private FORM_* storage — not public upload folders.
     *
     * @return list<string>
     */
    public static function versionedFormCodes(): array
    {
        $codes = config('document_versioning.versioned_form_codes', ['S', 'W', 'WH']);

        return array_values(array_unique(array_map(
            static fn ($code) => strtoupper(trim((string) $code)),
            is_array($codes) ? $codes : []
        )));
    }

    public static function versioningEnabled(): bool
    {
        return (bool) config('document_versioning.production_enabled', true);
    }

    public static function usesVersionedStorage(?string $formName): bool
    {
        if (!self::versioningEnabled()) {
            return false;
        }

        $code = strtoupper(trim((string) $formName));

        return $code !== '' && in_array($code, self::versionedFormCodes(), true);
    }

    public static function certificateFolderForForm(?string $formName): string
    {
        $code = strtoupper(trim((string) $formName));
        $folders = config('document_versioning.certificate_folders', []);
        $key = 'FORM_' . $code;

        return (string) ($folders[$key] ?? $folders[$code] ?? config('document_versioning.default_certificate_folder', 'FORM_S'));
    }

    public static function documentDownloadRouteName(): string
    {
        return 'competency.documents.download';
    }

    /** Absolute filesystem root for uploads (from DOCUMENT_STORAGE_ROOT). */
    public static function storageRoot(): string
    {
        return (string) config('document_versioning.storage_root', base_path('competency'));
    }

    /** URL path segment (from DOCUMENT_PUBLIC_URL_PREFIX). */
    public static function publicUrlPrefix(): string
    {
        return trim((string) config('document_versioning.public_url_prefix', 'competency'), '/');
    }

    /**
     * Absolute origin for document links when DOCUMENT_PUBLIC_BASE_URL is set.
     * Empty string means "use relative URLs".
     */
    public static function publicBaseUrl(): string
    {
        $configured = config('document_versioning.public_base_url');
        if ($configured === null || trim((string) $configured) === '') {
            return '';
        }

        return rtrim(trim((string) $configured), '/');
    }

    /**
     * Browser URL for a stored relative path (FORM_S/NEW/EDUCATION/...pdf).
     * Built only from DOCUMENT_PUBLIC_BASE_URL + DOCUMENT_PUBLIC_URL_PREFIX.
     */
    public static function publicUrlForStoredPath(string $storedPath): string
    {
        $storedPath = trim(str_replace('\\', '/', $storedPath));
        $prefix = self::publicUrlPrefix();
        $relative = '/' . $prefix . '/' . ltrim($storedPath, '/');

        $base = self::publicBaseUrl();
        if ($base !== '') {
            return $base . $relative;
        }

        return $relative;
    }

    public static function serveViaLaravel(): bool
    {
        return (bool) config('document_versioning.serve_via_laravel', true);
    }
}
