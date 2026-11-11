"""Synthetic render-metadata diagnostics tests; no database or network access."""
from copy import deepcopy
from hashlib import sha256
from pathlib import Path
import json
import os
import subprocess
import sys
import tempfile


HERE = Path(__file__).resolve().parent
HELPER = HERE / "observe-render-meta.py"
MANIFEST = HERE / "manifest.json"
PASSED = 0
PHASE = "original-after-rollback"
MARKER = "RELEASE-RENDER-META "


def invoke(*args, data=""):
    return subprocess.run([sys.executable, "-I", "-B", str(HELPER), *map(str, args)],
                          input=data, capture_output=True, text=True, timeout=10)


def check(name, result, success=True):
    global PASSED
    assert (result.returncode == 0) == success, (name, result.returncode, result.stdout, result.stderr)
    PASSED += 1
    return result


def hex_value(value):
    return "NULL" if value is None else value.encode().hex().upper()


def canonical_hash(value):
    return sha256(json.dumps(value, sort_keys=True, ensure_ascii=False, separators=(",", ":")).encode()).hexdigest()


def fixture(manifest, meta=None):
    pages = manifest["pages"]
    by_path = {page["path"]: page for page in pages}
    front = by_path["/"]["id"]
    rows = [["option", key, hex_value(value)] for key, value in
            (("home", "http://localhost:8090"), ("siteurl", "http://localhost:8090"), ("page_on_front", str(front)))]
    for page in pages:
        path = page["path"]
        parts = path.strip("/").split("/")
        parent_path = "/" + "/".join(parts[:-1]) + "/"
        parent = by_path[parent_path]["id"] if len(parts) > 1 else 0
        rows.append(["page", str(page["id"]), hex_value(parts[-1] or "home"), str(parent), hex_value(page["title"])])
    if meta is None:
        meta = [
            {"meta_id": "10", "post_id": str(front), "meta_key": "_uag_page_assets", "meta_value": 'a:1:{s:3:"css";s:8:"test.css";}'},
            {"meta_id": "11", "post_id": "99999", "meta_key": "PRIVATE_KEY_7c8a", "meta_value": "PRIVATE_VALUE_9d2a\tline\nไทย   / 😀"},
            {"meta_id": "12", "post_id": str(front), "meta_key": None, "meta_value": None},
            {"meta_id": "13", "post_id": str(front), "meta_key": "", "meta_value": ""},
        ]
    for row in meta:
        rows.append(["meta", row["meta_id"], row["post_id"], hex_value(row["meta_key"]), hex_value(row["meta_value"])])
    return "".join("\t".join(row) + "\n" for row in rows), meta


def parsed(result):
    lines = result.stdout.splitlines()
    assert len(lines) == 1 and lines[0].startswith(MARKER), ("exact one marker", result.stdout)
    return json.loads(lines[0][len(MARKER):])


