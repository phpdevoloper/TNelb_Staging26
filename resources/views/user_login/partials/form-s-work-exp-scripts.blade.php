@if ((($editFormName ?? '') === 'W') || (($editFormName ?? ($application_details->form_name ?? '')) === 'S'))
@php
    $showBoardMemberEmploymentType = $showBoardMemberEmploymentType ?? false;
    $hideUploadWhenDocExists = !empty($hideUploadWhenDocExists);
    $isAlterationMode = !empty($isAlterationMode);
    // Form W serial-7 has no Voltage Level / Nature of Work / Transformer (kVA) fields.
    $hideVoltageFields = !empty($hideVoltageFields);
@endphp
<script>
        (function() {
            var CONTRACTOR_TYPE = 'electrical_contractor';
            var BOARD_MEMBER_TYPE = 'board_member_tnelb';
            var VOLTAGE_DISABLES_KVA = 'up_to_650v';
            var MAX_WORK_ROWS = 3;
            var TWO_YEARS_MS = 730 * 86400000;
            var hideUploadWhenDocExists = @json($hideUploadWhenDocExists);
            var isAlterationMode = @json($isAlterationMode);
            /* Form W: Voltage / Nature of Work / Transformer (kVA) fields are absent. */
            var WX_HIDE_VOLTAGE_FIELDS = @json($hideVoltageFields);
            var DOCUMENT_PUBLIC_URL_PREFIX = @json(trim((string) config('document_versioning.public_url_prefix', 'competency'), '/'));
            var DOCUMENT_PUBLIC_BASE_URL = @json(rtrim(trim((string) (config('document_versioning.public_base_url') ?: '')), '/'));
            /* 7b Representing Organisation options are filled per selected meeting date. */
            var REPRESENTING_ORG_OPTIONS = [];
            window.wx7bRepresentingOrgOptions = REPRESENTING_ORG_OPTIONS;

            /**
             * jQuery attribute selectors break on name="foo[]" because ] ends the selector.
             * Match by the DOM name property instead.
             */
            function namedInputs($scope, name) {
                var $root = $scope && $scope.length ? $scope : $(document);
                return $root.find('input').filter(function () {
                    return this.name === name;
                });
            }

            function namedInputVal($scope, name) {
                return (namedInputs($scope, name).first().val() || '').trim();
            }

            /**
             * Ensure 7b "Representing Organisation" is a <select> everywhere
             * (covers legacy markup / cloned text inputs in #work-container-current).
             */
            function ensure7bRepresentingOrgSelect($scope) {
                var $roots = $scope && $scope.length
                    ? $scope
                    : $('#work-container-current .work-fields, .js-work-container[data-work-part="current"] .work-fields');
                $roots.each(function () {
                    var $tr = $(this);
                    var $field = $tr.find('[data-field="organisation"]').first();
                    var $emp = $tr.find('.work-employer-input').first();
                    if (!$emp.length) return;

                    $field.find('.work-card-field-label').each(function () {
                        var $lab = $(this);
                        var t = ($lab.html() || '').replace(/\s+/g, ' ');
                        if (/Representing Organisation/i.test(t) || /Organisation/i.test(t)) {
                            $lab.html('Representing Organisation<span class="req">*</span>');
                        }
                    });

                    if ($emp.is('select')) return;

                    var current = ($emp.val() || '').trim();
                    var wasDisabled = !!$emp.prop('disabled');
                    var wasRequired = !!$emp.prop('required');
                    var $sel = $('<select class="form-control work-employer-input" name="work_employer_name[]" autocomplete="off" aria-label="Representing Organisation"></select>');
                    $sel.append($('<option></option>').attr('value', '').text('Select date first'));
                    if (current) {
                        $sel.append($('<option></option>').attr('value', current).text(current).prop('selected', true));
                    }
                    if (wasRequired) $sel.prop('required', true);
                    $sel.prop('disabled', true);
                    if (wasDisabled) $sel.prop('disabled', true);
                    $emp.replaceWith($sel);
                });
            }

            /* ── 7b: date picker → meeting-number dropdown from cc_board_details ── */
            function get7bBoardMasterRows() {
                var el = document.getElementById('fs-7b-board-master-json');
                if (!el) return [];
                try {
                    var rows = JSON.parse(el.textContent || '[]');
                    return Array.isArray(rows) ? rows : [];
                } catch (e) {
                    return [];
                }
            }

            function format7bBmNo(no) {
                if (no === null || no === undefined || no === '') return '';
                var s = String(no).trim();
                if (/^\d+\.0+$/.test(s)) s = s.replace(/\.0+$/, '');
                return s;
            }

            function clear7bMeetingDateValidation($row) {
                if (!$row || !$row.length) return;
                $row.find('.work-board-meeting-date-error').remove();
                $row.find('.work-card-field[data-field="board-meeting-date"] .error-message').remove();
            }

            function show7bNoMeetingValidation($row) {
                clear7bMeetingDateValidation($row);
                var $dateField = $row.find('.work-card-field[data-field="board-meeting-date"]').first();
                var $date = $row.find('.work-board-meeting-date').first();
                var $target = $dateField.length ? $dateField : $date;
                if (!$target.length) return;
                $target.append(
                    '<span class="error-message text-danger d-block mt-1 work-board-meeting-date-error" role="alert">No meeting for this date</span>'
                );
            }

            function fill7bOrgOptionsForDate($row, dateIso, preferredOrg, hasMeetings) {
                var $org = $row.find('.work-employer-input').first();
                if (!$org.length || !$org.is('select')) return;
                if ($org.prop('disabled') && $org.closest('.fs-alt-existing-work').length) {
                    return;
                }

                dateIso = String(dateIso || '').trim();
                preferredOrg = String(preferredOrg || '').trim();
                var wasAlterationLocked = $org.closest('.fs-alt-existing-work').length > 0;

                $org.empty();
                if (!dateIso || !hasMeetings) {
                    $org.append($('<option></option>').attr('value', '').text('Select date first'));
                    if (!wasAlterationLocked) {
                        $org.prop('disabled', true).val('');
                    }
                    $row.find('.work-level-sync').val('');
                    return;
                }

                var orgs = [];
                get7bBoardMasterRows().forEach(function (r) {
                    if (String((r && r.bm_date) || '') !== dateIso) return;
                    var org = String((r && r.bm_member) || '').trim();
                    if (org && orgs.indexOf(org) === -1) orgs.push(org);
                });
                orgs.sort(function (a, b) { return a.localeCompare(b); });

                $org.append($('<option></option>').attr('value', '').text('Select organisation'));
                orgs.forEach(function (org) {
                    $org.append($('<option></option>').attr('value', org).text(org));
                });
                if (preferredOrg && orgs.indexOf(preferredOrg) === -1) {
                    $org.append($('<option></option>').attr('value', preferredOrg).text(preferredOrg));
                }

                if (!wasAlterationLocked) {
                    $org.prop('disabled', false);
                }
                $org.val(preferredOrg || '');
                $row.find('.work-level-sync').val(String($org.val() || '').trim());
            }

            function fill7bMeetingDetailsForDate($row, dateIso, preferredNo, preferredOrg) {
                var $details = $row.find('.work-board-meeting-details').first();
                if (!$details.length) return '';

                dateIso = String(dateIso || '').trim();
                preferredNo = format7bBmNo(preferredNo);
                preferredOrg = String(preferredOrg || '').trim();
                clear7bMeetingDateValidation($row);

                $details.empty();
                if (!dateIso) {
                    $details.append($('<option></option>').attr('value', '').text('Select date first'));
                    $details.prop('disabled', true).val('');
                    fill7bOrgOptionsForDate($row, '', '', false);
                    return '';
                }

                var nos = [];
                get7bBoardMasterRows().forEach(function (r) {
                    if (String((r && r.bm_date) || '') !== dateIso) return;
                    var n = format7bBmNo(r.bm_no);
                    if (n && nos.indexOf(n) === -1) nos.push(n);
                });
                nos.sort(function (a, b) { return Number(a) - Number(b); });

                if (!nos.length) {
                    $details.append($('<option></option>').attr('value', '').text('Select date first'));
                    $details.prop('disabled', true).val('');
                    fill7bOrgOptionsForDate($row, dateIso, '', false);
                    show7bNoMeetingValidation($row);
                    return '';
                }

                $details.append($('<option></option>').attr('value', '').text('Select'));
                nos.forEach(function (n) {
                    $details.append($('<option></option>').attr('value', n).text(n));
                });
                /* Keep a previously saved value available when editing. */
                if (preferredNo && nos.indexOf(preferredNo) === -1) {
                    $details.append($('<option></option>').attr('value', preferredNo).text(preferredNo));
                }

                $details.prop('disabled', false);
                $details.val(preferredNo || '');
                fill7bOrgOptionsForDate($row, dateIso, preferredOrg, true);
                return String($details.val() || '').trim();
            }

            function on7bMeetingDateChanged($dateInput, preferredNo, preferredOrg) {
                var $row = $dateInput.closest('.work-fields');
                if (!$row.length) return;
                var dateIso = '';
                if (typeof readWorkDateFromInput === 'function') {
                    dateIso = readWorkDateFromInput($dateInput);
                }
                if (!dateIso) {
                    dateIso = String($dateInput.val() || '').trim();
                }
                if ($dateInput.length) {
                    if (dateIso) $dateInput.attr('data-raw', dateIso);
                    else $dateInput.removeAttr('data-raw');
                }
                fill7bMeetingDetailsForDate($row, dateIso, preferredNo || '', preferredOrg || '');
            }

            $(document).on(
                'change input',
                '#work-container-current .work-board-meeting-date, .js-work-container[data-work-part="current"] .work-board-meeting-date',
                function () {
                    on7bMeetingDateChanged($(this), '', '');
                }
            );

            $(document).on(
                'change',
                '#work-container-current .work-employer-input, .js-work-container[data-work-part="current"] .work-employer-input',
                function () {
                    var $row = $(this).closest('.work-fields');
                    $row.find('.work-level-sync').val(String($(this).val() || '').trim());
                }
            );

            function competencyStoredDocHref(storedPath) {
                storedPath = String(storedPath || '').trim();
                if (!storedPath) return '';
                if (/^https?:\/\//i.test(storedPath)) return storedPath;
                if (/^FORM_[A-Z]+\//i.test(storedPath)) {
                    var relative = '/' + DOCUMENT_PUBLIC_URL_PREFIX + '/' + storedPath.replace(/^\/+/, '');
                    return DOCUMENT_PUBLIC_BASE_URL ? (DOCUMENT_PUBLIC_BASE_URL + relative) : relative;
                }
                if (storedPath.charAt(0) === '/') return storedPath;
                return '/' + storedPath.replace(/^\/+/, '');
            }

            // Keep local in this partial so pages that don't load form_s.js
            // still have the same upload-error cleanup behavior.
            function clearWorkRowUploadErrors($scope) {
                if (!$scope || !$scope.length) return;
                $scope.find('.error-message').each(function () {
                    var txt = ($(this).text() || '').toLowerCase();
                    if (
                        txt.indexOf('supporting document is required') !== -1 ||
                        txt.indexOf('relieving letter is required') !== -1 ||
                        txt.indexOf('highest transformer capacity') !== -1 ||
                        txt.indexOf('only pdf') !== -1 ||
                        txt.indexOf('only pdf, jpg or png') !== -1 ||
                        txt.indexOf('file size permitted') !== -1
                    ) {
                        $(this).remove();
                    }
                });
            }

            function isTillDateWorkRow($tr) {
                return $tr.hasClass('fs-till-date-work')
                    || $tr.find('.work-date-till').is(':checked')
                    || String($tr.find('.work-date-till-hidden').val() || '') === '1';
            }

            function isAlterationFrozenRow($tr) {
                /* Non-till existing rows stay read-only. Till date rows can be edited on renewal,
                   and on alteration after Work Experience Edit is clicked. */
                if (!$tr.hasClass('fs-alt-existing-work')) return false;
                if (isTillDateWorkRow($tr) && $('#competency_form_ws.fs-alt-form').hasClass('fs-alt-work-unlocked')) {
                    return false;
                }
                return true;
            }

            function applyFrozenSummaryActions($tr, $str) {
                if (!$str || !$str.length || !isAlterationFrozenRow($tr)) return;
                $str.addClass('work-exp-summary-tr--frozen');
                $str.find('.work-row-edit-trigger, .work-row-remove').remove();
                var $inner = $str.find('.wx-summary-actions-inner');
                if ($inner.length && !$inner.find('.wx-sum-frozen-label').length) {
                    $inner.html('<span class="wx-sum-frozen-label">—</span>');
                }
            }

            function restoreEditableSummaryActions($str) {
                if (!$str || !$str.length) return;
                $str.removeClass('work-exp-summary-tr--frozen');
                $str.find('.wx-sum-frozen-label').remove();
                var $inner = $str.find('.wx-summary-actions-inner');
                if ($inner.length && !$inner.find('.work-row-edit-trigger').length) {
                    $inner.prepend(
                        $('<button type="button" class="wx-order-edit-link work-row-edit-trigger" aria-label="Edit this work experience entry">')
                            .html('<i class="fa fa-pencil" aria-hidden="true"></i> Edit')
                    );
                }
                /* Existing master rows must not be removable on alteration. */
                $inner.find('.work-row-remove, .remove-work').remove();
            }

            /**
             * Alteration form: unlock Add row and Till date existing rows only.
             */
            window.wxSetAlterationExistingWorkUnlocked = function (unlocked) {
                var $form = $('#competency_form_ws.fs-alt-form');
                if (!$form.length) return;
                $form.toggleClass('fs-alt-work-unlocked', !!unlocked);
                $('.fs-alt-existing-work').each(function () {
                    var $tr = $(this);
                    var canEditTillDate = !!unlocked && isTillDateWorkRow($tr);
                    if (canEditTillDate) {
                        $tr.find('input, textarea, select, button').prop('disabled', false);
                        $tr.find('.work-duration-y, .work-duration-m, .work-duration-d, .work-year-total-display')
                            .prop('disabled', true)
                            .prop('readonly', true);
                        initWorkRow($tr);
                        restoreEditableSummaryActions(getSummaryTr($tr));
                        return;
                    }
                    $tr.find('input, textarea, select, button')
                        .not('input[type="hidden"]')
                        .prop('disabled', true);
                    $tr.find('input[name="work_id[]"], input[name="fs_alt_existing_work[]"]').prop('disabled', false);
                    $tr.addClass('is-complete work-row--compact work-row--in-summary').removeClass('work-row--expanded');
                    updateRowSummary($tr);
                    applyFrozenSummaryActions($tr, getSummaryTr($tr));
                });
                syncSummaryTable();
                updateWorkAddBtn();
            };

            function workContainers() {
                var $multi = $('.js-work-container');
                if ($multi.length) return $multi;
                return $('#work-container');
            }

            function allWorkFields() {
                return workContainers().find('.work-fields');
            }

            /** 7a previous work only — 7b current work is excluded from the 2-year minimum total. */
            function workFieldsForTwoYearTotal() {
                var $prev = $('#work-container-previous .work-fields');
                if ($prev.length) return $prev;
                return $('#work-container .work-fields');
            }

            /** Voltage "Up to 650V" does not count toward the combined experience total (all appl types). */
            function workRowCountsTowardExperienceTotal($tr) {
                if (!$tr || !$tr.length) return false;
                var voltage = ($tr.find('.work-voltage').val() || '').trim();
                return voltage !== VOLTAGE_DISABLES_KVA;
            }

            function totalDurationAcrossRows() {
                var totalMs = 0;
                var anyCountable = false;
                var anyDatedExcluded650v = false;
                workFieldsForTwoYearTotal().each(function() {
                    var $tr = $(this);
                    var fromStr = readWorkDateFromInput($tr.find('.work-date-from'));
                    var toStr = effectiveToStr($tr);
                    if (!fromStr || !toStr) return;
                    var from = new Date(fromStr + 'T12:00:00');
                    var to   = new Date(toStr + 'T12:00:00');
                    if (isNaN(from.getTime()) || isNaN(to.getTime())) return;
                    if (to < from) return;
                    if (!workRowCountsTowardExperienceTotal($tr)) {
                        anyDatedExcluded650v = true;
                        return;
                    }
                    anyCountable = true;
                    /* Inclusive of From and To (+1 day). */
                    totalMs += (to - from) + 86400000;
                });
                return {
                    ms: totalMs,
                    hasAny: anyCountable,
                    hasExcluded650vOnly: anyDatedExcluded650v && !anyCountable,
                    hasExcluded650v: anyDatedExcluded650v
                };
            }

            function workExpTotalMsgEl() {
                var $prev = $('#work-exp-total-msg-previous');
                if ($prev.length) return $prev;
                return $('#work-exp-total-msg');
            }

            function workContainerFor(el) {
                var $c = $(el).closest('.js-work-container');
                if ($c.length) return $c;
                return $('#work-container');
            }

            function summaryTbodyFor($container) {
                var part = ($container.data('work-part') || 'all').toString();
                if (part === 'previous') return $('#work-exp-summary-tbody-previous');
                if (part === 'current') return $('#work-exp-summary-tbody-current');
                return $('#work-exp-summary-tbody');
            }

            function summaryPanelFor($container) {
                var part = ($container.data('work-part') || 'all').toString();
                var $panel = $('#work-exp-summary-panel-' + part);
                if ($panel.length) return $panel;
                return $('#work-exp-summary-panel');
            }

            /** §7b (and similar) keeps the inline form visible — no summary table to collapse into. */
            function workContainerUsesSummaryPanel($container) {
                return summaryPanelFor($container).length > 0;
            }
            var EMP_LABEL = {
                '': 'Select employment type',
                private_organisation: 'Private organization',
                electrical_contractor: 'Electrical Contractor',
                retired_employee: 'Retired Employee',
                govt_organisation: 'Government Organization',
                apprenticeship: 'Apprenticeship',
                board_member_tnelb: 'Board member of TNELB or Ex board member of TNELB'
            };
            var NATURE_LABEL = {
                erection: 'Erection',
                maintenance: 'Maintenance',
                erection_maintenance: 'Erection & Maintenance'
            };
            var VOLTAGE_LABEL = {
                up_to_650v: 'Up to 650V',
                '650v_to_33kv': 'Above 650V to 33KV',
                above_33kv: 'Above 33KV'
            };
            var MONTH_SHORT = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

            function summaryFilePreviewUrl($input) {
                if (!$input || !$input.length) return '';
                var $preview = $input.closest('.form-s-file-upload-wrap').nextAll('.local-file-preview').first();
                return ($preview.length && $preview.data('blobUrl')) ? $preview.data('blobUrl') : '';
            }

            function summaryExistingDocHref($row, kind) {
                if (!$row || !$row.length) return '';
                var name = (kind === 'relieve')
                    ? 'existing_work_relieving_document[]'
                    : 'existing_work_document[]';
                return competencyStoredDocHref(namedInputVal($row, name));
            }

            function summaryAttachmentBlock(label, $input, naText, $row) {
                var $block = $('<div class="wx-sum-attach-block">');
                $block.append($('<span class="wx-sum-attach-label">').text(label + ' :'));
                if (naText) {
                    $block.append($('<span class="wx-sum-attach-value">').text(naText));
                    return $block;
                }
                var file = ($input[0] && $input[0].files && $input[0].files[0]) ? $input[0].files[0] : null;
                var isImage = file && file.type && file.type.indexOf('image/') === 0;
                var blobUrl = summaryFilePreviewUrl($input);
                if (!blobUrl && file) {
                    blobUrl = URL.createObjectURL(file);
                }
                var existingHref = summaryExistingDocHref($row, label === 'Relieving' ? 'relieve' : 'support');
                if (blobUrl) {
                    var icon = isImage ? 'fa-image' : 'fa-file-pdf-o';
                    $block.append(
                        $('<a>', {
                            href: blobUrl,
                            target: '_blank',
                            rel: 'noopener noreferrer',
                            class: 'preview-link wx-sum-doc-link'
                        }).html('<i class="fa ' + icon + '"></i> View Document')
                    );
                } else if (existingHref) {
                    $block.append(
                        $('<a>', {
                            href: existingHref,
                            target: '_blank',
                            rel: 'noopener noreferrer',
                            class: 'preview-link wx-sum-doc-link'
                        }).html('<i class="fa fa-file-pdf-o"></i> View Document')
                    );
                } else if ($input.attr('data-has-local-file')) {
                    $block.append($('<span class="wx-sum-attach-value">').text('File attached'));
                } else {
                    $block.append($('<span class="wx-sum-attach-value">').text('—'));
                }
                return $block;
            }

            function $workRow(el) { return $(el).closest('.work-fields'); }

            /* Legacy backend (work_level[], experience[]) still expects something; mirror it from the
               new fields so server-side `required` rules pass. */
            function syncLegacyHidden($tr) {
                var emp = ($tr.find('.work-employer-input').val() || '').trim();
                var tot = ($tr.find('.work-experience-total-hidden').val() || '').trim();
                /* Prefer calculated decimal total; fall back to stored Y/M/D when total_exp was never saved. */
                if (!tot) {
                    var y = parseInt($tr.find('.work-duration-y').val(), 10) || 0;
                    var m = parseInt($tr.find('.work-duration-m').val(), 10) || 0;
                    var d = parseInt($tr.find('.work-duration-d').val(), 10) || 0;
                    if (y || m || d) {
                        tot = (Math.round((y + (m / 12) + (d / 365.25)) * 10) / 10).toFixed(1);
                        $tr.find('.work-experience-total-hidden').val(tot);
                    }
                }
                $tr.find('.work-level-sync').val(emp);
                $tr.find('.experience-sync').val(tot);
            }

            function clearWorkDuration($tr) {
                $tr.find('.work-duration-y, .work-duration-m, .work-duration-d').val('');
                $tr.find('.work-experience-total-hidden').val('');
            }

            /** Inclusive calendar Y/M/D: both From and To count (measure to To+1 day).
             *  e.g. 01-07-2020 → 30-06-2021 = 1y 0m 0d; → 01-07-2021 = 1y 0m 1d.
             *  Mirrors server `workExperienceCalendarYmd`. */
            function calendarDiffYMD(from, to) {
                if (isNaN(from.getTime()) || isNaN(to.getTime()) || to < from) return null;
                var end = new Date(to.getFullYear(), to.getMonth(), to.getDate() + 1, 12, 0, 0);
                var y = end.getFullYear() - from.getFullYear();
                var m = end.getMonth() - from.getMonth();
                var d = end.getDate() - from.getDate();
                if (d < 0) {
                    m--;
                    d += new Date(end.getFullYear(), end.getMonth(), 0).getDate();
                }
                if (m < 0) {
                    y--;
                    m += 12;
                }
                if (d < 0) {
                    m--;
                    if (m < 0) {
                        y--;
                        m += 12;
                    }
                    d += new Date(end.getFullYear(), end.getMonth(), 0).getDate();
                }
                return { y: y, m: m, d: d };
            }

            function todayIso() {
                var n = new Date();
                return n.getFullYear() + '-' + String(n.getMonth() + 1).padStart(2, '0') + '-' + String(n.getDate()).padStart(2, '0');
            }

            function fmtPretty(iso) {
                if (!iso) return '';
                var p = iso.split('-');
                if (p.length !== 3) return iso;
                var y = parseInt(p[0], 10), m = parseInt(p[1], 10), d = parseInt(p[2], 10);
                if (isNaN(y) || isNaN(m) || isNaN(d) || m < 1 || m > 12) return iso;
                return d + ' ' + MONTH_SHORT[m - 1] + ' ' + y;
            }

            /** Parse work date input to ISO yyyy-mm-dd (native date + DD-MM-YYYY display). */
            function parseWorkDateToIso(str) {
                var s = String(str || '').trim();
                if (!s) return '';
                if (/^\d{4}-\d{2}-\d{2}$/.test(s)) return s;
                var m = s.match(/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/);
                return m ? (m[3] + '-' + String(m[2]).padStart(2, '0') + '-' + String(m[1]).padStart(2, '0')) : '';
            }

            function readWorkDateFromInput($input) {
                if (typeof window.readWorkDateIsoGeneric === 'function') {
                    return window.readWorkDateIsoGeneric($input);
                }
                var $el = ($input && $input.length) ? $input.first() : $();
                var node = $el.get(0);
                if (!node) return '';
                var candidates = [];
                if (node.type === 'date' && node.value) {
                    candidates.push(String(node.value).trim());
                }
                candidates.push(String($el.val() || node.value || '').trim());
                candidates.push(String(node.getAttribute('data-raw') || '').trim());
                for (var i = 0; i < candidates.length; i++) {
                    var iso = parseWorkDateToIso(candidates[i]);
                    if (iso) return iso;
                }
                return '';
            }

            function syncWorkDateRaw($input) {
                var iso = readWorkDateFromInput($input);
                if (iso && $input && $input.length) {
                    $input.get(0).setAttribute('data-raw', iso);
                }
            }

            var WORK_DATE_MAX_ISO = '9999-12-31';

            /** True when ISO year is a real 4-digit year (not 0097 / 0006 from partial typing). */
            function workDateYearIsPlausible(iso) {
                if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return false;
                var y = parseInt(iso.slice(0, 4), 10);
                return y >= 1000 && y <= 9999;
            }

            /** Trim year only when it already has more than 4 digits. Do not rewrite the value while typing. */
            function clampWorkDateYearDigits(el) {
                if (!el || el.type === 'hidden') return false;
                var v = String(el.value || '').trim();
                if (!v) return false;
                var next = v;
                var iso = v.match(/^(\d{5,})(-(\d{0,2})(-(\d{0,2}))?)?$/);
                if (iso) {
                    next = iso[1].slice(0, 4);
                    if (iso[2]) next += iso[2];
                } else {
                    var dmy = v.match(/^(\d{1,2})([-\/])(\d{1,2})\2(\d{5,})$/);
                    if (dmy) {
                        next = dmy[1] + dmy[2] + dmy[3] + dmy[2] + dmy[4].slice(0, 4);
                    }
                }
                if (next === v) return false;
                el.value = next;
                return true;
            }

            function applyWorkDateYearCap(el) {
                if (!el || el.type === 'hidden') return;
                el.removeAttribute('min');
                if (!el.getAttribute('max')) {
                    el.setAttribute('max', WORK_DATE_MAX_ISO);
                }
            }

            /** Reset cloned work date fields to native date inputs (clone may copy type="text" from initDateDisplay). */
            function resetWorkRowDateInputs(row) {
                if (!row) return;
                row.querySelectorAll('.work-date-from, .work-date-to').forEach(function(inp) {
                    inp.removeAttribute('data-raw');
                    inp.value = '';
                    inp.type = 'date';
                    applyWorkDateYearCap(inp);
                });
            }

            /** Re-bind DD-MM-YYYY display + native picker (edit_application initDateDisplay). */
            function bindWorkRowDateDisplay(row) {
                if (!row || typeof initDateDisplay !== 'function') return;
                row.querySelectorAll('.work-date-from, .work-date-to').forEach(initDateDisplay);
            }

            function clearWorkDateFieldErrors($input) {
                if (typeof window.clearWorkDateRequiredErrors === 'function') {
                    window.clearWorkDateRequiredErrors($input);
                    return;
                }
                if (!$input || !$input.length) return;
                $input.nextAll('.error-message').each(function() {
                    var txt = ($(this).text() || '').toLowerCase();
                    if (txt.indexOf('to date is required') !== -1 || txt.indexOf('from date is required') !== -1) {
                        $(this).remove();
                    }
                });
            }

            /** Effective To date for a row: explicit value, or today if "Till date" is checked. */
            function effectiveToStr($tr) {
                if ($tr.find('.work-date-till').is(':checked')) return todayIso();
                return readWorkDateFromInput($tr.find('.work-date-to'));
            }

            /** Validation message lives below the card, outside .work-row border. */
            function workRowCard($tr) {
                if (!$tr || !$tr.length) return $();
                return $tr.hasClass('work-fields') ? $tr : $tr.closest('.work-fields');
            }

            function ensureWorkEntryBlock($card) {
                var $c = workRowCard($card);
                if (!$c.length || $c.parent('.work-entry-block').length) return $c.parent('.work-entry-block');
                var $slot = $c.find('.work-row-date-validation').first().detach();
                if (!$slot.length) {
                    $slot = $('<div class="work-row-date-validation" aria-live="polite"></div>');
                }
                $c.wrap('<div class="work-entry-block"></div>');
                $c.parent('.work-entry-block').append($slot);
                return $c.parent('.work-entry-block');
            }

            function workRowDateValidationSlot($tr) {
                var $card = workRowCard($tr);
                if (!$card.length) return $();
                ensureWorkEntryBlock($card);
                var $block = $card.closest('.work-entry-block');
                if ($block.length) {
                    return $block.children('.work-row-date-validation').first();
                }
                var $slot = $card.next('.work-row-date-validation');
                if ($slot.length) return $slot;
                return $('<div class="work-row-date-validation" aria-live="polite"></div>').insertAfter($card);
            }

            function workEntryRemoveTarget(cardEl) {
                if (!cardEl) return null;
                var block = cardEl.closest('.work-entry-block');
                return block || cardEl;
            }

            function clearWorkRowDateRangeError($tr) {
                if (!$tr || !$tr.length) return;
                workRowDateValidationSlot($tr).empty();
                $tr.find('.work-exp-date-range-error').remove();
                $tr.removeData('wxDateSequenceError');
            }

            function showWorkRowDateRangeError($tr, message) {
                if (!$tr || !$tr.length || !message) return;
                clearWorkRowDateRangeError($tr);
                $tr.data('wxDateSequenceError', message);
                workRowDateValidationSlot($tr).html(
                    '<span class="error-message text-danger d-block work-exp-date-range-error" role="alert">' +
                        message +
                    '</span>'
                );
            }

            /** True when the row card is shown for editing (not collapsed into the summary table). */
            function isWorkRowActiveInForm($tr) {
                if (!$tr || !$tr.length) return false;
                if ($tr.hasClass('work-row--in-summary')) return false;
                return $tr.is(':visible');
            }

            /** §7a (previous / legacy) enforces non-overlapping sequential periods. §7b does not. */
            function isPreviousWorkPartContainer($container) {
                if (!$container || !$container.length) return false;
                var part = ($container.data('work-part') || 'all').toString();
                if (part === 'current') return false;
                return true;
            }

            function workRowPeriodBounds($tr) {
                if (!$tr || !$tr.length) return null;
                var fromStr = readWorkDateFromInput($tr.find('.work-date-from'));
                var toStr = effectiveToStr($tr);
                if (!fromStr && !toStr) return null;
                var from = fromStr ? new Date(fromStr + 'T12:00:00') : null;
                var to = toStr ? new Date(toStr + 'T12:00:00') : null;
                if (from && isNaN(from.getTime())) from = null;
                if (to && isNaN(to.getTime())) to = null;
                return { fromStr: fromStr, toStr: toStr, from: from, to: to };
            }

            /**
             * §7a: no experience period may overlap ANY other §7a period.
             * Rows may be entered in any order (oldest→newest, newest→oldest, random).
             * Two periods clash when they intersect, and touching endpoints count as a
             * clash (a new From must be strictly after every other row's To).
             */
            function workRowDateSequenceMessage($tr) {
                if (!$tr || !$tr.length) return '';
                var $container = workContainerFor($tr);
                if (!isPreviousWorkPartContainer($container)) return '';

                var bounds = workRowPeriodBounds($tr);
                if (!bounds) return '';

                if (bounds.fromStr && !workDateYearIsPlausible(bounds.fromStr)) {
                    var fromEl = $tr.find('input.work-date-from:not([type="hidden"])').get(0);
                    if (fromEl && document.activeElement === fromEl) {
                        return '';
                    }
                    return 'Enter a 4-digit year for From date.';
                }
                if (bounds.toStr && !workDateYearIsPlausible(bounds.toStr)) {
                    var toEl = $tr.find('input.work-date-to:not([type="hidden"])').get(0);
                    if (toEl && document.activeElement === toEl) {
                        return '';
                    }
                    return 'Enter a 4-digit year for To date.';
                }

                if (bounds.from && bounds.to && bounds.to < bounds.from) {
                    return 'To date must be greater than or equal to From date.';
                }

                // Order-independent overlap check: compare this row against EVERY other
                // §7a row (not just the immediate previous/next). Only rows that have a
                // complete, valid period participate; incomplete/invalid rows report their
                // own errors and are skipped here.
                if (bounds.from && bounds.to) {
                    var $rows = $container.find('.work-fields');
                    var selfEl = $tr.get(0);
                    var overlapMsg = '';
                    $rows.each(function () {
                        if (overlapMsg) return;
                        if (this === selfEl) return;
                        var other = workRowPeriodBounds($(this));
                        if (!other || !other.from || !other.to) return;
                        if (other.fromStr && !workDateYearIsPlausible(other.fromStr)) return;
                        if (other.toStr && !workDateYearIsPlausible(other.toStr)) return;
                        if (other.to < other.from) return;
                        // Inclusive intersection: [from,to] ∩ [other.from,other.to] ≠ ∅.
                        // Shared endpoints count as an overlap, so a From equal to another
                        // row's To is rejected (next From must be strictly after that To).
                        if (bounds.from <= other.to && other.from <= bounds.to) {
                            var selfToLabel = $tr.find('.work-date-till').is(':checked')
                                ? 'Till date' : fmtPretty(bounds.toStr);
                            var otherToLabel = $(this).find('.work-date-till').is(':checked')
                                ? 'Till date' : fmtPretty(other.toStr);
                            overlapMsg = 'Experience periods must not overlap. This period (' +
                                fmtPretty(bounds.fromStr) + ' \u2013 ' + selfToLabel +
                                ') clashes with another row (' +
                                fmtPretty(other.fromStr) + ' \u2013 ' + otherToLabel + ').';
                        }
                    });
                    if (overlapMsg) return overlapMsg;
                }

                return '';
            }

            function workRowHasDateSequenceError($tr) {
                return !!workRowDateSequenceMessage($tr);
            }

            /** Overlap is a validation message only. Do not rewrite max/min while the user is typing. */
            function syncWorkDateSequenceBounds($container) {
                if (!$container || !$container.length) return;
                $container.find('.work-fields').each(function () {
                    var $tr = $(this);
                    var $from = $tr.find('input.work-date-from:not([type="hidden"])').first();
                    var $to = $tr.find('input.work-date-to:not([type="hidden"])').first();
                    if ($from.length) $from.removeAttr('min');
                    if ($to.length) $to.removeAttr('min');
                });
            }

            /**
             * Per-row From/To order + §7a non-overlap against neighbouring rows.
             * Returns true when the row has no date-sequence error.
             */
            function validateWorkRowDateRange($tr) {
                if (!$tr || !$tr.length) return true;
                var $container = workContainerFor($tr);
                syncWorkDateSequenceBounds($container);

                var fromStr = readWorkDateFromInput($tr.find('.work-date-from'));
                var toStr = effectiveToStr($tr);
                /* Keep the last message while the user is still entering dates (do not clear on partial input). */
                if (!fromStr && !toStr) {
                    if (isWorkRowActiveInForm($tr)) clearWorkRowDateRangeError($tr);
                    return true;
                }

                var msg = workRowDateSequenceMessage($tr);
                if (msg) {
                    if (isWorkRowActiveInForm($tr)) {
                        showWorkRowDateRangeError($tr, msg);
                    } else {
                        $tr.data('wxDateSequenceError', msg);
                    }
                    return false;
                }

                if (isWorkRowActiveInForm($tr)) {
                    if (fromStr && toStr) {
                        clearWorkRowDateRangeError($tr);
                    }
                } else {
                    clearWorkRowDateRangeError($tr);
                }
                return true;
            }

            function expandWorkRowForDateError($tr) {
                if (!$tr || !$tr.length) return;
                if ($tr.hasClass('work-row--in-summary') || $tr.hasClass('work-row--compact')) {
                    $tr.removeClass('work-row--in-summary work-row--compact').addClass('work-row--expanded');
                    applyRowLayout($tr);
                    ensureWorkRowDoneBar($tr);
                    syncSummaryTable();
                }
                showWorkRowDateRangeError($tr, workRowDateSequenceMessage($tr) || ($tr.data('wxDateSequenceError') || ''));
            }

            /** Re-check every §7a row after a date change (neighbours can become invalid). */
            function validateWorkContainerDateSequence($container) {
                if (!$container || !$container.length || !isPreviousWorkPartContainer($container)) {
                    return { ok: true };
                }
                syncWorkDateSequenceBounds($container);
                var firstInvalid = null;
                var firstMessage = '';
                $container.find('.work-fields').each(function () {
                    var $row = $(this);
                    var msg = workRowDateSequenceMessage($row);
                    if (msg) {
                        if (isWorkRowActiveInForm($row)) {
                            showWorkRowDateRangeError($row, msg);
                        } else {
                            $row.data('wxDateSequenceError', msg);
                        }
                        if (!firstInvalid) {
                            firstInvalid = $row;
                            firstMessage = msg;
                        }
                    } else if (isWorkRowActiveInForm($row)) {
                        var fromStr = readWorkDateFromInput($row.find('.work-date-from'));
                        var toStr = effectiveToStr($row);
                        if (fromStr && toStr) clearWorkRowDateRangeError($row);
                    } else {
                        clearWorkRowDateRangeError($row);
                    }
                });
                if (firstInvalid) {
                    return {
                        ok: false,
                        $row: firstInvalid,
                        message: firstMessage || 'Experience periods must not overlap. Each From date must be strictly after every other period\'s To date.'
                    };
                }
                return { ok: true };
            }

            /** Section meter + legacy combined-message kept in sync. */
            function updateOverallTotalYears() {
                var t = totalDurationAcrossRows();
                /* Legacy banner under the cards (kept for backward compatibility with footer.blade.php). */
                var $msg = workExpTotalMsgEl();
                if ($msg.length) {
                    var needsTwoYears = t.hasAny || t.hasExcluded650vOnly;
                    if (!needsTwoYears || t.ms >= TWO_YEARS_MS) {
                        $msg.empty();
                    } else if (t.hasExcluded650vOnly) {
                        $msg.html(
                            '<div class="work-exp-total-error error-message text-danger" role="alert">' +
                                'Experience with Voltage Level "Up to 650V" is not counted. Add experience above 650V totaling at least 2 years.' +
                            '</div>'
                        );
                    } else {
                        $msg.html(
                            '<div class="work-exp-total-error error-message text-danger" role="alert">' +
                                'Minimum 2 Years Experience needed across all entries (Voltage up to 650V is not counted).' +
                            '</div>'
                        );
                    }
                }
                updateSummaryOverallExperience();
                updateWorkAddBtn();
            }

            /** Sum Y/M/D across a container and write into the summary table footer. */
            function normalizeOverallYMD(y, m, d) {
                m += Math.floor(d / 30);
                d = d % 30;
                y += Math.floor(m / 12);
                m = m % 12;
                return { y: y, m: m, d: d };
            }

            function overallYmdForContainer($container) {
                var y = 0, m = 0, d = 0;
                $container.find('.work-fields').each(function() {
                    var $tr = $(this);
                    var yN = parseInt($tr.find('.work-duration-y').val(), 10) || 0;
                    var mN = parseInt($tr.find('.work-duration-m').val(), 10) || 0;
                    var dN = parseInt($tr.find('.work-duration-d').val(), 10) || 0;
                    if (!yN && !mN && !dN) {
                        var fromIso = readWorkDateFromInput($tr.find('.work-date-from'));
                        var toIso = effectiveToStr($tr);
                        if (fromIso && toIso) {
                            var fromDt = new Date(fromIso + 'T12:00:00');
                            var toDt = new Date(toIso + 'T12:00:00');
                            var diff = calendarDiffYMD(fromDt, toDt);
                            if (diff) {
                                yN = diff.y;
                                mN = diff.m;
                                dN = diff.d;
                            }
                        }
                    }
                    y += yN;
                    m += mN;
                    d += dN;
                });
                return normalizeOverallYMD(y, m, d);
            }

            function updateSummaryOverallExperience($onlyContainer) {
                var $containers = $onlyContainer && $onlyContainer.length
                    ? $onlyContainer
                    : workContainers();
                $containers.each(function() {
                    var $container = $(this);
                    var $panel = summaryPanelFor($container);
                    if (!$panel.length) return;
                    var $tfoot = $panel.find('.wx-overall-exp-tfoot');
                    if (!$tfoot.length) return;
                    var hasVisible = $panel.find('.work-exp-summary-tr:visible').length > 0;
                    if (!hasVisible) {
                        $tfoot.attr('hidden', true);
                        return;
                    }
                    var tot = overallYmdForContainer($container);
                    $tfoot.find('.wx-overall-y').text(String(tot.y));
                    $tfoot.find('.wx-overall-m').text(String(tot.m));
                    $tfoot.find('.wx-overall-d').text(String(tot.d));
                    $tfoot.removeAttr('hidden');
                });
            }

            function isAlterationWorkAddLocked() {
                var $form = $('#competency_form_ws.fs-alt-form');
                if (!$form.length) return false;
                return !$form.hasClass('fs-alt-work-unlocked');
            }

            /** Row-count badge + disable state on the Add button. */
            function updateWorkAddBtn() {
                var altLocked = isAlterationWorkAddLocked();
                $('.add-more-work').each(function() {
                    var $btn = $(this);
                    var containerId = $btn.data('work-container');
                    var maxRows = parseInt($btn.data('max-rows'), 10) || MAX_WORK_ROWS;
                    var $container = containerId ? $('#' + containerId) : $('#work-container');
                    if (!$container.length) return;
                    var rows = $container.find('.work-fields').length;
                    var $count = $btn.find('[id^="work-exp-row-count"]');
                    if ($count.length) {
                        $count.text('(' + rows + '/' + maxRows + ')');
                    }
                    var atMax = rows >= maxRows;
                    var rowsReady = typeof workContainerCanAddRow === 'function'
                        ? workContainerCanAddRow($container)
                        : true;
                    $btn.prop('disabled', altLocked || atMax || !rowsReady);
                });
                if (!$('.add-more-work').length) {
                    var rows = allWorkFields().length;
                    $('#work-exp-row-count').text('(' + rows + '/' + MAX_WORK_ROWS + ')');
                    $('#work-exp-add-btn').prop('disabled', altLocked || rows >= MAX_WORK_ROWS);
                }
            }

            /** Toggle the .is-locked class + lock-icon visibility + conditional hint on a field wrapper. */
            function setFieldLock($tr, fieldName, locked) {
                var $f = $tr.find('.work-card-field[data-field="' + fieldName + '"]');
                if (!$f.length) return;
                $f.toggleClass('is-locked', !!locked);
                $f.find('.lock-icon').toggle(!!locked);
                if (locked) {
                    $f.find('.work-card-field-hint[data-hint="' + fieldName + '"], ' +
                            '.work-card-field-hint[data-hint="' + mapHintName(fieldName) + '"]').show();
                    if (fieldName === 'relieve') $f.find('.work-card-field-hint[data-hint="relieve-default"]').hide();
                } else {
                    $f.find('.work-card-field-hint[data-hint="' + fieldName + '"], ' +
                            '.work-card-field-hint[data-hint="' + mapHintName(fieldName) + '"]').hide();
                    if (fieldName === 'relieve') $f.find('.work-card-field-hint[data-hint="relieve-default"]').show();
                }
            }

            /** Unlock a column input and clear any stale lock styling on its cell. */
            function unlockWorkField($tr, $input, fieldName) {
                if (!$input || !$input.length) return;
                $input.prop('disabled', false).removeAttr('disabled').removeClass('is-locked');
                if (fieldName) {
                    setFieldLock($tr, fieldName, false);
                }
            }

            /** Show or hide board-meeting sub-question panel for Board Member employment type. */
            function toggleBoardMeetingFields($tr, show) {
                var $panel = $tr.find('.work-board-member-panel');
                $panel.toggle(!!show);
                $tr.toggleClass('work-row--board-member', !!show);
                var $details = $tr.find('.work-board-meeting-details');
                var $date = $tr.find('.work-board-meeting-date');
                if (show) {
                    $tr.find('.work-board-meeting-placeholder').remove();
                    $details.attr('name', 'work_board_meeting_details[]').prop('disabled', false).prop('required', true);
                    $date.attr('name', 'work_board_meeting_date[]').prop('disabled', false).prop('required', true);
                } else {
                    $details.removeAttr('name').val('').prop('disabled', true).prop('required', false);
                    $date.removeAttr('name').val('').prop('disabled', true).prop('required', false);
                    if (!$tr.find('.work-board-meeting-placeholder').length) {
                        $('<input type="hidden" class="work-board-meeting-placeholder" name="work_board_meeting_details[]" value="">').prependTo($tr);
                        $('<input type="hidden" class="work-board-meeting-placeholder" name="work_board_meeting_date[]" value="">').prependTo($tr);
                    }
                }
            }

            /** Board Member: disable contractor / technical columns; enable org, dates, uploads. */
            function applyBoardMemberEmployment($tr) {
                var isCurrentPart = $tr.closest('.js-work-container[data-work-part="current"], #work-container-current').length > 0;
                var $cat = $tr.find('.work-contractor-cat');
                var $lic = $tr.find('.work-licence-number');
                var $emp = $tr.find('.work-employer-input');
                var $addr = $tr.find('.work-org-address');
                var $des = $tr.find('.work-designation');
                var $nat = $tr.find('.work-nature');
                var $volt = $tr.find('.work-voltage');
                var $kva = $tr.find('.work-transformer-kva');
                var $yFrom = $tr.find('.work-date-from');
                var $yTo = $tr.find('.work-date-to');
                var $till = $tr.find('.work-date-till');
                var $doc = $tr.find('.work-doc-input');
                var $rel = $tr.find('.work-relieve-input');

                $cat.val('').prop('disabled', true).prop('required', false);
                $lic.val('').prop('disabled', true).prop('required', false);
                setFieldLock($tr, 'contractor-cat', true);
                setFieldLock($tr, 'licence-number', true);

                $nat.val('').prop('disabled', true).prop('required', false);
                $volt.val('').prop('disabled', true).prop('required', false);
                $kva.val('').prop('disabled', true).prop('required', false);
                setFieldLock($tr, 'work-nature', true);
                setFieldLock($tr, 'voltage-level', true);
                setFieldLock($tr, 'transformer-kva', true);
                syncTransformerKvaHidden($tr);
                syncContractorFieldsHidden($tr);
                $tr.find('[data-field="work-nature"] .req, [data-field="voltage-level"] .req, [data-field="transformer-kva"] .req').hide();

                unlockWorkField($tr, $emp, 'organisation');
                unlockWorkField($tr, $addr, 'organisation-address');
                unlockWorkField($tr, $des, 'designation');
                $emp.prop('required', true);
                $addr.prop('required', true);
                $des.prop('required', true);

                /* §7b has no From/To dates — do not mark hidden date inputs required. */
                if (!isCurrentPart) {
                    unlockWorkField($tr, $yFrom, 'from-date');
                    $yFrom.prop('required', true);
                    $till.prop('disabled', false);
                    applyTillDate($tr, true);
                    if (!$yTo.prop('disabled') && !$yTo.prop('readonly')) {
                        $yTo.prop('required', true);
                    }
                } else {
                    $yFrom.prop('required', false);
                    $yTo.prop('required', false);
                }

                unlockWorkField($tr, $doc, 'support-doc');
                /* §7b supporting docs are optional. */
                $doc.prop('required', !isCurrentPart);
                if (isCurrentPart) {
                    $tr.find('[data-field="support-doc"] .req').hide();
                }

                $rel.prop('required', false);
                if (!isCurrentPart && !$till.is(':checked')) {
                    unlockWorkField($tr, $rel, 'relieve');
                }
                $tr.find('[data-field="relieve"] .work-card-field-label .req').hide();
                $tr.find('[data-field="relieve"] .work-card-field-hint[data-hint="relieve-board"]').show();
                toggleBoardMeetingFields($tr, true);
            }
            /* data-field uses kebab; some hints use shorter names. */
            function mapHintName(fieldName) {
                switch (fieldName) {
                    case 'contractor-cat': return 'cat';
                    case 'licence-number': return 'licence';
                    case 'transformer-kva': return 'kva';
                    case 'work-nature': return 'work-nature';
                    case 'voltage-level': return 'voltage-level';
                    default: return fieldName;
                }
            }

            /** Row header: status pill, compact summary, expand/collapse. */
            function updateRowHeader($tr) {
                updateRowStatus($tr);
            }

            /** Complete rows collapse to summary strip unless manually expanded for editing. */
            function applyRowLayout($tr) {
                var complete = $tr.hasClass('is-complete');
                var expanded = $tr.hasClass('work-row--expanded');
                $tr.toggleClass('work-row--compact', complete && !expanded);
                $tr.find('.work-row-toggle-btn')
                    .attr('aria-expanded', expanded ? 'true' : 'false')
                    .attr('title', expanded ? 'Submit and return to summary card' : 'Expand to edit')
                    .attr('aria-label', expanded ? 'Submit entry and return to summary card' : 'Expand entry to edit');
            }

            /** Refresh summary and collapse expanded complete row back to order-card view. */
            function collapseToSummary($tr) {
                $tr.find('.work-row-done-hint').remove();
                var $container = workContainerFor($tr);
                if (isPreviousWorkPartContainer($container) && workRowHasDateSequenceError($tr)) {
                    validateWorkContainerDateSequence($container);
                    expandWorkRowForDateError($tr);
                    ensureWorkRowDoneBar($tr);
                    var $barOverlap = $tr.find('.work-row-done-bar');
                    if ($barOverlap.length && !$barOverlap.find('.work-row-done-hint').length) {
                        $barOverlap.append('<p class="work-row-done-hint" role="alert">Fix overlapping experience dates before submitting this entry.</p>');
                    }
                    applyRowLayout($tr);
                    return false;
                }
                var complete = isRowComplete($tr);
                $tr.toggleClass('is-complete', complete);
                $tr.data('wxWasComplete', complete);
                if (!complete) {
                    ensureWorkRowDoneBar($tr);
                    var $bar = $tr.find('.work-row-done-bar');
                    if ($bar.length && !$bar.find('.work-row-done-hint').length) {
                        $bar.append('<p class="work-row-done-hint" role="alert">Fill all required fields and upload documents before you can submit.</p>');
                    }
                    applyRowLayout($tr);
                    return false;
                }
                if (!workContainerUsesSummaryPanel(workContainerFor($tr))) {
                    $tr.addClass('work-row--expanded').removeClass('work-row--compact work-row--in-summary');
                    applyRowLayout($tr);
                    return true;
                }
                ensureWorkRowDoneBar($tr);
                $tr.removeClass('work-row--expanded');
                applyRowLayout($tr);
                updateRowSummary($tr);
                syncSummaryTable();
                updateWorkAddBtn();
                syncWorkDateSequenceBounds($container);
                return true;
            }

            /** Resolve the form row linked to a shared summary table row. */
            function workRowFromSummaryTr($summaryTr) {
                var linked = null;
                allWorkFields().each(function() {
                    var $str = $(this).data('wxSummaryTr');
                    if ($str && $str.length && $str[0] === $summaryTr[0]) {
                        linked = $(this);
                        return false;
                    }
                });
                return linked;
            }

            /** Create (once) the shared-table row for a work-fields block. */
            function getSummaryTr($tr) {
                var $container = workContainerFor($tr);
                var $tbody = summaryTbodyFor($container);
                var                 $str = $tr.data('wxSummaryTr');
                if ($str && $str.length) {
                    applyFrozenSummaryActions($tr, $str);
                    return $str;
                }
                var rowIdx = $tr.attr('data-row-index');
                if (rowIdx !== undefined && rowIdx !== '') {
                    var $existing = $tbody.find('.work-exp-summary-tr[data-work-row-index="' + rowIdx + '"]');
                    if ($existing.length) {
                        $tr.data('wxSummaryTr', $existing);
                        applyFrozenSummaryActions($tr, $existing);
                        return $existing;
                    }
                }
                var $summaryCells = [
                    $('<td class="work-row-summary-sno">'),
                    $('<td class="work-row-summary-employment">'),
                    $('<td class="work-row-summary-org-address">'),
                    $('<td class="work-row-summary-designation">')
                ];
                if (!WX_HIDE_VOLTAGE_FIELDS) {
                    $summaryCells.push(
                        $('<td class="work-row-summary-nature">'),
                        $('<td class="work-row-summary-voltage">'),
                        $('<td class="work-row-summary-kva">')
                    );
                }
                $summaryCells.push(
                    $('<td class="work-row-summary-period">'),
                    $('<td class="work-row-summary-attachments">'),
                    $('<td class="work-row-summary-actions">').append(
                        $('<div class="wx-summary-actions-inner">').append(
                            $('<button type="button" class="wx-order-edit-link work-row-edit-trigger" aria-label="Edit this work experience entry">')
                                .html('<i class="fa fa-pencil" aria-hidden="true"></i> Edit'),
                            $('<button type="button" class="work-row-remove remove-work" title="Remove this entry" aria-label="Remove this work experience entry">')
                                .html('<i class="fa fa-trash-o" aria-hidden="true"></i>')
                        )
                    )
                );
                $str = $('<tr class="work-exp-summary-tr">').attr('data-work-row-index', rowIdx || '').append($summaryCells);
                $tr.data('wxSummaryTr', $str);
                $tbody.append($str);
                applyFrozenSummaryActions($tr, $str);
                return $str;
            }

            /** Show complete collapsed rows in the shared table; hide their form cards. */
            function syncSummaryTable() {
                workContainers().each(function() {
                    var $container = $(this);
                    var $panel = summaryPanelFor($container);
                    var $tbody = summaryTbodyFor($container);
                    if (!$tbody.length) return;
                    var hasVisible = false;
                    var linkedRows = [];
                    $container.find('.work-fields').each(function() {
                        var $wf = $(this);
                        var inTable = $wf.hasClass('is-complete') && $wf.hasClass('work-row--compact');
                        if ($wf.hasClass('is-complete')) getSummaryTr($wf);
                        var $str = $wf.data('wxSummaryTr');
                        if (inTable && $str && $str.length) {
                            $tbody.append($str);
                            $str.show();
                            $wf.addClass('work-row--in-summary');
                            linkedRows.push($str[0]);
                            hasVisible = true;
                        } else {
                            if ($str && $str.length) $str.hide();
                            $wf.removeClass('work-row--in-summary');
                        }
                    });
                    $tbody.find('.work-exp-summary-tr').each(function() {
                        if (linkedRows.indexOf(this) === -1) {
                            $(this).hide();
                        }
                    });
                    if ($panel.length) {
                        $panel.toggleClass('is-visible', hasVisible);
                    }
                    updateSummaryOverallExperience($container);
                });
                refreshWorkSerials();
            }
            window.wxSyncWorkSummaryTable = syncSummaryTable;

            /** Summary table row — filled details shown when collapsed. */
            function updateRowSummary($tr) {
                if (!$tr.hasClass('is-complete')) return;
                var $str = getSummaryTr($tr);
                var type = ($tr.find('.work-employment-type').val() || '').trim();
                var employer = ($tr.find('.work-employer-input').val() || '').trim();
                var address = ($tr.find('.work-org-address').val() || '').trim();
                var designation = ($tr.find('.work-designation').val() || '').trim();
                var cat = ($tr.find('.work-contractor-cat').val() || '').trim();
                var licence = ($tr.find('.work-licence-number').val() || '').trim();
                var nature = ($tr.find('.work-nature').val() || '').trim();
                var voltage = ($tr.find('.work-voltage').val() || '').trim();
                var kva = ($tr.find('.work-transformer-kva').val() || '').trim();
                var fromIso = readWorkDateFromInput($tr.find('.work-date-from'));
                var toIso = readWorkDateFromInput($tr.find('.work-date-to'));
                var isTill = $tr.find('.work-date-till').is(':checked');
                var y = ($tr.find('.work-duration-y').val() || '').trim();
                var m = ($tr.find('.work-duration-m').val() || '').trim();
                var d = ($tr.find('.work-duration-d').val() || '').trim();
                var isContractor = (type === CONTRACTOR_TYPE);
                var isBoardMember = (type === BOARD_MEMBER_TYPE);

                /* Col 1 — Employment Type (+ contractor cat / licence) */
                var $empCell = $str.find('.work-row-summary-employment');
                $empCell.empty();
                $empCell.append($('<span class="wx-sum-main">').text(type ? (EMP_LABEL[type] || type) : '—'));
                if (isContractor && cat) {
                    $empCell.append($('<span class="wx-sum-sub">').text('Grade of Licence: ' + cat));
                }
                if (isContractor && licence) {
                    $empCell.append($('<span class="wx-sum-sub">').text('Licence No: ' + licence));
                }

                /* Col 2 — Organisation & Address */
                var $orgCell = $str.find('.work-row-summary-org-address');
                $orgCell.empty();
                $orgCell.append($('<span class="wx-sum-main">').text(employer || '—'));
                if (address) {
                    $orgCell.append($('<span class="wx-sum-sub">').text(address));
                }

                /* Col 3–6 */
                $str.find('.work-row-summary-designation').text(designation || '—');
                if (isBoardMember) {
                    $str.find('.work-row-summary-nature').text('Not applicable');
                    $str.find('.work-row-summary-voltage').text('Not applicable');
                    $str.find('.work-row-summary-kva').text('Not applicable');
                } else {
                    $str.find('.work-row-summary-nature').text(nature ? (NATURE_LABEL[nature] || nature) : '—');
                    $str.find('.work-row-summary-voltage').text(voltage ? (VOLTAGE_LABEL[voltage] || voltage) : '—');
                    if (voltage === VOLTAGE_DISABLES_KVA) {
                        $str.find('.work-row-summary-kva').text('Not applicable');
                    } else if (!kva) {
                        $str.find('.work-row-summary-kva').text('—');
                    } else if (kva === 'Above 1000') {
                        $str.find('.work-row-summary-kva').text('Above 1000');
                    } else {
                        $str.find('.work-row-summary-kva').text(kva + ' kVA');
                    }
                }

                /* Col 7 — From / To / Duration (mini boxes) */
                var $periodCell = $str.find('.work-row-summary-period');
                $periodCell.empty();
                var toEffIso = isTill ? todayIso() : toIso;
                var toText = isTill ? 'Till date' : (toIso ? fmtPretty(toIso) : '—');
                var yN = parseInt(y, 10) || 0, mN = parseInt(m, 10) || 0, dN = parseInt(d, 10) || 0;
                if (!y && !m && !d && fromIso && toEffIso) {
                    var fromDt = new Date(fromIso + 'T12:00:00');
                    var toDt = new Date(toEffIso + 'T12:00:00');
                    var diff = calendarDiffYMD(fromDt, toDt);
                    if (diff) { yN = diff.y; mN = diff.m; dN = diff.d; }
                }
                var $box = $('<div class="wx-period-box">');
                var $dates = $('<div class="wx-period-dates">');
                $dates.append(
                    $('<div class="wx-period-mini">')
                        .append($('<span class="wx-period-label">').text('From'))
                        .append($('<span class="wx-period-val">').text(fromIso ? fmtPretty(fromIso) : '—'))
                );
                $dates.append(
                    $('<div class="wx-period-mini">')
                        .append($('<span class="wx-period-label">').text('To'))
                        .append($('<span class="wx-period-val">').text(toText))
                );
                $box.append($dates);
                if (fromIso && toEffIso) {
                    var $durRow = $('<div class="wx-period-duration">');
                    [
                        { n: yN, l: 'Years' },
                        { n: mN, l: 'Months' },
                        { n: dN, l: 'Days' }
                    ].forEach(function(item) {
                        $durRow.append(
                            $('<div class="wx-period-dur-cell">')
                                .append($('<span class="wx-period-dur-num">').text(String(item.n)))
                                .append($('<span class="wx-period-dur-lbl">').text(item.l))
                        );
                    });
                    $box.append($durRow);
                }
                $periodCell.append($box);
                // if (voltage === VOLTAGE_DISABLES_KVA) {
                //     $periodCell.append(
                //         $('<div class="text-danger small mt-1" style="font-weight:600;">')
                //             .text('Not counted toward experience (Up to 650V)')
                //     );
                // }

                /* Col 8 — Attachments */
                var $docInput = $tr.find('.work-doc-input');
                var $relInput = $tr.find('.work-relieve-input');
                var $attachCell = $str.find('.work-row-summary-attachments');
                $attachCell.empty();
                var $attachStack = $('<div class="wx-sum-attach-stack">');
                $attachStack.append(summaryAttachmentBlock('Supporting', $docInput, null, $tr));
                var relieveNa = isTill ? 'Not required (Till date)' : (isBoardMember ? 'Optional' : null);
                $attachStack.append(summaryAttachmentBlock('Relieving', $relInput, relieveNa, $tr));
                $attachCell.append($attachStack);
                applyFrozenSummaryActions($tr, $str);
            }

            function toggleRowExpanded($tr, expand) {
                if (isAlterationFrozenRow($tr)) return;
                if (!$tr.hasClass('is-complete')) return;
                var wasExpanded = $tr.hasClass('work-row--expanded');
                var shouldExpand = (typeof expand === 'boolean') ? expand : !wasExpanded;
                if (wasExpanded && !shouldExpand) {
                    collapseToSummary($tr);
                    return;
                }
                $tr.toggleClass('work-row--expanded', shouldExpand);
                applyRowLayout($tr);
                syncSummaryTable();
                $tr.find('.work-row-done-hint').remove();
                if (shouldExpand) {
                    ensureWorkRowDoneBar($tr);
                    applyEmploymentType($tr);
                    updateTotalYears($tr);
                    var $focus = $tr.find('.work-row-grid :input:not([type="hidden"]):not([readonly]):enabled').first();
                    if ($focus.length) $focus.trigger('focus');
                } else {
                    clearWorkRowDateRangeError($tr);
                }
            }

            /** Ensure the Submit bar exists (cloned / legacy rows can lose it). */
            function ensureWorkRowDoneBar($tr) {
                if (!$tr || !$tr.length) return $();
                var $bar = $tr.find('.work-row-done-bar').first();
                if ($bar.length) {
                    if (!$bar.find('.work-row-done-btn').length) {
                        $bar.html(
                            '<button type="button" class="work-row-done-btn" aria-label="Submit this entry and return to summary card">' +
                                '<i class="fa fa-check" aria-hidden="true"></i> Submit' +
                            '</button>'
                        );
                    }
                    return $bar;
                }
                if (!workContainerUsesSummaryPanel(workContainerFor($tr))) return $();
                $bar = $(
                    '<div class="work-row-done-bar">' +
                        '<button type="button" class="work-row-done-btn" aria-label="Submit this entry and return to summary card">' +
                            '<i class="fa fa-check" aria-hidden="true"></i> Submit' +
                        '</button>' +
                    '</div>'
                );
                var $grid = $tr.children('.work-row-grid').first();
                if ($grid.length) {
                    $grid.after($bar);
                } else {
                    $tr.append($bar);
                }
                return $bar;
            }

            /**
             * New complete rows auto-collapse into the summary grid.
             * Expanded complete rows (Edit) stay open until Submit / chevron.
             */
            function updateRowStatus($tr) {
                if (isAlterationFrozenRow($tr)) {
                    $tr.addClass('is-complete work-row--compact').removeClass('work-row--expanded');
                    $tr.data('wxWasComplete', true);
                    applyRowLayout($tr);
                    updateRowSummary($tr);
                    syncSummaryTable();
                    return;
                }
                var wasComplete = !!$tr.data('wxWasComplete');
                var complete = isRowComplete($tr);
                var useSummary = workContainerUsesSummaryPanel(workContainerFor($tr));
                $tr.toggleClass('is-complete', complete);
                if (complete) {
                    ensureWorkRowDoneBar($tr);
                    if (!useSummary) {
                        /* §7b-style: keep the inline form visible. */
                        $tr.addClass('work-row--expanded').removeClass('work-row--compact work-row--in-summary');
                    } else if (!wasComplete) {
                        /* First time complete → auto-collapse into summary table (unless dates overlap). */
                        if (isPreviousWorkPartContainer(workContainerFor($tr)) && workRowHasDateSequenceError($tr)) {
                            validateWorkRowDateRange($tr);
                            $tr.addClass('work-row--expanded').removeClass('work-row--compact work-row--in-summary');
                        } else {
                            $tr.removeClass('work-row--expanded');
                        }
                    } else if ($tr.hasClass('work-row--expanded')) {
                        /* Editing a complete row — keep Submit available. */
                        $tr.removeClass('work-row--compact work-row--in-summary');
                    }
                } else {
                    $tr.removeClass('work-row--expanded');
                }
                $tr.data('wxWasComplete', complete);
                applyRowLayout($tr);
                updateRowSummary($tr);
                syncSummaryTable();
                updateWorkAddBtn();
            }

            function hasExistingWorkDoc($row, kind) {
                if (!$row || !$row.length) return false;
                var isRelieve = kind === 'relieve';
                var path = namedInputVal($row, isRelieve
                    ? 'existing_work_relieving_document[]'
                    : 'existing_work_document[]');
                var removed = namedInputVal($row, isRelieve
                    ? 'removed_document_work_relieving[]'
                    : 'removed_document_work[]') === '1';
                return path !== '' && !removed;
            }

            function workInputHasFile($input) {
                if (!$input || !$input.length) return false;
                var $row = $input.closest('.work-fields');
                if ($input.hasClass('work-doc-input') && hasExistingWorkDoc($row, 'support')) return true;
                if ($input.hasClass('work-relieve-input') && hasExistingWorkDoc($row, 'relieve')) return true;
                var el = $input[0];
                if (el && el.files && el.files.length) return true;
                if ($input.attr('data-has-local-file') === '1') return true;
                var $field = $input.closest('.work-card-field');
                if ($field.find('.local-file-preview .preview-link, .work-doc-existing a, .work-relieve-existing a').length) {
                    return true;
                }
                var $wrap = $input.closest('.form-s-file-upload-wrap');
                if ($wrap.length && $wrap.nextAll('.local-file-preview').first().find('.preview-link').length) return true;
                return String($input.val() || '').trim() !== '';
            }

            function isRowComplete($tr) {
                var type = ($tr.find('.work-employment-type').val() || '').trim();
                if (!type) return false;
                var isBoardMember = (type === BOARD_MEMBER_TYPE);
                var isCurrentPart = $tr.closest('.js-work-container[data-work-part="current"], #work-container-current').length > 0;
                /* Every enabled required text/select/textarea must be filled. */
                var ok = true;
                $tr.find('select[required], input[type="text"][required], input[type="number"][required], textarea[required]').each(function() {
                    if ($(this).prop('disabled')) return;
                    if (($(this).val() || '').trim() === '') { ok = false; return false; }
                });
                if (!isCurrentPart) {
                    if (!readWorkDateFromInput($tr.find('.work-date-from'))) ok = false;
                    if (!$tr.find('.work-date-till').is(':checked') && !$tr.find('.work-date-to').prop('disabled')
                        && !readWorkDateFromInput($tr.find('.work-date-to'))) ok = false;
                }
                if (!ok) return false;
                /* Explicit field checks (required attr may be missing on some clones). */
                if (!isBoardMember) {
                    if (!($tr.find('.work-employer-input').val() || '').trim()) return false;
                    if (!($tr.find('.work-org-address').val() || '').trim()) return false;
                    if (!($tr.find('.work-designation').val() || '').trim()) return false;
                    if (!WX_HIDE_VOLTAGE_FIELDS) {
                        if (!($tr.find('.work-nature').val() || '').trim()) return false;
                        if (!($tr.find('.work-voltage').val() || '').trim()) return false;
                        var voltage = ($tr.find('.work-voltage').val() || '').trim();
                        var $kva = $tr.find('.work-transformer-kva');
                        if (!$kva.prop('disabled') && voltage !== VOLTAGE_DISABLES_KVA && ($kva.val() || '').trim() === '') return false;
                    }
                    if (type === CONTRACTOR_TYPE) {
                        if (!($tr.find('.work-contractor-cat').val() || '').trim()) return false;
                        if (!String($tr.find('.work-licence-number').val() || '').replace(/\D+/g, '')) return false;
                    }
                } else {
                    if (!($tr.find('.work-board-meeting-details').val() || '').trim()) return false;
                    if (!readWorkDateFromInput($tr.find('.work-board-meeting-date'))) return false;
                    if (!($tr.find('.work-employer-input').val() || '').trim()) return false;
                }
                var $doc = $tr.find('.work-doc-input').first();
                if (!isCurrentPart && !workInputHasFile($doc)) return false;
                var till = $tr.find('.work-date-till').is(':checked');
                if (!till && !isBoardMember && !isCurrentPart) {
                    var $rel = $tr.find('.work-relieve-input').first();
                    if (!workInputHasFile($rel)) return false;
                }
                return true;
            }

            /** True when every row in the container is filled — required before adding another. */
            function workContainerCanAddRow($container) {
                if (!$container || !$container.length) return false;
                var canAdd = true;
                $container.find('.work-fields').each(function() {
                    var $row = $(this);
                    /* Locked existing renew/alteration rows never block Add. */
                    if (isAlterationFrozenRow($row)) return;
                    if (!isRowComplete($row) || workRowHasDateSequenceError($row)) {
                        canAdd = false;
                        return false;
                    }
                });
                return canAdd;
            }

            function updateTotalYears($tr) {
                var fromStr = readWorkDateFromInput($tr.find('.work-date-from'));
                var toStr   = effectiveToStr($tr);
                var done = function() {
                    syncLegacyHidden($tr);
                    updateOverallTotalYears();
                    updateRowHeader($tr);
                };
                if (!fromStr || !toStr || !workDateYearIsPlausible(fromStr) || !workDateYearIsPlausible(toStr)) {
                    clearWorkDuration($tr);
                    validateWorkContainerDateSequence(workContainerFor($tr));
                    done();
                    return;
                }
                var from = new Date(fromStr + 'T12:00:00'), to = new Date(toStr + 'T12:00:00');
                if (isNaN(from.getTime()) || isNaN(to.getTime())) {
                    clearWorkDuration($tr);
                    validateWorkContainerDateSequence(workContainerFor($tr));
                    done();
                    return;
                }
                if (to < from) {
                    clearWorkDuration($tr);
                    validateWorkContainerDateSequence(workContainerFor($tr));
                    done();
                    return;
                }
                var diff = calendarDiffYMD(from, to);
                if (!diff) { clearWorkDuration($tr); done(); return; }
                $tr.find('.work-duration-y').val(String(diff.y));
                $tr.find('.work-duration-m').val(String(diff.m));
                $tr.find('.work-duration-d').val(String(diff.d));
                var yearsDec = ((to - from) / 86400000 + 1) / 365.25;
                var rounded = Math.round(yearsDec * 10) / 10;
                $tr.find('.work-experience-total-hidden').val(rounded.toFixed(1));
                validateWorkContainerDateSequence(workContainerFor($tr));
                done();
            }

            /**
             * Lock To-date / relieving when "Till date" is checked.
             * keepSavedToDate: on edit load, keep the stored To date; on user check, fill today.
             */
            function applyTillDate($tr, keepSavedToDate) {
                var $till = $tr.find('.work-date-till');
                var checked = $till.is(':checked');
                // Previous: posted today's date (Y-m-d) in the hidden field.
                // $tr.find('.work-date-till-hidden').val(checked ? todayIso() : '0');
                $tr.find('.work-date-till-hidden').val(checked ? '1' : '0');

                var $toDate = $tr.find('.work-date-to');
                if (checked) {
                    var hasSavedToDate = !!keepSavedToDate && (($toDate.val() || '').trim() !== '');
                    if (!hasSavedToDate) {
                        var today = todayIso();
                        var node = $toDate.get(0);
                        if (node) {
                            node.setAttribute('data-raw', today);
                            if (node.type === 'date') {
                                $toDate.val(today);
                            } else {
                                var parts = today.split('-');
                                $toDate.val(parts[2] + '-' + parts[1] + '-' + parts[0]);
                            }
                        }
                    }
                    /* readonly so the value is still posted (disabled inputs are omitted). */
                    $toDate.prop('readonly', true).prop('disabled', false).prop('required', false);
                } else {
                    $toDate.prop('readonly', false).prop('disabled', false);
                    // Required-state for $toDate is re-evaluated by applyEmploymentType.
                }
                setFieldLock($tr, 'to-date', checked);

                var $relieve = $tr.find('.work-relieve-input');
                if (checked) {
                    $relieve.val('').prop('disabled', true).prop('required', false).addClass('is-locked');
                    var $wrap = $relieve.closest('.form-s-file-upload-wrap');
                    var $preview = $wrap.nextAll('.local-file-preview').first();
                    if ($preview.length) {
                        var blobUrl = $preview.data('blobUrl');
                        if (blobUrl) { try { URL.revokeObjectURL(blobUrl); } catch(e) {} }
                        $preview.remove();
                    }
                    $relieve.removeAttr('data-has-local-file');
                } else {
                    $relieve.prop('disabled', false).removeClass('is-locked');
                    // Required-state for $relieve is re-evaluated by applyEmploymentType.
                }
                setFieldLock($tr, 'relieve', checked);
                updateTotalYears($tr);
                updateRowHeader($tr);
            }

            /** Keep hidden work_transformer_kva[] in sync (disabled <select> is not posted). */
            function syncTransformerKvaHidden($tr) {
                if (!$tr || !$tr.length) return;
                var $kva = $tr.find('select.work-transformer-kva').first();
                var $sync = $tr.find('.work-transformer-kva-sync').first();
                if (!$sync.length) return;
                if ($sync.prop('disabled')) return;
                var locked = !!$kva.prop('disabled')
                    || (($tr.find('.work-voltage').val() || '').trim() === VOLTAGE_DISABLES_KVA)
                    || (($tr.find('.work-employment-type').val() || '').trim() === BOARD_MEMBER_TYPE);
                var val = locked ? '' : (($kva.val() || '').trim());
                $sync.val(val);
            }

            /** Keep Grade of Licence / Licence No posted for every row (disabled UI fields are omitted by FormData). */
            function syncContractorFieldsHidden($tr) {
                if (!$tr || !$tr.length) return;
                var $catSync = $tr.find('.work-contractor-category-sync').first();
                var $licSync = $tr.find('.work-licence-number-sync').first();
                var isContractor = (($tr.find('.work-employment-type').val() || '').trim() === CONTRACTOR_TYPE);
                var cat = '';
                var lic = '';
                if (isContractor) {
                    cat = ($tr.find('.work-contractor-cat').val() || '').trim();
                    lic = String($tr.find('.work-licence-number').val() || '').replace(/\D+/g, '');
                }
                if ($catSync.length && !$catSync.prop('disabled')) {
                    $catSync.val(cat);
                }
                if ($licSync.length && !$licSync.prop('disabled')) {
                    $licSync.val(lic);
                }
            }

            /** Lock or unlock the row's Transformer-kVA input based on the voltage dropdown. */
            function applyVoltage($tr) {
                var v = ($tr.find('.work-voltage').val() || '').trim();
                var $kva = $tr.find('select.work-transformer-kva');
                var locked = (v === VOLTAGE_DISABLES_KVA);
                if (locked) {
                    $kva.val('').prop('disabled', true).prop('required', false);
                    $kva.nextAll('.error-message').remove();
                    $tr.find('.work-card-field[data-field="transformer-kva"] .error-message').remove();
                } else {
                    $kva.prop('disabled', false);
                    // Required-state is re-evaluated by applyEmploymentType (only required when a type is chosen).
                }
                setFieldLock($tr, 'transformer-kva', locked);
                $tr.find('.work-card-field-hint[data-hint="duration-650v"]').toggle(locked);
                syncTransformerKvaHidden($tr);
            }

            /** Drive every column's enable / required state from the Employment Type. */
            function applyEmploymentType($tr) {
                var t = ($tr.find('.work-employment-type').val() || '').trim();
                var hasType = t !== '';
                var isContractor = (t === CONTRACTOR_TYPE);
                var isBoardMember = (t === BOARD_MEMBER_TYPE);

                var $cat = $tr.find('.work-contractor-cat');
                var $lic = $tr.find('.work-licence-number');
                var $emp = $tr.find('.work-employer-input');
                var $addr = $tr.find('.work-org-address');
                var $des = $tr.find('.work-designation');
                var $nat = $tr.find('.work-nature');
                var $volt = $tr.find('.work-voltage');
                var $kva = $tr.find('.work-transformer-kva');
                var $yFrom = $tr.find('.work-date-from');
                var $yTo = $tr.find('.work-date-to');
                var $till = $tr.find('.work-date-till');
                var $doc = $tr.find('.work-doc-input');
                var $rel = $tr.find('.work-relieve-input');

                if (!hasType) {
                    /* No type selected → blank every column 3–13. */
                    $cat.val('').prop('disabled', true).prop('required', false);
                    $lic.val('').prop('disabled', true).prop('required', false);
                    $emp.val('').prop('disabled', true).prop('required', false);
                    $addr.val('').prop('disabled', true).prop('required', false);
                    $des.val('').prop('disabled', true).prop('required', false);
                    $nat.val('').prop('disabled', true).prop('required', false);
                    $volt.val('').prop('disabled', true).prop('required', false);
                    $kva.val('').prop('disabled', true).prop('required', false);
                    $yFrom.val('').prop('disabled', true).prop('required', false);
                    $yTo.val('').prop('disabled', true).prop('required', false);
                    $till.prop('checked', false).prop('disabled', true);
                    $tr.find('.work-date-till-hidden').val('0');
                    $doc.val('').prop('disabled', true).prop('required', false).removeAttr('data-has-local-file').addClass('is-locked');
                    $rel.val('').prop('disabled', true).prop('required', false).removeAttr('data-has-local-file').addClass('is-locked');
                    $tr.find('[data-field="relieve"] .work-card-field-hint[data-hint="relieve-board"]').hide();
                    setFieldLock($tr, 'contractor-cat', false);
                    setFieldLock($tr, 'licence-number', false);
                    setFieldLock($tr, 'transformer-kva', false);
                    setFieldLock($tr, 'to-date', false);
                    setFieldLock($tr, 'relieve', false);
                    toggleBoardMeetingFields($tr, false);
                    syncTransformerKvaHidden($tr);
                    syncContractorFieldsHidden($tr);
                    /* Clear any blob previews left over from a previous selection. */
                    $tr.find('.local-file-preview').each(function() {
                        var $p = $(this);
                        var url = $p.data('blobUrl');
                        if (url) { try { URL.revokeObjectURL(url); } catch(e) {} }
                        $p.remove();
                    });
                    clearWorkDuration($tr);
                    syncLegacyHidden($tr);
                    updateOverallTotalYears();
                    updateRowHeader($tr);
                    return;
                }

                if (isBoardMember) {
                    applyBoardMemberEmployment($tr);
                    updateTotalYears($tr);
                    syncLegacyHidden($tr);
                    updateRowHeader($tr);
                    if (typeof window.wxSyncBoardMemberRenewalFee === 'function') {
                        window.wxSyncBoardMemberRenewalFee();
                    }
                    return;
                }

                $tr.find('[data-field="work-nature"] .req, [data-field="voltage-level"] .req, [data-field="transformer-kva"] .req').show();
                $tr.find('[data-field="relieve"] .work-card-field-label .req').show();
                $tr.find('[data-field="relieve"] .work-card-field-hint[data-hint="relieve-board"]').hide();
                toggleBoardMeetingFields($tr, false);
                /* Cols 3 & 4 — Contractor only. */
                if (isContractor) {
                    $cat.prop('disabled', false).prop('required', true);
                    $lic.prop('disabled', false).prop('required', true);
                    setFieldLock($tr, 'contractor-cat', false);
                    setFieldLock($tr, 'licence-number', false);
                } else {
                    $cat.val('').prop('disabled', true).prop('required', false);
                    $lic.val('').prop('disabled', true).prop('required', false);
                    setFieldLock($tr, 'contractor-cat', true);
                    setFieldLock($tr, 'licence-number', true);
                }

                /* Cols 5–9, 11–12 — required for every chosen type. */
                $emp.prop('disabled', false).prop('required', true);
                $addr.prop('disabled', false).prop('required', true);
                $des.prop('disabled', false).prop('required', true);
                $nat.prop('disabled', false).prop('required', true);
                $volt.prop('disabled', false).prop('required', true);
                setFieldLock($tr, 'work-nature', false);
                setFieldLock($tr, 'voltage-level', false);
                $yFrom.prop('disabled', false).prop('required', true);
                $till.prop('disabled', false);
                $doc.prop('disabled', false).prop('required', true).removeClass('is-locked');

                /* Col 10 — kVA is conditional on voltage; let applyVoltage finalise it. */
                $volt.prop('disabled', false);
                applyVoltage($tr);
                /* When voltage allows kVA, make it required. */
                if (!$kva.prop('disabled')) $kva.prop('required', true);
                syncTransformerKvaHidden($tr);
                syncContractorFieldsHidden($tr);

                /* Col 11.To & Col 13 — conditional on Till date. */
                applyTillDate($tr, true);
                if (!$yTo.prop('disabled') && !$yTo.prop('readonly')) $yTo.prop('required', true);
                if (!$rel.prop('disabled')) $rel.prop('required', false);
                /* Note: supporting doc (col 12) AND relieving letter (col 13) are validated
                   at submit-time (file OR existing path); we don't mark them HTML5-required
                   because that breaks the cross-row "till date" logic. */

                updateTotalYears($tr);
                syncLegacyHidden($tr);
                updateRowHeader($tr);
            }

            function initWorkRow($tr) {
                if (isAlterationFrozenRow($tr)) {
                    /* Renewal/alteration existing rows stay read-only. */
                    syncTransformerKvaHidden($tr);
                    syncContractorFieldsHidden($tr);
                    return;
                }
                if ($tr.closest('#work-container-current, .js-work-container[data-work-part="current"]').length) {
                    ensure7bRepresentingOrgSelect($tr);
                }
                applyEmploymentType($tr);
                syncTransformerKvaHidden($tr);
                syncContractorFieldsHidden($tr);
            }

            function refreshWorkSerials() {
                workContainers().each(function() {
                    $(this).find('.work-fields').each(function(idx) {
                        var n = idx + 1;
                        var $row = $(this);
                        $row.attr('data-row-index', idx);
                        var $str = $row.data('wxSummaryTr');
                        if ($str && $str.length) $str.find('.work-row-summary-sno').text(n);
                    });
                    syncWorkDateSequenceBounds($(this));
                });
                updateWorkAddBtn();
            }

            function showSectionError(msg) {
                $('.work-error').remove();
                var $bar = $('#work-exp-section-bar, .work-exp-wrap .work-exp-section-bar').first();
                var $err = $('<div class="text-danger small mt-1 work-error" role="alert">' + msg + '</div>');
                if ($bar.length) $bar.after($err); else $('#work-container').before($err);
                setTimeout(function() { $('.work-error').fadeOut(400, function() { $(this).remove(); }); }, 5000);
            }

            function editRowHasSavedData($row) {
                return namedInputVal($row, 'work_id[]') !== ''
                    || (
                        !!($row.find('.work-employment-type').val() || '').trim()
                        && !!($row.find('.work-employer-input').val() || '').trim()
                    );
            }

            /** DB-saved rows: show in summary table only (card hidden until Edit). */
            function hydrateStoredWorkRow($row) {
                var workId = namedInputVal($row, 'work_id[]');
                if (!workId) return false;
                initWorkRow($row);
                updateTotalYears($row);
                updateRowStatus($row);
                if (!$row.hasClass('is-complete')) {
                    $row.addClass('is-complete');
                }
                var $container = workContainerFor($row);
                var hasSummaryTable = summaryTbodyFor($container).length > 0;
                if (hasSummaryTable) {
                    $row.removeClass('work-row--expanded');
                } else {
                    $row.addClass('work-row--expanded').removeClass('work-row--compact work-row--in-summary');
                }
                applyRowLayout($row);
                updateRowSummary($row);
                syncSummaryTable();
                return true;
            }

            window.wxUpdateOverallWorkYears = updateOverallTotalYears;
            window.wxValidateWorkRowDateRange = validateWorkRowDateRange;
            window.wxValidateFormSExperienceDateSequence = function () {
                var result = { ok: true };
                workContainers().each(function () {
                    var check = validateWorkContainerDateSequence($(this));
                    if (!check.ok && result.ok) {
                        result = check;
                    }
                });
                if (!result.ok && result.$row && result.$row.length) {
                    expandWorkRowForDateError(result.$row);
                }
                return result;
            };
            window.wxRecalcWorkDuration = function($row) {
                if ($row && $row.length) {
                    updateTotalYears($row);
                } else {
                    updateOverallTotalYears();
                }
            };

            $(document).ready(function() {
                workContainers().each(function() {
                    $(this).children('.work-fields, .work-entry-block').each(function() {
                        var $row = $(this).hasClass('work-fields') ? $(this) : $(this).find('.work-fields').first();
                        if ($row.length) ensureWorkEntryBlock($row);
                    });
                });
                ensureExistingWorkDocViewLinks();
                allWorkFields().each(function() {
                    var $row = $(this);
                    initWorkRow($row);
                    updateTotalYears($row);
                    if (isAlterationFrozenRow($row)) {
                        $row.addClass('is-complete work-row--compact').removeClass('work-row--expanded');
                        updateRowSummary($row);
                        applyFrozenSummaryActions($row, getSummaryTr($row));
                        return;
                    }
                    if (hydrateStoredWorkRow($row)) {
                        return;
                    }
                    if (editRowHasSavedData($row)) {
                        collapseToSummary($row);
                    }
                });
                refreshWorkSerials();
                syncSummaryTable();
                updateOverallTotalYears();
                if (typeof window.wxSyncBoardMemberRenewalFee === 'function') {
                    window.wxSyncBoardMemberRenewalFee();
                }
                /* Prefill §7b meeting/org dropdowns when editing an existing board row. */
                $('#work-container-current .work-fields, .js-work-container[data-work-part="current"] .work-fields').each(function () {
                    var $row = $(this);
                    var $date = $row.find('.work-board-meeting-date').first();
                    if (!$date.length) return;
                    var dateIso = '';
                    if (typeof readWorkDateFromInput === 'function') {
                        dateIso = readWorkDateFromInput($date);
                    }
                    if (!dateIso) dateIso = String($date.val() || '').trim();
                    if (dateIso) $date.attr('data-raw', dateIso);
                    var preferredNo = format7bBmNo($row.find('.work-board-meeting-details').first().val());
                    var preferredOrg = String($row.find('.work-employer-input').first().val() || '').trim();
                    if (dateIso) {
                        fill7bMeetingDetailsForDate($row, dateIso, preferredNo, preferredOrg);
                    } else {
                        fill7bOrgOptionsForDate($row, '', '', false);
                    }
                });
            });

            function revealWorkUploadAfterRemove($row, kind) {
                if (!hideUploadWhenDocExists) return;
                var selector = kind === 'relieve' ? '.work-relieve-input' : '.work-doc-input';
                var $file = $row.find(selector);
                $file.closest('.form-s-file-upload-wrap').removeClass('d-none work-upload-hidden-until-remove');
                $file.prop('disabled', false);
                var $field = $file.closest('.work-card-field');
                $field.find('.work-upload-hint-hidden-until-remove').removeClass('d-none');
            }

            $(document).on('click', '.remove-work-doc-confirm', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $row = $workRow($btn);
                if (isAlterationFrozenRow($row)) return;
                Swal.fire({
                    title: 'Do you want to remove the document?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    reverseButtons: true
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    var $row = $workRow($btn);
                    $btn.closest('.work-doc-existing').remove();
                    namedInputs($row, 'existing_work_document[]').val('');
                    namedInputs($row, 'removed_document_work[]').val('1');
                    var $file = $row.find('.work-doc-input');
                    clearWorkLocalFilePreview($file);
                    $file.val('').removeAttr('data-has-local-file');
                    revealWorkUploadAfterRemove($row, 'support');
                    updateRowStatus($row);
                });
            });

            $(document).on('click', '.remove-work-relieve-confirm', function(e) {
                e.preventDefault();
                var $btn = $(this);
                var $row = $workRow($btn);
                if (isAlterationFrozenRow($row)) return;
                Swal.fire({
                    title: 'Do you want to remove the document?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    reverseButtons: true
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    var $row = $workRow($btn);
                    $btn.closest('.work-relieve-existing').remove();
                    namedInputs($row, 'existing_work_relieving_document[]').val('');
                    namedInputs($row, 'removed_document_work_relieving[]').val('1');
                    var $file = $row.find('.work-relieve-input');
                    clearWorkLocalFilePreview($file);
                    $file.val('').removeAttr('data-has-local-file');
                    revealWorkUploadAfterRemove($row, 'relieve');
                    updateRowStatus($row);
                });
            });

            /* Type / voltage / till-date drive all the conditional locks. */
            $(document).on('input', '.js-work-container .work-licence-number, #work-container .work-licence-number', function() {
                var $el = $(this);
                var cleaned = String($el.val() || '').replace(/\D+/g, '');
                if ($el.val() !== cleaned) {
                    $el.val(cleaned);
                }
                syncContractorFieldsHidden($workRow(this));
            });
            $(document).on('change', '.js-work-container .work-contractor-cat, #work-container .work-contractor-cat', function() {
                syncContractorFieldsHidden($workRow(this));
            });
            $(document).on('change', '.js-work-container .work-employment-type, #work-container .work-employment-type', function() {
                applyEmploymentType($workRow(this));
                if (typeof window.wxSyncBoardMemberRenewalFee === 'function') {
                    window.wxSyncBoardMemberRenewalFee();
                }
            });
            $(document).on('change', '.js-work-container .work-date-till, #work-container .work-date-till', function() {
                var $tr = $workRow(this);
                applyTillDate($tr);
                if (($tr.find('.work-employment-type').val() || '').trim() === BOARD_MEMBER_TYPE && !$tr.find('.work-date-till').is(':checked')) {
                    unlockWorkField($tr, $tr.find('.work-relieve-input'), 'relieve');
                    $tr.find('.work-relieve-input').prop('required', false);
                    $tr.find('[data-field="relieve"] .work-card-field-label .req').hide();
                }
            });
            $(document).on('change', '.js-work-container .work-voltage, #work-container .work-voltage', function() {
                var $tr = $workRow(this);
                applyVoltage($tr);
                if (!$tr.find('select.work-transformer-kva').prop('disabled')) {
                    $tr.find('select.work-transformer-kva').prop('required', true);
                } else {
                    $tr.find('select.work-transformer-kva').prop('required', false);
                }
                $tr.find('select.work-transformer-kva').nextAll('.error-message').remove();
                $tr.find('.work-card-field[data-field="transformer-kva"] .error-message').remove();
                updateRowHeader($tr);
                /* Up to 650V rows are excluded from the combined experience total. */
                updateOverallTotalYears();
            });
            $(document).on('change', '.js-work-container select.work-transformer-kva, #work-container select.work-transformer-kva', function() {
                syncTransformerKvaHidden($workRow(this));
                updateRowHeader($workRow(this));
            });
            $(document).on('input', '.js-work-container .work-date-from, .js-work-container .work-date-to, #work-container .work-date-from, #work-container .work-date-to', function() {
                var $field = $(this);
                clampWorkDateYearDigits($field.get(0));
                syncWorkDateRaw($field);
                clearWorkDateFieldErrors($field);
                updateTotalYears($workRow(this));
            });
            $(document).on('change blur', '.js-work-container .work-date-from, .js-work-container .work-date-to, #work-container .work-date-from, #work-container .work-date-to', function() {
                var $field = $(this);
                clampWorkDateYearDigits($field.get(0));
                syncWorkDateRaw($field);
                clearWorkDateFieldErrors($field);
                var $tr = $workRow(this);
                updateTotalYears($tr);
                validateWorkContainerDateSequence(workContainerFor($tr));
            });
            /* Any field change refreshes the live row header + status pill. */
            $(document).on('input change', '.js-work-container .work-employer-input, #work-container .work-employer-input', function() {
                var $tr = $workRow(this);
                syncLegacyHidden($tr); updateRowHeader($tr);
            });
            $(document).on('input change', '.js-work-container .work-fields :input, #work-container .work-fields :input', function() {
                var $tr = $workRow(this);
                updateRowStatus($tr);
            });
            $(document).on('wx:recheck-complete', '.js-work-container .work-fields, #work-container .work-fields', function() {
                updateRowStatus($(this));
            });
            function clearWorkLocalFilePreview($input) {
                if (!$input || !$input.length) return;
                var $wrap = $input.closest('.form-s-file-upload-wrap');
                var $preview = $wrap.length
                    ? $wrap.nextAll('.local-file-preview').first()
                    : $input.closest('.work-card-field').find('.local-file-preview').first();
                if ($preview.length) {
                    var blobUrl = $preview.data('blobUrl');
                    if (blobUrl) {
                        try { URL.revokeObjectURL(blobUrl); } catch (e) {}
                    }
                    $preview.remove();
                }
                $input.removeAttr('data-has-local-file');
            }

            function ensureExistingWorkDocViewLinks($scope) {
                var $rows = $scope && $scope.length
                    ? $scope
                    : $('.js-work-container .work-fields, #work-container .work-fields');
                $rows.each(function () {
                    var $row = $(this);
                    [
                        {
                            existingName: 'existing_work_document[]',
                            fieldSel: '[data-field="support-doc"]',
                            existingClass: 'work-doc-existing',
                            removeClass: 'remove-work-doc-confirm',
                            docKind: 'support',
                            label: 'View Document'
                        },
                        {
                            existingName: 'existing_work_relieving_document[]',
                            fieldSel: '[data-field="relieve"]',
                            existingClass: 'work-relieve-existing',
                            removeClass: 'remove-work-relieve-confirm',
                            docKind: 'relieve',
                            label: 'View Document'
                        }
                    ].forEach(function (cfg) {
                        var path = namedInputVal($row, cfg.existingName);
                        if (!path) return;
                        var $field = $row.find(cfg.fieldSel).first();
                        if (!$field.length || $field.find('.' + cfg.existingClass).length) return;
                        var href = competencyStoredDocHref(path);
                        if (!href) return;
                        var $box = $('<div class="' + cfg.existingClass + ' mb-1"></div>');
                        $box.append(
                            $('<a class="text-primary" target="_blank" rel="noopener"></a>')
                                .attr('href', href)
                                .html('<i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> ' + cfg.label)
                        );
                        $box.append(
                            $('<button type="button" class="btn btn-sm btn-danger ml-1"></button>')
                                .addClass(cfg.removeClass)
                                .attr('data-doc-kind', cfg.docKind)
                                .text('Remove')
                        );
                        var $wrap = $field.find('.form-s-file-upload-wrap').first();
                        if ($wrap.length) {
                            $wrap.before($box);
                        } else {
                            $field.find('.work-card-field-label').first().after($box);
                        }
                        if (hideUploadWhenDocExists) {
                            $wrap.addClass('d-none work-upload-hidden-until-remove');
                            $field.find('.work-upload-hint-hidden-until-remove, .work-card-field-hint').first().addClass('d-none');
                        }
                    });
                });
            }

            /* File-input change also affects "Complete" pill and summary attachments.
               View Document preview is owned by the page-level handler (same as edit_application). */
            $(document).on('change', '.js-work-container .work-doc-input, .js-work-container .work-relieve-input, #work-container .work-doc-input, #work-container .work-relieve-input', function() {
                var $tr = $workRow(this);
                clearWorkRowUploadErrors($tr);
                updateRowStatus($tr);
                /* Preview handlers (page-level) run in parallel; refresh summary once they finish. */
                setTimeout(function() {
                    if ($tr.hasClass('is-complete')) {
                        updateRowSummary($tr);
                        syncSummaryTable();
                    }
                }, 0);
            });

            $(document).on('click', '.work-exp-summary-panel .work-row-edit-trigger', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $wf = workRowFromSummaryTr($(this).closest('.work-exp-summary-tr'));
                if ($wf && $wf.length && isAlterationFrozenRow($wf)) return;
                if ($wf && $wf.length) toggleRowExpanded($wf, true);
            });

            /* Click compact summary header (or chevron) to expand/collapse for editing. */
            $(document).on('click', '.js-work-container .work-row-head, #work-container .work-row-head', function(e) {
                if ($(e.target).closest('.work-row-remove, .remove-work').length) return;
                var $tr = $workRow(this);
                if (isAlterationFrozenRow($tr)) return;
                if (!$tr.hasClass('is-complete')) return;
                toggleRowExpanded($tr);
            });
            $(document).on('click', '.js-work-container .work-row-edit-trigger, #work-container .work-row-edit-trigger', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $tr = $workRow(this);
                if (isAlterationFrozenRow($tr)) return;
                toggleRowExpanded($tr, true);
            });
            $(document).on('click', '.js-work-container .work-row-toggle-btn, #work-container .work-row-toggle-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var $tr = $workRow(this);
                if (isAlterationFrozenRow($tr)) return;
                toggleRowExpanded($tr);
            });
            $(document).on('click', '.js-work-container .work-row-done-btn, #work-container .work-row-done-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                collapseToSummary($workRow(this));
            });

            /* Add / remove handlers — bound via delegation so cloned rows keep working. */
            document.addEventListener('click', function(e) {
                var addBtn = e.target.closest('.add-more-work');
                if (addBtn) {
                    e.preventDefault();
                    if (isAlterationWorkAddLocked()) {
                        return;
                    }
                    var containerId = addBtn.getAttribute('data-work-container');
                    var container = containerId ? document.getElementById(containerId) : document.getElementById('work-container');
                    if (!container) return;
                    var maxRows = parseInt(addBtn.getAttribute('data-max-rows'), 10) || MAX_WORK_ROWS;
                    var workRows = container.querySelectorAll('.work-fields');
                    if (workRows.length >= maxRows) {
                        showSectionError('You can add a maximum of ' + maxRows + ' work experience entries.');
                        return;
                    }
                    if (!workContainerCanAddRow($(container))) {
                        showSectionError('Complete the current work experience entry before adding another row.');
                        return;
                    }
                    var firstBlock = container.querySelector('.work-entry-block');
                    var first = container.querySelector('.work-fields');
                    if (!first) {
                        var $fallback = workContainers().not(container).find('.work-fields').first();
                        if (!$fallback.length) return;
                        first = $fallback[0];
                        firstBlock = first.closest('.work-entry-block');
                    }
                    var newRoot = firstBlock ? firstBlock.cloneNode(true) : first.cloneNode(true);
                    var newRow = newRoot.querySelector ? newRoot.querySelector('.work-fields') : newRoot;
                    if (!newRow && newRoot.classList && newRoot.classList.contains('work-fields')) {
                        newRow = newRoot;
                    }
                    if (!newRow) return;
                    var isCurrent = (container.getAttribute('data-work-part') || '') === 'current';
                    /* Cloned rows must not inherit locked existing state (alteration / renewal). */
                    newRow.classList.remove('fs-alt-existing-work');
                    if (newRoot && newRoot.classList) {
                        newRoot.classList.remove('fs-alt-existing-block');
                    }
                    var altExistingFlag = newRow.querySelector('input[name="fs_alt_existing_work[]"]');
                    if (altExistingFlag) altExistingFlag.remove();
                    /* Blank the clone before appending. */
                    newRow.classList.remove('is-collapsed', 'is-complete', 'work-row--compact', 'work-row--expanded', 'work-row--in-summary');
                    $(newRow).removeData('wxSummaryTr wxWasComplete');
                    newRow.querySelectorAll('input[type="file"]').forEach(function(el) { el.value = ''; el.removeAttribute('data-has-local-file'); });
                    newRow.querySelectorAll('.local-file-preview').forEach(function(preview) {
                        var blobUrl = preview.dataset ? preview.dataset.blobUrl : '';
                        if (blobUrl) { try { URL.revokeObjectURL(blobUrl); } catch(err) {} }
                        preview.remove();
                    });
                    resetWorkRowDateInputs(newRow);
                    newRow.querySelectorAll('input[type="text"], input[type="date"], input[type="number"]').forEach(function(inp) {
                        if (inp.classList.contains('work-date-from') || inp.classList.contains('work-date-to')) return;
                        inp.value = '';
                    });
                    newRow.querySelectorAll('.work-contractor-category-sync, .work-licence-number-sync, .work-transformer-kva-sync').forEach(function(inp) {
                        inp.value = '';
                        inp.removeAttribute('disabled');
                    });
                    newRow.querySelectorAll('select').forEach(function(sel) { sel.selectedIndex = 0; });
                    newRow.querySelectorAll('textarea').forEach(function(el) { el.value = ''; });
                    var boardPanel = newRow.querySelector('.work-board-member-panel');
                    if (boardPanel) boardPanel.style.display = 'none';
                    newRow.classList.remove('work-row--board-member');
                    var till = newRow.querySelector('.work-date-till');
                    if (till) till.checked = isCurrent;
                    var tillH = newRow.querySelector('.work-date-till-hidden');
                    // Previous: if (tillH) tillH.value = isCurrent ? todayIso() : '0';
                    if (tillH) tillH.value = isCurrent ? '1' : '0';
                    newRow.querySelectorAll('.work-duration-y, .work-duration-m, .work-duration-d').forEach(function(inp) { inp.value = ''; });
                    var kvaSync = newRow.querySelector('.work-transformer-kva-sync');
                    if (kvaSync) kvaSync.value = '';
                    var hTot = newRow.querySelector('.work-experience-total-hidden'); if (hTot) hTot.value = '';
                    var hLevel = newRow.querySelector('.work-level-sync'); if (hLevel) hLevel.value = '';
                    var hEx = newRow.querySelector('.experience-sync'); if (hEx) hEx.value = '';
                    var workId = newRow.querySelector('input[name="work_id[]"]'); if (workId) workId.value = '';
                    var existingDoc = newRow.querySelector('input[name="existing_work_document[]"]'); if (existingDoc) existingDoc.value = '';
                    var existingRel = newRow.querySelector('input[name="existing_work_relieving_document[]"]'); if (existingRel) existingRel.value = '';
                    var removedDoc = newRow.querySelector('input[name="removed_document_work[]"]'); if (removedDoc) removedDoc.value = '0';
                    var removedRel = newRow.querySelector('input[name="removed_document_work_relieving[]"]'); if (removedRel) removedRel.value = '0';
                    newRow.querySelectorAll('.work-doc-existing, .work-relieve-existing').forEach(function(el) { el.remove(); });
                    if (hideUploadWhenDocExists) {
                        newRow.querySelectorAll('.work-upload-hidden-until-remove').forEach(function(el) {
                            el.classList.remove('d-none', 'work-upload-hidden-until-remove');
                        });
                        newRow.querySelectorAll('.work-upload-hint-hidden-until-remove').forEach(function(el) {
                            el.classList.remove('d-none', 'work-upload-hint-hidden-until-remove');
                        });
                    }
                    var removeBtn = newRow.querySelector('.remove_exp');
                    if (removeBtn) {
                        removeBtn.classList.remove('remove_exp');
                        removeBtn.removeAttribute('data-exp_id');
                        removeBtn.removeAttribute('data-url');
                    }
                    newRow.querySelectorAll('.error-message').forEach(function(el) { el.remove(); });
                    var dateValSlot = (newRoot.querySelector && newRoot.querySelector('.work-row-date-validation'))
                        || newRow.querySelector('.work-row-date-validation');
                    if (dateValSlot) dateValSlot.innerHTML = '';
                    container.appendChild(newRoot);
                    if (!newRoot.classList.contains('work-entry-block')) {
                        ensureWorkEntryBlock($(newRow));
                    }
                    refreshWorkSerials();
                    bindWorkRowDateDisplay(newRow);
                    initWorkRow($(newRow));
                    /* New rows added on renewal/alteration must be fully editable. */
                    (function () {
                        var $newRow = $(newRow);
                        $newRow.removeClass('is-complete work-row--compact work-row--in-summary fs-alt-existing-work')
                            .addClass('work-row--expanded')
                            .removeData('wxWasComplete wxSummaryTr');
                        $newRow.find('input[name="fs_alt_existing_work[]"]').remove();
                        $newRow.find('input, textarea, select, button')
                            .not('.work-duration-y, .work-duration-m, .work-duration-d, .work-year-total-display')
                            .prop('disabled', false)
                            .prop('readonly', false);
                        ensureWorkRowDoneBar($newRow);
                        applyEmploymentType($newRow);
                        applyRowLayout($newRow);
                    })();
                    if (isCurrent && till && till.checked) {
                        applyTillDate($(newRow));
                    }
                    refreshWorkSerials();
                    syncSummaryTable();
                    updateOverallTotalYears();
                    updateWorkAddBtn();
                    var scrollEl = newRoot.classList.contains('work-entry-block') ? newRoot : newRow;
                    scrollEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    return;
                }

                if (e.target.closest('.remove-work')) {
                    e.preventDefault();
                    var card = e.target.closest('.work-fields');
                    if (card && isAlterationFrozenRow($(card))) return;
                    var container = card ? card.closest('.js-work-container, #work-container') : null;
                    if (!container) {
                        var $summaryTr = $(e.target).closest('.work-exp-summary-tr');
                        if ($summaryTr.length) {
                            var $linked = workRowFromSummaryTr($summaryTr);
                            if ($linked && $linked.length) {
                                card = $linked[0];
                                container = card.closest('.js-work-container, #work-container');
                            }
                        }
                    }
                    if (!container || !card) return;
                    var minRows = parseInt(container.getAttribute('data-min-rows'), 10);
                    if (isNaN(minRows)) minRows = 1;
                    var workRows = container.querySelectorAll('.work-fields');
                    if (workRows.length <= minRows) {
                        showSectionError(minRows === 0
                            ? 'This section cannot be empty while the row is present.'
                            : 'You must have at least ' + minRows + ' work experience entr' + (minRows === 1 ? 'y' : 'ies') + ' in this section.');
                        return;
                    }
                    var $card = $(card);
                    var $summaryTr = $card.data('wxSummaryTr');
                    if ($summaryTr && $summaryTr.length) {
                        $summaryTr.remove();
                        $card.removeData('wxSummaryTr');
                    }
                    var removeTarget = workEntryRemoveTarget(card);
                    if (removeTarget) removeTarget.classList.add('is-removing');
                    setTimeout(function() {
                        if (removeTarget && removeTarget.parentNode) {
                            removeTarget.parentNode.removeChild(removeTarget);
                        } else if (card.parentNode) {
                            card.parentNode.removeChild(card);
                        }
                        refreshWorkSerials();
                        syncSummaryTable();
                        updateOverallTotalYears();
                        validateWorkContainerDateSequence($(container));
                        if (typeof window.wxSyncBoardMemberRenewalFee === 'function') {
                            window.wxSyncBoardMemberRenewalFee();
                        }
                    }, 180);
                }
            });

            /**
             * Shared 2-year / Up-to-650V gate for New, Renewal, Digitization, Edit, Alteration.
             * Returns { ok: true } or { ok: false, message: '...' }.
             */
            window.wxValidateFormSCountableExperience = function () {
                var t = totalDurationAcrossRows();
                var needsTwoYears = t.hasAny || t.hasExcluded650vOnly;
                if (!needsTwoYears || t.ms >= TWO_YEARS_MS) {
                    return { ok: true, total: t };
                }
                return {
                    ok: false,
                    total: t,
                    message: t.hasExcluded650vOnly
                        ? 'Experience with Voltage Level "Up to 650V" is not counted. Add experience above 650V totaling at least 2 years.'
                        : 'Minimum 2 Years Experience needed across all entries (Voltage up to 650V is not counted).'
                };
            };
        })();

