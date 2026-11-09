# Site op `peek-titles-public-20261109-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/core-title-strip/check-public.mjs` `https://staging39.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `ec67609474bc676ac7f88546081e2cdb17ff89bc` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42601203134 |
| started | 2026-11-09 10:29:21 UTC |

## check-public.mjs https://staging39.thaisolitude.com(S_TARGET=staging39): FAIL (exit 2)

```text
INFO title inventory: 19 published pages; reviewed inventory; all routes included.
INFO title inventory: 19 published pages; reviewed inventory; all routes included.
INVALID title chromium: INVALID published-page inventory comparison: HTTP202
INVALID thai-massage: response 202; no verdict for this run
INVALID thai-massage: response 202; no rendered verdict
INFO Peek title strip chromium: 0 passed, 0 failed; 0 actual routes, 0 captures, 2s; browser simulation is not physical-device or assistive-technology validation.
INVALID touch: INVALID thai-massage/390/right: response 202
INFO Peek clearance chromium: 0 passed, 0 failed, 0 captures, 1s; browser simulation does not establish physical-device or assistive-technology support.
INFO Peek native touch: 0 passed, 0 failed, 2s; simulated Chromium touch, not physical-device validation.
INVALID title firefox: INVALID published-page inventory comparison: HTTP202
INFO Peek title strip firefox: 0 passed, 0 failed; 0 actual routes, 0 captures, 2s; browser simulation is not physical-device or assistive-technology validation.
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
INFO Peek native touch: 0 passed, 0 failed, 2s; simulated Chromium touch, not physical-device validation.
INVALID touch: INVALID thai-massage/390/right: response 202
INFO public suite pages-chromium: INVALID; child_exit=2; signal=none; effective_exit=2; counts unavailable; stdout=/home/runner/work/_temp/ops-out/peek-public/pages-chromium.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-public/pages-chromium.stderr.log
INVALID thai-massage: response 202; no verdict for this run
INVALID pages-chromium: no final suite summary
INFO Peek core-upgrade public verification: INVALID, 4 suites, 5s; read-only staging39 browser checks; complete suite logs: /home/runner/work/_temp/ops-out/peek-public
INFO Peek title-strip public final suite results:
INFO title-strip public wrapper retained-core: child_exit=2; signal=none; effective_exit=2; 0 passed, 0 failed across 3 reported suites; stdout=/home/runner/work/_temp/ops-out/peek-title-public/retained-core.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-title-public/retained-core.stderr.log
INFO public suite clearance-chromium: INVALID; child_exit=2; signal=none; effective_exit=2; 0 passed, 0 failed; stdout=/home/runner/work/_temp/ops-out/peek-public/clearance-chromium.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-public/clearance-chromium.stderr.log
INFO public suite clearance-firefox: INVALID; child_exit=2; signal=none; effective_exit=2; 0 passed, 0 failed; stdout=/home/runner/work/_temp/ops-out/peek-public/clearance-firefox.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-public/clearance-firefox.stderr.log
INFO public suite native-touch-chromium: INVALID; child_exit=2; signal=none; effective_exit=2; 0 passed, 0 failed; stdout=/home/runner/work/_temp/ops-out/peek-public/native-touch-chromium.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-public/native-touch-chromium.stderr.log
INFO public suite pages-chromium: INVALID; child_exit=2; signal=none; effective_exit=2; counts unavailable; stdout=/home/runner/work/_temp/ops-out/peek-public/pages-chromium.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-public/pages-chromium.stderr.log
INFO Peek clearance chromium: 0 passed, 0 failed, 0 captures, 1s; browser simulation does not establish physical-device or assistive-technology support.
INFO Peek native touch: 0 passed, 0 failed, 2s; simulated Chromium touch, not physical-device validation.
INFO Peek clearance firefox: 0 passed, 0 failed, 0 captures, 4s; browser simulation does not establish physical-device or assistive-technology support.
INFO Peek core-upgrade public verification: INVALID, 4 suites, 5s; read-only staging39 browser checks; complete suite logs: /home/runner/work/_temp/ops-out/peek-public
INVALID thai-massage: response 202; no verdict for this run
INVALID thai-massage: response 202; no rendered verdict
INVALID touch: INVALID thai-massage/390/right: response 202
INVALID thai-massage: response 202; no rendered verdict
INVALID thai-massage: response 202; no rendered verdict
INVALID thai-massage: response 202; no rendered verdict
INVALID touch: INVALID thai-massage/390/right: response 202
INVALID thai-massage: response 202; no verdict for this run
INVALID pages-chromium: no final suite summary
INVALID retained-core: final suite summary or nested numeric results missing
INFO title-strip public wrapper title-chromium: child_exit=2; signal=none; effective_exit=2; 0 passed, 0 failed across 1 reported suites; stdout=/home/runner/work/_temp/ops-out/peek-title-public/title-chromium.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-title-public/title-chromium.stderr.log
INFO Peek title strip chromium: 0 passed, 0 failed; 0 actual routes, 0 captures, 2s; browser simulation is not physical-device or assistive-technology validation.
INVALID title chromium: INVALID published-page inventory comparison: HTTP202
INFO title-strip public wrapper title-firefox: child_exit=2; signal=none; effective_exit=2; 0 passed, 0 failed across 1 reported suites; stdout=/home/runner/work/_temp/ops-out/peek-title-public/title-firefox.stdout.log; stderr=/home/runner/work/_temp/ops-out/peek-title-public/title-firefox.stderr.log
INFO Peek title strip firefox: 0 passed, 0 failed; 0 actual routes, 0 captures, 2s; browser simulation is not physical-device or assistive-technology validation.
INVALID title firefox: INVALID published-page inventory comparison: HTTP202
INFO Peek title-strip public verification: INVALID, 3 wrapper suites, 5s; read-only staging39; complete wrapper logs: /home/runner/work/_temp/ops-out/peek-title-public
```

**RESULT: INVALID** - check-public.mjs could not test (exit 2, see its output; usually the SiteGround bot challenge): nothing was tested, run it once more later

_finished 2026-11-09 10:30:14 UTC_
