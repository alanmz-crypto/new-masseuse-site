"""Offline execution of the historical input-focus and new neighbours push guards.

Uses local command doubles and temporary storage, never WordPress or network.
The remote copy replaces HOME references with a task-specific fixture root;
production argument parsing and command ordering run unchanged. PHP gates are
explicit doubles; actual HTML/PHP correctness belongs to the separate installer
and rendered-copy suites. Historical shared-wrapper dispatch is also exercised.
"""
from pathlib import Path
import json
import os
import socket
import subprocess
import tempfile

SOURCE = Path(__file__).resolve().parents[1]
PROFILE = globals().get('CORE_PUSH_PROFILE', 'input-focus')
assert PROFILE in ('input-focus', 'neighbours', 'index-clearance'), 'Unknown fixed push fixture profile'
COMPONENT = 'core-' + PROFILE
WRAPPER_NAME = 'push-peek-' + PROFILE + '.sh'
APPLIED_SOURCE = {'input-focus':'peek-nav-shared-v7.php', 'neighbours':'peek-nav-shared-v8.php', 'index-clearance':'peek-nav.php'}[PROFILE]
ORIGINAL_SOURCE = {'input-focus':'peek-nav-shared-v6.php', 'neighbours':'peek-nav-shared-v7.php', 'index-clearance':'peek-nav-shared-v8.php'}[PROFILE]
WRAPPER = (SOURCE / WRAPPER_NAME).read_text()
SHARED = (SOURCE / 'push-peek-nav.sh').read_text()
REMOTE = WRAPPER.split("read -r -d '' REMOTE <<'EOS' || true\n", 1)[1].split('\nEOS\n', 1)[0]
assert '<(' not in REMOTE, 'SiteGround remote Bash cannot use /dev/fd process substitution'
def inventory_body(source):
    return source.split("read -r -d '' INVENTORY <<'PHP' || true\n",1)[1].split('\nPHP\n',1)[0]
assert inventory_body(REMOTE)==inventory_body(SHARED), 'Keep the historical published-inventory guard exact'
SNAP_NAME = 'peek-template-' + COMPONENT + '-before-20261109-235959-a1b2c3d4.json'
SNAP = '/home/coretest/private-backups/'+SNAP_NAME
passed = 0

