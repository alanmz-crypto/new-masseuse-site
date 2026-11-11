# Site op `peek-title-resize-verify-1`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/push-peek-title-resize.sh` `--verify /home/customer/private-backups/peek-template-core-title-resize-before-20261111-101808-92c3067f.json` |
| lane | ops (site-ops.yml) |
| commit | `03b0b5e243a1231bcc53af46067c8e1d8295a31c` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/43005630245 |
| started | 2026-11-11 10:41:02 UTC |

## push-peek-title-resize.sh --verify /home/customer/private-backups/peek-template-core-title-resize-before-20261111-101808-92c3067f.json(S_TARGET=staging39): PASS

```text
Target site: staging39   component: core-title-resize   mode: verify
[1] Uploading the reviewed package outside every web root ...
staging39 is www/staging39.thaisolitude.com/public_html
Success: Exact core-title-resize applied core and all four page-data records verified read-only; core_state_sha256=e54fda8e0dbeba0f95e325b7fac470001befa3b04d7412ac4ae58ac4682c8a70 pages_sha256=3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9. Snapshot: /home/customer/private-backups/peek-template-core-title-resize-before-20261111-101808-92c3067f.json
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
Success: Exact core-title-resize applied core and all four page-data records verified read-only; core_state_sha256=e54fda8e0dbeba0f95e325b7fac470001befa3b04d7412ac4ae58ac4682c8a70 pages_sha256=3e9acc4ea6dfedf7fe99e7a70534603ba0d32a1827793755c8ee10f4e55c9cc9. Snapshot: /home/customer/private-backups/peek-template-core-title-resize-before-20261111-101808-92c3067f.json
```

**RESULT: PASS** - push-peek-title-resize.sh succeeded

_finished 2026-11-11 10:41:20 UTC_
