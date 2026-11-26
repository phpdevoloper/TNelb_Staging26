/* ==========================================================================
   Dashboard application preview (Form S / W / WH / P, renewal, alteration)
   Design tokens: one font stack, one type scale, 4/8/12/16/24px spacing.
   ========================================================================== */
.dash-prv {
    --dp-font: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Noto Sans Tamil', 'Latha', 'Nirmala UI', system-ui, sans-serif;
    --dp-font-ta: 'Noto Sans Tamil', 'Latha', 'Nirmala UI', 'Segoe UI', system-ui, sans-serif;
    --dp-fs-2xs: 0.6875rem;  /* 11px  labels, table headers */
    --dp-fs-xs: 0.75rem;     /* 12px  sub lines, pills, badges */
    --dp-fs-sm: 0.8125rem;   /* 13px  table cells, Tamil sub-lines */
    --dp-fs-md: 0.875rem;    /* 14px  values */
    --dp-fs-lg: 1rem;        /* 16px  section titles */
    --dp-s1: 0.25rem;
    --dp-s2: 0.5rem;
    --dp-s3: 0.75rem;
    --dp-s4: 1rem;
    --dp-s5: 1.5rem;
    --dp-brand: #035ab3;
    --dp-brand-dark: #02468c;
    --dp-brand-soft: #eef5ff;
    --dp-ink: #12233f;
    --dp-text: #243652;
    --dp-muted: #556b8d;
    --dp-faint: #8a9ab3;
    --dp-line: #e3eaf3;
    --dp-line-soft: #eef2f8;
    --dp-surface: #ffffff;
    --dp-surface-2: #f7f9fc;
    --dp-radius: 0.75rem;
    --dp-radius-sm: 0.5rem;
    --dp-shadow: 0 1px 2px rgba(16, 40, 80, 0.06), 0 4px 16px rgba(16, 40, 80, 0.05);

    display: flex;
    flex-direction: column;
    gap: var(--dp-s4);
    min-width: 0;
    font-family: var(--dp-font);
    font-size: var(--dp-fs-md);
    line-height: 1.5;
    color: var(--dp-text);
}
.dash-prv h1, .dash-prv h2, .dash-prv h3,
.dash-prv h4, .dash-prv h5, .dash-prv h6,
.dash-prv table, .dash-prv th, .dash-prv td,
.dash-prv button {
    font-family: var(--dp-font);
}

