<!DOCTYPE html>
<html lang="en">
    
    @php
use Illuminate\Support\Facades\Auth;
@endphp

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'TNELB - Home')</title>
    {{-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> --}}
    {{-- <meta http-equiv="X-UA-Compatible" content="IE=edge"> --}}
    {{-- Proper mobile scaling (required for responsive phones). Keeps pinch-zoom accessible. --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    
    
    <!-- Stylesheets -->
    <link href="{{ asset('assets/css/bootstrap.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome-4.7.0/css/font-awesome.min.css') }}">
    <!-- CDN fallback so icon font files load (local /fonts/ may be incomplete) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/responsive.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/color-2.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/main.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/page_top.css') }}" rel="stylesheet">
   
    <link href="{{ asset('assets/admin/src/plugins/src/flatpickr/flatpickr.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/src/plugins/css/light/flatpickr/custom-flatpickr.css') }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">



    <!-- Google Fonts -->
    {{-- <link href="https://fonts.googleapis.com/css2?family=Arimo:wght@400;700&family=Merriweather:ital@0;1&display=swap" rel="stylesheet"> --}}

    <link rel="shortcut icon" href="{{ asset('assets/images/logo/favicon.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('assets/images/logo/favicon.png') }}" type="image/x-icon">



    <style>

        .header-container{
            padding: 8px 0px;
        }

        .logo-text{
            display: inline-block;
            vertical-align: middle;
            margin: 0 0 0 10px;
        }

        .logo-text h3{
            font-weight: 700;
        }   

        .instruct li {
            list-style: unset;
        }

        #verify_btn {
            min-width: 80px;  /* or whatever suits */
            height: 38px;     /* match your .form-control height */
            line-height: 1.5;
            white-space: nowrap;
            overflow: hidden;
        }

        #verify_result{
            color: red;
        }

        /* Ensure native calendar icon is visible for date inputs (Chrome/Edge WebKit).
           Our bootstrap.css sets -webkit-appearance:listbox for date inputs, which can hide the picker icon. */
        .apply-form input[type="date"] {
            -webkit-appearance: initial !important;
            appearance: auto !important;
            padding-right: 2.25rem; /* leave room for indicator */
        }
        .apply-form input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 1 !important;
            display: block !important;
            visibility: visible !important;
            cursor: pointer;
        }

        .swal2-icon-content {
            font-size: 1.75em;
        }

        .swal2-icon.swal2-info.swal2-icon-show {
            width: 0px;
            height: 21px;
            margin-top: 10px;
        }

        h2#swal2-title {
            padding: 0.3em 1em 0;
        }

       button.btn-close.btn-close-white {
            background: red;
            padding: 6px;
            color: #fff;
        }

    
        .show-list-numbers ol {
            list-style-type: decimal !important;
            list-style-position: outside !important;
            margin-left: 20px !important;
            padding-left: 20px !important;
        }

        .show-list-numbers ul {
            list-style-type: disc !important;
            list-style-position: outside !important;
            margin-left: 20px !important;
            padding-left: 20px !important;
        }

        /* This is the magic fix */
        .show-list-numbers ol li {
            list-style-type: decimal !important;
            display: list-item !important;
        }

        .show-list-numbers ul li {
            list-style-type: disc !important;
            display: list-item !important;
        }

        .info-grid {
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 8px 20px;
            font-size: 14px;
        }

        .info-grid .label {
            font-weight: bold;
        }

        .stylish-divider {
            position: relative;
            padding-right: 20px;
        }

        .stylish-divider::after {
            content: "";
            position: absolute;
            top: 20px;
            bottom: 20px;
            right: 0;
            width: 3px;
            background-color: #0d6efd;
            box-shadow: 0 0 6px rgba(13,110,253,0.5);
            border-radius: 10px;
        }

        .swal2-timer-progress-bar {
            background-color: #035ab3 !important; /* Red */
        }

        /*
         | Header-style-two: responsive layout without viewport @media in this block.
         | Uses container queries + clamp/flex/grid. Theme responsive.css may still ship @media.
         */
        .main-header.header-style-two {
            container-type: inline-size;
            container-name: hdrSite;
        }

        .main-header.header-style-two .header-top-two > .container {
            container-type: inline-size;
            container-name: hdrTop;
            padding-inline: max(12px, env(safe-area-inset-left))
                max(12px, env(safe-area-inset-right));
        }

        .main-header.header-style-two .logo-fun > .container {
            container-type: inline-size;
            container-name: hdrLogo;
            padding-inline: max(12px, env(safe-area-inset-left))
                max(12px, env(safe-area-inset-right));
        }

        /* Top bar: flat strip, no nested “pills” — keeps utility row readable */
        .main-header.header-style-two .header-top-two.bg-gray {
            background: #eef1f5;
            border-bottom: 1px solid #d5dde6;
            padding-block: 0.45rem 0.5rem;
        }

        .main-header.header-style-two .header-top-two .row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, auto);
            align-items: center;
            column-gap: 1rem;
            row-gap: 0.5rem;
            margin-inline: 0;
        }

        .main-header.header-style-two .header-top-two .top-social {
            justify-self: end;
            min-width: 0;
        }

        .main-header.header-style-two .header-top-two [class*="col-"] {
            width: auto;
            max-width: none;
            padding-inline: calc(var(--bs-gutter-x, 0.75rem) / 2);
        }

        .main-header.header-style-two .header-top-two .top-info.text-center.text-md-left,
        .main-header.header-style-two .header-top-two .top-social {
            margin-inline: 0;
        }

        /* page_top.css forces line-height: 10px on these */
        .main-header.header-style-two .header-top-two ul.top-info li {
            line-height: 1.45;
            padding-right: 0;
            text-align: inherit;
        }

        .main-header.header-style-two .header-top-two .info-text.color-dark {
            color: #2c3b4d !important;
        }

        .main-header.header-style-two .header-top-two .info-text {
            overflow-wrap: break-word;
            margin: 0;
            font-weight: 500;
            font-size: clamp(0.78rem, 0.6rem + 0.95cqi, 0.9375rem);
            line-height: 1.45;
            max-width: 72ch;
        }

        .main-header.header-style-two .header-top-two .toolbarline {
            display: none !important;
        }

        /* Legacy markup: separator list items only held .toolbarline */
        .main-header.header-style-two .header-top-two .top-social ul li:has(> span.toolbarline:only-child) {
            display: none !important;
        }

        .main-header.header-style-two .header-top-two .top-social ul {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 0.15rem;
            margin: 0;
            padding: 0 !important;
            list-style: none;
            margin-right: 0 !important;
        }

        .main-header.header-style-two .header-top-two .top-social ul li {
            display: flex;
            align-items: center;
            line-height: 0;
        }

        .main-header.header-style-two .header-top-two .top-social ul li:not(.topbar-search-li) {
            order: 1;
        }

        .main-header.header-style-two .header-top-two .topbar-search-li {
            order: 99;
            margin-left: 0.35rem;
            padding-left: 0.65rem;
            border-left: 1px solid #cbd5df;
        }

        .main-header.header-style-two .header-top-two .top-social ul li a:not(.searchBox) {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            min-height: 2rem;
            padding: 0.2rem !important;
            margin: 0;
            border: none !important;
            border-radius: 4px;
            color: #4a5b6f !important;
            font-size: 15px;
        }

        .main-header.header-style-two .header-top-two .top-social ul li a:not(.searchBox):hover {
            background: transparent;
            color: #035ab3 !important;
        }

        .main-header.header-style-two .header-top-two .top-social ul li a:not(.searchBox):focus-visible {
            outline: 2px solid #035ab3;
            outline-offset: 1px;
        }

        .main-header.header-style-two .header-top-two li a.searchBox {
            display: inline-flex !important;
            align-items: center;
            gap: 2px;
            flex-direction: row !important;
            min-height: 2rem;
            padding: 0 0.35rem 0 0.5rem !important;
            border: 1px solid #c5cdd8 !important;
            border-radius: 6px;
            background: #fff !important;
            color: #2c3b4d !important;
            box-shadow: none;
        }

        .main-header.header-style-two .header-top-two li a.searchBox:hover {
            border-color: #a8b4c4 !important;
        }

        .main-header.header-style-two .header-top-two .searchInput {
            width: clamp(5.5rem, 18cqi + 3rem, 9.5rem) !important;
            max-width: 10rem;
            padding: 0.2rem 0 !important;
            font-size: 0.8125rem !important;
            line-height: 1.3;
            border: none !important;
            background: transparent !important;
        }

        .main-header.header-style-two .header-top-two .searchButton {
            color: #5a6b7d !important;
            min-width: 1.75rem;
            padding: 0 0.15rem;
        }

        .main-header.header-style-two .header-top-two .searchButton:hover {
            color: #035ab3 !important;
        }

        /* Do not override Bootstrap flex on .col-* here — flex: 1 1 14rem squeezed two skinny columns on phones */
        .main-header.header-style-two .logo-fun .logo-fun-row {
            align-items: center;
        }

        .main-header.header-style-two .logo-fun .logo-fun-brand {
            min-width: 0;
        }

        /* page_top.css floats this list and breaks header flow + overlaps */
        .main-header.header-style-two .logo-fun .logo-fun-actions ul.top-info-box {
            float: none !important;
            justify-content: center;
            width: 100%;
        }

        @container hdrLogo (inline-size >= 36rem) {
            .main-header.header-style-two .logo-fun .logo-fun-actions ul.top-info-box {
                justify-content: flex-end;
            }
        }

        .main-header.header-style-two .logo-fun .logo-fun-actions .top-info-box li {
            padding-right: 0;
            margin-right: 0;
        }

        .main-header.header-style-two .logo-fun .logo-fun-actions .header-get-a-quote .btn {
            max-width: 100%;
            white-space: normal;
            font-size: clamp(0.8rem, 0.72rem + 0.35cqi, 1rem);
            line-height: 1.25;
            padding-inline: clamp(12px, 3cqi, 20px);
        }

        .main-header.header-style-two .logo-fun .top-info-box {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        /* Crest + titles stay one horizontal band (official layout); text stacks only inside .logo-text */
        .main-header.header-style-two .logo-fun .logo {
            width: 100%;
            min-width: 0;
        }

        .main-header.header-style-two .logo a {
            display: flex;
            flex-wrap: nowrap;
            align-items: center;
            gap: 0.5rem 0.75rem;
            min-width: 0;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            text-decoration: none;
        }

        .main-header.header-style-two .logo .logo-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 0.08em;
            min-width: 0;
            flex: 1 1 0%;
            vertical-align: unset;
            margin: 0;
        }

        .main-header.header-style-two .logo .logo-text h3 {
            font-size: clamp(0.78rem, 0.5rem + 1.55cqi, 1.35rem);
            line-height: 1.22;
            margin: 0;
            overflow-wrap: normal;
            word-break: normal;
            hyphens: manual;
            font-weight: 700;
        }

        .main-header.header-style-two .logo .logo-text h5 {
            font-size: clamp(0.68rem, 0.46rem + 1cqi, 1rem);
            line-height: 1.22;
            margin: 0;
            overflow-wrap: normal;
            word-break: normal;
            font-weight: 600;
            opacity: 0.92;
        }

        .main-header.header-style-two .logo .site_logo {
            max-width: clamp(44px, 7cqi + 38px, 120px);
            width: auto;
            height: auto;
            flex-shrink: 0;
            object-fit: contain;
            align-self: center;
        }

        .main-header.header-style-two .header-upper .inner-container {
            column-gap: clamp(0.35rem, 1.5cqi, 1rem);
            row-gap: 0.35rem;
        }

        .main-header.header-style-two .header-upper .nav-outer {
            min-width: 0;
            flex: 1 1 auto;
        }

        /* Single search: top bar only (#txt_search). Theme also renders search in .navbar-right — drop it. */
        .main-header.header-style-two .header-upper .navbar-right,
        .main-header.header-style-two .sticky-header .navbar-right {
            display: none !important;
        }

        .main-header.header-style-two .main-menu .navigation {
            row-gap: 0.15rem;
        }

        .main-header.header-style-two .main-menu .navigation > li {
            margin-right: clamp(0.35rem, 1.2cqi + 0.2rem, 2.1rem);
        }

        @container hdrTop (inline-size < 26rem) {
            .main-header.header-style-two .header-top-two .row {
                grid-template-columns: 1fr;
                justify-items: center;
                text-align: center;
            }

            .main-header.header-style-two .header-top-two .info-text {
                max-width: 100%;
            }

            .main-header.header-style-two .header-top-two .top-social {
                justify-self: center;
            }

            .main-header.header-style-two .header-top-two .top-social ul {
                justify-content: center;
            }

            .main-header.header-style-two .header-top-two .topbar-search-li {
                margin-left: 0;
                padding-left: 0;
                border-left: none;
            }
        }

        /* ~1139px theme breakpoint, keyed to header width (full-bleed ≈ viewport). */
        @container hdrSite (inline-size < 71.25rem) {
            .main-header.header-style-two .sticky-header {
                display: none !important;
            }

            .main-header.header-style-two .header-upper .inner-container {
                justify-content: flex-end;
            }

            .main-header.header-style-two .nav-outer {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: flex-end;
                width: 100%;
            }

            .main-header.header-style-two .nav-outer .main-menu {
                display: none !important;
            }

            .main-header.header-style-two .nav-outer .mobile-nav-toggler {
                display: flex !important;
                align-items: center;
                justify-content: center;
                float: none;
                margin: 0;
                margin-left: auto;
                order: 4;
                flex-shrink: 0;
            }
        }

    </style>

