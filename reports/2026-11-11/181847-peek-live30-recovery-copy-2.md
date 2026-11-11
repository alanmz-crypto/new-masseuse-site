# Site op `peek-live30-recovery-copy-2`

| | |
|---|---|
| operation | `run` on `live` |
| script | `link-ideas/peek-release/prove-live30-recovery.sh`  |
| lane | ops (site-ops.yml) |
| commit | `b31dc65d2b984edd2a893f76239924ec58343af1` on `codex/peek-live-verification-diagnostics` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/43075719186 |
| started | 2026-11-11 18:13:52 UTC |

## prove-live30-recovery.sh (S_TARGET=live): PASS

```text
LIVE30 recovery copy: server reads only; raw URLs preserved
COLD-SQL bytes=3919398 sha256=4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0
RECOVERY-CURRENT-SQL bytes=4189962 sha256=e2800ea70194fef1688c86b0271383dc95fbb64ec20594a690d191789fcbb14e
COLD-SOURCE ultimate-addons-for-gutenberg.php 84960e2aab455bcaf90ca1fa12e1d7916fb783f933666cb838ce96b06c4e2265
COLD-SOURCE classes/class-uagb-helper.php ddb31e3c3a200537fac002fba6ab964149f9b459a71b38a9723af84e17da70fd
COLD-SOURCE classes/class-uagb-rest-api.php f9308c31294d9493533fcde3b326e29f2ccd8c6980a824e4f213472c6efc12d3
COLD-SOURCE classes/class-uagb-loader.php 7b6b14efe37e012bc920ce6abed8a5c25c19f508b36390b64b3104d8e61fda98
COLD-SOURCE classes/class-uagb-post-assets.php 565651321cef5b64a29e91df9bf8e267ebc34c4112294f2671ade008e637d6be
COLD-SOURCE classes/class-uagb-front-assets.php 329d3330bf4c46e537a864f417e6903f7e1607f928c03cd6244ee69f700d195a
{"data_rewritten": false, "ddl_collation_substitutions": 0, "import_sha256": "e2800ea70194fef1688c86b0271383dc95fbb64ec20594a690d191789fcbb14e", "schema": 1, "scope": "fresh-current-live-copy", "source_bytes": 4189962, "source_sha256": "e2800ea70194fef1688c86b0271383dc95fbb64ec20594a690d191789fcbb14e"}
RECOVERY-STEP verify-applied wp_exit=0 marker_exit=0
RECOVERY-STDERR verify-applied bytes=171 sha256=77ae37ab88e0fcf6f77b6541e2f5beeb1e5ca5245706ffce61ff2a2a80341e37
RECOVERY-RENDER applied / HTTP=200 exact=true
RECOVERY-RENDER applied /about/ HTTP=200 exact=true
RECOVERY-RENDER applied /services/ HTTP=200 exact=true
RECOVERY-RENDER applied /contact/ HTTP=200 exact=true
RECOVERY-RENDER applied /deep-tissue-massage/ HTTP=200 exact=true
RECOVERY-RENDER applied /therapeutic-massage/ HTTP=200 exact=true
RECOVERY-RENDER applied /thai-massage/ HTTP=200 exact=true
RECOVERY-RENDER applied /work-with-me/ HTTP=200 exact=true
RECOVERY-RENDER applied /privacy-policy/ HTTP=200 exact=true
RECOVERY-RENDER applied /accessibility/ HTTP=200 exact=true
RECOVERY-RENDER applied /personal-automation/ HTTP=200 exact=true
RECOVERY-RENDER applied /personal-automation/privacy/ HTTP=200 exact=true
RECOVERY-RENDER applied /personal-automation/terms/ HTTP=200 exact=true
RECOVERY-RENDER applied /corporate-massage-portland/ HTTP=200 exact=true
RECOVERY-RENDER applied /chair-massage/ HTTP=200 exact=true
RECOVERY-RENDER applied /?s=massage HTTP=200 exact=true
RECOVERY-RENDER applied /peek-release-not-a-real-page/ HTTP=404 exact=true
RECOVERY-STEP verify-applied-after-render wp_exit=0 marker_exit=0
RECOVERY-STDERR verify-applied-after-render bytes=171 sha256=77ae37ab88e0fcf6f77b6541e2f5beeb1e5ca5245706ffce61ff2a2a80341e37
RECOVERY-STEP recoverable wp_exit=0 marker_exit=0
RECOVERY-STDERR recoverable bytes=171 sha256=77ae37ab88e0fcf6f77b6541e2f5beeb1e5ca5245706ffce61ff2a2a80341e37
RECOVERY-STEP rollback wp_exit=0 marker_exit=0
RECOVERY-STDERR rollback bytes=171 sha256=77ae37ab88e0fcf6f77b6541e2f5beeb1e5ca5245706ffce61ff2a2a80341e37
RECOVERY-RENDER original / HTTP=200 exact=true
RECOVERY-RENDER original /about/ HTTP=200 exact=true
RECOVERY-RENDER original /services/ HTTP=200 exact=true
RECOVERY-RENDER original /contact/ HTTP=200 exact=true
RECOVERY-RENDER original /deep-tissue-massage/ HTTP=200 exact=true
RECOVERY-RENDER original /therapeutic-massage/ HTTP=200 exact=true
RECOVERY-RENDER original /thai-massage/ HTTP=200 exact=true
RECOVERY-RENDER original /work-with-me/ HTTP=200 exact=true
RECOVERY-RENDER original /privacy-policy/ HTTP=200 exact=true
RECOVERY-RENDER original /accessibility/ HTTP=200 exact=true
RECOVERY-RENDER original /personal-automation/ HTTP=200 exact=true
RECOVERY-RENDER original /personal-automation/privacy/ HTTP=200 exact=true
RECOVERY-RENDER original /personal-automation/terms/ HTTP=200 exact=true
RECOVERY-RENDER original /corporate-massage-portland/ HTTP=200 exact=true
RECOVERY-RENDER original /chair-massage/ HTTP=200 exact=true
RECOVERY-RENDER original /?s=massage HTTP=200 exact=true
RECOVERY-RENDER original /peek-release-not-a-real-page/ HTTP=404 exact=true
RECOVERY-STEP verify-restored-after-render wp_exit=0 marker_exit=0
RECOVERY-STDERR verify-restored-after-render bytes=171 sha256=77ae37ab88e0fcf6f77b6541e2f5beeb1e5ca5245706ffce61ff2a2a80341e37
RECOVERY-STEP recovery-restored wp_exit=0 marker_exit=0
RECOVERY-STDERR recovery-restored bytes=171 sha256=77ae37ab88e0fcf6f77b6541e2f5beeb1e5ca5245706ffce61ff2a2a80341e37
RECOVERY-STEP verify-restored wp_exit=0 marker_exit=0
RECOVERY-STDERR verify-restored bytes=171 sha256=77ae37ab88e0fcf6f77b6541e2f5beeb1e5ca5245706ffce61ff2a2a80341e37
{"applied_state_sha256":"b34e86f75140a0e89b725e8dc9e56dd2fcc75e509155b768348bb3670daf4427","cache_delta_retained":true,"context_sha256":"c36f90c1a43194cd0f424edf5c06d0a25d43c76122e5a3446f438d6f3777d388","marker_sha256":["d1e1c7b7e9810dc14b469ea0bc85a114b1a2a2a6502f5dae9b2fab07162f713f","c79a0508b716332cc879fc91c67a4518bb93b062e09ab83d8ffd4db12196c05e","8e08d41b3babea432f17bb6e194e2c751e27e6152ba46483518f41aed6895579","7c92374f58a2d9cccb00d52aa73b6488ee004153fbb5d416c0fbff533162a40b","f797536b188b23996d82652dde7c0a72ce3c9c6439a1ead86b757cf4878e8dcc","8cab7b1f9560f04517afdb3a5471fa3e1ee13755151f334186a8146c42cd37ef","b3a049bbdffe5917bec7d577500d41befd561d3ef1f90359b72c19d6768ade0f"],"observation_sha256":["b1aa105c35878b5edd71c25737956e80e506bac2ad2aea0ce4428dda77fbec39","0d00d89c7499f1505ef8f3752975e04c2b119a2e5809c0d33c72baf01063fa4e","586b0134c802197699f7736e39a4efc98e966fa2d9c0c088aa81cb0379890157","66037f18543c074069782f63408782685040c9363226917858b84f56d0874a2d","415be1c81b54d8504f3778488608613a47f0516a0b8998b1d28fdc19c1a5e73a","e14ac392c83bdae1b7ed8e06951feb5c54c1c70a9a5c76c7fef18b4de39b2138","c9a281805557c17634200059e4c21e30da415ec61e4ff31986cb807bb86e1939","8b5a01f78677d2a65ff540be36a9a3b6793a229614e2c3387690d1bcef720260"],"receipt_sha256":"9202c8fb4e90babcba739a75660af944fc2a5adbfd1881278458312bc9ab9a0c","restored_state_sha256":"629c1e66eece68a9d7c91b6de5d7b780582a688e4d885a14aa389e053dcf05a4","result":"seven-mode-checks-and-eight-unchanged-sql-observations","schema":1,"scope":"isolated-copy-only","snapshot_sha256":"4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b"}
RECOVERY-PRIVATE-INPUTS unchanged journal_sha256=4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b sql_sha256=4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0 mode=0600
RECOVERY COPY PASSED: actual current IDs and immutable journal, exact incident verification/recovery/rollback, retained external cache rows; server was read-only
INFO release cleanup: owned relay absent
INFO release cleanup: Compose teardown completed
INFO release cleanup: owned container resources absent
INFO release cleanup: owned volume resources absent
INFO release cleanup: owned network resources absent
INFO release cleanup: owned temporary directory absent
INFO release cleanup: PASSED; owned relay, containers, volumes, networks and temporary directory absent
```

**RESULT: PASS** - prove-live30-recovery.sh succeeded

_finished 2026-11-11 18:18:45 UTC_
