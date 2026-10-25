---
name: corporate-rate-100
description: The corporate rate is $100 per service hour (Ryan, 2026-10-25), in the rate line AND the "What does it cost?" FAQ; staging38 applied, live prepared (only the FAQ needs the change); how it was found, exact rollback
metadata:
  type: project
---

Ryan, 2026-10-25: **"the price should be $100."** The brief's number was $130 per service hour and the corporate release installed it ([[corporate-group-massage]]). Comparing live with staging38 after the availability update ([[availability-update]]) showed live's rate line already said **$100** (edited on live after the release) while its "What does it cost?" FAQ still said **$130**, and staging38 said $130 in both places, so the live page contradicted itself. The availability update did not cause it (exact replacements only; none involve the price).

**What was done:** the corporate builder and its generated content now say $100. `scripts/work-with-me-employer/corporate/install-corporate-rate.php` makes two exact replacements, each on its own: rate line `<strong>$130</strong> per service hour` -> `$100`, FAQ answer `The proposed corporate rate is $130 per service hour.` -> `$100`. A replacement that is already done is "applied" and left alone, so on live only the FAQ changes; anything else (text missing, doubled, edited) stops the run. The FAQ's structured data is built by Spectra from the same answer, so it follows. `push-corporate-rate.sh` (`--apply | --check | --rollback FILE`) takes a DB export and a verified encrypted off-site copy first. Allow-list: localhost:8090, staging38, live.

**State:** staging38 APPLIED 2026-10-25 05:00 UTC (`rate-apply-38`; public check: $100 in both places, $130 nowhere). Rehearsal on a copy of staging38 (`rate-rehearse-1`) PASS; live dry run (`rate-live-dry-1`): rate line already applied, FAQ ready. **Live is prepared, not applied:** request `live-corporate-rate-1`; Ryan merges the pull request, then Actions > "Site ops (LIVE)" > Run workflow > main > confirm that id (type it exactly, no leading space: an earlier live attempt was refused for that).

**Rollback (exact, per what the apply changed):** staging38: `request.sh run --target staging38 --script corporate/push-corporate-rate.sh --args '--rollback $HOME/private-backups/corporate-rate-before-20261025-045952.json' --no-tests`. Live: the same with `--target live` and the snapshot name in the apply's report; on live it turns back only the FAQ (a replacement that was already applied before stays $100).

**Still open:** whether $100 covers table massage as well as chair massage, setup and pack-down, travel, equipment, a minimum, a deposit, cancellation and insurance (Ryan / Butsaba; nothing guessed).
