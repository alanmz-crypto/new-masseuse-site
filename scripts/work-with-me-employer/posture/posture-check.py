#!/usr/bin/env python3
"""Security-posture regression check: read-only, public requests only, no login, no server access.

Usage: python3 posture-check.py <base-url> [<base-url> ...] [-v] [--role live|staging] [--csp require|skip] [--skip CODE[,CODE]] [--timeout SECONDS]
Exits 0 only if every check passed; 1 if any check failed; 2 if something could not be checked: nothing ran, or SiteGround's bot challenge
(HTTP 202, header sg-captcha: challenge) answered instead of the site, which happens when this machine has sent a lot of traffic. A challenge page
is never reported as a pass and never as an exposure: those checks are reported BLOCKED and the run is INVALID until it is repeated later.

It pins what was fixed on 2026-10-16/17 so it cannot quietly regress:
  headers     the five security headers snippet 1036 sends, with their exact values, and no X-Powered-By
  csp         exactly one Content-Security-Policy on the home page, byte-equal to the policy validated in a browser (TESTED_POLICY in
              headers/tests/csp-snippet-test.php, the same string the snippet test proves the snippet emits); the login page must NOT get it
  exposure    backup, config, log, VCS and directory-listing paths are never served (not 2xx); xmlrpc.php is blocked; install.php says "already installed"
  identity    /?author=1 does not redirect to an author archive; the public users endpoint (if open) exposes no email address
  robots      staging: "Disallow: /" (kept out of search); live: crawlable and advertises the sitemap
  transport   http:// redirects to https:// on the same host; the TLS certificate verifies and has at least 14 days left
Informational lines (not failures): readme.html reachable, number of users the public endpoint lists (known open item).
Server-side checks (plugin versions, files on disk, error logs) are not public and live in check-no-new-zips.sh and post-change-test.sh.
Check codes for --skip: hsts xfo refpol permpol corp xpb csp login-csp exposure xmlrpc install author users robots redirect tls home
"""
import argparse
import http.client
import json
import os
import re
import socket
import ssl
import sys
import time
import urllib.parse

HERE = os.path.dirname(os.path.abspath(__file__))
POLICY_SRC = os.path.join(HERE, "..", "headers", "tests", "csp-snippet-test.php")
HEADERS = [  # (code, header name, exact expected value, https only)
    ("hsts", "strict-transport-security", "max-age=31536000; includeSubDomains", True),
    ("xfo", "x-frame-options", "SAMEORIGIN", False),
    ("refpol", "referrer-policy", "strict-origin-when-cross-origin", False),
    ("permpol", "permissions-policy", "geolocation=(), microphone=(), camera=()", False),
    ("corp", "cross-origin-resource-policy", "same-site", False),
]
NOT_PUBLIC = [  # paths that must never answer 2xx
    "/wp-content/wpvividbackups/", "/wp-content/uploads/", "/wp-content/plugins/", "/wp-content/themes/", "/wp-includes/",
    "/wp-content/debug.log", "/php_errorlog", "/.git/config", "/.env", "/wp-config.php.bak", "/wp-config.php~", "/backup.zip",
]
MIN_CERT_DAYS = 14


class FetchError(Exception):
    pass


class Blocked(FetchError):
    """SiteGround's bot challenge answered instead of the site: the request proves nothing about the site."""


def fetch(base, path, timeout, limit=20000):
    """One GET, no redirect following. Returns (status, [(lowercase-name, value), ...], body-bytes)."""
    u = urllib.parse.urlparse(base)
    cls = http.client.HTTPSConnection if u.scheme == "https" else http.client.HTTPConnection
    try:
        c = cls(u.hostname, u.port, timeout=timeout)
        c.request("GET", path, headers={"User-Agent": "posture-check/1", "Accept": "*/*"})
        r = c.getresponse()
        body = r.read(limit)
        hdrs = [(k.lower(), v) for k, v in r.getheaders()]
        c.close()
    except (OSError, http.client.HTTPException) as e:
        raise FetchError(f"{type(e).__name__}: {e}") from e
    if r.status == 202 and values(hdrs, "sg-captcha"):
        raise Blocked(f"GET {path} was answered by SiteGround's bot challenge (HTTP 202)")
    return r.status, hdrs, body


def values(hdrs, name):
    return [v for k, v in hdrs if k == name]


