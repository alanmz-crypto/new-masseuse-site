# Thai Solitude receptionist plan — v7

> **Planning only, revised 2026-10-05.** This document authorizes no site change, data deletion, provider signup, corpus ingest, or live deployment. Ryan approves each release; Butsaba approves practice facts and wording.
> **Current status: discovery; implementation on ice.** Ryan confirms that Butsaba wants a receptionist because she cannot answer clients while giving a massage. This establishes the need for inquiry coverage, not which channels or tasks the receptionist should handle. Resume solution selection only with Butsaba's account of missed inquiries and expected responses, confirmation of private-client status, and Ryan's review of the actual employment agreement. No Phase 0 copy or site work begins before those facts are resolved. Phase 1 chat is **not planned**; its specifications are reference material and reopen only with documented visitor need that the FAQ and existing email cannot meet, plus a new approval of safety and deployment design.
> **Ryan's subjective 2026-10-05 confidence:** need for inquiry coverage 95%; discovery before choosing a channel 95%; FAQ, existing email, and booking link as the solution 45%; starting Phase 0 site work now 15%; starting chat now 0%. These are planning judgments, not measured safety or success rates.
>
> Sources: Ryan's 2026-10-05 clarification and confidence review, the 2026-10-05 read-only public contact-path check, the 2026-10-04 plan and review, the Thai Solitude project instructions, and an earlier shallow read of convmem at `ca0397c`. OpenClaw and convmem implementation details and their current arc states require a fresh check before private-side work.

## 1. Purpose and first release

Help visitors find accurate answers and reach Butsaba while she is occupied with a massage, without implying that an automated system can decide health suitability, policy exceptions, or whether an appointment is confirmed. Discovery must identify how often inquiries arrive by phone, text, email, or the site, what happens today, and whether clients need an immediate answer, an acknowledgment with a callback time, or a way to book without speaking to her. A callback time is offered only if Butsaba approves one she can meet.

**The Phase 0 candidate is a plain FAQ page, a clear contact path, and a link to the scheduling platform's booking page once Butsaba chooses one. It uses no AI.** A FAQ can reduce routine questions, but it does not answer an incoming call or promise an immediate reply. Compare this candidate with the actual mix of missed inquiries before calling it a receptionist solution; it may be a useful site improvement even if callers need different coverage. Confirm that the practice is taking private clients and review the actual employment agreement before deciding whether this contact and booking path fits. If Butsaba has recurring client questions, use them to seed the FAQ; do not assume that 20 such questions exist. A “Didn't find your answer?” prompt offers contact. Butsaba can count unanswered topics manually from her inbox without copying message text into a new system.

Thai Solitude is currently a WordPress site with a local practice stack and a SiteGround staging-to-live path. The project rule keeps remotely deployed site design and behavior in WordPress storage, not remote plugin or theme files. Phase 0 fits the existing page-content workflow. Section 5 preserves the unapproved chat design for reference.

## 2. Decisions

1. **The public path never connects to OpenClaw, convmem, MCP, or A2A.** The approved registry is its only source of practice claims. ConvMem already contains Thai Solitude material from development sessions, including potential drafts and rejected text; it is not a public claim source.
2. **Butsaba owns facts, service descriptions, policies, and contact details.** Her approval is necessary but does not replace the regulated-copy check. An entry needs evidence of her approval and a separately recorded check against the current site checklist before publication.
3. **For later chat, the model selects an action and approved claim IDs. Application code renders the exact approved text.** It does not attempt to prove that a free-form sentence matches a claim. A connective phrase or one clarifying question may be permitted only from a reviewed, fact-free set.
4. **A wrong selection remains possible.** Routing a health suitability question to a service answer is a material failure even if the answer text is approved. Health classification and handoff need specific acceptance cases.
5. **The scheduling platform owns booking and confirmation.** Phase 0 links to its page. Availability display is optional later; a visitor confirms in the platform's own interface. The receptionist never asserts that a booking exists without authoritative platform confirmation.
6. **Handoff is a successful outcome.** Visitors choose whether to send a note. No health conversation transcript is forwarded by default. Private drafting is optional and later.

## 3. Source of truth and content lifecycle

