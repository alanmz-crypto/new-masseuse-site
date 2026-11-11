const fs = require('fs'), crypto = require('crypto');
const { chromium, devices } = require('/opt/codex/runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const dir = '/tmp/peek-kiro-review'; fs.mkdirSync(dir, { recursive: true });
const core = fs.readFileSync('/workspace/new-masseuse-site/scripts/work-with-me-employer/link-ideas/peek-nav.php', 'utf8');
const sourceJS = core.match(/<script id="ts-peek-nav-runtime">([\s\S]*?)<\/script>/)[1].trim();
const results = { scope: 'Read-only actual staging39 Chromium review; emulated devices, not physical devices', sourceSHA: crypto.createHash('sha256').update(core).digest('hex'), cases: [] };
const pause = ms => new Promise(resolve => setTimeout(resolve, ms));
let browser;
async function state(p) { return p.evaluate(() => {
  const target = document.getElementById('ts-client-privacy'), strip = document.getElementById('ts-peek-title-strip');
  const r = target?.getBoundingClientRect();
  return { y: scrollY, hash: location.hash, target: target && { tag: target.tagName, top: r.top, bottom: r.bottom }, stripBottom: strip?.getBoundingClientRect().bottom,
    focus: { tag: document.activeElement.tagName, id: document.activeElement.id, text: document.activeElement.innerText?.slice(0,100) },
    panels: [...document.querySelectorAll('.uagb-faq-item')].map(e => ({ active: e.classList.contains('uagb-faq-item-active'), title: e.querySelector('.uagb-faq-questions-button')?.innerText, expanded: e.querySelector('.uagb-faq-questions-button')?.getAttribute('aria-expanded'), height: e.getBoundingClientRect().height })) };
}); }
async function tap(p, selector) {
  const xy = await p.locator(selector).evaluate(e => { const r = e.getBoundingClientRect(), x = r.left+r.width/2, y = r.top+r.height/2; if (!e.contains(document.elementFromPoint(x,y))) throw Error('Input target is obstructed: '+selector); return { x,y }; });
  const cdp = await p.context().newCDPSession(p);
  await cdp.send('Input.dispatchTouchEvent', { type:'touchStart', touchPoints:[{...xy,id:1,radiusX:3,radiusY:3,force:1}] });
  await p.waitForTimeout(35);
  await cdp.send('Input.dispatchTouchEvent', { type:'touchEnd', touchPoints:[] }); await cdp.detach();
}
(async () => {
 browser = await chromium.launch({ executablePath:'/usr/bin/chromium' });
 for (const spec of [
  { name:'privacy-touch-normal', mode:'touch', motion:'no-preference', phone:true },
  { name:'privacy-mouse-normal', mode:'mouse', motion:'no-preference', phone:true },
  { name:'privacy-touch-reduced', mode:'touch', motion:'reduce', phone:true },
  { name:'privacy-keyboard-normal', mode:'keyboard', motion:'no-preference', phone:false }
 ]) {
  const ctx = await browser.newContext({ ...(spec.phone ? devices['iPhone 13'] : { viewport:{width:1280,height:800} }), deviceScaleFactor:1, reducedMotion:spec.motion });
  await ctx.addInitScript(() => { localStorage.setItem('ts-pk-tip','seen'); localStorage.setItem('ts-pk-side','right'); window._reviewEvents=[]; window._reviewTrace=false;
   for(const type of ['pointerdown','pointerup','touchend','mousedown','mouseup','click','keydown','keypress','keyup']) window.addEventListener(type,e => { if(window._reviewTrace) window._reviewEvents.push({ type, at:performance.now(), tag:e.target.tagName, id:e.target.id, key:e.key, trusted:e.isTrusted, prevented:e.defaultPrevented, touch:e.sourceCapabilities?.firesTouchEvents }); },true);
  });
  const p = await ctx.newPage(), errors=[]; p.on('pageerror',e=>errors.push(e.message));
  const response=await p.goto('https://staging39.thaisolitude.com/privacy-policy/',{waitUntil:'load',timeout:45000});
  const status=response?.status();
  if(status!==200 || /Robot Challenge/i.test(await p.title())) throw Error('ACCESS BLOCKED/INVALID '+spec.name+' HTTP '+status+'; no further site requests');
  await p.waitForSelector('.ts-pk-nav'); await p.evaluate(()=>document.fonts.ready); await p.waitForTimeout(350);
  const runtimeMatches=await p.locator('#ts-peek-nav-runtime').textContent().then(s=>s.trim()===sourceJS);
  const before=await state(p), control='.ts-pk-rail [href="#ts-client-privacy"]';
  if (!before.panels[0]?.active || before.panels[1]?.active) throw Error('INVALID initial accordion state '+JSON.stringify(before.panels));
  if(spec.mode==='keyboard') { await p.locator(control).focus(); await p.keyboard.press('Enter'); }
  else if(spec.mode==='touch') await tap(p,control); else await p.locator(control).click();
  await p.waitForTimeout(350); const preview=await state(p);
  await p.evaluate(()=>{window._reviewTrace=true;window._reviewEvents=[]});
  if(spec.mode==='keyboard') { await p.locator('.ts-pk-card').focus(); await p.keyboard.press('Enter'); }
  else if(spec.mode==='touch') await tap(p,'.ts-pk-card .open > b'); else await p.locator('.ts-pk-card .open > b').click();
  await p.waitForTimeout(1400); const after=await state(p), events=await p.evaluate(()=>window._reviewEvents);
  await p.screenshot({path:dir+'/'+spec.name+'.png'});
  const row={...spec,status,runtimeMatches,before,preview,after,events,errors}; results.cases.push(row); fs.writeFileSync(dir+'/results.json',JSON.stringify(results,null,2));
  console.log(JSON.stringify({name:spec.name,status,runtimeMatches,before:before.panels,after,errors}));
  await ctx.close(); await pause(5500);
 }
})().catch(e=>{results.error=e.stack;fs.writeFileSync(dir+'/results.json',JSON.stringify(results,null,2));console.error(e.stack);process.exitCode=2}).finally(async()=>{if(browser)await browser.close()});
