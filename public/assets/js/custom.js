$(document).ready(function () {

    // License verfication for FORM -w , FORM - WH ----------------------

   const regexRules = {
        license:     /^\d+$/,                      // unchanged
        certificate: /^(\d+|CB\d{11})$/i,          // 123213  or  CB20260900003
        supervisor:  /^(\d+|CC\d{11})$/i,          // 123213  or  CC20260900010
        helper:      /^(\d+|CH\d{11})$/i,          // 123213  or  CH20260900010
    };

    // ✅ Toggle Yes/No for all sections
    $(document).on("change", ".toggle-details", function () {
        const target = $(this).data("target");
        if ($(this).val() === "yes") {
            $(target).show();
        } else {
            $(target).hide()
                     .find("input[type=text], input[type=date]").val("");
            $(target).find("span.text-danger, span.text-success").text("");
        }
    });

    // Form S certificate numbers (Q8 supervisor and Q9 wireman = 1 to 5 digits, no C/W prefix).
    // Only inputs that carry data-cert-digits are checked here; Form W/WH/P inputs keep the old behaviour.
    const FS_CERT_INVALID_MSG = "Invalid certificate number";
    const FS_CERT_REQUIRED_MSG = "Certificate Number is required";
    window.FS_CERT_REQUIRED_MSG = FS_CERT_REQUIRED_MSG;
    window.FS_CERT_INVALID_MSG = FS_CERT_INVALID_MSG;

    function fsCertDigits($input) {
        const n = parseInt($input.attr("data-cert-digits"), 10);
        return isNaN(n) ? 0 : n;
    }

    function fsCertNumberIsValid(value, digits) {
        // `digits` is the MAX length; 1..digits numeric characters are valid.
        return /^[0-9]+$/.test(value) && value.length >= 1 && value.length <= digits;
    }

    /* True when the Yes option that opens this input's panel is selected (or the input has no Yes/No toggle). */
    function fsCertYesSelected($input) {
        const panelId = $input.closest(".fs-toggle-panel").attr("id");
        if (!panelId) return true;
        const $yes = $('input.toggle-details[data-target="#' + panelId + '"][value="yes"]');
        if (!$yes.length) return true;
        return $yes.is(":checked");
    }

    /**
     * Validates every Form S certificate number input (data-cert-digits) inside $scope.
     * Writes the inline red message into the input's data-error span.
     * Returns the first invalid input (jQuery) or null when all are valid.
     */
    function fsValidateCertNumbers($scope) {
        let $first = null;
        ($scope && $scope.length ? $scope : $(document))
            .find("input.verify-input[data-cert-digits]")
            .each(function () {
                const $el = $(this);
                const $err = $($el.data("error"));
                if ($el.prop("disabled") || $el.prop("readonly") || $el.closest(".fs-return-section-locked").length) {
                    return;
                }
                if (!fsCertYesSelected($el)) {
                    $err.text("");
                    return;
                }
                const value = ($el.val() || "").trim();
                let message = "";
                if (value === "") {
                    message = FS_CERT_REQUIRED_MSG;
                } else if (!fsCertNumberIsValid(value, fsCertDigits($el))) {
                    message = FS_CERT_INVALID_MSG;
                }
                $err.text(message);
                if (message && !$first) $first = $el;
            });
        return $first;
    }
    window.fsValidateCertNumbers = fsValidateCertNumbers;

    $(document).on("blur", "input.verify-input[data-cert-digits]", function () {
        const $el = $(this);
        const value = ($el.val() || "").trim();
        if (value !== "" && !fsCertNumberIsValid(value, fsCertDigits($el))) {
            $($el.data("error")).text(FS_CERT_INVALID_MSG);
        }
    });

    // ✅ Common Keyup Validation
    $(document).on("input", ".verify-input", function () {

        const certDigits = fsCertDigits($(this));
        if (certDigits) {
            // Form S: do not strip silently; a prefix such as "C123"/"W123" is shown as invalid.
            const certValue = ($(this).val() || "").trim();
            const $certErr = $($(this).data("error"));
            $($(this).data("msg")).text("");
            if (certValue === "") {
                $certErr.text(FS_CERT_REQUIRED_MSG);
            } else if (!/^[0-9]+$/.test(certValue) || certValue.length > certDigits) {
                $certErr.text(FS_CERT_INVALID_MSG);
            } else {
                // Any 1..max digit number is valid.
                $certErr.text("");
            }
            return;
        }

        let value = ($(this).val() || "").replace(/\D/g, "");
        $(this).val(value);

        let type = $(this).data("type");

        let errorBox = $($(this).data("error"));
        let msgBox = $($(this).data("msg"));

        errorBox.text("");
        msgBox.text("");

        if (value === "") {
            errorBox.text(FS_CERT_REQUIRED_MSG);
            return;
        }

        if (!regexRules[type].test(value)) {
            errorBox.text("Number should contain only digits");
        }
    });

    // ✅ Common Date Validation
    $(document).on("change", ".verify-date", function () {
        let value = $(this).val()?.trim() || "";
        let errorBox = $($(this).data("error"));
        errorBox.text(value === "" ? "Date of Expiry is required" : "");
    });

    // ✅ Common Date of First Issue Validation
    $(document).on("change", ".verify-issue-date", function () {
        let value = $(this).val()?.trim() || "";
        let errorBox = $($(this).data("error"));
        errorBox.text(value === "" ? "Date of First Issue is required" : "");
    });

    // ✅ When Yes/No toggles hide, clear Date of First Issue error too
    $(document).on("change", ".toggle-details", function () {
        if ($(this).val() !== "yes") {
            let target = $(this).data("target");
            $(target).find(".verify-issue-date, .verify-valid-from, .verify-date").val("");
        }
    });

    /* ------------------------------------------------------------------
     * Form S Q8 (#previously_details) / Q9 (#wireman_details) basic date rules.
     *   Date of First Issue : real date, 1900-01-01 .. today
     *   From date           : real date, >= Date of First Issue, <= today
     *   To date             : real date, > From date, <= 2099-12-31
     * Only active when #form_name is S (W/WH/P share the classes but are left untouched).
     * Empty values are not reported here (the "is required" handlers own that).
     * ------------------------------------------------------------------ */
    const FS_DATE_SECTIONS = "#previously_details, #wireman_details";
    const FS_DATE_MIN = "1900-01-01";
    const FS_DATE_MAX_TO = "2099-12-31";

    function fsIsFormS() {
        return String($("#form_name").val() || "").trim().toUpperCase() === "S";
    }

    function fsTodayIso() {
        const t = new Date();
        return t.getFullYear() + "-" + String(t.getMonth() + 1).padStart(2, "0") + "-" + String(t.getDate()).padStart(2, "0");
    }

    function fsAddDaysIso(iso, days) {
        const m = iso.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!m) return "";
        const d = new Date(Date.UTC(+m[1], +m[2] - 1, +m[3] + days));
        return d.getUTCFullYear() + "-" + String(d.getUTCMonth() + 1).padStart(2, "0") + "-" + String(d.getUTCDate()).padStart(2, "0");
    }

    /* Returns {empty:true} | {valid:false} | {valid:true, iso}. Accepts yyyy-mm-dd and dd-mm-yyyy / dd/mm/yyyy. */
    function fsParseCertDate($el) {
        if (!$el || !$el.length) return { empty: true };
        const raw = String($el.val() || "").trim();
        if (raw === "") return { empty: true };
        let y, mo, d, m = raw.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (m) {
            y = +m[1]; mo = +m[2]; d = +m[3];
        } else if ((m = raw.match(/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/))) {
            d = +m[1]; mo = +m[2]; y = +m[3];
        } else {
            return { valid: false };
        }
        const dt = new Date(Date.UTC(y, mo - 1, d));
        if (dt.getUTCFullYear() !== y || dt.getUTCMonth() !== mo - 1 || dt.getUTCDate() !== d) return { valid: false };
        return { valid: true, iso: String(y).padStart(4, "0") + "-" + String(mo).padStart(2, "0") + "-" + String(d).padStart(2, "0") };
    }

    function fsCertDateEditable($el) {
        return $el.length && !$el.prop("disabled") && !$el.prop("readonly") && !$el.closest(".fs-return-section-locked").length;
    }

    function fsSectionDateFields($section) {
        return {
            issue: $section.find(".verify-issue-date").first(),
            from: $section.find(".verify-valid-from").first(),
            to: $section.find(".verify-date").first(),
        };
    }

    /* Validate one field; returns the error message ("" when fine or empty). */
    function fsCertDateMessage(kind, f) {
        const today = fsTodayIso();
        const $el = f[kind];
        const p = fsParseCertDate($el);
        if (p.empty) return "";
        if (!p.valid) return "Enter a valid date";
        if (kind === "issue") {
            if (p.iso < FS_DATE_MIN) return "Date of First Issue cannot be before 01-01-1900";
            if (p.iso > today) return "Date of First Issue cannot be in the future";
            return "";
        }
        if (kind === "from") {
            if (p.iso < FS_DATE_MIN) return "From date cannot be before 01-01-1900";
            const issue = fsParseCertDate(f.issue);
            if (issue.valid && p.iso < issue.iso) return "From date cannot be before Date of First Issue";
            if (p.iso > today) return "From date cannot be in the future";
            return "";
        }
        // to
        const base = f.from.length ? fsParseCertDate(f.from) : fsParseCertDate(f.issue);
        if (base.valid && p.iso <= base.iso) return "To date must be after " + (f.from.length ? "From date" : "Date of First Issue");
        if (p.iso > FS_DATE_MAX_TO) return "To date cannot be after 31-12-2099";
        return "";
    }

    function fsWriteCertDateMessage($el, msg) {
        const $err = $($el.data("error"));
        if ($err.length) $err.text(msg);
        return msg;
    }

    /**
     * Validates the Q8/Q9 date fields of $section (default: both sections) and writes inline errors.
     * Sections whose Yes option is not selected, and locked/readonly fields, are skipped.
     * Returns true when everything checked is valid. window.fsLastInvalidCertDate holds the first bad input.
     */
    function fsValidateCertificateDates($section) {
        window.fsLastInvalidCertDate = null;
        if (!fsIsFormS()) return true;
        const $secs = $section && $section.length ? $section : $(FS_DATE_SECTIONS);
        let ok = true;
        $secs.each(function () {
            const f = fsSectionDateFields($(this));
            const $probe = f.to.length ? f.to : (f.issue.length ? f.issue : f.from);
            if (!$probe.length || !fsCertYesSelected($probe)) return;
            ["issue", "from", "to"].forEach(function (kind) {
                const $el = f[kind];
                if (!$el.length || !fsCertDateEditable($el)) return;
                if (fsParseCertDate($el).empty) return;
                const msg = fsWriteCertDateMessage($el, fsCertDateMessage(kind, f));
                if (msg) {
                    ok = false;
                    if (!window.fsLastInvalidCertDate) window.fsLastInvalidCertDate = $el;
                }
            });
        });
        return ok;
    }
    window.fsValidateCertificateDates = fsValidateCertificateDates;

    // Live validation: re-check the changed field and the ones that depend on it.
    $(document).on("change", FS_DATE_SECTIONS.split(", ").map(function (s) {
        return s + " .verify-issue-date, " + s + " .verify-valid-from, " + s + " .verify-date";
    }).join(", "), function () {
        if (!fsIsFormS()) return;
        const $section = $(this).closest(FS_DATE_SECTIONS);
        if (!$section.length) return;
        fsValidateCertificateDates($section);
    });


    // ✅ Common Verify Button AJAX
    $(document).on("click", ".verify-btn", function () {
        // 🔹 Select only from the correct section
        let section = $(this).closest("#previously_details, #wireman_details");
        let input = section.find(".verify-input");
        let issueDate = section.find(".verify-issue-date");
        let fromDate = section.find(".verify-valid-from");
        let toDate = section.find(".verify-date");

        if (input.length === 0 || toDate.length === 0) {
            console.error("Certificate or to date field not found!");
            return;
        }
        if (!fromDate.length && !issueDate.length) {
            console.error("From date or date of first issue field not found!");
            return;
        }

        let value = input.val()?.trim().toUpperCase() || "";

        let type = $(this).data("type");
        let errorBox = $(input.data("error"));
        let issueDateErrorBox = issueDate.length ? $(issueDate.data("error")) : $();
        let fromDateErrorBox = fromDate.length ? $(fromDate.data("error")) : $();
        let toDateErrorBox = $(toDate.data("error"));
        let msgBox = $(input.data("msg"));

        errorBox.text("");
        if (issueDateErrorBox.length) issueDateErrorBox.text("");
        if (fromDateErrorBox.length) fromDateErrorBox.text("");
        toDateErrorBox.text("");

        let isValid = true;

        const verifyCertDigits = fsCertDigits(input);
        if (value === "") {
            errorBox.text(FS_CERT_REQUIRED_MSG);
            isValid = false;
        } else if (verifyCertDigits ? !fsCertNumberIsValid(value, verifyCertDigits) : !regexRules[type].test(value)) {
            errorBox.text(FS_CERT_INVALID_MSG);
            isValid = false;
        }

        const isValidDateString = function (val) {
            const regexDate = /^(\d{4})-(\d{2})-(\d{2})$/;
            const match = val.match(regexDate);
            if (!match) return false;
            const year = parseInt(match[1]);
            const month = parseInt(match[2]);
            const day = parseInt(match[3]);
            const checkDate = new Date(year, month - 1, day);
            return (
                checkDate.getFullYear() === year &&
                checkDate.getMonth() === month - 1 &&
                checkDate.getDate() === day
            );
        };

        const validateDateInput = function ($el, emptyMsg) {
            let val = ($el.val() || "").trim();
            if (val === "") {
                $($el.data("error")).text(emptyMsg);
                return false;
            }
            if (!isValidDateString(val)) {
                $($el.data("error")).text("Enter a valid date");
                return false;
            }
            return true;
        };

        // Date of First Issue (Form S sections 8 & 9 — separate from from-date)
        if (issueDate.length && fromDate.length) {
            if (!validateDateInput(issueDate, "Date of First Issue is required")) {
                isValid = false;
            }
        }

        // From date for API verify (valid-from field, or issue-date on older forms)
        let fromDateForApi;
        if (fromDate.length) {
            if (!validateDateInput(fromDate, "From date is required")) {
                isValid = false;
            }
            fromDateForApi = fromDate;
        } else {
            if (!validateDateInput(issueDate, "Date of First Issue is required")) {
                isValid = false;
            }
            fromDateForApi = issueDate;
        }

        if (!validateDateInput(toDate, "To date is required")) {
            isValid = false;
        }

        // Form S: range / ordering rules (no-op on other forms)
        if (isValid && section.length && !fsValidateCertificateDates(section)) {
            isValid = false;
        }

        if (!isValid) return;

        let fromDateVal = (fromDateForApi.val() || "").trim();
        let toDateVal = (toDate.val() || "").trim();
        let issueDateVal = issueDate.length ? (issueDate.val() || "").trim() : "";

        let url = $(this).data("url");

        let verifyPayload = {
            license_number: value,
            fromdate: fromDateVal,
            todate: toDateVal,
            _token: $('meta[name="csrf-token"]').attr("content"),
        };
        if (issueDateVal) {
            verifyPayload.issuedt = issueDateVal;
        }

        $.ajax({
            url: url,
            method: "POST",
            data: verifyPayload,
            success: function (response) {
                let $licenseNumber = type === 'license' ? $('#l_verify') : $('#cert_verify');

                if (response.exists) {
                    $licenseNumber.val('1');

                      msgBox.removeClass("text-danger").addClass("text-success")
                          .html("&#10004; Valid License.");
                } else {
                    $licenseNumber.val('0');

                    msgBox.removeClass("text-success").addClass("text-danger")
                          .html("&#128683; Invalid License.");
                }
            },
            error: function () {
                msgBox.removeClass("text-success").addClass("text-danger")
                      .html("🚫 Error verifying license. Try again.");
            },
        });
    });


    async function isSelectedFileReadableForDraft(file) {
        if (!file) return true;
        if (typeof file.arrayBuffer !== 'function') return true;
        try {
            await file.arrayBuffer();
            return true;
        } catch (err) {
            return false;
        }
    }

    async function validateReadableSelectedFilesForDraft() {
        const $form = $('#competency_form_ws');
        if (!$form.length) return true;

        const broken = [];
        const fileInputs = $form.find('input[type="file"]').toArray();
        for (const input of fileInputs) {
            const file = input.files && input.files[0] ? input.files[0] : null;
            if (!file) continue;

            const ok = await isSelectedFileReadableForDraft(file);
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

    function normalizeIsoDateInputs(scopeSelector) {
        var $scope = scopeSelector ? $(scopeSelector) : $(document);
        $scope.find('input[data-raw]').each(function () {
            var raw = (this.getAttribute('data-raw') || '').trim();
            if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
                this.type = 'date';
                this.value = raw;
                return;
            }
            var v = (this.value || '').trim();
            var m = v.match(/^(\d{2})-(\d{2})-(\d{4})$/);
            if (m) {
                this.type = 'date';
                this.value = m[3] + '-' + m[2] + '-' + m[1];
                this.setAttribute('data-raw', this.value);
            }
        });
    }
    window.normalizeIsoDateInputs = normalizeIsoDateInputs;

    $('#saveDraftBtn').on('click', async function(e) {
        if ($('#competency_form_ws.fs-alt-form').length) {
            return;
        }
        if ($('#competency_form_p').length && !$('#competency_form_ws').length) {
            return;
        }
        e.preventDefault(); 

        const isRenewalDraft = String($('#appl_type').val() || '').toUpperCase() === 'R';

        if (isRenewalDraft && typeof window.validateCompetencyFormForSubmit === 'function') {
            const renewalDraftValid = await window.validateCompetencyFormForSubmit({
                requireDeclaration: false
            });
            if (!renewalDraftValid) {
                return;
            }
        } else {
        if ($('#competency_form_ws').length && typeof window.normalizeCompetencyDynamicSections === 'function') {
            window.normalizeCompetencyDynamicSections();
        }

        const readableFiles = await validateReadableSelectedFilesForDraft();
        if (!readableFiles) {
            return;
        }

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

            let educationUpload = $(this).find(
                'input[type="file"][name="education_document[]"], input[type="file"][name^="education_document["]'
            );

            if (educationUpload.length && educationUpload[0].files.length > 0) {
                const $educationUploadWrap = educationUpload.closest('.form-s-file-upload-wrap');
                const $educationErrorTarget = $educationUploadWrap.length ? $educationUploadWrap : educationUpload;
                const file = educationUpload[0].files[0]; // ✅ use raw DOM element
                if (file) {
                    const allowedType = 'application/pdf';
                    const minSize = 1 * 1024;   // 1 KB
                    const maxSize = 200 * 1024; // 200 KB

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

        $('#work-container .work-fields').each(function () {
            let workDocument = $(this).find(
                'input[type="file"][name="work_document[]"], input[type="file"][name^="work_document["]'
            );

           if (workDocument.length && workDocument[0].files.length > 0) {
                const $workUploadWrap = workDocument.closest('.form-s-file-upload-wrap');
                const $workErrorTarget = $workUploadWrap.length ? $workUploadWrap : workDocument;
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
        

        let licenseError = document.getElementById("licenseError");
        let dateError = document.getElementById("dateError");

        if (licenseError) licenseError.textContent = '';
        if (dateError) dateError.textContent = '';

        // Form S Q8/Q9 certificate numbers (only checked when "Yes" is chosen).
        const $badCertNo = fsValidateCertNumbers($('#competency_form_ws'));
        if ($badCertNo) {
            if (!firstErrorField) firstErrorField = $badCertNo;
            isValid = false;
        }

        // Form S Q8/Q9 date rules (only when "Yes" is chosen; no-op on other forms).
        if (!fsValidateCertificateDates()) {
            if (!firstErrorField) firstErrorField = window.fsLastInvalidCertDate;
            isValid = false;
        }

        $("#checkboxError").text("");


        // Validate Date of Birth

        // Aadhaar details removed

        // PAN details removed



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

        }
        }

            let applicationId = (typeof window.competencyDraftApplicationId === 'function')
                ? window.competencyDraftApplicationId()
                : ($('#competency_form_ws input[name="application_id"]').val() || '');

            let applType = $('#appl_type').val();

            normalizeIsoDateInputs('#competency_form_ws');
            let formData = new FormData($('#competency_form_ws')[0]);

            formData.append('form_action', 'draft');
            if (typeof window.appendWorkTransformerKvaToFormData === 'function') {
                window.appendWorkTransformerKvaToFormData(formData, $('#competency_form_ws')[0]);
            }
            if (typeof window.appendWorkExperienceDateFieldsToFormData === 'function') {
                window.appendWorkExperienceDateFieldsToFormData(formData, $('#competency_form_ws')[0]);
            }
            if (typeof window.appendWorkExperienceFilesToFormData === 'function') {
                window.appendWorkExperienceFilesToFormData(formData, $('#competency_form_ws')[0]);
            }
            if (typeof window.appendWorkContractorFieldsToFormData === 'function') {
                window.appendWorkContractorFieldsToFormData(formData, $('#competency_form_ws')[0]);
            }
            if (typeof window.appendWorkBoardMemberFieldsToFormData === 'function') {
                window.appendWorkBoardMemberFieldsToFormData(formData, $('#competency_form_ws')[0]);
            }
            if (typeof window.appendCompetencyPhotoSignToFormData === 'function') {
                window.appendCompetencyPhotoSignToFormData(formData, $('#competency_form_ws')[0]);
            }

            

            if (applType === "R") {
                if (typeof window.competencyPersistUrls === 'function') {
                    const persistUrls = window.competencyPersistUrls();
                    if (applicationId) {
                        url = String(persistUrls.renewal || '').replace('__APPL_ID__', applicationId);
                    } else {
                        url = persistUrls.store;
                    }
                } else {
                    url = BASE_URL + "/form/draft_renewal_submit";
                    if (applicationId) {
                        url += "/" + applicationId;
                    }
                }
            } else {
                url = BASE_URL + "/form/draft_submit";
                if (typeof window.competencyPersistUrls === 'function') {
                    const persistUrls = window.competencyPersistUrls();
                    if (applicationId && persistUrls.draftWithId) {
                        url = String(persistUrls.draftWithId).replace('__APPL_ID__', applicationId);
                    } else if (persistUrls.draft) {
                        url = persistUrls.draft;
                        if (applicationId) {
                            url = String(url).replace(/\/$/, '') + '/' + applicationId;
                        }
                    }
                } else if (applicationId) {
                    url += "/" + applicationId;
                }
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
                    if (response.status == 'success') {
                        if (response.cc_digitization_temp_id) {
                            sessionStorage.setItem(
                                'cc_digitization_temp_id',
                                response.cc_digitization_temp_id
                            );
                        }

                        Swal.fire({
                            title: 'Application Saved As Draft!',
                            html: 'Your Application ID: <strong>' + response.application_id + '</strong>',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            window.location.href = BASE_URL +'/dashboard'; // change as needed
                        });

                    }else{
                        Swal.fire("Failed", "Application not saved as draft", "error");
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
    });

    $(document).on('click', '.remove-doc_edu', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const $row = $(this).closest('tr');
        if ($row.attr('data-renew-locked') === '1') {
            return;
        }
        // Keep array indexes aligned: flip existing hidden flag to 1
        const $flag = $row.find('input.removed-document-edu[name="removed_document[]"]');
        if ($flag.length) $flag.val('1');

        // Preserve stable row index so backend can map the upload correctly
        const idx = $row.data('edu-index');
        const nameAttr = (idx !== undefined && idx !== null && idx !== '') ? `education_document[${idx}]` : 'education_document[0]';
        let fileInput = `<div class="file-section"><input type="file" class="form-control" name="${nameAttr}" accept=".pdf,application/pdf"></div>`;
        $(this).closest('.file-section').replaceWith(fileInput);
    });

    // If user selects a new file after clicking Remove, treat it as a replacement (not removed).
    $(document).on('change', 'input[type="file"][name^="education_document["]', function () {
        const $row = $(this).closest('tr');
        const $flag = $row.find('input.removed-document-edu[name="removed_document[]"]');
        if ($flag.length && this.files && this.files.length > 0) {
            $flag.val('0');
        }
    });

    $(document).on('click', '.remove-doc_work', function () {
        let fileInput = '<input type="file" class="form-control" name="work_document[]" accept=".pdf,application/pdf"><input type="hidden" name="removed_document_work[]" value="1">';

        $(this).closest('.file-section').replaceWith(fileInput);
    });

    $(document).on('click', '.remove-inst', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const $row = $(this).closest('tr');
        const $flag = $row.find('input.removed-document-inst[name="removed_document_inst[]"]');
        if ($flag.length) {
            $flag.val('1');
        }
        const idx = $('#institute-container .institute-fields').index($row);
        const nameAttr = idx >= 0 ? `institute_document[${idx}]` : 'institute_document[0]';
        const fileInput = `<div class="file-section"><input type="file" class="form-control" name="${nameAttr}" accept=".pdf,application/pdf,.png,.jpg,.jpeg"></div>`;
        $(this).closest('.file-section').replaceWith(fileInput);
    });

    $(document).on('change', 'input[type="file"][name^="institute_document["]', function () {
        const $row = $(this).closest('tr');
        const $flag = $row.find('input.removed-document-inst[name="removed_document_inst[]"]');
        if ($flag.length && this.files && this.files.length > 0) {
            $flag.val('0');
        }
    });


    $(document).on('click', '.remove_edu', function() {
        let edu_id = $(this).data('edu_id'); 


        let url = $(this).data('url');

        $.ajax({
            url:  url, // Laravel route
            type: "POST",
            data: {
                edu_id : edu_id,
                _token: $('meta[name="csrf-token"]').attr("content") // CSRF token
            },
            success: function (response) {
             
            },
            error: function () {
                Swal.fire("Error!", "Something went wrong. Try again!", "error");
            }
        });
    });

    $(document).on('click', '.remove_exp', function() {
        let exp_id = $(this).data('exp_id'); 


        console.log(exp_id);


        let url = $(this).data('url');

        $.ajax({
            url:  url, // Laravel route
            type: "POST",
            data: {
                exp_id : exp_id,
                _token: $('meta[name="csrf-token"]').attr("content") // CSRF token
            },
            success: function (response) {
             
            },
            error: function () {
                Swal.fire("Error!", "Something went wrong. Try again!", "error");
            }
        });
    });



    // Aadhaar document removed

    // PAN document removed



    // Button toggle option for verify license

    function unlockVerifyField(el) {
        if (!el) return;
        const oldNode = el.jquery ? el.get(0) : el;
        if (!oldNode || !oldNode.parentNode) return;

        const newNode = oldNode.cloneNode(false);
        newNode.removeAttribute('readonly');
        newNode.removeAttribute('disabled');
        newNode.readOnly     = false;
        newNode.disabled     = false;
        newNode.value        = '';
        newNode.defaultValue = '';

        oldNode.parentNode.replaceChild(newNode, oldNode);
        return newNode;
    }

    function unlockVerifySection($section) {
        if (!$section || !$section.length) return;
        $section.find('.verify-input, .verify-issue-date, .verify-valid-from, .verify-date').each(function () {
            unlockVerifyField(this);
        });
        $section.find('.text-danger, .text-success, #verify_status, .verify_status').text('');
    }

    $(document).on('click', '.remove_verify', function () {

        const $btn  = $(this);
        const type  = $btn.data('type');

        const $section = $btn.closest('#previously_details, #wireman_details');

        if ($section.length) {
            unlockVerifySection($section);
        } else if (type == 'superviser') {
            unlockVerifyField(document.getElementById('previously_number'));
            unlockVerifyField(document.getElementById('previously_valid_to'));
            unlockVerifyField(document.getElementById('previously_issue_date'));
            unlockVerifyField(document.getElementById('previously_valid_from'));
            $('.verify_status').text('');
            $('#previouslyIssueDateError, #previouslyFromDateError, #dateError').text('');
        } else {
            unlockVerifyField(document.getElementById('certificate_no'));
            unlockVerifyField(document.getElementById('certificate_valid_to'));
            unlockVerifyField(document.getElementById('certificate_issue_date'));
            unlockVerifyField(document.getElementById('certificate_valid_from'));
            $('#verify_status').text('');
            $('#certIssueDateError, #certFromDateError, #certDateError').text('');
        }

        $btn.remove();

        if (type == 'superviser') {
            $('.btn-forms').length && $('.btn-forms').removeClass('d-none');
        }

        if ($section.length) {
            $section.find('.verify-btn').removeClass('d-none');
        } else {
            $('.verify-btn').removeClass('d-none');
        }
    });



    // $('.remove-pan-doc').click(function (e) {
    //     e.preventDefault();
    //     console.log('sdf');
    //     // Hide existing document section
        
    //     // Show file input
    //     $('.pan-doc-input').removeClass('d-none');
    //     $('.pan-doc-container').hide();

    //     // Mark document as removed
    //     $('#pan_doc_removed').val('1');
    // });


});


/* ------------------------------------------------------------------
 * Global Father's Name formatter (Title Case while typing)
 * Applies to every <input name="fathers_name"> (also added dynamically)
 * "ARUN srinivasakannan g" -> "Arun Srinivasakannan G"
 * ------------------------------------------------------------------ */
(function () {
    'use strict';

    // First letter of each whitespace-separated word upper, the rest lower.
    function toNameCase(str) {
        return String(str == null ? '' : str)
            .toLocaleLowerCase()
            .replace(/(^|\s)(\S)/g, function (m, sp, ch) {
                return sp + ch.toLocaleUpperCase();
            });
    }
    window.formatFathersName = toNameCase;

    function applyFormat(el) {
        var oldVal = el.value;
        var newVal = toNameCase(oldVal);
        if (oldVal === newVal) return;

        // Only letter case changes, so length is identical and the caret
        // index stays valid. Save/restore it so typing is not disturbed.
        var start = el.selectionStart, end = el.selectionEnd, dir = el.selectionDirection;
        el.value = newVal;
        try {
            if (start !== null) el.setSelectionRange(start, end, dir || 'none');
        } catch (e) { /* input types without selection support */ }
    }

    // Father's Name + Applicant Name (Forms S/W/WH/P). Form A / EA firm-name
    // inputs use the "text-box" class and are excluded (e.g. "ABC PVT LTD").
    // Registration form: first_name / last_name.
    var SELECTOR = 'input[name="fathers_name" i], input[name="applicant_name" i]:not(.text-box), ' +
        'input[name="first_name" i], input[name="last_name" i], input[data-titlecase]';

    // Delegated: works for fields present now and added later.
    document.addEventListener('input', function (e) {
        var el = e.target;
        if (!el || !el.matches || !el.matches(SELECTOR)) return;
        if (e.isComposing) return; // don't break IME composition
        applyFormat(el);
    }, true);

    // Finish formatting after IME composition ends.
    document.addEventListener('compositionend', function (e) {
        var el = e.target;
        if (el && el.matches && el.matches(SELECTOR)) applyFormat(el);
    }, true);
})();
