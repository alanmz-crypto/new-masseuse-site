> **SUPERSEDED 2026-10-04 — DO NOT USE THE MU APPROACH DESCRIBED BELOW.** The staging14 restyle was first deployed through host-guarded mu-plugins; that was removed everywhere (localhost never had it, staging14 and production were cleaned and audited). The design now lives only in WordPress storage (Additional CSS, `ihaf_insert_header`, `astra-settings`, page content) via `scripts/restyle/native/` (see its README), and production runs it. The MU loader/installer files named below no longer exist. Rule: AGENTS.md "no MU-plugins". Everything below is history.

# [Arc none — ad-hoc] White flash on navigation, F5, and menu: handoff to Claude Fabel

From: OpenAI Codex · To: Claude Fabel · Date: 2026-10-04

## Current state — Home deployed to staging14, 2026-10-04

Ryan accepted the local result (“Whole thing passes”) and explicitly authorized **the homepage, not every page**, for staging14. The deployment is complete. Localhost continues to serve `scripts/restyle/custom-css-after9.css`; staging14 serves that exact stylesheet **only on Home**. It includes Fabel's font/header flash correction plus the three service-image frames capped at 320px and centered at widths up to 1024px, preserving the 6:5 crop. The earlier `after6` failure descriptions below are historical.

### Where the deployed homepage now lives

- Homepage content: staging14 WordPress page ID **296**, copied from the accepted local page with URLs changed to `https://staging14.thaisolitude.com`.
- Homepage CSS: `~/www/staging14.thaisolitude.com/public_html/wp-content/mu-plugins/ts-staging14-home-only.css` on the SiteGround account. **Editing staging14's shared Additional CSS will not change Home while this override is active.** Update the scoped file deliberately for future Home design work; localhost still uses Additional CSS.
- Homepage-only loader: adjacent `ts-staging14-home-only.php`. It activates on the main `wp` action only for staging14, Astra, and front-page ID 296. It supplies the CSS through `wp_get_custom_css`, emits the existing approved reload script in the head, and supplies the license footer through Astra's option filter for that request.
- Release sources: `scripts/restyle/install-home-staging14.php`, `home-staging14-release.json`, and `ts-staging14-home-only.php`. Private uploaded copies are under `~/private-releases/ts-home-20261004/`. The installer has read-only default behavior and uses **`apply-staging14-home-only`** for application. The older shared-style apply argument is rejected. Reapplying will correctly refuse because the original content hashes and absent-file conditions no longer match; inspect current state rather than bypassing these checks.

### Verified after deployment

- Home returns HTTP 200, serves exact `after9` CSS, matches localhost's visible text including the license footer, includes the reload script exactly once, has the staging14 canonical, and contains no localhost URLs.
- About and Services retain exactly their pre-deployment visible text and Additional CSS. Work With Me and Contact serve the original shared CSS. All four pages return HTTP 200 and contain none of the new homepage styling or reload script.
- The installer verified every other page's stored content/title/status/slug/parent/order fingerprint unchanged: `0fd3af5f43c87287a31f30b1a5015a047cfdf532ab16ee573598ce9e9c4de6ce`. Shared Additional CSS, `ihaf_insert_header`, and the complete `astra-settings` option are unchanged.
- All nine referenced source media files existed before deployment. The three service images return HTTP 200 as WebP afterward. Staging14's robots.txt continues to return `Disallow: /`.
- These are source/HTTP/database checks. This session had no connected browser for a new staging visual inspection. Ryan had accepted the local design; he can review the deployed homepage at `https://staging14.thaisolitude.com/`.
- F5/initial-visit fade code is included on Home. Cross-document view transitions require both pages to opt in; links between Home and the untouched legacy pages use ordinary navigation. Do not enable effects on the other pages without authorization merely to make those transitions run.

### Backups and rollback

Before this deployment a fresh private SiteGround-hosted DB dump was exported, compressed, and checked with `gzip -t`: `~/private-backups/staging14-before-home-only-20261004.sql.gz`, SHA-256 `6096ad031afff1f14305d209bb1c2c3b72e7f27072c6a0717a87150091de3c89`. The remote mu-plugins directory was absent before deployment. This is a private database snapshot on the hosting account, not a SiteGround control-panel full-site deployment.

