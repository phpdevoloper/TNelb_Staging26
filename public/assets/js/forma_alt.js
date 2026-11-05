// $(document).on("submit", "#competency_form_a_alter", async function (e) {
 $("#competency_form_a_alter").on("submit", async function (e) {
    e.preventDefault();

      let form = this;

    // ==========================================
    // ENABLE DISABLED FIELDS BEFORE FORMDATA
    // ==========================================
    let disabledFields = $(form).find(":input:disabled");

    disabledFields.prop("disabled", false);

     let formData = new FormData(this);
        let submitter = e.originalEvent?.submitter;
        let actionType = "submit";
        if ($(submitter).hasClass("save-draft")) {
            actionType = "draft";
        }

        $(".error").text("");
        let isValid = true;

        let isValiddraft = true;

        let applicantName = $("#applicant_name").val().trim();
        let businessAddress = $("textarea[name='business_address']").val().trim();


        let license_number =
            $("input[name='license_number']").val()?.trim() || "";

        formData.append("module", $("input[name='module']").val());
        formData.append("appl_type", $("input[name='appl_type']").val());
        // formData.append("license_name", $("input[name='upload_license_name']").val());

        if ((applicantName === "") | (businessAddress === "")) {
            Swal.fire({
                icon: "error",
                width: 450,
                // title: 'Missing Details',
                text: "Fill 1. Name in which Electrical Contractor/s licence is applied for and 2.Business Address to Save  ",
            });

            if (applicantName === "") {
                $("#applicant_name_error").text("Name is required.");
            }

            if (businessAddress === "") {
                $("#business_address_error").text("Business address is required.");
            }

            $(".nav-item").each(function () {
                if ($(this).text().trim() === "Basic Details") {
                    $(this).addClass("tab-error-bg");
                    $(this).trigger("click"); // switch to Basic Details tab
                }
            });

            const ownershipNotice = document.querySelector(".text-red");
            if (ownershipNotice) {
                ownershipNotice.style.color = "red";
                ownershipNotice.style.fontWeight = "bold";
                ownershipNotice.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }

            return;

            isValid = false;
        }

        $("#applicant_name").on("keyup", function () {
            if ($(this).val().trim() !== "") {
                $("#applicant_name_error").text("");
            }
        });

        $("#business_address").on("keyup", function () {
            if ($(this).val().trim() !== "") {
                $("#business_address_error").text("");
            }
        });

        // -----------------------------------------

        let proprietor = [];

        // Collect all proprietor rows
        $("#proprietor-section tbody tr").each(function () {

            let $tr = $(this);
            let $tds = $tr.find("td");

            let id = $tr.data("id") || null;

            proprietor.push({

                id: id,

                proprietor_name:
                    $tds.eq(0).attr("data-name") ||
                    $tds.eq(0).find("input").val() ||
                    $tds.eq(0).text().trim(),

                fathers_name:
                    $tds.eq(1).text().trim(),

                dob:
                    $tds.eq(2).attr("data-dob") || "",

                age:
                    $tds.eq(2).attr("data-age") || "",

                proprietor_address:
                    $tds.eq(3).text().trim(),

                age_proof:
                    $tds.eq(2).attr("data-age_proof") || "",

                qualification:
                    $tds.eq(4).attr("data-qualification") || "",

                qualification_text:
                    $tds.eq(4).attr("data-qual_text") || "",

                qual_proof:
                    $tds.eq(4).attr("data-qual_proof") || "",

                present_business:
                    $tds.eq(5).text().trim(),

                competency:
                    $tds.eq(6).attr("data-competency") || "",

                competency_certno:
                    $tds.eq(6).attr("data-certno") || "",

                ccfirstissue:
                    $tds.eq(6).attr("data-ccfirstissue") || "",

                ccvalidityfrom:
                    $tds.eq(6).attr("data-ccvalidityfrom") || "",

                ccvalidityto:
                    $tds.eq(6).attr("data-ccvalidityto") || "",

                expverify:
                    $tds.eq(6).attr("data-expverify") || "",

                ownership_type:
                    $tds.eq(9).find("input[name='ownership_type[]']").val() ||
                    $tds.eq(9).attr("data-ownership") ||
                    "",

                row_index:
                    $tr.find("input[name='row_index[]']").val() ||
                    $tr.attr("data-row-index") ||
                    ""

            });

        });

        // Append proprietor JSON
        formData.append(
            "proprietor",
            JSON.stringify(proprietor)
        );

        // Append individually indexed proprietor data
        proprietor.forEach((p, index) => {

            formData.append(`proprietor_id[${index}]`, p.id ?? "");

            formData.append(`proprietor_name[${index}]`, p.proprietor_name);
            formData.append(`fathers_name[${index}]`, p.fathers_name);
            formData.append(`ownership_type[${index}]`, p.ownership_type);

            formData.append(`age[${index}]`, p.age);
            formData.append(`dob[${index}]`, p.dob);

            formData.append(
                `proprietor_address[${index}]`,
                p.proprietor_address
            );

            formData.append(`qualification[${index}]`, p.qualification);
            formData.append(`qual_text[${index}]`, p.qualification_text);

            formData.append(`present_business[${index}]`, p.present_business);

            formData.append(`competency[${index}]`, p.competency);

            formData.append(
                `competency_certno[${index}]`,
                p.competency_certno
            );

            formData.append(`ccfirstissue[${index}]`, p.ccfirstissue);

            formData.append(
                `ccvalidityfrom[${index}]`,
                p.ccvalidityfrom
            );

            formData.append(
                `ccvalidityto[${index}]`,
                p.ccvalidityto
            );

            formData.append(`expverify[${index}]`, p.expverify);

            formData.append(`age_proof[${index}]`, p.age_proof);
            formData.append(`qual_proof[${index}]`, p.qual_proof);

            formData.append(`row_index[${index}]`, p.row_index);

        });

        // partner push-----------------
        let partners = [];

// Collect all partner rows
$("#partner-section table tbody tr").each(function () {

    let $tr = $(this);
    let $tds = $tr.find("td");

    let id = $tr.data("id") || null;

    partners.push({

        id: id,

       proprietor_name:
            $tds.eq(0).attr("data-name") ||
            $tds.eq(0).find("input").val() ||
            $tds.eq(0).text().trim(),

        fathers_name:
            $tds.eq(1).text().trim(),

        dob:
            $tds.eq(2).attr("data-dob") || "",

        age:
            $tds.eq(2).attr("data-age") || "",

        age_proof:
            $tds.eq(2).attr("data-age_proof") || "",

        qual_proof:
            $tds.eq(4).attr("data-qual_proof") || "",

        proprietor_address:
            $tds.eq(3).text().trim(),

        qualification:
            $tds.eq(4).attr("data-qualification") || "",

        qualification_text:
            $tds.eq(4).attr("data-qual_text") || "",

        present_business:
            $tds.eq(5).text().trim(),

        competency:
            $tds.eq(6).attr("data-competency") || "",

        competency_certno:
            $tds.eq(6).attr("data-certno") || "",

        ccfirstissue:
            $tds.eq(6).attr("data-ccfirstissue") || "",

        ccvalidityfrom:
            $tds.eq(6).attr("data-ccvalidityfrom") || "",

        ccvalidityto:
            $tds.eq(6).attr("data-ccvalidityto") || "",

        ccverify:
            $tds.eq(6).attr("data-ccverify") ?? "",

        ownership_type:
            $tds.eq(7).find("input[name='ownership_type[]']").val() ||
            $tds.eq(7).attr("data-ownership") ||
            "pt",

        row_index:
            $tr.attr("data-row-index") || ""

    });
});

// Append partners JSON
formData.append(
    "partners",
    JSON.stringify(partners)
);

// Append individually indexed partner data
partners.forEach((p, index) => {

    formData.append(`partner_id[${index}]`, p.id ?? "");

    formData.append(
        `partner_name[${index}]`,
        p.proprietor_name
    );

    formData.append(
        `partner_fathers_name[${index}]`,
        p.fathers_name
    );

    formData.append(
        `partner_ownership_type[${index}]`,
        p.ownership_type
    );

    formData.append(`partner_age[${index}]`, p.age);
    formData.append(`partner_dob[${index}]`, p.dob);

    formData.append(
        `partner_proprietor_address[${index}]`,
        p.proprietor_address
    );

    formData.append(
        `partner_qualification[${index}]`,
        p.qualification
    );

    formData.append(
        `partner_qual_text[${index}]`,
        p.qualification_text
    );

    formData.append(
        `partner_present_business[${index}]`,
        p.present_business
    );

    formData.append(
        `partner_competency[${index}]`,
        p.competency
    );

    formData.append(
        `partner_competency_certno[${index}]`,
        p.competency_certno
    );

    formData.append(
        `partner_ccfirstissue[${index}]`,
        p.ccfirstissue
    );

    formData.append(
        `partner_ccvalidityfrom[${index}]`,
        p.ccvalidityfrom
    );

    formData.append(
        `partner_ccvalidityto[${index}]`,
        p.ccvalidityto
    );

    formData.append(
        `partner_ccverify[${index}]`,
        p.ccverify
    );

    formData.append(
        `partner_age_proof[${index}]`,
        p.age_proof
    );

    formData.append(
        `partner_qual_proof[${index}]`,
        p.qual_proof
    );

    formData.append(
        `partner_row_index[${index}]`,
        p.row_index
    );

});
        // ------------director push--------------

        let directors = [];

        // Collect all director rows
        $("#director-section table tbody tr").each(function () {

            let $tr = $(this);
            let $tds = $tr.find("td");

            let id = $tr.data("id") || null;

            directors.push({

                id: id,

               proprietor_name:
                $tds.eq(0).attr("data-name") ||
                $tds.eq(0).find("input").val() ||
                $tds.eq(0).text().trim(),

                managing_director:
                    $tds.eq(0).attr("data-managing_director") || "no",

                fathers_name:
                    $tds.eq(1).text().trim(),

                dob:
                    $tds.eq(2).attr("data-dob") || "",

                age:
                    $tds.eq(2).attr("data-age") || "",

                age_proof: $tds.eq(2).attr("data-age_proof") || "",
                qual_proof: $tds.eq(4).attr("data-qual_proof") || "",

                proprietor_address:
                    $tds.eq(3).text().trim(),

                qualification:
                    $tds.eq(4).attr("data-qualification") || "",

                qualification_text:
                    $tds.eq(4).attr("data-qual_text") || "",

                present_business:
                    $tds.eq(5).text().trim(),

                competency:
                    $tds.eq(6).attr("data-competency") || "",

                competency_certno:
                    $tds.eq(6).attr("data-certno") || "",

                ccfirstissue:
                    $tds.eq(6).attr("data-ccfirstissue") || "",

                ccvalidityfrom:
                    $tds.eq(6).attr("data-ccvalidityfrom") || "",

                ccvalidityto:
                    $tds.eq(6).attr("data-ccvalidityto") || "",

                ccverify:
                    $tds.eq(6).attr("data-ccverify") ?? "",

                ownership_type:
                    $tds.eq(7).find("input[name='ownership_type[]']").val() ||
                    $tds.eq(7).attr("data-ownership") ||
                    ""

            });
        });

        // Append directors JSON
        formData.append(
            "directors",
            JSON.stringify(directors)
        );

        // Append individually indexed data
        directors.forEach((p, index) => {

            formData.append(
                `director_id[${index}]`,
                p.id ?? ""
            );

            formData.append(
                `director_name[${index}]`,
                p.proprietor_name
            );

            // Managing Director - keep unchanged
            formData.append(
                `director_managing_director[${index}]`,
                p.managing_director
            );

            formData.append(
                `director_fathers_name[${index}]`,
                p.fathers_name
            );

            formData.append(
                `director_ownership_type[${index}]`,
                p.ownership_type
            );

            formData.append(
                `director_age[${index}]`,
                p.age
            );

            formData.append(
                `director_dob[${index}]`,
                p.dob
            );

            formData.append(
                `director_proprietor_address[${index}]`,
                p.proprietor_address
            );

            formData.append(
                `director_qualification[${index}]`,
                p.qualification
            );

            formData.append(
                `director_qual_text[${index}]`,
                p.qualification_text
            );

            formData.append(
                `director_present_business[${index}]`,
                p.present_business
            );

            formData.append(
                `director_competency[${index}]`,
                p.competency
            );

            formData.append(
                `director_competency_certno[${index}]`,
                p.competency_certno
            );

            formData.append(
                `director_ccfirstissue[${index}]`,
                p.ccfirstissue
            );

            formData.append(
                `director_ccvalidityfrom[${index}]`,
                p.ccvalidityfrom
            );

            formData.append(
                `director_ccvalidityto[${index}]`,
                p.ccvalidityto
            );

            formData.append(
                `director_ccverify[${index}]`,
                p.ccverify
            );

                formData.append(
                `director_age_proof[${index}]`,
                p.age_proof
            );

            formData.append(
                `director_qual_proof[${index}]`,
                p.qual_proof
            );

            formData.append(`director_row_index[${index}]`, p.row_index);

        });

        // If draft and basic checks failed → stop right here
        if (actionType === "draft" && !isValid) {
            return false;
        }

        const allowedTypessize = ["application/pdf"];
        const maxdocSize = 250 * 1024;

        const aadhaarInputFile = document.querySelector(
            'input[type="file"]#aadhaar_doc',
        );
        const aadhaarInputHidden = document.querySelector(
            'input[type="hidden"]#aadhaar_doc',
        );

        let aadhaarFilePresent = false;

        if (aadhaarInputHidden && aadhaarInputHidden.value.trim() !== "") {
            aadhaarFilePresent = true;
        }

        // Aadhaar file validation
        if (aadhaarInputFile && aadhaarInputFile.offsetParent !== null) {
            const file = aadhaarInputFile.files[0];

            if (file) {
                // Check file type and size
                if (!allowedTypessize.includes(file.type)) {
                    $(".aadhaar_doc_error").text("Only PDF files are allowed.");
                    isValid = false;

                    return false;
                } else if (file.size > maxdocSize) {
                    $(".aadhaar_doc_error").text(
                        "File size Permitted Only 5 to 250 KB",
                    );
                    isValid = false;

                    return false;
                } else {
                    $(".aadhaar_doc_error").text("");
                    aadhaarFilePresent = true;
                }
            }
        }

        // PAN

        const panInputFile = document.querySelector(
            'input[type="file"]#pancard_doc',
        );
        const panInputHidden = document.querySelector(
            'input[type="hidden"]#pancard_doc',
        );

        let panFilePresent = false;

        if (panInputHidden && panInputHidden.value.trim() !== "") {
            panFilePresent = true;
        }

        // Aadhaar file validation
        if (panInputFile && panInputFile.offsetParent !== null) {
            const file = panInputFile.files[0];

            if (file) {
                // Check file type and size
                if (!allowedTypessize.includes(file.type)) {
                    $("#pancard_doc_error").text("Only PDF files are allowed.");
                    isValid = false;

                    return false;
                } else if (file.size > maxdocSize) {
                    $("#pancard_doc_error").text(
                        "File size Permitted Only 5 to 250 KB",
                    );
                    isValid = false;

                    return false;
                } else {
                    $("#pancard_doc_error").text("");
                    panFilePresent = true;
                }
            }
        }

        const gstInputFile = document.querySelector('input[type="file"]#gst_doc');
        const gstInputHidden = document.querySelector(
            'input[type="hidden"]#gst_doc',
        );

        let gstFilePresent = false;

        if (gstInputHidden && gstInputHidden.value.trim() !== "") {
            gstFilePresent = true;
        }

        // Aadhaar file validation
        if (gstInputFile && gstInputFile.offsetParent !== null) {
            const file = gstInputFile.files[0];

            if (file) {
                // Check file type and size
                if (!allowedTypessize.includes(file.type)) {
                    $("#gst_doc_error").text("Only PDF files are allowed.");
                    isValid = false;

                    return false;
                } else if (file.size > maxdocSize) {
                    $("#gst_doc_error").text(
                        "File size Permitted Only 5 to 250 KB",
                    );
                    isValid = false;

                    return false;
                } else {
                    $("#gst_doc_error").text("");
                    panFilePresent = true;
                }
            }
        }

        // Clear errors on file change
        $("#aadhaar_doc").on("change", function () {
            $("#aadhaar_doc_error").text("");
        });

        $("#pancard_doc").on("change", function () {
            $("#pancard_doc_error").text("");
        });

        $("#gst_doc").on("change", function () {
            $("#gst_doc_error").text("");
        });

      
    if (isValiddraft) {
        if (actionType === "draft") {
            submitFormAFinalalter(formData, actionType);
            return;
        }
    }
        // -----------------------------------------

        let ownershipType = $("#ownership_type_select").val();

        // Validate ONLY if not draft
        if (ownershipType === "1") {
            $("#ownership_type_error").text("Please select an ownership type");

            $(".nav-item").each(function () {
                if ($(this).text().trim() === "Basic Details") {
                    $(this).addClass("tab-error-bg");
                    $(this).trigger("click");
                }
            });

            const ownershipNotice = document.querySelector(".text-red");
            if (ownershipNotice) {
                ownershipNotice.style.color = "red";
                ownershipNotice.style.fontWeight = "bold";
                ownershipNotice.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }
            // Smooth scroll to the input field
            document
                .getElementById("ownership_type_select")
                .scrollIntoView({ behavior: "smooth", block: "center" });
            isValid = false;
            return;
        } else {
            $("#ownership_type_error").text("");
        }

        // Clear error when user selects a valid option
        $("#ownership_type_select").on("change", function () {
            if ($(this).val() !== "0") {
                $("#ownership_type_error").text("");
            }
        });

        // --------------ownership doc error-----------------------
        function activateBasicDetailsTab() {
            $(".nav-item").each(function () {
                if ($(this).text().trim() === "Basic Details") {
                    $(this).addClass("tab-error-bg");
                    $(this).trigger("click");
                }
            });

            const ownershipNotice = document.querySelector(".text-red");
            if (ownershipNotice) {
                ownershipNotice.style.color = "red";
                ownershipNotice.style.fontWeight = "bold";
                ownershipNotice.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }
        }

        // Clear previous errors
        $("#partnership_deed_error").text("");
        $("#director_mom_error").text("");

        // Get file link container
        let ownershipFileLink = $(".file-link a").length; // check if any file link exists

        // ================= PARTNERSHIP =================
        if (ownershipType === "pt") {
            if (ownershipFileLink === 0) {
                activateBasicDetailsTab();

                $("#partnership_deed_error").text("Partnership Deed is required");

                document
                    .getElementById("partnershipdeed")
                    .scrollIntoView({ behavior: "smooth", block: "center" });

                isValid = false;
                return;
            }
        }

        // ================= PRIVATE / LTD =================
        else if (ownershipType === "pvt" || ownershipType === "ltd") {
            if (ownershipFileLink === 0) {
                activateBasicDetailsTab();

                $("#director_mom_error").text("Director MOM is required");

                document
                    .getElementById("directormom")
                    .scrollIntoView({ behavior: "smooth", block: "center" });

                isValid = false;
                return;
            }
        }

        // ---------------------ownership type validation------------------------------
        if (
            proprietor.length === 0 &&
            partners.length === 0 &&
            directors.length === 0
        ) {
            Swal.fire({
                icon: "error",
                width: 450,
                title: "Missing Details",
                text: "Please choose an ownership type and enter details for Proprietor, Partner, or Director in Basic Detail Section.",
            });

            $(".nav-item").each(function () {
                if ($(this).text().trim() === "Basic Details") {
                    $(this).addClass("tab-error-bg");
                    $(this).trigger("click"); // switch to Basic Details tab
                }
            });

            const ownershipNotice = document.querySelector(".text-red");
            if (ownershipNotice) {
                ownershipNotice.style.color = "red";
                ownershipNotice.style.fontWeight = "bold";
                ownershipNotice.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }

            return;
        }



        // ------------------ 3. Previous Contractor License ------------------
        let previousSelected = $(
            'input[name="previous_contractor_license"]:checked'
        ).val();

        if (!previousSelected) {

            $("#previous_contractor_license_error").text(
                "Select Yes or No for previous application."
            );

            isValid = false;

            showBasicDetailsTab();

        } else if (previousSelected === "yes") {

            let prevAppNo = $("#previous_application_number").val().trim();

            $("#previous_application_number").next(".error").remove();
            $("#previous_application_number_error").remove();

            if (prevAppNo === "") {

                $("#previous_application_number").after(
                    '<span class="error text-danger d-block">' +
                    'Previous Licence Number is required.' +
                    '</span>'
                );

                isValid = false;

                showBasicDetailsTab();

            } else if (!/^(EA|L)/i.test(prevAppNo)) {

                $("#previous_application_number").after(
                    '<span id="previous_application_number_error" class="error text-danger d-block">' +
                    'License number must start with "EA" or "L".' +
                    '</span>'
                );

                $("#previous_application_number").addClass("input-error");

                isValid = false;

                showBasicDetailsTab();

                document
                    .getElementById("previous_application_number")
                    .scrollIntoView({
                        behavior: "smooth",
                        block: "center"
                    });

                return;
            }

            // First Issue
            let firstIssue = $("#previous_validity_first_issue").val().trim();

            $("#previous_validity_first_issue").next(".error").remove();

            if (firstIssue === "") {

                $("#previous_validity_first_issue").after(
                    '<span class="error text-danger d-block">' +
                    'Licence Date of First Issue is required.' +
                    '</span>'
                );

                isValid = false;

                showBasicDetailsTab();
            }

            // Validity From
            let validityFrom = $("#previous_validity_from").val().trim();

            $("#previous_validity_from").next(".error").remove();

            if (validityFrom === "") {

                $("#previous_validity_from").after(
                    '<span class="error text-danger d-block">' +
                    'Licence Validity From is required.' +
                    '</span>'
                );

                isValid = false;

                showBasicDetailsTab();
            }

            // Validity To
            let validityTo = $("#previous_validity_to").val().trim();

            $("#previous_validity_to").next(".error").remove();

            if (validityTo === "") {

                $("#previous_validity_to").after(
                    '<span class="error text-danger d-block">' +
                    'Licence Validity To is required.' +
                    '</span>'
                );

                isValid = false;

                showBasicDetailsTab();
            }
        }

        $('input[name="previous_contractor_license"]').on("change", function () {
            $("#previous_contractor_license_error").text("");
        });

        $("#previous_application_number").on("keyup", function () {
            $(this).next(".error").remove();
            $("#previous_application_number_error").remove();
            $(this).removeClass("input-error");
        });

        $("#previous_validity_first_issue").on("change", function () {
            $(this).next(".error").remove();
        });

        $("#previous_validity_from").on("change", function () {
            $(this).next(".error").remove();
        });

        $("#previous_validity_to").on("change", function () {
            $(this).next(".error").remove();
        });

        function showBasicDetailsTab() {
            $(".nav-item").each(function () {

                if ($(this).text().trim() === "Basic Details") {

                    $(this).addClass("tab-error-bg");

                    $(this).trigger("click");

                    return false;
                }

            });
        }
        // ======================================================
        // 5A STAFF QC/QSC VALIDATION - FIRST PRIORITY
        // ======================================================

        // ======================================================
        // QC / QSC STAFF VALIDATION
        // ======================================================

        let qcStaffRecords = [];


        // ==========================================================
        // GET ALL QC / QSC STAFF ROWS
        // ==========================================================

        $("#staffqc-records tr.staffqc-record").each(function (index) {

            let $tr = $(this);

            // ------------------------------------------------------
            // CATEGORY
            // ------------------------------------------------------

            let category = $tr
                .find('input[name="staffqc_category[]"]')
                .val();

            // Fallback for dynamically created rows
            if (!category) {
                category = $tr
                    .find('input[name^="staffqc_category["]')
                    .val();
            }

            if (!category) {
                category = $tr
                    .find(".staffqc_category")
                    .val();
            }

            if (!category) {
                category = $tr.find("td:eq(1)").text().trim();
            }

            category = String(category || "")
                .trim()
                .toUpperCase();


            // ------------------------------------------------------
            // ONLY QC / QSC
            // ------------------------------------------------------

            if (category !== "QC" && category !== "QSC") {
                return;
            }


            // ------------------------------------------------------
            // CREATE RECORD
            // ------------------------------------------------------

            let record = {

                id:
                    $tr.attr("data-id") ||
                    $tr.data("id") ||
                    null,

                staffqc_category:
                    category,

                // ------------------------------------------------------
                // CERTIFICATE HOLDER NAME
                // ------------------------------------------------------

                certificate_holder_name:
                    $tr.find('input[name="certificate_holder_name[]"]').val()
                    ||
                    $tr.find('input[name^="certificate_holder_name["]').val()
                    ||
                    $tr.find(".certificate_holder_name").val()
                    ||
                    $tr.find(".certificate-holder-name").text().trim()
                    ||
                    $tr.find("td:eq(2)").text().trim()
                    ||
                    "",

                // ------------------------------------------------------
                // CERTIFICATE NUMBER
                // ------------------------------------------------------

                staff_cc_no:
                    $tr.find('input[name="staff_cc_no[]"]').val()
                    ||
                    $tr.find('input[name^="staff_cc_no["]').val()
                    ||
                    $tr.find(".staff_cc_no").val()
                    ||
                    $tr.find(".cc_number").val()
                    ||
                    $tr.find("td:eq(3)").text().trim()
                    ||
                    "",

                // ------------------------------------------------------
                // FIRST ISSUE
                // ------------------------------------------------------

                staff_cc_first_issue:
                    $tr.find('input[name="staff_cc_first_issue[]"]').val()
                    ||
                    $tr.find('input[name^="staff_cc_first_issue["]').val()
                    ||
                    $tr.find(".staff_cc_first_issue").val()
                    ||
                    $tr.find(".cc_firstissue").val()
                    ||
                    "",

                // ------------------------------------------------------
                // VALIDITY FROM
                // ------------------------------------------------------

                staff_cc_validity_from:
                    $tr.find('input[name="staff_cc_validity_from[]"]').val()
                    ||
                    $tr.find('input[name^="staff_cc_validity_from["]').val()
                    ||
                    $tr.find(".staff_cc_validity_from").val()
                    ||
                    $tr.find(".cc_validity_from").val()
                    ||
                    "",

                // ------------------------------------------------------
                // VALIDITY TO
                // ------------------------------------------------------

                staff_cc_validity_to:
                    $tr.find('input[name="staff_cc_validity_to[]"]').val()
                    ||
                    $tr.find('input[name^="staff_cc_validity_to["]').val()
                    ||
                    $tr.find(".staff_cc_validity_to").val()
                    ||
                    $tr.find(".cc_validity_to").val()
                    ||
                    "",

                // ------------------------------------------------------
                // APP DOCUMENT
                // ------------------------------------------------------

                app_doc:
                    $tr.find('input[name="app_doc[]"]').val()
                    ||
                    $tr.find('input[name^="app_doc["]').val()
                    ||
                    "",

                // ------------------------------------------------------
                // CONSENT DOCUMENT
                // ------------------------------------------------------

                cons_doc:
                    $tr.find('input[name="cons_doc[]"]').val()
                    ||
                    $tr.find('input[name^="cons_doc["]').val()
                    ||
                    "",

                // ------------------------------------------------------
                // ROW INDEX
                // ------------------------------------------------------

                row_index:
                    $tr.attr("data-row-index")
                    ||
                    $tr.data("row-index")
                    ||
                    $tr.find('input[name="row_index[]"]').val()
                    ||
                    ""
            };


            // ------------------------------------------------------
            // ADD RECORD
            // ------------------------------------------------------

            qcStaffRecords.push(record);

        });

        // ==========================================================
        // MINIMUM ONE QC / QSC
        // ==========================================================

        if (qcStaffRecords.length === 0) {

            isValid = false;

            $("#qc-staff-table").addClass("qc-table-error");


            Swal.fire({
                icon: "warning",
                title: "Staff Details Required",
                text: "Please enter minimum one QC/QSC staff.",
                confirmButtonText: "OK",
                width: 450
            });


            // Open Staff & Bank Details tab
            $(".nav-item").each(function () {

                if (
                    $(this)
                        .text()
                        .trim()
                        .includes("Staff & Bank Details")
                ) {

                    $(this).addClass("tab-error-bg");

                    $(this).trigger("click");

                    return false;
                }

            });


            return;
        }


        // ==========================================================
        // QC/QSC EXISTS
        // ==========================================================

        $("#qc-staff-table").removeClass("qc-table-error");


        $(".nav-item").each(function () {

            if (
                $(this)
                    .text()
                    .trim()
                    .includes("Staff & Bank Details")
            ) {

                $(this).removeClass("tab-error-bg");

                return false;
            }

        });


        // ==========================================================
        // ADD QC RECORDS TO FORMDATA
        // ==========================================================

        formData.set(
            "qc_staff_records",
            JSON.stringify(qcStaffRecords)
        );


        // ======================================================
        // STAFF B VALIDATION - SECOND PRIORITY
        // FIRST 2 STAFF MUST BE CATEGORY B
        // ALL CERTIFICATE FIELDS ARE MANDATORY
        // ======================================================


        // ======================================================
        // MANDATORY STAFF VALIDATION
        // ======================================================

        let $mandatoryStaffRows =
            $("#staff-container tr.staff-fields");

        let mandatoryStaffRowCount =
            $mandatoryStaffRows.length;

        $("#staff-container .staff-validation-error").remove();

        let staffValidationPassed = true;


        // ======================================================
        // CHECK MINIMUM 2 STAFF
        // ======================================================

        if (mandatoryStaffRowCount < 2) {

            isValid = false;
            staffValidationPassed = false;

            Swal.fire({
                icon: "warning",
                title: "Staff Details Required",
                text: "Minimum 2 B staff members are mandatory.",
                confirmButtonText: "OK",
                width: 450
            });


            // Open Staff & Bank Details tab
            $(".nav-item").each(function () {

                if (
                    $(this)
                        .text()
                        .trim()
                        .includes("Staff & Bank Details")
                ) {

                    $(this).addClass("tab-error-bg");

                    $(this).trigger("click");

                    return false;
                }
            });


            $("#staff-container").after(

                '<span class="staff-validation-error error text-danger d-block">' +
                'Minimum 2 B staff members are mandatory.' +
                '</span>'
            );


            return;
        }


        // ======================================================
        // CLEAR PREVIOUS STAFF VALIDATION ERRORS
        // ======================================================

        $(".staff-validation-error").remove();


        // ======================================================
        // VALIDATE STAFF
        // ======================================================

        let staffValidationRequests = [];

        let staffBasicValidationPassed = true;


        // ======================================================
        // GLOBAL DUPLICATE CERTIFICATE NUMBER CHECK
        // B / C / QC / QSC
        // ======================================================

        let allStaffCCNumbers = {};


        // ======================================================
        // 1. COLLECT B / C CERTIFICATE NUMBERS
        // ======================================================

        $mandatoryStaffRows.each(function (index) {

            let $row = $(this);
            

            

            // ==================================================
            // CATEGORY
            // ==================================================

            let category = (
                $row.find(".staff_category").val() || ""
            ).trim().toUpperCase();


            // First 2 rows are mandatory B
            if (index < 2) {
                category = "B";
            }


            // ==================================================
            // OTHERS
            // No certificate number validation
            // ==================================================

            if (category === "OTHERS") {
                return;
            }


            // ==================================================
            // ONLY B / C
            // ==================================================

            if (category !== "B" && category !== "C") {
                return;
            }


            // ==================================================
            // CERTIFICATE NUMBER
            // ==================================================

            let ccNumber = (
                $row.find(".cc_number").val() || ""
            ).trim();


            // Empty value handled by normal required validation
            if (ccNumber === "") {
                return;
            }


            // Normalize for duplicate comparison
            let normalizedCC = ccNumber.toUpperCase();


            // ==================================================
            // CREATE ARRAY
            // ==================================================

            if (!allStaffCCNumbers[normalizedCC]) {
                allStaffCCNumbers[normalizedCC] = [];
            }


            // ==================================================
            // STORE B / C RECORD
            // ==================================================

            allStaffCCNumbers[normalizedCC].push({

                type: "mandatory",

                row: $row,

                category: category,

                ccNumber: ccNumber

            });

        });


        // ======================================================
        // 2. COLLECT QC / QSC CERTIFICATE NUMBERS
        // ======================================================

        $("#staffqc-records tr.staffqc-record").each(function (index) {

            let $tr = $(this);


            // ==================================================
            // CATEGORY
            // ==================================================

            let category =
                $tr.find('input[name="staffqc_category[]"]').val()
                ||
                $tr.find('input[name^="staffqc_category["]').val()
                ||
                $tr.find(".staffqc_category").val()
                ||
                $tr.find("td:eq(1)").text().trim()
                ||
                "";


            category = String(category)
                .trim()
                .toUpperCase();


            // ==================================================
            // ONLY QC / QSC
            // ==================================================

            if (category !== "QC" && category !== "QSC") {
                return;
            }


            // ==================================================
            // CERTIFICATE NUMBER
            // ==================================================

            // let ccNumber =
            //     $tr.find('input[name="staff_cc_no[]"]').val()
            //     ||
            //     $tr.find('input[name^="staff_cc_no["]').val()
            //     ||
            //     $tr.find(".staff_cc_no").val()
            //     ||
            //     $tr.find(".cc_number").val()
            //     ||
            //     $tr.find("td:eq(3)").text().trim()
            //     ||
            //     "";


            // ccNumber = String(ccNumber).trim();

            let ccNumber = String(
                $tr.find('input[name="staff_cc_no[]"]').first().val() ||
                $tr.find(".staff_cc_no").val() ||
                $tr.find(".cc_number").val() ||
                $tr.find("td:eq(2)").text().trim() ||
                ""
            ).trim();


            // Empty value
            if (ccNumber === "") {
                return;
            }


            // Normalize for duplicate comparison
            let normalizedCC = ccNumber.toUpperCase();


            // ==================================================
            // CREATE ARRAY
            // ==================================================

            if (!allStaffCCNumbers[normalizedCC]) {
                allStaffCCNumbers[normalizedCC] = [];
            }


            // ==================================================
            // STORE QC / QSC RECORD
            // ==================================================

            allStaffCCNumbers[normalizedCC].push({

                type: "qc",

                row: $tr,

                category: category,

                ccNumber: ccNumber

            });

        });


        // ======================================================
        // 3. CHECK DUPLICATE CERTIFICATE NUMBERS
        // ======================================================

        let duplicateStaffCCFound = false;


        Object.keys(allStaffCCNumbers).forEach(function (normalizedCC) {

            let records =
                allStaffCCNumbers[normalizedCC];


            // ==================================================
            // MORE THAN ONE = DUPLICATE
            // ==================================================

            if (records.length > 1) {

                duplicateStaffCCFound = true;


                // ==================================================
                // SHOW ERROR IN EVERY DUPLICATE RECORD
                // ==================================================

                records.forEach(function (record) {

                    let $row = record.row;


                    // ==================================================
                    // NORMAL B / C STAFF
                    // ==================================================

                    if (record.type === "mandatory") {

                        $row
                            .find(".duplicate-cc-error")
                            .remove();


                        let $ccField =
                            $row.find(".cc_number").first();


                        if ($ccField.length) {

                            $ccField.after(

                                '<span class="duplicate-cc-error staff-validation-error error text-danger d-block">' +
                                'Duplicate Certificate Number. This certificate number is already used in another staff row.' +
                                '</span>'

                            );

                        }

                    }


                    // ==================================================
                    // QC / QSC STAFF
                    // ==================================================

                    if (record.type === "qc") {

                        $row
                            .find(".duplicate-cc-error")
                            .remove();


                        let $ccField =
                            $row.find(
                                'input[name="staff_cc_no[]"],' +
                                'input[name^="staff_cc_no["],' +
                                '.staff_cc_no,' +
                                '.cc_number'
                            ).first();


                        if ($ccField.length) {

                            $ccField.after(

                                '<span class="duplicate-cc-error staff-validation-error error text-danger d-block">' +
                                'Duplicate Certificate Number. This certificate number is already used in another staff row.' +
                                '</span>'

                            );

                        } else {

                            // Fallback if certificate number is table text

                            $row
                                .find("td:eq(3)")
                                .append(

                                    '<span class="duplicate-cc-error staff-validation-error error text-danger d-block">' +
                                    'Duplicate Certificate Number. This certificate number is already used in another staff row.' +
                                    '</span>'

                                );

                        }

                    }

                });

            }

        });


        // ======================================================
        // DUPLICATE CERTIFICATE VALIDATION FAILED
        // ======================================================

        if (duplicateStaffCCFound) {

            isValid = false;

            staffBasicValidationPassed = false;


            Swal.fire({
                icon: "warning",
                title: "Duplicate Certificate Number",
                text: "The same certificate number cannot be used for multiple staff members.",
                confirmButtonText: "OK",
                width: 500
            });


            // ==================================================
            // OPEN STAFF & BANK DETAILS TAB
            // ==================================================

            $(".nav-item").each(function () {

                if (
                    $(this)
                        .text()
                        .trim()
                        .includes("Staff & Bank Details")
                ) {

                    $(this).addClass("tab-error-bg");

                    $(this).trigger("click");

                    return false;
                }

            });


            // ==================================================
            // SCROLL TO STAFF TABLE
            // ==================================================

            setTimeout(function () {

                if ($("#staff-table").length) {

                    $("#staff-table")[0].scrollIntoView({

                        behavior: "smooth",

                        block: "center"

                    });

                }

            }, 300);


            // VERY IMPORTANT
            return;
        }


        // ======================================================
        // VALIDATE ALL STAFF ROWS
        // B + C = CERTIFICATE VALIDATION
        // OTHERS = SKIP CERTIFICATE VALIDATION
        // ======================================================

        $mandatoryStaffRows.each(function (index) {

            let $row = $(this);

            
            // ==================================================
            // CATEGORY
            // ==================================================

            let category =
                ($row.find(".staff_category").val() || "")
                    .trim();


            // First 2 rows are mandatory B
            if (index < 2) {
                category = "B";
            }


            // ==================================================
            // OTHERS
            // No certificate validation required
            // ==================================================

            if (category === "OTHERS") {

                return;
            }


            // ==================================================
            // ONLY B AND C REQUIRE CERTIFICATE DETAILS
            // ==================================================

            if (
                category !== "B" &&
                category !== "C"
            ) {

                staffBasicValidationPassed = false;


                $row.find(".staff_category").after(

                    '<span class="staff-validation-error error text-danger d-block">' +
                    'Please select a valid staff category.' +
                    '</span>'

                );


                return;
            }


            // ==================================================
            // CERTIFICATE NUMBER
            // ==================================================

            let ccNumber =
                ($row.find(".cc_number").val() || "")
                    .trim();


            // ==================================================
            // CERTIFICATE HOLDER NAME
            // ==================================================

            let ccName =
                (
                    $row.find(
                        ".certificate_holder_name_others"
                    ).val() || ""
                ).trim();


            // ==================================================
            // FIRST ISSUE
            // ==================================================

            let firstIssue =
                (
                    $row.find(".cc_firstissue").val() || ""
                ).trim();


            // ==================================================
            // VALIDITY FROM
            // ==================================================

            let validityFrom =
                (
                    $row.find(".cc_validity_from").val() || ""
                ).trim();


            // ==================================================
            // VALIDITY TO
            // ==================================================

            let validityTo =
                (
                    $row.find(".cc_validity_to").val() || ""
                ).trim();



            // ==================================================
            // CERTIFICATE NUMBER REQUIRED
            // ==================================================

            if (ccNumber === "") {

                staffBasicValidationPassed = false;


                $row.find(".cc_number").after(

                    '<span class="staff-validation-error error text-danger d-block">' +
                    'Certificate Number is required.' +
                    '</span>'

                );


                return;
            }


            // ==================================================
            // FIRST ISSUE REQUIRED
            // ==================================================

            if (firstIssue === "") {

                staffBasicValidationPassed = false;


                $row.find(".cc_firstissue").after(

                    '<span class="staff-validation-error error text-danger d-block">' +
                    'Certificate First Issue is required.' +
                    '</span>'

                );


                return;
            }


            // ==================================================
            // VALIDITY FROM REQUIRED
            // ==================================================

            if (validityFrom === "") {

                staffBasicValidationPassed = false;


                $row.find(".cc_validity_from").after(

                    '<span class="staff-validation-error error text-danger d-block">' +
                    'Certificate Validity From is required.' +
                    '</span>'

                );


                return;
            }


            // ==================================================
            // VALIDITY TO REQUIRED
            // ==================================================

            if (validityTo === "") {

                staffBasicValidationPassed = false;


                $row.find(".cc_validity_to").after(

                    '<span class="staff-validation-error error text-danger d-block">' +
                    'Certificate Validity To is required.' +
                    '</span>'

                );


                return;
            }


            let holderName = (
                    $row.find(".certificate_holder_name_others").val() || ""
                ).trim();

                let $holderColumn = $row.find(".certificate-holder-column");
                let $verifyButton = $row.find(".verify-otherstaff");

                // If the holder name is already displayed, remove the error
                let displayedHolderName = (
                    $row.find(".certificate-holder").text() || ""
                ).trim();

                if (holderName !== "" || displayedHolderName !== "") {
                    $holderColumn.find(".holder-name-error").remove();
                    $verifyButton.removeClass("btn-danger");
                } else {
                    $holderColumn.find(".holder-name-error").remove();

                    staffBasicValidationPassed = false;

                    $holderColumn.append(
                        '<span class="holder-name-error text-danger d-block mt-1">' +
                        'Certificate holder name is missing. Please verify first.' +
                        '</span>'
                    );

                    $verifyButton.addClass("btn-danger");

                    return;
                }

                    $holderColumn.find(".holder-name-error").remove();
                $verifyButton.removeClass("btn-danger");

            // Holder name exists: remove Verify button highlight
            $verifyButton.removeClass("btn-danger");


            // ==================================================
            // SEND B / C CERTIFICATE TO CONTROLLER
            // ==================================================

            let request = validatealterStaffCertificate(

                category,

                ccNumber,

                firstIssue,

                validityFrom,

                validityTo,

                  license_number


            ).then(function (response) {

                    // ==================================================
                    // CERTIFICATE VALIDATION FAILED
                    // ==================================================

                    if (response.status !== true) {

                        staffValidationPassed = false;


                        $row.find(".cc_number").after(

                            '<span class="staff-validation-error error text-danger d-block">' +
                            (
                                response.message ||
                                "Certificate validation failed."
                            ) +
                            '</span>'

                        );


                        return false;
                    }


                    // /check-cc-certificate confirms the certificate only.
                    // It does not return a holder name, so keep the name
                    // already set by Verify.
                    let returnedHolderName = (
                        response.certificate_holder_name_others || ""
                    ).trim();

                    if (returnedHolderName !== "") {

                        $row.find(".certificate-holder")

                            .text(returnedHolderName)

                            .removeClass("text-danger")

                            .addClass("text-success");


                        $row.find(".certificate_holder_name_others")
                            .val(returnedHolderName);

                    }
                    return true;
                }).catch(function (xhr) {


                    staffValidationPassed = false;


                    $row.find(".cc_number").after(

                        '<span class="staff-validation-error error text-danger d-block">' +
                        'Unable to verify certificate. Please try again.' +
                        '</span>'

                    );


                    return false;

                });


            staffValidationRequests.push(request);

        });

        // ======================================================
        // BASIC VALIDATION FAILED
        // ======================================================

        if (!staffBasicValidationPassed) {

            isValid = false;

            Swal.fire({

                icon: "warning",

                title: "Staff Details Incomplete",

                text: "Please complete all mandatory staff certificate details.",

                confirmButtonText: "OK",

                width: 500

            });


            $(".nav-item").each(function () {

                if (
                    $(this)
                        .text()
                        .trim()
                        .includes("Staff & Bank Details")
                ) {

                    $(this).addClass("tab-error-bg");

                    $(this).trigger("click");

                    return false;
                }

            });


            setTimeout(function () {

                if ($("#staff-table").length) {

                    $("#staff-table")[0].scrollIntoView({

                        behavior: "smooth",

                        block: "center"

                    });

                }

            }, 300);


            return;
        }


        // ======================================================
        // WAIT FOR ALL B + C CERTIFICATE CHECKS
        // ======================================================

        let results = await Promise.all(
            staffValidationRequests
        );


        // ======================================================
        // CHECK ALL CERTIFICATES
        // ======================================================

        let allCertificatesValid =
            results.every(function (result) {

                return result === true;

            });


        // ======================================================
        // CERTIFICATE VALIDATION FAILED
        // ======================================================

        if (!allCertificatesValid) {

            isValid = false;



            Swal.fire({

                icon: "warning",

                title: "Invalid Staff Certificate",

                text: "Please correct the invalid B/C staff certificate details.",

                confirmButtonText: "OK",

                width: 500

            });


            $(".nav-item").each(function () {

                if (
                    $(this)
                        .text()
                        .trim()
                        .includes("Staff & Bank Details")
                ) {

                    $(this).addClass("tab-error-bg");

                    $(this).trigger("click");

                    return false;
                }

            });


            setTimeout(function () {

                if ($("#staff-table").length) {

                    $("#staff-table")[0].scrollIntoView({

                        behavior: "smooth",

                        block: "center"

                    });

                }

            }, 300);


            // VERY IMPORTANT
            return false;
        }
        // ======================================================
        // CLEAR STAFF ERRORS WHEN USER CHANGES/ENTERS DATA
        // ======================================================

        $(document).on("input change", "#staff-container .cc_number", function () {

            $(this)
                .siblings(".staff-validation-error")
                .remove();

        });

        $(document).on("input change", "#staff-container .cc_firstissue", function () {

            $(this)
                .siblings(".staff-validation-error")
                .remove();

        });

        $(document).on("input change", "#staff-container .cc_validity_from", function () {

            $(this)
                .siblings(".staff-validation-error")
                .remove();

        });

        $(document).on("input change", "#staff-container .cc_validity_to", function () {

            $(this)
                .siblings(".staff-validation-error")
                .remove();

        });

        $(document).on("change", "#staff-container .staff_category", function () {

            $(this)
                .siblings(".staff-validation-error")
                .remove();

        });
        // ---------------- 7 Bank------------------------

        let bankAddress = $("textarea[name='bank_address']").val().trim();
        let bankAmount = $("#bank_amount").val().trim();
        let bankValidity = $("input[name='bank_validity']").val().trim();

      
        function checkBankValidity(bankValidityValue) {
            let isValid = true;

            $.ajax({
                url: BASE_URL + "/checkBankValidity",
                type: "POST",
                async: false,
                data: {
                    _token: $('meta[name="csrf-token"]').attr("content"),
                    bank_validity: bankValidityValue,
                },
                success: function (res) {
                    if (res.status === "invalid_bank") {
                        $("#bank_validity_error").text(res.msg);
                        isValid = false;
                    } else {
                        $("#bank_validity_error").text("");
                    }
                },
            });

            return isValid;
        }


        if (
            bankValidity === "" ||
            bankAmount === "" ||
            Number(bankAmount) <= 10 ||
            bankAddress === ""
        ) {

            if (bankValidity === "") {
                $("#bank_validity_error").text("Validity period is required.");
            }

            if (bankAmount === "") {
                $("#bank_amount_error").text("Amount is required.");
            } else if (Number(bankAmount) <= 10) {
                $("#bank_amount_error").text("Amount must be greater than 10.");
            }

            if (bankAddress === "") {
                $("#bank_address_error").text("Bank name and address is required.");
            }

            $(".nav-item").each(function () {
                if ($(this).text().trim() === "Staff & Bank Details") {
                    $(this).addClass("tab-error-bg");
                    $(this).trigger("click");
                }
            });

            const ownershipNotice = document.querySelector(".text-red");

            if (ownershipNotice) {
                ownershipNotice.style.color = "red";
                ownershipNotice.style.fontWeight = "bold";
                ownershipNotice.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
            }

            isValid = false;
            return;
        }

        let hasUploadedFile = $("#bank_doc_section .file-link a").length > 0;

        if (!hasUploadedFile) {


            $("#bank_doc_error").text("Bank Solvency Document must be uploaded.");

            $(".nav-item").each(function () {
                if ($(this).text().trim() === "Staff & Bank Details") {
                    $(this).addClass("tab-error-bg");
                    $(this).trigger("click");
                }
            });

            document.getElementById("bank_doc_input").scrollIntoView({
                behavior: "smooth",
                block: "center",
            });

            return;
            isValid = false;
        }

        // if (bankAmount === "") {
        //     $("#bank_amount_error").text("Amount is required.");
        //     isValid = false;
        // }

        // Clear bank_address error on typing
        $("textarea[name='bank_address']").on("keyup", function () {
            if ($(this).val().trim() !== "") {
                $("#bank_address_error").text("");
            }
        });

        // Clear bank_validity error on typing
        $("input[name='bank_validity']").on("keyup change", function () {
            if ($(this).val().trim() !== "") {
                $("#bank_validity_error").text("");
            }
        });

        // Clear bank_amount error on typing
        $("#bank_amount").on("keyup change", function () {
            if ($(this).val().trim() !== "") {
                $("#bank_amount_error").text("");
            }
        });

        // =====================================================
        // 8. SIGNATORY
        // =====================================================
        // AUTHORITY / SIGNATORY VALIDATION
        // =====================================================

        $("#authority-names-table .authority-field-error").remove();

        let authorityRows = $("#authority-names-table tbody tr");

        authorityRows.each(function (index) {

            let row = $(this);

            let nameInput = row.find(
                "input[name='name_of_authorised_to_sign[]']"
            );

            let ageInput = row.find(
                "input[name='age_of_authorised_to_sign[]']"
            );

            let qualificationInput = row.find(
                "input[name='qualification_of_authorised_to_sign[]']"
            );

            let designationInput = row.find(
                "input[name='designation_of_authorised_to_sign[]']"
            );

            let specimenInput = row.find(
                "input[name='specimen_sign[]'], input[name='specimen_sign']"
            );

            let name = nameInput.val()
                ? nameInput.val().trim()
                : "";

            // =================================================
            // Only validate the row if NAME is entered
            // =================================================

            if (name !== "") {

                let age = ageInput.val()
                    ? ageInput.val().trim()
                    : "";

                let qualification = qualificationInput.val()
                    ? qualificationInput.val().trim()
                    : "";

                let designation = designationInput.val()
                    ? designationInput.val().trim()
                    : "";

                // =================================================
                // NAME
                // =================================================

                if (name === "") {

                    nameInput.after(
                        '<span class="text-danger authority-field-error">' +
                        'Name of authorised signatory is required.' +
                        '</span>'
                    );

                    isValid = false;
                }

                // =================================================
                // AGE
                // =================================================

                if (age === "") {

                    ageInput.after(
                        '<span class="text-danger authority-field-error">' +
                        'Age of authorised signatory is required.' +
                        '</span>'
                    );

                    isValid = false;

                } else if (parseInt(age) < 15 || parseInt(age) > 70) {

                    ageInput.after(
                        '<span class="text-danger authority-field-error">' +
                        'Age must be between 15 and 70.' +
                        '</span>'
                    );

                    isValid = false;
                }

                // =================================================
                // QUALIFICATION
                // =================================================

                if (qualification === "") {

                    qualificationInput.after(
                        '<span class="text-danger authority-field-error">' +
                        'Qualification is required.' +
                        '</span>'
                    );

                    isValid = false;
                }

                // =================================================
                // DESIGNATION
                // =================================================

                if (designation === "") {

                    designationInput.after(
                        '<span class="text-danger authority-field-error">' +
                        'Designation is required.' +
                        '</span>'
                    );

                    isValid = false;
                }

                // =================================================
                // SPECIMEN SIGNATURE UPLOAD
                // =================================================

                let hasSpecimenFile = row.find(
                    ".file-link a"
                ).length > 0;

                if (!hasSpecimenFile) {

                    // Prevent duplicate error
                    row.find(".specimen-sign-error").remove();

                    specimenInput.after(
                        '<span class="text-danger authority-field-error specimen-sign-error">' +
                        'Specimen signature must be uploaded.' +
                        '</span>'
                    );

                    isValid = false;
                }
            }
        });


        // =====================================================
        // IF AUTHORITY / SIGNATORY VALIDATION FAILED
        // =====================================================

        if (!isValid) {

            $(".nav-item").each(function () {

                if ($(this).text().trim() === "Staff & Bank Details") {

                    $(this).addClass("tab-error-bg");
                    $(this).trigger("click");
                }
            });

            const authorityError = document.querySelector(
                "#authority-names-table .authority-field-error"
            );

            if (authorityError) {

                authorityError.scrollIntoView({
                    behavior: "smooth",
                    block: "center"
                });
            }

            return;
        }


        // -----------------Address proof------------------------------

        let addressValid = true;

        // 1️⃣ Type Validation
        let typeDoc = $("#type_doc").val();

        if (!typeDoc) {
            $("#type_error").text("Please select Address Proof Type");
            addressValid = false;
        } else {
            $("#type_error").text("");
        }

        // 2️⃣ Address Proof Number Validation
        let proofNo = $("#addressproofno").val().trim();

        if (proofNo === "") {
            $("#addressproofno_error").text("Address Proof Number is required");
            addressValid = false;
        } else {
            $("#addressproofno_error").text("");
        }

        // 3️⃣ File Validation (Uploaded OR Present)
        let hasAddressFile =
            $("#address_proof .file-link:not(.d-none) a").length > 0;

        if (!hasAddressFile) {
            $("#gst_doc_error").text("Address Proof Document must be uploaded");
            addressValid = false;
        } else {
            $("#gst_doc_error").text("");
        }

        // ❌ IF INVALID
        if (!addressValid) {

            $(".nav-item").each(function () {
                if ($(this).text().trim() === "Staff & Bank Details") {
                    $(this).addClass("tab-error-bg");
                    $(this).trigger("click");
                }
            });

            document.getElementById("address_proof").scrollIntoView({
                behavior: "smooth",
                block: "center",
            });

            return false;
        }

        // Clear type error
        $(document).on("change", "#type_doc", function () {
            if ($(this).val()) {
                $("#type_error").text("");
            }
        });

        // Clear number error
        $(document).on("keyup", "#addressproofno", function () {
            if ($(this).val().trim() !== "") {
                $("#addressproofno_error").text("");
            }
        });

        // Declaration Checkboxes
        const declaration1Checked = $("#declarationCheckbox").is(":checked");
        const declaration2Checked = $("#declarationCheckbox1").is(":checked");

        if (!declaration1Checked) {
            $("#declaration3_error").text(
                "⚠ Please check this declaration before proceeding.",
            );
            isValid = false;
        }

        if (!declaration2Checked) {
            $("#declaration4_error").text(
                "⚠ Please check this declaration before proceeding.",
            );
            isValid = false;
        }

        // Clear errors on change
        $("#declarationCheckbox").on("change", function () {
            if ($(this).is(":checked")) {
                $("#declaration3_error").text("");
            }
        });

        $("#declarationCheckbox1").on("change", function () {
            if ($(this).is(":checked")) {
                $("#declaration4_error").text("");
            }
        });



        // let bankValidity = $("input[name='bank_validity']").val().trim();
        if (!checkBankValidity(bankValidity)) {
            $(".nav-item").each(function () {
                if ($(this).text().trim() === "Staff & Bank Details") {
                    $(this).addClass("tab-error-bg");
                    $(this).trigger("click");
                }
            });

            $("#bank_validity_error").text(
                "Minimum 1 year is required for Bank Solvency Validity Period.",
            );

            const bankValidityInput = $("input[name='bank_validity']")[0];
            if (bankValidityInput) {
                bankValidityInput.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                });
                bankValidityInput.focus();
            }

            return false;
        }



        // --------------------equipment data--------------------
        let equipmentValid = true;

        $(".equipment-row").each(function () {
            let row = $(this);

            let serial = row.find('input[name="serial_no[]"]');
            let model = row.find('input[name="model[]"]');
            let date = row.find('input[name="date_of_test[]"]');

            let testFile = row.find('input[name^="instrument_test_report"]')[0];
            let purchaseFile = row.find(
                'input[name^="instrument_purchase_report"]',
            )[0];

            let presentTest = row.find(".present-test-file").length;
            let presentPurchase = row.find(".present-purchase-file").length;

            // SERIAL
            if (serial.val().trim() === "") {
                row.find(".serial_error").text("Serial Number required");
                equipmentValid = false;
            } else {
                row.find(".serial_error").text("");
            }


            // DATE
            if (date.val().trim() === "") {
                row.find(".date_error").text("Date of Test required");
                equipmentValid = false;
            } else {
                row.find(".date_error").text("");
            }

            // Check if link exists below input
            // TEST
            let testCell = row.find('input[name^="instrument_test_report"]').closest("td");
            let hasTestFile = testFile.files.length > 0 || testCell.find(".uploaded-file, .file-link").length > 0;

            // PURCHASE
            let purchaseCell = row.find('input[name^="instrument_purchase_report"]').closest("td");
            let hasPurchaseFile = purchaseFile.files.length > 0 || purchaseCell.find(".uploaded-file, .file-link").length > 0;

            if (!hasTestFile) {
                row.find(".instrument_test_report_error").text("Upload Test Report");
                equipmentValid = false;
            } else {
                row.find(".instrument_test_report_error").text("");
            }

            if (!hasPurchaseFile) {
                row.find(".instrument_purchase_report_error").text("Upload Purchase Report");
                equipmentValid = false;
            } else {
                row.find(".instrument_purchase_report_error").text("");
            }


        });

        // -----------------remove errors----------------
        $(document).on("keyup change", 'input[name="serial_no[]"]', function () {
            if ($(this).val().trim() !== "") {
                $(this).closest("tr").find(".serial_error").text("");
            }
        });

        $(document).on("change", 'input[name="date_of_test[]"]', function () {
            if ($(this).val().trim() !== "") {
                $(this).closest("tr").find(".date_error").text("");
            }
        });

        $(document).on(
            "change",
            'input[name^="instrument_test_report"]',
            function () {
                if (this.files.length > 0) {
                    $(this)
                        .closest("tr")
                        .find(".instrument_test_report_error")
                        .text("");
                }
            },
        );

        $(document).on(
            "change",
            'input[name^="instrument_purchase_report"]',
            function () {
                if (this.files.length > 0) {
                    $(this)
                        .closest("tr")
                        .find(".instrument_purchase_report_error")
                        .text("");
                }
            },
        );

        if (!equipmentValid) {
            Swal.fire({
                icon: "warning",
                width: 450,
                title: "Incomplete Equipment Details",
                text: "All equipment fields and documents are mandatory.",
                confirmButtonText: "OK",
            });

            // Auto open Equipment tab
            $(".nav-item").each(function () {
                if ($(this).text().trim() === "Equipment / Instruments List") {
                    $(this).addClass("tab-error-bg");
                    $(this).trigger("click");
                }
            });

            return false;
        }

    // -------------------------------------

    if (isValid) {
        
        checkvalidityalterdatesformA(formData);
        
    }
    
});

