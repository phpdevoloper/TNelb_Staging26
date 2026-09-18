<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">

    <!-- CSRF Token for AJAX Security -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'TNELB - Dashboard')</title>
    {{-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests"> --}}
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/admin/images/logo/logo.png') }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/src/font-icons/fontawesome/css/regular.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/src/font-icons/fontawesome/css/fontawesome.css') }}">

    <!-- Bootstrap & Core Styles -->
    <link rel="stylesheet" href="{{ asset('assets/admin/src/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/layouts/vertical-light-menu/css/light/plugins.css') }}">

    <!-- Loader Styles -->
    {{-- <link rel="stylesheet" href="{{ asset('assets/admin/layouts/vertical-light-menu/css/light/loader.css') }}">
    <script src="{{ asset('assets/admin/layouts/vertical-light-menu/loader.js') }}"></script> --}}

    <!-- Page-Level Plugins/Custom Styles -->
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/src/apex/apexcharts.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/assets/css/light/components/list-group.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/assets/css/light/dashboard/dash_1.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/assets/css/light/dashboard/dash_2.css') }}">

    <!-- File Upload Styles -->
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/src/filepond/filepond.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/src/filepond/FilePondPluginImagePreview.min.css') }}">

    <!-- Notifications & Alerts -->
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/src/notification/snackbar/snackbar.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/src/sweetalerts2/sweetalerts2.css') }}">

    <!-- Light Theme Styles -->
    {{-- <link rel="stylesheet" href="{{ asset('assets/admin/src/assets/css/light/scrollspyNav.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/src/assets/css/light/components/carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/assets/css/light/components/modal.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/assets/css/light/components/tabs.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/css/light/filepond/custom-filepond.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/assets/css/light/elements/alert.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/css/light/sweetalerts2/custom-sweetalert.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/css/light/notification/snackbar/custom-snackbar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/assets/css/light/forms/switches.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/assets/css/light/users/account-setting.css') }}">


    <!-- Datatables CSS -->
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/src/table/datatable/datatables.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/css/light/table/datatable/dt-global_style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/css/dark/table/datatable/dt-global_style.css') }}">

    <!-- Main Styles -->
    <link rel="stylesheet" href="{{ asset('assets/admin/main.css') }}">

    <!-- Light Theme Styles -->
    <link rel="stylesheet" href="{{ asset('assets/admin/src/assets/css/light/components/timeline.css') }}">

    
    <!-- Multi select -->
    <link rel="stylesheet" href="{{ asset('assets/admin/select2/dist/css/select2.min.css') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css"> 

    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/src/flatpickr/flatpickr.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/src/plugins/css/light/flatpickr/custom-flatpickr.css') }}">

</head>

<script>
    const BASE_URL = "{{ UrlHelper::baseFileUrl() }}";
</script>

<style>
    .competency-result-box {
    padding: 18px;
    border: 1px solid #cfd8e3;
    border-radius: 8px;
    background: #f8fafc;
}

.competency-result-title {
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #d9e0e8;
    color: #104484;
    font-size: 18px;
    font-weight: 600;
}

.competency-verified {
    display: inline-block;
    margin-bottom: 18px;
    padding: 5px 12px;
    border-radius: 20px;
    background: #d1e7dd;
    color: #146c43;
    font-size: 13px;
    font-weight: 600;
}

.competency-item {
    margin-bottom: 16px;
}

.competency-label {
    display: block;
    margin-bottom: 4px;
    color: #555;
    font-size: 13px;
    font-weight: 600;
}

.competency-value {
    display: block;
    color: #104484;
    font-size: 14px;
    font-weight: 500;
    word-break: break-word;
}

.competency-experience {
    padding: 12px 15px;
    border: 1px solid #b8d4ef;
    border-radius: 6px;
    background: #eef6ff;
}

.competency-experience .competency-label {
    color: #104484;
}

.competency-experience .competency-value {
    color: #198754;
    font-size: 16px;
    font-weight: 700;
}
</style>

<body class="layout-boxed">
    <!-- BEGIN LOADER -->   
    {{-- <div id="load_screen"> 
        <div class="loader"> 
            <div class="loader-content">
                <div class="spinner-grow align-self-center"></div>
            </div>
        </div>
    </div> --}}