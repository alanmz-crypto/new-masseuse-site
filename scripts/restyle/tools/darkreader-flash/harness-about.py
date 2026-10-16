"""Drive menu clicks and F5 in the throwaway browser; log the header's computed colours every frame and record the screen.
usage: ./env.sh up dr; python3 harness.py LABEL [--css FILE] [--strip-spec] [--cold] [--light] [--no-video]; python3 analyze.py LABEL
  --css injects a candidate Additional CSS via DevTools Fetch (use --strip-spec with it so prefetch cannot bypass the injection);
  --light emulates a light OS scheme (use with ./env.sh up plain, then blink.py LABEL); --cold idles 35 s so Dark Reader's service worker sleeps"""
import asyncio, base64, json, re, subprocess, sys, time, os
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__))); from cdp import CDP
SP = os.environ.get('TS_FLASH_WORK', '/tmp/ts-flash-work'); os.makedirs(SP, exist_ok=True); BASE = 'http://localhost:8090'
args = sys.argv[1:]; label = args[0]
css = open(args[args.index('--css') + 1]).read() if '--css' in args else None
strip_spec, cold, video = '--strip-spec' in args, '--cold' in args, '--no-video' not in args
TRACE = r"""(() => {
  const tr = window.__tsTrace = {nav: null, frames: [], events: []};
  try { tr.nav = performance.getEntriesByType('navigation')[0]?.type; } catch (e) {}
  let n = 0;
  const cs = el => el ? getComputedStyle(el) : null;
  function sample() {
    const bar = document.querySelector('#ast-desktop-header .ast-primary-header-bar');
    const links = document.querySelectorAll('#ast-desktop-header .main-header-menu .menu-link');
    const fb = document.querySelector('.darkreader--fallback');
    const b = cs(bar), l = cs(links[0]), cur = cs(document.querySelector('#ast-desktop-header .current-menu-item > .menu-link'));
    tr.frames.push([Math.round(performance.now()), b && b.backgroundColor, l && l.backgroundColor, l && l.color, cur && cur.color,
      fb ? fb.textContent.length : -1, document.querySelectorAll('.darkreader').length, document.readyState[0], links.length, document.fonts.status[0], [...document.fonts].filter(f => /Newsreader|Hanken/.test(f.family) && f.status === 'loaded').length]);
    if (++n < 120) requestAnimationFrame(sample);
  }
  requestAnimationFrame(sample);
  addEventListener('pagereveal', e => tr.events.push(['pagereveal', Math.round(performance.now()), !!e.viewTransition]));
  try { new PerformanceObserver(l => l.getEntries().forEach(en => tr.events.push([en.name, Math.round(en.startTime)]))).observe({type: 'paint', buffered: true}); } catch (e) {}
})();"""

