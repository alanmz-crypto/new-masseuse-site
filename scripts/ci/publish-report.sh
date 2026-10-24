#!/usr/bin/env bash
# GitHub Actions: put the run's report where its reader finds it. Runs even when the operation failed.
#
#   publish-report.sh REPORT.md REQUEST.json
#
# 1. the run's summary page (what Ryan sees in the Actions tab, also on a phone)
# 2. the ops-log branch (created on first use; never merged into anything): reports/YYYY-MM-DD/HHMMSS-<id>.md, plus INDEX.md newest first.
#    A cloud session reads it with `git fetch origin ops-log` (scripts/ci/request.sh wait ID does that). Pushed with the job's own token,
#    which starts no other workflow.
# If the operation never wrote a report (setup failed before it ran), a short INVALID report is written instead.
set -uo pipefail

report="${1:?usage: publish-report.sh REPORT.md REQUEST.json}"; req="${2:-}"
: "${RUNNER_TEMP:?GitHub Actions only}"
id=$(jq -r '.id // empty' "$req" 2>/dev/null); [[ "$id" =~ ^[a-z0-9][a-z0-9-]{2,59}$ ]] || id="unknown-${GITHUB_RUN_ID:-0}"
if [ ! -s "$report" ]; then
  {
    echo "# Site op \`$id\`"; echo
    echo "The operation did not start: a setup step failed before it (most often the SiteGround key is not imported yet, or a secret is missing)."
    echo "Open the run to see which step: ${GITHUB_SERVER_URL:-}/${GITHUB_REPOSITORY:-}/actions/runs/${GITHUB_RUN_ID:-}"; echo
    echo "**RESULT: INVALID** - nothing was run"
  } > "$report"
fi
head -c 900000 "$report" >> "${GITHUB_STEP_SUMMARY:-/dev/null}"

root=$(git rev-parse --show-toplevel)
wt="$RUNNER_TEMP/ops-log"; rm -rf "$wt"
if git -C "$root" fetch -q --depth=1 origin ops-log 2>/dev/null; then
  git -C "$root" worktree add -q --detach "$wt" FETCH_HEAD
  git -C "$wt" checkout -q -B ops-log
else
  git -C "$root" worktree add -q --detach "$wt" HEAD
  git -C "$wt" checkout -q --orphan ops-log && git -C "$wt" rm -rfq . >/dev/null
  cat > "$wt/README.md" <<'MD'
# ops-log

Reports of the site operations GitHub Actions ran for this repository (workflows `site-ops.yml` and `site-live.yml`), one file per request,
newest first in `INDEX.md`. Written by the workflows only; never merge this branch into anything. The format of a request is in
`ops/README.md` on the main branch.
MD
fi
day=$(date -u +%Y-%m-%d); file="reports/$day/$(date -u +%H%M%S)-$id.md"
mkdir -p "$wt/reports/$day"; cp "$report" "$wt/$file"
result=$(grep -o 'RESULT: [A-Z]*' "$report" | tail -1 | cut -d' ' -f2); result=${result:-UNKNOWN}
line="- $(date -u '+%Y-%m-%d %H:%M') UTC  **$result**  \`$id\`  [$file]($file)  (run ${GITHUB_RUN_ID:-?})"
{ echo "# Index (newest first)"; echo; echo "$line"; [ -f "$wt/INDEX.md" ] && tail -n +3 "$wt/INDEX.md"; } > "$wt/INDEX.new"; mv "$wt/INDEX.new" "$wt/INDEX.md"
git -C "$wt" add -A
git -C "$wt" -c user.name="github-actions[bot]" -c user.email="41898282+github-actions[bot]@users.noreply.github.com" commit -q -m "ops-log: $id $result"
for try in 1 2 3; do
  if git -C "$wt" push -q origin HEAD:refs/heads/ops-log; then echo "report published: ops-log/$file ($result)"; exit 0; fi
  echo "push to ops-log rejected (try $try); replaying on top of the newer log" >&2
  git -C "$wt" fetch -q --depth=50 origin ops-log && git -C "$wt" rebase -q FETCH_HEAD || { git -C "$wt" rebase --abort 2>/dev/null; sleep 5; }
done
echo "could not publish the report to ops-log (it is still in the run summary)" >&2; exit 1

# ref: ./run-op.sh — writes the report; ./request.sh — reads it back from ops-log
