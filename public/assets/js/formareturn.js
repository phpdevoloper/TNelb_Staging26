
/* ============================================================
   SEPARATE QC STAFF EDIT / UPDATE FUNCTIONS
   Existing Save and addQCStaffRecord() remain unchanged.
============================================================ */
let editingQCRow = null;
let editingQCData = null;

// Check whether Update can be enabled
function qcCheckUpdateButton() {
    let $section = $("#staffqc_datasection");

    let holderName = $.trim(
        $("#certificate-holder-name").text()
    );

    let ccNo = $.trim(
        $section.find('input[name="staff_cc_no[]"]').val() || ""
    );

    let firstIssue = $.trim(
        $section.find('input[name="staff_cc_first_issue[]"]').val() || ""
    );

    let validityFrom = $.trim(
        $section.find('input[name="staff_cc_validity_from[]"]').val() || ""
    );

    let validityTo = $.trim(
        $section.find('input[name="staff_cc_validity_to[]"]').val() || ""
    );

    let verified = $section.attr("data-qc-verified") === "1";

    let allFieldsFilled =
        ccNo !== "" &&
        firstIssue !== "" &&
        validityFrom !== "" &&
        validityTo !== "";

    $("#update_qc").prop(
        "disabled",
        !(verified && holderName !== "" && allFieldsFilled)
    );
}


// When any certificate field changes, require verification again
$(document).on(
    "input change",
    '#staffqc_datasection input[name="staff_cc_no[]"], ' +
    '#staffqc_datasection input[name="staff_cc_first_issue[]"], ' +
    '#staffqc_datasection input[name="staff_cc_validity_from[]"], ' +
    '#staffqc_datasection input[name="staff_cc_validity_to[]"]',
    function () {

        let $section = $("#staffqc_datasection");

        $section.attr("data-qc-verified", "0");

        $("#update_qc").prop("disabled", true);

        // Clear the previously verified name
        $("#certificate-holder-name").text("");

        // Clear previous verification errors
        $section.find(".staff-verify-error").text("");
    }
);




$(document).on("click", ".edit-qc-staff", function () {

    editingQCRow = $(this).closest("tr.staffqc-record");

    let $row = editingQCRow;
    let $section = $("#staffqc_datasection");

    let rowIndex = $row.attr("data-row-index");

    editingQCData = {

        id: $row.attr("data-id"),

        rowIndex: rowIndex,

        category: $row.find('input[name="staffqc_category[]"]').val() || "QC",

        ccNo: $row.find('input[name="staff_cc_no[]"]').val(),

        holderName: $row.find('input[name="certificate_holder_name[]"]').val(),

        firstIssue: $row.children("td").eq(3).find("input").val() ||
                    $row.children("td").eq(3).text().trim(),

        validityFrom: $row.children("td").eq(4).find("input").val() ||
                      $row.children("td").eq(4).text().trim(),

        validityTo: $row.children("td").eq(5).find("input").val() ||
                    $row.children("td").eq(5).text().trim(),

        appDoc: $row.find(".app-doc-link").attr("href") || "",

        consDoc: $row.find(".cons-doc-link").attr("href") || ""

    };


    // Hidden values
    $("#edit_qc_id").val(editingQCData.id);

    $("#edit_qc_row_index").val(rowIndex);

    $("#edit_qc_old_cc_no").val(editingQCData.ccNo);

    $("#edit_qc_holder_name").val(editingQCData.holderName);


    // Populate your EXISTING inputs
    $section.find('input[name="staffqc_category[]"]').last()
        .val(editingQCData.category);

    $section.find('input[name="staff_cc_no[]"]').last()
        .val(editingQCData.ccNo);

    $section.find('input[name="staff_cc_first_issue[]"]').last()
        .val(formatQCDate(editingQCData.firstIssue));

    $section.find('input[name="staff_cc_validity_from[]"]').last()
        .val(formatQCDate(editingQCData.validityFrom));

    $section.find('input[name="staff_cc_validity_to[]"]').last()
        .val(formatQCDate(editingQCData.validityTo));


    // Holder name
    $("#certificate-holder-name").text(editingQCData.holderName);


    // Preserve row index for document upload
    $section.attr("data-edit-row-index", rowIndex);

    $section.find(".upload-btn").attr("data-row-index", rowIndex);


    // Existing document links
    qcShowExistingDocument(
        ".app-doc-file",
        editingQCData.appDoc,
        "Appointment Letter"
    );

    qcShowExistingDocument(
        ".cons-doc-file",
        editingQCData.consDoc,
        "Consent Letter"
    );


    // Show upload section
    $("#qc_upload_section").show();

    // Existing record is already verified
    $section.attr("data-qc-verified", "1");


    // Button visibility
    $("#update_qc").show().prop("disabled", false);

    $("#save_qc").hide();


    // Open section
    $section.stop(true, true).slideDown();


    $("html, body").animate({

        scrollTop: $section.offset().top - 100

    }, 300);

});


