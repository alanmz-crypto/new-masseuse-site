"""Execute core-neighbours rehearsal orchestration with local Bash function doubles.

Runs the actual helper/baseline/browser/recovery source, without Docker, a
browser, WordPress or network. Snapshot states deliberately differ between
applies while content fingerprints stay equal, exposing wrong-snapshot reuse.
"""
from pathlib import Path
import ast
import hashlib
import json
import os
import re
import subprocess
import tempfile

PROFILE = globals().get('CORE_REHEARSAL_PROFILE', 'neighbours')
assert PROFILE in ('neighbours', 'index-clearance', 'quiet-nav')
COMPONENT = 'core-' + PROFILE
INSTALLER = 'link-ideas/' + COMPONENT + '/install-peek-' + COMPONENT + '.php'
ROOT = Path(__file__).resolve().parents[4]
SOURCE = (ROOT / 'scripts/ci/rehearse.sh').read_text()
HELPERS = SOURCE[SOURCE.index('core_focus_snapshot_from_log() {'):SOURCE.index('\nrelease_manifest=')]
BODY = SOURCE[SOURCE.index('echo; echo "[3] baseline:'):]
# Preserve the already-approved Rail observation and recovery branches byte-for-byte.
rail_blocks=[]
for match in re.finditer(r'^if \[ "\$inst" = link-ideas/rail-input/install-rail-input.php \]; then$', SOURCE, re.M):
    end=SOURCE.index('\nfi\n',match.start())+4
    rail_blocks.append(hashlib.sha256(SOURCE[match.start():end].encode()).hexdigest())
assert rail_blocks == ['53c120e2225789e2e6bceb524af0932dbc13fef22c9dd7746876950e5bfa41e4',
                       'c7bd30bf1a6811d38d904e0f37ddb3a364f8cf9081899154908358dab1acd89f'], rail_blocks
SNAPS = ['/backups/peek-template-' + COMPONENT + '-before-20261109-23595'+str(i)+'-'+str(i)*8+'.json' for i in [1,2,3]]
passed=0

SHIM = r'''
set -uo pipefail
out=$CORE_FIXTURE/out; wwm=$CORE_FIXTURE/wwm; bk=$CORE_FIXTURE/backups
trace=$CORE_FIXTURE/trace; state=$CORE_FIXTURE/state
inst=link-ideas/core-neighbours/install-peek-core-neighbours.php
cleanup() { :; }
src=staging39; fails=0; release_rehearsal=0; pages=(/ /about/)
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
    apply1:applied) expected=$SNAP1 ;;
    apply2:applied) expected=$SNAP2 ;;
    apply3:applied) expected=$SNAP3 ;;
    old:original) case "$path" in "$SNAP1"|"$SNAP2") expected=$path ;; *) return 88 ;; esac ;;
    *) return 88 ;;
  esac
  [ "$path" = "$expected" ] || return 88
}
cp() {
  if [ "${COPY_LOG_FAIL:-0}" = 1 ] && [ "$1" = "$out/package-browser.log" ]; then emit retain-log-failed; return 73; fi
  command cp "$@"
}
timeout() { emit browser || return 73; printf 'INFO simulated browser gate passed\n'; return "${BROWSER_RC:-0}"; }
'''.replace('core-neighbours', COMPONENT)


def run_case(*, options=None, expected=None, valid=True, absent_browser=False):
    global passed
    with tempfile.TemporaryDirectory(prefix='core-rehearsal-') as temp:
        base=Path(temp)
        for name in ['out','backups','wwm/link-ideas/' + COMPONENT]:(base/name).mkdir(parents=True,exist_ok=True)
        if not absent_browser:(base/('wwm/link-ideas/' + COMPONENT + '/check-rehearsal.mjs')).write_text('local stub\n')
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
         'verify:'+SNAPS[0]+':applied','install:apply-again:apply','fp:B2',
         'verify:'+SNAPS[0]+':applied','install:rollback:rollback:'+SNAPS[0],
         'verify:'+SNAPS[0]+':original','fp:C','install:re-apply:apply','fp:D',
         'pages','wording:verify-home.py','wording:verify-client-pages.py',
         'verify:'+SNAPS[1]+':applied','inventory','browser',
         'verify:'+SNAPS[1]+':applied','install:rollback-after-browser:rollback:'+SNAPS[1],
         'verify:'+SNAPS[1]+':original','fp:E','install:reapply-after-browser:apply',
         'verify:'+SNAPS[2]+':applied','fp:F']