DOUBLE = r'''#!/usr/bin/env python3
from pathlib import Path
import json,os,shlex,sys
trace=Path(os.environ['CORE_TEST_TRACE'])
def event(name):
    rows=trace.read_text().splitlines() if trace.exists() else []
    with trace.open('a') as out: out.write(json.dumps(name)+'\n')
    at=len(rows)+1
    if at==int(os.environ.get('CORE_FAIL_AT','0')):sys.exit(73)
    if at==int(os.environ.get('CORE_FAIL_SECOND_AT','0')):sys.exit(74)
name=Path(sys.argv[0]).name; args=sys.argv[1:]
if name=='ssh-add':event('agent');sys.exit(0)
if name=='sync-server-backups.sh':event('offsite');sys.exit(0)
if name=='ssh':
    cmd=args[-1]
    if cmd=='true':event('connect');sys.exit(0)
    sys.stdin.read()
    if 'tar -xf' in cmd:event('upload');sys.exit(0)
    words=shlex.split(cmd);assert words[:3]==['bash','-s','--'],words
    phase,target,*rest=words[3:]
    event('remote:'+phase+(':'+json.dumps(rest,separators=(',',':')) if rest else ''))
    if phase=='apply':
        print('Old snapshot: /home/coretest/private-backups/peek-template-@@COMPONENT@@-before-20200101-000000-aaaaaaaa.json')
        if os.environ.get('CORE_OUTPUT')!='missing':
            returned=os.environ['CORE_SNAP']
            if os.environ.get('CORE_OUTPUT')=='wrong-component':returned=returned.replace('@@COMPONENT@@','core-kiro-fixes')
            print('Success: Peek @@COMPONENT@@ applied. Before-snapshot: '+returned)
            if os.environ.get('CORE_OUTPUT')=='duplicate':print('Success: Peek @@COMPONENT@@ applied. Before-snapshot: '+returned)
    sys.exit(0)
if name=='wp':
    words=[a for a in args if not a.startswith('--')]
    if '@@COMPONENT@@' in ['core-neighbours','core-index-clearance']:
        paths=[a.split('=',1)[1] for a in args if a.startswith('--path=')];assert len(paths)==1,args
        candidate=Path(paths[0]).name
        model=json.loads(os.environ.get('CORE_DISCOVERY_MAP','{}')).get(candidate,{})
        prefix=model.get('prefix',os.environ.get('CORE_TABLE_PREFIX','wp_'))
        home=model.get('home',os.environ.get('CORE_HOME_URL','https://staging39.thaisolitude.com'))
        if words[:2] in [['config','get'],['db','query']]:
            assert '--skip-plugins' in args and '--skip-themes' in args and not any(a.startswith('--user') for a in args),args
            assert '--exec=ini_set("log_errors", "0"); ini_set("error_log", "/dev/null");' in args,args
            if words[:2]==['config','get']:
                assert words==['config','get','table_prefix'] and '--type=variable' in args,args
                event('config-prefix');print(prefix);sys.exit(model.get('config_rc',0))
            assert words==['db','query',"SELECT option_value FROM "+prefix+"options WHERE option_name='home';"],args
            assert all(flag in args for flag in ['--skip-column-names','--batch','--raw']),args
            event('raw-home');print(home);sys.exit(model.get('query_rc',0))
        # Any command that bootstraps WordPress must target only the selected
        # staging install after raw discovery has established unique ownership.
        assert paths[0]==os.environ['CORE_SELECTED_SITE'] and '--skip-plugins=sg-security' in args,args
        assert words[:3]!=['option','get','home'], 'New discovery must not bootstrap WordPress'
    if words[:3]==['option','get','home']:
        event('discover');print(os.environ.get('CORE_HOME_URL','https://staging39.thaisolitude.com'));sys.exit(0)
    if words[:2]==['db','export']:
        event('export');Path(words[2]).write_text('' if os.environ.get('CORE_EMPTY_DUMP') else 'mock database\n');sys.exit(0)
    if words[:2]==['sg','purge']:event('purge');sys.exit(0)
    if words[0]=='eval':
        event('inventory-reviewed' if os.environ.get('WWM_PEEK_REVIEWED_PAGES') else 'inventory')
        if not os.environ.get('CORE_EMPTY_INVENTORY'):
            print('home\t/\t__home__\npage-10\t/thai-massage/\tthai-massage')
        sys.exit(0)
    assert words[0]=='eval-file',words
    script,action,*rest=words[1:]
    if action=='preflight':
        event('preflight:'+':'.join(rest));state=os.environ.get('CORE_STATE','ready') if rest[0]=='@@COMPONENT@@' else 'applied'
        print('Success: '+':'.join(rest)+' '+state+'; exact mock state')
    elif action in ['verify','rollback']:
        assert rest[0]==os.environ['CORE_SNAP'],rest
        event(action+(':'+rest[1] if len(rest)>1 else ':applied'))
    else:
        assert action=='apply' and rest==['@@COMPONENT@@'],words
        event('apply');print('Success: Peek @@COMPONENT@@ applied. Before-snapshot: '+os.environ['CORE_SNAP'])
    sys.exit(0)
if name=='curl':
    route=args[-1].split('thaisolitude.com',1)[1]
    event('get:'+route)
    html='<style id="ts-peek-nav-css"></style><script id="ts-peek-nav-runtime"></script><script id="ts-peek-nav-config"></script>LMT-29905'
    if route=='/thai-massage/':html+='<script id="ts-peek-nav-data"></script><nav class="ts-jump"></nav>'
    Path(args[args.index('-o')+1]).write_text(html)
    print(os.environ.get('CORE_HTTP','200'),end='');sys.exit(0)
if name=='php':
    assert args[0]=='-r',args
    if 'Snapshot belongs to another update' in args[1]:
        assert len(args)==3 and args[2]==os.environ['CORE_SNAP'],args
        assert 'peek-@@COMPONENT@@-v1' in args[1] and '"@@COMPONENT@@"' in args[1] and '"slug"' in args[1],args[1]
        event('own-snapshot');sys.exit(0)
    assert len(args)==5 and 'FAIL exact served' in args[1],args
    event('render:'+Path(args[-3]).name+':'+args[-1]);sys.exit(0)
raise AssertionError((name,args))
'''.replace('@@COMPONENT@@', COMPONENT)


