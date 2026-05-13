@include('admincms.include.top')
@include('admincms.include.header')
@include('admincms.include.navbar')

<style>
 
:root{
    --border-color: #E2E8F0;
}

 .table tbody tr {
    border: 1px solid var(--border-color);
}

.swal-staff-success {
    border-radius: 10px;
    padding: 18px;
}

/* Main card */
.staff-success-card {
    font-size: 14px;
    color: #333;
}

/* Staff ID box */
.staff-id-box {
    background: #f4f8ff;
    border: 1px dashed #0d6efd;
    border-radius: 6px;
    padding: 12px;
    text-align: center;
    margin-bottom: 15px;
}

.staff-id-box small {
    display: block;
    font-size: 11px;
    color: #6c757d;
    letter-spacing: 1px;
}

.staff-id-box h4 {
    margin: 6px 0;
    color: #0d6efd;
    font-weight: 700;
}

.copy-btn {
    background: #0d6efd;
    color: #fff;
    border: none;
    padding: 4px 10px;
    font-size: 12px;
    border-radius: 4px;
    cursor: pointer;
}

.copy-btn:hover {
    background: #0b5ed7;
}

.login-note-muted {
    font-size: 12px;
    color: rgb(187, 47, 47);
    margin: 10px 0 15px;
    text-align: center;
}



.select2.select2-container .select2-selection .select2-selection__rendered {
  color: #333;
  background: #fff;
  /* line-height: 32px; */
  /* padding-right: 33px; */
}


.select2.select2-container .select2-selection--multiple .select2-selection__choice {
  /* background-color: #f8f8f8; */
  border: 1px solid #ccc;
  -webkit-border-radius: 3px;
  -moz-border-radius: 3px;
  border-radius: 3px;
  margin: 4px 4px 0 0;
  padding: 0 6px 0 22px;
  height: 24px;
  /* line-height: 24px; */
  font-size: 12px;
  position: relative;
}

.select2.select2-container .select2-selection--multiple .select2-selection__choice .select2-selection__choice__remove {
  position: absolute;
  top: 0;
  left: 0;
  margin: 0;
  text-align: center;
  color: #e74c3c;
  font-weight: bold;
  font-size: 16px;
}
/* .select2-container .select2-dropdown {
  background: transparent;
  border: none;
  margin-top: -5px;
} */
.select2-container .select2-dropdown .select2-results ul {
  background: #fff;
  border: 1px solid #34495e;
}
.select2-container .select2-dropdown .select2-results ul .select2-results__option--highlighted[aria-selected] {
  background-color: #3498db;
}

.select2-container--default .select2-selection--multiple .select2-selection__clear {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background-color: #f8d7da;
    color: #842029;
    font-size: 20px;
    margin-right: 6px;
    cursor: pointer;
    transition: all 0.2s ease-in-out;
}

.select2-selection__clear:hover {
    background-color: #dc3545;
    color: #fff;
}

  .userNameLable {
    display: block;
    text-align: center;
    color: #000;
    font-weight: 700;
    font-size: 22px;
    line-height: 1.2;
    letter-spacing: 0.3px;
  }

  .cursor-pointer {
    cursor: pointer;
}

/* Compact status action button */
.status-dropdown {
    display: inline-flex;
    align-items: center;
}

.status-btn {
    border: 1px solid transparent;
    background-color: transparent;
    padding: 1px 6px;
    font-size: 11px;
    line-height: 1.2;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    
}

/* Active / Inactive colors */
.status-active {
    color: #198754;            /* Bootstrap success */
}

.status-inactive {
    color: #dc3545;            /* Bootstrap danger */
}

/* Toggle separator */
.status-toggle {
    padding: 1px 4px;
}

/* Hover effect */
.status-btn:hover {
    background-color: rgba(0,0,0,0.05);
}

/* Dropdown menu size adjust (optional) */
.status-dropdown .dropdown-menu {
    font-size: 12px;
    min-width: 120px;
}

.updateFormNew,
.updateFormRenew {
    white-space: nowrap;
    flex-shrink: 0;
    min-width: 52px;
}

table.dataTable thead th {
    border: 1px solid #dee2e6 !important;
}

table.dataTable tbody td {
    border: 1px solid #dee2e6 !important;
}

table.dataTable {
    border-collapse: collapse !important;
}


table.dataTable thead th {
    vertical-align: middle !important;
    position: relative;
}

