"""Offline wrapper command doubles; no SSH, HTTP, WP or encrypted-store proof."""
import hashlib
import importlib.util
import json
import copy
import os
from pathlib import Path
import socket
import subprocess
import tempfile

HERE = Path(__file__).resolve().parent
PUSH = HERE.parent/'push-peek-release.sh'
BACKUP = '/home/releasefixture/private-backups/live-before-peek-release-20261111-120000-123.sql'
SNAP = '/home/releasefixture/private-backups/peek-release-before-20261111-120001-1234abcd.json'
LIVE30_SNAP = '/home/customer/private-backups/peek-release-before-20261111-162615-e00022b6.json'
LIVE30_SHA = '4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b'
def sha(data): return hashlib.sha256(data).hexdigest()
def canonical(obj): return json.dumps(obj, sort_keys=True, ensure_ascii=False, separators=(',', ':')).encode()
SSH = r'''#!/usr/bin/env python3
import hashlib,json,os,shlex,sys
from pathlib import Path
state=json.loads(Path(os.environ['RELEASE_FIXTURE']).read_text()); scenario=os.environ['RELEASE_CASE']
def event(name):
 with open(os.environ['RELEASE_EVENTS'],'a') as f:f.write(name+'\n')
def transfer(kind,path):
 body=state['files'][path].encode(); digest=hashlib.sha256(body).hexdigest(); n=len(body)
 if scenario=='backup-hash' and kind=='RELEASE-BACKUP':digest='0'*64
 if scenario=='journal-size' and kind=='RELEASE-JOURNAL':n+=1
 print(kind,path,digest,n)
def mark(kind,which):
 d=dict(state['base'],state=which)
 if kind=='RELEASE-PREFLIGHT':d['protected_state_sha256']='a'*64
 else:
  d.update(snapshot=state['snapshot'],snapshot_sha256=state['sha'],ids=state['ids'],state_sha256=state['new_sha'] if which=='applied' else state['old_sha'])
  if kind=='RELEASE-RECOVERY':d['complete']=state['complete']
  if kind=='RELEASE-INCIDENT':
   d.update(complete=True,receipt_sha256=state['receipt_sha'],sql_sha256=state['sql_sha'],cache_delta_retained=True)
   d['state_sha256']=state['projected_old_sha'] if which=='restored' else state['projected_new_sha']
   if scenario=='incident-marker':d['receipt_sha256']='0'*64
 if scenario=='wrong-profile':d['profile_sha256']='b'*64
 if scenario=='wrong-ids' and kind=='RELEASE-SNAPSHOT':d['ids']={'core':999}
 if scenario=='wrong-marker-hash' and kind=='RELEASE-SNAPSHOT':d['snapshot_sha256']='b'*64
 print(kind,json.dumps(d))
 if scenario=='duplicate-marker' and kind=='RELEASE-SNAPSHOT':print(kind,json.dumps(d))
cmd=sys.argv[-1]
if cmd=='true':event('connect');sys.exit(0)
if 'tar -xf -' in cmd:sys.stdin.buffer.read();event('upload');sys.exit(0)
p=shlex.split(cmd)
if p[0]=='cat':event('copy:'+Path(p[1]).suffix);sys.stdout.write(state['files'][p[1]]);sys.exit(0)
if p[0]=='sha256sum':
 event('hash:'+Path(p[1]).suffix);digest=hashlib.sha256(state['files'][p[1]].encode()).hexdigest()
 if scenario=='remote-changed':digest='1'*64
 print(digest+'  '+p[1]);sys.exit(0)
assert p[:3]==['bash','-s','--'];sys.stdin.buffer.read();phase=p[3];event('remote:'+phase);assert p[4]=='live'
if phase.endswith('-live30'):
 assert p[5]==state['snapshot'] and p[6]==state['sha'] and p[7]==state['receipt_sha']
 if phase=='inspect-live30':
  if scenario=='state-fail':print('RELEASE-STATE-DIAGNOSTIC {"fixture":"incident-drift"}');sys.exit(10)
  mark('RELEASE-INCIDENT','applied');mark('RELEASE-INCIDENT','recoverable')
 elif phase.startswith('prepare-'):
  if scenario=='incident-precheck':print('RELEASE-STATE-DIAGNOSTIC {"fixture":"incident-drift"}');sys.exit(12)
  if phase=='prepare-finish-live30':mark('RELEASE-INCIDENT','applied');print('RELEASE-FINAL-BEGIN')
  mark('RELEASE-INCIDENT','recoverable');transfer('RELEASE-JOURNAL',state['snapshot'])
  if scenario=='export-fail':sys.exit(7)
  transfer('RELEASE-BACKUP',state['backup'])
  if scenario=='duplicate-backup':transfer('RELEASE-BACKUP',state['backup'])
 else:
  if scenario in ('purge-fail','render-fail','state-fail'):
   print('RELEASE-STATE-DIAGNOSTIC {"fixture":"incident-final-failure"}');sys.exit(10)
  print('RELEASE-FINAL-BEGIN')
  if scenario=='duplicate-final':print('RELEASE-FINAL-BEGIN')
  mark('RELEASE-INCIDENT','restored' if phase=='rollback-live30' else 'applied')
elif phase in ('prepare','prepare-rollback'):
 if scenario=='export-fail':sys.exit(7)
 if phase=='prepare':
  mark('RELEASE-PREFLIGHT','ready');print('RELEASE-INTENT',state['snapshot'])
  if scenario=='duplicate-intent':print('RELEASE-INTENT',state['snapshot'])
 else:mark('RELEASE-RECOVERY','recoverable');transfer('RELEASE-JOURNAL',state['snapshot'])
 transfer('RELEASE-BACKUP',state['backup'])
 if scenario=='duplicate-backup':transfer('RELEASE-BACKUP',state['backup'])
elif phase=='apply':
 assert p[5]==state['snapshot']
 if scenario=='apply-fail':sys.exit(8)
 if scenario!='missing-marker':mark('RELEASE-SNAPSHOT','applied')
elif phase=='preserve':
 assert p[5]==state['snapshot']
 if scenario=='missing-journal':sys.exit(9)
 transfer('RELEASE-JOURNAL',state['snapshot'])
 if scenario=='duplicate-journal':transfer('RELEASE-JOURNAL',state['snapshot'])
elif phase=='inspect-state':
 assert p[5]==state['snapshot'] and p[6]==state['sha']
 if scenario=='state-fail':
  print('RELEASE-STATE-DIAGNOSTIC {"fixture":"changed-row-hash"}');sys.exit(10)
 mark('RELEASE-SNAPSHOT','applied')
elif phase in ('verify','verify-purged','rollback'):
 assert p[5]==state['snapshot'] and p[6]==state['sha'];event('bound:'+p[6])
 if scenario in ('purge-fail','render-fail','state-fail'):
  print('RELEASE-STATE-DIAGNOSTIC {"fixture":"legacy-final-failure"}');sys.exit(10)
 print('RELEASE-FINAL-BEGIN')
 if scenario=='duplicate-final':print('RELEASE-FINAL-BEGIN')
 mark('RELEASE-RECOVERY' if phase=='rollback' else 'RELEASE-SNAPSHOT','original' if phase=='rollback' else 'applied')
elif phase=='preflight':
 if scenario=='preflight-fail':
  print('RELEASE-SPECTRA-SOURCE {"files":{"fixture":"missing-or-noncanonical"}}');sys.exit(11)
 mark('RELEASE-PREFLIGHT','ready')
else:raise SystemExit('unknown fixture phase')
'''
HOOK = r'''#!/usr/bin/env python3
import os,sys
from pathlib import Path
assert len(sys.argv)==3 and Path(sys.argv[1],sys.argv[2]).is_file()
suffix=Path(sys.argv[2]).suffix
if sys.argv[2]=='peek-release-live30-receipt.json':suffix='receipt'
with open(os.environ['RELEASE_EVENTS'],'a') as f:f.write('offsite:'+suffix+'\n')
if os.environ['RELEASE_CASE']==('offsite-db-fail' if suffix=='.sql' else ('offsite-receipt-fail' if suffix=='receipt' else 'offsite-journal-fail')):sys.exit(5)
'''
checks=0
def check(ok,label):
 global checks
 checks+=1
 if not ok:raise AssertionError(label)
