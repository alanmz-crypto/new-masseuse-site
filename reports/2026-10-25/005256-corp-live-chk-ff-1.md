# Site op `corp-live-chk-ff-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `corporate/check-corporate-massage.mjs` `https://thaisolitude.com /tmp/corp-shots live-ff firefox quick` |
| lane | ops (site-ops.yml) |
| commit | `746bdf3f135fb353939db65b0cf0023f05078ba1` on `feat/2026-10-24-corporate-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40148355572 |
| started | 2026-10-25 00:52:11 UTC |

## check-corporate-massage.mjs https://thaisolitude.com /tmp/corp-shots live-ff firefox quick(S_TARGET=live): FAIL (exit 1)

```text
FAIL page light-1280: HTTP 200  [null]
FAIL page light-1280: one h1 with the page title
FAIL page light-1280: the section headings, in order, no skipped heading level  [0 h2, skipped=false]
FAIL page light-1280: the 7 FAQ questions, once each, in order  [0]
PASS page light-1280: no links inside FAQ answers  [0]
FAIL page light-1280: the first FAQ item is open, the others closed
PASS page light-1280: no side-scroll  [0]
PASS page light-1280: no picture on the page  [0]
PASS page light-1280: no we/our/us in the visible text  [0]
PASS page light-1280: no announcement strip on the page itself  [0]
FAIL page light-1280: both quote buttons carry one identical mailto link  [0 buttons]
FAIL page light-1280: the visible email address is a mailto link  [0]
FAIL page light-1280: the in-page anchors (booking options, chair, table) exist  [false,false,false]
FAIL page: title tag  [Robot Challenge Screen]
FAIL page: meta description
FAIL page: canonical link points at this page
PASS page: indexable (no noindex)  [no robots meta]
node:internal/url:818
      href = bindingUrl.parse(input, base, true);
                        ^

TypeError: Invalid URL
    at new URL (node:internal/url:818:25)
    at file:///home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/corporate/check-corporate-massage.mjs:104:17 {
  code: 'ERR_INVALID_URL',
  input: ''
}

Node.js v22.23.3
```

**RESULT: FAIL** - check-corporate-massage.mjs failed (exit 1)

_finished 2026-10-25 00:52:56 UTC_
