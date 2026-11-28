#!/usr/bin/env python3
"""Read-only public verification for the live Thai Solitude booking notice."""

from __future__ import annotations

import re
import sys
from html.parser import HTMLParser
from urllib.error import HTTPError, URLError
from urllib.parse import urljoin, urlparse
from urllib.request import Request, urlopen
from urllib.robotparser import RobotFileParser


ORIGIN = 'https://thaisolitude.com'
BOOKING = 'https://www.windingwillowacupuncture.com/butsaba-dusrai-lmt'
HEADING = "Now booking appointments at Winding Willow Acupuncture and for Solitude's corporate & group massage in Portland."
PARAGRAPH = 'Book an appointment with Butsaba at Winding Willow, or arrange a corporate or group massage at a suitable Portland venue with Thai Solitude.'
LABEL = 'Winding Willow Acupuncture · Corporate & Group Massage · Portland'
EXPECTED_NOINDEX_ROUTES = frozenset({
    '/personal-automation/',
    '/personal-automation/privacy/',
    '/personal-automation/terms/',
})


class NoticeParser(HTMLParser):
    VOID = {'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr'}

    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.meta_robots: list[str] = []
        self.bands: list[dict] = []
        self._band: dict | None = None
        self._stack: list[str] = []
        self._anchor: dict | None = None
        self._style_chunks: list[str] = []
        self._in_style = False

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str | None]]) -> None:
        attrs = dict(attrs)
        if tag == 'meta' and (attrs.get('name') or '').casefold() == 'robots':
            self.meta_robots.append(attrs.get('content') or '')
        if tag == 'style':
            self._in_style = True
        if tag == 'div' and 'ts-corp-band' in (attrs.get('class') or '').split() and self._band is None:
            self._band = {'text': [], 'label': [], 'links': []}
            self.bands.append(self._band)
            self._stack = ['div']
            return
        if self._band is None:
            return
        if tag == 'a':
            self._anchor = {'href': attrs.get('href') or '', 'text': []}
        if tag not in self.VOID:
            self._stack.append(tag)

    def handle_endtag(self, tag: str) -> None:
        if tag == 'style':
            self._in_style = False
        if self._band is not None:
            if tag == 'a' and self._anchor is not None:
                self._anchor['text'] = ' '.join(' '.join(self._anchor['text']).split())
                self._band['links'].append(self._anchor)
                self._anchor = None
            if tag in self._stack:
                index = len(self._stack) - 1 - self._stack[::-1].index(tag)
                del self._stack[index:]
            if not self._stack:
                self._band['text'] = ' '.join(' '.join(self._band['text']).split())
                self._band['label'] = ' '.join(' '.join(self._band['label']).split())
                self._band = None

    def handle_data(self, data: str) -> None:
        if self._in_style:
            self._style_chunks.append(data)
        if self._band is None:
            return
        self._band['text'].append(data)
        if self._anchor is not None:
            self._anchor['text'].append(data)
        # The label is the first compact paragraph before the notice text.
        if not self._band['label'] and 'Winding Willow Acupuncture' in data:
            self._band['label'].append(data)


def fetch(url: str, timeout: int = 20) -> tuple[int, object, bytes, str]:
    request = Request(url, headers={'User-Agent': 'ThaiSolitudeLiveNoticeCheck/1.0'})
    try:
        with urlopen(request, timeout=timeout) as response:
            return response.status, response.headers, response.read(), response.url
    except HTTPError as response:
        return response.code, response.headers, response.read(), response.url


def check(condition: bool, message: str, failures: list[str]) -> None:
    if not condition:
        failures.append(message)


def noindex_present(headers: object, meta_values: list[str]) -> bool:
    fields = list(meta_values)
    if hasattr(headers, 'get_all'):
        fields.extend(headers.get_all('X-Robots-Tag', []))
    else:
        fields.append(headers.get('X-Robots-Tag', ''))
    return any(re.search(r'\b(?:noindex|none)\b', field, re.I) for field in fields if field)


def has_responsive_notice_css(html: str) -> bool:
    if not re.search(r'\.ts-corp-band__mobile\s*\{\s*display\s*:\s*none', html, re.I):
        return False
    media = re.compile(r'@media\s*\(\s*max-width\s*:\s*767px\s*\)\s*\{', re.I)
    for match in media.finditer(html):
        depth = 1
        index = match.end()
        while index < len(html) and depth:
            if html[index] == '{':
                depth += 1
            elif html[index] == '}':
                depth -= 1
            index += 1
        rules = html[match.end():index - 1]
        desktop_hidden = re.search(r'\.ts-corp-band__desktop\s*\{[^}]*display\s*:\s*none', rules, re.I | re.S)
        mobile_shown = re.search(r'\.ts-corp-band__mobile\s*\{[^}]*display\s*:\s*inline', rules, re.I | re.S)
        if desktop_hidden and mobile_shown:
            return True
    return False


