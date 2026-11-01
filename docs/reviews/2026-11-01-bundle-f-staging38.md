# Bundle F: paused for staging38 admin access

**Status: NOT APPLIED.** Draft PR #67 contains read-only preparation only and is stacked on applied E (#66). No F installer or option write has run. Live remains unchanged. Ryan’s instruction is to stop and ask for access when required information is inaccessible; the admin verification is blocked, so no dependent F work will proceed until access is provided.

## Captured facts and exact proposed changes

| Option | Stored value now | Intended staging38 value |
| --- | --- | --- |
| siteurl | http://staging38.thaisolitude.com | https://staging38.thaisolitude.com |
| home | http://staging38.thaisolitude.com | https://staging38.thaisolitude.com |
| default_comment_status | open | closed |
| default_ping_status | open | closed |
| default_pingback_flag | 1 | 0 |
| show_avatars | 1 | 0 |

All six rows have raw autoload `on`. Neither WP_HOME nor WP_SITEURL is defined. Existing published discussion fields are already closed/closed (39 published records); these defaults affect future content, and no existing discussion row should be edited. There are two stored menu-URL rows; their bytes must remain unchanged. No GUID, user profile, config, credential, content or menu edit is proposed. HTTPS must use the staging38 host, never the apex production host.

## What is blocked

Read-only origin capture on 2026-11-01 12:27 UTC:

| Request | Result |
| --- | --- |
| GET /wp-json/ | 200, valid JSON |
| GET /wp-json/wp/v2/users | 200, valid JSON |
| HEAD /wp-admin/ | **403, server: nginx** |
| GET /wp-login.php | 200, verified public login form |

The login form is reachable, but this does not establish admin access. No authentication was attempted; no credentials or cookies were supplied, and response cookies were omitted. The overall capture is correctly **INVALID** for the required admin check. The 403’s cause has not been established, and it is not claimed to be the same as the intermittent Robot Challenge.

Report: `reports/2026-11-01/122715-review-f-capture-staging38-20261101-2.md` ([read-only run 41306536331](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41306536331)). Exact values, overrides, preservation digests, installed public core source and the verified public login form were captured. An earlier source-path guard rejected a core helper outside wp-includes; the corrected guard includes public wp-admin/includes and the source read succeeded. That inspection issue did not change the site.

## Required next access

Provide an authorized way to verify staging38’s /wp-admin/ before its URL options change. If hosting support is needed, Ryan sends the request; no message has been sent by the agent. The existing [AntiBot support draft](../inter-model/2026-11-01-siteground-antibot-support.md) can be supplemented with:

> Our read-only admin verification on staging38.thaisolitude.com receives HTTP 403 from nginx for HEAD /wp-admin/, including from the server-origin check. GET /wp-login.php and both public REST endpoints return 200. Please identify the restriction and provide an authorized way to verify staging admin access. Keep any change limited to staging38 and confirm the scope before a production/account-wide change.

Once access is restored or an authorized verification route is supplied: refresh source; implement exactly these six values with raw/autoload snapshots and stable-host rollback; preserve discussion/menu rows and server files; rehearse apply/reapply/exact rollback/reapply; dry run; apply only after fresh verified off-site backup; verify admin/login/REST and avatar removal. The localhost URL rows will need an explicit neutral scheme mapping because the rehearsal serves HTTP, while staging must actually become HTTPS. No F pass is claimed now.