For a narrow rollback, first back up the then-current site. Restore only Home's `post_content` from option `ts_staging14_home_only_20261004_before`, using `wp_update_post` with `wp_slash`. Disable only the new `ts-staging14-home-only.php` loader (rename it so it no longer ends in `.php`), then flush WP/SiteGround caches. The saved CSS and header values in that rollback option are evidence; they were never overwritten and do not need restoring. Do not overwrite other pages or all Astra settings. The unused scoped CSS file can remain for recovery. This rollback is documented, not exercised after the accepted deployment.

The earlier local image-sizing backup is tag `pre-responsive-service-images` (files `2bfda093`, DB `dcddd5ab`). Runtime sources and this handoff remain local/untracked; no commits, pushes to Git, SiteGround stage-to-live operation, or production changes were made. The remainder of this file preserves historical investigation evidence.

## RESOLUTION (menu bar), 2026-10-04, Claude Fabel — read this first

**Status: menu-bar white flash FIXED on localhost only.** Ryan, in his own Chrome with Dark Reader on: "much smoother in the menu bar. 98% quality in the menu bar, the rest is fine." Not deployed to staging14 or production. The original Codex status below is kept as history.

**Applied now:** local Additional CSS = `scripts/restyle/custom-css-after8.css` (sha256 `9fbdcf8ee090b7f389a3828b612ef5264d38342a38b9e81436e432922a9622a1`), verified byte-identical on Home, About, Services, Work With Me and Contact. The head script, fade, view-transition and `ts-header` blocks are unchanged from `after6`.

### Cause: two Dark Reader mechanisms, both triggered by the designer's restyle CSS

Ryan's Chrome profiles 4 and 6 run Dark Reader 4.9.133 with default synced settings: dynamic theme, enabled for all sites, no localhost exception. KDE is in dark mode. The original site CSS (`custom-css-before-LOCAL.css`) had neither trigger; both come from `ts-restyle.css`.

1. **`!important` cream header beat Dark Reader's loading fallback.** At document start Dark Reader injects `html, body, body :not(iframe) { background-color:#181a1b !important; color:#e8e6e3 !important; border-color:#776e62 !important }`. Its specificity is only (0,0,2). The restyle's header rules were `!important` with class selectors, (0,1,0) to (0,4,0), so they won. While the rest of the page was already dark, the menu showed a cream strip of link boxes with dark text, cream bar edges, and a light bottom rule. That lasted about one frame on menu clicks and up to about 90 ms on F5. It was reproduced in 5 of 6 navigations.
2. **`@import` in the Additional CSS made Dark Reader theme the whole stylesheet late.** Dark Reader routes any `<style>` containing `@import` through an asynchronous path. In `inject/index.js`, `containsCSSImport()` makes `getRulesSync()` return null, and `getRulesAsync()` builds a CORS copy. When the page is already parsed, as with speculation-rules prefetch, Dark Reader removes the fallback before that async step finishes. The stylesheet then shows its un-darkened cream for one or two frames. The header's own view-transition layer (`ts-header`) shows the new header at full opacity, so the flash reads as "the menu bar."

### Fix (CSS only, each mechanism changed and measured separately)

- **`after7`, header colours:** removed `!important` from the header background, bottom-border and menu-link colour rules. They are prefixed with `#masthead` so they still beat Astra's class-only, non-important header colours. Dark Reader's fallback now darkens the menu while it loads.
- **`after8`, fonts:** replaced line 1, the Google Fonts css2 `@import` for Newsreader and Hanken Grotesk, with the 27 `@font-face` rules that URL served on 2026-10-04: fonts.gstatic.com woff2 files, `font-display: swap`, all subsets. The stylesheet now contains no `@import` text at all, comments included. Dark Reader themes it synchronously, like every other inline style.

### Evidence (controlled reproduction, not Ryan's tab)

Test setup: Chromium 153 on a virtual display, an unpacked copy of the same Dark Reader 4.9.133 with default settings, OS dark scheme. Each run logs the header's computed colours on every rendered frame and records the screen at 60 fps.

