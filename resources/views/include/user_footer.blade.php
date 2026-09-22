    </div>
    <footer class="main-footer d-flex justify-content-between px-4 py-3">
        <div class="footer-left">
            <a href="">Test</a>
        </div>
        <div class="footer-right">
            <a href="">Test</a>
        </div>
    </footer>
    <div class="scroll-to-top scroll-to-target" data-target="html"><span class="icon-arrow"></span></div>
</body>
<!--Scroll to top-->

<script src="{{ url('assets/js/jquery.js') }}"></script>
<script src="{{ url('assets/js/popper.min.js') }}"></script>
<script src="{{ url('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ url('assets/js/bootstrap-select.min.js') }}"></script>
<script src="{{ url('assets/js/jquery.fancybox.js') }}"></script>
<script src="{{ url('assets/js/isotope.js') }}"></script>
<script src="{{ url('assets/js/owl.js') }}"></script>
<script src="{{ url('assets/js/appear.js') }}"></script>
<script src="{{ url('assets/js/wow.js') }}"></script>
<script src="{{ url('assets/js/lazyload.js') }}"></script>
<script src="{{ url('assets/js/scrollbar.js') }}"></script>
<script src="{{ url('assets/js/TweenMax.min.js') }}"></script>
<script src="{{ url('assets/js/swiper.min.js') }}"></script>
<script src="{{ url('assets/js/jquery.polyglot.language.switcher.js') }}"></script>
<script src="{{ url('assets/js/jquery.ajaxchimp.min.js') }}"></script>
<script src="{{ url('assets/js/parallax-scroll.js') }}"></script>

<script src="{{ url('assets/js/script.js') }}"></script>
<!-- --------------------------------------------------------------- -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/mixitup/3.2.2/mixitup.min.js'></script>
<!-- fancybox -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js'></script>
<!-- Fancybox js -->
<script>
    /*Downloaded from https://www.codeseek.co/ezra_siton/mixitup-fancybox3-JydYqm */
    // 1. querySelector
    var containerEl = document.querySelector(".portfolio-item");
    // 2. Passing the configuration object inline
    //https://www.kunkalabs.com/mixitup/docs/configuration-object/
    var mixer = mixitup(containerEl, {
        animation: {
            effects: "fade translateZ(-100px)",
            effectsIn: "fade translateY(-100%)",
            easing: "cubic-bezier(0.645, 0.045, 0.355, 1)"
        }
    });
    // fancybox insilaze & options //
    $("[data-fancybox]").fancybox({
        loop: true,
        hash: true,
        transitionEffect: "slide",
        /* zoom VS next////////////////////
        clickContent - i modify the deafult - now when you click on the image you go to the next image - i more like this approach than zoom on desktop (This idea was in the classic/first lightbox) */
        clickContent: function(current, event) {
            return current.type === "image" ? "next" : false;
        }
    });
</script>


<script>
    $('a[data-toggle="formtab"]').click(function() {
        event.preventDefault();
        var targetId = $(this).attr('href');

        $('.tabs-panels').removeClass('active')
        $('a[data-toggle="formtab"]').removeClass('active');

        $(targetId).addClass('active');
        $('a[href="' + targetId + '"]').addClass('active')



    });
</script>


<script>

    // Run on load and resize
    window.addEventListener('load', setFooterPosition);
    window.addEventListener('resize', setFooterPosition);
</script>



