@extends('without-tmp.layout')

@section('title', 'Without Temp — Dashboard')

@section('content')
<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">Create Application</div>
            <div class="card-body">
                <form method="POST" action="{{ route('without-tmp.applications.store') }}">
                    @csrf
                    <div class="form-group">
                        <label>Applicant Name</label>
                        <input type="text" name="applicant_name" class="form-control form-control-sm" required>
                    </div>
                    <div class="form-group mb-2">
                        <label>Creation Type</label>
                        <select name="create_type" class="form-control form-control-sm">
                            <option value="draft">Draft</option>
                            <option value="digitization">Digitization</option>
                        </select>
                        <small class="text-muted d-block mt-1">
                            Only two choices at create. <strong>Submitted</strong> is set when you click Submit Application.
                            <strong>Alteration</strong> is set automatically when a replacement upload is requested.
                        </small>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm btn-block">
                        <i class="fa fa-plus mr-1"></i> Create
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mt-3">
            <div class="card-header bg-white font-weight-bold">Select Application</div>
            <div class="card-body">
                <form method="POST" action="{{ route('without-tmp.set-application') }}">
                    @csrf
                    <select name="application_id" class="form-control form-control-sm mb-2" required>
                        <option value="">— Select —</option>
                        @foreach($applications as $app)
                            <option value="{{ $app->id }}" @selected($application?->id === $app->id)>
                                {{ $app->application_code }} — {{ $app->applicant_name }} ({{ $app->status->label() }})
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary btn-sm btn-block">Select</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        @if(!$application)
            <div class="alert alert-info mb-0">Create or select an application to begin.</div>
        @else
            <div class="card shadow-sm mb-3">
                <div class="card-body py-2 d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <strong>{{ $application->application_code }}</strong> — {{ $application->applicant_name }}
                        <span class="badge badge-{{ $application->status->badgeClass() }} ml-2">{{ $application->status->label() }}</span>
                    </div>
                    <a href="{{ route('without-tmp.alteration') }}" class="btn btn-outline-primary btn-sm">Alteration</a>
                </div>
            </div>

            <form method="POST" action="{{ route('without-tmp.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="application_id" value="{{ $application->id }}">

                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white font-weight-bold">Applicant</div>
                    <div class="card-body py-2">
                        <input type="text" name="applicant_name" class="form-control form-control-sm"
                               value="{{ old('applicant_name', $application->applicant_name) }}">
                    </div>
                </div>

                {{-- Education --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white font-weight-bold">Education</div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0" id="edu-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Level</th>
                                        <th>Institution</th>
                                        <th>Year</th>
                                        <th>Grade</th>
                                        <th>
                                            File
                                            <br><small class="text-muted font-weight-normal">PDF (max {{ config('without_tmp.max_file_size_kb') }} KB)</small>
                                        </th>
                                        <th class="text-center align-middle" style="width:55px;">
                                            <button type="button" class="btn btn-primary btn-sm btn-add-education" title="Add row">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="education-container">
                                    @forelse($application->educations as $i => $edu)
                                        @include('without-tmp.partials.education-row', ['row' => $edu, 'index' => $i, 'pendingAlterations' => $pendingAlterations])
                                    @empty
                                        @include('without-tmp.partials.education-row', ['row' => null, 'index' => 0, 'pendingAlterations' => $pendingAlterations])
                                    @endforelse
                                    @if($application->educations->isNotEmpty())
                                        @include('without-tmp.partials.education-row', ['row' => null, 'index' => $application->educations->count(), 'pendingAlterations' => $pendingAlterations])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Experience --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white font-weight-bold">Experience</div>
                    <div class="card-body p-2">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0" id="exp-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Company</th>
                                        <th>Years</th>
                                        <th>Designation</th>
                                        <th>
                                            File
                                            <br><small class="text-muted font-weight-normal">PDF (max {{ config('without_tmp.max_file_size_kb') }} KB)</small>
                                        </th>
                                        <th class="text-center align-middle" style="width:55px;">
                                            <button type="button" class="btn btn-primary btn-sm btn-add-experience" title="Add row">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody id="experience-container">
                                    @forelse($application->experiences as $i => $exp)
                                        @include('without-tmp.partials.experience-row', ['row' => $exp, 'index' => $i, 'pendingAlterations' => $pendingAlterations])
                                    @empty
                                        @include('without-tmp.partials.experience-row', ['row' => null, 'index' => 0, 'pendingAlterations' => $pendingAlterations])
                                    @endforelse
                                    @if($application->experiences->isNotEmpty())
                                        @include('without-tmp.partials.experience-row', ['row' => null, 'index' => $application->experiences->count(), 'pendingAlterations' => $pendingAlterations])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Photo & Signature --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white font-weight-bold">Photo</div>
                            <div class="card-body">
                                @php
                                    $photoPending = $application->photo
                                        ? ($pendingAlterations['c_photo:' . $application->photo->id] ?? null)
                                        : null;
                                @endphp
                                @if($application->photo?->file_name && !$photoPending)
                                    <small class="d-block mb-1 text-success">Active: {{ $application->photo->file_name }}</small>
                                    <input type="text" class="form-control form-control-sm mb-2" name="photo_alteration_reason"
                                           value="{{ old('photo_alteration_reason') }}"
                                           placeholder="Alteration reason (required if replacing file)" maxlength="1000">
                                @elseif($photoPending)
                                    <small class="d-block mb-1 text-warning">
                                        Pending alteration · <a href="{{ route('without-tmp.review.show', $photoPending->id) }}">Review</a>
                                    </small>
                                @endif
                                <input type="file" name="photo_file" class="form-control-file form-control-sm" accept="image/*">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-white font-weight-bold">Signature</div>
                            <div class="card-body">
                                @php
                                    $signPending = $application->signature
                                        ? ($pendingAlterations['c_signature:' . $application->signature->id] ?? null)
                                        : null;
                                @endphp
                                @if($application->signature?->file_name && !$signPending)
                                    <small class="d-block mb-1 text-success">Active: {{ $application->signature->file_name }}</small>
                                    <input type="text" class="form-control form-control-sm mb-2" name="signature_alteration_reason"
                                           value="{{ old('signature_alteration_reason') }}"
                                           placeholder="Alteration reason (required if replacing file)" maxlength="1000">
                                @elseif($signPending)
                                    <small class="d-block mb-1 text-warning">
                                        Pending alteration · <a href="{{ route('without-tmp.review.show', $signPending->id) }}">Review</a>
                                    </small>
                                @endif
                                <input type="file" name="signature_file" class="form-control-file form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Documents --}}
                <div class="card shadow-sm mb-3">
                    <div class="card-header bg-white font-weight-bold">Supporting Documents</div>
                    <div class="card-body p-2">
                        <table class="table table-bordered table-sm mb-0" id="doc-table">
                            <thead class="thead-light">
                                <tr><th>Label</th><th>File</th><th></th></tr>
                            </thead>
                            <tbody>
                                @forelse($application->documents as $i => $doc)
                                    @include('without-tmp.partials.document-row', ['row' => $doc, 'index' => $i])
                                @empty
                                    @include('without-tmp.partials.document-row', ['row' => null, 'index' => 0])
                                @endforelse
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-doc"><i class="fa fa-plus"></i> Add Document</button>
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" name="action" value="draft" class="btn btn-secondary">
                        <i class="fa fa-save mr-1"></i> Save Draft
                    </button>
                    <button type="submit" name="action" value="submit" class="btn btn-success">
                        <i class="fa fa-paper-plane mr-1"></i> Submit Application
                    </button>
                </div>
            </form>

            @foreach($application->educations as $edu)
                <form id="delete-education-{{ $edu->id }}" method="POST"
                      action="{{ route('without-tmp.educations.delete', $edu->id) }}" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach
            @foreach($application->experiences as $exp)
                <form id="delete-experience-{{ $exp->id }}" method="POST"
                      action="{{ route('without-tmp.experiences.delete', $exp->id) }}" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>
            @endforeach

            @if($pendingReviewItems->isNotEmpty())
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white font-weight-bold">Document Review Status</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Upload Type</th>
                                        <th>Target</th>
                                        <th>Active File</th>
                                        <th>Pending File</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingReviewItems as $item)
                                        <tr>
                                            <td>{{ $item->upload_type }}</td>
                                            <td>{{ $item->target_table }} #{{ $item->target_row_id }}</td>
                                            <td>{{ $item->old_file_name ?? '—' }}</td>
                                            <td>{{ $item->new_file_name }}</td>
                                            <td>
                                                <a href="{{ route('without-tmp.review.show', $item->id) }}" class="btn btn-outline-secondary btn-sm">Review</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

@if($application)
<template id="education-row-template">
    @include('without-tmp.partials.education-row', ['row' => null, 'index' => '__INDEX__', 'pendingAlterations' => $pendingAlterations])
</template>
<template id="experience-row-template">
    @include('without-tmp.partials.experience-row', ['row' => null, 'index' => '__INDEX__', 'pendingAlterations' => $pendingAlterations])
</template>
@endif
@endsection

@push('scripts')
@if($application)
<script>
(function () {
    let eduIndex = {{ max($application->educations->count() + 1, 1) }};
    let expIndex = {{ max($application->experiences->count() + 1, 1) }};
    let docIndex = {{ $application->documents->count() + 1 }};

    function addEducationRow() {
        var container = document.getElementById('education-container');
        if (!container) return;
        var tpl = document.getElementById('education-row-template');
        var html = tpl.innerHTML.replace(/__INDEX__/g, eduIndex);
        container.insertAdjacentHTML('beforeend', html);
        eduIndex++;
    }

    function addExperienceRow() {
        var container = document.getElementById('experience-container');
        if (!container) return;
        var tpl = document.getElementById('experience-row-template');
        var html = tpl.innerHTML.replace(/__INDEX__/g, expIndex);
        container.insertAdjacentHTML('beforeend', html);
        expIndex++;
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
        }
        if (e.target.closest('.btn-remove-experience')) {
            var expContainer = document.getElementById('experience-container');
            if (expContainer.querySelectorAll('.experience-fields').length <= 1) {
                alert('At least one experience row is required.');
                return;
            }
            e.target.closest('tr').remove();
        }
    });

    $('#add-doc').on('click', function () {
        $('#doc-table tbody').append(`
            <tr>
                <td><input type="text" name="document_label[${docIndex}]" class="form-control form-control-sm"></td>
                <td><input type="file" name="document_file[${docIndex}]" class="form-control-file form-control-sm"></td>
                <td></td>
            </tr>`);
        docIndex++;
    });
})();
</script>
@endif
@endpush
