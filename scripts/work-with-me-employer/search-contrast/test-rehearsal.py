"""Exercise the actual Search rehearsal flow with local command doubles.

No Docker, WordPress, browser or network is used. Every mutation and evidence
boundary can fail independently; snapshots differ between successful applies.
"""
from pathlib import Path
import os
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[3]
SOURCE = (ROOT / 'scripts/ci/rehearse.sh').read_text()
HELPERS = SOURCE[SOURCE.index('search_contrast_lane() {'):SOURCE.index('\nrelease_manifest=')]
NATIVE = SOURCE[SOURCE.index('inst_run() {'):SOURCE.index('core_focus_snapshot_from_log() {')]
INSTALLER = 'search-contrast/install-search-contrast.php'
SNAPS = [f'/backups/search-contrast-before-20261111-06000{i}-{str(i)*8}.json' for i in (1, 2, 3)]
passed = 0

SHIM = r'''
set -uo pipefail
out=$SEARCH_FIXTURE/out; wwm=$SEARCH_FIXTURE/wwm; trace=$SEARCH_FIXTURE/trace; state=$SEARCH_FIXTURE/state
inst=search-contrast/install-search-contrast.php; src=staging39; release_rehearsal=0; fails=0
emit() { printf '%s\n' "$1" >> "$trace"; [ "$(wc -l < "$trace")" != "${FAIL_AT:-0}" ]; }
check() { if [ "$2" -ne 0 ]; then fails=$((fails+1)); printf 'FAIL %s\n' "$1"; fi; }
invalid() { printf 'INVALID %s\n' "$*"; exit 2; }
cleanup() { :; }
load_pages() { emit pages; }
errs() { if [ "${NEW_PHP:-0}" = 1 ] && [ "$(grep -c '^pages$' "$trace")" = 2 ]; then printf 'PHP Warning: fixture new error\n'; fi; return 0; }
fp() {
 emit "fp:$1" || exit 73
 local payload id=29 snippet=same
 payload=$(printf 'a%.0s' {1..64})
 [ "$(cat "$state")" = old ] || payload=$(printf 'b%.0s' {1..64})
 [ "${NO_EFFECT:-0}" != 1 ] || payload=$(printf 'a%.0s' {1..64})
 if [ "${FOREIGN_CHANGE:-0}" = 1 ] && [ "$(cat "$state")" != old ]; then snippet=unexpected; fi
 [ "${DRIFT_FP:-}" != "$1" ] || id=999
 printf 'css\t%s\t%s\npage\tthai\tunchanged\nsnippet\t1380\tpublish:%s\noption\twpcode_snippets\tsame\n' "$id" "$payload" "$snippet" > "$out/$1.content"
 cp "$out/$1.content" "$out/$1.txt"
}
python3() { emit "wording:$(basename "$3")" || return 73; }
timeout() {
 if [ "$1" = 180 ]; then emit baseline-inspection || return 73; printf 'INFO fixture baseline observations only\n'; return "${INSPECT_RC:-0}"; fi
 emit browser || return 73; printf 'INFO fixture browser evidence\n'; return "${BROWSER_RC:-0}"
}
snapshot_marker() {
 local path=$1 hash bytes
 case "$path" in
 "$SNAP1") hash=$(printf 'a%.0s' {1..64}); bytes=101 ;;
 "$SNAP2") hash=$(printf 'b%.0s' {1..64}); bytes=102 ;;
 "$SNAP3") hash=$(printf 'c%.0s' {1..64}); bytes=103 ;;
 *) return 88 ;;
 esac
 printf 'SEARCH-SNAPSHOT %s %s %s\n' "$path" "$hash" "$bytes"
}
write_log() {
 local label=$1 path=$2 action=$3 mode=valid
 [ "${BAD_LOG:-}" != "$label" ] || mode=${BAD_KIND:-missing-marker}
 snapshot_marker "$path" > "$out/$label.log"
 if [ "$action" = apply ]; then printf 'Success: Applied exact search-contrast CSS. Before-snapshot: %s\n' "$path" >> "$out/$label.log"; fi
 case "$mode" in
 missing-marker) sed -i '/^SEARCH-SNAPSHOT /d' "$out/$label.log" ;;
 duplicate-marker) snapshot_marker "$path" >> "$out/$label.log" ;;
 wrong-hash) sed -i 's/ [abc]\{64\} / dddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd /' "$out/$label.log" ;;
 invalid-hash) sed -i 's/ [abc]\{64\} / invalid /' "$out/$label.log" ;;
 invalid-bytes) sed -i 's/ 10[123]$/ 0/' "$out/$label.log" ;;
 wrong-prefix) sed -i 's@/backups/search-contrast-before-@/backups/peek-template-core-quiet-nav-before-@g' "$out/$label.log" ;;
 wrong-directory) sed -i 's@/backups/@/tmp/@g' "$out/$label.log" ;;
 missing-success) sed -i '/^Success: /d' "$out/$label.log" ;;
 duplicate-success) printf 'Success: Applied exact search-contrast CSS. Before-snapshot: %s\n' "$path" >> "$out/$label.log" ;;
 marker-mismatch) sed -i '/^SEARCH-SNAPSHOT /s/-[123]\{8\}\.json/-ffffffff.json/' "$out/$label.log" ;;
 success-prefixed) sed -i 's/^Success:/Deprecated: Success:/' "$out/$label.log" ;;
 esac
}
inst_run() {
 local label=$1 action=$2 path=${3:-} mode=${4:-applied} current expected
 emit "install:$label:$action${path:+:$path}${path:+:$mode}" || return 73
 case "$label" in
 preflight|apply-again) : ;;
 apply) printf apply1 > "$state"; write_log "$label" "$SNAP1" apply ;;
 re-apply) printf apply2 > "$state"; write_log "$label" "${REUSE_SECOND:-$SNAP2}" apply ;;
 reapply-after-browser) printf apply3 > "$state"; write_log "$label" "${REUSE_FINAL:-$SNAP3}" apply ;;
 rollback) [ "$path" = "$SNAP1" ] && [ "$(cat "$state")" = apply1 ] || return 88; printf old > "$state" ;;
 rollback-after-browser) [ "$path" = "$SNAP2" ] && [ "$(cat "$state")" = apply2 ] || return 88; printf old > "$state" ;;
 verify-*)
  [ "$action" = verify ] || return 88; current=$(cat "$state")
  case "$current:$mode" in
   apply1:applied) expected=$SNAP1 ;; apply2:applied) expected=$SNAP2 ;; apply3:applied) expected=$SNAP3 ;;
   old:original) case "$path" in "$SNAP1"|"$SNAP2") expected=$path ;; *) return 88 ;; esac ;;
   *) return 88 ;;
  esac
  [ "$path" = "$expected" ] || return 88
  write_log "$label" "$path" verify ;;
 *) return 89 ;;
 esac
}
'''


