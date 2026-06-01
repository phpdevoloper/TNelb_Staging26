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
    .fs-card-header .header-titles .form-substatus { display: block; font-size: .86rem; font-weight: 600; color: #fff; text-transform: uppercase; letter-spacing: .4px; }
    .fs-card-header .instructions-link { text-align: right; margin-top: 0; margin-bottom: 0; font-size: .82rem; line-height: 1; }
    .fs-card-header .instructions-link a { color: rgba(255,255,255,.9); text-decoration: none; border-bottom: 1px dashed rgba(255,255,255,.5); }
    .fs-card-header .instructions-link a:hover { color: #fff; border-bottom-color: #fff; }

    /* ── Mandatory notice ─────────────────────────────── */
    .fs-mandatory-bar { background: #f8f9ff; border-bottom: 1px solid #e3e8f0; padding: 7px 28px; font-size: .83rem; color: #555; text-align: right; }
    .fs-mandatory-bar .req-dot { color: #d9363e; font-weight: 700; margin-right: 2px; }

    /* ── Returned-application alert ───────────────────── */
    .fs-query-alert { background: #fff8e1; border: 1px solid #f3d896; border-left: 4px solid #e0a800; border-radius: 8px; padding: 14px 18px; margin: 18px 28px 0; }
    .fs-query-alert h6 { margin: 0 0 6px; font-size: .92rem; font-weight: 700; color: #8a6100; }
    .fs-query-alert p { margin: 0 0 6px; font-size: .82rem; color: #5c4400; }
    .fs-query-alert ul { margin: 0; padding-left: 20px; font-size: .82rem; color: #5c4400; }
    .fs-query-alert ul li { margin-bottom: 3px; }

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
    .btn-verify-remove { background: #dc3545; color: #fff; border: none; border-radius: 6px; padding: 7px 16px; font-size: .82rem; font-weight: 600; cursor: pointer; transition: background .2s; white-space: nowrap; }
    .btn-verify-remove:hover { background: #b52a37; color: #fff; }
    .verify_status, #verify_status { font-size: .76rem; display: inline-block; margin-top: 2px; }

    /* ── Tables ───────────────────────────────────────── */
    .fs-table-wrap { overflow-x: auto; border-radius: 6px; border: 1px solid #dde5f3; }
    .fs-form table.table { margin-bottom: 0; font-size: .83rem; }
    .fs-form table.table thead th { background: #eef3fb; color: #1a2a4a; font-weight: 600; font-size: .78rem; padding: .45rem .5rem; vertical-align: middle; border-bottom: 2px solid #d0ddf5; border-color: #d0ddf5; line-height: 1.25; }
    .fs-form table.table tbody td { padding: .45rem .5rem; vertical-align: middle; border-color: #e8edf6; }
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
    #education-table tbody td, #institute-table tbody td, #work-table tbody td { vertical-align: middle; }

    /* ── Existing-document file cell (edit mode) ─────── */
    .fs-doc-existing { display: flex; align-items: center; flex-wrap: wrap; gap: 6px; justify-content: center; }
    .fs-doc-existing a { color: #035ab3; font-size: .8rem; font-weight: 600; text-decoration: none; }
    .fs-doc-existing a:hover { text-decoration: underline; }

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
    .btn-fs-edit { background: #035ab3; color: #fff; border: none; border-radius: 8px; padding: 10px 28px; font-size: .9rem; font-weight: 600; cursor: pointer; transition: background .2s; }
    .btn-fs-edit:hover { background: #024a98; color: #fff; }
    .btn-fs-cancel { background: #fff; color: #dc3545; border: 2px solid #dc3545; border-radius: 8px; padding: 10px 28px; font-size: .9rem; font-weight: 600; cursor: pointer; transition: all .2s; }
    .btn-fs-cancel:hover { background: #fdeaea; }

    /* ── Validation messages ─────────────────────────── */
    .fs-form .text-danger, .fs-form .error-message, .fs-form .error,
    .fs-form span[id$="-error"], .fs-form span[class*="error"], .fs-form #checkboxError { font-size: .78rem !important; line-height: 1.3; display: block; margin-top: 2px; }

    /* ── PDF icon ────────────────────────────────────── */
    .fa-file-pdf-o { color: #d9363e !important; }

    /* ── FontAwesome fix ──────────────────────────────── */
    .comp_certificate .btn .fa, .comp_certificate .btn i.fa,
    .comp_certificate .btn-tbl-add .fa, .comp_certificate .btn-tbl-add i.fa,
    .comp_certificate .btn-tbl-remove .fa, .comp_certificate .btn-tbl-remove i.fa { font-family: 'FontAwesome'; display: inline-block; }

    /* ═══════════════════════════════════════════════════════════════════
       UX ENHANCEMENTS
       ═══════════════════════════════════════════════════════════════════ */

    /* ── Read-only banner ─────────────────────────────── */
    .fs-readonly-banner { display: flex; align-items: center; gap: 14px; background: linear-gradient(135deg, #eef3fb, #f8f9ff); border: 1px solid #d0ddf5; border-left: 4px solid #035ab3; border-radius: 8px; padding: 12px 16px; margin: 18px 28px 0; }
    .fs-readonly-banner .icon { width: 38px; height: 38px; border-radius: 50%; background: #035ab3; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .fs-readonly-banner .body { flex: 1; min-width: 0; }
    .fs-readonly-banner .title { font-size: .9rem; font-weight: 700; color: #1a3a72; margin: 0 0 2px; }
    .fs-readonly-banner .desc { font-size: .78rem; color: #4a5d80; line-height: 1.4; margin: 0; }
    .fs-readonly-banner .desc em { font-style: normal; font-weight: 700; color: #035ab3; }

    /* ── Progress pill ────────────────────────────────── */
    .fs-progress-row { display: flex; justify-content: flex-end; padding: 0 28px; margin-top: 10px; }
    .fs-progress-pill { display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #d0ddf5; border-radius: 20px; padding: 5px 14px; font-size: .78rem; font-weight: 600; color: #035ab3; box-shadow: 0 1px 3px rgba(3,90,179,.08); }
    .fs-progress-pill .progress-track { width: 80px; height: 6px; background: #e3e8f0; border-radius: 3px; overflow: hidden; }
    .fs-progress-pill .progress-fill { height: 100%; background: linear-gradient(90deg, #035ab3, #1a9e4f); transition: width .4s ease; width: 0%; }
    .fs-progress-pill .progress-fill.complete { background: linear-gradient(90deg, #1a9e4f, #15883f); }
    .fs-progress-pill .progress-text { white-space: nowrap; }

    /* ── Query field highlighting ─────────────────────── */
    .fs-section.fs-section-queried { border-color: #f3d896 !important; box-shadow: 0 0 0 3px rgba(224,168,0,.12); }
    .fs-section.fs-section-queried .fs-section-header { background: #fff8e1; border-bottom-color: #f3d896; }
    .fs-section.fs-section-queried .fs-section-num { background: #e0a800; }
    .fs-section.fs-section-queried::before { content: '⚠ Query raised'; display: block; background: #e0a800; color: #fff; font-size: .68rem; font-weight: 700; padding: 3px 12px; border-radius: 8px 8px 0 0; letter-spacing: .5px; text-transform: uppercase; }
    .fs-form .fs-field-queried { border-color: #e0a800 !important; box-shadow: 0 0 0 3px rgba(224,168,0,.15) !important; background: #fffbeb !important; }

    /* ── Sticky action bar ────────────────────────────── */
    .fs-action-bar { position: sticky; bottom: 0; z-index: 50; background: linear-gradient(0deg, rgba(248,250,253,1) 0%, rgba(248,250,253,.95) 70%, rgba(248,250,253,.0) 100%); padding: 16px 0 14px; margin: 0 -18px; transition: box-shadow .2s; backdrop-filter: blur(4px); }
    .fs-action-bar.is-stuck { box-shadow: 0 -3px 14px rgba(3,90,179,.10); border-top: 1px solid #e3e8f0; background: rgba(255,255,255,.98); }
    .fs-action-bar > * { position: relative; z-index: 1; }
    .fs-action-bar-sentinel { height: 1px; }

    /* ── Photo / Signature: hover overlay pattern ─────── */
    .fs-photo-card { border: 1px dashed #b8c8e2; background: #f8fbff; border-radius: 10px; padding: 12px; display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
    .fs-photo-frame { position: relative; border: 2px dashed #b8c8e2; background: #f8fbff; border-radius: 12px; overflow: hidden; cursor: pointer; transition: border-color .2s, transform .2s; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
    .fs-photo-frame:hover { border-color: #035ab3; transform: translateY(-1px); }
    .fs-photo-frame.has-image { border-style: solid; border-color: #035ab3; }
    .fs-photo-frame--photo { width: 110px; height: 132px; }
    .fs-photo-frame--sign { width: 200px; height: 92px; }
    .fs-photo-frame img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .fs-photo-frame--sign img { object-fit: contain; background: #fff; }
    .fs-photo-frame .fs-photo-placeholder { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; color: #89a0c4; font-size: .72rem; padding: 10px; text-align: center; line-height: 1.35; }
    .fs-photo-frame .fs-photo-placeholder i { font-size: 1.6rem; color: #b8c8e2; }
    .fs-photo-frame .fs-photo-overlay { position: absolute; inset: 0; background: rgba(3,90,179,.78); color: #fff; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; opacity: 0; transition: opacity .2s; pointer-events: none; font-size: .8rem; font-weight: 600; }
    .fs-photo-frame .fs-photo-overlay i { font-size: 1.4rem; }
    .fs-photo-frame:hover .fs-photo-overlay { opacity: 1; }
    .fs-photo-meta { flex: 1; min-width: 220px; max-width: 420px; order: 1; text-align: left; }
    .fs-photo-card > .fs-photo-frame { order: 2; margin-left: auto; }
    .fs-photo-meta .file-limit { font-size: .76rem; color: #28a745; display: block; margin-bottom: 2px; }
    .fs-photo-meta .fs-photo-filename { font-size: .76rem; color: #5a7299; display: block; margin-top: 2px; min-height: 1.1rem; }
    .fs-photo-meta .error-message { font-size: .78rem; color: #dc3545; display: block; margin-top: 2px; }
    .fs-photo-card input[type="file"] { display: none !important; visibility: hidden !important; opacity: 0 !important; width: 0 !important; height: 0 !important; }
    #upload_photo, #upload_sign { display: none !important; visibility: hidden !important; opacity: 0 !important; width: 0 !important; height: 0 !important; position: absolute !important; left: -9999px !important; }

    /* ── Verify button spinner state ──────────────────── */
    .btn-verify.is-loading, .btn-verify-remove.is-loading { pointer-events: none; opacity: .8; position: relative; padding-left: 36px; }
    .btn-verify.is-loading::before, .btn-verify-remove.is-loading::before { content: ''; position: absolute; left: 12px; top: 50%; width: 14px; height: 14px; margin-top: -7px; border: 2px solid rgba(255,255,255,.3); border-top-color: #fff; border-radius: 50%; animation: fs-spin .7s linear infinite; }
    @keyframes fs-spin { to { transform: rotate(360deg); } }

    /* ── Toggle panel slide animation ─────────────────── */
    .fs-toggle-panel { overflow: hidden; transition: max-height .3s ease, opacity .3s ease, padding .3s ease, margin .3s ease; }
    .fs-toggle-panel.collapsing-out { max-height: 0 !important; opacity: 0; padding-top: 0 !important; padding-bottom: 0 !important; margin-top: 0 !important; }

    /* ── Inline file validation ───────────────────────── */
    .file-limit.is-error { color: #dc3545 !important; font-weight: 600; }
    .file-limit.is-success { color: #1a9e4f !important; font-weight: 600; }
    .form-control.file-invalid { border-color: #dc3545 !important; box-shadow: 0 0 0 3px rgba(220,53,69,.12) !important; }

    /* ── Table horizontal scroll cue ──────────────────── */
    .fs-table-wrap { position: relative; }
    .fs-table-wrap.has-scroll::after { content: '⟶ scroll'; position: absolute; right: 8px; top: 8px; background: rgba(3,90,179,.85); color: #fff; font-size: .68rem; padding: 2px 8px; border-radius: 10px; pointer-events: none; opacity: 0; transition: opacity .3s; }
    .fs-table-wrap.has-scroll:hover::after { opacity: 1; }

    /* ── Mobile polish ────────────────────────────────── */
    @media (max-width: 575.98px) {
        .fs-form-body { padding: 18px 14px 24px; }
        .fs-card-header { padding: 10px 14px 6px; }
        .fs-card-header .header-titles h5 { font-size: .92rem; }
        .fs-card-header .header-titles h5.tamil-title { font-size: .82rem; }
        .fs-mandatory-bar { padding: 7px 14px; }
        .fs-readonly-banner, .fs-query-alert { margin-left: 14px; margin-right: 14px; }
        .fs-progress-row { padding: 0 14px; justify-content: flex-start; }
        .fs-section-body { padding: 14px 12px; }
        .fs-action-bar { flex-direction: column; padding: 12px 4px 10px; margin: 0 -12px; }
        .fs-action-bar .btn-fs-edit, .fs-action-bar .btn-fs-cancel, .fs-action-bar .btn-fs-draft, .fs-action-bar .btn-fs-submit { width: 100%; }
        #actionButtonsWrap { flex-direction: column; gap: 10px !important; width: 100%; }
        #actionButtonsWrap .btn-fs-cancel, #actionButtonsWrap .btn-fs-submit { width: 100%; }
        .fs-declaration { padding: 12px 14px; }
        .fs-photo-card { gap: 12px; flex-wrap: wrap; justify-content: space-between; }
        .fs-photo-frame--photo { width: 92px; height: 110px; }
        .fs-photo-frame--sign { width: 160px; height: 76px; }
    }
</style>

@php
    $isRenewFormP   = $isRenewFormP ?? false;
    $isReturned     = !$isRenewFormP && isset($application_details->app_status) && $application_details->app_status === 'QU';
    $hasPreviousApplication = !empty($application_details->previously_number);

    $decryptedaadhar = !empty($application_details->aadhaar)
        ? Crypt::decryptString($application_details->aadhaar)
        : null;

    $signaturePath  = $applicant_sign?->uploaded_doc ?? null;
    $signatureSrc   = !empty($signaturePath) ? url($signaturePath) : '';
@endphp

{{-- ░░ BREADCRUMB ░░ --}}
<div class="fs-breadcrumb-bar">
    <div class="container">
        <ul id="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><span class="fa fa-home"></span> Dashboard</a></li>
            <li>
                <a href="#"><span class="fa fa-info-circle"></span>
                    @if($isRenewFormP)
                        Application for Renewal – Form P
                    @elseif($isReturned)
                        Correct and resubmit – Form P
                    @else
                        Form P
                    @endif
                </a>
            </li>
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
                    <h5>Renewal Application for Power Generating Station Operation &amp; Maintenance Competency Certificate</h5>
                    <h5 class="tamil-title">மின்சார உற்பத்தி நிலையத்தின் செயல்பாடு மற்றும் பராமரிப்பு திறன் சான்றிதழ் புதுப்பித்தல் விண்ணப்பம்</h5>
                    <span class="form-badge">FORM - P / Certificate P</span>
                    <span class="form-substatus">{{ $isRenewFormP ? 'Renewal' : ($isReturned ? 'Correct and resubmit' : 'Draft') }}</span>
                </div>
                <div class="instructions-link">
                    <span class="text-white font-weight-bold" style="font-size:.82rem;">Instructions &nbsp;</span>
                    <a href="{{ url('assets/pdf/form_p_notes.pdf') }}" target="_blank">English <i class="fa fa-file-pdf-o"></i> (8 KB)</a>
                </div>
            </div>

            {{-- ── Mandatory notice ── --}}
            <div class="fs-mandatory-bar">
                <span class="req-dot">*</span> Fields are Mandatory
            </div>

            {{-- ── Read-only banner (returned applications) ── --}}
            @if($isReturned)
                <div class="fs-readonly-banner" id="readonlyBanner">
                    <div class="icon"><i class="fa fa-eye"></i></div>
                    <div class="body">
                        <p class="title">View Mode</p>
                        <p class="desc">Click <em>Edit</em> below to make changes. Fields with raised queries are highlighted in <span style="color:#e0a800;font-weight:700;">amber</span>.</p>
                    </div>
                </div>
            @endif

            {{-- ── Progress pill ── --}}
            <div class="fs-progress-row">
                <div class="fs-progress-pill" id="formProgressPill">
                    <i class="fa fa-list-alt"></i>
                    <div class="progress-track"><div class="progress-fill" id="formProgressFill"></div></div>
                    <span class="progress-text" id="formProgressText">0 of 0 sections</span>
                </div>
            </div>

            {{-- ── Form body ── --}}
            <div class="fs-form-body fs-form apply-card">
                <form id="competency_form_p" enctype="multipart/form-data">

                    {{-- ═══ SECTION 1 & 2 — Name & Father's Name ═══ --}}
                    <div class="fs-section" data-section-key="personal" data-query-keywords="name|father|applicant name|father's name|fathers name">
                        <div class="fs-section-body">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <div class="fs-field-label">1. Name of the applicant <span class="req">*</span></div>
                                    <div class="fs-field-tamil">விண்ணப்பதாரர் பெயர்</div>
                                    <input autocomplete="off" class="form-control" id="Applicant_Name" name="applicant_name" type="text" value="{{ isset($application_details) ? $application_details->applicant_name : Auth::user()->name }}" readonly>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="fs-field-label">2. Father's Name <span class="req">*</span></div>
                                    <div class="fs-field-tamil">தகப்பனார் பெயர்</div>
                                    <input autocomplete="off" class="form-control" id="Fathers_Name" name="fathers_name" type="text" value="{{ isset($application_details) ? $application_details->fathers_name : '' }}" maxlength="80">
                                    <span class="error-message text-danger"></span>
                                </div>
                                @php
                                    $emailValFormP = '';
                                    if (!empty($application_details->applicant_email)) {
                                        $emailValFormP = $application_details->applicant_email;
                                    } elseif (!empty(Auth::user()->email)) {
                                        $emailValFormP = Auth::user()->email;
                                    }
                                @endphp
                                <div class="col-12 col-md-6 mt-1">
                                    <div class="fs-field-label">Email ID</div>
                                    <div class="fs-field-tamil">மின்னஞ்சல் முகவரி</div>
                                    <input autocomplete="email" class="form-control" id="applicant_email" name="applicant_email" type="email" maxlength="191" value="{{ old('applicant_email', $emailValFormP) }}">
                                    <span class="error-message text-danger"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 3 & 4 — Address / DOB / Age ═══ --}}
                    <div class="fs-section" data-section-key="contact" data-query-keywords="address|dob|date of birth|age|பிறந்த">
                        <div class="fs-section-body">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <div class="fs-field-label">3. Address of the applicant <span class="req">*</span> <span style="font-weight:400;font-size:.78rem;">(To be clear)</span></div>
                                    <div class="fs-field-tamil">விண்ணப்பதாரர் முகவரி <span style="font-size:.72rem;">(தெளிவாக இருக்க வேண்டும்)</span></div>
                                    <textarea rows="3" class="form-control" id="applicants_address" name="applicants_address" maxlength="250">{{ isset($application_details) ? $application_details->applicants_address : Auth::user()->address }}</textarea>
                                    <span id="applicants_address_error" class="text-danger error"></span>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="row">
                                        <div class="col-12 col-sm-7 mb-3 mb-sm-0">
                                            <div class="fs-field-label">4. (i) Date of Birth <span class="req">*</span></div>
                                            <div class="fs-field-tamil">பிறந்த நாள், மாதம், வருடம்</div>
                                            <input autocomplete="off" class="form-control" id="d_o_b" name="d_o_b" type="text" placeholder="DD/MM/YYYY" value="{{ $application_details->d_o_b ?? '' }}">
                                            <span id="dob-error" class="text-danger d-block mt-1" style="display:none;"></span>
                                        </div>
                                        <div class="col-12 col-sm-5">
                                            <div class="fs-field-label">4. (ii) Age <span class="req">*</span></div>
                                            <div class="fs-field-tamil">வயது</div>
                                            <input autocomplete="off" class="form-control" id="age" name="age" type="number" value="{{ isset($application_details) ? $application_details->age : '' }}" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 5 — Technical Qualifications ═══ --}}
                    <div class="fs-section" data-section-key="qualifications" data-query-keywords="education|qualification|institute|training|power station|work|experience|employer|technical">
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
                                            <th>S.No</th>
                                            <th>Education Level</th>
                                            <th>Institution/School Name</th>
                                            <th>Month &amp; Year of Passing</th>
                                            <th>Certificate No</th>
                                            <th class="text-center">Upload Document<br><span class="file-limit">File type: PDF, PNG (Max 200 KB)</span></th>
                                            <th class="text-center p-1">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-add add-more-education py-1 px-2" title="Add row"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="education-container">
                                        @if ($edu_details->isNotEmpty())
                                            @foreach ($edu_details as $edu)
                                                <tr class="education-fields text-center">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <select class="form-control" name="educational_level[]">
                                                            <option disabled {{ empty($edu->educational_level) ? 'selected' : '' }}>Select Education</option>
                                                            <option value="BEM" {{ $edu->educational_level == 'BEM' ? 'selected' : '' }}>B.E(Mechanical)</option>
                                                            <option value="BEE" {{ $edu->educational_level == 'BEE' ? 'selected' : '' }}>B.E(Electrical)</option>
                                                            <option value="DME" {{ $edu->educational_level == 'DME' ? 'selected' : '' }}>Diploma(Mechanical)</option>
                                                            <option value="DEE" {{ $edu->educational_level == 'DEE' ? 'selected' : '' }}>Diploma(Electrical)</option>
                                                        </select>
                                                    </td>
                                                    <td><input type="text" class="form-control" name="institute_name[]" value="{{ $edu->institute_name ?? '' }}"></td>
                                                    <td>
                                                        <div style="display:flex;gap:4px;">
                                                            <select name="month_of_passing[]" class="form-control" style="flex:1;min-width:0;">
                                                                <option value="">Month</option>
                                                                <option value="01" {{ ($edu->month_passing ?? '') == '01' ? 'selected' : '' }}>Jan</option>
                                                                <option value="02" {{ ($edu->month_passing ?? '') == '02' ? 'selected' : '' }}>Feb</option>
                                                                <option value="03" {{ ($edu->month_passing ?? '') == '03' ? 'selected' : '' }}>Mar</option>
                                                                <option value="04" {{ ($edu->month_passing ?? '') == '04' ? 'selected' : '' }}>Apr</option>
                                                                <option value="05" {{ ($edu->month_passing ?? '') == '05' ? 'selected' : '' }}>May</option>
                                                                <option value="06" {{ ($edu->month_passing ?? '') == '06' ? 'selected' : '' }}>Jun</option>
                                                                <option value="07" {{ ($edu->month_passing ?? '') == '07' ? 'selected' : '' }}>Jul</option>
                                                                <option value="08" {{ ($edu->month_passing ?? '') == '08' ? 'selected' : '' }}>Aug</option>
                                                                <option value="09" {{ ($edu->month_passing ?? '') == '09' ? 'selected' : '' }}>Sep</option>
                                                                <option value="10" {{ ($edu->month_passing ?? '') == '10' ? 'selected' : '' }}>Oct</option>
                                                                <option value="11" {{ ($edu->month_passing ?? '') == '11' ? 'selected' : '' }}>Nov</option>
                                                                <option value="12" {{ ($edu->month_passing ?? '') == '12' ? 'selected' : '' }}>Dec</option>
                                                            </select>
                                                            <select name="year_of_passing[]" class="form-control" style="flex:1;min-width:0;">
                                                                <option value="0" disabled {{ empty($edu->year_of_passing) ? 'selected' : '' }}>Select Year</option>
                                                                @php $currentYear = date('Y'); @endphp
                                                                @for ($year = $currentYear; $year >= 1980; $year--)
                                                                    <option value="{{ $year }}" {{ $edu->year_of_passing == $year ? 'selected' : '' }}>{{ $year }}</option>
                                                                @endfor
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control certificate-input" name="certificate_no[]" maxlength="20" value="{{ $edu->certificate_no ?? '' }}" placeholder="Certificate No">
                                                    </td>
                                                    <td>
                                                        @if (!empty($edu->upload_document))
                                                            <div class="fs-doc-existing">
                                                                <a href="{{ asset($edu->upload_document) }}" target="_blank">
                                                                    <i class="fa fa-file-pdf-o"></i> View
                                                                </a>
                                                                <button type="button" class="btn-tbl-remove remove-doc_edu py-1 px-2">Remove</button>
                                                            </div>
                                                        @else
                                                            <input type="file" class="form-control" name="education_document[]" accept=".pdf,application/pdf">
                                                        @endif
                                                    </td>
                                                    <td class="text-center p-1">
                                                        <div class="form-s-actions-stack">
                                                            <button type="button" class="btn-tbl-remove remove-education remove_edu py-1 px-2" data-edu_id="{{ $edu->id }}" data-url="{{ route('delete_education') }}" title="Remove row">
                                                                <i class="fa fa-trash-o"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <input type="hidden" name="edu_id[]" value="{{ $edu->id }}">
                                                    <input type="hidden" name="existing_document[]" value="{{ $edu->upload_document }}">
                                                    <input type="hidden" name="removed_document[]" value="0" class="removed-document-edu">
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="education-fields text-center">
                                                <td>1</td>
                                                <td>
                                                    <select class="form-control" name="educational_level[]">
                                                        <option selected disabled>Select Education</option>
                                                        <option value="BEM">B.E(Mechanical)</option>
                                                        <option value="BEE">B.E(Electrical)</option>
                                                        <option value="DME">Diploma(Mechanical)</option>
                                                        <option value="DEE">Diploma(Electrical)</option>
                                                    </select>
                                                </td>
                                                <td><input type="text" class="form-control" name="institute_name[]"></td>
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
                                                            <option value="0">Select Year</option>
                                                            @php $currentYear = date('Y'); @endphp
                                                            @for ($year = $currentYear; $year >= 1980; $year--)
                                                                <option value="{{ $year }}">{{ $year }}</option>
                                                            @endfor
                                                        </select>
                                                    </div>
                                                </td>
                                                <td>
                                                    <input type="text" class="form-control certificate-input" name="certificate_no[]" maxlength="20" placeholder="Certificate No">
                                                </td>
                                                <td><input type="file" class="form-control" name="education_document[]" accept=".pdf,application/pdf"></td>
                                                <td class="text-center p-1">
                                                    <div class="form-s-actions-stack">
                                                        <button type="button" class="btn-tbl-remove remove-education py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button>
                                                    </div>
                                                </td>
                                                <input type="hidden" name="edu_id[]" value="">
                                                <input type="hidden" name="existing_document[]" value="">
                                                <input type="hidden" name="removed_document[]" value="0" class="removed-document-edu">
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            {{-- (ii) Institute table --}}
                            <div class="fs-field-label mb-2">(ii) Institute in which the applicant has undergone the training and the period <span class="req">*</span> <span style="font-weight:400;font-size:.78rem;">(Upload the documents)</span></div>
                            <div class="fs-field-tamil mb-2">விண்ணப்பதாரர் பயிற்சி பெற்ற நிறுவனம் மற்றும் பயிற்சி பெற்ற காலம் <span style="font-size:.72rem;">(ஆவணங்களை பதிவேற்ற வேண்டும்)</span></div>
                            <div class="fs-table-wrap mb-4">
                                <table class="table table-bordered" id="institute-table">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
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
                                        @if ($institutes->isNotEmpty())
                                            @foreach ($institutes as $institute)
                                                <tr class="institute-fields text-center">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <textarea autocomplete="off" class="form-control" name="institute_name_address[]" cols="5" rows="3" maxlength="255">{{ $institute->institute_name_address ?? '' }}</textarea>
                                                    </td>
                                                    <td>
                                                        <input autocomplete="off" class="form-control" name="from_date[]" type="date" value="{{ $institute->from_date ?? '' }}">
                                                    </td>
                                                    <td>
                                                        <input autocomplete="off" class="form-control" name="to_date[]" type="date" value="{{ $institute->to_date ?? '' }}">
                                                    </td>
                                                    <td>
                                                        <input autocomplete="off" class="form-control" name="duration[]" type="text" value="{{ $institute->duration ?? '' }}" readonly>
                                                    </td>
                                                    <td>
                                                        @if (!empty($institute->upload_doc))
                                                            <div class="fs-doc-existing">
                                                                <a href="{{ asset($institute->upload_doc) }}" target="_blank">
                                                                    <i class="fa fa-file-pdf-o"></i> View
                                                                </a>
                                                                <button type="button" class="btn-tbl-remove remove-inst py-1 px-2">Remove</button>
                                                            </div>
                                                        @else
                                                            <input class="form-control" name="institute_document[]" type="file" accept=".pdf,application/pdf">
                                                        @endif
                                                    </td>
                                                    <td class="text-center p-1">
                                                        <div class="form-s-actions-stack">
                                                            <button type="button" class="btn-tbl-remove remove-institute remove-inst-row py-1 px-2" data-inst_id="{{ $institute->id }}" data-url="{{ route('delete_institute') }}" title="Remove row">
                                                                <i class="fa fa-trash-o"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <input type="hidden" name="institute_id[]" value="{{ $institute->id ?? '' }}">
                                                    <input type="hidden" name="exist_institute_document[]" value="{{ $institute->upload_doc ?? '' }}">
                                                    <input type="hidden" name="removed_document_inst[]" value="0" class="removed-document-inst">
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="institute-fields text-center">
                                                <td>1</td>
                                                <td><textarea autocomplete="off" class="form-control" name="institute_name_address[]" cols="5" rows="3" maxlength="255"></textarea></td>
                                                <td><input autocomplete="off" class="form-control" name="from_date[]" type="date"></td>
                                                <td><input autocomplete="off" class="form-control" name="to_date[]" type="date"></td>
                                                <td><input autocomplete="off" class="form-control" name="duration[]" type="text" readonly></td>
                                                <td><input class="form-control" name="institute_document[]" type="file" accept=".pdf,application/pdf"></td>
                                                <td class="text-center p-1">
                                                    <div class="form-s-actions-stack">
                                                        <button type="button" class="btn-tbl-remove remove-empty_institute py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button>
                                                    </div>
                                                </td>
                                                <input type="hidden" name="institute_id[]">
                                                <input type="hidden" name="exist_institute_document[]">
                                                <input type="hidden" name="removed_document_inst[]" value="0" class="removed-document-inst">
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            {{-- (iii) Power Station table --}}
                            <div class="fs-field-label mb-2">(iii) Power Station to which he is attached at present <span style="font-weight:400;font-size:.78rem;">(Upload the documents)</span></div>
                            <div class="fs-field-tamil mb-2">தற்போது பணியாற்றும் மின்சார நிலையம் மற்றும் பயிற்சி பெற்ற காலம் <span style="font-size:.72rem;">(ஆவணங்களை பதிவேற்ற வேண்டும்)</span></div>
                            <div class="fs-table-wrap mb-4">
                                <table class="table table-bordered" id="work-table">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Power Station</th>
                                            <th>
                                                <div>Year of Experience</div>
                                                <div class="d-flex justify-content-between" style="gap:6px;font-size:.72rem;font-weight:400;">
                                                    <span>From (date)</span><span>To (date)</span><span>Total yrs</span>
                                                </div>
                                            </th>
                                            <th>Designation</th>
                                            <th class="text-center">Upload Document<br><span class="file-limit">File type: PDF, PNG (Max 200 KB)</span></th>
                                            <th class="text-center p-1">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-add add-more-work py-1 px-2" title="Add row"><i class="fa fa-plus"></i></button>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="work-container">
                                        @if ($exp_details->isNotEmpty())
                                            @foreach ($exp_details as $exp)
                                                @php
                                                    $expFromDate = !empty($exp->from_date) ? \Carbon\Carbon::parse($exp->from_date)->format('Y-m-d') : '';
                                                    $expToDate = !empty($exp->to_date) ? \Carbon\Carbon::parse($exp->to_date)->format('Y-m-d') : '';
                                                    $expTotal = $exp->total_exp ?? $exp->experience ?? '';
                                                @endphp
                                                <tr class="work-fields text-center">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        <input autocomplete="off" class="form-control" name="work_level[]" type="text" value="{{ $exp->company_name ?? $exp->emp_cate ?? '' }}">
                                                    </td>
                                                    <td>
                                                        <div class="d-flex" style="gap:6px;">
                                                            <input type="date" class="form-control work-date-from" name="work_date_from[]" value="{{ $expFromDate }}">
                                                            <input type="date" class="form-control work-date-to" name="work_date_to[]" value="{{ $expToDate }}">
                                                            <input type="text" class="form-control work-year-total-display" placeholder="—" readonly tabindex="-1" value="{{ $expTotal }}">
                                                        </div>
                                                        <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]" value="{{ $expTotal }}">
                                                        <input type="hidden" name="experience[]" class="experience-sync" value="{{ $exp->experience ?? $exp->total_exp ?? '' }}">
                                                    </td>
                                                    <td>
                                                        <input autocomplete="off" class="form-control" name="designation[]" type="text" value="{{ $exp->designation ?? '' }}">
                                                    </td>
                                                    <td>
                                                        @if (!empty($exp->upload_document))
                                                            <div class="fs-doc-existing">
                                                                <a href="{{ asset($exp->upload_document) }}" target="_blank">
                                                                    <i class="fa fa-file-pdf-o"></i> View
                                                                </a>
                                                                <button type="button" class="btn-tbl-remove remove-doc_work py-1 px-2">Remove</button>
                                                            </div>
                                                        @else
                                                            <input class="form-control" name="work_document[]" type="file" accept=".pdf,application/pdf">
                                                        @endif
                                                    </td>
                                                    <td class="text-center p-1">
                                                        <div class="form-s-actions-stack">
                                                            <button type="button" class="btn-tbl-remove remove-work remove_exp py-1 px-2" data-exp_id="{{ $exp->id }}" data-url="{{ route('delete_experience') }}" title="Remove row">
                                                                <i class="fa fa-trash-o"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                    <input type="hidden" name="work_id[]" value="{{ $exp->id ?? '' }}">
                                                    <input type="hidden" name="existing_work_document[]" value="{{ $exp->upload_document ?? '' }}">
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="work-fields text-center">
                                                <td>1</td>
                                                <td><input autocomplete="off" class="form-control" name="work_level[]" type="text"></td>
                                                <td>
                                                    <div class="d-flex" style="gap:6px;">
                                                        <input type="date" class="form-control work-date-from" name="work_date_from[]">
                                                        <input type="date" class="form-control work-date-to" name="work_date_to[]">
                                                        <input type="text" class="form-control work-year-total-display" placeholder="—" readonly tabindex="-1">
                                                    </div>
                                                    <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]">
                                                    <input type="hidden" name="experience[]" class="experience-sync">
                                                </td>
                                                <td><input autocomplete="off" class="form-control" name="designation[]" type="text"></td>
                                                <td><input class="form-control" name="work_document[]" type="file" accept=".pdf,application/pdf"></td>
                                                <td class="text-center p-1">
                                                    <div class="form-s-actions-stack">
                                                        <button type="button" class="btn-tbl-remove remove-work py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button>
                                                    </div>
                                                </td>
                                                <input type="hidden" name="work_id[]">
                                                <input type="hidden" name="existing_work_document[]">
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div id="work-exp-validation-msg" class="text-danger small mt-1" style="display:none;"></div>

                            {{-- (iv) Employer name --}}
                            <div class="row align-items-start">
                                <div class="col-12 col-md-3">
                                    <div class="fs-field-label">(iv) Name of the employer</div>
                                    <div class="fs-field-tamil">தொழில் வழங்குநரின் பெயர்</div>
                                </div>
                                <div class="col-12 col-md-9">
                                    <textarea class="form-control" name="employer_name" id="employer_name" cols="5" rows="3" maxlength="255">{{ $application_details->employer_detail ?? '' }}</textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ═══ SECTION 7 — Previous Application (Form P) ═══ --}}
                    <div class="fs-section" data-section-key="prev_application" data-query-keywords="previously applied|previous application|reference no|previously_number">
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
                                    <input class="form-check-input toggle-details" type="radio" name="previous_license" id="previous_license_yes" data-target="#previously_details" value="yes" {{ $hasPreviousApplication ? 'checked' : '' }}>
                                    <label class="form-check-label" for="previous_license_yes">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input toggle-details" type="radio" name="previous_license" id="previous_license_no" data-target="#previously_details" value="no" {{ !$hasPreviousApplication ? 'checked' : '' }}>
                                    <label class="form-check-label" for="previous_license_no">No</label>
                                </div>
                            </div>
                            <div id="previously_details" class="fs-toggle-panel" style="display: {{ $hasPreviousApplication ? 'block' : 'none' }};">
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-4">
                                        <div class="fs-field-label">Application Number <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control" id="previously_number" name="previously_number" type="text" placeholder="Application Number" maxlength="80" value="{{ $application_details->previously_number ?? '' }}">
                                        <span id="licenseError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="fs-field-label">Date <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-date" id="previously_date" name="previously_date" type="date" data-error="#dateError" value="{{ $application_details->previously_date ?? '' }}">
                                        <span id="dateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 8 — Upload Documents (Form P) ═══ --}}
                    <div class="fs-section" data-section-key="uploads" data-query-keywords="photo|passport|signature|aadhaar|aadhar|ஆதார்|sign|upload">
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
                                    {{-- (i) Photo --}}
                                    <tr>
                                        <td class="doc-serial">(i)</td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">Upload Passport Size Photo <span class="req">*</span></div>
                                            <div class="fs-field-tamil">பாஸ்போர்ட் அளவு புகைப்படம் பதிவேற்ற</div>
                                        </td>
                                        <td colspan="3">
                                            <div class="fs-photo-card">
                                                <label class="fs-photo-frame fs-photo-frame--photo {{ !empty($applicant_photo->upload_path) ? 'has-image' : '' }}" for="upload_photo" id="photo-input-wrapper" title="Click to {{ !empty($applicant_photo->upload_path) ? 'change' : 'upload' }} photo">
                                                    @if (!empty($applicant_photo->upload_path))
                                                        <img src="{{ url($applicant_photo->upload_path) }}" id="preview_applicant" alt="Applicant Photo">
                                                    @else
                                                        <img id="preview_applicant" src="" alt="Photo preview" style="display:none;">
                                                        <div class="fs-photo-placeholder" id="photo_placeholder">
                                                            <i class="fa fa-camera"></i>
                                                            <span>Click to upload<br>photo</span>
                                                        </div>
                                                    @endif
                                                    <div class="fs-photo-overlay">
                                                        <i class="fa fa-camera"></i>
                                                        <span>Change Photo</span>
                                                    </div>
                                                </label>
                                                <input autocomplete="off" id="upload_photo" name="upload_photo" type="file" accept="image/*" class="d-none" style="display:none !important;">
                                                <div class="fs-photo-meta">
                                                    <span class="file-limit" id="upload_photo_limit">File type: JPG, PNG (Max 50 KB)</span>
                                                    <span class="fs-photo-filename" id="upload_photo_name">{{ !empty($applicant_photo->upload_path) ? basename($applicant_photo->upload_path) : 'No file selected' }}</span>
                                                    <span class="error-message text-danger"></span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    {{-- (ii) Aadhaar Number / (iii) Aadhaar Document --}}
                                    <tr>
                                        <td class="doc-serial">(ii)</td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">Aadhaar Number <span class="req">*</span></div>
                                            <div class="fs-field-tamil">ஆதார் எண்</div>
                                        </td>
                                        <td style="min-width:180px;">
                                            <input type="text" class="form-control" name="aadhaar" id="aadhaar" maxlength="14" value="{{ $decryptedaadhar }}" style="max-width:260px;">
                                            <span id="aadhaar-error" class="text-danger"></span>
                                        </td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">(iii) Upload Aadhaar Document</div>
                                            <div class="fs-field-tamil">ஆதார் ஆவணத்தை பதிவேற்றவும்</div>
                                        </td>
                                        <td style="min-width:200px;">
                                            @if (!empty($application_details->aadhaar_doc))
                                                <div class="aadhaar-doc-container fs-doc-existing" style="justify-content:flex-start;">
                                                    <a href="{{ route('document.show', ['type' => 'aadhaar', 'filename' => $application_details->aadhaar_doc]) }}" target="_blank">
                                                        <i class="fa fa-file-pdf-o"></i> View
                                                    </a>
                                                    <button type="button" class="btn-tbl-remove remove-docs py-1 px-2">Remove</button>
                                                </div>
                                            @else
                                                <div class="aadhaar-doc-input">
                                                    <div class="form-s-file-upload-wrap" style="max-width:280px;">
                                                        <input autocomplete="off" class="form-control" id="aadhaar_doc" name="aadhaar_doc" type="file" accept=".pdf,application/pdf">
                                                    </div>
                                                    <span class="file-limit">File type: PDF (Max 250 KB)</span>
                                                    <small class="text-danger file-error"></small>
                                                </div>
                                            @endif
                                            <input type="hidden" name="aadhaar_doc_removed" id="aadhaar_doc_removed" value="0">
                                        </td>
                                    </tr>
                                    {{-- (iv) PAN Number / (v) PAN Document --}}
                                    @php
                                        $decryptedPanEdit = '';
                                        if (!empty($application_details->pancard)) {
                                            try {
                                                $decryptedPanEdit = \Illuminate\Support\Facades\Crypt::decryptString($application_details->pancard);
                                            } catch (\Throwable $e) {
                                                $decryptedPanEdit = '';
                                            }
                                        }
                                        $existingPanDocFormP = $application_details->pan_doc ?? $application_details->pancard_doc ?? null;
                                    @endphp
                                    <tr>
                                        <td class="doc-serial">(iv)</td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">PAN Card Number</div>
                                            <div class="fs-field-tamil">நிரந்தர கணக்கு எண்</div>
                                        </td>
                                        <td style="min-width:180px;">
                                            <input type="text" class="form-control text-uppercase" name="pancard" id="pancard" maxlength="10" autocomplete="off" value="{{ $decryptedPanEdit }}" style="max-width:260px;" placeholder="e.g. ABCDE1234F">
                                            <span id="pancard-error" class="text-danger d-block"></span>
                                        </td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">(v) Upload PAN Card Document</div>
                                            <div class="fs-field-tamil">பான் கார்டு ஆவணத்தைப் பதிவேற்றவும்</div>
                                        </td>
                                        <td style="min-width:200px;">
                                            @if (!empty($existingPanDocFormP))
                                                <div class="pan-doc-container fs-doc-existing" style="justify-content:flex-start;">
                                                    <a href="{{ route('document.show', ['type' => 'pan', 'filename' => $existingPanDocFormP]) }}" target="_blank">
                                                        <i class="fa fa-file-pdf-o"></i> View
                                                    </a>
                                                    <button type="button" class="btn-tbl-remove remove-pan-doc py-1 px-2">Remove</button>
                                                </div>
                                            @else
                                                <div class="pancard-doc-input">
                                                    <div class="form-s-file-upload-wrap" style="max-width:280px;">
                                                        <input autocomplete="off" class="form-control" id="pancard_doc" name="pancard_doc" type="file" accept=".pdf,application/pdf">
                                                    </div>
                                                    <span class="file-limit">File type: PDF (Max 250 KB)</span>
                                                    <small class="text-danger file-error"></small>
                                                </div>
                                            @endif
                                            <input type="hidden" name="pancard_doc_removed" id="pancard_doc_removed" value="0">
                                        </td>
                                    </tr>
                                    {{-- (vi) Signature --}}
                                    <tr>
                                        <td class="doc-serial">(vi)</td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">Upload Signature <span class="req">*</span></div>
                                            <div class="fs-field-tamil">கையொப்பத்தைப் பதிவேற்றவும்</div>
                                        </td>
                                        <td colspan="3">
                                            <div class="fs-photo-card">
                                                <label class="fs-photo-frame fs-photo-frame--sign {{ !empty($signaturePath) ? 'has-image' : '' }}" for="upload_sign" id="sign-input-wrapper" title="Click to {{ !empty($signaturePath) ? 'change' : 'upload' }} signature">
                                                    @if (!empty($signaturePath))
                                                        <img src="{{ $signatureSrc }}" id="preview_signature" alt="Uploaded Signature">
                                                    @else
                                                        <img id="preview_signature" src="" alt="Signature preview" style="display:none;">
                                                        <div class="fs-photo-placeholder" id="sign_placeholder">
                                                            <i class="fa fa-pencil"></i>
                                                            <span>Click to upload signature</span>
                                                        </div>
                                                    @endif
                                                    <div class="fs-photo-overlay">
                                                        <i class="fa fa-pencil"></i>
                                                        <span>Change Signature</span>
                                                    </div>
                                                </label>
                                                <input autocomplete="off" id="upload_sign" name="upload_sign" type="file" accept=".jpg,.jpeg,.png" class="d-none" style="display:none !important;" @if(empty($signaturePath)) required @endif>
                                                <div class="fs-photo-meta">
                                                    <span class="file-limit" id="upload_sign_limit">File type: JPG, PNG (Max 50 KB)</span>
                                                    <span class="fs-photo-filename" id="upload_sign_name">{{ !empty($signaturePath) ? basename($signaturePath) : 'No file selected' }}</span>
                                                    <span class="error-message text-danger"></span>
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
                            <input type="checkbox" id="declarationCheckbox" required>
                            <span class="checkmark"></span>
                            <div class="decl-text">
                                <span>I hereby declare that the particulars stated above are correct and true to the best of my knowledge.</span><br>
                                <span>I request that I may be granted a Power Generating Station Operation and Maintenance Competency Certificate.</span><br>
                                <span class="tamil">என் அறிவுக்கு எட்டியவரை மேலே குறிப்பிட்டுள்ள விவரங்கள் யாவும் சரியானவை எனவும் உண்மையானவை எனவும் உறுதி கூறுகிறேன். <br>மின்சாரம் உற்பத்தி நிலையத்தின் செயல்பாடு மற்றும் பராமரிப்பு திறன் சான்றிதழை எனக்கு வழங்குமாறு நான் கேட்டுக்கொள்கிறேன்.</span>
                            </div>
                        </label>
                        <span id="checkboxError" class="text-danger mt-2 d-block" style="display:none!important;font-size:.82rem;">Please check the declaration box before proceeding.</span>
                    </div>

                    {{-- Hidden fields --}}
                    @csrf
                    <input type="hidden" class="form-control text-box single-line" id="login_id_store" name="login_id" value="{{ Auth::user()->login_id }}">
                    <input type="hidden" id="application_id" name="application_id" value="{{ $applicationid ?? ($application_details->application_id ?? '') }}">
                    <input type="hidden" id="old_application" name="old_application" value="{{ $old_application_id ?? ($application_details->old_application ?? '') }}">
                    <input type="hidden" id="license_number" name="license_number" value="{{ $license_details->license_number ?? '' }}">
                    <input type="hidden" id="form_name" name="form_name" value="P">
                    <input type="hidden" id="license_name" name="license_name" value="{{ $application_details->license_name ?? 'P' }}">
                    <input type="hidden" id="form_id" name="form_id" value="{{ $application_details->form_id ?? '' }}">
                    <input type="hidden" id="appl_type" name="appl_type" value="{{ $isRenewFormP ? 'R' : ($application_details->appl_type ?? 'N') }}">
                    @if($isRenewFormP)
                        <input type="hidden" id="amount" name="amount" value="750">
                    @endif

                    {{-- ── Action buttons ── --}}
                    <div class="fs-action-bar">
                        @if($isReturned)
                            <button type="button" class="btn-fs-edit" id="editBtn">Edit</button>
                            <span id="actionButtonsWrap" style="display:none; gap:12px;" class="d-inline-flex flex-wrap">
                                <button type="button" class="btn-fs-cancel" id="cancelBtn">Cancel</button>
                                <button type="button" class="btn-fs-submit" id="DraftBtn">Submit</button>
                            </span>
                        @elseif($isRenewFormP)
                            <button type="button" class="btn-fs-draft" id="saveDraftBtn">
                                <i class="fa fa-floppy-o"></i> Save As Draft
                            </button>
                            <button type="button" class="btn-fs-submit" id="ProceedtoPayment">
                                <i class="fa fa-eye"></i> Preview &amp; Proceed
                            </button>
                        @else
                            <button type="button" class="btn-fs-draft" id="DraftBtn">
                                <i class="fa fa-floppy-o"></i> Save As Draft
                            </button>
                            <button type="button" class="btn-fs-submit" id="ProceedtoPayment">
                                <i class="fa fa-credit-card"></i> Save and Proceed for Payment
                            </button>
                        @endif
                    </div>

                </form>
            </div>{{-- /fs-form-body --}}
        </div>{{-- /fs-card --}}
    </div>{{-- /container --}}
</div>{{-- /fs-page-wrap --}}

@include('user_login.partials.form-p-preview-modal')

<footer class="main-footer">
    @include('include.footer')
</footer>

<script>
    window.returnApplicationQueryReasons = @json(isset($queryReasonsForValidation) ? $queryReasonsForValidation : []);
    window.isRenewFormP = @json($isRenewFormP ?? false);
    window.isReturnedFormP = @json($isReturned);
</script>
<script>
    function confirmWithSwal(message, onConfirm) {
        if (window.Swal && typeof window.Swal.fire === 'function') {
            window.Swal.fire({
                title: 'Please Confirm',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed && typeof onConfirm === 'function') {
                    onConfirm();
                }
            });
            return;
        }
        if (confirm(message) && typeof onConfirm === 'function') {
            onConfirm();
        }
    }

    $(document).on('click', '.remove-docs', function () {
        confirmWithSwal('Are you sure you want to remove Aadhaar document?', function () {
            $('#aadhaar_doc_removed').val('1');
            const $wrap = $('.aadhaar-doc-container').first();
            if ($wrap.length) {
                $wrap.replaceWith(
                    '<div class="aadhaar-doc-input">' +
                        '<div class="form-s-file-upload-wrap" style="max-width:280px;">' +
                            '<input autocomplete="off" class="form-control" id="aadhaar_doc" name="aadhaar_doc" type="file" accept=".pdf,application/pdf">' +
                        '</div>' +
                        '<span class="file-limit">File type: PDF (Max 250 KB)</span>' +
                        '<small class="text-danger file-error"></small>' +
                    '</div>'
                );
            }
        });
    });

    $(document).on('click', '.remove-pan-doc', function () {
        confirmWithSwal('Are you sure you want to remove PAN document?', function () {
            $('#pancard_doc_removed').val('1');
            const $wrap = $('.pan-doc-container').first();
            if ($wrap.length) {
                $wrap.replaceWith(
                    '<div class="pancard-doc-input">' +
                        '<div class="form-s-file-upload-wrap" style="max-width:280px;">' +
                            '<input autocomplete="off" class="form-control" id="pancard_doc" name="pancard_doc" type="file" accept=".pdf,application/pdf">' +
                        '</div>' +
                        '<span class="file-limit">File type: PDF (Max 250 KB)</span>' +
                        '<small class="text-danger file-error"></small>' +
                    '</div>'
                );
            }
        });
    });

    document.getElementById('upload_photo').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const nameEl = document.getElementById('upload_photo_name');
        const frame  = document.getElementById('photo-input-wrapper');

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('preview_applicant');
                const placeholder = document.getElementById('photo_placeholder');
                preview.src = e.target.result;
                preview.style.display = 'block';
                if (placeholder) placeholder.style.display = 'none';
                if (frame) frame.classList.add('has-image');
            };
            reader.readAsDataURL(file);
            if (nameEl) nameEl.textContent = file.name;
        } else if (nameEl) {
            nameEl.textContent = 'No file selected';
        }
    });

    // Kept as no-op for backward-compat; the label now handles toggling.
    function togglePhotoInput() {
        const inp = document.getElementById('upload_photo');
        if (inp) inp.click();
    }
</script>
<script>
    document.getElementById('upload_sign').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const nameEl = document.getElementById('upload_sign_name');
        const frame  = document.getElementById('sign-input-wrapper');

        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('preview_signature');
                const placeholder = document.getElementById('sign_placeholder');
                if (preview) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                if (placeholder) placeholder.style.display = 'none';
                if (frame) frame.classList.add('has-image');
            };
            reader.readAsDataURL(file);
            if (nameEl) nameEl.textContent = file.name;
        } else if (nameEl) {
            nameEl.textContent = 'No file selected';
        }
    });

    // Kept as no-op for backward-compat; the label now handles toggling.
    function toggleSignInput() {
        const inp = document.getElementById('upload_sign');
        if (inp) inp.click();
    }
</script>
<script>
    // Age calculation on DOB change
    $('#d_o_b').on('change', function() {
        const dob = new Date($(this).val());
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();

        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }

        $('#age').val(age);
    });

    // Add more education row
    $(document).on('click', function(e) {
        let container = document.getElementById("education-container");
        let educationRows = container.querySelectorAll(".education-fields");

        if (e.target.closest(".add-more-education")) {

            if (educationRows.length >= 5) {
                $('#education-table').next('.education-error').remove();

                $('<div class="text-danger mt-2 education-error">You can add a maximum of 5 education entries.</div>')
                .insertAfter('#education-table');

                setTimeout(() => {
                    $('.education-error').fadeOut();
                }, 7000);
                return;
            }

            let currentYear = new Date().getFullYear();
            let yearOptions = '<option value="">Select Year</option>';
            for (let year = currentYear; year >= 1980; year--) {
                yearOptions += `<option value="${year}">${year}</option>`;
            }

            // calculate next serial number
            let serialNo = $('#education-container .education-fields').length + 1;

            let newRow = `
            <tr class="education-fields text-center">
                <td>${serialNo}</td>
                <td>
                    <select class="form-control" name="educational_level[]" required>
                        <option value="">Select Education</option>
                        <option value="BEM">B.E(Mechanical)</option>
                        <option value="BEE">B.E(Electrical)</option>
                        <option value="DME">Diploma(Mechanical)</option>
                        <option value="DEE">Diploma(Electrical)</option>
                    </select>
                </td>
                <td><input type="text" class="form-control" name="institute_name[]" required></td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <select name="month_of_passing[]" class="form-control" style="flex:1;min-width:0;" required>
                            <option value="">Month</option>
                            <option value="01">Jan</option><option value="02">Feb</option>
                            <option value="03">Mar</option><option value="04">Apr</option>
                            <option value="05">May</option><option value="06">Jun</option>
                            <option value="07">Jul</option><option value="08">Aug</option>
                            <option value="09">Sep</option><option value="10">Oct</option>
                            <option value="11">Nov</option><option value="12">Dec</option>
                        </select>
                        <select name="year_of_passing[]" class="form-control" style="flex:1;min-width:0;" required>
                            ${yearOptions}
                        </select>
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control certificate-input" name="certificate_no[]" maxlength="20" placeholder="Certificate No" required>
                </td>
                <td>
                    <input type="file" class="form-control education-file" accept=".pdf,.png,.jpg,.jpeg" required>
                </td>
                <td class="text-center p-1">
                    <div class="form-s-actions-stack">
                        <button type="button" class="btn-tbl-remove remove-education py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button>
                    </div>
                </td>
                <input type="hidden" name="edu_id[]" value="">
                <input type="hidden" name="existing_document[]" value="">
                <input type="hidden" name="removed_document[]" value="0" class="removed-document-edu">
            </tr> `;
            $('#education-container').append(newRow);

            $("#education-container .education-fields").each(function (index) {
                $(this).find(".education-file").attr("name", `education_document[${index}]`);
            });

        }

        if (e.target.closest(".remove-education")) {
            const row = e.target.closest("tr");
            confirmWithSwal('Are you sure you want to remove this education row?', function () {
                row.remove();
            });
        }
    });

    // Add more work row
    $(document).on('click', function(e) {

        let container = document.getElementById("work-container");
        let workRows = container.querySelectorAll(".work-fields");

        if (e.target.closest(".add-more-work")) {

            if (workRows.length >= 3) {

                $('#work-table').next('.work-error').remove();

                $('<div class="text-danger mt-2 work-error">You can add a maximum of 3 work experience entries.</div>')
                .insertAfter('#work-table');

                setTimeout(() => {
                    $('.work-error').fadeOut();
                }, 7000);

                return;
            }

            let serialNo = $('#work-container .work-fields').length + 1;
            let newRowIndex = serialNo - 1;
            let newRow = `
                    <tr class="work-fields text-center">
                        <td>${serialNo}</td>
                        <td><input type="text" class="form-control" name="work_level[]"></td>
                        <td>
                            <div class="d-flex" style="gap:6px;">
                                <input type="date" class="form-control work-date-from" name="work_date_from[]">
                                <input type="date" class="form-control work-date-to" name="work_date_to[]">
                                <input type="text" class="form-control work-year-total-display" placeholder="—" readonly tabindex="-1">
                            </div>
                            <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]">
                            <input type="hidden" name="experience[]" class="experience-sync">
                        </td>
                        <td><input type="text" class="form-control" name="designation[]"></td>
                        <td class="text-center">
                            <input type="file" class="form-control" name="work_document[${newRowIndex}]" accept=".pdf,.png,.jpg,.jpeg">
                        </td>
                        <td class="text-center p-1">
                            <div class="form-s-actions-stack">
                                <button type="button" class="btn-tbl-remove remove-work py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button>
                            </div>
                        </td>
                        <input type="hidden" name="work_id[]">
                        <input type="hidden" name="existing_work_document[]">
                    </tr>
                `;
            $('#work-container').append(newRow);

            $('#work-container .work-fields').each(function (index) {
                $(this).find('.work-file').attr('name', `work_document[${index}]`);
            });
        }

        if (e.target.closest(".remove-work")) {
            const row = e.target.closest("tr");
            confirmWithSwal('Are you sure you want to remove this work row?', function () {
                row.remove();
            });
        }
    });

    // Form P work-experience total years calculator
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
        var legacyInput = row.querySelector('.experience-sync');
        if (!fromInput || !toInput || !displayInput) return;
        var total = calcWorkTotalYearsP(fromInput.value, toInput.value);
        displayInput.value = total;
        var clean = (total === 'Invalid range') ? '' : total;
        if (hiddenInput) hiddenInput.value = clean;
        if (legacyInput) legacyInput.value = clean;
    }
    $(document).on('change', '.work-date-from, .work-date-to', function () {
        refreshWorkTotalP(this.closest('.work-fields'));
    });
    $(function () {
        document.querySelectorAll('#work-container .work-fields').forEach(refreshWorkTotalP);
    });

    $(document).on('click', function(e) {

        let container = document.getElementById("institute-container");
        let workRows = container.querySelectorAll(".institute-fields");

        if (e.target.closest(".add-more-institute")) {

            if (workRows.length >= 2) {

                $('#institute-table').next('.institute-error').remove();

                $('<div class="text-danger mt-2 institute-error">You can add a maximum of 2 Institute entries.</div>')
                .insertAfter('#institute-table');

                setTimeout(() => {
                    $('.institute-error').fadeOut();
                }, 7000);

                return;
            }

            let serialNo = $('#institute-container .institute-fields').length + 1;
            let newRowIndex = serialNo - 1;
            let newRow = `
                    <tr class="institute-fields text-center">
                        <td>${serialNo}</td>
                        <td><textarea autocomplete="off" class="form-control" name="institute_name_address[]" cols="5" rows="3"></textarea></td>
                        <td><input type="date" class="form-control" name="from_date[]"></td>
                        <td><input type="date" class="form-control" name="to_date[]"></td>
                        <td><input type="text" class="form-control" name="duration[]" readonly></td>
                        <td class="text-center">
                            <input type="file" class="form-control institute-file" name="institute_document[]" accept=".pdf,.png,.jpg,.jpeg">
                        </td>
                        <td class="text-center p-1">
                            <div class="form-s-actions-stack">
                                <button type="button" class="btn-tbl-remove remove-inst-row py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button>
                            </div>
                        </td>
                        <input type="hidden" name="institute_id[]">
                        <input type="hidden" name="exist_institute_document[]">
                        <input type="hidden" name="removed_document_inst[]" value="0" class="removed-document-inst">
                    </tr>
                `;
            $('#institute-container').append(newRow);
        }

        if (e.target.closest(".remove-inst-row")) {
            const row = e.target.closest("tr");
            confirmWithSwal('Are you sure you want to remove this institute row?', function () {
                row.remove();
            });
        }
    });

    // Remove existing education document (switch to file upload + mark removed)
    $(document).on('click', '.remove-doc_edu', function () {
        const $row = $(this).closest('tr');
        const uploadCell = $(this).closest('td');
        confirmWithSwal('Are you sure you want to remove this education document?', function () {
            $row.find('.removed-document-edu').val('1');
            $row.find('input[name="existing_document[]"]').val('');
            uploadCell.html('<input type="file" class="form-control education-file" name="education_document[]" accept=".pdf,application/pdf">');
        });
    });

    // Remove existing institute document (switch to file upload + mark removed)
    $(document).on('click', '.remove-inst', function () {
        const $row = $(this).closest('tr');
        const uploadCell = $(this).closest('td');
        confirmWithSwal('Are you sure you want to remove this institute document?', function () {
            $row.find('.removed-document-inst').val('1');
            $row.find('input[name="exist_institute_document[]"]').val('');
            uploadCell.html('<input type="file" class="form-control institute-file" name="institute_document[]" accept=".pdf,.png,.jpg,.jpeg">');
        });
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
        var from = new Date(fromDate + 'T00:00:00');
        var to = new Date(toDate + 'T00:00:00');
        if (isNaN(from.getTime()) || isNaN(to.getTime()) || to < from) return '';

        var years = to.getFullYear() - from.getFullYear();
        var months = to.getMonth() - from.getMonth();
        if (to.getDate() < from.getDate()) months -= 1;
        if (months < 0) { years -= 1; months += 12; }
        if (years < 0) return '';
        return years + '.' + months;
    }

    function updateInstituteDuration($row) {
        var fromDate = $row.find('input[name="from_date[]"]').val();
        var toDate = $row.find('input[name="to_date[]"]').val();
        var duration = calculateInstituteDurationYears(fromDate, toDate);
        $row.find('input[name="duration[]"]').val(duration);
    }

    $(document).on('change input', 'input[name="from_date[]"], input[name="to_date[]"]', function () {
        var $row = $(this).closest('tr.institute-fields');
        if ($row.length) updateInstituteDuration($row);
    });

    $('#institute-container tr.institute-fields').each(function () {
        updateInstituteDuration($(this));
    });

    function clearLocalFilePreviewEditP($input) {
        const $preview = $input.siblings('.local-file-preview');
        const blobUrl = $preview.data('blobUrl');
        if (blobUrl) {
            URL.revokeObjectURL(blobUrl);
        }
        $preview.remove();
        $input.removeAttr('data-has-local-file');
    }

    function showLocalFilePreviewEditP($input, file) {
        clearLocalFilePreviewEditP($input);
        if (!file) return;

        const blobUrl = URL.createObjectURL(file);
        $input.attr('data-has-local-file', '1');

        const $preview = $('<div class="local-file-preview"></div>').data('blobUrl', blobUrl);
        const $link = $('<a>', {
            href: blobUrl,
            target: '_blank',
            rel: 'noopener noreferrer',
            class: 'preview-link'
        }).html('<i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View Document');

        $preview.append($link);
        $input.after($preview);
    }

    $(document).on('change', 'input[type="file"][name^="education_document"], input[type="file"][name^="institute_document"], input[type="file"][name^="work_document"]', function () {
        const file = this.files && this.files[0] ? this.files[0] : null;
        showLocalFilePreviewEditP($(this), file);
    });
</script>

{{-- ═══════════════════════════════════════════════════════════════════
     UX ENHANCEMENTS — query highlight, progress, animations,
     verify spinner, file validation, sticky bar, confirm modal
     ═══════════════════════════════════════════════════════════════════ --}}
<script>
(function(){
    'use strict';
    if (typeof window.jQuery === 'undefined') return;
    var $ = window.jQuery;
    var $form = $('#competency_form_p');
    if (!$form.length) return;

    /* ── 1. Query field/section highlighting ─────────────── */
    function highlightQueriedSections() {
        var queryItems = [];
        $('.fs-query-alert ul li').each(function(){
            var t = ($(this).text() || '').toLowerCase().trim();
            if (t) queryItems.push(t);
        });
        // Also fold in queryReasonsForValidation if available as strings
        var reasons = window.returnApplicationQueryReasons || [];
        if (Array.isArray(reasons)) {
            reasons.forEach(function(r){
                if (typeof r === 'string') queryItems.push(r.toLowerCase());
                else if (r && typeof r === 'object') {
                    Object.values(r).forEach(function(v){
                        if (typeof v === 'string') queryItems.push(v.toLowerCase());
                    });
                }
            });
        }
        if (!queryItems.length) return;

        $('.fs-section[data-query-keywords]').each(function(){
            var $sec = $(this);
            var kws = ($sec.data('query-keywords') || '').toLowerCase().split('|').map(function(k){ return k.trim(); }).filter(Boolean);
            var matched = queryItems.some(function(qi){
                return kws.some(function(k){ return qi.indexOf(k) !== -1; });
            });
            if (matched) $sec.addClass('fs-section-queried');
        });
    }

    /* ── 2. Section progress tracker ─────────────────────── */
    function updateProgress() {
        var $sections = $('.fs-section').filter(function(){ return $(this).is(':visible'); });
        var total = 0, done = 0;
        $sections.each(function(){
            total++;
            var $sec = $(this);
            var requiredEls = $sec.find('input[required], select[required], textarea[required], input.form-control[name="applicant_name"], input.form-control[name="fathers_name"], textarea[name="applicants_address"], input[name="d_o_b"]');
            // If section has no explicit required, count it complete if any visible input has a value
            if (!requiredEls.length) {
                var $any = $sec.find('input.form-control:not([type=hidden]), select.form-control, textarea.form-control').filter(':visible').first();
                if ($any.length && ($any.val() || '').toString().trim() !== '') done++;
                else if (!$any.length) done++; // sections with no fields auto-complete
                return;
            }
            var allFilled = true;
            requiredEls.each(function(){
                var v = ($(this).val() || '').toString().trim();
                if (!v) { allFilled = false; return false; }
            });
            // Doc table sections: check at least one non-hidden file or existing-doc indicator
            var $docTables = $sec.find('#education-table, #institute-table, #work-table');
            if ($docTables.length) {
                var rowsHaveData = $sec.find('tbody tr').filter(function(){
                    return $(this).find('input[type=text], select, textarea').filter(function(){
                        return ($(this).val() || '').toString().trim() !== '' && $(this).val() !== '0';
                    }).length > 0;
                }).length > 0;
                if (!rowsHaveData) allFilled = false;
            }
            if (allFilled) done++;
        });
        var pct = total ? Math.round((done / total) * 100) : 0;
        $('#formProgressFill').css('width', pct + '%').toggleClass('complete', pct === 100);
        $('#formProgressText').text(done + ' of ' + total + ' sections' + (pct === 100 ? ' ✓' : ''));
    }

    /* ── 3. Smooth toggle panel animations ──────────────── */
    function slideTogglePanel($panel, show) {
        if (!$panel || !$panel.length) return;
        if (show) {
            $panel.css({display:'block', maxHeight:0, opacity:0}).removeClass('collapsing-out');
            requestAnimationFrame(function(){
                var h = $panel[0].scrollHeight;
                $panel.css({maxHeight: h + 'px', opacity: 1});
                setTimeout(function(){ $panel.css('maxHeight',''); }, 320);
            });
        } else {
            var h = $panel[0].scrollHeight;
            $panel.css({maxHeight: h + 'px'});
            requestAnimationFrame(function(){
                $panel.addClass('collapsing-out');
                setTimeout(function(){ $panel.css('display','none').removeClass('collapsing-out').css({maxHeight:'',opacity:''}); }, 320);
            });
        }
    }
    $form.on('change', '.toggle-details', function(){
        var target = $(this).data('target');
        if (!target) return;
        var $panel = $(target);
        var isYes = $(this).val() === 'yes';
        slideTogglePanel($panel, isYes);
    });

    /* ── 4. Verify button spinner ───────────────────────── */
    $form.on('click', '.verify-btn', function(){
        var $btn = $(this);
        if ($btn.hasClass('is-loading')) return;
        $btn.addClass('is-loading');
        // Restore after 8s as a safety net (in case external JS doesn't fire callback)
        setTimeout(function(){ $btn.removeClass('is-loading'); }, 8000);
        // Watch for verify_status text mutations to remove spinner sooner
        var observer = new MutationObserver(function(){
            $btn.removeClass('is-loading');
            observer.disconnect();
        });
        var statusEls = document.querySelectorAll('.verify_status, #verify_status, #verify_result');
        statusEls.forEach(function(el){ observer.observe(el, {childList:true, characterData:true, subtree:true}); });
    });

    /* ── 5. Inline file validation ──────────────────────── */
    function validateFileInput(input) {
        var $inp = $(input);
        var $row = $inp.closest('tr, .fs-photo-card, .aadhaar-doc-input, .fs-upload-card').first();
        var $limit = $row.find('.file-limit').first();
        if ($limit.length && !$limit.data('defaultText')) {
            $limit.data('defaultText', $limit.text());
        }
        var file = input.files && input.files[0];
        if (!file) {
            $inp.removeClass('file-invalid');
            if ($limit.length) {
                $limit.removeClass('is-error is-success').text($limit.data('defaultText') || '');
            }
            return true;
        }
        var maxKB = 250; // default for PDF docs
        var name = (input.name || '').toLowerCase();
        if (name === 'upload_photo' || name === 'upload_sign') maxKB = 50;
        else if (name.indexOf('education_document') === 0 || name.indexOf('institute_document') === 0 || name.indexOf('work_document') === 0) maxKB = 200;
        var sizeKB = file.size / 1024;
        if (sizeKB > maxKB) {
            $inp.addClass('file-invalid');
            if ($limit.length) $limit.removeClass('is-success').addClass('is-error').text('✗ ' + Math.ceil(sizeKB) + ' KB exceeds ' + maxKB + ' KB limit. Please upload a smaller file.');
            return false;
        }
        $inp.removeClass('file-invalid');
        if ($limit.length) $limit.removeClass('is-error is-success').text($limit.data('defaultText') || '');
        return true;
    }
    $form.on('change', 'input[type="file"]', function(){ validateFileInput(this); updateProgress(); });

    /* ── 6. Table horizontal scroll cue ─────────────────── */
    function detectScroll() {
        $('.fs-table-wrap').each(function(){
            var el = this;
            $(this).toggleClass('has-scroll', el.scrollWidth > el.clientWidth + 4);
        });
    }
    $(window).on('resize', detectScroll);

    /* ── 7. Sticky action bar visual state ──────────────── */
    function updateStickyState() {
        var $bar = $('.fs-action-bar');
        if (!$bar.length) return;
        var rect = $bar[0].getBoundingClientRect();
        var vh = window.innerHeight || document.documentElement.clientHeight;
        // Considered "stuck" while the bottom edge is at viewport bottom and content extends below
        var stuck = rect.bottom >= vh - 1 && (document.documentElement.scrollHeight - window.scrollY - vh) > 20;
        $bar.toggleClass('is-stuck', stuck);
    }
    $(window).on('scroll resize', updateStickyState);

    /* ── 8. Re-run progress on input changes ─────────────── */
    $form.on('change input', 'input, select, textarea', function(){
        // throttle
        if (window._fsProgressT) clearTimeout(window._fsProgressT);
        window._fsProgressT = setTimeout(updateProgress, 200);
    });

    /* ── Init ────────────────────────────────────────────── */
    $(function(){
        highlightQueriedSections();
        updateProgress();
        detectScroll();
        updateStickyState();
        // Set initial toggle panel state via slide instead of inline display
        $('.toggle-details:checked').each(function(){
            var $panel = $($(this).data('target'));
            if (!$panel.length) return;
            if ($(this).val() === 'yes' && $panel.is(':hidden')) slideTogglePanel($panel, true);
            else if ($(this).val() === 'no'  && $panel.is(':visible')) $panel.hide();
        });
    });
})();
</script>
@include('user_login.partials.form-p-preview-modal-script')
