/*
 | Structured applicant address (Forms S / W / WH and the Form P edit page).
 | Markup: resources/views/user_login/partials/applicant-address-fields.blade.php, used by
 | apply-form-s, edit_application, edit_returned_application and edit_application_p (which
 | edit_returned_application_p includes).
 |
 | The visible parts (#address_line1, #address_line2, #city, #state, #district, #pincode)
 | are composed into the hidden #applicants_address, which is what the backend validates
 | (required|max:255), saves and what the preview modals / view summaries read.
 |
 | Block modes (data attributes on #fs-address-block):
 |   data-address-locked="1"  section locked (returned application, partial unlock): never
 |                            compose, validate or touch the hidden value.
 |   data-legacy-address      saved address was legacy free text: the hidden input keeps the
 |                            old text until the structured parts are complete, so a draft
 |                            save never replaces it with a half-typed address; submit /
 |                            preview are still blocked by the per-field required errors.
 |
 | No-op on pages without the structured block.
 */
(function ($) {
    'use strict';

    var TN = 'Tamil Nadu';
    var MAX_LEN = 255;
    var PIN_RE = /^[0-9]{6}$/;
    var PART_SEL = '#address_line1, #address_line2, #city, #state, #district, #pincode';
    // Preview & Proceed / Save as Draft (apply-form-s, edit_application) and the returned
    // application Submit (edit_returned_application). Form P edit page: #ProceedtoPayment
    // (Save and Proceed / Preview & Submit), #saveDraftBtn, and #DraftBtn (returned Submit).
    var BTN_SEL = '#submitPaymentBtn, #saveDraftBtn, #submitCorrectionsBtn, #ProceedtoPayment, #DraftBtn';

    function block() {
        return document.getElementById('fs-address-block');
    }

    function hasBlock() {
        return !!document.getElementById('address_line1') && !!document.getElementById('applicants_address');
    }

    function isLocked() {
        var b = block();
        return !!(b && b.getAttribute('data-address-locked') === '1');
    }

    function legacyAddress() {
        var b = block();
        return b && b.hasAttribute('data-legacy-address') ? String(b.getAttribute('data-legacy-address') || '') : null;
    }

    function val(id) {
        var el = document.getElementById(id);
        return el ? String(el.value || '').replace(/\s+/g, ' ').trim() : '';
    }

    function isTamilNadu() {
        return val('state') === TN;
    }

    // All required parts present (lengths are checked separately by validate()).
    function isComplete() {
        return val('address_line1') !== '' &&
            val('city') !== '' &&
            val('state') !== '' &&
            (!isTamilNadu() || val('district') !== '') &&
            PIN_RE.test(val('pincode'));
    }

    function compose() {
        if (!hasBlock()) return '';
        var hidden = document.getElementById('applicants_address');
        if (isLocked()) return hidden.value;

        var legacy = legacyAddress();
        if (legacy !== null && !isComplete()) {
            hidden.value = legacy;
            return legacy;
        }

        var parts = [
            val('address_line1'),
            val('address_line2'),
            val('city'),
            isTamilNadu() ? val('district') : '',
            val('state')
        ].filter(function (p) { return p !== ''; });
        var out = parts.join(', ');
        var pin = val('pincode');
        if (pin !== '') {
            out += (out !== '' ? ' - ' : '') + pin;
        }
        hidden.value = out;
        return out;
    }
    window.fsComposeApplicantAddress = compose;

    // Edit views: keep the read-only summary in step with the hidden value.
    function refreshSummary() {
        var view = document.querySelector('[data-view-for="applicants_address"]');
        var hidden = document.getElementById('applicants_address');
        if (!view || !hidden) return;
        var v = String(hidden.value || '').trim();
        view.textContent = v !== '' ? v : 'Not provided';
        view.classList.toggle('fs-view-value--empty', v === '');
    }

    function syncDistrict() {
        var row = document.getElementById('district_row');
        var sel = document.getElementById('district');
        if (!sel) return;
        var tn = isTamilNadu();
        if (!tn) {
            sel.value = '';
            setError('district', '');
        }
        sel.disabled = !tn;
        if (row) row.hidden = !tn;
    }

    function setError(id, msg) {
        var err = document.getElementById(id + '_error');
        if (err) err.textContent = msg || '';
    }

    // Returns the first invalid element (or null). Writes messages into the per-field spans.
    function validate() {
        if (!hasBlock() || isLocked()) return null;
        compose();
        var first = null;
        function fail(id, msg) {
            setError(id, msg);
            if (!first) first = document.getElementById(id);
        }
        function check(id, label, required, max) {
            var v = val(id);
            if (required && v === '') {
                fail(id, label + ' is required.');
            } else if (max && v.length > max) {
                fail(id, label + ' may not be greater than ' + max + ' characters (currently ' + v.length + ').');
            } else {
                setError(id, '');
            }
        }

        check('address_line1', 'Address Line 1', true, 70);
        check('address_line2', 'Address Line 2', false, 60);
        check('city', 'Village / Town / City', true, 40);
        check('state', 'State', true, 0);
        if (isTamilNadu()) {
            check('district', 'District', true, 0);
        } else {
            setError('district', '');
        }
        var pin = val('pincode');
        if (pin === '') {
            fail('pincode', 'Pin Code is required.');
        } else if (!PIN_RE.test(pin)) {
            fail('pincode', 'Enter a valid 6 digit Pin Code.');
        } else {
            setError('pincode', '');
        }

        var composed = document.getElementById('applicants_address').value;
        if (composed.length > MAX_LEN) {
            setError('applicants_address', 'Address may not be greater than ' + MAX_LEN + ' characters. Please shorten it.');
            if (!first) first = document.getElementById('address_line1');
        } else {
            setError('applicants_address', '');
        }
        return first;
    }
    window.fsValidateApplicantAddress = function () { return validate() === null; };

    // Edit views collapse sections into a read-only summary (data-mode="view"); open the
    // section first so the error is visible (mirrors competency_form_ws.js).
    function revealSection(el) {
        var section = el && el.closest ? el.closest('.fs-section[data-mode="view"]') : null;
        if (!section) return;
        section.setAttribute('data-mode', 'edit');
        var toggle = section.querySelector('.fs-section-edit-toggle');
        if (toggle) {
            var icon = toggle.querySelector('i');
            if (icon) icon.className = 'fa fa-check';
            toggle.setAttribute('title', 'Done');
        }
    }

    function focusError(el) {
        if (!el) return;
        revealSection(el);
        try {
            el.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } catch (e) {
            el.scrollIntoView();
        }
        try { el.focus({ preventScroll: true }); } catch (e2) { /* ignore */ }
    }

    // Pin code: digits only, max 6.
    $(document).on('input', '#pincode', function () {
        var clean = String(this.value || '').replace(/\D+/g, '').slice(0, 6);
        if (clean !== this.value) this.value = clean;
    });

    // Recompose on every edit; clear that field's error once the user changes it.
    $(document).on('input change', PART_SEL, function (e) {
        if (isLocked()) return;
        if (this.id === 'state' && e.type === 'change') syncDistrict();
        if (String(this.value || '').trim() !== '') setError(this.id, '');
        compose();
    });

    // Capture phase on document runs before every target/bubble-phase click handler,
    // whatever order the scripts were loaded in: custom.js #saveDraftBtn (bound on the
    // button), competency_form_ws.js #submitPaymentBtn (delegated) and the inline
    // #submitCorrectionsBtn handler in edit_returned_application; on Form P the delegated
    // #ProceedtoPayment and #DraftBtn / #saveDraftBtn handlers in form_p_script.js. So the
    // hidden value is always fresh when FormData is built, and an invalid address stops the click.
    document.addEventListener('click', function (e) {
        if (!hasBlock() || isLocked() || !e.target || !e.target.closest) return;
        var btn = e.target.closest(BTN_SEL);
        if (!btn) return;
        compose();

        // Drafts accept a partial address (backend: nullable) except S/W/WH renewal drafts,
        // which run full validation in custom.js (the Form P draft handler never does).
        var isRenewal = String($('#appl_type').val() || '').toUpperCase() === 'R' &&
            !document.getElementById('competency_form_p');
        if (btn.id === 'saveDraftBtn' && !isRenewal) return;

        var firstInvalid = validate();
        if (firstInvalid) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            focusError(firstInvalid);
        }
    }, true);

    $(function () {
        if (!hasBlock() || isLocked()) return;
        syncDistrict();
        compose();
        refreshSummary();
    });
})(jQuery);
