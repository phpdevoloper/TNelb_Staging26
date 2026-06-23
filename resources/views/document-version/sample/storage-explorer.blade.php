@extends('document-version.layout')

@section('title', 'Document Version — Storage Explorer')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
    <h4 class="page-title mb-2 mb-md-0"><i class="fa fa-folder-open mr-2"></i>Storage Explorer</h4>
    <div>
        <a href="{{ route('document-version.sample.storage') }}" class="btn btn-outline-primary btn-sm mr-1">
            <i class="fa fa-refresh mr-1"></i> Refresh
        </a>
        <a href="{{ route('document-version.sample.table-data') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-table mr-1"></i> Table Data
        </a>
    </div>
</div>

<p class="text-muted small mb-3">
    Live view of files on disk. After upload → files appear under <code>temp/</code>.
    After submit → <strong>initial</strong> uploads copy to <code>permanent/</code>.
    Alteration uploads stay in <code>temp/</code> until approved, then overwrite the same file in <code>permanent/</code>.
    After reject → temp file removed (approved permanent copies remain).
</p>

<div class="row mb-3">
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm storage-stat-card h-100">
            <div class="card-body py-2 px-3">
                <div class="small text-muted">Physical root</div>
                <code class="small d-block storage-path">{{ $stats['physical_root'] }}</code>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm storage-stat-card h-100 border-warning">
            <div class="card-body py-2 px-3">
                <div class="small text-muted"><i class="fa fa-clock-o mr-1"></i>{{ $stats['temp_prefix'] }}/</div>
                <div class="h5 mb-0 text-warning">{{ $stats['temp_files'] }} <span class="small font-weight-normal">file(s)</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm storage-stat-card h-100 border-success">
            <div class="card-body py-2 px-3">
                <div class="small text-muted"><i class="fa fa-check-circle mr-1"></i>{{ $stats['permanent_prefix'] }}/</div>
                <div class="h5 mb-0 text-success">{{ $stats['permanent_files'] }} <span class="small font-weight-normal">file(s)</span></div>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card shadow-sm storage-stat-card h-100">
            <div class="card-body py-2 px-3">
                <div class="small text-muted">Total on disk</div>
                <div class="h5 mb-0">{{ $stats['files'] }} files · {{ $stats['directories'] }} folders</div>
            </div>
        </div>
    </div>
</div>

@if($selectedApplication)
    <div class="alert alert-info py-2 small mb-3">
        <i class="fa fa-info-circle mr-1"></i>
        Highlighting folder for selected application: <strong>{{ $selectedApplication->application_no }}</strong>
        (select another app on Dashboard to change highlight).
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-white font-weight-bold d-flex justify-content-between align-items-center">
        <span><i class="fa fa-sitemap mr-1"></i> Folder tree</span>
        <span class="badge badge-light">{{ $stats['files'] }} files</span>
    </div>
    <div class="card-body storage-tree-panel">
        @if(empty($tree))
            <div class="text-center text-muted py-4">
                <i class="fa fa-folder-open-o fa-2x mb-2 d-block"></i>
                No files yet. Upload a document from the Dashboard to see folders here.
            </div>
        @else
            @include('document-version.partials.storage-tree', [
                'nodes' => $tree,
                'highlightFolder' => $highlightFolder,
                'documentsByPath' => $documentsByPath,
                'tempPrefix' => $stats['temp_prefix'],
                'permanentPrefix' => $stats['permanent_prefix'],
            ])
        @endif
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-header bg-white font-weight-bold small">Expected structure</div>
    <div class="card-body py-2">
        <pre class="storage-structure-hint mb-0"><code>storage/app/documents/
├── temp/
│   └── {application_no}/
│       ├── education/
│       ├── experience/
│       └── identity/
└── permanent/
    └── {application_no}/
        ├── education/
        ├── experience/
        └── identity/</code></pre>
    </div>
</div>

@include('document-version.partials.module-reset', [
    'moduleCounts' => $moduleCounts ?? [
        'applications' => 0,
        'educations' => 0,
        'experiences' => 0,
        'documents' => 0,
    ],
])
@endsection

@push('styles')
<style>
    .storage-stat-card .h5 { font-size: 1.15rem; }
    .storage-path { word-break: break-all; }
    .storage-tree-panel { max-height: 520px; overflow: auto; }
    .storage-structure-hint {
        background: #f8f9fa; padding: .75rem; border-radius: .35rem;
        font-size: .8rem; color: #495057;
    }
</style>
@endpush