def fixture(temp):
    base=Path(temp); commands=base/'bin';commands.mkdir()
    double=commands/'double.py';double.write_text(DOUBLE);double.chmod(0o700)
    for name in ['ssh','ssh-add','wp','curl','php']:(commands/name).symlink_to(double)
    (base/'sync-server-backups.sh').symlink_to(double)
    package=base/'link-ideas';package.mkdir()
    (package/WRAPPER_NAME).write_text(WRAPPER)
    (package/WRAPPER_NAME).chmod(0o700)
    (package/'push-peek-nav.sh').write_text(SHARED)
    for name in ['install-peek-nav.php','peek-nav.php','peek-nav-legacy.php','title-strip-pages.json',
                 *['peek-nav-shared-v'+str(i)+'.php' for i in range(1,9)]]:
        (package/name).write_text('offline payload fixture\n')
    (package/'peek-data').mkdir()
    creds=base/'creds.txt';creds.write_text('Host: mock.invalid\nPort: 22\nUser: coretest\n')
    trace=base/'trace.jsonl'
    env=dict(os.environ,PATH=str(commands)+os.pathsep+os.environ['PATH'],CORE_TEST_TRACE=str(trace),
             CORE_SNAP=SNAP,S26_CREDS=str(creds),S_TARGET='staging39',TS_OFFSITE_HOOK=str(double))
    for key in ['CORE_FAIL_AT','CORE_FAIL_SECOND_AT','CORE_OUTPUT','CORE_STATE','CORE_HTTP',
                'CORE_EMPTY_DUMP','CORE_EMPTY_INVENTORY','CORE_HOME_URL','CORE_TABLE_PREFIX','CORE_DISCOVERY_MAP','CORE_SELECTED_SITE']:env.pop(key,None)
    return base,package,trace,env


def run_outer(args,expected,*,valid=True,fail_at=0,options=None,remove=(),shared=False,exit_code=None):
    global passed
    with tempfile.TemporaryDirectory(prefix='core-push-outer-') as temp:
        base,package,trace,env=fixture(temp)
        sock=socket.socket(socket.AF_UNIX);sock.bind(str(base/'agent.sock'))
        env.update(SSH_AUTH_SOCK=str(base/'agent.sock'),CORE_FAIL_AT=str(fail_at));env.update(options or {})
        for key in remove:env.pop(key,None)
        script=package/('push-peek-nav.sh' if shared else WRAPPER_NAME)
        result=subprocess.run(['bash',str(script),*args],env=env,capture_output=True,text=True);sock.close()
        actual=[json.loads(s) for s in trace.read_text().splitlines()] if trace.exists() else []
        assert (result.returncode==0)==valid,('outer exit',args,shared,options,fail_at,result.returncode,result.stdout,result.stderr)
        assert actual==expected,('outer order',args,shared,options,fail_at,expected,actual)
        if exit_code is not None:assert result.returncode==exit_code,(result.returncode,exit_code)
        passed+=1


def remote_event(phase,*args):
    return 'remote:'+phase+(':'+json.dumps(list(args),separators=(',',':')) if args else '')


PREFIX=['agent','connect','upload']
APPLY=PREFIX+[remote_event('prepare'),'offsite',remote_event('apply'),'offsite',remote_event('verify-purged',SNAP)]
VERIFY=PREFIX+[remote_event('verify',SNAP)]
ROLLBACK=PREFIX+[remote_event('prepare-rollback',SNAP),'offsite',remote_event('rollback',SNAP),'offsite']
for args,expected in [([],PREFIX+[remote_event('preflight')]),(['--apply'],APPLY),
                      (['--verify',SNAP],VERIFY),(['--rollback',SNAP],ROLLBACK)]:
    run_outer(args,expected)
    run_outer([COMPONENT,*args],expected,shared=True)
    for at in range(1,len(expected)+1):
        stop=at+1 if args==['--apply'] and at==6 else at
        run_outer(args,expected[:stop],fail_at=at,valid=False)
run_outer(['--verify',SNAP],VERIFY,remove=['TS_OFFSITE_HOOK'])
# Snapshot-free --check must never fall through to the historical v6 checker.
run_outer([COMPONENT,'--check'],[],shared=True,valid=False)
for output in ['missing','duplicate','wrong-component']:
    run_outer(['--apply'],APPLY[:7],options={'CORE_OUTPUT':output},valid=False)
run_outer(['--apply'],APPLY[:7],fail_at=6,options={'CORE_FAIL_SECOND_AT':'7'},valid=False,exit_code=73)
for args,options,remove in [
    (['--apply'],{},['TS_OFFSITE_HOOK']),
    (['--rollback',SNAP],{'TS_OFFSITE_HOOK':'/does/not/exist'},[]),
    (['--apply'],{'S_TARGET':'live'},[]),
    (['--apply'],{'S_TARGET':'main'},[]),
    (['--unknown'],{},[]),(['--apply','extra'],{},[]),
    (['--verify'],{},[]),(['--rollback'],{},[]),
    (['--verify',SNAP,'extra'],{},[]),
    *[(['--verify',path],{},[]) for path in ['/tmp/'+SNAP_NAME,
        '/home/coretest/private-backups/../'+SNAP_NAME,
        SNAP.replace(COMPONENT,'core-kiro-fixes'),
        '/home/coretest/private-backups/latest.json',SNAP+';id']],
]:run_outer(args,[],options=options,remove=remove,valid=False)
# The dispatch must not route historical components through new core-only gates.
for component in ['core','core-upgrade','core-kiro-fixes']:
    for args,phases in [([],['preflight']),(['--apply'],['prepare','offsite','apply','offsite','verify']),(['--check'],['verify'])]:
        expected=PREFIX+[p if p=='offsite' else remote_event(p,component,'') for p in phases]
        run_outer([component,*args],expected,shared=True,remove=['TS_OFFSITE_HOOK'])
