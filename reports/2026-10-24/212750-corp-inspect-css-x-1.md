# Site op `corp-inspect-css-x-1`

| | |
|---|---|
| operation | `inspect` on `staging37` |
| query | `css`  |
| lane | ops (site-ops.yml) |
| commit | `7bc775b7b3a2b64bfb5bd7a828dcdd39432994a4` on `feat/2026-10-24-corporate-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40134292738 |
| started | 2026-10-24 21:27:42 UTC |

## inspect staging37 css : PASS

_(last 400 of 822 lines; the whole log is in the run's artifact)_
```text
.entry-content figure.wp-block-image img,
.entry-content .uagb-ifb-image-content img:not([src*="avatar"]) {
  border-radius: 4px !important;
  box-shadow: none !important;
}
.entry-content .uagb-ifb-image-content img[src*="avatar"] {
  border-radius: 50% !important;
}
/* Spectra separators and borders pick up the warm rule color */
.entry-content .wp-block-uagb-separator__inner {
  border-top-color: var(--ts-rule) !important;
}

/* 9. Home */
body.home .entry-content > .uagb-is-root-container:first-of-type .uagb-ifb-desc {
  font-size: 1.15rem;
  line-height: 1.6;
}
/* "Discover the healing power…" line and the six benefit words */
body.home .entry-content > .uagb-is-root-container:nth-of-type(2) .wp-block-uagb-advanced-heading p.uagb-desc-text {
  font-family: var(--ts-serif);
  font-style: italic;
  font-size: 1.6rem;
  line-height: 1.3;
  color: var(--ts-text);
}
body.home .entry-content .uagb-icon-list__label {
  font-size: 13px;
  font-weight: 500;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--ts-ink);
}
body.home .entry-content .uagb-icon-list__source-wrap svg {
  fill: var(--ts-accent) !important;
  color: var(--ts-accent) !important;
  width: 9px !important;
  height: 9px !important;
}
/* Service cards: the small "Services" link reads as an editorial text link */
body.home .entry-content .uagb-buttons-repeater {
  background: transparent !important;
  color: var(--ts-ink) !important;
  border: 0 !important;
  padding: 10px 0 !important;
  text-decoration: underline !important;
  text-decoration-color: var(--ts-accent) !important;
  text-underline-offset: 0.25em;
}

/* "About Thai Solitude": a text link, not a pill */
body.home .entry-content .wp-block-uagb-info-box--has-margin .uagb-infobox-cta-link {
  border-radius: 0 !important;
  border-color: var(--ts-accent) !important;
}

/* 10. About: the "Learn About" band and its accordions */
.entry-content > .uagb-is-root-container:has(.wp-block-uagb-faq) {
  background-image: none !important;
  background-color: var(--ts-stone) !important;
}
.entry-content .wp-block-uagb-faq .uagb-faq-child__outer-wrap,
.entry-content .wp-block-uagb-faq .uagb-faq-item {
  background: var(--ts-panel) !important;
  border-color: var(--ts-rule) !important;
  border-radius: 4px !important;
}
.entry-content .wp-block-uagb-faq .uagb-question {
  font-family: var(--ts-serif);
  font-size: 1.2rem;
  font-weight: 400;
  color: var(--ts-ink);
}
.entry-content .wp-block-uagb-faq .uagb-icon svg,
.entry-content .wp-block-uagb-faq .uagb-icon-active svg {
  fill: var(--ts-accent) !important;
}

/* 11. Contact: the three detail cards were hard-coded mint */
body.page-id-301 .entry-content .wp-block-group .wp-block-uagb-container .wp-block-uagb-container {
  background-color: var(--ts-panel) !important;
  border-color: var(--ts-rule) !important;
}

