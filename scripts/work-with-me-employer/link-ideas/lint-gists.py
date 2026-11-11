#!/usr/bin/env python3
"""Check Peek page data without making it a second navigation map.

Only literal plans in page-navigation/jump-links.php define possible section IDs.
The browser gate must still check the actual rendered row and targets: an existing
page ID can override a plan's default. A lint pass cannot establish accuracy,
semantic overlap, or percentage coverage. Those remain editorial review, followed
by Ryan's wording approval. Draft items are checked but are never displayed.

Usage: python3 -I -B lint-gists.py [peek-data/*.json]
"""
import argparse
import importlib.util
import json
from pathlib import Path
import re
import sys

HERE = Path(__file__).resolve().parent
PLAN_FILE = HERE.parent / "page-navigation" / "jump-links.php"
spec = importlib.util.spec_from_file_location("wording_tiers", HERE.parent / "client-pages" / "wording_tiers.py")
wording = importlib.util.module_from_spec(spec)
spec.loader.exec_module(wording)


def unique_object(pairs):
    result = {}
    for key, value in pairs:
        if key in result:
            raise ValueError(f"duplicate JSON key: {key}")
        result[key] = value
    return result


def read_json(path):
    return json.loads(path.read_text(encoding="utf-8"), object_pairs_hook=unique_object)


def read_plans(source):
    """Parse only PHP's current string/array literal grammar; refuse other code.

    No eval, PHP executable, WordPress environment or copied section registry is
    needed. An expression or unsupported plan shape fails rather than silently
    changing which IDs or research exemptions this lint accepts.
    """
    marker = "$plans = "
    if source.count(marker) != 1:
        raise ValueError("expected one literal $plans assignment in jump-links.php")
    source = source.split(marker, 1)[1]
    token = re.compile(r"\s+|//[^\n]*|'(?:\\.|[^'\\])*'|array\b|=>|[(),;]")
    position = 0

    def next_token():
        nonlocal position
        while True:
            match = token.match(source, position)
            if not match:
                raise ValueError("unsupported jump-links plan syntax")
            position = match.end()
            value = match.group()
            if value.isspace() or value.startswith("//"):
                continue
            return value

    current = next_token()

    def take(expected=None):
        nonlocal current
        value = current
        if expected is not None and value != expected:
            raise ValueError(f"expected {expected} in jump-links plan, found {value}")
        # The assignment semicolon is the last token this parser may consume.
        if value != ";":
            current = next_token()
        return value

    def value():
        if current.startswith("'"):
            raw = take()[1:-1]
            return re.sub(r"\\(['\\])", r"\1", raw)
        take("array")
        take("(")
        entries = []
        while current != ")":
            first = value()
            if current == "=>":
                take("=>")
                entries.append((first, value()))
            else:
                entries.append(first)
            if current != ")":
                take(",")
        take(")")
        return entries

    raw = value()
    take(";")
    plans = {}
    for entry in raw:
        if not isinstance(entry, tuple) or len(entry) != 2:
            raise ValueError("page plans must be keyed arrays")
        slug, rows = entry
        if not isinstance(slug, str) or slug in plans or not isinstance(rows, list):
            raise ValueError("invalid or repeated page plan")
        anchors = {}
        for row in rows:
            if not isinstance(row, list) or len(row) not in (3, 4) or not all(isinstance(x, str) for x in row):
                raise ValueError(f"unsupported row in {slug}")
            label, kind, match = row[:3]
            if kind not in {"exact", "h", "id", "blk", "faq"}:
                raise ValueError(f"unsupported target kind in {slug}: {kind}")
            if len(row) == 4:
                anchor = row[3]
            elif kind == "id":
                anchor = match
            else:
                # Current labels are ASCII. Do not pretend to reproduce WP's
                # locale-dependent sanitize_title() for a translated label.
                if not label.isascii():
                    raise ValueError(f"{slug}: non-ASCII label needs an explicit stable ID")
                anchor = "ts-" + re.sub(r"[\s-]+", "-", re.sub(r"[^a-z0-9\s-]", "", label.lower())).strip("-")
            if anchor in anchors:
                raise ValueError(f"{slug}: repeated planned anchor {anchor}")
            anchors[anchor] = {"label": label, "kind": kind, "match": match}
        plans[slug] = anchors
    if not plans:
        raise ValueError("no navigation plans found")
    return plans


