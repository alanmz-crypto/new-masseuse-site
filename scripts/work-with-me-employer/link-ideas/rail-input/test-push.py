"""Execute push-rail-input's outer and remote Bash with local command doubles.

No network, WordPress, real backups or HOME changes. The extracted remote body
uses RAIL_TEST_HOME; only its /home/<user> observation-prefix matcher is adapted
to temporary storage. Production CLI path validation runs unmodified. wp,
curl and php are explicit gate doubles; rendered HTML correctness is covered by
the actual rehearsal/public checks, not claimed by this ordering harness.
"""
from pathlib import Path
import json
import os
import socket
import subprocess
import tempfile

SOURCE = Path(__file__).resolve().parents[1]
WRAPPER = (SOURCE / 'push-rail-input.sh').read_text()
REMOTE = WRAPPER.split("read -r -d '' REMOTE <<'EOS' || true\n", 1)[1].split('\nEOS\n', 1)[0]
assert '<(' not in REMOTE, 'SiteGround remote Bash has no /dev/fd process substitution'
SNAP = '/home/railtest/private-backups/rail-input-before-20261109-235959-a1b2c3d4.json'
WARM = '/home/railtest/private-backups/rail-input-observe-20261109-235958-b1b2c3d4.json'
passed = 0

# A single command double records actual command order and can fail exactly one
# reached gate. It never calls a network client or a production service.
DOUBLE = r'''#!/usr/bin/env python3
from pathlib import Path
import json, os, shlex, sys
trace=Path(os.environ['RAIL_TEST_TRACE'])
def event(name):
    rows=trace.read_text().splitlines() if trace.exists() else []
    with trace.open('a') as out: out.write(json.dumps(name)+'\n')
    if len(rows)+1 == int(os.environ.get('RAIL_FAIL_AT','0')): sys.exit(73)
    if len(rows)+1 == int(os.environ.get('RAIL_FAIL_SECOND_AT','0')): sys.exit(74)
def events(): return [json.loads(s) for s in trace.read_text().splitlines()]
name=Path(sys.argv[0]).name
args=sys.argv[1:]
if name=='ssh-add': event('agent'); sys.exit(0)
if name=='sync-server-backups.sh': event('offsite'); sys.exit(0)
if name=='ssh':
    cmd=args[-1]
    if cmd=='true': event('connect'); sys.exit(0)
    sys.stdin.read()
    if 'tar -xf' in cmd: event('upload'); sys.exit(0)
    words=shlex.split(cmd); assert words[:3]==['bash','-s','--'], words
    phase, target, *rest=words[3:]
    event('remote:'+phase+(':'+rest[0] if rest else ''))
    if phase=='settle-original':
        if os.environ.get('RAIL_OUTPUT')!='no-observation':
            print('Settled-observation: '+os.environ['RAIL_WARM'])
            if os.environ.get('RAIL_OUTPUT')=='two-observations': print('Settled-observation: '+os.environ['RAIL_WARM'])
    elif phase=='apply':
        print('Old snapshot: /home/railtest/private-backups/rail-input-before-20200101-000000-aaaaaaaa.json')
        if os.environ.get('RAIL_OUTPUT')!='no-snapshot':
            print('Success: Applied. Before-snapshot: '+os.environ['RAIL_SNAP'])
            if os.environ.get('RAIL_OUTPUT')=='two-snapshots': print('Success: Applied. Before-snapshot: '+os.environ['RAIL_SNAP'])
    sys.exit(0)
if name=='wp':
    words=[a for a in args if not a.startswith('--')]
    if words[:3]==['option','get','home']:
        event('discover'); print(os.environ.get('RAIL_HOME_URL','https://staging39.thaisolitude.com')); sys.exit(0)
    assert os.environ.get('WWM_BACKUP_DIR')==os.environ['RAIL_EXPECT_PRIVATE'], 'canonical backup root not exported'
    if words[:2]==['db','export']:
        event('export:'+os.environ['RAIL_PHASE'].removeprefix('prepare-'))
        Path(words[2]).write_text('' if os.environ.get('RAIL_EMPTY_DUMP') else 'mock database export\n'); sys.exit(0)
    if words[:2]==['sg','purge']: event('purge'); sys.exit(0)
    assert words[0]=='eval-file', words
    script, action, *rest=words[1:]
    if script.endswith('/observe-state.php'):
        if action=='record':
            which='cold' if not any(e.startswith('record:') for e in (events() if trace.exists() else [])) else 'warm'
            event('record:'+which)
            if os.environ.get('RAIL_RECORD_OUTPUT')=='missing': sys.exit(0)
            path=os.environ['RAIL_COLD' if which=='cold' else 'RAIL_WARM']
            if os.environ.get('RAIL_RECORD_OUTPUT')=='wrong-parent':
                path=str(Path(path).parents[2]/'foreign-home'/'private-backups'/Path(path).name)
            print('Success: Observation saved. Observation: '+path)
            if os.environ.get('RAIL_RECORD_OUTPUT')=='duplicate': print('Success: Observation saved. Observation: '+path)
        else:
            assert rest[0] in [os.environ['RAIL_COLD'],os.environ['RAIL_WARM']], rest
            event('compare:'+Path(rest[0]).name+':'+(rest[1] if len(rest)>1 else 'exact'))
    elif action=='preflight':
        event('preflight'); print('home=mock rail-input='+os.environ.get('RAIL_STATE','ready')+' page=1348')
    else:
        if action in ['verify','rollback']: assert rest[0]==os.environ['RAIL_SNAP'], rest
        event(action+(':'+Path(rest[0]).name if rest else '')+(':'+rest[1] if len(rest)>1 else ''))
    sys.exit(0)
if name=='curl':
    event('get'); Path(args[args.index('-o')+1]).write_text('mock rendered HTML'); print(os.environ.get('RAIL_HTTP','200'),end=''); sys.exit(0)
if name=='php': event('render:'+Path(args[-2]).name); sys.exit(0)
raise AssertionError((name,args))
'''


