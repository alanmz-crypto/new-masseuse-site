"""Offline fixed LIVE #30 cold-copy orchestration controls.

Only synthetic files and local command doubles are used. Production shell
sections execute unchanged; no SSH, WordPress, Docker or site request occurs.
"""
from pathlib import Path
import hashlib
import io
import json
import os
import runpy
import subprocess
import sys
import tarfile
import tempfile

HERE = Path(__file__).resolve().parent
ROOT = HERE.parents[3]
SOURCE = (HERE / 'prove-live30-cold.sh').read_text()
REHEARSE = (ROOT / 'scripts/ci/rehearse.sh').read_text()
PIN = '4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0'
SIZE = 3919398
PASSED = 0


def check(condition, message):
    global PASSED
    assert condition, message
    PASSED += 1


def shell(source, args=(), env=None, timeout=20):
    clean = dict(os.environ)
    clean.pop('BASH_ENV', None)
    clean.update(env or {})
    return subprocess.run(['bash', '--noprofile', '--norc', '-s', '--', *map(str, args)],
                          input=source.encode(), env=clean, capture_output=True, timeout=timeout)


def section(start, end):
    at = SOURCE.index(start)
    return SOURCE[at:SOURCE.index(end, at)]


# The causal script must not acquire a weakened cleanup implementation.
cleanup = section('release_cleanup_resources() {', '# END IDENTICAL REHEARSAL CLEANUP').rstrip()
start = REHEARSE.index('release_cleanup_resources() {')
end = REHEARSE.index('\nif [ -n "$owned_cleanup_component" ]', start)
check(cleanup == REHEARSE[start:end].rstrip(), 'cold cleanup must equal the already tested decisive helper byte-for-byte')
check(SOURCE.index('trap release_cleanup_exit EXIT') < SOURCE.index('mkdir -m 700 -- "$work"') < SOURCE.index('remote sql >'),
      'early ownership trap must precede exclusive copy creation and all source reads')
check('trap cold_cleanup EXIT' in SOURCE and 'release_cleanup_exit "$result"' in SOURCE,
      'late evidence retention must delegate to decisive cleanup with the original result')

# Fixed source and origin guard is evaluated before any credentials or command.
early = section('proof_mode=cold', 'creds=')
for target, args, override, valid in [
    ('live', (), '', True), ('staging39', (), '', False), ('', (), '', False),
    ('live', ('--apply',), '', False), ('live', (), 'source', False), ('live', (), 'prep', False),
    ('live', ('--recovery',), '', True), ('live', ('--recovery','extra'), '', False),
    ('staging39', ('--recovery',), '', False), ('live', ('--recovery',), 'source', False),
]:
    env = {'S_TARGET': target, 'TS_REHEARSAL_FROM_DIR': 'private' if override == 'source' else '',
           'TS_REHEARSAL_TEST_PREP': 'private' if override == 'prep' else ''}
    result = shell('invalid() { exit 2; }\n' + early, args, env)
    check(result.returncode == (0 if valid else 2), ('source scope', target, args, override, result.stderr))

REMOTE = SOURCE.split("read -r -d '' REMOTE <<'EOS' || true\n", 1)[1].split('\nEOS\n', 1)[0]
REMOTE_DOUBLE = r'''#!/usr/bin/env python3
from pathlib import Path
import json,os,sys
args=sys.argv[1:]; name=Path(sys.argv[0]).name
trace=Path(os.environ['COLD_TRACE'])
with trace.open('a') as out:out.write(json.dumps([name,*args])+'\n')
mode=os.environ['COLD_REMOTE_MODE']; expected=os.environ['COLD_PIN']
if name=='sha256sum':
    assert args==['--',os.environ['COLD_BACKUP']]
    print(('0'*64 if mode=='bad-sha' else expected)+'  '+args[1]);sys.exit(0)
assert name=='wp' and '--skip-plugins' in args and '--skip-themes' in args
assert '--exec=ini_set("log_errors", "0"); ini_set("error_log", "/dev/null");' in args
words=[a for a in args if not a.startswith('--')]
path=next(a.split('=',1)[1] for a in args if a.startswith('--path='))
if words==['config','get','table_prefix']:
    assert '--type=variable' in args
    if mode=='config-fails':sys.exit(47)
    print('invalid;' if mode=='bad-prefix' else 'iej_');sys.exit(0)
if words==['db','export','-']:
    assert all(flag in args for flag in ['--single-transaction','--skip-lock-tables','--quiet'])
    if mode=='export-fails':sys.exit(49)
    print('SYNTHETIC_PRIVATE_CURRENT_SQL');sys.exit(0)
assert words==['db','query',"SELECT option_value FROM iej_options WHERE option_name='home';"]
assert all(flag in args for flag in ['--skip-column-names','--batch','--raw'])
if mode=='query-fails':sys.exit(48)
print('https://staging39.thaisolitude.com' if mode=='wrong-home' or Path(path).name=='unrelated' else 'http://thaisolitude.com')
'''