<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.all.min.js"> </script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let NameInput = document.getElementById("Name");

        NameInput.addEventListener("input", function() {
            this.value = this.value.replace(/[^A-Za-z\s]/g, ''); // Only letters and spaces
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        let phoneInput = document.getElementById("PhoneNo");
        let phoneError = document.getElementById("PhoneNoError");

        phoneInput.addEventListener("input", function() {
            // Remove non-digits
            this.value = this.value.replace(/[^0-9]/g, '');

            // Limit to 10 digits
            if (this.value.length > 10) {
                this.value = this.value.slice(0, 10);
            }

            // Live validation
            if (this.value.length === 10) {
                if (!/^[6-9]\d{9}$/.test(this.value)) {
                    phoneError.textContent = "Enter a valid 10-digit mobile number starting with 6-9.";
                } else {
                    phoneError.textContent = "";
                }
            } else {
                phoneError.textContent = "";
            }
        });
    });
    document.addEventListener("DOMContentLoaded", function() {
        let aadhaarInput = document.getElementById("aadhaar");
        let aadhaarError = document.getElementById("aadhaarError");

        aadhaarInput.addEventListener("input", function() {
            this.value = this.value.replace(/[^0-9]/g, '');

            if (this.value.length > 12) {
                this.value = this.value.slice(0, 12);
            }

            if (this.value.length === 12) {
                aadhaarError.textContent = "";
            } else if (this.value.length > 0) {
                aadhaarError.textContent = "Aadhaar number must be exactly 12 digits.";
            } else {
                aadhaarError.textContent = "";
            }
        });
    });


    $(document).ready(function() {


        $("#form1").submit(function(event) {
            event.preventDefault();

            // Clear previous error messages
            $("#PhoneNoError").text("");
            $("#EmailError").text("");
            $("#NameError").text("");
            $("#GenderError").text("");
            $("#AddressError").text("");
            $("#StateError").text("");
            $("#DistrictError").text("");
            $("#PincodeError").text("");
            $("#aadhaarError").text("");
            $("#pancardError").text("");

            let name = $("#Name").val().trim();
            let gender = $("input[name='gender']:checked").val();
            let phone = $("#PhoneNo").val().trim();
            let email = $("#EmailAddress").val().trim();
            let address = $("#Address").val().trim();
            let state = $("#state").val();
            let district = $("#district").val();
            let pincode = $("#pincode").val().trim();

            let aadhaar = $("#aadhaar").val().trim();
            let pancard = $("#pancard").val().trim();




            let formData = {
                _token: "{{ csrf_token() }}",
                Name: name,
                gender: gender,
                PhoneNo: phone,
                EmailAddress: email,
                Address: address,
                state: state,
                district: district,
                pincode: pincode,
                aadhaar: aadhaar,
                pancard: pancard,
            };

            $.ajax({
                type: "POST",
                url: "{{ route('register.store') }}",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $("#login-id-display").text(response.login_id);
                        $("#success-popup").fadeIn();
                        $("#overlay").fadeIn();
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let errors = xhr.responseJSON.errors;

                        if (errors.Name) {
                            $("#NameError").text(errors.Name[0]);
                        }
                        if (errors.gender) {
                            $("#GenderError").text(errors.gender[0]);
                        }
                        if (errors.PhoneNo) {
                            $("#PhoneNoError").text(errors.PhoneNo[0]);
                        }
                        if (errors.EmailAddress) {
                            $("#EmailError").text(errors.EmailAddress[0]);
                        }
                        if (errors.Address) {
                            $("#AddressError").text(errors.Address[0]);
                        }
                        if (errors.state) {
                            $("#StateError").text(errors.state[0]);
                        }
                        if (errors.district) {
                            $("#DistrictError").text(errors.district[0]);
                        }
                        if (errors.pincode) {
                            $("#PincodeError").text(errors.pincode[0]);
                        }

                        if (errors.aadhaar) {
                            $("#aadhaarError").text(errors.aadhaar[0]);
                        }
                        if (errors.pancard) {
                            $("#pancardError").text(errors.pancard[0]);
                        }
                    }
                }
            });
        });
    });

    $(document).ready(function() {
        $("#login-form").submit(function(event) {
            event.preventDefault(); // Prevent form submission

            let phone = $("#phone").val().trim();
            let captcha = $("input[name='captcha']").val().trim();
            let errors = [];



            // if (phone === "" || !/^\d{10}$/.test(phone)) {
            //     errors.push("Enter a valid 10-digit mobile number.");
            // }

            if (captcha === "") {
                errors.push("CAPTCHA is required.");
            }

            if (errors.length > 0) {
                alert(errors.join("\n"));
                return;
            }

            $.ajax({
                type: "POST",
                url: "{{ route('login.check') }}", // Laravel route
                data: {
                    _token: "{{ csrf_token() }}",
                    phone: phone,
                    captcha: captcha
                },
                success: function(response) {
                    if (response.success) {
                        // alert("Login successful!");
                        // window.location.href = "/dashboard";

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

            const validateBtn = document.getElementById('validateBtn');
            validateBtn.addEventListener('click', function() {
                let otp = '';
                document.querySelectorAll('.otp-inputs > input').forEach(input => otp += input.value);

                // If OTP is correct, redirect user to the login page
                if (otp === '123456') {
                    window.location.href = "{{ route('user_login') }}";

                } else {
                    alert("Incorrect OTP, please try again.");
                }
            });
        });


        // Refresh CAPTCHA
        $("#refresh-captcha").click(function(event) {
            event.preventDefault();
            $("#image-captcha").attr("src", "{{ url('captcha/image') }}?rand=" + Math.random());
        });
    });

    let profile = document.querySelector('.profile');
    let menu = document.querySelector('.menu');

    profile.onclick = function() {
        menu.classList.toggle('active');
    }
</script>


<script type="text/javascript">
        var refreshButton = document.getElementById("refresh-captcha");
        var captchaImage = document.getElementById("image-captcha");

        refreshButton.onclick = function(event) {
            event.preventDefault();
            captchaImage.src = './captcha/image.php?' + Date.now();
        };

        $("#contact-form").submit(function(e) {

            // console.log('asd');


            e.preventDefault(); // avoid to execute the actual submit of the form.

            // var phone = $("#phone").val();

            var phone = $('input[name="phone"]').val();
            // return false;


            intRegex = /[0-9 -()+]+$/;

            if ((phone.length < 10) || (!intRegex.test(phone))) {
                alert('Please enter a valid phone number.');
                return false;
            }


            if (phone.length !== 0) {
                $('#otp_card').removeAttr("style");


            }
            console.log(phone);
            return false;

            // $.ajax({
            //     type: "POST",
            //     url: '',
            //     data: form.serialize(), // serializes the form's elements.
            //     success: function(data)
            //     {
            //     alert(data); // show response from the php script.
            //     }
            // });

        });

        document.addEventListener("DOMContentLoaded", function() {

            function OTPInput() {
                const inputs = document.querySelectorAll('.otp-inputs > input');

                inputs.forEach((input, i) => {
                    input.addEventListener('input', function() {
                        if (this.value.length > 1) {
                            this.value = this.value[0];
                        }
                        if (this.value !== '' && i < inputs.length - 1) {
                            inputs[i + 1].focus();
                        }
                    });

                    input.addEventListener('keydown', function(event) {
                        if (event.key === 'Backspace') {
                            this.value = '';
                            if (i > 0) {
                                inputs[i - 1].focus();
                            }
                        }
                    });
                });
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


        $("#contact-form").submit(function(e) {
            e.preventDefault(); // Avoid the form submission

            var phone = $('input[name="phone"]').val();

            var intRegex = /[0-9 -()+]+$/;

            if ((phone.length < 10) || (!intRegex.test(phone))) {
                alert('Please enter a valid phone number.');
                return false;
            }

            // Show OTP modal if the phone number is valid
            if (phone.length !== 0) {
                $('#otp-overlay').show();
                $('#overlay-bg').show();
            }

            return false;
        });

        // Function to handle OTP input behavior
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

            const validateBtn = document.getElementById('validateBtn');
            validateBtn.addEventListener('click', function() {
                let otp = '';
                document.querySelectorAll('.otp-inputs > input').forEach(input => otp += input.value);

                // If OTP is correct, redirect user to the login page
                if (otp === '123456') {
                    window.location.href = "{{ route('user_login') }}";

                } else {
                    alert("Incorrect OTP, please try again.");
                }
            });
        });

        $(document).ready(function() {
            $("#refresh-captcha").click(function(e) {
                e.preventDefault();
                $("#image-captcha").attr("src", "{{ url('captcha/image') }}?" + Math.random());
            });
        });
    </script>


<script>
    document.getElementById("filter-status-login").addEventListener("change", function(e) {
        const filter = e.target.value;

        // Filter projects
        document.querySelectorAll(".project-card-login").forEach(card => {
            if (filter === "all" || card.dataset.status === filter) {
                card.style.display = "block";
            } else {
                card.style.display = "none";
            }
        });

        // Filter tasks
        document.querySelectorAll(".table-login tbody tr").forEach(row => {
            if (filter === "all" || row.dataset.status === filter) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });

</script>
</html>
