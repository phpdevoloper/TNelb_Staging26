@extends('without-tmp.layout')

@section('title', 'Without Temp — Review')

@section('content')
<h4 class="page-title mb-2"><i class="fa fa-check-square-o mr-2"></i>Review Queue</h4>
<p class="text-muted mb-4">Pending alteration requests awaiting supervisor approval.</p>

@if($selectedApplicationId)
    <div class="alert alert-light border py-2 small">
        <i class="fa fa-filter mr-1"></i> Showing pending items for selected application (ID {{ $selectedApplicationId }}).
        <a href="{{ route('without-tmp.index') }}">Change selection on Dashboard</a>.
    </div>
@endif

@if($pendingReviewItems->isEmpty())
    <div class="alert alert-secondary mb-0">
        No pending alteration requests.
        <a href="{{ route('without-tmp.index') }}">Go to Dashboard</a>
    </div>
@else
    <div class="card shadow-sm">
        <div class="card-header bg-white font-weight-bold">Pending Alterations</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm table-wt mb-0">
                    <thead>
                        <tr>
                            <th>Application</th>
                            <th>Upload Type</th>
                            <th>Target</th>
                            <th>Active File</th>
                            <th>Pending File</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingReviewItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->application?->application_code }}</strong>
                                    <div class="small text-muted">{{ $item->application?->applicant_name }}</div>
                                </td>
                                <td>{{ $item->upload_type }}</td>
                                <td>{{ $item->target_table }} #{{ $item->target_row_id }}</td>
                                <td>{{ $item->old_file_name ?? '—' }}</td>
                                <td>{{ $item->new_file_name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($item->reason, 40) }}</td>
                                <td>
                                    <a href="{{ route('without-tmp.review.show', $item->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fa fa-gavel mr-1"></i> Review
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .table-wt thead th {
        background-color: var(--wt-primary);
        color: #fff;
        border-color: var(--wt-primary);
        vertical-align: middle;
    }
</style>
@endpush