$(document).on("click", "#save_qc_alter", function (e) {

    e.preventDefault();

    let $button = $(this);
    let $section = $("#staffqc_datasection");

    let applicationId = $("#qc_application_name").val();

    let form_name = $("#form_name").val();
    let license_name = $("#license_name").val();
    let appl_type = $("#appl_type").val();

    // QC details
    let ccNo = $section.find("input[name='staff_cc_no[]']").val();

    let firstIssue = $section
        .find("input[name='staff_cc_first_issue[]']").val();

    let validityFrom = $section
        .find("input[name='staff_cc_validity_from[]']").val();

    let validityTo = $section
        .find("input[name='staff_cc_validity_to[]']").val();

    let certificateHolderName = $.trim(
        $("#certificate-holder-name").text()
    );

    // Uploaded documents
    let appDocLink = $section
        .find(".app-doc-file")
        .siblings(".file-link")
        .find("a")
        .prop("href") || "";

    let consDocLink = $section
        .find(".cons-doc-file")
        .siblings(".file-link")
        .find("a")
        .prop("href") || "";

    // Validation
    if (!certificateHolderName) {
        Swal.fire("Validation Error", "Certificate holder name is required.", "warning");
        return;
    }

    if (!ccNo) {
        Swal.fire("Validation Error", "CC Number is required.", "warning");
        return;
    }

    if (!firstIssue || !validityFrom || !validityTo) {
        Swal.fire("Validation Error", "Please check all CC dates.", "warning");
        return;
    }

    if (!appDocLink || !consDocLink) {
        Swal.fire("Document Required", "Please upload both Appointment and Consent documents.", "warning");
        return;
    }

    // Prevent duplicate clicks
    $button.prop("disabled", true);

    $.ajax({

        url: BASE_URL + "/save-qc-staff_alter",
        type: "POST",

        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),

            application_id: applicationId,
            staff_name: certificateHolderName,
            staff_cc_no: ccNo,

            first_issue: firstIssue,
            validity_from: validityFrom,
            validity_to: validityTo,

            app_doc: appDocLink,
            cons_doc: consDocLink,

            form_name: form_name,
            license_name: license_name,
            appl_type: appl_type
        },

        success: function (response) {

            if (!response.status) {
                Swal.fire("Error", response.message || "Unable to save QC staff.", "error");
                return;
            }

            // Add the saved record to the table
            addQCStaffRecord(
                "QC",
                response.staff_name,
                response.staff_cc_no,
                response.first_issue,
                response.validity_from,
                response.validity_to,
                15000,
                appl_type,
                response.app_doc,
                "Appointment Letter",
                response.cons_doc,
                "Consent Letter",
                false,
                response.row_index,
                response.staff_id
            );

            // Update serial numbers
            updateQCStaffSerialNumbers();

            // Update total fee
            updateQCStaffTotalFee();

            // Close the entry form
            $section.slideUp(300);

            // Reset entry form
            resetQCStaffForm();

            Swal.fire({
                title: "Success!",
                text: response.message,
                icon: "success",
                confirmButtonText: "OK"
            });

        },

        error: function (xhr) {

            console.error(xhr.responseText);

            let message = "Unable to save QC staff.";

            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }

            Swal.fire("Error", message, "error");

        },

        complete: function () {

            $button.prop("disabled", false);

        }

    });

});


  function addQCStaffRecord(
    category,
    certificateHolderName,
    ccNo,
    firstIssue,
    validityFrom,
    validityTo,
    fee,
    appl_type,
    appDocLink = "",
    appDocName = "",
    consDocLink = "",
    consDocName = "",
    isExisting = false,
    staffRowIndex = null,
    savedStaffId = ""
) {

    // ========================================================
    // 1. GET ROW INDEX
    // ========================================================

    if (
        staffRowIndex === null ||
        staffRowIndex === undefined ||
        staffRowIndex === ""
    ) {
        staffRowIndex = getNextQCStaffIndex();
    }

    let appDocIndex = staffRowIndex;
    let consDocIndex = staffRowIndex;


    // ========================================================
    // 2. ESCAPE HTML VALUES
    // ========================================================

    function escapeHtml(value) {
        return $("<div>")
            .text(value == null ? "" : String(value))
            .html();
    }

    let safeCategory = escapeHtml(category);
    let safeName = escapeHtml(certificateHolderName);
    let safeCCNo = escapeHtml(ccNo);

    let safeFirstIssue = escapeHtml(formatDateDisplay(firstIssue));
    let safeValidityFrom = escapeHtml(formatDateDisplay(validityFrom));
    let safeValidityTo = escapeHtml(formatDateDisplay(validityTo));

    let safeAppDocLink = escapeHtml(appDocLink);
    let safeConsDocLink = escapeHtml(consDocLink);

    let safeAppDocName = escapeHtml(
        appDocName || "Appointment Letter"
    );

    let safeConsDocName = escapeHtml(
        consDocName || "Consent Letter"
    );


    // ========================================================
    // 3. CREATE TABLE ROW
    // ========================================================

    let row = `
        <tr class="staffqc-record"
            data-id="${escapeHtml(savedStaffId)}"
            data-row-index="${escapeHtml(staffRowIndex)}"
            data-existing="${isExisting ? "1" : "0"}">

            <!-- S.NO -->
            <td class="sno"></td>

            <!-- STAFF DETAILS -->
            <td>

                <input type="hidden"
                    name="staffqc_id[]"
                    value="${escapeHtml(savedStaffId)}">

                <input type="hidden"
                    name="row_index[]"
                    value="${escapeHtml(staffRowIndex)}">

                <input type="hidden"
                    name="staffqc_category[]"
                    value="${safeCategory}">

                <input type="hidden"
                    name="staff_cc_no[]"
                    value="${safeCCNo}">

                <input type="hidden"
                    name="first_issue[]"
                    value="${safeFirstIssue}">

                <input type="hidden"
                    name="validity_from[]"
                    value="${safeValidityFrom}">

                <input type="hidden"
                    name="validity_to[]"
                    value="${safeValidityTo}">

                <input type="hidden"
                    name="app_doc[]"
                    value="${escapeHtml(appDocIndex)}">

                <input type="hidden"
                    name="cons_doc[]"
                    value="${escapeHtml(consDocIndex)}">

                <input type="hidden"
                    name="app_doc_path[]"
                    value="${safeAppDocLink}">

                <input type="hidden"
                    name="cons_doc_path[]"
                    value="${safeConsDocLink}">

                ${safeName}

            </td>

            <!-- CC NUMBER -->
            <td>
                ${safeCCNo}
            </td>

            <!-- FIRST ISSUE -->
            <td>
                <input type="text"
                    class="form-control"
                    name="staff_cc_first_issue[]"
                    value="${safeFirstIssue}"
                    readonly>
            </td>

            <!-- VALIDITY FROM -->
            <td>
                <input type="text"
                    class="form-control"
                    name="staff_cc_validity_from[]"
                    value="${safeValidityFrom}"
                    readonly>
            </td>

            <!-- VALIDITY TO -->
            <td>
                <input type="text"
                    class="form-control"
                    name="staff_cc_validity_to[]"
                    value="${safeValidityTo}"
                    readonly>
            </td>

            <!-- DOCUMENT LINKS -->
            <td>
                <div class="file-link">

                    ${
                        appDocLink
                            ? `
                                <a href="${safeAppDocLink}"
                                    target="_blank"
                                    class="app-doc-link">

                                    <i class="fa fa-file-pdf-o"
                                        style="color:red;"></i>

                                    ${safeAppDocName}

                                </a>
                            `
                            : ""
                    }

                    ${
                        consDocLink
                            ? `
                                <br>

                                <a href="${safeConsDocLink}"
                                    target="_blank"
                                    class="cons-doc-link">

                                    <i class="fa fa-file-pdf-o"
                                        style="color:red;"></i>

                                    ${safeConsDocName}

                                </a>
                            `
                            : ""
                    }

                </div>
            </td>

            <!-- REMOVE BUTTON -->
            <td>

                <button type="button"
                    class="btn btn-danger remove-qc-staff">

                    🗑 Remove

                </button>

                <input type="hidden"
                    name="qc_removed[]"
                    value="0">

            </td>

        </tr>
    `;


    // ========================================================
    // 4. CHECK WHETHER STAFF RECORD ALREADY EXISTS
    // ========================================================

    let $existingRow = $();

    if (savedStaffId) {

        $existingRow = $("#staffqc-records tr.staffqc-record")
            .filter(function () {

                let currentId = $(this)
                    .find("input[name='staffqc_id[]']")
                    .val();

                return String(currentId) === String(savedStaffId);

            });

    }


    // ========================================================
    // 5. REPLACE EXISTING ROW OR APPEND NEW ROW
    // ========================================================

    if ($existingRow.length > 0) {

        $existingRow.replaceWith(row);

    } else {

        $("#staffqc-records").append(row);

    }


    // ========================================================
    // 6. UPDATE SERIAL NUMBERS
    // ========================================================

    updateQCStaffSerialNumbers();


    // ========================================================
    // 7. UPDATE TOTAL QC / QSC FEE
    // ========================================================

    updateQCStaffTotalFee();

}

