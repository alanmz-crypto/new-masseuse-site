#!/usr/bin/env python3
"""SQL-only evidence for the exact LIVE30 pre-install dump in an isolated copy.

Raw SQL rows exist only in the input pipe and memory. Every column of the six
installer-protected tables and every options row is hashed. Only the ten
installer-protected options participate in the protected equality claim;
changes to other options are reported separately, with their names hashed.
This observer neither bootstraps WordPress nor changes a database or journal.
"""
import hashlib
import json
from pathlib import Path
import re
import sys


BUNDLE = "peek-visitor-release-v1"
ORIGIN = "http://thaisolitude.com"
MANIFEST_SHA = "5255d85c0d64cfe4bd4eed740f4273f993940e013a715f071cfe3eef9b8269b5"
PROFILE_SHA = "49ed31542554634dcd945d8048a116e6dd3ae61635d1ad47a2a6ae56df6c3cac"
SQL_SHA = "4f27b4644dca8a85a671e8cbad1326515e49c356314c3e81327eeba46dd032a0"
PHASES = ("cold-import", "after-bootstrap", "first-1254", "first-1255", "first-1257",
          "repeat-1254", "repeat-1255", "repeat-1257")
TARGETS = ("1254", "1255", "1257")
CACHE_KEY = "_uag_page_assets"
FINAL_VALUES = {
    "1254": (740, "30f619157647f3a5cd68219525b5b12a8dc210e828f0a130cf4f3fc9ed7d76ba"),
    "1255": (740, "30f619157647f3a5cd68219525b5b12a8dc210e828f0a130cf4f3fc9ed7d76ba"),
    "1257": (693, "65156d9a7ff627f3726c259b781bbb272f6b3bc0eed91220fc6db1e65303bff6"),
}
COLD_ROWS = {
    "4701": {"post_id": "1255", "value_bytes": 735,
             "value_sha256": "a5e39e98f6d20e1f0f41994f0ee66c28ad6d882c09a2cf2f14b62f5534dd7e7d",
             "row_sha256": "8a204a7db18edf9be297689861b987ec19a2cb373d65fb5b7a8ffab4419b2fe0"},
    "4702": {"post_id": "1257", "value_bytes": 688,
             "value_sha256": "07565e74285b1b9a9ec702f536431fc67560c54775411ed596d8ce48b37b37bb",
             "row_sha256": "80efacd2d6270111b8e880f396bf42e5fc77dcab94a44e8adb7e1354f2bbbef3"},
}
LIVE_IDS = {"1254": "5089", "1255": "5090", "1257": "5091"}
# These are installer diagnostic descriptors of a string containing a row SHA,
# not raw row SHA values: sha256('string:' + canonical_json(row_sha_string)).
LIVE_WITNESS_SHA = {
    "1254": "7c7d3169447a31ae354e397d24c88d6397c98a5abd28ef203bd49ee5b7d7fb66",
    "1255": "5e5c40155d90ff4c5a85054fde59fe320e08602563e1df03f529e1a90b80929d",
    "1257": "cb764b12ffa1feec471e0ab9d39dff278243378b775f123a8fece64af85e57f2",
}
OPTION_NAMES = tuple(sorted(("home", "siteurl", "page_on_front", "page_for_posts",
                            "permalink_structure", "show_on_front", "stylesheet",
                            "template", "astra-settings", "__uagb_asset_version")))
# An extra, missing, or renamed column refuses the observation, rather than
# silently omitting data. SELECT order is fixed independently of physical order.
COLUMNS = {
    "posts": "ID post_author post_date post_date_gmt post_content post_title post_excerpt post_status comment_status ping_status post_password post_name to_ping pinged post_modified post_modified_gmt post_content_filtered post_parent guid menu_order post_type post_mime_type comment_count".split(),
    "postmeta": "meta_id post_id meta_key meta_value".split(),
    "term_relationships": "object_id term_taxonomy_id term_order".split(),
    "term_taxonomy": "term_taxonomy_id term_id taxonomy description parent count".split(),
    "terms": "term_id name slug term_group".split(),
    "termmeta": "meta_id term_id meta_key meta_value".split(),
    "options": "option_id option_name option_value autoload".split(),
}
KEYS = {"posts": ("ID",), "postmeta": ("meta_id",),
        "term_relationships": ("object_id", "term_taxonomy_id"),
        "term_taxonomy": ("term_taxonomy_id",), "terms": ("term_id",),
        "termmeta": ("meta_id",), "options": ("option_id",)}
