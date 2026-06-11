$(document).ready(function () {

    alterationModal = new bootstrap.Modal(
        document.getElementById("alteration"),
        {
            backdrop: "static",
            keyboard: false,
        }
    );

    alterationModal.show();

    // Check on page load
    toggleQcUpgrade();

    // Check when certificate changes
    $("#cert_name").on("change", function () {
        toggleQcUpgrade();
    });

    function toggleQcUpgrade() {
        let cert = $("#cert_name").val();

        if (cert === "C") {
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

// alteration Submit
$(document).on("click", "#alterationSubmit", function () {
    $(".error").html("");

    let isValid = true;

    let cert_name = $('input[name="cert_name"]').val().trim();
    

    if (cert_name === "") {
        $("#cert_name_error").html("Certificate Number is required");
        isValid = false;
    }

    


    if (!isValid) {
        return false;
    }

    let formData = new FormData(document.getElementById("alterationForm"));

    $.ajax({
        url: "/alteration/storealteration",

        type: "POST",

        data: formData,

        processData: false,

        contentType: false,

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        beforeSend: function () {
            $("#alterationSubmit")
                .prop("disabled", true)
                .text("Please Wait...");
        },

        success: async function (response) {
            $("#alterationSubmit").prop("disabled", false).text("Submit");

            if (response.status == 200) {
                // If certificate found, fill applicant name
                if (response.is_matched == 1) {
                    // $("#Applicant_Name").val(response.appname);
                    $("#certcode").val(response.certcode);
                    // $("#applicants_address").val(response.address);
                } else {
                    $("#Applicant_Name").val("");
                    $("#certcode").val("");
                    $("#applicants_address").val("");
                }

                $("#alterationForm")[0].reset();

                alterationModal.hide();

                $("#declaration-agree-renew").prop("checked", false);

                $("#declaration-error-renew").addClass("d-none");

                await loadInstructions();

                competencyModal.show();
            }
        },

        error: function (xhr) {
            $("#alterationSubmit").prop("disabled", false).text("Submit");

            if (xhr.status === 422) {
                $.each(xhr.responseJSON.errors, function (key, value) {
                    $("#" + key + "_error").html(value[0]);
                });
            }
        },
    });
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
