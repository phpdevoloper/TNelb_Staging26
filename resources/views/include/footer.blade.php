
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
<script src="{{ url('assets/js/form_p_script.js') }}?v={{ filemtime(public_path('assets/js/form_p_script.js')) }}"></script>
<script>
// Blade values for the Form A / contractor licence code in forma.js
window.FORMA_CONFIG = {
    isDraft: @json(isset($application)),
    savedOwnership: @json($application->application_ownershiptype ?? ''),
    draftCount: @json($draftCount ?? 0),
    draftOwnershipType: @json(trim($application->application_ownershiptype ?? '') ?: ($ownershipType ?? '')),
    verifyLicenseCcUrl: @json(route('verifylicenseformAcc')),
    verifyLicenseEaUrl: @json(route('verifylicenseformAea')),
    alterUrl: @json(route('forma_alter')),
};
</script>
<script src="{{ url('assets/js/forma.js') }}?v={{ filemtime(public_path('assets/js/forma.js')) }}"></script>
<script src="{{ url('assets/js/formareturn.js') }}"></script>

<script src="{{ url('assets/js/forma_alt.js') }}"></script>
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
    formDraftSubmitUrl: @json(url('/form/draft_submit')),
    formDraftSubmitUrlTemplate: @json(url('/form/draft_submit/__APPL_ID__')),
    formWStoreUrl: @json(\Illuminate\Support\Facades\Route::has('form_w.store') ? route('form_w.store') : url('/form_w/store')),
    formWUpdateUrlTemplate: @json(\Illuminate\Support\Facades\Route::has('form_w.update') ? route('form_w.update', ['appl_id' => '__APPL_ID__']) : url('/form_w/update/__APPL_ID__')),
    formWDraftSubmitUrl: @json(\Illuminate\Support\Facades\Route::has('form_w.draft_submit') ? route('form_w.draft_submit') : url('/form_w/draft_submit')),
    formWDraftSubmitUrlTemplate: @json(url('/form_w/draft_submit/__APPL_ID__')),
    formWDraftRenewalUrlTemplate: @json(\Illuminate\Support\Facades\Route::has('form_w.draft_renewal_submit') ? route('form_w.draft_renewal_submit', ['appl_id' => '__APPL_ID__']) : url('/form_w/draft_renewal_submit/__APPL_ID__')),
    formWHStoreUrl: @json(\Illuminate\Support\Facades\Route::has('form_wh.store') ? route('form_wh.store') : url('/form_wh/store')),
    formWHUpdateUrlTemplate: @json(\Illuminate\Support\Facades\Route::has('form_wh.update') ? route('form_wh.update', ['appl_id' => '__APPL_ID__']) : url('/form_wh/update/__APPL_ID__')),
    formWHDraftSubmitUrl: @json(\Illuminate\Support\Facades\Route::has('form_wh.draft_submit') ? route('form_wh.draft_submit') : url('/form_wh/draft_submit')),
    formWHDraftSubmitUrlTemplate: @json(url('/form_wh/draft_submit/__APPL_ID__')),
    formWHDraftRenewalUrlTemplate: @json(\Illuminate\Support\Facades\Route::has('form_wh.draft_renewal_submit') ? route('form_wh.draft_renewal_submit', ['appl_id' => '__APPL_ID__']) : url('/form_wh/draft_renewal_submit/__APPL_ID__')),
    formPStoreUrl: @json(route('form_p.store')),
    formPUpdateUrl: @json(route('form_p.update')),
    formPDraftSubmitUrl: @json(route('form_p.saveDraft')),
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
    payuStatusUrl: @json(route('payu.status')),
};
window.CONTRACTOR_PAYU_CONFIG = {
    payuInitiateUrl: @json(route('payu.contractor.initiate')),
    csrfToken: @json(csrf_token()),
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
        if (dobInput && dobInput.type !== 'date' && dobInput.type !== 'hidden') {
            var storedDob = (dobInput.value || '').trim();
            var storedIso = storedDob.match(/^(\d{4})-(\d{2})-(\d{2})/);
            if (storedIso) {
                dobInput.value = storedIso[3] + '-' + storedIso[2] + '-' + storedIso[1];
            }
            if (dobInput.readOnly || dobInput.disabled) {
                dobInput.setAttribute('tabindex', '-1');
            } else {
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
        }


        $('#previously_number').on('keyup', function () {
            if (this.closest && this.closest('#competency_form_p')) {
                var formPLicenseError = document.getElementById('licenseError');
                if (formPLicenseError) formPLicenseError.textContent = '';
                return;
            }
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
        if (dobInput && (dobInput.type === 'date' || dobInput.type === 'hidden')) {
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



function getPaymentsService(licence_code,issued_licence,appl_type, options){
        const silent = !!(options && typeof options === 'object' && options.silent);

        return new Promise((resolve, reject) => {


                $.ajax({
                url: "{{ route('licences.getPaymentDetails') }}",
                type: "POST",
                data: {
                    licence_code: licence_code,
                    issued_licence: issued_licence,
                    appl_type:appl_type,
                    _token: $('meta[name="csrf-token"]').attr(
                        'content')
                },
                success: function(response) {

                    if (response.status == 'success') {
                        resolve(response.fees_details);
                    } else {
                        if (!silent) {
                            Swal.fire("Error", response.message, "error");
                        }
                        reject(response);
                    }
                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        let messages = Object.values(xhr.responseJSON.errors).flat().join("\n");
                        if (!silent) {
                            Swal.fire("Error", messages, "error");
                        }
                    } else {
                        if (!silent) {
                            Swal.fire("Error", window.getAjaxErrorMessage(xhr), "error");
                        }
                    }
                    reject(xhr);
                }
            });
        });

    }


    async function showDeclarationPopup(licence_code, directProceed = false) {

        try {

            let total_fees,renewl_fees,lateFee,lateMonths,form_cost, form_name, licence, renewalAmoutStartson, latefee_amount, latefee_starts,form_instruct,fees_date;

            const appl_type = $('#appl_type').val();
            const issued_licence = $('#license_number').val();
            const isDigitization = String(appl_type || '').trim().toUpperCase() === 'D';
            const noPaymentApplType = isNoPaymentApplType();

            const formResponse = await $.ajax({
                url: "{{ route('licences.getFormInstruction') }}",
                type: "POST",
                data: {
                    appl_type,
                    licence_code,
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (formResponse.status == 200) {
                form_instruct = formResponse.data;
            } else {
                Swal.fire("Error", "Instruction not available", "error");
                return;
            }

            const boardMemberFeeExempt = !noPaymentApplType
                && ($('#form_name').val() || '').trim().toUpperCase() === 'S'
                && ['N', 'R'].includes(String(appl_type || '').trim().toUpperCase())
                && typeof window.wxHasBoardMemberWorkRow === 'function'
                && window.wxHasBoardMemberWorkRow();

            let data = null;
            if (!noPaymentApplType && !boardMemberFeeExempt) {
                data = await getPaymentsService(licence_code, issued_licence, appl_type);
            }

            if (!noPaymentApplType && !boardMemberFeeExempt && !data) {
                Swal.fire("Error", "Unable to load payment details. Please try again.", "error");
                return;
            }

            if (noPaymentApplType || boardMemberFeeExempt) {
                $('#amount').val('0');
            }

            if (boardMemberFeeExempt) {
                $('#amount').val('0');
                $('#board_member_fee_exempt').val('1');
            } else if ($('#board_member_fee_exempt').length) {
                $('#board_member_fee_exempt').val('0');
            }

            const feeWaived = noPaymentApplType || boardMemberFeeExempt;

            if (feeWaived) {
                actual_fees = 0;
                total_fees = 0;
                lateFee = 0;
                lateMonths = 0;
            } else if (data.lateFees < 0) {
                actual_fees = data.basic_fees;
                total_fees = data.total_fees;
                lateMonths = data.late_months;
            } else {
                actual_fees = data.basic_fees;
                lateMonths = data.late_months;
                total_fees = data.total_fees;
                lateFee = data.lateFees;
            }

            fees_date = data ? data.fees_start_date : '';
            certificate_name = data ? data.certificate_name : '';

            const modalEl = document.getElementById('competencyInstructionsModal');
            if (!modalEl) {
                Swal.fire("Error", "Payment instructions modal is not available. Please refresh the page.", "error");
                return;
            }

            const agreeCheckbox = modalEl.querySelector('#declaration-agree-renew');
            const errorText = modalEl.querySelector('#declaration-error-renew');
            const proceedBtn = modalEl.querySelector('#proceedPayment');

            if (!agreeCheckbox || !errorText || !proceedBtn) {
                Swal.fire("Error", "Payment form controls are missing. Please refresh the page.", "error");
                return;
            }

            const certNameEl = document.getElementById('certificate_name');
            if (certNameEl) {
                certNameEl.textContent = certificate_name || '';
            }
            const feesStartEl = document.getElementById('fees_starts_from');
            if (feesStartEl) {
                feesStartEl.textContent = fees_date || '';
            }
            const formFeesEl = document.getElementById('form_fees');
            if (formFeesEl) {
                const applUpper = String(appl_type || '').trim().toUpperCase();
                formFeesEl.textContent = noPaymentApplType
                    ? (applUpper === 'A' ? 'No fee (Alteration)' : 'No fee (Digitization)')
                    : (boardMemberFeeExempt
                        ? 'Application fee-exempted for this application'
                        : ('Rs.' + actual_fees + '/-'));
                formFeesEl.style.color = boardMemberFeeExempt ? '#198754' : '';
            }
            const exemptNotice = document.getElementById('board-member-fee-exempt-notice');
            if (exemptNotice) {
                exemptNotice.classList.toggle('d-none', !boardMemberFeeExempt);
            }

            // Reset state
            agreeCheckbox.checked = false;
            errorText.classList.add('d-none');

            // Show modal
            const modalBody = modalEl.querySelector('#instructionContent');


            const delta = JSON.parse(form_instruct);

            const converter = new QuillDeltaToHtmlConverter(delta.ops, {
                inlineStyles: true,
                multiLineParagraph: false,
                listItemTag: "li",
                paragraphTag: "p"
            });

            let html = converter.convert();
            /* Stray "@" before (ii) / list markers when Quill split merge-tag text */
            html = html.replace(/@(\s*)(\(|\uFF08)/g, '$1$2');
            html = html.replace(/<(li|p)([^>]*)>@(\s*)(\(|\uFF08)/gi, '<$1$2>$3$4');
            modalBody.innerHTML = html;
            const el = document.querySelector("#instructionContent");


            // return false;

            const modal = new bootstrap.Modal(modalEl, {
                backdrop: 'static',
                keyboard: false
            });
            if (!directProceed) {
                modal.show();
            }

            // Remove old listeners
            proceedBtn.replaceWith(proceedBtn.cloneNode(true));

            // Re-assign click listener
            modalEl.querySelector('#proceedPayment').addEventListener('click', async function() {
                window._competencyPaymentProceedActive = true;
                if (!agreeCheckbox.checked) {
                    errorText.classList.remove('d-none');
                    window._competencyPaymentProceedActive = false;
                    return;
                }

                modal.hide();

                if (typeof window.normalizeIsoDateInputs === 'function') {
                    window.normalizeIsoDateInputs('#competency_form_ws');
                }
                let formData = new FormData($('#competency_form_ws')[0]);
                formData.set('form_action', (noPaymentApplType || boardMemberFeeExempt) ? 'submit' : 'draft');
                if (typeof window.appendWorkExperienceDateFieldsToFormData === 'function') {
                    window.appendWorkExperienceDateFieldsToFormData(formData, $('#competency_form_ws')[0]);
                }
                if (typeof window.appendWorkExperienceFilesToFormData === 'function') {
                    window.appendWorkExperienceFilesToFormData(formData, $('#competency_form_ws')[0]);
                }
                if (typeof window.appendWorkBoardMemberFieldsToFormData === 'function') {
                    window.appendWorkBoardMemberFieldsToFormData(formData, $('#competency_form_ws')[0]);
                }
                if (typeof window.appendCompetencyPhotoSignToFormData === 'function') {
                    window.appendCompetencyPhotoSignToFormData(formData, $('#competency_form_ws')[0]);
                }
                let applicationId = (typeof window.competencyDraftApplicationId === 'function')
                    ? window.competencyDraftApplicationId()
                    : ($('#competency_form_ws input[name="application_id"]').val() || '');
                let formUrl;

                if (applicationId) {
                    if (appl_type === 'R') {
                        formUrl = "{{ route('form.draft_renewal_submit', ['appl_id' => '__APPL_ID__']) }}"
                        .replace('__APPL_ID__', applicationId);
                    } else {
                        formUrl = "{{ route('form.update', ['appl_id' => '__APPL_ID__']) }}"
                        .replace('__APPL_ID__', applicationId);
                    }
                } else {
                    formUrl = "{{ route('form.store') }}";
                }

                // ---- Date helpers (avoid RangeError: Invalid time value) ----
                const safeIsoDateOnly = (value) => {
                    if (value === null || value === undefined) return null;
                    const raw = String(value).trim();
                    if (!raw) return null;

                    // Already ISO date-only
                    if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) return raw;

                    // Common DB datetime "YYYY-MM-DD HH:mm:ss" or "YYYY-MM-DDTHH:mm:ss"
                    const dbMatch = raw.match(/^(\d{4}-\d{2}-\d{2})[ T]/);
                    if (dbMatch) return dbMatch[1];

                    // "DD-MM-YYYY"
                    const dmy = raw.match(/^(\d{2})-(\d{2})-(\d{4})$/);
                    if (dmy) return `${dmy[3]}-${dmy[2]}-${dmy[1]}`;

                    // Last resort: Date.parse; never call toISOString on invalid date
                    const d = new Date(raw);
                    if (Number.isNaN(d.getTime())) return null;
                    return d.toISOString().slice(0, 10);
                };

                const formatDateDDMMYYYY = (isoDateOnly) => {
                    if (!isoDateOnly) return '';
                    const m = String(isoDateOnly).match(/^(\d{4})-(\d{2})-(\d{2})$/);
                    if (!m) return String(isoDateOnly);
                    return `${m[3]}-${m[2]}-${m[1]}`;
                };
                try {
                    // 🔹 Submit form
                    let saveResponse = await $.ajax({
                        url: formUrl,
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        error: function (xhr) {
                            console.error("Uncaught AJAX Error:", xhr);
                        }
                    });

                    if (saveResponse.status === "success") {


                        let form_type = isDigitization
                            ? 'Digitization Application'
                            : (String(appl_type || '').trim().toUpperCase() === 'A'
                                ? 'Alteration Application'
                                : (appl_type === 'R' ? 'Renewal Application' : 'New Application'));

                        const login_id = window.login_id || "{{ auth()->user()->login_id ?? '' }}";
                        const application_id = saveResponse.application_id;

                        const transactionDateIso = safeIsoDateOnly(saveResponse.date_apps) || safeIsoDateOnly(new Date());
                        const transactionDate = formatDateDDMMYYYY(transactionDateIso) || new Date().toLocaleDateString('en-GB');
                        // Backward-compatible alias (some pages/scripts expect this name)
                        const formatted_transaction_date = transactionDate;
                        const applicantName = saveResponse.applicantName || 'N/A';
                        const type_apps = saveResponse.type_of_apps || 'N/A';
                        const form_name = saveResponse.form_name || 'N/A';
                        const amount = total_fees;
                        const licence_name = saveResponse.licence_name || 'N/A';
                        const feeExemptSubmit = noPaymentApplType || boardMemberFeeExempt;

                        //console.log(transactionDate);

                        // const serviceCharge = 10;
                        // let lateFee = typeof lateFee !== "undefined" ? lateFee : 0;
                        // let total_charge = Number(amount) + Number(serviceCharge);
                        let lateFeeRow = "";
                        if(lateFee > 0){
                             lateFeeRow = `
                                <tr>
                                    <th style="text-align: left; padding: 6px 10px; color: #555;">Late Fees (${lateMonths} Months)</th>
                                    <td style="text-align: right; padding: 6px 10px; font-weight: 500;">Rs. ${lateFee} </td>
                                </tr>
                            `;
                        }


                        const payment_mode = 'UPI';

                        const runCompetencyPayment = async function () {
                            const txnId = 'TRX' + Math.floor(100000 + Math.random() * 900000);
                            let paymentResponse;
                            try {
                                paymentResponse = await $.ajax({
                                    url: "{{ route('payment.updatePayment') }}",
                                    type: "POST",
                                    dataType: "json",
                                    data: {
                                        login_id,
                                        application_id,
                                        applicantName,
                                        transaction_id: txnId,
                                        transactionDate: transactionDateIso || transactionDate,
                                        amount,
                                        payment_mode,
                                        form_name,
                                        form_type,
                                        lateFee: lateFee ?? 0,
                                        lateMonths: lateMonths ?? 0,
                                        board_member_fee_exempt: $('#board_member_fee_exempt').val() || '0',
                                        _token: $('meta[name="csrf-token"]').attr('content')
                                    },
                                    headers: {
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                });
                            } catch (xhr) {
                                const msg = (xhr && xhr.responseJSON && xhr.responseJSON.message)
                                    ? xhr.responseJSON.message
                                    : (xhr && xhr.responseJSON && xhr.responseJSON.errors
                                        ? Object.values(xhr.responseJSON.errors).flat().join(' ')
                                        : window.getAjaxErrorMessage(xhr));
                                throw new Error(msg || 'Payment could not be completed. Please try again.');
                            }

                            if (paymentResponse.status !== 200) {
                                throw new Error(paymentResponse.message || 'Payment could not be completed. Please try again.');
                            }

                            return {
                                transactionId: txnId,
                                application_id,
                                transactionDate,
                                applicantName,
                                amount,
                                form_type,
                                licence_name,
                            };
                        };

                        // Zero-fee paths — submit directly (no payment gateway UI)
                        if (feeExemptSubmit) {
                            try {
                                showPaymentSuccessPopup(
                                    application_id,
                                    '',
                                    transactionDate,
                                    applicantName,
                                    0,
                                    form_type,
                                    licence_name,
                                    false,
                                    { feeExempt: true, boardMemberExempt: boardMemberFeeExempt }
                                );
                            } catch (err) {
                                Swal.fire({
                                    title: (noPaymentApplType || boardMemberFeeExempt) ? 'Submission Failed' : 'Payment Failed',
                                    text: err.message || 'Something went wrong. Please try again.',
                                    icon: 'error'
                                });
                            }
                            return;
                        }

                        // Paid applications — confirm fees before gateway (Pay Now can be retried on failure)
                        Swal.fire({
                            title: "<span style='color:#0d6efd;'>₹ Payment Details</span>",
                            html: `
                            <div class="text-start" style="font-size: 14px; padding: 10px 0;">
                                <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                                    <tbody>
                                            <tr>
                                            <th style="text-align: left; padding: 6px 10px; color: #555;">Application ID</th>
                                            <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${application_id}</td>
                                            </tr>
                                            <tr>
                                            <th style="text-align: left; padding: 6px 10px; color: #555;">Applicant Name</th>
                                            <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${applicantName}</td>
                                            </tr>
                                            <tr>
                                            <th style="text-align: left; padding: 6px 10px; color: #555;">Type of Application</th>
                                            <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${licence_name}</td>
                                            </tr>
                                            <tr>
                                            <th style="text-align: left; padding: 6px 10px; color: #555;">Type of Form</th>
                                            <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${form_type}</td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; padding: 6px 10px; color: #555;">Date</th>
                                                <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${formatted_transaction_date}</td>
                                                </tr>
                                                <tr>
                                                    <th style="text-align: left; padding: 10px; color: #333;">Application Fees</th>
                                                    <td style="text-align: right; padding: 10px; font-weight: bold; color: #0d6efd;">Rs. ${actual_fees} </td>
                                                    </tr>
                                                            ${lateFeeRow}
                                                                <tr>
                                                                    <th style="text-align: left; padding: 6px 10px; color: #555;">Total</th>
                                                                    <td style="text-align: right; padding: 6px 10px; font-weight: 500;">Rs. ${amount}</td>
                                                                    </tr>
                                                                    </tbody>
                                                                    </table>
                                                                    </div>
                                                                    `,
                            width: '515px',
                            showCancelButton: true,
                            confirmButtonText: '<span class="btn btn-primary px-4 pr-4 payment">Pay Now</span>',
                            cancelButtonText: '<span class="btn btn-danger px-4">Cancel</span>',
                            showCloseButton: false,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showLoaderOnConfirm: true,
                            customClass: {
                                popup: 'swal2-border-radius',
                                actions: 'd-flex justify-content-around mt-3',
                            },
                            buttonsStyling: false,
                            footer: '<div><span style="font-size: 13px;">Note: </span><span style="font-size: 13px;color: red;">The total amount is exclusive of payment gateway service charges.</span>',
                            preConfirm: async () => {
                                try {
                                    return await runCompetencyPayment();
                                } catch (err) {
                                    Swal.showValidationMessage(
                                        err.message || 'Payment failed. You can click Pay Now to try again.'
                                    );
                                    return false;
                                }
                            }

                        }).then((result) => {
                            if (result.isConfirmed && result.value) {
                                const paid = result.value;
                                showPaymentSuccessPopup(
                                    paid.application_id,
                                    paid.transactionId,
                                    paid.transactionDate,
                                    paid.applicantName,
                                    paid.amount,
                                    paid.form_type,
                                    paid.licence_name,
                                    false,
                                    { feeExempt: noPaymentApplType }
                                );
                                return;
                            }
                            if (result.dismiss === Swal.DismissReason.cancel) {
                                Swal.fire({
                                    title: "Payment Failed!",
                                    text: "Application Saved as Draft",
                                    icon: "error",
                                    timer: 3000,
                                    timerProgressBar: true
                                }).then(() => {
                                    window.location.href = BASE_URL+"/dashboard";
                                });
                            }
                        });
                    } else {
                        Swal.fire("Form Submission Failed", "Application not submitted", "error");
                    }
                } catch (xhr) {
                    console.error("❌ Form Submit Error:", xhr);

                    // Handle date parsing/formatting issues gracefully
                    if (xhr instanceof RangeError && String(xhr.message || '').toLowerCase().includes('invalid time value')) {
                        Swal.fire({
                            icon: "warning",
                            title: "Invalid Date",
                            text: "Please check all date fields and try again."
                        });
                        return;
                    }

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        const errors = xhr.responseJSON.errors;

                        // Remove any old error labels
                        $('.server-error').remove();
                        $('.is-invalid').removeClass('is-invalid');

                        $.each(errors, function (field, messages) {
                            // Find input by name (supports array names)
                            const input = $('[name="' + field + '"]');
                            if (input.length) {
                                input.addClass('is-invalid');
                                input.after('<span class="text-danger server-error">' + messages[0] + '</span>');
                            }
                        });

                        Swal.fire({
                            icon: "warning",
                            title: "Validation Error",
                            text: "Please correct the highlighted fields."
                        });
                        return;
                    }

                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: window.getAjaxErrorMessage(xhr)
                    });
                } finally {
                    window._competencyPaymentProceedActive = false;
                }

            });

            if (directProceed) {
                agreeCheckbox.checked = true;
                modalEl.querySelector('#proceedPayment').click();
                return;
            }
        } catch (err) {
            console.error('showDeclarationPopup failed:', err);
            Swal.fire({
                icon: 'error',
                title: 'Unable to proceed to payment',
                text: err && err.message ? err.message : 'Something went wrong. Please try again.'
            });
        }
    }

    function showPaymentSuccessPopup(loginId, transactionId, transactionDate, applicantName, amount, form_type, licence_name, isFormP, options) {
        isFormP = (typeof isFormP !== 'undefined' && isFormP === true);
        options = options || {};
        window.paymentIsFormP = isFormP;

        const isFeeExemptSubmit = (typeof isFeeExemptCompetencySuccess === 'function')
            ? isFeeExemptCompetencySuccess(loginId, form_type, options.feeExempt === true)
            : (options.feeExempt === true);
        const $modal = $("#paymentSuccessModal");

        $("#ps_applicantName_competency").text(applicantName);
        $("#ps_applicationId_competency").text(loginId);
        $("#ps_licenceName_competency").text(licence_name);
        $("#ps_transactionDate_competency").text(transactionDate);

        // Digitisation (D) / Alteration (A) / board-member waiver: never show or retain payment details.
        if (isFeeExemptSubmit) {
            $("#ps_transactionId_competency").text('');
            $("#ps_amount_competency").text('');
            $modal.find("#ps_success_modal_title").text("Application Submitted Successfully!");
            $modal.find(".ps-payment-only").addClass("d-none");
            $modal.find(".ps-transaction-date-label").text("Submission Date:");
            $modal.find(".ps-app-pdf-heading").removeClass("mt-3");
        } else {
            $("#ps_transactionId_competency").text(transactionId);
            $("#ps_amount_competency").text(amount);
            $modal.find("#ps_success_modal_title").text("Payment Successful!");
            $modal.find(".ps-payment-only").removeClass("d-none");
            $modal.find(".ps-transaction-date-label").text("Transaction Date:");
            $modal.find(".ps-app-pdf-heading").addClass("mt-3");
        }
        const boardMemberExempt = options.boardMemberExempt === true;
        const $exemptNotice = $modal.find("#ps_fee_exempt_notice");
        if (boardMemberExempt) {
            $exemptNotice.removeClass("d-none");
        } else {
            $exemptNotice.addClass("d-none");
        }

        // store ID globally for download actions
        window.paymentAppId = loginId;
        window.paymentFormType = form_type;
        $modal.modal({
            backdrop: 'static',
            keyboard: false
        });

        // Show bootstrap modal
        $modal.modal("show");


        // Swal.fire({
        //     title: `<h3 style="color:#198754; font-size:1.5rem;">Payment Successful!</h3>`,
        //     html: `
        //     <div style="font-size: 14px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: flex-start;">
        //         <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; max-width: 90%; margin: 0 auto;">
        //             <div style="
        //             display: grid;
        //             grid-template-columns: auto 1fr;
        //             gap: 7px 50px;
        //             font-size: 14px;
        //             max-width: 350px;
        //             border-right:2px solid #0d6efd;
        //             padding: 0px 15px;
        //             ">
        //             <div style="font-weight: bold;">Applicant Name:</div>
        //             <div>${applicantName}</div>
        //             <div style="font-weight: bold;">Application ID:</div>
        //             <div style="word-break: break-word;">${loginId}</div>

        //             <div style="font-weight: bold;">Type of Application:</div>
        //             <div style="word-break: break-word;">${licence_name}</div>

        //             <div style="font-weight: bold;">Transaction ID:</div>
        //             <div style="word-break: break-word;">${transactionId}</div>

        //             <div style="font-weight: bold;">Transaction Date:</div>
        //             <div>${transactionDate}</div>


        //             <div style="font-weight: bold;">Amount Paid:</div>
        //             <div>${amount}</div>
        //             </div>
        //             <div style="min-width: 200px; text-align: center;">
        //                 <p><strong>Download Your Payment Receipt:</strong></p>
        //                 <button class="btn btn-info btn-sm mb-2" onclick="paymentreceipt('${loginId}')">
        //                     <i class="fa fa-file-pdf-o text-danger"></i>
        //                     <i class="fa fa-download text-danger"></i>
        //                     Download Receipt
        //                     </button>
        //                     <p class="mt-2"><strong>Download Your Application PDF:</strong></p>
        //                     <button class="btn btn-primary btn-sm me-1" onclick="downloadPDF('english', '${loginId}')"><i class="fa fa-file-pdf-o text-danger"></i>
        //                         English</button>
        //                         <button class="btn btn-success btn-sm" onclick="downloadPDF('tamil', '${loginId}')"><i class="fa fa-file-pdf-o text-danger"></i>
        //                             Tamil</button>
        //                             </div>
        //                             </div>
        //                             </div>
        //                             `,
        //     // 🧹 removed: icon: "success",
        //     width: '50%',
        //     customClass: {
        //         popup: 'swal2-border-radius p-3'
        //     },
        //     confirmButtonText: "Go to Dashboard",
        //     confirmButtonColor: "#0d6efd",
        //     allowOutsideClick: false,
        //     allowEscapeKey: false,
        //     showCloseButton: false,
        //     didOpen: () => {
        //         const iconEl = document.querySelector('.swal2-icon');
        //         if (iconEl) {
        //             iconEl.style.display = 'none'; // hide icon if still rendered
        //         }

        //         const popup = document.querySelector('.swal2-popup');
        //         if (popup) {
        //             popup.style.marginTop = '10px';
        //             popup.style.padding = '10px 20px';
        //         }

        //         const container = document.querySelector('.swal2-container');
        //         if (container) {
        //             container.style.alignItems = 'flex-start';
        //             container.style.paddingTop = '20px';
        //         }
        //     },
        //     willClose: () => {
        //         window.location.href = BASE_URL + '/dashboard';
        //     }
        // });

    }



    // Open Payment Receipt in New Tab
    //  function paymentreceipt(loginId) {
    //     window.open(`/payment-receipt/${loginId}`, '_blank');
    // }

    function downloadPDF(language) {
        if (!window.paymentAppId) {
            alert("Application ID not found!");
            return;
        }
        let url;
        if (window.paymentIsFormP) {
            // Form P (Competency) application PDF routes
            url = (language === 'tamil')
                ? `${BASE_URL}/generatePDFFormPTA/${window.paymentAppId}`
                : `${BASE_URL}/generate-pdf-p/${window.paymentAppId}`;
        } else {
            url = (language === 'tamil')
                ? `${BASE_URL}/generateTamilPDF/${window.paymentAppId}`
                : `${BASE_URL}/generate-pdf/${window.paymentAppId}`;
        }
        window.open(url, '_blank');
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



    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('infoModal');
        if (!modalElement) {
            return;
        }

        // Bootstrap 4 appends the backdrop to <body>. This modal lives inside
        // .page-wrapper (z-index: 9), so the backdrop sat on top and blocked clicks.
        if (modalElement.parentElement !== document.body) {
            document.body.appendChild(modalElement);
        }

        if (window.jQuery && typeof window.jQuery.fn.modal === 'function') {
            const $modal = window.jQuery(modalElement);

            $modal
                .off('shown.bs.modal.noticeAlert hidden.bs.modal.noticeAlert')
                .on('shown.bs.modal.noticeAlert', function () {
                    window.jQuery('.modal-backdrop').last().addClass('notice-alert-backdrop');
                    const okButton = modalElement.querySelector('.notice-alert__ok');
                    if (okButton) {
                        okButton.focus();
                    }
                })
                .on('hidden.bs.modal.noticeAlert', function () {
                    window.jQuery('.modal-backdrop.notice-alert-backdrop').remove();
                })
                .modal({
                    backdrop: 'static',
                    keyboard: true,
                    show: true
                });
            return;
        }

        if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
            new bootstrap.Modal(modalElement).show();
        }
    });

</script>

</body>

</html>

