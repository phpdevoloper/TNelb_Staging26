<?php

namespace App\Services;

use App\Models\CC_Forms_Meta;
use App\Models\CC_Payments;
use App\Models\PaymentTransactionModel;
use App\Services\Competency\CompetencyMetaService;
use App\Services\Competency\FormPPaymentStatusRules;
use App\Services\Payments\PayUCallbackRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayUPaymentSettlementService
{
    /** At most this many earlier attempts are looked up at PayU before a new Form P payment starts. */
    public const PRIOR_LOOKUP_LIMIT = 5;

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

            // Serialise concurrent settles (surl post-back + Status button + checkStatus) on this attempt.
            $lockedTxn = PaymentTransactionModel::query()->whereKey($paymentTxn->getKey())->lockForUpdate()->first();
            $form = $this->lockedForm((string) $paymentTxn->application_id);

            // Idempotent: an attempt already settled on an application already marked paid changes nothing.
            if ($lockedTxn
                && strtoupper(trim((string) $lockedTxn->status)) === 'SUCCESS'
                && $form
                && strtoupper(trim((string) ($form->payment_status ?? ''))) === 'Y') {
                $paymentTxn->setRawAttributes($lockedTxn->getAttributes(), true);

                return;
            }

            $paymentTxn->update([
                'status' => 'SUCCESS',
                'mihpayid' => $callback['mihpayid'] ?? $paymentTxn->mihpayid,
                'payment_method' => $callback['mode'] ?? $paymentTxn->payment_method,
                'gateway_response' => json_encode($callback),
                'error_code' => null,
                'error_message' => null,
            ]);

            $applicationId = $paymentTxn->application_id;
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
                $updates = ['payment_status' => 'Y'];

                // The first successful payment is the submission: a draft becomes a submitted application.
                // Form P is only ever submitted here (initiate() leaves it D); other forms are normally flipped
                // by initiate() and this covers any path that skipped it. A repeat call finds P and keeps
                // the original submitted_date.
                if (FormPPaymentStatusRules::submitsOnPaymentSuccess((string) ($form->app_status ?? ''))) {
                    $updates['app_status'] = 'P';
                    $updates['submitted_date'] = now();
                }

                $form->update($updates);
            }
        });
    }

    /**
     * After verified failure — allow user to pay again.
     */
    public function resetApplicationForPaymentRetry(PaymentTransactionModel $paymentTxn): void
    {
        DB::transaction(function () use ($paymentTxn) {
            // Locked so a concurrent settleSuccess (another attempt) cannot be overwritten with N.
            $form = $this->lockedForm((string) $paymentTxn->application_id);
            if (!$form) {
                return;
            }

            $metaService = app(CompetencyMetaService::class);
            if (! $metaService->supportsForm((string) ($form->form_name ?? ''))) {
                return;
            }

            // Form P goes back to an unpaid draft (D/N) and a paid one is never touched;
            // other forms keep the previous P/N reset.
            $updates = FormPPaymentStatusRules::updatesAfterFailure(
                (string) ($form->form_name ?? ''),
                (string) ($form->app_status ?? ''),
                (string) ($form->payment_status ?? '')
            );

            if ($updates !== []) {
                $form->update($updates);
            }
        });
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

        // Only a post-back that passed validation (or an earlier verify answer) may be reused; a rejected /
        // forged post-back stored for audit is never read back as a gateway answer.
        $baseCallback = PayUCallbackRules::trustedStoredCallbackFromJson((string) $paymentTxn->gateway_response);
        $isFormP = $this->isFormPApplication((string) $paymentTxn->application_id);

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

        // Form P reads the entry for exactly this txnid (no single-entry fallback).
        $details = $isFormP
            ? FormPPaymentStatusRules::transactionDetailsFor($verifyResponse, (string) $paymentTxn->txnid)
            : $this->payU->getTransactionDetailsFromVerify((string) $paymentTxn->txnid, $verifyResponse);
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

        // A "success" for another amount or another application is never settled.
        if ($outcome === PayUService::OUTCOME_SUCCESS
            && ! FormPPaymentStatusRules::verifiedPaymentMatches($paymentTxn->amount, (string) $paymentTxn->application_id, $details)) {
            Log::warning('PayU verify success does not match the payment attempt; not settled', [
                'txnid' => $paymentTxn->txnid,
                'application_id' => $paymentTxn->application_id,
                'expected_amount' => $paymentTxn->amount,
                'gateway_amount' => $details['amt'] ?? $details['transaction_amount'] ?? null,
                'gateway_udf1' => $details['udf1'] ?? null,
            ]);

            return [
                'outcome' => PayUService::OUTCOME_PENDING,
                'payment' => $paymentTxn,
                'message' => 'The payment reported by PayU could not be matched with this application. Please contact the Board with your transaction reference.',
            ];
        }

        // Form P: PayU has no record of the attempt, or only the payment page was opened. Keep the row
        // INITIATED (never PENDING) so an abandoned attempt can age out and the applicant can pay again.
        if ($outcome === PayUService::OUTCOME_PENDING
            && $isFormP
            && strtoupper(trim((string) $paymentTxn->status)) === 'INITIATED'
            && (FormPPaymentStatusRules::isGatewayNotFound($details) || FormPPaymentStatusRules::isGatewayPageInitiated($details))) {
            return [
                'outcome' => PayUService::OUTCOME_PENDING,
                'payment' => $paymentTxn,
                'message' => FormPPaymentStatusRules::isGatewayNotFound($details)
                    ? 'PayU has no record of this payment attempt. If no amount was debited, you can pay again from your dashboard.'
                    : 'The PayU payment page was opened but the payment was not completed. If no amount was debited, you can pay again from your dashboard after a few minutes.',
            ];
        }

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
     * $baseCallback must already be PayUCallbackRules::trustedStoredCallback() output: a rejected
     * (forged / hash-failed) post-back is never used here.
     *
     * @param  array<string, mixed>  $baseCallback
     * @return array{outcome: string, payment: PaymentTransactionModel, message?: string}|null
     */
    private function resolveOutcomeFromStoredCallback(
        PaymentTransactionModel $paymentTxn,
        array $baseCallback
    ): ?array {
        $baseCallback = PayUCallbackRules::trustedStoredCallback($baseCallback);
        if ($baseCallback === []) {
            return null;
        }

        $callback = $this->payU->mapVerifyDetailsToCallback($baseCallback, $baseCallback);
        $outcome = $this->payU->resolveCallbackOutcome($callback);
        $message = $this->payU->outcomeUserMessage($outcome, $callback);

        if ($outcome === PayUService::OUTCOME_SUCCESS
            && ! FormPPaymentStatusRules::verifiedPaymentMatches($paymentTxn->amount, (string) $paymentTxn->application_id, $callback)) {
            Log::warning('Stored PayU callback does not match the payment attempt; not settled', [
                'txnid' => $paymentTxn->txnid,
                'application_id' => $paymentTxn->application_id,
            ]);

            return null;
        }

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

    /**
     * True when the application is already paid / fee-exempt (payment_status Y or B) or any gateway
     * attempt for it has succeeded.
     */
    public function isApplicationPaid(string $applicationId, ?Model $form = null): bool
    {
        $form = $form ?? CC_Forms_Meta::findByApplicationId($applicationId);
        if ($form && in_array(strtoupper(trim((string) ($form->payment_status ?? ''))), ['Y', 'B'], true)) {
            return true;
        }

        return PaymentTransactionModel::query()
            ->where('application_id', $applicationId)
            ->where('status', 'SUCCESS')
            ->exists();
    }

    /**
     * Before a new Form P payment starts, look up earlier open attempts (INITIATED / PENDING /
     * PENDING_VERIFICATION, newest first, at most PRIOR_LOOKUP_LIMIT lookups) at PayU:
     *  - one actually succeeded  -> settle it and refuse a second payment;
     *  - one failed              -> mark it FAILED and continue;
     *  - not found / page opened long ago (INITIATED) -> treat as abandoned and continue;
     *  - still in progress / no usable answer -> refuse for now.
     *
     * @return array{allowed: bool, paid?: bool, message?: string}
     */
    public function checkPriorFormPAttempts(string $applicationId): array
    {
        $attempts = PaymentTransactionModel::query()
            ->where('application_id', $applicationId)
            ->whereIn('status', FormPPaymentStatusRules::OPEN_ATTEMPT_STATUSES)
            ->orderByDesc('id')
            ->get();

        $inProgress = [
            'allowed' => false,
            'message' => 'An earlier payment attempt for this application is still being processed by PayU / your bank. '
                . 'Please wait a few minutes and use the Status button on your dashboard before paying again.',
        ];

        $lookups = 0;
        foreach ($attempts as $attempt) {
            $txnStatus = strtoupper(trim((string) $attempt->status));
            $isOld = FormPPaymentStatusRules::isOlderThanAbandonAge($attempt->created_at);

            if ($lookups >= self::PRIOR_LOOKUP_LIMIT) {
                // Not looked up: only an old INITIATED attempt (never posted back) may be skipped.
                if ($txnStatus === 'INITIATED' && $isOld) {
                    continue;
                }

                return $inProgress;
            }
            $lookups++;

            $details = null;
            $verifyResponse = null;
            try {
                $verifyResponse = $this->payU->verifyPayment((string) $attempt->txnid);
                $details = FormPPaymentStatusRules::transactionDetailsFor($verifyResponse, (string) $attempt->txnid);
            } catch (\Throwable $e) {
                Log::warning('PayU verify failed while checking earlier Form P attempts', [
                    'application_id' => $applicationId,
                    'txnid' => $attempt->txnid,
                    'message' => $e->getMessage(),
                ]);
            }

            $outcome = is_array($details) && $details !== []
                ? $this->payU->resolveCallbackOutcome($details)
                : PayUService::OUTCOME_PENDING;

            $action = FormPPaymentStatusRules::priorAttemptAction($txnStatus, $details, $outcome, $isOld);

            if ($action === FormPPaymentStatusRules::PRIOR_PAID) {
                if (! FormPPaymentStatusRules::verifiedPaymentMatches($attempt->amount, $applicationId, $details)) {
                    Log::warning('Earlier Form P attempt reported paid but does not match; new payment refused', [
                        'application_id' => $applicationId,
                        'txnid' => $attempt->txnid,
                    ]);

                    return [
                        'allowed' => false,
                        'message' => 'An earlier payment for this application needs to be checked by the Board. '
                            . 'Please contact the Board with your transaction reference before paying again.',
                    ];
                }

                $callback = $this->payU->mapVerifyDetailsToCallback(
                    $details,
                    PayUCallbackRules::trustedStoredCallbackFromJson((string) $attempt->gateway_response)
                );
                $attempt->update([
                    'mihpayid' => $callback['mihpayid'] ?? $attempt->mihpayid,
                    'payment_method' => $callback['mode'] ?? $attempt->payment_method,
                    'gateway_response' => json_encode(array_merge($callback, [
                        '_verified_at' => now()->toDateTimeString(),
                        '_verified_by' => 'prior_attempt_check',
                        '_verify' => $verifyResponse,
                    ])),
                ]);
                $this->settleSuccess($attempt, $callback);

                return [
                    'allowed' => false,
                    'paid' => true,
                    'message' => 'Your earlier payment for this application was successful and has now been recorded. '
                        . 'No further payment is needed.',
                ];
            }

            if ($action === FormPPaymentStatusRules::PRIOR_MARK_FAILED) {
                $callback = $this->payU->mapVerifyDetailsToCallback(
                    $details,
                    PayUCallbackRules::trustedStoredCallbackFromJson((string) $attempt->gateway_response)
                );
                $this->markFailedForRetry($attempt, $callback, array_merge($callback, [
                    '_verified_at' => now()->toDateTimeString(),
                    '_verified_by' => 'prior_attempt_check',
                    '_verify' => $verifyResponse,
                ]));

                continue;
            }

            if ($action === FormPPaymentStatusRules::PRIOR_IGNORE) {
                // Abandoned (never reached PayU, or the page was closed long ago). The row is left as it is:
                // if PayU ever settles it, the post-back / Status button still records it.
                continue;
            }

            return $inProgress;
        }

        return ['allowed' => true];
    }

    private function isFormPApplication(string $applicationId): bool
    {
        $form = CC_Forms_Meta::findByApplicationId($applicationId);

        return $form !== null && FormPPaymentStatusRules::isFormP((string) ($form->form_name ?? ''));
    }

    /** The application's meta row re-read FOR UPDATE (call inside a DB transaction). */
    private function lockedForm(string $applicationId): ?Model
    {
        $form = CC_Forms_Meta::findByApplicationId($applicationId);
        if (! $form) {
            return null;
        }

        return $form->newQuery()
            ->where('application_id', $form->application_id)
            ->lockForUpdate()
            ->first() ?? $form;
    }
}
