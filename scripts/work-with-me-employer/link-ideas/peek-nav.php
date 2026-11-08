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
.ts-pk-top{position:sticky;top:var(--pk-head,0px);z-index:90;margin:0 -12px 1.25rem;padding:.45rem 12px .55rem;background:var(--pk-ground);background:color-mix(in srgb,var(--pk-ground) 94%,transparent);-webkit-backdrop-filter:blur(10px);backdrop-filter:blur(10px);border-bottom:1px solid var(--pk-rule);pointer-events:none;-webkit-user-select:none;user-select:none}
body.admin-bar .ts-pk-top{top:calc(var(--wp-admin--admin-bar--height,32px) + var(--pk-head,0px))}
@media (max-width:600px){body.admin-bar .ts-pk-top{top:var(--pk-head,0px)}}
.ts-pk-top .in{margin:0}
.ts-pk-top p{display:flex;justify-content:space-between;gap:1rem;margin:0 0 .35rem;font-size:.82rem;line-height:1.3;color:var(--pk-muted)}
.ts-pk-top p b{min-width:0;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;color:var(--pk-ink);font-weight:700}
.ts-pk-top p span{flex:0 0 auto}
.ts-pk-top ol{display:flex;gap:3px;margin:0;padding:0;list-style:none}
.ts-pk-top li{flex:var(--len,1) 1 0;min-width:0;height:5px;margin:0;border-radius:9px;background:var(--pk-rule);overflow:hidden}
.ts-pk-top li i{display:block;height:100%;border-radius:inherit;background:var(--pk-on);transform-origin:left;transform:scaleX(var(--p,0))}
/* rail: right edge, thumb zone */
.ts-pk-nav{position:fixed;right:max(2px,env(safe-area-inset-right));top:max(30%,calc(var(--pk-head,0px) + var(--pk-strip,0px) + 64px));bottom:max(14%,76px);z-index:91;width:44px}   /* % follows the visible viewport on iOS; the index icon stays clear of the header and strip */
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
/* the stack: closed boxes for the sections above and below the open one, so its place on the page shows (Ryan, 2026-11-08) */
html .ts-pk .ts-pk-card.ts-pk-stack{padding:.4rem}
.ts-pk-card .open{position:relative;margin:.3rem 0;padding:.75rem .9rem;border:1.5px solid var(--pk-on);border-radius:14px;background:var(--pk-panel)}
.ts-pk-card .open:first-child{margin-top:0}.ts-pk-card .open:last-child{margin-bottom:0}
html .ts-pk .ts-pk-card .row{display:flex;align-items:center;gap:.55rem;width:100%;min-height:34px;margin:0;padding:.3rem .6rem;border:0;border-radius:10px;background:var(--pk-stone);color:var(--pk-ink);box-shadow:none;font:inherit;font-size:.82rem;font-weight:600;line-height:1.25;text-align:left;cursor:pointer;touch-action:manipulation;-webkit-tap-highlight-color:transparent}
html .ts-pk .ts-pk-card .row:hover,html .ts-pk .ts-pk-card .row:focus{background:var(--pk-stone);color:var(--pk-ink);box-shadow:none}
html .ts-pk .ts-pk-card .row:focus-visible{outline:3px solid var(--pk-accent);outline-offset:1px}
html .ts-pk .ts-pk-card .row+.row{margin-top:.25rem}
html .ts-pk .ts-pk-card .row b{display:grid;place-items:center;flex:0 0 auto;width:1.45rem;height:1.45rem;margin:0;border-radius:50%;background:var(--pk-panel);font-size:.72rem;font-weight:800}
html .ts-pk .ts-pk-card .row span{min-width:0;overflow:hidden;white-space:nowrap;text-overflow:ellipsis}
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
/* keep the text clear of the rail on phones and tablets; the bottom "On this page" button is replaced by the rail */
@media (max-height:500px){.ts-pk-card .shot>span,.ts-pk-card .here{display:none}.ts-pk-card .shot em{margin:0}}   /* landscape phones: the card fits the screen */
html.ts-pk-ready.ts-pk-pad:not(.ts-pk-left) .entry-content{padding-right:var(--pk-clear)}html:not(.ts-pk-left) .ts-pk-top{padding-right:44px}   /* the strip's text stays clear of the rail while it is still in the page */
html.ts-pk-ready .entry-content .ts-jump{display:none!important}   /* Ryan, 2026-11-08: "You can remove the in-page links beneath header." The strip, rail and index replace the row; without JavaScript (no ts-pk-ready) it still shows and works */
html.ts-pk-ready #ts-section-guide-trigger,html.ts-pk-ready #ts-section-guide-popover{display:none!important}   /* only once the rail is there */
html.ts-pk-ready .entry-content [id]{scroll-margin-top:calc(var(--pk-head,0px) + var(--pk-strip,0px) + 12px)}   /* plain in-page links land below the pinned header and the strip */
@media (prefers-reduced-motion:reduce){.ts-pk *,.ts-pk-top,.ts-pk-nav,.ts-pk-card{transition:none!important}.ts-pk-card,.ts-pk-card.is-on{transform:none}}
/* theme content rules (.entry-content ol, li, a, p) must not reach these parts, which sit inside the content for keyboard order */
html .ts-pk ol,html .ts-pk li{margin:0;padding:0;list-style:none;text-indent:0}
html .ts-pk .ts-pk-rail{padding:8px 0}
html .ts-pk a{text-decoration:none!important;border:0!important;box-shadow:none!important;background:none!important}   /* staging39: a more specific theme rule underlined these links */
html .ts-pk.ts-pk-top p{margin:0 0 .35rem}
/* left-hand option: everything mirrored to the left edge */
html.ts-pk-left .ts-pk-nav{right:auto;left:max(2px,env(safe-area-inset-left))}
html.ts-pk-left .ts-pk-label{right:auto;left:48px}
html.ts-pk-left .ts-pk-you{right:auto;left:28px}
html.ts-pk-left .ts-pk-card{right:auto;left:58px;transform:translateX(-10px) scale(.98)}
html.ts-pk-left .ts-pk-card.is-on{transform:none}
html.ts-pk-left .ts-pk-tip{right:auto;left:58px}
html.ts-pk-ready.ts-pk-pad.ts-pk-left .entry-content{padding-left:var(--pk-clear)}html.ts-pk-left .ts-pk-top{padding-left:44px}   /* the right side keeps the theme's own spacing */
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
/* index icon: always above the rail, drawn in the page's own colours (no box, border or shadow); hold it to fan out every section name */
html .ts-pk .ts-pk-ix{position:absolute;right:0;bottom:100%;width:44px;height:44px;margin:0 0 8px;padding:0;border:0;border-radius:50%;background:transparent;color:var(--pk-muted);box-shadow:none;cursor:pointer;touch-action:none;-webkit-touch-callout:none;-webkit-user-select:none;user-select:none;-webkit-tap-highlight-color:transparent;transition:color .2s,transform .2s var(--pk-ease)}
html .ts-pk .ts-pk-ix:hover,html .ts-pk .ts-pk-ix:focus{background:transparent;color:var(--pk-ink);box-shadow:none}   /* nothing sits under the icon (the text column stops before it), so no background: it takes the page's own */
html .ts-pk .ts-pk-ix:focus-visible{outline:3px solid var(--pk-accent);outline-offset:2px}
html .ts-pk .ts-pk-nav.is-index .ts-pk-ix{color:var(--pk-on);transform:scale(.92)}
.ts-pk-ix svg{display:block;width:22px;height:22px;margin:auto;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;pointer-events:none}
html.ts-pk-left .ts-pk .ts-pk-ix{right:auto;left:0}
.ts-pk-fan{position:absolute;inset:0;pointer-events:none}
html .ts-pk .ts-pk-fan li{position:absolute;right:52px;top:0;display:flex;align-items:center;gap:.45rem;max-width:min(64vw,260px);margin:-17px 0 0;padding:.42rem .8rem .42rem .55rem;border-radius:999px;background:var(--pk-ink);color:var(--pk-panel);font-size:.86rem;font-weight:700;line-height:1.2;white-space:nowrap;opacity:0;box-shadow:0 8px 20px -12px rgba(0,0,0,.55);transform:translateX(26px) scale(.55);transform-origin:right center;transition:opacity .2s,transform .3s var(--pk-ease);transition-delay:var(--d-close,0ms)}
html .ts-pk .ts-pk-fan li span{overflow:hidden;text-overflow:ellipsis}
html .ts-pk .ts-pk-fan li b{display:grid;place-items:center;flex:0 0 auto;width:1.45rem;height:1.45rem;margin:0;border-radius:50%;background:rgba(255,255,255,.14);font-size:.72rem}
html .ts-pk .ts-pk-fan li.is-done b{background:var(--pk-on);color:var(--pk-on-text)}
html .ts-pk .ts-pk-fan li.is-now{background:var(--pk-on);color:var(--pk-on-text)}
html .ts-pk .ts-pk-fan li.is-now b{background:rgba(0,0,0,.16);color:inherit}
html .ts-pk .ts-pk-nav.is-index .ts-pk-fan li{opacity:1;transform:none;transition-delay:var(--d-open,0ms)}
html.ts-pk-left .ts-pk .ts-pk-fan li{right:auto;left:52px;transform:translateX(-26px) scale(.55);transform-origin:left center}
html.ts-pk-left .ts-pk .ts-pk-nav.is-index .ts-pk-fan li{transform:none}
.ts-pk-nav.is-index .ts-pk-label{opacity:0}
@media (prefers-reduced-motion:reduce){html .ts-pk .ts-pk-fan li,html .ts-pk .ts-pk-ix{transition-duration:.01ms!important;transition-delay:0ms!important}}
/* the site header stays on top (Ryan, 2026-11-08: "make the header persistent too"); not on short landscape screens, where header, strip and rail would not fit */
@media (min-height:501px){
html.ts-pk-ready #masthead{position:sticky;top:0;z-index:96}
html.ts-pk-ready body.admin-bar #masthead{top:var(--wp-admin--admin-bar--height,32px)}
html.ts-pk-ready #masthead .ast-mobile-header-content{max-height:calc(100vh - var(--pk-head,0px));max-height:calc(100dvh - var(--pk-head,0px));overflow-y:auto}
html.ts-pk-ready body.admin-bar #masthead .ast-mobile-header-content{max-height:calc(100dvh - var(--pk-head,0px) - var(--wp-admin--admin-bar--height,32px))}   /* the phone menu still opens fully from the pinned header */
}
@media (min-height:501px) and (max-width:600px){html.ts-pk-ready body.admin-bar #masthead{top:0}}
/* the stack fits between the strip and the bottom of the screen: it gives up detail in steps (fit1-fit3), never the open box's name and "Tap to go there" */
html .ts-pk .ts-pk-card.fit1 .shot>span,html .ts-pk .ts-pk-card.fit1 .here{display:none}
html .ts-pk .ts-pk-card.fit1 .shot em{margin:0}
html .ts-pk .ts-pk-card.fit2 .shot{display:none}
html .ts-pk .ts-pk-card.fit3 .open .d{display:none}
html .ts-pk .ts-pk-card.fit3 .row{min-height:26px;padding:.1rem .5rem;font-size:.75rem}
html .ts-pk .ts-pk-card.fit3 .row b{width:1.2rem;height:1.2rem}
html .ts-pk .ts-pk-card.fit3 .open{padding:.45rem .7rem}
html .ts-pk .ts-pk-card .more{display:none;margin:.2rem .3rem;font-size:.72rem;font-weight:700;color:var(--pk-muted);text-align:center}
html .ts-pk .ts-pk-card.fit4 .row:not(.near){display:none}
html .ts-pk .ts-pk-card.fit4 .more{display:block}
html .ts-pk .ts-pk-card.fit5 .row,html .ts-pk .ts-pk-card.fit5 .more{display:none}
html .ts-pk .ts-pk-card.fit5 .open small,html .ts-pk .ts-pk-card.fit5 .side{display:none}
html .ts-pk .ts-pk-card.fit5 .open > b{font-size:.95rem;margin-top:0}
html .ts-pk .ts-pk-card.fit5 .hint{margin-top:.3rem;padding-top:.3rem}   /* very short screens or very large text: the open box keeps only its name and "Tap to go there" */
html .ts-pk .ts-pk-card.ts-pk-stack{overflow-y:auto;overscroll-behavior:contain}   /* last resort: the card scrolls, and fit() brings the open box into view */
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

    // the parts: top strip (display only), rail with its index icon, card
    var segs = null;
    var strip = document.createElement('div'); strip.className = 'ts-pk ts-pk-top'; strip.setAttribute('aria-hidden', 'true');
    strip.innerHTML = '<div class="in"><p><b></b><span></span></p><ol>' + S.map(function () { return '<li><i></i></li>'; }).join('') + '</ol></div>';
    row.parentNode.insertBefore(strip, row.nextSibling);   // in the page, right under the row; it sticks to the top as you read (display only)
    var box = document.createElement('div'); box.className = 'ts-pk';
    box.innerHTML = '<nav class="ts-pk-nav" aria-label="Sections on this page"><button type="button" class="ts-pk-ix" aria-expanded="false" aria-controls="ts-pk-fan" aria-label="Show all sections (press and hold)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 7h12M6 12h9M6 17h6"/></svg></button>' +
      '<ol class="ts-pk-fan" id="ts-pk-fan" aria-label="All sections" aria-hidden="true">' + S.map(function (s, i) { return '<li><b>' + (i + 1) + '</b><span>' + esc(s.label) + '</span></li>'; }).join('') + '</ol><ol class="ts-pk-rail">' + S.map(function (s) { return '<li><a href="' + esc(s.href) + '" aria-label="' + esc(s.label) + '" draggable="false"><i></i></a></li>'; }).join('') + '</ol><span class="ts-pk-you" aria-hidden="true"></span><span class="ts-pk-label" aria-hidden="true"></span></nav>' +
      '<div class="ts-pk-card" role="group" aria-label="Section preview" tabindex="-1"></div>' +
      '<div class="ts-pk-tip" role="note" hidden><p>Slide your thumb along this bar to preview each section. Lift, then tap the preview to go there. Hold the icon above it to see them all, and slide down onto the bar to preview one.</p><div class="row"><button type="button" class="ok">Got it</button><button type="button" class="flip"></button></div></div>';
    var host = strip.parentNode, okHost = true;
    for (var an = host; an && an !== document.documentElement; an = an.parentElement) { var cs = getComputedStyle(an); if (cs.transform !== 'none' || cs.filter !== 'none' || cs.perspective !== 'none' || (cs.backdropFilter || cs.webkitBackdropFilter || 'none') !== 'none' || /transform|filter|perspective/.test(cs.willChange || '') || (cs.containerType && cs.containerType !== 'normal') || /paint|layout|strict|content/.test(cs.contain || '')) { okHost = false; break; } }   // any of these would make position:fixed relative to that ancestor
    if (okHost) host.insertBefore(box, strip.nextSibling); else document.body.appendChild(box);   // keyboard order: row, strip, rail, then the sections
    var top = strip, topSegs = [].slice.call(top.querySelectorAll('li')), nav = box.querySelector('.ts-pk-nav'), rail = nav.querySelector('.ts-pk-rail'),
      you = nav.querySelector('.ts-pk-you'), label = nav.querySelector('.ts-pk-label'), card = box.querySelector('.ts-pk-card');
    segs = [].slice.call(rail.children);
    var tip = box.querySelector('.ts-pk-tip'), ixBtn = nav.querySelector('.ts-pk-ix'), fan = nav.querySelector('.ts-pk-fan'), fanItems = [].slice.call(fan.children);
    root.classList.add('ts-pk-ready');
    // the pinned header: the strip sticks right under it (its height changes with the phone menu, the logo and the admin bar)
    var head = document.getElementById('masthead');
    function measureHead() {
      var pinned = head && getComputedStyle(head).position === 'sticky';
      var menu = head ? head.querySelector('.ast-mobile-header-content') : null;   // the open phone menu lives inside the header: never count it (counting it made the menu's
      var bar = pinned ? head.offsetHeight - (menu ? menu.offsetHeight : 0) : 0;     // max-height depend on itself, so it flickered between two sizes)
      root.style.setProperty('--pk-head', Math.max(0, bar) + 'px');
      root.style.setProperty('--pk-strip', strip.offsetHeight + 'px');
    }
    measureHead();
    if (head && 'ResizeObserver' in window) new ResizeObserver(measureHead).observe(head);
    window.addEventListener('resize', measureHead);
    // the text column stops 6px before the rail and its icon, whatever margin the theme gives the content at this width (measured, not guessed);
    // the theme's own padding stays whenever it is already enough
    function clearRail() {
      var left = root.classList.contains('ts-pk-left'), side = left ? 'paddingLeft' : 'paddingRight';
      root.classList.remove('ts-pk-pad');   // start from the theme's own padding (no paint happens in between)
      var base = parseFloat(getComputedStyle(content)[side]) || 0, need = base;
      function apply(n) { if (n > base + 0.5) { root.style.setProperty('--pk-clear', n + 'px'); root.classList.add('ts-pk-pad'); } else root.classList.remove('ts-pk-pad'); }
      // measure where the text blocks really are (they can be inset from the column or wider than it) and add only what is missing
      for (var round = 0; round < 3; round++) {
        var edge = left ? Infinity : -Infinity;
        [].forEach.call(content.querySelectorAll('p,li,h1,h2,h3,h4,h5,h6,blockquote,figcaption,dt,dd,td,th'), function (el) {
          if (el.closest('.ts-pk') || el.closest('.ts-jump')) return;
          var r = el.getBoundingClientRect(); if (!r.width) return;
          var cs = getComputedStyle(el), e2 = left ? r.left + (parseFloat(cs.paddingLeft) || 0) : r.right - (parseFloat(cs.paddingRight) || 0);
          edge = left ? Math.min(edge, e2) : Math.max(edge, e2);
        });
        if (!isFinite(edge)) break;
        var nr = nav.getBoundingClientRect(), over = left ? (nr.right + 6) - edge : edge - (nr.left - 6);
        if (!(over > 0.5) || need >= 80) break;
        need = Math.min(80, need + Math.ceil(over)); apply(need);   // capped, so an unusually wide element cannot squeeze the page
      }
    }
    // at most once per frame, and on resize only when the width really changed (phone toolbars resize the page while scrolling)
    var clearQ = 0, lastW = -1;
    function scheduleClear(force) { if (!force && window.innerWidth === lastW) return; lastW = window.innerWidth; if (clearQ) return; clearQ = requestAnimationFrame(function () { clearQ = 0; clearRail(); }); }
    root.classList.toggle('ts-pk-left', store('ts-pk-side') === 'left');   // the saved side first, so the first measurement is for the right side (Kiro, third review)
    lastW = window.innerWidth; clearRail(); window.addEventListener('resize', function () { scheduleClear(false); });

    // left-hand option (remembered on this device)
    function store(k, v) { try { if (v === undefined) return localStorage.getItem(k); localStorage.setItem(k, v); } catch (e) { return null; } return null; }
    function sideName() { return root.classList.contains('ts-pk-left') ? 'right' : 'left'; }   // the side a flip would move to
    function setSide(left) { root.classList.toggle('ts-pk-left', left); store('ts-pk-side', left ? 'left' : 'right'); clearRail(); tip.querySelector('.flip').textContent = 'Use it on the ' + sideName(); lengths(); ask(); }
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
    var cur = -1;
    function line() { return Math.max(window.innerHeight * 0.25, (parseFloat(getComputedStyle(top).top) || 0) + top.offsetHeight + 24); }   // below the pinned header and the strip
    function bounds(i) { var h = el(i), nx = el(i + 1), a = h ? h.getBoundingClientRect().top : 0, b = nx ? nx.getBoundingClientRect().top : content.getBoundingClientRect().bottom; return [a, b]; }
    function part(i) { var b = bounds(i); return Math.max(0, Math.min(1, (line() - b[0]) / Math.max(1, b[1] - b[0]))); }
    function fill(i) { return i < cur ? 1 : (i === cur ? part(i) : 0); }
    function whole() { var a = bounds(0)[0], b = bounds(S.length - 1)[1]; return Math.max(0, Math.min(1, (line() - a) / Math.max(1, b - a))); }
    function lengths() { var hs = S.map(function (s, i) { var b = bounds(i); return Math.max(1, b[1] - b[0]); }), avg = hs.reduce(function (x, y) { return x + y; }, 0) / S.length; S.forEach(function (s, i) { s.len = hs[i] / avg; });
      if (segs) { segs.forEach(function (li, k) { li.style.setProperty('--len', S[k].len.toFixed(2)); }); topSegs.forEach(function (li, k) { li.style.setProperty('--len', S[k].len.toFixed(2)); }); } }

    // a jump: the heading lands just below the strip (also under the admin bar)
    var glow = { el: null, t: 0 };
    function below() { return (parseFloat(getComputedStyle(top).top) || 0) + top.offsetHeight + 12; }   // where the stuck strip ends
    function jumpTo(i) {   // Ryan, 2026-11-08: no "Back to ..." button
      var h = el(i); if (!h) return;
      window.scrollTo({ top: h.getBoundingClientRect().top + window.scrollY - below(), behavior: calm.matches ? 'auto' : 'smooth' });
      if (glow.el) { glow.el.classList.remove('ts-pk-flash'); clearTimeout(glow.t); }
      glow.el = h; h.classList.remove('ts-pk-unflash'); h.classList.add('ts-pk-flash'); glow.t = setTimeout(function () { h.classList.add('ts-pk-unflash'); h.classList.remove('ts-pk-flash'); glow.el = null; }, 1200);
      if (!h.hasAttribute('tabindex')) h.setAttribute('tabindex', '-1');
      h.focus({ preventScroll: true });
      try { history.replaceState(null, '', S[i].href); } catch (e) {}   // no history entry per jump (the browser's Back would only change the address); the in-page Back returns
    }
    // the row at the top of the page uses the same jump (otherwise the theme's own smooth scroll hides the heading under the strip)
    row.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('a[href^="#"]') : null; if (!a || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var i = links.indexOf(a); if (i < 0 || !el(i)) return; e.preventDefault(); e.stopPropagation(); jumpTo(i);
    }, true);

    // any other in-page link in the content (a "see the questions below" link): the theme's own smooth scroll would hide the heading under the pinned header and strip
    content.addEventListener('click', function (e) {
      var a = e.target.closest ? e.target.closest('a[href^="#"]') : null;
      if (!a || a.closest('.ts-pk') || a.closest('.ts-jump') || e.defaultPrevented || e.button || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      var id = a.getAttribute('href').slice(1); try { id = decodeURIComponent(id); } catch (err) {}
      var t = id ? document.getElementById(id) : null; if (!t || !content.contains(t)) return;
      e.preventDefault(); e.stopPropagation();
      window.scrollTo({ top: t.getBoundingClientRect().top + window.scrollY - below(), behavior: calm.matches ? 'auto' : 'smooth' });
      try { history.replaceState(null, '', '#' + id); } catch (err) {}
    }, true);

    // the peek card
    function rowHTML(k, near) { return '<button type="button" class="row' + (near ? ' near' : '') + '" data-k="' + k + '" aria-label="Section ' + (k + 1) + ': ' + esc(S[k].heading) + ' (show its preview)"><b>' + (k + 1) + '</b><span>' + esc(S[k].heading) + '</span></button>'; }
    function cardHTML(i, pinned) {
      var above = '', below = '';
      for (var k = 0; k < S.length; k++) { if (k < i) above += rowHTML(k, k === i - 1); else if (k > i) below += rowHTML(k, k === i + 1); }
      var upN = Math.max(0, i - 1), downN = Math.max(0, S.length - i - 2);   // shown only when the far rows are folded away (fit4)
      return (upN ? '<span class="more" aria-hidden="true">+' + upN + ' more above</span>' : '') + above + '<div class="open" tabindex="-1" aria-label="Section ' + (i + 1) + ' preview: ' + esc(S[i].label) + '">' + openHTML(i, pinned) + '</div>' + below + (downN ? '<span class="more" aria-hidden="true">+' + downN + ' more below</span>' : '');
    }
    function openHTML(i, pinned) {
      var s = S[i], here = i === cur ? 'You are reading this section.' : (cur < 0 ? '' : 'You are in: ' + S[cur].label);
      return '<button type="button" class="x" aria-label="Close the preview">×</button><small>Section ' + (i + 1) + ' of ' + S.length + ' · ' + s.mins + ' min</small><b>' + esc(s.label) + '</b>' + (s.desc ? '<span class="d">' + esc(s.desc) + '</span>' : '') + '<span class="here">' + esc(here) + '</span>' +
        '<span class="shot"><strong>' + esc(s.heading) + '</strong>' + (s.excerpt ? '<em>' + esc(s.excerpt) + '</em>' : '') + '<span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span></span>' +
        '<span class="hint"><span>' + (pinned ? 'Tap to go there' : 'Lift to keep this preview') + '</span>' + (pinned ? '<button type="button" class="side" aria-label="Move the section bar to the ' + sideName() + ' side">⇆ ' + (sideName() === 'left' ? 'Left' : 'Right') + '</button>' : '') + '</span>';
    }
    function hover(i) { segs.forEach(function (li, k) { li.classList.toggle('is-hover', k === i); }); }
    function stuckBottom() { return (parseFloat(getComputedStyle(top).top) || 0) + top.offsetHeight; }   // where the strip ends once it sticks (also before it has)
    function fit() {   // drop detail in steps until the whole stack fits between the stuck strip and the bottom of the screen (and within the card's own max-height)
      card.classList.remove('fit1', 'fit2', 'fit3', 'fit4', 'fit5');
      var avail = window.innerHeight - Math.max(8, stuckBottom() + 8) - 12, mh = parseFloat(getComputedStyle(card).maxHeight);
      if (mh > 0) avail = Math.min(avail, mh);
      // fit1-3 trim the open box; fit4 folds the far rows into "+N more"; fit5 leaves the open box alone
      ['fit1', 'fit2', 'fit3', 'fit4', 'fit5'].some(function (c) { if (card.scrollHeight <= avail + 0.5) return true; card.classList.add(c); return false; });
      var o = card.querySelector('.open'); if (o && card.scrollHeight > card.clientHeight + 1) card.scrollTop = Math.max(0, o.offsetTop - 4);   // never hidden
    }
    function place(y) {   // the open box beside the thumb; never past the bottom of the screen, then never above the strip
      var h = card.offsetHeight, o = card.querySelector('.open'), mid = o ? o.offsetTop + o.offsetHeight / 2 : h / 2;
      var lo = Math.max(8, stuckBottom() + 8), hi = window.innerHeight - h - 12;
      card.style.top = Math.max(Math.min(lo, hi), Math.min(hi, y - mid)) + 'px';
    }
    var shown = '', clear = 0;
    function hideCard() { card.className = 'ts-pk-card'; shown = ''; hover(-1); nav.classList.remove('is-active'); clearTimeout(clear); clear = setTimeout(function () { if (!shown) card.innerHTML = ''; }, 220); }   // empty it after the fade
    function show(i, y, pinned) {
      var key = i + '|' + (pinned ? 1 : 0) + '|' + cur;
      if (key !== shown) { clearTimeout(clear); card.className = 'ts-pk-card ts-pk-stack is-on' + (pinned ? ' is-pinned' : ''); card.innerHTML = cardHTML(i, pinned); card.dataset.i = i; nav.classList.add('is-active'); hover(i); shown = key; fit(); }
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
      var row = target.closest('.row');
      if (row) {   // a closed box: open it in the stack (no jump), where it was
        var k = +row.getAttribute('data-k'), rr = row.getBoundingClientRect(), f = card.contains(document.activeElement);
        show(k, rr.top + rr.height / 2, true); hover(k); if (f) { var no = card.querySelector('.open'); (no || card).focus({ preventScroll: true }); } return;   // screen readers hear the new section
      }
      var i = +card.dataset.i, hadFocus = card.contains(document.activeElement); hideCard();
      if (target.closest('.side')) { setSide(sideName() === 'left'); if (hadFocus) focusSeg(i); return; }
      if (target.closest('.x')) { if (hadFocus) focusSeg(i); return; }   // closing keeps keyboard users on the rail
      jumpTo(i);
    }
    var cdn = null, cardTapAt = -1e9;
    card.addEventListener('pointerdown', function (e) { cdn = card.classList.contains('is-pinned') && e.pointerType !== 'mouse' && e.isPrimary ? { id: e.pointerId, x: e.clientX, y: e.clientY } : null; });
    card.addEventListener('pointerup', function (e) {
      var d = cdn; cdn = null; if (!d || e.pointerId !== d.id || Math.abs(e.clientX - d.x) > 10 || Math.abs(e.clientY - d.y) > 10) return;
      cardTapAt = performance.now(); swallowNextClick(true); activate(e.target);   // the click that follows would land on whatever is under the card once it closes
    });
    card.addEventListener('pointercancel', function () { cdn = null; });
    card.addEventListener('click', function (e) { if (performance.now() - cardTapAt < 700) { e.preventDefault(); return; } activate(e.target); });
    card.addEventListener('keydown', function (e) { if (e.key !== 'Enter' || !card.classList.contains('is-pinned') || e.target.closest('button')) return; var i = +card.dataset.i; hideCard(); jumpTo(i); });   // Enter on × or the side button is that button's own click
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
    function tipSeen() { if (!tipDone) { tipDone = true; store('ts-pk-tip', 'seen'); } hideTip(); }   // someone who already uses the rail or the icon needs no hint
    rail.addEventListener('pointerdown', function (e) { if (e.button > 0 || !e.isPrimary || g) return; tipSeen(); try { rail.setPointerCapture(e.pointerId); } catch (err) {} g = { id: e.pointerId, i: pickAt(e.clientY), y: e.clientY }; show(g.i, g.y, false); e.preventDefault(); });
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
    var lastLabel = '', lastRove = -2, tipWait = 0;
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
      if (cur !== lastRove && cur > -1 && !rail.contains(document.activeElement)) { rove(cur); lastRove = cur; }   // only when the section changes
      if (ixOpen) fanLayout();   // the fan stays current if the page moves while it is open (keyboard toggle)
      if (!tipDone && !tipWait && cur > -1) tipWait = setTimeout(function () { tipWait = 0; if (!tipDone && !card.classList.contains('is-on') && !ixOpen && !g && ixPress === null) maybeTip(); }, 1500);   // after 1.5 s of reading, never over a preview or the fan
    }

    // the index icon: hold it and every section name fans out beside its piece of the rail; lift and they slide back (a click without a press toggles)
    var ixOpen = false, ixPress = null, ixPressedAt = -1e9;
    function fanLayout() {
      var nr = nav.getBoundingClientRect(), n = S.length;
      segs.forEach(function (li, k) {
        var r = li.getBoundingClientRect(), it = fanItems[k];
        it.style.top = (r.top + r.height / 2 - nr.top) + 'px';
        it.style.setProperty('--d-open', (k * 35) + 'ms'); it.style.setProperty('--d-close', ((n - 1 - k) * 25) + 'ms');   // out top to bottom, back bottom to top
        it.classList.toggle('is-now', k === cur); it.classList.toggle('is-done', k < cur); it.firstChild.textContent = k < cur ? '✓' : String(k + 1);
      });
    }
    function setIndex(on) {
      if (on === ixOpen) return; ixOpen = on;
      if (on) { hideCard(); hideTip(); fanLayout(); buzz(); }
      nav.classList.toggle('is-index', on); ixBtn.setAttribute('aria-expanded', String(on)); fan.setAttribute('aria-hidden', String(!on));
    }
    ixBtn.addEventListener('contextmenu', function (e) { e.preventDefault(); });
    // press the icon: the names fan out. Without lifting, slide down onto the rail: the fan closes and the rail's preview takes over (Ryan, 2026-11-08);
    // lifting there keeps the preview, exactly as a press that started on the rail
    var handed = null;   // the rail gesture the icon press turned into: { i, y }
    ixBtn.addEventListener('pointerdown', function (e) { if (e.button > 0 || !e.isPrimary) return; tipSeen(); ixPress = e.pointerId; handed = null; try { ixBtn.setPointerCapture(e.pointerId); } catch (err) {} setIndex(true); e.preventDefault(); });
    ixBtn.addEventListener('pointermove', function (e) {
      if (ixPress === null || e.pointerId !== ixPress) return;
      var rr = rail.getBoundingClientRect();
      if (!handed) {
        if (e.clientY < rr.top + 4) return;   // still on (or around) the icon: the fan stays out
        setIndex(false); handed = { i: pickAt(e.clientY), y: e.clientY }; buzz(); show(handed.i, handed.y, false); return;
      }
      var i = pickAt(e.clientY); handed.y = e.clientY; if (i !== handed.i) { handed.i = i; buzz(); } show(handed.i, handed.y, false);
    });
    function ixRelease(e) {
      if (ixPress === null || e.pointerId !== ixPress) return;
      ixPress = null; ixPressedAt = performance.now();
      var h = handed; handed = null;
      if (h && e.type === 'pointerup') { show(h.i, h.y, true); return; }   // lifted on the rail: the preview stays, tap it to go there
      if (h) { hideCard(); return; }                                           // cancelled while on the rail
      setIndex(false);
    }
    ixBtn.addEventListener('pointerup', ixRelease); ixBtn.addEventListener('pointercancel', ixRelease); ixBtn.addEventListener('lostpointercapture', function (e) { if (ixPress !== null && e.pointerId === ixPress) setTimeout(function () { ixRelease({ pointerId: e.pointerId, type: 'pointercancel' }); }, 0); });
    ixBtn.addEventListener('click', function (e) { e.preventDefault(); if (performance.now() - ixPressedAt < 600) { ixPressedAt = -1e9; return; } setIndex(!ixOpen); });
    document.addEventListener('pointerdown', function (e) { if (ixOpen && ixPress === null && !ixBtn.contains(e.target)) { setIndex(false); swallowNextClick(false); } }, true);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && ixOpen) { setIndex(false); ixBtn.focus(); } });

    // first-time hint: once per visitor, when they start reading; it goes away by itself, on "Got it", or when they touch the rail
    var tipDone = store('ts-pk-tip') === 'seen' || /^#./.test(location.hash), tipT = 0;   // not for visitors who arrived at a section by link
    function maybeTip() {
      tipDone = true; store('ts-pk-tip', 'seen');
      var rr = rail.getBoundingClientRect(); tip.hidden = false; tip.style.top = Math.max(12, rr.top + rr.height / 2 - tip.offsetHeight / 2) + 'px';
      requestAnimationFrame(function () { tip.classList.add('is-on'); }); nav.classList.add('is-hinting');
      tipT = setTimeout(autoHide, 9000);
    }
    function autoHide() { if (tip.contains(document.activeElement)) { tipT = setTimeout(autoHide, 3000); return; } hideTip(); }   // never take the hint away from someone using it with the keyboard
    function hideTip() { clearTimeout(tipT); clearTimeout(tipWait); tipWait = 0; if (tip.hidden) return; var had = tip.contains(document.activeElement); tip.classList.remove('is-on'); nav.classList.remove('is-hinting'); setTimeout(function () { tip.hidden = true; }, 260); if (had) focusSeg(Math.max(cur, 0)); }
    tip.querySelector('.ok').addEventListener('click', hideTip);
    tip.querySelector('.flip').addEventListener('click', function () { setSide(sideName() === 'left'); var rr = rail.getBoundingClientRect(); tip.style.top = Math.max(12, rr.top + rr.height / 2 - tip.offsetHeight / 2) + 'px'; nav.classList.remove('is-hinting'); void nav.offsetWidth; nav.classList.add('is-hinting'); clearTimeout(tipT); tipT = setTimeout(autoHide, 6000); });
    function ask() { if (!ticking) { ticking = true; window.requestAnimationFrame(draw); } }
    var still = 0;
    window.addEventListener('scroll', function () { ask(); nav.classList.add('is-moving'); clearTimeout(still); still = setTimeout(function () { nav.classList.remove('is-moving'); }, 900); }, { passive: true });   // the section name shows beside the rail while the page scrolls
    window.addEventListener('resize', function () { lengths(); ask(); if (card.classList.contains('is-on')) { fit(); place(card.getBoundingClientRect().top + card.offsetHeight / 2); } });   // rotation or zoom with a card open: fit it again
    var lastCW = -1;
    if ('ResizeObserver' in window) new ResizeObserver(function (en) { lengths(); ask(); var w = Math.round(en[0].contentRect.width); if (w !== lastCW) { lastCW = w; scheduleClear(true); } }).observe(content);   // re-measure the rail clearance only when the width changes (an FAQ opening changes the height every frame)   // FAQ answers or late fonts move block edges   // FAQ answers opening change the lengths

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
