@extends('document-version.layout')

@section('title', 'CC Inspect — Application Data')

@section('content')
@php
    $pathUrl = static function (?string $path): ?string {
        $path = trim((string) ($path ?? ''));
        if ($path === '') {
            return null;
        }
        return competency_document_path_url($path);
    };
@endphp

<h4 class="page-title mb-3"><i class="fa fa-search mr-2"></i>CC Application Inspect</h4>
<p class="text-muted mb-3">
    Enter a competency <code>application_id</code> (NEW <code>SC26…</code> or alteration <code>ASC26…</code>)
    to view <code>cc_form_*_meta</code>, <code>cc_edu</code>, <code>cc_exp</code>, and <code>cc_proof_doc</code>.
</p>

<div class="card shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('document-version.sample.cc-inspect') }}" class="form-inline flex-wrap">
            <label class="mr-2 mb-2" for="application_id">Application ID</label>
            <input type="text" class="form-control form-control-sm mr-2 mb-2" id="application_id" name="application_id"
                   value="{{ $applicationId }}" placeholder="e.g. SC261111111 or ASC261111112" style="min-width: 22rem;" required>
            <button type="submit" class="btn btn-primary btn-sm mb-2">
                <i class="fa fa-search mr-1"></i> Show
            </button>
        </form>
    </div>
</div>

@if($notFound)
    <div class="alert alert-warning">
        No competency meta row found for <strong>{{ $applicationId }}</strong>
        in <code>cc_form_s_meta</code> / <code>cc_form_w_meta</code> / <code>cc_form_wh_meta</code> / <code>cc_form_p_meta</code>.
    </div>