def remote_case(mode='valid', part='sql', valid=True):
    with tempfile.TemporaryDirectory(prefix='cold-source-') as directory:
        base=Path(directory); commands=base/'bin'; commands.mkdir()
        home=base/'home'; site=home/'www/site'; site.mkdir(parents=True)
        (site/'wp-config.php').write_text('SYNTHETIC_PRIVATE_CONFIG')
        unrelated=home/'www/unrelated'; unrelated.mkdir(); (unrelated/'wp-config.php').write_text('SYNTHETIC_PRIVATE_UNRELATED')
        backup_name='peek-release-before-20261111-162615-e00022b6.json' if part=='journal' else 'live-before-peek-release-20261111-162615-74851.sql'
        expected_size=1682009 if part=='journal' else SIZE
        expected_pin='4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b' if part=='journal' else PIN
        backup=home/'private-backups'/backup_name; backup.parent.mkdir()
        with backup.open('wb') as f:
            f.write(b'SYNTHETIC_PRIVATE_SQL');f.truncate(expected_size if mode!='bad-size' else expected_size-1)
        if mode=='backup-symlink':
            original=backup.with_suffix('.original');backup.rename(original);backup.symlink_to(original)
        if mode=='backup-missing':backup.unlink()
        if mode=='duplicate':
            duplicate=home/'www/duplicate';duplicate.mkdir();(duplicate/'wp-config.php').write_text('SYNTHETIC_PRIVATE_DUPLICATE')
        for name in ['wp','sha256sum']:
            executable=commands/name;executable.write_text(REMOTE_DOUBLE);executable.chmod(0o700)
        if part=='files':
            (site/'wp-includes').mkdir();(site/'wp-includes/version.php').write_text('public source')
            for name in ['.htaccess','php_errorlog','private.log','backup.sql','backup.sql.gz','backup.zip','backup.tar.gz']:
                (site/name).write_text('SYNTHETIC_PRIVATE_EXCLUDED')
            (site/'wp-content/cache').mkdir(parents=True);(site/'wp-content/cache/secret').write_text('SYNTHETIC_PRIVATE_CACHE')
        env={'PATH':str(commands)+os.pathsep+os.environ['PATH'],'COLD_TRACE':str(base/'trace'),
             'COLD_REMOTE_MODE':mode,'COLD_PIN':expected_pin,'COLD_BACKUP':str(backup),'COLD_FIXTURE_HOME':str(home)}
        result=shell(REMOTE.replace('$HOME','$COLD_FIXTURE_HOME'), (part,), env)
        events=[json.loads(line) for line in (base/'trace').read_text().splitlines()]
        check((result.returncode==0)==valid, ('remote',mode,part,result.returncode,result.stderr))
        check(all(e[0] in ['wp','sha256sum'] for e in events) and
              all(not any(word in e for word in ['eval','eval-file','update','search-replace']) for e in events) and
              all('export' not in e or part=='current-sql' for e in events),
              ('read-only source commands',events))
        if valid and part in ['sql','journal']:
            check(result.stdout==backup.read_bytes(), 'the fixed SQL stream must remain byte-identical')
        elif valid and part=='current-sql':
            check(result.stdout==b'SYNTHETIC_PRIVATE_CURRENT_SQL\n' and sum('export' in e for e in events)==1,
                  'fresh export uses stdout once without a remote backup write or WordPress bootstrap')
        elif valid and part=='files':
            with tarfile.open(fileobj=io.BytesIO(result.stdout)) as archive:
                names=archive.getnames()
                check('./wp-includes/version.php' in names and './wp-config.php' not in names and
                      not any('SYNTHETIC_PRIVATE' in archive.extractfile(member).read().decode(errors='replace')
                              for member in archive.getmembers() if member.isfile()), 'private files must not enter copied file stream')
        else:
            check(result.stdout==b'' and b'SYNTHETIC_PRIVATE' not in result.stderr, 'failed source read must not leak payload')


