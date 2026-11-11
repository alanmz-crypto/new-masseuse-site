#!/usr/bin/env python3
"""Bind one installer marker to the preserved actual journal; never print rows."""
import hashlib
import copy
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


def exact_keys(value, keys):
    require(isinstance(value, dict) and set(value) == set(keys))


def is_sha(value):
    return isinstance(value, str) and re.fullmatch('[a-f0-9]{64}', value) is not None


def incident_binding(package):
    package = Path(package)
    binding_path = package/'incident-live30-binding.json'
    receipt_path = package/'incident-live30.json'
    for path in (binding_path, receipt_path):
        require(path.is_file() and not path.is_symlink() and path.resolve() == path)
    binding = read_json(binding_path.read_bytes())
    exact_keys(binding, ('schema', 'incident', 'receipt_sha256'))
    require(type(binding['schema']) is int and binding['schema'] == 1 and binding['incident'] == 'live30-assets-20261111' and is_sha(binding['receipt_sha256']))
    raw = receipt_path.read_bytes()
    require(sha(raw) == binding['receipt_sha256'])
    r = read_json(raw)
    exact_keys(r, ('schema', 'incident', 'bundle', 'home', 'manifest_sha256', 'profile_sha256', 'snapshot', 'sql', 'ids', 'original_applied_sha256', 'projected_applied_sha256', 'removed', 'added', 'proof_sha256'))
    require(type(r['schema']) is int and r['schema'] == 1 and r['incident'] == 'live30-assets-20261111' and r['bundle'] == 'peek-visitor-release-v1' and r['home'] == 'http://thaisolitude.com')
    for key in ('manifest_sha256', 'profile_sha256', 'original_applied_sha256', 'projected_applied_sha256', 'proof_sha256'):
        require(is_sha(r[key]))
    require(r['manifest_sha256'] == sha((package/'manifest.json').read_bytes()) and r['profile_sha256'] == sha((package/'live-profile.json').read_bytes()))
    for key, pattern in (('snapshot', r'peek-release-before-\d{8}-\d{6}-[a-f0-9]{8}\.json'), ('sql', r'live-before-peek-release-\d{8}-\d{6}-[0-9]+\.sql')):
        exact_keys(r[key], ('file', 'sha256', 'bytes'))
        require(isinstance(r[key]['file'], str) and re.fullmatch(pattern, r[key]['file']) and is_sha(r[key]['sha256']) and type(r[key]['bytes']) is int and r[key]['bytes'] > 0)
    exact_keys(r['ids'], ('core', 'thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage'))
    require(all(type(i) is int and i > 0 for i in r['ids'].values()) and len(set(r['ids'].values())) == 5)
    for kind, identities in (('removed', {'4701': 1255, '4702': 1257}), ('added', {'5089': 1254, '5090': 1255, '5091': 1257})):
        exact_keys(r[kind], identities)
        for meta_id, post_id in identities.items():
            row = r[kind][meta_id]
            exact_keys(row, ('post_id', 'meta_key', 'row_sha256', 'value_sha256', 'value_bytes'))
            require(type(row['post_id']) is int and row['post_id'] == post_id and row['meta_key'] == '_uag_page_assets' and is_sha(row['row_sha256']) and is_sha(row['value_sha256']) and type(row['value_bytes']) is int and row['value_bytes'] > 0 and post_id not in r['ids'].values())
    return r, binding['receipt_sha256']


def incident_projection(state, r):
    state = copy.deepcopy(state)
    rows = state['witnesses']['postmeta']
    require(isinstance(rows, dict))
    for meta_id, row in r['removed'].items():
        require(rows.get(meta_id) == row['row_sha256'])
        del rows[meta_id]
    for meta_id, row in r['added'].items():
        require(meta_id not in rows)
        rows[meta_id] = row['row_sha256']
    return state


def incident_prefixes(old, new, ids):
    """Independent exact reconstruction of the installer's seven reverse steps."""
    current = copy.deepcopy(new)
    hashes = [digest(current)]
    for action in ('core', 'css', 'astra', 'chair-massage', 'deep-tissue-massage', 'therapeutic-massage', 'thai-massage'):
        if action == 'css':
            current['records']['29']['post']['post_content'] = old['records']['29']['post']['post_content']
        elif action == 'astra':
            current['options']['astra-settings'] = copy.deepcopy(old['options']['astra-settings'])
        else:
            require(current['owned'][action]['post']['ID'] == str(ids[action]))
            current['owned'][action] = None
            require(len(current['taxonomies']) == 2)
            for row in current['taxonomies']:
                require(row['term_taxonomy_id'] in ('9', '10') and isinstance(row['count'], str) and re.fullmatch('[1-9][0-9]*', row['count']))
                row['count'] = str(int(row['count']) - 1)
        hashes.append(digest(current))
    require(current == old)
    return hashes


def validate(package, log, kind, state, remote, saved=None):
    package = Path(package)
    require(kind in ('RELEASE-PREFLIGHT', 'RELEASE-SNAPSHOT', 'RELEASE-RECOVERY', 'RELEASE-INCIDENT'))
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
    if kind == 'RELEASE-INCIDENT':
        exact_keys(marker, ('schema', 'bundle', 'home', 'state', 'snapshot', 'snapshot_sha256', 'manifest_sha256', 'profile_sha256', 'state_sha256', 'complete', 'ids', 'receipt_sha256', 'sql_sha256', 'cache_delta_retained'))
        r, receipt_sha = incident_binding(package)
        require(snap['complete'] is True and marker['complete'] is True and marker['cache_delta_retained'] is True)
        require(marker['receipt_sha256'] == receipt_sha and marker['sql_sha256'] == r['sql']['sha256'])
        require(Path(remote).name == r['snapshot']['file'] and len(raw) == r['snapshot']['bytes'] and sha(raw) == r['snapshot']['sha256'])
        require(snap['ids'] == r['ids'] and snap['new_sha256'] == r['original_applied_sha256'])
        old = incident_projection(snap['old'], r); new = incident_projection(snap['new'], r)
        require(digest(new) == r['projected_applied_sha256'])
        require(state in ('applied', 'recoverable', 'restored'))
        expected = incident_prefixes(old, new, snap['ids']) if state == 'recoverable' else [digest(new if state == 'applied' else old)]
        require(marker['state_sha256'] in expected)
        return
    if kind == 'RELEASE-SNAPSHOT':
        require(snap['complete'] and set(role_ids) == set(roles) and state in ('applied','original'))
    else:
        require(type(marker['complete']) is bool and marker['complete'] == snap['complete'] and state in ('recoverable','original'))
    if state in ('applied','original'):
        require(marker['state_sha256'] == snap['new_sha256' if state == 'applied' else 'old_sha256'])


if __name__ == '__main__':
    try:
        if len(sys.argv) == 3 and sys.argv[1] == '--incident-bindings':
            receipt, receipt_sha = incident_binding(sys.argv[2])
            print(json.dumps(dict(snapshot_basename=receipt['snapshot']['file'], snapshot_sha256=receipt['snapshot']['sha256'], snapshot_bytes=receipt['snapshot']['bytes'], sql_basename=receipt['sql']['file'], sql_sha256=receipt['sql']['sha256'], sql_bytes=receipt['sql']['bytes'], receipt_basename='incident-live30.json', receipt_sha256=receipt_sha), separators=(',', ':')))
        else:
            require(len(sys.argv) in (6,7))
            validate(*sys.argv[1:])
            print('Exact release marker/private journal binding verified.')
    except Exception:
        raise SystemExit('Release marker/private journal binding failed; no values disclosed.')
