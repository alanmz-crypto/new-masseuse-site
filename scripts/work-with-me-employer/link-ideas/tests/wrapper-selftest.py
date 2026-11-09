# Offline wrapper evidence test: subprocess stubs only; no browser or network.
from pathlib import Path
import json,os,shutil,subprocess,tempfile
source=Path(__file__).resolve().parents[1]/'core-kiro-fixes'
passed=0
for mode in ['public','rehearsal']:
 for scenario,expected in [('pass',0),('missing',2),('zero',2),('failed',1),('invalid',2),('crash',2),('signal',2),('incomplete-retained',2)]:
  with tempfile.TemporaryDirectory(prefix='peek-wrapper-') as temp:
   base=Path(temp);package=base/'core-kiro-fixes';package.mkdir();retained=base/'core-title-rail';retained.mkdir()
   for name in ['run-gates.mjs','check-public.mjs','check-rehearsal.mjs']:shutil.copyfile(source/name,package/name)
   for name,label in [('check-peek-disclosures.mjs','disclosures'),('check-peek-kiro-layout.mjs','Kiro layout')]:
    code=f"const engine=process.argv.includes('--firefox')?'firefox':'chromium'; console.log('INFO Peek {label} '+engine+': 2 passed, 0 failed; actual cases');\n"
    if name=='check-peek-disclosures.mjs':
     if scenario=='missing':code="console.log('No final results');\n"
     if scenario=='zero':code="console.log('INFO Peek disclosures chromium: 0 passed, 0 failed; no cases');\n"
     if scenario=='failed':code="console.log('FAIL disclosure landing');console.log('INFO Peek disclosures chromium: 1 passed, 1 failed; cases');process.exit(1);\n"
     if scenario=='invalid':code="console.log('INVALID HTTP202: zero cases');process.exit(2);\n"
     if scenario=='crash':code="process.exit(1);\n"
     if scenario=='signal':code="process.kill(process.pid,'SIGTERM');\n"
    (base/name).write_text(code)
   lines=[]
   for i in range(9 if mode=='public' else 10):lines.append(f'INFO Peek retained {i}: 2 passed, 0 failed; retained')
   for i in range(3):lines.append(f'INFO title-rail {mode} '+('wrapper' if mode=='public' else 'suite')+f' {i}: child_exit=0; signal=none; effective_exit=0; 2 passed, 0 failed; stdout=retained; stderr=retained')
   if scenario!='incomplete-retained':lines.append(f'INFO Peek title-rail '+('public verification' if mode=='public' else mode)+': PASSED, 3 wrapper suites, 1s; complete')
   (retained/f'check-{mode}.mjs').write_text('console.log('+json.dumps('\n'.join(lines))+');\n')
   url='https://staging39.thaisolitude.com' if mode=='public' else 'http://localhost:8090'
   env=dict(os.environ,RUNNER_TEMP=str(base/'logs'))
   result=subprocess.run(['node',str(package/f'check-{mode}.mjs'),url],env=env,capture_output=True,text=True)

   if result.returncode!=expected:
    print('DEBUG',mode,scenario)
    print('CHILD', (base/'check-peek-disclosures.mjs').read_text())
    for log in (base/'logs').rglob('*.log'): print(log.name,repr(log.read_text()))
   assert result.returncode==expected,(mode,scenario,result.returncode,result.stdout,result.stderr)
   count=5 if scenario in ['pass','incomplete-retained'] else 1
   assert f'{count} wrapper suites' in result.stdout,(mode,scenario,result.stdout)
   if scenario=='pass':assert 'PASSED, 5 wrapper suites' in result.stdout
   logs=list((base/'logs'/'ops-out'/f'peek-kiro-fixes-{mode}').glob('*.log'))
   assert len(logs)==count*2,(mode,scenario,logs)
   passed+=1
for file,args in [('check-public.mjs',['https://thaisolitude.com']),('check-rehearsal.mjs',['https://staging39.thaisolitude.com']),('check-public.mjs',['https://staging39.thaisolitude.com','extra'])]:
 result=subprocess.run(['node',str(source/file),*args],capture_output=True,text=True)
 assert result.returncode==2 and 'INVALID' in result.stdout
 passed+=1
print(f'Wrapper evidence/exit/stop/origin harness: {passed} cases passed; no browser or network used.')
