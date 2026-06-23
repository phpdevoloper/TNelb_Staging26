@php
    $ctx = strtoupper((string) ($requestContext ?? 'NEW'));
    $isRenewal = $ctx === 'RENEWAL';
    $isAlteration = $ctx === 'ALTERATION';
    $isFollowUp = $isRenewal || $isAlteration;
    $activeDoc = $row['document']['active'] ?? null;
    $pendingDoc = $row['document']['pending'] ?? null;
    $carriedForward = $activeDoc && str_contains((string) ($activeDoc->remarks ?? ''), 'Carried forward');
    $activePath = (string) ($activeDoc->file_path ?? '');
    $isReplacedFile = $activeDoc && (
        str_contains($activePath, '/RENEWAL/')
        || str_contains($activePath, '/ALTERATION/')
        || ((int) ($activeDoc->version_no ?? 1) > 1 && !$carriedForward)
    );
    $viewDoc = $pendingDoc ?: $activeDoc;
    $parentViewDoc = ($pendingDoc && $activeDoc) ? $activeDoc : null;
    $showReason = $isFollowUp && !$pendingDoc && ($carriedForward || $isReplacedFile);
    $reasonPlaceholder = $isRenewal ? 'Reason if replacing' : 'Reason if altering';
    $fileName = $viewDoc ? basename((string) ($viewDoc->file_path ?? '')) : null;

    if ($pendingDoc) {
        $docNote = 'Replacement pending';
        $noteClass = 'text-warning';
    } elseif ($isFollowUp && $carriedForward) {
        $docNote = 'Parent file';
        $noteClass = 'text-muted';
    } elseif ($isFollowUp && $isReplacedFile) {
        $docNote = 'Replaced';
        $noteClass = 'text-success';
    } elseif ($activeDoc) {
        $docNote = 'v' . $activeDoc->version_no;
        $noteClass = 'text-muted';
    } elseif ($isFollowUp) {
        $docNote = 'Upload required';
        $noteClass = 'text-muted';
    } else {
        $docNote = null;
        $noteClass = 'text-muted';
    }
@endphp
<input type="file" class="form-control-file form-control-sm" name="{{ $fileInputName }}"
       accept=".pdf,application/pdf" @disabled($pendingDoc)>
@if($pendingDoc)
    <small class="{{ $noteClass }} d-block mt-1" title="{{ $pendingDoc->file_path ?? '' }}">
        {{ $docNote }}@if($fileName) · {{ $fileName }}@endif ·
        <a href="{{ route('document-version.sample.download', $pendingDoc->id) }}" target="_blank">View</a>
    </small>
    @if($parentViewDoc)
        <small class="text-muted d-block mt-1" title="{{ $parentViewDoc->file_path ?? '' }}">
            Parent file · {{ basename((string) ($parentViewDoc->file_path ?? '')) }} ·
            <a href="{{ route('document-version.sample.download', $parentViewDoc->id) }}" target="_blank">View</a>
        </small>
    @endif
@elseif($docNote && $viewDoc)
    <small class="{{ $noteClass }} d-block mt-1" title="{{ $viewDoc->file_path ?? '' }}">
        {{ $docNote }}@if($fileName) · {{ $fileName }}@endif ·
        <a href="{{ route('document-version.sample.download', $viewDoc->id) }}" target="_blank">View</a>
    </small>
@elseif($docNote)
    <small class="{{ $noteClass }} d-block mt-1">{{ $docNote }}</small>
@endif
@if($showReason)
    <input type="text" class="form-control form-control-sm mt-1" name="{{ $reasonInputName }}"
           placeholder="{{ $reasonPlaceholder }}" maxlength="1000">
@endif
