---
name: ai-dlc
description: Daily AI-DLC orchestrator - plan, build, format, test, review
---

# /ai-dlc Command

**Usage:** `/ai-dlc "<task description>"`

**Example:**
```
/ai-dlc "Add FormS renewal workflow with automatic document upload"
```

This is your **daily entrypoint** for building features. The orchestrator runs a full pipeline with pauses for review:

```
1. PLAN    → Understand the task, break it into backend + UI parts
2. [PAUSE] → You review the plan before we start building
3. BUILD   → Backend agent builds controllers/models/migrations; UI agent builds views/JS
4. [PAUSE] → You review the code changes
5. FORMAT  → Run Pint + Prettier on changed files
6. TEST    → Write + run PHPUnit tests
7. REVIEW  → Security + correctness review
8. [PAUSE] → Final review before you commit manually
```

## How It Works

### Phase 1: Plan
The orchestrator receives your task description and produces a **plan** that includes:
- What backend components are needed (controllers, models, migrations, services)
- What UI components are needed (Blade views, jQuery scripts, CSS updates)
- Which existing patterns to follow (Form module pattern, RBAC conventions, etc.)
- Dependencies and integration points

**You review this plan.** If it looks good, you say "yes, proceed to build." If you want to adjust scope, you say "no, focus on just X" and the plan adjusts.

### Phase 2: Build
The orchestrator calls two agents in parallel (if independent):
- **laravel-backend** — Implements controllers, models, migrations, services, routes
- **blade-ui** — Implements Blade views, jQuery scripts, CSS updates

Both agents produce code. The orchestrator **pauses here** and shows you:
- What files were created/modified
- A diff of the changes (or summary)
- Any errors or warnings

**You review the code.** If it looks right, you say "yes, continue." If something is wrong, you say "no, fix X" and the backend/UI agents adjust.

### Phase 3: Format
The **formatter** agent runs:
- `vendor/bin/pint` on changed PHP files
- `npx prettier --write` on changed JS/CSS files

Outputs a summary of what was reformatted.

### Phase 4: Test
The **tester** agent:
- Writes PHPUnit Feature tests for new controllers/endpoints
- Writes Unit tests for new models/services
- Runs `php artisan test` and reports results

If tests fail, the orchestrator shows the failure details. The backend agent can then fix the underlying issue, and tests are re-run.

### Phase 5: Review
The **reviewer** agent performs a security + correctness gate:
- Checks for SQL injection, XSS, CSRF vulnerabilities
- Verifies RBAC controls are in place
- Checks file uploads are stored securely (not under `public/`)
- Validates pattern consistency (Form module pattern, hygiene rules)

Reports findings as **PASS**, **WARN**, or **FAIL**:
- **PASS** — Ready to commit
- **WARN** — Non-blocking issues (style, suggestions); code can proceed
- **FAIL** — Blocking issues (security, bugs); must be fixed before commit

If FAIL, the backend agent fixes the issues, and the review is re-run.

### Phase 6: Final Review & Commit
The orchestrator shows a summary:
- Files created/modified
- Format changes applied
- Test results (pass count)
- Review results (pass/warn/fail)

**You then manually run git to commit:**
```bash
git add .
git commit -m "Add FormS renewal workflow..."
git push origin your-branch
```

The orchestrator does **not** run git commands — that stays under your control (per project policy).

## Checkpoint Flow

```
You: /ai-dlc "Add FormS renewal"
Orchestrator: [Planning...]
Orchestrator: Here's the plan:
  - Backend: FormSRenewalController, FormSRenewal model, migration, service
  - UI: renewal form view, success page, confirmation modal
  - Tests: FormSRenewalControllerTest, FormSRenewalModelTest
  [Plan details]

You: looks good
Orchestrator: [Building backend and UI in parallel...]
Orchestrator: Changes ready for review:
  app/Http/Controllers/FormSRenewalController.php [+150 lines]
  app/Models/FormSRenewal.php [+80 lines]
  database/migrations/2024_11_03_123456_create_form_s_renewal_table.php [+50 lines]
  resources/views/form-s/renewal.blade.php [+120 lines]
  [Diff preview]

You: looks good
Orchestrator: [Formatting with Pint and Prettier...]
Orchestrator: Formatted 2 PHP files, 1 view template
Orchestrator: [Running tests...]
Orchestrator: 3 tests written and passing ✓
Orchestrator: [Security + correctness review...]
Orchestrator: Review: PASS ✓ No issues found
Orchestrator: Ready to commit. Git commands are yours to run.

You: git add . && git commit -m "Add FormS renewal workflow" && git push origin feat/form-s-renewal
```

## Error Recovery

If something goes wrong:

**Example: Tests fail**
```
Orchestrator: [Running tests...]
Orchestrator: ✗ Test failed: test_renewal_email_sent
  Error: Call to undefined method FormSRenewal::send_email()

Backend agent: [Fixing...]
Backend agent: Added send_email() method to FormSRenewal model
Orchestrator: [Re-running tests...]
Orchestrator: ✓ All tests pass
```

**Example: Review finds a security issue**
```
Orchestrator: [Security + correctness review...]
Orchestrator: ✗ FAIL: File uploads stored under public/
  Issue: Renewal documents stored in public/form-s-renewal/ with direct web access
  Fix: Move to storage/app/uploads/ and serve via controller route

Backend agent: [Fixing...]
Backend agent: Created storage migration, updated controller to use Storage::download()
Orchestrator: [Re-running review...]
Orchestrator: ✓ Review: PASS
```

## When to Use /ai-dlc

- **Start of day:** You have a feature/bug to work on → `/ai-dlc "description"`
- **Multi-step task:** When your work touches both backend and frontend
- **Zero-context entry:** You want the AI to plan and organize the work for you

## When to Use /ai-dlc-gate Instead

- **End of day:** You've written code manually, want to run format+test+review without the planning phase → `/ai-dlc-gate`
- **Quick spot check:** Want a fast security review of some files without full orchestration

## Troubleshooting

**Q: The orchestrator paused but I want to auto-continue**
A: The stepwise pauses are intentional (per your setup). Code review and testing are important for this codebase given thin test coverage. If you want to make it fully autonomous, ask me to adjust the orchestrator setup.

**Q: A backend/UI change broke something**
A: The reviewer gate will catch most issues. The tester gate ensures tests pass. If something slips through, you'll see it when you manually test in the browser or in the running app.

**Q: Can the orchestrator commit for me?**
A: No, git commits are always manual. This is per project policy (no automatic pushes). You run `git add`, `git commit`, `git push` yourself.

**Q: I want to run just the formatter or tests without the full pipeline**
A: Use `/ai-dlc-gate` to run format+test+review. Or use Bash to run `vendor/bin/pint <path>` or `php artisan test --filter=<name>` directly.
