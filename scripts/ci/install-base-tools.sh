#!/usr/bin/env bash
# GitHub Actions: the small tools every site operation needs on a stock ubuntu-24.04 runner (jq, curl, rsync, docker and shellcheck are there).
#   age 1.3.2   encrypts the off-site backups (pinned download, checked against the release's published sha256)
#   ruff        the Python gate in check-all.sh (same version as on Ryan's computer)
set -euo pipefail

AGE_VERSION=v1.3.2
AGE_SHA256=cbe24006683f8eb669266162894b9a522a1af52f2665fbc63a4bb032ed26ac10   # github.com/FiloSottile/age release asset digest, read 2026-10-23
RUFF_VERSION=0.17.2

bin="${RUNNER_TEMP:-$HOME/.cache}/bin"; mkdir -p "$bin"
if ! "$bin/age" --version >/dev/null 2>&1; then
  t=$(mktemp -d)
  # GitHub's release download can answer 504 for a few minutes (2026-11-08: every run failed here for ~10 minutes); retry before giving up.
  # The sha256 check below still decides whether the file is the right one.
  curl -fsSL --retry 8 --retry-delay 10 --retry-all-errors --connect-timeout 20 -o "$t/age.tgz" "https://github.com/FiloSottile/age/releases/download/$AGE_VERSION/age-$AGE_VERSION-linux-amd64.tar.gz"
  echo "$AGE_SHA256  $t/age.tgz" | sha256sum -c --quiet -
  tar -xzf "$t/age.tgz" -C "$t" && install -m 755 "$t/age/age" "$bin/age"; rm -rf "$t"
fi
command -v ruff >/dev/null || pipx install --quiet "ruff==$RUFF_VERSION" >/dev/null
command -v rsync >/dev/null || { sudo apt-get update -qq >/dev/null && sudo apt-get install -y -qq rsync >/dev/null; }
[ -z "${GITHUB_PATH:-}" ] || echo "$bin" >> "$GITHUB_PATH"
echo "age $("$bin/age" --version), $(ruff --version 2>/dev/null || echo 'ruff (on PATH next step)')"

# ref: ./offsite-store.sh — uses age; ../check-all.sh — uses ruff