Keep a reviewable registry source for authoring, then publish its approved wording into WordPress page content for Phase 0. The published FAQ must match the approved revision; the release record identifies both revisions. A repository file alone is not the site's live source because WordPress page content is stored in its database.

Each registry entry needs:

```yaml
id: faq-booking-location
class: F # F verified fact; S approved service wording; E cited evidence; H handoff; R refusal
question: "<approved question>"
answer: "<exact approved public wording>"
source: "Butsaba, <date>" # named citation for E
approved_by: Butsaba
approved_date: "<date>"
approval_ref: "<dated email/message or signed review reference>"
compliance_checked:
  checklist_sha256: "<SHA-256 of exact Thai Solitude Fix Checklist bytes checked>"
  reviewer: "<Ryan or named independent human reviewer>"
  checked_date: "<date>"
review_by: "<date>"
```

The authoring registry lives in this site's reviewed source repository under a path chosen before Phase 0, with a named editor who transcribes Butsaba's approved plain-language answers. Butsaba does not need to edit YAML. `approval_ref` identifies the actual dated approval, without putting private messages or health details into the public registry. **Ryan is the default regulated-copy reviewer and cannot approve an entry he edited; if Ryan edits it, name another independent human reviewer before release.** The model cannot approve its own output. The reviewer checks the site's forbidden-term list and whether wording implies diagnosis, treatment, an unsupported outcome, or an unverified credential. Because the canonical checklist is untracked, record its SHA-256 at check time; a changed hash requires rechecking affected entries. An entry lacking either approval record or independent compliance check cannot publish.

Cover location, service model, hours, prices, session lengths, booking link, credentials, and contact details only when confirmed. Do not fill the site's unresolved `[ASK]` items by inference. Health-suitability questions share one approved boundary answer directing the visitor to Butsaba; do not publish individual pregnancy, medication, injury, or contraindication answers as FAQ claims. Before release, check FAQ text, chat connectives, fixed replies, title/meta/OG/Twitter fields, image alt text, and rendered schema/JSON-LD (including any Spectra `FAQPage` output and SureRank fields). Check the Oregon LMT license number in the rendered footer on every page and in any standalone promotional surface. A changed fact triggers a registry revision and a new page release.

`review_by` is an operational cutoff. An expired entry is unavailable to chat selection. The editor receives a review task before that date; if an FAQ entry expires without reapproval, remove or replace the published answer rather than leaving it live indefinitely. The Phase 0 checklist verifies this. Allowed chat combinations are explicit: one answer entry, optionally one approved connective, optionally the approved booking or contact link. Combining two service claims or a service answer with a health handoff is invalid unless a reviewed compatibility table names that pair.

Use the existing local backup → localhost review → staging review → SiteGround Custom Deploy workflow. Before remote editing or promotion, confirm a usable remote backup. Review the selected posts and options, preserving live-only submissions and data. Live promotion is a separate Ryan decision.

## 4. Phase 0: FAQ and handoff

- Record how often inquiries reach Butsaba while she is in a session (phone, text, email, or site), how they are handled today, and whether callers need an immediate answer, an acknowledgment with a callback time, or self-service booking. Confirm that the practice is taking private clients and review the actual employment agreement for this client-facing FAQ and booking path. Ask Butsaba for questions she actually receives; group duplicates without inventing answers.
- Draft answers from Butsaba-confirmed facts. Each public answer points to its registry ID in the authoring source.
- Publish a navigable FAQ with contact options and a “Didn't find your answer?” prompt. Health-suitability questions use the single boundary answer and contact path. Do not require visitors to describe a health concern.
- Add a direct booking-page link when the scheduling platform and public URL are confirmed. Do not build availability or booking APIs in this phase.
- Make clear that Butsaba personally handles suitability, exceptions, complaints, and special arrangements.
- Complete the separate [Phase 0 release checklist](thai-solitude-phase0-checklist.md), including copy, metadata/schema, contact-data flow, mobile layout, and the staging/live crawling split.

### Discovery test before choosing a release

Once private-client inquiries are actually arriving, count missed inquiries for roughly two working weeks by channel (call, text, email, site) and needed outcome (immediate answer, callback acknowledgment, self-service booking, or other). Record only counts and whether a response was possible after the session; do not copy names, message text, or health details into the planning record. If the practice is not yet accepting clients, use Butsaba's recollection to draft scenarios but label the frequency unknown. A zero count before opening is not evidence that no receptionist is needed.