def run_case(options=None, expected=None, valid=True, absent_browser=False):
    global passed
    with tempfile.TemporaryDirectory(prefix='search-rehearsal-') as temp:
        base = Path(temp)
        (base / 'out').mkdir()
        (base / 'wwm/search-contrast').mkdir(parents=True)
        (base / 'wwm/search-contrast/check-search-contrast.mjs').write_text('fixture only\n')
        if not absent_browser:
            (base / 'wwm/search-contrast/check-rehearsal.mjs').write_text('fixture only\n')
        (base / 'state').write_text('old')
        env = dict(os.environ, SEARCH_FIXTURE=temp, SNAP1=SNAPS[0], SNAP2=SNAPS[1], SNAP3=SNAPS[2])
        for key in ['FAIL_AT', 'DRIFT_FP', 'NO_EFFECT', 'FOREIGN_CHANGE', 'BAD_LOG', 'BAD_KIND', 'NEW_PHP', 'BROWSER_RC', 'INSPECT_RC', 'REUSE_SECOND', 'REUSE_FINAL']:
            env.pop(key, None)
        env.update(options or {})
        result = subprocess.run(['bash', '-s'], input=SHIM + '\n' + HELPERS + '\nsearch_contrast_rehearse\n', env=env, capture_output=True, text=True)
        trace = (base / 'trace').read_text().splitlines()
        assert (result.returncode == 0) == valid, (options, result.returncode, result.stdout, result.stderr, trace)
        if expected is not None:
            assert trace == expected, (options, expected, trace, result.stdout, result.stderr)
        passed += 1
        return trace