/* Fix Bootstrap 5 DataTables SVG sorting icon position – vertical center the asc/desc symbols only */
table.dataTable thead th.sorting,
table.dataTable thead th.sorting_asc,
table.dataTable thead th.sorting_desc {
    background-position: right 8px center !important;
    background-repeat: no-repeat !important;
}
/* Vertically center the sort arrow symbols ( :before = up, :after = down ) in the header cell */
table.dataTable thead .sorting:before,
table.dataTable thead .sorting_asc:before,
table.dataTable thead .sorting_desc:before {
    position: absolute !important;
    top: calc(50% - 10px) !important;
    margin-top: 0 !important;
}
table.dataTable thead .sorting:after,
table.dataTable thead .sorting_asc:after,
table.dataTable thead .sorting_desc:after {
    position: absolute !important;
    top: calc(50% + 2px) !important;
    margin-top: 0 !important;
}

/* DataTables sort arrows: bold bright white */
/* Inactive up arrow */
table.dataTable thead .sorting:before,
table.dataTable thead .sorting_desc:before {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='4.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='18 15 12 9 6 15'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
    background-size: 14px !important;
    opacity: 0.9 !important;
}
/* Inactive down arrow */
table.dataTable thead .sorting:after,
table.dataTable thead .sorting_asc:after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='4.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
    background-size: 14px !important;
    opacity: 0.9 !important;
}
/* Ascending active – up arrow */
table.dataTable thead .sorting_asc:before {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='5.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='18 15 12 9 6 15'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
    background-size: 15px !important;
    opacity: 1 !important;
    filter: drop-shadow(0 0 2px rgba(255,255,255,0.8)) !important;
}

/* Descending active – down arrow */
table.dataTable thead .sorting_desc:after {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='5.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: center !important;
    background-size: 15px !important;
    opacity: 1 !important;
    filter: drop-shadow(0 0 2px rgba(255,255,255,0.8)) !important;
}

#historyTable thead th {
    background-color: #2f4f4f !important;
    color: #ffffff !important;
}


.modal-content .modal-body a:not(.btn) {
    color: #ffffff;
    font-weight: 600;
}



</style>