TABLES = tuple(name for name in COLUMNS if name != "options")
ERROR = "FAIL live30 cold evidence: invalid, incomplete, or out-of-scope evidence; no raw data retained"


def require(value):
    if not value:
        raise ValueError("invalid evidence")


def unique(pairs):
    value = {}
    for key, item in pairs:
        require(key not in value)
        value[key] = item
    return value


def encoded(value):
    # Same bytes as the installer's sorted string/null rows and unescaped JSON.
    return json.dumps(value, ensure_ascii=False, sort_keys=True, separators=(",", ":")).encode("utf-8")


def digest(value):
    return hashlib.sha256(value).hexdigest()


def is_sha(value):
    return isinstance(value, str) and re.fullmatch(r"[a-f0-9]{64}", value) is not None


def integer(value, zero=False):
    require(isinstance(value, str) and re.fullmatch(r"0|[1-9][0-9]*" if zero else r"[1-9][0-9]*", value))
    return int(value)


def unhex(value):
    if value == "NULL":
        return None
    require(re.fullmatch(r"(?:[0-9A-F]{2})*", value) is not None)
    return bytes.fromhex(value).decode("utf-8", errors="strict")


def bindings(manifest, profile):
    manifest_raw, profile_raw = Path(manifest).read_bytes(), Path(profile).read_bytes()
    require(digest(manifest_raw) == MANIFEST_SHA and digest(profile_raw) == PROFILE_SHA)
    m, p = (json.loads(raw, object_pairs_hook=unique) for raw in (manifest_raw, profile_raw))
    require(m["schema"] == p["schema"] == 1 and m["bundle"] == p["bundle"] == BUNDLE)
    require(p["origin"] == ORIGIN and m["source"] == p["source"])
    pages = m["pages"]
    require(isinstance(pages, list) and len(pages) == 15)
    require(all(set(row) == {"id", "path", "title"} and type(row["id"]) is int and
                row["id"] > 0 and isinstance(row["title"], str) and isinstance(row["path"], str)
                and re.fullmatch(r"/(?:[^?#\s]*/)?", row["path"]) and not row["path"].startswith("//")
                for row in pages))
    require(len({row["id"] for row in pages}) == len({row["path"] for row in pages}) == 15)
    options = p["baseline"]["option_sha256"]
    require(set(options) == set(OPTION_NAMES) - {"__uagb_asset_version"} and all(is_sha(v) for v in options.values()))
    require(set(m["baseline"]["option_sha256"]) == set(options))
    return {row["id"]: row for row in pages}, options


def sql(prefix, manifest, profile):
    bindings(manifest, profile)
    require(re.fullmatch(r"[A-Za-z0-9_]+", prefix) is not None)
    statements = ["SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ;",
                  "START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY;"]
    for table in COLUMNS:
        statements.append(f"SELECT 'schema','{table}',ORDINAL_POSITION,HEX(COLUMN_NAME) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='{prefix}{table}' ORDER BY ORDINAL_POSITION;")
    for table, columns in COLUMNS.items():
        cells = ",".join(f"IFNULL(HEX(CAST(`{column}` AS CHAR CHARACTER SET utf8mb4)),'NULL')" for column in columns)
        order = ",".join(f"`{column}`" for column in KEYS[table])
        statements.append(f"SELECT 'row','{table}',{cells} FROM `{prefix}{table}` ORDER BY {order};")
        statements.append(f"SELECT 'count','{table}',COUNT(*) FROM `{prefix}{table}`;")
    statements.extend((f"SELECT 'end','{SQL_SHA}';", "COMMIT;"))
    return "\n".join(statements)


