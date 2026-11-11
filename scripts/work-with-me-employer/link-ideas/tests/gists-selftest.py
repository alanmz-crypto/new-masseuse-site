#!/usr/bin/env python3
"""Deliberate bad content must not pass the Peek data gate."""
import copy
import importlib.util
import json
from pathlib import Path
import tempfile

HERE = Path(__file__).resolve().parent.parent
spec = importlib.util.spec_from_file_location("gists", HERE / "lint-gists.py")
lint = importlib.util.module_from_spec(spec)
spec.loader.exec_module(lint)
source = lint.PLAN_FILE.read_text(encoding="utf-8")
plans = lint.read_plans(source)
thai = lint.read_json(HERE / "peek-data" / "thai-massage.json")
checks = 0


def expect(name, condition):
    global checks
    assert condition, name
    checks += 1
    print("PASS", name)


def broken(name, mutate, reason):
    data = copy.deepcopy(thai)
    mutate(data)
    errors = lint.validate(data, "thai-massage", plans)
    expect(name, any(reason in error for error in errors))


expect("approved Thai data accepted", not lint.validate(thai, "thai-massage", plans))
for slug in ("therapeutic-massage", "deep-tissue-massage", "chair-massage"):
    data = lint.read_json(HERE / "peek-data" / f"{slug}.json")
    expect(f"{slug} wording passes mechanical checks", bool(data["items"]) and not lint.validate(data, slug, plans))
    empty = copy.deepcopy(data)
    empty["approval"]["status"] = "draft"
    empty["items"] = []
    empty["coverage"] = {}
    expect(f"{slug} empty draft accepted for paragraph fallback", not lint.validate(empty, slug, plans))
expect("Therapeutic/Chair retain their six-section source shape", len(plans["therapeutic-massage"]) == len(plans["chair-massage"]) == 6)
expect("Services remains eligible for separate assessment", len(plans["services"]) == 7)
expect("native Privacy FAQ plans recognized", set(plans["privacy-policy"]) == {"ts-online-privacy", "ts-client-privacy"})
broken("schema boolean refused", lambda d: d.update(schema=True), "integer 1")
broken("unknown field refused", lambda d: d.update(destinations=[]), "exactly schema")
broken("other-page data refused", lambda d: d.update(slug="chair-massage"), "filename")
broken("missing approval provenance refused", lambda d: d["approval"].update(source=""), "approval source")
broken("unrecognized approval state refused", lambda d: d["approval"].update(status="almost-approved"), "approval status")
broken("bad pair shape refused", lambda d: d["items"].append({"text": "Test"}), "expected [text, anchor]")
broken("non-string item refused", lambda d: d["items"].append([None, "ts-what-it-is"]), "expected [text, anchor]")
broken("HTML text refused", lambda d: d["items"][0].__setitem__(0, "<b>Movement</b>"), "plain text")
broken("control character refused", lambda d: d["items"][0].__setitem__(0, "Movement\nmeets massage"), "plain text")
broken("nonexistent anchor refused", lambda d: d["items"][0].__setitem__(1, "missing-anchor"), "unknown planned anchor")
broken("URL instead of local anchor refused", lambda d: d["items"][0].__setitem__(1, "https://example.com"), "unknown planned anchor")
broken("exact duplicate inside box refused", lambda d: d["items"].insert(1, d["items"][0][:]), "duplicate idea")
broken("duplicate across boxes refused", lambda d: d["items"].append([d["items"][0][0].upper(), "ts-common-reasons"]), "duplicate idea")
broken("ordinary overlong phrase refused", lambda d: d["items"][0].__setitem__(0, "Six separate words belong in here"), "ordinary phrase")
broken("forbidden marketing evidence word refused", lambda d: d["items"][0].__setitem__(0, "Chronic stiffness"), "forbidden wording")
broken("claim word refused in research", lambda d: d["items"].insert(-1, ["Studies guarantee outcomes", "ts-guide-research"]), "forbidden wording")
broken("voice word refused in research", lambda d: d["items"].insert(-1, ["Our research summary", "ts-guide-research"]), "forbidden wording")
broken("research exemption cannot be reassigned", lambda d: d.update(research_anchor="ts-what-it-is"), "recognized research heading")
broken("partial summary without etc refused", lambda d: d["items"].pop(), "partial coverage")
broken("etc inside a box refused", lambda d: d["items"].insert(-2, ["etc.", "ts-guide-research"]), "only once at the end")
broken("coverage omission refused", lambda d: d["coverage"].pop("ts-common-reasons"), "exactly the summarized")
broken("coverage unknown anchor refused", lambda d: d["coverage"].update(missing="most"), "exactly the summarized")
broken("coverage percentage guess refused", lambda d: d["coverage"].update({"ts-common-reasons": 60}), "most or partial")
data = copy.deepcopy(thai)
data["items"].insert(-1, ["Research on chronic pain remains uncertain across these small studies", "ts-guide-research"])
expect("research qualification can be longer and retain evidence terms", not lint.validate(data, "thai-massage", plans))
data["approval"]["status"] = "draft"
data["items"][0][0] = "Guaranteed healing"
expect("draft wording is still linted", any("forbidden wording" in x for x in lint.validate(data, "thai-massage", plans)))
with tempfile.TemporaryDirectory() as tmp:
    path = Path(tmp) / "data.json"
    path.write_text('{"schema":1,"schema":2}', encoding="utf-8")
    try:
        lint.read_json(path)
    except ValueError:
        expect("duplicate JSON keys refused", True)
    else:
        raise AssertionError("duplicate JSON keys accepted")
for name, edited in [
    ("dynamic plan expression", source.replace("'What it is', 'exact'", "dynamic_label(), 'exact'", 1)),
    ("unsupported plan kind", source.replace("'What it is', 'exact'", "'What it is', 'guess'", 1)),
    ("missing plan assignment", source.replace("$plans = ", "$renamed = ", 1)),
]:
    try:
        lint.read_plans(edited)
    except ValueError:
        expect(f"{name} fails closed", True)
    else:
        raise AssertionError(f"{name} accepted")
# A genuine source edit is followed automatically; there is no second ID list.
changed = source.replace("'Questions About Thai Massage', 'ts-guide-questions'", "'Questions About Thai Massage', 'new-question-id'", 1)
expect("planned ID change invalidates stale data", any("unknown planned anchor" in e for e in lint.validate(thai, "thai-massage", lint.read_plans(changed))))
print(f"gists: {checks} checks passed")