def fixture(temp):
    base = Path(temp)
    bin_dir = base / 'bin'
    bin_dir.mkdir()
    command = bin_dir / 'double.py'
    command.write_text(DOUBLE)
    command.chmod(0o700)
    for name in ['ssh', 'ssh-add', 'wp', 'curl', 'php']:
        (bin_dir / name).symlink_to(command)
    (base / 'sync-server-backups.sh').symlink_to(command)
    package = base / 'link-ideas'
    (package / 'rail-input').mkdir(parents=True)
    (package / 'push-rail-input.sh').write_text(WRAPPER)
    for item in ['rail-input/install-rail-input.php', 'rail-input/observe-state.php', 'rail-ideas-v1.html', 'rail-ideas.html']:
        (package / item).write_text('offline upload fixture\n')
    creds = base / 'credentials.txt'
    creds.write_text('Host: mock.invalid\nPort: 22\nUser: railtest\n')
    trace = base / 'trace.jsonl'
    env = dict(os.environ, PATH=str(bin_dir)+os.pathsep+os.environ['PATH'],
               RAIL_TEST_TRACE=str(trace), RAIL_SNAP=SNAP, RAIL_WARM=WARM,
               S26_CREDS=str(creds), S_TARGET='staging39', TS_OFFSITE_HOOK=str(command))
    for key in ['RAIL_FAIL_AT', 'RAIL_OUTPUT', 'RAIL_STATE', 'RAIL_HTTP',
                'RAIL_RECORD_OUTPUT', 'RAIL_HOME_URL', 'RAIL_EMPTY_DUMP', 'RAIL_FAIL_SECOND_AT']:
        env.pop(key, None)
    return base, package, trace, env