class PrimitiveSerialization:
    """Bounded PHP primitive/array grammar; object and reference tags refuse.

    This is deliberately not an unserializer with object hooks. Arrays retain
    their keys, and only a scalar top-level uag_version is ever described.
    """
    def __init__(self, value):
        self.raw, self.pos, self.nodes = value.encode(), 0, 0

    def take(self, count):
        require(0 <= count <= len(self.raw) - self.pos)
        value = self.raw[self.pos:self.pos + count]
        self.pos += count
        return value

    def until(self, delimiter):
        end = self.raw.find(delimiter, self.pos)
        require(end >= self.pos and end - self.pos < 30)
        value = self.raw[self.pos:end]
        self.pos = end + len(delimiter)
        return value

    def parse(self, depth=0):
        self.nodes += 1
        require(depth < 24 and self.nodes <= 20000)
        tag = self.take(1)
        if tag == b"N":
            require(self.take(1) == b";")
            return None
        require(self.take(1) == b":")
        if tag == b"s":
            raw_length = self.until(b":")
            require(re.fullmatch(rb"0|[1-9][0-9]*", raw_length))
            length = int(raw_length)
            require(self.take(1) == b'"')
            value = self.take(length).decode("utf-8", errors="strict")
            require(self.take(2) == b'";')
            return value
        if tag in {b"i", b"b", b"d"}:
            value = self.until(b";")
            if tag == b"b":
                require(value in {b"0", b"1"})
                return value == b"1"
            if tag == b"i":
                require(re.fullmatch(rb"-?(?:0|[1-9][0-9]*)", value))
                return int(value)
            require(re.fullmatch(rb"-?(?:[0-9]+(?:\.[0-9]*)?|\.[0-9]+)(?:[Ee][+-]?[0-9]+)?", value))
            return float(value)
        require(tag == b"a")
        raw_count = self.until(b":")
        require(re.fullmatch(rb"0|[1-9][0-9]*", raw_count))
        count = int(raw_count)
        require(count <= 10000 and self.take(1) == b"{")
        value = {}
        for _ in range(count):
            key = self.parse(depth + 1)
            require(type(key) in {str, int} and key not in value)
            value[key] = self.parse(depth + 1)
        require(self.take(1) == b"}")
        return value


def version_description(value, global_version):
    result = {"status": "unparsed", "type": None, "sha256": None, "matches_global": None}
    if value is None:
        return result
    try:
        parser = PrimitiveSerialization(value)
        parsed = parser.parse()
        require(parser.pos == len(parser.raw) and isinstance(parsed, dict))
        version = parsed.get("uag_version")
        require(type(version) in {str, int})
        result = {"status": "scalar", "type": "string" if type(version) is str else "integer",
                  "sha256": digest(str(version).encode()), "matches_global": str(version) == global_version}
    except (ValueError, UnicodeError, OverflowError):
        pass
    return result


def value_descriptor(value):
    return {"value_type": "null" if value is None else "string",
            "value_bytes": None if value is None else len(value.encode()),
            "value_sha256": None if value is None else digest(value.encode())}


def identity(table, row):
    values = tuple(integer(row[key], table == "term_relationships") for key in KEYS[table])
    return ":".join(row[key] for key in KEYS[table]), values


def check_pages(posts, options, expected):
    require(options["home"]["option_value"] == options["siteurl"]["option_value"] == ORIGIN)
    front = integer(options["page_on_front"]["option_value"])
    pages = {int(row["ID"]): row for row in posts if row["post_type"] == "page" and row["post_status"] == "publish"}
    require(set(pages) == set(expected) and front in pages)
    for page_id, row in pages.items():
        parts, seen, current = [], set(), page_id
        while current:
            require(current in pages and current not in seen)
            seen.add(current)
            node = pages[current]
            slug = node["post_name"]
            require(isinstance(slug, str) and slug and "/" not in slug)
            parts.insert(0, slug)
            current = integer(node["post_parent"], True)
        path = "/" if page_id == front else "/" + "/".join(parts) + "/"
        require(expected[page_id]["path"] == path and expected[page_id]["title"] == row["post_title"])


def protected_state(data):
    return {"tables": data["tables"], "options": data["protected_options"]}


