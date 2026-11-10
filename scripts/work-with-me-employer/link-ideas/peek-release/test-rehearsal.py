"""Offline release-evidence controls using synthetic records only.

Execute the real evidence helper and selected real Bash functions. SQL, the
installer, curl and container cleanup are local doubles; no WordPress, Docker,
browser, credentials, stored site baseline or network is used.
"""
from copy import deepcopy
from hashlib import sha256
from pathlib import Path
import json
import os
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
curl() {
  local output='' url='' path
  while [ "$#" -gt 0 ]; do
    case "$1" in -o) output=$2; shift ;; http://localhost:8090/*) url=$1 ;; esac
    shift
  done
  [ -n "$output" ] && [ -n "$url" ] || return 9
  path=${url#http://localhost:8090}
  printf '%s\n' "$path" >> "$out/requested-paths.txt"
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
wpc() { printf 'Synthetic installer result\n'; [ "$broken" != wpc ]; }
tee() { command tee "$@"; local result=$?; [ "$broken" != tee ] || return 1; return "$result"; }
grep() { command grep "$@"; local result=$?; [ "$broken" != grep ] || return 1; return "$result"; }
sed() { command sed "$@"; local result=$?; [ "$broken" != sed ] || return 1; return "$result"; }
curl() { if [ "$broken" = http ]; then printf 500; else printf 200; fi; [ "$broken" != curl ]; }
'''
        for broken in ("none", "wpc", "tee", "grep", "sed"):
            check("installer pipeline " + broken,
                  shell(runner_shim + inst_run + "\ninst_run test apply\n", (work / ("runner-" + broken), broken)),
                  0 if broken == "none" else 1)
        for broken in ("none", "http", "curl"):
            check("page status " + broken,
                  shell(runner_shim + load_pages + "\npages=(/ /privacy-policy/); load_pages\n", (work / ("pages-" + broken), broken)),
                  0 if broken == "none" else 1)

        exit_cleanup = function(source, "release_cleanup", '\n\nif [ "$release_rehearsal"')
        for initial in (0, 1, 2):
            case = work / f"cleanup-{initial}"
            out = case / "out"
            out.mkdir(parents=True)
            allowed = {"apply.log", "A.content", "errors-before.txt", "original-before-navigation.jsonl", "title-strip-pages.json", "release-paths.txt"}
            for name in allowed | {"private.sql", "snapshot.json", "release-original-page.html"}:
                (out / name).write_text("Synthetic " + name + "\n")
            cleanup_shim = r'''
set -uo pipefail
RUNNER_TEMP=$1; out=$1/out
cleanup() { touch "$RUNNER_TEMP/cleanup-ran"; }
'''
            result = shell(cleanup_shim + exit_cleanup + '\ntrap release_cleanup EXIT\nexit "$2"\n', (case, initial))
            check(f"EXIT retains status {initial}", result, initial)
            kept = {path.name for path in (case / "ops-out/peek-release-native").iterdir()}
            assert kept == allowed and (case / "cleanup-ran").exists(), ("retained evidence scope", initial, kept)

    print(f"Peek visitor-release rehearsal evidence: {PASSED} offline controls passed; no site or browser requests.")


if __name__ == "__main__":
    main()
