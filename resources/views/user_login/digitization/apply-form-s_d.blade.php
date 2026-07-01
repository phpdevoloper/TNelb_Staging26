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

    /* ── Work Experience: row-grid layout (restructured) ──── */
    .work-exp-wrap {
        --wx-accent: #035ab3;
        --wx-accent-2: #0472d9;
        --wx-bg: #fff;
        --wx-surface: #f7faff;
        --wx-border: #dde5f3;
        --wx-border-strong: #c8d8f5;
        --wx-muted: #6b7a99;
        --wx-text: #1a2a4a;
        --wx-amber: #b76e00;
        --wx-amber-bg: #fff5e0;
        --wx-green: #1f7a3a;
        --wx-green-bg: #e6f7ec;
        --wx-red: #c1272d;
        --wx-radius: 10px;
        --wx-radius-sm: 6px;
        display: flex; flex-direction: column; gap: 12px;
    }

    /* Section header bar — Add row button only */
    .work-exp-section-bar {
        display: flex; align-items: center; justify-content: flex-end;
        gap: 12px; flex-wrap: wrap;
        padding: 0 0 4px;
        border: none;
        background: transparent;
    }
    .work-exp-section-bar .work-exp-add-btn {
        display: inline-flex; align-items: center; gap: 6px;
        background: var(--wx-accent); color: #fff; border: none;
        border-radius: 7px; padding: 6px 12px; font-size: .8rem; font-weight: 600;
        cursor: pointer; transition: background .2s, transform .15s, box-shadow .2s;
        box-shadow: 0 2px 5px rgba(3,90,179,.16);
    }
    .work-exp-section-bar .work-exp-add-btn:hover:not(:disabled) {
        background: #024a98; transform: translateY(-1px); box-shadow: 0 4px 9px rgba(3,90,179,.22);
    }
    .work-exp-section-bar .work-exp-add-btn:disabled {
        background: #b6c2d6; cursor: not-allowed; box-shadow: none; opacity: .8;
    }

    /* Rows container — light page bg like order list */
    .work-rows {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 4px 0;
    }

    /* ── A single work-experience row (all fields visible inline) ────── */
    .work-row {
        background: var(--wx-bg);
        border: 1px solid var(--wx-border);
        border-radius: var(--wx-radius);
        box-shadow: 0 1px 3px rgba(3,90,179,.05);
        transition: box-shadow .2s, border-color .2s, opacity .18s, transform .18s;
        overflow: hidden;
        animation: wxRowIn .22s ease;
    }
    .work-row:hover { box-shadow: 0 3px 10px rgba(3,90,179,.08); border-color: var(--wx-border-strong); }
    .work-row.is-removing { opacity: 0; transform: translateY(-6px) scale(.98); }
    @keyframes wxRowIn {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* Row header (always visible — serial + title/summary + status pill + remove) */
    .work-row-head {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 14px;
        background: linear-gradient(135deg, #f7faff 0%, #fbfdff 100%);
        border-bottom: 1px solid var(--wx-border);
        transition: background .18s ease, border-color .18s ease;
    }
    .work-row-serial,
    .work-row-serial--card,
    .work-card-serial { display: none !important; }
    .work-row-title { display: none !important; }
    .work-row-summary {
        flex: 1 1 auto;
        min-width: 0;
        display: none;
    }
    /* Collapsed complete entry — summary table */
    .wx-order-card {
        display: block;
        width: 100%;
        padding-right: 28px;
    }
    .wx-summary-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        margin: 0;
    }
    .wx-summary-table {
        width: 100%;
        min-width: 0;
        table-layout: auto;
        border-collapse: collapse;
        margin: 0;
        font-size: 12px;
    }
    .wx-summary-table .wx-summary-th-sno,
    .wx-summary-table .work-row-summary-sno {
        width: 52px;
        min-width: 52px;
        max-width: 52px;
        text-align: center;
        vertical-align: middle;
    }
    .wx-summary-table .work-row-summary-sno {
        font-weight: 700;
        color: var(--wx-accent);
        font-size: .85rem;
    }
    .wx-summary-table thead th {
        font-size: 12px;
        font-weight: 700;
        color: #1a3a6b;
        text-transform: uppercase;
        letter-spacing: .03em;
        padding: 9px 8px;
        border: 1px solid #b8cfe8;
        background: linear-gradient(180deg, #dce8f8 0%, #c8daf2 100%);
        vertical-align: middle;
        line-height: 1.25;
        text-align: center;
        white-space: normal;
    }
    .wx-summary-table .wx-summary-th-org,
    .wx-summary-table .work-row-summary-org-address {
        min-width: 108px;
        max-width: 124px;
    }
    .wx-summary-table .wx-summary-th-org .wx-th-org-line {
        display: block;
        white-space: nowrap;
        line-height: 1.2;
    }
    .wx-summary-table thead th small {
        display: block;
        font-size: .62rem;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0;
        color: #4a6288;
        margin-top: 3px;
        line-height: 1.2;
    }
    .wx-summary-table tbody td {
        padding: 10px;
        border: 1px solid #e8edf6;
        vertical-align: top;
        font-size: 12px;
        font-weight: 600;
        color: #212121;
        line-height: 1.35;
        word-break: break-word;
        background: #fff;
    }
    .wx-summary-table .wx-sum-main { display: block; font-weight: 600; color: #212121; }
    .wx-summary-table .wx-sum-sub,
    .wx-summary-table .wx-sum-line {
        display: block;
        font-size: .92em;
        font-weight: 500;
        color: #555;
        margin-top: 4px;
        line-height: 1.3;
    }
    .wx-summary-table .wx-sum-line strong {
        font-weight: 600;
        color: #878787;
        font-size: .88em;
        text-transform: uppercase;
        letter-spacing: .02em;
    }
    .wx-summary-table .wx-sum-dur { color: var(--wx-green); font-weight: 600; }
    /* Period column — date + duration mini boxes */
    .wx-summary-table .work-row-summary-period { min-width: 168px; padding: 8px; }
    .wx-period-box {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 0;
    }
    .wx-period-dates {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 5px;
    }
    .wx-period-mini {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        padding: 5px 6px;
        background: #f7faff;
        border: 1px solid #ccd9ec;
        border-radius: 5px;
        text-align: center;
        min-width: 0;
    }
    .wx-period-mini .wx-period-label {
        font-size: .58rem;
        font-weight: 700;
        color: #878787;
        text-transform: uppercase;
        letter-spacing: .04em;
        line-height: 1;
    }
    .wx-period-mini .wx-period-val {
        font-size: .72rem;
        font-weight: 600;
        color: #212121;
        line-height: 1.25;
        word-break: break-word;
    }
    .wx-period-duration {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 4px;
        padding: 4px 5px;
        border: 1px solid #b8dfc4;
        border-radius: 5px;
        background: linear-gradient(135deg, #f4fbf6 0%, #e6f7ec 100%);
    }
    .wx-period-dur-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1px;
        text-align: center;
        min-width: 0;
    }
    .wx-period-dur-cell .wx-period-dur-num {
        font-size: .82rem;
        font-weight: 700;
        color: var(--wx-green);
        line-height: 1.1;
    }
    .wx-period-dur-cell .wx-period-dur-lbl {
        font-size: .5rem;
        font-weight: 600;
        color: #5a8a6a;
        text-transform: uppercase;
        letter-spacing: .03em;
        line-height: 1;
    }
    .wx-summary-table .wx-sum-doc-link {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #0056b3 !important;
        font-size: .78rem;
        font-weight: 600;
        text-decoration: none;
        margin-top: 2px;
    }
    .wx-summary-table .wx-sum-doc-link:hover { text-decoration: underline; }
    .wx-summary-table .wx-sum-doc-link .fa-file-pdf-o { color: #d9534f !important; }
    .wx-summary-table .wx-sum-doc-link .fa-image { color: var(--wx-accent); }
    /* Attachment column — centered stacked label + link */
    .wx-summary-table .work-row-summary-attachments {
        text-align: center;
        vertical-align: middle;
        min-width: 108px;
    }
    .wx-summary-table .wx-sum-attach-stack {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .wx-summary-table .wx-sum-attach-block {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 3px;
        line-height: 1.3;
    }
    .wx-summary-table .wx-sum-attach-label {
        display: block;
        font-size: .78em;
        font-weight: 600;
        color: #878787;
        text-transform: capitalize;
    }
    .wx-summary-table .wx-sum-attach-value {
        display: block;
        font-size: .78rem;
        font-weight: 600;
        color: #555;
    }
    .wx-summary-table .wx-sum-attach-block .wx-sum-doc-link {
        margin-top: 0;
    }
    .wx-summary-footer {
        display: flex;
        align-items: center;
        margin-top: 12px;
        padding-top: 10px;
        border-top: 1px solid #f0f0f0;
    }
    /* Shared summary table — all complete entries in one thead */
    .work-exp-summary-panel {
        display: none;
        margin-bottom: 12px;
    }
    .work-exp-summary-panel.is-visible { display: block; }
    .work-exp-summary-panel .wx-order-card { padding-right: 0; }
    .wx-summary-table .wx-summary-th-actions,
    .wx-summary-table .work-row-summary-actions {
        width: 72px;
        min-width: 72px;
        max-width: 72px;
        text-align: center;
        vertical-align: middle;
    }
    .wx-summary-table .work-row-summary-actions {
        white-space: nowrap;
    }
    .wx-summary-table .work-row-summary-actions .work-row-remove {
        margin-left: 4px;
    }
    .work-row.work-row--in-summary { display: none !important; }
    .wx-order-edit-link {
        appearance: none;
        background: transparent;
        border: 0;
        padding: 0;
        margin: 0;
        box-shadow: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: .78rem;
        font-weight: 600;
        color: var(--wx-accent);
        line-height: 1.2;
    }
    .wx-order-edit-link:hover { color: #024a98; text-decoration: underline; }
    .wx-order-edit-link i { font-size: .72rem; }
    .work-row-summary-subline,
    .work-row-summary-top,
    .work-row-summary-meta,
    .work-row-summary-hint,
    .work-row-summary-main,
    .work-row-summary-duration,
    .wx-order-cell,
    .wx-order-label,
    .wx-order-main { display: none !important; }
    .work-row-head-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        margin-left: auto;
    }
    .work-row-toggle-btn {
        appearance: none;
        background: transparent;
        border: 0;
        padding: 0;
        margin: 0;
        box-shadow: none;
        cursor: pointer;
        width: 26px; height: 26px;
        display: none;
        align-items: center; justify-content: center;
        border-radius: 5px;
        color: var(--wx-accent);
        line-height: 1;
        transition: background .15s, transform .2s;
    }
    .work-row-toggle-btn:hover { background: rgba(3,90,179,.08); }
    .work-row-toggle-btn i { transition: transform .22s ease; }
    .work-row--expanded .work-row-toggle-btn i { transform: rotate(180deg); }
    .work-row-spacer { flex: 1 1 auto; }
    .work-card-status-pill { display: none !important; }
    .work-card-remove,
    .work-row-remove {
        appearance: none;
        background: transparent;
        border: 0;
        padding: 0;
        margin: 0;
        box-shadow: none;
        cursor: pointer;
        width: 26px; height: 26px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 5px;
        color: var(--wx-red);
        line-height: 1;
        transition: background .15s;
    }
    .work-card-remove:hover,
    .work-row-remove:hover { background: rgba(193,39,45,.08); }

    /* Row grid — uniform compact field sizing */
    .work-row-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 12px 14px;
        padding: 14px 16px;
        transition: opacity .22s ease, padding .22s ease;
    }
    .work-row-grid-span { grid-column: 1 / -1; }
    .work-board-member-panel {
        margin-top: 4px;
        padding: 12px 14px;
        background: #f4f8fd;
        border: 1px solid #c5d5eb;
        border-radius: 8px;
    }
    .work-board-member-panel-hd {
        margin-bottom: 10px;
        font-size: .82rem;
        font-weight: 600;
        color: #1a2a4a;
        line-height: 1.35;
    }
    .work-board-member-panel-hint {
        display: block;
        font-size: .72rem;
        font-weight: 500;
        color: #5a7299;
        margin-top: 2px;
    }
    .work-board-member-panel-body {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 10px 14px;
    }
    .work-board-member-panel-note {
        margin: 10px 0 0;
        font-size: .72rem;
        color: #5a7299;
    }
    .work-row.work-row--board-member [data-field="contractor-cat"],
    .work-row.work-row--board-member [data-field="licence-number"],
    .work-row.work-row--board-member [data-field="work-nature"],
    .work-row.work-row--board-member [data-field="voltage-level"],
    .work-row.work-row--board-member [data-field="transformer-kva"] {
        display: none !important;
    }

    /* Field cell: label on top, input below */
    .work-card-field {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 0;
    }
    .work-card-field-label {
        font-size: 12px; font-weight: 600; color: var(--wx-text);
        line-height: 1.2;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .work-card-field-label .req { color: var(--wx-red); font-weight: 700; }
    .work-card-field .form-control {
        font-size: .78rem; padding: 5px 9px; line-height: 1.28;
        border: 1px solid #ccd5e3; border-radius: 5px;
        height: auto;
        width: 100%;
    }
    .work-card-field .form-control:focus {
        border-color: var(--wx-accent);
        box-shadow: 0 0 0 3px rgba(3,90,179,.12);
        outline: none;
    }
    .work-card-field .form-control:disabled,
    .work-card-field input[readonly]:not(.work-duration-y):not(.work-duration-m):not(.work-duration-d) {
        background: #f1f3f5; color: var(--wx-muted); cursor: not-allowed;
    }
    .work-card-field input[type="file"].is-locked {
        background: #f1f3f5; pointer-events: none; opacity: .55;
    }
    .work-card-field-hint {
        font-size: .62rem; color: var(--wx-muted);
        display: inline-flex; align-items: center; gap: 4px; line-height: 1.2;
    }
    .work-card-field-hint i { font-size: .64rem; }
    .work-card-field.is-locked .work-card-field-label { color: var(--wx-muted); }
    .work-card-field.is-locked .work-card-field-label .lock-icon {
        font-size: .68rem; opacity: .65;
    }

    /* Till-date toggle sits just below the To-date input inside its field cell */
    .work-card-till-toggle {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: .66rem; color: var(--wx-text);
        background: #fff; border: 1px dashed var(--wx-border-strong);
        padding: 3px 9px; border-radius: 5px; cursor: pointer;
        margin-top: 2px; user-select: none;
        transition: background .15s, border-color .15s;
        width: max-content; max-width: 100%;
    }
    .work-card-till-toggle:hover { background: #f7faff; border-color: var(--wx-accent); }
    .work-card-till-toggle input { accent-color: var(--wx-accent); margin: 0; }
    .work-card-till-toggle input:checked + span { color: var(--wx-accent); font-weight: 600; }

    /* File upload pill — fills its field cell */
    .work-row .form-s-file-upload-wrap--combined {
        max-width: none; width: 100%;
        border-radius: 5px;
    }
    .work-row .form-s-file-upload-wrap--combined .form-control,
    .work-row .form-s-file-upload-wrap--combined input[type="file"] {
        font-size: .78rem !important;
        padding: 5px 9px !important;
    }

    /* Duration readout — compact single cell (not full row width) */
    .work-card-field--duration {
        grid-column: span 1;
        max-width: 220px;
    }
    .work-card-field--duration .work-card-duration-readout {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 4px;
        padding: 4px 5px;
        max-width: 220px;
        background: #fff;
        border: 1px solid var(--wx-border);
        border-radius: 5px;
    }
    .work-card-duration-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0;
        text-align: center;
        min-width: 0;
    }
    .work-card-duration-cell .work-duration-label {
        font-size: .48rem;
        font-weight: 600;
        color: var(--wx-muted);
        text-transform: uppercase;
        letter-spacing: .03em;
        line-height: 1;
    }
    .work-card-duration-cell .form-control {
        font-size: .72rem;
        font-weight: 700;
        color: var(--wx-accent);
        text-align: center;
        padding: 0 1px;
        border: 0;
        background: transparent !important;
        line-height: 1.1;
        min-height: 0;
        height: auto;
    }
    .work-card-duration-cell .form-control:focus { box-shadow: none; }

    /* Inline error messages within a row */
    .work-row .error-message,
    .work-card .error-message {
        font-size: .7rem;
        color: var(--wx-red);
        line-height: 1.2;
        display: block;
    }

    @media (max-width: 767.98px) {
        .work-row-grid { gap: 12px 14px; padding: 12px 14px; }
    }
    @media (max-width: 575.98px) {
        .work-row-head { padding: 7px 12px; gap: 8px; }
        .work-row-grid { grid-template-columns: 1fr 1fr; gap: 10px 12px; padding: 10px 12px; }
        .work-card-field--duration { max-width: 100%; }
        .work-card-field--duration .work-card-duration-readout { max-width: 100%; }
        .work-exp-section-bar { padding: 8px 10px; gap: 8px; }
    }
    @media (max-width: 419.98px) {
        .work-row-grid { grid-template-columns: 1fr; }
    }

    /* ── Completed entry — Flipkart-style order card (collapsed) ─── */
    .work-row--compact {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0,0,0,.06);
    }
    .work-row--compact:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,.08);
        border-color: #d0d0d0;
    }
    .work-row--compact .work-row-head {
        display: block;
        padding: 0;
        cursor: default;
        user-select: none;
        border-bottom: none;
        background: transparent;
        position: relative;
    }
    .work-row--compact .work-row-head:hover { background: transparent; }
    .work-row--compact .work-row-summary {
        display: block;
        width: 100%;
        cursor: pointer;
    }
    .work-row--compact .wx-order-card {
        padding: 16px 40px 16px 18px;
        border-radius: 8px;
        background: #fff;
    }
    .work-row--compact .work-row-title,
    .work-row--compact .work-row-spacer { display: none !important; }
    .work-row--compact .work-row-head-actions {
        display: flex !important;
        position: absolute;
        top: 12px;
        right: 12px;
        gap: 4px;
        margin-left: 0;
    }
    .work-row--compact .work-row-toggle-btn { display: none !important; }
    .work-row--compact .work-row-remove {
        width: 28px;
        height: 28px;
        color: #878787;
        font-size: .85rem;
    }
    .work-row--compact .work-row-remove:hover {
        background: #f5f5f5;
        color: var(--wx-red);
    }

    /* Summary table stacks above entry cards */
    .work-exp-summary-panel {
        position: relative;
        z-index: 2;
        isolation: isolate;
    }
    .work-rows {
        position: relative;
        z-index: 1;
    }

    /* Collapsed rows: delete only in table Actions column */
    .work-row.is-complete:not(.work-row--expanded) .work-row-head,
    .work-row.work-row--in-summary .work-row-head {
        display: none !important;
    }
    .work-row.is-complete:not(.work-row--expanded) .work-row-head .work-row-remove,
    .work-row.work-row--in-summary .work-row-remove {
        display: none !important;
    }

    /* Active (incomplete) row — remove stays inside the card */
    .work-row:not(.is-complete) {
        position: relative;
        overflow: hidden;
    }
    .work-row:not(.is-complete) .work-row-head {
        position: relative;
        top: auto;
        right: auto;
        z-index: 1;
        padding: 8px 14px 0;
        background: transparent;
        border: none;
        width: auto;
        min-height: 0;
        display: flex;
        justify-content: flex-end;
    }
    .work-row:not(.is-complete) .work-row-spacer,
    .work-row:not(.is-complete) .work-row-summary { display: none !important; }
    .work-row:not(.is-complete) .work-row-grid {
        padding-top: 14px;
    }

    /* Collapsed complete row: hide the field grid */
    .work-row--compact .work-row-grid { display: none; }

    /* Expanded complete row: standard full form header */
    .work-row.is-complete.work-row--expanded {
        border: 1px solid var(--wx-border);
        background: var(--wx-bg);
        box-shadow: 0 1px 3px rgba(3,90,179,.05);
    }
    .work-row.is-complete.work-row--expanded .work-row-head {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 8px;
        cursor: pointer;
        border-bottom-color: var(--wx-border);
        background: linear-gradient(135deg, #f7faff 0%, #fbfdff 100%);
        padding: 8px 14px;
        position: static;
    }
    .work-row.is-complete.work-row--expanded .work-row-spacer { display: none !important; }
    .work-row.is-complete.work-row--expanded .work-row-summary { display: none; }
    .work-row.is-complete.work-row--expanded .work-row-head-actions {
        margin-left: 0;
        flex-shrink: 0;
    }
    .work-row.is-complete.work-row--expanded .work-row-toggle-btn { display: inline-flex; }

    /* Shown only while editing a complete entry (expanded full form) */
    .work-row-done-bar {
        display: none;
        grid-column: 1 / -1;
        margin-top: 4px;
        padding-top: 12px;
        border-top: 1px dashed var(--wx-border);
        text-align: right;
    }
    .work-row.is-complete.work-row--expanded .work-row-done-bar { display: block; }
    .work-row-done-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        font-size: .78rem;
        font-weight: 600;
        color: #fff;
        background: var(--wx-accent);
        border: none;
        border-radius: 5px;
        cursor: pointer;
        line-height: 1.2;
    }
    .work-row-done-btn:hover { background: #024a98; }
    .work-row-done-btn i { font-size: .72rem; }
    .work-row-done-hint {
        margin: 8px 0 0;
        font-size: .72rem;
        color: var(--wx-red);
        text-align: right;
    }
    @media (max-width: 767.98px) {
        .wx-summary-table { min-width: 0; }
        .wx-order-card { padding-right: 32px; }
    }
    @media (max-width: 575.98px) {
        .work-row--compact .work-row-head { padding: 14px; }
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

    @media print {
        body * { visibility:hidden !important; }
        #appPreviewModal.prv-open, #appPreviewModal.prv-open * { visibility:visible !important; }
        #appPreviewModal.prv-open { position:static !important; inset:auto; width:auto; max-width:none; overflow:visible !important; background:transparent !important; box-shadow:none !important; }
        .prv-overlay { background:none !important; }
        .prv-panel { box-shadow:none !important; border-radius:0 !important; }
        .prv-header, .prv-footer, .prv-close, .prv-badge, .prv-subtitle, .prv-confirm-check, .prv-btn-back, .prv-btn-print, .prv-btn-confirm { display:none !important; }
        .prv-section { border:1px solid #ccc !important; box-shadow:none !important; page-break-inside:avoid; }
        .prv-section-body { padding:8px 10px !important; }
        .prv-table th, .prv-table td { border-color:#ccc !important; background:transparent !important; }
        .prv-value { background:transparent !important; border-color:#ccc !important; }
        .prv-thumb img, .prv-thumb .prv-no-img { width:68px !important; height:80px !important; }
        .prv-thumb-sign img { width:110px !important; height:42px !important; }
        #appPreviewModal.prv-open { position:relative !important; }
    }

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
                                        value="{{ $user['salutation'].' '.$user['applicant_name'] }}" >
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
                            <div class="work-exp-wrap">
                                {{-- Add row control --}}
                                <div class="work-exp-section-bar" role="region" aria-label="Work experience actions">
                                    <button type="button" class="work-exp-add-btn add-more-work" id="work-exp-add-btn" title="Add a work experience entry">
                                        <i class="fa fa-plus"></i>
                                        <span>Add row</span>
                                        <span class="text-muted" style="font-weight:500;font-size:.7rem;opacity:.85;" id="work-exp-row-count">(1/3)</span>
                                    </button>
                                </div>

                                {{-- Single summary table for all completed (collapsed) entries --}}
                                <div class="work-exp-summary-panel" id="work-exp-summary-panel" aria-live="polite">
                                    <div class="wx-order-card">
                                        <div class="wx-summary-table-wrap">
                                            <table class="wx-summary-table">
                                                <thead>
                                                    <tr>
                                                        <th class="wx-summary-th-sno">S.No</th>
                                                        <th>Employment Type</th>
                                                        <th class="wx-summary-th-org"><span class="wx-th-org-line">Organisation &amp;</span><span class="wx-th-org-line">Address</span></th>
                                                        <th>Designation</th>
                                                        <th>Nature of Work</th>
                                                        <th>Voltage Level</th>
                                                        <th>Transformer kVA(max 1000kVA)</th>
                                                        <th>Total Experience</th>
                                                        <th>Attachment</th>
                                                        <th class="wx-summary-th-actions">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="work-exp-summary-tbody"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- Rows container (one row per work-experience entry — all fields visible inline) --}}
                                <div class="work-rows" id="work-container">
                                    <div class="work-fields work-row" data-row-index="0">
                                        <div class="work-row-head" role="group">
                                            <span class="work-row-spacer"></span>
                                            <div class="work-row-head-actions">
                                                <button type="button" class="work-row-toggle-btn" aria-expanded="false" title="Expand to edit" aria-label="Expand entry to edit">
                                                    <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                                </button>
                                                <button type="button" class="work-row-remove remove-work" title="Remove this entry" aria-label="Remove this work experience entry">
                                                    <i class="fa fa-trash-o" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="work-row-grid">
                                            <div class="work-card-field">
                                                <label class="work-card-field-label">Employment Type <span class="req">*</span></label>
                                                <select class="form-control work-employment-type" name="work_employment_type[]" required>
                                                    <option value="" selected disabled>Select type</option>
                                                    <option value="private_organisation">Private organisation</option>
                                                    <option value="electrical_contractor">Electrical contractor</option>
                                                    <option value="retired_employee">Retired Employee</option>
                                                    <option value="govt_organisation">Govt organisation</option>
                                                    <option value="apprenticeship">Apprenticeship</option>
                                                    <option value="board_member_tnelb">Board member of TNELB or Ex board member of TNELB</option>
                                                </select>
                                            </div>
                                            <div class="work-card-field" data-field="contractor-cat">
                                                <label class="work-card-field-label">Grade of Licence <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
                                                <select class="form-control work-contractor-cat" name="work_contractor_category[]" disabled>
                                                    <option value="">—</option>
                                                    <option value="ESA">ESA</option>
                                                    <option value="EA">EA</option>
                                                    <option value="ESB">ESB</option>
                                                    <option value="EB">EB</option>
                                                </select>
                                                <span class="work-card-field-hint" data-hint="cat" style="display:none;"><i class="fa fa-info-circle"></i> Only for Electrical contractor</span>
                                            </div>
                                            <div class="work-card-field" data-field="licence-number">
                                                <label class="work-card-field-label">Licence No <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
                                                <input type="text" class="form-control work-licence-number" name="work_licence_number[]" maxlength="40" autocomplete="off" disabled placeholder="e.g. 5645">
                                                <span class="work-card-field-hint" data-hint="licence" style="display:none;"><i class="fa fa-info-circle"></i> Only for Electrical contractor</span>
                                            </div>
                                            <div class="work-card-field">
                                                <label class="work-card-field-label">Organisation <span class="req">*</span></label>
                                                <input type="text" class="form-control work-employer-input" name="work_employer_name[]" maxlength="120" autocomplete="off" disabled placeholder="Organisation name">
                                            </div>
                                            <div class="work-card-field">
                                                <label class="work-card-field-label">Address <span class="req">*</span></label>
                                                <input type="text" class="form-control work-org-address" name="work_organisation_address[]" maxlength="255" autocomplete="off" disabled placeholder="Street, City, State, PIN">
                                            </div>
                                            <div class="work-card-field">
                                                <label class="work-card-field-label">Designation <span class="req">*</span></label>
                                                <input type="text" class="form-control work-designation" name="designation[]" maxlength="80" autocomplete="off" disabled placeholder="e.g. Site Engineer">
                                            </div>
                                            <div class="work-card-field" data-field="work-nature">
                                                <label class="work-card-field-label">Work Nature <span class="req">*</span></label>
                                                <select class="form-control work-nature" name="work_nature_of_work[]" disabled>
                                                    <option value="">—</option>
                                                    <option value="erection">Erection</option>
                                                    <option value="maintenance">Maintenance</option>
                                                    <option value="erection_maintenance">Erection &amp; Maintenance</option>
                                                </select>
                                            </div>
                                            <div class="work-card-field" data-field="voltage-level">
                                                <label class="work-card-field-label">Voltage Level <span class="req">*</span></label>
                                                <select class="form-control work-voltage" name="work_voltage_level[]" disabled>
                                                    <option value="">—</option>
                                                    <option value="up_to_650v">Up to 650V</option>
                                                    <option value="650v_to_33kv">Above 650V to 33KV</option>
                                                    <option value="above_33kv">Above 33KV</option>
                                                </select>
                                            </div>
                                            <div class="work-card-field" data-field="transformer-kva">
                                                <label class="work-card-field-label">Transformer kVA <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
                                                <input type="number" class="form-control work-transformer-kva" name="work_transformer_kva[]" min="0" max="9999999" step="any" inputmode="decimal" autocomplete="off" disabled placeholder="e.g. 250">
                                                <span class="work-card-field-hint" data-hint="kva" style="display:none;"><i class="fa fa-info-circle"></i> Not applicable for voltage up to 650V</span>
                                            </div>
                                            <div class="work-card-field">
                                                <label class="work-card-field-label">From date <span class="req">*</span></label>
                                                <input type="date" class="form-control work-date-from" name="work_date_from[]" title="From date" aria-label="Period of experience: from date" disabled>
                                            </div>
                                            <div class="work-card-field" data-field="to-date">
                                                <label class="work-card-field-label">To date <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
                                                <input type="date" class="form-control work-date-to" name="work_date_to[]" title="To date" aria-label="Period of experience: to date" disabled>
                                                <label class="work-card-till-toggle">
                                                    <input type="checkbox" class="work-date-till">
                                                    <span>Till date (currently working)</span>
                                                </label>
                                                <input type="hidden" class="work-date-till-hidden" name="work_to_till_date[]" value="0">
                                            </div>
                                            <div class="work-card-field work-card-field--duration">
                                                <label class="work-card-field-label">Duration</label>
                                                <div class="work-card-duration-readout" role="group" aria-label="Auto-calculated duration">
                                                    <div class="work-card-duration-cell">
                                                        <span class="work-duration-label">Years</span>
                                                        <input type="text" class="form-control work-duration-y" readonly inputmode="none" tabindex="-1" placeholder="0" aria-label="Years in this period">
                                                    </div>
                                                    <div class="work-card-duration-cell">
                                                        <span class="work-duration-label">Months</span>
                                                        <input type="text" class="form-control work-duration-m" readonly inputmode="none" tabindex="-1" placeholder="0" aria-label="Months in this period">
                                                    </div>
                                                    <div class="work-card-duration-cell">
                                                        <span class="work-duration-label">Days</span>
                                                        <input type="text" class="form-control work-duration-d" readonly inputmode="none" tabindex="-1" placeholder="0" aria-label="Days in this period">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="work-board-member-panel work-row-grid-span" style="display:none;">
                                                <div class="work-board-member-panel-hd">
                                                    <span class="work-board-member-panel-title">Details of board meeting attended</span>
                                                    <span class="section-req">*</span>
                                                    <span class="work-board-member-panel-hint">(Mandatory for Board member of TNELB or Ex board member of TNELB)</span>
                                                </div>
                                                <div class="work-board-member-panel-body">
                                                    <div class="work-card-field work-board-meeting-field" data-field="board-meeting-details">
                                                        <label class="work-card-field-label">Details of the meeting <span class="req">*</span></label>
                                                        <textarea class="form-control work-board-meeting-details" name="work_board_meeting_details[]" rows="2" maxlength="500" autocomplete="off" placeholder="Enter details of the board meeting attended" disabled></textarea>
                                                    </div>
                                                    <div class="work-card-field work-board-meeting-field" data-field="board-meeting-date">
                                                        <label class="work-card-field-label">Date of Meeting <span class="req">*</span></label>
                                                        <input type="date" class="form-control work-board-meeting-date" name="work_board_meeting_date[]" title="Date of Meeting" aria-label="Date of board meeting attended" disabled>
                                                    </div>
                                                </div>
                                                <p class="work-board-member-panel-note"><i class="fa fa-info-circle"></i> Supporting documents must be attached in the field below.</p>
                                            </div>
                                            <div class="work-card-field">
                                                <label class="work-card-field-label">Supporting docs <span class="req">*</span></label>
                                                <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="work">
                                                    <input class="form-control work-doc-input" name="work_document[]" type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png" disabled>
                                                </div>
                                                <span class="work-card-field-hint"><i class="fa fa-info-circle"></i> PDF / JPG / PNG, 5-200 KB</span>
                                            </div>
                                            <div class="work-card-field" data-field="relieve">
                                                <label class="work-card-field-label">Relieving Letter <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
                                                <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="work">
                                                    <input class="form-control work-relieve-input" name="work_relieving_letter[]" type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png" disabled>
                                                </div>
                                                <span class="work-card-field-hint" data-hint="relieve" style="display:none;"><i class="fa fa-info-circle"></i> Not required when "Till date" is selected</span>
                                                <span class="work-card-field-hint" data-hint="relieve-board" style="display:none;"><i class="fa fa-info-circle"></i> Optional for Board Member / Ex. Board Member of TNELB</span>
                                                <span class="work-card-field-hint" data-hint="relieve-default"><i class="fa fa-info-circle"></i> PDF / JPG / PNG, 5-200 KB</span>
                                            </div>

                                            <div class="work-row-done-bar">
                                                <button type="button" class="work-row-done-btn" aria-label="Submit this entry and return to summary card">
                                                    <i class="fa fa-check" aria-hidden="true"></i> Submit
                                                </button>
                                            </div>

                                            <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]" value="">
                                            <input type="hidden" name="work_level[]" class="work-level-sync" value="" tabindex="-1" aria-hidden="true">
                                            <input type="hidden" name="experience[]" class="experience-sync" value="" tabindex="-1" aria-hidden="true">
                                        </div>
                                    </div>
                                </div>

                                {{-- Existing combined-2-year validation message target (kept for footer.blade.php submit-time message) --}}
                                <div id="work-exp-total-msg" class="work-exp-total-msg-wrap" aria-live="polite"></div>
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
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">Date of First Issue <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-issue-date" id="previously_issue_date" name="previously_issue_date" type="date"
                                            data-error="#previouslyIssueDateError" value="">
                                        <span id="previouslyIssueDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">From date <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-valid-from" id="previously_valid_from" name="previously_valid_from" type="date"
                                            data-error="#previouslyFromDateError" value="">
                                        <span id="previouslyFromDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">To date <span class="req">*</span></div>
                                        <input autocomplete="off" class="form-control verify-date" id="previously_valid_to" name="previously_valid_to" type="date"
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
                                    <div class="col-12 col-md-3">
                                        <div class="fs-field-label">Certificate Number <span class="req">*</span> <span class="text-muted" style="font-size:.75rem;font-weight:400;">(eg. W1234)</span></div>
                                        <input class="form-control verify-input" id="certificate_no" name="competency_certificate_no" type="text"
                                            data-type="supervisor" data-error="#certError" data-msg="#license_message"
                                            placeholder="Certificate Number" maxlength="80" value="{{ $oldCertNo }}">
                                        <input type="hidden" id="cert_verify" name="cert_verify" value="0">
                                        <span id="license_message" class="mt-1"></span>
                                        <span id="certError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">Date of First Issue <span class="req">*</span></div>
                                        <input class="form-control verify-issue-date" id="certificate_issue_date" name="certificate_issue_date"
                                            data-error="#certIssueDateError" type="date" value="">
                                        <span id="certIssueDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">From date <span class="req">*</span></div>
                                        <input class="form-control verify-valid-from" id="certificate_valid_from" name="certificate_valid_from"
                                            data-error="#certFromDateError" type="date" value="">
                                        <span id="certFromDateError" class="text-danger" style="font-size:.78rem;"></span>
                                    </div>
                                    <div class="col-12 col-md-2">
                                        <div class="fs-field-label">To date <span class="req">*</span></div>
                                        <input class="form-control verify-date" id="certificate_valid_to" name="certificate_valid_to"
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
                    <input type="hidden" id="application_id" name="application_id" value="{{ $application_details->application_id ?? '' }}">
                    <input type="hidden" id="cc_digitization_temp_id" name="cc_digitization_temp_id" value="{{ $cc_digitization_temp_id ?? '' }}">
                    <input type="hidden" id="form_name" name="form_name" value="S">
                    <input type="hidden" id="license_name" name="license_name" value="C">
                    <input type="hidden" id="form_id" name="form_id" value="1">
                    <input type="hidden" id="appl_type" name="appl_type" value="D">
                    <input type="hidden" id="amount" name="amount" value="0">
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
                                    <th>Contractor Cat.</th>
                                    <th>Licence No.</th>
                                    <th>Name of Contractor / Organisation / Board</th>
                                    <th>Organisation Address</th>
                                    <th>Designation</th>
                                    <th>Nature of Work</th>
                                    <th>Voltage Level</th>
                                    <th>Transformer kVA(max 1000kVA)</th>
                                    <th>From Date</th>
                                    <th>To Date</th>
                                    <th>Period (Y/M/D)</th>
                                    <th>Supporting Doc.</th>
                                    <th>Relieving Letter</th>
                                </tr>
                            </thead>
                            <tbody id="prv_work_body">
                                <tr><td colspan="15" class="text-center text-muted py-3">—</td></tr>
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
                                    <div class="col-12 col-sm-3">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">Certificate No</div>
                                            <div class="prv-value" id="prv_prev_cert_no">—</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-3">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">Date of First Issue</div>
                                            <div class="prv-value" id="prv_prev_issue_date">—</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-3">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">From date</div>
                                            <div class="prv-value" id="prv_prev_from_date">—</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-3">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">To date</div>
                                            <div class="prv-value" id="prv_prev_to_date">—</div>
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
                                    <div class="col-12 col-sm-3">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">Certificate No</div>
                                            <div class="prv-value" id="prv_wireman_cert_no">—</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-3">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">Date of First Issue</div>
                                            <div class="prv-value" id="prv_wireman_issue_date">—</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-3">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">From date</div>
                                            <div class="prv-value" id="prv_wireman_from_date">—</div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-3">
                                        <div class="prv-field mb-1">
                                            <div class="prv-label">To date</div>
                                            <div class="prv-value" id="prv_wireman_to_date">—</div>
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
            <button type="button" class="prv-btn-print" id="prvPrintBtn">
                <i class="fa fa-print"></i> Print
            </button>
            <button type="button" class="prv-btn-confirm" id="prvConfirmBtn" disabled>
                <i class="fa fa-credit-card"></i> Confirm &amp; Proceed to Payment
            </button>
        </div>

    </div>
</div>
<footer class="main-footer">
    @include('include.footer')

    <script src="{{ url('assets/js/digitization.js') }}"></script>

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
        /*
         * Section 7 – Work Experience (Form S, card + accordion redesign).
         *
         * Conditional behaviour (column numbers per the SCC application table):
         *   • Col 2 = Employment Type.
         *       – "" (no type):          cols 3–13 all disabled.
         *       – "electrical_contractor": cols 3–13 enabled.
         *       – any other type:         cols 3 & 4 disabled, cols 5–13 enabled.
         *   • Col 9 = Voltage Level → col 10 (Transformer kVA) disabled when voltage = "up_to_650v".
         *   • Col 11 = Period of Experience. "Till date" checkbox locks To-date and
         *     also disables col 13 (Relieving Letter).
         *   • Combined-2-year minimum is checked across all rows (Till date = today for
         *     the calc) and surfaced under the table in #work-exp-total-msg.
         *
         * UI extras driven by the same state functions:
         *   • Header chips (type pill, employer, period chip, duration chip, status pill).
         *   • Combined-experience meter (progress bar + caption + status pill).
         *   • Per-field lock indicators (.work-card-field.is-locked + .lock-icon + hint).
         *   • Card chevron toggle for collapse / expand.
         */
        (function() {
            var CONTRACTOR_TYPE = 'electrical_contractor';
            var BOARD_MEMBER_TYPE = 'board_member_tnelb';
            var VOLTAGE_DISABLES_KVA = 'up_to_650v';
            var MAX_WORK_ROWS = 3;
            var TWO_YEARS_MS = 730 * 86400000;
            var EMP_LABEL = {
                '': 'Select employment type',
                private_organisation: 'Private organisation',
                electrical_contractor: 'Electrical contractor',
                retired_employee: 'Retired Employee',
                govt_organisation: 'Govt organisation',
                apprenticeship: 'Apprenticeship',
                board_member_tnelb: 'Board member of TNELB or Ex board member of TNELB'
            };
            var NATURE_LABEL = {
                erection: 'Erection',
                maintenance: 'Maintenance',
                erection_maintenance: 'Erection & Maintenance'
            };
            var VOLTAGE_LABEL = {
                up_to_650v: 'Up to 650V',
                '650v_to_33kv': 'Above 650V to 33KV',
                above_33kv: 'Above 33KV'
            };
            var MONTH_SHORT = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

            function summaryFilePreviewUrl($input) {
                if (!$input || !$input.length) return '';
                var $preview = $input.closest('.form-s-file-upload-wrap').next('.local-file-preview');
                return ($preview.length && $preview.data('blobUrl')) ? $preview.data('blobUrl') : '';
            }

            function summaryExistingDocHref($row, kind) {
                if (!$row || !$row.length) return '';
                var sel = (kind === 'relieve')
                    ? 'input[name="existing_work_relieving_document[]"]'
                    : 'input[name="existing_work_document[]"]';
                var path = ($row.find(sel).first().val() || '').trim();
                if (!path) return '';
                if (/^https?:\/\//i.test(path)) return path;
                var base = (typeof BASE_URL !== 'undefined' ? BASE_URL : '').replace(/\/$/, '');
                if (path.charAt(0) === '/') return base + path;
                return base + '/' + path.replace(/^\/+/, '');
            }

            function summaryAttachmentBlock(label, $input, naText, $row) {
                var $block = $('<div class="wx-sum-attach-block">');
                $block.append($('<span class="wx-sum-attach-label">').text(label + ' :'));
                if (naText) {
                    $block.append($('<span class="wx-sum-attach-value">').text(naText));
                    return $block;
                }
                var blobUrl = summaryFilePreviewUrl($input);
                var file = ($input[0] && $input[0].files && $input[0].files[0]) ? $input[0].files[0] : null;
                var isImage = file && file.type && file.type.indexOf('image/') === 0;
                var existingHref = summaryExistingDocHref($row, label === 'Relieving' ? 'relieve' : 'support');
                if (blobUrl) {
                    var icon = isImage ? 'fa-image' : 'fa-file-pdf-o';
                    $block.append(
                        $('<a>', {
                            href: blobUrl,
                            target: '_blank',
                            rel: 'noopener noreferrer',
                            class: 'preview-link wx-sum-doc-link'
                        }).html('<i class="fa ' + icon + '"></i> View Document')
                    );
                } else if (existingHref) {
                    $block.append(
                        $('<a>', {
                            href: existingHref,
                            target: '_blank',
                            rel: 'noopener noreferrer',
                            class: 'preview-link wx-sum-doc-link'
                        }).html('<i class="fa fa-file-pdf-o"></i> View Document')
                    );
                } else if ($input.attr('data-has-local-file')) {
                    $block.append($('<span class="wx-sum-attach-value">').text('File attached'));
                } else {
                    $block.append($('<span class="wx-sum-attach-value">').text('—'));
                }
                return $block;
            }

            function $workRow(el) { return $(el).closest('.work-fields'); }

            /* Legacy backend (work_level[], experience[]) still expects something; mirror it from the
               new fields so server-side `required` rules pass. */
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

            /** Calendar Y/M/D between two local dates (mirrors the server `workExperienceCalendarYmd`). */
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

            function todayIso() {
                var n = new Date();
                return n.getFullYear() + '-' + String(n.getMonth() + 1).padStart(2, '0') + '-' + String(n.getDate()).padStart(2, '0');
            }

            function fmtPretty(iso) {
                if (!iso) return '';
                var p = iso.split('-');
                if (p.length !== 3) return iso;
                var y = parseInt(p[0], 10), m = parseInt(p[1], 10), d = parseInt(p[2], 10);
                if (isNaN(y) || isNaN(m) || isNaN(d) || m < 1 || m > 12) return iso;
                return d + ' ' + MONTH_SHORT[m - 1] + ' ' + y;
            }

            /** Parse work date input to ISO yyyy-mm-dd (native date + DD-MM-YYYY display). */
            function parseWorkDateToIso(str) {
                var s = String(str || '').trim();
                if (!s) return '';
                if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
                var m = s.match(/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/);
                return m ? (m[3] + '-' + String(m[2]).padStart(2, '0') + '-' + String(m[1]).padStart(2, '0')) : '';
            }

            function readWorkDateFromInput($input) {
                if (typeof window.readWorkDateIsoGeneric === 'function') {
                    return window.readWorkDateIsoGeneric($input);
                }
                var $el = ($input && $input.length) ? $input.first() : $();
                var node = $el.get(0);
                if (!node) return '';
                var candidates = [];
                if (node.type === 'date' && node.value) {
                    candidates.push(String(node.value).trim());
                }
                candidates.push(String($el.val() || node.value || '').trim());
                candidates.push(String(node.getAttribute('data-raw') || '').trim());
                for (var i = 0; i < candidates.length; i++) {
                    var iso = parseWorkDateToIso(candidates[i]);
                    if (iso) return iso;
                }
                return '';
            }

            function syncWorkDateRaw($input) {
                var iso = readWorkDateFromInput($input);
                if (iso && $input && $input.length) {
                    $input.get(0).setAttribute('data-raw', iso);
                }
            }

            function clearWorkDateFieldErrors($input) {
                if (typeof window.clearWorkDateRequiredErrors === 'function') {
                    window.clearWorkDateRequiredErrors($input);
                    return;
                }
                if (!$input || !$input.length) return;
                $input.nextAll('.error-message').each(function() {
                    var txt = ($(this).text() || '').toLowerCase();
                    if (txt.indexOf('to date is required') !== -1 || txt.indexOf('from date is required') !== -1) {
                        $(this).remove();
                    }
                });
            }

            /** Effective To date for a row: explicit value, or today if "Till date" is checked. */
            function effectiveToStr($tr) {
                if ($tr.find('.work-date-till').is(':checked')) return todayIso();
                return readWorkDateFromInput($tr.find('.work-date-to'));
            }

            function totalDurationAcrossRows() {
                var totalMs = 0;
                var anyFilled = false;
                $('#work-container .work-fields').each(function() {
                    var $tr = $(this);
                    var fromStr = readWorkDateFromInput($tr.find('.work-date-from'));
                    var toStr = effectiveToStr($tr);
                    if (!fromStr || !toStr) return;
                    var from = new Date(fromStr + 'T12:00:00');
                    var to   = new Date(toStr + 'T12:00:00');
                    if (isNaN(from.getTime()) || isNaN(to.getTime())) return;
                    if (to < from) return;
                    anyFilled = true;
                    totalMs += (to - from);
                });
                return { ms: totalMs, hasAny: anyFilled };
            }

            /** Section meter + legacy combined-message kept in sync. */
            function updateOverallTotalYears() {
                var t = totalDurationAcrossRows();
                /* Legacy banner under the cards (kept for backward compatibility with footer.blade.php). */
                var $msg = $('#work-exp-total-msg');
                if ($msg.length) {
                    if (!t.hasAny || t.ms >= TWO_YEARS_MS) { $msg.empty(); }
                    else {
                        $msg.html(
                            '<div class="work-exp-total-error text-danger small" role="alert">' +
                                'Minimum 2 Years Experience needed across all entries.' +
                            '</div>'
                        );
                    }
                }
                updateWorkAddBtn();
            }

            /** Row-count badge + disable state on the Add button. */
            function updateWorkAddBtn() {
                var rows = $('#work-container .work-fields').length;
                $('#work-exp-row-count').text('(' + rows + '/' + MAX_WORK_ROWS + ')');
                $('#work-exp-add-btn').prop('disabled', rows >= MAX_WORK_ROWS);
            }

            /** Toggle the .is-locked class + lock-icon visibility + conditional hint on a field wrapper. */
            function setFieldLock($tr, fieldName, locked) {
                var $f = $tr.find('.work-card-field[data-field="' + fieldName + '"]');
                if (!$f.length) return;
                $f.toggleClass('is-locked', !!locked);
                $f.find('.lock-icon').toggle(!!locked);
                if (locked) {
                    $f.find('.work-card-field-hint[data-hint="' + fieldName + '"], ' +
                            '.work-card-field-hint[data-hint="' + mapHintName(fieldName) + '"]').show();
                    if (fieldName === 'relieve') $f.find('.work-card-field-hint[data-hint="relieve-default"]').hide();
                } else {
                    $f.find('.work-card-field-hint[data-hint="' + fieldName + '"], ' +
                            '.work-card-field-hint[data-hint="' + mapHintName(fieldName) + '"]').hide();
                    if (fieldName === 'relieve') $f.find('.work-card-field-hint[data-hint="relieve-default"]').show();
                }
            }
            /* data-field uses kebab; some hints use shorter names. */
            function mapHintName(fieldName) {
                switch (fieldName) {
                    case 'contractor-cat': return 'cat';
                    case 'licence-number': return 'licence';
                    case 'transformer-kva': return 'kva';
                    default: return fieldName;
                }
            }

            /** Row header: status pill, compact summary, expand/collapse. */
            function updateRowHeader($tr) {
                updateRowStatus($tr);
            }

            /** Complete rows collapse to summary strip unless manually expanded for editing. */
            function applyRowLayout($tr) {
                var complete = $tr.hasClass('is-complete');
                var expanded = $tr.hasClass('work-row--expanded');
                $tr.toggleClass('work-row--compact', complete && !expanded);
                $tr.find('.work-row-toggle-btn')
                    .attr('aria-expanded', expanded ? 'true' : 'false')
                    .attr('title', expanded ? 'Submit and return to summary card' : 'Expand to edit')
                    .attr('aria-label', expanded ? 'Submit entry and return to summary card' : 'Expand entry to edit');
            }

            /** Refresh summary and collapse expanded complete row back to order-card view. */
            function collapseToSummary($tr) {
                updateRowStatus($tr);
                $tr.find('.work-row-done-hint').remove();
                if (!$tr.hasClass('is-complete')) {
                    var $bar = $tr.find('.work-row-done-bar');
                    if ($bar.length && !$bar.find('.work-row-done-hint').length) {
                        $bar.append('<p class="work-row-done-hint" role="alert">Fill all required fields and upload documents before you can submit.</p>');
                    }
                    return false;
                }
                $tr.removeClass('work-row--expanded');
                applyRowLayout($tr);
                updateRowSummary($tr);
                syncSummaryTable();
                return true;
            }

            /** Resolve the form row linked to a shared summary table row. */
            function workRowFromSummaryTr($summaryTr) {
                var linked = null;
                $('#work-container .work-fields').each(function() {
                    var $str = $(this).data('wxSummaryTr');
                    if ($str && $str.length && $str[0] === $summaryTr[0]) {
                        linked = $(this);
                        return false;
                    }
                });
                return linked;
            }

            /** Create (once) the shared-table row for a work-fields block. */
            function getSummaryTr($tr) {
                var $str = $tr.data('wxSummaryTr');
                if ($str && $str.length) return $str;
                $str = $('<tr class="work-exp-summary-tr">').append(
                    $('<td class="work-row-summary-sno">'),
                    $('<td class="work-row-summary-employment">'),
                    $('<td class="work-row-summary-org-address">'),
                    $('<td class="work-row-summary-designation">'),
                    $('<td class="work-row-summary-nature">'),
                    $('<td class="work-row-summary-voltage">'),
                    $('<td class="work-row-summary-kva">'),
                    $('<td class="work-row-summary-period">'),
                    $('<td class="work-row-summary-attachments">'),
                    $('<td class="work-row-summary-actions">').append(
                        $('<button type="button" class="wx-order-edit-link work-row-edit-trigger" aria-label="Edit this work experience entry">')
                            .html('<i class="fa fa-pencil" aria-hidden="true"></i> Edit'),
                        $('<button type="button" class="work-row-remove remove-work" title="Remove this entry" aria-label="Remove this work experience entry">')
                            .html('<i class="fa fa-trash-o" aria-hidden="true"></i>')
                    )
                );
                $tr.data('wxSummaryTr', $str);
                $('#work-exp-summary-tbody').append($str);
                return $str;
            }

            /** Show complete collapsed rows in the shared table; hide their form cards. */
            function syncSummaryTable() {
                var $panel = $('#work-exp-summary-panel');
                var $tbody = $('#work-exp-summary-tbody');
                var hasVisible = false;
                var linkedRows = [];
                $('#work-container .work-fields').each(function() {
                    var $wf = $(this);
                    var inTable = $wf.hasClass('is-complete') && $wf.hasClass('work-row--compact');
                    if ($wf.hasClass('is-complete')) getSummaryTr($wf);
                    var $str = $wf.data('wxSummaryTr');
                    if (inTable && $str && $str.length) {
                        $tbody.append($str);
                        $str.show();
                        $wf.addClass('work-row--in-summary');
                        linkedRows.push($str[0]);
                        hasVisible = true;
                    } else {
                        if ($str && $str.length) $str.hide();
                        $wf.removeClass('work-row--in-summary');
                    }
                });
                $tbody.find('.work-exp-summary-tr').each(function() {
                    if (linkedRows.indexOf(this) === -1) {
                        $(this).hide();
                    }
                });
                $panel.toggleClass('is-visible', hasVisible);
                refreshWorkSerials();
            }
            window.wxSyncWorkSummaryTable = syncSummaryTable;

            /** Summary table row — filled details shown when collapsed. */
            function updateRowSummary($tr) {
                if (!$tr.hasClass('is-complete')) return;
                var $str = getSummaryTr($tr);
                var type = ($tr.find('.work-employment-type').val() || '').trim();
                var employer = ($tr.find('.work-employer-input').val() || '').trim();
                var address = ($tr.find('.work-org-address').val() || '').trim();
                var designation = ($tr.find('.work-designation').val() || '').trim();
                var cat = ($tr.find('.work-contractor-cat').val() || '').trim();
                var licence = ($tr.find('.work-licence-number').val() || '').trim();
                var nature = ($tr.find('.work-nature').val() || '').trim();
                var voltage = ($tr.find('.work-voltage').val() || '').trim();
                var kva = ($tr.find('.work-transformer-kva').val() || '').trim();
                var fromIso = readWorkDateFromInput($tr.find('.work-date-from'));
                var toIso = readWorkDateFromInput($tr.find('.work-date-to'));
                var isTill = $tr.find('.work-date-till').is(':checked');
                var y = ($tr.find('.work-duration-y').val() || '').trim();
                var m = ($tr.find('.work-duration-m').val() || '').trim();
                var d = ($tr.find('.work-duration-d').val() || '').trim();
                var isContractor = (type === CONTRACTOR_TYPE);

                /* Col 1 — Employment Type (+ contractor cat / licence) */
                var $empCell = $str.find('.work-row-summary-employment');
                $empCell.empty();
                $empCell.append($('<span class="wx-sum-main">').text(type ? (EMP_LABEL[type] || type) : '—'));
                if (isContractor && cat) {
                    $empCell.append($('<span class="wx-sum-sub">').text('Cat: ' + cat));
                }
                if (isContractor && licence) {
                    $empCell.append($('<span class="wx-sum-sub">').text('Licence: ' + licence));
                }

                /* Col 2 — Organisation & Address */
                var $orgCell = $str.find('.work-row-summary-org-address');
                $orgCell.empty();
                $orgCell.append($('<span class="wx-sum-main">').text(employer || '—'));
                if (address) {
                    $orgCell.append($('<span class="wx-sum-sub">').text(address));
                }

                /* Col 3–6 */
                $str.find('.work-row-summary-designation').text(designation || '—');
                if (type === BOARD_MEMBER_TYPE) {
                    $str.find('.work-row-summary-nature').text('—');
                    $str.find('.work-row-summary-voltage').text('—');
                    $str.find('.work-row-summary-kva').text('Not applicable');
                } else {
                    $str.find('.work-row-summary-nature').text(nature ? (NATURE_LABEL[nature] || nature) : '—');
                    $str.find('.work-row-summary-voltage').text(voltage ? (VOLTAGE_LABEL[voltage] || voltage) : '—');
                    if (voltage === VOLTAGE_DISABLES_KVA) {
                        $str.find('.work-row-summary-kva').text('Not applicable');
                    } else {
                        $str.find('.work-row-summary-kva').text(kva ? (kva + ' kVA') : '—');
                    }
                }

                /* Col 7 — From / To / Duration (mini boxes) */
                var $periodCell = $str.find('.work-row-summary-period');
                $periodCell.empty();
                var toEffIso = isTill ? todayIso() : toIso;
                var toText = isTill ? 'Till date' : (toIso ? fmtPretty(toIso) : '—');
                var yN = parseInt(y, 10) || 0, mN = parseInt(m, 10) || 0, dN = parseInt(d, 10) || 0;
                if (!y && !m && !d && fromIso && toEffIso) {
                    var fromDt = new Date(fromIso + 'T12:00:00');
                    var toDt = new Date(toEffIso + 'T12:00:00');
                    var diff = calendarDiffYMD(fromDt, toDt);
                    if (diff) { yN = diff.y; mN = diff.m; dN = diff.d; }
                }
                var $box = $('<div class="wx-period-box">');
                var $dates = $('<div class="wx-period-dates">');
                $dates.append(
                    $('<div class="wx-period-mini">')
                        .append($('<span class="wx-period-label">').text('From'))
                        .append($('<span class="wx-period-val">').text(fromIso ? fmtPretty(fromIso) : '—'))
                );
                $dates.append(
                    $('<div class="wx-period-mini">')
                        .append($('<span class="wx-period-label">').text('To'))
                        .append($('<span class="wx-period-val">').text(toText))
                );
                $box.append($dates);
                if (fromIso && toEffIso) {
                    var $durRow = $('<div class="wx-period-duration">');
                    [
                        { n: yN, l: 'Years' },
                        { n: mN, l: 'Months' },
                        { n: dN, l: 'Days' }
                    ].forEach(function(item) {
                        $durRow.append(
                            $('<div class="wx-period-dur-cell">')
                                .append($('<span class="wx-period-dur-num">').text(String(item.n)))
                                .append($('<span class="wx-period-dur-lbl">').text(item.l))
                        );
                    });
                    $box.append($durRow);
                }
                $periodCell.append($box);

                /* Col 8 — Attachments */
                var $docInput = $tr.find('.work-doc-input');
                var $relInput = $tr.find('.work-relieve-input');
                var $attachCell = $str.find('.work-row-summary-attachments');
                $attachCell.empty();
                var $attachStack = $('<div class="wx-sum-attach-stack">');
                $attachStack.append(summaryAttachmentBlock('Supporting', $docInput, null, $tr));
                $attachStack.append(summaryAttachmentBlock('Relieving', $relInput, isTill ? 'Not required (Till date)' : null, $tr));
                $attachCell.append($attachStack);
            }

            function toggleRowExpanded($tr, expand) {
                if (!$tr.hasClass('is-complete')) return;
                var wasExpanded = $tr.hasClass('work-row--expanded');
                var shouldExpand = (typeof expand === 'boolean') ? expand : !wasExpanded;
                if (wasExpanded && !shouldExpand) {
                    collapseToSummary($tr);
                    return;
                }
                $tr.toggleClass('work-row--expanded', shouldExpand);
                if (shouldExpand) {
                    $tr.removeClass('work-row--compact');
                }
                applyRowLayout($tr);
                syncSummaryTable();
                $tr.find('.work-row-done-hint').remove();
                if (shouldExpand) {
                    var $focus = $tr.find('.work-row-grid :input:not([type="hidden"]):not([readonly]):enabled').first();
                    if ($focus.length) $focus.trigger('focus');
                }
            }

            /** Complete rows switch to compact order-card layout (no status badge in UI). */
            function updateRowStatus($tr) {
                var wasComplete = !!$tr.data('wxWasComplete');
                var complete = isRowComplete($tr);
                $tr.toggleClass('is-complete', complete);
                if (complete) {
                    if (!wasComplete) $tr.removeClass('work-row--expanded');
                } else {
                    $tr.removeClass('work-row--expanded');
                }
                $tr.data('wxWasComplete', complete);
                applyRowLayout($tr);
                updateRowSummary($tr);
                syncSummaryTable();
            }

            function workInputHasFile($input) {
                if (!$input || !$input.length) return false;
                var el = $input[0];
                if (el && el.files && el.files.length) return true;
                if ($input.attr('data-has-local-file') === '1') return true;
                var $wrap = $input.closest('.form-s-file-upload-wrap');
                if ($wrap.length && $wrap.next('.local-file-preview').find('.preview-link').length) return true;
                return String($input.val() || '').trim() !== '';
            }

            function isRowComplete($tr) {
                var type = ($tr.find('.work-employment-type').val() || '').trim();
                if (!type) return false;
                var isBoardMember = (type === BOARD_MEMBER_TYPE);
                /* Every enabled required text/select must be filled. */
                var ok = true;
                $tr.find('select[required], input[type="text"][required], input[type="number"][required]').each(function() {
                    if ($(this).prop('disabled')) return;
                    if (($(this).val() || '').trim() === '') { ok = false; return false; }
                });
                if (!readWorkDateFromInput($tr.find('.work-date-from'))) ok = false;
                if (!$tr.find('.work-date-till').is(':checked') && !$tr.find('.work-date-to').prop('disabled')
                    && !readWorkDateFromInput($tr.find('.work-date-to'))) ok = false;
                if (!ok) return false;
                if (!isBoardMember) {
                    var voltage = ($tr.find('.work-voltage').val() || '').trim();
                    var $kva = $tr.find('.work-transformer-kva');
                    if (!$kva.prop('disabled') && voltage !== VOLTAGE_DISABLES_KVA && ($kva.val() || '').trim() === '') return false;
                } else {
                    if (!($tr.find('.work-board-meeting-details').val() || '').trim()) return false;
                    if (!readWorkDateFromInput($tr.find('.work-board-meeting-date'))) return false;
                }
                var $doc = $tr.find('.work-doc-input');
                if (!$doc.prop('disabled') && !workInputHasFile($doc)) return false;
                var till = $tr.find('.work-date-till').is(':checked');
                if (!till && !isBoardMember) {
                    var $rel = $tr.find('.work-relieve-input');
                    if (!$rel.prop('disabled') && !workInputHasFile($rel)) return false;
                }
                return true;
            }

            function updateTotalYears($tr) {
                var fromStr = readWorkDateFromInput($tr.find('.work-date-from'));
                var toStr   = effectiveToStr($tr);
                var done = function() { syncLegacyHidden($tr); updateOverallTotalYears(); updateRowHeader($tr); };
                if (!fromStr || !toStr) { clearWorkDuration($tr); done(); return; }
                var from = new Date(fromStr + 'T12:00:00'), to = new Date(toStr + 'T12:00:00');
                if (isNaN(from.getTime()) || isNaN(to.getTime())) { clearWorkDuration($tr); done(); return; }
                if (to < from) { clearWorkDuration($tr); done(); return; }
                var diff = calendarDiffYMD(from, to);
                if (!diff) { clearWorkDuration($tr); done(); return; }
                $tr.find('.work-duration-y').val(String(diff.y));
                $tr.find('.work-duration-m').val(String(diff.m));
                $tr.find('.work-duration-d').val(String(diff.d));
                var yearsDec = (to - from) / 86400000 / 365.25;
                var rounded = Math.round(yearsDec * 10) / 10;
                $tr.find('.work-experience-total-hidden').val(rounded.toFixed(1));
                done();
            }

            /** Lock or unlock the row's relieving-letter upload based on the till-date checkbox. */
            function applyTillDate($tr) {
                var $till = $tr.find('.work-date-till');
                var checked = $till.is(':checked');
                $tr.find('.work-date-till-hidden').val(checked ? '1' : '0');

                var $toDate = $tr.find('.work-date-to');
                if (checked) {
                    $toDate.val('').prop('disabled', true).prop('required', false);
                } else {
                    $toDate.prop('disabled', false);
                    // Required-state for $toDate is re-evaluated by applyEmploymentType.
                }
                setFieldLock($tr, 'to-date', checked);

                var $relieve = $tr.find('.work-relieve-input');
                if (checked) {
                    $relieve.val('').prop('disabled', true).prop('required', false).addClass('is-locked');
                    var $wrap = $relieve.closest('.form-s-file-upload-wrap');
                    var $preview = $wrap.next('.local-file-preview');
                    if ($preview.length) {
                        var blobUrl = $preview.data('blobUrl');
                        if (blobUrl) { try { URL.revokeObjectURL(blobUrl); } catch(e) {} }
                        $preview.remove();
                    }
                    $relieve.removeAttr('data-has-local-file');
                } else {
                    $relieve.prop('disabled', false).removeClass('is-locked');
                    // Required-state for $relieve is re-evaluated by applyEmploymentType.
                }
                setFieldLock($tr, 'relieve', checked);
                updateTotalYears($tr);
                updateRowHeader($tr);
            }

            /** Lock or unlock the row's Transformer-kVA input based on the voltage dropdown. */
            function applyVoltage($tr) {
                var v = ($tr.find('.work-voltage').val() || '').trim();
                var $kva = $tr.find('.work-transformer-kva');
                var locked = (v === VOLTAGE_DISABLES_KVA);
                if (locked) {
                    $kva.val('').prop('disabled', true).prop('required', false);
                    $kva.nextAll('.error-message').remove();
                    $tr.find('.work-card-field[data-field="transformer-kva"] .error-message').remove();
                } else {
                    $kva.prop('disabled', false);
                    // Required-state is re-evaluated by applyEmploymentType (only required when a type is chosen).
                }
                setFieldLock($tr, 'transformer-kva', locked);
            }

            /** Show or hide board-meeting sub-question panel for Board Member employment type. */
            function toggleBoardMeetingFields($tr, show) {
                var $panel = $tr.find('.work-board-member-panel');
                $panel.toggle(!!show);
                $tr.toggleClass('work-row--board-member', !!show);
                var $details = $tr.find('.work-board-meeting-details');
                var $date = $tr.find('.work-board-meeting-date');
                if (show) {
                    $details.prop('disabled', false).prop('required', true);
                    $date.prop('disabled', false).prop('required', true);
                } else {
                    $details.val('').prop('disabled', true).prop('required', false);
                    $date.val('').prop('disabled', true).prop('required', false);
                }
            }

            /** Board Member: disable contractor / technical columns; enable org, dates, uploads. */
            function applyBoardMemberEmployment($tr) {
                var $cat = $tr.find('.work-contractor-cat');
                var $lic = $tr.find('.work-licence-number');
                var $emp = $tr.find('.work-employer-input');
                var $addr = $tr.find('.work-org-address');
                var $des = $tr.find('.work-designation');
                var $nat = $tr.find('.work-nature');
                var $volt = $tr.find('.work-voltage');
                var $kva = $tr.find('.work-transformer-kva');
                var $yFrom = $tr.find('.work-date-from');
                var $yTo = $tr.find('.work-date-to');
                var $till = $tr.find('.work-date-till');
                var $doc = $tr.find('.work-doc-input');
                var $rel = $tr.find('.work-relieve-input');

                $cat.val('').prop('disabled', true).prop('required', false);
                $lic.val('').prop('disabled', true).prop('required', false);
                setFieldLock($tr, 'contractor-cat', true);
                setFieldLock($tr, 'licence-number', true);

                $nat.val('').prop('disabled', true).prop('required', false);
                $volt.val('').prop('disabled', true).prop('required', false);
                $kva.val('').prop('disabled', true).prop('required', false);
                setFieldLock($tr, 'work-nature', true);
                setFieldLock($tr, 'voltage-level', true);
                setFieldLock($tr, 'transformer-kva', true);
                $tr.find('[data-field="work-nature"] .req, [data-field="voltage-level"] .req, [data-field="transformer-kva"] .req').hide();

                $emp.prop('disabled', false).prop('required', true);
                $addr.prop('disabled', false).prop('required', true);
                $des.prop('disabled', false).prop('required', true);
                $yFrom.prop('disabled', false).prop('required', true);
                $till.prop('disabled', false);
                $doc.prop('disabled', false).removeClass('is-locked');

                applyTillDate($tr);
                if (!$yTo.prop('disabled')) {
                    $yTo.prop('required', true);
                }

                $rel.prop('required', false);
                if (!$till.is(':checked')) {
                    $rel.prop('disabled', false).removeClass('is-locked');
                }
                $tr.find('[data-field="relieve"] .work-card-field-label .req').hide();
                $tr.find('[data-field="relieve"] .work-card-field-hint[data-hint="relieve-board"]').show();
                $tr.find('[data-field="relieve"] .work-card-field-hint[data-hint="relieve-default"]').hide();

                toggleBoardMeetingFields($tr, true);
                updateTotalYears($tr);
                syncLegacyHidden($tr);
                updateRowHeader($tr);
            }

            /** Drive every column's enable / required state from the Employment Type. */
            function applyEmploymentType($tr) {
                var t = ($tr.find('.work-employment-type').val() || '').trim();
                var hasType = t !== '';
                var isContractor = (t === CONTRACTOR_TYPE);

                var $cat = $tr.find('.work-contractor-cat');
                var $lic = $tr.find('.work-licence-number');
                var $emp = $tr.find('.work-employer-input');
                var $addr = $tr.find('.work-org-address');
                var $des = $tr.find('.work-designation');
                var $nat = $tr.find('.work-nature');
                var $volt = $tr.find('.work-voltage');
                var $kva = $tr.find('.work-transformer-kva');
                var $yFrom = $tr.find('.work-date-from');
                var $yTo = $tr.find('.work-date-to');
                var $till = $tr.find('.work-date-till');
                var $doc = $tr.find('.work-doc-input');
                var $rel = $tr.find('.work-relieve-input');
                var $qsRadios = $tr.find('.work-qualified-supervisor-radio');

                if (!hasType) {
                    /* No type selected → blank every column 3–13. */
                    $cat.val('').prop('disabled', true).prop('required', false);
                    $lic.val('').prop('disabled', true).prop('required', false);
                    $emp.val('').prop('disabled', true).prop('required', false);
                    $addr.val('').prop('disabled', true).prop('required', false);
                    $des.val('').prop('disabled', true).prop('required', false);
                    $nat.val('').prop('disabled', true).prop('required', false);
                    $volt.val('').prop('disabled', true).prop('required', false);
                    $kva.val('').prop('disabled', true).prop('required', false);
                    $yFrom.val('').prop('disabled', true).prop('required', false);
                    $yTo.val('').prop('disabled', true).prop('required', false);
                    $till.prop('checked', false).prop('disabled', true);
                    $tr.find('.work-date-till-hidden').val('0');
                    $doc.val('').prop('disabled', true).prop('required', false).removeAttr('data-has-local-file').addClass('is-locked');
                    $rel.val('').prop('disabled', true).prop('required', false).removeAttr('data-has-local-file').addClass('is-locked');
                    setFieldLock($tr, 'contractor-cat', false);
                    setFieldLock($tr, 'licence-number', false);
                    setFieldLock($tr, 'transformer-kva', false);
                    setFieldLock($tr, 'to-date', false);
                    setFieldLock($tr, 'relieve', false);
                    toggleBoardMeetingFields($tr, false);
                    $tr.find('[data-field="relieve"] .work-card-field-hint[data-hint="relieve-board"]').hide();
                    $tr.find('[data-field="relieve"] .work-card-field-hint[data-hint="relieve-default"]').show();
                    /* Clear any blob previews left over from a previous selection. */
                    $tr.find('.local-file-preview').each(function() {
                        var $p = $(this);
                        var url = $p.data('blobUrl');
                        if (url) { try { URL.revokeObjectURL(url); } catch(e) {} }
                        $p.remove();
                    });
                    clearWorkDuration($tr);
                    syncLegacyHidden($tr);
                    updateOverallTotalYears();
                    updateRowHeader($tr);
                    return;
                }

                if (t === BOARD_MEMBER_TYPE) {
                    applyBoardMemberEmployment($tr);
                    return;
                }

                $tr.find('.work-nature, .work-voltage').closest('.work-card-field').find('.req').show();
                $tr.find('[data-field="transformer-kva"] .req').show();
                $tr.find('[data-field="relieve"] .work-card-field-label .req').show();
                $tr.find('[data-field="relieve"] .work-card-field-hint[data-hint="relieve-board"]').hide();
                toggleBoardMeetingFields($tr, false);

                /* Cols 3 & 4 — Contractor only. */
                if (isContractor) {
                    $cat.prop('disabled', false).prop('required', true);
                    $lic.prop('disabled', false).prop('required', true);
                    setFieldLock($tr, 'contractor-cat', false);
                    setFieldLock($tr, 'licence-number', false);
                } else {
                    $cat.val('').prop('disabled', true).prop('required', false);
                    $lic.val('').prop('disabled', true).prop('required', false);
                    setFieldLock($tr, 'contractor-cat', true);
                    setFieldLock($tr, 'licence-number', true);
                }

                /* Cols 5–9, 11–12 — required for every chosen type. */
                $emp.prop('disabled', false).prop('required', true);
                $addr.prop('disabled', false).prop('required', true);
                $des.prop('disabled', false).prop('required', true);
                $nat.prop('disabled', false).prop('required', true);
                $volt.prop('disabled', false).prop('required', true);
                $yFrom.prop('disabled', false).prop('required', true);
                $till.prop('disabled', false);
                $doc.prop('disabled', false).prop('required', false).removeClass('is-locked');

                /* Col 10 — kVA is conditional on voltage; let applyVoltage finalise it. */
                $volt.prop('disabled', false);
                applyVoltage($tr);
                /* When voltage allows kVA, make it required. */
                if (!$kva.prop('disabled')) $kva.prop('required', true);

                /* Col 11.To & Col 13 — conditional on Till date. */
                applyTillDate($tr);
                if (!$yTo.prop('disabled')) $yTo.prop('required', true);
                if (!$rel.prop('disabled')) $rel.prop('required', false);
                /* Note: supporting doc (col 12) AND relieving letter (col 13) are validated
                   at submit-time (file OR existing path); we don't mark them HTML5-required
                   because that breaks the cross-row "till date" logic. */

                updateTotalYears($tr);
                syncLegacyHidden($tr);
                updateRowHeader($tr);
            }

            function initWorkRow($tr) { applyEmploymentType($tr); }

            function refreshWorkSerials() {
                $('#work-container .work-fields').each(function(idx) {
                    var n = idx + 1;
                    var $row = $(this);
                    $row.attr('data-row-index', idx);
                    $row.find('.work-row-entry-num').text(n);
                    var $str = $row.data('wxSummaryTr');
                    if ($str && $str.length) $str.find('.work-row-summary-sno').text(n);
                });
                updateWorkAddBtn();
            }

            function showSectionError(msg) {
                $('.work-error').remove();
                var $bar = $('#work-container').prev('.work-exp-section-bar');
                var $err = $('<div class="text-danger small mt-1 work-error" role="alert">' + msg + '</div>');
                if ($bar.length) $bar.after($err); else $('#work-container').before($err);
                setTimeout(function() { $('.work-error').fadeOut(400, function() { $(this).remove(); }); }, 5000);
            }

            window.wxRecalcWorkDuration = function($row) {
                if ($row && $row.length) {
                    updateTotalYears($row);
                }
            };

            $(document).ready(function() {
                $('#work-container .work-fields').each(function() {
                    var $row = $(this);
                    initWorkRow($row);
                    updateTotalYears($row);
                });
                refreshWorkSerials();
                syncSummaryTable();
                updateOverallTotalYears();
            });

            /* Type / voltage / till-date drive all the conditional locks. */
            $(document).on('change', '#work-container .work-employment-type', function() { applyEmploymentType($workRow(this)); });
            $(document).on('change', '#work-container .work-voltage', function() {
                var $tr = $workRow(this);
                applyVoltage($tr);
                if (!$tr.find('.work-transformer-kva').prop('disabled')) {
                    $tr.find('.work-transformer-kva').prop('required', true);
                } else {
                    $tr.find('.work-transformer-kva').prop('required', false);
                }
                $tr.find('.work-transformer-kva').nextAll('.error-message').remove();
                $tr.find('.work-card-field[data-field="transformer-kva"] .error-message').remove();
                updateRowHeader($tr);
            });
            $(document).on('change', '#work-container .work-date-till', function() {
                var $tr = $workRow(this);
                applyTillDate($tr);
            });
            $(document).on('change input blur', '#work-container .work-date-from, #work-container .work-date-to', function() {
                var $field = $(this);
                syncWorkDateRaw($field);
                clearWorkDateFieldErrors($field);
                updateTotalYears($workRow(this));
            });
            /* Any field change refreshes the live row header + status pill. */
            $(document).on('input change', '#work-container .work-employer-input', function() {
                var $tr = $workRow(this);
                syncLegacyHidden($tr); updateRowHeader($tr);
            });
            $(document).on('input change', '#work-container .work-fields :input', function() {
                var $tr = $workRow(this);
                updateRowStatus($tr);
            });
            /* File-input change also affects "Complete" pill. */
            $(document).on('change', '#work-container .work-doc-input, #work-container .work-relieve-input', function() {
                var $tr = $workRow(this);
                clearWorkRowUploadErrors($tr);
                updateRowStatus($tr);
            });

            $(document).on('click', '#work-exp-summary-tbody .work-row-edit-trigger', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $wf = workRowFromSummaryTr($(this).closest('.work-exp-summary-tr'));
                if ($wf && $wf.length) toggleRowExpanded($wf, true);
            });

            /* Click compact summary header (or chevron) to expand/collapse for editing. */
            $(document).on('click', '#work-container .work-row-head', function(e) {
                if ($(e.target).closest('.work-row-remove, .remove-work').length) return;
                var $tr = $workRow(this);
                if (!$tr.hasClass('is-complete')) return;
                toggleRowExpanded($tr);
            });
            $(document).on('click', '#work-container .work-row-edit-trigger', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleRowExpanded($workRow(this), true);
            });
            $(document).on('click', '#work-container .work-row-toggle-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleRowExpanded($workRow(this));
            });
            $(document).on('click', '#work-container .work-row-done-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                collapseToSummary($workRow(this));
            });

            /* Add / remove handlers — bound via delegation so cloned rows keep working. */
            document.addEventListener('click', function(e) {
                var container = document.getElementById('work-container');
                if (!container) return;

                if (e.target.closest('.add-more-work')) {
                    e.preventDefault();
                    var workRows = container.querySelectorAll('.work-fields');
                    if (workRows.length >= MAX_WORK_ROWS) {
                        showSectionError('You can add a maximum of ' + MAX_WORK_ROWS + ' work experience entries.');
                        return;
                    }
                    var first = container.querySelector('.work-fields');
                    var newRow = first.cloneNode(true);
                    /* Blank the clone before appending. */
                    newRow.classList.remove('is-collapsed', 'is-complete', 'work-row--compact', 'work-row--expanded', 'work-row--in-summary');
                    $(newRow).removeData('wxSummaryTr');
                    newRow.querySelectorAll('input[type="file"]').forEach(function(el) { el.value = ''; el.removeAttribute('data-has-local-file'); });
                    newRow.querySelectorAll('.local-file-preview').forEach(function(preview) {
                        var blobUrl = preview.dataset ? preview.dataset.blobUrl : '';
                        if (blobUrl) { try { URL.revokeObjectURL(blobUrl); } catch(err) {} }
                        preview.remove();
                    });
                    newRow.querySelectorAll('input[type="text"], input[type="date"], input[type="number"]').forEach(function(inp) { inp.value = ''; });
                    newRow.querySelectorAll('textarea').forEach(function(el) { el.value = ''; });
                    var boardPanel = newRow.querySelector('.work-board-member-panel');
                    if (boardPanel) boardPanel.style.display = 'none';
                    newRow.classList.remove('work-row--board-member');
                    newRow.querySelectorAll('select').forEach(function(sel) { sel.selectedIndex = 0; });
                    var till = newRow.querySelector('.work-date-till'); if (till) till.checked = false;
                    var tillH = newRow.querySelector('.work-date-till-hidden'); if (tillH) tillH.value = '0';
                    newRow.querySelectorAll('.work-duration-y, .work-duration-m, .work-duration-d').forEach(function(inp) { inp.value = ''; });
                    var hTot = newRow.querySelector('.work-experience-total-hidden'); if (hTot) hTot.value = '';
                    var hLevel = newRow.querySelector('.work-level-sync'); if (hLevel) hLevel.value = '';
                    var hEx = newRow.querySelector('.experience-sync'); if (hEx) hEx.value = '';
                    /* Clear inline error messages copied over from the template. */
                    newRow.querySelectorAll('.error-message').forEach(function(el) { el.remove(); });
                    container.appendChild(newRow);
                    initWorkRow($(newRow));
                    refreshWorkSerials();
                    syncSummaryTable();
                    updateOverallTotalYears();
                    /* Smooth scroll the new card into view. */
                    newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    return;
                }

                if (e.target.closest('.remove-work')) {
                    e.preventDefault();
                    var workRows = container.querySelectorAll('.work-fields');
                    if (workRows.length <= 1) {
                        showSectionError('You must have at least one work experience entry.');
                        return;
                    }
                    var card = e.target.closest('.work-fields');
                    if (!card) {
                        var $summaryTr = $(e.target).closest('.work-exp-summary-tr');
                        if ($summaryTr.length) {
                            var $linked = workRowFromSummaryTr($summaryTr);
                            if ($linked && $linked.length) card = $linked[0];
                        }
                    }
                    if (!card) return;
                    var $card = $(card);
                    var $summaryTr = $card.data('wxSummaryTr');
                    if ($summaryTr && $summaryTr.length) {
                        $summaryTr.remove();
                        $card.removeData('wxSummaryTr');
                    }
                    /* Animate-out, then remove. */
                    card.classList.add('is-removing');
                    setTimeout(function() {
                        if (card.parentNode) card.parentNode.removeChild(card);
                        refreshWorkSerials();
                        syncSummaryTable();
                        updateOverallTotalYears();
                    }, 180);
                }
            });
        })();

        $('#verify_form_s').on('click', function() {
            const licenseNumber = $('#certificate_no').val().trim().toUpperCase();
            const date = $('#certificate_valid_to').val().trim();
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
        private_organisation: 'Private organisation',
        electrical_contractor: 'Electrical contractor',
        retired_employee: 'Retired Employee',
        govt_organisation: 'Govt organisation',
        apprenticeship: 'Apprenticeship',
        board_member_tnelb: 'Board Member / Ex. Board Member of TNELB'
    };
    var WORK_NATURE_MAP = {
        erection: 'Erection',
        maintenance: 'Maintenance',
        erection_maintenance: 'Erection & Maintenance'
    };
    var VOLTAGE_LEVEL_MAP = {
        up_to_650v: 'Up to 650V',
        '650v_to_33kv': 'Above 650V to 33KV',
        above_33kv: 'Above 33KV'
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
            workBody.innerHTML = '<tr><td colspan="15" class="text-center text-muted py-3">No work entries</td></tr>';
        } else {
            var fileLink = function(doc) {
                return (doc && doc.files && doc.files[0])
                    ? '<a href="'+URL.createObjectURL(doc.files[0])+'" target="_blank" style="color:#035ab3;font-size:.75rem;"><i class="fa fa-file-pdf-o"></i> View</a>'
                    : '<span class="text-muted">—</span>';
            };
            var val = function(el) { return el ? ((el.value || '').trim() || '—') : '—'; };
            workRows.forEach(function(row, i) {
                var empType  = row.querySelector('.work-employment-type');
                var cat      = row.querySelector('.work-contractor-cat');
                var lic      = row.querySelector('.work-licence-number');
                var employer = row.querySelector('.work-employer-input');
                var address  = row.querySelector('.work-org-address');
                var desig    = row.querySelector('[name="designation[]"]');
                var nature   = row.querySelector('.work-nature');
                var voltage  = row.querySelector('.work-voltage');
                var kva      = row.querySelector('.work-transformer-kva');
                var fromInp  = row.querySelector('.work-date-from');
                var toInp    = row.querySelector('.work-date-to');
                var tillChk  = row.querySelector('.work-date-till');
                var yPart    = row.querySelector('.work-duration-y');
                var mPart    = row.querySelector('.work-duration-m');
                var dPart    = row.querySelector('.work-duration-d');
                var doc      = row.querySelector('[name="work_document[]"]');
                var rel      = row.querySelector('[name="work_relieving_letter[]"]');

                var yv = yPart ? (yPart.value || '').trim() : '';
                var mv = mPart ? (mPart.value || '').trim() : '';
                var dv = dPart ? (dPart.value || '').trim() : '';
                var totalTxt = (yv === '' && mv === '' && dv === '') ? '—' : (yv + 'y ' + mv + 'm ' + dv + 'd');

                var empTxt    = empType ? (EMP_LABEL_MAP[empType.value] || empType.value || '—') : '—';
                var natureTxt = nature ? (WORK_NATURE_MAP[nature.value] || nature.value || '—') : '—';
                var voltTxt   = voltage ? (VOLTAGE_LEVEL_MAP[voltage.value] || voltage.value || '—') : '—';
                var fromDate  = fromInp ? fmtDate(fromInp.getAttribute('data-raw') || fromInp.value) : '—';
                var toDate    = (tillChk && tillChk.checked)
                    ? '<span class="prv-badge-yes">Till date</span>'
                    : (toInp ? fmtDate(toInp.getAttribute('data-raw') || toInp.value) : '—');

                workBody.innerHTML +=
                    '<tr><td class="text-center">'+(i+1)+'</td>'
                    +'<td>'+empTxt+'</td>'
                    +'<td class="text-center">'+val(cat)+'</td>'
                    +'<td>'+val(lic)+'</td>'
                    +'<td>'+val(employer)+'</td>'
                    +'<td>'+val(address)+'</td>'
                    +'<td>'+val(desig)+'</td>'
                    +'<td>'+natureTxt+'</td>'
                    +'<td>'+voltTxt+'</td>'
                    +'<td class="text-center">'+val(kva)+'</td>'
                    +'<td class="text-center">'+fromDate+'</td>'
                    +'<td class="text-center">'+toDate+'</td>'
                    +'<td class="text-center">'+totalTxt+'</td>'
                    +'<td class="text-center">'+fileLink(doc)+'</td>'
                    +'<td class="text-center">'+(tillChk && tillChk.checked ? '<span class="text-muted">N/A</span>' : fileLink(rel))+'</td>'
                    +'</tr>';
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
            var fromEl = document.getElementById('previously_valid_from');
            setVal('prv_prev_from_date', fromEl ? fmtDate(fromEl.value) : '');
            var toEl = document.getElementById('previously_valid_to');
            setVal('prv_prev_to_date', toEl ? fmtDate(toEl.value) : '');
        }

        // Section 9 — Wireman
        var wireYes = document.getElementById('yesOption');
        var isYes8 = wireYes && wireYes.checked;
        var yn8 = document.getElementById('prv_wireman_yn');
        if (yn8) yn8.innerHTML = isYes8 ? '<span class="prv-badge-yes">Yes</span>' : '<span class="prv-badge-no">No</span>';
        var wb = document.getElementById('prv_wireman_details_block'); if (wb) wb.style.display = isYes8 ? '' : 'none';
        if (isYes8) {
            setVal('prv_wireman_cert_no', document.getElementById('certificate_no') ? document.getElementById('certificate_no').value : '');
            var wIssEl = document.getElementById('certificate_issue_date');
            setVal('prv_wireman_issue_date', wIssEl ? fmtDate(wIssEl.value) : '');
            var wFromEl = document.getElementById('certificate_valid_from');
            setVal('prv_wireman_from_date', wFromEl ? fmtDate(wFromEl.value) : '');
            var wToEl = document.getElementById('certificate_valid_to');
            setVal('prv_wireman_to_date', wToEl ? fmtDate(wToEl.value) : '');
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
        modal.classList.add('prv-open');
        document.body.style.overflow = 'hidden';
        document.getElementById('prvConfirmCheck').checked = false;
        document.getElementById('prvConfirmBtn').disabled = true;
        document.getElementById('prvBody').scrollTop = 0;
    }

    function closePreviewModal() {
        var modal = document.getElementById('appPreviewModal');
        modal.style.display = 'none';
        modal.classList.remove('prv-open');
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

    document.getElementById('prvPrintBtn').addEventListener('click', function() {
        window.print();
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

    // Use shared footer preview (#appPreviewModalSw) — populated in footer.blade.php populateSwPreview()
    </script>
</footer>