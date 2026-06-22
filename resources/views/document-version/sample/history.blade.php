@extends('document-version.layout')

@section('title', 'Document Version — History')

@section('content')
@php
    $docTypeLabel = $documentTypes[$summary['document_type']] ?? $summary['document_type'];
@endphp

<div class="mb-3">
    <a href="{{ route('document-version.sample.review', $groupKey) }}" class="btn btn-sm btn-outline-primary">
        <i class="fa fa-arrow-left mr-1"></i> Review
    </a>
    <a href="{{ route('document-version.sample.index') }}" class="btn btn-sm btn-outline-secondary">Dashboard</a>
</div>

<h4 class="page-title mb-4">{{ $docTypeLabel }} — Version History</h4>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-dv mb-0">
                <thead>
                    <tr>
                        <th>Version</th>
                        <th>File Name</th>
                        <th>Storage</th>
                        <th>Request</th>
                        <th>Status</th>
                        <th>Active</th>
                        <th>Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($versions as $version)
                        <tr>
                            <td>v{{ $version->version_no }}</td>
                            <td>
                                <a href="{{ route('document-version.sample.download', $version->id) }}"
                                   target="_blank" rel="noopener noreferrer" title="Open document">
                                    {{ $version->file_name }}
                                </a>
                                <div class="small text-muted">{{ $version->file_path }}</div>
                            </td>
                            <td>
                                <span class="badge badge-{{ ($version->storage_type ?? null) === \App\Enums\DocumentStorageType::PERMANENT ? 'success' : 'secondary' }}">
                                    {{ $version->storage_type?->label() ?? 'Temporary' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $version->request_type?->badgeClass() ?? 'secondary' }}">
                                    {{ $version->request_type?->label() ?? 'Initial Upload' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $version->status->badgeClass() }}">
                                    {{ $version->status->label() }}
                                </span>
                            </td>
                            <td>
                                @if($version->is_active)
                                    <span class="badge badge-primary">Yes</span>
                                @else
                                    No
                                @endif
                            </td>
                            <td>{{ $version->created_at?->format('d M Y H:i') ?? '—' }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary" type="button"
                                        data-toggle="collapse" data-target="#detail-{{ $version->id }}"
                                        aria-expanded="false">
                                    Details
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse" id="detail-{{ $version->id }}">
                            <td colspan="8" class="bg-light">
                                <div class="p-3">
                                    @if($version->remarks)
                                        <p class="mb-0"><strong>Remarks / audit:</strong><br>{!! nl2br(e($version->remarks)) !!}</p>
                                    @else
                                        <p class="mb-0 text-muted">No remarks recorded.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No versions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
