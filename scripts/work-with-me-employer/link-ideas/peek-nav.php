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
.ts-pk-top{position:fixed;left:0;right:0;top:0;z-index:90;padding:.45rem max(16px,env(safe-area-inset-left)) .55rem;background:color-mix(in srgb,var(--pk-ground) 94%,transparent);-webkit-backdrop-filter:blur(10px);backdrop-filter:blur(10px);border-bottom:1px solid var(--pk-rule);pointer-events:none;-webkit-user-select:none;user-select:none;transform:translateY(-110%);transition:transform .3s var(--pk-ease)}
body.admin-bar .ts-pk-top{top:var(--wp-admin--admin-bar--height,32px)}
@media (max-width:600px){body.admin-bar .ts-pk-top{top:0}}
html.ts-pk-shown .ts-pk-top{transform:none}
.ts-pk-top .in{max-width:1200px;margin:0 auto}
.ts-pk-top p{display:flex;justify-content:space-between;gap:1rem;margin:0 0 .35rem;font-size:.82rem;line-height:1.3;color:var(--pk-muted)}
.ts-pk-top p b{min-width:0;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;color:var(--pk-ink);font-weight:700}
.ts-pk-top p span{flex:0 0 auto}
.ts-pk-top ol{display:flex;gap:3px;margin:0;padding:0;list-style:none}
.ts-pk-top li{flex:var(--len,1) 1 0;min-width:0;height:5px;margin:0;border-radius:9px;background:var(--pk-rule);overflow:hidden}
.ts-pk-top li i{display:block;height:100%;border-radius:inherit;background:var(--pk-on);transform-origin:left;transform:scaleX(var(--p,0))}
/* rail: right edge, thumb zone */
.ts-pk-nav{position:fixed;right:max(2px,env(safe-area-inset-right));top:30vh;bottom:14vh;z-index:91;width:44px;opacity:0;visibility:hidden;transition:opacity .25s,visibility 0s linear .25s}
html.ts-pk-shown .ts-pk-nav{opacity:1;visibility:visible;transition:opacity .25s}
.ts-pk-rail{position:absolute;inset:0;display:flex;flex-direction:column;gap:5px;margin:0;padding:8px 0;list-style:none;touch-action:none;-webkit-user-select:none;user-select:none;border-radius:22px;transition:background-color .2s,box-shadow .2s}
.ts-pk-nav.is-active .ts-pk-rail{background:color-mix(in srgb,var(--pk-panel) 92%,transparent);box-shadow:0 6px 24px -12px rgba(0,0,0,.45)}
.ts-pk-rail li{position:relative;flex:var(--len,1) 1 0;min-height:24px;margin:0;padding:0}
.ts-pk-rail a{position:absolute;inset:0;display:block;text-decoration:none;-webkit-tap-highlight-color:transparent}
.ts-pk-rail a:focus-visible{outline:3px solid var(--pk-accent);outline-offset:-2px;border-radius:12px}
.ts-pk-rail a::before{content:"";position:absolute;left:50%;top:0;bottom:0;width:6px;margin-left:-3px;border-radius:9px;background:var(--pk-rule);box-shadow:0 0 0 2px var(--pk-ground);transition:width .15s,margin .15s,background-color .15s}
.ts-pk-rail a i{position:absolute;left:50%;top:0;bottom:0;width:6px;margin-left:-3px;border-radius:9px;background:var(--pk-on);transform-origin:top;transform:scaleY(var(--p,0));transition:width .15s,margin .15s}
.ts-pk-rail li.is-hover a::before,.ts-pk-rail li.is-hover a i{width:12px;margin-left:-6px}
.ts-pk-rail li.is-hover a::before{background:var(--pk-accent)}
.ts-pk-you{position:absolute;right:28px;width:10px;height:10px;margin-top:-5px;border-radius:50%;background:var(--pk-ink);box-shadow:0 0 0 3px var(--pk-ground);pointer-events:none;transition:top .2s}
/* peek card */
.ts-pk-card{position:fixed;right:58px;z-index:92;width:min(300px,calc(100vw - 76px));padding:.85rem 1rem;border:1px solid var(--pk-rule);border-radius:18px;background:var(--pk-panel);color:var(--pk-ink);box-shadow:var(--pk-shadow);pointer-events:none;opacity:0;transform:translateX(10px) scale(.98);transition:opacity .15s,transform .2s var(--pk-ease);font-size:1rem;line-height:1.4}
.ts-pk-card.is-on{opacity:1;transform:none}
.ts-pk-card.is-pinned{pointer-events:auto;cursor:pointer}
.ts-pk-card small{display:block;font-size:.72rem;color:var(--pk-muted);text-transform:uppercase;letter-spacing:.08em}
.ts-pk-card b{display:block;font-size:1.05rem;margin:.15rem 2rem .1rem 0}
.ts-pk-card .here{display:block;margin-top:.35rem;font-size:.75rem;color:var(--pk-muted)}
.ts-pk-card .shot{display:block;margin:.55rem 0 0;padding:.7rem .75rem;border-radius:12px;background:var(--pk-ground);border:1px solid var(--pk-rule)}
.ts-pk-card .shot strong{display:block;font-size:.95rem;line-height:1.25;margin:0 0 .3rem}
.ts-pk-card .shot em{display:block;font-style:normal;font-size:.82rem;line-height:1.4;color:var(--pk-muted);margin:0 0 .45rem}
.ts-pk-card .shot span{display:block;height:.42rem;margin:0 0 .32rem;border-radius:9px;background:var(--pk-stone)}
.ts-pk-card .shot span:nth-of-type(2){width:78%}
.ts-pk-card .hint{display:block;margin-top:.6rem;padding-top:.55rem;border-top:1px solid var(--pk-rule);font-size:.82rem;font-weight:700;color:var(--pk-on)}
html[data-ts-theme="dark"] .ts-pk-card .hint{color:var(--pk-on)}
.ts-pk-card .x{position:absolute;right:.35rem;top:.35rem;display:none;width:40px;height:40px;margin:0;padding:0;border:0;border-radius:50%;background:transparent;color:var(--pk-muted);font:inherit;font-size:1.3rem;line-height:1;cursor:pointer;touch-action:manipulation}
.ts-pk-card .x:hover,.ts-pk-card .x:focus{background:var(--pk-stone);color:var(--pk-ink)}
.ts-pk-card.is-pinned .x{display:block}
.ts-pk-card:focus-visible{outline:3px solid var(--pk-accent);outline-offset:2px}
/* back to where you were */
.ts-pk-back{position:fixed;right:max(10px,env(safe-area-inset-right));bottom:max(14px,env(safe-area-inset-bottom));z-index:93;display:inline-flex;align-items:center;gap:.35rem;max-width:calc(100vw - 20px);min-height:46px;margin:0;padding:0 1rem;border:0;border-radius:999px;background:var(--pk-ink);color:var(--pk-panel);font:inherit;font-size:.85rem;font-weight:700;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;box-shadow:var(--pk-shadow);cursor:pointer;touch-action:manipulation}
.ts-pk-back:hover,.ts-pk-back:focus{background:var(--pk-ink);color:var(--pk-panel)}
.ts-pk-back:focus-visible{outline:3px solid var(--pk-accent);outline-offset:2px}
.ts-pk-back[hidden]{display:none}
/* keep the text clear of the rail on phones and tablets; the bottom "On this page" button is replaced by the rail */
@media (max-width:1100px){html.ts-pk-ready .entry-content{padding-right:26px}}
#ts-section-guide-trigger,#ts-section-guide-popover{display:none!important}
html.ts-pk-ready .entry-content [id]{scroll-margin-top:64px}
@media (prefers-reduced-motion:reduce){.ts-pk *,.ts-pk-top,.ts-pk-nav,.ts-pk-card{transition:none!important}}
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
    function el(i) { return S[i] ? document.getElementById(decodeURIComponent(S[i].href.slice(1))) : null; }   // null past the last section
    function esc(t) { var d = document.createElement('div'); d.textContent = t; return d.innerHTML; }
    function buzz() { if (navigator.vibrate) { try { navigator.vibrate(6); } catch (e) {} } }

    // what each section is: its heading, its first paragraph, its length (read from the page itself)
    function describe() {
      var ps = [].slice.call(content.querySelectorAll('p'));
      S.forEach(function (s, i) {
        var h = el(i), nx = el(i + 1) || null; s.heading = h ? h.textContent.trim() : s.label; s.excerpt = ''; s.mins = 1;
        if (!h) return;
        var r = document.createRange(); r.setStartBefore(h); if (nx) r.setEndBefore(nx); else r.setEndAfter(content.lastChild);
        var words = (r.toString().match(/\S+/g) || []).length; s.mins = Math.max(1, Math.round(words / 200));
        for (var k = 0; k < ps.length; k++) {
          var p = ps[k], after = h.compareDocumentPosition(p) & Node.DOCUMENT_POSITION_FOLLOWING, before = !nx || (p.compareDocumentPosition(nx) & Node.DOCUMENT_POSITION_FOLLOWING);
          if (after && before && p.textContent.trim().length > 30 && !p.closest('.ts-jump')) {
            var t = p.textContent.trim().replace(/\s+/g, ' '); s.excerpt = t.length > 120 ? t.slice(0, 117).replace(/\s+\S*$/, '') + '…' : t; break;
          }
        }
      });
    }

    // the parts: top strip (display only), rail, card, back button
    var box = document.createElement('div'); box.className = 'ts-pk';
    box.innerHTML = '<div class="ts-pk-top" aria-hidden="true"><div class="in"><p><b></b><span></span></p><ol>' + S.map(function () { return '<li><i></i></li>'; }).join('') + '</ol></div></div>' +
      '<nav class="ts-pk-nav" aria-label="Sections on this page"><ol class="ts-pk-rail">' + S.map(function (s) { return '<li><a href="' + esc(s.href) + '"><i></i><span class="screen-reader-text">' + esc(s.label) + '</span></a></li>'; }).join('') + '</ol><span class="ts-pk-you" aria-hidden="true"></span></nav>' +
      '<div class="ts-pk-card" role="group" aria-label="Section preview" tabindex="-1"></div><button type="button" class="ts-pk-back" hidden></button>';
    document.body.appendChild(box);
    var top = box.querySelector('.ts-pk-top'), topSegs = [].slice.call(top.querySelectorAll('li')), nav = box.querySelector('.ts-pk-nav'), rail = nav.querySelector('.ts-pk-rail'),
      segs = [].slice.call(rail.children), you = nav.querySelector('.ts-pk-you'), card = box.querySelector('.ts-pk-card'), back = box.querySelector('.ts-pk-back');
    root.classList.add('ts-pk-ready');

    // where the reader is
    var cur = -1, from = null;
    function line() { return Math.max(window.innerHeight * 0.25, top.offsetHeight + 24); }
    function bounds(i) { var h = el(i), nx = el(i + 1), a = h ? h.getBoundingClientRect().top : 0, b = nx ? nx.getBoundingClientRect().top : content.getBoundingClientRect().bottom; return [a, b]; }
    function part(i) { var b = bounds(i); return Math.max(0, Math.min(1, (line() - b[0]) / Math.max(1, b[1] - b[0]))); }
    function fill(i) { return i < cur ? 1 : (i === cur ? part(i) : 0); }
    function whole() { var a = bounds(0)[0], b = bounds(S.length - 1)[1]; return Math.max(0, Math.min(1, (line() - a) / Math.max(1, b - a))); }
    function lengths() { var hs = S.map(function (s, i) { var b = bounds(i); return Math.max(1, b[1] - b[0]); }), avg = hs.reduce(function (x, y) { return x + y; }, 0) / S.length; S.forEach(function (s, i) { s.len = hs[i] / avg; }); }

    // jumping (heading just below the strip), and the way back
    function jumpTo(i, record) {
      var h = el(i); if (!h) return;
      if (record) { from = { y: window.scrollY, name: cur < 0 ? 'the top' : S[cur].label }; back.textContent = '↩ Back to ' + from.name; back.hidden = false; }
      root.classList.add('ts-pk-shown');
      window.scrollTo({ top: h.getBoundingClientRect().top + window.scrollY - (top.offsetHeight + 12), behavior: calm.matches ? 'auto' : 'smooth' });
      if (!h.hasAttribute('tabindex')) h.setAttribute('tabindex', '-1');
      h.focus({ preventScroll: true });
      try { history.pushState(null, '', S[i].href); } catch (e) {}
    }
    back.addEventListener('click', function () { if (!from) return; window.scrollTo({ top: from.y, behavior: calm.matches ? 'auto' : 'smooth' }); from = null; back.hidden = true; });
    // the row at the top of the page uses the same jump (otherwise the theme's own smooth scroll hides the heading under the strip)
    row.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('a[href^="#"]') : null; if (!a || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var i = links.indexOf(a); if (i < 0 || !el(i)) return; e.preventDefault(); e.stopPropagation(); jumpTo(i, true);
    }, true);

    // the peek card
    function cardHTML(i, pinned) {
      var s = S[i], here = i === cur ? 'You are reading this section.' : (cur < 0 ? '' : 'You are in: ' + S[cur].label);
      return '<button type="button" class="x" aria-label="Close the preview">×</button><small>Section ' + (i + 1) + ' of ' + S.length + ' · ' + s.mins + ' min</small><b>' + esc(s.label) + '</b><span class="here">' + esc(here) + '</span>' +
        '<span class="shot"><strong>' + esc(s.heading) + '</strong>' + (s.excerpt ? '<em>' + esc(s.excerpt) + '</em>' : '') + '<span aria-hidden="true"></span><span aria-hidden="true"></span></span>' +
        '<span class="hint">' + (pinned ? 'Tap to go there' : 'Lift to keep this preview') + '</span>';
    }
    function hover(i) { segs.forEach(function (li, k) { li.classList.toggle('is-hover', k === i); }); }
    function place(y) { var h = card.offsetHeight, t = Math.max(top.offsetHeight + 8, Math.min(window.innerHeight - h - 70, y - h / 2)); card.style.top = t + 'px'; }
    function hideCard() { card.className = 'ts-pk-card'; card.innerHTML = ''; hover(-1); nav.classList.remove('is-active'); }
    function show(i, y, pinned) { card.className = 'ts-pk-card is-on' + (pinned ? ' is-pinned' : ''); card.innerHTML = cardHTML(i, pinned); card.dataset.i = i; nav.classList.add('is-active'); hover(i); place(y); }
    card.addEventListener('click', function (e) { if (!card.classList.contains('is-pinned')) return; var i = +card.dataset.i; if (e.target.closest('.x')) { hideCard(); return; } hideCard(); jumpTo(i, true); });
    card.addEventListener('keydown', function (e) { if (e.key === 'Enter' && card.classList.contains('is-pinned')) { var i = +card.dataset.i; hideCard(); jumpTo(i, true); } });
    document.addEventListener('pointerdown', function (e) { if (card.classList.contains('is-pinned') && !card.contains(e.target) && !rail.contains(e.target)) hideCard(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && card.classList.contains('is-on')) { var i = +card.dataset.i; hideCard(); var a = segs[i] && segs[i].querySelector('a'); if (a) a.focus(); } });

    // the rail gesture: slide to browse (the page does not move), lift to keep the preview
    var g = null;
    function pickAt(y) { var best = 0; segs.forEach(function (li, i) { if (y >= li.getBoundingClientRect().top - 3) best = i; }); return best; }
    rail.addEventListener('pointerdown', function (e) { if (e.button > 0) return; try { rail.setPointerCapture(e.pointerId); } catch (err) {} g = { i: pickAt(e.clientY), y: e.clientY }; show(g.i, g.y, false); e.preventDefault(); });
    rail.addEventListener('pointermove', function (e) { if (!g) return; var i = pickAt(e.clientY); g.y = e.clientY; if (i !== g.i) { g.i = i; buzz(); } show(g.i, g.y, false); });
    rail.addEventListener('pointerup', function () { if (!g) return; var x = g; g = null; show(x.i, x.y, true); });
    rail.addEventListener('pointercancel', function () { g = null; hideCard(); });
    rail.addEventListener('click', function (e) {   // presses are handled above; keyboard Enter (detail 0) opens the preview and focuses it
      e.preventDefault(); e.stopPropagation();
      if (e.detail === 0) { var li = e.target.closest('li'), i = segs.indexOf(li), r = li.getBoundingClientRect(); show(i, r.top + r.height / 2, true); card.focus(); }
    }, true);

    // draw on scroll, once per frame
    var ticking = false;
    function draw() {
      ticking = false;
      root.classList.toggle('ts-pk-shown', row.getBoundingClientRect().bottom < 0);
      var y = line(), i = -1; S.forEach(function (s, k) { var h = el(k); if (h && h.getBoundingClientRect().top <= y) i = k; }); cur = i;
      segs.forEach(function (li, k) { li.style.setProperty('--len', (S[k].len || 1).toFixed(2)); li.querySelector('i').style.setProperty('--p', fill(k).toFixed(3)); var a = li.querySelector('a'); if (k === cur) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current'); });
      topSegs.forEach(function (li, k) { li.style.setProperty('--len', (S[k].len || 1).toFixed(2)); li.firstChild.style.setProperty('--p', fill(k).toFixed(3)); });
      top.querySelector('b').textContent = S[Math.max(cur, 0)].label; top.querySelector('span').textContent = (Math.max(cur, 0) + 1) + ' of ' + S.length;
      var rr = rail.getBoundingClientRect(), nr = nav.getBoundingClientRect(); you.style.top = (rr.top - nr.top + 8 + whole() * (rr.height - 16)) + 'px';
    }
    function ask() { if (!ticking) { ticking = true; window.requestAnimationFrame(draw); } }
    window.addEventListener('scroll', ask, { passive: true });
    window.addEventListener('resize', function () { lengths(); ask(); });
    if ('ResizeObserver' in window) new ResizeObserver(function () { lengths(); ask(); }).observe(content);   // FAQ answers opening change the lengths

    // arriving with #section in the address: the theme's own smooth scroll runs after load and ignores the strip, so line the heading up once the page stops moving
    // (only for 4 s, and never once the visitor touches, scrolls or types)
    function arrive() {
      var hash = location.hash, i = -1; S.forEach(function (s, k) { if (s.href === hash) i = k; }); if (i < 0) return;
      var until = Date.now() + 4000, timer = 0, done = false;
      function stop() { done = true; window.removeEventListener('scroll', idle); }
      function fix() { var h = el(i); if (!h || done) return; root.classList.add('ts-pk-shown'); var t = h.getBoundingClientRect().top, want = top.offsetHeight + 12; if (Math.abs(t - want) > 6) window.scrollTo({ top: t + window.scrollY - want, behavior: 'instant' }); }
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
