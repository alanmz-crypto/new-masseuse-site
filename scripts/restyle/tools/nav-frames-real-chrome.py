import time,base64,io
from playwright.sync_api import sync_playwright
from PIL import Image
INIT='addEventListener("pagereveal",e=>{try{sessionStorage.setItem("vt",e.viewTransition?"yes":"no")}catch(x){}});addEventListener("pageswap",e=>{try{sessionStorage.setItem("vtswap",e.viewTransition?"yes":"no")}catch(x){}})'
def meas(pg,ctx,action,label,cold=False):
    cdp=ctx.new_cdp_session(pg); fr=[]
    cdp.on('Page.screencastFrame',lambda e:(fr.append((time.time(),e['data'])),cdp.send('Page.screencastFrameAck',{'sessionId':e['sessionId']})))
    cdp.send('Page.startScreencast',{'format':'png','everyNthFrame':1}); pg.wait_for_timeout(250); t0=time.time(); action(); pg.wait_for_load_state('load'); pg.wait_for_timeout(1800); cdp.send('Page.stopScreencast')
    rows=[]
    for t,d in fr:
        if t<t0: continue
        px=list(Image.open(io.BytesIO(base64.b64decode(d))).convert('RGB').resize((32,20)).getdata()); n=len(px)
        rows.append((round(t-t0,2),round(sum(1 for r,g,b in px if min(r,g,b)>=252)/n*100),round(sum(sum(c) for c in px)/n/3)))
    vt=pg.evaluate("[sessionStorage.getItem('vtswap'),sessionStorage.getItem('vt')]"); b=[r[2] for r in rows]
    trans=sum(1 for i in range(1,len(b)) if 0<abs(b[i]-b[i-1])<=6 )  # small steps = gradual fade frames
    print(f'{label:34s} frames={len(rows):3d} pure-white={sum(1 for r in rows if r[1]>=97)} vt(swap,reveal)={vt} gradual-steps={trans} span={round(rows[-1][0]-rows[0][0],2) if rows else 0}s')
with sync_playwright() as p:
    br=p.chromium.launch(executable_path='/usr/bin/google-chrome-stable',headless=False,args=['--no-first-run','--window-size=1300,900'])
    ctx=br.new_context(viewport={'width':1280,'height':760}); ctx.add_init_script(INIT); pg=ctx.new_page()
    pg.goto('http://localhost:8090/',wait_until='networkidle'); pg.wait_for_timeout(1200)
    meas(pg,ctx,lambda:pg.click('a[href$="/about/"] >> nth=0'),'warm: home -> about')
    meas(pg,ctx,lambda:pg.click('a[href$="/services/"] >> nth=0'),'warm: about -> services')
    meas(pg,ctx,lambda:pg.click('a[href$="/contact/"] >> nth=0'),'warm: services -> contact')
    meas(pg,ctx,lambda:pg.go_back(),'warm: back button')
    meas(pg,ctx,lambda:pg.reload(),'warm: refresh')
    # cold + slow: cache disabled, 350ms latency, ~1 Mbps
    cdp=ctx.new_cdp_session(pg); cdp.send('Network.enable'); cdp.send('Network.setCacheDisabled',{'cacheDisabled':True}); cdp.send('Network.emulateNetworkConditions',{'offline':False,'latency':350,'downloadThroughput':1.0*1024*1024/8,'uploadThroughput':400*1024/8})
    meas(pg,ctx,lambda:pg.click('a[href$="/about/"] >> nth=0'),'COLD slow: -> about')
    meas(pg,ctx,lambda:pg.reload(),'COLD slow: refresh')
    br.close()
