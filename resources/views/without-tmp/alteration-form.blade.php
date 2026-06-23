@extends('without-tmp.layout')

@section('title', 'Without Temp — Alteration Form')

@section('content')
<h4 class="page-title mb-3">Request Alteration</h4>

<div class="card shadow-sm">
    <div class="card-body">
        <p><strong>Application:</strong> {{ $application->application_code }}</p>
        <p><strong>Current file:</strong>
            @if($row->file_name)
                <a href="{{ route('without-tmp.download', ['path' => $row->file_path, 'name' => $row->file_name]) }}" target="_blank">{{ $row->file_name }}</a>
            @else
                —
            @endif
        </p>

        <form method="POST" action="{{ route('without-tmp.alteration.store', $targetKey) }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label>New File</label>
                <input type="file" name="file" class="form-control-file" required>
            </div>
            <div class="form-group">
                <label>Reason</label>
                <textarea name="reason" class="form-control" rows="3" required>{{ old('reason') }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit Alteration</button>
            <a href="{{ route('without-tmp.alteration') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
