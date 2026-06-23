@props(['fileName' => null, 'filePath' => null, 'title' => 'File', 'status' => null, 'statusClass' => 'secondary', 'badge' => null, 'uploadedAt' => null, 'remarks' => null])

@if(!$fileName || !$filePath)
    <p class="text-muted mb-0">None</p>
@else
    <dl class="row mb-0">
        <dt class="col-sm-4">{{ $title }}</dt>
        <dd class="col-sm-8">{{ $title === 'Active' ? 'Current file' : 'Replacement file' }}</dd>

        <dt class="col-sm-4">File Name</dt>
        <dd class="col-sm-8">
            <a href="{{ route('without-tmp.download', ['path' => $filePath, 'name' => $fileName]) }}"
               target="_blank" rel="noopener noreferrer" title="Open file">
                <i class="fa fa-file-o mr-1"></i>{{ $fileName }}
            </a>
        </dd>

        @if($status)
            <dt class="col-sm-4">Status</dt>
            <dd class="col-sm-8">
                <span class="badge badge-{{ $statusClass }}">{{ $status }}</span>
                @if($badge)
                    <span class="badge badge-primary ml-1">{{ $badge }}</span>
                @endif
            </dd>
        @endif

        @if($uploadedAt)
            <dt class="col-sm-4">Uploaded</dt>
            <dd class="col-sm-8">{{ $uploadedAt }}</dd>
        @endif

        @if($remarks)
            <dt class="col-sm-4">Remarks</dt>
            <dd class="col-sm-8">{{ $remarks }}</dd>
        @endif
    </dl>
@endif
