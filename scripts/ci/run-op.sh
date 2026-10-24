#!/usr/bin/env bash
# Run ONE site operation in GitHub Actions from a request file, and write a report for whoever asked (a cloud session reads it from the
# ops-log branch; Ryan sees it as the run's summary). The request format is described in ops/README.md.
#
#   run-op.sh REQUEST.json LANE REPORT.md        LANE: ops  = site-ops.yml (staging writes and read-only work; live writes refused)
#                                                       live = site-live.yml (live writes only; run from main, typed confirmation)
# Rules enforced here (the workflow files and GitHub's environment settings enforce the rest, see CLOUD-SETUP.md):
#   - a write (--apply or --rollback) is only ever a push-*.sh script; nothing under cleanup/ and no plugin/zip/version tool runs at all
#     (deletions stay with Ryan, asked twice); live writes only in the live lane, with CONFIRM equal to the request id, from main
#   - a request id that already has a report is not run again (a re-pushed branch must not repeat an apply)
#   - after a write the routine runs: post-change-test.sh and prove-restore.sh on the same target (skip with "tests": "none")
# Exit 0 = the operation succeeded; 1 = it failed; 2 = the request was refused or could not run (INVALID).
set -uo pipefail

here=$(cd "$(dirname "$0")" && pwd)
root=$(cd "$here/../.." && pwd)
wwm="$root/scripts/work-with-me-employer"
req="${1:?usage: run-op.sh REQUEST.json ops|live REPORT.md}"; lane="${2:?}"; report="${3:?}"
out="${RUNNER_TEMP:-$HOME/.cache}/ops-out"; mkdir -p "$out"
started=$(date -u '+%Y-%m-%d %H:%M:%S UTC')

# ---- report helpers --------------------------------------------------------------------------------------------------------------------
mask() { # the SSH host and user never go into a report (GitHub masks them in its log; the report is a file)
  local h u e=(-E -e 's/u[0-9]{3,6}-[a-z0-9]{6,}/<ssh-user>/g')
  h=$(sed -n 's/^Host:[[:space:]]*//p' "${S26_CREDS:-/dev/null}" 2>/dev/null | head -1)
  u=$(sed -n 's/^User:[[:space:]]*//p' "${S26_CREDS:-/dev/null}" 2>/dev/null | head -1); u=${u%%@*}
  [ -z "$h" ] || e+=(-e "s/${h//./\\.}/<ssh-host>/g")
  [ -z "$u" ] || e+=(-e "s/${u//./\\.}/<ssh-user>/g")
  sed "${e[@]}"
}
results=()
head_md() {
  {
    echo "# Site op \`$id\`"
    echo
    echo "| | |"; echo "|---|---|"
    echo "| operation | \`$op\` ${target:+on \`$target\`} |"
    [ -z "$script" ] || echo "| script | \`$script\` ${args[*]:+\`${args[*]}\`} |"
    [ -z "$installer" ] || echo "| installer | \`$installer\` |"
    [ -z "$query" ] || echo "| query | \`$query\` ${qarg:+\`$qarg\`} |"
    echo "| lane | $lane ($([ "$lane" = live ] && echo 'site-live.yml, from main' || echo 'site-ops.yml')) |"
    echo "| commit | \`${GITHUB_SHA:-local}\` on \`${GITHUB_REF_NAME:-local}\` |"
    echo "| run | ${GITHUB_SERVER_URL:-}/${GITHUB_REPOSITORY:-}/actions/runs/${GITHUB_RUN_ID:-} |"
    echo "| started | $started |"
    echo
  } > "$report"
}
step() { # step NAME KIND COMMAND...   KIND: main (decides the result) or info (reported, never decides it)
  local name=$1 kind=$2; shift 2
  local log; log="$out/$(printf '%02d' "${#results[@]}")-$(tr -c 'a-z0-9' '-' <<<"$name" | sed 's/-*$//').log"
  echo "::group::$name"
  "$@" 2>&1 | tee "$log.raw"; local rc=${PIPESTATUS[0]}
  mask < "$log.raw" > "$log"; rm -f "$log.raw"   # the kept log (run artifact) is masked like the report
  echo "::endgroup::"
  local verdict; if [ "$rc" -eq 0 ]; then verdict=PASS; elif [ "$kind" = info ]; then verdict="exit $rc (information, see below)"; else verdict="FAIL (exit $rc)"; fi
  results+=("$kind|$rc|$name")
  {
    echo "## $name: $verdict"
    echo
    local n; n=$(wc -l < "$log")
    [ "$n" -le 400 ] || echo "_(last 400 of $n lines; the whole log is in the run's artifact)_"
    echo '```text'; tail -n 400 "$log" | sed 's/```/` ` `/g'; echo '```'
    echo
  } >> "$report"
  return "$rc"
}
finish() { # finish CODE MESSAGE
  local code=$1 msg=$2 word
  case "$code" in 0) word=PASS ;; 2) word=INVALID ;; *) word=FAIL ;; esac
  { echo "**RESULT: $word** - $msg"; echo; echo "_finished $(date -u '+%Y-%m-%d %H:%M:%S UTC')_"; } >> "$report"
  echo "RESULT: $word - $msg"
  exit "$code"
}
refuse() { [ -s "$report" ] || { id=${id:-unknown}; op=${op:-?}; target=${target:-}; script=${script:-}; installer=${installer:-}; query=${query:-}; head_md; }; finish 2 "refused: $*"; }

