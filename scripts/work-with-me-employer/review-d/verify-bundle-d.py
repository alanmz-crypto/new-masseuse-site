#!/usr/bin/env python3
"""Read-only origin checks of the reviewed copy, links and preserved licence."""
from pathlib import Path
import html
import json
import re
import subprocess
import sys

base = sys.argv[1].rstrip('/')
if base not in ('http://localhost:8090', 'https://staging38.thaisolitude.com'):
    sys.exit('staging38 or isolated localhost only')
checks = json.loads(Path(__file__).with_name('checks.json').read_text())
failures = 0
total = 0


def text(source):
    source = re.sub(r'<!--[\s\S]*?-->', '', source)
    source = re.sub(r'<(script|style)\b[^>]*>[\s\S]*?</\1>', '', source)
    return re.sub(r'\s+', ' ', html.unescape(re.sub(r'<[^>]*>', ' ', source))).strip().replace('’', "'").replace('“', '"').replace('”', '"')


for slug, expected in checks.items():
    path = '/' if slug == 'home' else '/' + slug + '/'
    result = subprocess.run(['curl', '-sS', '--max-time', '40', '-w', '\n%{http_code}', base + path], capture_output=True, text=True)
    page, _, code = result.stdout.rpartition('\n')
    if code == '202' or re.search(r'<title>[^<]*Robot Challenge', page, re.I):
        print('INVALID: hosting robot challenge; no pass claimed')
        sys.exit(2)
    main = re.search(r'<main\b[^>]*>[\s\S]*?</main>', page)
    if result.returncode or code != '200' or not main:
        sys.exit('FAIL: no valid rendered origin page for ' + slug)
    body = main[0]
    t = text(body)
    page_fails = 0
    for item in expected['texts']:
        total += 1
        count = t.count(item['text'])
        if count != item['count']:
            page_fails += 1
            print('FAIL', slug, item['label'], 'count', count, 'expected', item['count'])
    hrefs = [html.unescape(h) for h in re.findall(r'<a\b[^>]*\bhref="([^"]+)"', body)]
    for item in expected['links']:
        total += 1
        if hrefs.count(item['href']) != item['count']:
            page_fails += 1
            print('FAIL', slug, 'href', item['href'])
    total += 1
    if 'Butsaba Dusrai, LMT · Oregon License LMT-29905' not in text(page):
        page_fails += 1
        print('FAIL', slug, 'footer licence separator')
    failures += page_fails
    print(('PASS' if not page_fails else 'FAIL'), slug, 'origin copy/link/licence checks')
print('Bundle D: %d origin assertions, %d failures' % (total, failures))
sys.exit(1 if failures else 0)