<style>
    .popup-overlay_pdf {
        display: none;
        position: fixed;
        inset: 0;
        width: 100%;
        height: 100%;
        padding: max(12px, env(safe-area-inset-top)) max(12px, env(safe-area-inset-right))
            max(12px, env(safe-area-inset-bottom)) max(12px, env(safe-area-inset-left));
        box-sizing: border-box;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .popup-overlay_pdf .popup_pdf-content {
        background: #fff;
        padding: clamp(14px, 3vw, 22px);
        border-radius: 10px;
        text-align: center;
        width: min(400px, 100%);
        max-width: 100%;
        margin: 10vh auto 2rem;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.3);
        box-sizing: border-box;
    }

    #pdfButtons button {
        margin: 5px;
    }

    .swal2-icon.swal2-info.swal2-icon-show {
        width: 50px;
        height: 50px;
        margin-top: 10px;
    }

    button.swal2-confirm {
        margin-right: 20px;
    }

    .info-row {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: baseline;
        gap: 4px 12px;
        padding: 6px 0;
        font-size: 14px;
    }

    .info-row .label {
        font-weight: 600;
        color: #333;
        flex: 1 1 8rem;
        min-width: 0;
    }

    .info-row .value {
        text-align: right;
        color: #555;
        flex: 1 1 10rem;
        min-width: 0;
        margin-left: 0;
        word-break: break-word;
    }

    /* --- Site footer (.footer-bottom): phones / notches --- */
    .footer-bottom.tnelb-footer {
        container-type: inline-size;
        container-name: tnelbFoot;
    }

    .footer-bottom.tnelb-footer .auto-container {
        padding-inline: max(12px, env(safe-area-inset-left)) max(12px, env(safe-area-inset-right));
    }

    /* responsive.css hides all br below 767px — restore line breaks in legal strip */
    .footer-bottom.tnelb-footer .middleContent br {
        display: block !important;
    }

    .footer-bottom.tnelb-footer .wrapper-box {
        flex-direction: column;
        align-items: stretch;
        gap: 0.5rem 0;
        padding-inline: clamp(6px, 2cqi, 14px);
        padding-block: clamp(10px, 2cqi, 14px);
    }

    .footer-bottom.tnelb-footer .wrapper-box .footer-links-inner {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 0.35rem 0.65rem;
        row-gap: 0.45rem;
        font-size: clamp(0.8rem, 0.72rem + 0.65cqi, 0.95rem);
        line-height: 1.35;
        text-align: center;
    }

    .footer-bottom.tnelb-footer .wrapper-box .footer-links-inner a {
        word-break: break-word;
        padding: 0.35rem 0.25rem;
        display: inline-flex;
        align-items: center;
    }

    .footer-bottom.tnelb-footer .middleContent {
        font-size: clamp(0.72rem, 0.62rem + 0.85cqi, 0.875rem);
        line-height: 1.55;
        padding-inline: 0 !important;
        overflow-wrap: break-word;
        hyphens: auto;
    }

    .footer-bottom.tnelb-footer .middleContent .external_link {
        display: inline;
        word-break: break-word;
    }

    .footer-bottom.tnelb-footer .copyright .text {
        font-size: clamp(0.75rem, 0.68rem + 0.5cqi, 0.875rem);
    }

    .footer-bottom.tnelb-footer .footer-meta-stats {
        color: rgb(204, 204, 204);
        font-size: clamp(0.72rem, 0.65rem + 0.55cqi, 0.82rem);
        line-height: 1.55;
        margin-top: 0.5rem;
        padding-bottom: 0.25rem;
    }

    .footer-bottom.tnelb-footer .footer-meta-stats span {
        display: block;
        margin: 0.15rem 0;
    }

    .swal2-popup {
        max-width: 95vw !important;
        overflow-x: hidden !important;
    }

    .local-file-preview {
        margin-top: 2px;
        margin-bottom: 3px;
        margin-left: 2px;
        font-size: 0.78rem;
        line-height: 1.15;
        display: flex;
        align-items: center;
        gap: 4px;
        width: fit-content;
    }

    .local-file-preview .fa-file-pdf-o {
        font-size: 0.72rem;
        color: #d61f26 !important;
    }

    .local-file-preview .preview-link {
        color: #1f4f8a;
        text-decoration: none;
        font-weight: 700;
        letter-spacing: 0;
    }

    .local-file-preview .preview-link:hover,
    .local-file-preview .preview-link:focus {
        color: #163b6a;
        text-decoration: underline;
    }