function formatDateDisplay(value) {

    if (!value) return "";

    let parts = String(value).split(/[-/]/);

    if (parts.length !== 3) return value;

    // YYYY-MM-DD
    if (parts[0].length === 4) {
        return `${parts[2].padStart(2, "0")}/${parts[1].padStart(2, "0")}/${parts[0]}`;
    }

    // DD-MM-YYYY or DD/MM/YYYY
    return `${parts[0].padStart(2, "0")}/${parts[1].padStart(2, "0")}/${parts[2]}`;
}    

// $(document).on("click", "#cancel_qc", function () {
    //     $("#staffqc_datasection").stop(true, true).slideUp();
    // });

// ============================================================
// GET NEXT QC / QSC DOCUMENT AND ROW INDEX
// ============================================================

function getNextQCStaffIndex() {

    let maxIndex = 0;

    $("#staffqc-records tr.staffqc-record").each(function () {

        let index = parseInt($(this).attr("data-row-index")) || 0;

        if (index > maxIndex) {
            maxIndex = index;
        }

    });

    return maxIndex + 1;
}


function updateQCStaffSerialNumbers() {

    $("#staffqc-records tr.staffqc-record").each(function (index) {

        $(this).find("td.sno").text(index + 1);

    });

}





    function checkvaliditydatesformA(formData, actionType = "") {
        let firstCertNo = $("input[name='cc_number[]']").eq(0).val()?.trim();
        let firstCertValidity = $("input[name='cc_validity[]']")
            .eq(0)
            .val()
            ?.trim();
        let bankValidity = $("input[name='bank_validity']").val()?.trim();
        let appl_type = $("input[name='appl_type']").val()?.trim();
        let form_name = $("input[name='form_name']").val()?.trim();

        // If missing data → skip DB check
        if (!firstCertNo || !firstCertValidity || !bankValidity) {
            formData.append("check_value", "NO");
            showDeclarationPopupformA(formData);
            return;
        }

        $.ajax({
            url: BASE_URL + "/check_ealicence_validity",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            data: {
                firstCertNo: firstCertNo,
                qc_validity_date: firstCertValidity,
                bank_validity: bankValidity,
                appl_type: appl_type,
                form_name: form_name,
            },
            success: function (res) {
                // ⚠️ INVALID FROM DB
                if (res.status === "INVALID") {
                    let msgHtml = `
                     <hr>
                    <div style="text-align:left;font-size:15px;">
                        <p>QC Certificate No:<b> ${firstCertNo} </b> Validity:<b> ${formatDDMMYYYY(firstCertValidity)} </b></p>

                        <p>Bank Solvency Validity:<b> ${formatDDMMYYYY(bankValidity)} </b></p>
                        <hr>
                        <h6 style="font-weight:bold;line-height:30px;text-align:center;color:red;">
                            ${res.message} (${formatDDMMYYYY(res.licence_validitydate)})<br>
                           Hence, Licence will be issued up to the expiry date (${formatDDMMYYYY(res.renewal_period)}) <br>
                            Confirm to proceed?
                        </h6>
                    </div>
                `;

                    Swal.fire({
                        title: "Important Notice",
                        width: 750,
                        html: msgHtml,
                        showCancelButton: true,
                        confirmButtonText: "OK",
                        cancelButtonText: "Cancel",
                        confirmButtonColor: "#0d6efd", // Bootstrap primary blue
                        cancelButtonColor: "red",
                        allowOutsideClick: false,
                    }).then((result) => {
                        if (result.isConfirmed) {
                            formData.append("check_value", "YES");
                            showDeclarationPopupformA(formData);
                        } else {
                            formData.append("check_value", "NO");
                            actionType = "draft";
                            submitFormAFinal(formData, actionType);
                        }
                    });
                } else {
                    // ✅ VALID
                    formData.append("check_value", "NO");
                    showDeclarationPopupformA(formData);
                }
            },
            error: function () {
                Swal.fire("Error", "Validity check failed", "error");
            },
        });
    }

    function storeValidityCheck(checkValue) {
        $.ajax({
            url: "/storevaliditycheck_cl",
            type: "POST",
            data: {
                check_value: checkValue,
                application_id: $("input[name='application_id']").val(),
                form_name: "FORM_A",
                license_name: "CL",
                _token: $('meta[name="csrf-token"]').attr("content"),
            },
        });
    }

    function formatDDMMYYYY(dateStr) {
        if (!dateStr) return "";
        const d = new Date(dateStr);
        const day = String(d.getDate()).padStart(2, "0");
        const month = String(d.getMonth() + 1).padStart(2, "0");
        const year = d.getFullYear();
        return `${day}-${month}-${year}`;
    }


    // ============================================================
    // CALCULATE TOTAL QC/QSC FEE
    // ============================================================
    function updateQCStaffTotalFee() {

        let totalFee = 0;

        $("#staffqc-records tr.staffqc-record").each(function () {

            let $row = $(this);

            // Existing/draft row
            let isExisting = $row.attr("data-existing") === "1";

            // Existing alteration records are FREE
            if (isExisting) {
                return;
            }

            let category = $row
                .find('input[name="staffqc_category[]"]')
                .val();

            category = $.trim(category).toUpperCase();

            if (category === "QC") {
                totalFee += 15000;
            }
            else if (category === "QSC") {
                totalFee += 25000;
            }
        });


        // Remove previous fee row
        $("#staffqc-records tr.staffqc-total-fee").remove();


        // No NEW staff = no fee
        if (totalFee <= 0) {
            return;
        }


        let feeRow = `
        <tr class="staffqc-total-fee">

            <td colspan="7" class="fw-bold text-end">
                Fees
            </td>

            <td>
                <span class="btn btn-primary">
                    ₹ ${totalFee.toLocaleString("en-IN")}
                </span>
            </td>

        </tr>
    `;

        $("#staffqc-records").append(feeRow);
    }

    // ============================================================
    // REMOVE QC / QSC STAFF
    // ============================================================

   $(document).on("click", ".remove-qc-staff_alter", function (e) {

    e.preventDefault();

    let $row = $(this).closest("tr.staffqc-record");

    let staffId = $row.find("input[name='staffqc_id[]']").val()
        || $row.attr("data-id");

    let ccNo = $row.find("input[name='staff_cc_no[]']").val();

    let applicationId = $("#qc_application_name").val()
        || $("input[name='record_id']").val();


    Swal.fire({

        title: "Are you sure?",
        text: "You want to remove this QC staff record?",
        icon: "warning",

         width: 450,

        showCancelButton: true,

        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",

        confirmButtonText: "Yes, remove it!",
        cancelButtonText: "Cancel",

        reverseButtons: true

    }).then((result) => {

        if (!result.isConfirmed) {
            return;
        }


        // ====================================================
        // NEW UNSAVED ROW
        // ====================================================

        if (!staffId) {

            $row.remove();

            updateQCStaffSerialNumbers();
            updateQCStaffTotalFee();

            Swal.fire({
                title: "Removed!",
                text: "QC staff record removed successfully.",
                icon: "success",
                timer: 1500,
                showConfirmButton: false
            });

            return;
        }


        // ====================================================
        // EXISTING SAVED ROW - UPDATE FLAG TO 0
        // ====================================================

        $.ajax({

            url: BASE_URL + "/remove-qc-staff",

            type: "POST",

            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                staff_id: staffId,
                application_id: applicationId,
                cc_no: ccNo
            },

            beforeSend: function () {

                $row.find(".remove-qc-staff")
                    .prop("disabled", true)
                    .text("Removing...");

            },

            success: function (response) {

                if (response.status) {

                    $row.remove();

                    updateQCStaffSerialNumbers();
                    updateQCStaffTotalFee();

                    Swal.fire({
                        title: "Removed!",
                        text: response.message || "QC staff removed successfully.",
                         width: 450,
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });

                } else {

                    Swal.fire({
                        title: "Unable to remove!",
                        text: response.message || "QC staff could not be removed.",
                        icon: "error",
                         width: 450,
                    });

                    $row.find(".remove-qc-staff")
                        .prop("disabled", false)
                        .text("🗑 Remove");

                }

            },

            error: function (xhr) {

                console.error(xhr.responseText);

                Swal.fire({
                    title: "Error!",
                    text: "Unable to remove QC staff. Please try again.",
                    icon: "error"
                });

                $row.find(".remove-qc-staff")
                    .prop("disabled", false)
                    .text("🗑 Remove");

            }

        });

    });

});

    // ============================================================
    // PAGE LOAD
    // ============================================================

    $(document).ready(function () {

        // Calculate fee for existing draft QC/QSC records
        updateQCStaffTotalFee();

    });


    // ============================================================
    // RESET QC/QSC FORM
    // ============================================================

    function resetQCStaffForm() {

        let $section =
            $("#staffqc_datasection");


        // Clear text fields
        $section
            .find("input[type='text']")
            .val("");


        // Clear select
        $section
            .find("select")
            .val("");


        // Clear document indexes
        $section
            .find(".app_doc")
            .val("");


        $section
            .find(".cons_doc")
            .val("");


        // Clear errors
        $section
            .find(".text-danger")
            .text("");


        // Clear file inputs
        $section
            .find('input[type="file"]')
            .val("");

    }

