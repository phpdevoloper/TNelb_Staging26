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
    .fs-section-num--sub {
        width: auto;
        min-width: 34px;
        height: 26px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1;
    }
    .fs-question-part + .fs-question-part {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px dashed #d5deed;
    }
    .fs-question-part-hd {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 12px;
    }
    .fs-question-part-hd .fs-section-title {
        font-size: .84rem;
    }
    .fs-question-part-hd .fs-section-tamil {
        font-size: .76rem;
    }
    /* Board member sub-question panel inside 7b work row */
    .work-row-grid-span { grid-column: 1 / -1; }
    .fs-question-part--7b { position: relative; }
    .fs-7b-hd.fs-question-part-hd {
        align-items: center;
        margin-bottom: 8px;
    }
    .fs-7b-hd .fs-section-num--sub {
        align-self: center;
    }
    .fs-7b-hd-content { flex: 1; min-width: 0; }
    .fs-7b-board-gate-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .fs-7b-board-gate-label { flex: 1; min-width: 0; }
    .fs-7b-board-gate-label .fs-section-title {
        margin-bottom: 1px;
        line-height: 1.35;
    }
    .fs-7b-board-gate-label .fs-section-tamil {
        font-size: .74rem;
        margin-top: 0;
        line-height: 1.35;
    }
    .fs-7b-board-gate-row .fs-segmented-toggle {
        flex-shrink: 0;
        align-self: center;
    }
    .fs-7b-board-toggle.fs-segmented-toggle {
        border-radius: 6px;
        box-shadow: none;
        align-items: center;
        line-height: 1;
        vertical-align: middle;
    }
    .fs-7b-board-toggle .fs-segmented-opt {
        display: flex;
        margin: 0;
        padding: 0;
        line-height: 1;
        cursor: pointer;
        user-select: none;
    }
    .fs-7b-board-toggle .fs-segmented-opt span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 44px;
        padding: 3px 12px;
        font-size: .72rem;
        font-weight: 600;
        line-height: 1;
    }
    @media (max-width: 768px) {
        .fs-7b-hd.fs-question-part-hd {
            align-items: flex-start;
        }
        .fs-7b-board-gate-row {
            flex-direction: column;
            align-items: stretch;
        }
        .fs-7b-board-gate-row .fs-segmented-toggle {
            align-self: flex-end;
        }
    }
    .fs-7b-board-details { margin-top: 6px; }
    .fs-7b-work-wrap { margin-top: 4px; }
    .fs-7b-mode-board .fs-7b-work-wrap {
        padding: 0;
        background: transparent;
        border: 0;
        border-radius: 0;
    }
    #work-container-current .work-row-grid.row {
        display: flex;
        flex-wrap: wrap;
        grid-template-columns: none;
        --bs-gutter-x: 14px;
        --bs-gutter-y: 10px;
        gap: 0;
        padding: 10px 4px 6px;
        margin-right: 0;
        margin-left: 0;
    }
    #work-container-current .work-row-grid.row > .work-card-field,
    #work-container-current .work-row-grid.row > .work-board-member-panel {
        margin-bottom: 6px;
    }
    #work-container-current .work-board-member-panel.col-12 {
        padding-left: 0;
        padding-right: 0;
    }
    #work-container-current .work-board-member-panel .row {
        margin-left: calc(var(--bs-gutter-x, 0.5rem) * -0.5);
        margin-right: calc(var(--bs-gutter-x, 0.5rem) * -0.5);
        --bs-gutter-x: 14px;
        --bs-gutter-y: 10px;
    }
    #work-container-current [data-field="board-meeting-details"] textarea.form-control {
        min-height: 52px;
        resize: vertical;
    }
    #work-container-current .work-row-done-bar.col-12 {
        padding-left: 0;
        padding-right: 0;
        margin-top: 0;
        padding-bottom: 4px;
    }
    #work-container-current .work-card-field { gap: 5px; }
    #work-container-current .work-card-field .form-control {
        min-height: 36px;
    }
    #work-container-current .work-card-till-toggle {
        font-size: .66rem;
        padding: 4px 8px;
        margin-top: 5px;
    }
    #work-container-current .work-row.work-row--board-member [data-field="support-doc"] {
        grid-column: unset;
        padding-top: 0;
        border-top: 0;
        margin-top: 0;
        max-width: none;
    }
    /* 7b — flat layout: no nested card/panel chrome */
    #work-container-current .work-entry-block,
    #work-container-current .work-fields.work-row {
        background: transparent;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }
    #work-container-current .work-fields.work-row:hover {
        border-color: transparent;
        box-shadow: none;
    }
    #work-container-current .work-row-head {
        display: none !important;
    }
    #work-container-current .work-board-member-panel,
    #work-container-current .work-board-member-panel.col-12 {
        margin: 0;
        padding: 8px 0 4px;
        background: transparent;
        border: 0;
        border-left: 0;
        border-radius: 0;
        box-shadow: none;
    }
    #work-container-current .work-board-member-panel .row {
        row-gap: 10px;
    }
    #work-container-current .work-rows {
        padding: 0;
        gap: 0;
    }
    .fs-segmented-toggle {
        display: inline-flex;
        align-items: stretch;
        border: 1px solid #b8cfe8;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 3px rgba(3, 90, 179, 0.06);
    }
    .fs-segmented-opt {
        position: relative;
        margin: 0;
        cursor: pointer;
        user-select: none;
    }
    .fs-segmented-opt input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }
    .fs-segmented-opt span {
        display: block;
        min-width: 72px;
        padding: 8px 22px;
        font-size: .84rem;
        font-weight: 600;
        text-align: center;
        color: #5a7299;
        background: #fff;
        border-right: 1px solid #e3eaf5;
        transition: background .15s, color .15s;
    }
    .fs-segmented-opt:last-child span { border-right: 0; }
    .fs-segmented-opt.is-active span,
    .fs-segmented-opt input:checked + span {
        background: #035ab3;
        color: #fff;
    }
    .fs-segmented-opt input:focus-visible + span {
        outline: 2px solid #035ab3;
        outline-offset: 2px;
    }
    .fs-7b-board-toggle .fs-segmented-opt input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
        margin: 0;
        padding: 0;
        border: 0;
    }
    .fs-7b-board-toggle .fs-segmented-opt span {
        color: #5a7299;
        background: #fff;
        border-right: 1px solid #e3eaf5;
    }
    .fs-7b-board-toggle .fs-segmented-opt:last-child span { border-right: 0; }
    .fs-7b-board-toggle .fs-segmented-opt.is-active span,
    .fs-7b-board-toggle .fs-segmented-opt input:checked + span {
        background: #035ab3;
        color: #fff;
    }
    .fs-7b-mode-board #work-container-current .work-card-field:has(.work-employment-type),
    #work-container-current [data-field="contractor-cat"],
    #work-container-current [data-field="licence-number"],
    #work-container-current [data-field="work-nature"],
    #work-container-current [data-field="voltage-level"],
    #work-container-current [data-field="transformer-kva"],
    #work-container-current [data-field="relieve"] {
        display: none !important;
    }
    .fs-7b-mode-standard #work-container-current option[value="board_member_tnelb"] {
        display: none;
    }
    #work-container-current .work-row-remove,
    #work-exp-summary-panel-current .work-row-remove {
        display: none !important;
    }
    .fs-board-member-fee-banner {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin: 0 0 12px;
        padding: 10px 14px;
        font-size: .8rem;
        line-height: 1.45;
        color: #1f5c35;
        background: #e8f7ee;
        border: 1px solid #b8dfc8;
        border-left: 4px solid #2e9b52;
        border-radius: 8px;
    }
    .fs-board-member-fee-banner .fa {
        color: #2e9b52;
        font-size: 1rem;
        margin-top: 1px;
        flex-shrink: 0;
    }
    .work-board-member-panel {
        margin-top: 6px;
        padding: 14px 16px 12px;
        background: linear-gradient(180deg, #f7faff 0%, #f2f7fd 100%);
        border: 1px solid #b8cfe8;
        border-left: 4px solid #035ab3;
        border-radius: 0 10px 10px 0;
        box-shadow: 0 2px 8px rgba(3, 90, 179, 0.07);
    }
    .work-board-member-panel-hd {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px 8px;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px dashed #c5d5eb;
    }
    .work-board-member-panel-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 8px;
        border-radius: 999px;
        background: #035ab3;
        color: #fff;
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }
    .work-board-member-panel-title {
        font-size: .86rem;
        font-weight: 700;
        color: #1a2a4a;
    }
    .work-board-member-panel-hint {
        flex: 1 1 100%;
        font-size: .72rem;
        font-weight: 500;
        color: #5a7299;
        line-height: 1.35;
    }
    .work-board-member-panel-tamil {
        flex: 1 1 100%;
        font-size: .74rem;
        color: #5a7299;
        line-height: 1.35;
    }
    .work-board-member-panel-body {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 12px 16px;
    }
    @media (max-width: 768px) {
        .work-board-member-panel-body { grid-template-columns: 1fr; }
    }
    .work-board-member-panel-body .work-card-field textarea.form-control {
        min-height: 72px;
        resize: vertical;
    }
    .work-board-member-panel-note {
        margin: 12px 0 0;
        padding-top: 10px;
        border-top: 1px dashed #d5deed;
        font-size: .72rem;
        color: #5a7299;
    }
    .work-board-member-panel-note .fa { color: #035ab3; margin-right: 4px; }
    .work-row.work-row--board-member [data-field="contractor-cat"],
    .work-row.work-row--board-member [data-field="licence-number"],
    .work-row.work-row--board-member [data-field="work-nature"],
    .work-row.work-row--board-member [data-field="voltage-level"],
    .work-row.work-row--board-member [data-field="transformer-kva"],
    .work-row.work-row--board-member [data-field="relieve"] {
        display: none !important;
    }
    .work-row.work-row--board-member [data-field="support-doc"] {
        grid-column: 1 / -1;
        padding-top: 4px;
        border-top: 1px dashed #e3eaf5;
        margin-top: 4px;
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
        /* color: #5a7299; */
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
        /* color: #7a90b0; */
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
    .work-exp-section-bar .work-exp-add-btn,
    .work-exp-section-bar .work-exp-add-btn i,
    .work-exp-section-bar .work-exp-add-btn span,
    .work-exp-section-bar .work-exp-add-btn .work-exp-row-count {
        color: #fff;
    }
    .work-exp-section-bar .work-exp-add-btn .work-exp-row-count {
        font-weight: 500;
        font-size: .7rem;
        opacity: .92;
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
    .prv-section-num--sub { width:auto; min-width:28px; height:20px; padding:0 8px; border-radius:999px; font-size:.64rem; font-weight:700; letter-spacing:.02em; line-height:1; }
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

                    {{-- ═══ SECTION 7 — Work Experience (7a Previous / 7b Current) ═══ --}}
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
                            @php
                                use App\Support\FormSExperiencePartition;

                                $showBoardMemberEmploymentType = false;
                                $partition = FormSExperiencePartition::partition($exp_details ?? collect());
                                $previousExpDetails = $partition['previous'];
                                $currentExpDetails = $partition['current'];
                                $is7bBoardMemberPrefill = $partition['is7bBoardMemberPrefill'];
                            @endphp

                            <div class="fs-question-part">
                                <div class="fs-question-part-hd">
                                    <span class="fs-section-num fs-section-num--sub">7a</span>
                                </div>
                                @include('user_login.partials.form-s-work-exp-section', [
                                    'exp_details' => $previousExpDetails,
                                    'showBoardMemberEmploymentType' => $showBoardMemberEmploymentType,
                                    'workContainerId' => 'work-container-previous',
                                    'workAddBtnId' => 'work-exp-add-btn-previous',
                                    'workRowCountId' => 'work-exp-row-count-previous',
                                    'workSummaryTbodyId' => 'work-exp-summary-tbody-previous',
                                    'workMaxRows' => 7,
                                    'workMinRows' => 1,
                                    'workPart' => 'previous',
                                ])
                            </div>

                            @php
                                $is7bBoardMemberPrefill = $partition['is7bBoardMemberPrefill'];
                            @endphp
                            <div class="fs-question-part fs-question-part--7b {{ $is7bBoardMemberPrefill ? 'fs-7b-mode-board' : 'fs-7b-mode-standard' }}" id="fs-7b-root">
                                <div class="fs-question-part-hd fs-7b-hd">
                                    <span class="fs-section-num fs-section-num--sub">7b</span>
                                    <div class="fs-7b-hd-content">
                                        <div class="fs-7b-board-gate-row" role="group" aria-labelledby="fs-7b-board-gate-label">
                                            <div class="fs-7b-board-gate-label" id="fs-7b-board-gate-label">
                                                <div class="fs-section-title">
                                                    Are you a Board member of TNELB or Ex board member of TNELB?
                                                    <span class="section-req">*</span>
                                                </div>
                                                <div class="fs-section-tamil">தமிழ்நாடு மின்சார வாரிய கோப்புறை / முன்னாள் கோப்புறை உறுப்பினரா?</div>
                                            </div>
                                            <div class="fs-segmented-toggle fs-7b-board-toggle" role="radiogroup" aria-label="Board member of TNELB or Ex board member">
                                                <label class="fs-segmented-opt{{ $is7bBoardMemberPrefill ? '' : ' is-active' }}">
                                                    <input type="radio" name="current_work_board_member" id="current_work_board_member_no" value="no"{{ $is7bBoardMemberPrefill ? '' : ' checked' }}>
                                                    <span>No</span>
                                                </label>
                                                <label class="fs-segmented-opt{{ $is7bBoardMemberPrefill ? ' is-active' : '' }}">
                                                    <input type="radio" name="current_work_board_member" id="current_work_board_member_yes" value="yes"{{ $is7bBoardMemberPrefill ? ' checked' : '' }}>
                                                    <span>Yes</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="fs-7b-board-details" class="fs-7b-board-details{{ $is7bBoardMemberPrefill ? '' : ' d-none' }}">
                                <div id="fs-7b-work-wrap" class="fs-7b-work-wrap">
                                @include('user_login.partials.form-s-work-exp-section', [
                                    'exp_details' => $currentExpDetails,
                                    'workContainerId' => 'work-container-current',
                                    'workAddBtnId' => 'work-exp-add-btn-current',
                                    'workRowCountId' => 'work-exp-row-count-current',
                                    'workSummaryTbodyId' => 'work-exp-summary-tbody-current',
                                    'workMaxRows' => 1,
                                    'workMinRows' => 1,
                                    'workPart' => 'current',
                                    'showSummaryPanel' => false,
                                    'showAddRow' => false,
                                    'showBoardMemberEmploymentType' => true,
                                    'defaultTillDate' => true,
                                    'hideDuration' => true,
                                    'hideRemoveButton' => true,
                                    'hideBoardPanelNote' => true,
                                    'useBootstrapGrid' => true,
                                ])
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══ SECTION 8 — Previous License ═══ --}}
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
                    <input type="hidden" id="amount" name="amount" value="">
                    <input type="hidden" id="board_member_fee_exempt" name="board_member_fee_exempt" value="0">
                    <input type="hidden" id="form_action" name="form_action" value="draft">
                    @csrf

                    <div id="board-member-fee-notice" class="alert alert-info d-none mb-3 py-2 px-3" role="status" style="font-size:.9rem;">
                        <i class="fa fa-info-circle" aria-hidden="true"></i>
                        <strong>Fee not applicable:</strong> Applicants with TNEB/TANGEDCO Board Member work experience are exempt from application fees. You may proceed without payment.
                    </div>

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

            {{-- Section 7a: Previous Work Experience --}}
            <div class="prv-section">
                <div class="prv-section-hd">
                    <span class="prv-section-num prv-section-num--sub">7a</span>
                    <span class="prv-section-title">Previous Work Experience</span>
                </div>
                <div class="prv-section-body p-0">
                    <div style="overflow-x:auto;">
                        <table class="prv-table" id="prv_work_table_previous">
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
                            <tbody id="prv_work_body_previous">
                                <tr><td colspan="15" class="text-center text-muted py-3">—</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Section 7b: Current Work Experience --}}
            <div class="prv-section">
                <div class="prv-section-hd">
                    <span class="prv-section-num prv-section-num--sub">7b</span>
                    <span class="prv-section-title">Current Work Experience</span>
                </div>
                <div class="prv-section-body p-0">
                    <div style="overflow-x:auto;">
                        <table class="prv-table" id="prv_work_table_current">
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
                            <tbody id="prv_work_body_current">
                                <tr><td colspan="15" class="text-center text-muted py-3">—</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Section 8: Supervisor Certificate --}}
            <div class="prv-section">
                <div class="prv-section-hd">
                    <span class="prv-section-num">8</span>
                    <span class="prv-section-title">@include('user_login.partials.form-s-question-8-preview-title')</span>
                </div>
                <div class="prv-section-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span style="font-size:.8rem;color:#5a7299;font-weight:600;">Possess Certificate:</span>
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

            {{-- Section 9: Wireman Certificate --}}
            <div class="prv-section">
                <div class="prv-section-hd">
                    <span class="prv-section-num">9</span>
                    <span class="prv-section-title">@include('user_login.partials.form-s-question-9-preview-title')</span>
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

    @include('user_login.partials.form-s-work-exp-scripts', [
        'editFormName' => 'S',
        'showBoardMemberEmploymentType' => false,
        'enableBoardMemberFeeExempt' => true,
    ])

    <script>
        window.FORM_S_CONFIG = {
            verifyLicenseUrl: @json(route('verifylicense')),
            formInstructionUrl: @json(route('licences.getFormInstruction')),
        };
    </script>
    <script src="{{ url('assets/js/form_s.js') }}"></script>
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

                if (!$row.length) {
                    if (typeof window.wxSyncBoardMemberRenewalFee === 'function') {
                        window.wxSyncBoardMemberRenewalFee();
                    }
                    return;
                }

                var $emp = $row.find('.work-employment-type');
                if (isYes) {
                    if ($emp.val() !== BOARD_MEMBER_TYPE) {
                        $emp.val(BOARD_MEMBER_TYPE).trigger('change');
                    }
                    $row.addClass('work-row--expanded').removeClass('work-row--compact work-row--in-summary');
                } else if ($emp.val() === BOARD_MEMBER_TYPE) {
                    $emp.val('').trigger('change');
                }

                if (typeof window.wxSyncBoardMemberRenewalFee === 'function') {
                    window.wxSyncBoardMemberRenewalFee();
                }
            }

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

        $(document).ready(async function () {
            if (typeof window.wxSyncBoardMemberRenewalFee !== 'function') return;
            try {
                if (typeof getPaymentsService === 'function') {
                    const licence_code = ($('#license_name').val() || '').trim();
                    const appl_type = ($('#appl_type').val() || '').trim();
                    if (licence_code && appl_type) {
                        const data = await getPaymentsService(licence_code, '', appl_type, { silent: true });
                        if (data && data.total_fees !== undefined && data.total_fees !== null && data.total_fees !== '') {
                            $('#amount').val(data.total_fees);
                        }
                    }
                }
            } catch (e) { /* keep empty fallback */ }
            await window.wxSyncBoardMemberRenewalFee();
        });
    </script>
</footer>
