"""Execute core-focus rehearsal orchestration with local Bash function doubles.

Runs the actual helper/baseline/browser/recovery source, without Docker, a
browser, WordPress or network. Snapshot states deliberately differ between
applies while content fingerprints stay equal, exposing wrong-snapshot reuse.
"""
from pathlib import Path
import hashlib
import os
import re
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[4]
SOURCE = (ROOT / 'scripts/ci/rehearse.sh').read_text()
HELPERS = SOURCE[SOURCE.index('core_focus_snapshot_from_log() {'):SOURCE.index('\nif [ "$inst" = link-ideas/rail-input/install-rail-input.php ]; then')]
BODY = SOURCE[SOURCE.index('echo; echo "[3] baseline: fingerprint A and the 10 pages"'):]
# Preserve the already-approved Rail observation and recovery branches byte-for-byte.
rail_blocks=[]
for match in re.finditer(r'^if \[ "\$inst" = link-ideas/rail-input/install-rail-input.php \]; then$', SOURCE, re.M):
    end=SOURCE.index('\nfi\n',match.start())+4
    rail_blocks.append(hashlib.sha256(SOURCE[match.start():end].encode()).hexdigest())
assert rail_blocks == ['53c120e2225789e2e6bceb524af0932dbc13fef22c9dd7746876950e5bfa41e4',
                       'c7bd30bf1a6811d38d904e0f37ddb3a364f8cf9081899154908358dab1acd89f'], rail_blocks
SNAPS = ['/backups/peek-template-core-input-focus-before-20261109-23595'+str(i)+'-'+str(i)*8+'.json' for i in [1,2,3]]
passed=0

SHIM = r'''
set -uo pipefail
out=$CORE_FIXTURE/out; wwm=$CORE_FIXTURE/wwm; bk=$CORE_FIXTURE/backups
trace=$CORE_FIXTURE/trace; state=$CORE_FIXTURE/state
inst=link-ideas/core-input-focus/install-peek-core-input-focus.php
src=staging39; fails=0
emit() {
  printf '%s\n' "$1" >> "$trace"
  [ "$(wc -l < "$trace")" != "${FAIL_AT:-0}" ]
}
invalid() { printf 'INVALID: %s\n' "$*"; exit 2; }
check() { if [ "$2" -ne 0 ]; then fails=$((fails+1)); printf 'FAIL %s\n' "$1"; fi; }
load_pages() { emit pages; }
errs() { :; }
python3() { emit "wording:$(basename "$3")" || return 73; }
fp() {
  emit "fp:$1" || exit 73
  local payload=old id=1380
  [ "$(cat "$state")" = old ] || payload=new
  [ "${DRIFT_FP:-}" != "$1" ] || id=9999
  printf 'snippet\t%s\tpublish:%s\nseo\t%s:setting\tsame\npage\tthai\tsame\noption\twpcode_snippets\tcache-%s\n' "$id" "$payload" "$id" "$1" > "$out/$1.content"
  # Idempotence compares B/B2 directly, including the unchanged cache value.
  [ "$1" != B2 ] || sed -i 's/cache-B2$/cache-B/' "$out/$1.content"
  cp "$out/$1.content" "$out/$1.txt"
}
write_snapshot_log() {
  local label=$1 snapshot=$2 mode=valid
  [ "${BAD_LOG:-}" != "$label" ] || mode=${BAD_LOG_KIND:-missing}
  printf 'Old unrelated snapshot: /backups/peek-template-page-thai-massage-before-20261109-235959-aaaaaaaa.json\n' > "$out/$label.log"
  case "$mode" in
    missing) : ;;
    wrong-prefix) printf 'Success: Applied. Before-snapshot: /backups/peek-template-core-kiro-fixes-before-20261109-235959-aaaaaaaa.json\n' >> "$out/$label.log" ;;
    duplicate) printf 'Success: Applied. Before-snapshot: %s\nSuccess: Applied. Before-snapshot: %s\n' "$snapshot" "$snapshot" >> "$out/$label.log" ;;
    *) printf 'Success: Applied. Before-snapshot: %s\n' "$snapshot" >> "$out/$label.log" ;;
  esac
}
inst_run() {
  local label=$1 action=$2 path=${3:-}
  emit "install:$label:$action${path:+:$path}" || return 73
  case "$label" in
    preflight) : ;;
    apply) printf apply1 > "$state"; write_snapshot_log "$label" "$SNAP1" ;;
    apply-again) : ;;
    rollback) [ "$path" = "$SNAP1" ] && [ "$(cat "$state")" = apply1 ] || return 88; printf old > "$state" ;;
    re-apply) printf apply2 > "$state"; write_snapshot_log "$label" "$SNAP2" ;;
    rollback-after-browser) [ "$path" = "$SNAP2" ] && [ "$(cat "$state")" = apply2 ] || return 88; printf old > "$state" ;;
    reapply-after-browser) printf apply3 > "$state"; write_snapshot_log "$label" "$SNAP3" ;;
    *) return 89 ;;
  esac
}
wpc() {
  if [ "$1" = eval ]; then emit inventory || return 73; printf '{"pages":[]}\n'; return 0; fi
  [ "$1" = --user=1 ] && [ "$2" = eval-file ] && [ "$3" = /wwm/link-ideas/install-peek-nav.php ] && [ "$4" = verify ] || return 89
  local path=$5 mode=${6:-applied} current expected
  emit "verify:$path:$mode" || return 73
  current=$(cat "$state")
  case "$current:$mode" in
    apply2:applied) expected=$SNAP2 ;;
    apply3:applied) expected=$SNAP3 ;;
    old:original) expected=$SNAP2 ;;
    *) return 88 ;;
  esac
  [ "$path" = "$expected" ] || return 88
}
cp() {
  if [ "${COPY_LOG_FAIL:-0}" = 1 ] && [ "$1" = "$out/package-browser.log" ]; then emit retain-log-failed; return 73; fi
  command cp "$@"
}
timeout() { emit browser || return 73; printf 'INFO simulated browser gate passed\n'; return "${BROWSER_RC:-0}"; }
'''


