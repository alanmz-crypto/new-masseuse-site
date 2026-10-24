#!/usr/bin/env bash
# Ask GitHub Actions to do server work, then read its report. For sessions that cannot reach SiteGround themselves (a cloud session in
# claude.ai/code or the Claude app); it works from any clone that can push a branch.
#
#   request.sh rehearse --installer thai/install-x.php [--source staging36] [--with-uploads]   throwaway copy: apply, re-apply, rollback, re-apply
#   request.sh run --target staging36 --script thai/push-x.sh [--args "--apply"] [--no-tests]  a push script (dry run without --args) or a read-only tool
#   request.sh inspect --target live --query page --arg thai-massage                           read-only: page|seo|snippets|snippet|css|option|plugins|fingerprint
#   request.sh backup --target staging36                                                        DB export now + encrypted off-site copy
#   request.sh live --script thai/push-x.sh --args "--apply"                                    PREPARE a live write for Ryan (writes ops/live-request.json; runs nothing)
#   request.sh wait ID [MINUTES]      wait for ID's report (default 45 minutes) and print it; exit 0 PASS, 1 FAIL, 2 INVALID, 3 still waiting
#   request.sh show ID                print ID's report if there is one
#   request.sh list [N]               the newest N reports (default 15)
# Common options: --id ID (default: <op>-<UTC time>), --no-wait (commit and push, do not wait).
#
# How it works: the request is written to ops/request.json, committed on the CURRENT branch and pushed; the push starts
# .github/workflows/site-ops.yml, which runs the request (scripts/ci/run-op.sh) and commits its report to the ops-log branch.
# A live write cannot be started this way: `live` only writes the request; Ryan merges it to main and starts "Site ops (LIVE)" himself.
set -euo pipefail

root=$(git rev-parse --show-toplevel)
push_to="${TS_PUSH_REMOTE:-origin}"   # on Ryan's computer origin pushes over SSH (passphrase): TS_PUSH_REMOTE=https://github.com/alanmz-crypto/new-masseuse-site.git
cmd="${1:-}"; [ -n "$cmd" ] && shift || true
id=""; target=""; script=""; installer=""; query=""; qarg=""; args_s=""; tests="auto"; source_site="staging36"; uploads=0; wait=1

die() { echo "request.sh: $*" >&2; exit 2; }
list_reports() { # every report path for ID on ops-log, oldest first (reports/YYYY-MM-DD/HHMMSS-<id>.md sorts by time)
  git -C "$root" fetch -q origin ops-log 2>/dev/null || return 1
  git -C "$root" ls-tree -r --name-only FETCH_HEAD -- reports 2>/dev/null | grep -- "-$1\.md$"
}
find_report() { list_reports "$1" | tail -1; }   # the newest
id_used() { # true if ID has a report that is not a refusal (a refused request ran nothing; run-op.sh applies the same rule)
  local p
  while IFS= read -r p; do
    [ -n "$p" ] || continue
    git -C "$root" show "FETCH_HEAD:$p" 2>/dev/null | grep -q 'RESULT: INVALID\*\* - refused:' || return 0
  done < <(list_reports "$1")
  return 1
}
show() {
  local p; p=$(find_report "$1") && [ -n "$p" ] || return 1
  git -C "$root" show "FETCH_HEAD:$p"
}
result_code() { case "$(grep -o 'RESULT: [A-Z]*' <<<"$1" | tail -1)" in "RESULT: PASS") echo 0 ;; "RESULT: INVALID") echo 2 ;; *) echo 1 ;; esac; }
wait_for() { # wait_for ID MINUTES [OLD]   OLD = report paths that existed when the request was sent (an earlier refusal): not the answer
  local i=$1 mins=${2:-45} old=${3:-} t0 r p
  t0=$(date +%s)
  echo "waiting for the report of '$i' on ops-log (up to $mins minutes; the run itself: GitHub > Actions > Site ops) ..."
  while :; do
    p=$(find_report "$i" || true)
    if [ -n "$p" ] && ! grep -qxF -- "$p" <<<"$old"; then r=$(git -C "$root" show "FETCH_HEAD:$p"); printf '%s\n' "$r"; return "$(result_code "$r")"; fi
    [ $(( $(date +%s) - t0 )) -lt $(( mins * 60 )) ] || { echo "no report for '$i' after $mins minutes: the run may still be going, or it never started (check the Actions tab); request.sh wait $i continues waiting" >&2; return 3; }
    sleep 20
  done
}

while [ $# -gt 0 ]; do
  case "$1" in
    --id) id=$2; shift 2 ;;
    --target) target=$2; shift 2 ;;
    --script) script=$2; shift 2 ;;
    --installer) installer=$2; shift 2 ;;
    --query) query=$2; shift 2 ;;
    --arg) qarg=$2; shift 2 ;;
    --args) args_s=$2; shift 2 ;;
    --source) source_site=$2; shift 2 ;;
    --with-uploads) uploads=1; shift ;;
    --no-tests) tests=none; shift ;;
    --no-wait) wait=0; shift ;;
    *) break ;;
  esac
