$(document).ready(function () {
    digitization_clModal = new bootstrap.Modal(
        document.getElementById("digitization_cl"),
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

    digitization_clModal.show();

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

// digitization_cl Submit
$(document).on("click", "#digitization_clSubmit", function () {

    // Clear all errors
    $("#digitization_clForm .error").html("");

    let isValid = true;

    // -----------------------------------------
    // GET VALUES
    // -----------------------------------------

    let clnumber = $('#digitization_clForm input[name="clnumber"]')
        .val()
        .trim();

    let fissue = $('#digitization_clForm input[name="fissue"]')
        .val();

    let from_date = $('#digitization_clForm input[name="from_date"]')
        .val();

    let to_date = $('#digitization_clForm input[name="to_date"]')
        .val();

    let fileInput = $('#digitization_clForm input[name="cl_doc"]')[0];

    let file = fileInput && fileInput.files.length
        ? fileInput.files[0]
        : null;


    // -----------------------------------------
    // LICENCE NUMBER
    // -----------------------------------------

    if (clnumber === "") {

        $("#clnumber_error")
            .html("Licence Number is required");

        isValid = false;
    }


    // -----------------------------------------
    // FIRST ISSUE
    // -----------------------------------------

    if (fissue === "") {
        

        $(".fissue_error")
            .html("Date of First Issue is required");

        isValid = false;
    }


    // -----------------------------------------
    // VALIDITY FROM
    // -----------------------------------------

    if (from_date === "") {

        $(".from_date_error")
            .html("Validity From Date is required");

        isValid = false;
    }


    // -----------------------------------------
    // VALIDITY TO
    // -----------------------------------------

    if (to_date === "") {

        $(".to_date_error")
            .html("Validity To Date is required");

        isValid = false;
    }


    // -----------------------------------------
    // DATE COMPARISON
    // -----------------------------------------

    if (from_date && to_date) {

        if (new Date(to_date) < new Date(from_date)) {

            $(".to_date_error").html(
                "Validity To Date must be greater than or equal to Validity From Date"
            );

            isValid = false;
        }
    }


    // -----------------------------------------
    // FILE VALIDATION
    // -----------------------------------------

    if (!file) {

        $(".cl_doc_error")
            .html("Please upload PDF document");

        isValid = false;

    } else {

        if (file.type !== "application/pdf") {

            $("#cl_doc_error")
                .html("Only PDF files are allowed");

            isValid = false;
        }

        if (file.size > 250 * 1024) {

            $("#cl_doc_error")
                .html("File size should not exceed 250 KB");

            isValid = false;
        }
    }


    // -----------------------------------------
    // STOP IF FRONTEND VALIDATION FAILS
    // -----------------------------------------

    if (!isValid) {
        return false;
    }


    // -----------------------------------------
    // FORM DATA
    // -----------------------------------------

    let formData = new FormData(
        document.getElementById("digitization_clForm")
    );


    // -----------------------------------------
    // AJAX
    // -----------------------------------------

    $.ajax({

        url: BASE_URL + "/digitization_cl/storedigitization_cl",

        type: "POST",

        data: formData,

        processData: false,

        contentType: false,

        headers: {
            "X-CSRF-TOKEN":
                $('meta[name="csrf-token"]').attr("content")
        },


        beforeSend: function () {

            $("#digitization_clSubmit")
                .prop("disabled", true)
                .html("Please Wait...");
        },


        success: function (response) {

            $("#digitization_clSubmit")
                .prop("disabled", false)
                .html(`
                    Submit
                    <svg viewBox="0 0 24 24"
                         fill="none"
                         stroke="currentColor"
                         stroke-width="2.5"
                         stroke-linecap="round"
                         stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                `);


            if (response.status === 200) {

                $("#digitization_clForm")[0].reset();

                digitization_clModal.hide();

                $("#declaration-agree-renew")
                    .prop("checked", false);

                $("#declaration-error-renew")
                    .addClass("d-none");

                loadInstructions();

                competencyModal.show();
            }
        },


error: function (xhr) {

    $("#digitization_clSubmit")
        .prop("disabled", false)
        .text("Submit");

    $("#digitization_clForm .error").html("");

    if (xhr.status === 422 && xhr.responseJSON?.errors) {

        // alert("Please correct the errors in the form.");

        $.each(xhr.responseJSON.errors, function (key, value) {

            let message = value[0];

            if (
                message === "Validity To Date must be greater than or equal to Validity From Date." ||
                message === "Apply New Application Validity Period including Renewal exceeds limits"
            ) {

                $("#digitization_clForm .error_message")
                    .html(message)
                    .show();

            } else {

                $("#" + key + "_error")
                    .html(message)
                    .show();
            }

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
