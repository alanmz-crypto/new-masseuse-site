#!/usr/bin/env python3
"""Offline controls for the actual SQL observer; synthetic rows, no site access."""
from copy import deepcopy
import importlib.util
import io
import json
from pathlib import Path
import subprocess
import sys
import tempfile
import unittest
from unittest.mock import patch


HERE = Path(__file__).resolve().parent
SPEC = importlib.util.spec_from_file_location("cold_cache_evidence", HERE / "cold-cache-evidence.py")
E = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(E)
MANIFEST, PROFILE = HERE / "manifest.json", HERE / "live-profile.json"
REAL_BINDINGS = E.bindings
PAGES, _ = REAL_BINDINGS(MANIFEST, PROFILE)
SECRET = "PRIVATE-COLD-FIXTURE-NOT-FOR-OUTPUT-รหัส"


def php_string(value):
    return 's:' + str(len(value.encode())) + ':"' + value + '";'


def asset(version, extra=""):
    return 'a:2:{' + php_string("uag_version") + php_string(version) + php_string("css") + php_string(SECRET + extra) + '}'


def fixture():
    tables = {name: [] for name in E.COLUMNS}
    by_path = {page["path"]: page_id for page_id, page in PAGES.items()}
    for page_id, page in PAGES.items():
        row = {column: "" for column in E.COLUMNS["posts"]}
        path = page["path"]
        parent_path = "/" + "/".join(path.strip("/").split("/")[:-1]) + "/"
        row.update({"ID": str(page_id), "post_title": page["title"], "post_content": SECRET,
                    "post_name": "home" if path == "/" else path.strip("/").split("/")[-1],
                    "post_parent": str(by_path.get(parent_path, 0)) if parent_path != "//" else "0",
                    "post_type": "page", "post_status": "publish", "post_password": SECRET,
                    "post_excerpt": None, "guid": E.ORIGIN + "/?p=" + str(page_id)})
        tables["posts"].append(row)
    tables["postmeta"] = [
        {"meta_id": "40", "post_id": "296", "meta_key": SECRET, "meta_value": SECRET + " metadata"},
        {"meta_id": "4701", "post_id": "1255", "meta_key": E.CACHE_KEY, "meta_value": asset("2.20.6")},
        {"meta_id": "4702", "post_id": "1257", "meta_key": E.CACHE_KEY, "meta_value": asset("2.20.6", "terms")},
    ]
    tables["term_relationships"] = [{"object_id": "296", "term_taxonomy_id": "9", "term_order": "0"}]
    tables["term_taxonomy"] = [{"term_taxonomy_id": "9", "term_id": "9", "taxonomy": SECRET, "description": SECRET, "parent": "0", "count": "1"}]
    tables["terms"] = [{"term_id": "9", "name": SECRET, "slug": SECRET, "term_group": "0"}]
    tables["termmeta"] = [{"meta_id": "5", "term_id": "9", "meta_key": SECRET, "meta_value": SECRET}]
    for index, name in enumerate(E.OPTION_NAMES, 1):
        value = {"home": E.ORIGIN, "siteurl": E.ORIGIN, "page_on_front": "296", "__uagb_asset_version": "1794410000"}.get(name, SECRET)
        tables["options"].append({"option_id": str(index), "option_name": name, "option_value": value, "autoload": "on"})
    tables["options"].append({"option_id": "100", "option_name": SECRET + "-option-name", "option_value": SECRET + " option", "autoload": "off"})
    return tables


def stream(tables, columns=None):
    lines = []
    for table, names in (columns or E.COLUMNS).items():
        lines.extend("\t".join(("schema", table, str(index), name.encode().hex().upper())) for index, name in enumerate(names, 1))
    for table, rows in tables.items():
        for row in rows:
            cells = ["NULL" if row[name] is None else row[name].encode().hex().upper() for name in E.COLUMNS[table]]
            lines.append("\t".join(("row", table, *cells)))
        lines.append("\t".join(("count", table, str(len(rows)))))
    lines.append("end\t" + E.SQL_SHA)
    return io.StringIO("\n".join(lines) + "\n")


def seal(data):
    data["protected_state_sha256"] = E.digest(E.encoded(E.protected_state(data)))
    data["all_options_sha256"] = E.digest(E.encoded(data["options"]))
    data["evidence_sha256"] = E.digest(E.encoded({key: value for key, value in data.items() if key != "evidence_sha256"}))
    return data


