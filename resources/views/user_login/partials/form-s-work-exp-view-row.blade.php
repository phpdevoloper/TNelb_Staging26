@php
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
    $monthShort = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

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

    $fromIso = $expRow->from_date ? \Carbon\Carbon::parse($expRow->from_date)->format('Y-m-d') : '';
    $toIso = $expRow->to_date ? \Carbon\Carbon::parse($expRow->to_date)->format('Y-m-d') : '';
    $isTill = $fromIso !== '' && $toIso === '';

    $fmtPretty = function ($iso) use ($monthShort) {
        if ($iso === '') return '—';
        $p = explode('-', $iso);
        if (count($p) !== 3) return $iso;
        $y = (int) $p[0]; $m = (int) $p[1]; $d = (int) $p[2];
        if ($m < 1 || $m > 12) return $iso;
        return $d . ' ' . $monthShort[$m - 1] . ' ' . $y;
    };

    $yN = $expRow->total_y !== null ? (int) $expRow->total_y : 0;
    $mN = $expRow->total_m !== null ? (int) $expRow->total_m : 0;
    $dN = $expRow->total_d !== null ? (int) $expRow->total_d : 0;
    if ($yN === 0 && $mN === 0 && $dN === 0 && $fromIso !== '' && ($isTill || $toIso !== '')) {
        $toEff = $isTill ? \Carbon\Carbon::today() : \Carbon\Carbon::parse($toIso);
        $fromDt = \Carbon\Carbon::parse($fromIso);
        if ($toEff->gte($fromDt)) {
            $diff = $fromDt->diff($toEff);
            $yN = $diff->y; $mN = $diff->m; $dN = $diff->d;
        }
    }

    $supportDoc = (string) ($expRow->support_document ?? $expRow->upload_document ?? '');
    $relieveDoc = (string) ($expRow->releive_document ?? $expRow->relieve_document ?? '');
    $supportDocUrl = !empty($expRow->support_document_url)
        ? $expRow->support_document_url
        : ($supportDoc !== '' ? competency_document_url($supportDoc, 'experience', (int) ($expRow->id ?? $expRow->exp_id ?? 0), 'experience_doc') : null);
    $relieveDocUrl = !empty($expRow->releive_document_url)
        ? $expRow->releive_document_url
        : ($relieveDoc !== '' ? competency_document_url($relieveDoc, 'experience', (int) ($expRow->id ?? $expRow->exp_id ?? 0), 'relieving_doc') : null);
    $isAlterationNew = !empty($expRow->is_alteration_new);
    $sno = isset($sno) ? $sno : 1;
    $rowIndex = $rowIndex ?? ($sno - 1);
    $withActions = !empty($withActions);
@endphp
<tr class="work-exp-summary-tr{{ $isAlterationNew ? ' wx-alteration-alter-row' : '' }}" data-work-row-index="{{ $rowIndex }}">
    <td class="work-row-summary-sno text-center">{{ $sno }}</td>
    <td class="work-row-summary-employment">
        <span class="wx-sum-main">{{ $empTxt }}</span>
        @if($isAlterationNew)
            <span class="wx-alter-badge ms-1">ALTER</span>
        @endif
        @if($empType === 'electrical_contractor' && $contractorCat !== '')
            <span class="wx-sum-sub">Grade of Licence: {{ $contractorCat }}</span>
        @endif
        @if($empType === 'electrical_contractor' && $licenceNo !== '')
            <span class="wx-sum-sub">Licence No: {{ $licenceNo }}</span>
        @endif
    </td>
    <td class="work-row-summary-org-address">
        <span class="wx-sum-main">{{ $orgName !== '' ? $orgName : '—' }}</span>
        @if($orgAddress !== '')
            <span class="wx-sum-sub">{{ $orgAddress }}</span>
        @endif
    </td>
    <td class="work-row-summary-designation">{{ $designation !== '' ? $designation : '—' }}</td>
    <td class="work-row-summary-nature">{{ $natureTxt }}</td>
    <td class="work-row-summary-voltage">{{ $voltTxt }}</td>
    <td class="work-row-summary-kva text-center">{{ $kvaTxt }}</td>
    <td class="work-row-summary-period">
        <div class="wx-period-box">
            <div class="wx-period-dates">
                <div class="wx-period-mini">
                    <span class="wx-period-label">From</span>
                    <span class="wx-period-val">{{ $fmtPretty($fromIso) }}</span>
                </div>
                <div class="wx-period-mini">
                    <span class="wx-period-label">To</span>
                    <span class="wx-period-val">
                        @if($isTill)
                            <span class="prv-sw-badge-till" style="display:inline-block;background:#e8f4fd;color:#035ab3;border:1px solid #b8d4f0;border-radius:4px;padding:1px 6px;font-size:.68rem;font-weight:600;">Till date</span>
                        @else
                            {{ $fmtPretty($toIso) }}
                        @endif
                    </span>
                </div>
            </div>
            @if($fromIso !== '' && ($isTill || $toIso !== ''))
            <div class="wx-period-duration">
                <div class="wx-period-dur-cell"><span class="wx-period-dur-num">{{ $yN }}</span><span class="wx-period-dur-lbl">Years</span></div>
                <div class="wx-period-dur-cell"><span class="wx-period-dur-num">{{ $mN }}</span><span class="wx-period-dur-lbl">Months</span></div>
                <div class="wx-period-dur-cell"><span class="wx-period-dur-num">{{ $dN }}</span><span class="wx-period-dur-lbl">Days</span></div>
            </div>
            @endif
        </div>
    </td>
    <td class="work-row-summary-attachments">
        <div class="wx-sum-attach-stack">
            <div class="wx-sum-attach-block">
                <span class="wx-sum-attach-label">Supporting :</span>
                @if($supportDocUrl)
                    <a class="wx-sum-doc-link" href="{{ $supportDocUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View Document</a>
                @else
                    <span class="wx-sum-attach-value">—</span>
                @endif
            </div>
            <div class="wx-sum-attach-block">
                <span class="wx-sum-attach-label">Relieving :</span>
                @if($isTill)
                    <span class="wx-sum-attach-value">Not required (Till date)</span>
                @elseif($relieveDocUrl)
                    <a class="wx-sum-doc-link" href="{{ $relieveDocUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View Document</a>
                @else
                    <span class="wx-sum-attach-value">—</span>
                @endif
            </div>
        </div>
    </td>
    @if ($withActions)
    <td class="work-row-summary-actions">
        <div class="wx-summary-actions-inner">
            <button type="button" class="wx-order-edit-link work-row-edit-trigger" aria-label="Edit this work experience entry">
                <i class="fa fa-pencil" aria-hidden="true"></i> Edit
            </button>
            <button type="button" class="work-row-remove remove-work{{ !empty($expRow->id) ? ' remove_exp' : '' }}"
                @if(!empty($expRow->id)) data-exp_id="{{ $expRow->id }}" data-url="{{ route('delete_experience') }}" @endif
                title="Remove this entry" aria-label="Remove this work experience entry">
                <i class="fa fa-trash-o" aria-hidden="true"></i>
            </button>
        </div>
    </td>
    @endif
</tr>