run_case(expected=SUCCESS)
# Every baseline, snapshot and native transition must stop later mutation.
# Once apply succeeds, each later apply has its own returned snapshot/dates.
for at in [1,2,3,4,5,6,9,10,11,12,13,14,18,19,21,22,23,24,25,26,27]:
    # Baseline page failure is reported before preflight, which is read-only.
    stop=3 if at==2 else at
    run_case(options={'FAIL_AT':str(at)},expected=SUCCESS[:stop],valid=False)
# Repeat apply failure is followed only by the read-only B2 fingerprint.
run_case(options={'FAIL_AT':'7'},expected=SUCCESS[:8],valid=False)
run_case(options={'FAIL_AT':'8'},expected=SUCCESS[:8],valid=False)
# Rendered copy failures stop the browser and all later mutations.
for at in [15,16,17]:
    run_case(options={'FAIL_AT':str(at)},expected=SUCCESS[:17],valid=False)
run_case(options={'COPY_LOG_FAIL':'1'},expected=SUCCESS[:20]+['retain-log-failed'],valid=False)
for status in ['1','2']:
    run_case(options={'BROWSER_RC':status},expected=SUCCESS[:20],valid=False)
run_case(expected=SUCCESS[:18],valid=False,absent_browser=True)
for point,stop in [('B2',8),('C',12),('D',14),('E',24),('F',27)]:
    run_case(options={'DRIFT_FP':point},expected=SUCCESS[:stop],valid=False)
for label,stop in [('apply',5),('re-apply',17),('reapply-after-browser',25)]:
    for kind in ['missing','wrong-prefix','duplicate']:
        run_case(options={'BAD_LOG':label,'BAD_LOG_KIND':kind},expected=SUCCESS[:stop],valid=False)
# Exercise the actual path parser in isolation, including multiple different
# snapshots and unrelated noise, with no copied parser implementation.
for lines,want in [
    (['Success: Applied. Before-snapshot: '+SNAPS[0]],SNAPS[0]),
    (['unrelated '+SNAPS[1],'Success: Applied. Before-snapshot: '+SNAPS[2]],SNAPS[2]),
    ([],None),
    (['Success: Applied. Before-snapshot: '+SNAPS[0],'Success: Applied. Before-snapshot: '+SNAPS[1]],None),
    (['Success: Applied. Before-snapshot: '+SNAPS[0].replace('/backups/','/tmp/')],None),
    (['Success: Applied. Before-snapshot: '+SNAPS[0].replace(COMPONENT,'core-kiro-fixes')],None),
    (['Success: Applied. Before-snapshot: '+SNAPS[0]+';id'],None),
]:
    with tempfile.TemporaryDirectory(prefix='core-snapshot-parser-') as temp:
        path=Path(temp)/'apply.log';path.write_text('\n'.join(lines)+'\n')
        result=subprocess.run(['bash','-s','--',str(path)],input='inst='+INSTALLER+'\n'+HELPERS+'\ncore_focus_snapshot_from_log "$1"\n',capture_output=True,text=True)
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
# Execute the actual early scope gate before any credential/server access.
start = SOURCE.index('release_rehearsal=0\n')
end = SOURCE.index('case "$src" in', start)
scope = SOURCE[start:end]
for src, uploads, valid in [('staging39','1',True), ('staging39','0',False),
                            ('live','1',False), ('staging38','1',False), ('','1',False)]:
    prefix='invalid() { printf "INVALID %s\\n" "$*"; exit 2; }; src=$1; uploads=$2; inst='+INSTALLER+'\n'
    result=subprocess.run(['bash','-s','--',src,uploads],input=prefix+scope+'\n[ "$release_rehearsal" = 0 ] && [ "$read_only_discovery" = 1 ] && [ "$copy_isolation" = 1 ]\n',capture_output=True,text=True)
    assert result.returncode == (0 if valid else 2),(src,uploads,result.stdout,result.stderr)
    passed+=1
