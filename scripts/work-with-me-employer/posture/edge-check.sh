#!/usr/bin/env bash
# One request: is the web edge answering with the SITE, or with SiteGround's bot challenge? (a burst of requests from one IP gets HTTP 202 + "sg-captcha: challenge")
#
#   ./edge-check.sh <url>      exit 0 = the site answered (any status but a challenge)
#                              exit 2 = BLOCKED: the bot challenge answered, so nothing tested through this URL proves anything (INVALID, not a pass, not a failure of the site)
#                              exit 1 = no answer at all (network, DNS, TLS)
# Used by check-all.sh <url> and post-change-test.sh to stop early with one clear message instead of a pile of misleading failures.
# Do not loop on this: while challenged, steady probing may keep the flag alive. Wait (it can take hours) and try once.
set -uo pipefail
url="${1:-}"
[ -n "$url" ] || { echo "usage: $0 <url>" >&2; exit 1; }
hdr=$(curl -s -D - -o /dev/null --max-time 20 -A 'edge-check/1' "$url" 2>/dev/null | tr -d '\r') || true
status=$(printf '%s\n' "$hdr" | sed -n '1s/^HTTP[^ ]* \([0-9][0-9]*\).*/\1/p')
if [ -z "$status" ]; then echo "NO ANSWER from $url (network, DNS or TLS problem)"; exit 1; fi
if [ "$status" = 202 ] && printf '%s\n' "$hdr" | grep -qi '^sg-captcha:'; then
  echo "BLOCKED: SiteGround's bot challenge (HTTP 202) answered for $url instead of the site. Nothing can be tested from this computer until it lapses (can take hours). INVALID: not a pass, and not a problem with the site."
  exit 2
fi
echo "the site answered ($status) for $url"
exit 0

# ref: ./posture-check.py — the full check, which handles the same challenge per request; ../post-change-test.sh, ../../check-all.sh — callers
