# WordPress-native design package (no MU-plugins)

Source of truth = **localhost** (`http://localhost:8090`). The package writes only WordPress storage, so a SiteGround
stage-to-live push carries it: Additional CSS (`custom_css`/astra), option `ihaf_insert_header`, `astra-settings[footer-copyright-editor]`,
and `post_content` of all 8 pages. No files are installed on any host.

| File | Purpose |
|---|---|
| `build-release.py <target-url> <out.json>` | Exports the design from localhost, rewrites `http://localhost:8090` to the target, computes hashes |
| `install-native.php` | Applies a release with WP-CLI. No arg = read-only (prints current-state hashes to pin, then full preflight). `apply-native` = apply. Refuses on any drift; rolls itself back on any mismatch |
| `rollback-native.php` | Restores the values saved before apply (`TS_ROLLBACK_OPTION=ts_native_sitewide_before_YYYYMMDD`, arg `apply-rollback`) |
| `release-staging14.json` | Release built for `https://staging14.thaisolitude.com` (hashes marked `FILL` until staging14 is read) |

## Rehearsal done on localhost (2026-10-04)
Restored the pre-restyle site (files `dd66e43f`, DB `1f6d40d2`) into an isolated stack on :8092, built a release for it, pinned,
preflighted and applied. Result vs localhost: CSS identical, visible text identical, fade script present, license footer present,
fonts loaded, 0 console errors, **0.000% differing pixels on all 8 pages (desktop)**. Rollback restored the exact original hashes;
re-apply worked; a second apply refused (rollback option exists). No MU files existed at any point.

## Deploying to staging14 (needs Ryan's go and an unlocked SSH key)
1. Read-only state read: `wp --path=<staging14 public_html> eval-file install-native.php` (with the release JSON next to it).
2. Take a verified DB backup (`wp db export - | gzip`, `gzip -t`, sha256) and pin it plus the printed hashes into the release JSON.
3. Re-run preflight, then `apply-native`. Verify as in the rehearsal (text, CSS, pixels, Dark Reader harness).
4. Remove the old MU files from staging14 separately: `ts-staging14-home-only|about-only|pages-only` `.php` and `.css` in `wp-content/mu-plugins/`.
   They are inert on other hosts but must not linger; the live copies need the same clean-up after the next push.
5. Push staging14 to live. **The push must include the posts table (Additional CSS and pages) and the options table (header script and Astra
   settings).** Then verify live read-only: CSS served, fade script, license footer, text parity with staging14.

The earlier MU-based loaders, installers and release files were deleted on 2026-10-04 and must never be recreated (AGENTS.md rule: no MU-plugins).

## Applied to production (2026-10-04)
Route chosen by Ryan: the native package applied directly to production (not a SiteGround table push, so no URL-rewrite or
search-setting dependency). Order: removed the 6 inert MU files (archived at `~/private-backups/production-mu-plugins-removed-20261004.tar.gz`),
verified production DB backup `~/private-backups/thaisolitude-PRODUCTION-before-native-20261004.sql.gz` (sha256 d32b0b8d3db0bd93...),
pinned preflight, `apply-native`. Result: live == localhost on all 8 pages (css, text, scripts, footer; 0.000% pixel diff), robots unchanged
(crawlable, blog_public=1), no noindex, no staging/localhost links, no MU anywhere, Dark Reader harness 0 light frames over 15 navigations.
Rollback option in production DB: `ts_native_sitewide_before_20261004` (run `rollback-native.php` with `TS_ROLLBACK_OPTION=...`). Remove that option
only after Ryan has accepted the result. SEO titles / meta descriptions were NOT changed (still the old ones on all hosts).

## CSS-only installer (2026-10-05) — use this for design fixes
`install-css-native.php` / `rollback-css-native.php` change ONLY the Additional CSS and verify that pages, header option and astra-settings are byte-identical
afterward (fingerprint). Release JSON: `{scope:"css-only", target, css, css_sha256, accept_before_css_sha256, backup_basename, backup_sha256}`.
Rehearsed on the localhost restore stack (preflight, apply, pixel-identical to localhost, rollback to the exact original hash, re-apply).
**Why:** on 2026-10-05 staging15 turned out to hold newer copy edits (Home H1, About, Services text and a different footer wording) that localhost did not have.
The full package (`install-native.php`) writes all 8 pages and the footer from localhost and would have reverted them; its footer guard refused. Rule: before any apply,
compare the target's visible text with localhost; if they differ, use the CSS-only installer and never overwrite the target's content.
Applied 2026-10-05: hero-image stacking fix (`ts-hero-image-stacking.css`, in `custom-css-after11.css`) on localhost and staging15. Production NOT yet.
