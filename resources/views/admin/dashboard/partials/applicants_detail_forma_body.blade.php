                <div class="col-lg-12 layout-spacing">
                    <div class="statbox widget ">
                        {{-- @php
                            var_dump($applicant->appl_type); exit;
                        @endphp --}}
                        <div
                            class="widget-header applicant_details {{ trim($applicant->appl_type) == 'D' ? 'digitization-header' : '' }}"">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <h4>Application Id : <span> {{ $applicant->application_id }}</span> Applicant Name :
                                        <span style="color:#098501;">{{ $applicant->applicant_name }}</span> Applied For
                                        : <span style="color:#098501;"> {{ $applicant->form_name }} | License
                                            {{ $applicant->license_name }}</span>
                                        @if (trim($applicant->appl_type) == 'A')
                                            Parent Application ID: <span
                                                style="color:#098501;">{{ $applicant->old_application }}
                                            </span>
                                        @endif
                                    </h4>
                                </div>

                                @if (trim($applicant->appl_type) == 'D')

                                    @if (!empty($cl_digitization))
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
                                                            <td>{{ \Carbon\Carbon::parse($cl_digitization->fissue)->format('d-m-Y') }}
                                                            </td>
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
                                                                <a href="{{ asset($cl_digitization->cl_doc) }}"
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
                                    <h4>View Application Details</h4>
                                </div>
                            </div>
                        </div>
                        <div class="widget-content widget-content-area">
                            <div class="simple-tab">
                                <ul class="nav nav-tabs nav-fill" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="home-tab" data-bs-toggle="tab"
                                            data-bs-target="#home-tab-pane" type="button" role="tab"
                                            aria-controls="home-tab-pane" aria-selected="true">Basic and Ownership
                                            Details</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="contact-tab" data-bs-toggle="tab"
                                            data-bs-target="#contact-tab-pane" type="button" role="tab"
                                            aria-controls="contact-tab-pane" aria-selected="false">Staff and Bank
                                            Details</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="other-tab" data-bs-toggle="tab"
                                            data-bs-target="#other-tab-panel" type="button" role="tab"
                                            aria-controls="other-tab-panel" aria-selected="false">Other Details and
                                            Address Proof</button>
                                    </li>

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="equipment-tab" data-bs-toggle="tab"
                                            data-bs-target="#equipment-tab-panel" type="button" role="tab"
                                            aria-controls="equipment-tab-panel" aria-selected="false">Equipment /
                                            Instruments List</button>
                                    </li>


                                    @if (trim($applicant->appl_type) != 'D')
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="payment-tab" data-bs-toggle="tab"
                                                data-bs-target="#payment-tab-panel" type="button" role="tab"
                                                aria-controls="payment-tab-panel" aria-selected="false">
                                                Payment Details
                                            </button>
                                        </li>
                                    @endif

                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="profile-tab" data-bs-toggle="tab"
                                            data-bs-target="#profile-tab-pane" type="button" role="tab"
                                            aria-controls="profile-tab-pane" aria-selected="false">Checklist
                                            Details</button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel"
                                        aria-labelledby="home-tab" tabindex="0">
                                        <div class="row mt-3 ">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row">
                                                        <div class="col-lg-3">


                                                            <p class="text-info"><strong>1. Name of the
                                                                    Applicant:</strong></p>

                                                            <p class="text-info"><strong>2. Business address:</strong>
                                                            </p>

                                                        </div>
                                                        <div class="col-lg-8">


                                                            <p>{{ $applicant->applicant_name }}</p>

                                                            <p>{{ $applicant->business_address }}</p>


                                                        </div>

                                                    </div>


                                                </div>

                                            </div>





                                            <p class="mt-4 mb-2 fw-bold text-info">3. Ownership Type - Proprietor /
                                                Partners / Directors Details</p>
                                            <div class="row">
                                                <div class="col-lg-3">


                                                    <p class="text-info"><strong>Type of Ownership:</strong></p>



                                                </div>
                                                <div class="col-lg-3">

                                                    <p>
                                                        @if ($applicant->application_ownershiptype == 'pr')
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
                                                @if (
                                                    $applicant->application_ownershiptype == 'pt' ||
                                                        $applicant->application_ownershiptype == 'pvt' ||
                                                        $applicant->application_ownershiptype == 'ltd')
                                                    <div class="col-lg-3">

                                                        <a href="{{ asset($applicant->ownership_doc) }}"
                                                            class="file_view fw-bold" target="_blank"><i
                                                                class="fa fa-file-pdf-o"></i> View Ownership
                                                            Document</a>

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

                                                            $sortedProprietors = collect($cl_ownership_table)->sortBy(
                                                                function ($item) {
                                                                    return match ($item->ownership_type) {
                                                                        'pr' => 1,
                                                                        'pt' => 2,
                                                                        'dr' => 3,
                                                                        default => 4,
                                                                    };
                                                                },
                                                            );
                                                        @endphp
                                                        @forelse ($sortedProprietors as $proprietor)
                                                            <tr>
                                                                <td>
                                                                    <!-- {{ $proprietor->id }} -->
                                                                    @if ($proprietor->ownership_type == 'pr')
                                                                        Proprietor
                                                                    @elseif($proprietor->ownership_type == 'pt')
                                                                        Partner
                                                                    @else
                                                                        Director
                                                                    @endif

                                                                </td>
                                                                <td>{{ $proprietor->proprietor_name }} </td>
                                                                <td> {{ $proprietor->fathers_name }}</td>
                                                                <td>{{ \Carbon\Carbon::parse($proprietor->dob)->format('d-m-Y') }},
                                                                    {{ $proprietor->age }}
                                                                    <a href="{{ asset($proprietor->age_proof) }}"
                                                                        class="file_view fw-bold" target="_blank"><i
                                                                            class="fa fa-file-pdf-o"></i> </a>
                                                                </td>
                                                                <td>{{ $proprietor->proprietor_address }} </td>
                                                                <td> {{ $proprietor->qualification }},
                                                                    {{ $proprietor->qualification_text }} <a
                                                                        href="{{ asset($proprietor->educational_proof) }}"
                                                                        class="file_view fw-bold" target="_blank"><i
                                                                            class="fa fa-file-pdf-o"></i> </a></td>

                                                                <td> {{ $proprietor->present_business }}</td>
                                                                <td>
                                                                    @if (!empty($proprietor->competency_certificate_number))
                                                                        CC No :
                                                                        {{ $proprietor->competency_certificate_number }},<br>

                                                                        CC First Issue :
                                                                        {{ $proprietor->competency_certificate_first_issue
                                                                            ? \Carbon\Carbon::parse($proprietor->competency_certificate_first_issue)->format('d-m-Y')
                                                                            : '' }},<br>

                                                                        CC Validity From :
                                                                        {{ $proprietor->competency_certificate_validity_from
                                                                            ? \Carbon\Carbon::parse($proprietor->competency_certificate_validity_from)->format('d-m-Y')
                                                                            : '' }},<br>

                                                                        CC Validity To :
                                                                        {{ $proprietor->competency_certificate_validity_to
                                                                            ? \Carbon\Carbon::parse($proprietor->competency_certificate_validity_to)->format('d-m-Y')
                                                                            : '' }}

                                                                        <br><br>

                                                                        <button type="button"
                                                                            class="btn btn-primary verify-cert_ownership"
                                                                            data-id="{{ $proprietor->id }}"
                                                                            data-license="{{ $proprietor->competency_certificate_number }}"
                                                                            data-dateofissue="{{ $proprietor->competency_certificate_first_issue
                                                                                ? \Carbon\Carbon::parse($proprietor->competency_certificate_first_issue)->format('d-m-Y')
                                                                                : '' }}"
                                                                            data-validfrom="{{ $proprietor->competency_certificate_validity_from
                                                                                ? \Carbon\Carbon::parse($proprietor->competency_certificate_validity_from)->format('d-m-Y')
                                                                                : '' }}"
                                                                            data-validto="{{ $proprietor->competency_certificate_validity_to
                                                                                ? \Carbon\Carbon::parse($proprietor->competency_certificate_validity_to)->format('d-m-Y')
                                                                                : '' }}">

                                                                            Verify
                                                                        </button>
                                                                    @endif

                                                                    <div id="verify-result-{{ $proprietor->id }}">
                                                                    </div>
                                                                </td>


                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="4" class="text-center">No Proprietor
                                                                    Details details available.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>



                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="row mt-2">
                                                        <div class="col-lg-8">
                                                            <p class=" text-info"><strong>4. Whether any application
                                                                    for Contractor/s licence was made previously? If so,
                                                                    details thereof:</strong></p>
                                                        </div>
                                                        <div class="col-lg-4">
                                                            <p>{{ strtoupper($applicant->previous_contractor_license) }}
                                                                @if ($applicant->previous_contractor_license === 'yes')
                                                                    - {{ $applicant->previous_application_number }},
                                                                    {{ \Carbon\Carbon::parse($applicant->previous_validity_first_issue)->format('d-m-Y') }},
                                                                    {{ \Carbon\Carbon::parse($applicant->previous_validity_from)->format('d-m-Y') }}
                                                                    ,
                                                                    {{ \Carbon\Carbon::parse($applicant->previous_validity_to)->format('d-m-Y') }}





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
                                    <?php //var_dump($workflows->first()->is_verified);die;
                                    ?>
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
                                                            @php
                                                                $savedChecked =
                                                                    (int) ($checkedList_1[$item->id] ?? 0) === 1;
                                                                $savedVerify = array_key_exists($item->id, $verifyList)
                                                                    ? (int) $verifyList[$item->id]
                                                                    : 1;
                                                            @endphp
                                                            <tr>

                                                                <td>

                                                                    <label for="checklist_{{ $item->id }}">
                                                                        {{ $item->checklist_name }}
                                                                    </label>

                                                                    <input type="hidden"
                                                                        name="check_id[{{ $item->id }}]"
                                                                        value="{{ $item->id }}">

                                                                    <input type="hidden" name="cert_name"
                                                                        value="{{ $applicant->license_name }}">

                                                                </td>

                                                                <td class="text-center checklist_chk">

                                                                    <input type="checkbox" class="form-check-input"
                                                                        id="checklist_{{ $item->id }}"
                                                                        name="checklists[{{ $item->id }}]"
                                                                        value="1"
                                                                        {{ $savedChecked ? 'checked' : '' }}>
                                                                </td>

                                                                <td class="text-center">

                                                                    <div
                                                                        class="d-flex align-items-center justify-content-center gap-2">

                                                                        <span id="statusText_{{ $item->id }}"
                                                                            class="badge {{ $savedVerify === 1 ? 'bg-success' : 'bg-danger' }}">

                                                                            {{ $savedVerify === 1 ? 'Correct' : 'Incorrect' }}

                                                                        </span>

                                                                        <div class="form-check form-switch">

                                                                            <input
                                                                                class="form-check-input status-switch"
                                                                                type="checkbox"
                                                                                id="status_{{ $item->id }}"
                                                                                name="status[{{ $item->id }}]"
                                                                                value="{{ $savedVerify }}"
                                                                                {{ $savedVerify === 1 ? 'checked' : '' }}>

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
                                    <p class="mt-4 mb-2 fw-bold text-info">5A . QC Staff Details</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Staff Name</th>
                                                    <th>Competency Certificate Number</th>
                                                    <th>Certificate First Issue , <br>Validity From<br> Validity To</th>
                                                    <th>Attachments</th>
                                                    <th>Verify</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($qcQscStaffs_details as $index => $staff)
                                                    <tr>

                                                        <td>{{ $staff->staff_name }}</td>
                                                        <td>{{ $staff->staff_cc_no }}</td>
                                                        <td>{{ \Carbon\Carbon::parse($staff->staff_cc_first_issue)->format('d-m-Y') }},<br>
                                                            {{ \Carbon\Carbon::parse($staff->staff_cc_validity_from)->format('d-m-Y') }},<br>{{ \Carbon\Carbon::parse($staff->staff_cc_validity_to)->format('d-m-Y') }}
                                                        </td>


                                                        <td>
                                                            <div class="file-link">
                                                                <a href="{{ asset($staff->app_doc) }}"
                                                                    target="_blank">Appointment Letter</a>
                                                                <br>
                                                                <a href="{{ asset($staff->cons_doc) }}"
                                                                    target="_blank">Consent Letter</a>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">

                                                            @if ($staff->staff_category != 'OTHERS')
                                                                @php
                                                                    $verifyFlag = isset(
                                                                        $staffVerifyList[$staff->staff_cc_no],
                                                                    )
                                                                        ? (int) $staffVerifyList[$staff->staff_cc_no]
                                                                        : 0;
                                                                @endphp

                                                                <div
                                                                    class="d-flex align-items-center justify-content-center gap-2">

                                                                    <span id="staffStatusText_{{ $staff->id }}"
                                                                        class="badge {{ $verifyFlag == 1 ? 'bg-success' : 'bg-danger' }}">
                                                                        {{ $verifyFlag == 1 ? 'Correct' : 'Incorrect' }}
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
                                                        <td colspan="3" class="text-center">No Staffs available.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="mt-4 mb-2 fw-bold text-info">5B. Other Staff Details</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>

                                                    <th>Staff Category </th>
                                                    <th>Staff Name,CC Number</th>
                                                    <th>CC First Issue , <br>Validity From<br> Validity To</th>
                                                    <th class="text-center">Verify</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $otherstaffdetails = collect($otherstaffdetails)
                                                        ->sortBy(function ($staff) {
                                                            $order = [
                                                                'B' => 1,
                                                                'C' => 2,
                                                                'OTHERS' => 3,
                                                            ];

                                                            return sprintf(
                                                                '%d-%010d',
                                                                $order[$staff->staff_category ?? ''] ?? 4,
                                                                (int) ($staff->row_index ?? 0),
                                                            );
                                                        })
                                                        ->values();
                                                @endphp
                                                @forelse ($otherstaffdetails as $index => $staff)
                                                    <tr>


                                                        <td>{{ $staff->staff_category }}</td>

                                                        <td>

                                                            @if ($staff->staff_category == 'OTHERS')
                                                                {{ $staff->staff_designation }}
                                                            @else
                                                                {{ $staff->staff_name }}<br>
                                                                {{ $staff->staff_cc_no }}<br>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            {{ $staff->staff_cc_first_issue ? \Carbon\Carbon::parse($staff->staff_cc_first_issue)->format('d-m-Y') : '' }},<br>


                                                                {{ $staff->staff_cc_validity_from ? \Carbon\Carbon::parse($staff->staff_cc_validity_from)->format('d-m-Y') : '' }},<br>


                                                                {{ $staff->staff_cc_validity_to ? \Carbon\Carbon::parse($staff->staff_cc_validity_to)->format('d-m-Y') : '' }}
                                                        </td>

                                                        <td class="text-center">

                                                            @if ($staff->staff_category != 'OTHERS')
                                                                @php
                                                                    $verifyFlag = isset(
                                                                        $staffVerifyList[$staff->staff_cc_no],
                                                                    )
                                                                        ? (int) $staffVerifyList[$staff->staff_cc_no]
                                                                        : 0;
                                                                @endphp

                                                                <div
                                                                    class="d-flex align-items-center justify-content-center gap-2">

                                                                    <span id="staffStatusText_{{ $staff->id }}"
                                                                        class="badge {{ $verifyFlag == 1 ? 'bg-success' : 'bg-danger' }}">
                                                                        {{ $verifyFlag == 1 ? 'Correct' : 'Incorrect' }}
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
                                                        <td colspan="3" class="text-center">No Staffs available.
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- ---------------------------License History--------------- -->

                                    <div class="modal fade inputForm-modal" id="showlicense" tabindex="-1"
                                        aria-labelledby="inputFormModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-xl modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">License History</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-hidden="true">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                                            height="24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" class="feather feather-x">
                                                            <line x1="18" y1="6" x2="6"
                                                                y2="18"></line>
                                                            <line x1="6" y1="6" x2="18"
                                                                y2="18"></line>
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
                                                            <tr class="">
                                                                <td colspan="5" class="text-muted text-center">No
                                                                    data available</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ---------------------------License End--------------- -->

                                    <div class="row mt-3">

                                        
                                            <div class="col-lg-12">
                                                <p class="text-info"><strong>6. Bank Solvency Certificate
                                                        Details</strong></p>
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
                                                <p>{{ format_date($banksolvency->bank_validity) ?? '' }}</p>
                                            </div>
                                            <div class="col-lg-4">
                                                @if ($showbankWarning)
                                                    <p class="text-left fw-bold text-danger">Bank validity period is
                                                        less than EA licence period</p>
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
                                                <a href="{{ asset($banksolvency->bank_doc) }}"
                                                    class="file_view fw-bold" target="_blank"><i
                                                        class="fa fa-file-pdf-o"></i> View </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>


                                <div class="tab-pane fade" id="other-tab-panel" role="tabpanel"
                                    aria-labelledby="other-tab" tabindex="0">

                                    <div class="row mt-3">


                                        <div class="col-lg-8">
                                            <p>
                                                <strong>
                                                    7. Has the applicant or any of his/her staff referred to under item
                                                    6, been
                                                    at any time convicted in any court of law or punished by any other
                                                    authority for criminal offences
                                                </strong>
                                            </p>
                                        </div>

                                        <div class="col-lg-2">
                                            <p>{{ strtoupper($applicant->criminal_offence ?? '') }}</p>
                                        </div>

                                        @if (strtoupper(trim($applicant->criminal_offence ?? '')) == 'YES')
                                            @php
                                                $criminaloffence = $attachments_cl
                                                    ->where('type', 'criminaloffence')
                                                    ->first();
                                            @endphp

                                            <div class="col-lg-2">
                                                @if ($criminaloffence)
                                                    <a href="{{ asset($criminaloffence->file_doc) }}"
                                                        class="file_view fw-bold" target="_blank">
                                                        <i class="fa fa-file-pdf-o"></i> View Document
                                                    </a>
                                                @else
                                                    <span class="text-muted">Document not uploaded</span>
                                                @endif
                                            </div>
                                        @endif


                                    </div>

                                    <div class="row mt-3">


                                        <div class="col-lg-8">
                                            <p><strong>8. The name of the person/persons whom the applicant has
                                                    authorised to sign if any, on his/their behalf in case of Proprietor
                                                    or Partnership concern</strong></p>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Name of Signatory</th>
                                                            <th>Age of Signatory</th>
                                                            <th>Qualification of Signatory</th>
                                                            <th>Designation</th>
                                                            <th>Specimen Signature</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody>
                                                        @if ($authoritysignatory->count() > 0)

                                                            @foreach ($authoritysignatory as $index => $authority)
                                                                <tr>
                                                                    <td>
                                                                        {{ $authority->name_of_authorised_to_sign ?? '-' }}
                                                                    </td>

                                                                    <td>
                                                                        {{ $authority->age_of_authorised_to_sign ?? '-' }}
                                                                    </td>

                                                                    <td>
                                                                        {{ $authority->qualification_of_authorised_to_sign ?? '-' }}
                                                                    </td>

                                                                    <td>
                                                                        {{ $authority->designation_of_authorised_to_sign ?? '-' }}
                                                                    </td>

                                                                    <td>
                                                                        @if (!empty($authority->specimen_sign))
                                                                            <a href="{{ asset($authority->specimen_sign) }}"
                                                                                target="_blank"
                                                                                class="file_view fw-bold">
                                                                                <i class="fa fa-file-pdf-o"
                                                                                    style="color:red;"></i>
                                                                                View Document
                                                                            </a>
                                                                        @else
                                                                            <span class="text-muted">No document
                                                                                uploaded</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="5" class="text-center">
                                                                    No signatory records found.
                                                                </td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>






                                    </div>



                                    <div class="row mt-3">

                                        
                                            <div class="col-lg-12">
                                                <p class="text-info"><strong>9. Address Proof </strong></p>
                                            </div>
                                        

                                        <div class="row">
                                            <div class="col-lg-3">
                                                <p><strong>Type of Address Proof</strong></p>
                                            </div>
                                            <div class="col-lg-4">
                                                <p>{{ $addressproof->type_doc ?? '' }}</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <p><strong>GST/ Rental Aggrement/Others No</strong></p>
                                            </div>
                                            <div class="col-lg-4">
                                                <p>{{ $addressproof->addressproofno ?? '' }}</p>
                                            </div>

                                        </div>


                                        <div class="row">
                                            <div class="col-lg-3">
                                                <p><strong>Address Proof</strong></p>
                                            </div>
                                            <div class="col-lg-6">
                                                <a href="{{ asset($addressproof->file_doc) }}"
                                                    class="file_view fw-bold" target="_blank"><i
                                                        class="fa fa-file-pdf-o"></i> View </a>
                                            </div>
                                        </div>

                                    </div>
                                </div>



                                <!-- ----------------equipment-tab--------------------- -->
                                <div class="tab-pane fade" id="equipment-tab-panel" role="tabpanel"
                                    aria-labelledby="equipment-tab" tabindex="0">

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
                                                            <th>S.No</th>
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

                                                        @foreach ($equiplist as $index => $equip)
                                                            @if ($equip->equip_licence_name == $licenceId)
                                                                @php
                                                                    $userEquip = $equipmentMap[$equip->id] ?? null;

                                                                    $serial = $userEquip->serial_no ?? '';
                                                                    $model = $userEquip->model_no ?? '';
                                                                    $date = $userEquip->dateoftest ?? '';

                                                                    $testfile = $userEquip->testreport_file ?? '';

                                                                    $purchasefile = $userEquip->purchasereport_file;
                                                                @endphp

                                                                <tr>
                                                                    <td class="text-center">{{ $loop->iteration }}
                                                                    </td>

                                                                    <td>
                                                                        {{ $equip->equip_name }}

                                                                    </td>

                                                                    <td>
                                                                        {{ $equip->equipment_type }}

                                                                    </td>

                                                                    <td>{{ strtoupper($serial) }}</td>

                                                                    <td>{{ strtoupper($model) }}</td>


                                                                    <td><a href="{{ asset($testfile) }}"
                                                                            target="_blank"
                                                                            class="file_view fw-bold"><i
                                                                                class="fa fa-file-pdf-o"></i> View </a>
                                                                    </td>
                                                                    <td><a href="{{ asset($purchasefile) }}"
                                                                            target="_blank"
                                                                            class="file_view fw-bold"><i
                                                                                class="fa fa-file-pdf-o"></i> View </a>
                                                                    </td>

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
                                    </div>
                                </div>
                                <!-- -------------------------------- -->

                                <div class="tab-pane fade" id="payment-tab-panel" role="tabpanel"
                                    aria-labelledby="payment-tab" tabindex="0">
                                    <div class="row mt-3">

                                        <div class="col-lg-6">
                                            <div class="row">
                                                <h6 class="fw-bold text-primary">Payment Details</h6>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-lg-6">
                                                    <p><strong>Application Type</strong></p>
                                                </div>
                                                <div class="col-lg-6">
                                                    <p>
                                                        <strong>
                                                            {{ match (trim($applicant->appl_type ?? '')) {
                                                                'N' => 'New',
                                                                'R' => 'Renewal',
                                                                'D' => 'Digitisation',
                                                                'A' => 'Alteration',
                                                                default => $applicant->appl_type ?? 'NA',
                                                            } }}
                                                        </strong>
                                                    </p>
                                                </div>


                                                <div class="col-lg-6">
                                                    <p><strong>Appication Fees</strong></p>
                                                </div>
                                                <div class="col-lg-6">
                                                    <p>{{ $applicant->amount }}</p>
                                                </div>

                                                <div class="col-lg-6">
                                                    <p><strong>Total Amount</strong></p>
                                                </div>
                                                <div class="col-lg-6">

                                                    <p>{{ $applicant->amount }}.00</p>

                                                </div>

                                                @if ($applicant->late_fee > 0)
                                                    <div class="col-lg-6">
                                                        <p><strong>Late fees</strong></p>
                                                    </div>
                                                    <div class="col-lg-6">
                                                        <p>{{ $applicant->late_fee }}</p>
                                                    </div>
                                                @endif


                                                <div class="col-lg-6">
                                                    <p><strong>Date of Application</strong></p>
                                                </div>
                                                <div class="col-lg-6">
                                                    <p>{{ format_date_other($applicant->dt_submit) }}</p>
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
                                                    <p class="badge badge-success">
                                                        {{ strtoupper($applicant->payment_status) ?? 'NA' }}</p>
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
                                                    <p>{{ $applicant->amount }}</p>
                                                </div>
                                                <div class="col-lg-6">
                                                    <p><strong>Payment mode:</strong></p>
                                                </div>
                                                <div class="col-lg-6">
                                                    <P>{{ $applicant->payment_mode }}</P>
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
