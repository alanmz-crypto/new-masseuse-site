"""Offline release-evidence controls using synthetic records only.

Execute the real evidence helper and selected real Bash functions. SQL, the
installer, curl and container cleanup are local doubles; no WordPress, Docker,
browser, credentials, stored site baseline or network is used.
"""
from copy import deepcopy
from hashlib import sha256
from pathlib import Path
import base64
import json
import os
import shutil
import subprocess
import sys
import tempfile


HERE = Path(__file__).resolve().parent
REHEARSE = HERE.parents[3] / "scripts/ci/rehearse.sh"
HELPER = HERE / "rehearsal-evidence.py"
ROLES = ["core", "thai-massage", "therapeutic-massage", "deep-tissue-massage", "chair-massage"]
PASSED = 0


def digest(text):
    return sha256(text.encode()).hexdigest()


def check(name, result, expected, message=None):
    global PASSED
    assert result.returncode == expected, (
        name, "exit", result.returncode, "expected", expected, result.stdout, result.stderr
    )
    if message is not None:
        assert message in result.stdout + result.stderr, (name, "missing diagnostic", message, result.stdout, result.stderr)
    PASSED += 1


def invoke(*args):
    return subprocess.run([sys.executable, "-I", "-B", str(HELPER), *map(str, args)],
                          capture_output=True, text=True, timeout=15)


def shell(source, args=()):
    env = dict(os.environ)
    env.pop("BASH_ENV", None)
    return subprocess.run(["bash", "--noprofile", "--norc", "-s", "--", *map(str, args)],
                          input=source, env=env, capture_output=True, text=True, timeout=15)


def function(source, name, end):
    # Execute the current production function, not a copied approximation of it.
    start = source.index(name + "() {")
    return source[start:source.index(end, start)]


def focused_fixture(family, engine):
    slugs = ["thai-massage", "therapeutic-massage", "deep-tissue-massage", "chair-massage"]
    if family != "neighbours":
        slugs += ["services", "corporate-massage-portland", "work-with-me", "privacy-policy", "search"]
    cases = [f"{slug}/{side}" for slug in slugs for side in ("right", "left")]
    kinds = {"neighbours": ("middle-short", "research-landscape"),
             "index-clearance": ("short-portrait", "landscape-top"),
             "quiet-nav": ("idle-reading", "index-open")}[family]
    assertions = []
    for case in cases:
        for label in ("geometry", "original content", "no JavaScript errors"):
            separator = ": " if family == "neighbours" else "/"
            assertions.append(f"PASS {family} {engine}/{case}{separator}{label} [check {len(assertions) + 1}]")
    completed = [] if family == "neighbours" else [f"INFO {family} completed {engine}/{case}" for case in cases]
    captures = [f"INLINE peek-{family}-{engine}-{slug}-{side}-{kind}.jpg base64:/9j/2Q=="
                for slug in slugs for side in ("right", "left") for kind in kinds]
    label = {"neighbours": "neighbours", "index-clearance": "index clearance", "quiet-nav": "quiet nav"}[family]
    limitation = "viewport resizing is not Safari, physical-device or OS text-scale validation."
    if family == "neighbours":
        touch = "trusted CDP touch and keyboard" if engine == "chromium" else "keyboard only"
        tail = f"8/8 completed cases, 8 page loads, 16 captures; 1s; {touch}; {limitation}"
    else:
        if family == "quiet-nav":
            limitation = "browser touch emulation is not physical-device, Safari or assistive-technology validation."
        tail = f"18/18 cases, 18 page loads, 36 captures, 1s; {limitation}"
    summary = f"INFO Peek {label} {engine}: {len(assertions)} passed, 0 failed; {tail}"
    return assertions + completed + captures + [summary]


def search_fixture(engine, contract):
    lines, number = [], 0
    for route in contract["routes"]:
        for width in contract["widths"]:
            for theme in contract["themes"]:
                case = f"{route['name']}/{width}/{theme}"
                for label in contract["assertions"][theme]:
                    number += 1
                    lines.append(f"PASS search-contrast {engine}/{case}/{label} [check {number}]")
                measures = {"rows": [{"contrast": 7.2}, {"contrast": 8.1}], "surfaces": [{"background": "rgb(20,30,40)"}]}
                lines += [f"INFO Search contrast {engine}/{case}: {json.dumps(measures)}",
                          f"INLINE search-contrast-{engine}-{route['name']}-{width}-{theme}.jpg base64:/9j/2Q==",
                          f"INFO Search contrast completed {engine}/{case}"]
            number += 1
            lines.append(f"PASS search-contrast {engine}/{route['name']}/{width}/return-to-light/{contract['returnAssertion']} [check {number}]")
    assert number == 150, "release fixture requires the complete current Search assertion contract"
    lines.append(f"INFO Search contrast {engine}: CHECK, {number} passed, 0 failed; 12/12 cases, 6 page loads, 12 captures; 1s; evidence fixture; {contract['limitation']}")
    return lines


