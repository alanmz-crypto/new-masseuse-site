# Handoff: fix everything in the 2026-10-31 site review

**For:** the next agent (a cloud session on claude.ai/code or the Claude app; Ryan is on his phone until about 2026-11-08).
**From:** the session that wrote `docs/reviews/2026-10-31-site-review.md` (Claude, 2026-10-31/11-01).
**Authorization:** Ryan, 2026-11-01: "Prepare a handoff to have a new agent fix all." Read that as: every item in the review is approved for the
staging38 lane, including the ones marked "edit of established copy", because he has now seen the exact before/after wording in the report.
It does NOT change his standing rules: staging38 first, rehearsed first, exact rollback for everything, live only through his own
"Site ops (LIVE)" run after he merges, never delete anything, never touch credentials, and never invent a fact. Where the review's proposal
offered two options, pick the one it lists first unless this file says otherwise.

## 1. Read these first, in this order

1. `CLAUDE.md` and `AGENTS.md` (the rules, the OBMT word tiers, the tooling list, the "no MU-plugins" rule, the cloud routine).
2. `.claude/project-memory/open-items.md`, then `site-review-2026-10-31.md`, `add-only-never-subtract.md`, `stop-before-hard-to-reverse-changes.md`,
   `target-content-may-be-newer.md`, `siteground-bot-challenge.md`, `chair-massage-page.md` (installer lessons), `seo-fixes.md` (SureRank rows and
   the on-page insertion), `contact-address.md` / `onpage-portland.md` (exact-replacement pattern), `accessibility-page.md` (CSS block and snippet
   patterns), `booking-date.md` (date-aware copy), `banned-words-research-exception.md`.
3. `docs/reviews/2026-10-31-site-review.md`: the whole thing. Item numbers below refer to its sections. If it has a section 12 (the four lenses
   that were still running when this handoff was written: research integrity, outbound links, visual pass, storage and CSS), those items are in
   scope too, with the same rules.
4. `ops/README.md` and `scripts/ci/request.sh` (how a cloud session asks GitHub Actions for server work).

## 2. Facts about the environment you will be in

- No SSH, no localhost stack, and the network policy denies thaisolitude.com, so you cannot curl or WebFetch the site. Read it from the server:
  `scripts/ci/request.sh run --target staging38 --script seo-scan/dump-site.sh` (every page as served plus stored content, SureRank rows,
  snippets, Additional CSS; decode line in the script header) or `request.sh inspect --target staging38 --query page --arg <slug>` for one page's
  exact stored bytes. Screenshots: `request.sh run --target staging38 --script tools/shots-inline.mjs --args "https://staging38.thaisolitude.com"`.
  Write every installer against the STORED bytes of staging38 (they are the same as live since the chair change; verify with a fingerprint or
  the dump before you rely on it).
- Ids differ between live and staging38 for anything created by an installer (chair page: live 1293, staging38 1302; chair picture: live 1292,
  staging38 1301). Look pages up by slug and snippets by title, never by id. Live snippet ids: 1268 CorporateBand (the strip), 1232
  A11yPageJumpLinks, 1234 A11yHeartLinks, 1237 ThemeToggle, 1036 HeaderSecurityProduction, 1023 LocalBusinessInfo, 1225/1226 performance, 1256
  PersonalAutomationUnlistedPages. Additional CSS is the `custom_css` post of the astra theme (live id 29).
