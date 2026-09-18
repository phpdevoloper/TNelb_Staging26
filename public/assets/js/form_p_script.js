async function showInstructPopup(licence_code,login_id) {

    try {

        let total_fees, lateFee, lateMonths;

        const appl_type = $('#appl_type').val();
        const issued_licence = $('#license_number').val();

        const data = await getPaymentsService(licence_code, issued_licence, appl_type);

        if (data) {
            if (data.lateFees < 0) {
                actual_fees = data.basic_fees;
                total_fees = data.total_fees;
                lateMonths = data.late_months;
            } else {
                actual_fees = data.basic_fees;
                lateMonths = data.late_months;
                total_fees = data.total_fees;
                lateFee = data.lateFees;
            }
        }

            let formData = new FormData($('#competency_form_p')[0]);
            formData.delete('month_passing[]');
            $('#competency_form_p select[name="month_of_passing[]"]').each(function () {
                formData.append('month_passing[]', $(this).val() || '');
            });
            let applicationId = $('#application_id').val();
            let formUrl;

            if (applicationId) {
                // if (appl_type === 'R') {
                //     formUrl = "{{ route('form.draft_renewal_submit', ['appl_id' => '__APPL_ID__']) }}"
                //         .replace('__APPL_ID__', applicationId);
                // } else {
                formUrl = BASE_URL + "/form_p/update";
                    // }
            } else {
                formUrl = BASE_URL + "/form_p/store";
            }


            console.log(formUrl);
            
            
            try {

                // 🔹 Submit form
                let saveResponse = await $.ajax({
                    url: formUrl,
                    type: "POST",
                    data: formData,
                    dataType: "json",
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

                    // Backend already sends form_type as "FRESH" or "RENEWAL" – use that,
                    // and fall back to appl_type-based inference if missing.
                    let form_type = saveResponse.form_type || (appl_type === 'R' ? 'RENEWAL' : 'FRESH');
                    const application_id = saveResponse.application_id;
                    const transactionDate = saveResponse.date_apps;
                    const applicantName = saveResponse.applicantName || 'N/A';
                    const type_apps = saveResponse.type_of_apps || 'N/A';
                    const form_name = saveResponse.form_name || 'N/A';
                    const amount = total_fees;
                    const licence_name = saveResponse.licence_name || 'N/A';

                
                    let lateFeeRow = "";
                    if (lateFee > 0) {
                        lateFeeRow = `
                                <tr>
                                    <th style="text-align: left; padding: 6px 10px; color: #555;">Late Fees (${lateMonths} Months)</th>
                                    <td style="text-align: right; padding: 6px 10px; font-weight: 500;">Rs. ${lateFee} </td>
                                </tr>`;
                    }


                    const formTypeLabel = form_type === 'FRESH' || form_type === 'New Application'
                        ? 'New Application'
                        : (form_type === 'RENEWAL' || form_type === 'Renewal Application'
                            ? 'Renewal Application'
                            : form_type);

                    // 🔹 Show payment popup then open PayU (same as Form S/W/WH)
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
                                            <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${formTypeLabel}</td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; padding: 6px 10px; color: #555;">Date</th>
                                                <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${transactionDate}</td>
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
                                const cfg = window.COMPETENCY_FORM_CONFIG || {};
                                const payuWin = window.open('', 'tnelb_payu_gateway');
                                if (!payuWin) {
                                    throw new Error('Please allow pop-ups for this site to open the payment window.');
                                }

                                const form = document.createElement('form');
                                form.method = 'POST';
                                form.action = cfg.payuInitiateUrl;
                                form.target = 'tnelb_payu_gateway';

                                const fields = {
                                    _token: cfg.csrfToken || $('meta[name="csrf-token"]').attr('content'),
                                    application_id: application_id,
                                    amount: amount ?? total_fees ?? 0,
                                    actual_fees: actual_fees ?? 0,
                                    lateFee: lateFee ?? 0,
                                    lateMonths: lateMonths ?? 0,
                                };

                                Object.keys(fields).forEach((name) => {
                                    const input = document.createElement('input');
                                    input.type = 'hidden';
                                    input.name = name;
                                    input.value = fields[name];
                                    form.appendChild(input);
                                });

                                document.body.appendChild(form);
                                form.submit();
                                form.remove();

                                if (typeof window.watchPayUPaymentProgress === 'function') {
                                    window.watchPayUPaymentProgress({
                                        applicationId: application_id,
                                        applicantName: applicantName,
                                        amount: amount ?? total_fees ?? 0,
                                        formType: formTypeLabel,
                                        licenceName: licence_name,
                                        transactionDate: transactionDate,
                                        payuWin: payuWin,
                                        isFormP: true,
                                    });
                                }
                                return false;
                            } catch (err) {
                                Swal.showValidationMessage(
                                    err.message || 'Payment failed. You can click Pay Now to try again.'
                                );
                                return false;
                            }
                        }

                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.cancel) {
                            Swal.fire({
                                title: "Payment Failed!",
                                text: "Application Saved as Draft",
                                icon: "error",
                                timer: 3000, // Auto close in 3 seconds
                                timerProgressBar: true
                            }).then(() => {
                                window.location.href = "/dashboard";
                            }); // your redirect URL
                        }
                    });
                } else {
                    Swal.fire("Form Submission Failed", "Application not submitted", "error");
                }
            } catch (xhr) {

                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    const errors = xhr.responseJSON.errors;

                    // Remove any old error labels
                    $('.server-error').remove();
                    $('.is-invalid').removeClass('is-invalid');

                    $.each(errors, function (field, messages) {

                        let input;

                        // Handle array fields (field.0, field.1 etc.)
                        if (field.includes('.')) {
                            const baseField = field.split('.')[0];
                            input = $('[name="' + baseField + '[]"]');
                        } else {
                            input = $('[name="' + field + '"]');
                        }

                        if (input.length) {
                            input.addClass('is-invalid');

                            // Prevent duplicate messages
                            if (!input.next('.server-error').length) {
                                input.after(
                                    '<span class="text-danger server-error d-block mt-1">' +
                                    messages[0] +
                                    '</span>'
                                );
                            }
                        }
                    });

                    Swal.fire({
                        icon: "warning",
                        title: "Validation Error",
                        text: "Please correct the highlighted fields."
                    });
                    return;
                }
            }
    } catch (err) {
        console.error("Error fetching form cost or saving form:", err);

        console.error("❌ Uncaught AJAX Error:", xhr);

        // Check if Laravel validation failed (422)
        if (xhr.status === 422 && xhr.responseJSON?.errors) {
            // You can show validation messages here
            $.each(xhr.responseJSON.errors, function (key, msg) {
                console.log(key, msg);
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Request Failed",
                text: window.getAjaxErrorMessage(xhr)
            });
        }
    }
}

