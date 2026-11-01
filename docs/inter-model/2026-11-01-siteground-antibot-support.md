# SiteGround Anti-Bot challenge — owner support request

Ryan asked for help removing the robot block on 2026-11-01. The target is
`staging38.thaisolitude.com`. No production or hosting-control change has been
made. GitHub access works; no authenticated SiteGround admin/support session or
connected Desktop Commander device is available to this cloud session.

## Phone steps

1. Sign in to the SiteGround Client Area.
2. Tap **? → View Help Center → Contact Us**.
3. Choose the category/topic closest to website security or access problems;
   follow the offered chat/ticket route and paste the message below.
4. Bring the reply back to this session. Confirm the scope before any change
   that would also affect `thaisolitude.com` or the whole account.

## Message to paste

Please investigate false-positive Anti-Bot AI / SG-Captcha challenges on
staging38.thaisolitude.com. Authorized read-only Playwright checks from GitHub
Actions received your Robot Challenge page instead of the site at
https://staging38.thaisolitude.com/ on November 1, 2026, approximately
09:50–09:52 UTC. These checks use GET requests and do not attempt logins.

Please provide a supported staging38-only CAPTCHA exception for this testing,
or another durable way to allow it. GitHub-hosted runner IP addresses change,
so unblocking one runner IP would not prevent recurrence. Please retain the
other security controls. If this requires changing protection for
thaisolitude.com or the whole account, explain the scope before making it.

## Evidence

- [Full browser check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41294845199):
  INVALID at the first Home page, zero assertions; challenge was detected and
  the checker stopped without further page requests.
- [Later Contact CSP check](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41294973273):
  PASS, November 1 at 09:52–09:53 UTC; desktop/phone in Chromium/Firefox,
  four page views, zero policy violations. The failure is intermittent.
- [Staging apply and origin-side checks](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41294478300):
  all 12 pages answer 200; rendered gates/posture pass; no new site PHP errors.
- Challenge signature documented in this repository: HTTP202 and
  `sg-captcha: challenge`. A challenge is INVALID evidence, never a site pass.

## What is verified about the remedy

This is the hosting Anti-Bot AI layer, distinct from the WordPress Security
Optimizer plugin. There is no verified self-service CAPTCHA toggle or supported
write API in this repository. Do not guess a `site-tools-client` mutation or
disable the WordPress security plugin to try to fix this host response.

Official SiteGround sources:

- [CAPTCHA explanation](https://www.siteground.com/kb/seeing-captcha-website/):
  the hosting Antibot AI issues the challenge and can whitelist the source after
  a successful human challenge.
- [Anti-Bot system](https://www.siteground.com/blog/new-anti-bot-ai/):
  persistent CAPTCHA problems should be referred to support. Current per-host
  exemption availability must be confirmed with them.
- [Contact support](https://www.siteground.com/tutorials/getting-started/getting-help-from-our-support-team/):
  the authenticated Client Area route and available contact channels.

No support message has been sent by the agent. After SiteGround confirms the
change, make one controlled verification attempt. Continue to stop at the first
challenge; do not spoof identities, copy challenge cookies, or retry in a loop.
