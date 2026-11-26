@include('include.header')
<style>
    /* ── Reset helpers ────────────────────────────────── */
    .fs-form hr {
        margin: 0;
        border: 0;
        border-top: 1px solid #e3e8f0;
    }
    .fs-form .form-group { margin-bottom: 0; }
    .apply-card label { font-size: 12px; }

    /* ── SweetAlert overrides ─────────────────────────── */
    .swal2-popup li            { font-size: 15px; margin-bottom: 8px; }
    .swal2-popup li ul         { margin-left: 15px; }

    /* ── Page wrapper ─────────────────────────────────── */
    .fs-page-wrap {
        background: #f0f4f9;
        min-height: 100vh;
        padding-bottom: 48px;
    }

    /* Digitization enrollment modal: stay hidden unless JS opens it (first visit) */
    #digitization.modal:not(.show) {
        display: none !important;
    }

    /* ── Breadcrumb ───────────────────────────────────── */
    .fs-breadcrumb-bar {
        background: #fff;
        border-bottom: 1px solid #e3e8f0;
        padding: 10px 0;
    }
    .fs-breadcrumb-bar #breadcrumb,
    .fs-breadcrumb-bar #breadcrumb li,
    .fs-breadcrumb-bar #breadcrumb li a {
        all: unset;
    }
    .fs-breadcrumb-bar #breadcrumb {
        display: flex !important;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        list-style: none !important;
        margin: 0 !important;
        padding: 0 !important;
        font-size: 0.85rem;
        background: none !important;
    }
    .fs-breadcrumb-bar #breadcrumb li {
        display: flex !important;
        align-items: center;
        background: none !important;
        clip-path: none !important;
        padding: 0 !important;
        margin: 0 !important;
        float: none !important;
    }
    .fs-breadcrumb-bar #breadcrumb li + li::before {
        content: '›';
        color: #adb5bd;
        margin-right: 6px;
        font-size: 1rem;
        line-height: 1;
    }
    .fs-breadcrumb-bar #breadcrumb a {
        color: #035ab3 !important;
        text-decoration: none !important;
        font-size: 0.85rem !important;
        background: none !important;
        padding: 0 !important;
    }
    .fs-breadcrumb-bar #breadcrumb a:hover { text-decoration: underline !important; cursor: pointer; }

    /* ── Main card ────────────────────────────────────── */
    .fs-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 16px rgba(3,90,179,.10);
        overflow: hidden;
        margin-top: 24px;
    }

    /* ── Card header ──────────────────────────────────── */
    .fs-card-header {
        background: linear-gradient(135deg, #035ab3 0%, #0472d9 100%);
        padding: 10px 24px 6px;
        position: relative;
    }
    .fs-card-header .header-titles { text-align: center; }
    .fs-card-header .header-titles h5 {
        margin: 0 0 2px;
        font-size: 1.05rem;
        font-weight: 700;
        letter-spacing: .5px;
        color: #fff;
        text-transform: uppercase;
        line-height: 1.4;
    }
    .fs-card-header .header-titles h5.tamil-title {
        font-size: .98rem;
        font-weight: 400;
        opacity: .9;
    }
    .fs-card-header .header-titles .form-badge {
        display: inline-block;
        background: rgba(255,255,255,.18);
        border: 1px solid rgba(255,255,255,.35);
        color: #fff;
        border-radius: 20px;
        padding: 2px 14px;
        font-size: .82rem;
        font-weight: 600;
        margin-top: 4px;
        letter-spacing: .5px;
    }
    /* ── Mandatory notice ─────────────────────────────── */
    .fs-mandatory-bar {
        background: #f8f9ff;
        border-bottom: 1px solid #e3e8f0;
        padding: 7px 28px;
        font-size: .83rem;
        color: #555;
        text-align: right;
    }
    .fs-mandatory-bar .req-dot { color: #d9363e; font-weight: 700; margin-right: 2px; }

    /* ── Form body ────────────────────────────────────── */
    .fs-form-body { padding: 28px 28px 32px; }

    /* ── Section blocks ───────────────────────────────── */
    .fs-section {
        background: #f8fafd;
        border: 1px solid #e3e8f0;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    .fs-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 18px;
        background: #eef3fb;
        border-bottom: 1px solid #dde5f3;
    }
    .fs-section-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: #035ab3;
        color: #fff;
        font-size: .75rem;
        font-weight: 700;
        flex-shrink: 0;
    }
    .fs-section-title {
        font-size: .9rem;
        font-weight: 600;
        color: #1a2a4a;
        line-height: 1.35;
    }
    .fs-section-title .section-req { color: #d9363e; }
    .fs-section-title .section-hint {
        font-size: .78rem;
        font-weight: 400;
        color: #5a7299;
        margin-left: 4px;
    }
    .fs-section-tamil {
        font-size: .8rem;
        color: #5a7299;
        line-height: 1.4;
        margin-top: 1px;
    }
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

    /* ── Field rows ───────────────────────────────────── */
    .fs-field-label {
        font-size: .83rem;
        font-weight: 600;
        color: #2c3e5e;
        margin-bottom: 3px;
        line-height: 1.3;
    }
    .fs-field-label .req { color: #d9363e; }
    .fs-field-tamil {
        font-size: .76rem;
        color: #7a90b0;
        margin-bottom: 4px;
        line-height: 1.3;
    }
    /* DOB + Age: badge 5 inline with labels (same pattern as other in-grid headers) */
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
    .fs-form .form-control {
        border: 1px solid #ccd5e3;
        border-radius: 6px;
        font-size: .875rem;
        height: auto;
        padding: 7px 11px;
        transition: border-color .2s, box-shadow .2s;
        background: #fff;
    }
    .fs-form .form-control:focus {
        border-color: #035ab3;
        box-shadow: 0 0 0 3px rgba(3,90,179,.12);
        outline: none;
    }
    .fs-form .form-control[readonly],
    .fs-form .form-control:disabled {
        background: #f4f6fb;
        color: #6b7a99;
    }
    .fs-form textarea.form-control { resize: vertical; }

    /* ── Radio toggle ─────────────────────────────────── */
    .fs-radio-group {
        display: flex;
        gap: 16px;
        align-items: center;
        flex-wrap: wrap;
    }
    .fs-radio-group .form-check { margin: 0; }
    .fs-radio-group .form-check-input { margin-top: 2px; accent-color: #035ab3; }
    .fs-radio-group .form-check-label { font-size: .875rem; font-weight: 500; color: #2c3e5e; cursor: pointer; }

    /* ── Toggle sub-panel ─────────────────────────────── */
    .fs-toggle-panel {
        background: #f0f5ff;
        border: 1px solid #d0ddf5;
        border-radius: 6px;
        padding: 16px;
        margin-top: 12px;
    }
    .fs-toggle-panel .fs-field-label { color: #1a3a72; }

    /* ── Verify button ────────────────────────────────── */
    .btn-verify {
        background: #035ab3;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 7px 16px;
        font-size: .82rem;
        font-weight: 600;
        letter-spacing: .3px;
        cursor: pointer;
        transition: background .2s;
        white-space: nowrap;
    }
    .btn-verify:hover { background: #024a98; color: #fff; }

    /* ── Tables ───────────────────────────────────────── */
    .fs-table-wrap { overflow-x: auto; border-radius: 6px; border: 1px solid #dde5f3; }
    .fs-form table.table { margin-bottom: 0; font-size: .83rem; }
    .fs-form table.table thead th {
        background: #eef3fb;
        color: #1a2a4a;
        font-weight: 600;
        font-size: .78rem;
        padding: .45rem .5rem;
        vertical-align: middle;
        border-bottom: 2px solid #d0ddf5;
        border-color: #d0ddf5;
        line-height: 1.25;
    }
    .fs-form table.table tbody td {
        padding: .45rem .5rem;
        vertical-align: middle;
        border-color: #e8edf6;
    }
    .fs-form table.table tbody tr:nth-child(even) td { background: #f8fafd; }
    .fs-form table.table tbody tr:hover td { background: #eef3fb; }
    .fs-form table.table .form-control {
        font-size: .82rem;
        padding: 5px 8px;
    }
    .fs-form .file-limit {
        font-size: .72rem;
        color: #28a745;
        display: block;
        margin-top: 2px;
        line-height: 1.3;
    }

    /* ── File upload wrap ─────────────────────────────── */
    .form-s-file-upload-wrap {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .35rem;
    }
    .form-s-file-upload-wrap .form-control { flex: 1 1 auto; min-width: 0; }

    #education-table .form-s-file-upload-wrap--combined,
    .work-card .form-s-file-upload-wrap--combined {
        display: flex;
        flex-direction: row;
        flex-wrap: nowrap;
        align-items: stretch;
        align-self: flex-start;
        gap: 0;
        width: 100%;
        min-width: 12rem;
        max-width: 20rem;
        border: 1px solid #ccd5e3;
        border-radius: 6px;
        overflow: hidden;
        background: #fff;
    }
    /* In the card layout, allow the upload pill to grow with the field column. */
    .work-card .form-s-file-upload-wrap--combined { max-width: none; }
    #education-table .form-s-file-upload-wrap--combined .form-control,
    .work-card .form-s-file-upload-wrap--combined .form-control,
    #education-table .form-s-file-upload-wrap--combined input[type="file"],
    .work-card .form-s-file-upload-wrap--combined input[type="file"] {
        flex: 1 1 auto;
        min-width: 0;
        width: auto;
        font-size: .8125rem;
        border: 0 !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        padding: .3rem .45rem;
        background: #fff;
    }

    /* ── Table action cells ───────────────────────────── */
    #education-table td.form-s-actions-cell,
    #work-table td.work-exp-col-actions { vertical-align: middle; width: 3rem; }
    #education-table .form-s-actions-stack,
    #work-table .form-s-actions-stack {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        gap: .35rem;
    }

    /* ── Table add/remove buttons ─────────────────────── */
    .btn-tbl-add {
        background: #035ab3;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 4px 9px;
        font-size: .8rem;
        cursor: pointer;
        transition: background .2s;
    }
    .btn-tbl-add:hover { background: #024a98; }
    .btn-tbl-remove {
        background: #dc3545;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 4px 9px;
        font-size: .8rem;
        cursor: pointer;
        transition: background .2s;
    }
    .btn-tbl-remove:hover { background: #b52a37; }

    /* ── Local file preview ───────────────────────────── */
    .local-file-preview {
        display: flex;
        align-items: center;
        gap: .4rem;
        margin-top: .35rem;
    }
    .local-file-preview .preview-link {
        color: #0056b3 !important;
        font-size: .78rem;
        font-weight: 600;
    }
    .local-file-preview .img-preview {
        width: 44px; height: 44px;
        border: 1px solid #ccd5e3;
        border-radius: 4px;
        object-fit: cover;
    }

    /* ── Education table column widths ───────────────── */
    #education-table thead th:last-child,
    #work-table thead th.work-exp-col-actions { vertical-align: middle; text-align: center; }
    #education-table thead th {
        font-size: .72rem; font-weight: 600;
        padding: .3rem .35rem;
        vertical-align: middle; line-height: 1.2; text-align: center;
    }
    #education-table thead tr:nth-child(2) th { font-size: .7rem; padding: .25rem .3rem; }
    #education-table thead th .file-limit { font-size: .66rem; }
    #education-table tbody td { text-align: center; vertical-align: middle; }
    #education-table tbody .form-control,
    #education-table tbody select,
    #education-table tbody input { font-size: .86rem; line-height: 1.25; }
    #education-table tbody select option { font-size: .86rem; }

    /* ── Documents upload table ───────────────────────── */
    .fs-docs-table { width: 100%; }
    .fs-docs-table td { vertical-align: middle; padding: 10px 12px; border-color: #e8edf6; }
    .fs-docs-table .doc-serial {
        width: 48px;
        min-width: 48px;
        font-weight: 700;
        color: #035ab3;
        font-size: .85rem;
        white-space: nowrap;
        text-align: center;
    }
    .fs-docs-table .doc-label-cell { min-width: 180px; }
    .photo-preview-box {
        display: inline-block;
    }
    .photo-preview-box img {
        width: 90px; height: 108px;
        object-fit: cover;
        border: 2px solid #ccd5e3;
        border-radius: 6px;
    }
    .fs-upload-card {
        border: 1px dashed #b8c8e2;
        background: #f8fbff;
        border-radius: 10px;
        padding: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        flex-wrap: wrap;
    }
    .fs-upload-controls {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 220px;
        flex: 1 1 220px;
    }
    .fs-upload-input {
        width: 100%;
        max-width: 300px;
    }
    .fs-upload-file-name {
        font-size: .75rem;
        color: #60779c;
        line-height: 1.3;
        min-height: 1.1rem;
    }
    .fs-upload-preview {
        border: 1px solid #ccd5e3;
        border-radius: 8px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }
    .fs-upload-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: none;
    }
    .fs-upload-preview--photo {
        width: 96px;
        height: 118px;
    }
    .fs-upload-preview--sign {
        width: 180px;
        height: 80px;
    }
    .fs-upload-preview--sign img {
        object-fit: contain;
    }
    .fs-upload-placeholder {
        font-size: .72rem;
        color: #89a0c4;
        text-align: center;
        padding: 0 10px;
        line-height: 1.35;
    }
    @media (max-width: 575.98px) {
        .fs-upload-preview--photo {
            width: 84px;
            height: 102px;
        }
        .fs-upload-preview--sign {
            width: 144px;
            height: 68px;
        }
    }

    /* ── Declaration ──────────────────────────────────── */
    .fs-declaration {
        background: #f0f5ff;
        border: 1px solid #c8d8f5;
        border-radius: 8px;
        padding: 16px 20px;
        margin-top: 4px;
    }
    .fs-declaration label.container {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        cursor: pointer;
        padding: 0;
        margin: 0;
        width: 100%;
    }
    .fs-declaration input[type="checkbox"] {
        width: 18px; height: 18px;
        accent-color: #035ab3;
        flex-shrink: 0;
        margin-top: 3px;
        cursor: pointer;
    }
    .fs-declaration .decl-text {
        font-size: .875rem;
        color: #1a2a4a;
        line-height: 1.6;
    }
    .fs-declaration .decl-text .tamil { display: block; color: #5a7299; margin-top: 4px; font-size: .82rem; }
    .fs-declaration .checkmark { display: none; }

    /* ── Action buttons ───────────────────────────────── */
    .fs-action-bar {
        display: flex;
        justify-content: center;
        gap: 12px;
        flex-wrap: wrap;
        padding: 24px 0 4px;
    }
    .btn-fs-draft {
        background: #fff;
        color: #035ab3;
        border: 2px solid #035ab3;
        border-radius: 8px;
        padding: 10px 28px;
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s;
    }
    .btn-fs-draft:hover { background: #eef3fb; }
    .btn-fs-submit {
        background: linear-gradient(135deg, #1a9e4f, #15883f);
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 10px 28px;
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        box-shadow: 0 3px 10px rgba(26,158,79,.25);
        transition: all .2s;
    }
    .btn-fs-submit:hover { background: linear-gradient(135deg, #15883f, #116e32); box-shadow: 0 4px 14px rgba(26,158,79,.35); }

    /* ── Draft modal ──────────────────────────────────── */
    .overlay-bg {
        position: fixed; inset: 0;
        background: rgba(0,0,0,.45);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .otp-modal {
        background: #fff;
        border-radius: 12px;
        padding: 32px 36px;
        text-align: center;
        box-shadow: 0 8px 32px rgba(0,0,0,.2);
        max-width: 380px;
        width: 90%;
    }
    .otp-modal h5 { color: #1a9e4f; font-weight: 700; margin-bottom: 16px; }
    .otp-modal button {
        background: #035ab3; color: #fff;
        border: none; border-radius: 6px;
        padding: 8px 32px; font-size: .9rem;
        cursor: pointer;
    }
    .otp-modal button:hover { background: #024a98; }

    /* ── Application Preview Modal ───────────────────── */
    .prv-overlay { position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:10000; display:flex; align-items:flex-end; justify-content:center; }
    .prv-panel   { background:#f8fbff; width:100%; max-width:820px; max-height:90vh; display:flex; flex-direction:column; border-radius:14px 14px 0 0; box-shadow:0 -6px 30px rgba(2,63,149,.2); overflow:hidden; animation:prvSlideUp .25s ease; }
    @keyframes prvSlideUp { from { transform:translateY(40px); opacity:0; } to { transform:translateY(0); opacity:1; } }

    .prv-header  { background:linear-gradient(135deg,#035ab3,#0472d9); padding:12px 18px 10px; display:flex; align-items:center; justify-content:space-between; flex-shrink:0; }
    .prv-header-left h5 { margin:0; font-size:.95rem; font-weight:700; color:#fff; }
    .prv-header-left .prv-subtitle { font-size:.72rem; color:rgba(255,255,255,.85); margin-top:1px; }
    .prv-badge  { background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.28); color:#fff; border-radius:16px; padding:2px 10px; font-size:.72rem; font-weight:600; margin-left:10px; }
    .prv-close  { background:rgba(255,255,255,.18); border:none; color:#fff; width:30px; height:30px; border-radius:50%; font-size:1.1rem; line-height:1; cursor:pointer; transition:background .2s; flex-shrink:0; }
    .prv-close:hover { background:rgba(255,255,255,.32); }

    .prv-body   { overflow-y:auto; padding:16px 18px; flex:1; }
    .prv-section { background:#fff; border:1px solid #e3e8f0; border-radius:10px; margin-bottom:10px; overflow:hidden; }
    .prv-section-hd { background:#f2f7ff; border-bottom:1px solid #dce6f3; padding:7px 12px; display:flex; align-items:center; gap:8px; }
    .prv-section-num { width:20px; height:20px; border-radius:50%; background:#035ab3; color:#fff; font-size:.68rem; font-weight:700; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
    .prv-section-title { font-size:.78rem; font-weight:600; color:#1a2a4a; }
    .prv-section-body { padding:10px 12px; }

    .prv-field  { margin-bottom:8px; }
    .prv-label  { font-size:.68rem; font-weight:600; color:#556c8f; text-transform:uppercase; letter-spacing:.35px; margin-bottom:2px; }
    .prv-value  { font-size:.82rem; color:#17273d; font-weight:500; padding:5px 8px; background:#f7fbff; border:1px solid #dce6f2; border-radius:6px; min-height:auto; word-break:break-word; }
    .prv-value.prv-empty { color:#9da9b7; font-style:italic; }

    .prv-table  { width:100%; font-size:.72rem; border-collapse:collapse; }
    .prv-table th { background:#f2f7ff; color:#1a2a4a; font-weight:600; padding:.3rem .4rem; border:1px solid #dce6f2; font-size:.7rem; white-space:nowrap; }
    .prv-table td { padding:.3rem .4rem; border:1px solid #e3e8f0; vertical-align:middle; }
    .prv-table tr:nth-child(even) td { background:#f9fcff; }
    .prv-badge-yes  { background:#d4edda; color:#155724; border-radius:4px; padding:2px 7px; font-size:.7rem; font-weight:600; }
    .prv-badge-no   { background:#f8d7da; color:#721c24; border-radius:4px; padding:2px 7px; font-size:.7rem; font-weight:600; }

    .prv-thumb { text-align:center; }
    .prv-thumb img { width:72px; height:86px; object-fit:cover; border:2px solid #dbe6f1; border-radius:6px; display:block; margin-bottom:4px; background:#eef6ff; }
    .prv-thumb-sign img { width:120px; height:44px; object-fit:contain; }
    .prv-thumb span { font-size:.68rem; color:#5a7299; }
    .prv-no-img { width:72px; height:86px; background:#eef6ff; border:2px dashed #c9d6e5; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#9aa7b9; font-size:.68rem; text-align:center; }

    .prv-footer { background:#fff; border-top:1px solid #e3e8f0; padding:12px 18px; display:flex; align-items:center; gap:10px; flex-shrink:0; flex-wrap:wrap; }
    .prv-confirm-check { display:flex; align-items:center; gap:8px; flex:1; font-size:.8rem; color:#31445f; cursor:pointer; }
    .prv-confirm-check input { width:16px; height:16px; accent-color:#035ab3; cursor:pointer; }
    .prv-btn-back, .prv-btn-print, .prv-btn-confirm { border-radius:8px; padding:7px 16px; font-size:.82rem; font-weight:600; cursor:pointer; white-space:nowrap; transition:all .2s ease; }
    .prv-btn-back { background:#fff; color:#035ab3; border:1px solid #035ab3; }
    .prv-btn-back:hover { background:#eef3fb; }
    .prv-btn-print { background:#ffffff; color:#4f5f79; border:1px solid #99a7c0; }
    .prv-btn-print:hover { background:#f3f6fb; }
    .prv-btn-confirm { background:linear-gradient(135deg,#1a9e4f,#14813f); color:#fff; border:none; }
    .prv-btn-confirm:disabled { opacity:.45; cursor:not-allowed; }
    .prv-btn-confirm:not(:disabled):hover { opacity:.92; }

    /* ── Validation messages — uniform size ─────────── */
    .fs-form .text-danger,
    .fs-form .error-message,
    .fs-form .error,
    .fs-form span[id$="-error"],
    .fs-form span[class*="error"],
    .fs-form #checkboxError {
        font-size: .78rem !important;
        line-height: 1.3;
        margin-top: 2px;
    }

    /* ── PDF icon always red ─────────────────────────── */
    .fa-file-pdf-o { color: #d9363e !important; }

    /* ── FontAwesome fix (buttons with custom reset must not drop icon font) ── */
    .comp_certificate .btn .fa,
    .comp_certificate .btn i.fa,
    .comp_certificate .btn-tbl-add .fa,
    .comp_certificate .btn-tbl-add i.fa,
    .comp_certificate .btn-tbl-remove .fa,
    .comp_certificate .btn-tbl-remove i.fa,
    .comp_certificate .form-s-file-upload-btn .fa,
    .comp_certificate .form-s-file-upload-btn i.fa,
    .work-exp-wrap .fa,
    .work-exp-wrap i.fa,
    .work-exp-wrap button .fa,
    .work-exp-wrap button i.fa {
        font: normal normal normal 14px/1 FontAwesome;
        display: inline-block;
        text-rendering: auto;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    .work-exp-wrap .work-row-remove .fa,
    .work-exp-wrap .work-row-toggle-btn .fa {
        font-size: .85rem;
    }
    .work-exp-wrap .work-card-field-hint .fa,
    .work-exp-wrap .wx-order-edit-link .fa {
        font-size: .7rem;
    }
</style>
<style>
    @include('user_login.partials.form-s-work-exp-styles', ['editFormName' => 'S'])
</style>
<style>
    @include('user_login.partials.form-s-work-exp-7ab-styles')
    /* Digitization Form S — Section 7a card chrome (7b stays flat) */
    .fs-section:has(.work-exp-wrap) {
        overflow: visible;
    }
    #work-container-previous .work-entry-block > .work-fields.work-row,
    #work-container-previous .work-fields.work-row {
        background: #ffffff !important;
        border: 1px solid #c8d8f5 !important;
        border-radius: 10px !important;
        box-shadow: 0 2px 10px rgba(3, 90, 179, 0.14) !important;
    }
    #work-container-previous .work-fields.work-row:hover {
        border-color: #b8cfe8 !important;
        box-shadow: 0 4px 14px rgba(3, 90, 179, 0.18) !important;
    }
    #work-exp-summary-panel-previous .wx-order-card {
        background: #ffffff !important;
        border: 1px solid #c8d8f5 !important;
        border-radius: 10px !important;
        box-shadow: 0 2px 10px rgba(3, 90, 179, 0.14) !important;
        padding: 12px 14px !important;
    }
    #work-container-previous .work-row-head {
        background: linear-gradient(135deg, #f7faff 0%, #fbfdff 100%) !important;
        border-bottom: 1px solid #dde5f3 !important;
    }
    #work-container-previous .work-row:not(.is-complete) .work-row-head {
        background: linear-gradient(135deg, #f7faff 0%, #fbfdff 100%) !important;
        border-bottom: 1px solid #dde5f3 !important;
    }
    #work-container-previous .work-row-head-actions .work-row-remove,
    #work-container-previous .work-row-remove.remove-work {
        color: #c1272d !important;
    }
    #work-container-previous .work-row-head-actions .work-row-remove .fa,
    #work-container-previous .work-row-remove.remove-work .fa {
        color: #c1272d !important;
    }
    /* Ensure 7a Submit is visible once the row is complete and still being edited */
    #work-container-previous .work-row.is-complete.work-row--expanded {
        overflow: visible !important;
    }
    #work-container-previous .work-row.is-complete.work-row--expanded .work-row-done-bar {
        display: block !important;
    }
</style>

{{-- BREADCRUMB --}}
<div class="fs-breadcrumb-bar">
    <div class="container">
        <ul id="breadcrumb">
            <li><a href="{{ route('dashboard')}}"><span class="fa fa-home"></span> Dashboard</a></li>
            <li><a href="#"><span class="fa fa-info-circle"></span> Form C Digitization</a></li>
        </ul>
    </div>
</div>

{{--  PAGE BODY --}}
<div class="fs-page-wrap">
    <div class="container">
        <div class="fs-card comp_certificate" data-select2-id="14">

            {{-- Card header --}}
            <div class="fs-card-header">
                <div class="header-titles">
                    <h5>Digitization of Supervisor Competency Certificate</h5>
                    <h5 class="tamil-title">மேற்பார்வையாளர் தகுதிச் சான்றிதழை மின்னணுமயமாக்குதல்</h5>
                    <span class="form-badge">FORM - C</span>
                </div>
            </div>

            {{-- Mandatory notice --}}
            <div class="fs-mandatory-bar">
                <span class="req-dot">*</span> Fields are Mandatory
            </div>

            {{-- Form body --}}
            <div class="fs-form-body fs-form apply-card">

                <form id="competency_form_ws" class="apply-form" enctype="multipart/form-data">

                    {{-- SECTIONS 1–5 — Name, Father's Name, Email, Address, DOB/Age --}}
                    <div class="fs-section">
                        <div class="fs-section-body">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <div class="fs-section-header fs-section-header--in-grid">
                                        <span class="fs-section-num">1</span>
                                        <div>
                                            <div class="fs-section-title">Applicant's Name <span class="section-req">*</span></div>
                                            <div class="fs-section-tamil">விண்ணப்பதாரர் பெயர்</div>
                                        </div>
                                    </div>
                                    <input autocomplete="off" class="form-control" id="Applicant_Name" name="applicant_name" type="text"
                                        value="{{ $user['salutation'].' '.$user['applicant_name'] }}" readonly>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="fs-section-header fs-section-header--in-grid">
                                        <span class="fs-section-num">2</span>
                                        <div>
                                            <div class="fs-section-title">Father's Name <span class="section-req">*</span></div>
                                            <div class="fs-section-tamil">தகப்பனார் பெயர்</div>
                                        </div>
                                    </div>
                                    <input autocomplete="off" class="form-control" id="Fathers_Name" name="fathers_name"
                                        type="text" value="{{ isset($application) ? $application->fathers_name : '' }}" maxlength="80"
                                        placeholder="e.g. Arun Srinivasakannan G">
                                    <span class="error-message text-danger" style="font-size:.78rem;"></span>
                                </div>
                                <div class="col-12 col-md-6 mb-2 mt-1">
                                    <div class="fs-section-header fs-section-header--in-grid">
                                        <span class="fs-section-num">3</span>
                                        <div>
                                            <div class="fs-section-title">Email ID <span class="section-req">*</span></div>
                                            <div class="fs-section-tamil">மின்னஞ்சல் முகவரி</div>
                                        </div>
                                    </div>
                                    <input autocomplete="email" class="form-control" id="applicant_email" name="applicant_email" type="email"
                                        maxlength="191" required
                                        value="{{ old('applicant_email', isset($application) ? ($application->applicant_email ?? '') : (Auth::user()->email ?? '')) }}">
                                    <span class="error-message text-danger" style="font-size:.78rem;"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="fs-section">
                        <div class="fs-section-body">
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3 mb-md-0">
                                    <div class="fs-section-header fs-section-header--in-grid">
                                        <span class="fs-section-num">4</span>
                                        <div>
                                            <div class="fs-section-title">
                                                Applicant Address <span class="section-req">*</span>
                                                <span class="section-hint">(To be clear)</span>
                                            </div>
                                            <div class="fs-section-tamil">விண்ணப்பதாரர் முகவரி <span style="font-size:.72rem;">(தெளிவாக இருத்தல் வேண்டும்)</span></div>
                                        </div>
                                    </div>
                                    {{-- Structured address (shared with apply-form-s / edit views);
                                         applicant_address.js composes the parts into the hidden #applicants_address. --}}
                                    @include('user_login.partials.applicant-address-fields')
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="fs-dob-age-badge-row">
                                        <span class="fs-section-num">5</span>
                                        <div class="fs-dob-age-badge-row__body">
                                            <div class="row fs-dob-age-pair align-items-start">
                                                <div class="col-12 col-sm-6 mb-2 mb-sm-0">
                                                    <div class="fs-dob-age-label-block">
                                                        <div class="fs-field-label">(i) D.O.B <span class="req">*</span></div>
                                                        <div class="fs-field-tamil">பிறந்த நாள், மாதம், வருடம்</div>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-sm-6">
                                                    <div class="fs-dob-age-label-block">
                                                        <div class="fs-field-label">(ii) Age</div>
                                                        <div class="fs-field-tamil">வயது</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row fs-dob-age-pair align-items-start mx-0">
                                                <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                                                    <input autocomplete="off" class="form-control" id="d_o_b" name="d_o_b"
                                                        type="text" placeholder="DD-MM-YYYY"
                                                        value="{{ isset($application) ? $application->d_o_b : '' }}">
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

                    {{-- SECTION 6 — Education --}}
                    @include('user_login.partials.form-s-education-section', ['eduMode' => 'digitization', 'eduSectionNo' => 6])

                    {{-- SECTION 7 — Work Experience --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">7</span>
                            <div>
                                <div class="fs-section-title">
                                    Details of Previous and Current Work experiences
                                    <span class="section-req">*</span>
                                    <span class="section-hint">(Upload the documents)</span>
                                </div>
                                <div class="fs-section-tamil">முந்தைய மற்றும் தற்போதைய பணியனுபவ விவரங்கள் <span class="section-req">*</span> <span style="font-size:.72rem;">(ஆவணங்களை பதிவேற்ற வேண்டும்)</span></div>
                            </div>
                        </div>
                        <div class="fs-section-body">
                            @include('user_login.partials.form-s-work-exp-7ab-body', [
                                'exp_details' => $exp_details ?? collect(),
                                'showContractorNotice' => true,
                                'contractorDetails' => $contractorDetails ?? null,
                            ])
                        </div>
                    </div>

                    {{-- SECTION 8 — Previous License --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">8</span>
                            <div>
                                @include('user_login.partials.form-s-question-8-head')
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
                                        <div class="fs-field-label">Certificate Number <span class="req">*</span> <span class="text-muted" style="font-size:.75rem;font-weight:400;">(eg. 12345)</span></div>
                                        <input autocomplete="off" class="form-control verify-input" id="previously_number" name="previously_number" type="text" inputmode="numeric" pattern="[0-9]{5}"
                                            data-type="license" data-cert-digits="5" data-error="#licenseError" data-msg="#license_messagdfde"
                                            placeholder="Certificate Number" value="" maxlength="5">
                                        <input type="hidden" id="l_verify" name="l_verify" value="0">
                                        <span id="licenseError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">Date of First Issue <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-issue-date" id="previously_issue_date" name="previously_issue_date" type="date"
                                            data-error="#previouslyIssueDateError" max="{{ now()->toDateString() }}" value="">
                                        <span id="previouslyIssueDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">From date <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-valid-from" id="previously_valid_from" name="previously_valid_from" type="date"
                                            data-error="#previouslyFromDateError" max="{{ now()->toDateString() }}" value="">
                                        <span id="previouslyFromDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">To date <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-date" id="previously_valid_to" name="previously_valid_to" type="date"
                                            data-error="#dateError" value="">
                                        <span id="dateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    {{-- <div class="col-12 col-md-2">
                                        <button type="button" class="btn-verify verify-btn" data-type="license" data-url="{{ route('verifylicense') }}">
                                            <i class="fa fa-check-circle"></i> Verify
                                        </button>
                                    </div> --}}
                                </div>
                                <div class="mt-1">
                                    <span id="verify_result"></span>
                                    <span id="license_messagdfde"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 9 — Wireman Certificate --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">9</span>
                            <div>
                                @include('user_login.partials.form-s-question-9-head')
                            </div>
                        </div>
                        <div class="fs-section-body">
                            @php
                                $oldCertNo   = trim((string) request('old_cert_no', ''));
                                $oldExpiryRaw = trim((string) request('old_expiry_date', ''));
                                $oldExpiry   = $oldExpiryRaw !== '' ? \Carbon\Carbon::parse($oldExpiryRaw)->format('Y-m-d') : '';
                                $hasOldPrefill = $oldCertNo !== '';
                            @endphp
                            <div class="fs-radio-group mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input toggle-details" type="radio" name="previous_certificate" id="yesOption" data-target="#wireman_details" value="yes" {{ $hasOldPrefill ? 'checked' : '' }}>
                                    <label class="form-check-label" for="yesOption">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input toggle-details" type="radio" name="previous_certificate" id="noOption" data-target="#wireman_details" value="no" {{ $hasOldPrefill ? '' : 'checked' }}>
                                    <label class="form-check-label" for="noOption">No</label>
                                </div>
                            </div>
                            <div id="wireman_details" class="fs-toggle-panel" style="display:{{ $hasOldPrefill ? 'block' : 'none' }};">
                                <div class="row g-2 align-items-end">
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Certificate Number <span class="req">*</span> <span class="text-muted" style="font-size:.75rem;font-weight:400;">(eg. 123456)</span></div>
                                        <input class="form-control verify-input" id="certificate_no" name="competency_certificate_no" type="text" inputmode="numeric" pattern="[0-9]{6}"
                                            data-type="certificate" data-cert-digits="6" data-error="#certError" data-msg="#license_message"
                                            placeholder="Certificate Number" maxlength="6" value="{{ $oldCertNo }}">
                                        <input type="hidden" id="cert_verify" name="cert_verify" value="0">
                                        <span id="license_message" class="mt-1"></span>
                                        <span id="certError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">Date of First Issue <span class="req">*</span></div>
                                        <input class="form-control verify-issue-date" id="certificate_issue_date" name="certificate_issue_date"
                                            data-error="#certIssueDateError" type="date" max="{{ now()->toDateString() }}" value="">
                                        <span id="certIssueDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">From date <span class="req">*</span></div>
                                        <input class="form-control verify-valid-from" id="certificate_valid_from" name="certificate_valid_from"
                                            data-error="#certFromDateError" type="date" max="{{ now()->toDateString() }}" value="">
                                        <span id="certFromDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">To date <span class="req">*</span></div>
                                        <input class="form-control verify-date" id="certificate_valid_to" name="certificate_valid_to"
                                            data-error="#certDateError" type="date" value="{{ $oldExpiry }}">
                                        <span id="certDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                  {{--  <div class="col-12 col-md-2">
                                        <button type="button" class="btn-verify verify-btn" data-type="certificate" data-url="{{ route('verifylicense') }}">
                                            <i class="fa fa-check-circle"></i> Verify
                                        </button>
                                    </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 10 — Upload Documents --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">10</span>
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
                                    {{-- PAN --}}
                                    <tr>
                                        <td class="doc-serial">(ii)</td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">PAN Card Number</div>
                                            <div class="fs-field-tamil">நிரந்தர கணக்கு எண்</div>
                                        </td>
                                        <td style="min-width:180px;">
                                            <input type="text" class="form-control text-uppercase" name="pancard" id="pancard" maxlength="10" autocomplete="off" style="max-width:260px;" placeholder="e.g. ABCDE1234F">
                                            <span id="pancard-error" class="text-danger d-block" style="font-size:.78rem;"></span>
                                        </td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">(iii) Upload PAN Card Document</div>
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
                                        <td class="doc-serial">(iv)</td>
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

                    {{-- Declaration --}}
                    <div class="fs-declaration">
                        <label class="container">
                            <input type="checkbox" id="declarationCheckbox" required {{ isset($application) ? 'checked' : '' }}>
                            <span class="checkmark"></span>
                            <div class="decl-text">
                                I hereby declare that all the details mentioned above are correct and true to the best of my knowledge.<span class="req">*</span>
                                <span class="tamil">என் அறிவுக்கு எட்டியவரை மேலே குறிப்பிட்டுள்ள விவரங்கள் யாவும் சரியானவை எனவும் உண்மையானவை எனவும் உறுதி கூறுகிறேன்.</span>
                            </div>
                        </label>
                        <span id="checkboxError" class="text-danger mt-2 d-none" style="font-size:.82rem;">Please check the declaration box before proceeding.</span>
                    </div>

                    {{-- Hidden Fields --}}
                    <input type="hidden" id="login_id_store" name="login_id" value="{{ $user['user_id'] }}">
                    <input type="hidden" id="application_id" name="application_id" value="{{ $application_details->application_id ?? '' }}">
                    <input type="hidden" id="cc_digitization_temp_id" name="cc_digitization_temp_id" value="{{ $cc_digitization_temp_id ?? '' }}">
                    <input type="hidden" id="form_name" name="form_name" value="S">
                    <input type="hidden" id="license_name" name="license_name" value="C">
                    <input type="hidden" id="form_id" name="form_id" value="1">
                    <input type="hidden" id="appl_type" name="appl_type" value="D">
                    <input type="hidden" id="amount" name="amount" value="0">
                    <input type="hidden" id="form_action" name="form_action" value="draft">
                    @csrf

                    {{-- Action buttons --}}
                    <div class="fs-action-bar">
                        @if(! isset($application))
                        <button type="button" class="btn-fs-draft" id="saveDraftBtn"
                            data-url="{{ route('form.draft_submit') }}"
                            data-id="{{ $application_details->application_id ?? '' }}">
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

{{-- Draft saved modal --}}
<div id="draftModal" class="overlay-bg" style="display:none;">
    <div class="otp-modal">
        <h5><i class="fa fa-check-circle"></i> Your Application Details Saved Successfully</h5>
        <button onclick="closeDraftModal()">OK</button>
    </div>
</div>

<footer class="main-footer">
    @include('include.footer')

    {{-- Structured applicant address (section 4); needs jQuery from the footer. --}}
    <script src="{{ url('assets/js/applicant_address.js') }}?v={{ filemtime(public_path('assets/js/applicant_address.js')) }}"></script>
    <script src="{{ url('assets/js/digitization.js') }}?v={{ filemtime(public_path('assets/js/digitization.js')) }}"></script>
    <script src="{{ url('assets/js/form_s_education.js') }}?v={{ filemtime(public_path('assets/js/form_s_education.js')) }}"></script>
    <script>
        window.showContractorDetails = window.showContractorDetails || function (details) {
            var notice = document.getElementById("contractor-details-notice");
            if (!notice) {
                return;
            }
            var hasDetails = details && (details.licence_no || details.cl_type || details.contractor_name);
            if (hasDetails) {
                var cl = document.getElementById("contractor-cl-type");
                var no = document.getElementById("contractor-licence-no");
                var name = document.getElementById("contractor-name");
                if (cl) cl.textContent = details.cl_type || "";
                if (no) no.textContent = details.licence_no || "";
                if (name) name.textContent = details.contractor_name || "";
                notice.classList.remove("d-none");
                notice.style.removeProperty("display");
                window.contractorDetails = details;
            } else {
                notice.classList.add("d-none");
                notice.style.setProperty("display", "none", "important");
                window.contractorDetails = null;
            }
        };
        @if (!empty($contractorDetails) && is_array($contractorDetails))
        window.showContractorDetails(@json($contractorDetails));
        @endif
        $(document).ajaxSuccess(function (event, xhr, settings) {
            var url = (settings && settings.url) ? String(settings.url) : "";
            if (url.indexOf("storeDigitization") === -1 && url.indexOf("getContractorDetails") === -1) {
                return;
            }
            try {
                var response = typeof xhr.responseJSON === "object" && xhr.responseJSON
                    ? xhr.responseJSON
                    : JSON.parse(xhr.responseText || "{}");
                if (response && response.contractorDetails) {
                    window.showContractorDetails(response.contractorDetails);
                }
            } catch (e) {}
        });
    </script>

    @include('user_login.partials.form-s-work-exp-scripts', [
        'editFormName' => 'S',
        'showBoardMemberEmploymentType' => false,
    ])
    <script>
        (function () {
            var BOARD_MEMBER_TYPE = 'board_member_tnelb';

            function get7bWorkRow() {
                return $('#work-container-current .work-fields').first();
            }

            function sync7bSegmentedActive($input) {
                var $toggle = $('.fs-7b-board-toggle');
                $toggle.find('.fs-segmented-opt').removeClass('is-active');
                $input.closest('.fs-segmented-opt').addClass('is-active');
            }

            function apply7bBoardToggle(mode, isInit) {
                var $root = $('#fs-7b-root');
                var $row = get7bWorkRow();
                if (!$root.length) return;

                var isYes = mode === 'yes';
                $root.toggleClass('fs-7b-mode-board', isYes).toggleClass('fs-7b-mode-standard', !isYes);
                $('#fs-7b-board-details').toggleClass('d-none', !isYes);

                if (!$row.length) return;

                var $emp = $row.find('.work-employment-type');
                if (isYes) {
                    // Sync field must participate when board details are shown.
                    $emp.prop('disabled', false).prop('required', true);
                    if ($emp.val() !== BOARD_MEMBER_TYPE) {
                        $emp.val(BOARD_MEMBER_TYPE).trigger('change');
                    }
                    $row.addClass('work-row--expanded').removeClass('work-row--compact work-row--in-summary');
                } else {
                    // d-none does not remove HTML5 required — disable so validators ignore it.
                    $emp.prop('required', false).prop('disabled', true).val('');
                }
            }

            $(document).on('input change', '#work-container-current .work-fields :input', function () {
                if (($('input[name="current_work_board_member"]:checked').val() || 'no') !== 'yes') return;
                var $row = $(this).closest('.work-fields');
                if ($row.length) {
                    $row.addClass('work-row--expanded').removeClass('work-row--compact work-row--in-summary');
                }
            });

            $(document).ready(function () {
                $('input[name="current_work_board_member"]').on('change', function () {
                    sync7bSegmentedActive($(this));
                    apply7bBoardToggle($(this).val(), false);
                });

                var $checked = $('input[name="current_work_board_member"]:checked');
                if ($checked.length) {
                    sync7bSegmentedActive($checked);
                    apply7bBoardToggle($checked.val(), true);
                }
            });
        })();
    </script>

    <script>
        $(document).on('click', '.form-s-file-upload-btn:not(.form-s-file-upload-btn--table)', function(e) {
            e.preventDefault();
            var $file = $(this).closest('.form-s-file-upload-wrap').find('input[type="file"]').first();
            if ($file.length) $file.trigger('click');
        });

        function clearLocalPreview($fileInput) {
            var $wrap = $fileInput.closest('.form-s-file-upload-wrap');
            var $preview = $wrap.next('.local-file-preview');
            var oldUrl = $preview.data('blobUrl');
            if (oldUrl) URL.revokeObjectURL(oldUrl);
            $preview.remove();
            $fileInput.removeAttr('data-has-local-file');
        }

        function clearWorkRowUploadErrors($scope) {
            if (!$scope || !$scope.length) return;
            $scope.find('.error-message').each(function() {
                var txt = ($(this).text() || '').toLowerCase();
                if (
                    txt.indexOf('supporting document is required') !== -1 ||
                    txt.indexOf('relieving letter is required') !== -1 ||
                    txt.indexOf('highest transformer capacity') !== -1 ||
                    txt.indexOf('only pdf') !== -1 ||
                    txt.indexOf('file size permitted') !== -1
                ) {
                    $(this).remove();
                }
            });
        }

        $(document).on('change', 'input[type="file"][name="education_document[]"], input[type="file"][name="work_document[]"], input[type="file"][name="work_relieving_letter[]"]', function() {
            var $input = $(this);
            clearLocalPreview($input);
            var file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) return;
            var allowed = ['application/pdf', 'image/jpeg', 'image/png'];
            var maxSize = 200 * 1024;
            if (allowed.indexOf(file.type) === -1) { window.alert('Only PDF, JPG, PNG files are allowed.'); this.value = ''; $input.removeAttr('data-has-local-file'); return; }
            if (file.size > maxSize) { window.alert('File size should not exceed 200 KB.'); this.value = ''; $input.removeAttr('data-has-local-file'); return; }
            $input.attr('data-has-local-file', '1');
            var blobUrl = URL.createObjectURL(file);
            var isImage = file.type.indexOf('image/') === 0;
            var $preview = $('<div class="local-file-preview"></div>').data('blobUrl', blobUrl);
            if (isImage) $preview.append($('<img>', { src: blobUrl, class: 'img-preview', alt: 'Selected image preview' }));
            $preview.append($('<a>', { href: blobUrl, target: '_blank', rel: 'noopener noreferrer', class: 'preview-link' })
                .html(isImage ? '<i class="fa fa-image"></i> Preview image' : '<i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View Document'));
            $input.closest('.form-s-file-upload-wrap').after($preview);
            clearWorkRowUploadErrors($input.closest('.work-fields'));
            /* Re-evaluate 7a complete/Submit after preview is attached (runs after shared work-exp handlers). */
            var $workRow = $input.closest('.work-fields');
            if ($workRow.length) {
                $workRow.trigger('wx:recheck-complete');
            }
        });

        $(document).on('change', '#pancard_doc', function() {
            var $input = $(this);
            clearLocalPreview($input);
            var file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) return;
            var minSize = 10 * 1024, maxSize = 250 * 1024;
            if (file.type !== 'application/pdf') { window.alert('Only PDF files are allowed.'); this.value = ''; return; }
            if (file.size < minSize) { window.alert('File size must be at least 10 KB.'); this.value = ''; return; }
            if (file.size > maxSize) { window.alert('File size should not exceed 250 KB.'); this.value = ''; return; }
            var blobUrl = URL.createObjectURL(file);
            var $preview = $('<div class="local-file-preview"></div>').data('blobUrl', blobUrl);
            $preview.append($('<a>', { href: blobUrl, target: '_blank', rel: 'noopener noreferrer', class: 'preview-link' })
                .html('<i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View Document'));
            $input.closest('.form-s-file-upload-wrap').after($preview);
        });

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
                previewEl.onload = function() {
                    URL.revokeObjectURL(blobUrl);
                };
                previewEl.src = blobUrl;
                previewEl.style.display = 'block';
                placeholderEl.style.display = 'none';
            });
        }

        bindImageUploadPreview('upload_photo', 'photo_preview', 'upload_photo_name', 'photo_placeholder');
        bindImageUploadPreview('upload_sign', 'sign_preview', 'upload_sign_name', 'sign_placeholder');

    </script>
</footer>