# Site op `probe-arrival-staging39-20261108-2`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/probe-arrival.mjs` `https://staging39.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `c0b4b6a5b4084c5af8457df873766184dca814f2` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42433570659 |
| started | 2026-11-08 01:17:08 UTC |

## probe-arrival.mjs https://staging39.thaisolitude.com(S_TARGET=staging39): PASS

```text
185ms scroll-event  y=2 :: at addEventListener.passive (<anonymous>:8:36)
202ms scroll-event  y=9 :: at addEventListener.passive (<anonymous>:8:36)
219ms scroll-event  y=22 :: at addEventListener.passive (<anonymous>:8:36)
318ms scrollTo [{"top":3538.71875,"behavior":"auto"}] y=223 :: at obj.<computed> (<anonymous>:6:59) <- at jump (https://staging39.thaisolitude.com/thai-massage/:2152:12) <- at arrive (https://staging39.thaisolitude.com/thai-massage/:2169:87)
319ms scroll-event  y=310 :: at addEventListener.passive (<anonymous>:8:36)
916ms scrollTo [{"top":3703,"left":0,"behavior":"smooth"}] y=3314 :: at obj.<computed> (<anonymous>:6:59) <- at astraSmoothScroll (https://staging39.thaisolitude.com/wp-content/themes/astra/assets/js/minified/frontend.min.js?ver=4.14.1:1:405) <- at t (https://staging39.thaisolitude.com/wp-content/themes/astra/assets/js/minified/frontend.min.js?ver=4.14.1:1:23303)
919ms scroll-event  y=3338 :: at addEventListener.passive (<anonymous>:8:36)
final {"y":3703,"h":0,"sm":"164px","runtime":true}
```

**RESULT: PASS** - probe-arrival.mjs succeeded

_finished 2026-11-08 01:17:50 UTC_
