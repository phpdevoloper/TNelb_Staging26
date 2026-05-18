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


    /* your colors */
    /* .legend .superawesome { background-color: #ff00ff; }
    .legend .awesome      { background-color: #00ffff; }
    .legend .kindaawesome { background-color: #0000ff; }
    .legend .notawesome   { background-color: #000000; } */

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

    /* ------------------------------------------------------------------
       Active / Present License Details — modern card-grid UI
       Split into Competency Certificates and Contractor Licenses.
       ------------------------------------------------------------------ */
    .license-board {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .license-section {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        padding: 1rem 1.1rem 1.15rem;
    }

    .license-section__header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        padding-bottom: 0.65rem;
        margin-bottom: 0.9rem;
        border-bottom: 1px solid #eef2f7;
    }

    .license-section__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 8px;
        font-size: 15px;
        color: #fff;
        flex-shrink: 0;
    }

    .license-section--competency .license-section__icon {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
    }

    .license-section--contractor .license-section__icon {
        background: linear-gradient(135deg, #0d9488, #0f766e);
    }

    .license-section__title {
        font-size: 0.98rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        letter-spacing: 0.01em;
    }

    .license-section__count {
        margin-left: auto;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 999px;
        letter-spacing: 0.04em;
    }

    .license-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(235px, 1fr));
        gap: 0.65rem;
    }

    /* Compact ID-card style: gradient background, monospace license number focal point. */
    .license-card {
        position: relative;
        display: flex;
        flex-direction: column;
        padding: 0.6rem 0.75rem 0.65rem;
        border: none;
        border-radius: 9px;
        color: #fff;
        overflow: hidden;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 55%, #2563eb 100%);
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.18);
    }

    .license-section--contractor .license-card {
        background: linear-gradient(135deg, #134e4a 0%, #0f766e 55%, #0d9488 100%);
        box-shadow: 0 2px 6px rgba(13, 148, 136, 0.18);
    }

    .license-card.is-expired {
        background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 55%, #b91c1c 100%);
        box-shadow: 0 2px 6px rgba(153, 27, 27, 0.22);
    }

    /* Decorative glow blobs (smaller for compact card) */
    .license-card::before {
        content: '';
        position: absolute;
        top: -55%;
        right: -25%;
        width: 130px;
        height: 130px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.13), transparent 70%);
        pointer-events: none;
    }

    .license-card::after {
        content: '';
        position: absolute;
        bottom: -45%;
        left: -18%;
        width: 110px;
        height: 110px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.06), transparent 70%);
        pointer-events: none;
    }

    .license-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 14px rgba(30, 58, 138, 0.26);
    }

    .license-section--contractor .license-card:hover {
        box-shadow: 0 6px 14px rgba(13, 148, 136, 0.26);
    }

    .license-card.is-expired:hover {
        box-shadow: 0 6px 14px rgba(153, 27, 27, 0.3);
    }

    .license-card__top {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.45rem;
        margin-bottom: 0.4rem;
    }

    .license-card__category {
        font-size: 0.58rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: rgba(255, 255, 255, 0.74);
    }

    .license-card__status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: rgba(255, 255, 255, 0.16);
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
        color: #fff;
        font-size: 0.6rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 2px 8px;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.3);
        white-space: nowrap;
        line-height: 1.4;
    }

    .license-card__status::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .license-card.is-active .license-card__status::before {
        background: #4ade80;
        box-shadow: 0 0 5px rgba(74, 222, 128, 0.7);
    }

    .license-card.is-expired .license-card__status::before {
        background: #fca5a5;
    }

    .license-card__title {
        position: relative;
        z-index: 1;
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1.3;
        color: rgba(255, 255, 255, 0.95);
        margin-bottom: 0.25rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .license-card__number {
        position: relative;
        z-index: 1;
        font-family: 'Courier New', Consolas, ui-monospace, monospace;
        font-size: 0.95rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: #fff;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.18);
        margin-bottom: 0.55rem;
        word-break: break-all;
    }

    .license-card__bottom {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 0.5rem;
        margin-top: auto;
    }

    .license-card__dates {
        display: flex;
        gap: 0.85rem;
    }

    .license-card__date-block {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .license-card__date-label {
        font-size: 0.54rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: rgba(255, 255, 255, 0.62);
        margin-bottom: 1px;
    }

    .license-card__date-value {
        font-size: 0.74rem;
        font-weight: 600;
        color: #fff;
        letter-spacing: 0.01em;
    }

    .license-card__chip {
        background: rgba(255, 255, 255, 0.95);
        color: #1e3a8a;
        font-size: 0.6rem;
        font-weight: 800;
        padding: 2px 7px;
        border-radius: 4px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        line-height: 1.4;
    }

    .license-section--contractor .license-card__chip {
        color: #134e4a;
    }

    .license-card.is-expired .license-card__chip {
        color: #7f1d1d;
    }

    .license-card__reason {
        position: relative;
        z-index: 1;
        margin-top: 0.45rem;
        padding: 0.35rem 0.55rem;
        background: rgba(255, 255, 255, 0.16);
        border-left: 2px solid rgba(255, 255, 255, 0.65);
        border-radius: 3px;
        font-size: 0.68rem;
        font-weight: 500;
        line-height: 1.35;
        color: #fff;
        display: flex;
        align-items: flex-start;
        gap: 5px;
    }

    .license-card__reason i {
        margin-top: 1px;
        flex-shrink: 0;
        font-size: 0.7rem;
    }

    .license-empty {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        padding: 1.7rem 1rem;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
        color: #64748b;
        font-size: 0.86rem;
        font-weight: 500;
    }

    .license-empty i {
        font-size: 1rem;
        color: #94a3b8;
    }

    @media (max-width: 575px) {
        .license-grid {
            grid-template-columns: 1fr;
        }
    }

</style>
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
                        $competencyCodes = ['C', 'B', 'W', 'WH'];
                        $allLicenses = collect($present_license);
                        $competencyLicenses = $allLicenses->filter(function ($l) use ($competencyCodes) {
                            return in_array($l->license_name, $competencyCodes);
                        });
                        $contractorLicenses = $allLicenses->reject(function ($l) use ($competencyCodes) {
                            return in_array($l->license_name, $competencyCodes);
                        });
                        @endphp

                        <div class="projects-section-login license-board">
                            <h5 class="mb-2"><strong>Active / Present License Details</strong></h5>

                            {{-- Competency Certificates --}}
                            <section class="license-section license-section--competency">
                                <div class="license-section__header">
                                    <span class="license-section__icon"><i class="fa fa-certificate"></i></span>
                                    <h6 class="license-section__title">Competency Certificates</h6>
                                    <span class="license-section__count">{{ $competencyLicenses->count() }}</span>
                                </div>

                                @if ($competencyLicenses->isEmpty())
                                    <div class="license-empty">
                                        <i class="fa fa-folder-open-o"></i>
                                        <span>No active competency certificates</span>
                                    </div>
                                @else
                                    <div class="license-grid">
                                        @foreach ($competencyLicenses as $workflow)
                                            @php
                                                $licName = DB::table('mst_licences')
                                                    ->where('cert_licence_code', $workflow->license_name)
                                                    ->first();
                                                $issuedDate = $workflow->issued_at ? Carbon::parse($workflow->issued_at)->format('d-m-Y') : 'N/A';
                                                $expiryDate = $workflow->expires_at ? Carbon::parse($workflow->expires_at)->format('d-m-Y') : 'N/A';
                                                $expiry = $workflow->renewal_expires_at ?: $workflow->expires_at;
                                                $isExpired = $expiry ? Carbon::parse($expiry)->lte($today) : false;
                                            @endphp

                                            <article class="license-card {{ $isExpired ? 'is-expired' : 'is-active' }}">
                                                <div class="license-card__top">
                                                    {{-- <span class="license-card__category">Competency Certificate</span> --}}
                                                    <span class="license-card__status">
                                                        {{ $isExpired ? 'Expired' : 'Active' }}
                                                    </span>
                                                </div>
                                                <div class="license-card__title">
                                                    {{ $licName->licence_name ?? 'N/A' }}
                                                </div>
                                                <div class="license-card__number">
                                                    {{ $workflow->license_number ?? '— — — —' }}
                                                </div>
                                                <div class="license-card__bottom">
                                                    <div class="license-card__dates">
                                                        <div class="license-card__date-block">
                                                            <span class="license-card__date-label">Issued</span>
                                                            <span class="license-card__date-value">{{ $issuedDate }}</span>
                                                        </div>
                                                        <div class="license-card__date-block">
                                                            <span class="license-card__date-label">Expiry</span>
                                                            <span class="license-card__date-value">{{ $expiryDate }}</span>
                                                        </div>
                                                    </div>
                                                    <span class="license-card__chip">Form {{ $workflow->license_name ?? 'N/A' }}</span>
                                                </div>

                                                @if ($isExpired && $workflow->expires_at)
                                                    <div class="license-card__reason">
                                                        <i class="fa fa-exclamation-triangle"></i>
                                                        <span>Expired on {{ Carbon::parse($workflow->expires_at)->format('d-m-Y') }}</span>
                                                    </div>
                                                @endif
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </section>

                            {{-- Contractor Licenses --}}
                            <section class="license-section license-section--contractor">
                                <div class="license-section__header">
                                    <span class="license-section__icon"><i class="fa fa-id-card-o"></i></span>
                                    <h6 class="license-section__title">Contractor Licenses</h6>
                                    <span class="license-section__count">{{ $contractorLicenses->count() }}</span>
                                </div>

                                @if ($contractorLicenses->isEmpty())
                                    <div class="license-empty">
                                        <i class="fa fa-folder-open-o"></i>
                                        <span>No active contractor licenses</span>
                                    </div>
                                @else
                                    <div class="license-grid">
                                        @foreach ($contractorLicenses as $workflow)
                                            @php
                                                $licName = DB::table('mst_licences')
                                                    ->where('cert_licence_code', $workflow->license_name)
                                                    ->first();
                                                $issuedDate = $workflow->issued_at ? Carbon::parse($workflow->issued_at)->format('d-m-Y') : 'N/A';
                                                $expiryDate = $workflow->expires_at ? Carbon::parse($workflow->expires_at)->format('d-m-Y') : 'N/A';
                                                $expiry = $workflow->renewal_expires_at ?: $workflow->expires_at;
                                                $isExpired = $expiry ? Carbon::parse($expiry)->lte($today) : false;

                                                // TEMP: Bank Solvency / Staff CC checks disabled (source tables missing in DB)
                                                $bankValidity = null;
                                                $expiredStaffDates = [];
                                                $hasBankExpired = $bankValidity && $bankValidity->lt($today);
                                                $hasStaffExpired = !empty($expiredStaffDates);
                                                $isFlagged = $isExpired || $hasBankExpired || $hasStaffExpired;
                                            @endphp

                                            <article class="license-card {{ $isFlagged ? 'is-expired' : 'is-active' }}">
                                                <div class="license-card__top">
                                                    <span class="license-card__category">Contractor License</span>
                                                    <span class="license-card__status">
                                                        {{ $isFlagged ? 'Expired' : 'Active' }}
                                                    </span>
                                                </div>
                                                <div class="license-card__title">
                                                    {{ $licName->licence_name ?? 'N/A' }}
                                                </div>
                                                <div class="license-card__number">
                                                    {{ $workflow->license_number ?? '— — — —' }}
                                                </div>
                                                <div class="license-card__bottom">
                                                    <div class="license-card__dates">
                                                        <div class="license-card__date-block">
                                                            <span class="license-card__date-label">Issued</span>
                                                            <span class="license-card__date-value">{{ $issuedDate }}</span>
                                                        </div>
                                                        <div class="license-card__date-block">
                                                            <span class="license-card__date-label">Expiry</span>
                                                            <span class="license-card__date-value">{{ $expiryDate }}</span>
                                                        </div>
                                                    </div>
                                                    <span class="license-card__chip">Form {{ $workflow->license_name ?? 'N/A' }}</span>
                                                </div>

                                                @if ($isExpired && $workflow->expires_at)
                                                    <div class="license-card__reason">
                                                        <i class="fa fa-exclamation-triangle"></i>
                                                        <span>Expired on {{ Carbon::parse($workflow->expires_at)->format('d-m-Y') }}</span>
                                                    </div>
                                                @elseif ($hasBankExpired || $hasStaffExpired)
                                                    <div class="license-card__reason">
                                                        <i class="fa fa-exclamation-triangle"></i>
                                                        <span>
                                                            @if ($hasBankExpired)
                                                                Bank Solvency expired on {{ $bankValidity->format('d-m-Y') }}<br>
                                                            @endif
                                                            @if ($hasStaffExpired)
                                                                @foreach ($expiredStaffDates as $expired)
                                                                    QC Staff CC {{ $expired['number'] }} expired on {{ $expired['date'] }}<br>
                                                                @endforeach
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </section>
                        </div>

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
                         @if($returnapplication->isNotEmpty())

                        <div class="projects-section-login active_license">
                           
                            <div class="project-list-login mt-2">

                                <div class="project-card-login return_section"  data-status="en-cours">
                                
                                     <h5 class="mb-2 clr_retur" ><strong>Returned Application Details</strong></h5>

                                     <table class="table table-bordered " width="100%">
                                        <thead class="text-center">
                                            <tr>
                                                <th>Licence Name / <br>Certificate Name</th>
                                                <th>Category</th>

                                                <th>Application ID</th>
                                                
                                                <th>Returned By</th>
                                                <th>Returned Date</th>
                                                <th>Reason</th>

                                                 <th>Action</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                            @foreach($returnapplication as $app)

                                          @php
                                          
                                               
                                             $licence = $mstLicences->firstWhere('form_code', $app->form_name);

                                                if ($app->processed_by == 'SE') {
                                                    $processed_by = 'Secretary';
                                                } else {
                                                    $processed_by = 'President';
                                                }

                                                $returnformatdate = \Carbon\Carbon::parse($app->return_date)->format('d-m-Y');
                                            @endphp

                                                <tr class="text-center">
                                                    
                                                    <td>{{ $licence->licence_name }} <br> [ Form {{$app->form_name}} ]</td>
                                                    <td>    
                                                        @if($licence->category_id == '1')
                                                          Contractor Licence
                                                        @else
                                                            Competency Certificate
                                                        @endif
                                                    
                                                    </td>

                                                    <td> {{ $app->application_id }}</td>
                                                    <td> {{ $processed_by }}</td>
                                                   <td> {{ $returnformatdate }}</td>
                                                   <td> 
                                                    @php
                                                        $reasons = json_decode($app->return_reason, true);
                                                    @endphp
                                                         @if(!empty($reasons))
                                                            @foreach($reasons as $reason)
                                                                <span class="text-danger">{{ Str::upper($reason) }} </span> <br>
                                                            @endforeach
                                                        @endif
                                                   </td>

                                                     <td> 
                                                        <a href="{{ route('apply-form-a_return', ['application_id' => $app->application_id]) }}">
                                                           <button class="btn btn-info">
                                                                Edit <i class="fa fa-long-arrow-right"></i>
                                                            </button>
                                                        </a>
                                                   </td>

                                                </tr>
                                            @endforeach
                                              
                                        </tbody>
                                    </table>

                                </div>
                            </div>
                        </div>

        
                        @endif

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
                                           <th>Form Type</th>
                                           <th>Application ID</th>
                                           <th>Applied On</th>
                                           <th>Application<br> Status</th>
                                           <th>Payment <br> Status</th>
                                           {{-- <th>Payment <br> Receipt</th> --}}
                                           <th>Acknowledgement<br> Download</th>
                                           <th>Licence Number</th>
                                           <th>Licence<br> Download</th>
                                       </tr>
                                   </thead>
                                   <tbody>
                                       @if (isset($workflows_cl) && $workflows_cl->isNotEmpty())
                                       @foreach ($workflows_cl as $index => $workflow)
                                       <?php //var_dump($workflow);die;
                                       ?>
                                       <tr>
                                           <td>{{ $index + 1 }}</td>
                                            <td style="width: 18%;">
                                           {{ $workflow->licence_display_name ?? 'N/A' }} <br>
                                           [Form {{ strtoupper($workflow->form_name ?? 'NA') }}]
                                           </td>
                                           <td>{{ $workflow->application_id ?? 'N/A' }}</td>
                                           <td>{{ isset($workflow->dt_submit) ? \Carbon\Carbon::parse($workflow->dt_submit)->format('d/m/Y') : 'N/A' }}
                                           </td>

                                           <!-- Application Status -->
                                           <td>
                                               @if ($workflow->payment_status == 'draft')

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

                                               @elseif($workflow->application_status == 'F')
                                               <span class="btn btn-sm btn-warning">In Progress</span>
                                               
                                               @elseif($workflow->application_status == 'RET')
                                              

                                                <a href="{{ route('renew-form_ea', ['application_id' => $workflow->application_id]) }}">
                                                   <button class="btn btn-sm btn-return">
                                                       <i class="fa fa-pencil"></i> Return
                                                   </button>
                                               </a>
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
                                                   <span style="font-size: x-small;">English</span>
                                               </a>
                                               @elseif(($workflow->form_name == 'SB'))
                                               <a href="{{ route('generatesb.pdf', ['login_id' => $workflow->application_id]) }}"
                                                   target="_blank" style="font-weight:500;">
                                                   <i class="fa fa-file-pdf-o"
                                                       style="font-size:20px;color:red"></i>
                                                   <span style="font-size: x-small;">English</span>
                                               </a>
                                               @elseif(($workflow->form_name == 'B'))
                                               <a href="{{ route('generateb.pdf', ['login_id' => $workflow->application_id]) }}"
                                                   target="_blank" style="font-weight:500;">
                                                   <i class="fa fa-file-pdf-o"
                                                       style="font-size:20px;color:red"></i>
                                                   <span style="font-size: x-small;">English</span>
                                               </a>
                                               @else
                                               <a href="{{ route('generatesa.pdf', ['login_id' => $workflow->application_id]) }}"
                                                   target="_blank" style="font-weight:500;">
                                                   <i class="fa fa-file-pdf-o"
                                                       style="font-size:20px;color:red"></i>
                                                   <span style="font-size: x-small;">English</span>
                                               </a>

                                               @endif
                                               @endif
                                           </td>

                                           <!-- License Number -->

                                           <td>
                                               @if (!empty($workflow->license_number) && $workflow->application_status == 'A')
                                               <a href="{{ route('admin.generateFormcontractor_download.pdf', ['application_id' => $workflow->application_id]) }}" target="_blank">
                                                   <span class="badge badge-info" style="font-size: 15px;">{{ $workflow->license_number }}</span>
                                               </a>
                                               <br>

                                               @if (!empty($workflow->renewals))
                                               <span class="text-muted" style="font-size: 12px;">
                                                   Renewed {{ count($workflow->renewals) }} times
                                               </span>
                                               <br>
                                               @endif

                                               @if (!empty($workflow->renewal_application_id))
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
                                               @else
                                               <p class="text-primary">NA</p>
                                               @endif
                                           </td>

                                           <!-- ---------------License download-------- -->
                                            <td>

                                               @if ( $workflow->application_status == 'A')
                                               
                                               <span> <a href="{{ route('admin.generateFormcontractor_download.pdf', ['application_id' => $workflow->application_id]) }}" target="_blank">
                                                       <i class="fa fa-file-pdf-o"
                                                       style="font-size:20px;color:red"></i>
                                                   <span style="font-size: x-small;">English</span> | <a href="{{ route('admin.generateFormcontractor_download_tamil.pdf', ['application_id' => $workflow->application_id]) }}" target="_blank">
                                                        <i class="fa fa-file-pdf-o"
                                                       style="font-size:20px;color:red"></i>
                                                   <span style="font-size: x-small;">தமிழ்</span>
                                               </a>
                                               <br>

                                             

                                              
                                              
                                               @else
                                               <p class="text-primary">NA</p>
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
</script>
