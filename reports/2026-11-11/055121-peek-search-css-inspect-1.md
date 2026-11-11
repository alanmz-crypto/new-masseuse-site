# Site op `peek-search-css-inspect-1`

| | |
|---|---|
| operation | `inspect` on `staging39` |
| query | `css`  |
| lane | ops (site-ops.yml) |
| commit | `558c13a27467356ec984199b15acad92fd4a6133` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42968382007 |
| started | 2026-11-11 05:50:58 UTC |

## inspect staging39 css : PASS

_(last 400 of 1330 lines; the whole log is in the run's artifact)_
```text
.ts-corp h3 {
  margin: 0 0 0.4em;
  font-size: 1.4rem;
  font-weight: 400;
  line-height: 1.2;
}
.ts-corp p,
.ts-corp li {
  color: var(--ts-text);
}
.ts-corp__eyebrow {
  margin: 0 0 0.9rem;
  font-family: var(--ts-sans);
  font-size: 0.78rem;
  font-weight: 700;
  letter-spacing: 0.16em;
  line-height: 1.4;
  text-transform: uppercase;
  color: var(--ts-accent-deep);
}
.ts-corp__hero {
  padding: clamp(2.2rem, 6vw, 5rem) clamp(1.25rem, 5vw, 4.5rem);
  background: var(--ts-stone);
  border-radius: 6px;
}
.ts-corp__lead {
  margin: 0 0 1rem;
  font-family: var(--ts-serif);
  font-size: clamp(1.3rem, 2.2vw, 1.75rem);
  line-height: 1.3;
  color: var(--ts-ink);
}
.ts-corp__hero p:not(.ts-corp__eyebrow):not(.ts-corp__lead):not(.ts-corp__fine) {
  max-width: 40rem;
  font-size: 1.08rem;
}
.ts-corp__actions {
  margin: 1.6rem 0 0;
  gap: 0.75rem;
}
.ts-corp__fine {
  max-width: 40rem;
  margin: 1.1rem 0 0;
  font-size: 0.93rem;
  color: var(--ts-muted);
}
.ts-corp__glance,
.ts-corp__steps,
.ts-corp__blocks {
  gap: 1rem;
}
.ts-corp__glance > .wp-block-column,
.ts-corp__steps > .wp-block-column,
.ts-corp__blocks > .wp-block-column {
  padding: 1.5rem;
  background: var(--ts-panel);
  border: 1px solid var(--ts-rule);
  border-radius: 4px;
}
.ts-corp__glance p,
.ts-corp__steps p:not(.ts-corp__num),
.ts-corp__blocks p {
  margin: 0;
}
.ts-corp__glance .ts-corp__card-title {
  margin: 0 0 0.4em;
  font-family: var(--ts-serif);
  font-size: 1.4rem;
  line-height: 1.2;
  color: var(--ts-ink);
}
.ts-corp__num {
  margin: 0 0 0.5rem;
  font-family: var(--ts-serif);
  font-size: 1.6rem;
  line-height: 1;
  color: var(--ts-accent-deep);
}
.ts-corp__section > p {
  max-width: 44rem;
  font-size: 1.05rem;
}
.ts-corp__section > p + p,
.ts-corp__section > .ts-corp__note + p {
  margin-top: 1.1rem;
}
.ts-corp__note {
  max-width: 44rem;
  margin: 1.4rem 0;
  padding: 1rem 1.25rem;
  background: var(--ts-panel);
  border: 1px solid var(--ts-rule);
  border-left: 3px solid var(--ts-accent);
  border-radius: 4px;
}
.ts-corp__note p {
  margin: 0;
}
.ts-corp__rate {
  max-width: 44rem;
  margin: 0 0 1.4rem;
  padding: 1.25rem 1.5rem;
  background: var(--ts-stone);
  border-radius: 4px;
}
.ts-corp__rate p {
  margin: 0;
}
.ts-corp__rate .ts-corp__rate-line {
  font-family: var(--ts-serif);
  font-size: 1.5rem;
  color: var(--ts-ink);
}
.ts-corp__rate .ts-corp__rate-line strong {
  font-size: 2.4rem;
  font-weight: 400;
  line-height: 1.1;
}
.ts-corp__rate .ts-corp__rate-note {
  margin-top: 0.4rem;
  font-size: 0.95rem;
}
.ts-corp__booking > p {
  margin-top: 1.4rem;
}
.ts-corp__section ul {
  max-width: 44rem;
  margin: 0.4rem 0 1.1rem;
  padding-left: 1.2rem;
}
.ts-corp__section li {
  margin: 0.3rem 0;
  font-size: 1.05rem;
}
.ts-corp__cta {
  padding: clamp(1.8rem, 4vw, 3.2rem) clamp(1.25rem, 5vw, 3.6rem);
  background: var(--ts-stone);
  border-radius: 6px;
}
.ts-corp__cta h2 {
  margin-bottom: 0.4em;
}
.ts-corp__cta > p:not(.ts-corp__eyebrow):not(.ts-corp__fine) {
  max-width: 40rem;
  font-size: 1.08rem;
}
.ts-corp .ts-corp__actions .wp-block-button__link:focus-visible {
  outline: 3px solid var(--ts-accent-deep);
  outline-offset: 3px;
}

/* 3. The picture in the Corporate & Group Massage row on the Services page. It is shown whole (the other rows crop theirs to a square), centred in its column like the others.
   (Spectra gives every block inside its containers width:100%, so the size is capped with max-width.) */
.ts-corp-pic {
  max-width: 200px;
  margin: 0 auto;
  justify-self: center;
}
.ts-corp-pic img {
  display: block;
  width: 100%;
  height: auto;
  aspect-ratio: 512 / 416;
  border-radius: 20px;
  box-shadow: 2px 5px 5px -1px rgba(21, 28, 38, 0.25);
}
@media (max-width: 976px) {
  .ts-corp-pic {
    max-width: 282px;
  }
  .ts-corp-pic img {
    border-radius: 16px;
  }
}
@media (max-width: 767px) {
  .ts-corp-pic {
    max-width: 360px;
  }
  .ts-corp-pic img {
    border-radius: 14px;
  }
}

/* 4. The "Corporate Massage" menu item (class ts-corp-nav) must not push the header or the footer menu into a layout they did not have before.
   Measured on the site: six items need about 633 px where five need 459 px. The header menu has 0.5 x width - 8 px, the footer menu at most 575 px (555 px at 1240).
   - Header, 1120-1349 px: the space between items is tightened so six items stay on one line (1350 px and wider need nothing).
   - Footer, 1240 px and wider, and 545-921 px: the space after each item is tightened so six items stay on one line (they cannot fit at the original spacing at any width).
   - Between 922 and 1119 px (header) and in the footer at 601-819 and 922-1239 px the menus do not have room, so they show the original five items exactly as before.
   The phone menu (921 px and below), the announcement strip and the Services row reach the page from every width. */
@media (min-width: 1120px) and (max-width: 1349px) {
  #ast-hf-menu-1 > .menu-item > .menu-link {
    padding-left: 0.5em;
    padding-right: 0.5em;
  }
}
@media (min-width: 1240px) {
  #astra-footer-menu > .menu-item > .menu-link {
    padding-right: 12px;
  }
}
@media (min-width: 545px) and (max-width: 921px) {
  #astra-footer-menu > .menu-item > .menu-link {
    padding-right: 8px;
  }
}
@media (min-width: 922px) and (max-width: 1119px) {
  #ast-hf-menu-1 > .ts-corp-nav {
    display: none;
  }
}
@media (min-width: 601px) and (max-width: 819px), (min-width: 922px) and (max-width: 1239px) {
  #astra-footer-menu > .menu-item.ts-corp-nav {
    display: none;
  }
}
/* ts-corp:end */

/* ts-home-corp:start (2026-10-25, Home: the Corporate and Group Massage card at the top of "The Massage I Offer") */
/* The same picture-frame rules the three existing cards have (a 6:5 frame, rounded, picture cropped to fill it; compact and centred when Spectra stacks the rows), for the new card's block id only. No colours, fonts or images of its own; nothing existing is edited. */
body.home .wp-block-uagb-container.uagb-block-e7a31c58 > figure.wp-block-image {
    width: 100%;
    margin: 0;
    overflow: hidden;
    border-radius: 16px;
}
body.home .wp-block-uagb-container.uagb-block-e7a31c58 > figure.wp-block-image img {
    display: block;
    width: 100%;
    height: auto;
    aspect-ratio: 6 / 5;
    object-fit: cover;
}
@media (max-width: 1024px) {
  body.home .wp-block-uagb-container.uagb-block-e7a31c58 > figure.wp-block-image {
    max-width: 320px;
    margin-inline: auto;
    justify-self: center;
  }
}
/* ts-home-corp:end */

/* TS REVIEW A START */
.entry-content .ts-review-next{box-sizing:border-box;max-width:1120px;margin:3rem auto 4rem;padding:clamp(1.5rem,5vw,3rem);background:var(--ts-panel);border:1px solid var(--ts-rule);border-radius:6px}
.entry-content .ts-review-eyebrow{color:var(--ts-accent-deep);font-size:.85rem;font-weight:600;letter-spacing:.12em;text-transform:uppercase;margin:0 0 1rem}
.entry-content .ts-review-next .wp-block-buttons,.entry-content .ts-review-contact .wp-block-buttons{display:flex;flex-wrap:wrap;gap:.8rem;margin-top:1.8rem}
.entry-content .ts-review-next .wp-block-button__link,.entry-content .ts-review-contact .wp-block-button__link{box-sizing:border-box;max-width:100%;white-space:normal;border:2px solid var(--ts-ink);border-radius:6px;padding:.8em 1.4em;color:var(--ts-ink);background:transparent;text-decoration:none}
.entry-content .ts-review-next a:focus-visible,.entry-content .ts-review-contact a:focus-visible,.entry-content .ts-review-other a:focus-visible{outline:3px solid var(--ts-accent-deep);outline-offset:3px}
.entry-content .ts-review-other{box-sizing:border-box;max-width:1120px;margin:2rem auto;padding:0 24px;line-height:1.8}
.entry-content .ts-review-contact{box-sizing:border-box;width:100%;margin:0;padding:40px;border:1px solid var(--ts-rule);border-radius:20px;background:var(--ts-panel);scroll-margin-top:90px}
.entry-content .ts-review-contact h2{font-size:clamp(22px,3vw,30px);margin-top:0}
.ts-corp__sticky{display:none}
@media(max-width:600px){
 .entry-content .ts-review-next{margin:2rem 24px}
 .entry-content .ts-review-contact{padding:24px;border-radius:14px}
 .ts-corp__sticky{display:flex;gap:.6rem;position:fixed;left:0;right:0;bottom:0;max-width:none;z-index:60;box-sizing:border-box;padding:.6rem .8rem calc(.6rem + env(safe-area-inset-bottom,0px));background:var(--ts-paper,#fff);border-top:1px solid var(--ts-rule)}
 .entry-content .ts-corp__sticky a{flex:1;display:flex;align-items:center;justify-content:center;min-width:0;min-height:44px;box-sizing:border-box;border:2px solid var(--ts-ink,#1c2531);border-radius:999px;background:transparent;color:var(--ts-ink,#1c2531);font-size:.85rem;line-height:1.25;font-weight:600;text-align:center;text-decoration:none;overflow-wrap:anywhere}
 .entry-content .ts-corp__sticky a:not(.wp-block-button__link):not(.uagb-buttons-repeater){color:var(--ts-ink,#1c2531);text-decoration:none}
 .ts-corp__sticky a:focus-visible{outline:3px solid var(--ts-accent-deep);outline-offset:2px}
 body:has(.ts-corp__sticky){padding-bottom:calc(4.6rem + env(safe-area-inset-bottom,0px))}
 body:has(.ts-corp__sticky) #ast-scroll-top{bottom:calc(5.2rem + env(safe-area-inset-bottom,0px))}
 html:has(.ts-corp__sticky){scroll-padding-bottom:5.5rem}
}
html[data-ts-theme="dark"] .ts-corp__sticky{background:#151C26}
/* TS REVIEW A END */


/* ts-review-c:start (2026-11-01, reviewed semantics/accessibility) */
/* Keep the Services H2 in heading navigation, without a grid slot or dimensions. */
body .entry-content #ts-services-heading{position:absolute!important;width:1px!important;height:1px!important;min-width:0!important;min-height:0!important;max-width:1px!important;max-height:1px!important;margin:-1px!important;padding:0!important;border:0!important;overflow:hidden!important;clip:rect(0,0,0,0)!important;clip-path:inset(50%)!important;white-space:nowrap!important}
body .entry-content #ts-services-heading::after{display:none!important}
/* Preserve the measured 24/22/20 px Spectra paragraph typography after the H3 tag swap. */
body .entry-content :is(.uagb-block-78b38aa2,.uagb-block-05fef95b,.uagb-block-084d247e,.uagb-block-f14b2a44,.uagb-block-6c30a7ff,.uagb-block-0422be24,.uagb-block-863c3724,.uagb-block-cf15aaf5,.uagb-block-b0f9a0b1,.uagb-block-d2b1c2d3,.uagb-block-f4d3e4f5,.uagb-block-b6f5a6b7,.uagb-block-2c1fc22f,.uagb-block-3e2e789f,.uagb-block-4fdc0b6f,.uagb-block-3b941aaf,.uagb-block-e6d24376,.uagb-block-0a8b3bc1,.uagb-block-eee8f105) > h3.uagb-heading-text{font-size:24px!important;font-weight:400;line-height:1.3;letter-spacing:normal;margin-top:0;margin-bottom:10px}
body .entry-content :is(.uagb-block-e6d24376,.uagb-block-0a8b3bc1,.uagb-block-eee8f105) > h3.uagb-heading-text{margin-bottom:0}
.ts-corp__glance h3.ts-corp__card-title{font-size:1.4rem;line-height:1.2;letter-spacing:normal;margin:0 0 .4em}
.ts-therapeutic-faq{min-width:0}
.ts-therapeutic-faq > .wp-block-uagb-faq{margin-top:0}
.ts-corp-band__mobile{display:none}
.uagb-block-2e703c12 .uagb-google-map__iframe{display:block;width:100%;height:500px;border:0}
.ts-review-404 ul{display:flex;flex-wrap:wrap;justify-content:center;gap:.6rem 1.2rem;margin:1rem 0 1.5rem;padding:0;list-style:none}
.ts-review-search{margin:0 0 2rem}
@media(max-width:976px){body .entry-content :is(.uagb-block-78b38aa2,.uagb-block-05fef95b,.uagb-block-084d247e,.uagb-block-f14b2a44,.uagb-block-6c30a7ff,.uagb-block-0422be24,.uagb-block-863c3724,.uagb-block-cf15aaf5,.uagb-block-b0f9a0b1,.uagb-block-d2b1c2d3,.uagb-block-f4d3e4f5,.uagb-block-b6f5a6b7,.uagb-block-2c1fc22f,.uagb-block-3e2e789f,.uagb-block-4fdc0b6f,.uagb-block-3b941aaf,.uagb-block-e6d24376,.uagb-block-0a8b3bc1,.uagb-block-eee8f105) > h3.uagb-heading-text{font-size:22px!important}.uagb-block-2e703c12 .uagb-google-map__iframe{height:350px}}
@media(max-width:767px){
 body .entry-content :is(.uagb-block-78b38aa2,.uagb-block-05fef95b,.uagb-block-084d247e,.uagb-block-f14b2a44,.uagb-block-6c30a7ff,.uagb-block-0422be24,.uagb-block-863c3724,.uagb-block-cf15aaf5,.uagb-block-b0f9a0b1,.uagb-block-d2b1c2d3,.uagb-block-f4d3e4f5,.uagb-block-b6f5a6b7,.uagb-block-2c1fc22f,.uagb-block-3e2e789f,.uagb-block-4fdc0b6f,.uagb-block-3b941aaf,.uagb-block-e6d24376,.uagb-block-0a8b3bc1,.uagb-block-eee8f105) > h3.uagb-heading-text{font-size:20px!important}
 .ts-corp-band__desktop{display:none}.ts-corp-band__mobile{display:inline}
 .uagb-block-2e703c12 .uagb-google-map__iframe{height:325px}
 body.page-id-1227 .entry-header,body.page-id-1230 .entry-header{padding-top:1.5rem}
 body.home .entry-content .wp-block-columns:has(> .wp-block-column > .uagb-block-972dd645){row-gap:12px!important}
}
.entry-content .wp-block-uagb-faq button.ts-faq-disclosure{
  appearance:none;display:flex;align-items:center;width:100%;min-width:0;min-height:0;
  margin:0;padding:0;border:0;border-radius:0;background:transparent!important;
  color:inherit!important;font:inherit;line-height:inherit;text-align:inherit;
  text-transform:none;letter-spacing:inherit;box-shadow:none;cursor:pointer;
}
.entry-content .wp-block-uagb-faq button.ts-faq-disclosure:focus-visible{
  outline:3px solid var(--ts-accent-deep);outline-offset:3px;
}
/* ts-review-c:end */




/* ts-section-guide:start */
#ts-section-guide-trigger {
  position: fixed;
  left: calc(1rem + env(safe-area-inset-left, 0px));
  bottom: calc(1rem + env(safe-area-inset-bottom, 0px));
  z-index: 60;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: .65rem;
  box-sizing: border-box;
  min-height: 44px;
  /* Leave the existing scroll-to-top control its own space, even with large text. */
  max-width: calc(100vw - 1rem - 88px - env(safe-area-inset-left, 0px) - env(safe-area-inset-right, 0px));
  margin: 0;
  padding: .65rem 1rem;
  border: 1px solid var(--ts-accent-deep, #7A520A);
  border-radius: 999px;
  background: var(--ts-panel, #fff);
  color: var(--ts-ink, #1C2531);
  box-shadow: 0 3px 16px rgb(0 0 0 / .14);
  font: inherit;
  font-size: .9rem;
  font-weight: 600;
  line-height: 1.4;
  text-transform: none;
  letter-spacing: normal;
  cursor: pointer;
}
#ts-section-guide-trigger[hidden] { display: none; }
#ts-section-guide-trigger svg {
  width: 18px;
  height: 18px;
  flex: 0 0 auto;
  fill: none;
  stroke: var(--ts-accent-deep, #7A520A);
  stroke-width: 1.8;
  stroke-linecap: round;
  stroke-linejoin: round;
}
#ts-section-guide-trigger:hover { background: var(--ts-stone, #F1F0EA); }
#ts-section-guide-popover {
  position: fixed;
  inset: auto auto calc(1.75rem + var(--ts-guide-trigger-height, 44px) + env(safe-area-inset-bottom, 0px)) calc(1rem + env(safe-area-inset-left, 0px));
  box-sizing: border-box;
  width: min(22rem, calc(100vw - 2rem - env(safe-area-inset-left, 0px) - env(safe-area-inset-right, 0px)));
  max-height: min(60vh, calc(100vh - 2.75rem - var(--ts-guide-trigger-height, 44px) - env(safe-area-inset-bottom, 0px)));
  max-height: min(60dvh, calc(100dvh - 2.75rem - var(--ts-guide-trigger-height, 44px) - env(safe-area-inset-bottom, 0px)));
  overflow-y: auto;
  overscroll-behavior: contain;
  margin: 0;
  padding: .5rem;
  border: 1px solid var(--ts-rule, #D5D6CF);
  border-radius: 12px;
  background: var(--ts-panel, #fff);
  color: var(--ts-ink, #1C2531);
  box-shadow: 0 8px 30px rgb(0 0 0 / .2);
  font: inherit;
  font-size: 1rem;
  line-height: 1.5;
}
#ts-section-guide-popover ul { list-style: none; margin: 0; padding: 0; }
#ts-section-guide-popover li { margin: 0; padding: 0; }
#ts-section-guide-popover a {
  display: flex;
  align-items: center;
  box-sizing: border-box;
  min-height: 44px;
  padding: .65rem .8rem;
  border-radius: 6px;
  color: var(--ts-ink, #1C2531);
  text-decoration: underline;
  text-decoration-color: var(--ts-accent-deep, #7A520A);
  text-underline-offset: .2em;
  overflow-wrap: anywhere;
}
#ts-section-guide-popover a:hover { background: var(--ts-stone, #F1F0EA); }
#ts-section-guide-trigger:focus-visible, #ts-section-guide-popover a:focus-visible {
  outline: 3px solid var(--ts-accent-deep, #7A520A);
  outline-offset: 2px;
}
@media (forced-colors: active) {
  #ts-section-guide-trigger, #ts-section-guide-popover {
    border-color: ButtonText;
    background: Canvas;
    color: CanvasText;
    box-shadow: none;
  }
  #ts-section-guide-trigger svg { stroke: ButtonText; }
  #ts-section-guide-popover a { color: LinkText; text-decoration-color: LinkText; }
  #ts-section-guide-trigger:focus-visible, #ts-section-guide-popover a:focus-visible { outline-color: Highlight; }
}
@media print {
  #ts-section-guide-trigger, #ts-section-guide-popover { display: none; }
}
/* ts-section-guide:end */

----- END custom_css -----
```

**RESULT: PASS** - read-only inspect done

_finished 2026-11-11 05:51:19 UTC_
