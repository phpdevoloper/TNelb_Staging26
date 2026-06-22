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

    .fs-section-header.fs-section-header--in-grid {
        padding: 4px 0 10px;
        margin-bottom: 0;
        border-radius: 0;
        border: 0;
        background: transparent;
    }
    .fs-section-header.fs-section-header--in-grid .fs-section-title { font-size: .83rem; }
    .fs-section-header.fs-section-header--in-grid .fs-section-tamil { font-size: .74rem; margin-top: 2px; }
    .fs-section-header.fs-section-header--in-grid .fs-section-num {
        width: 24px;
        height: 24px;
        font-size: .7rem;
    }

    /* DOB + Age: badge 5 (matches apply-form-s) */
    .fs-dob-age-badge-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 4px 0 0;
        margin-bottom: 0;
    }
    .fs-dob-age-badge-row > .fs-section-num {
        width: 24px;
        height: 24px;
        font-size: .7rem;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .fs-dob-age-badge-row__body {
        flex: 1 1 0;
        min-width: 0;
    }
    .fs-dob-age-pair.row { align-items: flex-start; }
    .fs-dob-age-pair > [class*="col-"] { align-self: flex-start; }
    .fs-dob-age-label-block {
        min-height: 3.35rem;
        margin-bottom: 2px;
    }
    @media (min-width: 576px) {
        .fs-dob-age-label-block { min-height: 3.5rem; }
    }

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

                    {{-- ═══ SECTIONS 1–3 — Name, Father's Name, Email (same layout as apply-form-s) ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-body">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <div class="fs-section-header fs-section-header--in-grid">
                                        <span class="fs-section-num">1</span>
                                        <div>
                                            <div class="fs-section-title">Name of the applicant <span class="section-req">*</span></div>
                                            <div class="fs-section-tamil">விண்ணப்பதாரர் பெயர்</div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="login_id" id="login_id_store" value="{{ $user['user_id'] }}">
                                    <input autocomplete="off" class="form-control" id="Applicant_Name" name="applicant_name" type="text" value="{{ $user['salutation'].' '.$user['applicant_name'] }}" readonly>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="fs-section-header fs-section-header--in-grid">
                                        <span class="fs-section-num">2</span>
                                        <div>
                                            <div class="fs-section-title">Father's Name <span class="section-req">*</span></div>
                                            <div class="fs-section-tamil">தகப்பனார் பெயர்</div>
                                        </div>
                                    </div>
                                    <input autocomplete="off" class="form-control" id="Fathers_Name" name="fathers_name" type="text" value="{{ isset($application) ? $application->fathers_name : '' }}" maxlength="80">
                                    <span class="error-message text-danger" style="font-size:.78rem;"></span>
                                </div>
                                <div class="col-12 col-md-6 mb-2 mt-1">
                                    <div class="fs-section-header fs-section-header--in-grid">
                                        <span class="fs-section-num">3</span>
                                        <div>
                                            <div class="fs-section-title">Email ID <span class="section-hint">(optional)</span></div>
                                            <div class="fs-section-tamil">மின்னஞ்சல் முகவரி</div>
                                        </div>
                                    </div>
                                    <input autocomplete="email" class="form-control" id="applicant_email" name="applicant_email" type="email" maxlength="191"
                                        value="{{ old('applicant_email', isset($application) ? ($application->applicant_email ?? '') : (Auth::user()->email ?? '')) }}">
                                    <span class="error-message text-danger" style="font-size:.78rem;"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTIONS 4–5 — Address / D.O.B & Age (same layout as apply-form-s) ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-body">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <div class="fs-section-header fs-section-header--in-grid">
                                        <span class="fs-section-num">4</span>
                                        <div>
                                            <div class="fs-section-title">
                                                Address of the applicant <span class="section-req">*</span>
                                                <span class="section-hint">(To be clear)</span>
                                            </div>
                                            <div class="fs-section-tamil">விண்ணப்பதாரர் முகவரி <span style="font-size:.72rem;">(தெளிவாக இருக்க வேண்டும்)</span></div>
                                        </div>
                                    </div>
                                    <textarea rows="3" class="form-control" id="applicants_address" name="applicants_address" maxlength="255">{{Auth::user()->address}}</textarea>
                                    <span id="applicants_address_error" class="text-danger error" style="font-size:.78rem;"></span>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="fs-dob-age-badge-row">
                                        <span class="fs-section-num">5</span>
                                        <div class="fs-dob-age-badge-row__body">
                                            <div class="row fs-dob-age-pair align-items-start">
                                                <div class="col-12 col-sm-6 mb-2 mb-sm-0">
                                                    <div class="fs-dob-age-label-block">
                                                        <div class="fs-field-label">(i) Date of Birth <span class="req">*</span></div>
                                                        <div class="fs-field-tamil">பிறந்த நாள், மாதம், வருடம்</div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6">
                                                    <div class="fs-dob-age-label-block">
                                                        <div class="fs-field-label">(ii) Age <span class="req">*</span></div>
                                                        <div class="fs-field-tamil">வயது</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row fs-dob-age-pair align-items-start mx-0">
                                                <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                                                    <input autocomplete="off" class="form-control" id="d_o_b" name="d_o_b" type="text" placeholder="DD/MM/YYYY" value="{{ isset($application) ? $application->d_o_b : '' }}">
                                                    <span id="dob-error" class="text-danger" style="font-size:.78rem;"></span>
                                                </div>
                                                <div class="col-12 col-sm-6">
                                                    <input autocomplete="off" class="form-control" id="age" name="age" type="number" value="{{ isset($application) ? $application->age : '' }}" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 6 — Technical Qualifications ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">6</span>
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
                                            <td><input autocomplete="off" class="form-control" name="duration[]" type="text" maxlength="8" readonly placeholder="Y.M"></td>
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

                    {{-- ═══ SECTION 7 — Previous Application ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">7</span>
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

                    {{-- ═══ SECTION 8 — Upload Documents ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">8</span>
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

@include('user_login.partials.form-p-preview-modal')

<footer class="main-footer">
    @include('include.footer')
    <script src="{{ url('assets/js/digitization.js') }}"></script>

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
                <td><input type="text" class="form-control" name="duration[]" maxlength="8" readonly placeholder="Y.M"></td>
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

        // Returns the institute attendance duration as a "Y.M" string where the
        // decimal point is just a separator (NOT a math decimal):
        //   2 years exactly       -> "2.0"
        //   1 year 2 months       -> "1.2"
        //   3 years 5 months      -> "3.5"
        //   1 year 11 months      -> "1.11"
        // The day-component is honoured (Feb 15 -> Apr 1 counts as 1 month, not 2).
        function calculateInstituteDurationYears(fromDate, toDate) {
            if (!fromDate || !toDate) return '';
            const from = new Date(fromDate + 'T00:00:00');
            const to = new Date(toDate + 'T00:00:00');
            if (Number.isNaN(from.getTime()) || Number.isNaN(to.getTime()) || to < from) return '';
            let years = to.getFullYear() - from.getFullYear();
            let months = to.getMonth() - from.getMonth();
            if (to.getDate() < from.getDate()) months -= 1;
            if (months < 0) { years -= 1; months += 12; }
            if (years < 0) return '';
            return years + '.' + months;
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
    @include('user_login.partials.form-p-preview-modal-script')
</footer>
