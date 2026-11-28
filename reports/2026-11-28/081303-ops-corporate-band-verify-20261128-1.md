# Site op `ops-corporate-band-verify-20261128-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `corporate-band/push-corporate-band-live.sh` `--check` |
| lane | ops (site-ops.yml) |
| commit | `e518d2e8feecbfd33cb6a9050e52cc0de9bb9dc3` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/46124718712 |
| started | 2026-11-28 08:12:41 UTC |

## push-corporate-band-live.sh --check(S_TARGET=live): PASS

```text
Live published pages discovered through REST: 16
PASS page /winding-willow/
PASS page /chair-massage/
PASS page /corporate-massage-portland/
PASS page /terms/ (intentional noindex)
PASS page /privacy/ (intentional noindex)
PASS page /personal-automation/ (intentional noindex)
PASS page /accessibility/
PASS page /privacy-policy/
PASS page /work-with-me/
PASS page /thai-massage/
PASS page /therapeutic-massage/
PASS page /deep-tissue-massage/
PASS page /contact/
PASS page /services/
PASS page /about/
PASS page /home/
INFO no published posts available for a post-route sample
PASS internal destination /winding-willow/: HTTP 200
PASS internal destination /corporate-massage-portland/: HTTP 200
PASS live sitemap: HTTP 200
PASS category archive /category/general/
PASS representative 404 route

LIVE BOOKING NOTICE CHECK: PASS
Checked 16 published pages, an available post, internal destinations, live robots/sitemap, category archive, and 404.
The external booking target was checked in markup only; Ryan has confirmed it works by opening it.
```

**RESULT: PASS** - push-corporate-band-live.sh succeeded

_finished 2026-11-28 08:13:02 UTC_
