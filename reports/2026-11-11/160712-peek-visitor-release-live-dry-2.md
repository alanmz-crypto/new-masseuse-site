# Site op `peek-visitor-release-live-dry-2`

| | |
|---|---|
| operation | `run` on `live` |
| script | `link-ideas/push-peek-release.sh`  |
| lane | ops (site-ops.yml) |
| commit | `ae5172f2e25738cbc32ef3d6df1664898d323c10` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/43055031280 |
| started | 2026-11-11 16:06:58 UTC |

## push-peek-release.sh (S_TARGET=live): FAIL (exit 1)

```text
Target site: live   component: peek-release   mode: dry
Error: Installed/loaded Spectra differs from the reviewed shipped source. Nothing changed.
live is www/thaisolitude.com/public_html
RELEASE-SPECTRA-SOURCE {"files":{"ultimate-addons-for-gutenberg.php":"missing-or-noncanonical","classes/class-uagb-helper.php":"missing-or-noncanonical","classes/class-uagb-rest-api.php":"missing-or-noncanonical","classes/class-uagb-loader.php":"missing-or-noncanonical","classes/class-uagb-post-assets.php":"missing-or-noncanonical","classes/class-uagb-front-assets.php":"missing-or-noncanonical"},"header_version":"2.20.6","runtime_version":"2.20.6","loaded":{"main":false,"UAGB_Helper":false,"UAGB_Rest_API":false,"UAGB_Loader":false,"UAGB_Post_Assets":false,"UAGB_Front_Assets":false},"save_post_priority_10":true}
```

**RESULT: FAIL** - push-peek-release.sh failed (exit 1)

_finished 2026-11-11 16:07:10 UTC_