Walk through representative, non-identifying scenarios with Butsaba: a routine fact question, a caller who needs an immediate answer, a caller willing to wait for a callback, a person who wants to book without speaking, and a suitability question that needs her personal judgment. For each candidate, check whether the visitor can reach an accurate next step without interrupting a massage. A FAQ is an adjunct if calls still need live handling. A callback path needs a response window Butsaba can meet. Self-service booking needs an approved, working platform link; neither the site nor a receptionist invents confirmation. If the common task is an immediate answer, evaluate live human coverage as a separate candidate rather than relabeling a FAQ as coverage.

Keep three operational candidates distinct until the inquiry counts identify the primary task:

- **Acknowledgment and callback:** a caller can leave a message and learn when Butsaba expects to respond. Test the actual phone route, message delivery, and her ability to meet the promised window. This fails as the main solution if callers commonly need an immediate answer.
- **Self-service booking:** a caller or visitor can reach the chosen scheduler and complete a representative booking without speaking to Butsaba. Test the real link, service choices, confirmation, and cancellation in the platform. This is unavailable until a platform and public URL are approved.
- **Live answering:** a named human covers the periods when clients expect an immediate response. Test coverage hours, permitted answers, handoff boundaries, and cost against observed call volume before choosing a provider. It is unnecessary if delayed acknowledgment or self-booking resolves the common task.

An approved FAQ may complement any candidate for routine facts. It does not substitute for the candidate's phone or booking test.