def run_case(*, options=None, expected=None, valid=True, absent_browser=False):
    global passed
    with tempfile.TemporaryDirectory(prefix='core-rehearsal-') as temp:
        base=Path(temp)
        for name in ['out','backups','wwm/link-ideas/core-input-focus']:(base/name).mkdir(parents=True,exist_ok=True)
        if not absent_browser:(base/'wwm/link-ideas/core-input-focus/check-rehearsal.mjs').write_text('local stub\n')
        # A newer unrelated file must never win over the actual returned path.
        (base/'backups/unrelated-latest.json').write_text('{}')
        (base/'state').write_text('old')
        env=dict(os.environ,CORE_FIXTURE=str(base),RUNNER_TEMP=str(base/'runner'),SNAP1=SNAPS[0],SNAP2=SNAPS[1],SNAP3=SNAPS[2])
        for key in ['FAIL_AT','BAD_LOG','BAD_LOG_KIND','DRIFT_FP','BROWSER_RC','COPY_LOG_FAIL']:env.pop(key,None)
        env.update(options or {})
        result=subprocess.run(['bash','-s'],input=SHIM+'\n'+HELPERS+'\n'+BODY,env=env,capture_output=True,text=True)
        actual=(base/'trace').read_text().splitlines() if (base/'trace').exists() else []
        assert (result.returncode==0)==valid,('exit',options,result.returncode,result.stdout,result.stderr,actual)
        if expected is not None:assert actual==expected,('order',options,expected,actual,result.stdout,result.stderr)
        passed+=1
        return actual,result.stdout


SUCCESS=['fp:A','pages','install:preflight:preflight','install:apply:apply','fp:B',
         'install:apply-again:apply','fp:B2','install:rollback:rollback:'+SNAPS[0],
         'fp:C','install:re-apply:apply','fp:D','pages','wording:verify-home.py',
         'wording:verify-client-pages.py','verify:'+SNAPS[1]+':applied','inventory','browser',
         'verify:'+SNAPS[1]+':applied','install:rollback-after-browser:rollback:'+SNAPS[1],
         'verify:'+SNAPS[1]+':original','fp:E','install:reapply-after-browser:apply',
         'verify:'+SNAPS[2]+':applied','fp:F']
