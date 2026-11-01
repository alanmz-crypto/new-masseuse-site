# Review Bundle C — markup, accessibility and utilities

Staging38 first; live disabled. Authorization: listed changes in the
2026-11-01 handoff/review. Ryan separately approved `butsabad@gmail.com` for
security.txt. No service facts, credentials, theme updates or existing-content
deletions. PR #63 (Bundle B) is merged; this branch continues from its merge.

Fresh captures:

- `review-c-capture-staging38-20261101` PASS: native page/snippet/option/CSS stores;
  actual public Spectra FAQ and Astra event handlers. DumpSHA256
  `4755fd2c1dc35a3a0c6477fea91d23966b5dcbb69ebabaac7b7eb2f91aa3cc30`;
  stores/source archive `9b9f7ccb0dd08b6a8285c70bbbd7f33e03a96ef029f95fb7a83a9b2fbc3d2b0d`.
- `review-c-css-source-staging38-20261101` PASS: generated CSS under assets/0;
  SHA256 `0d584c9ad80175001a40cef375e4095d0de4d63f7d776305fc17603f3112b996`.
- `review-c-baseline-staging38-20261101` PASS: seven pages × 1280/390 px. Frozen measured
  font/margin/box values in `heading-baseline.json`. Scoped CSS preserves the
  measured 24/22/20 px sizes and 1.3 lineheight, including tablet source rules.

`changes.json` freezes exact before/after fragments and old/result content
hashes for staging38 and the localhost rehearsal. There are 23 Spectra paragraph
headings and 3 Corporate card headings, not the handoff's stale 16/19 count.
Services Chair/Corporate titles are already H3. Every target's words/IDs remain.
Add hidden Services H2 “Massage services” and the Therapeutic FAQ H2.

Native FAQ button styles are in the marked Additional CSS block, following the
house storage rule; the footer snippet contains only behavior.

One installer covers all 12 affected pages, two frozen active snippets, CSS,
Astra's blog-meta array and two new WPCode snippets. It preserves page SureRank
rows and snapshots the whole Astra option and existing snippet settings. Any
partial state or drift refuses. PHP syntax is checked before writes. Exact
rollback requires current whole stores and newly created snippet post/meta/terms
to match the snapshot; restore stores first, then remove only those new snippets.
The push script exports the DB and verifies an encrypted off-site copy before
apply or rollback. Existing images/snippets/files remain.

Reviewed visible/UI changes:

- 49 FAQ icons closed/down, open/up; native buttons expose expanded/controls while
  retaining Spectra click handlers. The original item loses its tab role and
  keyboard stop. Default geometry is checked in the browser.
- New Therapeutic FAQ H2. Hidden H2 and tag swaps preserve geometry.
- Mobile strip keeps the November 14 and summer 2027 facts in shorter text:
  “Corporate & group massage bookings begin November 14, 2026. Individual
  appointments are planned for summer 2027.” After the Pacific boundary it uses
  “Now accepting corporate & group massage bookings. Individual appointments
  are planned for summer 2027.” Desktop copy is unchanged. Region name Booking
  notice; only the displayed text variant enters the accessibility tree.
- Home jump label About Thai Solitude → Learn about Thai Solitude, target retained.
- Five gold focus outlines use the existing deep accent; phone Privacy/Accessibility
  H1 spacing and Home hearts row gap are tightened. Contact phone gains underline.
- Contact iframe is titled Map of Portland, Oregon and really lazy-loads. Exact
  Mt. Tabor/zoom/locale URL and 500/350/325 px geometry remain. CSP already permits
  maps.google.com; run the existing CSP check after the embed-type change.
- Résumé labels announce a new tab through the runtime, preserving valid core
  Button serialization. Footer names are distinct; scrolltop has button role and
  Space support (Astra already handles Enter).
- 404 navigation adds Home/Services/Corporate/Contact. Search gains a form, loses
  blog bylines through Astra blog-meta[], and says View page ». RSD/oEmbed/shortlink
  discovery tags are removed. security.txt is a virtual RFC 9116 plain-text endpoint;
  no server file/rewrite flush. Contact mailto:butsabad@gmail.com; fixed expiry
  2027-10-31T23:59:59Z (owner review before then).
- Accessibility statement v5 names Chair in both existing research-page lists,
  records Chair/Sources mouse/keyboard/contrast checks and the November 1 date.
  Existing limits/no-screenreader disclaimer remain. V4 is preserved exactly.

Chair/Corporate builders and generated content move together. They describe
historical install baselines; C must be rolled back before their earlier exact
installers/rollbacks. C layers after B/A; use reverse order for rollback.

`check-rehearsal.mjs` opts into the generic package browser gate: the Actions lane
installs browser dependencies, and the throwaway rehearsal runs this gate with
uploads before cleanup. It checks headings against the measured baseline, FAQ
keyboard/mouse/ARIA, focus, map, strips, spacing, utilities, Chair native panels
and light/dark contrast. No staging write until that gate and rendered copy pass.


## Current staging state

Applied on staging38 at 09:45 UTC. Rehearsal 2 passes 1,248 assertions and exact
rollback/re-apply. Dry/apply, off-site backup, rendered gates/posture, restore
import and focused Contact CSP in Chromium/Firefox PASS. The final whole-site
public browser attempt is INVALID at its first page (robot challenge), so it
proved no assertion. PR #64 stays draft pending final verification. Source proof,
report links and exact rollback: `docs/reviews/2026-11-01-bundle-c-staging38.md`.

Snapshot `bundle-c-before-20261101-094545.json`; export
`staging38-before-bundle-c-20261101-094534.sql`. C must be rolled back before B/A
on affected stores. No live or hosting-protection change was made.

Ryan has requested help resolving the host challenge. Supported support route:
`docs/inter-model/2026-11-01-siteground-antibot-support.md`. No authenticated
SiteGround admin/support session is connected. No support message sent.

The security.txt PHP fallback passes Apache rehearsal, but its public SiteGround
GET delivery remains unverified. Earlier HEAD was nginx 404/93 bytes; a static
owner upload may be needed. Prepared approved staging payload:
`security-staging38/security.txt`; no server file has been written. Do not alter
the applied snippet directly and invalidate its exact rollback snapshot.
