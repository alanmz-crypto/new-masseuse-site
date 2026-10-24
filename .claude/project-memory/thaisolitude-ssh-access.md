---
name: thaisolitude-ssh-access
description: How to reach the thaisolitude.com SiteGround account (staging25/26, live): passphrase key via ssh-agent socket, plain username, staging folder pattern, sg purge
metadata:
  type: reference
---
The thaisolitude SiteGround account is reached with `ssh -p 18765 <ssh-user>@ssh.thaisolitude.com` (plain `<ssh-user>...` username; host/port/user are in the gitignored `remote_execution.txt`, whose `User:` line wrongly includes `@thaisolitude.com`). The key `~/.ssh/thaisolitude` is **passphrase-protected**, so BatchMode logins only work through an ssh-agent that Ryan has loaded: `SSH_AUTH_SOCK=/tmp/claude-1000/tsag/a.sock` (Ryan starts it with `ssh-agent -a ...` + `ssh-add ~/.ssh/thaisolitude`, typing the passphrase himself; I cannot).

**Why:** on 2026-10-15 I wrongly told Ryan the key was "not authorized". A `grep ENCRYPTED` check cannot detect new-format OpenSSH key passphrases; `ssh-keygen -y -P '' -f key` does ("incorrect passphrase" means protected). `scripts/prose-pass/apply.py` already showed the working pattern (no `-i`, agent only).

**Confirmed working 2026-10-15:** after Ryan loaded the key into `/tmp/claude-1000/tsag/a.sock`, `push-staging26.sh` connected, found `~/www/staging26.thaisolitude.com/public_html`, and pushed. The account holds ~20 staging installs (staging3 to staging26) plus the live `thaisolitude.com`; WP-CLI works there with `wp --path=<dir> --user=1`; `wp sg purge` clears the SiteGround cache.

**How to apply:** staging sites live at `~/www/stagingNN.thaisolitude.com/public_html`; run `wp --user=1 ...` there and `wp sg purge` after writes. The account also holds the LIVE site, so guard every write on `wp option get home` equal to the intended staging URL. Tooling: `scripts/work-with-me-employer/push-staging26.sh` (dry run by default, `--apply`, `--rollback FILE`) + `install-staging.php`, rehearsed on a restored copy. See [[work-with-me-employer-update-local]], [[no-mu-plugins-rule]].