// Date format helper: d-m-Y to Y-m-d
function formatQCDate(date) {

    if (!date) return "";

    date = $.trim(date);

    if (/^\d{4}-\d{2}-\d{2}$/.test(date)) {
        return date;
    }

    let parts = date.split("-");

    if (parts.length === 3) {

        return parts[2] + "-" +
               parts[1].padStart(2, "0") + "-" +
               parts[0];

    }

    return "";

}
// ==========================================================
// ESCAPE HTML VALUES
// ==========================================================

function escapeQC(value) {
    return $("<div>").text(value ?? "").html();
}

// ==========================================================
// FORMAT DATE
// ==========================================================

function formatQCDisplayDate(value) {

    if (!value) return "";

    let parts = value.split("-");

    if (parts.length === 3) {
        return `${parts[2]}-${parts[1]}-${parts[0]}`;
    }

    return value;
}

// ==========================================================
// BUILD QC STAFF TABLE ROW
// ==========================================================

function buildQCStaffRow(staff, serialNo, rowClass = "") {

    let appDocLink = "";
    let consDocLink = "";

    if (staff.app_doc_url) {

        appDocLink = `
            <a href="${escapeQC(staff.app_doc_url)}"
               target="_blank"
               class="app-doc-link">
                <i class="fa fa-file-pdf-o text-danger"></i>
                Appointment Letter
            </a>
        `;
    }

    if (staff.cons_doc_url) {

        consDocLink = `
            <a href="${escapeQC(staff.cons_doc_url)}"
               target="_blank"
               class="cons-doc-link">
                <i class="fa fa-file-pdf-o text-danger"></i>
                Consent Letter
            </a>
        `;
    }

    return `
        <tr class="staffqc-record ${escapeQC(rowClass)}"
            data-id="${escapeQC(staff.id)}"
            data-row-index="${escapeQC(staff.row_index)}"
            data-staff-flag="1">

            <td>${serialNo}.</td>

            <td>

                <input type="hidden"
                    name="row_index[]"
                    value="${escapeQC(staff.row_index)}">

                <input type="hidden"
                    name="staffqc_id[]"
                    value="${escapeQC(staff.id)}">

                <input type="hidden"
                    name="staffqc_category[]"
                    value="${escapeQC(staff.staff_category)}">

                <input type="hidden"
                    name="staff_cc_no[]"
                    value="${escapeQC(staff.staff_cc_no)}">

                <input type="hidden"
                    name="certificate_holder_name[]"
                    value="${escapeQC(staff.staff_name)}">

                <input type="hidden"
                    class="qc-first-issue"
                    value="${escapeQC(staff.staff_cc_first_issue)}">

                <input type="hidden"
                    class="qc-validity-from"
                    value="${escapeQC(staff.staff_cc_validity_from)}">

                <input type="hidden"
                    class="qc-validity-to"
                    value="${escapeQC(staff.staff_cc_validity_to)}">

                ${escapeQC(staff.staff_name)}

            </td>

            <td>${escapeQC(staff.staff_cc_no)}</td>

            <td>${formatQCDisplayDate(staff.staff_cc_first_issue)}</td>

            <td>${formatQCDisplayDate(staff.staff_cc_validity_from)}</td>

            <td>${formatQCDisplayDate(staff.staff_cc_validity_to)}</td>

            <td>
                <div class="file-link">
                    ${appDocLink}
                    <br>
                    ${consDocLink}
                </div>
            </td>

            <td>
                <button type="button"
                    class="btn btn-warning edit-qc-staff">
                    <i class="fa fa-edit"></i> Edit
                </button>
            </td>

        </tr>
    `;
}