</style>
</head>
<script>
    const BASE_URL = "{{ UrlHelper::baseFileUrl() }}";
    
</script>

<body class="theme-color-two">

    <!-- Modal -->
<!-- Declaration Modal -->
<div class="modal fade" id="declarationModal" tabindex="-1" aria-labelledby="declarationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content" style="font-family: Georgia, 'Times New Roman', serif; font-size: 16px; line-height: 1.6;">
        <div class="modal-header text-white">
          <h4 class="modal-title" id="declarationModalLabel">📋 Instructions & Declaration</h4>
          <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close">X</button>
        </div>
  
        <div class="modal-body" style="padding: 30px; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.8; color: #333;">
            <p style="font-size: 17px; font-weight: 600; margin-bottom: 20px;">
              📋 Please read and confirm the following carefully:
            </p>
          
            <ol class="instruct" style="margin-left: 20px; padding-left: 10px;">
              <li>
                Fees for the issue of <span style=" font-weight: 600;">‘EA’ Contractor licence</span> from 
                <span>01.01.2024</span> onwards is 
                <span id="ea_fees" style="color: #dc3545; font-weight: 600;">Rs.30,000/-</span>.
                <br>
                <ol type="i" style="margin-left: 20px; margin-top: 5px;">
                  <li>
                    <span style=" font-weight: 600;">Mode of Payment:</span> Fees should be sent in favour of the <strong>Secretary, Electrical Licensing Board, Chennai</strong> by Bank Demand Draft obtained from any <span style="">Scheduled Bank</span> or <span style="">Co-operative Bank</span> payable at Chennai. 
                    <em style="color: #6c757d;">Remittance of fees by any other method will not be accepted.</em>
                  </li>
                </ol>
              </li>
          
              <li>
                The <span style=" font-weight: 600;">Proprietor</span> or the <span style=" font-weight: 600;">Managing Partner/Director</span> must be at least 
                <span style="color: #198754; font-weight: 600;">25 years old</span> and should have passed a minimum educational qualification of 
                <span style="color: #198754; font-weight: 600;">VIII Standard</span>.
              </li>
          
              <li>
                <span style=" font-weight: 600;">Establishment:</span> The applicant shall employ the following minimum staff on a full-time basis solely for the purpose of contract works:
                <div style="margin-left: 20px;">
                  One <span style=" font-weight: 600;">Supervisor</span> holding <span style="">Supervisor Competency Certificate</span> granted by the Board with a minimum Technical Educational qualification of a <span style="color: #198754; font-weight: 600;">Diploma in Electrical Engineering</span>…
                </div>
              </li>
              <li>
                <span style=" font-weight: 600;">Instruments:</span> The applicant must possess the following instruments:
                <ul style="margin-left: 20px; list-style-type: disc;">
                  <li>Earth Resistance Tester</li>
                  <li>500 Volts Insulation Tester</li>
                  <li>1000 Volts Insulation Tester</li>
                  <li>Phase Sequence Indicator</li>
                  <li>Tong Type Ammeter</li>
                  <li>Live Line Tester</li>
                  <li>Portable Voltmeter (Hand Operated)</li>
                </ul>
              </li>
              <li>
                <span style=" font-weight: 600;">Financial Status:</span> The applicant shall produce a <span style=" font-weight: 600;">Bank Solvency Certificate</span> for 
                <span style="color: #dc3545; font-weight: 600;">Rs.50,000/-</span> in <strong>Form ‘G’</strong>…
              </li>
            </ol>
          
            <div class="form-check mt-4">
              <input type="checkbox" class="form-check-input" id="declaration-agree">
              <label for="declaration-agree" class="form-check-label" style="font-weight: 600;">
                I have read and agree to the above terms.
              </label>
              <div class="text-danger mt-2 d-none" id="declaration-error">
                You must agree to proceed.
              </div>
            </div>
          </div>
  
        <div class="modal-footer text-center" style="padding: 20px;justify-content: center;">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="ea_declarationProceedBtn">Proceed</button>
        </div>
      </div>
    </div>
  </div>

