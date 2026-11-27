---
name: ai-dlc-gate
description: Standalone format + test + review gate (no planning phase)
---

# /ai-dlc-gate Command

**Usage:** `/ai-dlc-gate` (no arguments)

**Example:**
```
/ai-dlc-gate
```

This is a **quick gate** that runs the format → test → review sequence **without the planning phase**. Use it when:
- You've already written code manually and want to format + test + review it
- You want a fast security check on changed files
- End of day: verify your work passes all gates before committing tomorrow

## How It Works

The gate runs:

1. **Format** — Pint on PHP, Prettier on JS/CSS (changed files only)
2. **Test** — PHPUnit on affected test files (or all tests if unsure)
3. **Review** — Security + correctness gate

After each phase it ticks the matching line in `TASKS.md` (see below). Then it pauses with a summary:
- What was formatted
- Test results (pass/fail)
- Review results (pass/warn/fail)

If any phase fails:
- **Format issues:** Show what would be reformatted; you can re-run manually if needed
- **Test failures:** Show the failing test output; backend agent can fix, then re-run
- **Review failures (FAIL):** Show blocking issues; must be fixed before code is safe

## Updating TASKS.md

The gate reads `TASKS.md` first and records its results there. The orchestrator owns all edits to the file (the formatter and reviewer agents have no Edit/Write tools).

1. **Find the matching task:** look under `## Current` for a `### <task>` section that covers the changed files. If there is exactly one active task, use it. If there are several, ask the user which one. If there is none, skip the update and do not create a section.
2. **Tick the gate steps:** mark the task's Format, Test and Review lines `[x]` as each phase passes. If a step line is missing, add it, e.g. `- [x] Format with Pint/Prettier (formatter)`.
3. **On failure:** leave the step unticked and add `- [ ] Fix: <short issue> (laravel-backend)` for each failing test or review FAIL. Review WARN findings do not block ticking, but add them as `- [ ] Warn: <short issue>` lines.
4. **When everything passes:** do not move the section to `## Done`. That happens only when the user confirms the work is finished.
5. **Re-runs:** when the gate is re-run after fixes, tick the Fix lines that are now resolved.
6. Keep entries to one line each. No code, diffs or test output in `TASKS.md`.

## Typical Workflow

**Scenario: You've written FormP support code manually during the day**

```
You: [finish work on FormP renewal feature]
You: /ai-dlc-gate

Orchestrator: [Checking changed files...]
Orchestrator: Detected changes in:
  app/Http/Controllers/FormPController.php
  app/Models/FormP.php
  database/migrations/2024_11_03_create_form_p_table.php
  resources/views/form-p/create.blade.php

Orchestrator: [Formatting...]
Orchestrator: ✓ Pint formatted 2 PHP files
Orchestrator: [Running tests...]
Orchestrator: ✓ 4 tests pass

Orchestrator: [Security review...]
Orchestrator: ✓ PASS — No issues found

You: Ready to commit!
```

## Difference from /ai-dlc

| Phase | /ai-dlc | /ai-dlc-gate |
|-------|---------|--------------|
| Plan | ✓ (pauses) | ✗ |
| Build | ✓ (pauses) | ✗ |
| Format | ✓ | ✓ |
| Test | ✓ | ✓ |
| Review | ✓ (pauses) | ✓ (pauses) |

**Use /ai-dlc** when: AI should plan and build the feature for you.
**Use /ai-dlc-gate** when: You've already written the code, just need to verify it passes quality gates.

## When to Use

- **End of day:** Before you leave, run the gate to catch any issues
- **Code review prep:** Before asking a teammate to review, verify automated gates pass
- **Spot check:** Quick security review of sensitive files (uploads, payment flow, auth)
- **Manual coding:** You wrote something yourself and want to verify it before commit

## Troubleshooting

**Q: The gate found formatting issues**
A: Pint/Prettier can auto-fix most. Run them again or adjust the code manually.

**Q: Tests failed**
A: Run the failing test manually to understand why: `php artisan test --filter=test_name`. The backend agent can fix logical issues in the code.

**Q: Review found a FAIL (security issue)**
A: This blocks commit. Fix the issue (usually backend/security-related), then re-run the gate.

**Q: What if I want to force through a review finding?**
A: Don't. Review findings are **blocking** for a reason (security, correctness). Fix them properly, then re-run. If you believe a finding is a false positive, discuss it; don't bypass the gate.
