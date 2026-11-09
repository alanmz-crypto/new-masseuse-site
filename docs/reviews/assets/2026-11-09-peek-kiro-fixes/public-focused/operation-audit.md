# Focused public verification audit

Request `peek-kiro-fixes-public-1`, run `42699275642`, source `0c47495e67d06dd5a5caac1479476f5ff4d76d19`, staging39 only. Report started 2026-11-09 21:06:26 UTC and finished 21:19:05 UTC, PASS.

All 305 sanitized report lines read. All 33 raw artifact streams scanned in full (13,167 log lines): 13 nonempty numeric leaf suites, three retained-wrapper streams, one top aggregate stream and 16 empty stderr streams. Every leaf's literal PASS count agrees with its summary; no FAIL or INVALID lines, unclassified nonempty line prefixes, missing summaries, or inconsistent duplicate counts. All 16 distinct wrapper/suite exit records consistently show child_exit=0, signal=none, effective_exit=0. Nested final verdicts are complete: core 5 suites; title strip 3 wrappers; title rail 3 wrappers; Kiro fixes 5 wrappers.

| Unique numeric leaf | Passed | Failed |
|---|---:|---:|
| Disclosures Chromium | 66 | 0 |
| Disclosures Firefox | 41 | 0 |
| Kiro layout Chromium | 158 | 0 |
| Kiro layout Firefox | 134 | 0 |
| Title rails Chromium | 1022 | 0 |
| Title rails Firefox | 1018 | 0 |
| Pages Chromium | 172 | 0 |
| Title strip Chromium | 1110 | 0 |
| Title strip Firefox | 1107 | 0 |
| Clearance Chromium | 3340 | 0 |
| Clearance Firefox | 3340 | 0 |
| Native touch Chromium | 387 | 0 |
| Clearance diagnostic subset Chromium | 342 | 0 |
| **Total: 13 suites** | **12237** | **0** |

The four new leaves contribute 399; the nine retained leaves contribute 11838. Replayed nested counts are not added twice.

The native constrained Chair card independently satisfies the strict guard. Five attempted viewports each retain scale 1 and visual origin 0/0. The selected requested and visual viewport is 390×180, root font 27.36px, title font 25.992px, title height 72.75px, card 312×156px. The title is hit-testable, with a 60px stroke and genuine outer range 21px. Actual native swipe moves outer scroll 0→21; page stays x0/y704, inner stays0, and hash/index5/pinned/right-side state remain unchanged. The layout viewport is 463×214 because of outside wide page content; the visual viewport remains the requested size. No scale/origin guard was waived.

Title-strip and title-rail suites cover 21 actual routes, including all 19 published pages. The retained Firefox Rail-practice Enter defect is explicitly reported at 390/768/1280 widths; this run does not claim to fix it. Physical devices, Safari/WebKit, assistive technology and true OS text scaling are not established by these browser checks.

Artifact 13711243153: 25,811,348 bytes, created 21:19:12Z, source metadata matches the requested commit. Downloaded ZIP SHA256 matches the GitHub digest: `386bd85f763d68309f9771d64581551a2daa091a88f399f761f5b1bf057752e2`. Extracted artifact: `/tmp/peek-kiro-fixes-public-1-artifact`. Detailed numeric/stream audit: `/tmp/peek-kiro-fixes-public-1-audit.json`.

Screenshot assessment/persistence belongs to the separate evidence audit. No website calls, operations, repository edits, commits or pushes were made by this audit.

Independent second review of all four new leaf logs confirmed 399/0, all four stderr streams empty and no FAIL/INVALID/ERROR/WARN. Privacy disclosure traces retain correct state and native button focus, landing about 11.5–12.52px below the strip despite Chromium compatibility mouse events; new visitor-input cancellation also passes. Known layout diagnostic: simulated 150% root text makes the existing non-Peek `.ts-corp-band__link` overflow about 24.59px (Chromium document width415, Firefox411). The visible viewport remains390×664 atscale1, and Peek stays inside it. This is not a new Peek drift or true OS text-scale validation.
