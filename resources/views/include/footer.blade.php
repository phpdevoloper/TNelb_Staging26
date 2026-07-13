
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

<!--
<script>
    $('a[data-toggle="formtab"]').click(function(event) {
        event.preventDefault(); // Prevent default anchor click behavior
        var targetId = $(this).attr('href'); // Get the target tab's ID

        // Remove active class from all tabs and links
        $('.tabs-panels').removeClass('active');
        $('a[data-toggle="formtab"]').removeClass('active');

        // Add active class to the clicked tab and its corresponding content
        $(targetId).addClass('active');
        $('a[href="' + targetId + '"]').addClass('active');
    });
</script> -->

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

        /** Remove pay-validation errors without deleting empty placeholder spans in the form markup. */
        function clearCompetencyValidationErrors() {
            $('.error-message.d-block.mt-1, .error-message.work-exp-date-range-error').remove();
            $('.error-message').each(function () {
                var $el = $(this);
                if (($el.text() || '').trim() !== '') {
                    $el.text('');
                }
            });
        }

        function showCompetencyFieldError($field, message) {
            if (!$field || !$field.length) {
                return;
            }
            var $err = $field.nextAll('.error-message').first();
            if (!$err.length) {
                $err = $('<span class="error-message text-danger d-block mt-1"></span>');
                $field.after($err);
            } else {
                $err.addClass('d-block mt-1');
            }
            $err.text(message);
        }

        function clearCompetencyFieldError($field) {
            if (!$field || !$field.length) {
                return;
            }
            $field.nextAll('.error-message').each(function () {
                var $err = $(this);
                if ($err.hasClass('d-block') && $err.hasClass('mt-1')) {
                    $err.remove();
                } else {
                    $err.text('');
                }
            });
        }

        function readApplicantEmailValue() {
            var el = document.getElementById('applicant_email');
            if (!el) {
                return '';
            }
            return String(el.value || $(el).val() || '').trim();
        }

        /** Date order / min-duration for work rows: below table when #work-exp-validation-msg exists; else inline in row. */
        function parseWorkDateToIso(str) {
            var s = String(str || '').trim();
            if (!s) {
                return '';
            }
            if (/^\d{4}-\d{2}-\d{2}$/.test(s)) {
                return s;
            }
            var m = s.match(/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/);
            if (m) {
                return m[3] + '-' + String(m[2]).padStart(2, '0') + '-' + String(m[1]).padStart(2, '0');
            }
            return '';
        }

        function readWorkDateIsoGeneric($input) {
            if (!$input || !$input.length) {
                return '';
            }
            var $el = $input.first();
            var node = $el.get(0);
            if (!node) {
                return '';
            }
            var candidates = [];
            if (node.type === 'date' && node.value) {
                candidates.push(String(node.value).trim());
            }
            candidates.push(String($el.val() || node.value || '').trim());
            candidates.push(String($el.attr('data-raw') || node.getAttribute('data-raw') || '').trim());

            for (var i = 0; i < candidates.length; i++) {
                var iso = parseWorkDateToIso(candidates[i]);
                if (iso) {
                    return iso;
                }
            }
            return '';
        }

        function clearWorkDateRequiredErrors($field) {
            if (!$field || !$field.length) {
                return;
            }
            $field.nextAll('.error-message').each(function () {
                var txt = ($(this).text() || '').toLowerCase();
                if (
                    txt.indexOf('to date is required') !== -1 ||
                    txt.indexOf('from date is required') !== -1
                ) {
                    $(this).remove();
                }
            });
        }

        window.readWorkDateIsoGeneric = readWorkDateIsoGeneric;
        window.clearWorkDateRequiredErrors = clearWorkDateRequiredErrors;

        function refreshWorkExpValidationMsgBelowTable() {
            var $container = $('#work-exp-validation-msg');
            if (!$container.length) {
                return false;
            }

            var formName = String($('#form_name').val() || '').trim().toUpperCase();
            var messages = [];

            $('.js-work-container .work-fields, #work-container .work-fields').each(function () {
                var $row = $(this);
                var $fromDate = $row.find('.work-date-from').first();
                var $toDate = $row.find('.work-date-to').first();
                var fromIso = readWorkDateIsoGeneric($fromDate);
                var toIso = readWorkDateIsoGeneric($toDate);
                /* Form S only: a "Till date" checkbox can replace the explicit To-date with today. */
                if (formName === 'S' && $row.find('.work-date-till').is(':checked')) {
                    var t = new Date();
                    toIso = t.getFullYear() + '-' + String(t.getMonth() + 1).padStart(2, '0') + '-' + String(t.getDate()).padStart(2, '0');
                }

                if (formName === 'WH') {
                    var wl0 = ($row.find('input[name="work_level[]"]').val() || '').trim();
                    var ex0 = ($row.find('input[name="experience[]"]').val() || '').trim();
                    var des0 = ($row.find('input[name="designation[]"]').val() || '').trim();
                    if (!(wl0 !== '' || ex0 !== '' || des0 !== '' || fromIso !== '' || toIso !== '')) {
                        return;
                    }
                }

                if (!fromIso || !toIso) {
                    return;
                }

                var from = new Date(fromIso + 'T12:00:00');
                var to = new Date(toIso + 'T12:00:00');
                if (isNaN(from.getTime()) || isNaN(to.getTime())) {
                    return;
                }

                var serial = ($row.find('.work-serial').text() || '').trim();
                var prefix = serial ? ('Row ' + serial + ': ') : '';

                if (to < from) {
                    messages.push(prefix + 'To date must be greater than or equal to From date.');
                    return;
                }

                if (formName === 'WH') {
                    var minTo = new Date(from.getTime());
                    minTo.setFullYear(minTo.getFullYear() + 2);
                    if (to < minTo) {
                        messages.push(prefix + 'Minimum 2 Years Experience needed');
                    }
                }
            });

            if (messages.length) {
                $container.html(
                    messages.map(function (msg) {
                        return '<div class="error-message text-danger work-exp-date-range-error">' + msg + '</div>';
                    }).join('')
                );
            } else {
                $container.empty();
            }

            return true;
        }

        function clearWorkExpDateRangeError($row) {
            if (refreshWorkExpValidationMsgBelowTable()) {
                return;
            }
            if (!$row || !$row.length) {
                return;
            }
            $row.find('.work-exp-date-range-error').remove();
            var $inline = $row.find('.work-exp-col-years .work-exp-inline').not('.work-exp-inline--head').first();
            if ($inline.length) {
                $inline.next('.work-exp-date-range-error').remove();
            }
        }

        function showWorkExpDateRangeError($row, message) {
            if (refreshWorkExpValidationMsgBelowTable()) {
                return;
            }
            if (!$row || !$row.length) {
                return;
            }
            clearWorkExpDateRangeError($row);
            var cls = 'error-message text-danger d-block work-exp-date-range-error';
            var html = '<span class="' + cls + '" role="alert">' + message + '</span>';
            var $dateSlot = $row.closest('.work-entry-block').children('.work-row-date-validation').first();
            if (!$dateSlot.length) {
                $dateSlot = $row.next('.work-row-date-validation').first();
            }
            if (!$dateSlot.length) {
                $dateSlot = $row.find('.work-row-date-validation').first();
            }
            if ($dateSlot.length) {
                $dateSlot.html(html);
                return;
            }
            var $toCell = $row.find('.work-card-field[data-field="to-date"]').first();
            if ($toCell.length) {
                $toCell.append(html);
                return;
            }
            var $inline = $row.find('.work-exp-col-years .work-exp-inline').not('.work-exp-inline--head').first();
            if ($inline.length) {
                $inline.after(html);
            } else {
                var $flex = $row.find('.d-flex').has('.work-date-from, .work-date-to').first();
                if ($flex.length) {
                    $flex.after(html);
                } else {
                    var $toDate = $row.find('.work-date-to').first();
                    if ($toDate.length) {
                        $toDate.after(html);
                    }
                }
            }
        }

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





        $("#add-more-education").off('click.dynamicRows').on('click.dynamicRows', function() {
            // Clone the last set of education fields
            let newFields = $(".education-fields").last().clone();

            // Clear input values inside the cloned fields
            newFields.find("input").val("");
            newFields.find("select").prop("selectedIndex", 0);

            // Append cloned fields to the container
            $("#education-container").append(newFields);
        });

        $("#add-more-work").off('click.dynamicRows').on('click.dynamicRows', function() {
            // Clone the last work fields section
            let newFields = $(".work-fields").last().clone();

            // Clear input values
            newFields.find("input").val("");
            newFields.find("input[type='checkbox']").prop("checked", false);

            // Append cloned fields to the container
            $("#work-container").append(newFields);
        });


        // License verfication---------------------------------------
        
        $('#verify_btn').on('click', function() {
            let dateError = document.getElementById("dateError");

            
            const licenseNumber = $('#previously_number').val().trim();
            const date = $('#previously_date').val().trim();
            const verify_result = document.getElementById("licenseError");
            const $btn = $(this);
            const regex = /^(B|H|LB|LWH)\d+$/;
            
            licenseError.textContent = '';
            dateError.textContent = '';

            let isValid = true;

            if (licenseNumber === '' || !regex.test(licenseNumber)) {
            // $btn.after('<div class="text-danger mt-1">⚠️ Enter license number and date.</div>');
                verify_result.textContent = 'License Number is required.';
                isValid = false;
            }

            if (date === '') {
                dateError.textContent = 'Date is required';
                isValid = false;
            }else {
                const regexDate = /^(\d{4})-(\d{2})-(\d{2})$/; 
                const parts = date.match(regexDate);

                if (!parts) {
                    $('#dateError').text('Enter a valid date');
                    isValid = false;
                } else {
                    const year = parseInt(parts[1], 10);
                    const month = parseInt(parts[2], 10) - 1;
                    const day = parseInt(parts[3], 10);

                    const checkDate = new Date(year, month, day);

                    if (
                        checkDate.getFullYear() !== year ||
                        checkDate.getMonth() !== month ||
                        checkDate.getDate() !== day ||
                        year < 1800 // ✅ Optional: Prevents year < 1900
                    ) {
                        $('#dateError').text('Enter a valid date');
                        isValid = false;
                    }
                }
            }

            if (!isValid) return;

            // Save original button text
            const originalBtnHtml = $btn.html();

            // Remove any previous message
            $('.license-result-msg').remove();


            $.ajax({
                url: "{{ route('verifylicense') }}",
                method: "POST",
                data: {
                    license_number: licenseNumber,
                    date: date,
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                success: function(response) {
                    let $msgBox = $("#license_message");
                    let $licenseNumber = $('#l_verify');

                    if (response.exists) {

                        isLicenseVerified = true;
                        $licenseNumber.val('1');

                        $msgBox
                            .removeClass("text-danger")
                            .addClass("text-success")
                            .html("&#10004; Valid License.");
                    } else {
                        isLicenseVerified = false;
                        $licenseNumber.val('0');
                        $msgBox
                            .removeClass("text-success")
                            .addClass("text-danger")
                            .html("&#10060; Invalid License.");
                    }
                },
                error: function(xhr, status, error) {
                    let $msgBox = $("#license_message");

                    $msgBox
                        .removeClass("text-success")
                        .addClass("text-danger")
                        .html("🚫 Error verifying license. Try again.");

                    console.error(xhr.responseText || error);
                },
            });

        });


      
        //-------------------------------------------------- competency form submit action---------------------------------------


        async function isSelectedFileReadable(file) {
            if (!file) return true;
            if (typeof file.arrayBuffer !== 'function') return true;
            try {
                await file.arrayBuffer();
                return true;
            } catch (err) {
                return false;
            }
        }

        async function validateReadableSelectedFiles() {
            const $form = $('#competency_form_ws');
            if (!$form.length) return true;

            const broken = [];
            const fileInputs = $form.find('input[type="file"]').toArray();
            for (const input of fileInputs) {
                const file = input.files && input.files[0] ? input.files[0] : null;
                if (!file) continue;

                const ok = await isSelectedFileReadable(file);
                if (!ok) {
                    const labelText = $(`label[for="${input.id}"]`).first().text().trim() || input.name || input.id || 'Selected file';
                    broken.push(labelText);
                    input.value = '';
                }
            }

            if (!broken.length) return true;

            const unique = [...new Set(broken)];
            const isEducationMissing = unique.length === 1 && /education_document/i.test(unique[0]);
            const msg = isEducationMissing
                ? 'Selected file is missing or deleted on education upload. Please choose the file again.'
                : (unique.length === 1
                    ? `Selected file is not accessible for "${unique[0]}". Please choose the file again.`
                    : `Some selected files are not accessible: ${unique.join(', ')}. Please choose them again.`);
            Swal.fire({
                icon: 'warning',
                title: 'File Not Accessible',
                text: msg
            });
            return false;
        }

        $(document).on('click', '.local-file-preview .preview-link', async function (e) {
            e.preventDefault();

            const $link = $(this);
            const href = $link.attr('href');
            const target = $link.attr('target') || '_blank';
            const $preview = $link.closest('.local-file-preview');
            const $scope = $preview.closest('td, .file-section, .form-group, .education-fields, .work-fields, .col-12, .col-md-7');
            const $fileInput = $scope.find('input[type="file"]').first();
            const input = $fileInput.get(0);
            const file = input && input.files && input.files[0] ? input.files[0] : null;

            // In preview modal, file inputs are removed; allow opening existing href/blob link directly.
            if (!file) {
                if (href) {
                    window.open(href, target);
                    return;
                }
            }

            if (!file) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Not Accessible',
                    text: 'Selected file is missing or deleted on education upload. Please choose the file again.'
                });
                return;
            }

            const readable = await isSelectedFileReadable(file);
            if (!readable) {
                if (input) input.value = '';
                Swal.fire({
                    icon: 'warning',
                    title: 'File Not Accessible',
                    text: 'Selected file is missing or deleted on education upload. Please choose the file again.'
                });
                return;
            }

            window.open(href, target);
        });

        function isWiremanOrWiremanHelperForm() {
            const formName = ($('#form_name').val() || '').toString().trim().toUpperCase();
            return formName === 'W' || formName === 'WH';
        }

        function clearLocalFilePreviewForInput($input) {
            if (!$input || !$input.length) return;
            const $scope = $input.closest('td, .file-section, .form-group, .education-fields, .work-fields');
            const $preview = $scope.find('.local-file-preview').first();
            const blobUrl = $preview.data('blobUrl');
            if (blobUrl) {
                try { URL.revokeObjectURL(blobUrl); } catch (e) {}
            }
            $preview.remove();
            $input.removeAttr('data-has-local-file');
        }

        function renderLocalFilePreviewForInput($input) {
            if (!isWiremanOrWiremanHelperForm()) return;
            if (!$input || !$input.length) return;

            clearLocalFilePreviewForInput($input);

            const input = $input.get(0);
            const file = input && input.files && input.files[0] ? input.files[0] : null;
            if (!file) return;

            const blobUrl = URL.createObjectURL(file);
            $input.attr('data-has-local-file', '1');

            const $preview = $('<div class="local-file-preview"></div>').data('blobUrl', blobUrl);
            const $link = $('<a></a>', {
                href: blobUrl,
                target: '_blank',
                rel: 'noopener noreferrer',
                class: 'preview-link'
            }).text('View Document');

            $preview.append('<i class="fa fa-file-pdf-o text-danger" aria-hidden="true"></i> ');
            $preview.append($link);

            const $wrap = $input.closest('.form-s-file-upload-wrap');
            if ($wrap.length) {
                const $limitText = $wrap.parent().find('.file-limit').first();
                if ($limitText.length) {
                    $preview.insertBefore($limitText);
                } else {
                    $wrap.after($preview);
                }
            } else {
                const $limitText = $input.parent().find('.file-limit').first();
                if ($limitText.length) {
                    $preview.insertBefore($limitText);
                } else {
                    $input.after($preview);
                }
            }
        }

        // W / WH forms: show "View Document" for all selected uploads except
        // photo/signature (they already have an inline image preview).
        $(document).on('change', '#competency_form_ws input[type="file"]', function () {
            var inputName = this.name || '';
            if (inputName === 'upload_photo' || inputName === 'upload_sign') return;
            renderLocalFilePreviewForInput($(this));
        });

        function resolveCompetencyApplType() {
            const $form = $('#competency_form_ws, #competency_form_p').first();
            if ($form.length) {
                const fromForm = String($form.find('[name="appl_type"]').first().val() || '').trim().toUpperCase();
                if (fromForm) {
                    return fromForm;
                }
            }
            return String($('#appl_type').val() || '').trim().toUpperCase();
        }
        window.resolveCompetencyApplType = resolveCompetencyApplType;

        function isDigitizationApplType() {
            return resolveCompetencyApplType() === 'D';
        }
        window.isDigitizationApplType = isDigitizationApplType;

        /** Digitisation and Alteration submit without payment; New/Renewal require payment. */
        function isNoPaymentApplType() {
            const appl = resolveCompetencyApplType();
            return appl === 'D' || appl === 'A';
        }
        window.isNoPaymentApplType = isNoPaymentApplType;

        function isFeeExemptCompetencySuccess(applicationId, formType, feeExemptHint) {
            if (feeExemptHint === true) {
                return true;
            }
            if (typeof isNoPaymentApplType === 'function' && isNoPaymentApplType()) {
                return true;
            }
            const appId = String(applicationId || '').trim().toUpperCase();
            if (appId.startsWith('D') || appId.startsWith('A')) {
                return true;
            }
            return /digitization|alteration/i.test(String(formType || ''));
        }
        window.isFeeExemptCompetencySuccess = isFeeExemptCompetencySuccess;

        async function saveCompetencyDraftSilently() {
            const formWsEl = $('#competency_form_ws')[0];
            const formPEl = $('#competency_form_p')[0];
            const formEl = formWsEl || formPEl;
            if (!formEl) return null;

            const formData = new FormData(formEl);
            formData.set('form_action', 'draft');

            if (formPEl) {
                formData.delete('month_passing[]');
                $('#competency_form_p select[name="month_of_passing[]"]').each(function () {
                    formData.append('month_passing[]', $(this).val() || '');
                });
            }

            const applType = $('#appl_type').val();
            const applicationId = ($('#application_id').val() || '').trim();
            let formUrl = '';

            if (formWsEl) {
                if (applicationId) {
                    if (applType === 'R') {
                        formUrl = "{{ route('form.draft_renewal_submit', ['appl_id' => '__APPL_ID__']) }}".replace('__APPL_ID__', applicationId);
                    } else {
                        // New (N) and Digitization (D) — update existing draft
                        formUrl = "{{ route('form.update', ['appl_id' => '__APPL_ID__']) }}".replace('__APPL_ID__', applicationId);
                    }
                } else {
                    formUrl = "{{ route('form.store') }}";
                }
            } else if (formPEl) {
                if (applicationId) {
                    if (applType === 'R') {
                        formUrl = "{{ route('form_p.draft_renewal_submit', ['appl_id' => '__APPL_ID__']) }}".replace('__APPL_ID__', applicationId);
                    } else {
                        formUrl = "{{ route('form_p.update') }}";
                    }
                } else {
                    formUrl = "{{ route('form_p.store') }}";
                }
            }

            try {
                const saveResponse = await $.ajax({
                    url: formUrl,
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                if (saveResponse && saveResponse.status === "success" && saveResponse.application_id) {
                    $('#application_id').val(saveResponse.application_id);
                }

                return saveResponse;
            } catch (xhr) {
                const errors = xhr?.responseJSON?.errors || null;
                $('.server-error').remove();
                $('.is-invalid').removeClass('is-invalid');

                if (errors) {
                    $.each(errors, function (field, messages) {
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
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: window.getAjaxErrorMessage(xhr, "Unable to save form data before preview.")
                    });
                }
                return null;
            }
        }
        window.saveCompetencyDraftSilently = saveCompetencyDraftSilently;

        function normalizeMirroredDynamicRows($container, rowSelector, fields) {
            if (!$container.length) return;
            const $rows = $container.find(rowSelector);
            const count = $rows.length;
            if (count < 2 || count % 2 !== 0) return;

            const signatures = $rows.map(function () {
                const $row = $(this);
                return fields.map(function (selector) {
                    const $el = $row.find(selector).first();
                    if (!$el.length) return '';
                    if ($el.is(':file')) {
                        const input = $el.get(0);
                        const hasFile = !!(input && input.files && input.files.length);
                        const existing = ($row.find('input[name="existing_document[]"], input[name="existing_work_document[]"]').first().val() || '').trim();
                        return hasFile ? `file:${input.files[0].name}` : `existing:${existing}`;
                    }
                    return ($el.val() || '').toString().trim();
                }).join('||');
            }).get();

            const half = count / 2;
            let mirrored = true;
            for (let i = 0; i < half; i++) {
                if (signatures[i] !== signatures[i + half]) {
                    mirrored = false;
                    break;
                }
            }
            if (!mirrored) return;

            for (let i = count - 1; i >= half; i--) {
                $rows.eq(i).remove();
            }
        }

        /**
         * Remove education rows that match on all visible answers but differ only on document state.
         * Mirrored-row detection fails when one duplicate still has the file input and the other does not.
         */
        function dedupeSemanticDuplicateEducationRows() {
            if (!$('#competency_form_ws').length) return;
            const $box = $('#education-container');
            if (!$box.length) return;

            function rowKey($r) {
                function v(sel) {
                    return ($r.find(sel).first().val() || '').toString().trim();
                }
                const level = v('select[name="educational_level[]"]');
                const month = v('select[name="month_of_passing[]"]');
                const year = v('select[name="year_of_passing[]"]');
                if (!level || !month || !year || year === '0') {
                    return null;
                }
                return [
                    level,
                    v('input[name="institute_name[]"]'),
                    month,
                    year,
                    v('input[name="certificate_no[]"]'),
                ].join('\u0001');
            }

            function rowMeta($r) {
                const fin = $r.find('input[type="file"][name="education_document[]"]').first().get(0);
                const hasNew = !!(fin && fin.files && fin.files.length);
                const fname = hasNew ? ((fin.files[0] && fin.files[0].name) || '') : '';
                const existing = ($r.find('input[name="existing_document[]"]').first().val() || '').toString().trim();
                let score = (hasNew ? 2 : 0) + (existing ? 1 : 0);
                return { score: score, fname: fname, existing: existing };
            }

            while (true) {
                const buckets = {};
                $box.find('.education-fields').each(function () {
                    const $row = $(this);
                    const k = rowKey($row);
                    if (!k) return;
                    if (!buckets[k]) buckets[k] = [];
                    const m = rowMeta($row);
                    buckets[k].push({ $row: $row, score: m.score, fname: m.fname });
                });

                let removedOne = false;
                $.each(buckets, function (k, list) {
                    if (!list || list.length < 2) return;
                    list.sort(function (a, b) { return b.score - a.score; });
                    // Two rows both with a newly chosen file but different names — keep both.
                    if (list.length >= 2 && list[0].score === 2 && list[1].score === 2) {
                        const a = (list[0].fname || '');
                        const b = (list[1].fname || '');
                        if (a && b && a !== b) return;
                    }
                    for (let x = 1; x < list.length; x++) {
                        const $r = list[x].$row;
                        $r.find('.local-file-preview').each(function () {
                            const u = $(this).data('blobUrl');
                            if (u) {
                                try { URL.revokeObjectURL(u); } catch (e) {}
                            }
                        });
                        $r.remove();
                        removedOne = true;
                    }
                });

                if (!removedOne) break;

                const $eduTable = $('#education-table');
                if ($eduTable.length) $eduTable.next('.education-error').remove();
                $box.find('.education-fields .edu-serial').each(function (idx) {
                    $(this).text(String(idx + 1));
                });
            }
        }

        function normalizeCompetencyDynamicSections() {
            normalizeMirroredDynamicRows(
                $('#education-container'),
                '.education-fields',
                [
                    'select[name="educational_level[]"]',
                    'input[name="institute_name[]"]',
                    'select[name="month_of_passing[]"]',
                    'select[name="year_of_passing[]"]',
                    'input[name="certificate_no[]"]',
                    'input[name="existing_document[]"]',
                    'input[name="education_document[]"]'
                ]
            );

            dedupeSemanticDuplicateEducationRows();

            normalizeMirroredDynamicRows(
                $('#work-container'),
                '.work-fields',
                [
                    '.work-employment-type',
                    '.work-contractor-cat',
                    '.work-licence-number',
                    '.work-employer-input',
                    '.work-org-address',
                    'input[name="designation[]"]',
                    '.work-nature',
                    '.work-voltage',
                    '.work-transformer-kva',
                    '.work-date-from',
                    '.work-date-to',
                    '.work-date-till-hidden',
                    '.work-experience-total-hidden',
                    'input[name="existing_work_document[]"]',
                    'input[name="work_document[]"]',
                    'input[name="work_relieving_letter[]"]'
                ]
            );
        }

        window.normalizeCompetencyDynamicSections = normalizeCompetencyDynamicSections;

        normalizeCompetencyDynamicSections();

        // ── S/W/WH preview modal: outer chrome (mirrors Form P preview) ────────
        // Injects shared styles + modal DOM once per page load; safe to call repeatedly.
        function ensureSwPreviewStyles() {
            if (document.getElementById('prv-sw-preview-modal-styles')) return;
            const style = document.createElement('style');
            style.id = 'prv-sw-preview-modal-styles';
            style.textContent = `
                /* ── Outer chrome (Form P parity) ────────────────────────── */
                .prv-sw-overlay {
                    position: fixed; inset: 0; z-index: 10050;
                    background: rgba(10, 24, 48, .58);
                    display: none; align-items: center; justify-content: center;
                    padding: 20px 16px;
                    backdrop-filter: blur(2px);
                }
                .prv-sw-overlay.is-open { display: flex; }
                @media (max-width: 767.98px) { .prv-sw-overlay { align-items: flex-end; padding: 0; } }
                .prv-sw-modal-root .prv-sw-panel {
                    background: #f0f4f9; width: 100%; max-width: 940px;
                    max-height: min(90vh, 920px); display: flex; flex-direction: column;
                    border-radius: 14px; overflow: hidden;
                    box-shadow: 0 18px 48px rgba(3, 90, 179, .22);
                    animation: prvSwIn .28s ease;
                }
                @media (max-width: 767.98px) {
                    .prv-sw-modal-root .prv-sw-panel { max-height: 92vh; border-radius: 16px 16px 0 0; animation: prvSwSlideUp .28s ease; }
                }
                @keyframes prvSwIn { from { opacity: 0; transform: scale(.97); } to { opacity: 1; transform: scale(1); } }
                @keyframes prvSwSlideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
                .prv-sw-modal-root .prv-sw-header {
                    background: linear-gradient(135deg, #035ab3 0%, #0472d9 100%);
                    padding: 16px 22px 14px; flex-shrink: 0;
                    display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;
                }
                .prv-sw-modal-root .prv-sw-header-main { min-width: 0; }
                .prv-sw-modal-root .prv-sw-title {
                    margin: 0; font-size: 1.05rem; font-weight: 700; color: #fff; line-height: 1.35;
                    display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
                }
                .prv-sw-modal-root .prv-sw-title .fa { opacity: .9; }
                .prv-sw-modal-root .prv-sw-badge {
                    display: inline-block; background: rgba(255,255,255,.16);
                    border: 1px solid rgba(255,255,255,.32); color: #fff;
                    border-radius: 999px; padding: 2px 11px; font-size: .72rem; font-weight: 600;
                }
                .prv-sw-modal-root .prv-sw-badge--renew { background: rgba(255, 193, 7, .22); border-color: rgba(255, 220, 100, .45); }
                .prv-sw-modal-root .prv-sw-subtitle { font-size: .78rem; color: rgba(255,255,255,.82); margin-top: 4px; line-height: 1.4; }
                .prv-sw-modal-root .prv-sw-close {
                    background: rgba(255,255,255,.14); border: none; color: #fff;
                    width: 34px; height: 34px; border-radius: 50%; font-size: 1.25rem;
                    line-height: 1; cursor: pointer; flex-shrink: 0; transition: background .2s;
                }
                .prv-sw-modal-root .prv-sw-close:hover { background: rgba(255,255,255,.28); }

                .prv-sw-modal-root .prv-sw-meta {
                    display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px;
                    padding: 14px 22px 0; flex-shrink: 0;
                }
                @media (max-width: 575.98px) { .prv-sw-modal-root .prv-sw-meta { grid-template-columns: 1fr; } }
                .prv-sw-modal-root .prv-sw-meta-card {
                    background: #fff; border: 1px solid #dde5f3; border-radius: 8px;
                    padding: 10px 12px; min-width: 0;
                }
                .prv-sw-modal-root .prv-sw-meta-label {
                    font-size: .68rem; font-weight: 600; color: #5a7299;
                    text-transform: uppercase; letter-spacing: .35px; margin-bottom: 2px;
                }
                .prv-sw-modal-root .prv-sw-meta-value {
                    font-size: .86rem; font-weight: 600; color: #1a2a4a;
                    word-break: break-word; line-height: 1.35;
                }

                .prv-sw-modal-root .prv-sw-body { overflow-y: auto; padding: 14px 22px 18px; flex: 1; }
                .prv-sw-modal-root .prv-sw-section {
                    background: #fff; border: 1px solid #e3e8f0; border-radius: 10px;
                    margin-bottom: 12px; overflow: hidden;
                }
                .prv-sw-modal-root .prv-sw-section-hd {
                    background: #eef3fb; border-bottom: 1px solid #dde5f3;
                    padding: 9px 14px; display: flex; align-items: flex-start; gap: 10px;
                }
                .prv-sw-modal-root .prv-sw-section-num {
                    width: 24px; height: 24px; border-radius: 50%; background: #035ab3; color: #fff;
                    font-size: .72rem; font-weight: 700; display: inline-flex; align-items: center;
                    justify-content: center; flex-shrink: 0; margin-top: 1px;
                }
                .prv-sw-modal-root .prv-sw-section-num.prv-sw-section-num--sub {
                    width: auto; min-width: 28px; height: 24px; padding: 0 7px; border-radius: 8px;
                    font-size: .66rem; letter-spacing: .02em;
                }
                .prv-sw-modal-root .prv-sw-section-title { font-size: .84rem; font-weight: 600; color: #1a2a4a; line-height: 1.35; }
                .prv-sw-modal-root .prv-sw-section-tamil { font-size: .74rem; color: #5a7299; margin-top: 2px; line-height: 1.35; }
                .prv-sw-modal-root .prv-sw-section-body { padding: 14px; }
                .prv-sw-modal-root .prv-sw-question-part + .prv-sw-question-part {
                    margin-top: 14px;
                    padding-top: 14px;
                    border-top: 1px dashed #d5deed;
                }
                .prv-sw-modal-root .prv-sw-question-part .prv-sw-section-hd { margin-bottom: 8px; }
                .prv-sw-modal-root .prv-sw-question-part .prv-sw-section-body { padding-top: 0; }

                .prv-sw-modal-root .prv-sw-field { margin-bottom: 10px; }
                .prv-sw-modal-root .prv-sw-field:last-child { margin-bottom: 0; }
                .prv-sw-modal-root .prv-sw-label {
                    font-size: .7rem; font-weight: 600; color: #5a7299;
                    text-transform: uppercase; letter-spacing: .35px; margin-bottom: 3px;
                }
                .prv-sw-modal-root .prv-sw-value {
                    font-size: .88rem; color: #1a2a4a; font-weight: 500;
                    padding: 7px 10px; background: #f8fafd; border: 1px solid #e3e8f0;
                    border-radius: 6px; min-height: 34px; word-break: break-word;
                }
                .prv-sw-modal-root .prv-sw-value.prv-sw-empty { color: #9aa8bf; font-style: italic; font-weight: 400; }

                /* Personal & contact — photo + signature column + 2-col details grid */
                .prv-sw-modal-root .prv-sw-personal-layout {
                    display: grid;
                    grid-template-columns: minmax(120px, 148px) minmax(0, 1fr);
                    gap: 16px;
                    align-items: start;
                }
                @media (max-width: 575.98px) {
                    .prv-sw-modal-root .prv-sw-personal-layout { grid-template-columns: 1fr; }
                }
                .prv-sw-modal-root .prv-sw-media-col {
                    display: flex; flex-direction: column; gap: 12px;
                    padding: 10px; background: #f8fafd; border: 1px solid #e3e8f0; border-radius: 8px;
                }
                .prv-sw-modal-root .prv-sw-media-label {
                    font-size: .66rem; font-weight: 700; color: #5a7299;
                    text-transform: uppercase; letter-spacing: .35px; margin-bottom: 4px; text-align: center;
                }
                .prv-sw-modal-root .prv-sw-media-col .prv-sw-thumb { width: 100%; }
                .prv-sw-modal-root .prv-sw-media-col .prv-sw-thumb img,
                .prv-sw-modal-root .prv-sw-media-col .prv-sw-no-img { width: 100% !important; max-width: 120px; margin: 0 auto; }
                .prv-sw-modal-root .prv-sw-media-col .prv-sw-thumb--photo img,
                .prv-sw-modal-root .prv-sw-media-col .prv-sw-thumb--photo .prv-sw-no-img { height: 120px !important; }
                .prv-sw-modal-root .prv-sw-media-col .prv-sw-thumb--sign img,
                .prv-sw-modal-root .prv-sw-media-col .prv-sw-thumb--sign .prv-sw-no-img { height: 52px !important; max-width: 128px !important; }
                .prv-sw-modal-root .prv-sw-media-col .prv-sw-thumb span { display: none; }

                .prv-sw-modal-root .prv-sw-details-grid {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 10px 14px;
                    min-width: 0;
                }
                @media (max-width: 767.98px) {
                    .prv-sw-modal-root .prv-sw-details-grid { grid-template-columns: 1fr; }
                }
                .prv-sw-modal-root .prv-sw-detail-item { min-width: 0; }
                .prv-sw-modal-root .prv-sw-detail-item--full { grid-column: 1 / -1; }
                .prv-sw-modal-root .prv-sw-detail-item .prv-sw-field { margin-bottom: 0; }

                .prv-sw-modal-root .prv-sw-thumb { text-align: center; flex-shrink: 0; }
                .prv-sw-modal-root .prv-sw-thumb img {
                    display: block; border: 2px solid #dde5f3; border-radius: 8px; background: #f0f4f9;
                }
                .prv-sw-modal-root .prv-sw-thumb--photo img { width: 88px; height: 106px; object-fit: cover; }
                .prv-sw-modal-root .prv-sw-thumb--sign img { width: 150px; height: 56px; object-fit: contain; }
                .prv-sw-modal-root .prv-sw-no-img {
                    background: #f0f4f9; border: 2px dashed #ccd5e3; border-radius: 8px;
                    display: flex; align-items: center; justify-content: center;
                    color: #9aa8bf; font-size: .68rem; text-align: center; padding: 6px;
                }
                .prv-sw-modal-root .prv-sw-thumb span { font-size: .68rem; color: #5a7299; margin-top: 4px; display: block; }

                .prv-sw-modal-root .prv-sw-table-wrap { overflow-x: auto; border: 1px solid #e3e8f0; border-radius: 8px; margin-bottom: 12px; }
                .prv-sw-modal-root .prv-sw-section-hint { font-weight: 500; font-size: .78rem; color: #5a7299; }
                .prv-sw-modal-root .prv-sw-work-table { font-size: .72rem; min-width: 880px; }
                .prv-sw-modal-root .prv-sw-work-table th { white-space: nowrap; font-size: .68rem; padding: 6px 8px; }
                .prv-sw-modal-root .prv-sw-work-table td { padding: 6px 8px; vertical-align: top; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-summary-th-sno,
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .work-row-summary-sno { width: 3rem; text-align: center; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-summary-th-org,
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .work-row-summary-org-address { min-width: 140px; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-th-org-line { display: block; line-height: 1.2; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-sum-main { display: block; font-weight: 600; color: #212121; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-sum-sub { display: block; font-size: .68rem; color: #5a7299; margin-top: 2px; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .work-row-summary-period { min-width: 168px; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-period-box { display: flex; flex-direction: column; gap: 6px; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-period-dates { display: flex; flex-wrap: wrap; gap: 6px; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-period-mini {
                    flex: 1 1 72px; min-width: 72px; background: #f4f7fc; border: 1px solid #dde5f3;
                    border-radius: 6px; padding: 4px 6px; text-align: center;
                }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-period-label { display: block; font-size: .62rem; color: #5a7299; font-weight: 600; text-transform: uppercase; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-period-val { display: block; font-size: .7rem; font-weight: 600; color: #1a2a4a; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-period-duration { display: flex; gap: 4px; justify-content: center; flex-wrap: wrap; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-period-dur-cell {
                    flex: 1 1 52px; min-width: 48px; background: #e8f5e9; border: 1px solid #c8e6c9; border-radius: 6px; padding: 4px 4px; text-align: center;
                }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-period-dur-num { display: block; font-size: .85rem; font-weight: 700; color: #2e7d32; line-height: 1.1; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-period-dur-lbl { display: block; font-size: .58rem; color: #5a7299; text-transform: uppercase; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-sum-attach-stack { display: flex; flex-direction: column; gap: 6px; text-align: left; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-sum-attach-block { font-size: .68rem; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-sum-attach-label { font-weight: 600; color: #5a7299; }
                .prv-sw-modal-root .prv-sw-work-table.wx-summary-table .wx-sum-attach-value { color: #2c3e5e; }
                .prv-sw-modal-root .prv-sw-badge-till {
                    display: inline-block; background: #e8f4fd; color: #035ab3;
                    border: 1px solid #b8d4f0; border-radius: 4px; padding: 1px 6px; font-size: .68rem; font-weight: 600;
                }
                .prv-sw-modal-root .prv-sw-table { width: 100%; font-size: .76rem; border-collapse: collapse; margin: 0; min-width: 520px; }
                .prv-sw-modal-root .prv-sw-table th {
                    background: #eef3fb; color: #1a2a4a; font-weight: 600;
                    padding: .4rem .45rem; border: 1px solid #dde5f3; font-size: .7rem;
                    white-space: nowrap; text-align: center; vertical-align: middle;
                }
                .prv-sw-modal-root .prv-sw-table td {
                    padding: .4rem .45rem; border: 1px solid #e8edf6; vertical-align: middle;
                    color: #2c3e5e; text-align: center;
                }
                .prv-sw-modal-root .prv-sw-table td.prv-sw-td-left { text-align: left; white-space: pre-line; }
                .prv-sw-modal-root .prv-sw-table tr:nth-child(even) td { background: #f8fafd; }

                .prv-sw-modal-root .prv-sw-doc-pill {
                    display: inline-flex; align-items: center; gap: 4px;
                    background: #e8f2ff; color: #035ab3; border-radius: 999px;
                    padding: 3px 10px; font-size: .72rem; font-weight: 600; text-decoration: none;
                    max-width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
                }
                .prv-sw-modal-root .prv-sw-doc-pill:hover { background: #d6e8ff; text-decoration: none; color: #024a98; }
                .prv-sw-modal-root .prv-sw-doc-empty { color: #9aa8bf; font-size: .75rem; }

                .prv-sw-modal-root .prv-sw-yesno-yes {
                    background: #d4edda; color: #155724; border-radius: 4px;
                    padding: 2px 9px; font-size: .72rem; font-weight: 600;
                }
                .prv-sw-modal-root .prv-sw-yesno-no {
                    background: #f8d7da; color: #721c24; border-radius: 4px;
                    padding: 2px 9px; font-size: .72rem; font-weight: 600;
                }

                .prv-sw-modal-root .prv-sw-footer {
                    background: #fff; border-top: 1px solid #e3e8f0; padding: 14px 22px;
                    display: flex; align-items: center; justify-content: center; gap: 10px;
                    flex-shrink: 0; flex-wrap: wrap;
                }
                .prv-sw-modal-root .prv-sw-btn-back {
                    background: #fff; color: #035ab3; border: 1px solid #035ab3; border-radius: 8px;
                    padding: 8px 18px; font-size: .84rem; font-weight: 600; cursor: pointer; white-space: nowrap;
                }
                .prv-sw-modal-root .prv-sw-btn-back:hover { background: #eef3fb; }
                .prv-sw-modal-root .prv-sw-btn-print {
                    background: #fff; color: #4f5f79; border: 1px solid #99a7c0; border-radius: 8px;
                    padding: 8px 18px; font-size: .84rem; font-weight: 600; cursor: pointer; white-space: nowrap;
                }
                .prv-sw-modal-root .prv-sw-btn-print:hover { background: #f3f6fb; }
                .prv-sw-modal-root .prv-sw-btn-go {
                    background: linear-gradient(135deg, #1a9e4f, #14813f); color: #fff; border: none;
                    border-radius: 8px; padding: 8px 20px; font-size: .84rem; font-weight: 600;
                    cursor: pointer; white-space: nowrap;
                }
                .prv-sw-modal-root .prv-sw-btn-go:disabled { opacity: .45; cursor: not-allowed; }
                .prv-sw-modal-root .prv-sw-btn-go:not(:disabled):hover { opacity: .92; }
                .prv-sw-modal-root .prv-sw-print-head { display: none; }

                /* ── Print (isolated iframe activates via html.prv-sw-print-active) ── */
                @page { size: A4 portrait; margin: 8mm 10mm; }
                @media print {
                    html, body { height: auto !important; overflow: visible !important; background: #fff !important; margin: 0 !important; padding: 0 !important; }
                    html.prv-sw-print-active #prvSwPrintRoot { display: block !important; position: static !important; width: 100% !important; max-width: none !important; height: auto !important; overflow: visible !important; background: #fff !important; padding: 0 !important; margin: 0 !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-panel { display: block !important; max-height: none !important; width: 100% !important; box-shadow: none !important; border-radius: 0 !important; background: #fff !important; overflow: visible !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-header,
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-footer,
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-close,
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-subtitle { display: none !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-print-head { display: block !important; text-align: center; padding: 0 0 6px; margin-bottom: 6px; border-bottom: 2px solid #1f3a63; page-break-after: avoid; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-print-head-org { font-size: 8.5pt; font-weight: 700; text-transform: uppercase; color: #444; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-print-head-title { font-size: 12pt; font-weight: 800; color: #1f3a63; margin-top: 2px; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-print-head-tag { font-size: 7.5pt; color: #666; margin-top: 2px; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-meta { display: grid !important; grid-template-columns: repeat(3, 1fr) !important; gap: 6px !important; padding: 0 0 6px !important; page-break-after: avoid; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-meta-card { border: 1px solid #bbb !important; padding: 4px 6px !important; background: #fff !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-meta-label { font-size: 6.5pt !important; margin-bottom: 0 !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-meta-value { font-size: 8.5pt !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-body { overflow: visible !important; padding: 0 !important; max-height: none !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-section { border: 1px solid #aaa !important; box-shadow: none !important; page-break-inside: auto !important; margin-bottom: 5px !important; border-radius: 0 !important; background: #fff !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-section-hd { background: #eee !important; padding: 4px 8px !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-section-tamil { display: none !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-section-title { font-size: 9pt !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-section-num { width: 18px !important; height: 18px !important; font-size: 8pt !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-section-body { padding: 6px 8px !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-personal-layout { display: grid !important; grid-template-columns: 88px minmax(0, 1fr) !important; gap: 8px !important; align-items: start !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-media-col { display: flex !important; flex-direction: column !important; gap: 6px !important; padding: 4px !important; background: transparent !important; border: 1px solid #bbb !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-media-label { font-size: 6pt !important; margin-bottom: 2px !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-media-col .prv-sw-thumb--photo img,
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-media-col .prv-sw-thumb--photo .prv-sw-no-img { width: 72px !important; height: 86px !important; max-width: 72px !important; margin: 0 auto !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-media-col .prv-sw-thumb--sign img,
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-media-col .prv-sw-thumb--sign .prv-sw-no-img { width: 72px !important; height: 34px !important; max-width: 72px !important; margin: 0 auto !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-details-grid { display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)) !important; gap: 5px 8px !important; width: 100% !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-detail-item--full { grid-column: 1 / -1 !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-label { font-size: 6.5pt !important; margin-bottom: 1px !important; color: #333 !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-value { font-size: 8.5pt !important; padding: 3px 5px !important; min-height: 0 !important; line-height: 1.25 !important; background: transparent !important; border: 1px solid #bbb !important; color: #111 !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-table-wrap { overflow: visible !important; margin-bottom: 6px !important; page-break-inside: auto; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-table { font-size: 7.5pt !important; min-width: 0 !important; width: 100% !important; table-layout: fixed !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-table th,
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-table td { border: 1px solid #bbb !important; background: transparent !important; padding: 2px 3px !important; color: #111 !important; word-break: break-word !important; white-space: normal !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-doc-pill { background: transparent !important; border: 0 !important; padding: 0 !important; color: #111 !important; max-width: none !important; white-space: normal !important; }
                    html.prv-sw-print-active .prv-sw-modal-root img { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-section--identity .row { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 5px 8px !important; margin: 0 !important; }
                    html.prv-sw-print-active .prv-sw-modal-root .prv-sw-section--identity .col-12 { width: auto !important; max-width: none !important; flex: none !important; padding: 0 !important; }
                }
            `;
            document.head.appendChild(style);
        }

        function ensureSwPreviewModalDom() {
            if (document.getElementById('appPreviewModalSw')) return;
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <div id="appPreviewModalSw" class="prv-sw-overlay prv-sw-modal-root" role="dialog" aria-modal="true" aria-labelledby="prvSwTitle" aria-hidden="true">
                    <div class="prv-sw-panel">
                        <div class="prv-sw-header">
                            <div class="prv-sw-header-main">
                                <h2 class="prv-sw-title" id="prvSwTitle">
                                    <i class="fa fa-file-text-o"></i>
                                    Application Preview
                                    <span class="prv-sw-badge" id="prvSwFormBadge">FORM</span>
                                    <span class="prv-sw-badge prv-sw-badge--renew" id="prvSwRenewBadge" style="display:none;">Renewal</span>
                                </h2>
                                <div class="prv-sw-subtitle">Review every section carefully before proceeding to payment. Use <strong>Back to Edit</strong> if anything needs correction.</div>
                            </div>
                            <button type="button" class="prv-sw-close" id="prvSwCloseBtn" title="Close preview" aria-label="Close preview">&times;</button>
                        </div>

                        <div class="prv-sw-print-head" aria-hidden="true">
                            <div class="prv-sw-print-head-org">Tamil Nadu Electrical Licencing Board</div>
                            <div class="prv-sw-print-head-title" id="prvSwPrintTitle">Competency Certificate</div>
                            <div class="prv-sw-print-head-tag" id="prvSwPrintTag">Application Preview</div>
                        </div>

                        <div class="prv-sw-meta">
                            <div class="prv-sw-meta-card">
                                <div class="prv-sw-meta-label">Applicant</div>
                                <div class="prv-sw-meta-value" id="prvSwMetaName">&mdash;</div>
                            </div>
                            <div class="prv-sw-meta-card">
                                <div class="prv-sw-meta-label">Application ID</div>
                                <div class="prv-sw-meta-value" id="prvSwMetaAppId">&mdash;</div>
                            </div>
                            <div class="prv-sw-meta-card">
                                <div class="prv-sw-meta-label">Licence / Certificate</div>
                                <div class="prv-sw-meta-value" id="prvSwMetaLicence">&mdash;</div>
                            </div>
                        </div>

                        <div class="prv-sw-body" id="prvSwBody">
                            <!-- Section: Personal & Contact Details -->
                            <div class="prv-sw-section" id="prvSwSecPersonal">
                                <div class="prv-sw-section-hd">
                                    <span class="prv-sw-section-num" data-section-num="personal">1</span>
                                    <div>
                                        <div class="prv-sw-section-title">Personal &amp; Contact Details</div>
                                        <div class="prv-sw-section-tamil">விண்ணப்பதாரர் தனிப்பட்ட மற்றும் தொடர்பு விவரங்கள்</div>
                                    </div>
                                </div>
                                <div class="prv-sw-section-body">
                                    <div class="prv-sw-personal-layout">
                                        <div class="prv-sw-media-col">
                                            <div>
                                                <div class="prv-sw-thumb prv-sw-thumb--photo">
                                                    <div id="prvSwPhotoWrap"><div class="prv-sw-no-img" style="width:100%;height:120px;">No Photo</div></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="prv-sw-media-label">Signature</div>
                                                <div class="prv-sw-thumb prv-sw-thumb--sign">
                                                    <div id="prvSwSignWrap"><div class="prv-sw-no-img" style="width:100%;height:52px;">No Signature</div></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="prv-sw-details-grid">
                                            <div class="prv-sw-detail-item">
                                                <div class="prv-sw-field"><div class="prv-sw-label">Applicant's Name</div><div class="prv-sw-value" id="prvSwName">&mdash;</div></div>
                                            </div>
                                            <div class="prv-sw-detail-item">
                                                <div class="prv-sw-field"><div class="prv-sw-label">Father's Name</div><div class="prv-sw-value" id="prvSwFather">&mdash;</div></div>
                                            </div>
                                            <div class="prv-sw-detail-item">
                                                <div class="prv-sw-field"><div class="prv-sw-label">Email ID</div><div class="prv-sw-value" id="prvSwEmail">&mdash;</div></div>
                                            </div>
                                            <div class="prv-sw-detail-item">
                                                <div class="prv-sw-field"><div class="prv-sw-label">Date of Birth</div><div class="prv-sw-value" id="prvSwDob">&mdash;</div></div>
                                            </div>
                                            <div class="prv-sw-detail-item">
                                                <div class="prv-sw-field"><div class="prv-sw-label">Age</div><div class="prv-sw-value" id="prvSwAge">&mdash;</div></div>
                                            </div>
                                            <div class="prv-sw-detail-item">
                                                <div class="prv-sw-field"><div class="prv-sw-label">Address</div><div class="prv-sw-value" id="prvSwAddress" style="white-space:pre-line;">&mdash;</div></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Education -->
                            <div class="prv-sw-section" id="prvSwSecEdu">
                                <div class="prv-sw-section-hd">
                                    <span class="prv-sw-section-num" data-section-num="edu">6</span>
                                    <div>
                                        <div class="prv-sw-section-title" id="prvSwSecEduTitle">Educational / Technical Qualification</div>
                                        <div class="prv-sw-section-tamil">விண்ணப்பதாரரின் கல்வி தகுதி மற்றும் தேர்ச்சி விவரங்கள்</div>
                                    </div>
                                </div>
                                <div class="prv-sw-section-body">
                                    <div class="prv-sw-table-wrap">
                                        <table class="prv-sw-table">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Education Level</th>
                                                    <th>University / Institute</th>
                                                    <th>Month</th>
                                                    <th>Year</th>
                                                    <th>Certificate No.</th>
                                                    <th>Document</th>
                                                </tr>
                                            </thead>
                                            <tbody id="prvSwEduBody"><tr><td colspan="7" class="text-muted py-3">&mdash;</td></tr></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Work Experience (S/W only) -->
                            <div class="prv-sw-section" id="prvSwSecWork">
                                <div id="prvSwWorkFormS">
                                    <div class="prv-sw-question-part" id="prvSwSecWork7a">
                                        <div class="prv-sw-section-hd">
                                            <span class="prv-sw-section-num prv-sw-section-num--sub" data-section-num="7a">7a</span>
                                            <div>
                                                <div class="prv-sw-section-title">Previous Work Experience</div>
                                                <div class="prv-sw-section-tamil">முந்தைய பணி அனுபவ விவரங்கள்</div>
                                            </div>
                                        </div>
                                        <div class="prv-sw-section-body">
                                            <div class="prv-sw-table-wrap">
                                                <table class="prv-sw-table prv-sw-work-table wx-summary-table" id="prvSwWorkTable7a">
                                                    <thead id="prvSwWorkThead7a"></thead>
                                                    <tbody id="prvSwWorkBody7a"><tr><td colspan="9" class="text-muted py-3">&mdash;</td></tr></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="prv-sw-question-part" id="prvSwSecWork7b">
                                        <div class="prv-sw-section-hd">
                                            <span class="prv-sw-section-num prv-sw-section-num--sub" data-section-num="7b">7b</span>
                                            <div>
                                                <div class="prv-sw-section-title">Are you a Board member of TNELB or Ex board member of TNELB?</div>
                                                <div class="prv-sw-section-tamil">தமிழ்நாடு மின்சார வாரிய கோப்புறை / முன்னாள் கோப்புறை உறுப்பினரா?</div>
                                            </div>
                                        </div>
                                        <div class="prv-sw-section-body">
                                            <div class="mb-2" id="prvSwWork7bYn">&mdash;</div>
                                            <div id="prvSwWork7bBlock" style="display:none;">
                                                <div class="row g-2">
                                                    <div class="col-12 col-sm-6">
                                                        <div class="prv-sw-field mb-0">
                                                            <div class="prv-sw-label">Organisation</div>
                                                            <div class="prv-sw-value" id="prvSwWork7bOrg">&mdash;</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <div class="prv-sw-field mb-0">
                                                            <div class="prv-sw-label">Designation</div>
                                                            <div class="prv-sw-value" id="prvSwWork7bDesig">&mdash;</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="prv-sw-field mb-0">
                                                            <div class="prv-sw-label">Address</div>
                                                            <div class="prv-sw-value" id="prvSwWork7bAddr" style="white-space:pre-line;">&mdash;</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-4">
                                                        <div class="prv-sw-field mb-0">
                                                            <div class="prv-sw-label">Date of Meeting</div>
                                                            <div class="prv-sw-value" id="prvSwWork7bMeetingDate">&mdash;</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-8">
                                                        <div class="prv-sw-field mb-0">
                                                            <div class="prv-sw-label">Details of the meeting</div>
                                                            <div class="prv-sw-value" id="prvSwWork7bMeetingDetails" style="white-space:pre-line;">&mdash;</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-4">
                                                        <div class="prv-sw-field mb-0">
                                                            <div class="prv-sw-label">From date</div>
                                                            <div class="prv-sw-value" id="prvSwWork7bFrom">&mdash;</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-4">
                                                        <div class="prv-sw-field mb-0">
                                                            <div class="prv-sw-label">To date</div>
                                                            <div class="prv-sw-value" id="prvSwWork7bTo">&mdash;</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-4">
                                                        <div class="prv-sw-field mb-0">
                                                            <div class="prv-sw-label">Duration</div>
                                                            <div class="prv-sw-value" id="prvSwWork7bDuration">&mdash;</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <div class="prv-sw-field mb-0">
                                                            <div class="prv-sw-label">Supporting docs</div>
                                                            <div class="prv-sw-value" id="prvSwWork7bSupportDoc">&mdash;</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                        <div class="prv-sw-field mb-0">
                                                            <div class="prv-sw-label">Relieving Letter</div>
                                                            <div class="prv-sw-value" id="prvSwWork7bRelieveDoc">&mdash;</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="prvSwWorkFormOther" style="display:none;">
                                    <div class="prv-sw-section-hd">
                                        <span class="prv-sw-section-num" data-section-num="work">7</span>
                                        <div>
                                            <div class="prv-sw-section-title" id="prvSwWorkTitle">Details of Previous and Current Work experiences <span class="prv-sw-section-hint">(Upload the documents)</span></div>
                                            <div class="prv-sw-section-tamil" id="prvSwWorkTamil">பெற்றுள்ள முந்தைய மற்றும் தற்போதைய அனுபவங்களின் விவரங்கள் (ஆவணங்களை பதிவேற்ற வேண்டும்)</div>
                                        </div>
                                    </div>
                                    <div class="prv-sw-section-body">
                                        <div class="prv-sw-table-wrap">
                                            <table class="prv-sw-table prv-sw-work-table" id="prvSwWorkTable">
                                                <thead id="prvSwWorkThead">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Employment Type</th>
                                                        <th>Employer / Organisation</th>
                                                        <th>From</th>
                                                        <th>To</th>
                                                        <th>Duration</th>
                                                        <th>Designation</th>
                                                        <th>Document</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="prvSwWorkBody"><tr><td colspan="8" class="text-muted py-3">&mdash;</td></tr></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Previous Same-Type Certificate -->
                            <div class="prv-sw-section" id="prvSwSecPrev">
                                <div class="prv-sw-section-hd">
                                    <span class="prv-sw-section-num prv-sw-section-num--sub" data-section-num="prev">8</span>
                                    <div>
                                        <div class="prv-sw-section-title" id="prvSwSecPrevTitle">Do you already possess a Supervisor Competency Certificate issued by this Board? If yes, please furnish the details.</div>
                                        <div class="prv-sw-section-tamil" id="prvSwSecPrevTamil">இந்த வாரியத்தால் வழங்கப்பட்ட மேற்பார்வையாளர் தகுதி சான்றிதழ் உங்களிடம் உள்ளதா? ஆம் என்றால் அதன் குறிப்பு எண் மற்றும் தேதியை குறிப்பிடுக</div>
                                    </div>
                                </div>
                                <div class="prv-sw-section-body">
                                    <div class="mb-2" id="prvSwPrevYn">&mdash;</div>
                                    <div id="prvSwPrevBlock" style="display:none;">
                                        <div class="row g-2">
                                            <div class="col-12 col-sm-3"><div class="prv-sw-field mb-0"><div class="prv-sw-label">Certificate Number</div><div class="prv-sw-value" id="prvSwPrevNo">&mdash;</div></div></div>
                                            <div class="col-12 col-sm-3"><div class="prv-sw-field mb-0"><div class="prv-sw-label">Date of First Issue</div><div class="prv-sw-value" id="prvSwPrevIssueDate">&mdash;</div></div></div>
                                            <div class="col-12 col-sm-3"><div class="prv-sw-field mb-0"><div class="prv-sw-label">From date</div><div class="prv-sw-value" id="prvSwPrevFromDate">&mdash;</div></div></div>
                                            <div class="col-12 col-sm-3"><div class="prv-sw-field mb-0"><div class="prv-sw-label">To date</div><div class="prv-sw-value" id="prvSwPrevExpiryDate">&mdash;</div></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Wireman Competency Certificate (Form S only) -->
                            <div class="prv-sw-section" id="prvSwSecWiremanCert">
                                <div class="prv-sw-section-hd">
                                    <span class="prv-sw-section-num prv-sw-section-num--sub" data-section-num="wireman">9</span>
                                    <div>
                                        <div class="prv-sw-section-title" id="prvSwSecWcTitle">Do you also possess Wireman Competency Certificate issued by this Board? If so furnish the details.</div>
                                        <div class="prv-sw-section-tamil" id="prvSwSecWcTamil">இந்த வாரியம் வழங்கிய கம்பி இணைப்பாளர் திறன் சான்றிதழ் உள்ளதா? இருந்தால், அதன் விவரங்களை வழங்கவும்.</div>
                                    </div>
                                </div>
                                <div class="prv-sw-section-body">
                                    <div class="mb-2" id="prvSwWcYn">&mdash;</div>
                                    <div id="prvSwWcBlock" style="display:none;">
                                        <div class="row g-2">
                                            <div class="col-12 col-sm-3"><div class="prv-sw-field mb-0"><div class="prv-sw-label">Certificate Number</div><div class="prv-sw-value" id="prvSwWcNo">&mdash;</div></div></div>
                                            <div class="col-12 col-sm-3"><div class="prv-sw-field mb-0"><div class="prv-sw-label">Date of First Issue</div><div class="prv-sw-value" id="prvSwWcIssueDate">&mdash;</div></div></div>
                                            <div class="col-12 col-sm-3"><div class="prv-sw-field mb-0"><div class="prv-sw-label">From date</div><div class="prv-sw-value" id="prvSwWcFromDate">&mdash;</div></div></div>
                                            <div class="col-12 col-sm-3"><div class="prv-sw-field mb-0"><div class="prv-sw-label">To date</div><div class="prv-sw-value" id="prvSwWcExpiryDate">&mdash;</div></div></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Identity Documents (Aadhaar + PAN) -->
                            <div class="prv-sw-section prv-sw-section--identity" id="prvSwSecDocs" data-section="identity">
                                <div class="prv-sw-section-hd">
                                    <span class="prv-sw-section-num" data-section-num="docs">8</span>
                                    <div>
                                        <div class="prv-sw-section-title">Identity Documents</div>
                                        <div class="prv-sw-section-tamil">அடையாள ஆவண விவரங்கள்</div>
                                    </div>
                                </div>
                                <div class="prv-sw-section-body">
                                    <div class="row g-2">
                                        <div class="col-12 col-sm-5"><div class="prv-sw-field mb-0"><div class="prv-sw-label">Aadhaar Number</div><div class="prv-sw-value" id="prvSwAadhaar">&mdash;</div></div></div>
                                        <div class="col-12 col-sm-7"><div class="prv-sw-field mb-0"><div class="prv-sw-label">Aadhaar Document</div><div class="prv-sw-value" id="prvSwAadhaarDoc">&mdash;</div></div></div>
                                        <div class="col-12 col-sm-5"><div class="prv-sw-field mb-0"><div class="prv-sw-label">PAN Number</div><div class="prv-sw-value" id="prvSwPan">&mdash;</div></div></div>
                                        <div class="col-12 col-sm-7"><div class="prv-sw-field mb-0"><div class="prv-sw-label">PAN Document</div><div class="prv-sw-value" id="prvSwPanDoc">&mdash;</div></div></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="prv-sw-footer">
                            <button type="button" class="prv-sw-btn-back" id="prvSwBackBtn"><i class="fa fa-arrow-left"></i> Back to Edit</button>
                            <button type="button" class="prv-sw-btn-print" id="prvSwPrintBtn" title="Print preview"><i class="fa fa-print"></i> Print</button>
                            <button type="button" class="prv-sw-btn-go" id="prvSwConfirmBtn"><i class="fa fa-check"></i> Confirm &amp; Proceed</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(wrapper.firstElementChild);
        }

        function printSwPreview() {
            const swModal = document.getElementById('appPreviewModalSw');
            if (!swModal) return;
            const swPanel = swModal.querySelector('.prv-sw-panel');
            if (!swPanel) return;

            const old = document.getElementById('prvSwPrintFrame');
            if (old) old.remove();

            const iframe = document.createElement('iframe');
            iframe.id = 'prvSwPrintFrame';
            iframe.setAttribute('aria-hidden', 'true');
            iframe.style.cssText = 'position:fixed;left:0;top:0;width:0;height:0;border:0;visibility:hidden;';
            document.body.appendChild(iframe);

            const styleEl = document.getElementById('prv-sw-preview-modal-styles');
            const styles = styleEl ? styleEl.textContent : '';

            // Inline force-show the print-head in case iframe @media print rules don't activate before print()
            let panelHtml = swPanel.innerHTML;
            const headMatch = panelHtml.match(/<div class="prv-sw-print-head"[^>]*>/);
            if (headMatch) {
                panelHtml = panelHtml.replace(headMatch[0], headMatch[0].replace('>', ' style="display:block !important;">'));
            }

            // Bring page-level stylesheets into the iframe so Bootstrap/FontAwesome render in print
            const linkSheets = Array.prototype.slice.call(document.querySelectorAll('link[rel="stylesheet"]'))
                .map(function (l) { return '<link rel="stylesheet" href="' + l.getAttribute('href') + '">'; })
                .join('');

            const printDoc = iframe.contentWindow.document;
            printDoc.open();
            printDoc.write('<!DOCTYPE html><html class="prv-sw-print-active" lang="en"><head><meta charset="utf-8">');
            printDoc.write('<title>Application Preview</title>');
            printDoc.write(linkSheets);
            printDoc.write('<style>' + styles + '</style>');
            printDoc.write('</head><body>');
            printDoc.write('<div id="prvSwPrintRoot" class="prv-sw-modal-root">');
            printDoc.write('<div class="prv-sw-panel">');
            printDoc.write(panelHtml);
            printDoc.write('</div></div></body></html>');
            printDoc.close();

            const runPrint = function () {
                try {
                    iframe.contentWindow.focus();
                    iframe.contentWindow.print();
                } catch (err) {
                    console.error('S/W/WH preview print failed:', err);
                }
                window.setTimeout(function () { iframe.remove(); }, 1000);
            };
            window.setTimeout(runPrint, 300);
        }

        // ── Read form values and populate the S/W/WH preview modal ─────────────
        // Mirrors the Form P preview's populate pattern (read by element ID/name, set
        // text/HTML on placeholder targets). Visibility / section numbers / labels are
        // computed from the form_name hidden field so a single modal serves S, W and WH.
        function populateSwPreview() {
            const v = function (id) {
                const el = document.getElementById(id);
                return el ? String(el.value || '').trim() : '';
            };
            const valByName = function (name) {
                const el = document.querySelector('[name="' + name + '"]');
                return el ? String(el.value || '').trim() : '';
            };
            const esc = function (s) {
                return String(s == null ? '' : s)
                    .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
            };
            const fmtDate = function (s) {
                if (!s) return '';
                const m = String(s).match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (m) return m[3] + '-' + m[2] + '-' + m[1];
                return s;
            };
            const setField = function (id, txt) {
                const el = document.getElementById(id);
                if (!el) return;
                const t = (txt || '').toString().trim();
                el.textContent = t || '—';
                el.classList.toggle('prv-sw-empty', !t);
            };
            const setFieldHtml = function (id, html) {
                const el = document.getElementById(id);
                if (!el) return;
                const h = (html || '').toString().trim();
                if (!h || h === '—') {
                    el.textContent = '—';
                    el.classList.add('prv-sw-empty');
                    return;
                }
                el.innerHTML = h;
                el.classList.remove('prv-sw-empty');
            };
            const setNum = function (key, num) {
                const el = document.querySelector('#appPreviewModalSw [data-section-num="' + key + '"]');
                if (!el) return;
                el.textContent = num;
                el.classList.toggle('prv-sw-section-num--sub', /[a-z]$/i.test(String(num)));
            };
            const setSecVisible = function (id, visible) {
                const el = document.getElementById(id);
                if (el) el.style.display = visible ? '' : 'none';
            };
            const resolveAssetUrl = function (path) {
                if (!path) return '';
                path = String(path).trim();
                if (!path) return '';
                if (/^https?:\/\//i.test(path)) return path;
                var docPrefix = (typeof DOCUMENT_PUBLIC_URL_PREFIX !== 'undefined' ? DOCUMENT_PUBLIC_URL_PREFIX : 'competency').replace(/^\/+|\/+$/g, '');
                var docBase = (typeof DOCUMENT_PUBLIC_BASE_URL !== 'undefined' ? DOCUMENT_PUBLIC_BASE_URL : '').replace(/\/+$/g, '');
                if (/^FORM_[A-Z]+\//i.test(path)) {
                    var relative = '/' + docPrefix + '/' + path.replace(/^\/+/, '');
                    return docBase ? (docBase + relative) : relative;
                }
                if (path.charAt(0) === '/') return path;
                return '/' + path.replace(/^\/+/, '');
            };
            // Uniform document pill — always renders the PDF icon + "View Document" label.
            // Filenames are intentionally hidden in the preview so every row looks consistent
            // regardless of whether the file was just attached or comes from an existing record.
            const DOC_PILL_LABEL = 'View Document';
            const docPillLink = function (href) {
                return '<a class="prv-sw-doc-pill" href="' + esc(href) + '" target="_blank" rel="noopener">'
                    + '<i class="fa fa-file-pdf-o"></i> ' + DOC_PILL_LABEL + '</a>';
            };
            const docPillStatic = function () {
                return '<span class="prv-sw-doc-pill" style="cursor:default;">'
                    + '<i class="fa fa-file-pdf-o"></i> ' + DOC_PILL_LABEL + '</span>';
            };
            const docCellFromRow = function (row, fileSel, existingSel) {
                const inp = row.querySelector(fileSel);
                if (inp && inp.files && inp.files[0]) {
                    return docPillLink(URL.createObjectURL(inp.files[0]));
                }
                if (existingSel) {
                    const exi = row.querySelector(existingSel);
                    if (exi && exi.value && String(exi.value).trim()) {
                        return docPillLink(resolveAssetUrl(exi.value));
                    }
                }
                const link = row.querySelector('.fs-doc-existing a, .local-file-preview a');
                if (link && link.getAttribute('href')) {
                    return docPillLink(link.getAttribute('href'));
                }
                return '<span class="prv-sw-doc-empty">—</span>';
            };
            const docLabelForInput = function (inputId) {
                const inp = document.getElementById(inputId);
                if (!inp) return '<span class="prv-sw-doc-empty">—</span>';

                const removedMap = {
                    aadhaar_doc: 'aadhaar_doc_removed',
                    pancard_doc: 'pancard_doc_removed',
                };
                const removedId = removedMap[inputId];
                if (removedId) {
                    const removedEl = document.getElementById(removedId);
                    if (removedEl && String(removedEl.value || '').trim() === '1') {
                        return '<span class="prv-sw-doc-empty">—</span>';
                    }
                }

                if (inp.files && inp.files[0]) {
                    return docPillLink(URL.createObjectURL(inp.files[0]));
                }

                const sib = inp.nextElementSibling;
                if (sib && sib.classList && sib.classList.contains('local-file-preview')) {
                    const a = sib.querySelector('a');
                    if (a && a.getAttribute('href')) return docPillLink(a.getAttribute('href'));
                }

                const wrap = inp.closest('td, .fs-upload-card, tr, .fs-return-upload-cell, .fs-section-body');
                if (wrap) {
                    const existingLink = wrap.querySelector(
                        '.aadhaar-doc-container a[href], .pan-doc-container a[href], '
                        + '.fs-doc-existing a[href], a[href*="private_documents"], '
                        + 'a[href*="attached_documents"], a[href*="/competency/"], a[href*="competency/"]'
                    );
                    if (existingLink && existingLink.getAttribute('href')) {
                        return docPillLink(existingLink.getAttribute('href'));
                    }
                }

                const inputWrap = inp.closest('.aadhaar-doc-input, .pan-doc-input');
                if (inputWrap && inputWrap.parentElement) {
                    const siblingLink = inputWrap.parentElement.querySelector(
                        '.aadhaar-doc-container a[href], .pan-doc-container a[href]'
                    );
                    if (siblingLink && siblingLink.getAttribute('href')) {
                        return docPillLink(siblingLink.getAttribute('href'));
                    }
                }

                return '<span class="prv-sw-doc-empty">—</span>';
            };
            const imageSrcAny = function (ids) {
                for (let i = 0; i < ids.length; i++) {
                    const el = document.getElementById(ids[i]);
                    if (!el) continue;
                    const s = (el.getAttribute('src') || '').trim();
                    if (s) return s;
                }
                return '';
            };
            const renderThumb = function (wrapId, imgIds, w, h, alt) {
                const wrap = document.getElementById(wrapId);
                if (!wrap) return;
                const src = imageSrcAny(Array.isArray(imgIds) ? imgIds : [imgIds]);
                if (src) {
                    wrap.innerHTML = '<img src="' + esc(src) + '" alt="' + esc(alt) + '" style="width:' + w + 'px;height:' + h + 'px;">';
                } else {
                    wrap.innerHTML = '<div class="prv-sw-no-img" style="width:' + w + 'px;height:' + h + 'px;">No ' + esc(alt) + '</div>';
                }
            };
            const selectedText = function (sel) {
                if (!sel) return '';
                const opt = sel.options && sel.options[sel.selectedIndex];
                const t = opt ? (opt.textContent || '').trim() : '';
                if (!t || /^select /i.test(t)) return '';
                return t;
            };

            const formCode = (v('form_name') || valByName('form_name') || 'S').toUpperCase();
            const applType = (v('appl_type') || valByName('appl_type') || 'N').toUpperCase();
            const showWork = (formCode === 'S' || formCode === 'W');
            const showWiremanCert = (formCode === 'S');

            // Section visibility + numbering — matches each form's native section numbers.
            // S:  1 (1-5) · 6 Edu · 7 Work (7a/7b) · 8 Prev S · 9 Wireman · 10 Docs
            // W:  1 (1-5) · 6 Edu · 7 Work · 8 Prev W · ──        · 9  Docs
            // WH: 1 (1-5) · 6 Edu · ──     · 7 Prev H · ──        · 8  Docs
            setSecVisible('prvSwSecWork', showWork);
            setSecVisible('prvSwSecWiremanCert', showWiremanCert);
            setNum('personal', '1');
            setNum('edu', '6');
            if (formCode === 'S') {
                setNum('7a', '7a');
                setNum('7b', '7b');
            } else {
                setNum('work', '7');
            }
            if (formCode === 'S') {
                setNum('prev', '8');
                setNum('wireman', '9');
                setNum('docs', '10');
            } else if (formCode === 'W') {
                setNum('wireman', '9');
                setNum('prev', '8');
                setNum('docs', '9');
            } else {
                setNum('wireman', '8');
                setNum('prev', '7');
                setNum('docs', '8');
            }

            // Form / renew badge
            const formBadgeEl = document.getElementById('prvSwFormBadge');
            if (formBadgeEl) formBadgeEl.textContent = 'FORM ' + formCode;
            const renewBadgeEl = document.getElementById('prvSwRenewBadge');
            if (renewBadgeEl) renewBadgeEl.style.display = (applType === 'R') ? '' : 'none';

            // Header / meta cards + print head
            const formTitleMap = {
                S: 'Supervisor Competency Certificate',
                W: 'Wireman Competency Certificate',
                WH: 'Wireman Helper Competency Certificate'
            };
            const formFullTitle = formTitleMap[formCode] || ('Form ' + formCode);
            const applicantName = v('Applicant_Name') || valByName('applicant_name');
            const appId = v('application_id') || valByName('application_id');
            const licenceVal = v('license_number') || valByName('license_number');
            setField('prvSwMetaName', applicantName);
            setField('prvSwMetaAppId', appId || 'Draft (not saved yet)');
            setField('prvSwMetaLicence', licenceVal || ('Certificate ' + formCode));

            const printTagEl = document.getElementById('prvSwPrintTag');
            if (printTagEl) {
                const tagParts = [];
                if (applType === 'R') tagParts.push('Renewal');
                tagParts.push('Form ' + formCode);
                if (licenceVal) tagParts.push('Licence: ' + licenceVal);
                printTagEl.textContent = tagParts.join(' · ');
            }
            const printTitleEl = document.getElementById('prvSwPrintTitle');
            if (printTitleEl) printTitleEl.textContent = formFullTitle;

            // Personal & contact details
            setField('prvSwName', applicantName);
            setField('prvSwFather', v('Fathers_Name') || valByName('fathers_name'));
            setField('prvSwEmail', v('applicant_email'));
            setField('prvSwAddress', v('applicants_address'));
            setField('prvSwDob', fmtDate(v('d_o_b')));
            setField('prvSwAge', v('age'));
            renderThumb('prvSwPhotoWrap', ['photo_preview', 'preview_applicant'], 88, 106, 'Photo');
            renderThumb('prvSwSignWrap', ['sign_preview', 'preview_signature'], 150, 56, 'Signature');

            // Education table
            const eduTitleEl = document.getElementById('prvSwSecEduTitle');
            if (eduTitleEl) {
                eduTitleEl.textContent = (formCode === 'S')
                    ? "Applicant's Educational / Technical Qualification"
                    : 'Educational / Technical Qualification';
            }
            const eduBody = document.getElementById('prvSwEduBody');
            if (eduBody) {
                const eduRows = document.querySelectorAll('#education-container .education-fields, #education-container tr');
                eduBody.innerHTML = '';
                let printed = 0;
                eduRows.forEach(function (row) {
                    const lv = row.querySelector('[name="educational_level[]"]');
                    const inst = row.querySelector('[name="institute_name[]"]');
                    const mon = row.querySelector('[name="month_of_passing[]"]');
                    const yr = row.querySelector('[name="year_of_passing[]"]');
                    const cert = row.querySelector('[name="certificate_no[]"]');
                    if (!lv && !inst && !cert) return;
                    printed++;
                    const lvText = lv ? (selectedText(lv) || lv.value || '—') : '—';
                    const monText = mon ? (selectedText(mon) || mon.value || '—') : '—';
                    const yrText = yr && yr.value && yr.value !== '0' ? yr.value : '—';
                    eduBody.innerHTML += '<tr>'
                        + '<td>' + printed + '</td>'
                        + '<td class="prv-sw-td-left">' + esc(lvText) + '</td>'
                        + '<td class="prv-sw-td-left">' + esc(inst ? inst.value || '—' : '—') + '</td>'
                        + '<td>' + esc(monText) + '</td>'
                        + '<td>' + esc(yrText) + '</td>'
                        + '<td>' + esc(cert ? cert.value || '—' : '—') + '</td>'
                        + '<td>' + docCellFromRow(row, '[name="education_document[]"]', '[name="existing_document[]"]') + '</td>'
                        + '</tr>';
                });
                if (!printed) {
                    eduBody.innerHTML = '<tr><td colspan="7" class="text-muted py-3" style="text-align:center;">No education entries</td></tr>';
                }
            }

            const EMP_LABEL_SW = {
                private_organisation: 'Private organisation',
                electrical_contractor: 'Electrical contractor',
                retired_employee: 'Retired Employee',
                govt_organisation: 'Govt organisation',
                apprenticeship: 'Apprenticeship',
                board_member_tnelb: 'Board Member / Ex. Board Member of TNELB'
            };
            const WORK_NATURE_SW = {
                erection: 'Erection',
                maintenance: 'Maintenance',
                erection_maintenance: 'Erection & Maintenance'
            };
            const VOLTAGE_LEVEL_SW = {
                up_to_650v: 'Up to 650V',
                '650v_to_33kv': 'Above 650V to 33KV',
                above_33kv: 'Above 33KV'
            };
            const CONTRACTOR_TYPE_SW = 'electrical_contractor';
            const VOLTAGE_DISABLES_KVA_SW = 'up_to_650v';
            const MONTH_SHORT_SW = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const readRowDateIso = function (inp) {
                if (!inp) return '';
                if (typeof readWorkDateIsoGeneric === 'function') {
                    return readWorkDateIsoGeneric($(inp));
                }
                return String(inp.value || inp.getAttribute('data-raw') || '').trim();
            };
            const fmtRowDate = function (inp) {
                const iso = readRowDateIso(inp);
                if (!iso) return '—';
                const m = iso.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                return m ? (m[3] + '-' + m[2] + '-' + m[1]) : iso;
            };
            const fmtPrettySw = function (iso) {
                if (!iso) return '—';
                const p = iso.match(/^(\d{4})-(\d{2})-(\d{2})$/);
                if (!p) return iso;
                const y = parseInt(p[1], 10);
                const m = parseInt(p[2], 10);
                const d = parseInt(p[3], 10);
                if (isNaN(y) || isNaN(m) || isNaN(d) || m < 1 || m > 12) return iso;
                return d + ' ' + MONTH_SHORT_SW[m - 1] + ' ' + y;
            };
            const todayIsoSw = function () {
                const n = new Date();
                return n.getFullYear() + '-' + String(n.getMonth() + 1).padStart(2, '0') + '-' + String(n.getDate()).padStart(2, '0');
            };
            const calendarDiffYMDSw = function (fromIso, toIso) {
                const from = new Date(fromIso + 'T12:00:00');
                const to = new Date(toIso + 'T12:00:00');
                if (isNaN(from.getTime()) || isNaN(to.getTime()) || to < from) return null;
                let y = to.getFullYear() - from.getFullYear();
                let m = to.getMonth() - from.getMonth();
                let d = to.getDate() - from.getDate();
                if (d < 0) {
                    m--;
                    d += new Date(to.getFullYear(), to.getMonth(), 0).getDate();
                }
                if (m < 0) {
                    y--;
                    m += 12;
                }
                return { y: y, m: m, d: d };
            };
            const rowVal = function (el) {
                return el ? (String(el.value || '').trim() || '—') : '—';
            };
            const swAttachBlockHtml = function (label, row, fileSel, existingSel, naText) {
                let inner = '';
                if (naText) {
                    inner = '<span class="wx-sum-attach-value">' + esc(naText) + '</span>';
                } else {
                    const docHtml = docCellFromRow(row, fileSel, existingSel);
                    if (docHtml.indexOf('prv-sw-doc-pill') !== -1) {
                        inner = docHtml.replace('prv-sw-doc-pill', 'prv-sw-doc-pill wx-sum-doc-link');
                    } else if (docHtml.indexOf('prv-sw-doc-empty') !== -1) {
                        inner = '<span class="wx-sum-attach-value">—</span>';
                    } else {
                        inner = '<span class="wx-sum-attach-value">File attached</span>';
                    }
                }
                return '<div class="wx-sum-attach-block"><span class="wx-sum-attach-label">' + esc(label) + ' :</span>' + inner + '</div>';
            };
            const buildSwWorkPeriodHtml = function (fr, to, tillChk, yPart, mPart, dPart) {
                const fromIso = readRowDateIso(fr);
                const toIso = readRowDateIso(to);
                const isTill = tillChk && tillChk.checked;
                const toEffIso = isTill ? todayIsoSw() : toIso;
                const toText = isTill ? 'Till date' : (toIso ? fmtPrettySw(toIso) : '—');
                let yN = yPart ? (parseInt(yPart.value, 10) || 0) : 0;
                let mN = mPart ? (parseInt(mPart.value, 10) || 0) : 0;
                let dN = dPart ? (parseInt(dPart.value, 10) || 0) : 0;
                if (!yN && !mN && !dN && fromIso && toEffIso) {
                    const diff = calendarDiffYMDSw(fromIso, toEffIso);
                    if (diff) {
                        yN = diff.y;
                        mN = diff.m;
                        dN = diff.d;
                    }
                }
                let html = '<div class="wx-period-box"><div class="wx-period-dates">'
                    + '<div class="wx-period-mini"><span class="wx-period-label">From</span><span class="wx-period-val">' + esc(fromIso ? fmtPrettySw(fromIso) : '—') + '</span></div>'
                    + '<div class="wx-period-mini"><span class="wx-period-label">To</span><span class="wx-period-val">' + (isTill ? '<span class="prv-sw-badge-till">Till date</span>' : esc(toText)) + '</span></div>'
                    + '</div>';
                if (fromIso && toEffIso) {
                    html += '<div class="wx-period-duration">'
                        + '<div class="wx-period-dur-cell"><span class="wx-period-dur-num">' + yN + '</span><span class="wx-period-dur-lbl">Years</span></div>'
                        + '<div class="wx-period-dur-cell"><span class="wx-period-dur-num">' + mN + '</span><span class="wx-period-dur-lbl">Months</span></div>'
                        + '<div class="wx-period-dur-cell"><span class="wx-period-dur-num">' + dN + '</span><span class="wx-period-dur-lbl">Days</span></div>'
                        + '</div>';
                }
                html += '</div>';
                return html;
            };
            const buildSwWorkThead = function (code, theadId) {
                const thead = document.getElementById(theadId || 'prvSwWorkThead');
                const tableId = (theadId === 'prvSwWorkThead7a') ? 'prvSwWorkTable7a'
                    : ((theadId === 'prvSwWorkThead7b') ? 'prvSwWorkTable7b' : 'prvSwWorkTable');
                const workTable = document.getElementById(tableId);
                if (!thead) return;
                if (workTable) {
                    workTable.classList.toggle('wx-summary-table', code === 'S');
                }
                if (code === 'S') {
                    thead.innerHTML = '<tr>'
                        + '<th class="wx-summary-th-sno">S.No</th>'
                        + '<th>Employment Type</th>'
                        + '<th class="wx-summary-th-org"><span class="wx-th-org-line">Organisation &amp;</span><span class="wx-th-org-line">Address</span></th>'
                        + '<th>Designation</th>'
                        + '<th>Nature of Work</th>'
                        + '<th>Voltage Level</th>'
                        + '<th>Transformer kVA</th>'
                        + '<th>Total Experience</th>'
                        + '<th>Attachment</th></tr>';
                } else {
                    thead.innerHTML = '<tr>'
                        + '<th>#</th><th>Employment Type</th><th>Employer / Organisation</th>'
                        + '<th>From</th><th>To</th><th>Duration</th><th>Designation</th><th>Document</th></tr>';
                }
            };
            const buildFormSWorkSummaryRow = function (row, sno) {
                const typeSel = row.querySelector('.work-employment-type');
                const emp = row.querySelector('.work-employer-input') || row.querySelector('[name="work_employer_name[]"]');
                const address = row.querySelector('.work-org-address');
                const des = row.querySelector('.work-designation') || row.querySelector('[name="designation[]"]');
                const cat = row.querySelector('.work-contractor-cat');
                const licence = row.querySelector('.work-licence-number');
                const natureSel = row.querySelector('.work-nature');
                const voltSel = row.querySelector('.work-voltage');
                const kvaEl = row.querySelector('.work-transformer-kva');
                const fr = row.querySelector('.work-date-from');
                const to = row.querySelector('.work-date-to');
                const tillChk = row.querySelector('.work-date-till');
                const yPart = row.querySelector('.work-duration-y');
                const mPart = row.querySelector('.work-duration-m');
                const dPart = row.querySelector('.work-duration-d');

                const empTypeVal = typeSel ? (typeSel.value || '').trim() : '';
                const isContractor = (empTypeVal === CONTRACTOR_TYPE_SW);
                const isBoardMember = (empTypeVal === 'board_member_tnelb');
                const empTxt = EMP_LABEL_SW[empTypeVal] || selectedText(typeSel) || empTypeVal || '—';
                const employer = emp ? (emp.value || '').trim() : '';
                const addrTxt = address ? (address.value || '').trim() : '';
                const catTxt = cat ? (cat.value || '').trim() : '';
                const licTxt = licence ? (licence.value || '').trim() : '';
                const natureVal = natureSel ? (natureSel.value || '').trim() : '';
                const voltVal = voltSel ? (voltSel.value || '').trim() : '';
                const kvaRaw = kvaEl ? (kvaEl.value || '').trim() : '';

                let empCell = '<span class="wx-sum-main">' + esc(empTxt) + '</span>';
                if (isContractor && catTxt) {
                    empCell += '<span class="wx-sum-sub">Cat: ' + esc(catTxt) + '</span>';
                }
                if (isContractor && licTxt) {
                    empCell += '<span class="wx-sum-sub">Licence: ' + esc(licTxt) + '</span>';
                }

                let orgCell = '<span class="wx-sum-main">' + esc(employer || '—') + '</span>';
                if (addrTxt) {
                    orgCell += '<span class="wx-sum-sub">' + esc(addrTxt) + '</span>';
                }

                const kvaTxt = isBoardMember
                    ? 'Not applicable'
                    : ((voltVal === VOLTAGE_DISABLES_KVA_SW)
                        ? 'Not applicable'
                        : (kvaRaw ? esc(kvaRaw + ' kVA') : '—'));

                const periodHtml = buildSwWorkPeriodHtml(fr, to, tillChk, yPart, mPart, dPart);
                const isTill = tillChk && tillChk.checked;
                const relieveNote = isTill ? 'Not required (Till date)' : (isBoardMember ? 'Optional' : null);
                const attachHtml = '<div class="wx-sum-attach-stack">'
                    + swAttachBlockHtml('Supporting', row, '[name="work_document[]"]', '[name="existing_work_document[]"]', null)
                    + swAttachBlockHtml('Relieving', row, '[name="work_relieving_letter[]"]', '[name="existing_work_relieving_document[]"]', relieveNote)
                    + '</div>';

                return '<tr>'
                    + '<td class="work-row-summary-sno">' + sno + '</td>'
                    + '<td class="work-row-summary-employment prv-sw-td-left">' + empCell + '</td>'
                    + '<td class="work-row-summary-org-address prv-sw-td-left">' + orgCell + '</td>'
                    + '<td class="work-row-summary-designation prv-sw-td-left">' + esc(rowVal(des)) + '</td>'
                    + '<td class="work-row-summary-nature">' + esc(isBoardMember ? '—' : (WORK_NATURE_SW[natureVal] || natureVal || '—')) + '</td>'
                    + '<td class="work-row-summary-voltage">' + esc(isBoardMember ? '—' : (VOLTAGE_LEVEL_SW[voltVal] || voltVal || '—')) + '</td>'
                    + '<td class="work-row-summary-kva">' + kvaTxt + '</td>'
                    + '<td class="work-row-summary-period">' + periodHtml + '</td>'
                    + '<td class="work-row-summary-attachments prv-sw-td-left">' + attachHtml + '</td>'
                    + '</tr>';
            };
            const buildFormSWorkMeetingRow = function (row) {
                const meetingDetails = row.querySelector('.work-board-meeting-details');
                const meetingDateInp = row.querySelector('.work-board-meeting-date');
                const detailsTxt = meetingDetails ? (meetingDetails.value || '').trim() : '';
                const meetingIso = meetingDateInp ? readRowDateIso(meetingDateInp) : '';
                const meetingDateTxt = meetingIso ? fmtPrettySw(meetingIso) : '—';
                return '<tr class="prv-sw-board-meeting-row"><td></td>'
                    + '<td colspan="8" style="font-size:.78rem;background:#f8fafd;">'
                    + '<strong>Date of Meeting:</strong> ' + esc(meetingDateTxt)
                    + ' &nbsp;|&nbsp; <strong>Details of the meeting:</strong> ' + esc(detailsTxt || '—')
                    + '</td></tr>';
            };
            const rowHasFormSWorkData = function (row) {
                const typeSel = row.querySelector('.work-employment-type');
                const emp = row.querySelector('.work-employer-input') || row.querySelector('[name="work_employer_name[]"]');
                const fr = row.querySelector('.work-date-from');
                const to = row.querySelector('.work-date-to');
                const des = row.querySelector('.work-designation') || row.querySelector('[name="designation[]"]');
                return !!(typeSel || emp || fr || to || des
                    || row.querySelector('.work-contractor-cat')
                    || row.querySelector('.work-org-address'));
            };
            const fillFormSWorkPreviewBody = function (workBody, rowSelector, startSno) {
                if (!workBody) return 0;
                workBody.innerHTML = '';
                let printed = 0;
                document.querySelectorAll(rowSelector).forEach(function (row) {
                    if (!row.classList.contains('work-fields') || !rowHasFormSWorkData(row)) return;
                    printed++;
                    workBody.innerHTML += buildFormSWorkSummaryRow(row, startSno + printed - 1);
                    const typeSel = row.querySelector('.work-employment-type');
                    const empTypeVal = typeSel ? (typeSel.value || '').trim() : '';
                    if (empTypeVal === 'board_member_tnelb') {
                        workBody.innerHTML += buildFormSWorkMeetingRow(row);
                    }
                });
                if (!printed) {
                    workBody.innerHTML = '<tr><td colspan="9" class="text-muted py-3" style="text-align:center;">No work experience entries</td></tr>';
                }
                return printed;
            };

            const fillFormSWork7bPreview = function () {
                const clearIds = [
                    'prvSwWork7bOrg', 'prvSwWork7bDesig', 'prvSwWork7bAddr',
                    'prvSwWork7bMeetingDate', 'prvSwWork7bMeetingDetails',
                    'prvSwWork7bFrom', 'prvSwWork7bTo', 'prvSwWork7bDuration'
                ];
                const clearHtmlIds = ['prvSwWork7bSupportDoc', 'prvSwWork7bRelieveDoc'];
                let row = null;
                document.querySelectorAll('#work-container-current .work-fields').forEach(function (r) {
                    if (r.classList.contains('work-fields') && rowHasFormSWorkData(r)) {
                        row = r;
                    }
                });
                if (!row) {
                    clearIds.forEach(function (id) { setField(id, ''); });
                    clearHtmlIds.forEach(function (id) { setFieldHtml(id, ''); });
                    return;
                }

                const emp = row.querySelector('.work-employer-input') || row.querySelector('[name="work_employer_name[]"]');
                const address = row.querySelector('.work-org-address');
                const des = row.querySelector('.work-designation') || row.querySelector('[name="designation[]"]');
                const meetingDetails = row.querySelector('.work-board-meeting-details');
                const meetingDateInp = row.querySelector('.work-board-meeting-date');
                const fr = row.querySelector('.work-date-from');
                const to = row.querySelector('.work-date-to');
                const tillChk = row.querySelector('.work-date-till');
                const yPart = row.querySelector('.work-duration-y');
                const mPart = row.querySelector('.work-duration-m');
                const dPart = row.querySelector('.work-duration-d');

                setField('prvSwWork7bOrg', emp ? emp.value : '');
                setField('prvSwWork7bAddr', address ? address.value : '');
                setField('prvSwWork7bDesig', des ? des.value : '');

                const meetingIso = meetingDateInp ? readRowDateIso(meetingDateInp) : '';
                setField('prvSwWork7bMeetingDate', meetingIso ? fmtPrettySw(meetingIso) : '');
                setField('prvSwWork7bMeetingDetails', meetingDetails ? meetingDetails.value : '');

                const fromIso = readRowDateIso(fr);
                const toIso = readRowDateIso(to);
                const isTill = tillChk && tillChk.checked;
                setField('prvSwWork7bFrom', fromIso ? fmtPrettySw(fromIso) : '');
                if (isTill) {
                    setFieldHtml('prvSwWork7bTo', '<span class="prv-sw-badge-till">Till date</span>');
                } else {
                    setField('prvSwWork7bTo', toIso ? fmtPrettySw(toIso) : '');
                }

                let yN = yPart ? (parseInt(yPart.value, 10) || 0) : 0;
                let mN = mPart ? (parseInt(mPart.value, 10) || 0) : 0;
                let dN = dPart ? (parseInt(dPart.value, 10) || 0) : 0;
                const toEffIso = isTill ? todayIsoSw() : toIso;
                if (!yN && !mN && !dN && fromIso && toEffIso) {
                    const diff = calendarDiffYMDSw(fromIso, toEffIso);
                    if (diff) {
                        yN = diff.y;
                        mN = diff.m;
                        dN = diff.d;
                    }
                }
                const durParts = [];
                if (yN || mN || dN) {
                    durParts.push(yN + ' Year' + (yN === 1 ? '' : 's'));
                    durParts.push(mN + ' Month' + (mN === 1 ? '' : 's'));
                    durParts.push(dN + ' Day' + (dN === 1 ? '' : 's'));
                }
                setField('prvSwWork7bDuration', durParts.join(', '));

                setFieldHtml('prvSwWork7bSupportDoc', docCellFromRow(row, '[name="work_document[]"]', '[name="existing_work_document[]"]'));
                const relieveHtml = isTill
                    ? '<span class="text-muted" style="font-size:.82rem;">Not required (Till date)</span>'
                    : docCellFromRow(row, '[name="work_relieving_letter[]"]', '[name="existing_work_relieving_document[]"]');
                setFieldHtml('prvSwWork7bRelieveDoc', relieveHtml);
            };

            const workTitleEl = document.getElementById('prvSwWorkTitle');
            const workTamilEl = document.getElementById('prvSwWorkTamil');
            const workFormS = document.getElementById('prvSwWorkFormS');
            const workFormOther = document.getElementById('prvSwWorkFormOther');
            if (workTitleEl && workTamilEl) {
                if (formCode === 'S') {
                    workTitleEl.innerHTML = 'Details of Previous and Current Work experiences <span class="prv-sw-section-hint">(Upload the documents)</span>';
                    workTamilEl.textContent = 'பெற்றுள்ள முந்தைய மற்றும் தற்போதைய அனுபவங்களின் விவரங்கள் (ஆவணங்களை பதிவேற்ற வேண்டும்)';
                } else {
                    workTitleEl.textContent = 'Previous & Current Work Experience';
                    workTamilEl.textContent = 'முந்தைய மற்றும் தற்போதைய பணி அனுபவம்';
                }
            }

            // Work experience table (S/W only)
            if (showWork) {
                const swPanel = document.querySelector('#appPreviewModalSw .prv-sw-panel');
                if (swPanel) {
                    swPanel.style.maxWidth = (formCode === 'S') ? 'min(96vw, 1100px)' : '940px';
                }

                if (formCode === 'S') {
                    if (workFormS) workFormS.style.display = '';
                    if (workFormOther) workFormOther.style.display = 'none';

                    buildSwWorkThead('S', 'prvSwWorkThead7a');
                    fillFormSWorkPreviewBody(
                        document.getElementById('prvSwWorkBody7a'),
                        '#work-container-previous .work-fields',
                        1
                    );

                    const boardYesEl = document.getElementById('current_work_board_member_yes');
                    const is7bYes = !!(boardYesEl && boardYesEl.checked);
                    const yn7bEl = document.getElementById('prvSwWork7bYn');
                    if (yn7bEl) {
                        yn7bEl.innerHTML = is7bYes
                            ? '<span class="prv-sw-yesno-yes">Yes</span>'
                            : '<span class="prv-sw-yesno-no">No</span>';
                    }
                    const block7bEl = document.getElementById('prvSwWork7bBlock');
                    if (block7bEl) block7bEl.style.display = is7bYes ? '' : 'none';
                    if (is7bYes) {
                        fillFormSWork7bPreview();
                    }
                } else {
                    if (workFormS) workFormS.style.display = 'none';
                    if (workFormOther) workFormOther.style.display = '';

                    const workBody = document.getElementById('prvSwWorkBody');
                    buildSwWorkThead(formCode, 'prvSwWorkThead');
                    if (workBody) {
                        const workRows = document.querySelectorAll('#work-container .work-fields');
                        workBody.innerHTML = '';
                        let printed = 0;
                        const colSpan = 8;

                        workRows.forEach(function (row) {
                            if (!row.classList.contains('work-fields')) return;

                            const typeSel = row.querySelector('[name="work_employment_type[]"]') || row.querySelector('.work-employment-type');
                            const emp = row.querySelector('[name="work_employer_name[]"]') || row.querySelector('input[name="work_level[]"]');
                            const fr = row.querySelector('[name="work_date_from[]"]') || row.querySelector('.work-date-from');
                            const to = row.querySelector('[name="work_date_to[]"]') || row.querySelector('.work-date-to');
                            const yrs = row.querySelector('.work-duration-y');
                            const mos = row.querySelector('.work-duration-m');
                            const days = row.querySelector('.work-duration-d');
                            const totHidden = row.querySelector('[name="work_experience_total[]"]');
                            const des = row.querySelector('[name="designation[]"]');
                            if (!typeSel && !emp && !fr && !to && !des) return;

                            printed++;
                            const typeText = typeSel ? (selectedText(typeSel) || typeSel.value || '—') : '—';
                            let durText = '';
                            if (yrs && mos && days) {
                                const y = (yrs.value || '').trim();
                                const m = (mos.value || '').trim();
                                const d = (days.value || '').trim();
                                const parts = [];
                                if (y) parts.push(y + 'y');
                                if (m) parts.push(m + 'm');
                                if (d) parts.push(d + 'd');
                                durText = parts.join(' ');
                            }
                            if (!durText && totHidden) durText = totHidden.value || '';

                            workBody.innerHTML += '<tr>'
                                + '<td>' + printed + '</td>'
                                + '<td class="prv-sw-td-left">' + esc(typeText) + '</td>'
                                + '<td class="prv-sw-td-left">' + esc(emp ? emp.value || '—' : '—') + '</td>'
                                + '<td>' + esc(fmtRowDate(fr)) + '</td>'
                                + '<td>' + esc(fmtRowDate(to)) + '</td>'
                                + '<td>' + esc(durText || '—') + '</td>'
                                + '<td class="prv-sw-td-left">' + esc(des ? des.value || '—' : '—') + '</td>'
                                + '<td>' + docCellFromRow(row, '[name="work_document[]"]', '[name="existing_work_document[]"]') + '</td>'
                                + '</tr>';
                        });

                        if (!printed) {
                            workBody.innerHTML = '<tr><td colspan="' + colSpan + '" class="text-muted py-3" style="text-align:center;">No work experience entries</td></tr>';
                        }
                    }
                }
            }

            // Previous same-type certificate section
            let prevTitle, prevTamil, prevYesValue, prevNumId, prevIssueId, prevFromId, prevExpiryId;
            if (formCode === 'S') {
                prevTitle = 'Do you already possess a Supervisor Competency Certificate issued by this Board? If yes, please furnish the details.';
                prevTamil = 'இந்த வாரியத்தால் வழங்கப்பட்ட மேற்பார்வையாளர் தகுதி சான்றிதழ் உங்களிடம் உள்ளதா? ஆம் என்றால் அதன் குறிப்பு எண் மற்றும் தேதியை குறிப்பிடுக';
                prevYesValue = !!((document.getElementById('previous_license_yes') || {}).checked);
                prevNumId = 'previously_number';
                prevIssueId = 'previously_issue_date';
                prevFromId = 'previously_valid_from';
                prevExpiryId = 'previously_valid_to';
            } else if (formCode === 'W') {
                prevTitle = 'Previous Wireman / Helper Certificate';
                prevTamil = 'மின்கம்பியாளர் / உதவியாளர் தகுதி சான்றிதழ் விவரம்';
                prevYesValue = !!((document.getElementById('wireman_license_yes') || {}).checked);
                prevNumId = 'previously_number';
                prevIssueId = 'previously_issue_date';
                prevFromId = 'previously_valid_from';
                prevExpiryId = 'previously_valid_to';
            } else {
                prevTitle = 'Previous Wireman Helper Certificate';
                prevTamil = 'மின் கம்பி உதவியாளர் தகுதி சான்றிதழ் விவரம்';
                prevYesValue = !!((document.getElementById('wireman_license_yes') || {}).checked);
                prevNumId = 'previously_number_h';
                prevIssueId = 'previously_issue_date_h';
                prevFromId = 'previously_valid_from_h';
                prevExpiryId = 'previously_date_h';
            }
            const prevTitleEl = document.getElementById('prvSwSecPrevTitle');
            if (prevTitleEl) prevTitleEl.textContent = prevTitle;
            const prevTamilEl = document.getElementById('prvSwSecPrevTamil');
            if (prevTamilEl) prevTamilEl.textContent = prevTamil;
            const prevYnEl = document.getElementById('prvSwPrevYn');
            if (prevYnEl) {
                prevYnEl.innerHTML = prevYesValue
                    ? '<span class="prv-sw-yesno-yes">Yes</span>'
                    : '<span class="prv-sw-yesno-no">No</span>';
            }
            const prevBlockEl = document.getElementById('prvSwPrevBlock');
            if (prevBlockEl) prevBlockEl.style.display = prevYesValue ? '' : 'none';
            if (prevYesValue) {
                setField('prvSwPrevNo', v(prevNumId));
                setField('prvSwPrevIssueDate', fmtDate(v(prevIssueId)));
                setField('prvSwPrevFromDate', fmtDate(v(prevFromId)));
                setField('prvSwPrevExpiryDate', fmtDate(v(prevExpiryId)));
            } else {
                setField('prvSwPrevNo', '');
                setField('prvSwPrevIssueDate', '');
                setField('prvSwPrevFromDate', '');
                setField('prvSwPrevExpiryDate', '');
            }

            // Wireman certificate section (S only)
            if (showWiremanCert) {
                const wcTitleEl = document.getElementById('prvSwSecWcTitle');
                const wcTamilEl = document.getElementById('prvSwSecWcTamil');
                if (wcTitleEl) {
                    wcTitleEl.textContent = 'Do you also possess Wireman Competency Certificate issued by this Board? If so furnish the details.';
                }
                if (wcTamilEl) {
                    wcTamilEl.textContent = 'இந்த வாரியம் வழங்கிய கம்பி இணைப்பாளர் திறன் சான்றிதழ் உள்ளதா? இருந்தால், அதன் விவரங்களை வழங்கவும்.';
                }
                const wcYesEl = document.getElementById('yesOption');
                const wcYesValue = !!(wcYesEl && wcYesEl.checked);
                const wcYnEl = document.getElementById('prvSwWcYn');
                if (wcYnEl) {
                    wcYnEl.innerHTML = wcYesValue
                        ? '<span class="prv-sw-yesno-yes">Yes</span>'
                        : '<span class="prv-sw-yesno-no">No</span>';
                }
                const wcBlockEl = document.getElementById('prvSwWcBlock');
                if (wcBlockEl) wcBlockEl.style.display = wcYesValue ? '' : 'none';
                if (wcYesValue) {
                    setField('prvSwWcNo', v('certificate_no'));
                    setField('prvSwWcIssueDate', fmtDate(v('certificate_issue_date')));
                    setField('prvSwWcFromDate', fmtDate(v('certificate_valid_from')));
                    setField('prvSwWcExpiryDate', fmtDate(v('certificate_valid_to')));
                } else {
                    setField('prvSwWcNo', '');
                    setField('prvSwWcIssueDate', '');
                    setField('prvSwWcFromDate', '');
                    setField('prvSwWcExpiryDate', '');
                }
            }

            // Identity documents
            setField('prvSwAadhaar', v('aadhaar'));
            setField('prvSwPan', v('pancard'));
            const aadhaarDocEl = document.getElementById('prvSwAadhaarDoc');
            if (aadhaarDocEl) aadhaarDocEl.innerHTML = docLabelForInput('aadhaar_doc');
            const panDocEl = document.getElementById('prvSwPanDoc');
            if (panDocEl) panDocEl.innerHTML = docLabelForInput('pancard_doc');
        }

        async function showCompetencyPreviewModal() {
            const $sourceForm = $('#competency_form_ws').length ? $('#competency_form_ws') : $('#competency_form_p');
            if (!$sourceForm.length) return false;

            ensureSwPreviewStyles();
            ensureSwPreviewModalDom();

            const swModal = document.getElementById('appPreviewModalSw');
            if (!swModal) return false;

            populateSwPreview();

            swModal.classList.add('is-open');
            swModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            const swBodyEl = document.getElementById('prvSwBody');
            if (swBodyEl) swBodyEl.scrollTop = 0;

            return new Promise(function (resolve) {
                let resolved = false;
                const confirmBtn = document.getElementById('prvSwConfirmBtn');
                const backBtn = document.getElementById('prvSwBackBtn');
                const closeBtn = document.getElementById('prvSwCloseBtn');
                const printBtn = document.getElementById('prvSwPrintBtn');

                const escHandler = function (e) {
                    if (e.key === 'Escape' && swModal.classList.contains('is-open')) cleanup(false);
                };
                const overlayClick = function (e) {
                    if (e.target === swModal) cleanup(false);
                };

                function cleanup(val) {
                    if (resolved) return;
                    resolved = true;
                    swModal.classList.remove('is-open');
                    swModal.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    document.removeEventListener('keydown', escHandler);
                    swModal.removeEventListener('click', overlayClick);
                    if (confirmBtn) confirmBtn.onclick = null;
                    if (backBtn) backBtn.onclick = null;
                    if (closeBtn) closeBtn.onclick = null;
                    if (printBtn) printBtn.onclick = null;
                    resolve(!!val);
                }

                if (confirmBtn) confirmBtn.onclick = function () { cleanup(true); };
                if (backBtn) backBtn.onclick = function () { cleanup(false); };
                if (closeBtn) closeBtn.onclick = function () { cleanup(false); };
                if (printBtn) printBtn.onclick = function () {
                    // Refresh values in case the user changed something while previewing (unlikely but cheap)
                    populateSwPreview();
                    printSwPreview();
                };
                document.addEventListener('keydown', escHandler);
                swModal.addEventListener('click', overlayClick);
            });
        }

        if (!document.getElementById('appPreviewModalFormP')) {
            window.showCompetencyPreviewModal = showCompetencyPreviewModal;
        }

        function revealCompetencySectionForField($field) {
            if (!$field || !$field.length) {
                return;
            }
            const $section = $field.closest('.fs-section[data-mode="view"]');
            if (!$section.length) {
                return;
            }
            $section.attr('data-mode', 'edit');
            const toggleBtn = $section.find('.fs-section-edit-toggle').get(0);
            if (toggleBtn) {
                const icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.className = 'fa fa-check';
                }
                toggleBtn.setAttribute('title', 'Done');
            }
        }

        function scrollCompetencyToValidationError(firstErrorField) {
            $('#work-container .work-fields.work-row--compact').each(function () {
                if ($(this).find('.error-message').length) {
                    $(this).addClass('work-row--expanded').removeClass('work-row--compact work-row--in-summary');
                    $(this).find('.work-row-toggle-btn').attr('aria-expanded', 'true');
                    if (typeof window.wxSyncWorkSummaryTable === 'function') {
                        window.wxSyncWorkSummaryTable();
                    }
                }
            });

            const $visibleMsg = $('#competency_form_ws .error-message:visible').first();
            if ($visibleMsg.length) {
                const msgEl = $visibleMsg.get(0);
                if (msgEl && typeof msgEl.scrollIntoView === 'function') {
                    msgEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
            }

            if (!firstErrorField || !firstErrorField.length) {
                return;
            }

            revealCompetencySectionForField(firstErrorField);

            let $scrollTarget = firstErrorField.filter(':visible');
            if (!$scrollTarget.length && firstErrorField.is('.error-message')) {
                $scrollTarget = firstErrorField;
            }
            if (!$scrollTarget.length) {
                $scrollTarget = firstErrorField.nextAll('.error-message').filter(':visible').first();
            }
            if (!$scrollTarget.length) {
                $scrollTarget = firstErrorField.siblings('.error-message').filter(':visible').first();
            }
            if (!$scrollTarget.length) {
                const $section = firstErrorField.closest('.fs-section');
                if ($section.length) {
                    $scrollTarget = $section.find('.error-message:visible').first();
                }
            }
            if (!$scrollTarget.length) {
                $scrollTarget = firstErrorField.closest('tr, .form-group, .fs-field-head, td').filter(':visible').first();
            }
            if (!$scrollTarget.length) {
                $scrollTarget = firstErrorField;
            }

            const el = $scrollTarget.get(0);
            if (!el) {
                return;
            }

            if (typeof el.scrollIntoView === 'function') {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            const off = $scrollTarget.offset();
            if (off && typeof off.top === 'number' && off.top > 50) {
                $('html, body').animate({ scrollTop: off.top - 100 }, 500);
            }
        }

        $(document).off('click.competencyPay', '#submitPaymentBtn').on('click.competencyPay', '#submitPaymentBtn', async function (e) {
            e.preventDefault();
            e.stopPropagation();
            if ($('#competency_form_ws.fs-alt-form').length) {
                return;
            }
            if ($('#competency_form_p').length && !$('#competency_form_ws').length) {
                return;
            }
            const $submitBtn = $(this);
            if ($submitBtn.data('isProcessing') === true) {
                return;
            }
            $submitBtn.data('isProcessing', true).prop('disabled', true);
            const originalSubmitLabel = $submitBtn.html();
            $submitBtn.html('Processing...');
            normalizeCompetencyDynamicSections();

            const readableFiles = await validateReadableSelectedFiles();
            if (!readableFiles) {
                $submitBtn.data('isProcessing', false).prop('disabled', false).html(originalSubmitLabel);
                return;
            }

            clearCompetencyValidationErrors();
            $('.certificate-error').text('');
            $('.certificate-input').removeClass('is-invalid');
            let isValid = true;
            let firstErrorField = null;

            let dobEl = $('#d_o_b');
            if (dobEl.length && dobEl.val() === "") {
                let errorMsg = $('<span class="error-message text-danger d-block mt-1">Date of Birth is required.</span>');
                dobEl.after(errorMsg);
                if (!firstErrorField) firstErrorField = dobEl;
                isValid = false;
            } else if (dobEl.length) {
                const dobStr = dobEl.val();

                let dob;
                const inputType = dobEl.attr('type');

                // If native date input (YYYY-MM-DD), parse directly
                if (inputType === 'date') {
                    dob = new Date(dobStr + 'T00:00:00');
                } else {
                    // Expect DD-MM-YYYY or DD/MM/YYYY for text inputs (flatpickr)
                    const match = dobStr.trim().match(/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/);
                    if (match) {
                        const dd = parseInt(match[1], 10);
                        const mm = parseInt(match[2], 10);
                        const yyyy = parseInt(match[3], 10);
                        dob = new Date(yyyy, mm - 1, dd);
                    } else {
                        dob = new Date(''); // force invalid
                    }
                }

                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (isNaN(dob.getTime())) {
                    dobEl.after('<span class="error-message text-danger d-block mt-1">Please select a valid Date of Birth.</span>');
                    if (!firstErrorField) firstErrorField = dobEl;
                    isValid = false;
                } else {
                    let age = today.getFullYear() - dob.getFullYear();
                    const monthDiff = today.getMonth() - dob.getMonth();
                    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
                        age--;
                    }
                    $('#age').val(age);

                    if (dob > today) {
                        dobEl.after('<span class="error-message text-danger d-block mt-1">Date of Birth cannot be in the future.</span>');
                        if (!firstErrorField) firstErrorField = dobEl;
                        isValid = false;
                    } else if (age < 18 || age > 100) {
                        $('#age').after('<span class="error-message text-danger d-block mt-1">Age must be between 18 and 100.</span>');
                        if (!firstErrorField) firstErrorField = $('#age');
                        isValid = false;
                    }
                }
            }

            let nameRegex = /^[A-Za-z\s]+$/;
            let fathersNameEl = $('#Fathers_Name');
            if (fathersNameEl.length) {
                let fathersName = fathersNameEl.val().trim();
                if (fathersName === "") {
                    fathersNameEl.after('<span class="error-message text-danger d-block mt-1">Father\'s Name is required.</span>');
                    if (!firstErrorField) firstErrorField = fathersNameEl;
                    isValid = false;
                } else if (!nameRegex.test(fathersName)) {
                    fathersNameEl.after('<span class="error-message text-danger d-block mt-1">Only alphabets and spaces are allowed.</span>');
                    if (!firstErrorField) firstErrorField = fathersNameEl;
                    isValid = false;
                }
            }

            let applicantEmailEl = $('#competency_form_ws #applicant_email, #competency_form_ws [name="applicant_email"]').first();
            if (!applicantEmailEl.length) {
                applicantEmailEl = $('#applicant_email').first();
            }
            if (applicantEmailEl.length) {
                let ev = readApplicantEmailValue();
                let formNameEmail = ($('#form_name').val() || '').toString().trim().toUpperCase();
                let emailRequired = formNameEmail === 'S';
                if (emailRequired && ev === '') {
                    showCompetencyFieldError(applicantEmailEl, 'Email ID is required.');
                    if (!firstErrorField) firstErrorField = applicantEmailEl;
                    isValid = false;
                } else if (ev !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(ev)) {
                    showCompetencyFieldError(applicantEmailEl, 'Enter a valid Email ID.');
                    if (!firstErrorField) firstErrorField = applicantEmailEl;
                    isValid = false;
                } else {
                    clearCompetencyFieldError(applicantEmailEl);
                }
            }

            let applicantNameEl = $('#Applicant_Name');
            if (applicantNameEl.length) {
                let applicantName = applicantNameEl.val().trim();
                if (applicantName === "") {
                    applicantNameEl.after('<span class="error-message text-danger d-block mt-1">Applicant\'s Name is required.</span>');
                    if (!firstErrorField) firstErrorField = applicantNameEl;
                    isValid = false;
                } else if (!nameRegex.test(applicantName)) {
                    applicantNameEl.after('<span class="error-message text-danger d-block mt-1">Only alphabets and spaces are allowed.</span>');
                    if (!firstErrorField) firstErrorField = applicantNameEl;
                    isValid = false;
                }
            }

            if ($('#education-container .education-fields').length === 0) {
                $('#education-table').after('<span class="error-message text-danger d-block mt-1">At least one educational qualification is required.</span>');
                if (!firstErrorField) firstErrorField = $('#education-table');
                isValid = false;
            }

            $('#education-container .education-fields').each(function () {
                let eduLevel = $(this).find('select[name="educational_level[]"]');
                let instituteName = $(this).find('input[name="institute_name[]"]');
                let monthOfPassing = $(this).find('select[name="month_of_passing[]"]');
                let yearOfPassing = $(this).find('select[name="year_of_passing[]"]');
                let certificateNo = $(this).find('input[name="certificate_no[]"]');
                let educationUpload = $(this).find('input[name="education_document[]"], input[name^="education_document["]');

                if (eduLevel.length && (eduLevel.val() === null || eduLevel.val() === "")) {
                    eduLevel.after('<span class="error-message text-danger d-block mt-1">Education level is required.</span>');
                    if (!firstErrorField) firstErrorField = eduLevel;
                    isValid = false;
                } else if (eduLevel.length) {
                    let formName = ($('#form_name').val() || '').toString().toUpperCase();
                    if (formName === 'S') {
                        const allowed = ['DEE', 'BEE', 'MEE', 'AMIE'];
                        const val = (eduLevel.val() || '').toString().toUpperCase();
                        if (val !== '' && !allowed.includes(val)) {
                            eduLevel.after('<span class="error-message text-danger d-block mt-1">For FORM S, only Diploma (EE), B.E (EE), M.E (EE), or A pass in AMIE options are allowed.</span>');
                            if (!firstErrorField) firstErrorField = eduLevel;
                            isValid = false;
                        }
                    }
                }

                if (instituteName.length && instituteName.val().trim() === "") {
                    instituteName.after('<span class="error-message text-danger d-block mt-1">Institution name is required.</span>');
                    if (!firstErrorField) firstErrorField = instituteName;
                    isValid = false;
                }

                if (monthOfPassing.length && (monthOfPassing.val() === null || monthOfPassing.val() === "")) {
                    monthOfPassing.after('<span class="error-message text-danger d-block mt-1">Month of passing is required.</span>');
                    if (!firstErrorField) firstErrorField = monthOfPassing;
                    isValid = false;
                }

                if (yearOfPassing.length && (yearOfPassing.val() === "0" || yearOfPassing.val() === "")) {
                    yearOfPassing.after('<span class="error-message text-danger d-block mt-1">year of passing is required.</span>');
                    if (!firstErrorField) firstErrorField = yearOfPassing;
                    isValid = false;
                }

                // if (percentage.length && (percentage.val().trim() === "" || isNaN(percentage.val()) || percentage.val() < 0 || percentage.val() > 100)) {
                //     percentage.after('<span class="error-message text-danger d-block mt-1">Percentage / Grade is required</span>');
                //     if (!firstErrorField) firstErrorField = percentage;
                //     isValid = false;
                // }

                if (certificateNo.length && certificateNo.val().trim() === "") {
                    const $err = certificateNo.closest('td').find('.certificate-error').first();
                    if ($err.length) {
                        $err.text('Certificate No is required.');
                        certificateNo.addClass('is-invalid');
                    } else {
                        // Fallback (in case markup doesn't include certificate-error span)
                        certificateNo.after('<span class="error-message text-danger d-block mt-1">Certificate No is required.</span>');
                    }
                    if (!firstErrorField) firstErrorField = certificateNo;
                    isValid = false;
                }

                const $educationUploadWrap = educationUpload.closest('.form-s-file-upload-wrap');
                const $educationErrorTarget = $educationUploadWrap.length ? $educationUploadWrap : educationUpload;
                const hasEducationFile = educationUpload.toArray().some(function (input) {
                    if (!input) return false;
                    const hasFiles = !!(input.files && input.files.length > 0);
                    const hasValue = String(input.value || '').trim() !== '';
                    return hasFiles || hasValue;
                });
                const hasEducationPreview = $(this).find('.local-file-preview .preview-link').length > 0;
                const hasMarkedLocalSelection = educationUpload.toArray().some(function (input) {
                    return input && String(input.getAttribute('data-has-local-file') || '') === '1';
                });
                const existingEduInput = $(this).find('input[name="existing_document[]"]').first();
                const hasExistingEducationDoc = existingEduInput.length && (existingEduInput.val() || '').trim() !== '';

                if (educationUpload.length && !hasEducationFile && !hasEducationPreview && !hasMarkedLocalSelection && !hasExistingEducationDoc) {
                    $educationErrorTarget.after('<span class="error-message text-danger d-block mt-1">Education certificate upload is required.</span>');
                    if (!firstErrorField) firstErrorField = educationUpload;
                    isValid = false;
                } else if (educationUpload.length && hasEducationFile) {
                    const firstInputWithFile = educationUpload.toArray().find(function (input) {
                        if (!input) return false;
                        const hasFiles = !!(input.files && input.files.length > 0);
                        const hasValue = String(input.value || '').trim() !== '';
                        return hasFiles || hasValue;
                    });
                    const file = firstInputWithFile ? firstInputWithFile.files[0] : null;
                    if (file) {
                        const allowedType = 'application/pdf';
                        const minSize = 5 * 1024;   // 5 KB
                        const maxSize = 250 * 1024; // 250 KB

                        if (file.type !== allowedType) {
                            $educationErrorTarget.after('<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for Education upload.</span>');
                            if (!firstErrorField) firstErrorField = educationUpload;
                            isValid = false;
                        } else if (file.size < minSize || file.size > maxSize) {
                            $educationErrorTarget.after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 200 KB.</span>');
                            if (!firstErrorField) firstErrorField = educationUpload;
                            isValid = false;
                        }
                    }
                }
            });

            const formName = (($('#form_name').val() || '').trim() || '').toUpperCase();
            const workOptional = (formName === 'W' || formName === 'WH' || formName === 'P');
            const isSWorkForm = (formName === 'S');

            /** True when a work file input has a new selection, local preview, or marked upload (Form S card layout). */
            function workFileInputHasSelection($input) {
                if (!$input || !$input.length) return false;
                const el = $input.get(0);
                if (!el) return false;
                if (el.files && el.files.length > 0) return true;
                if (String(el.value || '').trim() !== '') return true;
                if (String(el.getAttribute('data-has-local-file') || '') === '1') return true;
                const $wrap = $input.closest('.form-s-file-upload-wrap');
                if ($wrap.length && $wrap.next('.local-file-preview').find('.preview-link').length) return true;
                return false;
            }

            function workRowHasSupportingDoc($row) {
                const $file = $row.find('input[name="work_document[]"], input[name^="work_document["]').first();
                if (workFileInputHasSelection($file)) return true;
                const $existing = $row.find('input[name="existing_work_document[]"]').first();
                return $existing.length && String($existing.val() || '').trim() !== '';
            }

            function workRowHasRelievingDoc($row) {
                const $file = $row.find('input[name="work_relieving_letter[]"], input[name^="work_relieving_letter["]').first();
                if (workFileInputHasSelection($file)) return true;
                const $existing = $row.find('input[name="existing_work_relieving_document[]"]').first();
                return $existing.length && String($existing.val() || '').trim() !== '';
            }

            function isFormS7bCurrentWorkRow($row) {
                return $row.closest('#work-container-current, .js-work-container[data-work-part="current"]').length > 0;
            }

            function isFormS7bBoardGateYes() {
                if (!$('#fs-7b-root').length) {
                    return false;
                }
                return ($('input[name="current_work_board_member"]:checked').val() || 'no').toLowerCase() === 'yes';
            }

            /* Recompute work duration hidden fields before validating (native change + blur for jQuery handlers). */
            if ($('#work-container').length) {
                $('#work-container .work-fields').find('.work-date-from, .work-date-to').each(function () {
                    const el = this;
                    if (el.dispatchEvent) {
                        el.dispatchEvent(new Event('input', { bubbles: true }));
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                    $(el).trigger('blur');
                });
            }

            $('.js-work-container .work-fields, #work-container .work-fields').each(function () {
                if (isSWorkForm) {
                    /* Form S (13-column SCC layout):
                       Required per row:
                         • Employment Type (col 2)
                         • Contractor Category (col 3) + Licence Number (col 4) — ONLY if Electrical contractor
                         • Name of Contractor / org / board (col 5), Organisation Address (col 6),
                           Designation (col 7), Nature of Work (col 8), Voltage Level (col 9)
                         • Highest Transformer kVA (col 10) — UNLESS Voltage = "Up to 650V"
                         • Period of Experience From (col 11), and To unless "Till date" is checked
                         • Supporting documents (col 12)
                         • Relieving Letter (col 13) — UNLESS "Till date" is checked */
                    const $row = $(this);
                    if (isFormS7bCurrentWorkRow($row) && !isFormS7bBoardGateYes()) {
                        return;
                    }
                    const employmentType  = $row.find('.work-employment-type');
                    const contractorCat   = $row.find('.work-contractor-cat');
                    const licenceNumber   = $row.find('.work-licence-number');
                    const employerInput   = $row.find('.work-employer-input');
                    const orgAddress      = $row.find('.work-org-address');
                    const designation     = $row.find('input[name="designation[]"]');
                    const natureOfWork    = $row.find('.work-nature');
                    const voltageLevel    = $row.find('.work-voltage');
                    const transformerKva  = $row.find('.work-transformer-kva');
                    const fromDate        = $row.find('.work-date-from').first();
                    const toDate          = $row.find('.work-date-to').first();
                    const tillDateChk     = $row.find('.work-date-till');
                    const isTillDate      = tillDateChk.length && tillDateChk.is(':checked');
                    const fromIso         = readWorkDateIsoGeneric(fromDate);
                    const toIso           = readWorkDateIsoGeneric(toDate);

                    const workDocument    = $row.find('input[name="work_document[]"], input[name^="work_document["]').first();
                    const hasSupportingDoc = workRowHasSupportingDoc($row);
                    const $workUploadWrap = workDocument.closest('.form-s-file-upload-wrap');
                    const $workErrorTarget = $workUploadWrap.length ? $workUploadWrap : workDocument;

                    const relieveInput    = $row.find('input[name="work_relieving_letter[]"], input[name^="work_relieving_letter["]').first();
                    const hasRelieveFile  = workRowHasRelievingDoc($row);
                    const $relieveWrap    = relieveInput.closest('.form-s-file-upload-wrap');
                    const $relieveErrorTarget = $relieveWrap.length ? $relieveWrap : relieveInput;

                    /* Column 2 — Employment Type */
                    if (employmentType.length && (!employmentType.val() || employmentType.val().trim() === '')) {
                        employmentType.after('<span class="error-message text-danger d-block mt-1">Please select employment type.</span>');
                        if (!firstErrorField) firstErrorField = employmentType;
                        isValid = false;
                    }
                    const selectedEmploymentType = (employmentType.val() || '').trim().toLowerCase();
                    const isContractor = (selectedEmploymentType === 'electrical_contractor');
                    const isBoardMember = (selectedEmploymentType === 'board_member_tnelb');

                    /* Columns 3 & 4 — Contractor only */
                    if (isContractor) {
                        if (contractorCat.length && (contractorCat.val() || '').trim() === '') {
                            contractorCat.after('<span class="error-message text-danger d-block mt-1">Contractor category is required.</span>');
                            if (!firstErrorField) firstErrorField = contractorCat;
                            isValid = false;
                        }
                        if (licenceNumber.length && (licenceNumber.val() || '').trim() === '') {
                            licenceNumber.after('<span class="error-message text-danger d-block mt-1">Licence number is required.</span>');
                            if (!firstErrorField) firstErrorField = licenceNumber;
                            isValid = false;
                        }
                    }

                    /* Column 5 — Name of Contractor / organisation / Board */
                    if (employerInput.length && employerInput.val().trim() === '') {
                        employerInput.after('<span class="error-message text-danger d-block mt-1">Please enter Name of Contractor / organization / Board.</span>');
                        if (!firstErrorField) firstErrorField = employerInput;
                        isValid = false;
                    }

                    /* Column 6 — Organisation Address */
                    if (orgAddress.length && (orgAddress.val() || '').trim() === '') {
                        orgAddress.after('<span class="error-message text-danger d-block mt-1">Organisation address is required.</span>');
                        if (!firstErrorField) firstErrorField = orgAddress;
                        isValid = false;
                    }

                    /* Column 7 — Designation */
                    if (designation.length && designation.val().trim() === '') {
                        designation.after('<span class="error-message text-danger d-block mt-1">Designation is required.</span>');
                        if (!firstErrorField) firstErrorField = designation;
                        isValid = false;
                    }

                    /* Board Member — meeting details */
                    if (isBoardMember) {
                        const meetingDetails = $row.find('.work-board-meeting-details').first();
                        const meetingDate = $row.find('.work-board-meeting-date').first();
                        if (meetingDetails.length && (meetingDetails.val() || '').trim() === '') {
                            meetingDetails.after('<span class="error-message text-danger d-block mt-1">Details of the meeting is required.</span>');
                            if (!firstErrorField) firstErrorField = meetingDetails;
                            isValid = false;
                        }
                        if (meetingDate.length && !readWorkDateIsoGeneric(meetingDate)) {
                            meetingDate.after('<span class="error-message text-danger d-block mt-1">Date of Meeting is required.</span>');
                            if (!firstErrorField) firstErrorField = meetingDate;
                            isValid = false;
                        }
                    }

                    /* Column 8 — Nature of Work */
                    if (!isBoardMember && !natureOfWork.prop('disabled') && natureOfWork.length && (natureOfWork.val() || '').trim() === '') {
                        natureOfWork.after('<span class="error-message text-danger d-block mt-1">Nature of Work Experience is required.</span>');
                        if (!firstErrorField) firstErrorField = natureOfWork;
                        isValid = false;
                    }

                    /* Column 9 — Voltage Level */
                    const voltageVal = (voltageLevel.val() || '').trim();
                    if (!isBoardMember && !voltageLevel.prop('disabled') && voltageLevel.length && voltageVal === '') {
                        voltageLevel.after('<span class="error-message text-danger d-block mt-1">Voltage level is required.</span>');
                        if (!firstErrorField) firstErrorField = voltageLevel;
                        isValid = false;
                    }

                    /* Column 10 — Transformer kVA (required unless Voltage = Up to 650V) */
                    const kvaIsLocked = isBoardMember || (voltageVal === 'up_to_650v') || transformerKva.prop('disabled');
                    if (!kvaIsLocked && transformerKva.length && (transformerKva.val() || '').trim() === '') {
                        transformerKva.after('<span class="error-message text-danger d-block mt-1">Highest Transformer capacity (kVA) is required.</span>');
                        if (!firstErrorField) firstErrorField = transformerKva;
                        isValid = false;
                    }

                    /* Column 11 — Period of Experience (dates) */
                    if (fromDate.length && !fromIso) {
                        fromDate.after('<span class="error-message text-danger d-block mt-1">From date is required.</span>');
                        if (!firstErrorField) firstErrorField = fromDate;
                        isValid = false;
                    }
                    if (!isTillDate) {
                        if (toDate.length && !toIso) {
                            toDate.after('<span class="error-message text-danger d-block mt-1">To date is required (or tick "Till date").</span>');
                            if (!firstErrorField) firstErrorField = toDate;
                            isValid = false;
                        }
                        if (fromDate.length && toDate.length && fromIso && toIso) {
                            const from = new Date(fromIso + 'T12:00:00');
                            const to = new Date(toIso + 'T12:00:00');
                            if (!isNaN(from.getTime()) && !isNaN(to.getTime()) && to < from) {
                                showWorkExpDateRangeError($row, 'To date must be greater than or equal to From date.');
                                if (!firstErrorField) firstErrorField = toDate;
                                isValid = false;
                            }
                        }
                    }
                    /* 2-year minimum is enforced as a combined total across all rows (after this .each() loop). */

                    /* Column 12 — Supporting documents (always required for S) */
                    if (!hasSupportingDoc) {
                        $workErrorTarget.after('<span class="error-message text-danger d-block mt-1">Supporting document is required.</span>');
                        if (!firstErrorField) firstErrorField = workDocument.length ? workDocument : designation;
                        isValid = false;
                    } else if (workDocument.length && workDocument.get(0) && workDocument.get(0).files && workDocument.get(0).files.length) {
                        const file = workDocument.get(0).files[0];
                        if (file) {
                            const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                            const minSize = 5 * 1024;
                            const maxSize = 200 * 1024;
                            if (allowedTypes.indexOf(file.type) === -1) {
                                $workErrorTarget.after('<span class="error-message text-danger d-block mt-1">Only PDF, JPG or PNG files are allowed.</span>');
                                if (!firstErrorField) firstErrorField = workDocument;
                                isValid = false;
                            } else if (file.size < minSize || file.size > maxSize) {
                                $workErrorTarget.after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 200 KB.</span>');
                                if (!firstErrorField) firstErrorField = workDocument;
                                isValid = false;
                            }
                        }
                    }

                    /* Column 13 — Relieving Letter (required unless Till date or Board Member) */
                    if (!isTillDate && !isBoardMember && relieveInput.length) {
                        if (!hasRelieveFile) {
                            $relieveErrorTarget.after('<span class="error-message text-danger d-block mt-1">Relieving letter is required.</span>');
                            if (!firstErrorField) firstErrorField = relieveInput;
                            isValid = false;
                        } else {
                            const rf = relieveInput[0].files[0];
                            if (rf) {
                                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                                const minSize = 5 * 1024;
                                const maxSize = 200 * 1024;
                                if (allowedTypes.indexOf(rf.type) === -1) {
                                    $relieveErrorTarget.after('<span class="error-message text-danger d-block mt-1">Only PDF, JPG or PNG files are allowed.</span>');
                                    if (!firstErrorField) firstErrorField = relieveInput;
                                    isValid = false;
                                } else if (rf.size < minSize || rf.size > maxSize) {
                                    $relieveErrorTarget.after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 200 KB.</span>');
                                    if (!firstErrorField) firstErrorField = relieveInput;
                                    isValid = false;
                                }
                            }
                        }
                    }
                    return;
                }

                if (formName === 'W') {
                    return;
                }

                const workLevel = $(this).find('input[name="work_level[]"]');
                const experience = $(this).find('input[name="experience[]"]');
                const designation = $(this).find('input[name="designation[]"]');
                const workDocument = $(this).find('input[name="work_document[]"], input[name^="work_document["]');
                const fromDate = $(this).find('.work-date-from');
                const toDate = $(this).find('.work-date-to');
                const fromIso = readWorkDateIsoGeneric(fromDate);
                const toIso = readWorkDateIsoGeneric(toDate);

                const wl = (workLevel.val() || '').trim();
                const ex = (experience.val() || '').trim();
                const des = (designation.val() || '').trim();

                // For W/WH forms, the entire work section is optional.
                // Validate a row only if the user started filling something in that row.
                const shouldValidateRow = !workOptional || (wl !== '' || ex !== '' || des !== '' || fromIso || toIso);

                if (!shouldValidateRow) {
                    return;
                }

                if (workLevel.length && wl === "") {
                    workLevel.after('<span class="error-message text-danger d-block mt-1">Please enter the company / contractor name.</span>');
                    if (!firstErrorField) firstErrorField = workLevel;
                    isValid = false;
                }

                if (fromDate.length && !fromIso) {
                    fromDate.after('<span class="error-message text-danger d-block mt-1">From date is required.</span>');
                    if (!firstErrorField) firstErrorField = fromDate;
                    isValid = false;
                }

                if (toDate.length && !toIso) {
                    toDate.after('<span class="error-message text-danger d-block mt-1">To date is required.</span>');
                    if (!firstErrorField) firstErrorField = toDate;
                    isValid = false;
                }

                if (fromDate.length && toDate.length && fromIso && toIso) {
                    const fromD = new Date(fromIso + 'T12:00:00');
                    const toD = new Date(toIso + 'T12:00:00');
                    if (!isNaN(fromD.getTime()) && !isNaN(toD.getTime())) {
                        if (toD < fromD) {
                            showWorkExpDateRangeError($(this), 'To date must be greater than or equal to From date.');
                            if (!firstErrorField) firstErrorField = toDate;
                            isValid = false;
                        } else {
                            const minToW = new Date(fromD.getTime());
                            minToW.setFullYear(minToW.getFullYear() + 2);
                            if (toD < minToW) {
                                showWorkExpDateRangeError($(this), 'Minimum 2 Years Experience needed');
                                if (!firstErrorField) firstErrorField = toDate;
                                isValid = false;
                            }
                        }
                    }
                }

                const exNum = parseFloat(ex);
                if (experience.length && (ex === '' || isNaN(exNum) || exNum < 0 || exNum > 50)) {
                    const expMsg = (ex === '')
                        ? 'Year of experience is required.'
                        : 'Experience must be a valid number between 0 and 50 years.';
                    const $expAnchor = $(this).find('.work-exp-total-inline').first();
                    if ($expAnchor.length) {
                        $expAnchor.after('<span class="error-message text-danger d-block mt-1">' + expMsg + '</span>');
                    } else {
                        experience.after('<span class="error-message text-danger d-block mt-1">' + expMsg + '</span>');
                    }
                    if (!firstErrorField) {
                        firstErrorField = toDate.length ? toDate : (fromDate.length ? fromDate : workLevel);
                    }
                    isValid = false;
                }

                if (designation.length && des === "") {
                    designation.after('<span class="error-message text-danger d-block mt-1">Designation is required.</span>');
                    if (!firstErrorField) firstErrorField = designation;
                    isValid = false;
                }

                // For S form (non-optional work), experience document is required
                // when the row is filled. For W / WH / P, it's optional (validate only if uploaded).
                const hasFile = workDocument.length && workDocument[0].files.length > 0;
                const existingDocInput = $(this).find('input[name="existing_work_document[]"]');
                const hasExistingDoc = existingDocInput.length && (existingDocInput.val() || '').trim() !== '';
                const $workUploadWrap = workDocument.closest('.form-s-file-upload-wrap');
                const $workErrorTarget = $workUploadWrap.length ? $workUploadWrap : workDocument;

                if (!workOptional && shouldValidateRow && !hasFile && !hasExistingDoc) {
                    $workErrorTarget.after('<span class="error-message text-danger d-block mt-1">Experience document is required.</span>');
                    if (!firstErrorField) firstErrorField = workDocument.length ? workDocument : designation;
                    isValid = false;
                } else if (hasFile) {
                    const file = workDocument[0].files[0]; // ✅ use raw DOM element
                    if (file) {
                        const allowedType = 'application/pdf';
                        const minSize = 5 * 1024;   // 5 KB
                        const maxSize = 250 * 1024; // 250 KB

                        if (file.type !== allowedType) {
                            $workErrorTarget.after('<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for Experience certificate.</span>');
                            if (!firstErrorField) firstErrorField = workDocument;
                            isValid = false;
                        } else if (file.size < minSize || file.size > maxSize) {
                            $workErrorTarget.after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 200 KB.</span>');
                            if (!firstErrorField) firstErrorField = workDocument;
                            isValid = false;
                        }
                    }
                }
            });

            // Form S only: combined-total experience must be >= 2 calendar years (730 days).
            // "Till date" rows are evaluated against today's date for the duration calc.
            if (isSWorkForm) {
                var twoYearsMs = 730 * 86400000;
                var totalMs = 0;
                var anyFilled = false;
                var $firstFilledToDate = null;
                var todayIso = (function () {
                    var d = new Date();
                    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
                })();
                $('#work-container-previous .work-fields, #work-container .work-fields').each(function () {
                    var $tr = $(this);
                    var $fr = $tr.find('.work-date-from');
                    var $to = $tr.find('.work-date-to');
                    var till = $tr.find('.work-date-till').is(':checked');
                    var fIso = readWorkDateIsoGeneric($fr);
                    var tIso = till ? todayIso : readWorkDateIsoGeneric($to);
                    if (!fIso || !tIso) return;
                    var fromD = new Date(fIso + 'T12:00:00');
                    var toD = new Date(tIso + 'T12:00:00');
                    if (isNaN(fromD.getTime()) || isNaN(toD.getTime())) return;
                    if (toD < fromD) return;
                    anyFilled = true;
                    totalMs += (toD - fromD);
                    if (!$firstFilledToDate) $firstFilledToDate = $to;
                });
                if (anyFilled && totalMs < twoYearsMs) {
                    var $combinedMsg = $('#work-exp-total-msg-previous').length
                        ? $('#work-exp-total-msg-previous')
                        : $('#work-exp-total-msg');
                    if ($combinedMsg.length) {
                        $combinedMsg.html('<div class="work-exp-total-error text-danger small" role="alert">Minimum 2 Years Experience needed across all entries.</div>');
                    }
                    if (!firstErrorField && $firstFilledToDate && $firstFilledToDate.length) {
                        firstErrorField = $firstFilledToDate;
                    }
                    isValid = false;
                }
            }

            // Max length validation for competency form (S/W/WH/P) – validate all text/number fields
            if ($('#competency_form_ws').length) {
                $('#competency_form_ws').find('input[maxlength], textarea[maxlength]').each(function () {
                    var $el = $(this);
                    var max = parseInt($el.attr('maxlength'), 10);
                    if (isNaN(max)) return;
                    var val = ($el.val() || '').trim();
                    if (val.length > max) {
                        $el.after('<span class="error-message text-danger d-block mt-1">Maximum ' + max + ' characters allowed.</span>');
                        if (!firstErrorField) firstErrorField = $el;
                        isValid = false;
                    }
                });
                $('#competency_form_ws').find('input[type="number"][max]').each(function () {
                    var $el = $(this);
                    var maxVal = parseInt($el.attr('max'), 10);
                    var val = $el.val();
                    if (val !== '' && !isNaN(maxVal) && parseInt(val, 10) > maxVal) {
                        $el.after('<span class="error-message text-danger d-block mt-1">Value cannot exceed ' + maxVal + '.</span>');
                        if (!firstErrorField) firstErrorField = $el;
                        isValid = false;
                    }
                });
                $('#competency_form_ws').find('input[type="number"][min]').each(function () {
                    var $el = $(this);
                    var minVal = parseInt($el.attr('min'), 10);
                    var val = $el.val();
                    if (val !== '' && !isNaN(minVal) && parseInt(val, 10) < minVal) {
                        $el.after('<span class="error-message text-danger d-block mt-1">Value cannot be less than ' + minVal + '.</span>');
                        if (!firstErrorField) firstErrorField = $el;
                        isValid = false;
                    }
                });
            }

            // Same max length / min-max validation for Form P
            if ($('#competency_form_p').length) {
                $('#competency_form_p').find('input[maxlength], textarea[maxlength]').each(function () {
                    var $el = $(this);
                    var max = parseInt($el.attr('maxlength'), 10);
                    if (isNaN(max)) return;
                    var val = ($el.val() || '').trim();
                    if (val.length > max) {
                        $el.after('<span class="error-message text-danger d-block mt-1">Maximum ' + max + ' characters allowed.</span>');
                        if (!firstErrorField) firstErrorField = $el;
                        isValid = false;
                    }
                });
                $('#competency_form_p').find('input[type="number"][max]').each(function () {
                    var $el = $(this);
                    var maxVal = parseInt($el.attr('max'), 10);
                    var val = $el.val();
                    if (val !== '' && !isNaN(maxVal) && parseInt(val, 10) > maxVal) {
                        $el.after('<span class="error-message text-danger d-block mt-1">Value cannot exceed ' + maxVal + '.</span>');
                        if (!firstErrorField) firstErrorField = $el;
                        isValid = false;
                    }
                });
                $('#competency_form_p').find('input[type="number"][min]').each(function () {
                    var $el = $(this);
                    var minVal = parseInt($el.attr('min'), 10);
                    var val = $el.val();
                    if (val !== '' && !isNaN(minVal) && parseInt(val, 10) < minVal) {
                        $el.after('<span class="error-message text-danger d-block mt-1">Value cannot be less than ' + minVal + '.</span>');
                        if (!firstErrorField) firstErrorField = $el;
                        isValid = false;
                    }
                });
            }

            let aadhaarInput = document.getElementById("aadhaar");
            let aadhaarError = document.getElementById("aadhaar-error");
            if (aadhaarInput && aadhaarError) {
                const aadhaar = aadhaarInput.value.replace(/\s+/g, '').trim();
                const aadhaarRegex = /^[2-9]{1}[0-9]{11}$/;
                if (aadhaar === "") {
                    aadhaarError.textContent = "Aadhaar number is required.";
                    if (!firstErrorField) firstErrorField = $(aadhaarInput);
                    isValid = false;
                } else if (!aadhaarRegex.test(aadhaar)) {
                    aadhaarError.textContent = "Please enter a valid 12-digit Aadhaar number (should not start with 0 or 1).";
                    if (!firstErrorField) firstErrorField = $(aadhaarInput);
                    isValid = false;
                } else {
                    aadhaarError.textContent = "";
                }
            }

            const formNameUpper = ($('#form_name').val() || '').toString().toUpperCase();
            const competencyFormNames = ['S', 'W', 'WH', 'P'];
            if (competencyFormNames.includes(formNameUpper)) {
                const panEl = document.getElementById('pancard');
                const panErr = document.getElementById('pancard-error');
                const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]$/;
                if (panEl) {
                    const pv = (panEl.value || '').replace(/\s+/g, '').toUpperCase();
                    if (pv === '') {
                        if (panErr) panErr.textContent = '';
                    } else if (!panRegex.test(pv)) {
                        if (panErr) panErr.textContent = 'Enter a valid 10-character PAN (e.g. ABCDE1234F).';
                        if (!firstErrorField) firstErrorField = $(panEl);
                        isValid = false;
                    } else if (panErr) {
                        panErr.textContent = '';
                    }
                }
                const panDoc = document.getElementById('pancard_doc');
                if (panDoc && $(panDoc).is(':visible')) {
                    $(panDoc).nextAll('.error-message').remove();
                    if (panDoc.files.length > 0) {
                        const file = panDoc.files[0];
                        if (file) {
                            const allowedType = 'application/pdf';
                            const maxSize = 250 * 1024;
                            if (file.type !== allowedType) {
                                $('#pancard_doc').after('<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for PAN document.</span>');
                                if (!firstErrorField) firstErrorField = $('#pancard_doc');
                                isValid = false;
                            } else if (file.size > maxSize) {
                                $('#pancard_doc').after('<span class="error-message text-danger d-block mt-1">File size permitted only up to 250 KB.</span>');
                                if (!firstErrorField) firstErrorField = $('#pancard_doc');
                                isValid = false;
                            }
                        }
                    }
                }
            }

            let aadhaarFileInput = document.getElementById("aadhaar_doc");
            if (aadhaarFileInput && $(aadhaarFileInput).is(":visible")) {
                if (aadhaarFileInput && aadhaarFileInput.files.length === 0) {
                    $('#aadhaar_doc').after('<span class="error-message text-danger d-block mt-1">Aadhaar document upload is required.</span>');
                    if (!firstErrorField) firstErrorField = $('#aadhaar_doc');
                    isValid = false;
                } else if (aadhaarFileInput && aadhaarFileInput.files.length > 0) {
                    const file = aadhaarFileInput.files[0];
                    if (file) {
                        const allowedType = 'application/pdf';
                        const maxSize = 250 * 1024;
                        if (file.type !== allowedType) {
                            $('#aadhaar_doc').after('<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for Aadhaar document.</span>');
                            if (!firstErrorField) firstErrorField = $('#aadhaar_doc');
                            isValid = false;
                        } else if (file.size > maxSize) {
                            $('#aadhaar_doc').after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 250.</span>');
                            if (!firstErrorField) firstErrorField = $('#aadhaar_doc');
                            isValid = false;
                        }
                    }
                }
            }

            if (!$('#declarationCheckbox').is(':checked')) {
                $('#checkboxError').removeClass('d-none');
                if (!firstErrorField) firstErrorField = $('#checkboxError');
                isValid = false;
            } else {
                $('#checkboxError').addClass('d-none');
            }


            let photoInput = document.getElementById("upload_photo");
            const previewPhoto = document.getElementById("preview_applicant");
            const hasExistingPhotoPreview = !!(
                previewPhoto &&
                String(previewPhoto.getAttribute('src') || '').trim() !== '' &&
                $(previewPhoto).is(':visible')
            );

            if (photoInput && $(photoInput).is(':visible') && photoInput.files.length === 0 && !hasExistingPhotoPreview) {
                $(photoInput).nextAll('.error-message').remove();
                $('#upload_photo').after('<span class="error-message text-danger d-block mt-1">Photo upload is required.</span>');
                if (!firstErrorField) firstErrorField = $('#upload_photo');
                isValid = false;
            } else if (photoInput && photoInput.files.length > 0) {
                const file = photoInput.files[0];
                if (file) {
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/pjpeg', 'image/x-png'];
                    const allowedExts = ['jpg', 'jpeg', 'png'];
                    const maxSize = 50 * 1024;
                    const fileName = String(file.name || '').toLowerCase();
                    const fileExt = fileName.includes('.') ? fileName.split('.').pop() : '';
                    const hasAllowedMime = allowedTypes.includes(String(file.type || '').toLowerCase());
                    const hasAllowedExt = allowedExts.includes(String(fileExt || '').toLowerCase());
                    if (!hasAllowedMime && !hasAllowedExt) {
                        $('#upload_photo').after('<span class="error-message text-danger d-block mt-1">Only JPG, JPEG, or PNG images are allowed for photo upload.</span>');
                        if (!firstErrorField) firstErrorField = $('#upload_photo');
                        isValid = false;
                    } else if (file.size > maxSize) {
                        const sizeKb = (file.size / 1024).toFixed(1);
                        $('#upload_photo').after('<span class="error-message text-danger d-block mt-1">Photo size is ' + sizeKb + ' KB. Allowed: up to 50 KB.</span>');
                        if (!firstErrorField) firstErrorField = $('#upload_photo');
                        isValid = false;
                    }
                }
            }

            // Signature validation (required only when marked required)
            let signInput = document.getElementById("upload_sign");
            if (signInput && $(signInput).is(':visible')) {
                $(signInput).nextAll('.error-message').remove();
                const isRequiredSign = signInput.hasAttribute('required');

                if (isRequiredSign && signInput.files.length === 0) {
                    $('#upload_sign').after('<span class="error-message text-danger d-block mt-1">Signature upload is required.</span>');
                    if (!firstErrorField) firstErrorField = $('#upload_sign');
                    isValid = false;
                } else if (signInput.files.length > 0) {
                    const sfile = signInput.files[0];
                    if (sfile) {
                        const sAllowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/pjpeg', 'image/x-png'];
                        const sAllowedExts = ['jpg', 'jpeg', 'png'];
                        const sMaxSize = 50 * 1024;
                        const sFileName = String(sfile.name || '').toLowerCase();
                        const sFileExt = sFileName.includes('.') ? sFileName.split('.').pop() : '';
                        const sHasAllowedMime = sAllowedTypes.includes(String(sfile.type || '').toLowerCase());
                        const sHasAllowedExt = sAllowedExts.includes(String(sFileExt || '').toLowerCase());
                        if (!sHasAllowedMime && !sHasAllowedExt) {
                            $('#upload_sign').after('<span class="error-message text-danger d-block mt-1">Only JPG, JPEG, or PNG images are allowed for signature upload.</span>');
                            if (!firstErrorField) firstErrorField = $('#upload_sign');
                            isValid = false;
                        } else if (sfile.size > sMaxSize) {
                            const sSizeKb = (sfile.size / 1024).toFixed(1);
                            $('#upload_sign').after('<span class="error-message text-danger d-block mt-1">Signature size is ' + sSizeKb + ' KB. Allowed: up to 50 KB.</span>');
                            if (!firstErrorField) firstErrorField = $('#upload_sign');
                            isValid = false;
                        }
                    }
                }
            }

            if (!isValid) {
                try {
                    const $first = (firstErrorField && firstErrorField.length) ? firstErrorField.first() : $();
                    const fieldId = $first.attr('id') || '';
                    const fieldName = $first.attr('name') || '';
                    let firstMessage = '';
                    if ($first.hasClass('error-message')) {
                        firstMessage = ($first.text() || '').trim();
                    }
                    if (!firstMessage) {
                        firstMessage = ($first.nextAll('.error-message:visible').first().text() || '').trim();
                    }
                    if (!firstMessage) {
                        firstMessage = ($first.siblings('.error-message:visible').first().text() || '').trim();
                    }
                } catch (e) {
                    // no-op: keep legacy scroll behaviour
                }
                scrollCompetencyToValidationError(firstErrorField);
                $submitBtn.data('isProcessing', false).prop('disabled', false).html(originalSubmitLabel);
                return;
            }

            // Persist to DB first, so preview document links are available consistently.
            if (typeof window.wxSyncBoardMemberRenewalFee === 'function' && !isDigitizationApplType()) {
                await window.wxSyncBoardMemberRenewalFee();
            }
            const draftSaved = await saveCompetencyDraftSilently();
            if (!draftSaved || draftSaved.status !== "success") {
                $submitBtn.data('isProcessing', false).prop('disabled', false).html(originalSubmitLabel);
                return;
            }

            const previewConfirmed = await showCompetencyPreviewModal();
            if (!previewConfirmed) {
                $submitBtn.data('isProcessing', false).prop('disabled', false).html(originalSubmitLabel);
                return;
            }

            let license_name = $("#license_name").val();
            if (isNoPaymentApplType()) {
                $('#amount').val('0');
            }
            showDeclarationPopup(license_name, true);
            $submitBtn.data('isProcessing', false).prop('disabled', false).html(originalSubmitLabel);
        });

        function clearWorkUploadErrorMessages($scope) {
            if (!$scope || !$scope.length) return;
            $scope.find('.error-message').each(function () {
                var txt = ($(this).text() || '').toLowerCase();
                if (
                    txt.indexOf('supporting document is required') !== -1 ||
                    txt.indexOf('relieving letter is required') !== -1 ||
                    txt.indexOf('highest transformer capacity') !== -1 ||
                    txt.indexOf('education certificate upload is required') !== -1 ||
                    txt.indexOf('experience document is required') !== -1 ||
                    txt.indexOf('only pdf') !== -1 ||
                    txt.indexOf('file size permitted') !== -1
                ) {
                    $(this).remove();
                }
            });
        }

        // Clear row-level upload-required errors immediately on file change
        // (so user doesn't need to click submit again to clear old message)
        $(document).on('change', 'input[type="file"][name="education_document[]"], input[type="file"][name^="education_document["], input[type="file"][name="work_document[]"], input[type="file"][name^="work_document["], input[type="file"][name="work_relieving_letter[]"], input[type="file"][name^="work_relieving_letter["]', function () {
            var $input = $(this);
            var $wrap = $input.closest('.form-s-file-upload-wrap');
            var $target = $wrap.length ? $wrap : $input;
            var $row = $input.closest('tr, .education-fields, .work-fields');

            clearWorkUploadErrorMessages($target);
            clearWorkUploadErrorMessages($row);
        });

        $(document).on('change', '#work-container .work-voltage', function () {
            var $row = $(this).closest('.work-fields');
            $row.find('.work-transformer-kva').nextAll('.error-message').remove();
            $row.find('.work-card-field[data-field="transformer-kva"] .error-message').remove();
        });



        $("#aadhaar").on("input", function() {
            let value = $(this).val();

            // Remove all spaces from masked input
            const digitsOnly = value.replace(/\s+/g, '');

            // Validate Aadhaar: must be 12 digits starting with 2–9
            if (digitsOnly.length === 14) {
                if (/^[2-9]{1}[0-9]{11}$/.test(digitsOnly)) {
                    $("#aadhaar-error").text(""); // ✅ Valid Aadhaar
                    $("#aadhaar_error").text("");

                } else {
                    $("#aadhaar-error").text("Enter valid Aadhaar Number.");
                    $("#aadhaar_error").text("Enter valid Aadhaar Number.");
                }
            } else {
                $("#aadhaar-error").text("");
                $("#aadhaar_error").text("");
            }
        });

        $(document).ready(function() {
            const aadhaarInput = document.getElementById("aadhaar_doc");
            const panInput = document.getElementById("pancard_doc")

            if (aadhaarInput) {
                aadhaarInput.addEventListener("change", function() {
                    const aadhaarError = aadhaarInput.parentElement.querySelector(
                        ".error-message");

                    if (this.files.length !== 0 && aadhaarError) {
                        aadhaarError.remove();
                    }
                });
            }

            // PAN document removed
        });

        $(document).on('input change blur', '#applicant_email', function () {
            var ev = readApplicantEmailValue();
            var formNameEmail = ($('#form_name').val() || '').toString().trim().toUpperCase();
            var emailRequired = formNameEmail === 'S';
            if (!emailRequired) {
                clearCompetencyFieldError($(this));
                return;
            }
            if (ev === '') {
                return;
            }
            if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(ev)) {
                clearCompetencyFieldError($(this));
            }
        });

        $(document).on('input change blur', '#Applicant_Name, #Fathers_Name', function () {
            if (($(this).val() || '').trim() !== '') {
                clearCompetencyFieldError($(this));
            }
        });

        $(document).on('keyup change', '#education-container .education-fields input, #education-container .education-fields select',
        function() {
            const $field = $(this);
            if ($field.val().trim() !== '') {
                $field.nextAll('.error-message').first().remove();
                if ($field.hasClass('certificate-input')) {
                    $field.closest('td').find('.certificate-error').text('');
                    $field.removeClass('is-invalid');
                }
                $field.closest('.work-fields').find('.error-message').filter(function() {
                    return $(this).text().includes(
                        "Please fill in at least one field");
                }).remove();
            }
        });
        
        // Clear Certificate No error styling when the user focuses the field,
        // so validation does not appear to be "triggered" just by clicking in/out.
        $(document).on('focus', '.certificate-input', function () {
            const $field = $(this);
            $field.closest('td').find('.certificate-error').text('');
            $field.removeClass('is-invalid');
        });
    

        /** Form S only: From/To work dates — live check for date order and minimum 2 years (matches Pay validation). */
        function readWorkDateIsoFormS($input) {
            return readWorkDateIsoGeneric($input);
        }

        /* Form S, WH — From/To only: date order + minimum 2 calendar years (matches Pay / server). WH: only when the row is partially filled. Form W: no client work-date rules.
           Form S supports a "Till date" checkbox on the To-date that suppresses the To-date input. */
        $(document).on('change blur input', '#work-container .work-fields .work-date-from, #work-container .work-fields .work-date-to, #work-container .work-fields .work-date-till', function (e) {
            var formName = String($('#form_name').val() || '').trim().toUpperCase();
            if (formName !== 'S' && formName !== 'WH') {
                return;
            }
            var $row = $(this).closest('.work-fields');
            var $fromDate = $row.find('.work-date-from');
            var $toDate = $row.find('.work-date-to');
            var isTillDate = formName === 'S' && $row.find('.work-date-till').is(':checked');

            /* Form S: work-exp partial owns date validation; avoid clearing the message on every keystroke. */
            if (formName === 'S') {
                if (typeof window.wxRecalcWorkDuration === 'function') {
                    window.wxRecalcWorkDuration($row);
                }
                if (e.type !== 'input' && typeof window.wxValidateWorkRowDateRange === 'function') {
                    window.wxValidateWorkRowDateRange($row);
                }
                if (typeof window.wxUpdateOverallWorkYears === 'function') {
                    window.wxUpdateOverallWorkYears();
                }
                return;
            }

            if (formName === 'WH') {
                var wl0 = ($row.find('input[name="work_level[]"]').val() || '').trim();
                var ex0 = ($row.find('input[name="experience[]"]').val() || '').trim();
                var des0 = ($row.find('input[name="designation[]"]').val() || '').trim();
                var fi0 = readWorkDateIsoFormS($fromDate);
                var ti0 = readWorkDateIsoFormS($toDate);
                if (!(wl0 !== '' || ex0 !== '' || des0 !== '' || fi0 !== '' || ti0 !== '')) {
                    clearWorkExpDateRangeError($row);
                    return;
                }
            }

            clearWorkExpDateRangeError($row);

            var fromIso = readWorkDateIsoFormS($fromDate);
            var toIso;
            if (isTillDate) {
                var today = new Date();
                toIso = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-' + String(today.getDate()).padStart(2, '0');
            } else {
                toIso = readWorkDateIsoFormS($toDate);
            }
            if (fromIso) {
                clearWorkDateRequiredErrors($fromDate);
            }
            if (toIso || isTillDate) {
                clearWorkDateRequiredErrors($toDate);
            }
            if (typeof window.wxRecalcWorkDuration === 'function') {
                window.wxRecalcWorkDuration($row);
            }
            if (!fromIso || !toIso) {
                return;
            }

            var from = new Date(fromIso + 'T12:00:00');
            var to = new Date(toIso + 'T12:00:00');
            if (isNaN(from.getTime()) || isNaN(to.getTime())) {
                return;
            }

            if (to < from) {
                showWorkExpDateRangeError($row, 'To date must be greater than or equal to From date.');
                return;
            }
            if (formName === 'WH') {
                var minTo = new Date(from.getTime());
                minTo.setFullYear(minTo.getFullYear() + 2);
                if (to < minTo) {
                    showWorkExpDateRangeError($row, 'Minimum 2 Years Experience needed');
                }
            }
        });

        $(document).on('keyup change', '#work-container .work-fields input, #work-container .work-fields select',
            function () {
                const $field = $(this);
                if ($field.is('.work-date-from, .work-date-to')) {
                    var iso = readWorkDateIsoFormS($field);
                    if (iso) {
                        $field.get(0).setAttribute('data-raw', iso);
                        clearWorkDateRequiredErrors($field);
                    }
                    return;
                }
                if ($field.val().trim() !== '') {
                    $field.nextAll('.error-message').first().remove();
                    $field.closest('.work-fields').find('.error-message').filter(function () {
                        return $(this).text().includes('Please fill in at least one field');
                    }).remove();
                }
            });
        // -----------------fathers name Validation-------------

        let isValid = true;
        let firstErrorField = null;

        // Block numbers and special characters during typing
        $("#Fathers_Name").on("keypress", function(e) {
            let char = String.fromCharCode(e.which);
            if (!/^[a-zA-Z\s]$/.test(char)) {
                e.preventDefault();
            }
        });

        // Validate input on change
        $("#Fathers_Name").on("input", function() {
            $(".error-message", this.parentElement).remove(); // Clear previous error

            let fathersName = $(this).val().trim();
            let nameRegex = /^[A-Za-z\s]+$/;

            if (!nameRegex.test(fathersName)) {
                if (!firstErrorField) firstErrorField = $(this);
                isValid = false;
            }
        });

        // --------------------End------------


        $("#upload_photo").on("input change", function() {
            const $field = $(this);

            if ($field.val()) {
                $field.nextAll('.error-message').first().remove();
            }

            // Simple photo preview if a preview element is present on the page
            const previewEl = document.getElementById('photo_preview');
            if (!previewEl) return;

            const input = this;
            if (!input.files || !input.files[0]) {
                previewEl.style.display = 'none';
                previewEl.src = '';
                return;
            }

            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                previewEl.src = e.target.result;
                previewEl.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });

        $("#upload_sign").on("input change", function() {
            const $field = $(this);
            if ($field.val()) {
                $field.nextAll('.error-message').first().remove();
            }

            // Simple signature preview if a preview element is present on the page
            const previewEl = document.getElementById('sign_preview');
            if (!previewEl) return;

            const input = this;
            if (!input.files || !input.files[0]) {
                previewEl.style.display = 'none';
                previewEl.src = '';
                return;
            }

            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function (e) {
                previewEl.src = e.target.result;
                previewEl.style.display = 'block';
            };
            reader.readAsDataURL(file);
        });


        $('#closePopup').on('click', function() {
            $('#pdfPopup').fadeOut(function() {
                window.location.href = "{{ route('dashboard') }}";
            });
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



    //    $(document).on("change", "#ownership_type_select", function () {

    //     let type = $(this).val();

    //     // 🔹 Reset ALL file inputs inside both sections
    //     $("#partnershipdeed input[type='file'], #directormom input[type='file']").val("");

    //     // 🔹 Clear file preview / link div
    //     $("#partnershipdeed .file-link, #directormom .file-link .ownershipdoc_upload_error")
    //         .html("")
    //         .addClass("d-none");
    //         // <span class="text-danger ownershipdoc_upload_error"></span>


    //     // 🔹 Clear hidden fields if any (file name / path)
    //     // $("#partnershipdeed input[type='hidden'], #directormom input[type='hidden']").val("");

    //     // 🔹 Hide both sections first
    //     $("#partnershipdeed, #directormom").slideUp();

    //     // 🔹 Show based on selection
    //     if (type === 'pt') {
    //         $("#partnershipdeed").slideDown();
    //     } 
    //     else if (type === 'pvt' || type === 'ltd') {
    //         $("#directormom").slideDown();
    //     }
    // });



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

            let data = null;
            if (!noPaymentApplType) {
                data = await getPaymentsService(licence_code, issued_licence, appl_type);
            }

            if (!noPaymentApplType && !data) {
                Swal.fire("Error", "Unable to load payment details. Please try again.", "error");
                return;
            }

            if (noPaymentApplType) {
                $('#amount').val('0');
            }

            const boardMemberFeeExempt = !noPaymentApplType
                && ($('#form_name').val() || '').trim().toUpperCase() === 'S'
                && ['N', 'R'].includes(String(appl_type || '').trim().toUpperCase())
                && typeof window.wxHasBoardMemberWorkRow === 'function'
                && window.wxHasBoardMemberWorkRow();

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
                        ? 'No fee (Board Member — fee not applicable)'
                        : ('Rs.' + actual_fees + '/-'));
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
                formData.set('form_action', 'draft');
                let applicationId = $('#application_id').val();
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

                        // Zero-fee paths (digitization / alteration / board member) — submit directly
                        if (feeExemptSubmit) {
                            try {
                                const paid = await runCompetencyPayment();
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
                            } catch (err) {
                                Swal.fire({
                                    title: noPaymentApplType ? 'Submission Failed' : 'Payment Failed',
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
        $("#ps_transactionId_competency").text(transactionId);
        $("#ps_transactionDate_competency").text(transactionDate);
        $("#ps_amount_competency").text(amount);

        // Digitisation (D) and Alteration (A): no payment UI. New (N) / Renewal (R): full payment success.
        if (isFeeExemptSubmit) {
            $modal.find("#ps_success_modal_title").text("Application Submitted Successfully!");
            $modal.find(".ps-payment-only").addClass("d-none");
            $modal.find(".ps-transaction-date-label").text("Submission Date:");
            $modal.find(".ps-app-pdf-heading").removeClass("mt-3");
        } else {
            $modal.find("#ps_success_modal_title").text("Payment Successful!");
            $modal.find(".ps-payment-only").removeClass("d-none");
            $modal.find(".ps-transaction-date-label").text("Transaction Date:");
            $modal.find(".ps-app-pdf-heading").addClass("mt-3");
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


  


    function paymentreceipt() {
        if (!window.paymentAppId) {
            alert("Application ID not found!");
            return;
        }
        window.open(`${BASE_URL}/payment-receipt/${window.paymentAppId}`, "_blank");
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




    // ---------------formA staff license check----------------------


    // $(document).on('change blur', '.cc_number, .cc_validity', function() {
    //     const $row = $(this).closest('.staff-fields');
    //     const licenseNumber = $row.find('.cc_number').val().trim();
    //     const date = $row.find('.cc_validity').val().trim();
    //     const resultBox = $row.find('.competency_verify_result');

    //     if (!licenseNumber || !date) {
    //         resultBox.text('⚠ Enter certificate number and validity date.');
    //         return;
    //     }

    //     resultBox.html(`<span class="text-info">Verifying...</span>`);


    //     $.ajax({
    //         url: "{{ route('verifylicenseformAcc') }}",
    //         method: 'POST',
    //         data: {
    //             license_number: licenseNumber,
    //             date: date,
    //             _token: $('meta[name="csrf-token"]').attr('content')
    //         },
    //         success: function(response) {
    //             if (response.exists) {
    //                 resultBox.html('<span class="text-success">&#10004; License verified.</span>');
    //             } else {
    //                 resultBox.html('<span class="text-danger">&#10060; License not found.</span>');
    //             }
    //         },
    //         error: function(xhr) {
    //             resultBox.html(
    //                 '<span class="text-danger">🚫 Error verifying license. Try again.</span>');
    //             console.error(xhr.responseText);
    //         }
    //     });
    // });


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

    // --------------------------------5--------------------------------

    // $('#previous_application_number, #previous_application_validity').on('change blur', function() {
    //     const licenseNumber = $('#previous_application_number').val().trim();
    //     const date = $('#previous_application_validity').val().trim();
    //     const resultBox = $('#verifyea_result');

    //     if (!licenseNumber || !date) {
    //         resultBox.text('⚠ Enter license number and date.');
    //         return;
    //     }

    //     resultBox.html(`<span class="text-info">Verifying...</span>`);


    //     $.ajax({
    //         url: "{{ route('verifylicenseformAea') }}",
    //         method: 'POST',
    //         data: {
    //             license_number: licenseNumber,
    //             date: date,
    //             _token: $('meta[name="csrf-token"]').attr('content')
    //         },
    //         success: function(response) {
    //             if (response.exists) {
    //                 resultBox.html('<span class="text-success">&#10004; License verified.</span>');
    //             } else {
    //                 resultBox.html('<span class="text-danger">&#10060; License not found.</span>');
    //             }
    //         },
    //         error: function(xhr) {
    //             resultBox.html(
    //                 '<span class="text-danger">🚫 Error verifying license. Try again.</span>');
    //             console.error(xhr.responseText);
    //         }
    //     });
    // });


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
