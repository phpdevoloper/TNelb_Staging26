@include('admin.include.top')
@include('admin.include.header')
@include('admin.include.navbar')

@php
    $newApplications = $new_applications ?? $workflows ?? collect();
    $renewalApplications = $renewal ?? collect();
    $returnedApplications = $returned_applications ?? collect();
    $requestedType = strtoupper((string) request()->query('form_type', ''));
    $isRenewalOnly = $requestedType === 'R';
    $isNewOnly = $requestedType === 'N';
    $isCompletedList = $is_completed_list ?? false;

    $showRenewalBlock = (!$isNewOnly) && ($isRenewalOnly || $renewalApplications->isNotEmpty());

    $allApplications = collect();
    if (!$isRenewalOnly) {
        $allApplications = $allApplications->merge($newApplications);
    }
    if ($showRenewalBlock) {
        $allApplications = $allApplications->merge($renewalApplications);
    }
    if (!$isCompletedList) {
        $allApplications = $allApplications->merge($returnedApplications);
    }
    $allApplications = $allApplications
        ->unique('application_id')
        ->sortByDesc(function ($r) {
            return (string) ($r->submitted_date ?? $r->created_at ?? $r->dt_submit ?? '');
        })
        ->values();

    // Non-QU rows in returned list are "resubmitted" (same as old dedicated table); used when the merged row lacks has_return_history.
    $resubmittedReturnedLookup = array_fill_keys(
        $returnedApplications
            ->filter(function ($r) {
                $st = strtoupper((string) ($r->status ?? $r->application_status ?? $r->app_status ?? ''));

                return $st !== '' && $st !== 'QU';
            })
            ->pluck('application_id')
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->values()
            ->all(),
        true
    );

    $firstApp = $allApplications->first() ?? $newApplications->first() ?? $renewalApplications->first();
@endphp