with tempfile.TemporaryDirectory(prefix='release-push-model-') as td:
 root=Path(td);binary=root/'bin';binary.mkdir()
 for name,body in [('ssh',SSH),('ssh-add','#!/bin/sh\nexit 0\n'),('offsite',HOOK)]:
  (binary/name).write_text(body);(binary/name).chmod(0o700)
 creds=root/'credentials';creds.write_text('Host: fixture.invalid\nPort: 22\nUser: fixture\n')
 sock=socket.socket(socket.AF_UNIX);sock.bind(str(root/'agent.sock'))
 env=dict(os.environ,PATH=str(binary)+os.pathsep+os.environ['PATH'],S26_CREDS=str(creds),SSH_AUTH_SOCK=str(root/'agent.sock'),TS_OFFSITE_HOOK=str(binary/'offsite'),S_TARGET='live',RELEASE_FIXTURE=str(root/'state.json'),RELEASE_EVENTS=str(root/'events'))
 base={'schema':1,'bundle':'peek-visitor-release-v1','home':'http://thaisolitude.com','manifest_sha256':sha((HERE/'manifest.json').read_bytes()),'profile_sha256':sha((HERE/'live-profile.json').read_bytes())}
 ids={role:2000+n for n,role in enumerate(('core','thai-massage','therapeutic-massage','deep-tissue-massage','chair-massage'))}
 old={'private':'SYNTHETIC_PRIVATE_DO_NOT_LOG'};new={'private':'SYNTHETIC_PRIVATE_APPLIED'}
 def run(case,args,partial=False,**overrides):
  copydir=root/f'backups-{checks}'
  (root/'events').write_text('')
  incident=any(a in ('--finish-live30','--incident-live30','--inspect-live30') for a in args)
  old_state=old;new_state=new
  if incident:
   old_state=dict(witnesses={'postmeta':{'4701':'c'*64,'4702':'d'*64,'777':'e'*64}},owned={role:None for role in ids},records={'29':{'post':{'post_content':'SYNTHETIC_PRIVATE_CSS'}}},options={'astra-settings':{'option_value':'SYNTHETIC_PRIVATE_ASTRA'}},taxonomies=[{'term_taxonomy_id':'9','count':'0'},{'term_taxonomy_id':'10','count':'0'}])
   new_state=copy.deepcopy(old_state);new_state['owned']={role:{'post':{'ID':str(identity)}} for role,identity in ids.items()}
   new_state['records']['29']['post']['post_content']='SYNTHETIC_PRIVATE_NEW_CSS';new_state['options']['astra-settings']['option_value']='SYNTHETIC_PRIVATE_NEW_ASTRA'
   for row in new_state['taxonomies']:row['count']='5'
  snap=dict(base,complete=not partial,ids=[] if partial == 'empty' else ({'thai-massage':2001} if partial else ids),old=old_state,old_sha256=sha(canonical(old_state)))
  snap.update({('journal' if partial else 'new'):new_state,('journal_sha256' if partial else 'new_sha256'):sha(canonical(new_state))})
  raw=canonical(snap).decode()
  snapshot=LIVE30_SNAP if incident else SNAP
  state=dict(base=base,complete=not partial,ids=snap['ids'],old_sha=snap['old_sha256'],new_sha=sha(canonical(new_state)),snapshot=snapshot,backup=BACKUP,sha=sha(raw.encode()),files={snapshot:raw,BACKUP:'SYNTHETIC_PRIVATE_SQL\n'})
  push=PUSH
  if incident:
   # Explicit synthetic source/package copy: no production env or CLI pin bypass.
   package=root/f'package-{checks}'/'peek-release';package.mkdir(parents=True)
   for name in ('manifest.json','live-profile.json','validate-marker.py','incident-receipt.php'):(package/name).write_bytes((HERE/name).read_bytes())
   for name in ('install-peek-release.php','source-binding.php','verify-render.php'):(package/name).write_text('synthetic command-double payload\n')
   (package/'payload').mkdir()
   removed={identity:dict(post_id=post,meta_key='_uag_page_assets',row_sha256=digest,value_sha256='a'*64,value_bytes=3) for identity,post,digest in [('4701',1255,'c'*64),('4702',1257,'d'*64)]}
   added={identity:dict(post_id=post,meta_key='_uag_page_assets',row_sha256=sha(identity.encode()),value_sha256='b'*64,value_bytes=4) for identity,post in [('5089',1254),('5090',1255),('5091',1257)]}
   def projected(value):
    value=copy.deepcopy(value)
    for identity in removed:del value['witnesses']['postmeta'][identity]
    for identity,row in added.items():value['witnesses']['postmeta'][identity]=row['row_sha256']
    return sha(canonical(value))
   receipt=dict(base,incident='live30-assets-20261111',snapshot={'file':Path(snapshot).name,'sha256':state['sha'],'bytes':len(raw.encode())},sql={'file':Path(BACKUP).name,'sha256':sha(state['files'][BACKUP].encode()),'bytes':len(state['files'][BACKUP].encode())},ids=ids,original_applied_sha256=state['new_sha'],projected_applied_sha256=projected(new_state),removed=removed,added=added,proof_sha256='f'*64)
   receipt_raw=canonical(receipt);state.update(receipt_sha=sha(receipt_raw),sql_sha=receipt['sql']['sha256'],projected_old_sha=projected(old_state),projected_new_sha=receipt['projected_applied_sha256'])
   (package/'incident-live30.json').write_bytes(receipt_raw+(b' ' if case=='receipt-changed' else b''))
   (package/'incident-live30-binding.json').write_bytes(canonical(dict(schema=1,incident='live30-assets-20261111',receipt_sha256=None if case=='incident-disabled' else state['receipt_sha'])))
   push=package.parent/'push-peek-release.sh';source=PUSH.read_text()
   check(source.count(LIVE30_SHA)==2,'exact two immutable snapshot pins in production wrapper')
   push.write_text(source.replace(LIVE30_SHA,state['sha']))
  (root/'state.json').write_text(json.dumps(state))
  if case in ('existing-different','existing-symlink'):
   copydir.mkdir();dest=copydir/Path(BACKUP).name
   if case=='existing-symlink':dest.symlink_to(root/'state.json')
   else:dest.write_text('earlier evidence')
  result=subprocess.run(['bash',str(push),*args],env=dict(env,RELEASE_CASE=case,TS_SERVER_BACKUPS=str(copydir),**overrides),capture_output=True,text=True,timeout=15)
  check('SYNTHETIC_PRIVATE' not in result.stdout+result.stderr,'private raw values leaked')
  return result,(root/'events').read_text().splitlines(),copydir
 r,seen,d=run('ok',['--apply'])
 check(r.returncode==0,'apply fixture: '+r.stdout+r.stderr)
 check(seen.index('offsite:.sql')<seen.index('remote:apply')<seen.index('remote:preserve')<seen.index('offsite:.json')<seen.index('remote:verify-purged'),'exact backup-before-write and journal-before-render order')
 check(any(x.startswith('bound:') for x in seen),'final verify binds actual preserved snapshot hash')
 check((d/Path(SNAP).name).stat().st_mode&0o777==0o600,'actual journal private permissions')
 for case in ('export-fail','wrong-profile','backup-hash','remote-changed','offsite-db-fail','duplicate-backup','duplicate-intent','existing-different','existing-symlink'):
  r,seen,_=run(case,['--apply']);check(r.returncode!=0 and 'remote:apply' not in seen,case+' must stop before writes')
 r,seen,d=run('apply-fail',['--apply'],partial=True)
 check(r.returncode!=0 and 'offsite:.json' in seen and (d/Path(SNAP).name).is_file(),'failed apply preserves actual partial recovery journal offsite')
 check('remote:verify-purged' not in seen,'failed apply never claims rendered success')
 for case in ('missing-marker','wrong-ids','wrong-marker-hash','duplicate-marker','missing-journal','journal-size','duplicate-journal','offsite-journal-fail'):
  r,seen,_=run(case,['--apply']);check(r.returncode!=0 and 'remote:verify-purged' not in seen,case+' must stop before success/render')
 for case in ('purge-fail','render-fail','state-fail','duplicate-final'):
  r,seen,_=run(case,['--apply']);check(r.returncode!=0,case+' cannot produce success')
  if case!='duplicate-final':check('RELEASE-STATE-DIAGNOSTIC ' in r.stdout,'legacy failed verification stdout retained')
 for partial in (False,True,'empty'):
  r,seen,_=run('ok',['--rollback',SNAP],partial=partial)
  check(r.returncode==0,'rollback fixture: '+r.stdout+r.stderr)
  check('remote:preflight' not in seen and 'remote:verify' not in seen,'rollback never requires snapshot-free preflight/completed verify')
  check(seen.index('offsite:.sql')<seen.index('remote:rollback') and seen.index('offsite:.json')<seen.index('remote:rollback'),'backup and actual complete/partial journal offsite precede rollback')
 for case in ('export-fail','offsite-db-fail','offsite-journal-fail'):
  r,seen,_=run(case,['--rollback',SNAP],partial=True);check(r.returncode!=0 and 'remote:rollback' not in seen,'rollback '+case+' stops writes')
 r,seen,_=run('ok',[]);check(r.returncode==0 and not any(x.startswith('offsite:') for x in seen),'dry checks raw profile only')
 inspect_raw=canonical(dict(base,complete=True,ids=ids,old=old,old_sha256=sha(canonical(old)),new=new,new_sha256=sha(canonical(new))))
 for scenario in ('ok','state-fail'):
  r,seen,d=run(scenario,['--inspect-state',SNAP,sha(inspect_raw)],TS_OFFSITE_HOOK='')
  check(r.returncode==(0 if scenario=='ok' else 10),'state inspection preserves exact success/failure exit')
  check(seen==['connect','upload','remote:inspect-state'] and not d.exists(),'state inspection performs no backup, purge, rendered request or journal transfer')
  check(('RELEASE-SNAPSHOT ' if scenario=='ok' else 'RELEASE-STATE-DIAGNOSTIC ') in r.stdout,'state inspection streams its exact diagnostic')
 r,seen,_=run('ok',['--inspect-state',SNAP,'invalid'],TS_OFFSITE_HOOK='')
 check(r.returncode!=0 and not seen,'state inspection refuses malformed expected SHA before contact')
 r,seen,_=run('preflight-fail',[])
 check(r.returncode==11 and 'RELEASE-SPECTRA-SOURCE ' in r.stdout,'failed preflight retains diagnostics and exact failure exit')
 check('Dry run only' not in r.stdout and seen==['connect','upload','remote:preflight'],'failed preflight never continues or claims success')
 r,seen,_=run('ok',['--apply'],S_TARGET='staging39');check(r.returncode!=0 and not seen,'non-live target rejected before contact')
 r,seen,_=run('ok',['--apply'],TS_OFFSITE_HOOK='');check(r.returncode!=0 and not seen,'missing offsite hook stops before contact')
 for args,phase in [(['--apply','--finish-live30'],'finish-live30'),(['--rollback',LIVE30_SNAP,'--incident-live30'],'rollback-live30')]:
  r,seen,d=run('ok',args)
  check(r.returncode==0,phase+' succeeds with independently validated fixture receipt: '+r.stdout+r.stderr)
  check(all(seen.index(event)<seen.index('remote:'+phase) for event in ('offsite:.sql','offsite:.json','offsite:receipt')),phase+' preserves SQL, actual journal and receipt before mutation')
  check(not any(event in seen for event in ('remote:apply','remote:preflight','remote:prepare','remote:rollback')),phase+' cannot enter ordinary install/recovery path')
  check((d/'peek-release-live30-receipt.json').stat().st_mode&0o777==0o600,phase+' receipt preserved privately')
  for case in ('incident-disabled','receipt-changed','incident-precheck','export-fail','backup-hash','journal-size','offsite-db-fail','offsite-journal-fail','offsite-receipt-fail','duplicate-backup','incident-marker'):
   r,seen,_=run(case,args)
   check(r.returncode!=0 and 'remote:'+phase not in seen,phase+' '+case+' stops before purge/rollback')
   if case in ('incident-disabled','receipt-changed'):check(not seen,'invalid incident receipt refused before SSH')
   if case=='incident-precheck':check('RELEASE-STATE-DIAGNOSTIC ' in r.stdout and r.returncode==12,'incident precheck diagnostic and exit retained')
  for case in ('purge-fail','render-fail','state-fail','duplicate-final'):
   r,seen,_=run(case,args);check(r.returncode!=0,phase+' final '+case+' cannot claim success')
   if case!='duplicate-final':check('RELEASE-STATE-DIAGNOSTIC ' in r.stdout and r.returncode==10,'incident final diagnostic and exit retained')
  r,seen,_=run('ok',args,TS_OFFSITE_HOOK='');check(r.returncode!=0 and not seen,'incident write requires offsite hook before contact')
 r,seen,d=run('ok',['--inspect-live30'],TS_OFFSITE_HOOK='')
 check(r.returncode==0 and seen==['connect','upload','remote:inspect-live30'] and not d.exists(),'incident inspection is state-only without journal copies, backup, purge or render')
 r,seen,_=run('state-fail',['--inspect-live30'],TS_OFFSITE_HOOK='')
 check(r.returncode==10 and 'RELEASE-STATE-DIAGNOSTIC ' in r.stdout,'incident inspection retains failure output')
 r,seen,_=run('ok',['--rollback',SNAP,'--incident-live30'])
 check(r.returncode!=0 and not seen,'incident rollback rejects any other snapshot before contact')
 for args in (['--finish-live30'],['--incident-live30'],['--apply','--finish-live30','extra'],['--inspect-live30','extra']):
  r,seen,_=run('ok',args);check(r.returncode!=0 and not seen,'incident mode rejects malformed/unclassified argument combination')
 sock.close()
