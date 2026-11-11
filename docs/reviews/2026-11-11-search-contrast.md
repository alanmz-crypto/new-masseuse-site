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

## Proposed change and verification

The proposal was explained before implementation: use the existing dark panel
background on Search's affected surfaces, in a small marked Additional CSS
block. Scope it to dark mode and screen media so light mode and printing retain
their existing rules. Actual populated and empty Search markup will determine
the exact selectors before the payload is finalized. No Peek gestures, summary
words, destinations, page content or global palette tokens are changed.

The staging installer must bind the exact baseline and payload, retain its
actual private before-snapshot, refuse changed-state rollback, and verify exact
readback. A fresh staging39 copy with uploads must pass source, regulated-copy,
rendered Search, idempotence and exact recovery checks before any staging write.
Apply and rollback require a fresh DB export with verified encrypted off-site
readback; the actual snapshot must also be verified off-site before purge and
rendered verification. Public checks and final actual-snapshot verification
follow. INVALID evidence verifies nothing.

The release candidate will freeze the accepted quiet-navigation runtime and the
same finalized Search CSS. Its first-install rehearsal remains separate from
the completed in-place staging upgrade. The earlier automatic rejection of a
temporary live-data copy is not bypassed by this preparation; no new live-copy
request or live enablement has been dispatched.
