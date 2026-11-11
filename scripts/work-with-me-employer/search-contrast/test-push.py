"""Exercise the real push shell with local SSH/off-site command doubles.

This checks control flow and fail-closed backup/snapshot binding. It makes no
network requests and does not stand in for WordPress or encrypted-store proof.
"""
from __future__ import annotations

import json
import os
from pathlib import Path
import socket
import subprocess
import tempfile

HERE = Path(__file__).resolve().parent
PUSH = HERE / 'push-search-contrast.sh'
BACKUP = '/home/searchfixture/private-backups/staging39-before-search-contrast-20261111-120000-123.sql'
SNAP = '/home/searchfixture/private-backups/search-contrast-before-20261111-120001-1234abcd.json'
PAYLOADS = {BACKUP: 'fixture SQL export\n', SNAP: '{"fixture":"private snapshot"}\n'}

SSH = r'''#!/usr/bin/env python3
import hashlib,json,os,shlex,sys
from pathlib import Path
state=json.loads(Path(os.environ['SEARCH_FIXTURE']).read_text())
scenario=os.environ['SEARCH_CASE']
def event(name):
 with open(os.environ['SEARCH_EVENTS'],'a') as f: f.write(name+'\n')
def marker(kind,path):
 data=state['files'][path].encode(); sha=hashlib.sha256(data).hexdigest(); n=len(data)
 if scenario=='wrong-checksum' and kind=='SEARCH-BACKUP': sha='0'*64
 if scenario=='wrong-size' and kind=='SEARCH-SNAPSHOT': n+=1
 print(kind,path,sha,n)
cmd=sys.argv[-1]
if cmd=='true': event('connect'); sys.exit(0)
if 'tar -xf -' in cmd: sys.stdin.buffer.read(); event('upload'); sys.exit(0)
parts=shlex.split(cmd)
if parts[0]=='cat':
 event('copy:'+Path(parts[1]).suffix); sys.stdout.write(state['files'][parts[1]]); sys.exit(0)
if parts[0]=='sha256sum':
 event('hash:'+Path(parts[1]).suffix)
 sha=hashlib.sha256(state['files'][parts[1]].encode()).hexdigest()
 if scenario=='remote-changed': sha='1'*64
 print(sha+'  '+parts[1]); sys.exit(0)
if parts[:3]!=['bash','-s','--']: raise SystemExit('unexpected SSH fixture command '+cmd)
sys.stdin.buffer.read(); phase=parts[3]; event('remote:'+phase)
if parts[4]!='staging39': raise SystemExit('unexpected target')
if phase in ('prepare','prepare-rollback'):
 if scenario=='prepare-fail': sys.exit(7)
 if phase=='prepare-rollback': marker('SEARCH-SNAPSHOT',state['snapshot'])
 marker('SEARCH-BACKUP',state['backup'])
 if scenario=='duplicate-backup': marker('SEARCH-BACKUP',state['backup'])
 if scenario=='malformed-extra-backup': print('SEARCH-BACKUP malformed')
elif phase=='apply':
 if scenario!='missing-snapshot': marker('SEARCH-SNAPSHOT',state['snapshot'])
 if scenario=='duplicate-snapshot': marker('SEARCH-SNAPSHOT',state['snapshot'])
 if scenario=='malformed-extra-snapshot': print('SEARCH-SNAPSHOT malformed')
 if scenario=='apply-fail': sys.exit(8)
 if scenario!='missing-completion':
  path=state['snapshot'] if scenario!='wrong-completion' else state['snapshot'].replace('1234abcd','ffffffff')
  print('Success: Applied exact search-contrast CSS. Before-snapshot: '+path)
elif phase in ('verify','verify-purged','rollback','preflight'):
 if phase!='preflight' and parts[5]!=state['snapshot']: raise SystemExit('snapshot path differs')
 if scenario=='verify-fail' and phase=='verify-purged': sys.exit(9)
else: raise SystemExit('unexpected phase '+phase)
'''
HOOK = r'''#!/usr/bin/env python3
import os,sys
from pathlib import Path
name=sys.argv[2]
assert len(sys.argv)==3 and Path(sys.argv[1],name).is_file()
suffix=Path(name).suffix
with open(os.environ['SEARCH_EVENTS'],'a') as f:f.write('offsite:'+suffix+'\n')
if os.environ['SEARCH_CASE']==('offsite-db-fail' if suffix=='.sql' else 'offsite-snapshot-fail'):sys.exit(5)
'''
checks = 0


def check(condition: bool, message: str) -> None:
    global checks
    checks += 1
    if not condition:
        raise AssertionError(message)


