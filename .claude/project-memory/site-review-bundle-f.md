---
name: site-review-bundle-f
description: Bundle F paused for staging38 admin verification access; no option writes, raw six-row proposal captured
metadata:
  type: project
---

PAUSED, NOT APPLIED. Draft PR #67, branch codex/2026-11-01-review-bundle-f,
stacked on applied E (#66). No F installer or option write. Live unchanged.
Ryan explicitly says stop/ask for access when required information is inaccessible.
F capture 2 (12:27 UTC) is INVALID: unauthenticated HEAD /wp-admin/ returns 403
from nginx. REST root/users and GET /wp-login.php return 200; login form verified,
no authentication/cookies supplied. Login reachability is not admin access.
No further dependent F work until authorized admin-verification access exists.

Exact raw proposal: siteurl/home HTTP staging38→HTTPS staging38;
default_comment_status/default_ping_status open→closed;
default_pingback_flag/show_avatars 1→0. All six autoload on. WP_HOME/WP_SITEURL
both undefined. Existing 39 published discussion rows already closed/closed;
two stored menu-URL rows must stay byte-identical. No content, existing-post
status, menu, GUID, profile, config, credential or server-file edit proposed.
Blog_public currently 1; preserve it and the hosting-controlled staging robots
behavior rather than guessing a new indexing setting outside this scope.

First read-only capture’s core-path guard rejected update_home_siteurl outside
wp-includes. Expanded only to public wp-admin/includes; source read now succeeds.
The remaining 403 is actual admin-verification access, cause unestablished.
Source archive: /workspace/review-f-source on this worker; report
reports/2026-11-01/122715-review-f-capture-staging38-20261101-2.md.
Options/core/preservation/login data captured; overall admin check is not a pass.

When access is restored: refresh source, use stable host identity for reversible
HTTP→HTTPS, preserve raw autoload and menu/discussion digests, rehearse exact
rollback before staging dry/apply with fresh verified backup. Keep production
host disabled. User owns every live go/run. G after November 14; Butsaba answers,
AntiBot support and static security.txt fallback remain separate.

Status/proof/access request: docs/reviews/2026-11-01-review-fix-status.md and
2026-11-01-bundle-f-staging38.md. No support message sent.