<!-- Declaration Modal for competency certificate-->


  <div class="modal fade" id="competencyInstructionsModal" tabindex="-1" aria-labelledby="competencyInstructionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background-color: white;">
          <h5 class="modal-title" id="competencyInstructionsModalLabel">📋 Instructions & Declaration</h5>
          <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close">X</button>
        </div>
        <div class="modal-body" style="padding: 30px; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.8; color: #333;">
          <div class="show-list-numbers">
            1) (i) Fees Issue for <span id="certificate_name"></span> from <span id="fees_starts_from"></span> onwards is <span id="form_fees" style="color:#1f6920; font-weight:600;"></span>
            
          </div>
          <div id="instructionContent" class="show-list-numbers"></div>
            
          <div class="form-check mt-4">
            <input type="checkbox" class="form-check-input" id="declaration-agree-renew">
            <label for="declaration-agree-renew" class="form-check-label" style="font-weight: 600;">
              I have read and agree to the above instructions.
            </label>
            <div class="text-danger mt-2 d-none" id="declaration-error-renew">
              Please agree the above instructions.
            </div>
          </div>
        </div>
  
        <div class="modal-footer" style="justify-content: center;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="proceedPayment">Proceed</button>
        </div>
      </div>
    </div>
  </div>


<!-- -----contractor license ------------------- -->
  <div class="modal fade" id="contractorInstructionsModal" tabindex="-1" aria-labelledby="contractorInstructionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background-color: white;">
          <h5 class="modal-title" id="contractorInstructionsModalLabel">📋 Instructions & Declaration</h5>
          <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close">X</button>
        </div>
        <div class="modal-body" style="padding: 30px; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.8; color: #333;">
          
         <ol class="instruct" style="color:#000!important;"></ol>

  
          <div class="form-check mt-4">
            <input type="checkbox" class="form-check-input" id="declaration-agree-renew-contractor">
            <label for="declaration-agree-renew-contractor" class="form-check-label" style="font-weight: 600;">
              I have read and agree to the above instructions.
            </label>
            <div class="text-danger mt-2 d-none" id="declaration-error-renew-contractor">
              Please agree the above instructions.
            </div>
          </div>
        </div>
  
        <div class="modal-footer" style="justify-content: center;">
         
          <button type="button" class="btn btn-primary" id="proceedPayment">Proceed</button>
           <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
        </div>
      </div>
    </div>
  </div>
  <!-- --------------------------------------------- -->

  <div class="modal fade" id="competencyInstructionsModalP" tabindex="-1" aria-labelledby="competencyInstructionsModalLabelP" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header" style="background-color: white;">
          <h5 class="modal-title" id="competencyInstructionsModalLabelP">📋 Instructions & Declaration</h5>
          <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close">X</button>
        </div>
        <div class="modal-body" style="padding: 30px; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 15px; line-height: 1.8; color: #333;">
          <div class="show-list-numbers">
            1) (i) Fees Issue for <span id="p_certificate_name"></span> from <span id="p_fees_starts_from"></span> onwards is <span id="p_form_fees" style="color:#1f6920; font-weight:600;"></span>
          </div>
          <div id="instructionContentP" class="show-list-numbers"></div>

          <div class="form-check mt-4">
            <input type="checkbox" class="form-check-input" id="declaration-agree-renew-p">
            <label for="declaration-agree-renew-p" class="form-check-label" style="font-weight: 600;">
              I have read and agree to the above instructions.
            </label>
            <div class="text-danger mt-2 d-none" id="declaration-error-renew-p">
              Please agree the above instructions.
            </div>
          </div>
        </div>
        <div class="modal-footer" style="justify-content: center;">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="proceedtoPayment">Proceed</button>
        </div>
      </div>
    </div>
  </div>

  <!-- --------------------------------------------- -->
