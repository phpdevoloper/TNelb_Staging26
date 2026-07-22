@include('admin.include.top')
@include('admin.include.header')
@include('admin.include.navbar')
<style>
    .digi_title {
        color: #199393;
        font-size: 17px;
        font-weight: 600;
        margin: 0;

    }

    .digitization-header {
        background: #fff !important;
    }

    .digi_data table th,
    td {
        border: 1px solid #8692bd4f !important;
        padding: 10px;
        font-weight: 600;
    }

    .digi_data table {
        padding: 10px;
    }

    /* .tab-table tr, td{
        border: none!important;
    } */
    /* ================================================================
       Applicant Detail (Supervisor) — refreshed visual styling
       Scoped to .applicant-supervisor-page so it won't bleed elsewhere.
       ================================================================ */
    .applicant-supervisor-page {
        --asp-primary: #4361ee;
        --asp-primary-soft: #eef2ff;
        --asp-success: #10b981;
        --asp-success-soft: #ecfdf5;
        --asp-danger: #ef4444;
        --asp-danger-soft: #fef2f2;
        --asp-warning: #f59e0b;
        --asp-ink: #1f2937;
        --asp-ink-soft: #4b5563;
        --asp-muted: #6b7280;
        --asp-border: #e5e7eb;
        --asp-border-strong: #d1d5db;
        --asp-bg: #f8fafc;
        --asp-card-bg: #ffffff;
        --asp-radius: 12px;
        --asp-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 4px 12px rgba(15, 23, 42, 0.05);
    }

    .applicant-supervisor-page .tab-content {
        padding: 1rem 1.25rem 0.5rem;
    }

    /* ---------- Applicant summary header ---------- */
    .applicant-supervisor-page .applicant_details {
        background: linear-gradient(135deg, #eef2ff 0%, #e0f2fe 100%);
        border: 1px solid var(--asp-border);
        border-radius: var(--asp-radius);
        padding: 1rem 1.25rem;
        box-shadow: var(--asp-shadow);
    }

    .applicant-supervisor-page .applicant_details h4 {
        margin: 0;
        color: var(--asp-ink);
        font-size: 0.95rem;
        font-weight: 600;
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem 0.75rem;
        align-items: center;
        line-height: 1.5;
    }

    .applicant-supervisor-page .applicant_details h4>span {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.6rem;
        font-weight: 600;
        font-size: 0.82rem;
        border-radius: 999px;
        background: #ffffffcc;
        color: var(--asp-ink) !important;
        border: 1px solid #ffffff;
    }

    /* ---------- Widget cards ---------- */
    .applicant-supervisor-page .statbox.widget {
        border: 1px solid var(--asp-border);
        border-radius: var(--asp-radius);
        background: var(--asp-card-bg);
        box-shadow: var(--asp-shadow);
    }

    .applicant-supervisor-page .widget-content-area {
        padding: 0.75rem 0.75rem 1rem;
    }

    /* ---------- Tabs ---------- */
    .applicant-supervisor-page .simple-tab .nav-tabs {
        border-bottom: 2px solid var(--asp-border);
    }

    .applicant-supervisor-page .simple-tab .nav-tabs .nav-link {
        color: var(--asp-ink-soft);
        font-weight: 600;
        font-size: 0.9rem;
        border: none;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        padding: 0.6rem 1rem;
        transition: color 0.15s ease, border-color 0.15s ease, background 0.15s ease;
    }

    .applicant-supervisor-page .simple-tab .nav-tabs .nav-link:hover {
        color: var(--asp-primary);
        background: var(--asp-primary-soft);
    }

    .applicant-supervisor-page .simple-tab .nav-tabs .nav-link.active {
        color: var(--asp-primary);
        background: transparent;
        border-bottom-color: var(--asp-primary);
    }

    /* ---------- Section headings inside Personal Details ---------- */
    .applicant-supervisor-page .asp-section-title {
        position: relative;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--asp-ink);
        margin: 1.1rem 0 0.6rem;
        padding-left: 0.65rem;
    }

    .applicant-supervisor-page .asp-section-title::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0.18rem;
        bottom: 0.18rem;
        width: 4px;
        border-radius: 4px;
        background: var(--asp-primary);
    }

    /* ---------- Alteration highlights (Form S / W alteration requests) ---------- */
    .applicant-supervisor-page .asp-alteration-summary {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.85rem;
        color: var(--asp-ink);
    }

    .applicant-supervisor-page .asp-alteration-summary strong {
        color: #b45309;
    }

    .applicant-supervisor-page .asp-alteration-summary ul {
        margin: 0.35rem 0 0;
        padding-left: 1.15rem;
    }

    .applicant-supervisor-page td.asp-alteration-highlight {
        background: #fffbeb;
        box-shadow: inset 3px 0 0 #f59e0b;
    }

    .applicant-supervisor-page .wx-alter-badge,
    .applicant-supervisor-page .asp-alter-badge {
        display: inline-block;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.1rem 0.45rem;
        border-radius: 4px;
        background: #fef3c7;
        color: #b45309;
        border: 1px solid #f59e0b;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        vertical-align: middle;
    }

    .applicant-supervisor-page .wx-alteration-alter-row>td {
        background: #fffbeb !important;
        box-shadow: inset 3px 0 0 #f59e0b;
    }

    /* ---------- Personal details mini-table ---------- */
    .applicant-supervisor-page .home-tab-pane .table-sm tbody td {
        padding: 0.45rem 0.5rem;
        border-color: var(--asp-border);
        font-size: 0.86rem;
    }

    .applicant-supervisor-page .home-tab-pane .table-sm tbody td.fw-bold {
        color: var(--asp-ink-soft);
    }

    /* ---------- Photo + signature frame ---------- */
    .applicant-supervisor-page .asp-photo-frame {
        border: 1px dashed var(--asp-border-strong);
        border-radius: var(--asp-radius);
        padding: 0.75rem;
        background: var(--asp-bg);
        display: inline-block;
    }

    .applicant-supervisor-page .asp-photo-frame img {
        border-radius: 8px;
    }

    .applicant-supervisor-page .asp-signature-frame {
        border: 1px dashed var(--asp-border-strong);
        border-radius: 8px;
        padding: 0.35rem 0.5rem;
        background: #fff;
        display: inline-block;
        margin-top: 0.5rem;
    }

    /* ---------- Compact tables (education / work) ---------- */
    .applicant-supervisor-page .applicant-detail-table-wrap {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid var(--asp-border);
        border-radius: 10px;
        background: #fff;
    }

    .applicant-supervisor-page .applicant-detail-table-wrap::-webkit-scrollbar {
        height: 8px;
    }

    .applicant-supervisor-page .applicant-detail-table-wrap::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    .applicant-supervisor-page .applicant-detail-table-wrap::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    .applicant-supervisor-page .applicant-detail-compact-table {
        table-layout: auto;
        width: 100%;
        font-size: 0.8125rem;
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .applicant-supervisor-page .applicant-detail-compact-table.edu-qual-table {
        min-width: 720px;
    }

    .applicant-supervisor-page .applicant-detail-compact-table.work-exp-table {
        min-width: 720px;
    }

    .applicant-supervisor-page .applicant-detail-compact-table.work-exp-table.work-exp-with-doc {
        min-width: 1080px;
    }

    .applicant-supervisor-page .work-exp-admin-readonly {
        margin-bottom: 0.75rem;
    }

    .applicant-supervisor-page .work-exp-admin-readonly .work-exp-view-wrap {
        overflow-x: auto;
    }

    .applicant-supervisor-page .work-exp-admin-readonly .wx-summary-table-wrap {
        overflow-x: auto;
    }

    .applicant-supervisor-page .board-member-qa-block {
        margin-top: 1.1rem;
    }

    .applicant-supervisor-page .board-member-qa-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 0.35rem;
    }

    .applicant-supervisor-page .board-member-qa-head .asp-section-title {
        margin: 0;
        flex: 1 1 auto;
        min-width: 0;
    }

    .applicant-supervisor-page .board-member-detail-wrap {
        margin-top: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .applicant-supervisor-page .board-member-detail-alter-flag {
        display: flex;
        justify-content: flex-end;
        margin-bottom: 0.35rem;
    }

    .applicant-supervisor-page .board-member-detail-table {
        margin-bottom: 0;
        border: none;
    }

    .applicant-supervisor-page .board-member-detail-table tbody th,
    .applicant-supervisor-page .board-member-detail-table tbody td {
        border: none !important;
    }

    .applicant-supervisor-page .board-member-detail-table tbody th {
        width: 34%;
        min-width: 180px;
        padding: 0.45rem 0.5rem 0.45rem 0;
        vertical-align: top;
        line-height: 1.35;
        background: transparent;
        border: none;
        color: var(--asp-ink-soft);
        font-weight: 600;
        font-size: 0.86rem;
        white-space: normal;
        word-break: normal;
    }

    .applicant-supervisor-page .board-member-detail-table tbody td {
        padding: 0.45rem 0.5rem;
        vertical-align: top;
        line-height: 1.45;
        background: transparent;
        border: none;
        color: var(--asp-ink);
        white-space: normal;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .applicant-supervisor-page .wx-overall-exp-row td {
        background: #f8fafc;
        border-top: 2px solid var(--asp-border);
        vertical-align: middle;
    }

    .applicant-supervisor-page .wx-overall-exp-label-cell {
        padding-right: 0.75rem;
    }

    .applicant-supervisor-page .wx-overall-exp-label {
        font-weight: 600;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: var(--asp-ink);
    }

    .applicant-supervisor-page .wx-period-box--overall {
        justify-content: flex-start;
    }

    .applicant-supervisor-page .wx-period-box--overall .wx-period-duration {
        border-color: #b29ae9;
        background: linear-gradient(135deg, #cdbbff 0%, #b197fc 100%);
    }

    .applicant-supervisor-page .wx-period-box--overall .wx-period-dur-cell .wx-period-dur-num,
    .applicant-supervisor-page .wx-period-box--overall .wx-period-dur-cell .wx-period-dur-lbl {
        color: #ffffff;
    }

    .applicant-supervisor-page .applicant-detail-compact-table thead th {
        padding: 0.55rem 0.6rem;
        vertical-align: middle;
        line-height: 1.2;
        background: #f1f5f9;
        color: var(--asp-ink);
        font-weight: 600;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        border-bottom: 1px solid var(--asp-border);
        white-space: nowrap;
    }

    .applicant-supervisor-page .applicant-detail-compact-table tbody td {
        padding: 0.55rem 0.6rem;
        vertical-align: middle;
        line-height: 1.3;
        border-top: 1px solid var(--asp-border);
        color: var(--asp-ink);
        white-space: nowrap;
    }

    .applicant-supervisor-page .applicant-detail-compact-table tbody tr:first-child td {
        border-top: none;
    }

    .applicant-supervisor-page .applicant-detail-compact-table tbody tr:hover td {
        background: #f8fafc;
    }

    .applicant-supervisor-page .applicant-detail-compact-table .col-wrap {
        word-break: normal;
        overflow-wrap: normal;
        white-space: nowrap;
    }

    .applicant-supervisor-page .applicant-detail-compact-table .col-doc {
        text-align: center;
        white-space: nowrap;
    }

    .applicant-supervisor-page .applicant-detail-compact-table .col-doc .doc-pdf-link {
        display: inline-flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        max-width: 100%;
        font-size: 0.72rem;
        line-height: 1.15;
        text-decoration: none;
    }

    .applicant-supervisor-page .applicant-detail-compact-table .col-doc .doc-pdf-link:hover {
        text-decoration: underline;
    }

    .applicant-supervisor-page .applicant-detail-compact-table .col-doc .doc-thumb {
        max-width: 48px;
        height: auto;
        display: block;
        margin: 0 auto;
        border-radius: 4px;
    }

    /* ---------- Certificate Q/A cards (Q7 / Q8 / WH / W) ---------- */
    .applicant-supervisor-page .asp-qa-card {
        border: 1px solid var(--asp-border);
        background: #fff;
        border-radius: var(--asp-radius);
        padding: 0.75rem 1rem;
        margin: 0.75rem 0;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
    }

    .applicant-supervisor-page .asp-qa-card:hover {
        border-color: #c7d2fe;
        box-shadow: 0 2px 10px rgba(67, 97, 238, 0.08);
    }

    .applicant-supervisor-page .asp-qa-card h6 {
        font-size: 0.88rem;
        font-weight: 600;
        color: var(--asp-ink);
        margin: 0;
        line-height: 1.45;
    }

    .applicant-supervisor-page .asp-qa-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: nowrap;
    }

    .applicant-supervisor-page .asp-qa-head h6 {
        flex: 1 1 auto;
        min-width: 0;
        margin-bottom: 0;
    }

    .applicant-supervisor-page .asp-qa-answer {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.2rem 0.7rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.82rem;
        line-height: 1.3;
        white-space: nowrap;
        flex: 0 0 auto;
    }

    .applicant-supervisor-page .asp-qa-answer.is-yes {
        background: var(--asp-success-soft);
        color: var(--asp-success);
    }

    .applicant-supervisor-page .asp-qa-answer.is-no {
        background: #f3f4f6;
        color: var(--asp-muted);
    }

    .applicant-supervisor-page .asp-qa-detail {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.5rem;
        margin-top: 0.75rem;
    }

    .applicant-supervisor-page .asp-qa-detail .asp-detail-cell {
        background: #f8fafc;
        border: 1px solid var(--asp-border);
        border-radius: 8px;
        padding: 0.55rem 0.7rem;
        text-align: center;
    }

    .applicant-supervisor-page .asp-qa-detail .asp-detail-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--asp-muted);
        font-weight: 600;
        display: block;
        margin-bottom: 0.15rem;
    }

    .applicant-supervisor-page .asp-qa-detail .asp-detail-value {
        font-size: 0.88rem;
        color: var(--asp-ink);
        font-weight: 600;
        word-break: break-word;
    }

    .applicant-supervisor-page .asp-qa-detail .asp-verify-row {
        margin-top: 0.4rem;
        display: flex;
        justify-content: center;
    }

    .applicant-supervisor-page .admin_verify.badge {
        background: var(--asp-primary);
        color: #fff;
        padding: 0.35em 0.7em;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        border-radius: 999px;
        cursor: pointer;
        transition: background 0.15s ease, transform 0.15s ease;
    }

    .applicant-supervisor-page .admin_verify.badge:hover {
        background: #3b52d8;
        transform: translateY(-1px);
    }

    @media (max-width: 576px) {
        .applicant-supervisor-page .asp-qa-detail {
            grid-template-columns: 1fr;
        }
    }

    /* ---------- Documents uploaded block ---------- */
    .applicant-supervisor-page .asp-docs-block {
        background: var(--asp-bg);
        border: 1px solid var(--asp-border);
        border-radius: var(--asp-radius);
        padding: 0.75rem 1rem;
        margin-top: 0.5rem;
    }

    .applicant-supervisor-page .asp-docs-block h6 {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--asp-ink);
        margin: 0 0 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .applicant-supervisor-page .applicant-inline-doc-link {
        font-size: 0.75rem;
        line-height: 1.2;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        text-decoration: none;
        padding: 0.15rem 0.5rem;
        border-radius: 6px;
        background: var(--asp-primary-soft);
        color: var(--asp-primary) !important;
        transition: background 0.15s ease;
    }

    .applicant-supervisor-page .applicant-inline-doc-link:hover {
        background: #dbe4ff;
        text-decoration: none;
    }

    /* ---------- Checklist ---------- */
    .applicant-supervisor-page #profile-tab-pane .checklist-header-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 2.5rem;
        margin: 0.25rem 0 0.5rem;
        padding: 0.4rem 0.5rem;
        border-bottom: 1px solid var(--asp-border, #e5e7eb);
    }

    .applicant-supervisor-page #profile-tab-pane .checklist-header-row .form-check {
        margin: 0;
        padding: 0.15rem 0.25rem 0.15rem 1.6rem;
    }

    .applicant-supervisor-page #profile-tab-pane #specific-class {
        /* display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 0.75rem;
        row-gap: 0.1rem;
        padding: 0.25rem 0.25rem 0.5rem; */
    }

    .applicant-supervisor-page #profile-tab-pane .form-check {
        padding: 0.2rem 0.4rem 0.2rem 1.6rem;
        border-radius: 6px;
        margin: 0;
        min-height: auto;
        transition: background 0.15s ease;
    }

    .applicant-supervisor-page #profile-tab-pane .form-check:hover {
        background: var(--asp-primary-soft);
    }

    .applicant-supervisor-page #profile-tab-pane .form-check-label {
        font-size: 0.85rem;
        color: var(--asp-ink);
        cursor: pointer;
        line-height: 1.35;
    }

    @media (max-width: 575.98px) {
        .applicant-supervisor-page #profile-tab-pane #specific-class {
            grid-template-columns: 1fr;
        }

        .applicant-supervisor-page #profile-tab-pane .checklist-header-row {
            gap: 1.25rem;
        }
    }

    /* ---------- Payment panel ---------- */
    .applicant-supervisor-page #contact-tab-pane .text-primary {
        font-size: 0.95rem;
    }

    .applicant-supervisor-page #contact-tab-pane p {
        font-size: 0.85rem;
        margin-bottom: 0.4rem;
        color: var(--asp-ink);
    }

    .applicant-supervisor-page #contact-tab-pane .badge.badge-success {
        background: var(--asp-success);
        color: #fff;
        padding: 0.3em 0.7em;
        border-radius: 999px;
        font-size: 0.75rem;
    }

    /* ---------- Remarks & action buttons ---------- */
    .applicant-supervisor-page #remarks {
        border: 1px solid var(--asp-border-strong);
        border-radius: 8px;
        font-size: 0.88rem;
        resize: vertical;
    }

    .applicant-supervisor-page #remarks:focus {
        border-color: var(--asp-primary);
        box-shadow: 0 0 0 0.15rem rgba(67, 97, 238, 0.15);
    }

    .applicant-supervisor-page .remarks-actions-wrap .btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.45rem 1rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.06);
    }

    .applicant-supervisor-page .remarks-actions-wrap .btn:hover {
        transform: translateY(-1px);
    }

    /* ---------- Query switch card ---------- */
    .applicant-supervisor-page .switch-label {
        font-size: 0.88rem;
        color: var(--asp-ink);
    }

    .eligibile_criteria {
        padding: 10px;
    }

    .eligibile_criteria h4 {
        color: green;
        font-size: 20px;
        font-weight: 600;

    }

    .eligibile_criteria h6 {
        padding: 15px;
        color: #2131a7;
        font-size: 18px;
        font-weight: 600;

    }


    .border_right {
        border-right: 1px solid gray;
    }

    /* Default (Unchecked = Red) */
    .status-switch {
        background-color: #645e5e !important;
        border-color: #645e5e !important;
        cursor: pointer;
    }

    /* Checked = Green */
    .status-switch:checked {
        background-color: #198754 !important;
        border-color: #198754 !important;
    }

    /* Focus */
    .status-switch:focus {
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }

    .checklist_chk .form-check-input:checked {
        background-color: #4361ee !important;
        border-color: #4361ee !important;
    }


    @php $editFormName = 'S'; @endphp
    @include('user_login.partials.form-s-work-exp-styles')
