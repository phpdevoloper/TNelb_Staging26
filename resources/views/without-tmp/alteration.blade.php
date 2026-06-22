@extends('without-tmp.layout')

@section('title', 'Without Temp — Alteration')

@section('content')
@if(!$application)
    <div class="alert alert-warning">Select an application on the <a href="{{ route('without-tmp.index') }}">Dashboard</a>.</div>
@else
<h4 class="page-title mb-3"><i class="fa fa-exchange mr-2"></i>Alteration</h4>

<div class="alert alert-info">
    Pending alteration files are stored separately under <code>without_tmp/</code> with a new filename.
    The active file stays unchanged until a supervisor approves the alteration.
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <strong>{{ $application->application_code }}</strong> — {{ $application->applicant_name }}
        <span class="badge badge-{{ $application->status->badgeClass() }} ml-2">{{ $application->status->label() }}</span>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-header bg-white font-weight-bold">Alterable Items</div>
    <div class="card-body p-0">
        @if(empty($alterableItems))
            <p class="p-3 mb-0 text-muted">No alterable files available (application must be submitted/digitization and have active files).</p>
        @else
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr><th>Item</th><th>Current File</th><th></th></tr>
                </thead>
                <tbody>
                    @foreach($alterableItems as $item)
                        <tr>
                            <td>{{ $item['label'] }}</td>
                            <td>
                                <a href="{{ route('without-tmp.download', ['path' => $item['file_path'], 'name' => $item['file_name']]) }}" target="_blank">{{ $item['file_name'] }}</a>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('without-tmp.alteration.form', $item['target_key']) }}" class="btn btn-sm btn-primary">Request Alteration</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

@if($pendingRequests->isNotEmpty())
<div class="card shadow-sm">
    <div class="card-header bg-white font-weight-bold">Your Pending Alterations</div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead class="thead-light">
                <tr><th>New File</th><th>Reason</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($pendingRequests as $req)
                    <tr>
                        <td>{{ $req->new_file_name }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($req->reason, 60) }}</td>
                        <td><span class="badge badge-warning">{{ $req->status->value }}</span></td>
                        <td class="text-right">
                            <a href="{{ route('without-tmp.review.show', $req->id) }}" class="btn btn-sm btn-outline-secondary">Review</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endif
@endsection
