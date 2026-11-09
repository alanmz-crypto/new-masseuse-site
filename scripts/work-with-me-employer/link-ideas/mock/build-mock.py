#!/usr/bin/env python3
"""Build separate local structural fixtures for Peek (never real website copy).

The four service-page shapes come from jump-links.php, including the six-section
Therapeutic/Chair order. The enabled pages follow peek-pages.json; the browser
fixture checks may also inject the packaged core into prepared shapes explicitly.
All jump targets are in the HTML so the no-JavaScript fallback is testable.
The tiny dark theme exercises the core's colour branches, not the site's theme.
Responsive root sizes reproduce the observed staging typography reflow so a
768px-to-desktop resize genuinely changes the summary's scrolling endpoint.

  python3 build-mock.py [OUT_DIR] [--enabled-page SLUG]
  # --enabled-page adds one approved page for this local output only; repeatable.
  python3 -m http.server 8090 -d OUT_DIR
"""
import argparse
import html
import json
from pathlib import Path
import re

here = Path(__file__).resolve().parent
package = here.parent
parser = argparse.ArgumentParser(description=__doc__)
parser.add_argument('out_dir', nargs='?', type=Path, default=here / 'site')
parser.add_argument('--enabled-page', action='append', default=[], metavar='SLUG',
                    help='also enable this approved page in local output only (repeatable)')
args = parser.parse_args()
out = args.out_dir
manifest = json.loads((package / 'peek-pages.json').read_text(encoding='utf-8'))
if manifest.get('schema') != 1 or not isinstance(manifest.get('pages'), list):
    raise SystemExit('Unsupported peek-pages.json schema.')
slugs = [entry['slug'] for entry in manifest['pages']]
if len(slugs) != len(set(slugs)):
    raise SystemExit('Duplicate pages in peek-pages.json.')
enabled_pages = {entry['slug'] for entry in manifest['pages'] if entry['staging'] == 'enabled'}
for slug in args.enabled_page:
    if slug not in slugs:
        parser.error(f'--enabled-page is not a manifest page: {slug}')
    data = json.loads((package / 'peek-data' / f'{slug}.json').read_text(encoding='utf-8'))
    if data.get('approval', {}).get('status') != 'approved':
        parser.error(f'--enabled-page requires approved wording: {slug}')
    enabled_pages.add(slug)
src = (package / 'peek-nav.php').read_text(encoding='utf-8')
css_match = re.search(r'(<style id="ts-peek-nav-css">.*?</style>)', src, re.S)
js_match = re.search(r'(<script id="ts-peek-nav-runtime">.*?</script>)', src, re.S)
if not css_match or not js_match:
    raise SystemExit('Core style/runtime markers missing; cannot build fixtures.')
css, js = css_match.group(1), js_match.group(1)
para = ("Placeholder sentence for the mock page, standing in for the first sentence of the section. "
        "A second placeholder sentence follows so that the preview has more than one line of text to show. ")


def sec(hid, title, n):
    """Keep Thai's original heights and wide/tricky blocks for gesture regressions."""
    wide = f'<div class="wp-block-group" style="margin-right:-14px"><p>{para}</p></div>'
    tricky = ('<p>A mock line about a session of about 1.5 hours, set in the U.S. style of the check. '
              'A visitor said "it felt longer." That ends the mock line.</p>') if hid == 'ts-common-reasons' else ''
    full = (f'<div class="alignfull mock-full" style="box-sizing:border-box;width:100vw;max-width:100vw;'
            f'margin-left:calc(50% - 50vw);padding:0 20px;background:#ECEBE4"><p>{para}</p></div>') if hid == 'ts-common-reasons' else ''
    return (f'<h2 class="wp-block-heading" id="{html.escape(hid, quote=True)}">{html.escape(title)}</h2>'
            + tricky + full + wide + ''.join(f'<p>{para}</p>' for _ in range(n - 1)))


plans_source = (package.parent / 'page-navigation' / 'jump-links.php').read_text(encoding='utf-8')


def service_plan(slug):
    """Read only the reviewed exact-heading plans; fail if their structure changes."""
    block = re.search(r"\t\t'" + re.escape(slug) + r"' => array\(\n(.*?)\n\t\t\),", plans_source, re.S)
    if not block:
        raise ValueError(f'No exact-heading plan for {slug}')
    lines = [line.strip() for line in block.group(1).splitlines() if line.strip()]
    result = []
    for line in lines:
        match = re.fullmatch(r"array\( '([^']+)', 'exact', '([^']+)'(?:, '([^']+)')? \),", line)
        if not match:
            raise ValueError(f'Unexpected navigation plan: {line}')
        label, heading, anchor = match.groups()
        if anchor is None:
            anchor = 'ts-' + re.sub(r'[^a-z0-9]+', '-', label.lower()).strip('-')
        result.append((anchor, label, heading))
    return result


