$(document).ready(function () {
    let currentStep = 1;
    let otpVerified = false;
    let otpTimer = null;
    const csrf = "{{ csrf_token() }}";

    $.ajaxSetup({
        headers: { "X-CSRF-TOKEN": csrf }
    });


    // Shrink the card slightly if the visible area is too short, so the page never needs scrolling
    function fitCard() {
        const box = document.querySelector(".register-form > .auto-container");
        if (!box) return;
        box.style.zoom = 1;
        if (window.innerWidth < 768) return;
        const top = box.getBoundingClientRect().top + window.pageYOffset;
        const avail = window.innerHeight - top - 12;
        const z = Math.max(0.7, Math.min(1, avail / box.offsetHeight));
        box.style.zoom = z;
    }
    $(window).on("resize load", fitCard);

    function goToStep(step) {
        currentStep = step;

        $(".tnelb-wiz-panel").each(function () {
            const n = parseInt($(this).data("panel"), 10);
            $(this).toggleClass("is-active", n === step);
            // Locked panels stay visible (like the mockup) but can't be focused/clicked
            this.inert = (n !== step);
        });

        $(".tnelb-wiz-step").each(function (index) {
            const n = index + 1;
            $(this).toggleClass("is-active", n === step);
            $(this).toggleClass("is-done", n < step);
            $(this).find("span").html(n < step ? '<i class="fa fa-check"></i>' : n);
        });

        $("#wizLine1").css("width", step > 1 ? "100%" : "60%");
        $("#wizLine2").css("width", step > 2 ? "100%" : (step === 2 ? "60%" : "0"));
        fitCard();
    }

    function getOtpValue() {
        let otp = "";
        $(".otp-digit").each(function () { otp += $(this).val(); });
        return otp;
    }

    function setNextEnabled(on) {
        $("#step1Next, #step1NextMobile").prop("disabled", !on);
    }

    function startOtpTimer(seconds) {
        let remaining = seconds;
        $("#otpTimerWrap").show();
        $("#resendOtpBtn").hide().prop("disabled", true);
        function tick() {
            const mm = String(Math.floor(remaining / 60)).padStart(2, "0");
            const ss = String(remaining % 60).padStart(2, "0");
            $("#otpTimer").text(mm + ":" + ss);
            if (remaining <= 0) {
                clearInterval(otpTimer);
                $("#otpTimerWrap").hide();
                $("#resendOtpBtn").show().prop("disabled", false);
                return;
            }
            remaining -= 1;
        }
        tick();
        clearInterval(otpTimer);
        otpTimer = setInterval(tick, 1000);
    }

    function sendOtp() {

        const mobile = $("#PhoneNo").val().trim();
        $("#PhoneNoError").text("");
        $("#OtpError").text("");
        otpVerified = false;
        setNextEnabled(false);

        if (!/^[6-9]\d{9}$/.test(mobile)) {
            $("#PhoneNoError").text("Enter a valid 10-digit mobile number starting with 6-9.");
            return;
        }

        $("#sendOtpBtn").prop("disabled", true);

        const csrf = "{{ csrf_token() }}";

        $.ajax({
            type: "POST",
            url: BASE_URL + "/generate-otp",

            data: { 
                mobile: mobile,
                _token: csrf
            },
            success: function (response) {
                console.log(response);

                if (!response.success) {
                    $("#PhoneNoError").text(response.message || "Unable to send OTP.");
                    return;
                }
                $(".otp-digit").prop("disabled", false).val("");
                $(".otp-digit").first().focus();
                startOtpTimer(30);
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.errors?.mobile?.[0]
                || xhr.responseJSON?.message
                || "Unable to send OTP.";
                 $("#PhoneNoError").text(msg);
            },
            complete: function () {
                $("#sendOtpBtn").prop("disabled", false).html('<i class="fa fa-bullhorn"></i> Send OTP');
            }
        });

        $(".otp-digit").prop("disabled", false).val("");
        $(".otp-digit").first().focus();
        startOtpTimer(30);
    }

    function verifyOtpAndContinue() {
        const otp = getOtpValue();
        $("#OtpError").text("");
        if (otp.length !== 6) {
            $("#OtpError").text("Enter the 6-digit OTP.");
            return;
        }
        otpVerified = true;
        goToStep(2);
    }

    $("#PhoneNo").on("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "").slice(0, 10);
        otpVerified = false;
        setNextEnabled(false);
    });

    $("#sendOtpBtn, #resendOtpBtn").on("click", sendOtp);

    $(".otp-digit").on("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "").slice(0, 1);
        if (this.value && $(this).next(".otp-digit").length) {
            $(this).next(".otp-digit").focus();
        }
        setNextEnabled(getOtpValue().length === 6);
    }).on("keydown", function (e) {
        if (e.key === "Backspace" && !this.value && $(this).prev(".otp-digit").length) {
            $(this).prev(".otp-digit").focus();
        }
    });

    $("#step1Next, #step1NextMobile").on("click", verifyOtpAndContinue);
    $("#step2Back").on("click", function () {
        goToStep(1);
    });

    $("#first_name, #lastname").on("input", function () {
        this.value = this.value.replace(/[^A-Za-z\s]/g, "");
    });
    $("#pincode").on("input", function () {
        this.value = this.value.replace(/[^0-9]/g, "").slice(0, 6);
    });

    $("#state").on("change", function () {
        if ($(this).val() === "Tamil Nadu") {
            $("#districtRow").slideDown(150, fitCard);
        } else {
            $("#districtRow").hide();
            $("#district").val("");
            fitCard();
        }
    });

    function clearErrors() {
        $("[id$='Error']").text("");
    }

    function showErrors(errors) {
        $.each(errors, function (field, messages) {
            switch (field) {
                case "salutation": $("#salutationError").text(messages[0]); break;
                case "first_name": $("#FirstNameError").text(messages[0]); break;
                case "last_name": $("#lastnameError").text(messages[0]); break;
                case "gender": $("#GenderError").text(messages[0]); break;
                case "mobile": $("#PhoneNoError").text(messages[0]); goToStep(1); break;
                case "email":
                case "EmailAddress": $("#EmailError").text(messages[0]); break;
                case "Address":
                case "address": $("#AddressError").text(messages[0]); break;
                case "state": $("#StateError").text(messages[0]); break;
                case "district": $("#DistrictError").text(messages[0]); break;
                case "pincode": $("#PincodeError").text(messages[0]); break;
            }
        });
    }

    $("#registerform").on("submit", function (event) {
        event.preventDefault();
        clearErrors();

        const firstName = $("#first_name").val().trim();
        const lastName = $("#lastname").val().trim();
        const gender = $("input[name='gender']:checked").val();
        const email = $("#EmailAddress").val().trim();
        const address = $("#Address").val().trim();
        const state = $("#state").val();
        const district = $("#district").val();
        const pincode = $("#pincode").val().trim();
        let valid = true;

        if (!firstName) { $("#FirstNameError").text("First name is required."); valid = false; }
        if (!lastName) { $("#lastnameError").text("Last name is required."); valid = false; }
        if (!gender) { $("#GenderError").text("Please select gender."); valid = false; }
        if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { $("#EmailError").text("Enter a valid email address."); valid = false; }
        if (!address) { $("#AddressError").text("Address is required."); valid = false; }
        if (!state) { $("#StateError").text("Please select a state."); valid = false; }
        if (state === "Tamil Nadu" && !district) { $("#DistrictError").text("Please select a district."); valid = false; }
        if (!/^\d{6}$/.test(pincode)) { $("#PincodeError").text("Enter a valid 6-digit pincode."); valid = false; }
        if (!valid) return;

        const formData = {
            _token: csrf,
            salutation: $("#salutation").val().trim(),
            first_name: firstName,
            last_name: lastName,
            gender: gender,
            mobile: $("#PhoneNo").val().trim(),
            email: email,
            Address: address,
            state: state,
            district: district,
            pincode: pincode,
        };

        $("#step2Next").prop("disabled", true).text("Saving...");
        $.ajax({
            type: "POST",
            url: "{{ route('register.store') }}",
            data: formData,
            success: function (response) {
                if (response.success) {
                    $("#successName").text(formData.salutation + " " + firstName + " " + lastName);
                    $("#successMobile").text("+91 " + formData.mobile);
                    $("#successEmail").text(formData.email);
                    goToStep(3);
                }
            },
            error: function (xhr) {
                if (xhr.responseJSON?.errors) {
                    showErrors(xhr.responseJSON.errors);
                }
            },
            complete: function () {
                $("#step2Next").prop("disabled", false).html('Next <i class="fa fa-arrow-right"></i>');
            }
        });
    });

    goToStep(1);
});