<!-- payment success modal for contractor License -->
<div class="modal fade" id="paymentSuccessModalcontractor" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-3">

            <div class="modal-header border-0">
                <h4 class="text-success w-100 text-center m-0">
                    Payment Successful !
                </h4>
            </div>

            <div class="modal-body">
                <div class="row">

                    <!-- LEFT INFO PANEL -->
                    <div class="col-md-6 stylish-divider">
                        <div class="info-grid">
                            <div class="label">Application ID:</div>
                            <div class="value" id="ps_applicationId"></div>

                            <div class="label">Applicant Name <br>[Contractor Licence]:</div>
                            <div class="value" id="ps_applicantName"></div>

                            <div class="label">Type of Application:</div>
                            <div class="value" id="ps_licenceName"></div>

                            <div class="label">Transaction ID:</div>
                            <div class="value" id="ps_transactionId"></div>

                            <div class="label">Transaction Date:</div>
                            <div class="value" id="ps_transactionDate"></div>

                            <div class="label">Amount Paid:</div>
                            <div><span>Rs.</span><span class="value" id="ps_amount"></span></div>
                        </div>
                    </div>

                    <!-- RIGHT DOWNLOAD PANEL -->
                    <div class="col-md-6 text-center">
                        <p class="fw-bold">Download Your Payment Receipt:</p>
                        <button class="btn btn-info btn-sm mb-2" onclick="paymentreceiptformA()">
                            <i class="fa fa-file-pdf-o text-danger"></i>
                            Download Receipt
                        </button>

                        <p class="fw-bold mt-3">Download Your Application PDF:</p>

                        <button class="btn btn-primary btn-sm me-2" onclick="downloadPDFformApdf()">
                            <i class="fa fa-file-pdf-o text-danger"></i> English
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 justify-content-center">
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
            </div>

        </div>
    </div>
