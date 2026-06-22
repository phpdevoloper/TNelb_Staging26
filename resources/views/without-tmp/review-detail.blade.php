@extends('without-tmp.layout')

@section('title', 'Without Temp — Review Detail')

@section('content')
<div class="mb-3">
    <a href="{{ route('without-tmp.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left mr-1"></i> Dashboard
    </a>
    <a href="{{ route('without-tmp.review') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-list mr-1"></i> Review Queue
    </a>
</div>

<h4 class="page-title mb-2">{{ $uploadTypeLabel }} — Review</h4>
<p class="text-muted mb-4">
    Application: <code>{{ $application->application_code }}</code> |
    Target: <code>{{ $targetLabel }}</code> |
    Table: <code>{{ $alteration->target_table }}</code> #{{ $alteration->target_row_id }}
</p>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-success h-100">
            <div class="card-header bg-success text-white font-weight-bold">Current Active File</div>
            <div class="card-body">
                @include('without-tmp.partials.file-card', [
                    'fileName' => $alteration->old_file_name,
                    'filePath' => $alteration->old_file_path,
                    'title' => 'Active',
                    'status' => 'Approved',
                    'statusClass' => 'success',
                    'badge' => 'Active',
                ])
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-warning h-100">
            <div class="card-header bg-warning font-weight-bold">Pending Alteration Request</div>
            <div class="card-body">
                @include('without-tmp.partials.file-card', [
                    'fileName' => $alteration->new_file_name,
                    'filePath' => $alteration->new_file_path,
                    'title' => 'Pending',
                    'status' => 'Pending Review',
                    'statusClass' => 'warning',
                    'badge' => 'Alteration',
                    'uploadedAt' => $alteration->created_at?->format('d M Y H:i'),
                    'remarks' => $alteration->reason,
                ])
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white font-weight-bold">Approval Status</div>
    <div class="card-body">
        <div class="d-flex align-items-center">
            <span class="badge badge-warning p-2 mr-3">
                <i class="fa fa-clock-o mr-1"></i> Awaiting {{ $reviewerLabel }} Review
            </span>
            <span class="text-muted small">Single-level approval (test mode)</span>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white font-weight-bold">Approval Actions</div>
    <div class="card-body">
        <form id="approval-form" method="POST">
            @csrf
            <div class="form-row align-items-end">
                <div class="form-group col-md-8">
                    <label for="remarks">Remarks</label>
                    <input type="text" class="form-control" id="remarks" name="remarks" placeholder="Optional note">
                </div>
                <div class="form-group col-md-4">
                    <label class="d-block">&nbsp;</label>
                    <div class="btn-group btn-block">
                        <button type="submit" formaction="{{ route('without-tmp.review.approve', $alteration->id) }}"
                                class="btn btn-success">
                            <i class="fa fa-check mr-1"></i> Approve
                        </button>
                        <button type="submit" formaction="{{ route('without-tmp.review.reject', $alteration->id) }}"
                                class="btn btn-danger">
                            <i class="fa fa-times mr-1"></i> Reject
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
