#!/usr/bin/env python3
"""Redacted, exact LIVE30 recovery-copy evidence; no WordPress bootstrap or writes."""
from copy import deepcopy
import importlib.util
import json
from pathlib import Path
import re
import sys

HERE = Path(__file__).resolve().parent
SPEC = importlib.util.spec_from_file_location("live30_cold_evidence", HERE / "cold-cache-evidence.py")
C = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(C)
require, encoded, digest, unique = C.require, C.encoded, C.digest, C.unique
SNAPSHOT_FILE = "peek-release-before-20261111-162615-e00022b6.json"
SNAPSHOT_PATH = "/home/customer/private-backups/" + SNAPSHOT_FILE
SNAPSHOT_SHA = "4038da0f9f77780ce355029bc87e53bff0479294711c821d253f51151e10751b"
SNAPSHOT_BYTES = 1682009
SQL_FILE = "live-before-peek-release-20261111-162615-74851.sql"
RECEIPT_SHA = "9202c8fb4e90babcba739a75660af944fc2a5adbfd1881278458312bc9ab9a0c"
ORIGINAL_APPLIED = "c1bb9082d44a3cf1aa151678101b2509417d9e1f338e60365e7c8066c02ce874"
APPLIED = "b34e86f75140a0e89b725e8dc9e56dd2fcc75e509155b768348bb3670daf4427"
IDS = {"thai-massage": 1339, "therapeutic-massage": 1340, "deep-tissue-massage": 1341, "chair-massage": 1342, "core": 1343}
STEPS = ("verify-applied", "verify-applied-after-render", "recoverable", "rollback",
         "verify-restored-after-render", "recovery-restored", "verify-restored")
STATES = ("applied", "applied", "recoverable", "restored", "restored", "restored", "restored")
LABELS = ("before-bootstrap",) + tuple("after-" + step for step in STEPS)
OPTIONS = ("__uagb_asset_version", "home", "siteurl")
ERROR = "FAIL live30 recovery evidence: invalid, incomplete, or out-of-scope evidence; no raw data retained"


def read_json(path):
    require(Path(path).is_file() and not Path(path).is_symlink())
    raw = Path(path).read_bytes()
    require(len(raw) <= 8 * 1024 * 1024)
    return raw, json.loads(raw, object_pairs_hook=unique)


def receipt(receipt_path, binding_path):
    raw, r = read_json(receipt_path)
    _, binding = read_json(binding_path)
    require(digest(raw) == RECEIPT_SHA and binding == {"schema": 1, "incident": "live30-assets-20261111", "receipt_sha256": digest(raw)})
    require(set(r) == {"schema", "incident", "bundle", "home", "manifest_sha256", "profile_sha256", "snapshot", "sql", "ids", "original_applied_sha256", "projected_applied_sha256", "removed", "added", "proof_sha256"})
    require(type(r["schema"]) is int and r["schema"] == 1 and r["incident"] == "live30-assets-20261111" and r["bundle"] == C.BUNDLE and r["home"] == C.ORIGIN)
    require(r["manifest_sha256"] == C.MANIFEST_SHA and r["profile_sha256"] == C.PROFILE_SHA and r["ids"] == IDS and all(type(v) is int for v in r["ids"].values()))
    require(r["snapshot"] == {"file": SNAPSHOT_FILE, "sha256": SNAPSHOT_SHA, "bytes": SNAPSHOT_BYTES})
    require(r["sql"] == {"file": SQL_FILE, "sha256": C.SQL_SHA, "bytes": 3919398})
    require(r["original_applied_sha256"] == ORIGINAL_APPLIED and r["projected_applied_sha256"] == APPLIED and C.is_sha(r["proof_sha256"]))
    require(set(r["removed"]) == set(C.COLD_ROWS) and set(r["added"]) == set(C.LIVE_IDS.values()))
    for meta_id, row in r["removed"].items():
        expected = C.COLD_ROWS[meta_id]
        require(row == {**expected, "post_id": int(expected["post_id"]), "meta_key": C.CACHE_KEY})
    for post_id, meta_id in C.LIVE_IDS.items():
        row = r["added"][meta_id]
        require(set(row) == {"post_id", "meta_key", "row_sha256", "value_sha256", "value_bytes"} and type(row["post_id"]) is int and row["post_id"] == int(post_id) and row["meta_key"] == C.CACHE_KEY)
        require((row["value_bytes"], row["value_sha256"]) == C.FINAL_VALUES[post_id] and type(row["value_bytes"]) is int and C.is_sha(row["row_sha256"]))
        require(digest(b"string:" + encoded(row["row_sha256"])) == C.LIVE_WITNESS_SHA[post_id])
    return digest(raw), r


