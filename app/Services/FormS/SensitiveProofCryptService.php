<?php

namespace App\Services\FormS;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class SensitiveProofCryptService
{
    public static function requiresEncryption(string $proofName): bool
    {
        return in_array($proofName, [
            FormSProofDocumentService::PROOF_AADHAAR,
            FormSProofDocumentService::PROOF_PAN,
        ], true);
    }

    public static function requiresEncryptionForModule(string $moduleType, string $documentType): bool
    {
        return in_array(strtolower($moduleType), ['aadhaar', 'pan'], true)
            || in_array(strtolower($documentType), ['aadhaar_doc', 'pancard_doc'], true);
    }

    public static function isEncryptedProofDocumentPath(string $path): bool
    {
        return str_ends_with(strtolower(trim($path)), '.bin');
    }

    public function encryptProofNumber(string $plain): string
    {
        $plain = trim($plain);
        if ($plain === '') {
            return $plain;
        }

        if ($this->looksLikeEncryptedPayload($plain)) {
            return $plain;
        }

        return Crypt::encryptString($plain);
    }

    public function decryptProofNumber(?string $stored): ?string
    {
        if ($stored === null || trim($stored) === '') {
            return null;
        }

        $stored = trim($stored);

        try {
            return Crypt::decryptString($stored);
        } catch (DecryptException|\Throwable) {
            return $stored;
        }
    }

    public function encryptFileContents(string $contents): string
    {
        return Crypt::encrypt($contents);
    }

    public function decryptFileContents(string $encrypted): string
    {
        return Crypt::decrypt($encrypted);
    }

    public function inlineMimeTypeForProofDocument(string $storedPath, string $downloadName): string
    {
        if (self::isEncryptedProofDocumentPath($storedPath) || self::isEncryptedProofDocumentPath($downloadName)) {
            return 'application/pdf';
        }

        return match (strtolower(pathinfo($downloadName, PATHINFO_EXTENSION))) {
            'pdf' => 'application/pdf',
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            default => 'application/octet-stream',
        };
    }

    public function displayFileNameForProofDocument(string $downloadName): string
    {
        if (self::isEncryptedProofDocumentPath($downloadName)) {
            return preg_replace('/\.bin$/i', '.pdf', $downloadName) ?: $downloadName;
        }

        return $downloadName;
    }

    protected function looksLikeEncryptedPayload(string $value): bool
    {
        return str_starts_with($value, 'eyJpdiI6') && strlen($value) > 40;
    }
}
