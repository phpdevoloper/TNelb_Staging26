@php
    $dashboardActive = request()->routeIs('dashboard');
    $activeFormS = request()->routeIs('apply-form-s');
    $activeFormWh = request()->routeIs('apply-form-wh');
    $activeFormW = request()->routeIs('apply-form-w');
    $activeFormP = request()->routeIs('apply_form_p');
    $activeFormA = request()->routeIs([
        'apply-form-a',
        'apply-form-a_draft',
        'apply-form-a_renewal_draft',
        'apply-form-a_return',
    ]);
    $activeFormSa = request()->routeIs(['apply-form-sa', 'apply-form-sa_draft', 'apply-form-sa_renewal_draft']);
    $activeFormSb = request()->routeIs(['apply-form-sb', 'apply-form-sb_draft', 'apply-form-sb_renewal_draft']);
    $activeFormB = request()->routeIs(['apply-form-b', 'apply-form-b_draft']);
    $activeOldCertRenewal = request()->routeIs('old_certificate_renewal')
        || request()->routeIs('old_certificate_renewal.*')
        || request()->routeIs('old_renewal.*');
    $activeOldContractorRenewal = request()->routeIs('old_contractor_renewal')
        || request()->routeIs('old_contractor_renewal.*');
@endphp
@once
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome-4.7.0/css/font-awesome.min.css') }}">
@endonce
<style>
    /* User sidebar - rounded card style */
    .sidebar-login-v2 {
        --sb-panel: #143b62;
        --sb-row: rgba(255, 255, 255, 0.08);
        --sb-row-soft: rgba(255, 255, 255, 0.06);
        --sb-row-hover: rgba(255, 255, 255, 0.16);
        --sb-border: rgba(255, 255, 255, 0.2);
        --sb-text: #ffffff;
        --sb-muted: rgba(255, 255, 255, 0.88);
        --sb-active: rgba(255, 255, 255, 0.22);
        --sb-caret-bg: rgba(255, 255, 255, 0.16);
        --sb-radius: 10px;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background: linear-gradient(180deg, #143b62 0%, #12365a 100%);
        color: var(--sb-text);
        width: 340px;
        min-height: 100%;
        padding: 0.55rem 0.45rem 0.65rem;
        border-right: 1px solid var(--sb-border);
    }

    .sidebar-login-v2 span em {
        font-style: normal;
        font-weight: 600;
    }

    .sidebar-login-v2 .sidebar-login-v2__list {
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 0.38rem;
        display: flex;
        flex-direction: column;
    }

    .sidebar-login-v2 .sidebar-login-v2__list > .nav-item {
        margin: 0;
        border: none;
        background: transparent;
    }

    .sidebar-login-v2 .nav-link {
        border-radius: var(--sb-radius);
        color: var(--sb-text);
        font-size: 0.83rem;
        line-height: 1.25;
        font-weight: 500;
        padding: 0.45rem 0.8rem;
        text-decoration: none;
        border: 1px solid var(--sb-border);
        background: var(--sb-row);
        transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
    }

    .sidebar-login-v2 .nav-link:hover {
        background: var(--sb-row-hover);
        color: var(--sb-text);
    }

    .sidebar-login-v2 .nav-link.is-active {
        background: var(--sb-active);
        color: var(--sb-text);
        border-color: rgba(255, 255, 255, 0.38);
        font-weight: 600;
    }

    .sidebar-login-v2 .nav-link.is-active .sidebar-login-v2__icon {
        color: var(--sb-text);
    }

    .sidebar-login-v2 .sidebar-login-v2__icon {
        width: 1.1rem;
        height: 1.1rem;
        line-height: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.55rem;
        color: var(--sb-muted);
        flex-shrink: 0;
        font-size: 0.8rem;
    }

    .sidebar-login-v2 .sidebar-login-v2__icon--sub {
        font-size: 0.68rem;
        opacity: 0.95;
        width: 1.1rem;
        text-align: center;
        margin-right: 0.55rem;
        color: var(--sb-muted);
        flex-shrink: 0;
    }

    .sidebar-login-v2 .nav-link.is-active .sidebar-login-v2__icon--sub {
        color: var(--sb-text);
        opacity: 1;
    }

    .sidebar-login-v2 .sidebar-login-v2__toggle {
        background: var(--sb-row);
        border: 1px solid var(--sb-border);
        font-weight: 500;
        color: var(--sb-text);
        padding: 0;
        margin-top: 0;
    }

    .sidebar-login-v2 .sidebar-login-v2__toggle:hover {
        background: var(--sb-row-hover);
        color: var(--sb-text);
    }

    .sidebar-login-v2 .sidebar-login-v2__toggle:hover .sidebar-login-v2__icon,
    .sidebar-login-v2 .sidebar-login-v2__toggle:hover .sidebar-login-v2__caret {
        color: var(--sb-text);
    }

    .sidebar-login-v2 .sidebar-login-v2__caret {
        font-size: 0.7rem;
        opacity: 1;
        transition: transform 0.22s ease;
    }

    .sidebar-login-v2 .sidebar-login-v2__toggle-left {
        padding: 0.45rem 0.8rem;
        flex: 1 1 auto;
        min-width: 0;
    }

    .sidebar-login-v2 .sidebar-login-v2__caret-wrap {
        width: 1.9rem;
        min-height: 1.9rem;
        margin: 0.18rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--sb-caret-bg);
        border: 1px solid var(--sb-border);
        border-radius: 7px;
    }

    .sidebar-login-v2 .sidebar-login-v2__toggle:hover .sidebar-login-v2__caret-wrap {
        background: rgba(255, 255, 255, 0.24);
    }

    .sidebar-login-v2 .sidebar-login-v2__toggle[aria-expanded="true"] .sidebar-login-v2__caret {
        transform: rotate(180deg);
    }

    .sidebar-login-v2 div.collapse-menu {
        background: transparent;
        margin-top: 0.18rem;
        border-radius: 0;
    }

    .sidebar-login-v2 .sidebar-login-v2__subnav {
        list-style: none;
        margin: 0;
        padding: 0.15rem 0 0.1rem;
        border-left: 2px solid rgba(255, 255, 255, 0.22);
        margin-left: 1rem;
    }

    .sidebar-login-v2 .sidebar-login-v2__subnav .nav-link {
        padding: 0.3rem 0.55rem 0.3rem 0.6rem;
        font-size: 0.78rem;
        font-weight: 450;
        color: var(--sb-muted);
        border: 0;
        border-radius: 7px;
        background: transparent;
    }

    .sidebar-login-v2 .sidebar-login-v2__subnav .nav-link:hover {
        background: var(--sb-row-hover);
        color: var(--sb-text);
    }

    .sidebar-login-v2 .sidebar-login-v2__subnav .nav-link.is-active {
        background: var(--sb-active);
        color: var(--sb-text);
        border-radius: 7px;
    }

    .sidebar-login-v2 .sidebar-login-v2__item-title {
        display: block;
        line-height: 1.3;
    }

    .sidebar-login-v2 .sidebar-login-v2__item-form {
        display: block;
        margin-top: 0.1rem;
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: var(--sb-muted);
    }

    .sidebar-login-v2 .sidebar-login-v2__subnav .nav-link.is-active .sidebar-login-v2__item-form {
        color: var(--sb-text);
    }

    .sidebar-login-v2 .sidebar-login-v2__section-title {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--sb-muted);
        padding: 0.1rem 0.2rem;
        margin: 0.2rem 0 0;
        list-style: none;
        background: transparent;
        border: 0;
    }

    .sidebar-login-v2 .sidebar-login-v2__section-title span {
        color: inherit;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .sidebar-login-v2 .sidebar-login-v2__section-title i {
        color: var(--sb-muted);
        font-size: 0.78rem;
        letter-spacing: 0;
    }

    .sidebar-login-v2 .sidebar-login-v2__divider {
        height: 0;
        border: 0;
        margin: 0;
        padding: 0;
        list-style: none;
        opacity: 0;
    }

    @media (max-width: 991px) {
        .sidebar-login-v2 {
            width: 100%;
            min-height: unset;
            border-right: none;
            border-bottom: 1px solid var(--sb-border);
        }
    }
</style>


<div class="sidebar sidebar-login sidebar-login-v2">
    <ul class="nav flex-column sidebar-login-v2__list">
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center {{ $dashboardActive ? 'is-active' : '' }}"
                href="{{ route('dashboard') }}">
                <i class="fa fa-home sidebar-login-v2__icon" aria-hidden="true"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="sidebar-login-v2__divider" role="presentation"></li>

        <li class="nav-item">
            <a class="nav-link sidebar-login-v2__toggle d-flex justify-content-between align-items-center"
                data-toggle="collapse" href="#competencyMenu" role="button" aria-expanded="true"
                aria-controls="competencyMenu">
                <span class="d-flex align-items-center sidebar-login-v2__toggle-left">
                    <i class="fa fa-wpforms sidebar-login-v2__icon" aria-hidden="true"></i>
                    <span>Competency Certificates</span>
                </span>
                <span class="sidebar-login-v2__caret-wrap">
                    <i class="fa fa-chevron-down sidebar-login-v2__caret" aria-hidden="true"></i>
                </span>
            </a>
            <div class="collapse collapse-menu show" id="competencyMenu">
                <ul class="nav flex-column certificate-menu sidebar-login-v2__subnav">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-start {{ $activeFormWh ? 'is-active' : '' }}"
                            href="{{ route('apply-form-wh') }}">
                            <i class="fa fa-angle-right sidebar-login-v2__icon--sub" aria-hidden="true"></i>
                            <span>
                                <span class="sidebar-login-v2__item-title">Wireman Helper Competency Certificate</span>
                                <span class="sidebar-login-v2__item-form">[Form H]</span>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-start {{ $activeFormW ? 'is-active' : '' }}"
                            href="{{ route('apply-form-w') }}">
                            <i class="fa fa-angle-right sidebar-login-v2__icon--sub" aria-hidden="true"></i>
                            <span>
                                <span class="sidebar-login-v2__item-title">Wireman Competency Certificate</span>
                                <span class="sidebar-login-v2__item-form">[Form W]</span>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-start {{ $activeFormP ? 'is-active' : '' }}"
                            href="{{ route('apply_form_p') }}">
                            <i class="fa fa-angle-right sidebar-login-v2__icon--sub" aria-hidden="true"></i>
                            <span>
                                <span class="sidebar-login-v2__item-title">Power Generating Station Operation &amp;
                                    Maintenance Competency Certificate</span>
                                <span class="sidebar-login-v2__item-form">[Form P]</span>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-start {{ $activeFormS ? 'is-active' : '' }}"
                            href="{{ route('apply-form-s') }}">
                            <i class="fa fa-angle-right sidebar-login-v2__icon--sub" aria-hidden="true"></i>
                            <span>
                                <span class="sidebar-login-v2__item-title">Supervisor Competency Certificate</span>
                                <span class="sidebar-login-v2__item-form">[Form S]</span>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="sidebar-login-v2__divider" role="presentation"></li>

        <li class="nav-item">
            <a class="nav-link sidebar-login-v2__toggle d-flex justify-content-between align-items-center"
                data-toggle="collapse" href="#contractorMenu" role="button" aria-expanded="true"
                aria-controls="contractorMenu">
                <span class="d-flex align-items-center sidebar-login-v2__toggle-left">
                    <i class="fa fa-file-text-o sidebar-login-v2__icon" aria-hidden="true"></i>
                    <span>Contractor Licences</span>
                </span>
                <span class="sidebar-login-v2__caret-wrap">
                    <i class="fa fa-chevron-down sidebar-login-v2__caret" aria-hidden="true"></i>
                </span>
            </a>
            <div class="collapse collapse-menu show" id="contractorMenu">
                <ul class="nav flex-column contractor-menu sidebar-login-v2__subnav">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-start {{ $activeFormA ? 'is-active' : '' }}"
                            href="{{ route('apply-form-a') }}">
                            <i class="fa fa-angle-right sidebar-login-v2__icon--sub" aria-hidden="true"></i>
                            <span>
                                <span>Electrical Contractor's Licence-Grade 'A'</span>
                                <span class="sidebar-login-v2__item-form">[Form A]</span>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-start {{ $activeFormSa ? 'is-active' : '' }}"
                            href="{{ route('apply-form-sa') }}">
                            <i class="fa fa-angle-right sidebar-login-v2__icon--sub" aria-hidden="true"></i>
                            <span>
                                <span>Electrical Contractors Licence Grade Super 'A'</span>
                                <span class="sidebar-login-v2__item-form">[Form SA]</span>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-start {{ $activeFormSb ? 'is-active' : '' }}"
                            href="{{ route('apply-form-sb') }}">
                            <i class="fa fa-angle-right sidebar-login-v2__icon--sub" aria-hidden="true"></i>
                            <span>
                                <span>Electrical Contractor's Licence-Grade `SB'</span>
                                <span class="sidebar-login-v2__item-form">[Form SB]</span>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-start {{ $activeFormB ? 'is-active' : '' }}"
                            href="{{ route('apply-form-b') }}">
                            <i class="fa fa-angle-right sidebar-login-v2__icon--sub" aria-hidden="true"></i>
                            <span>
                                <span>Electrical Contractor License 'EB'</span>
                                <span class="sidebar-login-v2__item-form">[Form B]</span>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="sidebar-login-v2__divider" role="presentation"></li>

        <li class="nav-item">
            <a class="nav-link sidebar-login-v2__toggle d-flex justify-content-between align-items-center"
                data-toggle="collapse" href="#oldRenewalsMenu" role="button" aria-expanded="true"
                aria-controls="oldRenewalsMenu">
                <span class="d-flex align-items-center sidebar-login-v2__toggle-left">
                    <i class="fa fa-refresh sidebar-login-v2__icon" aria-hidden="true"></i>
                    <span>Old Renewals</span>
                </span>
                <span class="sidebar-login-v2__caret-wrap">
                    <i class="fa fa-chevron-down sidebar-login-v2__caret" aria-hidden="true"></i>
                </span>
            </a>
            <div class="collapse collapse-menu show" id="oldRenewalsMenu">
                <ul class="nav flex-column old-renewals-menu sidebar-login-v2__subnav">
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-start {{ $activeOldCertRenewal ? 'is-active' : '' }}"
                            href="{{ route('old_certificate_renewal') }}">
                            <i class="fa fa-angle-right sidebar-login-v2__icon--sub" aria-hidden="true"></i>
                            <span>Old Certificate Renewal</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex align-items-start {{ $activeOldContractorRenewal ? 'is-active' : '' }}"
                            href="{{ route('old_contractor_renewal') }}">
                            <i class="fa fa-angle-right sidebar-login-v2__icon--sub" aria-hidden="true"></i>
                            <span>Old Contractor Renewal</span>
                        </a>
                    </li>
                </ul>
            </div>
        </li>

        <li class="sidebar-login-v2__section-title" role="presentation">
            <span><i class="fa fa-ellipsis-h" aria-hidden="true"></i> Others</span>
        </li>

        <li class="nav-item">
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('expiry_date_change') ? 'is-active' : '' }}"
                href="{{ route('expiry_date_change') }}">
                <i class="fa fa-angle-right sidebar-login-v2__icon" aria-hidden="true"></i>
                <span>License Expiry Date Change</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link d-flex align-items-center {{ request()->routeIs('previous_licence_date_change') ? 'is-active' : '' }}"
                href="{{ route('previous_licence_date_change') }}">
                <i class="fa fa-angle-right sidebar-login-v2__icon" aria-hidden="true"></i>
                <span>C &amp; W Licence Change</span>
            </a>
        </li>

        {{-- <li class="nav-item">
            <a href="" class="nav-link d-flex align-items-center">
                <i class="fa fa-clipboard sidebar-login-v2__icon" aria-hidden="true"></i>
                <span>Previous (or) Old License Details</span>
            </a>
        </li> --}}
    </ul>
</div>