# Existing core input-focus remains on its historical copy path, while the
# separate first-install release still selects only its own live-copy flags.
for inst,src,uploads,flags in [
    ('link-ideas/core-input-focus/install-peek-core-input-focus.php','staging39','0','0 0 0'),
    ('link-ideas/peek-release/install-peek-release.php','live','1','1 1 1'),
]:
    prefix='invalid() { exit 2; }; inst=$1; src=$2; uploads=$3\n'
    result=subprocess.run(['bash','-s','--',inst,src,uploads],input=prefix+scope+'\nprintf "%s %s %s\\n" "$release_rehearsal" "$read_only_discovery" "$copy_isolation"\n',capture_output=True,text=True)
    assert result.returncode==0 and result.stdout.strip()==flags,(inst,result.stdout,result.stderr)
    passed+=1
# New native installer pipelines must fail when any evidence writer fails.
start=SOURCE.index('inst_run() {')
native=SOURCE[start:SOURCE.index('core_focus_snapshot_from_log() {',start)]
for broken in ['none','wpc','grep','tee','sed']:
    with tempfile.TemporaryDirectory(prefix='neighbours-native-pipeline-') as temp:
        prefix=r'''set -uo pipefail
out=$1; broken=$2; release_rehearsal=0
inst=link-ideas/core-neighbours/install-peek-core-neighbours.php
wpc() { printf 'Success: fixture native output\n'; [ "$broken" != wpc ]; }
grep() { command grep "$@"; [ "$broken" != grep ]; }
tee() { command tee "$@"; [ "$broken" != tee ]; }
sed() { command sed "$@"; [ "$broken" != sed ]; }
'''
        result=subprocess.run(['bash','-s','--',temp,broken],input=prefix.replace('core-neighbours',COMPONENT)+HELPERS+'\n'+native+'\ninst_run fixture preflight\n',capture_output=True,text=True)
        assert (result.returncode==0)==(broken=='none'),(broken,result.stdout,result.stderr)
        passed+=1
# Artifact cleanup exports only complete public evidence, never private recovery
# snapshot contents, copied source or SQL. Failed copying cannot report success.
for original, copy_fail in [(0,False),(7,False),(0,True)]:
    with tempfile.TemporaryDirectory(prefix='neighbours-native-retain-') as temp:
        base=Path(temp); (base/'out').mkdir()
        allowed=['apply.log','A.content','errors-before.txt','title-strip-pages.json']
        for name in allowed+['private-snapshot.json','db.sql','copied-source.php']:
            (base/'out'/name).write_text('fixture-'+name)
        prefix=r'''set -uo pipefail
RUNNER_TEMP=$1; out=$1/out; broken=$2
cleanup() { printf cleaned > "$RUNNER_TEMP/cleaned"; }
cp() { [ "$broken" != yes ] || return 73; command cp "$@"; }
'''
        result=subprocess.run(['bash','-s','--',temp,'yes' if copy_fail else 'no'],input=prefix+'\ninst='+INSTALLER+'\n'+HELPERS+'\ntrap core_neighbours_cleanup EXIT\nexit '+str(original)+'\n',capture_output=True,text=True)
        assert result.returncode==(1 if copy_fail else original),(original,copy_fail,result.stdout,result.stderr)
        assert (base/'cleaned').read_text()=='cleaned'
        names=sorted(path.name for path in (base/('ops-out/'+COMPONENT+'-native')).iterdir())
        assert names==([] if copy_fail else sorted(allowed)),names
        for name in names:assert (base/('ops-out/'+COMPONENT+'-native')/name).read_bytes()==(base/'out'/name).read_bytes()
        passed+=1
