#!/usr/bin/env python3
"""Bind one installer marker to the preserved actual journal; never print rows."""
import hashlib
import json
from pathlib import Path
import re
import sys


def require(ok):
    if not ok:
        raise ValueError('binding')


def unique(pairs):
    out = {}
    for key, value in pairs:
        require(key not in out)
        out[key] = value
    return out


def read_json(data):
    return json.loads(data, object_pairs_hook=unique)


def sha(data):
    return hashlib.sha256(data).hexdigest()


def digest(value):
    return sha(json.dumps(value, sort_keys=True, ensure_ascii=False, separators=(',', ':')).encode())


def validate(package, log, kind, state, remote, saved=None):
    package = Path(package)
    require(kind in ('RELEASE-PREFLIGHT', 'RELEASE-SNAPSHOT', 'RELEASE-RECOVERY'))
    lines = [line for line in Path(log).read_text().splitlines() if re.match(r'^'+kind+r'(?:\s|$)', line)]
    require(len(lines) == 1 and lines[0].startswith(kind+' '))
    marker = read_json(lines[0][len(kind)+1:])
    require(type(marker['schema']) is int and marker['schema'] == 1 and marker['bundle'] == 'peek-visitor-release-v1')
    require(marker['home'] == 'http://thaisolitude.com' and marker['state'] == state)
    require(marker['manifest_sha256'] == sha((package/'manifest.json').read_bytes()))
    require(marker['profile_sha256'] == sha((package/'live-profile.json').read_bytes()))
    if kind == 'RELEASE-PREFLIGHT':
        require(state == 'ready' and re.fullmatch('[a-f0-9]{64}', marker['protected_state_sha256']))
        return
    require(re.fullmatch(r'/home/[a-zA-Z0-9_-]+/private-backups/peek-release-before-\d{8}-\d{6}-[a-f0-9]{8}\.json', remote))
    require(marker['snapshot'] == remote and saved is not None)
    saved = Path(saved)
    require(saved.is_file() and not saved.is_symlink() and saved.stat().st_mode & 0o777 == 0o600)
    raw = saved.read_bytes(); snap = read_json(raw)
    require(marker['snapshot_sha256'] == sha(raw))
    for key in ('schema','bundle','home','manifest_sha256','profile_sha256','ids'):
        require(marker[key] == snap[key])
    roles = ('core','thai-massage','therapeutic-massage','deep-tissue-massage','chair-massage')
    require(type(snap['complete']) is bool and (isinstance(snap['ids'], dict) or snap['ids'] == []))
    role_ids = snap['ids'] if isinstance(snap['ids'], dict) else {}
    require(set(role_ids).issubset(roles))
    require(all(type(i) is int and i > 0 for i in role_ids.values()))
    require(len(set(role_ids.values())) == len(snap['ids']))
    require(digest(snap['old']) == snap['old_sha256'])
    key = 'new' if snap['complete'] else 'journal'
    require(digest(snap[key]) == snap[key+'_sha256'])
    require(re.fullmatch('[a-f0-9]{64}', marker['state_sha256']))
    if kind == 'RELEASE-SNAPSHOT':
        require(snap['complete'] and set(role_ids) == set(roles) and state in ('applied','original'))
    else:
        require(type(marker['complete']) is bool and marker['complete'] == snap['complete'] and state in ('recoverable','original'))
    if state in ('applied','original'):
        require(marker['state_sha256'] == snap['new_sha256' if state == 'applied' else 'old_sha256'])


if __name__ == '__main__':
    try:
        require(len(sys.argv) in (6,7))
        validate(*sys.argv[1:])
    except Exception:
        raise SystemExit('Release marker/private journal binding failed; no values disclosed.')
    print('Exact release marker/private journal binding verified.')
