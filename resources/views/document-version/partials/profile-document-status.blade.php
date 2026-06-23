@php
    $active = $document['active'] ?? null;
    $pending = $document['pending'] ?? null;
    $groupKey = $document['group_key'] ?? null;
@endphp

<div class="profile-doc-status mt-1">
    @if($active)
        <div class="profile-doc-pill approved">
            <i class="fa fa-check-circle mr-1"></i>
            Approved ·
            <a href="{{ route('document-version.sample.download', $active->id) }}"
               target="_blank" rel="noopener noreferrer" title="Open document">{{ $active->file_name }}</a>
        </div>
    @endif
    @if($pending)
        <div class="profile-doc-pill pending mt-1">
            <i class="fa fa-clock-o mr-1"></i>
            Pending review · v{{ $pending->version_no }}
            @if($groupKey)
                · <a href="{{ route('document-version.sample.review', $groupKey) }}">Review</a>
            @endif
        </div>
    @endif
    @if(!$active && !$pending)
        <span class="text-muted small"><i class="fa fa-file-o mr-1"></i>No document uploaded</span>
    @endif
</div>

@once
@push('styles')
<style>
    .profile-doc-pill {
        display: inline-block; font-size: .8rem; padding: .2rem .55rem;
        border-radius: 2rem; background: #fff; border: 1px solid #dee2e6;
    }
    .profile-doc-pill.approved { border-color: #c3e6cb; background: #f0fff4; color: #155724; }
    .profile-doc-pill.pending { border-color: #ffeeba; background: #fffdf5; color: #856404; }
    .profile-doc-pill a { font-weight: 600; }
</style>
@endpush
@endonce
