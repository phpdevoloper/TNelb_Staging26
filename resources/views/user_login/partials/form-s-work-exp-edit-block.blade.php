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
                    @php
                        $overallY = 0;
                        $overallM = 0;
                        $overallD = 0;
                        if (isset($exp_details) && $exp_details->isNotEmpty()) {
                            foreach ($exp_details as $expRow) {
                                $fromIso = $expRow->from_date ? \Carbon\Carbon::parse($expRow->from_date)->format('Y-m-d') : '';
                                $toIso = $expRow->to_date ? \Carbon\Carbon::parse($expRow->to_date)->format('Y-m-d') : '';
                                // Previous: till was inferred when from_date was set and to_date was empty.
                                // $isTill = $fromIso !== '' && $toIso === '';
                                $isTill = (int) ($expRow->work_to_till_date ?? 0) === 1;
                                if (!$isTill && $fromIso !== '' && $toIso === '') {
                                    $isTill = true;
                                }
                                $yN = $expRow->total_y !== null ? (int) $expRow->total_y : 0;
                                $mN = $expRow->total_m !== null ? (int) $expRow->total_m : 0;
                                $dN = $expRow->total_d !== null ? (int) $expRow->total_d : 0;
                                if ($yN === 0 && $mN === 0 && $dN === 0 && $fromIso !== '' && ($isTill || $toIso !== '')) {
                                    $toEff = $isTill ? \Carbon\Carbon::today() : \Carbon\Carbon::parse($toIso);
                                    $fromDt = \Carbon\Carbon::parse($fromIso);
                                    if ($toEff->gte($fromDt)) {
                                        $diff = $fromDt->diff($toEff);
                                        $yN = (int) $diff->y;
                                        $mN = (int) $diff->m;
                                        $dN = (int) $diff->d;
                                    }
                                }
                                $overallY += $yN;
                                $overallM += $mN;
                                $overallD += $dN;
                            }
                            $overallM += intdiv($overallD, 30);
                            $overallD = $overallD % 30;
                            $overallY += intdiv($overallM, 12);
                            $overallM = $overallM % 12;
                        }
                    @endphp
                    @if (isset($exp_details) && $exp_details->isNotEmpty())
                    <tfoot class="wx-overall-exp-tfoot">
                        <tr class="wx-overall-exp-row">
                            <td colspan="7" class="wx-overall-exp-label-cell text-end">
                                <span class="wx-overall-exp-label">Total Experience</span>
                            </td>
                            <td class="work-row-summary-period">
                                <div class="wx-period-box wx-period-box--overall">
                                    <div class="wx-period-duration">
                                        <div class="wx-period-dur-cell">
                                            <span class="wx-period-dur-num wx-overall-y">{{ $overallY }}</span>
                                            <span class="wx-period-dur-lbl">Years</span>
                                        </div>
                                        <div class="wx-period-dur-cell">
                                            <span class="wx-period-dur-num wx-overall-m">{{ $overallM }}</span>
                                            <span class="wx-period-dur-lbl">Months</span>
                                        </div>
                                        <div class="wx-period-dur-cell">
                                            <span class="wx-period-dur-num wx-overall-d">{{ $overallD }}</span>
                                            <span class="wx-period-dur-lbl">Days</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                            @if ($workExpWithActions)
                            <td></td>
                            @endif
                        </tr>
                    </tfoot>
                    @endif
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