@if (!empty($enableBoardMemberRenewalFeeExempt) || !empty($enableBoardMemberFeeExempt))
        (function() {
            var BOARD_MEMBER_TYPE = 'board_member_tnelb';
            var cachedStandardFee = null;

            function isFormSBoardMemberFeeForm() {
                var form = ($('#form_name').val() || '').trim().toUpperCase();
                var appl = ($('#appl_type').val() || '').trim().toUpperCase();
                return form === 'S' && (appl === 'N' || appl === 'R');
            }

            window.isFormS7bBoardGateYes = function() {
                if (!$('#fs-7b-root').length) {
                    return false;
                }
                return ($('input[name="current_work_board_member"]:checked').val() || 'no').toLowerCase() === 'yes';
            };

            window.wxHasBoardMemberWorkRow = function() {
                if (!window.isFormS7bBoardGateYes()) {
                    return false;
                }
                var $rows = $('.js-work-container[data-work-part="current"] .work-fields');
                if (!$rows.length) {
                    $rows = allWorkFields();
                }
                var found = false;
                $rows.each(function() {
                    var type = ($(this).find('.work-employment-type').val() || '').trim();
                    if (type === BOARD_MEMBER_TYPE) {
                        found = true;
                        return false;
                    }
                });
                return found;
            };

            $(document).on('change', 'input[name="current_work_board_member"]', function () {
                if (($(this).val() || '').toLowerCase() === 'no') {
                    $('#work-container-current .error-message.d-block.mt-1').remove();
                }
            });

            function setFeeNotice(/* exempt */) {
                var $notice = $('#board-member-fee-notice');
                if (!$notice.length) return;
                /* Keep fee exemption silent once §7b board-member details apply — do not show the banner. */
                $notice.addClass('d-none');
            }

            window.wxSyncBoardMemberRenewalFee = async function() {
                if (!isFormSBoardMemberFeeForm()) return;

                var $amount = $('#amount');
                var $flag = $('#board_member_fee_exempt');
                var exempt = window.wxHasBoardMemberWorkRow();

                if ($flag.length) {
                    $flag.val(exempt ? '1' : '0');
                }

                if (exempt) {
                    if (cachedStandardFee === null && $amount.length) {
                        var stored = parseFloat($amount.data('standardFee'));
                        if (!isNaN(stored)) {
                            cachedStandardFee = stored;
                        }
                    }
                    if ($amount.length) $amount.val('0');
                    setFeeNotice(true);
                    return;
                }

                setFeeNotice(false);
                if (!$amount.length) return;

                if (cachedStandardFee !== null) {
                    $amount.val(String(cachedStandardFee));
                    return;
                }

                try {
                    if (typeof getPaymentsService !== 'function') return;
                    var licence_code = ($('#license_name').val() || '').trim();
                    var appl_type = ($('#appl_type').val() || '').trim();
                    var issued_licence = ($('#license_number').val() || '').trim();
                    if (!licence_code || !appl_type) return;
                    var data = await getPaymentsService(licence_code, issued_licence, appl_type, { silent: true });
                    if (data && data.total_fees !== undefined && data.total_fees !== null && data.total_fees !== '') {
                        cachedStandardFee = parseFloat(data.total_fees);
                        if (!isNaN(cachedStandardFee)) {
                            $amount.data('standardFee', cachedStandardFee);
                            $amount.val(String(cachedStandardFee));
                        }
                    }
                } catch (e) {
                    /* keep existing fallback amount */
                }
            };

            $(document).ready(function() {
                var $amount = $('#amount');
                if ($amount.length) {
                    var initial = parseFloat($amount.val());
                    if (!isNaN(initial) && initial > 0) {
                        $amount.data('standardFee', initial);
                        cachedStandardFee = initial;
                    }
                }
                if (typeof window.wxSyncBoardMemberRenewalFee === 'function') {
                    window.wxSyncBoardMemberRenewalFee();
                }
            });
        })();
@endif

</script>
@endif