// ==========================================================
// UPDATE QC STAFF
// ==========================================================

$(document).on("click", "#update_qc", function () {

    let $section = $("#staffqc_datasection");

    // ======================================================
    // 1. GET EDITING DETAILS
    // ======================================================

    let applicationId = $("#qc_application_name").val();
    let id = $("#edit_qc_id").val();
    let rowIndex = $("#edit_qc_row_index").val();
    let oldCCNo = $("#edit_qc_old_cc_no").val();

    // ======================================================
    // 2. GET FORM VALUES
    // ======================================================

    let newCCNo = $.trim(
        $section.find('input[name="staff_cc_no[]"]').last().val()
    );

    let holderName = $("#edit_qc_holder_name").val();

    let category = $section
        .find('input[name="staffqc_category[]"]')
        .last().val();

    let firstIssue = $section
        .find('input[name="staff_cc_first_issue[]"]')
        .last().val();

    let validityFrom = $section
        .find('input[name="staff_cc_validity_from[]"]')
        .last().val();

    let validityTo = $section
        .find('input[name="staff_cc_validity_to[]"]')
        .last().val();

    // ======================================================
    // 3. VALIDATION
    // ======================================================

    if (
        !id ||
        !applicationId ||
        !rowIndex ||
        !newCCNo ||
        !firstIssue ||
        !validityFrom ||
        !validityTo
    ) {

        Swal.fire({
            icon: "warning",
            title: "Required Fields",
            text: "Please fill all required fields.",
            width: 450,
            confirmButtonText: "OK"
        });

        return;
    }

    // ======================================================
    // 4. REQUEST DATA
    // ======================================================

    let formData = {

        _token: $('meta[name="csrf-token"]').attr("content"),

        id: id,
        application_id: applicationId,
        row_index: rowIndex,
        staff_category: category,
        staff_name: holderName,
        old_staff_cc_no: oldCCNo,
        staff_cc_no: newCCNo,
        staff_cc_first_issue: firstIssue,
        staff_cc_validity_from: validityFrom,
        staff_cc_validity_to: validityTo

    };

    // ======================================================
    // 5. AJAX
    // ======================================================

    $.ajax({

        url: BASE_URL + "/update-qc-staff",

        type: "POST",

        data: formData,

        beforeSend: function () {

            $("#update_qc")
                .prop("disabled", true)
                .text("Updating...");

        },

        success: function (response) {

            if (response.status !== "success") {

                Swal.fire({
                    icon: "error",
                    title: "Update Failed",
                    text: response.message || "Unable to update QC staff.",
                    width: 450
                });

                return;
            }

            let staff = response.staff;

            // ==============================================
            // CASE 1: CC NUMBER CHANGED
            // ==============================================

            if (response.cc_changed == 1) {

                // Find the existing old record using its ID.
                let $oldRow = $("#staffqc-records tr.staffqc-record")
                    .filter(function () {

                        return String($(this).attr("data-id")) ===
                               String(response.old_staff_id);

                    })
                    .first();

                if (!$oldRow.length) {

                    // Database update succeeded, but old row was not found.
                    // Do not create a duplicate new row.
                    Swal.fire({
                        icon: "warning",
                        title: "Table Refresh Required",
                        text: "The record was updated, but its previous table row could not be located. Please refresh the page.",
                        width: 450
                    });

                    return;
                }

                // Mark old record inactive.
                $oldRow.remove();

                // Remove edit button from old record.
                $oldRow.find(".edit-qc-staff").remove();

                // Display inactive label.
                if (!$oldRow.find(".qc-inactive-label").length) {

                    $oldRow.find("td:last").append(
                        '<span class="qc-inactive-label">Inactive</span>'
                    );
                }

                // Create new active row at the bottom.
                let serialNo =
                    $("#staffqc-records tr.staffqc-record").length + 1;

                let newRow = buildQCStaffRow(
                    staff,
                    serialNo,
                    "qc-staff-new"
                );

                // Insert before Fees row, if present.
                let $feesRow = $("#staffqc-records tr").filter(function () {

                    return $(this)
                        .children("td, th")
                        .first()
                        .text()
                        .trim()
                        .toLowerCase() === "fees";

                }).first();

                if ($feesRow.length) {
                    $feesRow.before(newRow);
                } else {
                    $("#staffqc-records").append(newRow);
                }

            }

            // ==============================================
            // CASE 2: CC NUMBER NOT CHANGED
            // ==============================================

            else {

                if (!editingQCRow || !editingQCRow.length) {

                    Swal.fire({
                        icon: "error",
                        title: "Row Not Found",
                        text: "Unable to locate the QC staff row.",
                        width: 450
                    });

                    return;
                }

                let serialNo = editingQCRow
                    .children("td:first")
                    .text()
                    .replace(".", "")
                    .trim();

                let updatedRow = buildQCStaffRow(
                    staff,
                    serialNo
                );

                editingQCRow.replaceWith(updatedRow);
            }

            // ==============================================
            // UPDATE SERIAL NUMBERS
            // ==============================================

            $("#staffqc-records tr.staffqc-record").each(function (index) {

                $(this)
                    .children("td:first")
                    .text((index + 1) + ".");

            });

            // ==============================================
            // CLOSE EDIT FORM
            // ==============================================

            $section.slideUp();

            editingQCRow = null;
            editingQCData = null;

            // ==============================================
            // SUCCESS ALERT
            // ==============================================

            Swal.fire({
                icon: "success",
                title: "Success",
                text: response.message,
                width: 450,
                confirmButtonText: "OK"
            });

        },

        error: function (xhr) {

            console.log(xhr.responseText);

            Swal.fire({
                icon: "error",
                title: "Server Error",
                text: xhr.responseJSON?.message ||
                      "QC staff update failed.",
                width: 450,
                confirmButtonText: "OK"
            });

        },

        complete: function () {

            $("#update_qc")
                .prop("disabled", false)
                .text("Update");

        }

    });

});

