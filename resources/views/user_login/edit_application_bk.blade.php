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
        cursor: pointer;
    }
    .fs-breadcrumb-bar #breadcrumb a:hover { text-decoration: underline !important; }

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
        padding: 10px 24px 2px;
        position: relative;
    }
    .fs-card-header .header-titles {
        text-align: center;
        margin-bottom: 0;
    }
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
        text-transform: none;
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
        margin-top: 2px;
        letter-spacing: .5px;
    }
    .fs-card-header .header-titles .draft-title {
        margin: 2px 0 0;
        font-size: .74rem;
        font-weight: 600;
        line-height: 1.15;
        letter-spacing: .4px;
        color: #fff;
        text-transform: uppercase;
    }
    .fs-card-header .instructions-link {
        text-align: right;
        margin-top: 0;
        margin-bottom: -1px;
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

    /* ── Query alert ──────────────────────────────────── */
    .fs-query-alert-wrap { padding: 12px 28px 0; }
    .fs-query-alert-wrap .alert {
        border-radius: 8px;
        font-size: .85rem;
    }

    /* ── Form body ────────────────────────────────────── */
    .fs-form-body { padding: 28px 28px 32px; }

    /* ── Section blocks ───────────────────────────────── */
    .fs-section {
        background: #f8fafd;
        border: 1px solid #e3e8f0;
        border-radius: 8px;
        margin-bottom: 20px;
        position: relative;
    }
    .fs-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 18px;
        background: #eef3fb;
        border-bottom: 1px solid #dde5f3;
        position: relative;
    }
    .fs-section-edit-toggle {
        margin-left: auto;
        background: #fff;
        border: 1px solid #c8d8f5;
        color: #035ab3;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background .15s ease, color .15s ease, border-color .15s ease;
        flex-shrink: 0;
    }
    .fs-section-edit-toggle:hover { background: #035ab3; color: #fff; border-color: #035ab3; }
    .fs-section-edit-toggle i { font-size: .9rem; }
    .fs-section[data-mode="edit"] .fs-section-edit-toggle { background: #035ab3; color: #fff; border-color: #035ab3; }
    .fs-view-block { padding: 6px 0; }
    .fs-view-row {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 18px;
        padding: 10px 4px;
        border-bottom: 1px dashed #e8edf6;
        align-items: start;
    }
    .fs-view-row:last-child { border-bottom: 0; }
    .fs-view-label {
        font-size: .82rem;
        font-weight: 600;
        color: #4a5b7a;
    }
    .fs-view-label .tamil-tiny { display: block; font-weight: 400; color: #6b7894; font-size: .72rem; margin-top: 2px; }
    .fs-view-value {
        font-size: .9rem;
        color: #1f2937;
        word-break: break-word;
        line-height: 1.45;
    }
    .fs-view-value--empty { color: #a0acc1; font-style: italic; }
    .fs-view-grid-item {
        background: transparent;
        border: 0;
        border-radius: 0;
        padding: 4px 2px;
        height: 100%;
    }
    .fs-view-grid-label {
        font-size: .76rem;
        font-weight: 700;
        color: #35507a;
        margin-bottom: 8px;
        line-height: 1.3;
        text-transform: uppercase;
        letter-spacing: .2px;
    }
    .fs-view-grid-label .tamil-tiny {
        display: block;
        font-weight: 400;
        color: #6b7894;
        font-size: .72rem;
        margin-top: 2px;
    }
    .fs-view-grid-value {
        font-size: .92rem;
        color: #1f2937;
        word-break: break-word;
        line-height: 1.45;
    }
    .fs-view-grid-value-box {
        background: #f7faff;
        border: 1px solid #dbe6f7;
        border-radius: 6px;
        padding: 8px 10px;
        min-height: 42px;
    }
    @media (max-width: 575.98px) {
        .fs-view-row { grid-template-columns: 1fr; gap: 4px; }
    }
    .fs-section[data-mode="view"] .fs-edit-block { display: none; }
    .fs-section[data-mode="edit"] .fs-view-block { display: none; }
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

    /* ── Field rows ───────────────────────────────────── */
    .fs-field-label {
        font-size: .83rem;
        font-weight: 600;
        color: #2c3e5e;
        margin-bottom: 3px;
        line-height: 1.3;
    }
    .fs-field-label .req { color: #d9363e; }
    .fs-field-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        background: #035ab3;
        color: #fff;
        font-size: .7rem;
        font-weight: 700;
        margin-right: 6px;
        vertical-align: middle;
        flex-shrink: 0;
    }
    .fs-field-num-sub {
        margin-right: 4px;
        color: #2c3e5e;
        font-weight: 600;
    }
    .fs-field-head {
        display: flex;
        align-items: flex-start;
        gap: 6px;
        margin-bottom: 4px;
    }
    .fs-field-head-text { flex: 1; min-width: 0; }
    .fs-field-head .fs-field-num { margin-right: 0; margin-top: 1px; }
    .fs-field-head .fs-field-label { margin-bottom: 1px; }
    .fs-field-head .fs-field-tamil { margin-bottom: 0; }
    .fs-field-tamil {
        font-size: .76rem;
        color: #7a90b0;
        margin-bottom: 4px;
        line-height: 1.3;
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

    /* ── Verify / Delete buttons ──────────────────────── */
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
    .btn-verify-delete {
        background: #dc3545;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 7px 16px;
        font-size: .82rem;
        font-weight: 600;
        cursor: pointer;
        transition: background .2s;
        white-space: nowrap;
    }
    .btn-verify-delete:hover { background: #b52a37; color: #fff; }
    .fs-verify-grid .fs-field-label {
        min-height: 18px;
        margin-bottom: 4px;
    }
    .fs-verify-actions {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        flex-wrap: wrap;
        min-height: 70px;
    }
    .fs-verify-actions .verify-btn,
    .fs-verify-actions .remove_verify {
        min-width: 96px;
        height: 36px;
        padding: 6px 14px;
        white-space: nowrap;
    }
    @media (max-width: 767.98px) {
        .fs-verify-actions {
            min-height: 0;
        }
    }

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
    #work-table.work-exp-table tbody td { padding: .4rem .45rem; vertical-align: top; }
    #work-table .work-exp-col-type { width: 12%; max-width: 10.5rem; }
    #work-table .work-exp-col-employer { width: 16%; max-width: 12rem; }
    #work-table.work-exp-table .work-exp-col-years { width: 32%; min-width: 17rem; }
    #work-table.work-table-w .work-exp-col-company { width: 28%; }
    #work-table.work-table-w .work-exp-col-years { width: 38%; min-width: 17rem; }
    #work-table .work-exp-col-designation { width: 12%; }
    #work-table .work-exp-col-upload { width: 22%; }
    #work-table .work-exp-col-sno { width: 2.5rem; min-width: 2.5rem; white-space: nowrap; text-align: center; }
    #work-table .work-exp-col-actions { width: 2.75rem; white-space: nowrap; }
    #work-table .work-exp-upload-head { font-size: .72rem; line-height: 1.2; }
    #work-table .work-exp-upload-head .file-limit { font-size: .68rem; }
    #work-table .work-exp-inline { display: flex; flex-wrap: nowrap; align-items: flex-end; gap: .25rem; }
    #work-table .work-exp-date-group { flex: 1 1 auto; min-width: 7.5rem; max-width: 10rem; }
    #work-table .work-exp-total-inline { flex: 0 0 4rem; min-width: 4rem; max-width: 4.5rem; }
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
    #work-table .work-year-total-display { max-width: 4.5rem; font-size: .7rem; padding: .22rem .3rem; line-height: 1.3; text-align: center; }
    #work-table .work-employer-label { font-size: .7rem !important; margin-bottom: .15rem !important; }

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
    .fs-upload-uploaded {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .fs-upload-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .82rem;
        font-weight: 600;
        color: #1a8754;
        background: #e6f6ec;
        border: 1px solid #b7e1c4;
        padding: 4px 10px;
        border-radius: 999px;
    }
    .fs-upload-status i { font-size: .82rem; }
    .btn-fs-change {
        background: #fff;
        border: 1px solid #035ab3;
        color: #035ab3;
        padding: 5px 12px;
        border-radius: 6px;
        font-size: .8rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background .15s ease, color .15s ease;
        line-height: 1.2;
    }
    .btn-fs-change:hover { background: #035ab3; color: #fff; }
    .btn-fs-change i { font-size: .78rem; }
    .fs-existing-doc-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #035ab3;
        text-decoration: none;
        font-size: .82rem;
        font-weight: 600;
    }
    .fs-existing-doc-link:hover { text-decoration: underline; }
    .btn-doc-remove {
        background: #dc3545;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 3px 10px;
        font-size: .75rem;
        cursor: pointer;
        margin-left: 8px;
    }
    .btn-doc-remove:hover { background: #b52a37; color: #fff; }
    .btn-edit-upload {
        background: #035ab3;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 4px 10px;
        font-size: .76rem;
        cursor: pointer;
        margin-top: 4px;
    }
    .btn-edit-upload:hover { background: #024a98; color: #fff; }
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


@php
    $editFormName = $application_details->form_name ?? '';
    $editLicenseName = $application_details->license_name ?? '';
    $editEnglishTitle = isset($licence_name->licence_name) ? $licence_name->licence_name : 'Competency Certificate';
    if ($editFormName === 'S') {
        $editTamilTitle = 'மேற்பார்வையாளர் தகுதி சான்றிதழ் பெறுவதற்கான விண்ணப்பம்';
        $editNotesPdf = 'assets/pdf/form_s_notes.pdf';
        $editNotesLang = 'English';
        $editNotesSize = '8 KB';
    } elseif ($editFormName === 'W') {
        $editTamilTitle = 'மின்கம்பியாளர் தகுதி சான்றிதழ் பெறுவதற்கான விண்ணப்பம்';
        $editNotesPdf = 'assets/pdf/form_w_notes.pdf';
        $editNotesLang = 'தமிழ்';
        $editNotesSize = '38 KB';
    } elseif ($editFormName === 'WH') {
        $editTamilTitle = 'மின் கம்பி உதவியாளர் தகுதிச் சான்றிதழ் பெறுவதற்கான விண்ணப்பம்';
        $editNotesPdf = 'assets/pdf/form_wh_notes.pdf';
        $editNotesLang = 'தமிழ்';
        $editNotesSize = '38 KB';
    } else {
        $editTamilTitle = '';
        $editNotesPdf = 'assets/pdf/form_s_notes.pdf';
        $editNotesLang = 'English';
        $editNotesSize = '8 KB';
    }
@endphp

{{-- ░░ BREADCRUMB ░░ --}}
<div class="fs-breadcrumb-bar">
    <div class="container">
        <ul id="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><span class="fa fa-home"></span> Dashboard</a></li>
            <li><a href="#"><span class="fa fa-info-circle"></span> Form {{ $editFormName }}</a></li>
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
                    <h5>Application for {{ $editEnglishTitle }}</h5>
                    @if($editTamilTitle)
                        <h5 class="tamil-title">{{ $editTamilTitle }}</h5>
                    @endif
                    <span class="form-badge">FORM - {{ $editFormName }} / Certificate {{ $editLicenseName }}</span>
                    <h5 class="draft-title">Draft Application</h5>
                </div>
                <div class="instructions-link">
                    <span class="text-white font-weight-bold" style="font-size:.82rem;">Instructions &nbsp;</span>
                    <a href="{{url($editNotesPdf)}}" target="_blank">{{ $editNotesLang }} <i class="fa fa-file-pdf-o"></i> ({{ $editNotesSize }})</a>
                </div>
            </div>

            {{-- ── Mandatory notice ── --}}
            <div class="fs-mandatory-bar">
                <span class="req-dot">*</span> Fields are Mandatory
            </div>

            @if(isset($queries) && $queries->isNotEmpty())
            <div class="fs-query-alert-wrap">
                <div class="alert alert-warning mb-0" role="alert">
                    <h6 class="alert-heading font-weight-bold mb-2">
                        <i class="fa fa-exclamation-triangle"></i> Query raised – please correct and resubmit
                    </h6>
                    <p class="mb-1">The following issue(s) were reported. Please correct and submit again:</p>
                    <ul class="mb-0 pl-4">
                        @foreach($queries as $q)
                            @php
                                $items = is_string($q->query_type) ? json_decode($q->query_type, true) : $q->query_type;
                                $items = is_array($items) ? $items : [$items];
                            @endphp
                            @foreach($items as $item)
                                <li>{{ is_string($item) ? $item : '' }}</li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            {{-- ── Form body ── --}}
            <div class="fs-form-body fs-form apply-card">

                <form id="competency_form_ws" enctype="multipart/form-data">

                    <input type="hidden" id="login_id_store" name="login_id" value="{{ Auth::user()->login_id }}">
                    <input type="hidden" id="application_id" name="application_id"
                        value="{{ isset($application_details) ? $application_details->application_id : '' }}">
                    <input type="hidden" id="license_number" name="license_number"
                        value="{{ isset($license_details) ? $license_details->license_number : '' }}">

                    {{-- ═══ SECTION 1 to 4 — Applicant Details ═══ --}}
                    @php
                        $applicantNameVal = isset($application_details) ? $application_details->applicant_name : Auth::user()->name;
                        $fathersNameVal = isset($application_details) ? $application_details->fathers_name : '';
                        $addressVal = isset($application_details) ? $application_details->applicants_address : Auth::user()->address;
                        $dobIsoVal = !empty($application_details->d_o_b) ? \Carbon\Carbon::parse($application_details->d_o_b)->format('Y-m-d') : '';
                        $dobDisplayVal = $dobIsoVal ? \Carbon\Carbon::parse($dobIsoVal)->format('d-m-Y') : '';
                        $ageVal = isset($application_details) ? $application_details->age : '';
                    @endphp
                    <div class="fs-section" data-mode="view">
                        <button type="button" class="fs-section-edit-toggle" onclick="toggleSectionEdit(this)" title="Edit" style="position:absolute;top:10px;right:10px;">
                            <i class="fa fa-pencil"></i>
                        </button>
                        <div class="fs-section-body">
                            <div class="fs-view-block">
                                <div class="row">
                                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                                        <div class="fs-field-head">
                                            <span class="fs-field-num">1</span>
                                            <div class="fs-field-head-text">
                                                <div class="fs-field-label">Applicant's Name</div>
                                                <div class="fs-field-tamil">விண்ணப்பதாரர் பெயர்</div>
                                            </div>
                                        </div>
                                        <div class="fs-view-grid-value-box">
                                            <div class="fs-view-value {{ empty($applicantNameVal) ? 'fs-view-value--empty' : '' }}" data-view-for="Applicant_Name">{{ $applicantNameVal ?: 'Not provided' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="fs-field-head">
                                            <span class="fs-field-num">2</span>
                                            <div class="fs-field-head-text">
                                                <div class="fs-field-label">Father's Name</div>
                                                <div class="fs-field-tamil">தகப்பனார் பெயர்</div>
                                            </div>
                                        </div>
                                        <div class="fs-view-grid-value-box">
                                            <div class="fs-view-value {{ empty($fathersNameVal) ? 'fs-view-value--empty' : '' }}" data-view-for="Fathers_Name">{{ $fathersNameVal ?: 'Not provided' }}</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                                        <div class="fs-field-head">
                                            <span class="fs-field-num">3</span>
                                            <div class="fs-field-head-text">
                                                <div class="fs-field-label">Applicant Address</div>
                                                <div class="fs-field-tamil">விண்ணப்பதாரர் முகவரி</div>
                                            </div>
                                        </div>
                                        <div class="fs-view-grid-value-box">
                                            <div class="fs-view-value {{ empty($addressVal) ? 'fs-view-value--empty' : '' }}" data-view-for="applicants_address">{{ $addressVal ?: 'Not provided' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="row">
                                            <div class="col-12 col-sm-7 mb-3 mb-sm-0">
                                                <div class="fs-field-head">
                                                    <span class="fs-field-num">4</span>
                                                    <div class="fs-field-head-text">
                                                        <div class="fs-field-label"><span class="fs-field-num-sub">(i)</span>Date of Birth</div>
                                                        <div class="fs-field-tamil">பிறந்த நாள், மாதம், வருடம்</div>
                                                    </div>
                                                </div>
                                                <div class="fs-view-grid-value-box">
                                                    <div class="fs-view-value {{ empty($dobDisplayVal) ? 'fs-view-value--empty' : '' }}" data-view-for="d_o_b" data-view-format="date">{{ $dobDisplayVal ?: 'Not provided' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-sm-5">
                                                <div class="fs-field-label"><span class="fs-field-num-sub">(ii)</span>Age</div>
                                                <div class="fs-field-tamil">வயது</div>
                                                <div class="fs-view-grid-value-box">
                                                    <div class="fs-view-value {{ empty($ageVal) ? 'fs-view-value--empty' : '' }}" data-view-for="age">{{ $ageVal ?: 'Not provided' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="fs-edit-block">
                                <div class="row">
                                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                                        <div class="fs-field-head">
                                            <span class="fs-field-num">1</span>
                                            <div class="fs-field-head-text">
                                                <div class="fs-field-label">Applicant's Name <span class="req">*</span></div>
                                                <div class="fs-field-tamil">விண்ணப்பதாரர் பெயர்</div>
                                            </div>
                                        </div>
                                        <input autocomplete="off" class="form-control" id="Applicant_Name" name="applicant_name" type="text"
                                            value="{{ str_replace('.', '', $applicantNameVal) }}" readonly>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="fs-field-head">
                                            <span class="fs-field-num">2</span>
                                            <div class="fs-field-head-text">
                                                <div class="fs-field-label">Father's Name <span class="req">*</span></div>
                                                <div class="fs-field-tamil">தகப்பனார் பெயர்</div>
                                            </div>
                                        </div>
                                        <input autocomplete="off" class="form-control" id="Fathers_Name" name="fathers_name"
                                            type="text" value="{{ $fathersNameVal }}" maxlength="50">
                                        <span class="error-message text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-12 col-md-6 mb-3 mb-md-0">
                                        <div class="fs-field-head">
                                            <span class="fs-field-num">3</span>
                                            <div class="fs-field-head-text">
                                                <div class="fs-field-label">Applicant Address <span class="req">*</span> <span style="font-weight:400;font-size:.78rem;">(To be clear)</span></div>
                                                <div class="fs-field-tamil">விண்ணப்பதாரர் முகவரி <span style="font-size:.72rem;">(தெளிவாக இருத்தல் வேண்டும்)</span></div>
                                            </div>
                                        </div>
                                        <textarea rows="3" class="form-control" id="applicants_address" name="applicants_address" maxlength="250">{{ $addressVal }}</textarea>
                                        <span id="applicants_address_error" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="row">
                                            <div class="col-12 col-sm-7 mb-3 mb-sm-0">
                                                <div class="fs-field-head">
                                                    <span class="fs-field-num">4</span>
                                                    <div class="fs-field-head-text">
                                                        <div class="fs-field-label"><span class="fs-field-num-sub">(i)</span>D.O.B <span class="req">*</span></div>
                                                        <div class="fs-field-tamil">பிறந்த நாள், மாதம், வருடம்</div>
                                                    </div>
                                                </div>
                                                <input class="form-control" type="date" autocomplete="off"
                                                    id="d_o_b" name="d_o_b"
                                                    min="{{ \Carbon\Carbon::now()->subYears(100)->format('Y-m-d') }}"
                                                    max="{{ \Carbon\Carbon::now()->subYears(18)->format('Y-m-d') }}"
                                                    value="{{ $dobIsoVal }}">
                                                <span id="dob-error" class="text-danger" style="display:none;"></span>
                                            </div>
                                            <div class="col-12 col-sm-5">
                                                <div class="fs-field-label"><span class="fs-field-num-sub">(ii)</span>Age <span class="req">*</span></div>
                                                <div class="fs-field-tamil">வயது</div>
                                                <input autocomplete="off" class="form-control" id="age" name="age"
                                                    type="number" min="18" max="100" value="{{ $ageVal }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php $formName = $application_details->form_name ?? ''; @endphp

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
                            <div class="table-responsive">
                                                <table class="table table-bordered {{ (isset($application_details->form_name) && $application_details->form_name == 'S') ? '' : 'table-striped' }}"
                                                    id="education-table">
                                                    <thead>
                                                        <tr>
                                                            <th rowspan="2">S.No</th>
                                                            <th rowspan="2">Education Level</th>
                                                            <th rowspan="2">Institution/School Name</th>
                                                            <th colspan="2" class="text-center">Year of Passing</th>
                                                            <th rowspan="2">Certificate No</th>
                                                            <th class="text-center" rowspan="2">Upload Document
                                                                <br><span class="file-limit text-success small">File type: PDF(Min 5 KB To Max 200 KB)</span>
                                                            </th>
                                                            <th class="text-center p-1" rowspan="2">
                                                                <div class="form-s-actions-stack">
                                                                    <button type="button"
                                                                        class="btn-tbl-add add-more add-more-education py-1 px-2" title="Add row">
                                                                        <i class="fa fa-plus"></i>
                                                                    </button>
                                                                </div>
                                                            </th>
                                                        </tr>
                                                        <tr>
                                                            <th class="text-center">Month</th>
                                                            <th class="text-center">Year</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="education-container">
                                                        {{-- @php
                                                            var_dump($edu_details->isEmpty());die;
                                                        @endphp --}}
                                                        @if ($edu_details->isNotEmpty())
                                                        @foreach ($edu_details as $edu_details)
                                                        <tr class="education-fields text-center" data-edu-index="{{ $loop->index }}">
                                                            <td class="edu-serial text-center">{{ $loop->iteration }}</td>
                                                            <td>
                                                                @php $formName = $application_details->form_name ?? ''; @endphp
                                                                <select class="form-control" name="educational_level[]">
                                                                    <option disabled {{ empty($edu_details->educational_level) ? 'selected' : '' }}>Select Education</option>
                                                                    @if ($formName === 'S')
                                                                        <option value="DEE" {{ $edu_details->educational_level == 'DEE' ? 'selected' : '' }}>Diploma(Electrical Engineering)</option>
                                                                        <option value="BEE" {{ $edu_details->educational_level == 'BEE' ? 'selected' : '' }}>B.E(Electrical Engineering)</option>
                                                                        <option value="MEE" {{ $edu_details->educational_level == 'MEE' ? 'selected' : '' }}>M.E(Electrical Engineering)</option>
                                                                    @elseif ($formName === 'W')
                                                                        <option value="NTC" {{ $edu_details->educational_level == 'NTC' ? 'selected' : '' }}>NTC</option>
                                                                        <option value="Provisional" {{ $edu_details->educational_level == 'Provisional' ? 'selected' : '' }}>Provisional</option>
                                                                        <option value="Ex-Serviceman" {{ $edu_details->educational_level == 'Ex-Serviceman' ? 'selected' : '' }}>Ex-Serviceman</option>
                                                                        <option value="H to B" {{ $edu_details->educational_level == 'H to B' ? 'selected' : '' }}>H to B</option>
                                                                        <option value="SCVT" {{ $edu_details->educational_level == 'SCVT' ? 'selected' : '' }}>SCVT</option>
                                                                    @elseif ($formName === 'WH')
                                                                        <option value="Up to 8th Standard" {{ $edu_details->educational_level == 'Up to 8th Standard' ? 'selected' : '' }}>Up to 8th Standard</option>
                                                                        <option value="Wireman Helper Examination" {{ $edu_details->educational_level == 'Wireman Helper Examination' ? 'selected' : '' }}>Wireman Helper Examination</option>
                                                                        <option value="ITI Certificate" {{ $edu_details->educational_level == 'ITI Certificate' ? 'selected' : '' }}>ITI Certificate</option>
                                                                    @elseif ($formName === 'P')
                                                                        <option value="BEM" {{ $edu_details->educational_level == 'BEM' ? 'selected' : '' }}>B.E(Mechanical)</option>
                                                                        <option value="BEE" {{ $edu_details->educational_level == 'BEE' ? 'selected' : '' }}>B.E(Electrical)</option>
                                                                        <option value="DiplomaM" {{ $edu_details->educational_level == 'DiplomaM' ? 'selected' : '' }}>Diploma(Mechanical)</option>
                                                                        <option value="DiplomaE" {{ $edu_details->educational_level == 'DiplomaE' ? 'selected' : '' }}>Diploma(Electrical)</option>
                                                                    @else
                                                                        <option value="PG" {{ $edu_details->educational_level == 'PG' ? 'selected' : '' }}>PG</option>
                                                                        <option value="UG" {{ $edu_details->educational_level == 'UG' ? 'selected' : '' }}>UG</option>
                                                                        <option value="B.E" {{ $edu_details->educational_level == 'B.E' ? 'selected' : '' }}>B.E</option>
                                                                        <option value="M.E" {{ $edu_details->educational_level == 'M.E' ? 'selected' : '' }}>M.E</option>
                                                
                                                                    @endif
                                                                </select>
                                                            </td>
                                                            @php
                                                                $isWH = (isset($application_details->form_name) && $application_details->form_name === 'WH');
                                                                $isDraft = isset($application_details->payment_status) && strtolower(trim((string) $application_details->payment_status)) === 'draft';
                                                                $instituteDisplayValue = !empty(trim((string) ($edu_details->institute_name ?? '')))
                                                                    ? $edu_details->institute_name
                                                                    : ($isDraft && $isWH ? 'Dept of Employment & Training' : '');
                                                            @endphp
                                                            <td><input type="text" class="form-control" name="institute_name[]" value="{!! e($instituteDisplayValue) !!}"></td>
                                                            <td>
                                                                <select name="month_of_passing[]" class="form-control">
                                                                    <option value="">Select Month</option>
                                                                    <option value="01" {{ ($edu_details->month_passing ?? '') == '01' ? 'selected' : '' }}>Jan</option>
                                                                    <option value="02" {{ ($edu_details->month_passing ?? '') == '02' ? 'selected' : '' }}>Feb</option>
                                                                    <option value="03" {{ ($edu_details->month_passing ?? '') == '03' ? 'selected' : '' }}>Mar</option>
                                                                    <option value="04" {{ ($edu_details->month_passing ?? '') == '04' ? 'selected' : '' }}>Apr</option>
                                                                    <option value="05" {{ ($edu_details->month_passing ?? '') == '05' ? 'selected' : '' }}>May</option>
                                                                    <option value="06" {{ ($edu_details->month_passing ?? '') == '06' ? 'selected' : '' }}>Jun</option>
                                                                    <option value="07" {{ ($edu_details->month_passing ?? '') == '07' ? 'selected' : '' }}>Jul</option>
                                                                    <option value="08" {{ ($edu_details->month_passing ?? '') == '08' ? 'selected' : '' }}>Aug</option>
                                                                    <option value="09" {{ ($edu_details->month_passing ?? '') == '09' ? 'selected' : '' }}>Sep</option>
                                                                    <option value="10" {{ ($edu_details->month_passing ?? '') == '10' ? 'selected' : '' }}>Oct</option>
                                                                    <option value="11" {{ ($edu_details->month_passing ?? '') == '11' ? 'selected' : '' }}>Nov</option>
                                                                    <option value="12" {{ ($edu_details->month_passing ?? '') == '12' ? 'selected' : '' }}>Dec</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select name="year_of_passing[]" class="form-control">
                                                                    <option value="0" disabled {{ empty($edu_details->year_of_passing) ? 'selected' : '' }}>Select Year</option>
                                                                    @php
                                                                        $currentYear = date('Y');
                                                                    @endphp
                                                                    @for ($year = $currentYear; $year >= 1980; $year--)
                                                                        <option value="{{ $year }}" {{ $edu_details->year_of_passing == $year ? 'selected' : '' }}>
                                                                            {{ $year }}
                                                                        </option>
                                                                    @endfor
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text"
                                                                    class="form-control certificate-input"
                                                                    name="certificate_no[]"
                                                                    maxlength="20"
                                                                    required
                                                                    value="{{ $edu_details->certificate_no ?? $edu_details->percentage ?? '' }}">
                                                                <span class="error text-danger certificate-error"></span>
                                                            </td>
                                                            <td>
                                                                <div class="file-section text-center">
                                                                    @if (!empty($edu_details->upload_document))
                                                                        <div class="edu-doc-container d-flex align-items-center justify-content-center">
                                                                            <a class="text-primary" href="{{ asset($edu_details->upload_document) }}" target="_blank">
                                                                                <i class="fa fa-file-pdf-o" style="color: red"></i> View
                                                                            </a>
                                                                            <button type="button" class="btn btn-sm btn-danger ml-2 remove-doc_edu_confirm">Remove</button>
                                                                        </div>
                                                                        <div class="edu-doc-input d-none">
                                                                            <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="education">
                                                                                <input type="file" class="form-control" name="education_document[{{ $loop->index }}]" accept="{{ (isset($application_details->form_name) && $application_details->form_name == 'S') ? '.pdf,application/pdf' : '.pdf,application/pdf,image/jpeg,image/png' }}">
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="education">
                                                                            <input type="file" class="form-control" name="education_document[{{ $loop->index }}]" accept="{{ (isset($application_details->form_name) && $application_details->form_name == 'S') ? '.pdf,application/pdf' : '.pdf,application/pdf,image/jpeg,image/png' }}">
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </td>

                                                            <td class="form-s-actions-cell text-center p-1">
                                                                <div class="form-s-actions-stack">
                                                                    <button type="button" class="btn btn-danger btn-sm remove-education remove_edu py-1 px-2" data-edu_id = "{{ $edu_details->id }}" data-url= "{{ route('delete_education') }}" title="Remove row">
                                                                        <i class="fa fa-trash-o"></i>
                                                                    </button>
                                                                </div>
                                                                <!-- Keep IDs inside a cell to avoid invalid table markup causing dropped/misaligned inputs -->
                                                                <input type="hidden" name="edu_id[]" value="{{ $edu_details->id }}">
                                                                <input type="hidden" name="existing_document[]" value="{{ $edu_details->upload_document }}">
                                                                <input type="hidden" class="removed-document-edu" name="removed_document[]" value="0">
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                        @else
                                                        <tr class="education-fields text-center" data-edu-index="0">
                                                            <td class="edu-serial text-center">1</td>
                                                            <td>
                                                                @php $formName = $application_details->form_name ?? ''; @endphp
                                                                <select class="form-control" name="educational_level[]">
                                                                    <option selected disabled>Select Education</option>
                                                                    @if ($formName === 'S')
                                                                        <option value="DEE">Diploma(Electrical Engineering)</option>
                                                                        <option value="BEE">B.E(Electrical Engineering)</option>
                                                                        <option value="MEE">M.E(Electrical Engineering)</option>
                                                                    @elseif ($formName === 'W')
                                                                        <option value="NTC">NTC</option>
                                                                        <option value="Provisional">Provisional</option>
                                                                        <option value="Ex-Serviceman">Ex-Serviceman</option>
                                                                        <option value="H to B">H to B</option>
                                                                        <option value="SCVT">SCVT</option>
                                                                    @elseif ($formName === 'WH')
                                                                        <option value="Up to 8th Standard">Up to 8th Standard</option>
                                                                        <option value="Wireman Helper Examination">Wireman Helper Examination</option>
                                                                        <option value="ITI Certificate">ITI Certificate</option>
                                                                    @elseif ($formName === 'P')
                                                                        <option value="BEM">B.E(Mechanical)</option>
                                                                        <option value="BEE">B.E(Electrical)</option>
                                                                        <option value="DiplomaM">Diploma(Mechanical)</option>
                                                                        <option value="DiplomaE">Diploma(Electrical)</option>
                                                                    @else
                                                                        <option value="PG">PG</option>
                                                                        <option value="UG">UG</option>
                                                                        <option value="B.E">B.E</option>
                                                                        <option value="M.E">M.E</option>
                                                                        <option value="Diploma">Diploma</option>
                                                                        <option value="+2">+2</option>
                                                                        <option value="10">10</option>
                                                                    @endif
                                                                </select>
                                                            </td>
                                                            @php
                                                                $isWHEmptyRow = isset($application_details->form_name) && $application_details->form_name === 'WH';
                                                                $isDraftEmptyRow = isset($application_details->payment_status) && strtolower(trim((string) $application_details->payment_status)) === 'draft';
                                                                $defaultInstituteForEmptyRow = ($isDraftEmptyRow && $isWHEmptyRow) ? 'Dept of Employment & Training' : '';
                                                            @endphp
                                                            <td><input type="text" class="form-control" name="institute_name[]" value="{!! e($defaultInstituteForEmptyRow) !!}"></td>
                                                            <td>
                                                                <select name="month_of_passing[]" class="form-control">
                                                                    <option value="">Select Month</option>
                                                                    <option value="01">Jan</option>
                                                                    <option value="02">Feb</option>
                                                                    <option value="03">Mar</option>
                                                                    <option value="04">Apr</option>
                                                                    <option value="05">May</option>
                                                                    <option value="06">Jun</option>
                                                                    <option value="07">Jul</option>
                                                                    <option value="08">Aug</option>
                                                                    <option value="09">Sep</option>
                                                                    <option value="10">Oct</option>
                                                                    <option value="11">Nov</option>
                                                                    <option value="12">Dec</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select name="year_of_passing[]" class="form-control">
                                                                    <option value="0">Select Year</option>
                                                                    @php
                                                                        $currentYear = date('Y');
                                                                    @endphp
                                                                    @for ($year = $currentYear; $year >= 1980; $year--)
                                                                        <option value="{{ $year }}">{{ $year }}</option>
                                                                    @endfor
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="text"
                                                                    class="form-control certificate-input"
                                                                    name="certificate_no[]"
                                                                    maxlength="20"
                                                                    required>
                                                                <span class="error text-danger certificate-error"></span>
                                                            </td>
                                                            <td>
                                                                <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="education">
                                                                    <input type="file" class="form-control" name="education_document[0]" accept="{{ (isset($application_details->form_name) && $application_details->form_name == 'S') ? '.pdf,application/pdf' : '.pdf,application/pdf,image/jpeg,image/png' }}">
                                                                </div>
                                                            </td>
                                                            <td class="form-s-actions-cell text-center p-1">
                                                                <div class="form-s-actions-stack">
                                                                    <button type="button" class="btn btn-danger btn-sm remove-education py-1 px-2" title="Remove row">
                                                                        <i class="fa fa-trash-o"></i>
                                                                    </button>
                                                                </div>
                                                                <input type="hidden" name="edu_id[]" value="">
                                                                <input type="hidden" name="existing_document[]" value="">
                                                                <input type="hidden" class="removed-document-edu" name="removed_document[]" value="0">
                                                            </td>
                                                        </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                        </div>
                    </div>
                    {{-- /SECTION 5 --}}

                    @if (!isset($application_details->form_name) || $application_details->form_name !== 'WH')
                                                @php
                                                    $workQuestionNo = 6;
                                                @endphp

                    {{-- ═══ SECTION 6 — Work Experience ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">{{ $workQuestionNo }}</span>
                            <div>
                                <div class="fs-section-title">
                                    Details of Previous and Current Work experiences
                                    @if(isset($application_details->form_name) && in_array($application_details->form_name, ['W','WH']))
                                        <span class="section-hint">(Optional)</span>
                                    @else
                                        <span class="section-req">*</span>
                                    @endif
                                    <span class="section-hint">(Upload the documents)</span>
                                </div>
                                <div class="fs-section-tamil">பெற்றுள்ள முந்தைய மற்றும் தற்போதைய அனுபவங்களின் விவரங்கள்
                                    @if(isset($application_details->form_name) && in_array($application_details->form_name, ['W','WH']))
                                        <span style="font-size:.72rem;">(விருப்பமெனில் நிரப்பலாம்)</span>
                                    @endif
                                    <span style="font-size:.72rem;">(ஆவணங்களை பதிவேற்ற வேண்டும்)</span>
                                </div>
                            </div>
                        </div>
                        <div class="fs-section-body">
                            <div class="table-responsive">
                                                    <table class="table table-bordered {{ (isset($application_details->form_name) && in_array($application_details->form_name, ['S','W'])) ? 'table-sm work-exp-table' : 'table-striped' }} {{ (isset($application_details->form_name) && $application_details->form_name == 'W') ? 'work-table-w' : '' }}" id="work-table">
                                                        <thead>
                                                            <tr>
                                                                @if(isset($application_details->form_name) && $application_details->form_name == 'S')
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
                                                                @elseif(isset($application_details->form_name) && $application_details->form_name == 'W')
                                                                <th class="work-exp-col-sno text-center">S.No</th>
                                                                <th class="work-exp-col-company">Company Name / Contractor</th>
                                                                <th class="work-exp-col-years" scope="col">
                                                                    <div class="work-exp-years-title">Year of Experience</div>
                                                                    <div class="work-exp-inline work-exp-inline--head">
                                                                        <div class="work-exp-date-group"><span class="work-exp-label-fromto d-block">From (date)</span></div>
                                                                        <div class="work-exp-date-group"><span class="work-exp-label-fromto d-block">To (date)</span></div>
                                                                        <div class="work-exp-total-inline"><span class="work-exp-label-fromto d-block">Total yrs</span></div>
                                                                    </div>
                                                                </th>
                                                                @else
                                                                <th>S.No</th>
                                                                <th>Company Name / Contractor</th>
                                                                <th>Years of Experience (Years)</th>
                                                                @endif
                                                                <th class="work-exp-col-designation">Designation</th>
                                                                @if(isset($application_details->form_name) && $application_details->form_name == 'S')
                                                                    <th class="text-center work-exp-col-upload work-exp-upload-head">
                                                                        Upload Document
                                                                        <br><span class="file-limit text-success small">File type: PDF(Min 5 KB To Max 200 KB)</span>
                                                                    </th>
                                                                @endif
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
                                                            @if ($exp_details->isNotEmpty())
                                                            @foreach ($exp_details as $expRow)
                                                                @if(isset($application_details->form_name) && $application_details->form_name == 'S')
                                                                <tr class="work-fields">
                                                                    @php
                                                                        $workEmpType = $expRow->emp_type ?? 'company';
                                                                        $workEmployerName = $expRow->emp_cate ?? $expRow->company_name ?? '';
                                                                        $workTotalExp = $expRow->total_exp ?? $expRow->experience ?? '';
                                                                        $workFromDate = $expRow->from_date ? \Carbon\Carbon::parse($expRow->from_date)->format('Y-m-d') : '';
                                                                        $workToDate = $expRow->to_date ? \Carbon\Carbon::parse($expRow->to_date)->format('Y-m-d') : '';
                                                                        $workIntimationDate = $expRow->intimation_date ? \Carbon\Carbon::parse($expRow->intimation_date)->format('Y-m-d') : '';
                                                                    @endphp
                                                                    <td class="work-serial text-center">{{ $loop->iteration }}</td>
                                                                    <td class="work-exp-col-type">
                                                                        <select class="form-control form-control-sm work-employment-type" name="work_employment_type[]" required>
                                                                            <option value="company" {{ $workEmpType === 'company' ? 'selected' : '' }}>Company</option>
                                                                            <option value="contractor" {{ $workEmpType === 'contractor' ? 'selected' : '' }}>Contractor</option>
                                                                            <option value="apprentice" {{ $workEmpType === 'apprentice' ? 'selected' : '' }}>Apprentice</option>
                                                                            <option value="electrical_inspector" {{ $workEmpType === 'electrical_inspector' ? 'selected' : '' }}>Electrical Inspector / Assistant Electrical Inspector</option>
                                                                            <option value="retired_employees" {{ $workEmpType === 'retired_employees' ? 'selected' : '' }}>Retired Employees</option>
                                                                        </select>
                                                                    </td>
                                                                    <td class="work-employer-cell work-exp-col-employer">
                                                                        <input type="text" class="form-control form-control-sm work-employer-input" name="work_employer_name[]" maxlength="120" autocomplete="off" placeholder="Company name *" value="{{ $workEmployerName }}">
                                                                        <div class="work-block work-block--intimation mt-1" style="display:none;text-align:left;">
                                                                            <div style="font-size:.7rem;line-height:1.1;margin-bottom:2px;color:#6c757d;white-space:nowrap;display:inline-block;">Intimation&nbsp;letter&nbsp;<span style="color:#d9363e;">*</span></div>
                                                                            <input type="date" class="form-control form-control-sm work-intimation-date" name="work_intimation_date[]" value="{{ $workIntimationDate }}">
                                                                        </div>
                                                                    </td>
                                                                    <td class="work-exp-col-years">
                                                                        <div class="work-exp-inline">
                                                                            <div class="work-exp-date-group">
                                                                                <input type="date" class="form-control form-control-sm work-date-from" name="work_date_from[]" value="{{ $workFromDate }}" title="From date" aria-label="Year of experience from date">
                                                                            </div>
                                                                            <div class="work-exp-date-group">
                                                                                <input type="date" class="form-control form-control-sm work-date-to" name="work_date_to[]" value="{{ $workToDate }}" title="To date" aria-label="Year of experience to date">
                                                                            </div>
                                                                            <div class="work-exp-total-inline">
                                                                                <input type="text" class="form-control form-control-sm work-year-total-display" readonly placeholder="—" tabindex="-1" aria-label="Total years of experience" value="{{ $workTotalExp }}">
                                                                                <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]" value="{{ $workTotalExp }}">
                                                                            </div>
                                                                        </div>
                                                                        <input type="hidden" name="work_level[]" class="work-level-sync" value="{{ $workEmployerName }}" tabindex="-1" aria-hidden="true">
                                                                        <input type="hidden" name="experience[]" class="experience-sync" value="{{ $workTotalExp }}" tabindex="-1" aria-hidden="true">
                                                                    </td>
                                                                    <td class="work-exp-col-designation">
                                                                        <input autocomplete="off" class="form-control form-control-sm" name="designation[]" type="text" maxlength="80" value="{{ $expRow->designation ?? '' }}">
                                                                    </td>
                                                                    <td class="work-exp-col-upload">
                                                                        <div class="file-section text-center">
                                                                            @if (!empty($expRow->upload_document))
                                                                                <div class="work-doc-container d-flex align-items-center justify-content-center">
                                                                                    <a class="text-primary" href="{{ asset($expRow->upload_document) }}" target="_blank">
                                                                                        <i class="fa fa-file-pdf-o" style="color: red"></i> View
                                                                                    </a>
                                                                                    <button type="button" class="btn btn-sm btn-danger ml-2 remove-work-doc-confirm">Remove</button>
                                                                                </div>
                                                                                <div class="work-doc-input d-none">
                                                                                    <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined mt-1" data-upload-kind="work">
                                                                                        <input class="form-control form-control-sm p-1" name="work_document[]" type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png">
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div class="work-doc-container d-none"></div>
                                                                                <div class="work-doc-input">
                                                                                    <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="work">
                                                                                        <input class="form-control form-control-sm p-1" name="work_document[]" type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png">
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                    <td class="work-exp-col-actions text-center p-1">
                                                                        <div class="form-s-actions-stack">
                                                                            <button type="button" class="btn btn-danger btn-sm remove-work remove_exp py-1 px-2" data-exp_id="{{ $expRow->id }}" data-url="{{ route('delete_experience') }}" title="Remove row">
                                                                                <i class="fa fa-trash-o"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                    <input type="hidden" name="work_id[]" value="{{ $expRow->id ?? '' }}">
                                                                    <input type="hidden" name="existing_work_document[]" value="{{ $expRow->upload_document ?? '' }}">
                                                                    <input type="hidden" name="removed_document_work[]" value="0">
                                                                </tr>
                                                                @elseif(isset($application_details->form_name) && $application_details->form_name == 'W')
                                                                @php
                                                                    $wFromDate = $expRow->from_date ? \Carbon\Carbon::parse($expRow->from_date)->format('Y-m-d') : '';
                                                                    $wToDate   = $expRow->to_date   ? \Carbon\Carbon::parse($expRow->to_date)->format('Y-m-d')   : '';
                                                                    $wTotalExp = $expRow->total_exp ?? $expRow->experience ?? '';
                                                                    $wCompany  = $expRow->emp_cate ?? $expRow->company_name ?? '';
                                                                @endphp
                                                                <tr class="work-fields">
                                                                    <td class="work-serial text-center">{{ $loop->iteration }}</td>
                                                                    <td class="work-exp-col-company">
                                                                        <input autocomplete="off" class="form-control form-control-sm" name="work_level[]" type="text" maxlength="80" value="{{ $wCompany }}">
                                                                    </td>
                                                                    <td class="work-exp-col-years">
                                                                        <div class="work-exp-inline">
                                                                            <div class="work-exp-date-group">
                                                                                <input type="date" class="form-control form-control-sm work-date-from" name="work_date_from[]" value="{{ $wFromDate }}" title="From date">
                                                                            </div>
                                                                            <div class="work-exp-date-group">
                                                                                <input type="date" class="form-control form-control-sm work-date-to" name="work_date_to[]" value="{{ $wToDate }}" title="To date">
                                                                            </div>
                                                                            <div class="work-exp-total-inline">
                                                                                <input type="text" class="form-control form-control-sm work-year-total-display" readonly placeholder="—" tabindex="-1" value="{{ $wTotalExp }}">
                                                                                <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]" value="{{ $wTotalExp }}">
                                                                            </div>
                                                                        </div>
                                                                        <input type="hidden" name="experience[]" class="experience-sync" value="{{ $wTotalExp }}" tabindex="-1" aria-hidden="true">
                                                                    </td>
                                                                    <td class="work-exp-col-designation">
                                                                        <input autocomplete="off" class="form-control form-control-sm" name="designation[]" type="text" maxlength="80" value="{{ $expRow->designation ?? '' }}">
                                                                    </td>
                                                                    <td class="work-exp-col-actions text-center p-1">
                                                                        <button type="button" class="btn btn-danger btn-sm remove-work remove_exp py-1 px-2" data-exp_id="{{ $expRow->id }}" data-url="{{ route('delete_experience') }}" title="Remove row">
                                                                            <i class="fa fa-trash-o"></i>
                                                                        </button>
                                                                    </td>
                                                                    <input type="hidden" name="work_id[]" value="{{ $expRow->id ?? '' }}">
                                                                    <input type="hidden" name="existing_work_document[]" value="">
                                                                    <input type="hidden" name="removed_document_work[]" value="0">
                                                                </tr>
                                                                @else
                                                                <tr class="work-fields text-center">
                                                                    <td>{{ $loop->iteration }}</td>
                                                                    <td><input autocomplete="off" class="form-control" name="work_level[]" type="text" value="{{ $expRow->company_name ?? '' }}"></td>
                                                                    <td><input autocomplete="off" class="form-control" name="experience[]" type="number" value="{{ $expRow->experience ?? '' }}"></td>
                                                                    <td><input autocomplete="off" class="form-control" name="designation[]" type="text" value="{{ $expRow->designation ?? '' }}"></td>
                                                                    <td>
                                                                        <button type="button" class="btn btn-danger remove-work remove_exp" data-exp_id="{{ $expRow->id }}" data-url="{{ route('delete_experience') }}">
                                                                            <i class="fa fa-trash-o"></i>
                                                                        </button>
                                                                    </td>
                                                                    <input type="hidden" name="work_id[]" value="{{ $expRow->id ?? '' }}">
                                                                    <input type="hidden" name="existing_work_document[]" value="">
                                                                    <input type="hidden" name="removed_document_work[]" value="0">
                                                                </tr>
                                                                @endif
                                                            @endforeach
                                                            @else
                                                                @if(isset($application_details->form_name) && $application_details->form_name == 'S')
                                                                <tr class="work-fields">
                                                                    <td class="work-serial text-center">1</td>
                                                                    <td class="work-exp-col-type">
                                                                        <select class="form-control form-control-sm work-employment-type" name="work_employment_type[]" required>
                                                                            <option value="" selected disabled>Select type</option>
                                                                            <option value="company">Company</option>
                                                                            <option value="contractor">Contractor</option>
                                                                            <option value="apprentice">Apprentice</option>
                                                                            <option value="electrical_inspector">Electrical Inspector / Assistant Electrical Inspector</option>
                                                                            <option value="retired_employees">Retired Employees</option>
                                                                        </select>
                                                                    </td>
                                                                    <td class="work-employer-cell work-exp-col-employer">
                                                                        <input type="text" class="form-control form-control-sm work-employer-input" name="work_employer_name[]" maxlength="120" autocomplete="off" disabled>
                                                                        <div class="work-block work-block--intimation mt-1" style="display:none;text-align:left;">
                                                                            <div style="font-size:.7rem;line-height:1.1;margin-bottom:2px;color:#6c757d;white-space:nowrap;display:inline-block;">Intimation&nbsp;letter&nbsp;<span style="color:#d9363e;">*</span></div>
                                                                            <input type="date" class="form-control form-control-sm work-intimation-date" name="work_intimation_date[]">
                                                                        </div>
                                                                    </td>
                                                                    <td class="work-exp-col-years">
                                                                        <div class="work-exp-inline">
                                                                            <div class="work-exp-date-group">
                                                                                <input type="date" class="form-control form-control-sm work-date-from" name="work_date_from[]" disabled title="From date" aria-label="Year of experience from date">
                                                                            </div>
                                                                            <div class="work-exp-date-group">
                                                                                <input type="date" class="form-control form-control-sm work-date-to" name="work_date_to[]" disabled title="To date" aria-label="Year of experience to date">
                                                                            </div>
                                                                            <div class="work-exp-total-inline">
                                                                                <input type="text" class="form-control form-control-sm work-year-total-display" readonly placeholder="—" tabindex="-1" aria-label="Total years of experience">
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
                                                                        <div class="file-section text-center">
                                                                            <div class="work-doc-container d-none"></div>
                                                                            <div class="work-doc-input">
                                                                                <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="work">
                                                                                    <input class="form-control form-control-sm p-1" name="work_document[]" type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="work-exp-col-actions text-center p-1">
                                                                        <div class="form-s-actions-stack">
                                                                            <button type="button" class="btn btn-danger btn-sm remove-work py-1 px-2" title="Remove row">
                                                                                <i class="fa fa-trash-o"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                    <input type="hidden" name="work_id[]">
                                                                    <input type="hidden" name="existing_work_document[]">
                                                                    <input type="hidden" name="removed_document_work[]" value="0">
                                                                </tr>
                                                                @elseif(isset($application_details->form_name) && $application_details->form_name == 'W')
                                                                <tr class="work-fields">
                                                                    <td class="work-serial text-center">1</td>
                                                                    <td class="work-exp-col-company">
                                                                        <input autocomplete="off" class="form-control form-control-sm" name="work_level[]" type="text" maxlength="80">
                                                                    </td>
                                                                    <td class="work-exp-col-years">
                                                                        <div class="work-exp-inline">
                                                                            <div class="work-exp-date-group">
                                                                                <input type="date" class="form-control form-control-sm work-date-from" name="work_date_from[]" title="From date">
                                                                            </div>
                                                                            <div class="work-exp-date-group">
                                                                                <input type="date" class="form-control form-control-sm work-date-to" name="work_date_to[]" title="To date">
                                                                            </div>
                                                                            <div class="work-exp-total-inline">
                                                                                <input type="text" class="form-control form-control-sm work-year-total-display" readonly placeholder="—" tabindex="-1">
                                                                                <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]" value="">
                                                                            </div>
                                                                        </div>
                                                                        <input type="hidden" name="experience[]" class="experience-sync" value="" tabindex="-1" aria-hidden="true">
                                                                    </td>
                                                                    <td class="work-exp-col-designation">
                                                                        <input autocomplete="off" class="form-control form-control-sm" name="designation[]" type="text" maxlength="80">
                                                                    </td>
                                                                    <td class="work-exp-col-actions text-center p-1">
                                                                        <button type="button" class="btn btn-danger btn-sm remove-work py-1 px-2" title="Remove row">
                                                                            <i class="fa fa-trash-o"></i>
                                                                        </button>
                                                                    </td>
                                                                    <input type="hidden" name="work_id[]">
                                                                    <input type="hidden" name="existing_work_document[]">
                                                                    <input type="hidden" name="removed_document_work[]" value="0">
                                                                </tr>
                                                                @else
                                                                <tr class="work-fields text-center">
                                                                    <td>1</td>
                                                                    <td><input autocomplete="off" class="form-control" name="work_level[]" type="text"></td>
                                                                    <td><input autocomplete="off" class="form-control" name="experience[]" type="number"></td>
                                                                    <td><input autocomplete="off" class="form-control" name="designation[]" type="text"></td>
                                                                    <td>
                                                                        <button type="button" class="btn btn-danger remove-work">
                                                                            <i class="fa fa-trash-o"></i>
                                                                        </button>
                                                                    </td>
                                                                    <input type="hidden" name="work_id[]">
                                                                    <input type="hidden" name="existing_work_document[]">
                                                                    <input type="hidden" name="removed_document_work[]" value="0">
                                                                </tr>
                                                                @endif
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                        </div>
                    </div>
                    {{-- /SECTION 6 --}}
                    @endif

                    @if(isset($application_details->form_name) && $application_details->form_name == 'S')
                    {{-- ═══ SECTION 7 — Previous License (Form S only) ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">7</span>
                            <div>
                                <div class="fs-section-title">Have previously applied for Electrical Assistant Qualification Certificate and if yes then mention its number and date</div>
                                <div class="fs-section-tamil">இதற்கு முன்னாள் விண்ணப்பம் செய்துள்ளீர்களா ? ஆம் என்றால் அதன் குறிப்பு எண் மற்றும் தேதியை குறிப்பிடுக</div>
                            </div>
                        </div>
                        <div class="fs-section-body">
                            <div class="fs-radio-group mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input toggle-details" type="radio" name="previous_license" id="previous_license_yes" data-target="#previously_details" value="yes" {{ !empty($application_details->previously_number) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="previous_license_yes">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input toggle-details" type="radio" name="previous_license" id="previous_license_no" data-target="#previously_details" value="no" {{ empty($application_details->previously_number) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="previous_license_no">No</label>
                                </div>
                            </div>
                            <div class="fs-toggle-panel mt-2" id="previously_details" style="display: {{ !empty($application_details->previously_number) ? 'block' : 'none' }};">
                                <div class="row g-2 align-items-end fs-verify-grid">
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Certificate Number <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control text-box single-line verify-input"
                                               id="previously_number" name="previously_number" type="text"
                                               data-type="license" data-error="#licenseError" data-msg="#license_messagdfde"
                                               placeholder="Certificate Number" {{ !empty($application_details->previously_number) ? 'readonly':'' }} value="{{ $application_details->previously_number }}" maxlength="80">
                                        <input type="hidden" id="l_verify" name="l_verify" value="{{ $application_details->license_verify }}">
                                        <span id="licenseError" class="text-danger"></span>
                                        <span id="verify_result"></span>
                                        <span id="license_messagdfde" class="mt-1"></span>
                                        <span class="mt-1 verify_status {{ $application_details->license_verify == 0 ? 'text-danger' : 'text-success' }}">
                                            @if (!empty($application_details->previously_number))
                                                {!! $application_details->license_verify == 0 ? '&#128683; Invalid License.' : '&#10004; Valid License.' !!}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Date of Issue <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control text-box single-line verify-issue-date"
                                               id="previously_issue_date" name="previously_issue_date" type="date"
                                               data-error="#previouslyIssueDateError"
                                               {{ !empty($application_details->previously_number) ? 'readonly':'' }}
                                               value="{{ $application_details->previously_issue_date }}">
                                        <span id="previouslyIssueDateError" class="text-danger"></span>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Date of Expiry <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control text-box single-line verify-date"
                                               id="previously_date" name="previously_date" type="date"
                                               data-error="#dateError"
                                               {{ !empty($application_details->previously_number) ? 'readonly':'' }}
                                               value="{{ $application_details->previously_date }}">
                                        <span id="dateError" class="text-danger"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-verify-actions">
                                            @if (!empty($application_details->previously_number))
                                                <button type="button" class="btn btn-danger remove_verify" data-type="superviser"><i class="fa fa-trash"></i> Delete</button>
                                                <button type="button" class="btn btn-primary verify-btn btn-forms d-none" data-type="license" data-url="{{ route('verifylicense') }}"><i class="fa fa-check-circle"></i> Verify</button>
                                            @else
                                                <button type="button" class="btn btn-primary verify-btn" data-type="license" data-url="{{ route('verifylicense') }}"><i class="fa fa-check-circle"></i> Verify</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>{{-- /fs-toggle-panel --}}
                        </div>{{-- /fs-section-body --}}
                    </div>
                    {{-- /SECTION 7 --}}
                    @endif

                    @php
                        if (isset($application_details->form_name) && $application_details->form_name == 'S') {
                            $questionNumber = 8;
                            $cert_name = 'Wireman Competency Certificate / Supervisor Competency Certificate';
                        } elseif (isset($application_details->form_name) && $application_details->form_name == 'WH') {
                            $questionNumber = 6;
                            $cert_name = 'Wireman Helper Competency Certificate';
                        } elseif (isset($application_details->form_name) && $application_details->form_name == 'W') {
                            $questionNumber = 7;
                            $cert_name = 'Wireman Competency Certificate / Wireman Helper Competency Certificate';
                        } else {
                            $questionNumber = 7;
                            $cert_name = 'Wireman Competency Certificate / Wireman Helper Competency Certificate';
                        }
                    @endphp

                    {{-- ═══ SECTION 8 — Wireman/Helper Competency ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">{{ $questionNumber }}</span>
                            <div>
                                <div class="fs-section-title">Do you possess {{ $cert_name }} issued by this Board? If so furnish the details and surrender the same.</div>
                                <div class="fs-section-tamil">இந்த வாரியம் வழங்கிய கம்பி இணைப்பாளர் திறன் சான்றிதழ் / மேற்பார்வையாளர் திறன் சான்றிதழ் உங்களிடம் உள்ளதா? இருந்தால், அதன் விவரங்களை வழங்கி, அதனை ஒப்படைக்கவும்.</div>
                            </div>
                        </div>
                        <div class="fs-section-body">
                            <div class="fs-radio-group mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input toggle-details" type="radio" name="previous_certificate" id="yesOption" data-target="#wireman_details" value="yes" {{ !empty($application_details->certificate_no) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="yesOption">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input toggle-details" type="radio" name="previous_certificate" id="noOption" data-target="#wireman_details" value="no" {{ empty($application_details->certificate_date) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="noOption">No</label>
                                </div>
                            </div>
                            <div class="fs-toggle-panel mt-2" id="wireman_details" style="display: {{ !empty($application_details->certificate_no) ? 'block' : 'none' }};">
                                                        @php
                                                            if($application_details->form_name == 'S'){
                                                                $cert_type = 'supervisor';
                                                            }else if($application_details->form_name == 'WH'){
                                                                $cert_type = 'helper';
                                                            }else{
                                                                $cert_type = 'certificate';
                                                            }
                                                        @endphp
                                <div class="row g-2 align-items-end fs-verify-grid">
                                                        <div class="col-12 col-md-3">
                                                            <div class="fs-field-label">Certificate Number <span class="req">*</span><span class="text-muted" style="font-size:.75rem;font-weight:400;">(eg. W1234 / H1234, LB2026041234 / LWH2026041234)</span></div>
                                                            <input class="form-control text-box single-line verify-input"
                                                                   id="certificate_no" name="competency_certificate_no" type="text"
                                                                   data-type="{{ $cert_type }}" data-error="#certError" data-msg="#license_message"
                                                                   placeholder="Certificate Number" maxlength="80"
                                                                   value="{{ $application_details->certificate_no }}"
                                                                   {{ !empty($application_details->certificate_no) ? 'readonly':'' }}>
                                                            <input type="hidden" id="cert_verify" name="cert_verify" value="{{ $application_details->cert_verify }}">
                                                            <span id="licenseError" class="text-danger"></span>
                                                            <span id="license_message" class="mt-1"></span>
                                                            <span id="verify_status" class="mt-1 {{ $application_details->cert_verify == 0 ? 'text-danger' : 'text-success' }}">
                                                                @if (!empty($application_details->certificate_no))
                                                                    {!! $application_details->cert_verify == 0 ? '&#128683; Invalid License.' : '&#10004; Valid License.' !!}
                                                                @endif
                                                            </span>
                                                            <span id="certError" class="text-danger"></span>
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <div class="fs-field-label">Date of Issue <span class="req">*</span></div>
                                                            <input class="form-control text-box single-line verify-issue-date"
                                                                   id="certificate_issue_date" name="certificate_issue_date"
                                                                   data-error="#certIssueDateError" type="date"
                                                                   value="{{ $application_details->certificate_issue_date }}"
                                                                   {{ !empty($application_details->certificate_no) ? 'readonly':'' }}>
                                                            <span id="certIssueDateError" class="text-danger"></span>
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <div class="fs-field-label">Date of Expiry <span class="req">*</span></div>
                                                            <input class="form-control text-box single-line verify-date"
                                                                   id="certificate_date" name="certificate_date"
                                                                   data-error="#certDateError" type="date"
                                                                   value="{{ $application_details->certificate_date }}"
                                                                   {{ !empty($application_details->certificate_no) ? 'readonly':'' }}>
                                                            <span id="certDateError" class="text-danger"></span>
                                                        </div>
                                                        <div class="col-12 col-md-2">
                                                            <div class="fs-verify-actions">
                                                            @if (!empty($application_details->certificate_no))
                                                                <button type="button" class="btn btn-danger remove_verify" data-type="superviser_two"><i class="fa fa-trash"></i> Delete</button>
                                                                <button type="button" class="btn btn-primary verify-btn d-none" data-type="{{ $cert_type }}" data-url="{{ route('verifylicense') }}"><i class="fa fa-check-circle"></i> Verify</button>
                                                            @else
                                                                <button type="button" class="btn btn-primary verify-btn" data-type="{{ $cert_type }}" data-url="{{ route('verifylicense') }}"><i class="fa fa-check-circle"></i> Verify</button>
                                                            @endif
                                                            </div>
                                                        </div>
                                </div>
                            </div>{{-- /fs-toggle-panel --}}
                        </div>{{-- /fs-section-body --}}
                    </div>
                    {{-- /SECTION 8 --}}

                    @php
                        if ($formName === 'S') {
                            $uploadQuestionNo = 9;
                        } elseif ($formName === 'W') {
                            $uploadQuestionNo = 8;
                        } elseif ($formName === 'WH') {
                            $uploadQuestionNo = 7;
                        } elseif ($formName === 'P') {
                            $uploadQuestionNo = 7;
                        } else {
                            $uploadQuestionNo = 9;
                        }
                    @endphp

                    {{-- ═══ SECTION 9 — Upload Documents ═══ --}}
                    <div class="fs-section">
                        <div class="fs-section-header">
                            <span class="fs-section-num">{{ $uploadQuestionNo }}</span>
                            <div>
                                <div class="fs-section-title">Upload Documents <span class="section-req">*</span></div>
                                <div class="fs-section-tamil">ஆவணங்களைப் பதிவேற்றவும்</div>
                            </div>
                        </div>
                        <div class="fs-section-body p-0">
                            @php
                                $decryptedaadhar = !empty($application_details->aadhaar) ? safeDecrypt($application_details->aadhaar) : '';
                                $existingPanDoc = $application_details->pancard_doc ?? $application_details->pan_doc ?? '';
                                $hasPhoto = !empty($applicant_photo->upload_path);
                                $hasSign  = !empty($proof_doc?->uploaded_doc);
                            @endphp
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
                                                    <div id="photo-input-wrapper" style="{{ $hasPhoto ? 'display:none;' : 'display:block;' }}">
                                                        <div class="form-s-file-upload-wrap fs-upload-input">
                                                            <input autocomplete="off" class="form-control" id="upload_photo" name="upload_photo" type="file" accept=".jpg,.jpeg,.png">
                                                        </div>
                                                        <span class="file-limit">File type: JPG, PNG (Max 50 KB)</span>
                                                        <span class="error-message text-danger d-block"></span>
                                                    </div>
                                                    @if ($hasPhoto)
                                                        <div class="fs-upload-uploaded" id="photo-uploaded-state">
                                                            <button type="button" class="btn-fs-change" onclick="togglePhotoInput()">
                                                                <i class="fa fa-pencil"></i> Change Photo
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="fs-upload-preview fs-upload-preview--photo">
                                                    <span id="photo_placeholder" class="fs-upload-placeholder" style="{{ $hasPhoto ? 'display:none;' : '' }}">Photo preview</span>
                                                    <img id="preview_applicant" src="{{ $hasPhoto ? url($applicant_photo->upload_path) : '' }}" alt="Photo preview" style="{{ $hasPhoto ? 'display:block;' : 'display:none;' }}">
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
                                            <input type="text" class="form-control" name="aadhaar" id="aadhaar" maxlength="14" style="max-width:260px;" value="{{ $decryptedaadhar }}">
                                            <span id="aadhaar-error" class="text-danger" style="font-size:.78rem;"></span>
                                        </td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">(iii) Upload Aadhaar Document <span class="req">*</span></div>
                                            <div class="fs-field-tamil">ஆதார் ஆவணத்தை பதிவேற்றவும் <span class="req">*</span></div>
                                        </td>
                                        <td style="min-width:200px;">
                                            @if (!empty($application_details->aadhaar_doc))
                                                <div class="aadhaar-doc-container mb-2 d-flex align-items-center">
                                                    <a href="{{ route('document.show', ['type' => 'aadhaar', 'filename' => $application_details->aadhaar_doc]) }}" target="_blank" style="color:#007bff;">
                                                        <i class="fa fa-file-pdf-o" style="color:red;"></i> View
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger ml-3 remove-aadhaar-doc">Remove</button>
                                                </div>
                                            @endif
                                            <div class="aadhaar-doc-input {{ !empty($application_details->aadhaar_doc) ? 'd-none' : '' }}">
                                                <div class="form-s-file-upload-wrap" style="max-width:280px;">
                                                    <input autocomplete="off" class="form-control" id="aadhaar_doc" name="aadhaar_doc" type="file" accept=".pdf,application/pdf">
                                                </div>
                                                <span class="file-limit">File type: PDF (Max 250 KB)</span>
                                                <small class="text-danger file-error d-block"></small>
                                            </div>
                                            <input type="hidden" name="aadhaar_doc_removed" id="aadhaar_doc_removed" value="0">
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
                                            <input type="text" class="form-control text-uppercase" name="pancard" id="pancard" maxlength="10" autocomplete="off" style="max-width:260px;" placeholder="e.g. ABCDE1234F" value="{{ old('pancard', $application_details->pancard ?? '') }}">
                                            <span id="pancard-error" class="text-danger d-block" style="font-size:.78rem;"></span>
                                        </td>
                                        <td class="doc-label-cell">
                                            <div class="fs-field-label">(iv) Upload PAN Card Document</div>
                                            <div class="fs-field-tamil">பான் கார்டு ஆவணத்தைப் பதிவேற்றவும்</div>
                                        </td>
                                        <td style="min-width:200px;">
                                            @if (!empty($existingPanDoc))
                                                <div class="pan-doc-container mb-2 d-flex align-items-center">
                                                    <a href="{{ route('document.show', ['type' => 'pan', 'filename' => $existingPanDoc]) }}" target="_blank" style="color:#007bff;">
                                                        <i class="fa fa-file-pdf-o" style="color:red;"></i> View
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-danger ml-3 remove-pan-doc">Remove</button>
                                                </div>
                                            @endif
                                            <div class="pan-doc-input {{ !empty($existingPanDoc) ? 'd-none' : '' }}">
                                                <div class="form-s-file-upload-wrap" style="max-width:280px;">
                                                    <input autocomplete="off" class="form-control" id="pancard_doc" name="pancard_doc" type="file" accept=".pdf,application/pdf">
                                                </div>
                                                <span class="file-limit">File type: PDF (Max 250 KB)</span>
                                                <small class="text-danger file-error d-block"></small>
                                            </div>
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
                                                    <div id="sign-input-wrapper" style="{{ $hasSign ? 'display:none;' : 'display:block;' }}">
                                                        <div class="form-s-file-upload-wrap fs-upload-input">
                                                            <input autocomplete="off" class="form-control" id="upload_sign" name="upload_sign" type="file" accept=".jpg,.jpeg,.png">
                                                        </div>
                                                        <span class="file-limit">File type: JPG, PNG (Max 50 KB)</span>
                                                        <span class="error-message text-danger d-block"></span>
                                                    </div>
                                                    @if ($hasSign)
                                                        <div class="fs-upload-uploaded" id="sign-uploaded-state">
                                                            <button type="button" class="btn-fs-change" onclick="toggleSignInput()">
                                                                <i class="fa fa-pencil"></i> Change Signature
                                                            </button>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="fs-upload-preview fs-upload-preview--sign">
                                                    <span id="sign_placeholder" class="fs-upload-placeholder" style="{{ $hasSign ? 'display:none;' : '' }}">Signature preview</span>
                                                    <img id="preview_signature" src="{{ $hasSign ? asset($proof_doc->uploaded_doc) : '' }}" alt="Signature preview" style="{{ $hasSign ? 'display:block;' : 'display:none;' }}">
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>{{-- /fs-section-body --}}
                    </div>
                    {{-- /SECTION 9 --}}

                    {{-- ═══ Declaration ═══ --}}
                    <div class="fs-declaration">
                        <label class="container">
                            <input type="checkbox" id="declarationCheckbox" required {{ isset($application) ? 'checked' : '' }}>
                            <span class="checkmark"></span>
                            <div class="decl-text">
                                @if ($formName === 'S')
                                    I hereby declare that the particulars stated above are correct and true to the best of my knowledge. <br>
                                    I request that I may be granted a Supervisor Competency Certificate.<span class="req">*</span>
                                    <span class="tamil">என் அறிவுக்கு எட்டியவரை மேலே குறிப்பிட்டுள்ள விவரங்கள் யாவும் சரியானவை எனவும் உண்மையானவை எனவும் உறுதி கூறுகிறேன். <br> எனக்கு மேற்பார்வையாளர் திறன் சான்றிதழ் வழங்குமாறு கேட்டுக்கொள்கிறேன்.</span>
                                @elseif ($formName === 'W')
                                    I hereby declare that all the details mentioned above are correct and true to the best of my knowledge.<br>
                                    I request that I may be granted a Wireman Competency Certificate.
                                    <span class="tamil">என் அறிவுக்கு எட்டியவரை மேலே குறிப்பிட்டுள்ள விவரங்கள் யாவும் சரியானவை எனவும் உண்மையானவை எனவும் உறுதி கூறுகிறேன். <br>எனக்கு மின்கம்பியாளர் தகுதி சான்றிதழ் எனக்கு வழங்குமாறு வேண்டுகிறேன்.</span>
                                @elseif ($formName === 'WH')
                                    I hereby declare that all the details mentioned above are correct and true to the best of my knowledge.<br>
                                    I request that I may be granted a Wireman Helper Competency Certificate.
                                    <span class="tamil">என் அறிவுக்கு எட்டியவரை மேலே குறிப்பிட்டுள்ள விவரங்கள் யாவும் சரியானவை எனவும் உண்மையானவை எனவும் உறுதி கூறுகிறேன். <br>எனக்கு மின்கம்பி உதவியாளர் தகுதி சான்றிதழ் எனக்கு வழங்குமாறு வேண்டுகிறேன்.</span>
                                @elseif ($formName === 'P')
                                    I hereby declare that the particulars stated above are correct and true to the best of my knowledge.<span class="req">*</span><br>
                                    I request that I may be granted a Power Generating Station Operation and maintenance Competency Certificate.
                                    <span class="tamil">என் அறிவின் படி மேலே குறிப்பிட்டுள்ள விவரங்கள் அனைத்தும் சரியானதும் உண்மையானதுமாக இருப்பதாக நான் இங்கே அறிவிக்கிறேன். <br>மின்சாரம் உற்பத்தி நிலையத்தின் செயல்பாடு மற்றும் பராமரிப்பு திறன் சான்றிதழை எனக்கு வழங்குமாறு நான் கேட்டுக்கொள்கிறேன்.</span>
                                @else
                                    I hereby declare that the particulars stated above are correct and true to the best of my knowledge.
                                    <span class="tamil">என் அறிவுக்கு எட்டியவரை மேலே குறிப்பிட்டுள்ள விவரங்கள் யாவும் சரியானவை எனவும் உண்மையானவை எனவும் உறுதி கூறுகிறேன்.</span>
                                @endif
                            </div>
                        </label>
                        <span id="checkboxError" class="text-danger mt-2 d-block" style="display:none!important;font-size:.82rem;">Please check the declaration box before proceeding.</span>
                    </div>

                    {{-- Hidden fields --}}
                    <input type="hidden" id="form_name" name="form_name"
                        value="{{ isset($application_details) ? $application_details->form_name : '' }}">
                    <input type="hidden" id="license_name" name="license_name"
                        value="{{ isset($application_details) ? $application_details->license_name : '' }}">
                    <input type="hidden" id="form_id" name="form_id"
                        value="{{ isset($application_details) ? $application_details->form_id : '' }}">
                    <input type="hidden" id="amount" name="amount" value="">
                    <input type="hidden" id="appl_type" name="appl_type"
                        value="{{ isset($application_details) ? ($application_details->appl_type ?? 'N') : 'N' }}">
                    @csrf

                    {{-- ── Action buttons ── --}}
                    <div class="fs-action-bar">
                        <button type="button" class="btn-fs-draft" id="saveDraftBtn"
                            data-url="{{ route('form.draft_submit') }}"
                            data-id="{{ $application_details->application_id ?? '' }}">
                            <i class="fa fa-floppy-o"></i> Save As Draft
                        </button>
                        <button type="button" class="btn-fs-submit" id="submitPaymentBtn">
                            <i class="fa fa-eye"></i> Preview &amp; Proceed
                        </button>
                    </div>

                </form>
            </div>{{-- /fs-form-body --}}
        </div>{{-- /fs-card --}}
    </div>{{-- /container --}}
</div>{{-- /fs-page-wrap --}}

<footer class="main-footer">
    @include('include.footer')
</footer>
</div>
<script>
    window.toggleSectionEdit = function(btn) {
        var section = btn.closest('.fs-section');
        if (!section) return;
        var current = section.getAttribute('data-mode') || 'view';
        var next = current === 'edit' ? 'view' : 'edit';
        if (next === 'view') {
            section.querySelectorAll('[data-view-for]').forEach(function(viewEl) {
                var input = document.getElementById(viewEl.getAttribute('data-view-for'));
                if (!input) return;
                var val = input.value;
                if (input.tagName === 'SELECT' && input.options[input.selectedIndex]) {
                    val = input.options[input.selectedIndex].text;
                }
                viewEl.textContent = (val && val.trim() !== '') ? val : 'Not provided';
                viewEl.classList.toggle('fs-view-value--empty', !(val && val.trim() !== ''));
            });
        }
        section.setAttribute('data-mode', next);
        var icon = btn.querySelector('i');
        if (icon) icon.className = next === 'edit' ? 'fa fa-check' : 'fa fa-pencil';
        btn.setAttribute('title', next === 'edit' ? 'Done' : 'Edit');
    };
</script>
<script>
    (function() {
        var uploadPhoto = document.getElementById('upload_photo');
        var previewApplicant = document.getElementById('preview_applicant');
        var photoPlaceholder = document.getElementById('photo_placeholder');
        var photoInputWrapper = document.getElementById('photo-input-wrapper');
        if (uploadPhoto && previewApplicant) {
            uploadPhoto.addEventListener('change', function(event) {
                var file = event.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        previewApplicant.src = e.target.result;
                        previewApplicant.style.display = 'block';
                        if (photoPlaceholder) photoPlaceholder.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        window.togglePhotoInput = function() {
            if (photoInputWrapper) {
                photoInputWrapper.style.display = photoInputWrapper.style.display === 'none' ? 'block' : 'none';
            }
            var photoUploadedState = document.getElementById('photo-uploaded-state');
            if (photoUploadedState) photoUploadedState.style.display = 'none';
        };
    })();
</script>
<script>
    (function() {
        var uploadSign = document.getElementById('upload_sign');
        var previewSignature = document.getElementById('preview_signature');
        var signPlaceholder = document.getElementById('sign_placeholder');
        var signInputWrapper = document.getElementById('sign-input-wrapper');
        if (uploadSign && previewSignature) {
            uploadSign.addEventListener('change', function(event) {
                var file = event.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        previewSignature.src = e.target.result;
                        previewSignature.style.display = 'block';
                        if (signPlaceholder) signPlaceholder.style.display = 'none';
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        window.toggleSignInput = function() {
            if (signInputWrapper) {
                signInputWrapper.style.display = signInputWrapper.style.display === 'none' ? 'block' : 'none';
            }
            var signUploadedState = document.getElementById('sign-uploaded-state');
            if (signUploadedState) signUploadedState.style.display = 'none';
        };
    })();
</script>
<script>
    function clearLocalPreview($fileInput) {
        var $wrap = $fileInput.closest('.form-s-file-upload-wrap');
        var $preview = $wrap.next('.local-file-preview');
        var oldUrl = $preview.data('blobUrl');
        if (oldUrl) URL.revokeObjectURL(oldUrl);
        $preview.remove();
        $fileInput.removeAttr('data-has-local-file');
    }

    $(document).on('change', 'input[type="file"][name^="education_document"], input[type="file"][name^="work_document"]', function() {
        var $input = $(this);
        clearLocalPreview($input);

        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) return;

        var allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        var maxSize = 200 * 1024;
        if (allowed.indexOf(file.type) === -1) {
            window.alert('Only PDF, JPG, PNG files are allowed.');
            this.value = '';
            $input.removeAttr('data-has-local-file');
            return;
        }
        if (file.size > maxSize) {
            window.alert('File size should not exceed 200 KB.');
            this.value = '';
            $input.removeAttr('data-has-local-file');
            return;
        }

        $input.attr('data-has-local-file', '1');
        var blobUrl = URL.createObjectURL(file);
        var isImage = file.type.indexOf('image/') === 0;
        var $preview = $('<div class="local-file-preview"></div>').data('blobUrl', blobUrl);
        if (isImage) {
            $preview.append($('<img>', { src: blobUrl, class: 'img-preview', alt: 'Selected image preview' }));
        }
        $preview.append($('<a>', {
            href: blobUrl,
            target: '_blank',
            rel: 'noopener noreferrer',
            class: 'preview-link'
        }).html(isImage ? '<i class="fa fa-image"></i> Preview image' : '<i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View Document'));
        $input.closest('.form-s-file-upload-wrap').after($preview);
    });

    $(document).on('change', '#aadhaar_doc, #pancard_doc', function() {
        var $input = $(this);
        clearLocalPreview($input);

        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) return;

        var minSize = 10 * 1024;
        var maxSize = 250 * 1024;
        if (file.type !== 'application/pdf') {
            window.alert('Only PDF files are allowed.');
            this.value = '';
            return;
        }
        if (file.size < minSize) {
            window.alert('File size must be at least 10 KB.');
            this.value = '';
            return;
        }
        if (file.size > maxSize) {
            window.alert('File size should not exceed 250 KB.');
            this.value = '';
            return;
        }

        var blobUrl = URL.createObjectURL(file);
        var $preview = $('<div class="local-file-preview"></div>').data('blobUrl', blobUrl);
        $preview.append($('<a>', {
            href: blobUrl,
            target: '_blank',
            rel: 'noopener noreferrer',
            class: 'preview-link'
        }).html('<i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View Document'));
        $input.closest('.form-s-file-upload-wrap').after($preview);
    });

    $(document).on('click', '.remove-aadhaar-doc', function(e) {
        e.preventDefault();
        var $button = $(this);
        Swal.fire({
            title: 'Do you want to remove the document?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;

            var $scope = $button.closest('td, .col-12, .col-md-3');
            var $docContainer = $scope.find('.aadhaar-doc-container').first();
            var $docInput = $scope.find('.aadhaar-doc-input').first();
            var $fileInput = $scope.find('#aadhaar_doc').first();

            $docContainer.removeClass('d-flex align-items-center justify-content-center').addClass('d-none').hide();
            $docInput.removeClass('d-none').show();
            $scope.find('#aadhaar_doc_removed').val('1');
            clearLocalPreview($fileInput);
        });
    });

    $(document).on('click', '.remove-pan-doc', function(e) {
        e.preventDefault();
        var $button = $(this);
        Swal.fire({
            title: 'Do you want to remove the document?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;

            var $scope = $button.closest('td, .col-12, .col-md-3');
            var $docContainer = $scope.find('.pan-doc-container').first();
            var $docInput = $scope.find('.pan-doc-input').first();
            var $fileInput = $scope.find('#pancard_doc').first();

            $docContainer.removeClass('d-flex align-items-center justify-content-center').addClass('d-none').hide();
            $docInput.removeClass('d-none').show();
            $fileInput.val('');
            clearLocalPreview($fileInput);
        });
    });

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

    // Keep hidden #amount dynamic (no static fees in blade)
    // This uses the existing global `getPaymentsService` from the shared footer include.
    $(document).ready(async function () {
        try {
            if (typeof getPaymentsService !== 'function') return;

            const licence_code = ($('#license_name').val() || '').trim();
            const appl_type = ($('#appl_type').val() || '').trim();
            const issued_licence = ($('#license_number').val() || '').trim();

            if (!licence_code || !appl_type) return;

            const data = await getPaymentsService(licence_code, issued_licence, appl_type);
            if (data && data.basic_fees !== undefined && data.basic_fees !== null && data.basic_fees !== '') {
                $('#amount').val(data.basic_fees);
            }
        } catch (e) {
            // ignore; popup/payment flow will handle service errors
        }
    });

    // Add more education row
    $(document).on('click', function(e) {
        if (!e.target.closest(".add-more-education") && !e.target.closest(".remove-education")) return;
        const refreshEducationSerials = () => {
            $('#education-container .education-fields td:first-child').each(function(index) {
                $(this).text(index + 1);
            });
        };

        if (e.target.closest(".add-more-education")) {
            let container = document.getElementById("education-container");
            if (!container) return;
            let educationRows = container.querySelectorAll(".education-fields");
            const isSForm = "{{ $application_details->form_name ?? '' }}" === 'S';
            const isWHForm = "{{ $application_details->form_name ?? '' }}" === 'WH';
            const isWOrWHForm = "{{ $application_details->form_name ?? '' }}" === 'W' || isWHForm;

            if (educationRows.length >= 5) {
                $('#education-table').next('.education-error').remove();

                $('<div class="text-danger mt-2 education-error">You can add a maximum of 5 education entries.</div>')
                .insertAfter('#education-table');

                setTimeout(() => {
                    $('.education-error').fadeOut();
                }, 7000);
                // alert("You can add a maximum of 5 education entries.");
                return;
            }

            let currentYear = new Date().getFullYear();
            let yearOptions = '<option value="">Select Year</option>';
            for (let year = currentYear; year >= 1980; year--) {
                yearOptions += `<option value="${year}">${year}</option>`;
            }

            // calculate next serial number + stable 0-based index for file upload mapping
            let serialNo = $('#education-container .education-fields').length + 1;
            let eduIdx = $('#education-container .education-fields').length;

            let newRow = `
            <tr class="education-fields text-center" data-edu-index="${eduIdx}">
                <td class="edu-serial text-center">${serialNo}</td>
                <td> 
                    <select class="form-control" name="educational_level[]" required>
                        <option value="">Select Education</option>
                        ${isSForm
                            ? '<option value="DEE">Diploma(Electrical Engineering)</option><option value="BEE">B.E(Electrical Engineering)</option><option value="MEE">M.E(Electrical Engineering)</option>'
                            : (isWOrWHForm
                                ? '<option value="Up to 8th Standard">Up to 8th Standard</option><option value="Wireman Helper(H) Certificate">Wireman Helper(H) Certificate</option><option value="ITI Certificate">ITI Certificate</option>'
                                : '<option value="PG">PG</option><option value="UG">UG</option><option value="B.E">B.E</option><option value="M.E">M.E</option>' + (isWHForm ? '<option value="8">8</option>' : ''))}
                    </select>
                </td>
                <td><input type="text" class="form-control" name="institute_name[]" required value="${isWHForm ? 'Dept of Employment & Training' : ''}"></td>
                <td>
                    <select name="month_of_passing[]" class="form-control" required>
                        <option value="">Select Month</option>
                        <option value="01">Jan</option>
                        <option value="02">Feb</option>
                        <option value="03">Mar</option>
                        <option value="04">Apr</option>
                        <option value="05">May</option>
                        <option value="06">Jun</option>
                        <option value="07">Jul</option>
                        <option value="08">Aug</option>
                        <option value="09">Sep</option>
                        <option value="10">Oct</option>
                        <option value="11">Nov</option>
                        <option value="12">Dec</option>
                    </select>
                </td>
                <td>
                    <select name="year_of_passing[]" class="form-control" required>
                        ${yearOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control certificate-input" name="certificate_no[]" maxlength="20" placeholder="Certificate No" required>
                    <span class="error text-danger certificate-error"></span>
                </td>
                <td>
                    <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="education">
                        <input type="file" class="form-control education-file" name="education_document[${eduIdx}]" accept="${isSForm ? '.pdf,application/pdf' : '.pdf,application/pdf,image/jpeg,image/png'}" required>
                    </div>
                </td>
                <td class="form-s-actions-cell text-center p-1">
                    <div class="form-s-actions-stack">
                        <button type="button" class="btn btn-danger btn-sm remove-education py-1 px-2" title="Remove row">
                            <i class="fa fa-trash-o"></i>
                        </button>
                    </div>
                    <input type="hidden" name="edu_id[]" value="">
                    <input type="hidden" name="existing_document[]" value="">
                    <input type="hidden" class="removed-document-edu" name="removed_document[]" value="0">
                </td>
            </tr> `;
            $('#education-container').append(newRow);
            refreshEducationSerials();

        }

        if (e.target.closest(".remove-education")) {
            // if (educationRows.length <= 1) {

            //     $('#education-table').next('.education-error').remove();

            //     $('<div class="text-danger mt-2 education-error">You must have at least one education entry.</div>')
            //     .insertAfter('#education-table');

            //     setTimeout(() => {
            //         $('.education-error').fadeOut();
            //     }, 7000);

            //     // alert("You must have at least one education entry.");
            //     return;
            // }
            e.target.closest("tr").remove();
            refreshEducationSerials();
        }
    });

    // Handle removing existing/newly uploaded education documents (toggle view <-> input)
    $(document).on('click', '.remove-doc_edu_confirm', function(e) {
        e.preventDefault();
        var $button = $(this);
        Swal.fire({
            title: 'Do you want to remove the document?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            var $row = $button.closest('tr');
            var $docContainer = $row.find('.edu-doc-container');
            var $docInput = $row.find('.edu-doc-input');

            $docContainer.removeClass('d-flex align-items-center').addClass('d-none').hide();
            $docInput.removeClass('d-none').show();
            $row.find('input[name="existing_document[]"]').first().val('');
            $row.find('input[name="removed_document[]"]').first().val('1');
            clearLocalPreview($docInput.find('input[type="file"]').first());
        });
    });

    // Remove education row
    // $(document).on('click', '.remove-education', function() {
    //     $(this).closest('tr').remove();
    // });

    (function() {
        var isSForm = "{{ $application_details->form_name ?? '' }}" === 'S';
        var isWForm = "{{ $application_details->form_name ?? '' }}" === 'W';
        function refreshWorkSerials() {
            $('#work-container .work-fields .work-serial').each(function(index) {
                $(this).text(index + 1);
            });
            $('#work-container .work-fields.text-center td:first-child').each(function(index) {
                if (!$(this).hasClass('work-serial')) {
                    $(this).text(index + 1);
                }
            });
        }

        if (isWForm) {
            function syncExpHiddenW($tr) {
                var tot = ($tr.find('.work-experience-total-hidden').val() || '').trim();
                $tr.find('.experience-sync').val(tot);
            }

            function updateTotalYearsW($tr) {
                var fromStr = readIsoDate($tr.find('.work-date-from'));
                var toStr   = readIsoDate($tr.find('.work-date-to'));
                if (!fromStr || !toStr) {
                    $tr.find('.work-year-total-display').val('');
                    $tr.find('.work-experience-total-hidden').val('');
                    syncExpHiddenW($tr); return;
                }
                var from = new Date(fromStr + 'T12:00:00'), to = new Date(toStr + 'T12:00:00');
                if (isNaN(from.getTime()) || isNaN(to.getTime())) {
                    $tr.find('.work-year-total-display').val('');
                    $tr.find('.work-experience-total-hidden').val('');
                    syncExpHiddenW($tr); return;
                }
                var display, hidden;
                if (to < from) { display = 'Invalid range'; hidden = ''; }
                else {
                    var years = (to - from) / 86400000 / 365.25;
                    var rounded = Math.round(years * 10) / 10;
                    hidden = rounded.toFixed(1); display = rounded.toFixed(1);
                }
                $tr.find('.work-year-total-display').val(display);
                $tr.find('.work-experience-total-hidden').val(hidden);
                syncExpHiddenW($tr);
            }

            $(document).on('change', '.work-date-from, .work-date-to', function() {
                updateTotalYearsW($(this).closest('tr.work-fields'));
            });

            $(document).on('click', function(e) {
                if (!e.target.closest('.add-more-work') && !e.target.closest('.remove-work')) return;

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
                    // Reset date inputs: clone may have been switched to type="text" / DD-MM-YYYY
                    // by initDateDisplay, and addEventListener listeners aren't cloned.
                    newRow.querySelectorAll('.work-date-from, .work-date-to').forEach(function(inp) {
                        inp.removeAttribute('data-raw');
                        inp.value = '';
                        inp.type = 'date';
                    });
                    var wtd = newRow.querySelector('.work-year-total-display'); if (wtd) wtd.value = '';
                    var hTot = newRow.querySelector('.work-experience-total-hidden'); if (hTot) hTot.value = '';
                    var hEx = newRow.querySelector('.experience-sync'); if (hEx) hEx.value = '';
                    var wIn = newRow.querySelector('input[name="work_level[]"]'); if (wIn) wIn.value = '';
                    var dIn = newRow.querySelector('input[name="designation[]"]'); if (dIn) dIn.value = '';
                    var idIn = newRow.querySelector('input[name="work_id[]"]'); if (idIn) idIn.value = '';
                    var docIn = newRow.querySelector('input[name="existing_work_document[]"]'); if (docIn) docIn.value = '';
                    container.appendChild(newRow);
                    if (typeof initDateDisplay === 'function') {
                        newRow.querySelectorAll('.work-date-from, .work-date-to').forEach(initDateDisplay);
                    }
                    refreshWorkSerials();
                }

                if (e.target.closest('.remove-work')) {
                    e.target.closest('tr').remove();
                    refreshWorkSerials();
                }
            });

            $(document).ready(function() { refreshWorkSerials(); });
            return;
        }

        var EMP_LABELS = {
            '': '',
            company: 'Company name *',
            contractor: 'Contractor / firm name *',
            apprentice: 'Establishment / training organization *',
            electrical_inspector: 'Office / department *',
            retired_employees: 'Name of PSU (State / Central / Corporation) *'
        };

        function $workRow(el) {
            return $(el).closest('tr.work-fields');
        }

        function syncLegacyHidden($tr) {
            var emp = ($tr.find('.work-employer-input').val() || '').trim();
            var tot = ($tr.find('.work-experience-total-hidden').val() || '').trim();
            $tr.find('.work-level-sync').val(emp);
            $tr.find('.experience-sync').val(tot);
        }

        function readIsoDate($input) {
            if (!$input || !$input.length) return '';
            var raw = ($input.attr('data-raw') || '').trim();
            if (raw) return raw;
            var v = ($input.val() || '').trim();
            if (!v) return '';
            // YYYY-MM-DD already
            if (/^\d{4}-\d{2}-\d{2}$/.test(v)) return v;
            // DD-MM-YYYY display format → convert to ISO
            var m = v.match(/^(\d{2})-(\d{2})-(\d{4})$/);
            if (m) return m[3] + '-' + m[2] + '-' + m[1];
            return '';
        }

        function updateTotalYears($tr) {
            var $from = $tr.find('.work-date-from');
            var $to = $tr.find('.work-date-to');
            var fromStr = readIsoDate($from);
            var toStr = readIsoDate($to);
            var display = '';
            var hidden = '';
            if (!fromStr || !toStr) {
                $tr.find('.work-year-total-display').val('');
                $tr.find('.work-experience-total-hidden').val('');
                syncLegacyHidden($tr);
                return;
            }
            var from = new Date(fromStr + 'T12:00:00');
            var to = new Date(toStr + 'T12:00:00');
            if (isNaN(from.getTime()) || isNaN(to.getTime())) {
                $tr.find('.work-year-total-display').val('');
                $tr.find('.work-experience-total-hidden').val('');
                syncLegacyHidden($tr);
                return;
            }
            if (to < from) {
                display = 'Invalid range';
                hidden = '';
            } else {
                var msPerDay = 86400000;
                var years = (to - from) / msPerDay / 365.25;
                var rounded = Math.round(years * 10) / 10;
                hidden = rounded.toFixed(1);
                display = rounded.toFixed(1);
            }
            $tr.find('.work-year-total-display').val(display);
            $tr.find('.work-experience-total-hidden').val(hidden);
            syncLegacyHidden($tr);
        }

        function applyEmploymentType($tr) {
            var t = $tr.find('.work-employment-type').val() || '';
            $tr.find('.work-employer-input').attr('placeholder', EMP_LABELS[t] || '');

            var $emp = $tr.find('.work-employer-input');
            var $yFrom = $tr.find('.work-date-from');
            var $yTo = $tr.find('.work-date-to');
            var $blockInt = $tr.find('.work-block--intimation');
            var $intDate = $tr.find('.work-intimation-date');

            if (!t) {
                $emp.prop('disabled', true).prop('required', false);
                $yFrom.prop('disabled', true).prop('required', false);
                $yTo.prop('disabled', true).prop('required', false);
                $blockInt.hide();
                // Keep the intimation input enabled (just hidden/cleared) so its POST array
                // index stays aligned with the other work_* arrays. Disabled inputs are not
                // submitted, which causes off-by-one row mismatches on save.
                $intDate.prop('disabled', false).prop('required', false).val('');
                syncLegacyHidden($tr);
                return;
            }

            $emp.prop('disabled', false).prop('required', true);
            $yFrom.prop('disabled', false).prop('required', true);
            $yTo.prop('disabled', false).prop('required', true);

            if (t === 'contractor') {
                $blockInt.show();
                $intDate.prop('disabled', false).prop('required', true);
            } else {
                $blockInt.hide();
                $intDate.prop('disabled', false).prop('required', false).val('');
            }

            updateTotalYears($tr);
            syncLegacyHidden($tr);
        }

        function initWorkRow($tr) {
            if (($tr.find('.work-employment-type').val() || '') === '') {
                $tr.find('.work-employment-type').val('company');
            }
            applyEmploymentType($tr);
            syncLegacyHidden($tr);
        }

        $(document).ready(function() {
            $('#work-container .work-fields').each(function() {
                initWorkRow($(this));
            });
            refreshWorkSerials();
        });

        $(document).on('change', '.work-employment-type', function() {
            applyEmploymentType($workRow(this));
        });

        $(document).on('change', '.work-date-from, .work-date-to', function() {
            updateTotalYears($workRow(this));
        });

        $(document).on('input change', '.work-employer-input, .work-intimation-date', function() {
            syncLegacyHidden($workRow(this));
        });

        $(document).on('click', '.remove-work-doc-confirm', function(e) {
            e.preventDefault();
            var $button = $(this);
            Swal.fire({
                title: 'Do you want to remove the document?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                var $row = $button.closest('tr');
                var $docContainer = $row.find('.work-doc-container');
                var $docInput = $row.find('.work-doc-input');

                $docContainer.removeClass('d-flex align-items-center justify-content-center').addClass('d-none').hide();
                $docInput.removeClass('d-none').show();
                $row.find('input[name="existing_work_document[]"]').val('');
                $row.find('input[name="removed_document_work[]"]').val('1');
                clearLocalPreview($docInput.find('input[type="file"]').first());
            });
        });

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
                newRow.querySelectorAll('input[type="file"]').forEach(function(el) { el.value = ''; });
                // Reset all date inputs back to native date pickers. The source row may have
                // been switched to type="text" by initDateDisplay (DD-MM-YYYY display mode);
                // cloneNode preserves that mutated state, so we explicitly restore type="date".
                newRow.querySelectorAll('.work-date-from, .work-date-to, .work-intimation-date').forEach(function(inp) {
                    inp.type = 'date';
                    inp.value = '';
                    inp.removeAttribute('data-raw');
                });
                var typeSel = newRow.querySelector('.work-employment-type');
                if (typeSel) typeSel.value = '';
                var wtd = newRow.querySelector('.work-year-total-display');
                if (wtd) wtd.value = '';
                var hTot = newRow.querySelector('.work-experience-total-hidden');
                if (hTot) hTot.value = '';
                var hLevel = newRow.querySelector('.work-level-sync');
                if (hLevel) hLevel.value = '';
                var hEx = newRow.querySelector('.experience-sync');
                if (hEx) hEx.value = '';
                var empIn = newRow.querySelector('.work-employer-input');
                if (empIn) empIn.value = '';
                var desIn = newRow.querySelector('input[name="designation[]"]');
                if (desIn) desIn.value = '';
                var workId = newRow.querySelector('input[name="work_id[]"]');
                if (workId) workId.value = '';
                var existingDoc = newRow.querySelector('input[name="existing_work_document[]"]');
                if (existingDoc) existingDoc.value = '';
                var removedDoc = newRow.querySelector('input[name="removed_document_work[]"]');
                if (removedDoc) removedDoc.value = '0';
                var docContainer = newRow.querySelector('.work-doc-container');
                if (docContainer) {
                    docContainer.classList.add('d-none');
                    docContainer.innerHTML = '';
                }
                var docInput = newRow.querySelector('.work-doc-input');
                if (docInput) docInput.classList.remove('d-none');

                container.appendChild(newRow);
                // Re-bind the date display formatter on the cloned date inputs so they
                // behave the same as the original row (native picker on focus,
                // DD-MM-YYYY display on blur once a value is chosen).
                if (typeof initDateDisplay === 'function') {
                    newRow.querySelectorAll('.work-date-from, .work-date-to, .work-intimation-date').forEach(initDateDisplay);
                }
                initWorkRow($(newRow));
                refreshWorkSerials();
                return;
            }

            if (e.target.closest('.remove-work')) {
                if (workRows.length <= 1) {
                    $('#work-table').next('.work-error').remove();
                    $('<div class="text-danger mt-2 work-error">You must have at least one work experience entry.</div>').insertAfter('#work-table');
                    setTimeout(function() { $('.work-error').fadeOut(); }, 7000);
                    return;
                }
                e.target.closest('tr').remove();
                refreshWorkSerials();
            }
        });
    })();

    // Remove work row
    // $(document).on('click', '.remove-work', function() {
    //     $(this).closest('tr').remove();
    // });

    // ── Date display formatter: show DD-MM-YYYY, revert to picker on focus ──
    function initDateDisplay(inp) {
        function toDisplay(raw) {
            if (!raw) return;
            var p = raw.split('-');
            if (p.length === 3) { inp.type = 'text'; inp.value = p[2] + '-' + p[1] + '-' + p[0]; }
        }
        if (inp.value) { inp.setAttribute('data-raw', inp.value); toDisplay(inp.value); }
        inp.addEventListener('focus', function() {
            var raw = this.getAttribute('data-raw') || '';
            this.type = 'date'; if (raw) this.value = raw;
        });
        inp.addEventListener('blur', function() {
            if (this.type === 'date' && this.value) {
                this.setAttribute('data-raw', this.value); toDisplay(this.value);
            }
        });
        inp.addEventListener('change', function() {
            if (this.type === 'date' && this.value) this.setAttribute('data-raw', this.value);
        });
    }
    document.querySelectorAll('.work-date-from, .work-date-to, .work-intimation-date').forEach(initDateDisplay);

</script>
</body>

</html>
