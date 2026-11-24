/**
 * Form S – education table "Add row" / "Remove row".
 *
 * Shared by New, Digitisation, Renewal and Alteration. The row markup is NOT in this file:
 * it is cloned from <template id="education-row-template"> that the
 * user_login.partials.form-s-education-section partial renders, so Blade rows and added rows
 * always match.
 *
 * Reads from #education-container: data-max-rows, data-min-rows, data-require-file.
 */
(function () {
    'use strict';

    var MAX_MSG = 'You can add a maximum of {n} education entries.';
    var MIN_MSG = 'You must have at least one education entry.';

    function showError(text) {
        var $table = $('#education-table');
        $table.next('.education-error').remove();
        $('<div class="text-danger mt-2 education-error"></div>').text(text).insertAfter($table);
        setTimeout(function () { $('.education-error').fadeOut(); }, 7000);
    }

    function rows(container) {
        return container.querySelectorAll('.education-fields');
    }

    function refreshSerials(container) {
        // Renewal/alteration key each file input by row position (education_document[i]) and the
        // server pairs it with educational_level[i], edu_id[i]... so renumber after every add/remove.
        var indexed = container.getAttribute('data-require-file') === '1';
        rows(container).forEach(function (row, i) {
            var cell = row.querySelector('.edu-serial');
            if (cell) { cell.textContent = String(i + 1); }
            if (indexed) {
                row.setAttribute('data-edu-index', String(i));
                row.querySelectorAll('input[type="file"]').forEach(function (el) {
                    el.name = 'education_document[' + i + ']';
                });
            }
        });
    }

    // Returned application with the Education section locked: CSS blocks the mouse, this blocks the keyboard.
    function isLocked(container) {
        return !!container.closest('.fs-return-section-locked');
    }

    // Used by competency_form_ws.js after it drops duplicate rows on submit.
    window.fsRenumberEducationRows = function () {
        var container = document.getElementById('education-container');
        if (container) { refreshSerials(container); }
    };

    function addRow(container) {
        if (isLocked(container)) { return; }
        var tpl = document.getElementById('education-row-template');
        if (!tpl) { return; }

        var max = parseInt(container.getAttribute('data-max-rows'), 10) || 5;
        var count = rows(container).length;
        if (count >= max) {
            showError(MAX_MSG.replace('{n}', String(max)));
            return;
        }

        // Fill the index/serial placeholders in the template markup, then parse it as a row.
        var holder = document.createElement('template');
        holder.innerHTML = tpl.innerHTML
            .split('__IDX__').join(String(count))
            .split('__SERIAL__').join(String(count + 1));
        var row = holder.content.querySelector('tr');
        if (!row) { return; }

        // A row the applicant adds on purpose has to be filled in completely.
        row.querySelectorAll('[name="educational_level[]"], [name="institute_name[]"], [name="month_of_passing[]"], [name="year_of_passing[]"]')
            .forEach(function (el) { el.required = true; });
        if (container.getAttribute('data-require-file') === '1') {
            row.querySelectorAll('input[type="file"]').forEach(function (el) { el.required = true; });
        }

        container.appendChild(row);
        refreshSerials(container);
    }

    function removeRow(container, row) {
        if (isLocked(container)) { return; }
        // Renewal: qualifications already on the licence are read-only.
        if (row.getAttribute('data-renew-locked') === '1') { return; }

        var min = parseInt(container.getAttribute('data-min-rows'), 10) || 0;
        if (rows(container).length <= min) {
            showError(MIN_MSG);
            return;
        }
        row.remove();
        refreshSerials(container);
    }

    // jQuery delegation (not a native listener) on purpose: jQuery resolves every delegated handler
    // for a click before running any of them, so custom.js's `.remove_edu` AJAX delete still fires
    // even though this handler removes the row first.
    $(document).on('click', '#education-table .btn-tbl-add', function (e) {
        var container = document.getElementById('education-container');
        if (!container) { return; }
        e.preventDefault();
        addRow(container);
    });

    $(document).on('click', '#education-table .remove-education', function (e) {
        var container = document.getElementById('education-container');
        var row = this.closest('tr');
        if (!container || !row || !container.contains(row)) { return; }
        e.preventDefault();
        removeRow(container, row);
    });
})();
