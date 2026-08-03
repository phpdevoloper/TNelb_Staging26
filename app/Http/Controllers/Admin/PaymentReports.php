<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CC_Payments;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentReports extends Controller
{
    public function index(Request $request)
    {
        $query = CC_Payments::query()
            ->leftJoin('payment_transactions as pt', 'pt.txnid', '=', 'cc_payments.transaction_id')
            ->select([
                'cc_payments.p_id',
                'cc_payments.login_id',
                'cc_payments.application_id',
                'cc_payments.transaction_id',
                'cc_payments.app_type',
                'cc_payments.form_name',
                'cc_payments.cert_name',
                'cc_payments.application_fee',
                'cc_payments.late_fee',
                'cc_payments.late_months',
                'cc_payments.amount_paid',
                'cc_payments.payment_status',
                'cc_payments.payment_mode',
                'cc_payments.transaction_date',
                'cc_payments.created_at',
                'pt.mihpayid',
                'pt.gateway',
                'pt.status as gateway_status',
            ]);

        if ($request->filled('from_date')) {
            $query->whereDate('cc_payments.transaction_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('cc_payments.transaction_date', '<=', $request->to_date);
        }

        if ($request->filled('form_name')) {
            $query->whereRaw('UPPER(TRIM(cc_payments.form_name)) = ?', [strtoupper(trim($request->form_name))]);
        }

        if ($request->filled('app_type')) {
            $query->whereRaw('UPPER(TRIM(cc_payments.app_type)) = ?', [strtoupper(trim($request->app_type))]);
        }

        if ($request->filled('payment_status')) {
            $query->whereRaw('LOWER(TRIM(cc_payments.payment_status)) = ?', [strtolower(trim($request->payment_status))]);
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($inner) use ($q) {
                $inner->where('cc_payments.application_id', 'ilike', "%{$q}%")
                    ->orWhere('cc_payments.transaction_id', 'ilike', "%{$q}%")
                    ->orWhere('cc_payments.login_id', 'ilike', "%{$q}%")
                    ->orWhere('pt.mihpayid', 'ilike', "%{$q}%");
            });
        }

        $summaryQuery = clone $query;
        $summary = $summaryQuery
            ->reorder()
            ->select([
                DB::raw('COUNT(cc_payments.p_id) as total_count'),
                DB::raw('COALESCE(SUM(cc_payments.amount_paid), 0) as total_amount'),
            ])
            ->first();

        $payments = $query
            ->orderByDesc('cc_payments.p_id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.payment_reports.index', [
            'payments' => $payments,
            'summary' => $summary,
            'filters' => [
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'form_name' => $request->form_name,
                'app_type' => $request->app_type,
                'payment_status' => $request->payment_status,
                'q' => $request->q,
            ],
        ]);
    }
}
