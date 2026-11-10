# Site op `peek-rail-input-verify-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-rail-input.sh` `--verify $HOME/private-backups/rail-input-before-20261109-235652-0adc77b9.json` |
| lane | ops (site-ops.yml) |
| commit | `d8b545c2dc4001c1ad372b80ea8c497055455057` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42727758208 |
| started | 2026-11-10 00:22:21 UTC |

## push-rail-input.sh --verify $HOME/private-backups/rail-input-before-20261109-235652-0adc77b9.json(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: verify
[1] Uploading the reviewed package outside every web root ...
staging39 is www/staging39.thaisolitude.com/public_html
Private backup directory: configured=/home/<ssh-user>/private-backups canonical=/home/customer/private-backups
home=http://staging39.thaisolitude.com rail-input=applied page=1348 bytes=37750 content_sha256=eb50ba823a668423b0a3408093e707f5cdb59dec3794adcff89885f64a50e2fa full_state_sha256=22c3ad02fdb83208a9542ae33817c2d8960fcaf61ce5074969e02d459e3b48a6
Success: Exact applied Rail state verified read-only; full_state_sha256=22c3ad02fdb83208a9542ae33817c2d8960fcaf61ce5074969e02d459e3b48a6. Snapshot: /home/customer/private-backups/rail-input-before-20261109-235652-0adc77b9.json
public check: HTTP200, exact stored Rail script/style, five ideas, six sections, shared strip and licence present
home=http://staging39.thaisolitude.com rail-input=applied page=1348 bytes=37750 content_sha256=eb50ba823a668423b0a3408093e707f5cdb59dec3794adcff89885f64a50e2fa full_state_sha256=22c3ad02fdb83208a9542ae33817c2d8960fcaf61ce5074969e02d459e3b48a6
Success: Exact applied Rail state verified read-only; full_state_sha256=22c3ad02fdb83208a9542ae33817c2d8960fcaf61ce5074969e02d459e3b48a6. Snapshot: /home/customer/private-backups/rail-input-before-20261109-235652-0adc77b9.json
```

**RESULT: PASS** - push-rail-input.sh succeeded

_finished 2026-11-10 00:22:33 UTC_
