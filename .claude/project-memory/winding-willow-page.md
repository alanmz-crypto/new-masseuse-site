---
name: winding-willow-page
description: 2026-11-13 new UNLISTED page /winding-willow/ (Sa's massage at Winding Willow Acupuncture & Wellness, equal-weight services, sandwiched research section); staging39 only, not live
metadata:
  type: project
---
**What:** a new, unlisted page `/winding-willow/` ("Massage with Sa at Winding Willow Acupuncture & Wellness"): Winding Willow as a whole (three equal, alphabetical service cards), Sa's three massage types, "Branching Out", a one-line disclosure, the address, and a research section "What the Research Says About Massage Alongside Other Care" (two sandwiched panels + Sources, 7 PubMed sources). No menu item, no Services row, no snippet or CSS change.

**Ryan's words (2026-11-12/13, ButsabaJobScout session):** keep Winding Willow's services equal ("we want people to consider Winding Willow as a whole and not just Butsaba"); research at the bottom, sandwiched; "move it to staging so it follows all the rules of thaisolitude.com"; answered: unlisted page, register the research heading, reword "treatment"; booking link = https://www.windingwillowacupuncture.com/butsaba-dusrai-lmt. Target staging39 (he first picked staging36 from an out-of-date note; staging39 is the current copy).

**Evidence limits (do not loosen):** no coordinated-care claim (no Winding Willow page confirms it); "complementary" is not "synergistic"; adding acupuncture or a herbal compress to Thai massage showed no added benefit (Siriyong 2026, Laosee 2020), and that stays in the middle of the sandwich. Full audit: ButsabaJobScout `Research/Butsaba_Dusrai_Winding_Willow_Massage_Complementarity_Evidence_Audit_Portland_2026-11-12.md` (on Ryan's computer).

**Tooling:** `scripts/work-with-me-employer/winding-willow/` (`build-winding-willow-page.py` -> `winding-willow-content.php`, gated in `check-all.sh`; `install-winding-willow-page.php` allow-list localhost:8090 + staging39; `push-winding-willow-page.sh` staging39 only). Heading registered in `client-pages/wording_tiers.py` (+ self-test cases). Localhost: apply, re-apply, exact rollback, re-apply, refusals verified 2026-11-13; desktop and 390 px renders checked, no sideways scroll.

**Before live (Ryan's go only):** fix the "[License Number]" placeholder and the "Dsrai" misspelling on Sa's Winding Willow pages (this page links to them); decide noindex/sitemap and any link to the page; enable live in the installer and push script; he runs the live apply.
