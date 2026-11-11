#!/usr/bin/env bash
# Supplemental Linux WebKit checks of the current, unmodified rendered site.
# This is not Safari, physical-device, assistive-technology or native-swipe proof.
# Site ops may run this read-only wrapper on staging39. Local runs use an
# already installed Playwright; only an Actions runner installs dependencies.
set -euo pipefail
umask 077

invalid() { printf 'INVALID Peek WebKit: %s\n' "$*"; exit 2; }
[[ $# == 1 ]] || invalid 'expected one exact staging39 or isolated localhost origin'
base=$1
case "$base" in
  https://staging39.thaisolitude.com|http://localhost:8090|http://localhost:8091) ;;
  *) invalid 'exact staging39 or isolated localhost origin only' ;;
esac
[[ ${S_TARGET:-staging39} == staging39 ]] || invalid 'S_TARGET must be staging39'
here=$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd)
log_parent="${RUNNER_TEMP:-${TMPDIR:-/tmp}}/ops-out"
mkdir -p -- "$log_parent" || invalid 'cannot create complete-log directory'
log_dir=$(mktemp -d "$log_parent/peek-webkit.XXXXXX") || invalid 'cannot allocate complete-log directory'
printf 'INFO Peek WebKit: complete logs: %s\n' "$log_dir"

if [[ ${GITHUB_ACTIONS:-false} == true ]]; then
  # Reuse the repository's pinned package installer, without its Chromium/Firefox
  # browser flag. Resolve the exact installed CLI below; never fetch latest via npx.
  if ! bash "$here/../../ci/install-test-tools.sh" >"$log_dir/install-tools.log" 2>&1; then
    cat "$log_dir/install-tools.log"
    invalid 'pinned test-tool installation failed; no browser checks ran'
  fi
  cat "$log_dir/install-tools.log"
  export TS_PLAYWRIGHT_ROOT="$HOME/.cache/ts-playwright/package.json"
fi
[[ -n ${TS_PLAYWRIGHT_ROOT:-} ]] || invalid 'local runs require an installed TS_PLAYWRIGHT_ROOT'
if ! cli=$(node --input-type=module - 2>"$log_dir/pin-check.stderr.log" <<'NODE'
import { createRequire } from 'node:module';
import { dirname, join } from 'node:path';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT);
const version = require('playwright/package.json').version;
if (version !== '1.63.0') throw new Error(`expected pinned Playwright 1.63.0, found ${version}`);
console.log(join(dirname(require.resolve('playwright/package.json')), 'cli.js'));
NODE
); then cat "$log_dir/pin-check.stderr.log"; invalid 'pinned Playwright package unavailable; no browser checks ran'; fi
printf 'INFO Peek WebKit: Playwright 1.63.0; trusted browser taps, keyboard and mouse; no native swipe or Safari claim.\n'

if [[ ${GITHUB_ACTIONS:-false} == true ]]; then
  deps=(node "$cli" install-deps webkit)
  if [[ $(id -u) != 0 ]]; then deps=(sudo -n env "PATH=$PATH" "${deps[@]}"); fi
  if ! "${deps[@]}" >"$log_dir/install-webkit-deps.log" 2>&1; then
    cat "$log_dir/install-webkit-deps.log"
    invalid 'official WebKit system dependency installation failed; no browser checks ran'
  fi
  cat "$log_dir/install-webkit-deps.log"
  if ! node "$cli" install webkit >"$log_dir/install-webkit.log" 2>&1; then
    cat "$log_dir/install-webkit.log"
    invalid 'official pinned WebKit installation failed; no browser checks ran'
  fi
  cat "$log_dir/install-webkit.log"
fi

# Shell redirection retains the browser's complete output even when the checker
# exits early. No browser process inherits the parser's input or output pipes.
status=0
for suite in disclosures layout; do
  case "$suite" in
    disclosures) checker=check-peek-disclosures.mjs ;;
    layout) checker=check-peek-kiro-layout.mjs ;;
  esac
  if node "$here/$checker" "$base" --webkit >"$log_dir/$suite.stdout.log" 2>"$log_dir/$suite.stderr.log"; then child_status=0; else child_status=$?; fi
  if node --input-type=module - "$suite" "$child_status" "$log_dir" <<'NODE'
import { readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
const [name, childStatus, logDir] = process.argv.slice(2);
const specs = {
  disclosures: { prefix: 'disclosure webkit/',
    summary: /^INFO Peek disclosures webkit: (\d+) passed, (\d+) failed; 10 Privacy loads, 3 captures; \d+s\.$/,
    imagePrefix: 'peek-disclosure-webkit-', captures: 3 },
  layout: { prefix: 'kiro-layout webkit/',
    summary: /^INFO Peek Kiro layout webkit: (\d+) passed, (\d+) failed; 8 actual routes, 19 page loads, 16 captures, \d+s; trusted Playwright taps and keyboard\/mouse; no swipe, Safari, physical-device or assistive-technology claim, root text enlargement is not OS text scale\.$/,
    imagePrefix: 'kiro-layout-webkit-', captures: 16 }
};
const spec = specs[name], logs = { stdout: join(logDir, `${name}.stdout.log`), stderr: join(logDir, `${name}.stderr.log`) };
const summaries = [], captures = new Set(), invalid = [];
let passed = 0, failed = 0;
try {
  for (const file of Object.values(logs)) for (const line of readFileSync(file, 'utf8').split(/\r?\n/)) {
    if (line && !line.startsWith('PASS ')) process.stdout.write(line + '\n');
    if (line.startsWith(`PASS ${spec.prefix}`)) passed++;
    else if (line.startsWith(`FAIL ${spec.prefix}`)) failed++;
    else if (/^(PASS|FAIL)(?:\s|$)/.test(line)) invalid.push('unexpected assertion source');
    if (/^INFO Peek (?:disclosures|Kiro layout) /.test(line)) summaries.push(line);
    if (/^(?:INVALID|FAIL)(?:\s|$)/.test(line) && !line.startsWith(`FAIL ${spec.prefix}`)) invalid.push(line);
    if (line.startsWith('INLINE ')) {
      const match = /^INLINE ([a-zA-Z0-9_.-]+\.jpg) base64:([A-Za-z0-9+/]+={0,2})$/.exec(line);
      const jpeg = match && Buffer.from(match[2], 'base64');
      if (!match || !match[1].startsWith(spec.imagePrefix) || captures.has(match[1]) || jpeg.length < 4 || jpeg[0] !== 0xff || jpeg[1] !== 0xd8 || jpeg.at(-2) !== 0xff || jpeg.at(-1) !== 0xd9) invalid.push('missing, duplicate or malformed screenshot evidence');
      else captures.add(match[1]);
    }
  }
} catch (error) { invalid.push(`cannot read complete logs: ${error.message}`); }
const summary = summaries.length === 1 && spec.summary.exec(summaries[0]);
if (!summary || passed + failed === 0 || Number(summary[1]) !== passed || Number(summary[2]) !== failed || captures.size !== spec.captures) invalid.push('complete nonempty assertion, page-load and screenshot evidence missing or inconsistent');
const code = failed ? 1 : Number(childStatus) !== 0 || invalid.length ? 2 : 0;
const result = { name, code, exitCode: Number(childStatus), passed, failed, captures: captures.size, summary: summaries[0] || null, logs, invalid };
try { writeFileSync(join(logDir, `${name}.result.json`), JSON.stringify(result, null, 2) + '\n', { flag: 'wx', mode: 0o600 }); }
catch (error) { console.log(`INVALID Peek WebKit ${name}: cannot retain final evidence: ${error.message}`); process.exit(2); }
console.log(`INFO Peek WebKit suite ${name}: child_exit=${childStatus}; effective_exit=${code}; ${passed} passed, ${failed} failed; stdout=${logs.stdout}; stderr=${logs.stderr}`);
for (const reason of invalid) console.log(`INVALID Peek WebKit ${name}: ${reason}`);
process.exit(code);
NODE
  then status=0; else status=$?; fi
  if [[ $status != 0 ]]; then
    printf 'INFO Peek WebKit: stop after %s; remaining suites skipped, no further page loads.\n' "$suite"
    break
  fi
  # Each child spaces its public document loads by 5.5 seconds. The disclosure
  # suite also pauses after its final page, covering this suite boundary.
done
node --input-type=module - "$base" "$log_dir" "$status" <<'NODE'
import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';
const [base, logDir, status] = process.argv.slice(2), results = [];
let code = Number(status);
try {
  for (const name of ['disclosures', 'layout']) {
    const file = join(logDir, `${name}.result.json`);
    if (existsSync(file)) results.push(JSON.parse(readFileSync(file, 'utf8')));
  }
  // A parser/process exit without recorded failed assertions is invalid
  // evidence, not a browser assertion failure.
  code = results.some(result => result.failed > 0) ? 1 : code !== 0 || results.length !== 2 || results.some(result => result.code !== 0) ? 2 : 0;
  writeFileSync(join(logDir, 'results.json'), JSON.stringify({ base, engine: 'webkit', results }, null, 2) + '\n', { flag: 'wx', mode: 0o600 });
} catch (error) { console.log(`INVALID Peek WebKit: cannot retain final evidence: ${error.message}`); code = code || 2; }
const passed = results.reduce((sum, result) => sum + result.passed, 0), failed = results.reduce((sum, result) => sum + result.failed, 0);
console.log(`INFO Peek WebKit supplemental verification: ${code === 2 ? 'INVALID' : code ? 'FAILED' : 'PASSED'}, ${results.length}/2 suites, ${passed} passed, ${failed} failed; complete logs: ${logDir}`);
process.exit(code);
NODE
