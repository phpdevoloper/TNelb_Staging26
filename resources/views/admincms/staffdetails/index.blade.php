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

/* Staff thumbnail in the table */
.staff-thumb {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    object-fit: cover;
    background: #eef2f7;
    border: 1px solid #dee2e6;
    flex-shrink: 0;
}
.staff-thumb-placeholder {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
    color: #6c757d;
}

/* Photo preview in modals */
.profile-photo-preview {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e9ecef;
}
.profile-photo-preview-wrap {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

/* Accordion polish for staff modals */
.staff-accordion .accordion-button {
    background: #f8f9fa;
    font-weight: 600;
    color: #212529;
}
.staff-accordion .accordion-button:not(.collapsed) {
    background: #e7f1ff;
    color: #0d6efd;
    box-shadow: inset 0 -1px 0 rgba(0,0,0,.075);
}
.staff-accordion .accordion-button:focus {
    box-shadow: none;
    border-color: rgba(0,0,0,.125);
}
.staff-accordion .accordion-item {
    margin-bottom: 8px;
    border-radius: 6px;
    overflow: hidden;
}

/* =========================================================
   Modern Edit User Details modal
   ========================================================= */
#editStaffDetailsModal .modal-content {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 25px 60px rgba(15, 23, 42, 0.25);
}

#editStaffDetailsModal .modal-header {
    border: none;
    padding: 0;
    position: relative;
}

#editStaffDetailsModal .modal-body { padding: 0; }
#editStaffDetailsModal .modal-footer {
    border: none;
    background: #ffffff;
    padding: 16px 28px;
    box-shadow: 0 -1px 0 #eef1f6;
}

/* Hero banner */
.staff-hero {
    position: relative;
    padding: 24px 28px 78px 28px;
    background: linear-gradient(135deg, #4f46e5 0%, #2563eb 55%, #06b6d4 100%);
    color: #fff;
}
.staff-hero::after {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(800px 200px at 80% 0%, rgba(255,255,255,0.18), transparent 60%);
    pointer-events: none;
}
.staff-hero .hero-title {
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    opacity: 0.85;
    margin-bottom: 4px;
}
.staff-hero .hero-emp-code {
    font-size: 18px;
    font-weight: 700;
    letter-spacing: 0.5px;
}
.staff-hero .hero-close {
    position: absolute;
    top: 14px;
    right: 16px;
    z-index: 3;
    background: rgba(255,255,255,0.18);
    color: #fff;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s;
}
.staff-hero .hero-close:hover { background: rgba(255,255,255,0.32); }

/* Avatar with edit overlay */
.staff-hero-card {
    position: absolute;
    left: 28px;
    right: 28px;
    bottom: -46px;
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 8px 28px rgba(15, 23, 42, 0.12);
    padding: 14px 18px 14px 110px;
    min-height: 92px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    z-index: 2;
}
.staff-avatar-uploader {
    position: absolute;
    left: 18px;
    bottom: 14px;
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: #e0e7ff;
    border: 4px solid #fff;
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.25);
    overflow: hidden;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    font-weight: 700;
    color: #4f46e5;
    user-select: none;
}
.staff-avatar-uploader img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.staff-avatar-uploader .avatar-edit-overlay {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.18s;
    font-size: 13px;
    flex-direction: column;
    gap: 2px;
}
.staff-avatar-uploader:hover .avatar-edit-overlay { opacity: 1; }
.staff-avatar-uploader .avatar-edit-overlay i { font-size: 18px; }

