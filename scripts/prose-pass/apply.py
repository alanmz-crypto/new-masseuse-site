#!/usr/bin/env python3
"""Apply edits.py to the localhost:8090 practice stack or to staging25.

  python3 scripts/prose-pass/apply.py                               # localhost dry run
  python3 scripts/prose-pass/apply.py --apply                       # localhost (back up the DB first)
  python3 scripts/prose-pass/apply.py --target staging25            # staging25 dry run over SSH
  python3 scripts/prose-pass/apply.py --target staging25 --apply    # dump the staging DB first
  ... --edits edits_about_intro                                      # apply a follow-up edit set

Refuses a target if any `old` string is missing or ambiguous, so it will not run
against content that changed since the edits were written. Options are edited
in place with PHP str_replace on the one key, never round-tripped as a whole.
staging25 needs SSH_AUTH_SOCK holding the ~/.ssh/thaisolitude key.
"""
import base64
import importlib
import json
import os
import shlex
import subprocess
import sys

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(os.path.dirname(HERE))
sys.path.insert(0, HERE)

TARGETS = {
    "local": {"host": "localhost:8090", "purge": []},
    "staging25": {"host": "staging25.thaisolitude.com", "purge": [["sg", "purge"]]},
}
SSH = ["ssh", "-o", "BatchMode=yes", "-p", "18765", "u3302-bwfv7ug4e2fy@ssh.thaisolitude.com"]
REMOTE_DIR = "~/www/staging25.thaisolitude.com/public_html"


def load_edits(name):
    """Return (PAGES, MULTI, OPTION_EDITS); option edits are (option, key path, old, new)."""
    m = importlib.import_module(name)
    options = getattr(m, "OPTIONS", {})
    option_edits = (
        [("astra-settings", ["footer-copyright-editor"], o, n) for o, n in options.get("astra-settings.footer-copyright-editor", [])]
        + [("widget_block", [7, "content"], o, n) for o, n in options.get("widget_block.7.content", [])]
    )
    return m.PAGES, getattr(m, "MULTI", {}), option_edits

OPTION_PHP = r"""
$edits = json_decode(base64_decode('%s'), true);
$apply = %s;
foreach ($edits as $e) {
    list($opt, $path, $old, $new) = $e;
    $value = get_option($opt);
    $ref = &$value;
    foreach ($path as $k) { $ref = &$ref[$k]; }
    $n = is_string($ref) ? substr_count($ref, $old) : -1;
    if ($n !== 1) { echo "MISS $opt: found $n\n"; exit(1); }
    if ($apply) {
        $ref = str_replace($old, $new, $ref);
        unset($ref);
        update_option($opt, $value);
    } else {
        unset($ref);
    }
}
echo "OK\n";
"""


def make_wp(target):
    def wp(args, stdin=None):
        if target == "local":
            cmd = ["docker", "compose", "run", "--rm", "-T", "wpcli", "wp", "--user=1"] + args
        else:
            remote = f"cd {REMOTE_DIR} && wp --user=1 " + " ".join(shlex.quote(a) for a in args)
            cmd = SSH + [remote]
        r = subprocess.run(cmd, cwd=ROOT, input=stdin, capture_output=True, text=True)
        if r.returncode != 0:
            sys.exit(f"wp {' '.join(args)} failed:\n{r.stdout}\n{r.stderr}")
        return r.stdout
    return wp


def rewrite(label, text, edits, multi=()):
    errors = []
    for old, new in edits:
        n = text.count(old)
        if n != 1:
            errors.append(f"  {label}: expected 1, found {n}: {old[:90]!r}")
        else:
            text = text.replace(old, new)
    for old, new, want in multi:
        n = text.count(old)
        if n != want:
            errors.append(f"  {label}: expected {want}, found {n}: {old[:90]!r}")
        else:
            text = text.replace(old, new)
    return text, errors


def option_php(option_edits, apply):
    data = base64.b64encode(json.dumps(option_edits).encode()).decode()
    return OPTION_PHP % (data, "true" if apply else "false")


def main():
    apply = "--apply" in sys.argv
    target = sys.argv[sys.argv.index("--target") + 1] if "--target" in sys.argv else "local"
    if target not in TARGETS:
        sys.exit(f"Unknown target {target!r}; use one of {', '.join(TARGETS)}.")
    PAGES, MULTI, OPTION_EDITS = load_edits(sys.argv[sys.argv.index("--edits") + 1] if "--edits" in sys.argv else "edits")
    wp = make_wp(target)
    if TARGETS[target]["host"] not in wp(["option", "get", "siteurl"]):
        sys.exit(f"Refusing: siteurl is not {TARGETS[target]['host']}.")

    def content(pid):
        out = wp(["post", "get", str(pid), "--field=post_content"])
        return out[:-1] if out.endswith("\n") else out  # WP-CLI appends one newline

    plans, errors = [], []
    for pid, edits in PAGES.items():
        new, errs = rewrite(f"post {pid}", content(pid), edits, MULTI.get(pid, ()))
        errors += errs
        plans.append((pid, new))
    if errors:
        sys.exit("Refusing; no changes made:\n" + "\n".join(errors))
    if OPTION_EDITS:
        check = wp(["eval-file", "-"], stdin="<?php\n" + option_php(OPTION_EDITS, False))
        if "OK" not in check:
            sys.exit("Refusing; option check failed:\n" + check)
    print(f"[{target}] All {sum(len(e) for e in PAGES.values())} page edits, "
          f"{len(MULTI)} multi edit, and {len(OPTION_EDITS)} option edits matched.")
    if not apply:
        print("Dry run only. Re-run with --apply.")
        return

    for pid, new in plans:
        wp(["post", "update", str(pid), "-"], stdin=new)
        if content(pid) != new:
            sys.exit(f"post {pid}: stored content differs from what was written (kses?). Stop and restore.")
    if OPTION_EDITS:
        wp(["eval-file", "-"], stdin="<?php\n" + option_php(OPTION_EDITS, True))
    wp(["cache", "flush"])
    for purge in TARGETS[target]["purge"]:
        wp(purge)
    print(f"[{target}] Applied.")


if __name__ == "__main__":
    main()
