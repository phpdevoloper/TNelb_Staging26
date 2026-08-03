@include('admin.include.top')
@include('admin.include.header')
@include('admin.include.navbar')
<style>
    .tab-content { padding: 0px 20px; }
    thead th {
        background-color: #004185 !important;
        color: #ffffff !important;
        font-weight: 600;
        white-space: nowrap;
    }
    .form-select, .form-control { border: 1px solid #004185; }
    .summary-card {
        background: #f8fafc;
        border: 1px solid #dbe3ef;
        border-radius: 8px;
        padding: 14px 16px;
        height: 100%;
    }
    .summary-card .label { color: #64748b; font-size: 13px; margin-bottom: 4px; }
    .summary-card .value { color: #0f172a; font-size: 22px; font-weight: 700; }
</style>

<div id="content" class="main-content">
    <div class="layout-px-spacing">
        <div class="middle-content container-xxl p-0">
            <div class="secondary-nav">
                <div class="breadcrumbs-container" data-page-heading="Analytics">
                    <header class="header navbar navbar-expand-sm">
                        <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </a>
                    </header>
                </div>
            </div>

            <div class="row layout-top-spacing">
                <div class="col-lg-12 layout-spacing">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header applicant_details">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <h4>Payment Reports</h4>
                                </div>
                            </div>
                        </div>

                        <div class="widget-content widget-content-area">
                            <form method="GET" action="{{ route('admin.payment_reports') }}" class="row g-3 mb-3">
                                <div class="col-md-2">
                                    <label class="form-label">From Date</label>
                                    <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">To Date</label>
                                    <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Form</label>
                                    <select name="form_name" class="form-select">
                                        <option value="">All</option>
                                        @foreach (['S', 'W', 'WH', 'P'] as $form)
                                            <option value="{{ $form }}" @selected(($filters['form_name'] ?? '') === $form)>FORM {{ $form }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">App Type</label>
                                    <select name="app_type" class="form-select">
                                        <option value="">All</option>
                                        @foreach (['N' => 'New', 'R' => 'Renewal', 'D' => 'Digitisation', 'A' => 'Alteration'] as $code => $label)
                                            <option value="{{ $code }}" @selected(($filters['app_type'] ?? '') === $code)>{{ $label }} ({{ $code }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Status</label>
                                    <select name="payment_status" class="form-select">
                                        <option value="">All</option>
                                        <option value="success" @selected(($filters['payment_status'] ?? '') === 'success')>Success</option>
                                        <option value="failed" @selected(($filters['payment_status'] ?? '') === 'failed')>Failed</option>
                                        <option value="pending" @selected(($filters['payment_status'] ?? '') === 'pending')>Pending</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Search</label>
                                    <input type="text" name="q" class="form-control" placeholder="App / Txn / Login / PayU ID" value="{{ $filters['q'] ?? '' }}">
                                </div>
                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Filter</button>
                                    <a href="{{ route('admin.payment_reports') }}" class="btn btn-outline-secondary">Reset</a>
                                </div>
                            </form>

                            <div class="row mb-3">
                                <div class="col-md-3 mb-2">
                                    <div class="summary-card">
                                        <div class="label">Total Payments</div>
                                        <div class="value">{{ number_format((int) ($summary->total_count ?? 0)) }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <div class="summary-card">
                                        <div class="label">Total Amount (Rs.)</div>
                                        <div class="value">{{ number_format((float) ($summary->total_amount ?? 0), 0) }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Application ID</th>
                                            <th>Login ID</th>
                                            <th>Form</th>
                                            <th>Cert</th>
                                            <th>App Type</th>
                                            <th>Transaction ID</th>
                                            <th>PayU ID</th>
                                            <th>Mode</th>
                                            <th>App Fee</th>
                                            <th>Late Fee</th>
                                            <th>Amount Paid</th>
                                            <th>Status</th>
                                            <th>Gateway</th>
                                            <th>Txn Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($payments as $index => $row)
                                            <tr>
                                                <td>{{ $payments->firstItem() + $index }}</td>
                                                <td>{{ $row->application_id }}</td>
                                                <td>{{ $row->login_id }}</td>
                                                <td>{{ $row->form_name }}</td>
                                                <td>{{ $row->cert_name }}</td>
                                                <td>{{ $row->app_type }}</td>
                                                <td>{{ $row->transaction_id }}</td>
                                                <td>{{ $row->mihpayid ?: '-' }}</td>
                                                <td>{{ $row->payment_mode }}</td>
                                                <td>{{ number_format((float) $row->application_fee, 0) }}</td>
                                                <td>{{ number_format((float) $row->late_fee, 0) }}</td>
                                                <td><strong>{{ number_format((float) $row->amount_paid, 0) }}</strong></td>
                                                <td>
                                                    @php $st = strtolower(trim((string) $row->payment_status)); @endphp
                                                    @if ($st === 'success')
                                                        <span class="badge bg-success">Success</span>
                                                    @elseif (in_array($st, ['failed', 'failure'], true))
                                                        <span class="badge bg-danger">Failed</span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">{{ $row->payment_status ?: 'N/A' }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $row->gateway_status ?: ($row->gateway ?: '-') }}</td>
                                                <td>{{ $row->transaction_date ? \Carbon\Carbon::parse($row->transaction_date)->format('d-m-Y') : '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="15" class="text-center text-muted py-4">No payment records found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="d-flex justify-content-end mt-3">
                                {{ $payments->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.include.footer')
