@include('admin.include.top')
@include('admin.include.header')
@include('admin.include.navbar')
@include('admin.dashboard.partials.applicants_detail_forma_styles')
<div id="content" class="main-content">
    <div class="layout-px-spacing">
        <div class="middle-content container-xxl p-0">
            <div class="secondary-nav">
                <div class="breadcrumbs-container" data-page-heading="Analytics">
                    <header class="header navbar navbar-expand-sm">
                        <a href="javascript:void(0);" class="btn-toggle sidebarCollapse" data-placement="bottom">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="feather feather-menu">
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
                @include('admin.dashboard.partials.applicants_detail_forma_body')

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
                                        <label class="switch-label mb-0 fw-bold text-end" for="Queryswitch">If you
                                            have any queries</label>
                                        <div
                                            class="switch form-switch-custom switch-inline form-switch-primary form-switch-custom inner-text-toggle">
                                            <div class="input-checkbox">
                                                <span class="switch-chk-label label-left">Yes</span>
                                                <input class="switch-input" type="checkbox" id="Queryswitch"
                                                    role="switch">
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



                                <textarea class="form-control placement-top" id="remarks" name="remarks" rows="4" cols="50"
                                    maxlength="250"></textarea>

                                <span id="remarks_error" class="text-danger small"></span>
                            </div>
                            <div class="modal-footer mt-2" style="justify-content: center;">

                                @php
                                    $role = Auth::user()->name; // Current role name
                                    $workflow = [
                                        'Supervisor' => 'Assistant Secretary',
                                        'Supervisor2' => 'Assistant Secretary',
                                        'Assistant Secretary' => 'Secretary',
                                        'Secretary' => 'President',
                                        'President' => null,
                                    ];

                                @endphp

                                @if ($role == 'Supervisor' || $role == 'Supervisor2')

                                    {{-- Forward to Assistant Secretary --}}

                                    <button class="btn btn-success" id="forwardbtn">
                                        Forward to {{ $workflow[$role] }}
                                    </button>
                                @elseif ($role == 'Assistant Secretary')
                                    <div class="row justify-content-center">
                                        <div class="col-12">
                                            <div
                                                class="d-flex flex-wrap justify-content-center align-items-center gap-2">
                                                {{-- Forward to Secretary --}}
                                                <button class="btn btn-success" id="forwardbtn"
                                                    data-bs-toggle="modal" data-bs-target="#declarationModal">
                                                    Forward to {{ $workflow[$role] }}
                                                </button>

                                            </div>
                                        </div>
                                    </div>
                                @elseif ($role == 'Secretary')
                                    <button class="btn btn-success" id="confirmForwardPres">
                                        Forward to {{ $workflow[$role] }}
                                    </button>


                                    @if (trim($applicant->appl_type) != 'D')
                                        {{-- <button id="confirmReturnBtn" class="btn btn-warning">
                                        Return to Supervisor
                                    </button> --}}

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
                            <button class="btn btn-success" id="forwardbtn" style="margin-right: 20px;" data-bs-toggle="modal" data-bs-target="#declarationModal" disabled>Forward To {{ $applicant->application_status == 'RE' ? 'Secretary' : 'Assistant Secretary' }}</button>
                            <button class="btn btn-warning " style="margin-right: 20px;" data-bs-dismiss="modal">On Hold</button>
                            {{-- <button type="button" class="btn btn-danger">Reject</button> --}}
                        </div> -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- ------------------------------------ -->
            @if ($role == 'Secretary' || $role == 'President')
                <div class="col-lg-6 layout-spacing" id="return_section" style="display:none;">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <div class="row">
                                <div class="col-xl-12 officers_section col-md-12 col-sm-12 col-12">
                                    <h4 class=" text-white">Reason for Return Application to Applicant</h4>
                                </div>
                            </div>
                            <div class="form-check mt-2">
                                <input type="checkbox" id="basic_details" name="return_reasons[]"
                                    value="basic_details" class="form-check-input return-checkbox">
                                <label class="form-check-label" for="basic_details"> 1 & 2) Applicant Basic Detail
                                    (Name & Address)</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="ownership_details" name="return_reasons[]"
                                    value="ownership_details" class="form-check-input return-checkbox">
                                <label class="form-check-label" for="ownership_details">3) Applicant Ownership Details
                                </label>
                            </div>




                            <div class="form-check">
                                <input type="checkbox" id="qc_staff" name="return_reasons[]" value="qc_staff"
                                    class="form-check-input return-checkbox">
                                <label class="form-check-label" for="qc_staff">4) QC Staff Issue </label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" id="staff_details" name="return_reasons[]"
                                    value="staff_details" class="form-check-input return-checkbox">
                                <label class="form-check-label" for="staff_details">5) Other Staff Issue</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="bank_solvency" name="return_reasons[]"
                                    value="bank_solvency" class="form-check-input return-checkbox">
                                <label class="form-check-label" for="bank_solvency">6) Bank Solvency </label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" id="atachment_points" name="return_reasons[]"
                                    value="atachment_points" class="form-check-input return-checkbox">
                                <label class="form-check-label" for="bank_solvency">7 and 8) Attachments Points
                                </label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" id="address_proof" name="return_reasons[]"
                                    value="address_proof" class="form-check-input return-checkbox">
                                <label class="form-check-label" for="address_proof">9) Address Proof </label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" id="equipments_details" name="return_reasons[]"
                                    value="equipments_details" class="form-check-input return-checkbox">
                                <label class="form-check-label" for="equipments_details">10) Equipments Details
                                </label>
                            </div>

                            <div id="checkbox_error" class="text-danger mt-2 fw-bold" style="display:none;"></div>

                            <div class="">
                                <h4>Remarks For Return (Optional)</h4>



                                <textarea class="form-control" name="remarks_return" id="remarks_return" rows="4" cols="50"
                                    maxlength="300"></textarea>
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

                                        <div
                                            class="t-dot
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
                                                @if ($row->processed_by == 'AP')
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
                                                @if ($row->query_status == 'P')
                                                    <p class="text-danger">Note: Query raised by
                                                        {{ $row->processed_by }} (
                                                        @php
                                                            $queries = $row->queries;

                                                            if (is_string($queries)) {
                                                                $queries = json_decode($queries, true);
                                                            }
                                                        @endphp

                                                        @if (!empty($queries) && is_array($queries))
                                                            {{ implode(', ', $queries) }}
                                                        @endif
                                                        )
                                                    </p>
                                                @endif
                                            @endif


                                            <p class="t-meta-time">
                                                @if ($row->appl_status == 'RET')
                                                    Returned to Applicant
                                                    <br>
                                                    Remarks: {{ $row->remarks }}
                                                    <br>

                                                    @php
                                                        $reasons = json_decode($row->return_reason, true);
                                                    @endphp



                                                    <span class="text-return">
                                                        @if (!empty($reasons))
                                                            Return Reason :

                                                            {{ collect($reasons)->map(fn($r) => Str::upper($r))->implode(', ') }}
                                                            <br>

                                                            Return Remarks :
                                                            @if ($row->remarks_return)
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
                                                        @if ($row->processed_by !== 'AP')
                                                            @if ($row->appl_status == 'RE')
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
                                        <p class="t-meta-time">Form: {{ $user_entry->form_name }}, License:
                                            {{ $user_entry->license_name }}</p>
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
    <div id="queryToast" class="toast align-items-center text-white bg-danger border-0" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                You have raised a query, so you must select at least one query type.
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
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
        confirmApproval.change(function() {
            approveButton.prop('disabled', !this.checked);
        });


        var checkPresident = $('#confirmPresident');

        confirmForwardPres = $("#confirmForwardPres");

        checkPresident.change(function() {
            confirmForwardPres.prop('disabled', !this.checked);
        });



        // Initially disable Reset All
        resetAllBox.prop('disabled', true);

        var checklistBoxes = $('#specific-class input[name^="checklists"]');

        function syncForwardButton() {
            var total = checklistBoxes.length;
            var checkedCount = checklistBoxes.filter(':checked').length;
            var allChecked = total === 0 || checkedCount === total;
            forwardbtn.prop('disabled', !allChecked);
        }

        syncForwardButton();

        checkAllBox.change(function() {
            if ($(this).prop('checked')) {
                individualCheckboxes.prop('checked', true);
                resetAllBox.prop('disabled', false).prop('checked', false); // Enable Reset All
            } else {
                individualCheckboxes.prop('checked', false);
                resetAllBox.prop('disabled', true).prop('checked', false); // Disable Reset All
            }
            syncForwardButton();
        });

        // "Reset All" functionality
        resetAllBox.change(function() {
            if ($(this).prop('checked')) {
                individualCheckboxes.prop('checked', false);
                checkAllBox.prop('checked', false); // Uncheck Check All
                checkAllBox.prop('disabled', false); // Enable Check All
                resetAllBox.prop('disabled', true); // Disable Reset All after use
                syncForwardButton();
            }
        });

        // If any individual checkbox is manually unchecked, uncheck "Check All"
        individualCheckboxes.change(function() {
            if (checklistBoxes.length > 0 && checklistBoxes.filter(':checked').length === checklistBoxes
                .length) {
                checkAllBox.prop('checked', true);
            } else {
                checkAllBox.prop('checked', false);
            }
            syncForwardButton();
        });


        approveButton.click(function() {

            var oldapplicationId = @json($applicant->old_application);

            var appl_type = @json($applicant->appl_type);
            var form_name = @json($applicant->form_name);

            var licensename = @json($applicant->license_name);
            var applicationId = @json($applicant->application_id);
            var processedBy = @json(Auth::user()->name);

            var old_issuedat = @json($old_issued_at_date);

            var returnapp = @json($applicant->return_flag);

            // alert(old_issuedat);
            var remarks = $("#remarks").val().trim();
            $("#remarks_error").text("");

            if (remarks === "") {
                $("#remarks_error").text("Remarks is required.");
                $("#remarks").focus();
                return;
            }

            var firstCertNo = @json($qcQscStaffs_details->first()->staff_cc_no);
            // alert(firstStaff);
            // var firstCertNo = firstStaff.cc_number;
            var qc_validity_date = @json($qcQscStaffs_details->first()->staff_cc_validity_to);

            // alert(qc_validity_date);
            var bank_validity = @json($banksolvency->bank_validity);

            var role_id = @json(Auth::user()->roles_id);



            var checklistStatus = [];

            var maxQcCertNo = @json($maxQcCertNo);

            var qc_validity_date = @json($maxQcValidityDate);

            // alert(qc_validity_date);

            $("#specific-class input[name='checklists[]']:checked").each(function() {
                checklistStatus.push($(this).val());
            });

            let checklists = {};
            let status = {};
            let check_id = {};


            $("#specific-class tbody tr").each(function() {

                let checkbox = $(this).find("input[name^='checklists']");
                let switchBtn = $(this).find("input[name^='status']");
                let checkid = $(this).find("input[name^='check_id']");

                let name = checkbox.attr("name");

                if (!name) {
                    return; // skip this row
                }

                let match = name.match(/\d+/);

                if (!match) {
                    return; // skip if no number found
                }

                let id = match[0];

                checklists[id] = checkbox.is(":checked") ? 1 : 0;
                status[id] = $(this).find('.status-switch').prop('checked') ? 1 : 0;
                check_id[id] = checkid.val();
            });


            let staffVerification = [];

            $('.staff-status-switch').each(function() {

                let staffId = $(this).data('id');

                let verifyFlag = $(this).is(':checked') ? 1 : 0;

                staffVerification.push({
                    staff_id: staffId,
                    verify_flag: verifyFlag
                });

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
                    returnapp: returnapp,
                    checklists: checklists,
                    status: status,
                    check_id: check_id,


                },
                success: function(res) {

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
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                                    "content")
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
                                old_issuedat: old_issuedat,
                                role_id: role_id,


                                staff_verification: JSON.stringify(staffVerification),
                                check_id: check_id,
                                checklists: checklists,
                                status: status
                            },
                            success: function(response) {

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


        confirmForwardPres.click(function() {
            var applicationId = @json($applicant->application_id);
            var role_id = @json(Auth::user()->roles_id);
            var forwardedTo = @json($nextForwardUser->roles_id);
            var processedBy = @json(Auth::user()->name);
            var role = @json($nextForwardUser->name);
            var remarks = $("#remarks").val().trim();
            $("#remarks_error").text("");

            if (remarks === "") {
                $("#remarks_error").text("Remarks is required");
                $("#remarks").focus();
                return;
            }
            var queryswitch = $("#Queryswitch").prop("checked");
            var checkboxStatus = "Yes";

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

            $("#specific-class tbody tr").each(function() {

                let checkbox = $(this).find("input[name^='checklists']");
                let switchBtn = $(this).find("input[name^='status']");
                let checkid = $(this).find("input[name^='check_id']");

                let name = checkbox.attr("name");

                if (!name) {
                    return; // skip this row
                }

                let match = name.match(/\d+/);

                if (!match) {
                    return; // skip if no number found
                }

                let id = match[0];

                checklists[id] = checkbox.is(":checked") ? 1 : 0;
                status[id] = $(this).find('.status-switch').prop('checked') ? 1 : 0;
                check_id[id] = checkid.val();
            });

            Swal.fire({
                title: "Declaration",
                text: 'I confirm that have been verified by me as a secretary.',
                showCancelButton: true,
                confirmButtonText: "Forward to President",
                cancelButtonText: "Cancel",
                focusConfirm: false,
            }).then((result) => {
                if (result.isConfirmed) {

                    let staffVerification = [];

                    $('.staff-status-switch').each(function() {

                        let staffId = $(this).data('id');

                        let verifyFlag = $(this).is(':checked') ? 1 : 0;

                        staffVerification.push({
                            staff_id: staffId,
                            verify_flag: verifyFlag
                        });

                    });

                    console.log("STAFF VERIFICATION:", staffVerification);

                    $.ajax({
                        url: '{{ route('admin.forwardApplicationforma', ['role' => '__ROLE__']) }}'
                            .replace('__ROLE__', role),
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

                        success: function(response) {

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
                                    window.location.href =
                                        "{{ url('admin/dashboard') }}";
                                });
                            }

                        },
                        error: function(xhr) {
                            let errorMessage = xhr.responseJSON && xhr.responseJSON
                                .error ?
                                xhr.responseJSON.error :
                                "An unexpected error occurred.";
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


        $('#confirmReturnBtn').on('click', function() {


            var applicationId = @json($applicant->application_id);
            var returnBy = @json(Auth::user()->name);
            var forwardedTo = @json($returnForwardUser->roles_id ?? 0);

            // alert(forwardedTo);
            var remarks = $("#remarks").val().trim();

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

            $("#specific-class tbody tr").each(function() {

                let checkbox = $(this).find("input[name^='checklists']");
                let switchBtn = $(this).find("input[name^='status']");
                let checkid = $(this).find("input[name^='check_id']");

                let name = checkbox.attr("name");

                if (!name) {
                    return; // skip this row
                }

                let match = name.match(/\d+/);

                if (!match) {
                    return; // skip if no number found
                }

                let id = match[0];

                checklists[id] = checkbox.is(":checked") ? 1 : 0;
                status[id] = $(this).find('.status-switch').prop('checked') ? 1 : 0;
                check_id[id] = checkid.val();
            });

            let staffVerification = [];

            $('.staff-status-switch').each(function() {

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
                            application_id: applicationId,
                            return_by: returnBy,
                            forwarded_to: forwardedTo,
                            remarks: remarks || "No remarks provided",
                            checkboxes: checkboxStatus,
                            queryswitch: queryswitch,
                            "queryType[]": queryType,
                            checklists: checklists,
                            status: status,
                            check_id: check_id,

                            returnflag: returnflag,

                            staff_verification: JSON.stringify(staffVerification)

                        },
                        success: function(response) {
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
                                    window.location.href =
                                        "{{ url('admin/dashboard') }}";
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = xhr.responseJSON && xhr.responseJSON
                                .error ? xhr.responseJSON.error :
                                "An unexpected error occurred.";
                            $('#errorMessage').text(errorMessage);
                            $('#errorModal').modal('show');
                        }
                    });
                }
            });

        });



        // -----------------return to applicant---------------------

        // --------process-----------


        $("#returntoapplicant").on("click", function() {

            $(".modal-footer button").not(this).prop("disabled", true);

            $("#return_section").slideDown();

            $('html, body').animate({
                scrollTop: $("#return_section").offset().top - 120
            }, 600);

        });

        $("#exit_return").on("click", function() {

            $("#return_section").slideUp();

            $(".modal-footer button").prop("disabled", false);

            // Clear return checkboxes
            $(".return-checkbox").prop("checked", false);

            // Clear remarks
            $("#remarks_return").val("");

            // Clear validation messages
            $("#checkbox_error").text("").hide();
            $("#remarks_error").text("");
            $("#query_error").text("");
        });

        $("#process_return").on("click", function() {

            // -----------------------------------------
            // BASIC VALIDATION
            // -----------------------------------------

            var remarks = $("#remarks").val().trim();
            var remarks_return = $("#remarks_return").val().trim();

            $("#remarks_error").text("");
            $("#checkbox_error").text("").hide();
            $("#query_error").text("");

            // Remarks validation (same as forwardbtn)
            if (remarks === "") {
                $("#remarks_error").text("Remarks is required");
                $("#remarks").focus();
                return;
            }

            // Return reason validation
            let checkedReasons = [];

            $(".return-checkbox:checked").each(function() {
                checkedReasons.push($(this).val());
            });

            if (checkedReasons.length === 0) {

                $("#checkbox_error")
                    .text("Select at least one reason before return.")
                    .fadeIn();

                $('html, body').animate({
                    scrollTop: $("#checkbox_error").offset().top - 150
                }, 600);

                return;
            } else {
                $("#checkbox_error").fadeOut();
            }

            // -----------------------------------------
            // APPLICATION DETAILS
            // -----------------------------------------

            var applicationId = @json($applicant->application_id);
            var processedBy = @json(Auth::user()->name);
            var role_id = @json(Auth::user()->roles_id);
            var forwardedTo = @json($returnForwardUser->roles_id ?? 0);

            var returnflag = @json($applicant->return_flag);
            var application_status = @json($applicant->application_status);

            var checkboxStatus = "Yes";

            // -----------------------------------------
            // QUERY VALIDATION
            // -----------------------------------------

            let queryswitch = $("#Queryswitch").prop("checked");
            let queryType = $("#queryType").val() || [];
            let errorBox = $("#query_error");

            errorBox.text("");

            if (queryswitch && queryType.length === 0) {

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Please select at least one query type."
                });

                return;
            }

            // -----------------------------------------
            // RETURN CONFIRMATION
            // -----------------------------------------

            Swal.fire({
                title: "Declaration",
                text: "Confirm that the application is being returned to the applicant for the selected reasons.",
                showCancelButton: true,
                confirmButtonText: "Return to Applicant",
                cancelButtonText: "Cancel",
                focusConfirm: false
            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                // -----------------------------------------
                // CHECKLIST DATA
                // -----------------------------------------

                let check_id = {};
                let checklists = {};
                let status = {};

                $('input[name^="check_id["]').each(function() {

                    let id = $(this).attr("name")
                        .replace("check_id[", "")
                        .replace("]", "");

                    check_id[id] = $(this).val();

                    checklists[id] =
                        $('input[name="checklists[' + id + ']"]').is(":checked") ?
                        1 :
                        0;

                    status[id] =
                        $('input[name="status[' + id + ']"]').is(":checked") ?
                        1 :
                        0;
                });

                console.log("CHECK ID:", check_id);
                console.log("CHECKLISTS:", checklists);
                console.log("STATUS:", status);

                // -----------------------------------------
                // STAFF VERIFICATION DATA
                // -----------------------------------------

                let staffVerification = [];

                $(".staff-status-switch").each(function() {

                    let staffId = $(this).data("id");
                    let verifyFlag = $(this).is(":checked") ? 1 : 0;

                    staffVerification.push({
                        staff_id: staffId,
                        verify_flag: verifyFlag
                    });
                });

                console.log("STAFF VERIFICATION:", staffVerification);

                // -----------------------------------------
                // AJAX REQUEST
                // -----------------------------------------

                $.ajax({
                    url: '{{ route('admin.returntoapplicantforma') }}',
                    type: "POST",

                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    },

                    data: {
                        application_id: applicationId,
                        return_by: processedBy,
                        forwarded_to: forwardedTo,
                        role_id: role_id,

                        remarks: remarks,
                        remarks_return: remarks_return,

                        checkboxes: checkboxStatus,
                        reasons: checkedReasons,

                        application_status: application_status,
                        returnflag: returnflag,

                        queryswitch: queryswitch ? "Yes" : "No",
                        "queryType[]": queryType,

                        // Staff verification
                        staff_verification: JSON.stringify(staffVerification),

                        // Checklist data
                        check_id: check_id,
                        checklists: checklists,
                        status: status
                    },

                    success: function(response) {

                        if (response.status === "success") {

                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: response.message,
                                confirmButtonText: "OK",
                                allowOutsideClick: false
                            }).then(() => {
                                window.location.href =
                                    "{{ url('admin/dashboard') }}";
                            });

                        } else {

                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: response.message ||
                                    "Unable to return the application."
                            });
                        }
                    },

                    error: function(xhr) {

                        let errorMessage =
                            xhr.responseJSON && (
                                xhr.responseJSON.error ||
                                xhr.responseJSON.message
                            ) ?
                            (
                                xhr.responseJSON.error ||
                                xhr.responseJSON.message
                            ) :
                            "An unexpected error occurred.";

                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: errorMessage
                        });
                    }
                });
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

                    $('input[name^="check_id["]').each(function() {

                        let id = $(this).attr('name')
                            .replace('check_id[', '')
                            .replace(']', '');

                        check_id[id] = $(this).val();

                        checklists[id] =
                            $('input[name="checklists[' + id + ']"]').is(':checked') ?
                            1 :
                            0;

                        status[id] =
                            $('#status_' + id).prop('checked') ?
                            1 :
                            0;
                    });

                    console.log("CHECK ID:", check_id);
                    console.log("CHECKLISTS:", checklists);
                    console.log("STATUS:", status);


                    let staffVerification = [];

                    $('.staff-status-switch').each(function() {

                        let staffId = $(this).data('id');

                        let verifyFlag = $(this).is(':checked') ? 1 : 0;

                        staffVerification.push({
                            staff_id: staffId,
                            verify_flag: verifyFlag
                        });

                    });

                    console.log("STAFF VERIFICATION:", staffVerification);

                    $.ajax({
                        url: '{{ route('admin.forwardApplicationforma', ['role' => '__ROLE__']) }}'
                            .replace('__ROLE__', role),
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
                        success: function(response) {
                            if (response.status == "success") {
                                Swal.fire({
                                    icon: "success",
                                    title: "Success",
                                    text: response.message,
                                    confirmButtonText: "OK",
                                    allowOutsideClick: false
                                }).then(() => {
                                    window.location.href =
                                        "{{ url('admin/dashboard') }}";
                                });
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = xhr.responseJSON && xhr.responseJSON
                                .error ?
                                xhr.responseJSON.error :
                                "An unexpected error occurred.";
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
            var processedBy = @json(Auth::user()->name);
            var role_id = @json(Auth::user()->roles_id);
            var forwardedTo = @json($nextForwardUser->roles_id);
            var role = @json($nextForwardUser->name);
            var remarks = $("#remarks").val().trim();
            var queryswitch = $("#Queryswitch").prop("checked");
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
                url: '{{ route('admin.forwardApplicationforma', ['role' => '__ROLE__']) }}'
                    .replace('__ROLE__', role),
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
                            window.location.href = BASE_URL + '/admin/dashboard'
                        });
                    }

                },
                error: function(xhr) {
                    let errorMessage = xhr.responseJSON && xhr.responseJSON.error ? xhr
                        .responseJSON.error : "An unexpected error occurred.";
                    $('#errorModal .modal-body').html(`<p>${errorMessage}</p>`);
                    $('#errorModal').modal('show');
                }
            });
        });
    });