function parseFormPInstituteIsoDate(value) {
    var raw = (value || '').trim();
    if (!/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
        return null;
    }
    var parsed = new Date(raw + 'T12:00:00');
    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

function calculateFormPInstituteDuration(fromDate, toDate) {
    var from = parseFormPInstituteIsoDate(fromDate);
    var to = parseFormPInstituteIsoDate(toDate);
    if (!from || !to || to < from) {
        return '';
    }
    var years = to.getFullYear() - from.getFullYear();
    var months = to.getMonth() - from.getMonth();
    if (to.getDate() < from.getDate()) {
        months -= 1;
    }
    if (months < 0) {
        years -= 1;
        months += 12;
    }
    if (years < 0) {
        return '';
    }
    return years + '.' + months;
}

function showFormPInstituteFieldError($field, message, firstErrorField) {
    $field.after('<span class="error-message text-danger d-block mt-1">' + message + '</span>');
    return firstErrorField || $field;
}

/**
 * @param {boolean} required  true = payment/submit (full row); false = draft (dates optional unless one is filled)
 */
function validateFormPInstituteDateRows(required) {
    var isValid = true;
    var firstErrorField = null;
    var $rows = $('#institute-container .institute-fields');

    if (required && $rows.length === 0) {
        $('#institute-table').after('<span class="error-message text-danger d-block mt-1">At least one institute entry is required.</span>');
        return { isValid: false, firstErrorField: $('#institute-table') };
    }

    var completeRows = 0;
    $rows.each(function () {
        var $row = $(this);
        var $name = $row.find('textarea[name="institute_name_address[]"]');
        var $from = $row.find('input[name="from_date[]"]');
        var $to = $row.find('input[name="to_date[]"]');
        var $duration = $row.find('input[name="duration[]"]');
        var nameVal = ($name.val() || '').trim();
        var fromVal = ($from.val() || '').trim();
        var toVal = ($to.val() || '').trim();
        var rowStarted = nameVal !== '' || fromVal !== '' || toVal !== '';

        if (!rowStarted) {
            return;
        }

        if (required && nameVal === '') {
            firstErrorField = showFormPInstituteFieldError($name, 'Institute name and address is required.', firstErrorField);
            isValid = false;
        }

        var mustCheckDates = required || fromVal !== '' || toVal !== '';
        if (!mustCheckDates) {
            return;
        }

        if (fromVal === '') {
            firstErrorField = showFormPInstituteFieldError($from, 'From date is required.', firstErrorField);
            isValid = false;
        }
        if (toVal === '') {
            firstErrorField = showFormPInstituteFieldError($to, 'To date is required.', firstErrorField);
            isValid = false;
        }

        var fromDate = fromVal ? parseFormPInstituteIsoDate(fromVal) : null;
        var toDate = toVal ? parseFormPInstituteIsoDate(toVal) : null;
        if (fromVal !== '' && !fromDate) {
            firstErrorField = showFormPInstituteFieldError($from, 'From date is required.', firstErrorField);
            isValid = false;
        }
        if (toVal !== '' && !toDate) {
            firstErrorField = showFormPInstituteFieldError($to, 'To date is required.', firstErrorField);
            isValid = false;
        }
        if (fromDate && toDate && toDate < fromDate) {
            firstErrorField = showFormPInstituteFieldError($to, 'To date must be greater than or equal to From date.', firstErrorField);
            isValid = false;
            $duration.val('');
            return;
        }
        if (fromDate && toDate) {
            var duration = calculateFormPInstituteDuration(fromVal, toVal);
            $duration.val(duration);
            var years = parseInt((duration.split('.')[0] || ''), 10);
            if (required && (Number.isNaN(years) || years < 0 || years > 50)) {
                firstErrorField = showFormPInstituteFieldError($duration, 'Duration must be between 0 and 50 years.', firstErrorField);
                isValid = false;
            } else {
                completeRows += 1;
            }
        }
    });

    if (required && completeRows === 0 && isValid) {
        $('#institute-table').after('<span class="error-message text-danger d-block mt-1">Please add at least one institute entry with From Date and To Date.</span>');
        firstErrorField = firstErrorField || $('#institute-table');
        isValid = false;
    }

    return { isValid: isValid, firstErrorField: firstErrorField };
}

function validateFormPWorkDateRows(required) {
    var isValid = true;
    var firstErrorField = null;

    $('#work-container .work-fields').each(function () {
        var $row = $(this);
        var $from = $row.find('input[name="work_date_from[]"], .work-date-from').first();
        var $to = $row.find('input[name="work_date_to[]"], .work-date-to').first();
        var wl = ($row.find('input[name="work_level[]"]').val() || '').trim();
        var des = ($row.find('input[name="designation[]"]').val() || '').trim();
        var ex = ($row.find('input[name="experience[]"]').val() || '').trim();
        var fromVal = ($from.val() || '').trim();
        var toVal = ($to.val() || '').trim();
        var rowStarted = wl !== '' || des !== '' || ex !== '' || fromVal !== '' || toVal !== '';
        if (!rowStarted) {
            return;
        }

        var mustCheckDates = required || fromVal !== '' || toVal !== '';
        if (!mustCheckDates) {
            return;
        }

        if (fromVal === '') {
            firstErrorField = showFormPInstituteFieldError($from, 'From date is required.', firstErrorField);
            isValid = false;
        }
        if (toVal === '') {
            firstErrorField = showFormPInstituteFieldError($to, 'To date is required.', firstErrorField);
            isValid = false;
        }

        var fromDate = fromVal ? parseFormPInstituteIsoDate(fromVal) : null;
        var toDate = toVal ? parseFormPInstituteIsoDate(toVal) : null;
        if (fromVal !== '' && !fromDate) {
            firstErrorField = showFormPInstituteFieldError($from, 'From date is required.', firstErrorField);
            isValid = false;
        }
        if (toVal !== '' && !toDate) {
            firstErrorField = showFormPInstituteFieldError($to, 'To date is required.', firstErrorField);
            isValid = false;
        }
        if (fromDate && toDate && toDate < fromDate) {
            firstErrorField = showFormPInstituteFieldError($to, 'To date must be greater than or equal to From date.', firstErrorField);
            isValid = false;
        }
    });

    return { isValid: isValid, firstErrorField: firstErrorField };
}

// Proceed for Payment
$(document).ready(function () {
    $(document).on('change', '#competency_form_p input[name="from_date[]"], #competency_form_p input[name="to_date[]"]', function () {
        var $row = $(this).closest('.institute-fields');
        if (!$row.length) {
            return;
        }
        var $from = $row.find('input[name="from_date[]"]');
        var $to = $row.find('input[name="to_date[]"]');
        $from.next('.error-message').remove();
        $to.next('.error-message').remove();
        var fromVal = ($from.val() || '').trim();
        var toVal = ($to.val() || '').trim();
        $row.find('input[name="duration[]"]').val(calculateFormPInstituteDuration(fromVal, toVal));
        var fromDate = parseFormPInstituteIsoDate(fromVal);
        var toDate = parseFormPInstituteIsoDate(toVal);
        if (fromDate && toDate && toDate < fromDate) {
            showFormPInstituteFieldError($to, 'To date must be greater than or equal to From date.', null);
        }
    });

    $(document).on('change', '#competency_form_p .work-date-from, #competency_form_p .work-date-to', function () {
        var $row = $(this).closest('.work-fields');
        if (!$row.length) {
            return;
        }
        var $from = $row.find('.work-date-from').first();
        var $to = $row.find('.work-date-to').first();
        $from.next('.error-message').remove();
        $to.next('.error-message').remove();
        var fromDate = parseFormPInstituteIsoDate(($from.val() || '').trim());
        var toDate = parseFormPInstituteIsoDate(($to.val() || '').trim());
        if (fromDate && toDate && toDate < fromDate) {
            showFormPInstituteFieldError($to, 'To date must be greater than or equal to From date.', null);
        }
    });

    $(document).on('click', '#ProceedtoPayment', async function (e) {
        if (!$('#competency_form_p').length) return;
        e.preventDefault();

        $('.error-message').remove();
        let isValid = true;
        let firstErrorField = null;

        let dobEl = $('#d_o_b');
        if (dobEl.length && dobEl.val() === "") {
            let errorMsg = $('<span class="error-message text-danger d-block mt-1">Date of Birth is required.</span>');
            dobEl.after(errorMsg);
            if (!firstErrorField) firstErrorField = dobEl;
            isValid = false;
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
            let yearOfPassing = $(this).find('select[name="year_of_passing[]"]');
            let percentage = $(this).find('input[name="percentage[]"]');
            let educationUpload = $(this).find('input[name="education_document[]"]');

            if (eduLevel.length && (eduLevel.val() === null || eduLevel.val() === "")) {
                eduLevel.after('<span class="error-message text-danger d-block mt-1">Education level is required.</span>');
                if (!firstErrorField) firstErrorField = eduLevel;
                isValid = false;
            }

            if (instituteName.length && instituteName.val().trim() === "") {
                instituteName.after('<span class="error-message text-danger d-block mt-1">Institution name is required.</span>');
                if (!firstErrorField) firstErrorField = instituteName;
                isValid = false;
            }

            if (yearOfPassing.length && (yearOfPassing.val() === "0" || yearOfPassing.val() === "")) {
                yearOfPassing.after('<span class="error-message text-danger d-block mt-1">Year of passing is required.</span>');
                if (!firstErrorField) firstErrorField = yearOfPassing;
                isValid = false;
            }

            if (percentage.length && (percentage.val().trim() === "" || isNaN(percentage.val()) || percentage.val() < 0 || percentage.val() > 100)) {
                percentage.after('<span class="error-message text-danger d-block mt-1">Percentage / Grade is required</span>');
                if (!firstErrorField) firstErrorField = percentage;
                isValid = false;
            }

            if (educationUpload.length && educationUpload.val() === "") {
                educationUpload.after('<span class="error-message text-danger d-block mt-1">Education certificate upload is required.</span>');
                if (!firstErrorField) firstErrorField = educationUpload;
                isValid = false;
            } else if (educationUpload.length && educationUpload[0].files.length > 0) {
                const file = educationUpload[0].files[0]; // ✅ use raw DOM element
                if (file) {
                    const allowedType = 'application/pdf';
                    const minSize = 5 * 1024;   // 5 KB
                    const maxSize = 250 * 1024; // 250 KB

                    if (file.type !== allowedType) {
                        educationUpload.after('<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for Education upload.</span>');
                        if (!firstErrorField) firstErrorField = educationUpload;
                        isValid = false;
                    } else if (file.size < minSize || file.size > maxSize) {
                        educationUpload.after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 200 KB.</span>');
                        if (!firstErrorField) firstErrorField = educationUpload;
                        isValid = false;
                    }
                }
            }
        });

        // Power Station (work) section is optional – no required validation; only validate file type/size when a file is uploaded
        $('#work-container .work-fields').each(function () {
            let workDocument = $(this).find('input[name="work_document[]"]');

            if (workDocument.length && workDocument[0].files.length > 0) {
                const file = workDocument[0].files[0];
                if (file) {
                    const allowedType = 'application/pdf';
                    const minSize = 5 * 1024;   // 5 KB
                    const maxSize = 250 * 1024; // 250 KB

                    if (file.type !== allowedType) {
                        workDocument.after('<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for Experience certificate.</span>');
                        if (!firstErrorField) firstErrorField = workDocument;
                        isValid = false;
                    } else if (file.size < minSize || file.size > maxSize) {
                        workDocument.after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 200 KB.</span>');
                        if (!firstErrorField) firstErrorField = workDocument;
                        isValid = false;
                    }
                }
            }
        });


        var instituteDateCheck = validateFormPInstituteDateRows(true);
        if (!instituteDateCheck.isValid) {
            isValid = false;
            if (!firstErrorField) {
                firstErrorField = instituteDateCheck.firstErrorField;
            }
        }
        var workDateCheck = validateFormPWorkDateRows(true);
        if (!workDateCheck.isValid) {
            isValid = false;
            if (!firstErrorField) {
                firstErrorField = workDateCheck.firstErrorField;
            }
        }

        $('#institute-container .institute-fields').each(function () {
            let $row = $(this);
            let instituteDocument = $row.find('input[name="institute_document[]"], input[name^="institute_document["]').filter(':visible').first();
            let hasExistingDoc = $row.find('.fs-doc-existing').length > 0;

            if (!hasExistingDoc && instituteDocument.length && instituteDocument.val().trim() === "") {
                instituteDocument.after('<span class="error-message text-danger d-block mt-1">Institute upload is required.</span>');
                if (!firstErrorField) firstErrorField = instituteDocument;
                isValid = false;
            } else if (instituteDocument.length && instituteDocument[0].files.length > 0) {
                const file = instituteDocument[0].files[0];
                if (file) {
                    const allowedType = 'application/pdf';
                    const minSize = 5 * 1024;
                    const maxSize = 250 * 1024;

                    if (file.type !== allowedType) {
                        instituteDocument.after('<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for Institute upload.</span>');
                        if (!firstErrorField) firstErrorField = instituteDocument;
                        isValid = false;
                    } else if (file.size < minSize || file.size > maxSize) {
                        instituteDocument.after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 200 KB.</span>');
                        if (!firstErrorField) firstErrorField = instituteDocument;
                        isValid = false;
                    }
                }
            }
        });


        // Employer name is optional – no required validation

        console.log(isValid);

        

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

        let pancardInput = document.getElementById("pancard");
        let pancardError = document.getElementById("pancard-error");
        if (pancardInput && pancardError) {
            const pancardValue = pancardInput.value.replace(/\s+/g, '').toUpperCase();
            const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]$/;
            if (pancardValue === "") {
                pancardError.textContent = "";
            } else if (!panRegex.test(pancardValue)) {
                pancardError.textContent = "Enter a valid 10-character PAN (e.g. ABCDE1234F).";
                if (!firstErrorField) firstErrorField = $(pancardInput);
                isValid = false;
            } else {
                pancardError.textContent = "";
            }
        }

        let pancardDocInput = document.getElementById("pancard_doc");

        if (pancardDocInput && pancardDocInput.files.length > 0) {
            const file = pancardDocInput.files[0];
            if (file) {
                const allowedType = 'application/pdf';
                const maxSize = 250 * 1024;
                if (file.type !== allowedType) {
                    $('#pancard_doc').after('<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for PAN document.</span>');
                    if (!firstErrorField) firstErrorField = $('#pancard_doc');
                    isValid = false;
                } else if (file.size > maxSize) {
                    $('#pancard_doc').after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 250 KB.</span>');
                    if (!firstErrorField) firstErrorField = $('#pancard_doc');
                    isValid = false;
                }
            }
        }


        if (!$('#declarationCheckbox').is(':checked')) {
            $('#checkboxError').show();
            if (!firstErrorField) firstErrorField = $('#checkboxError');
            isValid = false;
        } else {
            $('#checkboxError').hide();
        }


        let photoInput = document.getElementById("upload_photo");

        if (photoInput && $(photoInput).is(':visible') && photoInput.files.length === 0) {
            $(photoInput).nextAll('.error-message').remove();
            $('#upload_photo').after('<span class="error-message text-danger d-block mt-1">Photo upload is required.</span>');
            if (!firstErrorField) firstErrorField = $('#upload_photo');
            isValid = false;
        } else if (photoInput && photoInput.files.length > 0) {
            const file = photoInput.files[0];
            if (file) {
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                const maxSize = 50 * 1024;
                if (!allowedTypes.includes(file.type)) {
                    $('#upload_photo').after('<span class="error-message text-danger d-block mt-1">Only JPG, JPEG, or PNG images are allowed for photo upload.</span>');
                    if (!firstErrorField) firstErrorField = $('#upload_photo');
                    isValid = false;
                } else if (file.size > maxSize) {
                    $('#upload_photo').after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 50 KB.</span>');
                    if (!firstErrorField) firstErrorField = $('#upload_photo');
                    isValid = false;
                }
            }
        }

        // Signature validation for Form P
        let signInput = document.getElementById("upload_sign");
        if (signInput && $(signInput).is(':visible')) {
            $(signInput).nextAll('.error-message').remove();
            const isRequiredSign = signInput.hasAttribute('required');

            // Base rule: required only when input has required attribute (new application)
            if (isRequiredSign && signInput.files.length === 0) {
                $('#upload_sign').after('<span class="error-message text-danger d-block mt-1">Signature upload is required.</span>');
                if (!firstErrorField) firstErrorField = $('#upload_sign');
                isValid = false;
            }

            // Additional rule for returned applications: if query reason says "Signature is missing"
            if (window.returnApplicationQueryReasons) {
                const reasons = window.returnApplicationQueryReasons || [];
                if (reasons.indexOf('Signature is missing') !== -1) {
                    const signWrapper = document.getElementById('sign-input-wrapper');
                    const hasSign = (signInput.files && signInput.files.length > 0) ||
                        (signWrapper && signWrapper.style.display === 'none');
                    if (!hasSign) {
                        $('#upload_sign').after('<span class="error-message text-danger d-block mt-1">Please upload Signature.</span>');
                        if (!firstErrorField) firstErrorField = $('#upload_sign');
                        isValid = false;
                    }
                }
            }

            if (signInput.files.length > 0) {
                const sfile = signInput.files[0];
                if (sfile) {
                    const sAllowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                    const sMaxSize = 50 * 1024;
                    if (!sAllowedTypes.includes(sfile.type)) {
                        $('#upload_sign').after('<span class="error-message text-danger d-block mt-1">Only JPG, JPEG, or PNG images are allowed for signature upload.</span>');
                        if (!firstErrorField) firstErrorField = $('#upload_sign');
                        isValid = false;
                    } else if (sfile.size > sMaxSize) {
                        $('#upload_sign').after('<span class="error-message text-danger d-block mt-1">Signature file size permitted only 5 KB to 50 KB.</span>');
                        if (!firstErrorField) firstErrorField = $('#upload_sign');
                        isValid = false;
                    }
                }
            }
        }
        if (!isValid && firstErrorField) {
            $('html, body').animate({ scrollTop: firstErrorField.offset().top - 100 }, 500);
            return;
        }

        if (typeof window.saveCompetencyDraftSilently === 'function' && $('#competency_form_p').length) {
            const draftSaved = await window.saveCompetencyDraftSilently();
            if (!draftSaved || draftSaved.status !== "success") {
                return;
            }
        }

        let license_name = $("#license_name").val();
        let login_id = $("#login_id_store").val();

        if (typeof window.showCompetencyPreviewModal === 'function') {
            const previewConfirmed = await window.showCompetencyPreviewModal();
            if (!previewConfirmed) return;
        }

        showInstructPopup(license_name,login_id);
    });



    $("#pancard").on("keyup", function () {
        let value = $(this).val().toUpperCase();

        if (value.length > 10) {
            value = value.slice(0, 10);
        }

        $(this).val(value);

        if (value === "") {
            $("#pancard-error").text("");
        } else if (/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(value)) {
            $("#pancard-error").text("");
        } else {
            $("#pancard-error").text("Enter valid 10-character PAN (e.g., ABCDE1234F).");
        }
    });

    $("#aadhaar").on("input", function () {
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

    $(document).ready(function () {
        const aadhaarInput = document.getElementById("aadhaar_doc");
        const panInput = document.getElementById("pancard_doc")

        if (aadhaarInput) {
            aadhaarInput.addEventListener("change", function () {
                const aadhaarError = aadhaarInput.parentElement.querySelector(
                    ".error-message");

                if (this.files.length !== 0 && aadhaarError) {
                    aadhaarError.remove();
                }
            });
        }

        if (panInput) {
            panInput.addEventListener("change", function () {
                const panError = panInput.parentElement.querySelector(".error-message");

                if (this.files.length !== 0 && panError) {
                    panError.remove();
                }
            });
        }
    });

    $(document).on('keyup change', '#education-container .education-fields input, #education-container .education-fields select',
        function () {
            const $field = $(this);
            if ($field.val().trim() !== '') {
                $field.nextAll('.error-message').first().remove();
                $field.closest('.work-fields').find('.error-message').filter(function () {
                    return $(this).text().includes(
                        "Please fill in at least one field");
                }).remove();
            }
        });


    $(document).on('keyup change', '#work-container .work-fields input, #work-container .work-fields select',
        function () {
            const $field = $(this);
            if ($field.val().trim() !== '') {
                $field.nextAll('.error-message').first().remove();
                $field.closest('.work-fields').find('.error-message').filter(function () {
                    return $(this).text().includes("Please fill in at least one field");
                }).remove();
            }
        });


    $(document).on('keyup change', '#institute-container .institute-fields input, #institute-container .institute-fields select, #institute-container .institute-fields textarea',
        function () {
            const $field = $(this);
            if ($field.val().trim() !== '') {
                $field.nextAll('.error-message').first().remove();
                $field.closest('.institute-fields').find('.error-message').filter(function () {
                    return $(this).text().includes("Please fill in at least one field");
                }).remove();
            }
        });
    // -----------------fathers name Validation-------------

    let isValid = true;
    let firstErrorField = null;

    // Block numbers and special characters during typing
    $("#employer_name").on("input", function () {

        // Clear previous error message
        $(this).siblings(".error-message").remove();

        let employer_name = $(this).val().trim();
        let nameRegex = /^[A-Za-z\s.]+$/; // Adds dot support

        // Only validate format when user has entered something (field is optional)
        if (employer_name !== "" && !nameRegex.test(employer_name)) {
            $(this).after('<span class="error-message text-danger d-block mt-1">Enter a valid Employer Name.</span>');
            if (!firstErrorField) firstErrorField = $(this);
            isValid = false;
        }
    });

    // Validate input on change
    $("#Fathers_Name").on("input", function () {
        $(".error-message", this.parentElement).remove(); // Clear previous error

        let fathersName = $(this).val().trim();
        let nameRegex = /^[A-Za-z\s]+$/;

        if (!nameRegex.test(fathersName)) {
            if (!firstErrorField) firstErrorField = $(this);
            isValid = false;
        }
    });

    // --------------------End------------


    $("#upload_photo").on("input change", function () {
        const $field = $(this);

        if ($field.val()) {
            $field.nextAll('.error-message').first().remove();
        }
    });



    $('#closePopup').on('click', function () {
        $('#pdfPopup').fadeOut(function () {
            window.location.href = "{{ route('dashboard') }}";
        });
    });


    // Preview for photo and signature (new application Form P)
    $('#upload_photo').on('change', function (e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                const img = document.getElementById('photo_preview');
                if (img) {
                    img.src = ev.target.result;
                    img.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        }
    });

    $('#upload_sign').on('change', function (e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function (ev) {
                const img = document.getElementById('sign_preview');
                if (img) {
                    img.src = ev.target.result;
                    img.style.display = 'block';
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // Save As Draft or Submit Returned Form P
    $(document).on('click', '#DraftBtn, #saveDraftBtn', function(e) {
        if (!$('#competency_form_p').length) return;
        e.preventDefault();

        $('.error-message').remove(); 
        let isValid = true;
        let firstErrorField = null; 

        let nameRegex = /^[A-Za-z\s]+$/;
        let dob = $('#d_o_b').val();

        // Validate Father's Name
        let fathersName = $('#Fathers_Name').val().trim();
        if (fathersName === "") {
            let errorMsg = $(
                '<span class="error-message text-danger d-block mt-1">Father\'s Name is required.</span>'
            );
            $('#Fathers_Name').after(errorMsg);
            if (!firstErrorField) firstErrorField = $('#Fathers_Name');
            isValid = false;
        } else if (!nameRegex.test(fathersName)) {
            let errorMsg = $(
                '<span class="error-message text-danger d-block mt-1">Only alphabets and spaces are allowed.</span>'
            );
            $('#Fathers_Name').after(errorMsg);
            if (!firstErrorField) firstErrorField = $('#Fathers_Name');
            isValid = false;
        }


        if (dob === "") {
            let errorMsg = $(
                '<span class="error-message text-danger d-block mt-1">Date of Birth is required.</span>'
            );
            $('#d_o_b').after(errorMsg);
            if (!firstErrorField) firstErrorField = errorMsg;
            isValid = false;
        }

        $('#education-container .education-fields').each(function () {

            let educationUpload = $(this).find('input[type="file"][name^="education_document["]');

            if (educationUpload.length && educationUpload[0].files.length > 0) {
                const file = educationUpload[0].files[0]; // ✅ use raw DOM element
                if (file) {
                    const allowedType = 'application/pdf';
                    const minSize = 5 * 1024;   // 5 KB
                    const maxSize = 250 * 1024; // 250 KB

                    if (file.type !== allowedType) {
                        educationUpload.after('<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for Education upload.</span>');
                        if (!firstErrorField) firstErrorField = educationUpload;
                        isValid = false;
                    } else if (file.size < minSize || file.size > maxSize) {
                        educationUpload.after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 200 KB.</span>');
                        if (!firstErrorField) firstErrorField = educationUpload;
                        isValid = false;
                    }
                }
            }
        });

        $('#work-container .work-fields').each(function () {
            let workDocument = $(this).find('input[type="file"][name^="work_document["]');

           if (workDocument.length && workDocument[0].files.length > 0) {
                const file = workDocument[0].files[0]; // ✅ use raw DOM element
                if (file) {
                    const allowedType = 'application/pdf';
                    const minSize = 5 * 1024;   // 5 KB
                    const maxSize = 250 * 1024; // 250 KB

                    if (file.type !== allowedType) {
                        workDocument.after('<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for Experience certificate.</span>');
                        if (!firstErrorField) firstErrorField = workDocument;
                        isValid = false;
                    } else if (file.size < minSize || file.size > maxSize) {
                        workDocument.after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 200 KB.</span>');
                        if (!firstErrorField) firstErrorField = workDocument;
                        isValid = false;
                    }
                }
            }
        });

        var instituteDraftDateCheck = validateFormPInstituteDateRows(false);
        if (!instituteDraftDateCheck.isValid) {
            isValid = false;
            if (!firstErrorField) {
                firstErrorField = instituteDraftDateCheck.firstErrorField;
            }
        }
        var workDraftDateCheck = validateFormPWorkDateRows(false);
        if (!workDraftDateCheck.isValid) {
            isValid = false;
            if (!firstErrorField) {
                firstErrorField = workDraftDateCheck.firstErrorField;
            }
        }

        let licenseError = document.getElementById("licenseError");
        let dateError = document.getElementById("dateError");

        if (licenseError) {
            licenseError.textContent = '';
        }
        if (dateError) {
            dateError.textContent = '';
        }

        $("#pancard-error").text("");
        $("#checkboxError").text("");


        // Validate Date of Birth

        const aadhaarInput = document.getElementById("aadhaar");
        const aadhaarError = document.getElementById("aadhaar-error");

        if (aadhaarInput) {
            const aadhaar = aadhaarInput.value.replace(/\s+/g, '').trim();
            const aadhaarRegex = /^[2-9]{1}[0-9]{11}$/;

            if (aadhaar !== '' && !aadhaarRegex.test(aadhaar)) {
                if (aadhaarError) {
                    aadhaarError.textContent =
                        "Please enter a valid 12-digit Aadhaar number (should not start with 0 or 1).";
                }
                if (!firstErrorField) firstErrorField = $(aadhaarInput);
                isValid = false;
            } else if (aadhaarError) {
                aadhaarError.textContent = "";
            }
        }

        let aadhaarFileInput = $('#aadhaar_doc')[0];
        if (aadhaarFileInput) {
            if (aadhaarFileInput.files.length !== 0) {
                const file = aadhaarFileInput.files[0];
                const allowedType = 'application/pdf';
                const maxSize = 250 * 1024; // 250 KB

                if (file.type !== allowedType) {
                    let errorMsg = $(
                        '<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for Aadhaar document.</span>'
                    );
                    $('#aadhaar_doc').after(errorMsg);
                    if (!firstErrorField) firstErrorField = $('#aadhaar_doc');
                    isValid = false;
                } else if (file.size > maxSize) {
                    let errorMsg = $(
                        '<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 250 KB.</span>'
                    );
                    $('#aadhaar_doc').after(errorMsg);
                    if (!firstErrorField) firstErrorField = $('#aadhaar_doc');
                    isValid = false;
                }
            }
        }

        const pancardInput = document.getElementById("pancard");
        const pancardError = document.getElementById("pancard-error");
        const pancardValue = pancardInput ? pancardInput.value.replace(/\s+/g, '').toUpperCase() : "";
        const panRegex = /^[A-Z]{5}[0-9]{4}[A-Z]$/;
        if (pancardInput && pancardError && pancardValue !== "" && !panRegex.test(pancardValue)) {
            pancardError.textContent = "Enter a valid 10-character PAN (e.g. ABCDE1234F).";
            if (!firstErrorField) firstErrorField = $(pancardInput);
            isValid = false;
        } else if (pancardError) {
            pancardError.textContent = "";
        }

        const pancardDocInput = document.getElementById("pancard_doc");
        
        if (pancardDocInput && pancardDocInput.files.length > 0) {
            const file = pancardDocInput.files[0];
            if (file) {
                const allowedType = 'application/pdf';
                const maxSize = 250 * 1024;
                if (file.type !== allowedType) {
                    $('#pancard_doc').after('<span class="error-message text-danger d-block mt-1">Only PDF files are allowed for PAN document.</span>');
                    if (!firstErrorField) firstErrorField = $('#pancard_doc');
                    isValid = false;
                } else if (file.size > maxSize) {
                    $('#pancard_doc').after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 250 KB.</span>');
                    if (!firstErrorField) firstErrorField = $('#pancard_doc');
                    isValid = false;
                }
            }
        }

        let photoInput = document.getElementById("upload_photo");
        
        if (photoInput && photoInput.files.length > 0) {
            const file = photoInput.files[0];
            if (file) {
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                const maxSize = 50 * 1024;
                if (!allowedTypes.includes(file.type)) {
                    $('#upload_photo').after('<span class="error-message text-danger d-block mt-1">Only JPG, JPEG, or PNG images are allowed for photo upload.</span>');
                    if (!firstErrorField) firstErrorField = $('#upload_photo');
                    isValid = false;
                } else if (file.size > maxSize) {
                    $('#upload_photo').after('<span class="error-message text-danger d-block mt-1">File size permitted only 5 KB to 50 KB.</span>');
                    if (!firstErrorField) firstErrorField = $('#upload_photo');
                    isValid = false;
                }
            }
        }

        if (!isValid) {

            $('html, body').animate({
                scrollTop: firstErrorField.offset().top - 100
            }, 500);

            return; 

        } else {
            let applicationId = $('#application_id').val();

            let applType = $('#appl_type').val();
            let formData = new FormData($('#competency_form_p')[0]);
            formData.delete('month_passing[]');
            $('#competency_form_p select[name="month_of_passing[]"]').each(function () {
                formData.append('month_passing[]', $(this).val() || '');
            });

            // For normal applications, this button saves as draft.
            // For returned Form P (app_status = QU), it submits corrections.
            var isReturnedFormP = (typeof window.isReturnedFormP !== 'undefined') && window.isReturnedFormP;
            let url;

            if (isReturnedFormP) {
                url = BASE_URL + "/form_p/submit_returned/" + applicationId;
            } else {
                formData.append('form_action', 'draft');

                if (applType === "R") {
                    url = BASE_URL + "/form_p/draft_renewal_submit/" + encodeURIComponent(applicationId);
                } else {
                    // New application draft submit route
                    url = BASE_URL + "/form_p/saveDraft";
                }
            }

            // let url = $(this).data("url");

            // if (applicationId) {
            //     url += "/" + applicationId;
            // }

            formData.append('application_id', applicationId);

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
                    if (response.status == 'success') {
                        if (response.application_id) {
                            $('#application_id').val(response.application_id);
                        }
                        if (isReturnedFormP) {
                            Swal.fire({
                                title: 'Application Submitted',
                                html: 'Your Application ID: <strong>' + (response.application_id || applicationId) + '</strong>',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = (response.redirect || (BASE_URL + '/dashboard'));
                            });
                        } else {
                            Swal.fire({
                                title: 'Application Saved As Draft!',
                                html: 'Your Application ID: <strong>' + response.application_id + '</strong>',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.href = BASE_URL +'/dashboard'; // change as needed
                            });
                        }

                    } else {
                        Swal.fire("Failed", isReturnedFormP ? "Corrections submit failed" : "Application not saved as draft", "error");
                    }

                },
                error: function(xhr) {
                    if (xhr.responseJSON && xhr.responseJSON.errors) {

                        // First clear previous errors
                        $('.text-danger.server-error').remove();
                        $('.is-invalid').removeClass('is-invalid');

                        // Loop through errors  

                        $.each(xhr.responseJSON.errors, function (fieldName, messages) {

                            console.log(fieldName);
                            console.log(messages);
                            // Support dot notation (like education_document.0)
                            let fieldSelector = fieldName.replace(/\./g, '\\.'); // escape dot for jQuery

                            // Try to find the input by name
                            let field = $(`[name="${fieldName}"]`);
                            if (field.length === 0) {
                                // If not found directly, try fallback (use name starts with for array fields)
                                field = $(`[name^="${fieldName.split('.')[0]}"]`).eq(parseInt(fieldName.split('.')[1]));
                            }

                            // Add error message if input found
                            if (field.length) {
                                field.addClass('is-invalid');

                                // Append the error message after the input
                                field.after(`<span class="text-danger server-error">${messages[0]}</span>`);
                            }
                        });

                        let firstInvalid = $('.is-invalid').first();
                        if (firstInvalid.length) {
                            $('html, body').animate({
                                scrollTop: firstInvalid.offset().top - 100 // Adjust offset as needed
                            }, 500);
                        }
                    

                    } else {
                        Swal.fire("Error", window.getAjaxErrorMessage(xhr), "error");
                    }
                }
            });

        }
    });



});
