---
name: repo-hygiene
description: Code repository hygiene rules - what NOT to commit
---

# Repository Hygiene Skill

This skill encodes the specific **hygiene problems found in the TNelb_Staging26 codebase** and establishes rules to prevent them from recurring. If you see these patterns, **stop and fix them before committing**.

## Rule 1: No Backup Controller Files

**What to avoid:**
- `*_bk.php` (e.g., `FormAController_bk.php`, `generateLicencepdfController_bk.php`)
- `*_edit.php` (e.g., `FormAController_edit.php`)
- Dated-suffix files (e.g., `FormAController090926.php`, `FormAController_sp_last_bk.php`)
- Any file that duplicates another file with a suffix indicating it's a backup or older version

**Why:** Multiple versions of the same file in the codebase are confusing, waste space, and create a false sense of "I have a backup if this breaks." The real backup is git history. If you need to keep an old version, commit it to git, then delete the duplicate file. If you're iterating on a file, commit the changes directly or use a git branch.

**How to fix:**
- Delete all `*_bk.php`, `*_edit.php`, dated-suffix copies from `app/Http/Controllers/`, `app/Models/`, and `app/Services/`
- If a backup file has code that's not in the main file, merge that code into the main file first, then delete the backup

**Current offenders in the repo:**
```
app/Http/Controllers/Admin/generateLicencepdfController_bk.php
app/Http/Controllers/FormAController_bk.php
app/Http/Controllers/FormAController_edit.php
app/Http/Controllers/FormAController090926.php
app/Http/Controllers/FormAController_sp_last_bk.php
```

Delete these files or merge their unique code into the active versions before committing new work.

## Rule 2: No Raw SQL Dumps in db/

**What to avoid:**
- `.sql` files under `db/` (e.g., `db/backup.sql`, `db/local.sql`, `db/*.backup`)
- Any raw SQL snapshots committed to the repo
- Using raw SQL dumps for migrations or version control

**Why:** SQL dumps are unmigrated schema snapshots. They don't integrate with Laravel's migration system, they're hard to review in diffs, and they can cause merge conflicts. If you need to recover the schema, use migrations. If you need a database backup for local development, use a `.gitignore`'d directory outside the repo, or document how to restore it (don't commit the actual file).

**How to fix:**
- Delete all `.sql`, `.backup`, `.dump` files from `db/`
- Use `database/migrations/` for all schema versioning
- If you need a local database snapshot for development, store it in a separate location (e.g., `~/tnelb_backup/`) outside the repo, and add `db/` to `.gitignore`

**Current situation in the repo:**
The `db/` directory contains raw SQL dumps. These should be removed or moved to `.gitignore`.

## Rule 3: No User Uploads Under public/

**What to avoid:**
- Storing user-uploaded documents under `public/age_proof/`, `public/education_document/`, `public/work_experience/`, `public/uploads/`, `public/attached_documents/`
- Serving uploaded files directly via the web root (public access to all uploads)
- Relying on web server config (htaccess, nginx rules) to restrict access after the fact

**Why:** Storing uploads under `public/` means they're served directly by the web server with no permission checks. A user can access another user's upload by guessing the filename or URL structure. This is a **security vulnerability**. Additionally, uploads should be isolated from code, making backups and deployments cleaner.

**How to fix:**
1. Move uploads from `public/` to `storage/app/uploads/` (or a similar non-public location)
2. Create a controller route (e.g., `GET /download/{type}/{id}`) that:
   - Validates the user is authenticated
   - Checks the user has permission to access that upload
   - Returns the file via `Storage::download()` or `response()->download()`
3. Update the database to store the file path in `storage/`, not `public/`
4. Create a migration to move existing files from `public/` to `storage/` (or handle via a one-time artisan command)

**Example controller route:**
```php
Route::get('/download/{type}/{id}', [DocumentController::class, 'download'])
    ->middleware('auth')
    ->name('documents.download');

// DocumentController.php
public function download($type, $id)
{
    $document = UserDocument::findOrFail($id);
    $this->authorize('view', $document); // Check user owns it
    return Storage::download('uploads/' . $document->path);
}
```

**Current situation in the repo:**
```
public/age_proof/              # User-uploaded age proofs
public/education_document/     # User-uploaded education docs
public/work_experience/        # User-uploaded work experience docs
public/uploads/                # Generic user uploads
public/attached_documents/     # Attached documents
```

These directories should be moved to `storage/app/uploads/` and served via a permission-checked controller route.

## Rule 4: Consistent PSR-4 Namespace/File-Path Matching

**What to avoid:**
- Namespace `App\Http\Controllers\Admin\SomeController` in file `app/Http/Controllers/admin/SomeController.php` (case mismatch: `Admin` vs `admin`)
- Namespace `App\Models\admin\SomeModel` in file `app/Models/Admin/SomeModel.php` (case mismatch)
- File not matching its namespace in terms of directory structure

**Why:** PSR-4 autoloading is case-sensitive on Unix/Linux systems. While Windows is forgiving, the code will break when deployed to Linux servers or in CI pipelines. Additionally, inconsistent naming makes it harder to understand the codebase structure.

**How to fix:**
1. Ensure namespace exactly matches the file path (including case): `App\Http\Controllers\Admin\SomeController` → file `app/Http/Controllers/Admin/SomeController.php`
2. Use a linter/fixer to catch these automatically: `composer audit` or Pint will warn about PSR-4 violations

**Current situation in the repo:**
Composer and Pint flag PSR-4 issues during package discovery (e.g., "Class App\Http\Controllers\admin\PageController located in Admin/PageController.php does not comply with psr-4 autoloading standard"). These should be fixed by aligning namespaces with file paths.

## Enforcement

**When you commit:**
1. Run `git status` and visually check for `*_bk.php`, `*_edit.php`, or dated copies
2. Check that no `.sql` or `.backup` files are staged
3. Verify that no new uploads are being committed under `public/`
4. Check that class namespaces match file paths (Pint will warn if not)

**In the AI-DLC workflow:**
- The `reviewer` agent checks for these violations and flags them as **FAIL** (blocking issues)
- The `formatter` agent (Pint) will warn about PSR-4 mismatches
- The orchestrator will not proceed to commit until these are resolved

**If you break these rules:**
- The reviewer will catch it and report it as a blocking issue
- Fix it before the code is merged
- Don't try to work around the rules — they exist because these patterns caused real problems in the codebase