def browser_contract_tests(work):
    """Exercise actual parser/adapter with synthetic complete child streams."""
    parser_cases = []
    for family in ("neighbours", "index-clearance", "quiet-nav"):
        for engine in ("chromium", "firefox", "webkit"):
            parser_cases.append({"family": family, "engine": engine,
                                 "name": f"valid-{family}-{engine}", "valid": True,
                                 "lines": focused_fixture(family, engine)})
        original = focused_fixture(family, "chromium")
        for mutation in ("missing-summary", "duplicate-summary", "wrong-count", "wrong-loads", "missing-image",
                         "duplicate-image", "wrong-image", "malformed-image", "wrong-case", "missing-case",
                         "check-number", "missing-check-number", "wrong-source", "stderr-error", "stderr-invalid"):
            lines = list(original)
            if mutation == "missing-summary": lines.pop()
            elif mutation == "duplicate-summary": lines.append(lines[-1])
            elif mutation == "wrong-count": lines[-1] = lines[-1].replace(" passed,", "0 passed,", 1)
            elif mutation == "wrong-loads": lines[-1] = lines[-1].replace(" page loads,", "0 page loads,", 1)
            elif mutation == "missing-image": lines.pop(next(i for i, line in enumerate(lines) if line.startswith("INLINE ")))
            elif mutation == "duplicate-image": lines.append(next(line for line in lines if line.startswith("INLINE ")))
            elif mutation == "wrong-image":
                index = next(i for i, line in enumerate(lines) if line.startswith("INLINE "))
                lines[index] = lines[index].replace(".jpg", "-unknown.jpg")
            elif mutation == "malformed-image":
                index = next(i for i, line in enumerate(lines) if line.startswith("INLINE "))
                lines[index] = lines[index].replace("/9j/2Q==", "AAAA")
            elif mutation == "wrong-case": lines[0] = lines[0].replace("thai-massage/right", "unknown/right")
            elif mutation == "missing-case":
                # Keep the count and sequence plausible while omitting one
                # actual case; the independent case set must reject it.
                lines = [line.replace("thai-massage/right", "thai-massage/left") if line.startswith("PASS ") else line for line in lines]
            elif mutation == "check-number": lines[0] = lines[0].replace("[check 1]", "[check 2]")
            elif mutation == "missing-check-number": lines[0] = lines[0].split(" [check", 1)[0]
            elif mutation == "wrong-source": lines[0] = lines[0].replace(f"PASS {family}", "PASS unknown")
            elif mutation == "stderr-error": lines.append("ERROR browser child failed")
            elif mutation == "stderr-invalid": lines.append("INVALID child evidence incomplete")
            parser_cases.append({"family": family, "engine": "chromium", "name": f"{family}-{mutation}", "valid": False, "lines": lines})
        if family != "neighbours":
            for mutation in ("missing-completion", "duplicate-completion"):
                lines = list(original)
                index = next(i for i, line in enumerate(lines) if line.startswith(f"INFO {family} completed "))
                if mutation == "missing-completion": lines.pop(index)
                else: lines.append(lines[index])
                parser_cases.append({"family": family, "engine": "chromium", "name": f"{family}-{mutation}", "valid": False, "lines": lines})
        if family == "quiet-nav":
            lines = list(original)
            lines[1] = lines[0].replace("[check 1]", "[check 2]")
            parser_cases.append({"family": family, "engine": "chromium", "name": "quiet-repeated-label", "valid": False, "lines": lines})
        lines = list(original)
        lines[0] = lines[0].replace("PASS ", "FAIL ", 1)
        count = sum(line.startswith(("PASS ", "FAIL ")) for line in lines)
        lines[-1] = lines[-1].replace(f"{count} passed, 0 failed", f"{count - 1} passed, 1 failed")
        parser_cases.append({"family": family, "engine": "chromium", "name": f"{family}-literal-failure", "valid": True, "failed": 1, "lines": lines})
    fixture = work / "focused-parser-fixtures.json"
    fixture.write_text(json.dumps(parser_cases))
    parser_runner = work / "check-focused-parser.mjs"
    parser_runner.write_text("import { readFileSync } from 'node:fs';\n"
                            f"import {{ focusedSpec, parseFocused }} from {json.dumps((HERE / 'focused-evidence.mjs').as_uri())};\n"
                            "const cases=JSON.parse(readFileSync(process.argv[2]));\n"
                            "for (const c of cases) { const r=parseFocused(c.lines, focusedSpec(c.family,c.engine));\n"
                            " if ((!r.invalid.length)!==c.valid || (c.failed && (r.failed!==c.failed || !r.failureObserved))) throw new Error(c.name+': '+JSON.stringify(r)); }\n"
                            "console.log('PASS '+cases.length+' focused evidence controls');\n")
    check("focused current-runtime evidence controls", subprocess.run(["node", str(parser_runner), str(fixture)],
          capture_output=True, text=True, timeout=15), 0, f"PASS {len(parser_cases)} focused evidence controls")

    package_root = work / "browser-package"
    package = package_root / "peek-release"
    shutil.copytree(HERE, package, ignore=shutil.ignore_patterns("*.py", "test-installer.php"))
    shutil.copy2(HERE.parent / "peek-nav.php", package_root / "peek-nav.php")
    shutil.copytree(HERE.parent / "peek-data", package_root / "peek-data")
    for name in ("core-index-clearance", "core-quiet-nav"):
        (package_root / name).mkdir()
        shutil.copy2(HERE.parent / name / "contract.mjs", package_root / name / "contract.mjs")
    search = work / "search-contrast"
    search.mkdir()
    for name in ("check-contract.mjs", "check-evidence.mjs", "search-contrast.css"):
        shutil.copy2(HERE.parent.parent / "search-contrast" / name, search / name)
    (package_root / "core-input-focus").mkdir()
    declared = json.loads((package / "manifest.json").read_text())
    inventory = work / "release-browser-inventory.json"
    inventory.write_text(json.dumps({"pages": declared["pages"]}))
    names = ["input focus chromium", "disclosures chromium", "disclosures firefox", "Kiro layout chromium",
             "Kiro layout firefox", "title rails chromium", "title rails firefox", "title strip chromium",
             "title strip firefox", "clearance chromium", "clearance firefox", "pages chromium", "pages firefox",
             "native touch", "retained Thai chromium"]
    retained = [f"INFO Peek {name}: 2 passed, 0 failed; 17 actual routes, fixture" for name in names]
    retained += [f"INFO core-input-focus rehearsal wrapper {name}: child_exit=0; signal=none; effective_exit=0; 15 leaf suites"
                 for name in ("input-focus-chromium", "retained-kiro-fixes")]
    retained += ["INFO core-input-focus rehearsal: PASSED, 2 wrapper suites, 30 passed, 0 failed; 1s"]
    supplemental = [f"INFO Peek {name} webkit: 2 passed, 0 failed; fixture" for name in ("disclosures", "Kiro layout")]
    supplemental += [f"INFO Peek WebKit suite {name}: child_exit=0; effective_exit=0; 2 passed, 0 failed; stdout=fixture; stderr=fixture"
                     for name in ("disclosures", "layout")]
    supplemental += ["INFO Peek WebKit supplemental verification: PASSED, 2/2 suites, 4 passed, 0 failed; complete logs: fixture"]
    outputs = {"retained-core": retained, "webkit": supplemental}
    for family in ("neighbours", "index-clearance", "quiet-nav"):
        for engine in ("chromium", "firefox", "webkit"):
            outputs[f"{family}-{engine}"] = focused_fixture(family, engine)
    # More than a pipe buffer, and terminal stderr without a newline, must
    # survive the real regular-file child logging unchanged.
    large_jpeg = base64.b64encode(b"\xff\xd8" + b"x" * 262144 + b"\xff\xd9").decode()
    rows = outputs["quiet-nav-webkit"]
    image_index = next(i for i, line in enumerate(rows) if line.startswith("INLINE "))
    rows[image_index] = rows[image_index].replace("/9j/2Q==", large_jpeg)
    # Load the exported browser contract rather than copying its semantic
    # assertion labels into another navigation map or a permissive count test.
    contract_source = HERE.parent.parent / "search-contrast/check-contract.mjs"
    contract_result = subprocess.run(["node", "--input-type=module", "-e",
        "const c=await import(process.argv[1]); console.log(JSON.stringify({routes:c.SEARCH_ROUTES,widths:c.SEARCH_WIDTHS,themes:c.SEARCH_THEMES,assertions:Object.fromEntries(c.SEARCH_THEMES.map(t=>[t,c.searchAssertions(t)])),returnAssertion:c.SEARCH_RETURN_ASSERTION,limitation:c.SEARCH_LIMITATION}));",
        contract_source.as_uri()], capture_output=True, text=True, timeout=10, check=True)
    search_contract = json.loads(contract_result.stdout)
    for engine in ("chromium", "firefox"):
        outputs[f"search-contrast-{engine}"] = search_fixture(engine, search_contract)
    output_file = package_root / "fixture-output.json"
    output_file.write_text(json.dumps(outputs))
    fixture_runner = package_root / "fixture-child.mjs"
    fixture_runner.write_text("import { appendFileSync, readFileSync, writeSync } from 'node:fs';\n"
        "export function run(name) {\n"
        "const rows=JSON.parse(readFileSync(new URL('./fixture-output.json',import.meta.url)))[name];\n"
        "appendFileSync(process.env.RELEASE_FIXTURE_TRACE,JSON.stringify(name)+'\\n');\n"
        "let code=0; const broken=process.env.RELEASE_FIXTURE_PHASE===name?process.env.RELEASE_FIXTURE_MODE:'';\n"
        "if(broken==='failed'){rows[0]=rows[0].replace('PASS ','FAIL '); const last=rows.length-1; rows[last]=rows[last].replace(/(\\d+) passed, 0 failed/,(_,n)=>(Number(n)-1)+' passed, 1 failed'); code=1;}\n"
        "if(broken==='missing-image')rows.splice(rows.findIndex(x=>x.startsWith('INLINE ')),1);\n"
        "if(broken==='inspection')rows[rows.length-1]=rows[rows.length-1].replace(': CHECK,',': INSPECTION,');\n"
        "if(broken==='wrong-search-label')rows[0]=rows[0].slice(0,rows[0].lastIndexOf('/')+1)+'unexpected assertion label [check 1]';\n"
        "writeSync(1, rows.join('\\r\\n')+'\\r\\n'); if(!name.startsWith('search-contrast-'))writeSync(2,'fixture stderr: Unicode →; backslash \\\\; last byte');\n"
        "if(broken==='stderr-error')writeSync(2,'\\nERROR child failed after its apparent pass\\n');\n"
        "if(broken==='signal')process.kill(process.pid,'SIGTERM'); else process.exitCode=code;\n}\n")
    (package_root / "core-input-focus/check-rehearsal.mjs").write_text("import {run} from '../fixture-child.mjs';run('retained-core');\n")
    (package_root / "webkit-fixture.mjs").write_text("import {run} from './fixture-child.mjs';run('webkit');\n")
    (package_root / "check-peek-webkit.sh").write_text('#!/bin/sh\nexec node "$(dirname "$0")/webkit-fixture.mjs"\n')
    for family in ("neighbours", "index-clearance", "quiet-nav"):
        (package_root / f"check-peek-{family}.mjs").write_text("import {run} from './fixture-child.mjs';\n"
            "const engine=process.argv.includes('--webkit')?'webkit':process.argv.includes('--firefox')?'firefox':'chromium';\n"
            f"run({json.dumps(family + '-')}+engine);\n")
    (search / "check-search-contrast.mjs").write_text("import {run} from '../browser-package/fixture-child.mjs';\n"
        "run('search-contrast-'+(process.argv.includes('--firefox')?'firefox':'chromium'));\n")
    phases = list(outputs)
    expected_total = 34 + sum(sum(line.startswith("PASS ") for line in outputs[name]) for name in phases[2:])
    for mode, bad, expected in (("pass", "", 0), ("failed", "neighbours-firefox", 1),
                                ("missing-image", "quiet-nav-webkit", 2), ("signal", "index-clearance-chromium", 2),
                                ("stderr-error", "retained-core", 2), ("stderr-error", "webkit", 2),
                                ("failed", "search-contrast-firefox", 1), ("inspection", "search-contrast-chromium", 2),
                                ("missing-image", "search-contrast-firefox", 2), ("stderr-error", "search-contrast-chromium", 2),
                                ("wrong-search-label", "search-contrast-chromium", 2)):
        case = work / ("browser-graph-" + mode + "-" + bad)
        case.mkdir()
        trace = case / "trace.jsonl"
        env = dict(os.environ, RUNNER_TEMP=str(case), TS_PEEK_TITLE_INVENTORY=str(inventory),
                   RELEASE_FIXTURE_TRACE=str(trace), RELEASE_FIXTURE_MODE=mode, RELEASE_FIXTURE_PHASE=bad)
        env.pop("TS_PEEK_REHEARSAL_PROFILE", None)
        env.pop("S_TARGET", None)
        result = subprocess.run(["node", str(package / "check-rehearsal.mjs"), "http://localhost:8090"],
                                env=env, capture_output=True, text=True, timeout=20)
        check("actual current release browser graph " + mode, result, expected)
        wanted = phases if not bad else phases[:phases.index(bad) + 1]
        assert [json.loads(line) for line in trace.read_text().splitlines()] == wanted, (mode, "child order/stop")
        folders = list((case / "ops-out").glob("peek-release-browser-*"))
        assert len(folders) == 1
        saved = json.loads((folders[0] / "results.json").read_text())
        assert saved["code"] == expected and [row["name"] for row in saved["results"]] == wanted
        for row in saved["results"]:
            stderr = "" if row["name"].startswith("search-contrast-") else "fixture stderr: Unicode →; backslash \\; last byte"
            if mode == "stderr-error" and row["name"] == bad:
                stderr += "\nERROR child failed after its apparent pass\n"
            assert Path(row["logs"]["stderr"]).read_bytes() == stderr.encode()
            if mode == "pass":
                assert Path(row["logs"]["stdout"]).read_bytes() == ("\r\n".join(outputs[row["name"]]) + "\r\n").encode()
        if mode == "pass":
            assert len(saved["results"]) == 13 and sum(len(row["summaries"]) for row in saved["results"]) == 28
            assert saved["counts"] == {"passed": expected_total, "failed": 0}
            assert "13/13 phases, 28/28 leaf suites" in result.stdout
            assert rows[image_index] in result.stdout, "complete large JPEG must survive every adapter"
    for guard in ("live-origin", "inherited-profile", "missing-inventory", "wrong-inventory", "frozen-drift", "source-drift",
                  "frozen-search-drift", "source-search-drift", "missing-search-declaration"):
        case = work / ("browser-guard-" + guard)
        case.mkdir()
        trace = case / "trace.jsonl"
        env = dict(os.environ, RUNNER_TEMP=str(case), TS_PEEK_TITLE_INVENTORY=str(inventory),
                   RELEASE_FIXTURE_TRACE=str(trace), RELEASE_FIXTURE_MODE="pass", RELEASE_FIXTURE_PHASE="")
        env.pop("TS_PEEK_REHEARSAL_PROFILE", None)
        env.pop("S_TARGET", None)
        origin = "https://thaisolitude.com" if guard == "live-origin" else "http://localhost:8090"
        if guard == "inherited-profile": env["TS_PEEK_REHEARSAL_PROFILE"] = "visitor-release-v1"
        if guard == "missing-inventory": env.pop("TS_PEEK_TITLE_INVENTORY")
        if guard == "wrong-inventory":
            changed = case / "inventory.json"
            changed.write_text(json.dumps({"pages": declared["pages"][:-1]}))
            env["TS_PEEK_TITLE_INVENTORY"] = str(changed)
        drift_file = {"frozen-drift": package / "payload/core.php",
                      "source-drift": package_root / "peek-nav.php",
                      "frozen-search-drift": package / "payload/search-contrast.css",
                      "source-search-drift": search / "search-contrast.css",
                      "missing-search-declaration": package / "manifest.json"}.get(guard)
        before = drift_file.read_bytes() if drift_file else None
        if guard == "missing-search-declaration":
            changed = json.loads(before)
            changed["payloads"].pop("search_contrast")
            drift_file.write_text(json.dumps(changed))
        elif drift_file:
            drift_file.write_bytes(before + b"\n/* synthetic changed bytes */\n")
        try:
            result = subprocess.run(["node", str(package / "check-rehearsal.mjs"), origin],
                                    env=env, capture_output=True, text=True, timeout=10)
        finally:
            if drift_file: drift_file.write_bytes(before)
        check("release browser preflight " + guard, result, 2, "no browser suites started")
        assert not trace.exists(), "bad release identity must stop before every browser child"


