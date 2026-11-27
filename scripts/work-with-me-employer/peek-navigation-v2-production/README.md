# Production navigation v2

The four payload files match the candidate verified on staging40. This archive targets the production host. Build it with Python from a checkout containing the historical v1 source.

```sh
python3 scripts/work-with-me-employer/peek-navigation-v2-production/package.py /tmp/peek-production.zip
```

The installer checks the physical site and content directories, stored and configured URLs, active components and versions, companion snippets, navigation post, WPCode cache and immutable asset paths. A mismatched preflight stops apply. Rollback checks the recorded fingerprint and can repair a stale navigation cache. Apply and rollback change the snippet’s modified date.

## Local rehearsal

Supply unpacked WordPress 7.1.5, Astra 4.14.1, WPCode 2.4.2 and Spectra 2.20.7 directories. The Docker fixture has an internal network and synthetic posts. Run from this package directory.

```sh
stack=$(mktemp -d)
bash tests/rehearsal-up.sh "$stack" "$wpcode_dir" "$astra_dir" "$spectra_dir" "$wordpress_dir"
python3 -I -B tests/rehearse.py "$stack"
bash tests/rehearse-wrapper.sh "$stack"
(cd "$stack" && docker compose down -v)
```

The wrapper test runs the actual WP-CLI operations, database export, read-back, snapshot selection and public HTML/asset verifier. Its adapters model SSH, off-site storage and SiteGround purge locally. It checks browser invocation separately from browser behavior. The read-only browser verifier exercises Chromium and Firefox on Home and Thai Massage, plus Chromium transfer sizes across Services and Thai Massage. Run it with the project's Playwright installation via `TS_PLAYWRIGHT_ROOT`. An explicit loopback origin selects an isolated replay.

## Production review and recovery

Review the scoped change against main and confirm the hosting recovery point and current WPCode cache inventory. The apply request is `live-peek-navigation-v2-1`. Ryan reviews and merges the request, then separately authorizes and manually starts the main-only live workflow.

The wrapper creates a fresh database export, verifies its downloaded digest and waits for the existing encrypted off-site hook. It prints the production snapshot path and SHA-256 and preserves the snapshot off-server. After purging caches it checks public pages, assets and crawl directives. On apply it also runs the browser verifier. Review both the workflow’s post-change and restore reports, including their exit statuses.

If verification fails after a write, identify the stored and public state from that run before choosing recovery. For an authorized rollback, prepare a fresh main request ID, keep the same script and `tests: "auto"`, and set its arguments to `["--rollback", "<absolute production snapshot path from apply>", "<full SHA-256 from apply>"]`. Ryan starts that workflow separately. Successful rollback restores the v1 code and cached snippet, checks the two inline tags on all 13 sitemap routes, and retains both v2 assets for previously cached pages.


A failed snapshot copy after apply is reported immediately. The wrapper still checks the stored and public release and returns failure. A failed write triggers read-only preflight. When the installed version can be identified after a change, the wrapper purges, checks that version and preserves the original write exit status. An unverifiable state requires operator inspection. Once the installer has attempted the snippet swap, both verified assets are retained for visitors who received v2 HTML.

The live navigation request compares screenshots against staging40. The comparison suppresses the one-time tip for consistent captures. Matching browser sizes and identical releases on both sites are necessary to interpret a zero-difference result. The post-change and restore reports run after successful writes in the parent workflow.
