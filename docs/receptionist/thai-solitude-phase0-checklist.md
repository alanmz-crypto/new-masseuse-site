# Thai Solitude Phase 0 release checklist — v5

> Planning attachment to [receptionist plan v7](thai-solitude-receptionist-plan.md). Complete on the actual release candidate and record evidence before asking Ryan to approve a live promotion. This is the AI-free FAQ and contact release; the [chat suite](thai-solitude-receptionist-acceptance.md) is reference material for a phase that is not planned.

## Practice and source

- [ ] Document the frequency and channel of inquiries Butsaba misses while giving a massage, how they are handled today, and whether clients need an immediate answer, an acknowledgment with a callback time, or self-service booking. Check whether FAQ/contact/booking links address the common tasks. Do not present this release as live phone or message coverage if it cannot provide it; do not promise a callback time Butsaba has not approved and can meet.
- [ ] Dry-run representative non-identifying inquiries through the proposed path: routine fact, immediate answer, callback, booking without speaking, and suitability requiring Butsaba's judgment. Record which tasks it handles and which still interrupt her. If the common missed-call task remains unanswered, label the FAQ as a site improvement rather than a receptionist solution.
- [ ] Butsaba confirms the practice accepts private clients. Ryan reviews the **actual employment agreement** for the FAQ, contact, and booking path. If either point is unresolved, do not publish a client-booking FAQ.
- [ ] Questions come from Butsaba's actual experience. Unanswered health-suitability questions use one approved boundary entry and contact path, with no individual contraindication advice.
- [ ] Each published registry entry has a named editor, source, dated `approval_ref`, compliance checklist SHA-256/reviewer/date, and future `review_by`. An expired entry is removed or replaced before release.
- [ ] Approved wording in the authoring registry matches the WordPress page and the release record names both revisions.

## Regulated copy and rendered output

- [ ] Review FAQ questions and answers, contact prompts, fixed site notices, navigation labels, link text, title and meta descriptions, OG/Twitter fields, image alt text, and any SureRank or Spectra-generated schema against the current canonical `inter-model/Thai Solitude Fix Checklist.md` in the local site workspace. That checklist is untracked: calculate and record its SHA-256 at check time and recheck affected entries if the bytes change. Search the rendered page **and view-source**, including `FAQPage` JSON-LD, for forbidden terms, unsupported outcomes, diagnosis/treatment implications, and unverified credentials.
- [ ] Confirm the Oregon LMT license number `LMT-29905` is visible in the rendered footer on every released page and on any standalone promotional surface.
- [ ] Voice is singular and consistent; no “we,” “our,” “us,” “studio,” or wording implying staff who do not exist.
- [ ] Ryan signs the regulated-copy check separately from Butsaba's approval of facts and tone. If Ryan edited the entry, name another independent human reviewer. Neither the editor nor a model self-approves compliance.

## Contact and visitor data

- [ ] Recheck the **rendered live routes and source** on the release candidate using the repeatable command below. A 2026-10-05 public-HTML check of these eight routes found no form/input/textarea elements or SureForms assets; Contact had a direct email link. This does not establish the state of hidden routes or future releases. If no form is exposed, plan separately authorized, backup-first removal of the unused form and its entries. If a live form is exposed and retained, verify remote plugin status, form settings, and notification behavior.
- [ ] For a retained form, turn entry storage **off for new submissions** and verify no new WordPress entry row appears after a non-sensitive test. Document inbox delivery and retention. “No database entry” does not mean “no stored message.”
- [ ] Start an **existing-copy inventory**: local database (one entry dated 2026-06-08, content unclassified), `source-db.sql`, `new-masseuse-db-fixed.sql`, `deploy/10-production-thaisolitude.sql`, production, staging4/staging5, local Restic, SiteGround backups, and notification inboxes. Use counts/dates/metadata only. Ryan or Butsaba privately classifies the entry as test or visitor data. For each copy, record owner, access, retention or purge decision, and completion evidence. For Restic, normal `KEEP=10` aging may be selected: record snapshot groups and backup cadence, estimate the expiry date, then verify expiry. Do not improvise a snapshot rewrite or forget whole snapshots. No data mutation or purge without a backup and explicit authorization.
- [ ] For **future** SiteGround staging and local database copies, document how visitor-entry tables and related metadata are excluded or scrubbed. If the tool clones the full database first, restrict access and scrub before using the copy; verify with counts and non-sensitive metadata, without printing visitor text.
- [ ] Verify Google Site Kit/Analytics behavior on the actual remote page. Confirm form values, health text, URL query parameters, and page paths containing such values do not reach analytics. Do not infer Analytics activation from an installed plugin alone.
- [ ] If a form remains, a non-sensitive staging submission reaches the intended inbox and no staging notification goes to an unintended live recipient. If the contact path uses direct links, verify each address/number and the destination inbox policy with Butsaba.