remote_case()
remote_case(part='files')
for mode in ['bad-sha','bad-size','backup-symlink','backup-missing','duplicate','wrong-home','bad-prefix','config-fails','query-fails']:
    remote_case(mode,valid=False)
remote_case(part='apply',valid=False)
remote_case(part='journal')
for mode in ['bad-sha','bad-size','backup-symlink','backup-missing']:
    remote_case(mode,part='journal',valid=False)
remote_case(part='current-sql')
remote_case('export-fails',part='current-sql',valid=False)

# IO primitives use synthetic dump bytes and the actual immutable profiles.
io_module = runpy.run_path(str(HERE/'cold-proof-io.py'))
transform = io_module['transform_sql']
raw = (b'-- utf8mb4_0900_ai_ci stays in comments\n'
       b'CREATE TABLE `iej_posts` (\n  `body` longtext COLLATE utf8mb4_0900_ai_ci\n'
       b') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;\n'
       b"INSERT INTO `iej_posts` VALUES ('COLLATE=utf8mb4_0900_ai_ci', 'http://thaisolitude.com', 'SYNTHETIC_PRIVATE');\n")
adapted, count = transform(raw)
check(count == 2 and b'COLLATE=utf8mb4_unicode_ci' in adapted,
      'only known DDL collation is adapted')
check(adapted.splitlines()[0] == raw.splitlines()[0] and adapted.splitlines()[-1] == raw.splitlines()[-1],
      'SQL data, origin and comments remain byte-identical')
for sample in [b'', b"INSERT INTO `x` VALUES ('utf8mb4_0900_ai_ci');\r\n", b'CREATE TABLE `x` (id int);\r\n']:
    check(transform(sample) == (sample, 0), 'unchanged SQL retains exact bytes')
for sample in [b'CREATE TABLE `x` (\n', b'CREATE TABLE `x` (\nCREATE TABLE `y` (id int);\n',
               b'CREATE TABLE `x` (id int) COLLATE = utf8mb4_0900_ai_ci;\n']:
    try:
        transform(sample)
    except ValueError:
        check(True, 'unreviewed or incomplete DDL refuses')
    else:
        check(False, 'unreviewed or incomplete DDL accepted')
manifest = (HERE/'manifest.json').read_bytes()
profile = (HERE/'live-profile.json').read_bytes()
spec = json.loads(manifest)
preflight = dict(schema=1, bundle='peek-visitor-release-v1', home='http://thaisolitude.com',
                 manifest_sha256=io_module['MANIFEST_SHA'], profile_sha256=io_module['PROFILE_SHA'],
                 state='ready', protected_state_sha256='a'*64)
bound = dict(files=spec['spectra']['files'], header_version='2.20.6', runtime_version='2.20.6',
             loaded=dict.fromkeys(['main','UAGB_Helper','UAGB_Rest_API','UAGB_Loader','UAGB_Post_Assets','UAGB_Front_Assets'], True),
             save_post_priority_10=True)
def markers(first=preflight, second=bound):
    return ('RELEASE-PREFLIGHT '+json.dumps(first)+'\nRELEASE-SPECTRA-SOURCE '+json.dumps(second)+'\n').encode()
receipt = io_module['bootstrap_receipt']
check(receipt(markers(), b'', 0, manifest, profile)['valid'], 'exact bootstrap markers pass')
deprecation = b'PHP Deprecated:  Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95\n'
check(receipt(markers()+b'Success: Original visitor baseline ready; nothing changed.\n', deprecation, 0, manifest, profile)['valid'],
      'only reviewed harmless CLI text is accepted')
for stdout, stderr, status in [
    (markers(), b'', 7), (b'', b'', 0), (markers()+markers(), b'', 0),
    (markers(), b'SYNTHETIC_PRIVATE_ERROR\n', 0), (markers()+b'SYNTHETIC_PRIVATE_STDOUT\n', b'', 0),
    (markers(dict(preflight, secret='SYNTHETIC_PRIVATE')), b'', 0),
    (markers(dict(preflight, home='https://staging39.thaisolitude.com')), b'', 0),
    (markers(dict(preflight, protected_state_sha256='SYNTHETIC_PRIVATE')), b'', 0),
    (markers(preflight, dict(bound, secret='SYNTHETIC_PRIVATE')), b'', 0),
    (b'RELEASE-PREFLIGHT {SYNTHETIC_PRIVATE\n'+markers(), b'', 0),
    (markers(), deprecation.replace(b'95', b'96'), 0),
]:
    result = receipt(stdout, stderr, status, manifest, profile)
    check(not result['valid'] and 'SYNTHETIC_PRIVATE' not in json.dumps(result),
          'invalid bootstrap receipt is decisive and redacted')