</div>

  <!-- --------------------------------------------- -->





<!-- Payment Success Modal -->
<div class="modal fade" id="paymentSuccessModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content p-3">

          <div class="modal-header border-0">
              <h4 class="text-success w-100 text-center m-0">
                  Payment Successful!
              </h4>
          </div>

          <div class="modal-body">
              <div class="row">

                  <!-- LEFT INFO PANEL -->
                  <div class="col-md-6 stylish-divider">
                      <div class="info-grid">
                          <div class="label">Application ID:</div>
                          <div class="value" id="ps_applicationId_competency">11</div>

                          <div class="label">Applicant Name:</div>
                          <div class="value" id="ps_applicantName_competency"></div>

                          <div class="label">Type of Application:</div>
                          <div class="value" id="ps_licenceName_competency"></div>

                          <div class="label">Transaction ID:</div>
                          <div class="value" id="ps_transactionId_competency"></div>

                          <div class="label">Transaction Date:</div>
                          <div class="value" id="ps_transactionDate_competency"></div>

                          <div class="label">Amount Paid:</div>
                          <div><span>Rs.</span>
                              <span class="value" id="ps_amount_competency"></span>
                          </div>
                      </div>
                  </div>

                  <!-- RIGHT DOWNLOAD PANEL -->
                  <div class="col-md-6 text-center">

                      <p class="fw-bold">Download Your Payment Receipt:</p>
                      <button class="btn btn-info btn-sm mb-2" onclick="paymentreceipt()">
                          <i class="fa fa-file-pdf-o text-danger"></i>
                          Download Receipt
                      </button>

                      <p class="fw-bold mt-3">Download Your Application PDF:</p>

                      <button class="btn btn-primary btn-sm me-2" onclick="downloadPDF('english')">
                          <i class="fa fa-file-pdf-o text-danger"></i> English
                      </button>

                      <button class="btn btn-success btn-sm" onclick="downloadPDF('tamil')">
                          <i class="fa fa-file-pdf-o text-danger"></i> Tamil
                      </button>

                  </div>
              </div>
          </div>

          <div class="modal-footer border-0 justify-content-center">
               <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to Dashboard</a>
          </div>

      </div>
  </div>
