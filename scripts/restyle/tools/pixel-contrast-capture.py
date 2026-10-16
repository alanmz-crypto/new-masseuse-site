import json
from playwright.sync_api import sync_playwright
JS='''()=>{const out=[];const w=document.createTreeWalker(document.body,NodeFilter.SHOW_TEXT);
while(w.nextNode()){const n=w.currentNode;if(!n.textContent.trim())continue;const el=n.parentElement;if(el.closest('script,style,noscript'))continue;
const s=getComputedStyle(el);if(s.visibility=='hidden'||s.display=='none'||parseFloat(s.opacity)==0)continue;
const r=document.createRange();r.selectNodeContents(n);for(const q of r.getClientRects()){if(q.width<2||q.height<2)continue;
out.push({t:n.textContent.trim().slice(0,30),x:q.left+scrollX,y:q.top+scrollY,w:q.width,h:q.height,c:s.color,fs:parseFloat(s.fontSize),fw:parseInt(s.fontWeight),tag:el.tagName+'.'+String(el.className).slice(0,25)});}}return out}'''
with sync_playwright() as p:
    b=p.chromium.launch(executable_path='/usr/bin/chromium',args=['--no-sandbox'])
    for lab,w in (('desktop',1440),('mobile',390)):
        pg=b.new_page(viewport={'width':w,'height':900}); pg.goto('http://localhost:8090/',wait_until='networkidle'); pg.wait_for_timeout(800)
        H=pg.evaluate('document.documentElement.scrollHeight')
        for y in range(0,H,450): pg.evaluate(f'scrollTo(0,{y})'); pg.wait_for_timeout(120)
        pg.evaluate('scrollTo(0,0)'); pg.wait_for_timeout(500)
        items=pg.evaluate(JS)
        pg.add_style_tag(content='*,*::before,*::after{color:transparent!important;-webkit-text-fill-color:transparent!important;text-shadow:none!important;caret-color:transparent!important}a{text-decoration-color:transparent!important}')
        pg.wait_for_timeout(400); pg.screenshot(path=f'/tmp/claude-1000/notext-{lab}.png',full_page=True)
        json.dump(items,open(f'/tmp/claude-1000/items-{lab}.json','w')); print(lab,len(items),'text rects')
    b.close()