for bad_manifest, bad_profile in [(manifest+b' ', profile), (manifest, profile+b' ')]:
    try:
        receipt(markers(), b'', 0, bad_manifest, bad_profile)
    except ValueError:
        check(True, 'receipt profiles are byte-bound')
    else:
        check(False, 'changed profile accepted')
with tempfile.TemporaryDirectory(prefix='cold-io-') as directory:
    base=Path(directory); sqlfile=base/'raw.sql'; output=base/'receipt.json'
    sqlfile.write_bytes(raw)
    result=subprocess.run([sys.executable,'-I','-B',str(HERE/'cold-proof-io.py'),'import-sql',str(sqlfile),str(output)],capture_output=True)
    check(result.returncode == 1 and result.stdout == b'' and not output.exists() and b'SYNTHETIC_PRIVATE' not in result.stderr,
          'CLI rejects unpinned SQL before emitting import data or receipt')
    current_raw=raw+b'-- synthetic padding\n'*100
    for case in ['valid','wrong-hash','invalid-hash','too-short']:
        sqlfile.write_bytes(raw if case=='too-short' else current_raw)
        expected=hashlib.sha256(sqlfile.read_bytes()).hexdigest()
        if case=='wrong-hash':expected='0'*64
        if case=='invalid-hash':expected='not-a-hash'
        output.unlink(missing_ok=True)
        result=subprocess.run([sys.executable,'-I','-B',str(HERE/'cold-proof-io.py'),'import-current-sql',str(sqlfile),expected,str(output)],capture_output=True)
        if case=='valid':
            receipt_data=json.loads(output.read_text())
            check(result.returncode==0 and result.stdout==transform(current_raw)[0] and
                  receipt_data['source_sha256']==expected and receipt_data['scope']=='fresh-current-live-copy' and
                  receipt_data['data_rewritten'] is False and 'SYNTHETIC_PRIVATE' not in output.read_text(),
                  'fresh current import preserves data and emits only hash receipt')
        else:
            check(result.returncode==1 and result.stdout==b'' and not output.exists() and b'SYNTHETIC_PRIVATE' not in result.stderr,
                  'changed, invalid or incomplete fresh current import refuses before emitting data')

