<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Document Version Management')</title>

    {{-- Bootstrap 4 (project library) --}}
    <link href="{{ asset('assets/css/bootstrap.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/font-awesome-4.7.0/css/font-awesome.min.css') }}" rel="stylesheet">

    <style>
        :root { --tnelb-primary: #004185; }
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-dv { background-color: var(--tnelb-primary) !important; }
        .navbar-dv .navbar-brand,
        .navbar-dv .nav-link { color: #fff !important; }
        .navbar-dv .nav-link.active { font-weight: 600; border-bottom: 2px solid #fff; }
        .page-title { color: var(--tnelb-primary); font-weight: 600; }
        .table-dv thead th {
            background-color: var(--tnelb-primary);
            color: #fff;
            border-color: var(--tnelb-primary);
            vertical-align: middle;
        }
        .approval-stepper { display: flex; margin: 1rem 0; flex-wrap: wrap; }
        .approval-step {
            flex: 1;
            min-width: 150px;
            text-align: center;
            padding: 1rem .75rem;
            border: 1px solid #dee2e6;
            background: #fff;
        }
        .approval-step.completed { border-color: #28a745; background: #d4edda; }
        .approval-step.current { border-color: #ffc107; background: #fff3cd; font-weight: 600; }
        .approval-step.rejected { border-color: #dc3545; background: #f8d7da; }
        .approval-step.upcoming { opacity: .7; background: #f8f9fa; }
        .approval-step .step-num { font-size: .75rem; text-transform: uppercase; color: #6c757d; }
    </style>
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-dv shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('document-version.sample.index') }}">
                <i class="fa fa-files-o mr-2"></i> Document Version Module
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#dvNavbar"
                    aria-controls="dvNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="dvNavbar">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('document-version.sample.index') ? 'active' : '' }}"
                           href="{{ route('document-version.sample.index') }}">
                            <i class="fa fa-dashboard mr-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('document-version.sample.alteration*') ? 'active' : '' }}"
                           href="{{ route('document-version.sample.alteration') }}">
                            <i class="fa fa-exchange mr-1"></i> Alteration
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('document-version.sample.details*') ? 'active' : '' }}"
                           href="{{ route('document-version.sample.details') }}">
                            <i class="fa fa-user-circle mr-1"></i> Applicant Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('document-version.sample.storage') ? 'active' : '' }}"
                           href="{{ route('document-version.sample.storage') }}">
                            <i class="fa fa-folder-open mr-1"></i> Storage
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('document-version.sample.table-data') ? 'active' : '' }}"
                           href="{{ route('document-version.sample.table-data') }}">
                            <i class="fa fa-table mr-1"></i> Table Data
                        </a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('document-version.sample.upload*') ? 'active' : '' }}"
                           href="{{ route('document-version.sample.upload') }}">
                            <i class="fa fa-upload mr-1"></i> Upload
                        </a>
                    </li> --}}
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0 pl-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @yield('content')
    </div>

    <footer class="bg-white border-top py-3 mt-auto">
        <div class="container text-center text-muted small">
            Tamil Nadu Electrical Licencing Board — Document Version Sample Module
        </div>
    </footer>

    <script src="{{ asset('assets/js/jquery.js') }}"></script>
    <script src="{{ asset('assets/js/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script>
    @stack('scripts')
</body>
</html>
