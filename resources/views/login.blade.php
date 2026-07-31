@include('include.header')
<style>
    .height-100 {
        height: 80vh
    }

    .card {
        width: 400px;
        border: none;
        height: 300px;
        box-shadow: 0px 5px 20px 0px #d2dae3;
        z-index: 1;
        display: flex;
        justify-content: center;
        align-items: center
    }

    .card h6 {
        color: red;
        font-size: 20px
    }

    .inputs input {
        width: 40px;
        height: 40px
    }

    input[type=number]::-webkit-inner-spin-button,
    input[type=number]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        margin: 0
    }

    .card-2 {
        background-color: #fff;
        padding: 10px;
        width: 350px;
        height: 100px;
        bottom: -50px;
        left: 20px;
        position: absolute;
        border-radius: 5px
    }

    .card-2 .content {
        margin-top: 50px
    }

    .card-2 .content a {
        color: red
    }

    /* .form-control:focus {
        box-shadow: none;
        border: 2px solid red
    } */

    .validate {
        border-radius: 20px;
        height: 40px;
        background-color: #035ab3;
        border: 1px solid #035ab3;
        width: 140px
    }
</style>


<!-- About section -->

<section class="register-form">
    <div class="auto-container-form">
        <div class="wrapper-box">
            <div class="row">
                <div class=" offset-md-4 col-lg-4 col-12">
                    <div class="card1 register">
                        <div class="mb-3" data-select2-id="14">
                            <div class="card-header-login">
                                <h2>Applicant's Login Form</h2>
                            </div>
                        </div>
                        <form id="login-form" novalidate>
                            <div class="row">
                                <div class="form-group col-md-12">
                                    <label>Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" id="phone" name="phone" value="" class="form-control"
                                        placeholder="Enter Phone Number"
                                        inputmode="numeric" autocomplete="tel"
                                        maxlength="10">
                                    <span id="phoneError" class="text-danger"></span>
                                </div>

                            </div>

                            <div class="row">

                                <div class="form-group col-md-12">
                                    <img src="{{ captcha_src('flat') }}" alt="CAPTCHA" id="image-captcha">
                                    <a href="#" id="refresh-captcha" class="align-middle" title="refresh">
                                        <span class="fas fa-redo-alt align-middle" style="margin-left: 20px; color:#035ab3;"></span></a>
                                </div>
                                <div class="form-group col-md-12">
                                    <label>CAPTCHA <span class="text-danger">*</span></label>
                                    <input type="text" name="captcha" class="form-control" 
                                    placeholder="Enter CAPTCHA">
                                    <span id="captchaError" class="text-danger"></span>
                                </div>
                            </div>

                            <button type="submit">Submit</button>
                        </form>
                        <p class="text-md-right register_title mt-3"> <a href="{{route('register')}}" style="text-decoration: underline;"> Click to Register </a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<div class="separator50"></div>
<!-- OTP  -->
<!-- OTP Overlay -->
<div id="otp-overlay" class="otp-overlay" style="display: none;">
    <div class="otp-modal">
        <h6>Please enter the one-time password to verify your account</h6>
        <div class="otp-inputs d-flex justify-content-center">
            <input class="m-2 text-center form-control" type="text" maxlength="1" value="1" />
            <input class="m-2 text-center form-control" type="text" maxlength="1" value="2" />
            <input class="m-2 text-center form-control" type="text" maxlength="1" value="3" />
            <input class="m-2 text-center form-control" type="text" maxlength="1" value="4" />
            <input class="m-2 text-center form-control" type="text" maxlength="1" value="5" />
            <input class="m-2 text-center form-control" type="text" maxlength="1" value="6" />
        </div>
        <h5>OTP: 123456</h5>
        <div class="mt-4">
            <button id="validateBtn" class="btn btn-danger px-4">Validate</button>
        </div>
    </div>
</div>

<!-- Overlay Background -->
<div id="overlay-bg" class="overlay-bg" style="display: none;"></div>

<footer class="main-footer">

    @include('include.footer')

    <script>
        $(document).ready(function() {
            $("#phone").on("input", function () {
                this.value = this.value.replace(/\D/g, "").slice(0, 10);
                $("#phoneError").text("");
            });

            $("input[name='captcha']").on("input", function () {
                $("#captchaError").text("");
            });

            $("#login-form").submit(function(event) {
                event.preventDefault(); // Prevent form submission

                let phone = $("#phone").val().trim();
                let captcha = $("input[name='captcha']").val().trim();
                let errors = [];

                $("#phoneError").text('');
                $("#captchaError").text('');

                if (phone === '') {
                    errors.push("Phone number is required.");
                    $("#phoneError").text("Phone number is required.");
                } else if (!/^[6-9]\d{9}$/.test(phone)) {
                    errors.push("Enter a valid 10-digit phone number.");
                    $("#phoneError").text("Enter a valid 10-digit phone number.");
                }

                if (captcha === "") {
                    errors.push("CAPTCHA is required.");
                    $("#captchaError").text("CAPTCHA is required.");
                } 
                
                // else if (!/^[A-Za-z0-9]{6}$/.test(captcha)) {
                //     errors.push("Enter a valid CAPTCHA.");
                //     $("#captchaError").text("Enter a valid CAPTCHA.");
                // }

                // Stop here if any client-side error exists — AJAX will not run
                if (errors.length > 0) {
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: "{{ route('login.check') }}", 
                    data: {
                        _token: "{{ csrf_token() }}",
                        phone: phone,
                        captcha: captcha
                    },
                    success: function(response) {
                        if (response.success) {

                            $("#otp-overlay").fadeIn();
                            $("#overlay-bg").fadeIn();
                        }
                    },
                    error: function(xhr) {
                        let response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            $("#phone").after('<span class="text-danger">' + response.message + '</span>'); // Show error below input
                        } else {
                            alert("An error occurred. Please try again.");
                        }
                    }
                });
            });
        });

         document.addEventListener("DOMContentLoaded", function() {
            function OTPInput() {
                const inputs = document.querySelectorAll('.otp-inputs > input');
                for (let i = 0; i < inputs.length; i++) {
                    inputs[i].addEventListener('input', function() {
                        if (this.value.length > 1) {
                            this.value = this.value[0]; // Limit input to one character
                        }
                        if (this.value !== '' && i < inputs.length - 1) {
                            inputs[i + 1].focus(); // Move to the next input field
                        }
                    });

                    inputs[i].addEventListener('keydown', function(event) {
                        if (event.key === 'Backspace') {
                            this.value = '';
                            if (i > 0) {
                                inputs[i - 1].focus(); // Move to the previous input field on backspace
                            }
                        }
                    });
                }
            }

            OTPInput();

            $("#validateBtn").click(function() {
                let otp = "";
                $(".otp-inputs > input").each(function() {
                    otp += $(this).val();
                });

                if (otp.length !== 6) {
                    alert("Please enter a valid 6-digit OTP.");
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: "{{ route('login.verify') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        otp: otp
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.href = response.redirect_url;
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        const errMsg = xhr.responseJSON?.message || "Something went wrong.";
                        alert(errMsg);
                    }
                });
            });
        });

    </script>


