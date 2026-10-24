# Thai Solitude - Codex Working Handoff

From: Crush. To: OpenAI Codex (working with Ryan).
Date: October 4, 2026.
Repo: `/home/lauer/WordPress/new-masseuse-site`
Site under edit for this citation pass: **staging19** (Ryan says to treat it as the same state as staging15).
Status: **Staging19 citations and current-sentence source fit verified October 5. The six sentences differ from Ryan's original wording; his decision on those revisions remains. The broader approved chore list is partly complete.**

## Current citation execution update (October 5, 2026)

This update takes precedence over older host and citation-work instructions below.

- Ryan's target is staging19. Do not edit localhost or production, and do not promote.
- The staging19 WordPress page output shows numbered, direct PubMed links in the Therapeutic Massage (page 1107) and Deep Tissue Massage (page 1091) research copy. The current qualified wording was checked against the sources and differs from Ryan's original text. Ryan's decision on keeping the revisions is open; see `Thai Solitude - Citation Worklist.md` for exact sentences and links.
- Codex's proposed A1 replacement sentence from chat was not approved or written. Do not use it as a substitute for Ryan's original wording.
- Thai Massage (page 1160) has no research statement requiring a citation, so no citation edit is due there.
- The mistaken Codex paragraph and bottom References block are absent from Services (page 299). Codex added them temporarily, then removed that material. The pre-cleanup database dump is /tmp/pre-citation-cleanup-staging19-20261005.sql.
- Ryan clarified that Butsaba has had clients, just not in Portland, and said to leave the relevant client-experience copy. Do not describe her as having no client history or imply Portland clients.
- Ryan supplied Oregon LMT-29905, issued September 28, 2026 and expiring March 31, 2028, and instructed us to take his word rather than retry the robot-blocked lookup. Public author display name/nickname/slug are now Butsaba; login details are out of scope. REST user enumeration, the PDF-specific noindex header, metadata cleanup, duplicated service imagery, and several approved copy/links edits remain. Contact's public route returned HTTP 403. See the top of `Thai Solitude Fix Checklist.md` for the verified accounting.
- Ryan's direction is to restore his research claims and annotate his claims with numbered markers linking directly to outside sources, without a bottom reference list. Sol's current citations should be checked against the original wording in the research files. If a source does not support the original wording, show Ryan the exact proposed wording and placement and wait for his approval before another site write.
- Codex has not restored A1-A6 or applied the current citations on the Therapeutic or Deep Tissue pages. Its only citation-related site write was the temporary Services-page addition described below, which it then removed. Any further staging write requires Ryan's one-change-at-a-time approval and a fresh database dump.

## Read this first

Six documents describe this work. They are split by role so you read one, not six.

| # | File | Read it for |
|---|------|-------------|
| 1 | `Thai Solitude - Decisions Register.md` | **Ryan's decisions**, status table, do-now list. **Start here** |
| 2 | `Thai Solitude - Compliance Register.md` | The rules, the findings, and the corrections to earlier documents |
| 3 | `Thai Solitude - Evidence Register.md` | Source index; which citation bundle is current |
| 4 | `README-Citation-Bundles.md` | The five citation bundles supersede each other; this says which is current |
| 5 | `Thai Solitude - Citation Worklist.md` | Claim-by-claim citation verdicts |
| 6 | `Thai Solitude Fix Checklist.md` | The original page-by-page audit. Historical |

**Runtime checkout is on `main` with substantial pre-existing dirty and untracked work.**
Make **no commits and no pushes**. Never use broad `git add` here. Git is not the
complete-site backup.

## Handoff chain

Current Sol handoff: CODEX-TO-SOL-2026-10-05-citation-followthrough.md.

This work was carried by three agents on 2026-10-04. Their handoffs are in `inter-model/`:

- `CODEX-TO-CLAUDE-2026-10-04-staging14-home-return.md` - **the most recent deployment.**
  Read this before touching staging. Home is deployed to `staging14` only, and its CSS
  reaches the page through a guarded MU-plugin override, not the normal page content.
- `CLAUDE-FABEL-2026-10-04-white-flash-handoff.md` - the Dark Reader flash work.
- `CODEX-ASTRA-2026-10-04-f5-white-flash-handoff.md` - the earlier flash pass.

## The blocking warning: staging14 Home

**Read this twice.** `staging14.thaisolitude.com` Home is served through an MU plugin at:

```
~/www/staging14.thaisolitude.com/public_html/wp-content/mu-plugins/ts-staging14-home-only.php
~/www/staging14.thaisolitude.com/public_html/wp-content/mu-plugins/ts-staging14-home-only.css
```

