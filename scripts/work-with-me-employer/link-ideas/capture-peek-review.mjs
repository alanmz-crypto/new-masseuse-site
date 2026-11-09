// Read-only, staging39-only rendered evidence. Five page loads; stop on a hosting challenge.
import { createRequire } from 'node:module';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || `${process.cwd()}/package.json`);
const { chromium } = require('playwright');
const base = process.argv[2];
if (base !== 'https://staging39.thaisolitude.com') throw new Error('staging39 only');
const browser = await chromium.launch();
try {
  for (const [slug, width] of [['thai-massage',390], ['thai-massage',1280], ['therapeutic-massage',390], ['deep-tissue-massage',390], ['chair-massage',390]]) {
    const ctx = await browser.newContext({viewport:{width,height:844}, deviceScaleFactor:1, reducedMotion:'reduce'});
    await ctx.addInitScript(() => localStorage.setItem('ts-pk-tip','seen'));
    const page = await ctx.newPage();
    const errors=[]; page.on('pageerror', e=>errors.push(e.message));
    const response=await page.goto(`${base}/${slug}/`,{waitUntil:'load',timeout:45000});
    if (!response || response.status() !== 200 || /Robot Challenge/i.test(await page.title())) { console.log(`INVALID ${slug} ${response?.status()}: no conclusion`); process.exitCode=2; break; }
    await page.waitForTimeout(1600);
    const sections = await page.evaluate(() => {
      const c=document.querySelector('.entry-content');
      const links=[...c.querySelectorAll('.ts-jump a')];
      return links.map((a,i)=>{
        const target=document.getElementById(decodeURIComponent(a.hash.slice(1)));
        const next=links[i+1] && document.getElementById(decodeURIComponent(links[i+1].hash.slice(1)));
        if(!target) throw new Error('Missing target '+a.hash);
        const r=document.createRange(); r.setStartBefore(target); next?r.setEndBefore(next):r.setEndAfter(c.lastChild);
        const fragment=r.cloneContents(); fragment.querySelectorAll('script,style,nav,.ts-pk').forEach(x=>x.remove());
        return {anchor:target.id,label:a.textContent.trim(),text:fragment.textContent.replace(/\s+/g,' ').trim()};
      });
    });
    console.log('SECTIONS '+JSON.stringify({slug,width,sections}));
    if(slug==='thai-massage') {
      await page.evaluate(()=>{const h=document.getElementById('ts-common-reasons');scrollTo(0,h.getBoundingClientRect().top+scrollY-140);});
      await page.waitForTimeout(500);
      const shot=async(name)=>console.log(`INLINE ${slug}-${width}-${name}.jpg base64:${(await page.screenshot({type:'jpeg',quality:80})).toString('base64')}`);
      await shot('reading');
      await page.locator('.ts-pk-ix').focus(); await page.keyboard.press('Enter'); await page.waitForTimeout(500); await shot('index');
      await page.keyboard.press('Escape');
      await page.locator('.ts-pk-rail a').last().focus(); await page.keyboard.press('Enter'); await page.waitForTimeout(500); await shot('research');
      console.log('GEOMETRY '+JSON.stringify(await page.evaluate(()=>({width:innerWidth,scrollWidth:document.documentElement.scrollWidth,rail:[...document.querySelectorAll('.ts-pk-rail li')].map(x=>({height:x.getBoundingClientRect().height})),card:document.querySelector('.ts-pk-card').getBoundingClientRect().toJSON()}))));
    }
    console.log('PAGEERRORS '+JSON.stringify({slug,width,errors}));
    if(errors.length) process.exitCode=1;
    await ctx.close();
  }
} finally { await browser.close(); }
