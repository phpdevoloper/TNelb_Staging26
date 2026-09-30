
$(document).on("submit", "#competency_form_a_alter", async function (e) {

    e.preventDefault();




    console.log($('input[name="staff_name[]"]').length);
    console.log($('input[name="cc_number[]"]').length);
    //     console.log($('input[name="staff_name[]"]').length);
    // console.log($('input[name="cc_number[]"]').length);
    // console.log($('input[name="cc_validity[]"]').length);

    let formData = new FormData(this);
    let submitter = e.originalEvent?.submitter;
    let actionType = "submit";
    if ($(submitter).hasClass("save-draft")) {
        actionType = "draft";
    }
    // alert('111');
    // exit;
    $(".error").text("");
    let isValid = true;

    let isValiddraft = true;

    let applicantName = $("#applicant_name").val().trim();
    let businessAddress = $("textarea[name='business_address']").val().trim();

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

    // ✅ Collect all rows data first
    $("#proprietor-section tbody tr").each(function () {
        let $tr = $(this);
        let $tds = $tr.find("td");

        let id = $tr.data("id") || null;
        // alert(id);
        // exit;
        let qualification_text = $tds.eq(4).data("qual_text") || "";

        // alert($tds.eq(2).data("data-age"));
        // alert(qualification_text);
        //                 exit;
        proprietor.push({
            id: id,
            proprietor_name: $tds.eq(0).text().trim(),
            fathers_name: $tds.eq(1).text().trim(),
            // age: $tds.eq(2).text().trim(),
            // age: $tds.eq(2).data("age"),
            // dob: $tds.eq(2).data("dob"),

            dob: $tds.eq(2).attr("data-dob"),
            age: $tds.eq(2).attr("data-age"),

            proprietor_address: $tds.eq(3).text().trim(),
            // qualification: $tds.eq(4).text().trim(),

            qualification: $tds.eq(4).attr("data-qualification"),
            qualification_text: $tds.eq(4).attr("data-qual_text"),
            // qualification: $tds.eq(4).attr("data-qualification"),
            // qualification_text: $tds.eq(4).attr("data-qual_text") || "",

            present_business: $tds.eq(5).text().trim(),
            competency: $tds.eq(6).data("competency") || "",
            competency_certno: $tds.eq(6).data("certno") || "",
            ccfirstissue: $tds.eq(6).data("ccfirstissue") || "",
            ccvalidityfrom: $tds.eq(6).data("ccvalidityfrom") || "",
            ccvalidityto: $tds.eq(6).data("ccvalidityto") || "",

            ownership_type:
                $tds.eq(9).find("input[name='ownership_type[]']").val() ||
                $tds.eq(9).data("ownership") ||
                "",
        });
    });

    // ✅ Append partners JSON to FormData
    formData.append("proprietor", JSON.stringify(proprietor));

    // ✅ (Optional) Also append individually indexed data if needed
    proprietor.forEach((p, index) => {
        formData.append(`proprietor_id[${index}]`, p.id ?? "");

        // formData.append(`proprietor_name[${index}]`, p.proprietor_name);
        formData.append(`proprietor_name[${index}]`, p.proprietor_name);
        formData.append(`fathers_name[${index}]`, p.fathers_name);
        formData.append(`ownership_type[${index}]`, p.ownership_type);
        formData.append(`age[${index}]`, p.age);
        formData.append(`dob[${index}]`, p.dob);
        formData.append(`proprietor_address[${index}]`, p.proprietor_address);
        formData.append(`qualification[${index}]`, p.qualification);
        formData.append(`qual_text[${index}]`, p.qualification_text);

        formData.append(`present_business[${index}]`, p.present_business);
        formData.append(`competency[${index}]`, p.competency);
        formData.append(`competency_certno[${index}]`, p.competency_certno);
        formData.append(`ccfirstissue[${index}]`, p.ccfirstissue);
        formData.append(`ccvalidityfrom[${index}]`, p.ccvalidityfrom);
        formData.append(`ccvalidityto[${index}]`, p.ccvalidityto);


        formData.append(`expverify[${index}]`, p.expverify);
        // console.log(p.ccverify, p.expverify);

        // formData.append(`exp_validity[${index}]`, p.exp_validity);
    });
    // ---------------------------------------------------

    let partners = [];

// Collect all partner rows
$("#partner-section table tbody tr").each(function () {

    let $tr = $(this);
    let $tds = $tr.find("td");

    let id = $tr.data("id") || null;

    partners.push({

        id: id,

        proprietor_name:
            $tds.eq(0).text().trim(),

        fathers_name:
            $tds.eq(1).text().trim(),

        dob:
            $tds.eq(2).attr("data-dob") || "",

        age:
            $tds.eq(2).attr("data-age") || "",

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

// Append partners JSON
formData.append(
    "partners",
    JSON.stringify(partners)
);

// Append individually indexed partner data
partners.forEach((p, index) => {

    formData.append(
        `partner_id[${index}]`,
        p.id ?? ""
    );

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

    formData.append(
        `partner_age[${index}]`,
        p.age
    );

    formData.append(
        `partner_dob[${index}]`,
        p.dob
    );

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
            $tds.eq(0).attr("data-name") || "",

        managing_director:
            $tds.eq(0).attr("data-managing_director") || "no",

        fathers_name:
            $tds.eq(1).text().trim(),

        dob:
            $tds.eq(2).attr("data-dob") || "",

        age:
            $tds.eq(2).attr("data-age") || "",

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

});
    // --------------------------------------

    //     let proprietor_name = $("input[name='proprietor_name[]']").val();
    // if (!proprietor_name || proprietor_name.trim() === "") {
    //     $("#proprietor_name_error").text("Proprietor Name is required.");
    //     isValid = false;
    //     // return false;
    // }
    // $("#proprietor_name").on("keyup", function () {
    //     if ($(this).val().trim() !== "") {
    //         $("#proprietor_name_error").text("");
    //     }
    // });

    // $("input[name='proprietor_name[]']").each(function () {
    //     let value = $(this).val().trim();
    //     let errorSpan = $(this).siblings(".proprietor_name_error");

    //     if (value === "") {
    //         errorSpan.text("Proprietor Name is required.");
    //         isValid = false;

    //         if (actionType === "draft") {
    //             // 👇 stop draft immediately on first failure
    //             return false;
    //         }
    //     } else {
    //         errorSpan.text("");
    //     }
    // });

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
    // alert(actionType);
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


    // ----------------------------------------------------------
    // GET ALL QC / QSC STAFF ROWS
    // ----------------------------------------------------------


$("#staffqc-records tr.staffqc-record").each(function (index) {

    let $tr = $(this);

    let record = {

        // --------------------------------------------------
        // Existing database ID
        // --------------------------------------------------

        id: $tr.data("id") || null,


        // --------------------------------------------------
        // Category
        // --------------------------------------------------

        staffqc_category:
            $tr.find(".staffqc_category").val()
            ||
            $tr.find('input[name="staffqc_category[]"]').val()
            ||
            $tr.find('input[name^="staffqc_category["]').val()
            ||
            $tr.find("td:eq(1)").text().trim()
            ||
            "",


        // --------------------------------------------------
        // Certificate Number
        // --------------------------------------------------

        staff_cc_no:
            $tr.find(".staff_cc_no").val()
            ||
            $tr.find(".cc_number").val()
            ||
            $tr.find('input[name="staff_cc_no[]"]').val()
            ||
            $tr.find('input[name^="staff_cc_no["]').val()
            ||
            $tr.find("td:eq(2)").text().trim()
            ||
            "",


        // --------------------------------------------------
        // First Issue
        // --------------------------------------------------

        staff_cc_first_issue:
            $tr.find(".staff_cc_first_issue").val()
            ||
            $tr.find(".cc_firstissue").val()
            ||
            $tr.find('input[name="staff_cc_first_issue[]"]').val()
            ||
            $tr.find('input[name^="staff_cc_first_issue["]').val()
            ||
            "",


        // --------------------------------------------------
        // Validity From
        // --------------------------------------------------

        staff_cc_validity_from:
            $tr.find(".staff_cc_validity_from").val()
            ||
            $tr.find(".cc_validity_from").val()
            ||
            $tr.find('input[name="staff_cc_validity_from[]"]').val()
            ||
            $tr.find('input[name^="staff_cc_validity_from["]').val()
            ||
            "",


        // --------------------------------------------------
        // Validity To
        // --------------------------------------------------

        staff_cc_validity_to:
            $tr.find(".staff_cc_validity_to").val()
            ||
            $tr.find(".cc_validity_to").val()
            ||
            $tr.find('input[name="staff_cc_validity_to[]"]').val()
            ||
            $tr.find('input[name^="staff_cc_validity_to["]').val()
            ||
            "",


        // --------------------------------------------------
        // Appointment Document
        // --------------------------------------------------

        app_doc:
            $tr.find('input[name="app_doc[]"]').val()
            ||
            $tr.find('input[name^="app_doc["]').val()
            ||
            "",


        // --------------------------------------------------
        // Consent Document
        // --------------------------------------------------

        cons_doc:
            $tr.find('input[name="cons_doc[]"]').val()
            ||
            $tr.find('input[name^="cons_doc["]').val()
            ||
            "",


        // --------------------------------------------------
        // IMPORTANT: ROW INDEX
        // --------------------------------------------------

        row_index:
            $tr.attr("data-row-index")
            ||
            $tr.data("row-index")
            ||
            $tr.find('input[name="row_index[]"]').val()
            ||
            ""
    };


    // --------------------------------------------------
    // ADD ONLY QC / QSC
    // --------------------------------------------------

    let category = String(
        record.staffqc_category || ""
    ).trim().toUpperCase();


    if (!["QC", "QSC"].includes(category)) {
        return;
    }


    // --------------------------------------------------
    // ADD RECORD
    // --------------------------------------------------

    qcStaffRecords.push(record);

});


    // ----------------------------------------------------------
    // DEBUG
    // ----------------------------------------------------------

    console.log("QC/QSC row count:",
        $("#staffqc-records tr").length
    );

    console.log("QC/QSC records:",
        qcStaffRecords);


    // ----------------------------------------------------------
    // MINIMUM ONE QC / QSC STAFF
    // ----------------------------------------------------------

    if (qcStaffRecords.length === 0) {

        isValid = false;

        // Add red border
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


    // ----------------------------------------------------------
    // QC/QSC STAFF EXISTS
    // ----------------------------------------------------------

    console.log(
        "Minimum QC/QSC requirement satisfied.",
        qcStaffRecords.length,
        "record(s) found."
    );


    // Remove previous error border
    $("#qc-staff-table").removeClass("qc-table-error");


    // Remove tab error
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

    // ----------------------------------------
    // ADD QC RECORDS TO FORMDATA
    // ----------------------------------------

    formData.set(
        "qc_staff_records",
        JSON.stringify(qcStaffRecords)
    );


    console.log(
        "FINAL QC STAFF:",
        formData.get("qc_staff_records")
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
    // VALIDATE FIRST 2 B STAFF
    // ======================================================
    let staffValidationRequests = [];
    let staffBasicValidationPassed = true;

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

        let category = ($row.find(".staff_category").val() || "").trim();

        // First 2 rows are mandatory B
        if (index < 2) {
            category = "B";
        }

        console.log("====================================");
        console.log("STAFF " + (index + 1));
        console.log("CATEGORY:", category);


        // ==================================================
        // OTHERS
        // No certificate validation required
        // ==================================================

        if (category === "OTHERS") {

            console.log(
                "STAFF " + (index + 1) +
                ": OTHERS - Certificate validation skipped"
            );

            return;
        }


        // ==================================================
        // ONLY B AND C REQUIRE CERTIFICATE DETAILS
        // ==================================================

        if (category !== "B" && category !== "C") {

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

        let ccNumber = ($row.find(".cc_number").val() || "").trim();

        // ==================================================
        // FIRST ISSUE
        // ==================================================

        let firstIssue = ($row.find(".cc_firstissue").val() || "").trim();

        // ==================================================
        // VALIDITY FROM
        // ==================================================

        let validityFrom = ($row.find(".cc_validity_from").val() || "").trim();

        // ==================================================
        // VALIDITY TO
        // ==================================================

        let validityTo = ($row.find(".cc_validity_to").val() || "").trim();


        console.log("Certificate No:", ccNumber);
        console.log("First Issue:", firstIssue);
        console.log("Validity From:", validityFrom);
        console.log("Validity To:", validityTo);


        // ==================================================
        // CERTIFICATE NUMBER REQUIRED
        // ==================================================

        if (ccNumber === "") {

            staffBasicValidationPassed = false;

            $row.find(".cc_number").after(
                '<span class="staff-validation-error error text-danger d-block">' +
                'Certificate Number is required.</span>'
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
                'Certificate First Issue is required.</span>'
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
                'Certificate Validity From is required.</span>'
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
                'Certificate Validity To is required.</span>'
            );

            return;
        }


        // ==================================================
        // SEND B / C CERTIFICATE TO CONTROLLER
        // ==================================================

        let request = validatealterStaffCertificate(
            category,
            ccNumber,
            firstIssue,
            validityFrom,
            validityTo
        )


            .then(function (response) {

                console.log(
                    "STAFF " + (index + 1) +
                    " CERTIFICATE RESPONSE:",
                    response
                );


                // ==================================================
                // CERTIFICATE INVALID
                // ==================================================

                if (response.status !== true) {

                    staffValidationPassed = false;

                    $row.find(".cc_number").after(
                        '<span class="staff-validation-error error text-danger d-block">' +
                        (response.message || "Certificate validation failed.") +
                        '</span>'
                    );

                    return false;
                }


                // ==================================================
                // CERTIFICATE VALID
                // ==================================================

                console.log(
                    "STAFF " + (index + 1) +
                    " certificate verified successfully."
                );

                return true;

            })
            .catch(function (xhr) {

                staffValidationPassed = false;

                console.log(
                    "STAFF " + (index + 1) +
                    " CERTIFICATE ERROR"
                );

                console.log("HTTP STATUS:", xhr.status);
                console.log("RESPONSE:", xhr.responseText);
                console.log("JSON:", xhr.responseJSON);


                $row.find(".cc_number").after(
                    '<span class="staff-validation-error error text-danger d-block">' +
                    'Unable to verify certificate. Please try again.' +
                    '</span>'
                );

                return false;
            });
        console.log(request);

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

            if ($(this).text().trim().includes("Staff & Bank Details")) {

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


let results = await Promise.all(staffValidationRequests);

console.log(
    "ALL STAFF CERTIFICATE RESULTS:",
    results
);


// ======================================================
// CHECK ALL CERTIFICATES
// ======================================================

let allCertificatesValid = results.every(function (result) {
    return result === true;
});


// ======================================================
// CERTIFICATE VALIDATION FAILED
// ======================================================

if (!allCertificatesValid) {

    isValid = false;

    console.log("Certificate validation failed");
    console.log("isValid:", isValid);

    Swal.fire({
        icon: "warning",
        title: "Invalid Staff Certificate",
        text: "Please correct the invalid B/C staff certificate details.",
        confirmButtonText: "OK",
        width: 500
    });

    $(".nav-item").each(function () {

        if ($(this).text().trim().includes("Staff & Bank Details")) {

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

    // if (bankAddress === "") {
    //     $("#bank_address_error").text("Bank name and address is required.");

    //      $(".nav-item").each(function () {
    //         if ($(this).text().trim() === "Staff & Bank Details") {
    //             $(this).addClass("tab-error-bg");
    //             $(this).trigger("click"); // switch to Basic Details tab
    //         }
    //     });

    //     const ownershipNotice = document.querySelector('.text-red');
    //     if (ownershipNotice) {
    //         ownershipNotice.style.color = 'red';
    //         ownershipNotice.style.fontWeight = 'bold';
    //         ownershipNotice.scrollIntoView({ behavior: 'smooth', block: 'center' });
    //     }

    //     return;

    //     isValid = false;
    // }
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

    if ((bankValidity === "") | (bankAmount === "") | (bankAddress === "")) {

        if (bankValidity === "") {
            $("#bank_validity_error").text("Validity period is required.");
        }

        if (bankAmount === "") {
            $("#bank_amount_error").text("Amount is required.");
        }

        if (bankAddress === "") {
            $("#bank_address_error").text("Bank name and address is required.");
        }

        $(".nav-item").each(function () {
            if ($(this).text().trim() === "Staff & Bank Details") {
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

    // -----------------Address proof------------------------------

    // =======================================
    // ADDRESS PROOF VALIDATION
    // =======================================

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

        // // MODEL
        // if (model.val().trim() === "") {
        //     row.find(".model_error").text("Model required");
        //     equipmentValid = false;
        // } else {
        //     row.find(".model_error").text("");
        // }

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

    // $(document).on("keyup change", 'input[name="model[]"]', function () {
    //     if ($(this).val().trim() !== "") {
    //         $(this).closest("tr").find(".model_error").text("");
    //     }
    // });

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
        // alert('valid final');
        checkvalidityalterdatesformA(formData);
        // showDeclarationalterPopupformA(formData);
    }
    // if (staffCount < 4) {
    //     Swal.fire("Warning", "Please add at least 4 valid staff entries.", "warning");
    //     $('html, body').animate({
    //         scrollTop: $("#staff-table").offset().top - 100
    //     }, 800);
    //     return;
    // }

    // if (!proprietorValid) {
    //     Swal.fire("Warning", "Please fill all required fields in Proprietor / Partner section.", "warning");
    //     $('html, body').animate({
    //         scrollTop: $(".border.box-shadow-blue").offset().top - 100
    //     }, 800);
    //     return;
    // }

    // -------------------- 2. Staff Validation --------------------

    // if (!staffValid) {
    //     $('html, body').animate({
    //         scrollTop: $("#staff-table").offset().top - 100
    //     }, 800);
    //     return;
    // }

    // -------------------- 3. Submit Form via AJAX --------------------

    // $.ajax({
    //     url: "{{ route('forma.store') }}",
    //     type: "POST",
    //     data: formData,
    //     contentType: false,
    //     processData: false,
    //     headers: {
    //         "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    //     },
    //     beforeSend: function() {
    //         $(".save-draft, .submit-payment").prop("disabled", true);
    //     },
    //     success: function(response) {
    //         const loginId = response.login_id;
    //         const transactionId = response.transaction_id || 'TXN123456';
    //         const transactionDate = new Date().toLocaleDateString('en-GB');
    //         const applicantName = $("#applicant_name").val() || "Applicant";
    //         const amount = $("#amount").val() || "30000";

    //         if (actionType === "draft") {
    //             Swal.fire("Saved!", "Draft saved successfully!", "success").then(() => {
    //                 window.location.href = "/dashboard";
    //             });
    //         } else {
    //             showDeclarationalterPopupformA(loginId, loginId, transactionId, transactionDate, applicantName, amount);
    //         }

    //         $(".save-draft, .submit-payment").prop("disabled", false);
    //     },
    //     error: function(xhr) {
    //         $(".save-draft, .submit-payment").prop("disabled", false);
    //         if (xhr.status === 422) {
    //             let errors = xhr.responseJSON.errors;
    //             $.each(errors, function(field, message) {
    //                 $(`#${field}_error`).text(message);
    //             });
    //         } else {
    //             Swal.fire("Error!", "Something went wrong. Try again.", "error");
    //         }
    //     }
    // });
});


function validatealterStaffCertificate(
    staffCategory,
    certificateNo,
    firstIssue,
    validityFrom,
    validityTo
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

            valid_to: validityTo

        }

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
        formName
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
        licenseName,
        formName
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
    // alert(licence_name);
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
        width: "450px",
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
