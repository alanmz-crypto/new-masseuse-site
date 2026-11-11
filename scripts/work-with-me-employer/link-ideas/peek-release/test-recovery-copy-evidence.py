#!/usr/bin/env python3
"""Focused offline recovery evidence controls; synthetic private rows only."""
from copy import deepcopy
import importlib.util
import io
import json
from pathlib import Path
import tempfile
import unittest
from unittest.mock import patch

HERE = Path(__file__).resolve().parent
SPEC = importlib.util.spec_from_file_location("recovery_copy_evidence", HERE / "recovery-copy-evidence.py")
E = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(E)
C = E.C
SECRET = "PRIVATE-RECOVERY-FIXTURE-NEVER-RETAIN"


class RecoveryTests(unittest.TestCase):
    def setUp(self):
        self.temp = tempfile.TemporaryDirectory(prefix="recovery-evidence-test-")
        self.addCleanup(self.temp.cleanup)
        self.directory = Path(self.temp.name)
        self.rows = [{"meta_id": meta, "post_id": post, "meta_key": C.CACHE_KEY,
                      "meta_value": 'a:2:{s:11:"uag_version";s:3:"123";s:3:"css";s:' + str(len(SECRET)) + ':"' + SECRET + '";}'} for post, meta in C.LIVE_IDS.items()]
        self.options = [{"option_id": str(index), "option_name": name, "option_value": "123" if name == "__uagb_asset_version" else C.ORIGIN, "autoload": "on"} for index, name in enumerate(E.OPTIONS, 1)]
        values = {row["post_id"]: (len(row["meta_value"].encode()), C.digest(row["meta_value"].encode())) for row in self.rows}
        witnesses = {row["post_id"]: C.digest(b"string:" + C.encoded(C.digest(C.encoded(row)))) for row in self.rows}
        receipt = json.loads((HERE / "incident-live30.json").read_bytes())
        receipt["added"] = {row["meta_id"]: {"post_id": int(row["post_id"]), "meta_key": C.CACHE_KEY, "row_sha256": C.digest(C.encoded(row)), "value_sha256": C.digest(row["meta_value"].encode()), "value_bytes": len(row["meta_value"].encode())} for row in self.rows}
        old = {"witnesses": {"postmeta": {meta: value["row_sha256"] for meta, value in C.COLD_ROWS.items()}}, "options": {row["option_name"]: row for row in self.options}, "private": SECRET, "owned": None}
        new = {**deepcopy(old), "owned": E.IDS}
        journal = {"schema": 1, "complete": True, "home": C.ORIGIN, "bundle": C.BUNDLE, "ids": E.IDS, "manifest_sha256": C.MANIFEST_SHA, "profile_sha256": C.PROFILE_SHA, "old": old, "new": new, "old_sha256": C.digest(C.encoded(old)), "new_sha256": C.digest(C.encoded(new))}
        journal_raw = C.encoded(journal)
        self.journal = self.directory / E.SNAPSHOT_FILE
        self.journal.write_bytes(journal_raw)
        original, applied = journal["new_sha256"], C.digest(C.encoded(E.project(new, receipt)))
        receipt["snapshot"]["sha256"] = C.digest(journal_raw)
        receipt["snapshot"]["bytes"] = len(journal_raw)
        receipt["original_applied_sha256"] = original
        receipt["projected_applied_sha256"] = applied
        self.receipt = self.directory / "receipt.json"
        self.receipt.write_bytes(C.encoded(receipt))
        receipt_sha = C.digest(self.receipt.read_bytes())
        self.binding = self.directory / "binding.json"
        self.binding.write_bytes(C.encoded({"schema": 1, "incident": "live30-assets-20261111", "receipt_sha256": receipt_sha}))
        for module, key, value in ((C, "FINAL_VALUES", values), (C, "LIVE_WITNESS_SHA", witnesses), (E, "SNAPSHOT_SHA", C.digest(journal_raw)), (E, "SNAPSHOT_BYTES", len(journal_raw)), (E, "ORIGINAL_APPLIED", original), (E, "APPLIED", applied), (E, "RECEIPT_SHA", receipt_sha)):
            item = patch.object(module, key, value)
            item.start()
            self.addCleanup(item.stop)
        self.context = E.prepare(self.receipt, self.binding, self.journal)

    def sql_stream(self, rows=None, options=None):
        lines = []
        for table in ("postmeta", "options"):
            for index, name in enumerate(C.COLUMNS[table], 1):
                lines.append("\t".join(("schema", table, str(index), name.encode().hex().upper())))
        for table, values in (("postmeta", self.rows if rows is None else rows), ("options", self.options if options is None else options)):
            for row in values:
                lines.append("\t".join(("row", table, *("NULL" if row[name] is None else row[name].encode().hex().upper() for name in C.COLUMNS[table]))))
            lines.append("\t".join(("count", table, str(len(values)))))
        lines.append("end\t" + self.context["receipt_sha256"])
        return io.StringIO("\n".join(lines) + "\n")

    def marker_stream(self, step, incident=None, extra=""):
        incident = E.expected_incident(step, self.context) if incident is None else incident
        source = {"files": json.loads((HERE / "manifest.json").read_bytes())["spectra"]["files"], "header_version": "2.20.6", "runtime_version": "2.20.6", "loaded": {key: True for key in ("main", "UAGB_Helper", "UAGB_Rest_API", "UAGB_Loader", "UAGB_Post_Assets", "UAGB_Front_Assets")}, "save_post_priority_10": True}
        success = "Success: LIVE30 " + E.expected_incident(step, self.context)["state"] + ": exact release state with the proved external cache delta retained; historical database identity is not claimed. Original journal unchanged."
        return io.StringIO("RELEASE-SPECTRA-SOURCE " + C.encoded(source).decode() + "\nRELEASE-INCIDENT " + C.encoded(incident).decode() + "\n" + success + "\n" + extra)

    def make_files(self):
        for label in E.LABELS:
            result = E.capture(label, self.context, self.sql_stream())
            (self.directory / (label + ".json")).write_bytes(C.encoded(result) + b"\n")
        for step in E.STEPS:
            result = E.marker(step, self.context, self.marker_stream(step))
            self.assertTrue(result["valid"])
            (self.directory / (step + ".marker.json")).write_bytes(C.encoded(result) + b"\n")

    def test_full_seven_mode_eight_observation_proof(self):
        self.make_files()
        result = E.verify(self.context, self.directory)
        self.assertEqual(len(result["marker_sha256"]), 7)
        self.assertEqual(len(result["observation_sha256"]), 8)
        self.assertTrue(result["cache_delta_retained"])

    def test_restored_hash_is_projected_from_actual_journal(self):
        journal = json.loads(self.journal.read_bytes())
        receipt = json.loads(self.receipt.read_bytes())
        self.assertEqual(self.context["restored_state_sha256"], C.digest(C.encoded(E.project(journal["old"], receipt))))
        self.assertNotEqual(self.context["restored_state_sha256"], journal["old_sha256"])
        self.assertNotIn(SECRET, C.encoded(self.context).decode())

    def test_changed_journal_receipt_and_binding_refuse(self):
        for path in (self.journal, self.receipt, self.binding):
            raw = path.read_bytes()
            path.write_bytes(raw + b" ")
            if path == self.binding:
                path.write_text('{"schema":1,"incident":"live30-assets-20261111","receipt_sha256":null}')
            with self.subTest(path=path.name), self.assertRaises(ValueError):
                E.prepare(self.receipt, self.binding, self.journal)
            path.write_bytes(raw)

    def test_marker_requires_every_identity_and_retention_field(self):
        for field in E.expected_incident("verify-applied", self.context):
            changed = E.expected_incident("verify-applied", self.context)
            del changed[field]
            with self.subTest(field=field):
                self.assertFalse(E.marker("verify-applied", self.context, self.marker_stream("verify-applied", changed))["valid"])
        changed = E.expected_incident("verify-applied", self.context)
        changed["complete"] = 1
        self.assertFalse(E.marker("verify-applied", self.context, self.marker_stream("verify-applied", changed))["valid"])

    def test_unknown_logs_and_invalid_marker_bodies_never_escape(self):
        result = E.marker("verify-applied", self.context, self.marker_stream("verify-applied", extra=SECRET + "\n"))
        self.assertFalse(result["valid"])
        self.assertNotIn(SECRET, C.encoded(result).decode())
        self.assertEqual(result["unexpected_lines"][0]["sha256"], C.digest(SECRET.encode()))
        result = E.marker("verify-applied", self.context, self.marker_stream("verify-applied", {"secret": SECRET}))
        self.assertFalse(result["valid"])
        self.assertIsNone(result["incident"])
        self.assertNotIn(SECRET, C.encoded(result).decode())

    def test_wrong_state_and_duplicate_or_missing_marker_refuse(self):
        for step in E.STEPS:
            wrong = E.expected_incident(step, self.context)
            wrong["state_sha256"] = "0" * 64
            self.assertFalse(E.marker(step, self.context, self.marker_stream(step, wrong))["valid"])
        raw = self.marker_stream("rollback").getvalue()
        self.assertFalse(E.marker("rollback", self.context, io.StringIO(raw + raw))["valid"])
        self.assertFalse(E.marker("rollback", self.context, io.StringIO(""))["valid"])

    def test_sql_requires_exact_cache_ids_raw_values_and_global_option_row(self):
        for mode in ("id", "value", "key", "sixth", "removed", "version", "autoload", "origin"):
            rows, options = deepcopy(self.rows), deepcopy(self.options)
            if mode in {"id", "value", "key"}:
                rows[0][{"id": "meta_id", "value": "meta_value", "key": "meta_key"}[mode]] += "1"
            elif mode == "sixth":
                rows.append({**rows[-1], "meta_id": "5092"})
            elif mode == "removed":
                rows.insert(0, {**rows[0], "meta_id": "4701"})
            elif mode == "version":
                options[0]["option_value"] = "124"
            elif mode == "autoload":
                options[0]["autoload"] = "off"
            else:
                options[1]["option_value"] = "http://localhost:8090"
            with self.subTest(mode=mode), self.assertRaises(ValueError):
                E.capture("before-bootstrap", self.context, self.sql_stream(rows, options))

    def test_sql_schema_counts_end_and_label_refusals(self):
        raw = self.sql_stream().getvalue()
        candidates = [raw.rsplit("end", 1)[0], raw[:-1], raw.replace("count\tpostmeta\t3", "count\tpostmeta\t2"), raw.replace("schema\tpostmeta\t1\t6D6574615F6964", "schema\tpostmeta\t1\t736563726574")]
        for candidate in candidates:
            with self.assertRaises(ValueError):
                E.capture("before-bootstrap", self.context, io.StringIO(candidate))
        with self.assertRaises(ValueError):
            E.capture("live", self.context, self.sql_stream())

    def test_output_contains_descriptors_not_raw_metadata(self):
        result = E.capture("before-bootstrap", self.context, self.sql_stream())
        text = C.encoded(result).decode()
        self.assertNotIn(SECRET, text)
        self.assertNotIn(SECRET.encode().hex().upper(), text)
        self.assertNotIn("a:2:{", text)
        self.assertEqual(result["cache_rows"], self.context["cache_rows"])

    def test_final_missing_reordered_tampered_or_private_evidence_refuses(self):
        self.make_files()
        path = self.directory / "after-rollback.json"
        raw = path.read_bytes()
        path.unlink()
        with self.assertRaises(ValueError):
            E.verify(self.context, self.directory)
        path.write_bytes(raw)
        for field, value in (("label", "after-verify-restored"), ("context_sha256", "0" * 64), ("private", SECRET)):
            data = json.loads(raw)
            data[field] = value
            path.write_bytes(C.encoded(E.seal(data)) + b"\n")
            with self.subTest(field=field), self.assertRaises(ValueError):
                E.verify(self.context, self.directory)
        path.write_bytes(raw)

    def test_sql_is_narrow_readonly_and_prefix_guarded(self):
        text = E.sql("iej_", self.context)
        self.assertIn("WITH CONSISTENT SNAPSHOT, READ ONLY", text)
        self.assertIn("meta_id IN (4701,4702,5089,5090,5091)", text)
        self.assertIn("option_name IN ('__uagb_asset_version','home','siteurl')", text)
        self.assertNotRegex(text.upper(), r"\b(?:INSERT|UPDATE|DELETE|ALTER|DROP|REPLACE)\b")
        for prefix in ("iej_;", "a`", "a'", ""):
            with self.assertRaises(ValueError):
                E.sql(prefix, self.context)


class PublishedReceiptTest(unittest.TestCase):
    def test_current_exact_receipt_and_all_three_witnesses(self):
        sha, receipt = E.receipt(HERE / "incident-live30.json", HERE / "incident-live30-binding.json")
        self.assertEqual(sha, E.RECEIPT_SHA)
        self.assertEqual(set(receipt["added"]), {"5089", "5090", "5091"})


if __name__ == "__main__":
    unittest.main(verbosity=2)
