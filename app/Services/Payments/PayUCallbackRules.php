<?php

namespace App\Services\Payments;

/**
 * Pure rules for PayU post-backs stored in payment_transactions.gateway_response.
 *
 * A post-back that failed validation (bad hash, wrong key, wrong amount) is kept for audit only and
 * must never be read back as a gateway answer (e.g. by the dashboard Status button). No I/O here.
 */
class PayUCallbackRules
{
    /** The rejected (untrusted) post-back is nested under this key. */
    public const REJECTED_KEY = '_rejected_callback';

    /** Reason a post-back was rejected; legacy rows stored it next to the forged fields. */
    public const VALIDATION_ERROR_KEY = '_validation_error';

    /** A rejected post-back that arrived after a trusted one is attached under this key. */
    public const LAST_REJECTED_KEY = '_last_rejected_callback';

    /** Amount tolerance (rupees) between the fee and the gateway amount. */
    public const AMOUNT_TOLERANCE = 0.01;

    /**
     * The gateway fields of a stored callback that may be trusted, without internal "_" keys.
     * Returns [] when nothing trustworthy is stored (missing, undecodable or a rejected post-back).
     *
     * @return array<string, mixed>
     */
    public static function trustedStoredCallback(mixed $stored): array
    {
        if (! is_array($stored) || $stored === []) {
            return [];
        }

        if (array_key_exists(self::REJECTED_KEY, $stored) || array_key_exists(self::VALIDATION_ERROR_KEY, $stored)) {
            return [];
        }

        $trusted = [];
        foreach ($stored as $key => $value) {
            if (is_string($key) && str_starts_with($key, '_')) {
                continue;
            }
            $trusted[$key] = $value;
        }

        return $trusted;
    }

    /**
     * @return array<string, mixed>
     */
    public static function trustedStoredCallbackFromJson(?string $json): array
    {
        if ($json === null || trim($json) === '') {
            return [];
        }

        $decoded = json_decode($json, true);

        return self::trustedStoredCallback(is_array($decoded) ? $decoded : null);
    }

    /**
     * Audit record for a rejected post-back: the posted fields are nested so they can never be read
     * as a gateway answer.
     *
     * @param  array<string, mixed>  $callback
     * @return array<string, mixed>
     */
    public static function rejectedCallbackRecord(array $callback, string $source, ?string $reason): array
    {
        return [
            self::REJECTED_KEY => $callback,
            '_callback_source' => $source,
            self::VALIDATION_ERROR_KEY => $reason,
            '_rejected_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * Value to store in gateway_response after a rejected post-back. A trusted stored callback is kept
     * exactly as it was (the rejected one is only attached for audit); otherwise the rejected record is stored.
     *
     * @param  array<string, mixed>|string|null  $existing  current gateway_response (JSON or decoded)
     * @param  array<string, mixed>  $callback
     * @return array<string, mixed>
     */
    public static function storedValueAfterRejectedCallback(
        array|string|null $existing,
        array $callback,
        string $source,
        ?string $reason
    ): array {
        $decoded = is_array($existing)
            ? $existing
            : (is_string($existing) && trim($existing) !== '' ? json_decode($existing, true) : null);

        $record = self::rejectedCallbackRecord($callback, $source, $reason);

        if (is_array($decoded) && self::trustedStoredCallback($decoded) !== []) {
            $decoded[self::LAST_REJECTED_KEY] = $record;

            return $decoded;
        }

        return $record;
    }

    /**
     * Gateway amount (verify "amt"/"transaction_amount" or post-back "amount") equals the expected fee
     * within one paisa. A missing or unusable amount never matches.
     *
     * @param  array<string, mixed>  $details
     */
    public static function amountMatches(mixed $expected, array $details): bool
    {
        if ($expected === null || $expected === '' || ! is_numeric($expected)) {
            return false;
        }

        $amount = null;
        foreach (['amt', 'transaction_amount', 'amount'] as $key) {
            if (array_key_exists($key, $details) && $details[$key] !== null && $details[$key] !== '') {
                $amount = $details[$key];
                break;
            }
        }

        if ($amount === null || ! is_numeric($amount)) {
            return false;
        }

        $diff = abs(round((float) $amount, 2) - round((float) $expected, 2));

        return round($diff, 2) <= self::AMOUNT_TOLERANCE;
    }
}