function validatealterStaffCertificate(
    staffCategory,
    certificateNo,
    firstIssue,
    validityFrom,
    validityTo,
     license_number
) {
    return $.ajax({
        url: BASE_URL + "/check-cc-certificate",

        type: "POST",

        dataType: "json",

        data: {
            _token: $('meta[name="csrf-token"]').attr("content"),

            staffcategory: staffCategory,

            certificate_no: certificateNo,

            dateof_issue: firstIssue,

            valid_from: validityFrom,

            valid_to: validityTo,
            
            license_number: license_number
        },
    });
}

function checkvalidityalterdatesformA(formData, actionType = "") {
    let firstCertNo = $("input[name='cc_number[]']").eq(0).val()?.trim();
    let firstCertValidity = $("input[name='cc_validity[]']")
        .eq(0)
        .val()
        ?.trim();
    let bankValidity = $("input[name='bank_validity']").val()?.trim();
    let appl_type = $("input[name='appl_type']").val()?.trim();
    let form_name = $("input[name='form_name']").val()?.trim();

    // If missing data → skip DB check
    if (!firstCertNo || !firstCertValidity || !bankValidity) {
        formData.append("check_value", "NO");
        submitFormAFinalalter(formData);
        return;
    }

    $.ajax({
        url: BASE_URL + "/check_ealicence_validity",
        type: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: {
            firstCertNo: firstCertNo,
            qc_validity_date: firstCertValidity,
            bank_validity: bankValidity,
            appl_type: appl_type,
            form_name: form_name,
        },
        success: function (res) {
            // ⚠️ INVALID FROM DB
            if (res.status === "INVALID") {
                let msgHtml = `
                     <hr>
                    <div style="text-align:left;font-size:15px;">
                        <p>QC Certificate No:<b> ${firstCertNo} </b> Validity:<b> ${formatDDMMYYYY(firstCertValidity)} </b></p>

                        <p>Bank Solvency Validity:<b> ${formatDDMMYYYY(bankValidity)} </b></p>
                        <hr>
                        <h6 style="font-weight:bold;line-height:30px;text-align:center;color:red;">
                            ${res.message} (${formatDDMMYYYY(res.licence_validitydate)})<br>
                           Hence, Licence will be issued up to the expiry date (${formatDDMMYYYY(res.renewal_period)}) <br>
                            Confirm to proceed?
                        </h6>
                    </div>
                `;

                Swal.fire({
                    title: "Important Notice",
                    width: 750,
                    html: msgHtml,
                    showCancelButton: true,
                    confirmButtonText: "OK",
                    cancelButtonText: "Cancel",
                    confirmButtonColor: "#0d6efd", // Bootstrap primary blue
                    cancelButtonColor: "red",
                    allowOutsideClick: false,
                }).then((result) => {
                    if (result.isConfirmed) {
                        formData.append("check_value", "YES");
                        submitFormAFinalalter(formData);
                    } else {
                        formData.append("check_value", "NO");
                        actionType = "draft";
                        submitFormAFinalalter(formData, actionType);
                    }
                });
            } else {
                // ✅ VALID
                formData.append("check_value", "NO");
                submitFormAFinalalter(formData);
            }
        },
        error: function () {
            Swal.fire("Error", "Validity check failed", "error");
        },
    });
}

