@include('admin.include.top')
@include('admin.include.header')
@include('admin.include.navbar')
@php
    $formName = strtoupper((string) ($applicant->form_name ?? ''));
    $isFormP = $formName === 'P';
    $isFormS = $formName === 'S';
    $applType = strtoupper((string) ($applicant->appl_type ?? ''));
    $applTypeLabel = match ($applType) {
        'N' => 'New',
        'R' => 'Renewal',
        'D' => 'Digitisation',
        'A' => 'Alteration',
        default => $applType !== '' ? $applType : '—',
    };
    $showPayment = ! in_array($applType, ['D', 'A'], true);
    $isAlterationApp = $applType === 'A';
    $parentForAlter = $parentApplicantForAlter ?? null;
    $nameAltered = $isAlterationApp && $parentForAlter
        && trim((string) ($applicant->applicant_name ?? '')) !== trim((string) ($parentForAlter->applicant_name ?? $parentForAlter->applicants_name ?? ''));
    $parentAddressValue = trim((string) ($parentForAlter->applicants_address ?? $parentForAlter->applicant_address ?? ''));
    $addressAltered = $isAlterationApp && $parentForAlter
        && trim((string) ($applicant->applicants_address ?? $applicant->applicant_address ?? '')) !== $parentAddressValue;
    $hasAlteredWork = $isAlterationApp && ($workExperience ?? collect())->contains(fn ($row) => ! empty($row->is_alteration_new));
    $hasAlterationProofs = $isAlterationApp && ($alterationProofs ?? collect())->isNotEmpty();
    $resolveAlterProofUrl = static function ($proof) {
        if (! $proof) {
            return null;
        }
        if (! empty($proof->url)) {
            return $proof->url;
        }
        $storedPath = trim((string) ($proof->proof_doc ?? ''));

        return $storedPath !== '' ? competency_document_path_url($storedPath) : null;
    };
    $nameProofUrl = $resolveAlterProofUrl(($alterationProofs ?? collect())->first(fn ($proof) => ($proof->document_type ?? '') === 'name_proof'));
    $addressProofUrl = $resolveAlterProofUrl(($alterationProofs ?? collect())->first(fn ($proof) => ($proof->document_type ?? '') === 'address_proof'));
    $photo = $uploadedPhoto ?? $applicant_photo ?? null;
    $photoPath = $photo && ! empty($photo->upload_path) ? $photo->upload_path : null;
    $signPath = ! empty($uploadedSign?->uploaded_doc) ? $uploadedSign->uploaded_doc : null;
    $photoUrl = ! empty($photo?->media_url) ? $photo->media_url : ($photoPath ? competency_media_url($photoPath) : null);
    $signUrl = ! empty($uploadedSign?->media_url) ? $uploadedSign->media_url : ($signPath ? competency_media_url($signPath) : null);
    $decryptedAadhaar = displayProofNumber($applicant->aadhaar ?? '');
    $decryptedPan = displayProofNumber($applicant->pancard ?? '');
    $maskedAadhaar = strlen($decryptedAadhaar) === 12
        ? str_repeat('X', 8).substr($decryptedAadhaar, -4)
        : ($decryptedAadhaar !== '' ? 'Invalid Aadhaar' : '—');
    $maskedPan = strlen($decryptedPan) === 10
        ? str_repeat('X', 6).substr($decryptedPan, -4)
        : ($decryptedPan !== '' ? 'Invalid PAN' : '—');
    $aadhaarDocUrl = proof_document_url($applicant->aadhaar_doc ?? null, 'aadhaar');
    $panDocUrl = proof_document_url($applicant->pan_doc ?? $applicant->pancard_doc ?? null, 'pan');
    $paymentStatusRaw = strtoupper(trim((string) ($applicant->payment_status ?? '')));
    $paymentStatusLabel = match ($paymentStatusRaw) {
        'B' => 'Fee exempt',
        'Y', 'PAYMENT', 'PAID', 'SUCCESS' => 'Success',
        'N', 'DRAFT' => 'Draft',
        default => $paymentStatusRaw !== '' ? $paymentStatusRaw : 'N/A',
    };
    $paymentIsSuccess = in_array($paymentStatusRaw, ['Y', 'PAYMENT', 'PAID', 'SUCCESS', 'B'], true);
    $monthLabels = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
    $submittedRaw = $applicant->dt_submit ?? $applicant->created_at ?? null;
    $submittedLabel = $submittedRaw ? format_date($submittedRaw) : '—';
    $statusCode = strtoupper((string) ($applicant->appl_status ?? 'A'));
    $statusLabel = match ($statusCode) {
        'RJ' => 'Rejected',
        'A' => 'Approved',
        default => 'Approved',
    };
    $statusTone = $statusCode === 'RJ' ? 'danger' : 'ok';
    $amountLabel = is_numeric($applicant->amount ?? null)
        ? '₹ '.number_format((float) $applicant->amount, 2)
        : ($applicant->amount ?: '—');

    $roleOf = static function ($code) {
        return match ((string) $code) {
            'SE' => 'Secretary',
            'PR' => 'President',
            'S', 'S2' => 'Supervisor',
            'A', 'AC', 'AS' => 'Assistant Secretary',
            'AP' => 'Applicant',
            'Assistant Secretary' => 'Assistant Secretary',
            default => trim((string) $code) !== '' ? (string) $code : '—',
        };
    };
    $stringList = static function ($value) {
        if ($value === null || $value === '') {
            return [];
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, static fn ($item) => is_string($item) && $item !== ''));
    };
    $whenParts = static function ($value) {
        if (empty($value)) {
            return ['—', ''];
        }
        try {
            $dt = \Carbon\Carbon::parse($value);
        } catch (\Throwable $e) {
            return [(string) $value, ''];
        }

        return [$dt->format('d-m-Y'), $dt->format('h:i:s A')];
    };

    $flowEvents = [];
    foreach (($workflows ?? []) as $row) {
        $processedBy = (string) data_get($row, 'processed_by');
        $applStatus = strtoupper((string) (data_get($row, 'appl_status') ?? ''));
        $roleLabel = $roleOf($processedBy);
        $isApplicantResubmission = $applStatus === 'RE' && $processedBy === 'AP';
        $isExternalApplicantReturn = $applStatus === 'QU' && in_array($processedBy, ['SE', 'PR'], true);
        $hasPendingQuery = data_get($row, 'query_status') === 'P';
        $raisedByRaw = trim((string) (data_get($row, 'raised_by') ?? ''));
        $queryRaisedByLabel = ($raisedByRaw !== '' && ! ctype_digit($raisedByRaw)) ? $roleOf($raisedByRaw) : $roleLabel;
        $returnLogInternalQ = $stringList(data_get($row, 'return_log_internal_queries', []));
        if ($returnLogInternalQ === []) {
            $returnLogInternalQ = $stringList(data_get($row, 'return_queries'));
        }
        $queries = $stringList(data_get($row, 'queries'));
        $applicantFacingQueries = $returnLogInternalQ !== [] ? $returnLogInternalQ : $queries;
        $forwardedRoleName = trim((string) (data_get($row, 'role_name') ?: data_get($row, 'name') ?: ''));
        $remarks = data_get($row, 'remarks');
        $returnRemarksDisplay = data_get($row, 'return_remarks') ?: data_get($row, 'return_log_internal_remarks');
        $remarksInColoredBlock = $hasPendingQuery || $isExternalApplicantReturn;

        $tone = 'info';
        $title = 'Processed by '.$roleLabel;
        $icon = 'check';
        if ($isApplicantResubmission) {
            $tone = 'danger';
            $title = 'Resubmitted by Applicant';
            $icon = 'bang';
        } elseif ($applStatus === 'A') {
            $tone = 'ok';
            $title = 'Approved by '.$roleLabel;
            $icon = 'check';
        } elseif ($applStatus === 'RJ') {
            $tone = 'danger';
            $title = 'Rejected by '.$roleLabel;
            $icon = 'bang';
        } elseif (in_array($applStatus, ['RE', 'PRE', 'QU'], true)) {
            $tone = 'warn';
            $title = 'Returned by '.$roleLabel;
            $icon = 'bang';
        }

        $lines = [];
        if (! $isApplicantResubmission) {
            if ($applStatus === 'RJ') {
                $lines[] = ['text' => 'Reason: '.(data_get($row, 'reject_reason') ?: '—'), 'kind' => 'plain'];
            } elseif ($applStatus === 'PRE' && $forwardedRoleName !== '') {
                $lines[] = ['text' => 'Returned to '.$forwardedRoleName, 'kind' => 'plain'];
            } elseif ($forwardedRoleName !== '') {
                $lines[] = ['text' => 'Forwarded to '.$forwardedRoleName, 'kind' => 'plain'];
            } elseif ($isExternalApplicantReturn) {
                $lines[] = ['text' => 'Application returned to Applicant', 'kind' => 'plain'];
            }
            if (! $remarksInColoredBlock && $remarks) {
                $lines[] = ['text' => 'Remarks: '.$remarks, 'kind' => 'plain'];
            }
        }
        if ($isExternalApplicantReturn) {
            if ($remarks !== null && $remarks !== '') {
                $lines[] = ['text' => 'Staff remark: '.$remarks, 'kind' => 'plain'];
            }
            if ($queries !== [] && $queries != $applicantFacingQueries) {
                $lines[] = ['text' => 'Internal note: ('.implode(', ', $queries).')', 'kind' => 'internal'];
            }
            if ($applicantFacingQueries !== []) {
                $lines[] = ['text' => 'Query to applicant: ('.implode(', ', $applicantFacingQueries).')', 'kind' => 'query'];
            }
            $lines[] = ['text' => 'Return remark: '.(($returnRemarksDisplay !== null && $returnRemarksDisplay !== '') ? $returnRemarksDisplay : '—'), 'kind' => 'return'];
        }
        if ($hasPendingQuery && ! $isExternalApplicantReturn && ! $isApplicantResubmission) {
            $queryText = 'Query raised by '.$queryRaisedByLabel;
            if ($queries !== []) {
                $queryText .= ' ('.implode(', ', $queries).')';
            }
            $lines[] = ['text' => $queryText, 'kind' => 'internal'];
            if ($remarks) {
                $lines[] = ['text' => 'Remark: '.$remarks, 'kind' => 'plain'];
            }
        }

        [$flowDate, $flowTime] = $whenParts(data_get($row, 'created_at'));
        $flowEvents[] = [
            'date' => $flowDate,
            'time' => $flowTime,
            'tone' => $tone,
            'title' => $title,
            'icon' => $icon,
            'lines' => $lines,
        ];
    }
    [$receivedDate, $receivedTime] = $whenParts($user_entry->created_at ?? $applicant->created_at ?? null);
    $flowEvents[] = [
        'date' => $receivedDate,
        'time' => $receivedTime,
        'tone' => 'muted',
        'title' => 'Received from Applicant',
        'icon' => 'user',
        'lines' => [[
            'text' => 'Form: '.($user_entry->form_name ?? $formName).', License: '.($user_entry->license_name ?? ($applicant->license_name ?: '—')),
            'kind' => 'plain',
        ]],
    ];
    $expTints = ['green', 'orange', 'blue', 'purple'];
    $expRows = collect($workExperience ?? []);
    $printFormTitle = match ($formName) {
        'P' => 'Application for Power Generating Station Operation & Maintenance Competency Certificate',
        'S' => 'Application for Supervisor Competency Certificate',
        'W' => 'Application for Wireman Competency Certificate',
        'WH' => 'Application for Wireman Helper Competency Certificate',
        default => 'Competency Certificate Application',
    };
    $eduLevelLabels = [
        'BEM' => 'B.E (Mechanical)',
        'BEE' => 'B.E (Electrical)',
        'DiplomaM' => 'Diploma (Mechanical)',
        'DiplomaE' => 'Diploma (Electrical)',
    ];
