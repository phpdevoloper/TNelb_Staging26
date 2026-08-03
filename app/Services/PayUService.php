<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
class PayUService
{
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
     * Validate PayU surl/furl reverse hash (no outbound network call).
     * Formula: salt|status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key
     */
    public function isValidResponseHash(array $data): bool
    {
        $received = strtolower(trim((string) ($data['hash'] ?? '')));
        if ($received === '') {
            return false;
        }

        $hashString =
            $this->salt . '|' .
            ($data['status'] ?? '') . '||||||' .
            ($data['udf5'] ?? '') . '|' .
            ($data['udf4'] ?? '') . '|' .
            ($data['udf3'] ?? '') . '|' .
            ($data['udf2'] ?? '') . '|' .
            ($data['udf1'] ?? '') . '|' .
            ($data['email'] ?? '') . '|' .
            ($data['firstname'] ?? '') . '|' .
            ($data['productinfo'] ?? '') . '|' .
            ($data['amount'] ?? '') . '|' .
            ($data['txnid'] ?? '') . '|' .
            $this->key;

        return hash_equals(strtolower(hash('sha512', $hashString)), $received);
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
        // Short timeout — server outbound to PayU may be blocked; callers must treat this as optional.
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
}
