"""Light-mode parity: dump computed styles of every #masthead element + header screenshots, optionally with a CSS variant injected.
usage: lightcheck.py LABEL [--css FILE]"""
import asyncio, base64, json, re, sys, os
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__))); from cdp import CDP
SP = os.environ.get('TS_FLASH_WORK', '/tmp/ts-flash-work'); os.makedirs(SP, exist_ok=True); BASE = 'http://localhost:8090'
args = sys.argv[1:]; label = args[0]; css = open(args[args.index('--css') + 1]).read() if '--css' in args else None
DUMP = r"""(() => { const out = {}; const props = ['background-color','background-image','color','border-top-color','border-bottom-color','border-left-color','border-right-color','text-decoration-line','text-decoration-color','text-decoration-thickness','text-underline-offset','font-family','font-size','font-weight','line-height','letter-spacing','outline-color','box-shadow','opacity','padding-top','padding-left','height','width'];
  document.querySelectorAll('#masthead, #masthead *').forEach((el, i) => { const s = getComputedStyle(el); const r = el.getBoundingClientRect();
    out[i + ':' + el.tagName + '.' + [...el.classList].join('.')] = props.map(p => p + '=' + s.getPropertyValue(p)).join('|') + '|rect=' + [r.x, r.y, r.width, r.height].map(Math.round).join(','); });
  return out; })()"""
async def main():
    c = await CDP.page()
    for d in ('Page', 'Runtime'): await c.call(d + '.enable')
    await c.call('Emulation.setEmulatedMedia', {'features': [{'name': 'prefers-color-scheme', 'value': 'light'}, {'name': 'prefers-reduced-motion', 'value': 'reduce'}]})
    if css is not None:
        async def paused(p):
            rid = p['requestId']
            if p.get('responseStatusCode') == 200:
                b = await c.call('Fetch.getResponseBody', {'requestId': rid})
                html = (base64.b64decode(b['body']) if b['base64Encoded'] else b['body'].encode()).decode()
                html, k = re.subn(r'(<style id="wp-custom-css">)(.*?)(</style>)', lambda m: m.group(1) + css + m.group(3), html, flags=re.S); assert k == 1
                html = re.sub(r'<script type="speculationrules">.*?</script>', '', html, flags=re.S)
                hdr = [h for h in p['responseHeaders'] if h['name'].lower() not in ('content-length', 'content-encoding')]
                await c.call('Fetch.fulfillRequest', {'requestId': rid, 'responseCode': 200, 'responseHeaders': hdr, 'body': base64.b64encode(html.encode()).decode()}); return
            await c.call('Fetch.continueRequest', {'requestId': rid})
        c.on('Fetch.requestPaused', paused)
        await c.call('Fetch.enable', {'patterns': [{'urlPattern': BASE + '/*', 'resourceType': 'Document', 'requestStage': 'Response'}]})
    res = {}
    async def shot(name, h=120, w=None):
        r = await c.call('Page.captureScreenshot', {'format': 'png', 'clip': {'x': 0, 'y': 0, 'width': w or 1284, 'height': h, 'scale': 1}})
        open(f'{SP}/lc-{label}-{name}.png', 'wb').write(base64.b64decode(r['data']))
    for width, mobile in ((1300, False), (390, True)):
        await c.call('Emulation.setDeviceMetricsOverride', {'width': width, 'height': 900, 'deviceScaleFactor': 1, 'mobile': mobile})
        for path in ('/', '/about/'):
            await c.call('Input.dispatchMouseEvent', {'type': 'mouseMoved', 'x': width - 5, 'y': 880})
            await c.call('Page.navigate', {'url': BASE + path}); await asyncio.sleep(2.5)
            await c.js("document.fonts.ready.then(() => 1)")
            key = f'{width}{path}'
            res[key] = await c.js(DUMP); await shot(key.replace('/', '_'), 120, min(width, 1284))
            if not mobile:  # hover a non-current link
                xy = await c.js("(a => { const r = a.getBoundingClientRect(); return [r.x + r.width / 2, r.y + r.height / 2]; })([...document.querySelectorAll('#ast-desktop-header .main-header-menu .menu-link')].find(a => a.textContent.trim() === 'Services'))")
                await c.call('Input.dispatchMouseEvent', {'type': 'mouseMoved', 'x': xy[0], 'y': xy[1]}); await asyncio.sleep(0.5)
                res[key + ':hover'] = await c.js(DUMP); await shot(key.replace('/', '_') + '-hover', 120)
            else:  # open the mobile menu
                await c.js("document.querySelector('#ast-mobile-header .menu-toggle').click()"); await asyncio.sleep(0.8)
                res[key + ':menu-open'] = await c.js(DUMP); await shot(key.replace('/', '_') + '-open', 520, width)
    json.dump(res, open(f'{SP}/lc-{label}.json', 'w'))
    print(label, {k: len(v) for k, v in res.items()})
    if css is not None: await c.call('Fetch.disable')
    await c.call('Emulation.clearDeviceMetricsOverride'); await c.ws.close()
asyncio.run(main())
