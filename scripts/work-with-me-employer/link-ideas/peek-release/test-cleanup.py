"""Execute fixed release/title-resize cleanup profiles against local doubles.

Only temporary directories created by this test are removed. The actual Bash
bootstrap, cleanup functions and EXIT traps are extracted from rehearse.sh.
No Docker, network or real signals are used. Historical lanes remain separate.
"""
from pathlib import Path
import os
import subprocess
import tempfile


HERE = Path(__file__).resolve().parent
PROFILE = globals().get('CLEANUP_PROFILE', 'release')
assert PROFILE in ('release', 'title-resize'), 'Unknown fixed cleanup fixture profile'
NATIVE = 'peek-release-native' if PROFILE == 'release' else 'core-title-resize-native'
INSTALLER = 'link-ideas/peek-release/install-peek-release.php' if PROFILE == 'release' else 'link-ideas/core-title-resize/install-peek-core-title-resize.php'
SOURCE = (HERE.parents[3] / "scripts/ci/rehearse.sh").read_text()
BOOTSTRAP = SOURCE[SOURCE.index('work="${RUNNER_TEMP:-$HOME/.cache}/ts-rehearsal-$$"'):SOURCE.index("\nread -r -d '' REMOTE")]
RETAIN_NAME = 'release_cleanup' if PROFILE == 'release' else 'core_neighbours_cleanup'
RETAIN_START = SOURCE.index(RETAIN_NAME + '() {')
RETAIN_END = '\n\nif [ "$release_rehearsal"' if PROFILE == 'release' else '\nsearch_contrast_lane()'
RETAIN = SOURCE[RETAIN_START:SOURCE.index(RETAIN_END, RETAIN_START)]
PASSED = 0