# Direct parser controls cover corruption independently of shell doubles.
spec=importlib.util.spec_from_file_location('release_marker',HERE/'validate-marker.py')
parser=importlib.util.module_from_spec(spec);spec.loader.exec_module(parser)
with tempfile.TemporaryDirectory(prefix='release-marker-model-') as td:
 saved=Path(td)/'private.json';log=Path(td)/'marker.log'
 journal=dict(base,complete=True,ids=ids,old=old,old_sha256=sha(canonical(old)),new=new,new_sha256=sha(canonical(new)))
 raw=canonical(journal);saved.write_bytes(raw);saved.chmod(0o600)
 good=dict(base,state='applied',snapshot=SNAP,snapshot_sha256=sha(raw),state_sha256=sha(canonical(new)),ids=ids)
 def binding(data,ok=True,body=None):
  log.write_text(body if body is not None else 'RELEASE-SNAPSHOT '+json.dumps(data)+'\n')
  try:parser.validate(HERE,log,'RELEASE-SNAPSHOT','applied',SNAP,saved);result=True
  except Exception:result=False
  check(result==ok,'marker corruption boundary')
 binding(good)
 for key,value in [('schema',True),('home','https://thaisolitude.com'),('state','original'),('snapshot',SNAP.replace('1234abcd','ffffffff')),('snapshot_sha256','0'*64),('manifest_sha256','0'*64),('profile_sha256','0'*64),('state_sha256','0'*64),('ids',{'core':2000}),('ids',dict(ids,core=True))]:
  binding(dict(good,**{key:value}),False)
 binding(good,False,'RELEASE-SNAPSHOT '+json.dumps(good)+'\nRELEASE-SNAPSHOT malformed\n')
 duplicate=json.dumps(good)[:-1]+',"home":"http://thaisolitude.com"}'
 binding(good,False,'RELEASE-SNAPSHOT '+duplicate+'\n')
 saved.chmod(0o644);binding(good,False);saved.chmod(0o600)
 saved.write_bytes(raw+b' ');binding(good,False);saved.write_bytes(raw)
 saved.unlink();saved.symlink_to(HERE/'manifest.json');binding(good,False)

