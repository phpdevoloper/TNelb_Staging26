@include('include.header')
<style>
    /* ── Reset helpers ────────────────────────────────── */
    .fs-form hr {
        margin: 0;
        border: 0;
        border-top: 1px solid #e3e8f0;
    }
    .fs-form .form-group { margin-bottom: 0; }

    /* ── SweetAlert overrides ─────────────────────────── */
    .swal2-popup li            { font-size: 15px; margin-bottom: 8px; }
    .swal2-popup li ul         { margin-left: 15px; }

    /* ── Page wrapper ─────────────────────────────────── */
    .fs-page-wrap {
        background: #f0f4f9;
        min-height: 100vh;
        padding-bottom: 48px;
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
    .fs-card-header .instructions-link {
        text-align: right;
        margin-top: 0;
        margin-bottom: 0;
        font-size: .82rem;
        line-height: 1;
    }
    .fs-card-header .instructions-link a {
        color: rgba(255,255,255,.9);
        text-decoration: none;
        border-bottom: 1px dashed rgba(255,255,255,.5);
    }
    .fs-card-header .instructions-link a:hover { color: #fff; border-bottom-color: #fff; }

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
    #work-table .form-s-file-upload-wrap--combined {
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
    #education-table .form-s-file-upload-wrap--combined .form-control,
    #work-table .form-s-file-upload-wrap--combined .form-control,
    #education-table .form-s-file-upload-wrap--combined input[type="file"],
    #work-table .form-s-file-upload-wrap--combined input[type="file"] {
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

    /* ── Work-table specific ──────────────────────────── */
    #work-table.work-exp-table { font-size: .8125rem; width: 100%; max-width: 100%; }
    #work-table.work-exp-table thead th { font-size: .78rem; font-weight: 600; padding: .35rem .4rem; vertical-align: middle; line-height: 1.25; }
    #work-table.work-exp-table tbody td { padding: .4rem .45rem; vertical-align: middle; }
    #work-table .work-employer-cell { vertical-align: top; }
    #work-table .work-employer-label-row { display: flex; align-items: baseline; margin-bottom: .15rem; min-width: 0; }
    #work-table .work-employer-label { font-size: .7rem; color: #6c757d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1 1 0; min-width: 0; }
    #work-table .work-employer-req { font-size: .7rem; flex: 0 0 auto; }
    #work-table .work-exp-col-type { width: 12%; max-width: 10.5rem; }
    #work-table .work-exp-col-employer { width: 16%; max-width: 12rem; }
    #work-table .work-exp-col-years { width: 34%; min-width: 19.5rem; }
    #work-table .work-exp-col-designation { width: 12%; }
    #work-table .work-exp-col-upload { width: 22%; }
    #work-table .work-exp-col-sno { width: 2.5rem; min-width: 2.5rem; white-space: nowrap; text-align: center; }
    #work-table .work-exp-col-actions { width: 2.75rem; white-space: nowrap; }
    #work-table .work-exp-upload-head { font-size: .72rem; line-height: 1.2; }
    #work-table .work-exp-upload-head .file-limit { font-size: .68rem; }
    #work-table .work-exp-inline { display: flex; flex-wrap: nowrap; align-items: flex-end; gap: .25rem; }
    #work-table .work-exp-date-group { flex: 1 1 auto; min-width: 7.5rem; max-width: 10rem; }
    #work-table .work-exp-total-inline { flex: 0 0 5.75rem; min-width: 5.5rem; max-width: 6.5rem; }
    #work-table .work-exp-label-fromto { font-size: .72rem; font-weight: 600; color: #212529; margin-bottom: .2rem; line-height: 1.2; }
    #work-table thead th.work-exp-col-years { vertical-align: top; }
    #work-table .work-exp-years-title { text-align: center; margin-bottom: .35rem; font-weight: 600; font-size: .78rem; }
    #work-table .work-exp-inline--head { align-items: flex-end; border-top: 1px solid #dee2e6; padding-top: .25rem; }
    #work-table .work-exp-inline--head .work-exp-label-fromto { margin-bottom: 0; }
    #work-table .work-exp-inline--head .work-exp-date-group,
    #work-table .work-exp-inline--head .work-exp-total-inline { position: relative; padding-left: .35rem; }
    #work-table .work-exp-inline--head .work-exp-date-group + .work-exp-date-group,
    #work-table .work-exp-inline--head .work-exp-total-inline { border-left: 1px solid #dee2e6; }
    #work-table .work-date-from,
    #work-table .work-date-to { font-size: .8125rem; color: #212529; min-width: 9.5rem; width: 100%; }
    #work-table .work-duration-ymd {
        display: flex;
        gap: .18rem;
        align-items: flex-end;
        justify-content: center;
        width: 100%;
    }
    #work-table .work-duration-cell { flex: 1 1 0; min-width: 0; text-align: center; }
    #work-table .work-duration-label {
        display: block;
        font-size: .6rem;
        font-weight: 600;
        color: #6c757d;
        line-height: 1;
        margin-bottom: .1rem;
    }
    #work-table .work-duration-y,
    #work-table .work-duration-m,
    #work-table .work-duration-d {
        font-size: .66rem;
        padding: .16rem .06rem;
        line-height: 1.25;
        text-align: center;
        width: 100%;
        min-width: 0;
    }
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

    .prv-thumb-wrap { display:flex; gap:20px; }
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

    /* ── Validation messages — uniform size ─────────── */
    .fs-form .text-danger,
    .fs-form .error-message,
    .fs-form .error,
    .fs-form span[id$="-error"],
    .fs-form span[class*="error"],
    .fs-form #checkboxError {
        font-size: .78rem !important;
        line-height: 1.3;
        display: block;
        margin-top: 2px;
    }

    /* ── PDF icon always red ─────────────────────────── */
    .fa-file-pdf-o { color: #d9363e !important; }

    /* ── FontAwesome fix ──────────────────────────────── */
    .comp_certificate .btn .fa,
    .comp_certificate .btn i.fa,
    .comp_certificate .btn-tbl-add .fa,
    .comp_certificate .btn-tbl-add i.fa,
    .comp_certificate .btn-tbl-remove .fa,
    .comp_certificate .btn-tbl-remove i.fa,
    .comp_certificate .form-s-file-upload-btn .fa,
    .comp_certificate .form-s-file-upload-btn i.fa {
        font-family: 'FontAwesome';
        display: inline-block;
    }
</style>

{{-- ░░ BREADCRUMB ░░ --}}
<div class="fs-breadcrumb-bar">
    <div class="container">
        <ul id="breadcrumb">
            <li><a href="{{ route('dashboard')}}"><span class="fa fa-home"></span> Dashboard</a></li>
            <li><a href="#"><span class="fa fa-info-circle"></span> Form S</a></li>
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
                    <h5>Application for Supervisor Competency Certificate</h5>
                    <h5 class="tamil-title">மேற்பார்வையாளர் தகுதி சான்றிதழ் பெறுவதற்கான விண்ணப்பம்</h5>
                    <span class="form-badge">FORM - S / Certificate C</span>
                </div>
                <div class="instructions-link">
                    <span class="text-white font-weight-bold" style="font-size:.82rem;">Instructions &nbsp;</span>
                    <a href="{{url('assets/pdf/form_s_notes.pdf')}}" target="_blank">English <i class="fa fa-file-pdf-o"></i> (8 KB)</a>
                </div>
            </div>

            {{-- ── Mandatory notice ── --}}
            <div class="fs-mandatory-bar">
                <span class="req-dot">*</span> Fields are Mandatory
            </div>

            {{-- ── Form body ── --}}
            <div class="fs-form-body fs-form apply-card">

                <form id="competency_form_ws" class="apply-form" enctype="multipart/form-data">

                    {{-- ═══ SECTIONS 1–5 — Name, Father's Name, Email, Address, DOB/Age ═══ --}}
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
                                        type="text" value="{{ isset($application) ? $application->fathers_name : '' }}" maxlength="80">
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
                                    <textarea rows="3" class="form-control" id="applicants_address" name="applicants_address" maxlength="255">{{Auth::user()->address}}</textarea>
                                    <span id="applicants_address_error" class="text-danger" style="font-size:.78rem;"></span>
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
                                                        type="text" placeholder="DD/MM/YYYY"
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

                    {{-- ═══ SECTION 6 — Education ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">6</span>
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
                                            <th rowspan="2">University / Institute</th>
                                            <th colspan="2" class="text-center">Month & Year of Passing</th>
                                            <th rowspan="2">Certificate No</th>
                                            <th class="text-center" rowspan="2">Upload Document
                                                <br><span class="file-limit">File type: PDF(Min 5 KB To Max 200 KB)</span>
                                            </th>
                                            <th class="text-center p-1" rowspan="2">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-add add-more py-1 px-2" title="Add row">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
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
                                                <select class="form-control" name="educational_level[]">
                                                    <option selected disabled>Select Education</option>
                                                    <option value="DEE">Diploma(Electrical Engineering)</option>
                                                    <option value="BEE">B.E(Electrical Engineering)</option>
                                                    <option value="MEE">M.E(Electrical Engineering)</option>
                                                    <option value="AMIE">A pass in AMIE</option>
                                                </select>
                                            </td>
                                            <td><input type="text" class="form-control" name="institute_name[]" maxlength="80"></td>
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
                                                    <button type="button" class="btn-tbl-remove remove-education py-1 px-2" title="Remove row">
                                                        <i class="fa fa-trash-o"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 7 — Work Experience ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">7</span>
                            <div>
                                <div class="fs-section-title">
                                    Details of Previous and Current Work experiences
                                    <span class="section-req">*</span>
                                    <span class="section-hint">(Upload the documents)</span>
                                </div>
                                <div class="fs-section-tamil">பெற்றுள்ள முந்தைய மற்றும் தற்போதைய அனுபவங்களின் விவரங்கள் <span class="section-req">*</span> <span style="font-size:.72rem;">(ஆவணங்களை பதிவேற்ற வேண்டும்)</span></div>
                            </div>
                        </div>
                        <div class="fs-section-body">
                            <div class="fs-table-wrap">
                                <table class="table table-bordered table-sm work-exp-table" id="work-table">
                                    <thead>
                                        <tr>
                                            <th class="work-exp-col-sno text-center">S.No</th>
                                            <th class="work-exp-col-type">Employment type</th>
                                            <th class="work-exp-col-employer">Employer / organization</th>
                                            <th class="work-exp-col-years work-exp-years-head" scope="col">
                                                <div class="work-exp-years-title">Year of Experience</div>
                                                <div class="work-exp-inline work-exp-inline--head">
                                                    <div class="work-exp-date-group">
                                                        <span class="work-exp-label-fromto d-block">From (date)</span>
                                                    </div>
                                                    <div class="work-exp-date-group">
                                                        <span class="work-exp-label-fromto d-block">To (date)</span>
                                                    </div>
                                                    <div class="work-exp-total-inline">
                                                        <span class="work-exp-label-fromto d-block">Total yrs</span>
                                                    </div>
                                                </div>
                                            </th>
                                            <th class="work-exp-col-designation">Designation</th>
                                            <th class="text-center work-exp-col-upload work-exp-upload-head">Upload Document
                                                <br><span class="file-limit">File type: PDF(Min 5 KB To Max 200 KB)</span>
                                            </th>
                                            <th class="work-exp-col-actions text-center p-1">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-add add-more-work py-1 px-2" title="Add row">
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                </div>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="work-container">
                                        <tr class="work-fields">
                                            <td class="work-serial text-center">1</td>
                                            <td class="work-exp-col-type">
                                                <select class="form-control form-control-sm work-employment-type" name="work_employment_type[]" required>
                                                    <option value="" selected disabled>Select type</option>
                                                    <option value="company">Company</option>
                                                    <option value="contractor">Contractor</option>
                                                    <option value="apprentice">Apprentice</option>
                                                    <option value="electrical_inspector">Government / Quasi Government / Board</option>
                                                    <option value="retired_employees">Retired Employees</option>
                                                </select>
                                            </td>
                                            <td class="work-employer-cell work-exp-col-employer">
                                                <div class="work-employer-label-row">
                                                    <span class="work-employer-label">—</span><span class="text-danger work-employer-req" style="display:none;"> *</span>
                                                </div>
                                                <input type="text" class="form-control form-control-sm work-employer-input" name="work_employer_name[]" maxlength="120" autocomplete="off" disabled>
                                                <div class="work-block work-block--intimation mt-1" style="display:none;">
                                                    <label class="small mb-0" style="font-size:.7rem;display:flex;align-items:center;gap:2px;flex-wrap:nowrap;"><span style="white-space:nowrap;">Intimation letter</span><span class="text-danger flex-shrink-0">*</span></label>
                                                    <input type="date" class="form-control form-control-sm work-intimation-date" name="work_intimation_date[]">
                                                </div>
                                            </td>
                                            <td class="work-exp-col-years">
                                                <div class="work-exp-inline">
                                                    <div class="work-exp-date-group">
                                                        <input type="date" class="form-control form-control-sm work-date-from" name="work_date_from[]" title="From date" aria-label="Year of experience from date">
                                                    </div>
                                                    <div class="work-exp-date-group">
                                                        <input type="date" class="form-control form-control-sm work-date-to" name="work_date_to[]" title="To date" aria-label="Year of experience to date">
                                                    </div>
                                                    <div class="work-exp-total-inline">
                                                        <div class="work-duration-ymd" role="group" aria-label="Duration (years, months, days from dates)">
                                                            <div class="work-duration-cell">
                                                                <span class="work-duration-label">Yrs</span>
                                                                <input type="text" class="form-control form-control-sm work-duration-y" readonly inputmode="none" tabindex="-1" title="Years" aria-label="Years in this period">
                                                            </div>
                                                            <div class="work-duration-cell">
                                                                <span class="work-duration-label">Mo</span>
                                                                <input type="text" class="form-control form-control-sm work-duration-m" readonly inputmode="none" tabindex="-1" title="Months" aria-label="Months in this period">
                                                            </div>
                                                            <div class="work-duration-cell">
                                                                <span class="work-duration-label">Days</span>
                                                                <input type="text" class="form-control form-control-sm work-duration-d" readonly inputmode="none" tabindex="-1" title="Days" aria-label="Days in this period">
                                                            </div>
                                                        </div>
                                                        <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]" value="">
                                                    </div>
                                                </div>
                                                <input type="hidden" name="work_level[]" class="work-level-sync" value="" tabindex="-1" aria-hidden="true">
                                                <input type="hidden" name="experience[]" class="experience-sync" value="" tabindex="-1" aria-hidden="true">
                                            </td>
                                            <td class="work-exp-col-designation">
                                                <input autocomplete="off" class="form-control form-control-sm" name="designation[]" type="text" maxlength="80">
                                            </td>
                                            <td class="work-exp-col-upload">
                                                <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="work">
                                                    <input class="form-control form-control-sm p-1" name="work_document[]" type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png">
                                                </div>
                                            </td>
                                            <td class="work-exp-col-actions text-center p-1">
                                                <div class="form-s-actions-stack">
                                                    <button type="button" class="btn-tbl-remove remove-work py-1 px-2" title="Remove row">
                                                        <i class="fa fa-trash-o"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 8 — Previous License ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">8</span>
                            <div>
                                <div class="fs-section-title">Do you possess a Supervisor Competency Certificate issued by this Board? If yes, please furnish the details.</div>
                                <div class="fs-section-tamil">இந்த வாரியத்தால் வழங்கப்பட்ட மேற்பார்வையாளர் தகுதி சான்றிதழ் உங்களிடம் உள்ளதா? ஆம் என்றால் அதன் குறிப்பு எண் மற்றும் தேதியை குறிப்பிடுக</div>
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
                                        <div class="fs-field-label">Certificate Number <span class="req">*</span> <span class="text-muted" style="font-size:.75rem;font-weight:400;">(eg. C1234)</span></div>
                                        <input autocomplete="off" class="form-control verify-input" id="previously_number" name="previously_number" type="text"
                                            data-type="license" data-error="#licenseError" data-msg="#license_messagdfde"
                                            placeholder="Certificate Number" value="" maxlength="80">
                                        <input type="hidden" id="l_verify" name="l_verify" value="0">
                                        <span id="licenseError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Date of First Issue <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-issue-date" id="previously_issue_date" name="previously_issue_date" type="date"
                                            data-error="#previouslyIssueDateError" value="">
                                        <span id="previouslyIssueDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Date of Expiry <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-date" id="previously_date" name="previously_date" type="date"
                                            data-error="#dateError" value="">
                                        <span id="dateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <button type="button" class="btn-verify verify-btn" data-type="license" data-url="{{ route('verifylicense') }}">
                                            <i class="fa fa-check-circle"></i> Verify
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-1">
                                    <span id="verify_result"></span>
                                    <span id="license_messagdfde"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 9 — Wireman Certificate ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">9</span>
                            <div>
                                <div class="fs-section-title">Do you possess Wireman Competency Certificate issued by this Board? If so furnish the details and surrender the same.</div>
                                <div class="fs-section-tamil">இந்த வாரியம் வழங்கிய கம்பி இணைப்பாளர் திறன் சான்றிதழ் உள்ளதா? இருந்தால், அதன் விவரங்களை வழங்கி, அதனை ஒப்படைக்கவும்.</div>
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
                                    <div class="col-12 col-md-4">
                                        <div class="fs-field-label">Certificate Number <span class="req">*</span> <span class="text-muted" style="font-size:.75rem;font-weight:400;">(eg. W1234)</span></div>
                                        <input class="form-control verify-input" id="certificate_no" name="competency_certificate_no" type="text"
                                            data-type="supervisor" data-error="#certError" data-msg="#license_message"
                                            placeholder="Certificate Number" maxlength="80" value="{{ $oldCertNo }}">
                                        <input type="hidden" id="cert_verify" name="cert_verify" value="0">
                                        <span id="license_message" class="mt-1"></span>
                                        <span id="certError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Date of First Issue <span class="req">*</span></div>
                                        <input class="form-control verify-issue-date" id="certificate_issue_date" name="certificate_issue_date"
                                            data-error="#certIssueDateError" type="date" value="">
                                        <span id="certIssueDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Date of Expiry <span class="req">*</span></div>
                                        <input class="form-control verify-date" id="certificate_date" name="certificate_date"
                                            data-error="#certDateError" type="date" value="{{ $oldExpiry }}">
                                        <span id="certDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <button type="button" class="btn-verify verify-btn" data-type="certificate" data-url="{{ route('verifylicense') }}">
                                            <i class="fa fa-check-circle"></i> Verify
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 10 — Upload Documents ═══ --}}
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
                                I hereby declare that the particulars stated above are correct and true to the best of my knowledge. <br>
                                I request that I may be granted a Supervisor Competency Certificate.<span class="req">*</span>
                                <span class="tamil">என் அறிவுக்கு எட்டியவரை மேலே குறிப்பிட்டுள்ள விவரங்கள் யாவும் சரியானவை எனவும் உண்மையானவை எனவும் உறுதி கூறுகிறேன். <br> எனக்கு மேற்பார்வையாளர் திறன் சான்றிதழ் வழங்குமாறு கேட்டுக்கொள்கிறேன்.</span>
                            </div>
                        </label>
                        <span id="checkboxError" class="text-danger mt-2 d-block" style="display:none!important;font-size:.82rem;">Please check the declaration box before proceeding.</span>
                    </div>

                    {{-- Hidden fields --}}
                    <input type="hidden" id="login_id_store" name="login_id" value="{{ $user['user_id'] }}">
                    <input type="hidden" id="application_id" name="application_id" value="{{ $application->id ?? '' }}">
                    <input type="hidden" id="form_name" name="form_name" value="S">
                    <input type="hidden" id="license_name" name="license_name" value="C">
                    <input type="hidden" id="form_id" name="form_id" value="1">
                    <input type="hidden" id="appl_type" name="appl_type" value="N">
                    <input type="hidden" id="form_action" name="form_action" value="draft">
                    @csrf

                    {{-- ── Action buttons ── --}}
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

        {{-- Header --}}
        <div class="prv-header">
            <div class="prv-header-left">
                <h5><i class="fa fa-file-text-o"></i> Application Preview <span class="prv-badge">FORM - S / Certificate C</span></h5>
                <div class="prv-subtitle">Please verify all your details before proceeding to payment</div>
            </div>
            <button class="prv-close" onclick="closePreviewModal();if(typeof window._prvResolve==='function'){window._prvResolve(false);window._prvResolve=null;}" title="Close preview">&times;</button>
        </div>

        {{-- Scrollable body --}}
        <div class="prv-body" id="prvBody">

            {{-- Section 1&2: Personal Info --}}
            <div class="prv-section">
                <div class="prv-section-hd">
                    <span class="prv-section-num">1</span>
                    <span class="prv-section-title">Personal Information</span>
                </div>
                <div class="prv-section-body">
                    <div class="row">
                        {{-- Photo & Signature alongside personal details --}}
                        <div class="col-12 col-md-auto mb-3 mb-md-0 d-flex align-items-start" style="gap:12px;">
                            <div class="prv-thumb text-center">
                                <div id="prv_photo_wrap"><div class="prv-no-img">No Photo</div></div>
                                <span>Photo</span>
                            </div>
                            <div class="prv-thumb prv-thumb-sign text-center">
                                <div id="prv_sign_wrap"><div class="prv-no-img" style="width:120px;height:46px;">No Signature</div></div>
                                <span>Signature</span>
                            </div>
                        </div>
                        <div class="col-12 col-md">
                            <div class="row">
                                <div class="col-12 col-sm-6">
                                    <div class="prv-field">
                                        <div class="prv-label">Applicant's Name</div>
                                        <div class="prv-value" id="prv_name">—</div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="prv-field">
                                        <div class="prv-label">Father's Name</div>
                                        <div class="prv-value" id="prv_fathers_name">—</div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="prv-field">
                                        <div class="prv-label">Email ID</div>
                                        <div class="prv-value" id="prv_email">—</div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <div class="prv-field">
                                        <div class="prv-label">Address</div>
                                        <div class="prv-value" id="prv_address" style="white-space:pre-line;">—</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="prv-field">
                                        <div class="prv-label">Date of Birth</div>
                                        <div class="prv-value" id="prv_dob">—</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <div class="prv-field">
                                        <div class="prv-label">Age</div>
                                        <div class="prv-value" id="prv_age">—</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 6: Education --}}
            <div class="prv-section">
                <div class="prv-section-hd">
                    <span class="prv-section-num">6</span>
                    <span class="prv-section-title">Educational / Technical Qualification Details</span>
                </div>
                <div class="prv-section-body p-0">
                    <div style="overflow-x:auto;">
                        <table class="prv-table" id="prv_edu_table">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Education Level</th>
                                    <th>Institution / School Name</th>
                                    <th>Month</th>
                                    <th>Year</th>
                                    <th>Certificate No</th>
                                    <th>Document</th>
                                </tr>
                            </thead>
                            <tbody id="prv_edu_body">
                                <tr><td colspan="7" class="text-center text-muted py-3">—</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Section 7: Work Experience --}}
            <div class="prv-section">
                <div class="prv-section-hd">
                    <span class="prv-section-num">7</span>
                    <span class="prv-section-title">Work Experience Details</span>
                </div>
                <div class="prv-section-body p-0">
                    <div style="overflow-x:auto;">
                        <table class="prv-table" id="prv_work_table">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th>Employment Type</th>
                                    <th>Employer / Organization</th>
                                    <th>From Date</th>
                                    <th>To Date</th>
                                    <th>Total Yrs</th>
                                    <th>Designation</th>
                                    <th>Document</th>
                                </tr>
                            </thead>
                            <tbody id="prv_work_body">
                                <tr><td colspan="8" class="text-center text-muted py-3">—</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Sections 8 & 9 side by side --}}
            <div class="row" style="gap:0;">
                <div class="col-12 col-md-6 pr-md-1">
                    <div class="prv-section h-100">
                        <div class="prv-section-hd">
                            <span class="prv-section-num">8</span>
                            <span class="prv-section-title">Previously Applied for Electrical Assistant Qualification Certificate</span>
                        </div>
                        <div class="prv-section-body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span style="font-size:.8rem;color:#5a7299;font-weight:600;">Applied Previously:</span>
                                <span id="prv_prev_license_yn">—</span>
                            </div>
                            <div id="prv_prev_details_block" style="display:none;">
                                <div class="row">
                                    <div class="col-12 col-sm-4">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">Certificate No</div>
                                            <div class="prv-value" id="prv_prev_cert_no">—</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">Date of First Issue</div>
                                            <div class="prv-value" id="prv_prev_issue_date">—</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-4">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">Date of Expiry</div>
                                            <div class="prv-value" id="prv_prev_expiry_date">—</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 pl-md-1 mt-3 mt-md-0">
                    <div class="prv-section h-100">
                        <div class="prv-section-hd">
                            <span class="prv-section-num">9</span>
                            <span class="prv-section-title">Wireman Competency Certificate issued by this Board</span>
                        </div>
                        <div class="prv-section-body">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span style="font-size:.8rem;color:#5a7299;font-weight:600;">Possess Certificate:</span>
                                <span id="prv_wireman_yn">—</span>
                            </div>
                            <div id="prv_wireman_details_block" style="display:none;">
                                <div class="row">
                                    <div class="col-12 col-sm-6">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">Certificate No</div>
                                            <div class="prv-value" id="prv_wireman_cert_no">—</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">Date of Expiry</div>
                                            <div class="prv-value" id="prv_wireman_expiry">—</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 10: Documents --}}
            <div class="prv-section">
                <div class="prv-section-hd">
                    <span class="prv-section-num">10</span>
                    <span class="prv-section-title">Identity &amp; Uploaded Documents</span>
                </div>
                <div class="prv-section-body">
                    {{-- Aadhaar row --}}
                    <div class="row align-items-center mb-2">
                        <div class="col-5 col-md-3">
                            <div class="prv-field mb-0">
                                <div class="prv-label">Aadhaar Number</div>
                                <div class="prv-value" id="prv_aadhaar">—</div>
                            </div>
                        </div>
                        <div class="col-7 col-md-4">
                            <div class="prv-field mb-0">
                                <div class="prv-label">Aadhaar Document</div>
                                <div class="prv-value" id="prv_aadhaar_doc">—</div>
                            </div>
                        </div>
                    </div>
                    {{-- PAN row --}}
                    <div class="row align-items-center">
                        <div class="col-5 col-md-3">
                            <div class="prv-field mb-0">
                                <div class="prv-label">PAN Card Number</div>
                                <div class="prv-value" id="prv_pan">—</div>
                            </div>
                        </div>
                        <div class="col-7 col-md-4">
                            <div class="prv-field mb-0">
                                <div class="prv-label">PAN Document</div>
                                <div class="prv-value" id="prv_pan_doc">—</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /prv-body --}}

        {{-- Footer --}}
        <div class="prv-footer">
            <label class="prv-confirm-check">
                <input type="checkbox" id="prvConfirmCheck">
                I confirm that all the above details are correct and true
            </label>
            <button type="button" class="prv-btn-back" onclick="closePreviewModal();if(typeof window._prvResolve==='function'){window._prvResolve(false);window._prvResolve=null;}">
                <i class="fa fa-arrow-left"></i> Back to Edit
            </button>
            <button type="button" class="prv-btn-confirm" id="prvConfirmBtn" disabled>
                <i class="fa fa-credit-card"></i> Confirm &amp; Proceed to Payment
            </button>
        </div>

    </div>
