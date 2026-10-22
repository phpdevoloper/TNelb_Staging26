<?php

namespace App\Services;

use App\Models\EA_Application_model;
use App\Models\PaymentTransactionModel;
use Illuminate\Support\Facades\DB;

class FormAPayUSettlementService
{
    public function settleSuccess(PaymentTransactionModel $paymentTxn, array $callback): void
    {
        DB::transaction(function () use ($paymentTxn, $callback) {
            $paymentTxn->update([
                'status' => 'SUCCESS',
                'mihpayid' => $callback['mihpayid'] ?? $paymentTxn->mihpayid,
                'payment_method' => $callback['mode'] ?? $paymentTxn->payment_method,
                'gateway_response' => json_encode($callback),
                'error_code' => null,
                'error_message' => null,
            ]);

            $applicationId = (string) $paymentTxn->application_id;
            $form = EA_Application_model::where('application_id', $applicationId)->first();
            if (!$form) {
                throw new \RuntimeException('Contractor application not found for payment: ' . $applicationId);
            }

            $amountPaid = (int) round((float) $paymentTxn->amount);
            $lateFee = (int) ($paymentTxn->late_fee ?? 0);
            $lateMonths = (int) ($paymentTxn->late_months ?? 0);
            $applicationFee = (int) ($paymentTxn->application_fee ?? 0);
            if ($applicationFee <= 0) {
                $applicationFee = max(0, $amountPaid - $lateFee);
            }

            EA_Application_model::where('application_id', $applicationId)->update([
                'payment_status' => 'paid',
                'dt_submit' => DB::raw('NOW()'),
                'updated_at' => DB::raw('NOW()'),
            ]);

            DB::table('cl_staff_tbl')
                ->where('application_id', $applicationId)
                ->where('staff_flag', '1')
                ->update([
                    'staff_status' => 'P',
                    'updated_at' => now(),
                ]);

            $paymentData = [
                'login_id' => $form->login_id,
                'application_id' => $applicationId,
                'transaction_id' => (string) $paymentTxn->txnid,
                'payment_status' => 'success',
                'amount' => $amountPaid,
                'application_fee' => $applicationFee,
                'late_fee' => $lateFee,
                'late_months' => $lateMonths,
                'form_name' => $form->form_name,
                'license_name' => $form->license_name,
                'payment_mode' => $callback['mode'] ?? ($paymentTxn->payment_method ?: 'PayU'),
                'updated_at' => now(),
            ];

            $existingPayment = DB::table('payments')
                ->where('application_id', $applicationId)
                ->where('login_id', $form->login_id)
                ->orderByDesc('id')
                ->first();

            if ($existingPayment) {
                DB::table('payments')->where('id', $existingPayment->id)->update($paymentData);
            } else {
                $paymentData['created_at'] = now();
                DB::table('payments')->insert($paymentData);
            }
        });
    }

    public function resetApplicationForPaymentRetry(PaymentTransactionModel $paymentTxn): void
    {
        $applicationId = (string) $paymentTxn->application_id;
        $form = EA_Application_model::where('application_id', $applicationId)->first();
        if (!$form) {
            return;
        }

        EA_Application_model::where('application_id', $applicationId)->update([
            'payment_status' => 'pending',
            'updated_at' => DB::raw('NOW()'),
        ]);
    }
}
