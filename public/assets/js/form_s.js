(function ($) {
    'use strict';

    var cfg = window.FORM_S_CONFIG || {};
    var verifyLicenseUrl = cfg.verifyLicenseUrl || '/verifylicense';
    var formInstructionUrl = cfg.formInstructionUrl || '/licences/getFormInstruction';

    $(document).on('click', '.form-s-file-upload-btn:not(.form-s-file-upload-btn--table)', function (e) {
        e.preventDefault();
        var $file = $(this).closest('.form-s-file-upload-wrap').find('input[type="file"]').first();
        if ($file.length) {
            $file.trigger('click');
        }
    });

    function clearLocalPreview($fileInput) {
        var $wrap = $fileInput.closest('.form-s-file-upload-wrap');
        var $preview = $wrap.next('.local-file-preview');
        var oldUrl = $preview.data('blobUrl');
        if (oldUrl) {
            URL.revokeObjectURL(oldUrl);
        }
        $preview.remove();
        $fileInput.removeAttr('data-has-local-file');
    }

    function clearWorkRowUploadErrors($scope) {
        if (!$scope || !$scope.length) {
            return;
        }
        $scope.find('.error-message').each(function () {
            var txt = ($(this).text() || '').toLowerCase();
            if (
                txt.indexOf('supporting document is required') !== -1 ||
                txt.indexOf('relieving letter is required') !== -1 ||
                txt.indexOf('highest transformer capacity') !== -1 ||
                txt.indexOf('only pdf') !== -1 ||
                txt.indexOf('file size permitted') !== -1
            ) {
                $(this).remove();
            }
        });
    }

    $(document).on('change', 'input[type="file"][name="education_document[]"], input[type="file"][name="work_document[]"], input[type="file"][name="work_relieving_letter[]"]', function () {
        var $input = $(this);
        clearLocalPreview($input);
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            return;
        }
        var allowed = ['application/pdf', 'image/jpeg', 'image/png'];
        var maxSize = 200 * 1024;
        if (allowed.indexOf(file.type) === -1) {
            window.alert('Only PDF, JPG, PNG files are allowed.');
            this.value = '';
            $input.removeAttr('data-has-local-file');
            return;
        }
        if (file.size > maxSize) {
            window.alert('File size should not exceed 200 KB.');
            this.value = '';
            $input.removeAttr('data-has-local-file');
            return;
        }
        $input.attr('data-has-local-file', '1');
        var blobUrl = URL.createObjectURL(file);
        var isImage = file.type.indexOf('image/') === 0;
        var $preview = $('<div class="local-file-preview"></div>').data('blobUrl', blobUrl);
        if (isImage) {
            $preview.append($('<img>', { src: blobUrl, class: 'img-preview', alt: 'Selected image preview' }));
        }
        $preview.append($('<a>', { href: blobUrl, target: '_blank', rel: 'noopener noreferrer', class: 'preview-link' })
            .html(isImage ? '<i class="fa fa-image"></i> Preview image' : '<i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View Document'));
        $input.closest('.form-s-file-upload-wrap').after($preview);
        clearWorkRowUploadErrors($input.closest('.work-fields'));
    });

    $(document).on('change', '#aadhaar_doc, #pancard_doc', function () {
        var $input = $(this);
        clearLocalPreview($input);
        var file = this.files && this.files[0] ? this.files[0] : null;
        if (!file) {
            return;
        }
        var minSize = 10 * 1024;
        var maxSize = 250 * 1024;
        if (file.type !== 'application/pdf') {
            window.alert('Only PDF files are allowed.');
            this.value = '';
            return;
        }
        if (file.size < minSize) {
            window.alert('File size must be at least 10 KB.');
            this.value = '';
            return;
        }
        if (file.size > maxSize) {
            window.alert('File size should not exceed 250 KB.');
            this.value = '';
            return;
        }
        var blobUrl = URL.createObjectURL(file);
        var $preview = $('<div class="local-file-preview"></div>').data('blobUrl', blobUrl);
        $preview.append($('<a>', { href: blobUrl, target: '_blank', rel: 'noopener noreferrer', class: 'preview-link' })
            .html('<i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View Document'));
        $input.closest('.form-s-file-upload-wrap').after($preview);
    });

    function bindImageUploadPreview(inputId, previewId, nameId, placeholderId) {
        var inputEl = document.getElementById(inputId);
        var previewEl = document.getElementById(previewId);
        var nameEl = document.getElementById(nameId);
        var placeholderEl = document.getElementById(placeholderId);
        if (!inputEl || !previewEl || !nameEl || !placeholderEl) {
            return;
        }

        inputEl.addEventListener('change', function () {
            var file = this.files && this.files[0] ? this.files[0] : null;
            if (!file) {
                previewEl.removeAttribute('src');
                previewEl.style.display = 'none';
                placeholderEl.style.display = 'block';
                nameEl.textContent = 'No file selected';
                return;
            }
            nameEl.textContent = file.name;
            var blobUrl = URL.createObjectURL(file);
            previewEl.onload = function () {
                URL.revokeObjectURL(blobUrl);
            };
            previewEl.src = blobUrl;
            previewEl.style.display = 'block';
            placeholderEl.style.display = 'none';
        });
    }

    bindImageUploadPreview('upload_photo', 'photo_preview', 'upload_photo_name', 'photo_placeholder');
    bindImageUploadPreview('upload_sign', 'sign_preview', 'upload_sign_name', 'sign_placeholder');

    document.addEventListener('click', function (e) {
        var container = document.getElementById('education-container');
        if (!container) {
            return;
        }

        var educationRows = container.querySelectorAll('.education-fields');
        var refreshEducationSerials = function () {
            container.querySelectorAll('.education-fields .edu-serial').forEach(function (cell, idx) {
                cell.textContent = String(idx + 1);
            });
        };

        if (e.target.closest('.add-more')) {
            if (educationRows.length >= 5) {
                $('#education-table').next('.education-error').remove();
                $('<div class="text-danger mt-2 education-error">You can add a maximum of 5 education entries.</div>').insertAfter('#education-table');
                setTimeout(function () { $('.education-error').fadeOut(); }, 7000);
                return;
            }
            var newRow = document.createElement('tr');
            newRow.classList.add('education-fields');
            newRow.innerHTML =
                '<td class="edu-serial text-center">' + (educationRows.length + 1) + '</td>' +
                '<td><select class="form-control" name="educational_level[]" required>' +
                '<option selected disabled>Select Education</option>' +
                '<option value="DEE">Diploma(Electrical Engineering)</option>' +
                '<option value="BEE">B.E(Electrical Engineering)</option>' +
                '<option value="MEE">M.E(Electrical Engineering)</option>' +
                '<option value="AMIE">A pass in AMIE</option>' +
                '</select></td>' +
                '<td><input type="text" class="form-control" name="institute_name[]" maxlength="80" required></td>' +
                '<td><select name="month_of_passing[]" class="form-control" required>' +
                '<option value="">Select Month</option>' +
                '<option value="01">Jan</option><option value="02">Feb</option><option value="03">Mar</option>' +
                '<option value="04">Apr</option><option value="05">May</option><option value="06">Jun</option>' +
                '<option value="07">Jul</option><option value="08">Aug</option><option value="09">Sep</option>' +
                '<option value="10">Oct</option><option value="11">Nov</option><option value="12">Dec</option>' +
                '</select></td>' +
                '<td><select name="year_of_passing[]" class="form-control" required>' +
                '<option value="0">Select Year</option>' +
                Array.from({ length: new Date().getFullYear() - 1979 }, function (_, i) {
                    var year = new Date().getFullYear() - i;
                    return '<option value="' + year + '">' + year + '</option>';
                }).join('') +
                '</select></td>' +
                '<td><input type="text" class="form-control certificate-input" name="certificate_no[]" maxlength="20" required>' +
                '<span class="error text-danger certificate-error" style="font-size:.75rem;"></span></td>' +
                '<td><div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="education">' +
                '<input type="file" class="form-control" name="education_document[]" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png"></div></td>' +
                '<td class="form-s-actions-cell text-center p-1"><div class="form-s-actions-stack">' +
                '<button type="button" class="btn-tbl-remove remove-education py-1 px-2" title="Remove row"><i class="fa fa-trash-o"></i></button>' +
                '</div></td>';
            container.appendChild(newRow);
            refreshEducationSerials();
        }

        if (e.target.closest('.remove-education')) {
            if (educationRows.length <= 1) {
                $('#education-table').next('.education-error').remove();
                $('<div class="text-danger mt-2 education-error">You must have at least one education entry.</div>').insertAfter('#education-table');
                setTimeout(function () { $('.education-error').fadeOut(); }, 7000);
                return;
            }
            e.target.closest('tr').remove();
            refreshEducationSerials();
        }
    });

    $('#verify_form_s').on('click', function () {
        var licenseError = document.getElementById('licenseError');
        var licenseNumber = $('#certificate_no').val().trim().toUpperCase();
        var date = $('#certificate_valid_to').val().trim();
        var regex = /^(B|C|LC|LB)\d+$/;
        if (licenseError) {
            licenseError.textContent = '';
        }
        $('#dateError').text('');
        var isValid = true;
        if (licenseNumber === '' || !regex.test(licenseNumber)) {
            if (licenseError) {
                licenseError.textContent = 'Enter a valid Certificate Number';
            }
            isValid = false;
        }
        if (date === '') {
            $('#dateError').text('Date is required');
            isValid = false;
        } else {
            var regexDate = /^(\d{4})-(\d{2})-(\d{2})$/;
            var parts = date.match(regexDate);
            if (!parts) {
                $('#dateError').text('Enter a valid date');
                isValid = false;
            } else {
                var year = parseInt(parts[1], 10);
                var month = parseInt(parts[2], 10) - 1;
                var day = parseInt(parts[3], 10);
                var checkDate = new Date(year, month, day);
                if (checkDate.getFullYear() !== year || checkDate.getMonth() !== month || checkDate.getDate() !== day || year < 1800) {
                    $('#dateError').text('Enter a valid date');
                    isValid = false;
                }
            }
        }
        if (!isValid) {
            return;
        }
        $.ajax({
            url: verifyLicenseUrl,
            method: 'POST',
            data: {
                license_number: licenseNumber,
                date: date,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {
                var $msgBox = $('#license_message');
                if (response.exists) {
                    $msgBox.removeClass('text-danger').addClass('text-success').html('&#10004; License verified.');
                } else {
                    $msgBox.removeClass('text-success').addClass('text-danger').html('&#10060; License not found.');
                }
            },
            error: function () {
                $('#license_message').removeClass('text-success').addClass('text-danger').html('Error verifying license. Try again.');
            }
        });
    });

    $(document).ready(async function () {
        var modalEl = document.getElementById('competencyInstructionsModal');
        if (!modalEl || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
            return;
        }
        var agreeCheckbox = modalEl.querySelector('#declaration-agree-renew');
        var errorText = modalEl.querySelector('#declaration-error-renew');
        var proceedBtn = modalEl.querySelector('#proceedPayment');
        if (!agreeCheckbox || !errorText || !proceedBtn) {
            return;
        }
        var acceptModal = new bootstrap.Modal(modalEl, { backdrop: 'static', keyboard: false });
        var modalBody = modalEl.querySelector('#instructionContent');
        if (modalBody) {
            modalBody.innerHTML = '<p class="mb-0 text-muted">Loading instructions...</p>';
        }
        try {
            var instructionResponse = await $.ajax({
                url: formInstructionUrl,
                type: 'POST',
                data: {
                    appl_type: ($('#appl_type').val() || 'N'),
                    licence_code: ($('#license_name').val() || 'C'),
                    _token: $('meta[name="csrf-token"]').attr('content')
                }
            });
            if (modalBody) {
                if (instructionResponse && Number(instructionResponse.status) === 200 && instructionResponse.data) {
                    try {
                        var delta = JSON.parse(instructionResponse.data);
                        if (typeof QuillDeltaToHtmlConverter !== 'undefined' && delta && delta.ops) {
                            var converter = new QuillDeltaToHtmlConverter(delta.ops, {
                                inlineStyles: true,
                                multiLineParagraph: false,
                                listItemTag: 'li',
                                paragraphTag: 'p'
                            });
                            var html = converter.convert();
                            html = html.replace(/@(\s*)(\(|\uFF08)/g, '$1$2');
                            html = html.replace(/<(li|p)([^>]*)>@(\s*)(\(|\uFF08)/gi, '<$1$2>$3$4');
                            modalBody.innerHTML = html;
                        } else {
                            modalBody.textContent = instructionResponse.data;
                        }
                    } catch (parseErr) {
                        modalBody.textContent = instructionResponse.data;
                    }
                } else {
                    modalBody.innerHTML = '<p class="mb-0 text-danger">Instruction not available.</p>';
                }
            }
        } catch (err) {
            if (modalBody) {
                modalBody.innerHTML = '<p class="mb-0 text-danger">Unable to load instructions right now.</p>';
            }
        }
        agreeCheckbox.checked = false;
        errorText.classList.add('d-none');
        acceptModal.show();
        if (!modalEl.dataset.acceptGateBound) {
            modalEl.dataset.acceptGateBound = '1';
            modalEl.addEventListener('hide.bs.modal', function (e) {
                if (!agreeCheckbox.checked) {
                    e.preventDefault();
                    errorText.classList.remove('d-none');
                }
            });
            proceedBtn.addEventListener('click', function (e) {
                if (!agreeCheckbox.checked) {
                    e.preventDefault();
                    errorText.classList.remove('d-none');
                    return;
                }
                errorText.classList.add('d-none');
                acceptModal.hide();
            });
            agreeCheckbox.addEventListener('change', function () {
                if (agreeCheckbox.checked) {
                    errorText.classList.add('d-none');
                }
            });
        }
    });
})(jQuery);

// Preview modal
var EDU_LEVEL_MAP = {
    DEE: 'Diploma(Electrical Engineering)',
    BEE: 'B.E(Electrical Engineering)',
    MEE: 'M.E(Electrical Engineering)',
    AMIE: 'A pass in AMIE'
};
var MONTH_MAP = {
    '01': 'Jan', '02': 'Feb', '03': 'Mar', '04': 'Apr', '05': 'May', '06': 'Jun',
    '07': 'Jul', '08': 'Aug', '09': 'Sep', '10': 'Oct', '11': 'Nov', '12': 'Dec'
};
var EMP_LABEL_MAP = {
    private_organisation: 'Private organization',
    electrical_contractor: 'Electrical Contractor',
    retired_employee: 'Retired Employee',
    govt_organisation: 'Government Organization',
    apprenticeship: 'Apprenticeship',
    board_member_tnelb: 'Board member of TNELB or Ex board member of TNELB'
};
var WORK_NATURE_MAP = {
    erection: 'Erection',
    maintenance: 'Maintenance',
    erection_maintenance: 'Erection & Maintenance'
};
var VOLTAGE_LEVEL_MAP = {
    up_to_650v: 'Up to 650V',
    '650v_to_33kv': 'Above 650V to 33KV',
    above_33kv: 'Above 33KV'
};

function fmtDate(val) {
    if (!val) {
        return '—';
    }
    var p = val.split('-');
    return p.length === 3 ? p[2] + '-' + p[1] + '-' + p[0] : val;
}

function setVal(id, v) {
    var el = document.getElementById(id);
    if (!el) {
        return;
    }
    var txt = (v || '').toString().trim();
    el.textContent = txt || '—';
    el.classList.toggle('prv-empty', !txt);
}

function fileLabel(input) {
    return input && input.files && input.files[0] ? input.files[0].name : '—';
}

function fillWorkPreviewTable(bodyId, containerSelector) {
    var workBody = document.getElementById(bodyId);
    if (!workBody) return;
    workBody.innerHTML = '';
    var workRows = document.querySelectorAll(containerSelector + ' .work-fields');
    if (!workRows.length) {
        workBody.innerHTML = '<tr><td colspan="15" class="text-center text-muted py-3">No work entries</td></tr>';
        return;
    }
    var fileLink = function (doc) {
        return (doc && doc.files && doc.files[0])
            ? '<a href="' + URL.createObjectURL(doc.files[0]) + '" target="_blank" style="color:#035ab3;font-size:.75rem;"><i class="fa fa-file-pdf-o"></i> View</a>'
            : '<span class="text-muted">—</span>';
    };
    var val = function (el) { return el ? ((el.value || '').trim() || '—') : '—'; };
    workRows.forEach(function (row, i) {
        var empType = row.querySelector('.work-employment-type');
        var cat = row.querySelector('.work-contractor-cat');
        var lic = row.querySelector('.work-licence-number');
        var employer = row.querySelector('.work-employer-input');
        var address = row.querySelector('.work-org-address');
        var desig = row.querySelector('[name="designation[]"]');
        var nature = row.querySelector('.work-nature');
        var voltage = row.querySelector('.work-voltage');
        var kva = row.querySelector('.work-transformer-kva');
        var fromInp = row.querySelector('.work-date-from');
        var toInp = row.querySelector('.work-date-to');
        var tillChk = row.querySelector('.work-date-till');
        var yPart = row.querySelector('.work-duration-y');
        var mPart = row.querySelector('.work-duration-m');
        var dPart = row.querySelector('.work-duration-d');
        var doc = row.querySelector('[name="work_document[]"]');
        var rel = row.querySelector('[name="work_relieving_letter[]"]');

        var yv = yPart ? (yPart.value || '').trim() : '';
        var mv = mPart ? (mPart.value || '').trim() : '';
        var dv = dPart ? (dPart.value || '').trim() : '';
        var totalTxt = (yv === '' && mv === '' && dv === '') ? '—' : (yv + 'y ' + mv + 'm ' + dv + 'd');

        var empTxt = empType ? (EMP_LABEL_MAP[empType.value] || empType.value || '—') : '—';
        var isBoardMember = empType && empType.value === 'board_member_tnelb';
        var natureTxt = isBoardMember ? 'N/A' : (nature ? (WORK_NATURE_MAP[nature.value] || nature.value || '—') : '—');
        var voltTxt = isBoardMember ? 'N/A' : (voltage ? (VOLTAGE_LEVEL_MAP[voltage.value] || voltage.value || '—') : '—');
        var fromDate = fromInp ? fmtDate(fromInp.getAttribute('data-raw') || fromInp.value) : '—';
        var toDate = (tillChk && tillChk.checked)
            ? '<span class="prv-badge-yes">Till date</span>'
            : (toInp ? fmtDate(toInp.getAttribute('data-raw') || toInp.value) : '—');

        workBody.innerHTML +=
            '<tr><td class="text-center">' + (i + 1) + '</td>' +
            '<td>' + empTxt + '</td>' +
            '<td class="text-center">' + (isBoardMember ? 'N/A' : val(cat)) + '</td>' +
            '<td>' + (isBoardMember ? 'N/A' : val(lic)) + '</td>' +
            '<td>' + val(employer) + '</td>' +
            '<td>' + val(address) + '</td>' +
            '<td>' + val(desig) + '</td>' +
            '<td>' + natureTxt + '</td>' +
            '<td>' + voltTxt + '</td>' +
            '<td class="text-center">' + (isBoardMember ? 'N/A' : val(kva)) + '</td>' +
            '<td class="text-center">' + fromDate + '</td>' +
            '<td class="text-center">' + toDate + '</td>' +
            '<td class="text-center">' + totalTxt + '</td>' +
            '<td class="text-center">' + fileLink(doc) + '</td>' +
            '<td class="text-center">' + (tillChk && tillChk.checked ? '<span class="text-muted">N/A</span>' : (isBoardMember ? '<span class="text-muted">N/A</span>' : fileLink(rel))) + '</td>' +
            '</tr>';
        if (isBoardMember) {
            var meetingDetails = row.querySelector('.work-board-meeting-details');
            var meetingDateInp = row.querySelector('.work-board-meeting-date');
            var detailsTxt = meetingDetails ? (meetingDetails.value || '').trim() : '';
            var meetingDateTxt = meetingDateInp ? fmtDate(meetingDateInp.getAttribute('data-raw') || meetingDateInp.value) : '—';
            workBody.innerHTML +=
                '<tr><td></td><td colspan="14" style="font-size:.78rem;background:#f4f8fd;">' +
                '<strong>Board meeting:</strong> ' + (detailsTxt || '—') +
                ' &nbsp;|&nbsp; <strong>Date:</strong> ' + meetingDateTxt +
                '</td></tr>';
        }
    });
}

function populatePreview() {
    setVal('prv_name', document.getElementById('Applicant_Name').value);
    setVal('prv_fathers_name', document.getElementById('Fathers_Name').value);
    var emailEl = document.getElementById('applicant_email');
    setVal('prv_email', emailEl ? emailEl.value : '');
    setVal('prv_address', document.getElementById('applicants_address').value);
    setVal('prv_dob', document.getElementById('d_o_b').value);
    setVal('prv_age', document.getElementById('age').value);

    var eduBody = document.getElementById('prv_edu_body');
    eduBody.innerHTML = '';
    var eduRows = document.querySelectorAll('#education-container .education-fields');
    if (!eduRows.length) {
        eduBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No education entries</td></tr>';
    } else {
        eduRows.forEach(function (row, i) {
            var level = row.querySelector('[name="educational_level[]"]');
            var inst = row.querySelector('[name="institute_name[]"]');
            var mon = row.querySelector('[name="month_of_passing[]"]');
            var yr = row.querySelector('[name="year_of_passing[]"]');
            var cert = row.querySelector('[name="certificate_no[]"]');
            var doc = row.querySelector('[name="education_document[]"]');
            var lvlTxt = level ? (EDU_LEVEL_MAP[level.value] || level.value || '—') : '—';
            var monTxt = mon ? (MONTH_MAP[mon.value] || mon.value || '—') : '—';
            var yrTxt = yr ? (yr.value === '0' || !yr.value ? '—' : yr.value) : '—';
            var docLink = (doc && doc.files && doc.files[0])
                ? '<a href="' + URL.createObjectURL(doc.files[0]) + '" target="_blank" style="color:#035ab3;font-size:.75rem;"><i class="fa fa-file-pdf-o"></i> View</a>'
                : '<span class="text-muted">—</span>';
            eduBody.innerHTML +=
                '<tr><td class="text-center">' + (i + 1) + '</td><td>' + lvlTxt + '</td>' +
                '<td>' + (inst ? inst.value || '—' : '—') + '</td>' +
                '<td class="text-center">' + monTxt + '</td><td class="text-center">' + yrTxt + '</td>' +
                '<td>' + (cert ? cert.value || '—' : '—') + '</td>' +
                '<td class="text-center">' + docLink + '</td></tr>';
        });
    }

    if (document.getElementById('prv_work_body_previous')) {
        fillWorkPreviewTable('prv_work_body_previous', '#work-container-previous');
        fillWorkPreviewTable('prv_work_body_current', '#work-container-current');
    } else {
        fillWorkPreviewTable('prv_work_body', '#work-container');
    }

    var prevLicYes = document.getElementById('previous_license_yes');
    var isYes7 = prevLicYes && prevLicYes.checked;
    var yn7 = document.getElementById('prv_prev_license_yn');
    if (yn7) {
        yn7.innerHTML = isYes7 ? '<span class="prv-badge-yes">Yes</span>' : '<span class="prv-badge-no">No</span>';
    }
    var pb = document.getElementById('prv_prev_details_block');
    if (pb) {
        pb.style.display = isYes7 ? '' : 'none';
    }
    if (isYes7) {
        setVal('prv_prev_cert_no', document.getElementById('previously_number') ? document.getElementById('previously_number').value : '');
        var issEl = document.getElementById('previously_issue_date');
        setVal('prv_prev_issue_date', issEl ? fmtDate(issEl.value) : '');
        var fromEl = document.getElementById('previously_valid_from');
        setVal('prv_prev_from_date', fromEl ? fmtDate(fromEl.value) : '');
        var toEl = document.getElementById('previously_valid_to');
        setVal('prv_prev_to_date', toEl ? fmtDate(toEl.value) : '');
    }

    var wireYes = document.getElementById('yesOption');
    var isYes8 = wireYes && wireYes.checked;
    var yn8 = document.getElementById('prv_wireman_yn');
    if (yn8) {
        yn8.innerHTML = isYes8 ? '<span class="prv-badge-yes">Yes</span>' : '<span class="prv-badge-no">No</span>';
    }
    var wb = document.getElementById('prv_wireman_details_block');
    if (wb) {
        wb.style.display = isYes8 ? '' : 'none';
    }
    if (isYes8) {
        setVal('prv_wireman_cert_no', document.getElementById('certificate_no') ? document.getElementById('certificate_no').value : '');
        var wIssEl = document.getElementById('certificate_issue_date');
        setVal('prv_wireman_issue_date', wIssEl ? fmtDate(wIssEl.value) : '');
        var wFromEl = document.getElementById('certificate_valid_from');
        setVal('prv_wireman_from_date', wFromEl ? fmtDate(wFromEl.value) : '');
        var wToEl = document.getElementById('certificate_valid_to');
        setVal('prv_wireman_to_date', wToEl ? fmtDate(wToEl.value) : '');
    }

    var photoWrap = document.getElementById('prv_photo_wrap');
    var photoSrc = document.getElementById('photo_preview');
    if (photoWrap) {
        var src = photoSrc && photoSrc.style.display !== 'none' ? photoSrc.src : '';
        photoWrap.innerHTML = src
            ? '<img src="' + src + '" alt="Photo" style="width:80px;height:96px;object-fit:cover;border:2px solid #dde5f3;border-radius:6px;">'
            : '<div class="prv-no-img">No Photo</div>';
    }

    var signWrap = document.getElementById('prv_sign_wrap');
    var signSrc = document.getElementById('sign_preview');
    if (signWrap) {
        var ssrc = signSrc && signSrc.style.display !== 'none' ? signSrc.src : '';
        signWrap.innerHTML = ssrc
            ? '<img src="' + ssrc + '" alt="Signature" style="width:140px;height:50px;object-fit:contain;border:2px solid #dde5f3;border-radius:6px;">'
            : '<div class="prv-no-img" style="width:140px;height:50px;">No Signature</div>';
    }

    setVal('prv_aadhaar', document.getElementById('aadhaar') ? document.getElementById('aadhaar').value : '');
    setVal('prv_pan', document.getElementById('pancard') ? document.getElementById('pancard').value : '');
    var aDoc = document.getElementById('aadhaar_doc');
    setVal('prv_aadhaar_doc', fileLabel(aDoc));
    var pDoc = document.getElementById('pancard_doc');
    setVal('prv_pan_doc', fileLabel(pDoc));
}

function openPreviewModal() {
    populatePreview();
    var modal = document.getElementById('appPreviewModal');
    modal.style.display = 'flex';
    modal.classList.add('prv-open');
    document.body.style.overflow = 'hidden';
    document.getElementById('prvConfirmCheck').checked = false;
    document.getElementById('prvConfirmBtn').disabled = true;
    document.getElementById('prvBody').scrollTop = 0;
}

function closePreviewModal() {
    var modal = document.getElementById('appPreviewModal');
    modal.style.display = 'none';
    modal.classList.remove('prv-open');
    document.body.style.overflow = '';
    if (typeof window.normalizeCompetencyDynamicSections === 'function') {
        window.normalizeCompetencyDynamicSections();
    }
}

document.addEventListener('DOMContentLoaded', function () {
    var prvConfirmCheck = document.getElementById('prvConfirmCheck');
    var prvConfirmBtn = document.getElementById('prvConfirmBtn');
    var prvPrintBtn = document.getElementById('prvPrintBtn');
    var appPreviewModal = document.getElementById('appPreviewModal');

    if (prvConfirmCheck && prvConfirmBtn) {
        prvConfirmCheck.addEventListener('change', function () {
            prvConfirmBtn.disabled = !this.checked;
        });

        prvConfirmBtn.addEventListener('click', function () {
            closePreviewModal();
            if (typeof window._prvResolve === 'function') {
                window._prvResolve(true);
                window._prvResolve = null;
            }
        });
    }

    if (prvPrintBtn) {
        prvPrintBtn.addEventListener('click', function () {
            window.print();
        });
    }

    if (appPreviewModal) {
        appPreviewModal.addEventListener('click', function (e) {
            if (e.target === this) {
                closePreviewModal();
                if (typeof window._prvResolve === 'function') {
                    window._prvResolve(false);
                    window._prvResolve = null;
                }
            }
        });
    }
});
