# Site op `rollback-peek-nav-v8-staging39-20261108`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-peek-nav.sh` `--rollback $HOME/private-backups/peek-nav-before-20261108-064135-d72debe4.json` |
| lane | ops (site-ops.yml) |
| commit | `fd75d2275d870ac1e1a7b3344a02fbdc2623a22d` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42456647499 |
| started | 2026-11-08 06:55:06 UTC |

## push-peek-nav.sh --rollback $HOME/private-backups/peek-nav-before-20261108-064135-d72debe4.json(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: rollback
[1] Connecting and uploading the package to ~/wwm-package-peek-nav (outside every web root) ...
[2] Rolling back:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Rollback done: snippet #1357 is deleted; the page is back to how it was.
SiteGround cache purged
```

## post-change-test.sh staging39 (pages, structured data, 20 pixel comparisons, PHP log): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/post-change-test.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [minutes]
```

## prove-restore.sh staging39 (the export taken before the change restores; what moved since): exit 1 (information, see below)

```text
Usage: /home/runner/work/new-masseuse-site/new-masseuse-site/scripts/work-with-me-employer/prove-restore.sh <live|staging24|staging25|staging26|staging27|staging30|staging31|staging32|staging33|staging34|staging36|staging37|staging38> [export.sql]
```

**RESULT: PASS** - push-peek-nav.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-08 06:56:00 UTC_
