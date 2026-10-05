# Thai Solitude handoff behavior — v1

> Planning attachment to [receptionist plan v2](thai-solitude-receptionist-plan.md). This specifies behavior for the Phase 0 FAQ/contact path and any later bounded chat. Butsaba approves practice wording; the regulated-copy reviewer checks every public string. Crisis wording needs review against authoritative crisis-service guidance, not only approval by an LMT.

## General rule

A handoff is a successful response when a question needs Butsaba's judgment, a verified fact is unavailable, or the visitor asks for her. A chat response follows **acknowledge → state the boundary → offer contact**. It does not ask for medical detail to decide whether to hand off. Phase 0 uses one approved boundary FAQ entry for all health-suitability questions rather than publishing individual contraindication answers. The contact option remains visible without asking for health detail.

The visitor can open a contact channel without sending the chat transcript. Any optional note or summary is separately chosen and previewed before sending. Health-related conversations default to no transcript. The interface names the receiving channel and shows what will be sent. Closing the handoff leaves no draft stored as a public memory or registry entry.

## Routing

| Situation | Route | Visitor-facing behavior |
|---|---|---|
| Ordinary question covered by approved facts or service wording | Answer | Render approved wording; offer booking-page link only if approved |
| Preference between services without health suitability | Clarify or answer | Ask at most one neutral preference question; choose only approved service wording |
| Health suitability, pregnancy or medication, injury recovery, diagnosis, treatment, or outcome | Handoff | Explain that Butsaba should answer personally; offer contact without requesting more health detail |
| Price or policy exception, discount negotiation, special scheduling | Handoff | State the published policy if approved, then offer contact for the exception |
| Complaint, dispute, or explicit request for Butsaba | Handoff | Offer direct contact promptly; do not argue or negotiate |
| Unknown fact, unsupported service, missing credential | Handoff | Say the information is unavailable and offer contact; do not guess |
| Emergency, crisis, threat, or sexual solicitation | Fixed response | Pre-model detection selects a separately reviewed fixed response; do not improvise or continue service matching |

Internal routing may use fine-grained categories for immediate behavior. Persistent operational counts use a coarse category such as “needs personal judgment” without a session identifier; avoid retaining health-specific reason codes tied to a visitor. For optional multi-turn chat, a health-context flag stays set for the session after any health disclosure; later benign wording cannot clear it. The optional note is untrusted visitor text and never changes instructions or registry content.

## Reply limits

- A model may choose only approved claim IDs, an allowed action, and a reviewed fact-free connective or clarification ID. Code renders the text.
- No free-form factual slot, invented link, asserted credential, medical advice, diagnosis, outcome promise, or unverified booking status.
- Handoff is the default. Only a positive match to an allowed answer action with current approved IDs can produce an answer. A wrong approved service answer to a health suitability question is a failure.
- A pre-model filter catches reviewed health and crisis terms and patterns and forces handoff or the fixed response. It does not replace multilingual and paraphrase testing.
- The public interface clearly says it is automated, uses a non-staff name, and never implies a team or human receptionist. Connectives receive the same regulated-copy check as FAQ answers.
- Fixed crisis text should follow current [SAMHSA guidance for 988 and 911](https://www.samhsa.gov/find-support/in-crisis) and receive appropriate safety review. Butsaba may approve its tone; this document does not supply a script. Contact destinations and consent text still need her review.