function storealterValidityCheck(checkValue) {
    $.ajax({
        url: "/storealterValidityCheck_cl",
        type: "POST",
        data: {
            check_value: checkValue,
            application_id: $("input[name='application_id']").val(),
            form_name: "FORM_A",
            license_name: "CL",
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
    });
}

function formatDDMMYYYY(dateStr) {
    if (!dateStr) return "";
    const d = new Date(dateStr);
    const day = String(d.getDate()).padStart(2, "0");
    const month = String(d.getMonth() + 1).padStart(2, "0");
    const year = d.getFullYear();
    return `${day}-${month}-${year}`;
}

function showDeclarationalterPopupformA(formData) {
    // alert(formData.check_value);
    let formName = formData.get("form_name");
    let appl_type = formData.get("appl_type");

    let issued_licence = $("#license_number").val();
    if (!issued_licence || issued_licence.trim() === "") {
        issued_licence = "0";
    }

    $.ajax({
        url: BASE_URL + "/get-form-instructions_alter",
        method: "GET",
        data: {
            form_name: formName,
            appl_type: appl_type,
            issued_licence: issued_licence,
        },
        success: function (response) {
            if (!response || !response.fees_details) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Fees details not found!",
                });
                return;
            }

            // Store fees values in formData
            formData.set("total_fees", response.fees_details.total_fees);
            formData.set("basic_fees", response.fees_details.basic_fees);
            formData.set("lateFees", response.fees_details.lateFees);
            formData.set("late_months", response.fees_details.late_months);
            formData.set("dbNow", response.fees_details.dbNow);
            formData.set("licenseName", response.licenseName);
            formData.set("qcfee", response.fees_details.qcfee);

            // Get fee details
            let fees_start_date = response.fees_start_date;
            let basic_fees = response.fees_details.basic_fees;

            let dbNow = response.fees_details.dbNow;

            // alert(basic_fees);
            let certificate_name = response.licenseName;

            if (fees_start_date) {
                let parts = fees_start_date.split("-"); // ["2025","01","27"]
                fees_start_date = `${parts[2]}-${parts[1]}-${parts[0]}`; // DD-MM-YYYY
            }

            // Convert Delta to HTML
            let form_instruct = response.instructions;

            let html = "";
            try {
                const delta = JSON.parse(form_instruct);
                const converter = new QuillDeltaToHtmlConverter(delta.ops, {
                    multiLineParagraph: true,
                    listItemTag: "li",
                    paragraphTag: "p",
                });
                html = converter.convert();
            } catch (e) {
                console.error("Delta parse failed. Showing raw text.");
                html = `<p>${form_instruct}</p>`;
            }

            // Insert instructions only (not replacing values above)

            submitFormAFinalalter(formData, "submit");
        },

        error: function () {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Unable to load instructions. Please try again.",
            });
        },
    });
}

