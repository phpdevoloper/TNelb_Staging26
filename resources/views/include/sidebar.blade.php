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
    $activeExpiry = request()->routeIs('expiry_date_change');
    $activePrevLicence = request()->routeIs('previous_licence_date_change');
    $activeFormWhDigitization = request()->routeIs('apply-form-wh_d');
    $activeFormWDigitization = request()->routeIs('apply-form-w_d');
    $activeFormPDigitization = request()->routeIs('apply_form_p_d');
    $activeFormSDigitization = request()->routeIs('apply-form-s_d');
    $activeContractorDigitization = request()->routeIs('apply-form-a_d');
    $activeCompetencyAlteration = request()->routeIs('form_s_alt');
    $activeFormSAlteration = request()->routeIs('form_s_alt') && strtoupper((string) request('form', 'S')) === 'S';
    $activeFormWhAlteration = request()->routeIs('form_s_alt') && strtoupper((string) request('form')) === 'H';
    $activeFormWAlteration = request()->routeIs('form_s_alt') && strtoupper((string) request('form')) === 'W';
    $activeFormPAlteration = request()->routeIs('form_s_alt') && strtoupper((string) request('form')) === 'P';
    $activeContractorAlteration = request()->routeIs('alteration_cl');
@endphp
@once
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome-4.7.0/css/font-awesome.min.css') }}">
@endonce
<style>
    .sb-nav {
        --sb-bg-start: #0f2c4a;
        --sb-bg-end: #0b2238;
        --sb-text: #ffffff;
        --sb-muted: rgba(255, 255, 255, 0.78);
        --sb-divider: rgba(255, 255, 255, 0.10);
        --sb-overlay: rgba(255, 255, 255, 0.10);
        --sb-overlay-strong: rgba(255, 255, 255, 0.18);

        --sb-c-competency: #2ebb84;
        --sb-c-contractor: #f59e42;
        --sb-c-renewals: #60a5fa;

        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        background: linear-gradient(180deg, var(--sb-bg-start) 0%, var(--sb-bg-end) 100%);
        color: var(--sb-text);
        width: 340px;
        min-height: 100%;
        padding: 12px 10px 16px;
        border-right: 1px solid rgba(255, 255, 255, 0.08);
        box-sizing: border-box;
    }

    .sb-nav *,
    .sb-nav *::before,
    .sb-nav *::after {
        box-sizing: border-box;
    }

    .sb-nav .sb-nav__list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .sb-nav .sb-nav__group {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    /* Standalone items (Dashboard / Others) */
    .sb-nav .sb-nav__link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 10px;
        color: var(--sb-text);
        font-size: 0.86rem;
        font-weight: 500;
        text-decoration: none;
        background: var(--sb-overlay);
        border: 1px solid rgba(255, 255, 255, 0.10);
        transition: background 0.18s ease, transform 0.18s ease;
    }

    .sb-nav .sb-nav__link:hover {
        background: var(--sb-overlay-strong);
        color: var(--sb-text);
        text-decoration: none;
    }

    .sb-nav .sb-nav__link.is-active {
        background: rgba(255, 255, 255, 0.22);
        border-color: rgba(255, 255, 255, 0.35);
        font-weight: 600;
    }

    .sb-nav .sb-nav__link i {
        width: 18px;
        text-align: center;
        font-size: 0.9rem;
        color: var(--sb-muted);
    }

    .sb-nav .sb-nav__link.is-active i {
        color: var(--sb-text);
    }

    /* Section heading (Others) */
    .sb-nav .sb-nav__section-title {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: rgba(255, 255, 255, 0.55);
        padding: 6px 10px 2px;
        display: flex;
        align-items: center;
        gap: 6px;
        margin: 4px 0 -2px;
    }

    /* Tinted card group */
    .sb-nav .sb-nav__card {
        border-radius: 12px;
        padding: 8px 8px 10px;
        border: 1px solid;
        background-clip: padding-box;
    }

    .sb-nav .sb-nav__card-toggle {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
        padding: 8px 10px;
        background: transparent;
        border: 0;
        color: var(--sb-text);
        font-size: 0.88rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-decoration: none;
        cursor: pointer;
        border-radius: 8px;
        transition: background 0.18s ease;
    }

    .sb-nav .sb-nav__card-toggle:hover {
        background: rgba(255, 255, 255, 0.10);
        color: var(--sb-text);
        text-decoration: none;
    }

    .sb-nav .sb-nav__card-toggle-icon {
        width: 26px;
        height: 26px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        background: rgba(255, 255, 255, 0.16);
        color: var(--sb-text);
        flex-shrink: 0;
    }

    .sb-nav .sb-nav__card-toggle-title {
        flex: 1 1 auto;
        min-width: 0;
    }

    .sb-nav .sb-nav__card-toggle-caret {
        font-size: 0.7rem;
        opacity: 0.9;
        transition: transform 0.22s ease;
    }

    .sb-nav .sb-nav__card-toggle[aria-expanded="true"] .sb-nav__card-toggle-caret {
        transform: rotate(180deg);
    }

    .sb-nav .sb-nav__card-body {
        margin-top: 6px;
    }

    .sb-nav .sb-nav__sublist {
        list-style: none;
        margin: 0;
        padding: 4px 0 2px 8px;
        border-left: 2px solid rgba(255, 255, 255, 0.18);
        margin-left: 12px;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .sb-nav .sb-nav__sublink {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 8px;
        color: rgba(255, 255, 255, 0.86);
        font-size: 0.8rem;
        font-weight: 500;
        line-height: 1.3;
        text-decoration: none;
        transition: background 0.18s ease, color 0.18s ease;
    }

    .sb-nav .sb-nav__sublink:hover {
        background: rgba(255, 255, 255, 0.12);
        color: var(--sb-text);
        text-decoration: none;
    }

    .sb-nav .sb-nav__sublink.is-active {
        background: rgba(255, 255, 255, 0.22);
        color: var(--sb-text);
        font-weight: 600;
    }

    .sb-nav .sb-nav__sublink-bullet {
        font-size: 0.65rem;
        margin-top: 4px;
        color: rgba(255, 255, 255, 0.55);
        flex-shrink: 0;
    }

    .sb-nav .sb-nav__sublink.is-active .sb-nav__sublink-bullet {
        color: var(--sb-text);
    }

    .sb-nav .sb-nav__sublink-text {
        display: flex;
        flex-direction: column;
        gap: 1px;
        min-width: 0;
    }

    .sb-nav .sb-nav__sublink-form {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: rgba(255, 255, 255, 0.6);
    }

    .sb-nav .sb-nav__sublink.is-active .sb-nav__sublink-form {
        color: var(--sb-text);
    }

    .sb-nav .sb-nav__item-toggle {
        display: flex;
        align-items: center;
        width: 100%;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 8px;
        border: 0;
        background: transparent;
        color: rgba(255, 255, 255, 0.86);
        font-size: 0.8rem;
        font-weight: 500;
        line-height: 1.3;
        text-align: left;
        transition: background 0.18s ease, color 0.18s ease;
    }

    .sb-nav .sb-nav__item-toggle:hover {
        background: rgba(255, 255, 255, 0.12);
        color: var(--sb-text);
        text-decoration: none;
    }

    .sb-nav .sb-nav__item-toggle.is-active {
        background: rgba(255, 255, 255, 0.22);
        color: var(--sb-text);
        font-weight: 600;
    }

    .sb-nav .sb-nav__item-toggle-caret {
        margin-left: auto;
        font-size: 0.7rem;
        opacity: 0.9;
        transition: transform 0.22s ease;
    }

    .sb-nav .sb-nav__item-toggle[aria-expanded="true"] .sb-nav__item-toggle-caret {
        transform: rotate(180deg);
    }

    .sb-nav .sb-nav__child-list {
        list-style: none;
        margin: 4px 0 6px;
        padding: 4px 0 2px 18px;
        border-left: 1px dashed rgba(255, 255, 255, 0.2);
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .sb-nav .sb-nav__child-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 7px;
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.76rem;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.18s ease, color 0.18s ease;
    }

    .sb-nav .sb-nav__child-link:hover {
        background: rgba(255, 255, 255, 0.11);
        color: var(--sb-text);
        text-decoration: none;
    }

    .sb-nav .sb-nav__child-link.is-active {
        background: rgba(255, 255, 255, 0.22);
        color: var(--sb-text);
        font-weight: 600;
    }

    .sb-nav .sb-nav__child-bullet {
        font-size: 0.58rem;
        color: rgba(255, 255, 255, 0.56);
    }

    .sb-nav .sb-nav__child-link.is-active .sb-nav__child-bullet {
        color: var(--sb-text);
    }

    /* Section variants */
    .sb-nav .sb-nav__card--competency {
        background: linear-gradient(180deg, rgba(46, 187, 132, 0.22), rgba(46, 187, 132, 0.08));
        border-color: rgba(46, 187, 132, 0.55);
        box-shadow: inset 3px 0 0 0 var(--sb-c-competency);
    }

    .sb-nav .sb-nav__card--competency .sb-nav__card-toggle-icon {
        background: rgba(46, 187, 132, 0.35);
        color: #d6f7e8;
    }

    .sb-nav .sb-nav__card--competency .sb-nav__sublist {
        border-left-color: rgba(46, 187, 132, 0.6);
    }

    .sb-nav .sb-nav__card--contractor {
        background: linear-gradient(180deg, rgba(245, 158, 66, 0.22), rgba(245, 158, 66, 0.08));
        border-color: rgba(245, 158, 66, 0.55);
        box-shadow: inset 3px 0 0 0 var(--sb-c-contractor);
    }

    .sb-nav .sb-nav__card--contractor .sb-nav__card-toggle-icon {
        background: rgba(245, 158, 66, 0.38);
        color: #ffe6c7;
    }

    .sb-nav .sb-nav__card--contractor .sb-nav__sublist {
        border-left-color: rgba(245, 158, 66, 0.6);
    }

    .sb-nav .sb-nav__card--renewals {
        background: linear-gradient(180deg, rgba(96, 165, 250, 0.22), rgba(96, 165, 250, 0.08));
        border-color: rgba(96, 165, 250, 0.55);
        box-shadow: inset 3px 0 0 0 var(--sb-c-renewals);
    }

    .sb-nav .sb-nav__card--renewals .sb-nav__card-toggle-icon {
        background: rgba(96, 165, 250, 0.38);
        color: #d6e7ff;
    }

    .sb-nav .sb-nav__card--renewals .sb-nav__sublist {
        border-left-color: rgba(96, 165, 250, 0.6);
    }

    @media (max-width: 991px) {
        .sb-nav {
            width: 100%;
            min-height: unset;
            border-right: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.10);
        }
    }
