#!/usr/bin/env bash
# ONE-TIME, run by Ryan on his computer: make the GitHub-only SiteGround key and store the five secrets the workflows need, in both
# GitHub environments of this repository ("staging" for site-ops.yml, "production" for site-live.yml, which gives its secrets only to
# runs from main). Prints the PUBLIC key at the end: import it in SiteGround (Site Tools > Devs > SSH Keys Manager > Import) to switch
# the access on; delete it there to switch it off.
#
#   scripts/ci/setup-github-secrets.sh            make a new key (refuses if one was made before and is still on this computer)
#   scripts/ci/setup-github-secrets.sh --rotate   make a new key and replace the secrets (then import the new public key, delete the old one)
#
# Values come from the gitignored remote_execution.txt (Host, Port, User; User without its "@domain" part) and from ~/.ssh/known_hosts
# (the SiteGround host key this computer already trusts, so GitHub pins the same one). Nothing secret is printed. The private key goes
# only to GitHub: the copy on this computer is deleted once both environments hold it (keep the public key; GitHub never shows secrets back).
# Needs: gh signed in with admin rights on the repository, ssh-keygen.
set -euo pipefail

repo="${TS_REPO:-alanmz-crypto/new-masseuse-site}"
root=$(cd "$(dirname "$0")/../.." && pwd)
creds="${S26_CREDS:-$root/remote_execution.txt}"
dir="$HOME/.config/thai-solitude"; key="$dir/github-actions-sg"
[ "${1:-}" = --rotate ] || [ ! -e "$key.pub" ] || { echo "A GitHub key was made before ($key.pub). Use --rotate to replace it." >&2; exit 1; }
[ -f "$creds" ] || { echo "Missing $creds" >&2; exit 1; }
field() { sed -n "s/^$1:[[:space:]]*//p" "$creds" | head -1; }
host=$(field Host); port=$(field Port); user=$(field User); user="${user%%@*}"
[ -n "$host" ] && [ -n "$port" ] && [ -n "$user" ] || { echo "remote_execution.txt is missing Host, Port or User." >&2; exit 1; }
known=$(ssh-keygen -F "[$host]:$port" -f "$HOME/.ssh/known_hosts" 2>/dev/null | grep -v '^#' | head -1)
[ -n "$known" ] || { echo "No trusted host key for the SiteGround server in ~/.ssh/known_hosts; connect once with ssh first." >&2; exit 1; }
for env in staging production; do gh api "repos/$repo/environments/$env" >/dev/null || { echo "GitHub environment '$env' is missing (see CLOUD-SETUP.md)." >&2; exit 1; }; done

mkdir -p "$dir"; chmod 700 "$dir"
rm -f "$key" "$key.pub"
ssh-keygen -q -t ed25519 -N '' -C "github-actions@new-masseuse-site $(date -u +%Y-%m-%d)" -f "$key"
for env in staging production; do
  printf '%s' "$host" | gh secret set SG_HOST --env "$env" -R "$repo"
  printf '%s' "$port" | gh secret set SG_PORT --env "$env" -R "$repo"
  printf '%s' "$user" | gh secret set SG_USER --env "$env" -R "$repo"
  printf '%s\n' "$known" | gh secret set SG_KNOWN_HOSTS --env "$env" -R "$repo"
  gh secret set SG_SSH_KEY --env "$env" -R "$repo" < "$key"
done
for env in staging production; do
  have=$(gh secret list --env "$env" -R "$repo" --json name --jq '[.[].name] | sort | join(" ")')
  [ "$have" = "SG_HOST SG_KNOWN_HOSTS SG_PORT SG_SSH_KEY SG_USER" ] || { echo "Environment $env holds '$have', not the five secrets; the private key is kept at $key for a retry." >&2; exit 1; }
  echo "GitHub environment $env: $have"
done
rm -f "$key"
echo
echo "Private key: stored in GitHub only (deleted here). Public key kept at $key.pub."
echo "NEXT, in SiteGround: Site Tools > Devs > SSH Keys Manager > Import. Name: github-actions. Public key:"
echo
cat "$key.pub"
echo
echo "Fingerprint: $(ssh-keygen -lf "$key.pub" | awk '{print $2}')"

# ref: ./setup-ssh.sh — how the workflows use these secrets; ../../CLOUD-SETUP.md — the whole one-time setup
