<script>
(function () {
    if (!document.getElementById('competency_form_p') && !(document.getElementById('form_name') && document.getElementById('form_name').value === 'P' && document.getElementById('competency_form_ws'))) {
        return;
    }
    if (!document.getElementById('appPreviewModalFormP')) {
        return;
    }

    var EDU_LEVEL_MAP = {
        BEM: 'B.E (Mechanical)',
        BEE: 'B.E (Electrical)',
        DiplomaM: 'Diploma (Mechanical)',
        DiplomaE: 'Diploma (Electrical)'
    };
    var MONTH_MAP = {
        '01': 'Jan', '02': 'Feb', '03': 'Mar', '04': 'Apr', '05': 'May', '06': 'Jun',
        '07': 'Jul', '08': 'Aug', '09': 'Sep', '10': 'Oct', '11': 'Nov', '12': 'Dec'
    };

    function escHtml(s) {
        return String(s || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function val(id) {
        var el = document.getElementById(id);
        return el ? String(el.value || '').trim() : '';
    }

    function fmtDate(v) {
        if (!v) return '';
        var s = String(v).trim();
        var iso = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
        if (iso) return iso[3] + '-' + iso[2] + '-' + iso[1];
        var dmy = s.match(/^(\d{1,2})-(\d{1,2})-(\d{4})$/);
        if (dmy) {
            return String(dmy[1]).padStart(2, '0') + '-' + String(dmy[2]).padStart(2, '0') + '-' + dmy[3];
        }
        return s;
    }

    function ymdText(row) {
        var y = row.querySelector('.fp-years');
        var m = row.querySelector('.fp-months');
        var d = row.querySelector('.fp-days');
        if (!y && !m && !d) return '';
        var yv = y ? String(y.value || '').trim() : '';
        var mv = m ? String(m.value || '').trim() : '';
        var dv = d ? String(d.value || '').trim() : '';
        if (yv === '' && mv === '' && dv === '') return '';
        return (yv || '0') + ' Y, ' + (mv || '0') + ' M, ' + (dv || '0') + ' D';
    }

    function setField(id, text) {
        var el = document.getElementById(id);
        if (!el) return;
        var t = (text || '').toString().trim();
        el.textContent = t || '—';
        el.classList.toggle('prv-fp-empty', !t);
    }

    function resolveAssetUrl(path) {
        if (!path) return '';
        path = String(path).trim();
        if (!path) return '';
        if (/^https?:\/\//i.test(path)) return path;
        var base = (typeof BASE_URL !== 'undefined' ? BASE_URL : '').replace(/\/$/, '');
        if (path.charAt(0) === '/') return base + path;
        return base + '/' + path.replace(/^\/+/, '');
    }

    function usableHref(link) {
        if (!link) return '';
        var href = (link.getAttribute('href') || '').trim();
        if (!href || href === '#' || href.indexOf('javascript:') === 0) return '';
        return href;
    }

    function docPill(href, label) {
        return '<a class="prv-fp-doc-pill" href="' + escHtml(href) + '" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> ' + escHtml(label || 'View') + '</a>';
    }

    function docCellFromRow(row, filePrefix) {
        var inp = row.querySelector('input[type="file"][name^="' + filePrefix + '"]');
        if (inp && inp.files && inp.files[0]) {
            var blob = URL.createObjectURL(inp.files[0]);
            return docPill(blob, 'View');
        }
        var links = row.querySelectorAll('a[href]');
        for (var i = 0; i < links.length; i++) {
            var href = usableHref(links[i]);
            if (href) return docPill(href, 'View');
        }
        return '<span class="prv-fp-doc-empty">—</span>';
    }

    function existingDocLink(selector) {
        var link = document.querySelector(selector);
        if (!link || !link.getAttribute('href')) return '';
        return '<a class="prv-fp-doc-pill" href="' + escHtml(link.getAttribute('href')) + '" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View</a>';
    }

    function docLabelForInput(inputId) {
        var savedSelectors = {
            aadhaar_doc: '.aadhaar-doc-container a[href]',
            pancard_doc: '.pan-doc-container a[href]'
        };
        var inp = document.getElementById(inputId);
        if (!inp) {
            return savedSelectors[inputId] ? (existingDocLink(savedSelectors[inputId]) || '<span class="prv-fp-doc-empty">—</span>') : '<span class="prv-fp-doc-empty">—</span>';
        }
        if (inp.files && inp.files[0]) {
            return '<span class="prv-fp-doc-pill" style="cursor:default;"><i class="fa fa-file-pdf-o"></i> ' + escHtml(inp.files[0].name) + '</span>';
        }
        var $local = inp.nextElementSibling && inp.nextElementSibling.classList && inp.nextElementSibling.classList.contains('local-file-preview')
            ? inp.nextElementSibling
            : null;
        if ($local) {
            var a = $local.querySelector('a');
            if (a) {
                return '<a class="prv-fp-doc-pill" href="' + escHtml(a.getAttribute('href')) + '" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> ' + escHtml(a.textContent.trim() || 'View') + '</a>';
            }
        }
        var wrap = inp.closest('td, .fs-upload-card, tr');
        if (wrap) {
            var viewA = wrap.querySelector('.aadhaar-doc-container a[href], .pan-doc-container a[href], .fs-doc-existing a[href], a[href*="private_documents"], a[href*="attached_documents"], a[href*="/document/"]');
            if (viewA && usableHref(viewA)) {
                return docPill(usableHref(viewA), 'View');
            }
        }
        return '<span class="prv-fp-doc-empty">—</span>';
    }

    function imageSrcForPreview(ids) {
        for (var i = 0; i < ids.length; i++) {
            var el = document.getElementById(ids[i]);
            if (!el) continue;
            var s = (el.getAttribute('src') || '').trim();
            if (s) return s;
        }
        return '';
    }

    function renderThumb(wrapId, imgIds, w, h, alt) {
        var wrap = document.getElementById(wrapId);
        if (!wrap) return;
        var src = imageSrcForPreview(Array.isArray(imgIds) ? imgIds : [imgIds]);
        if (src) {
            wrap.innerHTML = '<img src="' + escHtml(src) + '" alt="' + escHtml(alt) + '" style="width:' + w + 'px;height:' + h + 'px;">';
        } else {
            wrap.innerHTML = '<div class="prv-fp-no-img" style="width:' + w + 'px;height:' + h + 'px;">No ' + escHtml(alt) + '</div>';
        }
    }

    function populateFormPPreview() {
        var applType = val('appl_type') || 'N';
        var isAlteration = applType === 'A' || !!document.querySelector('#competency_form_p.fs-alt-form, #competency_form_ws.fs-alt-form');
        var renewBadge = document.getElementById('prvFpRenewBadge');
        if (renewBadge) renewBadge.style.display = (applType === 'R') ? '' : 'none';
        var altBadge = document.getElementById('prvFpAltBadge');
        if (altBadge) altBadge.style.display = isAlteration ? '' : 'none';
        var subtitle = document.getElementById('prvFpSubtitle');
        if (subtitle) {
            subtitle.innerHTML = isAlteration
                ? 'Review every section carefully before submitting this alteration. Use <strong>Back to Edit</strong> if anything needs correction.'
                : 'Review every section carefully before proceeding to payment. Use <strong>Back to Edit</strong> if anything needs correction.';
        }
        if (confirmBtn) {
            confirmBtn.innerHTML = isAlteration
                ? '<i class="fa fa-check"></i> Confirm &amp; Submit'
                : '<i class="fa fa-check"></i> Confirm &amp; Proceed';
        }

        var applicantName = val('Applicant_Name') || val('applicant_name');
        setField('prvFpMetaName', applicantName);
        setField('prvFpMetaAppId', val('application_id') || 'Draft (not saved yet)');
        setField('prvFpMetaLicence', val('license_number') || 'Certificate P');

        var printTag = document.getElementById('prvFpPrintTag');
        if (printTag) {
            var tagParts = [];
            if (applType === 'R') tagParts.push('Renewal');
            if (isAlteration) tagParts.push('Alteration');
            tagParts.push('Form P');
            var lic = val('license_number');
            if (lic) tagParts.push('Licence: ' + lic);
            printTag.textContent = tagParts.join(' · ');
        }

        setField('prvFpName', applicantName);
        setField('prvFpFather', val('Fathers_Name') || val('fathers_name'));
        setField('prvFpEmail', val('applicant_email'));
        setField('prvFpAddress', val('applicants_address'));
        setField('prvFpDob', fmtDate(val('d_o_b')));
        setField('prvFpAge', val('age'));

        renderThumb('prvFpPhotoWrap', ['photo_preview', 'preview_applicant'], 88, 106, 'Photo');
        renderThumb('prvFpSignWrap', ['sign_preview', 'preview_signature'], 150, 56, 'Signature');

        // Education
        var eduBody = document.getElementById('prvFpEduBody');
        var eduRows = document.querySelectorAll('#education-container .education-fields');
        eduBody.innerHTML = '';
        if (!eduRows.length) {
            eduBody.innerHTML = '<tr><td colspan="7" class="text-muted py-3">No education entries</td></tr>';
        } else {
            eduRows.forEach(function (row, i) {
                var lv = row.querySelector('[name="educational_level[]"]');
                var inst = row.querySelector('[name="institute_name[]"]');
                var mon = row.querySelector('[name="month_of_passing[]"]');
                var yr = row.querySelector('[name="year_of_passing[]"]');
                var cert = row.querySelector('[name="certificate_no[]"]');
                var lvText = lv ? (EDU_LEVEL_MAP[lv.value] || lv.value || '—') : '—';
                var monText = mon ? (MONTH_MAP[mon.value] || mon.value || '—') : '—';
                var yrText = yr && yr.value && yr.value !== '0' ? yr.value : '—';
                eduBody.innerHTML += '<tr>'
                    + '<td>' + (i + 1) + '</td>'
                    + '<td class="prv-fp-td-left">' + escHtml(lvText) + '</td>'
                    + '<td class="prv-fp-td-left">' + escHtml(inst ? inst.value || '—' : '—') + '</td>'
                    + '<td>' + escHtml(monText) + '</td>'
                    + '<td>' + escHtml(yrText) + '</td>'
                    + '<td>' + escHtml(cert ? cert.value || '—' : '—') + '</td>'
                    + '<td>' + docCellFromRow(row, 'education_document') + '</td>'
                    + '</tr>';
            });
        }

        // Institute
        var instBody = document.getElementById('prvFpInstBody');
        var instRows = document.querySelectorAll('#institute-container .institute-fields');
        instBody.innerHTML = '';
        if (!instRows.length) {
            instBody.innerHTML = '<tr><td colspan="6" class="text-muted py-3">No institute entries</td></tr>';
        } else {
            instRows.forEach(function (row, i) {
                var nm = row.querySelector('[name="institute_name_address[]"]');
                var fr = row.querySelector('[name="from_date[]"]');
                var to = row.querySelector('[name="to_date[]"]');
                var durText = ymdText(row);
                instBody.innerHTML += '<tr>'
                    + '<td>' + (i + 1) + '</td>'
                    + '<td class="prv-fp-td-left">' + escHtml(nm ? nm.value || '—' : '—') + '</td>'
                    + '<td>' + escHtml(fmtDate(fr ? fr.value : '') || '—') + '</td>'
                    + '<td>' + escHtml(fmtDate(to ? to.value : '') || '—') + '</td>'
                    + '<td>' + escHtml(durText || '—') + '</td>'
                    + '<td>' + docCellFromRow(row, 'institute_document') + '</td>'
                    + '</tr>';
            });
        }

        // Work / Power Station
        var workBody = document.getElementById('prvFpWorkBody');
        var workRows = document.querySelectorAll('#work-container .work-fields');
        workBody.innerHTML = '';
        if (!workRows.length) {
            workBody.innerHTML = '<tr><td colspan="7" class="text-muted py-3">No power station entries</td></tr>';
        } else {
            var workShown = 0;
            workRows.forEach(function (row) {
                var station = row.querySelector('[name="work_level[]"]');
                var fr = row.querySelector('[name="work_date_from[]"]');
                var to = row.querySelector('[name="work_date_to[]"]');
                var des = row.querySelector('[name="designation[]"]');
                var stationText = station ? String(station.value || '').trim() : '';
                var fromText = fr ? String(fr.value || '').trim() : '';
                var toText = to ? String(to.value || '').trim() : '';
                var desText = des ? String(des.value || '').trim() : '';
                var hasFile = row.querySelector('input[type="file"][name^="work_document"]');
                var hasUpload = hasFile && hasFile.files && hasFile.files[0];
                if (!stationText && !fromText && !toText && !desText && !hasUpload && !row.querySelector('a[href]')) {
                    return;
                }
                var totText = ymdText(row);
                workShown += 1;
                workBody.innerHTML += '<tr>'
                    + '<td>' + workShown + '</td>'
                    + '<td class="prv-fp-td-left">' + escHtml(stationText || '—') + '</td>'
                    + '<td>' + escHtml(fmtDate(fromText) || '—') + '</td>'
                    + '<td>' + escHtml(fmtDate(toText) || '—') + '</td>'
                    + '<td>' + escHtml(totText || '—') + '</td>'
                    + '<td class="prv-fp-td-left">' + escHtml(desText || '—') + '</td>'
                    + '<td>' + docCellFromRow(row, 'work_document') + '</td>'
                    + '</tr>';
            });
            if (!workShown) {
                workBody.innerHTML = '<tr><td colspan="7" class="text-muted py-3">No power station entries</td></tr>';
            }
        }

        setField('prvFpEmployer', val('employer_name'));

        var prevYes = document.querySelector('#previous_license_yes, input[name="previous_license"][value="yes"]');
        var hasPrevData = val('previously_number') !== '' || val('previously_date') !== '';
        var isPrev = (prevYes && prevYes.checked) || hasPrevData;
        var yn = document.getElementById('prvFpPrevYn');
        if (yn) {
            yn.innerHTML = isPrev
                ? '<span class="prv-fp-yesno-yes">Yes</span>'
                : '<span class="prv-fp-yesno-no">No</span>';
        }
        var prevBlock = document.getElementById('prvFpPrevBlock');
        if (prevBlock) prevBlock.style.display = isPrev ? '' : 'none';
        if (isPrev) {
            setField('prvFpPrevNo', val('previously_number'));
            setField('prvFpPrevDate', fmtDate(val('previously_date')));
        }

        setField('prvFpAadhaar', val('aadhaar'));
        setField('prvFpPan', val('pancard'));
        document.getElementById('prvFpAadhaarDoc').innerHTML = docLabelForInput('aadhaar_doc');
        document.getElementById('prvFpPanDoc').innerHTML = docLabelForInput('pancard_doc');
    }

    var modal = document.getElementById('appPreviewModalFormP');
    var confirmBtn = document.getElementById('prvFpConfirmBtn');

    function closeFormPPreview(confirmed) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
        if (typeof window._prvResolveFormP === 'function') {
            window._prvResolveFormP(!!confirmed);
            window._prvResolveFormP = null;
        }
    }

    function openFormPPreview() {
        populateFormPPreview();
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        var body = document.getElementById('prvFpBody');
        if (body) body.scrollTop = 0;
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            closeFormPPreview(true);
        });
    }
    document.getElementById('prvFpBackBtn')?.addEventListener('click', function () {
        closeFormPPreview(false);
    });
    function getFormPPreviewStyles() {
        var styleEl = document.getElementById('form-p-preview-modal-styles');
        return styleEl ? styleEl.textContent : '';
    }

    function cleanupFormPPrintFrame() {
        var frame = document.getElementById('prvFpPrintFrame');
        if (frame) {
            frame.remove();
        }
    }

    function printFormPPreview() {
        populateFormPPreview();

        var panel = modal.querySelector('.prv-fp-panel');
        if (!panel) {
            return;
        }

        cleanupFormPPrintFrame();

        var iframe = document.createElement('iframe');
        iframe.id = 'prvFpPrintFrame';
        iframe.setAttribute('aria-hidden', 'true');
        iframe.style.cssText = 'position:fixed;left:0;top:0;width:0;height:0;border:0;visibility:hidden;';
        document.body.appendChild(iframe);

        var panelHtml = panel.innerHTML;
        var printHeadMatch = panelHtml.match(/<div class="prv-fp-print-head"[^>]*>/);
        if (printHeadMatch) {
            panelHtml = panelHtml.replace(
                printHeadMatch[0],
                printHeadMatch[0].replace('>', ' style="display:block !important;">')
            );
        }

        var styles = getFormPPreviewStyles();
        var printDoc = iframe.contentWindow.document;
        printDoc.open();
        printDoc.write('<!DOCTYPE html><html class="prv-fp-print-active" lang="en"><head><meta charset="utf-8">');
        printDoc.write('<title>Form P Application Preview</title>');
        printDoc.write('<style>' + styles + '</style>');
        printDoc.write('</head><body>');
        printDoc.write('<div id="prvFpPrintRoot" class="prv-fp-modal-root">');
        printDoc.write('<div class="prv-fp-panel">');
        printDoc.write(panelHtml);
        printDoc.write('</div></div></body></html>');
        printDoc.close();

        var runPrint = function () {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch (err) {
                console.error('Form P print failed:', err);
            }
            window.setTimeout(cleanupFormPPrintFrame, 1000);
        };

        window.setTimeout(runPrint, 300);
    }

    document.getElementById('prvFpPrintBtn')?.addEventListener('click', function () {
        printFormPPreview();
    });
    document.getElementById('prvFpCloseBtn')?.addEventListener('click', function () {
        closeFormPPreview(false);
    });
    modal.addEventListener('click', function (e) {
        if (e.target === modal) closeFormPPreview(false);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) {
            closeFormPPreview(false);
        }
    });

    window.showCompetencyPreviewModal = function () {
        return new Promise(function (resolve) {
            window._prvResolveFormP = resolve;
            openFormPPreview();
        });
    };
})();
</script>
