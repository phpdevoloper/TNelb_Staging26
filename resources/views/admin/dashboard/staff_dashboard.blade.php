@include('admin.include.top')
@include('admin.include.header')
@include('admin.include.navbar')
<style>
    thead th {
        background-color: #004185 !important;
        color: #ffffff !important;
        font-weight: 600;
        border-bottom: 2px solid #dee2e6;
    }

    tbody td {
        background-color: #ffffff;
    }

    tbody tr:nth-child(even) td {
        background-color: #f1f1f1;
    }

    tbody tr:hover td {
        background-color: #e9ecef;
    }

    .table:not(.dataTable).table-bordered thead tr th{
        border-radius: unset;
    }

    .badgeWH{
        color: #fff;
        background-color: #10298f;
    }

    #dashboard-inprogress-table td.corner-ribbon-cell {
        position: relative;
        overflow: hidden;
        vertical-align: middle !important;
        min-width: 3.35rem;
        text-align: center;
        padding-top: 1rem !important;
        padding-bottom: 0.75rem !important;
    }
    #dashboard-inprogress-table .corner-ribbon-resubmitted {
        position: absolute;
        top: 17px;
        left: -24px;
        width: 99px;
        padding: 5px 0 4px;
        box-sizing: content-box;
        background: #2e7d32;
        color: #fff;
        font-size: 0.52rem;
        font-weight: 800;
        letter-spacing: 0.04em;
        line-height: 1.1;
        text-transform: uppercase;
        text-align: center;
        white-space: nowrap;
        transform: rotate(-45deg);
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.22);
        pointer-events: none;
        z-index: 2;
    }
    #dashboard-inprogress-table .sno-num-inprog {
        position: relative;
        z-index: 1;
        display: inline-block;
    }

    /* DataTables sort arrows — white on dark blue header */
    #dashboard-inprogress-table.dataTable thead > tr > th.sorting:before,
    #dashboard-inprogress-table.dataTable thead > tr > th.sorting:after,
    #dashboard-inprogress-table.dataTable thead > tr > th.sorting_asc:before,
    #dashboard-inprogress-table.dataTable thead > tr > th.sorting_asc:after,
    #dashboard-inprogress-table.dataTable thead > tr > th.sorting_desc:before,
    #dashboard-inprogress-table.dataTable thead > tr > th.sorting_desc:after,
    #dashboard-inprogress-table.dataTable thead > tr > th.sorting_asc_disabled:before,
    #dashboard-inprogress-table.dataTable thead > tr > th.sorting_asc_disabled:after,
    #dashboard-inprogress-table.dataTable thead > tr > th.sorting_desc_disabled:before,
    #dashboard-inprogress-table.dataTable thead > tr > th.sorting_desc_disabled:after {
        color: #ffffff !important;
        opacity: 1 !important;
        filter: brightness(0) invert(1);
    }
</style>

