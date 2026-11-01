#!/usr/bin/env python3
"""Read-only E origin validation: image delivery/attributes, preloads and emoji."""
from pathlib import Path
from html.parser import HTMLParser
from urllib.parse import urlsplit
import hashlib
import json
import subprocess
import sys

base = sys.argv[1].rstrip('/')
if base not in ('http://localhost:8090', 'https://staging38.thaisolitude.com', 'https://thaisolitude.com'):
    sys.exit('Exact staging38, live apex or isolated localhost only')
data = json.loads(Path(__file__).with_name('changes.json').read_text())
total = failures = 0


def check(label, ok):
    global total, failures
    total += 1
    if not ok:
        failures += 1
    print(('PASS ' if ok else 'FAIL ') + label)


def get(path):
    r = subprocess.run(['curl', '-sS', '--max-time', '40', '-w', '\n%{http_code}\n%{content_type}', base + path], capture_output=True)
    parts = r.stdout.rsplit(b'\n', 2)
    if r.returncode or len(parts) != 3:
        sys.exit('FAIL: origin request did not complete')
    body, code, mime = parts
    if code == b'202' or b'<title>Robot Challenge' in body:
        print('INVALID: hosting challenge; no pass claimed')
        sys.exit(2)
    return body, code.decode(), mime.decode()


class Tags(HTMLParser):
    def __init__(self):
        super().__init__()
        self.images, self.fonts = [], []

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'img':
            self.images.append(attrs)
        if tag == 'link' and attrs.get('rel') == 'preload' and attrs.get('as') == 'font':
            self.fonts.append(attrs)


prefix = '/wp-content/uploads/'
targets = data['originals']
placements = {
    'home': [targets['home-' + name + '-target'] for name in ('therapeutic', 'deep-tissue', 'thai')] + [targets['corporate']],
    'services': [targets['corporate']],
    'work-with-me': [targets['portrait'], targets['thai-session-cutout']],
    'therapeutic-massage': [dict(data['media'], width=200, height=200)],
}
fonts = {
    'https://fonts.gstatic.com/s/hankengrotesk/v12/ieVn2YZDLWuGJpnzaiwFXS9tYtpd59A.woff2',
    'https://fonts.gstatic.com/s/newsreader/v26/cY9AfjOCX1hbuyalUrK4397yjA.woff2',
}
for slug, expected in placements.items():
    body, code, _ = get('/' if slug == 'home' else '/' + slug + '/')
    page = body.decode('utf-8')
    check(slug + ' HTTP 200', code == '200')
    parsed = Tags()
    parsed.feed(page)
    check(slug + ' two exact anonymous WOFF2 preloads', len(parsed.fonts) == 2 and {f.get('href') for f in parsed.fonts} == fonts and all(f.get('type') == 'font/woff2' and f.get('crossorigin') == 'anonymous' for f in parsed.fonts))
    check(slug + ' emoji settings/loader/styles absent', all(value not in page for value in ('id="wp-emoji-settings"', 'wp-emoji-loader', 'wp-emoji-release.min.js', 'id="wp-emoji-styles-inline-css"')))
    for item in expected:
        path = prefix + item['relative_file']
        images = [img for img in parsed.images if urlsplit(img.get('src', '')).path == path]
        check(slug + ' image dimensions ' + item['relative_file'], len(images) == 1 and images[0].get('width') == str(item['width']) and images[0].get('height') == str(item['height']))
        if slug == 'home' and item is not targets['corporate']:
            img = images[0] if images else {}
            check(slug + ' responsive canonical image ' + item['relative_file'], '300w' in img.get('srcset', '') and '512w' in img.get('srcset', '') and 'wp-image-' not in img.get('class', '') and img.get('loading') == 'lazy')
    if slug == 'therapeutic-massage':
        check('Therapeutic no old PNG reference', targets['therapeutic-png']['relative_file'] not in page)

body, code, mime = get(prefix + data['media']['relative_file'])
check('WebP delivered with correct status/type', code == '200' and mime.split(';')[0] == 'image/webp')
check('WebP delivered exact prepared bytes', len(body) == data['media']['filesize'] and hashlib.sha256(body).hexdigest() == data['media']['sha256'])
print('Bundle E: %d origin assertions, %d failures' % (total, failures))
sys.exit(1 if failures else 0)
