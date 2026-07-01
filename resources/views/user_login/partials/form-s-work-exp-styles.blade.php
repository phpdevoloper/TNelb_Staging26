@if (($editFormName ?? ($application_details->form_name ?? '')) === 'S')
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
        box-shadow: 0 2px 8px rgba(3, 90, 179, 0.12);
        transition: box-shadow .2s, border-color .2s, opacity .18s, transform .18s;
        overflow: hidden;
        animation: wxRowIn .22s ease;
    }
    .work-row:hover { box-shadow: 0 4px 14px rgba(3, 90, 179, 0.16); border-color: var(--wx-border-strong); }
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
    .work-exp-summary-panel .wx-order-card {
        padding-right: 0;
        background: #fff;
        border: 1px solid var(--wx-border);
        border-radius: var(--wx-radius);
        box-shadow: 0 2px 8px rgba(3, 90, 179, 0.12);
        padding: 12px 14px;
    }
    .wx-summary-table .wx-summary-th-actions,
    .wx-summary-table .work-row-summary-actions {
        width: 100px;
        min-width: 100px;
        max-width: none;
        text-align: center;
        vertical-align: middle;
    }
    .wx-summary-table .work-row-summary-actions {
        white-space: nowrap;
        padding: 8px 6px !important;
    }
    .wx-summary-table .wx-summary-actions-inner {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        flex-wrap: nowrap;
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
    .work-exp-wrap .work-row-head-actions .work-row-remove,
    .work-exp-wrap .work-row-remove.remove-work {
        color: #c1272d !important;
        background: transparent !important;
        border: none !important;
        padding: 0 !important;
        width: 26px !important;
        height: 26px !important;
        min-width: 26px !important;
    }
    .work-exp-wrap .work-row-head-actions .work-row-remove .fa,
    .work-exp-wrap .work-row-remove.remove-work .fa,
    .work-exp-wrap .work-row-remove.remove-work i.fa-trash-o {
        color: #c1272d !important;
    }
    .work-exp-wrap .work-row-head-actions .work-row-remove:hover,
    .work-exp-wrap .work-row-remove.remove-work:hover {
        background: rgba(193, 39, 45, 0.1) !important;
        color: #9e1f24 !important;
    }
    .work-exp-wrap .work-row-head-actions .work-row-remove:hover .fa,
    .work-exp-wrap .work-row-remove.remove-work:hover .fa {
        color: #9e1f24 !important;
    }

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
        font-size: .68rem;
        opacity: .65;
        display: inline !important;
    }
    .work-card-field.is-locked .form-control:disabled,
    .work-card-field.is-locked select.form-control:disabled {
        background: #eef1f6 !important;
        color: #9aa5b8 !important;
        border-color: #dce3ef !important;
        cursor: not-allowed;
        opacity: 1;
    }

    /* Till-date toggle sits just below the To-date input inside its field cell */
    .work-card-till-toggle {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .66rem;
        color: var(--wx-text);
        background: #fff;
        border: 1px dashed var(--wx-border-strong);
        padding: 3px 9px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 2px;
        user-select: none;
        transition: background .15s, border-color .15s;
        width: max-content;
        max-width: 100%;
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
        box-shadow: 0 2px 8px rgba(3, 90, 179, 0.12);
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
    /* Edit page: collapsed saved rows show in summary table only */
    .work-row.is-complete:not(.work-row--expanded) {
        display: none !important;
    }

    /* Keep summary table above entry cards; prevent controls bleeding into the table */
    .work-exp-summary-panel {
        position: relative;
        z-index: 2;
        isolation: isolate;
    }
    .work-rows {
        position: relative;
        z-index: 1;
    }
    .work-exp-section-bar.wx-summary-footer {
        margin-top: 0;
        padding-top: 10px;
        border-top: 1px solid #f0f0f0;
    }

    /* Collapsed / in-table rows: delete only in table Actions column */
    .work-row.is-complete:not(.work-row--expanded) .work-row-head,
    .work-row.work-row--in-summary .work-row-head {
        display: none !important;
    }
    .work-row.is-complete:not(.work-row--expanded) .work-row-head .work-row-remove,
    .work-row.work-row--in-summary .work-row-remove {
        display: none !important;
    }

    /* ── High-contrast controls (edit page theme overrides) ───────────── */

    /* Add row — always visible blue button */
    .work-exp-wrap .work-exp-section-bar .work-exp-add-btn,
    .work-exp-wrap #work-exp-add-btn {
        display: inline-flex !important;
        align-items: center;
        gap: 6px;
        background-color: #035ab3 !important;
        background-image: none !important;
        color: #ffffff !important;
        border: 1px solid #024a98 !important;
        border-radius: 7px;
        padding: 7px 14px !important;
        font-size: .82rem !important;
        font-weight: 600 !important;
        line-height: 1.2;
        box-shadow: 0 2px 6px rgba(3, 90, 179, 0.28) !important;
        opacity: 1 !important;
    }
    .work-exp-wrap .work-exp-section-bar .work-exp-add-btn:hover:not(:disabled),
    .work-exp-wrap #work-exp-add-btn:hover:not(:disabled) {
        background-color: #024a98 !important;
        color: #ffffff !important;
    }
    .work-exp-wrap .work-exp-section-bar .work-exp-add-btn:disabled,
    .work-exp-wrap #work-exp-add-btn:disabled {
        background-color: #b6c2d6 !important;
        border-color: #a8b5ca !important;
        color: #ffffff !important;
        opacity: 0.85 !important;
    }
    .work-exp-wrap .work-exp-section-bar .work-exp-add-btn i,
    .work-exp-wrap .work-exp-section-bar .work-exp-add-btn > span,
    .work-exp-wrap #work-exp-add-btn i,
    .work-exp-wrap #work-exp-add-btn > span {
        color: #ffffff !important;
    }
    .work-exp-wrap .work-exp-section-bar .work-exp-add-btn #work-exp-row-count,
    .work-exp-wrap #work-exp-add-btn #work-exp-row-count {
        color: rgba(255, 255, 255, 0.92) !important;
        opacity: 1 !important;
        font-weight: 500 !important;
    }

    /* Summary table — Edit + Delete clearly visible */
    .work-exp-wrap .wx-summary-table .work-row-summary-actions .wx-order-edit-link {
        display: inline-flex !important;
        align-items: center;
        gap: 4px;
        padding: 5px 9px !important;
        font-size: .76rem !important;
        font-weight: 600 !important;
        color: #035ab3 !important;
        background: #eef4fc !important;
        border: 1px solid #b8cfe8 !important;
        border-radius: 5px;
        text-decoration: none !important;
        line-height: 1.2;
    }
    .work-exp-wrap .wx-summary-table .work-row-summary-actions .wx-order-edit-link:hover {
        background: #dce8f8 !important;
        color: #024a98 !important;
        text-decoration: none !important;
    }
    .work-exp-wrap .wx-summary-table .work-row-summary-actions .wx-order-edit-link i.fa {
        color: #035ab3 !important;
        font-size: .85rem !important;
    }
    .work-exp-wrap .wx-summary-table .work-row-summary-actions .work-row-remove {
        width: 28px !important;
        height: 28px !important;
        min-width: 28px !important;
        margin-left: 0 !important;
        padding: 0 !important;
        color: #ffffff !important;
        background-color: #c1272d !important;
        background-image: none !important;
        border: 1px solid #9e1f24 !important;
        border-radius: 6px !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.15) !important;
    }
    .work-exp-wrap .wx-summary-table .work-row-summary-actions .work-row-remove i.fa {
        color: #ffffff !important;
        font-size: .92rem !important;
        line-height: 1;
    }
    .work-exp-wrap .wx-summary-table .work-row-summary-actions .work-row-remove:hover {
        background-color: #9e1f24 !important;
        color: #ffffff !important;
    }

    /* Combined + per-row date validation messages */
    .work-exp-wrap .work-exp-total-msg-wrap {
        margin-top: 8px;
        min-height: 0;
    }
    .work-exp-wrap .work-exp-total-error {
        display: block;
        font-size: .82rem;
        font-weight: 600;
        padding: 8px 12px;
        border-radius: 6px;
        background: #fff5f5;
        border: 1px solid #f1c0c0;
        color: #c1272d !important;
    }
    .work-exp-wrap .work-entry-block {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 4px;
    }
    .work-exp-wrap .work-entry-block.is-removing {
        opacity: 0;
        transform: translateY(-6px) scale(.98);
        transition: opacity .18s, transform .18s;
    }
    /* Below the card — outside border/background */
    .work-exp-wrap .work-entry-block > .work-row-date-validation {
        display: block;
        min-height: 1.35rem;
        margin: 0;
        padding: 0 4px 4px;
        overflow: visible;
    }
    .work-exp-wrap .work-entry-block > .work-row-date-validation .work-exp-date-range-error {
        font-size: .78rem;
        font-weight: 600;
        line-height: 1.35;
        color: #c1272d !important;
    }
    .work-exp-wrap .work-card-field[data-field="to-date"] {
        overflow: visible;
    }
    .work-exp-wrap .work-row.is-complete.work-row--expanded {
        overflow: visible;
    }

    /* ── Typography isolation (renew / edit / alteration parent .fs-form themes) ── */
    .work-exp-wrap .work-card-field-label {
        font-size: 12px !important;
        font-weight: 600 !important;
        color: var(--wx-text) !important;
        font-family: inherit;
    }
    .work-exp-wrap .work-card-field .form-control,
    .work-exp-wrap .work-card-field select.form-control,
    .work-exp-wrap .work-card-field textarea.form-control {
        font-size: .78rem !important;
        padding: 5px 9px !important;
        line-height: 1.28 !important;
        border-radius: 5px !important;
        height: auto !important;
    }
    .work-exp-wrap .work-card-field:not(.is-locked) .form-control:not(:disabled),
    .work-exp-wrap .work-card-field:not(.is-locked) select.form-control:not(:disabled) {
        background: #fff !important;
        color: #1a2a4a !important;
    }
    .work-exp-wrap .work-card-field .form-control:disabled,
    .work-exp-wrap .work-card-field select.form-control:disabled {
        background: #f1f3f5 !important;
        color: #6b7a99 !important;
        border-color: #ccd5e3 !important;
    }
    .work-exp-wrap .work-card-field-hint {
        font-size: .62rem !important;
        color: var(--wx-muted) !important;
    }
    .work-exp-wrap .work-card-till-toggle {
        font-size: .66rem !important;
        font-weight: 400 !important;
        color: var(--wx-text) !important;
    }
    .work-exp-wrap .work-card-till-toggle input:checked + span {
        font-weight: 600 !important;
    }
    .work-exp-wrap .work-row .form-s-file-upload-wrap--combined .form-control,
    .work-exp-wrap .work-row .form-s-file-upload-wrap--combined input[type="file"] {
        font-size: .78rem !important;
        padding: 5px 9px !important;
    }
    .work-exp-wrap .work-card-duration-cell .work-duration-label {
        font-size: .48rem !important;
        font-weight: 600 !important;
        color: var(--wx-muted) !important;
    }
    .work-exp-wrap .work-card-duration-cell .form-control {
        font-size: .72rem !important;
        font-weight: 700 !important;
    }
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

    /* Board member sub-question panel inside work row (7b) */
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
    .work-board-member-panel-title { margin-right: 2px; }
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
@endif