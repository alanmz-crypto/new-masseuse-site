---
name: winding-willow-page
description: 2026-11-18 Winding Willow page (/winding-willow/, staging39 #1385) captured exactly into git with installer + push script; rehearsed on a copy of live; staging40 + live pending; copy flags and Ryan's decisions; how SSH access was restored
metadata:
  type: project
---

**What:** "Massage at Winding Willow" (H1 "Massage with Sa at Winding Willow Acupuncture & Wellness"), Sa's page about her massage at Winding Willow Acupuncture & Wellness, 1540 SE Clinton St. Built on localhost/staging39 around 2026-11-12/13; by 2026-11-17 it existed ONLY on staging39 (page #1385, modified 2026-11-13 09:54:27 UTC; not in localhost's DB, not in git).

**Captured 2026-11-18** read-only (`winding-willow/capture-winding-willow.sh`, request `ww-capture-s39-1`, run 44247871249; the full JSON bundle is in that run's artifact): 30,373 bytes, sha256 `e21a940b...b60`, slug `winding-willow`, published, default template, `site-post-title` disabled, SureRank title "Massage at Winding Willow | Butsaba Dusrai, Oregon LMT" + a 140-character description. It has NO images, NO snippet, NO CSS of its own (the 328 "ts-ww" hits in the CSS are `ts-wwm`, the Work With Me rules) and NO links to it (menu, Services, Contact, About). Spectra regenerates its block CSS meta on first view.

**Tooling** `scripts/work-with-me-employer/winding-willow/`: `winding-willow-page.html` (exact bytes) + `winding-willow.json` (spec, block ids), `install-winding-willow.php` (preflight/apply/rollback; allow-list localhost:8090 + staging40, staging39 preflight only; mirrors the wording tiers with this page's research heading), `push-winding-willow.sh` (dry run, `--apply` with DB export + verified off-server copy, `--check`, `--rollback FILE`). Enable-live: `docs/inter-model/2026-11-18-winding-willow-enable-live.patch` (adds `thaisolitude.com`; NOT applied on the feature branch).

**Proof:** staging39 preflight `state=applied` (`ww-dry-s39-1`) = the package equals the reference exactly; rehearsal on a throwaway copy of LIVE PASS (`ww-rehearse-live-1`: apply, idempotent, exact rollback, re-apply, 10 pages 200, no new PHP errors, client-page gates pass; touches only the page + its SureRank row); read-only live dry run `state=ready` (`ww-live-dry-1`, from throwaway branch `ops/2026-11-18-winding-willow-live-dry`, never merge).

**On main** since PR #83 (merged 2026-11-18 07:01 UTC). **Open:** staging40 (Ryan creates it from live in Site Tools; tools already accept it), then the staging apply; then Ryan's live go. Copy flags and decisions are listed in [[open-items]] (section D): the "encouraging" framing and the "Massage Added to Other Care" panel can read as combined benefit, which the evidence audit (ButsabaJobScout Research, 2026-11-12) says to omit; Siriyong 2026 (PMID 42142427) found acupuncture added nothing to Thai massage.

**SSH access lesson (2026-11-18):** "Permission denied (publickey)" was NOT a revoked key: a probe with only the public key (`ssh -i thaisolitude.pub -o IdentityAgent=none`) showed "Server accepts key". The key is passphrase-protected and the agent socket `/tmp/claude-1000/tsag/a.sock` had vanished (no agent running). Fix: Ryan restarts the agent and loads the key himself; meanwhile the GitHub Actions lane (`scripts/ci/request.sh`, its own key) works from this computer too, since `git push` works. See [[thaisolitude-ssh-access]].