# Execute the actual import/bootstrap/request chain using command doubles. Each
# stage can fail independently; no Docker, HTTP, WordPress or real SQL executes.
LIFECYCLE = SOURCE[SOURCE.index('dc up -d db >'):]
COMMAND_DOUBLE = r'''#!@@PYTHON@@
from pathlib import Path
import json,os,sys,time
args=sys.argv[1:];name=Path(sys.argv[0]).name
base=Path(os.environ['COLD_FIXTURE']);mode=os.environ['COLD_MODE']
fd=os.open(base/'trace',os.O_WRONLY|os.O_CREAT|os.O_APPEND,0o600)
os.write(fd,(json.dumps([name,*args])+'\n').encode());os.close(fd)
def hit(key):
    if mode==key:sys.exit(37)
def counter(key):
    path=base/key;n=int(path.read_text())+1 if path.exists() else 1;path.write_text(str(n));return n
if name=='sleep':
    if args==['1']:time.sleep(.015)
    sys.exit(0)
if name=='dc':
    if args[:2]==['up','-d']:hit('up:'+args[2]);sys.exit(0)
    if args[:2]==['ps','-q']:
        kind=args[2];hit('ps:'+kind);print('bad' if mode=='id:'+kind else ('a' if kind=='db' else 'b')*64);sys.exit(0)
    if args[0]=='run':
        if '--entrypoint' in args and args[args.index('--entrypoint')+1]=='php':
            assert args[:10]==['run','--rm','-T','--no-deps','--user','0','--entrypoint','php','wpcli','/wwm/link-ideas/peek-release/verify-render.php']
            assert args[10:12]==['/home/customer/private-backups/recovery-response.private.html','/home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json']
            assert 'SYNTHETIC_PRIVATE_RESPONSE' in (base/'work/backups/recovery-response.private.html').read_text()
            hit('render-verify');print('Exact public response verified: '+args[12]+' '+args[13]);sys.exit(0)
        if os.environ.get('COLD_PROOF_MODE')=='recovery':
            n=counter('recovery-mode-count')
            mode_arg=['verify-incident','verify-incident','recovery-check-incident','rollback-incident','verify-incident','recovery-check-incident','verify-incident'][n-1]
            expected=['run','--rm','-T','--no-deps','--user','0','--entrypoint','wp','wpcli','--allow-root','--skip-plugins=sg-security','--user=1','eval-file','/wwm/link-ideas/peek-release/install-peek-release.php',mode_arg,'/home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json']
            if n>4:expected.append('restored')
            assert args==expected
            print('SYNTHETIC_PRIVATE_RECOVERY_STDOUT');print('SYNTHETIC_PRIVATE_RECOVERY_STDERR',file=sys.stderr)
            if mode=='private-input-changed' and n==7:(base/'work/backups/peek-release-before-20261111-162615-e00022b6.json').write_text('changed')
            if mode=='private-mode-changed' and n==7:(base/'work/backups/peek-release-before-20261111-162615-e00022b6.json').chmod(0o644)
            hit('recovery-mode:'+str(n));sys.exit(0)
        assert args==['run','--rm','-T','--no-deps','--entrypoint','wp','wpcli','--skip-plugins=sg-security','--user=1','eval-file','/wwm/link-ideas/peek-release/install-peek-release.php','preflight']
        print('SYNTHETIC_PRIVATE_BOOTSTRAP_STDOUT');print('SYNTHETIC_PRIVATE_BOOTSTRAP_STDERR',file=sys.stderr);hit('bootstrap');sys.exit(0)
    assert args[:6]==['exec','-T','db','mariadb','-uroot','-prehearsal']
    if args[-2:]==['-e','SELECT 1']:hit('db-ready');sys.exit(0)
    sys.stdin.buffer.read()
    if '--raw' in args:
        n=counter('observe-db');hit('dbsql:'+str(n));print('synthetic raw SQL');sys.exit(0)
    hit('import-db');sys.exit(0)
if name=='docker':
    if args[:2]==['network','inspect']:
        print('false' if mode=='network-external' else 'true');sys.exit(0)
    assert args[:2]==['inspect','--format']
    hit('docker-inspect')
    if 'IPAddress' in args[2]:print('172.28.0.2')
    elif mode=='network-id':print('invalid')
    elif mode=='network-mismatch' and args[-1]=='b'*64:print('e'*64)
    else:print('c'*64)
    sys.exit(0)
if name=='curl':
    n=counter('curl-count');hit('curl:'+str(n))
    if os.environ.get('COLD_PROOF_MODE')=='recovery':
        path=Path(args[args.index('-o')+1]);assert path==base/'work/backups/recovery-response.private.html';path.write_text('SYNTHETIC_PRIVATE_RESPONSE')
    print('302' if mode=='http:'+str(n) else ('404' if args[-1].endswith('/peek-release-not-a-real-page/') else '200'));sys.exit(0)
if name=='tee':
    raw=sys.stdin.buffer.read();n=counter('tee-count');hit('tee:'+str(n))
    with open(args[-1],'ab') as f:f.write(raw)
    sys.stdout.buffer.write(raw);sys.exit(0)
assert name=='python3' and args[:2]==['-I','-B'];program=args[2];rest=args[3:]
if program=='-':
    raw=sys.stdin.read()
    if rest[0].endswith('/manifest.json'):
        assert "manifest['pages']" in raw
        for page in json.loads(Path(rest[0]).read_text())['pages']:print(page['path'])
        print('/?s=massage');print('/peek-release-not-a-real-page/');sys.exit(0)
    assert 'socket.create_connection' in raw and 'http' not in raw.lower();hit('tcp');sys.exit(0)
if program.endswith('/rehearsal-loopback.py'):
    hit('relay');print('READY rehearsal loopback 127.0.0.1:8090 -> '+rest[0]+':80',flush=True)
    while True:time.sleep(1)
if program.endswith('/cold-proof-io.py'):
    if rest[0] in ['import-sql','import-current-sql']:
        assert (rest[0]=='import-current-sql')==(os.environ.get('COLD_PROOF_MODE')=='recovery')
        hit('import-source');Path(rest[-1]).write_text('{"safe":"import"}\n');print('synthetic import');sys.exit(0)
    assert rest[0]=='bootstrap'
    assert Path(rest[1]).parent==base/'work' and Path(rest[2]).parent==base/'work'
    assert 'SYNTHETIC_PRIVATE' in Path(rest[1]).read_text() and 'SYNTHETIC_PRIVATE' in Path(rest[2]).read_text()
    print('LIVE30-COLD-BOOTSTRAP {"safe":true}');hit('bootstrap-receipt');sys.exit(0)
if program.endswith('/recovery-copy-evidence.py') and rest[0]=='marker':
    raw=sys.stdin.read();assert 'SYNTHETIC_PRIVATE_RECOVERY' in raw
    hit('recovery-marker:'+rest[1]);print('{"safe":"marker"}');sys.exit(0)
if program.endswith('/recovery-copy-evidence.py') and rest[0]=='verify':
    hit('recovery-final');print('{"safe":"recovery-final"}');sys.exit(0)
assert program.endswith(('/cold-cache-evidence.py','/recovery-copy-evidence.py'))
if rest[0]=='sql':
    n=counter('observe-source');hit('sql:'+str(n));print('synthetic SQL query');sys.exit(0)
if rest[0]=='capture':
    sys.stdin.buffer.read();hit('capture:'+rest[1]);print(json.dumps({'label':rest[1]}));sys.exit(0)
if rest[0]=='compare':
    hit('compare:'+Path(rest[2]).stem);print('LIVE30-COLD-COMPARE {"safe":true}');sys.exit(0)
assert rest[0]=='verify-final';hit('final');print('LIVE30-COLD-FINAL {"safe":true}')
'''.replace('@@PYTHON@@',sys.executable)