# Exercise the existing actual Compose/relay blocks with the NEW isolation flag
# and release_rehearsal=0. Reuse only local command doubles from the established
# isolation tests; do not copy the production orchestration into this test.
release_tests=(ROOT/'scripts/work-with-me-employer/link-ideas/peek-release/test-rehearsal.py').read_text()
fixture_tree=ast.parse(release_tests)
def existing_double(name):
    matches=[node.value for node in ast.walk(fixture_tree) if isinstance(node,ast.Assign)
             and any(isinstance(target,ast.Name) and target.id==name for target in node.targets)]
    assert len(matches)==1,name
    return ast.literal_eval(matches[0])
compose_start=SOURCE.index('cat > "$work/compose.yml" <<YML')
compose_boot=SOURCE[compose_start:SOURCE.index('\nready=0',compose_start)]
with tempfile.TemporaryDirectory(prefix='neighbours-internal-compose-') as temp:
    shim=existing_double('compose_shim')+'\ncopy_isolation=1\n'
    result=subprocess.run(['bash','-s','--',temp,'0'],input=shim+compose_boot,capture_output=True,text=True)
    assert result.returncode==0,(result.stdout,result.stderr)
    config=(Path(temp)/'compose.yml').read_text()
    assert config==(Path(temp)/'at-first-start.yml').read_text()
    assert config.count('networks:\n  default:\n    internal: true\n')==1 and 'ports:' not in config and 'network_mode:' not in config,config
    passed+=1
relay_start=SOURCE.index('dc up -d wordpress >/dev/null 2>&1 || invalid "could not start the web container"')
relay=SOURCE[relay_start:SOURCE.index('\nup=0;',relay_start)]
for mode,exit_code in [('success',0),('container-invalid',2),('container-error',2),
                       ('network-invalid',2),('network-error',2),('external-network',2),
                       ('internal-error',2),('backend-error',2),('startup-failure',2),
                       ('wrong-ready',2),('ready-but-dead',2)]:
    with tempfile.TemporaryDirectory(prefix='neighbours-internal-relay-') as temp:
        shim=existing_double('relay_shim')+'\ncopy_isolation=1\n'
        result=subprocess.run(['bash','-s','--',temp,mode,'0'],input=shim+relay,capture_output=True,text=True)
        assert result.returncode==exit_code,(mode,result.stdout,result.stderr)
        calls=(Path(temp)/'calls.txt').read_text().splitlines()
        if mode in ['success','startup-failure','wrong-ready','ready-but-dead']:
            assert len(calls)==6 and calls[-1]=='python3 -I -B /fixture/ci/rehearsal-loopback.py 172.30.0.2',calls
        else:assert not any(call.startswith('python3 ') for call in calls),calls
        passed+=1
# Actual SSH command assembly carries the explicit raw-discovery flag. Its
# payload is captured locally; the ssh command below is a Bash function double.
remote_function=SOURCE[SOURCE.index('remote() { ssh '):SOURCE.index('\nif [ -n "$seam" ]; then',SOURCE.index('remote() { ssh '))]
with tempfile.TemporaryDirectory(prefix='neighbours-ssh-argv-') as temp:
    prefix=r'''set -uo pipefail
trace=$1; port=22; user=fixture; host=fixture.invalid
want=staging39.thaisolitude.com; read_only_discovery=1; REMOTE='literal reviewed payload'
ssh() { printf '%s\n' "$@" > "$trace/argv"; cat > "$trace/stdin"; }
'''
    result=subprocess.run(['bash','-s','--',temp],input=prefix+remote_function+'\nremote files 1\n',capture_output=True,text=True)
    assert result.returncode==0,(result.stdout,result.stderr)
    assert (Path(temp)/'argv').read_text().splitlines()[-7:]==['bash','-s','--','staging39.thaisolitude.com','files','1','1']
    assert (Path(temp)/'stdin').read_text()=='literal reviewed payload\n'
    passed+=1
