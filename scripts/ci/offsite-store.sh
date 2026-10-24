#!/usr/bin/env bash
# GitHub Actions hook for sync-server-backups.sh (TS_OFFSITE_HOOK): keep each verified backup OUTSIDE the runner, encrypted, before any change goes on.
#
#   offsite-store.sh DIR FILE...      FILE = names inside DIR (DB exports *.sql and rollback snapshots *.json)
#
# Each file is encrypted with age to the public key in ./offsite-recipient.txt (only Ryan's private key, kept off GitHub, can read it) and uploaded
# as FILE.age to a DRAFT release named offsite-YYYY-MM in this repository, together with FILE.sha256 (the plaintext checksum, for checking a
# later decryption). The uploaded file is then downloaded back and must match byte for byte; only then is FILE.sha256 uploaded, so a file
# whose .sha256 is already in the release is a finished copy and is skipped (exports and snapshots are never rewritten), while a .age without
# its .sha256 (an interrupted run) is replaced. Exit 0 only when every file is stored and read back identical.
# Read the copies on a computer with: scripts/ci/fetch-offsite.sh
# Needs: age, curl, jq, GH_TOKEN (the job's token, contents: write) and GITHUB_REPOSITORY.
set -euo pipefail

here=$(cd "$(dirname "$0")" && pwd)
dir="${1:?usage: offsite-store.sh DIR FILE...}"; shift
[ "$#" -gt 0 ] || { echo "offsite-store: no files given" >&2; exit 1; }
: "${GH_TOKEN:?offsite-store needs GH_TOKEN}" "${GITHUB_REPOSITORY:?offsite-store needs GITHUB_REPOSITORY}"
command -v age >/dev/null || { echo "offsite-store: age is not installed" >&2; exit 1; }
recipients="$here/offsite-recipient.txt"
grep -q '^age1' "$recipients" || { echo "offsite-store: no age public key in $recipients" >&2; exit 1; }

api="https://api.github.com/repos/$GITHUB_REPOSITORY"
tok=(-H "Authorization: Bearer $GH_TOKEN" -H "X-GitHub-Api-Version: 2022-11-28")
auth=("${tok[@]}" -H "Accept: application/vnd.github+json")   # JSON calls; a download sends only "Accept: application/octet-stream" (two Accept headers return JSON)
tag="offsite-$(date -u +%Y-%m)"

# A draft release is not visible through the by-tag endpoint, so look it up in the list.
release_id() {
  local page=1 out id
  while :; do
    out=$(curl -fsS "${auth[@]}" "$api/releases?per_page=100&page=$page")
    id=$(jq -r --arg t "$tag" 'map(select(.tag_name == $t and .draft)) | .[0].id // empty' <<<"$out")
    [ -z "$id" ] || { echo "$id"; return 0; }
    [ "$(jq length <<<"$out")" -eq 100 ] || return 1
    page=$((page + 1))
  done
}
rid=$(release_id || true)
if [ -z "$rid" ]; then
  body=$(jq -n --arg t "$tag" '{tag_name: $t, name: ("Off-site backups " + ($t | ltrimstr("offsite-")) + " (encrypted)"), draft: true,
    body: "Encrypted copies of the DB exports and rollback snapshots taken on the SiteGround server by the GitHub workflows. Each FILE.age is encrypted with age to the key in scripts/ci/offsite-recipient.txt; FILE.sha256 is the checksum of the decrypted file. Read them with scripts/ci/fetch-offsite.sh. Do not publish this draft."}')
  rid=$(curl -fsS "${auth[@]}" -X POST "$api/releases" -d "$body" | jq -r .id)
  [ -n "$rid" ] && [ "$rid" != null ] || { echo "offsite-store: could not create the draft release $tag" >&2; exit 1; }
  echo "off-site: created draft release $tag"
fi
existing=""; page=1   # "ID NAME" lines
while :; do
  out=$(curl -fsS "${auth[@]}" "$api/releases/$rid/assets?per_page=100&page=$page")
  existing+="$(jq -r '.[] | "\(.id) \(.name)"' <<<"$out")"$'\n'
  [ "$(jq length <<<"$out")" -eq 100 ] || break
  page=$((page + 1))
done
asset_id() { awk -v n="$1" '$2 == n {print $1; exit}' <<<"$existing"; }
delete_asset() { curl -fsS "${auth[@]}" -X DELETE "$api/releases/assets/$1" >/dev/null; }

upload() { # upload FILE NAME -> prints the asset id
  curl -fsS "${auth[@]}" -H "Content-Type: application/octet-stream" --data-binary @"$1" \
    "https://uploads.github.com/repos/$GITHUB_REPOSITORY/releases/$rid/assets?name=$2" | jq -r .id
}

work=$(mktemp -d); trap 'rm -rf "$work"' EXIT
stored=0; skipped=0
for f in "$@"; do
  case "$f" in */*|'') echo "offsite-store: refusing file name '$f'" >&2; exit 1 ;; esac
  [ -s "$dir/$f" ] || { echo "offsite-store: $dir/$f is missing or empty" >&2; exit 1; }
  # FILE.sha256 is uploaded only after FILE.age was read back identical, so its presence marks a finished, verified copy.
  if [ -n "$(asset_id "$f.sha256")" ]; then skipped=$((skipped + 1)); continue; fi
  stale=$(asset_id "$f.age"); [ -z "$stale" ] || { delete_asset "$stale"; echo "off-site: removed an unverified earlier upload of $f.age"; }
  age -R "$recipients" -o "$work/$f.age" "$dir/$f"
  (cd "$dir" && sha256sum -- "$f") > "$work/$f.sha256"
  id=$(upload "$work/$f.age" "$f.age")
  [ -n "$id" ] && [ "$id" != null ] || { echo "offsite-store: upload of $f.age failed" >&2; exit 1; }
  # Read it back from GitHub and compare, so "stored" means stored.
  curl -fsSL "${tok[@]}" -H "Accept: application/octet-stream" "$api/releases/assets/$id" -o "$work/back.age"
  if ! cmp -s "$work/$f.age" "$work/back.age"; then
    delete_asset "$id" || true
    echo "offsite-store: $f.age read back DIFFERENT from what was uploaded (upload removed)" >&2; exit 1
  fi
  sid=$(upload "$work/$f.sha256" "$f.sha256")
  [ -n "$sid" ] && [ "$sid" != null ] || { echo "offsite-store: upload of $f.sha256 failed" >&2; exit 1; }
  echo "off-site: $f -> $tag/$f.age ($(stat -c%s "$work/$f.age") bytes, read back identical; plaintext sha256 $(cut -c1-12 "$work/$f.sha256")...)"
  stored=$((stored + 1))
  rm -f "$work/$f.age" "$work/back.age"
done
echo "OFF-SITE VERIFIED: $stored file(s) stored encrypted in draft release $tag, $skipped already there."

# ref: ../work-with-me-employer/sync-server-backups.sh — calls this after its own verified copy; ./fetch-offsite.sh — reads the copies back on a computer
# ref: ./offsite-recipient.txt — the public key; the private key is ~/.config/thai-solitude/offsite-backups.agekey on Ryan's computer (never in git)
