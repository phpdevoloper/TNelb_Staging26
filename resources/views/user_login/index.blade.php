@include('include.header')

<style>
    td {
        font-size: 15px;
    }

    .custom-fieldset {
        border: 1px solid #34495e;
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1.5rem;
        position: relative;
    }

    .custom-legend {
        font-weight: bold;
        font-size: 1rem;
        padding: 0 10px;
        color: #333;
        display: inline-block;
    }

    /* basic positioning */
    .legend {
        list-style: none;
        padding: 0;
        margin: 0 0 1rem 0;
        display: flex;
        justify-content: flex-end;
        gap: 20px;
        flex-wrap: wrap;
        /* wrap on smaller screens */
    }

    .legend li {
        display: flex;
        align-items: center;
        /* align box + text vertically */
        font-size: 14px;
    }

    /* color box */
    .legend span {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 1px solid #ccc;
        margin-right: 6px;
        /* spacing between box and text */
        border-radius: 2px;
        /* optional, softer look */
    }

    .pagination .page-link {
    cursor: pointer;
    }
    .pagination .active .page-link {
        background-color: #007bff;
        border-color: #007bff;
    }

    /* RETURN ribbon: clip to cell so it cannot draw into the row above; centered + shallower angle */
    .return-cell {
        position: relative;
        overflow: hidden;
        text-align: center;
        vertical-align: middle;
        min-width: 0;
        padding: 1.35rem 2px 0.4rem;
    }

    .return-ribbon-wrapper {
        position: absolute;
        top: 0.62rem;
        left: 50%;
        transform: translateX(-50%);
        z-index: 1;
        pointer-events: none;
    }

    .return-ribbon {
        display: block;
        width: 76px;
        padding: 3px 0;
        margin: 0 auto;
        background: #6f42c1;
        color: #fff;
        font-size: 6.5px;
        font-weight: 800;
        letter-spacing: 0.04em;
        line-height: 1.15;
        text-align: center;
        transform: rotate(-32deg);
        transform-origin: center center;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
        text-transform: uppercase;
        white-space: nowrap;
    }

    .return-sno-num {
        position: relative;
        z-index: 1;
        display: block;
        margin-top: 0.4rem;
        font-weight: 600;
        line-height: 1.2;
    }

    /* Toolbar row: page length (left) + search (right), table-header styling */
    .applications-table-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }
    .applications-page-length-toolbar {
        margin-bottom: 0;
    }
    .applications-search-toolbar {
        margin-left: auto;
    }
    .applications-search-inner {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.35rem 0.5rem;
        padding: 4px 8px;
        line-height: 1.2;
        background: var(--primary-color-login, #34495e);
        color: #fff;
        border: 1px solid #c5bebe;
        border-radius: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
    }
    .applications-search-input {
        min-width: 11rem;
        width: 14rem;
        max-width: min(22rem, 56vw);
        padding: 2px 6px !important;
        font-size: 0.72rem !important;
        font-weight: 500 !important;
        line-height: 1.25 !important;
        color: #1e293b !important;
        border: 1px solid #c5bebe !important;
        border-radius: 2px !important;
        background: #fff !important;
    }
    .applications-search-input::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }
    .applications-search-input:focus {
        border-color: #ecf0f1 !important;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.45) !important;
        outline: none;
    }

    /* Page length — same visual language as .table-login thead (#34495e, white text) */
    .applications-page-length-inner {
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.35rem 0.5rem;
        padding: 4px 8px;
        line-height: 1.2;
        background: var(--primary-color-login, #34495e);
        color: #fff;
        border: 1px solid #c5bebe;
        border-radius: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
    }
    .applications-page-length-label {
        margin: 0;
        font-size: 0.68rem;
        font-weight: 600;
        color: #fff;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }
    .applications-page-length-select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        min-width: 2.75rem !important;
        width: auto !important;
        max-width: 3.5rem;
        height: auto !important;
        min-height: 0 !important;
        padding: 2px 1.2rem 2px 5px !important;
        font-size: 0.72rem !important;
        font-weight: 600 !important;
        line-height: 1.2 !important;
        color: #1e293b !important;
        border: 1px solid #c5bebe !important;
        border-radius: 2px !important;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2334495e' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 0.25rem center;
        background-size: 0.55rem;
        cursor: pointer;
    }
    .applications-page-length-select:hover {
        border-color: #ecf0f1 !important;
        background-color: #f8fafc !important;
    }
    .applications-page-length-select:focus {
        border-color: #ecf0f1 !important;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.45) !important;
        outline: none;
    }
    .applications-page-length-hint {
        font-size: 0.68rem;
        color: rgba(255, 255, 255, 0.88);
        font-weight: 500;
        letter-spacing: 0.02em;
    }

    /* Contractor pagination/info: align with competency style */
    #contractorTableInfo {
        color: #6c757d;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }
    #contractorTablePagination {
        margin-top: 0.6rem;
    }
    #contractorTablePagination .pagination {
        justify-content: flex-end !important;
        margin-bottom: 0;
    }
    #contractorTablePagination .page-link {
        font-size: 0.875rem;
        padding: 0.375rem 0.72rem;
    }

    .dash-tl-overlay {
        position: fixed;
        inset: 0;
        z-index: 10050;
        background: rgba(10, 24, 48, 0.55);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 0.85rem;
        backdrop-filter: blur(2px);
    }
    .dash-tl-overlay.is-open {
        display: flex;
    }
    .dash-tl-panel {
        background: #eef3f9;
        width: min(52rem, 96vw);
        max-height: min(90vh, 46rem);
        display: flex;
        flex-direction: column;
        border-radius: 0.85rem;
        overflow: hidden;
        box-shadow: 0 1.1rem 2.8rem rgba(3, 90, 179, 0.22);
    }
    .dash-tl-header {
        background: linear-gradient(135deg, #035ab3 0%, #0472d9 100%);
        padding: 0.8rem 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-shrink: 0;
    }
    .dash-tl-title {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.3;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }
    .dash-tl-subtitle {
        margin: 0.15rem 0 0;
        font-size: 0.76rem;
        color: rgba(255, 255, 255, 0.88);
        word-break: break-word;
    }
    .dash-tl-close {
        background: rgba(255, 255, 255, 0.14);
        border: none;
        color: #fff;
        width: 2.15rem;
        height: 2.15rem;
        border-radius: 50%;
        font-size: 1.3rem;
        line-height: 1;
        cursor: pointer;
        flex-shrink: 0;
    }
    .dash-tl-close:hover,
    .dash-tl-close:focus-visible {
        background: rgba(255, 255, 255, 0.28);
        outline: 2px solid #fff;
        outline-offset: 2px;
    }
    .dash-tl-body {
        position: relative;
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 1rem 1.1rem 1.2rem;
        background: #eef3f9;
    }
    .dash-tl-loading {
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 0.5rem;
        min-height: 10rem;
        color: #035ab3;
        font-weight: 600;
        font-size: 0.88rem;
    }
    .dash-tl-loading.is-visible {
        display: flex;
    }
    body.dash-tl-open {
        overflow: hidden;
    }

    /* ------------------------------------------------------------------
       Active / Present License Details — compact tabbed list
       One slim row per licence; colour per certificate code, thin bar = validity used.
       ------------------------------------------------------------------ */
    .lic-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        padding: 0.85rem 1rem 1rem;
    }

    .lic-panel__head {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 0.6rem 1rem;
        margin-bottom: 0.75rem;
    }

    .lic-panel__title {
        margin: 0;
        font-size: 1.05rem;
        color: #1e293b;
    }

    .lic-tabs {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 3px;
        background: #f1f5f9;
        border-radius: 8px;
    }

    .lic-tab {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 0;
        background: transparent;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
    }

    .lic-tab:hover {
        color: #1e293b;
    }

    .lic-tab.is-active {
        background: #fff;
        color: #1e3a8a;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.12);
    }

    .lic-tab:focus-visible {
        outline: 2px solid #2563eb;
        outline-offset: 2px;
    }

    .lic-tab__count {
        min-width: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: #e2e8f0;
        color: #334155;
        font-size: 0.7rem;
        line-height: 18px;
        text-align: center;
    }

    .lic-tab.is-active .lic-tab__count {
        background: #1e3a8a;
        color: #fff;
    }

    .lic-pane {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(270px, 100%), 1fr));
        gap: 0.75rem;
    }

    .lic-pane[hidden] {
        display: none;
    }

    /* Credit-card style licence card. Gradient per certificate code
       (C = Supervisor, B = Wireman, H = Wireman Helper, P = Power Generating, EA = Contractor Grade A) */
    .lic-card {
        --lic-from: #1e3a8a;
        --lic-mid: #1e40af;
        --lic-to: #2563eb;
        position: relative;
        isolation: isolate;
        display: flex;
        flex-direction: column;
        gap: 0.55rem;
        min-height: 150px;
        padding: 0.8rem 0.95rem 0.95rem;
        border-radius: 14px;
        color: #fff;
        overflow: hidden;
        background: linear-gradient(135deg, var(--lic-from) 0%, var(--lic-mid) 55%, var(--lic-to) 100%);
        box-shadow: 0 6px 16px -6px rgba(15, 23, 42, 0.35);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .lic-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 22px -8px rgba(15, 23, 42, 0.45);
    }

    /* Decorative rings, like a bank card */
    .lic-card::before,
    .lic-card::after {
        content: '';
        position: absolute;
        z-index: -1;
        border-radius: 50%;
        pointer-events: none;
    }

    .lic-card::before {
        width: 180px;
        height: 180px;
        top: -80px;
        right: -60px;
        background: rgba(255, 255, 255, 0.1);
    }

    .lic-card::after {
        width: 140px;
        height: 140px;
        right: 10px;
        bottom: -90px;
        background: rgba(255, 255, 255, 0.07);
    }

    .lic-card--c  { --lic-from: #1e3a8a; --lic-mid: #1e40af; --lic-to: #2563eb; }
    .lic-card--b  { --lic-from: #4c1d95; --lic-mid: #6d28d9; --lic-to: #7c3aed; }
    .lic-card--h  { --lic-from: #78350f; --lic-mid: #92400e; --lic-to: #b45309; }
    .lic-card--p  { --lic-from: #701a75; --lic-mid: #a21caf; --lic-to: #c026d3; }
    .lic-card--ea { --lic-from: #134e4a; --lic-mid: #0f766e; --lic-to: #0d9488; }

    /* Expired stays red whatever the certificate code, so keep this after the code colours */
    .lic-card.is-expired { --lic-from: #7f1d1d; --lic-mid: #991b1b; --lic-to: #b91c1c; }

    .lic-card__top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
    }

    .lic-card__name {
        font-size: 0.8rem;
        font-weight: 600;
        line-height: 1.3;
        color: rgba(255, 255, 255, 0.95);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .lic-card__status {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 2px 8px;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.3);
        font-size: 0.6rem;
        font-weight: 700;
        line-height: 1.4;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
    }

    .lic-card__status::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #4ade80;
        box-shadow: 0 0 5px rgba(74, 222, 128, 0.7);
    }

    .lic-card.is-expiring .lic-card__status::before {
        background: #fbbf24;
        box-shadow: 0 0 5px rgba(251, 191, 36, 0.7);
    }

    .lic-card.is-expired .lic-card__status::before {
        background: #fca5a5;
        box-shadow: none;
    }

    .lic-card__middle {
        display: flex;
        align-items: center;
        gap: 0.65rem;
    }

    /* EMV-style chip in the same white as the certificate number; inner lines take the card colour */
    .lic-card__chip {
        position: relative;
        flex-shrink: 0;
        width: 34px;
        height: 25px;
        border-radius: 5px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.25);
    }

    .lic-card__chip::before {
        content: '';
        position: absolute;
        inset: 6px 4px;
        border: 1px solid var(--lic-mid);
        border-radius: 3px;
        opacity: 0.45;
    }

    .lic-card__number {
        font-family: 'Courier New', Consolas, ui-monospace, monospace;
        font-size: 1.02rem;
        font-weight: 700;
        letter-spacing: 0.1em;
        word-break: break-all;
        /* Gold gradient text: color cannot take a gradient, so paint it as a background clipped to the letters */
        color: #fcd34d;
        background: linear-gradient(135deg, #fde68a 0%, #f59e0b 60%, #fcd34d 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.3));
    }

    .lic-card__flag {
        display: flex;
        align-items: flex-start;
        gap: 0.4rem;
        padding: 0.3rem 0.5rem;
        border-left: 2px solid rgba(255, 255, 255, 0.65);
        border-radius: 6px;
        background: rgba(255, 255, 255, 0.14);
        font-size: 0.68rem;
        line-height: 1.35;
    }

    .lic-card__flag i {
        margin-top: 2px;
    }

    .lic-card__bottom {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 0.5rem;
        margin-top: auto;
    }

    .lic-card__dates {
        display: flex;
        gap: 0.9rem;
    }

    .lic-card__date {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .lic-card__label {
        margin-bottom: 1px;
        font-size: 0.52rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: rgba(255, 255, 255, 0.65);
    }

    .lic-card__value {
        font-size: 0.74rem;
        font-weight: 600;
        letter-spacing: 0.01em;
    }

    .lic-card__end {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        line-height: 1.2;
    }

    .lic-card__left {
        font-size: 0.62rem;
        color: rgba(255, 255, 255, 0.8);
        white-space: nowrap;
    }

    .lic-card__brand {
        font-size: 0.8rem;
        font-weight: 800;
        font-style: italic;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: rgba(255, 255, 255, 0.92);
        white-space: nowrap;
    }

    .lic-card__bar {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 3px;
        background: rgba(255, 255, 255, 0.18);
    }

    .lic-card__bar span {
        display: block;
        height: 100%;
        background: rgba(255, 255, 255, 0.75);
    }

    .lic-empty {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        padding: 0.6rem 0.75rem;
        background: #f8fafc;
        border-radius: 8px;
        color: #64748b;
        font-size: 0.82rem;
    }

    #infoModal.notice-alert-modal {
        --color-primary: #1d4ed8;
        --color-primary-hover: #1e40af;
        --color-accent: #d97706;
        --color-background: #ffffff;
        --color-surface: #fffbeb;
        --color-text-main: #0f172a;
        --color-text-muted: #64748b;
        --color-border: #e2e8f0;
        --radius-md: 0.5rem;
        --radius-lg: 0.75rem;
        --transition-smooth: all 0.2s ease-in-out;
        z-index: 10060;
    }

    .modal-backdrop.notice-alert-backdrop {
        z-index: 10050;
    }

    #infoModal.notice-alert-modal .modal-dialog {
        max-width: min(28rem, calc(100vw - 2rem));
        margin: 1rem auto;
    }

    #infoModal.notice-alert-modal .modal-content {
        border: 1px solid var(--color-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: 0 1.25rem 2.5rem rgba(15, 23, 42, 0.16);
    }

    #infoModal.notice-alert-modal .modal-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 1.1rem 0.85rem;
        border-bottom: 1px solid #f1f5f9;
        background: var(--color-background);
    }

    #infoModal.notice-alert-modal .notice-alert__heading {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
        flex: 1;
    }

    #infoModal.notice-alert-modal .notice-alert__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        flex-shrink: 0;
        border-radius: var(--radius-md);
        background: linear-gradient(135deg, #1d4ed8, #1d4ed8);
        color: #fff;
        font-size: 1.05rem;
    }

    #infoModal.notice-alert-modal .modal-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--color-text-main);
        letter-spacing: 0.01em;
        line-height: 1.3;
    }

    #infoModal.notice-alert-modal .notice-alert__close {
        margin: 0 0 0 auto;
        padding: 0;
        font-size: 1.75rem;
        font-weight: 400;
        line-height: 1;
        color: #64748b;
        opacity: 0.7;
        background: transparent;
        border: 0;
        transition: var(--transition-smooth);
    }

    #infoModal.notice-alert-modal .notice-alert__close:hover,
    #infoModal.notice-alert-modal .notice-alert__close:focus {
        color: #0f172a;
        opacity: 1;
        text-decoration: none;
    }

    #infoModal.notice-alert-modal .notice-alert__close:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 3px;
    }

    #infoModal.notice-alert-modal .modal-body {
        padding: 0.9rem 1.15rem 0.35rem;
        background: var(--color-background);
        border-left: 0.25rem solid var(--color-accent);
    }

    #infoModal.notice-alert-modal .notice-alert__copy {
        margin: 0;
        padding: 0.85rem 0.95rem;
        background: var(--color-surface);
        border: 1px solid #fde68a;
        border-radius: var(--radius-md);
        color: var(--color-text-main);
        font-size: 0.92rem;
        line-height: 1.6;
    }

    #infoModal.notice-alert-modal .modal-footer {
        border-top: 0;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 0.85rem 1.15rem 1.05rem;
        background: var(--color-background);
    }

    #infoModal.notice-alert-modal .notice-alert__ok {
        min-width: 6.5rem;
        font-weight: 600;
        border-radius: var(--radius-md);
        background: var(--color-primary);
        border-color: var(--color-primary);
        transition: var(--transition-smooth);
    }

    #infoModal.notice-alert-modal .notice-alert__ok:hover,
    #infoModal.notice-alert-modal .notice-alert__ok:focus {
        background: var(--color-primary-hover);
        border-color: var(--color-primary-hover);
    }

    #infoModal.notice-alert-modal .notice-alert__ok:focus-visible {
        outline: 2px solid var(--color-primary);
        outline-offset: 3px;
    }