// $(document).on("click", ".edit-qc-staff", function () {

//     editingQCRow = $(this).closest("tr.staffqc-record");

//     let $row = editingQCRow;
//     let $section = $("#staffqc_datasection");

//     // Get the selected staff member's actual row_index
//     let rowIndex = $row.attr("data-row-index");

//     if (
//         rowIndex === undefined ||
//         rowIndex === null ||
//         String(rowIndex).trim() === "" ||
//         !/^\d+$/.test(String(rowIndex))
//     ) {
//         console.error(
//             "Selected staff row has an invalid row_index:",
//             rowIndex,
//             $row[0]
//         );

//         $section.find(".staff-verify-error").text(
//             "Staff row index is missing. Please select the staff row again."
//         );

//         return;
//     }

//     // Store row_index for upload handlers
//     $section.attr("data-edit-row-index", rowIndex);

//     $section.find(".upload-btn").attr("data-row-index", rowIndex);

//     editingQCData = {
//         id: $row.attr("data-id") || "",
//         rowIndex: rowIndex,

//         category: $.trim(
//             $row.find('input[name="staffqc_category[]"]').val() || "QC"
//         ),

//         ccNo: $.trim(
//             $row.find('input[name="staff_cc_no[]"]').val() ||
//             $row.children("td").eq(2).text()
//         ),

//         holderName: $.trim(
//             $row.find('input[name="certificate_holder_name[]"]').val() ||
//             $row.children("td").eq(1).text()
//         ),

//         firstIssue: formatQCDate(
//             $row.find('input[name="staff_cc_first_issue[]"]').val() ||
//             $row.children("td").eq(3).find("input").val() ||
//             $row.children("td").eq(3).text()
//         ),

//         validityFrom: formatQCDate(
//             $row.find('input[name="staff_cc_validity_from[]"]').val() ||
//             $row.children("td").eq(4).find("input").val() ||
//             $row.children("td").eq(4).text()
//         ),

//         validityTo: formatQCDate(
//             $row.find('input[name="staff_cc_validity_to[]"]').val() ||
//             $row.children("td").eq(5).find("input").val() ||
//             $row.children("td").eq(5).text()
//         ),

//         appDoc: $row.find(".app-doc-link").attr("href") || "",
//         consDoc: $row.find(".cons-doc-link").attr("href") || "",

//         appDocIndex: $row.find("input.app_doc").val() || "",
//         consDocIndex: $row.find("input.cons_doc").val() || "",