def project(state, r):
    state = deepcopy(state)
    metadata = state["witnesses"]["postmeta"]
    require(isinstance(metadata, dict))
    for meta_id, row in r["removed"].items():
        require(metadata.get(meta_id) == row["row_sha256"])
        del metadata[meta_id]
    for meta_id, row in r["added"].items():
        require(meta_id not in metadata)
        metadata[meta_id] = row["row_sha256"]
    return state


def seal(data):
    data["evidence_sha256"] = digest(encoded({key: value for key, value in data.items() if key != "evidence_sha256"}))
    return data


def prepare(receipt_path, binding_path, journal_path):
    receipt_sha, r = receipt(receipt_path, binding_path)
    raw, journal = read_json(journal_path)
    require(Path(journal_path).name == SNAPSHOT_FILE and len(raw) == SNAPSHOT_BYTES and digest(raw) == SNAPSHOT_SHA)
    require(journal["schema"] == 1 and journal["complete"] is True and journal["home"] == C.ORIGIN and journal["bundle"] == C.BUNDLE and journal["ids"] == IDS)
    require(journal["manifest_sha256"] == C.MANIFEST_SHA and journal["profile_sha256"] == C.PROFILE_SHA)
    require(digest(encoded(journal["old"])) == journal["old_sha256"] and digest(encoded(journal["new"])) == journal["new_sha256"] == ORIGINAL_APPLIED)
    restored = digest(encoded(project(journal["old"], r)))
    require(digest(encoded(project(journal["new"], r))) == APPLIED and restored != APPLIED)
    options = {}
    for name in OPTIONS:
        row = journal["old"]["options"][name]
        require(row == journal["new"]["options"][name] and set(row) == set(C.COLUMNS["options"]) and all(value is None or isinstance(value, str) for value in row.values()))
        require(row["option_name"] == name and isinstance(row["option_value"], str))
        C.integer(row["option_id"])
        require(name == "__uagb_asset_version" or row["option_value"] == C.ORIGIN)
        options[name] = {"row_sha256": digest(encoded(row)), **C.value_descriptor(row["option_value"])}
    return seal({"schema": 1, "kind": "live30-recovery-context", "scope": "isolated-copy-only",
                 "receipt_sha256": receipt_sha, "proof_sha256": r["proof_sha256"], "snapshot": SNAPSHOT_PATH,
                 "snapshot_sha256": SNAPSHOT_SHA, "sql_sha256": C.SQL_SHA, "manifest_sha256": C.MANIFEST_SHA,
                 "profile_sha256": C.PROFILE_SHA, "ids": IDS, "applied_state_sha256": APPLIED,
                 "restored_state_sha256": restored, "cache_rows": r["added"], "options": options})