done

case "$cmd" in
  wait) [ -n "${1:-}" ] || die "wait needs an ID"; wait_for "$1" "${2:-45}"; exit $? ;;
  show) [ -n "${1:-}" ] || die "show needs an ID"; show "$1" || { echo "no report for '$1' yet"; exit 3; }; exit 0 ;;
  list) git -C "$root" fetch -q origin ops-log 2>/dev/null || { echo "no ops-log branch yet (no operation has run)"; exit 0; }
        git -C "$root" show FETCH_HEAD:INDEX.md | head -n $(( ${1:-15} + 2 )); exit 0 ;;
  run|rehearse|inspect|backup|live) ;;
  *) sed -n '2,20p' "$0" | sed 's/^# \{0,1\}//'; exit 2 ;;
esac

id=${id:-$cmd-$(date -u +%Y%m%d-%H%M%S)}
[[ "$id" =~ ^[a-z0-9][a-z0-9-]{2,59}$ ]] || die "id must be 3-60 characters of a-z, 0-9 and '-'"
read -r -a args <<<"$args_s"
args_json=$(printf '%s\n' "${args[@]}" | jq -R . | jq -s 'map(select(length > 0))')
case "$cmd" in
  run)      [ -n "$target" ] && [ -n "$script" ] || die "run needs --target and --script"
            json=$(jq -n --arg id "$id" --arg t "$target" --arg s "$script" --argjson a "$args_json" --arg tests "$tests" '{id:$id, op:"run", target:$t, script:$s, args:$a, tests:$tests}') ;;
  rehearse) [ -n "$installer" ] || die "rehearse needs --installer"
            a='[]'; [ "$uploads" = 1 ] && a='["--with-uploads"]'
            json=$(jq -n --arg id "$id" --arg t "$source_site" --arg i "$installer" --argjson a "$a" '{id:$id, op:"rehearse", target:$t, installer:$i, args:$a}') ;;
  inspect)  [ -n "$target" ] && [ -n "$query" ] || die "inspect needs --target and --query"
            json=$(jq -n --arg id "$id" --arg t "$target" --arg q "$query" --arg x "$qarg" '{id:$id, op:"inspect", target:$t, query:$q} + (if $x == "" then {} else {arg:$x} end)') ;;
  backup)   [ -n "$target" ] || die "backup needs --target"
            json=$(jq -n --arg id "$id" --arg t "$target" '{id:$id, op:"backup", target:$t}') ;;
  live)     [ -n "$script" ] || die "live needs --script"
            case " $args_s " in *" --apply "*|*" --rollback "*) ;; *) die "live is only for writes (--args \"--apply\" or \"--rollback FILE\"); a live DRY RUN goes through: run --target live" ;; esac
            id=${id/#live-live-/live-}; [[ "$id" == live-* ]] || id="live-$id"
            json=$(jq -n --arg id "$id" --arg s "$script" --argjson a "$args_json" '{id:$id, op:"run", target:"live", script:$s, args:$a, tests:"auto"}') ;;
esac

branch=$(git -C "$root" rev-parse --abbrev-ref HEAD)
[ "$branch" != HEAD ] || die "not on a branch"
[ "$branch" != main ] || die "refusing to commit a request on main; work on a branch"
[ "$branch" != ops-log ] || die "refusing to commit on ops-log"
if id_used "$id"; then die "id '$id' has already run; choose another --id"; fi
old_reports=$(list_reports "$id" 2>/dev/null || true)   # earlier refusals of this id (if any): the wait below ignores them

if [ "$cmd" = live ]; then
  printf '%s\n' "$json" | jq . > "$root/ops/live-request.json"
  git -C "$root" add ops/live-request.json
  git -C "$root" commit -q -m "ops: prepare live request $id ($script ${args_s})" -- ops/live-request.json
  git -C "$root" push -q "$push_to" "HEAD:refs/heads/$branch"
  cat <<TXT
Prepared live request '$id' on branch $branch (ops/live-request.json):
$(jq . "$root/ops/live-request.json")

Nothing has run. For Ryan, two steps (phone is fine):
  1. Merge the pull request of branch $branch into main (it carries this request and the change it applies).
  2. GitHub > Actions > "Site ops (LIVE)" > Run workflow > branch main > confirm: $id
Then: scripts/ci/request.sh wait $id 1440
TXT
  exit 0
fi

printf '%s\n' "$json" | jq . > "$root/ops/request.json"
git -C "$root" add ops/request.json
git -C "$root" commit -q -m "ops: $id ($cmd ${target:-$source_site} ${script}${installer}${query:+ $query $qarg} ${args_s})" -- ops/request.json
git -C "$root" push -q "$push_to" "HEAD:refs/heads/$branch"
echo "requested '$id' (pushed $branch at $(git -C "$root" rev-parse --short HEAD)); the push starts the Site ops workflow"
[ "$wait" = 1 ] || exit 0
wait_for "$id" 45 "$old_reports"

# ref: ../../ops/README.md — the request format; ./run-op.sh — what runs it; ./publish-report.sh — where the report goes