</style>
<div id="content" class="main-content applicant-supervisor-page">
    <div class="layout-px-spacing">
        <div class="middle-content container-xxl p-0">
            <div class="secondary-nav">
                <div class="breadcrumbs-container" data-page-heading="Analytics">
                    <header class="header navbar navbar-expand-sm">
                        <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-menu">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </a>

                    </header>
                </div>
            </div>

            <div class="row layout-top-spacing align-items-start">
                <div class="col-lg-12 layout-spacing">
                    <div class="statbox widget ">
                        <div
                            class="widget-header applicant_details {{ $applicant->appl_type == 'D' ? 'digitization-header' : '' }}">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <h4>
                                        <strong>Applicant Id:</strong> <span>{{ $applicant->application_id }}</span>
                                        <strong>Name:</strong> <span>{{ $applicant->applicant_name }}</span>
                                        <strong>D.O.B:</strong> <span>{{ format_date($applicant->d_o_b) }} &middot;
                                            {{ $applicant->age }} yrs</span>
                                        <strong>Email:</strong>
                                        <span>{{ !empty($applicant->applicant_email) ? e($applicant->applicant_email) : '—' }}</span>
                                        <strong>Applied For:</strong> <span>FORM {{ $applicant->form_name }} &middot;
                                            License {{ $applicant->license_name }}</span>
                                        @if($applicant->appl_type == 'A' && !empty($applicant->old_application))
                                            <strong>Parent Application:</strong>
                                            <span>{{ $applicant->old_application }}</span>
                                        @endif
                                    </h4>
                                </div>
                                @if($applicant->appl_type == 'D')

                                    <div class="col-xl-6 col-md-6 col-sm-12 col-12">
                                        <h3 class="digi_title">Digitization Old Certificate Details </h3>
                                        <div class="table-responsive digi_data">
                                            <table class="table table-bordered table-sm">
                                                <tbody>
                                                    <tr>
                                                        <th width="30%">Certificate Number</th>
                                                        <td>{{ $cc_digitization->ccnumber }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Date of First Issue</th>
                                                        <td>{{ $cc_digitization->fissue }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>Validity From</th>
                                                        <td>{{ \Carbon\Carbon::parse($cc_digitization->from_date)->format('d-m-Y') }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Validity To</th>
                                                        <td>{{ \Carbon\Carbon::parse($cc_digitization->to_date)->format('d-m-Y') }}
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Certificate Document</th>
                                                        <td>
                                                            <a href="{{ asset('uploads/digitization/scc/' . $cc_digitization->cc_doc) }}"
                                                                target="_blank">
                                                                <i class="fa fa-file-pdf-o text-danger"></i>
                                                                View Document
                                                            </a>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>


                                    @if($cc_digitization->qc_det == '1')

                                        <div class="col-xl-6 col-md-6 col-sm-12 col-12">
                                            <h3 class="digi_title">Is Supervisory Competency Certificate recognized as a
                                                Qualified </h3>
                                            <div class="table-responsive digi_data">
                                                <table class="table table-bordered table-sm">
                                                    <tbody>
                                                        <tr>
                                                            <th width="30%">Grade of Licence</th>
                                                            <td>{{ $cc_digitization->cl_type}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Licence Number</th>
                                                            <td>{{ $cc_digitization->licence_no}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Name of Contractor</th>
                                                            <td>{{ $cc_digitization->contractor_name}}</td>
                                                        </tr>

                                                        <tr>
                                                            <th> Document</th>
                                                            <td>
                                                                <a href="{{ asset('uploads/digitization/qc/' . $cc_digitization->qc_doc) }}"
                                                                    target="_blank">
                                                                    <i class="fa fa-file-pdf-o text-danger"></i>
                                                                    View Document
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif

                                @endif
                            </div>
                        </div>




                    </div>
                </div>



                <div id="tabsSimple" class="col-xl-7 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    {{-- <h3 class="application_id_css">Application Id :<span style="color:#098501;"> {{
                                            $applicant->application_id }}</span> </h3> --}}
                                    {{-- <h4>Edit / View Applicant's Details</h4> --}}
                                </div>
                            </div>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="simple-tab">
                                <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab"
                                            data-bs-target="#home-tab-pane" type="button" role="tab"
                                            aria-controls="home-tab-pane" aria-selected="true">Personal Details</button>
                                    </li>

                                   @if(!in_array($applicant->appl_type, ['D', 'A']))

                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link " id="contact-tab" data-bs-toggle="tab"
                                                data-bs-target="#contact-tab-pane" type="button" role="tab"
                                                aria-controls="contact-tab-pane" aria-selected="false">Payment
                                                Status</button>
                                        </li>
                                    @endif

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab"
                                            data-bs-target="#profile-tab-pane" type="button" role="tab"
                                            aria-controls="profile-tab-pane" aria-selected="false">Check List</button>
                                    </li>
                                </ul>

                                <div class="tab-content tab-table" id="myTabContent">
                                    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel"
                                        aria-labelledby="home-tab" tabindex="0">
                                        @php
                                            $isAlterationApp = ($applicant->appl_type ?? '') === 'A';
                                            $parentForAlter = $parentApplicantForAlter ?? null;
                                            $nameAltered = $isAlterationApp && $parentForAlter
                                                && trim((string) ($applicant->applicant_name ?? '')) !== trim((string) ($parentForAlter->applicant_name ?? $parentForAlter->applicants_name ?? ''));
                                            $hasAlteredWork = $isAlterationApp && ($workExperience ?? collect())->contains(function ($row) {
                                                return !empty($row->is_alteration_new);
                                            });
                                            $hasAlterationProofs = $isAlterationApp && ($alterationProofs ?? collect())->isNotEmpty();
                                            $nameProofDoc = ($alterationProofs ?? collect())->first(function ($proof) {
                                                return ($proof->document_type ?? '') === 'name_proof';
                                            });
                                            $addressProofDoc = ($alterationProofs ?? collect())->first(function ($proof) {
                                                return ($proof->document_type ?? '') === 'address_proof';
                                            });
                                            $resolveAlterProofUrl = function ($proof) {
                                                if (!$proof) {
                                                    return null;
                                                }
                                                if (!empty($proof->url)) {
                                                    return $proof->url;
                                                }
                                                $storedPath = trim((string) ($proof->proof_doc ?? ''));
                                                return $storedPath !== '' ? competency_document_path_url($storedPath) : null;
                                            };
                                            $nameProofUrl = $resolveAlterProofUrl($nameProofDoc);
                                            $addressProofUrl = $resolveAlterProofUrl($addressProofDoc);
                                            $parentAddressValue = trim((string) ($parentForAlter->applicants_address ?? $parentForAlter->applicant_address ?? ''));
                                            $addressAltered = $isAlterationApp && $parentForAlter
                                                && trim((string) ($applicant->applicants_address ?? $applicant->applicant_address ?? '')) !== $parentAddressValue;
                                        @endphp
                                        @if($isAlterationApp && ($nameAltered || $addressAltered || $hasAlteredWork || $hasAlterationProofs))
                                            <div class="asp-alteration-summary mt-3 mb-2">
                                                <strong>Altered in this request</strong>
                                                <ul class="mb-0">
                                                    @if($nameAltered)
                                                        <li>Applicant name <span class="asp-alter-badge">ALTER</span></li>
                                                    @endif
                                                    @if($addressAltered)
                                                        <li>Address <span class="asp-alter-badge">ALTER</span></li>
                                                    @endif
                                                    @if($hasAlteredWork)
                                                        <li>Work experience or board member details — see sections marked <span
                                                                class="asp-alter-badge">ALTER</span> below</li>
                                                    @endif
                                                    @if($hasAlterationProofs)
                                                        <li>Supporting documents uploaded for name/address change</li>
                                                    @endif
                                                </ul>
                                            </div>
                                        @endif
                                        <div class="row mt-3 ">
                                            <div class="row">
                                                <!-- Left Side: Applicant Details -->
                                                <div class="col-md-8">
                                                    <div class="table-responsive">
                                                        <table class="table  no-border-table table-sm">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="fw-bold " style="width: 30%;">Applicant
                                                                        Id :</td>
                                                                    <td>{{ $applicant->application_id }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold">Applicant Name :</td>
                                                                    <td
                                                                        class="{{ $nameAltered ? 'asp-alteration-highlight' : '' }}">
                                                                        {{ $applicant->applicant_name }}
                                                                        @if($nameAltered)
                                                                            <span class="asp-alter-badge ms-1">ALTER</span>
                                                                            @if($parentForAlter && trim((string) ($parentForAlter->applicant_name ?? '')) !== '')
                                                                                <div class="text-muted small mt-1">Previously:
                                                                                    {{ $parentForAlter->applicant_name }}
                                                                                </div>
                                                                            @endif
                                                                            @if(!empty($nameProofUrl))
                                                                                <div class="mt-1">
                                                                                    <a href="{{ $nameProofUrl }}"
                                                                                        target="_blank"
                                                                                        rel="noopener noreferrer"
                                                                                        class="doc-pdf-link text-primary small">
                                                                                        <i
                                                                                            class="fa fa-file-pdf-o text-danger"></i>
                                                                                        <span>View name proof</span>
                                                                                    </a>
                                                                                </div>
                                                                            @endif
                                                                        @elseif(!empty($nameProofUrl))
                                                                            <div class="mt-1">
                                                                                <a href="{{ $nameProofUrl }}"
                                                                                    target="_blank"
                                                                                    rel="noopener noreferrer"
                                                                                    class="doc-pdf-link text-primary small">
                                                                                    <i
                                                                                        class="fa fa-file-pdf-o text-danger"></i>
                                                                                    <span>View name proof</span>
                                                                                </a>
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold">Father's Name :</td>
                                                                    <td>{{ $applicant->fathers_name }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold align-top">Address :</td>
                                                                    <td class="{{ $addressAltered ? 'asp-alteration-highlight' : '' }}"
                                                                        style="white-space: normal; word-break: break-word;">
                                                                        {{ $applicant->applicants_address }}
                                                                        @if($addressAltered)
                                                                            <span class="asp-alter-badge ms-1">ALTER</span>
                                                                            @if($parentForAlter && $parentAddressValue !== '')
                                                                                <div class="text-muted small mt-1">Previously:
                                                                                    {{ $parentAddressValue }}
                                                                                </div>
                                                                            @endif
                                                                            @if(!empty($addressProofUrl))
                                                                                <div class="mt-1">
                                                                                    <a href="{{ $addressProofUrl }}"
                                                                                        target="_blank"
                                                                                        rel="noopener noreferrer"
                                                                                        class="doc-pdf-link text-primary small">
                                                                                        <i
                                                                                            class="fa fa-file-pdf-o text-danger"></i>
                                                                                        <span>View address proof</span>
                                                                                    </a>
                                                                                </div>
                                                                            @endif
                                                                        @elseif(!empty($addressProofUrl))
                                                                            <div class="mt-1">
                                                                                <a href="{{ $addressProofUrl }}"
                                                                                    target="_blank"
                                                                                    rel="noopener noreferrer"
                                                                                    class="doc-pdf-link text-primary small">
                                                                                    <i
                                                                                        class="fa fa-file-pdf-o text-danger"></i>
                                                                                    <span>View address proof</span>
                                                                                </a>
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold">D.O.B & Age :</td>
                                                                    <td>{{ $applicant->d_o_b }} ({{ $applicant->age }}
                                                                        years old)</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold">Email ID :</td>
                                                                    <td>
                                                                        @if (!empty($applicant->applicant_email))
                                                                            <a
                                                                                href="mailto:{{ e($applicant->applicant_email) }}">{{ e($applicant->applicant_email) }}</a>
                                                                        @else
                                                                            —
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <!-- Right Side: Applicant Photo -->
                                                <div class="col-md-4 text-center">
                                                    @php
                                                        $photo = $uploadedPhoto ?? $applicant_photo ?? null;
                                                        $photoPath = $photo && !empty($photo->upload_path) ? $photo->upload_path : null;
                                                        $signPath = !empty($uploadedSign?->uploaded_doc) ? $uploadedSign->uploaded_doc : null;
                                                        $photoUrl = !empty($photo?->media_url)
                                                            ? $photo->media_url
                                                            : ($photoPath ? competency_media_url($photoPath) : null);
                                                        $signUrl = !empty($uploadedSign?->media_url)
                                                            ? $uploadedSign->media_url
                                                            : ($signPath ? competency_media_url($signPath) : null);
                                                    @endphp
                                                    @if($photoUrl)
                                                        <div class="asp-photo-frame">
                                                            <img src="{{ $photoUrl }}" alt="Applicant Photo"
                                                                class="img-fluid"
                                                                style="width: 140px; height: 180px; object-fit: cover;"
                                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                            <p class="text-muted mb-0" style="display: none;">No photo
                                                                available</p>
                                                        </div>
                                                    @else
                                                        <p class="text-muted">No photo available</p>
                                                    @endif

                                                    <div class="mt-3">
                                                        @if($signUrl)
                                                            <div class="asp-signature-frame">
                                                                <img src="{{ $signUrl }}" alt="Applicant Signature"
                                                                    class="img-fluid"
                                                                    style="width: 110px; height: 50px; object-fit: contain; background: #fff;"
                                                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                                <p class="text-muted mb-0" style="display: none;">No
                                                                    signature available</p>
                                                            </div>
                                                        @else
                                                            <p class="text-muted mb-0">No signature available</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            @if($applicant->appl_type == 'A' && ($alterationProofs ?? collect())->isNotEmpty())
                                                <h6 class="asp-section-title mt-3">Alteration Supporting Documents</h6>
                                                <div class="applicant-detail-table-wrap mb-3">
                                                    <table
                                                        class="table table-sm table-bordered applicant-detail-compact-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Document</th>
                                                                <th>View</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($alterationProofs as $proof)
                                                                @php
                                                                    $proofViewUrl = !empty($proof->url)
                                                                        ? $proof->url
                                                                        : (!empty($proof->proof_doc) ? competency_document_path_url($proof->proof_doc) : null);
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $proof->label ?? 'Supporting proof' }}</td>
                                                                    <td>
                                                                        @if(!empty($proofViewUrl))
                                                                            <a href="{{ $proofViewUrl }}" target="_blank"
                                                                                rel="noopener noreferrer"
                                                                                class="doc-pdf-link text-primary">
                                                                                <i class="fa fa-file-pdf-o text-danger"></i>
                                                                                <span>View Document</span>
                                                                            </a>
                                                                        @else
                                                                            <span class="text-muted small">—</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif

                                            <h6 class="asp-section-title">Educational Qualifications</h6>
                                            <div class="applicant-detail-table-wrap">
                                                <table
                                                    class="table table-sm table-bordered applicant-detail-compact-table edu-qual-table">
                                                    <thead>
                                                        <tr>
                                                            <th rowspan="2">Degree</th>
                                                            <th rowspan="2">Institution</th>
                                                            <th colspan="2">Month &amp; Year of Passing</th>
                                                            <th rowspan="2">Certificate No</th>
                                                            <th rowspan="2">Document Upload</th>
                                                        </tr>
                                                        <tr>
                                                            <th>Month</th>
                                                            <th>Year</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $monthLabels = [
                                                                '01' => 'Jan',
                                                                '02' => 'Feb',
                                                                '03' => 'Mar',
                                                                '04' => 'Apr',
                                                                '05' => 'May',
                                                                '06' => 'Jun',
                                                                '07' => 'Jul',
                                                                '08' => 'Aug',
                                                                '09' => 'Sep',
                                                                '10' => 'Oct',
                                                                '11' => 'Nov',
                                                                '12' => 'Dec',
                                                            ];
                                                        @endphp
                                                        @forelse ($educationalQualifications as $education)
                                                            @php
                                                                $rawMonth = trim((string) ($education->month_passing ?? $education->month_of_passing ?? ''));
                                                                $monthKey = $rawMonth !== '' && is_numeric($rawMonth) ? str_pad($rawMonth, 2, '0', STR_PAD_LEFT) : $rawMonth;
                                                                $monthDisplay = $monthLabels[$monthKey] ?? '';
                                                                if ($monthDisplay === '' && $rawMonth !== '') {
                                                                    $alphaMonth = strtolower(substr($rawMonth, 0, 3));
                                                                    $alphaMap = ['jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr', 'may' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'aug' => 'Aug', 'sep' => 'Sep', 'oct' => 'Oct', 'nov' => 'Nov', 'dec' => 'Dec'];
                                                                    $monthDisplay = $alphaMap[$alphaMonth] ?? $rawMonth;
                                                                }
                                                                $monthDisplay = $monthDisplay !== '' ? $monthDisplay : '-';
                                                            @endphp
                                                            <tr>
                                                                <td class="col-wrap">{{ $education->educational_level }}
                                                                </td>
                                                                <td class="col-wrap">{{ $education->institute_name }}</td>
                                                                <td class="col-wrap">{{ $monthDisplay }}</td>
                                                                <td class="col-wrap">
                                                                    {{ !empty($education->year_of_passing) ? $education->year_of_passing : '-' }}
                                                                </td>
                                                                @php
                                                                    $certificateNo = data_get($education, 'certificate_no');
                                                                    $percentage = data_get($education, 'percentage');
                                                                @endphp
                                                                <td class="col-wrap">
                                                                    @if($certificateNo !== null && $certificateNo !== '')
                                                                        {{ $certificateNo }}
                                                                    @elseif($percentage !== null && $percentage !== '')
                                                                        {{ $percentage }}%
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                </td>
                                                                <td class="col-doc">
                                                                    @if(!empty($education->upload_document) || !empty($education->document_url))
                                                                        @php
                                                                            $eduDocUrl = $education->document_url
                                                                                ?? competency_document_url(
                                                                                    $education->upload_document ?? null,
                                                                                    'education',
                                                                                    (int) ($education->id ?? 0),
                                                                                    'certificate',
                                                                                    array_filter([
                                                                                        (int) ($formSWorkflowAppPk ?? 0),
                                                                                        (int) ($formSMasterWorkflowAppPk ?? 0),
                                                                                    ])
                                                                                );
                                                                            $fileExtension = strtolower(pathinfo($education->upload_document ?? 'document.pdf', PATHINFO_EXTENSION));
                                                                        @endphp
                                                                        @if($eduDocUrl && \in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'], true))
                                                                            <a href="{{ $eduDocUrl }}" target="_blank"
                                                                                rel="noopener noreferrer" title="View image">
                                                                                <img src="{{ $eduDocUrl }}" alt=""
                                                                                    class="doc-thumb">
                                                                            </a>
                                                                        @elseif($eduDocUrl)
                                                                            <a href="{{ $eduDocUrl }}" target="_blank"
                                                                                rel="noopener noreferrer"
                                                                                class="doc-pdf-link text-primary"
                                                                                title="View document">
                                                                                <i class="fa fa-file-pdf-o text-danger"></i>
                                                                                <span>View Document</span>
                                                                            </a>
                                                                        @else
                                                                            <span class="text-muted small">—</span>
                                                                        @endif
                                                                    @else
                                                                        <span class="text-muted small">—</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="6" class="text-center">No educational details
                                                                    available.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>

                                            @if (in_array(($applicant->form_name ?? ''), ['S', 'W'], true))
                                                @php $isFormS = (($applicant->form_name ?? '') === 'S'); @endphp
                                                <h6 class="asp-section-title">Work Experience</h6>
                                                @if($applicant->appl_type == 'A')
                                                    <p class="text-muted small mb-2">Existing experience from the parent
                                                        certificate is shown below. Rows marked <span
                                                            class="asp-alter-badge">ALTER</span> were added or changed in this
                                                        alteration request.</p>
                                                @endif
                                                @if ($isFormS)
                                                    @include('admin.partials.form-s-work-exp-readonly', ['workExperience' => $workExperience ?? collect()])
                                                @else
                                                    <div class="applicant-detail-table-wrap">
                                                        <table
                                                            class="table table-sm table-bordered applicant-detail-compact-table work-exp-table">
                                                            <thead>
                                                                <tr>
                                                                    <th>Company</th>
                                                                    <th>Designation</th>
                                                                    <th>Exp.</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse ($workExperience as $experience)
                                                                    <tr>
                                                                        <td class="col-wrap">
                                                                            {{ $experience->emp_cate ?? $experience->company_name ?? '' }}
                                                                        </td>
                                                                        <td class="col-wrap">{{ $experience->designation }}</td>
                                                                        <td class="col-wrap">
                                                                            {{ $experience->total_exp ?? $experience->experience ?? 0 }}
                                                                            yrs
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="3" class="text-center">No work experience
                                                                            available.</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            @endif
                                            @if ($applicant->form_name == 'S')
                                                @php
                                                    $prevValidTo = $applicant->previously_valid_to ?? $applicant->previously_date ?? null;
                                                    $hasPreviousEaQual = !empty($applicant->previously_number) || !empty($prevValidTo);
                                                @endphp
                                                <div class="asp-qa-card">
                                                    <div class="asp-qa-head">
                                                        <h6>
                                                            Do you already possess a Supervisor Competency Certificate
                                                            issued by this Board? If yes, please furnish the details.
                                                        </h6>
                                                        <span
                                                            class="asp-qa-answer {{ $hasPreviousEaQual ? 'is-yes' : 'is-no' }}">
                                                            {{ $hasPreviousEaQual ? 'Yes' : 'No' }}
                                                        </span>
                                                    </div>
                                                    @if ($hasPreviousEaQual)
                                                        <div class="asp-qa-detail">
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Certificate Number</span>
                                                                <span
                                                                    class="asp-detail-value">{{ $applicant->previously_number ?: '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of First Issue</span>
                                                                <span
                                                                    class="asp-detail-value">{{ !empty($applicant->previously_issue_date) ? format_date($applicant->previously_issue_date) : '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">From date</span>
                                                                <span
                                                                    class="asp-detail-value">{{ !empty($applicant->previously_valid_from) ? format_date($applicant->previously_valid_from) : '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">To date</span>
                                                                <span
                                                                    class="asp-detail-value">{{ !empty($prevValidTo) ? format_date($prevValidTo) : '—' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="asp-verify-row">
                                                            <span class="badge badge-primary admin_verify"
                                                                data-license_number="{{ $applicant->previously_number }}"
                                                                data-license_from_date="{{ $applicant->previously_valid_from }}"
                                                                data-license_date="{{ $prevValidTo }}"
                                                                data-license_issue_date="{{ $applicant->previously_issue_date }}"
                                                                data-type="certificate" style="cursor: pointer;">Verify</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                @php
                                                    $certValidTo = $applicant->certificate_valid_to ?? $applicant->certificate_date ?? null;
                                                    $hasWiremanCompCert = !empty($applicant->certificate_no) || !empty($certValidTo);
                                                @endphp
                                                <div class="asp-qa-card">
                                                    <div class="asp-qa-head">
                                                        <h6>
                                                            Do you also possess Wireman Competency Certificate issued by
                                                            this Board? If so furnish the details.
                                                        </h6>
                                                        <span
                                                            class="asp-qa-answer {{ $hasWiremanCompCert ? 'is-yes' : 'is-no' }}">
                                                            {{ $hasWiremanCompCert ? 'Yes' : 'No' }}
                                                        </span>
                                                    </div>
                                                    @if ($hasWiremanCompCert)
                                                        <div class="asp-qa-detail">
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Certificate Number</span>
                                                                <span
                                                                    class="asp-detail-value">{{ $applicant->certificate_no ?: '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of First Issue</span>
                                                                <span
                                                                    class="asp-detail-value">{{ !empty($applicant->certificate_issue_date) ? format_date($applicant->certificate_issue_date) : '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">From date</span>
                                                                <span
                                                                    class="asp-detail-value">{{ !empty($applicant->certificate_valid_from) ? format_date($applicant->certificate_valid_from) : '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">To date</span>
                                                                <span
                                                                    class="asp-detail-value">{{ !empty($certValidTo) ? format_date($certValidTo) : '—' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="asp-verify-row">
                                                            <span class="badge badge-primary admin_verify"
                                                                data-license_number="{{ $applicant->certificate_no }}"
                                                                data-license_from_date="{{ $applicant->certificate_valid_from }}"
                                                                data-license_date="{{ $applicant->certificate_valid_to ?? $applicant->certificate_date }}"
                                                                data-license_issue_date="{{ $applicant->certificate_issue_date }}"
                                                                data-type="certificate" style="cursor: pointer;">Verify</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif


                                            @if (in_array($applicant->form_name, ['WH']))
                                                @php
                                                    $hasWiremanBoardCert = !empty($applicant->certificate_no) && !empty($applicant->certificate_date);
                                                @endphp
                                                <div class="asp-qa-card">
                                                    <div class="asp-qa-head">
                                                        <h6>
                                                            Have you applied for and obtained a Certificate of Qualification
                                                            for Wireman Helper? If yes, please state its number and
                                                            validity.
                                                        </h6>
                                                        <span
                                                            class="asp-qa-answer {{ $hasWiremanBoardCert ? 'is-yes' : 'is-no' }}">
                                                            {{ $hasWiremanBoardCert ? 'Yes' : 'No' }}
                                                        </span>
                                                    </div>
                                                    @if ($hasWiremanBoardCert)
                                                        <div class="asp-qa-detail">
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">License Number</span>
                                                                <span
                                                                    class="asp-detail-value">{{ $applicant->certificate_no ?: '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of First Issue</span>
                                                                <span
                                                                    class="asp-detail-value">{{ !empty($applicant->certificate_issue_date) ? format_date($applicant->certificate_issue_date) : '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of Expiry</span>
                                                                <span
                                                                    class="asp-detail-value">{{ format_date($applicant->certificate_date) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="asp-verify-row">
                                                            <span class="badge badge-primary admin_verify"
                                                                data-license_number="{{ $applicant->certificate_no }}"
                                                                data-license_from_date="{{ $applicant->certificate_valid_from }}"
                                                                data-license_date="{{ $applicant->certificate_valid_to ?? $applicant->certificate_date }}"
                                                                data-license_issue_date="{{ $applicant->certificate_issue_date }}"
                                                                data-type="certificate" style="cursor: pointer;">Verify</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                            @if (in_array($applicant->form_name, ['W']))
                                                @php
                                                    $hasWiremanBoardCert = !empty($applicant->certificate_no) && !empty($applicant->certificate_date);
                                                @endphp
                                                <div class="asp-qa-card">
                                                    <div class="asp-qa-head">
                                                        <h6>
                                                            Have you applied for and obtained a Certificate of Qualification
                                                            for Wireman / Wireman Helper? If yes, please state its number
                                                            and validity.
                                                        </h6>
                                                        <span
                                                            class="asp-qa-answer {{ $hasWiremanBoardCert ? 'is-yes' : 'is-no' }}">
                                                            {{ $hasWiremanBoardCert ? 'Yes' : 'No' }}
                                                        </span>
                                                    </div>
                                                    @if ($hasWiremanBoardCert)
                                                        <div class="asp-qa-detail">
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">License Number</span>
                                                                <span
                                                                    class="asp-detail-value">{{ $applicant->certificate_no ?: '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of First Issue</span>
                                                                <span
                                                                    class="asp-detail-value">{{ !empty($applicant->certificate_issue_date) ? format_date($applicant->certificate_issue_date) : '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of Expiry</span>
                                                                <span
                                                                    class="asp-detail-value">{{ format_date($applicant->certificate_date) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="asp-verify-row">
                                                            <span class="badge badge-primary admin_verify"
                                                                data-license_number="{{ $applicant->certificate_no }}"
                                                                data-license_from_date="{{ $applicant->certificate_valid_from }}"
                                                                data-license_date="{{ $applicant->certificate_valid_to ?? $applicant->certificate_date }}"
                                                                data-license_issue_date="{{ $applicant->certificate_issue_date }}"
                                                                data-type="certificate" style="cursor: pointer;">Verify</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            <!-- ----------------------------------------------- -->

                                            @php
                                                $decryptedAadhaar = displayProofNumber($applicant->aadhaar ?? '');
                                                $decryptedPan = displayProofNumber($applicant->pancard ?? '');

                                                $masked = strlen($decryptedAadhaar) === 12
                                                    ? str_repeat('X', 8) . substr($decryptedAadhaar, -4)
                                                    : ($decryptedAadhaar !== '' ? 'Invalid Aadhaar' : '—');

                                                $maskedPan = strlen($decryptedPan) === 10
                                                    ? str_repeat('X', 6) . substr($decryptedPan, -4)
                                                    : ($decryptedPan !== '' ? 'Invalid PAN' : '—');

                                                $panDocument = $applicant->pan_doc ?? $applicant->pancard_doc ?? null;
                                                $aadhaarDocUrl = proof_document_url($applicant->aadhaar_doc ?? null, 'aadhaar');
                                                $panDocUrl = proof_document_url($panDocument, 'pan');
                                            @endphp

                                            <div class="asp-docs-block">
                                                <h6>Documents Uploaded</h6>
                                                <div class="row align-items-center g-2">
                                                    <div class="col-lg-6">
                                                        <span class="fw-bold" style="color: #111;">Aadhaar</span>
                                                    </div>
                                                    <div class="col-lg-6 text-lg-end">
                                                        <span class="fw-bold"
                                                            style="color: #515365">{{ $masked }}</span>
                                                        @if ($aadhaarDocUrl)
                                                            <a href="{{ $aadhaarDocUrl }}" target="_blank"
                                                                class="applicant-inline-doc-link ms-1"
                                                                title="Open Aadhaar document">
                                                                <i class="fa fa-file-pdf-o text-danger"
                                                                    aria-hidden="true"></i>
                                                                <span>View Document</span>
                                                            </a>
                                                        @else
                                                            <span class="text-muted small ms-1">No document</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row align-items-center g-2 mt-1">
                                                    <div class="col-lg-6">
                                                        <span class="fw-bold" style="color: #111;">PAN</span>
                                                    </div>
                                                    <div class="col-lg-6 text-lg-end">
                                                        <span class="fw-bold"
                                                            style="color: #515365">{{ $maskedPan }}</span>
                                                        @if ($panDocUrl)
                                                            <a href="{{ $panDocUrl }}" target="_blank"
                                                                class="applicant-inline-doc-link ms-1"
                                                                title="Open PAN document">
                                                                <i class="fa fa-file-pdf-o text-danger"
                                                                    aria-hidden="true"></i>
                                                                <span>View Document</span>
                                                            </a>
                                                        @else
                                                            <span class="text-muted small ms-1">No document</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel"
                                        aria-labelledby="profile-tab" tabindex="0">
                                        <?php //var_dump($workflows->first()->is_verified);die; ?>
                                        @php
                                            $workflow = $workflows?->first();

                                            $checkedList = [];

                                            if (!empty($workflow?->chklist_status)) {
                                                $checkedList = is_array($workflow->chklist_status)
                                                    ? $workflow->chklist_status
                                                    : json_decode($workflow->chklist_status, true);
                                            }
                                        @endphp


                                        <div class="row mt-2">
                                            {{-- <div class="checklist-header-row">
                                                <div class="form-check">
                                                    <input type="checkbox" id="check_all" name="check_all"
                                                        class="form-check-input" @if($isVerified) checked disabled
                                                        @endif>
                                                    <label class="form-check-label" for="check_all">Check All</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" id="reset_all" name="reset_all"
                                                        class="form-check-input">
                                                    <label class="form-check-label" for="reset_all">Reset All</label>
                                                </div>
                                            </div> --}}
                                            <div id="specific-class" class="col-lg-12">

                                                <div class="table-responsive">
                                                    <table class="table table-bordered  table-striped align-middle">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Checklist Name</th>
                                                                <th width="10%" class="text-center">Checked</th>
                                                                <th width="25%" class="text-center">Status</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                        @forelse($checklist as $item)

                                                        <tr>

                                                            <td>

                                                                <label for="checklist_{{ $item->id }}">
                                                                    {{ $item->checklist_name }}
                                                                </label>

                                                                <input
                                                                    type="hidden"
                                                                    name="check_id[{{ $item->id }}]"
                                                                    value="{{ $item->id }}">

                                                                <input
                                                                    type="hidden"
                                                                    name="cert_name"
                                                                    value="{{ $applicant->certificate_name }}">

                                                            </td>

                                                            <td class="text-center checklist_chk">

                                                                <input
                                                                    type="checkbox"
                                                                    class="form-check-input"
                                                                    id="checklist_{{ $item->id }}"
                                                                    name="checklists[{{ $item->id }}]"
                                                                    value="1"
                                                                    {{ ($checkedList_1[$item->id] ?? 0) == 1 ? 'checked' : '' }}>
                                                            </td>

                                                            <td class="text-center">

                                                                <div class="d-flex align-items-center justify-content-center gap-2">

                                                                    <span
                                                                        id="statusText_{{ $item->id }}"
                                                                        class="badge {{ (isset($verifyList[$item->id]) ? $verifyList[$item->id] : 1) ? 'bg-success' : 'bg-danger' }}">

                                                                        {{ (isset($verifyList[$item->id]) ? $verifyList[$item->id] : 1) ? 'Correct' : 'Incorrect' }}

                                                                    </span>

                                                                    <div class="form-check form-switch">

                                                                        <input
                                                                            class="form-check-input status-switch"
                                                                            type="checkbox"
                                                                            id="status_{{ $item->id }}"
                                                                            name="status[{{ $item->id }}]"
                                                                            value="1"
                                                                            {{ (isset($verifyList[$item->id]) ? $verifyList[$item->id] : 1) ? 'checked' : '' }}>

                                                                    </div>

                                                                </div>

                                                            </td>

                                                        </tr>

                                                        @empty

                                                        <tr>
                                                            <td colspan="3" class="text-center">
                                                                No Checklist Available
                                                            </td>
                                                        </tr>

                                                        @endforelse

                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel"
                                        aria-labelledby="contact-tab" tabindex="0">
                                        <div class="row text-center fw-bold border-bottom pb-2 mb-2 mt-2 gx-0">
                                            <div class="col-lg-6 text-primary">
                                                Payment Details
                                            </div>
                                            <div class="col-lg-6 text-primary">
                                                Transaction Details
                                            </div>
                                        </div>
                                        <div class="row mt-2 gx-0">
                                            <div class="col-lg-6">
                                                <div class="row g-0">
                                                    <div class="col-lg-6">
                                                        <p><strong>Application Type</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->appl_type == 'R' ? 'Renewal Application' : ($applicant->appl_type == 'D' ? 'Digitization Application' : ($applicant->appl_type == 'A' ? 'Alteration Application' : 'New Application')) }}
                                                        </p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p><strong>Application Fees</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->amount }}.00</p>
                                                    </div>
                                                    @if (!empty($applicant->late_fees))
                                                        <div class="col-lg-6">
                                                            <p><strong>Late Fees({{ $applicant->late_months }}
                                                                    Months)</strong></p>
                                                        </div>
                                                        <div class="col-lg-6">
                                                            <p>Rs.{{ $applicant->late_fees }}.00</p>
                                                        </div>
                                                    @endif
                                                    <div class="col-lg-6">
                                                        <p><strong> Date of application</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ format_date($applicant->transaction_date) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-lg-6">
                                                <div class="row g-0">
                                                    <div class="col-lg-6">
                                                        <p><strong> Payment Status</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        @php
                                                            $paymentStatusRaw = strtoupper(trim((string) ($applicant->payment_status ?? '')));
                                                            $paymentStatusLabel = match ($paymentStatusRaw) {
                                                                'Y', 'PAYMENT', 'PAID', 'SUCCESS' => 'Success',
                                                                'N', 'DRAFT' => 'Draft',
                                                                default => $paymentStatusRaw !== '' ? $paymentStatusRaw : 'N/A',
                                                            };
                                                            $paymentStatusBadge = in_array($paymentStatusRaw, ['Y', 'PAYMENT', 'PAID', 'SUCCESS'], true)
                                                                ? 'badge-success'
                                                                : 'badge-warning';
                                                        @endphp
                                                        <p class="badge {{ $paymentStatusBadge }}">
                                                            {{ $paymentStatusLabel }}
                                                        </p>
                                                    </div>

                                                    <div class="col-lg-6">
                                                        <p><strong> Transaction Id</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->transaction_id }}</p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p><strong>Amount</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->amount }}.00</p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p><strong>Payment mode:</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->payment_mode ?? 'UPI' }}</p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p><strong> Payment Time</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ format_date($applicant->transaction_date) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-5 col-md-12 col-sm-12 col-12 layout-spacing">
                    @php
                        $role = Auth::user()->name; // Current role name
                        $workflow = [
                            'Supervisor' => $applicant->status == 'RE' ? 'Secretary' : 'Assistant Secretary',
                            'Assistant Secretary' => 'Secretary',
                            'Secretary' => 'President',
                            'President' => null, // last step
                        ];

                    @endphp
                    @if ($role == 'Secretary' || $role == 'President')

                        <div class="statbox widget box eligibile_criteria">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <div class="text-center">
                                        <h4>QC/QSC Eligiblity</h4>
                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <h6>QC</h6>

                                            </div>
                                            <div class="col-md-3 border_right">
                                                <div
                                                    class="switch form-switch-custom switch-inline form-switch-primary form-switch-custom inner-text-toggle">
                                                    <div class="input-checkbox">
                                                        <span class="switch-chk-label label-left">Yes</span>
                                                        <input class="switch-input" type="checkbox" id="qc_switch"
                                                            data-application-id="{{ $applicant->application_id }}"
                                                            role="switch" {{ $applicant->qc == 1 ? 'checked' : '' }}>
                                                        <span class="switch-chk-label label-right">No</span>
                                                    </div>
                                                </div>

                                            </div>


                                            <div class="col-md-3">
                                                <h6>QSC</h6>

                                            </div>
                                            <div class="col-md-3 ">
                                                <div
                                                    class="switch form-switch-custom switch-inline form-switch-primary form-switch-custom inner-text-toggle">
                                                    <div class="input-checkbox">
                                                        <span class="switch-chk-label label-left">Yes</span>
                                                        <input class="switch-input" type="checkbox" id="qsc_switch"
                                                            data-application-id="{{ $applicant->application_id }}"
                                                            role="switch" {{ $applicant->qsc == 1 ? 'checked' : '' }}>
                                                        <span class="switch-chk-label label-right">No</span>
                                                    </div>
                                                </div>

                                            </div>



                                        </div>


                                    </div>
                                </div>
                            </div>


                        </div>

                    @endif


                    @if(($applicant->status ?? '') != 'A')
                        <div class="statbox widget box box-shadow mb-2">
                            <div class="row align-items-center">

                                <div class="col-lg-12">
                                    <div class="switch-wrapper d-flex justify-content-between align-items-center">
                                        <label class="switch-label mb-0 fw-bold text-end" for="Queryswitch">If you have any
                                            queries</label>
                                        <div
                                            class="switch form-switch-custom switch-inline form-switch-primary form-switch-custom inner-text-toggle">
                                            <div class="input-checkbox">
                                                <span class="switch-chk-label label-left">Yes</span>
                                                <input class="switch-input" type="checkbox" id="Queryswitch" role="switch">
                                                <span class="switch-chk-label label-right">No</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="box box-shadow" id="queryOptions" style="display: none;">
                                <div class="row mt-2">
                                    <div class="col-lg-12">
                                        <div class="form-group">

                                            {{-- <label class="fw-bold">Select Query Type:</label> --}}
                                            <select class="form-control" id="queryType" name="queryType[]" multiple>
                                                <option value="general">General Query</option>
                                                <option value="technical">Technical Query</option>
                                                <option value="other">Other</option>
                                                <option value="checklist">Checklist Details Missing</option>
                                            </select>

                                            <span id="query_error" class="text-danger"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="statbox widget box box-shadow">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <div class="widget-header">
                                        <h4>Remarks</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 col-md-offset-2">
                                    <textarea class="form-control placement-top" id="remarks" name="remarks" rows="4"
                                        cols="50" maxlength="250"></textarea>
                                        <span id="remarks_error" class="text-danger small"></span>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 remarks-actions-wrap w-100">

                                @php
                                    $role = Auth::user()->name; // Current role name
                                    $workflow = [
                                        'Supervisor' =>  'Assistant Secretary',
                                        'Assistant Secretary' => 'Secretary',
                                        'Secretary' => 'President',
                                        'President' => null, // last step
                                    ];

                                @endphp

                                @if ($role == 'Supervisor')
                                    <div class="row justify-content-center">
                                        <div class="col-12">
                                            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                                {{-- Forward to Assistant Secretary --}}
                                                {{-- $isVerified == 'Yes'? '' : 'disabled' --}}
                                                <button class="btn btn-success" id="forwardbtn">
                                                    Forward to {{ $workflow[$role] }}
                                                </button>
                                                <button class="btn btn-warning">On Hold</button>
                                            </div>
                                        </div>
                                    </div>

                                @elseif ($role == 'Assistant Secretary')
                                    <div class="row justify-content-center">
                                        <div class="col-12">
                                            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                                {{-- Forward to Secretary --}}
                                                <button class="btn btn-success" id="forwardbtn" data-bs-toggle="modal"
                                                    data-bs-target="#declarationModal">
                                                    Forward to {{ $workflow[$role] }}
                                                </button>
                                                {{-- <button class="btn btn-warning">On Hold</button> --}}
                                            </div>
                                        </div>
                                    </div>

                                @elseif ($role == 'Secretary')
                                    <div class="row justify-content-center">
                                        <div class="col-12 col-xl-10">
                                            {{-- Row 1: Forward / Approve + Reject --}}
                                            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mb-2">
                                                @if ($applicant->form_name !== 'S')
                                                    <button class="btn btn-success" id="confirmApprovalBtn">
                                                        Approve
                                                    </button>
                                                @else
                                                    <button class="btn btn-success" id="confirmForwardPres">
                                                        Forward to {{ $workflow[$role] }}
                                                    </button>
                                                @endif
                                                <button class="btn btn-danger reject_application" data-bs-toggle="modal"
                                                    data-bs-target="#rejectionModal">Reject</button>
                                            </div>
                                            {{-- Row 2: Return actions (single line) --}}
                                            @if(!in_array($applicant->appl_type, ['D', 'A']))
                                            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">

                                                <button type="button" id="confirmReturnToApplicantBtn" class="btn btn-info"
                                                    data-bs-toggle="modal" data-bs-target="#returnToApplicantModal">
                                                    Return to Applicant
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </div>

                                @elseif ($role == 'President')
                                    <div class="row justify-content-center">
                                        <div class="col-12 col-xl-10">
                                            {{-- Row 1: Approve + Reject --}}
                                            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2 mb-2">
                                                <button class="btn btn-success" id="confirmApprovalBtn">
                                                    Approve
                                                </button>

                                               <button id="confirmReturnBtn" class="btn btn-warning">
                                                    Return to Secretary
                                                </button>
                                                {{-- <button class="btn btn-danger" data-bs-toggle="modal"
                                                    data-bs-target="#rejectionModal">Reject</button> --}}
                                            </div>
                                            {{-- Row 2: Return actions (single line) --}}
                                            <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                                {{-- <button id="confirmReturnBtn" class="btn btn-warning">
                                                    Return to Supervisor
                                                </button> --}}
                                                {{-- <button type="button" id="confirmReturnToApplicantBtn" class="btn btn-info"
                                                    data-bs-toggle="modal" data-bs-target="#returnToApplicantModal">
                                                    Return to Applicant
                                                </button> --}}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>
                        @include('admin.include.workflow_timeline')
                    @endif
                    <!-- ----------------------------- -->
                </div>
            </div>
        </div>
        <!-- Confirmation Modal -->
        <!-- Alert message for user -->
        <div id="alertMessage" class="alert alert-danger" style="display: none;">
            ⚠️ Please make sure all checkboxes are checked before confirming!
        </div>
        <!-- Modal -->

        <div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approvalModalLabel">Approval Declaration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="confirmApproval">
                            <label class="form-check-label" for="confirmApproval">
                                I confirm that this application has been reviewed and approved.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="confirmApprovalBtn" disabled>Approve
                            Application</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Success</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="returnMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="finalsuccessModal" tabindex="-1" aria-labelledby="finalsuccessModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="finalsuccessModalLabel">Success</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center">
                        <p id="message"></p>
                        <p><strong>License Number:</strong> <span id="licenseNumber"></span></p>
                        <a class="badge badge-primary"
                            href="{{ route('admin.generate.pdf', ['application_id' => $applicant->application_id]) }}"
                            style="color: #fff;" target="_blank">
                            <i class="fa fa-eye"></i> View
                        </a>
                        {{-- <p><strong>License Expiry:</strong> <span id="licenseExpiry"></span></p> --}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="toast-container position-fixed top-0 start-50 translate-middle-x p-3">
            <div id="queryToast" class="toast align-items-center text-white bg-danger border-0" role="alert"
                aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        You have raised a query, so you must select at least one query type.
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        </div>


        <!-- Return to Applicant Modal -->
        <div class="modal fade" id="returnToApplicantModal" tabindex="-1" aria-labelledby="returnToApplicantModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="returnToApplicantModalLabel">Return to Applicant</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="fw-bold mb-3">What document(s) are missing? (Select all that apply)</p>
                        <div class="form-group">
                            <div class="form-check mb-2">
                                <input class="form-check-input return-to-applicant-query" type="checkbox"
                                    name="return_applicant_query[]" id="query_edu_doc"
                                    value="Education document is missing">
                                <label class="form-check-label" for="query_edu_doc">Education document is
                                    missing</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input return-to-applicant-query" type="checkbox"
                                    name="return_applicant_query[]" id="query_photo" value="Photo is missing">
                                <label class="form-check-label" for="query_photo">Photo is missing</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input return-to-applicant-query" type="checkbox"
                                    name="return_applicant_query[]" id="query_signature" value="Signature is missing">
                                <label class="form-check-label" for="query_signature">Signature is missing</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input return-to-applicant-query" type="checkbox"
                                    name="return_applicant_query[]" id="query_aadhaar"
                                    value="Aadhaar document is missing">
                                <label class="form-check-label" for="query_aadhaar">Aadhaar document is missing</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input return-to-applicant-query" type="checkbox"
                                    name="return_applicant_query[]" id="query_other" value="Other">
                                <label class="form-check-label" for="query_other">Other</label>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label for="returnToApplicantRemarks" class="form-label">Remarks (optional)</label>
                            <textarea class="form-control" id="returnToApplicantRemarks" name="returnToApplicantRemarks"
                                rows="3" maxlength="250" placeholder="Add any additional remarks..."></textarea>
                        </div>
                        <p id="returnToApplicantQueryError" class="text-danger small mt-1" style="display: none;">Please
                            select at least one option.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-info" id="confirmReturnToApplicantModalBtn">Return to
                            Applicant</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="forwardmodal" tabindex="-1" aria-labelledby="declarationModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="declarationModalLabel">Declaration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="confirmPresident">
                            <label class="form-check-label" for="confirmApproval">
                                I confirm that have been verified by me as a secretary.
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="confirmForwardPres" disabled>Forward to
                            President</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="successModalForward" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Success</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Confirmation Modal -->
        <div class="modal fade" id="returnConfirmModal" tabindex="-1" aria-labelledby="returnConfirmModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header text-dark">
                        <h5 class="modal-title" id="returnConfirmModalLabel">Are you sure?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        You want to return this!
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmReturnBtn" class="btn btn-primary">Yes</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Confirmation Modal -->
        <div class="modal fade" id="rejectionModal" tabindex="-1" role="dialog" aria-labelledby="rejectionModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectionModalLabel">Are sure want to Reject..!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                            <svg> ... </svg>
                        </button>
                    </div>
                    <form id="reject_application">
                        <div class="modal-body">
                            <!-- Radio 1 + Dropdown -->
                            <div class="form-check form-check-primary">
                                <input class="form-check-input reason-option" type="radio" name="radio-reason"
                                    id="radio-select" value="select" checked>
                                <label class="form-check-label" for="radio-select">
                                    Reason
                                </label>

                                <!-- Dropdown -->
                                <select class="form-select mt-2 reason-select" name="rejection_reason">
                                    <option value="">-- Select Reason --</option>
                                    <option value="Incomplete application">Incomplete application</option>
                                    <option value="Invalid information">Invalid information</option>
                                    <option value="Eligibility criteria not met">Eligibility criteria not met</option>
                                    <option value="Supporting documents not clear">Supporting documents not clear
                                    </option>
                                    <option value="Duplicate application">Duplicate application</option>
                                    <option value="Submission deadline missed">Submission deadline missed</option>
                                    <option value="Policy violation">Policy violation</option>
                                    <option value="Fraudulent / Misleading information">Fraudulent / Misleading
                                        information</option>
                                </select>
                                <div class="invalid-feedback reason-select-error"></div>
                            </div>

                            <!-- Radio 2 + Textarea -->
                            <div class="form-check form-check-primary mt-3">
                                <input class="form-check-input reason-option" type="radio" name="radio-reason"
                                    id="radio-other" value="other">
                                <label class="form-check-label" for="radio-other">
                                    Other reason
                                </label>
                            </div>
                            <div class="form-group mb-4 reason-textarea" style="display:none;">
                                <textarea class="form-control other_reason" name="other_reason" rows="3"
                                    placeholder="Enter other reason"></textarea>
                                <div class="invalid-feedback reason-textarea-error"></div>
                            </div>
                            <input type="hidden" name="action_by" id="action_by" value="{{ $staff->name }}">
                            <input type="hidden" name="login_id" id="login_id" value="{{ $staff->id }}">
                            <input type="hidden" name="application_id" id="application_id"
                                value="{{ $applicant->application_id }}">
                            <input type="hidden" name="appl_status" id="appl_status" value="RJ">
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn btn-light-dark" data-bs-dismiss="modal"><i
                                    class="flaticon-cancel-12"></i> Cancel</button>
                            <button type="submit" class="btn btn-danger">Reject</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @php
            // var_dump($nextForwardUser);die;
        @endphp


        @include('admin.include.footer')

        <script>

            var switch_status = document.getElementById('Queryswitch');
            var queryDropdown = document.getElementById('queryType');

            function toggleQueryOptions() {
                if (switch_status.checked) {
                    document.getElementById('queryOptions').style.display = 'block';
                } else {
                    document.getElementById('queryOptions').style.display = 'none';

                    // Clear all selections (works for multi-select)
                    for (let i = 0; i < queryDropdown.options.length; i++) {
                        queryDropdown.options[i].selected = false;
                    }

                    // If using jQuery plugins like Select2 / Bootstrap-select, refresh UI too
                    if ($(queryDropdown).hasClass("select2-hidden-accessible")) {
                        $(queryDropdown).val(null).trigger("change"); // Select2 reset
                    }
                }
            }

            // Run on load
            toggleQueryOptions();

            // Run on change
            switch_status.addEventListener('change', toggleQueryOptions);

            $('#remarks').maxlength({
                placement: "top"
            });

            $(document).ready(function () {

                // var checkAllBox = $('#check_all');
                // var resetAllBox = $('#reset_all');
                var forwardbtn = $("#forwardbtn");
                var confirmForward = $("#confirmForward");
                var confirmVerification = $('#confirmVerification');
                // var individualCheckboxes = $('.form-check-input:not(#check_all):not(#reset_all)');
                var checklistStatus = [];

                $("#specific-class input[name='checklists[]']:checked").each(function () {
                    checklistStatus.push($(this).val());
                });




                //forwardbtn
                var approveButton = $('#confirmApprovalBtn');
                var confirmApproval = $('#confirmApproval');
                confirmApproval.change(function () {
                    approveButton.prop('disabled', !this.checked);
                });


                var checkPresident = $('#confirmPresident');

                confirmForwardPres = $("#confirmForwardPres");

                checkPresident.change(function () {
                    confirmForwardPres.prop('disabled', !this.checked);
                });


                // If any individual checkbox is manually unchecked, uncheck "Check All"



                approveButton.click(function () {
                    var checklistStatus = [];

                    $("#specific-class input[name='checklists[]']:checked").each(function () {
                        checklistStatus.push($(this).val());
                    });
                    var applicationId = @json($applicant->application_id);
                    var processedBy = @json(Auth::user()->name);
                    var remarks = $("#remarks").val().trim();

                    $("#remarks_error").text("");

                    if (remarks === "") {
                        $("#remarks_error").text("Remarks is required.");
                        $("#remarks").focus();
                        return;
                    }
                    var qc = $("#qc_switch").is(":checked") ? 1 : 0;
                    var qsc = $("#qsc_switch").is(":checked") ? 1 : 0;


                      // ===============================
                        // Checklist Data
                        // ===============================
                        let checklists = {};
                        let status = {};
                        let check_id = {};

                        $("#specific-class tbody tr").each(function () {

                            let checkbox = $(this).find("input[name^='checklists']");
                            let switchBtn = $(this).find("input[name^='status']");
                            let checkid = $(this).find("input[name^='check_id']");

                            let id = checkbox.attr("name").match(/\d+/)[0];
                            checklists[id] = checkbox.is(":checked") ? 1 : 0;
                            status[id] = switchBtn.is(":checked") ? 1 : 0;
                            check_id[id] = checkid.val();

                        });


                    Swal.fire({
                        title: "Declaration",
                        // html: `
                        //     <div class="form-check text-start">
                        //         <label class="form-check-label" for="confirmVerification">
                        //             I confirm that this application has been reviewed and approved.
                        //         </label>
                        //     </div>
                        // `,
                        text: 'Confirm to this application has been reviewed and approved.',
                        showCancelButton: true,
                        confirmButtonText: "Approved",
                        cancelButtonText: "Cancel",
                        focusConfirm: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ route('admin.approveApplication') }}',
                                type: 'POST',
                                headers: {
                                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                                },
                                data: {
                                    application_id: applicationId,
                                    processed_by: processedBy,
                                    remarks: remarks || "No remarks provided",
                                    qc: qc,
                                    qsc: qsc,
                                     checklists: checklists,
                                    status: status,
                                    check_id: check_id

                                },
                                success: function (response) {

                                    if (response.status == "success") {
                                        Swal.fire({
                                            icon: "success",
                                            title: "Success",
                                            html: `
                                        <p>${response.message}</p>
                                        <p><b>License Number:</b> ${response.license_number}</p>
                                    `,
                                            confirmButtonText: "OK",
                                            allowOutsideClick: false
                                        }).then(() => {
                                            window.location.href = "{{ url('admin/dashboard') }}";
                                        });
                                    }
                                    // $('#licenseExpiry').text(response.license_expiry);
                                },
                                error: function (xhr) {
                                    let errorMessage = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : "An unexpected error occurred.";
                                    $('#errorMessage').text(errorMessage);
                                    $('#errorModal').modal('show');
                                }
                            });
                        }
                    });

                });


                confirmForwardPres.click(function () {
                    var applicationId = @json($applicant->application_id);
                    var role_id = @json(Auth::user()->roles_id);
                    var forwardedTo = @json($nextForwardUser->roles_id);
                    var processedBy = @json(Auth::user()->name);
                    var role = @json($nextForwardUser->name);
                    var remarks = $("#remarks").val().trim();

                     $("#remarks_error").text("");

                        if (remarks === "") {
                            $("#remarks_error").text("Remarks is required");
                            $("#remarks").focus();
                            return;
                        }

                    var queryswitch = $("#Queryswitch").prop("checked");
                    var checkboxStatus = "Yes";

                    var qc = $("#qc_switch").is(":checked") ? 1 : 0;
                    var qsc = $("#qsc_switch").is(":checked") ? 1 : 0;

                    var queryType = null;
                    var query_status = "No";


                    if (queryswitch) {
                        queryType = $("#queryType").val() || null;
                        query_status = 'Yes';
                    }

                      // ===============================
                        // Checklist Data
                        // ===============================
                        let checklists = {};
                        let status = {};
                        let check_id = {};

                        $("#specific-class tbody tr").each(function () {

                            let checkbox = $(this).find("input[name^='checklists']");
                            let switchBtn = $(this).find("input[name^='status']");
                            let checkid = $(this).find("input[name^='check_id']");

                            let id = checkbox.attr("name").match(/\d+/)[0];
                            checklists[id] = checkbox.is(":checked") ? 1 : 0;
                            status[id] = switchBtn.is(":checked") ? 1 : 0;
                            check_id[id] = checkid.val();

                        });

                        console.log(checklists);
                        console.log(status);

                    Swal.fire({
                        title: "Declaration",
                        // html: `
                        //     <div class="form-check text-start">
                        //         <label class="form-check-label" for="confirmVerification">
                        //             I confirm that have been verified by me as a secretary.
                        //         </label>
                        //     </div>
                        // `,
                        text: 'Confirm that have been verified.',
                        showCancelButton: true,
                        confirmButtonText: "Forward to President",
                        cancelButtonText: "Cancel",
                        focusConfirm: false,
                    }).then((result) => {
                        if (result.isConfirmed) {

                            $.ajax({
                                url: '{{ route('admin.forwardApplication', ["role" => "__ROLE__"]) }}'.replace('__ROLE__', role),
                                type: 'POST',
                                headers: {
                                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                                },
                                data: {
                                    application_id: applicationId,
                                    processed_by: processedBy,
                                    forwarded_to: forwardedTo,
                                    role_id: role_id,
                                    remarks: remarks || "No remarks provided",
                                    checkboxes: checkboxStatus, // Only "Yes" or "No"
                                    queryswitch: query_status, // Only "Yes" or "No"
                                    "queryType[]": queryType,
                                    qc: qc,
                                    qsc: qsc,
                                    checklists: checklists,
                                    status: status,
                                    check_id: check_id
                                },
                                success: function (response) {

                                    if (response.status == "success") {
                                        Swal.fire({
                                            icon: "success",
                                            title: "Success",
                                            text: response.message,
                                            confirmButtonText: "OK",
                                            allowOutsideClick: false
                                        }).then(() => {
                                            window.location.href = "{{ url('admin/dashboard') }}";
                                        });
                                    }

                                },
                                error: function (xhr) {
                                    let errorMessage = xhr.responseJSON && xhr.responseJSON.error
                                        ? xhr.responseJSON.error
                                        : "An unexpected error occurred.";
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: errorMessage
                                    });
                                }
                            });
                        }
                    });

                });




                forwardbtn.click(function () {
                var role = @json(Auth::user()->roles_id);
                   var buttonText = role == 2
                    ? 'Forward to Secretary'
                    : 'Forward to Assistant Secretary';

                    Swal.fire({
                        title: "Declaration",
                        text: "Confirm that all documents have been verified ",
                        showCancelButton: true,
                        confirmButtonText: buttonText,
                        cancelButtonText: "Cancel",
                        focusConfirm: false,
                    }).then((result) => {

                        if (!result.isConfirmed) {
                            return;
                        }

                        var applicationId = @json($applicant->application_id);
                        var processedBy = @json(Auth::user()->name);
                        var role_id = @json(Auth::user()->roles_id);
                        var forwardedTo = @json($nextForwardUser->roles_id);
                        var role = @json($nextForwardUser->name);

                        var remarks = $("#remarks").val().trim();

                         $("#remarks_error").text("");

                        if (remarks === "") {
                            $("#remarks_error").text("Remarks is required");
                            $("#remarks").focus();
                            return;
                        }

                        var qc = $("#qc_switch").is(":checked") ? 1 : 0;
                        var qsc = $("#qsc_switch").is(":checked") ? 1 : 0;

                        var checkboxStatus = "Yes";

                        var queryswitch = $("#Queryswitch").is(":checked");
                        var queryType = $("#queryType").val() || [];

                        if (queryswitch && queryType.length === 0) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Please select at least one query type."
                            });
                            return;
                        }

                        // ===============================
                        // Checklist Data
                        // ===============================
                        let checklists = {};
                        let status = {};
                        let check_id = {};

                        $("#specific-class tbody tr").each(function () {

                            let checkbox = $(this).find("input[name^='checklists']");
                            let switchBtn = $(this).find("input[name^='status']");
                            let checkid = $(this).find("input[name^='check_id']");

                            let id = checkbox.attr("name").match(/\d+/)[0];
                            checklists[id] = checkbox.is(":checked") ? 1 : 0;
                            status[id] = switchBtn.is(":checked") ? 1 : 0;
                            check_id[id] = checkid.val();

                        });

                        console.log(checklists);
                        console.log(status);

                        $.ajax({

                            url: '{{ route("admin.forwardApplication", ["role" => "__ROLE__"]) }}'.replace("__ROLE__", role),

                            type: "POST",

                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                            },

                            data: {

                                application_id: applicationId,
                                processed_by: processedBy,
                                forwarded_to: forwardedTo,
                                role_id: role_id,

                                remarks: remarks,

                                checkboxes: checkboxStatus,

                                queryswitch: queryswitch ? "Yes" : "No",

                                queryType: queryType,

                                qc: qc,
                                qsc: qsc,

                                checklists: checklists,
                                status: status,
                                check_id: check_id

                            },

                            success: function (response) {

                                if (response.status == "success") {

                                    Swal.fire({
                                        icon: "success",
                                        title: "Success",
                                        text: response.message,
                                        confirmButtonText: "OK",
                                        allowOutsideClick: false
                                    }).then(() => {
                                        window.location.href = "{{ url('admin/dashboard') }}";
                                    });

                                }

                            },

                            error: function (xhr) {

                                console.log(xhr.responseText);

                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: "Something went wrong."
                                });

                            }

                        });

                    });

                });
                // confirmForward.click(function() {

                //     var queryType = [];

                //     var applicationId = @json($applicant->application_id);
                //     var processedBy = @json(Auth::user()->name);
                //     var role_id = @json(Auth::user()->roles_id);
                //     var forwardedTo = @json($nextForwardUser->roles_id);
                //     var role = @json($nextForwardUser->name);
                //     var remarks = $("#remarks").val().trim();

                //     var checkboxStatus = "Yes";

                //     let queryswitch = $("#Queryswitch").prop("checked");
                //     queryType = $("#queryType").val();
                //     let errorBox = $("#query_error");

                //     errorBox.text(""); // clear previous error

                //     if (queryswitch && queryType.length === 0) {
                //         errorBox.text("Please select at least one query type.");
                //         $('#declarationModal').modal('hide');

                //         setTimeout(function () {
                //             let errorTop = errorBox.offset().top - 100;
                //             let currentScroll = $(window).scrollTop();

                //             $('html, body').animate({ scrollTop: errorTop }, 500);
                //         }, 300);

                //         return;
                //     }

                //     $.ajax({
                //         url: '{{ route('admin.forwardApplication', ["role" => "__ROLE__"]) }}'.replace('__ROLE__', role),
                //         type: 'POST',
                //         // contentType: 'application/json',
                //         headers: {
                //             "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                //         },
                //         data: {
                //             application_id: applicationId,
                //             processed_by: processedBy,
                //             forwarded_to: forwardedTo,
                //             role_id: role_id,
                //             remarks: remarks || "No remarks provided",
                //             checkboxes: checkboxStatus, // Only "Yes" or "No"
                //             queryswitch: queryswitch ? "Yes" : "No", // Only "Yes" or "No"
                //             "queryType[]": queryType
                //         },
                //         success: function(response) {

                //             // if (response.status == "success") {
                //             //     // Cleanup Bootstrap modal instance on hide
                //             //     $('#declarationModal').modal('hide');

                //             //     $('#successModal .modal-body').html(`<p>${response.message}</p>`);
                //             //     $('#successModal').modal('show');

                //             //     $('#successModal').on('hidden.bs.modal', function() {
                //             //         window.location.href = '/admin/dashboard'
                //             //     });
                //             // }

                //             if (response.status == "success") {
                //                 Swal.fire({
                //                     icon: 'success',
                //                     title: 'Success',
                //                     text: response.message,
                //                     confirmButtonText: 'OK',
                //                     confirmButtonColor: '#3085d6',
                //                     allowOutsideClick: false
                //                 }).then((result) => {
                //                     if (result.isConfirmed) {
                //                         window.location.href = '/admin/dashboard';
                //                     }
                //                 });
                //             }

                //         },
                //         error: function(xhr) {
                //             let errorMessage = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : "An unexpected error occurred.";
                //             $('#errorModal .modal-body').html(`<p>${errorMessage}</p>`);
                //             $('#errorModal').modal('show');
                //         }
                //     });
                // });

                //
                // var returnButton = document.querySelector('#returntoSuper');

                // if (returnButton) {
                //     returnButton.addEventListener('click', function () {
                //         // Show Bootstrap confirmation modal
                //         $('#returnConfirmModal').modal('show');
                //     });
                // }

                // Handle confirm button inside modal
                $('#confirmReturnBtn').on('click', function () {

                    var queryType = [];

                    var applicationId = @json($applicant->application_id);
                    var returnBy = @json(Auth::user()->name);
                    var forwardedTo = 3; // Assuming 3 is the role ID for Secretary
                    var remarks = $("#remarks").val().trim();
                    // var queryswitch     = $("#Queryswitch").prop("checked");

                     var remarks = $("#remarks").val().trim();

                        $("#remarks_error").text("");

                        if (remarks === "") {
                            $("#remarks_error").text("Remarks is required");
                            $("#remarks").focus();
                            return;
                        }

                         // ===============================
                        // Checklist Data
                        // ===============================
                        let checklists = {};
                        let status = {};
                        let check_id = {};

                        $("#specific-class tbody tr").each(function () {

                            let checkbox = $(this).find("input[name^='checklists']");
                            let switchBtn = $(this).find("input[name^='status']");
                            let checkid = $(this).find("input[name^='check_id']");

                            let id = checkbox.attr("name").match(/\d+/)[0];
                            checklists[id] = checkbox.is(":checked") ? 1 : 0;
                            status[id] = switchBtn.is(":checked") ? 1 : 0;
                            check_id[id] = checkid.val();

                        });


                    var checkboxStatus = "Yes";

                    let queryswitch = $("#Queryswitch").prop("checked");
                    queryType = $("#queryType").val();
                    let errorBox = $("#query_error");


                    Swal.fire({
                        title: "Return",
                        html: 'Confirm to return this application!',
                        showCancelButton: true,
                        confirmButtonText: "Forward to {{ 'Secretary' }}",
                        cancelButtonText: "Cancel",
                        focusConfirm: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: '{{ route('admin.returntoSecretary') }}',
                                type: 'POST',
                                headers: {
                                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                                },
                                data: {
                                    application_id: applicationId,
                                    return_by: returnBy,
                                    forwarded_to: forwardedTo,
                                    remarks: remarks || "No remarks provided",
                                    checkboxes: checkboxStatus,
                                    queryswitch: queryswitch ? "Yes" : "No",
                                    "queryType[]": queryType,
                                      checklists: checklists,
                                        status: status,
                                        check_id: check_id
                                },
                                success: function (response) {
                                    if (response.status == "success") {
                                        Swal.fire({
                                            icon: "success",
                                            title: "Success",
                                            text: response.message,
                                            confirmButtonText: "OK",
                                            allowOutsideClick: false
                                        }).then(() => {
                                            window.location.href = "{{ url('admin/dashboard') }}";
                                        });
                                    }
                                },
                                error: function (xhr) {
                                    let errorMessage = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : "An unexpected error occurred.";
                                    $('#errorMessage').text(errorMessage);
                                    $('#errorModal').modal('show');
                                }
                            });
                        }
                    });

                });


                // Return to Applicant modal: validate and call API
                $('#confirmReturnToApplicantModalBtn').on('click', function () {
                    var selected = [];
                    $('.return-to-applicant-query:checked').each(function () { selected.push($(this).val()); });
                    var remarks = $('#returnToApplicantRemarks').val().trim();
                    var staff_remarks = $('#remarks').val().trim();
                    var staff_queryType = $("#queryType").val();
                    $('#returnToApplicantQueryError').hide();
                    if (selected.length === 0) {
                        $('#returnToApplicantQueryError').show();
                        return;
                    }
                    var applicationId = @json($applicant->application_id);
                    $.ajax({
                        url: '{{ route('admin.returnToApplicant') }}',
                        type: 'POST',
                        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                        data: {
                            application_id: applicationId,
                            'return_applicant_query[]': selected,
                            remarks: remarks,
                            staff_remarks: staff_remarks,
                            "staff_queryType[]": staff_queryType
                        },
                        success: function (response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.message,
                                    confirmButtonText: 'OK',
                                    allowOutsideClick: false
                                }).then(function () {
                                    $('#returnToApplicantModal').modal('hide');
                                    $('.return-to-applicant-query').prop('checked', false);
                                    $('#returnToApplicantRemarks').val('');
                                    window.location.href = "{{ url('admin/dashboard') }}";
                                });
                            }
                        },
                        error: function (xhr) {
                            var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : (xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'An unexpected error occurred.');
                            Swal.fire({ icon: 'error', title: 'Error', text: msg });
                        }
                    });
                });

                $(document).on("click", ".admin_verify", function () {
                    let btn = $(this);
                    let licenseNumber = btn.data("license_number");
                    let licenseDate = btn.data("license_date");
                    let type = btn.data("type");
                    let label = (type === "certificate") ? "Certificate" : "License";

                    $.ajax({
                        url: "{{ route('admin.verifylicense') }}",
                        method: "POST",
                        data: {
                            license_number: licenseNumber,
                            date: licenseDate,
                            type: type,
                            _token: $('meta[name="csrf-token"]').attr("content"),
                        },
                        success: function (response) {
                            btn.hide();

                            if (response.exists) {
                                btn.after('<span class="text-success ms-2 small fw-semibold">(Valid ' + label + ')</span>');
                            } else {
                                btn.after('<span class="text-danger ms-2 small fw-semibold">(Invalid ' + label + ')</span>');
                            }
                        },
                        error: function () {
                            btn.hide();
                            btn.after('<span class="text-danger ms-2 small fw-semibold">Verification failed.</span>');
                        },
                    });
                });

            });




            $(".reason-option").on("change", function () {
                if ($(this).val() === "select") {
                    $(".reason-select").show();
                    $(".reason-textarea").hide();
                    $("textarea[name='other_reason']").val("");

                    $(".reason-textarea-error").text("");
                    $("textarea[name='other_reason']").removeClass("is-invalid");

                } else if ($(this).val() === "other") {
                    $(".reason-textarea").show();
                    $(".reason-select").hide().val(""); // reset select

                    // reset errors
                    $(".reason-select-error").text("");
                    $(".reason-select").removeClass("is-invalid");
                }
            });

            // Initialize on page load
            $(".reason-option:checked").trigger("change");


            // Hide validation error on change/typing
            $(document).on("change", ".reason-select", function () {
                if ($(this).val() !== "") {
                    $(this).removeClass("is-invalid");
                    $(this).siblings(".reason-select-error").text("");
                }
            });

            $(document).on("input", "textarea[name='other_reason']", function () {
                if ($(this).val().trim() !== "") {
                    $(this).removeClass("is-invalid");
                    $(this).siblings(".reason-textarea-error").text("");
                }
            });


            $("#reject_application").on("submit", function (e) {
                e.preventDefault();


                const rejectAppUrl = "{{ route('admin.rejectApplication') }}";
                const APP_URL = "{{ config('app.url') }}";

                // clear old errors
                $(".form-select, textarea").removeClass("is-invalid");
                $(".invalid-feedback").text("");
                $("#successMsg").addClass("d-none");

                let selectedOption = $("input[name='radio-reason']:checked").val();
                let valid = true;
                let formData = {
                    action_by: $("#action_by").val(),
                    login_id: $("#login_id").val(),
                    application_id: $("#application_id").val(),
                    appl_status: $("#appl_status").val(),
                    _token: "{{ csrf_token() }}" // important for Laravel
                };

                if (selectedOption === "select") {
                    let reason = $(".reason-select").val();
                    if (reason === "") {
                        $(".reason-select").addClass("is-invalid");
                        $(".reason-select").siblings(".reason-select-error").text("Please select a reason.");
                        valid = false;
                    } else {
                        formData.reason = reason;
                    }
                } else if (selectedOption === "other") {
                    let other = $("textarea[name='other_reason']").val().trim();
                    if (other === "") {
                        $("textarea[name='other_reason']").addClass("is-invalid");
                        $("textarea[name='other_reason']").siblings(".reason-textarea-error").text("Please enter the other reason.");
                        valid = false;
                    } else {
                        formData.reason = other;
                    }
                }

                if (!valid) return;

                // AJAX request
                $.ajax({
                    url: rejectAppUrl,
                    type: "POST",
                    data: formData,
                    success: function (response) {
                        if (response.success === true) {
                            $("#rejectionModal").modal("hide");
                            Swal.fire({
                                icon: 'success',
                                title: 'Rejected successfully',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                window.location.href = APP_URL + "/admin/dashboard"; // redirect URL
                            });
                        } else {
                            $("#rejectionModal").modal("hide");
                            Swal.fire('Something went wrong', '', 'error');
                        }
                    },
                    error: function (xhr) {
                        Swal.fire('Server error occurred', '', 'error').then(() => {
                            $("#rejectionModal").modal("hide");
                            //window.location.href = "/admin/dashboard"; // redirect path
                        });
                    }
                });
            });



            $(".status-switch").on("change", function () {

                let id = this.id.replace("status_", "");
                let badge = $("#statusText_" + id);

                if ($(this).is(":checked")) {
                    badge
                        .removeClass("bg-danger")
                        .addClass("bg-success")
                        .text("Correct");
                } else {
                    badge
                        .removeClass("bg-success")
                        .addClass("bg-danger")
                        .text("Incorrect");
                }

            });

        </script>