</style>
<!-- Notice Alert -->
<div class="modal fade notice-alert-modal" id="infoModal" tabindex="-1" role="alertdialog" aria-labelledby="infoModalLabel" aria-describedby="infoModalBody" aria-modal="true" aria-hidden="true" data-backdrop="static" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="notice-alert__heading">
                    <span class="notice-alert__icon" aria-hidden="true"><i class="fa fa-bullhorn"></i></span>
                    <h5 class="modal-title" id="infoModalLabel">Important Notice</h5>
                </div>
                <button type="button" class="close notice-alert__close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="notice-alert__copy" id="infoModalBody">There is any correction in your competency certificate experience details. Please update your valid experience through the Alteration form within <span class="text-danger">30 days</span> of Application Submission.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary notice-alert__ok" data-bs-dismiss="modal" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<section class="dashboard-panel">
    <div class="layout-login">
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                {{-- <aside class="sidebar-login">
                    <nav>
                        <ul>
                            <li><a href="#" class="active">Dashboard</a></li>

                            <li class="has-submenu">
                                <a href="#">Competency Certificates</a>
                                <ul class="submenu">
                                    <li><a href="{{ route('apply-form-s') }}"> <i class="fa fa-arrow-circle-o-right"></i> Apply for License C [Form S]</a></li>
                <li><a href="{{ route('apply-form-w') }}"> <i class="fa fa-arrow-circle-o-right"></i> Apply For License B [Form W]</a></li>
                <li><a href="{{ route('apply-form-wh') }}"> <i class="fa fa-arrow-circle-o-right"></i> Apply For License WH [Form WH]</a></li>
                </ul>
                </li>

                <li class="has-submenu">
                    <a href="#">Contractor License</a>
                    <ul class="submenu">
                        <li><a href="{{ route('apply-form-a') }}"> <i class="fa fa-arrow-circle-o-right"></i> Apply For License EA [Form A]</a></li>

                    </ul>
                </li>


                </ul>
                </nav>
                </aside> --}}

                @include('include.sidebar')

                <main class="main-content-login">
                    <!-- Tasks and Projects Section -->
                    <section class="tasks-projects-login">


                        <!-- Projects -->

                        @php
                        use Carbon\Carbon;
                        $today = Carbon::today();
                        // license_name codes stored in the meta tables, used for per-code row colours
                        $competencyCodes = ['C', 'B', 'H', 'P'];
                        $contractorCodes = ['EA'];
                        $allLicenses = collect($present_license ?? []);
                        $competencyLicenses = $allLicenses->where('license_type', 'C');
                        $contractorLicenses = $allLicenses->where('license_type', 'CL');
                        // Open the contractor tab first only when the user has no competency certificates
                        $licOpenContractor = $competencyLicenses->isEmpty() && $contractorLicenses->isNotEmpty();
                        @endphp

                        <div class="projects-section-login lic-panel" id="licPanel">
                            <div class="lic-panel__head">
                                <h5 class="lic-panel__title"><strong>Active / Present License Details</strong></h5>

                                <div class="lic-tabs" role="tablist" aria-label="Licence type">
                                    <button type="button" class="lic-tab {{ $licOpenContractor ? '' : 'is-active' }}" role="tab"
                                        id="licTabCompetency" aria-controls="licPaneCompetency" data-lic-tab="licPaneCompetency"
                                        aria-selected="{{ $licOpenContractor ? 'false' : 'true' }}">
                                        <i class="fa fa-certificate" aria-hidden="true"></i> Competency Certificates
                                        <span class="lic-tab__count">{{ $competencyLicenses->count() }}</span>
                                    </button>
                                    <button type="button" class="lic-tab {{ $licOpenContractor ? 'is-active' : '' }}" role="tab"
                                        id="licTabContractor" aria-controls="licPaneContractor" data-lic-tab="licPaneContractor"
                                        aria-selected="{{ $licOpenContractor ? 'true' : 'false' }}">
                                        <i class="fa fa-id-card-o" aria-hidden="true"></i> Contractor Licenses
                                        <span class="lic-tab__count">{{ $contractorLicenses->count() }}</span>
                                    </button>
                                </div>
                            </div>

                            {{-- Competency Certificates --}}
                            <div class="lic-pane" id="licPaneCompetency" role="tabpanel" aria-labelledby="licTabCompetency" @if ($licOpenContractor) hidden @endif>
                                @forelse ($competencyLicenses as $licence)
                                    @include('user_login.license-row', ['licence' => $licence, 'codes' => $competencyCodes])
                                @empty
                                    <p class="lic-empty"><i class="fa fa-folder-open-o" aria-hidden="true"></i> No active competency certificates</p>
                                @endforelse
                            </div>

                            {{-- Contractor Licenses --}}
                            <div class="lic-pane" id="licPaneContractor" role="tabpanel" aria-labelledby="licTabContractor" @unless ($licOpenContractor) hidden @endunless>
                                @forelse ($contractorLicenses as $licence)
                                    @php
                                    // TEMP: Bank Solvency / Staff CC checks disabled (source tables missing in DB)
                                    $bankValidity = null;
                                    $expiredStaffDates = [];
                                    $licFlags = [];
                                    if ($bankValidity && $bankValidity->lt($today)) {
                                        $licFlags[] = 'Bank Solvency expired on ' . $bankValidity->format('d-m-Y');
                                    }
                                    foreach ($expiredStaffDates as $expired) {
                                        $licFlags[] = 'QC Staff CC ' . $expired['number'] . ' expired on ' . $expired['date'];
                                    }
                                    @endphp
                                    @include('user_login.license-row', ['licence' => $licence, 'codes' => $contractorCodes, 'flags' => $licFlags])
                                @empty
                                    <p class="lic-empty"><i class="fa fa-folder-open-o" aria-hidden="true"></i> No active contractor licenses</p>
                                @endforelse
                            </div>
                        </div>

                        <script>
                            (function () {
                                var panel = document.getElementById('licPanel');
                                if (!panel) {
                                    return;
                                }
                                var tabs = panel.querySelectorAll('[data-lic-tab]');
                                tabs.forEach(function (tab) {
                                    tab.addEventListener('click', function () {
                                        tabs.forEach(function (t) {
                                            var on = t === tab;
                                            t.classList.toggle('is-active', on);
                                            t.setAttribute('aria-selected', on ? 'true' : 'false');
                                            document.getElementById(t.getAttribute('data-lic-tab')).hidden = !on;
                                        });
                                    });
                                });
                            })();
                        </script>

                        <!-- Tasks -->
                        @if (isset($paginatedData) && $paginatedData->isNotEmpty())

                        <!-- ----------------- -->
                        <div class="tasks-section-login">
                            <h5 class="mb-2">
                                <strong>Status of Applications ( Competency Certificate )</strong>
                            </h5>
                            <fieldset class="custom-fieldset">
                                <ul class="legend justify-content-end mb-2">
                                    <li><span class="bg-info"></span> Draft</li>
                                    <li><span class="bg-primary"></span> Submitted</li>

                                    <li><span class="bg-warning"></span> In Progress</li>
                                    <li><span class="bg-success"></span> Completed</li>
                                    <li><span class="bg-danger"></span> Rejected</li>
                                    <li><span class="bg-return"></span> Returned </li>

                                </ul>
                                <div class="applications-table-toolbar">
                                    <div class="applications-page-length-toolbar">
                                        <div class="applications-page-length-inner">
                                            <label for="applicationsPerPageSelect" class="applications-page-length-label text-nowrap">Show</label>
                                            <select id="applicationsPerPageSelect" name="per_page" class="applications-page-length-select" aria-label="Entries per page">
                                                @foreach ([5, 10, 20, 50, 100] as $n)
                                                    <option value="{{ $n }}" @selected((int) $paginatedData->perPage() === $n)>{{ $n }}</option>
                                                @endforeach
                                            </select>
                                            <span class="applications-page-length-hint text-nowrap">entries</span>
                                        </div>
                                    </div>
                                    <div class="applications-search-toolbar">
                                        <div class="applications-search-inner">
                                            <input type="search" id="applicationsSearchInput" name="search" value="{{ request('search', '') }}" class="applications-search-input" placeholder="Search" maxlength="120" autocomplete="off" aria-label="Search applications table">
                                        </div>
                                    </div>
                                </div>
                                <div id="applicationsTable">
                                    @include('user_login.pagination-list')
                                </div>
                            </fieldset>
                        </div>
                        @endif

                        <!-- ---------------------------------------------------------- -->
                         @if (isset($workflows_cl) && $workflows_cl->isNotEmpty())

                       <div class="tasks-section-login d-none d-sm-block">
                            <h5 class="mb-2">
                                <strong>Status of Applications ( Contractor Licence )</strong>
                            </h5>
                            <fieldset class="custom-fieldset">
                                <ul class="legend justify-content-end mb-2">
                                    <li><span class="bg-info"></span> Draft</li>
                                    <li><span class="bg-primary"></span> Submitted</li>
                                    <li><span class="bg-warning"></span> In Progress</li>
                                    <li><span class="bg-success"></span> Completed</li>
                                    <li><span class="bg-danger"></span> Rejected</li>
                                    <li><span class="bg-return"></span> Returned </li>

                                </ul>
                                <div class="applications-table-toolbar">
                                    <div class="applications-page-length-toolbar">
                                        <div class="applications-page-length-inner">
                                            <label for="contractorApplicationsPerPageSelect" class="applications-page-length-label text-nowrap">Show</label>
                                            <select id="contractorApplicationsPerPageSelect" class="applications-page-length-select" aria-label="Entries per page">
                                                @foreach ([5, 10, 20, 50, 100] as $n)
                                                    <option value="{{ $n }}" @selected($n === 5)>{{ $n }}</option>
                                                @endforeach
                                            </select>
                                            <span class="applications-page-length-hint text-nowrap">entries</span>
                                        </div>
                                    </div>
                                    <div class="applications-search-toolbar">
                                        <div class="applications-search-inner">
                                            <input type="search" id="contractorApplicationsSearchInput" class="applications-search-input" placeholder="Search" maxlength="120" autocomplete="off" aria-label="Search contractor applications table">
                                        </div>
                                    </div>
                                </div>
                                <table id="contractorApplicationsTable" class="table table-login table-login-compact contractor-status-table" width="100%">
                                    <thead>
                                       <tr>
                                           <th>S.No</th>
                                           <th>Form / Licence Name</th>
                                           <th>Application ID</th>
                                           <th>Applied On</th>
                                           <th>Application<br> Status</th>
                                           <th>Payment <br> Status</th>
                                           {{-- <th>Payment <br> Receipt</th> --}}
                                           <th>Acknowledgement<br> Download</th>
                                           <th>Licence Status</th>

                                       </tr>
                                   </thead>
                                   <tbody>
                                       @if (isset($workflows_cl) && $workflows_cl->isNotEmpty())
                                       @foreach ($workflows_cl as $index => $workflow)
                                       <tr>
                                           

                                           <td @if(in_array($workflow->application_status, ['RET', 'RETD'])) class="return-cell" @endif>
                                            @if(in_array($workflow->application_status, ['RET', 'RETD']))
                                                <div class="return-ribbon-wrapper">
                                                    <span class="return-ribbon">RETURNED</span>
                                                </div>
                                                <span class="return-sno-num">{{ $index + 1 }}</span>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </td>
                                            <td style="width: 18%;">
                                           {{ $workflow->licence_display_name ?? 'N/A' }} <br>
                                           [Form {{ strtoupper($workflow->form_name ?? 'NA') }}]
                                           </td>
                                           <td> <a href="{{ route('contractor.application.preview', ['application_id' => $workflow->application_id]) }}" class="js-app-preview-link" target="_blank">{{ $workflow->application_id ?? 'N/A' }}</a></td>
                                           <td>{{ isset($workflow->dt_submit) ? \Carbon\Carbon::parse($workflow->dt_submit)->format('d/m/Y') : 'N/A' }}
                                           </td>

                                           <!-- Application Status -->
                                           
                                            <td>
                                                @if (in_array($workflow->application_status, ['RET', 'RETD']))

                                               @if (strtoupper(trim($workflow->appl_type)) === 'N')

                                                    @if($workflow->form_name == 'A')
                                                    <a href="{{ route('apply-form-a_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>
                                                    @elseif($workflow->form_name == 'B')
                                                    <a href="{{ route('apply-form-b_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>
                                                    @elseif($workflow->form_name == 'SB')
                                                    <a href="{{ route('apply-form-sb_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>
                                                    @else
                                                    <a href="{{ route('apply-form-sa_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>

                                                    @endif
                                                @elseif(strtoupper(trim($workflow->appl_type)) === 'D')
                                                     @if($workflow->form_name == 'A')
                                                    <a href="{{ route('apply-form-a_d_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>
                                                    @elseif($workflow->form_name == 'B')
                                                    <a href="{{ route('apply-form-b_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>
                                                    @elseif($workflow->form_name == 'SB')
                                                    <a href="{{ route('apply-form-sb_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>
                                                    @else
                                                    <a href="{{ route('apply-form-sa_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>

                                                    @endif
                                               @elseif(strtoupper(trim($workflow->appl_type)) === 'A')
                                                     @if($workflow->form_name == 'A')
                                                    <a href="{{ route('forma_alter_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>
                                                    @elseif($workflow->form_name == 'B')
                                                    <a href="{{ route('apply-form-b_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>
                                                    @elseif($workflow->form_name == 'SB')
                                                    <a href="{{ route('apply-form-sb_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>
                                                    @else
                                                    <a href="{{ route('apply-form-sa_draft', ['application_id' => $workflow->application_id]) }}">
                                                        <button class="btn btn-sm btn-info">
                                                            <i class="fa fa-pencil"></i> Draft
                                                        </button>
                                                    </a>

                                                    @endif     
                                               @else
                                               @if($workflow->form_name == 'A')
                                               <a href="{{ route('renew-form_ea', ['application_id' => $workflow->application_id]) }}">
                                                   <button class="btn btn-sm btn-info">
                                                       <i class="fa fa-pencil"></i> Draft
                                                   </button>
                                               </a>
                                               @elseif($workflow->form_name == 'B')
                                               <a href="{{ route('renew-form_eb', ['application_id' => $workflow->application_id]) }}">
                                                   <button class="btn btn-sm btn-info">
                                                       <i class="fa fa-pencil"></i> Draft
                                                   </button>
                                               </a>
                                               @elseif($workflow->form_name == 'SB')
                                               <a href="{{ route('apply-form-sb_renewal_draft', ['application_id' => $workflow->application_id]) }}">
                                                   <button class="btn btn-sm btn-info">
                                                       <i class="fa fa-pencil"></i> Draft
                                                   </button>
                                               </a>
                                               @else
                                               <a href="{{ route('apply-form-sa_renewal_draft', ['application_id' => $workflow->application_id]) }}">
                                                   <button class="btn btn-sm btn-info">
                                                       <i class="fa fa-pencil"></i> Draft
                                                   </button>
                                               </a>

                                               @endif

                                               @endif

                                               @else
                                               @if ($workflow->appl_type == 'R')
                                               @if ($workflow->application_status == 'P')
                                               <span class="btn btn-sm btn-primary">Renewal Form
                                                   Submitted</span>
                                               @elseif($workflow->application_status == 'F')
                                               <span class="btn btn-sm btn-warning">In Progress</span>
                                               @else
                                               <span class="btn btn-sm btn-success">Completed</span>
                                               @endif
                                               @else
                                               @if ($workflow->application_status == 'P')
                                               <span class="btn btn-sm btn-primary">Submitted</span>

                                               @elseif($workflow->application_status == 'F')
                                               <span class="btn btn-sm btn-warning">In Progress</span>

                                               @elseif($workflow->application_status == 'RET')
                                                   <span class="btn btn-sm btn-return">Returned</span>
                                               @else
                                               <span class="btn btn-sm btn-success">Completed</span>
                                               @endif
                                               @endif
                                               @endif
                                           </td>

                                           <!-- Payment Status -->
                                           <td>
                                               @if ($workflow->payment_status == 'paid')
                                               <p class="text-success"><strong>Success</strong></p>
                                               @else
                                               <p class="text-danger">Pending</p>
                                               @endif
                                           </td>

                                           {{--
                                           <td>
                                               @if ($workflow->payment_status == 'paid')
                                               <a href="{{ route('paymentreceipt.pdf', ['loginId' => $workflow->application_id]) }}"
                                                   target="_blank" rel="noopener noreferrer"
                                                   title="Download Payment Receipt PDF"
                                                   style="font-weight:500;">
                                                   <i class="fa fa-file-pdf-o"
                                                       style="font-size:20px;color:red"></i>
                                               </a>
                                               @else
                                               <p class="text-danger">Pending</p>
                                               @endif
                                           </td>
                                           --}}

                                           <!-- Application Download -->
                                           <td>
                                               @if ($workflow->payment_status == 'draft')
                                               <p>-</p>
                                               @else
                                                @if(($workflow->form_name == 'A'))
                                                <a href="{{ route('generatea.pdf', ['login_id' => $workflow->application_id]) }}"
                                                    target="_blank" style="font-weight:500;">
                                                    <i class="fa fa-file-pdf-o"
                                                        style="font-size:20px;color:red"></i>
                                                    <span style="font-size: x-small;">Download</span>
                                                </a>
                                                @elseif(($workflow->form_name == 'SB'))
                                                <a href="{{ route('generatesb.pdf', ['login_id' => $workflow->application_id]) }}"
                                                    target="_blank" style="font-weight:500;">
                                                    <i class="fa fa-file-pdf-o"
                                                        style="font-size:20px;color:red"></i>
                                                    <span style="font-size: x-small;">Download</span>
                                                </a>
                                                @elseif(($workflow->form_name == 'B'))
                                                <a href="{{ route('generateb.pdf', ['login_id' => $workflow->application_id]) }}"
                                                    target="_blank" style="font-weight:500;">
                                                    <i class="fa fa-file-pdf-o"
                                                        style="font-size:20px;color:red"></i>
                                                    <span style="font-size: x-small;">Download</span>
                                                </a>
                                                @else
                                                <a href="{{ route('generatesa.pdf', ['login_id' => $workflow->application_id]) }}"
                                                    target="_blank" style="font-weight:500;">
                                                    <i class="fa fa-file-pdf-o"
                                                        style="font-size:20px;color:red"></i>
                                                    <span style="font-size: x-small;">Download</span>
                                                </a>

                                                @endif
                                               @endif
                                           </td>

                                           <!-- License Number -->

                                             <td>
                                               @if (!empty($workflow->license_number) && $workflow->application_status == 'A')
                                                    <a href="{{ route('admin.licencepdf_cl.pdf', ['application_id' => $workflow->application_id]) }}" target="_blank">
                                                        <span class="badge badge-info" style="font-size: 15px;">{{ $workflow->license_number }}</span>
                                                    </a>
                                                    <br>

                                                    @if (!empty($workflow->renewals))
                                                    <span class="text-muted" style="font-size: 12px;">
                                                        Renewed {{ count($workflow->renewals) }} times
                                                    </span>
                                                    <br>
                                               @endif

                                                    @if (!empty($workflow->renewal_application_id) && $workflow->appl_type == 'R')
                                                        <strong>Renewal Application</strong><br>
                                                        ID :
                                                        <a href="{{ route('generate.pdf', ['login_id' => $workflow->renewal_application_id]) }}"
                                                            target="_blank"
                                                            class="text-success">
                                                            {{ $workflow->renewal_application_id }}
                                                        </a>
                                                    @else
                                                        @if ($workflow->is_under_validity_period)
                                                            @if(($workflow->form_name == 'SA'))
                                                                <a href="{{ route('renew-form_esa', ['application_id' => $workflow->application_id]) }}" class="text-primary">
                                                                    (Apply for renewal)
                                                                </a>
                                                            @elseif($workflow->form_name == 'SB')
                                                                <a href="{{ route('renew-form_esb', ['application_id' => $workflow->application_id]) }}" class="text-primary">
                                                                    (Apply for renewal)
                                                                </a>

                                                            @elseif($workflow->form_name == 'B')
                                                                <a href="{{ route('renew-form_eb', ['application_id' => $workflow->application_id]) }}" class="text-primary">
                                                                    (Apply for renewal)
                                                                </a>
                                                            @else

                                                                    <a href="{{ route('renew-form_ea', ['application_id' => $workflow->application_id]) }}" class="text-primary">
                                                                    (Apply for renewal)
                                                                </a>

                                                            @endif
                                                        @endif
                                                    @endif
                                               @elseif (!empty($workflow->renewal_application_id))

                                               <strong>Renewal Application</strong><br>
                                               ID :
                                               <span class="text-success">{{ $workflow->renewal_application_id }}</span>
