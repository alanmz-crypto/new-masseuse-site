# Site op `peek-quiet-nav-final-verify-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-peek-quiet-nav.sh` `--verify /home/customer/private-backups/peek-template-core-quiet-nav-before-20261111-043846-d52816fb.json` |
| lane | ops (site-ops.yml) |
| commit | `8a6c584bdc4efba94d3978bb9bbdfcefa3f9b722` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42966700614 |
| started | 2026-11-11 05:35:12 UTC |

## push-peek-quiet-nav.sh --verify /home/customer/private-backups/peek-template-core-quiet-nav-before-20261111-043846-d52816fb.json(S_TARGET=staging39): PASS

```text
Target site: staging39   component: core-quiet-nav   mode: verify
[1] Uploading the reviewed package outside every web root ...
staging39 is www/staging39.thaisolitude.com/public_html
Success: Exact core-quiet-nav applied core and all four page-data records verified read-only; core_state_sha256=74ea2dd67eaf86ea065abeba398d41df6fe0f5451e1e0b36d11e2b309d60e56f pages_sha256=3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9. Snapshot: /home/customer/private-backups/peek-template-core-quiet-nav-before-20261111-043846-d52816fb.json
  200 /
  exact served core/style and data scope: home
  200 /about/
  exact served core/style and data scope: about
  200 /accessibility/
  exact served core/style and data scope: accessibility
  200 /chair-massage/
  exact served core/style and data scope: chair-massage
  200 /contact/
  exact served core/style and data scope: contact
  200 /corporate-massage-portland/
  exact served core/style and data scope: corporate-massage-portland
  200 /deep-tissue-massage/
  exact served core/style and data scope: deep-tissue-massage
  200 /in-page-link-ideas/
  exact served core/style and data scope: in-page-link-ideas
  200 /index-nav-ideas/
  exact served core/style and data scope: index-nav-ideas
  200 /personal-automation/
  exact served core/style and data scope: personal-automation
  200 /personal-automation/privacy/
  exact served core/style and data scope: privacy
  200 /personal-automation/terms/
  exact served core/style and data scope: terms
  200 /privacy-policy/
  exact served core/style and data scope: privacy-policy
  200 /progress-nav-ideas/
  exact served core/style and data scope: progress-nav-ideas
  200 /rail-nav-ideas/
  exact served core/style and data scope: rail-nav-ideas
  200 /services/
  exact served core/style and data scope: services
  200 /thai-massage/
  exact served core/style and data scope: thai-massage
  200 /therapeutic-massage/
  exact served core/style and data scope: therapeutic-massage
  200 /work-with-me/
  exact served core/style and data scope: work-with-me
Success: Exact core-quiet-nav applied core and all four page-data records verified read-only; core_state_sha256=74ea2dd67eaf86ea065abeba398d41df6fe0f5451e1e0b36d11e2b309d60e56f pages_sha256=3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9. Snapshot: /home/customer/private-backups/peek-template-core-quiet-nav-before-20261111-043846-d52816fb.json
```

**RESULT: PASS** - push-peek-quiet-nav.sh succeeded

_finished 2026-11-11 05:35:29 UTC_
