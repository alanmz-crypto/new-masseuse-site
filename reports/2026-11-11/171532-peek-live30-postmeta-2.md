# Site op `peek-live30-postmeta-2`

| | |
|---|---|
| operation | `run` on `live` |
| script | `link-ideas/inspect-live30-postmeta.sh`  |
| lane | ops (site-ops.yml) |
| commit | `20e9af7a84783d99d133c80f3d062985ad300a39` on `codex/peek-live-verification-diagnostics` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/43066327533 |
| started | 2026-11-11 17:15:23 UTC |

## inspect-live30-postmeta.sh (S_TARGET=live): PASS

```text
LIVE30-POSTMETA {"captured_utc":"2026-11-11 17:15:30.000000","read_only":true,"wordpress_bootstrap":false,"requested":5,"found":3}
LIVE30-POSTMETA-ROW {"kind":"postmeta","meta_id":4701,"post_id":null,"present":false,"meta_key_sha256":null,"meta_value_bytes":null,"meta_value_sha256":null}
LIVE30-POSTMETA-ROW {"kind":"postmeta","meta_id":4702,"post_id":null,"present":false,"meta_key_sha256":null,"meta_value_bytes":null,"meta_value_sha256":null}
LIVE30-POSTMETA-ROW {"kind":"postmeta","meta_id":5089,"post_id":1254,"present":true,"meta_key_sha256":"2e9207df9631808ef7779f3a1abfdd1c2cdd1668b3cef4085730edb9e1fa26a4","meta_value_bytes":740,"meta_value_sha256":"30f619157647f3a5cd68219525b5b12a8dc210e828f0a130cf4f3fc9ed7d76ba"}
LIVE30-POSTMETA-ROW {"kind":"postmeta","meta_id":5090,"post_id":1255,"present":true,"meta_key_sha256":"2e9207df9631808ef7779f3a1abfdd1c2cdd1668b3cef4085730edb9e1fa26a4","meta_value_bytes":740,"meta_value_sha256":"30f619157647f3a5cd68219525b5b12a8dc210e828f0a130cf4f3fc9ed7d76ba"}
LIVE30-POSTMETA-ROW {"kind":"postmeta","meta_id":5091,"post_id":1257,"present":true,"meta_key_sha256":"2e9207df9631808ef7779f3a1abfdd1c2cdd1668b3cef4085730edb9e1fa26a4","meta_value_bytes":693,"meta_value_sha256":"65156d9a7ff627f3726c259b781bbb272f6b3bc0eed91220fc6db1e65303bff6"}
```

**RESULT: PASS** - inspect-live30-postmeta.sh succeeded

_finished 2026-11-11 17:15:30 UTC_