def check_context(data):
    require(isinstance(data, dict) and set(data) == {"schema", "kind", "scope", "receipt_sha256", "proof_sha256", "snapshot", "snapshot_sha256", "sql_sha256", "manifest_sha256", "profile_sha256", "ids", "applied_state_sha256", "restored_state_sha256", "cache_rows", "options", "evidence_sha256"})
    require(type(data["schema"]) is int and data["schema"] == 1 and data["kind"] == "live30-recovery-context" and data["scope"] == "isolated-copy-only")
    require(data["snapshot"] == SNAPSHOT_PATH and data["snapshot_sha256"] == SNAPSHOT_SHA and data["sql_sha256"] == C.SQL_SHA and data["manifest_sha256"] == C.MANIFEST_SHA and data["profile_sha256"] == C.PROFILE_SHA and data["ids"] == IDS and all(type(v) is int for v in data["ids"].values()))
    require(data["receipt_sha256"] == RECEIPT_SHA and data["applied_state_sha256"] == APPLIED and data["restored_state_sha256"] != APPLIED and all(C.is_sha(data[key]) for key in ("receipt_sha256", "proof_sha256", "restored_state_sha256")))
    require(set(data["cache_rows"]) == set(C.LIVE_IDS.values()) and set(data["options"]) == set(OPTIONS))
    for post, meta in C.LIVE_IDS.items():
        row = data["cache_rows"][meta]
        require(set(row) == {"post_id", "meta_key", "row_sha256", "value_sha256", "value_bytes"} and row["post_id"] == int(post) and type(row["post_id"]) is int and row["meta_key"] == C.CACHE_KEY and (row["value_bytes"], row["value_sha256"]) == C.FINAL_VALUES[post] and type(row["value_bytes"]) is int)
        require(C.is_sha(row["row_sha256"]) and digest(b"string:" + encoded(row["row_sha256"])) == C.LIVE_WITNESS_SHA[post])
    for row in data["options"].values():
        require(set(row) == {"row_sha256", "value_type", "value_bytes", "value_sha256"} and C.is_sha(row["row_sha256"]))
        C.check_value(row)
        require(row["value_type"] == "string")
    require(data["evidence_sha256"] == digest(encoded({key: value for key, value in data.items() if key != "evidence_sha256"})))
    return data


def load(path, context=False):
    raw, data = read_json(path)
    require(raw.endswith(b"\n") and raw.count(b"\n") == 1)
    return check_context(data) if context else data


def sql(prefix, context):
    check_context(context)
    require(re.fullmatch(r"[A-Za-z0-9_]+", prefix))
    statements = ["SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;", "START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY;"]
    for table in ("postmeta", "options"):
        statements.append(f"SELECT 'schema','{table}',ORDINAL_POSITION,HEX(COLUMN_NAME) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{prefix}{table}' ORDER BY ORDINAL_POSITION;")
    for table, where in (("postmeta", "meta_id IN (4701,4702,5089,5090,5091)"), ("options", "option_name IN ('__uagb_asset_version','home','siteurl')")):
        cells = ",".join(f"IFNULL(HEX(CAST(`{column}` AS CHAR CHARACTER SET utf8mb4)),'NULL')" for column in C.COLUMNS[table])
        statements.append(f"SELECT 'row','{table}',{cells} FROM `{prefix}{table}` WHERE {where} ORDER BY `{C.KEYS[table][0]}`;")
        statements.append(f"SELECT 'count','{table}',COUNT(*) FROM `{prefix}{table}` WHERE {where};")
    statements.extend((f"SELECT 'end','{context['receipt_sha256']}';", "COMMIT;"))
    return "\n".join(statements)


