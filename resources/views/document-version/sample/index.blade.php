@extends('document-version.layout')

@section('title', 'Document Version — Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="page-title mb-0"><i class="fa fa-dashboard mr-2"></i>Dashboard</h4>
</div>

<div class="row">
    <div class="col-lg-3 mb-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white font-weight-bold">Create Application</div>
            <div class="card-body">
                <form method="POST" action="{{ route('document-version.sample.applications.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="application_no">Application No</label>
                        <input type="text" class="form-control form-control-sm" id="application_no" name="application_no"
                               value="{{ old('application_no', 'APP-' . now()->format('YmdHis')) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="applicant_name">Applicant Name</label>
                        <input type="text" class="form-control form-control-sm" id="applicant_name" name="applicant_name"
                               value="{{ old('applicant_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label for="create_request_context">Request Type</label>
                        <select class="custom-select custom-select-sm" id="create_request_context" name="request_context">
                            <option value="NEW" @selected(old('request_context', $requestContext ?? 'NEW') === 'NEW')>New</option>
                            <option value="RENEWAL" @selected(old('request_context', $requestContext ?? 'NEW') === 'RENEWAL')>Renewal</option>
                            <option value="DIGITISATION" @selected(old('request_context', $requestContext ?? 'NEW') === 'DIGITISATION')>Digitisation</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm btn-block">
                        <i class="fa fa-plus mr-1"></i> Create
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">Select Application</div>
            <div class="card-body">
                <form method="POST" action="{{ route('document-version.sample.set-application') }}">
                    @csrf
                    <div class="form-group">
                        <label for="select_request_context">Request Type</label>
                        <select class="custom-select custom-select-sm" id="select_request_context" name="request_context">
                            <option value="NEW" @selected(($requestContext ?? 'NEW') === 'NEW')>New</option>
                            <option value="RENEWAL" @selected(($requestContext ?? 'NEW') === 'RENEWAL')>Renewal</option>
                            <option value="DIGITISATION" @selected(($requestContext ?? 'NEW') === 'DIGITISATION')>Digitisation</option>
                            <option value="ALTERATION" @selected(($requestContext ?? 'NEW') === 'ALTERATION')>Alteration</option>
                        </select>
                        <small class="text-muted d-block mt-1">
                            Renewal → <code>REN-...</code> · Alteration → <code>ALT-...</code>. Upload only changed files.
                        </small>
                    </div>
                    <div class="form-group mb-2">
                        <select class="custom-select custom-select-sm" name="application_id" required>
                            <option value="">Choose application...</option>
                            @foreach($applications as $app)
                                <option value="{{ $app->id }}" @selected($application && $application->id === $app->id)>
                                    {{ $app->application_no }}
                                    @if($app->isChildApplication() && $app->parentApplication)
                                        (parent: {{ $app->parentApplication->application_no }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-outline-primary btn-sm btn-block">Select</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        @if(!$application)
            <div class="alert alert-info mb-0">
                <i class="fa fa-info-circle mr-1"></i> Create or select an application to add education & experience rows.
            </div>
        @else
            <div class="card shadow-sm mb-3">
                <div class="card-body py-2 d-flex justify-content-between align-items-center">
                    <div>
                        <strong>{{ $application->application_no }}</strong> — {{ $application->applicant_name }}
                        @php
                            $appStatusClass = match ($application->status) {
                                'APPROVED', 'A' => 'success',
                                'SUBMITTED' => 'info',
                                'REJECTED', 'R' => 'danger',
                                'PENDING', 'P' => 'warning',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge badge-{{ $appStatusClass }} ml-2">{{ $application->status }}</span>
                        <span class="badge badge-secondary ml-1">{{ $requestContext ?? 'NEW' }}</span>
                        @if($application->isChildApplication() && $application->parentApplication)
                            <small class="text-muted ml-2">Parent: {{ $application->parentApplication->application_no }}</small>
                        @endif
                    </div>
                    <div>
                    <a href="{{ route('document-version.sample.details', $application->id) }}" class="btn btn-outline-info btn-sm mr-2">
                        <i class="fa fa-user-circle mr-1"></i> View Profile
                    </a>
                    @if(($requestContext ?? 'NEW') === 'ALTERATION' || $application->isAlterationApplication())
                    <a href="{{ route('document-version.sample.alteration') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-exchange mr-1"></i> Alteration Requests
                    </a>
                    @endif
                    </div>
            </div>

            <form method="POST" action="{{ route('document-version.sample.rows.store') }}" enctype="multipart/form-data" id="application-rows-form">
                @csrf
                <input type="hidden" name="application_id" value="{{ $application->id }}">

                {{-- Education --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <span class="font-weight-bold">5. Educational Qualifications</span>
                        <small class="text-muted ml-2">(Upload the documents)</small>
                    </div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0" id="education-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th rowspan="2" class="align-middle text-center" style="width:45px;">S.No</th>
                                        <th rowspan="2" class="align-middle">Education Level</th>
                                        <th rowspan="2" class="align-middle">Institution / School Name</th>
                                        <th rowspan="2" class="align-middle" style="width:110px;">Year of Passing</th>
                                        <th rowspan="2" class="align-middle" style="width:110px;">Percentage / Grade</th>
                                        <th rowspan="2" class="align-middle">
                                            Upload Document
                                            <br><small class="text-muted font-weight-normal">PDF (max {{ config('document_versioning.max_file_size_kb') }} KB)</small>
                                            @if(in_array($requestContext ?? 'NEW', ['RENEWAL', 'ALTERATION'], true))
                                                <br><small class="text-muted font-weight-normal">Parent file · Replaced · Replacement pending</small>
                                            @endif
                                        </th>
                                        <th rowspan="2" class="text-center align-middle" style="width:55px;">
                                            <button type="button" class="btn btn-primary btn-sm btn-add-education" title="Add row">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="education-container">
                                    @forelse($educationRows as $index => $row)
                                        @include('document-version.partials.education-row', [
                                            'row' => $row,
                                            'serial' => $index + 1,
                                            'index' => $index,
                                            'currentYear' => $currentYear,
                                            'requestContext' => $requestContext ?? 'NEW',
                                        ])
                                    @empty
                                        @include('document-version.partials.education-row', [
                                            'row' => [],
                                            'serial' => 1,
                                            'index' => 0,
                                            'currentYear' => $currentYear,
                                            'requestContext' => $requestContext ?? 'NEW',
                                        ])
                                    @endforelse
                                    @if($educationRows->isNotEmpty())
                                        @include('document-version.partials.education-row', [
                                            'row' => [],
                                            'serial' => $educationRows->count() + 1,
                                            'index' => $educationRows->count(),
                                            'currentYear' => $currentYear,
                                            'requestContext' => $requestContext ?? 'NEW',
                                        ])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Experience --}}
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <span class="font-weight-bold">6. Work Experience Details</span>
                        <small class="text-muted ml-2">(Upload the documents)</small>
                    </div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0" id="experience-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center align-middle" style="width:45px;">S.No</th>
                                        <th class="align-middle">Company Name / Contractor</th>
                                        <th class="align-middle" style="width:100px;">Years of Experience</th>
                                        <th class="align-middle">Designation</th>
                                        <th class="align-middle">
                                            Upload Document
                                            <br><small class="text-muted font-weight-normal">PDF (max {{ config('document_versioning.max_file_size_kb') }} KB)</small>
                                            @if(in_array($requestContext ?? 'NEW', ['RENEWAL', 'ALTERATION'], true))
                                                <br><small class="text-muted font-weight-normal">Parent file · Replaced · Replacement pending</small>
                                            @endif
                                        </th>
                                        <th class="text-center align-middle" style="width:55px;">
                                            <button type="button" class="btn btn-primary btn-sm btn-add-experience" title="Add row">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="experience-container">
                                    @forelse($experienceRows as $index => $row)
                                        @include('document-version.partials.experience-row', [
                                            'row' => $row,
                                            'serial' => $index + 1,
                                            'index' => $index,
                                            'requestContext' => $requestContext ?? 'NEW',
                                        ])
                                    @empty
                                        @include('document-version.partials.experience-row', [
                                            'row' => [],
                                            'serial' => 1,
                                            'index' => 0,
                                            'requestContext' => $requestContext ?? 'NEW',
                                        ])
                                    @endforelse
                                    @if($experienceRows->isNotEmpty())
                                        @include('document-version.partials.experience-row', [
                                            'row' => [],
                                            'serial' => $experienceRows->count() + 1,
                                            'index' => $experienceRows->count(),
                                            'requestContext' => $requestContext ?? 'NEW',
                                        ])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="text-right mb-4">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save mr-1"></i> Save All Rows & Upload Documents
                    </button>
                </div>
            </form>

            {{-- Document summary --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white font-weight-bold">All Documents (d_documents)</div>
                <div class="card-body p-0">
                    @if($documents->isEmpty())
                        <p class="p-3 text-muted mb-0">No documents uploaded yet.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-dv table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Ref</th>
                                        <th>Type</th>
                                        <th>Active</th>
                                        <th>Pending</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($documents as $doc)
                                        <tr>
                                            <td>{{ $moduleTypes[$doc['module_type']] ?? $doc['module_type'] }}</td>
                                            <td>{{ $doc['module_ref_id'] ?? '—' }}</td>
                                            <td>{{ $documentTypes[$doc['document_type']] ?? $doc['document_type'] }}</td>
                                            <td>@if($doc['active_version']) v{{ $doc['active_version']->version_no }} @else — @endif</td>
                                            <td>@if($doc['pending_version']) v{{ $doc['pending_version']->version_no }} @else — @endif</td>
                                            <td>
                                                <a href="{{ route('document-version.sample.review', $doc['group_key']) }}" class="btn btn-outline-secondary btn-sm">Review</a>
                                                <a href="{{ route('document-version.sample.history', $doc['group_key']) }}" class="btn btn-outline-info btn-sm">History</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@if($application)
    @foreach($educationRows as $row)
        @if(!empty($row['id']))
            <form id="delete-education-{{ $row['id'] }}" method="POST"
                  action="{{ route('document-version.sample.educations.delete', $row['id']) }}" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endif
    @endforeach
    @foreach($experienceRows as $row)
        @if(!empty($row['id']))
            <form id="delete-experience-{{ $row['id'] }}" method="POST"
                  action="{{ route('document-version.sample.experiences.delete', $row['id']) }}" class="d-none">
                @csrf
                @method('DELETE')
            </form>
        @endif
    @endforeach
@endif

<template id="education-row-template">
    @include('document-version.partials.education-row', ['row' => [], 'serial' => '__SERIAL__', 'index' => '__INDEX__', 'currentYear' => $currentYear ?? date('Y'), 'requestContext' => $requestContext ?? 'NEW'])
</template>

<template id="experience-row-template">
    @include('document-version.partials.experience-row', ['row' => [], 'serial' => '__SERIAL__', 'index' => '__INDEX__', 'requestContext' => $requestContext ?? 'NEW'])
</template>
@endsection

@push('scripts')
<script>
(function () {
    var maxEducation = 5;
    var maxExperience = 5;
    var currentYear = {{ $currentYear ?? date('Y') }};

    function yearOptions() {
        var html = '<option value="">Select Year</option>';
        for (var y = currentYear; y >= 1980; y--) {
            html += '<option value="' + y + '">' + y + '</option>';
        }
        return html;
    }

    function refreshSerials(container, selector, cellClass) {
        container.querySelectorAll(selector).forEach(function (row, idx) {
            var cell = row.querySelector('.' + cellClass);
            if (cell) cell.textContent = String(idx + 1);
        });
    }

    function addEducationRow() {
        var container = document.getElementById('education-container');
        if (!container) return;
        if (container.querySelectorAll('.education-fields').length >= maxEducation) {
            alert('Maximum ' + maxEducation + ' education rows allowed.');
            return;
        }
        var tpl = document.getElementById('education-row-template');
        var count = container.querySelectorAll('.education-fields').length;
        var html = tpl.innerHTML.replace(/__SERIAL__/g, count + 1).replace(/__INDEX__/g, count);
        container.insertAdjacentHTML('beforeend', html);
        refreshSerials(container, '.education-fields', 'edu-serial');
    }

    function addExperienceRow() {
        var container = document.getElementById('experience-container');
        if (!container) return;
        if (container.querySelectorAll('.experience-fields').length >= maxExperience) {
            alert('Maximum ' + maxExperience + ' experience rows allowed.');
            return;
        }
        var tpl = document.getElementById('experience-row-template');
        var count = container.querySelectorAll('.experience-fields').length;
        var html = tpl.innerHTML.replace(/__SERIAL__/g, count + 1).replace(/__INDEX__/g, count);
        container.insertAdjacentHTML('beforeend', html);
        refreshSerials(container, '.experience-fields', 'exp-serial');
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-add-education')) {
            e.preventDefault();
            addEducationRow();
        }
        if (e.target.closest('.btn-add-experience')) {
            e.preventDefault();
            addExperienceRow();
        }
        if (e.target.closest('.btn-remove-education')) {
            var eduContainer = document.getElementById('education-container');
            if (eduContainer.querySelectorAll('.education-fields').length <= 1) {
                alert('At least one education row is required.');
                return;
            }
            e.target.closest('tr').remove();
            refreshSerials(eduContainer, '.education-fields', 'edu-serial');
        }
        if (e.target.closest('.btn-remove-experience')) {
            var expContainer = document.getElementById('experience-container');
            if (expContainer.querySelectorAll('.experience-fields').length <= 1) {
                alert('At least one experience row is required.');
                return;
            }
            e.target.closest('tr').remove();
            refreshSerials(expContainer, '.experience-fields', 'exp-serial');
        }
    });
})();
</script>
@endpush
