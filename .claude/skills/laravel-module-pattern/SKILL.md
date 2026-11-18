---
name: laravel-module-pattern
description: How the FormA/B/P/S/W/WH modules are really laid out in this repo (controllers, services, meta tables, views, JS)
---

# Form Module Layout (as it actually exists)

Match what is already in the repo. Do NOT invent `form-a/` view folders, `layouts/app.blade.php`
or `app/Http/Requests/Form[X]Request.php` — none of these exist here.

## Where things live

| Concern | Location |
|---|---|
| Routes | `routes/web.php` (public/user), `routes/admin.php` (admin, middleware-protected) |
| Controllers | `app/Http/Controllers/` — `FormAController`, `FormSAController`, `FormPController`, `FormWController`, `FormWHController`, `*DigitizationController`, `FormAPayUController`, `PayUPaymentController`; role controllers under `Admin/` |
| Business logic | `app/Services/` — `Competency/` (forms S/W/WH/P schema, workflow, certificates), `Contractor/` (Form A), `FormS/`, `DocumentVersion/`, `PayUService`, `PayUPaymentSettlementService`, `FormAPayUSettlementService` |
| Models | `app/Models/` — e.g. `EA_Application_model` (table `ccl_forma_meta`), `CC_Forms_Meta`/`CC_Form_S_Meta` and `Competency/CC_Form_{W,WH,P,S}_Meta`, `CC_Payments`, `PaymentTransactionModel` |
| User views | `resources/views/user_login/` — `apply-form-{a,s,p,w,wh}.blade.php`, `edit_*`, `renew-*`, `alteration/`, `digitization/`, `contractor/` |
| Public pages | `resources/views/*.blade.php`, `register.blade.php`, shared parts in `include/` (`header`, `footer`, `user_header`, `user_footer`, `sidebar`) |
| Blade components | `resources/views/components/` — `form-input`, `form-select`, `form-field`, `form-textarea`, `info-box` |
| Front-end JS | `public/assets/js/` — `custom.js` (global, loaded by `include/footer`), `register.js`, `forma.js`, `form_s.js`, `form_p_script.js`, `competency_form_ws.js`, `alteration*.js`, `digitization*.js` |
| Migrations | `database/migrations/` (37 files; many tables pre-date migrations) |
| Tests | `tests/Feature`, `tests/Unit` (very thin; no JS test setup) |

## Conventions

1. **Form A (contractor) and S/W/WH/P (competency) are two families.** Form A uses `ccl_forma_meta`
   and writes `payments`; the competency forms use `cc_form_*_meta` and write `cc_payments`.
   Both share `payment_transactions` for the PayU gateway state.
2. **Same field appears in many views.** A field such as `fathers_name` is repeated across apply,
   edit, renew, alteration and digitization views. Prefer a global/delegated solution (e.g. in
   `custom.js`) or update every variant; grep `name="<field>"` first.
3. **Skip dead copies.** `*_bk*`, `*_old*`, dated and `copy` files are not served — never edit them,
   never add new ones.
4. **Validation** is mostly inline in controllers/services (there is no `app/Http/Requests`). Follow
   the neighbouring controller; add a FormRequest only if the task asks for it.
5. **Keep controllers thin** — put workflow/payment/document logic in `app/Services/`; reuse
   `FormSDocumentUploadHandler`, `FormSProofDocumentService`, `FormSDocumentVersionService`.
6. **Payment state changes** go through the settlement services inside a `DB::transaction`.
7. **Migrations** — create them if needed, but NEVER run them; ask the user first.
8. **Uploads** — new uploads go under `storage/app/...` and are served through a permission-checked
   route, not `public/`.