<div id="content" class="main-content">
    <div class="layout-px-spacing">

        <div class="middle-content container-xxl p-0">
            <!--  BEGIN BREADCRUMBS  -->
            <div class="secondary-nav">
                <div class="breadcrumbs-container" data-page-heading="Analytics">
                    <header class="header navbar navbar-expand-sm">
                        <a href="#" class="btn-toggle sidebarCollapse" data-placement="bottom">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </a>
                        <div class="d-flex breadcrumb-content">
                            <div class="page-header">
                                <div class="page-title">
                                </div>
                                <nav class="breadcrumb-style-one" aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="#">Content Management System for TNELB</a>
                                        </li>
                                    </ol>
                                </nav>

                            </div>
                        </div>

                    </header>
                </div>
            </div>
            <!--  END BREADCRUMBS  -->

            <div class="row layout-top-spacing dashboard">

                <nav class="breadcrumb-style-five breadcrumbs_top  mb-2" aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-home">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                </svg><span class="inner-text">Dashboard </span></a></li>
                        <li class="breadcrumb-item"><a href="#">Homepage</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Portal User Management Console</li>
                    </ol>
                </nav>

                <div id="tableCustomBasic" class="col-lg-12 col-12 layout-spacing">
                    <div class="statbox widget  box-shadow ">
                        <div class="widget-header mb-4">
                            <div class="row mt-2">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <h4 class="text-dark card-title">Portal User Management Console </h4>
                                </div>

                            </div>
                        </div>
                        <div class="card">
                            {{-- <div class="card-header">
                                <div class="float-right">
                                    <button type="button" class="btn btn-info mb-2 me-4 float-end" data-bs-toggle="modal" data-bs-target="#inputFormModaladdstaffs">
                                        <i class="fa fa-plus"></i>&nbsp; Add New Staff
                                    </button>
                                </div>
                            </div> --}}
                            <div class="card-body">
                                <select id="customColumnFilter" class="form-select form-select-sm" style="display: none;">
                                    <option value="">All</option>
                                </select>
                                <table id="staffTable" class="table table-bordered align-middle portaladmin">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" rowspan="2">S.No</th>
                                            <th class="text-center" rowspan="2">User Name</th>
                                            <th class="text-center" rowspan="2">User Role</th>
                                            <th class="text-center" colspan="2" no-sort>Handle Forms</th>
                                            <th class="text-center" rowspan="2">Status</th>
                                            <th class="text-center" rowspan="2">Action</th>
                                        </tr>
                                        <tr>
                                            <th class="text-center">New</th>
                                            <th class="text-center">Renewal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="formtable">
                                        @foreach ($users as $user)
                                        
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-center">{{ $user->user_name }}</td>
                                            <td class="text-center">{{ $user->role_name }}</td>
                                            <td class="align-middle">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    
                                                    <!-- Forms info -->
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @if($user->new_forms && $user->new_forms !== '-')
                                                            @foreach(explode(',', $user->new_forms) as $form)
                                                                <span class="badge bg-light text-dark border">
                                                                    {{ trim($form) }}
                                                                </span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted fst-italic">Not assigned</span>
                                                        @endif
                                                    </div>

                                                    <!-- Manage action -->
                                                    <a href="javascript:void(0);"
                                                    class="btn btn-primary btn-sm updateFormNew ms-2 text-nowrap flex-shrink-0 align-self-start"
                                                    data-user_id="{{ $user->s_id }}"
                                                    data-user_name="{{ $user->user_name }}"
                                                    data-new_form_ids="{{ $user->new_form_ids }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editFormNew"
                                                    title="Manage Forms">
                                                        Edit
                                                    </a>
                                                </div>
                                            </td>
                                            <td class="align-middle">
                                                <div class="d-flex justify-content-between align-items-center">

                                                    <div class="d-flex flex-wrap gap-1">
                                                        @if($user->renewal_forms && $user->renewal_forms !== '-')
                                                            @foreach(explode(',', $user->renewal_forms) as $form)
                                                                <span class="badge bg-light text-dark border">
                                                                    {{ trim($form) }}
                                                                </span>
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted fst-italic">Not assigned</span>
                                                        @endif
                                                    </div>

                                                    <a href="javascript:void(0);"
                                                    class="btn btn-primary btn-sm updateFormRenew ms-2 text-nowrap flex-shrink-0 align-self-start"
                                                    data-user_id="{{ $user->s_id }}"
                                                    data-user_name="{{ $user->user_name }}"
                                                    data-renewal_form_ids="{{ $user->renewal_form_ids }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#renewalForm"
                                                    title="Manage Forms">
                                                        Edit
                                                    </a>
                                                </div>
                                            </td>
                                        
                                            <td class="text-center align-middle">
                                                @php
                                                    $isActive = ($user->user_status == '1');
                                                    $btnText  = $isActive ? 'Active' : 'Inactive';
                                                    $btnClass = $isActive ? 'status-active' : 'status-inactive';
                                                @endphp

                                                <div class="status-dropdown">
                                                    <button type="button" class="status-btn {{ $btnClass }}">
                                                        {{ $btnText }}
                                                    </button>

                                                    <button type="button"
                                                            class="status-btn {{ $btnClass }} status-toggle"
                                                            data-bs-toggle="dropdown"
                                                            aria-expanded="false">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            width="10"
                                                            height="10"
                                                            viewBox="0 0 24 24"
                                                            fill="none"
                                                            stroke="currentColor"
                                                            stroke-width="2"
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round">
                                                            <polyline points="6 9 12 15 18 9"></polyline>
                                                        </svg>
                                                    </button>

                                                    <ul class="dropdown-menu">
                                                        @if($isActive)
                                                            <li>
                                                                <a class="dropdown-item text-danger change-status"
                                                                href="javascript:void(0);"
                                                                data-user_id="{{ $user->s_id }}"
                                                                data-status="2">
                                                                    Deactivate
                                                                </a>
                                                            </li>
                                                        @else
                                                            <li>
                                                                <a class="dropdown-item text-success change-status"
                                                                href="javascript:void(0);"
                                                                data-user_id="{{ $user->s_id }}"
                                                                data-status="1">
                                                                    Activate
                                                                </a>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <a href="javascript:void(0);" class="editstaffdata"
                                                    data-user_id="{{ $user->s_id }}"
                                                    data-bs-toggle="modal" data-bs-target="#inputFormModaleditstaffs">
                                                    <i class="fa fa-key text-primary me-2 cursor-pointer" title="Reset Password"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="getUserHistory"
                                                    data-user_id="{{ $user->s_id }}"
                                                    data-bs-toggle="modal" data-bs-target="#userHistoryModal">
                                                    <i class="fa fa-history text-primary me-2 cursor-pointer" title="Form History"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <!-- ------ View Form history --------- -->
                <div class="modal fade" id="userHistoryModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">User Form History</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <table class="table table-bordered table-sm" id="historyTable">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Form Type</th>
                                            <th>Assigned Forms</th>
                                            <th>User Status</th>
                                            <th>Started At</th>
                                            <th>Ended At</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
                <!-- ------ View Form history --------- -->
                <div class="modal fade" id="userHistoryModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">User Form History</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <table class="table table-bordered table-sm" id="historyTable">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Form Type</th>
                                            <th>Assigned Forms</th>
                                            <th>User Status</th>
                                            <th>Started At</th>
                                            <th>Ended At</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    Close
                                </button>
                            </div>

                        </div>
                    </div>
                </div>


                <!-- --------------- -->

                <div class="modal fade" id="editFormNew" tabindex="-1" role="dialog" aria-labelledby="inputFormModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header" id="inputFormModalLabel">
                                <h5 class="modal-title">Assign / Update for New Forms</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form id="updateNewFormAssign" enctype="multipart/form-data">
                                    <input type="hidden" name="user_id" id="user_id">
                                    <input type="hidden" name="form_type" id="form_type" value="N">
                                    <div class="mb-4 text-center">
                                        <label class="form-label fw-semibold text-dark mb-1">User Name :</label>
                                        <span id="userNameLable" class="userNameLable fw-bold text-primary"></span>
                                    </div>
                                    <div class="row">
                                        <div class="pb-2 col-md-12">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                                <label class="mb-0 fw-bold bg-light px-2 py-1">Form Assignment :</label>
                                                <label class="mb-0 d-flex align-items-center gap-2 cursor-pointer user-select-none">
                                                    <input type="checkbox" class="form-check-input" id="editFormNewSelectAll" title="Select or clear all forms">
                                                    <span class="small text-muted">Select all forms</span>
                                                </label>
                                            </div>
                                                @php
                                                    $isCertificate = fn($form) => stripos((string) ($form->category_name ?? ''), 'certificate') !== false;
                                                    $isAmendment = fn($form) => stripos((string) ($form->category_name ?? ''), 'amendment') !== false;
                                                    $isLicence = fn($form) => stripos((string) ($form->category_name ?? ''), 'licence') !== false
                                                        || stripos((string) ($form->category_name ?? ''), 'license') !== false;

                                                    $certificateForms = $formlist->filter(fn($form) => $isCertificate($form));
                                                    $amendmentForms = $formlist->filter(fn($form) => $isAmendment($form));
                                                    $licenceForms = $formlist->filter(fn($form) => $isLicence($form) || (!$isCertificate($form) && !$isAmendment($form)));
                                                    $formLabel = fn($form) => "{$form->licence_name} [{$form->form_name}]";
                                                @endphp

                                                <div class="row g-3">
                                                    <div class="col-md-4">
                                                        <div class="border rounded-3 bg-white p-3 h-100">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <h6 class="mb-0 fw-bold text-dark">Certificates</h6>
                                                                <span class="badge bg-light text-primary border">{{ $certificateForms->count() }}</span>
                                                            </div>
                                                            <div class="pe-1" style="max-height: 260px; overflow-y: auto;">
                                                                @forelse ($certificateForms as $form)
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="checkbox" name="assigned_forms[]" id="assigned_form_certificate_{{ $form->id }}" value="{{ $form->id }}">
                                                                        <label class="form-check-label small" for="assigned_form_certificate_{{ $form->id }}">
                                                                            {{ $formLabel($form) }}
                                                                        </label>
                                                                    </div>
                                                                @empty
                                                                    <small class="text-muted">No forms</small>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="border rounded-3 bg-white p-3 h-100">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <h6 class="mb-0 fw-bold text-dark">Licences</h6>
                                                                <span class="badge bg-light text-success border">{{ $licenceForms->count() }}</span>
                                                            </div>
                                                            <div class="pe-1" style="max-height: 260px; overflow-y: auto;">
                                                                @forelse ($licenceForms as $form)
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="checkbox" name="assigned_forms[]" id="assigned_form_licence_{{ $form->id }}" value="{{ $form->id }}">
                                                                        <label class="form-check-label small" for="assigned_form_licence_{{ $form->id }}">
                                                                            {{ $formLabel($form) }}
                                                                        </label>
                                                                    </div>
                                                                @empty
                                                                    <small class="text-muted">No forms</small>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="border rounded-3 bg-white p-3 h-100">
                                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                                <h6 class="mb-0 fw-bold text-dark">Amendments</h6>
                                                                <span class="badge bg-light text-warning border">{{ $amendmentForms->count() }}</span>
                                                            </div>
                                                            <div class="pe-1" style="max-height: 260px; overflow-y: auto;">
                                                                @forelse ($amendmentForms as $form)
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="checkbox" name="assigned_forms[]" id="assigned_form_amendment_{{ $form->id }}" value="{{ $form->id }}">
                                                                        <label class="form-check-label small" for="assigned_form_amendment_{{ $form->id }}">
                                                                            {{ $formLabel($form) }}
                                                                        </label>
                                                                    </div>
                                                                @empty
                                                                    <small class="text-muted">No forms</small>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light-danger mt-2 mb-2" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary mt-2 mb-2">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="renewalForm" tabindex="-1" role="dialog" aria-labelledby="inputFormModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-xl" role="document">
                        <div class="modal-content">
                            <div class="modal-header" id="inputFormModalLabel">
                                <h5 class="modal-title">Assign / Update for Renewal Form</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form id="updateRenewalForm" enctype="multipart/form-data">
                                    <input type="hidden" name="user_id" id="user_id">
                                    <input type="hidden" name="form_type" id="form_type" value="R">
                                    <div class="mb-4 text-center">
                                        <label class="form-label fw-semibold text-dark mb-1">User Name :</label>
                                        <span id="userNameLableRenewal" class="userNameLable fw-bold text-primary"></span>
                                    </div>
                                    @php
                                        $isCertificate = fn($form) => stripos((string) ($form->category_name ?? ''), 'certificate') !== false;
                                        $isAmendment = fn($form) => stripos((string) ($form->category_name ?? ''), 'amendment') !== false;
                                        $isLicence = fn($form) => stripos((string) ($form->category_name ?? ''), 'licence') !== false
                                            || stripos((string) ($form->category_name ?? ''), 'license') !== false;

                                        $certificateForms = $formlist->filter(fn($form) => $isCertificate($form));
                                        $amendmentForms = $formlist->filter(fn($form) => $isAmendment($form));
                                        $licenceForms = $formlist->filter(fn($form) => $isLicence($form) || (!$isCertificate($form) && !$isAmendment($form)));
                                        $formLabel = fn($form) => "{$form->licence_name} [{$form->form_name}]";
                                    @endphp

                                    <div class="row">
                                        <div class="pb-2 col-md-12">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                                <label class="mb-0 fw-bold bg-light px-2 py-1">Form Assignment :</label>
                                                {{-- <small class="text-muted">Select one or more forms</small> --}}
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <div class="border rounded-3 bg-white p-3 h-100">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <h6 class="mb-0 fw-bold text-dark">Certificates</h6>
                                                            <span class="badge bg-light text-primary border">{{ $certificateForms->count() }}</span>
                                                        </div>
                                                        @forelse ($certificateForms as $form)
                                                            <div class="form-check mb-1">
                                                                <input class="form-check-input" type="checkbox" name="assigned_forms[]" id="assigned_form_renewal_certificate_{{ $form->id }}" value="{{ $form->id }}">
                                                                <label class="form-check-label" for="assigned_form_renewal_certificate_{{ $form->id }}">
                                                                    {{ $formLabel($form) }}
                                                                </label>
                                                            </div>
                                                        @empty
                                                            <small class="text-muted">No forms</small>
                                                        @endforelse
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="border rounded-3 bg-white p-3 h-100">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <h6 class="mb-0 fw-bold text-dark">Licences</h6>
                                                            <span class="badge bg-light text-success border">{{ $licenceForms->count() }}</span>
                                                        </div>
                                                        @forelse ($licenceForms as $form)
                                                            <div class="form-check mb-1">
                                                                <input class="form-check-input" type="checkbox" name="assigned_forms[]" id="assigned_form_renewal_licence_{{ $form->id }}" value="{{ $form->id }}">
                                                                <label class="form-check-label" for="assigned_form_renewal_licence_{{ $form->id }}">
                                                                    {{ $formLabel($form) }}
                                                                </label>
                                                            </div>
                                                        @empty
                                                            <small class="text-muted">No forms</small>
                                                        @endforelse
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="border rounded-3 bg-white p-3 h-100">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <h6 class="mb-0 fw-bold text-dark">Amendments</h6>
                                                            <span class="badge bg-light text-warning border">{{ $amendmentForms->count() }}</span>
                                                        </div>
                                                        @forelse ($amendmentForms as $form)
                                                            <div class="form-check mb-1">
                                                                <input class="form-check-input" type="checkbox" name="assigned_forms[]" id="assigned_form_renewal_amendment_{{ $form->id }}" value="{{ $form->id }}">
                                                                <label class="form-check-label" for="assigned_form_renewal_amendment_{{ $form->id }}">
                                                                    {{ $formLabel($form) }}
                                                                </label>
                                                            </div>
                                                        @empty
                                                            <small class="text-muted">No forms</small>
                                                        @endforelse
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                               
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light-danger mt-2 mb-2" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary mt-2 mb-2">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- -------- Add User Login ------- -->
                <div class="modal fade inputForm-modal reset-on-open" id="inputFormModaladdstaffs" tabindex="-1" role="dialog" aria-labelledby="inputFormModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">

                            <div class="modal-header" id="inputFormModalLabel">
                                <h5 class="modal-title">Add New Staff</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">
                                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form id="newstaffmaster" novalidate enctype="multipart/form-data">
                                    <!-- Page Type Selection -->
                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <div class="form-group">
                                                <label for="inputEmail4" class="form-label">User Name<span>*</span></label>
                                                <div class="input-group mb-1">
                                                    <input type="text" class="form-control" name="staff_name" id="staff_name">
                                                </div>
                                                <small class="text-danger error-text" data-error="staff_name"></small>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-2">
                                            <div class="form-group">
                                                <label>User Role<span>*</span></label>
                                                <select class="form-select" name="role_id" id="role_id">
                                                    <option value="">Please select the user role</option>
                                                    @foreach ($userRoles as $item)
                                                        <option value="{{ $item->r_id }}">{{ $item->role_name }}</option>
                                                    @endforeach
                                                </select>
                                                <small class="text-danger error-text" data-error="role_id"></small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 mb-2">
                                            <div class="form-group">
                                                <label for="inputEmail4" class="form-label">User Login Email<span>*</span></label>
                                                <div class="input-group mb-2">
                                                    <input type="email" class="form-control" name="staff_email" id="staff_email">
                                                </div>
                                                <small class="text-danger error-text" data-error="staff_email"></small>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mb-3">
                                            <div class="form-group">
                                                <label class="form-label">User Login Password <span>*</span></label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="user_random_pass" id="user_random_pass" placeholder="Enter password">&nbsp;
                                                </div>
                                                <small class="text-danger error-text" data-error="user_random_pass"></small>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <div class="form-group">
                                                <label class="form-label">Confirm Password <span>*</span></label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="user_random_pass" id="user_random_pass" placeholder="Enter password">&nbsp;
                                                </div>
                                                <small class="text-danger error-text" data-error="user_random_pass"></small>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <!-- Modal Footer -->
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-light-danger mt-2 mb-2 btn-no-effect" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary mt-2 mb-2 btn-no-effect">Add</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>


                <div class="modal fade inputForm-modal reset-on-open" id="inputFormModaleditstaffs" tabindex="-1" role="dialog" aria-labelledby="inputFormModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content">

                            <div class="modal-header" id="inputFormModalLabel">
                                <h5 class="modal-title">Reset User Password</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true">
                                    <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>

                            <div class="modal-body">
                                <form id="resetForm" novalidate enctype="multipart/form-data">
                                    <input type="hidden" name="user_id" id="user_id">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <div class="form-group">
                                                <label class="form-label">New Password <span>*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" name="user_passwd" id="user_passwd" placeholder="Enter password">
                                                    <span class="input-group-text cursor-pointer toggle-password" data-target="#user_passwd">
                                                        <i class="fa fa-eye"></i>
                                                    </span>
                                                </div>
                                                <small id="passwordError" class="text-danger d-none"></small>
                                            </div>
                                        </div>

                                        <div class="col-md-12 mb-3">
                                            <div class="form-group">
                                                <label class="form-label">Confirm Password <span>*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" name="confirmPasswd" id="confirmPasswd" placeholder="Confirm password">
                                                    <span class="input-group-text cursor-pointer toggle-password" data-target="#confirmPasswd">
                                                        <i class="fa fa-eye"></i>
                                                    </span>
                                                </div>
                                                <small id="confirmError" class="text-danger d-none">Passwords do not match</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Modal Footer -->
                                <div class="modal-footer justify-content-center">
                                    <button type="button" class="btn btn-light-danger mt-2 mb-2 btn-no-effect" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary mt-2 mb-2 btn-no-effect">Reset</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admincms.include.footer');
