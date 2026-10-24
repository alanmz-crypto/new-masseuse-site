---
name: tmp-inode-exhaustion
description: "/tmp on this machine is a tmpfs capped at 1,048,576 inodes; when other work fills it, Bash dies with ENOSPC even though df shows free GB. Keep restore stacks and scratch on /home."
metadata:
  node_type: memory
  type: project
  originSessionId: 237489c7-4e49-4987-a46e-f923473d4077
  modified: 2026-10-18T01:00:56.977Z
---

On 2026-10-15 the Bash tool began failing with `ENOSPC: no space left on device, open '/proc/self/fd/19/....output'` and `zsh: no space left on device: /tmp/claude-XXXX-cwd`, while `df -h /tmp` showed 14 GB free. Cause: `/tmp` is `tmpfs ... size=32G, nr_inodes=1048576`, and `df -i /tmp` read 100% used. The inode hogs were NOT this project: `/tmp/convmem-hermetic-harness*` (~250k files), `/tmp/pytest-of-test` (~79k), `/tmp/safe-harbor-*-evidence-*` (~150k), plus `/tmp/claude-1000` (~26k). Each throwaway restore stack from `scripts/test-restore.sh` also costs ~12.5k inodes (and ~600 MB).

**Recurred 2026-10-18 (UTC) during the accessibility work:** Bash died again with ENOSPC (even `df` failed), which blocked all commands until Ryan acted. When I retried on his word, `/tmp` read `nr_inodes=4194304` (the cap had been RAISED from 1,048,576) with about 1,048,331 inodes in use (25%), so the inode hogs from other projects were still there but there is now headroom. My own /tmp use that session was tiny (a few dozen screenshot/body files named /tmp/ts-*); my heavy scratch lives under /home/lauer/.cache (ts-axe, ts-perf, ts-privacy-local, ts-post-change). If it recurs: ask Ryan to run `! df -i /tmp` and clear other projects' dirs; I cannot run anything while Bash is dead (only Read/Write/Edit on /home work).

**Why it matters:** the harness itself writes tool output and a cwd marker under /tmp, so a full inode table kills every shell call (some succeed intermittently as other processes free inodes). The Write tool also fails for /tmp but works on /home.

**How to apply:** diagnose with `df -i /tmp` and `for d in /tmp/*; do echo "$(find "$d" -xdev | wc -l) $d"; done | sort -rn | head`. Do not delete other arcs' dirs (convmem harness, pytest, safe-harbor evidence); ask Ryan. Keep my own load off /tmp: run `scripts/test-restore.sh` with `MASSEUSE_RESTORE_ROOT=/home/lauer/.cache/wwm-scratch/restore-<port>` and `MASSEUSE_RESTIC_CACHE_DIR=/home/lauer/.cache/wwm-scratch/restic-cache`, and put screenshots/scratch under `/home/lauer/.cache/wwm-scratch/` instead of the /tmp scratchpad while this persists. A restore stack that died at step 1 leaves `/tmp/masseuse-restore-<port>`; remove it before retrying. See [[work-with-me-employer-update-local]].