def capture(label, context, stream):
    check_context(context)
    require(label in LABELS)
    schemas, counts, rows, ended, size = {}, {}, {"postmeta": [], "options": []}, False, 0
    for index, line in enumerate(stream):
        size += len(line)
        require(index < 100 and size < 1024 * 1024 and line.endswith("\n") and not ended)
        parts = line[:-1].split("\t")
        if parts[0] == "schema" and len(parts) == 4:
            _, table, ordinal, name = parts
            require(table in rows and not any(rows.values()) and not counts)
            names = schemas.setdefault(table, [])
            require(C.integer(ordinal) == len(names) + 1)
            names.append(C.unhex(name))
        elif parts[0] == "row" and len(parts) == 6:
            table = parts[1]
            require(table in rows and table not in counts and set(schemas) == set(rows) and all(len(schemas[t]) == len(C.COLUMNS[t]) and set(schemas[t]) == set(C.COLUMNS[t]) for t in rows))
            row = dict(zip(C.COLUMNS[table], map(C.unhex, parts[2:])))
            _, key = C.identity(table, row)
            require(not rows[table] or key > C.identity(table, rows[table][-1])[1])
            rows[table].append(row)
        elif parts[0] == "count" and len(parts) == 3:
            _, table, count = parts
            require(table in rows and table not in counts and C.integer(count, True) == len(rows[table]))
            counts[table] = len(rows[table])
        elif parts == ["end", context["receipt_sha256"]]:
            require(counts == {"postmeta": 3, "options": 3})
            ended = True
        else:
            raise ValueError("invalid SQL")
    require(ended and [row["meta_id"] for row in rows["postmeta"]] == ["5089", "5090", "5091"])
    actual_options = {}
    for row in rows["options"]:
        name = row["option_name"]
        require(name in OPTIONS and name not in actual_options)
        require(name == "__uagb_asset_version" or row["option_value"] == C.ORIGIN)
        actual_options[name] = {"row_sha256": digest(encoded(row)), **C.value_descriptor(row["option_value"])}
    require(actual_options == context["options"])
    actual_rows = {}
    global_version = next(row["option_value"] for row in rows["options"] if row["option_name"] == "__uagb_asset_version")
    for row in rows["postmeta"]:
        meta_id = row["meta_id"]
        expected = context["cache_rows"][meta_id]
        require(row["post_id"] == str(expected["post_id"]) and row["meta_key"] == C.CACHE_KEY and isinstance(row["meta_value"], str))
        actual_rows[meta_id] = {"post_id": int(row["post_id"]), "meta_key": row["meta_key"], "row_sha256": digest(encoded(row)), "value_sha256": digest(row["meta_value"].encode()), "value_bytes": len(row["meta_value"].encode())}
        require(actual_rows[meta_id] == expected and C.version_description(row["meta_value"], global_version)["matches_global"] is True)
    return seal({"schema": 1, "kind": "live30-recovery-sql", "scope": "isolated-copy-only", "label": label,
                 "context_sha256": context["evidence_sha256"], "cache_rows": actual_rows, "options": actual_options})


def expected_incident(step, context):
    require(step in STEPS)
    index = STEPS.index(step)
    return {"schema": 1, "bundle": C.BUNDLE, "home": C.ORIGIN, "state": STATES[index],
            "snapshot": SNAPSHOT_PATH, "snapshot_sha256": SNAPSHOT_SHA, "manifest_sha256": C.MANIFEST_SHA,
            "profile_sha256": C.PROFILE_SHA, "state_sha256": context["applied_state_sha256"] if index < 3 else context["restored_state_sha256"],
            "complete": True, "ids": IDS, "receipt_sha256": context["receipt_sha256"], "sql_sha256": C.SQL_SHA, "cache_delta_retained": True}


