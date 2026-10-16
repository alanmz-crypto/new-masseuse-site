import sys,json
from playwright.sync_api import sync_playwright
tag=sys.argv[1]; pages=sys.argv[2].split(','); shots=len(sys.argv)>3
B='http://localhost:8090'; P={'home':'/','about':'/about/','services':'/services/','work-with-me':'/work-with-me/','contact':'/contact/'}
res={}
with sync_playwright() as p:
    b=p.chromium.launch(executable_path='/usr/bin/chromium',args=['--no-sandbox'])
    for n in pages:
        for lab,w,h in (('desktop',1440,900),('mobile',390,844)):
            if lab=='mobile' and not shots and False: pass
            ctx=b.new_context(viewport={'width':w,'height':h}); pg=ctx.new_page(); errs=[]
            pg.on('console',lambda m:errs.append(m.text) if m.type in('error','warning') else None)
            pg.on('pageerror',lambda e:errs.append(str(e)))
            pg.goto(B+P[n],wait_until='networkidle',timeout=60000)
            H=pg.evaluate('document.documentElement.scrollHeight'); y=0
            while y<H: pg.evaluate(f'window.scrollTo(0,{y})'); pg.wait_for_timeout(200); y+=h//2
            pg.evaluate('window.scrollTo(0,0)'); pg.evaluate('document.fonts.ready'); pg.wait_for_load_state('networkidle')
            pg.wait_for_function('Array.from(document.images).every(i=>i.complete)',timeout=30000); pg.wait_for_timeout(800)
            if lab=='desktop':
                t=pg.evaluate("document.body.textContent.replace(/\\s+/g,' ').trim()"); res[n]={'text':t,'errs':errs}
                res[n]['css_first']=pg.evaluate("(document.getElementById('wp-custom-css')||{}).textContent||''")[:60]
                res[n]['marker']=pg.evaluate("((document.getElementById('wp-custom-css')||{}).textContent||'').split('TS RESTYLE START').length-1")
                res[n]['fonts']=pg.evaluate("[...document.fonts].filter(f=>f.status=='loaded').map(f=>f.family).filter((v,i,a)=>a.indexOf(v)==i)")
            else: res[n]['errs']+=errs
            if shots: pg.screenshot(path=f'scripts/restyle/shots-local/{n}-{lab}-{tag}.png',full_page=True)
            ctx.close()
    b.close()
json.dump(res,open(f'scripts/restyle/check-{tag}.json','w'))
for n,r in res.items(): print(n,len(r['text']),'errs',len(r['errs']),'marker',r['marker'],repr(r['css_first'][:30]),r['fonts'])
