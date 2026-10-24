#!/usr/bin/env bash
# On Ryan's computer: bring the encrypted off-site backups that GitHub Actions stored (draft releases offsite-YYYY-MM) into the usual folder,
# decrypted and checked, next to the copies sync-server-backups.sh makes. Copies only; never deletes anything on GitHub or here.
#
#   scripts/ci/fetch-offsite.sh            every month
#   scripts/ci/fetch-offsite.sh 2026-10    one month
#
# Destination: $TS_SERVER_BACKUPS or ~/WordPress/thai-solitude-server-backups. A file already there is skipped (and must match its .sha256).
# Key: $TS_OFFSITE_KEY or ~/.config/thai-solitude/offsite-backups.agekey. Needs gh (signed in), age and jq.
set -euo pipefail

repo="${TS_REPO:-alanmz-crypto/new-masseuse-site}"
dest="${TS_SERVER_BACKUPS:-$HOME/WordPress/thai-solitude-server-backups}"
key="${TS_OFFSITE_KEY:-$HOME/.config/thai-solitude/offsite-backups.agekey}"
month="${1:-}"
case "$month" in ''|[0-9][0-9][0-9][0-9]-[0-9][0-9]) ;; *) echo "Usage: $0 [YYYY-MM]" >&2; exit 1 ;; esac
[ -r "$key" ] || { echo "No age private key at $key" >&2; exit 1; }
for c in gh age jq; do command -v "$c" >/dev/null || { echo "$c is required" >&2; exit 1; }; done
mkdir -p "$dest"; chmod 700 "$dest"
work=$(mktemp -d); trap 'rm -rf "$work"' EXIT

mapfile -t rels < <(gh api --paginate "repos/$repo/releases?per_page=100" --jq '.[] | select(.draft and (.tag_name | startswith("offsite-"))) | "\(.id) \(.tag_name)"' | sort -k2)
[ "${#rels[@]}" -gt 0 ] || { echo "No off-site releases on GitHub yet."; exit 0; }
new=0; have=0; bad=0
for r in "${rels[@]}"; do
  rid=${r%% *}; tag=${r#* }
  [ -z "$month" ] || [ "$tag" = "offsite-$month" ] || continue
  mapfile -t assets < <(gh api --paginate "repos/$repo/releases/$rid/assets?per_page=100" --jq '.[] | "\(.id) \(.name)"')
  for a in "${assets[@]}"; do
    aid=${a%% *}; name=${a#* }
    case "$name" in *.age) ;; *) continue ;; esac
    f=${name%.age}
    sid=$(printf '%s\n' "${assets[@]}" | awk -v n="$f.sha256" '$2 == n {print $1}')
    [ -n "$sid" ] || { echo "  $tag/$name has no .sha256; skipped" >&2; bad=$((bad + 1)); continue; }
    gh api -H "Accept: application/octet-stream" "repos/$repo/releases/assets/$sid" > "$work/$f.sha256"
    if [ -e "$dest/$f" ]; then
      if (cd "$dest" && sha256sum -c --status "$work/$f.sha256"); then have=$((have + 1)); else echo "  $f is already here but DIFFERS from the off-site copy's checksum (left alone)" >&2; bad=$((bad + 1)); fi
      continue
    fi
    gh api -H "Accept: application/octet-stream" "repos/$repo/releases/assets/$aid" > "$work/$name"
    ( umask 077; age -d -i "$key" -o "$work/$f" "$work/$name" )
    if (cd "$work" && sha256sum -c --status "$f.sha256"); then
      mv "$work/$f" "$dest/$f"; chmod 600 "$dest/$f"; echo "  + $f ($tag)"; new=$((new + 1))
    else
      echo "  $f decrypted to something with a DIFFERENT checksum; not kept" >&2; bad=$((bad + 1))
    fi
    rm -f "$work/$name" "$work/$f"
  done
done
echo "off-site copies: $new new, $have already here and identical, $bad problem(s). Folder: $dest"
[ "$bad" -eq 0 ]

# ref: ./offsite-store.sh — what stores these copies; ../work-with-me-employer/sync-server-backups.sh — the same folder for direct copies