def main():
    manifest = json.loads(MANIFEST.read_text())
    source, rows = fixture(manifest)
    target = manifest["pages"][0]["path"]
    args = (MANIFEST, PHASE, target, "before")
    first = check("valid capture", invoke("capture", *args, data=source))
    second = check("deterministic capture", invoke("capture", *args, data=source))
    assert first.stdout == second.stdout
    result = parsed(first)
    assert result["manifest_sha256"] == sha256(MANIFEST.read_bytes()).hexdigest()
    assert result["home"] == "http://localhost:8090" and result["phase"] == PHASE and result["path"] == target and result["position"] == "before"
    assert "PRIVATE_KEY_7c8a" not in first.stdout and "PRIVATE_VALUE_9d2a" not in first.stdout
    assert hex_value("PRIVATE_KEY_7c8a") not in first.stdout and hex_value(rows[1]["meta_value"]) not in first.stdout
    descriptors = result["rows"]
    assert len(descriptors) == len(rows)
    for descriptor, raw in zip(descriptors, rows, strict=True):
        assert descriptor["meta_id"] == raw["meta_id"] and descriptor["post_id"] == raw["post_id"]
        assert descriptor["row_sha256"] == canonical_hash(raw), "row hash must match installer canonical PHP JSON"
        assert descriptor["without_meta_id_sha256"] == canonical_hash({k: v for k, v in raw.items() if k != "meta_id"})
    assert descriptors[0]["known_key"] == "_uag_page_assets" and descriptors[0]["public_page"] is True
    assert descriptors[1]["known_key"] is None and descriptors[1]["public_page"] is False
    assert descriptors[1]["value_bytes"] == len(rows[1]["meta_value"].encode())
    assert descriptors[1]["value_sha256"] == sha256(rows[1]["meta_value"].encode()).hexdigest()
    assert descriptors[2]["value_type"] == "null" and descriptors[2]["value_bytes"] is None and descriptors[2]["value_sha256"] is None
    assert descriptors[3]["value_type"] == "string" and descriptors[3]["value_bytes"] == 0 and descriptors[3]["value_sha256"] == sha256(b"").hexdigest()
    assert descriptors[2]["key_sha256"] is None and descriptors[3]["key_sha256"] == sha256(b"").hexdigest()

    moved_rows = deepcopy(rows)
    moved_rows[0]["meta_id"] = "9"
    moved_rows.sort(key=lambda row: int(row["meta_id"]))
    moved = parsed(check("row ID-only replacement", invoke("capture", *args, data=fixture(manifest, moved_rows)[0])))
    before = descriptors[0]
    after = next(row for row in moved["rows"] if row["meta_id"] == "9")
    assert before["row_sha256"] != after["row_sha256"]
    assert before["without_meta_id_sha256"] == after["without_meta_id_sha256"]
    assert before["value_sha256"] == after["value_sha256"]
    changed_rows = deepcopy(rows)
    changed_rows[0]["meta_value"] += "changed"
    changed = parsed(check("value replacement visible", invoke("capture", *args, data=fixture(manifest, changed_rows)[0])))
    assert changed["rows"][0]["without_meta_id_sha256"] != before["without_meta_id_sha256"]

    mutations = {
        "live origin": source.replace(hex_value("http://localhost:8090"), hex_value("https://thaisolitude.com")),
        "duplicate option": source + "option\thome\t" + hex_value("http://localhost:8090") + "\n",
        "missing option": "\n".join(line for line in source.splitlines() if not line.startswith("option\tsiteurl\t")) + "\n",
        "missing page": "\n".join(line for line in source.splitlines() if not line.startswith("page\t" + str(manifest["pages"][0]["id"]) + "\t")) + "\n",
        "changed title": source.replace(hex_value(manifest["pages"][0]["title"]), hex_value("PRIVATE_CHANGED_TITLE"), 1),
        "duplicate meta ID": source + "meta\t10\t99999\t61\t62\n",
        "malformed hex": source.replace(hex_value("PRIVATE_KEY_7c8a"), "PRIVATE_INVALID_HEX"),
        "invalid UTF8": source.replace(hex_value("PRIVATE_KEY_7c8a"), "FF"),
        "unknown row kind": source + "private\tPRIVATE_VALUE_9d2a\n",
        "extra column": source + "meta\t20\t99999\t61\t62\tPRIVATE_VALUE_9d2a\n",
        "invalid identity": source.replace("meta\t10\t", "meta\tPRIVATE_ID\t"),
    }
    for name, mutated in mutations.items():
        failure = check(name, invoke("capture", *args, data=mutated), False)
        assert not any(secret in failure.stdout + failure.stderr for secret in
                       ("PRIVATE_INVALID_HEX", "PRIVATE_VALUE_9d2a", "PRIVATE_CHANGED_TITLE", "PRIVATE_ID")), "failure echoed input"
    for phase, path, position in (("wrong-phase", target, "before"), (PHASE, "/not-reviewed/", "before"), (PHASE, target, "wrong-position")):
        check("capture context refusal", invoke("capture", MANIFEST, phase, path, position, data=source), False)

    with tempfile.TemporaryDirectory(prefix="peek-render-meta-test-") as directory:
        folder = Path(directory)
        captured = folder / "capture.log"
        captured.write_text(first.stdout)
        validation = check("validate exact capture", invoke("validate", captured, *args))
        assert PHASE in validation.stdout and target in validation.stdout
        assert "PRIVATE_" not in validation.stdout and MARKER not in validation.stdout
        for name, mutation in (
            ("wrong manifest hash", lambda value: value.update(manifest_sha256="a" * 64)),
            ("wrong row hash", lambda value: value["rows"][0].update(row_sha256="bad")),
            ("unexpected descriptor field", lambda value: value["rows"][0].update(raw="PRIVATE_VALUE_9d2a")),
            ("wrong key label", lambda value: value["rows"][0].update(known_key="PRIVATE_KEY_7c8a")),
            ("null inconsistency", lambda value: value["rows"][2].update(value_bytes=0)),
            ("duplicate descriptor", lambda value: value["rows"].append(deepcopy(value["rows"][0]))),
        ):
            corrupted = deepcopy(result)
            mutation(corrupted)
            captured.write_text(MARKER + json.dumps(corrupted) + "\n")
            invalid = check("validate " + name, invoke("validate", captured, *args), False)
            assert "PRIVATE_" not in invalid.stdout + invalid.stderr
        captured.write_text(first.stdout + first.stdout)
        check("duplicate marker refused", invoke("validate", captured, *args), False)
        captured.write_text(first.stdout)
        check("wrong capture context", invoke("validate", captured, MANIFEST, "original-before", target, "before"), False)
        changed_manifest = deepcopy(manifest)
        changed_manifest["pages"] = changed_manifest["pages"][:-1]
        changed_path = folder / "manifest.json"
        changed_path.write_text(json.dumps(changed_manifest))
        check("incomplete manifest", invoke("capture", changed_path, PHASE, target, "before", data=source), False)

    sql = check("read-only SQL", invoke("sql", "fixture_", MANIFEST)).stdout
    assert "fixture_options" in sql and "fixture_posts" in sql and "fixture_postmeta" in sql
    assert "HEX(" in sql and "ORDER BY" in sql
    assert all(statement.lstrip().upper().startswith("SELECT ") for statement in sql.split(";") if statement.strip())
    for prefix in ("", "bad`prefix", "x;DELETE FROM wp_posts", "../"):
        check("SQL prefix refusal", invoke("sql", prefix, MANIFEST), False)

    runner_source = (HERE.parents[3] / "scripts/ci/rehearse.sh").read_text()
    begin = runner_source.index("release_render_meta() {")
    capture_function = runner_source[begin:runner_source.index("\nrelease_original_render()", begin)]
    with tempfile.TemporaryDirectory(prefix="peek-render-meta-pipeline-") as directory:
        folder = Path(directory)
        fixture_input = folder / "synthetic-input.tsv"
        fixture_input.write_text(source)
        shim = r'''
set -uo pipefail
out=$1; fixture_mode=$2; fixture_input=$3; wwm=$4; release_manifest=$5; target=$6
prefix=fixture_; mkdir -p "$out"
python3() {
  local mode=$4 result
  command python3 "$@"; result=$?
  [ "$fixture_mode" != "$mode" ] || return 19
  return "$result"
}
sql() {
  cat > "$out/../sql-commands-$fixture_mode.txt"
  cat "$fixture_input"
  [ "$fixture_mode" != db ]
}
'''
        env = dict(os.environ)
        env.pop("BASH_ENV", None)
        for mode in ("none", "sql", "db", "capture", "validate"):
            output = folder / mode
            result = subprocess.run(["bash", "--noprofile", "--norc", "-s", "--", str(output), mode,
                                     str(fixture_input), str(HERE.parents[1]), str(MANIFEST), target],
                                    input=shim + capture_function + '\nrelease_render_meta original-after-rollback "$target" before\n',
                                    capture_output=True, text=True, env=env, timeout=10)
            check("capture pipeline " + mode, result, mode == "none")
            logs = list(output.glob("*.log"))
            assert len(logs) == 1, "one scoped descriptor file expected"
            retained = logs[0].read_text()
            assert retained.startswith(MARKER)
            assert "PRIVATE_" not in retained and hex_value(rows[1]["meta_value"]) not in retained, "raw SQL leaked to retained output"
            assert "PRIVATE_" not in result.stdout + result.stderr
    print(f"Peek render metadata diagnostics: {PASSED} offline controls passed; no database or network requests.")


if __name__ == "__main__":
    main()