PREFIX = r'''
set -uo pipefail
fixture=$1; fixture_mode=$2; fixture_prior=$3; release_rehearsal=$4; inst=$5
RUNNER_TEMP=$fixture/runner; cli_img=fixture-pinned-cli
command mkdir -p "$RUNNER_TEMP" "$fixture/victim"
printf '%s\n' "$$" > "$fixture/pid"
printf '%s\n' SYNTHETIC_PRIVATE_VICTIM > "$fixture/victim/keep"
fixture_remove_calls=0; fixture_alive=1
trace() { printf '%s\n' "$*" >> "$fixture/calls"; }
invalid() { printf 'INVALID fixture setup: %s\n' "$*" >&2; exit 2; }
mkdir() {
  if [ "$fixture_mode" = work-mkdir-fails ] && [[ "$*" == '-m 700 -- '* ]]; then return 73; fi
  if [ "$fixture_mode" = subdirectory-mkdir-fails ] && [[ "$*" == *'/public_html '* ]]; then return 74; fi
  if [ "$fixture_mode" = evidence-mkdir-fails ] && [[ "$*" == *'/ops-out/@@NATIVE@@' ]]; then return 75; fi
  command mkdir "$@"
}
cp() {
  trace "cp $*"
  [ "$fixture_mode" != evidence-copy-fails ] || return 76
  command cp "$@"
}
docker() {
  trace "docker $*"
  local resource=''
  case "${1:-}" in
    compose)
      [ "$*" = "compose --progress quiet -p $project -f $work/compose.yml down -v --remove-orphans" ] || return 91
      [ "$fixture_mode" != compose-fails ]; return $? ;;
    ps) resource=container ;;
    volume) resource=volume ;;
    network) resource=network ;;
    run)
      [[ " $* " == *' --rm '* ]] && [[ " $* " == *' --network none '* ]] &&
      [[ " $* " == *" --label com.docker.compose.project=$project "* ]] &&
      [[ " $* " == *' --label com.docker.compose.service=release-cleanup '* ]] &&
      [[ " $* " == *" -v $work:/w $cli_img "* ]] || return 92
      touch "$fixture/fallback-ran"
      [ "$fixture_mode" != rm-fallback-fails ]; return $? ;;
    *) return 93 ;;
  esac
  if [ -n "$resource" ]; then
    case "$resource" in
      container) [ "$*" = "ps --all --quiet --filter label=com.docker.compose.project=$project" ] || return 94 ;;
      *) [ "$*" = "$resource ls --quiet --filter label=com.docker.compose.project=$project" ] || return 94 ;;
    esac
    if [ "$fixture_mode" = "$resource-query-fails" ]; then printf 'SYNTHETIC_PRIVATE_DOCKER_ERROR\n' >&2; return 27; fi
    if [ "$fixture_mode" = "$resource-remains" ] || { [ "$fixture_mode" = "final-$resource-remains" ] && [ -f "$fixture/fallback-ran" ]; }; then
      printf '%s\n' SYNTHETIC_PRIVATE_RESOURCE_ID
    fi
  fi
}
rm() {
  trace "rm $*"
  fixture_remove_calls=$((fixture_remove_calls + 1))
  { [ "$*" = "-rf -- $work" ] || { [ -z "${owned_cleanup_component:-}" ] && [ "$*" = "-rf $work" ]; }; } || return 95
  if [ "$fixture_mode" = rm-remains ]; then return 0; fi
  case "$fixture_mode" in
    rm-fallback|rm-fallback-fails|final-container-remains|final-volume-remains|final-network-remains)
      [ "$fixture_remove_calls" -gt 1 ] && [ "$fixture_mode" != rm-fallback-fails ] || return 28 ;;
  esac
  command rm "$@"
}
kill() {
  trace "kill $*"
  [ "${2:-}" = 43210 ] || return 96
  case "$1" in
    -0) [ "$fixture_alive" = 1 ] ;;
    -TERM)
      case "$fixture_mode" in relay-needs-kill|relay-stubborn) ;; *) fixture_alive=0 ;; esac ;;
    -KILL) [ "$fixture_mode" = relay-stubborn ] || fixture_alive=0 ;;
    *) return 97 ;;
  esac
}
ps() {
  trace "ps $*"
  [ "$*" = '-o ppid= -p 43210' ] || return 98
  if [ "$fixture_mode" = relay-ps-fails ]; then return 29; fi
  if [ "$fixture_mode" = relay-foreign ]; then printf '%s\n' "$(( $$ + 1000 ))"; else printf '  %s \n' "$$"; fi
}
wait() { trace "wait $*"; [ "$*" = 43210 ] || return 99; return 143; }
sleep() { trace "sleep $*"; }
if [ "$fixture_mode" = setup-collision ]; then
  command mkdir "$RUNNER_TEMP/ts-rehearsal-$$"
  printf '%s\n' SYNTHETIC_PRIVATE_COLLISION > "$RUNNER_TEMP/ts-rehearsal-$$/keep"
elif [ "$fixture_mode" = setup-symlink ]; then
  ln -s "$fixture/victim" "$RUNNER_TEMP/ts-rehearsal-$$"
fi
'''.replace('@@NATIVE@@', NATIVE)

BODY = r'''
printf '%s\n' reached > "$fixture/body-ran"
if [ "$fixture_mode" != no-compose ] && [ "$fixture_mode" != early-source-fails ]; then
  printf '%s\n' 'SYNTHETIC_PRIVATE_COMPOSE_CONFIG' > "$work/compose.yml"
fi
printf '%s\n' SYNTHETIC_PRIVATE_SQL > "$work/private.sql"
printf '%s\n' SYNTHETIC_PRIVATE_SNAPSHOT > "$bk/snapshot.json"
printf '%s\n' SYNTHETIC_PRIVATE_SOURCE > "$pub/wp-config.php"
case "$fixture_mode" in
  relay-stop|relay-needs-kill|relay-stubborn|relay-foreign|relay-ps-fails) release_relay_pid=43210 ;;
  relay-already-gone) release_relay_pid=43210; fixture_alive=0 ;;
  relay-invalid) release_relay_pid=invalid ;;
  wrong-project) project=other-project ;;
  wrong-work) work="$fixture/victim" ;;
  wrong-parent) release_work_parent="$fixture/victim" ;;
  unowned) release_work_owned=0 ;;
  work-symlink) command rm -rf -- "$work"; ln -s "$fixture/victim" "$work" ;;
  compose-symlink) command rm -- "$work/compose.yml"; ln -s "$fixture/victim/keep" "$work/compose.yml" ;;
  compose-directory) command rm -- "$work/compose.yml"; command mkdir "$work/compose.yml" ;;
esac
'''