def projection(row):
    post = row["post_id"]
    value = row["meta_value"]
    if row["meta_key"] != CACHE_KEY or post not in TARGETS or value is None or (len(value.encode()), digest(value.encode())) != FINAL_VALUES[post]:
        return None
    sha = digest(encoded({**row, "meta_id": LIVE_IDS[post]}))
    return {"basis": "regenerated-copy-value-with-substituted-live-id", "substituted_meta_id": LIVE_IDS[post],
            "row_sha256": sha, "witness_descriptor_sha256": digest(b"string:" + encoded(sha))}


def check_cold_metadata(metadata):
    selected = {row["meta_id"]: row for row in metadata if row["known_key"] == CACHE_KEY and row["post_id"] in TARGETS}
    require(set(selected) == set(COLD_ROWS))
    for meta_id, fields in COLD_ROWS.items():
        row = selected[meta_id]
        require(all(row[key] == value for key, value in fields.items()) and
                row["cache_version"]["status"] == "scalar" and row["cache_version"]["matches_global"] is False)


def capture(phase, manifest, profile, stream):
    require(phase in PHASES)
    pages, expected_options = bindings(manifest, profile)
    schemas, counts = {}, {}
    rows = {table: [] for table in COLUMNS}
    previous, ended, total_bytes = {}, False, 0
    for index, line in enumerate(stream):
        total_bytes += len(line)
        require(index < 200000 and len(line) <= 8 * 1024 * 1024 and total_bytes <= 256 * 1024 * 1024)
        require(line.endswith("\n") and not ended)
        parts = line[:-1].split("\t")
        tag = parts[0]
        if tag == "schema" and len(parts) == 4:
            _, table, ordinal, name = parts
            require(table in COLUMNS and not any(rows.values()) and not counts)
            columns = schemas.setdefault(table, [])
            require(integer(ordinal) == len(columns) + 1)
            columns.append(unhex(name))
        elif tag == "row" and len(parts) >= 3:
            table = parts[1]
            require(table in COLUMNS and len(parts) == len(COLUMNS[table]) + 2 and table not in counts)
            require(set(schemas) == set(COLUMNS) and all(len(schemas[t]) == len(COLUMNS[t]) and set(schemas[t]) == set(COLUMNS[t]) for t in COLUMNS))
            row = dict(zip(COLUMNS[table], map(unhex, parts[2:])))
            _, order = identity(table, row)
            require(table not in previous or order > previous[table])
            previous[table] = order
            rows[table].append(row)
        elif tag == "count" and len(parts) == 3:
            _, table, count = parts
            require(table in COLUMNS and table not in counts and integer(count, True) == len(rows[table]))
            counts[table] = len(rows[table])
        elif tag == "end" and parts == ["end", SQL_SHA]:
            require(set(counts) == set(COLUMNS))
            ended = True
        else:
            raise ValueError("invalid evidence")
    require(ended and set(schemas) == set(COLUMNS) and all(set(schemas[t]) == set(COLUMNS[t]) and len(schemas[t]) == len(COLUMNS[t]) for t in COLUMNS))
    options = {}
    for row in rows["options"]:
        name = row["option_name"]
        require(isinstance(name, str) and name not in options)
        options[name] = row
    require(set(OPTION_NAMES) <= set(options))
    check_pages(rows["posts"], options, pages)
    require(all(digest(encoded(options[name])) == sha for name, sha in expected_options.items()))
    global_version = options["__uagb_asset_version"]["option_value"]
    require(isinstance(global_version, str) and global_version)
    tables = {table: {identity(table, row)[0]: digest(encoded(row)) for row in rows[table]} for table in TABLES}
    metadata = []
    for row in rows["postmeta"]:
        key = row["meta_key"]
        integer(row["post_id"], True)
        item = {"meta_id": row["meta_id"], "post_id": row["post_id"],
                "key_sha256": None if key is None else digest(key.encode()),
                "known_key": CACHE_KEY if key == CACHE_KEY else None,
                "row_sha256": digest(encoded(row)),
                "without_meta_id_sha256": digest(encoded({k: v for k, v in row.items() if k != "meta_id"})),
                **value_descriptor(row["meta_value"]),
                "cache_version": version_description(row["meta_value"], global_version) if key == CACHE_KEY else None,
                "projected_live_row": projection(row)}
        metadata.append(item)
    require(phase != "cold-import" or {"4701", "4702"} <= set(tables["postmeta"]))
    if phase == "cold-import":
        check_cold_metadata(metadata)
    option_rows = [{"option_id": row["option_id"], "name_sha256": digest(row["option_name"].encode()),
                    "known_name": row["option_name"] if row["option_name"] in OPTION_NAMES else None,
                    "protected": row["option_name"] in OPTION_NAMES, "row_sha256": digest(encoded(row)),
                    **value_descriptor(row["option_value"])} for row in rows["options"]]
    data = {"schema": 1, "kind": "live30-cold-cache-observation", "bundle": BUNDLE,
            "identity_scope": "isolated-copy", "origin": ORIGIN, "manifest_sha256": MANIFEST_SHA,
            "profile_sha256": PROFILE_SHA, "sql_sha256": SQL_SHA, "phase": phase,
            "pages_sha256": digest(encoded(list(pages.values()))), "columns": COLUMNS,
            "tables": tables, "metadata": metadata, "options": option_rows,
            "protected_options": {name: digest(encoded(options[name])) for name in OPTION_NAMES}}
    data["protected_state_sha256"] = digest(encoded(protected_state(data)))
    data["all_options_sha256"] = digest(encoded(option_rows))
    data["evidence_sha256"] = digest(encoded(data))
    return data


