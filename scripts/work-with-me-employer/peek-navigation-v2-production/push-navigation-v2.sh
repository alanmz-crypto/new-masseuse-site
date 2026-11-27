#!/usr/bin/env bash
# Production release candidate. Run only through Ryan's confirmed main workflow.
# shellcheck disable=SC2016
set -euo pipefail
die() { echo "push-navigation-v2: $*" >&2; exit 1; }
here=$(cd "$(dirname "$0")" && pwd)
repo=$(cd "$here/../../.." && pwd)
[[ "${S_TARGET:-}" = live && "${GITHUB_REF:-}" = refs/heads/main &&
   "${GITHUB_WORKFLOW_REF:-}" = "${GITHUB_REPOSITORY:?}/.github/workflows/site-live.yml@refs/heads/main" &&
   "${GITHUB_EVENT_NAME:-}" = workflow_dispatch && -n "${CONFIRM:-}" ]] || { echo 'Use the approved live workflow on main.' >&2; exit 1; }
args=$(printf '%s\n' "$@" | jq -R . | jq -s .)
jq -e --arg id "$CONFIRM" --argjson args "$args" '.op=="run" and .target=="live" and .id==$id and (.tests // "auto")=="auto" and .script=="peek-navigation-v2-production/push-navigation-v2.sh" and .args==$args' "$repo/ops/live-request.json" >/dev/null || die 'ops/live-request.json does not match this run (id, script, tests or arguments)'
case "${1:-}" in
  --apply) [[ $# = 1 ]] || die '--apply takes no further arguments'; operation=apply ;;
  --rollback) [[ $# = 3 && "$2" =~ ^/home/customer/private-backups/peek-v2-live-[0-9]+-[0-9]+/snapshots/peek-v2-before-[0-9]{8}-[0-9]{6}-[a-f0-9-]{36}\.json$ && "$3" =~ ^[a-f0-9]{64}$ ]] || die '--rollback needs the production snapshot path and its full SHA-256'; operation=rollback ;;
  *) die 'the argument must be --apply or --rollback SNAPSHOT SHA256' ;;
esac
shift
run="${GITHUB_RUN_ID:?}-${GITHUB_RUN_ATTEMPT:?}"
[[ "$run" =~ ^[0-9]+-[0-9]+$ ]] || die "run id '$run' is not <run>-<attempt>"
[[ -x "${TS_OFFSITE_HOOK:?}" ]] || die 'TS_OFFSITE_HOOK is not an executable program'
if [[ "$operation" = apply ]]; then
  bash "$repo/scripts/ci/install-test-tools.sh" --browsers
  export TS_PLAYWRIGHT_ROOT="$HOME/.cache/ts-playwright/package.json"
fi
work=$(mktemp -d); trap 'rm -rf "$work"' EXIT
python3 "$here/package.py" "$work/peek-v2.zip"
cp "$here/install-v2.php" "$here/remote-phase.sh" "$work/"
(cd "$work" && sha256sum peek-v2.zip install-v2.php remote-phase.sh >SHA256SUMS)
creds="${S26_CREDS:-$repo/remote_execution.txt}"
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); user=$(field User); port=$(field Port)
ssh_cmd=(ssh -o BatchMode=yes -o StrictHostKeyChecking=yes -o ConnectTimeout=20 -p "$port" "${user%%@*}@$host")
package="/home/customer/private-backups/peek-v2-live-$run"
"${ssh_cmd[@]}" "umask 077; test -d /home/customer/private-backups && test ! -L /home/customer/private-backups && mkdir '$package' && mkdir '$package/snapshots'"
tar -C "$work" -cf - SHA256SUMS peek-v2.zip install-v2.php remote-phase.sh | "${ssh_cmd[@]}" "umask 077; cd '$package' && tar -xf - && sha256sum -c SHA256SUMS"
remote() {
  local quoted
  printf -v quoted '%q ' "$1" "$operation" "$run" "${@:2}"
  "${ssh_cmd[@]}" "cd '$package' && sha256sum -c SHA256SUMS >/dev/null && bash remote-phase.sh $quoted"
}
preserve() {
  local digest=$1 file=$2 name=${2##*/} dest=${TS_SERVER_BACKUPS:?}
  [[ "$digest" =~ ^[a-f0-9]{64}$ ]] || return 1
  mkdir -p "$dest" && chmod 700 "$dest" || return 1
  "${ssh_cmd[@]}" "cat '$file'" >"$work/$name" || return 1
  [[ -s "$work/$name" && "$(sha256sum "$work/$name")" = "$digest  $work/$name" ]] || return 1
  [[ ! -e "$dest/$name" && ! -L "$dest/$name" ]] || return 1
  install -m 600 "$work/$name" "$dest/$name" || return 1
  "$TS_OFFSITE_HOOK" "$dest" "$name"
}
record=$(remote prepare)
dump="/home/customer/private-backups/live-before-peek-v2-$run.sql"
sha=${record%% *}; [[ "$record" = "$sha  $dump" ]] || die 'the remote export record is not "<sha256>  <expected dump path>"'
preserve "$sha" "$dump" || die "Database export could not be preserved off-site: $dump. The write did not start."
write_rc=0
remote write "$sha" "$@" || write_rc=$?
failed=0
have_snapshot=0
if [[ "$operation" = apply ]]; then
  if record=$(remote snapshot); then
    snap=${record#*  }; sha=${record%% *}
    if [[ "$record" = "$sha  $snap" && "$sha" =~ ^[a-f0-9]{64}$ && "${snap%/*}" = "$package/snapshots" && "${snap##*/}" =~ ^peek-v2-before-[0-9]{8}-[0-9]{6}-[a-f0-9-]{36}\.json$ ]]; then
      have_snapshot=1
      echo "Recovery snapshot $snap SHA-256 $sha"
      if ! preserve "$sha" "$snap"; then
        echo "Off-site snapshot copy FAILED: $snap SHA-256 $sha" >&2
        failed=1
      fi
    else
      echo "Invalid snapshot record. Inspect $package/snapshots." >&2
      failed=1
    fi
  else
    echo "Snapshot unavailable. Inspect $package/snapshots. Write exit $write_rc." >&2
    failed=1
  fi
fi

# After a write error, establish the actual code/cache state before choosing public checks.
verify_mode=$operation
if (( write_rc )); then
  echo "Write exited $write_rc. Inspecting stored state." >&2
  report=$(remote inspect) || { echo "State could not be established. Inspect $package before recovery." >&2; exit "$write_rc"; }
  echo "$report"
  case "$report" in
    *'state=applied '*) verify_mode=apply ;;
    *'state=ready '*) verify_mode=rollback ;;
    *) echo "Unknown preflight state. Inspect $package." >&2; exit "$write_rc" ;;
  esac
  # A refused apply before its first snapshot, or a refused rollback still on v2,
  # has no demonstrated state change. Leave caches alone and report the refusal.
  if [[ "$operation" = apply && "$have_snapshot" = 0 && "$verify_mode" = rollback ||
        "$operation" = rollback && "$verify_mode" = apply ]]; then exit "$write_rc"; fi
fi
if report=$(remote purge); then
  echo "$report"
  case "$verify_mode $report" in
    "apply "*'state=applied '*|"rollback "*'state=ready '*) ;;
    *) echo 'Unexpected stored state after purge.' >&2; failed=1 ;;
  esac
else
  echo 'Purge or stored-state verification failed. Checking public responses.' >&2
  failed=1
fi
python3 "$here/verify-public.py" "$verify_mode" || failed=1
if [[ "$verify_mode" = apply ]]; then
  node "$here/verify-browser.cjs" || failed=1
fi
if (( write_rc )); then exit "$write_rc"; fi
exit "$failed"
