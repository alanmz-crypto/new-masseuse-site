---
name: surerank-per-page-meta-storage
description: "How SureRank stores per-page SEO title/description on Thai Solitude, and why flat surerank_settings_page_title meta silently does nothing"
metadata:
  node_type: memory
  type: reference
  originSessionId: 237489c7-4e49-4987-a46e-f923473d4077
  modified: 2026-10-15T17:00:51.339Z
---

SureRank (the SEO plugin on new-masseuse-site) stores per-page SEO in two **grouped array** post-meta rows: `surerank_settings_general` (page_title, page_description, ...) and `surerank_settings_social` (facebook_*/twitter_*). Writing flat keys such as `surerank_settings_page_title` is ignored: no error, the rendered `<title>` and descriptions stay on the auto-generated fallback.

**Why it matters:** with no override, every page's meta description is auto-built from page content, so the retained "Coming soon" banner text leaks into `description`, `og:description` and `twitter:description` (seen on /work-with-me/ on 2026-10-15). On 2026-10-15 no page had any `surerank_settings_*` row.

**How to apply:** write through the plugin's own saver, `\SureRank\Inc\API\Post::update_post_meta_common( $post_id, ['page_title'=>..., 'page_description'=>..., 'facebook_title'=>..., 'facebook_description'=>...] )`. Twitter follows Facebook (`twitter_same_as_facebook` defaults true), so setting facebook_* fixes og and twitter. It writes six rows (general, social, schemas, post_no_index/follow/archive); to undo, delete all six. Verify in rendered HTML, not the DB. Worked example: `scripts/work-with-me-employer/install-local.php` in new-masseuse-site. Related: [[no-mu-plugins-rule]].
