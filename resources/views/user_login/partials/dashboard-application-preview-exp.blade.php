@php
    $hideVoltageFields = !empty($hideVoltageFields);
    $expRows = $exp_details ?? collect();
    $legacyEmpMap = [
        'company' => 'private_organisation',
        'contractor' => 'electrical_contractor',
        'apprentice' => 'apprenticeship',
        'electrical_inspector' => 'govt_organisation',
        'retired_employees' => 'retired_employee',
    ];
    $empLabels = [
        'private_organisation' => 'Private organisation',
        'electrical_contractor' => 'Electrical contractor',
        'retired_employee' => 'Retired Employee',
        'govt_organisation' => 'Govt organisation',
        'apprenticeship' => 'Apprenticeship',
        'board_member_tnelb' => 'Board Member / Ex. Board Member',
    ];
    $natureLabels = [
        'erection' => 'Erection',
        'maintenance' => 'Maintenance',
        'erection_maintenance' => 'Erection & Maintenance',
    ];
    $voltageLabels = [
        'up_to_650v' => 'Up to 650V',
        '650v_to_33kv' => 'Above 650V to 33KV',
        'above_33kv' => 'Above 33KV',
    ];
    $overallY = 0;
    $overallM = 0;
    $overallD = 0;
    $colCount = $hideVoltageFields ? 6 : 7;