def install(label, action, path='', mode='applied'):
    return f'install:{label}:{action}' + (f':{path}:{mode}' if path else '')


SUCCESS = ['pages', 'baseline-inspection', 'fp:A', install('preflight', 'preflight'), install('apply', 'apply'),
           install('verify-first-apply', 'verify', SNAPS[0]), 'fp:B', install('apply-again', 'apply'),
           install('verify-repeat-apply', 'verify', SNAPS[0]), 'fp:B2', install('rollback', 'rollback', SNAPS[0]),
           install('verify-first-original', 'verify', SNAPS[0], 'original'), 'fp:C', install('re-apply', 'apply'),
           install('verify-reapply', 'verify', SNAPS[1]), 'fp:D', 'pages', 'wording:verify-home.py',
           'wording:verify-client-pages.py', install('verify-before-browser', 'verify', SNAPS[1]), 'browser',
           install('verify-after-browser', 'verify', SNAPS[1]), install('rollback-after-browser', 'rollback', SNAPS[1]),
           install('verify-original-after-browser', 'verify', SNAPS[1], 'original'), 'fp:E',
           install('reapply-after-browser', 'apply'), install('verify-final-reapply', 'verify', SNAPS[2]), 'fp:F']
run_case(expected=SUCCESS)
for at in range(1, len(SUCCESS) + 1):
    run_case({'FAIL_AT': str(at)}, SUCCESS[:at], valid=False)
for point in ('B2', 'C', 'D', 'E', 'F'):
    stop = SUCCESS.index('fp:' + point) + 1
    run_case({'DRIFT_FP': point}, SUCCESS[:stop], valid=False)
run_case({'NO_EFFECT': '1'}, SUCCESS[:7], valid=False)
run_case({'FOREIGN_CHANGE': '1'}, SUCCESS[:7], valid=False)
run_case({'NEW_PHP': '1'}, SUCCESS[:17], valid=False)
for status in ('1', '2', '124'):
    run_case({'BROWSER_RC': status}, SUCCESS[:21], valid=False)
    run_case({'INSPECT_RC': status}, SUCCESS[:2], valid=False)
run_case(expected=SUCCESS[:20], valid=False, absent_browser=True)
run_case({'REUSE_SECOND': SNAPS[0]}, SUCCESS[:14], valid=False)
for path in SNAPS[:2]:
    run_case({'REUSE_FINAL': path}, SUCCESS[:26], valid=False)
for label, stop in [('apply', 5), ('re-apply', 14), ('reapply-after-browser', 26)]:
    for kind in ['missing-marker', 'duplicate-marker', 'invalid-hash', 'invalid-bytes', 'wrong-prefix',
                 'wrong-directory', 'missing-success', 'duplicate-success', 'marker-mismatch', 'success-prefixed']:
        run_case({'BAD_LOG': label, 'BAD_KIND': kind}, SUCCESS[:stop], valid=False)
for label in ['verify-first-apply', 'verify-repeat-apply', 'verify-first-original', 'verify-reapply',
              'verify-before-browser', 'verify-after-browser', 'verify-original-after-browser', 'verify-final-reapply']:
    stop = next(i + 1 for i, value in enumerate(SUCCESS) if value.startswith('install:' + label + ':'))
    for kind in ['missing-marker', 'duplicate-marker', 'wrong-hash', 'invalid-bytes', 'marker-mismatch']:
        run_case({'BAD_LOG': label, 'BAD_KIND': kind}, SUCCESS[:stop], valid=False)

