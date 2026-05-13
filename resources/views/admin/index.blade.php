    
    
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no">
        <title>TNELB - Home</title>
        <!-- <script src="https://kit.fontawesome.com/a076d05399.js"></script> -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

        <link rel="icon" type="image/x-icon" href="{{ asset('assets/admin/images/logo/logo.png') }}" />
        {{-- <link href="{{secure_asset('assets/admin/layouts/vertical-light-menu/css/light/loader.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{secure_asset('assets/admin/layouts/vertical-light-menu/css/dark/loader.css') }}" rel="stylesheet" type="text/css" />
        <script src="{{secure_asset('assets/admin/layouts/vertical-light-menu/loader.js') }}"></script> --}}
        <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700" rel="stylesheet">
        <link href="{{asset('assets/admin/src/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/admin/layouts/vertical-light-menu/css/light/plugins.css') }}" rel="stylesheet" type="text/css" />
        <link href="{{asset('assets/admin/src/assets/css/light/authentication/auth-cover.css') }}" rel="stylesheet" type="text/css" />
        <!-- END GLOBAL MANDATORY STYLES -->
        <link href="{{ asset('assets/admin/main.css') }}" rel="stylesheet" type="text/css" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">     --}}
        <style>
            .logo-img img{
                width: 150px;
            }
        </style>
    </head>

    <body class="form">
        <div class="auth-container d-flex">
            <div class="container mx-auto align-self-center">
                <div class="row">
                    <div class="col-6 d-lg-flex d-none h-100 my-auto top-0 start-0 text-center justify-content-center flex-column">
                        <div class="auth-cover-bg-image"></div>
                        <div class="auth-overlay"></div>
                        <div class="logo-img">
                            <div class="position-relative">
                                <img src="{{url('assets/admin/images/logo/logo.png') }}" alt="logo-img">
                                <h2 class="mt-5 text-white font-weight-bolder px-2">TamilNadu Electrical Licencing Board</h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-4 col-xl-5 col-lg-5 col-md-8 col-12 d-flex flex-column align-self-center ms-lg-auto me-lg-0 mx-auto">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <h2>Sign In</h2>
                                        <p>Enter your Username and  to login</p>
                                    </div>

                                    @if(session('a'))
                                    <div class="alert alert-success" style="color: #155724; background-color: #d4edda; padding: 10px; border-radius: 5px;">
                                        {{ session('a') }}
                                    </div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    {{-- Replace the action URL with your Laravel route --}}
                                    <form id="login-form">
                                        @csrf
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Username</label>
                                                <input type="text" name="username" id="username" class="form-control">
                                                <small class="text-danger" id="username-error"></small>
                                            </div>
                                        </div>
                                    
                                        <div class="col-12">
                                            <div class="mb-4">
                                                <label class="form-label">Password</label>
                                                <input type="password" name="password" id="password" class="form-control" value="">
                                                <small class="text-danger" id="password-error"></small>
                                            </div>
                                        </div>
                                    
                                        <div class="col-12">
                                            <div class="mb-4">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img id="captcha-image" src="{{ route('admin.custom.captcha') }}" alt="CAPTCHA" style="border: 1px solid #ddd; border-radius: 4px; padding: 5px;">
                                                    <a href="javascript:void(0);" id="refresh-captcha" class="captcha-refresh-link" title="Refresh CAPTCHA" aria-label="Refresh CAPTCHA">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    
                                        <div class="col-12">
                                            <div class="mb-4">
                                                <label class="form-label">Enter the captcha</label>
                                                <input type="text" name="captcha" id="captcha" class="form-control">
                                                <small class="text-danger" id="captcha-error"></small>
                                            </div>
                                        </div>
                                    
                                        <div class="col-12">
                                            <div class="mb-4">
                                                <button class="btn btn-secondary w-100" type="submit" id="login-btn">LOG IN</button>
                                            </div>
                                        </div>
                                    
                                        <div class="alert alert-danger d-none" id="error-message"></div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!-- BEGIN GLOBAL MANDATORY SCRIPTS -->
<script src="{{asset('assets/admin/src/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{asset('assets/admin/jquery-3.6.0.min.js') }}"></script>
<script src="{{asset('assets/admin/plugins/sha512/sha512.min.js') }}"></script>
<script src="{{asset('assets/admin/plugins/sha512/sha512.js') }}"></script>
<!-- <script src="{{url('assets/admin/plugins/jquery/jquery.min.js') }}"></script> -->
<script src="{{asset('assets/admin/custom.js') }}"></script>
<!-- END GLOBAL MANDATORY SCRIPTS -->



<script>
    $(document).ready(function () {
        $('#refresh-captcha').on('click', function(e) {
            e.preventDefault();
            $('#captcha-image').attr('src', '{{ route('admin.custom.captcha') }}?' + Date.now());
        });
        
    $('#login-form').on('submit', function (e) {
        e.preventDefault();

        var username = $('#username').val();
        var password = $('#password').val();
        var captcha = $("#captcha").val();

        $('#login-btn').prop('disabled', true).text('Logging in...');

        var data = {
            username: username,
            password: password,
            captcha: captcha
        };

        $.ajax({
            url: "{{ route('admin.login') }}",
            type: "POST",
            data: data,
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            xhrFields: {
                withCredentials: true
            },
            success: function (response) {
                window.location.href = "{{ route('admin.dashboard') }}";
            },
            error: function (xhr) {
                $('#login-btn').prop('disabled', false).text('LOG IN');
                $('.text-danger').text('');
                $('#error-message').addClass('d-none');

                var refreshCaptcha = false;

                if (xhr.status === 422 || xhr.status === 401 || xhr.status === 403) {
                    var errors = xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : {};
                    $.each(errors, function (key, value) {
                        var msg = Array.isArray(value) ? value[0] : value;
                        $('#' + key + '-error').text(msg);
                        if (String(msg).toLowerCase().includes('captcha')) {
                            refreshCaptcha = true;
                        }
                    });

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        $('#error-message').removeClass('d-none').text(xhr.responseJSON.message);
                        var m = xhr.responseJSON.message.toLowerCase();
                        if (m.includes('invalid') || m.includes('captcha') || m.includes('inactive')) {
                            refreshCaptcha = true;
                        }
                    }
                } else {
                    $('#error-message').removeClass('d-none').text('Invalid Credentials. Please try again.');
                    refreshCaptcha = true;
                }

                if (refreshCaptcha) {
                    $('#captcha-image').attr('src', '{{ route('admin.custom.captcha') }}?' + Date.now());
                    $('#captcha').val('');
                }
            }
        });
    });
});

</script>
    </body>

    </html>