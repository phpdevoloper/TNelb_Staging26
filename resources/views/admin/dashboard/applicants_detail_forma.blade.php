@include('admin.include.top')
@include('admin.include.header')
@include('admin.include.navbar')
<style>
    .tab-content {
        padding: 0px 20px;
    }
    .table td{
        text-align:left;
    }
    table th{
        font-weight:800!important;
    }
    .officers_section{
        background: #4361ee;
        padding:5px;
    }

      .officers_section h6{
        color:#fff;
        font-size:18px;
        font-weight:600;
    }
    .text-info{
        color:#3b3f5c!important;
        font-size:15px;
    }

    .file_view{
        color: #0082ff;
    }

     .file_view i{
        color: red;
    }

    .text-return{
        color: #6f42c1;
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
    .dash-tl-footer {
        display: none;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 0.75rem 1.1rem;
        background: #fff;
        border-top: 1px solid #d7e2f0;
        flex-shrink: 0;
    }
    .dash-tl-overlay.is-approve .dash-tl-footer {
        display: flex;
    }
    body.dash-tl-open {
        overflow: hidden;
    }

    .staff-status-switch {
    width: 45px !important;
    height: 22px !important;
    cursor: pointer;
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
}

.staff-status-switch:checked {
    background-color: #198754 !important;
    border-color: #198754 !important;
}

.staff-status-switch {
    width: 45px !important;
    height: 22px !important;
    cursor: pointer;
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
}

.staff-status-switch:checked {
    background-color: #198754 !important;
    border-color: #198754 !important;
}


.approval-success-popup {
    border-radius: 14px !important;
    overflow: hidden !important;
    box-shadow: 0 20px 60px rgba(0,0,0,.20) !important;
}


/* -------------------------
   HEADER
------------------------- */

.approval-header {
    display: flex;
    align-items: center;
    gap: 15px;

    padding: 22px 25px;

    background: linear-gradient(
        135deg,
        #198754,
        #157347
    );

    color: #fff;

    text-align: left;
}


.success-icon {
    width: 52px;
    height: 52px;

    border-radius: 50%;

    background: rgba(255,255,255,.20);

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 30px;
    font-weight: bold;
}


.approval-title {
    font-size: 21px;
    font-weight: 700;
}


.approval-subtitle {
    font-size: 13px;
    opacity: .9;

    margin-top: 4px;
}


/* -------------------------
   MAIN DETAILS
------------------------- */

.details-section {
    padding: 22px 25px 5px;

    text-align: left;
}


.section-title {
    font-size: 16px;
    font-weight: 700;

    color: #343a40;

    margin-bottom: 12px;

    display: flex;
    align-items: center;
    gap: 8px;
}


.section-icon {
    font-size: 18px;
}


.details-card {
    border: 1px solid #dee2e6;

    border-radius: 10px;

    overflow: hidden;

    background: #f8f9fa;
}


.detail-row {
    display: flex;

    justify-content: space-between;
    align-items: center;

    padding: 14px 17px;

    border-bottom: 1px solid #dee2e6;
}


.detail-row:last-child {
    border-bottom: 0;
}


.detail-label {
    color: #6c757d;

    font-size: 13px;

    font-weight: 600;
}


.detail-value {
    color: #212529;

    font-size: 15px;

    font-weight: 600;

    text-align: right;
}


.licence-number {
    color: #198754;

    font-size: 17px;

    font-weight: 700;
}


.expiry-date {
    color: #dc3545;

    font-size: 16px;

    font-weight: 700;
}


/* -------------------------
   EXPIRY MESSAGE
------------------------- */

.expiry-message {
    margin-top: 12px;

    padding: 12px 15px;

    background: #f1f8f4;

    border: 1px solid #b7dfc8;

    border-radius: 8px;

    color: #146c43;

    font-size: 13px;

    display: flex;

    align-items: center;

    gap: 9px;
}


.expiry-icon {
    width: 24px;
    height: 24px;

    border-radius: 50%;

    background: #198754;

    color: #fff;

    display: flex;

    align-items: center;
    justify-content: center;

    font-weight: bold;
}

.approval-success-popup {

    border-radius: 14px !important;

    overflow: hidden !important;

    box-shadow:
        0 20px 60px rgba(0, 0, 0, 0.20) !important;

}


/* =========================================
   HEADER
========================================= */

.approval-header {

    display: flex;

    align-items: center;

    gap: 15px;

    padding: 22px 26px;

    background:
        linear-gradient(
            135deg,
            #198754,
            #157347
        );

    color: #ffffff;

    text-align: left;

}


.success-circle {

    width: 52px;

    height: 52px;

    min-width: 52px;

    border-radius: 50%;

    background:
        rgba(255, 255, 255, 0.20);

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 30px;

    font-weight: 700;

}


.approval-title {

    font-size: 21px;

    font-weight: 700;

}


.approval-subtitle {

    font-size: 13px;

    opacity: 0.90;

    margin-top: 4px;

}


/* =========================================
   LICENCE DETAILS
========================================= */

.licence-details {

    padding: 22px 26px 8px;

    text-align: left;

}


.section-heading {

    display: flex;

    align-items: center;

    gap: 8px;

    font-size: 16px;

    font-weight: 700;

    color: #343a40;

    margin-bottom: 12px;

}


.heading-icon {

    font-size: 18px;

}


.licence-card {

    border: 1px solid #dee2e6;

    border-radius: 10px;

    overflow: hidden;

    background: #f8f9fa;

}


.detail-row {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 14px 18px;

    border-bottom: 1px solid #dee2e6;

}


.detail-row:last-child {

    border-bottom: none;

}


.detail-label {

    color: #6c757d;

    font-size: 13px;

    font-weight: 600;

}


.detail-value {

    color: #212529;

    font-size: 15px;

    font-weight: 600;

    text-align: right;

}


.licence-number {

    color: #198754;

    font-size: 17px;

    font-weight: 700;

}


.expiry-date {

    color: #dc3545;

    font-size: 16px;

    font-weight: 700;

}


/* =========================================
   EXPIRY MESSAGE
========================================= */

.expiry-message {

    display: flex;

    align-items: center;

    gap: 9px;

    margin-top: 12px;

    padding: 12px 15px;

    background: #f1f8f4;

    border: 1px solid #b7dfc8;

    border-radius: 8px;

    color: #146c43;

    font-size: 13px;

}


.expiry-check {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 24px;

    height: 24px;

    min-width: 24px;

    border-radius: 50%;

    background: #198754;

    color: #ffffff;

    font-weight: 700;

}


/* =========================================
   DIGITISATION CARD
========================================= */

.digitisation-card {

    margin: 18px 26px 5px;

    padding: 18px;

    border: 1px solid #cfe2ff;

    border-radius: 10px;

    background: #f4f8ff;

    text-align: left;

}


.digitisation-title {

    display: flex;

    align-items: center;

    gap: 8px;

    font-size: 16px;

    font-weight: 700;

    color: #0d6efd;

    margin-bottom: 14px;

}


.digitisation-icon {

    font-size: 19px;

}


/* =========================================
   MAPPING
========================================= */

.mapping-wrapper {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 12px;

}


.mapping-item {

    flex: 1;

    padding: 15px 12px;

    border-radius: 8px;

    text-align: center;

}


.old-cl {

    background: #fff3cd;

    border: 1px solid #ffe69c;

}


.new-cl {

    background: #d1e7dd;

    border: 1px solid #a3cfbb;

}


.mapping-label {

    font-size: 10px;

    font-weight: 700;

    letter-spacing: .5px;

    color: #6c757d;

    margin-bottom: 7px;

}


.mapping-value {

    font-size: 16px;

    font-weight: 700;

    word-break: break-word;

}


.old-cl .mapping-value {

    color: #856404;

}


.new-cl .mapping-value {

    color: #146c43;

}


.mapping-arrow {

    font-size: 28px;

    font-weight: 700;

    color: #0d6efd;

}


/* =========================================
   NO MAPPING
========================================= */

.digitisation-not-found {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 8px;

    margin: 18px 26px 5px;

    padding: 13px;

    border: 1px solid #dee2e6;

    border-radius: 8px;

    background: #f8f9fa;

    color: #6c757d;

    font-size: 13px;

}


.info-icon {

    display: flex;

    align-items: center;

    justify-content: center;

    width: 22px;

    height: 22px;

    border-radius: 50%;

    background: #6c757d;

    color: #ffffff;

    font-weight: 700;

}


/* =========================================
   OK BUTTON
========================================= */

.approval-ok-button {

    min-width: 110px !important;

    padding: 10px 28px !important;

    border-radius: 7px !important;

    font-weight: 600 !important;

}


/* =========================================
   MOBILE
========================================= */

@media (max-width: 600px) {

    .mapping-wrapper {

        flex-direction: column;

    }

    .mapping-item {

        width: 100%;

    }

    .mapping-arrow {

        transform: rotate(90deg);

    }

    .detail-row {

        flex-direction: column;

        align-items: flex-start;

        gap: 5px;

    }

    .detail-value {

        text-align: left;

    }

}
</style>
<div id="content" class="main-content">
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
                        <div class="d-flex breadcrumb-content">
                            <div class="page-header">
                                <div class="page-title"></div>
                                <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#"></a></li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                    </header>
                </div>
            </div>

            <div class="row layout-top-spacing">
                <div class="col-lg-12 layout-spacing">
                    <div class="statbox widget ">
                        <div class="widget-header applicant_details {{ trim($applicant->appl_type) == 'D' ? 'digitization-header' : '' }}"">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <h4>Applicant Id : <span> {{ $applicant->application_id }}</span> Applicant Name : <span style="color:#098501;">{{ $applicant->applicant_name }}</span> Applied For : <span style="color:#098501;"> {{ $applicant->form_name }} | License {{ $applicant->license_name }}</span> </h4>
                                </div>

                                @if(trim($applicant->appl_type) == 'D')

                                    @if(!empty($cl_digitization))
                                        <div class="col-xl-6 col-md-6 col-sm-12 col-12 text-center">
                                            <h3 class="badge badge-primary ">Digitisation Old Certificate Details </h3>
                                            <div class="table-responsive digi_data">
                                                <table class="table table-bordered table-sm">
                                                    <tbody>
                                                        <tr>
                                                            <th width="30%">Certificate Number</th>
                                                            <td>{{ $cl_digitization->clnumber }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Date of First Issue</th>
                                                            <td>{{ (\Carbon\Carbon::parse($cl_digitization->fissue))->format('d-m-Y') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Validity From</th>
                                                            <td>{{ \Carbon\Carbon::parse($cl_digitization->from_date)->format('d-m-Y') }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Validity To</th>
                                                            <td>{{ \Carbon\Carbon::parse($cl_digitization->to_date)->format('d-m-Y') }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Certificate Document</th>
                                                            <td>
                                                                <a href="{{ asset( $cl_digitization->cl_doc) }}"
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
                <div id="tabsSimple" class="col-xl-12 col-12 layout-spacing">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    {{-- <h3 class="application_id_css">Application Id :<span style="color:#098501;"> {{ $applicant->application_id }}</span> </h3> --}}
                                    <h4>View Applicant's Details</h4>
                                </div>
                            </div>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="simple-tab">
                                <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home-tab-pane" type="button" role="tab" aria-controls="home-tab-pane" aria-selected="true">Applicant's Details</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="false">Staff and Bank Details</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="other-tab" data-bs-toggle="tab" data-bs-target="#other-tab-panel" type="button" role="tab" aria-controls="other-tab-panel" aria-selected="false">Other Details and Address Proof</button>
                                    </li>

                                     <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="equipment-tab" data-bs-toggle="tab" data-bs-target="#equipment-tab-panel" type="button" role="tab" aria-controls="equipment-tab-panel" aria-selected="false">Equipment / Instruments List</button>
                                    </li>


                                    @if(trim($applicant->appl_type) !='D')

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="payment-tab" data-bs-toggle="tab"
                                            data-bs-target="#payment-tab-panel" type="button" role="tab"
                                            aria-controls="payment-tab-panel" aria-selected="false">
                                            Payment Details
                                        </button>
                                    </li>
                                    @endif

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="false">Checklist Details</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel" aria-labelledby="home-tab" tabindex="0">
                                        <div class="row mt-3 ">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-3">


                                                            <p class="text-info"><strong>1. Name of the Applicant:</strong></p>

                                                            <p class="text-info"><strong>2. Business address:</strong></p>

                                                        </div>
                                                        <div class="col-lg-8">


                                                            <p>{{ $applicant->applicant_name }}</p>

                                                            <p>{{ $applicant->business_address }}</p>


                                                        </div>

                                                    </div>


                                                </div>

                                            </div>





                                            <p class="mt-4 mb-2 fw-bold text-info">3. Ownership Type - Proprietor / Partners / Directors Details</p>
                                                    <div class="row">
                                                        <div class="col-lg-3">


                                                            <p class="text-info"><strong>Type of Ownership:</strong></p>



                                                        </div>
                                                        <div class="col-lg-3">

                                                           <p>
                                                                @if($applicant->application_ownershiptype == 'pr')
                                                                Proprietor
                                                                @elseif($applicant->application_ownershiptype == 'pt')
                                                                Partnership
                                                                @elseif($applicant->application_ownershiptype == 'pvt')
                                                                Private Limited (Pvt LTD)
                                                                 @elseif($applicant->application_ownershiptype == 'ltd')
                                                                Limited (LTD)
                                                                @else
                                                                -

                                                                @endif
                                                            </p>

                                                        </div>

                                                        <!-- ------------------------ownership doc--------- -->
                                                         @if($applicant->application_ownershiptype == 'pt' || $applicant->application_ownershiptype == 'pvt' || $applicant->application_ownershiptype == 'ltd')

                                                        <div class="col-lg-3">

                                                          <a href="{{asset($applicant->ownership_doc)}}" class="file_view fw-bold" target="_blank"><i class="fa fa-file-pdf-o"></i> View Ownership Document</a>

                                                        </div>
                                                        @endif

                                                    </div>
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Ownership <br>Type</th>
                                                            <th>Name </th>
                                                              <th>Father/s
                                                                Husband/s
                                                                Name</th>
                                                            <th>D.O.B, Age and Proof</th>
                                                            <th>Address </th>
                                                            <th>Qualifications and Proof</th>


                                                            <th>Present business of
                                                                the applicant</th>
                                                            <th>Competency
                                                                Certificate and
                                                                Validity </th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php

                                                            $sortedProprietors = collect($cl_ownership_table)
                                                                ->sortBy(function($item) {
                                                                    return match($item->ownership_type) {
                                                                        'pr' => 1,
                                                                        'pt' => 2,
                                                                        'dr' => 3,
                                                                        default => 4,
                                                                    };
                                                                });
                                                        @endphp
                                                        @forelse ($sortedProprietors as $proprietor)
                                                        <tr>
                                                            <td>
                                                                <!-- {{$proprietor->id}} -->
                                                                @if($proprietor->ownership_type == 'pr' )
                                                              Proprietor
                                                                @elseif($proprietor->ownership_type == 'pt')
                                                                Partner
                                                                @else
                                                                Director
                                                                @endif

                                                            </td>
                                                            <td>{{ $proprietor->proprietor_name }} </td>
                                                            <td> {{ $proprietor->fathers_name }}</td>
                                                            <td>{{ \Carbon\Carbon::parse($proprietor->dob)->format('d-m-Y')
                                                                 }}, {{ $proprietor->age }} <a href="{{asset($proprietor->age_proof)}}" class="file_view fw-bold" target="_blank"><i class="fa fa-file-pdf-o"></i> </a></td>
                                                            <td>{{ $proprietor->proprietor_address }} </td>
                                                             <td> {{ $proprietor->qualification }}, {{ $proprietor->qualification_text }} <a href="{{asset($proprietor->educational_proof)}}" class="file_view fw-bold" target="_blank"><i class="fa fa-file-pdf-o"></i> </a></td>

                                                            <td> {{ $proprietor->present_business }}</td>
                                                            <td>
                                                                @if(!empty($proprietor->competency_certificate_number))

                                                                    CC No :
                                                                    {{ $proprietor->competency_certificate_number }},<br>

                                                                    CC First Issue :
                                                                    {{ $proprietor->competency_certificate_first_issue
                                                                        ? \Carbon\Carbon::parse($proprietor->competency_certificate_first_issue)->format('d-m-Y')
                                                                        : ''
                                                                    }},<br>

                                                                    CC Validity From :
                                                                    {{ $proprietor->competency_certificate_validity_from
                                                                        ? \Carbon\Carbon::parse($proprietor->competency_certificate_validity_from)->format('d-m-Y')
                                                                        : ''
                                                                    }},<br>

                                                                    CC Validity To :
                                                                    {{ $proprietor->competency_certificate_validity_to
                                                                        ? \Carbon\Carbon::parse($proprietor->competency_certificate_validity_to)->format('d-m-Y')
                                                                        : ''
                                                                    }}

                                                                    <br><br>

                                                                    <button type="button"
                                                                        class="btn btn-primary verify-cert_ownership"

                                                                        data-id="{{ $proprietor->id }}"

                                                                        data-license="{{ $proprietor->competency_certificate_number }}"

                                                                        data-dateofissue="{{ $proprietor->competency_certificate_first_issue
                                                                            ? \Carbon\Carbon::parse($proprietor->competency_certificate_first_issue)->format('d-m-Y')
                                                                            : ''
                                                                        }}"

                                                                        data-validfrom="{{ $proprietor->competency_certificate_validity_from
                                                                            ? \Carbon\Carbon::parse($proprietor->competency_certificate_validity_from)->format('d-m-Y')
                                                                            : ''
                                                                        }}"

                                                                        data-validto="{{ $proprietor->competency_certificate_validity_to
                                                                            ? \Carbon\Carbon::parse($proprietor->competency_certificate_validity_to)->format('d-m-Y')
                                                                            : ''
                                                                        }}">

                                                                        Verify
                                                                    </button>

                                                                @endif

                                                                <div id="verify-result-{{ $proprietor->id }}"></div>
                                                            </td>


                                                        </tr>
                                                        @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center">No Proprietor Details details available.</td>
                                                        </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>



                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row mt-2">
                                                        <div class="col-lg-8">
                                                            <p class=" text-info"><strong>5. Whether any application for Contractor/s licence was made previously? If so, details thereof:</strong></p>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <p>{{ strtoupper($applicant->previous_contractor_license) }}
                                                                @if($applicant->previous_contractor_license === 'yes')
                                                                 - {{$applicant->previous_application_number}},
                                                                    {{ \Carbon\Carbon::parse($applicant->previous_application_validity)->format('d-m-Y') }}

                                                                                     <div class="row">



                                                           <div class="col-lg-12 col-12 d-flex align-items-center">
                                                            <div class="verify-result_EA "></div>  <!-- Result on left -->
                                                            <button class="btn btn-sm btn-primary verify-btn_EA"
                                                                data-license="{{ $applicant->previous_application_number }}"
                                                                data-date="{{ $applicant->previous_application_validity }}">
                                                                Verify
                                                            </button>
                                                        </div>



                                                             </div>
                                                                @endif
                                                                </p>





                                                        </div>
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
                                                                    value="{{ $applicant->license_name }}">

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
                                    <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
                                        <p class="mt-4 mb-2 fw-bold text-info">6A . QC Staff Details</p>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Staff Category </th>


                                                        <th>Competency Certificate Number
                                                        </th>
                                                        <th>Certificate First Issue , <br>Validity From<br> Validity To</th>

                                                        <th>Attachments
                                                        </th>
                                                        <th>History of Staff
                                                        </th>
                                                        <th>Verify</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($qcQscStaffs_details as $index => $staff)
                                                    <tr>

                                                        <td>{{ $staff->staff_category }}</td>
                                                        <td>{{ $staff->staff_cc_no }}</td>

                                                         <td>{{ \Carbon\Carbon::parse($staff->staff_cc_first_issue)->format('d-m-Y') }},<br> {{ \Carbon\Carbon::parse($staff->staff_cc_validity_from)->format('d-m-Y') }},<br>{{ \Carbon\Carbon::parse($staff->staff_cc_validity_to)->format('d-m-Y') }} </td>


                                                    <td>
                                                        <div class="file-link">

                                                                 <a href="#" target="_blank">
                                                                    Appointment Letter
                                                                </a>

                                                                <br>
                                                                <a href="#" target="_blank">
                                                                    Consent Letter
                                                                </a>

                                                        </div>
                                                    </td>
                                                   <td>
                                                    @if(!empty($staff->staff_cc_no))



                                                        <button type="button"
                                                            class="btn btn-primary verify-staff"



                                                            data-license="{{ $staff->staff_cc_no }}"

                                                            data-dateofissue="{{ $staff->staff_cc_first_issue
                                                                ? \Carbon\Carbon::parse($staff->staff_cc_first_issue)->format('d-m-Y')
                                                                : ''
                                                            }}"

                                                            data-validfrom="{{ $staff->staff_cc_validity_from
                                                                ? \Carbon\Carbon::parse($staff->staff_cc_validity_from)->format('d-m-Y')
                                                                : ''
                                                            }}"

                                                            data-validto="{{ $staff->staff_cc_validity_to
                                                                ? \Carbon\Carbon::parse($staff->staff_cc_validity_to)->format('d-m-Y')
                                                                : ''
                                                            }}">

                                                            View History
                                                        </button>

                                                    @endif


                                                </td>

                                            <td class="text-center">

                                                @if($staff->staff_category != 'OTHERS')

                                                    @php
                                                        $verifyFlag = isset($verifyList[$staff->staff_cc_no])
                                                            ? (int) $verifyList[$staff->staff_cc_no]
                                                            : 0;
                                                    @endphp

                                                    <div class="d-flex align-items-center justify-content-center gap-2">

                                                        <span
                                                            id="staffStatusText_{{ $staff->id }}"
                                                            class="badge {{ $verifyFlag == 1 ? 'bg-success' : 'bg-danger' }}">
                                                            {{ $verifyFlag == 1 ? 'Verified' : 'Not Verified' }}
                                                        </span>

                                                        <div class="form-check form-switch">
                                                            <input
                                                                class="form-check-input staff-status-switch"
                                                                type="checkbox"
                                                                id="staff_status_{{ $staff->id }}"
                                                                data-id="{{ $staff->id }}"
                                                                data-cc-no="{{ $staff->staff_cc_no }}"
                                                                {{ $verifyFlag == 1 ? 'checked' : '' }}>
                                                        </div>

                                                    </div>

                                                @endif

                                            </td>




                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center">No Staffs available.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <p class="mt-4 mb-2 fw-bold text-info">6B. Other Staff Details</p>
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>

                                                        <th>Staff Category </th>


                                                        <th>CC Number <br>
                                                       CC First Issue , <br>Validity From<br> Validity To</th>


                                                        <th>History of Staff
                                                        </th>
                                                        <th class="text-center">Verify</th>

                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($otherstaffdetails as $index => $staff)
                                                    <tr>


                                                        <td>{{ $staff->staff_category }}</td>

                                                        <td>

                                                        @if( $staff->staff_category == 'OTHERS')
                                                            {{ $staff->staff_designation }}

                                                        @else



                                                        {{ $staff->staff_cc_first_issue
                                                            ? \Carbon\Carbon::parse($staff->staff_cc_first_issue)->format('d-m-Y')
                                                            : ''
                                                        }},<br>


                                                        {{ $staff->staff_cc_validity_from
                                                            ? \Carbon\Carbon::parse($staff->staff_cc_validity_from)->format('d-m-Y')
                                                            : ''
                                                        }},<br>


                                                        {{ $staff->staff_cc_validity_to
                                                            ? \Carbon\Carbon::parse($staff->staff_cc_validity_to)->format('d-m-Y')
                                                            : ''
                                                        }}


                                                        @endif
                                                        </td>


                                                    <td>
                                                        @if( $staff->staff_category != 'OTHERS')





                                                        <button type="button"
                                                            class="btn btn-primary verify-staff"



                                                            data-license="{{ $staff->staff_cc_no }}"

                                                            data-dateofissue="{{ $staff->staff_cc_first_issue
                                                                ? \Carbon\Carbon::parse($staff->staff_cc_first_issue)->format('d-m-Y')
                                                                : ''
                                                            }}"

                                                            data-validfrom="{{ $staff->staff_cc_validity_from
                                                                ? \Carbon\Carbon::parse($staff->staff_cc_validity_from)->format('d-m-Y')
                                                                : ''
                                                            }}"

                                                            data-validto="{{ $staff->staff_cc_validity_to
                                                                ? \Carbon\Carbon::parse($staff->staff_cc_validity_to)->format('d-m-Y')
                                                                : ''
                                                            }}">

                                                            View History
                                                        </button>





                                                        <div class="history-result_staff mt-2"></div>
                                                        @endif
                                                    </td>

                                                     <td class="text-center">

                                                        @if($staff->staff_category != 'OTHERS')

                                                            @php
                                                                $verifyFlag = isset($verifyList[$staff->staff_cc_no])
                                                                    ? (int) $verifyList[$staff->staff_cc_no]
                                                                    : 0;
                                                            @endphp

                                                            <div class="d-flex align-items-center justify-content-center gap-2">

                                                                <span
                                                                    id="staffStatusText_{{ $staff->id }}"
                                                                    class="badge {{ $verifyFlag == 1 ? 'bg-success' : 'bg-danger' }}">
                                                                    {{ $verifyFlag == 1 ? 'Verified' : 'Not Verified' }}
                                                                </span>

                                                                <div class="form-check form-switch">
                                                                    <input
                                                                        class="form-check-input staff-status-switch"
                                                                        type="checkbox"
                                                                        id="staff_status_{{ $staff->id }}"
                                                                        data-id="{{ $staff->id }}"
                                                                        data-cc-no="{{ $staff->staff_cc_no }}"
                                                                        {{ $verifyFlag == 1 ? 'checked' : '' }}>
                                                                </div>

                                                            </div>

                                                        @endif

                                                    </td>

                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="3" class="text-center">No Staffs available.</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                <!-- ---------------------------License History--------------- -->

               <div class="modal fade inputForm-modal" id="showlicense" tabindex="-1" aria-labelledby="inputFormModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">License History</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered text-center">
                                    <thead>
                                        <tr>
                                            <!-- <th>S.No</th> -->
                                            <th>License Number</th>
                                            <th>Issued on</th>
                                            <th>Expires on</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class=""><td colspan="5" class="text-muted text-center">No data available</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                         <!-- ---------------------------License End--------------- -->

                                        <div class="row mt-3">

                                            <div class="row ">
                                                <div class="col-lg-12">
                                                    <p class="text-info"><strong>7. Bank Solvency Certificate Details</strong></p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-2">
                                                    <p><strong>Bank Name</strong></p>
                                                </div>
                                                <div class="col-lg-6">
                                                    <p>{{ $banksolvency->bank_address ?? '' }}</p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-2">
                                                    <p><strong>Validity</strong></p>
                                                </div>
                                                <div class="col-lg-2">
                                                    <p>{{ format_date($banksolvency->bank_validity) ?? ''}}</p>
                                                </div>
                                                   <div class="col-lg-4">
                                                            @if($showbankWarning)
                                                                     <p class="text-left fw-bold text-danger">Bank validity period is less than EA licence period</p>
                                                                @endif

                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-2">
                                                    <p><strong>Amount</strong></p>
                                                </div>
                                                <div class="col-lg-6">
                                                    <P>{{ $banksolvency->bank_amount ?? '' }}</P>
                                                </div>
                                            </div>

                                              <div class="row">
                                                <div class="col-lg-2">
                                                    <p><strong>Proof</strong></p>
                                                </div>
                                                <div class="col-lg-6">
                                                    <a href="{{asset($banksolvency->bank_doc)}}" class="file_view fw-bold" target="_blank"><i class="fa fa-file-pdf-o"></i> View </a>
                                                </div>
                                            </div>

                                    </div>
                                </div>


                                <div class="tab-pane fade" id="other-tab-panel" role="tabpanel" aria-labelledby="other-tab" tabindex="0">

                                    <div class="row mt-3">


                                        <div class="col-lg-8">
                                            <p><strong>8. Has the applicant or any of his/her staff referred to under item 6, been
                                                    at any time convicted in any court of law or punished by any other
                                                    authority for criminal offences</strong></p>
                                        </div>
                                        <div class="col-lg-2">
                                            <p>{{ strtoupper($applicant->criminal_offence) }}</p>
                                        </div>



                                                 @php
                                                    $criminaloffence = $attachments_cl->where('type', 'criminaloffence')->first();
                                                @endphp

                                                @if($criminaloffence)
                                                    <div class="col-lg-2">
                                                        <a href="{{asset($criminaloffence->file_doc)}}" class="file_view fw-bold" target="_blank"><i class="fa fa-file-pdf-o"></i> View</a>
                                                    </div>
                                                @endif










                                    </div>

                                    <div class="row mt-3">

                                            <div class="row ">
                                                <div class="col-lg-12">
                                                    <p class="text-info"><strong>12. Address Proof  </strong></p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <p><strong>Type of Address Proof</strong></p>
                                                </div>
                                                <div class="col-lg-4">
                                                    <p>{{$addressproof->type_doc ?? ''}}</p>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-3">
                                                    <p><strong>GST/ Rental Aggrement/Others No</strong></p>
                                                </div>
                                                <div class="col-lg-4">
                                                    <p>{{$addressproof->addressproofno ?? ''}}</p>
                                                </div>

                                            </div>


                                              <div class="row">
                                                <div class="col-lg-3">
                                                    <p><strong>Address Proof</strong></p>
                                                </div>
                                                <div class="col-lg-6">
                                                    <a href="{{asset($addressproof->file_doc)}}" class="file_view fw-bold" target="_blank"><i class="fa fa-file-pdf-o"></i> View </a>
                                                </div>
                                            </div>

                                    </div>
                                </div>



                                <!-- ----------------equipment-tab--------------------- -->
                                   <div class="tab-pane fade" id="equipment-tab-panel" role="tabpanel" aria-labelledby="equipment-tab" tabindex="0">

                                    <div class="row mt-3">


                                        <div class="col-md-12">

                                            @php
                                            /*
                                            Create map:
                                            equip_id => equipment_value


                                            */
                                           $equipmentMap = collect($equipmentlist)->keyBy('equipment_id');

                                            /*
                                            Get licence_id from stored table
                                            */
                                            $licenceId = optional($equipmentlist->first())->licence_id;
                                            @endphp

                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm align-middle">
                                                    <thead class="">
                                                        <tr>
                                                            <th >S.No</th>
                                                            <th>Equipment Name</th>
                                                            <th>Equipment <br> Type</th>
                                                            <th>Serial No</th>
                                                            <th>Make Model</th>
                                                            <th>Test Report</th>
                                                            <th>Purchase Report </th>
                                                            <th>Date of Test </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        @foreach($equiplist as $index => $equip)

                                                        @if($equip->equip_licence_name == $licenceId)

                                                        @php
                                                        $userEquip = $equipmentMap[$equip->id] ?? null;

                                                        $serial = $userEquip->serial_no ?? '';
                                                        $model  = $userEquip->model_no ?? '';
                                                        $date   = $userEquip->dateoftest ?? '';

                                                        $testfile   = $userEquip->testreport_file ?? '';

                                                        $purchasefile   = $userEquip->purchasereport_file;
                                                        @endphp

                                                        <tr>
                                                            <td class="text-center">{{ $loop->iteration }}</td>

                                                            <td>
                                                                {{ $equip->equip_name }}

                                                            </td>

                                                            <td>
                                                                {{ $equip->equipment_type }}

                                                            </td>

                                                            <td>{{ strtoupper($serial) }}</td>

                                                            <td>{{ strtoupper($model) }}</td>


                                                            <td><a href="{{asset($testfile)}}" target="_blank" class="file_view fw-bold"><i class="fa fa-file-pdf-o"></i> View </a> </td>
                                                            <td><a href="{{asset($purchasefile)}}" target="_blank" class="file_view fw-bold"><i class="fa fa-file-pdf-o"></i> View </a> </td>

                                                             <td>
                                                                {{ $date ? \Carbon\Carbon::parse($date)->format('d-m-Y') : '' }}
                                                            </td>
                                                        </tr>

                                                        @endif

                                                        @endforeach

                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>









                                        <!-- --------------18--------------------------- -->




                                    </div>
                                </div>
                                <!-- -------------------------------- -->

                                <div class="tab-pane fade" id="payment-tab-panel" role="tabpanel" aria-labelledby="payment-tab" tabindex="0">
                                    <div class="row mt-3">

                                            <div class="col-lg-6">
                                                <div class="row ">
                                                    <h6 class="fw-bold text-primary">Payment Details</h6>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-lg-6">
                                                        <p><strong> Payment Status</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p class="badge text-success">{{ strtoupper($applicant->payment_status) ?? 'NA' }}</p>
                                                    </div>


                                                     <div class="col-lg-6">
                                                        <p><strong>Appication Fees</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        @if($applicant->application_fee > 0)
                                                        <p>{{ $applicant->application_fee }}.00</p>
                                                        @else
                                                            <p>{{ $applicant->amount }}.00</p>
                                                        @endif
                                                    </div>

                                                    @if ($applicant->late_fee > 0)

                                                     <div class="col-lg-6">
                                                        <p><strong>Late fees</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->late_fee }}.00</p>
                                                    </div>
                                                    @endif


                                                     <div class="col-lg-6">
                                                        <p><strong>Amount Paid</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->amount }}.00</p>
                                                    </div>




                                                </div>
                                            </div>
                                             <div class="col-lg-6">
                                                <div class="row ">
                                                    <h6 class="fw-bold text-primary">Transaction Instruments</h6>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-lg-6">
                                                        <p><strong> Payment Status</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p class="badge text-success">{{ strtoupper($applicant->payment_status) ?? 'NA' }}</p>
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
                                                        <p>UPI</p>
                                                        {{-- <P>{{ $applicant->payment_mode }}</P> --}}
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p><strong> Payment Time</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ format_date_other($applicant->created_at) }}</p>
                                                    </div>

                                                </div>
                                            </div>



                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                    <!-- <div class="modal-footer mt-2">
                            <button class="btn btn-light-dark _effect--ripple waves-effect waves-light  " style="margin-right: 20px;" data-bs-dismiss="modal">Discard</button>
                            <button type="button" class="btn btn-primary _effect--ripple waves-effect waves-light">Save</button>
                        </div> -->
                </div>

            </div>


        </div>

        <!-- -------------------------------------------- -->
         <div class="row ">
             <div class="statbox widget officers_section mb-2">
                <div class="col-lg-12 col-12 text-center">
                    <h6>Officers Handling Functions </h6>
                </div>
                                            </div>


         </div>
        <div class="row">
            <div id="tabsSimple" class="col-xl-6 col-12 layout-spacing">

                <div class="row align-items-center">

                    <div class="col-lg-12">
                       <div class="statbox widget box box-shadow mb-2">
                            <div class="row align-items-center">

                                <div class="col-lg-12">
                                    {{-- <div class="form-check form-switch">
                                        <label class="form-check-label fw-bold text-end" for="flexSwitchCheckDefault">If you have any queries</label>
                                        <input class="form-check-input" type="checkbox" role="switch" id="Queryswitch">
                                    </div> --}}
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
                    </div>
                </div>

                <div class="row">
                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                    <div class="statbox widget box box-shadow">

                        <div class="widget-header">
                            <h4>Remarks</h4>



                            <textarea class="form-control placement-top" id="remarks" name="remarks" rows="4"
                                        cols="50" maxlength="250"></textarea>

                             <span id="remarks_error" class="text-danger small"></span>
                        </div>
                         <div class="modal-footer mt-2" style="justify-content: center;">

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

                                @if ($role == 'Supervisor' || $role == 'Supervisor2')

                                {{-- Forward to Assistant Secretary --}}

                                <button class="btn btn-success" id="forwardbtn"  >
                                        Forward to {{ $workflow[$role] }}
                                </button>


                                @elseif ($role == 'Assistant Secretary')
                                <div class="row justify-content-center">
                                    <div class="col-12">
                                        <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                            {{-- Forward to Secretary --}}
                                            <button class="btn btn-success" id="forwardbtn" data-bs-toggle="modal" data-bs-target="#declarationModal">
                                                Forward to {{ $workflow[$role] }}
                                            </button>

                                        </div>
                                    </div>
                                </div>

                                @elseif ($role == 'Secretary')




                                        <button class="btn btn-success" id="confirmForwardPres">
                                            Forward to {{ $workflow[$role] }}
                                        </button>


                                        @if(trim($applicant->appl_type) !='D')


                                    <button id="confirmReturnBtn" class="btn btn-warning">
                                        Return to Supervisor
                                    </button>

                                     <button class="btn btn-info" id="returntoapplicant">
                                            Return to Applicant
                                    </button>
                                    @endif
                                    <button class="btn btn-danger">Reject</button>

                                @elseif ($role == 'President')
                                    <button class="btn btn-success" id="confirmApprovalBtn">
                                       Approve
                                    </button>
                                    <button id="confirmReturnBtn" class="btn btn-warning">
                                        Return to Secretary
                                    </button>

                                    <!-- <button id="returntoSecretary" class="btn btn-warning">
                                        Return to Secretary
                                    </button> -->
                                    <!-- <button class="btn btn-danger">Reject</button> -->
                                @endif

                            </div>
                        <!-- <div class="modal-footer mt-2" style="justify-content: center;">
                            <button class="btn btn-success" id="forwardbtn" style="margin-right: 20px;" data-bs-toggle="modal" data-bs-target="#declarationModal" disabled>Forward To {{ $applicant->application_status == 'RE' ? 'Secretary': 'Assistant Secretary'}}</button>
                            <button class="btn btn-warning " style="margin-right: 20px;" data-bs-dismiss="modal">On Hold</button>
                            {{-- <button type="button" class="btn btn-danger">Reject</button> --}}
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
         <!-- ------------------------------------ -->
           @if ($role == 'Secretary' || $role == 'President')
            <div class="col-lg-6 layout-spacing" id="return_section" style="display:none;" >
                <div class="statbox widget box box-shadow">
                    <div class="widget-header" >
                        <div class="row">
                            <div class="col-xl-12 officers_section col-md-12 col-sm-12 col-12">
                                <h4 class=" text-white">Reason for Return Application to Applicant</h4>
                            </div>
                        </div>
                        <div class="form-check mt-2">
                            <input type="checkbox" id="basic_details" name="return_reasons[]" value="basic_details" class="form-check-input return-checkbox">
                            <label class="form-check-label" for="basic_details"> 1 & 2) Applicant Basic Detail (Name & Address)</label>
                        </div>
                         <div class="form-check">
                          <input type="checkbox" id="ownership_details" name="return_reasons[]" value="ownership_details" class="form-check-input return-checkbox">
                            <label class="form-check-label" for="ownership_details">3) Applicant Ownership Details </label>
                        </div>




                             <div class="form-check">
                          <input type="checkbox" id="qc_staff" name="return_reasons[]" value="qc_staff" class="form-check-input return-checkbox">
                            <label class="form-check-label" for="qc_staff">4) QC Staff Issue </label>
                        </div>

                         <div class="form-check">
                           <input type="checkbox" id="staff_details" name="return_reasons[]" value="staff_details" class="form-check-input return-checkbox">
                            <label class="form-check-label" for="staff_details">5) Other Staff Issue</label>
                        </div>
                         <div class="form-check">
                           <input type="checkbox" id="bank_solvency" name="return_reasons[]" value="bank_solvency" class="form-check-input return-checkbox">
                            <label class="form-check-label" for="bank_solvency">6) Bank Solvency </label>
                        </div>

                         <div class="form-check">
                           <input type="checkbox" id="atachment_points" name="return_reasons[]" value="atachment_points" class="form-check-input return-checkbox">
                            <label class="form-check-label" for="bank_solvency">7 and 8)  Attachments Points </label>
                        </div>

                        <div class="form-check">
                           <input type="checkbox" id="address_proof" name="return_reasons[]" value="address_proof" class="form-check-input return-checkbox">
                            <label class="form-check-label" for="address_proof">9)  Address Proof </label>
                        </div>

                        <div class="form-check">
                           <input type="checkbox" id="equipments_details" name="return_reasons[]" value="equipments_details" class="form-check-input return-checkbox">
                            <label class="form-check-label" for="equipments_details">10)  Equipments Details </label>
                        </div>

                        <div id="checkbox_error" class="text-danger mt-2 fw-bold" style="display:none;"></div>

                        <div class="">
                            <h4>Remarks For Return (Optional)</h4>



                            <textarea class="form-control" name="remarks_return" id="remarks_return" rows="4" cols="50"  maxlength="300"></textarea>
                        </div>



                        <button class="btn btn-success mt-2" id="process_return">
                            Submit Return
                        </button>

                         <button class="btn btn-info mt-2" id="exit_return">
                            Exit
                        </button>
                    </div>
                </div>
            </div>
            @endif
                <!-- ------------------------------------- -->
         <div id="timelineMinimal" class="col-lg-12 layout-spacing">
            <div class="statbox widget box box-shadow">
                <div class="widget-header">
                    <div class="row">
                        <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                             <h4>Application Process Flow</h4>
                        </div>
                    </div>
                </div>
                <div class="widget-content widget-content-area pb-1">
                    <div class="mt-container mx-auto">
                         <div class="timeline-line">
                                    @foreach ($workflows as $row)

                                    <div class="item-timeline">
                                        <p class="t-time">{{ format_date_other($row->created_at) }}</p>

                                        <div class="t-dot
                                                    {{ $row->appl_status == 'RE' ? 't-dot-danger' : ($row->appl_status == 'A' ? 't-dot-success' : 't-dot-info') }}">
                                        </div>
                                        <div class="t-text">
                                            @php
                                            // Normalize name if it's "auditor" (any case)
                                            $displayName = $row->name;
                                            if ($displayName && strcasecmp($displayName, 'auditor') === 0) {
                                            $displayName = 'Assistant Secretary';
                                            }
                                            @endphp



                                            @if ($row->appl_status == 'RE')

                                            @if($row->processed_by == 'AP')
                                            <p>Resubmitted by Applicant </p>
                                            @else

                                            <p>Returned by {{ $row->processed_by }}</p>
                                            @endif
                                            @elseif ($row->appl_status == 'RET')
                                            <p>Returned by {{ $row->processed_by }}</p>
                                            @elseif ($row->appl_status == 'A')
                                            <p>Approved by {{ $row->processed_by }}</p>
                                            @else
                                            <p>Processed by {{ $row->processed_by }}</p>
                                            @endif

                                             @if ($row->processed_by !== 'Assistant Secretary')
                                            @if ($row->query_status == "P")
                                                <p class="text-danger">Note: Query raised by {{ $row->processed_by }} (
                                                    @php
                                                        $queries = $row->queries;

                                                        if (is_string($queries)) {
                                                            $queries = json_decode($queries, true);
                                                        }
                                                    @endphp

                                                    @if(!empty($queries) && is_array($queries))
                                                        {{ implode(', ', $queries) }}
                                                    @endif
                                                    )
                                                </p>
                                            @endif
                                        @endif


                                            <p class="t-meta-time">
                                                @if($row->appl_status == 'RET')
                                                Returned to Applicant
                                                <br>
                                                Remarks: {{ $row->remarks }}
                                                <br>

                                                @php
                                                $reasons = json_decode($row->return_reason, true);
                                                @endphp



                                                <span class="text-return">
                                                    @if(!empty($reasons))
                                                    Return Reason :

                                                    {{ collect($reasons)->map(fn($r) => Str::upper($r))->implode(', ') }}
                                                    <br>

                                                    Return Remarks :
                                                    @if($row->remarks_return)
                                                    {{ $row->remarks_return }}
                                                    @else
                                                    No Remraks
                                                    @endif
                                                    @endif

                                                </span>
                                                <br>


                                                @else

                                                @if (!$row->name)
                                                Approved by {{ $row->processed_by }}
                                                @else


                                                @if($row->processed_by !== 'AP')

                                                @if($row->appl_status == 'RE')
                                                Returned to {{ $displayName }}<br>
                                                @else
                                                Forwarded to {{ $displayName }} <br>

                                                @endif

                                                Remarks: {{ $row->remarks }}

                                                @endif
                                                @endif
                                                @endif
                                            </p>


                                        </div>

                                    </div>
                                    @endforeach

                                    <div class="item-timeline">
                                        <p class="t-time">{{ format_date_other($user_entry->dt_submit) }}</p>
                                        <div class="t-dot t-dot-warning"></div>
                                        <div class="t-text">
                                            <p>Received from Applicant</p>
                                            <p class="t-meta-time">Form: {{ $user_entry->form_name }}, License: {{ $user_entry->license_name }}</p>
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