def expected_policy(path):
    try:
        src = open(path, encoding="utf-8").read()
    except OSError as e:
        raise SystemExit(f"cannot read the validated policy from {path}: {e}") from e
    m = re.search(r'const TESTED_POLICY = "([^"\n]+)";', src)
    if not m or "default-src" not in m.group(1):
        raise SystemExit(f"no TESTED_POLICY found in {path}")
    return m.group(1)


def days_left(not_after, now=None):
    """Whole days from now until a certificate's notAfter string (as ssl.getpeercert() reports it)."""
    return (ssl.cert_time_to_seconds(not_after) - (time.time() if now is None else now)) / 86400


def cert_days_left(host, port, timeout):
    ctx = ssl.create_default_context()
    with socket.create_connection((host, port), timeout) as s, ctx.wrap_socket(s, server_hostname=host) as t:
        return days_left(t.getpeercert()["notAfter"])


class Report:
    def __init__(self, label, skip, verbose):
        self.label, self.skip, self.verbose, self.passed, self.failed, self.blocked = label, skip, verbose, 0, 0, 0

    def check(self, code, ok, msg):
        if code in self.skip:
            return
        if ok:
            self.passed += 1
        else:
            self.failed += 1
        if self.verbose or not ok:
            print(f"{'PASS' if ok else 'FAIL'}  {self.label}  [{code}]  {msg}")

    def info(self, msg):
        print(f"INFO  {self.label}  {msg}")


def check_site(base, role, csp_mode, skip, timeout, policy, verbose=False):
    u = urllib.parse.urlparse(base)
    https = u.scheme == "https"
    host = u.hostname
    role = role or ("staging" if re.match(r"^staging\d+\.", host or "") else "live")
    rep = Report(base, skip, verbose)

    def get(path):
        try:
            return fetch(base, path, timeout)
        except Blocked:
            rep.blocked += 1
            return None
        except FetchError as e:
            rep.check("home" if path == "/" else "exposure", False, f"GET {path} failed: {e}")
            return None

    home = get("/")
    if home:
        status, hdrs, _ = home
        rep.check("home", status == 200, f"/ answers {status}")
        for code, name, want, https_only in HEADERS:
            if https_only and not https:
                continue
            got = values(hdrs, name)
            rep.check(code, got == [want], f"{name}: {got[0] if len(got) == 1 else got or 'missing'}" + ("" if got == [want] else f" (expected {want})"))
        rep.check("xpb", not values(hdrs, "x-powered-by"), "no X-Powered-By header" if not values(hdrs, "x-powered-by") else f"X-Powered-By leaks: {values(hdrs, 'x-powered-by')}")
        if csp_mode == "require":
            got = values(hdrs, "content-security-policy")
            rep.check("csp", got == [policy], "exactly one Content-Security-Policy, byte-equal to the validated policy" if got == [policy] else f"{len(got)} header(s); expected exactly one equal to the validated policy; got {[g[:70] for g in got]}")

    login = get("/wp-login.php")
    if login and csp_mode == "require":
        got = values(login[1], "content-security-policy")
        rep.check("login-csp", policy not in got, "login page does not receive the site policy (core's own frame-ancestors only)" if policy not in got else "the site policy reached wp-login.php")

    for p in NOT_PUBLIC:
        r = get(p)
        if r:
            rep.check("exposure", not 200 <= r[0] < 300, f"{p} -> {r[0]}" + ("" if not 200 <= r[0] < 300 else "  SERVED PUBLICLY"))
    r = get("/xmlrpc.php")
    if r:
        rep.check("xmlrpc", not 200 <= r[0] < 300, f"/xmlrpc.php -> {r[0]}")
    r = get("/wp-admin/install.php")
    if r:
        text = re.sub(r"<[^>]+>", " ", r[2].decode("utf-8", "replace"))
        rep.check("install", bool(re.search(r"already\s+installed", text, re.I)), "install.php reports the site as already installed" if re.search(r"already\s+installed", text, re.I) else f"install.php did not say 'already installed' (status {r[0]})")

    r = get("/?author=1")
    if r:
        loc = (values(r[1], "location") or [""])[0]
        rep.check("author", "/author/" not in loc, f"/?author=1 -> {r[0]} {loc or '(no redirect)'}")
    r = get("/wp-json/wp/v2/users")
    if r:
        if r[0] == 200:
            body = r[2].decode("utf-8", "replace")
            try:
                data = json.loads(body)
            except ValueError:
                rep.check("users", False, "users endpoint answered 200 but not with JSON (cannot verify what it exposes)")
            else:
                leaks = "@" in body or any("email" in u_ for u_ in data if isinstance(u_, dict))
                rep.check("users", not leaks and isinstance(data, list), "public users list exposes no email address" if not leaks else "the public users list exposes an email address")
                rep.info(f"public users endpoint is OPEN and lists {len(data)} user(s) (slug/name only; on 2026-10-17 the slug was verified to differ from the login name): known, low risk")
        else:
            rep.check("users", True, f"public users endpoint answers {r[0]} (not listing users)")
    r = get("/readme.html")
    if r and r[0] == 200:
        rep.info("readme.html is reachable (reveals the WordPress version): known, low value")

    r = get("/robots.txt")
    if r:
        lines = [ln.strip() for ln in r[2].decode("utf-8", "replace").splitlines()]
        blocked = any(re.fullmatch(r"Disallow:\s*/", ln) for ln in lines)
        sitemap = any(ln.lower().startswith("sitemap:") and "/wp-sitemap.xml" in ln for ln in lines)
        if role == "staging":
            rep.check("robots", r[0] == 200 and blocked, "staging robots.txt has 'Disallow: /'" if blocked else "staging robots.txt does NOT block crawling")
        else:
            rep.check("robots", r[0] == 200 and not blocked and sitemap, "live robots.txt is crawlable and advertises the sitemap" if (not blocked and sitemap) else f"live robots.txt wrong (blocks everything: {blocked}; sitemap line: {sitemap})")

    if https:
        try:
            r = fetch(f"http://{host}", "/", timeout)
            loc = (values(r[1], "location") or [""])[0]
            ok = r[0] in (301, 302, 307, 308) and loc.startswith(f"https://{host}")
            rep.check("redirect", ok, f"http://{host}/ -> {r[0]} {loc}")
        except Blocked:
            rep.blocked += 1
        except FetchError as e:
            rep.check("redirect", False, f"http request failed: {e}")
        try:
            d = cert_days_left(host, u.port or 443, timeout)
            rep.check("tls", d >= MIN_CERT_DAYS, f"certificate verifies; {d:.0f} days left" + ("" if d >= MIN_CERT_DAYS else f" (need {MIN_CERT_DAYS}+; renewal may be broken)"))
        except (OSError, ssl.SSLError, KeyError, ValueError) as e:
            rep.check("tls", False, f"TLS verification failed: {e}")
    return rep


