from pathlib import Path

root = Path(__file__).resolve().parents[1]
lines = (root / "resources/views/user_login/apply-form-s.blade.php").read_text(encoding="utf-8").splitlines()
work = "\n".join(lines[450:1136])
fa = "\n".join(lines[1414:1432])
header = "@if (($editFormName ?? ($application_details->form_name ?? '')) === 'S')"
footer = """
    /* Edit page: collapsed saved rows show in summary table only */
    .work-row.is-complete:not(.work-row--expanded) {
        display: none !important;
    }
@endif
"""
out = root / "resources/views/user_login/partials/form-s-work-exp-styles.blade.php"
out.write_text(header + "\n" + work + "\n" + fa + footer, encoding="utf-8")
print("Wrote", out, "—", len(work.splitlines()), "CSS lines")