<!-- <div class="modal fade" id="forwardmodal" tabindex="-1" aria-labelledby="declarationModalLabel" aria-hidden="true">
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
                <button type="button" class="btn btn-success" id="confirmForward" disabled>Forward to President</button>
            </div>
        </div>
    </div>
</div> -->

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="successModalLabel">Success</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p>Application has been successfully approved!</p>
                <p><strong>License Number:</strong> <span id="licenseNumber"></span></p>
                {{-- <p><strong>License Expiry:</strong> <span id="licenseExpiry"></span></p> --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Error Modal -->
<div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="errorModalLabel">Error</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <p id="errorMessage">Something went wrong. Please try again.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
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
@include('admin.include.footer')
<script>
    var switch_status = document.getElementById('Queryswitch');

    if (switch_status.checked) {
        document.getElementById('queryOptions').style.display = 'block';
    } else {
        document.getElementById('queryOptions').style.display = 'none';
    }

    document.getElementById('Queryswitch').addEventListener('change', function() {
        document.getElementById('queryOptions').style.display = this.checked ? 'block' : 'none';
    });


 $('#remarks').maxlength({
    placement: "top",
    warningClass: "badge badge-success",
    limitReachedClass: "badge badge-danger",
    alwaysShow: true
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

            var oldapplicationId = @json($applicant->old_application);

            var appl_type = @json($applicant->appl_type);
            var form_name = @json($applicant->form_name);

            var licensename      = @json($applicant->license_name);
            var applicationId    = @json($applicant->application_id);
            var processedBy      = @json(Auth::user()->name);

            var old_issuedat      = @json($old_issued_at_date);

            var returnapp      = @json($applicant->return_flag);

            // alert(old_issuedat);
            var remarks          = $("#remarks").val().trim();
             $("#remarks_error").text("");

                    if (remarks === "") {
                        $("#remarks_error").text("Remarks is required.");
                        $("#remarks").focus();
                        return;
                    }

            var firstCertNo = @json($qcQscStaffs_details->first()->staff_cc_no);
            // alert(firstStaff);
            // var firstCertNo = firstStaff.cc_number;
            var qc_validity_date =  @json($qcQscStaffs_details->first()->staff_cc_validity_to);

            // alert(qc_validity_date);
            var bank_validity = @json($banksolvency->bank_validity);

             var checklistStatus = [];

            var maxQcCertNo = @json($maxQcCertNo);

            var qc_validity_date = @json($maxQcValidityDate);

            // alert(qc_validity_date);

                    $("#specific-class input[name='checklists[]']:checked").each(function () {
                        checklistStatus.push($(this).val());
                    });

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


            // 🔹 STEP 1: CHECK VALIDITY FROM CONTROLLER
            $.ajax({
                url: '{{ route('admin.checkallvalidity') }}',
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                data: {
                    form_name: form_name,
                    firstCertNo: firstCertNo,
                    qc_validity_date: qc_validity_date,
                    bank_validity: bank_validity,
                    appl_type: appl_type,
                    returnapp : returnapp,
                    checklists: checklists,
                    status: status,
                    check_id: check_id

                },
                success: function (res) {

                    if (res.status === "INVALID") {

                        Swal.fire({
                            width: 780,
                            padding: "0",
                            background: "#ffffff",
                            showCancelButton: true,
                            confirmButtonText: "✓ Proceed",
                            cancelButtonText: "Cancel",
                            confirmButtonColor: "#198754",
                            cancelButtonColor: "#6c757d",
                            allowOutsideClick: false,
                            allowEscapeKey: false,

                            html: `
                                <div style="font-family: Arial, sans-serif; text-align:left;">

                                    <!-- Header -->
                                    <div style="
                                        background: linear-gradient(135deg, #dc3545, #b02a37);
                                        color:#fff;
                                        padding:22px 25px;
                                        margin:-0px -0px 0 -0px;
                                        border-radius:8px 8px 0 0;
                                    ">
                                        <div style="
                                            display:flex;
                                            align-items:center;
                                            gap:12px;
                                        ">
                                            <div style="
                                                width:48px;
                                                height:48px;
                                                border-radius:50%;
                                                background:rgba(255,255,255,0.18);
                                                display:flex;
                                                align-items:center;
                                                justify-content:center;
                                                font-size:25px;
                                            ">
                                                ⚠
                                            </div>

                                            <div>
                                                <div style="
                                                    font-size:21px;
                                                    font-weight:700;
                                                ">
                                                    Licence Validity Information
                                                </div>

                                                <div style="
                                                    font-size:13px;
                                                    opacity:.9;
                                                    margin-top:4px;
                                                ">
                                                    Please review the validity details before proceeding
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <!-- Certificate / Bank Details -->
                                    <div style="padding:22px 25px 10px;">

                                        <div style="
                                            display:grid;
                                            grid-template-columns:1fr 1fr;
                                            gap:15px;
                                        ">

                                            <!-- QC Certificate -->
                                            <div style="
                                                border:1px solid #dee2e6;
                                                border-radius:10px;
                                                padding:17px;
                                                background:#f8f9fa;
                                            ">
                                                <div style="
                                                    color:#6c757d;
                                                    font-size:12px;
                                                    font-weight:600;
                                                    text-transform:uppercase;
                                                    margin-bottom:8px;
                                                ">
                                                    QC Certificate
                                                </div>

                                                <div style="
                                                    font-size:16px;
                                                    font-weight:700;
                                                    color:#212529;
                                                    margin-bottom:6px;
                                                ">
                                                    ${firstCertNo ?? "-"}
                                                </div>

                                                <div style="
                                                    font-size:13px;
                                                    color:#495057;
                                                ">
                                                    Validity:
                                                    <strong style="color:#dc3545;">
                                                        ${formatDDMMYYYY(qc_validity_date)}
                                                    </strong>
                                                </div>
                                            </div>


                                            <!-- Bank Solvency -->
                                            <div style="
                                                border:1px solid #dee2e6;
                                                border-radius:10px;
                                                padding:17px;
                                                background:#f8f9fa;
                                            ">
                                                <div style="
                                                    color:#6c757d;
                                                    font-size:12px;
                                                    font-weight:600;
                                                    text-transform:uppercase;
                                                    margin-bottom:8px;
                                                ">
                                                    Bank Solvency
                                                </div>

                                                <div style="
                                                    font-size:16px;
                                                    font-weight:700;
                                                    color:#212529;
                                                    margin-bottom:6px;
                                                ">
                                                    Validity
                                                </div>

                                                <div style="
                                                    font-size:13px;
                                                    color:#495057;
                                                ">
                                                    Expiry:
                                                    <strong style="color:#dc3545;">
                                                        ${formatDDMMYYYY(bank_validity)}
                                                    </strong>
                                                </div>
                                            </div>

                                        </div>


                                        <!-- Warning Box -->
                                        <div style="
                                            margin-top:20px;
                                            border:1px solid #ffc107;
                                            border-left:5px solid #dc3545;
                                            border-radius:8px;
                                            background:#fff8e1;
                                            padding:18px 20px;
                                        ">

                                            <div style="
                                                color:#dc3545;
                                                font-size:17px;
                                                font-weight:700;
                                                margin-bottom:12px;
                                            ">
                                                ⚠ ${res.message}
                                            </div>

                                            <div style="
                                                font-size:14px;
                                                color:#495057;
                                                line-height:1.7;
                                            ">
                                                Licence Validity Date:
                                                <strong style="color:#dc3545;">
                                                    ${formatDDMMYYYY(res.licence_validitydate)}
                                                </strong>
                                            </div>

                                            <div style="
                                                margin-top:8px;
                                                font-size:14px;
                                                color:#495057;
                                                line-height:1.7;
                                            ">
                                                Hence, the licence will be issued only up to:
                                                <strong style="
                                                    color:#b02a37;
                                                    font-size:16px;
                                                ">
                                                    ${formatDDMMYYYY(res.renewal_period)}
                                                </strong>
                                            </div>

                                        </div>


                                        <!-- Confirmation -->
                                        <div style="
                                            margin-top:20px;
                                            padding:14px;
                                            background:#f1f3f5;
                                            border-radius:8px;
                                            text-align:center;
                                        ">
                                            <span style="
                                                font-size:14px;
                                                color:#495057;
                                            ">
                                                Do you want to continue with the licence approval?
                                            </span>
                                        </div>

                                    </div>

                                </div>
                            `,

                            customClass: {
                                popup: "licence-warning-popup",
                                actions: "licence-warning-actions"
                            }

                        }).then((result) => {

                            if (result.isConfirmed) {
                                showApprovePopup("YES");
                            }

                        });

                    } else {

                        // No warning → proceed directly
                        showApprovePopup("No");
                    }
                }
            });
            function formatDDMMYYYY(dateStr) {
                if (!dateStr) return '';
                const d = new Date(dateStr);
                const day = String(d.getDate()).padStart(2, '0');
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const year = d.getFullYear();
                return `${day}-${month}-${year}`;
            }
            // -----------------------------
            // FINAL APPROVAL POPUP
            // -----------------------------
            function showApprovePopup(validity_override) {

                Swal.fire({
                title: "Declaration",
                text: 'Confirm to this application has been reviewed and approved.',
                showCancelButton: true,
                confirmButtonText: "Approved",
                cancelButtonText: "Cancel",
                focusConfirm: false,
                }).then((result) => {

                    if (result.isConfirmed) {

                        $.ajax({
                            url: '{{ route('admin.approveApplicationForma') }}',
                            type: 'POST',
                            headers: {
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                            },
                            data: {
                                application_id: applicationId,
                                oldapplicationId: oldapplicationId,
                                processed_by: processedBy,
                                licensename: licensename,
                                remarks: remarks || "No remarks provided",

                                  validity_override: validity_override,
                                    qc_validity_date: qc_validity_date,
                                    bank_validity: bank_validity,
                                    appl_type: appl_type,
                                    old_issuedat : old_issuedat
                            },
                          success: function (response) {

                                if (response.status === "success") {

                                   let digitisationHTML = "";

                                    if (
                                        appl_type.trim() === "D" &&
                                        response.digitisation &&
                                        response.digitisation.old_cl_no &&
                                        response.digitisation.new_cl_no
                                    ) {

                                        let digitisation = response.digitisation;

                                        digitisationHTML = `
                                            <div class="digitisation-card">

                                                <div class="digitisation-title">
                                                    <span class="digitisation-icon">🔄</span>
                                                    <span>CL Digitisation Mapping</span>
                                                </div>

                                                <div class="mapping-wrapper">

                                                    <div class="mapping-item old-cl">
                                                        <div class="mapping-label">
                                                            OLD CL NUMBER
                                                        </div>

                                                        <div class="mapping-value">
                                                            ${digitisation.old_cl_no}
                                                        </div>
                                                    </div>

                                                    <div class="mapping-arrow">
                                                        →
                                                    </div>

                                                    <div class="mapping-item new-cl">
                                                        <div class="mapping-label">
                                                            NEW LICENCE NUMBER
                                                        </div>

                                                        <div class="mapping-value">
                                                            ${digitisation.new_cl_no}
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>
                                        `;
                                    } else {

                                        /*
                                        * No digitisation mapping found
                                        */

                                        digitisationHTML = `

                                            <div class="digitisation-not-found">

                                                <span class="info-icon">
                                                    ℹ
                                                </span>

                                                <span>
                                                    No CL Digitisation mapping found
                                                    for this application.
                                                </span>

                                            </div>

                                        `;
                                    }


                                    /* -----------------------------------------
                                    FINAL SUCCESS POPUP
                                    ----------------------------------------- */

                                    Swal.fire({

                                        width: 780,

                                        padding: 0,

                                        background: "#ffffff",

                                        showConfirmButton: true,

                                        confirmButtonText: "OK",

                                        confirmButtonColor: "#198754",

                                        allowOutsideClick: false,

                                        allowEscapeKey: false,

                                        customClass: {

                                            popup: "approval-success-popup",

                                            confirmButton: "approval-ok-button"

                                        },

                                        html: `

                                            <div class="approval-result">


                                                <!-- =========================
                                                    HEADER
                                                ========================== -->

                                                <div class="approval-header">

                                                    <div class="success-circle">
                                                        ✓
                                                    </div>

                                                    <div class="approval-header-text">

                                                        <div class="approval-title">
                                                            Application Approved
                                                        </div>

                                                        <div class="approval-subtitle">
                                                            Licence has been successfully generated
                                                        </div>

                                                    </div>

                                                </div>


                                                <!-- =========================
                                                    LICENCE DETAILS
                                                ========================== -->

                                                <div class="licence-details">

                                                    <div class="section-heading">

                                                        <span class="heading-icon">
                                                            📄
                                                        </span>

                                                        Licence Details

                                                    </div>


                                                    <div class="licence-card">


                                                        <!-- Licence Number -->

                                                        <div class="detail-row">

                                                            <div class="detail-label">
                                                                Licence Number
                                                            </div>

                                                            <div class="detail-value licence-number">
                                                                ${response.license_number ?? "-"}
                                                            </div>

                                                        </div>


                                                        <!-- Issued Date -->

                                                        <div class="detail-row">

                                                            <div class="detail-label">
                                                                Issued Date
                                                            </div>

                                                            <div class="detail-value">
                                                                ${formatDDMMYYYY(response.issued_at)}
                                                            </div>

                                                        </div>


                                                        <!-- Validity -->

                                                        <div class="detail-row">

                                                            <div class="detail-label">
                                                                Licence Validity Up To
                                                            </div>

                                                            <div class="detail-value expiry-date">
                                                                ${formatDDMMYYYY(response.expires_at)}
                                                            </div>

                                                        </div>


                                                    </div>


                                                    <!-- Expiry message -->

                                                    <div class="expiry-message">

                                                        <span class="expiry-check">
                                                            ✓
                                                        </span>

                                                        <span>
                                                            ${response.message}
                                                        </span>

                                                    </div>

                                                </div>


                                                <!-- =========================
                                                    DIGITISATION MAPPING
                                                ========================== -->

                                                ${digitisationHTML}


                                            </div>

                                        `

                                    }).then(() => {

                                        window.location.href =
                                            "{{ url('admin/dashboard') }}";

                                    });

                                }
                            }
                        });
                    }
                });
            }
        });


        confirmForwardPres.click(function () {
            var applicationId   = @json($applicant->application_id);
            var role_id         = @json(Auth::user()->roles_id);
            var forwardedTo     = @json($nextForwardUser->roles_id);
            var processedBy     = @json(Auth::user()->name);
            var role            = @json($nextForwardUser->name);
            var remarks         = $("#remarks").val().trim();
            $("#remarks_error").text("");

            if (remarks === "") {
                $("#remarks_error").text("Remarks is required");
                $("#remarks").focus();
                return;
            }
            var queryswitch     = $("#Queryswitch").prop("checked");
            var checkboxStatus  = "Yes";

            var queryType = null;
            var query_status = "No";
              var returnflag = @json($applicant->return_flag);
            var application_status = @json($applicant->application_status);

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

            Swal.fire({
                title: "Declaration",
                text:'I confirm that have been verified by me as a secretary.',
                showCancelButton: true,
                confirmButtonText: "Forward to President",
                cancelButtonText: "Cancel",
                focusConfirm: false,
            }).then((result) => {
                if (result.isConfirmed) {

                    let staffVerification = [];

                        $('.staff-status-switch').each(function () {

                            let staffId = $(this).data('id');

                            let verifyFlag = $(this).is(':checked') ? 1 : 0;

                            staffVerification.push({
                                staff_id: staffId,
                                verify_flag: verifyFlag
                            });

                        });

                        console.log("STAFF VERIFICATION:", staffVerification);

                    $.ajax({
                        url: '{{ route('admin.forwardApplicationforma',["role" => "__ROLE__"]) }}'.replace('__ROLE__', role),
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

                        checkboxes: checkboxStatus,

                        application_status: application_status,

                        queryswitch: queryswitch ? "Yes" : "No",

                        returnflag: returnflag,

                        "queryType[]": queryType,

                        // IMPORTANT
                        staff_verification: JSON.stringify(staffVerification),
                         checklists: checklists,
                                    status: status,
                                    check_id: check_id

                    },

                        success: function (response) {

                            // if (response.status == "success") {

                            //     $('#forwardmodal').modal('hide');

                            //     $('#successModalForward').modal('show');

                            //     $('#successModalForward .modal-body').html(`<p>${response.message}</p>`);

                            //     $('#successModalForward').on('hidden.bs.modal', function () {
                            //         window.location.href = '/admin/dashboard';
                            //     });
                            // }

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


 $('#confirmReturnBtn').on('click', function () {


            var applicationId   = @json($applicant->application_id);
            var returnBy        = @json(Auth::user()->name);
            var forwardedTo     = @json($returnForwardUser->roles_id ?? 0);

            // alert(forwardedTo);
            var remarks         = $("#remarks").val().trim();

            var returnflag = @json($applicant->return_flag);

            var checkboxStatus = "Yes";

            let queryswitch = $("#Queryswitch").prop("checked");
            queryType = $("#queryType").val();
            let errorBox = $("#query_error");

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

                        let staffVerification = [];

                        $('.staff-status-switch').each(function () {

                            let staffId = $(this).data('id');

                            let verifyFlag = $(this).is(':checked') ? 1 : 0;

                            staffVerification.push({
                                staff_id: staffId,
                                verify_flag: verifyFlag
                            });

                        });



            Swal.fire({
               title: "Return",
                html: 'You want to return this application!',
                showCancelButton: true,

                 confirmButtonText: "Forward to {{ 'Secretary' }}",
                cancelButtonText: "Cancel",
                focusConfirm: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('admin.returntoSecretaryforma') }}',
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
                            queryswitch     : queryswitch,
                            "queryType[]": queryType,
                            checklists: checklists,
                                        status: status,
                                        check_id: check_id,

                        returnflag: returnflag,

                        staff_verification: JSON.stringify(staffVerification)

                        },
                        success: function (response) {
                            // if (response.status == "success") {
                            //     $('#returnConfirmModal').modal('hide');
                            //     $('#declarationModal').modal('hide');

                            //     // Success message (can keep Swal or replace with Bootstrap alert/toast)
                            //     $('#returnMessage').text(response.message);
                            //     $('#successModal').modal('show');

                            //     setTimeout(function(){
                            //         window.location.href = '/admin/dashboard';
                            //     }, 2000);
                            // }
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



        // -----------------return to applicant---------------------

        // --------process-----------

        $("#returntoapplicant").on("click", function () {

            $(".modal-footer button").not(this).prop("disabled", true);

            $("#return_section").slideDown();

            $('html, body').animate({
                scrollTop: $("#return_section").offset().top - 120
            }, 600);

        });

        $("#exit_return").on("click", function () {

            $("#return_section").slideUp();

            $(".modal-footer button").prop("disabled", false);

            // Clear checkboxes
            $(".return-checkbox").prop("checked", false);

            // Clear remarks
            $("#remarks_return").val("");

        });

        $("#process_return").on("click", function () {


            var applicationId   = @json($applicant->application_id);
            var returnBy        = @json(Auth::user()->name);
            var forwardedTo     = @json($returnForwardUser->roles_id ?? 0);
            var checkboxStatus = "Yes";

            var remarks         = $("#remarks").val().trim();

            var remarks_return         = $("#remarks_return").val().trim();


         let checkedReasons = [];

        $('.return-checkbox:checked').each(function() {
            checkedReasons.push($(this).val());
        });

        if (checkedReasons.length === 0) {

            // Show error message
            $("#checkbox_error")
                .text("Select at least one reason before return.")
                .fadeIn();

            // Smooth scroll to checkbox section
            $('html, body').animate({
                scrollTop: $("#checkbox_error").offset().top - 150
            }, 600);

            return;
        } else {
            // Hide error if valid
            $("#checkbox_error").fadeOut();
        }


            let queryswitch = $("#Queryswitch").prop("checked");
            queryType = $("#queryType").val();
            let errorBox = $("#query_error");

            Swal.fire({
              title: "Return",
              html: 'Confirm to return this application!',

              showCancelButton: true,
              confirmButtonText: "Return to Applicant",
              cancelButtonText: "Cancel",
              focusConfirm: false,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('admin.returntoapplicantforma') }}',
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
                            reasons         : checkedReasons,
                            queryswitch     : queryswitch,
                            remarks_return     : remarks_return,
                            "queryType[]": queryType
                        },
                        success: function (response) {

                            if (response.status == "success") {
                                Swal.fire({

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

        // -----------------------------------------------
 forwardbtn.click(function() {

                        var remarks = $("#remarks").val().trim();

                         $("#remarks_error").text("");

                        if (remarks === "") {
                            $("#remarks_error").text("Remarks is required");
                            $("#remarks").focus();
                            return;
                        }

            Swal.fire({
                title: "Declaration",
                text: 'I confirm that all documents have been verified.',
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
                    var role = @json($nextForwardUser->name);

                    var returnflag = @json($applicant->return_flag);

                   var application_status = @json($applicant->application_status);
// alert(application_status);

// alert(forwardedTo);


                    // alert(application_status);

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

                    let check_id = {};
                    let checklists = {};
                    let status = {};

                    $('input[name^="check_id["]').each(function () {

                        let id = $(this).attr('name')
                            .replace('check_id[', '')
                            .replace(']', '');

                        check_id[id] = $(this).val();

                        checklists[id] =
                            $('input[name="checklists[' + id + ']"]').is(':checked')
                                ? 1
                                : 0;

                        status[id] =
                            $('input[name="status[' + id + ']"]').is(':checked')
                                ? 1
                                : 0;
                    });

                    console.log("CHECK ID:", check_id);
                    console.log("CHECKLISTS:", checklists);
                    console.log("STATUS:", status);


                    let staffVerification = [];

                        $('.staff-status-switch').each(function () {

                            let staffId = $(this).data('id');

                            let verifyFlag = $(this).is(':checked') ? 1 : 0;

                            staffVerification.push({
                                staff_id: staffId,
                                verify_flag: verifyFlag
                            });

                        });

                        console.log("STAFF VERIFICATION:", staffVerification);

                    $.ajax({
                        url: '{{ route('admin.forwardApplicationforma',["role" => "__ROLE__"]) }}'.replace('__ROLE__', role),
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

                        checkboxes: checkboxStatus,

                        application_status: application_status,

                        queryswitch: queryswitch ? "Yes" : "No",

                        returnflag: returnflag,

                        "queryType[]": queryType,

                        // IMPORTANT
                        staff_verification: JSON.stringify(staffVerification),

                        check_id: check_id,
                        checklists: checklists,
                        status: status


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


        confirmForward.click(function() {

               var applicationId = @json($applicant->application_id);
    var processedBy   = @json(Auth::user()->name);
    var role_id       = @json(Auth::user()->roles_id);
    var forwardedTo   = @json($nextForwardUser->roles_id);
    var role          = @json($nextForwardUser->name);
    var remarks       = $("#remarks").val().trim();
    var queryswitch   = $("#Queryswitch").prop("checked");
    var checkboxStatus = "No";
    var queryType = null;

    if (queryswitch === true) {
        queryType = $("#queryType").val();
        if (!queryType || queryType.length === 0) {
            $('#declarationModal').modal('hide');
            $("#queryToast").toast("show"); // Show Bootstrap Toast
            return false;
        }
        checkboxStatus = "Yes";
    }

    //  alert(
    //      "id: " + applicationId +
    //     "Role: " + role +
    //     "\nRemarks: " + remarks +
    //     "\nCheckbox Status: " + checkboxStatus +
    //     "\nQuery Type: " + (queryType ?? "None")
    // );

            $.ajax({
                url: '{{ route('admin.forwardApplicationforma',["role" => "__ROLE__"]) }}'.replace('__ROLE__', role),
                type: 'POST',
                // contentType: 'application/json',
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },

               data: {
            application_id: applicationId,
            processed_by: processedBy,
            role_id: role_id,
            forwarded_to: forwardedTo,
            remarks: remarks,
            checkboxes: checkboxStatus,
             queryswitch: queryswitch,
            queryType: queryType
                },
                success: function(response) {

                    if (response.status == "success") {
                        // Cleanup Bootstrap modal instance on hide
                        $('#declarationModal').modal('hide');

                        $('#successModal .modal-body').html(`<p>${response.message}</p>`);
                        $('#successModal').modal('show');

                        $('#successModal').on('hidden.bs.modal', function() {
                            window.location.href =  BASE_URL + '/admin/dashboard'
                        });
                    }

                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : "An unexpected error occurred.";
                    $('#errorModal .modal-body').html(`<p>${errorMessage}</p>`);
                    $('#errorModal').modal('show');
                }
            });
        });
    });
</script>


<script>
    $(document).on("click", ".verify-license", function () {
    let license = $(this).data("license");
    let date = $(this).data("date");
    let proprietorId = $(this).data("id");

    $.ajax({
        url: "{{ route('admin.verify.license.formAccc_admin') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            license_number: license,
            date: date
        },
        success: function (response) {
            if (response.exists) {
                $("#verify-result-" + proprietorId)
                    .html('<p class="text-success">Valid License</p>');
            } else {
                $("#verify-result-" + proprietorId)
                    .html('<p class="text-danger">Invalid License</p>');
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                let errMsg = errors.date ? errors.date[0] : "Validation failed";
                $("#verify-result-" + proprietorId)
                    .html('<p class="text-danger">' + errMsg + '</p>');
            }
        }
    });
});


$(document).on('click', '.verify-btn_EA', function () {
    let btn = $(this);
    let license = btn.data('license');
    let date = btn.data('date');
    let resultDiv = btn.siblings('.verify-result_EA');

    resultDiv.html('<p class="text-info">Checking...</p>');

    $.ajax({
    url: "{{ route('admin.verifylicenseformAea_admin') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            license_number: license,
            date: date
        },
        success: function (response) {
            if (response.exists) {
                resultDiv.html('<p class="text-success">Valid License</p>');
            } else {
                resultDiv.html('<p class="text-danger">Invalid License</p>');
            }
        },
        error: function () {
            resultDiv.html('<p class="text-danger">Error checking license</p>');
        }
    });
});



$(document).on('click', '.verify-btn_staff', function () {
    let btn = $(this);
    let license = btn.data('license');
    let date = btn.data('date');
    let resultDiv = btn.siblings('.verify-result_staff');

    resultDiv.html('<p class="text-info">Checking...</p>');

    $.ajax({
        url: "{{ route('admin.verifyLicenseFormAccc_adminstaff') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            license_number: license,
            date: date
        },
        success: function (response) {
            if (response.exists) {
                resultDiv.html('<p class="text-success">Valid License</p>');
            } else {
                resultDiv.html('<p class="text-danger">Invalid License</p>');
            }
        },
        error: function () {
            resultDiv.html('<p class="text-danger">Error checking license</p>');
        }
    });
});


// --------------------------------------------------------------------
$(document).on('click', '.history-btn_staff', function () {
    let btn = $(this);
    let license = btn.data('license');
    let date = btn.data('date');
    let application_id = btn.data('application_id');

    // Set modal title dynamically
    $("#showlicense .modal-title").text(`Certificate Number ${license} Mapped with`);

    // Clear any old data first
    $("#showlicense table tbody").html(`
        <tr>
            <td colspan="5" class="text-info">Fetching license history...</td>
        </tr>
    `);

    // Show modal immediately
    $("#showlicense").modal('show');

    // Fetch data via AJAX
    $.ajax({
        url: "{{ route('admin.history_resultstaff') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            license_number: license,
            date: date,
            application_id: application_id
        },
        success: function (response) {
            if (response.exists && response.html) {
                $("#showlicense table tbody").html(response.html);
            } else {
                $("#showlicense table tbody").html(`
                    <tr>
                        <td colspan="5" class="text-danger">❌ No license history found.</td>
                    </tr>
                `);
            }
        },
        error: function () {
            $("#showlicense table tbody").html(`
                <tr>
                    <td colspan="5" class="text-danger">⚠️ Error fetching license history.</td>
                </tr>
            `);
        }
    });
});



// ----------admin check cert-------------
$(document).on("click", ".verify-cert_ownership", function () {

    let $button = $(this);

    let id = $button.data("id");

    let certificate_no =
        $button.attr("data-license") || "";

    let dateof_issue =
        $button.attr("data-dateofissue") || "";

    let valid_from =
        $button.attr("data-validfrom") || "";

    let valid_to =
        $button.attr("data-validto") || "";


    console.log("========== ADMIN CC VERIFY ==========");
    console.log("ID:", id);
    console.log("Certificate No:", certificate_no);
    console.log("Date of Issue:", dateof_issue);
    console.log("Validity From:", valid_from);
    console.log("Validity To:", valid_to);


    // Check required values
    if (
        !certificate_no ||
        !dateof_issue ||
        !valid_from ||
        !valid_to
    ) {

        $("#competencyVerifyModalBody").html(`

            <div class="alert alert-warning">
                Certificate details are incomplete.
            </div>

        `);

        $("#competencyVerifyModal").modal("show");

        return;
    }


    // Show loading
    $("#competencyVerifyModalBody").html(`

        <div class="text-center py-5">

            <div class="spinner-border text-primary mb-3"
                role="status">
            </div>

            <div class="text-info">
                Checking competency certificate...
            </div>

        </div>

    `);


    // Open modal
    $("#competencyVerifyModal").modal("show");


    // Disable button
    $button
        .prop("disabled", true)
        .text("Verifying...");


    $.ajax({

        url: "{{ route('admin.checkCompetencyCertificateadmin') }}",

        type: "POST",

        data: {

            _token: $('meta[name="csrf-token"]').attr("content"),

            certificate_no: certificate_no,

            dateof_issue: dateof_issue,

            valid_from: valid_from,

            valid_to: valid_to

        },


        success: function (response) {

            console.log(
                "ADMIN VERIFY RESPONSE:",
                response
            );


            // Certificate found
            if (
                response.status === true &&
                response.certificate
            ) {

                let certificate =
                    response.certificate;

                let records =
                    response.data || [];


                // ----------------------------------
                // Certificate Details
                // ----------------------------------

                let result = `

                    <div class="competency-result-box">


                        <!-- CERTIFICATE DETAILS -->

                        <div class="table-responsive mb-4">

                            <table class="table table-bordered">

                                <tbody>

                                    <tr>

                                        <th width="20%">
                                            Certificate No
                                        </th>

                                        <td width="30%">
                                            ${
                                                certificate.certificate_no
                                                ?? '-'
                                            }
                                        </td>


                                        <th width="20%">
                                            Date of Issue
                                        </th>

                                        <td width="30%">
                                            ${
                                                certificate.dateof_issue
                                                ?? '-'
                                            }
                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Validity From
                                        </th>

                                        <td>
                                            ${
                                                certificate.valid_from
                                                ?? '-'
                                            }
                                        </td>


                                        <th>
                                            Validity To
                                        </th>

                                        <td>
                                            ${
                                                certificate.valid_to
                                                ?? '-'
                                            }
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>


                        <!-- EXPERIENCE TITLE -->

                        <div class="competency-result-title">
                            Experience Details
                        </div>
                `;


                // ----------------------------------
                // Experience Records
                // ----------------------------------

                if (records.length > 0) {

                    result += `

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

                                <thead>

                                    <tr>

                                        <th>
                                            S.No
                                        </th>

                                        <th>
                                            Employee Type
                                        </th>

                                        <th>
                                            Licence Category
                                        </th>

                                        <th>
                                            Organisation Name
                                        </th>

                                        <th>
                                            Designation
                                        </th>

                                        <th>
                                            Address
                                        </th>

                                        <th>
                                            Nature of Work
                                        </th>

                                        <th>
                                            Voltage Level
                                        </th>

                                        <th>
                                            Transformer (kVA)
                                        </th>

                                        <th>
                                            From Date
                                        </th>

                                        <th>
                                            To Date
                                        </th>

                                        <th>
                                            Total Experience
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                    `;


                    records.forEach(function (data, index) {

                        result += `

                            <tr>

                                <td>
                                    ${index + 1}
                                </td>


                                <td>
                                    ${data.emp_type ?? '-'}
                                </td>


                                <td>
                                    ${
                                        data.emp_type === 'emp_cate'
                                            ? (data.emp_cate ?? '-')
                                            : '-'
                                    }
                                </td>


                                <td>
                                    ${data.org_name ?? '-'}
                                </td>


                                <td>
                                    ${data.designation ?? '-'}
                                </td>


                                <td>
                                    ${data.org_address ?? '-'}
                                </td>


                                <td>
                                    ${data.nature_work ?? '-'}
                                </td>


                                <td>
                                    ${data.voltage_level ?? '-'}
                                </td>


                                <td>
                                    ${
                                        data.voltage_level != 'up_to_650v'
                                            ? (data.transformer_kva ?? '-')
                                            : '-'
                                    }
                                </td>


                                <td>
                                    ${data.from_date ?? '-'}
                                </td>


                                <td>
                                    ${data.to_date ?? '-'}
                                </td>


                                <td>
                                    ${data.total_exp ?? '-'}
                                </td>

                            </tr>

                        `;

                    });


                    result += `

                                </tbody>

                            </table>

                        </div>

                    `;

                } else {

                    result += `

                        <div class="alert alert-info">

                            Certificate found, but no
                            experience data found.

                        </div>

                    `;

                }


                // Close main box
                result += `

                    </div>

                `;


                // Display result
                $("#competencyVerifyModalBody")
                    .html(result);


            } else {

                // Certificate not found

                $("#competencyVerifyModalBody").html(`

                    <div class="alert alert-warning">

                        ${
                            response.message ??
                            "Certificate not found."
                        }

                    </div>

                `);

            }

        },


        error: function (xhr) {

            console.log(
                "ADMIN VERIFY ERROR:",
                xhr.responseText
            );


            let message =
                "Unable to check competency certificate.";


            // Laravel validation error
            if (
                xhr.responseJSON &&
                xhr.responseJSON.message
            ) {

                message =
                    xhr.responseJSON.message;

            }


            $("#competencyVerifyModalBody").html(`

                <div class="alert alert-danger">

                    ${message}

                </div>

            `);

        },


        complete: function () {

            $button
                .prop("disabled", false)
                .text("Verify");

        }

    });

});



// verify- competency_staff----------------

$(document).on("click", ".verify-staff", function () {

    let $button = $(this);

    let id = $button.data("id");

    let certificate_no =
        $button.attr("data-license") || "";

    let dateof_issue =
        $button.attr("data-dateofissue") || "";

    let valid_from =
        $button.attr("data-validfrom") || "";

    let valid_to =
        $button.attr("data-validto") || "";


    console.log("========== ADMIN CC VERIFY ==========");
    console.log("ID:", id);
    console.log("Certificate No:", certificate_no);
    console.log("Date of Issue:", dateof_issue);
    console.log("Validity From:", valid_from);
    console.log("Validity To:", valid_to);


    // Check required values
    if (
        !certificate_no ||
        !dateof_issue ||
        !valid_from ||
        !valid_to
    ) {

        $("#competencyVerifyModalBody").html(`

            <div class="alert alert-warning">
                Certificate details are incomplete.
            </div>

        `);

        $("#competencyVerifyModal").modal("show");

        return;
    }


    // Show loading
    $("#competencyVerifyModalBody").html(`

        <div class="text-center py-5">

            <div class="spinner-border text-primary mb-3"
                role="status">
            </div>

            <div class="text-info">
                Checking competency certificate...
            </div>

        </div>

    `);


    // Open modal
    $("#competencyVerifyModal").modal("show");


    // Disable button
    $button
        .prop("disabled", true)
        .text("Verifying...");


    $.ajax({

        url: "{{ route('admin.checkCompetencyCertificateadmin') }}",

        type: "POST",

        data: {

            _token: $('meta[name="csrf-token"]').attr("content"),

            certificate_no: certificate_no,

            dateof_issue: dateof_issue,

            valid_from: valid_from,

            valid_to: valid_to

        },


        success: function (response) {

            console.log(
                "ADMIN VERIFY RESPONSE:",
                response
            );


            // Certificate found
            if (
                response.status === true &&
                response.certificate
            ) {

                let certificate =
                    response.certificate;

                let records =
                    response.data || [];


                // ----------------------------------
                // Certificate Details
                // ----------------------------------

                let result = `

                    <div class="competency-result-box">


                        <!-- CERTIFICATE DETAILS -->

                        <div class="table-responsive mb-4">

                            <table class="table table-bordered">

                                <tbody>

                                    <tr>

                                        <th width="20%">
                                            Certificate No
                                        </th>

                                        <td width="30%">
                                            ${
                                                certificate.certificate_no
                                                ?? '-'
                                            }
                                        </td>


                                        <th width="20%">
                                            Date of Issue
                                        </th>

                                        <td width="30%">
                                            ${
                                                certificate.dateof_issue
                                                ?? '-'
                                            }
                                        </td>

                                    </tr>


                                    <tr>

                                        <th>
                                            Validity From
                                        </th>

                                        <td>
                                            ${
                                                certificate.valid_from
                                                ?? '-'
                                            }
                                        </td>


                                        <th>
                                            Validity To
                                        </th>

                                        <td>
                                            ${
                                                certificate.valid_to
                                                ?? '-'
                                            }
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>


                        <!-- EXPERIENCE TITLE -->

                        <div class="competency-result-title">
                            Experience Details
                        </div>
                `;


                // ----------------------------------
                // Experience Records
                // ----------------------------------

                if (records.length > 0) {

                    result += `

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

                                <thead>

                                    <tr>

                                        <th>
                                            S.No
                                        </th>

                                        <th>
                                            Employee Type
                                        </th>

                                        <th>
                                            Licence Category
                                        </th>

                                        <th>
                                            Organisation Name
                                        </th>

                                        <th>
                                            Designation
                                        </th>

                                        <th>
                                            Address
                                        </th>

                                        <th>
                                            Nature of Work
                                        </th>

                                        <th>
                                            Voltage Level
                                        </th>

                                        <th>
                                            Transformer (kVA)
                                        </th>

                                        <th>
                                            From Date
                                        </th>

                                        <th>
                                            To Date
                                        </th>

                                        <th>
                                            Total Experience
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                    `;


                    records.forEach(function (data, index) {

                        result += `

                            <tr>

                                <td>
                                    ${index + 1}
                                </td>


                                <td>
                                    ${data.emp_type ?? '-'}
                                </td>


                                <td>
                                    ${
                                        data.emp_type === 'emp_cate'
                                            ? (data.emp_cate ?? '-')
                                            : '-'
                                    }
                                </td>


                                <td>
                                    ${data.org_name ?? '-'}
                                </td>


                                <td>
                                    ${data.designation ?? '-'}
                                </td>


                                <td>
                                    ${data.org_address ?? '-'}
                                </td>


                                <td>
                                    ${data.nature_work ?? '-'}
                                </td>


                                <td>
                                    ${data.voltage_level ?? '-'}
                                </td>


                                <td>
                                    ${
                                        data.voltage_level != 'up_to_650v'
                                            ? (data.transformer_kva ?? '-')
                                            : '-'
                                    }
                                </td>


                                <td>
                                    ${data.from_date ?? '-'}
                                </td>


                                <td>
                                    ${data.to_date ?? '-'}
                                </td>


                                <td>
                                    ${data.total_exp ?? '-'}
                                </td>

                            </tr>

                        `;

                    });


                    result += `

                                </tbody>

                            </table>

                        </div>

                    `;

                } else {

                    result += `

                        <div class="alert alert-info">

                            Certificate found, but no
                            experience data found.

                        </div>

                    `;

                }


                // Close main box
                result += `

                    </div>

                `;


                // Display result
                $("#competencyVerifyModalBody")
                    .html(result);


            } else {

                // Certificate not found

                $("#competencyVerifyModalBody").html(`

                    <div class="alert alert-warning">

                        ${
                            response.message ??
                            "Certificate not found."
                        }

                    </div>

                `);

            }

        },


        error: function (xhr) {

            console.log(
                "ADMIN VERIFY ERROR:",
                xhr.responseText
            );


            let message =
                "Unable to check competency certificate.";


            // Laravel validation error
            if (
                xhr.responseJSON &&
                xhr.responseJSON.message
            ) {

                message =
                    xhr.responseJSON.message;

            }


            $("#competencyVerifyModalBody").html(`

                <div class="alert alert-danger">

                    ${message}

                </div>

            `);

        },


        complete: function () {

            $button
                .prop("disabled", false)
                .text("Verify");

        }

    });

});

$(document).on('change', '.staff-status-switch', function () {

    let id = $(this).data('id');

    if ($(this).is(':checked')) {

        $('#staffStatusText_' + id)
            .removeClass('bg-danger')
            .addClass('bg-success')
            .text('Verified');

    } else {

        $('#staffStatusText_' + id)
            .removeClass('bg-success')
            .addClass('bg-danger')
            .text('Not Verified');

    }

});

$(document).on('change', '.status-switch', function () {

    let id = $(this).attr('id').replace('status_', '');

    let verifyValue = $(this).is(':checked') ? 1 : 0;

    // Store the current value
    $(this).val(verifyValue);

    if (verifyValue === 1) {

        $('#statusText_' + id)
            .removeClass('bg-danger')
            .addClass('bg-success')
            .text('Correct');

    } else {

        $('#statusText_' + id)
            .removeClass('bg-success')
            .addClass('bg-danger')
            .text('Incorrect');
    }

    console.log('Checklist ID:', id);
    console.log('Verify Value:', verifyValue);
});


</script>