<div id="content" class="main-content">
    <div class="container-fluid px-2 px-sm-3">

        <div class="layout-px-spacing">
            <div class="middle-content container-xxl p-0">
                <!--  BEGIN BREADCRUMBS  -->
                <div class="secondary-nav">
                    <div class="breadcrumbs-container" data-page-heading="Analytics">
                        <header class="header navbar navbar-expand-sm">
                            <a href="#" class="btn-toggle sidebarCollapse" data-placement="bottom">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu">
                                    <line x1="3" y1="12" x2="21" y2="12"></line>
                                    <line x1="3" y1="6" x2="21" y2="6"></line>
                                    <line x1="3" y1="18" x2="21" y2="18"></line>
                                </svg>
                            </a>
                            <div class="d-flex breadcrumb-content">
                                <div class="page-header d-flex flex-column">
                                    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                                        <ol class="breadcrumb mb-0">
                                            <li class="breadcrumb-item">
                                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item">
                                                <span class="mx-1">/</span>
                                            </li>
                                            <li class="breadcrumb-item active" aria-current="page">
                                                Dashboard 
                                            </li>
                                        </ol>
                                    </nav>
                                </div>
                            </div>
                        </header>
                    </div>
                </div>
                <!--  END BREADCRUMBS  -->
    
                <div class="row layout-top-spacing dashboard">
                    @php
                        $summaries = collect($assignedFormSummary ?? []);
                    @endphp

                    @if($summaries->isEmpty())
                        <div class="col-12">
                            <div class="alert alert-info mb-0">
                                No forms are currently assigned to you.
                            </div>
                        </div>
                    @else

                        <div class="col-12 mb-3">
                            <h3 class="mb-0 fw-bold text-center">
                                Pending Competency Certificates / Contractor Licences / Amendments
                            </h3>
                        </div>

                        @if(!empty($competencyCards))
                            <div class="col-12 mb-4">
                                <div class="rounded border bg-white shadow-sm p-3 p-md-4">
                                    <div class="mb-3">
                                        <div class="bg-light shadow-sm py-2 px-3 mb-3">
                                            <p class="mb-0 fw-semibold fs-4">
                                                Competency Certificates
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row g-2 g-md-3">
                                        @foreach(collect($competencyCards) as $summary)
                                        @php
                                            $badgeClass = $formColors[$summary['color_code'] ?? ''] ?? 'bg-secondary';
                                        @endphp
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-3 min-w-0">
                                            <div class="p-3 rounded text-white {{ $badgeClass }} h-100 d-flex flex-column justify-content-between shadow-sm min-h-0">
                                                <div class="mb-2 mb-md-3 overflow-hidden">
                                                    <h5 class="mb-1 fw-bold text-white text-break">
                                                        {{ $summary['licence_name'] ?? 'Unknown Licence' }}
                                                    </h5>
                                                    <span class="text-uppercase small fw-semibold d-block text-white">
                                                        {{ $summary['form_name'] ?? '-' }}
                                                    </span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1 gap-sm-2 justify-content-center">
                                                    @php
                                                        // For contractor Form A cards, clicking "New/Renewal" should
                                                        // filter contractor list by appl_type (N/R) for the staff role.
                                                        $isFormAContractor = str_contains(mb_strtolower($summary['licence_name'] ?? ''), 'contractor')
                                                            && strtoupper($summary['form_name'] ?? '') === 'FORM A';
                                                        $roleName = $staff->name ?? '';
                                                        if ($isFormAContractor && in_array($roleName, ['Supervisor', 'Supervisor2'], true)) {
                                                            $newHref = route('admin.view_form', ['type' => 'A', 'form_type' => 'N']);
                                                            $renewHref = route('admin.view_form', ['type' => 'A', 'form_type' => 'R']);
                                                        } elseif ($isFormAContractor && $roleName === 'Assistant Secretary') {
                                                            $newHref = route('admin.view_forma_pending', ['type' => 'A', 'form_type' => 'N']);
                                                            $renewHref = route('admin.view_forma_pending', ['type' => 'A', 'form_type' => 'R']);
                                                        } elseif ($isFormAContractor && $roleName === 'Secretary') {
                                                            $newHref = route('admin.view_sec_forma_pending', ['type' => 'A', 'form_type' => 'N']);
                                                            $renewHref = route('admin.view_sec_forma_pending', ['type' => 'A', 'form_type' => 'R']);
                                                        } elseif ($isFormAContractor && $roleName === 'President') {
                                                            $newHref = route('admin.view_form', ['type' => 'A', 'form_type' => 'N']);
                                                            $renewHref = route('admin.view_form', ['type' => 'A', 'form_type' => 'R']);
                                                        } else {
                                                            $newHref = route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'N']);
                                                            $renewHref = route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'R']);
                                                        }
                                                    @endphp
                                                    <a href="{{ $newHref }}" class="badge rounded-pill bg-white text-dark px-2 px-sm-3 py-1 py-sm-2 d-inline-flex align-items-center gap-1 shadow-sm text-decoration-none">
                                                        <span class="small fw-semibold text-uppercase">New</span>
                                                        <span class="badge rounded-pill {{ ($summary['new_count'] ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary text-white' }}">{{ $summary['new_count'] ?? 0 }}</span>
                                                    </a>
                                                    <a href="{{ $renewHref }}" class="badge rounded-pill bg-white text-dark px-2 px-sm-3 py-1 py-sm-2 d-inline-flex align-items-center gap-1 shadow-sm text-decoration-none">
                                                        <span class="small fw-semibold text-uppercase">Renewal</span>
                                                        <span class="badge rounded-pill {{ ($summary['renewal_count'] ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary text-white' }}">{{ $summary['renewal_count'] ?? 0 }}</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($contractorCards))
                            <div class="col-12 mb-4">
                                <div class="rounded border bg-white shadow-sm p-3 p-md-4">
                                    <div class="mb-3">
                                        <div class="bg-light shadow-sm py-2 px-3 mb-3">
                                            <p class="mb-0 fw-semibold fs-4">
                                                Contractor Licences
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row g-2 g-md-3">
                                        @foreach(collect($contractorCards) as $summary)
                                        @php
                                            $badgeClass = $formColors[$summary['color_code'] ?? ''] ?? 'bg-secondary';
                                        @endphp
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-3 min-w-0">
                                            <div class="p-3 rounded text-white {{ $badgeClass }} h-100 d-flex flex-column justify-content-between shadow-sm min-h-0">
                                                <div class="mb-2 mb-md-3 overflow-hidden">
                                                    <h5 class="mb-1 fw-bold text-white text-break">
                                                        {{ $summary['licence_name'] ?? 'Unknown Licence' }}
                                                    </h5>
                                                    <span class="text-uppercase small fw-semibold d-block text-white">
                                                        {{ $summary['form_name'] ?? '-' }}
                                                    </span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1 gap-sm-2 justify-content-center">
                                                    @php
                                                        // For contractor Form A cards, route based on staff role:
                                                        // - Supervisor -> /admin/view_form/A  (SupervisorController@view_forma)
                                                        // - Assistant Secretary -> /admin/view_forma_pending/A (AuditorController@view_forma_pending)
                                                        // - Secretary  -> /admin/view_sec_forma_pending/A (SecretaryController@view_sec_forma_pending)
                                                        $isFormAContractor = str_contains(mb_strtolower($summary['licence_name'] ?? ''), 'contractor')
                                                            && strtoupper($summary['form_name'] ?? '') === 'FORM A';
                                                        $roleName = $staff->name ?? '';
                                                        if ($isFormAContractor && in_array($roleName, ['Supervisor'], true)) {
                                                            $contractorNewHref = route('admin.view_form', ['type' => 'A', 'form_type' => 'N']);
                                                            $contractorRenewHref = route('admin.view_form', ['type' => 'A', 'form_type' => 'R']);
                                                        } elseif ($isFormAContractor && $roleName === 'Assistant Secretary') {
                                                            $contractorNewHref = route('admin.view_forma_pending', ['type' => 'A']);
                                                            $contractorRenewHref = route('admin.view_forma_pending', ['type' => 'A']);
                                                        } elseif ($isFormAContractor && $roleName === 'Secretary') {
                                                            $contractorNewHref = route('admin.view_sec_forma_pending', ['type' => 'A']);
                                                            $contractorRenewHref = route('admin.view_sec_forma_pending', ['type' => 'A']);
                                                        } elseif ($isFormAContractor && $roleName === 'President') {
                                                            $contractorNewHref = route('admin.view_form', ['type' => 'A', 'form_type' => 'N']);
                                                            $contractorRenewHref = route('admin.view_form', ['type' => 'A', 'form_type' => 'R']);
                                                        } else {
                                                            $contractorNewHref = route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'N']);
                                                            $contractorRenewHref = route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'R']);
                                                        }
                                                    @endphp
                                                    <a href="{{ $contractorNewHref }}" class="badge rounded-pill bg-white text-dark px-2 px-sm-3 py-1 py-sm-2 d-inline-flex align-items-center gap-1 shadow-sm text-decoration-none">
                                                        <span class="small fw-semibold text-uppercase">New</span>
                                                        <span class="badge rounded-pill {{ ($summary['new_count'] ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary text-white' }}">{{ $summary['new_count'] ?? 0 }}</span>
                                                    </a>
                                                    <a href="{{ $contractorRenewHref }}" class="badge rounded-pill bg-white text-dark px-2 px-sm-3 py-1 py-sm-2 d-inline-flex align-items-center gap-1 shadow-sm text-decoration-none">
                                                        <span class="small fw-semibold text-uppercase">Renewal</span>
                                                        <span class="badge rounded-pill {{ ($summary['renewal_count'] ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary text-white' }}">{{ $summary['renewal_count'] ?? 0 }}</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($amendmentCards))
                            <div class="col-12">
                                <div class="rounded border bg-white shadow-sm p-3 p-md-4">
                                    <div class="mb-3">
                                        <div class="bg-light shadow-sm py-2 px-3 mb-3">
                                            <p class="mb-0 fw-semibold fs-4">
                                                Amendments
                                            </p>
                                        </div>
                                    </div>
                                    <div class="row g-2 g-md-3">
                                        @foreach(collect($amendmentCards) as $summary)
                                        @php
                                            $badgeClass = $formColors[$summary['color_code'] ?? ''] ?? 'bg-secondary';
                                        @endphp
                                        <div class="col-12 col-sm-6 col-md-4 col-xl-3 min-w-0">
                                            <div class="p-3 rounded text-white {{ $badgeClass }} h-100 d-flex flex-column justify-content-between shadow-sm min-h-0">
                                                <div class="mb-2 mb-md-3 overflow-hidden">
                                                    <h5 class="mb-1 fw-bold text-white text-break">
                                                        {{ $summary['licence_name'] ?? 'Unknown Licence' }}
                                                    </h5>
                                                    <span class="text-uppercase small fw-semibold d-block text-white">
                                                        {{ $summary['form_name'] ?? '-' }}
                                                    </span>
                                                </div>
                                                <div class="d-flex flex-wrap gap-1 gap-sm-2 justify-content-center">
                                                    <a href="{{ route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'N']) }}" class="badge rounded-pill bg-white text-dark px-2 px-sm-3 py-1 py-sm-2 d-inline-flex align-items-center gap-1 shadow-sm text-decoration-none">
                                                        <span class="small fw-semibold text-uppercase">New</span>
                                                        <span class="badge rounded-pill {{ ($summary['new_count'] ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary text-white' }}">{{ $summary['new_count'] ?? 0 }}</span>
                                                    </a>
                                                    <a href="{{ route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'R']) }}" class="badge rounded-pill bg-white text-dark px-2 px-sm-3 py-1 py-sm-2 d-inline-flex align-items-center gap-1 shadow-sm text-decoration-none">
                                                        <span class="small fw-semibold text-uppercase">Renewal</span>
                                                        <span class="badge rounded-pill {{ ($summary['renewal_count'] ?? 0) > 0 ? 'bg-success text-white' : 'bg-secondary text-white' }}">{{ $summary['renewal_count'] ?? 0 }}</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                @if(in_array($staff->name ?? '', ['Secretary', 'President'], true) && isset($recieved_apps) && isset($inprogress))
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-info">
                                <h5 class="mb-0 text-white">In Progress Applications (Competency Certificates / Contractor Licences / Amendments)</h5>
                            </div>
                            <div class="card-body" style="padding: 5px 15px;">
                                <div class="table-responsive">
                                    <table id="dashboard-inprogress-table" class="table dt-table-hover table-striped table-bordered zero-config" style="width:100%">
                                        <thead class="text-center">
                                            <tr>
                                                <th>S.No</th>
                                                <th>Application ID</th>
                                                <th>Application Type</th>
                                                <th>Date of Apps</th>
                                                <th>Received from</th>
                                                <th>Received Date of Apps</th>
                                                {{-- <th>No.of.day Pending</th> --}}
                                                <th>Total No.of.day</th>
                                                <th>Pending With</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $i = 1; @endphp
                                            @foreach ($inprogress as $row)
                                            <tr>
                                                @php
                                                    $badge_class = 'badge-secondary';
                                                    $fn = strtoupper($row->form_name ?? '');
                                                    if ($fn == 'S') $badge_class = 'badge-warning';
                                                    elseif (in_array($fn, ['EA', 'SA'])) $badge_class = 'badge-success';
                                                    elseif ($fn == 'W') $badge_class = 'badge-danger';
                                                    elseif ($fn == 'WH') $badge_class = 'badge-warning';
                                                    elseif ($fn == 'P') $badge_class = 'badge-info';
                                                    elseif (in_array($fn, ['B', 'EB', 'SB'])) $badge_class = 'badge-primary';
                                                    // Received from: last processor on application row
                                                    $received_from = '';
                                                    if (($row->processed_by ?? '') === 'S' || ($row->processed_by ?? '') === 'S2') {
                                                        $received_from = 'Supervisor';
                                                    } elseif (($row->processed_by ?? '') === 'A') {
                                                        $received_from = 'Assistant Secretary';
                                                    } elseif (($row->processed_by ?? '') === 'SE') {
                                                        $received_from = 'Secretary';
                                                    } elseif (($row->processed_by ?? '') === 'PR') {
                                                        $received_from = 'President';
                                                    }
                                                    // Pending with: latest workflow forwarded_to (Supervisor→Secretary leaves processed_by as S)
                                                    $pending_with = '';
                                                    $row_status = strtoupper($row->status ?? '');
                                                    $forwardRoleRaw = trim((string) ($row->latest_forward_role_name ?? ''));
                                                    $forwardNorm = strtolower($forwardRoleRaw);
                                                    if ($row_status === 'QU') {
                                                        $pending_with = 'Applicant';
                                                    } elseif ($forwardRoleRaw !== '') {
                                                        if (str_contains($forwardNorm, 'secretary')) {
                                                            $pending_with = 'Secretary';
                                                        } elseif (str_contains($forwardNorm, 'president')) {
                                                            $pending_with = 'President';
                                                        } elseif (str_contains($forwardNorm, 'Assistant Secretary')) {
                                                            $pending_with = 'Assistant Secretary';
                                                        } elseif (str_contains($forwardNorm, 'supervisor')) {
                                                            $pending_with = 'Supervisor';
                                                        } else {
                                                            $pending_with = $forwardRoleRaw;
                                                        }
                                                    } elseif (empty($row->processed_by)) {
                                                        $pending_with = 'Supervisor';
                                                    } elseif (($row->processed_by ?? '') === 'S' || ($row->processed_by ?? '') === 'S2') {
                                                        $pending_with = 'Assistant Secretary';
                                                    } elseif (($row->processed_by ?? '') === 'A') {
                                                        $pending_with = 'Secretary';
                                                    } elseif (($row->processed_by ?? '') === 'SE') {
                                                        $pending_with = 'President';
                                                    }
                                                    $showResubmittedRibbon = !empty($row->has_return_history)
                                                        && $row_status !== 'QU'
                                                        && \in_array($pending_with, ['Secretary', 'President'], true);
                                                @endphp
                                                <td class="{{ $showResubmittedRibbon ? 'corner-ribbon-cell' : '' }}">
                                                    @if ($showResubmittedRibbon)
                                                        <span class="corner-ribbon-resubmitted">Resubmitted</span>
                                                    @endif
                                                    <span class="sno-num-inprog">{{ $i }}</span>
                                                </td>
                                                <td><a href="{{ ($row->form_name ?? '') == 'P' ? route('admin.application_details_formp', ['applicant_id' => $row->application_id]) : route('admin.applicants_detail', ['applicant_id' => $row->application_id]) }}">{{ $row->application_id }}</a></td>
                                                <td><span class="badge {{ $badge_class }}">FORM {{ $row->form_name }}</span></td>
                                                <td>{{ format_date_other($row->created_at) }}</td>
                                                <td>{{ $received_from }}</td>
                                                <td>{{ format_date_other($row->updated_at) }}</td>
                                                {{-- <td>{{ calculateDaysDifference($row->updated_at) }} Days</td> --}}
                                                <td>{{ calculateDaysDifference($row->created_at) }} Days</td>
                                                <td>
                                                    @if($pending_with === 'Applicant')
                                                        <span class="badge badge-warning">Applicant</span>
                                                    @else
                                                        {{ $pending_with }}
                                                    @endif
                                                </td>
                                            </tr>
                                            @php $i++; @endphp
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
            </div>
        </div>
    </div>
    @include('admin.include.footer');
</div>
