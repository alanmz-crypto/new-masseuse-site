#!/usr/bin/env python3
"""Build a local mock of the Thai Massage page with the real peek-nav.php CSS and JavaScript, for testing without SiteGround.
The mock copies the page's structure that the runtime relies on (a #masthead with a simple Astra-like phone menu inside it, an .entry-content with the server-rendered .ts-jump row, h2 targets,
two heading ids that the section guide assigns only at runtime, a section-guide button) with placeholder text. It is NOT the real copy.

  python3 build-mock.py [OUT_DIR]          default OUT_DIR: ./site (next to this file)
  python3 -m http.server 8090 -d OUT_DIR   then: node ../check-peek-nav.mjs http://localhost:8090   (TS_PLAYWRIGHT_ROOT = a playwright install)
Dark-mode checks fail on the mock (it has no site theme script); everything else should pass."""
import os, re, sys

here = os.path.dirname(os.path.abspath(__file__))
out = sys.argv[1] if len(sys.argv) > 1 else os.path.join(here, 'site')
src = open(os.path.join(here, '..', 'peek-nav.php'), encoding='utf-8').read()
css = re.search(r'(<style id="ts-peek-nav-css">.*?</style>)', src, re.S).group(1)
js = re.search(r'(<script id="ts-peek-nav-runtime">.*?</script>)', src, re.S).group(1)
para = ("Placeholder sentence for the mock page, standing in for the first sentence of the section. "
        "A second placeholder sentence follows so that the preview has more than one line of text to show. ")

def sec(hid, title, n):
    attr = f' id="{hid}"' if hid else ''
    # one block per section is wider than the column (as some theme blocks are on staging39: their own negative margin)
    wide = f'<div class="wp-block-group" style="margin-right:-14px"><p>{para}</p></div>'
    return f'<h2 class="wp-block-heading"{attr}>{title}</h2>' + wide + ''.join(f'<p>{para}</p>' for _ in range(n - 1))

row = ('<nav class="ts-jump" aria-label="On this page"><ul class="ts-jump__list">'
       '<li><a href="#ts-what-it-is">What it is</a></li><li><a href="#ts-common-reasons">Common reasons</a></li>'
       '<li><a href="#ts-what-to-expect">What to expect</a></li><li><a href="#ts-guide-questions">Questions</a></li>'
       '<li><a href="#ts-guide-research">What Research Says</a></li></ul></nav>')
body = (row + sec('ts-what-it-is', 'What Is Thai Massage?', 6) + sec('ts-common-reasons', 'Common Reasons Clients Choose Thai Massage', 9)
        + sec('ts-what-to-expect', 'What To Expect During Your Appointment', 5) + sec('', 'Questions About Thai Massage', 8)
        + sec('', 'What the Research Says About Thai Massage', 10))
# the real section guide assigns these two ids in the browser, after DOMContentLoaded
guide = ('<script>document.addEventListener("DOMContentLoaded",function(){var h=document.querySelectorAll(".entry-content h2:not([id])");'
         'h[0].id="ts-guide-questions";h[1].id="ts-guide-research";});</script><button id="ts-section-guide-trigger">On this page</button>')
page = ('<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">' + css +
        '<style>body{margin:0;font-family:system-ui;background:#F5F4EF}.entry-content{padding:0 20px}#ts-section-guide-trigger{position:fixed;left:10px;bottom:10px}</style>'
        '</head><body><header id="masthead" style="background:#fff"><div id="ast-mobile-header"><div style="height:85px;display:flex;align-items:center;justify-content:space-between;padding:0 16px">header'
        '<button class="menu-toggle" type="button" aria-label="Menu" style="width:44px;height:44px">&#9776;</button></div>'
        '<div class="ast-mobile-header-content" style="display:none;padding:8px 16px">' + ''.join(f'<p style="margin:0;padding:14px 0">Menu item {n}</p>' for n in range(1, 9)) + '</div></div></header>'
        '<script>document.addEventListener("click",function(e){if(e.target.closest(".menu-toggle")){var c=document.querySelector(".ast-mobile-header-content");c.style.display=c.style.display==="none"?"block":"none";}});</script><div class="entry-content"><h1>Thai Massage (mock)</h1>'
        f'<p>{para}</p>{body}</div><footer style="height:400px;background:#222"></footer>{guide}{js}</body></html>')
for d, html in [('thai-massage', page), ('therapeutic-massage', '<!doctype html><html><body><div class="entry-content">other page</div></body></html>')]:
    os.makedirs(os.path.join(out, d), exist_ok=True)
    open(os.path.join(out, d, 'index.html'), 'w', encoding='utf-8').write(html)
print('mock written to', out)
