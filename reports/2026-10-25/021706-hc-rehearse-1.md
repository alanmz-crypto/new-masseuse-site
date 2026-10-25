# Site op `hc-rehearse-1`

| | |
|---|---|
| operation | `rehearse` on `staging38` |
| installer | `home-card/install-home-corporate-card.php` |
| lane | ops (site-ops.yml) |
| commit | `a65568e779030f8cee3011b24f094e732d0659ef` on `feat/2026-10-25-home-corporate-card` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40153779997 |
| started | 2026-10-25 02:15:54 UTC |

## rehearsal of home-card/install-home-corporate-card.php on a throwaway copy of staging38: PASS

```text
Rehearsal of home-card/install-home-corporate-card.php on a throwaway copy of staging38 (staging38.thaisolitude.com), at http://localhost:8090 on this machine
  source folder=www/staging38.thaisolitude.com/public_html
  source prefix=iej_
  source version=7.1.3
  source home=http://staging38.thaisolitude.com
[1] streaming the database and the files (read-only on the server) ...
  database 599754 bytes gzipped, files 316M
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

[preflight] wp eval-file home-card/install-home-corporate-card.php preflight
    Success: Preflight passed; nothing changed. host=http://localhost:8090 state=ready page=#296 card=ready (anchor=1 old-pinned=1 card-in-front=0 card-id=0 heading=1) css_block=absent picture=media#1269
  -> PASS  preflight

[apply] wp eval-file home-card/install-home-corporate-card.php apply
    Success: Inserted the card at the top of "The Massage I Offer" on the Home page #296 and added the ts-home-corp CSS block. Before-snapshot: /backups/home-corporate-card-before-20261025-021658.json
  -> PASS  apply

[apply-again] wp eval-file home-card/install-home-corporate-card.php apply
    Success: Nothing to do: already applied. host=http://localhost:8090 state=applied page=#296 card=applied (anchor=1 old-pinned=0 card-in-front=1 card-id=1 heading=1) css_block=exact
  -> PASS  apply a second time
  -> PASS  applying twice changes nothing more (idempotent)

[rollback] wp eval-file home-card/install-home-corporate-card.php rollback /backups/home-corporate-card-before-20261025-021658.json
    Success: Rollback done: card removed, CSS block removed. Home page is byte-identical to the before-snapshot; Additional CSS is byte-identical to the before-snapshot.
  -> PASS  rollback with home-corporate-card-before-20261025-021658.json
  -> PASS  after rollback every fingerprint equals the baseline (A)

[re-apply] wp eval-file home-card/install-home-corporate-card.php apply
    Success: Inserted the card at the top of "The Massage I Offer" on the Home page #296 and added the ts-home-corp CSS block. Before-snapshot: /backups/home-corporate-card-before-20261025-021701.json
  -> PASS  apply again after the rollback
  -> PASS  re-applied state equals the first applied state (B; ids of re-created snippets and SEO rows set aside)

[4] the 10 pages after the change, and PHP errors
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
  -> PASS  pages answer 200 with the change applied
  no PHP error, warning or deprecation that the copy did not already log
  -> PASS  no new PHP errors

[5] what the change touches (fingerprint A -> B; this is what staging will see move):
  changed: css	29
  changed: page	home
  rows now: iej_options	447
  rows now: iej_postmeta	630
  rows now: iej_posts	103

REHEARSAL PASSED: home-card/install-home-corporate-card.php on a copy of staging38 (apply, idempotent re-apply, exact rollback, re-apply, pages 200).
```

**RESULT: PASS** - rehearsal passed

_finished 2026-10-25 02:17:05 UTC_
