# Site op `peek-rail-input-rehearse-3`

| | |
|---|---|
| operation | `rehearse` on `staging39` |
| installer | `link-ideas/rail-input/install-rail-input.php` |
| lane | ops (site-ops.yml) |
| commit | `696d1020a762644a73980e21c039787337720a82` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42713697500 |
| started | 2026-11-09 22:38:49 UTC |

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
    Error: Content save/full-state read-back failed: state["meta"]["_uag_css_file_name"] expected array sha256=c66a8d045c46fe80b7a972cb1978ce1e472461da841b0a4bbcb25614c14b3636 actual absent; state["meta"]["_uag_page_assets"] expected array sha256=366037d18768ccb75cb9169902c52f6b569f97123d71c90f82db86a757b8477b actual absent; state["meta"]["surerank_seo_checks_last_updated"][0] expected string sha256=a0a30428f0119998fb2c6a46075b851d48b7853c46c163b4fb9555579a8dbb91 actual string sha256=fa6630a58e0552ddfa3649481b5016f10f8bcfdd132c0d35ed6ce5afb19e819e; state["meta"] key-order/set expected array sha256=547ea323b3f254f5e113466a222ed6e7568a613d111b4e53d40b411f7f496fa4 actual array sha256=b60d3b85eb58c69bda5c674e3058e7105a2987082154fa3f60478ae67487da44; recovery failed: Unknown concurrent page state; refusing recovery overwrite. Differences from intended state: state["meta"]["_uag_css_file_name"] expected array sha256=c66a8d045c46fe80b7a972cb1978ce1e472461da841b0a4bbcb25614c14b3636 actual absent; state["meta"]["_uag_page_assets"] expected array sha256=366037d18768ccb75cb9169902c52f6b569f97123d71c90f82db86a757b8477b actual absent; state["meta"]["surerank_seo_checks_last_updated"][0] expected string sha256=a0a30428f0119998fb2c6a46075b851d48b7853c46c163b4fb9555579a8dbb91 actual string sha256=fa6630a58e0552ddfa3649481b5016f10f8bcfdd132c0d35ed6ce5afb19e819e; state["meta"] key-order/set expected array sha256=547ea323b3f254f5e113466a222ed6e7568a613d111b4e53d40b411f7f496fa4 actual array sha256=b60d3b85eb58c69bda5c674e3058e7105a2987082154fa3f60478ae67487da44 Snapshot: /backups/rail-input-before-20261109-224106-2f5b6814.json
    
  -> FAIL  apply

REHEARSAL FAILED: apply did not succeed, so nothing after it was tried.
```

**RESULT: FAIL** - rehearsal failed

_finished 2026-11-09 22:41:09 UTC_