class EvidenceTests(unittest.TestCase):
    def setUp(self):
        self.tables = fixture()
        expected_options = {row["option_name"]: E.digest(E.encoded(row)) for row in self.tables["options"] if row["option_name"] in E.OPTION_NAMES and row["option_name"] != "__uagb_asset_version"}
        cold_rows = {row["meta_id"]: {"post_id": row["post_id"], "value_bytes": len(row["meta_value"].encode()), "value_sha256": E.digest(row["meta_value"].encode()), "row_sha256": E.digest(E.encoded(row))} for row in self.tables["postmeta"] if row["meta_id"] in {"4701", "4702"}}
        self.final_raw = {post: {"meta_id": str(6001 + index), "post_id": post, "meta_key": E.CACHE_KEY, "meta_value": asset("1794410000", post)} for index, post in enumerate(E.TARGETS)}
        final_values = {post: (len(row["meta_value"].encode()), E.digest(row["meta_value"].encode())) for post, row in self.final_raw.items()}
        witnesses = {post: E.digest(b"string:" + E.encoded(E.digest(E.encoded({**row, "meta_id": E.LIVE_IDS[post]})))) for post, row in self.final_raw.items()}
        for name, value in (("bindings", lambda *_: (PAGES, expected_options)), ("COLD_ROWS", cold_rows), ("FINAL_VALUES", final_values), ("LIVE_WITNESS_SHA", witnesses)):
            item = patch.object(E, name, value)
            item.start()
            self.addCleanup(item.stop)

    def capture(self, tables=None, phase="cold-import"):
        return E.capture(phase, MANIFEST, PROFILE, stream(tables or self.tables))

    def chain(self):
        tables = deepcopy(self.tables)
        observations = [self.capture(tables)]
        observations.append(self.capture(tables, "after-bootstrap"))
        for post in E.TARGETS:
            tables["postmeta"] = [row for row in tables["postmeta"] if not (row["post_id"] == post and row["meta_key"] == E.CACHE_KEY)]
            tables["postmeta"].append(self.final_raw[post])
            tables["postmeta"].sort(key=lambda row: int(row["meta_id"]))
            observations.append(self.capture(tables, "first-" + post))
        observations.extend(self.capture(tables, "repeat-" + post) for post in E.TARGETS)
        return observations

    def test_complete_capture_and_full_transition(self):
        observations = self.chain()
        for observation in observations:
            self.assertEqual(E.validate(observation), observation)
        modes = ("exact", "cold-1254", "cold-1255", "cold-1257", "exact", "exact", "exact")
        for before, after, mode in zip(observations, observations[1:], modes):
            self.assertEqual(E.compare(before, after, mode)["housekeeping_option_changes"], [])
        result = E.verify_final(observations[0], observations[-1], MANIFEST, PROFILE)
        self.assertEqual(result["result"], "exact-five-row-cache-transition")
        self.assertEqual(len(result["metadata_changes"]), 5)

    def test_output_redacts_all_private_values_keys_and_hex(self):
        for observation in self.chain():
            text = E.encoded(observation).decode()
            self.assertNotIn(SECRET, text)
            self.assertNotIn(SECRET.encode().hex().upper(), text)
            self.assertNotIn("a:2:{", text)
            self.assertIn(E.CACHE_KEY, text)
            self.assertEqual(set(observation["tables"]), set(E.TABLES))
            self.assertEqual(len(observation["options"]), 11)

    def test_every_raw_column_changes_its_row_hash(self):
        base = self.capture(phase="after-bootstrap")
        for table in E.TABLES:
            for column in E.COLUMNS[table]:
                with self.subTest(table=table, column=column):
                    tables = deepcopy(self.tables)
                    row = tables[table][0]
                    row[column] = (row[column] or "") + "X"
                    try:
                        changed = self.capture(tables, "after-bootstrap")
                    except ValueError:
                        continue  # An identity/manifest refusal is also decisive.
                    self.assertNotEqual(base["tables"][table], changed["tables"][table])

    def test_canonical_json_matches_installer_string_null_contract(self):
        row = {"z": None, "n": "12", "a": "/ไทย\u2028\u2029\n"}
        self.assertEqual(E.encoded(row), '{"a":"/ไทย\u2028\u2029\\n","n":"12","z":null}'.encode())
        source = (HERE / "install-peek-release.php").read_text()
        self.assertIn("JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_LINE_TERMINATORS", source)
        self.assertIn("ksort( $row, SORT_STRING )", source)

    def test_null_and_empty_remain_different(self):
        tables = deepcopy(self.tables)
        tables["posts"][0]["post_excerpt"] = ""
        self.assertNotEqual(self.capture()["tables"]["posts"], self.capture(tables)["tables"]["posts"])

    def test_extra_missing_duplicate_schema_columns_refuse(self):
        for mode in ("extra", "missing", "duplicate"):
            columns = deepcopy(E.COLUMNS)
            if mode == "extra":
                columns["posts"].append("secret_plugin_column")
            elif mode == "missing":
                columns["posts"].pop()
            else:
                columns["posts"][-1] = columns["posts"][0]
            with self.subTest(mode=mode), self.assertRaises(ValueError):
                E.capture("cold-import", MANIFEST, PROFILE, stream(self.tables, columns))

    def test_missing_duplicate_out_of_order_or_truncated_sql_refuses(self):
        raw = stream(self.tables).getvalue()
        variants = [raw.rsplit("end", 1)[0], raw[:-1], raw + "end\t" + E.SQL_SHA + "\n",
                    raw.replace("count\tposts\t15", "count\tposts\t14"),
                    raw.replace("end\t" + E.SQL_SHA, "end\t" + "0" * 64)]
        meta_line = next(line for line in raw.splitlines(True) if line.startswith("row\tpostmeta\t"))
        variants.append(raw.replace(meta_line, meta_line + meta_line))
        for candidate in variants:
            with self.subTest(candidate=variants.index(candidate)), self.assertRaises(ValueError):
                E.capture("cold-import", MANIFEST, PROFILE, io.StringIO(candidate))

    def test_raw_origin_and_exact_published_inventory_refuse_drift(self):
        for mode in ("https", "localhost", "title", "parent", "newpage", "front"):
            tables = deepcopy(self.tables)
            if mode in {"https", "localhost", "front"}:
                name = "page_on_front" if mode == "front" else "home"
                value = "298" if mode == "front" else ("https://thaisolitude.com" if mode == "https" else "http://localhost:8090")
                next(row for row in tables["options"] if row["option_name"] == name)["option_value"] = value
            elif mode == "title":
                tables["posts"][0]["post_title"] += " changed"
            elif mode == "parent":
                tables["posts"][0]["post_parent"] = "296"
            else:
                tables["posts"].append({**tables["posts"][0], "ID": "9999"})
            with self.subTest(mode=mode), self.assertRaises(ValueError):
                self.capture(tables)

    def test_cold_descriptor_binding_and_absent_1254_cache(self):
        for mode in ("missing", "key", "post", "value", "current", "1254"):
            tables = deepcopy(self.tables)
            row = tables["postmeta"][1]
            if mode == "missing":
                tables["postmeta"].remove(row)
            elif mode in {"key", "post", "value"}:
                row[{"key": "meta_key", "post": "post_id", "value": "meta_value"}[mode]] += "changed"
            elif mode == "current":
                row["meta_value"] = asset("1794410000")
            else:
                tables["postmeta"].append(self.final_raw["1254"])
            with self.subTest(mode=mode), self.assertRaises(ValueError):
                self.capture(tables)

    def test_protected_option_full_rows_refuse_changes(self):
        before = self.capture()
        for column in E.COLUMNS["options"]:
            tables = deepcopy(self.tables)
            row = next(row for row in tables["options"] if row["option_name"] == "__uagb_asset_version")
            row[column] = "101" if column == "option_id" else row[column] + "changed"
            tables["options"].sort(key=lambda row: int(row["option_id"]))
            with self.subTest(column=column), self.assertRaises(ValueError):
                E.compare(before, self.capture(tables, "after-bootstrap"), "exact")

    def test_housekeeping_changes_are_reported_with_hashed_names(self):
        tables = deepcopy(self.tables)
        tables["options"][-1]["option_value"] += " changed"
        result = E.compare(self.capture(), self.capture(tables, "after-bootstrap"), "exact")
        self.assertEqual(result["protected_result"], "exact")
        self.assertEqual(len(result["housekeeping_option_changes"]), 1)
        self.assertFalse(result["all_options_equal"])
        self.assertNotIn(SECRET, E.encoded(result).decode())

    def test_housekeeping_addition_removal_are_reported(self):
        for mode in ("add", "remove"):
            tables = deepcopy(self.tables)
            if mode == "add":
                tables["options"].append({"option_id": "101", "option_name": SECRET + "other", "option_value": SECRET, "autoload": "off"})
            else:
                tables["options"].pop()
            result = E.compare(self.capture(), self.capture(tables, "after-bootstrap"), "exact")
            self.assertEqual(len(result["housekeeping_option_changes"]), 1)

    def test_bootstrap_and_repeat_forbid_metadata_changes(self):
        observations = self.chain()
        bad_bootstrap = deepcopy(observations[2])
        bad_bootstrap["phase"] = "after-bootstrap"
        with self.assertRaises(ValueError):
            E.compare(observations[0], seal(bad_bootstrap), "exact")
        bad_repeat = deepcopy(observations[5])
        bad_repeat["tables"]["terms"]["9"] = "0" * 64
        with self.assertRaises(ValueError):
            E.compare(observations[4], seal(bad_repeat), "exact")

    def test_each_get_allows_only_its_cache_target(self):
        observations = self.chain()
        wrong = deepcopy(observations[3])
        wrong["phase"] = "first-1254"
        with self.assertRaises(ValueError):
            E.compare(observations[1], seal(wrong), "cold-1254")
        wrong = deepcopy(observations[2])
        wrong["tables"]["posts"]["296"] = "0" * 64
        with self.assertRaises(ValueError):
            E.compare(observations[1], seal(wrong), "cold-1254")

    def test_phase_skip_reorder_and_mode_mismatch_refuse(self):
        observations = self.chain()
        for a, b, mode in ((0, 2, "cold-1254"), (2, 1, "exact"), (1, 2, "exact"), (1, 2, "cold-1255"), (4, 5, "cold-1254")):
            with self.subTest(a=a, b=b, mode=mode), self.assertRaises(ValueError):
                E.compare(observations[a], observations[b], mode)

    def test_projection_uses_known_live_id_but_preserves_copy_id(self):
        final = self.chain()[-1]
        for row in final["metadata"]:
            if row["post_id"] not in E.TARGETS:
                continue
            projected = row["projected_live_row"]
            self.assertNotEqual(row["meta_id"], projected["substituted_meta_id"])
            self.assertEqual(projected["substituted_meta_id"], E.LIVE_IDS[row["post_id"]])
            self.assertEqual(projected["witness_descriptor_sha256"], E.LIVE_WITNESS_SHA[row["post_id"]])
            self.assertNotEqual(row["row_sha256"], projected["row_sha256"])

    def test_forged_projected_id_hash_and_descriptor_refuse(self):
        final = self.chain()[-1]
        for field in ("substituted_meta_id", "row_sha256", "witness_descriptor_sha256", "basis"):
            changed = deepcopy(final)
            row = next(row for row in changed["metadata"] if row["post_id"] == "1254")
            row["projected_live_row"][field] = "0" * 64
            with self.subTest(field=field), self.assertRaises(ValueError):
                E.validate(seal(changed))

    def test_final_refuses_extra_missing_wrong_value_and_unparsed_cache(self):
        observations = self.chain()
        for mode in ("extra", "missing", "wrongvalue", "unparsed"):
            changed = deepcopy(observations[-1])
            row = next(row for row in changed["metadata"] if row["post_id"] == "1254")
            if mode == "extra":
                changed["tables"]["terms"]["99"] = "0" * 64
            elif mode == "missing":
                changed["metadata"].remove(row)
                del changed["tables"]["postmeta"][row["meta_id"]]
            elif mode == "wrongvalue":
                row["value_sha256"] = "0" * 64
                row["projected_live_row"] = None
            else:
                row["cache_version"] = {"status": "unparsed", "type": None, "sha256": None, "matches_global": None}
            with self.subTest(mode=mode), self.assertRaises(ValueError):
                E.verify_final(observations[0], seal(changed), MANIFEST, PROFILE)

    def test_primitive_parser_accepts_scalars_arrays_and_rejects_objects_references(self):
        self.assertTrue(E.version_description(asset("1794410000"), "1794410000")["matches_global"])
        self.assertFalse(E.version_description(asset("2.20.6"), "1794410000")["matches_global"])
        valid = 'a:2:{s:11:"uag_version";i:123;s:1:"x";a:4:{i:0;N;i:1;b:0;i:2;d:1.5;i:3;s:6:"ไทย";}}'
        # UTF-8 byte lengths, not character counts.
        self.assertEqual(E.version_description(valid, "123")["status"], "unparsed")
        valid = valid.replace('s:6:"ไทย";', 's:9:"ไทย";')
        self.assertTrue(E.version_description(valid, "123")["matches_global"])
        for value in ('O:1:"X":0:{}', 'C:1:"X":0:{}', 'R:1;', 'r:1;', asset("123") + "junk", 'a:10001:{}', 'a:1:{s:11:"uag_version";O:1:"X":0:{}}'):
            self.assertEqual(E.version_description(value, "123")["status"], "unparsed")

    def test_forged_cache_version_match_refuses(self):
        data = self.chain()[-1]
        row = next(row for row in data["metadata"] if row["post_id"] == "1254")
        row["cache_version"]["sha256"] = "0" * 64
        with self.assertRaises(ValueError):
            E.validate(seal(data))

    def test_tampered_shape_hashes_and_raw_extra_fields_refuse(self):
        for mode in ("top", "meta", "option", "scope", "sql", "seal"):
            data = self.capture()
            if mode == "top":
                data["raw"] = SECRET
            elif mode == "meta":
                data["metadata"][0]["raw"] = SECRET
            elif mode == "option":
                data["options"][0]["raw"] = SECRET
            elif mode == "scope":
                data["identity_scope"] = "live"
            elif mode == "sql":
                data["sql_sha256"] = "0" * 64
            else:
                data["evidence_sha256"] = "0" * 64
            with self.subTest(mode=mode), self.assertRaises(ValueError):
                E.validate(data if mode == "seal" else seal(data))

    def test_file_loader_rejects_duplicates_and_multiple_lines(self):
        valid = E.encoded(self.capture()) + b"\n"
        with tempfile.TemporaryDirectory(prefix="cold-evidence-test-") as tmp:
            path = Path(tmp) / "capture.json"
            path.write_bytes(valid)
            E.load(path)
            for raw in (valid + valid, valid[:-1], valid.replace(b'"schema":1', b'"schema":1,"schema":1')):
                path.write_bytes(raw)
                with self.assertRaises(ValueError):
                    E.load(path)

    def test_cli_failure_is_fixed_and_never_echoes_private_input(self):
        result = subprocess.run([sys.executable, str(HERE / "cold-cache-evidence.py"), "capture", "cold-import", str(MANIFEST), str(PROFILE)], input=SECRET, text=True, capture_output=True, check=False)
        self.assertEqual(result.returncode, 1)
        self.assertEqual(result.stdout, "")
        self.assertEqual(result.stderr, E.ERROR + "\n")