def check_value(item):
    require(item["value_type"] in {"null", "string"})
    require((item["value_type"] == "null" and item["value_bytes"] is None and item["value_sha256"] is None) or
            (item["value_type"] == "string" and type(item["value_bytes"]) is int and item["value_bytes"] >= 0 and is_sha(item["value_sha256"])))


def validate(data):
    require(isinstance(data, dict) and set(data) == {"schema", "kind", "bundle", "identity_scope", "origin",
            "manifest_sha256", "profile_sha256", "sql_sha256", "phase", "pages_sha256", "columns", "tables",
            "metadata", "options", "protected_options", "protected_state_sha256", "all_options_sha256", "evidence_sha256"})
    require(type(data["schema"]) is int and data["schema"] == 1 and data["kind"] == "live30-cold-cache-observation" and
            data["bundle"] == BUNDLE and data["identity_scope"] == "isolated-copy" and data["origin"] == ORIGIN and
            data["manifest_sha256"] == MANIFEST_SHA and data["profile_sha256"] == PROFILE_SHA and data["sql_sha256"] == SQL_SHA and
            data["phase"] in PHASES and is_sha(data["pages_sha256"]) and data["columns"] == COLUMNS)
    require(isinstance(data["tables"], dict) and set(data["tables"]) == set(TABLES))
    for table, rows in data["tables"].items():
        require(isinstance(rows, dict) and len(rows) < 100000)
        for key, sha in rows.items():
            require(isinstance(key, str) and len(key.split(":")) == len(KEYS[table]) and is_sha(sha))
            for part in key.split(":"):
                integer(part, table == "term_relationships")
    require(isinstance(data["metadata"], list) and len(data["metadata"]) == len(data["tables"]["postmeta"]))
    last = 0
    for item in data["metadata"]:
        require(isinstance(item, dict) and set(item) == {"meta_id", "post_id", "key_sha256", "known_key", "row_sha256", "without_meta_id_sha256", "value_type", "value_bytes", "value_sha256", "cache_version", "projected_live_row"})
        current = integer(item["meta_id"])
        integer(item["post_id"], True)
        require(current > last and data["tables"]["postmeta"].get(item["meta_id"]) == item["row_sha256"] and is_sha(item["without_meta_id_sha256"]))
        last = current
        require(item["key_sha256"] is None or is_sha(item["key_sha256"]))
        require(item["known_key"] == (CACHE_KEY if item["key_sha256"] == digest(CACHE_KEY.encode()) else None))
        check_value(item)
        version = item["cache_version"]
        if item["known_key"] is None:
            require(version is None)
        else:
            require(isinstance(version, dict) and set(version) == {"status", "type", "sha256", "matches_global"})
            require(version == {"status": "unparsed", "type": None, "sha256": None, "matches_global": None} or
                    (version["status"] == "scalar" and version["type"] in {"string", "integer"} and is_sha(version["sha256"]) and type(version["matches_global"]) is bool))
        projected = item["projected_live_row"]
        if item["known_key"] == CACHE_KEY and item["post_id"] in TARGETS and (item["value_bytes"], item["value_sha256"]) == FINAL_VALUES[item["post_id"]]:
            require(isinstance(projected, dict) and set(projected) == {"basis", "substituted_meta_id", "row_sha256", "witness_descriptor_sha256"})
            require(projected["basis"] == "regenerated-copy-value-with-substituted-live-id" and projected["substituted_meta_id"] == LIVE_IDS[item["post_id"]] and is_sha(projected["row_sha256"]))
            require(projected["witness_descriptor_sha256"] == digest(b"string:" + encoded(projected["row_sha256"])) == LIVE_WITNESS_SHA[item["post_id"]])
        else:
            require(projected is None)
    require(isinstance(data["options"], list) and 0 < len(data["options"]) < 100000)
    last, names, protected = 0, set(), {}
    for item in data["options"]:
        require(isinstance(item, dict) and set(item) == {"option_id", "name_sha256", "known_name", "protected", "row_sha256", "value_type", "value_bytes", "value_sha256"})
        current = integer(item["option_id"])
        require(current > last and is_sha(item["name_sha256"]) and item["name_sha256"] not in names and is_sha(item["row_sha256"]))
        last = current
        names.add(item["name_sha256"])
        known = next((name for name in OPTION_NAMES if digest(name.encode()) == item["name_sha256"]), None)
        require(item["known_name"] == known and type(item["protected"]) is bool and item["protected"] == (known is not None))
        if known:
            protected[known] = item["row_sha256"]
        check_value(item)
    require(set(protected) == set(OPTION_NAMES) and data["protected_options"] == protected)
    global_version_sha = next(row["value_sha256"] for row in data["options"] if row["known_name"] == "__uagb_asset_version")
    for row in data["metadata"]:
        version = row["cache_version"]
        if version is not None and version["status"] == "scalar":
            require(version["matches_global"] == (version["sha256"] == global_version_sha))
    require(data["phase"] != "cold-import" or {"4701", "4702"} <= set(data["tables"]["postmeta"]))
    if data["phase"] == "cold-import":
        check_cold_metadata(data["metadata"])
    require(data["protected_state_sha256"] == digest(encoded(protected_state(data))) and data["all_options_sha256"] == digest(encoded(data["options"])))
    require(data["evidence_sha256"] == digest(encoded({k: v for k, v in data.items() if k != "evidence_sha256"})))
    return data