@endphp
<style>
    .cav-page { color: #0f172a; }
    #content.cav-page { background: #f3f6fb; }
    .cav-page .layout-px-spacing { padding-bottom: 1.5rem; }
    .cav-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin: .85rem 0 1rem; flex-wrap: wrap; }
    .cav-head h1 { margin: 0; font-size: 1.45rem; font-weight: 800; letter-spacing: -.02em; color: #0f172a; }
    .cav-head-title { display: flex; align-items: center; gap: .55rem; flex-wrap: wrap; }
    .cav-head p { margin: .2rem 0 0; color: #64748b; font-size: .86rem; }
    .cav-head-actions { display: flex; gap: .55rem; align-items: center; flex-wrap: wrap; }
    .cav-btn { border-radius: 10px; font-weight: 700; font-size: .84rem; padding: .48rem .9rem; border: 1px solid transparent; line-height: 1.2; }
    .cav-btn-ghost { background: #fff; border-color: #d7e0ea; color: #334155; }
    .cav-btn-ghost:hover { color: #0f172a; background: #f8fafc; }
    .cav-btn-primary { background: #2563eb; border-color: #2563eb; color: #fff; }
    .cav-btn-primary:hover { background: #1d4ed8; color: #fff; }
    .cav-pill { display: inline-flex; align-items: center; gap: .3rem; border-radius: 999px; padding: .16rem .55rem; font-size: .75rem; font-weight: 800; }
    .cav-pill-ok { background: #dcfce7; color: #15803d; }
    .cav-pill-danger { background: #ffe4e6; color: #be123c; }
    .cav-layout { display: grid; grid-template-columns: minmax(280px, .9fr) minmax(0, 1.35fr); gap: 14px; align-items: start; }
    .cav-page .middle-content,
    .cav-layout,
    .cav-stack,
    .cav-card,
    .cav-flow { min-width: 0; max-width: 100%; }
    .cav-page .work-exp-admin-readonly,
    .cav-page .work-exp-view-wrap,
    .cav-page .wx-order-card,
    .cav-page .wx-summary-table-wrap { max-width: 100%; min-width: 0; }
    .cav-page .wx-summary-table-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    @media (max-width: 1199.98px) { .cav-layout { grid-template-columns: minmax(0, 1fr); } }
    .cav-stack { display: flex; flex-direction: column; gap: 14px; }
    .cav-card { background: #fff; border: 1px solid #e6edf5; border-radius: 16px; box-shadow: 0 8px 24px rgba(15, 23, 42, .045); container-type: inline-size; container-name: cavcard; }
    .cav-identity { display: flex; gap: 14px; align-items: flex-start; padding: 16px 16px 12px; background: linear-gradient(180deg, #eef5ff 0%, #f8fbff 70%, #fff 100%); border-radius: 16px 16px 0 0; }
    .cav-avatar-wrap { position: relative; flex: 0 0 auto; }
    .cav-avatar, .cav-avatar-empty { width: 78px; height: 78px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 14px rgba(37, 99, 235, .18); background: #e8eefc; }
    .cav-avatar-empty { display: inline-flex; align-items: center; justify-content: center; color: #2563eb; font-weight: 800; font-size: 1.4rem; }
    .cav-avatar-check { position: absolute; right: -2px; bottom: 2px; width: 22px; height: 22px; border-radius: 50%; background: #22c55e; color: #fff; border: 2px solid #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; }
    .cav-identity-main { min-width: 0; flex: 1; }
    .cav-identity-main h2 { margin: 0; font-size: 1.15rem; font-weight: 800; }
    .cav-form-line { margin: .15rem 0 .35rem; color: #2563eb; font-weight: 700; font-size: .84rem; }
    .cav-meta { margin: .1rem 0; color: #64748b; font-size: .8rem; display: flex; gap: .4rem; align-items: flex-start; }
    .cav-meta i { color: #94a3b8; margin-top: .15rem; }
    .cav-meta a { color: #475569; }
    .cav-status-box { margin-left: auto; min-width: 148px; border: 1px solid #bbf7d0; background: #f0fdf4; border-radius: 12px; padding: .65rem .75rem; }
    .cav-status-box.tone-danger { border-color: #fecdd3; background: #fff1f2; }
    .cav-status-box strong { display: block; color: #166534; font-size: .84rem; }
    .cav-status-box.tone-danger strong { color: #be123c; }
    .cav-status-box span { display: block; color: #64748b; font-size: .72rem; margin-top: .15rem; }
    .cav-status-box i { color: #22c55e; margin-right: .2rem; }
    .cav-status-box.tone-danger i { color: #e11d48; }
    .cav-stats { display: grid; grid-template-columns: repeat(4, 1fr); border-top: 1px solid #eef2f7; }
    .cav-stat { text-align: center; padding: .75rem .35rem .85rem; border-right: 1px solid #eef2f7; }
    .cav-stat:last-child { border-right: 0; }
    .cav-stat-ico { width: 28px; height: 28px; margin: 0 auto .25rem; border-radius: 8px; background: #f1f5f9; color: #64748b; display: flex; align-items: center; justify-content: center; }
    .cav-stat b { display: block; font-size: .84rem; color: #0f172a; }
    .cav-stat b.ok { color: #16a34a; }
    .cav-stat small { display: block; margin-top: .1rem; color: #94a3b8; font-size: .62rem; font-weight: 800; letter-spacing: .04em; }
    .cav-card-h { display: flex; align-items: center; gap: .5rem; margin: 0; font-size: .95rem; font-weight: 800; }
    .cav-card-pad { padding: 14px 16px 16px; }
    .cav-card-tools { margin-left: auto; display: flex; gap: .35rem; }
    .cav-icon-btn { border: 0; background: transparent; color: #94a3b8; padding: .15rem .3rem; }
    .cav-icon-btn:hover { color: #2563eb; }
    .cav-info-row { display: grid; grid-template-columns: 148px 1fr; gap: .75rem; padding: .72rem 16px; border-top: 1px solid #f1f5f9; font-size: .86rem; align-items: center; }
    .cav-info-row span { color: #64748b; }
    .cav-info-row b { color: #0f172a; font-weight: 700; word-break: break-word; }
    .cav-sign-box { display: inline-flex; align-items: center; justify-content: center; min-width: 150px; min-height: 64px; border: 1px solid #e6edf5; border-radius: 10px; background: #f8fafc; padding: .35rem .6rem; }
    .cav-sign { max-width: 140px; max-height: 52px; object-fit: contain; }
    .cav-about { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr); gap: 14px; }
    .cav-facts { display: flex; flex-direction: column; }
    .cav-fact { display: grid; grid-template-columns: 110px 1fr; gap: .5rem; padding: .48rem 0; border-bottom: 1px solid #f1f5f9; font-size: .84rem; }
    .cav-fact:last-child { border-bottom: 0; }
    .cav-fact span { color: #64748b; }
    .cav-fact b { font-weight: 700; color: #0f172a; word-break: break-word; }
    .cav-proof { border: 1px solid #e6edf5; border-radius: 12px; padding: .65rem .75rem; }
    .cav-proof h4 { margin: 0 0 .45rem; font-size: .78rem; color: #64748b; font-weight: 800; }
    .cav-proof-row { display: flex; align-items: center; gap: .45rem; padding: .4rem 0; }
    .cav-proof-row + .cav-proof-row { border-top: 1px solid #f1f5f9; }
    .cav-proof-ico { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; background: #fff7ed; color: #ea580c; }
    .cav-proof-ico.pan { background: #eff6ff; color: #2563eb; }
    .cav-proof-row b { font-size: .84rem; }
    .cav-pdf { margin-left: auto; background: #fee2e2; color: #dc2626; border-radius: 6px; font-size: .62rem; font-weight: 800; padding: .12rem .35rem; }
    .cav-view { background: #2563eb; color: #fff !important; border-radius: 8px; font-size: .72rem; font-weight: 800; padding: .28rem .6rem; }
    .cav-view:hover { background: #1d4ed8; color: #fff !important; }
    .cav-pair { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; }
    .cav-tint { border-radius: 16px; padding: 14px 16px 16px; border: 1px solid transparent; min-height: 148px; }
    .cav-tint-blue { background: #eef6ff; border-color: #dbeafe; }
    .cav-tint-purple { background: #f5f3ff; border-color: #ede9fe; }
    .cav-tint-green { background: #ecfdf5; border-color: #d1fae5; }
    .cav-tint-orange { background: #fff7ed; border-color: #ffedd5; }
    .cav-ico { width: 28px; height: 28px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,.75); }
    .cav-tint-blue .cav-ico { color: #2563eb; }
    .cav-tint-purple .cav-ico { color: #7c3aed; }
    .cav-tint-green .cav-ico { color: #059669; }
    .cav-tint-orange .cav-ico { color: #ea580c; }
    .cav-item { padding: .55rem 0; }
    .cav-item + .cav-item { border-top: 1px solid rgba(15,23,42,.06); }
    .cav-item-title { margin: 0; font-weight: 800; font-size: .9rem; }
    .cav-item-meta { margin: .12rem 0 .2rem; color: #64748b; font-size: .78rem; }
    .cav-doc { color: #dc2626; font-weight: 700; font-size: .78rem; }
    .cav-doc:hover { color: #b91c1c; }
    .cav-note { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: .6rem .8rem; font-size: .82rem; color: #1e3a8a; }
    .cav-badge { display: inline-flex; border-radius: 999px; padding: .05rem .4rem; font-size: .66rem; font-weight: 800; }
    .cav-badge-alter { background: #ffedd5; color: #c2410c; }
    .cav-badge-yes { background: #dcfce7; color: #15803d; }
    .cav-badge-no { background: #f1f5f9; color: #64748b; }
    .cav-qa { border: 1px solid #e6edf5; border-radius: 12px; padding: .7rem .85rem; margin-top: .65rem; background: #fff; }
    .cav-qa h6 { font-size: .82rem; font-weight: 700; margin: 0 0 .35rem; }
    .cav-pay { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .75rem; margin-top: .85rem; }
    .cav-pay small { display: block; color: #94a3b8; font-size: .72rem; font-weight: 700; }
    .cav-pay b { display: block; margin-top: .15rem; font-size: .9rem; word-break: break-all; }
    .cav-pay-ok { display: inline-flex; align-items: center; gap: .3rem; color: #16a34a; }
    .cav-flow-head { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: .85rem; }
    .cav-expand { border: 0; background: transparent; color: #2563eb; font-weight: 800; font-size: .8rem; }
    .cav-flow-track { display: flex; gap: 10px; overflow-x: auto; padding-bottom: .35rem; }
    .cav-flow-step { flex: 1 0 168px; min-width: 168px; max-width: 230px; display: flex; flex-direction: column; }
    .cav-flow-when { text-align: center; color: #64748b; font-size: .72rem; line-height: 1.25; min-height: 2.1rem; font-weight: 700; }
    .cav-flow-rail { position: relative; height: 28px; display: flex; align-items: center; justify-content: center; }
    .cav-flow-rail:before { content: ""; position: absolute; left: -8px; right: -8px; top: 50%; height: 3px; background: #dbe4ee; transform: translateY(-50%); }
    .cav-flow-step:first-child .cav-flow-rail:before { left: 50%; }
    .cav-flow-step:last-child .cav-flow-rail:before { right: 50%; }
    .cav-flow-dot { position: relative; z-index: 1; width: 22px; height: 22px; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 11px; box-shadow: 0 0 0 4px #fff; }
    .tone-ok .cav-flow-dot { background: #22c55e; }
    .tone-info .cav-flow-dot { background: #3b82f6; }
    .tone-danger .cav-flow-dot { background: #ef4444; }
    .tone-warn .cav-flow-dot { background: #f59e0b; }
    .tone-muted .cav-flow-dot { background: #94a3b8; }
    .cav-flow-card { margin-top: .35rem; background: #fff; border: 1px solid #e6edf5; border-radius: 12px; padding: .6rem .65rem .7rem; min-height: 92px; }
    .tone-danger .cav-flow-card { background: #fff1f2; border-color: #fecdd3; }
    .tone-warn .cav-flow-card { background: #fff7ed; border-color: #fed7aa; }
    .cav-flow-title { margin: 0 0 .25rem; font-size: .8rem; font-weight: 800; line-height: 1.3; color: #1e293b; }
    .tone-ok .cav-flow-title { color: #16a34a; }
    .tone-danger .cav-flow-title { color: #e11d48; }
    .tone-warn .cav-flow-title { color: #ea580c; }
    .tone-muted .cav-flow-title { color: #64748b; }
    .cav-flow-card p { margin: 0 0 .2rem; font-size: .72rem; color: #475569; line-height: 1.35; }
    .cav-line-query, .cav-line-return { color: #a21caf; }
    .cav-line-internal { color: #b91c1c; }
    .cav-flow:not(.is-expanded) .cav-flow-card { max-height: 108px; overflow: hidden; }
    .cav-empty { margin: .35rem 0 0; color: #64748b; font-size: .82rem; }
    @media (max-width: 991.98px) {
        .cav-pay { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 767.98px) {
        .cav-head { flex-direction: column; align-items: stretch; }
        .cav-head-actions { display: grid; grid-template-columns: 1fr 1fr; width: 100%; }
        .cav-head-actions .cav-btn { text-align: center; }
        .cav-identity { flex-wrap: wrap; }
        .cav-identity-main { flex: 1 1 160px; }
        .cav-status-box { flex: 1 1 100%; margin: .35rem 0 0; min-width: 0; }
        .cav-stats { grid-template-columns: 1fr 1fr; }
        .cav-stat:nth-child(2n) { border-right: 0; }
        .cav-stat:nth-child(n+3) { border-top: 1px solid #eef2f7; }
        .cav-about { grid-template-columns: 1fr; }
        .cav-info-row, .cav-fact { grid-template-columns: 1fr; gap: .15rem; align-items: start; padding-top: .55rem; padding-bottom: .55rem; }
        .cav-proof { margin-top: .25rem; }
        .cav-proof-row { flex-wrap: wrap; }
        .cav-flow-head { flex-wrap: wrap; align-items: flex-start; }
        .cav-flow-step { flex-basis: 148px; min-width: 148px; max-width: 200px; }
    }
    @media (max-width: 575.98px) {
        .cav-head h1 { font-size: 1.22rem; }
        .cav-head-actions { grid-template-columns: 1fr; }
        .cav-pay { grid-template-columns: 1fr; }
        .cav-pair { grid-template-columns: minmax(0, 1fr); }
        .cav-card-h { flex-wrap: wrap; }
        .cav-sign-box { min-width: 0; max-width: 100%; }
    }
    @container cavcard (max-width: 640px) {
        .cav-identity { flex-wrap: wrap; }
        .cav-status-box { flex: 1 1 100%; margin-left: 0; min-width: 0; }
        .cav-stats { grid-template-columns: 1fr 1fr; }
        .cav-stat:nth-child(2n) { border-right: 0; }
        .cav-stat:nth-child(n+3) { border-top: 1px solid #eef2f7; }
        .cav-about { grid-template-columns: minmax(0, 1fr); }
        .cav-pay { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @container cavcard (max-width: 420px) {
        .cav-pay { grid-template-columns: minmax(0, 1fr); }
        .cav-info-row, .cav-fact { grid-template-columns: minmax(0, 1fr); gap: .15rem; align-items: start; }
    }
    #cavPrintForm { color: #111; font-family: Helvetica, Arial, sans-serif; font-size: 11pt; }
    #cavPrintForm * { box-sizing: border-box; }
    #cavPrintForm .bi-en, #cavPrintForm .lbl-en, #cavPrintForm .th-en { display: block; font-weight: 700; line-height: 1.2; margin: 0; }
    #cavPrintForm .bi-ta, #cavPrintForm .lbl-ta, #cavPrintForm .th-ta { display: block; font-weight: 400; line-height: 1.2; margin: .12em 0 0; font-size: 9.5pt; }
    #cavPrintForm .card { border: 1px solid #000; padding: 14px 16px; width: 100%; }
    #cavPrintForm .header { color: #003366; text-align: center; margin-bottom: 12px; border-bottom: 1px solid #c5d4e6; padding-bottom: 8px; }
    #cavPrintForm .header-main .bi-en { font-size: 14pt; }
    #cavPrintForm .header-title .bi-en { font-size: 12.5pt; margin-top: 2px; }
    #cavPrintForm .header-sub { font-size: 10pt; margin-top: 4px; }
    #cavPrintForm .header-sub .appl-type { color: green; font-weight: 700; }
    #cavPrintForm .info-table { width: 100%; border-collapse: collapse; table-layout: fixed; font-size: 11pt; }
    #cavPrintForm .info-table td { padding: 4px 0; vertical-align: top; border: 0; border-bottom: 1px solid #e8edf4; background: transparent; }
    #cavPrintForm .info-table td.lbl { width: 42%; }
    #cavPrintForm .info-table td.colon { width: 14px; text-align: center; font-weight: 700; }
    #cavPrintForm .info-table td.val { font-weight: 400; }
    #cavPrintForm .photo-frame { width: 38mm; height: 38mm; margin: 0 auto; overflow: hidden; text-align: center; }
    #cavPrintForm .photo-frame img { width: 38mm; height: 38mm; object-fit: cover; }
    #cavPrintForm .sign-frame { width: 38mm; min-height: 14mm; margin: 8px auto 0; text-align: center; }
    #cavPrintForm .sign-frame img { width: 34mm; height: 12mm; object-fit: contain; }
    #cavPrintForm .sign-missing { color: #666; font-size: 8pt; text-align: center; }
    #cavPrintForm .summary-card { border: 1px solid #cfd8e3; margin-top: 10px; overflow: hidden; }
    #cavPrintForm .summary-heading { background: #edf3fa; color: #0b3b6e; font-weight: 700; text-align: center; padding: 6px 8px; border-bottom: 1px solid #d8e2ef; }
    #cavPrintForm .summary-heading .bi-ta { font-size: 9pt; }
    #cavPrintForm .section-title { margin: 10px 0 8px; border: 1px solid #d8e2ef; }
    #cavPrintForm .lbl-with-sno { display: flex; align-items: flex-start; gap: 6px; }
    #cavPrintForm .pf-sno { display: inline-flex; align-items: center; justify-content: center; width: 18px; height: 18px; min-width: 18px; border-radius: 50%; background: #035ab3; color: #fff; font-size: 9pt; font-weight: 700; line-height: 1; margin-top: 1px; }
    #cavPrintForm .summary-table { width: 100%; border-collapse: collapse; font-size: 9.5pt; margin: 0; table-layout: fixed; }
    #cavPrintForm .summary-table th { background: #edf3fa; color: #123c66; text-align: center; padding: 6px 5px; border: 0; border-bottom: 1px solid #d7e1ee; font-size: 8.5pt; vertical-align: bottom; }
    #cavPrintForm .summary-table .th-ta { font-size: 8pt; }
    #cavPrintForm .summary-table td, #cavPrintForm .summary-table td.txt { text-align: center; padding: 6px 5px; border: 0; border-bottom: 1px solid #e7edf5; background: #fff; vertical-align: middle; word-wrap: break-word; }
    #cavPrintForm .summary-table tbody tr:nth-child(even) td { background: #fafcff; }
    #cavPrintForm .employer-line { width: 100%; border-collapse: collapse; border-top: 1px solid #d8e2ef; }
    #cavPrintForm .employer-line td { border: 0; background: #fff; padding: 7px 8px; vertical-align: middle; }
    #cavPrintForm .employer-line .lbl { width: 34%; text-align: left; }
    #cavPrintForm .employer-line .colon { width: 16px; text-align: center; font-weight: 700; }
    #cavPrintForm .employer-line .val { text-align: left; font-weight: 600; }
    #cavPrintForm .footer { margin-top: 14px; text-align: center; font-size: 11pt; }
    #cavPrintForm .footer .bi-en { font-weight: 700; }
    .cav-form-sheet { display: none; }
    @page { size: A4 portrait; margin: 10mm; }
    @media print {
        body * { visibility: hidden; }
        #cavPrintForm, #cavPrintForm * { visibility: visible; }
        #cavPrintForm {
            display: block !important; position: absolute; left: 0; top: 0; width: 100%;
            background: #fff; color: #111; font-family: Helvetica, Arial, sans-serif;
            -webkit-print-color-adjust: exact; print-color-adjust: exact;
        }
    }
    @include('user_login.partials.form-s-work-exp-styles', ['editFormName' => $formName])
</style>

<div id="content" class="main-content cav-page">
    <div class="layout-px-spacing">
        <div class="middle-content container-xxl p-0">
            <div class="secondary-nav">
                <div class="breadcrumbs-container">
                    <header class="header navbar navbar-expand-sm">
                        <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="feather feather-menu">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </a>
                        <div class="d-flex breadcrumb-content">
                            <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.completed_applications') }}">Completed Applications</a></li>
                                    <li class="breadcrumb-item active">View Application</li>
                                </ol>
                            </nav>
                        </div>
                    </header>
                </div>
            </div>

            <div class="cav-head">
                <div>
                    <div class="cav-head-title">
                        <h1>View Application</h1>
                        <span class="cav-pill {{ $statusTone === 'danger' ? 'cav-pill-danger' : 'cav-pill-ok' }}"><i class="fa fa-check"></i> {{ $statusLabel }}</span>
                    </div>
                    <p>Application details, documents and workflow status</p>
                </div>
                <div class="cav-head-actions">
                    <a class="cav-btn cav-btn-ghost" href="{{ route('admin.completed_applications') }}"><i class="fa fa-arrow-left"></i> Back</a>
                    <button type="button" class="cav-btn cav-btn-primary" id="cavPrintBtn" data-print-target="cavPrintForm"><i class="fa fa-print"></i> Print Application</button>
                </div>
            </div>

            <div class="cav-layout">
                <div class="cav-stack">
                    <div class="cav-card">
                        <div class="cav-identity">
                            <div class="cav-avatar-wrap">
                                @if($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="Applicant photo" class="cav-avatar" onerror="this.style.display='none'">
                                @else
                                    <span class="cav-avatar-empty">{{ strtoupper(substr(trim((string) ($applicant->applicant_name ?? 'A')), 0, 1)) }}</span>
                                @endif
                                @if($statusTone === 'ok')
                                    <span class="cav-avatar-check"><i class="fa fa-check"></i></span>
                                @endif
                            </div>
                            <div class="cav-identity-main">
                                <h2>{{ $applicant->applicant_name ?: 'Applicant' }}</h2>
                                <p class="cav-form-line">Form {{ $formName }} · {{ $applTypeLabel }}</p>
                                <p class="cav-meta"><i class="fa fa-map-marker"></i> <span>{{ $applicant->applicants_address ?: '—' }}</span></p>
                                @if(!empty($applicant->applicant_email))
                                    <p class="cav-meta"><i class="fa fa-envelope"></i> <a href="mailto:{{ e($applicant->applicant_email) }}">{{ e($applicant->applicant_email) }}</a></p>
                                @endif
                            </div>
                            <div class="cav-status-box {{ $statusTone === 'danger' ? 'tone-danger' : '' }}">
                                <strong><i class="fa fa-check-circle"></i> {{ $statusLabel }} Application</strong>
                                <span>Submitted on<br>{{ $submittedLabel }}</span>
                            </div>
                        </div>
                        <div class="cav-stats">
                            <div class="cav-stat">
                                <div class="cav-stat-ico"><i class="fa fa-file-text-o"></i></div>
                                <b>{{ $formName }}</b>
                                <small>FORM</small>
                            </div>
                            <div class="cav-stat">
                                <div class="cav-stat-ico"><i class="fa fa-refresh"></i></div>
                                <b>{{ $applTypeLabel }}</b>
                                <small>TYPE</small>
                            </div>
                            <div class="cav-stat">
                                <div class="cav-stat-ico"><i class="fa fa-check"></i></div>
                                <b class="{{ $statusTone === 'ok' ? 'ok' : '' }}">{{ $statusLabel }}</b>
                                <small>STATUS</small>
                            </div>
                            <div class="cav-stat">
                                <div class="cav-stat-ico"><i class="fa fa-calendar"></i></div>
                                <b>{{ $submittedLabel }}</b>
                                <small>DATE SUBMITTED</small>
                            </div>
                        </div>
                    </div>

                    <div class="cav-card">
                        <div class="cav-card-pad" style="padding-bottom: 8px;">
                            <h3 class="cav-card-h">
                                <span class="cav-ico" style="color:#2563eb;background:#eff6ff;"><i class="fa fa-file-text-o"></i></span>
                                Application Information
                                <span class="cav-card-tools">
                                    <button type="button" class="cav-icon-btn" data-copy="{{ $applicant->application_id }}" title="Copy application ID"><i class="fa fa-clone"></i></button>
                                </span>
                            </h3>
                        </div>
                        <div class="cav-info-row"><span>Application ID</span><b>{{ $applicant->application_id }}</b></div>
                        <div class="cav-info-row"><span>Father's name</span><b>{{ $applicant->fathers_name ?: '—' }}</b></div>
                        <div class="cav-info-row"><span>Date of birth</span><b>{{ format_date($applicant->d_o_b) }}@if(!empty($applicant->age)) ({{ $applicant->age }} yrs)@endif</b></div>
                        <div class="cav-info-row"><span>Aadhaar</span><b>{{ $maskedAadhaar }}</b></div>
                        <div class="cav-info-row"><span>PAN</span><b>{{ $maskedPan }}</b></div>
                        @if($applType === 'A' && !empty($applicant->old_application))
                            <div class="cav-info-row"><span>Parent application</span><b>{{ $applicant->old_application }}</b></div>
                        @endif
                        <div class="cav-info-row">
                            <span>Signature</span>
                            <b>
                                @if($signUrl)
                                    <span class="cav-sign-box"><img src="{{ $signUrl }}" alt="Signature" class="cav-sign"></span>
                                @else
                                    —
                                @endif
                            </b>
                        </div>
                    </div>
                </div>

                <div class="cav-stack">
                    @if($isAlterationApp && ($nameAltered || $addressAltered || $hasAlteredWork || $hasAlterationProofs))
                        <div class="cav-note">
                            Altered in this request:
                            {{ collect([$nameAltered ? 'Name' : null, $addressAltered ? 'Address' : null, $hasAlteredWork ? 'Work experience' : null, $hasAlterationProofs ? 'Supporting proofs' : null])->filter()->implode(', ') }}
                        </div>
                    @endif

                    <div class="cav-card cav-card-pad">
                        <h3 class="cav-card-h"><span class="cav-ico" style="color:#2563eb;background:#eff6ff;"><i class="fa fa-user"></i></span> About applicant</h3>
                        <div class="cav-about mt-2">
                            <div class="cav-facts">
                                <div class="cav-fact"><span>Full name</span><b>{{ $applicant->applicant_name }} @if($nameAltered)<span class="cav-badge cav-badge-alter">ALTER</span>@endif</b></div>
                                <div class="cav-fact"><span>Email</span><b>{{ $applicant->applicant_email ?: '—' }}</b></div>
                                <div class="cav-fact"><span>Date of birth</span><b>{{ format_date($applicant->d_o_b) }}</b></div>
                                <div class="cav-fact"><span>Address</span><b>{{ $applicant->applicants_address ?: '—' }} @if($addressAltered)<span class="cav-badge cav-badge-alter">ALTER</span>@endif</b></div>
                                @if($nameAltered && $parentForAlter)
                                    <div class="cav-fact"><span>Previous name</span><b>{{ $parentForAlter->applicant_name }}</b></div>
                                @endif
                                @if($addressAltered && $parentAddressValue !== '')
                                    <div class="cav-fact"><span>Previous address</span><b>{{ $parentAddressValue }}</b></div>
                                @endif
                                @if($nameProofUrl)
                                    <div class="cav-fact"><span>Name proof</span><b><a class="cav-doc" href="{{ $nameProofUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View document</a></b></div>
                                @endif
                                @if($addressProofUrl)
                                    <div class="cav-fact"><span>Address proof</span><b><a class="cav-doc" href="{{ $addressProofUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View document</a></b></div>
                                @endif
                            </div>
                            <div class="cav-proof">
                                <h4>ID Proof</h4>
                                <div class="cav-proof-row">
                                    <span class="cav-proof-ico"><i class="fa fa-id-card"></i></span>
                                    <b>Aadhaar</b>
                                    @if($aadhaarDocUrl)
                                        <span class="cav-pdf">PDF</span>
                                        <a class="cav-view" href="{{ $aadhaarDocUrl }}" target="_blank" rel="noopener">View</a>
                                    @endif
                                </div>
                                <div class="cav-proof-row">
                                    <span class="cav-proof-ico pan"><i class="fa fa-credit-card"></i></span>
                                    <b>PAN</b>
                                    @if($panDocUrl)
                                        <span class="cav-pdf">PDF</span>
                                        <a class="cav-view" href="{{ $panDocUrl }}" target="_blank" rel="noopener">View</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($applType === 'D' && !empty($cc_digitization))
                        <div class="cav-card cav-card-pad">
                            <h3 class="cav-card-h"><span class="cav-ico" style="color:#2563eb;background:#eff6ff;"><i class="fa fa-id-card"></i></span> Digitisation certificate</h3>
                            <div class="cav-facts mt-2">
                                <div class="cav-fact"><span>Certificate number</span><b>{{ $cc_digitization->ccnumber }}</b></div>
                                <div class="cav-fact"><span>First issue</span><b>{{ $cc_digitization->fissue }}</b></div>
                                <div class="cav-fact"><span>Validity</span><b>{{ !empty($cc_digitization->from_date) ? \Carbon\Carbon::parse($cc_digitization->from_date)->format('d-m-Y') : '—' }} to {{ !empty($cc_digitization->to_date) ? \Carbon\Carbon::parse($cc_digitization->to_date)->format('d-m-Y') : '—' }}</b></div>
                            </div>
                            @if(!empty($cc_digitization->cc_doc))
                                <a class="cav-doc" href="{{ digitization_document_url($cc_digitization->cc_doc, 'scc') }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View certificate</a>
                            @endif
                            @if(($cc_digitization->qc_det ?? '') == '1')
                                <div class="cav-facts mt-2">
                                    <div class="cav-fact"><span>Grade</span><b>{{ $cc_digitization->cl_type }}</b></div>
                                    <div class="cav-fact"><span>Licence number</span><b>{{ $cc_digitization->licence_no }}</b></div>
                                    <div class="cav-fact"><span>Contractor</span><b>{{ $cc_digitization->contractor_name }}</b></div>
                                </div>
                                @if(!empty($cc_digitization->qc_doc))
                                    <a class="cav-doc" href="{{ digitization_document_url($cc_digitization->qc_doc, 'qc') }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View QC document</a>
                                @endif
                            @endif
                        </div>
                    @endif

                    @if($applType === 'A' && ($alterationProofs ?? collect())->isNotEmpty())
                        <div class="cav-card cav-card-pad">
                            <h3 class="cav-card-h"><span class="cav-ico" style="color:#2563eb;background:#eff6ff;"><i class="fa fa-paperclip"></i></span> Alteration proofs</h3>
                            @foreach($alterationProofs as $proof)
                                @php $proofViewUrl = !empty($proof->url) ? $proof->url : (!empty($proof->proof_doc) ? competency_document_path_url($proof->proof_doc) : null); @endphp
                                <div class="cav-item">
                                    <p class="cav-item-title">{{ $proof->label ?? 'Supporting proof' }}</p>
                                    @if($proofViewUrl)
                                        <a class="cav-doc" href="{{ $proofViewUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View document</a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="cav-pair">
                        <div class="cav-tint cav-tint-blue">
                            <h3 class="cav-card-h"><span class="cav-ico"><i class="fa fa-graduation-cap"></i></span> Education</h3>
                            @forelse (($educationalQualifications ?? collect()) as $education)
                                @php
                                    $rawMonth = trim((string) ($education->month_passing ?? $education->month_of_passing ?? ''));
                                    $monthKey = $rawMonth !== '' && is_numeric($rawMonth) ? str_pad($rawMonth, 2, '0', STR_PAD_LEFT) : $rawMonth;
                                    $monthDisplay = $monthLabels[$monthKey] ?? $rawMonth;
                                    $certificateNo = data_get($education, 'certificate_no');
                                    $percentage = data_get($education, 'percentage');
                                    $eduDocUrl = $education->document_url ?? competency_document_url(
                                        $education->upload_document ?? null,
                                        'education',
                                        (int) ($education->id ?? 0),
                                        'certificate',
                                        array_filter([(int) ($formSWorkflowAppPk ?? 0), (int) ($formSMasterWorkflowAppPk ?? 0)])
                                    );
                                @endphp
                                <div class="cav-item">
                                    <p class="cav-item-title">{{ $education->educational_level }}{{ $education->institute_name ? ', '.$education->institute_name : '' }}</p>
                                    <p class="cav-item-meta">
                                        {{ trim($monthDisplay.' '.($education->year_of_passing ?? '')) ?: '—' }}
                                        @if($certificateNo) · Cert {{ $certificateNo }} @elseif($percentage !== null && $percentage !== '') · {{ $percentage }}% @endif
                                    </p>
                                    @if($eduDocUrl)
                                        <a class="cav-doc" href="{{ $eduDocUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View document</a>
                                    @endif
                                </div>
                            @empty
                                <p class="cav-empty">No educational details available.</p>
                            @endforelse
                        </div>

                        @if($isFormP)
                            <div class="cav-tint cav-tint-purple">
                                <h3 class="cav-card-h"><span class="cav-ico"><i class="fa fa-university"></i></span> Training institute</h3>
                                @forelse (($institute_details ?? collect()) as $institutes)
                                    <div class="cav-item">
                                        <p class="cav-item-title">{{ $institutes->institute_name_address }}</p>
                                        <p class="cav-item-meta">{{ format_date($institutes->from_date) }} to {{ format_date($institutes->to_date) }} · {{ format_institute_duration($institutes->duration ?? '', $institutes->from_date ?? null, $institutes->to_date ?? null) }}</p>
                                        @if($institutes->upload_doc)
                                            <a class="cav-doc" href="{{ competency_document_url($institutes->upload_doc, 'experience', (int) ($institutes->id ?? 0), 'supporting') }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View document</a>
                                        @endif
                                    </div>
                                @empty
                                    <p class="cav-empty">No institute details available.</p>
                                @endforelse
                            </div>
                        @endif
                    </div>

                    @if($isFormP)
                        <div class="cav-pair">
                            @forelse ($expRows as $expIndex => $exp)
                                @php $tint = $expTints[$expIndex % count($expTints)]; @endphp
                                <div class="cav-tint cav-tint-{{ $tint }}">
                                    <h3 class="cav-card-h">
                                        <span class="cav-ico"><i class="fa fa-briefcase"></i></span>
                                        {{ $expIndex === 0 ? 'Power station / experience' : (($exp->designation ?? 'Experience').(($exp->company_name ?? $exp->org_name ?? $exp->emp_cate) ? ' at '.($exp->company_name ?? $exp->org_name ?? $exp->emp_cate) : '')) }}
                                    </h3>
                                    @if($expIndex === 0)
                                        <p class="cav-item-title mt-2">{{ $exp->designation ?? '—' }} at {{ $exp->company_name ?? $exp->org_name ?? $exp->emp_cate ?? '—' }}</p>
                                    @endif
                                    <p class="cav-item-meta">{{ format_date($exp->from_date) }} to {{ format_date($exp->to_date) }} · {{ format_total_exp_years($exp->total_exp ?? $exp->experience ?? null, $exp->from_date ?? null, $exp->to_date ?? null) ?? '—' }}</p>
                                    @if(!empty($exp->upload_document) || !empty($exp->support_document))
                                        <a class="cav-doc" href="{{ competency_document_url($exp->upload_document ?? $exp->support_document ?? null, 'experience', (int) ($exp->id ?? $exp->exp_id ?? 0), 'experience_doc') }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View document</a>
                                    @endif
                                    @if($loop->last)
                                        <p class="cav-item-meta mt-2 mb-0">Name of the employer<br><b style="color:#0f172a;">{{ $applicant->employer_detail ?: '—' }}</b></p>
                                    @endif
                                </div>
                            @empty
                                <div class="cav-tint cav-tint-green">
                                    <h3 class="cav-card-h"><span class="cav-ico"><i class="fa fa-briefcase"></i></span> Power station / experience</h3>
                                    <p class="cav-empty">No work experience available.</p>
                                    <p class="cav-item-meta mt-2 mb-0">Name of the employer<br><b style="color:#0f172a;">{{ $applicant->employer_detail ?: '—' }}</b></p>
                                </div>
                            @endforelse
                        </div>
                    @endif

                    @if(in_array($formName, ['S', 'W', 'WH'], true))
                        <div class="cav-card cav-card-pad">
                            <h3 class="cav-card-h"><span class="cav-ico" style="color:#059669;background:#ecfdf5;"><i class="fa fa-briefcase"></i></span> Experience</h3>
                            <div class="mt-2">
                                @if($isFormS)
                                    @include('admin.partials.form-s-work-exp-readonly', ['workExperience' => $workExperience ?? collect()])
                                @else
                                    @include('admin.partials.form-s-work-exp-readonly', ['workExperience' => $workExperience ?? collect(), 'hideVoltageFields' => true])
                                @endif
                            </div>
                            @if($isFormS)
                                @php
                                    $prevValidTo = $applicant->previously_valid_to ?? $applicant->previously_date ?? null;
                                    $hasPreviousEaQual = !empty($applicant->previously_number) || !empty($prevValidTo);
                                    $hasWiremanCompCert = !empty($applicant->wcc_no) || !empty($applicant->wcc_from ?? $applicant->wcc_to ?? null);
                                @endphp
                                <div class="cav-qa">
                                    <h6>Supervisor Competency Certificate from this Board?</h6>
                                    <span class="cav-badge {{ $hasPreviousEaQual ? 'cav-badge-yes' : 'cav-badge-no' }}">{{ $hasPreviousEaQual ? 'Yes' : 'No' }}</span>
                                    @if($hasPreviousEaQual)
                                        <p class="cav-item-meta mt-2 mb-0">{{ $applicant->previously_number ?: '—' }} · {{ !empty($applicant->previously_valid_from) ? format_date($applicant->previously_valid_from) : '—' }} to {{ !empty($prevValidTo) ? format_date($prevValidTo) : '—' }}</p>
                                    @endif
                                </div>
                                <div class="cav-qa">
                                    <h6>Wireman Competency Certificate from this Board?</h6>
                                    <span class="cav-badge {{ $hasWiremanCompCert ? 'cav-badge-yes' : 'cav-badge-no' }}">{{ $hasWiremanCompCert ? 'Yes' : 'No' }}</span>
                                    @if($hasWiremanCompCert)
                                        <p class="cav-item-meta mt-2 mb-0">{{ $applicant->wcc_no ?: '—' }} · {{ !empty($applicant->wcc_from) ? format_date($applicant->wcc_from) : '—' }} to {{ !empty($applicant->wcc_to) ? format_date($applicant->wcc_to) : '—' }}</p>
                                    @endif
                                </div>
                            @endif
                            @if($formName === 'WH' || $formName === 'W')
                                @php
                                    $boardCertNo = $applicant->wcc_no ?? $applicant->certificate_no ?? null;
                                    $boardCertTo = $applicant->wcc_to ?? $applicant->certificate_valid_to ?? $applicant->certificate_date ?? null;
                                    $hasBoardCert = !empty($boardCertNo) && !empty($boardCertTo);
                                @endphp
                                <div class="cav-qa">
                                    <h6>{{ $formName === 'WH' ? 'Wireman Helper qualification certificate' : 'Wireman / Wireman Helper qualification certificate' }}</h6>
                                    <span class="cav-badge {{ $hasBoardCert ? 'cav-badge-yes' : 'cav-badge-no' }}">{{ $hasBoardCert ? 'Yes' : 'No' }}</span>
                                    @if($hasBoardCert)
                                        <p class="cav-item-meta mt-2 mb-0">{{ $boardCertNo }} · expires {{ format_date($boardCertTo) }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($showPayment)
                        <div class="cav-card cav-card-pad">
                            <h3 class="cav-card-h"><span class="cav-ico" style="color:#7c3aed;background:#f5f3ff;"><i class="fa fa-credit-card"></i></span> Payment Details</h3>
                            <div class="cav-pay">
                                <div>
                                    <small>Status</small>
                                    <b class="{{ $paymentIsSuccess ? 'cav-pay-ok' : '' }}">
                                        @if($paymentIsSuccess)<i class="fa fa-check-circle"></i>@endif
                                        {{ $paymentStatusLabel }}
                                    </b>
                                </div>
                                <div><small>Amount</small><b>{{ $amountLabel }}</b></div>
                                <div><small>Mode</small><b>{{ $applicant->payment_mode ?: '—' }}</b></div>
                                <div>
                                    <small>Transaction ID</small>
                                    <b>
                                        {{ $applicant->transaction_id ?: '—' }}
                                        @if(!empty($applicant->transaction_id))
                                            <button type="button" class="cav-icon-btn" data-copy="{{ $applicant->transaction_id }}" title="Copy transaction ID"><i class="fa fa-clone"></i></button>
                                        @endif
                                    </b>
                                </div>
                                <div><small>Payment date</small><b>{{ format_date($applicant->transaction_date) ?: '—' }}</b></div>
                                @if(!empty($applicant->late_fees))
                                    <div><small>Late fee</small><b>₹ {{ $applicant->late_fees }} ({{ $applicant->late_months }} months)</b></div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="cav-card cav-card-pad mt-3 cav-flow is-expanded" id="cavFlow">
                <div class="cav-flow-head">
                    <h3 class="cav-card-h"><span class="cav-ico" style="color:#2563eb;background:#eff6ff;"><i class="fa fa-sitemap"></i></span> Process Flow</h3>
                    <button type="button" class="cav-expand" id="cavExpandAll">Collapse <i class="fa fa-chevron-up"></i></button>
                </div>
                <div class="cav-flow-track">
                    @foreach($flowEvents as $event)
                        <div class="cav-flow-step tone-{{ $event['tone'] }}">
                            <div class="cav-flow-when">{{ $event['date'] }}@if($event['time'])<br>{{ $event['time'] }}@endif</div>
                            <div class="cav-flow-rail">
                                <span class="cav-flow-dot">
                                    @if($event['icon'] === 'bang')
                                        <i class="fa fa-exclamation"></i>
                                    @elseif($event['icon'] === 'user')
                                        <i class="fa fa-user"></i>
                                    @else
                                        <i class="fa fa-check"></i>
                                    @endif
                                </span>
                            </div>
                            <div class="cav-flow-card">
                                <p class="cav-flow-title">{{ $event['title'] }}</p>
                                @foreach($event['lines'] as $line)
                                    <p class="cav-line-{{ $line['kind'] }}">{{ $line['text'] }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div id="cavPrintForm" class="cav-form-sheet">
    <div class="card">
        <div class="header">
            <div class="hdr-stack header-main">
                <div class="bi-en">GOVERNMENT OF TAMIL NADU</div>
                <div class="bi-ta" lang="ta">தமிழ்நாடு அரசு</div>
            </div>
            <div class="hdr-stack header-title">
                <div class="bi-en">TAMIL NADU ELECTRICAL LICENCING BOARD</div>
                <div class="bi-ta" lang="ta">மின்சார உரிமையாளர்கள் வாரியம்</div>
            </div>
            <div class="header-sub">
                <div class="bi-en">Thiru Vi. Ka. Industrial Estate, Guindy, Chennai - 600 032.</div>
                <div class="bi-ta" lang="ta">திரு.வி.கா. தொழிற்சாலை, கிண்டி, சென்னை – 600032.</div>
                <div class="bi-en">{{ $printFormTitle }} - <span class="appl-type">{{ $applTypeLabel }}</span></div>
            </div>
        </div>

        <table width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="70%" valign="top">
                    <table class="info-table">
                        <tr>
                            <td class="lbl"><div class="lbl-bi"><div class="lbl-en">Application Number</div><div class="lbl-ta" lang="ta">விண்ணப்ப எண்</div></div></td>
                            <td class="colon">:</td>
                            <td class="val">{{ $applicant->application_id }}</td>
                        </tr>
                        <tr>
                            <td class="lbl"><div class="lbl-with-sno"><span class="pf-sno">1</span><div class="lbl-bi"><div class="lbl-en">{{ $isFormP ? 'Name of the applicant' : "Applicant's Name" }}</div><div class="lbl-ta" lang="ta">விண்ணப்பதாரர் பெயர்</div></div></div></td>
                            <td class="colon">:</td>
                            <td class="val">{{ $applicant->applicant_name ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl"><div class="lbl-with-sno"><span class="pf-sno">2</span><div class="lbl-bi"><div class="lbl-en">Father's Name</div><div class="lbl-ta" lang="ta">தகப்பனார் பெயர்</div></div></div></td>
                            <td class="colon">:</td>
                            <td class="val">{{ $applicant->fathers_name ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl"><div class="lbl-with-sno"><span class="pf-sno">3</span><div class="lbl-bi"><div class="lbl-en">Email ID</div><div class="lbl-ta" lang="ta">மின்னஞ்சல் முகவரி</div></div></div></td>
                            <td class="colon">:</td>
                            <td class="val">{{ $applicant->applicant_email ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl"><div class="lbl-with-sno"><span class="pf-sno">4</span><div class="lbl-bi"><div class="lbl-en">{{ $isFormP ? 'Address of the applicant' : 'Applicant Address' }}</div><div class="lbl-ta" lang="ta">விண்ணப்பதாரர் முகவரி</div></div></div></td>
                            <td class="colon">:</td>
                            <td class="val">{{ $applicant->applicants_address ?: '—' }}</td>
                        </tr>
                        <tr>
                            <td class="lbl"><div class="lbl-with-sno"><span class="pf-sno">5</span><div class="lbl-bi"><div class="lbl-en">(i) Date of Birth</div><div class="lbl-ta" lang="ta">பிறந்த நாள், மாதம், வருடம்</div></div></div></td>
                            <td class="colon">:</td>
                            <td class="val">{{ format_date($applicant->d_o_b) }}</td>
                        </tr>
                        <tr>
                            <td class="lbl"><div class="lbl-bi" style="padding-left:24px;"><div class="lbl-en">(ii) Age</div><div class="lbl-ta" lang="ta">வயது</div></div></td>
                            <td class="colon">:</td>
                            <td class="val">{{ $applicant->age ?: '—' }}</td>
                        </tr>
                    </table>
                </td>
                <td width="30%" valign="top" align="center">
                    <div class="photo-frame">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Photograph">
                        @endif
                    </div>
                    <div class="sign-frame">
                        @if($signUrl)
                            <img src="{{ $signUrl }}" alt="Signature">
                        @else
                            <div class="sign-missing">
                                <div class="bi-en">Signature not available</div>
                                <div class="bi-ta" lang="ta">கையெழுத்து இல்லை</div>
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        @php
            $eduSectionTitle = match ($formName) {
                'P', 'S' => '6. Details of Technical Qualification passed by the applicant',
                default => "6. Applicant's Educational / Technical Qualification and pass details",
            };
            $docSectionNo = match ($formName) {
                'P' => '8',
                'S' => '10',
                default => '9',
            };
        @endphp
        <div class="summary-heading section-title">
            <div class="bi-en">{{ $eduSectionTitle }}</div>
            <div class="bi-ta" lang="ta">விண்ணப்பதாரரின் தொழில்நுட்ப தேர்ச்சி மற்றும் தேர்ச்சி பற்றிய விவரங்கள்</div>
        </div>
        <div class="summary-card">
            <div class="summary-heading">
                <div class="bi-en">{{ $isFormP ? '(i) Education Details' : 'Education Details' }}</div>
                <div class="bi-ta" lang="ta">கல்வி தகுதி</div>
            </div>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th width="8%"><div class="th-bi"><div class="th-en">#</div><div class="th-ta" lang="ta">எண்</div></div></th>
                        <th><div class="th-bi"><div class="th-en">Education</div><div class="th-ta" lang="ta">கல்வி</div></div></th>
                        <th><div class="th-bi"><div class="th-en">Institution</div><div class="th-ta" lang="ta">நிறுவனம்</div></div></th>
                        <th><div class="th-bi"><div class="th-en">Month / Year</div><div class="th-ta" lang="ta">மாதம் / ஆண்டு</div></div></th>
                        <th><div class="th-bi"><div class="th-en">Certificate No.</div><div class="th-ta" lang="ta">சான்றிதழ் எண்</div></div></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse (($educationalQualifications ?? collect()) as $education)
                        @php
                            $rawMonth = trim((string) ($education->month_passing ?? $education->month_of_passing ?? ''));
                            $monthKey = $rawMonth !== '' && is_numeric($rawMonth) ? str_pad($rawMonth, 2, '0', STR_PAD_LEFT) : $rawMonth;
                            $monthDisplay = $monthLabels[$monthKey] ?? $rawMonth;
                            $levelCode = (string) ($education->educational_level ?? '');
                            $levelLabel = $eduLevelLabels[$levelCode] ?? $levelCode;
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="txt">{{ $levelLabel !== '' ? $levelLabel : '—' }}</td>
                            <td class="txt">{{ $education->institute_name ?: '—' }}</td>
                            <td>{{ trim($monthDisplay.' '.($education->year_of_passing ?? '')) ?: '—' }}</td>
                            <td>{{ data_get($education, 'certificate_no') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No educational details available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($isFormP)
        <div class="summary-card">
                <div class="summary-heading">
                    <div class="bi-en">(ii) Institute in which the applicant has undergone training and the period</div>
                    <div class="bi-ta" lang="ta">பயிற்சி நிறுவனம்</div>
                </div>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th width="8%"><div class="th-bi"><div class="th-en">#</div><div class="th-ta" lang="ta">எண்</div></div></th>
                            <th><div class="th-bi"><div class="th-en">Institute</div><div class="th-ta" lang="ta">நிறுவனம்</div></div></th>
                            <th><div class="th-bi"><div class="th-en">From</div><div class="th-ta" lang="ta">முதல்</div></div></th>
                            <th><div class="th-bi"><div class="th-en">To</div><div class="th-ta" lang="ta">வரை</div></div></th>
                            <th><div class="th-bi"><div class="th-en">Duration</div><div class="th-ta" lang="ta">காலம்</div></div></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse (($institute_details ?? collect()) as $institutes)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td class="txt">{{ $institutes->institute_name_address ?: '—' }}</td>
                                <td>{{ format_date($institutes->from_date) }}</td>
                                <td>{{ format_date($institutes->to_date) }}</td>
                                <td>{{ format_institute_duration($institutes->duration ?? '', $institutes->from_date ?? null, $institutes->to_date ?? null) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No institute details available.</td></tr>
                        @endforelse
                </tbody>
            </table>
            </div>
        @endif

        <div class="summary-card">
            <div class="summary-heading">
                <div class="bi-en">{{ $isFormP ? '(iii) Power Station to which he is attached at present' : '7. Details of Previous and Current Work experiences' }}</div>
                <div class="bi-ta" lang="ta">அனுபவ விவரங்கள்</div>
            </div>
            <table class="summary-table">
                <thead>
                    <tr>
                        <th width="8%"><div class="th-bi"><div class="th-en">#</div><div class="th-ta" lang="ta">எண்</div></div></th>
                        <th><div class="th-bi"><div class="th-en">Organisation</div><div class="th-ta" lang="ta">நிறுவனம்</div></div></th>
                        <th><div class="th-bi"><div class="th-en">Designation</div><div class="th-ta" lang="ta">பதவி</div></div></th>
                        <th><div class="th-bi"><div class="th-en">From</div><div class="th-ta" lang="ta">முதல்</div></div></th>
                        <th><div class="th-bi"><div class="th-en">To</div><div class="th-ta" lang="ta">வரை</div></div></th>
                        <th><div class="th-bi"><div class="th-en">Duration</div><div class="th-ta" lang="ta">காலம்</div></div></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expRows as $exp)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="txt">{{ $exp->company_name ?? $exp->org_name ?? $exp->emp_cate ?? '—' }}</td>
                            <td class="txt">{{ $exp->designation ?? '—' }}</td>
                            <td>{{ format_date($exp->from_date) }}</td>
                            <td>{{ format_date($exp->to_date) }}</td>
                            <td>{{ format_total_exp_years($exp->total_exp ?? $exp->experience ?? null, $exp->from_date ?? null, $exp->to_date ?? null) ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No experience details available.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @if($isFormP)
            <table class="employer-line">
                <tr>
                        <td class="lbl"><div class="lbl-en">(iv) Name of the employer</div><div class="lbl-ta" lang="ta">தொழில் வழங்குநரின் பெயர்</div></td>
                    <td class="colon">:</td>
                        <td class="val">{{ $applicant->employer_detail ?: '—' }}</td>
                    </tr>
                </table>
            @endif
            </div>

        @if($isFormS)
            @php
                $prevValidTo = $applicant->previously_valid_to ?? $applicant->previously_date ?? null;
                $hasPreviousEaQual = !empty($applicant->previously_number) || !empty($prevValidTo);
                $hasWiremanCompCert = !empty($applicant->wcc_no) || !empty($applicant->wcc_from ?? $applicant->wcc_to ?? null);
            @endphp
            <div class="summary-heading section-title">
                <div class="bi-en">8. Do you already possess a Supervisor Competency Certificate issued by this Board? If yes, please furnish the details.</div>
                <div class="bi-ta" lang="ta">இந்த வாரியத்தால் வழங்கப்பட்ட மேற்பார்வையாளர் தகுதி சான்றிதழ் உங்களிடம் உள்ளதா?</div>
            </div>
            <table class="info-table">
                <tr>
                    <td class="lbl"><div class="lbl-en">Answer</div></td>
                    <td class="colon">:</td>
                    <td class="val">{{ $hasPreviousEaQual ? 'Yes' : 'No' }}</td>
                </tr>
                @if($hasPreviousEaQual)
                    <tr><td class="lbl"><div class="lbl-en">Certificate Number</div></td><td class="colon">:</td><td class="val">{{ $applicant->previously_number ?: '—' }}</td></tr>
                    <tr><td class="lbl"><div class="lbl-en">From date</div></td><td class="colon">:</td><td class="val">{{ !empty($applicant->previously_valid_from) ? format_date($applicant->previously_valid_from) : '—' }}</td></tr>
                    <tr><td class="lbl"><div class="lbl-en">To date</div></td><td class="colon">:</td><td class="val">{{ !empty($prevValidTo) ? format_date($prevValidTo) : '—' }}</td></tr>
                @endif
            </table>
            <div class="summary-heading section-title">
                <div class="bi-en">9. Do you also possess Wireman Competency Certificate issued by this Board? If so furnish the details.</div>
                <div class="bi-ta" lang="ta">இந்த வாரியம் வழங்கிய கம்பி இணைப்பாளர் திறன் சான்றிதழ் உள்ளதா?</div>
            </div>
            <table class="info-table">
                <tr>
                    <td class="lbl"><div class="lbl-en">Answer</div></td>
                    <td class="colon">:</td>
                    <td class="val">{{ $hasWiremanCompCert ? 'Yes' : 'No' }}</td>
                </tr>
                @if($hasWiremanCompCert)
                    <tr><td class="lbl"><div class="lbl-en">Certificate Number</div></td><td class="colon">:</td><td class="val">{{ $applicant->wcc_no ?: '—' }}</td></tr>
                    <tr><td class="lbl"><div class="lbl-en">From date</div></td><td class="colon">:</td><td class="val">{{ !empty($applicant->wcc_from) ? format_date($applicant->wcc_from) : '—' }}</td></tr>
                    <tr><td class="lbl"><div class="lbl-en">To date</div></td><td class="colon">:</td><td class="val">{{ !empty($applicant->wcc_to) ? format_date($applicant->wcc_to) : '—' }}</td></tr>
                @endif
            </table>
        @endif

        @if(in_array($formName, ['W', 'WH'], true))
            @php
                $boardCertNo = $applicant->wcc_no ?? $applicant->certificate_no ?? null;
                $boardCertTo = $applicant->wcc_to ?? $applicant->certificate_valid_to ?? $applicant->certificate_date ?? null;
                $hasBoardCert = !empty($boardCertNo) && !empty($boardCertTo);
                $qualTitle = $formName === 'WH'
                    ? '8. Have you applied for and obtained a Certificate of Qualification for Wireman Helper? If yes, please state its number and validity.'
                    : '8. Have you applied for and obtained a Certificate of Qualification for Wireman / Wireman Helper? If yes, please state its number and validity.';
            @endphp
            <div class="summary-heading section-title">
                <div class="bi-en">{{ $qualTitle }}</div>
            </div>
            <table class="info-table">
                <tr>
                    <td class="lbl"><div class="lbl-en">Answer</div></td>
                    <td class="colon">:</td>
                    <td class="val">{{ $hasBoardCert ? 'Yes' : 'No' }}</td>
                </tr>
                @if($hasBoardCert)
                    <tr><td class="lbl"><div class="lbl-en">Certificate Number</div></td><td class="colon">:</td><td class="val">{{ $boardCertNo }}</td></tr>
                    <tr><td class="lbl"><div class="lbl-en">Validity</div></td><td class="colon">:</td><td class="val">{{ format_date($boardCertTo) }}</td></tr>
                @endif
            </table>
        @endif

        <div class="summary-heading section-title">
            <div class="bi-en">{{ $docSectionNo }}. Upload Documents</div>
            <div class="bi-ta" lang="ta">ஆவணங்களைப் பதிவேற்றவும்</div>
        </div>
        <table class="info-table">
            <tr>
                <td class="lbl"><div class="lbl-with-sno"><span class="pf-sno">{{ $docSectionNo }}</span><div class="lbl-bi"><div class="lbl-en">(ii) Aadhaar Number</div><div class="lbl-ta" lang="ta">ஆதார் எண்</div></div></div></td>
                <td class="colon">:</td>
                <td class="val">{{ $maskedAadhaar }}</td>
            </tr>
            <tr>
                <td class="lbl"><div class="lbl-bi" style="padding-left:24px;"><div class="lbl-en">(iv) PAN Card Number</div><div class="lbl-ta" lang="ta">நிரந்தர கணக்கு எண்</div></div></td>
                <td class="colon">:</td>
                <td class="val">{{ $maskedPan }}</td>
            </tr>
        </table>

        <div class="footer">
            <div class="bi-en">Issued by TNELB | Tamil Nadu</div>
            <div class="bi-ta" lang="ta">TNELB வழங்கியது | தமிழ்நாடு</div>
        </div>
    </div>
</div>
<script>
    document.getElementById('cavPrintBtn')?.addEventListener('click', function () {
        var targetId = this.getAttribute('data-print-target');
        if (!targetId || !document.getElementById(targetId)) return;
        window.print();
    });
    document.getElementById('cavExpandAll')?.addEventListener('click', function () {
        var flow = document.getElementById('cavFlow');
        if (!flow) return;
        flow.classList.toggle('is-expanded');
        var open = flow.classList.contains('is-expanded');
        this.innerHTML = (open ? 'Collapse' : 'Expand All') + ' <i class="fa fa-chevron-' + (open ? 'up' : 'down') + '"></i>';
    });
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var value = btn.getAttribute('data-copy') || '';
            if (!value || !navigator.clipboard) return;
            navigator.clipboard.writeText(value).then(function () {
                btn.setAttribute('title', 'Copied');
            });
        });
    });
</script>
@include('admin.include.footer')
