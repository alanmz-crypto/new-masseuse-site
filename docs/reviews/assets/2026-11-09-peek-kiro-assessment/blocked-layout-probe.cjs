const fs=require('fs');
const {chromium,devices}=require('/opt/codex/runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const dir='/tmp/peek-kiro-review';fs.mkdirSync(dir,{recursive:true});
const result={scope:'Read-only actual staging39; Chromium emulation, not physical devices',cases:[]};let browser;
const wait=ms=>new Promise(r=>setTimeout(r,ms));
const rect=e=>{const r=e.getBoundingClientRect();return{x:r.x,y:r.y,left:r.left,right:r.right,top:r.top,bottom:r.bottom,width:r.width,height:r.height}};
async function open(path,phone=true){const ctx=await browser.newContext({...phone?devices['iPhone 13']:{viewport:{width:1280,height:800}},deviceScaleFactor:1,reducedMotion:'no-preference'});await ctx.addInitScript(()=>{localStorage.setItem('ts-pk-tip','seen');localStorage.setItem('ts-pk-side','right')});const p=await ctx.newPage();const response=await p.goto('https://staging39.thaisolitude.com'+path,{waitUntil:'load',timeout:45000});if(response?.status()!==200||/Robot Challenge/i.test(await p.title()))throw Error('INVALID access '+path+' '+response?.status());await p.waitForSelector('.ts-pk-nav');await p.evaluate(()=>document.fonts.ready);await p.waitForTimeout(350);return{ctx,p};}
function save(row){result.cases.push(row);fs.writeFileSync(dir+'/layout-results.json',JSON.stringify(result,null,2));console.log(JSON.stringify(row));}
(async()=>{browser=await chromium.launch({executablePath:'/usr/bin/chromium'});
 for(const path of ['/privacy-policy/','/about/']){
  const {ctx,p}=await open(path),hits=[];
  for(const side of ['right','left']){
   if(side==='left'){await p.locator('.ts-pk-rail li:first-child > *').click();await p.locator('.ts-pk-card .side').click();await p.waitForTimeout(300);}
   const controls=p.locator('.uagb-faq-questions-button'),count=await controls.count();
   for(let index=0;index<count;index++){
    await controls.nth(index).evaluate(e=>{const r=e.getBoundingClientRect(),n=document.querySelector('.ts-pk-rail').getBoundingClientRect();scrollTo({top:scrollY+r.top+r.height/2-(n.top+n.height/2),behavior:'instant'})});await p.waitForTimeout(100);
    const hit=await controls.nth(index).evaluate((e,side)=>{const r=e.getBoundingClientRect(),n=document.querySelector('.ts-pk-nav').getBoundingClientRect(),y=r.top+r.height/2,x=side==='right'?r.right-2:r.left+2,h=document.elementFromPoint(x,y);return{title:e.innerText,side,button:{left:r.left,right:r.right,top:r.top,bottom:r.bottom},nav:{left:n.left,right:n.right,top:n.top,bottom:n.bottom},sample:{x,y},ownHit:e===h||e.contains(h),hit:h&&{tag:h.tagName,class:h.className},inViewport:y>0&&y<innerHeight}},side);
    hits.push(hit);
   }
   await p.screenshot({path:dir+'/'+path.replaceAll('/','')+'-'+side+'-hit.png'});
  }
  save({path,hits});await ctx.close();await wait(5500);
 }
 {
  const {ctx,p}=await open('/services/',false);
  const text=await p.evaluate(()=>{const nav=document.querySelector('.ts-pk-nav').getBoundingClientRect(),content=document.querySelector('.entry-content'),walker=document.createTreeWalker(content,NodeFilter.SHOW_TEXT),out=[];let node;while(node=walker.nextNode()){if(!node.textContent.trim()||node.parentElement.closest('.ts-pk,.ts-jump,script,style,nav'))continue;const r=document.createRange();r.selectNodeContents(node);for(const box of r.getClientRects()){if(box.width>0&&box.height>0&&box.right>nav.left-6)out.push({text:node.textContent.trim().slice(0,160),tag:node.parentElement.tagName,class:node.parentElement.className,left:box.left,right:box.right,top:box.top,bottom:box.bottom,overflowVsRail:box.right-nav.left});}}return{viewport:{width:innerWidth,height:innerHeight},nav:{left:nav.left,right:nav.right,top:nav.top,bottom:nav.bottom},clear:getComputedStyle(document.documentElement).getPropertyValue('--pk-clear'),hits:out};});
  for(const row of text.hits.slice(0,8)){await p.evaluate(y=>scrollTo({top:y,behavior:'instant'}),Math.max(0,row.top-400));await p.waitForTimeout(100);}
  await p.screenshot({path:dir+'/services-1280-text.png'});save({path:'/services/',...text});await ctx.close();await wait(5500);
 }
 {
  const {ctx,p}=await open('/therapeutic-massage/');const samples=[];
  for(const spec of [{name:'portrait-top',w:390,h:664,y:0},{name:'landscape-top',w:844,h:342,y:0},{name:'landscape-scrolled',w:844,h:342,y:600},{name:'portrait-150percent',w:390,h:664,y:0,scale:true}]){
   await p.keyboard.press('Escape');await p.setViewportSize({width:spec.w,height:spec.h});await p.evaluate(({y,scale})=>{document.documentElement.style.fontSize=scale?'150%':'';scrollTo({top:y,behavior:'instant'})},spec);await p.waitForTimeout(250);
   await p.locator('.ts-pk-rail li:last-child > *').click();await p.waitForTimeout(300);
   const m=await p.evaluate(()=>{const card=document.querySelector('.ts-pk-card'),sum=card.querySelector('.sum'),strip=document.querySelector('.ts-pk-top'),rect=e=>{const r=e.getBoundingClientRect();return{left:r.left,right:r.right,top:r.top,bottom:r.bottom,width:r.width,height:r.height}};return{viewport:{width:innerWidth,height:innerHeight},y:scrollY,card:rect(card),summary:rect(sum),scrollHeight:sum.scrollHeight,clientHeight:sum.clientHeight,lineHeight:getComputedStyle(sum).lineHeight,fit:card.className,strip:rect(strip)}});
   samples.push({...spec,...m});await p.screenshot({path:dir+'/therapeutic-'+spec.name+'.png'});
  }
  save({path:'/therapeutic-massage/',samples});await ctx.close();
 }
})().catch(e=>{result.error=e.stack;fs.writeFileSync(dir+'/layout-results.json',JSON.stringify(result,null,2));console.error(e.stack);process.exitCode=2}).finally(async()=>{if(browser)await browser.close()});