def load(filename):
    raw = Path(filename).read_bytes()
    require(len(raw) < 64 * 1024 * 1024 and raw.endswith(b"\n") and raw.count(b"\n") == 1)
    return validate(json.loads(raw, object_pairs_hook=unique))


def changes(before, after):
    return [{"id": key, "before": before.get(key), "after": after.get(key)}
            for key in sorted(set(before) | set(after), key=lambda key: tuple(int(part) for part in key.split(":")))
            if before.get(key) != after.get(key)]


def comparisons(before, after, target=None):
    require(before["pages_sha256"] == after["pages_sha256"] and before["protected_options"] == after["protected_options"])
    table_changes = {table: changes(before["tables"][table], after["tables"][table]) for table in TABLES}
    require(all(not rows for table, rows in table_changes.items() if table != "postmeta"))
    old_meta = {row["meta_id"]: row for row in before["metadata"]}
    new_meta = {row["meta_id"]: row for row in after["metadata"]}
    details = []
    for change in table_changes["postmeta"]:
        old, new = old_meta.get(change["id"]), new_meta.get(change["id"])
        require(target is not None and all(row["post_id"] in target and row["known_key"] == CACHE_KEY for row in (old, new) if row is not None))
        require(old is None or new is None or old["post_id"] == new["post_id"])
        details.append({"copy_meta_id": change["id"], "before": old, "after": new})
    old_options = {row["option_id"]: row for row in before["options"]}
    new_options = {row["option_id"]: row for row in after["options"]}
    housekeeping = []
    for change in changes({key: row["row_sha256"] for key, row in old_options.items()}, {key: row["row_sha256"] for key, row in new_options.items()}):
        old, new = old_options.get(change["id"]), new_options.get(change["id"])
        require(not any(row["protected"] for row in (old, new) if row is not None))
        housekeeping.append({"copy_option_id": change["id"], "before": old, "after": new})
    return details, housekeeping


