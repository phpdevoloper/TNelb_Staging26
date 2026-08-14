@include('include.header')

<style>
    .payment-maint-card {
        background: #fff;
        border: 1px solid #dbe3ef;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .payment-maint-card h5 { margin-bottom: 16px; color: #004185; }
    thead th {
        background-color: #004185 !important;
        color: #fff !important;
        font-weight: 600;
        white-space: nowrap;
        font-size: 13px;
    }
    tbody td { font-size: 13px; }
    .danger-zone {
        border: 1px solid #f1c0c0;
        background: #fff6f6;
        border-radius: 8px;
        padding: 16px;
    }
    .test-banner {
        background: #fff3cd;
        border: 1px solid #ffecb5;
        color: #664d03;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 20px;
    }
</style>

<section class="">
    <div class="container">
        <ul id="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><span class="fa fa-home"></span> Dashboard</a></li>
            <li><a href="#"><span class="fa fa-wrench"></span> Payment Maintenance (Test)</a></li>
        </ul>
    </div>
</section>

<section class="apply-form">
    <div class="auto-container">
        <div class="wrapper-box">
            <div class="row">
                <div class="col-lg-12">
                    <div class="apply-card apply-card-info">
                        <div class="apply-card-header" style="background-color: #70c6ef !important;">
                            <h4 class="mb-0">Payment Maintenance (Testing Only)</h4>
                        </div>
                        <div class="apply-card-body p-4">
                            <div class="test-banner">
                                <strong>Testing tool:</strong> View all payment records, filter by Application ID, update Form S payment status, or delete records.
                            </div>

                            @if (session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif
                            @if (session('error'))
                                <div class="alert alert-danger">{{ session('error') }}</div>
                            @endif

                            <form method="GET" action="{{ route('payment.maintenance') }}" class="row g-3 mb-4">
                                <div class="col-md-8">
                                    <label class="form-label">Filter by Application ID <span class="text-muted">(optional)</span></label>
                                    <input type="text" name="application_id" class="form-control" placeholder="e.g. SC261111132 — leave empty to show all" value="{{ $applicationId }}">
                                </div>
                                <div class="col-md-4 d-flex align-items-end gap-2">
                                    <button type="submit" class="btn btn-primary">Search / Filter</button>
                                    <a href="{{ route('payment.maintenance') }}" class="btn btn-outline-secondary">Show All</a>
                                </div>
                            </form>

                            @if ($applicationId !== '')
                                @if (!$meta)
                                    <div class="alert alert-warning">
                                        No record found in <strong>cc_form_s_meta</strong> for Application ID: <strong>{{ $applicationId }}</strong>.
                                        Payment table data below is still filtered by this Application ID.
                                    </div>
                                @else
                                    <div class="payment-maint-card">
                                        <h5>cc_form_s_meta</h5>
                                        <div class="row mb-3">
                                            <div class="col-md-3"><strong>Application ID:</strong> {{ $meta->application_id }}</div>
                                            <div class="col-md-3"><strong>Applicant:</strong> {{ $meta->applicant_name ?? 'N/A' }}</div>
                                            <div class="col-md-2"><strong>App Type:</strong> {{ $meta->appl_type ?? 'N/A' }}</div>
                                            <div class="col-md-2"><strong>Status:</strong> {{ $meta->app_status ?? ($meta->status ?? 'N/A') }}</div>
                                            <div class="col-md-2">
                                                <strong>Payment Status:</strong>
                                                @php $ps = strtoupper(trim((string) ($meta->payment_status ?? ''))); @endphp
                                                @if ($ps === 'Y')
                                                    <span class="badge bg-success">Y (Paid)</span>
                                                @elseif ($ps === 'N')
                                                    <span class="badge bg-warning text-dark">N (Not Paid)</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ $meta->payment_status ?: 'N/A' }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('payment.maintenance.update_status') }}" class="row g-3">
                                            @csrf
                                            <input type="hidden" name="application_id" value="{{ $applicationId }}">
                                            <div class="col-md-4">
                                                <label class="form-label">Update payment_status</label>
                                                <select name="payment_status" class="form-select" required>
                                                    <option value="Y" @selected(strtoupper(trim((string) ($meta->payment_status ?? ''))) === 'Y')>Y — Paid</option>
                                                    <option value="N" @selected(strtoupper(trim((string) ($meta->payment_status ?? ''))) === 'N')>N — Not Paid / Pending</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 d-flex align-items-end">
                                                <button type="submit" class="btn btn-success">Update payment_status</button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                                @if ($ccPayments->total() > 0 || $paymentTransactions->total() > 0)
                                    <div class="danger-zone mb-4">
                                        <strong class="text-danger">Delete actions</strong> apply only to Application ID: <strong>{{ $applicationId }}</strong>
                                    </div>
                                @endif
                            @endif

                            <div class="payment-maint-card">
                                <h5>
                                    cc_payments
                                    ({{ number_format($ccPayments->total()) }} total{{ $applicationId !== '' ? ' for ' . $applicationId : '' }})
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>p_id</th>
                                                <th>Application ID</th>
                                                <th>Login ID</th>
                                                <th>Form</th>
                                                <th>App Type</th>
                                                <th>Transaction ID</th>
                                                <th>App Fee</th>
                                                <th>Late Fee</th>
                                                <th>Amount Paid</th>
                                                <th>Status</th>
                                                <th>Mode</th>
                                                <th>Txn Date</th>
                                                <th>Created</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($ccPayments as $row)
                                                <tr>
                                                    <td>{{ $row->p_id }}</td>
                                                    <td>{{ $row->application_id }}</td>
                                                    <td>{{ $row->login_id ?? '-' }}</td>
                                                    <td>{{ $row->form_name ?? '-' }}</td>
                                                    <td>{{ $row->app_type ?? '-' }}</td>
                                                    <td>{{ $row->transaction_id ?? '-' }}</td>
                                                    <td>{{ number_format((float) ($row->application_fee ?? 0), 0) }}</td>
                                                    <td>{{ number_format((float) ($row->late_fee ?? 0), 0) }}</td>
                                                    <td><strong>{{ number_format((float) ($row->amount_paid ?? 0), 0) }}</strong></td>
                                                    <td>{{ $row->payment_status ?? '-' }}</td>
                                                    <td>{{ $row->payment_mode ?? '-' }}</td>
                                                    <td>{{ !empty($row->transaction_date) ? \Carbon\Carbon::parse($row->transaction_date)->format('d-m-Y') : '-' }}</td>
                                                    <td>{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="13" class="text-center text-muted py-3">No cc_payments records found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end">
                                    {{ $ccPayments->links('pagination::bootstrap-5') }}
                                </div>
                                @if ($applicationId !== '' && $ccPayments->total() > 0)
                                    <form method="POST" action="{{ route('payment.maintenance.delete_cc_payments') }}" class="mt-2" onsubmit="return confirm('Delete all cc_payments rows for {{ $applicationId }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="application_id" value="{{ $applicationId }}">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete all cc_payments for {{ $applicationId }}</button>
                                    </form>
                                @endif
                            </div>

                            <div class="payment-maint-card">
                                <h5>
                                    payment_transactions
                                    ({{ number_format($paymentTransactions->total()) }} total{{ $applicationId !== '' ? ' for ' . $applicationId : '' }})
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Application ID</th>
                                                <th>txnid</th>
                                                <th>Amount</th>
                                                <th>App Fee</th>
                                                <th>Late Fee</th>
                                                <th>Months</th>
                                                <th>Status</th>
                                                <th>Gateway</th>
                                                <th>Error Code</th>
                                                <th>Error Message</th>
                                                <th>Method</th>
                                                <th>PayU ID</th>
                                                <th>Created</th>
                                                <th>Updated</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($paymentTransactions as $row)
                                                <tr>
                                                    <td>{{ $row->id }}</td>
                                                    <td>{{ $row->application_id ?? '-' }}</td>
                                                    <td>{{ $row->txnid ?? '-' }}</td>
                                                    <td>{{ number_format((float) ($row->amount ?? 0), 0) }}</td>
                                                    <td>{{ number_format((float) ($row->application_fee ?? 0), 0) }}</td>
                                                    <td>{{ number_format((float) ($row->late_fee ?? 0), 0) }}</td>
                                                    <td>{{ $row->late_months ?? '-' }}</td>
                                                    <td>{{ $row->status ?? '-' }}</td>
                                                    <td>{{ $row->gateway ?? '-' }}</td>
                                                    <td>{{ $row->error_code ?? '-' }}</td>
                                                    <td>{{ $row->error_message ?? '-' }}</td>
                                                    <td>{{ $row->payment_method ?? '-' }}</td>
                                                    <td>{{ $row->mihpayid ?? '-' }}</td>
                                                    <td>{{ !empty($row->created_at) ? \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i') : '-' }}</td>
                                                    <td>{{ !empty($row->updated_at) ? \Carbon\Carbon::parse($row->updated_at)->format('d-m-Y H:i') : '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="13" class="text-center text-muted py-3">No payment_transactions records found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end">
                                    {{ $paymentTransactions->links('pagination::bootstrap-5') }}
                                </div>
                                @if ($applicationId !== '' && $paymentTransactions->total() > 0)
                                    <form method="POST" action="{{ route('payment.maintenance.delete_payment_transactions') }}" class="mt-2" onsubmit="return confirm('Delete all payment_transactions rows for {{ $applicationId }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="application_id" value="{{ $applicationId }}">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete all payment_transactions for {{ $applicationId }}</button>
                                    </form>
                                @endif
                            </div>

                            @if ($applicationId !== '' && ($ccPayments->total() > 0 || $paymentTransactions->total() > 0))
                                <div class="danger-zone">
                                    <strong class="text-danger">Note:</strong>
                                    Deleting payment records does not change <code>cc_form_s_meta.payment_status</code>.
                                    Update payment status separately if needed.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@include('include.footer')