def run_outer(args, expected, *, fail_at=0, options=None, remove=(), valid=True, exit_code=None):
    global passed
    with tempfile.TemporaryDirectory(prefix='rail-push-outer-') as temp:
        base, package, trace, env = fixture(temp)
        sock = socket.socket(socket.AF_UNIX)
        sock.bind(str(base / 'agent.sock'))
        env['SSH_AUTH_SOCK'] = str(base / 'agent.sock')
        env['RAIL_FAIL_AT'] = str(fail_at)
        env.update(options or {})
        for key in remove:
            env.pop(key, None)
        result = subprocess.run(['bash', str(package / 'push-rail-input.sh'), *args],
                                env=env, capture_output=True, text=True)
        sock.close()
        actual = [json.loads(line) for line in trace.read_text().splitlines()] if trace.exists() else []
        assert (result.returncode == 0) == valid, ('outer exit', args, options, fail_at, result.returncode, result.stdout, result.stderr)
        assert actual == expected, ('outer order', args, options, fail_at, expected, actual)
        if exit_code is not None:
            assert result.returncode == exit_code, ('outer preserved exit', result.returncode, exit_code)
        passed += 1


PREFIX = ['agent', 'connect', 'upload']
APPLY = PREFIX + ['remote:prepare-cold', 'offsite', 'remote:settle-original',
                  'remote:prepare-settled:'+WARM, 'offsite', 'remote:apply:'+WARM,
                  'offsite', 'remote:verify-purged:'+SNAP]
VERIFY = PREFIX + ['remote:verify:'+SNAP]
ROLLBACK = PREFIX + ['remote:prepare-rollback', 'offsite', 'remote:rollback:'+SNAP, 'offsite']
for args, expected in [([], PREFIX+['remote:preflight']), (['--apply'], APPLY),
                       (['--verify', SNAP], VERIFY), (['--rollback', SNAP], ROLLBACK)]:
    run_outer(args, expected)
    for at in range(1, len(expected)+1):
        # A failed apply still synchronizes any rollback snapshot before stopping.
        stop = at+1 if args == ['--apply'] and at == 9 else at
        run_outer(args, expected[:stop], fail_at=at, valid=False)
# Verify has no hook requirement, backup, purge or CAS/apply phase.
run_outer(['--verify', SNAP], VERIFY, remove=['TS_OFFSITE_HOOK'])
run_outer(['--verify', '$HOME/private-backups/'+Path(SNAP).name],
          PREFIX+['remote:verify:$HOME/private-backups/'+Path(SNAP).name])
for output, stop in [('no-observation', 6), ('two-observations', 6),
                     ('no-snapshot', 10), ('two-snapshots', 10)]:
    run_outer(['--apply'], APPLY[:stop], options={'RAIL_OUTPUT': output}, valid=False)
run_outer(['--apply'], APPLY[:10], fail_at=9,
          options={'RAIL_FAIL_SECOND_AT': '10'}, valid=False, exit_code=73)
for args, options, remove in [
    (['--apply'], {}, ['TS_OFFSITE_HOOK']),
    (['--rollback', SNAP], {'TS_OFFSITE_HOOK': '/does/not/exist'}, []),
    (['--apply'], {'S_TARGET': 'live'}, []),
    (['--apply'], {'S_TARGET': 'main'}, []),
    (['--apply'], {'S26_CREDS': '/does/not/exist'}, []),
    (['--unknown'], {}, []), (['--apply', 'extra'], {}, []),
    (['--verify'], {}, []), (['--rollback'], {}, []),
    (['--verify', SNAP, 'extra'], {}, []),
    *[(['--verify', path], {}, []) for path in [
        '/tmp/'+Path(SNAP).name, '/home/railtest/private-backups/../'+Path(SNAP).name,
        '/home/railtest/private-backups/latest.json',
        '/home/railtest/private-backups/rail-input-before-20261109-235959-a1b2c3d4.json;id']],
]:
    run_outer(args, [], options=options, remove=remove, valid=False)


