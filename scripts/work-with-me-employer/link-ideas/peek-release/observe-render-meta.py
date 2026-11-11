#!/usr/bin/env python3
"""Read-only copied-database metadata evidence; never print raw input or values.

The runner pipes SQL output directly here without bootstrapping WordPress. Only
canonical hash descriptors are retained; no baseline or rollback state changes.
"""
import hashlib
import json
from pathlib import Path
import re
import sys


BUNDLE = "peek-visitor-release-v1"
ORIGIN = "http://localhost:8090"
PHASES = {"original-before", "original-after-rollback", "original-after-browser"}
MARKER = "RELEASE-RENDER-META "
ROW_KEYS = {"meta_id", "post_id", "public_page", "key_sha256", "known_key", "value_type",
            "value_bytes", "value_sha256", "row_sha256", "without_meta_id_sha256"}


def require(value, reason):
    if not value:
        raise ValueError(reason)


def unique(pairs):
    result = {}
    for key, value in pairs:
        require(key not in result, "duplicate JSON key")
        result[key] = value
    return result


def encoded(value):
    return json.dumps(value, ensure_ascii=False, sort_keys=True, separators=(",", ":")).encode("utf-8")


def digest(value):
    return hashlib.sha256(value).hexdigest()


def read_manifest(filename):
    raw = Path(filename).read_bytes()
    value = json.loads(raw, object_pairs_hook=unique)
    require(value.get("schema") == 1 and value.get("bundle") == BUNDLE and
            value.get("copy_mapping", {}).get("origin") == ORIGIN, "manifest scope differs")
    pages = value.get("pages")
    require(isinstance(pages, list) and len(pages) == 15, "manifest inventory count differs")
    require(all(isinstance(p, dict) and set(p) == {"id", "path", "title"} and
                type(p["id"]) is int and p["id"] > 0 and isinstance(p["title"], str) and
                isinstance(p["path"], str) and re.fullmatch(r"/(?:[^?#\s]*/)?", p["path"]) and
                not p["path"].startswith("//") for p in pages), "manifest page shape differs")
    require(len({p["id"] for p in pages}) == 15 and len({p["path"] for p in pages}) == 15,
            "manifest pages are not unique")
    return digest(raw), {p["id"]: p for p in pages}


def context(manifest, phase, path, position):
    sha, pages = read_manifest(manifest)
    require(phase in PHASES and position in {"before", "after"}, "unsupported capture position")
    require(path in {p["path"] for p in pages.values()}, "capture route is outside the manifest")
    return sha, pages


def unhex(value):
    if value == "NULL":
        return None
    require(re.fullmatch(r"(?:[0-9A-F]{2})*", value) is not None, "invalid encoded SQL field")
    return bytes.fromhex(value).decode("utf-8", errors="strict")


def integer(value, zero=False):
    require(re.fullmatch(r"0|[1-9][0-9]*" if zero else r"[1-9][0-9]*", value) is not None,
            "invalid numeric SQL identity")
    return int(value)


def capture(manifest, phase, path, position, stream):
    sha, expected = context(manifest, phase, path, position)
    options, pages, rows = {}, {}, []
    last_id = 0
    for index, line in enumerate(stream):
        require(index < 100000 and len(line) <= 4 * 1024 * 1024 and line.endswith("\n"),
                "SQL evidence exceeds bounds or is incomplete")
        parts = line[:-1].split("\t")
        if parts[0] == "option" and len(parts) == 3:
            key = parts[1]
            require(key in {"home", "siteurl", "page_on_front"} and key not in options,
                    "unexpected or duplicate origin option")
            options[key] = unhex(parts[2])
        elif parts[0] == "page" and len(parts) == 5:
            identity = integer(parts[1])
            require(identity not in pages, "duplicate published page")
            pages[identity] = {"slug": unhex(parts[2]), "parent": integer(parts[3], True), "title": unhex(parts[4])}
        elif parts[0] == "meta" and len(parts) == 5:
            identity, post = integer(parts[1]), integer(parts[2], True)
            require(identity > last_id, "metadata identities are not strictly ordered")
            last_id = identity
            key, value = unhex(parts[3]), unhex(parts[4])
            raw = {"meta_id": parts[1], "post_id": parts[2], "meta_key": key, "meta_value": value}
            rows.append({"meta_id": parts[1], "post_id": parts[2], "public_page": post in expected,
                         "key_sha256": digest(key.encode()) if key is not None else None,
                         "known_key": key if key == "_uag_page_assets" else None,
                         "value_type": "null" if value is None else "string",
                         "value_bytes": len(value.encode()) if value is not None else None,
                         "value_sha256": digest(value.encode()) if value is not None else None,
                         "row_sha256": digest(encoded(raw)),
                         "without_meta_id_sha256": digest(encoded({k: v for k, v in raw.items() if k != "meta_id"}))})
        else:
            raise ValueError("unexpected SQL evidence row")
    require(set(options) == {"home", "siteurl", "page_on_front"} and
            options["home"] == options["siteurl"] == ORIGIN, "copied database origin differs")
    require(isinstance(options["page_on_front"], str), "front-page identity is missing")
    front = integer(options["page_on_front"])
    require(set(pages) == set(expected) and front in pages, "published page identities differ")
    for identity, page in pages.items():
        parts, seen, current = [], set(), identity
        while current:
            require(current in pages and current not in seen, "published page ancestry differs")
            seen.add(current)
            node = pages[current]
            require(isinstance(node["slug"], str) and node["slug"] and "/" not in node["slug"], "invalid page slug")
            parts.insert(0, node["slug"])
            current = node["parent"]
        actual_path = "/" if identity == front else "/" + "/".join(parts) + "/"
        require(actual_path == expected[identity]["path"] and page["title"] == expected[identity]["title"],
                "published page route or title differs")
    require(rows, "metadata evidence is empty")
    return {"schema": 1, "bundle": BUNDLE, "home": ORIGIN, "manifest_sha256": sha,
            "phase": phase, "path": path, "position": position, "rows": rows,
            "rows_sha256": digest(encoded(rows))}


