@include('include.header')

@php
    $formCode = strtoupper((string) ($formCode ?? $application_details->form_name ?? ''));
    $applType = strtoupper((string) ($application_details->appl_type ?? ''));
    $applTypeLabel = match ($applType) {
        'N' => 'New application',
        'R' => 'Renewal',
        'D' => 'Digitisation',
        'A' => 'Alteration',
        default => $applType !== '' ? $applType : '—',
    };
    $licenceLabel = trim((string) ($licence_name->licence_name ?? ''));
    if ($licenceLabel === '') {
        $licenceLabel = 'Form ' . ($formCode !== '' ? $formCode : '—');
    }
    $applicantName = trim((string) ($application_details->applicant_name ?? ''));
    if ($applicantName === '') {
        $applicantName = 'Applicant';
    }
    $applicationNo = trim((string) ($application_details->application_id ?? ''));
@endphp

<style>
    .apv {
        background:
            radial-gradient(900px 280px at 0% 0%, rgba(3, 90, 179, 0.07), transparent 60%),
            #eef3f8;
        padding-bottom: 3rem;
        font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, 'Noto Sans', 'Noto Sans Tamil', 'Latha', 'Nirmala UI', system-ui, sans-serif;
        line-height: 1.5;
        overflow-x: hidden;
    }
    .apv-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(128deg, #02356f 0%, #035ab3 52%, #0b7ad6 100%);
        color: #fff;
        padding: 1rem 0 2.5rem;
    }
    .apv-hero::before,
    .apv-hero::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }
    .apv-hero::before {
        width: 18rem;
        height: 18rem;
        right: -5rem;
        top: -7rem;
        background: rgba(255, 255, 255, 0.07);
    }
    .apv-hero::after {
        width: 9rem;
        height: 9rem;
        left: 38%;
        bottom: -4.5rem;
        background: rgba(255, 255, 255, 0.05);
    }
    .apv .container {
        position: relative;
        z-index: 1;
        max-width: 74rem;
        width: 100%;
    }
    .apv-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
    }
    .apv-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        height: 2.25rem;
        margin: 0;
        color: #fff;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.32);
        border-radius: 999px;
        padding: 0 1rem;
        font-family: inherit;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
        text-decoration: none;
        cursor: pointer;
        -webkit-appearance: none;
        appearance: none;
    }
    .apv-btn .fa { font-size: 0.875rem; line-height: 1; }
    .apv-btn:hover,
    .apv-btn:focus-visible {
        background: rgba(255, 255, 255, 0.22);
        color: #fff;
        text-decoration: none;
        outline: none;
    }
    .apv-btn:focus-visible {
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.35);
    }
    .apv-kicker {
        margin: 0 0 0.25rem;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        line-height: 1.4;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.8);
    }
    .apv-title {
        margin: 0;
        max-width: 42rem;
        font-family: inherit;
        font-size: clamp(1.375rem, 1.1rem + 1.2vw, 1.875rem);
        font-weight: 700;
        line-height: 1.25;
        color: #fff;
        letter-spacing: -0.01em;
        overflow-wrap: anywhere;
    }
    .apv-chips {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch;
        gap: 0.75rem;
        margin-top: 1rem;
    }
    .apv-chip {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
        min-height: 3.25rem;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.24);
        border-radius: 0.5rem;
        padding: 0.5rem 0.875rem;
    }
    .apv-chip-label {
        display: block;
        margin-bottom: 0.125rem;
        font-size: 0.6875rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        line-height: 1.4;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.8);
    }
    .apv-chip-value {
        display: block;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.4;
        word-break: break-word;
    }
    .apv-sheet {
        margin-top: -1.25rem;
    }
    @media (max-width: 575.98px) {
        .apv-hero { padding-bottom: 2rem; }
        .apv-actions { margin-bottom: 1rem; }
        .apv-chip { flex: 1 1 100%; }
    }
    @media print {
        .main-header,
        .main-footer,
        .footer-bottom,
        .apv-actions { display: none !important; }
        .apv,
        .apv-hero {
            background: #fff !important;
            color: #12233f !important;
            padding-top: 0;
        }
        .apv-kicker,
        .apv-title,
        .apv-chip-label,
        .apv-chip-value { color: #12233f !important; }
        .apv-chip {
            background: #fff !important;
            border-color: #c5d0e0 !important;
        }
        .apv-sheet { margin-top: 0.5rem; }
        .dash-prv-section { box-shadow: none !important; }
    }
</style>

<div class="apv">
    <header class="apv-hero">
        <div class="container">
            <div class="apv-actions">
                <a href="{{ route('dashboard') }}" class="apv-btn">
                    <i class="fa fa-arrow-left" aria-hidden="true"></i>
                    Back to Dashboard
                </a>
                <button type="button" class="apv-btn" onclick="window.print()">
                    <i class="fa fa-print" aria-hidden="true"></i>
                    Print
                </button>
            </div>
            <p class="apv-kicker">Application preview</p>
            <h1 class="apv-title">{{ $applicantName }}</h1>
            <div class="apv-chips">
                <div class="apv-chip">
                    <span class="apv-chip-label">Application ID</span>
                    <span class="apv-chip-value">{{ $applicationNo !== '' ? $applicationNo : '—' }}</span>
                </div>
                <div class="apv-chip">
                    <span class="apv-chip-label">Certificate Name</span>
                    <span class="apv-chip-value">{{ $licenceLabel }}</span>
                </div>
                <div class="apv-chip">
                    <span class="apv-chip-label">Application type</span>
                    <span class="apv-chip-value">{{ $applTypeLabel }}</span>
                </div>
            </div>
        </div>
    </header>

    <div class="apv-sheet">
        <div class="container">
            @include('user_login.partials.dashboard-application-preview')
        </div>
    </div>
</div>

<footer class="main-footer">
    @include('include.footer')
</footer>
