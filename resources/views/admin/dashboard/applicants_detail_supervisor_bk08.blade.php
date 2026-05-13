@include('admin.include.top')
@include('admin.include.header')
@include('admin.include.navbar')
<style>
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
    .applicant-supervisor-page .applicant_details h4 > span {
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
    .applicant-supervisor-page .applicant-detail-compact-table tbody tr:first-child td { border-top: none; }
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
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 0.75rem;
        row-gap: 0.1rem;
        padding: 0.25rem 0.25rem 0.5rem;
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
</style>
<div id="content" class="main-content applicant-supervisor-page">
    <div class="layout-px-spacing">
        <div class="middle-content container-xxl p-0">
            <div class="secondary-nav">
                <div class="breadcrumbs-container" data-page-heading="Analytics">
                    <header class="header navbar navbar-expand-sm">
                        <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu">
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
                        <div class="widget-header applicant_details">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <h4>
                                        <strong>Applicant Id:</strong> <span>{{ $applicant->application_id }}</span>
                                        <strong>Name:</strong> <span>{{ $applicant->applicant_name }}</span>
                                        <strong>D.O.B:</strong> <span>{{ format_date($applicant->d_o_b) }} &middot; {{ $applicant->age }} yrs</span>
                                        <strong>Applied For:</strong> <span>FORM {{ $applicant->form_name }} &middot; License {{ $applicant->license_name }}</span>
                                    </h4>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div id="tabsSimple" class="col-xl-7 col-md-12 col-sm-12 col-12 layout-spacing">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    {{-- <h3 class="application_id_css">Application Id :<span style="color:#098501;"> {{ $applicant->application_id }}</span> </h3> --}}
                                    {{-- <h4>Edit / View Applicant's Details</h4> --}}
                                </div>
                            </div>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="simple-tab">
                                <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Personal Details</button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link " id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="false">Payment Status</button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Check List</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                                        <div class="row mt-3 ">
                                            <div class="row">
                                                <!-- Left Side: Applicant Details -->
                                                <div class="col-md-8">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm">
                                                            <tbody>
                                                                <tr>
                                                                    <td class="fw-bold " style="width: 30%;">Applicant Id :</td>
                                                                    <td>{{ $applicant->application_id }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold">Applicant Name :</td>
                                                                    <td>{{ $applicant->applicant_name }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold">Father's Name :</td>
                                                                    <td>{{ $applicant->fathers_name }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold align-top">Address :</td>
                                                                    <td style="white-space: normal; word-break: break-word;">
                                                                        {{ $applicant->applicants_address }}
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="fw-bold">D.O.B & Age :</td>
                                                                    <td>{{ $applicant->d_o_b }} ({{ $applicant->age }} years old)</td>
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
                                                    @endphp
                                                    @php
                                                        //var_dump($photo->upload_path);die;
                                                    @endphp
                                                    @if($photoPath)
                                                        <div class="asp-photo-frame">
                                                            <img src="{{ asset($photoPath) }}"
                                                                 alt="Applicant Photo"
                                                                 class="img-fluid"
                                                                 style="width: 140px; height: 180px; object-fit: cover;"
                                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                            <p class="text-muted mb-0" style="display: none;">No photo available</p>
                                                        </div>
                                                    @else
                                                        <p class="text-muted">No photo available</p>
                                                    @endif

                                                    <div class="mt-3">
                                                        @if($signPath)
                                                            <div class="asp-signature-frame">
                                                                <img src="{{ asset($signPath) }}"
                                                                     alt="Applicant Signature"
                                                                     class="img-fluid"
                                                                     style="width: 110px; height: 50px; object-fit: contain; background: #fff;"
                                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                                <p class="text-muted mb-0" style="display: none;">No signature available</p>
                                                            </div>
                                                        @else
                                                            <p class="text-muted mb-0">No signature available</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <h6 class="asp-section-title">Educational Qualifications</h6>
                                            <div class="applicant-detail-table-wrap">
                                                <table class="table table-sm table-bordered applicant-detail-compact-table edu-qual-table">
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
                                                                '01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr',
                                                                '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Aug',
                                                                '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec',
                                                            ];
                                                        @endphp
                                                        @forelse ($educationalQualifications as $education)
                                                        @php
                                                            $rawMonth = trim((string) ($education->month_passing ?? ''));
                                                            $monthKey = $rawMonth !== '' ? str_pad($rawMonth, 2, '0', STR_PAD_LEFT) : '';
                                                            $monthDisplay = $monthLabels[$monthKey] ?? ($rawMonth !== '' ? $rawMonth : '-');
                                                        @endphp
                                                        <tr>
                                                            <td class="col-wrap">{{ $education->educational_level }}</td>
                                                            <td class="col-wrap">{{ $education->institute_name }}</td>
                                                            <td class="col-wrap">{{ $monthDisplay }}</td>
                                                            <td class="col-wrap">{{ !empty($education->year_of_passing) ? $education->year_of_passing : '-' }}</td>
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
                                                                @if(!empty($education->upload_document))
                                                                    @php
                                                                        $fileExtension = strtolower(pathinfo($education->upload_document ?? 'unknown.pdf', PATHINFO_EXTENSION));
                                                                    @endphp
                                                                    @if(\in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'], true))
                                                                        <a href="{{ url($education->upload_document) }}" target="_blank" rel="noopener noreferrer" title="View image">
                                                                            <img src="{{ url($education->upload_document) }}" alt="" class="doc-thumb">
                                                                        </a>
                                                                    @elseif($fileExtension === 'pdf')
                                                                        <a href="{{ url($education->upload_document) }}" target="_blank" rel="noopener noreferrer" class="doc-pdf-link text-primary" title="View document">
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
                                                            <td colspan="6" class="text-center">No educational details available.</td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                           
                                            @if (in_array(($applicant->form_name ?? ''), ['S', 'W'], true))
                                                @php
                                                    $isFormS = (($applicant->form_name ?? '') === 'S');
                                                    $hasContractorRow = false;
                                                    if ($isFormS && !empty($workExperience)) {
                                                        foreach ($workExperience as $__exp) {
                                                            if (strtolower(trim((string) ($__exp->emp_type ?? ''))) === 'contractor') {
                                                                $hasContractorRow = true;
                                                                break;
                                                            }
                                                        }
                                                    }
                                                    $expColspan = $isFormS
                                                        ? (7 + ($hasContractorRow ? 1 : 0))
                                                        : 3;
                                                @endphp
                                                <h6 class="asp-section-title">Work Experience</h6>
                                                <div class="applicant-detail-table-wrap">
                                                    <table class="table table-sm table-bordered applicant-detail-compact-table work-exp-table {{ $isFormS ? 'work-exp-with-doc' : '' }}">
                                                        <thead>
                                                            @if ($isFormS)
                                                                <tr>
                                                                    <th rowspan="2">S.No</th>
                                                                    <th rowspan="2">Employment Type</th>
                                                                    <th rowspan="2">Employer / Organization</th>
                                                                    @if ($hasContractorRow)
                                                                        <th rowspan="2">Intimation Date</th>
                                                                    @endif
                                                                    <th colspan="3">Year of Experience</th>
                                                                    <th rowspan="2">Designation</th>
                                                                    <th rowspan="2">Document Upload</th>
                                                                </tr>
                                                                <tr>
                                                                    <th>From (Date)</th>
                                                                    <th>To (Date)</th>
                                                                    <th>Total Yrs</th>
                                                                </tr>
                                                            @else
                                                                <tr>
                                                                    <th>Company</th>
                                                                    <th>Designation</th>
                                                                    <th>Exp.</th>
                                                                </tr>
                                                            @endif
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($workExperience as $index => $experience)
                                                            <tr>
                                                                @if ($isFormS)
                                                                    @php
                                                                        $empType = $experience->emp_type ?? '';
                                                                        $empTypeLabel = $empType !== '' ? ucwords(str_replace('_', ' ', $empType)) : '-';
                                                                        $isContractor = strtolower(trim((string) $empType)) === 'contractor';
                                                                        $fromDate = !empty($experience->from_date) ? \Carbon\Carbon::parse($experience->from_date)->format('d-m-Y') : '-';
                                                                        $toDate = !empty($experience->to_date) ? \Carbon\Carbon::parse($experience->to_date)->format('d-m-Y') : '-';
                                                                        $intimationDate = !empty($experience->intimation_date) ? \Carbon\Carbon::parse($experience->intimation_date)->format('d-m-Y') : '-';
                                                                    @endphp
                                                                    <td class="col-wrap">{{ $index + 1 }}</td>
                                                                    <td class="col-wrap">{{ $empTypeLabel }}</td>
                                                                    <td class="col-wrap">{{ $experience->emp_cate ?? $experience->company_name ?? '-' }}</td>
                                                                    @if ($hasContractorRow)
                                                                        <td class="col-wrap">{{ $isContractor ? $intimationDate : '-' }}</td>
                                                                    @endif
                                                                    <td class="col-wrap">{{ $fromDate }}</td>
                                                                    <td class="col-wrap">{{ $toDate }}</td>
                                                                    <td class="col-wrap">{{ $experience->total_exp ?? $experience->experience ?? 0 }}</td>
                                                                    <td class="col-wrap">{{ $experience->designation ?? '-' }}</td>
                                                                    <td class="col-doc">
                                                                        @if (!empty($experience->upload_document))
                                                                            @php
                                                                                $fileExtension = strtolower(pathinfo($experience->upload_document ?? 'unknown.pdf', PATHINFO_EXTENSION));
                                                                            @endphp
                                                                            @if(\in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'], true))
                                                                                <a href="{{ url($experience->upload_document) }}" target="_blank" rel="noopener noreferrer" title="View image">
                                                                                    <img src="{{ url($experience->upload_document) }}" alt="" class="doc-thumb">
                                                                                </a>
                                                                            @elseif($fileExtension === 'pdf')
                                                                                <a href="{{ url($experience->upload_document) }}" target="_blank" rel="noopener noreferrer" class="doc-pdf-link text-primary" title="View document">
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
                                                                @else
                                                                    <td class="col-wrap">{{ $experience->emp_cate ?? $experience->company_name ?? '' }}</td>
                                                                    <td class="col-wrap">{{ $experience->designation }}</td>
                                                                    <td class="col-wrap">{{ $experience->total_exp ?? $experience->experience ?? 0 }} yrs</td>
                                                                @endif
                                                            </tr>
                                                            @empty
                                                            <tr>
                                                                <td colspan="{{ $expColspan }}" class="text-center">No work experience available.</td>
                                                            </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                                @endif
                                                @if ($applicant->form_name == 'S')
                                                @php
                                                    $hasPreviousEaQual = !empty($applicant->previously_number) || !empty($applicant->previously_date);
                                                @endphp
                                                <div class="asp-qa-card">
                                                    <div class="asp-qa-head">
                                                        <h6>
                                                            Have previously applied for Electrical Assistant Qualification Certificate and if yes then mention its number and date:
                                                        </h6>
                                                        <span class="asp-qa-answer {{ $hasPreviousEaQual ? 'is-yes' : 'is-no' }}">
                                                            {{ $hasPreviousEaQual ? 'Yes' : 'No' }}
                                                        </span>
                                                    </div>
                                                    @if ($hasPreviousEaQual)
                                                        <div class="asp-qa-detail">
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">License Number</span>
                                                                <span class="asp-detail-value">{{ $applicant->previously_number ?: '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of Issue</span>
                                                                <span class="asp-detail-value">{{ !empty($applicant->previously_issue_date) ? format_date($applicant->previously_issue_date) : '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of Expiry</span>
                                                                <span class="asp-detail-value">{{ !empty($applicant->previously_date) ? format_date($applicant->previously_date) : '—' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="asp-verify-row">
                                                            @if ($applicant->admincverify == null)
                                                                <span class="badge badge-primary admin_verify" data-license_number="{{ $applicant->previously_number }}" data-license_date="{{ $applicant->previously_date }}" data-license_issue_date="{{ $applicant->previously_issue_date }}" data-type="certificate" style="cursor: pointer;">Verify</span>
                                                            @elseif($applicant->admincverify == 1)
                                                                <span class="text-success small fw-semibold">(Valid Certificate)</span>
                                                            @elseif($applicant->admincverify == 2)
                                                                <span class="text-danger small fw-semibold">(Invalid Certificate)</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                @php
                                                    $hasWiremanCompCert = !empty($applicant->certificate_no) || !empty($applicant->certificate_date);
                                                @endphp
                                                <div class="asp-qa-card">
                                                    <div class="asp-qa-head">
                                                        <h6>
                                                            Do you possess Wireman Competency Certificate / Supervisor Competency Certificate issued by this Board?
                                                        </h6>
                                                        <span class="asp-qa-answer {{ $hasWiremanCompCert ? 'is-yes' : 'is-no' }}">
                                                            {{ $hasWiremanCompCert ? 'Yes' : 'No' }}
                                                        </span>
                                                    </div>
                                                    @if ($hasWiremanCompCert)
                                                        <div class="asp-qa-detail">
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">License Number</span>
                                                                <span class="asp-detail-value">{{ $applicant->certificate_no ?: '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of Issue</span>
                                                                <span class="asp-detail-value">{{ !empty($applicant->certificate_issue_date) ? format_date($applicant->certificate_issue_date) : '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of Expiry</span>
                                                                <span class="asp-detail-value">{{ !empty($applicant->certificate_date) ? format_date($applicant->certificate_date) : '—' }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="asp-verify-row">
                                                            @if ($applicant->admincverify == null)
                                                                <span class="badge badge-primary admin_verify" data-license_number="{{ $applicant->certificate_no }}" data-license_date="{{ $applicant->certificate_date }}" data-license_issue_date="{{ $applicant->certificate_issue_date }}" data-type="certificate" style="cursor: pointer;">Verify</span>
                                                            @elseif($applicant->admincverify == 1)
                                                                <span class="text-success small fw-semibold">(Valid License)</span>
                                                            @elseif($applicant->admincverify == 2)
                                                                <span class="text-danger small fw-semibold">(Invalid License)</span>
                                                            @endif
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
                                                            Have you applied for and obtained a Certificate of Qualification for Wireman Helper? If yes, please state its number and validity.
                                                        </h6>
                                                        <span class="asp-qa-answer {{ $hasWiremanBoardCert ? 'is-yes' : 'is-no' }}">
                                                            {{ $hasWiremanBoardCert ? 'Yes' : 'No' }}
                                                        </span>
                                                    </div>
                                                    @if ($hasWiremanBoardCert)
                                                        <div class="asp-qa-detail">
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">License Number</span>
                                                                <span class="asp-detail-value">{{ $applicant->certificate_no ?: '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of Issue</span>
                                                                <span class="asp-detail-value">{{ !empty($applicant->certificate_issue_date) ? format_date($applicant->certificate_issue_date) : '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of Expiry</span>
                                                                <span class="asp-detail-value">{{ format_date($applicant->certificate_date) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="asp-verify-row">
                                                            @if ($applicant->admincverify == null)
                                                                <span class="badge badge-primary admin_verify" data-license_number="{{ $applicant->certificate_no }}" data-license_date="{{ $applicant->certificate_date }}" data-license_issue_date="{{ $applicant->certificate_issue_date }}" data-type="certificate" style="cursor: pointer;">Verify</span>
                                                            @elseif($applicant->admincverify == 1)
                                                                <span class="text-success small fw-semibold">(Valid License)</span>
                                                            @elseif($applicant->admincverify == 2)
                                                                <span class="text-danger small fw-semibold">(Invalid License)</span>
                                                            @endif
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
                                                            Have you applied for and obtained a Certificate of Qualification for Wireman / Wireman Helper? If yes, please state its number and validity.
                                                        </h6>
                                                        <span class="asp-qa-answer {{ $hasWiremanBoardCert ? 'is-yes' : 'is-no' }}">
                                                            {{ $hasWiremanBoardCert ? 'Yes' : 'No' }}
                                                        </span>
                                                    </div>
                                                    @if ($hasWiremanBoardCert)
                                                        <div class="asp-qa-detail">
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">License Number</span>
                                                                <span class="asp-detail-value">{{ $applicant->certificate_no ?: '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of Issue</span>
                                                                <span class="asp-detail-value">{{ !empty($applicant->certificate_issue_date) ? format_date($applicant->certificate_issue_date) : '—' }}</span>
                                                            </div>
                                                            <div class="asp-detail-cell">
                                                                <span class="asp-detail-label">Date of Expiry</span>
                                                                <span class="asp-detail-value">{{ format_date($applicant->certificate_date) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="asp-verify-row">
                                                            @if ($applicant->admincverify == null)
                                                                <span class="badge badge-primary admin_verify" data-license_number="{{ $applicant->certificate_no }}" data-license_date="{{ $applicant->certificate_date }}" data-license_issue_date="{{ $applicant->certificate_issue_date }}" data-type="certificate" style="cursor: pointer;">Verify</span>
                                                            @elseif($applicant->admincverify == 1)
                                                                <span class="text-success small fw-semibold">(Valid License)</span>
                                                            @elseif($applicant->admincverify == 2)
                                                                <span class="text-danger small fw-semibold">(Invalid License)</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif

                                            <!-- ----------------------------------------------- -->
                                            
                                            @php
                                                $decryptedaadhar = null;
                                                try {
                                                    $decryptedaadhar = !empty($applicant->aadhaar) ? Crypt::decryptString($applicant->aadhaar) : null;
                                                } catch (\Throwable $e) {
                                                    $decryptedaadhar = null;
                                                }

                                                $decryptedPan = null;
                                                try {
                                                    $decryptedPan = !empty($applicant->pancard) ? Crypt::decryptString($applicant->pancard) : null;
                                                } catch (\Throwable $e) {
                                                    $decryptedPan = null;
                                                }

                                                $masked = (is_string($decryptedaadhar) && strlen($decryptedaadhar) === 12)
                                                    ? str_repeat('X', 8) . substr($decryptedaadhar, -4)
                                                    : 'N/A';

                                                $maskedPan = (is_string($decryptedPan) && strlen($decryptedPan) === 10)
                                                    ? str_repeat('X', 6) . substr($decryptedPan, -4)
                                                    : 'N/A';

                                                $panDocument = $applicant->pan_doc ?? $applicant->pancard_doc ?? null;

                                            @endphp

                                            <div class="asp-docs-block">
                                                <h6>Documents Uploaded</h6>
                                                <div class="row align-items-center g-2">
                                                    <div class="col-lg-6">
                                                        <span class="fw-bold" style="color: #111;">Aadhaar</span>
                                                    </div>
                                                    <div class="col-lg-6 text-lg-end">
                                                        @if (!empty($applicant->aadhaar_doc))
                                                            <span class="fw-bold" style="color: #515365">{{ $masked }}</span>
                                                            <a href="{{ route('document.show', ['type' => 'aadhaar', 'filename' => $applicant->aadhaar_doc]) }}"
                                                               target="_blank"
                                                               class="applicant-inline-doc-link ms-1"
                                                               title="Open Aadhaar document">
                                                                <i class="fa fa-file-pdf-o text-danger" aria-hidden="true"></i>
                                                                <span>View Document</span>
                                                            </a>
                                                        @else
                                                            <span class="text-muted small">No document</span>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row align-items-center g-2 mt-1">
                                                    <div class="col-lg-6">
                                                        <span class="fw-bold" style="color: #111;">PAN</span>
                                                    </div>
                                                    <div class="col-lg-6 text-lg-end">
                                                        <span class="fw-bold" style="color: #515365">{{ $maskedPan }}</span>
                                                        @if (!empty($panDocument))
                                                            <a href="{{ route('document.show', ['type' => 'pan', 'filename' => $panDocument]) }}"
                                                               target="_blank"
                                                               class="applicant-inline-doc-link ms-1"
                                                               title="Open PAN document">
                                                                <i class="fa fa-file-pdf-o text-danger" aria-hidden="true"></i>
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
                                    <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                                        <?php //var_dump($workflows->first()->is_verified);die; ?>
                                        @php
                                            $workflow = $workflows?->first();
                                            $isVerified = $workflow?->is_verified == 'Yes';
                                        @endphp


                                        <div class="row mt-2">
                                            <div class="checklist-header-row">
                                                <div class="form-check">
                                                    <input type="checkbox" id="check_all" name="check_all" class="form-check-input" @if($isVerified) checked disabled @endif>
                                                    <label class="form-check-label" for="check_all">Check All</label>
                                                </div>
                                                <div class="form-check">
                                                    <input type="checkbox" id="reset_all" name="reset_all" class="form-check-input">
                                                    <label class="form-check-label" for="reset_all">Reset All</label>
                                                </div>
                                            </div>
                                            <div id="specific-class" class="col-lg-12">
                                                @php
                                                    $checkboxes = [
                                                        'signature_form' => 'Applicant Signature in Application Form',
                                                        'sign_attached' => 'Applicant Sign attached by Officer',
                                                        'edu_certificate' => 'Educational Qualification Certificate',
                                                        'dob_proof' => 'Proof of D.O.B',
                                                        'photograph' => 'Photograph',
                                                        'specimen_signature' => 'Specimen Signature',
                                                        'fees_details' => 'Fees Details',
                                                        'age_details' => 'Age 18',
                                                        'experience_details' => 'Two Years Experience after Degree/Diploma',
                                                        'all_doc_verification' => 'All Documents Filled by Applicant',
                                                        'safety_certificate' => 'Safety Certificate/ List of Equipment',
                                                        'contract_copy' => 'Contract Copy of HT Works',
                                                        'ht_experience_cert' => 'HT Experience Certificate in Specimen Format/ Transformer Details',
                                                        'experience_in_tamilnadu' => 'Experience in TamilNadu',
                                                        'intimation_letter' => 'Intimation Letter',
                                                        'complete_experience_details' => 'Complete Experience Details',
                                                        'required_qualification_certificate' => 'Required Qualification Certificate',
                                                    ];
                                                @endphp

                                                @foreach($checkboxes as $id => $label)
                                                    <div class="form-check">
                                                        <input type="checkbox" 
                                                            id="{{ $id }}" 
                                                            name="{{ $id }}" 
                                                            class="form-check-input"
                                                            @if($isVerified) checked disabled @endif>
                                                        <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
                                                    </div>
                                                @endforeach

                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
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
                                                        <p>{{ $applicant->appl_type == 'R'?'Renewal Application':'New Application' }}</p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p><strong>Application Fees</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->amount }}.00</p>
                                                    </div>
                                                    @if (!empty($applicant->late_fees))
                                                        <div class="col-lg-6">
                                                            <p><strong>Late Fees({{ $applicant->late_months }} Months)</strong></p>
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
                                                        <p class="badge badge-success">{{ strtoupper($applicant->payment_status) }}</p>
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
                                                        <p>{{ $applicant->payment_mode??'UPI' }}</p>
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
                    @if(($applicant->status ?? '') != 'A')
                    <div class="statbox widget box box-shadow mb-2">
                        <div class="row align-items-center">

                            <div class="col-lg-12">
                                <div class="switch-wrapper d-flex justify-content-between align-items-center">
                                    <label class="switch-label mb-0 fw-bold text-end" for="Queryswitch">If you have any queries</label>
                                    <div class="switch form-switch-custom switch-inline form-switch-primary form-switch-custom inner-text-toggle">
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
                                <textarea class="form-control placement-top" id="remarks" name="remarks" rows="4" cols="50" maxlength="250"></textarea>
                            </div>
                        </div>
                        <div class="mt-3 pt-2 remarks-actions-wrap w-100">

                            @php
                            $role = Auth::user()->name; // Current role name
                           $workflow = [
                                'Supervisor' => 'Assistant Secretary',
                                'Supervisor2' => 'Assistant Secretary',
                                'Assistant Secretary' => 'Secretary',
                                'Secretary'  => 'President',
                                'President'  => null, 
                            ];

                            @endphp

                            @if ($role == 'Supervisor')
                                <div class="row justify-content-center">
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                            {{-- Forward to Assistant Secretary --}}
                                            <button class="btn btn-success" id="forwardbtn" {{ $isVerified == 'Yes'? '' : 'disabled' }} >
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
                                            <button class="btn btn-success" id="forwardbtn" data-bs-toggle="modal" data-bs-target="#declarationModal">
                                                Forward to {{ $workflow[$role] }}
                                            </button>
                                            
                                              <button id="confirmReturnBtn" class="btn btn-warning">
                                                Return to Supervisor
                                            </button>

                                           

                                            <!-- <button class="btn btn-warning">On Hold</button> -->
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
                                            <button class="btn btn-danger reject_application" data-bs-toggle="modal" data-bs-target="#rejectionModal">Reject</button>
                                        </div>
                                        {{-- Row 2: Return actions (single line) --}}
                                        <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                            <button id="confirmReturnBtn" class="btn btn-warning">
                                                Return to Supervisor
                                            </button>
                                            <button type="button" id="confirmReturnToApplicantBtn" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#returnToApplicantModal">
                                                Return to Applicant
                                            </button>
                                        </div>
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

<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
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
                <button type="button" class="btn btn-success" id="confirmApprovalBtn" disabled>Approve Application</button>
            </div>
        </div>
    </div>
</div>



<!-- <div class="modal fade" id="declarationModal" tabindex="-1" aria-labelledby="declarationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="declarationModalLabel">Declaration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="confirmVerification">
                    <label class="form-check-label" for="confirmVerification">
                        I confirm that all documents have been verified by me as a supervisor.
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmForward">Forward to {{ $applicant->status == 'RE' ? 'Secretary': 'Assistant Secretary'}}</button>
            </div>
        </div>
    </div>
</div> -->
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

<div class="modal fade" id="finalsuccessModal" tabindex="-1" aria-labelledby="finalsuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="finalsuccessModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p id="message"></p>
                <p><strong>License Number:</strong> <span id="licenseNumber"></span></p>
                <a class="badge badge-primary" href="{{ route('admin.generate.pdf', ['application_id' => $applicant->application_id]) }}" style="color: #fff;" target="_blank">
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
    <div id="queryToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                You have raised a query, so you must select at least one query type.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>


<!-- Return to Applicant Modal -->
<div class="modal fade" id="returnToApplicantModal" tabindex="-1" aria-labelledby="returnToApplicantModalLabel" aria-hidden="true">
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
                        <input class="form-check-input return-to-applicant-query" type="checkbox" name="return_applicant_query[]" id="query_edu_doc" value="Education document is missing">
                        <label class="form-check-label" for="query_edu_doc">Education document is missing</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input return-to-applicant-query" type="checkbox" name="return_applicant_query[]" id="query_photo" value="Photo is missing">
                        <label class="form-check-label" for="query_photo">Photo is missing</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input return-to-applicant-query" type="checkbox" name="return_applicant_query[]" id="query_signature" value="Signature is missing">
                        <label class="form-check-label" for="query_signature">Signature is missing</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input return-to-applicant-query" type="checkbox" name="return_applicant_query[]" id="query_aadhaar" value="Aadhaar document is missing">
                        <label class="form-check-label" for="query_aadhaar">Aadhaar document is missing</label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input return-to-applicant-query" type="checkbox" name="return_applicant_query[]" id="query_other" value="Other">
                        <label class="form-check-label" for="query_other">Other</label>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label for="returnToApplicantRemarks" class="form-label">Remarks (optional)</label>
                    <textarea class="form-control" id="returnToApplicantRemarks" name="returnToApplicantRemarks" rows="3" maxlength="250" placeholder="Add any additional remarks..."></textarea>
                </div>
                <p id="returnToApplicantQueryError" class="text-danger small mt-1" style="display: none;">Please select at least one option.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-info" id="confirmReturnToApplicantModalBtn">Return to Applicant</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="forwardmodal" tabindex="-1" aria-labelledby="declarationModalLabel" aria-hidden="true">
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
                <button type="button" class="btn btn-success" id="confirmForwardPres" disabled>Forward to President</button>
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
<div class="modal fade" id="returnConfirmModal" tabindex="-1" aria-labelledby="returnConfirmModalLabel" aria-hidden="true">
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
<div class="modal fade" id="rejectionModal" tabindex="-1" role="dialog" aria-labelledby="rejectionModalLabel" aria-hidden="true">
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
                        <input class="form-check-input reason-option" type="radio" name="radio-reason" id="radio-select" value="select" checked>
                        <label class="form-check-label" for="radio-select">
                            Reason
                        </label>
                
                        <!-- Dropdown -->
                        <select class="form-select mt-2 reason-select" name="rejection_reason">
                            <option value="">-- Select Reason --</option>
                            <option value="Incomplete application">Incomplete application</option>
                            <option value="Invalid information">Invalid information</option>
                            <option value="Eligibility criteria not met">Eligibility criteria not met</option>
                            <option value="Supporting documents not clear">Supporting documents not clear</option>
                            <option value="Duplicate application">Duplicate application</option>
                            <option value="Submission deadline missed">Submission deadline missed</option>
                            <option value="Policy violation">Policy violation</option>
                            <option value="Fraudulent / Misleading information">Fraudulent / Misleading information</option>
                        </select>
                        <div class="invalid-feedback reason-select-error"></div>
                    </div>
                
                    <!-- Radio 2 + Textarea -->
                    <div class="form-check form-check-primary mt-3">
                        <input class="form-check-input reason-option" type="radio" name="radio-reason" id="radio-other" value="other">
                        <label class="form-check-label" for="radio-other">
                            Other reason
                        </label>
                    </div>
                    <div class="form-group mb-4 reason-textarea" style="display:none;">
                        <textarea class="form-control other_reason" name="other_reason" rows="3" placeholder="Enter other reason"></textarea>
                        <div class="invalid-feedback reason-textarea-error"></div>
                    </div>
                    <input type="hidden" name="action_by" id="action_by" value="{{ $staff->name }}">
                    <input type="hidden" name="login_id" id="login_id" value="{{ $staff->id }}">
                    <input type="hidden" name="application_id" id="application_id" value="{{ $applicant->application_id }}">
                    <input type="hidden" name="appl_status" id="appl_status" value="RJ">
                </div>
                <div class="modal-footer">
                    <button class="btn btn btn-light-dark" data-bs-dismiss="modal"><i class="flaticon-cancel-12"></i> Cancel</button>
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

    // var switch_status = document.getElementById('Queryswitch');
    // var queryDropdown = document.getElementById('queryType');

    // if (switch_status.checked) {
    //     document.getElementById('queryOptions').style.display = 'block';
    // } else {
    //     document.getElementById('queryOptions').style.display = 'none';
    //     queryDropdown.selectedIndex = 0; // reset dropdown
    // }



    // var switch_status = document.getElementById('Queryswitch');
    // var queryDropdown = $('#queryType');

    // if (switch_status.checked) {
    //     $('#queryOptions').show();
    // } else {
    //     $('#queryOptions').hide();
    //     queryDropdown.val([]).trigger('change'); // clear all selected options
    // }

    // document.getElementById('Queryswitch').addEventListener('change', function() {
    //     document.getElementById('queryOptions').style.display = this.checked ? 'block' : 'none';
    // });


    $('#remarks').maxlength({
        placement: "top"
    });

    $(document).ready(function() {

        var checkAllBox = $('#check_all');
        var resetAllBox = $('#reset_all');
        var forwardbtn = $("#forwardbtn");
        var confirmForward = $("#confirmForward");
        var confirmVerification = $('#confirmVerification');
        // var individualCheckboxes = $('.form-check-input:not(#check_all):not(#reset_all)');
        var individualCheckboxes = $('#specific-class .form-check-input:not(#check_all, #reset_all)');

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



        // forwardbtn.prop('disabled', $('.form-check-input:not(#check_all):checked').length === 0);

        // Initially disable Reset All
        resetAllBox.prop('disabled', true);

        checkAllBox.change(function() {
            if ($(this).prop('checked')) {
                individualCheckboxes.prop('checked', true);
                resetAllBox.prop('disabled', false).prop('checked', false); // Enable Reset All
                forwardbtn.prop('disabled', false);
            } else {
                individualCheckboxes.prop('checked', false);
                resetAllBox.prop('disabled', true).prop('checked', false); // Disable Reset All
                forwardbtn.prop('disabled', true);
            }
        });

        // "Reset All" functionality
        resetAllBox.change(function() {
            if ($(this).prop('checked')) {
                individualCheckboxes.prop('checked', false);
                checkAllBox.prop('checked', false); // Uncheck Check All
                checkAllBox.prop('disabled', false); // Enable Check All
                resetAllBox.prop('disabled', true); // Disable Reset All after use
                forwardbtn.prop('disabled', true);
            }
        });

        // If any individual checkbox is manually unchecked, uncheck "Check All"
        individualCheckboxes.change(function() {
            if ($('.form-check-input:not(#check_all):not(#reset_all):checked').length === individualCheckboxes.length) {
                checkAllBox.prop('checked', true);
            } else {
                checkAllBox.prop('checked', false);
            }
        });
        

        


        approveButton.click(function () {
            var applicationId = @json($applicant->application_id);
            var processedBy = @json(Auth::user()->name);
            var remarks = $("#remarks").val().trim();


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
            var applicationId   = @json($applicant->application_id);
            var role_id         = @json(Auth::user()->roles_id);
            var forwardedTo     = @json($nextForwardUser->roles_id);
            var processedBy     = @json(Auth::user()->name);
            var role            = @json($nextForwardUser->name);
            var remarks         = $("#remarks").val().trim();
            var queryswitch     = $("#Queryswitch").prop("checked");
            var checkboxStatus  = "Yes";

            var queryType = null;
            var query_status = "No";

            
            if (queryswitch) {
                queryType = $("#queryType").val() || null;
                query_status = 'Yes';
            }

            Swal.fire({
                title: "Declaration",
                // html: `
                //     <div class="form-check text-start">
                //         <label class="form-check-label" for="confirmVerification">
                //             I confirm that have been verified by me as a secretary.
                //         </label>
                //     </div>
                // `,
                text:'I confirm that have been verified by me as a secretary.',
                showCancelButton: true,
                confirmButtonText: "Forward to President",
                cancelButtonText: "Cancel",
                focusConfirm: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    
                    $.ajax({
                        url: '{{ route('admin.forwardApplication',["role" => "__ROLE__"]) }}'.replace('__ROLE__', role),
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
                            "queryType[]": queryType 
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




        forwardbtn.click(function() {
            Swal.fire({
                title: "Declaration",
                text: 'I confirm that all documents have been verified .',
                showCancelButton: true,
                 confirmButtonText: "Forward to {{ Auth::user()->name == 'Assistant Secretary' ? 'Secretary' : 'Assistant Secretary' }}",
                cancelButtonText: "Cancel",
                focusConfirm: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    // your existing ajax code

                    var queryType = [];

                    var applicationId = @json($applicant->application_id);
                    var processedBy = @json(Auth::user()->name);
                    var role_id = @json(Auth::user()->roles_id);
                    var forwardedTo = @json($nextForwardUser->roles_id);

                    // console.log(forwardedTo);
                    // return false;

                    var role = @json($nextForwardUser->name);
                    var remarks = $("#remarks").val().trim();

                    var checkboxStatus = "Yes";
                    let queryswitch = $("#Queryswitch").prop("checked");
                    queryType = $("#queryType").val();
                    let errorBox = $("#query_error");

                    errorBox.text(""); // clear previous error

                    if (queryswitch && queryType.length === 0) {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Please select at least one query type."
                        });
                        return;
                    }

                    $.ajax({
                        url: '{{ route('admin.forwardApplication',["role" => "__ROLE__"]) }}'.replace('__ROLE__', role),
                        type: "POST",
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                        },
                        data: {
                            application_id: applicationId,
                            processed_by: processedBy,
                            forwarded_to: forwardedTo,
                            role_id: role_id,
                            remarks: remarks || "No remarks provided",
                            checkboxes: checkboxStatus,
                            queryswitch: queryswitch ? "Yes" : "No",
                            "queryType[]": queryType
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
        // confirmForward.click(function() {

        //     var queryType = [];

        //     var applicationId = @json($applicant-> application_id);
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
        //         url: '{{ route('admin.forwardApplication',["role" => "__ROLE__"]) }}'.replace('__ROLE__', role),
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
            
            var applicationId   = @json($applicant->application_id);
            var returnBy        = @json(Auth::user()->name);
            var forwardedTo     = @json($returnForwardUser->roles_id ?? 0);
            var remarks         = $("#remarks").val().trim();
            // var queryswitch     = $("#Queryswitch").prop("checked");


            var checkboxStatus = "Yes";
            
            let queryswitch = $("#Queryswitch").prop("checked");
            queryType = $("#queryType").val();
            let errorBox = $("#query_error");


            Swal.fire({
                title: "Return",
                html: 'You want to return this application!',
                showCancelButton: true,
                confirmButtonText: "Forward to {{ $applicant->status == 'RE' ? 'Secretary' : 'Supervisor' }}",
                cancelButtonText: "Cancel",
                focusConfirm: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('admin.returntoSupervisor') }}',
                        type: 'POST',
                        headers: {
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                        },
                        data: {
                            application_id  : applicationId,
                            return_by       : returnBy,
                            forwarded_to    : forwardedTo,
                            remarks         : remarks || "No remarks provided",
                            checkboxes      : checkboxStatus,
                            queryswitch     : queryswitch ? "Yes" : "No",
                            "queryType[]": queryType 
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
            // 🔹 Select only from the correct section
            let licenseNumber = $(this).data("license_number");
            let licenseDate = $(this).data("license_date");
            let licenseIssueDate = $(this).data("license_issue_date");

            let type = $(this).data("type");

            let application_id = @json($applicant->application_id);
            
            let url = "{{ route('admin.verifylicense') }}";
            
            // console.log(licenseNumber, licenseDate, licenseIssueDate, url);
            // return false;
            $.ajax({
                url: url,
                method: "POST",
                data: {
                    license_number : licenseNumber,
                    date : licenseDate,
                    issue_date : licenseIssueDate,
                    type : type,
                    application_id : application_id,
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                success: function (response) {
                    btn.hide(); // hide the button

                    if (response.exists) {
                        btn.after('<span class="text-success ms-2">(Valid License.)</span>'); // ✅ tick mark
                    } else {
                        btn.after('<span class="text-danger ms-2">(Invalid License.)</span>'); // ❌ cross mark
                    }

                },
                error: function () {
                    btn.hide(); // also hide on error
                    btn.after('<span class="text-danger ms-2">🚫 Something went wrong!.</span>'); // error icon
                },
            });
        });

    });




    $(".reason-option").on("change", function() {
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
  $(document).on("change", ".reason-select", function() {
    if ($(this).val() !== "") {
      $(this).removeClass("is-invalid");
      $(this).siblings(".reason-select-error").text("");
    }
  });

  $(document).on("input", "textarea[name='other_reason']", function() {
    if ($(this).val().trim() !== "") {
      $(this).removeClass("is-invalid");
      $(this).siblings(".reason-textarea-error").text("");
    }
  });
  

  $("#reject_application").on("submit", function(e) {
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
            success: function(response) {
              if (response.success === true) {
                $("#rejectionModal").modal("hide");
                Swal.fire({
                  icon: 'success',
                  title: 'Rejected successfully',
                  showConfirmButton: false,
                  timer: 2000
                }).then(() => {
                        window.location.href = APP_URL +"/admin/dashboard"; // redirect URL
                      });
              }else{
                $("#rejectionModal").modal("hide");
                Swal.fire('Something went wrong', '', 'error');
              }
            },
            error: function(xhr) {
              Swal.fire('Server error occurred', '', 'error').then(() => {
                $("#rejectionModal").modal("hide");
                    //window.location.href = "/admin/dashboard"; // redirect path
                  });
            }
          });
      });
</script>
