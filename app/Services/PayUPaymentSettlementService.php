<?php

namespace App\Services;

use App\Models\CC_Forms_Meta;
use App\Models\CC_Payments;
use App\Models\PaymentTransactionModel;
use App\Services\Competency\CompetencyMetaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayUPaymentSettlementService
{
    public function __construct(
        private PayUService $payU
    ) {
    }

    /**
     * Mark gateway txn SUCCESS, write cc_payments, set form payment_status = Y.
     *
     * @param  array<string, mixed>  $callback  PayU surl/furl POST
     */
    public function settleSuccess(PaymentTransactionModel $paymentTxn, array $callback): void
    {
        DB::transaction(function () use ($paymentTxn, $callback) {
            $txnid = (string) $paymentTxn->txnid;

            $paymentTxn->update([
                'status' => 'SUCCESS',
                'mihpayid' => $callback['mihpayid'] ?? $paymentTxn->mihpayid,
                'payment_method' => $callback['mode'] ?? $paymentTxn->payment_method,
                'gateway_response' => json_encode($callback),
                'error_code' => null,
                'error_message' => null,
            ]);

            $applicationId = $paymentTxn->application_id;
            $form = CC_Forms_Meta::findByApplicationId($applicationId);
            if (!$form) {
                throw new \RuntimeException('Application not found for payment: ' . $applicationId);
            }

            $loginId = $form->login_id ?? null;
            if (!$loginId) {
                throw new \RuntimeException('login_id missing for application: ' . $applicationId);
            }

            $amountPaid = (int) round((float) $paymentTxn->amount);
            $appType = strtoupper(trim((string) ($form->appl_type ?? 'N')));
            if ($appType === '') {
                $appType = 'N';
            }

            $lateFee = (int) ($paymentTxn->late_fee ?? 0);
            if ($lateFee <= 0 && isset($callback['udf2']) && is_numeric($callback['udf2'])) {
                $lateFee = (int) round((float) $callback['udf2']);
            }

            $lateMonths = (int) ($paymentTxn->late_months ?? 0);
            if ($lateMonths <= 0 && isset($callback['udf3']) && is_numeric($callback['udf3'])) {
                $lateMonths = (int) $callback['udf3'];
            }

            $applicationFee = (int) ($paymentTxn->application_fee ?? 0);
            if ($applicationFee <= 0 && isset($callback['udf4']) && is_numeric($callback['udf4'])) {
                $applicationFee = (int) round((float) $callback['udf4']);
            }
            if ($applicationFee <= 0) {
                $applicationFee = max(0, $amountPaid - $lateFee);
            }

            CC_Payments::updateOrCreate(
                [
                    'login_id' => $loginId,
                    'application_id' => $applicationId,
                ],
                [
                    'transaction_id' => $txnid,
                    'payment_status' => 'success',
                    'amount_paid' => $amountPaid,
                    'application_fee' => $applicationFee,
                    'app_type' => $appType,
                    'form_name' => $form->form_name,
                    'cert_name' => $form->certificate_name,
                    'payment_mode' => $callback['mode'] ?? ($paymentTxn->payment_method ?: 'PayU'),
                    'late_fee' => $lateFee,
                    'late_months' => $lateMonths,
                    'transaction_date' => now()->toDateString(),
                ]
            );

            $metaService = app(CompetencyMetaService::class);
            if ($metaService->supportsForm((string) ($form->form_name ?? ''))) {
                $form->update(['payment_status' => 'Y']);
            }
        });
    }

    /**
     * After verified failure — allow user to pay again.
     */
    public function resetApplicationForPaymentRetry(PaymentTransactionModel $paymentTxn): void
    {
        $form = CC_Forms_Meta::findByApplicationId((string) $paymentTxn->application_id);
        if (!$form) {
            return;
        }

        $metaService = app(CompetencyMetaService::class);
        if (! $metaService->supportsForm((string) ($form->form_name ?? ''))) {
            return;
        }

        $form->update([
            'app_status' => 'P',
            'payment_status' => 'N',
        ]);
    }

    /**
     * Mark gateway failure and reset form meta for retry.
     *
     * @param  array<string, mixed>  $callback
     */
    public function markFailedForRetry(PaymentTransactionModel $paymentTxn, array $callback, ?array $audit = null): void
    {
        DB::transaction(function () use ($paymentTxn, $callback, $audit) {
            $paymentTxn->update(array_merge([
                'status' => 'FAILED',
                'mihpayid' => $callback['mihpayid'] ?? $paymentTxn->mihpayid,
                'payment_method' => $callback['mode'] ?? $paymentTxn->payment_method,
                'gateway_response' => json_encode($audit ?? $callback),
            ], $this->payU->extractErrorFields($callback)));

            $this->resetApplicationForPaymentRetry($paymentTxn);
        });
    }

    /**
     * Re-read stored PayU callback and finalize rows still marked pending.
     *
     * @return array{finalized: int, skipped: int, errors: list<string>}
     */
    public function finalizePendingSuccesses(?string $txnid = null): array
    {
        $query = PaymentTransactionModel::query()
            ->whereIn('status', ['PENDING', 'PENDING_VERIFICATION', 'INITIATED', 'FAILED']);

        if ($txnid) {
            $query->where('txnid', $txnid);
        }

        $finalized = 0;
        $skipped = 0;
        $errors = [];

        foreach ($query->get() as $paymentTxn) {
            $result = $this->refreshPendingFromPayU($paymentTxn);

            if ($result['outcome'] === PayUService::OUTCOME_SUCCESS) {
                $finalized++;
                continue;
            }

            if ($result['outcome'] === PayUService::OUTCOME_FAILED) {
                $skipped++;
                continue;
            }

            $skipped++;
        }

        return compact('finalized', 'skipped', 'errors');
    }

    /**
     * Ask PayU verify_payment for the latest status when our DB is still pending.
     *
     * @return array{outcome: string, payment: PaymentTransactionModel, message?: string}
     */
    public function refreshPendingFromPayU(PaymentTransactionModel $paymentTxn): array
    {
        $status = strtoupper((string) $paymentTxn->status);
        if ($status === 'SUCCESS') {
            return [
                'outcome' => PayUService::OUTCOME_SUCCESS,
                'payment' => $paymentTxn,
            ];
        }

        if (! in_array($status, ['PENDING', 'INITIATED', 'PENDING_VERIFICATION', 'FAILED'], true)) {
            return [
                'outcome' => PayUService::OUTCOME_PENDING,
                'payment' => $paymentTxn,
            ];
        }

        $stored = json_decode((string) $paymentTxn->gateway_response, true);
        $baseCallback = is_array($stored) ? $stored : [];
        unset($baseCallback['_callback_source'], $baseCallback['_validation_error']);

        try {
            $verifyResponse = $this->payU->verifyPayment((string) $paymentTxn->txnid);
        } catch (\Throwable $e) {
            Log::warning('PayU verify failed during pending refresh', [
                'txnid' => $paymentTxn->txnid,
                'message' => $e->getMessage(),
            ]);

            return $this->resolveOutcomeFromStoredCallback($paymentTxn, $baseCallback)
                ?? [
                    'outcome' => PayUService::OUTCOME_PENDING,
                    'payment' => $paymentTxn,
                    'message' => 'Unable to reach PayU to verify this payment. Please try again shortly.',
                ];
        }

        $details = $this->payU->getTransactionDetailsFromVerify((string) $paymentTxn->txnid, $verifyResponse);
        if (! is_array($details)) {
            return $this->resolveOutcomeFromStoredCallback($paymentTxn, $baseCallback)
                ?? [
                    'outcome' => PayUService::OUTCOME_PENDING,
                    'payment' => $paymentTxn,
                    'message' => 'PayU did not return transaction details yet. Please try again later.',
                ];
        }

        $callback = $this->payU->mapVerifyDetailsToCallback($details, $baseCallback);
        $outcome = $this->payU->resolveCallbackOutcome($callback);
        $message = $this->payU->outcomeUserMessage($outcome, $callback);

        $audit = array_merge($callback, [
            '_verified_at' => now()->toDateTimeString(),
            '_verify' => $verifyResponse,
        ]);

        if ($outcome === PayUService::OUTCOME_SUCCESS) {
            $paymentTxn->update([
                'mihpayid' => $callback['mihpayid'] ?? $paymentTxn->mihpayid,
                'payment_method' => $callback['mode'] ?? $paymentTxn->payment_method,
                'gateway_response' => json_encode($audit),
            ]);
            $this->settleSuccess($paymentTxn, $callback);

            return [
                'outcome' => PayUService::OUTCOME_SUCCESS,
                'payment' => $paymentTxn->fresh(),
            ];
        }

        if ($outcome === PayUService::OUTCOME_FAILED) {
            $this->markFailedForRetry($paymentTxn, $callback, $audit);

            return [
                'outcome' => PayUService::OUTCOME_FAILED,
                'payment' => $paymentTxn->fresh(),
                'message' => $message,
            ];
        }

        $paymentTxn->update([
            'status' => 'PENDING',
            'mihpayid' => $callback['mihpayid'] ?? $paymentTxn->mihpayid,
            'payment_method' => $callback['mode'] ?? $paymentTxn->payment_method,
            'gateway_response' => json_encode($audit),
        ]);

        return [
            'outcome' => PayUService::OUTCOME_PENDING,
            'payment' => $paymentTxn->fresh(),
            'message' => $message,
        ];
    }

    /**
     * When verify_payment is unavailable, finalize from a prior surl/furl post-back if stored.
     *
     * @param  array<string, mixed>  $baseCallback
     * @return array{outcome: string, payment: PaymentTransactionModel, message?: string}|null
     */
    private function resolveOutcomeFromStoredCallback(
        PaymentTransactionModel $paymentTxn,
        array $baseCallback
    ): ?array {
        if ($baseCallback === []) {
            return null;
        }

        $callback = $this->payU->mapVerifyDetailsToCallback($baseCallback, $baseCallback);
        $outcome = $this->payU->resolveCallbackOutcome($callback);
        $message = $this->payU->outcomeUserMessage($outcome, $callback);

        if ($outcome === PayUService::OUTCOME_SUCCESS) {
            $this->settleSuccess($paymentTxn, $callback);

            return [
                'outcome' => PayUService::OUTCOME_SUCCESS,
                'payment' => $paymentTxn->fresh(),
            ];
        }

        if ($outcome === PayUService::OUTCOME_FAILED) {
            $this->markFailedForRetry($paymentTxn, $callback, $baseCallback);

            return [
                'outcome' => PayUService::OUTCOME_FAILED,
                'payment' => $paymentTxn->fresh(),
                'message' => $message,
            ];
        }

        return null;
    }
}
