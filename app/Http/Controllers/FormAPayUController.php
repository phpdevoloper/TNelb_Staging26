<?php

namespace App\Http\Controllers;

use App\Models\EA_Application_model;
use App\Models\PaymentTransactionModel;
use App\Services\FormAPayUSettlementService;
use App\Services\PayUService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FormAPayUController extends BaseController
{
    public function initiate(Request $request, PayUService $payU)
    {
        try {
            $request->validate([
                'application_id' => 'required',
                'amount' => 'required|numeric|min:0.01',
                'actual_fees' => 'nullable|numeric|min:0',
                'lateFee' => 'nullable|numeric|min:0',
                'lateMonths' => 'nullable|integer|min:0',
            ]);

            $applicationId = trim((string) $request->application_id);
            $form = EA_Application_model::where('application_id', $applicationId)->first();

            if (!$form) {
                return $this->renderInitiateError('Contractor application not found.');
            }

            $loginId = (string) (auth()->user()->login_id ?? '');
            if ($loginId === '' || (string) ($form->login_id ?? '') !== $loginId) {
                return $this->renderInitiateError('Access denied for this application.');
            }

            $amount = (float) $request->amount;
            $lateFee = (int) round((float) ($request->input('lateFee', 0)));
            $lateMonths = (int) ($request->input('lateMonths', 0));
            $applicationFee = $request->filled('actual_fees')
                ? (int) round((float) $request->input('actual_fees'))
                : max(0, (int) round($amount) - $lateFee);

            $txnid = 'CL' . strtoupper(Str::random(18));
            PaymentTransactionModel::create([
                'application_id' => $applicationId,
                'txnid' => $txnid,
                'amount' => $amount,
                'application_fee' => $applicationFee,
                'late_fee' => $lateFee,
                'late_months' => $lateMonths,
                'gateway' => 'PAYU',
                'status' => 'INITIATED',
            ]);

            $user = auth()->user();
            $data = [
                'key' => config('payu.key'),
                'txnid' => $txnid,
                'amount' => number_format($amount, 2, '.', ''),
                'productinfo' => 'TNELB Contractor Fee - ' . $applicationId,
                'firstname' => $form->applicant_name ?? 'Applicant',
                'email' => $user->email ?? 'noreply@example.com',
                'phone' => $user->mobile ?? $user->phone ?? '9999999999',
                'surl' => route('payu.contractor.success'),
                'furl' => route('payu.contractor.failure'),
                'udf1' => $applicationId,
                'udf2' => (string) $lateFee,
                'udf3' => (string) $lateMonths,
                'udf4' => (string) $applicationFee,
                'udf5' => 'CL',
            ];
            $data['hash'] = $payU->generatePaymentHash($data);

            return view('user_login.payments.payu-submit', [
                'data' => $data,
                'url' => config('payu.payment_url'),
            ]);
        } catch (\Exception $e) {
            Log::error('Contractor PayU initiate failed', [
                'message' => $e->getMessage(),
                'application_id' => $request->application_id ?? null,
            ]);

            return $this->renderInitiateError($e->getMessage());
        }
    }

    public function success(Request $request, PayUService $payU, FormAPayUSettlementService $settlement)
    {
        return $this->handlePayUCallback($request, $payU, $settlement, 'surl');
    }

    public function failure(Request $request, PayUService $payU, FormAPayUSettlementService $settlement)
    {
        return $this->handlePayUCallback($request, $payU, $settlement, 'furl');
    }

    private function handlePayUCallback(
        Request $request,
        PayUService $payU,
        FormAPayUSettlementService $settlement,
        string $source
    ) {
        $txnid = trim((string) $request->input('txnid', ''));
        $paymentTxn = null;

        try {
            if ($txnid === '') {
                return $this->renderPaymentFailure(null, 'Payment reference missing from PayU response.');
            }

            $paymentTxn = PaymentTransactionModel::where('txnid', $txnid)->first();
            if (!$paymentTxn) {
                return $this->renderPaymentFailure(null, 'Payment record not found.');
            }

            $terminalStatus = strtoupper((string) $paymentTxn->status);
            if ($terminalStatus === 'SUCCESS') {
                return $this->redirectToPaymentSuccessPopup($paymentTxn);
            }
            if ($terminalStatus === 'FAILED') {
                return $this->renderPaymentFailure(
                    $paymentTxn,
                    $paymentTxn->error_message ?: 'Payment failed or was cancelled at the payment gateway.'
                );
            }

            $callback = $request->all();
            $validation = $payU->validateCallback($callback, $paymentTxn);
            if (! $validation['valid']) {
                Log::warning('Contractor PayU post-back rejected', [
                    'txnid' => $txnid,
                    'source' => $source,
                    'reason' => $validation['reason'],
                ]);

                $paymentTxn->update([
                    'gateway_response' => json_encode(array_merge($callback, [
                        '_callback_source' => $source,
                        '_validation_error' => $validation['reason'],
                    ])),
                ]);

                return $this->renderPaymentFailure(
                    $paymentTxn->fresh(),
                    $validation['reason'] ?? 'Payment response could not be verified.'
                );
            }

            $outcome = $payU->resolveCallbackOutcome($callback);
            $message = $payU->outcomeUserMessage($outcome, $callback);

            if ($outcome === PayUService::OUTCOME_SUCCESS) {
                $paymentTxn->update([
                    'mihpayid' => $callback['mihpayid'] ?? $paymentTxn->mihpayid,
                    'payment_method' => $callback['mode'] ?? $paymentTxn->payment_method,
                    'gateway_response' => json_encode(array_merge($callback, ['_callback_source' => $source])),
                ]);
                $settlement->settleSuccess($paymentTxn, $callback);
                $paymentTxn->refresh();

                return $this->redirectToPaymentSuccessPopup($paymentTxn);
            }

            if ($outcome === PayUService::OUTCOME_FAILED) {
                $paymentTxn->update(array_merge([
                    'status' => 'FAILED',
                    'mihpayid' => $callback['mihpayid'] ?? $paymentTxn->mihpayid,
                    'payment_method' => $callback['mode'] ?? $paymentTxn->payment_method,
                    'gateway_response' => json_encode(array_merge($callback, ['_callback_source' => $source])),
                ], $payU->extractErrorFields($callback)));
                $settlement->resetApplicationForPaymentRetry($paymentTxn);

                return $this->renderPaymentFailure($paymentTxn->fresh(), $message);
            }

            $paymentTxn->update([
                'status' => 'PENDING',
                'mihpayid' => $callback['mihpayid'] ?? $paymentTxn->mihpayid,
                'payment_method' => $callback['mode'] ?? $paymentTxn->payment_method,
                'gateway_response' => json_encode(array_merge($callback, ['_callback_source' => $source])),
                'error_code' => null,
                'error_message' => null,
            ]);

            return $this->renderPaymentPending($paymentTxn->fresh(), $message);
        } catch (\Throwable $e) {
            Log::error('Contractor PayU callback failed', [
                'message' => $e->getMessage(),
                'txnid' => $txnid,
                'source' => $source,
            ]);

            if ($paymentTxn) {
                return $this->renderPaymentFailure(
                    $paymentTxn,
                    'Payment received from PayU, but confirmation failed: ' . $e->getMessage()
                );
            }

            return $this->renderPaymentFailure(null, 'Payment confirmation failed. Please login and check application status.');
        }
    }

    private function redirectToPaymentSuccessPopup(PaymentTransactionModel $paymentTxn)
    {
        $form = EA_Application_model::where('application_id', $paymentTxn->application_id)->first();
        $payload = $this->buildSuccessPayload($paymentTxn, $form);

        $dashboardUrl = route('dashboard', array_merge(
            ['payu_success' => 1],
            $payload
        ));

        return response()
            ->view('user_login.payments.payu-return-bridge', [
                'dashboardUrl' => $dashboardUrl,
                'successPayload' => $payload,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    private function buildSuccessPayload(PaymentTransactionModel $paymentTxn, $form): array
    {
        $applType = strtoupper(trim((string) ($form->appl_type ?? 'N')));
        $formType = match ($applType) {
            'D' => 'Digitization Application',
            'A' => 'Alteration Application',
            'R' => 'Renewal Application',
            default => 'New Application',
        };

        return [
            'application_id' => $paymentTxn->application_id,
            'txnid' => $paymentTxn->txnid,
            'amount' => (int) round((float) $paymentTxn->amount),
            'applicant_name' => $form->applicant_name ?? 'N/A',
            'licence_name' => $form->license_name ?? ($form->form_name ?? 'N/A'),
            'form_type' => $formType,
            'transaction_date' => now()->format('d/m/Y'),
        ];
    }

    private function renderPaymentFailure(?PaymentTransactionModel $payment, string $errorMessage = '')
    {
        $failurePayload = [
            'application_id' => $payment?->application_id,
            'txnid' => $payment?->txnid,
            'message' => $errorMessage !== '' ? $errorMessage : 'Payment failed or was cancelled.',
        ];

        return response()
            ->view('user_login.payments.payu-failure-bridge', [
                'payment' => $payment,
                'errorMessage' => $failurePayload['message'],
                'failurePayload' => $failurePayload,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    private function renderPaymentPending(?PaymentTransactionModel $payment, string $message = '')
    {
        $pendingPayload = [
            'application_id' => $payment?->application_id,
            'txnid' => $payment?->txnid,
            'message' => $message !== '' ? $message : 'Payment is still in progress. Please check your dashboard shortly.',
        ];

        return response()
            ->view('user_login.payments.payu-pending-bridge', [
                'payment' => $payment,
                'pendingMessage' => $pendingPayload['message'],
                'pendingPayload' => $pendingPayload,
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    private function renderInitiateError(string $message)
    {
        return response(
            '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Payment Error</title></head>'
            . '<body style="font-family:sans-serif;padding:32px;">'
            . '<h3>Unable to open payment gateway</h3>'
            . '<p>' . e($message) . '</p>'
            . '<p>You can close this window and try Pay Now again.</p>'
            . '</body></html>',
            400
        );
    }
}