@endphp
<div class="dash-prv-exp-wrap">
    <div class="dash-prv-table-wrap dash-prv-exp-table-wrap">
        <table class="dash-prv-table dash-prv-exp-table">
            <thead>
                <tr>
                    <th class="dash-prv-exp-sno">#</th>
                    <th>Employment</th>
                    <th>Organisation</th>
                    <th>Designation</th>
                    @unless($hideVoltageFields)
                        <th>Nature / Voltage / kVA</th>
                    @endunless
                    <th>Period</th>
                    <th>Documents</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($expRows as $expRow)
                    @php
                        $empTypeRaw = (string) ($expRow->emp_type ?? '');
                        $empType = $legacyEmpMap[$empTypeRaw] ?? $empTypeRaw;
                        $empTxt = $empLabels[$empType] ?? ($empType !== '' ? $empType : '—');
                        $contractorCat = '';
                        $licenceNo = '';
                        if ($empType === 'electrical_contractor') {
                            $stored = (string) ($expRow->emp_cate ?? '');
                            if ($stored !== '' && str_contains($stored, '||')) {
                                $parts = explode('||', $stored, 2);
                                $contractorCat = $parts[0] ?? '';
                                $licenceNo = $parts[1] ?? '';
                            } elseif ($stored !== '') {
                                $contractorCat = $stored;
                            }
                        }
                        $orgName = (string) ($expRow->org_name ?? $expRow->company_name ?? '');
                        if ($orgName === '' && $empType !== 'electrical_contractor' && ! empty($expRow->emp_cate)) {
                            $orgName = (string) $expRow->emp_cate;
                        }
                        $orgAddress = (string) ($expRow->org_address ?? '');
                        $designation = (string) ($expRow->designation ?? '');
                        $nature = (string) ($expRow->nature_work ?? '');
                        $natureTxt = $natureLabels[$nature] ?? ($nature !== '' ? $nature : '—');
                        $voltage = (string) ($expRow->voltage_level ?? '');
                        $voltTxt = $voltageLabels[$voltage] ?? ($voltage !== '' ? $voltage : '—');
                        $kvaRaw = ($expRow->transformer_kva !== null && $expRow->transformer_kva !== '') ? (string) $expRow->transformer_kva : '';
                        if ($kvaRaw !== '' && is_numeric($kvaRaw)) {
                            $kvaRaw = (string) (0 + $kvaRaw);
                        }
                        $kvaTxt = ($voltage === 'up_to_650v')
                            ? 'Not applicable'
                            : ($kvaRaw === '' ? '—' : ($kvaRaw === 'Above 1000' ? 'Above 1000' : ($kvaRaw . ' kVA')));

                        $fromIso = $expRow->from_date ? calendar_date_ymd($expRow->from_date) : '';
                        $toIso = $expRow->to_date ? calendar_date_ymd($expRow->to_date) : '';
                        $isTill = (int) ($expRow->work_to_till_date ?? 0) === 1;
                        if (! $isTill && $fromIso !== '' && $toIso === '') {
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
                        $durTxt = trim(implode(' ', array_filter([
                            $yN ? $yN . 'y' : null,
                            $mN ? $mN . 'm' : null,
                            $dN ? $dN . 'd' : null,
                        ])));
                        $fromDisp = $fromIso !== '' ? \Carbon\Carbon::createFromFormat('Y-m-d', $fromIso)->format('d-m-Y') : '—';
                        $toDisp = $isTill ? 'Till date' : ($toIso !== '' ? \Carbon\Carbon::createFromFormat('Y-m-d', $toIso)->format('d-m-Y') : '—');

                        $supportDoc = (string) ($expRow->support_document ?? $expRow->upload_document ?? '');
                        $relieveDoc = (string) ($expRow->releive_document ?? $expRow->relieve_document ?? '');
                        $supportDocUrl = $supportDoc !== ''
                            ? competency_document_url($supportDoc, 'experience', (int) ($expRow->id ?? $expRow->exp_id ?? 0), 'experience_doc')
                            : null;
                        $relieveDocUrl = $relieveDoc !== ''
                            ? competency_document_url($relieveDoc, 'experience', (int) ($expRow->id ?? $expRow->exp_id ?? 0), 'relieving_doc')
                            : null;
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="dash-prv-td-left">
                            <strong>{{ $empTxt }}</strong>
                            @if ($empType === 'electrical_contractor' && $contractorCat !== '')
                                <div class="dash-prv-exp-sub">Grade: {{ $contractorCat }}</div>
                            @endif
                            @if ($empType === 'electrical_contractor' && $licenceNo !== '')
                                <div class="dash-prv-exp-sub">Licence: {{ $licenceNo }}</div>
                            @endif
                        </td>
                        <td class="dash-prv-td-left">
                            {{ $orgName !== '' ? $orgName : '—' }}
                            @if ($orgAddress !== '')
                                <div class="dash-prv-exp-sub">{{ $orgAddress }}</div>
                            @endif
                        </td>
                        <td class="dash-prv-td-left">{{ $designation !== '' ? $designation : '—' }}</td>
                        @unless($hideVoltageFields)
                            <td class="dash-prv-td-left">
                                <div>{{ $natureTxt }}</div>
                                <div class="dash-prv-exp-sub">{{ $voltTxt }}</div>
                                <div class="dash-prv-exp-sub">{{ $kvaTxt }}</div>
                            </td>
                        @endunless
                        <td>
                            <div>{{ $fromDisp }}</div>
                            <div class="dash-prv-exp-sub">to {{ $toDisp }}</div>
                            @if ($durTxt !== '')
                                <div class="dash-prv-exp-dur">{{ $durTxt }}</div>
                            @endif
                        </td>
                        <td class="dash-prv-td-left">
                            <div class="dash-prv-exp-docs">
                                @if ($supportDocUrl)
                                    <a class="dash-prv-doc-pill" href="{{ $supportDocUrl }}" target="_blank" rel="noopener">
                                        <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Supporting
                                    </a>
                                @else
                                    <span class="dash-prv-doc-empty">Supporting: —</span>
                                @endif
                                @if ($isTill)
                                    <span class="dash-prv-doc-empty">Relieving: not required</span>
                                @elseif ($relieveDocUrl)
                                    <a class="dash-prv-doc-pill" href="{{ $relieveDocUrl }}" target="_blank" rel="noopener">
                                        <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Relieving
                                    </a>
                                @else
                                    <span class="dash-prv-doc-empty">Relieving: —</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $colCount }}" class="text-muted py-3 text-center">No work experience entries</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($expRows->isNotEmpty())
                @php
                    $overallM += intdiv($overallD, 30);
                    $overallD = $overallD % 30;
                    $overallY += intdiv($overallM, 12);
                    $overallM = $overallM % 12;
                    $overallTxt = trim(implode(' ', array_filter([
                        $overallY ? $overallY . 'y' : null,
                        $overallM ? $overallM . 'm' : null,
                        $overallD ? $overallD . 'd' : null,
                    ]))) ?: '0d';
                @endphp
                <tfoot>
                    <tr>
                        <td colspan="{{ $colCount - 2 }}" class="dash-prv-td-left"><strong>Total experience</strong></td>
                        <td><strong>{{ $overallTxt }}</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