//         isExisting: $row.attr("data-existing") === "1"
//     };

//     // Populate certificate fields
//     $section.find('input[name="staff_cc_no[]"]')
//         .val(editingQCData.ccNo);

//     $section.find('input[name="staff_cc_first_issue[]"]')
//         .val(editingQCData.firstIssue);

//     $section.find('input[name="staff_cc_validity_from[]"]')
//         .val(editingQCData.validityFrom);

//     $section.find('input[name="staff_cc_validity_to[]"]')
//         .val(editingQCData.validityTo);

//     // Clear previously verified name
//     $("#certificate-holder-name").text("");

//     // Reset verification for this edit session
//     $section.attr("data-qc-verified", "0");

//     // Display existing document links
//     qcShowExistingDocument(
//         ".app-doc-file",
//         editingQCData.appDoc,
//         "Appointment Letter"
//     );

//     qcShowExistingDocument(
//         ".cons-doc-file",
//         editingQCData.consDoc,
//         "Consent Letter"
//     );

//     // Clear previously selected files
//     $section.find('input[type="file"]').val("");

//     // Clear validation messages
//     $section.find(".text-danger").text("");

//     // Show Update, but keep it disabled until verification
//     $("#save_qc").hide();

//     $("#update_qc")
//         .show()
//         .prop("disabled", true);

//     // Open the form
//     $section.stop(true, true).slideDown();

//     // Scroll to the form
//     $("html, body").animate({
//         scrollTop: $section.offset().top - 100
//     }, 300);
// });
// Convert date to YYYY-MM-DD for input[type="date"]
function formatQCDate(dateValue) {
    if (!dateValue) {
        return "";
    }

    dateValue = String(dateValue).trim();

    // Already in YYYY-MM-DD format
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateValue)) {
        return dateValue;
    }

    // Convert DD/MM/YYYY or DD-MM-YYYY to YYYY-MM-DD
    let match = dateValue.match(/^(\d{2})[\/-](\d{2})[\/-](\d{4})$/);

    if (match) {
        return `${match[3]}-${match[2]}-${match[1]}`;
    }

    return "";
}
/* ============================================================
   SHOW EXISTING DOCUMENT IN THE EDIT FORM
============================================================ */

function qcShowExistingDocument(inputSelector, url, label) {

    let $input = $("#staffqc_datasection").find(inputSelector);
    let $fileLink = $input.siblings(".file-link");

    if (!$fileLink.length) {
        $fileLink = $input.parent().find(".file-link").first();
    }

    if (url) {
        $fileLink.html(`
            <a href="${$("<div>").text(url).html()}"
               target="_blank"
               class="existing-qc-document">
                <i class="fa fa-file-pdf-o" style="color:red;"></i>
                ${label}
            </a>
        `);
    } else {
        $fileLink.empty();
    }
}


