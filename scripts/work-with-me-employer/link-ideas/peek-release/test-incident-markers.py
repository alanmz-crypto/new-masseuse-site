#!/usr/bin/env python3
"""Synthetic LIVE30 marker/receipt boundaries; no site, SSH or database access."""
import copy
import hashlib
import importlib.util
import json
from pathlib import Path
import subprocess
import sys
import tempfile

HERE = Path(__file__).resolve().parent
spec = importlib.util.spec_from_file_location('incident_marker', HERE/'validate-marker.py')
parser = importlib.util.module_from_spec(spec)
spec.loader.exec_module(parser)
checks = 0


def canonical(value):
    return json.dumps(value, sort_keys=True, ensure_ascii=False, separators=(',', ':')).encode()


def sha(value):
    return hashlib.sha256(value).hexdigest()


def check(ok, label):
    global checks
    checks += 1
    if not ok:
        raise AssertionError(label)


with tempfile.TemporaryDirectory(prefix='peek-incident-markers-') as td:
    package = Path(td)
    for name in ('manifest.json', 'live-profile.json'):
        (package/name).write_bytes((HERE/name).read_bytes())
    manifest_sha = sha((package/'manifest.json').read_bytes())
    profile_sha = sha((package/'live-profile.json').read_bytes())
    roles = ('thai-massage', 'therapeutic-massage', 'deep-tissue-massage', 'chair-massage', 'core')
    ids = dict(zip(roles, range(1339, 1344)))
    removed = {}
    added = {}
    for target, items in ((removed, ((4701, 1255), (4702, 1257))), (added, ((5089, 1254), (5090, 1255), (5091, 1257)))):
        for meta_id, post_id in items:
            value = 'SYNTHETIC_PRIVATE_CACHE_' + str(meta_id)
            row = dict(meta_id=str(meta_id), post_id=str(post_id), meta_key='_uag_page_assets', meta_value=value)
            target[str(meta_id)] = dict(post_id=post_id, meta_key='_uag_page_assets', row_sha256=sha(canonical(row)), value_sha256=sha(value.encode()), value_bytes=len(value))
    old = dict(records={'29': {'post': {'post_content': 'original CSS'}}}, options={'astra-settings': {'option_value': 'original settings'}}, taxonomies=[dict(term_taxonomy_id='9', count='17'), dict(term_taxonomy_id='10', count='17')], owned=dict.fromkeys(roles), witnesses={'postmeta': {key: row['row_sha256'] for key, row in removed.items()}})
    new = copy.deepcopy(old)
    new['records']['29']['post']['post_content'] = 'applied CSS'
    new['options']['astra-settings']['option_value'] = 'applied settings'
    for row in new['taxonomies']:
        row['count'] = '22'
    new['owned'] = {role: {'post': {'ID': str(ids[role])}} for role in roles}
    snapshot = dict(schema=1, bundle='peek-visitor-release-v1', home='http://thaisolitude.com', manifest_sha256=manifest_sha, profile_sha256=profile_sha, complete=True, ids=ids, old=old, old_sha256=sha(canonical(old)), new=new, new_sha256=sha(canonical(new)))
    raw = canonical(snapshot)
    snapshot_name = 'peek-release-before-20261111-162615-e00022b6.json'
    remote = '/home/customer/private-backups/' + snapshot_name
    saved = package/snapshot_name
    saved.write_bytes(raw)
    saved.chmod(0o600)
    receipt = dict(schema=1, incident='live30-assets-20261111', bundle=snapshot['bundle'], home=snapshot['home'], manifest_sha256=manifest_sha, profile_sha256=profile_sha, snapshot=dict(file=snapshot_name, sha256=sha(raw), bytes=len(raw)), sql=dict(file='live-before-peek-release-20261111-162615-74851.sql', sha256=sha(b'fixture SQL'), bytes=len(b'fixture SQL')), ids=ids, original_applied_sha256=snapshot['new_sha256'], projected_applied_sha256='', removed=removed, added=added, proof_sha256='f'*64)
    projected_old = parser.incident_projection(old, receipt)
    projected_new = parser.incident_projection(new, receipt)
    receipt['projected_applied_sha256'] = sha(canonical(projected_new))

    def publish(value=receipt):
        body = canonical(value)
        (package/'incident-live30.json').write_bytes(body)
        (package/'incident-live30-binding.json').write_bytes(canonical(dict(schema=1, incident='live30-assets-20261111', receipt_sha256=sha(body))))
        return sha(body)

    receipt_sha = publish()
    marker = dict(schema=1, bundle=snapshot['bundle'], home=snapshot['home'], state='applied', snapshot=remote, snapshot_sha256=sha(raw), manifest_sha256=manifest_sha, profile_sha256=profile_sha, state_sha256=receipt['projected_applied_sha256'], complete=True, ids=ids, receipt_sha256=receipt_sha, sql_sha256=receipt['sql']['sha256'], cache_delta_retained=True)
    log = package/'marker.log'

    def verify(value, expected=True, body=None):
        log.write_text(body if body is not None else 'RELEASE-INCIDENT ' + json.dumps(value) + '\n')
        try:
            parser.validate(package, log, 'RELEASE-INCIDENT', value['state'], remote, saved)
            passed = True
        except Exception:
            passed = False
        check(passed == expected, 'incident marker acceptance boundary')

    verify(marker)
    verify(dict(marker, state='restored', state_sha256=sha(canonical(projected_old))))
    prefixes = parser.incident_prefixes(projected_old, projected_new, ids)
    check(len(prefixes) == 8 and len(set(prefixes)) == 8, 'all seven reverse operations produce distinct bound prefix states')
    for prefix in prefixes:
        verify(dict(marker, state='recoverable', state_sha256=prefix))
    check(old == snapshot['old'] and new == snapshot['new'] and saved.read_bytes() == raw, 'projection and prefix reconstruction leave immutable originals intact')
    result = subprocess.run([sys.executable, '-I', '-B', str(HERE/'validate-marker.py'), '--incident-bindings', str(package)], text=True, capture_output=True, check=False)
    bindings = json.loads(result.stdout)
    check(result.returncode == 0 and bindings == dict(snapshot_basename=snapshot_name, snapshot_sha256=sha(raw), snapshot_bytes=len(raw), sql_basename=receipt['sql']['file'], sql_sha256=receipt['sql']['sha256'], sql_bytes=receipt['sql']['bytes'], receipt_basename='incident-live30.json', receipt_sha256=receipt_sha), 'accessor exact shared binding contract')
    for field, value in (('schema', True), ('state', 'original'), ('complete', False), ('cache_delta_retained', 1), ('receipt_sha256', '0'*64), ('sql_sha256', '0'*64), ('snapshot_sha256', '0'*64), ('state_sha256', snapshot['new_sha256']), ('ids', dict(ids, core=999)), ('extra', 'unexpected')):
        verify(dict(marker, **{field: value}), False)
    verify(dict(marker, state='recoverable', state_sha256='0'*64), False)
    verify(dict(marker, state='restored', state_sha256=snapshot['old_sha256']), False)
    verify(marker, False, 'RELEASE-INCIDENT ' + json.dumps(marker) + '\nRELEASE-INCIDENT {}\n')
    verify(marker, False, 'RELEASE-INCIDENT ' + json.dumps(marker)[:-1] + ',"schema":1}\n')
    for mutation in ('missing-removal', 'sixth-addition', 'wrong-post', 'wrong-key', 'bool-bytes', 'wrong-applied', 'wrong-projection', 'wrong-old', 'wrong-journal', 'wrong-manifest', 'extra-field'):
        bad = copy.deepcopy(receipt)
        if mutation == 'missing-removal':
            del bad['removed']['4701']
        elif mutation == 'sixth-addition':
            bad['added']['5092'] = copy.deepcopy(bad['added']['5091'])
        elif mutation == 'wrong-post':
            bad['added']['5089']['post_id'] = ids['core']
        elif mutation == 'wrong-key':
            bad['removed']['4701']['meta_key'] = '_edit_lock'
        elif mutation == 'bool-bytes':
            bad['added']['5090']['value_bytes'] = True
        elif mutation == 'wrong-applied':
            bad['original_applied_sha256'] = '0'*64
        elif mutation == 'wrong-projection':
            bad['projected_applied_sha256'] = '0'*64
        elif mutation == 'wrong-old':
            bad['projected_original_sha256'] = '0'*64
        elif mutation == 'wrong-journal':
            bad['snapshot']['sha256'] = '0'*64
        elif mutation == 'wrong-manifest':
            bad['manifest_sha256'] = '0'*64
        else:
            bad['extra'] = 'unexpected'
        verify(dict(marker, receipt_sha256=publish(bad)), False)
    publish()
    saved.write_bytes(raw + b' ')
    verify(marker, False)
    saved.write_bytes(raw)
    saved.chmod(0o644)
    verify(marker, False)
    saved.chmod(0o600)
    for invalid in (None, '0'*64):
        (package/'incident-live30-binding.json').write_bytes(canonical(dict(schema=1, incident='live30-assets-20261111', receipt_sha256=invalid)))
        verify(marker, False)
    publish()
    (package/'incident-live30.json').unlink()
    (package/'incident-live30.json').symlink_to(HERE/'manifest.json')
    verify(marker, False)
    check('SYNTHETIC_PRIVATE' not in result.stdout + result.stderr, 'binding accessor discloses no metadata values')

print(f'PASS LIVE30 incident marker adapter: {checks} offline controls; no live or cold-copy proof.')
