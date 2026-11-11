#!/usr/bin/env python3
"""Private cold-copy IO: exact SQL pin, DDL-only compatibility, safe receipts."""
import hashlib
import json
from pathlib import Path
import re
import sys

SQL_SHA = "4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0"
MANIFEST_SHA = "5255d85c0d64cfe4bd4eed740f4273f993940e013a715f071cfe3eef9b8269b5"
PROFILE_SHA = "49ed31542554634dcd945d8048a116e6dd3ae61635d1ad47a2a6ae56df6c3cac"
ORIGIN = "http://thaisolitude.com"


def digest(raw):
    return hashlib.sha256(raw).hexdigest()


def transform_sql(raw):
    """Preserve INSERT bytes; adapt only a known collation inside CREATE TABLE."""
    output, count, ddl = [], 0, False
    collation = re.compile(rb"\bCOLLATE(?:=| )utf8mb4_0900_ai_ci\b")
    for line in raw.splitlines(keepends=True):
        if line.startswith(b"CREATE TABLE "):
            if ddl:
                raise ValueError("nested DDL")
            ddl = True
        if ddl:
            line, changed = collation.subn(b"COLLATE=utf8mb4_unicode_ci", line)
            count += changed
            if b"utf8mb4_0900_ai_ci" in line:
                raise ValueError("unreviewed DDL collation syntax")
            if line.rstrip().endswith(b";"):
                ddl = False
        output.append(line)
    if ddl:
        raise ValueError("incomplete DDL")
    return b"".join(output), count


def bootstrap_receipt(stdout, stderr, status, manifest, profile):
    if digest(manifest) != MANIFEST_SHA or digest(profile) != PROFILE_SHA:
        raise ValueError("profile binding")
    spec = json.loads(manifest)
    preflights, sources, unknown = [], [], []
    deprecation = re.compile(r"^(?:PHP )?Deprecated: +Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors\.php on line 95$")
    known_success = "Success: Original visitor baseline ready; nothing changed."
    for stream, raw in (("stdout", stdout), ("stderr", stderr)):
        for line in raw.decode("utf-8", errors="replace").splitlines():
            if not line.strip() or deprecation.fullmatch(line) or line == known_success:
                continue
            try:
                if stream == "stdout" and line.startswith("RELEASE-PREFLIGHT "):
                    preflights.append(json.loads(line.split(" ", 1)[1]))
                    continue
                if stream == "stdout" and line.startswith("RELEASE-SPECTRA-SOURCE "):
                    sources.append(json.loads(line.split(" ", 1)[1]))
                    continue
            except (ValueError, TypeError):
                pass
            unknown.append({"stream": stream, "bytes": len(line.encode()), "sha256": digest(line.encode())})
    valid = len(preflights) == len(sources) == 1 and not unknown and status == 0
    preflight = preflights[0] if len(preflights) == 1 else {}
    source = sources[0] if len(sources) == 1 else {}
    expected_keys = {"schema", "bundle", "home", "manifest_sha256", "profile_sha256", "state", "protected_state_sha256"}
    valid = valid and isinstance(preflight, dict) and set(preflight) == expected_keys
    valid = valid and preflight.get("schema") == 1 and preflight.get("bundle") == "peek-visitor-release-v1"
    valid = valid and preflight.get("home") == ORIGIN and preflight.get("state") == "ready"
    valid = valid and preflight.get("manifest_sha256") == MANIFEST_SHA and preflight.get("profile_sha256") == PROFILE_SHA
    valid = valid and isinstance(preflight.get("protected_state_sha256"), str) and bool(re.fullmatch(r"[a-f0-9]{64}", preflight["protected_state_sha256"]))
    loaded = {"main": True, "UAGB_Helper": True, "UAGB_Rest_API": True, "UAGB_Loader": True, "UAGB_Post_Assets": True, "UAGB_Front_Assets": True}
    expected_source = {"files": spec["spectra"]["files"], "header_version": "2.20.6", "runtime_version": "2.20.6", "loaded": loaded, "save_post_priority_10": True}
    valid = valid and source == expected_source
    # Invalid marker bodies are never forwarded: they may contain private data.
    return {"schema": 1, "scope": "isolated-copy-only", "valid": bool(valid), "wp_exit": status,
            "manifest_sha256": MANIFEST_SHA, "profile_sha256": PROFILE_SHA,
            "protected_state_sha256": preflight["protected_state_sha256"] if valid else None,
            "spectra_source_bound": source == expected_source, "unexpected_lines": unknown,
            "stdout_sha256": digest(stdout), "stderr_sha256": digest(stderr)}


def main(args):
    if len(args) == 3 and args[0] == "import-sql":
        raw = Path(args[1]).read_bytes()
        if len(raw) != 3919398 or digest(raw) != SQL_SHA:
            raise ValueError("fixed SQL differs")
        adapted, count = transform_sql(raw)
        receipt = {"schema": 1, "source_sha256": SQL_SHA, "source_bytes": len(raw),
                   "ddl_collation_substitutions": count, "import_sha256": digest(adapted),
                   "data_rewritten": False}
        Path(args[2]).write_text(json.dumps(receipt, sort_keys=True) + "\n")
        sys.stdout.buffer.write(adapted)
        return 0
    if len(args) == 4 and args[0] == "import-current-sql":
        raw = Path(args[1]).read_bytes()
        if not re.fullmatch(r"[a-f0-9]{64}", args[2]) or not 1000 < len(raw) < 64 * 1024 * 1024 or digest(raw) != args[2]:
            raise ValueError("captured current SQL differs")
        adapted, count = transform_sql(raw)
        receipt = {"schema": 1, "scope": "fresh-current-live-copy", "source_sha256": digest(raw),
                   "source_bytes": len(raw), "ddl_collation_substitutions": count,
                   "import_sha256": digest(adapted), "data_rewritten": False}
        Path(args[3]).write_text(json.dumps(receipt, sort_keys=True) + "\n")
        sys.stdout.buffer.write(adapted)
        return 0
    if len(args) == 6 and args[0] == "bootstrap":
        result = bootstrap_receipt(Path(args[1]).read_bytes(), Path(args[2]).read_bytes(),
                                   int(args[3]), Path(args[4]).read_bytes(), Path(args[5]).read_bytes())
        print("LIVE30-COLD-BOOTSTRAP " + json.dumps(result, sort_keys=True))
        return 0 if result["valid"] else 1
    raise ValueError("unsupported IO command")


if __name__ == "__main__":
    try:
        sys.exit(main(sys.argv[1:]))
    except Exception:
        print("FAIL cold private IO: incomplete or unrecognized input; no raw data retained", file=sys.stderr)
        sys.exit(1)
