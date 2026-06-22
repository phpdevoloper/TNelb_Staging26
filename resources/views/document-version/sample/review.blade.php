@extends('document-version.layout')

@section('title', 'Document Version — Review')

@section('content')
@php
    $docTypeLabel = $documentTypes[$summary['document_type']] ?? $summary['document_type'];
    $moduleLabel = $moduleTypes[$summary['module_type']] ?? $summary['module_type'];
    $currentLevel = $pendingVersion?->currentApprovalLevel();
    $reviewerLabel = $approvalLevels[1]['label'] ?? 'Reviewer';
@endphp

<div class="mb-3">
    <a href="{{ route('document-version.sample.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fa fa-arrow-left mr-1"></i> Dashboard
    </a>
    <a href="{{ route('document-version.sample.history', $groupKey) }}" class="btn btn-sm btn-outline-info">
        <i class="fa fa-history mr-1"></i> History
    </a>
</div>

<h4 class="page-title mb-2">{{ $docTypeLabel }} — Review</h4>
<p class="text-muted mb-4">
    Application ID: <code>{{ $summary['application_id'] }}</code> |
    Module: <code>{{ $moduleLabel }}</code>
    @if($summary['module_ref_id']) | Ref: <code>{{ $summary['module_ref_id'] }}</code> @endif
</p>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-success h-100">
            <div class="card-header bg-success text-white font-weight-bold">Current Active Version</div>
            <div class="card-body">
                @include('document-version.partials.document-card', ['version' => $activeVersion, 'title' => 'Active'])
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-warning h-100">
            <div class="card-header bg-warning font-weight-bold">
                @if($pendingVersion?->request_type === \App\Enums\DocumentRequestType::RENEWAL)
                    Pending Renewal Request
                @elseif($pendingVersion?->request_type === \App\Enums\DocumentRequestType::ALTERATION)
                    Pending Alteration Request
                @else
                    Pending Version
                @endif
            </div>
            <div class="card-body">
                @include('document-version.partials.document-card', ['version' => $pendingVersion, 'title' => 'Pending'])
            </div>
        </div>
    </div>
</div>

@if($pendingVersion)
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
                <input type="hidden" name="approval_level" value="1">
                <div class="form-row align-items-end">
                    <div class="form-group col-md-8">
                        <label for="remarks">Remarks</label>
                        <input type="text" class="form-control" id="remarks" name="remarks" placeholder="Optional note">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="d-block">&nbsp;</label>
                        <div class="btn-group btn-block">
                            <button type="submit" formaction="{{ route('document-version.sample.approve', $groupKey) }}"
                                    class="btn btn-success" @if(!$currentLevel) disabled @endif>
                                <i class="fa fa-check mr-1"></i> Approve
                            </button>
                            <button type="submit" formaction="{{ route('document-version.sample.reject', $groupKey) }}"
                                    class="btn btn-danger" @if(!$currentLevel) disabled @endif>
                                <i class="fa fa-times mr-1"></i> Reject
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@else
    <div class="alert alert-secondary mb-0">
        No pending version.
        @if($activeVersion)
            <a href="{{ route('document-version.sample.alteration.form', $groupKey) }}">Request an alteration</a>
        @else
            <a href="{{ route('document-version.sample.index') }}">Upload</a> an initial document.
        @endif
    </div>
@endif
@endsection
