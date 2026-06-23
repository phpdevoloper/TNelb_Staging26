@extends('document-version.layout')



@section('title', 'Document Version — Alteration Requests')



@section('content')

@if(!$application)

    <div class="alert alert-warning mb-0">

        No application selected. <a href="{{ route('document-version.sample.index') }}">Go to dashboard</a> and select one.

    </div>

@else

<div class="d-flex justify-content-between align-items-center mb-4">

    <h4 class="page-title mb-0"><i class="fa fa-exchange mr-2"></i>Document Alteration</h4>

    <a href="{{ route('document-version.sample.index') }}" class="btn btn-outline-secondary btn-sm">

        <i class="fa fa-arrow-left mr-1"></i> Dashboard

    </a>

</div>



<div class="alert alert-info">
    <strong>Alteration flow:</strong> Each alteration is a separate application
    (example: <code>ALT-20260617095019</code> for parent <code>APP-20260617095019</code>).
    All approved parent PDFs are copied to the ALT application first; only rows you replace are changed on submit.
    Replacement files are stored under <code>FORM_*/ALTERATION/...</code> and reviewed on the ALT application.
</div>



<div class="card shadow-sm mb-3">

    <div class="card-body py-2">

        <strong>{{ $application->application_no }}</strong> — {{ $application->applicant_name }}

        @if($parentApplication && $parentApplication->id !== $application->id)

            <span class="text-muted ml-2">Parent: {{ $parentApplication->application_no }}</span>

        @endif

    </div>

</div>



<div class="card shadow-sm">

    <div class="card-header bg-white font-weight-bold">Approved documents eligible for alteration</div>

    <div class="card-body p-0">

        @if($alterableDocuments->isEmpty())

            <p class="p-3 text-muted mb-0">

                No documents available for alteration. You need at least one approved document with no pending version on the parent application.

            </p>

        @else

            <div class="table-responsive">

                <table class="table table-bordered table-hover table-dv mb-0">

                    <thead>

                        <tr>

                            <th>Module</th>

                            <th>Ref</th>

                            <th>Document Type</th>

                            <th>Current Active</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($alterableDocuments as $doc)

                            <tr>

                                <td>{{ $moduleTypes[$doc['module_type']] ?? $doc['module_type'] }}</td>

                                <td>{{ $doc['module_ref_id'] ?? '—' }}</td>

                                <td>{{ $documentTypes[$doc['document_type']] ?? $doc['document_type'] }}</td>

                                <td>

                                    v{{ $doc['active_version']->version_no }} —

                                    <a href="{{ route('document-version.sample.download', $doc['active_version']->id) }}"

                                       target="_blank" rel="noopener noreferrer" title="Open document">

                                        {{ $doc['active_version']->file_name }}

                                    </a>

                                </td>

                                <td>

                                    <a href="{{ route('document-version.sample.alteration.form', $doc['group_key']) }}"

                                       class="btn btn-primary btn-sm">

                                        <i class="fa fa-exchange mr-1"></i> Request Alteration

                                    </a>

                                    <a href="{{ route('document-version.sample.history', $doc['group_key']) }}"

                                       class="btn btn-outline-secondary btn-sm">History</a>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>

</div>

@endif

@endsection