function submitFormAFinalalter(formData, actionType) {
    if (!formData.has("check_value")) {
        formData.append("check_value", "NO");
    }

    formData.append("form_action", actionType);

    // formData.append("form_action", actionType);

    let applType = $("#appl_type").val()?.trim();
    let postUrl = BASE_URL + "/forma/storeAlter";
    // : BASE_URL + "/forma/store";
    //  let amount = formData.get("fees") || 0;
    let qcfee = parseFloat(formData.get("qcfee")) || 0;
    let totalfee = formData.get("total_fees") || 0;

    let amount = totalfee + qcfee;

    // alert(amount);

    $.ajax({
        url: postUrl,
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: function () {
            $(".save-draft, .submit-payment").prop("disabled", true);
        },
        success: function (response) {
            const loginId = response.login_id;
            const transactionId = response.transaction_id || "TXN123456";

            const application_id = response.login_id;

            const transactionDate = new Date().toLocaleDateString("en-GB");
            const applicantName = $("#applicant_name").val() || "Applicant";

            const licence_name = $("#license_name").val();

            // alert(licence_name);
            // const qcfees = parseFloat(response.qcfees) || 0;

            let form_type = $("#appl_type").val();

            form_type = "Alteration Application";

            let basic_fees = formData.get("basic_fees") || 0;

            let lateFees = formData.get("lateFees") || 0;
            let lateMonths = formData.get("late_months") || 0;

            const dbNow = response.dbNow;

            // alert($dbNow);
            const qcfees = parseFloat(response.qcfees) || 0;

            // alert(qcfees);
            const totalfee = parseFloat(formData.get("total_fees")) || 0;

            const amount = totalfee + qcfees;

            let licenseName = formData.get("licenseName");

            const formName = $("#form_name").val();
            // alert(licenseName);
            // alert(lateMonths);
            // exit;
            let lateFeeRow = "";
            if (lateFees > 0) {
                lateFeeRow = `
                 <tr>
                    <th style="text-align: left; padding: 6px 10px; color: #555;">Late Fees (${lateMonths} Months)</th>
                    <td style="text-align: right; padding: 6px 10px; font-weight: bold; color: #0d6efd;">Rs. ${lateFees} </td>
                </tr>
                            `;
            }

            if (actionType === "draft") {
                Swal.fire({
                    width: 450,
                    title: "Draft Saved!",
                    html: `Your Application ID is <strong>${loginId}</strong>`,
                    icon: "success",
                }).then(() => {
                    window.location.href = BASE_URL + "/dashboard";
                });
            } else {
                if (parseFloat(qcfees) === 0) {
                    // QC fee is 0 → directly show payment success
                    showPaymentSuccessPopupformAalter(
                        application_id,
                        loginId,
                        transactionId,
                        transactionDate,
                        licence_name,
                        form_type,
                        basic_fees,
                        lateFees,
                        lateFeeRow,
                        applicantName,
                        amount,
                        dbNow,
                        licenseName,
                        formName,
                    );
                } else {
                    // QC fee exists → initiate payment
                    showPaymentInitiationPopupformAalter(
                        application_id,
                        loginId,
                        transactionId,
                        transactionDate,
                        licence_name,
                        form_type,
                        basic_fees,
                        lateFees,
                        lateFeeRow,
                        applicantName,
                        amount,
                        dbNow,
                        qcfees,
                        licenseName,
                        formName,
                    );
                }
            }

            $(".save-draft, .submit-payment").prop("disabled", false);
        },

        error: function (xhr) {
            $(".save-draft, .submit-payment").prop("disabled", false);
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                $.each(errors, function (field, message) {
                    $(`#${field}_error`).text(message);
                });
            } else {
                Swal.fire(
                    "Error!",
                    "Fields 1 and 2 are Missing Fill it Properly.",
                    "error",
                );
            }
        },
    });
}

