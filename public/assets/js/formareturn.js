
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

    // Get the selected staff member's actual row_index
    let rowIndex = $row.attr("data-row-index");

    if (
        rowIndex === undefined ||
        rowIndex === null ||
        String(rowIndex).trim() === "" ||
        !/^\d+$/.test(String(rowIndex))
    ) {
        console.error(
            "Selected staff row has an invalid row_index:",
            rowIndex,
            $row[0]
        );

        $section.find(".staff-verify-error").text(
            "Staff row index is missing. Please select the staff row again."
        );

        return;
    }

    // Store row_index for upload handlers
    $section.attr("data-edit-row-index", rowIndex);

    $section.find(".upload-btn").attr("data-row-index", rowIndex);

    editingQCData = {
        id: $row.attr("data-id") || "",
        rowIndex: rowIndex,

        category: $.trim(
            $row.find('input[name="staffqc_category[]"]').val() || "QC"
        ),

        ccNo: $.trim(
            $row.find('input[name="staff_cc_no[]"]').val() ||
            $row.children("td").eq(2).text()
        ),

        holderName: $.trim(
            $row.find('input[name="certificate_holder_name[]"]').val() ||
            $row.children("td").eq(1).text()
        ),

        firstIssue: formatQCDate(
            $row.find('input[name="staff_cc_first_issue[]"]').val() ||
            $row.children("td").eq(3).find("input").val() ||
            $row.children("td").eq(3).text()
        ),

        validityFrom: formatQCDate(
            $row.find('input[name="staff_cc_validity_from[]"]').val() ||
            $row.children("td").eq(4).find("input").val() ||
            $row.children("td").eq(4).text()
        ),

        validityTo: formatQCDate(
            $row.find('input[name="staff_cc_validity_to[]"]').val() ||
            $row.children("td").eq(5).find("input").val() ||
            $row.children("td").eq(5).text()
        ),

        appDoc: $row.find(".app-doc-link").attr("href") || "",
        consDoc: $row.find(".cons-doc-link").attr("href") || "",

        appDocIndex: $row.find("input.app_doc").val() || "",
        consDocIndex: $row.find("input.cons_doc").val() || "",

        isExisting: $row.attr("data-existing") === "1"
    };

    // Populate certificate fields
    $section.find('input[name="staff_cc_no[]"]')
        .val(editingQCData.ccNo);

    $section.find('input[name="staff_cc_first_issue[]"]')
        .val(editingQCData.firstIssue);

    $section.find('input[name="staff_cc_validity_from[]"]')
        .val(editingQCData.validityFrom);

    $section.find('input[name="staff_cc_validity_to[]"]')
        .val(editingQCData.validityTo);

    // Clear previously verified name
    $("#certificate-holder-name").text("");

    // Reset verification for this edit session
    $section.attr("data-qc-verified", "0");

    // Display existing document links
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

    // Clear previously selected files
    $section.find('input[type="file"]').val("");

    // Clear validation messages
    $section.find(".text-danger").text("");

    // Show Update, but keep it disabled until verification
    $("#save_qc").hide();

    $("#update_qc")
        .show()
        .prop("disabled", true);

    // Open the form
    $section.stop(true, true).slideDown();

    // Scroll to the form
    $("html, body").animate({
        scrollTop: $section.offset().top - 100
    }, 300);
});
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
