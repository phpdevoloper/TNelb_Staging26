



$(document).ready(function () {
    
    alteration_clModal = new bootstrap.Modal(
        document.getElementById("alteration_cl"),
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
    alteration_clModal.show();

    // Check on page load
    toggleQcUpgrade();

    // Check when certificate changes
    $("#cert_name").on("change", function () {
        toggleQcUpgrade();
    });

    function toggleQcUpgrade() {
        let cert = $("#cert_name").val();

        if (cert === "S") {
            $("#qc_upgrade_section").show();
        } else {
            $("#qc_upgrade_section").hide();
            $("#qc").prop("checked", false);
        }
    }
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
                appl_type: "N",

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

// alteration_cl Submit
$(document).on("click", "#alteration_clSubmit", async function () {


    // alert('111');
    $(".error").html("");

    let isValid = true;

    let cert_name = $("#cert_name").val();

    if (cert_name == "0" || cert_name == "") {
        $("#cert_name_error").html("Certificate Name is required");
        isValid = false;
    }

    let checkedCount = $('input[type="checkbox"]:checked').length;

    if (checkedCount === 0) {
        $("#alter_fields_error").html("Select at least one field to alter");
        isValid = false;
    }

    if (!isValid) {
        return false;
    }

    let selectedFields = [];

    $("input[type='checkbox']:checked").each(function () {
        selectedFields.push($(this).attr("id"));
    });

    $("#selected_cert_name").val(cert_name);

    // Make all text fields readonly
    $("#mainForm")
        .find(
            "input[type='text'], input[type='date'], input[type='number'], textarea",
        )
        .prop("readonly", true);

    // Disable selects, radio, checkbox, file inputs
    $("#mainForm")
        .find(
            "select, input[type='radio'], input[type='checkbox'], input[type='file']",
        )
        .prop("disabled", true);

    // Enable based on selected checkboxes
    if (selectedFields.includes("name")) {
        
        $("#Applicant_Name").prop("readonly", false);
    }

   if (selectedFields.includes("address")) {
        $("#applicants_address").prop("readonly", false);
    }

    if (selectedFields.includes("workexp")) {
        $("#work-table")
            .find("input, textarea, select, button")
            .prop("disabled", false)
            .prop("readonly", false);
    }

    if (selectedFields.includes("qc")) {
        $(".qc-section")
            .find("input, textarea, select")
            .prop("disabled", false)
            .prop("readonly", false);
    }

    $("#work-table")
    .find("input, textarea, select, button")
    .prop("disabled", true)
    .prop("readonly", true);

    if (selectedFields.includes("workexp")) {

    $("#work-table")
        .find("input, textarea, select, button")
        .prop("disabled", false)
        .prop("readonly", false);

    // Keep calculated fields readonly
    $("#work-table")
        .find(
            ".work-duration-y, .work-duration-m, .work-duration-d, .work-year-total-display"
        )
        .prop("readonly", true);
        }

    alteration_clModal.hide();

    $("#declaration-agree-renew").prop("checked", false);
    $("#declaration-error-renew").addClass("d-none");

    await loadInstructions();
    competencyModal.show();
});

$(document).on("change", "#workexp", function () {

    if ($(this).is(":checked")) {

        $("#work-table")
            .find("input, textarea, select, button")
            .prop("disabled", false)
            .prop("readonly", false);

    } else {

        $("#work-table")
            .find("input, textarea, select, button")
            .prop("disabled", true)
            .prop("readonly", true);
    }

});
$(document).on("keyup", 'input[name="cert_name"]', function () {
    $("#cert_name_error").html("");
});

// Hide error when checkbox selected
$(document).on("change", "#declaration-agree-renew", function () {
    if ($(this).is(":checked")) {
        $("#declaration-error-renew").addClass("d-none");
    }
});

$(document).on("change", 'input[type="checkbox"]', function () {
    if ($('input[type="checkbox"]:checked').length > 0) {
        $("#alter_fields_error").html("");
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
});