def row_for(plan):
    if not plan:
        return ''
    links = ''.join(f'<li><a href="#{html.escape(anchor, quote=True)}">{html.escape(label)}</a></li>'
                    for anchor, label, _ in plan)
    return f'<nav class="ts-jump" aria-label="On this page"><ul class="ts-jump__list">{links}</ul></nav>'


mock_theme = '''<script>try{document.documentElement.dataset.tsTheme=localStorage.getItem('ts-theme')||'light';}catch(e){}</script>
<style>
html{font-size:15px}@media(max-width:921px){html{font-size:13.68px}}
body{margin:0;font-family:system-ui;background:#F5F4EF;color:#1C2531}.entry-content{padding:0}.mock-inner{padding:0 20px}
#masthead{background:#fff}#ts-section-guide-trigger{position:fixed;left:10px;bottom:10px}
html[data-ts-theme="dark"]{--ts-ink:#E8EBEF;--ts-muted:#B5BECA;--ts-rule:#414B58;--ts-panel:#1B2430;--ts-ground:#111923;--ts-stone:#273340}
html[data-ts-theme="dark"] body{background:var(--ts-ground);color:var(--ts-ink)}
html[data-ts-theme="dark"] #masthead,html[data-ts-theme="dark"] .mock-full{background:var(--ts-stone)!important}
</style>'''
header = ('<header id="masthead"><div id="ast-mobile-header"><div style="height:85px;display:flex;align-items:center;'
          'justify-content:space-between;padding:0 16px">header'
          '<button class="menu-toggle" type="button" aria-label="Menu" style="width:44px;height:44px">&#9776;</button></div>'
          '<div class="ast-mobile-header-content" style="display:none;padding:8px 16px">'
          + ''.join(f'<p style="margin:0;padding:14px 0">Menu item {n}</p>' for n in range(1, 9))
          + '</div></div></header><script>document.addEventListener("click",function(e){if(e.target.closest(".menu-toggle"))'
          '{var c=document.querySelector(".ast-mobile-header-content");c.style.display=c.style.display==="none"?"block":"none";}});</script>')


def page(slug, plan, body=None, enabled=False):
    data_script = ''
    if enabled:
        data = json.loads((package / 'peek-data' / f'{slug}.json').read_text(encoding='utf-8'))
        # Match ts_pk_page_data(): unapproved words never reach public source.
        if data.get('approval', {}).get('status') != 'approved':
            data['items'] = []
            data['coverage'] = {}
        # JSON is data inside a script element; escape HTML-sensitive bytes.
        encoded = json.dumps(data, ensure_ascii=True).replace('<', '\\u003c').replace('>', '\\u003e').replace('&', '\\u0026')
        data_script = f'<script type="application/json" id="ts-peek-nav-data">{encoded}</script>'
    counts = {'ts-what-it-is': 6, 'ts-common-reasons': 9, 'ts-what-to-expect': 5, 'ts-guide-questions': 8, 'ts-guide-research': 10}
    if body is None:
        body = ''.join(sec(anchor, heading, counts.get(anchor, 6)) for anchor, _, heading in plan)
    return ('<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            + mock_theme + (css if enabled else '') + '</head><body>' + header
            + '<div class="entry-content"><div class="mock-inner"><h1>' + html.escape(slug.replace('-', ' ').title()) + ' (structural mock)</h1>'
            + f'<p>{para}</p>' + row_for(plan) + body + '</div></div><footer style="height:400px;background:#222"></footer>'
            + '<button id="ts-section-guide-trigger">On this page</button>' + data_script + (js if enabled else '') + '</body></html>')


pages = {}
for slug in slugs:
    pages[slug] = page(slug, service_plan(slug), enabled=slug in enabled_pages)
for name, count in [('zero', 0), ('one', 1), ('two', 2), ('seven', 7)]:
    plan = [(f'fixture-{n}', f'Section {n}', f'Structural fixture section {n}') for n in range(1, count + 1)]
    pages[f'peek-shape-{name}'] = page(f'peek-shape-{name}', plan)
details_plan = [('fixture-first', 'First section', 'First section'), ('fixture-detail', 'Closed detail', 'Closed detail heading')]
details_body = (sec('fixture-first', 'First section', 4)
                + '<details><summary>Open the native details</summary>' + sec('fixture-detail', 'Closed detail heading', 4) + '</details>')
pages['peek-shape-details'] = page('peek-shape-details', details_plan, details_body)
for slug, markup in pages.items():
    destination = out / slug / 'index.html'
    destination.parent.mkdir(parents=True, exist_ok=True)
    destination.write_text(markup, encoding='utf-8')
print(f'{len(pages)} structural mock routes written to {out}; enabled: {", ".join(sorted(enabled_pages)) or "none"}')