**Read-only desk test, 2026-10-05:** The eight inspected routes on both [live Thai Solitude](https://thaisolitude.com/) and [staging12](https://staging12.thaisolitude.com/) returned pages without visible forms or booking-link candidates. [Live Contact](https://thaisolitude.com/contact/) exposed `tel:` and `mailto:` links; [staging12 Contact](https://staging12.thaisolitude.com/contact/) exposed an `sms:` link and no visible email link, although Work With Me exposed `mailto:` on both hosts. The home buttons led to Contact. This HTML check does not establish voicemail, actual call routing, inbox behavior, hidden routes, or whether a caller receives an acknowledgment. The current site therefore demonstrates contact paths, not receptionist coverage or self-service booking.

**Decision rule:** Prefer the least complex path that handles the observed common tasks and has an approved owner, response policy, and data flow. Do not start Phase 0 site work on the strength of the need alone. The present desk test does not validate any of the three operational candidates: callback behavior has not been checked, no booking link is exposed, and no live-answering coverage is identified. If private-client permission, the employment review, or usable inquiry evidence remains unavailable, put implementation on ice and retain this discovery plan. Do not treat an untested FAQ as completion of the receptionist objective.

**Phase 0 is done** when the practice/client path is confirmed, the candidate is shown to handle the observed common tasks it claims to handle, Butsaba approves the questions and every published answer, the compliance check is recorded, the Phase 0 checklist passes on staging, and Ryan approves the exact stage-to-live operation.

## 5. Reference only: public chat is not planned

Do not start chat implementation, provider selection, or a chat release from this plan. Reopening requires evidence of a visitor task the FAQ and existing email cannot serve, followed by a new decision on routing safety and a deployable runtime under the site rule. The material below records prior design work and is not an active phase.

### Trigger and answer boundary

Propose chat only after reviewing recurring unanswered topics counted manually from Butsaba's inbox. Phase 0 does not add a feedback widget, script, or storage. A concrete case for chat must explain which visitor task the FAQ and contact path cannot serve well.

The model output is structured, for example `{action, claim_ids, connective_id, clarification_id, fixed_response_id}`. **Handoff is the default for ordinary uncertainty.** The model may select a reviewed fixed-response ID for crisis or immediate danger but never write its text. Routing precedence is **crisis fixed response → other fixed safety response → health handoff → ordinary handoff → approved answer**. A crisis route overrides any answer IDs and never points the visitor to Butsaba's inbox as the primary response. An answer is rendered only after a positive match to an allowed answer action and currently approved IDs. The server rejects unknown or expired IDs, incompatible combinations, and actions outside a closed enum. The renderer composes only approved answer text and reviewed fact-free connective text. The model does not write registry entries or invent links, credentials, discounts, suitability, or booking state.

Before a model call, a deterministic pre-filter checks reviewed terms and patterns for pregnancy, medication, injury, surgery, crisis, and related health context, including common misspellings and Thai wording. A match forces handoff or a fixed response. This filter is a floor, not complete language understanding: **when it misses crisis wording, the model may still choose the crisis action and fixed-response ID; code renders only the reviewed fixed text.** If the model marks safety ambiguity, use a reviewed fixed safety response rather than an ordinary inbox handoff. Neither model nor pre-filter is assumed complete; the crisis paraphrase cases are release blockers.

If a clarifying turn is retained, keep only an opaque first-party session key, the pending approved question/claim ID, and a sticky `health_context` flag in server-side state for at most 30 minutes after the last turn. The key needs a first-party `Secure`, `HttpOnly`, `SameSite` cookie and a disclosed retention/expiry rule. Do not persist raw prior messages or send them to the provider. Once any turn sets `health_context`, later turns in that session hand off; a benign follow-up cannot clear it. If this state cannot be implemented within the approved runtime and retention decision, use single-turn chat only.

Keep the connective set small: greetings, transitions, and one neutral clarifying question. No open free-text slot. This may sound less fluid than free-form generation; review the actual visitor experience before widening it. A wrong approved claim still needs the misrouting cases in the [acceptance suite](thai-solitude-receptionist-acceptance.md).

### Deployment decision

The current project rule prohibits delivery through remote `wp-content/mu-plugins/` or other plugin or theme files; SiteGround promotion must carry site changes through WordPress storage. A custom WordPress endpoint implemented as a new plugin therefore **does not currently fit the release rule**, even though it is technically possible. A key in a WordPress option is not a substitute for server-side secret storage.

Before chat implementation, make a narrow architecture decision identifying runtime, secret storage, browser-to-endpoint path, abuse controls, version pairing with the WordPress page, staging rehearsal, feature flag, and rollback. If an Insert Headers and Footers option or other promoted WordPress option holds an endpoint URL or flag, guard against staging calling production and live calling staging: validate the expected host and environment on both sides, test each after promotion, and include URL/flag rollback. A separately hosted function is an option only if its deployment path is justified and a joint release and rollback are designed. Changing the no-plugin rule is a separate Ryan-approved governance decision. No endpoint is assumed for Phase 0.

### Booking and safety

Initially, chat may provide only the approved booking-page link. Consider free-slot display only if the chosen platform exposes public availability for named services without attendee or calendar details. The platform handles confirmation, cancellation, and rescheduling. No Google Calendar OAuth or direct booking write is in the model's tool set.

Health suitability, pregnancy or medication, injury recovery, diagnosis or outcome requests, exceptions, negotiation, complaints, requests for Butsaba, and unknown topics route to handoff. Emergency, crisis, threat, and sexual-solicitation cases use reviewed fixed responses outside the model. Visitor-facing UI discloses that the assistant is automated, uses a non-staff name, and does not imply a team. The [handoff behavior spec](thai-solitude-handoff-behavior.md) defines these routes.

### Acceptance

Before staging activation, run the [Phase 1 acceptance suite](thai-solitude-receptionist-acceptance.md) against the actual renderer, router, and provider path. Repeat each health and crisis case across multiple runs, paraphrases, misspellings, and Thai inputs. All safety and privacy cases must pass; any misrouted health suitability case blocks activation. Verify exact approved wording, expiry fallback, regulated copy in every rendered surface, fixed response routing, booking ownership, rate limits, and provider retention settings. Repeat relevant cases after any model, prompt, registry, or endpoint change.

## 6. Data and provider decisions

The governing promise is **minimization**, not that health details can never occur. Visitors can type them into an open field. Provider processing, handoff delivery, analytics, and local logs must be described before those fields are enabled.

| Path | Data allowed | Decision before activation |
|---|---|---|
| Phase 0 FAQ and ordinary analytics | Page views and aggregate FAQ use; no visitor health text | Confirm fields, retention, and whether contact or booking clicks are tracked |
| Phase 0 contact path | Retain the existing direct email/phone links without adding a new collection channel; any existing form can receive visitor-supplied health detail | Verify the published links and whether a form is actually exposed. Review the existing inbox's retention and legal questions in parallel; require a completed data-flow and legal review before any new free-text channel or different inbox |
| Later hosted chat model | Current visitor message, minimal approved registry context, and coarse sticky health flag if needed; no raw prior turns | Name provider, retention, training use, abuse-monitoring logs, and deletion terms; decide whether visitor text may be sent |
| Later receptionist runtime | Minimal request state, rate-limit keys, coarse failure counts | Specify retention, IP treatment, error-log redaction, and budget cap; no transcript logging by default |
| Optional multi-turn session cookie | Random first-party key only; server state holds pending ID and sticky health flag | Decide cookie notice, `Secure`/`HttpOnly`/`SameSite` attributes, 30-minute expiry, and deletion behavior |
| Scheduling platform | Data visitor supplies in the platform UI | Confirm booking URL, availability scope if used, privacy terms, and confirmation behavior |
| Optional B2 private drafter | Only text the visitor chose to share and approved registry export | Decide local versus external provider, access and retention; Butsaba reviews and sends every reply |

Avoid storing health-specific handoff reason enums with a session identifier. Use a coarse “needs personal judgment” category for operational counts, or aggregate counts without a session identifier. The handoff can explain its boundary without persisting a diagnosis-like label. Consent text must distinguish opening a contact channel from sending a note or transcript.

The local practice copy has SureForms installed but inactive, one published form definition, and no local page embedding that form according to the second read-only review. Google Site Kit is active locally; neither finding establishes production behavior. A **2026-10-05 read-only check of public HTML** for Home, About, Services, the three service pages, Work With Me, and Contact found zero form/input/textarea elements and zero SureForms asset references; Contact offers a direct email link. This is evidence for those eight routes at that time, not proof about hidden routes, later changes, or stored entries. Recheck the release candidate. If no form is exposed, plan removal of the unused form and its entries through a separately authorized, backup-first cleanup; there is no new-form storage or notification workflow to implement for Phase 0. If a form is exposed and retained, choose **entry storage off for new submissions**, verify the actual remote setting and delivery, and document inbox retention. The installed SureForms code ordinarily stores entries in a WordPress table and has a form-level no-storage path when compliance options are enabled. Disabling new entries does not remove historical entries or email copies.

**Existing-copy inventory comes first.** The second read-only review found one SureForms entry dated 2026-06-08 in the local database and its table in `source-db.sql`, `new-masseuse-db-fixed.sql`, and `deploy/10-production-thaisolitude.sql`; the entry text was not read and its test/visitor status is unknown. Inventory production, the local database, these three dumps, staging4/staging5, local Restic snapshots, SiteGround backups, and any notification inbox using counts, dates, and metadata only. Ryan or Butsaba classifies the entry as test or real visitor data through an authorized private review. For **each** existing copy, record owner, access, classification, retention or purge decision, and completion evidence. For local Restic snapshots, normal `KEEP=10` aging is an available retention decision: record the snapshot groups and backup cadence, estimate when the affected snapshots will leave retention, and verify their eventual expiry. Do not rewrite snapshots or forget whole snapshots ad hoc, because that can weaken restore coverage. No purge or DB mutation occurs without its required backup and explicit authorization; retention of a real visitor entry must have a stated purpose and period. Future SiteGround and local copies must exclude or scrub visitor data where possible; if cloning creates a full copy first, restrict access and scrub before use. Verify without printing visitor text. Check that Google Analytics/Site Kit does not receive form values, health text, URL query parameters, or page paths containing such values. Do not infer Analytics activation from a locally active plugin.

Direct email and phone links avoid a **new WordPress form**, not collection of health detail. A visitor may email health information to Butsaba's inbox. Review the existing inbox's recipient, access, retention, deletion, and any forwarding or provider processing as a parallel current-site task; retaining the already published email link does not block the AI-free FAQ release. No **new** free-text channel, different inbox, or changed contact-data flow is published until its privacy and legal review is complete.

## 7. Private assistance: optional Track B

**B1 offline curation:** OpenClaw or convmem may help propose registry revisions from Butsaba-approved source material and audit public claims. Proposals go through human review. No live corpus result becomes a public claim. If a repository-knowledge manifest is used, verify that the closed-inventory mechanism supports this repository and exclude drafts, secrets, live data, and visitor messages. B1 can support Phase 0 drafting if separately authorized.

**B2 handoff drafting:** Consider only when observed handoff volume warrants it. It has no tools, browser, mail send, calendar access, durable memory, or live convmem access. It receives approved registry text and only the visitor-selected handoff note; Butsaba edits and sends. Health-flagged conversations default to no transcript. Its provider and retention decision is separate from the public chat decision.

Track B belongs to a separately authorized convmem/OpenClaw slice if it modifies that environment. Do not change the ConvMem Switchboard or OpenClaw Watch Coverage arcs as part of this site plan. Recheck their status at the time of B work; the 2026-10-04 snapshot is not a durable dependency.

## 8. Phase gates

| Phase | Start condition | Done when |
|---|---|---|
| 0 — FAQ and handoff | Inbound channels and missed-contact tasks are identified; Butsaba confirms the practice accepts private clients; Ryan reviews the actual employment agreement and identifies the site release | Approved FAQ, independent compliance record, existing-copy inventory and disposition, existing contact links verified, and Phase 0 checklist pass staging review; Ryan approves exact live promotion. Existing inbox privacy/legal review runs in parallel unless the release changes the contact-data flow |
| 1 — bounded chat, not planned | No start under this plan; a documented unmet visitor need and new safety/deployment approval would be required to reopen | Reference suite has no current release role |
| 2 — availability display, optional | Chosen scheduler has a scoped availability API and a demonstrated visitor need | Free slots are correct; booking and confirmation remain in the platform |
| B1 — private curation, optional | Separate Ryan authorization for the private environment | Human-reviewed registry proposals improve the FAQ without corpus-to-public automation |
| B2 — private drafts, optional | Handoff volume justifies it; provider and retention decision made | Butsaba reviews every send and finds drafts useful; privacy and injection cases pass |

## 9. Decisions still needed

1. How often inquiries arrive during massage sessions, through which channels, how they are handled now, and whether clients expect an immediate answer, an acknowledgment with a realistic callback time, or self-service booking. Whether the practice is taking private clients and whether the actual employment agreement permits solicitation also remain open; gather Butsaba's actual recurring questions, service model, location, hours, lengths, prices, credentials, contact channels, and approval cadence. Existing site `[ASK]` items remain unresolved until she answers them.
2. Whether client booking is active now, which scheduling platform owns it, and the approved booking URL.
3. The exact Phase 0 WordPress pages, live form presence, existing-copy inventory and disposition, existing contact links, future staging/local copy procedure, posts/options to promote, with a usable remote backup. Review the existing inbox's retention in parallel.
4. If chat is warranted: runtime and release design under the no-plugin rule; provider, retention and training settings; visitor consent; budget and rate limits.
5. Whether optional B1/B2 work is worth a separate private-side arc and who owns it.
6. Review the existing direct-email inbox and its retention as a parallel current-site item. Keeping its already published link is not a Phase 0 legal-review gate. Before publishing **any new free-text channel, direct email link to a different inbox, form, or chat**, assess whether Washington's [My Health My Data Act](https://app.leg.wa.gov/rcw/default.aspx?cite=19.373) applies to this practice and these visitors, including collection, sharing, consent, privacy notice, and processor duties. Review any applicable automated-assistant disclosure requirements before Phase 1. This plan does not decide legal applicability.

## 10. Hard stops

- No public connection to OpenClaw, convmem, MCP, or A2A; no automatic public-to-registry or public-to-corpus write.
- No claim from development sessions, corpus search, model memory, or unapproved site copy. Registry wording is eligible only while both Butsaba's approval and the regulated-copy check are current.
- No health suitability determination or booking confirmation invented by a model.
- No visitor text to a provider or handoff channel before its data flow and consent are approved.
- No live deployment without the exact SiteGround operation and remote backup reviewed under the project instructions.
- No remote plugin or theme file deployment under the current site rule. Any exception requires a separate governance decision.