The loader runs only when all of these are true: front page, queried page ID 296, Astra
theme, and exact home URL `https://staging14.thaisolitude.com`. On that request it supplies
the approved CSS, the reload script, and the footer copyright line.

**Consequences you must not discover by accident:**

- **Editing staging14's shared Additional CSS will not change Home.** Update the scoped CSS
  file deliberately.
- **Editing page 296's `post_content` may not reach the rendered Home** while the override
  supplies footer text.
- **Do not copy the loader to production** or loosen its guards without a separately
  authorised release.
- **Do not rerun `scripts/restyle/install-home-staging14.php`** to "finish" that deployment.
  Its absent-file and hash guards should refuse a repeat; that is correct behaviour.

Source: `CODEX-TO-CLAUDE-2026-10-04-staging14-home-return.md`.

## Ryan's operating rules - these are binding

Ryan stated these for this work. They are not defaults you may relax.

1. **Work goes directly to staging**, not the local Docker stack, for these edits.
   He judged the work straightforward.
2. **Do not start until Ryan says "go."** This handoff is preparation, not authorisation.
3. **A standing request to use staging does not authorise a particular live deploy.** Live
   production, `thaisolitude.com`, and SiteGround stage-to-live are **out of scope**.
4. **Remove nothing without asking Ryan twice.** No deletion of content, media, files,
   plugins or rows, and no destructive `search-replace`, `DROP` or `DELETE`, without two
   confirmations. This includes anything that *reduces* what is on the site.
5. **Take a backup if any protocol calls for one.** Otherwise treat Ryan's existing backups
   as current and do not ask.
6. **Never commit credentials**, and never print private keys or passwords.

**Open question already put to Ryan and not yet answered:** whether the pre-approved removal
list (item 3 below) counts as *one* approval or needs confirmation *per change*. Ask before
acting on it.

## The work, in order

### 1. Verify LMT-29905 - do first, it gates the footer

The number is **already live on Work With Me and on the resume**, so the "nothing publishes
before verification" rule is already broken.

- Licence lookup: `https://public.orlicensing.oregon.gov/OBMTPublic/LPRBrowser.aspx`
- Discipline lookup: `https://public.orlicensing.oregon.gov/OBMTDiscipline/LPRBrowser.aspx`
- **Check the expiry date**, not just that it is active.
- Save a screenshot.

**Unverified by any agent so far.** Ryan says the licence is known; no agent has looked it up.

### 2. Fix the author email leak - P0, independent of everything else

The WordPress author display name is `<admin-login-email>`, exposed via
`/wp-json/wp/v2/users` and `/author/<admin-login-slug>/`.

- Change display name and nickname; change the author slug; disable author archives; block
  user enumeration.
- **Check whether the login username equals the exposed email.** That is a separate problem.
- Re-fetch the REST API and the old author URL to confirm the address is gone.

### 3. Ship the edits that need no new information

These are decided. **They are removals - see rule 4.**

| # | Edit | Note |
|---|------|------|
| a | Remove "Registered" | OAR 334-010-0025(6) reserves "LMT" |
| b | Remove "studio" and the mobile/hotel lines | There is no room yet; clients will come to her |
| c | Review outcome and "pain relief" claims | Ryan clarified Butsaba has had clients, just not in Portland. Do not use a no-client assumption to remove client-experience copy; check its location and accuracy. No testimonial approval is implied. |
| d | Remove the (224) 275-1887 number | Correct number is 312-539-4328 |
| e | Add the licence footer | `Butsaba Dusrai, LMT - Oregon License LMT-29905` |

### 4. Noindex the resume PDF

An `X-Robots-Tag: noindex` header, or a robots rule, whichever the server allows. It
contains a phone number and an email address.

### 5. Extend `scripts/credibility-pass/verify-local.php`

Turn the manual QA pass into one query. Assert on **every page**:

1. the licence string is **present**
2. the **banned terms are absent**
3. there is **exactly one phone number**
4. `ts-coming-soon` appears on **all 8 pages**

Run it local, then staging, then live. This also makes banner removal at launch a
one-query check rather than a page-by-page walk.

### 6. The copy pass (historical; current citation update above takes precedence)

The steps below are the October 4 plan. Do not follow their A1-A6 deletion or
rewriting directions over Ryan's October 5 instruction to restore and cite his
claims. Any further change still needs Ryan's exact per-change approval.

Ordered by what the answers allow:

1. **Reword the therapeutic/medical claims** to the evidence-matched versions in
   `Thai Solitude - Citation Worklist.md` section 5, then cite.
