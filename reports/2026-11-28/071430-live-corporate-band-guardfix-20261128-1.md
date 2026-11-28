# Site op `live-corporate-band-guardfix-20261128-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `corporate-band/push-corporate-band-live.sh` `--apply` |
| lane | live (site-live.yml, from main) |
| commit | `8856f4c9e3df023b9b4bd981bb8c3675fe3b9796` on `main` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/46120215246 |
| started | 2026-11-28 07:13:57 UTC |

## push-corporate-band-live.sh --apply(S_TARGET=live): FAIL (exit 1)

```text
Live is www/thaisolitude.com/public_html
Error: Live snippet, CSS, or visibility differs from the reviewed state; stop and inspect. host=http://thaisolitude.com state=CHANGED blog_public=1 snippet=#1268 code_sha=537c9a7be28cac1574c9b597f850008a66a7c7be4bb818cf1b69620f9d678363 css=#29 css_sha=7a14b1ff2fff98be296f4d9a4ab34a8669cb95a58eee7248fce4427ec97f064e cache_fallback=no expected_new_code_sha=7298fc1977c11e46a5aa2d9ad4d70f4dc59d0b7049b1b10eda88b2e04f026c7c expected_new_css_sha=2870307aa8248d06166b2bd7dfc8652cc3dae2cad859bdc718e530b593a1d4f5
```

**RESULT: FAIL** - push-corporate-band-live.sh failed (exit 1)

_finished 2026-11-28 07:14:28 UTC_
