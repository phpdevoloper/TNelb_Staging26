<?php

namespace App\Services;

use App\Models\CC_Forms_Meta;
use App\Models\CC_Payments;
use App\Models\PaymentTransactionModel;
use App\Services\Competency\CompetencyMetaService;
use Illuminate\Support\Facades\DB;

class PayUPaymentSettlementService
{
    /**
     * Mark gateway txn SUCCESS, write cc_payments, set form payment_status = Y.
     *
     * @param  array<string, mixed>  $callback  PayU surl POST (or decoded gateway_response)
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

            // Prefer values saved at initiate; fall back to PayU udf2/udf3/udf4
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
                    // cc_payments amount columns are bigint — never send "750.00"
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
     * Finalize PENDING_VERIFICATION rows whose saved PayU response already says success.
     *
     * @return array{finalized: int, skipped: int, errors: list<string>}
     */
    public function finalizePendingSuccesses(?string $txnid = null): array
    {
        $query = PaymentTransactionModel::query()
            ->where('status', 'PENDING_VERIFICATION');

        if ($txnid) {
            $query->where('txnid', $txnid);
        }

        $finalized = 0;
        $skipped = 0;
        $errors = [];

        foreach ($query->get() as $paymentTxn) {
            $callback = json_decode((string) $paymentTxn->gateway_response, true);
            if (! is_array($callback)) {
                $skipped++;
                continue;
            }

            if (strtolower((string) ($callback['status'] ?? '')) !== 'success') {
                $skipped++;
                continue;
            }

            try {
                $this->settleSuccess($paymentTxn, $callback);
                $finalized++;
            } catch (\Throwable $e) {
                $errors[] = $paymentTxn->txnid . ': ' . $e->getMessage();
            }
        }

        return compact('finalized', 'skipped', 'errors');
    }
}