COLD_NAME = 'rail-input-observe-20261109-235957-c1b2c3d4.json'
WARM_NAME = Path(WARM).name
SNAP_NAME = Path(SNAP).name
COMPARE_WARM = 'compare:'+WARM_NAME+':exact'
REMOTE_CASES = {
    'preflight': ['discover', 'preflight'],
    'prepare-cold': ['discover', 'preflight', 'export:cold'],
    'prepare-settled': ['discover', 'preflight', COMPARE_WARM, 'export:settled'],
    'prepare-rollback': ['discover', 'preflight', 'export:rollback'],
    'settle-original': ['discover', 'preflight', 'record:cold', 'purge', 'get',
        'render:rail-ideas-v1.html', 'compare:'+COLD_NAME+':cold-assets', 'record:warm',
        'get', 'render:rail-ideas-v1.html', COMPARE_WARM, 'purge', 'get',
        'render:rail-ideas-v1.html', COMPARE_WARM, 'preflight'],
    'apply': ['discover', 'preflight', COMPARE_WARM, 'apply'],
    'verify': ['discover', 'verify:'+SNAP_NAME, 'get', 'render:rail-ideas.html', 'verify:'+SNAP_NAME],
    'verify-purged': ['discover', 'verify:'+SNAP_NAME, 'purge', 'get', 'render:rail-ideas.html', 'verify:'+SNAP_NAME],
    'rollback': ['discover', 'rollback:'+SNAP_NAME, 'purge', 'get', 'render:rail-ideas-v1.html', 'verify:'+SNAP_NAME+':original'],
}


def run_remote(phase, expected, *, fail_at=0, options=None, valid=True, target='staging39', duplicate=False, literal_home=False, home_alias=False, trailing_slash=False, old_home_compare=False):
    global passed
    with tempfile.TemporaryDirectory(prefix='rail-push-remote-') as temp:
        base, package, trace, env = fixture(temp)
        canonical_home = base / ('canonical-home' if home_alias else 'remote-home')
        canonical_home.mkdir()
        home = base / 'configured-home' if home_alias else canonical_home
        if home_alias:
            home.symlink_to(canonical_home, target_is_directory=True)
        configured_home = str(home) + ('/' if trailing_slash else '')
        site = home / 'www' / 'staging39'
        site.mkdir(parents=True)
        (site / 'wp-config.php').write_text('fixture\n')
        if duplicate:
            (home / 'www' / 'duplicate').mkdir()
            (home / 'www' / 'duplicate' / 'wp-config.php').write_text('fixture\n')
        private = canonical_home / 'private-backups'
        env.update(RAIL_TEST_HOME=configured_home, RAIL_EXPECT_PRIVATE=str(private), RAIL_PHASE=phase,
                   RAIL_COLD=str(private / COLD_NAME), RAIL_WARM=str(private / WARM_NAME),
                   RAIL_SNAP=str(private / SNAP_NAME), RAIL_FAIL_AT=str(fail_at))
        env.update(options or {})
        body = REMOTE.replace('$HOME', '$RAIL_TEST_HOME').replace(r'\$RAIL_TEST_HOME', r'\$HOME')
        # sed's production prefix is rooted at /home/<user>; only the fixture
        # storage prefix changes. The date/name/unique-return checks stay exact.
        old_prefix = r'\/home\/[a-zA-Z0-9_-]*'
        assert old_prefix in body
        # Keep the user-name level variable, just like production, so an
        # otherwise-valid foreign parent must fail the canonical equality guard.
        body = body.replace(old_prefix, str(base).replace('/', r'\/') + r'\/[a-zA-Z0-9_-]*')
        if old_home_compare:
            current = '[ "${found%/*}" = "$private_dir" ]'
            assert current in body
            body = body.replace(current, '[ "${found%/*}" = "$RAIL_TEST_HOME/private-backups" ]')
        snap = env['RAIL_WARM'] if phase in ['prepare-settled', 'apply'] else env['RAIL_SNAP']
        if literal_home:
            snap = '$HOME/private-backups/'+SNAP_NAME
        result = subprocess.run(['bash', '-s', '--', phase, target, snap], input=body,
                                env=env, capture_output=True, text=True)
        actual = [json.loads(line) for line in trace.read_text().splitlines()] if trace.exists() else []
        assert (result.returncode == 0) == valid, ('remote exit', phase, options, fail_at, result.returncode, result.stdout, result.stderr)
        assert actual == expected, ('remote order', phase, options, fail_at, expected, actual, result.stdout, result.stderr)
        if home_alias or trailing_slash:
            assert f'Private backup directory: configured={configured_home}/private-backups canonical={private}' in result.stdout, result.stdout
            if not valid:
                count = 0 if env.get('RAIL_RECORD_OUTPUT') == 'missing' else 2 if env.get('RAIL_RECORD_OUTPUT') == 'duplicate' else 1
                assert f'Observation refused: candidates={count} basename=' in result.stderr, result.stderr
                assert f'canonical={private}' in result.stderr, result.stderr
        if valid and phase == 'settle-original':
            assert result.stdout.splitlines()[-1] == 'Settled-observation: '+env['RAIL_WARM']
        passed += 1


