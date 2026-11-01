# Site op `review-e-live-verify-20261101`

| | |
|---|---|
| operation | `run` on `live` |
| script | `review-e/check-live-result.mjs` `https://thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `a1fcef4a3f96fff6431843a82a447d0c05354bf9` on `codex/2026-11-01-review-e-live-checks` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41342103314 |
| started | 2026-11-01 19:31:03 UTC |

## check-live-result.mjs https://thaisolitude.com(S_TARGET=live): FAIL (exit 2)

```text
PASS native E live identity and exact canonical package SHA
PASS native E actual completed snapshot target and old/new checksums
PASS native E all 16 original and responsive files exactly preserved
PASS native E page home complete applied store and unchanged raw SureRank rows
PASS native E page therapeutic-massage complete applied store and unchanged raw SureRank rows
PASS native E owned snippet SiteReviewPerformance full post/meta/terms and exact runtime
PASS native E owned snippet SiteReviewEmoji full post/meta/terms and exact runtime
PASS native E owned WebP full post/meta/files, relative logical path and prepared bytes
PASS native E every owned upload file and prefix SHA intact inside uploads
NATIVE_E_RESULT {"snapshot":"bundle-e-before-20261101-191447.json","snapshot_sha256":"0ffe75e3aa18c34d5132670f812224b6cc3e152d854cb5b335cafc31f1720f75","package_sha256":"ab06069f6f755abe3f65df2dd5782edf17f7ffa0529f2aa514a537469e6d8307","wp_version":"7.1.4","pages":{"home":{"id":296,"sha256":"a5060e297017966ba024dc59c666f8eaef5cb0ccb0b0611c44c51e36b82308ed"},"therapeutic-massage":{"id":1107,"sha256":"2fbc7d388dc825744c38b7bd1b9d2acab973691f6dd9fd0eab480ebbd34be234"}},"snippets":{"SiteReviewPerformance":{"id":1332,"sha256":"a3a3a54348563cd28838853d89a3db3483e71e799efabb73376d566fca6fbe8e"},"SiteReviewEmoji":{"id":1333,"sha256":"cb638e3d1155a332752f28587ff2917fca1cd3edfbd99fcead6219cd8eb52f2d"}},"media":{"id":1331,"relative_file":"2026/11/therapeutic-red-400.webp","sha256":"e795b73e4272e90f92db59b2734be41dd06365ac8ca6f0e8aa5e02d11ee1a756","width":400,"height":313,"bytes":21796,"file_count":3},"original_file_count":16,"owned_upload_file_count":3}
Bundle E native result: 9 checks, 0 failures; no content writes attempted.
INVALID: hosting robot challenge; stop without more requests
Bundle E: 0 assertions, 0 failures, INVALID challenge; 8 reviewed images, four page loads
```

**RESULT: INVALID** - check-live-result.mjs could not test (exit 2, see its output; usually the SiteGround bot challenge): nothing was tested, run it once more later

_finished 2026-11-01 19:32:07 UTC_
