<style>
    .table-login-compact th,
    .table-login-compact td {
        padding: 7px 6px;
        font-size: 0.8125rem;
        line-height: 1.4;
    }
    .table-login-compact thead th {
        padding: 4px 6px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .table-login-compact .btn-sm {
        padding: 0.15rem 0.4rem;
        font-size: 0.72rem;
    }
    .table-login-compact p {
        margin-bottom: 0;
    }
    /* Returned ribbon: hidden overflow keeps stripe inside this row (no overlap above); extra top padding fits -32° band */
    .table-login-compact thead th:nth-child(1),
    .table-login-compact tbody td:nth-child(1) {
        width: 3.15rem;
        max-width: 3.15rem;
        box-sizing: border-box;
        padding-left: 2px !important;
        padding-right: 2px !important;
    }
    .table-login-compact td.return-cell {
        position: relative;
        overflow: hidden;
        text-align: center;
        vertical-align: middle !important;
        min-width: 0;
        padding: 1.35rem 2px 0.4rem !important;
    }
    .table-login-compact .return-ribbon-wrapper {
        position: absolute;
        top: 0.62rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1;
        pointer-events: none;
    }
    .table-login-compact .return-ribbon {
        display: block;
        width: 76px;
        padding: 3px 0;
        margin: 0 auto;
        background: #6f42c1;
        color: #fff;
        font-size: 6.5px;
        font-weight: 800;
        letter-spacing: 0.04em;
        line-height: 1.15;
        text-align: center;
        text-transform: uppercase;
        transform: rotate(-32deg);
        transform-origin: center center;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
        white-space: nowrap;
    }
    .table-login-compact .return-sno-num {
        position: relative;
        z-index: 1;
        display: block;
        margin-top: 0.4rem;
        font-weight: 600;
        line-height: 1.2;
    }
    /* Form Type — slightly wider for licence name + form code */
    .table-login-compact thead th:nth-child(2),
    .table-login-compact tbody td:nth-child(2) {
        width: 21%;
        box-sizing: border-box;
    }
    /* Payment Status — compact (Success / Pending only) */
    .table-login-compact thead th:nth-child(6),
    .table-login-compact tbody td:nth-child(6) {
        width: 5.25rem;
        max-width: 5.25rem;
        box-sizing: border-box;
        padding-left: 3px !important;
        padding-right: 3px !important;
    }
    /* Acknowledgement Download — only two small links; keep column narrow */
    .table-login-compact {
        table-layout: fixed;
        width: 100%;
    }
    .table-login-compact thead th:nth-child(7),
    .table-login-compact tbody td:nth-child(7) {
        width: 9rem;
        max-width: 9rem;
        box-sizing: border-box;
        padding-left: 3px !important;
        padding-right: 3px !important;
    }
    .table-login-compact thead th:nth-child(7) {
        font-size: 0.68rem;
        line-height: 1.2;
        font-weight: 600;
    }
    .table-login-compact tbody td:nth-child(7) {
        text-align: center;
        vertical-align: middle;
    }
    .table-login-compact tbody td:nth-child(7) a {
        display: inline-block;
        margin: 0 1px;
        white-space: nowrap;
    }
</style>
<table class="table-login table-login-compact">
    <thead>
        <tr>
            <th>S.No</th>
            <th>Form Type</th>
            <th>Application ID</th>
            <th>Applied On</th>
            <th>Application Status</th>
            <th>Payment Status</th>
            <th>Acknowledgement<br>Download</th>
            <th>Certificate Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($paginatedData as $index => $workflow)
        @php
            $sts = data_get($workflow, 'status') ?? data_get($workflow, 'application_status') ?? data_get($workflow, 'app_status');
        @endphp
        <tr @if($sts == 'QU') class="return-row" @endif>
            <td @if($sts == 'QU') class="return-cell" @endif>
                @if($sts == 'QU')
                    <div class="return-ribbon-wrapper">
                        <span class="return-ribbon">RETURNED</span>
                    </div>
                    <span class="return-sno-num">{{ $paginatedData->firstItem() + $index }}</span>
                @else
                    {{ $paginatedData->firstItem() + $index }}
                @endif
            </td>
            <td>
              @php
             $licence_name_present = DB::table('mst_licences')

            ->where('form_code', $workflow->form_name)
             ->first();
                @endphp    
                {{ $licence_name_present->licence_name }} <br>
            [Form {{ strtoupper($workflow->form_name ?? 'NA') }}]
            </td>
            <td>{{ $workflow->application_id ?? 'NA' }}</td>
            <td>{{ isset($workflow->created_at) ? \Carbon\Carbon::parse($workflow->created_at)->format('d/m/Y') : 'NA' }}</td>

            <!-- Application Status -->
            <td>
                @if ($workflow->payment_status == 'draft')
                    @php
                        $view_page =
                            isset($workflow->appl_type) && $workflow->appl_type == 'R'
                                ? 'renew_form'
                                : (in_array(strtoupper($workflow->form_name), ['P']) ? 'edit-application_p' : 'edit-application');
                    @endphp
                    <a href="{{ route($view_page, ['application_id' => $workflow->application_id]) }}">
                        <button class="btn btn-info btn-sm"><i class="fa fa-pencil"></i> Draft</button>
                    </a>
                @else
                    @if ($sts == 'P')
                        <span class="btn btn-sm btn-primary">Submitted</span>
                    @elseif ($sts == 'F')
                        <span class="btn btn-warning btn-sm">In Progress</span>
                    @elseif ($sts == 'QU')
                        <span class="btn btn-sm bg-return">Returned</span>
                    @elseif ($sts == 'RJ')
                        <span class="btn btn-danger btn-sm">Rejected</span>
                    @elseif ($sts == 'RE')
                        <span class="btn btn-primary btn-sm">Resubmitted</span>
                    @elseif ($sts == 'A' && !empty($workflow->license_number))
                        <span class="btn btn-sm btn-success">Completed</span>
                    @elseif ($sts == 'A')
                        <span class="btn btn-sm btn-success">Completed</span>
                    @else
                        <span class="btn btn-warning btn-sm">In Progress</span>
                    @endif
                @endif
            </td>

            <!-- Payment Status -->
            <td>
                @if ($workflow->payment_status == 'payment')
                    <p class="text-success"><strong>Success</strong></p>
                @else
                    <p class="text-danger"><strong>Pending</strong></p>
                @endif
            </td>

            <!-- Acknowledgement Download -->
            <td>
                @if ($workflow->payment_status == 'draft')
                    <p>-</p>
                @else
                    @if ($workflow->form_name == 'P')
                        <a href="{{ route('generatePDFFormP-ta.pdf', ['login_id' => $workflow->application_id]) }}" target="_blank">
                            <i class="fa fa-file-pdf-o" style="font-size:14px;color:red"></i>
                            <span style="font-size: x-small;">தமிழ்</span>
                        </a>
                        <a href="{{ route('generateformP.pdf', ['login_id' => $workflow->application_id]) }}" target="_blank">
                            <i class="fa fa-file-pdf-o" style="font-size:14px;color:red"></i>
                            <span style="font-size: x-small;">English</span>
                        </a>
                    @else
                        <a href="{{ route('generate.tamil.pdf', ['login_id' => $workflow->application_id]) }}" target="_blank">
                            <i class="fa fa-file-pdf-o" style="font-size:14px;color:red"></i>
                            <span style="font-size: x-small;">தமிழ்</span>
                        </a>

                        <a href="{{ route('generate.pdf', ['login_id' => $workflow->application_id]) }}" target="_blank">
                            <i class="fa fa-file-pdf-o" style="font-size:14px;color:red"></i>
                            <span style="font-size: x-small;">English</span>
                        </a>
                    @endif
                @endif
            </td>

            <!-- License Number -->
            <td>
                @if (!empty($workflow->license_number) && $sts == 'A')
                    <a href="{{ route('admin.getLicenceDoc.pdf', ['application_id' => $workflow->application_id]) }}" target="_blank" 
                        data-bs-toggle="tooltip" data-bs-placement="top" title="View Licence Details">
                        <span class="badge badge-info">{{ $workflow->license_number }}</span>
                    </a><br>

                    @if ($workflow->form_name == 'P')
                        {{-- Form P: single encrypted PDF (English + Tamil) --}}
                        <a href="{{ route('admin.formp.licence.en', ['application_id' => $workflow->application_id]) }}" target="_blank" data-bs-toggle="tooltip" data-bs-placement="top" title="Download Form P Licence (English & Tamil)">
                            <i class="fa fa-file-pdf-o" style="font-size:14px;color:red"></i>
                            <span class="badge outline-badge-info" style="font-size:10px;">View Certificate</span>
                        </a>
                    @else
                        {{-- <a href="{{ route('admin.competency-certificate-tamil.pdf', ['application_id' => $workflow->application_id]) }}" target="_blank"  data-bs-toggle="tooltip" data-bs-placement="top" title="Download Licence (Tamil)">
                            <i class="fa fa-file-pdf-o" style="font-size:14px;color:red"></i>
                            <span class="badge outline-badge-info" style="font-size:10px;">தமிழ்</span>
                        </a> --}}
                        <a href="{{ route('admin.generateLicensePDF', ['application_id' => $workflow->application_id]) }}" target="_blank"  data-bs-toggle="tooltip" data-bs-placement="top" title="Download Licence (English)">
                            <i class="fa fa-file-pdf-o" style="font-size:14px;color:red"></i>
                            <span class="badge outline-badge-info" style="font-size:10px;">View Certificate</span>
                        </a>
                    @endif
                    <br>

                    @if (!empty($workflow->renewals))
                        <span class="text-muted" style="font-size: 12px;">
                            Renewed {{ count($workflow->renewals) }} times
                        </span>
                        <br>
                    @endif

                    @if (!empty($workflow->renewal_application_id))
                        <strong>Renewal Application</strong><br>
                        ID :
                        <a href="{{ route('generate.pdf', ['login_id' => $workflow->renewal_application_id]) }}"
                            target="_blank" class="text-success">
                            {{ $workflow->renewal_application_id }}
                        </a>
                    @else
                        @php
                            $isFormW = strtoupper((string) ($workflow->form_name ?? '')) === 'W';
                            $hasCertificateC = false;

                            if ($isFormW && !empty($workflow->login_id)) {
                                $hasCertificateC = DB::table('tnelb_application_tbl as ta')
                                    ->leftJoin('tnelb_license as l', 'l.application_id', '=', 'ta.application_id')
                                    ->leftJoin('tnelb_renewal_license as rl', 'rl.application_id', '=', 'ta.application_id')
                                    ->where('ta.login_id', $workflow->login_id)
                                    ->where('ta.form_name', 'S')
                                    ->where('ta.status', 'A')
                                    ->where(function ($query) {
                                        $query->whereNotNull('l.license_number')
                                            ->orWhereNotNull('rl.license_number');
                                    })
                                    ->exists();
                            }
                        @endphp
                        @if ($workflow->is_under_validity_period && !($isFormW && $hasCertificateC))
                            <a href="{{ route('renew_form', ['application_id' => $workflow->application_id]) }}"
                                class="text-primary">
                                (Apply for renewal)
                            </a>
                        @elseif ($workflow->is_under_validity_period && $isFormW && $hasCertificateC)
                            <span class="text-danger" style="font-size: 12px;">(Renewal not allowed: Certificate C already issued)</span>
                        @endif
                    @endif
                @elseif (!empty($workflow->renewal_application_id))
                    <strong>Renewal Application</strong><br>
                    ID : <span class="text-success">{{ $workflow->renewal_application_id }}</span>
                @elseif($sts == 'QU')
                    <a href="{{ route(in_array(strtoupper($workflow->form_name ?? ''), ['P']) ? 'edit-application_p' : 'edit_returned_application', ['application_id' => $workflow->application_id]) }}">
                        <button class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i> Edit</button>
                    </a>
                @else
                    <p class="text-primary">NA</p>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="mt-3">
    {{ $paginatedData->links('pagination::bootstrap-5') }}
</div>