def published_pages(failures: list[str]) -> list[tuple[str, str]]:
    pages: list[tuple[str, str]] = []
    page = 1
    while True:
        endpoint = f'{ORIGIN}/wp-json/wp/v2/pages?status=publish&per_page=100&page={page}&_fields=slug,link'
        try:
            status, headers, body, _ = fetch(endpoint)
            check(status == 200, f'Published-pages REST API returned HTTP {status}', failures)
            if status != 200:
                return []
            import json
            batch = json.loads(body)
            if not isinstance(batch, list):
                failures.append('Published-pages REST API response was not a list')
                return []
            for item in batch:
                link = item.get('link', '')
                parsed = urlparse(link)
                if parsed.scheme != 'https' or parsed.netloc != 'thaisolitude.com':
                    failures.append(f"Unexpected published-page URL from REST API: {link!r}")
                    continue
                pages.append((item.get('slug', ''), link))
            try:
                total_pages = int(headers.get('X-WP-TotalPages', '1'))
            except ValueError:
                total_pages = 1
            if page >= total_pages:
                return pages
            page += 1
        except (URLError, TimeoutError, ValueError) as error:
            failures.append(f'Could not enumerate published pages: {error}')
            return []


def sample_published_post(failures: list[str]) -> str | None:
    endpoint = f'{ORIGIN}/wp-json/wp/v2/posts?status=publish&per_page=1&_fields=link'
    try:
        status, _, body, _ = fetch(endpoint)
        check(status == 200, f'Published-posts REST API returned HTTP {status}', failures)
        if status != 200:
            return None
        import json
        posts = json.loads(body)
        if not isinstance(posts, list):
            failures.append('Published-posts REST API response was not a list')
            return None
        if not posts:
            print('INFO no published posts available for a post-route sample')
            return None
        link = posts[0].get('link', '')
        parsed = urlparse(link)
        if parsed.scheme != 'https' or parsed.netloc != 'thaisolitude.com':
            failures.append(f'Unexpected published-post URL from REST API: {link!r}')
            return None
        return link
    except (URLError, TimeoutError, ValueError) as error:
        failures.append(f'Could not sample a published post: {error}')
        return None


def check_notice(url: str, expected_status: int, failures: list[str], *, expected_noindex: bool | None = False) -> bool:
    try:
        status, headers, body, final_url = fetch(url)
    except (URLError, TimeoutError) as error:
        failures.append(f'{url}: fetch failed: {error}')
        return False
    check(status == expected_status, f'{url}: expected HTTP {expected_status}, got {status}', failures)
    if status != expected_status:
        return False
    final = urlparse(final_url)
    requested = urlparse(url)
    check(final.scheme == 'https' and final.netloc == 'thaisolitude.com' and final.path.rstrip('/') == requested.path.rstrip('/'),
          f'{url}: unexpected final URL {final_url}', failures)
    html = body.decode('utf-8', errors='replace')
    parser = NoticeParser()
    parser.feed(html)
    check(len(parser.bands) == 1, f'{url}: expected one booking notice, found {len(parser.bands)}', failures)
    if expected_noindex is not None:
        actual_noindex = noindex_present(headers, parser.meta_robots)
        if expected_noindex:
            check(actual_noindex, f'{url}: expected noindex on this deliberately excluded route', failures)
        else:
            check(not actual_noindex, f'{url}: noindex found in HTML or response headers', failures)
    if len(parser.bands) != 1:
        return False
    band = parser.bands[0]
    check(band['text'].count(HEADING) == 2, f'{url}: expected exact heading in both responsive variants', failures)
    check(band['text'].count(PARAGRAPH) == 2, f'{url}: expected exact paragraph in both responsive variants', failures)
    check(band['label'] == LABEL, f'{url}: unexpected notice label {band["label"]!r}', failures)
    css_match = re.search(r'\.ts-corp-band__text a\s*\{([^}]+)\}', html, re.S)
    css_body = css_match.group(1) if css_match else ''
    check(bool(css_match and re.search(r'text-decoration-style\s*:\s*solid\b', css_body, re.I)
               and re.search(r'text-decoration-thickness\s*:\s*1\.25px\b', css_body, re.I)),
          f'{url}: approved solid 1.25px underline rule is missing', failures)
    check(has_responsive_notice_css(html),
          f'{url}: responsive desktop/mobile notice display rules are missing', failures)
    links = band['links']
    winding = [link for link in links if link['text'] == 'Winding Willow Acupuncture']
    corporate = [link for link in links if link['text'] == 'corporate & group massage']
    booking = [link for link in links if link['text'] == 'Book an appointment with Butsaba']
    check(len(winding) == 2 and all(link['href'] == '/winding-willow/' for link in winding), f'{url}: Winding Willow link destination/count mismatch', failures)
    check(len(corporate) == 2 and all(link['href'] == '/corporate-massage-portland/' for link in corporate), f'{url}: corporate link destination/count mismatch', failures)
    check(len(booking) == 2 and all(link['href'] == BOOKING for link in booking), f'{url}: Butsaba booking link destination/count mismatch', failures)
    check('staging43.thaisolitude.com' not in html, f'{url}: staging43 host leaked into rendered live page', failures)
    if url.endswith('/winding-willow/'):
        check(final_url.rstrip('/') == url.rstrip('/'), f'{url}: internal Winding Willow link redirected to {final_url}', failures)
    return True


