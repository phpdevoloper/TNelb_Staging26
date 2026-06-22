<div class="card shadow-sm border-danger mt-3">
    <div class="card-header bg-danger text-white font-weight-bold">
        <i class="fa fa-exclamation-triangle mr-1"></i> Reset Module (Testing)
    </div>
    <div class="card-body">
        <p class="small mb-2">
            Permanently deletes <strong>all data</strong> from these four tables, resets their ID sequences to 1, and removes all uploaded files:
        </p>
        <ul class="small mb-3">
            <li><code>d_applications</code> — {{ $moduleCounts['applications'] ?? 0 }} row(s)</li>
            <li><code>d_educations</code> — {{ $moduleCounts['educations'] ?? 0 }} row(s)</li>
            <li><code>d_experiences</code> — {{ $moduleCounts['experiences'] ?? 0 }} row(s)</li>
            <li><code>d_documents</code> — {{ $moduleCounts['documents'] ?? 0 }} row(s)</li>
            <li><code>storage/app/documents/</code> — temp &amp; permanent folders</li>
        </ul>

        <form method="POST" action="{{ route('document-version.sample.reset-module') }}"
              onsubmit="return confirm('This will permanently delete ALL module data and files. Continue?');">
            @csrf
            <div class="form-group mb-2">
                <label for="confirm_phrase" class="small font-weight-bold">Type <code>DELETE ALL</code> to confirm</label>
                <input type="text" class="form-control form-control-sm @error('confirm_phrase') is-invalid @enderror"
                       id="confirm_phrase" name="confirm_phrase" value="{{ old('confirm_phrase') }}"
                       placeholder="DELETE ALL" autocomplete="off">
                @error('confirm_phrase')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="form-group form-check mb-3">
                <input type="checkbox" class="form-check-input @error('confirm_check') is-invalid @enderror"
                       id="confirm_check" name="confirm_check" value="1" @checked(old('confirm_check'))>
                <label class="form-check-label small" for="confirm_check">
                    I understand this cannot be undone (testing module only).
                </label>
                @error('confirm_check')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="fa fa-trash mr-1"></i> Empty All Tables &amp; Delete Files
            </button>
        </form>
    </div>
</div>
