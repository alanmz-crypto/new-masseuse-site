"""Check the navigation release on every public page after purge."""

import argparse
import hashlib
import re
import subprocess
import tempfile
from html.parser import HTMLParser
from urllib.parse import urljoin

ORIGIN = 'https://thaisolitude.com'
ASSETS = {
    'navigation.js': 'fa65b13f3c29638f912bf2e246b74482f3183dc44b68c0db1c3a548d69eac2b8',
    'navigation.css': '722499cd66a4dc12d0dafd4731a5e5fd7cf5c3106e491b0b530ecaa074fe9c41',
}
ORIGINAL = {
    'style': ('ts-peek-nav-css', '8405c3f03ec27b3a59b61355a04f7187fac1e80d4be1957127a34456236dac50'),
    'script': ('ts-peek-nav-runtime', '1e1d28c69627408a2abcdaa9c9f51708d145c9672d10e6b643b5200373357673'),
}
ROUTES = ('/', '/about/', '/services/', '/contact/', '/deep-tissue-massage/', '/therapeutic-massage/',
          '/thai-massage/', '/work-with-me/', '/privacy-policy/', '/accessibility/',
          '/corporate-massage-portland/', '/chair-massage/', '/winding-willow/')
# Pages whose summary data snippet renders a ts-peek-nav-data script (read from production, 2026-11-27).
DATA_ROUTES = ('/deep-tissue-massage/', '/therapeutic-massage/', '/thai-massage/', '/chair-massage/')


class Page(HTMLParser):
    def __init__(self):
        super().__init__()
        self.main = 0
        self.controls = {}
        self.robots = []

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag == 'main':
            self.main += 1
        if attrs.get('id', '').startswith('ts-peek-'):
            self.controls.setdefault(attrs['id'], []).append((tag, attrs))
        if tag == 'meta' and attrs.get('name', '').lower() == 'robots':
            self.robots.append(attrs.get('content', ''))


def verify(mode, get, origin=ORIGIN):
    robots, _ = get('/robots.txt')
    rules = [line.strip() for line in robots.decode().splitlines() if line.strip()]
    if rules != ['User-agent: *', 'Disallow: /wp-admin/', 'Allow: /wp-admin/admin-ajax.php',
                 'Sitemap: ' + origin + '/wp-sitemap.xml']:
        raise ValueError('robots.txt differs from the recorded production rules')
    print('PASS production robots.txt')
    for route in ROUTES:
        body, headers = get(route)
        page = Page()
        page.feed(body.decode('utf-8'))
        if page.main != 1 or len(page.controls.get('ts-peek-nav-config', [])) != 1:
            raise ValueError(f'{route}: missing main content or navigation configuration')
        if len(page.controls.get('ts-peek-nav-data', [])) != (1 if route in DATA_ROUTES else 0):
            raise ValueError(f'{route}: summary data script count differs from the recorded production pages')
        directives = re.split(r'[,\s]+', ','.join(page.robots + headers).lower())
        if {'noindex', 'none'}.intersection(directives):
            raise ValueError(f'{route}: unexpected robots restriction')
        if mode == 'rollback':
            for tag, (name, expected) in ORIGINAL.items():
                matches = re.findall(fr'<{tag} id="{name}">.*?</{tag}>'.encode(), body, re.S)
                if len(matches) != 1 or hashlib.sha256(matches[0]).hexdigest() != expected:
                    raise ValueError(f'{route}: original {name} differs')
            if b'/ts-peek/v2/' in body:
                raise ValueError(f'{route}: still references v2')
        else:
            for symbol, tag, attr, asset in [('css', 'link', 'href', 'navigation.css'),
                                            ('runtime', 'script', 'src', 'navigation.js')]:
                found = page.controls.get('ts-peek-nav-' + symbol, [])
                expected_url = origin + '/wp-content/ts-peek/v2/' + asset
                if (len(found) != 1 or found[0][0] != tag
                        or urljoin(origin + route, found[0][1].get(attr, '')) != expected_url):
                    raise ValueError(f'{route}: expected one reference to {asset}')
        print(f'PASS {mode} {route}')
    # Both files remain available following rollback for cached v2 HTML.
    for name, expected in ASSETS.items():
        body, _ = get('/wp-content/ts-peek/v2/' + name)
        if hashlib.sha256(body).hexdigest() != expected:
            raise ValueError(f'{name}: served bytes differ')
        print(f'PASS {name} sha256')


def https_get(path):
    with tempfile.NamedTemporaryFile() as header_file:
        reply = subprocess.run(['curl', '--silent', '--show-error', '--fail', '--compressed',
                                '--max-time', '30', '-D', header_file.name, '-w', '%{http_code}',
                                ORIGIN + path], capture_output=True, check=True).stdout
        if reply[-3:] != b'200':
            raise ValueError(f'{path}: HTTP {reply[-3:]!r}')
        robots = re.findall(r'^x-robots-tag:\s*(.*)$', header_file.read().decode('latin1'), re.I | re.M)
        return reply[:-3], robots


if __name__ == '__main__':
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('mode', choices=['apply', 'rollback'])
    verify(parser.parse_args().mode, https_get)