/* ============================================================
   UPDATE BUTTON
============================================================ */
$(document).on("click", "#update_qc", function () {

    

    if (!editingQCRow || !editingQCRow.length || !editingQCData) {
        return;
    }

    let $section = $("#staffqc_datasection");

    $section.find(".text-danger").text("");

    let hasError = false;

    // Read edited values
    let category = editingQCData.category;

    let ccNo = $.trim(
        $section.find('input[name="staff_cc_no[]"]').val() || ""
    );

    let firstIssue = $.trim(
        $section.find('input[name="staff_cc_first_issue[]"]').val() || ""
    );

    let validityFrom = $.trim(
        $section.find('input[name="staff_cc_validity_from[]"]').val() || ""
    );

    let validityTo = $.trim(
        $section.find('input[name="staff_cc_validity_to[]"]').val() || ""
    );

    let holderName = $.trim(
        $("#certificate-holder-name").text()
    );

    // Validate required fields
    if (!ccNo) {
        $section.find(".staff-cc-error")
            .text("Please enter Certificate Number.");
        hasError = true;
    }

    if (!firstIssue) {
        $section.find(".staff-first-issue-error")
            .text("Please enter Certificate First Issue.");
        hasError = true;
    }

    if (!validityFrom) {
        $section.find(".staff-validity-from-error")
            .text("Please enter Validity From Date.");
        hasError = true;
    }

    if (!validityTo) {
        $section.find(".staff-validity-to-error")
            .text("Please enter Validity To Date.");
        hasError = true;
    }

    if (!holderName) {
        $section.find(".staff-verify-error")
            .text("Certificate holder name is required.");
        hasError = true;
    }

    // Validate date sequence
    if (firstIssue && validityFrom && firstIssue > validityFrom) {
        $section.find(".staff-first-issue-error")
            .text("First Issue Date cannot be after Validity From Date.");
        hasError = true;
    }

    if (validityFrom && validityTo && validityFrom > validityTo) {
        $section.find(".staff-validity-to-error")
            .text("Validity To Date cannot be before Validity From Date.");
        hasError = true;
    }

    if (hasError) {
        return;
    }

    // Duplicate check, excluding the row being edited
    let isDuplicate = false;

    $("#staffqc-records tr.staffqc-record")
        .not(editingQCRow)
        .each(function () {

            let $row = $(this);

            let existingCategory = $.trim(
                $row.find('input[name="staffqc_category[]"]').val() || ""
            ).toUpperCase();

            let existingCcNo = $.trim(
                $row.find('input[name="staff_cc_no[]"]').val() ||
                $row.children("td").eq(2).text()
            ).toUpperCase();

            if (
                existingCategory === String(category).toUpperCase() &&
                existingCcNo === ccNo.toUpperCase()
            ) {
                isDuplicate = true;
                return false;
            }
        });

    if (isDuplicate) {
        $section.find(".staff-cc-error")
            .text("This certificate number is already added.");
        return;
    }

    // Keep existing documents unless newly uploaded documents exist
    let appDocLink =
        qcGetUploadedDocument(".app-doc-file") ||
        editingQCData.appDoc;

    let consDocLink =
        qcGetUploadedDocument(".cons-doc-file") ||
        editingQCData.consDoc;

    if (!appDocLink) {
        $section.find(".app_doc_upload_error")
            .text("Please upload Appointment Letter.");
        hasError = true;
    }

    if (!consDocLink) {
        $section.find(".cons_doc_upload_error")
            .text("Please upload Consent Letter.");
        hasError = true;
    }

    if (hasError) {
        return;
    }

    // Update the existing row in place
    qcReplaceStaffRow({
        row: editingQCRow,
        id: editingQCData.id,
        rowIndex: editingQCData.rowIndex,
        category: category,
        holderName: holderName,
        ccNo: ccNo,
        firstIssue: firstIssue,
        validityFrom: validityFrom,
        validityTo: validityTo,
        appDoc: appDocLink,
        consDoc: consDocLink,
        appDocIndex: editingQCData.appDocIndex,
        consDocIndex: editingQCData.consDocIndex,
        isExisting: editingQCData.isExisting
    });

    // Recalculate fees if required
    // updateQCStaffTotalFee();

    // Reset editing state
    editingQCRow = null;
    editingQCData = null;

    // Toggle buttons
    $("#update_qc").hide();
    $("#save_qc").show();

    // Clear existing document links
    $section.find(".file-link").empty();

    // Reset form fields and errors
    // resetQCStaffForm();

    // Clear file inputs
    $section.find('input[type="file"]').val("");

    // Clear certificate holder name
    $("#certificate-holder-name").text("");

    // Remove edit-specific row index
    $section.removeAttr("data-edit-row-index");

    // Remove row index from upload buttons
    $section.find(".upload-btn").removeAttr("data-row-index");

    // Close the form after updating
    $section.stop(true, true).slideUp(300);

});

/* ============================================================
   GET DOCUMENT LINK FROM UPLOAD AREA
============================================================ */

function qcGetUploadedDocument(inputSelector) {

    let $input = $("#staffqc_datasection").find(inputSelector);

    let $fileLink = $input.siblings(".file-link");

    if (!$fileLink.length) {
        $fileLink = $input.parent().find(".file-link").first();
    }

    return $fileLink.find("a:not(.existing-qc-document)").first()
        .attr("href") || "";
}


/* ============================================================
   REPLACE EXISTING ROW
============================================================ */