# Execute the actual remote case body with simple command doubles: this proves
# shell failure semantics, not only failure propagation from mocked SSH.
source=PUSH.read_text(); remote=source.split("<<'EOS'",1)[1].split('\nEOS',1)[0]
# Exercise the actual discovery correction against a real ancestor alias. The
# strict plugin source binding remains unchanged; only WP-CLI's root is resolved.
canonical_root=next(line for line in remote.splitlines() if line.startswith('doc=$(cd --'))
with tempfile.TemporaryDirectory(prefix='release-root-alias-') as td:
 root=Path(td);physical=root/'physical';physical.mkdir();alias=root/'logical';alias.symlink_to(physical,target_is_directory=True)
 for path in (physical,alias):
  r=subprocess.run(['bash','-c','set -euo pipefail; doc=$1; '+canonical_root+'; printf "%s" "$doc"','fixture',str(path)],text=True,capture_output=True)
  check(r.returncode==0 and r.stdout==str(physical.resolve()),'canonical root resolves real and ancestor-alias paths identically')
 r=subprocess.run(['bash','-c','set -euo pipefail; doc=$1; '+canonical_root+'; echo continued','fixture',str(root/'missing')],text=True,capture_output=True)
 check(r.returncode!=0 and 'continued' not in r.stdout,'missing selected root stops before WordPress bootstrap')
