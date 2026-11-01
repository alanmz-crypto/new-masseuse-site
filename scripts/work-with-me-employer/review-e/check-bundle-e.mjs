// Read-only Bundle E browser contract. Four page loads; old-image geometry is
// measured by temporary DOM swaps from frozen captured markup, never by a second
// baseline navigation. Usage: node check-bundle-e.mjs <isolated-localhost-or-staging38> [inline]
import { createRequire } from 'node:module';
import { createHash } from 'node:crypto';
const require = createRequire(process.env.TS_PLAYWRIGHT_ROOT || '/home/lauer/Projects/SubProjects/Mirror/package.json');
const { chromium } = require('playwright');
const base = (process.argv[2] || '').replace(/\/$/, '');
if (!/^(https:\/\/staging38\.thaisolitude\.com|http:\/\/localhost:8090)$/.test(base)) throw new Error('staging38 or isolated localhost only');
const inline = process.argv.includes('inline') && !process.argv.includes('no-inline');
// Eight original rendered IMG elements from the pre-E staging capture. No full-page baseline or attachment-ID asset selection is packaged.
const PAGES = [
  {
    "path": "/",
    "images": [
      {
        "key": "home-therapeutic",
        "selector": ".uagb-block-db0b5e62 img",
        "src": "/wp-content/uploads/2026/06/ComfyUI_01040_curves.webp",
        "width": 512,
        "height": 399,
        "oldHTML": "<img loading=\"lazy\" decoding=\"async\" width=\"512\" height=\"399\" src=\"https://staging38.thaisolitude.com/wp-content/uploads/2026/10/therapeutic-massage-illustration.webp\" alt=\"Illustration of a therapist giving a therapeutic massage\" class=\"wp-image-1151\" style=\"border-radius:16px\" title=\"Home\" srcset=\"https://staging38.thaisolitude.com/wp-content/uploads/2026/10/therapeutic-massage-illustration.webp 512w, https://staging38.thaisolitude.com/wp-content/uploads/2026/10/therapeutic-massage-illustration-300x234.webp 300w\" sizes=\"auto, (max-width: 512px) 100vw, 512px\" />",
        "responsive": true,
        "medium": "/wp-content/uploads/2026/06/ComfyUI_01040_curves-300x234.webp"
      },
      {
        "key": "home-deep-tissue",
        "selector": ".uagb-block-9d58695e img",
        "src": "/wp-content/uploads/2026/06/ComfyUI_01044_.webp",
        "width": 512,
        "height": 287,
        "oldHTML": "<img loading=\"lazy\" decoding=\"async\" width=\"512\" height=\"460\" src=\"https://staging38.thaisolitude.com/wp-content/uploads/2026/10/deep-tissue-massage-illustration.webp\" alt=\"Illustration of a therapist giving a deep tissue massage\" class=\"wp-image-1157\" style=\"border-radius:16px\" title=\"Home\" srcset=\"https://staging38.thaisolitude.com/wp-content/uploads/2026/10/deep-tissue-massage-illustration.webp 512w, https://staging38.thaisolitude.com/wp-content/uploads/2026/10/deep-tissue-massage-illustration-300x270.webp 300w\" sizes=\"auto, (max-width: 512px) 100vw, 512px\" />",
        "responsive": true,
        "medium": "/wp-content/uploads/2026/06/ComfyUI_01044_-300x168.webp"
      },
      {
        "key": "home-thai",
        "selector": ".uagb-block-bb505ec2 img",
        "src": "/wp-content/uploads/2026/06/00illaCNN512Curve.webp",
        "width": 512,
        "height": 460,
        "oldHTML": "<img loading=\"lazy\" decoding=\"async\" width=\"512\" height=\"287\" src=\"https://staging38.thaisolitude.com/wp-content/uploads/2026/10/thai-massage-illustration.webp\" alt=\"Illustration of a therapist giving a Thai massage\" class=\"wp-image-1155\" style=\"border-radius:16px\" title=\"Home\" srcset=\"https://staging38.thaisolitude.com/wp-content/uploads/2026/10/thai-massage-illustration.webp 512w, https://staging38.thaisolitude.com/wp-content/uploads/2026/10/thai-massage-illustration-300x168.webp 300w\" sizes=\"auto, (max-width: 512px) 100vw, 512px\" />",
        "responsive": true,
        "medium": "/wp-content/uploads/2026/06/00illaCNN512Curve-300x270.webp"
      },
      {
        "key": "home-corporate",
        "selector": ".uagb-block-e7a31c58 img",
        "src": "/wp-content/uploads/2026/10/corporate-group-massage.webp",
        "width": 512,
        "height": 416,
        "oldHTML": "<img decoding=\"async\" src=\"/wp-content/uploads/2026/10/corporate-group-massage.webp\" alt=\"Illustration of a massage therapist giving a shoulder massage to a client lying on a massage table, with plants and flowers around them.\" title=\"Illustration of a massage therapist giving a shoulder massage to a client lying on a massage table, with plants and flowers around them.\" style=\"border-radius:16px\"/>"
      }
    ]
  },
  {
    "path": "/services/",
    "images": [
      {
        "key": "services-corporate",
        "selector": ".ts-corp-pic img",
        "src": "/wp-content/uploads/2026/10/corporate-group-massage.webp",
        "width": 512,
        "height": 416,
        "oldHTML": "<img decoding=\"async\" src=\"/wp-content/uploads/2026/10/corporate-group-massage.webp\" alt=\"Illustration of a massage therapist giving a shoulder massage to a client lying on a massage table, with plants and flowers around them.\" title=\"Illustration of a massage therapist giving a shoulder massage to a client lying on a massage table, with plants and flowers around them.\"/>"
      }
    ]
  },
  {
    "path": "/therapeutic-massage/",
    "images": [
      {
        "key": "therapeutic-red",
        "selector": ".uagb-block-b8f7a8b9 img",
        "src": "/wp-content/uploads/2026/11/therapeutic-red-400.webp",
        "width": 200,
        "height": 200,
        "oldHTML": "<img loading=\"lazy\" decoding=\"async\" src=\"https://staging38.thaisolitude.com/wp-content/uploads/2026/06/m0KN9hXmTHYQo2KXHhj4c_aa9c21bbb6df4fa79691af5de41f6f48-1.png\" alt=\"The same illustration in red tones.\" class=\"uag-image-1117\" width=\"200\" height=\"200\" title=\"The same illustration in red tones.\" role=\"img\" />"
      }
    ]
  },
  {
    "path": "/work-with-me/",
    "images": [
      {
        "key": "wwm-portrait",
        "selector": ".ts-wwm__hero-photo img",
        "src": "/wp-content/uploads/2026/09/butsaba_homepage.webp",
        "width": 360,
        "height": 540,
        "oldHTML": "<img decoding=\"async\" src=\"/wp-content/uploads/2026/09/butsaba_homepage.webp\" alt=\"Butsaba Dusrai standing outdoors in a yellow top\" title=\"Work With Me\" />"
      },
      {
        "key": "wwm-cutout",
        "selector": ".ts-wwm__thai-image img",
        "src": "/wp-content/uploads/2026/10/thai-massage-therapy-session-cutout.webp",
        "width": 1254,
        "height": 1254,
        "oldHTML": "<img decoding=\"async\" src=\"/wp-content/uploads/2026/10/thai-massage-therapy-session-cutout.webp\" alt=\"Butsaba Dusrai applying pressure to the upper back of a clothed person leaning forward on a mat.\" title=\"Work With Me\" />"
      }
    ]
  }
];
const WEBP = {
  "src": "/wp-content/uploads/2026/11/therapeutic-red-400.webp",
  "width": 400,
  "height": 313,
  "bytes": 21796,
  "sha256": "e795b73e4272e90f92db59b2734be41dd06365ac8ca6f0e8aa5e02d11ee1a756"
};
const OLD_PNG = "m0KN9hXmTHYQo2KXHhj4c_aa9c21bbb6df4fa79691af5de41f6f48-1.png";