def lifecycle_case(mode='', expected=0, gets=6, recovery=False):
    with tempfile.TemporaryDirectory(prefix='cold-chain-') as directory:
        base=Path(directory);commands=base/'bin';commands.mkdir();work=base/'work';work.mkdir();out=work/'out';out.mkdir()
        (work/'db.sql').write_text('SYNTHETIC_PRIVATE_SQL')
        if recovery:
            private=work/'backups';private.mkdir()
            for name in ['peek-release-before-20261111-162615-e00022b6.json','live-before-peek-release-20261111-162615-74851.sql']:
                file=private/name;file.write_text('SYNTHETIC_PRIVATE_RECOVERY_INPUT');file.chmod(0o600)
            (out/'recovery-context.json').write_text('{"safe":"context"}')
        for name in ['dc','docker','python3','curl','tee','sleep']:
            file=commands/name;file.write_text(COMMAND_DOUBLE);file.chmod(0o700)
        setup='''set -uo pipefail
work=$COLD_FIXTURE/work; out=$work/out; here=$COLD_SOURCE; ci=$COLD_CI
proof_mode=$COLD_PROOF_MODE; input_sql=$work/db.sql; current_sql_sha=$(sha256sum "$input_sql"); current_sql_sha=${current_sql_sha%% *}
if [ "$proof_mode" = recovery ]; then
  bk=$work/backups; private_inside=/home/customer/private-backups
  expected_snapshot=$(sha256sum "$bk/peek-release-before-20261111-162615-e00022b6.json"); expected_snapshot=${expected_snapshot%% *}
  backup_sha=$(sha256sum "$bk/live-before-peek-release-20261111-162615-74851.sql"); backup_sha=${backup_sha%% *}
fi
invalid() { echo "INVALID $*" >&2; exit 2; }
fail() { echo "FAIL $*" >&2; exit 1; }
trap 'result=$?; if [ -n "${release_relay_pid:-}" ]; then kill "$release_relay_pid" 2>/dev/null || true; wait "$release_relay_pid" 2>/dev/null || true; fi; exit "$result"' EXIT
'''
        env={'PATH':str(commands)+os.pathsep+os.environ['PATH'],'COLD_FIXTURE':str(base),'COLD_MODE':mode,
             'COLD_SOURCE':str(HERE),'COLD_CI':str(ROOT/'scripts/ci'),'COLD_PROOF_MODE':'recovery' if recovery else 'cold'}
        result=shell(setup+LIFECYCLE,env=env,timeout=12)
        events=[json.loads(line) for line in (base/'trace').read_text().splitlines()]
        requests=[event for event in events if event[0]=='curl']
        check(result.returncode==expected, ('lifecycle result',mode,result.returncode,result.stderr))
        check(len(requests)==gets, ('GET fail-closed count',mode,len(requests),gets))
        check(b'SYNTHETIC_PRIVATE' not in result.stdout+result.stderr and
              all('SYNTHETIC_PRIVATE' not in file.read_text() for file in out.iterdir()),
              ('raw bootstrap and SQL remain private',mode))
        pass_marker=b'RECOVERY COPY PASSED' if recovery else b'COLD PROOF PASSED'
        check((pass_marker in result.stdout)==(expected==0), ('no false final PASS',mode))
        if recovery:
            check(not any(any('playwright' in arg or 'check-peek' in arg for arg in event) for event in events),
                  'recovery runs only fixed static responses, without a browser suite')
        if not mode and recovery:
            check(sum(event[:2]==['dc','run'] and 'wp' in event for event in events)==7,
                  'recovery executes exactly the seven incident modes without preflight or apply')
            captures=[event[5] for event in events if len(event)>5 and event[0]=='python3' and event[4]=='capture']
            check(captures==['before-bootstrap','after-verify-applied','after-verify-applied-after-render','after-recoverable','after-rollback',
                             'after-verify-restored-after-render','after-recovery-restored','after-verify-restored'],
                  'recovery observes actual cache state before bootstrap and after each incident mode')
            paths=[page['path'] for page in spec['pages']]+['/?s=massage','/peek-release-not-a-real-page/']
            check([event[-1] for event in requests]==['http://127.0.0.1:8090'+path for path in paths*2] and
                  all(event[1:12]==['--noproxy','*','--proto','=http','--max-redirs','0','-sS','--max-time','30','-H','Host: thaisolitude.com'] for event in requests),
                  'applied and original response passes stay on 17 fixed raw-host loopback routes without redirects')
            check(not (work/'backups/recovery-response.private.html').exists() and
                  all(len((out/('render-'+phase+'.log')).read_text().splitlines())==17 for phase in ['applied','original']),
                  '34 safe response receipts retained, transient private response removed')
        if not mode and not recovery:
            captures=[event[5] for event in events if event[0]=='python3' and len(event)>5 and event[4]=='capture']
            check(captures==['cold-import','after-bootstrap','first-1254','first-1255','first-1257','repeat-1254','repeat-1255','repeat-1257'],
                  'all eight captures are ordered around exact first/repeat requests')
            capture0=next(i for i,event in enumerate(events) if len(event)>5 and event[4:6]==['capture','cold-import'])
            boot=next(i for i,event in enumerate(events) if event[:2]==['dc','run'])
            capture1=next(i for i,event in enumerate(events) if len(event)>5 and event[4:6]==['capture','after-bootstrap'])
            firstget=next(i for i,event in enumerate(events) if event[0]=='curl')
            check(capture0<boot<capture1<firstget and sum(event[:2]==['dc','run'] for event in events)==1,
                  'SQL-only baseline precedes exactly one preflight bootstrap and any GET')
            comparisons=[event[7] for event in events if len(event)>7 and event[0]=='python3' and event[4]=='compare']
            check(comparisons==['exact','cold-1254','cold-1255','cold-1257','exact','exact','exact'],
                  'bootstrap and warm requests stay exact; only their own first page cache may change')
            paths=['/personal-automation/','/personal-automation/privacy/','/personal-automation/terms/']*2
            check(all(event==['curl','--noproxy','*','--proto','=http','--max-redirs','0','-sS','--max-time','30',
                              '-H','Host: thaisolitude.com','-o','/dev/null','-w','%{http_code}','http://127.0.0.1:8090'+path]
                      for event,path in zip(requests,paths)), 'GETs cannot leave loopback, follow redirects, fetch assets or retain bodies')