body=remote[remote.index('case "$phase" in'):]
# Execute the real pre-bootstrap guard against temporary regular files. Only a
# copied test string substitutes the fixed production path/SHA; no runtime knob.
incident_guard=remote[remote.index('phase=$1;'):remote.index('command -v wp')]
with tempfile.TemporaryDirectory(prefix='release-incident-guard-') as td:
 root=Path(td);package=root/'wwm-package-peek-release';package.mkdir()
 snapshot=root/Path(LIVE30_SNAP).name;snapshot.write_bytes(b'SYNTHETIC_PRIVATE_JOURNAL');snapshot.chmod(0o600)
 receipt=package/'incident-live30.json';receipt.write_bytes(b'SYNTHETIC_RECEIPT')
 snapshot_sha=sha(snapshot.read_bytes());receipt_sha=sha(receipt.read_bytes())
 guard=incident_guard.replace(LIVE30_SNAP,str(snapshot)).replace(LIVE30_SHA,snapshot_sha)
 for scenario in ('ok','snapshot-changed','receipt-changed','snapshot-permissions','snapshot-symlink'):
  snapshot.unlink();snapshot.write_bytes(b'SYNTHETIC_PRIVATE_JOURNAL');snapshot.chmod(0o600);receipt.write_bytes(b'SYNTHETIC_RECEIPT')
  if scenario=='snapshot-changed':snapshot.write_bytes(b'drift')
  if scenario=='receipt-changed':receipt.write_bytes(b'drift')
  if scenario=='snapshot-permissions':snapshot.chmod(0o644)
  if scenario=='snapshot-symlink':
   alternate=root/'alternate';alternate.write_bytes(b'SYNTHETIC_PRIVATE_JOURNAL');alternate.chmod(0o600);snapshot.unlink();snapshot.symlink_to(alternate)
  r=subprocess.run(['bash','-s','--','finish-live30','live',str(snapshot),snapshot_sha,receipt_sha],input='set -euo pipefail\n'+guard+'echo BOOTSTRAP-BOUNDARY\n',text=True,capture_output=True,env=dict(os.environ,HOME=str(root)))
  check((r.returncode==0 and 'BOOTSTRAP-BOUNDARY' in r.stdout)==(scenario=='ok'),'real incident guard '+scenario+' before WordPress bootstrap')
  check('SYNTHETIC' not in r.stdout+r.stderr,'real file guard leaks no private bytes')
