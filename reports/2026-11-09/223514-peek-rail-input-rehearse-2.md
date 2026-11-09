# Site op `peek-rail-input-rehearse-2`

| | |
|---|---|
| operation | `rehearse` on `staging39` |
| installer | `link-ideas/rail-input/install-rail-input.php` |
| lane | ops (site-ops.yml) |
| commit | `c42e1611780937fba34a57a8dfa5fbe67655c19f` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42712817404 |
| started | 2026-11-09 22:32:54 UTC |

## rehearsal of link-ideas/rail-input/install-rail-input.php on a throwaway copy of staging39: FAIL (exit 1)

```text
Rehearsal of link-ideas/rail-input/install-rail-input.php on a throwaway copy of staging39 (staging39.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging39.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.4
  source home=http://staging39.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 680395 bytes gzipped, files 268M
[2] starting the pinned practice images and importing ...
Deprecated: Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors.php on line 95

[3] baseline: fingerprint A and the 10 pages
  200 /
  200 /about/
  200 /services/
  200 /thai-massage/
  200 /therapeutic-massage/
  200 /deep-tissue-massage/
  200 /work-with-me/
  200 /contact/
  200 /privacy-policy/
  200 /accessibility/
  -> PASS  pages answer 200 before the change

[preflight] wp eval-file link-ideas/rail-input/install-rail-input.php preflight
    home=http://localhost:8090 rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
    Success: Preflight ready; title/ownership verified; full post/meta/terms captured read-only.
  -> PASS  preflight

[apply] wp eval-file link-ideas/rail-input/install-rail-input.php apply
    home=http://localhost:8090 rail-input=ready page=1348 bytes=36892 content_sha256=0203ac81f0eca37b7d2f217d154beda0f0485faae55072fd75276829c4c77748 full_state_sha256=d502affb09862f28d3b7f462f5376561e679b74a2900d99549ebbe9e6da437dc
    Error: Content save/full-state read-back failed.; recovery failed: Unknown concurrent page state; refusing recovery overwrite. Snapshot: /backups/rail-input-before-20261109-223509-91cc2e2e.json
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-09 22:35:12 UTC_
