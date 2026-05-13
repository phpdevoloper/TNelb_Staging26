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
                                <div class="page-header">
    
                                    <div class="page-title">
                                    </div>
    
                                    <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="#"></a></li>
    
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

                {{-- <div class="row layout-top-spacing">
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary">
                                <h5 class="mb-0 text-white">Dashboard For Pendancy Report</h5>
                            </div>
                            <div class="card-body">
                                <div class="simple-tab">
                                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Received</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">In Progress</button>
                                        </li>
                                    </ul>
    
                                    <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                                            <table id="zero-config" class="table dt-table-hover table-striped">
                                            <thead class="text-center">
                                                <th>S.No</th>
                                                <th>Application Type</th>
                                                <th>Application ID</th>
                                                <th>Date of Application</th>
                                                <th>Total No.of.day Pending</th>
                                                <th>Pending With</th>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $i=1;
                                                @endphp
                                                @foreach ($recieved_apps as $row)
                                                <tr>
                                                <td>{{ $i }}</td>
                                                @php
                                                    if ($row->form_name == 'S') {
                                                        $badge_class = "badge-warning";
                                                    }elseif ($row->form_name == 'EA') {
                                                        $badge_class = "badge-success";
                                                    }elseif ($row->form_name == 'W') {
                                                        $badge_class = "badge-danger";
                                                    }elseif ($row->form_name == 'WH') {
                                                        $badge_class = "badge-warning";
                                                    }elseif ($row->form_name == 'P') {
                                                        $badge_class = "badge-info";
                                                    }
                                                @endphp
                                                <td><span class="badge {{ $badge_class }}">FORM {{ $row->form_name }}</span></td>
                                                <td><a href="{{ $row->form_name == 'P' ? route('admin.application_details_formp', ['applicant_id' => $row->application_id]) : route('admin.applicants_detail', ['applicant_id' => $row->application_id]) }}">{{ $row->application_id }}</a></td>
                                                <td>{{ format_date_other($row->created_at) }}</td>
                                                <td>{{ calculateDaysDifference($row->created_at) }} Days</td>
                                                <td>    @if ($row->processed_by == null)
                                                          {{ 'Supervisor' }}
                                                        @elseif ($row->processed_by == "S")
                                                            {{ 'Accountant' }}
                                                        @elseif ($row->processed_by == "A")
                                                            {{ 'Secretary' }}
                                                        @elseif ($row->processed_by == "SE")
                                                            {{ 'President' }}
                                                        @endif
                                                </td>
                                                </tr>
                                                @php
                                                    $i++;
                                                @endphp
                                                @endforeach
                                            </tbody>
                                            </table>
                                        </div>
                                        <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                                            <table class="table dt-table-hover zero-config table-striped" style="width:100%">
                                                <thead class="text-center">
                                                    <th>S.No</th>
                                                    <th>Application ID</th>
                                                    <th>Application Type</th>
                                                    <th>Date of Apps</th>
                                                    <th>Received from</th>
                                                    <th>Received Date of Apps</th>
                                                    <th>No.of.day Pending</th>
                                                    <th>Total No.of.day</th>
                                                    <th>Pending With</th>
                                                </thead>
                                                <tbody>
                                                    @php
                                                    $i=1;
                                                    @endphp
                                                    @foreach ($inprogress as $row)
                                                    <tr>
                                                    <td>{{ $i }}</td>
                                                    @php
                                                        if ($row->form_name == 'S') {
                                                            $badge_class = "badge-warning";
                                                        }elseif ($row->form_name == 'EA') {
                                                            $badge_class = "badge-success";
                                                        }elseif ($row->form_name == 'W') {
                                                            $badge_class = "badge-danger";
                                                        }elseif ($row->form_name == 'WH') {
                                                            $badge_class = "badge-warning";
                                                        }elseif ($row->form_name == 'P') {
                                                            $badge_class = "badge-info";
                                                        }
                                                    @endphp
                                                    <td><a href="{{ $row->form_name == 'P' ? route('admin.application_details_formp', ['applicant_id' => $row->application_id]) : route('admin.applicants_detail', ['applicant_id' => $row->application_id]) }}"> {{ $row->application_id }} </a></td>
                                                    <td><span class="badge {{ $badge_class }}"> FORM {{ $row->form_name }} </span></td>
                                                    <td>{{ format_date_other($row->created_at) }}</td>

                                                    <td>    @if ($row->processed_by == "S")
                                                        {{ 'Supervisor' }}
                                                      @elseif ($row->processed_by == "A")
                                                          {{ 'Accountant' }}
                                                      @elseif ($row->processed_by == "SE")
                                                          {{ 'Secretary' }}
                                                      @elseif ($row->processed_by == "PR")
                                                          {{ 'President' }}
                                                      @endif
                                                    </td>

                                                    <td>{{ format_date_other($row->updated_at) }}</td>
                                                    <td>{{ calculateDaysDifference($row->updated_at) }} Days</td>
                                                    <td>{{ calculateDaysDifference($row->created_at) }} Days</td>
                                                    <td>    @if ($row->processed_by == null)
                                                        {{ 'Supervisor' }}
                                                      @elseif ($row->processed_by == "S")
                                                          {{ 'Accountant' }}
                                                      @elseif ($row->processed_by == "A")
                                                          {{ 'Secretary' }}
                                                      @elseif ($row->processed_by == "SE")
                                                          {{ 'President' }}
                                                      @endif
                                                    </td>
                                                    </tr>
                                                    @php
                                                        $i++;
                                                    @endphp
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
                
            </div>
        </div>
    </div>
    @include('admin.include.footer');
</div>