# ---- read and check the request ----------------------------------------------------------------------------------------------------
jq -e 'type == "object"' "$req" >/dev/null 2>&1 || { id=unknown op='?' target="" script="" installer="" query="" qarg=""; args=(); refuse "the request is not a JSON object"; }
j() { jq -r --arg k "$1" '.[$k] // empty | tostring' "$req"; }
id=$(j id); op=$(j op); target=$(j target); script=$(j script); installer=$(j installer); query=$(j query); qarg=$(j arg); tests=$(j tests)
mapfile -t args < <(jq -r '(.args // []) | if type == "array" then .[] else error("args must be an array") end' "$req" 2>/dev/null)
head_md
jq -e '(.args // []) | type == "array"' "$req" >/dev/null || refuse "args must be a JSON array of strings"
[[ "$id" =~ ^[a-z0-9][a-z0-9-]{2,59}$ ]] || refuse "id must be 3-60 characters of a-z, 0-9 and '-'"
case "$op" in run|rehearse|inspect|backup) ;; *) refuse "op must be run, rehearse, inspect or backup" ;; esac
case "$target" in ''|live|staging[0-9]|staging[0-9][0-9]) ;; *) refuse "target must be live or stagingNN" ;; esac
case "${tests:-auto}" in auto|none) ;; *) refuse "tests must be auto or none" ;; esac
# One pair of matching quotes around an argument is shell quoting copied along with it (AGENTS.md writes rollbacks as
# --rollback '$HOME/private-backups/x.json'): take it off, then check. Quotes anywhere else are still refused.
for i in "${!args[@]}"; do
  a=${args[$i]}
  if [ "${#a}" -ge 2 ] && { [[ "$a" == \'*\' ]] || [[ "$a" == \"*\" ]]; }; then a=${a:1:${#a}-2}; args[i]=$a; fi
  [[ "$a" =~ ^[A-Za-z0-9_.,:=@/\$+-]{1,200}$ ]] && [[ "$a" != *..* ]] || refuse "argument '$a' has characters outside the allowed set"
done
write=0; for a in "${args[@]}"; do case "$a" in --apply|--rollback) write=1 ;; esac; done

if [ "$lane" = live ]; then
  [ "${GITHUB_REF:-}" = refs/heads/main ] || refuse "the live lane runs only from main"
  [ "$op" = run ] && [ "$target" = live ] && [ "$write" = 1 ] || refuse "the live lane is only for writes (run, target live, --apply or --rollback)"
  [ "${CONFIRM:-}" = "$id" ] || refuse "the typed confirmation '${CONFIRM:-}' is not the request id '$id'"
elif [ "$lane" = ops ]; then
  [ "$write" = 0 ] || [ "$target" != live ] || refuse "live writes go only through 'Site ops (LIVE)' from main (see ops/README.md)"
else
  refuse "unknown lane '$lane'"
fi

# a request id runs once (the report on ops-log is the record). A report that says "refused:" means the request was
# stopped by these checks before anything ran, so it does not use the id up (2026-10-24: a mistyped live confirmation
# blocked the live request's own id).
if git -C "$root" fetch -q --depth=1 origin ops-log 2>/dev/null; then
  while IFS= read -r p; do
    git -C "$root" show "FETCH_HEAD:$p" 2>/dev/null | grep -q 'RESULT: INVALID\*\* - refused:' && continue
    refuse "request id '$id' has already run ($p on the ops-log branch); give the request a new id to run it again"
  done < <(git -C "$root" ls-tree -r --name-only FETCH_HEAD -- reports 2>/dev/null | grep -- "-$id\.md$")
fi

need_browsers() { "$here/install-test-tools.sh" --browsers >/dev/null || finish 2 "could not install the browser test tools"; export TS_PLAYWRIGHT_ROOT="$HOME/.cache/ts-playwright/package.json"; }

case "$op" in
  run)
    [ -n "$script" ] || refuse "run needs a script"
    [ -n "$target" ] || refuse "run needs a target (it becomes S_TARGET)"
    if [ "$script" = check-all.sh ]; then path="$root/scripts/check-all.sh"
    else
      [[ "$script" =~ ^([a-z0-9_-]+/)*[a-z0-9_.-]+\.(sh|py|mjs)$ ]] && [[ "$script" != *..* ]] || refuse "script must be a path under scripts/work-with-me-employer"
      path="$wwm/$script"
    fi
    [ -f "$path" ] || refuse "no such script: $script"
    base=$(basename "$script")
    case "$script" in cleanup/*) refuse "cleanup/ tools delete things; Ryan runs those himself (asked twice)" ;; esac
    case "$base" in mirror-versions.sh|secure-backups.sh|secure-backups-staging31.sh|remove-*|quarantine-*|php-runtime-test.sh) refuse "$base changes plugins, files or PHP; not available through GitHub" ;; esac
    if [ "$write" = 1 ]; then case "$base" in push-*.sh) ;; *) refuse "only push-*.sh scripts may take --apply/--rollback" ;; esac; fi
    case "$base" in *.mjs|post-change-test.sh) need_browsers ;; esac
    export S_TARGET="$target"
    case "$path" in
      *.sh)  runner=(bash "$path") ;;
      *.py)  runner=(python3 -I -B "$path") ;;
      *.mjs) runner=(node "$path") ;;
    esac
    step "$base ${args[*]:-}(S_TARGET=$target)" main "${runner[@]}" "${args[@]}"; rc=$?
    if [ "$rc" -ne 0 ]; then finish 1 "$base failed (exit $rc)"; fi
    if [ "$write" = 1 ] && [ "${tests:-auto}" = auto ]; then
      need_browsers
      step "post-change-test.sh $target (pages, structured data, 20 pixel comparisons, PHP log)" info bash "$wwm/post-change-test.sh" "$target"
      step "prove-restore.sh $target (the export taken before the change restores; what moved since)" info bash "$wwm/prove-restore.sh" "$target"
      finish 0 "$base succeeded; read the two test sections: differences are expected only where the change is"
    fi
    finish 0 "$base succeeded" ;;
  rehearse)
    [ -n "$installer" ] || refuse "rehearse needs an installer"
    up=(); for a in "${args[@]}"; do [ "$a" = --with-uploads ] && up=(--with-uploads); done
    step "rehearsal of $installer on a throwaway copy of ${target:-staging36}" main bash "$here/rehearse.sh" "${target:-staging36}" "$installer" "${up[@]}"; rc=$?
    [ "$rc" -eq 0 ] && finish 0 "rehearsal passed"; [ "$rc" -eq 2 ] && finish 2 "rehearsal could not be set up"; finish 1 "rehearsal failed" ;;
  inspect)
    [ -n "$target" ] && [ -n "$query" ] || refuse "inspect needs a target and a query"
    step "inspect $target $query $qarg" main bash "$here/inspect.sh" "$target" "$query" "$qarg" || finish 1 "inspect failed"
    finish 0 "read-only inspect done" ;;
  backup)
    [ -n "$target" ] || refuse "backup needs a target"
    step "backup of $target (DB export on the server + encrypted off-site copy)" main bash "$here/backup-now.sh" "$target" || finish 1 "backup failed"
    finish 0 "backup taken and stored off-site" ;;
esac

# ref: ./rehearse.sh, ./inspect.sh, ./backup-now.sh — the other operations; ../work-with-me-employer/post-change-test.sh, ../work-with-me-employer/prove-restore.sh — the after-a-change routine
# ref: ../../ops/README.md — the request format; ../../.github/workflows/site-ops.yml, ../../.github/workflows/site-live.yml — the lanes
