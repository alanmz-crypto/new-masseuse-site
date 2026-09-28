#!/usr/bin/env bash
# Read-only parity check: PHP baked into the practice stack vs. its deploy target.
#
# The practice stack (:8090) is a stand-in for thaisolitude.com. Its PHP version
# comes from the digest-pinned wordpress/wpcli images in docker-compose.yml, while
# the target is SiteGround-provisioned. This script reports when they diverge.
#
# Unwired: no sync script exists yet in this single-tier project to call it from.
# It makes no changes, needs no network, and only reads docker-compose.yml plus
# the committed expectations file.
#
# Two severity tiers, and --warn only reaches the lower one:
#
#   fatal  - the check could not run, or its inputs cannot be trusted: bad args,
#            missing/malformed expectations file, an image that has lost its
#            digest pin, or a recorded PHP value that does not match what the
#            image tag itself states. --warn does not suppress these.
#   warn   - the check ran and found PHP drift between practice and the target.
#            This is the only tier --warn downgrades to a non-fatal report.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
COMPOSE_FILE="${COMPOSE_FILE:-$ROOT/docker-compose.yml}"
EXPECTED_FILE="${EXPECTED_FILE:-$ROOT/scripts/practice-expected-php.txt}"

WARN_ONLY=0
QUIET=0

usage() {
	sed -n '2,20p' "$0"
}

problem() {
	echo "IMAGE PARITY: $*" >&2
	exit 1
}

while [[ $# -gt 0 ]]; do
	case "$1" in
		-h|--help) usage; exit 0 ;;
		--warn) WARN_ONLY=1; shift ;;
		--quiet) QUIET=1; shift ;;
		*) problem "unknown arg: $1 (use --help)" ;;
	esac
done

[[ -f "$COMPOSE_FILE" ]] || problem "wrong-path: missing compose file $COMPOSE_FILE"
[[ -f "$EXPECTED_FILE" ]] || problem "wrong-path: missing expectations file $EXPECTED_FILE"

read_expected() {
	local key="$1" value
	value="$(sed -n "s/^${key}=\([0-9][0-9.]*\)$/\1/p" "$EXPECTED_FILE" | head -1)"
	[[ -n "$value" ]] || problem "malformed expectations: no '${key}=<version>' line in $EXPECTED_FILE"
	printf '%s' "$value"
}

# Every image line in the compose file must carry a digest pin; an unpinned tag
# can resolve to different bits later, which is what the expectations file cannot
# track.
check_pins() {
	local offenders
	offenders="$(sed -n 's/^[[:space:]]*image:[[:space:]]*\(.*\)$/\1/p' "$COMPOSE_FILE" | grep -v '@sha256:' || true)"
	if [[ -n "$offenders" ]]; then
		problem "docker-compose.yml has unpinned image reference(s): $(printf '%s ' "$offenders")"
	fi
}

check_pins

# The recorded practice_php value is a claim about what the pinned image runs,
# and a stale claim would make this whole check report parity that does not
# exist. wordpress:latest does not state a PHP version in its tag at all, so
# that tag is rejected as unverifiable rather than assumed correct -- re-pin to
# a version-stating tag (e.g. wordpress:php8.2-apache) before this check can
# confirm anything about it.
check_tag_matches_recorded() {
	local recorded="$1" tags tag minor
	tags="$(sed -n 's/^[[:space:]]*image:[[:space:]]*wordpress:\([^@]*\)@sha256:.*$/\1/p' "$COMPOSE_FILE" || true)"
	[[ -n "$tags" ]] || problem "no digest-pinned wordpress image found in $COMPOSE_FILE"
	while IFS= read -r tag; do
		[[ -n "$tag" ]] || continue
		if [[ "$tag" == latest || "$tag" == cli ]]; then
			echo "IMAGE PARITY: image tag 'wordpress:$tag' does not state a PHP version; cannot confirm it matches practice_php=$recorded" >&2
			problem "cannot verify practice_php against a version-agnostic image tag ($tag)"
		fi
		minor="$(printf '%s' "$tag" | grep -oE 'php[0-9]+\.[0-9]+' | head -1 | sed 's/^php//')"
		[[ -n "$minor" ]] || problem "cannot read a PHP version from image tag: wordpress:$tag"
		if [[ "${recorded%.*}" != "$minor" ]]; then
			echo "IMAGE PARITY: image tag says PHP $minor but expectations file records practice_php=$recorded" >&2
			problem "stale practice_php: update $EXPECTED_FILE after re-pinning (tag wordpress:$tag)"
		fi
	done <<<"$tags"
}

practice_php="$(read_expected practice_php)"
thaisolitude_php="$(read_expected thaisolitude_php)"

check_tag_matches_recorded "$practice_php"

drift=0
status="parity"

if [[ "$practice_php" != "$thaisolitude_php" ]]; then
	echo "IMAGE PARITY: practice=$practice_php but thaisolitude.com=$thaisolitude_php" >&2
	echo "IMAGE PARITY: refresh the recorded values with the commands in $EXPECTED_FILE" >&2
	echo "IMAGE PARITY: a PHP-version move is a behavior change; test it deliberately, do not ride it on another change" >&2
	drift=1
	status="drift"
fi

if [[ "$QUIET" -eq 0 ]]; then
	echo "practice=$practice_php thaisolitude.com=$thaisolitude_php status=$status"
fi

if [[ "$drift" -eq 1 ]]; then
	if [[ "$WARN_ONLY" -eq 1 ]]; then
		echo "IMAGE PARITY: WARN - divergence reported above; not failing due to --warn" >&2
		exit 0
	fi
	exit 1
fi

echo "IMAGE PARITY: OK - practice PHP matches thaisolitude.com ($practice_php)"
