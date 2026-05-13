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

    .president-dashboard-card {
        border: 1px solid #a7a7a7 !important;
    }

    .president-dashboard-card .card-header {
        border-bottom: 1px solid #a7a7a7 !important;
    }

    .bg-custom-card {
        background-color: rgb(239 241 243) !important;
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
                                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                                        </ol>
                                    </nav>
                                    {{-- <small class="text-muted">Competency / Contractor / Amendments</small> --}}
                                </div>
                            </div>
    
                        </header>
                    </div>
                </div>
                <!--  END BREADCRUMBS  -->

                {{-- <h3 class="mb-0 dashboard_title" style="margin-top: 10px;">Dashboard</h3>
                <small class="text-muted layout-top-spacing">Competency / Contractor / Amendments</small> --}}

                <div class="row dashboard" style="margin-top: 10px;">
                    @if(!empty($competencyCards))
                        <div class="col-xl-4 col-lg-12 mb-4">
                            <div class="card h-100 shadow-none rounded-3 overflow-hidden president-dashboard-card">
                                <div class="card-header d-flex justify-content-between align-items-center border-0 border-bottom bg-info">
                                    <h5 class="mb-0 text-white">Competency Certificates</h5>
                                </div>
                                <div class="card-body" style="padding: 7px 10px;">
                                    @foreach(collect($competencyCards) as $summary)
                                        @php
                                            $badgeClass = $formColors[$summary['color_code'] ?? ''] ?? 'bg-secondary';
                                        @endphp
                                        <div class="d-flex align-items-center px-3 py-2 mb-1 rounded-3 bg-custom-card">
                                            @php
                                                // For contractor Form A cards, clicking "NEW/RENEWAL" should filter
                                                // contractor list by appl_type (N/R) based on staff role.
                                                $isFormAContractor = str_contains(mb_strtolower($summary['licence_name'] ?? ''), 'contractor')
                                                    && strtoupper($summary['form_name'] ?? '') === 'FORM A';

                                                $roleName = $staff->name ?? '';
                                                if ($isFormAContractor && in_array($roleName, ['Supervisor', 'Supervisor2'], true)) {
                                                    $newHref = route('admin.view_form', ['type' => 'A', 'form_type' => 'N']);
                                                    $renewHref = route('admin.view_form', ['type' => 'A', 'form_type' => 'R']);
                                                } elseif ($isFormAContractor && $roleName === 'Accountant') {
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
                                                $rawCode = strtoupper((string) ($summary['form_name'] ?? ''));
                                                $compactCode = preg_replace('/[^A-Z0-9]/', '', $rawCode);
                                                $formCode = $compactCode !== '' ? substr($compactCode, -1) : '?';
                                            @endphp
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center">
                                                    <div class="me-3">
                                                        <div class="rounded-3 d-flex align-items-center justify-content-center {{ $badgeClass }}" style="width: 44px; height: 44px;">
                                                            <span class="fw-bold text-white">{{ $formCode }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1">
                                                        <div class="fw-semibold">{{ $summary['licence_name'] ?? 'Unknown Licence' }}</div>
                                                        <small class="text-muted d-block mb-1">{{ $summary['form_name'] ?? '-' }}</small>
                                                        <div class="d-flex flex-wrap align-items-center gap-3 mt-1">
                                                            <a href="{{ $newHref }}"
                                                               class="badge outline-badge-info fw-semibold text-decoration-none">
                                                                NEW <span class="ms-1 fw-bold text-danger">{{ $summary['new_count'] ?? 0 }}</span>
                                                            </a>
                                                            <a href="{{ $renewHref }}"
                                                               class="badge outline-badge-info fw-semibold text-decoration-none">
                                                                RENEWAL <span class="ms-1 fw-bold text-danger">{{ $summary['renewal_count'] ?? 0 }}</span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    @if(!empty($contractorCards))
                    <div class="col-xl-4 col-lg-12 mb-4">
                        <div class="card h-100 shadow-none rounded-3 overflow-hidden president-dashboard-card">
                            <div class="card-header d-flex justify-content-between align-items-center border-0 border-bottom bg-info">
                                <h5 class="mb-0 text-white">Contractor Licences</h5>
                            </div>
                            <div class="card-body" style="padding: 7px 10px;">
                                @foreach(collect($contractorCards) as $summary)
                                <div class="d-flex align-items-center px-3 py-2 mb-1 rounded-3 bg-custom-card">
                                    @php
                                        // For contractor Form A cards, clicking "New" should go to the
                                        // existing Form A applications list (/admin/view_form/A).
                                        $isFormAContractor = str_contains(mb_strtolower($summary['licence_name'] ?? ''), 'contractor')
                                            && strtoupper($summary['form_name'] ?? '') === 'FORM A';

                                        $newHref = $isFormAContractor
                                            ? route('admin.view_form', ['type' => 'A'])
                                            : route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'N']);
                                        $renewHref = $isFormAContractor
                                            ? route('admin.view_form', ['type' => 'A'])
                                            : route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'R']);
                                    @endphp
                                    @php
                                        $rawCode = strtoupper((string) ($summary['form_name'] ?? ''));
                                        $compactCode = preg_replace('/[^A-Z0-9]/', '', $rawCode);
                                        $formCode = $compactCode !== '' ? substr($compactCode, -1) : '?';
                                    @endphp
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary" style="width: 44px; height: 44px;">
                                                    <span class="fw-bold text-white">{{ $formCode }}</span>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-semibold">{{ $summary['licence_name'] ?? 'Unknown Licence' }}</div>
                                                <small class="text-muted d-block mb-1">{{ $summary['form_name'] ?? '-' }}</small>
                                                <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                                    <a href="{{ $newHref }}"
                                                       class="badge outline-badge-info fw-semibold text-decoration-none">
                                                        NEW <span class="ms-1 fw-bold text-danger">{{ $summary['new_count'] ?? 0 }}</span>
                                                    </a>
                                                    <a href="{{ $renewHref }}"
                                                       class="badge outline-badge-info fw-semibold text-decoration-none">
                                                        RENEWAL <span class="ms-1 fw-bold text-danger">{{ $summary['renewal_count'] ?? 0 }}</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(!empty($amendmentCards))
                    <div class="col-xl-4 col-lg-12 mb-4">
                        <div class="card h-100 shadow-none rounded-3 overflow-hidden president-dashboard-card">
                            <div class="card-header d-flex justify-content-between align-items-center border-0 border-bottom bg-info">
                                <h5 class="mb-0 text-white">Amendments</h5>
                            </div>
                            <div class="card-body" style="padding: 7px 10px;">
                                @foreach(collect($amendmentCards) as $summary)
                                <div class="d-flex align-items-center px-3 py-2 mb-1 rounded-3 bg-custom-card">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-semibold">{{ $summary['licence_name'] ?? 'Unknown Licence' }}</div>
                                                <small class="text-muted">{{ $summary['form_name'] ?? '-' }}</small>
                                            </div>
                                            <div class="text-end">
                                                <div class="d-flex align-items-center gap-2">
                                                    @php
                                                        $newHref = route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'N']);
                                                        $renewHref = route('admin.view_applications', ['form_id' => $summary['id'], 'form_type' => 'R']);
                                                    @endphp
                                                    <a href="{{ $newHref }}"
                                                       class="badge outline-badge-info fw-semibold text-decoration-none">
                                                        NEW <span class="ms-1 fw-bold text-danger">{{ $summary['new_count'] ?? 0 }}</span>
                                                    </a>
                                                    <a href="{{ $renewHref }}"
                                                       class="badge outline-badge-info fw-semibold text-decoration-none">
                                                        RENEWAL <span class="ms-1 fw-bold text-danger">{{ $summary['renewal_count'] ?? 0 }}</span>
                                                    </a>
                                                </div>
                                            </div>  
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @if(in_array($staff->name ?? '', ['Secretary', 'President'], true) && isset($recieved_apps) && isset($inprogress))
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-info">
                                <h5 class="mb-0 text-white">In Progress Applications (Competency Certificates / Contractor Licences / Amendments)</h5>
                            </div>
                            <div class="card-body" style="padding: 5px 15px;">
                                <div class="table-responsive">
                                    <table id="secretary-inprogress-table" class="table dt-table-hover table-striped table-bordered zero-config" style="width:100%">
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
                                                
                                                <td>{{ $i }}</td>
                                               @php
                                            $badge_class = 'badge-secondary';
                                            $fn = strtoupper($row->form_name ?? '');

                                            if ($fn == 'S') $badge_class = 'badge-warning';
                                            elseif (in_array($fn, ['EA', 'SA'])) $badge_class = 'badge-success';
                                            elseif ($fn == 'W') $badge_class = 'badge-danger';
                                            elseif ($fn == 'WH') $badge_class = 'badge-warning';
                                            elseif ($fn == 'P') $badge_class = 'badge-info';
                                            elseif (in_array($fn, ['B', 'EB', 'SB'])) $badge_class = 'badge-primary';

                                            
                                            if (($row->return_flag ?? null) == 1 && $row->application_status != 'RET') {
                                                $received_from = 'Applicant';
                                            }
                                            
                                            else {
                                                $received_from = '';
                                                if (($row->processed_by ?? '') === 'S') $received_from = 'Supervisor';
                                                elseif (($row->processed_by ?? '') === 'A') $received_from = 'Accountant';
                                                elseif (($row->processed_by ?? '') === 'SE') $received_from = 'Secretary';
                                                elseif (($row->processed_by ?? '') === 'PR') $received_from = 'President';
                                            }

                                            // Pending with
                                            $pending_with = '';
                                             if (($row->return_flag ?? null) == 1 && $row->application_status == 'RET') {
                                                $pending_with = 'Applicant';
                                            }
                                            
                                            else {
                                                if (empty($row->processed_by)) $pending_with = 'Supervisor';
                                                elseif (($row->processed_by ?? '') === 'S') $pending_with = 'Accountant';
                                                elseif (($row->processed_by ?? '') === 'A') $pending_with = 'Secretary';
                                                elseif (($row->processed_by ?? '') === 'SE') $pending_with = 'President';
                                            }
                                        @endphp
                                               <td>

                                               @if($row->form_name == 'P')
                                                    <a href=" route('admin.application_details_formp', ['applicant_id' => $row->application_id])">
                                                        
                                                        {{ $row->application_id }}
                                                    </a>
                                                @elseif($row->form_name == 'A')
                                                     <a href="{{ route('admin.applicants_detail_forma', ['applicant_id' => $row->application_id]) }}">
                                                {{ $row->application_id }}
                                                    </a>
                                                @else
                                                    <a href=" route('admin.application_details', ['applicant_id' => $row->application_id])">
                                                        
                                                        {{ $row->application_id }}
                                                    </a>

                                               @endif
                                                  

                                                    @if(($row->return_flag ?? null) == 1 && $row->application_status != 'RET')
                                                        <span class="badge badge-danger ml-1">Resubmitted</span>
                                                    @elseif(($row->return_flag ?? null) == 1 && $row->application_status == 'RET')
                                                        <span class="badge badge-danger ml-1">Returned</span>
                                                    @endif
                                                </td>
                                                <td><span class="badge {{ $badge_class }}">FORM {{ $row->form_name }}</span></td>
                                                <td>{{ format_date_other($row->created_at) }}</td>
                                                <td>{{ $received_from }}</td>
                                                <td>{{ format_date_other($row->updated_at) }}</td>
                                                {{-- <td>{{ calculateDaysDifference($row->updated_at) }} Days</td> --}}
                                                <td>{{ calculateDaysDifference($row->created_at) }} Days</td>
                                                <td>{{ $pending_with }}</td>
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
    @include('admin.include.footer');
</div>
