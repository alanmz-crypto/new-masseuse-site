"""Offline wrapper command doubles; no SSH, HTTP, WP or encrypted-store proof."""
import hashlib
import importlib.util
import json
import os
from pathlib import Path
import socket
import subprocess
import tempfile

HERE = Path(__file__).resolve().parent
PUSH = HERE.parent/'push-peek-release.sh'
BACKUP = '/home/releasefixture/private-backups/live-before-peek-release-20261111-120000-123.sql'
SNAP = '/home/releasefixture/private-backups/peek-release-before-20261111-120001-1234abcd.json'
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
if phase in ('prepare','prepare-rollback'):
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
elif phase in ('verify','verify-purged','rollback'):
 assert p[5]==state['snapshot'] and p[6]==state['sha'];event('bound:'+p[6])
 if scenario in ('purge-fail','render-fail','state-fail'):sys.exit(10)
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
with open(os.environ['RELEASE_EVENTS'],'a') as f:f.write('offsite:'+suffix+'\n')
if os.environ['RELEASE_CASE']==('offsite-db-fail' if suffix=='.sql' else 'offsite-journal-fail'):sys.exit(5)
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
  snap=dict(base,complete=not partial,ids=[] if partial == 'empty' else ({'thai-massage':2001} if partial else ids),old=old,old_sha256=sha(canonical(old)))
  snap.update({('journal' if partial else 'new'):new,('journal_sha256' if partial else 'new_sha256'):sha(canonical(new))})
  raw=canonical(snap).decode()
  state=dict(base=base,complete=not partial,ids=snap['ids'],old_sha=snap['old_sha256'],new_sha=sha(canonical(new)),snapshot=SNAP,backup=BACKUP,sha=sha(raw.encode()),files={SNAP:raw,BACKUP:'SYNTHETIC_PRIVATE_SQL\n'})
  (root/'state.json').write_text(json.dumps(state))
  if case in ('existing-different','existing-symlink'):
   copydir.mkdir();dest=copydir/Path(BACKUP).name
   if case=='existing-symlink':dest.symlink_to(root/'state.json')
   else:dest.write_text('earlier evidence')
  result=subprocess.run(['bash',str(PUSH),*args],env=dict(env,RELEASE_CASE=case,TS_SERVER_BACKUPS=str(copydir),**overrides),capture_output=True,text=True,timeout=15)
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
 for partial in (False,True,'empty'):
  r,seen,_=run('ok',['--rollback',SNAP],partial=partial)
  check(r.returncode==0,'rollback fixture: '+r.stdout+r.stderr)
  check('remote:preflight' not in seen and 'remote:verify' not in seen,'rollback never requires snapshot-free preflight/completed verify')
  check(seen.index('offsite:.sql')<seen.index('remote:rollback') and seen.index('offsite:.json')<seen.index('remote:rollback'),'backup and actual complete/partial journal offsite precede rollback')
 for case in ('export-fail','offsite-db-fail','offsite-journal-fail'):
  r,seen,_=run(case,['--rollback',SNAP],partial=True);check(r.returncode!=0 and 'remote:rollback' not in seen,'rollback '+case+' stops writes')
 r,seen,_=run('ok',[]);check(r.returncode==0 and not any(x.startswith('offsite:') for x in seen),'dry checks raw profile only')
 r,seen,_=run('preflight-fail',[])
 check(r.returncode==11 and 'RELEASE-SPECTRA-SOURCE ' in r.stdout,'failed preflight retains diagnostics and exact failure exit')
 check('Dry run only' not in r.stdout and seen==['connect','upload','remote:preflight'],'failed preflight never continues or claims success')
 r,seen,_=run('ok',['--apply'],S_TARGET='staging39');check(r.returncode!=0 and not seen,'non-live target rejected before contact')
 r,seen,_=run('ok',['--apply'],TS_OFFSITE_HOOK='');check(r.returncode!=0 and not seen,'missing offsite hook stops before contact')
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
for phase,fail in [('verify-purged','purge'),('verify-purged','render'),('verify-purged','verify-final'),('rollback','purge'),('rollback','render'),('rollback','verify-final')]:
 with tempfile.TemporaryDirectory() as td:
  events=Path(td)/'events'
  prefix='''set -euo pipefail
phase=$1; fail=$2; snap=/private/fixture.json; count=0
run() { echo "run:$*" >> "$EVENTS"; count=$((count+1)); if [[ "$fail" = verify-final && ( "$count" -eq 2 && "$phase" = verify-purged || "$count" -eq 4 && "$phase" = rollback ) ]]; then return 7; fi; }
purge() { echo purge >> "$EVENTS"; [ "$fail" != purge ]; }
render() { echo render >> "$EVENTS"; [ "$fail" != render ]; }
'''
  r=subprocess.run(['bash','-s','--',phase,fail],input=prefix+body,text=True,capture_output=True,env=dict(os.environ,EVENTS=str(events)))
  check(r.returncode!=0,phase+' actual remote case refuses '+fail)
  if fail=='purge':check('render' not in events.read_text().splitlines(),'purge failure prevents rendered claim')
print(f'PASS release wrapper: {checks} offline controls; no live/encrypted-store proof.')
