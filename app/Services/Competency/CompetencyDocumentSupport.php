<?php

namespace App\Services\Competency;

/**
 * Shared rules for competency certificate uploads (documents_log + private FORM_* storage).
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
}
