#!/usr/bin/env bash
# Deep Tissue research section + FAQ + body paragraph, THEN the slide-open CSS for the research panels, in one run (S_TARGET=staging36|live; default staging36).
# Ryan, 2026-10-24: "Prepare both for live". One live request can only name one script, so this wrapper calls the two reviewed push scripts one after the other and stops at the first failure.
# Each part keeps its own export, snapshot and exact rollback; roll back by running the part's own script with --rollback (see the report: each apply prints its snapshot).
#
#   ./push-deep-tissue-and-slide.sh              dry run of both (read-only on the site)
#   ./push-deep-tissue-and-slide.sh --apply      1) push-deep-tissue-evidence.sh --apply   2) ../research-slide/push-research-slide.sh --apply
# Nothing else is accepted (no --rollback here: use the two scripts).
set -euo pipefail
here=$(cd "$(dirname "$0")" && pwd)
case "${1:-}" in
  ""|--apply) ;;
  *) echo "Usage: $0 [--apply]   (roll back with the two scripts' own --rollback)" >&2; exit 1 ;;
esac
[ "$#" -le 1 ] || { echo "Usage: $0 [--apply]" >&2; exit 1; }
echo "================ 1 of 2: Deep Tissue research section, FAQ and body paragraph ================"
"$here/push-deep-tissue-evidence.sh" "$@"
echo
echo "================ 2 of 2: slide-open CSS for the research panels ================"
"$here/../research-slide/push-research-slide.sh" "$@"
echo
echo "Both parts finished${1:+ (applied)}."
# ref: ./push-deep-tissue-evidence.sh, ../research-slide/push-research-slide.sh — the two reviewed scripts this runs
