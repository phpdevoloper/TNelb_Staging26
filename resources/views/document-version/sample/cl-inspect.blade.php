@extends('document-version.layout')

@section('title', 'CL Inspect — Form A Data')

@section('content')
@php
    $pathUrl = static function (?string $path): ?string {
        return cl_document_path_url($path);
    };
    $tables = $tables ?? [];
@endphp

<h4 class="page-title mb-3"><i class="fa fa-search mr-2"></i>CL Application Inspect</h4>
<p class="text-muted mb-3">
    Enter a Form A contractor <code>application_id</code> (NEW <code>EA26…</code>, renewal/alteration <code>REA26…</code> / <code>AEA26…</code>)
    to view <code>ccl_forma_meta</code> and related live rows plus uploads.
    Delete removes this ID, descendant renewal/alteration IDs, and related table rows.
</p>

<div class="card shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('document-version.sample.cl-inspect') }}" class="form-inline flex-wrap">
            <label class="mr-2 mb-2" for="application_id">Application ID</label>
            <input type="text" class="form-control form-control-sm mr-2 mb-2" id="application_id" name="application_id"
                   value="{{ $applicationId }}" placeholder="e.g. EA261111111 or AEA261111112" style="min-width: 22rem;" required>
            <button type="submit" class="btn btn-primary btn-sm mb-2">
                <i class="fa fa-search mr-1"></i> Show
            </button>
        </form>
    </div>
</div>

@if($notFound)
    <div class="alert alert-warning">
        No Form A meta row found for <strong>{{ $applicationId }}</strong> in <code>ccl_forma_meta</code>.
    </div>