.staff-avatar-actions {
    position: absolute;
    left: 18px;
    bottom: -12px;
    display: flex;
    gap: 4px;
}
.staff-avatar-actions .btn {
    border-radius: 999px;
    font-size: 11px;
    padding: 2px 10px;
    line-height: 1.4;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.staff-hero-name {
    font-size: 18px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
    margin: 0 0 4px 0;
}
.staff-hero-meta { font-size: 13px; color: #64748b; }
.staff-hero-meta .badge {
    background: #eef2ff;
    color: #4338ca;
    font-weight: 600;
    border-radius: 999px;
    padding: 3px 10px;
}
.staff-hero-meta .badge-status-active { background: #dcfce7; color: #166534; }
.staff-hero-meta .badge-status-inactive { background: #fee2e2; color: #991b1b; }

/* Pill tab navigation */
.staff-tab-nav {
    padding: 78px 28px 0 28px;
    background: #ffffff;
    display: flex;
    gap: 8px;
    border-bottom: 1px solid #eef1f6;
    overflow-x: auto;
    flex-wrap: nowrap;
}
.staff-tab-nav .nav-link {
    border: none;
    border-radius: 999px;
    padding: 8px 18px;
    color: #64748b;
    font-weight: 600;
    font-size: 13px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: transparent;
    transition: all 0.18s;
    white-space: nowrap;
}
.staff-tab-nav .nav-link i { font-size: 14px; }
.staff-tab-nav .nav-link:hover { background: #f1f5f9; color: #0f172a; }
.staff-tab-nav .nav-link.active {
    background: linear-gradient(135deg, #4f46e5, #2563eb);
    color: #fff;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}
.staff-tab-nav .nav-link.tab-invalid { color: #dc2626; }
.staff-tab-nav .nav-link.tab-invalid::after {
    content: '';
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #dc2626;
    margin-left: 4px;
}

/* Tab body padding */
.staff-tab-body { padding: 24px 28px 12px 28px; background: #fff; }

/* Floating labels styled */
#editStaffDetailsModal .form-floating > label {
    color: #94a3b8;
    font-size: 13px;
    padding: 14px 14px;
}
#editStaffDetailsModal .form-floating > .form-control,
#editStaffDetailsModal .form-floating > .form-select {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 14px 14px 8px 14px;
    min-height: 54px;
    background: #fff;
    transition: border-color 0.15s, box-shadow 0.15s;
}
#editStaffDetailsModal .form-floating > .form-control:focus,
#editStaffDetailsModal .form-floating > .form-select:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
#editStaffDetailsModal .form-floating > .form-control.is-invalid,
#editStaffDetailsModal .form-floating > .form-select.is-invalid {
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.10);
}
#editStaffDetailsModal .form-floating > .form-control:focus ~ label,
#editStaffDetailsModal .form-floating > .form-control:not(:placeholder-shown) ~ label,
#editStaffDetailsModal .form-floating > .form-select ~ label {
    color: #475569;
    font-weight: 500;
}
#editStaffDetailsModal .error-text { font-size: 12px; margin-top: 4px; display: block; }

/* Modern submit button */
.btn-modern-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #2563eb 100%);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 10px 26px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28);
    transition: transform 0.12s, box-shadow 0.12s;
}
.btn-modern-primary:hover {
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(37, 99, 235, 0.36);
}
.btn-modern-primary:disabled { opacity: 0.7; transform: none; }
.btn-modern-secondary {
    background: #f1f5f9;
    color: #475569;
    border: none;
    border-radius: 10px;
    padding: 10px 22px;
    font-weight: 600;
}
.btn-modern-secondary:hover { background: #e2e8f0; color: #0f172a; }


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
                            <div class="card-header bg-transparent border-0 pb-0">
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#inputFormModaladdstaffs">
                                        <i class="fa fa-plus"></i>&nbsp; Add New Staff
                                    </button>
                                </div>
                            </div>
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
                                            <td class="align-middle">
                                                <div class="d-flex align-items-center gap-2">
                                                    @if(!empty($user->profile_photo))
                                                        <img src="{{ asset($user->profile_photo) }}" class="staff-thumb" alt="">
                                                    @else
                                                        <div class="staff-thumb staff-thumb-placeholder">
                                                            {{ strtoupper(substr($user->full_name ?? $user->user_name, 0, 1)) }}
                                                        </div>
                                                    @endif
                                                    <div class="lh-sm">
                                                        <div class="fw-semibold">{{ $user->full_name ?: $user->user_name }}</div>
                                                        <div class="small text-muted">
                                                            @if($user->employee_code)
                                                                <span class="badge bg-light text-dark border me-1">{{ $user->employee_code }}</span>
                                                            @endif
                                                            <span>@</span>{{ $user->user_name }}
                                                        </div>
                                                        @if($user->designation)
                                                            <div class="small text-muted fst-italic">{{ $user->designation }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
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
                                                <a href="javascript:void(0);" class="editstaffdetails me-2"
                                                    data-user_id="{{ $user->s_id }}"
                                                    title="Edit User Details">
                                                    <i class="fa fa-pencil text-success cursor-pointer"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="editstaffdata me-2"
                                                    data-user_id="{{ $user->s_id }}"
                                                    data-bs-toggle="modal" data-bs-target="#inputFormModaleditstaffs"
                                                    title="Reset Password">
                                                    <i class="fa fa-key text-primary cursor-pointer"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="getUserHistory me-2"
                                                    data-user_id="{{ $user->s_id }}"
                                                    data-user_name="{{ $user->user_name }}"
                                                    data-bs-toggle="modal" data-bs-target="#userHistoryModal"
                                                    title="Form History">
                                                    <i class="fa fa-history text-primary cursor-pointer"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="deletestaff"
                                                    data-user_id="{{ $user->s_id }}"
                                                    data-user_name="{{ $user->user_name }}"
                                                    title="Delete User">
                                                    <i class="fa fa-trash text-danger cursor-pointer"></i>
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
                                <h5 class="modal-title">User Form History &nbsp;<small class="text-muted" id="historyUserName"></small></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <table class="table table-bordered table-sm" id="historyTable">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Form Type</th>
                                            <th>Assigned Forms</th>
                                            <th>Action</th>
                                            <th>Status</th>
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
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
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
                                    @csrf

                                    <div class="accordion staff-accordion" id="addStaffAccordion">

                                        <!-- Section 1: Login & Role -->
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#addSec1">
                                                    <i class="fa fa-id-card-o me-2 text-primary"></i> Login &amp; Role <span class="text-danger ms-1">*</span>
                                                </button>
                                            </h2>
                                            <div id="addSec1" class="accordion-collapse collapse show" data-bs-parent="#addStaffAccordion">
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-2">
                                                            <label for="add_staff_name" class="form-label">User Name<span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="staff_name" id="add_staff_name" autocomplete="off" placeholder="Login handle (e.g. supervisor1)">
                                                            <small class="text-danger error-text" data-error="staff_name"></small>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <label for="add_role_id" class="form-label">User Role<span class="text-danger">*</span></label>
                                                            <select class="form-select" name="role_id" id="add_role_id">
                                                                <option value="">Please select the user role</option>
                                                                @foreach ($userRoles as $item)
                                                                    <option value="{{ $item->r_id }}">{{ $item->role_name }}</option>
                                                                @endforeach
                                                            </select>
                                                            <small class="text-danger error-text" data-error="role_id"></small>
                                                        </div>

                                                        <div class="col-md-12 mb-2">
                                                            <label for="add_staff_email" class="form-label">User Login Email<span class="text-danger">*</span></label>
                                                            <input type="email" class="form-control" name="staff_email" id="add_staff_email" autocomplete="off" placeholder="user@tnelb.com">
                                                            <small class="text-danger error-text" data-error="staff_email"></small>
                                                        </div>

                                                        <div class="col-md-6 mb-3">
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <label class="form-label mb-0" for="add_user_password">Password <span class="text-danger">*</span></label>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnGeneratePassword" title="Generate a strong random password">
                                                                    <i class="fa fa-magic me-1"></i>Generate
                                                                </button>
                                                            </div>
                                                            <div class="input-group mt-1">
                                                                <input type="text" class="form-control" name="user_random_pass" id="add_user_password" placeholder="Enter password" autocomplete="new-password">
                                                                <span class="input-group-text cursor-pointer toggle-password" data-target="#add_user_password" title="Show / hide password">
                                                                    <i class="fa fa-eye"></i>
                                                                </span>
                                                            </div>
                                                            <small class="text-muted">Min 8 chars: uppercase, number &amp; special char.</small><br>
                                                            <small class="text-danger error-text" data-error="user_random_pass"></small>
                                                        </div>

                                                        <div class="col-md-6 mb-3">
                                                            <label class="form-label" for="add_user_password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" name="user_random_pass_confirmation" id="add_user_password_confirmation" placeholder="Re-enter password" autocomplete="new-password">
                                                                <span class="input-group-text cursor-pointer toggle-password" data-target="#add_user_password_confirmation" title="Show / hide password">
                                                                    <i class="fa fa-eye"></i>
                                                                </span>
                                                            </div>
                                                            <small class="text-danger error-text" data-error="user_random_pass_confirmation"></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section 2: Personal Information -->
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#addSec2">
                                                    <i class="fa fa-user-o me-2 text-success"></i> Personal Information
                                                </button>
                                            </h2>
                                            <div id="addSec2" class="accordion-collapse collapse" data-bs-parent="#addStaffAccordion">
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-2">
                                                            <label for="add_full_name" class="form-label">Full Name<span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="full_name" id="add_full_name" placeholder="As per official records">
                                                            <small class="text-danger error-text" data-error="full_name"></small>
                                                        </div>
                                                        <div class="col-md-3 mb-2">
                                                            <label for="add_date_of_birth" class="form-label">Date of Birth</label>
                                                            <input type="date" class="form-control" name="date_of_birth" id="add_date_of_birth">
                                                            <small class="text-danger error-text" data-error="date_of_birth"></small>
                                                        </div>
                                                        <div class="col-md-3 mb-2">
                                                            <label for="add_gender" class="form-label">Gender</label>
                                                            <select class="form-select" name="gender" id="add_gender">
                                                                <option value="">-- Select --</option>
                                                                <option value="M">Male</option>
                                                                <option value="F">Female</option>
                                                                <option value="O">Other</option>
                                                            </select>
                                                            <small class="text-danger error-text" data-error="gender"></small>
                                                        </div>

                                                        <div class="col-md-4 mb-2">
                                                            <label for="add_mobile" class="form-label">Mobile Number<span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="mobile" id="add_mobile" maxlength="15" placeholder="10-digit mobile">
                                                            <small class="text-danger error-text" data-error="mobile"></small>
                                                        </div>
                                                        <div class="col-md-4 mb-2">
                                                            <label for="add_alt_phone" class="form-label">Alternate Phone</label>
                                                            <input type="text" class="form-control" name="alt_phone" id="add_alt_phone" maxlength="15" placeholder="Optional">
                                                            <small class="text-danger error-text" data-error="alt_phone"></small>
                                                        </div>
                                                        <div class="col-md-4 mb-2">
                                                            <label for="add_profile_photo" class="form-label">Profile Photo</label>
                                                            <input type="file" class="form-control" name="profile_photo" id="add_profile_photo" accept="image/jpeg,image/png,image/webp">
                                                            <small class="text-muted">JPG/PNG/WEBP &middot; max 2 MB</small>
                                                            <div class="mt-2" id="add_profile_photo_preview"></div>
                                                            <small class="text-danger error-text" data-error="profile_photo"></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section 3: Work Information -->
                                        <div class="accordion-item">
                                            <h2 class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#addSec3">
                                                    <i class="fa fa-briefcase me-2 text-warning"></i> Work Information <span class="text-danger ms-1">*</span>
                                                </button>
                                            </h2>
                                            <div id="addSec3" class="accordion-collapse collapse" data-bs-parent="#addStaffAccordion">
                                                <div class="accordion-body">
                                                    <div class="row">
                                                        <div class="col-md-6 mb-2">
                                                            <label class="form-label">Employee Code</label>
                                                            <input type="text" class="form-control bg-light" value="Auto-generated on save (TNELB-EMP-####)" disabled>
                                                            <small class="text-muted">Generated by the system on creation.</small>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <label for="add_designation" class="form-label">Designation<span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="designation" id="add_designation" placeholder="e.g. Supervisor, Auditor">
                                                            <small class="text-danger error-text" data-error="designation"></small>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <label for="add_joining_date" class="form-label">Joining Date<span class="text-danger">*</span></label>
                                                            <input type="date" class="form-control" name="joining_date" id="add_joining_date">
                                                            <small class="text-danger error-text" data-error="joining_date"></small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="modal-footer justify-content-center">
                                        <button type="button" class="btn btn-light-danger mt-2 mb-2 btn-no-effect" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" class="btn btn-primary mt-2 mb-2 btn-no-effect">Add Staff</button>
                                    </div>
                                </form>
                            </div>
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
                                    <input type="hidden" name="user_id" id="reset_user_id">
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

                <!-- -------- Edit Staff Details (Modern) ------- -->
                <div class="modal fade" id="editStaffDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                        <div class="modal-content">

                            <form id="editStaffDetailsForm" novalidate enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="user_id" id="edit_user_id">
                                <input type="hidden" name="remove_photo" id="edit_remove_photo" value="0">
                                <input type="file" name="profile_photo" id="edit_profile_photo" accept="image/jpeg,image/png,image/webp" class="d-none">

                                <div class="modal-header">
                                    <!-- Hero banner -->
                                    <div class="staff-hero w-100">
                                        <button type="button" class="hero-close" data-bs-dismiss="modal" aria-label="Close">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                                <line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                        <div class="hero-title">Edit Staff Profile</div>
                                        <div class="hero-emp-code" id="edit_hero_emp_code">&mdash;</div>

                                        <!-- Floating profile card -->
                                        <div class="staff-hero-card">
                                            <div class="staff-avatar-uploader" id="editAvatarUploader" title="Click to change photo">
                                                <span id="edit_avatar_initial">U</span>
                                                <img id="edit_avatar_img" src="" alt="" style="display:none;">
                                                <div class="avatar-edit-overlay">
                                                    <i class="fa fa-camera"></i>
                                                    <span>Change</span>
                                                </div>
                                            </div>
                                            <div class="staff-avatar-actions">
                                                <button type="button" class="btn btn-light btn-sm" id="btnRemoveEditPhoto" style="display:none;" title="Remove photo">
                                                    <i class="fa fa-trash text-danger"></i>
                                                </button>
                                            </div>
                                            <div class="staff-hero-name" id="edit_hero_name">&mdash;</div>
                                            <div class="staff-hero-meta">
                                                <span class="badge" id="edit_hero_role">Role</span>
                                                <span class="badge ms-1" id="edit_hero_status">Status</span>
                                                <span class="ms-2 text-truncate" id="edit_hero_email" style="vertical-align: middle;">&mdash;</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-body">
                                    <!-- Pill tab navigation -->
                                    <ul class="nav staff-tab-nav" role="tablist" id="editStaffTabs">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#editTab1" data-tab-key="login">
                                                <i class="fa fa-id-card-o"></i> Login &amp; Role
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#editTab2" data-tab-key="personal">
                                                <i class="fa fa-user-o"></i> Personal
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#editTab3" data-tab-key="work">
                                                <i class="fa fa-briefcase"></i> Work
                                            </button>
                                        </li>
                                    </ul>

                                    <div class="tab-content staff-tab-body" id="editStaffTabContent">

                                        <!-- Tab 1: Login & Role -->
                                        <div class="tab-pane fade show active" id="editTab1" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" name="staff_name" id="edit_staff_name" placeholder=" " autocomplete="off">
                                                        <label for="edit_staff_name">User Name *</label>
                                                    </div>
                                                    <small class="text-danger error-text" data-error="staff_name"></small>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <select class="form-select" name="role_id" id="edit_role_id">
                                                            <option value="">-- select --</option>
                                                            @foreach ($userRoles as $item)
                                                                <option value="{{ $item->r_id }}">{{ $item->role_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <label for="edit_role_id">User Role *</label>
                                                    </div>
                                                    <small class="text-danger error-text" data-error="role_id"></small>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="form-floating">
                                                        <input type="email" class="form-control" name="staff_email" id="edit_staff_email" placeholder=" " autocomplete="off">
                                                        <label for="edit_staff_email">User Login Email *</label>
                                                    </div>
                                                    <small class="text-danger error-text" data-error="staff_email"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tab 2: Personal -->
                                        <div class="tab-pane fade" id="editTab2" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" name="full_name" id="edit_full_name" placeholder=" ">
                                                        <label for="edit_full_name">Full Name *</label>
                                                    </div>
                                                    <small class="text-danger error-text" data-error="full_name"></small>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-floating">
                                                        <input type="date" class="form-control" name="date_of_birth" id="edit_date_of_birth" placeholder=" ">
                                                        <label for="edit_date_of_birth">Date of Birth</label>
                                                    </div>
                                                    <small class="text-danger error-text" data-error="date_of_birth"></small>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-floating">
                                                        <select class="form-select" name="gender" id="edit_gender">
                                                            <option value="">--</option>
                                                            <option value="M">Male</option>
                                                            <option value="F">Female</option>
                                                            <option value="O">Other</option>
                                                        </select>
                                                        <label for="edit_gender">Gender</label>
                                                    </div>
                                                    <small class="text-danger error-text" data-error="gender"></small>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" name="mobile" id="edit_mobile" maxlength="15" placeholder=" ">
                                                        <label for="edit_mobile">Mobile Number *</label>
                                                    </div>
                                                    <small class="text-danger error-text" data-error="mobile"></small>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" name="alt_phone" id="edit_alt_phone" maxlength="15" placeholder=" ">
                                                        <label for="edit_alt_phone">Alternate Phone</label>
                                                    </div>
                                                    <small class="text-danger error-text" data-error="alt_phone"></small>
                                                </div>

                                                <div class="col-md-12">
                                                    <small class="text-muted">Profile photo can be changed by clicking the avatar at the top.</small>
                                                    <small class="text-danger error-text d-block" data-error="profile_photo"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Tab 3: Work -->
                                        <div class="tab-pane fade" id="editTab3" role="tabpanel">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control bg-light" id="edit_employee_code" readonly placeholder=" ">
                                                        <label for="edit_employee_code">Employee Code (auto)</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="text" class="form-control" name="designation" id="edit_designation" placeholder=" ">
                                                        <label for="edit_designation">Designation *</label>
                                                    </div>
                                                    <small class="text-danger error-text" data-error="designation"></small>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-floating">
                                                        <input type="date" class="form-control" name="joining_date" id="edit_joining_date" placeholder=" ">
                                                        <label for="edit_joining_date">Joining Date *</label>
                                                    </div>
                                                    <small class="text-danger error-text" data-error="joining_date"></small>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-modern-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-modern-primary">
                                        <i class="fa fa-check me-1"></i> Save Changes
                                    </button>
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

    /* ============================================================
     *  Helpers
     * ============================================================ */
    function clearFormErrors($form) {
        $form.find('.error-text').text('');
        $form.find('.is-invalid').removeClass('is-invalid');
    }

    function showFormErrors($form, errors) {
        clearFormErrors($form);
        var firstInvalidField = null;
        var invalidTabPanes = new Set();

        $.each(errors, function (field, msgs) {
            var msg = Array.isArray(msgs) ? msgs[0] : msgs;
            var $err = $form.find('.error-text[data-error="' + field + '"]');
            if ($err.length) {
                $err.text(msg);
            }
            var $input = $form.find('[name="' + field + '"]').first();
            if ($input.length) {
                $input.addClass('is-invalid');
                if (!firstInvalidField) firstInvalidField = $input;

                var $pane = $input.closest('.tab-pane');
                if ($pane.length) invalidTabPanes.add($pane.attr('id'));
            }
        });

        // Flag tab triggers with invalid fields (red dot)
        $form.find('.staff-tab-nav .nav-link').removeClass('tab-invalid');
        invalidTabPanes.forEach(function (paneId) {
            $form.find('.staff-tab-nav .nav-link[data-bs-target="#' + paneId + '"]').addClass('tab-invalid');
        });

        if (firstInvalidField) {
            // 1) Open collapsed accordion section (used by Add modal)
            var $collapse = firstInvalidField.closest('.accordion-collapse');
            if ($collapse.length && !$collapse.hasClass('show')) {
                bootstrap.Collapse.getOrCreateInstance($collapse[0], { toggle: false }).show();
            }

            // 2) Switch tab if invalid field is in a hidden pane (used by Edit modal)
            var $pane = firstInvalidField.closest('.tab-pane');
            if ($pane.length && !$pane.hasClass('active')) {
                var paneId = $pane.attr('id');
                var $trigger = $form.find('[data-bs-target="#' + paneId + '"]').first();
                if ($trigger.length) {
                    bootstrap.Tab.getOrCreateInstance($trigger[0]).show();
                }
            }

            setTimeout(function () {
                firstInvalidField.focus();
            }, 300);
        }
    }

    function previewImageInto($previewEl, file) {
        if (!file) { $previewEl.empty(); return; }
        var reader = new FileReader();
        reader.onload = function (e) {
            $previewEl.html(
                '<div class="profile-photo-preview-wrap">' +
                    '<img src="' + e.target.result + '" class="profile-photo-preview" alt="preview">' +
                    '<small class="text-success">' + $('<div>').text(file.name).html() + '</small>' +
                '</div>'
            );
        };
        reader.readAsDataURL(file);
    }

    function generateStrongPassword(length) {
        length = length || 12;
        var upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        var lower = 'abcdefghijkmnpqrstuvwxyz';
        var digit = '23456789';
        var sym   = '@#$%&*!?';
        var all   = upper + lower + digit + sym;
        var pwd = [
            upper.charAt(Math.floor(Math.random() * upper.length)),
            lower.charAt(Math.floor(Math.random() * lower.length)),
            digit.charAt(Math.floor(Math.random() * digit.length)),
            sym.charAt(Math.floor(Math.random() * sym.length))
        ];
        for (var i = pwd.length; i < length; i++) {
            pwd.push(all.charAt(Math.floor(Math.random() * all.length)));
        }
        return pwd.sort(function () { return Math.random() - 0.5; }).join('');
    }

    /* ============================================================
     *  Reset Password modal – populate hidden user_id on open
     * ============================================================ */
    $(document).on('click', '.editstaffdata', function () {
        $('#reset_user_id').val($(this).data('user_id'));
        $('#user_passwd, #confirmPasswd').val('');
        $('#passwordError, #confirmError').addClass('d-none').text('');
    });

    /* ============================================================
     *  ADD NEW STAFF
     * ============================================================ */
    $('#btnGeneratePassword').on('click', function () {
        var pwd = generateStrongPassword(12);
        $('#add_user_password').val(pwd);
        $('#add_user_password_confirmation').val(pwd);
        $('#newstaffmaster').find('.error-text[data-error="user_random_pass"], .error-text[data-error="user_random_pass_confirmation"]').text('');
        $('#newstaffmaster').find('[name="user_random_pass"], [name="user_random_pass_confirmation"]').removeClass('is-invalid');
    });

    $('#inputFormModaladdstaffs').on('hidden.bs.modal', function () {
        var $form = $('#newstaffmaster');
        $form[0].reset();
        clearFormErrors($form);
        $('#add_profile_photo_preview').empty();
        // Re-open section 1 by default
        var sec1 = document.getElementById('addSec1');
        var sec2 = document.getElementById('addSec2');
        var sec3 = document.getElementById('addSec3');
        if (sec1) bootstrap.Collapse.getOrCreateInstance(sec1, { toggle: false }).show();
        if (sec2) bootstrap.Collapse.getOrCreateInstance(sec2, { toggle: false }).hide();
        if (sec3) bootstrap.Collapse.getOrCreateInstance(sec3, { toggle: false }).hide();
    });

    $('#add_profile_photo').on('change', function () {
        previewImageInto($('#add_profile_photo_preview'), this.files && this.files[0]);
    });

    $('#newstaffmaster').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        clearFormErrors($form);

        var pwd  = $('#add_user_password').val();
        var cpwd = $('#add_user_password_confirmation').val();
        if (pwd && cpwd && pwd !== cpwd) {
            showFormErrors($form, { user_random_pass_confirmation: 'Passwords do not match.' });
            return;
        }

        var $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true);

        var formData = new FormData(this);

        $.ajax({
            url: BASE_URL + '/admin/staff/insertstaff',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        })
        .done(function (res) {
            if (!res || res.status !== true) {
                Swal.fire('Error', (res && res.message) || 'Unable to add staff.', 'error');
                return;
            }

            var email = res.user.user_email;
            var uname = res.user.user_name;
            var empCode = res.user.employee_code || '';
            var html =
                '<div class="staff-success-card text-start">' +
                  '<div class="staff-id-box">' +
                    '<small>EMPLOYEE CODE</small>' +
                    '<h4>' + $('<div>').text(empCode).html() + '</h4>' +
                    '<div class="text-dark"><b>' + $('<div>').text(uname).html() + '</b></div>' +
                    '<div class="text-muted small">' + $('<div>').text(email).html() + '</div>' +
                  '</div>' +
                  '<div class="login-note-muted">Share the credentials securely with the user.</div>' +
                  '<div><b>Password:</b> <code id="newStaffPwd">' + $('<div>').text(pwd).html() + '</code> ' +
                    '<button type="button" class="copy-btn" id="copyNewStaffPwd">Copy</button></div>' +
                '</div>';

            Swal.fire({
                icon: 'success',
                title: 'Staff Added',
                html: html,
                customClass: { popup: 'swal-staff-success' },
                confirmButtonText: 'Done',
                didOpen: function () {
                    $('#copyNewStaffPwd').on('click', function () {
                        navigator.clipboard && navigator.clipboard.writeText(pwd);
                        $(this).text('Copied!');
                        var self = this;
                        setTimeout(function () { $(self).text('Copy'); }, 1500);
                    });
                }
            }).then(function () { location.reload(); });
        })
        .fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                showFormErrors($form, xhr.responseJSON.errors);
            } else {
                Swal.fire('Error', (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.', 'error');
            }
        })
        .always(function () {
            $btn.prop('disabled', false);
        });
    });

    /* ============================================================
     *  EDIT STAFF DETAILS  (Name / Email / Role)
     * ============================================================ */
    /* Modern Edit-Staff modal — helpers for the hero avatar */
    function setEditAvatar(srcOrInitial) {
        var $img = $('#edit_avatar_img');
        var $ini = $('#edit_avatar_initial');
        if (srcOrInitial && srcOrInitial.startsWith && (srcOrInitial.startsWith('data:') || srcOrInitial.startsWith('http') || srcOrInitial.startsWith('/'))) {
            $img.attr('src', srcOrInitial).show();
            $ini.hide();
            $('#btnRemoveEditPhoto').show();
        } else {
            $img.attr('src', '').hide();
            $ini.text((srcOrInitial || 'U').charAt(0).toUpperCase()).show();
            $('#btnRemoveEditPhoto').hide();
        }
    }

    function setEditStatusBadge(status) {
        var $b = $('#edit_hero_status');
        $b.removeClass('badge-status-active badge-status-inactive');
        if (status === '1' || status === 1) {
            $b.text('Active').addClass('badge-status-active');
        } else {
            $b.text('Inactive').addClass('badge-status-inactive');
        }
    }

    $(document).on('click', '.editstaffdetails', function () {
        var userId = $(this).data('user_id');
        var $form = $('#editStaffDetailsForm');
        clearFormErrors($form);

        $.get(BASE_URL + '/admin/staff/' + userId + '/getstaff')
            .done(function (res) {
                if (!res || res.status !== true) {
                    Swal.fire('Error', (res && res.message) || 'Unable to load staff.', 'error');
                    return;
                }
                var u = res.user;

                $('#edit_user_id').val(u.s_id);
                $('#edit_remove_photo').val('0');

                // ---- Hero ----
                $('#edit_hero_emp_code').text(u.employee_code || '(new employee code on save)');
                $('#edit_hero_name').text(u.full_name || u.user_name || '—');
                $('#edit_hero_role').text(u.role_name || 'No role');
                $('#edit_hero_email').text(u.user_email || '');
                setEditStatusBadge(u.user_status);
                setEditAvatar(u.profile_photo_url || (u.full_name || u.user_name || 'U'));

                // ---- Tab 1: Login & Role ----
                $('#edit_staff_name').val(u.user_name || '');
                $('#edit_staff_email').val(u.user_email || '');
                $('#edit_role_id').val(u.role_id || '');

                // ---- Tab 2: Personal ----
                $('#edit_full_name').val(u.full_name || '');
                $('#edit_date_of_birth').val(u.date_of_birth || '');
                $('#edit_gender').val(u.gender || '');
                $('#edit_mobile').val(u.mobile || '');
                $('#edit_alt_phone').val(u.alt_phone || '');
                $('#edit_profile_photo').val('');

                // ---- Tab 3: Work ----
                $('#edit_employee_code').val(u.employee_code || '(generated on save)');
                $('#edit_designation').val(u.designation || '');
                $('#edit_joining_date').val(u.joining_date || '');

                // Reset to first tab
                var firstTabBtn = document.querySelector('#editStaffTabs .nav-link');
                if (firstTabBtn) bootstrap.Tab.getOrCreateInstance(firstTabBtn).show();

                var modalEl = document.getElementById('editStaffDetailsModal');
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            })
            .fail(function (xhr) {
                Swal.fire('Error', (xhr.responseJSON && xhr.responseJSON.message) || 'Unable to load staff.', 'error');
            });
    });

    // Avatar click → open the hidden file input
    $(document).on('click', '#editAvatarUploader', function () {
        $('#edit_profile_photo').trigger('click');
    });

    // Photo selected → preview inside the avatar
    $('#edit_profile_photo').on('change', function () {
        if (!this.files || !this.files[0]) return;
        $('#edit_remove_photo').val('0');
        var reader = new FileReader();
        reader.onload = function (e) { setEditAvatar(e.target.result); };
        reader.readAsDataURL(this.files[0]);
    });

    // Remove existing photo
    $(document).on('click', '#btnRemoveEditPhoto', function (e) {
        e.stopPropagation();
        $('#edit_remove_photo').val('1');
        $('#edit_profile_photo').val('');
        setEditAvatar($('#edit_hero_name').text() || 'U');
    });

    // Live-update hero card as the admin edits fields
    $('#edit_full_name').on('input', function () {
        var v = $(this).val();
        if (v) $('#edit_hero_name').text(v);
    });
    $('#edit_staff_email').on('input', function () { $('#edit_hero_email').text($(this).val() || ''); });
    $('#edit_role_id').on('change', function () {
        $('#edit_hero_role').text($(this).find('option:selected').text() || 'No role');
    });

    $('#editStaffDetailsForm').on('submit', function (e) {
        e.preventDefault();
        var $form = $(this);
        clearFormErrors($form);

        var $btn = $form.find('button[type="submit"]');
        $btn.prop('disabled', true);

        var formData = new FormData(this);

        $.ajax({
            url: BASE_URL + '/admin/staff/updatestaffdetails',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        })
        .done(function (res) {
            if (!res || res.status !== true) {
                Swal.fire('Error', (res && res.message) || 'Unable to update.', 'error');
                return;
            }
            Swal.fire({
                icon: 'success',
                title: 'Updated',
                text: res.message,
                timer: 1500,
                showConfirmButton: false
            }).then(function () { location.reload(); });
        })
        .fail(function (xhr) {
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                showFormErrors($form, xhr.responseJSON.errors);
            } else {
                Swal.fire('Error', (xhr.responseJSON && xhr.responseJSON.message) || 'Something went wrong.', 'error');
            }
        })
        .always(function () {
            $btn.prop('disabled', false);
        });
    });

    /* ============================================================
     *  DELETE STAFF  (soft delete with force-confirm flow)
     * ============================================================ */
    function requestDeleteStaff(userId, force) {
        return $.ajax({
            url: BASE_URL + '/admin/staff/deletestaff',
            method: 'POST',
            data: {
                user_id: userId,
                force_delete: force ? 1 : 0,
                _token: $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    $(document).on('click', '.deletestaff', function () {
        var userId = $(this).data('user_id');
        var userName = $(this).data('user_name') || 'this user';

        Swal.fire({
            icon: 'warning',
            title: 'Delete user?',
            html: 'Are you sure you want to delete <b>' + $('<div>').text(userName).html() + '</b>?<br><small class="text-muted">This action will hide the user from the portal.</small>',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            confirmButtonColor: '#dc3545'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            requestDeleteStaff(userId, false)
                .done(function (res) {
                    if (res && res.status === true) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function () { location.reload(); });
                    } else {
                        Swal.fire('Error', (res && res.message) || 'Unable to delete user.', 'error');
                    }
                })
                .fail(function (xhr) {
                    var res = xhr.responseJSON || {};
                    if (xhr.status === 422 && res.needs_confirmation === true) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Forms Assigned',
                            text: res.message || 'Forms are assigned. Delete and clear all assigned forms?',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Delete & Clear',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#dc3545'
                        }).then(function (confirmResult) {
                            if (!confirmResult.isConfirmed) return;
                            requestDeleteStaff(userId, true)
                                .done(function (forceRes) {
                                    if (forceRes && forceRes.status === true) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Deleted',
                                            text: forceRes.message,
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(function () { location.reload(); });
                                    } else {
                                        Swal.fire('Error', (forceRes && forceRes.message) || 'Unable to delete user.', 'error');
                                    }
                                })
                                .fail(function (fxhr) {
                                    Swal.fire('Error', (fxhr.responseJSON && fxhr.responseJSON.message) || 'Something went wrong.', 'error');
                                });
                        });
                        return;
                    }
                    Swal.fire('Error', res.message || 'Something went wrong.', 'error');
                });
        });
    });

    /* ============================================================
     *  USER FORM HISTORY  (loads on #userHistoryModal show)
     * ============================================================ */
    $(document).on('click', '.getUserHistory', function () {
        var userId = $(this).data('user_id');
        var userName = $(this).data('user_name') || '';
        $('#historyUserName').text(userName ? '(' + userName + ')' : '');

        if (!window.historyTable) return;

        window.historyTable.clear().draw();
        window.historyTable.settings()[0].oLanguage.sEmptyTable = 'Loading...';

        $.get(BASE_URL + '/admin/staff/getUserHistory', { user_id: userId })
            .done(function (res) {
                if (!res || res.status !== true || !Array.isArray(res.data)) {
                    window.historyTable.settings()[0].oLanguage.sEmptyTable = 'No history found';
                    window.historyTable.clear().draw();
                    return;
                }
                var rows = res.data.map(function (r, i) {
                    var statusBadge = r.status_label === 'Active'
                        ? '<span class="badge bg-success">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>';
                    var actionBadge = r.action_status === 'START'
                        ? '<span class="badge bg-primary">START</span>'
                        : (r.action_status === 'STOP'
                            ? '<span class="badge bg-secondary">STOP</span>'
                            : '<span class="badge bg-info">' + $('<div>').text(r.action_status).html() + '</span>');
                    return [
                        i + 1,
                        $('<div>').text(r.form_type_label).html(),
                        $('<div>').text(r.form_names).html(),
                        actionBadge,
                        statusBadge,
                        $('<div>').text(r.started_at).html(),
                        $('<div>').text(r.ended_at).html()
                    ];
                });
                window.historyTable.settings()[0].oLanguage.sEmptyTable = 'No history found';
                window.historyTable.clear().rows.add(rows).draw();
            })
            .fail(function () {
                window.historyTable.settings()[0].oLanguage.sEmptyTable = 'Failed to load history';
                window.historyTable.clear().draw();
            });
    });

</script>

