<style>
    /* External return to applicant (SE/PR + QU) — purple */
    #timelineMinimal .timeline-line .item-timeline .t-text p.t-meta-applicant-return {
        font-size: 12px;
        color: #9611b1;
        font-weight: 500;
    }
    /* Internal query / internal return details — red */
    #timelineMinimal .timeline-line .item-timeline .t-text p.t-meta-internal {
        font-size: 12px;
        color: #b91c1c;
        font-weight: 500;
    }
</style>
<div id="timelineMinimal" class="layout-spacing mt-4">
    <div class="statbox widget box box-shadow">
        <div class="widget-header">
            <div class="row">
                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <h4>Application - Process Flow</h4>
                </div>
            </div>
        </div>
        <div class="widget-content widget-content-area pb-1">
            <div class="mt-container mx-auto">
                <div class="timeline-line">
                    @foreach ($workflows as $row)
                        <div class="item-timeline">
                            <p class="t-time">{{ format_date_other($row->created_at) }}</p>

                            <div class="t-dot 
                                {{ $row->appl_status == 'RE' ? 't-dot-danger' : ($row->appl_status == 'A' ? 't-dot-success' : ($row->appl_status == 'QU' ? 't-dot-warning' : 't-dot-info')) }}">
                            </div>

                            <div class="t-text">
                                @php
                                    $processedBy = $row->processed_by;
                                    $applStatus = strtoupper((string) ($row->appl_status ?? ''));
                                    $roleLabel = match ($processedBy) {
                                        'SE' => 'Secretary',
                                        'PR' => 'President',
                                        'S', 'S2' => 'Supervisor',
                                        'A', 'AC' => 'Accountant',
                                        'AP' => 'Applicant',
                                        'Accountant' => 'Accountant',
                                        default => $processedBy,
                                    };
                                    $isApplicantResubmission = $applStatus === 'RE' && $processedBy === 'AP';
                                    $isExternalApplicantReturn = $applStatus === 'QU'
                                        && in_array($processedBy, ['SE', 'PR'], true);
                                    $hasPendingQuery = ($row->query_status ?? null) === 'P';
                                    $raisedByRaw = trim((string) ($row->raised_by ?? ''));
                                    if ($raisedByRaw !== '' && !ctype_digit($raisedByRaw)) {
                                        $queryRaisedByLabel = match ($raisedByRaw) {
                                            'SE' => 'Secretary',
                                            'PR' => 'President',
                                            'S', 'S2' => 'Supervisor',
                                            'A', 'AC' => 'Accountant',
                                            default => $raisedByRaw,
                                        };
                                    } else {
                                        $queryRaisedByLabel = $roleLabel;
                                    }
                                    $returnLogInternalQ = $row->return_log_internal_queries ?? [];
                                    if (!is_array($returnLogInternalQ)) {
                                        $returnLogInternalQ = [];
                                    }
                                    if ($returnLogInternalQ === [] && isset($row->return_queries) && is_array($row->return_queries)) {
                                        $returnLogInternalQ = $row->return_queries;
                                    }
                                    $returnLogInternalQ = array_values(array_filter(
                                        $returnLogInternalQ,
                                        static fn ($item) => is_string($item) && $item !== ''
                                    ));
                                    $forwardedRoleName = trim((string) ($row->role_name ?? $row->name ?? ''));
                                    $returnLogInternalRemark = $row->remarks
                                        ?? $row->return_remarks_raw
                                        ?? null;
                                    $returnRemarksDisplay = $row->return_remarks ?? $row->return_remarks_raw ?? null;
                                    $queries = $row->queries ?? null;
                                    if ($queries === null || $queries === '') {
                                        $queries = [];
                                    } elseif (is_string($queries)) {
                                        $decoded = json_decode($queries, true);
                                        $queries = is_array($decoded) ? $decoded : [];
                                    } elseif (!is_array($queries)) {
                                        $queries = [];
                                    }
                                    $queries = array_values(array_filter(
                                        $queries,
                                        static fn ($item) => is_string($item) && $item !== ''
                                    ));
                                    // SE/PR return: workflow.queries is often null; applicant list comes from return-log query_types (hydrated)
                                    $applicantFacingQueries = $returnLogInternalQ !== [] ? $returnLogInternalQ : $queries;
                                    $remarksInColoredBlock = $hasPendingQuery || $isExternalApplicantReturn;
                                @endphp

                                @if ($isApplicantResubmission)
                                    <p>Resubmitted by Applicant</p>
                                    {{-- @if (!empty($row->remarks))
                                        <p class="t-meta-time mb-0">
                                            <span class="fw-semibold">Remarks:</span> {{ $row->remarks }}
                                        </p>
                                    @endif --}}
                                @elseif ($isExternalApplicantReturn)
                                    <p>Returned by {{ $roleLabel }}</p>
                                @elseif ($applStatus === 'RE')
                                    <p>Returned by {{ $roleLabel }}</p>
                                @elseif ($applStatus === 'A')
                                    <p>Approved by {{ $roleLabel }}</p>
                                @elseif ($applStatus === 'RJ')
                                    <p class="text-danger">Rejected by {{ $roleLabel }}</p>
                                @else
                                    <p>Processed by {{ $roleLabel }}</p>
                                @endif

                                @if (!$isApplicantResubmission)
                                    <p class="t-meta-time">
                                        @if ($applStatus === 'RJ')
                                            Reason: {{ $row->reject_reason }}
                                        @elseif ($forwardedRoleName !== '')
                                            Forwarded to {{ $forwardedRoleName }}
                                            @if (!$remarksInColoredBlock && !empty($row->remarks))
                                                <br>
                                                Remarks: {{ $row->remarks }}
                                            @endif
                                        @elseif ($isExternalApplicantReturn)
                                            {{-- Title + remarks live in purple block below --}}
                                        @endif
                                    </p>
                                @endif

                                {{-- Case 1: Secretary/President → return to applicant (purple) --}}
                                @if ($isExternalApplicantReturn)
                                    <p class="t-meta-time">Application returned to Applicant</p>
                                    <p class="t-meta-time mb-0">
                                        <span class="fw-semibold">Remark :</span>
                                        {{ $returnLogInternalRemark !== null && $returnLogInternalRemark !== '' ? $returnLogInternalRemark : '—' }}
                                    </p>
                                    @if ($queries !== [] && $queries != $applicantFacingQueries)
                                        <p class="t-meta-internal mb-1">
                                            <span class="fw-semibold">Note: Query</span>
                                            ({{ implode(', ', $queries) }})
                                        </p>
                                    @endif
                                    @if ($applicantFacingQueries !== [])
                                        <p class="t-meta-applicant-return mb-1">
                                            <span class="fw-semibold">Note: Query</span>
                                            ({{ implode(', ', $applicantFacingQueries) }})
                                        </p>
                                    @endif
                                    <p class="t-meta-applicant-return mb-1">
                                        <span class="fw-semibold">Return remark :</span>
                                        {{ $returnRemarksDisplay !== null && $returnRemarksDisplay !== '' ? $returnRemarksDisplay : '—' }}
                                    </p>
                                @endif

                                {{-- Case 2: Supervisor / Accountant / internal — query raised (red) --}}
                                @if ($hasPendingQuery && !$isExternalApplicantReturn && !$isApplicantResubmission)
                                    <p class="t-meta-internal mb-1">
                                        <span class="fw-semibold">Query raised by {{ $queryRaisedByLabel }}</span>
                                        @if (!empty($queries))
                                            ({{ implode(', ', $queries) }})
                                        @endif
                                    </p>
                                    @if (!empty($row->remarks))
                                        <p class="t-meta-time mb-0">
                                            <span>Remark:</span>
                                            {{ $row->remarks }}
                                        </p>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <div class="item-timeline">
                        <p class="t-time">{{ format_date_other($user_entry->created_at) }}</p>
                        <div class="t-dot t-dot-warning"></div>
                        <div class="t-text">
                            <p>Received from Applicant</p>
                            <p class="t-meta-time">Form: {{ $user_entry->form_name }}, License: {{ $user_entry->license_name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
