{{-- Form P application preview modal (apply / edit / renewal) --}}
@php
    $formPPreviewIsRenewal = ($isRenewFormP ?? false) || (($application_details->appl_type ?? '') === 'R');
@endphp
<style id="form-p-preview-modal-styles">
    /* ── Form P Preview Modal ─────────────────────────── */
    #appPreviewModalFormP.prv-fp-overlay {
        position: fixed; inset: 0; z-index: 10050;
        background: rgba(10, 24, 48, .58);
        display: none; align-items: center; justify-content: center;
        padding: 20px 16px;
        backdrop-filter: blur(2px);
    }
    #appPreviewModalFormP.prv-fp-overlay.is-open { display: flex; }
    @media (max-width: 767.98px) {
        #appPreviewModalFormP.prv-fp-overlay { align-items: flex-end; padding: 0; }
    }
    .prv-fp-modal-root .prv-fp-panel {
        background: #f0f4f9; width: 100%; max-width: 940px;
        max-height: min(90vh, 920px); display: flex; flex-direction: column;
        border-radius: 14px; overflow: hidden;
        box-shadow: 0 18px 48px rgba(3, 90, 179, .22);
        animation: prvFpIn .28s ease;
    }
    @media (max-width: 767.98px) {
        .prv-fp-modal-root .prv-fp-panel {
            max-height: 92vh; border-radius: 16px 16px 0 0;
            animation: prvFpSlideUp .28s ease;
        }
    }
    @keyframes prvFpIn { from { opacity: 0; transform: scale(.97); } to { opacity: 1; transform: scale(1); } }
    @keyframes prvFpSlideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }

    .prv-fp-modal-root .prv-fp-header {
        background: linear-gradient(135deg, #035ab3 0%, #0472d9 100%);
        padding: 16px 22px 14px; flex-shrink: 0;
        display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
    }
    .prv-fp-modal-root .prv-fp-header-main { min-width: 0; }
    .prv-fp-modal-root .prv-fp-title {
        margin: 0; font-size: 1.05rem; font-weight: 700; color: #fff; line-height: 1.35;
        display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
    }
    .prv-fp-modal-root .prv-fp-title .fa { opacity: .9; }
    .prv-fp-modal-root .prv-fp-badge {
        display: inline-block; background: rgba(255,255,255,.16);
        border: 1px solid rgba(255,255,255,.32); color: #fff;
        border-radius: 999px; padding: 2px 11px; font-size: .72rem; font-weight: 600;
    }
    .prv-fp-modal-root .prv-fp-badge--renew { background: rgba(255, 193, 7, .22); border-color: rgba(255, 220, 100, .45); }
    .prv-fp-modal-root .prv-fp-subtitle { font-size: .78rem; color: rgba(255,255,255,.82); margin-top: 4px; line-height: 1.4; }
    .prv-fp-modal-root .prv-fp-close {
        background: rgba(255,255,255,.14); border: none; color: #fff;
        width: 34px; height: 34px; border-radius: 50%; font-size: 1.25rem;
        line-height: 1; cursor: pointer; flex-shrink: 0; transition: background .2s;
    }
    .prv-fp-modal-root .prv-fp-close:hover { background: rgba(255,255,255,.28); }

    .prv-fp-modal-root .prv-fp-meta {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px;
        padding: 14px 22px 0; flex-shrink: 0;
    }
    @media (max-width: 575.98px) { .prv-fp-modal-root .prv-fp-meta { grid-template-columns: 1fr; } }
    .prv-fp-modal-root .prv-fp-meta-card {
        background: #fff; border: 1px solid #dde5f3; border-radius: 8px;
        padding: 10px 12px; min-width: 0;
    }
    .prv-fp-modal-root .prv-fp-meta-label {
        font-size: .68rem; font-weight: 600; color: #5a7299;
        text-transform: uppercase; letter-spacing: .35px; margin-bottom: 2px;
    }
    .prv-fp-modal-root .prv-fp-meta-value {
        font-size: .86rem; font-weight: 600; color: #1a2a4a;
        word-break: break-word; line-height: 1.35;
    }

    .prv-fp-modal-root .prv-fp-body { overflow-y: auto; padding: 14px 22px 18px; flex: 1; }
    .prv-fp-modal-root .prv-fp-section {
        background: #fff; border: 1px solid #e3e8f0; border-radius: 10px;
        margin-bottom: 12px; overflow: hidden;
    }
    .prv-fp-modal-root .prv-fp-section-hd {
        background: #eef3fb; border-bottom: 1px solid #dde5f3;
        padding: 9px 14px; display: flex; align-items: flex-start; gap: 10px;
    }
    .prv-fp-modal-root .prv-fp-section-num {
        width: 24px; height: 24px; border-radius: 50%; background: #035ab3; color: #fff;
        font-size: .72rem; font-weight: 700; display: inline-flex; align-items: center;
        justify-content: center; flex-shrink: 0; margin-top: 1px;
    }
    .prv-fp-modal-root .prv-fp-section-title { font-size: .84rem; font-weight: 600; color: #1a2a4a; line-height: 1.35; }
    .prv-fp-modal-root .prv-fp-section-tamil { font-size: .74rem; color: #5a7299; margin-top: 2px; line-height: 1.35; }
    .prv-fp-modal-root .prv-fp-section-body { padding: 14px; }

    .prv-fp-modal-root .prv-fp-field { margin-bottom: 10px; }
    .prv-fp-modal-root .prv-fp-field:last-child { margin-bottom: 0; }
    .prv-fp-modal-root .prv-fp-label {
        font-size: .7rem; font-weight: 600; color: #5a7299;
        text-transform: uppercase; letter-spacing: .35px; margin-bottom: 3px;
    }
    .prv-fp-modal-root .prv-fp-value {
        font-size: .88rem; color: #1a2a4a; font-weight: 500;
        padding: 7px 10px; background: #f8fafd; border: 1px solid #e3e8f0;
        border-radius: 6px; min-height: 34px; word-break: break-word;
    }
    .prv-fp-modal-root .prv-fp-value.prv-fp-empty { color: #9aa8bf; font-style: italic; font-weight: 400; }

    /* Personal & contact — compact grid layout */
    .prv-fp-modal-root .prv-fp-personal-layout {
        display: grid;
        grid-template-columns: minmax(120px, 148px) minmax(0, 1fr);
        gap: 16px;
        align-items: start;
    }
    @media (max-width: 575.98px) {
        .prv-fp-modal-root .prv-fp-personal-layout { grid-template-columns: 1fr; }
    }
    .prv-fp-modal-root .prv-fp-media-col {
        display: flex; flex-direction: column; gap: 12px;
        padding: 10px; background: #f8fafd; border: 1px solid #e3e8f0; border-radius: 8px;
    }
    .prv-fp-modal-root .prv-fp-media-label {
        font-size: .66rem; font-weight: 700; color: #5a7299;
        text-transform: uppercase; letter-spacing: .35px; margin-bottom: 4px; text-align: center;
    }
    .prv-fp-modal-root .prv-fp-media-col .prv-fp-thumb { width: 100%; }
    .prv-fp-modal-root .prv-fp-media-col .prv-fp-thumb img,
    .prv-fp-modal-root .prv-fp-media-col .prv-fp-no-img { width: 100% !important; max-width: 120px; margin: 0 auto; }
    .prv-fp-modal-root .prv-fp-media-col .prv-fp-thumb--photo img,
    .prv-fp-modal-root .prv-fp-media-col .prv-fp-thumb--photo .prv-fp-no-img { height: 120px !important; }
    .prv-fp-modal-root .prv-fp-media-col .prv-fp-thumb--sign img,
    .prv-fp-modal-root .prv-fp-media-col .prv-fp-thumb--sign .prv-fp-no-img { height: 52px !important; max-width: 128px !important; }
    .prv-fp-modal-root .prv-fp-media-col .prv-fp-thumb span { display: none; }

    .prv-fp-modal-root .prv-fp-details-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px 14px;
        min-width: 0;
    }
    @media (max-width: 767.98px) {
        .prv-fp-modal-root .prv-fp-details-grid { grid-template-columns: 1fr; }
    }
    .prv-fp-modal-root .prv-fp-detail-item { min-width: 0; }
    .prv-fp-modal-root .prv-fp-detail-item--full { grid-column: 1 / -1; }
    .prv-fp-modal-root .prv-fp-detail-item .prv-fp-field { margin-bottom: 0; }

    .prv-fp-modal-root .prv-fp-identity-row {
        display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-start; margin-bottom: 14px;
    }
    .prv-fp-modal-root .prv-fp-thumb { text-align: center; flex-shrink: 0; }
    .prv-fp-modal-root .prv-fp-thumb img {
        display: block; border: 2px solid #dde5f3; border-radius: 8px; background: #f0f4f9;
    }
    .prv-fp-modal-root .prv-fp-thumb--photo img { width: 88px; height: 106px; object-fit: cover; }
    .prv-fp-modal-root .prv-fp-thumb--sign img { width: 150px; height: 56px; object-fit: contain; }
    .prv-fp-modal-root .prv-fp-no-img {
        background: #f0f4f9; border: 2px dashed #ccd5e3; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        color: #9aa8bf; font-size: .68rem; text-align: center; padding: 6px;
    }
    .prv-fp-modal-root .prv-fp-thumb span { font-size: .68rem; color: #5a7299; margin-top: 4px; display: block; }

    .prv-fp-modal-root .prv-fp-subhead {
        font-size: .78rem; font-weight: 600; color: #1a3a72; margin: 0 0 8px;
        padding-bottom: 4px; border-bottom: 1px dashed #dde5f3;
    }
    .prv-fp-modal-root .prv-fp-table-wrap { overflow-x: auto; border: 1px solid #e3e8f0; border-radius: 8px; margin-bottom: 12px; }
    .prv-fp-modal-root .prv-fp-table { width: 100%; font-size: .76rem; border-collapse: collapse; margin: 0; min-width: 520px; }
    .prv-fp-modal-root .prv-fp-table th {
        background: #eef3fb; color: #1a2a4a; font-weight: 600;
        padding: .4rem .45rem; border: 1px solid #dde5f3; font-size: .7rem;
        white-space: nowrap; text-align: center; vertical-align: middle;
    }
    .prv-fp-modal-root .prv-fp-table td {
        padding: .4rem .45rem; border: 1px solid #e8edf6; vertical-align: middle;
        color: #2c3e5e; text-align: center;
    }
    .prv-fp-modal-root .prv-fp-table td.prv-fp-td-left { text-align: left; white-space: pre-line; }
    .prv-fp-modal-root .prv-fp-table tr:nth-child(even) td { background: #f8fafd; }

    .prv-fp-modal-root .prv-fp-doc-pill {
        display: inline-flex; align-items: center; gap: 4px;
        background: #e8f2ff; color: #035ab3; border-radius: 999px;
        padding: 3px 10px; font-size: .72rem; font-weight: 600; text-decoration: none;
        max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .prv-fp-modal-root .prv-fp-doc-pill:hover { background: #d6e8ff; text-decoration: none; color: #024a98; }
    .prv-fp-modal-root .prv-fp-doc-empty { color: #9aa8bf; font-size: .75rem; }

    .prv-fp-modal-root .prv-fp-yesno-yes {
        background: #d4edda; color: #155724; border-radius: 4px;
        padding: 2px 9px; font-size: .72rem; font-weight: 600;
    }
    .prv-fp-modal-root .prv-fp-yesno-no {
        background: #f8d7da; color: #721c24; border-radius: 4px;
        padding: 2px 9px; font-size: .72rem; font-weight: 600;
    }

    .prv-fp-modal-root .prv-fp-footer {
        background: #fff; border-top: 1px solid #e3e8f0; padding: 14px 22px;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        flex-shrink: 0; flex-wrap: wrap;
    }
    .prv-fp-modal-root .prv-fp-btn-back {
        background: #fff; color: #035ab3; border: 1px solid #035ab3; border-radius: 8px;
        padding: 8px 18px; font-size: .84rem; font-weight: 600; cursor: pointer; white-space: nowrap;
    }
    .prv-fp-modal-root .prv-fp-btn-back:hover { background: #eef3fb; }
    .prv-fp-modal-root .prv-fp-btn-print {
        background: #fff; color: #4f5f79; border: 1px solid #99a7c0; border-radius: 8px;
        padding: 8px 18px; font-size: .84rem; font-weight: 600; cursor: pointer; white-space: nowrap;
    }
    .prv-fp-modal-root .prv-fp-btn-print:hover { background: #f3f6fb; }
    .prv-fp-modal-root .prv-fp-btn-go {
        background: linear-gradient(135deg, #1a9e4f, #14813f); color: #fff; border: none;
        border-radius: 8px; padding: 8px 20px; font-size: .84rem; font-weight: 600;
        cursor: pointer; white-space: nowrap;
    }
    .prv-fp-modal-root .prv-fp-btn-go:disabled { opacity: .45; cursor: not-allowed; }
    .prv-fp-modal-root .prv-fp-btn-go:not(:disabled):hover { opacity: .92; }

    .prv-fp-modal-root .prv-fp-print-head { display: none; }

    @page { size: A4 portrait; margin: 8mm 10mm; }
    @media print {
        html, body {
            height: auto !important;
            overflow: visible !important;
            background: #fff !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        html.prv-fp-print-active #prvFpPrintRoot {
            display: block !important;
            position: static !important;
            width: 100% !important;
            max-width: none !important;
            height: auto !important;
            overflow: visible !important;
            background: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-panel {
            display: block !important;
            max-height: none !important;
            width: 100% !important;
            box-shadow: none !important;
            border-radius: 0 !important;
            background: #fff !important;
            overflow: visible !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-header,
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-footer,
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-close,
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-subtitle {
            display: none !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-print-head {
            display: block !important;
            text-align: center;
            padding: 0 0 6px;
            margin-bottom: 6px;
            border-bottom: 2px solid #1f3a63;
            page-break-after: avoid;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-print-head-org { font-size: 8.5pt; font-weight: 700; text-transform: uppercase; color: #444; }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-print-head-title { font-size: 12pt; font-weight: 800; color: #1f3a63; margin-top: 2px; }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-print-head-tag { font-size: 7.5pt; color: #666; margin-top: 2px; }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-meta {
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            gap: 6px !important;
            padding: 0 0 6px !important;
            page-break-after: avoid;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-meta-card {
            border: 1px solid #bbb !important;
            padding: 4px 6px !important;
            background: #fff !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-meta-label { font-size: 6.5pt !important; margin-bottom: 0 !important; }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-meta-value { font-size: 8.5pt !important; }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-body {
            overflow: visible !important;
            padding: 0 !important;
            max-height: none !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-section {
            border: 1px solid #aaa !important;
            box-shadow: none !important;
            page-break-inside: auto !important;
            margin-bottom: 5px !important;
            border-radius: 0 !important;
            background: #fff !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-section-hd {
            background: #eee !important;
            padding: 4px 8px !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-section-tamil { display: none !important; }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-section-title { font-size: 9pt !important; }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-section-num { width: 18px !important; height: 18px !important; font-size: 8pt !important; }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-section-body { padding: 6px 8px !important; }

        /* Personal block — photo left, fields fill width in 3 columns */
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-personal-layout {
            display: grid !important;
            grid-template-columns: 88px minmax(0, 1fr) !important;
            gap: 8px !important;
            align-items: start !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-media-col {
            display: flex !important;
            flex-direction: column !important;
            gap: 6px !important;
            padding: 4px !important;
            background: transparent !important;
            border: 1px solid #bbb !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-media-label { font-size: 6pt !important; margin-bottom: 2px !important; }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-media-col .prv-fp-thumb--photo img,
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-media-col .prv-fp-thumb--photo .prv-fp-no-img {
            width: 72px !important;
            height: 86px !important;
            max-width: 72px !important;
            margin: 0 auto !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-media-col .prv-fp-thumb--sign img,
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-media-col .prv-fp-thumb--sign .prv-fp-no-img {
            width: 72px !important;
            height: 34px !important;
            max-width: 72px !important;
            margin: 0 auto !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-details-grid {
            display: grid !important;
            grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            gap: 5px 8px !important;
            width: 100% !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-detail-item--full {
            grid-column: 1 / -1 !important;
        }

        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-label {
            font-size: 6.5pt !important;
            margin-bottom: 1px !important;
            color: #333 !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-value {
            font-size: 8.5pt !important;
            padding: 3px 5px !important;
            min-height: 0 !important;
            line-height: 1.25 !important;
            background: transparent !important;
            border: 1px solid #bbb !important;
            color: #111 !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-subhead {
            font-size: 7.5pt !important;
            margin: 4px 0 3px !important;
            padding-bottom: 2px !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-table-wrap {
            overflow: visible !important;
            margin-bottom: 6px !important;
            page-break-inside: auto;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-table {
            font-size: 7.5pt !important;
            min-width: 0 !important;
            width: 100% !important;
            table-layout: fixed !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-table th,
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-table td {
            border: 1px solid #bbb !important;
            background: transparent !important;
            padding: 2px 3px !important;
            color: #111 !important;
            word-break: break-word !important;
            white-space: normal !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-doc-pill {
            background: transparent !important;
            border: 0 !important;
            padding: 0 !important;
            color: #111 !important;
            max-width: none !important;
            white-space: normal !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root img {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        /* Identity docs — 2-column grid (target by stable hook, not :last-child) */
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-section--identity .row {
            display: grid !important;
            grid-template-columns: 1fr 1fr !important;
            gap: 5px 8px !important;
            margin: 0 !important;
        }
        html.prv-fp-print-active .prv-fp-modal-root .prv-fp-section--identity .col-12 {
            width: auto !important;
            max-width: none !important;
            flex: none !important;
            padding: 0 !important;
        }
    }
</style>

<div id="appPreviewModalFormP" class="prv-fp-overlay prv-fp-modal-root" role="dialog" aria-modal="true" aria-labelledby="prvFpTitle" aria-hidden="true">
    <div class="prv-fp-panel">
        <div class="prv-fp-header">
            <div class="prv-fp-header-main">
                <h2 class="prv-fp-title" id="prvFpTitle">
                    <i class="fa fa-file-text-o"></i>
                    Application Preview
                    <span class="prv-fp-badge">FORM P</span>
                    <span class="prv-fp-badge prv-fp-badge--renew" id="prvFpRenewBadge" style="{{ $formPPreviewIsRenewal ? '' : 'display:none;' }}">Renewal</span>
                </h2>
                <div class="prv-fp-subtitle">Review every section carefully before proceeding to payment. Use <strong>Back to Edit</strong> if anything needs correction.</div>
            </div>
            <button type="button" class="prv-fp-close" id="prvFpCloseBtn" title="Close preview" aria-label="Close preview">&times;</button>
        </div>

        <div class="prv-fp-print-head" aria-hidden="true">
            <div class="prv-fp-print-head-org">Tamil Nadu Electrical Licensing Board</div>
            <div class="prv-fp-print-head-title">Form P — Application Preview</div>
            <div class="prv-fp-print-head-tag" id="prvFpPrintTag">Competency Certificate</div>
        </div>

        <div class="prv-fp-meta">
            <div class="prv-fp-meta-card">
                <div class="prv-fp-meta-label">Applicant</div>
                <div class="prv-fp-meta-value" id="prvFpMetaName">—</div>
            </div>
            <div class="prv-fp-meta-card">
                <div class="prv-fp-meta-label">Application ID</div>
                <div class="prv-fp-meta-value" id="prvFpMetaAppId">—</div>
            </div>
            <div class="prv-fp-meta-card">
                <div class="prv-fp-meta-label">Licence / Certificate</div>
                <div class="prv-fp-meta-value" id="prvFpMetaLicence">—</div>
            </div>
        </div>

        <div class="prv-fp-body" id="prvFpBody">
            {{-- Section 1–5 Personal --}}
            <div class="prv-fp-section">
                <div class="prv-fp-section-hd">
                    <span class="prv-fp-section-num">1</span>
                    <div>
                        <div class="prv-fp-section-title">Personal &amp; Contact Details</div>
                        <div class="prv-fp-section-tamil">விண்ணப்பதாரர் தனிப்பட்ட மற்றும் தொடர்பு விவரங்கள்</div>
                    </div>
                </div>
                <div class="prv-fp-section-body">
                    <div class="prv-fp-personal-layout">
                        <div class="prv-fp-media-col">
                            <div>
                                <div class="prv-fp-thumb prv-fp-thumb--photo">
                                    <div id="prvFpPhotoWrap"><div class="prv-fp-no-img" style="width:100%;height:120px;">No Photo</div></div>
                                </div>
                            </div>
                            <div>
                                <div class="prv-fp-media-label">Signature</div>
                                <div class="prv-fp-thumb prv-fp-thumb--sign">
                                    <div id="prvFpSignWrap"><div class="prv-fp-no-img" style="width:100%;height:52px;">No Signature</div></div>
                                </div>
                            </div>
                        </div>
                        <div class="prv-fp-details-grid">
                            <div class="prv-fp-detail-item">
                                <div class="prv-fp-field"><div class="prv-fp-label">Applicant's Name</div><div class="prv-fp-value" id="prvFpName">—</div></div>
                            </div>
                            <div class="prv-fp-detail-item">
                                <div class="prv-fp-field"><div class="prv-fp-label">Father's Name</div><div class="prv-fp-value" id="prvFpFather">—</div></div>
                            </div>
                            <div class="prv-fp-detail-item">
                                <div class="prv-fp-field"><div class="prv-fp-label">Email ID</div><div class="prv-fp-value" id="prvFpEmail">—</div></div>
                            </div>
                            <div class="prv-fp-detail-item">
                                <div class="prv-fp-field"><div class="prv-fp-label">Date of Birth</div><div class="prv-fp-value" id="prvFpDob">—</div></div>
                            </div>
                            <div class="prv-fp-detail-item">
                                <div class="prv-fp-field"><div class="prv-fp-label">Age</div><div class="prv-fp-value" id="prvFpAge">—</div></div>
                            </div>
                            <div class="prv-fp-detail-item prv-fp-detail-item--full">
                                <div class="prv-fp-field"><div class="prv-fp-label">Address</div><div class="prv-fp-value" id="prvFpAddress" style="white-space:pre-line;">—</div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 6 Technical --}}
            <div class="prv-fp-section">
                <div class="prv-fp-section-hd">
                    <span class="prv-fp-section-num">6</span>
                    <div>
                        <div class="prv-fp-section-title">Technical Qualifications &amp; Experience</div>
                        <div class="prv-fp-section-tamil">தொழில்நுட்ப தகுதி, பயிற்சி மற்றும் அனுபவ விவரங்கள்</div>
                    </div>
                </div>
                <div class="prv-fp-section-body">
                    <div class="prv-fp-subhead">(i) Educational Qualification</div>
                    <div class="prv-fp-table-wrap">
                        <table class="prv-fp-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Education Level</th>
                                    <th>Institution</th>
                                    <th>Month</th>
                                    <th>Year</th>
                                    <th>Certificate No.</th>
                                    <th>Document</th>
                                </tr>
                            </thead>
                            <tbody id="prvFpEduBody"><tr><td colspan="7" class="text-muted py-3">—</td></tr></tbody>
                        </table>
                    </div>

                    <div class="prv-fp-subhead">(ii) Training Institute</div>
                    <div class="prv-fp-table-wrap">
                        <table class="prv-fp-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Institute Name &amp; Address</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Duration (yrs)</th>
                                    <th>Document</th>
                                </tr>
                            </thead>
                            <tbody id="prvFpInstBody"><tr><td colspan="6" class="text-muted py-3">—</td></tr></tbody>
                        </table>
                    </div>

                    <div class="prv-fp-subhead">(iii) Power Station Experience</div>
                    <div class="prv-fp-table-wrap">
                        <table class="prv-fp-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Power Station</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Total Yrs</th>
                                    <th>Designation</th>
                                    <th>Document</th>
                                </tr>
                            </thead>
                            <tbody id="prvFpWorkBody"><tr><td colspan="7" class="text-muted py-3">—</td></tr></tbody>
                        </table>
                    </div>

                    <div class="prv-fp-field mb-0">
                        <div class="prv-fp-label">(iv) Name of the Employer</div>
                        <div class="prv-fp-value" id="prvFpEmployer" style="white-space:pre-line;">—</div>
                    </div>
                </div>
            </div>

            {{-- Section 7 Previous application --}}
            <div class="prv-fp-section">
                <div class="prv-fp-section-hd">
                    <span class="prv-fp-section-num">7</span>
                    <div>
                        <div class="prv-fp-section-title">Previous Application</div>
                        <div class="prv-fp-section-tamil">முந்தைய விண்ணப்பம் பற்றிய விவரம்</div>
                    </div>
                </div>
                <div class="prv-fp-section-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span style="font-size:.78rem;color:#5a7299;font-weight:600;">Applied Previously:</span>
                        <span id="prvFpPrevYn">—</span>
                    </div>
                    <div id="prvFpPrevBlock" style="display:none;">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6"><div class="prv-fp-field mb-0"><div class="prv-fp-label">Application Number</div><div class="prv-fp-value" id="prvFpPrevNo">—</div></div></div>
                            <div class="col-12 col-sm-6"><div class="prv-fp-field mb-0"><div class="prv-fp-label">Date</div><div class="prv-fp-value" id="prvFpPrevDate">—</div></div></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Section 8 Identity --}}
            <div class="prv-fp-section prv-fp-section--identity" data-section="identity">
                <div class="prv-fp-section-hd">
                    <span class="prv-fp-section-num">8</span>
                    <div>
                        <div class="prv-fp-section-title">Identity Documents</div>
                        <div class="prv-fp-section-tamil">அடையாள ஆவண விவரங்கள்</div>
                    </div>
                </div>
                <div class="prv-fp-section-body">
                    <div class="row g-2">
                        <div class="col-12 col-sm-5"><div class="prv-fp-field mb-0"><div class="prv-fp-label">Aadhaar Number</div><div class="prv-fp-value" id="prvFpAadhaar">—</div></div></div>
                        <div class="col-12 col-sm-7"><div class="prv-fp-field mb-0"><div class="prv-fp-label">Aadhaar Document</div><div class="prv-fp-value" id="prvFpAadhaarDoc">—</div></div></div>
                        <div class="col-12 col-sm-5"><div class="prv-fp-field mb-0"><div class="prv-fp-label">PAN Number</div><div class="prv-fp-value" id="prvFpPan">—</div></div></div>
                        <div class="col-12 col-sm-7"><div class="prv-fp-field mb-0"><div class="prv-fp-label">PAN Document</div><div class="prv-fp-value" id="prvFpPanDoc">—</div></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="prv-fp-footer">
            <button type="button" class="prv-fp-btn-back" id="prvFpBackBtn"><i class="fa fa-arrow-left"></i> Back to Edit</button>
            <button type="button" class="prv-fp-btn-print" id="prvFpPrintBtn" title="Print preview"><i class="fa fa-print"></i> Print</button>
            <button type="button" class="prv-fp-btn-go" id="prvFpConfirmBtn"><i class="fa fa-check"></i> Confirm &amp; Proceed</button>
        </div>
    </div>
</div>
