# TNelb_Staging26 Development Guide

## Project Overview

**Tamil Nadu Electrical Licensing Board (TNELB) Portal** — Laravel 9 (PHP 8) monolithic application serving license registration, renewal, and document workflows. Heavy PDF generation (dompdf, mpdf, tcpdf), file uploads, role-based admin dashboard, and PayU payment integration.

- **Framework:** Laravel 9, PHP 8.0+
- **Database:** MySQL (via config/database.php, migrations in database/migrations/)
- **Frontend:** Server-rendered Blade templates (355 files) + jQuery/Bootstrap admin theme (public/assets/admin/)
- **Major modules:** FormA/B/P/S/W/WH (license application forms), LicenseController, PaymentController, role-based Admin/* (President, Secretary, Supervisor, Auditor, Staff)

## File Structure

```
app/
  Http/Controllers/
    Admin/                    # Role-based admin controllers (President, Secretary, etc.)
    FormA|B|P|S|W|WH*        # License form handlers (repeating module pattern)
    LicenseController        # License lifecycle
    PaymentController        # PayU integration
  Models/                     # 106 models (most under Admin/ subdirectory)
  Services/                   # Service layer (FormS* utilities)
  Helpers/                    # Global helpers, RoleHelper, UrlHelper
routes/
  web.php                     # Public routes
  admin.php                   # Admin routes (middleware-protected)
  api.php                     # API routes
resources/
  views/                      # 355 Blade templates (forms, licenses, admin pages)
  js/                         # Minimal (1 line app.js, mostly Laravel Mix default)
  css/                        # Empty (styling via public/assets/admin/)
database/
  migrations/                 # 36 migration files (schema versioning)
public/
  assets/admin/              # jQuery 3.6, Select2, Bootstrap, custom plugins (admin theme)
  age_proof/                 # User uploads (security issue: should be in storage/)
  education_document/        # User uploads
  work_experience/           # User uploads
  uploads/                   # Generic uploads
  attached_documents/        # Generic uploads
```

## Development Commands

### Laravel Artisan
- `php artisan serve` — Start dev server (port 8000)
- `php artisan tinker` — Interactive shell
- `php artisan migrate` — Run pending migrations
- `php artisan make:migration <name>` — Create migration

### Testing
- `php artisan test` — Run all PHPUnit tests (Feature + Unit)
- `php artisan test --filter=<test-name>` — Run specific test
- `vendor/bin/phpunit` — Direct PHPUnit runner

### Code Quality
- `vendor/bin/pint` — Format PHP code (Laravel Pint, matches StyleCI preset)
- `vendor/bin/pint --test` — Check formatting without changes
- `npx prettier --write <path>` — Format JS/CSS (runs via npx, no install needed)

## Code Patterns

### FormA/B/P/S/W/WH Module Template
Every license form module follows this repeating pattern:

1. **Controller** — `app/Http/Controllers/FormAController.php` (handles display, submit, payment)
2. **Model** — `app/Models/FormA.php` (database table mapping, relationships)
3. **Views** — `resources/views/form-a/*.blade.php` (form template, preview, success pages)
4. **Route** — `routes/web.php` or `routes/admin.php` (GET/POST endpoints)
5. **Migration** — `database/migrations/xxxx_xx_xx_xxxxxx_create_form_a_table.php` (table schema)

When adding a new form or feature to an existing form, follow this pattern precisely to maintain consistency. Reuse existing service utilities (FormSDocumentUploadHandler, FormSProofDocumentService) when handling uploads or versioning.

### Admin Role Controllers
`app/Http/Controllers/Admin/` contains role-specific controllers (PresidentController, SecretaryController, SupervisorController, AuditorController, StaffController, PageController). These are protected by middleware in `routes/admin.php`. Follow existing class/namespace conventions when extending admin functionality.

## Known Hygiene Issues (Avoid Repeating These)

1. **Backup Controller Files** — Do not commit `*_bk.php`, `*_edit.php`, or dated-suffix copies (e.g., `FormAController090926.php`, `FormAController_sp_last_bk.php`). If iterating on a controller, commit the changes directly or use a git branch; never commit multiple versions of the same file.

2. **SQL Dumps in Repo** — The `db/` directory contains raw SQL dumps (`.sql`, `.backup` files). Never commit these. Use only Laravel migrations for schema versioning.

3. **User Uploads Under public/** — Directories like `public/age_proof/`, `public/education_document/`, `public/work_experience/`, `public/uploads/`, `public/attached_documents/` serve user-uploaded documents directly from the web root. This is a security and maintenance problem. Store uploads under `storage/app/uploads/` instead and serve them through a controller route that validates user permissions before returning the file (e.g., via Storage::download()).

4. **PSR-4 Autoloading Issues** — The codebase has inconsistent namespace/file-path naming (e.g., `app\Http\Controllers\Admin\CertificateController` in `Admin/CertificateController.php`, but `app\Http\Controllers\admin\PageController` in `Admin/PageController.php` — note the case mismatch). This doesn't break anything currently, but Pint and Composer will warn about it. When renaming or refactoring, ensure namespaces match file paths exactly (PSR-4 standard: `namespace App\Http\Controllers\Admin` → file `app/Http/Controllers/Admin/ClassName.php`).

## Git Workflow

- **Branch naming:** Use lowercase, descriptive names (e.g., `feat/form-s-renewal`, `fix/payment-gateway`, `docs/setup-guide`). Avoid ad-hoc names like `V2` or numbered branches.
- **Commit messages:** Write clear, present-tense summaries (e.g., `Add FormS renewal workflow` not `addformsrenew`). Provide context in the body if the change is non-obvious.
- **PR/Merge:** Use the `/ai-dlc` command to orchestrate multi-step changes (plan → build → format → test → review). This ensures code quality gates run before merge.

## Daily Workflow with AI-DLC

Start your day's task with:
```
/ai-dlc "Description of what you want to build"
```

This orchestrator will:
1. **Plan** — Break the task into backend + UI components using the module pattern
2. **Build** — Delegate to `laravel-backend` and `blade-ui` agents in parallel (you review before continuing)
3. **Format** — Run Pint on PHP, Prettier on JS/CSS
4. **Test** — Write + run PHPUnit Feature/Unit tests
5. **Review** — Scan for bugs, security issues, and pattern violations

Or, use `/ai-dlc-gate` to run just the format → test → review sequence at the end of the day without the planning phase.

## CI/CD

**Currently:** No CI pipeline (no GitHub Actions, no GitLab CI). StyleCI runs on PRs/pushes if configured at the GitHub org level, but there's no `.github/workflows/` in this repo.

**Future:** Consider adding a GitHub Actions workflow that runs `php artisan test` and `vendor/bin/pint --test` on PRs to catch issues before merge.