remote_faults=[('verify-purged','purge'),('verify-purged','render'),('verify-purged','verify-final'),('rollback','purge'),('rollback','render'),('rollback','verify-final')]
remote_faults += [(phase,fail) for phase in ('finish-live30','rollback-live30') for fail in ('verify-start','recovery','purge','render','verify-final')]
for phase,fail in remote_faults:
 with tempfile.TemporaryDirectory() as td:
  events=Path(td)/'events'
  prefix='''set -euo pipefail
phase=$1; fail=$2; snap=/private/fixture.json; count=0
run() { echo "run:$*" >> "$EVENTS"; count=$((count+1)); if [[ "$fail" = verify-start && "$count" -eq 1 || "$fail" = recovery && "$count" -eq 2 || "$fail" = verify-final && ( "$count" -eq 2 && "$phase" = verify-purged || "$count" -eq 3 && "$phase" = finish-live30 || "$count" -eq 4 && ( "$phase" = rollback || "$phase" = rollback-live30 ) ) ]]; then return 7; fi; }
purge() { echo purge >> "$EVENTS"; [ "$fail" != purge ]; }
render() { echo render >> "$EVENTS"; [ "$fail" != render ]; }
'''
  r=subprocess.run(['bash','-s','--',phase,fail],input=prefix+body,text=True,capture_output=True,env=dict(os.environ,EVENTS=str(events)))
  check(r.returncode!=0,phase+' actual remote case refuses '+fail)
  if fail=='purge':check('render' not in events.read_text().splitlines(),'purge failure prevents rendered claim')
  if fail in ('verify-start','recovery'):check('purge' not in events.read_text().splitlines(),'incident state refusal prevents purge')
for phase,expected in (
 ('finish-live30',['run:verify-incident /private/fixture.json','run:recovery-check-incident /private/fixture.json','purge','render:applied','run:verify-incident /private/fixture.json']),
 ('rollback-live30',['run:recovery-check-incident /private/fixture.json','run:rollback-incident /private/fixture.json','run:recovery-check-incident /private/fixture.json restored','purge','render:original','run:recovery-check-incident /private/fixture.json restored'])):
 with tempfile.TemporaryDirectory() as td:
  events=Path(td)/'events'
  prefix='set -euo pipefail\nphase=$1; snap=/private/fixture.json\nrun() { echo "run:$*" >> "$EVENTS"; }\npurge() { echo purge >> "$EVENTS"; }\nrender() { echo "render:$*" >> "$EVENTS"; }\n'
  r=subprocess.run(['bash','-s','--',phase],input=prefix+body,text=True,capture_output=True,env=dict(os.environ,EVENTS=str(events)))
  check(r.returncode==0 and events.read_text().splitlines()==expected,'actual '+phase+' calls only the intended incident actions in order')
print(f'PASS release wrapper: {checks} offline controls; no live/encrypted-store proof.')