/* 12. Work With Me: same type and palette; soften the mint-specific details */
.ts-wwm h1,
.ts-wwm h2,
.ts-wwm h3 {
  font-family: var(--ts-serif);
  color: var(--ts-ink);
  text-transform: none !important;
}
.ts-wwm h1 {
  font-weight: 300;
  letter-spacing: -0.025em;
}
.ts-wwm h2 {
  font-weight: 300;
  letter-spacing: -0.02em;
}
.ts-wwm h3 {
  font-weight: 400;
  font-size: 1.55rem;
  letter-spacing: -0.005em;
}
.ts-wwm .ts-wwm__eyebrow,
.ts-wwm .ts-wwm__number {
  color: var(--ts-accent-deep);
}
.ts-wwm .ts-wwm__identity {
  color: var(--ts-ink);
  font-weight: 600;
  letter-spacing: 0.12em;
  font-size: 0.85rem;
}
.ts-wwm .ts-wwm__number {
  font-family: var(--ts-serif);
  font-size: 2.2rem;
  font-weight: 400;
  letter-spacing: 0;
  line-height: 1;
  color: var(--ts-accent);
}
.ts-wwm .ts-wwm__hero,
.ts-wwm .ts-wwm__approach,
.ts-wwm .ts-wwm__credentials,
.ts-wwm .ts-wwm__invitation,
.ts-wwm .ts-wwm__clients {
  border-radius: 6px;
}
.ts-wwm .ts-wwm__approach,
.ts-wwm .ts-wwm__credentials,
.ts-wwm .ts-wwm__clients {
  background: var(--ts-panel);
  border: 1px solid var(--ts-rule);
}
.ts-wwm .ts-wwm__hero:after {
  border-color: var(--ts-rule);
  box-shadow: -28px 30px 0 -10px #E6E5DE, -58px 60px 0 -20px #DEDDD5;
}
.ts-wwm .ts-wwm__hero-photo img {
  border-radius: 4px;
}
.ts-wwm .ts-wwm__cards > .wp-block-column {
  background: #FFFFFF;
  border-color: var(--ts-rule);
  border-radius: 4px;
}
.ts-wwm .ts-wwm__owners-grid > .wp-block-column {
  border-top: 2px solid var(--ts-accent);
  border-radius: 0 0 4px 4px;
}
.ts-wwm p a:not(.wp-block-button__link),
.ts-wwm .ts-wwm__credential-list a {
  color: var(--ts-ink);
  text-decoration-color: var(--ts-accent);
}
.ts-wwm .wp-block-button__link {
  border-radius: 999px;
}
.ts-wwm .is-style-outline .wp-block-button__link {
  color: var(--ts-ink);
  border-color: var(--ts-ink);
}

/* 13. Footer: ink "Coming soon" band over the existing texture, dark base row */
.site-primary-footer-wrap[data-section="section-primary-footer-builder"] {
  background-color: var(--ts-night) !important;
  background-image: linear-gradient(rgba(21, 28, 38, 0.88), rgba(21, 28, 38, 0.88)), url("/wp-content/uploads/2026/06/00FooterGrainy.webp") !important;
}
.site-footer .uagb-ifb-title {
  color: #F5F4EF !important;
  font-weight: 300;
  font-size: clamp(2.2rem, 4.5vw, 3.6rem) !important;
  line-height: 1.06;
  letter-spacing: -0.02em;
}
.site-footer .uagb-ifb-title::before {
  content: "";
  display: block;
  width: 48px;
  height: 2px;
  margin: 0 auto 0.6em;
  background: var(--ts-accent);
}
.site-footer .uagb-ifb-desc {
  color: #C9CED6 !important;
}
.site-footer .uagb-infobox-cta-link.wp-block-button__link {
  background: #F5F4EF !important;
  color: var(--ts-ink) !important;
  border-color: #F5F4EF !important;
}
.site-footer .uagb-infobox-cta-link.wp-block-button__link:hover {
  background: #FFFFFF !important;
}
.site-below-footer-wrap[data-section="section-below-footer-builder"] {
  background-color: var(--ts-night) !important;
  border-top-color: rgba(255, 255, 255, 0.08) !important;
}
.site-below-footer-wrap .ast-footer-copyright,
.site-below-footer-wrap .ast-footer-copyright p {
  color: #C9CED6 !important;
}
.site-below-footer-wrap .menu-link {
  color: #E6E8EC !important;
}
.site-below-footer-wrap .current-menu-item > .menu-link {
  color: #FFFFFF !important;
  text-decoration: underline;
  text-decoration-color: var(--ts-accent);
  text-underline-offset: 6px;
}
.site-below-footer-wrap .menu-link:hover {
  color: #FFFFFF !important;
}
#ast-scroll-top {
  background-color: var(--ts-ink) !important;
  border-radius: 999px;
}
/* TS RESTYLE END */

/* TS TRANSITION START — soft cross-fade between pages (Chrome, Edge, Safari).
   Uses the browser's default cross-fade so shared parts (menu, footer) stay steady.
   Remove everything from START to END to roll back. Skipped for reduced-motion users. */
