#!/usr/bin/env bash
# Portland reconcile, items 1-3 in ONE run (Ryan, 2026-10-29: "Go live"): runs, in this order, the three guarded push scripts that were each applied and proven on staging38:
#   1. portland-meta/push-portland-meta.sh          SEO titles and descriptions (Home, Thai, Therapeutic, Deep Tissue)
#   2. contact-address/push-contact-address.sh      Contact page Address line -> "Portland, OR" (the map is not touched)
#   3. onpage-portland/push-onpage-portland.sh      Home licence line, Thai H1, Deep Tissue H1
# Each script does its own DB export, verified encrypted off-site copy, guarded apply, cache purge and public check, and stops on the first problem, so a later step never runs after a failed earlier one.
# What is already applied is not undone by a later stop: each step has its own exact rollback (run the single script with --rollback and its snapshot; the report lists the snapshot names).
#
#   ./push-portland-live.sh             dry run of all three (read-only on the site)
#   ./push-portland-live.sh --apply     apply all three, in order
# There is no --rollback here: roll back with the single script and its own snapshot. S_TARGET (staging38 | live) is passed through to each script; default staging38.
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
case "${1:-}" in "" | --apply) ;; *) echo "Usage: $0 [--apply]   (roll back with the single scripts)" >&2; exit 1 ;; esac
export S_TARGET="${S_TARGET:-staging38}"
case "$S_TARGET" in staging38 | live) ;; *) echo "S_TARGET must be staging38 or live." >&2; exit 1 ;; esac
for step in portland-meta/push-portland-meta.sh contact-address/push-contact-address.sh onpage-portland/push-onpage-portland.sh; do
  echo "=================== $step ($S_TARGET, ${1:-dry run}) ==================="
  "$here/../$step" "$@"
done
echo "All three steps finished on $S_TARGET."

# ref: ../portland-meta/push-portland-meta.sh, ../contact-address/push-contact-address.sh, ../onpage-portland/push-onpage-portland.sh — the single scripts this runs