def run_case(mode="normal", prior=0, expected=0, retain=False, release=True):
    global PASSED
    with tempfile.TemporaryDirectory(prefix="peek-" + PROFILE + "-cleanup-") as directory:
        base = Path(directory)
        body = BODY
        if retain:
            body += r'''
for name in apply.log apply.stderr.log A.content errors-before.txt original-before-navigation.jsonl title-strip-pages.json release-paths.txt; do
  printf '%s\n' "PUBLIC fixture $name" > "$out/$name"
done
for name in private.sql snapshot.json release-original-page.html; do printf '%s\n' SYNTHETIC_PRIVATE_EXCLUDED > "$out/$name"; done
'''
            body += RETAIN + "\ntrap " + RETAIN_NAME + " EXIT\n"
        body += '\nexit "$fixture_prior"\n'
        env = dict(os.environ)
        env.pop("BASH_ENV", None)
        # Legacy controls deliberately use the historical quiet lane rather than
        # the new title-resize path, even when that is this fixture's profile.
        installer = INSTALLER if release else 'link-ideas/core-quiet-nav/install-peek-core-quiet-nav.php'
        release_flag = '1' if release and PROFILE == 'release' else '0'
        result = subprocess.run(["bash", "--noprofile", "--norc", "-s", "--", directory, mode, str(prior), release_flag, installer],
                                input=PREFIX + "\n" + BOOTSTRAP + "\n" + body,
                                env=env, capture_output=True, text=True, timeout=10)
        assert result.returncode == expected, (mode, prior, result.returncode, result.stdout, result.stderr)
        pid = (base / "pid").read_text().strip()
        work = base / "runner" / ("ts-rehearsal-" + pid)
        calls = (base / "calls").read_text().splitlines() if (base / "calls").exists() else []
        evidence = base / "runner/ops-out" / NATIVE
        log = evidence / "cleanup.log"
        assert (base / "victim/keep").read_text() == "SYNTHETIC_PRIVATE_VICTIM\n", "unowned path changed"
        guard_modes = {"wrong-project", "wrong-work", "wrong-parent", "unowned", "work-symlink", "setup-collision", "setup-symlink", "work-mkdir-fails"}
        retained_modes = guard_modes | {"relay-stubborn", "relay-foreign", "relay-invalid", "relay-ps-fails", "compose-symlink", "compose-directory", "compose-fails", "rm-remains", "rm-fallback-fails"}
        retained_modes |= {f"{kind}-{state}" for kind in ("container", "volume", "network") for state in ("query-fails", "remains")}
        if mode in retained_modes and mode != "work-mkdir-fails":
            assert work.exists() or work.is_symlink(), (mode, "unproved copy must remain inside runner")
        else:
            assert not work.exists() and not work.is_symlink(), (mode, "owned directory not removed")
        if mode in guard_modes:
            assert not any(call.startswith(("docker ", "kill ", "ps ", "wait ", "rm ")) for call in calls), (mode, "mutation without ownership", calls)
        if mode.startswith("setup-") or mode in {"work-mkdir-fails", "subdirectory-mkdir-fails"}:
            assert not (base / "body-ran").exists(), "setup refusal continued to copy phase"
        if mode in {"relay-foreign", "relay-invalid", "relay-ps-fails"}:
            assert not any(call.startswith(("kill -TERM", "kill -KILL")) for call in calls), "unowned relay signalled"
        if mode == "relay-stop":
            assert "kill -TERM 43210" in calls and "wait 43210" in calls and "kill -KILL 43210" not in calls
        if mode == "relay-already-gone":
            assert "wait 43210" in calls and "kill -TERM 43210" not in calls
        if mode == "relay-needs-kill":
            assert "kill -TERM 43210" in calls and "kill -KILL 43210" in calls and "wait 43210" in calls
        if mode in {"compose-symlink", "compose-directory", "no-compose", "early-source-fails", "subdirectory-mkdir-fails"}:
            assert not any(call.startswith("docker compose ") for call in calls), "unexpected Compose access"
        queries = [call for call in calls if call.startswith(("docker ps ", "docker volume ls ", "docker network ls "))]
        assert all(call.endswith("--filter label=com.docker.compose.project=ts-rehearsal-" + pid) for call in queries), "unscoped Docker lookup"
        assert not any("prune" in call for call in calls), "global Docker cleanup is forbidden"
        if not release:
            assert not log.exists() and not queries, "legacy lane entered release cleanup"
        elif mode != "evidence-mkdir-fails":
            assert log.exists() and log.stat().st_mode & 0o777 == 0o600, "missing private-mode cleanup evidence"
            text = log.read_text()
            clean = expected == prior and mode not in retained_modes and mode != "evidence-copy-fails"
            if clean:
                assert "PASSED; owned relay, containers, volumes, networks and temporary directory absent" in text, (mode, text)
                assert len(queries) == 6, "absence must be checked before and after removal"
            else:
                assert "FAIL" in text or mode == "evidence-copy-fails", (mode, text)
        if evidence.exists():
            for path in evidence.iterdir():
                assert "SYNTHETIC_PRIVATE" not in path.read_text(), (mode, "private copied data leaked into evidence", path.name)
            if retain and mode != "evidence-copy-fails":
                expected_files = {"cleanup.log", "apply.log", "apply.stderr.log", "A.content", "errors-before.txt", "title-strip-pages.json"}
                if PROFILE == 'release':
                    expected_files |= {"original-before-navigation.jsonl", "release-paths.txt"}
                assert {p.name for p in evidence.iterdir()} == expected_files
        PASSED += 1


