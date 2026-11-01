# Site op `review-e-upload-paths-staging38-20261101`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `review-e/inspect-upload-paths.sh`  |
| lane | ops (site-ops.yml) |
| commit | `ebbebb9a5ec09a403223577ba7896aa41c77415f` on `codex/2026-11-01-review-bundle-e` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41303626325 |
| started | 2026-11-01 11:50:07 UTC |

## inspect-upload-paths.sh (S_TARGET=staging38): PASS

```text
{
    "logical_basedir_matches_realpath": false,
    "logical_relative_correct": true,
    "physical_relative_correct": false,
    "physical_relative_is_absolute": true,
    "snapshot": "bundle-e-before-20261101-114734.json",
    "snapshot_complete": false,
    "owned_media_record_remains": false,
    "attached_file_was_absolute": true,
    "created_upload_file_count": 3,
    "created_upload_files_remaining": 0,
    "page_old_state": {
        "home": true,
        "therapeutic-massage": true
    },
    "new_snippets_remaining": 0
}
```

**RESULT: PASS** - inspect-upload-paths.sh succeeded

_finished 2026-11-01 11:50:16 UTC_
