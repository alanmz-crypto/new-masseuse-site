#!/usr/bin/env python3
"""Read the release installer's verified markers; never guess a snapshot or ID."""
import hashlib
from collections import Counter
from html.parser import HTMLParser
import json
import pathlib
import re
import sys
from urllib.parse import unquote

BUNDLE = "peek-visitor-release-v1"
ROLES = {"core", "thai-massage", "therapeutic-massage", "deep-tissue-massage", "chair-massage"}
KEYS = {"schema", "bundle", "home", "state", "snapshot", "snapshot_sha256", "manifest_sha256", "state_sha256", "ids"}


def require(value, message):
    if not value:
        raise ValueError(message)


def unique_object(pairs):
    result = {}
    for key, value in pairs:
        require(key not in result, "duplicate JSON key")
        result[key] = value
    return result


def binding(log, manifest, state):
    lines = [line.removeprefix("RELEASE-SNAPSHOT ") for line in pathlib.Path(log).read_text().splitlines()
             if line.startswith("RELEASE-SNAPSHOT ")]
    require(len(lines) == 1, "expected exactly one verified RELEASE-SNAPSHOT marker")
    data = json.loads(lines[0], object_pairs_hook=unique_object)
    require(isinstance(data, dict) and set(data) == KEYS, "unexpected snapshot marker schema")
    require(type(data["schema"]) is int and data["schema"] == 1 and data["bundle"] == BUNDLE,
            "unexpected release identity")
    require(data["home"] == "http://localhost:8090" and data["state"] == state,
            "snapshot marker has the wrong origin or state")
    require(isinstance(data["snapshot"], str) and re.fullmatch(
        r"/backups/peek-release-before-[0-9]{8}-[0-9]{6}-[a-f0-9]{8}\.json", data["snapshot"]),
        "snapshot marker path is not an actual release snapshot")
    for key in ("snapshot_sha256", "manifest_sha256", "state_sha256"):
        require(isinstance(data[key], str) and re.fullmatch(r"[a-f0-9]{64}", data[key]),
                "snapshot marker contains an invalid digest")
    require(data["manifest_sha256"] == hashlib.sha256(pathlib.Path(manifest).read_bytes()).hexdigest(),
            "snapshot marker does not bind the current manifest")
    ids = data["ids"]
    require(isinstance(ids, dict) and set(ids) == ROLES and
            all(type(value) is int and value > 0 for value in ids.values()) and
            len(set(ids.values())) == len(ROLES), "snapshot marker must bind five distinct owned IDs")
    return data


def normalized(fingerprint, data):
    roles = {str(identity): role for role, identity in data["ids"].items()}
    found, output = set(), []
    for line in pathlib.Path(fingerprint).read_text().splitlines():
        cells = line.split("\t")
        require(len(cells) == 3 and cells[0] in {"page", "css", "snippet", "seo", "option"},
                "unexpected content fingerprint row")
        if cells[:2] == ["option", "wpcode_snippets"]:
            continue  # This existing cache contains the newly created IDs/dates.
        if cells[0] == "snippet" and cells[1] in roles:
            require(cells[1] not in found, "duplicate owned snippet fingerprint")
            found.add(cells[1])
            cells[1] = "release:" + roles[cells[1]]
        output.append("\t".join(cells))
    require(found == set(roles), "fingerprint is missing a snapshot-owned snippet")
    require(len(output) >= 10, "fingerprint is incomplete")
    return sorted(output)


class OriginalNavigation(HTMLParser):
    """Observe the original rendered fallback without running page scripts."""
    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.ids = Counter()
        self.jumps = []
        self.active_jump = None
        self.active_text = None
        self.text = {}

    def handle_starttag(self, tag, attrs):
        attrs = dict(attrs)
        identity = attrs.get("id")
        if identity:
            self.ids[identity] += 1
        if tag == "nav" and "ts-jump" in (attrs.get("class") or "").split():
            require(self.active_jump is None, "nested original jump navigation")
            self.active_jump = []
            self.jumps.append(self.active_jump)
        if tag == "a" and self.active_jump is not None:
            self.active_jump.append(attrs.get("href"))
        if (tag, identity) in {("style", "wp-custom-css"), ("script", "ts-section-guide-runtime")}:
            require(identity not in self.text, "duplicate original CSS/guide source")
            self.active_text = (tag, identity)
            self.text[identity] = ""

    def handle_data(self, data):
        if self.active_text:
            self.text[self.active_text[1]] += data

    def handle_endtag(self, tag):
        if tag == "nav":
            self.active_jump = None
        if self.active_text and self.active_text[0] == tag:
            self.active_text = None

    def signature(self, path):
        require(not any(identity.startswith("ts-peek-") for identity in self.ids),
                "Peek remains in the original rendered page")
        require(self.ids["wp-custom-css"] == 1 and self.text.get("wp-custom-css"),
                "original Additional CSS is missing from rendered evidence")
        require(len(self.jumps) <= 1, "duplicate original jump navigation")
        for links in self.jumps:
            require(bool(links) and len(links) == len(set(links)), "empty or duplicate original jump destinations")
            for href in links:
                require(isinstance(href, str) and href.startswith("#") and len(href) > 1 and
                        self.ids[unquote(href[1:])] == 1, "original jump destination is missing or ambiguous")
        return {"path": path, "jump_hrefs": self.jumps, "scroll_top_count": self.ids["ast-scroll-top"],
                "source_sha256": {key: hashlib.sha256(value.encode()).hexdigest() for key, value in sorted(self.text.items())}}