for prior in (0, 1, 2, 7):
    run_case(prior=prior, expected=prior)
    run_case(prior=prior, expected=prior, retain=True)
for mode in ("no-compose", "relay-stop", "relay-already-gone", "relay-needs-kill", "rm-fallback"):
    run_case(mode)
for mode in ("compose-fails", "relay-stubborn", "relay-foreign", "relay-invalid", "relay-ps-fails",
             "compose-symlink", "compose-directory", "rm-remains", "rm-fallback-fails", "evidence-mkdir-fails",
             "wrong-project", "wrong-work", "wrong-parent", "unowned", "work-symlink",
             "container-query-fails", "volume-query-fails", "network-query-fails",
             "container-remains", "volume-remains", "network-remains",
             "final-container-remains", "final-volume-remains", "final-network-remains"):
    run_case(mode, expected=1)
for mode in ("compose-fails", "container-query-fails", "rm-remains", "evidence-mkdir-fails"):
    run_case(mode, prior=2, expected=2)
for prior in (0, 2, 7):
    run_case("evidence-copy-fails", prior=prior, expected=prior or 1, retain=True)
for mode in ("setup-collision", "setup-symlink", "work-mkdir-fails", "subdirectory-mkdir-fails", "early-source-fails"):
    run_case(mode, prior=2, expected=2)
for prior in (0, 2):
    run_case(prior=prior, expected=prior, release=False)
print(f"Peek {PROFILE} cleanup: {PASSED} offline ownership, teardown, status and evidence controls passed; no Docker, network or real signals.")
