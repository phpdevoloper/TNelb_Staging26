<?php

$root = dirname(__DIR__);
$src = $root . '/resources/views/user_login/apply-form-s.blade.php';
$lines = file($src, FILE_IGNORE_NEW_LINES);
if ($lines === false) {
    fwrite(STDERR, "Cannot read $src\n");
    exit(1);
}

$work = implode("\n", array_slice($lines, 450, 1136 - 450));
$fa = implode("\n", array_slice($lines, 1414, 1432 - 1414));

$header = "@if ((\$editFormName ?? (\$application_details->form_name ?? '')) === 'S')";
$footer = <<<'CSS'

    /* Edit page: collapsed saved rows show in summary table only */
    .work-row.is-complete:not(.work-row--expanded) {
        display: none !important;
    }
@endif
CSS;

$out = $root . '/resources/views/user_login/partials/form-s-work-exp-styles.blade.php';
$content = $header . "\n" . $work . "\n" . $fa . $footer;
file_put_contents($out, $content);

echo "Wrote $out — " . substr_count($work, "\n") + 1 . " CSS lines\n";
