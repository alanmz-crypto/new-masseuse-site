# Site op `ww-menu-rollback-s40-1`

| | |
|---|---|
| operation | `run` on `staging40` |
| script | `winding-willow/push-winding-willow.sh` `--rollback $HOME/private-backups/winding-willow-before-20261120-023925.json` |
| lane | ops (site-ops.yml) |
| commit | `28a344eb8b62044453e5ebfee205c78f57fc22b3` on `feat/2026-11-20-winding-willow-menu` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/44665195718 |
| started | 2026-11-20 03:04:48 UTC |

## push-winding-willow.sh --rollback $HOME/private-backups/winding-willow-before-20261120-023925.json(S_TARGET=staging40): PASS

```text
Target site: staging40   mode: rollback
[1] Connecting and uploading the package to ~/wwm-package-winding-willow (outside every web root) ...
[2] Rolling back:
staging40 is www/staging40.thaisolitude.com/public_html
Success: Rollback done: the menu item is deleted and the menu order is back, the ts-ww CSS block is out (Additional CSS byte-identical to the snapshot), and page #1344 has its previous content back.
SiteGround cache purged
```

**RESULT: PASS** - push-winding-willow.sh succeeded

_finished 2026-11-20 03:05:08 UTC_