function showPaymentInitiationPopupformAalter(
    application_id,
    loginId,
    transactionId,
    transactionDate,
    licence_name,
    form_type,
    basic_fees,
    lateFees,
    lateFeeRow,
    applicantName,
    amount,
    dbNow,
    qcfees,
    licenseName,
    formName,
) {
    // alert(qcfees);
    // alert(dbNow);
    Swal.fire({
        title: "<span style='color:#0d6efd;'>₹ Payment Details</span>",
        html: `
                                <div class="text-start" style="font-size: 14px; padding: 10px 0;">

 <table style="width: 100%; font-size: 14px; border-collapse: collapse;">
                                        <tbody>
                                            <tr>
                                                <th style="text-align: left; padding: 6px 10px; width: 50%; color: #555;">Application ID</th>
                                                <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${application_id}</td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; padding: 6px 10px; color: #555;">Applicant Name <br> [Contractor's License]</th>
                                                <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${applicantName}</td>
                                            </tr>

                                             <tr>
                                                <th style="text-align: left; padding: 6px 10px; color: #555;">Type of Application </th>
                                                <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${licence_name}</td>
                                            </tr>
                                             <tr>
                                                <th style="text-align: left; padding: 6px 10px; color: #555;">Type of Form </th>
                                                <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${form_type}</td>
                                            </tr>
                                            <tr>
                                                <th style="text-align: left; padding: 6px 10px; color: #555;">Date</th>
                                                <td style="text-align: right; padding: 6px 10px; font-weight: 500;">${dbNow}</td>
                                            </tr>

                                             <tr>
                                            <th style="text-align: left; padding: 10px; color: #333;">QC Staff Fee</th>
                                                <td style="text-align: right; padding: 10px; font-weight: bold; color: #0d6efd;">Rs. ${qcfees} </td>
                                            </tr>
                                            <tr>
                                            <th style="text-align: left; padding: 10px; color: #333;">Application Fees</th>
                                                <td style="text-align: right; padding: 10px; font-weight: bold; color: #0d6efd;">Rs. ${basic_fees} </td>
                                            </tr>
                                                ${lateFeeRow}
                                             <tr>
                                                 <th style="text-align: left; padding: 6px 10px; color: #555;">Total</th>
                                                    <td style="text-align: right; padding: 10px; font-weight: bold; color: #0d6efd;">Rs. ${amount}</td>
                                            </tr>






                                        </tbody>
                                    </table>




                                </div>
                            `,
        footer: `
                <div class="text-start" style="font-size: 13px;">

                    <strong>Note:</strong>
                    <span ">The total amount is exclusive of payment gateway service charges.</span>
                </div>
            `,
        // icon: "info",
        // // iconHtml: '<i class="swal2-icon" style="font-size: 1 em">ℹ️</i>',
            width: "650px",
        showCancelButton: true,
        confirmButtonText: '<span class="btn btn-primary px-4">Pay Now</span>',
        cancelButtonText: '<span class="btn btn-danger px-4">Cancel</span>',
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
        buttonsStyling: false,
    }).then((result) => {
        if (result.isConfirmed) {
            // Simulate payment success
            setTimeout(() => {
                const apiUrl = BASE_URL.replace(/\/$/, "");
                $.post(
                    apiUrl + "/update-payment-status",
                    {
                        application_id: application_id,
                        payment_status: "paid",
                        _token: $('meta[name="csrf-token"]').attr("content"),
                    },
                    function () {
                        showPaymentSuccessPopupformAalter(
                            application_id,
                            loginId,
                            transactionId,
                            transactionDate,
                            licence_name,
                            form_type,
                            basic_fees,
                            lateFees,
                            lateFeeRow,
                            applicantName,
                            amount,
                            dbNow,
                            licenseName,
                            formName,
                        );
                    },
                );
            }, 1000);
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            $.ajax({
                url: BASE_URL + "/update-payment-status",
                method: "POST",
                data: {
                    application_id: application_id,
                    payment_status: "draft",
                    _token: $('meta[name="csrf-token"]').attr("content"),
                },
                success: function () {
                    let timerInterval;
                    Swal.fire({
                        width: 450,
                        title: "<span style='color:red;'>Payment Failed</span>",
                        html: `
                    <p style="font-size:20px;margin-top:5px;color:#333;">
                        Application is <strong>saved as draft</strong>. <br><br>
                        Redirecting to dashboard in <b id="countdown">5</b> seconds...
                    </p>
                `,
                        icon: "error",
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        timer: 5000,
                        didOpen: () => {
                            const countdownEl =
                                document.getElementById("countdown");
                            let count = 5;
                            timerInterval = setInterval(() => {
                                count--;
                                countdownEl.textContent = count;
                            }, 1000);
                        },
                        willClose: () => {
                            clearInterval(timerInterval);
                            window.location.href = BASE_URL + "/dashboard";
                        },
                    });
                },
                error: function (xhr) {
                    console.error(
                        "Failed to update payment status:",
                        xhr.responseText,
                    );
                },
            });
        }
    });
}

