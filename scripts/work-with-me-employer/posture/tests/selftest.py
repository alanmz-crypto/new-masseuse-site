#!/usr/bin/env python3
"""Self-test for posture-check.py (runs in check-all.sh, offline): a mock site that is correct, then the same site broken ONE way at a time.
The known-good mock must pass; every break must make the tool exit 1 and name the right check. Also: a dead server and an empty URL set must
never read as success, and the certificate-expiry arithmetic is checked directly. Standard library only."""
import copy
import http.server
import importlib.util
import json
import os
import re
import subprocess
import sys
import threading

HERE = os.path.dirname(os.path.abspath(__file__))
TOOL = os.path.join(HERE, "..", "posture-check.py")
POLICY = re.search(r'const TESTED_POLICY = "([^"\n]+)";', open(os.path.join(HERE, "..", "..", "headers", "tests", "csp-snippet-test.php"), encoding="utf-8").read()).group(1)

GOOD = {
    "home": 200,
    "home_headers": [
        ("X-Frame-Options", "SAMEORIGIN"), ("Referrer-Policy", "strict-origin-when-cross-origin"),
        ("Permissions-Policy", "geolocation=(), microphone=(), camera=()"), ("Cross-Origin-Resource-Policy", "same-site"),
        ("Content-Security-Policy", POLICY),
    ],
    "login_headers": [("Content-Security-Policy", "frame-ancestors 'self';")],
    "status": {},  # path -> status override; anything not listed answers 403 (exposure paths) per the default below
    "install": "<h1>Already Installed</h1>You appear to have already installed WordPress.",
    "author": ("301", "/"),
    "users": json.dumps([{"id": 1, "name": "Butsaba", "slug": "butsaba", "link": "/author/butsaba/"}]),
    "robots": "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n\nSitemap: http://x/wp-sitemap.xml\n",
}
OPEN_PATHS = ("/wp-content/wpvividbackups/", "/wp-content/uploads/", "/wp-content/plugins/", "/wp-content/themes/", "/wp-includes/",
              "/wp-content/debug.log", "/php_errorlog", "/.git/config", "/.env", "/wp-config.php.bak", "/wp-config.php~", "/backup.zip", "/xmlrpc.php")


def handler_for(cfg):
    class H(http.server.BaseHTTPRequestHandler):
        def log_message(self, *a):
            pass

        def reply(self, status, headers=(), body=""):
            self.send_response(status)
            for k, v in headers:
                self.send_header(k, v)
            data = body.encode()
            self.send_header("Content-Length", str(len(data)))
            self.end_headers()
            self.wfile.write(data)

        def do_GET(self):
            p = self.path
            if cfg.get("challenge") or p in cfg.get("challenge_paths", ()):
                return self.reply(202, [("sg-captcha", "challenge"), ("Content-Type", "text/html")], "<html>challenge</html>")
            if p in cfg["status"]:
                return self.reply(cfg["status"][p], [], "x")
            if p == "/":
                return self.reply(cfg["home"], cfg["home_headers"], "<html>home</html>")
            if p == "/wp-login.php":
                return self.reply(200, cfg["login_headers"], "<html>login</html>")
            if p in OPEN_PATHS:
                return self.reply(403, [], "forbidden")
            if p == "/wp-admin/install.php":
                return self.reply(200, [], cfg["install"])
            if p == "/?author=1":
                return self.reply(int(cfg["author"][0]), [("Location", cfg["author"][1])], "")
            if p == "/wp-json/wp/v2/users":
                return self.reply(200, [("Content-Type", "application/json")], cfg["users"])
            if p == "/robots.txt":
                return self.reply(200, [], cfg["robots"])
            return self.reply(404, [], "nope")
    return H


def run(cfg, *extra):
    srv = http.server.ThreadingHTTPServer(("127.0.0.1", 0), handler_for(cfg))
    threading.Thread(target=srv.serve_forever, daemon=True).start()
    try:
        r = subprocess.run([sys.executable, "-I", "-B", TOOL, f"http://127.0.0.1:{srv.server_address[1]}", "--timeout", "5", *extra],
                           capture_output=True, text=True, timeout=120, check=False)
    finally:
        srv.shutdown()
        srv.server_close()
    return r.returncode, r.stdout + r.stderr


def variant(**changes):
    c = copy.deepcopy(GOOD)
    c.update(changes)
    return c


