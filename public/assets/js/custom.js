$(document).ready(function () {

    // License verfication for FORM -w , FORM - WH ----------------------

    const regexRules = {
        license: /^(C|LC)\d+$/,
        certificate: /^(B|H|LWH|LB)\d+$/,
        supervisor:/^(B|C|LC|LB)\d+$/,
        helper:/^(H|LWH)\d+$/,
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

    // ✅ Common Keyup Validation
    $(document).on("keyup", ".verify-input", function () {
        let value = $(this).val()?.trim().toUpperCase() || "";
        $(this).val(value);

        let type = $(this).data("type");
        let errorBox = $($(this).data("error"));
        let msgBox = $($(this).data("msg"));

        errorBox.text("");
        msgBox.text("");

        if (value === "") {
            errorBox.text("Certificate Number is Required");
            return;
        }

        if (!regexRules[type].test(value)) {
            errorBox.text("Invalid Certificate Number");
        }
    });

    // ✅ Common Date Validation
    $(document).on("change", ".verify-date", function () {
        let value = $(this).val()?.trim() || "";
        let errorBox = $($(this).data("error"));
        errorBox.text(value === "" ? "Date of Expiry is required" : "");
    });

    // ✅ Common Date of Issue Validation
    $(document).on("change", ".verify-issue-date", function () {
        let value = $(this).val()?.trim() || "";
        let errorBox = $($(this).data("error"));
        errorBox.text(value === "" ? "Date of Issue is required" : "");
    });

    // ✅ When Yes/No toggles hide, clear Date of Issue error too
    $(document).on("change", ".toggle-details", function () {
        if ($(this).val() !== "yes") {
            let target = $(this).data("target");
            $(target).find(".verify-issue-date").val("");
        }
    });

    // ✅ Common Verify Button AJAX
    $(document).on("click", ".verify-btn", function () {
        // 🔹 Select only from the correct section
        let section = $(this).closest("#previously_details, #wireman_details");
        let input = section.find(".verify-input");
        let date = section.find(".verify-date");
        let issueDate = section.find(".verify-issue-date");

        if (input.length === 0 || date.length === 0) {
            console.error("Input or date field not found!");
            return;
        }

        let value = input.val()?.trim().toUpperCase() || "";
        let dateVal = date.val()?.trim() || "";
        let issueDateVal = issueDate.length ? (issueDate.val()?.trim() || "") : "";

        let type = $(this).data("type");
        let errorBox = $(input.data("error"));
        let dateErrorBox = $(date.data("error"));
        let issueDateErrorBox = issueDate.length ? $(issueDate.data("error")) : $();
        let msgBox = $(input.data("msg"));


        errorBox.text("");
        dateErrorBox.text("");
        if (issueDateErrorBox.length) issueDateErrorBox.text("");

        let isValid = true;

        if (value === "") {
            errorBox.text("Certificate Number is required");
            isValid = false;
        } else if (!regexRules[type].test(value)) {
            errorBox.text("Invalid Certificate Number");
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

        if (dateVal === "") {
            dateErrorBox.text("Date of Expiry is required");
            isValid = false;
        } else if (!isValidDateString(dateVal)) {
            dateErrorBox.text("Enter a valid date");
            isValid = false;
        }

        if (issueDate.length) {
            if (issueDateVal === "") {
                issueDateErrorBox.text("Date of Issue is required");
                isValid = false;
            } else if (!isValidDateString(issueDateVal)) {
                issueDateErrorBox.text("Enter a valid date");
                isValid = false;
            }
        }

        if (!isValid) return;


        let url = $(this).data("url");


        $.ajax({
            url: url,
            method: "POST",
            data: {
                license_number: value,
                date: dateVal,
                issue_date: issueDateVal,
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                let $licenseNumber = type === 'license' ? $('#l_verify') : $('#cert_verify');

                console.log($licenseNumber);

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
        e.preventDefault(); 

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

        $("#checkboxError").text("");


        // Validate Date of Birth

        const aadhaarInput = document.getElementById("aadhaar");
        const aadhaarError = document.getElementById("aadhaar-error");

        // get value, remove spaces, and trim
        const aadhaar = aadhaarInput ? aadhaarInput.value.replace(/\s+/g, '').trim() : "";

        const aadhaarRegex = /^[2-9]{1}[0-9]{11}$/;

        if (aadhaarInput && aadhaarError && aadhaar !== '' && !aadhaarRegex.test(aadhaar)) {
            aadhaarError.textContent =
                "Please enter a valid 12-digit Aadhaar number (should not start with 0 or 1).";
            if (!firstErrorField) firstErrorField = aadhaar;
            isValid = false;
        } else if (aadhaarError) {
            aadhaarError.textContent = "";
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

        } else {
            let applicationId = $('#application_id').val();

            let applType = $('#appl_type').val();

            normalizeIsoDateInputs('#competency_form_ws');
            let formData = new FormData($('#competency_form_ws')[0]);

            formData.append('form_action', 'draft');

            

            if (applType === "R") {
                // Renewal draft submit route
                url = BASE_URL + "/form/draft_renewal_submit";
            } else {
                // New application draft submit route
                url = BASE_URL + "/form/draft_submit";
            } 

            // let url = $(this).data("url");

            if (applicationId) {
                url += "/" + applicationId;
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
                        Swal.fire("Error", xhr.responseText || "An unexpected error occurred.", "error");
                    }
                }
            });

        }
    });

    $(document).on('click', '.remove-doc_edu', function (e) {
        e.preventDefault();
        e.stopPropagation();
        const $row = $(this).closest('tr');
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



    $(document).on('click', '.remove-docs', function () {
        const $td = $(this).closest('td');
        // Legacy pages could have a hidden .aadhaar-doc-input alongside the container (duplicate name="aadhaar_doc").
        $td.find('.aadhaar-doc-input').remove();

        let inputHtml = `
            <div class="aadhaar-doc-input">
                <input autocomplete="off" class="form-control text-box single-line" id="aadhaar_doc" name="aadhaar_doc" type="file" accept=".pdf,application/pdf">
                <span class="file-limit"> File type: PDF (Max 250 KB) </span>
                <small class="text-danger file-error"></small>
            </div>
        `;

        $(this).closest('.aadhaar-doc-container').replaceWith(inputHtml);

        $('#aadhaar_doc_removed').val('1');
    });

    $(document).on('change', '#aadhaar_doc', function () {
        if (this.files && this.files.length > 0) {
            $('#aadhaar_doc_removed').val('0');
        }
    });

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
        $section.find('.verify-input, .verify-issue-date, .verify-date').each(function () {
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
            unlockVerifyField(document.getElementById('previously_date'));
            unlockVerifyField(document.getElementById('previously_issue_date'));
            $('.verify_status').text('');
            $('#previouslyIssueDateError').text('');
        } else {
            unlockVerifyField(document.getElementById('certificate_no'));
            unlockVerifyField(document.getElementById('certificate_date'));
            unlockVerifyField(document.getElementById('certificate_issue_date'));
            $('#verify_status').text('');
            $('#certIssueDateError').text('');
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