run_outer(['page','thai-massage','--check'],PREFIX+[remote_event('verify','page','thai-massage')],shared=True,remove=['TS_OFFSITE_HOOK'])
run_outer([],PREFIX+[remote_event('preflight','core','')],shared=True,remove=['TS_OFFSITE_HOOK'])
legacy_snap='/home/coretest/private-backups/peek-template-core-kiro-fixes-before-20261109-235959-a1b2c3d4.json'
run_outer(['core-kiro-fixes','--rollback',legacy_snap],
          PREFIX+[remote_event('prepare','core-kiro-fixes',''),'offsite',
                  remote_event('rollback','core-kiro-fixes','',legacy_snap),'offsite'],
          shared=True,remove=['TS_OFFSITE_HOOK'])


def renders(source):
    return ['inventory-reviewed','get:/','render:'+source+':__home__',
            'get:/thai-massage/','render:'+source+':thai-massage']


DISCOVERY = ['config-prefix','raw-home'] if PROFILE in ('neighbours', 'index-clearance') else ['discover']
REMOTE_CASES={
    'preflight':[*DISCOVERY,'inventory-reviewed','preflight:'+COMPONENT],
    'prepare':[*DISCOVERY,'inventory-reviewed','preflight:'+COMPONENT,'export'],
    'prepare-rollback':[*DISCOVERY,'preflight:'+COMPONENT,'own-snapshot','verify:applied','export'],
    'apply':[*DISCOVERY,'inventory-reviewed','preflight:'+COMPONENT,'apply'],
    'verify':[*DISCOVERY,'own-snapshot','verify:applied',*renders(APPLIED_SOURCE),'own-snapshot','verify:applied'],
    'verify-purged':[*DISCOVERY,'own-snapshot','verify:applied','purge',*renders(APPLIED_SOURCE),'own-snapshot','verify:applied'],
    'rollback':[*DISCOVERY,'preflight:'+COMPONENT,'own-snapshot','verify:applied','rollback:applied','purge',
                *renders(ORIGINAL_SOURCE),'own-snapshot','verify:original'],
}


def run_remote(phase,expected,*,valid=True,fail_at=0,options=None,target='staging39',duplicate=False,literal_home=False,extra_sites=(),expected_alternatives=()):
    global passed
    with tempfile.TemporaryDirectory(prefix='core-push-remote-') as temp:
        base,package,trace,env=fixture(temp)
        home=base/'remote-home';site=home/'www'/'staging39';site.mkdir(parents=True)
        (site/'wp-config.php').write_text('fixture\n')
        if duplicate:
            second=home/'www'/'duplicate';second.mkdir();(second/'wp-config.php').write_text('fixture\n')
        for extra in extra_sites:
            other=home/'www'/extra;other.mkdir();(other/'wp-config.php').write_text('fixture\n')
        snapshot=home/'private-backups'/SNAP_NAME
        env.update(CORE_TEST_HOME=str(home),CORE_SNAP=str(snapshot),CORE_FAIL_AT=str(fail_at),CORE_SELECTED_SITE=str(site),
                   CORE_STATE='applied' if phase in ['prepare-rollback','rollback'] else 'ready')
        env.update(options or {})
        # Keep the literal \$HOME pattern; only the real filesystem root changes.
        body=REMOTE.replace('$HOME','$CORE_TEST_HOME').replace(r'\$CORE_TEST_HOME',r'\$HOME')
        snap='$HOME/private-backups/'+SNAP_NAME if literal_home else str(snapshot)
        result=subprocess.run(['bash','-s','--',phase,target,snap],input=body,
                              env=env,capture_output=True,text=True)
        actual=[json.loads(s) for s in trace.read_text().splitlines()] if trace.exists() else []
        assert (result.returncode==0)==valid,('remote exit',phase,options,fail_at,result.returncode,result.stdout,result.stderr)
        assert actual==expected or actual in expected_alternatives,('remote order',phase,options,fail_at,expected,actual,result.stdout,result.stderr)
        passed+=1


