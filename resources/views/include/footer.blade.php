
<div id="pdfPopup" class="popup-overlay_pdf">
    <div class="popup_pdf-content">
        <h4>Download Your Application PDF</h4>
        <div id="pdfButtons"></div>
        <button id="closePopup" class="btn btn-danger mt-2">Close</button>
    </div>
</div>


<div class="footer-bottom tnelb-footer">
    <div class="auto-container">

        <div class="wrapper-box">
            <div class="row justify-content-center text-center">
                <div class="col-12 p-0">
                    <nav class="footer-links-inner" aria-label="Footer links">
                        @foreach($footerbottom as $footerbottommenu)
                                 @php
                                    $link = '#';
                                    $target = '';
                                    $label = $footerbottommenu->menu_name_en;

                                    if ($footerbottommenu->page_type === 'Static Page') {
                                    $link = '/tnelb_web' .  $footerbottommenu->menuPage?->page_url ?? '#';
                                    } elseif ($footerbottommenu->page_type === 'url') {
                                    $link = $footerbottommenu->menuPage?->external_url ?? '#';
                                    $target = '_blank';
                                    }
                                    @endphp

                                    @if($footerbottommenu->page_type === 'pdf')
                                    @if($footerbottommenu->menuPage?->pdf_en)
                                    <i class="fa fa-file "></i>
                                    <a href="{{ asset($footerbottommenu->menuPage->pdf_en) }}" target="_blank" title="English PDF">
                                        <i class="fa fa-file-pdf-o text-danger"></i> {{ $label }} (EN)
                                    </a>
                                    @endif
                                    @if($footerbottommenu->menuPage?->pdf_ta)
                                    <i class="fa fa-file "></i>
                                    <a href="{{ asset($footerbottommenu->menuPage->pdf_ta) }}" target="_blank" title="Tamil PDF">
                                        <i class="fa fa-file-pdf-o text-success"></i> {{ $label }} (TA)
                                    </a>
                                    @endif
                                    @elseif($footerbottommenu->page_type === 'submenu')
                                    — {{ $label }}
                                    @else
                                    <i class="fa fa-file "></i>
                                    <a href="{{ $link }}" target="{{ $target }}">{{ $label }}</a>
                                    @endif
                          @endforeach
                        <!-- <i class="fa fa-file "></i> <a rel="noopener" href="websitepolicies.php"> Website Policies
                        </a><span>
                            | </span>

                        <i class="fa fa-question"></i><a rel="noopener" href="#"> Help </a>
                        <span>|</span>

                        <i class="fa fa-comment"></i> <a rel="noopener" href="niot_feedback.php"> Feedback </a>
                        <span>|</span>

                        <i class="fa fa-id-badge"></i> <a rel="noopener" href="#"
                            onclick="set_session_home_menu('','','niot_contactus.php')"> Contact Us</a> -->

                    </nav>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-12 pt-2 px-0">
                    <div class="text-center middleContent text-white"> © Content Owned and Maintained by Tamilnadu
                    Electrical Licensing Board (TNELB), <br> Website Designed and Developed By<a rel="noopener"
                        href="http://www.nic.in/" target="blank" class="external_link pt-2"> National Informatics Centre
                        (NIC) </a>,
                    <a rel="noopener" href="http://meity.gov.in/" target="blank" class="external_link pt-2"> Ministry of
                        Electronics &amp; Information Technology</a>, Government of India
                    </div>
                </div>
            </div>

            <div class="copyright w-100 text-center px-2">
                <div class="text">©
                    <script>
                        document.write(new Date().getFullYear());
                    </script> <a href="#">TNELB</a> - All rights reserved.
                </div>
                <div class="footer-meta-stats" aria-label="Site update and visitor statistics">
                    <span>Last Updated : {{ $siteFooterLastUpdated ?? '—' }}</span>
                    <span>Visitors : {{ isset($siteVisitorCount) ? number_format($siteVisitorCount) : '—' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
</footer>
</div>
</div>
<!--End pagewrapper-->


<!--Scroll to top-->
<div class="scroll-to-top scroll-to-target" data-target="html"><span class="icon-arrow"></span></div>


<script src="{{ url('assets/js/jquery.js') }}"></script>
<script src="{{ url('assets/js/scriptnew.js') }}"></script>
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
<script src="{{ url('assets/admin/src/plugins/src/flatpickr/flatpickr.js') }}"></script>
<script src="{{ url('assets/js/script.js') }}"></script>

<script>
    window.getAjaxErrorMessage = function (xhr, fallback) {
        fallback = fallback || 'Something went wrong. Please try again!';
        if (!xhr) return fallback;

        var data = xhr.responseJSON;
        if (!data && xhr.responseText) {
            try {
                data = JSON.parse(xhr.responseText);
            } catch (e) { /* not JSON */ }
        }

        if (data) {
            if (data.message) return data.message;
            if (data.errors) {
                return Object.values(data.errors).flat().join('\n');
            }
        }

        return fallback;
    };
</script>

<script src="{{ url('assets/js/custom.js') }}?v={{ filemtime(public_path('assets/js/custom.js')) }}"></script>
<script src="{{ url('assets/js/form_p_script.js') }}"></script>
<script src="{{ url('assets/js/forma.js') }}"></script>
<script src="{{ url('assets/js/formsa.js') }}"></script>
<script src="{{ url('assets/js/formsb.js') }}"></script>
<script src="{{ url('assets/js/formb.js') }}"></script>

<script src="{{ asset('assets/admin/src/plugins/src/editors/quill/QuillDeltaToHtmlConverter.bundle.js') }}"></script>
<!-- --------------------------------------------------------------- -->
<script src='https://cdnjs.cloudflare.com/ajax/libs/mixitup/3.2.2/mixitup.min.js'></script>
<!-- fancybox -->
{{-- <script src='https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.1.20/jquery.fancybox.min.js'></script> --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.all.min.js"></script>

<script>
window.COMPETENCY_FORM_CONFIG = {
    formStoreUrl: @json(route('form.store')),
    formUpdateUrlTemplate: @json(route('form.update', ['appl_id' => '__APPL_ID__'])),
    draftRenewalUrlTemplate: @json(route('form.draft_renewal_submit', ['appl_id' => '__APPL_ID__'])),
    formPStoreUrl: @json(route('form_p.store')),
    formPUpdateUrl: @json(route('form_p.update')),
    formPDraftRenewalUrlTemplate: @json(route('form_p.draft_renewal_submit', ['appl_id' => '__APPL_ID__'])),
    getPaymentDetailsUrl: @json(route('licences.getPaymentDetails')),
    getFormInstructionUrl: @json(route('licences.getFormInstruction')),
    updatePaymentUrl: @json(route('payment.updatePayment')),
    verifyLicenseUrl: @json(route('verifylicense')),
    dashboardUrl: @json(route('dashboard')),
    baseUrl: @json(rtrim(UrlHelper::baseFileUrl(), '/')),
    loginId: @json(auth()->check() ? (auth()->user()->login_id ?? '') : ''),
    csrfToken: @json(csrf_token()),
    payuInitiateUrl: @json(route('payu.initiate')),
};
</script>
<script src="{{ url('assets/js/competency_form_ws.js') }}?v={{ filemtime(public_path('assets/js/competency_form_ws.js')) }}"></script>

<script>
$(document).ready(function() {
    $('a[data-toggle="formtab"]').click(function(event) {
        event.preventDefault();
        var targetId = $(this).attr('href');

        $('.tabs-panels').removeClass('active');
        $('a[data-toggle="formtab"]').removeClass('active');

        $(targetId).addClass('active');
        $('a[href="' + targetId + '"]').addClass('active');
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>

<script>
    // JavaScript to ensure footer is at the bottom
    function setFooterPosition() {
        const body = document.body;
        const html = document.documentElement;
        const wrapper = document.querySelector('.page-wrapper');
        const footer = document.querySelector('.footer-bottom');

        // Reset height to auto before recalculating
        wrapper.style.minHeight = '500';

        // Calculate height of visible content
        const contentHeight = Math.max(
            body.scrollHeight, body.offsetHeight,
            html.clientHeight, html.scrollHeight, html.offsetHeight
        );

        // Adjust wrapper height
        if (contentHeight < window.innerHeight) {
            wrapper.style.minHeight = `${window.innerHeight}px`;
        }
    }

    // Run on load and resize
    window.addEventListener('load', setFooterPosition);
    window.addEventListener('resize', setFooterPosition);
</script>

<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        new Cleave('#aadhaar', {
            delimiter: ' ',
            blocks: [4, 4, 4],
            numericOnly: true
        });


        const dobInput = document.getElementById('d_o_b');

        // Only attach flatpickr + DD-MM-YYYY validation on non-native date inputs.
        // On pages using <input type="date"> we rely on the browser picker and page-specific logic.
        if (dobInput && dobInput.type !== 'date') {
            flatpickr(dobInput, {
                dateFormat: "d-m-Y",
                onChange: function (_selectedDates, dateStr) {
                    validateDOB(dateStr || dobInput.value);
                },
                onClose: function (_selectedDates, dateStr) {
                    validateDOB(dateStr || dobInput.value);
                }
            });

            dobInput.addEventListener('keyup', () => validateDOB(dobInput.value));
            dobInput.addEventListener('change', () => validateDOB(dobInput.value));
            dobInput.addEventListener('blur', () => validateDOB(dobInput.value));
        }


        $('#previously_number').on('keyup', function () {
            const value = $(this).val().trim().toUpperCase();
            $(this).val(value);
            const regex = /^(B|H|LB|LWH)\d+$/;

            $('#license_messagdfde').text();

            if (value === '') {
                licenseError.textContent = 'License Number is Required';
                return; // ✅ Stop further checks if empty
            }

            if (!regex.test(value)) {
                licenseError.textContent = 'Invalid License Number';
            } else {
                licenseError.textContent = ''; // ✅ Clear error when valid
            }
        });

        $('#previously_date').on('change', function() {
            const value = $(this).val().trim();

            if (value !== '') {
                $('#dateError').text(''); // ✅ Clear error if not empty
                // You can add other logic here if needed
            }
        });

    });

    function validateDOB(value) {
        const dobInput = document.getElementById('d_o_b');
        const ageInput = document.getElementById('age');
        const errorElement = document.getElementById("dob-error");
        const errorMessage = $('.error-message').first().text('');

        // If the page uses native date input, don't run DD-MM-YYYY validation here.
        if (dobInput && dobInput.type === 'date') {
            return;
        }

        const err = document.querySelector('#d_o_b + .error-message');
        if (err) err.textContent = '';

        errorElement.textContent = "";
        ageInput.value = '';
        errorMessage.textContent = "";


        if (!value || value.trim() === "") {
            errorElement.textContent = "Date of Birth is required.";
            return;
        }

        const match = value.trim().match(/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/);
        if (!match) {
            errorElement.textContent = "Please enter date in DD-MM-YYYY format.";
            return;
        }

        const dd = parseInt(match[1], 10);
        const mm = parseInt(match[2], 10);
        const yyyy = parseInt(match[3], 10);

        const selectedDate = new Date(yyyy, mm - 1, dd);

        if (
            selectedDate.getFullYear() !== yyyy ||
            selectedDate.getMonth() !== (mm - 1) ||
            selectedDate.getDate() !== dd
        ) {
            errorElement.textContent = "Invalid calendar date.";
            return;
        }

        const min = new Date(1970, 0, 1);
        const max = new Date(2010, 11, 31);
        if (selectedDate < min || selectedDate > max) {
            errorElement.textContent = "Minimum age should be 15 and above";
            return;
        }

        const today = new Date();
        let age = today.getFullYear() - yyyy;
        const m = today.getMonth() - (mm - 1);
        if (m < 0 || (m === 0 && today.getDate() < dd)) {
            age--;
        }

        if (age < 0) {
            errorElement.textContent = "Age cannot be negative.";
            return;
        }

        ageInput.value = age;
    }


    document.addEventListener('DOMContentLoaded', function() {
        const addressField = document.getElementById('applicants_address');
        const errorField = document.getElementById('applicants_address_error');

        const regex = /^[A-Za-z0-9\s,.\-#\/]*$/;
        if (addressField) {
            addressField.addEventListener('keyup', function(e) {
                let address = addressField.value;

                // enforce 500 characters max
                if (address.length > 250) {
                    addressField.value = address.substring(0, 250);
                    errorField.textContent = 'Maximum 250 characters reached.';
                    return;
                }


                // clear error if within limit
                errorField.textContent = '';

                if (!regex.test(address)) {
                    errorField.textContent =
                        'Only letters, numbers, spaces, comma, dot, dash, #, / are allowed.';
                }

            });
        }
    });

    $(document).ready(function() {

        let profile = document.querySelector('.profile');
        let menu = document.querySelector('.menu');

        if (profile && menu) {
            profile.onclick = function() {
                menu.classList.toggle('active');
            };
        }

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


        $("#refresh-captcha").click(function(e) {
            e.preventDefault();
            $("#image-captcha").attr("src", "{{ url('captcha/image') }}?" + Math.random());
        });


        // -----------form A --------------code ---------------


        $("#competency_form_a_renewal").on("submit", function(e) {


            e.preventDefault(); // Prevent default form submission


            let isValid = true;

            // Declarations
            let applicantName = $("#applicant_name").val().trim();
            let businessAddress = $("textarea[name='business_address']").val().trim();

            // alert(applicantName);

            if (applicantName === "") {
                $("#applicant_name_error").text("Name is required.");
                isValid = false;
            }

            $("#applicant_name").on("keyup", function() {
                if ($(this).val().trim() !== "") {
                    $("#applicant_name_error").text("");
                }
            });

            if (businessAddress === "") {
                $("#business_address_error").text("Business address is required.");
                isValid = false;
            }

            $("#business_address").on("keyup", function() {
                if ($(this).val().trim() !== "") {
                    $("#business_address_error").text("");
                }
            });

            let authorisedSelected = $('input[name="authorised_name_designation"]:checked').val();
            $("#authorised_name_designation_error").text("");


            $("#authorised_name").next(".error").remove();
            $("#authorised_designation").next(".error").remove();

            if (!authorisedSelected) {
                $("#authorised_name_designation_error").text(
                    "Select Yes or No for authorised signatory.");
                isValid = false;
            } else if (authorisedSelected === "yes") {
                let authName = $("#authorised_name").val().trim();
                let authDesig = $("#authorised_designation").val().trim();

                if (authName === "") {
                    $("#authorised_name").after(
                        '<span class="error text-danger d-block">Authorised Name is required.</span>'
                    );
                    isValid = false;
                }

                if (authDesig === "") {
                    $("#authorised_designation").after(
                        '<span class="error text-danger d-block">Authorised Designation is required.</span>'
                    );
                    isValid = false;
                }
            }


            $('input[name="authorised_name_designation"]').on("change", function() {
                $("#authorised_name_designation_error").text("");
            });

            // Authorised Name & Designation Inputs
            $("#authorised_name, #authorised_designation").on("keyup", function() {
                $(this).next(".error").remove(); // remove dynamically appended span
            });

            // ------------------ 3. Previous Contractor License ------------------
            let previousSelected = $('input[name="previous_contractor_license"]:checked').val();


            if (!previousSelected) {
                $("#previous_contractor_license_error").text(
                    " select Yes or No for previous application.");
                isValid = false;
            } else if (previousSelected === "yes") {
                let prevAppNo = $("#previous_application_number").val().trim();
                $("#previous_application_number").next(".error").remove();

                if (prevAppNo === "") {
                    $("#previous_application_number").after(
                        '<span class="error text-danger d-block">Previous License Number is required.</span>'
                    );
                    isValid = false;
                }
            }
            $('input[name="previous_contractor_license"]').on("change", function() {
                $("#previous_contractor_license_error").text("");
            });
            $("#previous_application_number").on("keyup", function() {
                $(this).next(".error").remove(); // remove dynamically appended span
            });


            // ---------------- 7 Bank------------------------


            let bankAddress = $("textarea[name='bank_address']").val().trim();
            let bankValidity = $("input[name='bank_validity']").val().trim();
            let bankAmount = $("#bank_amount").val().trim();

            if (bankAddress === "") {
                $("#bank_address_error").text("Bank name and address is required.");
                isValid = false;
            }

            if (bankValidity === "") {
                $("#bank_validity_error").text("Validity period is required.");
                isValid = false;
            }

            if (bankAmount === "") {
                $("#bank_amount_error").text("Amount is required.");
                isValid = false;
            }

            // Clear bank_address error on typing
            $("textarea[name='bank_address']").on("keyup", function() {
                if ($(this).val().trim() !== "") {
                    $("#bank_address_error").text("");
                }
            });

            // Clear bank_validity error on typing
            $("input[name='bank_validity']").on("keyup change", function() {
                if ($(this).val().trim() !== "") {
                    $("#bank_validity_error").text("");
                }
            });

            // Clear bank_amount error on typing
            $("#bank_amount").on("keyup change", function() {
                if ($(this).val().trim() !== "") {
                    $("#bank_amount_error").text("");
                }
            });


            // -----------------8-----------------

            let criminalOffence = $('input[name="criminal_offence"]:checked').val();
            if (!criminalOffence) {
                $("#criminal_offence_error").text(" select Yes or No ");
                isValid = false;
            }

            $('input[name="criminal_offence"]').on("change", function() {
                $("#criminal_offence_error").text("");
            });


            // -----------------9-----------------

            let consent_letter_enclose = $('input[name="consent_letter_enclose"]:checked').val();
            if (!consent_letter_enclose) {
                $("#consent_letter_enclose_error").text(" select Yes or No ");
                isValid = false;
            }

            $('input[name="consent_letter_enclose"]').on("change", function() {
                $("#consent_letter_enclose_error").text("");
            });


            // -----------------10-----------------

            let cc_holders_enclosed = $('input[name="cc_holders_enclosed"]:checked').val();
            if (!cc_holders_enclosed) {
                $("#cc_holders_enclosed_error").text(" select Yes or No ");
                isValid = false;
            }

            $('input[name="cc_holders_enclosed"]').on("change", function() {
                $("#cc_holders_enclosed_error").text("");
            });

            // -----------------10 (ii)-----------------

            let purchase_bill_enclose = $('input[name="purchase_bill_enclose"]:checked').val();
            if (!purchase_bill_enclose) {
                $("#purchase_bill_enclose_error").text(" select Yes or No ");
                isValid = false;
            }

            $('input[name="purchase_bill_enclose"]').on("change", function() {
                $("#purchase_bill_enclose_error").text("");
            });

            // -----------------10-----------------

            let test_reports_enclose = $('input[name="test_reports_enclose"]:checked').val();
            if (!test_reports_enclose) {
                $("#test_reports_enclose_error").text(" select Yes or No ");
                isValid = false;
            }

            $('input[name="test_reports_enclose"]').on("change", function() {
                $("#test_reports_enclose_error").text("");
            });


            // -----------------11-----------------

            let specimen_signature_enclose = $('input[name="specimen_signature_enclose"]:checked')
                .val();
            if (!specimen_signature_enclose) {
                $("#specimen_signature_enclose_error").text(" select Yes or No ");
                isValid = false;
            }

            $('input[name="specimen_signature_enclose"]').on("change", function() {
                $("#specimen_signature_enclose_error").text("");
            });


            // -----------------11 (ii)-----------------

            let separate_sheet = $('input[name="separate_sheet"]:checked').val();
            if (!separate_sheet) {
                $("#separate_sheet_error").text(" select Yes or No ");
                isValid = false;
            }

            $('input[name="separate_sheet"]').on("change", function() {
                $("#separate_sheet_error").text("");
            });

            //   ----------------aadhaar change gap-----------------
            // Aadhaar number validation
            const aadhaarnumber = document.getElementById("aadhaar");
            const aadhaarErrormsg = document.getElementById("aadhaar_error");
            const aadhaar = aadhaarnumber.value.replace(/\s+/g, '').trim();
            // let aadhaar = $("#aadhaar").val().trim();
            const aadhaarRegex = /^[2-9]{1}[0-9]{11}$/;
            if (aadhaar === "") {
                aadhaarErrormsg.textContent = "Aadhaar number is required.";
                if (!firstErrorField) firstErrorField = aadhaar;
                isValid = false;
            } else if (!aadhaarRegex.test(aadhaar)) {
                aadhaarErrormsg.textContent =
                    "Please enter a valid 12-digit Aadhaar number (should not start with 0 or 1).";
                if (!firstErrorField) firstErrorField = aadhaar;
                isValid = false;
            } else {
                aadhaarErrormsg.textContent = "";
            }


            // Clear errors on file change
            $("#aadhaar_doc").on("change", function() {
                $('#aadhaar_doc_error').text("");
            });

            // PAN document removed

            $("#gst_doc").on("change", function() {
                $('#gst_doc_error').text("");
            });

            // -------------------end doc--------------------


            // PAN details removed
            const gst_number = $("#gst_number").val().trim().toUpperCase();

            if (gst_number === "") {
                $("#gst_number_error").text("GST Number is required.");
                isValid = false;
            } else if (!/^[A-Z0-9]{15}$/.test(gst_number)) {
                $("#gst_number_error").text("Enter 15-character alphanumeric GST Number.");
                isValid = false;
            } else {
                $("#gst_number_error").text("");
            }

            $("#gst_number").on("keyup", function() {
                const value = $(this).val().toUpperCase();
                $(this).val(value); // Force uppercase

                if (/^[A-Z0-9]{15}$/.test(value)) {
                    $("#gst_number_error").text("");
                } else {
                    $("#gst_number_error").text("Enter 15-character alphanumeric GST Number.");
                }
            });


            // -------------------- 1. Proprietor Validation --------------------
            let proprietorValid = true;

            $(".border.box-shadow-blue, .proprietor-block").each(function() {
                const name = $(this).find('input[name="proprietor_name[]"]');
                const address = $(this).find('textarea[name="proprietor_address[]"]');
                const age = $(this).find('input[name="age[]"]');
                const qualification = $(this).find('input[name="qualification[]"]');
                const fatherName = $(this).find('input[name="fathers_name[]"]');

                if (name.val().trim() === "") {
                    name.siblings('.error').text("Name is required.");
                    proprietorValid = false;
                }

                if (address.val().trim() === "") {
                    address.siblings('.error').text("Address is required.");
                    proprietorValid = false;
                }

                if (age.val().trim() === "") {
                    age.siblings('.error').text("Age is required.");
                    proprietorValid = false;
                }

                if (qualification.val().trim() === "") {
                    qualification.siblings('.error').text("Qualification is required.");
                    proprietorValid = false;
                }

                if (fatherName.val().trim() === "") {
                    fatherName.siblings('.error').text("Father/Husband's Name is required.");
                    proprietorValid = false;
                }

                // Optional fields (only if 'Yes' is selected)   
                const index = $(this).index();
                const competencyYes = $(this).find(
                        'input[name="competency_certificate_holding[${index}]"]:checked')
                    .val() === "yes";
                if (competencyYes) {
                    const certNo = $(this).find(
                        'input[name="competency_certificate_number[]"]');
                    const certValid = $(this).find(
                        'input[name="competency_certificate_validity[]"]');
                    if (certNo.val().trim() === "") {
                        certNo.after(
                            '<span class="error text-danger">Certificate Number is required.</span>'
                        );
                        proprietorValid = false;
                    }
                    if (certValid.val().trim() === "") {
                        certValid.after(
                            '<span class="error text-danger">Certificate Validity is required.</span>'
                        );
                        proprietorValid = false;
                    }
                }

                const employedYes = $(this).find(
                    'input[name="presently_employed[${index}]"]:checked').val() === "yes";
                if (employedYes) {
                    const employerName = $(this).find(
                        'input[name="presently_employed_name[]"]');
                    const employerAddress = $(this).find(
                        'textarea[name="presently_employed_address[]"]');
                    if (employerName.val().trim() === "") {
                        employerName.after(
                            '<span class="error text-danger">Employer name is required.</span>'
                        );
                        proprietorValid = false;
                    }
                    if (employerAddress.val().trim() === "") {
                        employerAddress.after(
                            '<span class="error text-danger">Employer address is required.</span>'
                        );
                        proprietorValid = false;
                    }
                }

                const experienceYes = $(this).find(
                    'input[name="previous_experience[${index}]"]:checked').val() === "yes";
                if (experienceYes) {
                    const expName = $(this).find('input[name="previous_experience_name[]"]');
                    const expAddress = $(this).find(
                        'textarea[name="previous_experience_address[]"]');
                    const expLicense = $(this).find(
                        'input[name="previous_experience_lnumber[]"]');
                    if (expName.val().trim() === "") {
                        expName.after(
                            '<span class="error text-danger">Contractor Name is required.</span>'
                        );
                        proprietorValid = false;
                    }
                    if (expAddress.val().trim() === "") {
                        expAddress.after(
                            '<span class="error text-danger">Contractor Address is required.</span>'
                        );
                        proprietorValid = false;
                    }
                    if (expLicense.val().trim() === "") {
                        expLicense.after(
                            '<span class="error text-danger">License Number is required.</span>'
                        );
                        proprietorValid = false;
                    }
                }
            });


            let staffValid = true;
            let staffCount = 0;

            // Clear all previous error messages once before loop
            $(".staff-fieldsrenew .error").text("");

            $(".staff-fieldsrenew").each(function() {
                const name = $(this).find('input[name="staff_name[]"]');
                const qual = $(this).find('select[name="staff_qualification[]"]');
                const ccNum = $(this).find('input[name="cc_number[]"]');
                const ccValid = $(this).find('input[name="cc_validity[]"]');
                const category = $(this).find('select[name="staff_category[]"]');

                const nameVal = name.val().trim();
                const ccNumVal = ccNum.val().trim();
                const ccValidVal = ccValid.val().trim();
                const qualVal = qual.val();
                const categoryVal = category.val();

                // Live error clearing
                name.on("keyup", function() {
                    if ($(this).val().trim() !== "") {
                        name.siblings(".error").text("");
                    }
                });

                qual.on("change", function() {
                    if ($(this).val() !== "") {
                        qual.siblings(".error").text("");
                    }
                });

                ccNum.on("keyup", function() {
                    if ($(this).val().trim() !== "") {
                        ccNum.siblings(".error").text("");
                    }
                });

                ccValid.on("change keyup", function() {
                    if ($(this).val().trim() !== "") {
                        ccValid.siblings(".error").text("");
                    }
                });

                category.on("change", function() {
                    if ($(this).val() !== "") {
                        category.siblings(".error").text("");
                    }
                });

                // Validation
                if (nameVal === "") {
                    name.siblings(".error").text("Name is required.");
                    staffValid = false;
                }

                if (!qualVal) {
                    qual.siblings(".error").text("Qualification is required.");
                    staffValid = false;
                }

                if (ccNumVal === "") {
                    ccNum.siblings(".error").text("Certificate Number is required.");
                    staffValid = false;
                }

                if (ccValidVal === "") {
                    ccValid.siblings(".error").text("Certificate Validity is required.");
                    staffValid = false;
                }

                if (!categoryVal) {
                    category.siblings(".error").text("Category is required.");
                    staffValid = false;
                }

                // Count only if all fields filled
                if (nameVal && qualVal && ccNumVal && ccValidVal && categoryVal) {
                    staffCount++;
                }
            });

            // Declaration Checkboxes
            const declaration1Checked = $("#declarationCheckbox").is(":checked");
            const declaration2Checked = $("#declarationCheckbox1").is(":checked");

            if (!declaration1Checked) {
                $("#declaration3_error").text("⚠ Please check this declaration before proceeding.");
                isValid = false;
            }

            if (!declaration2Checked) {
                $("#declaration4_error").text("⚠ Please check this declaration before proceeding.");
                isValid = false;
            }

            // Clear errors on change
            $("#declarationCheckbox").on("change", function() {
                if ($(this).is(":checked")) {
                    $("#declaration3_error").text("");
                }
            });

            $("#declarationCheckbox1").on("change", function() {
                if ($(this).is(":checked")) {
                    $("#declaration4_error").text("");
                }
            });

            console.log({
                applicantName,
                businessAddress,
                aadhaar,
                gst_number
            });
            console.group("🔍 Form Validation Summary");

            console.log("➡ Applicant Name:", applicantName);
            console.log("➡ Business Address:", businessAddress);
            console.log("➡ Aadhaar:", aadhaar);
            console.log("➡ GST:", gst_number);
            console.log("➡ Authorised Signatory:", authorisedSelected);
            if (authorisedSelected === "yes") {
                console.log("    ↪ Authorised Name:", $("#authorised_name").val().trim());
                console.log("    ↪ Authorised Designation:", $("#authorised_designation").val().trim());
            }
            console.log("➡ Previous License:", previousSelected);
            if (previousSelected === "yes") {
                console.log("    ↪ Previous App Number:", $("#previous_application_number").val()
                    .trim());
            }

            // Prepare formData
            const formData = new FormData($('#competency_form_a_renewal')[0]);
            const applicationId = $('#application_id').val();
            const status = $('select[name="status"]').val();
            const actionType = status == "0" ? "draft" : "submit";
            // let actionType = "submit";

            formData.append("form_action", actionType);

            if (isValid) {
                if (actionType === "draft") {
                    submitFormAFinalrenew(formData, actionType, applicationId);
                } else {
                    showDeclarationPopupformArenew(formData, applicationId);
                }
            }
        });


        function showDeclarationPopupformArenew(formData, applicationId) {
            Swal.fire({
                title: '<h5 class="mb-3" style="color: white; background-color: #0d6efd; padding: 10px 20px; text-align:left;">📋 Instructions & Declaration For Renewal</h5>',
                html: `
            <div style="text-align: left; max-height: 500px; overflow-y: auto; padding: 0 10px; font-size: 14px; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                <ol class="instruct" style="margin-left: 20px; padding-left: 10px;">
                    <li>
                      <span style="color: #0d6efd; font-weight: 600;">Fees:</span> 
                      <ol type="i" style="margin-left: 20px; margin-top: 5px;">
                        <li>Fees Issue for <strong> Electrical Contractor/s Licence-Grade "EA"</strong> from <span style="color:#198754;">01.01.2024</span> onwards is <span style="color:#dc3545; font-weight:600;">Rs. 6,000/-</span>.</li>
                        <li>The fee must be paid by <strong>Demand Draft</strong> from any <span style="color:#0d6efd;">Scheduled Bank</span> or <span style="color:#0d6efd;">Co-operative Bank</span>, in favour of <strong>Secretary, Electrical Licensing Board, Chennai – 600 032</strong>, payable at Chennai. <em style="color:#6c757d;">Other methods of payment will not be accepted.</em></li>
                      </ol>
                    </li>
                    <li>
                      The <span style="color:#0d6efd; font-weight:600;">applicant’s signature</span> and <span style="color:#0d6efd; font-weight:600;">photo</span> affixed in the application must be attested by a <span style="color:#198754;">Gazetted Officer</span>.
                    </li>
                    <li>
                      <u><strong>With Experience:</strong></u>
                      <ul style="margin-left: 20px; list-style-type: disc;">
                        <li>Two years experience in erection or operation and maintenance in High Voltage installation.</li>
                        <li><strong>OR</strong></li>
                        <li>The applicant should hold a <strong>Electrical Contractor/s Licence-Grade "EA"</strong> from the Department of Technical Education, Chennai.</li>
                      </ul>
                    </li>
                    <li>The applicant should possess a <span style="color:#0d6efd; font-weight:600;">Diploma</span> or <span style="color:#0d6efd; font-weight:600;">Degree</span> in Electrical Engineering or an <span style="color:#0d6efd; font-weight:600;">A.M.I.E.</span> Certificate (Part A & B).</li>
                    <li><span style="color:#0d6efd; font-weight:600;">Photographs:</span> Three passport-size photographs (6cm x 4cm), taken within the last three months, must be provided.</li>
                    <li><span style="color:#0d6efd; font-weight:600;">Signature:</span> Applicant’s signature in triplicate on a separate sheet of paper must be provided.</li>
                    <li><span style="color:#0d6efd; font-weight:600;">Proof of Age:</span> Original and photocopy of age proof document must be submitted.</li>
                    <li><span style="color:#0d6efd; font-weight:600;">Application Form:</span> All columns must be filled clearly in words and figures. No column should be left blank.</li>
                    <li>Application should be in the <strong>prescribed form only</strong>.</li>
                </ol>

                <div class="form-check mt-4">
                    <input type="checkbox" class="form-check-input" id="declaration-agree-renew">
                    <label for="declaration-agree-renew" class="form-check-label" style="font-weight: 600;">
                        I have read and agree to the above instructions.
                    </label>
                    <div class="text-danger mt-2 d-none" id="declaration-error-renew">You must agree to proceed.</div>
                </div>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: "Proceed",
                cancelButtonText: "Cancel",
                width: '80%',
                customClass: {
                    popup: 'swal-xl',
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false,
                didOpen: () => {
                    const confirmBtn = Swal.getConfirmButton();
                    confirmBtn.disabled = true;
                    const checkbox = Swal.getHtmlContainer().querySelector(
                        "#declaration-agree-renew");
                    checkbox?.addEventListener("change", () => {
                        confirmBtn.disabled = !checkbox.checked;
                    });
                },
                preConfirm: () => {
                    const isChecked = Swal.getHtmlContainer().querySelector(
                        "#declaration-agree-renew")?.checked;
                    if (!isChecked) {
                        Swal.getHtmlContainer().querySelector("#declaration-error-renew").classList
                            .remove("d-none");
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitFormAFinalrenew(formData, "submit", applicationId);
                }
            });
        }


        function submitFormAFinalrenew(formData, actionType, applicationId) {

            // alert(applicationId);
            let url = applicationId ?
                "{{ route('forma.update', ['appl_id' => '_APPL_ID']) }}".replace('_APPL_ID', applicationId) :
                "{{ route('forma.store') }}";


            if (applicationId) {
                formData.append('_method', 'PUT');
            }

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                beforeSend: function() {
                    $(".save-draft, .submit-payment").prop("disabled", true);
                },
                success: function(response) {
                    const applicationId = response
                        .application_id; // ✅ Get application_id returned from backend
                    const transactionId = response.transaction_id || 'TXN' + Math.floor(Math
                        .random() * 900000 + 100000);
                    const transactionDate = new Date().toLocaleDateString('en-GB');
                    const applicantName = $("#applicant_name").val() || "Applicant";
                    const amount = $("#amount").val() || "6000";

                    if (actionType === "draft") {
                        Swal.fire("Saved!", "Draft saved successfully!", "success").then(() => {
                            window.location.href = BASE_URL + "/dashboard";
                        });
                    } else {
                        // ✅ Pass returned application_id to the next popup
                        showPaymentInitiationPopupformArenew(applicationId, transactionId,
                            transactionDate, applicantName, amount);
                    }

                    $(".save-draft, .submit-payment").prop("disabled", false);
                },
                error: function(xhr) {
                    $(".save-draft, .submit-payment").prop("disabled", false);
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, message) {
                            $('#${field}_error').text(message);
                        });
                    } else {
                        Swal.fire("Error!", "Something went wrong. Try again.", "error");
                    }
                }
            });
        }


        function showPaymentInitiationPopupformArenew(application_id, transactionId, transactionDate,
            applicantName, amount) {
            Swal.fire({
                title: "<span style='color:#0d6efd;'>Initiate Payment</span>",
                html: `<div class="text-start" style="font-size: 14px; padding: 10px 0;">
            <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <th style="text-align: left; padding: 6px 10px; width: 50%; color: #555;">Application ID</th>
                        <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${application_id}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left; padding: 6px 10px; color: #555;">Transaction ID</th>
                        <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${transactionId}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left; padding: 6px 10px; color: #555;">Date</th>
                        <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${transactionDate}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left; padding: 6px 10px; color: #555;">Applicant Name</th>
                        <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${applicantName}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left; padding: 10px; color: #333;">Amount</th>
                        <td style="text-align: right; padding: 10px; font-weight: bold; color: #0d6efd;">Rs. ${amount} /-</td>
                    </tr>
                </tbody>
            </table>
        </div>`,
                icon: "info",
                showCancelButton: true,
                confirmButtonText: 'Pay Now',
                cancelButtonText: 'Cancel',
                width: '40%',
                showCloseButton: true,
                customClass: {
                    popup: 'swal2-popup-sm'
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    // Simulate delay and then show payment success popup
                    setTimeout(() => {
                        showPaymentSuccessPopupformArenew(application_id, transactionId,
                            transactionDate, applicantName, amount);
                    }, 1000);
                }
            });
        }


        function showPaymentSuccessPopupformArenew(application_id, transactionId, transactionDate,
            applicantName, amount) {

            let loginId = application_id;
            Swal.fire({
                icon: 'success',
                title: 'Payment Successful',
                html: `
                
           <div style="font-size: 14px; text-align: left; width: 100%; max-width: 100%;">
        <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
            <div style="
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 4px 12px;
            font-size: 14px;
            max-width: 400px;
            border-right:2px solid #0d6efd;
            padding: 0px 25px;
            ">
            <div style="font-weight: bold;">Application ID:</div>
            <div style="word-break: break-word;">${application_id}</div>

            <div style="font-weight: bold;">Transaction ID:</div>
            <div style="word-break: break-word;">${transactionId}</div>

            <div style="font-weight: bold;">Transaction Date:</div>
            <div>${transactionDate}</div>

            <div style="font-weight: bold;">Applicant Name:</div>
            <div>${applicantName}</div>

            <div style="font-weight: bold;">Amount Paid:</div>
            <div>${amount}</div>
        </div>
            <div style="min-width: 220px;">
            <p><strong>Download Your Payment Receipt:</strong></p>
            <button class="btn btn-info btn-sm mb-2" onclick="paymentreceiptrenew('${application_id}')">
                <i class="fa fa-file-pdf-o text-danger"></i> 
                <i class="fa fa-download text-danger"></i>
                Download Receipt
            </button>
            <p class="mt-3"><strong>Download Your Application PDF:</strong></p>
            <button class="btn btn-primary btn-sm me-1" onclick="downloadPDFformArenew('${loginId}')">English PDF</button>
            
            </div>
        </div>
        </div>
        `,
                confirmButtonText: 'OK',
                width: '40%',
                customClass: {
                    popup: 'swal2-popup-sm'
                }
            }).then(() => {
                window.location.href = BASE_URL + "/dashboard"; // Redirect or reset form as needed
            });
        }

     
        // ----------------------renew end-----------------------

       // ----------------------Form A dependencies-----------------------

       // contractor licence age calculation---------------------
    $(document).on("change", ".dob", function() {

        let dobVal = $(this).val();
        if (!dobVal) return;

        let dob = new Date(dobVal);
        let today = new Date();

        let age = today.getFullYear() - dob.getFullYear();
        let m = today.getMonth() - dob.getMonth();

        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
            age--;
        }

        // ✅ Target the nearest row instead of global section
        let row = $(this).closest(".row");

        if (age < 25) {
            row.find(".age_error").text("Minimum age is 25");
            row.find(".age").val('');
            return;
        }

        row.find(".age_error").text('');
        row.find(".age").val(age);
    });

    // ---CL forms qual-----------
    $(document).on("change", ".qualification", function () {

    let $section = $(this).closest(".border");

    let qualification = $(this).val();

    let $wrapper = $section.find(".qualTextWrapper");
    let $input   = $wrapper.find("input[name='qual_text[]']");

    if (qualification && qualification !== '8TH PASS') {

        if (!$wrapper.is(":visible")) {
            $wrapper.stop(true, true).slideDown(250);
            setTimeout(() => $input.focus(), 260);
        }

    } else {

        if ($wrapper.is(":visible")) {
            $wrapper.stop(true, true).slideUp(200, function () {
                $input.val("");
                $wrapper.find(".qual_text_error").text("");
            });
        }

    }
    });


    const IS_DRAFT = {{isset($application) ? 'true' : 'false'}};
    const SAVED_OWNERSHIP = "{{ $application->application_ownershiptype ?? '' }}";
    $(document).ready(function() {

    // 🔹 Draft load behavior
    if (IS_DRAFT) {
        $("#partnershipdeed, #directormom").hide();

        if (SAVED_OWNERSHIP === 'pt') {
            $("#partnershipdeed").show();
        } else if (SAVED_OWNERSHIP === 'pvt' || SAVED_OWNERSHIP === 'public' || SAVED_OWNERSHIP === 'ltd') {
            $("#directormom").show();
        }
    }
    });
/* 🔁 Ownership change */
$(document).on("change", "#ownership_type_select", function () {

    let type = $(this).val();

    // Clear files + errors
    $("input[type='file']").val("");
    $(".ownershipdoc_upload_error").text("");

    // Hide all sections first
    $("#partnershipdeed, #directormom, #proprietor-sectionfresh, #directorfill-section, #partnersfill-section")
        .hide();

    // Reset readonly + values
    $("input[name='proprietor_name[]']")
        .val("")
        .prop("readonly", false);

    // ================= PARTNERSHIP =================
    if (type === 'pt') {

        $("#partnershipdeed").slideDown();
        $("#partnersfill-section").slideDown();

        let rowCount = $("#partnersfill-section table tbody tr").length;

        @if(Auth::check())
        if (rowCount === 0) {
            $("#partnersfill-section")
                .find("input[name='proprietor_name[]']")
                .val("{{ Auth::user()->first_name.' '.Auth::user()->last_name }}")
                .prop("readonly", true);
        }
        @endif
    }

    // ================= COMPANY TYPES =================
    else if (
        type === 'pvt' ||
        type === 'public' ||
        type === 'ltd'
    ) {

        $("#directormom").slideDown();
        $("#directorfill-section").slideDown();

        let rowCount = $("#director-section table tbody tr").length;

        @if(Auth::check())
        if (rowCount === 0) {
            $("#directorfill-section")
                .find("input[name='proprietor_name[]']")
                .val("{{ Auth::user()->first_name.' '.Auth::user()->last_name }}")
                .prop("readonly", true);
        }
        @endif
    }

    // ================= PROPRIETOR =================
    else if (type === 'pr') {

        $("#proprietor-sectionfresh").slideDown();

        @if(Auth::check())
        $("#proprietor-sectionfresh")
            .find("input[name='proprietor_name[]']")
            .val("{{ Auth::user()->first_name.' '.Auth::user()->last_name }}")
            .prop("readonly", true);
        @endif
    }

});

    
    // ------------------------------------------------------------forma 8_11 Attachments open----------------------
    $(document).on("change", "input[name='criminal_offence']", function () {

    if ($(this).val() === "yes") {

        $(".criminaloffence_file").stop(true, true).slideDown(300);

    } else {

        $(".criminaloffence_file").stop(true, true).slideUp(300, function () {
            $("#criminal_offence_doc").val("");
            $("#criminal_offence_doc_error").text("");
        });

    }

    });

    $(document).on("change", "input[name='consent_letter_enclose']", function () {

    if ($(this).val() === "yes") {

        $(".consent_letter_enclosefile").stop(true, true).slideDown(300);

    } else {

        $(".consent_letter_enclosefile").stop(true, true).slideUp(300, function () {
            $("#cc_holders_enclosed_doc").val("");
            $("#cc_holders_enclosed_doc_error").text("");
        });

    }

    });

    $(document).on("change", "input[name='cc_holders_enclosed']", function () {

    if ($(this).val() === "yes") {

        $(".cc_holders_enclosedfile").stop(true, true).slideDown(300);

    } else {

        $(".cc_holders_enclosedfile").stop(true, true).slideUp(300, function () {
            $("#cc_holders_enclosed_doc").val("");
            $("#cc_holders_enclosed_doc_error").text("");
        });

    }

    });


    $(document).on("change", "input[name='specimen_signature_enclose']", function () {

    if ($(this).val() === "yes") {

        $(".specimen_signature_enclosefile").stop(true, true).slideDown(300);

    } else {

        $(".specimen_signature_enclosefile").stop(true, true).slideUp(300, function () {
            $("#specimen_signature").val("");
            $("#specimen_signature_error").text("");
        });

    }

    });


    $(document).on("change", "input[name='separate_sheet']", function () {

    if ($(this).val() === "yes") {

        $(".separate_sheetfile").stop(true, true).slideDown(300);

    } else {

        $(".separate_sheetfile").stop(true, true).slideUp(300, function () {
            $("#separate_sheet_doc").val("");
            $("#specimen_signature_error").text("");
        });

    }

    });


    // -------------------------------------
    document.addEventListener("DOMContentLoaded", function() {

    const hiddenBtn = document.getElementById('hiddenBtn');
    const chooseBtn = document.getElementById('chooseBtn');

    chooseBtn.addEventListener('click', () => hiddenBtn.click());

    hiddenBtn.addEventListener('change', function() {
        chooseBtn.innerText = this.files.length > 0 ?
            this.files[0].name :
            'Choose';
    });

    });


        function showDeclarationPopupformA(formData) {
            Swal.fire({
                title: '<h5 class="mb-3" style="color: white; background-color: #0d6efd; padding: 10px 20px; text-align:left;">📋 Instructions & Declaration</h5>',
                html: `
            <div style="text-align: left; max-height: 500px; overflow-y: auto; padding: 0 10px; font-size: 14px; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
                <ol class="instruct" style="margin-left: 20px; padding-left: 10px;">
                    <li>
                      <span style="color: #0d6efd; font-weight: 600;">Fees:</span> 
                      <ol type="i" style="margin-left: 20px; margin-top: 5px;">
                        <li>Fees Issue for <strong> Electrical Contractor/s Licence-Grade "EA"</strong> from <span style="color:#198754;">01.01.2024</span> onwards is <span style="color:#dc3545; font-weight:600;">Rs. 12,000/-</span>.</li>
                        <li>The fee must be paid by <strong>Demand Draft</strong> from any <span style="color:#0d6efd;">Scheduled Bank</span> or <span style="color:#0d6efd;">Co-operative Bank</span>, in favour of <strong>Secretary, Electrical Licensing Board, Chennai – 600 032</strong>, payable at Chennai. <em style="color:#6c757d;">Other methods of payment will not be accepted.</em></li>
                      </ol>
                    </li>
                    <li>
                      The <span style="color:#0d6efd; font-weight:600;">applicant’s signature</span> and <span style="color:#0d6efd; font-weight:600;">photo</span> affixed in the application must be attested by a <span style="color:#198754;">Gazetted Officer</span>.
                    </li>
                    <li>
                      <u><strong>With Experience:</strong></u>
                      <ul style="margin-left: 20px; list-style-type: disc;">
                        <li>Two years experience in erection or operation and maintenance in High Voltage installation.</li>
                        <li><strong>OR</strong></li>
                        <li>The applicant should hold a <strong>Electrical Contractor/s Licence-Grade "EA"</strong> from the Department of Technical Education, Chennai.</li>
                      </ul>
                    </li>
                    <li>The applicant should possess a <span style="color:#0d6efd; font-weight:600;">Diploma</span> or <span style="color:#0d6efd; font-weight:600;">Degree</span> in Electrical Engineering or an <span style="color:#0d6efd; font-weight:600;">A.M.I.E.</span> Certificate (Part A & B).</li>
                    <li><span style="color:#0d6efd; font-weight:600;">Photographs:</span> Three passport-size photographs (6cm x 4cm), taken within the last three months, must be provided.</li>
                    <li><span style="color:#0d6efd; font-weight:600;">Signature:</span> Applicant’s signature in triplicate on a separate sheet of paper must be provided.</li>
                    <li><span style="color:#0d6efd; font-weight:600;">Proof of Age:</span> Original and photocopy of age proof document must be submitted.</li>
                    <li><span style="color:#0d6efd; font-weight:600;">Application Form:</span> All columns must be filled clearly in words and figures. No column should be left blank.</li>
                    <li>Application should be in the <strong>prescribed form only</strong>.</li>
                </ol>

                <div class="form-check mt-4">
                    <input type="checkbox" class="form-check-input" id="declaration-agree-renew">
                    <label for="declaration-agree-renew" class="form-check-label" style="font-weight: 600;">
                        I have read and agree to the above instructions.
                    </label>
                    <div class="text-danger mt-2 d-none" id="declaration-error-renew">You must agree to proceed.</div>
                </div>
            </div>
        `,
                showCancelButton: true,
                confirmButtonText: "Proceed",
                cancelButtonText: "Cancel",
                width: '80%',
                customClass: {
                    popup: 'swal-xl',
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false,
                didOpen: () => {
                    const confirmBtn = Swal.getConfirmButton();
                    confirmBtn.disabled = true;
                    const checkbox = Swal.getHtmlContainer().querySelector(
                        "#declaration-agree-renew");

                    if (checkbox) {
                        checkbox.addEventListener("change", () => {
                            confirmBtn.disabled = !checkbox.checked;
                        });
                    }
                },
                preConfirm: () => {
                    const isChecked = Swal.getHtmlContainer().querySelector(
                        "#declaration-agree-renew")?.checked;
                    if (!isChecked) {
                        Swal.getHtmlContainer().querySelector("#declaration-error-renew").classList
                            .remove("d-none");
                        return false;
                    }
                    return true;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    submitFormAFinal(formData, "submit");
                }
            });
        }


        function submitFormAFinal(formData, actionType) {
            $.ajax({
                url: "{{ route('forma.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
                beforeSend: function() {
                    $(".save-draft, .submit-payment").prop("disabled", true);
                },
                success: function(response) {
                    const loginId = response.login_id;
                    const transactionId = response.transaction_id || 'TXN123456';
                    const transactionDate = new Date().toLocaleDateString('en-GB');
                    const applicantName = $("#applicant_name").val() || "Applicant";
                    const amount = $("#amount").val() || "30000";

                    if (actionType === "draft") {
                        Swal.fire("Saved!", "Draft saved successfully!", "success").then(() => {
                            window.location.href = BASE_URL + "/dashboard";
                        });
                    } else {
                        showPaymentInitiationPopupformA(loginId, transactionId, transactionDate,
                            applicantName, amount);
                    }

                    $(".save-draft, .submit-payment").prop("disabled", false);
                },
                error: function(xhr) {
                    $(".save-draft, .submit-payment").prop("disabled", false);
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, message) {
                            // $(#${field}_error).text(message);
                            $(`#${field}_error`).text(message);
                        });
                    } else {
                        Swal.fire("Error!", "Something went wrong. Try again.", "error");
                    }
                }
            });
        }


        function showPaymentInitiationPopupformA(application_id, transactionId, transactionDate, applicantName,
            amount) {
            Swal.fire({
                title: "<span style='color:#0d6efd;'>Initiate Payment</span>",
                html: `<div class="text-start" style="font-size: 14px; padding: 10px 0;">
            <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                <tbody>
                    <tr>
                        <th style="text-align: left; padding: 6px 10px; width: 50%; color: #555;">Application ID</th>
                        <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${application_id}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left; padding: 6px 10px; color: #555;">Transaction ID</th>
                        <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${transactionId}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left; padding: 6px 10px; color: #555;">Date</th>
                        <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${transactionDate}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left; padding: 6px 10px; color: #555;">Applicant Name</th>
                        <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${applicantName}</td>
                    </tr>
                    <tr>
                        <th style="text-align: left; padding: 10px; color: #333;">Amount</th>
                        <td style="text-align: right; padding: 10px; font-weight: bold; color: #0d6efd;">Rs. ${amount} /-</td>
                    </tr>
                </tbody>
            </table>
        </div>`,
                icon: "info",
                showCancelButton: true,
                confirmButtonText: 'Pay Now',
                cancelButtonText: 'Cancel',
                showCloseButton: true,
                customClass: {
                    popup: 'swal2-popup-sm'
                },
            }).then((result) => {
                if (result.isConfirmed) {
                    // Simulate delay and then show payment success popup
                    setTimeout(() => {
                        showPaymentSuccessPopupformA(application_id, transactionId,
                            transactionDate, applicantName, amount);
                    }, 1000);
                }
            });
        }


        function showPaymentSuccessPopupformA(application_id, transactionId, transactionDate, applicantName,
            amount) {

            let loginId = application_id;
            Swal.fire({
                icon: 'success',
                title: 'Payment Successful',
                html: `
                
            <div style="font-size: 14px; text-align: left; width: 100%; max-width: 100%;">
            <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: center;">
            <div style="
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 4px 12px;
            font-size: 14px;
            max-width: 400px;
            border-right:2px solid #0d6efd;
            padding: 0px 25px;
            ">
            <div style="font-weight: bold;">Application ID:</div>
            <div style="word-break: break-word;">${application_id}</div>

            <div style="font-weight: bold;">Transaction ID:</div>
            <div style="word-break: break-word;">${transactionId}</div>

            <div style="font-weight: bold;">Transaction Date:</div>
            <div>${transactionDate}</div>

            <div style="font-weight: bold;">Applicant Name:</div>
            <div>${applicantName}</div>

            <div style="font-weight: bold;">Amount Paid:</div>
            <div>${amount}</div>
            </div>
            <div style="min-width: 220px;">
            <p><strong>Download Your Payment Receipt:</strong></p>
            <button class="btn btn-info btn-sm mb-2" onclick="paymentreceipt('${application_id}')">
                <i class="fa fa-file-pdf-o text-danger"></i> 
                <i class="fa fa-download text-danger"></i>
                Download Receipt
            </button>
            <p class="mt-3"><strong>Download Your Application PDF:</strong></p>
            <button class="btn btn-primary btn-sm me-1" onclick="downloadPDFformA('${loginId}')">English PDF</button>

            </div>
            </div>
            </div>
            `,
                confirmButtonText: 'OK',
                customClass: {
                    popup: 'swal2-popup-sm'
                }
            }).then(() => {
                window.location.href = BASE_URL + "/dashboard"; // Redirect or reset form as needed
            });
        }


        $("#closePopup").on("click", function() {
            $("#pdfPopup").fadeOut(function() {
                window.location.href = BASE_URL + "/dashboard";
            });
        });

        // function downloadPDF(language) {
        //     let url = (language === 'tamil') ? `${BASE_URL}/generateTamilPDF/${loginId}` : `${BASE_URL}/generate-pdf/${loginId}`;
        //     window.open(url, '_blank');
        // }
  
        document.addEventListener("DOMContentLoaded", function() {
            let applicantNameInput = document.getElementById("Applicant_Name");

            applicantNameInput.addEventListener("focus", function() {
                this.addEventListener("input", function() {
                    this.value = this.value.replace(/[^A-Za-z\s]/g,
                        ''); // Allow only letters and spaces
                });
            });


            let applicantFatherNameInput = document.getElementById("Fathers_Name");

            applicantFatherNameInput.addEventListener("focus", function() {
                this.addEventListener("input", function() {
                    this.value = this.value.replace(/[^A-Za-z\s]/g,
                        ''); // Allow only letters and spaces
                });
            });

            $('#previously_date').datepicker({
                dateFormat: 'dd-mm-yy',
                maxDate: -1 // yesterday
            });

            let checkbox = document.getElementById("previous_exp");
            let detailsDiv = document.getElementById("previously_details");
            let licenseInput = document.getElementById("previously_number");
            let dateInput = document.getElementById("previously_date");
            let licenseError = document.getElementById("licenseError");
            let dateError = document.getElementById("dateError");


            checkbox.addEventListener("change", function() {
                if (this.checked) {
                    detailsDiv.style.display = "flex"; // Show details section
                    licenseInput.setAttribute("required", "required");
                    dateInput.setAttribute("required", "required");
                } else {
                    detailsDiv.style.display = "none"; // Hide details section
                    licenseInput.removeAttribute("required");
                    dateInput.removeAttribute("required");
                    licenseError.textContent = "";
                    dateError.textContent = "";
                }
            });

        });


        restrictToLetters("[name='institute_name[]']");
        restrictToLetters("[name='work_level[]']");
        restrictToLetters("[name='Designation[]']");

        // Form validation on submit
        // document.getElementById("competency_form_ws").addEventListener("submit", function(event) {
            
        // });

    });

  
    function showDeclarationModal(form_name) {      

        let appl_types = $('#appl_type').val();

        let form_cost;

        if (appl_types == 'R') {
            if (form_name == 'A') {
                form_cost = 6000
            }
        } else {
            if (form_name == 'A') {
                form_cost = 12000
            }
        }

        const modal = new bootstrap.Modal(document.getElementById('declarationModal'));

        modal.show();


        document.getElementById('ea_fees').textContent = 'Rs.' + form_cost + '/-';

        document.addEventListener("DOMContentLoaded", function() {
            let applicantNameInput = document.getElementById("applicant_name");

            applicantNameInput.addEventListener("focus", function() {
                this.addEventListener("input", function() {
                    this.value = this.value.replace(/[^A-Za-z\s]/g,
                        ''); // Allow only letters and spaces
                });
            });
        });

        document.getElementById('ea_declarationProceedBtn').onclick = function() {
            const checkbox = document.getElementById('declaration-agree');
            const errorMsg = document.getElementById('declaration-error');

            if (!checkbox.checked) {
                errorMsg.classList.remove('d-none');
                return;
            }

            errorMsg.classList.add('d-none');
            modal.hide();

            var formData = new FormData($('#competency_form_a')[0]);
            var actionType = $(document.activeElement).hasClass("save-draft") ? "draft" : "submit";
            formData.append("form_action", actionType);

            let applicationId = $('#application_id').val();

            let url = applicationId ?
                "{{ route('forma.update', ['appl_id' => '__APPL_ID__']) }}".replace('__APPL_ID__', applicationId) :
                "{{ route('forma.store') }}";

            if (applicationId) {
                formData.append('_method', 'PUT');
            }

            $.ajax({
                url: url,
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {

                    if (response.status == 200) {
                        const login_id = window.login_id || "{{ auth()->user()->login_id ?? '' }}";
                        const application_id = response.application_id;
                        const transactionDate = new Date().toLocaleDateString(
                            'en-GB'); // e.g., 23/06/2025
                        const applicantName = response.applicantName || 'N/A';
                        const amount = form_cost;
                        const transactionId = "TRX" + Math.floor(100000 + Math.random() *
                            900000); // random ID
                        const payment_mode = 'UPI';

                        Swal.fire({
                            title: "<span style='color:#0d6efd;'>Initiate Payment</span>",
                            html: `
                                <div class="text-start" style="font-size: 14px; padding: 10px 0;">
                                    <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                                        <tbody>
                                            <tr>
                                                <th style="text-align: left; padding: 6px 10px; width: 50%; color: #555;">Application ID</th>
                                                <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${application_id}</td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; padding: 6px 10px; color: #555;">Transaction ID</th>
                                                <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${transactionId}</td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; padding: 6px 10px; color: #555;">Date</th>
                                                <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${transactionDate}</td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; padding: 6px 10px; color: #555;">Applicant Name</th>
                                                <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${applicantName}</td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; padding: 10px; color: #333;">Amount</th>
                                                <td style="text-align: right; padding: 10px; font-weight: bold; color: #0d6efd;">Rs. ${amount} /-</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            `,
                            icon: "info",
                            iconHtml: '<i class="swal2-icon" style="font-size: 1 em">ℹ️</i>',
                            width: '450px',
                            showCancelButton: true,
                            confirmButtonText: '<span class="btn btn-primary px-4 pr-4">Pay Now</span>',
                            cancelButtonText: '<span class="btn btn-danger px-4">Cancel</span>',
                            showCloseButton: true,
                            customClass: {
                                popup: 'swal2-border-radius',
                                actions: 'd-flex justify-content-around mt-3',
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: "{{ route('payment.updatePayment') }}",
                                    type: "POST",
                                    data: {
                                        login_id: login_id,
                                        application_id: application_id,
                                        transaction_id: transactionId,
                                        transactionDate: transactionDate,
                                        amount: amount,
                                        payment_mode: payment_mode,
                                        _token: $('meta[name="csrf-token"]').attr(
                                            'content')
                                    },

                                    success: function(response) {
                                        if (response.status == 200) {
                                            showPaymentSuccessPopup(application_id,
                                                transactionId, transactionDate,
                                                applicantName, amount);
                                        } else {
                                            alert(response.message ||
                                                'Something went wrong!');
                                        }
                                    },
                                    error: function(xhr) {
                                        if (xhr.responseJSON && xhr.responseJSON
                                            .errors) {
                                            let messages = Object.values(xhr
                                                    .responseJSON.errors).flat()
                                                .join("\n");
                                            // alert("Validation errors:\n" + messages);
                                            Swal.fire("Error", messages, "error");
                                        } else {
                                            alert(window.getAjaxErrorMessage(xhr));
                                        }
                                    }
                                });

                            } else {
                                Swal.fire("Payment Failed", "Application saved as draft",
                                    "error");
                            }
                        });
                    } else {
                        return
                    }
                },
                error: function(xhr) {
                    alert(window.getAjaxErrorMessage(xhr));
                }
            });
        };

    }


    // **Close PDF Popup and Redirect**
    function restrictToLetters(inputSelector) {
        $(document).on("input", inputSelector, function() {
            this.value = this.value.replace(/[^A-Za-z\s]/g, ''); // Allow only letters and spaces
        });
    }


    function maskAadhaarFull(aadhaar) {
        aadhaar = aadhaar.replace(/\D/g, '');
        if (aadhaar.length !== 12) return 'Invalid Aadhaar';
        return 'XXXXXXXX' + aadhaar.slice(-4);
    }


    // ---------------verify formA license---------------

    function verifyCompetencyLicense() {
        const licenseNumber = $('#competency_number').val().trim();
        const date = $('#competency_certificate_validity').val().trim();
        const resultBox = $('#competency_verify_result');

        if (!licenseNumber || !date) {
            resultBox.text('⚠ Enter license number and date.');
            return;
        }

        resultBox.html(`<span class="text-info">Verifying...</span>`);


        $.ajax({
            url: "{{ route('verifylicenseformAcc') }}",
            method: 'POST',
            data: {
                license_number: licenseNumber,
                date: date,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.exists) {
                    resultBox.html('<span class="text-success">&#10004; License verified.</span>');
                } else {
                    resultBox.html('<span class="text-white">&#10060; License not found.</span>');
                }
            },
            error: function(xhr) {
                resultBox.html('<span class="text-white">🚫 Error verifying license. Try again.</span>');
                console.error(xhr.responseText);
            }
        });
    }

    // Trigger verification on input change or blur
    $('#competency_number, #competency_certificate_validity').on('change blur', function() {
        verifyCompetencyLicense();
    });


    // **Close PDF Popup and Redirect**
    function restrictToLetters(inputSelector) {
        $(document).on("input", inputSelector, function() {
            this.value = this.value.replace(/[^A-Za-z\s]/g, ''); // Allow only letters and spaces
        });
    }


    function maskAadhaarFull(aadhaar) {
        aadhaar = aadhaar.replace(/\D/g, '');
        if (aadhaar.length !== 12) return 'Invalid Aadhaar';
        return 'XXXXXXXX' + aadhaar.slice(-4);
    }


    // ---------------verify formA license---------------

    function verifyCompetencyLicense() {
        const licenseNumber = $('#competency_number').val().trim();
        const date = $('#competency_certificate_validity').val().trim();
        const resultBox = $('#competency_verify_result');

        if (!licenseNumber || !date) {
            resultBox.text('⚠ Enter license number and date.');
            return;
        }

        resultBox.html(`<span class="text-info">Verifying...</span>`);


        $.ajax({
            url: "{{ route('verifylicenseformAcc') }}",
            method: 'POST',
            data: {
                license_number: licenseNumber,
                date: date,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.exists) {
                    resultBox.html('<span class="text-success">&#10004; License verified.</span>');
                } else {
                    resultBox.html('<span class="text-white">&#10060; License not found.</span>');
                }
            },
            error: function(xhr) {
                resultBox.html('<span class="text-white">🚫 Error verifying license. Try again.</span>');
                console.error(xhr.responseText);
            }
        });
    }

    // Trigger verification on input change or blur
    $('#competency_number, #competency_certificate_validity').on('change blur', function() {
        verifyCompetencyLicense();
    });


    // ---------------------------------------------

   $(document).on('change blur', '.competency_number, .competency_validity', function() {
    const $parent = $(this).closest('.competency-fields');

    // Safe value retrieval with fallback to empty string
    const licenseNumber = ($parent.find('.competency_number').val() || '').trim();
    const date = ($parent.find('.competency_validity').val() || '').trim();
    const resultBox = $parent.find('.competency_verify_result');

    if (!licenseNumber || !date) {
        resultBox.text('⚠ Enter license number and date.');
        return;
    }

    resultBox.html(`<span class="text-info">Verifying...</span>`);

    $.ajax({
        url: "{{ route('verifylicenseformAcc') }}",
        method: 'POST',
        data: {
            license_number: licenseNumber,
            date: date,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.exists) {
                resultBox.html('<span class="text-success">&#10004; License verified.</span>');
            } else {
                resultBox.html('<span class="text-danger">&#10060; License not found.</span>');
            }
        },
        error: function(xhr) {
            resultBox.html('<span class="text-danger">🚫 Error verifying license. Try again.</span>');
            console.error(xhr.responseText);
        }
    });
});



    // ------------------------------

    function verifyEALicense() {
        const licenseNumber = $('#previous_experience_lnumber').val().trim();
        const date = $('#previous_experience_lnumber_validity').val().trim();
        const resultBox = $('#competency_verifyea_result');

        if (!licenseNumber || !date) {
            resultBox.text('⚠ Enter license number and date.');
            return;
        }

        resultBox.html(`<span class="text-info">Verifying...</span>`);


        $.ajax({
            url: "{{ route('verifylicenseformAea') }}",
            method: 'POST',
            data: {
                license_number: licenseNumber,
                date: date,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.exists) {
                    resultBox.html('<span class="text-success">&#10004; License verified.</span>');
                } else {
                    resultBox.html('<span class="text-danger">&#10060; License not found.</span>');
                }
            },
            error: function(xhr) {
                resultBox.html('<span class="text-danger">🚫 Error verifying license. Try again.</span>');
                console.error(xhr.responseText);
            }
        });
    }

    // Trigger verification on input change or blur
    $('#previous_experience_lnumber, #previous_experience_lnumber_validity').on('change blur', function() {
        verifyEALicense();
    });

    // -------------------

    $(document).on('change blur', '.ea_license_number, .ea_license_validity', function() {
        const $parent = $(this).closest('.experience-fields');
        const licenseNumber = $parent.find('.ea_license_number').val().trim();
        const date = $parent.find('.ea_license_validity').val().trim();
        const resultBox = $parent.find('.ea_license_result');

        if (!licenseNumber || !date) {
            resultBox.text('⚠ Enter license number and date.');
            return;
        }

        resultBox.html(`<span class="text-info">Verifying...</span>`);


        $.ajax({
            url: "{{ route('verifylicenseformAea') }}",
            method: 'POST',
            data: {
                license_number: licenseNumber,
                date: date,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.exists) {
                    resultBox.html('<span class="text-success">&#10004; License verified.</span>');
                } else {
                    resultBox.html('<span class="text-danger">&#10060; License not found.</span>');
                }
            },
            error: function(xhr) {
                resultBox.html(
                    '<span class="text-danger">🚫 Error verifying license. Try again.</span>');
                console.error(xhr.responseText);
            }
        });
    });    
    
</script>

</body>

</html>