2. **Delete the Group B items regardless** - the cancer/neurological sentence, the headache
   FAQ promise, the "Many of Butsaba's clients seek..." line, and any remaining uncited
   "may help". A citation does not make these acceptable.
3. **Term search-and-replace** to first person "I", including the checklist's term list.
4. **Location copy** via the decisions register.

## Things that will mislead you

An agent that reads the older documents first gets wrong answers. These are already
corrected in the registers, but the old files still say them.

| Wrong | Right | Where it is corrected |
|-------|-------|----------------------|
| OAR 334-010-0031 governs outcall | **That rule does not exist.** Outcall is OAR 334-020-0005(2) | Compliance Register, corrections |
| A sole LMT is not exempt from a facility permit | **Exempt.** ORS 676.160(6) lists OBMT as a health professional regulatory board, so OAR 334-020-0005(1)(C)(i) applies | Compliance Register, corrections |
| A2 and A3 are unrecoverable | **Recovered** from `thaisolitude.com/wp-json/wp/v2/pages/1107` | README-Citation-Bundles, Decision Register |
| "Long-term self-care" has no therapist-delivered evidence | **Wrong.** Perlman 2019 is a 52-week therapist-delivered maintenance trial | Citation Worklist section 5 |
| Bervoets proves massage fails for back/neck pain | It is a **constraint, not a contradiction** | Citation Worklist section 5 |
| The checklist's "no stale location terms" finding | Describes the **Willowy Hollow** site, not this one | Compliance Register, corrections |
| This is Butsaba's only site | It is **one of two**; the other shows `Sa Rai`, Chicago | Decision Register, Q-C |
| "Chicago past tense" | **Premature until Oct 27, 2026.** Still current today | Decision Register, 1.1 |
| "Based in Mt. Tabor" | **Premature until arrival.** Use "Relocating to Portland (Mt. Tabor area)" | Decision Register, 1.2 |

## Historical open questions from October 4

The host, removal approval granularity, and A2/A3 direction below were superseded
by Ryan's October 5 instructions at the top of this handoff. Treat other open
questions as historical unless the current Decisions Register still lists them.

Do not proceed past these without asking.

| Item | Blocked on |
|------|-----------|
| **Which staging host** | Ryan. `staging14` Home is behind the MU-plugin override; `staging12` was named earlier; `staging4`/`staging5` also exist |
| Whether the removal list is one approval or per-change | Ryan |
| Session mechanics (table or mat, clothing, draping) | Butsaba |
| Facility permit for a rented room | Board question, drafted in the Decision Register |
| Email address and Instagram handle | Butsaba |
| Pricing | Ryan is double-checking |
| Still Light hours and Thailand study | Butsaba |
| Whether A2/A3 stay at all, given the scope issue | Ryan. Their citability is settled; their positioning is not |
| The self-care phrase: research framing, practice guidance, or cut | Ryan |

## Verification habits

- **Audit the rendered page, not the editor.** Title tags, meta descriptions, OG/Twitter
  fields and `view-source` are where the banned terms hide.
- **Take a DB dump before any bulk change** if a protocol calls for it.
- **`search-replace` needs `--precise --dry-run` first.** Page content is serialized
  Spectra/UAGB block JSON; naive replacement corrupts it.
- **Re-fetch the REST API after security fixes** to confirm the change landed.
- **Report, do not decide silently,** when a document contradicts another.

## What this handoff does not authorise

- Any live production or SiteGround stage-to-live operation.
- Any removal, beyond the pre-approved list, without asking twice.
- Rerunning any deployment script that already succeeded.
- Copying the staging14 MU plugin anywhere.
- Commits or pushes.

## Historical starting checklist (superseded for this citation pass)

Use the current citation execution update at the top of this handoff and the
Sol-directed handoff instead of the host-selection steps below.

1. Read `Thai Solitude - Decisions Register.md` status table.
2. Read `CODEX-TO-CLAUDE-2026-10-04-staging14-home-return.md` if the work touches Home.
3. Ask Ryan which staging host, and whether the removal list is pre-approved.
4. Wait for "go."

## Sources

- `Source - Thai Solitude - Decisions Register.md:` Ryan's decisions, 2026-10-04.
- `Source - Thai Solitude - Compliance Register.md:` the rules and findings.
- `Source - CODEX-TO-CLAUDE-2026-10-04-staging14-home-return.md:` the staging14 deployment,
  the MU-plugin override, and the rollback procedure.
- `Source - CLAUDE-FABEL-2026-10-04-white-flash-handoff.md:` the Dark Reader flash evidence.
