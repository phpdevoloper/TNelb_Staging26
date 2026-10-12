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
        padding-bottom: 2.75rem;
    }
    .apv-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(128deg, #02356f 0%, #035ab3 52%, #0b7ad6 100%);
        color: #fff;
        padding: 1.05rem 0 2.35rem;
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
        gap: 0.65rem;
        flex-wrap: wrap;
        margin-bottom: 1.15rem;
    }
    .apv-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #fff;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.28);
        border-radius: 999px;
        padding: 0.42rem 0.95rem;
        font-size: 0.84rem;
        font-weight: 600;
        line-height: 1.2;
        text-decoration: none;
        cursor: pointer;
    }
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
        margin: 0 0 0.3rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.78);
    }
    .apv-title {
        margin: 0;
        max-width: 42rem;
        font-size: clamp(1.45rem, 1.1rem + 1.4vw, 2rem);
        font-weight: 700;
        line-height: 1.2;
        color: #fff;
        letter-spacing: -0.02em;
    }
    .apv-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-top: 1rem;
    }
    .apv-chip {
        min-width: 0;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 0.7rem;
        padding: 0.45rem 0.75rem 0.5rem;
    }
    .apv-chip-label {
        display: block;
        margin-bottom: 0.12rem;
        font-size: 0.64rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.72);
    }
    .apv-chip-value {
        display: block;
        font-size: 0.9rem;
        font-weight: 700;
        line-height: 1.3;
        word-break: break-word;
    }
    .apv-sheet {
        margin-top: -1.15rem;
    }
    @media (max-width: 575.98px) {
        .apv-hero { padding-bottom: 2rem; }
        .apv-actions { margin-bottom: 0.9rem; }
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