## Release and use

- [ ] Booking link points to Butsaba's confirmed platform page if booking is active; no FAQ text implies an appointment is confirmed by the site.
- [ ] FAQ and contact path work by keyboard and on mobile; links, focus, headings, and layout remain usable.
- [ ] Staging returns `Disallow: /`; live `robots.txt` still allows crawling and advertises the sitemap after promotion.
- [ ] A usable SiteGround backup for the relevant remote site is confirmed. Review the exact Custom Deploy file/table selections, preserving live-only submissions. Ryan approves the named live promotion.

### Parallel current-site task — not a Phase 0 gate

Review the existing direct-email inbox's provider, recipients, forwarding, access, retention, deletion, notice, and legal questions. Keeping the already published email link does not wait for that review. Complete privacy and legal review before a new free-text channel, form, chat, or link to a different inbox is published; a `mailto:` link can still collect health detail.

### Repeatable public-page check

Run on the release candidate's public host; replace only `base` when checking staging. This counts HTML elements and SureForms asset URLs without printing form entries or message text. Inspect Contact's `mailto:` destination separately with Butsaba. Check the eight content routes—`/`, `/about/`, `/services/`, `/therapeutic-massage/`, `/deep-tissue-massage/`, `/thai-massage/`, `/work-with-me/`, and `/contact/`—plus the form's standalone route, `/form/simple-contact-form/`. Record its HTTP status even when it returns 404; a later 200 response requires inspection before release.

```bash
python3 - <<'PY'
from html.parser import HTMLParser
from urllib.error import HTTPError
from urllib.request import urlopen

base = 'https://thaisolitude.com'
routes = ('/', '/about/', '/services/', '/therapeutic-massage/',
          '/deep-tissue-massage/', '/thai-massage/', '/work-with-me/', '/contact/',
          '/form/simple-contact-form/')

class Scan(HTMLParser):
    def __init__(self):
        super().__init__()
        self.counts = {'form': 0, 'input': 0, 'textarea': 0, 'sureforms_assets': 0, 'mailto': 0}
    def handle_starttag(self, tag, attrs):
        data = dict(attrs)
        if tag in ('form', 'input', 'textarea'):
            self.counts[tag] += 1
        asset_url = (data.get('src', '') + data.get('href', '')).lower()
        if tag in ('script', 'link') and ('sureforms' in asset_url or 'srfm' in asset_url):
            self.counts['sureforms_assets'] += 1
        if tag == 'a' and data.get('href', '').lower().startswith('mailto:'):
            self.counts['mailto'] += 1

for route in routes:
    try:
        response = urlopen(base + route, timeout=20)
    except HTTPError as error:
        print(route, {'http_status': error.code})
        continue
    html = response.read().decode('utf-8', errors='replace')
    scan = Scan()
    scan.feed(html)
    print(route, {'http_status': response.status, **scan.counts})
PY
```
