@extends('document-version.layout')

@section('title', 'Applicant Profile')

@section('content')
@php
    $educationLabels = [
        'DEE' => 'Diploma (Electrical Engineering)',
        'BEE' => 'B.E (Electrical Engineering)',
        'MEE' => 'M.E (Electrical Engineering)',
        'AMIE' => 'A pass in AMIE',
        'OTHER' => 'Other',
    ];
    $initials = $application
        ? strtoupper(collect(explode(' ', trim($application->applicant_name ?? 'A')))->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->join(''))
        : '';
    $statusClass = match ($application?->status ?? '') {
        'APPROVED', 'A' => 'success',
        'SUBMITTED' => 'info',
        'REJECTED', 'R' => 'danger',
        'PENDING', 'P' => 'warning',
        default => 'secondary',
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
    <h4 class="page-title mb-1 mb-md-0"><i class="fa fa-user-circle mr-2"></i>Applicant Profile</h4>
    <a href="{{ route('document-version.sample.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-arrow-left mr-1"></i> Back to Dashboard
    </a>
</div>

{{-- Application picker --}}
<div class="card shadow-sm mb-2 profile-picker">
    <div class="card-body py-2 px-3">
        <div class="row align-items-center no-gutters">
            <div class="col-md-8 pr-md-2">
                <label class="small text-muted mb-0 mr-2 d-md-inline">View profile for</label>
                <select class="custom-select custom-select-sm d-md-inline-block profile-select" id="application_select"
                        onchange="if(this.value){ window.location.href='{{ url('document-version/sample/details') }}/' + this.value; }">
                    <option value="">— Select an applicant —</option>
                    @foreach($applications as $app)
                        <option value="{{ $app->id }}" @selected($application && $application->id === $app->id)>
                            {{ $app->application_no }} · {{ $app->applicant_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if($application)
                <div class="col-md-4 text-md-right mt-2 mt-md-0">
                    <a href="{{ route('document-version.sample.alteration') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-exchange mr-1"></i> Request Alteration
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@if(!$application)
    <div class="profile-empty text-center py-5">
        <div class="profile-empty-icon mb-3"><i class="fa fa-user-o"></i></div>
        <h5 class="text-muted">No applicant selected</h5>
        <p class="text-muted mb-0">Choose an application above to view the full profile.</p>
    </div>
@else
    {{-- Profile header --}}
    <div class="profile-header card shadow-sm mb-3 overflow-hidden">
        <div class="profile-cover"></div>
        <div class="profile-header-body px-3 pb-3">
            <div class="d-flex flex-wrap align-items-end">
                <div class="profile-avatar">{{ $initials }}</div>
                <div class="profile-identity ml-md-3 flex-grow-1">
                    <h2 class="profile-name mb-0">{{ $application->applicant_name }}</h2>
                    <p class="profile-meta mb-1">
                        <span class="mr-2"><i class="fa fa-hashtag mr-1"></i>{{ $application->application_no }}</span>
                        <span><i class="fa fa-calendar mr-1"></i>Applied {{ $application->created_at?->format('d M Y') }}</span>
                    </p>
                    <span class="badge badge-{{ $statusClass }} badge-pill px-2 py-1">{{ $application->status }}</span>
                </div>
                <div class="profile-actions mt-2 mt-md-0">
                    <a href="{{ route('document-version.sample.index') }}" class="btn btn-light btn-sm">
                        <i class="fa fa-edit mr-1"></i> Edit Application
                    </a>
                </div>
            </div>

            <div class="row profile-stats mt-2 mx-0">
                <div class="col-4 col-md-4 profile-stat">
                    <div class="profile-stat-value">{{ $educationRows->count() }}</div>
                    <div class="profile-stat-label">Qualifications</div>
                </div>
                <div class="col-4 col-md-4 profile-stat">
                    <div class="profile-stat-value">{{ $experienceRows->count() }}</div>
                    <div class="profile-stat-label">Experience</div>
                </div>
                <div class="col-4 col-md-4 profile-stat">
                    <div class="profile-stat-value">{{ $documentGroups->count() }}</div>
                    <div class="profile-stat-label">Documents</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row profile-main-row">
        {{-- Left sidebar --}}
        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm profile-card mb-3">
                <div class="card-header bg-white border-0 profile-card-header">
                    <h6 class="profile-section-title mb-0"><i class="fa fa-info-circle mr-1"></i>About</h6>
                </div>
                <div class="card-body profile-card-body">
                    <ul class="profile-info-list list-unstyled mb-0">
                        <li>
                            <span class="profile-info-label">Application ID</span>
                            <span class="profile-info-value">{{ $application->id }}</span>
                        </li>
                        <li>
                            <span class="profile-info-label">Application Number</span>
                            <span class="profile-info-value">{{ $application->application_no }}</span>
                        </li>
                        <li>
                            <span class="profile-info-label">Applicant Name</span>
                            <span class="profile-info-value">{{ $application->applicant_name }}</span>
                        </li>
                        <li>
                            <span class="profile-info-label">Application Status</span>
                            <span class="profile-info-value">
                                <span class="badge badge-{{ $statusClass }}">{{ $application->status }}</span>
                            </span>
                        </li>
                        <li>
                            <span class="profile-info-label">Registered On</span>
                            <span class="profile-info-value">{{ $application->created_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </li>
                        <li>
                            <span class="profile-info-label">Last Updated</span>
                            <span class="profile-info-value">{{ $application->updated_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm profile-card">
                <div class="card-header bg-white border-0 profile-card-header">
                    <h6 class="profile-section-title mb-0"><i class="fa fa-paperclip mr-1"></i>Quick Links</h6>
                </div>
                <div class="card-body profile-card-body py-2">
                    <div class="list-group list-group-flush profile-links">
                        <a href="{{ route('document-version.sample.table-data') }}" class="list-group-item list-group-item-action">
                            <i class="fa fa-table text-muted mr-2"></i> View raw table data
                        </a>
                        @foreach($documentGroups as $doc)
                            @if($doc['pending_version'])
                                <a href="{{ route('document-version.sample.review', $doc['group_key']) }}" class="list-group-item list-group-item-action">
                                    <i class="fa fa-clock-o text-warning mr-2"></i>
                                    Review pending — {{ $documentTypes[$doc['document_type']] ?? $doc['document_type'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Main content --}}
        <div class="col-lg-8">
            {{-- Education --}}
            <div class="card shadow-sm profile-card mb-3">
                <div class="card-header bg-white border-0 profile-card-header d-flex justify-content-between align-items-center">
                    <h6 class="profile-section-title mb-0"><i class="fa fa-graduation-cap mr-1"></i>Educational Qualifications</h6>
                    <span class="badge badge-light badge-sm">{{ $educationRows->count() }}</span>
                </div>
                <div class="card-body profile-card-body">
                    @forelse($educationRows as $i => $row)
                        <div class="profile-timeline-item {{ !$loop->last ? 'profile-timeline-divider' : '' }}">
                            <div class="profile-timeline-icon edu"><i class="fa fa-book"></i></div>
                            <div class="profile-timeline-content">
                                <h6 class="mb-0">{{ $educationLabels[$row['education_level']] ?? $row['education_level'] }}</h6>
                                <p class="text-muted mb-1 small">{{ $row['institution_name'] }}</p>
                                @if($row['certificate_no'])
                                    <p class="small text-muted mb-1"><i class="fa fa-certificate mr-1"></i>{{ $row['certificate_no'] }}</p>
                                @endif

                                @include('document-version.partials.profile-document-status', ['document' => $row['document'] ?? []])
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0 py-2">No educational qualifications added yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Experience --}}
            <div class="card shadow-sm profile-card mb-3">
                <div class="card-header bg-white border-0 profile-card-header d-flex justify-content-between align-items-center">
                    <h6 class="profile-section-title mb-0"><i class="fa fa-briefcase mr-1"></i>Work Experience</h6>
                    <span class="badge badge-light badge-sm">{{ $experienceRows->count() }}</span>
                </div>
                <div class="card-body profile-card-body">
                    @forelse($experienceRows as $row)
                        <div class="profile-timeline-item {{ !$loop->last ? 'profile-timeline-divider' : '' }}">
                            <div class="profile-timeline-icon exp"><i class="fa fa-building"></i></div>
                            <div class="profile-timeline-content">
                                <h6 class="mb-0">{{ $row['company_name'] }}</h6>
                                <p class="text-muted mb-1 small">{{ $row['designation'] }}</p>

                                @include('document-version.partials.profile-document-status', ['document' => $row['document'] ?? []])
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0 py-2">No work experience added yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Documents overview --}}
            <div class="card shadow-sm profile-card mb-3">
                <div class="card-header bg-white border-0 profile-card-header">
                    <h6 class="profile-section-title mb-0"><i class="fa fa-folder-open mr-1"></i>Submitted Documents</h6>
                </div>
                <div class="card-body profile-card-body">
                    @forelse($documentGroups as $doc)
                        @php
                            $active = $doc['active_version'] ?? null;
                            $pending = $doc['pending_version'] ?? null;
                            $typeLabel = $documentTypes[$doc['document_type']] ?? $doc['document_type'];
                        @endphp
                        <div class="profile-doc-card mb-2">
                            <div class="d-flex align-items-start">
                                <div class="profile-doc-icon"><i class="fa fa-file-pdf-o"></i></div>
                                <div class="flex-grow-1 ml-2">
                                    <div class="d-flex justify-content-between flex-wrap">
                                        <div>
                                            <strong>{{ $typeLabel }}</strong>
                                            <div class="small text-muted">
                                                {{ $moduleTypes[$doc['module_type']] ?? $doc['module_type'] }}
                                                @if($doc['module_ref_id']) · Ref #{{ $doc['module_ref_id'] }} @endif
                                            </div>
                                        </div>
                                        <div class="profile-doc-badges mt-1 mt-md-0">
                                            @if($active)
                                                <span class="badge badge-success">Approved v{{ $active->version_no }}</span>
                                            @endif
                                            @if($pending)
                                                <span class="badge badge-warning">Pending v{{ $pending->version_no }}</span>
                                            @endif
                                            @if(!$active && !$pending)
                                                <span class="badge badge-secondary">No approved copy</span>
                                            @endif
                                        </div>
                                    </div>

                                    @if($active)
                                        <div class="profile-file-link mt-1">
                                            <a href="{{ route('document-version.sample.download', $active->id) }}"
                                               target="_blank" rel="noopener noreferrer" title="Open document">
                                                <i class="fa fa-file-pdf-o mr-1"></i>{{ $active->file_name }}
                                            </a>
                                            <span class="text-muted small ml-2">Current active file</span>
                                        </div>
                                    @endif

                                    <div class="mt-1">
                                        <a href="{{ route('document-version.sample.history', $doc['group_key']) }}" class="btn btn-sm btn-outline-secondary">History</a>
                                        @if($pending)
                                            <a href="{{ route('document-version.sample.review', $doc['group_key']) }}" class="btn btn-sm btn-primary">Review</a>
                                        @elseif($active)
                                            <a href="{{ route('document-version.sample.alteration.form', $doc['group_key']) }}" class="btn btn-sm btn-outline-primary">Request Alteration</a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0 py-2">No documents uploaded yet.</p>
                    @endforelse
                </div>
            </div>

            {{-- Version timeline (collapsible) --}}
            @if($allDocumentVersions->isNotEmpty())
                <div class="card shadow-sm profile-card mb-0">
                    <div class="card-header bg-white border-0 profile-card-header">
                        <a class="d-flex justify-content-between align-items-center text-decoration-none profile-collapse-toggle"
                           data-toggle="collapse" href="#versionTimeline" role="button" aria-expanded="false">
                            <h6 class="profile-section-title mb-0 text-dark">
                                <i class="fa fa-history mr-1"></i>Document Version Timeline
                            </h6>
                            <span class="badge badge-light">{{ $allDocumentVersions->count() }} version(s)</span>
                        </a>
                    </div>
                    <div class="collapse" id="versionTimeline">
                        <div class="card-body profile-card-body pt-2">
                            <div class="profile-version-list">
                                @foreach($allDocumentVersions as $ver)
                                    <div class="profile-version-item">
                                        <div class="profile-version-dot badge-{{ $ver->status->badgeClass() }}"></div>
                                        <div class="profile-version-body">
                                            <div class="d-flex justify-content-between flex-wrap">
                                                <strong>{{ $documentTypes[$ver->document_type] ?? $ver->document_type }}</strong>
                                                <span class="small text-muted">{{ $ver->created_at?->format('d M Y, h:i A') }}</span>
                                            </div>
                                            <div class="small text-muted mb-1">
                                                Version {{ $ver->version_no }}
                                                · {{ $ver->request_type?->label() ?? 'Upload' }}
                                                @if($ver->is_active) · <span class="text-primary font-weight-bold">Active</span> @endif
                                            </div>
                                            <a href="{{ route('document-version.sample.download', $ver->id) }}"
                                               class="small" target="_blank" rel="noopener noreferrer" title="Open document">
                                                <i class="fa fa-paperclip mr-1"></i>{{ $ver->file_name }}
                                            </a>
                                            <span class="badge badge-{{ $ver->status->badgeClass() }} ml-2">{{ $ver->status->label() }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .profile-select { max-width: 100%; width: 100%; }
    @media (min-width: 768px) { .profile-select { max-width: 420px; width: auto; vertical-align: middle; } }
    .profile-cover {
        height: 64px;
        background: linear-gradient(135deg, var(--tnelb-primary) 0%, #0066cc 100%);
    }
    .profile-header-body { margin-top: -32px; position: relative; }
    .profile-avatar {
        width: 72px; height: 72px; border-radius: 50%;
        background: #fff; border: 3px solid #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.12);
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; font-weight: 700; color: var(--tnelb-primary);
        flex-shrink: 0;
    }
    .profile-name { font-size: 1.35rem; font-weight: 700; color: #212529; line-height: 1.2; }
    .profile-meta { color: #6c757d; font-size: .85rem; line-height: 1.3; }
    .profile-stats {
        background: #f8f9fa; border-radius: .35rem;
        padding: .45rem 0; text-align: center;
    }
    .profile-stat-value { font-size: 1.15rem; font-weight: 700; color: var(--tnelb-primary); line-height: 1.2; }
    .profile-stat-label { font-size: .7rem; color: #6c757d; text-transform: uppercase; letter-spacing: .02em; }
    .profile-section-title { font-weight: 600; color: var(--tnelb-primary); font-size: .925rem; }
    .profile-card { border: none; border-radius: .4rem; }
    .profile-card-header { padding: .65rem 1rem .5rem; }
    .profile-card-body { padding: .65rem 1rem .75rem; }
    .profile-main-row > [class*="col-"] { padding-left: 10px; padding-right: 10px; }
    .profile-info-list li {
        display: grid; grid-template-columns: 42% 58%; gap: .35rem .5rem;
        align-items: center; padding: .35rem 0; border-bottom: 1px solid #f0f0f0;
        font-size: .875rem;
    }
    .profile-info-list li:last-child { border-bottom: none; padding-bottom: 0; }
    .profile-info-label { color: #6c757d; }
    .profile-info-value { font-weight: 500; text-align: right; word-break: break-word; }
    .profile-links .list-group-item { border: none; padding: .4rem 0; font-size: .85rem; }
    .profile-timeline-item { display: flex; position: relative; }
    .profile-timeline-divider { border-bottom: 1px solid #eee; padding-bottom: .65rem; margin-bottom: .65rem; }
    .profile-timeline-icon {
        width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        color: #fff; font-size: .85rem;
    }
    .profile-timeline-icon.edu { background: #17a2b8; }
    .profile-timeline-icon.exp { background: #6f42c1; }
    .profile-timeline-content { margin-left: .65rem; flex-grow: 1; min-width: 0; }
    .profile-doc-card {
        background: #f8f9fa; border-radius: .35rem; padding: .65rem .75rem;
        border-left: 3px solid var(--tnelb-primary);
    }
    .profile-doc-icon {
        width: 36px; height: 36px; background: #fff; border-radius: .3rem;
        display: flex; align-items: center; justify-content: center;
        color: #dc3545; font-size: 1.15rem; box-shadow: 0 1px 2px rgba(0,0,0,.06);
    }
    .profile-file-link a { font-weight: 500; font-size: .875rem; }
    .profile-version-list { position: relative; padding-left: 1.25rem; }
    .profile-version-list::before {
        content: ''; position: absolute; left: 5px; top: 6px; bottom: 6px;
        width: 2px; background: #dee2e6;
    }
    .profile-version-item { position: relative; padding-bottom: .75rem; font-size: .875rem; }
    .profile-version-item:last-child { padding-bottom: 0; }
    .profile-version-dot {
        position: absolute; left: -1.25rem; top: 3px;
        width: 12px; height: 12px; border-radius: 50%; background: #6c757d;
    }
    .profile-version-dot.badge-success { background: #28a745; }
    .profile-version-dot.badge-warning { background: #ffc107; }
    .profile-version-dot.badge-danger { background: #dc3545; }
    .profile-empty-icon { font-size: 3rem; color: #ced4da; }
    .profile-collapse-toggle:hover { opacity: .85; }
</style>
@endpush
