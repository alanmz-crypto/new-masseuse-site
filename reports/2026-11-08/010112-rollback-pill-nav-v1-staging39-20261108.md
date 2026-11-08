# Site op `rollback-pill-nav-v1-staging39-20261108`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-pill-nav.sh` `--rollback $HOME/private-backups/pill-nav-before-20261108-005626-17cd4ca5.json` |
| lane | ops (site-ops.yml) |
| commit | `ca4e6769d129b099a4a80bd7333ff170a51e60bf` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42432295360 |
| started | 2026-11-08 01:00:22 UTC |

## push-pill-nav.sh --rollback $HOME/private-backups/pill-nav-before-20261108-005626-17cd4ca5.json(S_TARGET=staging39): PASS

```text
Target site: staging39   mode: rollback
[1] Connecting and uploading the package to ~/wwm-package-pill-nav (outside every web root) ...
[2] Rolling back:
staging39 is www/staging39.thaisolitude.com/public_html
Success: Rollback done: snippet #1341 is deleted; the header and the link row are back to how they were.
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

**RESULT: PASS** - push-pill-nav.sh succeeded; read the two test sections: differences are expected only where the change is

_finished 2026-11-08 01:01:11 UTC_
