import time,base64,io
from playwright.sync_api import sync_playwright
from PIL import Image
INIT='addEventListener("pagereveal",e=>{try{sessionStorage.setItem("vt",e.viewTransition?"yes":"no")}catch(x){}})'
def run(p,label,scheme,flags,save):
    br=p.chromium.launch(executable_path='/usr/bin/google-chrome-stable',headless=False,args=['--no-first-run','--window-size=1300,900']+flags)
    ctx=br.new_context(viewport={'width':1280,'height':760},color_scheme=scheme); ctx.add_init_script(INIT); pg=ctx.new_page()
    pg.goto('http://localhost:8090/',wait_until='networkidle'); pg.wait_for_timeout(1200)
    if save: pg.screenshot(path=f'/tmp/claude-1000/dark-{save}-static.png')
    cdp=ctx.new_cdp_session(pg); fr=[]
    cdp.on('Page.screencastFrame',lambda e:(fr.append((time.time(),e['data'])),cdp.send('Page.screencastFrameAck',{'sessionId':e['sessionId']})))
    cdp.send('Page.startScreencast',{'format':'png','everyNthFrame':1}); pg.wait_for_timeout(250); t0=time.time()
    pg.click('a[href$="/about/"] >> nth=0'); pg.wait_for_load_state('load'); pg.wait_for_timeout(1500); cdp.send('Page.stopScreencast')
    rows=[];imgs=[]
    for t,d in fr:
        if t<t0: continue
        im=Image.open(io.BytesIO(base64.b64decode(d))).convert('RGB'); imgs.append(im); px=list(im.resize((32,20)).getdata()); n=len(px)
        rows.append((round(t-t0,2),round(sum(1 for r,g,b in px if min(r,g,b)>=252)/n*100),round(sum(sum(c) for c in px)/n/3)))
    print(label,'| prefers-dark:',pg.evaluate("matchMedia('(prefers-color-scheme: dark)').matches"),'| VT:',pg.evaluate("sessionStorage.getItem('vt')"),'| frames',len(rows),'| brightness min/max',min(r[2] for r in rows),max(r[2] for r in rows),'| pure-white frames',sum(1 for r in rows if r[1]>=97))
    if save:
        k=[0,len(imgs)//3,2*len(imgs)//3,len(imgs)-1]
        W=Image.new('RGB',(4*640,380),'#f0f'); 
        for i,ix in enumerate(k): W.paste(imgs[ix].resize((640,380)),(i*640,0))
        W.save(f'/tmp/claude-1000/dark-{save}-frames.png')
    br.close()
with sync_playwright() as p:
    run(p,'light scheme (what I tested before)','light',[],None)
    run(p,'dark scheme (prefers-color-scheme: dark)','dark',[],'scheme')
    run(p,'dark scheme + Chrome Auto Dark Mode','dark',['--enable-features=WebContentsForceDark'],'force')
