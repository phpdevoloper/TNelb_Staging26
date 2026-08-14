<?php

namespace App\Http\Controllers;

use App\Models\CC_Forms_Meta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\PayUService;
use App\Models\PaymentTransactionModel;
use App\Services\PayUPaymentSettlementService;
use Illuminate\Support\Facades\Log;

class PayUPaymentController extends BaseController
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

            $applicationId = $request->application_id;

            $form = CC_Forms_Meta::findByApplicationId($applicationId);

            if (!$form) {
                return back()->with('error', 'Application not found');
            }

            $amount = (float) $request->amount;
            $lateFee = (int) round((float) ($request->input('lateFee', 0)));
            $lateMonths = (int) ($request->input('lateMonths', 0));
            $applicationFee = $request->filled('actual_fees')
                ? (int) round((float) $request->input('actual_fees'))
                : max(0, (int) round($amount) - $lateFee);

            $txnid = 'TN' . strtoupper(Str::random(18));
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

            // Pay Now means the applicant has submitted; renewal draft saves may still have app_status D.
            if (strtoupper(trim((string) ($form->app_status ?? ''))) === 'D') {
                $form->update(['app_status' => 'P']);
            }

            $data = [
                'key' => config('payu.key'),
                'txnid' => $txnid,
                'amount' => number_format($amount, 2, '.', ''),
                'productinfo' => 'TNELB Application Fee - ' . $applicationId,
                'firstname' => $form->applicant_name ?? 'Applicant',
                'email' => $form->applicant_email ?? (auth()->user()->email ?? 'noreply@example.com'),
                'phone' => $form->mobile ?? '9999999999',
                'surl' => route('payu.success'),
                'furl' => route('payu.failure'),
                'udf1' => $applicationId,
                'udf2' => (string) $lateFee,
                'udf3' => (string) $lateMonths,
                'udf4' => (string) $applicationFee,
            ];
            $data['hash'] = $payU->generatePaymentHash($data);

            return view('user_login.payments.payu-submit', [
                'data' => $data,
                'url' => config('payu.payment_url'),
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function success(Request $request, PayUService $payU, PayUPaymentSettlementService $settlement)
    {
        return $this->handlePayUCallback($request, $payU, $settlement, 'surl');
    }

    public function failure(Request $request, PayUService $payU, PayUPaymentSettlementService $settlement)
    {
        return $this->handlePayUCallback($request, $payU, $settlement, 'furl');
    }

    public function status(Request $request)
    {
        $request->validate([
            'application_id' => 'required|string',
        ]);

        $applicationId = trim((string) $request->application_id);
        $form = CC_Forms_Meta::findByApplicationId($applicationId);
        if (!$form) {
            return response()->json(['status' => 'not_found'], 404);
        }

        $loginId = (string) (auth()->user()->login_id ?? '');
        if ($loginId === '' || (string) ($form->login_id ?? '') !== $loginId) {
            return response()->json(['status' => 'forbidden'], 403);
        }

        $paymentTxn = PaymentTransactionModel::where('application_id', $applicationId)
            ->orderByDesc('id')
            ->first();

        if (!$paymentTxn) {
            return response()->json(['status' => 'pending']);
        }

        $gatewayStatus = strtoupper((string) $paymentTxn->status);
        if ($gatewayStatus === 'FAILED') {
            return response()->json([
                'status' => 'failed',
                'txnid' => $paymentTxn->txnid,
                'message' => $paymentTxn->error_message,
            ]);
        }

        if ($gatewayStatus !== 'SUCCESS') {
            return response()->json([
                'status' => 'pending',
                'txnid' => $paymentTxn->txnid,
                'gateway_status' => $paymentTxn->status,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'payload' => $this->buildSuccessPayload($paymentTxn, $form),
        ]);
    }

    /**
     * User-triggered PayU verify for pending / failed gateway rows (dashboard button).
     */
    public function checkStatus(Request $request, PayUPaymentSettlementService $settlement)
    {
        $request->validate([
            'application_id' => 'required|string',
        ]);

        $applicationId = trim((string) $request->application_id);
        $form = CC_Forms_Meta::findByApplicationId($applicationId);
        if (!$form) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'Application not found.',
            ], 404);
        }

        $loginId = (string) (auth()->user()->login_id ?? '');
        if ($loginId === '' || (string) ($form->login_id ?? '') !== $loginId) {
            return response()->json(['status' => 'forbidden', 'message' => 'Access denied.'], 403);
        }

        $paymentTxn = PaymentTransactionModel::where('application_id', $applicationId)
            ->orderByDesc('id')
            ->first();

        if (!$paymentTxn) {
            return response()->json([
                'status' => 'not_found',
                'message' => 'No payment attempt found for this application.',
            ], 404);
        }

        if (strtoupper((string) $paymentTxn->status) === 'SUCCESS') {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment is already recorded as successful.',
                'payload' => $this->buildSuccessPayload($paymentTxn, $form),
            ]);
        }

        if (strtoupper((string) $paymentTxn->status) === 'FAILED') {
            $settlement->resetApplicationForPaymentRetry($paymentTxn);

            return response()->json([
                'status' => 'failed',
                'message' => $paymentTxn->error_message ?: 'Payment failed. You can pay again from your application.',
                'txnid' => $paymentTxn->txnid,
            ]);
        }

        $result = $settlement->refreshPendingFromPayU($paymentTxn);
        $paymentTxn = $result['payment'];
        $form = CC_Forms_Meta::findByApplicationId($applicationId);

        if ($result['outcome'] === PayUService::OUTCOME_SUCCESS) {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment confirmed successfully.',
                'payload' => $this->buildSuccessPayload($paymentTxn, $form),
            ]);
        }

        if ($result['outcome'] === PayUService::OUTCOME_FAILED) {
            return response()->json([
                'status' => 'failed',
                'message' => $result['message'] ?? 'Payment failed. You can pay again from your application.',
                'txnid' => $paymentTxn->txnid,
            ]);
        }

        
        return response()->json([
            'status' => 'pending',
            'message' => $result['message'] ?? 'Payment is still in progress at the bank. Please try again later.',
            'txnid' => $paymentTxn->txnid,
        ]);
    }

    private function handlePayUCallback(
        Request $request,
        PayUService $payU,
        PayUPaymentSettlementService $settlement,
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
                Log::warning('PayU post-back rejected', [
                    'txnid' => $txnid,
                    'source' => $source,
                    'reason' => $validation['reason'],
                    'status' => $callback['status'] ?? null,
                    'unmappedstatus' => $callback['unmappedstatus'] ?? null,
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
            Log::error('PayU callback handler failed', [
                'message' => $e->getMessage(),
                'txnid' => $txnid,
                'source' => $source,
                'trace' => $e->getTraceAsString(),
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
        $form = CC_Forms_Meta::findByApplicationId((string) $paymentTxn->application_id);
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
            'licence_name' => $form->certificate_name ?? ($form->form_name ?? 'N/A'),
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
}