# Run the actual remote discovery/info/export source against temporary config
# files and a WP CLI double. Any bootstrapping command is a hard fixture failure.
remote=SOURCE.split("read -r -d '' REMOTE <<'EOS' || true\n",1)[1].split('\nEOS\n',1)[0]
WP_DOUBLE=r'''#!/usr/bin/env python3
from pathlib import Path
import json,os,sys
args=sys.argv[1:]
with open(os.environ['READ_TRACE'],'a') as f:f.write(json.dumps(args)+'\n')
assert '--skip-plugins' in args and '--skip-themes' in args,args
assert any(a.startswith('--exec=ini_set(') for a in args),args
path=next(a[7:] for a in args if a.startswith('--path='))
words=[a for a in args if not a.startswith('--')]
if words[:3]==['config','get','table_prefix']:print('wp_')
elif words[:2]==['db','query']:
    assert 'SELECT option_value FROM wp_options WHERE option_name=' in words[2],words
    print(os.environ['READ_TARGET_HOME'] if Path(path).name in ['target','duplicate'] else 'https://thaisolitude.com')
elif words==['core','version']:print('7.1.4')
elif words==['db','export','-']:print('synthetic SQL export only')
else:raise AssertionError(('bootstrap/unsupported command refused',args))
'''
valid_origins=['https://staging39.thaisolitude.com','http://staging39.thaisolitude.com',
               'https://staging39.thaisolitude.com/','http://staging39.thaisolitude.com/']
invalid_origins=['staging39.thaisolitude.com','http://https://staging39.thaisolitude.com',
                 'https://http://staging39.thaisolitude.com','https://staging39.thaisolitude.com:443',
                 'https://staging39.thaisolitude.com/path','https://staging39.thaisolitude.com.evil.invalid',
                 'https://staging39.thaisolitude.com//','https://staging39.thaisolitude.com?x=1',
                 'https://staging39.thaisolitude.com#x','https://user@staging39.thaisolitude.com']
for phase,duplicate,target_home in [('info',False,url) for url in valid_origins+invalid_origins]+[
    ('db',False,valid_origins[0]),('info',True,valid_origins[0])]:
    with tempfile.TemporaryDirectory(prefix='neighbours-raw-discovery-') as temp:
        base=Path(temp); (base/'bin').mkdir(); (base/'bin/wp').write_text(WP_DOUBLE); (base/'bin/wp').chmod(0o700)
        for site in ['target','other']+(['duplicate'] if duplicate else []):
            (base/'www'/site).mkdir(parents=True); (base/'www'/site/'wp-config.php').write_text('synthetic config')
        env=dict(os.environ,PATH=str(base/'bin')+os.pathsep+os.environ['PATH'],READ_TRACE=str(base/'trace'),READ_FIXTURE=str(base),READ_TARGET_HOME=target_home)
        env.pop('BASH_ENV',None)
        body=remote.replace('$HOME','$READ_FIXTURE')
        result=subprocess.run(['bash','-s','--','staging39.thaisolitude.com',phase,'1','1'],input=body.encode(),env=env,capture_output=True)
        valid=not duplicate and target_home in valid_origins
        assert (result.returncode==0)==valid,(phase,duplicate,target_home,result.returncode,result.stdout,result.stderr)
        events=[json.loads(line) for line in (base/'trace').read_text().splitlines()]
        assert not any('option' in args or 'eval' in args or 'eval-file' in args for args in events),events
        if phase=='info' and valid:assert ('home='+target_home+'\n').encode() in result.stdout
        if not valid:assert not any('export' in args or 'version' in args for args in events),events
        if phase=='db':
            import gzip
            assert gzip.decompress(result.stdout)==b'synthetic SQL export only\n'
            export=next(args for args in events if 'export' in args)
            assert '--path='+str(base/'www/target') in export,export
        passed+=1
print(f'Core {PROFILE} rehearsal snapshot/order/recovery harness: {passed} cases passed; no Docker, browser, services or network.')