def main():
    with tempfile.TemporaryDirectory(prefix="peek-release-evidence-test-") as directory:
        work = Path(directory)
        pages = [{"id": 100 + index, "path": "/" if not index else f"/fixture-page-{index}/",
                  "title": f"Synthetic visitor page {index}"} for index in range(15)]
        manifest_data = {"schema": 1, "bundle": "peek-visitor-release-v1", "pages": pages}
        manifest = work / "manifest.json"
        manifest.write_text(json.dumps(manifest_data) + "\n")
        manifest_sha = sha256(manifest.read_bytes()).hexdigest()

        def log(name, value, prefix="RELEASE-SNAPSHOT"):
            path = work / (name + ".log")
            path.write_text("Synthetic installer output\n" + prefix + " " + json.dumps(value, separators=(",", ":")) + "\n")
            return path

        def raw(name, text):
            path = work / name.replace("/", "_")
            path.write_text(text)
            return path

        def marker(index):
            return {"schema": 1, "bundle": "peek-visitor-release-v1", "home": "http://localhost:8090",
                    "state": "applied", "snapshot": f"/backups/peek-release-before-20260101-00000{index}-{index:08x}.json",
                    "snapshot_sha256": digest(f"snapshot {index}"), "manifest_sha256": manifest_sha,
                    "state_sha256": digest(f"state {index}"),
                    "ids": dict(zip(ROLES, range(2000 + index * 10, 2005 + index * 10)))}

        fixed = ["page\tthai-massage\t" + digest("page"), "page\ttherapeutic-massage\t" + digest("thera"),
                 "css\t29\t" + digest("css"), "snippet\t1232\tpublish:" + digest("jump"),
                 "snippet\t1338\tpublish:" + digest("guide"), "seo\t1107:surerank_settings_general\t" + digest("seo"),
                 "option\tastra-settings\t" + digest("astra"), "option\thome\t" + digest("home")]

        def fingerprint(name, data):
            rows = fixed + [f"snippet\t{data['ids'][role]}\tpublish:{digest(role)}" for role in ROLES]
            rows.append("option\twpcode_snippets\t" + data["state_sha256"])
            return raw(name + ".content", "\n".join(rows) + "\n")

        first, second, third = (marker(index) for index in (1, 2, 3))
        logs = [log(f"apply-{index}", data) for index, data in enumerate((first, second, third), 1)]
        fps = [fingerprint(f"apply-{index}", data) for index, data in enumerate((first, second, third), 1)]
        check("snapshot path", invoke("snapshot", logs[0], manifest, "applied"), 0, first["snapshot"])
        check("exact repeat", invoke("same", logs[0], logs[0], manifest), 0)
        for earlier, later in ((0, 1), (0, 2), (1, 2)):
            check(f"independent apply {earlier + 1} to {later + 1}",
                  invoke("compare", logs[earlier], fps[earlier], logs[later], fps[later], manifest), 0)

        for key, value in (("home", "https://thaisolitude.com"), ("state", "original"),
                           ("snapshot_sha256", digest("changed snapshot")), ("state_sha256", digest("changed state"))):
            altered = deepcopy(first)
            altered[key] = value
            check("repeat refuses " + key, invoke("same", logs[0], log("repeat-" + key, altered), manifest), 1)
        mutations = {
            "wrong manifest": lambda data: data.update(manifest_sha256=digest("other manifest")),
            "boolean ID": lambda data: data["ids"].update(core=True),
            "duplicate ID": lambda data: data["ids"].update(core=data["ids"]["thai-massage"]),
            "missing role": lambda data: data["ids"].pop("chair-massage"),
            "extra role": lambda data: data["ids"].update(other=9999),
            "path traversal": lambda data: data.update(snapshot=first["snapshot"].replace("/backups/", "/backups/../")),
            "extra marker field": lambda data: data.update(extra="unexpected"),
        }
        for name, mutate in mutations.items():
            altered = deepcopy(first)
            mutate(altered)
            check(name, invoke("snapshot", log(name, altered), manifest, "applied"), 1)
        bad_logs = {
            "missing-marker": "No verified marker\n",
            "duplicate-marker": logs[0].read_text() * 2,
            "malformed-json": 'RELEASE-SNAPSHOT {"schema":\n',
            "duplicate-json-key": logs[0].read_text().replace('"schema":1', '"schema":1,"schema":1'),
            "duplicate-role-key": logs[0].read_text().replace('"core":2010', '"core":2010,"core":2010'),
        }
        for name, text in bad_logs.items():
            check(name, invoke("snapshot", raw(name + ".log", text), manifest, "applied"), 1)
        check("same apply is not independent", invoke("compare", logs[0], fps[0], logs[0], fps[0], manifest), 1)

        # B->F alone cannot detect reusing D's snapshot or IDs. Require the same
        # helper to reject D->F, even though B->F still has distinct identities.
        for field in ("snapshot", "ids"):
            altered = deepcopy(third)
            altered[field] = deepcopy(second[field])
            changed_log, changed_fp = log("third-reuses-" + field, altered), fingerprint("third-reuses-" + field, altered)
            check("first-to-third alone misses reused " + field,
                  invoke("compare", logs[0], fps[0], changed_log, changed_fp, manifest), 0)
            check("second-to-third rejects reused " + field,
                  invoke("compare", logs[1], fps[1], changed_log, changed_fp, manifest), 1)
        swapped = deepcopy(second)
        swapped["ids"]["core"], swapped["ids"]["thai-massage"] = swapped["ids"]["thai-massage"], swapped["ids"]["core"]
        check("owned role reassignment", invoke("compare", logs[0], fps[0], log("swapped-roles", swapped), fps[1], manifest), 1)

        for name, old, new in (
            ("page hash", digest("page"), digest("changed page")), ("CSS hash", digest("css"), digest("changed CSS")),
            ("option hash", digest("astra"), digest("changed option")),
            ("unrelated snippet ID", "snippet\t1232\t", "snippet\t1233\t"),
            ("SEO identity", "seo\t1107:", "seo\t1108:"),
            ("owned hash", digest("core"), digest("changed core")),
            ("owned status", "snippet\t2020\tpublish:", "snippet\t2020\tdraft:"),
        ):
            changed = raw(name + ".content", fps[1].read_text().replace(old, new))
            check(name, invoke("compare", logs[0], fps[0], logs[1], changed, manifest), 1)
        rows = fps[1].read_text().splitlines()
        changed_rows = {
            "missing owned record": [row for row in rows if not row.startswith("snippet\t2020\t")],
            "removed unrelated record": [row for row in rows if not row.startswith("seo\t")],
            "new unrelated record": rows + ["snippet\t9999\tpublish:" + digest("new")],
            "duplicate owned record": rows + ["snippet\t2020\tpublish:" + digest("core")],
            "unexpected row kind": rows + ["term\t1\t" + digest("term")],
        }
        for name, changed in changed_rows.items():
            check(name, invoke("compare", logs[0], fps[0], logs[1], raw(name + ".content", "\n".join(changed) + "\n"), manifest), 1)

        original = deepcopy(second)
        original.update(state="original", state_sha256=digest("restored original"))
        check("original binding", invoke("original", logs[1], log("original", original), manifest), 0)
        for key, value in (("snapshot", first["snapshot"]), ("snapshot_sha256", first["snapshot_sha256"]),
                           ("ids", first["ids"]), ("manifest_sha256", digest("wrong manifest")),
                           ("home", "https://thaisolitude.com"), ("state", "applied")):
            altered = deepcopy(original)
            altered[key] = value
            check("original refuses " + key, invoke("original", logs[1], log("original-" + key, altered), manifest), 1)

        warm = {"schema": 1, "bundle": "peek-visitor-release-v1", "home": "http://localhost:8090",
                "state": "ready", "manifest_sha256": manifest_sha, "protected_state_sha256": digest("warm state")}
        warm_log = log("warm", warm, "RELEASE-PREFLIGHT")
        check("stable warm state", invoke("warm", warm_log, warm_log, manifest), 0)
        for key, value in (("protected_state_sha256", digest("changed warm state")), ("state", "applied"),
                           ("schema", True), ("manifest_sha256", digest("wrong manifest")),
                           ("home", "https://thaisolitude.com")):
            altered = deepcopy(warm)
            altered[key] = value
            check("warm refuses " + key, invoke("warm", warm_log, log("warm-" + key, altered, "RELEASE-PREFLIGHT"), manifest), 1)
        check("duplicate preflight marker", invoke("warm", warm_log, raw("warm-duplicate.log", warm_log.read_text() * 2), manifest), 1)
        check("duplicate preflight JSON key", invoke("warm", warm_log, raw("warm-duplicate-key.log", warm_log.read_text().replace('"state":"ready"', '"state":"ready","state":"ready"')), manifest), 1)

        def inventory_case(name, actual, expected=manifest_data, code=1):
            inventory = raw(name + "-inventory.json", json.dumps({"pages": actual}))
            declared = raw(name + "-manifest.json", json.dumps(expected))
            check(name, invoke("inventory", inventory, declared), code)

        inventory_case("valid inventory", pages, code=0)
        inventory_case("inventory order independent", list(reversed(pages)), code=0)
        invalid_schema = deepcopy(manifest_data)
        invalid_schema["schema"] = True
        inventory_case("boolean manifest schema", pages, invalid_schema)
        inventory_case("missing page", pages[:-1])
        inventory_case("extra page", pages + [{"id": 999, "path": "/extra/", "title": "Extra"}])
        for field, value in (("id", True), ("id", pages[0]["id"]), ("path", pages[0]["path"]),
                             ("path", "https://example.invalid/"), ("title", ""), ("title", "Changed"), ("extra", "unexpected")):
            altered = deepcopy(pages)
            altered[1][field] = value
            inventory_case(f"inventory changed {field} {value!r}", altered)
        for route in ("/rail-nav-ideas/", "/index-nav-ideas/", "/progress-nav-ideas/", "/in-page-link-ideas/"):
            altered = deepcopy(pages)
            altered[1]["path"] = route
            declared = deepcopy(manifest_data)
            declared["pages"] = altered
            inventory_case("practice " + route.strip("/"), altered, declared)
        check("malformed inventory JSON", invoke("inventory", raw("broken-inventory.json", "{"), manifest), 1)
        duplicate_inventory = raw("duplicate-inventory.json", '{"pages":' + json.dumps(pages) + ',"pages":' + json.dumps(pages) + '}')
        check("duplicate inventory JSON key", invoke("inventory", duplicate_inventory, manifest), 1)

        html = ('<!doctype html><html><head><style id="wp-custom-css">.fixture{color:navy}</style></head><body>'
                '<nav class="fixture ts-jump"><a href="#one">One</a><a href="#section%2Dtwo">Two</a></nav>'
                '<h2 id="one">One</h2><h2 id="section-two">Two</h2><p>Unrelated paragraph.</p>'
                '<button id="ast-scroll-top">Top</button>'
                '<script id="ts-section-guide-runtime">window.fixtureGuide=true;</script></body></html>')
        original_html = raw("original.html", html)
        check("original navigation HTML", invoke("original-html", original_html, "/fixture-page/"), 0)
        no_jump_html = raw("no-jump.html", '<style id="wp-custom-css">.fixture{color:navy}</style><h1>Unmapped visitor page</h1>')
        check("original page without jump or guide", invoke("original-html", no_jump_html, "/fixture-page/"), 0)
        html_mutations = {
            "stale Peek runtime": html.replace("</body>", '<script id="ts-peek-nav-runtime">stale</script></body>'),
            "missing CSS": html.replace('<style id="wp-custom-css">.fixture{color:navy}</style>', ""),
            "empty CSS": html.replace(".fixture{color:navy}", ""),
            "duplicate CSS": html.replace("</head>", '<style id="wp-custom-css">extra</style></head>'),
            "duplicate guide": html.replace("</body>", '<script id="ts-section-guide-runtime">extra</script></body>'),
            "missing target": html.replace('id="one"', 'id="removed"'),
            "ambiguous target": html.replace("</body>", '<div id="one"></div></body>'),
            "duplicate links": html.replace('href="#section%2Dtwo"', 'href="#one"'),
            "external jump": html.replace('href="#one"', 'href="https://example.invalid/"'),
            "missing href": html.replace('href="#one"', ""),
            "empty jump row": html.replace('<a href="#one">One</a><a href="#section%2Dtwo">Two</a>', ""),
            "nested jump row": html.replace('<a href="#one">', '<nav class="ts-jump"><a href="#one">'),
        }
        for name, content in html_mutations.items():
            check(name, invoke("original-html", raw(name + ".html", content), "/fixture-page/"), 1)
        check("HTML path guard", invoke("original-html", original_html, "https://example.invalid/"), 1)

        source = REHEARSE.read_text()
        compose_start = source.index('cat > "$work/compose.yml" <<YML')
        compose_template = source[compose_start:source.index("\nYML\n", compose_start) + len("\nYML\n")]
        compose_boot = source[compose_start:source.index("\nready=0", compose_start)]
        compose_shim = r'''
set -uo pipefail
work=$1; release_rehearsal=$2; mkdir -p "$work"
db_img=fixture-mariadb; wp_img=fixture-wordpress; cli_img=fixture-wpcli
pub=/fixture/public_html; wwm=/fixture/wwm; bk=/fixture/backups
invalid() { printf 'INVALID compose fixture: %s\n' "$*" >&2; exit 2; }
dc() {
  printf '%s\n' "$*" >> "$work/container-calls.txt"
  cp "$work/compose.yml" "$work/at-first-start.yml"
}
'''
        legacy = work / "compose-template"
        check("original Compose template", shell(compose_shim + compose_template, (legacy, 0)), 0)
        original_config = (legacy / "compose.yml").read_text()
        configs = {}
        for release in (0, 1):
            case = work / f"compose-release-{release}"
            check(f"Compose generated before first start, release={release}",
                  shell(compose_shim + compose_boot, (case, release)), 0)
            assert (case / "container-calls.txt").read_text().splitlines() == ["up -d db"], "fixture must stop at first container start"
            configs[release] = (case / "at-first-start.yml").read_text()
            assert configs[release] == (case / "compose.yml").read_text(), "network isolation was added after first start"
        network = "networks:\n  default:\n    internal: true\n"
        port = '    ports: ["127.0.0.1:8090:80"]\n'
        assert configs[0] == original_config and "networks:" not in configs[0], "existing rehearsal network configuration changed"
        assert configs[0].count(port) == 1, "existing rehearsal must retain its published loopback port"
        assert configs[1].count(network) == 1 and configs[1].replace(network, "") == configs[0].replace(port, ""), "release must add exactly one internal default network and remove only the published port before first start"
        assert "network_mode:" not in configs[1] and configs[1].count("networks:") == 1, "a copied service bypasses the internal default network"
        assert "ports:" not in configs[1], "isolated release must use its owned relay instead of Docker port publishing"

        relay_start = source.index('dc up -d wordpress >/dev/null 2>&1 || invalid "could not start the web container"')
        relay = source[relay_start:source.index("\nup=0;", relay_start)]
        relay_shim = r'''
set -uo pipefail
out=$1; fixture_mode=$2; release_rehearsal=$3; here=/fixture/ci
release_relay_pid=''; mkdir -p "$out"
container_id=aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa
network_id=bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb
trace() { printf '%s\n' "$*" >> "$out/calls.txt"; }
invalid() { printf 'INVALID relay fixture: %s\n' "$*" >&2; exit 2; }
dc() {
  trace "dc $*"
  case "$*" in
    'up -d wordpress') return 0 ;;
    'ps -q wordpress')
      if [ "$fixture_mode" = container-invalid ]; then printf 'ambiguous container\n'; else printf '%s\n' "$container_id"; fi
      [ "$fixture_mode" != container-error ] ;;
    *) return 9 ;;
  esac
}
docker() {
  trace "docker $*"
  if [ "$1" = inspect ]; then
    [ "$2" = --format ] && [ "$4" = "$container_id" ] || return 9
    case "$3" in
      *NetworkID*)
        if [ "$fixture_mode" = network-invalid ]; then printf '%s%s\n' "$network_id" "$network_id"; else printf '%s\n' "$network_id"; fi
        [ "$fixture_mode" != network-error ] ;;
      *IPAddress*) printf '172.30.0.2\n'; [ "$fixture_mode" != backend-error ] ;;
      *) return 9 ;;
    esac
  else
    [ "$1" = network ] && [ "$2" = inspect ] && [ "$3" = --format ] && [ "$5" = "$network_id" ] || return 9
    if [ "$fixture_mode" = external-network ]; then printf 'false\n'; else printf 'true\n'; fi
    [ "$fixture_mode" != internal-error ]
  fi
}
python3() {
  trace "python3 $*"
  [ "$*" = '-I -B /fixture/ci/rehearsal-loopback.py 172.30.0.2' ] || return 9
  case "$fixture_mode" in
    startup-failure) printf 'fixture relay bind failed\n'; return 23 ;;
    wrong-ready) printf 'READY rehearsal loopback 127.0.0.1:8091 -> 172.30.0.2:80\n' ;;
    *) printf 'READY rehearsal loopback 127.0.0.1:8090 -> 172.30.0.2:80\n' ;;
  esac
}
# The launch double finishes immediately. Synchronize its log, then model a
# live/dead child explicitly; the separate relay tests exercise real sockets.
kill() {
  [ "$1" = -0 ] && [ "$2" = "$release_relay_pid" ] || return 9
  builtin wait "$2" 2>/dev/null || true
  [ "$fixture_mode" != startup-failure ] && [ "$fixture_mode" != ready-but-dead ]
}
sleep() { :; }
trap '[ -z "${release_relay_pid:-}" ] || builtin wait "$release_relay_pid" 2>/dev/null || true' EXIT
'''
        relay_cases = (("success", 1, 0), ("legacy", 0, 0), ("container-invalid", 1, 2),
                       ("container-error", 1, 2), ("network-invalid", 1, 2), ("network-error", 1, 2),
                       ("external-network", 1, 2), ("internal-error", 1, 2), ("backend-error", 1, 2),
                       ("startup-failure", 1, 2), ("wrong-ready", 1, 2), ("ready-but-dead", 1, 2))
        for mode, release, expected in relay_cases:
            case = work / ("relay-" + mode)
            result = shell(relay_shim + relay, (case, mode, release))
            check("relay orchestration " + mode, result, expected)
            calls = (case / "calls.txt").read_text().splitlines()
            if mode == "legacy":
                assert calls == ["dc up -d wordpress"], "other rehearsal lanes must not start a relay"
            elif mode in {"success", "startup-failure", "wrong-ready", "ready-but-dead"}:
                assert calls[-1] == "python3 -I -B /fixture/ci/rehearsal-loopback.py 172.30.0.2", "relay must receive only the verified backend"
                assert len(calls) == 6, ("identity/network/address checks before relay", calls)
            else:
                assert not any(call.startswith("python3 ") for call in calls), "failed identity/network lookup must stop before relay launch"
                expected_calls = {"container-invalid": 2, "container-error": 2, "network-invalid": 3,
                                  "network-error": 3, "external-network": 4, "internal-error": 4, "backend-error": 5}
                assert len(calls) == expected_calls[mode], ("failed relay prerequisite must stop subsequent lookups", mode, calls)

        cleanup_source = function(source, "cleanup", "\ntrap cleanup EXIT")
        cleanup_shim = r'''
set -uo pipefail
work=$1/ts-rehearsal-123; fixture_pid=$2; fixture_kill_status=$3
release_relay_pid=$fixture_pid; cli_img=fixture-cli; mkdir -p "$work"
kill() { printf 'kill %s\n' "$*" >> "$work/calls.txt"; return "$fixture_kill_status"; }
wait() { printf 'wait %s\n' "$*" >> "$work/calls.txt"; return 0; }
dc() { printf 'dc %s\n' "$*" >> "$work/calls.txt"; }
rm() { printf 'rm %s\n' "$*" >> "$work/calls.txt"; }
docker() { printf 'unexpected Docker cleanup\n' >&2; return 9; }
'''
        for name, pid, kill_status in (("owned", "43210", 0), ("already-exited", "43210", 1), ("no-relay", "", 0)):
            case = work / ("relay-cleanup-" + name)
            result = shell(cleanup_shim + cleanup_source + '\ncleanup\ncleanup\n[ -z "$release_relay_pid" ]\n', (case, pid, kill_status))
            check("relay cleanup " + name, result, 0)
            calls = (case / "ts-rehearsal-123/calls.txt").read_text().splitlines()
            signals = [call for call in calls if call.startswith(("kill ", "wait "))]
            assert signals == (["kill 43210", "wait 43210"] if pid else []), ("cleanup may signal only its recorded child once", signals)

        recovery_label = source.index('echo; echo "[4f] exact visitor-release recovery')
        recovery_start = source.rfind('if [ "$release_rehearsal" = 1 ]; then', 0, recovery_label)
        recovery = source[recovery_start:source.index('\necho; echo "[5]', recovery_label)]
        recovery_shim = r'''
set -uo pipefail
out=$1; release_helper=$2; release_manifest=$3
release_active_snapshot=$4; fixture_final_snapshot=$5; fixture_render_exit=$6
release_rehearsal=1; package_browser_rc=0; fails=0
invalid() { printf 'INVALID fixture: %s\n' "$*" >&2; exit 2; }
check() { if [ "$2" -ne 0 ]; then fails=$((fails+1)); printf 'FAIL %s\n' "$1"; fi; }
release_evidence() { command python3 -I -B "$release_helper" "$@"; }
release_snapshot() { release_evidence snapshot "$1" "$release_manifest" "${2:-applied}"; }
nocache() { grep -v -P '^option\twpcode_snippets\t' "$1"; }
release_original_render() {
  [ "$1" = original-after-browser ] && [ "$2" = "$out/original-before-navigation.jsonl" ] || return 9
  touch "$out/render-called"
  return "$fixture_render_exit"
}
inst_run() {
  local name=$1
  case "$name" in
    verify-after-browser)
      [ "${3:-}" = "$release_active_snapshot" ] || return 9
      cp "$out/re-apply.log" "$out/$name.log" ;;
    rollback-after-browser)
      [ "${3:-}" = "$release_active_snapshot" ] || return 9
      cp "$out/fixture-original.log" "$out/$name.log" ;;
    verify-original-after-browser)
      [ "${3:-}" = "$release_active_snapshot" ] && [ -f "$out/render-called" ] || return 9
      cp "$out/fixture-original.log" "$out/$name.log" ;;
    reapply-after-browser) cp "$out/fixture-final.log" "$out/$name.log" ;;
    verify-final-reapply)
      [ "${3:-}" = "$fixture_final_snapshot" ] || return 9
      cp "$out/fixture-final.log" "$out/$name.log" ;;
    *) return 9 ;;
  esac
}
fp() {
  case "$1" in
    E) cp "$out/A.content" "$out/E.content" ;;
    F) cp "$out/fixture-final.content" "$out/F.content" ;;
    *) return 9 ;;
  esac
}
'''
        for reused in (None, "snapshot", "ids", "render-failure"):
            case = work / ("recovery-" + str(reused))
            case.mkdir()
            final = deepcopy(third)
            if reused in ("snapshot", "ids"):
                final[reused] = deepcopy(second[reused])
            for destination, input_file in (("apply.log", logs[0]), ("re-apply.log", logs[1]),
                                            ("B.content", fps[0]), ("D.content", fps[1]),
                                            ("fixture-original.log", log("recovery-original", original)),
                                            ("fixture-final.log", log("recovery-final", final)),
                                            ("fixture-final.content", fingerprint("recovery-final", final))):
                (case / destination).write_bytes(input_file.read_bytes())
            (case / "A.content").write_text("\n".join(fixed) + "\n")
            result = shell(recovery_shim + recovery + '\nexit "$((fails > 0))"\n',
                           (case, HELPER, manifest, second["snapshot"], final["snapshot"], 1 if reused == "render-failure" else 0))
            check("actual recovery block " + str(reused), result, 0 if reused is None else 1)
            if reused == "render-failure":
                assert not (case / "reapply-after-browser.log").exists(), "failed rollback rendering must block another apply"

        render = function(source, "release_original_render", "\nrelease_cleanup()")
        render_shim = r'''
set -uo pipefail
out=$1; helper=$2; fixture_html=$3; fixture_mode=$4
mkdir -p "$out"; mapfile -t pages < "$out/paths.txt"
release_evidence() { command python3 -I -B "$helper" "$@"; }
release_render_meta() {
  [ "$1" = original-before ] || return 8
  printf 'meta-%s\t%s\n' "$3" "$2" >> "$out/render-events.txt"
  if [ "$2" = /fixture-page-7/ ] && [ "$fixture_mode" = "metadata-$3" ]; then return 7; fi
}
curl() {
  local output='' url='' path
  while [ "$#" -gt 0 ]; do
    case "$1" in -o) output=$2; shift ;; http://localhost:8090/*) url=$1 ;; esac
    shift
  done
  [ -n "$output" ] && [ -n "$url" ] || return 9
  path=${url#http://localhost:8090}
  printf '%s\n' "$path" >> "$out/requested-paths.txt"
  printf 'GET\t%s\n' "$path" >> "$out/render-events.txt"
  cp "$fixture_html" "$output"
  if [ "$path" = /fixture-page-7/ ]; then
    case "$fixture_mode" in
      http) printf 202; return 0 ;;
      curl) printf 200; return 28 ;;
    esac
  fi
  printf 200
}
'''
        render_baseline = work / "render-baseline"
        render_baseline.mkdir()
        paths = "\n".join(page["path"] for page in pages) + "\n"
        (render_baseline / "paths.txt").write_text(paths)
        render_body = render_shim + render + '\nrelease_original_render original-before "${5:-}"\n'
        check("all original routes rendered", shell(render_body, (render_baseline, HELPER, original_html, "none")), 0)
        reference = render_baseline / "original-before-navigation.jsonl"
        assert len(reference.read_text().splitlines()) == 15, "render evidence must cover all routes"
        assert (render_baseline / "render-events.txt").read_text().splitlines() == [
            f"{event}\t{page['path']}" for page in pages for event in ("meta-before", "GET", "meta-after")
        ], "each original GET must be bracketed by separate read-only metadata captures"
        rendered_changes = {
            "unchanged": html,
            "unrelated markup": html.replace("Unrelated paragraph.", "Other unrelated paragraph."),
            "CSS cache drift": html.replace("color:navy", "color:red"),
            "guide cache drift": html.replace("fixtureGuide=true", "fixtureGuide=false"),
            "scroll-to-top cache drift": html.replace('<button id="ast-scroll-top">Top</button>', ""),
            "jump cache drift": html.replace('<a href="#one">One</a><a href="#section%2Dtwo">Two</a>', '<a href="#section%2Dtwo">Two</a><a href="#one">One</a>'),
            "stale Peek": html_mutations["stale Peek runtime"],
            "http": html,
            "curl": html,
            "metadata-before": html,
            "metadata-after": html,
            "missing route": html,
        }
        for name, content in rendered_changes.items():
            case = work / ("render-" + name)
            case.mkdir()
            (case / "paths.txt").write_text(paths if name != "missing route" else "\n".join(paths.splitlines()[:-1]) + "\n")
            fixture = raw(name + "-render.html", content)
            result = shell(render_body, (case, HELPER, fixture, name, reference))
            check("rendered original " + name, result, 0 if name in ("unchanged", "unrelated markup") else 1)
            if name in ("http", "curl"):
                assert len((case / "requested-paths.txt").read_text().splitlines()) == 8, "route failure must stop further requests"
            if name in ("metadata-before", "metadata-after"):
                assert len((case / "requested-paths.txt").read_text().splitlines()) == (7 if name == "metadata-before" else 8), "metadata failure must stop subsequent GETs"
                assert (case / "render-events.txt").read_text().splitlines()[-1] == f"meta-{name.removeprefix('metadata-')}\t/fixture-page-7/"

        fp = function(source, "fp", "\npages=(")
        shim = r'''
set -uo pipefail
work=$1; out=$work/out; prefix=wp_; mkdir -p "$out"
invalid() { printf 'REHEARSAL INVALID: %s\n' "$*" >&2; exit 2; }
sql() {
  if [ "${1:-}" = -e ]; then printf 'wp_posts\nwp_options\n'; return 0; fi
  cat > "$work/generated.sql"
  local index
  for index in $(seq 1 "$fixture_rows"); do printf 'page\tpage-%s\t0123456789012345678901234567890123456789012345678901234567890123\n' "$index"; done
  return "$fixture_sql_exit"
}
fixture_rows=$2; fixture_sql_exit=$3
'''
        for name, count, status, expected in (("complete SQL output", 12, 0, 0), ("partial SQL output", 12, 1, 2), ("incomplete SQL output", 8, 0, 2)):
            check(name, shell(shim + fp + "\nfp A\n", (work / name, count, status)), expected)

        # The comparator cannot detect records missing equally from both files;
        # the real fp() status guard above must reject that failed SQL stream.
        partials = []
        for index, path in enumerate(fps[:2]):
            partials.append(raw(f"partial-{index}.content", "\n".join(row for row in path.read_text().splitlines() if not row.startswith(("page\t", "seo\t"))) + "\n"))
        check("partial comparison alone is not completeness proof", invoke("compare", logs[0], partials[0], logs[1], partials[1], manifest), 0)

        inst_run = function(source, "inst_run", "\ncore_focus_snapshot_from_log()")
        load_pages = function(source, "load_pages", "\nerrs()")
        runner_shim = r'''
set -uo pipefail
out=$1/out; broken=$2; mkdir -p "$out"
inst=link-ideas/peek-release/install-peek-release.php; release_rehearsal=1
wpc() { printf 'Synthetic installer result\n'; printf 'Synthetic stderr result\n' >&2; [ "$broken" != wpc ]; }
tee() {
  command tee "$@"; local result=$?
  [ "$broken" != stdout-tee ] || [ "$1" != "$out/test.log" ] || return 1
  [ "$broken" != stderr-tee ] || [ "$1" != "$out/test.stderr.log" ] || return 1
  return "$result"
}
grep() { printf 'unexpected merged-output filtering\n' >&2; return 1; }
sed() { command sed "$@"; local result=$?; [ "$broken" != sed ] || return 1; return "$result"; }
curl() { if [ "$broken" = http ]; then printf 500; else printf 200; fi; [ "$broken" != curl ]; }
'''
        for broken in ("none", "wpc", "stdout-tee", "stderr-tee", "sed"):
            check("installer pipeline " + broken,
                  shell(runner_shim + inst_run + "\ninst_run test apply\n", (work / ("runner-" + broken), broken)),
                  0 if broken == "none" else 1)
            folder = work / ("runner-" + broken) / "out"
            assert (folder / "test.log").read_text() == "Synthetic installer result\n"
            assert (folder / "test.stderr.log").read_text() == "Synthetic stderr result\n"
        # Execute the production stream capture around fragmented and delayed
        # stderr. Only stdout can supply a strict release marker.
        for mode in ("fragmented", "late-stderr", "stderr-decoy"):
            folder = work / ("runner-marker-" + mode)
            emitted_marker = logs[0].read_text()
            runner = runner_shim + r'''
wpc() {
  printf 'Deprecated: ' >&2
  cat "$3"
  printf 'Using null as array offset\n' >&2
  if [ "$broken" = late-stderr ]; then (sleep .02; printf 'late stderr fragment\n' >&2) & fi
  if [ "$broken" = stderr-decoy ]; then cat "$4" >&2; fi
}
'''
            # The double receives fixed fixture paths after the real eval-file
            # arguments. It does not invoke PHP or read a snapshot.
            runner = runner.replace('cat "$3"', 'cat "$fixture_stdout"').replace('cat "$4" >&2', 'cat "$fixture_decoy" >&2')
            runner += '\nfixture_stdout=$3; fixture_decoy=$4\n' + inst_run + '\ninst_run test apply\n'
            check("strict release stdout marker " + mode, shell(runner, (folder, mode, logs[0], logs[1])), 0)
            assert (folder / "out/test.log").read_text() == emitted_marker
            expected_stderr = "Deprecated: Using null as array offset\n"
            if mode == "late-stderr": expected_stderr += "late stderr fragment\n"
            if mode == "stderr-decoy": expected_stderr += logs[1].read_text()
            assert (folder / "out/test.stderr.log").read_text() == expected_stderr
            check("strict release marker parser " + mode, invoke("snapshot", folder / "out/test.log", manifest, "applied"), 0, first["snapshot"])
        for broken in ("none", "http", "curl"):
            check("page status " + broken,
                  shell(runner_shim + load_pages + "\npages=(/ /privacy-policy/); load_pages\n", (work / ("pages-" + broken), broken)),
                  0 if broken == "none" else 1)

        check("release cleanup ownership, teardown, status and evidence",
              subprocess.run([sys.executable, "-I", "-B", str(HERE / "test-cleanup.py")],
                             capture_output=True, text=True, timeout=60),
              0, "51 offline ownership, teardown, status and evidence controls passed")

        check("release render-metadata redaction, scope and row identity",
              subprocess.run([sys.executable, "-I", "-B", str(HERE / "test-render-meta.py")],
                             capture_output=True, text=True, timeout=60),
              0, "offline controls passed; no database or network requests.")

        browser_contract_tests(work)

    print(f"Peek visitor-release rehearsal evidence: {PASSED} offline controls passed; no site or browser requests.")


if __name__ == "__main__":
    main()
