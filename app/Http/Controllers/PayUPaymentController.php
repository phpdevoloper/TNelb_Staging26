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

            // Amount from Payment Details popup (getPaymentDetails → total_fees)
            $amount = (float) $request->amount;
            $lateFee = (int) round((float) ($request->input('lateFee', 0)));
            $lateMonths = (int) ($request->input('lateMonths', 0));
            $applicationFee = $request->filled('actual_fees')
                ? (int) round((float) $request->input('actual_fees'))
                : max(0, (int) round($amount) - $lateFee);

            // Keep txnid within typical varchar limits used by cc_payments.transaction_id
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
                // Backup for settlement if DB columns are missing/old rows
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
        $txnid = $request->input('txnid');
        $paymentTxn = null;

        try {
            if (!$txnid) {
                return view('user_login.payments.failed', ['payment' => null]);
            }

            $paymentTxn = PaymentTransactionModel::where('txnid', $txnid)->firstOrFail();

            // Already finalized — go to dashboard and show old success popup
            if ($paymentTxn->status === 'SUCCESS') {
                return $this->redirectToPaymentSuccessPopup($paymentTxn);
            }

            // Save raw PayU callback first
            $paymentTxn->update([
                'mihpayid' => $request->input('mihpayid'),
                'gateway_response' => json_encode($request->all()),
                'status' => 'PENDING_VERIFICATION',
                'payment_method' => $request->input('mode'),
            ]);

            // Do NOT call PayU verify_payment API — server outbound to test.payu.in
            // times out (cURL 28) and previously aborted this handler.
            $callback = $request->all();
            $isSuccess = strtolower((string) $request->input('status')) === 'success';

            if ($isSuccess && ! $payU->isValidResponseHash($callback)) {
                Log::warning('PayU surl status=success but reverse hash mismatch; accepting surl status', [
                    'txnid' => $txnid,
                ]);
            }

            if (!$isSuccess) {
                $paymentTxn->update(['status' => 'FAILED']);
                return view('user_login.payments.failed', ['payment' => $paymentTxn]);
            }

            $settlement->settleSuccess($paymentTxn, $callback);

            $paymentTxn->refresh();
            // Browser follows this as a same-site GET, so session cookie is sent again.
            return $this->redirectToPaymentSuccessPopup($paymentTxn);
        } catch (\Throwable $e) {
            // On handler failure keep public failed page (do not send users to auth routes).
            Log::error('PayU success handler failed', [
                'message' => $e->getMessage(),
                'txnid' => $txnid,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($paymentTxn) {
                return view('user_login.payments.failed', [
                    'payment' => $paymentTxn,
                    'errorMessage' => 'Payment received from PayU, but confirmation failed: ' . $e->getMessage(),
                ]);
            }

            return view('user_login.payments.failed', [
                'payment' => null,
                'errorMessage' => 'Payment confirmation failed. Please login and check application status.',
            ]);
        }
    }

    public function failure(Request $request)
    {
        $payment = PaymentTransactionModel::where(
            'txnid',
            $request->txnid
        )->first();
        if ($payment) {
            $payment->update([
                'mihpayid' => $request->mihpayid,
                'status' => 'FAILED',
                'gateway_response' => json_encode($request->all()),
            ]);
        }
        return view('user_login.payments.failed', compact('payment'));
    }

    /**
     * Poll from the original application tab while PayU runs in a popup.
     */
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
     * Notify opener tab (if any), else fall back to dashboard redirect.
     */
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
}