</div>

<footer class="main-footer">
    @include('include.footer')

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

        $(document).on('change', 'input[type="file"][name="education_document[]"], input[type="file"][name="work_document[]"]', function() {
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
        });

        $(document).on('change', '#aadhaar_doc, #pancard_doc', function() {
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

        document.addEventListener("click", function(e) {
            let container = document.getElementById("education-container");
            let educationRows = container.querySelectorAll(".education-fields");
            const refreshEducationSerials = () => {
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
    <option value="DEE">Diploma(Electrical Engineering)</option>
    <option value="BEE">B.E(Electrical Engineering)</option>
    <option value="MEE">M.E(Electrical Engineering)</option>
    <option value="AMIE">A pass in AMIE</option>
</select></td>
<td><input type="text" class="form-control" name="institute_name[]" maxlength="80" required></td>
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
<td><div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="education"><input type="file" class="form-control" name="education_document[]" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png"></div></td>
<td class="form-s-actions-cell text-center p-1"><div class="form-s-actions-stack"><button type="button" class="btn-tbl-remove remove-education py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button></div></td>`;
                container.appendChild(newRow);
                refreshEducationSerials();
            }

            if (e.target.closest(".remove-education")) {
                if (educationRows.length <= 1) {
                    $('#education-table').next('.education-error').remove();
                    $('<div class="text-danger mt-2 education-error">You must have at least one education entry.</div>').insertAfter('#education-table');
                    setTimeout(() => { $('.education-error').fadeOut(); }, 7000);
                    return;
                }
                e.target.closest("tr").remove();
                refreshEducationSerials();
            }
        });
    </script>

    <script>
        (function() {
            var EMP_LABELS = {
                '': '—',
                company: 'Company name',
                contractor: 'Contractor / firm name',
                apprentice: 'Establishment / training organization',
                electrical_inspector: 'Office / department',
                retired_employees: 'Name of PSU (State / Central / Corporation)'
            };

            function $workRow(el) { return $(el).closest('tr.work-fields'); }

            function syncLegacyHidden($tr) {
                var emp = ($tr.find('.work-employer-input').val() || '').trim();
                var tot = ($tr.find('.work-experience-total-hidden').val() || '').trim();
                $tr.find('.work-level-sync').val(emp);
                $tr.find('.experience-sync').val(tot);
            }

            function clearWorkDuration($tr) {
                $tr.find('.work-duration-y, .work-duration-m, .work-duration-d').val('');
                $tr.find('.work-experience-total-hidden').val('');
            }

            /** Calendar-style Y/M/D between two local dates (inclusive-style components). */
            function calendarDiffYMD(from, to) {
                if (isNaN(from.getTime()) || isNaN(to.getTime()) || to < from) return null;
                var y = to.getFullYear() - from.getFullYear();
                var m = to.getMonth() - from.getMonth();
                var d = to.getDate() - from.getDate();
                if (d < 0) {
                    m--;
                    d += new Date(to.getFullYear(), to.getMonth(), 0).getDate();
                }
                if (m < 0) {
                    y--;
                    m += 12;
                }
                if (d < 0) {
                    m--;
                    if (m < 0) {
                        y--;
                        m += 12;
                    }
                    d += new Date(to.getFullYear(), to.getMonth(), 0).getDate();
                }
                return { y: y, m: m, d: d };
            }

            function updateTotalYears($tr) {
                var $yearsCell = $tr.find('td.work-exp-col-years');
                $yearsCell.find('.work-exp-two-year-msg').remove();
                var fromStr = ($tr.find('.work-date-from').val() || '').trim();
                var toStr   = ($tr.find('.work-date-to').val() || '').trim();
                if (!fromStr || !toStr) { clearWorkDuration($tr); syncLegacyHidden($tr); return; }
                var from = new Date(fromStr + 'T12:00:00'), to = new Date(toStr + 'T12:00:00');
                if (isNaN(from.getTime()) || isNaN(to.getTime())) { clearWorkDuration($tr); syncLegacyHidden($tr); return; }
                if (to < from) { clearWorkDuration($tr); syncLegacyHidden($tr); return; }
                var minTo = new Date(from.getTime());
                minTo.setFullYear(minTo.getFullYear() + 2);
                if (to < minTo) {
                    $yearsCell.append(
                        '<div class="work-exp-two-year-msg text-danger small mt-1" role="alert">Minimum 2 Years Experience needed</div>'
                    );
                }
                var diff = calendarDiffYMD(from, to);
                if (!diff) { clearWorkDuration($tr); syncLegacyHidden($tr); return; }
                $tr.find('.work-duration-y').val(String(diff.y));
                $tr.find('.work-duration-m').val(String(diff.m));
                $tr.find('.work-duration-d').val(String(diff.d));
                var yearsDec = (to - from) / 86400000 / 365.25;
                var rounded = Math.round(yearsDec * 10) / 10;
                $tr.find('.work-experience-total-hidden').val(rounded.toFixed(1));
                syncLegacyHidden($tr);
            }

            function applyEmploymentType($tr) {
                var t = $tr.find('.work-employment-type').val() || '';
                $tr.find('.work-employer-label').text(EMP_LABELS[t] || EMP_LABELS['']);
                $tr.find('.work-employer-req').toggle(!!t);
                var $emp = $tr.find('.work-employer-input');
                var $yFrom = $tr.find('.work-date-from'), $yTo = $tr.find('.work-date-to');
                var $blockInt = $tr.find('.work-block--intimation'), $intDate = $tr.find('.work-intimation-date');
                if (!t) {
                    $emp.prop('disabled', true).prop('required', false);
                    $yFrom.prop('required', false);
                    $yTo.prop('required', false);
                    $blockInt.hide();
                    $intDate.prop('disabled', false).prop('required', false).val('');
                    syncLegacyHidden($tr); return;
                }
                $emp.prop('disabled', false).prop('required', true);
                $yFrom.prop('required', true);
                $yTo.prop('required', true);
                if (t === 'contractor') { $blockInt.show(); $intDate.prop('disabled', false).prop('required', true); }
                else { $blockInt.hide(); $intDate.prop('disabled', false).prop('required', false).val(''); }
                updateTotalYears($tr); syncLegacyHidden($tr);
            }

            function initWorkRow($tr) { applyEmploymentType($tr); }
            function refreshWorkSerials() {
                $('#work-container .work-fields .work-serial').each(function(idx) { $(this).text(String(idx + 1)); });
            }

            $(document).ready(function() {
                $('#work-container .work-fields').each(function() { initWorkRow($(this)); });
                refreshWorkSerials();
            });

            $(document).on('change', '.work-employment-type', function() { applyEmploymentType($workRow(this)); });
            $(document).on('change', '.work-date-from, .work-date-to', function() { updateTotalYears($workRow(this)); });
            $(document).on('input change', '.work-employer-input, .work-intimation-date', function() { syncLegacyHidden($workRow(this)); });

            document.addEventListener('click', function(e) {
                var container = document.getElementById('work-container');
                if (!container) return;
                var workRows = container.querySelectorAll('.work-fields');

                if (e.target.closest('.add-more-work')) {
                    if (workRows.length >= 3) {
                        $('#work-table').next('.work-error').remove();
                        $('<div class="text-danger mt-2 work-error">You can add a maximum of 3 work experience entries.</div>').insertAfter('#work-table');
                        setTimeout(function() { $('.work-error').fadeOut(); }, 7000);
                        return;
                    }
                    var first = container.querySelector('.work-fields');
                    var newRow = first.cloneNode(true);
                    newRow.querySelectorAll('input[type="file"]').forEach(function(el) { el.value = ''; el.removeAttribute('data-has-local-file'); });
                    newRow.querySelectorAll('.local-file-preview').forEach(function(preview) {
                        var blobUrl = preview.dataset ? preview.dataset.blobUrl : '';
                        if (blobUrl) { try { URL.revokeObjectURL(blobUrl); } catch(e) {} }
                        preview.remove();
                    });
                    newRow.querySelectorAll('.work-date-from, .work-date-to').forEach(function(inp) { inp.value = ''; });
                    var typeSel = newRow.querySelector('.work-employment-type'); if (typeSel) typeSel.selectedIndex = 0;
                    newRow.querySelectorAll('.work-duration-y, .work-duration-m, .work-duration-d').forEach(function(inp) { inp.value = ''; });
                    var hTot = newRow.querySelector('.work-experience-total-hidden'); if (hTot) hTot.value = '';
                    var hLevel = newRow.querySelector('.work-level-sync'); if (hLevel) hLevel.value = '';
                    var hEx = newRow.querySelector('.experience-sync'); if (hEx) hEx.value = '';
                    var empIn = newRow.querySelector('.work-employer-input'); if (empIn) empIn.value = '';
                    var intIn = newRow.querySelector('.work-intimation-date'); if (intIn) intIn.value = '';
                    var desIn = newRow.querySelector('input[name="designation[]"]'); if (desIn) desIn.value = '';
                    container.appendChild(newRow);
                    initWorkRow($(newRow)); refreshWorkSerials(); return;
                }

                if (e.target.closest('.remove-work')) {
                    if (workRows.length <= 1) {
                        $('#work-table').next('.work-error').remove();
                        $('<div class="text-danger mt-2 work-error">You must have at least one work experience entry.</div>').insertAfter('#work-table');
                        setTimeout(function() { $('.work-error').fadeOut(); }, 7000);
                        return;
                    }
                    e.target.closest('tr').remove(); refreshWorkSerials();
                }
            });
        })();

        $('#verify_form_s').on('click', function() {
            const licenseNumber = $('#certificate_no').val().trim().toUpperCase();
            const date = $('#certificate_date').val().trim();
            const regex = /^(B|C|LC|LB)\d+$/;
            licenseError.textContent = '';
            $('#dateError').text('');
            let isValid = true;
            if (licenseNumber === '' || !regex.test(licenseNumber)) { licenseError.textContent = 'Enter a valid Certificate Number'; isValid = false; }
            if (date === '') { $('#dateError').text('Date is required'); isValid = false; }
            else {
                const regexDate = /^(\d{4})-(\d{2})-(\d{2})$/;
                const parts = date.match(regexDate);
                if (!parts) { $('#dateError').text('Enter a valid date'); isValid = false; }
                else {
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
                    let $msgBox = $("#license_message");
                    if (response.exists) $msgBox.removeClass("text-danger").addClass("text-success").html("&#10004; License verified.");
                    else $msgBox.removeClass("text-success").addClass("text-danger").html("&#10060; License not found.");
                },
                error: function() { $("#license_message").removeClass("text-success").addClass("text-danger").html("🚫 Error verifying license. Try again."); }
            });
        });

        $(document).ready(async function() {
            var modalEl = document.getElementById('competencyInstructionsModal');
            if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) return;
            var agreeCheckbox = modalEl.querySelector('#declaration-agree-renew');
            var errorText = modalEl.querySelector('#declaration-error-renew');
            var proceedBtn = modalEl.querySelector('#proceedPayment');
            if (!agreeCheckbox || !errorText || !proceedBtn) return;
            var acceptModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
            var modalBody = modalEl.querySelector('#instructionContent');
            if (modalBody) modalBody.innerHTML = '<p class="mb-0 text-muted">Loading instructions...</p>';
            try {
                var instructionResponse = await $.ajax({
                    url: "{{ route('licences.getFormInstruction') }}", type: "POST",
                    data: { appl_type: ($('#appl_type').val() || 'N'), licence_code: ($('#license_name').val() || 'C'), _token: $('meta[name="csrf-token"]').attr('content') }
                });
                if (modalBody) {
                    if (instructionResponse && Number(instructionResponse.status) === 200 && instructionResponse.data) {
                        try {
                            var delta = JSON.parse(instructionResponse.data);
                            if (typeof QuillDeltaToHtmlConverter !== 'undefined' && delta && delta.ops) {
                                var converter = new QuillDeltaToHtmlConverter(delta.ops, { multiLineParagraph: false, listItemTag: "li", paragraphTag: "p" });
                                modalBody.innerHTML = converter.convert();
                            } else { modalBody.textContent = instructionResponse.data; }
                        } catch(parseErr) { modalBody.textContent = instructionResponse.data; }
                    } else { modalBody.innerHTML = '<p class="mb-0 text-danger">Instruction not available.</p>'; }
                }
            } catch(err) { if (modalBody) modalBody.innerHTML = '<p class="mb-0 text-danger">Unable to load instructions right now.</p>'; }
            agreeCheckbox.checked = false;
            errorText.classList.add('d-none');
            acceptModal.show();
            if (!modalEl.dataset.acceptGateBound) {
                modalEl.dataset.acceptGateBound = '1';
                modalEl.addEventListener('hide.bs.modal', function(e) { if (!agreeCheckbox.checked) { e.preventDefault(); errorText.classList.remove('d-none'); } });
                proceedBtn.addEventListener('click', function(e) {
                    if (!agreeCheckbox.checked) { e.preventDefault(); errorText.classList.remove('d-none'); return; }
                    errorText.classList.add('d-none'); acceptModal.hide();
                });
                agreeCheckbox.addEventListener('change', function() { if (agreeCheckbox.checked) errorText.classList.add('d-none'); });
            }
        });
    </script>
    <script>
    // ── Preview Modal ────────────────────────────────────────────────────────
    var EDU_LEVEL_MAP = {
        DEE:'Diploma(Electrical Engineering)', BEE:'B.E(Electrical Engineering)',
        MEE:'M.E(Electrical Engineering)', AMIE:'A pass in AMIE'
    };
    var MONTH_MAP = { '01':'Jan','02':'Feb','03':'Mar','04':'Apr','05':'May','06':'Jun',
                      '07':'Jul','08':'Aug','09':'Sep','10':'Oct','11':'Nov','12':'Dec' };
    var EMP_LABEL_MAP = {
        company:'Company', contractor:'Contractor', apprentice:'Apprentice',
        electrical_inspector:'Government / Quasi Government / Board', retired_employees:'Retired Employees'
    };

    function fmtDate(val) {
        if (!val) return '—';
        var p = val.split('-');
        return p.length === 3 ? p[2]+'-'+p[1]+'-'+p[0] : val;
    }
    function setVal(id, v) {
        var el = document.getElementById(id);
        if (!el) return;
        var txt = (v || '').toString().trim();
        el.textContent = txt || '—';
        el.classList.toggle('prv-empty', !txt);
    }
    function fileLabel(input) {
        return input && input.files && input.files[0] ? input.files[0].name : '—';
    }

    function populatePreview() {
        // Personal
        setVal('prv_name', document.getElementById('Applicant_Name').value);
        setVal('prv_fathers_name', document.getElementById('Fathers_Name').value);
        var emailEl = document.getElementById('applicant_email');
        setVal('prv_email', emailEl ? emailEl.value : '');
        setVal('prv_address', document.getElementById('applicants_address').value);
        setVal('prv_dob', document.getElementById('d_o_b').value);
        setVal('prv_age', document.getElementById('age').value);

        // Education
        var eduBody = document.getElementById('prv_edu_body');
        eduBody.innerHTML = '';
        var eduRows = document.querySelectorAll('#education-container .education-fields');
        if (!eduRows.length) {
            eduBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No education entries</td></tr>';
        } else {
            eduRows.forEach(function(row, i) {
                var level = row.querySelector('[name="educational_level[]"]');
                var inst  = row.querySelector('[name="institute_name[]"]');
                var mon   = row.querySelector('[name="month_of_passing[]"]');
                var yr    = row.querySelector('[name="year_of_passing[]"]');
                var cert  = row.querySelector('[name="certificate_no[]"]');
                var doc   = row.querySelector('[name="education_document[]"]');
                var lvlTxt = level ? (EDU_LEVEL_MAP[level.value] || level.value || '—') : '—';
                var monTxt = mon ? (MONTH_MAP[mon.value] || mon.value || '—') : '—';
                var yrTxt  = yr ? (yr.value === '0' || !yr.value ? '—' : yr.value) : '—';
                var docLink = (doc && doc.files && doc.files[0])
                    ? '<a href="'+URL.createObjectURL(doc.files[0])+'" target="_blank" style="color:#035ab3;font-size:.75rem;"><i class="fa fa-file-pdf-o"></i> View</a>'
                    : '<span class="text-muted">—</span>';
                var tr = '<tr><td class="text-center">'+(i+1)+'</td><td>'+lvlTxt+'</td>'
                    +'<td>'+(inst ? inst.value || '—' : '—')+'</td>'
                    +'<td class="text-center">'+monTxt+'</td><td class="text-center">'+yrTxt+'</td>'
                    +'<td>'+(cert ? cert.value || '—' : '—')+'</td>'
                    +'<td class="text-center">'+docLink+'</td></tr>';
                eduBody.innerHTML += tr;
            });
        }

        // Work Experience
        var workBody = document.getElementById('prv_work_body');
        workBody.innerHTML = '';
        var workRows = document.querySelectorAll('#work-container .work-fields');
        if (!workRows.length) {
            workBody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-3">No work entries</td></tr>';
        } else {
            workRows.forEach(function(row, i) {
                var empType  = row.querySelector('.work-employment-type');
                var employer = row.querySelector('.work-employer-input');
                var fromInp  = row.querySelector('.work-date-from');
                var toInp    = row.querySelector('.work-date-to');
                var yPart = row.querySelector('.work-duration-y');
                var mPart = row.querySelector('.work-duration-m');
                var dPart = row.querySelector('.work-duration-d');
                var yv = yPart ? (yPart.value || '').trim() : '';
                var mv = mPart ? (mPart.value || '').trim() : '';
                var dv = dPart ? (dPart.value || '').trim() : '';
                var totalTxt = (yv === '' && mv === '' && dv === '') ? '—' : (yv + 'y ' + mv + 'm ' + dv + 'd');
                var desig    = row.querySelector('[name="designation[]"]');
                var doc      = row.querySelector('[name="work_document[]"]');
                var empTxt   = empType ? (EMP_LABEL_MAP[empType.value] || empType.value || '—') : '—';
                var fromDate = fromInp ? fmtDate(fromInp.getAttribute('data-raw') || fromInp.value) : '—';
                var toDate   = toInp ? fmtDate(toInp.getAttribute('data-raw') || toInp.value) : '—';
                var docLink = (doc && doc.files && doc.files[0])
                    ? '<a href="'+URL.createObjectURL(doc.files[0])+'" target="_blank" style="color:#035ab3;font-size:.75rem;"><i class="fa fa-file-pdf-o"></i> View</a>'
                    : '<span class="text-muted">—</span>';
                var tr = '<tr><td class="text-center">'+(i+1)+'</td><td>'+empTxt+'</td>'
                    +'<td>'+(employer ? employer.value || '—' : '—')+'</td>'
                    +'<td>'+fromDate+'</td><td>'+toDate+'</td>'
                    +'<td class="text-center">' + totalTxt + '</td>'
                    +'<td>'+(desig ? desig.value || '—' : '—')+'</td>'
                    +'<td class="text-center">'+docLink+'</td></tr>';
                workBody.innerHTML += tr;
            });
        }

        // Section 7 — Previous License
        var prevLicYes = document.getElementById('previous_license_yes');
        var isYes7 = prevLicYes && prevLicYes.checked;
        var yn7 = document.getElementById('prv_prev_license_yn');
        if (yn7) yn7.innerHTML = isYes7 ? '<span class="prv-badge-yes">Yes</span>' : '<span class="prv-badge-no">No</span>';
        var pb = document.getElementById('prv_prev_details_block'); if (pb) pb.style.display = isYes7 ? '' : 'none';
        if (isYes7) {
            setVal('prv_prev_cert_no', document.getElementById('previously_number') ? document.getElementById('previously_number').value : '');
            var issEl = document.getElementById('previously_issue_date');
            setVal('prv_prev_issue_date', issEl ? fmtDate(issEl.value) : '');
            var expEl = document.getElementById('previously_date');
            setVal('prv_prev_expiry_date', expEl ? fmtDate(expEl.value) : '');
        }

        // Section 8 — Wireman
        var wireYes = document.getElementById('yesOption');
        var isYes8 = wireYes && wireYes.checked;
        var yn8 = document.getElementById('prv_wireman_yn');
        if (yn8) yn8.innerHTML = isYes8 ? '<span class="prv-badge-yes">Yes</span>' : '<span class="prv-badge-no">No</span>';
        var wb = document.getElementById('prv_wireman_details_block'); if (wb) wb.style.display = isYes8 ? '' : 'none';
        if (isYes8) {
            setVal('prv_wireman_cert_no', document.getElementById('certificate_no') ? document.getElementById('certificate_no').value : '');
            var wExpEl = document.getElementById('certificate_date');
            setVal('prv_wireman_expiry', wExpEl ? fmtDate(wExpEl.value) : '');
        }

        // Documents — Photo
        var photoWrap = document.getElementById('prv_photo_wrap');
        var photoSrc  = document.getElementById('photo_preview');
        if (photoWrap) {
            var src = photoSrc && photoSrc.style.display !== 'none' ? photoSrc.src : '';
            photoWrap.innerHTML = src
                ? '<img src="'+src+'" alt="Photo" style="width:80px;height:96px;object-fit:cover;border:2px solid #dde5f3;border-radius:6px;">'
                : '<div class="prv-no-img">No Photo</div>';
        }

        // Documents — Signature
        var signWrap = document.getElementById('prv_sign_wrap');
        var signSrc  = document.getElementById('sign_preview');
        if (signWrap) {
            var ssrc = signSrc && signSrc.style.display !== 'none' ? signSrc.src : '';
            signWrap.innerHTML = ssrc
                ? '<img src="'+ssrc+'" alt="Signature" style="width:140px;height:50px;object-fit:contain;border:2px solid #dde5f3;border-radius:6px;">'
                : '<div class="prv-no-img" style="width:140px;height:50px;">No Signature</div>';
        }

        // Aadhaar & PAN
        setVal('prv_aadhaar', document.getElementById('aadhaar') ? document.getElementById('aadhaar').value : '');
        setVal('prv_pan', document.getElementById('pancard') ? document.getElementById('pancard').value : '');
        var aDoc = document.getElementById('aadhaar_doc');
        setVal('prv_aadhaar_doc', fileLabel(aDoc));
        var pDoc = document.getElementById('pancard_doc');
        setVal('prv_pan_doc', fileLabel(pDoc));
    }

    function openPreviewModal() {
        populatePreview();
        var modal = document.getElementById('appPreviewModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        document.getElementById('prvConfirmCheck').checked = false;
        document.getElementById('prvConfirmBtn').disabled = true;
        document.getElementById('prvBody').scrollTop = 0;
    }

    function closePreviewModal() {
        document.getElementById('appPreviewModal').style.display = 'none';
        document.body.style.overflow = '';
        if (typeof window.normalizeCompetencyDynamicSections === 'function') {
            window.normalizeCompetencyDynamicSections();
        }
    }

    document.getElementById('prvConfirmCheck').addEventListener('change', function() {
        document.getElementById('prvConfirmBtn').disabled = !this.checked;
    });

    // Confirm button — resolve the promise so footer's flow continues
    document.getElementById('prvConfirmBtn').addEventListener('click', function() {
        closePreviewModal();
        if (typeof window._prvResolve === 'function') {
            window._prvResolve(true);
            window._prvResolve = null;
        }
    });

    // Close / back button — cancel the flow
    document.getElementById('appPreviewModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closePreviewModal();
            if (typeof window._prvResolve === 'function') {
                window._prvResolve(false);
                window._prvResolve = null;
            }
        }
    });

    // Override footer's showCompetencyPreviewModal so OUR preview opens after validation passes
    window.showCompetencyPreviewModal = function() {
        return new Promise(function(resolve) {
            window._prvResolve = resolve;
            openPreviewModal();
        });
    };
    </script>
</footer>