def main():
    ap = argparse.ArgumentParser(description="Read-only security-posture regression check.")
    ap.add_argument("urls", nargs="+")
    ap.add_argument("--role", choices=["live", "staging"], help="default: staging if the host is stagingNN.*, else live")
    ap.add_argument("--csp", choices=["require", "skip"], default="require", help="skip for a site cloned before the CSP deploy (2026-10-17)")
    ap.add_argument("--skip", default="", help="comma-separated check codes to skip")
    ap.add_argument("-v", "--verbose", action="store_true", help="also print every passing check")
    ap.add_argument("--timeout", type=float, default=25)
    ap.add_argument("--policy-file", default=POLICY_SRC, help=argparse.SUPPRESS)
    a = ap.parse_args()
    skip = {s for s in a.skip.split(",") if s}
    policy = expected_policy(a.policy_file) if a.csp == "require" else ""
    passed = failed = blocked = 0
    for url in a.urls:
        rep = check_site(url.rstrip("/"), a.role, a.csp, skip, a.timeout, policy, a.verbose)
        if rep.blocked:
            print(f"BLOCKED  {rep.label}: {rep.blocked} request(s) were answered by SiteGround's bot challenge (HTTP 202), not by the site; those checks did NOT run. Wait a while and repeat.")
        print(f"{'ok  ' if not rep.failed and not rep.blocked and rep.passed else 'FAIL' if rep.failed else 'INVALID' if rep.blocked else 'FAIL'}  {rep.label}: {rep.passed} passed, {rep.failed} failed" + (f", {rep.blocked} blocked" if rep.blocked else ""))
        passed, failed, blocked = passed + rep.passed, failed + rep.failed, blocked + rep.blocked
    print(f"{passed} passed, {failed} failed across {len(a.urls)} site(s)")
    if passed + failed == 0:
        print("INVALID: no check ran, so nothing is proven.")
        return 2
    if failed:
        return 1
    if blocked:
        print("INVALID: some checks were blocked by the bot challenge and did not run, so this is not a pass.")
        return 2
    return 0


if __name__ == "__main__":
    sys.exit(main())
