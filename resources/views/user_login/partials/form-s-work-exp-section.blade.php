@php
    $exp_details = $exp_details ?? collect();
  
    $showBoardMemberEmploymentType = $showBoardMemberEmploymentType ?? false;
    $workContainerId = $workContainerId ?? 'work-container';
    $workAddBtnId = $workAddBtnId ?? 'work-exp-add-btn';
    $workRowCountId = $workRowCountId ?? 'work-exp-row-count';
    $workSummaryTbodyId = $workSummaryTbodyId ?? 'work-exp-summary-tbody';
    $workMaxRows = (int) ($workMaxRows ?? 3);
    $workMinRows = (int) ($workMinRows ?? 1);
    $workPart = $workPart ?? 'all';
    $defaultTillDate = !empty($defaultTillDate);
    $showSummaryPanel = $showSummaryPanel ?? true;
    $showAddRow = $showAddRow ?? true;
    $hideDuration = !empty($hideDuration);
    $hideDates = !empty($hideDates) || $workPart === 'current';
    $hideRemoveButton = !empty($hideRemoveButton);
    $hideBoardPanelNote = !empty($hideBoardPanelNote);
    $useBootstrapGrid = !empty($useBootstrapGrid);
    $hideUploadWhenDocExists = !empty($hideUploadWhenDocExists);
    $isAlterationMode = !empty($isAlterationMode);
    $lockExistingRows = !empty($lockExistingRows) || $isAlterationMode;
@endphp
<div class="work-exp-wrap" data-work-part="{{ $workPart }}">
    @if ($showAddRow)
    <div class="work-exp-section-bar" role="region" aria-label="Work experience actions">
        <button type="button"
            class="work-exp-add-btn add-more-work"
            id="{{ $workAddBtnId }}"
            data-work-container="{{ $workContainerId }}"
            data-max-rows="{{ $workMaxRows }}"
            title="Add a work experience entry">
            <i class="fa fa-plus"></i>
            <span>Add row</span>
            <span class="work-exp-row-count" id="{{ $workRowCountId }}">(0/{{ $workMaxRows }})</span>
        </button>
    </div>
    @endif

    @if ($showSummaryPanel)
        <div class="work-exp-summary-panel" id="work-exp-summary-panel-{{ $workPart }}" aria-live="polite">
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
                                <th class="wx-summary-th-kva">Transformer(kVA)</th>
                                <th class="wx-summary-th-total-exp">Total<br>Experience</th>
                                <th>Attachment</th>
                                <th class="wx-summary-th-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="{{ $workSummaryTbodyId }}"></tbody>
                        <tfoot class="wx-overall-exp-tfoot" hidden>
                            <tr class="wx-overall-exp-row">
                                <td colspan="7" class="wx-overall-exp-label-cell text-end">
                                    <span class="wx-overall-exp-label">Total Experience</span>
                                </td>
                                <td class="work-row-summary-period">
                                    <div class="wx-period-box wx-period-box--overall">
                                        <div class="wx-period-duration">
                                            <div class="wx-period-dur-cell">
                                                <span class="wx-period-dur-num wx-overall-y">0</span>
                                                <span class="wx-period-dur-lbl">Years</span>
                                            </div>
                                            <div class="wx-period-dur-cell">
                                                <span class="wx-period-dur-num wx-overall-m">0</span>
                                                <span class="wx-period-dur-lbl">Months</span>
                                            </div>
                                            <div class="wx-period-dur-cell">
                                                <span class="wx-period-dur-num wx-overall-d">0</span>
                                                <span class="wx-period-dur-lbl">Days</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="work-rows js-work-container"
        id="{{ $workContainerId }}"
        data-work-part="{{ $workPart }}"
        data-min-rows="{{ $workMinRows }}"
        data-max-rows="{{ $workMaxRows }}">
        @if ($exp_details->isNotEmpty())
            @foreach ($exp_details as $index => $expRow)
{{-- @php
            var_dump($expRow->till_date); exit;
            @endphp --}}
                @include('user_login.partials.form-s-work-exp-row', [
                    'expRow' => $expRow,
                    'rowIndex' => $index,
                    'workPart' => $workPart,
                    'showSummaryPanel' => $showSummaryPanel,
                    'showBoardMemberEmploymentType' => $showBoardMemberEmploymentType,
                    'defaultTillDate' => $defaultTillDate,
                    'hideDuration' => $hideDuration,
                    'hideDates' => $hideDates,
                    'hideRemoveButton' => $hideRemoveButton,
                    'hideBoardPanelNote' => $hideBoardPanelNote,
                    'useBootstrapGrid' => $useBootstrapGrid,
                    'hideUploadWhenDocExists' => $hideUploadWhenDocExists,
                    'alterationExistingRow' => $lockExistingRows && $expRow,
                ])
            @endforeach
        @elseif ($workMinRows > 0)
            @for ($index = 0; $index < $workMinRows; $index++)
                @include('user_login.partials.form-s-work-exp-row', [
                    'expRow' => null,
                    'rowIndex' => $index,
                    'workPart' => $workPart,
                    'showSummaryPanel' => $showSummaryPanel,
                    'showBoardMemberEmploymentType' => $showBoardMemberEmploymentType,
                    'defaultTillDate' => $defaultTillDate,
                    'hideDuration' => $hideDuration,
                    'hideDates' => $hideDates,
                    'hideRemoveButton' => $hideRemoveButton,
                    'hideBoardPanelNote' => $hideBoardPanelNote,
                    'useBootstrapGrid' => $useBootstrapGrid,
                    'hideUploadWhenDocExists' => $hideUploadWhenDocExists,
                    'alterationExistingRow' => false,
                ])
            @endfor
        @endif
    </div>

    <div id="work-exp-total-msg-{{ $workPart }}" class="work-exp-total-msg-wrap" aria-live="polite"></div>
</div>
