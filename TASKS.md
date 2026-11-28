# TASKS

Shared task board for all agents (`laravel-backend`, `blade-ui`, `formatter`, `tester`, `reviewer`, `design`).
Read this before starting work. Update it when you start or finish an item.

## Rules
- One line per task. Format: `- [ ] Task description (agent)`
- Mark `[~]` when in progress, `[x]` when done.
- Do not edit a file another agent has marked `[~]`.
- Move finished features to "Done" and prune old entries regularly.
- Ask the user before running any migration.

## Current
<!-- Example:
### Form S renewal workflow
- [ ] Migration for renewal meta table (laravel-backend)
- [ ] Controller + routes (laravel-backend)
- [ ] Blade preview page (blade-ui)
- [ ] Format with Pint/Prettier (formatter)
- [ ] Unit tests (tester)
- [ ] Review (reviewer)
-->

### Form P digitization: file upload errors inline instead of window.alert
UI-only. File: resources/views/user_login/digitization/apply-form-p_d.blade.php (showFilePreviewP ~line 733). Reference: apply-form-p.blade.php showFileErrorP/clearFileErrorP.
- [x] Replace the 3 window.alert calls (PDF type, min 5 KB, max KB) with inline error under the input; clear it on next change (blade-ui)
- [ ] Syntax check + manual browser checklist (tester)
- [ ] Review: XSS, readonly/hidden fields, dead copies untouched (reviewer)