class BindingAndSqlTests(unittest.TestCase):
    def test_actual_manifest_and_profile_bytes(self):
        pages, options = REAL_BINDINGS(MANIFEST, PROFILE)
        self.assertEqual(len(pages), 15)
        self.assertEqual(len(options), 9)
        self.assertEqual(E.digest(MANIFEST.read_bytes()), E.MANIFEST_SHA)
        self.assertEqual(E.digest(PROFILE.read_bytes()), E.PROFILE_SHA)

    def test_modified_manifest_or_profile_refuses(self):
        with tempfile.TemporaryDirectory(prefix="cold-bind-test-") as tmp:
            path = Path(tmp) / "modified.json"
            for source, other, first in ((MANIFEST, PROFILE, True), (PROFILE, MANIFEST, False)):
                path.write_bytes(source.read_bytes() + b" ")
                with self.assertRaises(ValueError):
                    REAL_BINDINGS(path, other) if first else REAL_BINDINGS(other, path)

    def test_sql_reads_all_columns_all_options_and_no_mutations(self):
        text = E.sql("iej_", MANIFEST, PROFILE)
        self.assertIn("START TRANSACTION WITH CONSISTENT SNAPSHOT, READ ONLY;", text)
        self.assertIn("REPEATABLE READ", text)
        self.assertEqual(text.count("SELECT 'schema'"), 7)
        self.assertEqual(text.count("SELECT 'count'"), 7)
        self.assertEqual(text.count("SELECT 'row'"), 7)
        self.assertIn("SELECT 'end','" + E.SQL_SHA + "'", text)
        self.assertNotIn("WHERE option_name", text)
        self.assertNotRegex(text.upper(), r"\b(?:INSERT|UPDATE|DELETE|REPLACE|ALTER|DROP|TRUNCATE|INTO\s+OUTFILE)\b")
        for table, columns in E.COLUMNS.items():
            statement = next(line for line in text.splitlines() if line.startswith("SELECT 'row','" + table + "'"))
            for column in columns:
                self.assertIn("HEX(CAST(`" + column + "` AS CHAR CHARACTER SET utf8mb4))", statement)

    def test_sql_prefix_injection_refuses(self):
        for prefix in ("iej_;DELETE", "a'", "a`", "a.b", "", "a\n"):
            with self.subTest(prefix=prefix), self.assertRaises(ValueError):
                E.sql(prefix, MANIFEST, PROFILE)

    def test_no_raw_input_files_or_object_unserializer(self):
        source = (HERE / "cold-cache-evidence.py").read_text()
        self.assertNotIn("write_text(", source)
        self.assertNotIn("write_bytes(", source)
        self.assertNotIn("pickle", source)
        self.assertNotIn("subprocess", source)


if __name__ == "__main__":
    unittest.main(verbosity=2)
