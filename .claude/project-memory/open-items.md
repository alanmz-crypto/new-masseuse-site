---
name: open-items
description: START HERE. Every open item for thaisolitude.com as of 2026-10-25, sorted by what happens by itself, what Ryan does from a phone (with links), what needs a computer, decisions waiting on Ryan, what needs Butsaba, and how to restart from a phone cloud session
metadata:
  type: project
---

**Written 2026-10-25 (updated by pull request whenever an item changes). Ryan's computer is offline from about 2026-10-25 to 2026-11-08 and he works from his phone only.**
A phone cloud session (https://claude.ai/code, repo `alanmz-crypto/new-masseuse-site`) sees ONLY this repository: not his computer, not his global `~/.claude/CLAUDE.md`, not his local Claude memory, not convmem. Everything that must not be lost is therefore written here and in the notes this index points to.
First message to send in a fresh phone session: **"Read .claude/project-memory/open-items.md and tell me what is due."**

**2026-11-01 active review lane:** Bundle 0 is complete (Butsaba draft in
`docs/inter-model/2026-11-01-butsaba-questions.md`, fresh staging38 dump PASS).
Bundle A is **applied and verified on staging38**, PR #61, branch
`codex/2026-11-01-review-bundle-a`: rehearsal/rendered gates PASS, 262 browser
assertions PASS; exact rollback and expected pixels in [[site-review-bundle-a]].
No live write or request. The handoff's
recorded “fix all” approval supersedes older “nothing prepared until he picks”
status text below for the listed changes. Butsaba questions 1, 5, 8 and 9 are answered (record below); 2, 3, 4, 6 and 7
remain unanswered. Live is disabled, and Ryan still owns merges and live runs.
**Bundle B is applied on staging38 and PR #63 is merged** (confirmed 2026-11-01), PR #63 (`codex/2026-11-01-review-bundle-b`): uploads rehearsal2, dry run, apply, rendered gates/posture, restore import and strict 12-page semantic scan PASS. Ryan approved the exact default/Home brand card on 2026-11-01; attachment 1314's PNG 1200×630 SHA and Home OG/Twitter match. Home desktop/phone captures visually reviewed: no clipped text or side-scroll. Live disabled. See [[site-review-bundle-b]] and `docs/reviews/2026-11-01-bundle-b-staging38.md`. **Bundle C is applied on staging38**, draft PR #64: rehearsal2 (1,248 assertions/exact rollback), dry/apply, rendered gates/posture, restore import and Contact CSP in both browsers PASS. Final whole-site public browser attempt is INVALID at its first page (hosting robot challenge; zero assertions). Ryan asked for help removing that block; the verified support route and ready message are in `docs/inter-model/2026-11-01-siteground-antibot-support.md`. No SiteGround admin/support session is connected, and no host setting/message/live change was made. security.txt public delivery is still unverified; Ryan approved its mailbox. See [[site-review-bundle-c]].
After A, Bundle G must refresh 15 booking-date sentences (the original ten plus
five new closing blocks), including Services; still on/after November 14 only.

**Bundle D is applied on staging38**, draft PR #65 stacked on C: nine pages,
heart links and footer separator; rehearsal2 (1,376 browser assertions/exact
rollback), dry/apply, 53 origin checks, rendered Home/client/posture gates and
restore import PASS. Snapshot/rollback recorded in [[site-review-bundle-d]].
The runner captured 20 public screenshot views successfully, but the intermittent
host challenge has no durable fix and final interactive verification remains
pending. Live unchanged/disabled. E–F remain; G waits until November 14.

## A. Happens by itself (nothing to do)
- **BOOKING DATE MOVED to November 14, 2026 (Ryan 2026-10-30: "Change booking begins to Nov 14th. Everywhere."; [[booking-date]]). LIVE since 2026-10-30 15:43 UTC, so the strip flips on 2026-11-14 00:00 Pacific (08:00 UTC, PST), NOT Nov 1; until it is live the text below (Nov 1) is what live does.** ~~2026-11-01 00:00 Pacific (07:00 UTC) the announcement strip flips~~ (old date): from "Currently booking corporate & group massage only." to "Now accepting corporate & group massage bookings." on nine pages. It is PHP in a WPCode snippet; the SiteGround page cache is OFF (checked 2026-10-25), so no purge is needed. See [[availability-update]].
- **The one-time check routine still points at 2026-11-01 07:30 UTC and CANNOT be edited by an agent (created over the API): Ryan edits it at its link to 2026-11-14 08:30 UTC, and the dates in its prompt (flip time, ids `...20261114`, branch `docs/2026-11-14-strip-flip-verified`, `push-strip-flip.sh` already uses Nov 14).** Old text: 2026-11-01 07:30 UTC a one-time cloud routine checks it (read-only): https://claude.ai/code/routines/trig_01PH2Rj5wHuq7vLHx9RuPdcQ . It opens either a one-sentence notes pull request from branch `docs/2026-11-01-strip-flip-verified` (= all nine pages flipped), or, if old text is still served after a retry, a pull request that PREPARES a cache purge (`live-strip-flip-1`). It never merges and never starts a live run. It opens its PR with the GitHub tool, not `gh` (see section G); its final message in the routine log says which method worked, so if no PR shows up, the routine link above still has the result.
- **SEO fixes are LIVE (2026-10-30 21:14 UTC, `live-20261030-210629`; [[seo-fixes]]).** Public checks ok on live. Still open: the live browser post-change test was blocked by the bot challenge (INVALID), rerun once later; Ryan requests Search Console indexing (seven main pages first).

## B. Ryan, from the phone, with dates
1. **Now:** tap the one-tap Google Calendar link Claude gave in chat on 2026-10-25 (event Sunday 2026-11-01, 8:00-8:15 AM Chicago, for Ryan's Gmail). If it is lost, ask a cloud session to rebuild it.
2. **(moves to Saturday 2026-11-14 once booking-date is live; the Nov 1 calendar event should move too)** **Sunday 2026-11-01, 8:00 AM Chicago:** open https://github.com/alanmz-crypto/new-masseuse-site/pulls .
   - A new PR from `docs/2026-11-01-strip-flip-verified`: check the PR page shows the Gates check green, then Merge (notes only).
   - A PR that mentions `live-strip-flip-1`: (a) Merge it FIRST (the live workflow reads the request from `main`; a run started before the merge is refused and uses nothing up), (b) open https://github.com/alanmz-crypto/new-masseuse-site/actions/workflows/site-live.yml > Run workflow > branch `main` > type exactly `live-strip-flip-1` (no spaces) > Run.
   - No PR at all, or the banner at https://thaisolitude.com/about/ still says "Currently booking": open the routine link in section A (its final message has the result), or start a cloud session and say "check the strip".
3. **Handoff ready (2026-11-01, Ryan: "Prepare a handoff to have a new agent fix all"):** start a fresh cloud session and send it: "Read docs/inter-model/2026-11-01-site-review-fix-handoff.md and docs/reviews/2026-10-31-site-review.md, then start with Bundle 0 and Bundle A on staging38. Staging first, rehearse first, live only on my go. Give me the Butsaba message first." The handoff lists seven bundles (each one PR and one live run for Ryan), what stays his (deletions, credentials, settings screens) and what waits for Butsaba.
4. **Any time (optional):** delete finished branches at https://github.com/alanmz-crypto/new-masseuse-site/branches : `feat/2026-10-24-corporate-massage`, `chore/2026-10-25-home-card-live-checks`, `ops/2026-10-25-live-verify`, `ops/2026-10-25-fp-compare`, `ops/2026-10-25-copy-reads`, `ops/2026-10-25-strip-routine-test`. And delete the two used TEST routines ("TEST of the strip check routine" and "TEST: can a cloud session open pull requests?") at https://claude.ai/code/routines . (Claude never deletes branches or routines; that is Ryan's.)

## C. Needs Ryan's computer or a long sit-down (NOT during the two weeks)
- **BEFORE the computer goes offline: copy the off-site backup key into the password manager.** The encrypted DB exports and rollback snapshots in the draft releases `offsite-YYYY-MM` can only be read with the private age key, which exists ONLY on his computer (`~/.config/thai-solitude/offsite-backups.agekey`, one `AGE-SECRET-KEY-...` line). Do it in a separate terminal window (not inside Claude, which would write it into the chat): `cat ~/.config/thai-solitude/offsite-backups.agekey`, select the one line, paste it into the password manager. Never put it in the repo, a chat or a PR. Rolling back a change through GitHub does NOT need the key (it uses the snapshots on the SiteGround server); only reading the off-site copies does. See `CLOUD-SETUP.md`.
- **Credentials rotation (raise from 2026-11-07, once per session, briefly; Ryan deferred it on 2026-10-24):** salts, admin and DB passwords, 2FA, login hardening. Take a SiteGround MANUAL backup first (Site Tools > Backups). GitHub cannot do credential work; this stays with Ryan, best at a computer. Why it matters: old public backup zips once sat on live containing `wp-config.php` and an admin password hash ([[staging31-backend-audit]]).
- Files that exist ONLY on his computer and are untracked in git: `docs/inter-model/00_README_..._PACKAGE.md`, `01_..._Full_Build_Brief.md`, `02_..._Local_Agent_Handoff.md`, `ThaiSolitude_WorkWithMe_Availability_Plan.md`, `thai_solitude_corporate_massage_agent_handoff_prompt.md`, `images/`, `thai-solitude-snapshot*`, `docs/receptionist/`. They are the source briefs for the corporate and availability work. If he wants them safe, a cloud session can commit the text files on his word.

## D. Decisions waiting on Ryan (no deadline; nothing guessed)
- **Wording that goes stale after 2026-11-14 (date and pages corrected 2026-10-31):** the FAQ pairs on the Thai, Therapeutic, Deep Tissue and Chair pages ("...with bookings beginning November 14, 2026." and "Corporate and group massage bookings begin November 14, 2026."), the corporate hero line and the Work With Me "For clients" block: ten sentences that read as future once the strip says "Now accepting". Proposal: one installer run on or after the 14th swaps them to "since November 14, 2026" / "are open (since November 14, 2026)", staging38 first ([[site-review-2026-10-31]] item 3.7). Needs his OK (his rule: never reword established copy without asking).
- **Site review 2026-10-31 ([[site-review-2026-10-31]]; report `docs/reviews/2026-10-31-site-review.md`):** ten decisions for Ryan at the top of the report (closing call-to-action blocks on the five long pages; Chair <-> Corporate links and a corporate card on Contact; Home card buttons to their own pages; the "Relief" chip; WordPress Address http -> https; the Services availability sentence; four search titles plus Privacy/Accessibility rows; after Nov 14 the past-tense wording and the page cache; the Nov 7 credentials work and four deletions). The handoff records approval for its listed staging edits. Bundle 0 and A are complete on staging38, not live (PR #61); B is applied and verified on staging38 (PR #63 merged); C is applied, with public browser verification blocked by the host challenge (draft PR #64). D is applied (draft PR #65); E–F remain; G waits until November 14. The Butsaba draft now records Ryan's answers to 1, 5, 8 and 9; 2, 3, 4, 6 and 7 remain unanswered. The agent has sent no message.
- **"Mt. Tabor" to Portland (Ryan 2026-10-29: "We are in Portland and ready to start Thai Solitude for corporate and groups. Reconcile the website to that."; one item at a time):** item 1 SEO titles/descriptions of Home, Thai, Therapeutic, Deep Tissue = MERGED (PR #47), applied on staging38, NOT live ([[portland-meta]]); item 2 Contact Address line = MERGED (PR #48), applied on staging38, NOT live; Ryan: "Skip the map" ([[contact-address]]); item 3 Home licence line + Thai H1 + Deep Tissue H1 = on branch `feat/2026-10-29-onpage-portland`, staging38 first, NOT live ([[onpage-portland]]). STILL to do, one at a time: the map (left as is), About "From Chicago to Portland", "For Practices" rename, the "bookings begin November 1, 2026" wording after Nov 1. **LIVE since 2026-10-30 (run `live-portland-items-1-3`, all checks ok; see the three notes).**
- **About page section "From Chicago to Portland"** ("...started Thai Solitude in Chicago in 2024... Now I am bringing Thai Solitude to Portland..."): LEFT for now. It is Butsaba's own history; the availability plan says not to keep relocation wording. Offer: draft a Portland-only version for side-by-side review, apply nothing until he approves.
- **Home button "Ask about a session"**: left (an inquiry link; the Contact page opens with the corporate-only strip).
- **Rename "For Practices" to "Work With Me" in the menu** (NOT done; needs his OK); a corporate button on Contact (added on staging38 in Bundle A, not live); a permission-cleared chair-massage photo (the Services picture shows a table; his choice, flagged); five-hour capacity, table-massage room needs, start date, service area, which mailbox gets quotes (<butsaba-gmail> is used because the site already publishes it). [[corporate-group-massage]].
- **Chair Massage service page (2026-10-30; [[chair-massage-page]]):** LIVE since 2026-10-31 11:24 UTC (Ryan's own run `live-chair-massage-1`: page 1293, picture 1292, Services row, slug in snippets 1268/1232; all gates, restore proof and 73/73 browser checks pass on live; rollback snapshot `chair-massage-before-20261031-112429.json`, command in the note). Same change on staging38 (page 1302). Still his or Butsaba's: a Home card for chair massage (not added), a menu item (not added), the "10 to 20 minutes" and "six feet by six feet" figures in the copy, an Accessibility page line for the new research panels.
- **The price is settled:** $100 per service hour, no added fee exists anywhere ([[corporate-rate-100]]).
- Search Console "Request indexing" for `/thai-massage/` (his account, his click) if he wants Google to refresh the snippet.

## E. Butsaba answers and remaining questions

**Received from Ryan, 2026-11-01:** #1 “20”; #5 “60 and 90”; #8 yes; #9 yes.
The [question/answer record](../../docs/inter-model/2026-11-01-butsaba-questions.md)
preserves the exact reply: 20-minute chair length, 60/90-minute Therapeutic,
Deep Tissue and Thai options, call/text permission, and permission for both
Windy City Massage links and the reference offer. This is not a live go.
Do not infer mandatory/usual chair length or recalculate event capacity from
“20” alone. Website consistency work is still to be prepared and rehearsed.

**Still needed from that message:** #2 pregnancy; #3 table/mat, clothing and
draping; #4 Thai at events; #6 payment/deposit/cancellation; #7 event days/times.

- Confirm "No, you do not need to be flexible" (Thai FAQ). The old "Sessions take place in Mt. Tabor" and "I will reply to arrange a time" lines were REPLACED by the availability update, so those two no longer need confirming.
- Still unconfirmed: what the client lies on, clothing/draping, pregnancy and techniques; session lengths are now confirmed above ([[therapeutic-page-evidence]]).
- The three service pages' pictures show a client on a massage table while the FAQ says mat (Ryan said no new images).
- Client-privacy facts left out of the privacy policy ([[privacy-policy-page]]); the published (312) number is now approved for calls/texts; no replacement was requested.

## F. Older audit leftovers (low priority; verify before acting, they date from 2026-10-16 to 19)
- WPvivid is still ACTIVE on staging27/staging30 (deactivate or delete there); SiteGround backup-list glance; June form entry; Ryan's admin click-through on live wp-admin ([[staging31-backend-audit]]).

## G. Rules a cloud session must keep (full list in `CLAUDE.md` and `AGENTS.md`)
- **Live is Ryan's.** Never start "Site ops (LIVE)". Prepare the request, open the pull request, give him the two steps (merge first, then run with the typed id). Staging first; rehearse first; only ADD, never subtract or reword established copy without asking.
- **PR steward duty (Ryan's grant, 2026-10-25):** for every pull request Claude opens: say what it is in plain words, check that the Gates check is green and nothing conflicts, answer comments, give Ryan the merge steps, and keep the notes true after it merges. Claude never merges, force-pushes or pushes to `main`.
- Do not poke the live site with spoofed browser identities or bursts of requests (SiteGround answers 403 or the 202 challenge). Read from the server instead: `scripts/ci/request.sh run|inspect --target live ...` ([[siteground-bot-challenge]]).
- **In a cloud session `gh pr create` and `gh pr list` FAIL** (GitHub GraphQL is blocked, and `GH_TOKEN` reports invalid; tested 2026-10-26). `git push` works. Open pull requests with the GitHub tool `mcp__github__create_pull_request` (repo-scoped), or REST (`gh api repos/alanmz-crypto/new-masseuse-site/pulls -f ...`, or curl), and if none works push the branch and give Ryan the link `https://github.com/alanmz-crypto/new-masseuse-site/pull/new/<branch>`. Say which method worked.
- Routines created through the API attach ALL claude.ai connectors (Gmail, Drive, ...) by default; clear them straight away with `clear_mcp_connections: true`.
- Reply format Ryan asked for: plain language, the four lines "I finished / Next step / Next lane / See my work" (one click: a PR diff URL or one file) at the end of each finished phase, and a TL;DR.

<!-- ref: ./README.md — the notes index (this file is its first entry); ../../CLAUDE.md — how Ryan works and the cloud routine; ../../CLOUD-SETUP.md — the phone/cloud setup and the backup key -->
