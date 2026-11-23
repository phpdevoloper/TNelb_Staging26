// PostToolUse hook: run `php -l` on edited .php files. Reads the hook JSON from stdin.
// Uses node (always present with npx) instead of jq, which is not installed on this machine.
let input = '';
process.stdin.on('data', (d) => (input += d));
process.stdin.on('end', () => {
    try {
        const data = JSON.parse(input);
        const file = (data.tool_input && data.tool_input.file_path) || (data.tool_response && data.tool_response.filePath);
        if (!file || !/\.php$/i.test(file) || /\.blade\.php$/i.test(file)) return;
        const r = require('child_process').spawnSync('php', ['-l', file], { encoding: 'utf8' });
        if (r.status !== 0) {
            process.stderr.write((r.stdout || '') + (r.stderr || ''));
            process.exit(2); // surface the syntax error to Claude
        }
    } catch (e) {
        /* never block editing because of the hook itself */
    }
});