def validate(filename, manifest, phase, path, position):
    sha, pages = context(manifest, phase, path, position)
    text = Path(filename).read_text()
    require(text.startswith(MARKER) and text.endswith("\n") and text.count("\n") == 1, "capture marker is missing or ambiguous")
    data = json.loads(text[len(MARKER):], object_pairs_hook=unique)
    require(set(data) == {"schema", "bundle", "home", "manifest_sha256", "phase", "path", "position", "rows", "rows_sha256"},
            "capture shape differs")
    require(data["schema"] == 1 and data["bundle"] == BUNDLE and data["home"] == ORIGIN and
            data["manifest_sha256"] == sha and data["phase"] == phase and data["path"] == path and
            data["position"] == position, "capture binding differs")
    require(isinstance(data["rows"], list) and 0 < len(data["rows"]) < 100000, "capture rows are empty or oversized")
    last = 0
    for row in data["rows"]:
        require(isinstance(row, dict) and set(row) == ROW_KEYS, "capture descriptor shape differs")
        identity, post = integer(row["meta_id"]), integer(row["post_id"], True)
        require(identity > last and type(row["public_page"]) is bool and row["public_page"] == (post in pages), "capture identity differs")
        last = identity
        require(row["known_key"] in {None, "_uag_page_assets"} and row["value_type"] in {"null", "string"}, "capture descriptor type differs")
        for name in ("key_sha256", "value_sha256", "row_sha256", "without_meta_id_sha256"):
            require((row[name] is None and name in {"key_sha256", "value_sha256"}) or
                    (isinstance(row[name], str) and re.fullmatch("[a-f0-9]{64}", row[name])), "capture digest differs")
        require(row["known_key"] is None or row["key_sha256"] == digest(b"_uag_page_assets"), "known key binding differs")
        require((row["value_type"] == "null" and row["value_bytes"] is None and row["value_sha256"] is None) or
                (row["value_type"] == "string" and type(row["value_bytes"]) is int and row["value_bytes"] >= 0 and row["value_sha256"] is not None),
                "capture null/string description differs")
    require(data["rows_sha256"] == digest(encoded(data["rows"])), "capture rows digest differs")
    print(f"INFO release metadata: {phase} {position} {path}; rows={len(data['rows'])}; sha256={data['rows_sha256']}")


def main(args):
    command = args.pop(0) if args else ""
    if command == "sql" and len(args) == 2:
        prefix, manifest = args
        read_manifest(manifest)
        require(re.fullmatch(r"[A-Za-z0-9_]+", prefix) is not None, "SQL prefix is invalid")
        print(f"SELECT 'option',option_name,HEX(option_value) FROM `{prefix}options` WHERE option_name IN ('home','siteurl','page_on_front') ORDER BY option_name;")
        print(f"SELECT 'page',ID,HEX(post_name),post_parent,HEX(post_title) FROM `{prefix}posts` WHERE post_type='page' AND post_status='publish' ORDER BY ID;")
        print(f"SELECT 'meta',meta_id,post_id,HEX(meta_key),HEX(meta_value) FROM `{prefix}postmeta` ORDER BY meta_id;")
    elif command == "capture" and len(args) == 4:
        print(MARKER + encoded(capture(*args, sys.stdin)).decode())
    elif command == "validate" and len(args) == 5:
        validate(*args)
    else:
        raise ValueError("unsupported observer command")


if __name__ == "__main__":
    try:
        main(sys.argv[1:])
    except Exception:
        # Raw SQL, JSON parser fragments, paths and unknown exception messages
        # could contain copied values. All failures therefore use fixed text.
        print("FAIL release metadata: observation invalid or incomplete; no raw data retained", file=sys.stderr)
        sys.exit(1)