@elseif($meta)
    @php
        $relatedApplicationIds = $relatedApplicationIds ?? [ (string) $meta->application_id ];
        $childApplicationIds = $childApplicationIds ?? [];
        $deleteIdsLabel = implode(', ', $relatedApplicationIds);
        $countOf = static function (string $key) use ($tables): int {
            return (int) (($tables[$key]['rows'] ?? collect())->count());
        };
    @endphp
    <div class="mb-3 d-flex flex-wrap align-items-center">
        <span class="badge badge-primary mr-1 mb-1">{{ $metaTable }}</span>
        <span class="badge badge-light text-dark mr-1 mb-1">appl_type {{ $meta->appl_type ?: '—' }}</span>
        <span class="badge badge-light text-dark mr-1 mb-1">form {{ $meta->form_name ?: '—' }} {{ $meta->license_name ?: '' }}</span>
        @if(!empty($meta->old_application))
            <span class="badge badge-info mr-1 mb-1">old_application {{ $meta->old_application }}</span>
            <a class="btn btn-outline-secondary btn-sm mr-1 mb-1"
               href="{{ route('document-version.sample.cl-inspect', ['application_id' => $meta->old_application]) }}">
                Inspect parent
            </a>
        @endif
        @foreach($childApplicationIds as $childId)
            <a class="badge badge-warning text-dark mr-1 mb-1"
               href="{{ route('document-version.sample.cl-inspect', ['application_id' => $childId]) }}">
                child {{ $childId }}
            </a>
        @endforeach
        <button type="button" class="btn btn-danger btn-sm mb-1 ml-auto" data-toggle="modal" data-target="#clInspectDeleteModal">
            <i class="fa fa-trash mr-1"></i> Delete application &amp; related details
        </button>
    </div>

    <div class="modal fade" id="clInspectDeleteModal" tabindex="-1" role="dialog" aria-labelledby="clInspectDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="{{ route('document-version.sample.cl-inspect.delete') }}">
                @csrf
                <input type="hidden" name="application_id" value="{{ $meta->application_id }}">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="clInspectDeleteModalLabel">Delete Form A application</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2">This permanently deletes these application ID(s) and related rows:</p>
                        <p class="font-weight-bold mb-2">{{ $deleteIdsLabel }}</p>
                        <ul class="small mb-3">
                            <li><code>ccl_forma_meta</code> (this form and descendant IDs)</li>
                            <li><code>cl_ownership_table</code>, <code>cl_staff_tbl</code>, identity/address/attachment uploads</li>
                            <li><code>tnelb_equimentsuser_cl</code>, <code>cl_forma_signs</code>, <code>cl_forma_lic</code></li>
                            <li><code>tnelb_workflow_a</code>, <code>payments</code>, checklist / queries when present</li>
                        </ul>
                        <p class="small text-muted mb-2">The parent application is not deleted. Inspect the parent and delete from there if you need the whole chain.</p>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="confirm_check" id="clInspectConfirmCheck" value="1" required>
                            <label class="form-check-label" for="clInspectConfirmCheck">
                                I understand this cannot be undone.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fa fa-trash mr-1"></i> Delete
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="clInspectTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#panel-meta" role="tab">
                Application <span class="badge badge-light text-dark ml-1">1</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#panel-own" role="tab">
                Ownership <span class="badge badge-light text-dark ml-1">{{ $countOf('cl_ownership_table') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#panel-staff" role="tab">
                Staff <span class="badge badge-light text-dark ml-1">{{ $countOf('cl_staff_tbl') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#panel-uploads" role="tab">
                Uploads
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#panel-equip" role="tab">
                Equipment <span class="badge badge-light text-dark ml-1">{{ $countOf('tnelb_equimentsuser_cl') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#panel-lic" role="tab">
                License <span class="badge badge-light text-dark ml-1">{{ $countOf('cl_forma_lic') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#panel-wf" role="tab">
                Workflow <span class="badge badge-light text-dark ml-1">{{ $countOf('tnelb_workflow_a') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#panel-pay" role="tab">
                Payments <span class="badge badge-light text-dark ml-1">{{ $countOf('payments') }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#panel-other" role="tab">
                Other
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
                                        <td class="small">
                                            @if(in_array($column, ['aadhaar', 'pancard', 'gst_number'], true) && $value)
                                                ••••
                                            @else
                                                {{ is_scalar($value) || $value === null ? ($value === null || $value === '' ? '—' : $value) : json_encode($value) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="panel-own" role="tabpanel">
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'cl_ownership_table',
                'table' => $tables['cl_ownership_table'] ?? ['rows' => collect(), 'columns' => []],
                'pathColumns' => ['educ_qual_proof', 'educational_proof', 'age_proof'],
                'pathUrl' => $pathUrl,
            ])
        </div>

        <div class="tab-pane fade" id="panel-staff" role="tabpanel">
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'cl_staff_tbl',
                'table' => $tables['cl_staff_tbl'] ?? ['rows' => collect(), 'columns' => []],
                'pathUrl' => $pathUrl,
            ])
        </div>

        <div class="tab-pane fade" id="panel-uploads" role="tabpanel">
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'tnelb_applicant_doc_A',
                'table' => $tables['tnelb_applicant_doc_A'] ?? ['rows' => collect(), 'columns' => []],
                'pathColumns' => ['aadhaar_doc', 'pancard_doc', 'gst_doc', 'pan_doc'],
                'pathUrl' => $pathUrl,
            ])
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'tnelb_addressproof_cl',
                'table' => $tables['tnelb_addressproof_cl'] ?? ['rows' => collect(), 'columns' => []],
                'pathColumns' => ['file_doc'],
                'maskColumns' => ['addressproofno'],
                'pathUrl' => $pathUrl,
            ])
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'tnelb_attachments_cl',
                'table' => $tables['tnelb_attachments_cl'] ?? ['rows' => collect(), 'columns' => []],
                'pathColumns' => ['file_doc'],
                'pathUrl' => $pathUrl,
            ])
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'cl_forma_signs',
                'table' => $tables['cl_forma_signs'] ?? ['rows' => collect(), 'columns' => []],
                'pathColumns' => ['specimen_sign'],
                'pathUrl' => $pathUrl,
            ])
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'tnelb_temp_uploaded_documents',
                'table' => $tables['tnelb_temp_uploaded_documents'] ?? ['rows' => collect(), 'columns' => []],
                'pathColumns' => ['file_path', 'file_name'],
                'pathUrl' => $pathUrl,
            ])
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'mapping_digi_cls',
                'table' => $tables['mapping_digi_cls'] ?? ['rows' => collect(), 'columns' => []],
                'pathColumns' => ['cl_doc'],
                'pathUrl' => $pathUrl,
            ])
        </div>

        <div class="tab-pane fade" id="panel-equip" role="tabpanel">
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'tnelb_equimentsuser_cl',
                'table' => $tables['tnelb_equimentsuser_cl'] ?? ['rows' => collect(), 'columns' => []],
                'pathColumns' => ['testreport_file', 'purchasereport_file'],
                'pathUrl' => $pathUrl,
            ])
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'equipmentforma_tbls',
                'table' => $tables['equipmentforma_tbls'] ?? ['rows' => collect(), 'columns' => []],
                'pathUrl' => $pathUrl,
            ])
        </div>

        <div class="tab-pane fade" id="panel-lic" role="tabpanel">
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'cl_forma_lic',
                'table' => $tables['cl_forma_lic'] ?? ['rows' => collect(), 'columns' => []],
                'pathColumns' => ['license_pdf_en', 'license_pdf_ta', 'license_pdf_bilingual'],
                'pathUrl' => $pathUrl,
            ])
        </div>

        <div class="tab-pane fade" id="panel-wf" role="tabpanel">
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'tnelb_workflow_a',
                'table' => $tables['tnelb_workflow_a'] ?? ['rows' => collect(), 'columns' => []],
                'pathUrl' => $pathUrl,
            ])
        </div>

        <div class="tab-pane fade" id="panel-pay" role="tabpanel">
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'payments',
                'table' => $tables['payments'] ?? ['rows' => collect(), 'columns' => []],
                'pathUrl' => $pathUrl,
            ])
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'payment_transactions',
                'table' => $tables['payment_transactions'] ?? ['rows' => collect(), 'columns' => []],
                'pathUrl' => $pathUrl,
            ])
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'tnelb_banksolvency_a',
                'table' => $tables['tnelb_banksolvency_a'] ?? ['rows' => collect(), 'columns' => []],
                'pathUrl' => $pathUrl,
            ])
        </div>

        <div class="tab-pane fade" id="panel-other" role="tabpanel">
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'tnelb_query_applicable',
                'table' => $tables['tnelb_query_applicable'] ?? ['rows' => collect(), 'columns' => []],
                'pathUrl' => $pathUrl,
            ])
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'cl_checklist_applicant',
                'table' => $tables['cl_checklist_applicant'] ?? ['rows' => collect(), 'columns' => []],
                'pathUrl' => $pathUrl,
            ])
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'tnelb_cl_validitychecks',
                'table' => $tables['tnelb_cl_validitychecks'] ?? ['rows' => collect(), 'columns' => []],
                'pathUrl' => $pathUrl,
            ])
            @include('document-version.sample.partials.cl-inspect-table', [
                'title' => 'tnelb_ea_qc_models',
                'table' => $tables['tnelb_ea_qc_models'] ?? ['rows' => collect(), 'columns' => []],
                'pathUrl' => $pathUrl,
            ])
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