### Form P preview modal: move Important Note under the title (P&G New.docx point 14)
UI-only. File: resources/views/user_login/partials/form-p-preview-modal.blade.php (#prvFpSubtitle ~line 667 -> just under the header). JS in form-p-preview-modal-script.blade.php finds it by id, keep id.
- [x] Move the note block under "Application Preview" header and adjust CSS spacing for the new position (blade-ui)
- [ ] Browser checklist: apply, digitization, renewal, alteration previews; print view (tester)
- [ ] Review (reviewer)

### Form P application PDF (/generate-pdf-p): digitisation-only data, modelled on Form S PDF
Backend-only. PDFController::generateFormPPDF (+ generateFormPPDFTA). New/Renewal output must stay byte-for-byte unchanged. Reference: PDFController::generatePDF (~1338-1730, appl_type 'D', Tnelb_CC_Digitization).
- [x] Plan approved by user (orchestrator)
- [x] Load Tnelb_CC_Digitization when appl_type is D; heading says Digitization; add "Details of Competency Certificate" block (cert no, first issue, valid from/to) (laravel-backend)
- [-] Tamil PDF generateFormPPDFTA: out of scope per user ("leave it"); Backlog
- [ ] php -l / Pint --test (formatter)
- [ ] Unit test for the digitisation-row selection helper + manual PDF checklist (tester)
- [ ] Review (reviewer)

### Form P digitisation success popup: "Type of Application" is blank
UI-only. Popup field #ps_applicationType_competency (header.blade.php ~2310) is only filled by competency_form_ws.js; the footer.blade.php showPaymentSuccessPopup (~2495) used on Form P pages never sets it.
- [x] Set #ps_applicationType_competency from form_type in footer.blade.php showPaymentSuccessPopup, guarded for missing form_type (blade-ui)
- [ ] Browser checklist: Form P digitisation shows "Digitisation"; Form S/W/WH popups unchanged; paid flow does not show "undefined" (tester)
- [ ] Review (reviewer)

### Admin Form P detail: show digitisation (old certificate) details
Contract between agents: controller passes `$cc_digitization` (object|null; non-null only when appl_type is D) to both views.
- [x] Admin/FormPController view_application_formp + view_completed_formp: load via formPDigitizationRow(), pass `cc_digitization` (laravel-backend)
- [x] formp/applicants_detail.blade.php + applicants_detail_completed.blade.php: "Details of Competency Certificate" block, Application Type label "Digitization Application" for D (blade-ui)
- [x] User follow-up: show the Details of Competency Certificate block directly under the header bar (Applicant Id | Name | D.O.B | Applied For), above the "Edit / View Applicant's Details" card, in both views (blade-ui)
- [x] User follow-up 2: drop the bordered table; show the certificate details as plain label/value rows in the page's default style (like Applicant Id / Applicant Name rows), card stays under the header (blade-ui)
- [ ] Format check, tests/manual checklist (formatter, tester)
- [ ] Review (reviewer)

### Form P renewal + alteration: structured applicant address
Full-stack, small. No migration (address part columns already exist on cc_form_p_meta via 2026_11_17_120000). Persistence is shared: FormPController -> FormController (renewal/store, validates + saves parts when posted) and FormSAlterationService (alteration, addressPartsForForm uses the parent's form_name). Mirror alteration/form_s.blade.php lines ~1443-1456 and ~1551-1556.
- [x] Plan approved by user (option 1: extend to renewal + alteration); legacy single-line address handled by the partial's existing "re-enter" box (orchestrator)
- [x] Verify/supply `$application_details->address_parts` (+ parent parts for alteration) to renew-form-p and alteration/form_p views; renewal needs it in FormPController::renew_form_p (laravel-backend)
- [x] renew-form-p.blade.php: replace the address textarea (~line 412) with the partial, load applicant_address.js (blade-ui)
- [x] alteration/form_p.blade.php: locked partial in alteration mode (~1432) and editable partial in edit mode (~1527), load applicant_address.js, keep the Edit-badge toggle working (blade-ui)
- [ ] Format check: php -l, node --check, Blade compile (formatter)
- [ ] Unit test for Form P address-parts payload in alteration/renewal + manual browser checklist (tester)
- [ ] Review (reviewer)

### Form P alteration page: P&G New.docx points 12 and 9 (text only)
UI-only. File: resources/views/user_login/alteration/form_p.blade.php (work section ~line 1892). Mirror the wording already used on apply-form-p.blade.php.
- [x] Point 12: section title "Power Station to which the application is attached at present" + Tamil "விண்ணப்பதாரர் தற்போது பணிபுரியும் மின் உற்பத்தி நிலையத்தின் பெயர் & பணிக்காலம்" (blade-ui)
- [x] Point 9: add the "valid experience certificate issued by the Head of the Power Generating Station (not below the rank of Chief Engineer)" requirement note to the experience upload (blade-ui)
- [ ] Review (reviewer)

### Form P alteration declaration: P&G New.docx point 13
UI-only. User reversed the earlier "keep as is": the Form P alteration declaration must match the other Form P pages (single sentence, English + Tamil; second "I request that the selected alteration(s)..." line removed). File: resources/views/user_login/alteration/form_p.blade.php (~line 2738).
- [x] Replace the P alteration declaration branch with the same text as apply-form-p.blade.php (blade-ui)
- [ ] Review (reviewer)

### Form P point 12 wording on all pages: "application is attached at present"
UI-only text. User chose the document wording ("application") for every Form P page (alteration already done). Files: apply-form-p, digitization/apply-form-p_d, edit_application_p, renew-form-p, partials/form-p-preview-modal, admin/dashboard/formp/applicants_detail, admin/completedappls/competency/completed_appl_view.
- [x] Replace "Power Station to which the applicant is attached at present" with "...the application is attached at present" in those views (blade-ui)
- [ ] Review (reviewer)

### Form S digitisation: show Instructions & Declaration popup after the certificate popup
UI/JS-only. File: public/assets/js/digitization.js (success branch of #digitizationSubmit ~line 495). Modal #competencyInstructionsModal and loadInstructions() already exist (header.blade.php ~2088); nothing calls them on this page today. Form S digitisation page only (/apply-form-s_d); other digitisation scripts untouched.
- [x] Scope widened by user ("show that instruction for this digitisation too"): all four digitisation forms. Files: digitization.js (S), digitization_p.js, digitization_w.js, digitization_wh.js
- [x] After digitizationModal.hide() in each script's success branch: await loadInstructions(); competencyModal.show(); use each form's own appl_type D / licence_code (blade-ui)
- [x] Set a neutral subtitle on the instruction modal on digitisation pages (no "proceeding to payment" text), via JS so other pages keep theirs (blade-ui)
- [x] node --check the four scripts: pass (blade-ui); formatter re-check pending
- [ ] node --check the four scripts (formatter)
- [ ] Manual browser checklist: submit certificate popup -> instruction popup opens with text; tick declaration + Proceed closes it; unticked shows error; reload with existing enrolment does not re-open it; Form W/WH/P unchanged (tester)
- [ ] Review: XSS in instruction HTML, no regression to the S enrolment flow (reviewer)
- Open: instruction text for appl_type D / licence C may be missing in DB (popup would show "Instruction not available.")

### All forms: Year of Passing (education) dropdown starts from 1900
UI-only. Dropdowns currently stop at 1980 (S/W/WH/renew/alteration/edit) or 1953 (Form P apply/digitisation/edit). Both Blade `@for` loops and JS-generated rows must change. Check JS/server validation does not still reject years < 1980/1953.
- [~] Change lower bound to 1900 in all live education-year dropdowns: partials/form-s-education-row, document-version/partials/education-row, document-version/sample/index, without-tmp education-row, apply-form-p/w/wh, digitization/apply-form-p_d/w_d/wh_d, edit_application(_p), edit_returned_application, renew-form(-p), alteration/form_p/w/wh (blade-ui)
- [ ] node --check / Blade compile on changed files (formatter)
- [ ] Manual browser checklist: dropdown lists current year down to 1900 on each form; add-row rows too; saved old values preselect (tester)
- [ ] Review (reviewer)

### Form S Q8 (Supervisor) and Q9 (Wireman) certificate dates: basic date validation
Fields: Date of First Issue, From date, To date (sections #previously_details and #wireman_details; classes verify-issue-date / verify-valid-from / verify-date; shared custom.js loaded by footer). Today only "required" fires on change. Rules assumed: valid real date, year 4 digits, first issue between 01-01-1900 and today; from date >= first issue and <= today; to date after from date and <= 31-12-2099; inline errors, no alert; each re-checked when a dependent date changes; also checked on submit. Same rules on the server where these fields are validated.
- [~] Client rules in public/assets/js/custom.js (change handlers + min/max attributes + a reusable check callable on submit) and wire the submit-time check on the Form S pages (blade-ui)
- [~] Server-side rules for the same fields on the Form S save paths (laravel-backend)
- [ ] node --check / Blade compile / php -l (formatter)
- [ ] Unit test for the server rules + manual browser checklist (tester)
- [ ] Review (reviewer)

## Backlog
- [ ] Widen cc_digitisation_map.old_cc_no (reportedly numeric(5,0)) to 6 digits on every environment before release; user doing it manually, no migration written (user)
- [ ] Decide leading-zero handling for certificate numbers: 000123 is stored as 123 in old_cc_no (reviewer WARN)
- [ ] Optional: remove now-redundant preg_match guard in FormPController::storeDigitization (~line 307)
- [ ] Optional: Pint cleanup of FormPController and FormSDigitizationController as a separate commit

## Done
### All digitisation forms: certificate number = 1 to 6 digits, numeric only (2026-11-27)
- [x] Server rule `digits_between:1,6` + shared messages in FormP/FormSDigitization/FormW/FormWH controllers (laravel-backend)
- [x] Popup input shared by all forms in include/header.blade.php (blade-ui)
- [x] Client check + digit-only filter in digitization_p.js and digitization_wh.js; S and W already correct (blade-ui)
- [x] Format check: php -l and node --check pass; Pint not applied to legacy files (formatter)
- [x] Unit test DigitizationCertificateNumberRulesTest, 9 tests pass (tester)
- [x] Review: PASS, 3 WARNs moved to Backlog (reviewer)