def marker(step, context, stream):
    check_context(context)
    expected = expected_incident(step, context)
    manifest = (HERE / "manifest.json").read_bytes()
    require(digest(manifest) == C.MANIFEST_SHA)
    spectra = json.loads(manifest)["spectra"]
    expected_source = {"files": spectra["files"], "header_version": "2.20.6", "runtime_version": "2.20.6", "loaded": {name: True for name in ("main", "UAGB_Helper", "UAGB_Rest_API", "UAGB_Loader", "UAGB_Post_Assets", "UAGB_Front_Assets")}, "save_post_priority_10": True}
    success = "Success: LIVE30 " + expected["state"] + ": exact release state with the proved external cache delta retained; historical database identity is not claimed. Original journal unchanged."
    deprecation = re.compile(r"^(?:PHP )?Deprecated: +Using null as an array offset is deprecated, use an empty string instead in phar:///usr/local/bin/wp/vendor/wp-cli/php-cli-tools/lib/cli/Colors\.php on line 95$")
    incidents, sources, successes, unknown, size = [], [], 0, [], 0
    for index, line in enumerate(stream):
        size += len(line)
        require(index < 10000 and size < 8 * 1024 * 1024 and line.endswith("\n"))
        line = line[:-1]
        if not line.strip() or deprecation.fullmatch(line):
            continue
        if line == success:
            successes += 1
            continue
        try:
            if line.startswith("RELEASE-INCIDENT "):
                incidents.append(json.loads(line.split(" ", 1)[1], object_pairs_hook=unique))
                continue
            if line.startswith("RELEASE-SPECTRA-SOURCE "):
                sources.append(json.loads(line.split(" ", 1)[1], object_pairs_hook=unique))
                continue
        except (ValueError, TypeError):
            pass
        unknown.append({"bytes": len(line.encode()), "sha256": digest(line.encode())})
    # Canonical byte equality also distinguishes bools from JSON integers.
    valid = len(incidents) == len(sources) == successes == 1 and not unknown and encoded(incidents[0]) == encoded(expected) and encoded(sources[0]) == encoded(expected_source)
    return seal({"schema": 1, "kind": "live30-recovery-marker", "scope": "isolated-copy-only", "step": step,
                 "context_sha256": context["evidence_sha256"], "valid": bool(valid), "incident": expected if valid else None,
                 "spectra_source_bound": len(sources) == 1 and encoded(sources[0]) == encoded(expected_source),
                 "unexpected_lines": unknown})


def verify(context, directory):
    check_context(context)
    observations, markers = [], []
    for label in LABELS:
        data = load(Path(directory) / (label + ".json"))
        expected = seal({"schema": 1, "kind": "live30-recovery-sql", "scope": "isolated-copy-only", "label": label, "context_sha256": context["evidence_sha256"], "cache_rows": context["cache_rows"], "options": context["options"]})
        require(encoded(data) == encoded(expected))
        observations.append(data["evidence_sha256"])
    for step in STEPS:
        data = load(Path(directory) / (step + ".marker.json"))
        expected = seal({"schema": 1, "kind": "live30-recovery-marker", "scope": "isolated-copy-only", "step": step, "context_sha256": context["evidence_sha256"], "valid": True, "incident": expected_incident(step, context), "spectra_source_bound": True, "unexpected_lines": []})
        require(encoded(data) == encoded(expected))
        markers.append(data["evidence_sha256"])
    return {"schema": 1, "scope": "isolated-copy-only", "result": "seven-mode-checks-and-eight-unchanged-sql-observations", "context_sha256": context["evidence_sha256"], "receipt_sha256": context["receipt_sha256"], "snapshot_sha256": SNAPSHOT_SHA, "applied_state_sha256": APPLIED, "restored_state_sha256": context["restored_state_sha256"], "observation_sha256": observations, "marker_sha256": markers, "cache_delta_retained": True}


def main(args):
    command = args[0] if args else ""
    valid = True
    if command == "prepare" and len(args) == 4:
        result = prepare(*args[1:])
    elif command == "sql" and len(args) == 3:
        print(sql(args[1], load(args[2], True)))
        return 0
    elif command == "capture" and len(args) == 3:
        result = capture(args[1], load(args[2], True), sys.stdin)
    elif command == "marker" and len(args) == 3:
        result = marker(args[1], load(args[2], True), sys.stdin)
        valid = result["valid"]
    elif command == "verify" and len(args) == 3:
        result = verify(load(args[1], True), args[2])
    else:
        raise ValueError("invalid command")
    print(encoded(result).decode())
    return 0 if valid else 1


if __name__ == "__main__":
    try:
        sys.exit(main(sys.argv[1:]))
    except Exception:
        print(ERROR, file=sys.stderr)
        sys.exit(1)