<<<<<<< HEAD
                                               {{-- @else
                                               <p class="text-primary">NA</p> --}}
                                               @endif
=======
                                              
>>>>>>> 000cb0bd95af8df6b8c03b0ae81d2701e12369a3

                                               @elseif($workflow->application_status == 'RET')
                                               <a href="{{ route('apply-form-a_return', ['application_id' => $workflow->application_id]) }}">
                                                <button class="btn btn-primary btn-sm">
                                                    <i class="fa fa-pencil"></i> Edit
                                                </button>
                                               </a>
                                                @elseif ($workflow->application_status == 'A')
                                                        <br>
                                                    <span> 
                                                        <a href="{{ route('admin.licencepdf_cl.pdf', ['application_id' => $workflow->application_id]) }}" target="_blank">
                                                            <i class="fa fa-file-pdf-o"
                                                            style="font-size:20px;color:red"></i>
                                                        <span style="font-size: x-small;">English</span>
                                                        </a>
                                                        </span>
                                                @endif
                                           </td>


                                       </tr>
                                       @endforeach
                                       @else
                                       <tr>
                                           <td colspan="9" class="text-center text-danger">No records found</td>
                                       </tr>
                                       @endif
                                   </tbody>
                               </table>


                                <div id="contractorTableInfo"></div>
                                <div id="contractorTablePagination" class="table-pagination pt-20"></div>


                        </div>
                        </fieldset>
                        @endif
                    </section>
                </main>
            </div>
        </div>
    </div>
