<?php
// Display-only top strip + "Peek" side rail (idea 3 of /rail-nav-ideas/), Thai Massage page only. Practice on staging39 (Ryan, 2026-11-08: "Peek. Let's use this design. Now you can try it on a real staging page.").
// Built from the existing server-rendered row <nav class="ts-jump"> (A11yPageJumpLinks): its links stay the no-JavaScript fallback and the source of the destinations.
// Section names = the row's labels; descriptions = the first paragraph of each section; reading time = its words at 200 a minute. No page content is changed.
add_action( 'wp_head', function () {
	if ( is_admin() || is_feed() || ! is_page( 'thai-massage' ) ) {
		return;
	}
	?>
<style id="ts-peek-nav-css">
.ts-pk{--pk-ink:var(--ts-ink,#1C2531);--pk-muted:var(--ts-muted,#4A5563);--pk-rule:var(--ts-rule,#D5D6CF);--pk-panel:var(--ts-panel,#FAF9F6);--pk-ground:var(--ts-ground,#F5F4EF);--pk-stone:var(--ts-stone,#ECEBE4);--pk-on:var(--ts-green,var(--ast-global-color-0,#377A00));--pk-on-text:#fff;--pk-accent:var(--ts-accent,#C8963E);--pk-ease:cubic-bezier(.2,.8,.2,1);--pk-shadow:0 16px 44px -16px rgba(0,0,0,.45)}
html[data-ts-theme="dark"] .ts-pk{--pk-on:#8CCB5B;--pk-on-text:#0E141C}
.ts-pk,.ts-pk *,.ts-pk *::before{box-sizing:border-box}
/* top strip: display only */
.ts-pk-top{position:sticky;top:0;z-index:90;margin:0 -12px 1.25rem;padding:.45rem 12px .55rem;background:var(--pk-ground);background:color-mix(in srgb,var(--pk-ground) 94%,transparent);-webkit-backdrop-filter:blur(10px);backdrop-filter:blur(10px);border-bottom:1px solid var(--pk-rule);pointer-events:none;-webkit-user-select:none;user-select:none}
body.admin-bar .ts-pk-top{top:var(--wp-admin--admin-bar--height,32px)}
@media (max-width:600px){body.admin-bar .ts-pk-top{top:0}}
.ts-pk-top .in{margin:0}
.ts-pk-top p{display:flex;justify-content:space-between;gap:1rem;margin:0 0 .35rem;font-size:.82rem;line-height:1.3;color:var(--pk-muted)}
.ts-pk-top p b{min-width:0;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;color:var(--pk-ink);font-weight:700}
.ts-pk-top p span{flex:0 0 auto}
.ts-pk-top ol{display:flex;gap:3px;margin:0;padding:0;list-style:none}
.ts-pk-top li{flex:var(--len,1) 1 0;min-width:0;height:5px;margin:0;border-radius:9px;background:var(--pk-rule);overflow:hidden}
.ts-pk-top li i{display:block;height:100%;border-radius:inherit;background:var(--pk-on);transform-origin:left;transform:scaleX(var(--p,0))}
/* rail: right edge, thumb zone */
.ts-pk-nav{position:fixed;right:max(2px,env(safe-area-inset-right));top:30%;bottom:max(14%,76px);z-index:91;width:44px}   /* % follows the visible viewport on iOS; the bottom stays above the Back button */
.ts-pk-rail{position:absolute;inset:0;display:flex;flex-direction:column;gap:5px;margin:0;padding:8px 0;list-style:none;touch-action:none;-webkit-user-select:none;user-select:none;border-radius:22px;transition:background-color .2s,box-shadow .2s}
.ts-pk-nav.is-active .ts-pk-rail{background:var(--pk-panel);background:color-mix(in srgb,var(--pk-panel) 92%,transparent);box-shadow:0 6px 24px -12px rgba(0,0,0,.45)}
.ts-pk-rail li{position:relative;flex:var(--len,1) 1 0;min-height:22px;margin:0;padding:0}
.ts-pk-rail a{position:absolute;inset:0;display:block;text-decoration:none;-webkit-tap-highlight-color:transparent;-webkit-touch-callout:none;-webkit-user-drag:none;user-select:none}
.ts-pk-rail a:focus-visible{outline:3px solid var(--pk-accent);outline-offset:-2px;border-radius:12px}
.ts-pk-rail a::before{content:"";position:absolute;left:50%;top:0;bottom:0;width:6px;margin-left:-3px;border-radius:9px;background:var(--pk-rule);box-shadow:0 0 0 2px var(--pk-ground);transition:width .15s,margin .15s,background-color .15s}
.ts-pk-rail a i{position:absolute;left:50%;top:0;bottom:0;width:6px;margin-left:-3px;border-radius:9px;background:var(--pk-on);transform-origin:top;transform:scaleY(var(--p,0));transition:width .15s,margin .15s}
.ts-pk-rail li.is-hover a::before,.ts-pk-rail li.is-hover a i{width:12px;margin-left:-6px}
.ts-pk-rail li.is-hover a::before{background:var(--pk-accent)}
.ts-pk-label{position:absolute;right:48px;max-width:60vw;margin-top:-14px;padding:.3rem .65rem;border-radius:999px;background:var(--pk-ink);color:var(--pk-panel);font-size:.75rem;font-weight:700;line-height:1.3;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;pointer-events:none;opacity:0;transition:opacity .3s,top .2s}
.ts-pk-nav.is-moving .ts-pk-label{opacity:.92}
.ts-pk-nav.is-active .ts-pk-label{opacity:0}
.ts-pk-you{position:absolute;right:28px;width:10px;height:10px;margin-top:-5px;border-radius:50%;background:var(--pk-ink);box-shadow:0 0 0 3px var(--pk-ground);pointer-events:none;transition:top .2s}
/* peek card */
.ts-pk-card{position:fixed;right:58px;z-index:92;width:min(300px,calc(100vw - 78px));max-height:calc(100vh - 90px);max-height:calc(100dvh - 90px);overflow:hidden;padding:.85rem 1rem;border:1px solid var(--pk-rule);border-radius:18px;background:var(--pk-panel);color:var(--pk-ink);box-shadow:var(--pk-shadow);pointer-events:none;opacity:0;transform:translateX(10px) scale(.98);transition:opacity .15s,transform .2s var(--pk-ease);font-size:1rem;line-height:1.4}
.ts-pk-card.is-on{opacity:1;transform:none}
.ts-pk-card.is-pinned{pointer-events:auto;cursor:pointer;touch-action:manipulation;-webkit-tap-highlight-color:transparent}   /* a quick tap right after lifting must not be held back for double-tap detection */
.ts-pk-card small{display:block;font-size:.72rem;color:var(--pk-muted);text-transform:uppercase;letter-spacing:.08em}
.ts-pk-card b{display:block;font-size:1.05rem;margin:.15rem 2rem .1rem 0}
.ts-pk-card .d{display:block;font-size:.86rem;line-height:1.4;color:var(--pk-ink);opacity:.9}
.ts-pk-card .here{display:block;margin-top:.35rem;font-size:.75rem;color:var(--pk-muted)}
.ts-pk-card .shot{display:block;margin:.55rem 0 0;padding:.7rem .75rem;border-radius:12px;background:var(--pk-ground);border:1px solid var(--pk-rule)}
.ts-pk-card .shot strong{display:block;font-size:.95rem;line-height:1.25;margin:0 0 .3rem}
.ts-pk-card .shot em{display:block;font-style:normal;font-size:.82rem;line-height:1.4;color:var(--pk-muted);margin:0 0 .45rem}
.ts-pk-card .shot span{display:block;height:.42rem;margin:0 0 .32rem;border-radius:9px;background:var(--pk-stone)}
.ts-pk-card .shot span:nth-of-type(2){width:80%}.ts-pk-card .shot span:nth-of-type(3){width:60%}
.ts-pk-card .hint{display:block;margin-top:.6rem;padding-top:.55rem;border-top:1px solid var(--pk-rule);font-size:.82rem;font-weight:700;color:var(--pk-on)}
html[data-ts-theme="dark"] .ts-pk-card .hint{color:var(--pk-on)}
.ts-pk-card .x{position:absolute;right:.35rem;top:.35rem;display:none;width:40px;height:40px;margin:0;padding:0;border:0;border-radius:50%;background:transparent;color:var(--pk-muted);font:inherit;font-size:1.3rem;line-height:1;cursor:pointer;touch-action:manipulation}
.ts-pk-card .x:hover,.ts-pk-card .x:focus{background:var(--pk-stone);color:var(--pk-ink)}
.ts-pk-card.is-pinned .x{display:block}
.ts-pk-card:focus-visible{outline:3px solid var(--pk-accent);outline-offset:2px}
/* the heading glows briefly after a jump */
.ts-pk-flash{border-radius:8px;background:rgba(200,150,62,.28);background:color-mix(in srgb,var(--pk-accent) 28%,transparent);transition:background-color .6s}
.ts-pk-unflash{transition:background-color .6s}
/* back to where you were */
.ts-pk-back{position:fixed;right:max(10px,env(safe-area-inset-right));bottom:max(14px,env(safe-area-inset-bottom));z-index:93;display:inline-flex;align-items:center;gap:.35rem;max-width:calc(100vw - 20px);min-height:46px;margin:0;padding:0 1rem;border:0;border-radius:999px;background:var(--pk-ink);color:var(--pk-panel);font:inherit;font-size:.85rem;font-weight:700;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;box-shadow:var(--pk-shadow);cursor:pointer;touch-action:manipulation}
.ts-pk-back:hover,.ts-pk-back:focus{background:var(--pk-ink);color:var(--pk-panel)}
.ts-pk-back:focus-visible{outline:3px solid var(--pk-accent);outline-offset:2px}
.ts-pk-back[hidden]{display:none}
/* keep the text clear of the rail on phones and tablets; the bottom "On this page" button is replaced by the rail */
@media (max-height:500px){.ts-pk-card .shot>span,.ts-pk-card .here{display:none}.ts-pk-card .shot em{margin:0}}   /* landscape phones: the card fits the screen */
@media (max-width:1100px){html.ts-pk-ready:not(.ts-pk-left) .entry-content{padding-right:26px}html:not(.ts-pk-left) .ts-pk-top{padding-right:44px}}   /* the strip's text stays clear of the rail while it is still in the page */
html.ts-pk-ready #ts-section-guide-trigger,html.ts-pk-ready #ts-section-guide-popover{display:none!important}   /* only once the rail is there */
html.ts-pk-ready .entry-content [id]{scroll-margin-top:64px}
@media (prefers-reduced-motion:reduce){.ts-pk *,.ts-pk-top,.ts-pk-nav,.ts-pk-card{transition:none!important}.ts-pk-card,.ts-pk-card.is-on{transform:none}}
/* theme content rules (.entry-content ol, li, a, p) must not reach these parts, which sit inside the content for keyboard order */
html .ts-pk ol,html .ts-pk li{margin:0;padding:0;list-style:none;text-indent:0}
html .ts-pk .ts-pk-rail{padding:8px 0}
html .ts-pk a{text-decoration:none;border:0;box-shadow:none;background:none}
html .ts-pk.ts-pk-top p{margin:0 0 .35rem}
/* left-hand option: everything mirrored to the left edge */
html.ts-pk-left .ts-pk-nav{right:auto;left:max(2px,env(safe-area-inset-left))}
html.ts-pk-left .ts-pk-label{right:auto;left:48px}
html.ts-pk-left .ts-pk-you{right:auto;left:28px}
html.ts-pk-left .ts-pk-card{right:auto;left:58px;transform:translateX(-10px) scale(.98)}
html.ts-pk-left .ts-pk-card.is-on{transform:none}
html.ts-pk-left .ts-pk-back{right:auto;left:max(10px,env(safe-area-inset-left))}
html.ts-pk-left .ts-pk-tip{right:auto;left:58px}
@media (max-width:1100px){html.ts-pk-ready.ts-pk-left .entry-content{padding-left:26px}html.ts-pk-left .ts-pk-top{padding-left:44px}}   /* the right side keeps the theme's own spacing */
/* the side button in the preview */
.ts-pk-card .hint{display:flex;align-items:center;justify-content:space-between;gap:.5rem}
.ts-pk-card .side{display:none;flex:0 0 auto;min-height:36px;margin:0;padding:0 .7rem;border:1px solid var(--pk-rule);border-radius:999px;background:transparent;color:var(--pk-muted);font:inherit;font-size:.75rem;font-weight:700;cursor:pointer;touch-action:manipulation}
.ts-pk-card .side:hover,.ts-pk-card .side:focus{background:var(--pk-stone);color:var(--pk-ink)}
.ts-pk-card.is-pinned .side{display:inline-flex;align-items:center}
/* first-time hint beside the rail (shown once per visitor) */
.ts-pk-tip{position:fixed;right:58px;z-index:94;width:min(270px,calc(100vw - 78px));padding:.85rem 1rem;border-radius:16px;background:var(--pk-ink);color:var(--pk-panel);box-shadow:var(--pk-shadow);font-size:.88rem;line-height:1.4;opacity:0;transform:translateY(6px);transition:opacity .25s,transform .25s var(--pk-ease)}
.ts-pk-tip.is-on{opacity:1;transform:none}
.ts-pk-tip[hidden]{display:none}
.ts-pk-tip p{margin:0 0 .65rem;color:inherit}
.ts-pk-tip .row{display:flex;gap:.5rem;flex-wrap:wrap}
.ts-pk-tip button{min-height:40px;margin:0;padding:0 .85rem;border:0;border-radius:999px;font:inherit;font-size:.82rem;font-weight:700;cursor:pointer;touch-action:manipulation}
.ts-pk-tip .ok,.ts-pk-tip .ok:hover,.ts-pk-tip .ok:focus{background:var(--pk-on);color:var(--pk-on-text)}
.ts-pk-tip .flip,.ts-pk-tip .flip:hover,.ts-pk-tip .flip:focus{background:transparent;color:var(--pk-panel);box-shadow:inset 0 0 0 1px rgba(255,255,255,.45)}
.ts-pk-tip button:focus-visible,.ts-pk-card .side:focus-visible{outline:3px solid var(--pk-accent);outline-offset:2px}
.ts-pk-nav.is-hinting .ts-pk-rail{animation:ts-pk-wiggle 1.1s var(--pk-ease) 2}
@keyframes ts-pk-wiggle{0%,100%{transform:none}30%{transform:translateX(-7px)}60%{transform:translateX(3px)}}
html.ts-pk-left .ts-pk-nav.is-hinting .ts-pk-rail{animation-name:ts-pk-wiggle-l}
@keyframes ts-pk-wiggle-l{0%,100%{transform:none}30%{transform:translateX(7px)}60%{transform:translateX(-3px)}}
@media (prefers-reduced-motion:reduce){.ts-pk-nav.is-hinting .ts-pk-rail{animation:none}.ts-pk-tip{transition:none;transform:none}}
@media print{.ts-pk{display:none!important}}
</style>
	<?php
}, 200 );

add_action( 'wp_footer', function () {
	if ( is_admin() || is_feed() || ! is_page( 'thai-massage' ) ) {
		return;
	}
	?>
<script id="ts-peek-nav-runtime">
(function () {
  'use strict';
  function init() {
    var content = document.querySelector('.entry-content'), row = content && content.querySelector('.ts-jump');
    if (!row || !('PointerEvent' in window) || document.querySelector('.ts-pk-nav')) return;
    var links = [].slice.call(row.querySelectorAll('a[href^="#"]'));
    if (links.length < 2) return;
    var root = document.documentElement, calm = window.matchMedia('(prefers-reduced-motion: reduce)');
    var S = links.map(function (a) { return { href: a.getAttribute('href'), label: a.textContent.trim() }; });
    function el(i) { if (!S[i]) return null; var id = S[i].href.slice(1); try { id = decodeURIComponent(id); } catch (e) {} return document.getElementById(id); }   // null past the last section; a malformed href is used as written
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    function buzz() { if (navigator.vibrate) { try { navigator.vibrate(6); } catch (e) {} } }

    // what each section is: its heading, its first paragraph, its length (read from the page itself)
    function describe() {
      var ps = [].slice.call(content.querySelectorAll('p'));
      S.forEach(function (s, i) {
        var h = el(i), nx = el(i + 1) || null; s.heading = h ? h.textContent.trim() : s.label; s.excerpt = ''; s.desc = ''; s.mins = 1;
        if (!h) return;
        var r = document.createRange(); r.setStartBefore(h); if (nx) r.setEndBefore(nx); else r.setEndAfter(content.lastChild);
        var frag = r.cloneContents(); [].forEach.call(frag.querySelectorAll('script,style,noscript'), function (x) { x.remove(); });
        var words = (frag.textContent.match(/\S+/g) || []).length; s.mins = Math.max(1, Math.round(words / 200));
        for (var k = 0; k < ps.length; k++) {
          var p = ps[k], after = h.compareDocumentPosition(p) & Node.DOCUMENT_POSITION_FOLLOWING, before = !nx || (p.compareDocumentPosition(nx) & Node.DOCUMENT_POSITION_FOLLOWING);
          var pt = p.textContent.trim();
          if (after && before && pt.length > 30 && !/\?$/.test(pt) && !p.closest('.ts-jump') && !p.closest('.ts-pk-top') && !p.closest('.ts-pk') && !p.closest('figcaption')) {
            var t = p.textContent.trim().replace(/\s+/g, ' '); s.excerpt = t.length > 120 ? t.slice(0, 117).replace(/\s+\S*$/, '') + '…' : t;
            var safe = t.replace(/\b(Dr|Mr|Mrs|Ms|St|vs|etc|e\.g|i\.e)\./gi, function (m) { return m.replace(/\./g, '\u2024'); });   // abbreviations do not end the sentence
            var first = ((safe.match(/^.*?[.!?](\s|$)/) || [safe])[0].trim()).replace(/\u2024/g, '.'); s.desc = first.length > 90 ? first.slice(0, 87).replace(/\s+\S*$/, '') + '…' : first; break;
          }
        }
      });
      if (segs) segs.forEach(function (li, i) { li.querySelector('a').setAttribute('aria-label', S[i].label + (S[i].desc ? ': ' + S[i].desc : '')); });   // the rail's names for screen readers include the description
    }

    // the parts: top strip (display only), rail, card, back button
    var segs = null;
    var strip = document.createElement('div'); strip.className = 'ts-pk ts-pk-top'; strip.setAttribute('aria-hidden', 'true');
    strip.innerHTML = '<div class="in"><p><b></b><span></span></p><ol>' + S.map(function () { return '<li><i></i></li>'; }).join('') + '</ol></div>';
    row.parentNode.insertBefore(strip, row.nextSibling);   // in the page, right under the row; it sticks to the top as you read (display only)
    var box = document.createElement('div'); box.className = 'ts-pk';
    box.innerHTML = '<nav class="ts-pk-nav" aria-label="Sections on this page"><ol class="ts-pk-rail">' + S.map(function (s) { return '<li><a href="' + esc(s.href) + '" aria-label="' + esc(s.label) + '" draggable="false"><i></i></a></li>'; }).join('') + '</ol><span class="ts-pk-you" aria-hidden="true"></span><span class="ts-pk-label" aria-hidden="true"></span></nav>' +
      '<div class="ts-pk-card" role="group" aria-label="Section preview" tabindex="-1"></div><button type="button" class="ts-pk-back" hidden></button>' +
      '<div class="ts-pk-tip" role="note" hidden><p>Slide your thumb along this bar to preview each section. Lift, then tap the preview to go there.</p><div class="row"><button type="button" class="ok">Got it</button><button type="button" class="flip"></button></div></div>';
    var host = strip.parentNode, okHost = true;
    for (var an = host; an && an !== document.documentElement; an = an.parentElement) { var cs = getComputedStyle(an); if (cs.transform !== 'none' || cs.filter !== 'none' || cs.perspective !== 'none' || (cs.backdropFilter || cs.webkitBackdropFilter || 'none') !== 'none' || /transform|filter|perspective/.test(cs.willChange || '') || (cs.containerType && cs.containerType !== 'normal') || /paint|layout|strict|content/.test(cs.contain || '')) { okHost = false; break; } }   // any of these would make position:fixed relative to that ancestor
    if (okHost) host.insertBefore(box, strip.nextSibling); else document.body.appendChild(box);   // keyboard order: row, strip, rail, then the sections
    var top = strip, topSegs = [].slice.call(top.querySelectorAll('li')), nav = box.querySelector('.ts-pk-nav'), rail = nav.querySelector('.ts-pk-rail'),
      you = nav.querySelector('.ts-pk-you'), label = nav.querySelector('.ts-pk-label'), card = box.querySelector('.ts-pk-card'), back = box.querySelector('.ts-pk-back');
    segs = [].slice.call(rail.children);
    var tip = box.querySelector('.ts-pk-tip');
    root.classList.add('ts-pk-ready');

    // left-hand option (remembered on this device)
    function store(k, v) { try { if (v === undefined) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) { return null; } return null; }
    function sideName() { return root.classList.contains('ts-pk-left') ? 'right' : 'left'; }   // the side a flip would move to
    function setSide(left) { root.classList.toggle('ts-pk-left', left); store('ts-pk-side', left ? 'left' : 'right'); tip.querySelector('.flip').textContent = 'Use it on the ' + sideName(); lengths(); ask(); }
    root.classList.toggle('ts-pk-left', store('ts-pk-side') === 'left');
    tip.querySelector('.flip').textContent = 'Use it on the ' + sideName();

    // the rail is one keyboard stop: Tab reaches the current section's piece, arrow keys move between pieces
    function rove(i) { segs.forEach(function (li, k) { li.firstChild.setAttribute('tabindex', k === i ? '0' : '-1'); }); }
    rove(0);
    rail.addEventListener('keydown', function (e) {
      var li = e.target.closest('li'), i = segs.indexOf(li), n = -1;
      if (e.key === 'ArrowDown' || e.key === 'ArrowRight') n = Math.min(segs.length - 1, i + 1); else if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') n = Math.max(0, i - 1); else if (e.key === 'Home') n = 0; else if (e.key === 'End') n = segs.length - 1;
      if (n < 0) return; e.preventDefault(); rove(n); segs[n].firstChild.focus();
    });

    // where the reader is
    var cur = -1, from = null;
    function line() { return Math.max(window.innerHeight * 0.25, top.offsetHeight + 24); }
    function bounds(i) { var h = el(i), nx = el(i + 1), a = h ? h.getBoundingClientRect().top : 0, b = nx ? nx.getBoundingClientRect().top : content.getBoundingClientRect().bottom; return [a, b]; }
    function part(i) { var b = bounds(i); return Math.max(0, Math.min(1, (line() - b[0]) / Math.max(1, b[1] - b[0]))); }
    function fill(i) { return i < cur ? 1 : (i === cur ? part(i) : 0); }
    function whole() { var a = bounds(0)[0], b = bounds(S.length - 1)[1]; return Math.max(0, Math.min(1, (line() - a) / Math.max(1, b - a))); }
    function lengths() { var hs = S.map(function (s, i) { var b = bounds(i); return Math.max(1, b[1] - b[0]); }), avg = hs.reduce(function (x, y) { return x + y; }, 0) / S.length; S.forEach(function (s, i) { s.len = hs[i] / avg; });
      if (segs) { segs.forEach(function (li, k) { li.style.setProperty('--len', S[k].len.toFixed(2)); }); topSegs.forEach(function (li, k) { li.style.setProperty('--len', S[k].len.toFixed(2)); }); } }

    // jumping (heading just below the strip, also under the admin bar), and the way back
    var glow = { el: null, t: 0 }, jumping = false, jumpT = 0;   // jumping: a jump's own scrolling is under way (Back must not hide itself during it)
    function below() { return (parseFloat(getComputedStyle(top).top) || 0) + top.offsetHeight + 12; }   // where the stuck strip ends
    function jumpTo(i, record) {
      var h = el(i); if (!h) return;
      if (record) { from = { y: window.scrollY, name: cur < 0 ? 'the top' : S[cur].label }; back.textContent = '↩ Back to ' + from.name; back.hidden = false; }
      jumping = true; clearTimeout(jumpT); jumpT = setTimeout(function () { jumping = false; }, 3000);
      window.scrollTo({ top: h.getBoundingClientRect().top + window.scrollY - below(), behavior: calm.matches ? 'auto' : 'smooth' });
      if (glow.el) { glow.el.classList.remove('ts-pk-flash'); clearTimeout(glow.t); }
      glow.el = h; h.classList.remove('ts-pk-unflash'); h.classList.add('ts-pk-flash'); glow.t = setTimeout(function () { h.classList.add('ts-pk-unflash'); h.classList.remove('ts-pk-flash'); glow.el = null; }, 1200);
      if (!h.hasAttribute('tabindex')) h.setAttribute('tabindex', '-1');
      h.focus({ preventScroll: true });
      try { history.replaceState(null, '', S[i].href); } catch (e) {}   // no history entry per jump (the browser's Back would only change the address); the in-page Back returns
    }
    back.addEventListener('click', function () { if (!from) return; jumping = true; clearTimeout(jumpT); jumpT = setTimeout(function () { jumping = false; }, 3000); window.scrollTo({ top: from.y, behavior: calm.matches ? 'auto' : 'smooth' }); from = null; back.hidden = true; });
    // the row at the top of the page uses the same jump (otherwise the theme's own smooth scroll hides the heading under the strip)
    row.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('a[href^="#"]') : null; if (!a || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var i = links.indexOf(a); if (i < 0 || !el(i)) return; e.preventDefault(); e.stopPropagation(); jumpTo(i, true);
    }, true);

    // the peek card
    function cardHTML(i, pinned) {
      var s = S[i], here = i === cur ? 'You are reading this section.' : (cur < 0 ? '' : 'You are in: ' + S[cur].label);
      return '<button type="button" class="x" aria-label="Close the preview">×</button><small>Section ' + (i + 1) + ' of ' + S.length + ' · ' + s.mins + ' min</small><b>' + esc(s.label) + '</b>' + (s.desc ? '<span class="d">' + esc(s.desc) + '</span>' : '') + '<span class="here">' + esc(here) + '</span>' +
        '<span class="shot"><strong>' + esc(s.heading) + '</strong>' + (s.excerpt ? '<em>' + esc(s.excerpt) + '</em>' : '') + '<span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span></span>' +
        '<span class="hint"><span>' + (pinned ? 'Tap to go there' : 'Lift to keep this preview') + '</span>' + (pinned ? '<button type="button" class="side" aria-label="Move the section bar to the ' + sideName() + ' side">⇆ ' + (sideName() === 'left' ? 'Left' : 'Right') + '</button>' : '') + '</span>';
    }
    function hover(i) { segs.forEach(function (li, k) { li.classList.toggle('is-hover', k === i); }); }
    function place(y) {   // beside the thumb; never past the bottom of the screen, then never above the strip
      var h = card.offsetHeight, lo = Math.max(8, top.getBoundingClientRect().bottom + 8), hi = window.innerHeight - h - 12;
      card.style.top = Math.max(Math.min(lo, hi), Math.min(hi, y - h / 2)) + 'px';
    }
    var shown = '', clear = 0;
    function hideCard() { card.className = 'ts-pk-card'; shown = ''; hover(-1); nav.classList.remove('is-active'); clearTimeout(clear); clear = setTimeout(function () { if (!shown) card.innerHTML = ''; }, 220); }   // empty it after the fade
    function show(i, y, pinned) {
      var key = i + '|' + (pinned ? 1 : 0) + '|' + cur;
      if (key !== shown) { clearTimeout(clear); card.className = 'ts-pk-card is-on' + (pinned ? ' is-pinned' : ''); card.innerHTML = cardHTML(i, pinned); card.dataset.i = i; nav.classList.add('is-active'); hover(i); shown = key; }
      place(y);
    }
    // "this tap is already handled": swallow the click the browser may still send (to the card, or to whatever is under it once the card is gone)
    function swallowNextClick(lifted) {
      var on = true, t = 0;
      function sw(c) { c.preventDefault(); c.stopPropagation(); off(); }
      function up() { clearTimeout(t); t = setTimeout(off, 450); }   // timed from the finger lifting, however long the press
      function off() { if (!on) return; on = false; clearTimeout(t); document.removeEventListener('click', sw, true); document.removeEventListener('pointerup', up, true); document.removeEventListener('pointercancel', off, true); }
      document.addEventListener('click', sw, true); document.addEventListener('pointercancel', off, true);   // a touch that becomes a scroll sends no click: disarm
      if (lifted) up(); else document.addEventListener('pointerup', up, true);
      setTimeout(off, 5000);
    }
    function focusSeg(i) { var a = segs[i] && segs[i].firstChild; if (a) { rove(i); a.focus({ preventScroll: true }); } }
    // a tap on the pinned card: acted on when the finger lifts (a short, still touch), so it never depends on the browser turning the touch into a click;
    // mouse and keyboard use the click
    function activate(target) {
      if (!card.classList.contains('is-pinned')) return;
      var i = +card.dataset.i, hadFocus = card.contains(document.activeElement); hideCard();
      if (target.closest('.side')) { setSide(sideName() === 'left'); if (hadFocus) focusSeg(i); return; }
      if (target.closest('.x')) { if (hadFocus) focusSeg(i); return; }   // closing keeps keyboard users on the rail
      jumpTo(i, true);
    }
    var cdn = null, cardTapAt = -1e9;
    card.addEventListener('pointerdown', function (e) { cdn = card.classList.contains('is-pinned') && e.pointerType !== 'mouse' && e.isPrimary ? { id: e.pointerId, x: e.clientX, y: e.clientY } : null; });
    card.addEventListener('pointerup', function (e) {
      var d = cdn; cdn = null; if (!d || e.pointerId !== d.id || Math.abs(e.clientX - d.x) > 10 || Math.abs(e.clientY - d.y) > 10) return;
      cardTapAt = performance.now(); swallowNextClick(true); activate(e.target);   // the click that follows would land on whatever is under the card once it closes
    });
    card.addEventListener('pointercancel', function () { cdn = null; });
    card.addEventListener('click', function (e) { if (performance.now() - cardTapAt < 700) { e.preventDefault(); return; } activate(e.target); });
    card.addEventListener('keydown', function (e) { if (e.key !== 'Enter' || !card.classList.contains('is-pinned') || e.target.closest('button')) return; var i = +card.dataset.i; hideCard(); jumpTo(i, true); });   // Enter on × or the side button is that button's own click
    document.addEventListener('pointerdown', function (e) {
      if (!card.classList.contains('is-pinned') || card.contains(e.target) || rail.contains(e.target)) return;
      hideCard(); swallowNextClick(false);   // this tap only closes the preview
    }, true);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && card.classList.contains('is-on')) { var i = +card.dataset.i; hideCard(); focusSeg(i); } });

    // the rail gesture: slide to browse (the page does not move), lift to keep the preview
    var g = null;
    function pickAt(y) { var best = 0; segs.forEach(function (li, i) { if (y >= li.getBoundingClientRect().top - 3) best = i; }); return best; }
    var pressedAt = -1e9;   // when a finger or mouse last lifted off the rail; the click that may follow it is not an activation
    rail.addEventListener('contextmenu', function (e) { e.preventDefault(); });   // a long press must not open the link menu (it would cancel the preview)
    rail.addEventListener('dragstart', function (e) { e.preventDefault(); });
    rail.addEventListener('pointerdown', function (e) { if (e.button > 0 || !e.isPrimary || g) return; hideTip(); try { rail.setPointerCapture(e.pointerId); } catch (err) {} g = { id: e.pointerId, i: pickAt(e.clientY), y: e.clientY }; show(g.i, g.y, false); e.preventDefault(); });
    rail.addEventListener('pointermove', function (e) { if (!g || e.pointerId !== g.id) return; var i = pickAt(e.clientY); g.y = e.clientY; if (i !== g.i) { g.i = i; buzz(); } show(g.i, g.y, false); });
    rail.addEventListener('pointerup', function (e) { if (!g || e.pointerId !== g.id) return; var x = g; g = null; pressedAt = performance.now(); show(x.i, x.y, true); });
    function cancelPress(e) { if (!g || e.pointerId !== g.id) return; g = null; pressedAt = performance.now(); hideCard(); }
    rail.addEventListener('pointercancel', cancelPress);
    rail.addEventListener('lostpointercapture', function (e) { if (g && e.pointerId === g.id) setTimeout(function () { cancelPress(e); }, 0); });   // after pointerup g is already cleared, so only a real loss cancels
    rail.addEventListener('click', function (e) {   // a finger or mouse press is handled above; a click without one (keyboard Enter, VoiceOver/TalkBack double-tap) opens the preview and focuses it
      e.preventDefault(); e.stopPropagation();
      if (performance.now() - pressedAt < 600) { pressedAt = -1e9; return; }
      var li = e.target.closest('li'), i = segs.indexOf(li); if (i < 0) return; var r = li.getBoundingClientRect(); show(i, r.top + r.height / 2, true); card.focus();
    }, true);

    // draw on scroll, once per frame
    var ticking = false;
    var lastLabel = '';
    function draw() {
      ticking = false;
      // reads first (one layout pass) ...
      var y = line(), i = -1, tops = S.map(function (s, k) { var h = el(k); return h ? h.getBoundingClientRect().top : null; }), end = content.getBoundingClientRect().bottom;
      tops.forEach(function (t, k) { if (t !== null && t <= y) i = k; }); cur = i;
      var fills = S.map(function (s, k) { if (k < cur) return 1; if (k !== cur || tops[k] === null) return 0; var b = tops[k + 1] !== undefined && tops[k + 1] !== null ? tops[k + 1] : end; return Math.max(0, Math.min(1, (y - tops[k]) / Math.max(1, b - tops[k]))); });
      var a0 = tops[0] !== null ? tops[0] : 0, w = Math.max(0, Math.min(1, (y - a0) / Math.max(1, end - a0)));
      var rr = rail.getBoundingClientRect(), nr = nav.getBoundingClientRect(), sy = window.scrollY;
      // ... then writes
      segs.forEach(function (li, k) { li.firstChild.firstChild.style.setProperty('--p', fills[k].toFixed(3)); var a = li.firstChild; if (k === cur) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current'); });
      topSegs.forEach(function (li, k) { li.firstChild.style.setProperty('--p', fills[k].toFixed(3)); });
      var name = S[Math.max(cur, 0)].label;
      if (name !== lastLabel) { lastLabel = name; top.querySelector('b').textContent = name; top.querySelector('span').textContent = (Math.max(cur, 0) + 1) + ' of ' + S.length; label.textContent = name; }
      you.style.top = label.style.top = (rr.top - nr.top + 8 + w * (rr.height - 16)) + 'px';
      if (from && !back.hidden && !jumping && Math.abs(sy - from.y) < 40) { from = null; back.hidden = true; }   // the reader is back there already
      if (cur > -1 && !rail.contains(document.activeElement)) rove(cur);
      if (!tipDone && cur > -1) maybeTip();
    }

    // first-time hint: once per visitor, when they start reading; it goes away by itself, on "Got it", or when they touch the rail
    var tipDone = store('ts-pk-tip') === 'seen', tipT = 0;
    function maybeTip() {
      tipDone = true; store('ts-pk-tip', 'seen');
      var rr = rail.getBoundingClientRect(); tip.hidden = false; tip.style.top = Math.max(12, rr.top + rr.height / 2 - tip.offsetHeight / 2) + 'px';
      requestAnimationFrame(function () { tip.classList.add('is-on'); }); nav.classList.add('is-hinting');
      tipT = setTimeout(hideTip, 9000);
    }
    function hideTip() { clearTimeout(tipT); if (tip.hidden) return; tip.classList.remove('is-on'); nav.classList.remove('is-hinting'); setTimeout(function () { tip.hidden = true; }, 260); }
    tip.querySelector('.ok').addEventListener('click', hideTip);
    tip.querySelector('.flip').addEventListener('click', function () { setSide(sideName() === 'left'); var rr = rail.getBoundingClientRect(); tip.style.top = Math.max(12, rr.top + rr.height / 2 - tip.offsetHeight / 2) + 'px'; nav.classList.remove('is-hinting'); void nav.offsetWidth; nav.classList.add('is-hinting'); clearTimeout(tipT); tipT = setTimeout(hideTip, 6000); });
    function ask() { if (!ticking) { ticking = true; window.requestAnimationFrame(draw); } }
    var still = 0;
    window.addEventListener('scroll', function () { ask(); nav.classList.add('is-moving'); clearTimeout(still); still = setTimeout(function () { nav.classList.remove('is-moving'); if (jumping) { jumping = false; clearTimeout(jumpT); ask(); } }, 900); }, { passive: true });   // the section name shows beside the rail while the page scrolls
    window.addEventListener('resize', function () { lengths(); ask(); });
    if ('ResizeObserver' in window) new ResizeObserver(function () { lengths(); ask(); }).observe(content);   // FAQ answers opening change the lengths

    // arriving with #section in the address: the theme's own smooth scroll runs after load and ignores the strip, so line the heading up once the page stops moving
    // (only for 4 s, and never once the visitor touches, scrolls or types)
    function arrive() {
      var hash = location.hash, i = -1; S.forEach(function (s, k) { if (s.href === hash) i = k; }); if (i < 0) return;
      var until = Date.now() + 4000, timer = 0, done = false;
      function stop() { done = true; window.removeEventListener('scroll', idle); }
      function fix() { var h = el(i); if (!h || done) return; var t = h.getBoundingClientRect().top, want = below(); if (Math.abs(t - want) > 6) window.scrollTo({ top: t + window.scrollY - want, behavior: 'instant' }); }
      function idle() { clearTimeout(timer); timer = setTimeout(function () { if (Date.now() > until) { stop(); return; } fix(); }, 180); }
      ['wheel', 'touchstart', 'keydown', 'mousedown'].forEach(function (ev) { window.addEventListener(ev, stop, { once: true, passive: true }); });
      window.addEventListener('scroll', idle, { passive: true }); idle();
    }
    describe(); lengths(); draw();
    if (document.readyState === 'complete') arrive(); else window.addEventListener('load', function () { describe(); lengths(); ask(); arrive(); });
  }
  // after the section guide has given the Questions and Research headings their ids
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function () { setTimeout(init, 0); }); else setTimeout(init, 0);
})();
</script>
	<?php
}, 30 );