/* ---------- Meta cards (legacy, kept for compatibility) ---------- */
.dash-prv-meta {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--dp-s3);
}
@media (max-width: 767.98px) {
    .dash-prv-meta { grid-template-columns: 1fr; }
}
.dash-prv-meta-card {
    background: var(--dp-surface);
    border: 1px solid var(--dp-line);
    border-radius: var(--dp-radius-sm);
    padding: var(--dp-s3) var(--dp-s4);
    box-shadow: var(--dp-shadow);
}
.dash-prv-meta-label {
    font-size: var(--dp-fs-2xs);
    font-weight: 700;
    color: var(--dp-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: var(--dp-s1);
}
.dash-prv-meta-value {
    font-size: var(--dp-fs-md);
    font-weight: 600;
    color: var(--dp-ink);
    word-break: break-word;
    line-height: 1.4;
}

/* ---------- Section cards ---------- */
.dash-prv-section {
    background: var(--dp-surface);
    border: 1px solid var(--dp-line);
    border-radius: var(--dp-radius);
    overflow: hidden;
    box-shadow: var(--dp-shadow);
    min-width: 0;
}
.dash-prv-section-hd {
    display: flex;
    align-items: center;
    gap: var(--dp-s3);
    padding: var(--dp-s3) var(--dp-s5);
    min-height: 3.75rem;
    background: var(--dp-surface-2);
    border-bottom: 1px solid var(--dp-line);
}
.dash-prv-section-hd > div { min-width: 0; }
.dash-prv-section-num,
.dash-prv-section-num--sub {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    align-self: center;
    min-width: 2rem;
    width: auto;
    height: 2rem;
    padding: 0 var(--dp-s2);
    margin: 0;
    border-radius: var(--dp-radius-sm);
    background: var(--dp-brand);
    color: #fff;
    font-size: var(--dp-fs-xs);
    font-weight: 700;
    line-height: 1;
    letter-spacing: 0;
    box-shadow: 0 2px 6px rgba(3, 90, 179, 0.22);
}
.dash-prv-section-title {
    font-size: var(--dp-fs-lg);
    font-weight: 600;
    color: var(--dp-ink);
    line-height: 1.4;
    letter-spacing: 0;
}
.dash-prv-section-tamil {
    margin-top: 2px;
    font-family: var(--dp-font-ta);
    font-size: var(--dp-fs-sm);
    font-weight: 400;
    color: var(--dp-muted);
    line-height: 1.55;
}
.dash-prv-section-body {
    padding: var(--dp-s5);
}

/* Sub-questions inside one card (7a / 7b) */
.dash-prv-question + .dash-prv-question {
    border-top: 1px solid var(--dp-line);
}
.dash-prv-question .dash-prv-section-hd {
    margin: 0;
    border-radius: 0;
    border: 0;
    border-bottom: 1px solid var(--dp-line);
}
.dash-prv-question .dash-prv-section-body {
    padding: var(--dp-s5);
}

/* ---------- Personal block ---------- */
.dash-prv-personal {
    display: grid;
    grid-template-columns: 12rem minmax(0, 1fr);
    gap: var(--dp-s5);
    align-items: stretch;
}
@media (max-width: 767.98px) {
    .dash-prv-personal { grid-template-columns: 1fr; }
}
.dash-prv-media {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: var(--dp-s4);
    padding: var(--dp-s4) var(--dp-s3);
    background: var(--dp-surface-2);
    border: 1px solid var(--dp-line);
    border-radius: var(--dp-radius-sm);
}
@media (max-width: 767.98px) {
    .dash-prv-media {
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-around;
        align-items: flex-start;
    }
}
.dash-prv-media-label {
    font-size: var(--dp-fs-2xs);
    font-weight: 700;
    color: var(--dp-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    line-height: 1.4;
    margin-bottom: var(--dp-s2);
    text-align: center;
}
.dash-prv-thumb img,
.dash-prv-no-img {
    display: block;
    margin: 0 auto;
    border: 1px solid var(--dp-line);
    border-radius: var(--dp-radius-sm);
    background: #fff;
}
.dash-prv-thumb--photo img,
.dash-prv-no-img--photo {
    width: 8rem;
    height: 9.5rem;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(16, 40, 80, 0.08);
}
.dash-prv-thumb--sign img,
.dash-prv-no-img--sign {
    width: 100%;
    max-width: 8.5rem;
    height: 3.5rem;
    object-fit: contain;
    background: #fff;
    padding: var(--dp-s1);
}
.dash-prv-no-img {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--dp-faint);
    font-size: var(--dp-fs-xs);
    text-align: center;
    border-style: dashed;
    background: #f0f4f9;
}
.dash-prv-details {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: var(--dp-s4);
    align-content: start;
    min-width: 0;
}
@media (max-width: 991.98px) {
    .dash-prv-details { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575.98px) {
    .dash-prv-details { grid-template-columns: 1fr; }
}

/* ---------- Label / value fields ---------- */
.dash-prv-field {
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.dash-prv-field--full { grid-column: 1 / -1; }
.dash-prv-label {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--dp-s2);
    font-size: var(--dp-fs-2xs);
    font-weight: 700;
    color: var(--dp-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    line-height: 1.4;
    margin-bottom: 6px;
}
.dash-prv-value {
    flex: 1 1 auto;
    font-size: var(--dp-fs-md);
    color: var(--dp-ink);
    font-weight: 600;
    padding: var(--dp-s2) var(--dp-s3);
    background: var(--dp-surface-2);
    border: 1px solid var(--dp-line);
    border-radius: var(--dp-radius-sm);
    min-height: 2.5rem;
    word-break: break-word;
    overflow-wrap: anywhere;
    line-height: 1.5;
}
.dash-prv-value.is-empty {
    color: var(--dp-faint);
    font-style: normal;
    font-weight: 400;
}
.dash-prv-value > .dash-prv-doc-pill { vertical-align: middle; }
.dash-prv-subhead {
    display: flex;
    align-items: center;
    gap: var(--dp-s2);
    font-size: var(--dp-fs-md);
    font-weight: 700;
    color: var(--dp-brand-dark);
    line-height: 1.4;
    margin: var(--dp-s5) 0 var(--dp-s3);
}
.dash-prv-subhead::before {
    content: "";
    width: 3px;
    height: 1rem;
    border-radius: 2px;
    background: var(--dp-brand);
    flex-shrink: 0;
}
.dash-prv-section-body > .dash-prv-subhead:first-child {
    margin-top: 0;
}
.dash-prv-section-body > .dash-prv-field { margin-top: var(--dp-s5); }

/* ---------- Tables ---------- */
.dash-prv-table-wrap {
    width: 100%;
    max-width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    border: 1px solid var(--dp-line);
    border-radius: var(--dp-radius-sm);
    margin-bottom: 0;
    background: #fff;
}
.dash-prv-table {
    width: 100%;
    min-width: 52rem;
    margin: 0;
    border-collapse: collapse;
    table-layout: fixed;
    font-size: var(--dp-fs-sm);
    line-height: 1.45;
    color: var(--dp-text);
}
.dash-prv-table th {
    background: #f1f5fb;
    color: var(--dp-muted);
    font-size: var(--dp-fs-2xs);
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    line-height: 1.35;
    padding: var(--dp-s3);
    border-bottom: 1px solid var(--dp-line);
    border-right: 1px solid var(--dp-line-soft);
    text-align: center;
    vertical-align: middle;
    white-space: normal;
}
.dash-prv-table th:last-child { border-right: 0; }
.dash-prv-table td {
    padding: var(--dp-s3);
    border-bottom: 1px solid var(--dp-line-soft);
    border-right: 1px solid var(--dp-line-soft);
    vertical-align: middle;
    color: var(--dp-text);
    text-align: center;
    overflow-wrap: anywhere;
    word-break: normal;
}
.dash-prv-table td:last-child { border-right: 0; }
.dash-prv-table tbody tr:last-child td { border-bottom: 0; }
.dash-prv-table th.dash-prv-td-left,
.dash-prv-table td.dash-prv-td-left { text-align: left; }
.dash-prv-table tbody tr:nth-child(even) td { background: #fafbfd; }
.dash-prv-table tbody tr:hover td { background: #f3f7fd; }

/* Column widths (table-layout: fixed). Unset columns share the rest. */
.dash-prv-table .dash-prv-col-sno { width: 2.75rem; }
.dash-prv-table .dash-prv-col-month { width: 4.75rem; }
.dash-prv-table .dash-prv-col-year { width: 4.5rem; }
.dash-prv-table .dash-prv-col-cert { width: 8rem; }
.dash-prv-table .dash-prv-col-date { width: 7rem; }
.dash-prv-table .dash-prv-col-dur { width: 8.5rem; }
.dash-prv-table .dash-prv-col-doc { width: 9.5rem; }
.dash-prv-table .dash-prv-col-level { width: 17%; }
.dash-prv-table .dash-prv-col-desig { width: 15%; }

/* ---------- Pills / badges ---------- */
.dash-prv-doc-pill {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    height: 1.75rem;
    padding: 0 var(--dp-s3);
    background: var(--dp-brand-soft);
    color: var(--dp-brand);
    border: 1px solid #cfe1f8;
    border-radius: 999px;
    font-size: var(--dp-fs-xs);
    font-weight: 600;
    line-height: 1;
    text-decoration: none;
    white-space: nowrap;
    vertical-align: middle;
}
.dash-prv-doc-pill .fa { font-size: 0.875rem; line-height: 1; }
.dash-prv-doc-pill .fa-file-pdf-o { color: #c9302c; }
.dash-prv-doc-pill:hover,
.dash-prv-doc-pill:focus {
    background: #dcebff;
    border-color: #b9d4f5;
    color: var(--dp-brand-dark);
    text-decoration: none;
}
.dash-prv .fa-file-pdf-o { color: #c9302c; }
.dash-prv-doc-empty {
    color: var(--dp-faint);
    font-size: var(--dp-fs-xs);
    line-height: 1.4;
}

.dash-prv-yes,
.dash-prv-no,
.dash-prv .asp-qa-answer {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 3.25rem;
    height: 1.625rem;
    padding: 0 var(--dp-s3);
    border-radius: 999px;
    border: 1px solid transparent;
    font-size: var(--dp-fs-xs);
    font-weight: 700;
    line-height: 1;
    letter-spacing: 0.02em;
    list-style: none;
    vertical-align: middle;
}
.dash-prv-yes::before,
.dash-prv-no::before,
.dash-prv-yes::marker,
.dash-prv-no::marker {
    content: none;
    display: none;
}
.dash-prv-yes,
.dash-prv .asp-qa-answer.is-yes {
    background: #e7f6ec;
    border-color: #bfe5cc;
    color: #13612f;
}
.dash-prv-no,
.dash-prv .asp-qa-answer.is-no {
    background: #fdeeee;
    border-color: #f3c7c7;
    color: #9b1c1c;
}

/* ---------- Identity documents ---------- */
.dash-prv-id-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(min(100%, 22rem), 1fr));
    gap: var(--dp-s4);
}
.dash-prv-id-card {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--dp-s3) var(--dp-s4);
    padding: var(--dp-s4);
    background: var(--dp-surface-2);
    border: 1px solid var(--dp-line);
    border-radius: var(--dp-radius-sm);
}
.dash-prv-id-hd {
    grid-column: 1 / -1;
    display: flex;
    align-items: center;
    gap: var(--dp-s2);
    font-size: var(--dp-fs-md);
    font-weight: 700;
    color: var(--dp-ink);
    line-height: 1.4;
}
/* Theme's fontawesome-all.css remaps .fa to Font Awesome 5, which has no
   glyph for fa-id-card-o; force the FA 4.7 face for this icon. */
.dash-prv-id-hd .fa {
    font-family: 'FontAwesome';
    font-weight: normal;
    font-style: normal;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.75rem;
    height: 1.75rem;
    border-radius: var(--dp-radius-sm);
    background: var(--dp-brand-soft);
    color: var(--dp-brand);
    font-size: 0.875rem;
    line-height: 1;
}
.dash-prv-id-card .dash-prv-value {
    display: flex;
    align-items: center;
}
@media (max-width: 575.98px) {
    .dash-prv-id-card { grid-template-columns: 1fr; }
}

/* ---------- Generic field grids ---------- */
.dash-prv-grid-2,
.dash-prv-grid-4,
.dash-prv-grid-id {
    display: grid;
    gap: var(--dp-s4);
}
.dash-prv-grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.dash-prv-grid-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
.dash-prv-grid-id { grid-template-columns: repeat(2, minmax(0, 1fr)); }
.dash-prv-section-body > .mb-2 { margin-bottom: var(--dp-s4) !important; }
@media (max-width: 991.98px) {
    .dash-prv-grid-4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 575.98px) {
    .dash-prv-grid-2,
    .dash-prv-grid-4,
    .dash-prv-grid-id { grid-template-columns: 1fr; }
}

/* ---------- Legacy work-experience summary (wx-*) ---------- */
.dash-prv .wx-summary-table-wrap {
    overflow-x: auto;
    border: 1px solid var(--dp-line);
    border-radius: var(--dp-radius-sm);
}
.dash-prv .wx-summary-table {
    width: 100%;
    border-collapse: collapse;
    font-size: var(--dp-fs-sm);
    margin: 0;
}
.dash-prv .wx-summary-table thead th {
    background: #f1f5fb;
    color: var(--dp-muted);
    font-size: var(--dp-fs-2xs);
    font-weight: 700;
    text-transform: uppercase;
    padding: var(--dp-s2);
    border: 1px solid var(--dp-line);
    text-align: center;
}
.dash-prv .wx-summary-table td {
    padding: var(--dp-s2);
    border: 1px solid var(--dp-line-soft);
    vertical-align: top;
    color: var(--dp-text);
}
.dash-prv .wx-sum-main { display: block; font-weight: 700; }
.dash-prv .wx-sum-sub { display: block; font-size: var(--dp-fs-xs); color: var(--dp-muted); margin-top: 2px; }
.dash-prv .wx-period-dates { display: flex; gap: var(--dp-s3); flex-wrap: wrap; }
.dash-prv .wx-period-mini { display: flex; flex-direction: column; }
.dash-prv .wx-period-label { font-size: var(--dp-fs-2xs); color: var(--dp-muted); font-weight: 700; }
.dash-prv .wx-period-duration { display: flex; gap: var(--dp-s2); margin-top: var(--dp-s2); }
.dash-prv .wx-period-dur-cell {
    background: var(--dp-brand-soft);
    border: 1px solid #cfe1f8;
    border-radius: 4px;
    padding: 2px var(--dp-s2);
    text-align: center;
    min-width: 3rem;
}
.dash-prv .wx-period-dur-num { display: block; font-weight: 700; color: var(--dp-brand); }
.dash-prv .wx-period-dur-lbl { font-size: var(--dp-fs-2xs); color: var(--dp-muted); }
.dash-prv .wx-sum-attach-block { margin-bottom: var(--dp-s1); }
.dash-prv .wx-sum-attach-label { font-size: var(--dp-fs-xs); color: var(--dp-muted); font-weight: 700; }
.dash-prv .wx-sum-doc-link { color: var(--dp-brand); font-weight: 600; font-size: var(--dp-fs-xs); }
.dash-prv .wx-sum-doc-link .fa-file-pdf-o,
.dash-prv .doc-pdf-link .fa-file-pdf-o {
    color: #c9302c;
}

/* ---------- Work-experience table ---------- */
.dash-prv-exp-table-wrap { margin-bottom: 0; }
.dash-prv-exp-table {
    min-width: 56rem;
    table-layout: fixed;
}
.dash-prv-exp-table.is-compact { min-width: 48rem; }
.dash-prv-exp-table .dash-prv-exp-sno { width: 2.75rem; }
.dash-prv-exp-table .dash-prv-col-emp { width: 16%; }
.dash-prv-exp-table .dash-prv-col-desig { width: 14%; }
.dash-prv-exp-table .dash-prv-col-nature { width: 17%; }
.dash-prv-exp-table .dash-prv-col-period { width: 10rem; }
.dash-prv-exp-table .dash-prv-col-docs { width: 9.5rem; }
.dash-prv-exp-table tbody td { vertical-align: top; }
.dash-prv-exp-table tbody td strong { font-weight: 600; color: var(--dp-ink); }
.dash-prv-exp-table tfoot td {
    background: #f1f5fb;
    border-top: 1px solid var(--dp-line);
    border-bottom: 0;
    font-size: var(--dp-fs-sm);
    color: var(--dp-ink);
    vertical-align: middle;
}
.dash-prv-exp-table tfoot td strong { font-weight: 700; }
.dash-prv-exp-table tfoot td.dash-prv-exp-total {
    color: var(--dp-brand);
    text-align: left;
    white-space: nowrap;
    overflow-wrap: normal;
}
/* Period cell: same font/size/alignment as neighbouring cells */
.dash-prv-exp-table td.dash-prv-exp-period {
    font-family: var(--dp-font);
    font-size: var(--dp-fs-sm);
    line-height: 1.45;
    overflow-wrap: normal;
}
/* Period cell: duration pill first, then aligned From / To rows */
.dash-prv-exp-date-row {
    display: flex;
    align-items: baseline;
    margin-top: 2px;
    white-space: nowrap;
}
.dash-prv-exp-dur + .dash-prv-exp-date-row { margin-top: 6px; }
.dash-prv-exp-date-lbl {
    flex: 0 0 2.5rem;
    width: 2.5rem;
    font-size: var(--dp-fs-2xs);
    font-weight: 700;
    color: var(--dp-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.dash-prv-exp-date-val {
    font-size: var(--dp-fs-sm);
    font-weight: 400;
    color: var(--dp-text);
    white-space: nowrap;
}
.dash-prv-exp-sub {
    display: block;
    margin-top: 2px;
    font-size: var(--dp-fs-xs);
    font-weight: 400;
    color: var(--dp-muted);
    line-height: 1.4;
}
.dash-prv-exp-dur {
    display: table; /* own line, shrink-wrapped to content */
    margin-top: 0;
    padding: 2px var(--dp-s3);
    border: 1px solid #cfe1f8;
    border-radius: 999px;
    background: var(--dp-brand-soft);
    font-family: var(--dp-font);
    font-size: var(--dp-fs-xs);
    font-weight: 600;
    color: var(--dp-brand);
    line-height: 1.5;
    white-space: nowrap;
}

/* Form P power-station table: dates/duration aligned like text columns */
.dash-prv-ps-table .dash-prv-col-dur { width: 10rem; }
.dash-prv-ps-table td.dash-prv-ps-dur { white-space: nowrap; overflow-wrap: normal; }
.dash-prv-exp-docs {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 6px;
}
.dash-prv-exp-docs .dash-prv-doc-pill {
    width: 100%;
    justify-content: flex-start;
}
.dash-prv-exp-docs .dash-prv-doc-empty {
    display: flex;
    align-items: center;
    min-height: 1.75rem;
    padding: 0 var(--dp-s1);
}

/* ---------- 7b Board member (shared partial; styles scoped here) ---------- */
.dash-prv .board-member-qa-block { min-width: 0; }
.dash-prv .board-member-qa-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: var(--dp-s3);
    margin: 0;
    padding: var(--dp-s3) var(--dp-s4);
    background: var(--dp-surface-2);
    border: 1px solid var(--dp-line);
    border-radius: var(--dp-radius-sm);
}
.dash-prv .asp-section-title {
    margin: 0;
    font-size: var(--dp-fs-md);
    font-weight: 600;
    line-height: 1.4;
    color: var(--dp-ink);
}
.dash-prv .board-member-detail-wrap {
    margin-top: var(--dp-s3);
    padding: var(--dp-s2) var(--dp-s4);
    border: 1px solid var(--dp-line);
    border-radius: var(--dp-radius-sm);
}
.dash-prv .board-member-detail-alter-flag { padding-top: var(--dp-s2); }
.dash-prv .board-member-detail-table {
    width: 100%;
    margin: 0;
    font-size: var(--dp-fs-sm);
}
.dash-prv .board-member-detail-table th,
.dash-prv .board-member-detail-table td {
    border: 0;
    border-bottom: 1px solid var(--dp-line-soft);
    vertical-align: middle;
    line-height: 1.5;
}
.dash-prv .board-member-detail-table tr:last-child th,
.dash-prv .board-member-detail-table tr:last-child td { border-bottom: 0; }
.dash-prv .board-member-detail-table th {
    width: 30%;
    text-align: left;
    font-size: var(--dp-fs-2xs);
    font-weight: 700;
    color: var(--dp-muted);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: var(--dp-s2) var(--dp-s3) var(--dp-s2) 0;
}
.dash-prv .board-member-detail-table td {
    padding: var(--dp-s2) 0;
    color: var(--dp-ink);
    font-weight: 600;
}
.dash-prv .doc-pdf-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    font-size: var(--dp-fs-xs);
}
@media (max-width: 575.98px) {
    .dash-prv .board-member-detail-table th,
    .dash-prv .board-member-detail-table td {
        display: block;
        width: 100%;
        padding: var(--dp-s1) 0;
        border-bottom: 0;
    }
    .dash-prv .board-member-detail-table td { padding-bottom: var(--dp-s2); border-bottom: 1px solid var(--dp-line-soft); }
}

/* ---------- Alteration highlights ---------- */
.dash-prv-alter {
    background: #fffbeb;
    border: 1px solid #f5d48a;
    border-radius: var(--dp-radius);
    padding: var(--dp-s4) var(--dp-s5);
    box-shadow: var(--dp-shadow);
    font-size: var(--dp-fs-md);
}
.dash-prv-alter-title {
    font-weight: 700;
    color: #92400e;
}
.dash-prv-alter ul {
    margin: var(--dp-s2) 0 0;
    padding-left: 1.25rem;
}
.dash-prv-alter li + li { margin-top: var(--dp-s1); }
.dash-prv-alter-badge,
.dash-prv .asp-alter-badge,
.dash-prv .asp-renew-badge {
    display: inline-block;
    font-size: 0.625rem;
    font-weight: 700;
    line-height: 1.4;
    padding: 1px 6px;
    border-radius: 4px;
    background: #fef3c7;
    color: #92400e;
    border: 1px solid #f59e0b;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    vertical-align: middle;
}
.dash-prv .asp-renew-badge {
    background: #e0f2fe;
    color: #075985;
    border-color: #7dd3fc;
}
.dash-prv-field.is-altered {
    background: #fffbeb;
    box-shadow: inset 3px 0 0 #f59e0b;
    border-radius: var(--dp-radius-sm);
    padding: var(--dp-s2) var(--dp-s2) var(--dp-s2) var(--dp-s3);
}
.dash-prv-table tbody tr.is-altered td {
    background: #fffbeb;
}
.dash-prv-table tbody tr.is-altered td:first-child {
    box-shadow: inset 3px 0 0 #f59e0b;
}
.dash-prv-prev {
    margin-top: var(--dp-s1);
    color: #64748b;
    font-size: var(--dp-fs-xs);
    font-weight: 600;
}
.dash-prv-field .mt-1 { margin-top: var(--dp-s2) !important; }

/* ---------- Responsive ---------- */
@media (max-width: 767.98px) {
    .dash-prv-section-hd { padding: var(--dp-s3) var(--dp-s4); }
    .dash-prv-section-body,
    .dash-prv-question .dash-prv-section-body { padding: var(--dp-s4); }
    .dash-prv-section-title { font-size: 0.9375rem; }
}

/* ---------- Print ---------- */
@media print {
    .dash-prv { gap: var(--dp-s3); }
    .dash-prv-section,
    .dash-prv-alter {
        box-shadow: none !important;
        break-inside: avoid-page;
    }
    .dash-prv-question { break-inside: avoid-page; }
    .dash-prv-section-hd { background: #f3f6fa !important; }
    .dash-prv-table-wrap { overflow: visible !important; }
    .dash-prv-table,
    .dash-prv-exp-table,
    .dash-prv-exp-table.is-compact { min-width: 0 !important; }
    .dash-prv-table tr { break-inside: avoid; }
    .dash-prv-table tbody tr:hover td { background: inherit; }
    .dash-prv-section-num,
    .dash-prv-section-num--sub {
        box-shadow: none;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .dash-prv-yes,
    .dash-prv-no,
    .dash-prv-exp-dur,
    .dash-prv .asp-qa-answer {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
