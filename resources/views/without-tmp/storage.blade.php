@extends('without-tmp.layout')

@section('title', 'Without Temp — Storage')

@section('content')
<h4 class="page-title mb-3"><i class="fa fa-folder-open mr-2"></i>Storage (without_tmp)</h4>

<p class="text-muted small">Physical root: <code>{{ $stats['physical_root'] }}</code> — {{ $stats['total_files'] }} file(s)</p>

<div class="card shadow-sm">
    <div class="card-header bg-white font-weight-bold">Folders</div>
    <div class="card-body">
        @include('without-tmp.partials.storage-tree', ['nodes' => $tree, 'depth' => 0])
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-header bg-white font-weight-bold small">Expected structure</div>
    <div class="card-body py-2">
        <pre class="mb-0"><code>storage/app/without_tmp/
├── Education/
├── Experience/
├── Photo/
├── Signature/
└── Documents/</code></pre>
    </div>
</div>
@endsection
