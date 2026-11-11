# Site op `peek-live30-recovery-copy-1`

| | |
|---|---|
| operation | `run` on `live` |
| script | `link-ideas/peek-release/prove-live30-recovery.sh`  |
| lane | ops (site-ops.yml) |
| commit | `9fb36811170675f766add419e49f160cac5e7f5a` on `codex/peek-live-verification-diagnostics` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/43074759786 |
| started | 2026-11-11 18:07:52 UTC |

## prove-live30-recovery.sh (S_TARGET=live): FAIL (exit 1)

```text
LIVE30 recovery copy: server reads only; raw URLs preserved
COLD-SQL bytes=3919398 sha256=4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0
RECOVERY-CURRENT-SQL bytes=4189962 sha256=8c3f6ba96d5ef0367e33df00dce56d899506f87deaa19d3b3d0a2e1dd90df5ad
COLD-SOURCE ultimate-addons-for-gutenberg.php 84960e2aab455bcaf90ca1fa12e1d7916fb783f933666cb838ce96b06c4e2265
COLD-SOURCE classes/class-uagb-helper.php ddb31e3c3a200537fac002fba6ab964149f9b459a71b38a9723af84e17da70fd
COLD-SOURCE classes/class-uagb-rest-api.php f9308c31294d9493533fcde3b326e29f2ccd8c6980a824e4f213472c6efc12d3
COLD-SOURCE classes/class-uagb-loader.php 7b6b14efe37e012bc920ce6abed8a5c25c19f508b36390b64b3104d8e61fda98
COLD-SOURCE classes/class-uagb-post-assets.php 565651321cef5b64a29e91df9bf8e267ebc34c4112294f2671ade008e637d6be
COLD-SOURCE classes/class-uagb-front-assets.php 329d3330bf4c46e537a864f417e6903f7e1607f928c03cd6244ee69f700d195a
{"data_rewritten": false, "ddl_collation_substitutions": 0, "import_sha256": "8c3f6ba96d5ef0367e33df00dce56d899506f87deaa19d3b3d0a2e1dd90df5ad", "schema": 1, "scope": "fresh-current-live-copy", "source_bytes": 4189962, "source_sha256": "8c3f6ba96d5ef0367e33df00dce56d899506f87deaa19d3b3d0a2e1dd90df5ad"}
RECOVERY-STEP verify-applied wp_exit=0 marker_exit=0
RECOVERY-STDERR verify-applied bytes=171 sha256=77ae37ab88e0fcf6f77b6541e2f5beeb1e5ca5245706ffce61ff2a2a80341e37
RECOVERY-RENDER applied / HTTP=200 exact=true
COLD PROOF FAIL: response verification incomplete
INFO release cleanup: owned relay absent
INFO release cleanup: Compose teardown completed
INFO release cleanup: owned container resources absent
INFO release cleanup: owned volume resources absent
INFO release cleanup: owned network resources absent
INFO release cleanup: owned temporary directory absent
INFO release cleanup: PASSED; owned relay, containers, volumes, networks and temporary directory absent
```

**RESULT: FAIL** - prove-live30-recovery.sh failed (exit 1)

_finished 2026-11-11 18:08:52 UTC_
