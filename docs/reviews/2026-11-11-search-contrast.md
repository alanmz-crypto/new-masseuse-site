# Search contrast before the Peek release

**In preparation; no Search website change yet.** Ryan asked to continue the
remaining release preparation after accepting the four navigation controls on
iPhone Safari. Fluidity refinements remain deferred until after live. This
phase corrects Search contrast on staging39 and refreshes the separate release
candidate; it does not authorize a live write or a main merge.

## Access and actual baseline

The feature branch was current at `174f80c7b18c8480dc502a5cf3691badda452242`.
GitHub reports admin/push/read access. The attached environment reports current,
enforced unrestricted networking. The inner sandbox could not reach its proxy;
one approved outer request to staging39 Search returned SiteGround HTTP202.
No repeated local public requests followed. Actions supplies actual inspection
and rendered-copy evidence.

Read-only request `peek-search-css-inspect-1`, commit
`558c13a27467356ec984199b15acad92fd4a6133`, passed at **05:51:19 UTC** in
[Actions 42968382007](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42968382007).
The report contains only the final 400 of 1,330 lines. The full job log was
retrieved separately and its CSS extracted with the exact reported length and
SHA256: Additional CSS **#29**, **68,843 bytes**,
`2806a8a19e13aab2c672a4f62bb8265c091944652c54d45c5bb3845629ee38ae`.
The actual stylesheet has no staging39 origins to rewrite for localhost.

The preceding public navigation captures show pale text on white Search result
cards in dark mode. Captured Astra 4.14.1 CSS gives `.ast-article-post` a white
background. The site's dark theme changes body/headings to pale colors without
overriding that surface. A CSS-only fixture using captured styles measured body
contrast **1.37:1** and heading contrast **1.13:1**. Switching the fixture's
surface to the existing `--ts-panel` gives **11.36:1** and **13.81:1**, with green
links **8.04:1**. These are diagnostic fixture measurements, not actual-page
browser results or a deployed correction.

## Initial rendered diagnostic (incomplete)

Read-only `peek-search-render-inspect-1`, commit
`5c6660b6d5dc8fef0758a2cdb404d5e930f48353`, finished **INVALID** at
**05:57:56 UTC** in Actions **42968966786**. Its diagnostic reached the real
populated Search page and saved light/dark measurements and two captures, then
stopped before empty Search. There were zero assertions; it is not a browser
gate pass. The checker incorrectly required `data-ts-theme="light"` after the
return toggle, while the site's light mode removes that attribute. The actual
theme switch worked. The corrected diagnostic normalizes that existing state.

The downloaded 458,616-byte artifact matched SHA256
`7e6d70aca69e55236deb65132b26a758dfe71f017418659cfba23f51092be5af`
and ZIP integrity checks. Its partial observations refine the CSS proposal:
the outer result article is transparent and the white surface is on its nested
`.ast-article-inner`. The full-page image also shows the affected Search title
panel and selected pagination number outside the initial measurement root.
Actual result headings measured 1.13:1, excerpts 1.37:1, green links on white
1.94:1. The dark Search submit has white text on green at 1.94:1; its white
input's placeholder is 2.54:1 in both themes. These are individual observations
from an incomplete diagnostic, not verification of a correction. The expanded
Search-only fix and placeholder change were explained before implementation.

Corrected read-only `peek-search-render-inspect-2`, commit
`9f1a6e8a22b30411e8f7f3846274cb2b58ebf543`, completed the diagnostic at
**06:02:56 UTC** in Actions **42969567826**: populated/empty Search in both
themes, **four cases, two loads and four captures**. There are intentionally
zero contrast assertions in inspection mode; this is complete baseline
evidence, not a correction pass. The 822,485-byte archive matched SHA256
`c551b680df841a208ef6a2dc11fc611b252c4a36e3ea54ec499cd5e4593b6a42`
and ZIP checks. Full actual page/body HTML and measured painted surface owners
are retained in the artifact. The full report and measurements were read.

The actual surfaces are `.ast-archive-description`, `.ast-article-inner` and
`.no-results`. All are white in dark mode. The selected pagination number also
has white text on green at 1.94:1. Both themes' empty-result screenshot and
actual controls are considered separately: a submit element's computed text
color does not establish its icon's contrast when the rendered control is an
SVG magnifier. Root visually inspected the populated and empty dark captures.
The final candidate uses the existing panel/night colors for these specific
Search surfaces and controls; the placeholder uses the input's existing dark
text color. No further public baseline retry is needed.

## Proposed change and verification

The proposal was explained before implementation: use the existing dark panel
background on Search's affected surfaces, in a small marked Additional CSS
block. Scope it to dark mode and screen media so light mode and printing retain
their existing rules. Actual populated and empty Search markup will determine
the exact selectors before the payload is finalized. No Peek gestures, summary
words, destinations, page content or global palette tokens are changed.

The finalized payload is **536 bytes**, SHA256
`3225b279ae3d20adf696ac9ad6a16dc2be7c7c15c8fbe64ba59e5a74dc14e9e0`.
Appending exactly one LF and that payload produces **69,380 bytes**, SHA256
`b024acc687b49a37115940b52976f3bdcfc6cb236ae7bb4a81422f8c2d02c69b`.
The title, nested result-card and empty-result panels use `--ts-panel` only
in dark screen media. Placeholders use `#475569` in both themes. The populated
form's text button and selected page number use `--ts-night` in dark mode.
The empty form's existing magnifier and transparent submit input are preserved.

Cached actual-page replay passed **150/0 in each of Chromium and Firefox**:
populated/empty routes, three widths, both themes, 12 captures per engine.
Every request was fulfilled from captured HTML/assets or blocked; this is
fixture evidence, not a fresh WordPress copy or public deployment test.
Root reviewed both dark phone captures. The retained diagnostic failures were
checker errors: serialized generated controls needed rebuilding in the replay,
and transparent submit text had incorrectly been treated as painted text.
The corrected checker reports that text as N/A, retains its decorative shadow
in evidence, and measures the actual magnifier independently. It also checks
placeholder, hover, focus, print, light-mode scope and unchanged Peek bytes.

The installer model passed **89 checks**, deployment-wrapper doubles **37**,
and the rehearsal harness **130**. These cover one binary CSS compare-and-swap,
changed-state refusal, exact snapshot binding and backup failure stops. They
do not replace the fresh WordPress and off-site checks below.

The staging installer must bind the exact baseline and payload, retain its
actual private before-snapshot, refuse changed-state rollback, and verify exact
readback. A fresh staging39 copy with uploads must pass source, regulated-copy,
rendered Search, idempotence and exact recovery checks before any staging write.
Apply and rollback require a fresh DB export with verified encrypted off-site
readback; the actual snapshot must also be verified off-site before purge and
rendered verification. Public checks and final actual-snapshot verification
follow. INVALID evidence verifies nothing.

The release candidate now freezes the accepted quiet-navigation runtime and the
same finalized Search CSS. Its local PHP model passed **1,694 checks**, including
**138 injected write-boundary failures**; the evidence harness passed **162**.
Its 28 browser leaves across 13 phases have not run on a fresh first-install
copy. Its first-install rehearsal remains separate from
the completed in-place staging upgrade. The earlier automatic rejection of a
temporary live-data copy is not bypassed by this preparation; no new live-copy
request or live enablement has been dispatched.
