# Review Bundle F (staging38 only)

**Paused, not applied.** Read-only capture obtained six option rows/autoload,
URL overrides, core semantics and preservation digests, but /wp-admin/ HEAD
returns 403 from nginx. REST and the public login form return 200; no authentication
was performed. The capture is INVALID for admin verification, not a pass.

Ryan’s stop-for-access instruction applies. No installer or F write until an
authorized admin-verification route is supplied. [Proposal/evidence](../../../docs/reviews/2026-11-01-bundle-f-staging38.md).

`inspect-bundle-f.sh` is read-only. It captures only safe rows/URL overrides and
public core sources/unauthenticated responses, with cookies omitted. Never put
a production URL into staging; preserve menus, GUIDs, existing post discussion
fields, profiles, configuration and credentials. Live stays disabled.
