import re,time,base64,io
from playwright.sync_api import sync_playwright
from PIL import Image
SCRIPT='<script>try{var n=performance.getEntriesByType("navigation")[0],o=location.origin;if((n&&n.type==="reload")||!document.referrer||document.referrer.indexOf(o)!==0)document.documentElement.classList.add("ts-fade-in")}catch(e){}</script>'
CSS='<style id="ts-gate">@keyframes ts-fade-in{from{opacity:0}to{opacity:1}}html.ts-fade-in body{animation:ts-fade-in .4s cubic-bezier(0,0,.2,1) backwards}@media (prefers-reduced-motion:reduce){html.ts-fade-in body{animation:none}}</style>'
def run(p,label,action,flags=[],scheme='light'):
    br=p.chromium.launch(executable_path='/usr/bin/google-chrome-stable',headless=False,args=['--no-first-run','--window-size=1300,900']+flags)
    ctx=br.new_context(viewport={'width':1280,'height':760},color_scheme=scheme); pg=ctx.new_page()
    def h(route):
        if route.request.resource_type!='document': return route.continue_()
        r=route.fetch(); b=r.text(); b=re.sub(r'<script type="speculationrules">.*?</script>','',b,flags=re.S)
        b=b.replace('<head>','<head>'+SCRIPT,1).replace('</head>',CSS+'</head>',1); route.fulfill(response=r,body=b)
    pg.goto('http://localhost:8090/',wait_until='networkidle'); pg.wait_for_timeout(1200)
    first=pg.evaluate("document.documentElement.classList.contains('ts-fade-in')")
    cdp=ctx.new_cdp_session(pg); fr=[]; cdp.on('Page.screencastFrame',lambda e:(fr.append((time.time(),e['data'])),cdp.send('Page.screencastFrameAck',{'sessionId':e['sessionId']})))
    cdp.send('Page.startScreencast',{'format':'png','everyNthFrame':1}); pg.wait_for_timeout(250); t0=time.time()
    action(pg); pg.wait_for_load_state('load'); pg.wait_for_timeout(1500); cdp.send('Page.stopScreencast')
    cls=pg.evaluate("document.documentElement.classList.contains('ts-fade-in')"); lum=[];full=[]
    for t,d in fr:
        if t<t0: continue
        g=Image.open(io.BytesIO(base64.b64decode(d))).convert('L'); w,h2=g.size; hd=list(g.crop((0,0,w,int(88*h2/760))).resize((64,6)).getdata()); lum.append(sum(hd)/len(hd)); full.append(sum(g.resize((32,20)).getdata())/640)
    fin=lum[-1]; steps=sum(1 for i in range(1,len(full)) if 0<abs(full[i]-full[i-1])<=8)
    print(f'{label:40s} fade-class on new page={cls} | first load had class={first} | frames={len(lum)} | gradual whole-page steps={steps} | menu max change vs final={max(abs(l-fin) for l in lum):.1f}')
    br.close()
with sync_playwright() as p:
    run(p,'F5 reload (light)',lambda pg:pg.reload())
    run(p,'F5 reload (Chrome Auto Dark)',lambda pg:pg.reload(),['--enable-features=WebContentsForceDark'],'dark')
    run(p,'menu click Home->Home (same-page)',lambda pg:pg.click('.main-header-menu a:text-is("Home")'))
    run(p,'menu click Home->About',lambda pg:pg.click('.main-header-menu a:text-is("About")'))