lifecycle_case()
for mode in ['up:db','id:db','network-external','import-source','import-db']:
    lifecycle_case(mode,2,0)
for mode in ['bootstrap','bootstrap-receipt','compare:after-bootstrap','tee:1']:
    lifecycle_case(mode,1,0)
for mode in ['network-mismatch','relay','tcp']:
    lifecycle_case(mode,2,0)
labels=['cold-import','after-bootstrap','first-1254','first-1255','first-1257','repeat-1254','repeat-1255','repeat-1257']
for number in [1,3,6]:
    label=labels[number-1]
    for stage in ['sql:'+str(number),'dbsql:'+str(number),'capture:'+label]:
        lifecycle_case(stage,1,max(0,number-2))
for number in [1,4]:
    label=labels[number+1]
    for mode in ['curl:'+str(number),'http:'+str(number),'compare:'+label,'tee:'+str(number+1)]:
        lifecycle_case(mode,1,number)
for mode in ['final','tee:8']:
    lifecycle_case(mode,1,6)
lifecycle_case(gets=34,recovery=True)
for mode,gets in [('recovery-mode:4',17),('recovery-marker:rollback',17),('recovery-final',34),
                  ('private-input-changed',34),('private-mode-changed',34),('render-verify',1),('http:1',1)]:
    lifecycle_case(mode,1,gets,recovery=True)

