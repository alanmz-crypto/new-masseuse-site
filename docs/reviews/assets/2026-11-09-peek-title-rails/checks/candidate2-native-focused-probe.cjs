const fs = require('fs'), crypto = require('crypto');
const {chromium,devices}=require('/opt/codex/runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const base='/workspace/new-masseuse-site/scripts/work-with-me-employer/link-ideas/';
const cores={candidate1:fs.readFileSync('/tmp/peek-title-rail-frozen-33f8ad64/link-ideas/peek-nav.php','utf8'),candidate2:fs.readFileSync(base+'peek-nav.php','utf8')};
const parts=core=>({css:core.match(/<style id="ts-peek-nav-css">[\s\S]*?<\/style>/)[0],js:core.match(/<script id="ts-peek-nav-runtime">[\s\S]*?<\/script>/)[0]});
const paragraph='<p>This is source text for a section. The rail may show its heading but must never extract this paragraph into the preview.</p>'.repeat(7);
const html=(body,mode,core)=>{const{css,js}=parts(core);return '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><style>body{margin:0;font-family:"DejaVu Sans",sans-serif}#masthead{height:85px;background:white}.entry-content{padding:0 20px}h2{font-size:1.5rem}p{line-height:1.6}</style>'+css+'</head><body><header id="masthead">Header</header><main><div class="entry-content">'+body+'</div></main><footer>Original footer</footer><script id="ts-peek-nav-config" type="application/json">'+JSON.stringify({title:'Complete original page title',mode})+'</script>'+js+'</body></html>';};
let pass=0,fail=0;const ok=(name,yes,data)=>{yes?pass++:fail++;console.log(JSON.stringify({result:yes?'PASS':'FAIL',name,data}));};
const state=p=>p.evaluate(()=>{const c=document.querySelector('.ts-pk-card'),h=document.querySelector('[data-original]'),para=[...document.querySelectorAll('.entry-content p')].find(e=>!e.closest('.ts-pk')),range=document.createRange();if(para)range.selectNodeContents(para);return{y:scrollY,x:scrollX,max:Math.max(0,document.documentElement.scrollHeight-innerHeight),hash:location.hash,history:history.length,left:document.documentElement.classList.contains('ts-pk-left'),pinned:c.classList.contains('is-pinned'),open:c.querySelector('.open>b')?.textContent,summary:c.querySelectorAll('.sum').length,index:c.dataset.i,focus:document.activeElement.tagName,targetFocused:document.activeElement===((h&&h.querySelector('h1,h2,h3,h4,h5,h6,.ts-faq-disclosure'))||h),targetId:h?.id,targetTop:h?.getBoundingClientRect().top,stripBottom:document.querySelector('.ts-pk-top').getBoundingClientRect().bottom,paragraphLines:para?[...range.getClientRects()].length:0};});
const steady=(a,b)=>a.y===b.y&&a.x===b.x&&a.hash===b.hash&&a.history===b.history;
(async()=>{
 console.log(JSON.stringify({wholeFileSHA:crypto.createHash('sha256').update(cores.candidate2).digest('hex'),scope:'local structural fixtures, native Chromium touch input; no physical-device claim'}));
 const browser=await chromium.launch({executablePath:'/usr/bin/chromium'});
 async function pageFor(body,side='right'){
  const ctx=await browser.newContext({...devices['iPhone 13'],viewport:{width:390,height:844},deviceScaleFactor:1,reducedMotion:'reduce'});
  await ctx.addInitScript(side=>{localStorage.setItem('ts-pk-tip','seen');localStorage.setItem('ts-pk-side',side)},side);
  await ctx.route('**/*',r=>r.fulfill({status:200,contentType:'text/html; charset=utf-8',body}));
  const p=await ctx.newPage(),errors=[];p.on('pageerror',e=>errors.push(e.message));await p.goto('http://localhost:8091/native-candidate2/');await p.waitForSelector('.ts-pk-nav');await p.evaluate(()=>document.fonts.ready);
  const cdp=await ctx.newCDPSession(p);
  const touch=(type,pt)=>cdp.send('Input.dispatchTouchEvent',{type,touchPoints:type==='touchEnd'?[]:[{...pt,radiusX:3,radiusY:3,force:1,id:1}]});
  const point=sel=>p.locator(sel).first().evaluate(e=>{const r=e.getBoundingClientRect(),x=r.left+r.width/2,y=r.top+r.height/2;if(!e.contains(document.elementFromPoint(x,y)))throw Error('INVALID hit target');return{x,y}});
  const tap=async sel=>{await touch('touchStart',await point(sel));await p.waitForTimeout(35);await touch('touchEnd');await p.waitForTimeout(160);};
  const swipe=async sel=>{const from=await point(sel),samples=[];await touch('touchStart',from);for(let n=1;n<=8;n++){await touch('touchMove',{x:from.x,y:from.y-72*n/8});await p.waitForTimeout(16);samples.push(await state(p));}await touch('touchEnd');await p.waitForTimeout(130);samples.push(await state(p));return samples;};
  return{ctx,p,errors,tap,swipe};
 }
 for(const side of ['right','left']){
  const run=await pageFor(html('<section><h2 data-original>One existing heading</h2>'+paragraph+'</section>','sections',cores.candidate2),side),{p,tap,swipe}=run;
  await p.evaluate(()=>scrollTo({top:document.documentElement.scrollHeight-innerHeight,behavior:'instant'}));await p.waitForTimeout(100);let before=await state(p);
  await tap('.ts-pk-ix');await tap('.ts-pk-fan li');let after=await state(p);
  ok(side+' DejaVu bottom index/name opens without moving page',steady(before,after)&&after.pinned&&after.max>=190&&after.paragraphLines===4,{before,after});
  for(const surface of ['.ts-pk-card .open>b','.ts-pk-card .side','.ts-pk-card .x','.ts-pk-card .hint>span']){
   before=await state(p);const samples=await swipe(surface);after=samples.at(-1);
   ok(side+' native '+surface+' swipe contains every sampled page position',samples.every(s=>steady(before,s)&&s.left===before.left&&s.pinned&&s.open===before.open),{y:before.y,range:[Math.min(...samples.map(s=>s.y)),Math.max(...samples.map(s=>s.y))],after});
  }
  before=await state(p);await tap('.ts-pk-card .side');after=await state(p);ok(side+' native side tap preserves near-bottom reading position',steady(before,after)&&after.left!==before.left&&!after.pinned&&after.paragraphLines===4,{before,after});
  await tap('.ts-pk-rail li:first-child [data-ts-pk-section]');before=await state(p);await tap('.ts-pk-card .hint>span');after=await state(p);ok(side+' native IDless jump keeps original focus and address',after.targetFocused&&after.targetId===''&&after.hash===before.hash&&!after.pinned&&after.targetTop>=after.stripBottom-2,{before,after});
  ok(side+' no JavaScript errors',run.errors.length===0,run.errors);await run.ctx.close();
 }
 for(const spec of [{name:'zero-headings',mode:'sections',body:'<nav>Skipped navigation</nav><div hidden>Skipped hidden block</div><p data-original>First original reading paragraph.</p>'+paragraph},{name:'fixed-search',mode:'fixed',body:'<nav>Skipped navigation</nav><div hidden>Skipped hidden block</div><section data-original><h2>A search result</h2>'+paragraph+'</section><section><h2>Another result</h2>'+paragraph+'</section>'}]){
  const run=await pageFor(html(spec.body,spec.mode,cores.candidate2)),{p,tap}=run;await p.evaluate(()=>scrollTo(0,document.documentElement.scrollHeight));await p.waitForTimeout(100);await tap('.ts-pk-rail li:first-child [data-ts-pk-section]');const before=await state(p);await tap('.ts-pk-card .hint>span');const after=await state(p);
  ok(spec.name+' native fallback lands at first original child and focuses its heading or itself',after.targetFocused&&after.targetId===''&&after.hash===before.hash&&after.targetTop>=after.stripBottom-2&&after.targetTop<=after.stripBottom+20&&!after.pinned,{before,after});ok(spec.name+' no JavaScript errors',run.errors.length===0,run.errors);await run.ctx.close();
 }
 for(const slug of ['thai-massage','therapeutic-massage','deep-tissue-massage','chair-massage']){
  const baseline=fs.readFileSync('/tmp/peek-title-rail-manifest-fixtures/'+slug+'/index.html','utf8'),results={};
  for(const version of ['candidate1','candidate2']){
   const {css,js}=parts(cores[version]),body=baseline.replace(/<style id="ts-peek-nav-css">[\s\S]*?<\/style>/,css).replace(/<script id="ts-peek-nav-runtime">[\s\S]*?<\/script>/,js);
   const run=await pageFor(body),{p,tap,swipe}=run;await p.evaluate(()=>scrollTo(0,600));await p.waitForTimeout(100);await tap('.ts-pk-rail li:first-child [data-ts-pk-section]');let before=await state(p),all=[];
   for(const sel of ['.ts-pk-card .open>b','.ts-pk-card .row[data-k="1"]'])all.push(...await swipe(sel));
   let after=await state(p);ok(slug+' '+version+' retained native card/row containment',all.every(s=>steady(before,s)&&s.pinned),{y:before.y,range:[Math.min(...all.map(s=>s.y)),Math.max(...all.map(s=>s.y))],summary:after.summary});
   await tap('.ts-pk-card .side');after=await state(p);results[version]=after;ok(slug+' '+version+' native side tap preserves reading position',steady(before,after)&&after.left!==before.left,{y:before.y,afterY:after.y});
   ok(slug+' '+version+' no JavaScript errors',run.errors.length===0,run.errors);await run.ctx.close();
  }
  ok(slug+' candidate2 equals candidate1 native state',JSON.stringify(results.candidate1)===JSON.stringify(results.candidate2),results);
 }
 console.log(JSON.stringify({pass,fail}));await browser.close();if(fail)process.exitCode=1;
})().catch(e=>{console.error(e.stack);process.exit(2)});
