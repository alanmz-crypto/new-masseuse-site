#!/usr/bin/env bash
# Run after rehearse.py on its disposable stack. SSH and HTTPS go only to Docker.
set -euo pipefail
here=$(cd "$(dirname "$0")/.." && pwd)
repo=$(cd "$here/../../.." && pwd)
export STACK
STACK=$(cd "${1:?supply the stack directory}" && pwd)
work=$(mktemp -d); trap 'rm -rf "$work"' EXIT
mkdir -p "$work/bin" "$work/repo/ops" "$work/offsite"
path=scripts/work-with-me-employer/peek-navigation-v2-production
core=scripts/work-with-me-employer/link-ideas/peek-release/payload/core.php
mkdir -p "$work/repo/$path" "$work/repo/$(dirname "$core")"
cp "$repo/$core" "$work/repo/$core"
cp "$here"/*.{py,php,css,js,cjs,sh} "$work/repo/$path/"
cat >"$work/bin/ssh" <<'EOF'
#!/usr/bin/env bash
set -eu
[[ "${*: -2:1}" = fixture@fixture.invalid ]]
cd "$STACK"
docker compose run --rm --no-deps -T -e HOME=/home/customer -e WP_CLI_CONFIG_PATH=/archives/fixture-cli.yml -v "$STACK/backups:/home/customer/private-backups" --entrypoint bash wpcli -c "${!#}"
if [[ "${FIXTURE_WRITE_ERROR:-0}" = 1 && "${!#}" = *"remote-phase.sh write apply "* ]]; then exit 57; fi
EOF
cat >"$work/bin/curl" <<'PY'
#!/usr/bin/env python3
import os
import subprocess
import sys
from pathlib import Path
from urllib.parse import urlsplit
url = urlsplit(sys.argv[-1])
assert (url.scheme, url.netloc) == ('https', 'thaisolitude.com')
cmd = ['docker','compose','exec','-T','wordpress','curl','-sS','-i',
       '-H','Host: thaisolitude.com','-H','X-Forwarded-Proto: https',
       'http://127.0.0.1' + url.path]
reply = subprocess.run(cmd, cwd=os.environ['STACK'], check=True, capture_output=True).stdout
head, body = reply.split(b'\r\n\r\n', 1)
Path(sys.argv[sys.argv.index('-D') + 1]).write_bytes(head)
sys.stdout.buffer.write(body + head.split()[1])
PY
cat >"$work/bin/offsite" <<'EOF'
#!/usr/bin/env bash
set -eu
[[ "$FIXTURE_FAIL_COPY" != all && !( "$FIXTURE_FAIL_COPY" = snapshot && "$2" = *.json ) ]]
cp "$1/$2" "$FIXTURE_OFFSITE/$2"
cmp "$1/$2" "$FIXTURE_OFFSITE/$2"
EOF
# Browser behavior runs separately against the three recorded trial pages.
# Assert the wrapper schedules exactly that verifier after its HTTP checks.
mkdir -p "$work/repo/scripts/ci"
# shellcheck disable=SC2016
echo '[[ "$1" = --browsers ]]' >"$work/repo/scripts/ci/install-test-tools.sh"
cat >"$work/bin/node" <<'EOF'
#!/usr/bin/env bash
set -eu
[[ "$#" = 1 && "$1" = */verify-browser.cjs && -f "$1" ]]
touch "$FIXTURE_OFFSITE/browser-verified"
EOF
chmod 755 "$work/bin/"*
export PATH="$work/bin:$PATH" S_TARGET=live GITHUB_REF=refs/heads/main GITHUB_REPOSITORY=fixture/fixture
export GITHUB_WORKFLOW_REF=fixture/fixture/.github/workflows/site-live.yml@refs/heads/main GITHUB_EVENT_NAME=workflow_dispatch
GITHUB_RUN_ID=$(date +%s)
export CONFIRM=live-rehearsal GITHUB_RUN_ID GITHUB_RUN_ATTEMPT=1
export TS_OFFSITE_HOOK="$work/bin/offsite" FIXTURE_OFFSITE="$work/offsite" TS_SERVER_BACKUPS="$work/runner" S26_CREDS="$work/creds"
printf 'Host: fixture.invalid\nUser: fixture\nPort: 22\n' >"$S26_CREDS"
cd "$STACK"
# SiteGround's purge command has no local cache backend.
printf '%s\n' 'require: /archives/fixture-sg.php' 'db export:' '  skip-ssl: true' >archives/fixture-cli.yml
cat >archives/fixture-sg.php <<'PHP'
<?php
WP_CLI::add_command( 'sg purge', static function () { WP_CLI::success( 'fixture cache purge' ); } );
PHP
wp() { docker compose run --rm -T wpcli wp "$@"; }
wp rewrite structure '/%postname%/' --hard
wp post update "$(wp option get wp_page_for_privacy_policy)" --post_status=publish --post_name=privacy-policy
for slug in about services contact deep-tissue-massage therapeutic-massage thai-massage work-with-me privacy-policy accessibility corporate-massage-portland chair-massage winding-willow; do
  if [[ -z "$(wp post list --post_type=page --post_status=publish --name="$slug" --field=ID)" ]]; then
    wp post create --post_title="$slug" --post_name="$slug" --post_type=page --post_status=publish
  fi
