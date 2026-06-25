$(document).ready(function () {
    restoreCcDigitizationTempId();

    digitizationModal = new bootstrap.Modal(
        document.getElementById("digitization"),
        {
            backdrop: "static",
            keyboard: false,
        },
    );

    competencyModal = new bootstrap.Modal(
        document.getElementById("competencyInstructionsModal"),
        {
            backdrop: "static",
            keyboard: false,
        },
    );

    let path = window.location.pathname;

    if (path === "/apply-form-s_d") {
        $("#qc_section").show();
    } else {
        $("#qc_section").hide();
    }

    digitizationModal.show();

    $('input[name="qc_det"]').on("change", function () {
        if ($(this).val() === "yes") {
            $("#qc_details").slideDown();
        } else {
            $("#qc_details").slideUp();

            $("#cl_type").val("0");
            $('input[name="licence_no"]').val("");
            $('input[name="contractor_name"]').val("");
            $('input[name="qc_doc"]').val("");
        }
    });
});

function restoreCcDigitizationTempId() {
    var $field = $("#cc_digitization_temp_id");
    if (!$field.length) {
        return;
    }
    if (($field.val() || "").trim() !== "") {
        return;
    }
    var stored = sessionStorage.getItem("cc_digitization_temp_id");
    if (stored) {
        $field.val(stored);
    }
}

