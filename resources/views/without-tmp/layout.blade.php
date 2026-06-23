<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Without Temp Module')</title>
    <link href="{{ asset('assets/css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/font-awesome-4.7.0/css/font-awesome.min.css') }}" rel="stylesheet">
    <style>
        :root { --wt-primary: #00695c; }
        body { background: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-wt { background-color: var(--wt-primary) !important; }
        .navbar-wt .navbar-brand, .navbar-wt .nav-link { color: #fff !important; }
        .navbar-wt .nav-link.active { font-weight: 600; border-bottom: 2px solid #fff; }
        .page-title { color: var(--wt-primary); font-weight: 600; }
    </style>
    @stack('styles')
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark navbar-wt shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="{{ route('without-tmp.index') }}">
            <i class="fa fa-folder-o mr-2"></i> Without Temp Module
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#wtNavbar">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="wtNavbar">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('without-tmp.index') ? 'active' : '' }}" href="{{ route('without-tmp.index') }}">
                        <i class="fa fa-dashboard mr-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('without-tmp.alteration*') ? 'active' : '' }}" href="{{ route('without-tmp.alteration') }}">
                        <i class="fa fa-exchange mr-1"></i> Alteration
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('without-tmp.review*') ? 'active' : '' }}" href="{{ route('without-tmp.review') }}">
                        <i class="fa fa-check-square-o mr-1"></i> Review
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('without-tmp.storage') ? 'active' : '' }}" href="{{ route('without-tmp.storage') }}">
                        <i class="fa fa-folder-open mr-1"></i> Storage
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('without-tmp.table-data') ? 'active' : '' }}" href="{{ route('without-tmp.table-data') }}">
                        <i class="fa fa-table mr-1"></i> Table Data
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @yield('content')
</div>

<script src="{{ asset('assets/js/jquery.js') }}"></script>
<script src="{{ asset('assets/js/popper.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
@stack('scripts')
</body>
</html>
