#!/usr/bin/env python3
"""Read-only public-site checks before a SiteGround release decision."""

import argparse
import re
import sys
import time
from html.parser import HTMLParser
from urllib.parse import urljoin, urlparse
from urllib.request import Request, urlopen


ROUTES = (
    '/', '/about/', '/services/', '/therapeutic-massage/',
    '/deep-tissue-massage/', '/thai-massage/', '/work-with-me/', '/contact/',
)
LICENSE = 'LMT-29905'


class Page(HTMLParser):
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.title = ''
        self.canonical = ''
        self.robots = ''
        self.visible = []
        self.links = []
        self._title = False
        self._hidden = 0
        self._link = None

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        if tag in ('script', 'style'):
            self._hidden += 1
        elif tag == 'title':
            self._title = True
        elif tag == 'link' and 'canonical' in attrs.get('rel', '').lower().split():
            self.canonical = attrs.get('href', '')
        elif tag == 'meta' and attrs.get('name', '').lower() == 'robots':
            self.robots = attrs.get('content', '')
        elif tag == 'a':
            self._link = [attrs.get('href', ''), []]

    def handle_endtag(self, tag):
        if tag in ('script', 'style'):
            self._hidden = max(0, self._hidden - 1)
        elif tag == 'title':
            self._title = False
        elif tag == 'a' and self._link is not None:
            href, parts = self._link
            self.links.append((' '.join(' '.join(parts).split()), href))
            self._link = None

    def handle_data(self, data):
        if self._title:
            self.title += data
        if not self._hidden:
            self.visible.append(data)
            if self._link is not None:
                self._link[1].append(data)


def fetch(url):
    last_error = None
    for attempt in range(3):
        try:
            request = Request(url, headers={'User-Agent': 'ThaiSolitudeReleasePreflight/1.0'})
            with urlopen(request, timeout=15) as response:
                return response.status, response.headers, response.read(), response.url
        except Exception as error:
            last_error = error
            if attempt < 2:
                time.sleep(attempt + 1)
    raise RuntimeError(f'{url}: {last_error}')


def page_at(url, errors):
    try:
        status, headers, body, final_url = fetch(url)
        if status != 200 or final_url.rstrip('/') != url.rstrip('/'):
            errors.append(f'{url}: HTTP {status}, final URL {final_url}')
        page = Page()
        page.feed(body.decode('utf-8', errors='replace'))
        return page, headers
    except Exception as error:
        errors.append(str(error))
        return None, None


def check_action(origin, links, label, errors, pdf=False):
    matches = [href for text, href in links if label.casefold() in text.casefold()]
    if not matches:
        errors.append(f'{origin}: missing {label} action')
        return
    href = matches[0]
    if href.startswith('mailto:'):
        if label != 'Contact Butsaba' or '@' not in href:
            errors.append(f'{origin}: unexpected {label} destination {href}')
        else:
            print(f'  {label}: {href}')
        return
    target = urljoin(origin, href)
    if urlparse(target).scheme != 'https':
        errors.append(f'{origin}: {label} target is not HTTPS: {target}')
        return
    try:
        status, headers, body, _ = fetch(target)
        if status != 200:
            errors.append(f'{target}: {label} returned HTTP {status}')
        if pdf and not body.startswith(b'%PDF-'):
            errors.append(f'{target}: résumé target is not a PDF')
        print(f'  {label}: {target} (HTTP {status})')
    except Exception as error:
        errors.append(str(error))


def check_site(origin, staging, errors):
    role = 'staging' if staging else 'production'
    print(f'{role}: {origin}')
    for route in ROUTES:
        url = urljoin(origin, route)
        page, headers = page_at(url, errors)
        if page is None:
            continue
        if not page.title.strip():
            errors.append(f'{url}: missing HTML title')
        if 'registered therapist' in page.title.casefold():
            errors.append(f'{url}: title calls Butsaba a Registered Therapist')
        if any(phrase in page.title.casefold() for phrase in ('pain relief', 'recovery')):
            errors.append(f'{url}: title contains an outcome claim requiring review: {page.title.strip()!r}')
        if page.canonical.rstrip('/') != url.rstrip('/'):
            errors.append(f'{url}: canonical {page.canonical!r} does not match')
        if LICENSE not in ' '.join(page.visible):
            errors.append(f'{url}: visible Oregon license number {LICENSE} missing')
        indexing = (headers.get('X-Robots-Tag', '') + ',' + page.robots).lower()
        if staging and 'noindex' not in headers.get('X-Robots-Tag', '').lower():
            errors.append(f'{url}: staging lacks X-Robots-Tag: noindex')
        if not staging and 'noindex' in indexing:
            errors.append(f'{url}: production has noindex')
        print(f'  {route}: title={page.title.strip()!r}, canonical={page.canonical!r}')
        if route == '/work-with-me/':
            check_action(origin, page.links, 'Contact Butsaba', errors)
            check_action(origin, page.links, 'View Résumé', errors, pdf=True)

    robots_url = urljoin(origin, '/robots.txt')
    try:
        _, _, body, _ = fetch(robots_url)
        robots = body.decode('utf-8', errors='replace')
        if re.search(r'^\s*Disallow:\s*/\s*$', robots, re.I | re.M):
            errors.append(f'{robots_url}: global Disallow: /')
        if not staging:
            sitemap = re.search(r'^\s*Sitemap:\s*(\S+)', robots, re.I | re.M)
            if not sitemap:
                errors.append(f'{robots_url}: sitemap not advertised')
            else:
                status, _, _, _ = fetch(sitemap.group(1))
                print(f'  sitemap: {sitemap.group(1)} (HTTP {status})')
    except Exception as error:
        errors.append(str(error))


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--staging', default='https://staging12.thaisolitude.com')
    parser.add_argument('--production', default='https://thaisolitude.com')
    args = parser.parse_args()
    errors = []
    check_site(args.staging.rstrip('/') + '/', True, errors)
    check_site(args.production.rstrip('/') + '/', False, errors)
    if errors:
        print('\nPUBLIC PREFLIGHT: FAIL', file=sys.stderr)
        for error in errors:
            print(f'  - {error}', file=sys.stderr)
        return 1
    print('\nPUBLIC PREFLIGHT: PASS')
    print('Manual gates remain: factual claims, SiteGround backup, and exact Custom Deploy selection.')
    return 0


if __name__ == '__main__':
    sys.exit(main())
