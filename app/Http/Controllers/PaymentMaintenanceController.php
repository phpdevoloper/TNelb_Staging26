<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentMaintenanceController extends BaseController
{
    public function index(Request $request)
    {
        $applicationId = trim((string) $request->query('application_id', ''));
        $meta = null;

        if ($applicationId !== '') {
            $meta = DB::table('cc_form_s_meta')
                ->where('application_id', $applicationId)
                ->first();
        }

        $ccPaymentsQuery = DB::table('cc_payments')->orderByDesc('p_id');
        $paymentTransactionsQuery = DB::table('payment_transactions')->orderByDesc('id');

        if ($applicationId !== '') {
            $ccPaymentsQuery->where('application_id', $applicationId);
            $paymentTransactionsQuery->where('application_id', $applicationId);
        }

        $ccPayments = $ccPaymentsQuery->paginate(25, ['*'], 'cc_page');
        $ccPayments->appends($request->except('cc_page'));

        $paymentTransactions = $paymentTransactionsQuery->paginate(25, ['*'], 'pt_page');
        $paymentTransactions->appends($request->except('pt_page'));

        return view('user_login.payments.maintenance', [
            'applicationId' => $applicationId,
            'meta' => $meta,
            'ccPayments' => $ccPayments,
            'paymentTransactions' => $paymentTransactions,
        ]);
    }

    public function updatePaymentStatus(Request $request)
    {
        $validated = $request->validate([
            'application_id' => ['required', 'string', 'max:50'],
            'payment_status' => ['required', Rule::in(['Y', 'N'])],
        ]);

        $applicationId = trim($validated['application_id']);
        $meta = DB::table('cc_form_s_meta')
            ->where('application_id', $applicationId)
            ->first();

        if (!$meta) {
            return redirect()
                ->route('payment.maintenance', ['application_id' => $applicationId])
                ->with('error', 'Application not found in cc_form_s_meta.');
        }

        DB::table('cc_form_s_meta')
            ->where('application_id', $applicationId)
            ->update([
                'payment_status' => $validated['payment_status'],
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('payment.maintenance', ['application_id' => $applicationId])
            ->with('success', 'payment_status updated to ' . $validated['payment_status'] . ' for ' . $applicationId . '.');
    }

    public function deleteCcPayments(Request $request)
    {
        $validated = $request->validate([
            'application_id' => ['required', 'string', 'max:50'],
        ]);

        $applicationId = trim($validated['application_id']);
        $deleted = DB::table('cc_payments')
            ->where('application_id', $applicationId)
            ->delete();

        return redirect()
            ->route('payment.maintenance', ['application_id' => $applicationId])
            ->with('success', "Deleted {$deleted} row(s) from cc_payments for {$applicationId}.");
    }

    public function deletePaymentTransactions(Request $request)
    {
        $validated = $request->validate([
            'application_id' => ['required', 'string', 'max:50'],
        ]);

        $applicationId = trim($validated['application_id']);
        $deleted = DB::table('payment_transactions')
            ->where('application_id', $applicationId)
            ->delete();

        return redirect()
            ->route('payment.maintenance', ['application_id' => $applicationId])
            ->with('success', "Deleted {$deleted} row(s) from payment_transactions for {$applicationId}.");
    }
}