done
docker compose exec -T wordpress sh -ec 'test -e /var/www/html/.htaccess || { printf "%s\n" "RewriteEngine On" "RewriteCond %{REQUEST_FILENAME} !-f" "RewriteCond %{REQUEST_FILENAME} !-d" "RewriteRule . /index.php [L]" >/var/www/html/.htaccess; }'
wrapper="$work/repo/$path/push-navigation-v2.sh"
request() {
  jq -n --args '$ARGS.positional | {id:"live-rehearsal",op:"run",target:"live",script:"peek-navigation-v2-production/push-navigation-v2.sh",args:.,tests:"auto"}' -- "$@" >"$work/repo/ops/live-request.json"
}
request --apply
export FIXTURE_FAIL_COPY=all
if bash "$wrapper" --apply; then echo 'Accepted failed backup copy' >&2; exit 1; fi
[[ -s "backups/live-before-peek-v2-$GITHUB_RUN_ID-1.sql" ]]
docker compose run --rm -T --entrypoint sh wpcli -ec 'dir="/backups/peek-v2-live-$1-1/snapshots"; test -d "$dir"; entries=$(ls -A "$dir"); test -z "$entries"' sh "$GITHUB_RUN_ID"
[[ "$(wp eval 'echo hash("sha256", get_post(1343)->post_content);')" = f152fad975bb85ed4ad06a0ce5f0000cd656276b34efb38b748646a5fdc73566 ]]
echo 'PASS failed off-site copy kept v1 and created no snapshot.'
export GITHUB_RUN_ATTEMPT=2 FIXTURE_FAIL_COPY=0
bash "$wrapper" --apply
[[ -f "$FIXTURE_OFFSITE/browser-verified" ]]
shopt -s nullglob
snaps=("$FIXTURE_OFFSITE"/peek-v2-before-*.json)
[[ ${#snaps[@]} = 1 ]]
snap="/home/customer/private-backups/peek-v2-live-$GITHUB_RUN_ID-2/snapshots/${snaps[0]##*/}"
read -r digest _ < <(sha256sum "${snaps[0]}")
export GITHUB_RUN_ATTEMPT=3
request --rollback "$snap" "$digest"
bash "$wrapper" --rollback "$snap" "$digest"
echo 'PASS wrapper stopped at failed backup, applied, preserved snapshot, and rolled back.'

export GITHUB_RUN_ATTEMPT=4 FIXTURE_FAIL_COPY=snapshot
request --apply
rm -f "$FIXTURE_OFFSITE/browser-verified"
if bash "$wrapper" --apply >"$work/snapshot-failure.log" 2>&1; then echo 'Accepted failed off-site snapshot' >&2; exit 1; fi
cat "$work/snapshot-failure.log"
[[ -f "$FIXTURE_OFFSITE/browser-verified" ]]
grep -Fq 'Off-site snapshot copy FAILED:' "$work/snapshot-failure.log"
grep -Fq 'PASS apply /winding-willow/' "$work/snapshot-failure.log"
read -r _ _ snap _ digest < <(grep '^Recovery snapshot ' "$work/snapshot-failure.log")
export GITHUB_RUN_ATTEMPT=5 FIXTURE_FAIL_COPY=0
request --rollback "$snap" "$digest"
bash "$wrapper" --rollback "$snap" "$digest"
echo 'PASS snapshot-copy failure reported and all public routes checked before recovery.'

export GITHUB_RUN_ATTEMPT=6 FIXTURE_WRITE_ERROR=1
request --apply
rc=0
bash "$wrapper" --apply >"$work/write-error.log" 2>&1 || rc=$?
cat "$work/write-error.log"
[[ "$rc" = 57 ]]
grep -Fq 'PASS apply /winding-willow/' "$work/write-error.log"
read -r _ _ snap _ digest < <(grep '^Recovery snapshot ' "$work/write-error.log")
export GITHUB_RUN_ATTEMPT=7 FIXTURE_WRITE_ERROR=0
request --rollback "$snap" "$digest"
bash "$wrapper" --rollback "$snap" "$digest"
echo 'PASS failed write acknowledgment verified actual v2, retained status 57 and recovered exact v1.'