def main() -> int:
    failures: list[str] = []
    pages = published_pages(failures)
    check(bool(pages), 'No published pages were enumerated; coverage cannot be established', failures)
    print(f'Live published pages discovered through REST: {len(pages)}')
    for slug, link in pages:
        before = len(failures)
        path = urlparse(link).path.rstrip('/') or '/'
        path = path if path == '/' else path + '/'
        expected_noindex = path in EXPECTED_NOINDEX_ROUTES
        check_notice(link, 200, failures, expected_noindex=expected_noindex)
        if len(failures) == before:
            if expected_noindex:
                print(f'PASS page /{slug}/ (intentional noindex)')
            else:
                print(f'PASS page /{slug}/')

    post_url = sample_published_post(failures)
    if post_url:
        before = len(failures)
        check_notice(post_url, 200, failures)
        if len(failures) == before:
            print(f'PASS representative post {urlparse(post_url).path}')

    # The internal routes must resolve independently of the notice markup.
    for route in ('/winding-willow/', '/corporate-massage-portland/'):
        try:
            status, _, _, final_url = fetch(urljoin(ORIGIN, route))
            check(status == 200 and final_url.rstrip('/') == urljoin(ORIGIN, route).rstrip('/'), f'{route}: expected direct HTTP 200, got {status} at {final_url}', failures)
            if status == 200:
                print(f'PASS internal destination {route}: HTTP 200')
        except (URLError, TimeoutError) as error:
            failures.append(f'{route}: destination fetch failed: {error}')

    # Verify staging is not accidentally linked from live and live remains crawlable.
    try:
        status, _, body, _ = fetch(urljoin(ORIGIN, '/robots.txt'))
        check(status == 200, f'Live robots.txt returned HTTP {status}', failures)
        robots_text = body.decode('utf-8', errors='replace')
        parser = RobotFileParser()
        parser.parse(robots_text.splitlines())
        check(parser.can_fetch('*', '/'), 'Live robots.txt blocks crawling of /', failures)
        sitemap_match = re.search(r'^\s*Sitemap:\s*(\S+)', robots_text, re.I | re.M)
        check(bool(sitemap_match), 'Live robots.txt does not advertise a sitemap', failures)
        if sitemap_match:
            sitemap_url = urlparse(sitemap_match.group(1))
            check(sitemap_url.scheme == 'https' and sitemap_url.netloc == 'thaisolitude.com', 'Live sitemap URL is not the expected HTTPS production host', failures)
            sitemap_status, _, _, _ = fetch(sitemap_match.group(1))
            check(sitemap_status == 200, f'Live sitemap returned HTTP {sitemap_status}', failures)
            print(f'PASS live sitemap: HTTP {sitemap_status}')
    except (URLError, TimeoutError) as error:
        failures.append(f'Live robots.txt check failed: {error}')

    try:
        status, _, body, _ = fetch(urljoin(ORIGIN, '/category/general/'))
        check(status == 200, f'Category archive returned HTTP {status}', failures)
        if status == 200:
            before = len(failures)
            check_notice(urljoin(ORIGIN, '/category/general/'), 200, failures, expected_noindex=None)
            if len(failures) == before:
                print('PASS category archive /category/general/')
    except (URLError, TimeoutError) as error:
        failures.append(f'Category archive check failed: {error}')

    try:
        status, _, body, _ = fetch(urljoin(ORIGIN, '/codex-verify-not-found-20261128/'))
        check(status == 404, f'404 route returned HTTP {status}, expected 404', failures)
        if status == 404:
            parser = NoticeParser()
            parser.feed(body.decode('utf-8', errors='replace'))
            check(len(parser.bands) == 1, f'404 route expected one notice, found {len(parser.bands)}', failures)
            print('PASS representative 404 route')
    except (URLError, TimeoutError) as error:
        failures.append(f'404 route check failed: {error}')

    if failures:
        print('\nLIVE BOOKING NOTICE CHECK: FAIL', file=sys.stderr)
        for failure in failures:
            print(f'  - {failure}', file=sys.stderr)
        return 1
    print('\nLIVE BOOKING NOTICE CHECK: PASS')
    print(f'Checked {len(pages)} published pages, an available post, internal destinations, live robots/sitemap, category archive, and 404.')
    print('The external booking target was checked in markup only; Ryan has confirmed it works by opening it.')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())

# ref: ./install-corporate-band-live.php — updates only the live snippet and Additional CSS while preserving blog_public=1
# src: ../../../docs/reviews/2026-10-31-site-review.md — the three Personal Automation pages are deliberately noindexed and unlisted