with tempfile.TemporaryDirectory(prefix='search-push-test-') as td:
    root = Path(td)
    binary = root / 'bin'
    binary.mkdir()
    (binary / 'ssh').write_text(SSH)
    (binary / 'ssh-add').write_text('#!/bin/sh\nexit 0\n')
    hook = root / 'offsite'
    hook.write_text(HOOK)
    for path in (binary / 'ssh', binary / 'ssh-add', hook):
        path.chmod(0o700)
    creds = root / 'fixture-creds'
    creds.write_text('Host: fixture.invalid\nPort: 22\nUser: fixture\n')
    fixture = root / 'state.json'
    fixture.write_text(json.dumps({'backup': BACKUP, 'snapshot': SNAP, 'files': PAYLOADS}))
    events = root / 'events'
    agent_socket = socket.socket(socket.AF_UNIX)
    agent_socket.bind(str(root / 'agent.sock'))
    environment = dict(os.environ, PATH=str(binary) + os.pathsep + os.environ['PATH'],
                       S26_CREDS=str(creds), SSH_AUTH_SOCK=str(root / 'agent.sock'),
                       TS_OFFSITE_HOOK=str(hook), TS_SERVER_BACKUPS=str(root / 'backups'),
                       SEARCH_FIXTURE=str(fixture), SEARCH_EVENTS=str(events), S_TARGET='staging39')

    def run(case: str, args: list[str], **overrides: str) -> tuple[subprocess.CompletedProcess[str], list[str]]:
        events.write_text('')
        copies = root / ('backups-' + str(checks))
        if case in ('existing-different', 'existing-same', 'existing-symlink'):
            copies.mkdir()
            saved = copies / Path(BACKUP).name
            if case == 'existing-symlink':
                saved.symlink_to(fixture)
            else:
                saved.write_text('preserved earlier backup' if case == 'existing-different' else PAYLOADS[BACKUP])
        env = dict(environment, SEARCH_CASE=case, **overrides)
        env['TS_SERVER_BACKUPS'] = str(copies)
        result = subprocess.run(['bash', str(PUSH), *args], env=env, text=True,
                                capture_output=True, timeout=20, check=False)
        return result, events.read_text().splitlines()

    result, seen = run('ok', ['--apply'])
    check(result.returncode == 0, 'apply fixture failed: ' + result.stdout + result.stderr)
    required = ['remote:prepare', 'copy:.sql', 'hash:.sql', 'offsite:.sql', 'remote:apply',
                'copy:.json', 'hash:.json', 'offsite:.json', 'remote:verify-purged']
    check([item for item in seen if item not in ('connect', 'upload')] == required,
          'apply must bind fresh export/offsite before write and snapshot/offsite before render')
    for path, body in PAYLOADS.items():
        copied = root / 'backups-0' / Path(path).name
        check(copied.read_text() == body and copied.stat().st_mode & 0o077 == 0,
              'exact private copy differs')

    result, seen = run('ok', ['--rollback', SNAP])
    check(result.returncode == 0, 'rollback fixture failed: ' + result.stdout + result.stderr)
    check([item for item in seen if item not in ('connect', 'upload')] ==
          ['remote:prepare-rollback', 'copy:.sql', 'hash:.sql', 'offsite:.sql',
           'copy:.json', 'hash:.json', 'offsite:.json', 'remote:rollback'],
          'rollback must verify exact fresh backup and actual recovery snapshot offsite before mutation')
    for args, phase in (([], 'preflight'), (['--verify', SNAP], 'verify')):
        result, seen = run('ok', args, TS_OFFSITE_HOOK='')
        check(result.returncode == 0 and seen == ['connect', 'upload', 'remote:' + phase],
              'read-only mode should not need a backup or mutation')

    result, seen = run('existing-same', ['--apply'])
    check(result.returncode == 0 and 'copy:.sql' not in seen and 'offsite:.sql' in seen,
          'already preserved exact backup is reverified without overwriting it')
    for case in ('existing-different', 'existing-symlink'):
        result, seen = run(case, ['--apply'])
        check(result.returncode != 0 and 'remote:apply' not in seen and 'copy:.sql' not in seen,
              case + ' must refuse without overwriting an existing local backup')

    before_write = ('prepare-fail', 'duplicate-backup', 'malformed-extra-backup', 'wrong-checksum',
                    'remote-changed', 'offsite-db-fail')
    for case in before_write:
        result, seen = run(case, ['--apply'])
        check(result.returncode != 0 and 'remote:apply' not in seen,
              case + ' must stop before apply')
    after_write = ('missing-snapshot', 'duplicate-snapshot', 'malformed-extra-snapshot', 'wrong-size',
                   'offsite-snapshot-fail', 'missing-completion', 'wrong-completion', 'apply-fail')
    for case in after_write:
        result, seen = run(case, ['--apply'])
        check(result.returncode != 0 and 'remote:verify-purged' not in seen,
              case + ' must stop before purge/render')
        if case == 'apply-fail':
            check('offsite:.json' in seen, 'failed apply still preserves its actual snapshot offsite')
    result, seen = run('verify-fail', ['--apply'])
    check(result.returncode != 0 and 'remote:verify-purged' in seen,
          'failed served verification cannot return success')
    for case in ('prepare-fail', 'offsite-db-fail', 'offsite-snapshot-fail'):
        result, seen = run(case, ['--rollback', SNAP])
        check(result.returncode != 0 and 'remote:rollback' not in seen,
              case + ' must stop rollback before mutation')
    for args, overrides in ((['--apply'], {'S_TARGET': 'live'}),
                            (['--apply'], {'S_TARGET': 'staging38'}),
                            (['--apply'], {'TS_OFFSITE_HOOK': ''}),
                            (['--rollback', SNAP.replace('search-contrast', 'other')], {}),
                            (['--verify', '/tmp/snapshot.json'], {}),
                            (['--apply', 'extra'], {}), (['--verify'], {})):
        result, seen = run('ok', args, **overrides)
        check(result.returncode != 0 and not seen, 'bad target/arguments must fail before SSH')
    agent_socket.close()

    # Execute the actual remote wrapper's path functions against real directory
    # aliases. Only the inherited home ancestor may resolve differently; private
    # directory/file leaf symlinks and foreign canonical parents stay refused.
    source = PUSH.read_text()
    start = '# BEGIN SEARCH PRIVATE PATH FUNCTIONS (exercised with real filesystem aliases).\n'
    end = '# END SEARCH PRIVATE PATH FUNCTIONS.'
    check(source.count(start) == 1 and source.count(end) == 1,
          'exact remote path-function test seam remains unique')
    functions = source.split(start, 1)[1].split(end, 1)[0]
    physical_home = root / 'physical-home'
    physical = physical_home / 'private-backups'
    physical.mkdir(parents=True)
    alias_home = root / 'logical-home'
    alias_home.symlink_to(physical_home, target_is_directory=True)
    logical = alias_home / 'private-backups'
    actual_snapshot = physical / Path(SNAP).name
    actual_snapshot.write_text(PAYLOADS[SNAP])
    logical_snapshot = logical / Path(SNAP).name

    def path_check(function: str, *arguments: Path) -> subprocess.CompletedProcess[str]:
        return subprocess.run(['bash', '-c', 'set -euo pipefail\n' + functions +
                               '\n"$@"', 'search-path-test', function, *map(str, arguments)],
                              text=True, capture_output=True, timeout=10, check=False)

    for directory in (physical, logical):
        result = path_check('canonical_private_dir', directory)
        check(result.returncode == 0 and result.stdout.strip() == str(physical),
              'canonical private directory accepts physical path and home-ancestor alias')
    for snapshot_path in (actual_snapshot, logical_snapshot):
        result = path_check('canonical_snapshot', snapshot_path, physical)
        check(result.returncode == 0 and result.stdout.strip() == str(actual_snapshot),
              'verify/rollback snapshot normalizes only its same canonical parent')
    directory_link = root / 'private-link'
    directory_link.symlink_to(physical, target_is_directory=True)
    check(path_check('canonical_private_dir', directory_link).returncode != 0,
          'private directory leaf symlink is refused')
    check(path_check('canonical_snapshot', directory_link / Path(SNAP).name, physical).returncode != 0,
          'snapshot behind a private-directory leaf symlink is refused')
    snapshot_link = physical / 'search-contrast-before-20261111-120002-ffffffff.json'
    snapshot_link.symlink_to(actual_snapshot)
    check(path_check('canonical_snapshot', snapshot_link, physical).returncode != 0,
          'snapshot leaf symlink is refused before canonicalization')
    foreign = root / 'foreign-private'
    foreign.mkdir()
    foreign_snapshot = foreign / Path(SNAP).name
    foreign_snapshot.write_text(PAYLOADS[SNAP])
    check(path_check('canonical_snapshot', foreign_snapshot, physical).returncode != 0,
          'foreign private parent is refused even with the exact filename')
    check(path_check('canonical_snapshot', physical / 'missing.json', physical).returncode != 0,
          'missing snapshot cannot be normalized into a claimed file')
    check('WWM_BACKUP_DIR=$(canonical_private_dir "$HOME/private-backups")' in source and
          'snap=$(canonical_snapshot "$snap" "$WWM_BACKUP_DIR")' in source,
          'deployed wrapper uses tested canonical parent binding')

print(f'Search push: {checks} passed / 0 failed (offline command doubles).')
