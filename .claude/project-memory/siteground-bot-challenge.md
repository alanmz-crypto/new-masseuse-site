---
name: siteground-bot-challenge
description: SiteGround answers a burst of requests from one IP with HTTP 202 + sg-captcha challenge on live AND staging; tools must treat it as INVALID, and test traffic must be rationed
metadata:
  type: project
---

On 2026-10-17 ~00:25 UTC, after a long evening of test traffic from this machine (post-change-test, csp-test browser runs of 16 page-views each, posture probes of ~50 paths per site), every public URL on thaisolitude.com AND staging31 started answering **HTTP 202 with `sg-captcha: challenge`** (a JS bot challenge page) to this IP. It is per-IP and temporary; real visitors are unaffected. Ryan's own browser on the same IP may get a "checking your browser" page for a while.

**Why:** the first posture-check run called the 202 pages "SERVED PUBLICLY" (any 2xx = exposed) and the verify-home / verify-client-pages gates also failed. A challenge response proves nothing about the site.

**How to apply:** (1) `posture/posture-check.py` now reports BLOCKED and exits 2 (INVALID) on a challenge, never pass/never exposure; any new HTTP checker should do the same (status 202 + `sg-captcha` header = unverifiable). (2) Ration public test traffic: don't run the full battery repeatedly; one request per ~5 minutes to see if the block lapsed, never hammer while challenged. (3) check-all.sh with a URL, post-change-test.sh and csp-test.mjs all hit the site and will fail while challenged - wait, then rerun. See [[staging31-backend-audit]].

**2026-10-19 (long block):** after about 100 test requests in one day (full audits on staging34, live checks) SiteGround answered HTTP 202 sg-captcha for this IP on EVERY thaisolitude.com host (live and staging) from roughly 15:30 UTC to at least 23:40 UTC (14 polls, one per 25 minutes, all still challenged). Server-side requests (curl run ON the server by the push scripts) are NOT challenged, so the push scripts' public checks and SSH tools keep working. Do not poll all day: make ONE `posture/edge-check.sh` request next session (real exit code 2 = challenged; the printed `exit $?` after a pipe is tail's code, not the tool's). Pace tests: whole-site audits and 20-view comparisons in the same hour as repeated live checks tripped it.

**2026-10-24 (GitHub Actions runners get challenged too):** the live Deep Tissue browser check was answered 202 twice from Actions runners (fresh IPs) and its report said FAIL, which reads like a broken live page. Fixed: the six page checkers (Thai, Therapeutic x2, Deep Tissue, research-slide, research-contrast) stop at the first 202 with exit 2, and `scripts/ci/run-op.sh` reports a read-only tool's exit 2 as **INVALID** (a write's exit 2 stays FAIL). A new checker should copy that one line after its `page.goto`.


**2026-11-01:** Bundle C on staging38: full public browser run41294845199 was
INVALID on the first Home request (zero assertions). A separate Contact CSP run
41294973273 passed desktop/phone in Chromium/Firefox one minute later. This is
intermittent; do not infer all visitors or all runner addresses are affected, or
that a later pass removes the need for a durable remedy. Server checks pass.
Ryan explicitly asked for help getting rid of the block. A supported staging38
exception is in scope; production/account-wide changes still need scope review.
Official SiteGround support route and a ready message:
`docs/inter-model/2026-11-01-siteground-antibot-support.md`. No authenticated
SiteGround support/admin session is connected. No message or host change made.
The WordPress Security Optimizer plugin does not control this host challenge.