async function loadInstructions() {
    let modalBody = document.getElementById("instructionContent");

    if (modalBody) {
        modalBody.innerHTML =
            '<p class="mb-0 text-muted">Loading instructions...</p>';
    }

    try {
        let instructionResponse = await $.ajax({
            url: "/licences/getFormInstruction",
            type: "POST",

            data: {
                appl_type: $("#appl_type").val() || "N",

                licence_code: $("#license_name").val() || "C",

                _token: $('meta[name="csrf-token"]').attr("content"),
            },
        });

        if (
            instructionResponse &&
            Number(instructionResponse.status) === 200 &&
            instructionResponse.data
        ) {
            try {
                let delta = JSON.parse(instructionResponse.data);

                if (
                    typeof QuillDeltaToHtmlConverter !== "undefined" &&
                    delta &&
                    delta.ops
                ) {
                    let converter = new QuillDeltaToHtmlConverter(delta.ops, {
                        inlineStyles: true,
                        multiLineParagraph: false,
                        listItemTag: "li",
                        paragraphTag: "p",
                    });

                    let html = converter.convert();

                    html = html.replace(/@(\s*)(\(|\uFF08)/g, "$1$2");

                    html = html.replace(
                        /<(li|p)([^>]*)>@(\s*)(\(|\uFF08)/gi,
                        "<$1$2>$3$4",
                    );

                    modalBody.innerHTML = html;
                } else {
                    modalBody.textContent = instructionResponse.data;
                }
            } catch (e) {
                modalBody.textContent = instructionResponse.data;
            }
        } else {
            modalBody.innerHTML =
                '<p class="text-danger">Instruction not available.</p>';
        }
    } catch (err) {
        modalBody.innerHTML =
            '<p class="text-danger">Unable to load instructions.</p>';
    }
}

// Digitization Submit
$(document).on("click", "#digitizationSubmit", function () {
    $(".error").html("");

    let isValid = true;

    let ccnumber = $('input[name="ccnumber"]').val().trim();
    let fissue = $('input[name="fissue"]').val();
    let from_date = $('input[name="from_date"]').val();
    let to_date = $('input[name="to_date"]').val();
    let qc = $('input[name="qc_det"]:checked').val();
    let file = $('input[name="cc_doc"]')[0].files[0];

    let fileqc = $('input[name="qc_doc"]')[0].files[0];

    if (ccnumber === "") {
        $("#ccnumber_error").html("Certificate Number is required");
        isValid = false;
    }

    if (fissue === "") {
        $("#fissue_error").html("Date of First Issue is required");
        isValid = false;
    }

    if (from_date === "") {
        $("#from_date_error").html("From Date is required");
        isValid = false;
    }

    if (to_date === "") {
        $("#to_date_error").html("To Date is required");
        isValid = false;
    }

    if (from_date && to_date && new Date(to_date) < new Date(from_date)) {
        $("#to_date_error").html(
            "To Date must be greater than or equal to From Date",
        );
        isValid = false;
    }

    if (!qc) {
        $("#qc_error").html("Please select Yes or No");
        isValid = false;
    }

    if (!file) {
        $("#cc_doc_error").html("Please upload PDF document");

        isValid = false;
    } else {
        if (file.type !== "application/pdf") {
            $("#cc_doc_error").html("Only PDF files are allowed");

            isValid = false;
        }

        if (file.size > 250 * 1024) {
            $("#cc_doc_error").html("File size should not exceed 250 KB");

            isValid = false;
        }
    }

    if (qc === "yes") {
        let cl_type = $("#cl_type").val();
        let licence_no = $('input[name="licence_no"]').val().trim();
        let contractor_name = $('input[name="contractor_name"]').val().trim();

        if (cl_type === "" || cl_type === "0") {
            $("#cl_type_error").html("Please select License Type");
            isValid = false;
        }

        if (licence_no === "") {
            $("#licence_no_error").html("License Number is required");
            isValid = false;
        }

        if (contractor_name === "") {
            $("#contractor_error").html("Contractor Name is required");
            isValid = false;
        }

        if (!fileqc) {
            $("#qc_doc_error").html("Please upload PDF document");

            isValid = false;
        } else {
            if (fileqc.type !== "application/pdf") {
                $("#qc_doc_error").html("Only PDF files are allowed");

                isValid = false;
            }

            if (fileqc.size > 250 * 1024) {
                $("#qc_doc_error").html("File size should not exceed 250 KB");

                isValid = false;
            }
        }
    }

    if (!isValid) {
        return false;
    }

    let formData = new FormData(document.getElementById("digitizationForm"));

    $.ajax({
        url: "/digitization/storeDigitization",

        type: "POST",

        data: formData,

        processData: false,

        contentType: false,

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        beforeSend: function () {
            $("#digitizationSubmit")
                .prop("disabled", true)
                .text("Please Wait...");
        },

        success: async function (response) {
            $("#digitizationSubmit").prop("disabled", false).text("Submit");

            if (response.status == 200) {
                if (response.temp_app_id) {
                    $("#cc_digitization_temp_id").val(response.temp_app_id);
                    sessionStorage.setItem(
                        "cc_digitization_temp_id",
                        response.temp_app_id,
                    );
                }

                // If certificate found, fill applicant name
                if (response.is_matched == 1) {
                    // $("#Applicant_Name").val(response.appname);
                    $("#certcode").val(response.certcode);
                    // $("#applicants_address").val(response.address);
                } else {
                    $("#certcode").val("");
                    $("#applicants_address").val("");
                }

                $("#digitizationForm")[0].reset();

                digitizationModal.hide();

                $("#declaration-agree-renew").prop("checked", false);

                $("#declaration-error-renew").addClass("d-none");

                await loadInstructions();

                competencyModal.show();
            }
        },

        error: function (xhr) {
            $("#digitizationSubmit").prop("disabled", false).text("Submit");

            if (xhr.status === 422) {
                $.each(xhr.responseJSON.errors, function (key, value) {
                    $("#" + key + "_error").html(value[0]);
                });
            }
        },
    });
});

$(document).on("keyup", 'input[name="ccnumber"]', function () {
    $("#ccnumber_error").html("");
    
});

$(document).on("change", 'input[name="fissue"]', function () {
    $("#fissue_error").html("");
    
});

$(document).on("change", 'input[name="from_date"]', function () {
    $("#from_date_error").html("");
    
});

$(document).on("change", 'input[name="to_date"]', function () {
    $("#to_date_error").html("");
    
});

$(document).on("change", 'input[name="cc_doc"]', function () {
    $("#cc_doc_error").html("");
    
});

$(document).on("keyup", 'input[name="cl_type"]', function () {
    $("#cl_type_error").html("");
});

$(document).on("keyup", 'input[name="licence_no"]', function () {
    $("#licence_no_error").html("");
});

$(document).on("keyup", 'input[name="contractor_name"]', function () {
    $("#contractor_error").html("");
});

$(document).on("change", 'input[name="qc_doc"]', function () {
    $("#qc_doc_error").html("");
    
});

$(document).on("change", 'input[name="qc"]', function () {
    $("#cl_type_error").html("");
    $("#licence_no_error").html("");
    $("#contractor_error").html("");
    $("#qc_doc_error").html("");
});

// Hide error when checkbox selected
$(document).on("change", "#declaration-agree-renew", function () {
    if ($(this).is(":checked")) {
        $("#declaration-error-renew").addClass("d-none");
    }
});

$(document).on("click", "#proceedPayment", function () {
    if (window._competencyPaymentProceedActive) {
        return;
    }

    let agreeCheckbox = document.getElementById("declaration-agree-renew");

    let errorText = document.getElementById("declaration-error-renew");

    if (!agreeCheckbox.checked) {
        errorText.classList.remove("d-none");
        return false;
    }

    errorText.classList.add("d-none");

    if (document.activeElement) {
        document.activeElement.blur();
    }

    competencyModal.hide();

    // Continue next process here
});