for phase, expected in REMOTE_CASES.items():
    run_remote(phase, expected)
    for at in range(1, len(expected)+1):
        run_remote(phase, expected[:at], fail_at=at, valid=False)
for phase in ['verify', 'rollback']:
    run_remote(phase, REMOTE_CASES[phase], literal_home=True)
for phase in ['prepare-cold', 'prepare-settled', 'settle-original', 'apply']:
    for state in ['applied', 'CHANGED', 'unsupported']:
        run_remote(phase, ['discover', 'preflight'], options={'RAIL_STATE': state}, valid=False)
# Existing applied state is permitted for verify and rollback, never re-apply.
for phase in ['prepare-rollback', 'rollback', 'verify']:
    run_remote(phase, REMOTE_CASES[phase], options={'RAIL_STATE': 'applied'})
for phase in ['prepare-cold', 'prepare-settled', 'prepare-rollback']:
    run_remote(phase, REMOTE_CASES[phase], options={'RAIL_EMPTY_DUMP': '1'}, valid=False)
for status in ['202', '403', '500', '000']:
    run_remote('verify', REMOTE_CASES['verify'][:3], options={'RAIL_HTTP': status}, valid=False)
for output in ['missing', 'duplicate']:
    run_remote('settle-original', REMOTE_CASES['settle-original'][:3],
               options={'RAIL_RECORD_OUTPUT': output}, valid=False)
# SiteGround can expose HOME through an alias or with a trailing slash while
# PHP realpath() returns /home/customer. Execute those actual shell paths.
run_remote('settle-original', REMOTE_CASES['settle-original'], home_alias=True)
run_remote('settle-original', REMOTE_CASES['settle-original'], trailing_slash=True)
run_remote('settle-original', REMOTE_CASES['settle-original'], home_alias=True, trailing_slash=True)
for output in ['wrong-parent', 'duplicate', 'missing']:
    run_remote('settle-original', REMOTE_CASES['settle-original'][:3], home_alias=True,
               options={'RAIL_RECORD_OUTPUT': output}, valid=False)
# Negative control: restoring only the pre-fix literal HOME parent comparison
# reproduces the refusal for canonical output, with no purge/render/write reached.
run_remote('settle-original', REMOTE_CASES['settle-original'][:3], home_alias=True,
           old_home_compare=True, valid=False)
run_remote('apply', [], target='live', valid=False)
run_remote('apply', ['discover'], options={'RAIL_HOME_URL': 'https://thaisolitude.com'}, valid=False)
run_remote('apply', ['discover', 'discover'], duplicate=True, valid=False)
run_remote('not-a-phase', ['discover'], valid=False)
print(f'Rail push outer/remote ordering, refusal and stop harness: {passed} cases passed; no network, services or HOME changes.')
