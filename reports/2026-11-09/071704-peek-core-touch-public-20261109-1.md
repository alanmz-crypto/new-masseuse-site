# Site op `peek-core-touch-public-20261109-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/core-upgrade/check-public.mjs` `https://staging39.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `14c294ea456f757b379d7ccdc3150f846afb5320` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42578241317 |
| started | 2026-11-09 07:16:18 UTC |

## check-public.mjs https://staging39.thaisolitude.com(S_TARGET=staging39): FAIL (exit 2)

```text
INVALID thai-massage: response 202; no verdict for this run
INVALID thai-massage: response 202; no rendered verdict
INVALID touch: INVALID thai-massage/390/right: response 202
INFO Peek clearance chromium: 0 passed, 0 failed, 0 captures, 1s; browser simulation does not establish physical-device or assistive-technology support.
INFO Peek native touch: 0 passed, 0 failed, 1s; simulated Chromium touch, not physical-device validation.
INVALID thai-massage: response 202; no rendered verdict
INFO Peek clearance firefox: 0 passed, 0 failed, 0 captures, 4s; browser simulation does not establish physical-device or assistive-technology support.
INFO public matched captures skipped because a required matrix failed or was invalid.
INFO public suite clearance-chromium: INVALID; child_exit=2; signal=none; effective_exit=2; 0 passed, 0 failed; stdout=/home/runner/work/_temp/ops-out/peek-public/clearance-chromium.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-public/clearance-chromium.stderr.log
INFO Peek clearance chromium: 0 passed, 0 failed, 0 captures, 1s; browser simulation does not establish physical-device or assistive-technology support.
INVALID thai-massage: response 202; no rendered verdict
INFO public suite clearance-firefox: INVALID; child_exit=2; signal=none; effective_exit=2; 0 passed, 0 failed; stdout=/home/runner/work/_temp/ops-out/peek-public/clearance-firefox.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-public/clearance-firefox.stderr.log
INFO Peek clearance firefox: 0 passed, 0 failed, 0 captures, 4s; browser simulation does not establish physical-device or assistive-technology support.
INVALID thai-massage: response 202; no rendered verdict
INFO public suite native-touch-chromium: INVALID; child_exit=2; signal=none; effective_exit=2; 0 passed, 0 failed; stdout=/home/runner/work/_temp/ops-out/peek-public/native-touch-chromium.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-public/native-touch-chromium.stderr.log
INFO Peek native touch: 0 passed, 0 failed, 1s; simulated Chromium touch, not physical-device validation.
INVALID touch: INVALID thai-massage/390/right: response 202
INFO public suite pages-chromium: INVALID; child_exit=2; signal=none; effective_exit=2; counts unavailable; stdout=/home/runner/work/_temp/ops-out/peek-public/pages-chromium.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-public/pages-chromium.stderr.log
INVALID thai-massage: response 202; no verdict for this run
INVALID pages-chromium: no final suite summary
INFO Peek core-upgrade public verification: INVALID, 4 suites, 5s; read-only staging39 browser checks; complete suite logs: /home/runner/work/_temp/ops-out/peek-public
```

**RESULT: INVALID** - check-public.mjs could not test (exit 2, see its output; usually the SiteGround bot challenge): nothing was tested, run it once more later

_finished 2026-11-09 07:17:03 UTC_