def validate(data, slug, plans):
    errors = []

    def require(condition, message):
        if not condition:
            errors.append(message)

    fields = {"schema", "slug", "approval", "items", "coverage", "research_anchor"}
    if not isinstance(data, dict):
        return ["page data must be an object"]
    require(set(data) == fields, "page data must contain exactly schema, slug, approval, items, coverage, research_anchor")
    require(type(data.get("schema")) is int and data["schema"] == 1, "schema must be integer 1")
    require(isinstance(slug, str) and bool(re.fullmatch(r"[a-z0-9]+(?:-[a-z0-9]+)*", slug)), "invalid page slug")
    require(data.get("slug") == slug, "slug must match the JSON filename")
    anchors = plans.get(slug, {})
    require(bool(anchors), "page has no navigation plan")
    approval = data.get("approval")
    if not isinstance(approval, dict):
        errors.append("approval must be an object")
        approval = {}
    require(set(approval) == {"status", "source"}, "approval must contain exactly status and source")
    require(approval.get("status") in ("approved", "draft"), "approval status must be approved or draft")
    require(isinstance(approval.get("source"), str) and bool(approval.get("source", "").strip()), "approval source must explain the approval or pending review")
    research = data.get("research_anchor")
    expected_research = [key for key, row in anchors.items() if row["kind"] in ("h", "exact") and row["match"] in wording.RESEARCH_HEADINGS]
    require(research == (expected_research[0] if len(expected_research) == 1 else None), "research_anchor must match the page's recognized research heading (or null when absent)")
    items = data.get("items")
    if not isinstance(items, list):
        errors.append("items must be an array of [text, anchor] pairs")
        items = []
    require(approval.get("status") != "approved" or bool(items), "approved data must contain reviewed items")
    grouped = {}
    seen = set()
    for index, item in enumerate(items):
        prefix = f"item {index + 1}"
        if not isinstance(item, list) or len(item) != 2 or not all(isinstance(x, str) for x in item):
            errors.append(f"{prefix}: expected [text, anchor] strings")
            continue
        text, anchor = item
        require(bool(text) and text == text.strip() and not re.search(r"[\x00-\x1f\x7f<>]", text), f"{prefix}: text must be nonempty plain text without control characters")
        require(anchor in anchors, f"{prefix}: unknown planned anchor {anchor!r}")
        grouped.setdefault(anchor, []).append(text)
        normalized = " ".join(text.casefold().split())
        if normalized != "etc.":
            require(normalized not in seen, f"{prefix}: duplicate idea within or across boxes: {text!r}")
            seen.add(normalized)
        word_count = len([word for word in text.split() if word.lower() not in {"a", "an", "the"}])
        # Research may retain qualifications in longer phrases; it has no
        # arbitrary word-count cap. Length of ordinary phrases is mechanical.
        if anchor not in expected_research:
            require(word_count <= 4, f"{prefix}: ordinary phrase has {word_count} words; aim for about three")
        patterns = wording.VOICE + wording.CLAIMS
        if anchor not in expected_research:
            patterns += wording.EVIDENCE
        forbidden = wording.hits(text, patterns)
        require(not forbidden, f"{prefix}: forbidden wording: {', '.join(forbidden)}")
    coverage = data.get("coverage")
    if not isinstance(coverage, dict):
        errors.append("coverage must be an object")
        coverage = {}
    require(set(coverage) == set(grouped), "coverage must name exactly the summarized anchors")
    for anchor, texts in grouped.items():
        require(coverage.get(anchor) in ("most", "partial"), f"{anchor}: coverage must be most or partial")
        etc = [index for index, text in enumerate(texts) if text.casefold() == "etc."]
        require(not etc or etc == [len(texts) - 1], f"{anchor}: etc. may appear only once at the end")
        if coverage.get(anchor) == "partial":
            require(texts[-1] == "etc.", f"{anchor}: partial coverage must end with etc.")
    return errors


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("files", nargs="*", type=Path)
    args = parser.parse_args()
    files = args.files or sorted((HERE / "peek-data").glob("*.json"))
    try:
        plans = read_plans(PLAN_FILE.read_text(encoding="utf-8"))
    except (OSError, ValueError) as error:
        print(f"FAIL navigation source: {error}")
        return 1
    if not files:
        print("FAIL no page data found; nothing checked")
        return 1
    failed = 0
    for path in files:
        try:
            errors = validate(read_json(path), path.stem, plans)
        except (OSError, ValueError) as error:
            errors = [str(error)]
        if errors:
            failed += 1
            for error in errors:
                print(f"FAIL {path.name}: {error}")
        else:
            print(f"PASS {path.name}: schema, planned anchors, duplicates, phrase length and wording")
    print("Editorial review still required for accuracy, semantic overlap, coverage and approval.")
    return int(failed > 0)


if __name__ == "__main__":
    sys.exit(main())