function qcReplaceStaffRow(data) {

    let $row = data.row;

    // Preserve existing row ID and row index.
    $row.attr("data-id", data.id || "");
    $row.attr("data-row-index", data.rowIndex || "");

    if (data.isExisting) {
        $row.attr("data-existing", "1");
    }

    // Certificate holder
    $row.children("td").eq(1).html(`
        <input type="hidden"
               name="row_index[]"
               value="${$("<div>").text(data.rowIndex).html()}">

        <input type="hidden"
               name="staffqc_id[]"
               value="${$("<div>").text(data.id).html()}">

        <input type="hidden"
               name="staffqc_category[]"
               value="${$("<div>").text(data.category).html()}">

        <input type="hidden"
               name="certificate_holder_name[]"
               value="${$("<div>").text(data.holderName).html()}">

        ${$("<div>").text(data.holderName).html()}
    `);

    // Certificate number
    $row.children("td").eq(2).html(`
        <input type="hidden"
               name="staff_cc_no[]"
               value="${$("<div>").text(data.ccNo).html()}">

        ${$("<div>").text(data.ccNo).html()}
    `);

    // First issue
    $row.children("td").eq(3).html(`
        <input type="hidden"
               name="staff_cc_first_issue[]"
               value="${$("<div>").text(data.firstIssue).html()}">

        ${$("<div>").text(data.firstIssue).html()}
    `);

    // Validity from
    $row.children("td").eq(4).html(`
        <input type="hidden"
               name="staff_cc_validity_from[]"
               value="${$("<div>").text(data.validityFrom).html()}">

        ${$("<div>").text(data.validityFrom).html()}
    `);

    // Validity to
    $row.children("td").eq(5).html(`
        <input type="hidden"
               name="staff_cc_validity_to[]"
               value="${$("<div>").text(data.validityTo).html()}">

        ${$("<div>").text(data.validityTo).html()}
    `);

    // Attachments: retain original row and upload indexes.
    let appDocHtml = data.appDoc
        ? `<a href="${$("<div>").text(data.appDoc).html()}"
              target="_blank" class="app-doc-link">
              <i class="fa fa-file-pdf-o" style="color:red;"></i>
              Appointment Letter
           </a>`
        : "";

    let consDocHtml = data.consDoc
        ? `<a href="${$("<div>").text(data.consDoc).html()}"
              target="_blank" class="cons-doc-link">
              <i class="fa fa-file-pdf-o" style="color:red;"></i>
              Consent Letter
           </a>`
        : "";

    $row.children("td").eq(6).html(`
        <div class="file-link">
            ${appDocHtml}
            <br>
            ${consDocHtml}
        </div>

        <input type="hidden"
               name="app_doc[]"
               class="app_doc"
               value="${$("<div>").text(data.appDocIndex).html()}">

        <input type="hidden"
               name="cons_doc[]"
               class="cons_doc"
               value="${$("<div>").text(data.consDocIndex).html()}">
    `);

    // The original action buttons remain untouched.
}


/* ============================================================
   CANCEL EDIT MODE
============================================================ */


$('#cancel_qc').on('click', function () {

    const $section = $('#staffqc_datasection');

    // Clear all inputs
    $section.find('input').each(function () {
        const type = (this.type || '').toLowerCase();

        if (type === 'file') {
            this.value = '';
        } else if (type === 'checkbox' || type === 'radio') {
            this.checked = false;
        } else {
            $(this).val('');
        }
    });

    // Restore default category
    $section.find('input[name="staffqc_category[]"]').val('QC');

    // Clear validation messages
    $section.find(
        '.staff-cc-error, .staff-first-issue-error, ' +
        '.staff-validity-from-error, .staff-validity-to-error, ' +
        '.staff-verify-error, .qc-staff-errors, ' +
        '.app_doc_upload_error, .cons_doc_upload_error, .upload-error'
    ).text('');

    // Clear certificate holder and previews
    $('#certificate-holder-name').text('');
    $section.find('.file-link').empty();

    // Reset buttons
    $('#save_qc').prop('disabled', true);

    $section.find('.upload-btn')
        .prop('disabled', false)
        .html('<i class="fa fa-upload"></i> Upload');

    // Smoothly close upload section, then close the entire form
    $('#qc_upload_section').stop(true, true).slideUp(200, function () {
        $section.stop(true, true).slideUp(350);
    });
});



/* ============================================================
   INITIAL BUTTON STATE
============================================================ */
