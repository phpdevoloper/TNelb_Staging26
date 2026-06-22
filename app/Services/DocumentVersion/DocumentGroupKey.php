<?php

namespace App\Services\DocumentVersion;

use InvalidArgumentException;

class DocumentGroupKey
{
    public static function encode(
        int $applicationId,
        string $moduleType,
        ?int $moduleRefId,
        string $documentType
    ): string {
        $payload = json_encode([
            'application_id' => $applicationId,
            'module_type' => $moduleType,
            'module_ref_id' => $moduleRefId,
            'document_type' => $documentType,
        ]);

        return rtrim(strtr(base64_encode($payload), '+/', '-_'), '=');
    }

    public static function decode(string $key): array
    {
        $padded = strtr($key, '-_', '+/');
        $padded .= str_repeat('=', (4 - strlen($padded) % 4) % 4);
        $payload = json_decode(base64_decode($padded), true);

        if (!is_array($payload) || !isset($payload['application_id'], $payload['module_type'], $payload['document_type'])) {
            throw new InvalidArgumentException('Invalid document group key.');
        }

        return [
            'application_id' => (int) $payload['application_id'],
            'module_type' => (string) $payload['module_type'],
            'module_ref_id' => isset($payload['module_ref_id']) ? (int) $payload['module_ref_id'] : null,
            'document_type' => (string) $payload['document_type'],
        ];
    }
}