# Retention executes unchanged against exact synthetic output names. Unexpected
# JSON, raw bootstrap logs, SQL/configuration and response bodies must stay out.
RETAIN=section('cold_cleanup() {','trap cold_cleanup EXIT')
NAMES={label+'.json' for label in labels}|{'import-receipt.json','bootstrap.log','source-binding.log','proof.log'}
NAMES|={'recovery-context.json','before-bootstrap.json'}
recovery_steps=['verify-applied','verify-applied-after-render','recoverable','rollback','verify-restored-after-render','recovery-restored','verify-restored']
NAMES|={'after-'+step+'.json' for step in recovery_steps}
NAMES|={step+'.marker.json' for step in recovery_steps}
NAMES|={'render-applied.log','render-original.log'}
for mode in ['ok','cp-fails','mkdir-fails']:
    for prior in [0,2,7]:
        with tempfile.TemporaryDirectory(prefix='cold-retain-') as directory:
            base=Path(directory);out=base/'out';out.mkdir();evidence=base/'evidence'
            for name in NAMES:(out/name).write_text('safe receipt '+name)
            for name in ['unexpected.json','snapshot.json','bootstrap.stderr.log','bootstrap.private.stdout','db.sql','wp-config.php','response.html',
                         'rollback.private.stdout','rollback.private.stderr','current.sql','peek-release-before-20261111-162615-e00022b6.json']:
                (out/name).write_text('SYNTHETIC_PRIVATE_'+name)
            setup='''set -uo pipefail
out=$1; evidence=$2; mode=$3; prior=$4
cp() { [ "$mode" != cp-fails ] || return 55; command cp "$@"; }
mkdir() { [ "$mode" != mkdir-fails ] || return 56; command mkdir "$@"; }
release_cleanup_exit() { printf 'CLEANUP-RESULT %s\n' "$1"; exit "$1"; }
'''
            result=shell(setup+RETAIN+'\n(exit "$prior")\ncold_cleanup\n',[out,evidence,mode,prior])
            expected=prior or (1 if mode!='ok' else 0)
            check(result.returncode==expected and ('CLEANUP-RESULT '+str(expected)).encode() in result.stdout,
                  ('retention preserves original failure and upgrades success on evidence failure',mode,prior))
            retained={p.name for p in evidence.iterdir()} if evidence.exists() else set()
            check(retained==(NAMES if mode=='ok' else set()), ('retention exact allowlist',mode,retained))
            check(not any('SYNTHETIC_PRIVATE' in p.read_text() for p in evidence.iterdir()) if evidence.exists() else True,
                  'retained output contains no private synthetic data')

check("define('DISABLE_WP_CRON',true)" in SOURCE and "define('WP_HTTP_BLOCK_EXTERNAL',true)" in SOURCE
      and "define('WP_ENVIRONMENT_TYPE','local')" in SOURCE
      and "define('WP_HOME'" not in SOURCE and "define('WP_SITEURL'" not in SOURCE
      and 'search-replace' not in SOURCE, 'copy disables cron/external HTTP while retaining raw origin')
check('internal: true' in SOURCE and 'ports:' not in SOURCE and '@sha256:[a-f0-9]{64}$' in SOURCE,
      'copy uses pinned images on an internal network without published ports')
check(SOURCE.index('copy network is not internal before bootstrap')<SOURCE.index('observe cold-import'),
      'isolation is verified before WordPress can bootstrap')
check('if [ "$proof_mode" = cold ] && curl' in SOURCE,
      'recovery does not even run the cold-mode loopback availability HTTP probe')
check('volumes: ["$pub:/var/www/html", "$wwm:/wwm:ro", "$bk:$private_inside"]' in SOURCE and
      'private_inside=/home/customer/private-backups' in SOURCE and
      'chmod 600 "$bk/live-before-peek-release-20261111-162615-74851.sql" "$bk/peek-release-before-20261111-162615-e00022b6.json"' in SOURCE and
      'chown' not in SOURCE, 'incident private inputs use their exact path and 0600 mode without ownership changes')
wrapper=(HERE/'prove-live30-recovery.sh').read_text()
check('"$#" -eq 0' in wrapper and 'exec bash "$here/prove-live30-cold.sh" --recovery' in wrapper,
      'recovery wrapper exposes only the fixed selector and rejects arguments')

# The cleanup implementation is byte-identical to the existing independently
# fault-injected ownership/resource fixture; do not duplicate that matrix here.
print(f'LIVE30 cold-copy runner: {PASSED} offline source, IO, pipeline, ordering, privacy and failure controls passed; no site requests or Docker operations.')
