@props(['version', 'title' => 'Version'])

@if(!$version)
    <p class="text-muted mb-0">None</p>
@else
    <dl class="row mb-0">
        <dt class="col-sm-4">{{ $title }}</dt>
        <dd class="col-sm-8">v{{ $version->version_no }}</dd>

        <dt class="col-sm-4">File Name</dt>
        <dd class="col-sm-8">
            <a href="{{ route('document-version.sample.download', $version->id) }}"
               target="_blank" rel="noopener noreferrer" title="Open document">
                <i class="fa fa-file-pdf-o mr-1"></i>{{ $version->file_name }}
            </a>
        </dd>

        <dt class="col-sm-4">Status</dt>
        <dd class="col-sm-8">
            <span class="badge badge-{{ $version->status->badgeClass() }}">
                {{ $version->status->label() }}
            </span>
            <span class="badge badge-{{ $version->request_type?->badgeClass() ?? 'secondary' }} ml-1">
                {{ $version->request_type?->label() ?? 'Initial Upload' }}
            </span>
            @if($version->is_active)
                <span class="badge badge-primary ml-1">Active</span>
            @endif
        </dd>

        <dt class="col-sm-4">Uploaded</dt>
        <dd class="col-sm-8">{{ $version->created_at?->format('d M Y H:i') ?? '—' }}</dd>

        @if($version->remarks)
            <dt class="col-sm-4">Remarks</dt>
            <dd class="col-sm-8">{{ $version->remarks }}</dd>
        @endif
    </dl>
@endif