def compare(before, after, mode):
    validate(before)
    validate(after)
    old_index = PHASES.index(before["phase"])
    require(old_index + 1 < len(PHASES) and PHASES[old_index + 1] == after["phase"])
    target = None
    if mode == "exact":
        require(after["phase"] == "after-bootstrap" or after["phase"].startswith("repeat-"))
    else:
        require(mode in {"cold-" + post for post in TARGETS} and after["phase"] == "first-" + mode[5:])
        target = {mode[5:]}
    metadata, housekeeping = comparisons(before, after, target)
    return {"schema": 1, "identity_scope": "isolated-copy", "before": before["phase"], "after": after["phase"],
            "before_evidence_sha256": before["evidence_sha256"], "after_evidence_sha256": after["evidence_sha256"],
            "mode": mode, "protected_result": "exact" if not metadata else "only-requested-page-cache",
            "metadata_changes": metadata, "housekeeping_option_changes": housekeeping,
            "all_options_equal": not housekeeping,
            "scope": "six full protected tables and ten protected options; all other options reported separately"}


def verify_final(cold, final, manifest, profile):
    pages, option_hashes = bindings(manifest, profile)
    validate(cold)
    validate(final)
    require(cold["phase"] == "cold-import" and final["phase"] == "repeat-1257")
    require(cold["pages_sha256"] == digest(encoded(list(pages.values()))) and all(cold["protected_options"][name] == sha for name, sha in option_hashes.items()))
    metadata, housekeeping = comparisons(cold, final, set(TARGETS))
    require({row["copy_meta_id"] for row in metadata if row["before"] is not None} == {"4701", "4702"})
    require(all(row["after"] is None for row in metadata if row["before"] is not None))
    added = [row["after"] for row in metadata if row["after"] is not None]
    require(len(added) == 3 and {row["post_id"] for row in added} == set(TARGETS))
    for post in TARGETS:
        matches = [row for row in final["metadata"] if row["post_id"] == post and row["known_key"] == CACHE_KEY]
        require(len(matches) == 1 and (matches[0]["value_bytes"], matches[0]["value_sha256"]) == FINAL_VALUES[post])
        require(matches[0]["cache_version"]["status"] == "scalar" and matches[0]["cache_version"]["matches_global"])
    return {"schema": 1, "identity_scope": "isolated-copy", "manifest_sha256": MANIFEST_SHA,
            "profile_sha256": PROFILE_SHA, "sql_sha256": SQL_SHA,
            "cold_evidence_sha256": cold["evidence_sha256"], "final_evidence_sha256": final["evidence_sha256"],
            "result": "exact-five-row-cache-transition", "metadata_changes": metadata,
            "housekeeping_option_changes": housekeeping, "all_options_equal": not housekeeping,
            "limitations": "Copy-generated metadata IDs are not claimed as actual live IDs. Pairwise evidence proves bootstrap and repeat-pass equality; other options are separately reported, not claimed unchanged."}


def main(args):
    command = args[0] if args else ""
    if command == "sql" and len(args) == 4:
        print(sql(*args[1:]))
    elif command == "capture" and len(args) == 4:
        print(encoded(capture(*args[1:], sys.stdin)).decode())
    elif command == "compare" and len(args) == 4:
        print("LIVE30-COLD-COMPARE " + encoded(compare(load(args[1]), load(args[2]), args[3])).decode())
    elif command == "verify-final" and len(args) == 5:
        print("LIVE30-COLD-FINAL " + encoded(verify_final(load(args[1]), load(args[2]), *args[3:])).decode())
    else:
        raise ValueError("invalid command")


if __name__ == "__main__":
    try:
        main(sys.argv[1:])
    except Exception:
        # Exception messages, SQL fragments, paths and JSON errors may contain
        # private input. Never interpolate them into retained diagnostics.
        print(ERROR, file=sys.stderr)
        sys.exit(1)