</div>

  <!-- --------------------------------------------- -->
    <div class="page-wrapper">
       {{-- <div class="loader-wrap">
            <div class="preloader"></div>
            <div class="layer layer-one"><span class="overlay"></span></div>
            <div class="layer layer-two"><span class="overlay"></span></div>
            <div class="layer layer-three"><span class="overlay"></span></div>
        </div> --}}
        <div class="content ">

            <!-- Main Header -->
            <header class="main-header header-style-two ">

                <!-- Header Top two -->
                <div class="header-top-two bg-gray">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-md-8">
                                <ul class="top-info text-center text-md-left">
                                    <li>
                                        <!-- <i class="fas fa-map-marker-alt"></i> -->
                                        <p class="info-text color-dark">Government of Tamil Nadu | Tamil Nadu Electrical
                                            Licencing Board</p>
                                    </li>
                                </ul>
                            </div>
                            <!--/ Top info end -->

                            <div class="col-lg-4 col-md-4 top-social text-center text-md-right">
                                <ul class="list-unstyled">
                                    <li><a rel="noopener" href="#mainsection" title="Skip to main content"> <i
                                                class="fa fa-share-square"></i></a></li>
                                    <li><span class="toolbarline"></span></li>
                                    <li class="topbar-search-li"><a href="#" class="searchBox">
                                            <input class="searchInput" type="text" name="" placeholder="Search"
                                                id="txt_search" required="">
                                            <button class="searchButton" onclick="google_search();" href="#">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </a>
                                    </li>
                                    <li><span class="toolbarline"></span></li>
                                    <li><a rel="noopener" href="sitemap.php"><i class="fa fa-sitemap"></i></a></li>
                                    <li><span class="toolbarline"></span></li>
                                    <li class="dropdown-submenu">
                                        <a href="#" class="subhover" tabindex="-1"> <i class=" fa fa-wheelchair"></i></a>

                                    </li>
                                    <li><span class="toolbarline"></span></li>
                                    <li><a rel="noopener" href="screenreader.php" title="Screen Reader Access"> <i
                                                class="fa fa-volume-up"></i></a></li>

                                    <li><span class="toolbarline"></span></li>
                                    <li class="dropdown-submenu">
                                        <a href="#" class="subhover" tabindex="-1"> <i class="fa fa-globe"></i></a>

                                    </li>
                                    <li><span class="toolbarline"></span></li>
                                    <li><a rel="noopener" href="#" title="Google translator"><i
                                                class="fa fa-language"></i></a></li>
                                </ul>
                            </div>
                            <!--/ Top social end -->
                        </div>
                    </div>
                </div>

                <div class="logo-fun">
                    <div class="container header-container">
                        <div class="row logo-fun-row">
                            <div class="col-12 col-lg-8 logo-fun-brand">
                                <div class="logo">
                                    <a href="{{ url('/') }}">
                                        <img src="{{ asset('assets/images/logo/logo_tr.png') }}" class="site_logo" alt="Logo of Government of Tamil Nadu" />
                                        <div class="logo-text">
                                            <h3>Tamil Nadu Electrical Licencing Board</h3>
                                            <h5>Goverment of Tamil Nadu</h5>
                                        </div>
                                    </a>
                                </div>
                                {{-- <div class="flex-shrink-0 mr-3 mr-xl-8 mr-xlwd-16 d-none d-md-block">
                                    <a href="/logout">
                                        <img src="{{ asset('assets/images/logo/logo.png') }}" class="img-fluid" alt="tnelb" />
                                    </a>
                                </div> --}}

                                {{-- <div class="flex-shrink-0 mr-3 mr-xl-8 mr-xlwd-16 d-block d-lg-none">
                                    <a href="/logout">
                                        <img src="{{ asset('assets/images/logo/logo_mobile.png') }}" class="img-fluid" alt="tnelb" />
                                    </a>
                                </div> --}}
                            </div>
                            <div class="col-12 col-lg-4 text-center logo-fun-actions">
                                <ul class="top-info-box">
                                    @if(Auth::check())
                                    <li class="header-get-a-quote">
                                        <div class="profile">
                                            <div class="user">
                                               <a class="btn btn-success text-white text-capitalize">
                                                    <i class="fa fa-user-circle-o"></i>&nbsp; {{ Auth::user()->salutation.'.'.Auth::user()->first_name.' '.Auth::user()->last_name }}
                                                </a>
                                            </div>
                                        </div>
                                        <div class="menu">
                                            <ul>
                                                <li>
                                                    <a href="{{ route('dashboard') }}">
                                                        <i class="fa fa-dashboard"></i>&nbsp;Dashboard
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="{{ route('logout') }}">
                                                        <i class="fa fa-sign-out"></i>&nbsp;Log Out
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                    @else
                                    <li class="header-get-a-quote">
                                        <a class="btn btn-primary" href="{{ route('login') }}">Applicant Sign In/ Sign Up</a>
                                    </li>
                                    @endif
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>



                <!-- Header Upper -->
                <div class="header-upper">
                    <div class="auto-container">
                        <div class="inner-container">

                            <!--Nav Box-->
                            <div class="nav-outer">
                                <!--Mobile Navigation Toggler-->
                                <div class="mobile-nav-toggler"><img src="{{ asset('assets/images/icons/icon-bar-2.png') }}" alt=""></div>
                                <!-- Main Menu -->
                                <nav class="main-menu navbar-expand-md navbar-light">
                                    <div class="collapse navbar-collapse show clearfix" id="navbarSupportedContent">
                                        <ul class="navigation">


                                            <!-- Hidden Logout Form -->
                                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                @csrf
                                            </form>
                                            @foreach($menu as $main)
                                            @php
                                            $page = $main->menuPage;
                                            $link = '#';
                                            $target = '';

                                            if ($page) {
                                            if ($page->page_type == 'url') {
                                            $link = $page->external_url ?? '#';
                                            $target = ' target="_blank"';
                                            } elseif ($page->page_type == 'pdf') {
                                            $link = asset($page->pdf_en ?? '');
                                            $target = ' target="_blank"';
                                            } else {
                                            $link = $page->page_url ?? '#';
                                            $target = ''; // open in same tab
                                            }
                                            }

                                            $childMenus = $submenu->where('parent_code', $main->id);
                                            @endphp

                                            <li class="dropdown">
                                                   <a href="{{ $page && $page->page_type == 'url' ? $link : url($link) }}" {!! $target !!}>
														{{ $main->menu_name_en }}
													</a>

                                                @if($childMenus->isNotEmpty())
                                                <ul>
                                                   @foreach($childMenus as $child)
														@php
															$childPage   = $child->submenuPage;
															$childLink   = '#';
															$childTarget = '';

															if ($childPage) {
																if ($childPage->page_type == 'url') {
																	// External URL
																	$childLink   = $childPage->external_url ?? '#';
																	$childTarget = ' target="_blank"';

																} elseif ($childPage->page_type == 'pdf') {
																	// PDF file stored locally
																	$childLink   = url($childPage->pdf_en ?? '#'); 
																	$childTarget = ' target="_blank"';

																} else {
																	// Internal page → prepend APP_URL automatically
																	$childLink   = url($childPage->page_url ?? '#'); 
																	$childTarget = '';
																}
															}
														@endphp

														<li>
															<a href="{{ $childLink }}" {!! $childTarget !!}>{{ $child->menu_name_en }}</a>
														</li>
													@endforeach

                                                </ul>
                                                @endif
                                            </li>
                                            @endforeach

                                            @if(Auth::check())
                                            <li class="dropdown">
                                                <a href="#">Forms</a>
                                                <ul>
                                                    <li><a href="{{ route('apply-form-wh')}}">Form WH</a></li>
                                                    <li><a href="{{ route('apply-form-w')}}">Form W</a></li>
                                                    <li><a href="{{ route('apply-form-s')}}">Form S</a></li>
                                                    <li><a href="{{ route('apply-form-s')}}">Form P</a></li>
                                                    <!-- <li><a href="#">Form H TO B</a></li> -->
                                                     <li><a href="{{ route('apply-form-a')}}">Form A</a></li>
                                                    <li><a href="{{ route('apply-form-b')}}">Form EB</a></li>
                                                    <li><a href="{{ route('apply-form-sb')}}">Form SB</a></li>
                                                    <li><a href="{{ route('apply-form-sa')}}">Form SA</a></li>
                                                    <!-- <li><a href="#">Form SA</a></li> -->
                                                    <li><a href="#">Fees Structure</a></li>
                                                    <li><a href="#">Renewal Particulars (English)</a></li>
                                                    <li><a href="#">Renewal Particulars (Tamil)</a></li>
                                                </ul>
                                            </li>
                                            @else
                                            <li class="dropdown">
                                                <a href="#">Forms</a>
                                                <ul>

                                                    <li><a href="login">Form WH</a></li>
                                                    <li><a href="login">Form W</a></li>
                                                    <li><a href="login">Form S</a></li>
                                                    <li><a href="login">Form P</a></li>
                                                    <li><a href="login">Form H TO B</a></li>
                                                    <li><a href="login">Form EB</a></li>
                                                    <li><a href="login">Form SB</a></li>
                                                    <li><a href="login">Form A</a></li>
                                                    <li><a href="login">Form SA</a></li>
                                                    <li><a href="login">Fees Structure</a></li>
                                                    <li><a href="login">Renewal Particulars (English)</a></li>
                                                    <li><a href="login">Renewal Particulars (Tamil)</a></li>
                                                </ul>
                                            </li>
                                            @endif

                                        </ul>
                                    </div>
                                </nav>
                            </div>
                            <div class="navbar-right">
                                <div class="search-form-two">
                                    <form>
                                        <input type="search" placeholder="Search ...">
                                        <button type="submit"><i class="icon-search"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--End Header Upper-->


                <!-- Sticky Header  -->
                <div class="sticky-header">
                    <div class="header-upper">
                        <div class="auto-container">
                            <div class="inner-container">

                                <div class="nav-outer">
                                    <!--Mobile Navigation Toggler-->
                                    <div class="mobile-nav-toggler">
                                        <img src="{{ asset('assets/images/icons/icon-bar-2.png') }}" alt="">
                                    </div>
                                    <!-- Main Menu -->
                                    <nav class="main-menu navbar-expand-md navbar-light">
                                    </nav>
                                </div>
                                <div class="navbar-right">
                                    <div class="search-form-two">
                                        <form>
                                            <input type="search" placeholder="Search ...">
                                            <button type="submit"><i class="icon-search"></i></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- End Sticky Menu -->

                <!-- Mobile Menu  -->
                <div class="mobile-menu">
                    <div class="menu-backdrop"></div>
                    <div class="close-btn"><span class="icon far fa-times-circle"></span></div>

                    <nav class="menu-box">
                        <div class="menu-outer">
                            <!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header-->
                        </div>
                        <!--Social Links-->
                        <!-- <div class="social-links">
                        <ul class="clearfix">
                            <li><a href="#"><span class="fab fa-twitter"></span></a></li>
                            <li><a href="#"><span class="fab fa-facebook-square"></span></a></li>
                            <li><a href="#"><span class="fab fa-pinterest-p"></span></a></li>
                            <li><a href="#"><span class="fab fa-instagram"></span></a></li>
                            <li><a href="#"><span class="fab fa-youtube"></span></a></li>
                        </ul>
                    </div> -->
                    </nav>
                </div><!-- End Mobile Menu -->

                <div class="nav-overlay">
                    <div class="cursor"></div>
                    <div class="cursor-follower"></div>
                </div>
            </header>
            <!-- End Main Header -->

            <!--Search Popup-->
            <div id="search-popup" class="search-popup">
                <div class="close-search theme-btn"><span class="far fa-times-circle"></span></div>
                <div class="popup-inner">
                    <div class="overlay-layer"></div>
                    <div class="search-form">
                        <form method="post" action="#">
                            <div class="form-group">
                                <fieldset>
                                    <input type="search" class="form-control" name="search-input" value=""
                                        placeholder="Search Here" required>
                                    <input type="submit" value="Search Now!" class="theme-btn">
                                </fieldset>
                            </div>
                        </form>
                    </div>
                </div>
            </div>