@extends('document-version.layout')

@section('title', 'Document Version — Request Alteration')

@section('content')
@php
    $docTypeLabel = $documentTypes[$summary['document_type']] ?? $summary['document_type'];
    $moduleLabel = $moduleTypes[$summary['module_type']] ?? $summary['module_type'];
@endphp

<div class="mb-3">
    <a href="{{ route('document-version.sample.alteration') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left mr-1"></i> Back to Alteration List
    </a>
    <a href="{{ route('document-version.sample.history', $groupKey) }}" class="btn btn-sm btn-outline-info">
        <i class="fa fa-history mr-1"></i> History
    </a>
</div>

<h4 class="page-title mb-2">Request Alteration — {{ $docTypeLabel }}</h4>
<p class="text-muted mb-4">
    Module: <code>{{ $moduleLabel }}</code>
    @if($summary['module_ref_id']) | Ref: <code>{{ $summary['module_ref_id'] }}</code> @endif
</p>

<div class="row">
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm border-success">
            <div class="card-header bg-success text-white font-weight-bold">Current Approved Version (stays active)</div>
            <div class="card-body">
                @include('document-version.partials.document-card', ['version' => $activeVersion, 'title' => 'Active'])
            </div>
        </div>
    </div>

    <div class="col-lg-7 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-weight-bold">Submit Alteration Request</div>
            <div class="card-body">
                <form method="POST" action="{{ route('document-version.sample.alteration.store', $groupKey) }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="alteration_reason">Reason for alteration <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('alteration_reason') is-invalid @enderror"
                                  id="alteration_reason" name="alteration_reason" rows="3" required
                                  minlength="10" maxlength="1000"
                                  placeholder="Explain why this document is being replaced (min 10 characters)">{{ old('alteration_reason') }}</textarea>
                        @error('alteration_reason')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="document_file">Replacement file <span class="text-danger">*</span></label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('document_file') is-invalid @enderror"
                                   id="document_file" name="document_file" required
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="document_file">Choose file...</label>
                        </div>
                        @error('document_file')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Creates version {{ $activeVersion->version_no + 1 }} in temp folder. Pending until approved.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-exchange mr-1"></i> Submit Alteration Request
                    </button>
                </form>
            </div>
        </div>

        <div class="alert alert-light border mt-3 small mb-0">
            <strong>On approve:</strong> new version becomes active and moves to permanent folder.<br>
            <strong>On reject:</strong> temp file is deleted; current approved version stays active.
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var fileInput = document.getElementById('document_file');
    if (fileInput) {
        fileInput.addEventListener('change', function () {
            var label = this.nextElementSibling;
            if (label) {
                label.textContent = this.files.length ? this.files[0].name : 'Choose file...';
            }
        });
    }
})();
</script>
@endpush
@endsection
