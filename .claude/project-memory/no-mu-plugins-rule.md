---
name: no-mu-plugins-rule
description: Never deploy design/behavior via mu-plugins or remote files for Thai Solitude; use Additional CSS, ihaf header option, astra-settings and page content so a SiteGround push carries it
metadata:
  type: feedback
---
Ryan's rule for the new-masseuse-site / thaisolitude.com work: no MU-plugins (or any remote plugin/theme files). Put everything in WordPress-native storage: Additional CSS (`custom_css`/astra), `ihaf_insert_header`, `astra-settings`, page `post_content`.

**Why:** On 2026-10-04 the restyle went to staging14 through host/page-guarded MU plugins. Ryan pushed staging14 to live and live got only the page text; the guards (`home_url()===staging14`) kept the CSS, fade script and license footer inert. He also wants the whole site restyled, not per-page scoping.

**How to apply:** Before any deploy, confirm the change is stored in the DB (posts + options tables) and tell Ryan the push must include both tables. Rehearse on localhost / the restore stack first. Scope with CSS selectors, not guards in PHP files. Rule is also written in the project's AGENTS.md. See [[ryan-browses-with-dark-reader]] and [[darkreader-white-flash-causes]].