window.paymentAppId = null;
window.paymentFormType = null;
function showPaymentSuccessPopupformAalter(
    application_id,
    loginId,
    transactionId,
    transactionDate,
    licence_name,
    form_type,
    basic_fees,
    lateFees,
    lateFeeRow,
    applicantName,
    amount,
    dbNow,
    licenseName,
    formName,
) {
    //  alert(licenseName);
    $("#ps_applicantName").text(applicantName);
    $("#ps_applicationId").text(loginId);
    $("#ps_licenceName").text(licenseName);
    $("#ps_transactionId").text(transactionId);
    $("#ps_transactionDate").text(transactionDate);
    $("#ps_amount").text(amount);

    // store for receipt & PDF buttons
    window.paymentAppId = loginId;

    // alert(paymentAppId);
    // exit;
    window.paymentFormType = form_type;

    ((window.licenseName = licenseName),
        (window.formName = formName),
        // alert(formName);
        $("#paymentSuccessModalcontractor").modal({
            backdrop: "static",
            keyboard: false,
        }));

    $("#paymentSuccessModalcontractor").modal("show");
}

function paymentreceiptformAalter() {
    // alert('1111');
    if (!window.paymentAppId) {
        alert("Application ID not found!");
        return;
    }
    // alert(paymentAppId);
    window.open(`${BASE_URL}/payment-receipt/${window.paymentAppId}`, "_blank");
}

function downloadPDFformApdf() {
    if (!window.paymentAppId) {
        return alert("Application ID not found!");
    }

    let path = "";

    switch (window.formName) {
        case "A":
            path = "generatea-pdf";
            break;
        case "B":
            path = "generateb-pdf";
            break;
        case "SB":
            path = "generatesb-pdf";
            break;
        case "SA":
            path = "generatesa-pdf";
            break;
        default:
            alert("Invalid form type!");
            return;
    }

    window.open(`${BASE_URL}/${path}/${window.paymentAppId}`, "_blank");
}

$("#closePopup").on("click", function () {
    $("#pdfPopup").fadeOut(function () {
        window.location.href = BASE_URL + "/dashboard";
    });
});
