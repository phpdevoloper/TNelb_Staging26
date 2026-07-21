@if (($editFormName ?? ($application_details->form_name ?? '')) === 'S')
<script>
(function() {
    var EMP_LABEL = {
        private_organisation: 'Private organisation',
        electrical_contractor: 'Electrical contractor',
        retired_employee: 'Retired Employee',
        govt_organisation: 'Govt organisation',
        apprenticeship: 'Apprenticeship'
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

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function fmtPretty(iso) {
        if (!iso) return '—';
        var p = iso.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!p) return iso;
        var y = parseInt(p[1], 10), m = parseInt(p[2], 10), d = parseInt(p[3], 10);
        if (isNaN(y) || isNaN(m) || isNaN(d) || m < 1 || m > 12) return iso;
        return d + ' ' + MONTH_SHORT[m - 1] + ' ' + y;
    }

    function readIso($el) {
        if (!$el || !$el.length) return '';
        if (typeof window.readWorkDateIsoGeneric === 'function') {
            return window.readWorkDateIsoGeneric($el);
        }
        var v = String($el.val() || '').trim();
        if (/^\d{4}-\d{2}-\d{2}$/.test(v)) return v;
        var raw = String($el.attr('data-raw') || '').trim();
        return raw || v;
    }

    function todayIso() {
        var n = new Date();
        return n.getFullYear() + '-' + String(n.getMonth() + 1).padStart(2, '0') + '-' + String(n.getDate()).padStart(2, '0');
    }

    function calendarDiff(fromIso, toIso) {
        var from = new Date(fromIso + 'T12:00:00');
        var to = new Date(toIso + 'T12:00:00');
        if (isNaN(from.getTime()) || isNaN(to.getTime()) || to < from) return null;
        /* Inclusive of From and To (measure to To+1 day). */
        var end = new Date(to.getFullYear(), to.getMonth(), to.getDate() + 1, 12, 0, 0);
        var y = end.getFullYear() - from.getFullYear();
        var m = end.getMonth() - from.getMonth();
        var d = end.getDate() - from.getDate();
        if (d < 0) { m--; d += new Date(end.getFullYear(), end.getMonth(), 0).getDate(); }
        if (m < 0) { y--; m += 12; }
        return { y: y, m: m, d: d };
    }

    function docLink($row, kind) {
        var sel = kind === 'relieve' ? 'input[name="existing_work_relieving_document[]"]' : 'input[name="existing_work_document[]"]';
        var path = ($row.find(sel).first().val() || '').trim();
        var $inp = kind === 'relieve' ? $row.find('.work-relieve-input') : $row.find('.work-doc-input');
        if ($inp.length && $inp[0].files && $inp[0].files[0]) {
            return '<a class="wx-sum-doc-link" href="' + esc(URL.createObjectURL($inp[0].files[0])) + '" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View Document</a>';
        }
        if ($inp.attr('data-has-local-file')) {
            return '<span class="wx-sum-attach-value">File attached</span>';
        }
        if (!path) return '<span class="wx-sum-attach-value">—</span>';
        var href = path.charAt(0) === '/' ? path : ('/' + path.replace(/^\/+/, ''));
        if (!/^https?:\/\//i.test(path)) {
            var base = (typeof BASE_URL !== 'undefined' ? BASE_URL : '').replace(/\/$/, '');
            href = base + href;
        } else {
            href = path;
        }
        return '<a class="wx-sum-doc-link" href="' + esc(href) + '" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View Document</a>';
    }

    function buildViewRowHtml($row, sno) {
        var type = ($row.find('.work-employment-type').val() || '').trim();
        var empTxt = EMP_LABEL[type] || type || '—';
        var cat = ($row.find('.work-contractor-cat').val() || '').trim();
        var lic = ($row.find('.work-licence-number').val() || '').trim();
        var employer = ($row.find('.work-employer-input').val() || '').trim();
        var address = ($row.find('.work-org-address').val() || '').trim();
        var desig = ($row.find('.work-designation').val() || '').trim();
        var nature = ($row.find('.work-nature').val() || '').trim();
        var volt = ($row.find('.work-voltage').val() || '').trim();
        var kva = ($row.find('.work-transformer-kva').val() || '').trim();
        var fromIso = readIso($row.find('.work-date-from'));
        var toIso = readIso($row.find('.work-date-to'));
        var isTill = $row.find('.work-date-till').is(':checked');
        var toEff = isTill ? todayIso() : toIso;
        var yN = parseInt($row.find('.work-duration-y').val(), 10) || 0;
        var mN = parseInt($row.find('.work-duration-m').val(), 10) || 0;
        var dN = parseInt($row.find('.work-duration-d').val(), 10) || 0;
        if (!yN && !mN && !dN && fromIso && toEff) {
            var diff = calendarDiff(fromIso, toEff);
            if (diff) { yN = diff.y; mN = diff.m; dN = diff.d; }
        }
        var kvaTxt = (volt === 'up_to_650v')
            ? 'Not applicable'
            : (!kva ? '—' : (kva === 'Above 1000' ? esc(kva) : esc(kva + ' kVA')));
        var empCell = '<span class="wx-sum-main">' + esc(empTxt) + '</span>';
        if (type === 'electrical_contractor' && cat) empCell += '<span class="wx-sum-sub">Cat: ' + esc(cat) + '</span>';
        if (type === 'electrical_contractor' && lic) empCell += '<span class="wx-sum-sub">Licence: ' + esc(lic) + '</span>';
        var orgCell = '<span class="wx-sum-main">' + esc(employer || '—') + '</span>';
        if (address) orgCell += '<span class="wx-sum-sub">' + esc(address) + '</span>';
        var toCell = isTill
            ? '<span style="display:inline-block;background:#e8f4fd;color:#035ab3;border:1px solid #b8d4f0;border-radius:4px;padding:1px 6px;font-size:.68rem;font-weight:600;">Till date</span>'
            : esc(fmtPretty(toIso));
        var periodHtml = '<div class="wx-period-box"><div class="wx-period-dates">'
            + '<div class="wx-period-mini"><span class="wx-period-label">From</span><span class="wx-period-val">' + esc(fmtPretty(fromIso)) + '</span></div>'
            + '<div class="wx-period-mini"><span class="wx-period-label">To</span><span class="wx-period-val">' + toCell + '</span></div></div>';
        if (fromIso && toEff) {
            periodHtml += '<div class="wx-period-duration">'
                + '<div class="wx-period-dur-cell"><span class="wx-period-dur-num">' + yN + '</span><span class="wx-period-dur-lbl">Years</span></div>'
                + '<div class="wx-period-dur-cell"><span class="wx-period-dur-num">' + mN + '</span><span class="wx-period-dur-lbl">Months</span></div>'
                + '<div class="wx-period-dur-cell"><span class="wx-period-dur-num">' + dN + '</span><span class="wx-period-dur-lbl">Days</span></div></div>';
        }
        periodHtml += '</div>';
        var relInner = isTill ? '<span class="wx-sum-attach-value">Not required (Till date)</span>' : docLink($row, 'relieve');
        return '<tr>'
            + '<td class="work-row-summary-sno text-center">' + sno + '</td>'
            + '<td class="work-row-summary-employment">' + empCell + '</td>'
            + '<td class="work-row-summary-org-address">' + orgCell + '</td>'
            + '<td class="work-row-summary-designation">' + esc(desig || '—') + '</td>'
            + '<td class="work-row-summary-nature">' + esc(NATURE_LABEL[nature] || nature || '—') + '</td>'
            + '<td class="work-row-summary-voltage">' + esc(VOLTAGE_LABEL[volt] || volt || '—') + '</td>'
            + '<td class="work-row-summary-kva text-center">' + kvaTxt + '</td>'
            + '<td class="work-row-summary-period">' + periodHtml + '</td>'
            + '<td class="work-row-summary-attachments"><div class="wx-sum-attach-stack">'
            + '<div class="wx-sum-attach-block"><span class="wx-sum-attach-label">Supporting :</span>' + docLink($row, 'support') + '</div>'
            + '<div class="wx-sum-attach-block"><span class="wx-sum-attach-label">Relieving :</span>' + relInner + '</div>'
            + '</div></td></tr>';
    }

    window.renderFormSWorkExpView = function() {
        var $tbody = $('#work-exp-view-tbody');
        if (!$tbody.length) return;
        var html = '';
        var sno = 0;
        $('#work-container .work-fields').each(function() {
            var $row = $(this);
            var type = ($row.find('.work-employment-type').val() || '').trim();
            var emp = ($row.find('.work-employer-input').val() || '').trim();
            if (!type && !emp) return;
            sno++;
            html += buildViewRowHtml($row, sno);
        });
        $tbody.html(html || '<tr><td colspan="9" class="text-center text-muted py-3">No work experience entries</td></tr>');
    };

    var _origToggle = window.toggleSectionEdit;
    window.toggleSectionEdit = function(btn) {
        var section = btn.closest('.fs-section');
        if (section && section.id === 'fs-section-work-exp') {
            var current = section.getAttribute('data-mode') || 'view';
            var next = current === 'edit' ? 'view' : 'edit';
            if (next === 'view') {
                window.renderFormSWorkExpView();
            }
            section.setAttribute('data-mode', next);
            var icon = btn.querySelector('i');
            if (icon) icon.className = next === 'edit' ? 'fa fa-check' : 'fa fa-pencil';
            btn.setAttribute('title', next === 'edit' ? 'Done' : 'Edit');
            return;
        }
        if (typeof _origToggle === 'function') {
            _origToggle(btn);
        }
    };
})();
</script>
@endif