@elseif($meta)
    <div class="mb-3">
        <span class="badge badge-primary">{{ $metaTable }}</span>
        <span class="badge badge-light text-dark">appl_type {{ $meta->appl_type ?: '—' }}</span>
        @if(!empty($meta->old_application))
            <span class="badge badge-info">old_application {{ $meta->old_application }}</span>
            <a class="btn btn-outline-secondary btn-sm ml-1"
               href="{{ route('document-version.sample.cc-inspect', ['application_id' => $meta->old_application]) }}">
                Inspect parent
            </a>
        @endif
    </div>

    <ul class="nav nav-tabs mb-3" id="ccInspectTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#panel-meta" role="tab">
                Application <span class="badge badge-light text-dark ml-1">1</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#panel-edu" role="tab">
                cc_edu <span class="badge badge-light text-dark ml-1">{{ $educations->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#panel-exp" role="tab">
                cc_exp <span class="badge badge-light text-dark ml-1">{{ $experiences->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#panel-proof" role="tab">
                cc_proof_doc <span class="badge badge-light text-dark ml-1">{{ $proofs->count() }}</span>
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="panel-meta" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-white font-weight-bold">{{ $metaTable }}</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm table-dv mb-0">
                            <tbody>
                                @foreach($meta->toArray() as $column => $value)
                                    <tr>
                                        <th class="text-nowrap" style="width: 18rem;">{{ $column }}</th>
                                        <td class="small">{{ is_scalar($value) || $value === null ? ($value === null || $value === '' ? '—' : $value) : json_encode($value) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="panel-edu" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-white font-weight-bold">cc_edu</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm table-dv mb-0">
                            <thead>
                                <tr>
                                    <th>edu_id</th>
                                    <th>application_id</th>
                                    <th>level</th>
                                    <th>institute</th>
                                    <th>month</th>
                                    <th>year</th>
                                    <th>certificate_no</th>
                                    <th>upload_document</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($educations as $row)
                                    @php $url = $pathUrl($row->upload_document); @endphp
                                    <tr>
                                        <td>{{ $row->edu_id }}</td>
                                        <td>{{ $row->application_id }}</td>
                                        <td>{{ $row->educational_level }}</td>
                                        <td>{{ $row->institute_name }}</td>
                                        <td>{{ $row->month_passing ?? '—' }}</td>
                                        <td>{{ $row->year_of_passing ?? '—' }}</td>
                                        <td>{{ $row->certificate_no ?? '—' }}</td>
                                        <td class="small">
                                            @if($row->upload_document)
                                                @if($url)
                                                    <a href="{{ $url }}" target="_blank" rel="noopener">View</a>
                                                @endif
                                                <div class="text-muted">{{ $row->upload_document }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted py-4">No cc_edu rows for this application_id.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="panel-exp" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-white font-weight-bold">cc_exp</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm table-dv mb-0">
                            <thead>
                                <tr>
                                    <th>exp_id</th>
                                    <th>application_id</th>
                                    <th>emp_type</th>
                                    <th>org_name</th>
                                    <th>designation</th>
                                    <th>from</th>
                                    <th>to</th>
                                    <th>Y/M/D</th>
                                    <th>support_document</th>
                                    <th>relieve_document</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($experiences as $row)
                                    @php
                                        $supportUrl = $pathUrl($row->support_document);
                                        $relieveUrl = $pathUrl($row->relieve_document ?? $row->releive_document);
                                        $relievePath = $row->relieve_document ?? $row->releive_document;
                                    @endphp
                                    <tr>
                                        <td>{{ $row->exp_id }}</td>
                                        <td>{{ $row->application_id }}</td>
                                        <td>{{ $row->emp_type ?? '—' }}</td>
                                        <td>{{ $row->org_name ?? '—' }}</td>
                                        <td>{{ $row->designation ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $row->from_date ?? '—' }}</td>
                                        <td class="text-nowrap">{{ $row->to_date ?? '—' }}</td>
                                        <td class="text-nowrap">{{ (int) $row->total_y }}/{{ (int) $row->total_m }}/{{ (int) $row->total_d }}</td>
                                        <td class="small">
                                            @if($row->support_document)
                                                @if($supportUrl)
                                                    <a href="{{ $supportUrl }}" target="_blank" rel="noopener">View</a>
                                                @endif
                                                <div class="text-muted">{{ $row->support_document }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="small">
                                            @if($relievePath)
                                                @if($relieveUrl)
                                                    <a href="{{ $relieveUrl }}" target="_blank" rel="noopener">View</a>
                                                @endif
                                                <div class="text-muted">{{ $relievePath }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center text-muted py-4">No cc_exp rows for this application_id.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="panel-proof" role="tabpanel">
            <div class="card shadow-sm">
                <div class="card-header bg-white font-weight-bold">cc_proof_doc</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm table-dv mb-0">
                            <thead>
                                <tr>
                                    <th>p_id</th>
                                    <th>application_id</th>
                                    <th>app_type</th>
                                    <th>proof_name</th>
                                    <th>proof_type</th>
                                    <th>proof_no</th>
                                    <th>proof_doc</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($proofs as $row)
                                    @php $url = $pathUrl($row->proof_doc); @endphp
                                    <tr>
                                        <td>{{ $row->p_id }}</td>
                                        <td>{{ $row->application_id }}</td>
                                        <td>{{ $row->app_type ?? '—' }}</td>
                                        <td>{{ $row->proof_name ?? '—' }}</td>
                                        <td>{{ $row->proof_type ?? '—' }}</td>
                                        <td>{{ $row->proof_no ? '••••' : '—' }}</td>
                                        <td class="small">
                                            @if($row->proof_doc)
                                                @if($url)
                                                    <a href="{{ $url }}" target="_blank" rel="noopener">View</a>
                                                @endif
                                                <div class="text-muted">{{ $row->proof_doc }}</div>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted py-4">No cc_proof_doc rows for this application_id.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endsection

@push('styles')
<style>
    .nav-tabs .nav-link { color: var(--tnelb-primary); }
    .nav-tabs .nav-link.active { font-weight: 600; color: var(--tnelb-primary); }
    .table-dv tbody th { background: #f8f9fa; font-weight: 600; }
</style>
@endpush
