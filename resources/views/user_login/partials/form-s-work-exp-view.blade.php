<div class="work-exp-view-wrap" id="work-exp-view-wrap">
    @php
        $overallY = 0;
        $overallM = 0;
        $overallD = 0;

        if (isset($exp_details) && $exp_details->isNotEmpty()) {
            foreach ($exp_details as $expRow) {
                $fromIso = $expRow->from_date ? calendar_date_ymd($expRow->from_date) : '';
                $toIso = $expRow->to_date ? calendar_date_ymd($expRow->to_date) : '';
                $isTill = $fromIso !== '' && $toIso === '';

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
    <div class="wx-order-card">
        <div class="wx-summary-table-wrap">
            <table class="wx-summary-table" id="work-exp-view-table">
                <thead>
                    <tr>
                        <th class="wx-summary-th-sno">S.No</th>
                        <th>Employment Type</th>
                        <th class="wx-summary-th-org"><span class="wx-th-org-line">Organisation &amp;</span><span class="wx-th-org-line">Address</span></th>
                        <th>Designation</th>
                        <th>Nature of Work</th>
                        <th>Voltage Level</th>
                        <th>Transformer kVA</th>
                        <th>Total Experience</th>
                        <th>Attachment</th>
                    </tr>
                </thead>
                <tbody id="work-exp-view-tbody">
                    @if(isset($exp_details) && $exp_details->isNotEmpty())
                        @foreach($exp_details as $index => $expRow)
                            @include('user_login.partials.form-s-work-exp-view-row', ['expRow' => $expRow, 'sno' => $loop->iteration])
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">No work experience entries</td>
                        </tr>
                    @endif
                </tbody>
                @if(isset($exp_details) && $exp_details->isNotEmpty())
                <tfoot>
                    <tr class="wx-overall-exp-row">
                        <td colspan="7" class="wx-overall-exp-label-cell text-end">
                            <span class="wx-overall-exp-label">Total Experience</span>
                        </td>
                        <td class="work-row-summary-period">
                            <div class="wx-period-box wx-period-box--overall">
                                <div class="wx-period-duration">
                                    <div class="wx-period-dur-cell">
                                        <span class="wx-period-dur-num">{{ $overallY }}</span>
                                        <span class="wx-period-dur-lbl">Years</span>
                                    </div>
                                    <div class="wx-period-dur-cell">
                                        <span class="wx-period-dur-num">{{ $overallM }}</span>
                                        <span class="wx-period-dur-lbl">Months</span>
                                    </div>
                                    <div class="wx-period-dur-cell">
                                        <span class="wx-period-dur-num">{{ $overallD }}</span>
                                        <span class="wx-period-dur-lbl">Days</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
