#!/usr/bin/env bash
# Run by the reviewed live wrapper, separately for each phase. Artifacts are private.
set -euo pipefail
phase=${1:?}; operation=${2:?}; run=${3:?}; shift 3
[[ "$run" =~ ^[0-9]+-[0-9]+$ && "$operation" =~ ^(apply|rollback)$ ]] || exit 1
doc=/home/customer/www/thaisolitude.com/public_html
cd "$doc"; [[ "$(pwd -P)" = "$doc" ]] || exit 1
private="$HOME/private-backups"
[[ -d "$private" && ! -L "$private" ]] || exit 1
private=$(cd "$private" && pwd -P)
package="$private/peek-v2-live-$run"
[[ -d "$package" && ! -L "$package" ]] || exit 1
export WWM_BACKUP_DIR="$package/snapshots"
[[ -d "$WWM_BACKUP_DIR" && ! -L "$WWM_BACKUP_DIR" ]] || exit 1
wp_site() { wp --path="$doc" --skip-plugins=sg-security --user=1 "$@"; }
install_mode() { wp_site eval-file "$package/install-v2.php" "$1" "$package/peek-v2.zip" "${@:2}"; }
dump="$private/live-before-peek-v2-$run.sql"
case "$phase" in
  prepare)
    if [[ "$operation" = apply ]]; then
      report=$(install_mode preflight)
      printf '%s\n' "$report" >&2
      [[ "$report" = *'state=ready '* ]] || exit 1
    fi
    [[ ! -e "$dump" && ! -L "$dump" ]] || exit 1
    (umask 077; wp_site db export "$dump" >&2)
    [[ -s "$dump" && -f "$dump" && ! -L "$dump" ]] || exit 1
    sha256sum -- "$dump"
    ;;
  write)
    expected=${1:?}; shift
    [[ "$expected" =~ ^[a-f0-9]{64}$ && "$(sha256sum -- "$dump")" = "$expected  $dump" ]] || exit 1
    if [[ "$operation" = apply ]]; then
      install_mode apply
    else
      snap=${1:?}; sha=${2:?}
      [[ "$snap" =~ ^/home/customer/private-backups/peek-v2-live-[0-9]+-[0-9]+/snapshots/peek-v2-before-[0-9]{8}-[0-9]{6}-[a-f0-9-]{36}\.json$ ]] || exit 1
      [[ "$(realpath "$snap")" = "$snap" && -f "$snap" && "$sha" =~ ^[a-f0-9]{64}$ && "$(sha256sum -- "$snap")" = "$sha  $snap" ]] || exit 1
      install_mode rollback "$snap"
    fi
    ;;
  snapshot)
    shopt -s nullglob
    snapshots=("$WWM_BACKUP_DIR"/peek-v2-before-*.json)
    [[ ${#snapshots[@]} = 1 && -f "${snapshots[0]}" && ! -L "${snapshots[0]}" ]] || exit 1
    sha256sum -- "${snapshots[0]}"
    ;;
  inspect) install_mode preflight ;;
  purge)
    wp_site sg purge
    wp_site cache flush
    install_mode preflight
    ;;
  *) exit 1 ;;
esac
