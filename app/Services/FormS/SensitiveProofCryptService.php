<?php

namespace App\Services\FormS;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Response;
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

    public function detectInlineMimeFromBytes(string $bytes): string
    {
        if (strncmp($bytes, '%PDF', 4) === 0) {
            return 'application/pdf';
        }
        if (strncmp($bytes, "\xFF\xD8\xFF", 3) === 0) {
            return 'image/jpeg';
        }
        if (strncmp($bytes, "\x89PNG\r\n\x1A\n", 8) === 0) {
            return 'image/png';
        }
        if (strncmp($bytes, 'GIF87a', 6) === 0 || strncmp($bytes, 'GIF89a', 6) === 0) {
            return 'image/gif';
        }

        return 'application/pdf';
    }

    public function displayFileNameForProofDocument(string $downloadName, ?string $mime = null): string
    {
        $name = self::isEncryptedProofDocumentPath($downloadName)
            ? (preg_replace('/\.bin$/i', '.pdf', $downloadName) ?: $downloadName)
            : $downloadName;

        if ($mime !== null) {
            $ext = match ($mime) {
                'application/pdf' => 'pdf',
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                default => null,
            };
            if ($ext !== null) {
                $base = preg_replace('/\.(bin|pdf|jpe?g|png|gif)$/i', '', $name) ?: $name;

                return $base.'.'.$ext;
            }
        }

        return $name;
    }

    /**
     * Inline Aadhaar/PAN bytes so Firefox/Edge/Safari can preview (Chrome is more lenient).
     */
    public function browserInlineResponse(string $bytes, string $downloadName): Response
    {
        $mime = $this->detectInlineMimeFromBytes($bytes);
        $fileName = str_replace(['"', '\\', "\r", "\n"], '', $this->displayFileNameForProofDocument($downloadName, $mime));
        $encodedName = rawurlencode($fileName);

        $response = response($bytes, 200);
        $response->headers->set('Content-Type', $mime);
        $response->headers->set('Content-Length', (string) strlen($bytes));
        $response->headers->set(
            'Content-Disposition',
            'inline; filename="'.$fileName.'"; filename*=UTF-8\'\''.$encodedName
        );
        $response->headers->set('Accept-Ranges', 'none');
        $response->headers->set('Cache-Control', 'private, no-store, no-transform');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }

    protected function looksLikeEncryptedPayload(string $value): bool
    {
        return str_starts_with($value, 'eyJpdiI6') && strlen($value) > 40;
    }
}