</section>

<div id="dashAppTimelineModal" class="dash-tl-overlay" role="dialog" aria-modal="true" aria-labelledby="dashAppTimelineTitle" aria-hidden="true">
    <div class="dash-tl-panel" role="document">
        <header class="dash-tl-header">
            <div>
                <h2 id="dashAppTimelineTitle" class="dash-tl-title">
                    Information of Digitisation Dependency
                </h2>
                <p class="dash-tl-subtitle" id="dashAppTimelineSubtitle"></p>
            </div>
            <button type="button" class="dash-tl-close" id="dashAppTimelineClose" aria-label="Close application timeline">&times;</button>
        </header>
        <div class="dash-tl-body">
            <div class="dash-tl-loading" id="dashAppTimelineLoading" aria-live="polite">
                <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                <span>Loading digitisation details…</span>
            </div>
            <div id="dashAppTimelineContent"></div>
        </div>
    </div>
</div>

<footer class="main-footer">
    @include('include.footer')
    @if(session('already_applied'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'warning',
                title: 'You already applied this application!',
                // text: 'Redirecting to dashboard...',
                timer: 3000,
                showConfirmButton: false
            });
        });
    </script>
    @endif
    <script>
        // document.addEventListener("DOMContentLoaded", function () {
        //     const filterSelect = document.getElementById("filter-status-login");

        //     if (!filterSelect) {
        //         console.error("Element #filter-status-login not found in DOM.");
        //         return;
        //     }

        //     filterSelect.addEventListener("change", function(e) {
        //         const filter = e.target.value;

        //         // Filter projects
        //         document.querySelectorAll(".project-card-login").forEach(card => {
        //             if (filter === "all" || card.dataset.status === filter) {
        //                 card.style.display = "block";
        //             } else {
        //                 card.style.display = "none";
        //             }
        //         });

        //         // Filter tasks
        //         document.querySelectorAll(".table-login tbody tr").forEach(row => {
        //             if (filter === "all" || row.dataset.status === filter) {
        //                 row.style.display = "";
        //             } else {
        //                 row.style.display = "none";
        //             }
        //         });
        //     });
        // });
    </script>
