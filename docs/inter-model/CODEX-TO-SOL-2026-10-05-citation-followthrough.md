# Codex to Sol: Thai Solitude citation and chore follow-through

Date: October 5, 2026
Site: staging19.thaisolitude.com

This handoff records the October 5 staging19 verification after the citation pass and the subsequently approved medical-scope footer addition. Its current-state notes supersede the earlier request below where the check is now complete.

## Current state

Therapeutic Massage (page 1107) and Deep Tissue Massage (page 1091) render numbered markers beside A1-A6, linking directly to the named PubMed records. The current narrower sentences were matched to the cited abstracts; the exact original/current comparison is in `Thai Solitude - Citation Worklist.md`. Automated direct PubMed requests returned HTTP 203 challenge responses, so browser click-through remains unobserved. Thai Massage (page 1160) has no research sentence requiring a citation. Services (page 299) does not contain the temporary Codex-written research paragraph or bottom References block.

The Therapeutic and Deep Tissue research copy differs from the original passages preserved in the citation files. Ryan has now decided to keep the six improved, evidence-matched staging19 sentences. A proposed restoration of A2 was withdrawn before any site write. Do not revert or rewrite these sentences.

Codex's proposed replacement sentence for A1 in chat was not approved or written. Do not substitute it for Ryan's original claim.

Ryan clarified that Butsaba has had clients, just not in Portland, and said to leave the relevant client-experience copy. Do not describe her as having no client history or imply she served Portland clients.

Ryan approved the exact third-person medical-scope line in the shared staging19 footer: “Massage is not a substitute for medical care. Butsaba does not diagnose or treat medical conditions.” It was added directly below the existing license line after the fresh database dump `$HOME/private-backups/staging19-before-medical-scope-20261005T195329Z.sql`. The line and license each rendered once on Home, About, Services, Therapeutic, Deep Tissue, Thai, and Work With Me. Contact returned HTTP 403, so its footer remains unverified.

Ryan approved separate health-note updates on Services, Thai Massage, Therapeutic Massage, and Deep Tissue. The verbatim first-person note in the Decisions Register now renders once below each page's What to Expect heading and before the consultation cards. On Deep Tissue, Ryan confirmed moving the overlapping warning out of Common Reasons, preserving that paragraph's first two sentences verbatim, and adding a separate severe-pain sentence below the shared note. The fresh pre-write dumps are `$HOME/private-backups/staging19-before-services-health-note-20261005T200417Z.sql`, `$HOME/private-backups/staging19-before-thai-health-note-20261005T201656Z.sql`, `$HOME/private-backups/staging19-before-therapeutic-health-note-20261005T203107Z.sql`, and `$HOME/private-backups/staging19-before-deep-health-note-20261005T204612Z.sql`.

Ryan then approved the Work With Me license-lookup link. The credential item now reads “Oregon Licensed Massage Therapist — License No. LMT-29905 (Verify license),” with the link targeting the Board's public search form at https://public.orlicensing.oregon.gov/OBMTPublic/LPRBrowser.aspx. The page rendered the exact list item once and retained its other training links. The fresh pre-write dump is `$HOME/private-backups/staging19-before-wwm-license-link-20261005T212626Z.sql`. The destination is a search form, not a prefilled record; Ryan's supplied license information was not reverified.

## Completed verification and remaining decisions

1. A1-A6 original and current wording, marker placement, and paper scope were compared. The current sentences are qualified to the evidence; all six differ from Ryan's originals. Ryan chose to keep the improved sentences. Do not use Codex's unapproved A1 replacement from chat.
2. The Thai Massage body and metadata contain no research-existence claim; no citation edit is due there. Its meta description still needs ordinary SEO repair.
3. The wider approved chore list is partly complete. The public author display name/nickname/slug are now Butsaba, but REST user enumeration remains. The license and medical-scope lines are visible in the shared footer on seven public routes; Contact returned HTTP 403. Work With Me now has the adjacent Board lookup link. The approved health note is present in the same position on Services and all three massage pages. The résumé PDF lacks its own `X-Robots-Tag: noindex`. Stage robots.txt disallows crawling, while page metadata still says `index, follow`. Home SEO title and description and Services SEO title were approved, written, and verified in rendered title/meta/social fields on staging19 on October 5; Services SEO description remains open, with its exact replacement awaiting Ryan's decision. Root-relative links and duplicated Thai/Deep imagery remain open. See the current-state section at the top of `Thai Solitude Fix Checklist.md` and `Thai Solitude - Decisions Register.md`.
4. Ryan confirmed Oregon LMT-29905 was issued September 28, 2026 and expires March 31, 2028; take his word and do not retry the robot-blocked lookup. Do not change login details. Butsaba had clients before Portland; preserve truthful experience copy.
5. For any proposed site change, show exact wording, placement, and links one change at a time, obtain Ryan's approval, take a fresh staging database dump, then verify the rendered result. Ask twice before any removal.

## Requirements

- Work only on staging19. Do not edit localhost or production and do not promote.
- Place numbered markers beside the supported claims. Each marker links directly to the outside source. No reference list at the bottom.
- Keep the improved A1-A6 sentences already on staging19; do not silently rewrite them.
- Before each site change, show Ryan exact wording, placement, and links, then wait for approval.
- Do not include credentials.

## Codex Services-page cleanup

Codex briefly added an assistant-written paragraph, four citations, and a bottom References block to Services page 299. Ryan said to start over. Codex removed only that addition. The pre-cleanup database dump is /tmp/pre-citation-cleanup-staging19-20261005.sql. The mistaken paragraph and block are absent from the current page.
