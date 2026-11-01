// Bundle C post-apply contract. Imported only by the checker's default mode;
// baseline/preview remain usable against the original staging document.
const mapUrl = 'https://maps.google.com/maps?q=Mt.%20Tabor%2C%20Portland&z=12&hl=en&t=m&output=embed&iwloc=near';
const stripPaths = new Set(['/', '/about/', '/services/', '/contact/', '/privacy-policy/', '/accessibility/', '/thai-massage/', '/therapeutic-massage/', '/deep-tissue-massage/', '/chair-massage/']);
const normalize = text => text.trim().replace(/\s+/g, ' ');
const near = (a, b) => Math.abs(a - b) <= 0.1;
const rgb = value => {
  if (/^#[\da-f]{6}$/i.test(value)) return [1, 3, 5].map(i => parseInt(value.slice(i, i + 2), 16));
  const nums = value.match(/[\d.]+/g);
  return nums ? nums.slice(0, 3).map(Number) : [];
};

export function createContract(baselines, check, base) {
  const desktopStrips = new Map();
  let converted = 0, mapChecks = 0, minContrast = Infinity;
  async function atWidth(page, path, width, theme) {
    const expected = baselines.find(b => b.path === path && b.width === width);
    if (expected) {
      const actual = await page.evaluate(expected => expected.map(want => {
        const candidates = want.block ? [...document.querySelectorAll('.' + want.block + ' .uagb-heading-text')] : [...document.querySelectorAll('.ts-corp__card-title')];
        const el = candidates.find(node => node.textContent.trim() === want.text);
        if (!el) return null;
        const cs = getComputedStyle(el), r = el.getBoundingClientRect();
        return { text: el.textContent.trim(), tag: el.tagName, font: cs.fontFamily, size: cs.fontSize, weight: cs.fontWeight, line: cs.lineHeight, margins: [cs.marginTop, cs.marginBottom], rect: [r.width, r.height] };
      }), expected.headings);
      expected.headings.forEach((want, i) => {
        const got = actual[i];
        const equal = got && got.tag === 'H3' && got.text === want.text && got.font === want.font && got.size === want.size && got.weight === want.weight && got.line === want.line && JSON.stringify(got.margins) === JSON.stringify(want.margins) && near(got.rect[0], want.rect[2]) && near(got.rect[1], want.rect[3]);
        check(`${path} ${width} ${theme} H3 parity: ${want.text}`, equal, equal ? '' : JSON.stringify({ expected: want, actual: got }));
        if (width === 1280 && theme === 'light' && equal) converted++;
      });
    }
    const state = await page.evaluate(({ path, width }) => {
      const main = document.querySelector('main');
      const strip = document.querySelector('.ts-corp-band');
      const text = strip?.innerText.trim().replace(/\s+/g, ' ') || '';
      const jumpLinks = [...document.querySelectorAll('a[href^="#"]')].filter(a => a.getAttribute('href').length > 1);
      const head = document.head;
      const discoveries = [...head.querySelectorAll('link')].filter(link => ['edituri', 'shortlink'].includes((link.getAttribute('rel') || '').toLowerCase()) || /(?:json|xml)\+oembed/i.test(link.getAttribute('type') || '') || /rsd\+xml/i.test(link.getAttribute('type') || ''));
      const h1 = document.querySelector('h1');
      const h1box = h1?.getBoundingClientRect();
      const hidden = document.getElementById('ts-services-heading');
      const hiddenbox = hidden?.getBoundingClientRect();
      const contacts = [...(main?.querySelectorAll('a[href^="tel:"]') || [])];
      const hearts = [...document.querySelectorAll('.ts-heart-link')];
      const rows = [...new Set(hearts.map(e => Math.round(e.getBoundingClientRect().top)))].sort((a, b) => a - b);
      const heartWrap = document.querySelector('body.home .entry-content .wp-block-columns:has(> .wp-block-column > .uagb-block-972dd645)');
      const iframe = document.querySelector('iframe[title="Map of Portland, Oregon"]');
      return {
        h1s: document.querySelectorAll('h1').length,
        chevrons: [...document.querySelectorAll('.uagb-faq-item')].every(item => {
          const down = item.querySelector('.uagb-icon path')?.getAttribute('d')?.replace(/\s/g, '') || '';
          const up = item.querySelector('.uagb-icon-active path')?.getAttribute('d')?.replace(/\s/g, '') || '';
          return down.startsWith('M224416') && up.startsWith('M416352');
        }),
        jumps: jumpLinks.map(a => ({ href: a.getAttribute('href'), exists: !!document.getElementById(decodeURIComponent(a.getAttribute('href').slice(1))) })),
        discoveries: discoveries.map(e => e.outerHTML),
        stripCount: document.querySelectorAll('.ts-corp-band').length,
        stripRole: strip?.getAttribute('role'), stripName: strip?.getAttribute('aria-label'), stripText: text,
        stripDates: (text.match(/November 14, 2026/g) || []).length, stripSummers: (text.match(/summer 2027/g) || []).length,
        heartCount: hearts.length,
        stripHref: strip?.querySelector('.ts-corp-band__link')?.getAttribute('href'),
        hiddenHeading: hidden ? { tag: hidden.tagName, text: hidden.textContent.trim(), display: getComputedStyle(hidden).display, visibility: getComputedStyle(hidden).visibility, ariaHidden: hidden.getAttribute('aria-hidden'), width: hiddenbox.width, height: hiddenbox.height, position: getComputedStyle(hidden).position } : null,
        serviceRows: path === '/services/' ? ['Chair Massage', 'Corporate & Group Massage'].map(text => !![...main.querySelectorAll('h3')].find(h => h.textContent.trim() === text)) : [],
        faqH2: path === '/therapeutic-massage/' ? [...main.querySelectorAll('h2')].filter(h => h.textContent.trim() === 'Questions About Therapeutic Massage').length : 0,
        phoneUnderlines: contacts.map(a => getComputedStyle(a).textDecorationLine),
        map: iframe ? { src: iframe.getAttribute('src'), title: iframe.title, lazy: iframe.loading, height: iframe.getBoundingClientRect().height, embeds: main.querySelectorAll('embed').length } : null,
        layout: { path, width, height: document.documentElement.scrollHeight, h1Y: h1box?.top + scrollY, h1Height: h1box?.height, h1MarginTop: h1 ? getComputedStyle(h1).marginTop : null, entryHeaderPaddingTop: document.querySelector('.entry-header') ? getComputedStyle(document.querySelector('.entry-header')).paddingTop : null, stripHeight: strip?.getBoundingClientRect().height || 0, heartRowGap: heartWrap ? getComputedStyle(heartWrap).rowGap : null, heartRows: rows },
      };
    }, { path, width });
    const tag = `${path} ${width} ${theme}`;
    check(tag + ' one H1 and all internal jump targets exist', state.h1s === 1 && state.jumps.every(j => j.exists), state.jumps.filter(j => !j.exists).map(j => j.href).join(','));
    check(tag + ' FAQ normal/active icons point down/up', state.chevrons);
    check(tag + ' RSD/oEmbed/shortlink discovery absent', state.discoveries.length === 0, state.discoveries.join(' '));
    const expectedStrip = stripPaths.has(path);
    check(tag + ' booking landmark appears only on listed pages', state.stripCount === (expectedStrip ? 1 : 0) && (!expectedStrip || (state.stripRole === 'region' && state.stripName === 'Booking notice')));
    if (expectedStrip) {
      if (width === 1280 && theme === 'light') desktopStrips.set(path, state.stripText);
      check(tag + ' strip keeps opening/individual dates exactly once and Corporate path', state.stripDates === (Date.now() >= Date.parse('2026-11-14T08:00:00Z') ? 0 : 1) && state.stripSummers === 1 && new URL(state.stripHref || '/', base).pathname === '/corporate-massage-portland/');
      if (width <= 390) check(tag + ' phone strip is shorter than desktop', state.stripText.length < (desktopStrips.get(path) || '').length);
    }
    if (path === '/services/') {
      const h = state.hiddenHeading;
      check(tag + ' hidden Services H2 remains accessible without layout height', !!h && h.tag === 'H2' && h.text === 'Massage services' && h.display !== 'none' && h.visibility !== 'hidden' && h.ariaHidden !== 'true' && h.width <= 1 && h.height <= 1 && h.position === 'absolute');
      check(tag + ' Chair and Corporate rows have H3 titles', state.serviceRows.length === 2 && state.serviceRows.every(Boolean));
    }
    if (path === '/therapeutic-massage/') check(tag + ' one Therapeutic FAQ H2', state.faqH2 === 1);
    if (path === '/contact/') {
      check(tag + ' Contact phone links are visibly underlined', state.phoneUnderlines.length > 0 && state.phoneUnderlines.every(v => v.includes('underline')));
      const map = state.map;
      check(tag + ' titled lazy iframe retains exact Mt. Tabor query', !!map && map.src === mapUrl && map.title === 'Map of Portland, Oregon' && map.lazy === 'lazy' && map.embeds === 0);
    }
    if (theme === 'light') console.log('LAYOUT ' + JSON.stringify(state.layout));
    if (path === '/' && width <= 390) {
      check(tag + ' Home six heart links remain and phone rows are compact', state.heartCount === 6 && state.layout.heartRows.length > 0 && Number.parseFloat(state.layout.heartRowGap) <= 20);
    }
    // Check all five changed focus-rule families through actual keyboard focus.
    const focusTargets = [
      ['shared button/menu', '.wp-block-button__link,.uagb-buttons-repeater,.ast-custom-button-link,.menu-link'],
      ['jump link', '.ts-jump__list a'],
      ['heart link', '.ts-heart-link'],
      ['booking-strip link', '.ts-corp-band__link'],
      ['Corporate action', '.ts-corp .ts-corp__actions .wp-block-button__link'],
    ];
    for (const [label, selector] of focusTargets) {
      const targets = page.locator(selector);
      let target;
      for (let i = 0; i < await targets.count(); i++) { if (await targets.nth(i).isVisible()) { target = targets.nth(i); break; } }
      if (!target) continue;
      await page.keyboard.press('Tab');
      await target.focus();
      const focus = await target.evaluate(el => { const s = getComputedStyle(el); return { visible: el.matches(':focus-visible'), color: s.outlineColor, width: parseFloat(s.outlineWidth), wanted: getComputedStyle(document.documentElement).getPropertyValue('--ts-accent-deep').trim() }; });
      check(tag + ' deep-accent keyboard focus: ' + label, focus.visible && focus.width >= 2 && JSON.stringify(rgb(focus.color)) === JSON.stringify(rgb(focus.wanted)), JSON.stringify(focus));
    }
  }

  async function pageInteractions(page, path) {
    if (path === '/contact/') {
      for (const [width, height] of [[1280, 500], [976, 350], [767, 325], [390, 325]]) {
        await page.setViewportSize({ width, height: 900 });
        const actual = await page.locator('iframe[title="Map of Portland, Oregon"]').evaluate(e => e.getBoundingClientRect().height);
        check(`/contact/ ${width} map height ${height}`, near(actual, height), String(actual));
        mapChecks++;
      }
    }
    if (path !== '/chair-massage/') return;
    const details = page.locator('main details');
    check('Chair has four research panels plus native Sources disclosure', await details.count() === 5);
    for (let i = 0; i < await details.count(); i++) {
      const detail = details.nth(i), summary = detail.locator(':scope > summary');
      const initial = await detail.evaluate(e => e.open);
      const title = normalize(await summary.textContent());
      await summary.click();
      check('Chair details mouse: ' + title, await detail.evaluate(e => e.open) !== initial);
      await summary.press('Enter');
      check('Chair details Enter: ' + title, await detail.evaluate(e => e.open) === initial);
      await summary.press('Space');
      check('Chair details Space: ' + title, await detail.evaluate(e => e.open) !== initial);
      await summary.click();
      await detail.evaluate(e => { e.open = true; });
    }
    for (const theme of ['light', 'dark']) {
      await page.evaluate(t => document.documentElement.setAttribute('data-ts-theme', t), theme);
      const contrast = await page.evaluate(() => {
        const parse = s => { const a = s.match(/[\d.]+/g)?.map(Number) || [0, 0, 0]; return [a[0], a[1], a[2], a.length > 3 ? a[3] : 1]; };
        const over = (a, b) => { const alpha = a[3] + b[3] * (1 - a[3]); return alpha ? [0, 1, 2].map(i => (a[i] * a[3] + b[i] * b[3] * (1 - a[3])) / alpha).concat(alpha) : [0, 0, 0, 0]; };
        const bg = el => { let color = [0, 0, 0, 0]; for (let n = el; n; n = n.parentElement) { color = over(color, parse(getComputedStyle(n).backgroundColor)); if (color[3] >= 1) break; } return over(color, [255, 255, 255, 1]); };
        const lum = color => color.slice(0, 3).map(n => { n /= 255; return n <= .04045 ? n / 12.92 : ((n + .055) / 1.055) ** 2.4; }).reduce((v, n, i) => v + n * [.2126, .7152, .0722][i], 0);
        return [...document.querySelectorAll('main details summary,main details p,main details li,main details cite,main details a')].filter(e => e.textContent.trim() && e.getClientRects().length && getComputedStyle(e).visibility !== 'hidden').map(el => {
          const s = getComputedStyle(el), background = bg(el), foreground = over(parse(s.color), background);
          const a = lum(foreground), b = lum(background), ratio = (Math.max(a, b) + .05) / (Math.min(a, b) + .05);
          const size = parseFloat(s.fontSize), weight = Number.parseInt(s.fontWeight, 10), threshold = size >= 24 || (size >= 18.6667 && weight >= 700) ? 3 : 4.5;
          return { text: el.textContent.trim().slice(0, 80), ratio, threshold, color: s.color };
        });
      });
      const min = Math.min(...contrast.map(x => x.ratio));
      minContrast = Math.min(minContrast, min);
      const bad = contrast.filter(x => x.ratio + .01 < x.threshold);
      console.log('CONTRAST ' + JSON.stringify({ path, theme, tested: contrast.length, minimum: Number(min.toFixed(3)), failures: bad }));
      check('Chair research/Sources text contrast ' + theme, contrast.length > 20 && bad.length === 0);
    }
  }

  async function endpoints(context) {
    const endpoints = [
      ['404', '/ts-review-bundle-c-no-such-page-20261101/'],
      ['search', '/?s=massage'],
      ['security', '/.well-known/security.txt'],
    ];
    const parserPage = await context.newPage();
    // DOMParser can discover image/frame URLs even in inert HTML. These three
    // endpoint checks need only markup; block any accidental resource fetch.
    await parserPage.route('**/*', route => route.abort());
    try {
      for (const [kind, path] of endpoints) {
        const response = await context.request.get(base + path, { timeout: 40000 });
        const body = await response.text();
        if (response.status() === 202 || /<title>[^<]*Robot Challenge/i.test(body)) { console.log('INVALID: challenge on ' + path + '; no more endpoint requests'); return true; }
        if (kind === 'security') {
          const expected = `Contact: mailto:butsabad@gmail.com\nExpires: 2027-10-31T23:59:59Z\nCanonical: ${base}/.well-known/security.txt\n`;
          check('security.txt approved mailbox, future expiry, canonical and plain-text MIME', response.status() === 200 && body === expected && /^text\/plain;\s*charset=utf-8$/i.test(response.headers()['content-type'] || '') && Date.parse('2027-10-31T23:59:59Z') > Date.now(), JSON.stringify({ status: response.status(), type: response.headers()['content-type'] }));
          continue;
        }
        const state = await parserPage.evaluate(({ body, base }) => {
          const doc = new DOMParser().parseFromString(body, 'text/html');
          const nav = doc.querySelector('.ts-review-404');
          const links = nav ? [...nav.querySelectorAll('a')].map(a => [a.textContent.trim(), new URL(a.getAttribute('href'), base).pathname]) : [];
          return {
            navs: doc.querySelectorAll('.ts-review-404').length, links,
            search: !!doc.querySelector('main form input[name="s"],.site-content form input[name="s"]'),
            searchAdded: doc.querySelectorAll('.ts-review-search form input[name="s"]').length,
            bylines: [...doc.querySelectorAll('.entry-meta .byline,.entry-meta .posted-on,.posted-by,.posted-on')].length,
            viewPages: [...doc.querySelectorAll('a')].filter(a => { const copy = a.cloneNode(true); copy.querySelectorAll('.screen-reader-text').forEach(e => e.remove()); return /^View page\s*»$/.test(copy.textContent.trim()); }).length,
          };
        }, { body, base });
        if (kind === '404') check('404 includes four navigation links and search form', response.status() === 404 && state.navs === 1 && state.search && JSON.stringify(state.links) === JSON.stringify([['Home', '/'], ['Services', '/services/'], ['Corporate & Group Massage', '/corporate-massage-portland/'], ['Contact', '/contact/']]), JSON.stringify(state));
        if (kind === 'search') check('search has its own form, no bylines, and View page labels', response.status() === 200 && state.searchAdded === 1 && state.bylines === 0 && state.viewPages > 0, JSON.stringify(state));
      }
    } finally { await parserPage.close(); }
    return false;
  }
  function finish() {
    check('all 23 Spectra and three Corporate converted headings matched baseline', converted === 26, String(converted));
    check('all four responsive map heights checked', mapChecks === 4, String(mapChecks));
    console.log('Bundle C minimum measured Chair panel contrast: ' + (Number.isFinite(minContrast) ? minContrast.toFixed(3) + ':1' : 'not measured'));
  }
  return { atWidth, pageInteractions, endpoints, finish };
}
