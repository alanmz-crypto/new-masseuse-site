---
name: banned-words-research-exception
description: "Ryan's standing rule (2026-10-19): if a banned word is needed to state evidence accurately, update the banned list to accept it; implemented as three tiers, research section only, never for we/our/us or claim verbs"
metadata:
  node_type: memory
  type: feedback
  originSessionId: b7972be6-8d16-4d78-b186-706afcc661a9
  modified: 2026-10-19T03:36:56.004Z
---

Ryan, 2026-10-19: "If something is on my banned words list but evidence says those words should be used, then update my banned words to accept it."

**Why:** the site's banned list (OBMT-derived house list; the Board's rules themselves contain no word list) was blocking accurate descriptions of studies ("chronic", "anxiety", "effectiveness" in paper titles). He wants fidelity to the evidence over a blunt word filter.

**How to apply (done 2026-10-19, see AGENTS.md "Exception for cited research"):** three tiers in `scripts/work-with-me-employer/client-pages/wording_tiers.py` (self-test with mutation checks in `check-all.sh`): (1) never accepted: we/our/us, studio, Portland-area/metro and the claim verbs heal/cure/treat/diagnose/guarantee/detox/inflammation; (2) the rest accepted only inside the research section (from the "What the Research Says About Thai Massage" heading to page end), refused everywhere else including meta fields; (3) verbatim paper titles in the Sources panel accepted as published. No heading = no exemption. Do not widen it to other pages without his word. His older rule still stands: no implying diagnosis, treatment of conditions or clinical populations, so I did NOT use disease-treatment studies (headache disorders, knee osteoarthritis vs ibuprofen, etc.) and kept clinical labels out of prose; offer them as a choice, don't add them silently. Related: [[thai-page-evidence]], [[therapeutic-copy-staging33]], [[stop-before-hard-to-reverse-changes]].