<script>
// document.addEventListener("DOMContentLoaded", function () {
//     const tables = document.querySelectorAll(".table-login");
//     const rowsPerPage = 5;

//     tables.forEach((table, tableIndex) => {
//         const rows = table.querySelectorAll("tbody tr");
//         const paginationContainer = table.nextElementSibling; // .table-pagination div
//         let currentPage = 1;

//         function displayRows(page) {
//             const start = (page - 1) * rowsPerPage;
//             const end = start + rowsPerPage;

//             rows.forEach((row, index) => {
//                 row.style.display = (index >= start && index < end) ? "" : "none";
//             });
//         }

//         function createPagination() {
//             const pageCount = Math.ceil(rows.length / rowsPerPage);
//             paginationContainer.innerHTML = "";

//             if (pageCount <= 1) return;

//             let html = `<nav><ul class="pagination justify-content-center">`;
//             for (let i = 1; i <= pageCount; i++) {
//                 html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
//                             <a href="#" class="page-link" data-page="${i}">${i}</a>
//                          </li>`;
//             }
//             html += `</ul></nav>`;
//             paginationContainer.innerHTML = html;

//             paginationContainer.querySelectorAll(".page-link").forEach(btn => {
//                 btn.addEventListener("click", function (e) {
//                     e.preventDefault();
//                     currentPage = Number(this.dataset.page);
//                     displayRows(currentPage);
//                     createPagination();
//                 });
//             });
//         }