</style>

<div class="sidebar sidebar-login sb-nav">
    <ul class="sb-nav__list">
        <li class="sb-nav__group">
            <a class="sb-nav__link {{ $dashboardActive ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
                <i class="fa fa-home" aria-hidden="true"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="sb-nav__group">
            <div class="sb-nav__card sb-nav__card--competency">
                <a class="sb-nav__card-toggle" data-toggle="collapse" href="#competencyMenu" role="button"
                    aria-expanded="true" aria-controls="competencyMenu">
                    <span class="sb-nav__card-toggle-icon"><i class="fa fa-wpforms" aria-hidden="true"></i></span>
                    <span class="sb-nav__card-toggle-title">Competency Certificates</span>
                    <i class="fa fa-chevron-down sb-nav__card-toggle-caret" aria-hidden="true"></i>
                </a>
                <div class="collapse show sb-nav__card-body" id="competencyMenu">
                    <ul class="sb-nav__sublist" id="competencyMenuItems">
                        <li>
                            <button class="sb-nav__item-toggle {{ ($activeFormWh || $activeFormWhDigitization || $activeFormWhAlteration) ? 'is-active' : '' }}"
                                type="button" data-toggle="collapse" data-target="#competencyWhMenu"
                                aria-expanded="{{ ($activeFormWh || $activeFormWhDigitization || $activeFormWhAlteration) ? 'true' : 'false' }}"
                                aria-controls="competencyWhMenu">
                                <i class="fa fa-angle-right sb-nav__sublink-bullet" aria-hidden="true"></i>
                                <span class="sb-nav__sublink-text">
                                    <span>Wireman Helper Competency Certificate</span>
                                    <span class="sb-nav__sublink-form">[Form H]</span>
                                </span>
                                <i class="fa fa-chevron-down sb-nav__item-toggle-caret" aria-hidden="true"></i>
                            </button>
                            <div class="collapse {{ ($activeFormWh || $activeFormWhDigitization || $activeFormWhAlteration) ? 'show' : '' }}"
                                data-parent="#competencyMenuItems"
                                id="competencyWhMenu">
                                <ul class="sb-nav__child-list">
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormWh ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-wh') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>New</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ request()->routeIs('apply-form-wh_d') ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-wh_d') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Digitisation</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormWhAlteration ? 'is-active' : '' }}"
                                            href="{{ route('form_s_alt', ['form' => 'H']) }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Alteration</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li>
                            <button class="sb-nav__item-toggle {{ ($activeFormW || $activeFormWDigitization || $activeFormWAlteration) ? 'is-active' : '' }}"
                                type="button" data-toggle="collapse" data-target="#competencyWMenu"
                                aria-expanded="{{ ($activeFormW || $activeFormWDigitization || $activeFormWAlteration) ? 'true' : 'false' }}"
                                aria-controls="competencyWMenu">
                                <i class="fa fa-angle-right sb-nav__sublink-bullet" aria-hidden="true"></i>
                                <span class="sb-nav__sublink-text">
                                    <span>Wireman Competency Certificate</span>
                                    <span class="sb-nav__sublink-form">[Form W]</span>
                                </span>
                                <i class="fa fa-chevron-down sb-nav__item-toggle-caret" aria-hidden="true"></i>
                            </button>
                            <div class="collapse {{ ($activeFormW || $activeFormWDigitization || $activeFormWAlteration) ? 'show' : '' }}"
                                data-parent="#competencyMenuItems"
                                id="competencyWMenu">
                                <ul class="sb-nav__child-list">
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormW ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-w') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>New</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ request()->routeIs('apply-form-w_d') ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-w_d', ['form' => 'W']) }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Digitisation</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormWAlteration ? 'is-active' : '' }}"
                                            href="{{ route('form_s_alt', ['form' => 'W']) }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Alteration</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li>
                            <button class="sb-nav__item-toggle {{ ($activeFormP || $activeFormPDigitization || $activeFormPAlteration) ? 'is-active' : '' }}"
                                type="button" data-toggle="collapse" data-target="#competencyPMenu"
                                aria-expanded="{{ ($activeFormP || $activeFormPDigitization || $activeFormPAlteration) ? 'true' : 'false' }}"
                                aria-controls="competencyPMenu">
                                <i class="fa fa-angle-right sb-nav__sublink-bullet" aria-hidden="true"></i>
                                <span class="sb-nav__sublink-text">
                                    <span>Power Generating Station Operation &amp; Maintenance Competency
                                        Certificate</span>
                                    <span class="sb-nav__sublink-form">[Form P]</span>
                                </span>
                                <i class="fa fa-chevron-down sb-nav__item-toggle-caret" aria-hidden="true"></i>
                            </button>
                            <div class="collapse {{ ($activeFormP || $activeFormPDigitization || $activeFormPAlteration) ? 'show' : '' }}"
                                data-parent="#competencyMenuItems"
                                id="competencyPMenu">
                                <ul class="sb-nav__child-list">
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormP ? 'is-active' : '' }}"
                                            href="{{ route('apply_form_p') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>New</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ request()->routeIs('apply_form_p_d') ? 'is-active' : '' }}"
                                            href="{{ route('apply_form_p_d') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Digitisation</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormPAlteration ? 'is-active' : '' }}"
                                            href="{{ route('form_s_alt', ['form' => 'P']) }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Alteration</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li>
                            <button class="sb-nav__item-toggle {{ ($activeFormS || $activeFormSDigitization || $activeFormSAlteration) ? 'is-active' : '' }}"
                                type="button" data-toggle="collapse" data-target="#competencySMenu"
                                aria-expanded="{{ ($activeFormS || $activeFormSDigitization || $activeFormSAlteration) ? 'true' : 'false' }}"
                                aria-controls="competencySMenu">
                                <i class="fa fa-angle-right sb-nav__sublink-bullet" aria-hidden="true"></i>
                                <span class="sb-nav__sublink-text">
                                    <span>Supervisor Competency Certificate</span>
                                    <span class="sb-nav__sublink-form">[Form S]</span>
                                </span>
                                <i class="fa fa-chevron-down sb-nav__item-toggle-caret" aria-hidden="true"></i>
                            </button>
                            <div class="collapse {{ ($activeFormS || $activeFormSDigitization || $activeFormSAlteration) ? 'show' : '' }}"
                                data-parent="#competencyMenuItems"
                                id="competencySMenu">
                                <ul class="sb-nav__child-list">
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormS ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-s') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>New</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ request()->routeIs('apply-form-s_d') ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-s_d', ['form' => 'S']) }}"
                                            data-form="S">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Digitisation</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormSAlteration ? 'is-active' : '' }}"
                                            href="{{ route('form_s_alt', ['form' => 'S']) }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Alteration</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </li>

        <li class="sb-nav__group">
            <div class="sb-nav__card sb-nav__card--contractor">
                <a class="sb-nav__card-toggle" data-toggle="collapse" href="#contractorMenu" role="button"
                    aria-expanded="true" aria-controls="contractorMenu">
                    <span class="sb-nav__card-toggle-icon"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                    <span class="sb-nav__card-toggle-title">Contractor Licences</span>
                    <i class="fa fa-chevron-down sb-nav__card-toggle-caret" aria-hidden="true"></i>
                </a>
                <div class="collapse show sb-nav__card-body" id="contractorMenu">
                    <ul class="sb-nav__sublist" id="contractorMenuItems">
                        <li>
                            <button class="sb-nav__item-toggle {{ ($activeFormA || $activeContractorDigitization || $activeContractorAlteration) ? 'is-active' : '' }}"
                                type="button" data-toggle="collapse" data-target="#contractorAMenu"
                                aria-expanded="{{ ($activeFormA || $activeContractorDigitization || $activeContractorAlteration) ? 'true' : 'false' }}"
                                aria-controls="contractorAMenu">
                                <i class="fa fa-angle-right sb-nav__sublink-bullet" aria-hidden="true"></i>
                                <span class="sb-nav__sublink-text">
                                    <span>Electrical Contractor's Licence-Grade 'A'</span>
                                    <span class="sb-nav__sublink-form">[Form A]</span>
                                </span>
                                <i class="fa fa-chevron-down sb-nav__item-toggle-caret" aria-hidden="true"></i>
                            </button>
                            <div class="collapse {{ ($activeFormA || $activeContractorDigitization || $activeContractorAlteration) ? 'show' : '' }}"
                                data-parent="#contractorMenuItems"
                                id="contractorAMenu">
                                <ul class="sb-nav__child-list">
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormA ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-a') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>New</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeContractorDigitization ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-a_d') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Digitisation</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeContractorAlteration ? 'is-active' : '' }}"
                                            href="{{ route('alteration_cl') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Alteration</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li>
                            <button class="sb-nav__item-toggle {{ $activeFormSa ? 'is-active' : '' }}"
                                type="button" data-toggle="collapse" data-target="#contractorSaMenu"
                                aria-expanded="{{ $activeFormSa ? 'true' : 'false' }}"
                                aria-controls="contractorSaMenu">
                                <i class="fa fa-angle-right sb-nav__sublink-bullet" aria-hidden="true"></i>
                                <span class="sb-nav__sublink-text">
                                    <span>Electrical Contractors Licence Grade Super 'A'</span>
                                    <span class="sb-nav__sublink-form">[Form SA]</span>
                                </span>
                                <i class="fa fa-chevron-down sb-nav__item-toggle-caret" aria-hidden="true"></i>
                            </button>
                            <div class="collapse {{ $activeFormSa ? 'show' : '' }}"
                                data-parent="#contractorMenuItems"
                                id="contractorSaMenu">
                                <ul class="sb-nav__child-list">
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormSa ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-sa') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>New</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeContractorDigitization ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-a_d') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Digitisation</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeContractorAlteration ? 'is-active' : '' }}"
                                            href="{{ route('alteration_cl') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Alteration</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li>
                            <button class="sb-nav__item-toggle {{ $activeFormSb ? 'is-active' : '' }}"
                                type="button" data-toggle="collapse" data-target="#contractorSbMenu"
                                aria-expanded="{{ $activeFormSb ? 'true' : 'false' }}"
                                aria-controls="contractorSbMenu">
                                <i class="fa fa-angle-right sb-nav__sublink-bullet" aria-hidden="true"></i>
                                <span class="sb-nav__sublink-text">
                                    <span>Electrical Contractor's Licence-Grade `SB'</span>
                                    <span class="sb-nav__sublink-form">[Form SB]</span>
                                </span>
                                <i class="fa fa-chevron-down sb-nav__item-toggle-caret" aria-hidden="true"></i>
                            </button>
                            <div class="collapse {{ $activeFormSb ? 'show' : '' }}"
                                data-parent="#contractorMenuItems"
                                id="contractorSbMenu">
                                <ul class="sb-nav__child-list">
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormSb ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-sb') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>New</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeContractorDigitization ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-a_d') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Digitisation</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeContractorAlteration ? 'is-active' : '' }}"
                                            href="{{ route('alteration_cl') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Alteration</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li>
                            <button class="sb-nav__item-toggle {{ $activeFormB ? 'is-active' : '' }}"
                                type="button" data-toggle="collapse" data-target="#contractorBMenu"
                                aria-expanded="{{ $activeFormB ? 'true' : 'false' }}"
                                aria-controls="contractorBMenu">
                                <i class="fa fa-angle-right sb-nav__sublink-bullet" aria-hidden="true"></i>
                                <span class="sb-nav__sublink-text">
                                    <span>Electrical Contractor License 'EB'</span>
                                    <span class="sb-nav__sublink-form">[Form B]</span>
                                </span>
                                <i class="fa fa-chevron-down sb-nav__item-toggle-caret" aria-hidden="true"></i>
                            </button>
                            <div class="collapse {{ $activeFormB ? 'show' : '' }}"
                                data-parent="#contractorMenuItems"
                                id="contractorBMenu">
                                <ul class="sb-nav__child-list">
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeFormB ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-b') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>New</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeContractorDigitization ? 'is-active' : '' }}"
                                            href="{{ route('apply-form-a_d') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Digitisation</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="sb-nav__child-link {{ $activeContractorAlteration ? 'is-active' : '' }}"
                                            href="{{ route('alteration_cl') }}">
                                            <i class="fa fa-circle sb-nav__child-bullet" aria-hidden="true"></i>
                                            <span>Alteration</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </li>

        <li class="sb-nav__section-title" role="presentation">
            <i class="fa fa-ellipsis-h" aria-hidden="true"></i>
            <span>Others</span>
        </li>

        <li class="sb-nav__group">
            <a class="sb-nav__link {{ $activeExpiry ? 'is-active' : '' }}" href="{{ route('expiry_date_change') }}">
                <i class="fa fa-calendar-times-o" aria-hidden="true"></i>
                <span>License Expiry Date Change</span>
            </a>
        </li>

        <li class="sb-nav__group">
            <a class="sb-nav__link {{ $activePrevLicence ? 'is-active' : '' }}"
                href="{{ route('previous_licence_date_change') }}">
                <i class="fa fa-exchange" aria-hidden="true"></i>
                <span>C &amp; W Licence Change</span>
            </a>
        </li>
    </ul>
</div>
