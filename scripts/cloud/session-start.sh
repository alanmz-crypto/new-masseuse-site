#!/usr/bin/env bash
# SessionStart hook (.claude/settings.json). Does nothing outside a cloud session. In a cloud session (claude.ai/code, the Claude app) it
# makes `scripts/check-all.sh` complete: shellcheck and ruff, and Docker running with the pinned wordpress:cli image for the PHP 8.5 lint.
# Never fails the session: anything that cannot be installed is reported and skipped (check-all.sh then prints SKIP for that gate).
[ "${CLAUDE_CODE_REMOTE:-}" = true ] || exit 0
cd "${CLAUDE_PROJECT_DIR:-$(dirname "$0")/../..}" || exit 0
as_root() { if [ "$(id -u)" -eq 0 ]; then "$@"; else sudo -n "$@"; fi; }
note=()

command -v shellcheck >/dev/null || { as_root apt-get update -qq >/dev/null 2>&1; as_root apt-get install -y -qq shellcheck >/dev/null 2>&1; } || true
command -v shellcheck >/dev/null || note+=("shellcheck not installed")
command -v ruff >/dev/null || pip install --quiet ruff==0.17.2 >/dev/null 2>&1 || pipx install --quiet ruff==0.17.2 >/dev/null 2>&1 || true
command -v ruff >/dev/null || note+=("ruff not installed")

if command -v docker >/dev/null; then
  if ! docker info >/dev/null 2>&1; then
    (as_root dockerd >/tmp/dockerd.log 2>&1 &) ; for _ in $(seq 1 20); do docker info >/dev/null 2>&1 && break; sleep 1; done
  fi
  img=$(sed -n 's/^[[:space:]]*image:[[:space:]]*\(wordpress:cli[^[:space:]]*\).*/\1/p' docker-compose.yml | head -1)
  if docker info >/dev/null 2>&1 && [ -n "$img" ]; then (docker pull -q "$img" >/dev/null 2>&1 &); else note+=("docker not running: the PHP lint gate will SKIP"); fi
else note+=("no docker: the PHP lint gate will SKIP"); fi

echo "Cloud session: no SSH here. Server work goes through GitHub Actions: scripts/ci/request.sh (guide: ops/README.md; rules: CLAUDE.md)."
[ "${#note[@]}" -eq 0 ] || printf 'Setup notes: %s\n' "${note[*]}"
exit 0

# ref: ../../.claude/settings.json — registers this hook; ../check-all.sh — the gates it prepares for; ../../CLAUDE.md — the cloud routine