<script>
    $(document).ready(function() {

        $('.select2').each(function () {
            const $modal = $(this).closest('.modal'); // or your modal class
            $(this).select2({
                width: "100%",
                dropdownParent: $modal,
                allowClear: true
            });
        });

        $('#user_passwd').off('keyup blur').on('blur', function () {
            let password = $(this).val();

            if (!isStrongPassword(password)) {
                $('#passwordError')
                    .text('Password must be at least 8 characters and include uppercase, number, and special character (Example: Admin@123).')
                    .removeClass('d-none');
            } else {
                $('#passwordError').text('').addClass('d-none');
            }
        });

        $('#user_passwd').on('keyup', function () {
            $('#passwordError').text('').addClass('d-none');
        });

        $('#confirmPasswd').on('keyup', function () {
            if ($(this).val() !== $('#user_passwd').val()) {
                $('#confirmError').removeClass('d-none');
            } else {
                $('#confirmError').addClass('d-none');
            }
        });

        $(document).on('click', '.toggle-password', function () {
            let target = $($(this).data('target'));
            let icon = $(this).find('i');

            if (target.attr('type') === 'password') {
                target.attr('type', 'text');
                icon.removeClass('fa-eye').addClass('fa-eye-slash');
            } else {
                target.attr('type', 'password');
                icon.removeClass('fa-eye-slash').addClass('fa-eye');
            }
        });


        function isStrongPassword(pwd) {
            return (
                pwd.length >= 8 &&
                /[A-Z]/.test(pwd) &&
                /[0-9]/.test(pwd) &&
                /[\W_]/.test(pwd)
            );
        }
        
        $('#resetForm').on('submit', function (e) {
            e.preventDefault();

            let password = $('#user_passwd').val();
            let confirmPassword = $('#confirmPasswd').val();

            if (!isStrongPassword(password)) {
                $('#passwordError')
                    .text('Password must be at least 8 characters and include uppercase, number, and special character (Example: Admin@123).')
                    .removeClass('d-none');
                $('#user_passwd').focus();
                return;
            }
            $('#passwordError').text('').addClass('d-none');

            if (password !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Mismatch',
                    text: 'Passwords do not match'
                });
                return;
            }

            $.ajax({
                url: BASE_URL + "/admin/staff/reset-password",
                method: "POST",
                data: {
                    user_id: $('#resetForm input[name="user_id"]').val(),
                    password: password,
                    password_confirmation: confirmPassword,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated',
                        text: response.message
                    }).then(() => location.reload());
                },
                error: function (xhr) {
                    let msg = 'Something went wrong';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors)
                            .map(e => e[0])
                            .join('<br>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: msg
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                }
            });
        });
    });


    function requestUserStatusChange(userId, newStatus, forceDeactivate = 0) {
        return $.ajax({
            url: BASE_URL + '/admin/staff/change-status',
            type: 'POST',
            data: {
                user_id: userId,
                status: newStatus,
                force_deactivate: forceDeactivate,
                _token: $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    $(document).on('click', '.change-status', function () {
        let userId = $(this).data('user_id');
        let newStatus = $(this).data('status');
        let actionText = newStatus == 1 ? 'activate' : 'deactivate';

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure?',
            text: `Do you want to ${actionText} this user?`,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (!result.isConfirmed) return;

            requestUserStatusChange(userId, newStatus, 0)
                .done(function (response) {
                    if (response.status === true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error', response.message || 'Unable to update user', 'error');
                    }
                })
                .fail(function (xhr) {
                    const response = xhr.responseJSON || {};

                    if (
                        xhr.status === 422 &&
                        response.needs_confirmation === true &&
                        newStatus == 2
                    ) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Forms Assigned',
                            text: response.message || 'Forms are assigned for New or Renewal. Do you want to deactivate and clear all assigned forms?',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Deactivate',
                            cancelButtonText: 'Cancel'
                        }).then((confirmResult) => {
                            if (!confirmResult.isConfirmed) return;

                            requestUserStatusChange(userId, 2, 1)
                                .done(function (forceRes) {
                                    if (forceRes.status === true) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Updated',
                                            text: forceRes.message,
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire('Error', forceRes.message || 'Unable to deactivate user', 'error');
                                    }
                                })
                                .fail(function (forceXhr) {
                                    Swal.fire('Error', forceXhr.responseJSON?.message || 'Something went wrong', 'error');
                                });
                        });
                        return;
                    }

                    Swal.fire('Error', response.message || 'Something went wrong', 'error');
                });
        });
    });

    // $('.table').DataTable({
    //     "dom": "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l><'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
    // "<'table-responsive'tr>" +
    // "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count  mb-sm-0 mb-3'i><'dt--pagination'p>>",
    //     "oLanguage": {
    //         "oPaginate": { "sPrevious": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-left"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>', "sNext": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-arrow-right"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>' },
    //         "sInfo": "Showing page _PAGE_ of _PAGES_",
    //         "sSearch": '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-search"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>',
    //         "sSearchPlaceholder": "Search...",
    //         "sLengthMenu": "Results :  _MENU_",
    //     },
    //     "stripeClasses": [],
    //     "lengthMenu": [10, 20, 50],
    //     "pageLength": 10 ,
    //     columnDefs: [
    //     { orderable: false, targets: [] } // leave empty because all real columns sortable
    //     ],
    //     orderCellsTop: true 
    // });


    $('#staffTable').DataTable({
        dom:
            "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l>" +
            "<'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
            "<'table-responsive'tr>" +
            "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i>" +
            "<'dt--pagination'p>>",

        orderCellsTop: true,   // 🔥 Required for multi-row header

        stripeClasses: [],
        lengthMenu: [10, 20, 50],
        pageLength: 10,

        language: {
            paginate: {
                previous: `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-arrow-left">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                `,
                next: `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-arrow-right">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                `
            },
            info: "Showing page _PAGE_ of _PAGES_",

            search: `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="feather feather-search">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            `,

            searchPlaceholder: "Search...",
            lengthMenu: "Results : _MENU_"
        }
    });

    window.historyTable = $('#historyTable').DataTable({
        dom:
            "<'dt--top-section'<'row'<'col-12 col-sm-6 d-flex justify-content-sm-start justify-content-center'l>" +
            "<'col-12 col-sm-6 d-flex justify-content-sm-end justify-content-center mt-sm-0 mt-3'f>>>" +
            "<'table-responsive'tr>" +
            "<'dt--bottom-section d-sm-flex justify-content-sm-between text-center'<'dt--pages-count mb-sm-0 mb-3'i>" +
            "<'dt--pagination'p>>",
        lengthMenu: [5, 10, 20],
        pageLength: 5,
        ordering: false,
        language: {
            emptyTable: "Select a user",
            paginate: {
                previous: `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-arrow-left">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                `,
                next: `
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="feather feather-arrow-right">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                `
            },
            info: "Showing page _PAGE_ of _PAGES_",
            search: `
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="feather feather-search">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            `,
            searchPlaceholder: "Search...",
            lengthMenu: "Results : _MENU_"
        }
    });

    // Assign / Update for New Forms modal – single "Select all" checkbox
    (function () {
        var modal = document.getElementById('editFormNew');
        var form = document.getElementById('updateNewFormAssign');
        var selectAllCb = document.getElementById('editFormNewSelectAll');
        if (!form || !selectAllCb) return;
        var checkboxes = function () { return form.querySelectorAll('input[name="assigned_forms[]"]'); };
        selectAllCb.addEventListener('change', function () {
            var checked = this.checked;
            checkboxes().forEach(function (cb) { cb.checked = checked; });
        });
        modal.addEventListener('show.bs.modal', function () { selectAllCb.checked = false; });
        form.addEventListener('change', function (e) {
            if (e.target.name !== 'assigned_forms[]') return;
            var all = checkboxes();
            var checkedCount = 0;
            all.forEach(function (cb) { if (cb.checked) checkedCount++; });
            selectAllCb.checked = all.length > 0 && checkedCount === all.length;
        });
    })();

</script>