- Each request id runs once; a report that says INVALID proves nothing (SiteGround's bot challenge). Ration browser checks: one run per page
  change, never loops. A write with `--apply` takes a DB export and an encrypted off-site copy first, automatically.
- Usage limits: the account's 5-hour window ran out twice during the review when twelve agents read 200 KB pages in parallel, and the Workflow
  tool runs only two agents at a time on this VM. Do the installer work yourself in the main session; use at most two or three helper agents,
  pointed at small derived files, for verification.
- `gh pr create` fails in a cloud session; open pull requests with the GitHub tool `mcp__github__create_pull_request`, or push and give Ryan the
  `pull/new/<branch>` link. Check the Gates check is green on every PR (scripts/check-all.sh runs offline on every push).

## 3. How each change is built (the house pattern)

Every change is an installer + push script pair under `scripts/work-with-me-employer/<area>/`, copied from the nearest existing example:

| Kind of change | Copy this | Notes |
|---|---|---|
| Replace an exact string in page content | `contact-address/`, `onpage-portland/` | `$old` must occur exactly once; snapshot the page; rollback restores the exact bytes; refuse if the page changed |
| Insert a new block at an exact anchor | `seo-fixes/install-seo-fixes.php` part 3, `chair-massage/` (Services row) | anchor on a block comment or an id; relative image URLs; no attachment ids in blocks (re-apply must be byte-identical) |
| SureRank title/description/social rows | `portland-meta/`, `seo-fixes/` | grouped keys `surerank_settings_general` / `_social`; `facebook_image_url` + `_id`; use the plugin's saver |
| Edit a WPCode snippet | `chair-massage/install-chair-massage.php` (snippet parts) | sha256 of the frozen code before the edit; syntax-check the new code in memory; `wp_update_post(wp_slash())` |
| New WPCode snippet | `accessibility/install-jump-links.php`, `theme/install-theme-toggle.php` | title unique; auto-insert; exact rollback deletes it |
| Additional CSS block | `accessibility/install-focus-css.php`, `theme/install-header-fit.php` | marked START/END comments; append only; rollback removes the block byte for byte |
| A WordPress option | `structured-data/`, `headers/` (options via installer) | snapshot the old value; refuse if it differs at rollback |
| Media | `chair-massage/` (import once by sha256, serve by relative URL) | no second copy of an existing file |

Rules inside every installer: allow-list `localhost:8090` + `staging38` only until the live-enable commit (then add `thaisolitude.com`, as the
`docs/inter-model/*-enable-live.patch` files did); `--apply` is the only write; `preflight` prints ready/applied/CHANGED per part; all-or-nothing;
a snapshot file under `$HOME/private-backups/` with the exact previous bytes; `--rollback FILE` refuses unless every part is still exactly as applied.
Routine per bundle: build; `scripts/check-all.sh`; commit; `request.sh rehearse --installer <dir>/install-<name>.php` (add `--with-uploads` if it
touches media); `request.sh run --target staging38 --script <dir>/push-<name>.sh` (dry run); the same with `--args "--apply"` (tests run
automatically; additive blocks WILL change the pixel comparison on the pages they touch, say so); a browser check of the changed pages
(`tools/shots-inline.mjs` or a small checker modelled on `chair-massage/check-chair-massage.mjs`, `inline` flag); update the gates
(`verify-client-pages.py`, `home/verify-home.py`, `availability/check-availability-update.mjs`, the builders' content files) so `check-all.sh`
passes against staging38; PR with the before/after of every string; after Ryan's "go": the live-enable commit, a live dry run, `request.sh live
--script ... --args "--apply"`, PR, and the two steps for him (merge; Actions > "Site ops (LIVE)" > Run workflow on main > type the id).
After it is live: the notes (`.claude/project-memory/<area>.md`, the index, open-items) in the same PR as the live record, as the existing notes do.

## 4. The work, in bundles and in this order

Keep the number of live runs small: one installer per bundle, each bundle one PR. Ryan merges and runs one id per bundle.

**Bundle 0, no site writes (do first):**
- Draft one message for Butsaba (Ryan sends it), covering review items 3.1 chair session length (10 to 15 or up to 20 minutes), 3.2 pregnancy at
  events, 3.3 table/mat/clothing/draping per service, 3.4 Thai massage on a mat at events yes/no, 3.5 session lengths for Therapeutic and Deep
  Tissue, 8.3 how to pay / deposit or cancellation / days available, 3.14 "phone or text", and section 12: OK to keep the two Windy City Massage links
  and offer them as a reference. Put the draft in `docs/inter-model/` and in chat.
  Items that need her answer stay out of every bundle until it arrives (never guess; the review says which sentence changes for each answer).
- Run `dump-site.sh` on staging38 and keep the decoded tree in your scratchpad: every `$old` string comes from it.

**Bundle A, the booking path and internal links (pages Home 296, Services 299, About 298, Contact 301, Thai 1160, Therapeutic 1107, Deep Tissue
1091, Chair by slug, Corporate 1266; snippet A11yPageJumpLinks):**
1.1 closing "Next step" block on the four service pages and Services (exact wording and buttons in the review); 1.2 (a) and (b); 1.3 the Contact
corporate card plus the jump-link plan entry; 1.4 move the Chair and Corporate rows above Therapeutic and change the Services jump plan (Ryan's
"fix all" covers the reorder); 1.5 the corporate mobile sticky bar (copy the Work With Me `.ts-wwm__sticky` rules to `.ts-corp__sticky`);
2.1 the Home card buttons (href and label per the review; root-relative about/ and contact/); 2.2 (b) the "Other services:" line on the four
service pages (the footer services menu is 2.2 (a): do it as a footer WIDGET block addition only if the footer layout can be measured
unchanged at 30 widths, as the corporate menu work did; otherwise leave it and say so); 8.1 the About next-step block; 3.6 the Services
availability sentence. Gates to update: `home/verify-home.py` (new hrefs and labels), `verify-client-pages.py` (new blocks), `check-availability-update.mjs`.

**Bundle B, search and sharing (SureRank rows, snippet LocalBusinessInfo, one option):**
4.1 the four titles; 4.2 Privacy and Accessibility titles and descriptions; 4.3 Services description; 4.4 snippet 1023: add image, logo, email,
description, areaServed Portland, founder `#person`, drop `postalCode`; set the Tagline option to "Thai, Therapeutic and Deep Tissue Massage with
Butsaba Dusrai, LMT"; 5.2 corporate `facebook_image_url` to https; 3.9 the corporate description "with table massage by arrangement at suitable
host venues." SureRank's global Person entity (4.4) is a settings screen: prepare the option value if you can read the key with
`seo-scan/plugin-keys.sh`, otherwise give Ryan the tap-by-tap steps. 4.5 share image: build a 1200 x 630 brand card from the existing logo and
the text "Thai Solitude · Massage in Portland, OR" (no photo, no new illustration), show it to Ryan in the PR, and set it as SureRank's default
share image and Home's only after he says yes. Update `scan-seo.sh` expectations and `verify-client-pages.py` for the new titles.

**Bundle C, markup, accessibility and small snippets (no copy changes):**
6.1 heading tags (Spectra Advanced Heading `tag` p to h3 for the 16 step/row blocks, the corporate `<p class="ts-corp__card-title">` to h3, one
screen-reader-only H2 above the Services rows; one CSS rule so sizes stay identical: measure before/after); 6.2 the Therapeutic "Questions About
Therapeutic Massage" heading; 6.3 focus-ring colour (`--ts-accent-deep` in the five outline rules); 6.4 the FAQ role script (new snippet); 6.5
strip `role="region" aria-label="Booking notice"` (edit snippet CorporateBand), map as a Custom HTML iframe with `title="Map of Portland, Oregon"`
and the SAME query (the map stays on Mt. Tabor: Ryan's decision), phone link underline on Contact, résumé buttons' aria-label, scroll-to-top
role and footer labels (one footer script), swap the FAQ chevron attributes; 8.4 the two phone CSS rules (Privacy/Accessibility H1 spacing,
Home hearts row gap) and the shorter strip on phones; 4.6 the 404 links snippet, the search-results tidy (Astra blog meta option, read-more
filter); 5.5 the head cleanup snippet (RSD, oEmbed discovery, shortlink) and `security.txt` (ask Ryan which mailbox; the file is a static file in
the web root, so it is his to upload unless a snippet serves `/.well-known/security.txt` through a rewrite). Also the Accessibility statement v5
(page 1230): "and Chair Massage" in both sentences, the date, and a line naming the new panels, built with the `accessibility/` v3 pattern.

**Bundle D, wording (page content, exact replacements):**
3.10 "Relief" to "Ease" (block label + span, and the key in snippet A11yHeartLinks; add `wording_tiers.BAD` to `verify-home.py`); 3.8 the two
Work With Me lines; 3.11 the Home paragraph and the About "From Chicago to Portland" section (the sentence in the review; show the side by side
in the PR); 3.14: "What Is the Chair Like?", "I do not offer individual house calls." becomes "I do not offer individual home visits." on the four
FAQs, the FAQ pointer "The Corporate & Group Massage page" on the four pages, "agree on a realistic schedule", "setup and packing-up timing",
heading "Nurses, Hospital Staff and Caregivers", the Deep Tissue duplicate health notice (keep the first, drop the repeat), Accessibility
"contact me at", the footer licence line middot (astra-settings footer HTML), About "I also offer therapeutic, deep tissue and chair massage.";
review section 12: the five research-fidelity edits (the 2003 review sentence on three pages, the 2024 qualifier on two, the Cochrane low-back
phrase on two, the four study styles on Therapeutic, the two Cochrane citations in one form with full author lists) and the AHA href on Work
With Me (leave the shortened citation title as it is). Do NOT touch serial commas, Title Case or number styles site-wide (3.14's last sentences): too much churn for the
value; list them as declined in the PR. For "bi-weekly" read the cited abstract first and write what it says. The Chair page is built by
`chair-massage/build-chair-massage.py`: change the builder and its content file in the same commit as the installer so the "content matches its
builder" gate stays green; the exact-state check in `install-chair-massage.php` will no longer match the live page after this bundle, so its
rollback path is retired (say so in the chair note).

**Bundle E, performance (media and attributes):**
7.2 the 616 KB PNG as a 400 px WebP (server-side `wp_get_image_editor`, import once, rewrite the block url/urlTablet/urlMobile); 7.3 point the
Home cards at the pages' own attachments (by URL, no ids); 7.4 width/height on the corporate illustration on Home and Services and the three
Work With Me images; the font preload snippet (two `<link rel=preload>` lines at `wp_head` priority 1); the emoji removal snippet. Leave the
italic font and the inline CSS alone (declined / no safe fix).

**Bundle F, WordPress options (one installer, options table):**
5.1 `siteurl` and `home` to `https://thaisolitude.com` (snapshot both; staging38 first, confirm wp-admin still loads there; the menu items keep
their stored http links, which WordPress rewrites on output, so leave them); 5.5 Discussion defaults (`default_comment_status` closed,
`default_ping_status` closed, pingback flag off), `show_avatars` 0. Re-run the dump afterwards and confirm `/wp-json/` shows https and
`/wp-json/wp/v2/users` has no `avatar_urls`.

**Bundle G, after 2026-11-14 only:**
3.7 the past-tense installer for the ten "bookings begin November 14, 2026" sentences (pages Thai, Therapeutic, Deep Tissue, Chair, Corporate,
Work With Me); build and rehearse it any time, apply on staging38 and ask Ryan to run live only on or after the 14th. Then ask Ryan to turn the
SiteGround Dynamic Cache on (7.1) and re-run the dump to confirm `x-cache-enabled: True`; from then on a date change needs a purge (`wp sg purge`
in the push script).

**Ryan only (list them in the final PR, tap by tap, do not do them):** 5.3 credentials and 2FA (from 2026-11-07, at a computer); 5.4 deletions
(readme.html, the ten draft snippets, the two inactive plugins and their leftovers, `Default.html`, the ten unused media files); the Astra
update; the www redirect in Site Tools (4.6); the HSTS preload decision; the domain mailbox decision (5.5); the "For Practices" menu label
(open-items D; he has not chosen); anything that needs a fact from Butsaba.

## 5. Things that are easy to get wrong here

- The strip, the jump links and the heart links are PHP snippets with explicit per-page lists and plans; a new block or anchor needs the plan
  updated in the same installer, with the snippet's frozen code checked by sha256 before the edit.
- Spectra stores block attributes as JSON in the block comment; keep attribute order; `--` inside JSON is stored as `--` on live.
- `is_page('home')` in the strip matches the front page by slug `home` (page 296).
- The corporate builder (`corporate/build-corporate-massage.py`) and the chair builder describe their pages; after you edit those pages, update
  the builders' content files or retire their exact-state checks, and say which.
- Pixel comparisons (20 views) will differ wherever a block was added: expected, name each page and the height change.
- Do not re-fetch the public site in bursts (the challenge); server-side tools are unchallenged.
- Never put the `AGE-SECRET-KEY`, SSH details or passwords in a report, chat or commit.

## 6. What to send Ryan at each step

The four lines (`I finished:` / `Next step:` with his exact taps for a merge or a live run / `Next lane:` / `See my work:` one link) and a
TL;DR, every time. For a live run: "merge PR #N, then Actions > Site ops (LIVE) > Run workflow > branch main > type `<id>` > Run".
Keep `open-items.md` true in the same PR as the change that closes or moves an item; add a note per bundle in `.claude/project-memory/` and a
line in its README.

## 7. First message for Ryan to send the new agent

"Read docs/inter-model/2026-11-01-site-review-fix-handoff.md and docs/reviews/2026-10-31-site-review.md, then start with Bundle 0 and Bundle A on
staging38. Staging first, rehearse first, live only on my go. Give me the Butsaba message first."
