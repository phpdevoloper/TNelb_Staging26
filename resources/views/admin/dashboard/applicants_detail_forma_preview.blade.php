@section('title', 'Application ' . $applicant->application_id)
@include('admin.include.top')
@include('admin.dashboard.partials.applicants_detail_forma_styles')
<style>
    :root {
        --pv-ink: #1f2937;
        --pv-muted: #475569;
        --pv-navy: #0f2e5c;
        --pv-green: #15803d;
        --pv-line: #dfe7f0;
        --pv-band: #e8f1fb;
    }

    body {
        background: #f4f7fb !important;
        font-family: Inter, 'Segoe UI', Arial, sans-serif;
        color: var(--pv-ink);
    }
    body,
    .preview-sheet,
    .preview-sheet * {
        letter-spacing: normal !important;
    }

    .pv-header {
        background: linear-gradient(90deg, #0e2f57, #1a4d8c);
        color: #fff;
        padding: 14px 24px;
    }
    .pv-header .pv-org {
        font-size: 16px;
        font-weight: 700;
    }
    .pv-header .pv-sub {
        font-size: 12px;
        opacity: 0.85;
    }

    .preview-sheet {
        max-width: 1040px;
        margin: 0 auto;
        padding: 24px 28px 48px;
        font-size: 14px;
        line-height: 1.55;
    }
    .pv-header {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .pv-header .pv-logo {
        width: 48px;
        height: 48px;
        object-fit: contain;
        background: #fff;
        border-radius: 50%;
        padding: 3px;
    }
    .preview-sheet .pv-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 6px 0 14px;
    }
    .preview-sheet h1.pv-title {
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        color: var(--pv-navy);
    }
    .preview-sheet h1.pv-title::before {
        content: "\f15c";
        font-family: FontAwesome;
        display: inline-block;
        margin-right: 10px;
        color: var(--pv-green);
    }
    .preview-sheet .pv-back,
    .preview-sheet .pv-back i {
        display: inline-flex !important;
        align-items: center;
        gap: 6px;
    }
    .preview-sheet .pv-back {
        border: 1px solid #cbd5e1;
        background: #fff;
        color: var(--pv-navy) !important;
        border-radius: 8px;
        padding: 7px 14px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none !important;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }
    .pv-print-footer {
        display: none;
    }
    .preview-sheet .pv-print {
        display: inline-flex !important;
        border: 1px solid var(--pv-navy);
        background: var(--pv-navy);
        color: #fff;
        border-radius: 8px;
        padding: 7px 16px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
    }
    .preview-sheet .pv-print:hover {
        background: #1a4d8c;
    }

    /* Hide controls and icons */
    .preview-sheet .nav-tabs,
    .preview-sheet button,
    .preview-sheet .btn,
    .preview-sheet input,
    .preview-sheet .form-check,
    .preview-sheet .form-switch,
    .preview-sheet .verify-result,
    .preview-sheet .modal,
    .preview-sheet #profile-tab-pane {
        display: none !important;
    }

    .preview-sheet .tab-content {
        padding: 0;
    }
    .preview-sheet .tab-content > .tab-pane {
        display: block !important;
        opacity: 1 !important;
    }
    .preview-sheet .statbox,
    .preview-sheet .widget-content,
    .preview-sheet .widget-header {
        box-shadow: none !important;
        border: 0 !important;
        background: transparent;
        padding: 0;
    }

    /* Applicant strip */
    .preview-sheet .applicant_details {
        background: #fff;
        border: 1px solid #d5e6d9;
        border-radius: 10px;
        padding: 14px 18px;
        margin-bottom: 18px;
        text-align: center;
    }
    .preview-sheet .applicant_details h4 {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: var(--pv-muted);
        line-height: 2.1;
    }
    .preview-sheet .applicant_details h4 span {
        font-size: 16px;
        font-weight: 800;
        color: var(--pv-green) !important;
    }

    /* Section title */
    .preview-sheet #tabsSimple .widget-header {
        border-bottom: 0 !important;
        background: var(--pv-band) !important;
        border-radius: 8px;
        padding: 10px 14px !important;
        margin-bottom: 12px;
    }
    .preview-sheet #tabsSimple .widget-header h4 {
        margin: 0;
        font-size: 17px;
        font-weight: 800;
        color: var(--pv-navy);
    }
    .preview-sheet #tabsSimple .widget-header h4::before {
        content: "\f0f6";
        font-family: FontAwesome;
        display: inline-block;
        margin-right: 10px;
        color: var(--pv-green);
    }

    /* Numbered question headings */
    .preview-sheet p.text-info,
    .preview-sheet p.fw-bold,
    .preview-sheet h6 {
        margin: 18px 0 10px;
        padding: 8px 12px;
        border-radius: 8px;
        background: var(--pv-band);
        font-size: 15px;
        font-weight: 700;
        color: var(--pv-navy) !important;
    }
    .pv-num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 30px;
        height: 24px;
        padding: 0 6px;
        margin-right: 10px;
        border-radius: 6px;
        background: var(--pv-green);
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        vertical-align: middle;
    }

    /* Label / value rows */
    .preview-sheet .row {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -8px;
    }
    .preview-sheet .row > [class*="col-"] {
        padding: 10px 8px;
    }
    .preview-sheet .row > [class*="col-"] p {
        margin: 0;
    }
    .preview-sheet .row strong {
        color: var(--pv-muted);
        font-weight: 600;
    }
    .preview-sheet .row,
    .preview-sheet .row p,
    .preview-sheet .row strong {
        font-size: 14px;
    }
    .preview-sheet .col-lg-2 { flex: 0 0 22%; max-width: 22%; }
    .preview-sheet .col-lg-3 { flex: 0 0 32%; max-width: 32%; }
    .preview-sheet .col-lg-4 { flex: 0 0 36%; max-width: 36%; }
    .preview-sheet .col-lg-6 { flex: 0 0 50%; max-width: 50%; }
    .preview-sheet .col-lg-8 { flex: 0 0 66.66%; max-width: 66.66%; }
    .preview-sheet .col-lg-12,
    .preview-sheet .col-12 { flex: 0 0 100%; max-width: 100%; }
    .preview-sheet .row:has(> .col-lg-8):has(> .col-lg-4, > .col-lg-2) > .col-lg-8 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    .preview-sheet .row:has(> .col-lg-8):has(> .col-lg-4, > .col-lg-2) > [class*="col-"]:not(.col-lg-8) {
        flex: 1 1 auto;
        max-width: none;
        padding-top: 0;
    }

    /* Tables */
    .preview-sheet .table-responsive,
    .preview-sheet .apd-table-wrap,
    .preview-sheet [style*="overflow"] {
        overflow: visible !important;
    }
    .preview-sheet table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
        margin: 8px 0 18px;
        font-size: 12.5px;
        table-layout: auto;
        border: 1px solid var(--pv-line);
        border-radius: 8px;
        overflow: hidden;
    }
    .preview-sheet th,
    .preview-sheet td {
        border-bottom: 1px solid var(--pv-line) !important;
        border-right: 1px solid var(--pv-line) !important;
        border-top: 0 !important;
        border-left: 0 !important;
        padding: 9px 10px !important;
        vertical-align: middle;
        text-align: left;
        white-space: normal !important;
        min-width: 80px;
    }
    .preview-sheet th {
        background: #dcf3e5 !important;
        color: #14532d !important;
        font-weight: 600 !important;
        text-align: center;
        overflow-wrap: normal;
        word-break: normal;
    }
    .preview-sheet td {
        background: #fff;
        color: var(--pv-ink);
        font-weight: 500;
        overflow-wrap: break-word;
    }
    .preview-sheet tbody tr:nth-child(even) td {
        background: #f8fbf9;
    }
    .preview-sheet tbody tr:last-child td {
        border-bottom: 0 !important;
    }

    /* Badges */
    .preview-sheet .badge {
        background: none !important;
        color: var(--pv-ink) !important;
        border: 0 !important;
        padding: 0 !important;
        font-size: inherit !important;
        font-weight: 700;
    }
    .preview-sheet .badge.badge-success {
        background: var(--pv-green) !important;
        color: #fff !important;
        padding: 3px 12px !important;
        border-radius: 999px;
    }

    .preview-sheet .col-lg-6:has(> .row > h6) {
        flex: 0 0 calc(50% - 8px);
        max-width: calc(50% - 8px);
        border: 1px solid var(--pv-line);
        border-radius: 10px;
        background: #fff;
        padding: 0 0 10px;
        margin: 6px 4px;
        overflow: hidden;
    }
    .preview-sheet .col-lg-6:has(> .row > h6) > .row {
        margin: 0;
        padding: 0 14px;
    }
    .preview-sheet .col-lg-6:has(> .row > h6) > .row:first-child h6 {
        margin: 0;
        border-radius: 0;
        padding: 10px 14px;
        background: #dcf3e5;
        color: #14532d !important;
    }
    .preview-sheet i.fa {
        display: inline-block !important;
        color: #1d4ed8;
        margin-right: 4px;
    }
    .preview-sheet a {
        color: #1d4ed8;
        font-weight: 600;
        text-decoration: none;
    }
    .preview-sheet a:hover {
        text-decoration: underline;
    }

    @media print {
        body {
            background: #fff !important;
        }
        .pv-print {
            display: none !important;
        }
        .preview-sheet {
            padding-bottom: 16mm;
        }
        .pv-print-footer {
            display: block;
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 0 12mm 4mm;
            font-size: 9px;
            color: #475569;
            text-align: right;
        }
        .pv-header {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .preview-sheet {
            max-width: none;
            margin: 0;
            padding: 0;
        }
        .preview-sheet .tab-pane,
        .preview-sheet table,
        .preview-sheet tr,
        .preview-sheet .row {
            page-break-inside: avoid;
        }
        @page {
            size: A4;
            margin: 12mm;
        }
    }
</style>

<div class="pv-header">
    <img class="pv-logo" src="{{ asset('assets/admin/images/logo/logo.png') }}" alt="">
    <div>
        <div class="pv-org">Tamil Nadu Electrical Licensing Board</div>
        <div class="pv-sub">Government of Tamil Nadu</div>
    </div>
</div>

<div class="preview-sheet">
    <div class="pv-topbar">
        <h1 class="pv-title">Application Preview</h1>
        <button type="button" class="pv-print" onclick="window.print()"><i class="fa fa-print" style="color: #fff;margin-top: 4px;"></i> Print</button>
    </div>
    @include('admin.dashboard.partials.applicants_detail_forma_body')
</div>

<div class="pv-print-footer">{{ now()->format('d-m-Y') }}</div>

<script>
    (function () {
        var root = document.querySelector('.preview-sheet');
        if (!root) return;
        var pattern = /^(\s*)(\d+[A-Za-z]?)\s?\.(\s+|$)/;
        var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
        var nodes = [];
        while (walker.nextNode()) nodes.push(walker.currentNode);
        nodes.forEach(function (node) {
            var parent = node.parentNode;
            if (!parent || parent.closest('th, td')) return;
            var match = pattern.exec(node.nodeValue);
            if (!match) return;
            var badge = document.createElement('span');
            badge.className = 'pv-num';
            badge.textContent = match[2];
            parent.insertBefore(badge, node);
            node.nodeValue = node.nodeValue.slice(match[0].length);
        });
    })();
</script>
</body>
</html>