//         displayRows(currentPage);
//         createPagination();
//     });
// });
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const tables = document.querySelectorAll(".table-login:not(.contractor-status-table)");
    const rowsPerPage = 5;

    tables.forEach((table, tableIndex) => {
        const rows = table.querySelectorAll("tbody tr");
        const paginationContainer = table.nextElementSibling; // .table-pagination div
        if (!paginationContainer || !paginationContainer.classList.contains("table-pagination")) {
            return;
        }
        let currentPage = 1;

        function displayRows(page) {
            const start = (page - 1) * rowsPerPage;
            const end = start + rowsPerPage;

            rows.forEach((row, index) => {
                row.style.display = (index >= start && index < end) ? "" : "none";
            });
        }

        function createPagination() {
            const pageCount = Math.ceil(rows.length / rowsPerPage);
            paginationContainer.innerHTML = "";

            if (pageCount <= 1) return;

            let html = `<nav><ul class="pagination justify-content-center">`;
            for (let i = 1; i <= pageCount; i++) {
                html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                            <a href="#" class="page-link" data-page="${i}">${i}</a>
                         </li>`;
            }
            html += `</ul></nav>`;
            paginationContainer.innerHTML = html;

            paginationContainer.querySelectorAll(".page-link").forEach(btn => {
                btn.addEventListener("click", function (e) {
                    e.preventDefault();
                    currentPage = Number(this.dataset.page);
                    displayRows(currentPage);
                    createPagination();
                });
            });
        }

        displayRows(currentPage);
        createPagination();
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const contractorTable = document.getElementById("contractorApplicationsTable");
    const searchInput = document.getElementById("contractorApplicationsSearchInput");
    const perPageSelect = document.getElementById("contractorApplicationsPerPageSelect");
    const infoContainer = document.getElementById("contractorTableInfo");
    const paginationContainer = document.getElementById("contractorTablePagination");

    if (!contractorTable || !searchInput || !perPageSelect || !paginationContainer || !infoContainer) {
        return;
    }

    const allRows = Array.from(contractorTable.querySelectorAll("tbody tr")).filter((row) => {
        return !row.querySelector('td[colspan]');
    });
    const noRecordsRow = contractorTable.querySelector('tbody tr td[colspan]')?.closest('tr') || null;
    let currentPage = 1;

    function filteredRows() {
        const query = searchInput.value.trim().toLowerCase();
        if (!query) {
            return allRows;
        }
        return allRows.filter((row) => row.textContent.toLowerCase().includes(query));
    }

    function renderTable() {
        const visibleRows = filteredRows();
        const rowsPerPage = Number(perPageSelect.value) || 5;
        const pageCount = Math.max(1, Math.ceil(visibleRows.length / rowsPerPage));
        if (currentPage > pageCount) {
            currentPage = 1;
        }

        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        allRows.forEach((row) => {
            row.style.display = "none";
        });
        visibleRows.slice(start, end).forEach((row) => {
            row.style.display = "";
        });

        if (noRecordsRow) {
            noRecordsRow.style.display = visibleRows.length ? "none" : "";
        }

        if (!visibleRows.length) {
            infoContainer.textContent = "Showing 0 to 0 of 0 results";
        } else {
            infoContainer.textContent = `Showing ${start + 1} to ${Math.min(end, visibleRows.length)} of ${visibleRows.length} results`;
        }

        paginationContainer.innerHTML = "";
        if (pageCount <= 1 || !visibleRows.length) {
            return;
        }

        let html = '<nav><ul class="pagination justify-content-center">';
        html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                    <a href="#" class="page-link" data-page="${currentPage - 1}" aria-label="Previous">&laquo;</a>
                 </li>`;
        for (let i = 1; i <= pageCount; i++) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                        <a href="#" class="page-link" data-page="${i}">${i}</a>
                     </li>`;
        }
        html += `<li class="page-item ${currentPage === pageCount ? 'disabled' : ''}">
                    <a href="#" class="page-link" data-page="${currentPage + 1}" aria-label="Next">&raquo;</a>
                 </li>`;
        html += '</ul></nav>';
        paginationContainer.innerHTML = html;

        paginationContainer.querySelectorAll(".page-link").forEach((btn) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();
                if (this.parentElement.classList.contains("disabled")) {
                    return;
                }
                currentPage = Number(this.dataset.page);
                renderTable();
            });
        });
    }

    perPageSelect.addEventListener("change", function () {
        currentPage = 1;
        renderTable();
    });

    searchInput.addEventListener("input", function () {
        currentPage = 1;
        renderTable();
    });

    renderTable();
});
</script>

    <script>
    $(document).on('click', '#applicationsTable .pagination a', function (e) {
        e.preventDefault();

        var url = $(this).attr('href');

        console.log(url);

        $.ajax({
            url: url,
            type: "GET",
            success: function (data) {
                $("#applicationsTable").html(data);
            }
        });
    });

    function applicationsTableAjaxUrl(page) {
        var perPage = $('#applicationsPerPageSelect').val() || '5';
        var search = ($('#applicationsSearchInput').val() || '').trim();
        var url = "{{ route('dashboard') }}?page=" + encodeURIComponent(page || 1) + "&per_page=" + encodeURIComponent(perPage);
        if (search.length) {
            url += "&search=" + encodeURIComponent(search);
        }
        return url;
    }

    $(document).on('change', '#applicationsPerPageSelect', function () {
        $.ajax({
            url: applicationsTableAjaxUrl(1),
            type: 'GET',
            success: function (data) {
                $('#applicationsTable').html(data);
            }
        });
    });

    var applicationsSearchDebounceTimer = null;
    $(document).on('input', '#applicationsSearchInput', function () {
        clearTimeout(applicationsSearchDebounceTimer);
        applicationsSearchDebounceTimer = setTimeout(function () {
            $.ajax({
                url: applicationsTableAjaxUrl(1),
                type: 'GET',
                success: function (data) {
                    $('#applicationsTable').html(data);
                }
            });
        }, 350);
    });

    $(document).on('click', '.btn-payu-check-status', function () {
        var btn = $(this);
        var applicationId = btn.data('application-id');
        if (!applicationId || btn.prop('disabled')) {
            return;
        }

        btn.prop('disabled', true);
        var originalHtml = btn.html();
        btn.html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: "{{ route('payu.check_status') }}",
            type: 'POST',
            dataType: 'json',
            data: {
                application_id: applicationId,
                _token: $('meta[name="csrf-token"]').attr('content'),
            },
            success: function (res) {
                if (res && res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment successful',
                        text: res.message || 'Your payment has been confirmed.',
                        confirmButtonText: 'OK',
                    }).then(function () {
                        window.location.reload();
                    });
                    return;
                }
                if (res && res.status === 'failed') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Payment not completed',
                        text: res.message || 'Payment failed. You can pay again from your application.',
                        confirmButtonText: 'OK',
                    }).then(function () {
                        window.location.reload();
                    });
                    return;
                }
                Swal.fire({
                    icon: 'info',
                    title: 'Still in progress',
                    text: (res && res.message) ? res.message : 'Payment is still processing at the bank. Try again later.',
                    confirmButtonText: 'OK',
                });
            },
            error: function (xhr) {
                var msg = 'Unable to check payment status. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Check failed', text: msg });
            },
            complete: function () {
                btn.prop('disabled', false);
                btn.html(originalHtml);
            },
        });
    });
</script>