async def main():
    c = await CDP.page()
    for d in ('Page', 'Runtime'): await c.call(d + '.enable')
    await c.call('Page.addScriptToEvaluateOnNewDocument', {'source': TRACE})
    if '--light' in args: await c.call('Emulation.setEmulatedMedia', {'features': [{'name': 'prefers-color-scheme', 'value': 'light'}]})
    if css is not None or strip_spec:
        async def paused(p):
            rid = p['requestId']
            try:
                if p.get('responseStatusCode') == 200:
                    b = await c.call('Fetch.getResponseBody', {'requestId': rid})
                    html = (base64.b64decode(b['body']) if b['base64Encoded'] else b['body'].encode()).decode('utf-8')
                    if css is not None:
                        html, k = re.subn(r'(<style id="wp-custom-css">)(.*?)(</style>)', lambda m: m.group(1) + css + m.group(3), html, flags=re.S)
                        assert k == 1, 'custom css block not found'
                    if strip_spec: html = re.sub(r'<script type="speculationrules">.*?</script>', '', html, flags=re.S)
                    hdr = [h for h in p['responseHeaders'] if h['name'].lower() not in ('content-length', 'content-encoding')]
                    await c.call('Fetch.fulfillRequest', {'requestId': rid, 'responseCode': 200, 'responseHeaders': hdr, 'body': base64.b64encode(html.encode()).decode()})
                    return
            except Exception as e: print('intercept error', e)
            await c.call('Fetch.continueRequest', {'requestId': rid})
        c.on('Fetch.requestPaused', paused)
        await c.call('Fetch.enable', {'patterns': [{'urlPattern': BASE + '/*', 'resourceType': 'Document', 'requestStage': 'Response'}]})
    # start clean on home, mouse parked away from the menu
    await c.call('Input.dispatchMouseEvent', {'type': 'mouseMoved', 'x': 650, 'y': 600})
    await c.call('Page.navigate', {'url': BASE + '/'}); await asyncio.sleep(3.5)
    env = await c.js("({dark: matchMedia('(prefers-color-scheme: dark)').matches, dr: document.querySelectorAll('.darkreader').length, vw: innerWidth, vh: innerHeight, bar: (r => [r.x, r.y, r.width, r.height])(document.querySelector('#ast-desktop-header .ast-primary-header-bar').getBoundingClientRect())})")
    print('env', json.dumps(env))
    rec = None
    if video:
        rec = subprocess.Popen(['ffmpeg', '-loglevel', 'error', '-f', 'x11grab', '-framerate', '60', '-video_size', '1300x900', '-i', ':77.0+0,0',
                                '-copyts', '-c:v', 'libx264', '-preset', 'ultrafast', '-qp', '0', '-y', f'{SP}/rec-{label}.mkv'], stdin=subprocess.PIPE)
        await asyncio.sleep(1.0)
    steps = [('click', 'About'), ('reload', None), ('reload', None), ('click', 'Home'), ('click', 'About'), ('click', 'Services'), ('click', 'About'), ('reload', None)]
    if cold: steps = [('idle', 35)] + [('click', 'About'), ('idle', 35), ('reload', None)]
    out = {'label': label, 'env': env, 'actions': []}
    for kind, arg in steps:
        if kind == 'idle': await asyncio.sleep(arg); continue
        t = time.time()
        if kind == 'click':
            xy = await c.js(f"(a => {{ const r = a.getBoundingClientRect(); return [r.x + r.width / 2, r.y + r.height / 2]; }})([...document.querySelectorAll('#ast-desktop-header .main-header-menu .menu-link')].find(a => a.textContent.trim() === '{arg}'))")
            await c.call('Input.dispatchMouseEvent', {'type': 'mouseMoved', 'x': xy[0], 'y': xy[1]}); await asyncio.sleep(0.25)
            t = time.time()
            await c.call('Input.dispatchMouseEvent', {'type': 'mousePressed', 'x': xy[0], 'y': xy[1], 'button': 'left', 'clickCount': 1})
            await c.call('Input.dispatchMouseEvent', {'type': 'mouseReleased', 'x': xy[0], 'y': xy[1], 'button': 'left', 'clickCount': 1})
        else:
            await c.call('Page.reload')
        await asyncio.sleep(2.6)
        tr = await c.js('JSON.stringify(window.__tsTrace || null)')
        mk = await c.js("(s => s ? (s.textContent.includes('TS HEADER FLASH FIX') ? 'after7+' : 'pre-fix') : 'none')(document.getElementById('wp-custom-css'))")
        out['actions'].append({'kind': kind, 'arg': arg, 't': t, 'css': mk, 'trace': json.loads(tr) if tr else None})
    if rec: await asyncio.sleep(0.5); rec.stdin.write(b'q'); rec.stdin.flush(); rec.wait(timeout=20)
    json.dump(out, open(f'{SP}/trace-{label}.json', 'w'))
    if css is not None or strip_spec: await c.call('Fetch.disable')
    await c.ws.close()

asyncio.run(main())
