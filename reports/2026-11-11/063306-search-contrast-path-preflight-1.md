# Site op `search-contrast-path-preflight-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `search-contrast/push-search-contrast.sh`  |
| lane | ops (site-ops.yml) |
| commit | `3889be43cfa49ac366d6a437e84175c5d9ae0190` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42973176898 |
| started | 2026-11-11 06:32:55 UTC |

## push-search-contrast.sh (S_TARGET=staging39): FAIL (exit 1)

```text
Target site: staging39   component: search-contrast   mode: dry
staging39 is www/staging39.thaisolitude.com/public_html
SEARCH-PATH {"input":"/home/<ssh-user>/private-backups","realpath":"/home/customer/private-backups","webroot_realpath":"/home/customer/www/staging39.thaisolitude.com/public_html","input_is_link":false,"is_dir":true,"is_writable":true,"owner_uid":3732,"group_gid":3732,"mode":"0700","process_euid":3732}
Error: WWM_BACKUP_DIR must be a real writable private directory outside the web root. Stop and inspect before proceeding.
```

**RESULT: FAIL** - push-search-contrast.sh failed (exit 1)

_finished 2026-11-11 06:33:05 UTC_
