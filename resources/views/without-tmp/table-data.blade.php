@extends('without-tmp.layout')

@section('title', 'Without Temp — Table Data')

@section('content')
<h4 class="page-title mb-3"><i class="fa fa-table mr-2"></i>Table Data</h4>
<p class="text-muted mb-4">Raw data from all without-tmp tables (read-only view for testing).</p>

<ul class="nav nav-tabs mb-3" id="tableDataTabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#tab-applications" role="tab">
            scert_app <span class="badge badge-light text-dark ml-1">{{ $applications->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-educations" role="tab">
            c_education <span class="badge badge-light text-dark ml-1">{{ $educations->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-experiences" role="tab">
            c_experience <span class="badge badge-light text-dark ml-1">{{ $experiences->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-photos" role="tab">
            c_photo <span class="badge badge-light text-dark ml-1">{{ $photos->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-signatures" role="tab">
            c_signature <span class="badge badge-light text-dark ml-1">{{ $signatures->count() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#tab-alterations" role="tab">
            c_alteration_requests <span class="badge badge-light text-dark ml-1">{{ $alterations->count() }}</span>
        </a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-applications" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">scert_app</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm table-wt mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($applications as $row)
                                <tr @if($selectedApplicationId && $row->id === $selectedApplicationId) class="table-info" @endif>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->application_code }}</td>
                                    <td>{{ $row->applicant_name ?? '—' }}</td>
                                    <td>{{ $row->status->label() }}</td>
                                    <td>{{ $row->submitted_at?->format('d M Y H:i') ?? '—' }}</td>
                                    <td>{{ $row->created_at?->format('d M Y H:i') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-educations" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">c_education</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm table-wt mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>App ID</th>
                                <th>Application Code</th>
                                <th>Level</th>
                                <th>Institution</th>
                                <th>File</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($educations as $row)
                                <tr @if($selectedApplicationId && $row->application_id === $selectedApplicationId) class="table-info" @endif>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->application_id }}</td>
                                    <td>{{ $row->application?->application_code ?? '—' }}</td>
                                    <td>{{ $row->education_level }}</td>
                                    <td>{{ $row->institution_name }}</td>
                                    <td>{{ $row->file_name ?? '—' }}</td>
                                    <td>{{ $row->is_active ? 'Yes' : 'No' }}</td>
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

    <div class="tab-pane fade" id="tab-experiences" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">c_experience</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm table-wt mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>App ID</th>
                                <th>Application Code</th>
                                <th>Company</th>
                                <th>Designation</th>
                                <th>File</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($experiences as $row)
                                <tr @if($selectedApplicationId && $row->application_id === $selectedApplicationId) class="table-info" @endif>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->application_id }}</td>
                                    <td>{{ $row->application?->application_code ?? '—' }}</td>
                                    <td>{{ $row->company_name }}</td>
                                    <td>{{ $row->designation }}</td>
                                    <td>{{ $row->file_name ?? '—' }}</td>
                                    <td>{{ $row->is_active ? 'Yes' : 'No' }}</td>
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

    <div class="tab-pane fade" id="tab-photos" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">c_photo</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm table-wt mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>App ID</th>
                                <th>Application Code</th>
                                <th>File</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($photos as $row)
                                <tr @if($selectedApplicationId && $row->application_id === $selectedApplicationId) class="table-info" @endif>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->application_id }}</td>
                                    <td>{{ $row->application?->application_code ?? '—' }}</td>
                                    <td>{{ $row->file_name ?? '—' }}</td>
                                    <td>{{ $row->is_active ? 'Yes' : 'No' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-signatures" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">c_signature</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm table-wt mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>App ID</th>
                                <th>Application Code</th>
                                <th>File</th>
                                <th>Active</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($signatures as $row)
                                <tr @if($selectedApplicationId && $row->application_id === $selectedApplicationId) class="table-info" @endif>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->application_id }}</td>
                                    <td>{{ $row->application?->application_code ?? '—' }}</td>
                                    <td>{{ $row->file_name ?? '—' }}</td>
                                    <td>{{ $row->is_active ? 'Yes' : 'No' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No records.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="tab-alterations" role="tabpanel">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">c_alteration_requests</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm table-wt mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>App ID</th>
                                <th>Application Code</th>
                                <th>Target</th>
                                <th>Old File</th>
                                <th>New File</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($alterations as $row)
                                <tr @if($selectedApplicationId && $row->application_id === $selectedApplicationId) class="table-info" @endif>
                                    <td>{{ $row->id }}</td>
                                    <td>{{ $row->application_id }}</td>
                                    <td>{{ $row->application?->application_code ?? '—' }}</td>
                                    <td>{{ $row->target_table }}#{{ $row->target_row_id }}</td>
                                    <td>{{ $row->old_file_name ?? '—' }}</td>
                                    <td>{{ $row->new_file_name }}</td>
                                    <td>{{ $row->status->value }}</td>
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
</div>

@if($selectedApplicationId)
    @php $selectedApp = $applications->firstWhere('id', $selectedApplicationId); @endphp
    <p class="text-muted small mt-3 mb-0">
        <i class="fa fa-info-circle mr-1"></i>
        Rows highlighted in blue belong to the selected application
        @if($selectedApp)
            (<strong>{{ $selectedApp->application_code }}</strong>, ID {{ $selectedApplicationId }}).
        @else
            (ID {{ $selectedApplicationId }}).
        @endif
    </p>
@endif
@endsection

@push('styles')
<style>
    .nav-tabs .nav-link { color: var(--wt-primary); }
    .nav-tabs .nav-link.active { font-weight: 600; color: var(--wt-primary); }
    .table-wt thead th {
        background-color: var(--wt-primary);
        color: #fff;
        border-color: var(--wt-primary);
        vertical-align: middle;
    }
</style>
@endpush
