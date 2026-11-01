# Review Bundle B — search and sharing

Staging38 applied/verified; live preparation PR #69 is ready for Ryan's own run,
not applied. The 2026-11-01 handoff authorizes the listed
metadata/wording edits. The five unanswered Butsaba questions are independent
and remain pending. The four supplied answers are recorded, not applied here.

Fresh capture `review-b-capture-staging38-20261101` PASS. Archives verified:
site `dcbb4b86e99db5139d70b145bed025af620b4059cfbf157229dc335d295a5f9c`;
schema source/settings/logo `2fd3ff78d355e525bad288894f8e8acd053c09755e4b17f04f6f8870459ffb8a`.
Decoded trees stay outside git. `changes.json` freezes only necessary values;
`build-bundle-b.py` packages them and checks hashes/wording. Exact old snippet
hash `9cc63d278e5e5812899a6f0e0bf24a81e44f46f764b0a7b003d4e0e8faa8354e`.

One installer: seven page-title/description groups, About/Contact Organization
rows, Corporate HTTPS social URL, existing global Person, tagline,
LocalBusinessInfo, one exact Home card wording replacement, and the approved
default/Home share card. All snapshots
include the ten pages' exact content/SureRank rows plus both options and snippet.
Pages are looked up by slug, snippet by published title. Preflight refuses drift
or mixed states; rollback refuses unless every whole stored resource is still
exactly applied. Page saves precede metadata restore, preserving exact old rows.
SureRank's own saver is used. Plugin source confirms global Person inheritance,
`%site.url%#%id%` produces one `/#person` identifier, and jobTitle survives render.

The Corporate description is the exact approved **162-character** sentence;
its verifier exception is limited to that wording. Other description limits stay
at 160. Home's corporate card also gains “by arrangement” per review 3.9; its
builder and generated content move together. Other historical installers need
B rolled back before their earlier exact-state rollback can be used.

The 1200×630 share-card preview uses the current published logo, unchanged,
and the requested text. Its small licence line repeats the site's existing
Oregon License LMT-29905. Colours and fallback font stacks come from Additional
CSS. SVG is the layout source; PNG is a CairoSVG rendering. No photo or new
illustration. Logo source SHA256:
`5d4845d84308bd1e91afdf9be8274a89f2440f8e41820915236e8a32f8c9553a`.
Ryan approved this exact card in chat on 2026-11-01: **“Yes, use this card.”**
The installer imports the PNG once by hash, assigns its URL to the global fallback
and Home social metadata, and leaves existing images intact. Home uses a URL-only
setting so rehearsal re-apply does not depend on a new attachment ID. Exact rollback
restores the old settings first and removes only the attachment created by that
snapshot; any pre-existing identical attachment is retained. Rehearse with uploads.

Cloud Git CLI authentication is unavailable; the connected GitHub tool works.
Actions requests use the exact `request.sh` JSON protocol through GitHub tree /
commit / ref tools, with a lease on the expected branch head. No token is read,
no guard is weakened, and the same Actions rehearsal/staging lanes enforce the
sequence and take backups. Requests run once; all reports are read on ops-log.

Sequence: code gates; commit package; rehearsal from staging38; staging dry run;
apply (fresh export + verified encrypted off-site copy first); automatic tests
and restore proof; semantic scan/browser verification. Live only on Ryan's go
and through his own run. His later A–E go authorizes the prepared request
`live-review-b-20261101-1` in PR #69; fresh live-source uploads rehearsal and
actual live dry run pass. [Release proof/phone steps](../../../docs/reviews/2026-11-01-review-live-release.md).

## Staging evidence

Applied 2026-11-01 08:46 UTC on staging38. Uploads rehearsal2, dry run, apply,
rendered gates/posture and strict 12-page semantic scan PASS. Approved card is
attachment 1314; SHA256 and 1200×630 dimensions match; Home OG/Twitter and the
global fallback reference it. Backup restored successfully; expected A/B
entity/pixel differences versus live are recorded, not suppressed. Snapshot
`bundle-b-before-20261101-084613.json`; export
`staging38-before-bundle-b-20261101-084559.sql`, both verified encrypted off-site.
Full evidence/rollback: `docs/reviews/2026-11-01-bundle-b-staging38.md`.
PR #63 is merged. Follow-up live preparation is PR #69, not an apply.
The selected-host source capture supports `--sources-only`; it no longer combines
a live dump with staging plugin source. Installed SureRank's relevant APIs/rendering
match the reviewed source; the original B payload/card and exact rollback remain.

## Live record (supersedes preparation status above)

LIVE through Ryan's PR #69/run `live-review-b-20261101-1` on 2026-11-01.
Strict live 12-page scan passes with zero expectation failures; approved card is
attachment 1305, PNG 1200×630 and exact SHA, HTTPS default/Home/social values.
Actual snapshot `bundle-b-before-20261101-163141.json` and fresh export
`live-before-bundle-b-20261101-163126.sql` are verified encrypted off-site.
[Full live proof](../../../docs/reviews/2026-11-01-bundle-b-live.md). C/later touching
bundles must be undone before B, then B before A. C is next in PR #64.
