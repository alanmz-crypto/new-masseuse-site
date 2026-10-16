import re,time,base64,io
from playwright.sync_api import sync_playwright
from PIL import Image
INIT='addEventListener("pagereveal",e=>{try{sessionStorage.setItem("vt",e.viewTransition?"yes":"no")}catch(x){}})'
BASE='@view-transition{navigation:auto}html{background-color:#F5F4EF}'
V={
 'A current (custom easing/delay)':open('scripts/restyle/ts-transition-addendum.css').read(),
 'B default UA cross-fade 0.3s':BASE+'::view-transition-old(root),::view-transition-new(root){animation-duration:.3s}',
 'D A without custom keyframes, 0.35s ease':BASE+'::view-transition-old(root),::view-transition-new(root){animation-duration:.35s;animation-timing-function:ease-in-out}',
 'E B + header/footer own groups (default fade)':BASE+'::view-transition-old(root),::view-transition-new(root){animation-duration:.3s}.site-header{view-transition-name:ts-header}.site-footer{view-transition-name:ts-footer}::view-transition-group(ts-header),::view-transition-group(ts-footer){animation-duration:.3s}::view-transition-old(ts-header),::view-transition-new(ts-header),::view-transition-old(ts-footer),::view-transition-new(ts-footer){animation-duration:.3s}',
 'F header/footer pinned (old kept, never faded)':BASE+'::view-transition-old(root),::view-transition-new(root){animation-duration:.3s}.site-header{view-transition-name:ts-header}.site-footer{view-transition-name:ts-footer}::view-transition-old(ts-header),::view-transition-old(ts-footer){animation:none;opacity:1}::view-transition-new(ts-header),::view-transition-new(ts-footer){animation:none;opacity:0}',
}
def trial(p,css,nav):
    br=p.chromium.launch(executable_path='/usr/bin/google-chrome-stable',headless=False,args=['--no-first-run','--window-size=1300,900'])
    ctx=br.new_context(viewport={'width':1280,'height':760}); ctx.add_init_script(INIT); pg=ctx.new_page()
    def h(route):
        if route.request.resource_type!='document': return route.continue_()
        r=route.fetch(); b=r.text()
        b=re.sub(r'<script type="speculationrules">.*?</script>','',b,flags=re.S)
        b=re.sub(r'(<style id="wp-custom-css">.*?)/\* TS TRANSITION START.*?/\* TS TRANSITION END \*/',r'\1',b,flags=re.S)
        b=b.replace('</head>','<style>'+css+'</style></head>',1)
        route.fulfill(response=r,body=b)
    pg.route('http://localhost:8090/**',h)
    pg.goto('http://localhost:8090/',wait_until='networkidle'); pg.wait_for_timeout(1000)
    cdp=ctx.new_cdp_session(pg); fr=[]
    cdp.on('Page.screencastFrame',lambda e:(fr.append((time.time(),e['data'])),cdp.send('Page.screencastFrameAck',{'sessionId':e['sessionId']})))
    cdp.send('Page.startScreencast',{'format':'png','everyNthFrame':1}); pg.wait_for_timeout(250); t0=time.time()
    pg.click('.main-header-menu a:text-is("Home")'); pg.wait_for_load_state('load'); pg.wait_for_timeout(1500); cdp.send('Page.stopScreencast')
    vt=pg.evaluate("sessionStorage.getItem('vt')"); br.close()
    lum=[]
    for t,d in fr:
        im=Image.open(io.BytesIO(base64.b64decode(d))).convert('L'); w,h2=im.size; s=h2/760
        hd=im.crop((0,0,w,int(88*s))).resize((64,6)); px=list(hd.getdata()); lum.append((t-t0,sum(px)/len(px)))
    lum=[l for l in lum if l[0]>0]; fin=lum[-1][1]; start=lum[0][1]
    dev=max(abs(l[1]-fin) for l in lum); mid=min(l[1] for l in lum)
    return vt,len(lum),round(dev,1),round(min(l[1] for l in lum),1),round(max(l[1] for l in lum),1)
with sync_playwright() as p:
    for k,css in V.items():
        r=[trial(p,css,'SAME'),trial(p,css,'SAME')]
        print(f'{k:34s} reveal-VT={[x[0] for x in r]} header max deviation from final (0-255 grey): {[x[2] for x in r]}  min/max {[(x[3],x[4]) for x in r]}')
