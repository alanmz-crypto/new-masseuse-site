# Review Bundle A — booking paths and internal links

Authorized by `docs/inter-model/2026-11-01-site-review-fix-handoff.md` (Ryan's
“fix all”). Staging38 first; rehearsed first; live disabled. No Butsaba-dependent
facts, deletions, credentials, menu labels or live requests.

The stored-byte source is the fresh read-only staging38 capture
`review-a-dump-staging38-20261101`, archive SHA256
`29978f3af1e37d0ec2d5bc8661f24cbb84c3e1b7ca43c376c2899f5d47062487`.
The snippet bytes were independently confirmed by
`review-a-snippet-hashes-20261101`: A11yPageJumpLinks SHA256
`1c7a3c5e97905d57ce61d0eeb16a7874349baf0b95693e21335445edaf865b22`.
Only the exact replacement fragments and frozen jump snippet are tracked;
the decoded dump remains outside git. `wp post get` adds one output newline;
that byte was removed and the snippet hash checked against the server reading.

`changes.json` contains every exact before/after. `build-bundle-a.py` validates
complete block JSON, the snippet hash and the added wording, then generates
`bundle-a-content.php`. The installer looks up pages by slug and the snippet by
title, never by deployment-specific ID. Existing page and media IDs in captured
markup are retained; no new media or attachment IDs are introduced.

Changes:

- Home: three Services buttons become Therapeutic massage, Deep tissue massage
  and Thai massage, each linking directly to its page; About and Contact become
  root-relative, in both stored JSON and anchor markup.
- Services and four service pages: closing Next step block, date sentence and
  two outlined buttons. Four service pages also gain Other services before the
  research heading, linking the other four offers.
- Chair: the three existing Corporate and Group Massage page phrases become links.
- Corporate: the approved Chair reading sentence after the five-hour paragraph;
  phone-only Request a quote / Email butsabad@gmail.com shortcuts using the
  existing prefilled quote email. CSS follows Work With Me, including scroll and
  focus clearance and the dark background.
- Contact: fourth card after Address, approved sentence, existing prefilled quote
  email, Learn more link; Corporate quote replaces Map in its jump row. The map
  remains present and unchanged.
- Services: Chair, Corporate, Therapeutic, Deep Tissue, Thai row order; the jump
  row becomes Corporate & group, Chair massage, Thai massage, What to expect.
  Append the approved availability sentence to Your Session.
- About: existing Home licence line and Board link, plus Explore services,
  Corporate & group massage and Ask a question outlined buttons.
- One appended, marked Additional CSS block styles only these additions.

The footer widget addition is deferred: no proof that its existing geometry
stays unchanged at 30 widths has been produced. No footer layout is changed.
The Chair FAQ links are authorized in Bundle A; the existing Spectra tab roles
remain a known accessibility limitation until Bundle C's FAQ-role fix.

Historical page builders still describe their original releases. Bundle A's
fragment plan describes the subsequent edits. Their exact-state checks and
rollback installers are not applicable to the changed pages/snippet until
Bundle A is rolled back; do not regenerate and overwrite a current page from
an older builder. Bundle A's snapshot is the current exact rollback.

Run sequence (all server work through Actions):

1. `python3 -I -B scripts/work-with-me-employer/review-a/build-bundle-a.py`
2. `scripts/check-all.sh`; commit and push.
3. `scripts/ci/request.sh rehearse --source staging38 --installer review-a/install-bundle-a.php`
4. `scripts/ci/request.sh run --target staging38 --script review-a/push-bundle-a.sh`
5. Same run with `--args "--apply"` (DB export and verified encrypted off-site
   copy first; post-change tests and restore proof automatically).
6. `scripts/ci/request.sh run --target staging38 --script review-a/check-bundle-a.mjs --args "https://staging38.thaisolitude.com inline"`

The rehearsal now runs Home and client-page rendered copy gates on its applied
throwaway copy, before cleanup, so staging is blocked if regulated-copy checks
fail. Rehearsal also proves apply, repeat apply, exact rollback and re-apply.
The installer refuses mixed states, changed snippets and changed anchors.
Rollback refuses unless every whole page, snippet and CSS post is still exactly
as applied; it restores the snapshot's exact old bytes. Both staging apply and
rollback take a fresh export and off-site copy first.

Rollback command (substitute the exact private snapshot printed by apply):

```sh
scripts/ci/request.sh run --target staging38 --script review-a/push-bundle-a.sh --args '--rollback $HOME/private-backups/bundle-a-before-YYYYMMDD-HHMMSS.json'
```

Added blocks and longer labels intentionally change affected-page pixels and
heights. `check-bundle-a.mjs` measures heights with additions shown/hidden in the
same DOM; this is an estimate of the height change, not a before/after pixel
proof. Read the automatic pixel-test differences in that context.

No live host is allowed. After staging evidence is reviewed, Ryan must give the
live go before an enable-live commit and request are prepared. He merges and
starts Site ops (LIVE) himself.

Bundle G must include the five new closing date sentences: **15 total** after
Bundle A, rather than the original ten. The Services page is now also in scope
for that after-November-14 refresh.