@view-transition { navigation: auto; }
html { background-color: var(--ts-ground, #F5F4EF); }
::view-transition-old(root),
::view-transition-new(root) { animation-duration: 0.3s; }
@media (prefers-reduced-motion: reduce) {
  @view-transition { navigation: none; }
}
/* TS TRANSITION END */

/* TS RELOAD FADE START — fade-in on F5 and first visits only (needs the head script; see scripts/restyle/ts-reload-fade-snippet.html).
   Remove START..END to roll back. */
@keyframes ts-fade-in { from { opacity: 0; } to { opacity: 1; } }
/* Fade only below the header: animating #page also makes its menu transparent. */
html.ts-fade-in #content,
html.ts-fade-in #colophon { animation: ts-fade-in 0.4s cubic-bezier(0, 0, 0.2, 1) backwards; }
@media (prefers-reduced-motion: reduce) {
  html.ts-fade-in #content,
  html.ts-fade-in #colophon { animation: none; }
}
/* TS RELOAD FADE END */

/* TS STEADY HEADER START — keep the menu out of both kinds of fade.
   Roll back this change by restoring custom-css-after5.css. */
/* Astra's transition: all otherwise animates colors injected by dark-mode extensions. */
#masthead a,
#masthead a *,
#masthead .menu-toggle,
#masthead .ast-menu-toggle { transition: none !important; }

/* Capture the shared header separately from the content's root cross-fade.
   Show just the new header at full opacity, without blending two menu images. */
#masthead { view-transition-name: ts-header; }
::view-transition-group(ts-header) { animation: none; }
::view-transition-old(ts-header) { display: none; }
::view-transition-new(ts-header) {
  animation: none;
  opacity: 1;
  mix-blend-mode: normal;
}
/* TS STEADY HEADER END */

/* TS SERVICE IMAGE RESPONSIVE START — keep the compact 6:5 frames when Spectra stacks service rows. */
@media (max-width: 1024px) {
  body.home .wp-block-uagb-container.uagb-block-db0b5e62 > figure.wp-block-image,
  body.home .wp-block-uagb-container.uagb-block-9d58695e > figure.wp-block-image,
  body.home .wp-block-uagb-container.uagb-block-bb505ec2 > figure.wp-block-image {
    max-width: 320px;
    margin-inline: auto;
    justify-self: center;
  }
}
/* TS SERVICE IMAGE RESPONSIVE END */

/* TS WWM NUMBER CONTRAST START — step numerals 01/02/03 were #C8963E on a light panel (2.66:1, below the 3:1 large-text minimum);
   use the designer's own deeper accent. Work With Me only (.ts-wwm). Remove START..END to roll back. */
.ts-wwm .ts-wwm__number { color: var(--ts-accent-deep, #7A520A); }
/* TS WWM NUMBER CONTRAST END */

/* TS HERO IMAGE STACKING START — hero images overhang their box (margin-bottom: -180px) and the later full-width section below
   painted over their bottom edge (Therapeutic and others). Raise the image block above later sections. Remove START..END to roll back. */
.entry-content .uagb-is-root-container.uagb-layout-grid > .wp-block-uagb-image { position: relative; z-index: 2; }
/* TS HERO IMAGE STACKING END */

/* TS HOME TRIAL START */
/* Home page: licence line under the H1, the Learn About link colour, and a footer line. */
body.home .ts-home-trust{margin:-2.4rem 0 0;font-size:.95rem;font-weight:600;letter-spacing:.03em;color:var(--ast-global-color-0)}
body.home .ts-home-trust a{color:inherit;text-decoration:underline;text-underline-offset:3px}
@media(max-width:921px){body.home .ts-home-trust{margin-top:-1.6rem}}
@media(max-width:600px){body.home .ts-home-trust{margin-top:-.4rem}}
/* "About Thai Solitude" link in the Learn About info-box: the theme gives button links white text and this one has no fill, so it was white on cream (1.1:1). Navy, like the paragraph above it. */
body.home .uagb-block-36c16d4d .uagb-infobox-cta-link,body.home .uagb-block-36c16d4d .uagb-infobox-cta-link:hover,body.home .uagb-block-36c16d4d .uagb-infobox-cta-link:focus-visible{color:var(--ast-global-color-0)}
/* Site-wide footer panel (a footer widget, stored in options): email and résumé link under "Portland, OR". */
#colophon .ts-footer-links{margin:.7rem 0 0;text-align:center;font-size:.95rem;color:var(--ts-ground,#f5f4ef)}
#colophon .ts-footer-links a{color:inherit;text-decoration:underline;text-underline-offset:3px}
/* TS HOME TRIAL END */

/* ts-a11y-focus:start (2026-10-17, accessibility audit) */
#ast-scroll-top:focus-visible{outline:3px solid #fff;outline-offset:2px;box-shadow:0 0 0 6px #1f2a37}
html:has(.ts-wwm__sticky){scroll-padding-bottom:5.5rem}
/* ts-a11y-focus:end */

/* ts-a11y-jump:start (2026-10-18, accessibility) */
.ts-jump{margin:0 0 1.25rem;padding:.35rem 0;font-size:.85rem;line-height:1.4}
.entry-content .ts-jump .ts-jump__list{display:flex;flex-wrap:wrap;align-items:center;gap:0 1rem;margin:0;padding:0;list-style:none}
.entry-content .ts-jump .ts-jump__list li{margin:0;padding:0}
.entry-content .ts-jump__list li+li::before{content:"\00b7" / "";margin:0 .65rem 0 0;color:var(--ts-muted,#4A5563)}
.entry-content .ts-jump__list a{display:inline-block;padding:.3rem .1rem;color:var(--ts-muted,#4A5563);text-decoration:underline;text-decoration-thickness:1px;text-underline-offset:.25em}
.entry-content .ts-jump__list a:hover{color:var(--ts-ink,#1C2531)}
.entry-content .ts-jump__list a:focus-visible{outline:3px solid var(--ts-accent,#C8963E);outline-offset:2px}
@media (max-width:600px){.entry-content .ts-jump__list li+li::before{display:none}}
/* ts-a11y-jump:end */

/* ts-a11y-heart:start (2026-10-18) */
.entry-content a.wp-block-uagb-icon-list-child.ts-heart-link{position:relative;display:flex;color:inherit;text-decoration:none;cursor:pointer}
.entry-content a.wp-block-uagb-icon-list-child.ts-heart-link::after{content:"";position:absolute;inset:-5px -10px}
.entry-content a.wp-block-uagb-icon-list-child.ts-heart-link:hover .uagb-icon-list__label,.entry-content a.wp-block-uagb-icon-list-child.ts-heart-link:focus-visible .uagb-icon-list__label{text-decoration:underline;text-underline-offset:.3em}
.entry-content a.wp-block-uagb-icon-list-child.ts-heart-link:focus-visible{outline:3px solid var(--ts-accent,#C8963E);outline-offset:6px;border-radius:.4rem}
/* ts-a11y-heart:end */

/* ts-theme:start (2026-10-18) */
.ts-theme-toggle-wrap{display:flex;align-items:center;margin-left:.6rem}
#ast-mobile-header .ts-theme-toggle-wrap{margin-left:0;margin-right:.6rem}
.ts-theme-toggle{display:inline-flex;align-items:center;justify-content:center;flex:0 0 auto;width:44px;height:44px;margin:0;padding:0;border:1px solid var(--ts-rule,#D5D6CF);border-radius:999px;background:transparent;color:var(--ts-ink,#1C2531);cursor:pointer;line-height:1}
.ts-theme-toggle:hover,.ts-theme-toggle:focus,.ts-theme-toggle:active{border-color:var(--ts-ink,#1C2531);background:transparent;color:var(--ts-ink,#1C2531)}
.ts-theme-toggle:focus-visible{outline:3px solid var(--ts-ink,#1C2531);outline-offset:2px}
.ts-theme-toggle svg{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.ts-theme-toggle .ts-sun{display:none}
html[data-ts-theme="dark"] .ts-theme-toggle .ts-sun{display:block}
html[data-ts-theme="dark"] .ts-theme-toggle .ts-moon{display:none}
@media print{.ts-theme-toggle-wrap{display:none}}
@media screen{
html[data-ts-theme="dark"]{color-scheme:dark;
--ts-ground:#151C26;--ts-panel:#1B2432;--ts-stone:#202B3A;--ts-rule:#34404F;--ts-line:#34404F;
--ts-ink:#F3F1EA;--ts-text:#D9DCE2;--ts-muted:#AAB3BF;--ts-pale:#1D2A2A;--ts-deep:#EAF2E4;
--ts-green:#8CCB5B;--ts-accent:#C8963E;--ts-accent-deep:#E2B866;--ts-night:#0E141C;
--ast-global-color-0:#8CCB5B;--ast-global-color-1:#A5DB78;--ast-global-color-2:#F3F1EA;--ast-global-color-3:#D9DCE2;
--ast-global-color-4:#151C26;--ast-global-color-6:#34404F;--ast-global-color-7:#0E141C;--ast-global-color-8:#F3F1EA}
html[data-ts-theme="dark"] .ast-button,html[data-ts-theme="dark"] .wp-block-button__link,html[data-ts-theme="dark"] .ast-custom-button,html[data-ts-theme="dark"] .menu-item .ast-custom-button-link .ast-custom-button{color:#0E141C}
html[data-ts-theme="dark"] #colophon .ts-footer-location,html[data-ts-theme="dark"] #colophon .ts-footer-links{color:#F5F4EF}
html[data-ts-theme="dark"] #ast-scroll-top{background:#202B3A;color:#F3F1EA;border:1px solid #34404F}
html[data-ts-theme="dark"] .ts-wwm .ts-wwm__sticky{background:#151C26}
}
/* ts-theme:end */

/* ts-header-fit:start (2026-10-19, Ryan: the dark/light button made the phone header wrap "Thai Solitude" and split the word on an iPhone SE) */
@media (max-width: 389px) {
  /* the button added 44 px + ~28 px of spacing to a row that only had room for the logo and title: tighten the spacing around it, keep every tap target */
  #ast-mobile-header .ast-builder-grid-row { column-gap: 8px; }
  #ast-mobile-header .ts-theme-toggle-wrap { margin-right: 8px; padding-right: 0; }
  #ast-mobile-header .site-header-primary-section-right .ast-builder-layout-element:not(.ts-theme-toggle-wrap) { padding-left: 0; }
  #ast-mobile-header .site-logo-img { padding-right: 10px; }
  /* never wrap or split the name */
  #ast-mobile-header .site-title,
  #ast-mobile-header .site-title a { white-space: nowrap; }
}
@media (max-width: 349px) {
  /* below ~350 px the name shrinks smoothly (it is 23.712px at 350 px, so there is no jump) instead of wrapping */
  #ast-mobile-header .site-title { font-size: calc((100vw - 222px) / 5.4); }
}
/* ts-header-fit:end */

/* ts-thai-image-crop:start (2026-10-19, Ryan: "the pictures need to be cropped closer to their original square shape") */
/* The three Thai Massage illustrations are 512 x 460. Spectra gives each frame a fixed pixel HEIGHT, so the frame's shape drifts away from the picture as the screen narrows (up to 50% of the
   picture was cut off). The height now follows the width, in the picture's own proportions. "body" in front makes this win over Spectra's identical selector whatever order the files load in. */
body .uagb-block-5b6bb4f3.wp-block-uagb-image .wp-block-uagb-image__figure img,
body .uagb-block-04d0e681.wp-block-uagb-image .wp-block-uagb-image__figure img,
body .uagb-block-f48b2ed7.wp-block-uagb-image .wp-block-uagb-image__figure img {
  height: auto;
  aspect-ratio: 512 / 460;
}
/* ts-thai-image-crop:end */

/* ts-research-slide:start (2026-10-24, Ryan: "keep the 'what research says' area like the other slide open sections on all these pages") */
/* The collapsible research panels on the Thai, Therapeutic and Deep Tissue pages are native "details" sections, which snap open and shut. These rules make the panel's content slide open and
   shut over a third of a second, like the FAQ and About accordions. Browsers that cannot animate it (no ::details-content or interpolate-size) simply open and close at once, as before;
   a visitor who asks for reduced motion gets no animation. The closed panels look exactly as they did. */
.wp-block-details {
  interpolate-size: allow-keywords;
}
.wp-block-details::details-content {
  block-size: 0;
  overflow: hidden;
  transition: block-size 0.35s ease, content-visibility 0.35s allow-discrete;
}
.wp-block-details[open]::details-content {
  block-size: auto;
}
@media (prefers-reduced-motion: reduce) {
  .wp-block-details::details-content {
    transition: none;
  }
}
/* ts-research-slide:end */

----- END custom_css -----
```

**RESULT: PASS** - read-only inspect done

_finished 2026-10-24 21:27:49 UTC_
