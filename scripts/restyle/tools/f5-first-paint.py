import re
from playwright.sync_api import sync_playwright
def trial(p,variant):
    br=p.chromium.launch(executable_path='/usr/bin/google-chrome-stable',headless=False,args=['--no-first-run','--window-size=1300,900'])
    ctx=br.new_context(viewport={'width':1280,'height':760}); pg=ctx.new_page()
    def h(route):
        if route.request.resource_type!='document': return route.continue_()
        r=route.fetch(); b=r.text()
        if variant=='no-import': b=re.sub(r'@import url\([^)]*\);','',b)
        route.fulfill(response=r,body=b)
    pg.route('http://localhost:8090/**',h)
    pg.goto('http://localhost:8090/',wait_until='networkidle'); pg.wait_for_timeout(1500)
    out=[]
    for i in range(4):
        pg.reload(); pg.wait_for_load_state('load'); pg.wait_for_timeout(700)
        m=pg.evaluate('''()=>{const n=performance.getEntriesByType("navigation")[0];const fp=performance.getEntriesByName("first-paint")[0];const f=performance.getEntriesByType("resource").filter(e=>/fonts\\.googleapis/.test(e.name))[0];
          return {ttfb:n.responseStart,fp:fp?fp.startTime:-1,gfont:f?{start:f.startTime,end:f.responseEnd,size:f.transferSize}:null}}''')
        out.append(m)
    br.close(); return out
with sync_playwright() as p:
    for v in ('with @import (current)','no-import'):
        o=trial(p,'no-import' if v=='no-import' else 'cur')
        print(f'{v:24s} first-paint ms per F5:',[round(x["fp"]) for x in o],'| TTFB:',[round(x["ttfb"]) for x in o],'| gfonts css request:',[ (round(x["gfont"]["start"]),round(x["gfont"]["end"]),x["gfont"]["size"]) if x["gfont"] else None for x in o][:2])
