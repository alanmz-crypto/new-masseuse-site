Apply the Thai Solitude editorial restyle: first on the local site, then (only after I say so) on staging12.

The restyle is CSS only. It must not change any page text, image, block, menu or setting.
Never touch production (thaisolitude.com). Never run a SiteGround deploy.

## Input

The file `ts-restyle.css` (I've downloaded it). Put it in the repo at
`scripts/restyle/ts-restyle.css` and commit nothing until I review.

It has two parts:
- Line 1: an `@import` for two Google Fonts (Newsreader, Hanken Grotesk).
- A block between `/* TS RESTYLE START` and `/* TS RESTYLE END */`.

It is designed to sit in WordPress **Additional CSS** (the `custom_css` post for the
astra theme), after the existing CSS, which must stay exactly as it is.

## Phase 1: local (http://localhost:8090)

1. Back up first: `./scripts/backup-site.sh --note pre-restyle`.
   Then save the current Additional CSS, byte for byte, to
   `scripts/restyle/custom-css-before-LOCAL.css`.
2. Build the new Additional CSS as:
   1. the `@import` line, as the very first line (CSS ignores an @import that comes
      after any rule);
   2. the existing Additional CSS, unchanged;
   3. a blank line, then the START…END block.

   Make this idempotent. If the START/END markers or the @import line are already
   there, replace them instead of adding a second copy. Save the result to
   `scripts/restyle/custom-css-after.css` and show me a diff against the before file.
   The diff must contain only the added lines.
3. Apply it with WP-CLI through the `wpcli` service, using
   `wp_update_custom_css_post( $css, array( 'stylesheet' => 'astra' ) )`.
   Don't edit the database by hand. Then flush caches (`wp cache flush`, plus the
   SiteGround cache plugin's purge if it's active locally).
4. Verify:
   - In the logged-out HTML of `/`, `/about/`, `/services/`, `/work-with-me/` and
     `/contact/`, the `<style id="wp-custom-css">` block starts with the @import line
     and contains the START marker exactly once.
   - **Text unchanged.** For each of the five pages, compare `document.body.textContent`
     (whitespace collapsed) before and after applying. It must be identical. Use
     textContent, not innerText, because the restyle intentionally changes letter case.
   - **Screenshots.** Take full-page screenshots at 1440px and 390px wide into
     `scripts/restyle/shots-local/`. Also capture one with the mobile menu open at 390px.
   - Check the browser console for CSS errors.
5. Stop and report to me with:
   - the diff summary
   - the text check results
   - the screenshot paths
   - anything that looks off

   Local content differs from staging12 on four pages, so some text will differ from
   the preview I was shown. That's expected; judge the styling, not the wording.

Rollback (local): put `custom-css-before-LOCAL.css` back with the same WP-CLI call.

## Phase 2: staging12 (only after I reply "go staging")

Staging12 is the target. It's the only staging copy with all five pages, so treat any
note that says staging5 as out of date.

1. Remind me to take a SiteGround backup of staging12 first, and wait until I confirm
   I've done it.
2. Write `scripts/restyle/install-restyle-staging12.php`, following the same pattern as
   `scripts/install-work-with-me-staging12.php`:
   - Refuse to run unless the host is staging12.thaisolitude.com and the theme is astra.
   - **With no arguments, preflight only (read-only).** Print the current Additional CSS
     size and sha256, whether the restyle markers are already present, and the exact
     bytes that would be added.
   - Refuse to proceed if the current Additional CSS doesn't match the expected sha256.
     That expected value is the sha256 of the CSS I gave you in the snapshot
     (`css/custom-css.css`), or of the restyled version if the restyle is already there.
   - **With the confirmation argument,** first store the current CSS in the option
     `ts_restyle_backup_css`, then apply the same composition as step 2 of Phase 1 using
     `wp_update_custom_css_post`. Have it print a one-line rollback command that restores
     from that option.
3. Show me the script and the preflight output. Run it with the confirmation argument
   only after I approve.
4. After applying:
   - purge the SiteGround cache;
   - re-run the Phase 1 checks against https://staging12.thaisolitude.com
     (text identical, @import first, marker once);
   - save screenshots to `scripts/restyle/shots-staging12/`;
   - report back.

## Selectors to watch (from the designer)

Most of the restyle uses stable classes (Astra global colors, `wp-block-*`, `ast-*`,
`ts-*`). Two rules are tied to specific pages:
- the About page band that holds the accordions, found with
  `:has(.wp-block-uagb-faq)`;
- the Contact page cards, found with `body.page-id-301`.

If the Contact page ID is different on any site, the cards keep their old mint
background. Tell me if that happens; don't patch around it.

## Later, not now

Promoting to production is a separate decision and needs my explicit approval. When
the time comes: a SiteGround Custom Deploy only carries Additional CSS if the posts
table (custom_css post) is included.
