/**
 * Form S alteration — verify popup + top option bar on form page.
 */
(function ($) {
    'use strict';

    var parentName = '';
    var parentAddress = '';

    function newWorkRows() {
        return $('.js-work-container .work-fields:not(.fs-alt-existing-work)');
    }

    function scrollToSection(selector) {
        var el = document.querySelector(selector);
        if (!el) return;
        var top = el.getBoundingClientRect().top + window.pageYOffset - 100;
        window.scrollTo({ top: top, behavior: 'smooth' });
    }

    function updateChipStyles() {
        $('#fsAltOptName').closest('.fs-alt-option-card').toggleClass('is-active', $('#fsAltOptName').is(':checked'));
        $('#fsAltOptAddress').closest('.fs-alt-option-card').toggleClass('is-active', $('#fsAltOptAddress').is(':checked'));
        $('#fsAltOptWork').closest('.fs-alt-option-card').toggleClass('is-active', $('#fsAltOptWork').is(':checked'));
    }

    function revokeProofBlob($compact) {
        if (!$compact || !$compact.length) return;
        var url = $compact.data('proofBlobUrl');
        if (!url) return;
        try { URL.revokeObjectURL(url); } catch (err) { /* ignore */ }
        $compact.removeData('proofBlobUrl');
    }

    function updateProofCompact(input) {
        var $input = $(input);
        var $compact = $input.closest('.fs-alt-proof-compact');
        if (!$compact.length) return;

        var file = input.files && input.files[0];
        var $status = $compact.find('.fs-alt-proof-compact__status');
        var $view = $compact.find('.fs-alt-proof-view');
        var $icon = $compact.find('.fs-alt-proof-file-icon');

        if (!file) {
            revokeProofBlob($compact);
            $compact.removeClass('is-filled');
            $status.attr('hidden', 'hidden');
            $compact.find('.fs-alt-proof-fname').text('').attr('title', '');
            $view.attr('href', '#').removeClass('is-image');
            $input.removeAttr('data-has-local-file');
            return;
        }

        revokeProofBlob($compact);
        var blobUrl = URL.createObjectURL(file);
        $compact.data('proofBlobUrl', blobUrl);
        var isImage = file.type.indexOf('image/') === 0;

        $compact.addClass('is-filled');
        $status.removeAttr('hidden');
        $compact.find('.fs-alt-proof-fname').text(file.name).attr('title', file.name);
        $view.attr('href', blobUrl).toggleClass('is-image', isImage);
        $view.find('.fa').attr('class', isImage ? 'fa fa-image' : 'fa fa-file-pdf-o');
        $icon.toggleClass('is-image', isImage).find('.fa').attr('class', isImage ? 'fa fa-file-image-o' : 'fa fa-file-pdf-o');
        $input.attr('data-has-local-file', '1');
    }

    function clearAltProofInput(selector) {
        var $input = $(selector);
        if (!$input.length) return;
        $input.val('');
        updateProofCompact($input[0]);
    }

    function initProofCompact() {
        $(document).on('change', '#name_alteration_proof, #address_alteration_proof', function () {
            var file = this.files && this.files[0];
            if (file) {
                var allowed = ['application/pdf', 'image/jpeg', 'image/png'];
                if (allowed.indexOf(file.type) === -1 && !/\.(pdf|jpe?g|png)$/i.test(file.name)) {
                    window.alert('Only PDF, JPG, PNG files are allowed.');
                    clearAltProofInput(this);
                    syncAlterFlagsFromForm();
                    return;
                }
                if (file.size > 200 * 1024) {
                    window.alert('File size should not exceed 200 KB.');
                    clearAltProofInput(this);
                    syncAlterFlagsFromForm();
                    return;
                }
            }
            updateProofCompact(this);
            syncAlterFlagsFromForm();
        });

        $(document).on('click', '.fs-alt-proof-clear', function (e) {
            e.preventDefault();
            clearAltProofInput($(this).closest('.fs-alt-proof-compact').find('.fs-alt-proof-input'));
            syncAlterFlagsFromForm();
        });
    }

    function enableNewWorkRowFields($row) {
        $row.show().find('input, textarea, select, button')
            .not('.work-duration-y, .work-duration-m, .work-duration-d, .work-year-total-display')
            .prop('disabled', false)
            .prop('readonly', false);
        $row.find('.work-duration-y, .work-duration-m, .work-duration-d, .work-year-total-display')
            .prop('disabled', true)
            .prop('readonly', true);
    }

    function hasNewWorkRows() {
        var hasNew = false;
        newWorkRows().each(function () {
            var $row = $(this);
            var employer = ($row.find('[name="work_employer_name[]"]').val() || $row.find('[name="work_level[]"]').val() || '').trim();
            var desig = ($row.find('[name="designation[]"]').val() || '').trim();
            if (employer && desig) hasNew = true;
        });
        return hasNew;
    }

    function clearNewWorkRows() {
        newWorkRows().remove();
    }

    function applyAlterationOptions(scrollTarget) {
        var optName = $('#fsAltOptName').is(':checked');
        var optAddress = $('#fsAltOptAddress').is(':checked');
        var optWork = $('#fsAltOptWork').is(':checked');

        $('#fsAltOptionsError').text('');
        updateChipStyles();

        if (optName) {
            $('#Applicant_Name').prop('readonly', false);
            $('#fsAltNameProofPanel').addClass('is-visible');
        } else {
            $('#Applicant_Name').val(parentName).prop('readonly', true);
            $('#fsAltNameProofPanel').removeClass('is-visible');
            clearAltProofInput('#name_alteration_proof');
        }

        if (optAddress) {
            $('#applicants_address').prop('readonly', false);
            $('#fsAltAddressProofPanel').addClass('is-visible');
        } else {
            $('#applicants_address').val(parentAddress).prop('readonly', true);
            $('#fsAltAddressProofPanel').removeClass('is-visible');
            clearAltProofInput('#address_alteration_proof');
        }

        if (optWork) {
            $('#work-exp-add-btn-previous').prop('disabled', false);
        } else {
            $('#work-exp-add-btn-previous').prop('disabled', true);
            clearNewWorkRows();
        }

        syncAlterFlagsFromForm();

        if (scrollTarget === 'name') scrollToSection('#fsAltSectionApplicant');
        if (scrollTarget === 'address') scrollToSection('#fsAltSectionApplicant');
        if (scrollTarget === 'work') scrollToSection('#fsAltSectionWork');
    }

    function syncAlterFlagsFromForm() {
        var optName = $('#fsAltOptName').is(':checked');
        var optAddress = $('#fsAltOptAddress').is(':checked');
        var optWork = $('#fsAltOptWork').is(':checked');
        var name = ($('#Applicant_Name').val() || '').trim();
        var addr = ($('#applicants_address').val() || '').trim();
        if (addr === 'Not provided') addr = '';
        var alterName = optName && name !== parentName;
        var alterAddress = optAddress && addr !== parentAddress;
        var alterWork = optWork && hasNewWorkRows();

        $('#alter_name').val(alterName ? '1' : '0');
        $('#alter_address').val(alterAddress ? '1' : '0');
        $('#alter_workexp').val(alterWork ? '1' : '0');

        return { optName: optName, optAddress: optAddress, optWork: optWork, alterName: alterName, alterAddress: alterAddress, alterWork: alterWork };
    }
    window.syncAlterFlagsFromForm = syncAlterFlagsFromForm;

    function lockNonAlterableFields() {
        $('#Applicant_Name, #applicants_address').prop('readonly', true);
        $('.fs-alt-existing-work').show();
        $('#work-exp-add-btn-previous').prop('disabled', true);
        $('#fsAltNameProofPanel, #fsAltAddressProofPanel').removeClass('is-visible');
        $('#fs-7b-root input[name="current_work_board_member"]').prop('disabled', true);

        var newWorkFieldSelector = '.js-work-container .work-fields:not(.fs-alt-existing-work) input, ' +
            '.js-work-container .work-fields:not(.fs-alt-existing-work) textarea, ' +
            '.js-work-container .work-fields:not(.fs-alt-existing-work) select, ' +
            '.js-work-container .work-fields:not(.fs-alt-existing-work) button';

        $('#competency_form_ws')
            .find('input, textarea, select, button')
            .not('#Applicant_Name, #applicants_address, #name_alteration_proof, #address_alteration_proof')
            .not('#fsAltOptName, #fsAltOptAddress, #fsAltOptWork')
            .not(newWorkFieldSelector)
            .each(function () {
                var $el = $(this);
                if ($el.attr('type') === 'hidden') return;
                if ($el.closest('#fsAltNameProofPanel, #fsAltAddressProofPanel, #fsAltOptionsBar').length) return;
                if ($el.attr('id') === 'submitPaymentBtn' || $el.attr('id') === 'saveDraftBtn' || $el.attr('id') === 'fsAltCancelBtn' || $el.attr('id') === 'declarationCheckbox') return;
                if ($el.is('select, input[type="file"], input[type="radio"], input[type="checkbox"]')) {
                    $el.prop('disabled', true);
                } else if ($el.is('input, textarea')) {
                    $el.prop('readonly', true);
                } else if ($el.is('button')) {
                    $el.prop('disabled', true);
                }
            });

        $('.fs-section-edit-toggle').hide();
    }

    function validateAlterationBeforePreview() {
        var flags = syncAlterFlagsFromForm();

        if (!flags.optName && !flags.optAddress && !flags.optWork) {
            $('#fsAltOptionsError').text('Select at least one alteration option above.');
            Swal.fire('Validation', 'Select at least one alteration option at the top of the form.', 'warning');
            return false;
        }

        if (flags.alterName) {
            var name = ($('#Applicant_Name').val() || '').trim();
            if (!name || name === parentName) {
                Swal.fire('Validation', 'Enter a new applicant name different from the current name.', 'warning');
                return false;
            }
            if (!$('#name_alteration_proof')[0] || !$('#name_alteration_proof')[0].files.length) {
                Swal.fire('Validation', 'Upload supporting proof for the name change.', 'warning');
                return false;
            }
        }

        if (flags.alterAddress) {
            var addr = ($('#applicants_address').val() || '').trim();
            if (!addr || addr === parentAddress) {
                Swal.fire('Validation', 'Enter a new address different from the current address.', 'warning');
                return false;
            }
            if (!$('#address_alteration_proof')[0] || !$('#address_alteration_proof')[0].files.length) {
                Swal.fire('Validation', 'Upload supporting proof for the address change.', 'warning');
                return false;
            }
        }

        if (flags.optWork && !hasNewWorkRows() && !flags.alterName && !flags.alterAddress) {
            Swal.fire('Validation', 'Add at least one new work experience entry.', 'warning');
            return false;
        }

        if (!flags.alterName && !flags.alterAddress && !flags.alterWork) {
            Swal.fire('Validation', 'Make the selected change(s) before submitting.', 'warning');
            return false;
        }

        if (!$('#declarationCheckbox').is(':checked')) {
            $('#checkboxError').removeClass('d-none');
            $('#declarationCheckbox').focus();
            return false;
        }
        $('#checkboxError').addClass('d-none');

        return true;
    }

    function prepareAlterationFormForSubmit() {
        $('.fs-alt-existing-work').find('input, textarea, select').prop('disabled', true);
        newWorkRows().each(function () {
            var $row = $(this);
            $row.find('input, textarea, select')
                .not('.work-duration-y, .work-duration-m, .work-duration-d, .work-year-total-display')
                .prop('disabled', false);
            var employer = ($row.find('[name="work_employer_name[]"]').val() || '').trim();
            $row.find('.work-level-sync').val(employer);
        });
    }

    function buildAlterationFormData() {
        prepareAlterationFormForSubmit();
        syncAlterFlagsFromForm();
        var formData = new FormData(document.getElementById('competency_form_ws'));
        formData.set('alter_name', $('#alter_name').val() || '0');
        formData.set('alter_address', $('#alter_address').val() || '0');
        formData.set('alter_workexp', $('#alter_workexp').val() || '0');
        return formData;
    }

    function submitAlterationRequest() {
        var formData = buildAlterationFormData();

        return $.ajax({
            url: window.formSAltStoreUrl || '/form_s_alt/store',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    }

    function saveAlterationDraftRequest() {
        var formData = buildAlterationFormData();

        return $.ajax({
            url: window.formSAltDraftUrl || '/form_s_alt/draft',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
        });
    }

    function clearAlterLauncherFieldErrors() {
        $('#alter_certificate_no_error, #alter_date_of_issue_error, #alter_valid_from_error, #alter_valid_to_error').text('');
    }

    function clearAlterLauncherPrefill() {
        $('#alter_certificate_no').val('');
        $('#alter_date_of_issue').val('');
        $('#alter_valid_from').val('');
        $('#alter_valid_to').val('');
    }

    function applyAlterCertificateSelection($opt) {
        if (!$opt || !$opt.length || !$opt.val()) {
            clearAlterLauncherPrefill();
            return;
        }
        $('#alter_certificate_no').val(String($opt.data('certificateNo') || '').trim());
        $('#alter_date_of_issue').val(String($opt.data('dateOfIssue') || '').trim());
        $('#alter_valid_from').val(String($opt.data('validFrom') || '').trim());
        $('#alter_valid_to').val(String($opt.data('validTo') || '').trim());
        clearAlterLauncherFieldErrors();
    }

    function populateAlterCertificateDropdown(certificates) {
        var $select = $('#alter_certificate_select');
        if (!$select.length) return;

        $select.empty().append($('<option>', { value: '', text: '— Select certificate —' }));
        (certificates || []).forEach(function (cert) {
            var $opt = $('<option>', {
                value: String(cert.application_id || ''),
                text: String(cert.label || cert.certificate_no || cert.application_id || '')
            });
            $opt.attr('data-certificate-no', cert.certificate_no || '');
            $opt.attr('data-date-of-issue', cert.date_of_issue || '');
            $opt.attr('data-valid-from', cert.valid_from || '');
            $opt.attr('data-valid-to', cert.valid_to || '');
            $select.append($opt);
        });

        if (!(certificates || []).length) {
            $select.append($('<option>', {
                value: '',
                text: 'No issued certificates found',
                disabled: true
            }));
        }
    }

    async function loadAlterCertificates(certCode) {
        var url = window.formSAltCertificatesUrl || '/form_s_alt/certificates';
        try {
            var res = await $.ajax({
                url: url,
                type: 'GET',
                data: { form: certCode || 'S' }
            });
            populateAlterCertificateDropdown((res && res.certificates) ? res.certificates : []);
        } catch (err) {
            populateAlterCertificateDropdown([]);
            $('#alter_certificate_no_error').text('Unable to load certificates. Please try again.');
        }
    }

    function validateAlterLauncherFields() {
        clearAlterLauncherFieldErrors();

        var selected = ($('#alter_certificate_select').val() || '').trim();
        var certificateNo = ($('#alter_certificate_no').val() || '').trim();
        var dateOfIssue = ($('#alter_date_of_issue').val() || '').trim();
        var validFrom = ($('#alter_valid_from').val() || '').trim();
        var validTo = ($('#alter_valid_to').val() || '').trim();
        var hasError = false;

        if (!selected || !certificateNo) {
            $('#alter_certificate_no_error').text('Please select a certificate / licence.');
            hasError = true;
        }

        if (!dateOfIssue) {
            $('#alter_date_of_issue_error').text('Date of issue is required.');
            hasError = true;
        }

        if (!validFrom) {
            $('#alter_valid_from_error').text('Valid from date is required.');
            hasError = true;
        }

        if (!validTo) {
            $('#alter_valid_to_error').text('Valid to date is required.');
            hasError = true;
        }

        if (validFrom && validTo && validTo < validFrom) {
            $('#alter_valid_to_error').text('Valid to must be on or after valid from.');
            hasError = true;
        }

        return {
            hasError: hasError,
            certificateNo: certificateNo,
            dateOfIssue: dateOfIssue,
            validFrom: validFrom,
            validTo: validTo
        };
    }

    function showCertificateNotFoundAndRedirect() {
        Swal.fire({
            icon: 'error',
            title: 'Certificate Details Not Found.',
            confirmButtonText: 'OK',
            allowOutsideClick: false
        }).then(function () {
            window.location.href = window.dashboardUrl || '/dashboard';
        });
    }

    function initLauncherModal() {
        var modalEl = document.getElementById('alteration');
        if (!modalEl || typeof bootstrap === 'undefined') return;

        var alterationModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        var certCode = (window.formSAltCert || 'S').toUpperCase();
        var certLabel = window.formSAltCertLabel || 'Supervisor Competency Certificate [Form S]';

        $('#alter_cert_name').val(certCode);
        $('#alteration_cert_row').addClass('d-none');

        var $subtitle = $('#alteration .modal-subtitle');
        if ($subtitle.length) {
            $subtitle.text(certLabel + ' — select an issued certificate / licence to alter.');
        }

        if (window.formSAltLauncherError) {
            Swal.fire('Verification failed', window.formSAltLauncherError, 'error');
        }

        clearAlterLauncherPrefill();
        $('#alter_certificate_select').val('');
        loadAlterCertificates(certCode);

        $(document).off('change.formSAltLauncher', '#alter_certificate_select')
            .on('change.formSAltLauncher', '#alter_certificate_select', function () {
                applyAlterCertificateSelection($(this).find('option:selected'));
            });

        alterationModal.show();

        $(document).off('click.formSAltLauncher', '#alterationSubmit').on('click.formSAltLauncher', '#alterationSubmit', async function () {
            if (!window.formSAltLauncher) return;

            var fields = validateAlterLauncherFields();
            if (fields.hasError) {
                return;
            }

            if (certCode !== 'S') {
                Swal.fire('Not available', 'Alteration for this certificate type is not available yet.', 'info');
                return;
            }

            var $btn = $(this).prop('disabled', true);
            try {
                var res = await $.ajax({
                    url: window.formSAltVerifyUrl || '/form_s_alt/verify',
                    type: 'POST',
                    data: {
                        certificate_no: fields.certificateNo,
                        date_of_issue: fields.dateOfIssue,
                        valid_from: fields.validFrom,
                        valid_to: fields.validTo,
                        form: certCode,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    }
                });

                if (res && res.status === 'success') {
                    window.location.href = res.redirect_url || ('/form_s_alt?parent=' + encodeURIComponent(res.application_id || '') + '&form=' + encodeURIComponent(certCode));
                } else if (res && res.status === 'certificate_not_found') {
                    showCertificateNotFoundAndRedirect();
                } else {
                    $('#alter_certificate_no_error').text((res && res.message) ? res.message : 'Verification failed.');
                }
            } catch (xhr) {
                var payload = xhr.responseJSON || {};
                if (payload.status === 'certificate_not_found') {
                    showCertificateNotFoundAndRedirect();
                    return;
                }

                var msg = payload.message ? payload.message : 'Verification failed.';
                if (payload.errors) {
                    if (payload.errors.certificate_no) {
                        $('#alter_certificate_no_error').text(payload.errors.certificate_no[0]);
                    }
                    if (payload.errors.date_of_issue) {
                        $('#alter_date_of_issue_error').text(payload.errors.date_of_issue[0]);
                    }
                    if (payload.errors.valid_from) {
                        $('#alter_valid_from_error').text(payload.errors.valid_from[0]);
                    }
                    if (payload.errors.valid_to) {
                        $('#alter_valid_to_error').text(payload.errors.valid_to[0]);
                    }
                } else {
                    $('#alter_certificate_no_error').text(msg);
                }
            } finally {
                $btn.prop('disabled', false);
            }
        });
    }

    function initAlterationEditableMode() {
        if (!window.formSAltEditableMode) {
            lockNonAlterableFields();
            return;
        }

        $('#fsAltOptName, #fsAltOptAddress, #fsAltOptWork').prop('checked', true);
        applyAlterationOptions(null);
        updateChipStyles();

        var root = document.getElementById('fsAltFormRoot');
        if (root) {
            window.setTimeout(function () {
                root.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 150);
        }
    }

    function initAlterationFormPage() {
        if (!$('.fs-alt-form').length) return;

        parentName = ($('#fs_alt_parent_name').val() || '').trim();
        parentAddress = ($('#fs_alt_parent_address').val() || '').trim();

        clearNewWorkRows();
        initProofCompact();
        updateChipStyles();

        $('#fsAltOptName').on('change', function () {
            applyAlterationOptions($(this).is(':checked') ? 'name' : null);
        });
        $('#fsAltOptAddress').on('change', function () {
            applyAlterationOptions($(this).is(':checked') ? 'address' : null);
        });
        $('#fsAltOptWork').on('change', function () {
            applyAlterationOptions($(this).is(':checked') ? 'work' : null);
        });

        initAlterationEditableMode();

        $('#Applicant_Name, #applicants_address').on('input change', syncAlterFlagsFromForm);
        $(document).on('change.formSAltDecl', '#declarationCheckbox', function () {
            if ($(this).is(':checked')) {
                $('#checkboxError').addClass('d-none');
            }
        });
        $(document).on('change.formSAltWork input.formSAltWork', '.js-work-container input, .js-work-container textarea, .js-work-container select', syncAlterFlagsFromForm);
        $(document).on('click.formSAltWork', '#work-exp-add-btn-previous', function () {
            if (!$('#fsAltOptWork').is(':checked')) return false;
        });

        $(document).on('click.formSAltWork', '.work-exp-add-btn.add-more-work', function () {
            if (!$('#fsAltOptWork').is(':checked')) return false;
        });

        $(document).off('click.formSAltCancel', '#fsAltCancelBtn').on('click.formSAltCancel', '#fsAltCancelBtn', async function (e) {
            e.preventDefault();

            var result = await Swal.fire({
                icon: 'question',
                title: 'Cancel alteration?',
                text: 'Any unsaved changes will be lost. Return to dashboard?',
                showCancelButton: true,
                confirmButtonText: 'Yes, leave',
                cancelButtonText: 'Stay on form',
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#035ab3',
                reverseButtons: true
            });

            if (result.isConfirmed) {
                window.location.href = window.dashboardUrl || '/dashboard';
            }
        });

        $(document).off('click.formSAltDraft', '#saveDraftBtn').on('click.formSAltDraft', '#saveDraftBtn', async function (e) {
            if (!$('.fs-alt-form').length) return;

            e.preventDefault();
            e.stopImmediatePropagation();

            var $btn = $(this).prop('disabled', true);
            try {
                var res = await saveAlterationDraftRequest();
                if (res && res.status === 'success') {
                    if (res.application_id) {
                        $('#application_id').val(res.application_id);
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Draft Saved',
                        text: res.message || 'Your alteration draft has been saved.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    Swal.fire('Error', (res && res.message) ? res.message : 'Unable to save draft.', 'error');
                }
            } catch (xhr) {
                Swal.fire('Error', window.getAjaxErrorMessage ? window.getAjaxErrorMessage(xhr) : 'Unable to save draft.', 'error');
            } finally {
                $btn.prop('disabled', false);
            }
        });

        $(document).off('click.formSAltSubmit', '#submitPaymentBtn').on('click.formSAltSubmit', '#submitPaymentBtn', async function (e) {
            if (!$(this).closest('.fs-alt-form').length) return;

            e.preventDefault();
            e.stopImmediatePropagation();

            if (!validateAlterationBeforePreview()) return;

            if (typeof window.showCompetencyPreviewModal === 'function') {
                var confirmed = await window.showCompetencyPreviewModal();
                if (!confirmed) return;
            }

            var $btn = $(this).prop('disabled', true);
            try {
                var res = await submitAlterationRequest();
                if (res && res.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Alteration Submitted',
                        text: res.message || 'Your alteration request has been submitted.',
                        confirmButtonText: 'OK'
                    }).then(function () {
                        window.location.href = window.dashboardUrl || '/dashboard';
                    });
                } else {
                    Swal.fire('Error', (res && res.message) ? res.message : 'Submission failed.', 'error');
                }
            } catch (xhr) {
                Swal.fire('Error', window.getAjaxErrorMessage ? window.getAjaxErrorMessage(xhr) : 'Submission failed.', 'error');
            } finally {
                $btn.prop('disabled', false);
            }
        });
    }

    $(document).ready(function () {
        if (window.formSAltLauncher) {
            initLauncherModal();
            return;
        }
        initAlterationFormPage();
    });
})(jQuery);
