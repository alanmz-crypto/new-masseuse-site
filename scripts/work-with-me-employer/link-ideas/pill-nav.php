<?php
// Pinned header + sticky pill navigation (idea 1 of /in-page-link-ideas/), Thai Massage page only. Practice on staging39 (Ryan, 2026-11-08: "Pills is best. Can we also make the header static").
// Restyles the existing server-rendered row <nav class="ts-jump"> (A11yPageJumpLinks snippet): links work without JavaScript; the script only adds the sliding highlight.
add_action( 'wp_head', function () {
	if ( is_admin() || is_feed() || ! is_page( 'thai-massage' ) ) {
		return;
	}
	?>
<style id="ts-pill-nav-css">
:root{--ts-head:85px;--ts-pills-h:62px;--ts-pill-on:var(--ts-green,var(--ast-global-color-0,#377A00));--ts-pill-on-text:#fff}
@media (min-width:922px){:root{--ts-head:91px}}
html[data-ts-theme="dark"]{--ts-pill-on:#8CCB5B;--ts-pill-on-text:#0E141C}
html{scroll-padding-top:calc(var(--ts-head) + var(--ts-pills-h) + 12px)}
@media (prefers-reduced-motion:no-preference){html{scroll-behavior:smooth}}
/* the header stays on top */
#masthead{position:sticky;top:0;z-index:60;transition:box-shadow .25s}
body.admin-bar #masthead{top:var(--wp-admin--admin-bar--height,32px)}
@media (max-width:600px){body.admin-bar #masthead{top:0}}
#masthead.ts-head-shadow{box-shadow:0 6px 18px -12px rgba(0,0,0,.35)}
#masthead .ast-mobile-header-content{max-height:calc(100vh - var(--ts-head));max-height:calc(100dvh - var(--ts-head));overflow-y:auto}
/* the pills, stuck right under it */
body .ts-jump{position:sticky;top:var(--ts-head);z-index:50;margin:0 0 1.25rem;padding:.55rem 0;font-size:.9rem;background:var(--ts-ground,#F5F4EF);box-shadow:0 0 0 100vmax var(--ts-ground,#F5F4EF);clip-path:inset(0 -100vmax -24px)}
body.admin-bar .ts-jump{top:calc(var(--ts-head) + var(--wp-admin--admin-bar--height,32px))}
@media (max-width:600px){body.admin-bar .ts-jump{top:var(--ts-head)}}
body .ts-jump.ts-is-stuck{box-shadow:0 0 0 100vmax var(--ts-ground,#F5F4EF),0 10px 14px -12px rgba(0,0,0,.3)}
body .ts-jump .ts-jump__list{position:relative;display:flex;flex-wrap:nowrap;gap:.4rem;margin:0;padding:0;list-style:none;overflow-x:auto;scroll-snap-type:x proximity;scrollbar-width:none;-webkit-mask-image:linear-gradient(90deg,#000 calc(100% - 24px),transparent);mask-image:linear-gradient(90deg,#000 calc(100% - 24px),transparent)}
body .ts-jump .ts-jump__list.ts-at-end{-webkit-mask-image:none;mask-image:none}
body .ts-jump .ts-jump__list::-webkit-scrollbar{display:none}
body .ts-jump .ts-jump__list li{flex:0 0 auto;margin:0;padding:0;scroll-snap-align:center}
body .ts-jump .ts-jump__list li+li::before{display:none}
body .ts-jump .ts-jump__list a{position:relative;z-index:1;display:inline-flex;align-items:center;min-height:44px;padding:0 1rem;border:1px solid var(--ts-rule,#D5D6CF);border-radius:999px;background:transparent;color:var(--ts-ink,#1C2531);font-weight:600;text-decoration:none;white-space:nowrap;transition:color .3s,border-color .3s,background-color .3s}
body .ts-jump .ts-jump__list a:hover{border-color:var(--ts-ink,#1C2531);color:var(--ts-ink,#1C2531)}
body .ts-jump .ts-jump__list a:focus-visible{outline:3px solid var(--ts-accent,#C8963E);outline-offset:2px}
body .ts-jump .ts-jump__list a[aria-current="true"]{color:var(--ts-pill-on-text);border-color:transparent}
body .ts-jump .ts-jump__list:not(.ts-has-ind) a[aria-current="true"]{background:var(--ts-pill-on)}
body .ts-jump .ts-pill-ind{position:absolute;left:0;top:0;height:100%;width:0;margin:0;padding:0;border-radius:999px;background:var(--ts-pill-on);pointer-events:none;z-index:0;opacity:0;transition:transform .35s cubic-bezier(.2,.8,.2,1),width .35s cubic-bezier(.2,.8,.2,1),opacity .2s}
body .ts-jump .ts-pill-ind.ts-on{opacity:1}
/* the bottom "On this page" guide button is not needed while the pills are always on screen */
#ts-section-guide-trigger,#ts-section-guide-popover{display:none!important}
@media (prefers-reduced-motion:reduce){body .ts-jump .ts-pill-ind,body .ts-jump .ts-jump__list a,#masthead{transition:none}}
@media print{#masthead,body .ts-jump{position:static;box-shadow:none;clip-path:none}}
</style>
	<?php
}, 200 );

add_action( 'wp_footer', function () {
	if ( is_admin() || is_feed() || ! is_page( 'thai-massage' ) ) {
		return;
	}
	?>
<script id="ts-pill-nav-runtime">
(function () {
  'use strict';
  var nav = document.querySelector('.entry-content .ts-jump'), head = document.getElementById('masthead');
  if (!nav || !head) return;
  var list = nav.querySelector('.ts-jump__list'), root = document.documentElement;
  var links = Array.prototype.slice.call(list.querySelectorAll('a[href^="#"]'));
  var targets = links.map(function (a) { return document.getElementById(decodeURIComponent(a.getAttribute('href').slice(1))); });
  var calm = window.matchMedia('(prefers-reduced-motion: reduce)');
  var ind = document.createElement('li');
  ind.className = 'ts-pill-ind'; ind.setAttribute('aria-hidden', 'true'); ind.setAttribute('role', 'presentation');
  list.insertBefore(ind, list.firstChild); list.classList.add('ts-has-ind');
  var current = -2;

  function measure() {
    root.style.setProperty('--ts-head', head.offsetHeight + 'px');
    root.style.setProperty('--ts-pills-h', nav.offsetHeight + 'px');
  }
  function offset() { return head.getBoundingClientRect().bottom + nav.offsetHeight + 16; }
  function place(i) {
    links.forEach(function (a, k) { if (k === i) { a.setAttribute('aria-current', 'true'); } else { a.removeAttribute('aria-current'); } });
    var a = links[i];
    if (!a) { ind.classList.remove('ts-on'); return; }
    ind.style.width = a.offsetWidth + 'px';
    ind.style.transform = 'translateX(' + a.parentNode.offsetLeft + 'px)';
    ind.classList.add('ts-on');
    var want = a.parentNode.offsetLeft - (list.clientWidth - a.offsetWidth) / 2;
    if (list.scrollWidth > list.clientWidth) list.scrollTo({ left: want, behavior: calm.matches ? 'auto' : 'smooth' });
  }
  function update() {
    ticking = false;
    var y = offset(), i = -1;
    for (var k = 0; k < targets.length; k++) { if (targets[k] && targets[k].getBoundingClientRect().top <= y) i = k; }
    if (i !== current) { current = i; place(i); }
    var stuck = nav.getBoundingClientRect().top <= head.getBoundingClientRect().bottom + 1;
    nav.classList.toggle('ts-is-stuck', stuck);
    head.classList.toggle('ts-head-shadow', window.scrollY > 4 && !stuck);
  }
  function ends() { list.classList.toggle('ts-at-end', list.scrollLeft + list.clientWidth >= list.scrollWidth - 2); }
  var ticking = false;
  function ask() { if (!ticking) { ticking = true; window.requestAnimationFrame(update); } }
  window.addEventListener('scroll', ask, { passive: true });
  window.addEventListener('resize', function () { measure(); current = -2; ask(); ends(); });
  list.addEventListener('scroll', ends, { passive: true });
  if ('ResizeObserver' in window) new ResizeObserver(function () { measure(); }).observe(head);
  measure(); ends(); update();
})();
</script>
	<?php
}, 20 );