| Applied CSS, live local site with prefetch | Navigations with a cream header frame |
|---|---|
| `after6` (Codex attempt 2) | 5 of 6 |
| `after7` | 3 of 24, one frame each |
| `after8` (applied) | 0 of 24 warm; 0 of 2 after 35 s idle (Dark Reader's service worker asleep) |

Light mode, no extension: all 78 `#masthead` elements have identical computed styles to `after6` in 8 states (desktop, 390 px, hover, open mobile menu). Header screenshots are pixel-identical. The click and F5 fade metrics are frame-for-frame the same. Web fonts are loaded on the first frame of every navigation, so there is no font-swap blink.

**Likely remaining 2%:** on F5 and first load, the menu shows Dark Reader's neutral loading dark (#181a1b) for a frame or a few before its themed warm dark (rgb 31,29,19). That is a slight tint shift, not a light flash. Removing it would require hard-coding Dark Reader's colours, so it was left alone.

### Before staging14 or production

- Promote the served `after8` Additional CSS, **not** the designer's `ts-restyle.css`, which still contains both triggers. Tell the designer: no `!important` on surface colours and no `@import` in Additional CSS.
- The fonts now load straight from fonts.gstatic.com URLs that are pinned to versions (newsreader v26, hankengrotesk v12). Google keeps old versions served, but self-hosting the 10 woff2 files is the durable option.
- Recheck on staging14 with Ryan's Chrome. SiteGround caching changes timing, so localhost results do not carry over automatically.

### Backups and rollback

- `note:pre-header-flash-fix`: files `766d859a`, DB `276074d3`. This is the `after6` state.
- `note:pre-font-inline`: files `a4634cc6`, DB `9971e343`. This is the `after7` state.
- **CSS rollback:** re-apply `custom-css-after7.css` (undo the font inline) or `custom-css-after6.css` (undo both) with `wp_update_custom_css_post($css, ['stylesheet'=>'astra'])`, then `wp_cache_flush()` and `sg_cachepress_purge_cache()`. This stack's `wp eval` rejects extra positional arguments, so embed the base64 CSS inside the PHP string: `docker compose run --rm -T wpcli wp eval "\$css = base64_decode('<b64>', true); ..."`.

### Re-run the measurements

The harness is in `scripts/restyle/tools/darkreader-flash/`: `env.sh`, `harness.py`, `analyze.py`, `blink.py`, `lightcheck.py`, `cdp.py`. Usage is in the docstrings. Outputs go to `/tmp/ts-flash-work`. It never touches Ryan's real Chrome profiles; it copies the installed Dark Reader into a scratch profile. A smoke run from that folder against the applied `after8` gave 0 of 6.

No commits, pushes, staging or production writes, head-script changes, or browser-setting changes were made. Everything above is local and untracked.

---

**Original Codex status (superseded by the resolution above):** UNRESOLVED. Both Codex CSS attempts failed user acceptance.

## Start here

Ryan's latest observation, after the second Codex change:

> Now, I'm seeing a white flash again on page transition and F5 and the menu bar is still an issue with flash. The fade itself isn't bad except for the flash. We're going to give this problem to a higher tier model, Claude Fabel. Create a handoff so Fabel can take over.

The goal is to keep the soft fade while eliminating the white flash, including the menu. Do not treat the fade's appearance as the principal remaining problem. Ryan's actual Chrome result overrides the earlier clean-profile measurements and our static checks.

Work at `/home/lauer/WordPress/new-masseuse-site`, on `http://localhost:8090/`. Start with the homepage. The current applied CSS is **`scripts/restyle/custom-css-after6.css`**, not `after4` or `after5`. The earlier head script is still installed. Nothing was rolled back for this handoff, and no additional site changes were made during handoff preparation.

Read the previous Claude-to-Codex handoff for the original measurements and tools:
`inter-model/CODEX-ASTRA-2026-10-04-f5-white-flash-handoff.md`.
Its description of the applied CSS is now superseded by this document. Its claims that page navigation/menu were working describe that earlier test profile, not Ryan's current acceptance result.

## Boundaries and authority

- Localhost only for now. No staging12, staging14, production, or SiteGround changes. After Ryan approves localhost, the designated next target is staging14 only; that approval has not occurred.
- The design work was CSS only. Further PHP, JavaScript, or meta-tag changes require Ryan's OK under the inherited task constraint. The existing reload head script predates Codex; Codex did not change it.
- Back up before every WordPress DB mutation with `./scripts/backup-site.sh --note <reason>`.
- Apply Additional CSS with `wp_update_custom_css_post($css, ['stylesheet'=>'astra'])`, through the local `wpcli` service. Flush the WP cache and call `sg_cachepress_purge_cache()` if available. The service mounts only `public_html/`; CSS can be passed as base64 in a `wp eval` argument. Do not assume a file under `scripts/` is visible inside the container.
- Preserve page text, images, blocks, and the designer's original CSS file. Preserve reduced-motion behavior.
- `remote_execution.txt`, `sql_manager.txt`, and production credential/dump files are out of scope.
- This checkout is on `main` with substantial pre-existing dirty/untracked work. Codex made no commits or pushes. Do not sweep unrelated work into a commit. The restyle directory and inter-model documents are currently untracked.

## Current applied state, independently checked at handoff

On 2026-10-04, a fresh HTTP read of the localhost homepage showed that its `wp-custom-css` style matches `scripts/restyle/custom-css-after6.css` exactly, ignoring only outer whitespace.

SHA-256 of that saved CSS file:
`6e90b6ebe999cbebb283942841f346d1b432df1118dda05618043841c8403a1c`

The CSS contains the original styles, the designer's restyle, and these animation rules:

```css
@view-transition { navigation: auto; }
html { background-color: var(--ts-ground, #F5F4EF); }
::view-transition-old(root),
::view-transition-new(root) { animation-duration: 0.3s; }
@media (prefers-reduced-motion: reduce) {
  @view-transition { navigation: none; }
}

@keyframes ts-fade-in { from { opacity: 0; } to { opacity: 1; } }
html.ts-fade-in #content,
html.ts-fade-in #colophon {
  animation: ts-fade-in 0.4s cubic-bezier(0, 0, 0.2, 1) backwards;
}
@media (prefers-reduced-motion: reduce) {
  html.ts-fade-in #content,
  html.ts-fade-in #colophon { animation: none; }
}

#masthead a,
#masthead a *,
#masthead .menu-toggle,
#masthead .ast-menu-toggle { transition: none !important; }
#masthead { view-transition-name: ts-header; }
::view-transition-group(ts-header) { animation: none; }
::view-transition-old(ts-header) { display: none; }
::view-transition-new(ts-header) {
  animation: none;
  opacity: 1;
  mix-blend-mode: normal;
}
```

The earlier inline script remains in `<head>`, after stylesheet links, via the WordPress option `ihaf_insert_header`:

```html
<script>try{var n=performance.getEntriesByType("navigation")[0],o=location.origin;if((n&&n.type==="reload")||!document.referrer||document.referrer.indexOf(o)!==0)document.documentElement.classList.add("ts-fade-in")}catch(e){}</script>
```

No `color-scheme` meta was present in the fresh homepage response. `--ts-ground` is the light cream `#F5F4EF`. No native dark theme was added.

Header structure: `#page` wraps `#masthead`, `#content`, and `#colophon`. `#masthead` contains both `#ast-desktop-header` and `#ast-mobile-header`. Home and About each had exactly one `#masthead`, `#content`, and `#colophon` in the HTTP response.

## What Codex tried, and the actual outcome

1. **Starting state: `custom-css-after4.css` (earlier Claude's work).** Reload animation targeted `html.ts-fade-in body`; page clicks used the root cross-document view transition. Ryan said F5 faded a little but the dark-mode white flash remained obvious.
2. **First Codex attempt: `custom-css-after5.css`.** Changed the reload animation and reduced-motion selectors from `body` to `#page`, preserving the 0.4-second duration and existing navigation gate. Reason: installed Dark Reader code can force body opacity to 1 during startup. **Ryan's result: menu flash remained.** This target still included the header; it was not a verified flash fix.
3. **Second Codex attempt: `custom-css-after6.css` — CURRENT.** Changed reload fade targets to `#content` and `#colophon`, disabled transitions on header links/toggles, and added the separate non-fading `ts-header` view-transition layer shown above. **Ryan's result: white flash on page transitions and F5, with the menu still flashing.** The fade itself is acceptable. This attempt failed acceptance; the report may describe a regression, but no controlled browser capture established which rule caused it.

Do not carry forward Codex's implementation comments such as “steady header” as proof of successful behavior. They describe intent only.

## Evidence vs. hypotheses

**Observed from files / HTTP:**

- Chrome's `Local State` identified `Profile 4` as the last-used profile. Its stored experimental flag list was empty. That is not proof that no runtime flags were supplied.
- That profile has Dark Reader installed, version `4.9.133_0`, extension ID `eimadpbcbfnmbkopoojfekhnkhdbieeh`; its recorded disable-reasons list was empty. Installation is not proof that Dark Reader was enabled for localhost during Ryan's test.
- Installed `inject/fallback.js` conditionally injects dark backgrounds and `html, body { opacity: 1 !important; transition: none !important; }`. Its conditions include the dark color-scheme media query and per-host session state. It is configured to run at document start.
- Installed Dark Reader's main injector also contains root background and `color-scheme: dark !important` rules.
- Astra's `public_html/wp-content/themes/astra/assets/css/minified/main.min.css` includes `transition: all .2s linear` for menu links and header anchors. Such rules could animate extension-driven recoloring, but Codex did not observe that happening in Ryan's tab.
- After the second change, Home and About served the exact candidate CSS, and homepage text extracted outside scripts/styles was unchanged. These were static checks, not browser animation tests.

**Not established:**

- Which dark-mode mechanism is active in Ryan's specific tab; Dark Reader engine/settings and localhost enablement; actual computed root/header colors during the flash; whether the flash occurs before first paint or in view-transition snapshots.
- Whether the old head-script timing causes any flash; whether the separate header snapshot introduced or worsened the navigation flash; whether early `color-scheme` would fix this site without changing dark-mode behavior elsewhere.
- No fix was visually verified by Codex. Computer-use inventory repeatedly returned no connected apps or browsers, so Codex could not inspect Ryan's Chrome tab or capture its frames. The optional questions about dark-mode mechanism were unanswered when this handoff was written.

The original Claude tests used a separate Chrome profile, including a forced-dark test, and did not establish Dark Reader behavior. Their screencast could not capture the browser's own pre-first-paint blank canvas. “Zero white frames” from those captures is not acceptance evidence for Ryan's flash. Do not assume all browser blanks are necessarily white, or that CSS/meta can eliminate every pre-document frame.

## Suggested next investigation

1. Establish access to Ryan's actual Chrome/profile and confirm the dark-mode mechanism. Treat the missing browser connection as a concrete limitation, not permission to claim clean-profile reproduction is equivalent.
2. Separate three observations: F5, same-origin menu click, and initial visit. For each, locate the first bright frame and whether it is the whole viewport, header background, menu-link boxes, or a transition snapshot. Where possible capture the actual browser/window, including the interval before page paint.
3. Use a controlled, reversible comparison of the current rules against the saved earlier states. The separate `ts-header` snapshot and Astra color-transition override were added together in the second attempt; their individual effects remain unknown. Avoid adding more interacting rules without isolating these.
4. Test any Dark Reader comparison in the same profile, with Ryan's approval for changes to extension/browser settings. A native dark theme or early meta/script solution is a separate design/authorization decision under the current non-CSS constraint.
5. Keep the fade if feasible. Ask Ryan to validate the homepage after a materially supported change; accept only his real-browser result for the unresolved visible symptom.

Technical references consulted, not proof of this site's cause:
- https://web.dev/articles/color-scheme
- https://developer.chrome.com/docs/web-platform/view-transitions/same-document
- https://darkreader.org/help/en/

## Rollback and backups

Before the first Codex CSS update, local Restic backup tag `note:pre-darkreader-reload-fade` saved files snapshot `94cd91bf` and DB snapshot `54d52718`. This captures the `after4` state.

Before the second Codex CSS update, tag `note:pre-steady-menu` saved files snapshot `7def5849` and DB snapshot `d7e4a269`. This captures the `after5` state.

For a CSS-only rollback after a new backup:
- Restore `scripts/restyle/custom-css-after5.css` through `wp_update_custom_css_post` to undo the second Codex attempt.
- Restore `scripts/restyle/custom-css-after4.css` to undo both Codex attempts while retaining earlier Claude's transition and head-script setup.
- Neither rollback clears `ihaf_insert_header`. See the previous handoff for the older full animation rollback and its earlier backup IDs.
- These CSS rollbacks were preserved but **not exercised** by Codex. No whole-database restore was performed. Prefer the narrow CSS rollback over overwriting unrelated content.

All seven earlier measurement tools remain under `scripts/restyle/tools/`. Read them before running: they use a separate browser profile and some refer to `/tmp/claude-1000/`. The snippet file `ts-reload-fade-snippet.html` still describes the old body-target version and even says “NOT APPLIED”; use the served CSS and current snapshot as the source of truth.

## Notes for future models — avoid repeating this session's mistakes

- **Re-establish the baseline before editing.** Fetch the currently served Additional CSS and compare it with the saved snapshots. `after6` describes the state verified on October 4, not a guarantee about a later session. Do not overwrite work Fabel or Ryan may have done since this handoff.
- **Use the current folder:** `inter-model/`. It was renamed from `inter-model_docs/` during handoff preparation. Both handoffs are now here; older chat paths may be stale.
- **Installation is not diagnosis.** Finding Dark Reader and reading its source gave a plausible mechanism, but did not establish that its conditional styles ran in Ryan's localhost tab. Do not promote that hypothesis to root cause without observing the affected page.
- **Ancestor opacity matters.** Moving an animation from `body` to `#page` still fades every descendant, including the menu. Making a child opaque cannot cancel transparency applied to its ancestor. The first Codex change overlooked the practical consequence for the header.
- **Change one mechanism per comparison.** The second attempt changed reload targets, menu color transitions, and header snapshots together. That made the user's negative result harder to attribute. Preserve a baseline, vary one mechanism, and record the actual visible outcome before retaining it.
- **Separate verification levels.** Matching served CSS proves deployment; unchanged extracted text checks content; neither proves that a flash disappeared. Chrome forced-dark emulation and a clean profile do not reproduce an extension-controlled profile. A recording that begins at page paint cannot rule out an earlier browser blank.
- **Treat the fade as acceptable.** Ryan's latest preference is to eliminate the flash while keeping the fade. Do not spend another round tuning duration or claiming that menu animation removal alone solves the initial light frame. Early metadata may affect browser defaults, but does not by itself implement a complete dark theme.
- **Preserve uncertainty and reversibility.** This is an unresolved investigation, not a completed fix awaiting deployment. The backups, saved CSS variants, and inherited non-CSS authorization boundary remain relevant. No further site edits or rollback were made while adding these notes.
- **Session indexing can race the transcript.** A result containing `[skip] changed during index` means that attempt did not ingest the changed transcript, even if it ends with “No files changed.” Check `files_processed`/`units_indexed`; do not describe such a skip as proof that the current chat was already indexed.

## Acceptance and handoff state

- Ryan's normal dark-mode Chrome: no white flash on F5, menu clicks, or first load; steady menu; acceptable content fade; back navigation works.
- Reduced motion disables animation. Preserve content, responsive layout, links, and existing forms. Do not silently expand scope into other design defects.
- The previous handoff records separate designer defects: low-contrast Home “About Thai Solitude” link, rounded-divider artifacts, and the notice link's missing hover darkening. They remain outside this flash investigation.
- No staging/production writes, head-script changes, commits, or pushes were made by Codex. Its added CSS snapshots and this handoff are local untracked files. No background site-mutation job remains running.
- Track A session source: `/home/lauer/.codex/sessions/2026/10/04/rollout-2026-10-04T12-26-51-01a107f4-45da-77f2-b102-4345403daa84.jsonl`. Earlier index attempts sometimes skipped because the transcript changed during indexing; a later attempt succeeded with 10 indexed units. Indexing was nudged again during this handoff turn. No ledger record was requested or written.

I finished: [Arc none — ad-hoc] Handoff of the unresolved flash investigation
Next step: Reproduce the flash in Ryan's real Chrome and isolate its source
Next lane: Claude Fabel
See my work: `inter-model/CLAUDE-FABEL-2026-10-04-white-flash-handoff.md`

**TL;DR:** Ryan still sees white flashes on navigation, F5, and the menu after both Codex attempts. `custom-css-after6.css` and the earlier head script remain applied locally. Dark Reader is installed but its active behavior was not verified. Reproduce in the real profile before another fix; preserve the acceptable fade and the localhost-only boundary.