def without(headers, name):
    return [(k, v) for k, v in headers if k != name]


def replace_header(headers, name, value):
    return [(k, value if k == name else v) for k, v in headers]


fails = 0


def expect(label, ok, detail=""):
    global fails
    print(("PASS " if ok else "FAIL ") + label + (f"  [{detail}]" if detail and not ok else ""))
    if not ok:
        fails += 1


code, out = run(GOOD)
expect("known-good mock site passes (exit 0)", code == 0 and "FAIL" not in out, out[-300:])
code, out = run(GOOD, "--role", "staging")
expect("a live-style robots.txt FAILS when the site is declared staging", code == 1 and "[robots]" in out, out[-300:])
code, out = run(variant(robots="User-agent: *\nDisallow: /\n"), "--role", "staging")
expect("staging robots.txt with Disallow: / passes", code == 0, out[-300:])

H = GOOD["home_headers"]
BREAKS = [
    ("CSP header missing", variant(home_headers=without(H, "Content-Security-Policy")), "csp"),
    ("CSP changed by one character", variant(home_headers=replace_header(H, "Content-Security-Policy", POLICY.replace("'self'", "'sef'", 1))), "csp"),
    ("two CSP headers", variant(home_headers=H + [("Content-Security-Policy", POLICY)]), "csp"),
    ("X-Frame-Options weakened", variant(home_headers=replace_header(H, "X-Frame-Options", "ALLOWALL")), "xfo"),
    ("Referrer-Policy missing", variant(home_headers=without(H, "Referrer-Policy")), "refpol"),
    ("Permissions-Policy changed", variant(home_headers=replace_header(H, "Permissions-Policy", "geolocation=*")), "permpol"),
    ("Cross-Origin-Resource-Policy missing", variant(home_headers=without(H, "Cross-Origin-Resource-Policy")), "corp"),
    ("X-Powered-By leaks", variant(home_headers=H + [("X-Powered-By", "PHP/8.5")]), "xpb"),
    ("site policy leaks onto wp-login.php", variant(login_headers=[("Content-Security-Policy", POLICY)]), "login-csp"),
    ("home page answers 500", variant(home=500), "home"),
    ("backup folder served (200)", variant(status={"/wp-content/wpvividbackups/": 200}), "exposure"),
    ("a stray .env served (200)", variant(status={"/.env": 200}), "exposure"),
    ("debug.log served (200)", variant(status={"/wp-content/debug.log": 200}), "exposure"),
    ("php_errorlog in the web root served (200)", variant(status={"/php_errorlog": 200}), "exposure"),
    ("xmlrpc.php open (200)", variant(status={"/xmlrpc.php": 200}), "xmlrpc"),
    ("install.php offers a fresh install", variant(install="<h1>Welcome</h1> Information needed. Site Title"), "install"),
    ("/?author=1 reveals the author archive", variant(author=("301", "/author/butsaba/")), "author"),
    ("users endpoint leaks an email", variant(users=json.dumps([{"id": 1, "name": "B", "slug": "b", "email": "b@example.com"}])), "users"),
    ("live robots.txt blocks everything", variant(robots="User-agent: *\nDisallow: /\n"), "robots"),
    ("live robots.txt lost its sitemap line", variant(robots="User-agent: *\nDisallow: /wp-admin/\n"), "robots"),
]
for label, cfg, code_name in BREAKS:
    code, out = run(cfg)
    expect(f"broken: {label} -> exit 1 and [{code_name}] named", code == 1 and re.search(rf"FAIL .*\[{re.escape(code_name)}\]", out) is not None, f"exit={code} {out[-240:]!r}")

# SiteGround's bot challenge (HTTP 202 + sg-captcha) must never read as a pass or as an exposure.
CHALLENGE = variant(challenge=True)
code, out = run(CHALLENGE)
expect("bot challenge on every request -> INVALID (exit 2), not pass, not 'served publicly'", code == 2 and "BLOCKED" in out and "SERVED PUBLICLY" not in out and "PASS" not in out.replace("passed", ""), f"exit={code} {out[-300:]!r}")
code, out = run(variant(challenge_paths=["/.env", "/backup.zip"]))
expect("challenge on SOME requests -> INVALID (exit 2) even though the rest pass", code == 2 and "BLOCKED" in out and "SERVED PUBLICLY" not in out, f"exit={code} {out[-300:]!r}")
code, out = run(variant(challenge_paths=["/.env"], status={"/backup.zip": 200}))
expect("a real failure still wins over a challenge (exit 1)", code == 1 and re.search(r"FAIL .*\[exposure\]", out) is not None, f"exit={code} {out[-300:]!r}")

