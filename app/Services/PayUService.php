<?php

namespace App\Services;

use App\Models\PaymentTransactionModel;
use Illuminate\Support\Facades\Http;

class PayUService
{
    public const OUTCOME_SUCCESS = 'success';
    public const OUTCOME_FAILED = 'failed';
    public const OUTCOME_PENDING = 'pending';

    private string $key;
    private string $salt;

    public function __construct()
    {
        $this->key = config('payu.key');
        $this->salt = config('payu.salt');
    }

    public function generatePaymentHash(array $data): string
    {
        $udf1 = $data['udf1'] ?? '';
        $udf2 = $data['udf2'] ?? '';
        $udf3 = $data['udf3'] ?? '';
        $udf4 = $data['udf4'] ?? '';
        $udf5 = $data['udf5'] ?? '';
        $hashString =
            $this->key . '|' .
            $data['txnid'] . '|' .
            $data['amount'] . '|' .
            $data['productinfo'] . '|' .
            $data['firstname'] . '|' .
            $data['email'] . '|' .
            $udf1 . '|' .
            $udf2 . '|' .
            $udf3 . '|' .
            $udf4 . '|' .
            $udf5 . '||||||' .
            $this->salt;

        return strtolower(hash('sha512', $hashString));
    }

    /**
     * Validate PayU surl/furl post-back reverse hash per PayU docs.
     * Formula: sha512([additionalCharges|]SALT|status|udf10|…|udf1|email|firstname|productinfo|amount|txnid|key)
     */
    public function isValidResponseHash(array $data): bool
    {
        $received = strtolower(trim((string) ($data['hash'] ?? '')));
        if ($received === '') {
            return false;
        }

        $candidates = [$this->buildReverseHashString($data)];

        $additionalCharges = trim((string) ($data['additionalCharges'] ?? ''));
        if ($additionalCharges !== '') {
            array_unshift($candidates, $additionalCharges . '|' . $this->buildReverseHashString($data));
        }

        foreach ($candidates as $hashString) {
            if (hash_equals(strtolower(hash('sha512', $hashString)), $received)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify post-back integrity: reverse hash, merchant key, and amount.
     *
     * @param  array<string, mixed>  $callback
     * @return array{valid: bool, reason: ?string}
     */
    public function validateCallback(array $callback, PaymentTransactionModel $paymentTxn): array
    {
        if (! $this->isValidResponseHash($callback)) {
            return ['valid' => false, 'reason' => 'Payment response hash verification failed.'];
        }

        $callbackKey = trim((string) ($callback['key'] ?? ''));
        if ($callbackKey !== '' && $callbackKey !== $this->key) {
            return ['valid' => false, 'reason' => 'Payment response merchant key mismatch.'];
        }

        if (isset($callback['amount']) && is_numeric($callback['amount'])) {
            $callbackAmount = round((float) $callback['amount'], 2);
            $expectedAmount = round((float) $paymentTxn->amount, 2);
            if (abs($callbackAmount - $expectedAmount) > 0.01) {
                return ['valid' => false, 'reason' => 'Payment response amount mismatch.'];
            }
        }

        return ['valid' => true, 'reason' => null];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function buildReverseHashString(array $data): string
    {
        return implode('|', [
            $this->salt,
            (string) ($data['status'] ?? ''),
            (string) ($data['udf10'] ?? ''),
            (string) ($data['udf9'] ?? ''),
            (string) ($data['udf8'] ?? ''),
            (string) ($data['udf7'] ?? ''),
            (string) ($data['udf6'] ?? ''),
            (string) ($data['udf5'] ?? ''),
            (string) ($data['udf4'] ?? ''),
            (string) ($data['udf3'] ?? ''),
            (string) ($data['udf2'] ?? ''),
            (string) ($data['udf1'] ?? ''),
            (string) ($data['email'] ?? ''),
            (string) ($data['firstname'] ?? ''),
            (string) ($data['productinfo'] ?? ''),
            (string) ($data['amount'] ?? ''),
            (string) ($data['txnid'] ?? ''),
            $this->key,
        ]);
    }

    /**
     * Map PayU surl/furl callback to success | failed | pending per PayU Payment State docs.
     *
     * Uses unmappedstatus when present (auth, captured, dropped, pending, …),
     * otherwise falls back to the mapped status field (success, failure, pending).
     *
     * @param  array<string, mixed>  $callback
     */
    public function resolveCallbackOutcome(array $callback): string
    {
        $unmapped = strtolower(trim((string) ($callback['unmappedstatus'] ?? '')));
        $normalizedUnmapped = str_replace([' ', '-', '_'], '', $unmapped);

        if ($unmapped !== '') {
            if (in_array($normalizedUnmapped, ['auth', 'captured', 'success'], true)) {
                return self::OUTCOME_SUCCESS;
            }

            if (in_array($normalizedUnmapped, [
                'usercancelled',
                'bounced',
                'dropped',
                'failed',
                'autorefund',
                'failure',
            ], true)) {
                return self::OUTCOME_FAILED;
            }

            if (in_array($normalizedUnmapped, ['initiated', 'inprogress', 'pending'], true)) {
                return self::OUTCOME_PENDING;
            }
        }

        $status = strtolower(trim((string) ($callback['status'] ?? '')));

        if ($status === 'success') {
            return self::OUTCOME_SUCCESS;
        }

        if (in_array($status, ['failure', 'failed'], true)) {
            return self::OUTCOME_FAILED;
        }

        if ($status === 'pending') {
            return self::OUTCOME_PENDING;
        }

        // Unknown callback — do not mark paid or failed prematurely.
        return self::OUTCOME_PENDING;
    }

    /**
     * @param  array<string, mixed>  $callback
     */
    public function outcomeUserMessage(string $outcome, array $callback): string
    {
        $errors = $this->extractErrorFields($callback);
        if ($errors['error_message'] !== null && $errors['error_message'] !== '') {
            return $errors['error_message'];
        }

        $unmapped = strtolower(trim((string) ($callback['unmappedstatus'] ?? '')));
        $normalizedUnmapped = str_replace([' ', '-', '_'], '', $unmapped);

        if ($outcome === self::OUTCOME_FAILED) {
            return match ($normalizedUnmapped) {
                'usercancelled' => 'Payment was cancelled.',
                'bounced' => 'Payment was not completed at the gateway.',
                'dropped' => 'Payment was dropped before completion. Please try again.',
                'autorefund' => 'Payment was reversed by the gateway.',
                default => 'Payment failed or was cancelled at the payment gateway.',
            };
        }

        if ($outcome === self::OUTCOME_PENDING) {
            return match ($normalizedUnmapped) {
                'initiated' => 'Payment page was opened but not completed yet.',
                'inprogress' => 'Payment is in progress at your bank. Please wait.',
                default => 'Payment is still in progress. Please check your dashboard shortly.',
            };
        }

        return 'Payment completed successfully.';
    }

    /**
     * Map PayU callback keys to payment_transactions.error_code / error_message.
     *
     * @param  array<string, mixed>  $payload
     * @return array{error_code: ?string, error_message: ?string}
     */
    public function extractErrorFields(array $payload): array
    {
        $code = trim((string) ($payload['error'] ?? $payload['error_code'] ?? ''));
        $message = trim((string) (
            $payload['error_Message']
            ?? $payload['error_message']
            ?? $payload['field9']
            ?? ''
        ));

        if ($message !== '' && strcasecmp($message, 'No Error') === 0) {
            $message = '';
        }

        return [
            'error_code' => $code !== '' ? $code : null,
            'error_message' => $message !== '' ? $message : null,
        ];
    }

    public function verifyPayment(string $txnid): array
    {
        $command = 'verify_payment';
        $hashString =
            $this->key . '|' .
            $command . '|' .
            $txnid . '|' .
            $this->salt;
        $hash = strtolower(hash('sha512', $hashString));
        $response = Http::asForm()->timeout(5)->connectTimeout(3)->post(
            config('payu.verify_url'),
            [
                'key' => $this->key,
                'command' => $command,
                'var1' => $txnid,
                'hash' => $hash,
            ]
        );

        if (!$response->successful()) {
            throw new \RuntimeException('PayU verify HTTP ' . $response->status() . ': ' . $response->body());
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }

    /**
     * @param  array<string, mixed>  $verifyResponse
     * @return array<string, mixed>|null
     */
    public function getTransactionDetailsFromVerify(string $txnid, array $verifyResponse): ?array
    {
        $detailsRoot = $verifyResponse['transaction_details'] ?? null;
        if (! is_array($detailsRoot)) {
            return null;
        }

        $details = $detailsRoot[$txnid] ?? null;
        if (! is_array($details) && count($detailsRoot) === 1) {
            $details = reset($detailsRoot);
        }

        return is_array($details) ? $details : null;
    }

    /**
     * @param  array<string, mixed>  $details
     * @param  array<string, mixed>  $base
     * @return array<string, mixed>
     */
    public function mapVerifyDetailsToCallback(array $details, array $base = []): array
    {
        return array_merge($base, [
            'txnid' => $details['txnid'] ?? ($base['txnid'] ?? ''),
            'mihpayid' => $details['mihpayid'] ?? ($base['mihpayid'] ?? ''),
            'status' => $details['status'] ?? ($base['status'] ?? ''),
            'unmappedstatus' => $details['unmappedstatus'] ?? ($base['unmappedstatus'] ?? ''),
            'mode' => $details['mode'] ?? ($base['mode'] ?? ''),
            'amount' => $details['amt'] ?? $details['transaction_amount'] ?? ($base['amount'] ?? ''),
            'udf1' => $details['udf1'] ?? ($base['udf1'] ?? ''),
            'udf2' => $details['udf2'] ?? ($base['udf2'] ?? ''),
            'udf3' => $details['udf3'] ?? ($base['udf3'] ?? ''),
            'udf4' => $details['udf4'] ?? ($base['udf4'] ?? ''),
            'udf5' => $details['udf5'] ?? ($base['udf5'] ?? ''),
            'error' => $details['error_code'] ?? $details['error'] ?? ($base['error'] ?? ''),
            'error_Message' => $details['error_Message'] ?? ($base['error_Message'] ?? ''),
            'field9' => $details['field9'] ?? ($base['field9'] ?? ''),
        ]);
    }
}
