# Site op `corp-rollback-v1`

| | |
|---|---|
| operation | `run` on `staging37` |
| script | `corporate/push-corporate-massage.sh` `--rollback $HOME/private-backups/corporate-massage-before-20261024-221448.json` |
| lane | ops (site-ops.yml) |
| commit | `7c7996e41685f1c4cd9f46d5f41156248ba7e4ec` on `feat/2026-10-24-corporate-massage` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40142866220 |
| started | 2026-10-24 23:28:27 UTC |

## push-corporate-massage.sh --rollback $HOME/private-backups/corporate-massage-before-20261024-221448.json(S_TARGET=staging37): PASS

```text
Target site: staging37   mode: rollback
[1] Connecting and uploading the package to ~/wwm-package-corporate-massage (outside every web root) ...
[2] Rolling back:
staging37 is www/staging37.thaisolitude.com/public_html
Success: Rollback done: the page, snippet and menu item are deleted, the menu order is back, the Services row and the ts-corp block are out (Services content byte-identical to the snapshot; Additional CSS byte-identical).
SiteGround cache purged
```

**RESULT: PASS** - push-corporate-massage.sh succeeded

_finished 2026-10-24 23:28:45 UTC_