run_case(expected=SUCCESS)
# The pre-browser exact verifier fails closed before inventory or browser starts.
run_case(options={'FAIL_AT':'15'},expected=SUCCESS[:15],valid=False)
run_case(options={'FAIL_AT':'16'},expected=SUCCESS[:16],valid=False)
# Earlier exact-fingerprint/wording failures block the new lane's browser.
run_case(options={'DRIFT_FP':'D'},expected=SUCCESS[:14],valid=False)
for at in [13,14]:
    run_case(options={'FAIL_AT':str(at)},expected=SUCCESS[:14],valid=False)
run_case(options={'COPY_LOG_FAIL':'1'},expected=SUCCESS[:17]+['retain-log-failed'],valid=False)
# Every post-browser mutation is conditional on the preceding verification gate.
for at in [18,19,20,21,22,23,24]:
    run_case(options={'FAIL_AT':str(at)},expected=SUCCESS[:at],valid=False)
run_case(options={'BROWSER_RC':'1'},expected=SUCCESS[:17],valid=False)
run_case(options={'BROWSER_RC':'2'},expected=SUCCESS[:17],valid=False)
run_case(expected=SUCCESS[:15],valid=False,absent_browser=True)
for point,stop in [('E',21),('F',24)]:
    run_case(options={'DRIFT_FP':point},expected=SUCCESS[:stop],valid=False)
# A different core/SEO ID with equal content must fail; ID normalization would
# turn these into a pass and (for E) incorrectly allow another apply.
for label in ['apply','re-apply','reapply-after-browser']:
    for kind in ['missing','wrong-prefix','duplicate']:
        actual,_=run_case(options={'BAD_LOG':label,'BAD_LOG_KIND':kind},valid=False)
        if label=='apply':
            assert not any(e.startswith('install:rollback') or e=='browser' for e in actual),actual
        elif label=='re-apply':assert 'browser' not in actual,actual
        else:assert actual==SUCCESS[:22],actual
# Exercise the actual path parser in isolation, including multiple different
# snapshots and unrelated noise, with no copied parser implementation.
for lines,want in [
    (['Success: Applied. Before-snapshot: '+SNAPS[0]],SNAPS[0]),
    (['unrelated '+SNAPS[1],'Success: Applied. Before-snapshot: '+SNAPS[2]],SNAPS[2]),
    ([],None),
    (['Success: Applied. Before-snapshot: '+SNAPS[0],'Success: Applied. Before-snapshot: '+SNAPS[1]],None),
    (['Success: Applied. Before-snapshot: '+SNAPS[0].replace('/backups/','/tmp/')],None),
    (['Success: Applied. Before-snapshot: '+SNAPS[0].replace('core-input-focus','core-kiro-fixes')],None),
    (['Success: Applied. Before-snapshot: '+SNAPS[0]+';id'],None),
]:
    with tempfile.TemporaryDirectory(prefix='core-snapshot-parser-') as temp:
        path=Path(temp)/'apply.log';path.write_text('\n'.join(lines)+'\n')
        result=subprocess.run(['bash','-s','--',str(path)],input=HELPERS+'\ncore_focus_snapshot_from_log "$1"\n',capture_output=True,text=True)
        assert (result.returncode==0)==(want is not None),(lines,result.returncode,result.stdout)
        if want is not None:assert result.stdout.strip()==want,(want,result.stdout)
        passed+=1
# Verification must also reject failed evidence writers even if WP succeeds.
for broken in ['none','wpc','tee','sed']:
    with tempfile.TemporaryDirectory(prefix='core-verify-pipeline-') as temp:
        prefix = r'''set -uo pipefail
out=$1; broken=$2
wpc() { printf 'verified fixture\n'; [ "$broken" != wpc ]; }
tee() { command tee "$@"; [ "$broken" != tee ]; }
sed() { command sed "$@"; [ "$broken" != sed ]; }
'''
        result=subprocess.run(['bash','-s','--',temp,broken],input=prefix+HELPERS+'\ncore_focus_verify test "'+SNAPS[1]+'"\n',capture_output=True,text=True)
        assert (result.returncode==0)==(broken=='none'),(broken,result.returncode,result.stdout,result.stderr)
        passed+=1
print(f'Core input-focus rehearsal snapshot/order/recovery harness: {passed} cases passed; no Docker, browser, services or network.')
