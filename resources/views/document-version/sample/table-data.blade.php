@extends('document-version.layout')

@section('title', 'Document Version — Table Data')

@section('content')
<h4 class="page-title mb-3"><i class="fa fa-table mr-2"></i>Table Data</h4>
<p class="text-muted mb-4">Raw data from the four document-version tables (read-only view for testing).</p>

<ul class="nav nav-tabs mb-3" id="tableDataTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" id="tab-applications" data-toggle="tab" href="#panel-applications" role="tab">
            d_applications <span class="badge badge-light text-dark ml-1">{{ $applications->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-educations" data-toggle="tab" href="#panel-educations" role="tab">
            d_educations <span class="badge badge-light text-dark ml-1">{{ $educations->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-experiences" data-toggle="tab" href="#panel-experiences" role="tab">
            d_experiences <span class="badge badge-light text-dark ml-1">{{ $experiences->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" id="tab-documents" data-toggle="tab" href="#panel-documents" role="tab">
            d_documents <span class="badge badge-light text-dark ml-1">{{ $documents->count() }}</span>
        </a>
    </li>
</ul>

<div class="tab-content" id="tableDataTabContent">
    {{-- d_applications --}}
    <div class="tab-pane fade show active" id="panel-applications" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">d_applications</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm table-dv mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Application No</th>
                                <th>Applicant Name</th>
                                <th>Request Type</th>
                                <th>Parent App ID</th>
                                <th>Parent Application No</th>
                                <th>Alteration App</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $row)
                                <tr @if($highlightApplicationIds->contains($row->id)) class="table-info" @endif>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->application_no }}</td>
                                    <td>{{ $row->applicant_name ?? '—' }}</td>
                                    <td>{{ $row->request_context ?? 'NEW' }}</td>
                                    <td>{{ $row->parent_application_id ?? '—' }}</td>
                                    <td>
                                        @if($row->parentApplication)
                                            {{ $row->parentApplication->application_no }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($row->alterationApplications->isNotEmpty())
                                            {{ $row->alterationApplications->pluck('application_no')->join(', ') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $row->status }}</td>
                                    <td>{{ $row->created_at?->format('d M Y H:i') ?? '—' }}</td>
                                    <td>{{ $row->updated_at?->format('d M Y H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-muted py-4">No records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- d_educations --}}
    <div class="tab-pane fade" id="panel-educations" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">d_educations</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm table-dv mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>App ID</th>
                                <th>Application No</th>
                                <th>Education Level</th>
                                <th>Institution</th>
                                <th>Certificate No</th>
                                <th>File Path</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($educations as $row)
                                <tr @if($highlightApplicationIds->contains($row->application_id)) class="table-info" @endif>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->application_id }}</td>
                                    <td>{{ $row->application?->application_no ?? '—' }}</td>
                                    <td>{{ $row->education_level }}</td>
                                    <td>{{ $row->institution_name }}</td>
                                    <td>{{ $row->certificate_no ?? '—' }}</td>
                                    <td class="small text-muted">{{ $row->file_path ? \Illuminate\Support\Str::limit($row->file_path, 48) : '—' }}</td>
                                    <td>{{ $row->created_at?->format('d M Y H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">No records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- d_experiences --}}
    <div class="tab-pane fade" id="panel-experiences" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">d_experiences</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm table-dv mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>App ID</th>
                                <th>Application No</th>
                                <th>Company</th>
                                <th>Designation</th>
                                <th>File Path</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($experiences as $row)
                                <tr @if($highlightApplicationIds->contains($row->application_id)) class="table-info" @endif>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->application_id }}</td>
                                    <td>{{ $row->application?->application_no ?? '—' }}</td>
                                    <td>{{ $row->company_name }}</td>
                                    <td>{{ $row->designation }}</td>
                                    <td class="small text-muted">{{ $row->file_path ? \Illuminate\Support\Str::limit($row->file_path, 48) : '—' }}</td>
                                    <td>{{ $row->created_at?->format('d M Y H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- d_documents --}}
    <div class="tab-pane fade" id="panel-documents" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">d_documents</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm table-dv mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>App ID</th>
                                <th>Parent App</th>
                                <th>Module</th>
                                <th>Ref ID</th>
                                <th>Doc Type</th>
                                <th>App Type</th>
                                <th>Version</th>
                                <th>File</th>
                                <th>Old Path</th>
                                <th>Status</th>
                                <th>Active</th>
                                <th>Approved</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documents as $row)
                                <tr @if($highlightApplicationIds->contains($row->application_id)) class="table-info" @endif>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->application_id }}</td>
                                    <td>{{ $row->parent_application_id ?? '—' }}</td>
                                    <td>{{ $row->module_type }}</td>
                                    <td>{{ $row->module_ref_id ?? '—' }}</td>
                                    <td>{{ $row->document_type }}</td>
                                    <td>{{ $row->application_type?->value ?? $row->getRawOriginal('application_type') ?? '—' }}</td>
                                    <td>v{{ $row->version_no }}</td>
                                    <td>
                                        <a href="{{ route('document-version.sample.download', $row->id) }}"
                                           target="_blank" rel="noopener noreferrer" title="Open document">{{ $row->file_name }}</a>
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($row->file_path, 40) }}</div>
                                    </td>
                                    <td class="small text-muted">{{ $row->old_file_path ? \Illuminate\Support\Str::limit($row->old_file_path, 32) : '—' }}</td>
                                    <td>{{ $row->status?->value ?? $row->getRawOriginal('status') }}</td>
                                    <td>{{ $row->is_active ? 'Yes' : 'No' }}</td>
                                    <td>{{ $row->approved_at?->format('d M Y H:i') ?? '—' }}</td>
                                    <td>{{ $row->created_at?->format('d M Y H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="14" class="text-center text-muted py-4">No records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@if($selectedApplicationId)
    <p class="text-muted small mt-3 mb-0">
        <i class="fa fa-info-circle mr-1"></i>
        Rows highlighted in blue belong to the selected application (ID {{ $selectedApplicationId }})
        @if(($highlightApplicationIds ?? collect())->count() > 1)
            and its linked parent/alteration application(s).
        @endif
    </p>
@endif
@endsection

@push('styles')
<style>
    .nav-tabs .nav-link { color: var(--tnelb-primary); }
    .nav-tabs .nav-link.active { font-weight: 600; color: var(--tnelb-primary); }
</style>
@endpush
