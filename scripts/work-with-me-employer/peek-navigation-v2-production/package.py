"""Build a local production release archive with the exact prior WPCode payload."""

import argparse
import hashlib
import json
from io import BytesIO
from pathlib import Path
from zipfile import ZIP_DEFLATED, ZipFile, ZipInfo

HERE = Path(__file__).resolve().parent
CORE = 'scripts/work-with-me-employer/link-ideas/peek-release/payload/core.php'
INSTALLED = 'f152fad975bb85ed4ad06a0ce5f0000cd656276b34efb38b748646a5fdc73566'


def digest(data):
    return hashlib.sha256(data).hexdigest()


def snippet(source):
    if not source.startswith(b'<?php\n'):
        raise ValueError('Expected a PHP opening tag followed by a newline')
    return source[6:]


def build():
    original = (HERE.parents[2] / CORE).read_bytes()
    rollback = snippet(original)
    if digest(rollback) != INSTALLED:
        raise ValueError('Rollback code differs from the verified production snippet')
    files = {'wpcode/before.txt': rollback, 'wpcode/after.txt': snippet((HERE / 'core.php').read_bytes())}
    for name in ('navigation.js', 'navigation.css'):
        path = f'/ts-peek/v2/{name}'
        if path.encode() not in files['wpcode/after.txt']:
            raise ValueError(f'Core does not reference {path}')
        files['wp-content' + path] = (HERE / name).read_bytes()
    manifest = {
        'host': 'thaisolitude.com', 'spectra_version': '2.20.7',
        'snippet_id': 1343, 'snippet_title': 'TSPeekNav visitor release v1',
        'start_code_sha256': digest(files['wpcode/before.txt']),
        'replacement_code_sha256': digest(files['wpcode/after.txt']),
        'sha256': {path: digest(data) for path, data in files.items()},
    }
    expected = {
        "wpcode/before.txt": "f152fad975bb85ed4ad06a0ce5f0000cd656276b34efb38b748646a5fdc73566",
        "wpcode/after.txt": "6e17165537261decced46bfe310461ac16e6a69db5fea86173cfc5f86b18dd84",
        "wp-content/ts-peek/v2/navigation.js": "fa65b13f3c29638f912bf2e246b74482f3183dc44b68c0db1c3a548d69eac2b8",
        "wp-content/ts-peek/v2/navigation.css": "722499cd66a4dc12d0dafd4731a5e5fd7cf5c3106e491b0b530ecaa074fe9c41"
    }
    if manifest['sha256'] != expected:
        raise ValueError('Production payload differs from the release verified on staging40')
    archive = BytesIO()
    with ZipFile(archive, 'w', ZIP_DEFLATED) as package:
        files['manifest.json'] = (json.dumps(manifest, indent=2) + '\n').encode()
        for path, data in files.items():
            entry = ZipInfo(path)
            entry.external_attr = 0o100644 << 16
            package.writestr(entry, data, compress_type=ZIP_DEFLATED)
    return archive.getvalue()


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('destination', type=Path, help='New local ZIP file')
    destination = parser.parse_args().destination
    data = build()
    with destination.open('xb') as output:
        output.write(data)
