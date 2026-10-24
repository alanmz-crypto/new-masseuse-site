# Thai Solitude: longer privacy policy draft, saved for LATER REVIEW

**Saved:** 2026-10-17, at Ryan's request ("we will have to review these things, later, you don't need them now. Save them to a file you can return to").
**Status:** the pasted text below is NOT used as written. On 2026-10-17 Ryan asked for it to become the second section of the page ("Client privacy", next to "Online privacy", as a slide-open accordion like the About page). A cut-down version (only what the site itself says or what is plainly true, per Ryan: "include what we can ... while being completely true") has been LIVE since 2026-10-17 on Ryan's "go live" (and on staging31 and localhost). Left out until Butsaba confirms: payment details and processor, how records are kept and protected, retention, service providers, promotional messages and unsubscribing, appointment history, mailing address. Source: `scripts/work-with-me-employer/privacy/build-privacy-page.py`. To add any left-out item back, Butsaba must confirm it first.

## What is live (or about to be) instead

A deliberately tiny page, "keep it very very simple" (Ryan, 2026-10-17): `/privacy-policy/` with five short sections, plus a "Privacy Policy" link in the footer's centered row. Source: `scripts/work-with-me-employer/privacy/` (`privacy-policy.html`, `install-privacy-page.php`, `push-privacy-page.sh`). On staging31 since 2026-10-17; live only on Ryan's go. That page says the site has no forms, no accounts, no ads, no tracking tools and sets no cookies for visitors; that was checked against the live site on 2026-10-17 (6 pages: 0 forms, 0 iframes, no Set-Cookie, only Google Fonts as an outside load).

## Things to settle when this is reviewed

- **Is the longer text needed at all?** It describes booking, payments, health histories and session notes. The site today has none of those (contact is an email link and a phone link). Add sections only for things that really happen, on the site or in the practice, and keep the page true: if the site later gains a form, booking widget (the old cal.com snippets are drafts, inactive), analytics or a map embed, the page must be updated first.
- **Health and session information:** the template covers it. Whether Butsaba collects intake or session notes outside the website, and how they are kept, is a question for her. The live page does not mention it.
- **HIPAA:** do not claim "HIPAA compliant" unless it has been confirmed that HIPAA applies and the systems meet it (the template's own warning, below).
- **Site voice and the OBMT advertising rules:** the site must not use we/our/us or the other forbidden terms (list in `AGENTS.md`); the template is first person ("I"), which matches the footer disclaimer. Any added wording needs a check against that list, and Ryan sees the exact wording before it goes anywhere.
- **Facts to fill in:** retention period, payment provider (if any), real safeguards, what email provider is used, business mailing address (the site only says "Portland, OR"; location and the (312) phone number are open `[ASK]` items in the Fix Checklist).
- **Professional check:** the template itself recommends a local professional or licensing resource review the final version, especially if health histories or third-party booking or record systems are used.

---

## The pasted text (verbatim)

## TL;DR

A one-person massage practice’s privacy page should explain, in plain language, **what information you collect, why you collect it, who can access it, how you protect and retain it, and how clients can ask about it**.

Because you’re an LMT, address health and session information carefully. In the U.S., don’t claim that your practice is “HIPAA compliant” unless you’ve confirmed HIPAA applies to your practice and your systems meet its requirements. HIPAA does not automatically apply to every massage therapist. State privacy and recordkeeping rules may still apply.

## Suggested page

# Privacy Policy

**Last updated: [date]**

[Practice name] is a massage therapy practice operated by [your name], a Licensed Massage Therapist in [state]. This page explains how I collect, use, store, and share information when you visit my website, contact me, book an appointment, or receive services.

### Information I collect

Depending on how you interact with me, I may collect:

- **Contact and booking details**, such as your name, phone number, email address, appointment history, and preferred appointment times.
- **Payment and transaction details**, such as the amount and date of a payment. Payments are processed by [payment provider]; I do not store full payment card numbers.
- **Health and session information** you choose to share, such as health history, areas of concern, treatment preferences, and notes relevant to providing massage therapy.
- **Website information**, such as basic device or usage information collected through cookies or analytics, if those tools are enabled.

Please share only information that is relevant to your care. You can ask me how a particular piece of information will be used before providing it.

### How I use information

I use information to:

- Schedule, confirm, and manage appointments.
- Provide and document massage therapy services.
- Communicate with you about your appointments or requests.
- Process payments and maintain business and professional records.
- Meet applicable legal, licensing, insurance, and recordkeeping requirements.
- Maintain and improve my website and services.

I do not sell your personal information or use your health information for advertising.

### When I share information

I do not share your information except as needed to run my practice or as permitted or required by law. For example, information may be handled by service providers that help with scheduling, payment processing, email, website hosting, or record storage. I choose providers that are appropriate for the information they handle and limit access to what is needed.

I may also disclose information when required by law, to respond to a lawful request, or to protect the rights and safety of clients or others.

### How I protect and keep information

I use reasonable safeguards to protect practice records, including [briefly describe actual measures, such as password-protected accounts, device security, and limiting access]. No website, email, or electronic storage system can be guaranteed completely secure.

I keep records for as long as needed for professional care, business purposes, and applicable legal or licensing requirements. When records are no longer needed, I dispose of them securely where practical.

### Your choices and requests

You may contact me to ask what information I maintain about you, request a correction, or ask a question about how it is used. Some records may need to be retained under applicable requirements, so I may not be able to delete every record on request.

You can unsubscribe from nonessential promotional messages using the link in the message or by contacting me. Appointment and service-related messages may still be sent when needed.

### Website links and cookies

My website may link to other websites that have their own privacy practices. If I use cookies or analytics, I will describe the tools here: [list tools and link to their privacy information, or remove this paragraph if none are used].

### Contact

For privacy questions or requests, contact:

**[Your name / Practice name]**  
[Email address]  
[Business mailing address or other appropriate contact method]  
[Phone number, optional]

---

Before publishing, fill in the brackets and check that the page matches your actual booking, payment, email, website, and recordkeeping systems. Because LMT privacy and recordkeeping rules depend on where you practice, have a local professional or licensing resource check the final version—especially if you collect health histories or use third-party booking or record systems.

<!-- ref: ../../scripts/work-with-me-employer/privacy/privacy-policy.html — the short page that is used instead of this draft -->
<!-- ref: ../../scripts/work-with-me-employer/privacy/install-privacy-page.php — how the short page and footer link are installed -->