# Run the exact early scope check; no source discovery or credentials are touched.
scope = SOURCE[SOURCE.index('release_rehearsal=0\n'):SOURCE.index('case "$src" in')]
for src, uploads, valid in [('staging39', '1', True), ('staging39', '0', False), ('staging38', '1', False), ('live', '1', False)]:
    prefix = 'invalid() { exit 2; }; src=$1; uploads=$2; inst=' + INSTALLER + '\n'
    result = subprocess.run(['bash', '-s', '--', src, uploads], input=prefix + scope + '\n[ "$release_rehearsal" = 0 ] && [ "$read_only_discovery" = 1 ] && [ "$copy_isolation" = 1 ]\n', capture_output=True, text=True)
    assert result.returncode == (0 if valid else 2), (src, uploads, result.stdout, result.stderr)
    passed += 1

# Native stdout and stderr must remain exact and every writer's failure matters.
for broken in ['none', 'wpc', 'stdout-tee', 'stderr-tee', 'stdout-sed', 'stderr-sed']:
    with tempfile.TemporaryDirectory(prefix='search-native-streams-') as temp:
        shim = r'''set -uo pipefail
out=$1; broken=$2; release_rehearsal=0; inst=search-contrast/install-search-contrast.php
core_guarded_lane() { return 1; }
wpc() { printf 'Success: fixture output\n'; printf 'Deprecated: fixture stderr\n' >&2; [ "$broken" != wpc ]; }
tee() { local stream=stdout; [[ "$1" != *.stderr.log ]] || stream=stderr; command tee "$@"; [ "$broken" != "$stream-tee" ]; }
sed() { local stream=stdout; [ "$#" -eq 1 ] || stream=stderr; command sed "$@"; [ "$broken" != "$stream-sed" ]; }
'''
        result = subprocess.run(['bash', '-s', '--', temp, broken], input=shim + HELPERS + '\n' + NATIVE + '\ninst_run probe apply\n', capture_output=True, text=True)
        assert (result.returncode == 0) == (broken == 'none'), (broken, result.stdout, result.stderr)
        assert (Path(temp) / 'probe.log').read_text() == 'Success: fixture output\n'
        assert (Path(temp) / 'probe.stderr.log').read_text() == 'Deprecated: fixture stderr\n'
        passed += 1

# Retain complete evidence on success/failure without exporting private material.
for original, broken in [(0, False), (7, False), (0, True)]:
    with tempfile.TemporaryDirectory(prefix='search-native-retention-') as temp:
        base = Path(temp)
        (base / 'out').mkdir()
        allowed = ['apply.log', 'apply.stderr.log', 'A.content', 'errors-after.txt']
        for name in allowed + ['private-snapshot.json', 'db.sql', 'copied-source.php', 'additional.css']:
            (base / 'out' / name).write_text('fixture ' + name)
        shim = r'''set -uo pipefail
RUNNER_TEMP=$1; out=$1/out; broken=$2
cleanup() { printf done > "$RUNNER_TEMP/cleaned"; }
cp() { [ "$broken" != yes ] || return 73; command cp "$@"; }
'''
        result = subprocess.run(['bash', '-s', '--', temp, 'yes' if broken else 'no'], input=shim + HELPERS + '\ntrap search_contrast_cleanup EXIT\nexit ' + str(original) + '\n', capture_output=True, text=True)
        assert result.returncode == (1 if broken else original), (original, broken, result.stdout, result.stderr)
        assert (base / 'cleaned').read_text() == 'done'
        files = sorted(path.name for path in (base / 'ops-out/search-contrast-native').iterdir())
        assert files == ([] if broken else sorted(allowed)), files
        passed += 1

print(f'Search rehearsal snapshot/order/recovery harness: {passed} cases passed; no Docker, browser, services or network.')
