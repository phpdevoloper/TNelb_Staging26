@extends('document-version.layout')

@section('title', 'Document Version — Upload')

@section('content')
@if(!$application)
    <div class="alert alert-warning mb-0">
        No application selected. <a href="{{ route('document-version.sample.index') }}">Go to dashboard</a> and select one.
    </div>
@else
<h4 class="page-title mb-4"><i class="fa fa-upload mr-2"></i>Upload Document</h4>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">Upload to d_documents</div>
            <div class="card-body">
                <form method="POST" action="{{ route('document-version.sample.upload.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="application_id" value="{{ $application->id }}">

                    <div class="form-group">
                        <label for="module_type">Module Type</label>
                        <select class="custom-select" id="module_type" name="module_type" required>
                            @foreach($moduleTypes as $key => $label)
                                <option value="{{ $key }}" @selected(old('module_type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="module-ref-wrap">
                        <label for="module_ref_id">Module Ref ID</label>
                        <select class="custom-select" id="module_ref_id" name="module_ref_id">
                            <option value="">—</option>
                        </select>
                        <small class="form-text text-muted">Required for education / experience modules.</small>
                    </div>

                    <div class="form-group">
                        <label for="document_type">Document Type</label>
                        <select class="custom-select" id="document_type" name="document_type" required>
                            @foreach($documentTypes as $key => $label)
                                <option value="{{ $key }}" @selected(old('document_type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="document_file">File</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="document_file" name="document_file" required
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="document_file">Choose file...</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="remarks">Remarks (optional)</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-upload mr-1"></i> Upload New Version
                    </button>
                </form>
            </div>
        </div>

        <div class="alert alert-light border mt-3 small mb-0">
            <strong>Note:</strong> Files are never overwritten. Each upload creates a new version with
            <span class="badge badge-warning">Pending</span> status until fully approved.
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">
                Application {{ $application->application_no }} — document groups
            </div>
            <div class="card-body p-0">
                @if($documents->isEmpty())
                    <p class="p-3 text-muted mb-0">No documents yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-dv mb-0">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Ref</th>
                                    <th>Type</th>
                                    <th>Active File</th>
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
                                        <td>
                                            @if($doc['active_version'])
                                                v{{ $doc['active_version']->version_no }} —
                                                <a href="{{ route('document-version.sample.download', $doc['active_version']->id) }}"
                                                   target="_blank" rel="noopener noreferrer" title="Open document">
                                                    {{ $doc['active_version']->file_name }}
                                                </a>
                                            @else
                                                <span class="text-muted">None approved</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($doc['pending_version'])
                                                v{{ $doc['pending_version']->version_no }}
                                            @else — @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('document-version.sample.review', $doc['group_key']) }}"
                                                   class="btn btn-outline-primary">Review</a>
                                                <a href="{{ route('document-version.sample.history', $doc['group_key']) }}"
                                                   class="btn btn-outline-secondary">History</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var educations = @json($educationOptions ?? []);
    var experiences = @json($experienceOptions ?? []);
    var moduleType = document.getElementById('module_type');
    var moduleRef = document.getElementById('module_ref_id');
    var wrap = document.getElementById('module-ref-wrap');
    var fileInput = document.getElementById('document_file');

    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var label = this.nextElementSibling;
            if (label) {
                label.textContent = this.files.length ? this.files[0].name : 'Choose file...';
            }
        });
    }

    function refreshRefOptions() {
        var type = moduleType.value;
        moduleRef.innerHTML = '<option value="">—</option>';
        var items = type === 'education' ? educations : (type === 'experience' ? experiences : []);
        wrap.style.display = (type === 'application') ? 'none' : 'block';
        moduleRef.required = type !== 'application';
        items.forEach(function (item) {
            var opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.label;
            moduleRef.appendChild(opt);
        });
    }

    moduleType.addEventListener('change', refreshRefOptions);
    refreshRefOptions();
})();
</script>
@endpush
@endif
@endsection
