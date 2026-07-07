@php
    $workExpWithActions = $workExpWithActions ?? true;
    $showAddRow = $showAddRow ?? true;
    $showBoardMemberEmploymentType = $showBoardMemberEmploymentType ?? false;
@endphp
{{-- Form S work experience: summary table + editable row cards (apply-form-s parity) --}}
<div class="work-exp-wrap" id="edit-form-s-work-exp">
    @if ($showAddRow)
    <div class="work-exp-section-bar" id="work-exp-section-bar" role="region" aria-label="Work experience actions">
        <button type="button" class="work-exp-add-btn add-more-work" id="work-exp-add-btn" title="Add a work experience entry">
            <i class="fa fa-plus"></i>
            <span>Add row</span>
            <span class="work-exp-row-count" id="work-exp-row-count">(1/3)</span>
        </button>
    </div>
    @endif

    <div class="work-exp-summary-panel{{ (isset($exp_details) && $exp_details->isNotEmpty()) ? ' is-visible' : '' }}" id="work-exp-summary-panel" aria-live="polite">
        <div class="wx-order-card">
            <div class="wx-summary-table-wrap">
                <table class="wx-summary-table">
                    <thead>
                        <tr>
                            <th class="wx-summary-th-sno">S.No</th>
                            <th>Employment Type</th>
                            <th class="wx-summary-th-org"><span class="wx-th-stack-line">Organisation &amp;</span><span class="wx-th-stack-line">Address</span></th>
                            <th>Designation</th>
                            <th>Nature of Work</th>
                            <th>Voltage Level</th>
                            <th class="wx-summary-th-kva">Transformer kVA<br>(max 1000kVA)</th>
                            <th class="wx-summary-th-total-exp">Total<br>Experience</th>
                            <th>Attachment</th>
                            @if ($workExpWithActions)
                            <th class="wx-summary-th-actions">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody id="work-exp-summary-tbody">
                        @if (isset($exp_details) && $exp_details->isNotEmpty())
                            @foreach ($exp_details as $index => $expRow)
                                @include('user_login.partials.form-s-work-exp-view-row', [
                                    'expRow' => $expRow,
                                    'sno' => $loop->iteration,
                                    'rowIndex' => $index,
                                    'withActions' => $workExpWithActions,
                                ])
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="work-rows" id="work-container">
        @if (isset($exp_details) && $exp_details->isNotEmpty())
            @foreach ($exp_details as $index => $expRow)
                @include('user_login.partials.form-s-work-exp-row', [
                    'expRow' => $expRow,
                    'rowIndex' => $index,
                    'showBoardMemberEmploymentType' => $showBoardMemberEmploymentType,
                ])
            @endforeach
        @else
            @include('user_login.partials.form-s-work-exp-row', [
                'expRow' => null,
                'rowIndex' => 0,
                'showBoardMemberEmploymentType' => $showBoardMemberEmploymentType,
            ])
        @endif
    </div>

    <div id="work-exp-total-msg" class="work-exp-total-msg-wrap" aria-live="polite"></div>
</div>
