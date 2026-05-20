@include('include.header')
<style>
    /* ── Reset helpers ────────────────────────────────── */
    .fs-form hr { margin: 0; border: 0; border-top: 1px solid #e3e8f0; }
    .fs-form .form-group { margin-bottom: 0; }

    /* ── SweetAlert overrides ─────────────────────────── */
    .swal2-popup li            { font-size: 15px; margin-bottom: 8px; }
    .swal2-popup li ul         { margin-left: 15px; }

    /* ── Page wrapper ─────────────────────────────────── */
    .fs-page-wrap { background: #f0f4f9; min-height: 100vh; padding-bottom: 48px; }

    /* ── Breadcrumb ───────────────────────────────────── */
    .fs-breadcrumb-bar { background: #fff; border-bottom: 1px solid #e3e8f0; padding: 10px 0; }
    .fs-breadcrumb-bar #breadcrumb,
    .fs-breadcrumb-bar #breadcrumb li,
    .fs-breadcrumb-bar #breadcrumb li a { all: unset; }
    .fs-breadcrumb-bar #breadcrumb { display: flex !important; flex-wrap: wrap; align-items: center; gap: 6px; list-style: none !important; margin: 0 !important; padding: 0 !important; font-size: 0.85rem; background: none !important; }
    .fs-breadcrumb-bar #breadcrumb li { display: flex !important; align-items: center; background: none !important; clip-path: none !important; padding: 0 !important; margin: 0 !important; float: none !important; }
    .fs-breadcrumb-bar #breadcrumb li + li::before { content: '›'; color: #adb5bd; margin-right: 6px; font-size: 1rem; line-height: 1; }
    .fs-breadcrumb-bar #breadcrumb a { color: #035ab3 !important; text-decoration: none !important; font-size: 0.85rem !important; background: none !important; padding: 0 !important; cursor: pointer; }
    .fs-breadcrumb-bar #breadcrumb a:hover { text-decoration: underline !important; }

    /* ── Main card ────────────────────────────────────── */
    .fs-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 16px rgba(3,90,179,.10); overflow: hidden; margin-top: 24px; }

    /* ── Card header ──────────────────────────────────── */
    .fs-card-header { background: linear-gradient(135deg, #035ab3 0%, #0472d9 100%); padding: 10px 24px 6px; position: relative; }
    .fs-card-header .header-titles { text-align: center; }
    .fs-card-header .header-titles h5 { margin: 0 0 2px; font-size: 1.05rem; font-weight: 700; letter-spacing: .5px; color: #fff; text-transform: uppercase; line-height: 1.4; }
    .fs-card-header .header-titles h5.tamil-title { font-size: .98rem; font-weight: 400; opacity: .9; }
    .fs-card-header .header-titles .form-badge { display: inline-block; background: rgba(255,255,255,.18); border: 1px solid rgba(255,255,255,.35); color: #fff; border-radius: 20px; padding: 2px 14px; font-size: .82rem; font-weight: 600; margin-top: 4px; letter-spacing: .5px; }
    .fs-card-header .instructions-link { text-align: right; margin-top: 0; margin-bottom: 0; font-size: .82rem; line-height: 1; }
    .fs-card-header .instructions-link a { color: rgba(255,255,255,.9); text-decoration: none; border-bottom: 1px dashed rgba(255,255,255,.5); }
    .fs-card-header .instructions-link a:hover { color: #fff; border-bottom-color: #fff; }

    /* ── Mandatory notice ─────────────────────────────── */
    .fs-mandatory-bar { background: #f8f9ff; border-bottom: 1px solid #e3e8f0; padding: 7px 28px; font-size: .83rem; color: #555; text-align: right; }
    .fs-mandatory-bar .req-dot { color: #d9363e; font-weight: 700; margin-right: 2px; }

    /* ── Form body ────────────────────────────────────── */
    .fs-form-body { padding: 28px 28px 32px; }

    /* ── Section blocks ───────────────────────────────── */
    .fs-section { background: #f8fafd; border: 1px solid #e3e8f0; border-radius: 8px; margin-bottom: 20px; }
    .fs-section-header { display: flex; align-items: center; gap: 10px; padding: 10px 18px; background: #eef3fb; border-bottom: 1px solid #dde5f3; }
    .fs-section-num { display: inline-flex; align-items: center; justify-content: center; width: 26px; height: 26px; border-radius: 50%; background: #035ab3; color: #fff; font-size: .75rem; font-weight: 700; flex-shrink: 0; }
    .fs-section-title { font-size: .9rem; font-weight: 600; color: #1a2a4a; line-height: 1.35; }
    .fs-section-title .section-req { color: #d9363e; }
    .fs-section-title .section-hint { font-size: .78rem; font-weight: 400; color: #5a7299; margin-left: 4px; }
    .fs-section-tamil { font-size: .8rem; color: #5a7299; line-height: 1.4; margin-top: 1px; }
    .fs-section-body { padding: 18px 18px 14px; }

    /* ── Field rows ───────────────────────────────────── */
    .fs-field-label { font-size: .83rem; font-weight: 600; color: #2c3e5e; margin-bottom: 3px; line-height: 1.3; }
    .fs-field-label .req { color: #d9363e; }
    .fs-field-tamil { font-size: .76rem; color: #7a90b0; margin-bottom: 4px; line-height: 1.3; }
    .fs-form .form-control { border: 1px solid #ccd5e3; border-radius: 6px; font-size: .875rem; height: auto; padding: 7px 11px; transition: border-color .2s, box-shadow .2s; background: #fff; }
    .fs-form .form-control:focus { border-color: #035ab3; box-shadow: 0 0 0 3px rgba(3,90,179,.12); outline: none; }
    .fs-form .form-control[readonly], .fs-form .form-control:disabled { background: #f4f6fb; color: #6b7a99; }
    .fs-form textarea.form-control { resize: vertical; }

    /* ── Radio toggle ─────────────────────────────────── */
    .fs-radio-group { display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
    .fs-radio-group .form-check { margin: 0; }
    .fs-radio-group .form-check-input { margin-top: 2px; accent-color: #035ab3; }
    .fs-radio-group .form-check-label { font-size: .875rem; font-weight: 500; color: #2c3e5e; cursor: pointer; }

    /* ── Toggle sub-panel ─────────────────────────────── */
    .fs-toggle-panel { background: #f0f5ff; border: 1px solid #d0ddf5; border-radius: 6px; padding: 16px; margin-top: 12px; }
    .fs-toggle-panel .fs-field-label { color: #1a3a72; }

    /* ── Verify button ────────────────────────────────── */
    .btn-verify { background: #035ab3; color: #fff; border: none; border-radius: 6px; padding: 7px 16px; font-size: .82rem; font-weight: 600; letter-spacing: .3px; cursor: pointer; transition: background .2s; white-space: nowrap; }
    .btn-verify:hover { background: #024a98; color: #fff; }

    /* ── Tables ───────────────────────────────────────── */
    .fs-table-wrap { overflow-x: auto; border-radius: 6px; border: 1px solid #dde5f3; }
    .fs-form table.table { margin-bottom: 0; font-size: .83rem; }
    .fs-form table.table thead th { background: #eef3fb; color: #1a2a4a; font-weight: 600; font-size: .78rem; padding: .45rem .5rem; vertical-align: middle; border-bottom: 2px solid #d0ddf5; border-color: #d0ddf5; line-height: 1.25; }
    .fs-form table.table tbody td { padding: .45rem .5rem; vertical-align: middle; border-color: #e8edf6; }
    .fs-form table.table tbody tr:nth-child(even) td { background: #f8fafd; }
    .fs-form table.table tbody tr:hover td { background: #eef3fb; }
    .fs-form table.table .form-control { font-size: .82rem; padding: 5px 8px; }
    .fs-form .file-limit { font-size: .72rem; color: #28a745; display: block; margin-top: 2px; line-height: 1.3; }

    /* ── File upload wrap ─────────────────────────────── */
    .form-s-file-upload-wrap { display: flex; align-items: center; flex-wrap: wrap; gap: .35rem; }
    .form-s-file-upload-wrap .form-control { flex: 1 1 auto; min-width: 0; }
    #education-table .form-s-file-upload-wrap--combined { display: flex; flex-direction: row; flex-wrap: nowrap; align-items: stretch; align-self: flex-start; gap: 0; width: 100%; min-width: 12rem; max-width: 20rem; border: 1px solid #ccd5e3; border-radius: 6px; overflow: hidden; background: #fff; }
    #education-table .form-s-file-upload-wrap--combined .form-control,
    #education-table .form-s-file-upload-wrap--combined input[type="file"] { flex: 1 1 auto; min-width: 0; width: auto; font-size: .8125rem; border: 0 !important; border-radius: 0 !important; box-shadow: none !important; padding: .3rem .45rem; background: #fff; }

    /* ── Table action cells ───────────────────────────── */
    #education-table td.form-s-actions-cell { vertical-align: middle; width: 3rem; }
    #education-table .form-s-actions-stack { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; gap: .35rem; }

    /* ── Table add/remove buttons ─────────────────────── */
    .btn-tbl-add { background: #035ab3; color: #fff; border: none; border-radius: 5px; padding: 4px 9px; font-size: .8rem; cursor: pointer; transition: background .2s; }
    .btn-tbl-add:hover { background: #024a98; }
    .btn-tbl-remove { background: #dc3545; color: #fff; border: none; border-radius: 5px; padding: 4px 9px; font-size: .8rem; cursor: pointer; transition: background .2s; }
    .btn-tbl-remove:hover { background: #b52a37; }

    /* ── Education table column widths ───────────────── */
    #education-table thead th { font-size: .72rem; font-weight: 600; padding: .3rem .35rem; vertical-align: middle; line-height: 1.2; text-align: center; }
    #education-table thead tr:nth-child(2) th { font-size: .7rem; padding: .25rem .3rem; }
    #education-table tbody td { text-align: center; vertical-align: middle; }
    #education-table tbody .form-control, #education-table tbody select, #education-table tbody input { font-size: .86rem; line-height: 1.25; }

    /* ── Documents upload table ───────────────────────── */
    .fs-docs-table { width: 100%; }
    .fs-docs-table td { vertical-align: middle; padding: 10px 12px; border-color: #e8edf6; }
    .fs-docs-table .doc-serial { width: 48px; min-width: 48px; font-weight: 700; color: #035ab3; font-size: .85rem; white-space: nowrap; text-align: center; }
    .fs-docs-table .doc-label-cell { min-width: 180px; }
    .fs-upload-card { border: 1px dashed #b8c8e2; background: #f8fbff; border-radius: 10px; padding: 12px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
    .fs-upload-controls { display: flex; flex-direction: column; gap: 6px; min-width: 220px; flex: 1 1 220px; }
    .fs-upload-input { width: 100%; max-width: 300px; }
    .fs-upload-file-name { font-size: .75rem; color: #60779c; line-height: 1.3; min-height: 1.1rem; }
    .fs-upload-preview { border: 1px solid #ccd5e3; border-radius: 8px; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
    .fs-upload-preview img { width: 100%; height: 100%; object-fit: cover; display: none; }
    .fs-upload-preview--photo { width: 96px; height: 118px; }
    .fs-upload-preview--sign { width: 180px; height: 80px; }
    .fs-upload-preview--sign img { object-fit: contain; }
    .fs-upload-placeholder { font-size: .72rem; color: #89a0c4; text-align: center; padding: 0 10px; line-height: 1.35; }
    @media (max-width: 575.98px) { .fs-upload-preview--photo { width: 84px; height: 102px; } .fs-upload-preview--sign { width: 144px; height: 68px; } }

    /* ── Declaration ──────────────────────────────────── */
    .fs-declaration { background: #f0f5ff; border: 1px solid #c8d8f5; border-radius: 8px; padding: 16px 20px; margin-top: 4px; }
    .fs-declaration label.container { display: flex; align-items: flex-start; gap: 12px; cursor: pointer; padding: 0; margin: 0; width: 100%; }
    .fs-declaration input[type="checkbox"] { width: 18px; height: 18px; accent-color: #035ab3; flex-shrink: 0; margin-top: 3px; cursor: pointer; }
    .fs-declaration .decl-text { font-size: .875rem; color: #1a2a4a; line-height: 1.6; }
    .fs-declaration .decl-text .tamil { display: block; color: #5a7299; margin-top: 4px; font-size: .82rem; }
    .fs-declaration .checkmark { display: none; }

    /* ── Action buttons ───────────────────────────────── */
    .fs-action-bar { display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; padding: 24px 0 4px; }
    .btn-fs-draft { background: #fff; color: #035ab3; border: 2px solid #035ab3; border-radius: 8px; padding: 10px 28px; font-size: .9rem; font-weight: 600; cursor: pointer; transition: all .2s; }
    .btn-fs-draft:hover { background: #eef3fb; }
    .btn-fs-submit { background: linear-gradient(135deg, #1a9e4f, #15883f); color: #fff; border: none; border-radius: 8px; padding: 10px 28px; font-size: .9rem; font-weight: 600; cursor: pointer; box-shadow: 0 3px 10px rgba(26,158,79,.25); transition: all .2s; }
    .btn-fs-submit:hover { background: linear-gradient(135deg, #15883f, #116e32); box-shadow: 0 4px 14px rgba(26,158,79,.35); }

    /* ── Draft modal ──────────────────────────────────── */
    .overlay-bg { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; display: flex; align-items: center; justify-content: center; }
    .otp-modal { background: #fff; border-radius: 12px; padding: 32px 36px; text-align: center; box-shadow: 0 8px 32px rgba(0,0,0,.2); max-width: 380px; width: 90%; }
    .otp-modal h5 { color: #1a9e4f; font-weight: 700; margin-bottom: 16px; }
    .otp-modal button { background: #035ab3; color: #fff; border: none; border-radius: 6px; padding: 8px 32px; font-size: .9rem; cursor: pointer; }
    .otp-modal button:hover { background: #024a98; }

    /* ── Validation messages ─────────────────────────── */
    .fs-form .text-danger, .fs-form .error-message, .fs-form .error,
    .fs-form span[id$="-error"], .fs-form span[class*="error"], .fs-form #checkboxError { font-size: .78rem !important; line-height: 1.3; display: block; margin-top: 2px; }

    /* ── PDF icon ────────────────────────────────────── */
    .fa-file-pdf-o { color: #d9363e !important; }

    /* ── FontAwesome fix ──────────────────────────────── */
    .comp_certificate .btn .fa, .comp_certificate .btn i.fa,
    .comp_certificate .btn-tbl-add .fa, .comp_certificate .btn-tbl-add i.fa,
    .comp_certificate .btn-tbl-remove .fa, .comp_certificate .btn-tbl-remove i.fa { font-family: 'FontAwesome'; display: inline-block; }

    /* ── Application Preview Modal ───────────────────── */
    .prv-overlay { position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:10000; display:flex; align-items:flex-end; justify-content:center; }
    .prv-panel   { background:#f0f4f9; width:100%; max-width:900px; max-height:92vh; display:flex; flex-direction:column; border-radius:16px 16px 0 0; box-shadow:0 -6px 40px rgba(3,90,179,.18); overflow:hidden; animation:prvSlideUp .3s ease; }
    @keyframes prvSlideUp { from { transform:translateY(60px); opacity:0; } to { transform:translateY(0); opacity:1; } }
    .prv-header  { background:linear-gradient(135deg,#035ab3,#0472d9); padding:14px 24px 12px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0; }
    .prv-header-left h5 { margin:0; font-size:1rem; font-weight:700; color:#fff; }
    .prv-header-left .prv-subtitle { font-size:.78rem; color:rgba(255,255,255,.8); margin-top:2px; }
    .prv-badge  { background:rgba(255,255,255,.18); border:1px solid rgba(255,255,255,.35); color:#fff; border-radius:20px; padding:2px 12px; font-size:.75rem; font-weight:600; margin-left:10px; }
    .prv-close  { background:rgba(255,255,255,.15); border:none; color:#fff; width:32px; height:32px; border-radius:50%; font-size:1.2rem; line-height:1; cursor:pointer; transition:background .2s; flex-shrink:0; }
    .prv-close:hover { background:rgba(255,255,255,.3); }
    .prv-body   { overflow-y:auto; padding:20px 24px; flex:1; }
    .prv-section { background:#fff; border:1px solid #e3e8f0; border-radius:10px; margin-bottom:14px; overflow:hidden; }
    .prv-section-hd { background:#eef3fb; border-bottom:1px solid #dde5f3; padding:8px 16px; display:flex; align-items:center; gap:8px; }
    .prv-section-num { width:22px; height:22px; border-radius:50%; background:#035ab3; color:#fff; font-size:.7rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
    .prv-section-title { font-size:.82rem; font-weight:600; color:#1a2a4a; }
    .prv-section-body { padding:14px 16px; }
    .prv-field  { margin-bottom:10px; }
    .prv-label  { font-size:.72rem; font-weight:600; color:#5a7299; text-transform:uppercase; letter-spacing:.4px; margin-bottom:2px; }
    .prv-value  { font-size:.88rem; color:#1a2a4a; font-weight:500; padding:6px 10px; background:#f8fafd; border:1px solid #e3e8f0; border-radius:6px; min-height:32px; word-break:break-word; }
    .prv-value.prv-empty { color:#aab; font-style:italic; }
    .prv-table  { width:100%; font-size:.78rem; border-collapse:collapse; }
    .prv-table th { background:#eef3fb; color:#1a2a4a; font-weight:600; padding:.35rem .5rem; border:1px solid #dde5f3; font-size:.72rem; white-space:nowrap; }
    .prv-table td { padding:.35rem .5rem; border:1px solid #e3e8f0; vertical-align:middle; }
    .prv-table tr:nth-child(even) td { background:#f8fafd; }
    .prv-badge-yes  { background:#d4edda; color:#155724; border-radius:4px; padding:2px 8px; font-size:.72rem; font-weight:600; }
    .prv-badge-no   { background:#f8d7da; color:#721c24; border-radius:4px; padding:2px 8px; font-size:.72rem; font-weight:600; }
    .prv-thumb { text-align:center; }
    .prv-thumb img { width:80px; height:96px; object-fit:cover; border:2px solid #dde5f3; border-radius:6px; display:block; margin-bottom:4px; background:#f0f4f9; }
    .prv-thumb-sign img { width:140px; height:50px; object-fit:contain; }
    .prv-thumb span { font-size:.7rem; color:#5a7299; }
    .prv-no-img { width:80px; height:96px; background:#f0f4f9; border:2px dashed #ccd5e3; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#aab; font-size:.7rem; text-align:center; }
    .prv-footer { background:#fff; border-top:1px solid #e3e8f0; padding:14px 24px; display:flex; align-items:center; gap:12px; flex-shrink:0; flex-wrap:wrap; }
    .prv-confirm-check { display:flex; align-items:center; gap:8px; flex:1; font-size:.83rem; color:#2c3e5e; cursor:pointer; }
    .prv-confirm-check input { width:16px; height:16px; accent-color:#035ab3; cursor:pointer; }
    .prv-btn-back    { background:#fff; color:#035ab3; border:1px solid #035ab3; border-radius:8px; padding:8px 20px; font-size:.85rem; font-weight:600; cursor:pointer; transition:background .2s; white-space:nowrap; }
    .prv-btn-back:hover { background:#eef3fb; }
    .prv-btn-confirm { background:linear-gradient(135deg,#1a9e4f,#14813f); color:#fff; border:none; border-radius:8px; padding:8px 22px; font-size:.85rem; font-weight:600; cursor:pointer; transition:opacity .2s; white-space:nowrap; }
    .prv-btn-confirm:disabled { opacity:.45; cursor:not-allowed; }
    .prv-btn-confirm:not(:disabled):hover { opacity:.9; }
</style>

{{-- ░░ BREADCRUMB ░░ --}}
<div class="fs-breadcrumb-bar">
    <div class="container">
        <ul id="breadcrumb">
            <li><a href="{{ route('dashboard')}}"><span class="fa fa-home"></span> Dashboard</a></li>
            <li><a href="#"><span class="fa fa-info-circle"></span> Form WH</a></li>
        </ul>
    </div>
</div>

{{-- ░░ PAGE BODY ░░ --}}
<div class="fs-page-wrap">
    <div class="container">
        <div class="fs-card comp_certificate" data-select2-id="14">

            {{-- ── Card header ── --}}
            <div class="fs-card-header">
                <div class="header-titles">
                    <h5>Application for Wireman Helper Competency Certificate</h5>
                    <h5 class="tamil-title">மின் கம்பி உதவியாளர் தகுதிச் சான்றிதழ் பெறுவதற்கான விண்ணப்பம்</h5>
                    <span class="form-badge">FORM - WH / Certificate H</span>
                </div>
                <div class="instructions-link">
                    <span class="text-white font-weight-bold" style="font-size:.82rem;">Instructions &nbsp;</span>
                    <a href="{{url('assets/pdf/form_wh_notes.pdf')}}" target="_blank">தமிழ் <i class="fa fa-file-pdf-o"></i> (38 KB)</a>
                </div>
            </div>

            {{-- ── Mandatory notice ── --}}
            <div class="fs-mandatory-bar">
                <span class="req-dot">*</span> Fields are Mandatory
            </div>

            {{-- ── Form body ── --}}
            <div class="fs-form-body fs-form apply-card">
                <form id="competency_form_ws" enctype="multipart/form-data">

                    {{-- ═══ SECTION 1 & 2 — Name & Father's Name ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-body">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <div class="fs-field-label">1. Applicant's Name <span class="req">*</span></div>
                                    <div class="fs-field-tamil">விண்ணப்பதாரர் பெயர்</div>
                                    <input type="hidden" name="login_id" id="login_id_store" value="{{ Auth::user()->login_id }}">
                                    <input autocomplete="off" class="form-control" id="Applicant_Name" name="applicant_name" type="text" value="{{ $user['salutation'].' '.$user['applicant_name'] }}" readonly>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="fs-field-label">2. Father's Name <span class="req">*</span></div>
                                    <div class="fs-field-tamil">தகப்பனார் பெயர்</div>
                                    <input autocomplete="off" class="form-control" id="Fathers_Name" name="fathers_name" type="text" value="{{ isset($application) ? $application->fathers_name : '' }}" maxlength="80">
                                    <span class="error-message text-danger"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 3 & 4 — Address / DOB ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-body">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <div class="fs-field-label">3. Applicant Address <span class="req">*</span> <span style="font-weight:400;font-size:.78rem;">(To be clear)</span></div>
                                    <div class="fs-field-tamil">விண்ணப்பதாரர் முகவரி <span style="font-size:.72rem;">(தெளிவாக இருத்தல் வேண்டும்)</span></div>
                                    <textarea rows="3" class="form-control" id="applicants_address" name="applicants_address" maxlength="255">{{Auth::user()->address}}</textarea>
                                    <span id="applicants_address_error" class="text-danger error"></span>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="row">
                                        <div class="col-12 col-sm-7 mb-3 mb-sm-0">
                                            <div class="fs-field-label">4. (i) D.O.B <span class="req">*</span></div>
                                            <div class="fs-field-tamil">பிறந்த நாள், மாதம், வருடம்</div>
                                            <input autocomplete="off" class="form-control" id="d_o_b" name="d_o_b" type="text" placeholder="DD/MM/YYYY" value="{{ isset($application) ? $application->d_o_b : '' }}">
                                            <span id="dob-error" class="text-danger"></span>
                                        </div>
                                        <div class="col-12 col-sm-5">
                                            <div class="fs-field-label">4. (ii) Age</div>
                                            <div class="fs-field-tamil">வயது</div>
                                            <input autocomplete="off" class="form-control" id="age" name="age" type="number" value="{{ isset($application) ? $application->age : '' }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 5 — Education ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">5</span>
                            <div>
                                <div class="fs-section-title">
                                    Applicant's Educational / Technical Qualification and pass details
                                    <span class="section-req">*</span>
                                    <span class="section-hint">(Upload the documents)</span>
                                </div>
                                <div class="fs-section-tamil">விண்ணப்பதாரரின் தொழில்நுட்ப தேர்ச்சி மற்றும் தேர்ச்சி பற்றிய விவரங்கள் <span style="font-size:.72rem;">(ஆவணங்களை பதிவேற்ற வேண்டும்)</span></div>
                            </div>
                        </div>
                        <div class="fs-section-body">
                            <div class="fs-table-wrap">
                                <table class="table table-bordered" id="education-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2">S.No</th>
                                            <th rowspan="2">Education Level</th>
                                            <th rowspan="2">Institution/School Name</th>
                                            <th colspan="2" class="text-center">Month & Year of Passing</th>
                                            <th rowspan="2">Certificate No</th>
                                            <th class="text-center" rowspan="2">Upload Document<br><span class="file-limit">File type: PDF (Min 5 KB Max 200 KB)</span></th>
                                            <th class="text-center p-1" rowspan="2">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-add add-more py-1 px-2" title="Add row"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </th>
                                        </tr>
                                        {{-- <tr>
                                            <th class="text-center">Month</th>
                                            <th class="text-center">Year</th>
                                        </tr> --}}
                                    </thead>
                                    <tbody id="education-container">
                                        <tr class="education-fields">
                                            <td class="edu-serial text-center">1</td>
                                            <td>
                                                <select class="form-control educational-level-select" name="educational_level[]">
                                                    <option selected disabled>Select Education</option>
                                                    <option value="Up to 8th Standard">Up to 8th Standard</option>
                                                    <option value="Wireman Helper Examination">Wireman Helper Examination</option>
                                                    <option value="ITI Certificate">ITI Certificate</option>
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control" name="institute_name[]" maxlength="80" value="Dept of Employment &amp; Training"></td>
                                            <td>
                                                <select name="month_of_passing[]" class="form-control">
                                                    <option value="">Select Month</option>
                                                    <option value="01">Jan</option><option value="02">Feb</option>
                                                    <option value="03">Mar</option><option value="04">Apr</option>
                                                    <option value="05">May</option><option value="06">Jun</option>
                                                    <option value="07">Jul</option><option value="08">Aug</option>
                                                    <option value="09">Sep</option><option value="10">Oct</option>
                                                    <option value="11">Nov</option><option value="12">Dec</option>
                                                </select>
                                            </td>
                                            <td>
                                                <select name="year_of_passing[]" class="form-control">
                                                    <option value="0">Select Year</option>
                                                    @php $currentYear = date('Y'); @endphp
                                                    @for ($year = $currentYear; $year >= 1980; $year--)
                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                    @endfor
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" class="form-control certificate-input" name="certificate_no[]" maxlength="20" required>
                                                <span class="error text-danger certificate-error" style="font-size:.75rem;"></span>
                                            </td>
                                            <td>
                                                <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined">
                                                    <input type="file" class="form-control" name="education_document[]" accept=".pdf,application/pdf">
                                                </div>
                                            </td>
                                            <td class="form-s-actions-cell text-center p-1">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-remove remove-education py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 6 — Previous Helper Certificate ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">6</span>
                            <div>
                                <div class="fs-section-title">Have you applied for and obtained a Certificate of Qualification for Wireman Helper? If yes, please state its number and validity.</div>
                                <div class="fs-section-tamil">இதற்கு முன்னாள் விண்ணப்பம் செய்து மின் கம்பி உதவியாளர் தகுதி சான்றிதழ் பெறப்பட்டுள்ளதா? ஆம் என்றால் அதன் எண் மற்றும் செல்லத்தக்க காலம் குறிப்பிடுக</div>
                            </div>
                        </div>
                        <div class="fs-section-body">
                            @php
                                $oldCertNo    = trim((string) request('old_cert_no', ''));
                                $oldExpiryRaw = trim((string) request('old_expiry_date', ''));
                                $oldExpiry    = $oldExpiryRaw !== '' ? \Carbon\Carbon::parse($oldExpiryRaw)->format('Y-m-d') : '';
                                $hasOldPrefill = $oldCertNo !== '';
                            @endphp
                            <div class="fs-radio-group mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input toggle-details" type="radio" name="wireman_license" id="wireman_license_yes" data-target="#previously_details" value="yes" {{ $hasOldPrefill ? 'checked' : '' }}>
                                    <label class="form-check-label" for="wireman_license_yes">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input toggle-details" type="radio" name="wireman_license" id="wireman_license_no" data-target="#previously_details" value="no" {{ $hasOldPrefill ? '' : 'checked' }}>
                                    <label class="form-check-label" for="wireman_license_no">No</label>
                                </div>
                            </div>
                            <div id="previously_details" class="fs-toggle-panel" style="display:{{ $hasOldPrefill ? 'block' : 'none' }};">
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Certificate Number <span class="req">*</span><span class="text-muted" style="font-size:.75rem;font-weight:400;">(eg. H1234)</span></div>
                                        <input autocomplete="off" class="form-control verify-input" id="previously_number_h" name="competency_certificate_no" type="text" data-type="helper" data-error="#licenseError" data-msg="#license_message" placeholder="Certificate Number" data-is_verify="0" maxlength="80" value="{{ $oldCertNo }}">
                                        <input type="hidden" id="cert_verify" name="cert_verify" value="0">
                                        <span id="verify_result"></span>
                                        <span id="licenseError" class="text-danger" style="font-size:.78rem;"></span>
                                        <span id="license_message" class="mt-1"></span>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Date of First Issue <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-issue-date" id="previously_issue_date_h" name="certificate_issue_date" type="date" data-error="#previouslyIssueDateError" value="">
                                        <span id="previouslyIssueDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Date of Expiry <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-date" id="previously_date_h" name="certificate_date" type="date" data-error="#dateError" value="{{ $oldExpiry }}">
                                        <span id="dateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <button type="button" class="btn-verify verify-btn" id="verify_form_wh" data-type="helper" data-url="{{ route('verifylicense') }}">
                                            <i class="fa fa-check-circle"></i> Verify
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 7 — Upload Documents ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">7</span>
                            <div>
                                <div class="fs-section-title">Upload Documents <span class="section-req">*</span></div>
                                <div class="fs-section-tamil">ஆவணங்களைப் பதிவேற்றவும்</div>
                            </div>
                        </div>
                        <div class="fs-section-body p-0">
                            <table class="table fs-docs-table mb-0">
                                <tbody>
                                    {{-- Photo --}}
                                    <tr>
                                        <td class="doc-serial">(i)</td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">Upload Photo <span class="req">*</span></div>
                                            <div class="fs-field-tamil">புகைப்படத்தைப் பதிவேற்றவும்</div>
                                        </td>
                                        <td colspan="3">
                                            <div class="fs-upload-card">
                                                <div class="fs-upload-controls">
                                                    <div class="form-s-file-upload-wrap fs-upload-input">
                                                        <input autocomplete="off" class="form-control" id="upload_photo" name="upload_photo" type="file" accept=".jpg,.jpeg,.png">
                                                    </div>
                                                    <span class="file-limit">File type: JPG, PNG (Max 50 KB)</span>
                                                    <small id="upload_photo_name" class="fs-upload-file-name">No file selected</small>
                                                </div>
                                                <div class="fs-upload-preview fs-upload-preview--photo">
                                                    <span id="photo_placeholder" class="fs-upload-placeholder">Photo preview</span>
                                                    <img id="photo_preview" src="" alt="Photo preview">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    {{-- Aadhaar --}}
                                    <tr>
                                        <td class="doc-serial">(ii)</td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">Aadhaar Number <span class="req">*</span></div>
                                            <div class="fs-field-tamil">ஆதார் எண்</div>
                                        </td>
                                        <td style="min-width:180px;">
                                            <input type="text" class="form-control" name="aadhaar" id="aadhaar" maxlength="14" style="max-width:260px;">
                                            <span id="aadhaar-error" class="text-danger" style="font-size:.78rem;"></span>
                                        </td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">(iii) Upload Aadhaar Document <span class="req">*</span></div>
                                            <div class="fs-field-tamil">ஆதார் ஆவணத்தை பதிவேற்றவும் <span class="req">*</span></div>
                                        </td>
                                        <td style="min-width:200px;">
                                            <div class="form-s-file-upload-wrap" style="max-width:280px;">
                                                <input autocomplete="off" class="form-control" id="aadhaar_doc" name="aadhaar_doc" type="file" accept=".pdf,application/pdf">
                                            </div>
                                            <span class="file-limit">File type: PDF (Max 250 KB)</span>
                                            <small class="text-danger file-error d-block"></small>
                                        </td>
                                    </tr>
                                    {{-- PAN --}}
                                    <tr>
                                        <td class="doc-serial">(iii)</td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">PAN Card Number</div>
                                            <div class="fs-field-tamil">நிரந்தர கணக்கு எண்</div>
                                        </td>
                                        <td style="min-width:180px;">
                                            <input type="text" class="form-control text-uppercase" name="pancard" id="pancard" maxlength="10" autocomplete="off" style="max-width:260px;" placeholder="e.g. ABCDE1234F">
                                            <span id="pancard-error" class="text-danger d-block" style="font-size:.78rem;"></span>
                                        </td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">(iv) Upload PAN Card Document</div>
                                            <div class="fs-field-tamil">பான் கார்டு ஆவணத்தைப் பதிவேற்றவும்</div>
                                        </td>
                                        <td style="min-width:200px;">
                                            <div class="form-s-file-upload-wrap" style="max-width:280px;">
                                                <input autocomplete="off" class="form-control" id="pancard_doc" name="pancard_doc" type="file" accept=".pdf,application/pdf">
                                            </div>
                                            <span class="file-limit">File type: PDF (Max 250 KB)</span>
                                            <small class="text-danger file-error d-block"></small>
                                        </td>
                                    </tr>
                                    {{-- Signature --}}
                                    <tr>
                                        <td class="doc-serial">(v)</td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">Upload Signature <span class="req">*</span></div>
                                            <div class="fs-field-tamil">கையொப்பத்தைப் பதிவேற்றவும்</div>
                                        </td>
                                        <td colspan="3">
                                            <div class="fs-upload-card">
                                                <div class="fs-upload-controls">
                                                    <div class="form-s-file-upload-wrap fs-upload-input">
                                                        <input autocomplete="off" class="form-control" id="upload_sign" name="upload_sign" type="file" accept=".jpg,.jpeg,.png" required>
                                                    </div>
                                                    <span class="file-limit">File type: JPG, PNG (Max 50 KB)</span>
                                                    <small id="upload_sign_name" class="fs-upload-file-name">No file selected</small>
                                                </div>
                                                <div class="fs-upload-preview fs-upload-preview--sign">
                                                    <span id="sign_placeholder" class="fs-upload-placeholder">Signature preview</span>
                                                    <img id="sign_preview" src="" alt="Signature preview">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ═══ Declaration ═══ --}}
                    <div class="fs-declaration">
                        <label class="container">
                            <input type="checkbox" id="declarationCheckbox" required {{ isset($application) ? 'checked' : '' }}>
                            <span class="checkmark"></span>
                            <div class="decl-text">
                                I hereby declare that all the details mentioned above are correct and true to the best of my knowledge.<br>
                                I request that I may be granted a Wireman Helper Competency Certificate.<span class="req">*</span>
                                <span class="tamil">என் அறிவுக்கு எட்டியவரை மேலே குறிப்பிட்டுள்ள விவரங்கள் யாவும் சரியானவை எனவும் உண்மையானவை எனவும் உறுதி கூறுகிறேன்.<br>எனக்கு மின்கம்பி உதவியாளர் தகுதி சான்றிதழ் எனக்கு வழங்குமாறு வேண்டுகிறேன்.</span>
                            </div>
                        </label>
                        <span id="checkboxError" class="text-danger mt-2 d-block" style="display:none!important;font-size:.82rem;">Please check the declaration box before proceeding.</span>
                    </div>

                    {{-- Hidden fields --}}
                    <input type="hidden" id="form_name" name="form_name" value="WH">
                    <input type="hidden" id="license_name" name="license_name" value="H">
                    <input type="hidden" id="amount" name="amount" value="">
                    <input type="hidden" id="form_id" name="form_id" value="3">
                    <input type="hidden" id="appl_type" name="appl_type" value="N">
                    <input type="hidden" id="application_id" name="application_id" value="{{ $application_details->application_id ?? $application->application_id ?? '' }}">
                    @csrf
                    <input type="hidden" id="form_action" name="form_action" value="draft">

                    {{-- ── Action buttons ── --}}
                    <div class="fs-action-bar">
                        @if(! isset($application))
                        <button type="button" class="btn-fs-draft" id="saveDraftBtn"
                            data-url="{{ route('form.draft_submit') }}">
                            <i class="fa fa-floppy-o"></i> Save As Draft
                        </button>
                        @endif
                        <button type="button" class="btn-fs-submit" id="submitPaymentBtn">
                            <i class="fa fa-eye"></i> Preview &amp; Proceed
                        </button>
                    </div>

                </form>
            </div>{{-- /fs-form-body --}}
        </div>{{-- /fs-card --}}
    </div>{{-- /container --}}
</div>{{-- /fs-page-wrap --}}

{{-- ── Draft saved modal ── --}}
<div id="draftModal" class="overlay-bg" style="display:none;">
    <div class="otp-modal">
        <h5><i class="fa fa-check-circle"></i> Your Application Details Saved Successfully</h5>
        <button onclick="closeDraftModal()">OK</button>
    </div>
</div>

{{-- ── Application Preview Modal ── --}}
<div id="appPreviewModal" class="prv-overlay" style="display:none;" role="dialog" aria-modal="true" aria-label="Application Preview">
    <div class="prv-panel">
        <div class="prv-header">
            <div class="prv-header-left">
                <h5><i class="fa fa-file-text-o"></i> Application Preview <span class="prv-badge">FORM - WH / Certificate H</span></h5>
                <div class="prv-subtitle">Please verify all your details before proceeding to payment</div>
            </div>
            <button class="prv-close" onclick="closePreviewModal();if(typeof window._prvResolve==='function'){window._prvResolve(false);window._prvResolve=null;}" title="Close preview">&times;</button>
        </div>
        <div class="prv-body" id="prvBody">
            <div class="prv-section">
                <div class="prv-section-hd"><span class="prv-section-num">1</span><span class="prv-section-title">Personal Information</span></div>
                <div class="prv-section-body">
                    <div class="row">
                        <div class="col-12 col-md-auto mb-3 mb-md-0 d-flex align-items-start" style="gap:12px;">
                            <div class="prv-thumb text-center">
                                <div id="prv_photo_wrap"><div class="prv-no-img">No Photo</div></div>
                                <span>Photo</span>
                            </div>
                            <div class="prv-thumb text-center">
                                <div id="prv_sign_wrap"><div class="prv-no-img" style="width:120px;height:46px;">No Signature</div></div>
                                <span>Signature</span>
                            </div>
                        </div>
                        <div class="col-12 col-md">
                            <div class="row">
                                <div class="col-12 col-sm-6"><div class="prv-field"><div class="prv-label">Applicant's Name</div><div class="prv-value" id="prv_name">—</div></div></div>
                                <div class="col-12 col-sm-6"><div class="prv-field"><div class="prv-label">Father's Name</div><div class="prv-value" id="prv_fathers_name">—</div></div></div>
                                <div class="col-12 col-sm-6"><div class="prv-field"><div class="prv-label">Address</div><div class="prv-value" id="prv_address" style="white-space:pre-line;">—</div></div></div>
                                <div class="col-6 col-sm-3"><div class="prv-field"><div class="prv-label">Date of Birth</div><div class="prv-value" id="prv_dob">—</div></div></div>
                                <div class="col-6 col-sm-3"><div class="prv-field"><div class="prv-label">Age</div><div class="prv-value" id="prv_age">—</div></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="prv-section">
                <div class="prv-section-hd"><span class="prv-section-num">5</span><span class="prv-section-title">Educational / Technical Qualification Details</span></div>
                <div class="prv-section-body p-0">
                    <div style="overflow-x:auto;">
                        <table class="prv-table">
                            <thead><tr><th>S.No</th><th>Education Level</th><th>Institution / School Name</th><th>Month</th><th>Year</th><th>Certificate No</th><th>Document</th></tr></thead>
                            <tbody id="prv_edu_body"><tr><td colspan="7" class="text-center text-muted py-3">—</td></tr></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="prv-section">
                <div class="prv-section-hd"><span class="prv-section-num">6</span><span class="prv-section-title">Previously Obtained Wireman Helper Certificate</span></div>
                <div class="prv-section-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span style="font-size:.8rem;color:#5a7299;font-weight:600;">Certificate Obtained:</span>
                        <span id="prv_cert_yn">—</span>
                    </div>
                    <div id="prv_cert_details_block" style="display:none;">
                        <div class="row">
                            <div class="col-12 col-sm-4"><div class="prv-field mb-1"><div class="prv-label">Certificate No</div><div class="prv-value" id="prv_cert_no">—</div></div></div>
                            <div class="col-12 col-sm-4"><div class="prv-field mb-1"><div class="prv-label">Date of First Issue</div><div class="prv-value" id="prv_cert_issue_date">—</div></div></div>
                            <div class="col-12 col-sm-4"><div class="prv-field mb-1"><div class="prv-label">Date of Expiry</div><div class="prv-value" id="prv_cert_expiry_date">—</div></div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="prv-section">
                <div class="prv-section-hd"><span class="prv-section-num">7</span><span class="prv-section-title">Identity &amp; Uploaded Documents</span></div>
                <div class="prv-section-body">
                    <div class="row align-items-center mb-2">
                        <div class="col-5 col-md-3"><div class="prv-field mb-0"><div class="prv-label">Aadhaar Number</div><div class="prv-value" id="prv_aadhaar">—</div></div></div>
                        <div class="col-7 col-md-4"><div class="prv-field mb-0"><div class="prv-label">Aadhaar Document</div><div class="prv-value" id="prv_aadhaar_doc">—</div></div></div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-5 col-md-3"><div class="prv-field mb-0"><div class="prv-label">PAN Card Number</div><div class="prv-value" id="prv_pan">—</div></div></div>
                        <div class="col-7 col-md-4"><div class="prv-field mb-0"><div class="prv-label">PAN Document</div><div class="prv-value" id="prv_pan_doc">—</div></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="prv-footer">
            <label class="prv-confirm-check"><input type="checkbox" id="prvConfirmCheck"> I confirm that all the above details are correct and true</label>
            <button type="button" class="prv-btn-back" onclick="closePreviewModal();if(typeof window._prvResolve==='function'){window._prvResolve(false);window._prvResolve=null;}"><i class="fa fa-arrow-left"></i> Back to Edit</button>
            <button type="button" class="prv-btn-confirm" id="prvConfirmBtn" disabled><i class="fa fa-credit-card"></i> Confirm &amp; Proceed to Payment</button>
        </div>
    </div>
</div>

<footer class="main-footer">
    @include('include.footer')

    <script>
        function closeDraftModal() {
            document.getElementById('draftModal').style.display = 'none';
        }

        function bindImageUploadPreview(inputId, previewId, nameId, placeholderId) {
            var inputEl = document.getElementById(inputId);
            var previewEl = document.getElementById(previewId);
            var nameEl = document.getElementById(nameId);
            var placeholderEl = document.getElementById(placeholderId);
            if (!inputEl || !previewEl || !nameEl || !placeholderEl) return;
            inputEl.addEventListener('change', function() {
                var file = this.files && this.files[0] ? this.files[0] : null;
                if (!file) {
                    previewEl.removeAttribute('src');
                    previewEl.style.display = 'none';
                    placeholderEl.style.display = 'block';
                    nameEl.textContent = 'No file selected';
                    return;
                }
                nameEl.textContent = file.name;
                var blobUrl = URL.createObjectURL(file);
                previewEl.onload = function() { URL.revokeObjectURL(blobUrl); };
                previewEl.src = blobUrl;
                previewEl.style.display = 'block';
                placeholderEl.style.display = 'none';
            });
        }
        bindImageUploadPreview('upload_photo', 'photo_preview', 'upload_photo_name', 'photo_placeholder');
        bindImageUploadPreview('upload_sign',  'sign_preview',  'upload_sign_name',  'sign_placeholder');
    </script>

    <script>
        const licenseError = document.getElementById('licenseError');

        $('#previously_number_h').on('keyup', function() {
            const value = $(this).val().trim().toUpperCase();
            $(this).val(value);
            const regex = /^(H|LWH)\d+$/;
            licenseError.textContent = '';
            if (value === '') { licenseError.textContent = 'Certificate Number is Required'; return; }
            if (!regex.test(value)) { licenseError.textContent = 'Invalid Certificate Number'; } else { licenseError.textContent = ''; }
        });

        $('#previously_date_h').on('change', function() {
            $('#dateError').text('');
        });

        $('#verify_form_wh').on('click', function() {
            const licenseNumber = $('#previously_number_h').val().trim().toUpperCase();
            const date = $('#previously_date_h').val().trim();
            const regex = /^(H|LWH)\d+$/;
            licenseError.textContent = '';
            $('#dateError').text('');
            let isValid = true;
            if (licenseNumber === '' || !regex.test(licenseNumber)) { licenseError.textContent = 'Enter a valid Certificate Number'; isValid = false; }
            if (date === '') { $('#dateError').text('Date is required'); isValid = false; } else {
                const regexDate = /^(\d{4})-(\d{2})-(\d{2})$/;
                const parts = date.match(regexDate);
                if (!parts) { $('#dateError').text('Enter a valid date'); isValid = false; } else {
                    const year = parseInt(parts[1],10), month = parseInt(parts[2],10)-1, day = parseInt(parts[3],10);
                    const checkDate = new Date(year, month, day);
                    if (checkDate.getFullYear() !== year || checkDate.getMonth() !== month || checkDate.getDate() !== day || year < 1800) { $('#dateError').text('Enter a valid date'); isValid = false; }
                }
            }
            if (!isValid) return;
            $.ajax({
                url: "{{ route('verifylicense') }}", method: "POST",
                data: { license_number: licenseNumber, date: date, _token: $('meta[name="csrf-token"]').attr("content") },
                success: function(response) {
                    let $msgBox = $("#license_message"), $licenseNumber = $('#cert_verify');
                    if (response.exists) { $licenseNumber.val('1'); $msgBox.removeClass("text-danger").addClass("text-success").html("&#10004; License verified."); }
                    else { $licenseNumber.val('0'); $msgBox.removeClass("text-success").addClass("text-danger").html("&#10060; License not found."); }
                },
                error: function() { $("#license_message").removeClass("text-success").addClass("text-danger").html("🚫 Error verifying license. Try again."); }
            });
        });
    </script>

    <script>
        document.addEventListener("click", function(e) {
            let container = document.getElementById("education-container");
            let educationRows = container.querySelectorAll(".education-fields");
            const refreshSerials = () => {
                container.querySelectorAll('.education-fields .edu-serial').forEach((cell, idx) => { cell.textContent = String(idx + 1); });
            };

            if (e.target.closest(".add-more")) {
                if (educationRows.length >= 5) {
                    $('#education-table').next('.education-error').remove();
                    $('<div class="text-danger mt-2 education-error">You can add a maximum of 5 education entries.</div>').insertAfter('#education-table');
                    setTimeout(() => { $('.education-error').fadeOut(); }, 7000);
                    return;
                }
                let newRow = document.createElement("tr");
                newRow.classList.add("education-fields");
                newRow.innerHTML = `
<td class="edu-serial text-center">${educationRows.length + 1}</td>
<td><select class="form-control" name="educational_level[]" required>
    <option selected disabled>Select Education</option>
    <option value="Up to 8th Standard">Up to 8th Standard</option>
    <option value="Wireman Helper Examination">Wireman Helper Examination</option>
    <option value="ITI Certificate">ITI Certificate</option>
</select></td>
<td><input type="text" class="form-control" name="institute_name[]" maxlength="80" value="Dept of Employment &amp; Training" required></td>
<td><select name="month_of_passing[]" class="form-control" required>
    <option value="">Select Month</option>
    <option value="01">Jan</option><option value="02">Feb</option><option value="03">Mar</option>
    <option value="04">Apr</option><option value="05">May</option><option value="06">Jun</option>
    <option value="07">Jul</option><option value="08">Aug</option><option value="09">Sep</option>
    <option value="10">Oct</option><option value="11">Nov</option><option value="12">Dec</option>
</select></td>
<td><select name="year_of_passing[]" class="form-control" required>
    <option value="0">Select Year</option>
    ${[...Array(new Date().getFullYear() - 1979).keys()].map(i => `<option value="${new Date().getFullYear() - i}">${new Date().getFullYear() - i}</option>`).join('')}
</select></td>
<td><input type="text" class="form-control certificate-input" name="certificate_no[]" maxlength="20" required>
<span class="error text-danger certificate-error" style="font-size:.75rem;"></span></td>
<td><div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined"><input type="file" class="form-control" name="education_document[]" accept=".pdf,application/pdf"></div></td>
<td class="form-s-actions-cell text-center p-1"><div class="form-s-actions-stack"><button type="button" class="btn-tbl-remove remove-education py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button></div></td>`;
                container.appendChild(newRow);
                refreshSerials();
            }

            if (e.target.closest(".remove-education")) {
                if (educationRows.length <= 1) {
                    $('#education-table').next('.education-error').remove();
                    $('<div class="text-danger mt-2 education-error">At least one education entry is required.</div>').insertAfter('#education-table');
                    setTimeout(() => { $('.education-error').fadeOut(); }, 7000);
                    return;
                }
                e.target.closest("tr").remove();
                refreshSerials();
            }
        });
    </script>
    <script>
    // ── Preview Modal ──────────────────────────────────────────────────────
    var MONTH_MAP_WH = {'01':'Jan','02':'Feb','03':'Mar','04':'Apr','05':'May','06':'Jun','07':'Jul','08':'Aug','09':'Sep','10':'Oct','11':'Nov','12':'Dec'};
    function fmtDateWH(v){if(!v)return'—';var p=v.split('-');return p.length===3?p[2]+'-'+p[1]+'-'+p[0]:v;}
    function setValWH(id,v){var el=document.getElementById(id);if(!el)return;var t=(v||'').toString().trim();el.textContent=t||'—';el.classList.toggle('prv-empty',!t);}
    function setDocValWH(id,html){var el=document.getElementById(id);if(!el)return;el.innerHTML=html;}
    var NO_FILE_HTML='<span style="color:#aab;font-style:italic;font-size:.8rem;">No File Uploaded</span>';
    function fileLabelWH(inp){return inp&&inp.files&&inp.files[0]?'<span>'+inp.files[0].name+'</span>':NO_FILE_HTML;}

    function populatePreview(){
        setValWH('prv_name',(document.getElementById('Applicant_Name')||{}).value||'');
        setValWH('prv_fathers_name',(document.getElementById('Fathers_Name')||{}).value||'');
        setValWH('prv_address',(document.getElementById('applicants_address')||{}).value||'');
        setValWH('prv_dob',(document.getElementById('d_o_b')||{}).value||'');
        setValWH('prv_age',(document.getElementById('age')||{}).value||'');
        // Education
        var eduBody=document.getElementById('prv_edu_body');eduBody.innerHTML='';
        var eduRows=document.querySelectorAll('#education-container .education-fields');
        if(!eduRows.length){eduBody.innerHTML='<tr><td colspan="7" class="text-center text-muted py-3">No education entries</td></tr>';}
        else{eduRows.forEach(function(row,i){
            var lv=row.querySelector('[name="educational_level[]"]'),inst=row.querySelector('[name="institute_name[]"]');
            var mon=row.querySelector('[name="month_of_passing[]"]'),yr=row.querySelector('[name="year_of_passing[]"]');
            var cert=row.querySelector('[name="certificate_no[]"]'),doc=row.querySelector('[name="education_document[]"]');
            var docLink=(doc&&doc.files&&doc.files[0])?'<a href="'+URL.createObjectURL(doc.files[0])+'" target="_blank" style="color:#035ab3;font-size:.75rem;"><i class="fa fa-file-pdf-o"></i> View</a>':NO_FILE_HTML;
            eduBody.innerHTML+='<tr><td class="text-center">'+(i+1)+'</td><td>'+(lv?lv.value||'—':'—')+'</td><td>'+(inst?inst.value||'—':'—')+'</td><td class="text-center">'+(mon?(MONTH_MAP_WH[mon.value]||mon.value||'—'):'—')+'</td><td class="text-center">'+(yr?(yr.value==='0'||!yr.value?'—':yr.value):'—')+'</td><td>'+(cert?cert.value||'—':'—')+'</td><td class="text-center">'+docLink+'</td></tr>';
        });}
        // Section 6 — Previous Certificate (H)
        var certYes=document.getElementById('wireman_license_yes'),isY=certYes&&certYes.checked;
        var yn=document.getElementById('prv_cert_yn');if(yn)yn.innerHTML=isY?'<span class="prv-badge-yes">Yes</span>':'<span class="prv-badge-no">No</span>';
        var cb=document.getElementById('prv_cert_details_block');if(cb)cb.style.display=isY?'':'none';
        if(isY){
            setValWH('prv_cert_no',(document.getElementById('previously_number_h')||{}).value||'');
            setValWH('prv_cert_issue_date',fmtDateWH((document.getElementById('previously_issue_date_h')||{}).value||''));
            setValWH('prv_cert_expiry_date',fmtDateWH((document.getElementById('previously_date_h')||{}).value||''));
        }
        // Photo & Sign
        var pw=document.getElementById('prv_photo_wrap'),ps=document.getElementById('photo_preview');
        if(pw){var s=ps&&ps.style.display!=='none'?ps.src:'';pw.innerHTML=s?'<img src="'+s+'" alt="Photo" style="width:80px;height:96px;object-fit:cover;border:2px solid #dde5f3;border-radius:6px;">':'<div class="prv-no-img">No Photo</div>';}
        var sw=document.getElementById('prv_sign_wrap'),si=document.getElementById('sign_preview');
        if(sw){var sr=si&&si.style.display!=='none'?si.src:'';sw.innerHTML=sr?'<img src="'+sr+'" alt="Signature" style="width:140px;height:50px;object-fit:contain;border:2px solid #dde5f3;border-radius:6px;">':'<div class="prv-no-img" style="width:140px;height:50px;">No Signature</div>';}
        // Aadhaar & PAN
        setValWH('prv_aadhaar',(document.getElementById('aadhaar')||{}).value||'');
        setValWH('prv_pan',(document.getElementById('pancard')||{}).value||'');
        setDocValWH('prv_aadhaar_doc',fileLabelWH(document.getElementById('aadhaar_doc')));
        setDocValWH('prv_pan_doc',fileLabelWH(document.getElementById('pancard_doc')));
    }
    function openPreviewModal(){populatePreview();var m=document.getElementById('appPreviewModal');m.style.display='flex';document.body.style.overflow='hidden';document.getElementById('prvConfirmCheck').checked=false;document.getElementById('prvConfirmBtn').disabled=true;document.getElementById('prvBody').scrollTop=0;}
    function closePreviewModal() {
        document.getElementById('appPreviewModal').style.display = 'none';
        document.body.style.overflow = '';
        if (typeof window.normalizeCompetencyDynamicSections === 'function') {
            window.normalizeCompetencyDynamicSections();
        }
    }
    document.getElementById('prvConfirmCheck').addEventListener('change',function(){document.getElementById('prvConfirmBtn').disabled=!this.checked;});
    document.getElementById('prvConfirmBtn').addEventListener('click',function(){closePreviewModal();if(typeof window._prvResolve==='function'){window._prvResolve(true);window._prvResolve=null;}});
    document.getElementById('appPreviewModal').addEventListener('click',function(e){if(e.target===this){closePreviewModal();if(typeof window._prvResolve==='function'){window._prvResolve(false);window._prvResolve=null;}}});
    window.showCompetencyPreviewModal=function(){return new Promise(function(resolve){window._prvResolve=resolve;openPreviewModal();});};
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const licenceCode = ($('#license_name').val() || '').trim();
            const applType    = ($('#appl_type').val() || '').trim();
            const issuedLicence = ($('#license_number').val() || '').trim();
            if (!licenceCode || !applType || !$('#amount').length) return;
            $.ajax({
                url: "{{ route('licences.getPaymentDetails') }}", type: "POST",
                data: { licence_code: licenceCode, issued_licence: issuedLicence, appl_type: applType, _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response?.status === 'success' && response?.fees_details?.basic_fees != null) { $('#amount').val(response.fees_details.basic_fees); }
                }
            });
        });
    </script>
</footer>