for phase,expected in REMOTE_CASES.items():
    run_remote(phase,expected)
    for at in range(1,len(expected)+1):
        run_remote(phase,expected[:at],fail_at=at,valid=False)
for phase in ['verify','verify-purged','prepare-rollback','rollback']:
    run_remote(phase,REMOTE_CASES[phase],literal_home=True)
for phase in ['prepare','apply']:
    for state in ['applied','CHANGED','unsupported']:
        run_remote(phase,REMOTE_CASES[phase][:len(DISCOVERY)+2],options={'CORE_STATE':state},valid=False)
for phase in ['prepare-rollback','rollback']:
    original=[event.replace('verify:applied','verify:original') for event in REMOTE_CASES[phase]]
    run_remote(phase,original,options={'CORE_STATE':'ready'})
    for state in ['CHANGED','unsupported']:
        run_remote(phase,REMOTE_CASES[phase][:len(DISCOVERY)+1],options={'CORE_STATE':state},valid=False)
for phase in ['prepare','prepare-rollback']:
    run_remote(phase,REMOTE_CASES[phase],options={'CORE_EMPTY_DUMP':'1'},valid=False)
for phase in ['verify','verify-purged','rollback']:
    expected=REMOTE_CASES[phase]
    for status in ['202','403','500','000']:
        stop=expected.index('get:/')+1
        run_remote(phase,expected[:stop],options={'CORE_HTTP':status},valid=False)
    stop=expected.index('inventory-reviewed')+1
    run_remote(phase,expected[:stop],options={'CORE_EMPTY_INVENTORY':'1'},valid=False)
run_remote('apply',[],target='live',valid=False)
run_remote('apply',DISCOVERY,options={'CORE_HOME_URL':'https://thaisolitude.com'},valid=False)
run_remote('apply',DISCOVERY*2,duplicate=True,valid=False)
run_remote('not-a-phase',DISCOVERY,valid=False)
if PROFILE in ('neighbours', 'index-clearance'):
    # Neither malformed prefixes nor failing before-WP-load commands may reach
    # a SQL interpolation, plugin bootstrap, export or write.
    for prefix in ['', 'wp-', 'wp ', ' wp_', 'wp_\nother_', 'wp_`', "wp_; DROP TABLE x;--", 'wp.']:
        run_remote('apply',['config-prefix'],options={'CORE_TABLE_PREFIX':prefix},valid=False)
    for candidate,events in [({'prefix':'wp_','config_rc':47},['config-prefix']),
                             ({'home':'https://staging39.thaisolitude.com','query_rc':48},DISCOVERY)]:
        run_remote('apply',events,options={'CORE_DISCOVERY_MAP':json.dumps({'staging39':candidate})},valid=False)
    for prefix in ['iej_', 'WP9_', '0_']:
        run_remote('preflight',REMOTE_CASES['preflight'],options={'CORE_TABLE_PREFIX':prefix})
    for home in ['http://staging39.thaisolitude.com', 'https://staging39.thaisolitude.com',
                 'http://staging39.thaisolitude.com/', 'https://staging39.thaisolitude.com/']:
        run_remote('preflight',REMOTE_CASES['preflight'],options={'CORE_HOME_URL':home})
    for home in ['https://staging39.thaisolitude.com.evil.invalid', 'https://staging39.thaisolitude.com:443',
                 'https://staging39.thaisolitude.com/path', 'https://staging38.thaisolitude.com', '']:
        run_remote('apply',DISCOVERY,options={'CORE_HOME_URL':home},valid=False)
    for phase in ['prepare','apply','rollback']:
        expected=DISCOVERY*2+REMOTE_CASES[phase][len(DISCOVERY):]
        run_remote(phase,expected,extra_sites=('live',),
                   options={'CORE_DISCOVERY_MAP':json.dumps({'live':{'home':'https://thaisolitude.com'}})})
    # An unreadable unrelated config is skipped without bootstrapping it; the
    # exact staging candidate remains the only destination for later commands.
    expected=DISCOVERY+['config-prefix']+REMOTE_CASES['preflight'][len(DISCOVERY):]
    # find does not promise candidate order; both exact permitted traces keep
    # every discovery before any selected-site bootstrap.
    other_order=['config-prefix']+DISCOVERY+REMOTE_CASES['preflight'][len(DISCOVERY):]
    run_remote('preflight',expected,extra_sites=('live',),expected_alternatives=(other_order,),
               options={'CORE_DISCOVERY_MAP':json.dumps({'live':{'config_rc':49}})})
print(f'Core {PROFILE} push order/refusal/dispatch harness: {passed} cases passed; no network, services or HOME changes.')