</script>


<script>
    $(document).on("click", ".verify-license", function() {
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
            success: function(response) {
                if (response.exists) {
                    $("#verify-result-" + proprietorId)
                        .html('<p class="text-success">Valid License</p>');
                } else {
                    $("#verify-result-" + proprietorId)
                        .html('<p class="text-danger">Invalid License</p>');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errMsg = errors.date ? errors.date[0] : "Validation failed";
                    $("#verify-result-" + proprietorId)
                        .html('<p class="text-danger">' + errMsg + '</p>');
                }
            }
        });
    });

    $(document).on('click', '.verify-btn_EA', function(e) {
        e.preventDefault();

        let btn = $(this);

        let license = btn.data('license');
        let firstIssue = btn.data('firstissue');
        let validityFrom = btn.data('validity_from');
        let validityTo = btn.data('validity_to');

        let resultDiv = btn.siblings('.verify-result_EA');

        resultDiv.html('<span class="text-info">Checking...</span>');

        $.ajax({
            url: "{{ route('admin.verifylicenseformAea_admin') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                license_number: license,
                first_issue: firstIssue,
                validity_from: validityFrom,
                validity_to: validityTo
            },
            success: function(response) {
                if (response.exists) {
                    resultDiv.html(
                        '<span class="text-success">Valid License</span>'
                    );
                } else {
                    resultDiv.html(
                        '<span class="text-danger">Invalid License</span>'
                    );
                }
            },
            error: function(xhr) {
                let message = 'Error checking license';

                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    message = Object.values(xhr.responseJSON.errors)
                        .flat()
                        .join('<br>');
                }

                resultDiv.html(
                    '<span class="text-danger">' + message + '</span>'
                );
            }
        });
    });

    $(document).on('click', '.verify-btn_staff', function() {
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
            success: function(response) {
                if (response.exists) {
                    resultDiv.html('<p class="text-success">Valid License</p>');
                } else {
                    resultDiv.html('<p class="text-danger">Invalid License</p>');
                }
            },
            error: function() {
                resultDiv.html('<p class="text-danger">Error checking license</p>');
            }
        });
    });


    // --------------------------------------------------------------------
    $(document).on('click', '.history-btn_staff', function() {
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
            success: function(response) {
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
            error: function() {
                $("#showlicense table tbody").html(`
                <tr>
                    <td colspan="5" class="text-danger">⚠️ Error fetching license history.</td>
                </tr>
            `);
            }
        });
    });



    // ----------admin check cert-------------
    $(document).on("click", ".verify-cert_ownership", function() {

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


            success: function(response) {

                console.log("ADMIN VERIFY RESPONSE:", response);

                if (response.status === true && response.certificate) {

                    let certificate = response.certificate;
                    let records = Array.isArray(response.data) ? response.data : [];

                    let result = `
            <div class="competency-result-box">

                <!-- CERTIFICATE DETAILS -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <tbody>

                            <tr>
                                <th width="20%">Certificate No</th>
                                <td width="30%">
                                    ${certificate.certificate_no ?? '-'}
                                </td>

                                <th width="20%">Date of Issue</th>
                                <td width="30%">
                                    ${certificate.dateof_issue ?? '-'}
                                </td>
                            </tr>

                            <tr>
                                <th>Validity From</th>
                                <td>
                                    ${certificate.valid_from ?? '-'}
                                </td>

                                <th>Validity To</th>
                                <td>
                                    ${certificate.valid_to ?? '-'}
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

                    if (records.length > 0) {

                        result += `
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">

                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th>Employee Type</th>
                                <th>Licence Category</th>
                                <th>Organisation Name</th>
                                <th>Designation</th>
                                <th>Address</th>
                                <th>Nature of Work</th>
                                <th>Voltage Level</th>
                                <th>Transformer (kVA)</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Total Experience</th>
                            </tr>
                        </thead>

                        <tbody>
            `;

                        records.forEach(function(data, index) {

                            let licenceCategory = data.emp_cate ?? '-';

                            let transformer = '-';

                            if (
                                data.voltage_level &&
                                data.voltage_level !== 'up_to_650v'
                            ) {
                                transformer = data.transformer_kva ?? '-';
                            }

                            result += `
                    <tr>

                        <td>
                            ${index + 1}
                        </td>

                        <td>
                            ${data.emp_type ?? '-'}
                        </td>

                        <td>
                            ${licenceCategory}
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
                            ${transformer}
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
                    Certificate found, but no experience data found.
                </div>
            `;
                    }

                    result += `
            </div>
        `;

                    $("#competencyVerifyModalBody").html(result);

                } else {

                    $("#competencyVerifyModalBody").html(`
            <div class="alert alert-warning">
                ${response.message ?? "Certificate not found."}
            </div>
        `);
                }
            },


            error: function(xhr) {

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


            complete: function() {

                $button
                    .prop("disabled", false)
                    .text("Verify");

            }

        });

    });



    // verify- competency_staff----------------

    $(document).on("click", ".verify-staff", function() {

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


            success: function(response) {

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
                    let applicant = response.applicant_details || {};
                    let result = `

                    <div class="competency-result-box">

                           <div class="competency-result-title">
                                Applicant Details
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="20%">Applicant Name</th>
                                            <td width="30%">${applicant.applicant_name ?? '-'}</td>

                                            <th width="20%">Father's Name</th>
                                            <td width="30%">${applicant.fathers_name ?? '-'}</td>
                                        </tr>

                                        <tr>
                                            <th>Date of Birth</th>
                                            <td>${applicant.dob ?? '-'}</td>

                                            <th>Age</th>
                                            <td>${applicant.age ?? '-'}</td>
                                        </tr>
                                   

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

                                         <th colspan="2">
                                             Documents
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                    `;


                        records.forEach(function(data, index) {

                            result += `

                            <tr>

                                <td>
                                    ${index + 1}
                                </td>


                                <td>
                                    ${data.emp_type ?? '-'}
                                </td>


                                <td>
                                    ${data.emp_cate ?? '-'}
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
                            <td>
                                ${data.relieve_document
                                    ? `<a href="${data.relieve_document}"
                                        target="_blank" rel="noopener noreferrer">View Relieving Document</a>`
                                    : '-'}
                            </td>

                            <td>
                                ${data.support_document
                                    ? `<a href="${data.support_document}"
                                        target="_blank" rel="noopener noreferrer">View Supporting Document</a>`
                                    : '-'}
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


            error: function(xhr) {

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


            complete: function() {

                $button
                    .prop("disabled", false)
                    .text("View History");

            }

        });

    });


    // ---------other staff----------------------------------------

    $(document).on("click", ".verify-otherstaff", function() {

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

            url: "{{ route('admin.checkCompetencyotherCertificateadmin') }}",

            type: "POST",

            data: {

                _token: $('meta[name="csrf-token"]').attr("content"),

                certificate_no: certificate_no,

                dateof_issue: dateof_issue,

                valid_from: valid_from,

                valid_to: valid_to

            },


            success: function(response) {

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
                    let applicant = response.applicant_details || {};
                    let result = `

                    <div class="competency-result-box">

                           <div class="competency-result-title">
                                Applicant Details
                            </div>

                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th width="20%">Applicant Name</th>
                                            <td width="30%">${applicant.applicant_name ?? '-'}</td>

                                            <th width="20%">Father's Name</th>
                                            <td width="30%">${applicant.fathers_name ?? '-'}</td>
                                        </tr>

                                        <tr>
                                            <th>Date of Birth</th>
                                            <td>${applicant.dob ?? '-'}</td>

                                            <th>Age</th>
                                            <td>${applicant.age ?? '-'}</td>
                                        </tr>
                                   

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

                                         <th colspan="2">
                                             Documents
                                        </th>

                                    </tr>

                                </thead>

                                <tbody>

                    `;


                        records.forEach(function(data, index) {

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
                            <td>
                                ${data.relieve_document
                                    ? `<a href=" ${data.relieve_document}" target="_blank" rel="noopener noreferrer">View Relieving Document</a>`
                                    : '-'}
                            </td>

                            <td>
                                ${data.support_document
                                    ? `<a href="${data.support_document}" target="_blank" rel="noopener noreferrer">View Supporting Document</a>`
                                    : '-'}
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


            error: function(xhr) {

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


            complete: function() {

                $button
                    .prop("disabled", false)
                    .text("View History");

            }

        });

    });

    // ----------------------------------

    $(document).on('change', '.staff-status-switch', function() {

        let id = $(this).data('id');

        if ($(this).is(':checked')) {

            $('#staffStatusText_' + id)
                .removeClass('bg-danger')
                .addClass('bg-success')
                .text('Correct');

        } else {

            $('#staffStatusText_' + id)
                .removeClass('bg-success')
                .addClass('bg-danger')
                .text('InCorrect');

        }

    });

    $(document).on('change', '.status-switch', function() {

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