# edge-check.sh (the one-request pre-check used by check-all.sh <url> and post-change-test.sh)
EDGE = os.path.join(HERE, "..", "edge-check.sh")


def edge(cfg, url=None):
    srv = http.server.ThreadingHTTPServer(("127.0.0.1", 0), handler_for(cfg))
    threading.Thread(target=srv.serve_forever, daemon=True).start()
    try:
        r = subprocess.run(["bash", EDGE, url if url is not None else f"http://127.0.0.1:{srv.server_address[1]}/"], capture_output=True, text=True, timeout=60, check=False)
    finally:
        srv.shutdown()
        srv.server_close()
    return r.returncode, r.stdout + r.stderr


code, out = edge(GOOD)
expect("edge-check: a normal site answers (exit 0)", code == 0 and "answered (200)" in out, f"exit={code} {out!r}")
code, out = edge(variant(home=500))
expect("edge-check: a site answering 500 is still 'the site answered' (exit 0); that is posture-check's job", code == 0, f"exit={code} {out!r}")
code, out = edge(variant(challenge=True))
expect("edge-check: the bot challenge (202 + sg-captcha) -> exit 2 and BLOCKED", code == 2 and "BLOCKED" in out, f"exit={code} {out!r}")
code, out = edge(GOOD, "http://127.0.0.1:9/")
expect("edge-check: no answer at all -> exit 1", code == 1 and "NO ANSWER" in out, f"exit={code} {out!r}")
code, out = edge(GOOD, "")
expect("edge-check: no URL given -> exit 1, never success", code == 1, f"exit={code} {out!r}")

code, out = run(GOOD, "--skip", "csp")
expect("--skip works (CSP skipped on a good site: still exit 0)", code == 0, out[-200:])
code, out = run(variant(home_headers=without(H, "Content-Security-Policy")), "--csp", "skip")
expect("--csp skip tolerates a site without the policy", code == 0, out[-200:])
code, out = run(variant(home_headers=without(H, "Content-Security-Policy")), "--csp", "skip", "-v")
expect("-v prints passing checks", code == 0 and re.search(r"PASS .*\[hsts\]|PASS .*\[xfo\]", out) is not None, out[-200:])

# A server that is not there must be a failure, never a pass.
r = subprocess.run([sys.executable, "-I", "-B", TOOL, "http://127.0.0.1:9", "--timeout", "2"], capture_output=True, text=True, timeout=120, check=False)
expect("a dead server fails (exit 1), never passes", r.returncode == 1 and "FAIL" in r.stdout, f"exit={r.returncode} {r.stdout[-200:]!r}")
r = subprocess.run([sys.executable, "-I", "-B", TOOL, "http://127.0.0.1:9", "--timeout", "2", "--skip", "home,exposure,xmlrpc,install,author,users,robots,xpb,hsts,xfo,refpol,permpol,corp", "--csp", "skip"], capture_output=True, text=True, timeout=120, check=False)
expect("when every check is skipped the result is INVALID (exit 2), not success", r.returncode == 2 and "INVALID" in r.stdout, f"exit={r.returncode} {r.stdout[-200:]!r}")

# Certificate arithmetic, checked directly against the tool's own function.
spec = importlib.util.spec_from_file_location("posture_check", TOOL)
mod = importlib.util.module_from_spec(spec)
spec.loader.exec_module(mod)
import ssl  # noqa: E402
now = ssl.cert_time_to_seconds("Nov 27 20:13:13 2026 GMT") - 42 * 86400
expect("cert days-left: 42 days before notAfter reads 42.0", abs(mod.days_left("Nov 27 20:13:13 2026 GMT", now) - 42.0) < 1e-6)
expect("cert days-left: already expired reads negative", mod.days_left("Nov 27 20:13:13 2026 GMT", now + 50 * 86400) < 0)
expect("expected policy is read from the snippet test and matches", mod.expected_policy(os.path.join(HERE, "..", "..", "headers", "tests", "csp-snippet-test.php")) == POLICY)

print(f"\nRESULT: {'%d FAILED' % fails if fails else 'all passed'}")
sys.exit(1 if fails else 0)