const FONTS = [
  'https://fonts.gstatic.com/s/hankengrotesk/v12/ieVn2YZDLWuGJpnzaiwFXS9tYtpd59A.woff2',
  'https://fonts.gstatic.com/s/newsreader/v26/cY9AfjOCX1hbuyalUrK4397yjA.woff2',
];
const localize = html => html.replace(/https?:\/\/(?:staging38\.thaisolitude\.com|thaisolitude\.com)(?=\/)/g, base);
const browser = await chromium.launch();
const context = await browser.newContext({ viewport: { width: 1280, height: 900 }, reducedMotion: 'reduce' });
context.setDefaultTimeout(10000);
context.setDefaultNavigationTimeout(60000);
let total = 0, failures = 0, invalid = false;
const check = (label, ok, detail = '') => {
  total++;
  if (!ok) { failures++; console.log(`FAIL ${label}${detail ? ' ' + String(detail).slice(0, 900) : ''}`); }
};
const emojiURL = url => /\/(?:wp-includes\/js\/wp-emoji[^/?]*\.js|images\/core\/emoji\/)|twemoji/i.test(url);
const fontsReady = page => page.evaluate(() => Promise.race([
  document.fonts.ready.then(() => true),
  new Promise(resolve => setTimeout(() => resolve(false), 15000)),
]));
async function readyImages(page, targets) {
  const result = [];
  for (const target of targets) {
    const img = page.locator(target.selector);
    if (await img.count() !== 1) { result.push({ key: target.key, ok: false, error: 'selector must find exactly one image' }); continue; }
    try {
      await img.scrollIntoViewIfNeeded({ timeout: 5000 });
      result.push(await img.evaluate(async (el, key) => {
        const decoded = await Promise.race([
          el.decode().then(() => true, () => false),
          new Promise(resolve => setTimeout(() => resolve(false), 8000)),
        ]);
        return { key, ok: decoded && el.complete && el.naturalWidth > 0 && el.naturalHeight > 0, currentSrc: el.currentSrc };
      }, target.key));
    } catch (error) { result.push({ key: target.key, ok: false, error: String(error).split('\n')[0] }); }
  }
  await page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
  return result;
}
const geometry = (page, targets) => page.evaluate(targets => {
  const box = element => { const r = element.getBoundingClientRect(); return [r.x + scrollX, r.y + scrollY, r.width, r.height]; };
  return {
    height: document.documentElement.scrollHeight,
    images: targets.map(target => {
      const matches = document.querySelectorAll(target.selector);
      if (matches.length !== 1) return { key: target.key, missing: true };
      const img = matches[0];
      return { key: target.key, image: box(img), figure: box(img.closest('figure') || img.parentElement) };
    }),
  };
}, targets);
const geometryDiff = (before, after) => {
  const differences = [];
  if (Math.abs(before.height - after.height) > 0.5) differences.push(`page height ${before.height} -> ${after.height}`);
  if (before.images.length !== after.images.length) differences.push('image count differs');
  before.images.forEach((old, index) => {
    const next = after.images[index];
    if (!next || old.key !== next.key || old.missing || next.missing) { differences.push(`${old.key} missing or reordered`); return; }
    for (const part of ['image', 'figure']) {
      if (old[part].some((value, i) => Math.abs(value - next[part][i]) > 0.5)) differences.push(`${old.key} ${part} ${JSON.stringify(old[part])} -> ${JSON.stringify(next[part])}`);
    }
  });
  return differences;
};
async function swapOriginalImages(page, targets) {
  return page.evaluate(targets => {
    if (window.__tsBundleEImageSwap) throw new Error('Previous image swap was not restored');
    const entries = targets.map(target => {
      const matches = document.querySelectorAll(target.selector);
      if (matches.length !== 1) throw new Error(target.key + ': exact image selector required');
      const template = document.createElement('template'); template.innerHTML = target.oldHTML;
      if (template.content.children.length !== 1 || template.content.firstElementChild.tagName !== 'IMG') throw new Error('Baseline must contain only one IMG');
      return { key: target.key, actual: matches[0], original: template.content.firstElementChild };
    });
    window.__tsBundleEImageSwap = entries;
    for (const entry of entries) entry.actual.replaceWith(entry.original);
    return entries.length;
  }, targets.map(target => ({ ...target, oldHTML: localize(target.oldHTML) })));
}
async function restoreActualImages(page) {
  return page.evaluate(() => {
    const entries = window.__tsBundleEImageSwap || [];
    for (const entry of entries) {
      if (!entry.original.isConnected) throw new Error(entry.key + ': original IMG disappeared during measurement');
      entry.original.replaceWith(entry.actual);
      if (!entry.actual.isConnected) throw new Error(entry.key + ': actual IMG was not restored');
    }
    delete window.__tsBundleEImageSwap;
    return entries.length;
  });
}
async function checkImageState(page, targets, tag) {
  const state = await page.evaluate(targets => targets.map(target => {
    const matches = document.querySelectorAll(target.selector);
    if (matches.length !== 1) return { key: target.key, count: matches.length };
    const img = matches[0];
    const path = value => { try { return new URL(value, document.baseURI).pathname; } catch { return null; } };
    const srcset = (img.getAttribute('srcset') || '').split(',').map(item => item.trim()).filter(Boolean).map(item => {
      const parts = item.split(/\s+/); return { path: path(parts[0]), width: parts[1] || '' };
    });
    return { key: target.key, count: 1, src: img.getAttribute('src'), path: path(img.getAttribute('src')), width: Number(img.getAttribute('width')), height: Number(img.getAttribute('height')),
      complete: img.complete && img.naturalWidth > 0 && img.naturalHeight > 0, current: path(img.currentSrc), srcset, sizes: img.getAttribute('sizes'), oldIDClass: /\bwp-image-\d+\b/.test(img.className),
      box: { width: img.getBoundingClientRect().width, height: img.getBoundingClientRect().height } };
  }), targets);
  for (const target of targets) {
    const image = state.find(item => item.key === target.key);
    check(`${tag} ${target.key}: one decoded image at the reviewed URL and dimensions`, image?.count === 1 && image.path === target.src && image.width === target.width && image.height === target.height && image.complete && image.box.width > 0 && image.box.height > 0, JSON.stringify(image));
    if (target.responsive) {
      const candidates = image?.srcset || [];
      const allowed = [target.src, target.medium];
      check(`${tag} ${target.key}: canonical root-relative source, 300w/512w srcset and valid loaded currentSrc`, image?.src === target.src && !image.oldIDClass && !!image.sizes
        && candidates.some(item => item.path === target.medium && item.width === '300w')
        && candidates.some(item => item.path === target.src && item.width === '512w')
        && candidates.every(item => allowed.includes(item.path) && ['300w', '512w'].includes(item.width))
        && allowed.includes(image.current), JSON.stringify(image));
    }
  }
}
try {
  const asset = await context.request.get(base + WEBP.src, { timeout: 30000 });
  if (asset.status() === 202 || asset.headers()['sg-captcha']) {
    invalid = true; console.log('INVALID: hosting robot challenge; stop without more requests');
  } else {
    const bytes = await asset.body();
    const digest = createHash('sha256').update(bytes).digest('hex');
    check('derived red WebP: HTTP 200, WebP MIME, exact reviewed bytes and SHA256', asset.status() === 200 && /^image\/webp(?:;|$)/i.test(asset.headers()['content-type'] || '') && bytes.length === WEBP.bytes && digest === WEBP.sha256, `HTTP=${asset.status()} bytes=${bytes.length} sha256=${digest}`);
    const decodePage = await context.newPage();
    try {
      const dimensions = await decodePage.evaluate(async base64 => {
        const bytes = Uint8Array.from(atob(base64), char => char.charCodeAt(0));
        const bitmap = await createImageBitmap(new Blob([bytes], { type: 'image/webp' }));
        const out = [bitmap.width, bitmap.height]; bitmap.close(); return out;
      }, bytes.toString('base64')).catch(() => null);
      check('derived red WebP: actual binary decodes at 400×313', dimensions?.[0] === WEBP.width && dimensions?.[1] === WEBP.height, JSON.stringify(dimensions));
    } finally { await decodePage.close(); }
  }
  for (const config of PAGES) {
    if (invalid) break;
    const page = await context.newPage();
    const errors = [], emojiRequests = [];
    page.on('pageerror', error => errors.push(String(error)));
    page.on('request', request => { if (emojiURL(request.url())) emojiRequests.push(request.url()); });
    try {
      const response = await page.goto(base + config.path, { waitUntil: 'networkidle' });
      if (response?.status() === 202 || response?.headers()['sg-captcha'] || /Robot Challenge/i.test(await page.title())) {
        invalid = true; console.log('INVALID: hosting robot challenge; stop without more requests'); break;
      }
      check(`${config.path} HTTP 200`, response?.status() === 200);
      const source = await response.text();
      check(`${config.path} no old red PNG in served source`, !source.includes(OLD_PNG));
      const head = await page.evaluate(() => ({
        links: [...document.head.querySelectorAll('link[rel~="preload"][as="font"]')].map(link => ({ href: link.href, type: link.type, crossOrigin: link.getAttribute('crossorigin') })),
        allFonts: document.querySelectorAll('link[rel~="preload"][as="font"]').length,
        emojiNodes: document.querySelectorAll('[id*="wp-emoji"],script[src*="wp-emoji"]').length,
      }));
      check(`${config.path} exactly two unique valid font preloads in HEAD`, head.allFonts === 2 && head.links.length === 2 && new Set(head.links.map(link => link.href)).size === 2
        && FONTS.every(url => head.links.some(link => link.href === url && link.type === 'font/woff2' && link.crossOrigin !== null && ['', 'anonymous'].includes(link.crossOrigin.toLowerCase()))), JSON.stringify(head.links));
      check(`${config.path} emoji settings, loader and styles absent`, head.emojiNodes === 0 && !/wp-emoji-(?:settings|loader|release|styles)|_wpemojiSettings/i.test(source));
      await page.addStyleTag({ content: '*,*::before,*::after{animation:none!important;transition:none!important;scroll-behavior:auto!important}' });
      check(`${config.path} fonts ready within 15 seconds`, await fontsReady(page));
      for (const width of [1280, 390, 320]) {
        await page.setViewportSize({ width, height: 900 });
        for (const theme of ['light', 'dark']) {
          const tag = `${config.path} ${width} ${theme}`;
          await page.evaluate(theme => document.documentElement.setAttribute('data-ts-theme', theme), theme);
          check(`${tag} no side-scroll`, await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth));
          const actualReady = await readyImages(page, config.images);
          check(`${tag} reviewed images load/decode with bounded scrolling`, actualReady.every(item => item.ok), JSON.stringify(actualReady));
          await checkImageState(page, config.images, tag);
          const actualGeometry = await geometry(page, config.images);
          let baselineGeometry = null;
          try {
            check(`${tag} original DOM image count`, await swapOriginalImages(page, config.images) === config.images.length);
            const originalReady = await readyImages(page, config.images);
            check(`${tag} original images decode before comparison`, originalReady.every(item => item.ok), JSON.stringify(originalReady));
            baselineGeometry = await geometry(page, config.images);
          } finally {
            check(`${tag} actual new image nodes restored`, await restoreActualImages(page) === config.images.length);
          }
          const restoredReady = await readyImages(page, config.images);
          check(`${tag} restored images decode`, restoredReady.every(item => item.ok), JSON.stringify(restoredReady));
          const restoredGeometry = await geometry(page, config.images);
          const baselineDiff = baselineGeometry ? geometryDiff(baselineGeometry, restoredGeometry) : ['baseline unavailable'];
          const restoreDiff = geometryDiff(actualGeometry, restoredGeometry);
          check(`${tag} image/figure geometry and page height unchanged from original images`, baselineDiff.length === 0, baselineDiff.join('; '));
          check(`${tag} DOM measurement leaves applied geometry unchanged`, restoreDiff.length === 0, restoreDiff.join('; '));
          if (inline && width === 390 && theme === 'light') {
            const shots = config.path === '/' ? [['home-deep', '.uagb-block-9d58695e figure'], ['home-thai', '.uagb-block-bb505ec2 figure']]
              : config.path === '/therapeutic-massage/' ? [['therapeutic-red', '.uagb-block-b8f7a8b9 figure']] : [];
            for (const [name, selector] of shots) {
              const jpeg = await page.locator(selector).screenshot({ type: 'jpeg', quality: 45, timeout: 10000 });
              console.log(`INLINE bundle-e-${name}-390.jpg base64:${jpeg.toString('base64')}`);
            }
          }
        }
      }
      const emittedEmoji = await page.evaluate(() => performance.getEntriesByType('resource').map(item => item.name).filter(url => /wp-emoji|images\/core\/emoji|twemoji/i.test(url)));
      check(`${config.path} no emoji network requests`, emojiRequests.length === 0 && emittedEmoji.length === 0, [...emojiRequests, ...emittedEmoji].join(' '));
      check(`${config.path} no browser script errors`, errors.length === 0, errors.join('; '));
      console.log(`INFO Bundle E page complete ${config.path} (${config.images.length} images × 3 widths × 2 themes)`);
    } catch (error) {
      check(`${config.path} checker completed`, false, String(error).split('\n').slice(0, 2).join(' '));
      await restoreActualImages(page).catch(() => {});
    } finally { await page.close(); }
  }
} finally { await context.close(); await browser.close(); }
console.log(`Bundle E: ${total} assertions, ${failures} failures${invalid ? ', INVALID challenge' : ''}; 8 reviewed images, four page loads`);
process.exitCode = invalid ? 2 : failures ? 1 : 0;