def main(args):
    command = args.pop(0) if args else ""
    if command == "snapshot" and len(args) == 3:
        log, manifest, state = args
        require(state in {"original", "applied"}, "unsupported snapshot state")
        print(binding(log, manifest, state)["snapshot"])
    elif command == "same" and len(args) == 3:
        first, second, manifest = args
        require(binding(first, manifest, "applied") == binding(second, manifest, "applied"),
                "repeat or verification changed the actual snapshot binding/state")
        print("PASS release repeated operation retains the exact verified snapshot binding")
    elif command == "original" and len(args) == 3:
        applied_log, original_log, manifest = args
        applied, original = binding(applied_log, manifest, "applied"), binding(original_log, manifest, "original")
        for key in KEYS - {"state", "state_sha256"}:
            require(applied[key] == original[key], "restored state changed the immutable snapshot binding")
        print("PASS release original state binds the same immutable snapshot and owned IDs")
    elif command == "warm" and len(args) == 3:
        first, second, manifest = args
        values = []
        for path in (first, second):
            lines = [line.removeprefix("RELEASE-PREFLIGHT ") for line in pathlib.Path(path).read_text().splitlines()
                     if line.startswith("RELEASE-PREFLIGHT ")]
            require(len(lines) == 1, "expected exactly one ready-state preflight marker")
            value = json.loads(lines[0], object_pairs_hook=unique_object)
            require(isinstance(value, dict) and set(value) == {"schema", "bundle", "home", "manifest_sha256", "state", "protected_state_sha256"},
                    "unexpected preflight marker schema")
            require(type(value["schema"]) is int and value["schema"] == 1 and value["bundle"] == BUNDLE and
                    value["home"] == "http://localhost:8090" and value["state"] == "ready" and
                    value["manifest_sha256"] == hashlib.sha256(pathlib.Path(manifest).read_bytes()).hexdigest() and
                    isinstance(value["protected_state_sha256"], str) and re.fullmatch(r"[a-f0-9]{64}", value["protected_state_sha256"]),
                    "invalid release preflight identity/digest")
            values.append(value)
        require(values[0] == values[1], "normal warm page reads changed protected release state")
        print("PASS unchanged warm page reads preserve the complete protected release state")
    elif command == "compare" and len(args) == 5:
        first_log, first_fp, second_log, second_fp, manifest = args
        first, second = (binding(log, manifest, "applied") for log in (first_log, second_log))
        require(first["snapshot"] != second["snapshot"], "independent applies must have distinct snapshots")
        require(set(first["ids"].values()).isdisjoint(second["ids"].values()),
                "independent applies unexpectedly reused owned snippet IDs")
        require(normalized(first_fp, first) == normalized(second_fp, second),
                "reapplied fingerprint differs beyond the five snapshot-owned IDs and WPCode cache")
        print("PASS release reapply matches; only the five owned snippet IDs and WPCode cache are normalized")
    elif command == "inventory" and len(args) == 2:
        inventory_file, manifest_file = args
        inventory, manifest = (json.loads(pathlib.Path(path).read_text(), object_pairs_hook=unique_object)
                               for path in (inventory_file, manifest_file))
        require(type(manifest.get("schema")) is int and manifest.get("schema") == 1 and manifest.get("bundle") == BUNDLE,
                "unexpected release manifest")
        rows, expected = inventory.get("pages"), manifest.get("pages")
        require(isinstance(rows, list) and isinstance(expected, list) and len(rows) == len(expected) == 15,
                "release must enumerate exactly the reviewed 15 visitor pages")
        for data in (rows, expected):
            require(all(isinstance(row, dict) and set(row) == {"id", "path", "title"} and
                        type(row["id"]) is int and row["id"] > 0 and
                        isinstance(row["path"], str) and re.fullmatch(r"/(?:[a-z0-9-]+/)*", row["path"]) and
                        isinstance(row["title"], str) and row["title"] for row in data),
                    "invalid visitor inventory record")
            require(len({row["id"] for row in data}) == 15 and len({row["path"] for row in data}) == 15,
                    "duplicate visitor inventory identity")
        require(sorted(rows, key=lambda row: row["id"]) == sorted(expected, key=lambda row: row["id"]),
                "actual published-page inventory differs from the reviewed release")
        require(not any(row["path"] in {"/rail-nav-ideas/", "/index-nav-ideas/", "/in-page-link-ideas/", "/progress-nav-ideas/"}
                        for row in rows), "practice pages must remain outside the release")
        for row in rows:
            print(row["path"])
    elif command == "original-html" and len(args) == 2:
        html, path = args
        require(re.fullmatch(r"/(?:[a-z0-9-]+/)*", path), "invalid original-page path")
        parser = OriginalNavigation()
        parser.feed(pathlib.Path(html).read_text())
        parser.close()
        print(json.dumps(parser.signature(path), sort_keys=True, separators=(",", ":")))
    else:
        raise ValueError("usage: snapshot LOG MANIFEST STATE | same/original/warm LOG LOG MANIFEST | compare LOG FP LOG FP MANIFEST | inventory INVENTORY MANIFEST | original-html HTML PATH")


if __name__ == "__main__":
    try:
        main(sys.argv[1:])
    except (OSError, ValueError, TypeError, KeyError) as error:
        print(f"FAIL release rehearsal evidence: {error}", file=sys.stderr)
        sys.exit(1)
