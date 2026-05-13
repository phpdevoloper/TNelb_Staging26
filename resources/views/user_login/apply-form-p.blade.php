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

    /* ── Tables ───────────────────────────────────────── */
    .fs-table-wrap { overflow-x: auto; border-radius: 6px; border: 1px solid #dde5f3; }
    .fs-form table.table { margin-bottom: 0; font-size: .83rem; }
    .fs-form table.table thead th { background: #eef3fb; color: #1a2a4a; font-weight: 600; font-size: .78rem; padding: .45rem .5rem; vertical-align: middle; border-bottom: 2px solid #d0ddf5; border-color: #d0ddf5; line-height: 1.25; text-align: center; }
    .fs-form table.table tbody td { padding: .45rem .5rem; vertical-align: middle; border-color: #e8edf6; text-align: center; }
    .fs-form table.table tbody tr:nth-child(even) td { background: #f8fafd; }
    .fs-form table.table tbody tr:hover td { background: #eef3fb; }
    .fs-form table.table .form-control { font-size: .82rem; padding: 5px 8px; }
    .fs-form .file-limit { font-size: .72rem; color: #28a745; display: block; margin-top: 2px; line-height: 1.3; }

    /* ── Table action cells ───────────────────────────── */
    .form-s-actions-stack { display: flex; flex-direction: column; align-items: center; justify-content: flex-start; gap: .35rem; }

    /* ── Table add/remove buttons ─────────────────────── */
    .btn-tbl-add { background: #035ab3; color: #fff; border: none; border-radius: 5px; padding: 4px 9px; font-size: .8rem; cursor: pointer; transition: background .2s; }
    .btn-tbl-add:hover { background: #024a98; }
    .btn-tbl-remove { background: #dc3545; color: #fff; border: none; border-radius: 5px; padding: 4px 9px; font-size: .8rem; cursor: pointer; transition: background .2s; }
    .btn-tbl-remove:hover { background: #b52a37; }

    /* ── Education / institute / work table ──────────── */
    #education-table thead th, #institute-table thead th, #work-table thead th { font-size: .72rem; font-weight: 600; padding: .3rem .35rem; vertical-align: middle; line-height: 1.2; text-align: center; }
    #education-table tbody td, #institute-table tbody td, #work-table tbody td { vertical-align: middle; text-align: center; }

    /* ── Documents upload table ───────────────────────── */
    .fs-docs-table { width: 100%; }
    .fs-docs-table td { vertical-align: middle; padding: 10px 12px; border-color: #e8edf6; }
    .fs-docs-table .doc-serial { width: 48px; min-width: 48px; font-weight: 700; color: #035ab3; font-size: .85rem; white-space: nowrap; text-align: center; }
    .fs-docs-table .doc-label-cell { min-width: 180px; }
    .fs-upload-card { border: 1px dashed #b8c8e2; background: #f8fbff; border-radius: 10px; padding: 12px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
    .fs-upload-controls { display: flex; flex-direction: column; gap: 6px; min-width: 220px; flex: 1 1 220px; }
    .fs-upload-input { width: 100%; max-width: 300px; }
    .form-s-file-upload-wrap { display: flex; align-items: center; flex-wrap: wrap; gap: .35rem; }
    .form-s-file-upload-wrap .form-control { flex: 1 1 auto; min-width: 0; }
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
    .prv-panel   { background:#f0f4f9; width:100%; max-width:960px; max-height:92vh; display:flex; flex-direction:column; border-radius:16px 16px 0 0; box-shadow:0 -6px 40px rgba(3,90,179,.18); overflow:hidden; animation:prvSlideUp .3s ease; }
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
    .prv-sub-label { font-size:.75rem; font-weight:600; color:#1a2a4a; margin:10px 0 4px; }

    /* ── Local file preview ───────────────────────────── */
    .local-file-preview { display:flex; align-items:center; gap:.4rem; margin-top:.35rem; white-space:nowrap; }
    .local-file-preview .preview-link { color:#0056b3 !important; font-size:.78rem; font-weight:600; text-decoration:none; }
    .local-file-preview .preview-link:hover { text-decoration:underline; }
</style>

{{-- ░░ BREADCRUMB ░░ --}}
<div class="fs-breadcrumb-bar">
    <div class="container">
        <ul id="breadcrumb">
            <li><a href="{{ route('dashboard')}}"><span class="fa fa-home"></span> Dashboard</a></li>
            <li><a href="#"><span class="fa fa-info-circle"></span> Form P</a></li>
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
                    <h5>Application for Power Generating Station Operation &amp; Maintenance Competency Certificate</h5>
                    <h5 class="tamil-title">மின்சார உற்பத்தி நிலையத்தின் செயல்பாடு மற்றும் பராமரிப்பு திறன் சான்றிதழுக்கான விண்ணப்பம்</h5>
                    <span class="form-badge">FORM - P / Certificate P</span>
                </div>
                <div class="instructions-link">
                    <span class="text-white font-weight-bold" style="font-size:.82rem;">Instructions &nbsp;</span>
                    <a href="{{url('assets/pdf/form_p_notes.pdf')}}" target="_blank">English <i class="fa fa-file-pdf-o"></i> (8 KB)</a>
                </div>
            </div>

            {{-- ── Mandatory notice ── --}}
            <div class="fs-mandatory-bar">
                <span class="req-dot">*</span> Fields are Mandatory
            </div>

            {{-- ── Form body ── --}}
            <div class="fs-form-body fs-form apply-card">
                <form id="competency_form_p" enctype="multipart/form-data">

                    {{-- ═══ SECTION 1 & 2 — Name & Father's Name ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-body">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <div class="fs-field-label">1. Name of the applicant <span class="req">*</span></div>
                                    <div class="fs-field-tamil">விண்ணப்பதாரர் பெயர்</div>
                                    <input type="hidden" name="login_id" id="login_id_store" value="{{ $user['user_id'] }}">
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
                                    <div class="fs-field-label">3. Address of the applicant <span class="req">*</span> <span style="font-weight:400;font-size:.78rem;">(To be clear)</span></div>
                                    <div class="fs-field-tamil">விண்ணப்பதாரர் முகவரி <span style="font-size:.72rem;">(தெளிவாக இருக்க வேண்டும்)</span></div>
                                    <textarea rows="3" class="form-control" id="applicants_address" name="applicants_address" maxlength="255">{{Auth::user()->address}}</textarea>
                                    <span id="applicants_address_error" class="text-danger error"></span>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="row">
                                        <div class="col-12 col-sm-7 mb-3 mb-sm-0">
                                            <div class="fs-field-label">4. (i) Date of Birth <span class="req">*</span></div>
                                            <div class="fs-field-tamil">பிறந்த நாள், மாதம், வருடம்</div>
                                            <input autocomplete="off" class="form-control" id="d_o_b" name="d_o_b" type="text" placeholder="DD/MM/YYYY" value="{{ isset($application) ? $application->d_o_b : '' }}">
                                            <span id="dob-error" class="text-danger"></span>
                                        </div>
                                        <div class="col-12 col-sm-5">
                                            <div class="fs-field-label">4. (ii) Age <span class="req">*</span></div>
                                            <div class="fs-field-tamil">வயது</div>
                                            <input autocomplete="off" class="form-control" id="age" name="age" type="number" value="{{ isset($application) ? $application->age : '' }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 5 — Technical Qualifications ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">5</span>
                            <div>
                                <div class="fs-section-title">
                                    Details of Technical Qualification passed by the applicant
                                    <span class="section-req">*</span>
                                    <span class="section-hint">(Upload the documents)</span>
                                </div>
                                <div class="fs-section-tamil">விண்ணப்பதாரரின் தொழில்நுட்ப தேர்ச்சி மற்றும் தேர்ச்சி பற்றிய விவரங்கள் <span style="font-size:.72rem;">(ஆவணங்களை பதிவேற்ற வேண்டும்)</span></div>
                            </div>
                        </div>
                        <div class="fs-section-body">

                            {{-- (i) Education table --}}
                            <div class="fs-field-label mb-2">(i) Education Details <span class="req">*</span></div>
                            <div class="fs-table-wrap mb-4">
                                <table class="table table-bordered" id="education-table">
                                    <thead>
                                        <tr>
                                            <th>Education Level</th>
                                            <th>Institution/School Name</th>
                                            <th>Month &amp; Year of Passing</th>
                                            <th>Certificate No</th>
                                            <th class="text-center">Upload Document<br><span class="file-limit">File type: PDF, PNG (Max 200 KB)</span></th>
                                            <th class="text-center p-1">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-add add-more py-1 px-2" title="Add row"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="education-container">
                                        <tr class="education-fields">
                                            <td>
                                                <select class="form-control" name="educational_level[]">
                                                    <option selected disabled>Select Education</option>
                                                    <option value="BEM">B.E(Mechanical)</option>
                                                    <option value="BEE">B.E(Electrical)</option>
                                                    <option value="DiplomaM">Diploma(Mechanical)</option>
                                                    <option value="DiplomaE">Diploma(Electrical)</option>
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control" name="institute_name[]" maxlength="80"></td>
                                            <td>
                                                <div style="display:flex;gap:4px;">
                                                    <select name="month_of_passing[]" class="form-control" style="flex:1;min-width:0;">
                                                        <option value="">Month</option>
                                                        <option value="01">Jan</option><option value="02">Feb</option>
                                                        <option value="03">Mar</option><option value="04">Apr</option>
                                                        <option value="05">May</option><option value="06">Jun</option>
                                                        <option value="07">Jul</option><option value="08">Aug</option>
                                                        <option value="09">Sep</option><option value="10">Oct</option>
                                                        <option value="11">Nov</option><option value="12">Dec</option>
                                                    </select>
                                                    <select name="year_of_passing[]" class="form-control" style="flex:1;min-width:0;">
                                                        <option value="0">Year</option>
                                                        @php $currentYear = date('Y'); @endphp
                                                        @for ($year = $currentYear; $year >= 1980; $year--)
                                                            <option value="{{ $year }}">{{ $year }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </td>
                                            <td><input type="text" class="form-control certificate-input" name="certificate_no[]" maxlength="20" placeholder="Certificate No"></td>
                                            <td><input type="file" class="form-control" name="education_document[]" accept=".pdf,application/pdf"></td>
                                            <td class="text-center p-1">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-remove remove-education py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- (ii) Institute table --}}
                            <div class="fs-field-label mb-2">(ii) Institute in which the applicant has undergone training and the period <span class="req">*</span> <span style="font-weight:400;font-size:.78rem;">(Upload the documents)</span></div>
                            <div class="fs-field-tamil mb-2">விண்ணப்பதாரர் பயிற்சி பெற்ற நிறுவனம் மற்றும் பயிற்சி பெற்ற காலம் <span style="font-size:.72rem;">(ஆவணங்களை பதிவேற்ற வேண்டும்)</span></div>
                            <div class="fs-table-wrap mb-4">
                                <table class="table table-bordered" id="institute-table">
                                    <thead>
                                        <tr>
                                            <th style="width:22%">Institute Name &amp; Address</th>
                                            <th>From date</th>
                                            <th>To date</th>
                                            <th>Duration</th>
                                            <th class="text-center">Upload Document<br><span class="file-limit">File type: PDF, PNG (Max 200 KB)</span></th>
                                            <th class="text-center p-1">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-add add-more-institute py-1 px-2" title="Add row"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="institute-container">
                                        <tr class="institute-fields">
                                            <td><textarea autocomplete="off" class="form-control" name="institute_name_address[]" cols="5" rows="3" maxlength="255"></textarea></td>
                                            <td><input autocomplete="off" class="form-control" name="from_date[]" type="date"></td>
                                            <td><input autocomplete="off" class="form-control" name="to_date[]" type="date"></td>
                                            <td><input autocomplete="off" class="form-control" name="duration[]" type="number" min="0" max="50" readonly></td>
                                            <td><input class="form-control" name="institute_document[]" type="file" accept=".pdf,application/pdf"></td>
                                            <td class="text-center p-1">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-remove remove-institute py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- (iii) Power Station table --}}
                            <div class="fs-field-label mb-2">(iii) Power Station to which he is attached at present <span style="font-weight:400;font-size:.78rem;">(Upload the documents)</span></div>
                            <div class="fs-field-tamil mb-2">விண்ணப்பதாரர் பயிற்சி பெற்ற நிறுவனம் மற்றும் பயிற்சி பெற்ற காலம் <span style="font-size:.72rem;">(ஆவணங்களை பதிவேற்ற வேண்டும்)</span></div>
                            <div class="fs-table-wrap mb-4">
                                <table class="table table-bordered" id="work-table">
                                    <thead>
                                        <tr>
                                            <th rowspan="2" class="align-middle">Power Station</th>
                                            <th colspan="3" class="text-center">Year of Experience</th>
                                            <th rowspan="2" class="align-middle">Designation</th>
                                            <th rowspan="2" class="text-center align-middle">Upload Document<br><span class="file-limit">File type: PDF, PNG (Max 200 KB)</span></th>
                                            <th rowspan="2" class="text-center align-middle p-1">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-add add-more-work py-1 px-2" title="Add row"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th class="text-center" style="font-size:.72rem;font-weight:500;">From (date)</th>
                                            <th class="text-center" style="font-size:.72rem;font-weight:500;">To (date)</th>
                                            <th class="text-center" style="font-size:.72rem;font-weight:500;width:90px;">Total yrs</th>
                                        </tr>
                                    </thead>
                                    <tbody id="work-container">
                                        <tr class="work-fields">
                                            <td><input autocomplete="off" class="form-control" name="work_level[]" type="text" maxlength="80"></td>
                                            <td><input type="date" class="form-control work-date-from" name="work_date_from[]"></td>
                                            <td><input type="date" class="form-control work-date-to" name="work_date_to[]"></td>
                                            <td>
                                                <input type="text" class="form-control work-year-total-display text-center" placeholder="—" readonly tabindex="-1">
                                                <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]">
                                            </td>
                                            <td><input autocomplete="off" class="form-control" name="designation[]" type="text" maxlength="80"></td>
                                            <td><input class="form-control" name="work_document[]" type="file" accept=".pdf,application/pdf"></td>
                                            <td class="text-center p-1">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-remove remove-work py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- (iv) Employer name --}}
                            <div class="row align-items-start">
                                <div class="col-12 col-md-3">
                                    <div class="fs-field-label">(iv) Name of the employer</div>
                                    <div class="fs-field-tamil">தொழில் வழங்குநரின் பெயர்</div>
                                </div>
                                <div class="col-12 col-md-9">
                                    <textarea class="form-control" name="employer_name" id="employer_name" cols="5" rows="3" maxlength="255"></textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ═══ SECTION 6 — Previous Application ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">6</span>
                            <div>
                                <div class="fs-section-title">Have you made any previous application? If so, state reference No. and date.</div>
                                <div class="fs-section-tamil">இதற்கு முன்னாள் விண்ணப்பம் செய்துள்ளீர்களா? ஆம் என்றால் அதன் குறிப்பு எண் மற்றும் தேதியை குறிப்பிடுக</div>
                            </div>
                        </div>
                        <div class="fs-section-body">
                            <div class="fs-radio-group mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input toggle-details" type="radio" name="previous_license" id="previous_license_yes" data-target="#previously_details" value="yes">
                                    <label class="form-check-label" for="previous_license_yes">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input toggle-details" type="radio" name="previous_license" id="previous_license_no" data-target="#previously_details" value="no" checked>
                                    <label class="form-check-label" for="previous_license_no">No</label>
                                </div>
                            </div>
                            <div id="previously_details" class="fs-toggle-panel" style="display:none;">
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-4">
                                        <div class="fs-field-label">Application Number <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control" id="previously_number" name="previously_number" type="text" data-type="license" placeholder="Application Number" maxlength="80">
                                        <span id="licenseError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="fs-field-label">Date <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-date" id="previously_date" name="previously_date" type="date" data-error="#dateError">
                                        <span id="dateError" class="text-danger" style="font-size:.78rem;"></span>
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
                                            <div class="fs-field-label">(iii) Upload Aadhaar Document</div>
                                            <div class="fs-field-tamil">ஆதார் ஆவணத்தை பதிவேற்றவும்</div>
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
                                        <td class="doc-serial">(iv)</td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">PAN Card Number</div>
                                            <div class="fs-field-tamil">நிரந்தர கணக்கு எண்</div>
                                        </td>
                                        <td style="min-width:180px;">
                                            <input type="text" class="form-control text-uppercase" name="pancard" id="pancard" maxlength="10" autocomplete="off" style="max-width:260px;" placeholder="e.g. ABCDE1234F">
                                            <span id="pancard-error" class="text-danger d-block" style="font-size:.78rem;"></span>
                                        </td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">(v) Upload PAN Card Document</div>
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
                                        <td class="doc-serial">(vi)</td>
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
                                I hereby declare that the particulars stated above are correct and true to the best of my knowledge.<br>
                                I request that I may be granted a Power Generating Station Operation and Maintenance Competency Certificate.<span class="req">*</span>
                                <span class="tamil">என் அறிவின் படி மேலே குறிப்பிட்டுள்ள விவரங்கள் அனைத்தும் சரியானதும் உண்மையானதுமாக இருப்பதாக நான் இங்கே அறிவிக்கிறேன்.<br>மின்சாரம் உற்பத்தி நிலையத்தின் செயல்பாடு மற்றும் பராமரிப்பு திறன் சான்றிதழை எனக்கு வழங்குமாறு நான் கேட்டுக்கொள்கிறேன்.</span>
                            </div>
                        </label>
                        <span id="checkboxError" class="text-danger mt-2 d-block" style="display:none!important;font-size:.82rem;">Please check the declaration box before proceeding.</span>
                    </div>

                    {{-- Hidden fields --}}
                    <input type="hidden" id="application_id" name="application_id" value="{{ $application->id ?? '' }}">
                    <input type="hidden" id="form_name" name="form_name" value="P">
                    <input type="hidden" id="license_name" name="license_name" value="P">
                    <input type="hidden" id="appl_type" name="appl_type" value="N">
                    <input type="hidden" id="form_action" name="form_action" value="draft">
                    @csrf

                    {{-- ── Action buttons ── --}}
                    <div class="fs-action-bar">
                        @if(! isset($application))
                        <button type="button" class="btn-fs-draft" id="DraftBtn"
                            data-url="{{ route('form.draft_submit') }}"
                            data-id="{{ $application_details->application_id ?? '' }}">
                            <i class="fa fa-floppy-o"></i> Save As Draft
                        </button>
                        @endif
                        <button type="button" class="btn-fs-submit" id="ProceedtoPayment">
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
                <h5><i class="fa fa-file-text-o"></i> Application Preview <span class="prv-badge">FORM - P / Certificate P</span></h5>
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
                <div class="prv-section-hd"><span class="prv-section-num">5</span><span class="prv-section-title">Technical Qualifications &amp; Experience</span></div>
                <div class="prv-section-body">
                    <div class="prv-sub-label">(i) Education Details</div>
                    <div style="overflow-x:auto;margin-bottom:12px;">
                        <table class="prv-table">
                            <thead><tr><th>Education Level</th><th>Institution</th><th>Month</th><th>Year</th><th>Certificate No</th><th>Document</th></tr></thead>
                            <tbody id="prv_edu_body"><tr><td colspan="6" class="text-center text-muted py-3">—</td></tr></tbody>
                        </table>
                    </div>
                    <div class="prv-sub-label">(ii) Training Institute</div>
                    <div style="overflow-x:auto;margin-bottom:12px;">
                        <table class="prv-table">
                            <thead><tr><th>Institute Name &amp; Address</th><th>From Date</th><th>To Date</th><th>Duration</th><th>Document</th></tr></thead>
                            <tbody id="prv_inst_body"><tr><td colspan="5" class="text-center text-muted py-3">—</td></tr></tbody>
                        </table>
                    </div>
                    <div class="prv-sub-label">(iii) Power Station</div>
                    <div style="overflow-x:auto;margin-bottom:12px;">
                        <table class="prv-table">
                            <thead><tr><th>Power Station</th><th>From Date</th><th>To Date</th><th>Total yrs</th><th>Designation</th><th>Document</th></tr></thead>
                            <tbody id="prv_work_body"><tr><td colspan="6" class="text-center text-muted py-3">—</td></tr></tbody>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-12 col-md-8"><div class="prv-field mb-0"><div class="prv-label">(iv) Name of the Employer</div><div class="prv-value" id="prv_employer_name" style="white-space:pre-line;">—</div></div></div>
                    </div>
                </div>
            </div>
            <div class="prv-section">
                <div class="prv-section-hd"><span class="prv-section-num">6</span><span class="prv-section-title">Previous Application Details</span></div>
                <div class="prv-section-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span style="font-size:.8rem;color:#5a7299;font-weight:600;">Applied Previously:</span>
                        <span id="prv_cert_yn">—</span>
                    </div>
                    <div id="prv_cert_details_block" style="display:none;">
                        <div class="row">
                            <div class="col-12 col-sm-4"><div class="prv-field mb-1"><div class="prv-label">Application Number</div><div class="prv-value" id="prv_cert_no">—</div></div></div>
                            <div class="col-12 col-sm-4"><div class="prv-field mb-1"><div class="prv-label">Date</div><div class="prv-value" id="prv_cert_date">—</div></div></div>
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
    /* ── File upload preview for Form P ─────────────────────────────────── */
    function clearLocalPreviewP($inp) {
        var $prev = $inp.next('.local-file-preview');
        var old = $prev.data('blobUrl');
        if (old) URL.revokeObjectURL(old);
        $prev.remove();
        $inp.removeAttr('data-has-local-file');
    }

    function showFilePreviewP($inp, file, maxKB, minKB) {
        clearLocalPreviewP($inp);
        if (!file) return;
        var maxSize = (maxKB || 200) * 1024;
        var minSize = (minKB || 0) * 1024;
        if (file.type !== 'application/pdf') { window.alert('Only PDF files are allowed.'); $inp[0].value = ''; return; }
        if (minSize && file.size < minSize) { window.alert('File size must be at least ' + minKB + ' KB.'); $inp[0].value = ''; return; }
        if (file.size > maxSize) { window.alert('File size should not exceed ' + maxKB + ' KB.'); $inp[0].value = ''; return; }
        var blobUrl = URL.createObjectURL(file);
        $inp.attr('data-has-local-file', '1');
        var $preview = $('<div class="local-file-preview"></div>').data('blobUrl', blobUrl);
        $preview.append($('<a>', { href: blobUrl, target: '_blank', rel: 'noopener noreferrer', class: 'preview-link' })
            .html('<i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View Document'));
        $inp.after($preview);
    }

    $(document).on('change', '#competency_form_p input[type="file"]', function() {
        var n = this.name;
        if (n === 'upload_photo' || n === 'upload_sign') return;
        var maxKB = (n === 'aadhaar_doc' || n === 'pancard_doc') ? 250 : 200;
        showFilePreviewP($(this), this.files && this.files[0], maxKB, 5);
    });
    </script>

    <script>
        document.addEventListener("click", function(e) {
            let container = document.getElementById("education-container");
            let educationRows = container.querySelectorAll(".education-fields");

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
<td><select class="form-control" name="educational_level[]" required>
    <option selected disabled>Select Education</option>
    <option value="BEM">B.E(Mechanical)</option>
    <option value="BEE">B.E(Electrical)</option>
    <option value="DiplomaM">Diploma(Mechanical)</option>
    <option value="DiplomaE">Diploma(Electrical)</option>
</select></td>
<td><input type="text" class="form-control" name="institute_name[]" maxlength="80" required></td>
<td><div style="display:flex;gap:4px;">
<select name="month_of_passing[]" class="form-control" style="flex:1;min-width:0;" required>
    <option value="">Month</option>
    <option value="01">Jan</option><option value="02">Feb</option><option value="03">Mar</option>
    <option value="04">Apr</option><option value="05">May</option><option value="06">Jun</option>
    <option value="07">Jul</option><option value="08">Aug</option><option value="09">Sep</option>
    <option value="10">Oct</option><option value="11">Nov</option><option value="12">Dec</option>
</select>
<select name="year_of_passing[]" class="form-control" style="flex:1;min-width:0;" required>
    <option value="0">Year</option>
    ${[...Array(new Date().getFullYear() - 1979).keys()].map(i => `<option value="${new Date().getFullYear() - i}">${new Date().getFullYear() - i}</option>`).join('')}
</select>
</div></td>
<td><input type="text" class="form-control certificate-input" name="certificate_no[]" maxlength="20" placeholder="Certificate No"></td>
<td><input type="file" class="form-control" name="education_document[]" accept=".pdf,application/pdf"></td>
<td class="text-center p-1"><div class="form-s-actions-stack"><button type="button" class="btn-tbl-remove remove-education py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button></div></td>`;
                container.appendChild(newRow);
            }

            if (e.target.closest(".remove-education")) {
                if (educationRows.length <= 1) {
                    $('#education-table').next('.education-error').remove();
                    $('<div class="text-danger mt-2 education-error">At least one education entry is required.</div>').insertAfter('#education-table');
                    setTimeout(() => { $('.education-error').fadeOut(); }, 7000);
                    return;
                }
                e.target.closest("tr").remove();
            }
        });
    </script>

    <script>
        document.addEventListener("click", function(e) {
            let container = document.getElementById("work-container");
            let workRows = container.querySelectorAll(".work-fields");

            if (e.target.closest(".add-more-work")) {
                if (workRows.length >= 3) {
                    $('#work-table').next('.work-error').remove();
                    $('<div class="text-danger mt-2 work-error">You can add a maximum of 3 work experience entries.</div>').insertAfter('#work-table');
                    setTimeout(() => { $('.work-error').fadeOut(); }, 7000);
                    return;
                }
                let newRow = document.createElement("tr");
                newRow.classList.add("work-fields");
                newRow.innerHTML = `
<td><input autocomplete="off" class="form-control" name="work_level[]" type="text" maxlength="80"></td>
<td><input type="date" class="form-control work-date-from" name="work_date_from[]"></td>
<td><input type="date" class="form-control work-date-to" name="work_date_to[]"></td>
<td>
    <input type="text" class="form-control work-year-total-display text-center" placeholder="—" readonly tabindex="-1">
    <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]">
</td>
<td><input autocomplete="off" class="form-control" name="designation[]" type="text" maxlength="80"></td>
<td><input class="form-control" name="work_document[]" type="file" accept=".pdf,application/pdf"></td>
<td class="text-center p-1"><div class="form-s-actions-stack"><button type="button" class="btn-tbl-remove remove-work py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button></div></td>`;
                container.appendChild(newRow);
            }

            if (e.target.closest(".remove-work")) {
                e.target.closest("tr").remove();
            }
        });

        function calcWorkTotalYearsP(fromVal, toVal) {
            if (!fromVal || !toVal) return '';
            var from = new Date(fromVal + 'T12:00:00');
            var to = new Date(toVal + 'T12:00:00');
            if (Number.isNaN(from.getTime()) || Number.isNaN(to.getTime())) return '';
            if (to < from) return 'Invalid range';
            var years = (to - from) / 86400000 / 365.25;
            return (Math.round(years * 10) / 10).toFixed(1);
        }
        function refreshWorkTotalP(row) {
            if (!row) return;
            var fromInput = row.querySelector('.work-date-from');
            var toInput = row.querySelector('.work-date-to');
            var displayInput = row.querySelector('.work-year-total-display');
            var hiddenInput = row.querySelector('.work-experience-total-hidden');
            if (!fromInput || !toInput || !displayInput) return;
            var total = calcWorkTotalYearsP(fromInput.value, toInput.value);
            displayInput.value = total;
            if (hiddenInput) hiddenInput.value = (total === 'Invalid range') ? '' : total;
        }
        document.addEventListener('change', function (e) {
            if (!e.target.matches('.work-date-from, .work-date-to')) return;
            refreshWorkTotalP(e.target.closest('.work-fields'));
        });
        document.querySelectorAll('#work-container .work-fields').forEach(refreshWorkTotalP);

        document.addEventListener("click", function(e) {
            let container = document.getElementById("institute-container");
            let instituteEntry = container.querySelectorAll(".institute-fields");

            if (e.target.closest(".add-more-institute")) {
                if (instituteEntry.length >= 3) {
                    $('#institute-table').next('.institute-error').remove();
                    $('<div class="text-danger mt-2 institute-error">You can add a maximum of 3 institute entries.</div>').insertAfter('#institute-table');
                    setTimeout(() => { $('.institute-error').fadeOut(); }, 7000);
                    return;
                }
                let newRow = document.createElement("tr");
                newRow.classList.add("institute-fields");
                newRow.innerHTML = `
                <td><textarea autocomplete="off" class="form-control" name="institute_name_address[]" cols="5" rows="3" maxlength="255"></textarea></td>
                <td><input type="date" class="form-control" name="from_date[]"></td>
                <td><input type="date" class="form-control" name="to_date[]"></td>
                <td><input type="number" class="form-control" name="duration[]" min="0" max="50" readonly></td>
                <td><input type="file" class="form-control" name="institute_document[]" accept=".pdf,.png,.jpg,.jpeg"></td>
                <td class="text-center p-1"><div class="form-s-actions-stack"><button type="button" class="btn-tbl-remove remove-institute py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button></div></td>`;
                    container.appendChild(newRow);
                }

            if (e.target.closest(".remove-institute")) {
                if (instituteEntry.length <= 1) {
                    $('#institute-table').next('.institute-error').remove();
                    $('<div class="text-danger mt-2 institute-error">You must have at least one institute entry.</div>').insertAfter('#institute-table');
                    setTimeout(() => { $('.institute-error').fadeOut(); }, 7000);
                    return;
                }
                e.target.closest("tr").remove();
            }
        });

        function calculateInstituteDurationYears(fromDate, toDate) {
            if (!fromDate || !toDate) return '';
            const from = new Date(fromDate + 'T00:00:00');
            const to = new Date(toDate + 'T00:00:00');
            if (Number.isNaN(from.getTime()) || Number.isNaN(to.getTime()) || to < from) return '';
            let years = to.getFullYear() - from.getFullYear();
            const monthDiff = to.getMonth() - from.getMonth();
            const dayDiff = to.getDate() - from.getDate();
            if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) years -= 1;
            return years < 0 ? '' : String(years);
        }

        function updateInstituteDuration(row) {
            if (!row) return;
            const fromInput = row.querySelector('input[name="from_date[]"]');
            const toInput = row.querySelector('input[name="to_date[]"]');
            const durationInput = row.querySelector('input[name="duration[]"]');
            if (!fromInput || !toInput || !durationInput) return;
            durationInput.value = calculateInstituteDurationYears(fromInput.value, toInput.value);
        }

        document.addEventListener('change', function (e) {
            if (!e.target.matches('input[name="from_date[]"], input[name="to_date[]"]')) return;
            updateInstituteDuration(e.target.closest('.institute-fields'));
        });

        document.querySelectorAll('#institute-container .institute-fields').forEach(updateInstituteDuration);
    </script>
    <script>
    // ── Preview Modal ──────────────────────────────────────────────────────
    var EDU_LEVEL_MAP_P = {'BEM':'B.E(Mechanical)','BEE':'B.E(Electrical)','DiplomaM':'Diploma(Mechanical)','DiplomaE':'Diploma(Electrical)'};
    var MONTH_MAP_P = {'01':'Jan','02':'Feb','03':'Mar','04':'Apr','05':'May','06':'Jun','07':'Jul','08':'Aug','09':'Sep','10':'Oct','11':'Nov','12':'Dec'};
    function fmtDateP(v){if(!v)return'—';var p=v.split('-');return p.length===3?p[2]+'-'+p[1]+'-'+p[0]:v;}
    function setValP(id,v){var el=document.getElementById(id);if(!el)return;var t=(v||'').toString().trim();el.textContent=t||'—';el.classList.toggle('prv-empty',!t);}
    function fileLabelP(inp){return inp&&inp.files&&inp.files[0]?inp.files[0].name:'—';}

    function populatePreview(){
        setValP('prv_name',(document.getElementById('Applicant_Name')||{}).value||'');
        setValP('prv_fathers_name',(document.getElementById('Fathers_Name')||{}).value||'');
        setValP('prv_address',(document.getElementById('applicants_address')||{}).value||'');
        setValP('prv_dob',(document.getElementById('d_o_b')||{}).value||'');
        setValP('prv_age',(document.getElementById('age')||{}).value||'');
        // Education
        var eduBody=document.getElementById('prv_edu_body');eduBody.innerHTML='';
        var eduRows=document.querySelectorAll('#education-container .education-fields');
        if(!eduRows.length){eduBody.innerHTML='<tr><td colspan="6" class="text-center text-muted py-3">No education entries</td></tr>';}
        else{eduRows.forEach(function(row,i){
            var lv=row.querySelector('[name="educational_level[]"]'),inst=row.querySelector('[name="institute_name[]"]');
            var mon=row.querySelector('[name="month_of_passing[]"]'),yr=row.querySelector('[name="year_of_passing[]"]');
            var cert=row.querySelector('[name="certificate_no[]"]'),doc=row.querySelector('[name="education_document[]"]');
            var docLink=(doc&&doc.files&&doc.files[0])?'<a href="'+URL.createObjectURL(doc.files[0])+'" target="_blank" style="color:#035ab3;font-size:.75rem;"><i class="fa fa-file-pdf-o"></i> View</a>':'<span class="text-muted">—</span>';
            eduBody.innerHTML+='<tr><td>'+(lv?(EDU_LEVEL_MAP_P[lv.value]||lv.value||'—'):'—')+'</td><td>'+(inst?inst.value||'—':'—')+'</td><td class="text-center">'+(mon?(MONTH_MAP_P[mon.value]||mon.value||'—'):'—')+'</td><td class="text-center">'+(yr?(yr.value==='0'||!yr.value?'—':yr.value):'—')+'</td><td>'+(cert?cert.value||'—':'—')+'</td><td class="text-center">'+docLink+'</td></tr>';
        });}
        // Institute
        var instBody=document.getElementById('prv_inst_body');instBody.innerHTML='';
        var instRows=document.querySelectorAll('#institute-container .institute-fields');
        if(!instRows.length){instBody.innerHTML='<tr><td colspan="5" class="text-center text-muted py-3">No institute entries</td></tr>';}
        else{instRows.forEach(function(row){
            var nm=row.querySelector('[name="institute_name_address[]"]'),dur=row.querySelector('[name="duration[]"]');
            var fr=row.querySelector('[name="from_date[]"]'),to=row.querySelector('[name="to_date[]"]');
            var doc=row.querySelector('[name="institute_document[]"]');
            var docLink=(doc&&doc.files&&doc.files[0])?'<a href="'+URL.createObjectURL(doc.files[0])+'" target="_blank" style="color:#035ab3;font-size:.75rem;"><i class="fa fa-file-pdf-o"></i> View</a>':'<span class="text-muted">—</span>';
            instBody.innerHTML+='<tr><td style="white-space:pre-line;">'+(nm?nm.value||'—':'—')+'</td><td>'+fmtDateP((fr||{}).value||'')+'</td><td>'+fmtDateP((to||{}).value||'')+'</td><td class="text-center">'+(dur?dur.value||'—':'—')+'</td><td class="text-center">'+docLink+'</td></tr>';
        });}
        // Power Station
        var wBody=document.getElementById('prv_work_body');wBody.innerHTML='';
        var wRows=document.querySelectorAll('#work-container .work-fields');
        if(!wRows.length){wBody.innerHTML='<tr><td colspan="6" class="text-center text-muted py-3">No entries</td></tr>';}
        else{wRows.forEach(function(row){
            var co=row.querySelector('[name="work_level[]"]'),fr=row.querySelector('[name="work_date_from[]"]'),to=row.querySelector('[name="work_date_to[]"]'),tot=row.querySelector('.work-year-total-display'),de=row.querySelector('[name="designation[]"]'),doc=row.querySelector('[name="work_document[]"]');
            var docLink=(doc&&doc.files&&doc.files[0])?'<a href="'+URL.createObjectURL(doc.files[0])+'" target="_blank" style="color:#035ab3;font-size:.75rem;"><i class="fa fa-file-pdf-o"></i> View</a>':'<span class="text-muted">—</span>';
            wBody.innerHTML+='<tr><td>'+(co?co.value||'—':'—')+'</td><td class="text-center">'+fmtDateP((fr||{}).value||'')+'</td><td class="text-center">'+fmtDateP((to||{}).value||'')+'</td><td class="text-center">'+(tot?tot.value||'—':'—')+'</td><td>'+(de?de.value||'—':'—')+'</td><td class="text-center">'+docLink+'</td></tr>';
        });}
        // Employer
        setValP('prv_employer_name',(document.getElementById('employer_name')||{}).value||'');
        // Section 6 — Previous Application
        var prevYes=document.getElementById('previous_license_yes'),isY=prevYes&&prevYes.checked;
        var yn=document.getElementById('prv_cert_yn');if(yn)yn.innerHTML=isY?'<span class="prv-badge-yes">Yes</span>':'<span class="prv-badge-no">No</span>';
        var cb=document.getElementById('prv_cert_details_block');if(cb)cb.style.display=isY?'':'none';
        if(isY){
            setValP('prv_cert_no',(document.getElementById('previously_number')||{}).value||'');
            setValP('prv_cert_date',fmtDateP((document.getElementById('previously_date')||{}).value||''));
        }
        // Photo & Sign
        var pw=document.getElementById('prv_photo_wrap'),ps=document.getElementById('photo_preview');
        if(pw){var s=ps&&ps.style.display!=='none'?ps.src:'';pw.innerHTML=s?'<img src="'+s+'" alt="Photo" style="width:80px;height:96px;object-fit:cover;border:2px solid #dde5f3;border-radius:6px;">':'<div class="prv-no-img">No Photo</div>';}
        var sw=document.getElementById('prv_sign_wrap'),si=document.getElementById('sign_preview');
        if(sw){var sr=si&&si.style.display!=='none'?si.src:'';sw.innerHTML=sr?'<img src="'+sr+'" alt="Signature" style="width:140px;height:50px;object-fit:contain;border:2px solid #dde5f3;border-radius:6px;">':'<div class="prv-no-img" style="width:140px;height:50px;">No Signature</div>';}
        // Aadhaar & PAN
        setValP('prv_aadhaar',(document.getElementById('aadhaar')||{}).value||'');
        setValP('prv_pan',(document.getElementById('pancard')||{}).value||'');
        setValP('prv_aadhaar_doc',fileLabelP(document.getElementById('aadhaar_doc')));
        setValP('prv_pan_doc',fileLabelP(document.getElementById('pancard_doc')));
    }
    function openPreviewModal(){populatePreview();var m=document.getElementById('appPreviewModal');m.style.display='flex';document.body.style.overflow='hidden';document.getElementById('prvConfirmCheck').checked=false;document.getElementById('prvConfirmBtn').disabled=true;document.getElementById('prvBody').scrollTop=0;}
    function closePreviewModal(){document.getElementById('appPreviewModal').style.display='none';document.body.style.overflow='';}
    document.getElementById('prvConfirmCheck').addEventListener('change',function(){document.getElementById('prvConfirmBtn').disabled=!this.checked;});
    document.getElementById('prvConfirmBtn').addEventListener('click',function(){closePreviewModal();if(typeof window._prvResolve==='function'){window._prvResolve(true);window._prvResolve=null;}});
    document.getElementById('appPreviewModal').addEventListener('click',function(e){if(e.target===this){closePreviewModal();if(typeof window._prvResolve==='function'){window._prvResolve(false);window._prvResolve=null;}}});
    </script>
</footer>