<style>
    /* Applications view – modern card layout */
    .app-view-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        border: 1px solid #e9ecef;
        overflow: hidden;
    }
    .app-view-title {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #1e293b;
        padding: 1rem 1.25rem;
        font-size: 1.1rem;
        font-weight: 700;
        text-align: center;
        border-radius: 10px;
        border: 1px solid #fcd34d;
        margin-bottom: 1.25rem;
    }
    .app-view-table-wrap {
        padding: 0 1rem 1rem;
    }
    .app-view-table-wrap .table {
        margin-bottom: 0;
        border-radius: 8px;
        overflow: hidden;
    }
    .app-view-table-wrap .table thead th {
        background: linear-gradient(135deg, #1e3a5f 0%, #2c5282 100%) !important;
        color: #fff !important;
        font-weight: 600;
        font-size: 0.875rem;
        padding: 0.875rem 0.75rem;
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        text-align: center;
    }
    .app-view-table-wrap .table tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        font-size: 0.9rem;
    }
    .app-view-table-wrap .table tbody tr {
        transition: background 0.2s ease;
    }
    .app-view-table-wrap .table tbody tr:nth-child(even) {
        background: #f8fafc;
    }
    .app-view-table-wrap .table tbody tr:hover {
        background: #eff6ff !important;
    }
    .app-view-table-wrap .table tbody a:not(.btn) {
        color: #2563eb;
        font-weight: 500;
        text-decoration: none;
    }
    .app-view-table-wrap .table tbody a:not(.btn):hover {
        text-decoration: underline;
    }
    .app-view-table-wrap .badge {
        font-size: 0.75rem;
        font-weight: 600;
        padding: 0.35em 0.65em;
    }
    /* Corner ribbon: compact strip; extra width so full "Resubmitted" / "Returned" is not clipped */
    .app-view-table-wrap td.corner-ribbon-cell {
        position: relative;
        overflow: hidden;
        vertical-align: middle !important;
        min-width: 3.35rem;
        text-align: center;
        padding-top: 1rem !important;
        padding-bottom: 0.75rem !important;
    }
    .app-view-table-wrap .corner-ribbon {
        position: absolute;
        top: 17px;
        left: -24px;
        width: 99px;
        padding: 5px 0 4px;
        box-sizing: content-box;
        background: #6f42c1;
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
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.35);
        pointer-events: none;
        z-index: 2;
        -webkit-font-smoothing: antialiased;
    }
    .app-view-table-wrap .corner-ribbon.corner-ribbon-resubmitted {
        background: #2e7d32;
    }
    .app-view-table-wrap .sno-num {
        position: relative;
        z-index: 1;
        display: inline-block;
    }
    .app-view-table-wrap .badge-returned-pill {
        background-color: #6f42c1;
        color: #fff;
        font-weight: 600;
        font-size: 0.75rem;
    }
    .app-view-table-wrap .btn-view-licence {
        padding: 0.35rem 0.75rem;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 6px;
    }
    .app-view-back {
        padding: 1rem 1rem 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .app-view-back .btn-back {
        padding: 0.5rem 1.25rem;
        font-weight: 600;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    .app-view-empty {
        padding: 2.5rem 1rem;
        text-align: center;
        color: #64748b;
        font-size: 0.95rem;
    }
</style>

<div id="content" class="main-content">
    <div class="layout-px-spacing">

        <div class="middle-content container-xxl p-0">

            <!--  BEGIN BREADCRUMBS  -->
            <div class="secondary-nav">
                <div class="breadcrumbs-container" data-page-heading="Analytics">
                    <header class="header navbar navbar-expand-sm">
                        <a href="{{ route('admin.secretary_table') }}" class="btn-toggle sidebarCollapse"
                            data-placement="bottom">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-menu">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </a>
                        <div class="d-flex breadcrumb-content">
                            <div class="page-header">

                                <div class="page-title">
                                </div>



                            </div>
                        </div>

                    </header>
                </div>
            </div>
            <!--  END BREADCRUMBS  -->

            <!-- -------------------------------------------------------- -->
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-sm-12 layout-spacing">
                    <div class="app-view-title">
                        @if($isCompletedList)
                            @if($isRenewalOnly)
                                Completed Renewal Applications For {{ $firstApp->form_name ?? 'N/A' }}
                            @elseif($isNewOnly)
                                Completed New Applications For {{ $firstApp->form_name ?? 'N/A' }} ({{ $firstApp->license_name ?? 'N/A' }})
                            @else
                                Completed Applications For {{ $firstApp->form_name ?? 'N/A' }} ({{ $firstApp->license_name ?? 'N/A' }})
                            @endif
                        @else
                            @if($isRenewalOnly)
                                Pending Renewal Applications For {{ $firstApp->form_name ?? 'N/A' }}
                            @elseif($isNewOnly)
                                Pending New Applications For {{ $firstApp->form_name ?? 'N/A' }} ({{ $firstApp->license_name ?? 'N/A' }})
                            @else
                                Pending Applications For {{ $firstApp->form_name ?? 'N/A' }} ({{ $firstApp->license_name ?? 'N/A' }})
                            @endif
                        @endif
                    </div>

                    <div class="app-view-card">
                        <div class="app-view-table-wrap">
                            <table id="supervisor-applications-table" class="table table-hover zero-config" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>S.No</th>
                                        <th>Application Id</th>
                                        <th>Applicant's Name</th>
                                        @if($isCompletedList)
                                        <th>Applied On</th>
                                        <th>Status</th>
                                        <th>Licence No</th>
                                        <th>Issued At</th>
                                        <th>Expires At</th>
                                        <th>License</th>
                                        @else
                                        <th>Certificate of</th>
                                        <th>Payment Status</th>
                                        <th>Applied On</th>
                                        <th class="no-content">Action</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($allApplications as $key => $application)
                                        @php
                                            $appStatus = $application->status ?? $application->application_status ?? $application->app_status ?? null;
                                            $isCompleted = in_array($appStatus, ['A'], true);
                                            $__hr = $application->has_return_history ?? false;
                                            $wasReturned = $__hr === true || $__hr === 1 || $__hr === '1'
                                                || (is_string($__hr) && strtoupper($__hr) === 'T');
                                            $fn = strtoupper((string)($application->form_name ?? ''));

                                            if (($is_completed_list ?? false) && $isCompleted) {
                                                if ($fn === 'FORM P' || $fn === 'P') {
                                                    $detailUrl = route('admin.application_details_formp_completed', ['applicant_id' => $application->application_id]);
                                                } else {
                                                    $detailUrl = route('admin.view_completed_application', ['applicant_id' => $application->application_id]);
                                                }
                                            } else {
                                                $detailUrl = ($fn === 'FORM P' || $fn === 'P')
                                                    ? route('admin.application_details_formp', ['applicant_id' => $application->application_id])
                                                    : route('admin.applicants_detail', ['applicant_id' => $application->application_id]);
                                            }
                                            $isReturnedToApplicant = strtoupper((string) $appStatus) === 'QU';
                                            $inResubmittedReturnedList = ! empty($resubmittedReturnedLookup[(string) ($application->application_id ?? '')]);
                                            $showResubmitted = ! $isCompleted && ! $isReturnedToApplicant
                                                && ($wasReturned || $inResubmittedReturnedList);
                                            $appliedOnRaw = $application->submitted_date ?? $application->created_at ?? $application->dt_submit;
                                        @endphp
                                        <tr>
                                            <td class="@if($isReturnedToApplicant || $showResubmitted) corner-ribbon-cell @endif">
                                                @if($isReturnedToApplicant)
                                                    <span class="corner-ribbon">Returned</span>
                                                @elseif($showResubmitted)
                                                    <span class="corner-ribbon corner-ribbon-resubmitted">Resubmitted</span>
                                                @endif
                                                <span class="sno-num">{{ $loop->iteration }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ $detailUrl }}">
                                                    {{ $application->application_id }}
                                                </a>
                                            </td>
                                            <td>{{ $application->applicant_name ?? 'N/A' }}</td>
                                            @if($isCompletedList)
                                            <td>{{ format_date_other($appliedOnRaw) }}</td>
                                            <td class="text-center">
                                                @if($isCompleted)
                                                    <span class="badge rounded-pill bg-success">Completed</span>
                                                @else
                                                    <span class="badge rounded-pill bg-warning text-dark">Forwarded</span>
                                                @endif
                                            </td>
                                            <td>{{ $application->license_number ?? '-' }}</td>
                                            <td>{{ !empty($application->issued_at) ? date('d-m-Y', strtotime($application->issued_at)) : '-' }}</td>
                                            <td>{{ !empty($application->expires_at) ? date('d-m-Y', strtotime($application->expires_at)) : '-' }}</td>
                                            <td class="text-center">
                                                @if($isCompleted)
                                                    @php $fnLicence = strtoupper((string)($application->form_name ?? '')); @endphp
                                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                                        @if($fnLicence === 'FORM P' || $fnLicence === 'P')
                                                            <a href="{{ route('admin.formp.licence.en', ['application_id' => $application->application_id]) }}" target="_blank" class="btn btn-sm btn-primary btn-view-licence" title="Form P Licence (English)"><i class="fa fa-file-pdf-o me-1"></i> View EN</a>
                                                            <a href="{{ route('admin.formp.licence.ta', ['application_id' => $application->application_id]) }}" target="_blank" class="btn btn-sm btn-outline-primary btn-view-licence" title="Form P Licence (Tamil)"><i class="fa fa-file-pdf-o me-1"></i> View TA</a>
                                                        @else
                                                            <a href="{{ route('admin.getLicenceDoc.pdf', ['application_id' => $application->application_id]) }}" target="_blank" class="btn btn-sm btn-primary btn-view-licence" title="View stored Licence PDF"><i class="fa fa-file-pdf-o me-1"></i> View</a>
                                                            <a href="{{ route('admin.generate.pdf', ['application_id' => $application->application_id]) }}" target="_blank" class="btn btn-sm btn-outline-primary btn-view-licence" title="Generated Licence PDF"><i class="fa fa-download me-1"></i> Generated PDF</a>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            @else
                                            <td>{{ $application->license_name ?? 'N/A' }}</td>
                                            <td>{{ in_array($application->payment_status ?? null, ['payment', 'paid'], true) ? 'Success' : ($application->payment_status ?? 'N/A') }}</td>
                                            <td>{{ format_date_other($appliedOnRaw) }}</td>
                                            <td>
                                                <a href="{{ $detailUrl }}">
                                                    <button type="button" class="btn btn-primary" data-bs-placement="bottom" title="Forward Application">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                </a>
                                            </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $isCompletedList ? '9' : '7' }}" class="app-view-empty">{{ ($is_completed_list ?? false) ? 'No completed applications found.' : 'No pending applications found.' }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                        <div class="app-view-back">
                            <span></span>
                            @if($isCompletedList)
                                <a href="{{ route('admin.completed_applications') }}" class="btn btn-outline-primary btn-back">
                                    <i class="fa fa-arrow-left me-1"></i> Back to Completed Applications
                                </a>
                            @else
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary btn-back">
                                    <i class="fa fa-arrow-left me-1"></i> Back to Dashboard
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@include('admin.include.footer')
