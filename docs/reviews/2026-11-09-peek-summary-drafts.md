# Peek: approved summaries for three more service pages

**Ryan approved all three wording sets in chat on 2026-11-09**, after they were
reproduced inline for review. His reply began "Approved:" and quoted the review's
introductory line. The exact item arrays and coverage maps below are unchanged;
only approval metadata moved from `draft` to `approved`. Deployment still requires
each page's staging39-source rehearsal and rendered-card review. Follow the
[rollout record](2026-11-09-peek-service-rollout.md) for actual deployment status.
Thai's already approved summaries are outside this copy change.

## Evidence and limits

The fresh staging39 capture
[`peek-design-s39-20261109-1`](https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42548017902)
finished **2026-11-09 01:55:57 UTC**, using source commit
`72413de4dab31c4a1a6c1b5036bb374b19d8a749` on
`claude/wizardly-bell-xu0rta`. The capture succeeded and reported no page errors
for these three pages at 390 px. This review read each complete rendered section
dump, including FAQ answers and research. The captured text confirms their
current section IDs and order; it is more current evidence than the earlier
source-only proposals.

This is a content review of that rendered evidence, not a claim that these draft
cards have been tested in a browser. The capture does not establish Safari,
VoiceOver, TalkBack or physical-device behavior. New summaries still need visual
review in the cards after wording approval, including the longer research items.
The historical Thai 485/0 report is not testing of these drafts.

The builder sources used for comparison are
[`build-therapeutic-evidence.py`](../../scripts/work-with-me-employer/therapeutic/build-therapeutic-evidence.py),
[`build-deep-tissue-evidence.py`](../../scripts/work-with-me-employer/deep-tissue/build-deep-tissue-evidence.py)
and [`build-chair-massage.py`](../../scripts/work-with-me-employer/chair-massage/build-chair-massage.py).
Current FAQ/appointment prose also includes the later approved changes in
[`review-d/changes.json`](../../scripts/work-with-me-employer/review-d/changes.json)
and booking links in
[`review-a/changes.json`](../../scripts/work-with-me-employer/review-a/changes.json).
The proposals summarize the site's existing evidence account; this pass did not
independently re-review every cited research paper.

## Editorial choices

The proposals separate the service definition, reasons for choosing it, appointment
process and practical questions. They omit repeated details instead of filling
every box to the same length. Therapeutic's intake, goals and continuing feedback
become one appointment item. Deep Tissue keeps **“Deep pressure optional”** because
it corrects a central misconception about that service. Chair's vague “Host
prepares space” becomes the practical **“Host arranges space, signups.”**

Each item below maps to `[text, anchor]` in the page-data file. The anchor names
identify the existing `.ts-jump` destinations; this document does not prescribe
another navigation order. `partial` means the list intentionally covers roughly
60% or less of the section's ideas and ends with `etc.`. `most` is an editorial
judgment about idea coverage, not a calculated percentage of words. Neither lint
nor a phrase count can prove accuracy, distinctness or coverage.

## Therapeutic Massage — approved

| Section / anchor | Proposed items, in order | Coverage |
|---|---|---|
| What it is / `ts-what-it-is` | Focused work, relaxation<br>etc. | partial |
| Common reasons / `ts-common-reasons` | Everyday tension, stiffness<br>Neck, shoulder discomfort<br>etc. | partial |
| Who can benefit / `ts-who-can-benefit` | Health concerns considered<br>Within massage's scope<br>Doctor first when needed | most |
| Questions / `ts-guide-questions` | No complaint needed<br>Results vary<br>No fixed schedule<br>Corporate/group bookings only<br>No individual home visits<br>etc. | partial |
| What to expect / `ts-what-to-expect` | Goals, feedback guide work<br>Surface to deeper work<br>Discuss future sessions<br>etc. | partial |
| What Research Says / `ts-guide-research` | Mixed evidence, often short follow-up<br>Reviews reported less pain; certainty was limited<br>Neck versus placebo: probably little difference, low certainty<br>Sports flexibility and soreness gains were small<br>Sports performance gains remain unclear<br>Some relaxation measures improved after rest too<br>Cortisol changes were mostly indistinguishable from zero<br>Rare serious massage harms reported<br>etc. | partial |

The one-idea definition box is deliberate: the remaining introductory paragraphs
preview research, and duplicating research findings here would add overlap and
strip their qualifications. “Goals, feedback guide work” combines planning and
ongoing adjustments; a second intake/check-in/plan item would repeat it.

The research items retain the mixed results, short follow-up and limited certainty
of the reviews. The neck-placebo item specifically preserves the page's
**low-certainty** qualification (Cochrane review, page source [10]). The sports
items distinguish the small flexibility/soreness findings from unclear performance
gains ([3], [23]); the rest/cortisol items preserve the less favorable comparisons
([4], [28]). Rare serious harms come from the general massage safety account
([9], [13], [14]), not a claimed measured rate for this service.

## Deep Tissue Massage — approved

| Section / anchor | Proposed items, in order | Coverage |
|---|---|---|
| What it is / `ts-what-it-is` | Slow, deliberate pressure<br>Deep pressure optional<br>etc. | partial |
| Common reasons / `ts-common-reasons` | Persistent muscle tightness<br>Work, exercise, routines | most |
| What to expect / `ts-what-to-expect` | Share health history<br>Warning signs: see doctor<br>Focus agreed first<br>Technique follows feedback<br>Discuss future sessions | most |
| Questions / `ts-guide-questions` | Therapeutic massage compared<br>Corporate/group bookings only<br>No individual home visits<br>etc. | partial |
| What Research Says / `ts-guide-research` | Definitions differ between studies<br>Trials reported short-term pain gains<br>Neck trial: pain advantage only short term<br>Same trial: self-rated recovery advantage through one year<br>Neck-massage review: probably little difference from placebo, low certainty<br>Sports performance findings were mixed<br>Rest also improved relaxation<br>Rare serious harms reported<br>Case reports cannot show frequency<br>etc. | partial |

The neck trial's pain and self-rated recovery outcomes must remain separate:
the pain advantage was short term, while the self-rated recovery advantage was
reported at every follow-up through one year (page source [2]). “Benefits faded”
would misrepresent the trial. The low-certainty neck-placebo review ([19]) concerns
massage more broadly; calling it a deep-tissue-only review would also be wrong.
The method-definition problem ([3]) and the limits of case reports ([8]–[11]) are
independent qualifications. The brief summary does not reproduce the complete
cost analysis, adverse-event examples or sports-study comparisons, so it ends
with `etc.`.

## Chair Massage — approved

| Section / anchor | Proposed items, in order | Coverage |
|---|---|---|
| What it is / `ts-what-it-is` | Seated, clothed massage<br>Hands-on, without oil<br>etc. | partial |
| Common reasons / `ts-common-reasons` | Workday muscle tightness<br>A brief pause<br>Employee appreciation events<br>etc. | partial |
| Who can benefit / `ts-who-can-benefit` | First-timers welcome<br>Ordinary chair alternative<br>etc. | partial |
| Questions / `ts-guide-questions` | Table sessions last longer<br>Results vary<br>Host arranges space, signups<br>Corporate/group bookings only<br>No individual home visits<br>etc. | partial |
| What to expect / `ts-what-to-expect` | Share health history<br>Warning signs: see doctor<br>Chair fitted first<br>Focus follows feedback<br>Pause before standing | most |
| What Research Says / `ts-guide-research` | Mostly small workplace studies<br>Many lacked comparison groups<br>Some studies reported less stress and discomfort<br>Some comparisons found no difference<br>Longer-term effects remain uncertain<br>Reviews mainly concern massage in general<br>Chair-study harm reporting was sparse<br>General-massage reports include rare serious harms<br>etc. | partial |

The research box distinguishes missing safety reporting from an absence of harm.
The actual page says chair studies mostly did not report adverse events “one way
or the other”; **“no harms reported” would imply more reassurance than the evidence
supports**. The rare serious harms item explicitly describes general-massage
reports ([13]–[15]), not a measured rate from chair studies. The favorable and null
findings both remain visible; the studies of stress and discomfort include trials
with controls, alongside small studies without them ([1]–[6], [16]–[22], [26]–[27]).

**Existing duration inconsistency confirmed on staging39:** the introduction says
“10 to 20 minutes,” while the FAQ says “10 to 15 minutes” at corporate/group
events. These could describe different contexts, but the page does not clearly
explain the difference. The draft avoids a numerical duration. Resolving the
underlying wording remains a separate owner decision; no page text is changed by
this review.

## Services and short pages

No eligibility decision or rollout is made here. Length alone is a poor proxy for
navigation value: Services has several distinct choices, while a short Contact
page may still benefit from direct access to a specific destination. Conversely,
a compact two-panel Privacy page may need only its existing jump row. These are
design hypotheses from their known structures, not fresh rendered reviews of
those pages. Review the actual pages, useful destinations, reading obstruction
and duplicated controls before deciding. Keep each eligibility decision explicit;
do not silently exclude or enable pages by word count or section count.

## Validation and next lane

The exact table items were parsed into the page-data schema in memory and checked
with `lint-gists.py`'s validator, including the regulated wording tiers from
`client-pages/wording_tiers.py`: **Therapeutic 27 items / six sections, Deep Tissue
24 / five, Chair 30 / six; all passed** schema, planned-anchor, phrase-length,
exact-duplicate, wording and coverage-marker checks. Counts include `etc.` items.
The populated JSON datasets match these tables exactly, including coverage.
They were initially stored as drafts and are now approved by Ryan. After transfer, the file-level
`python3 -I -B scripts/work-with-me-employer/link-ideas/lint-gists.py` passed for
all four page-data files, and `tests/gists-selftest.py` passed **40 checks**. These
were rerun against the populated files. Approval provenance points to this
document and the fresh capture. These mechanical checks neither grant wording
approval nor verify the appearance of the proposed cards in a browser.

The next step is the guarded staging39-source rehearsal and rendered-card checks
for each approved page before its rollout. The shared core retains whole-paragraph
fallback for any future unapproved data and the plain jump row without JavaScript.

I finished: exact wording approval recorded for all three pages.
Next step: page-specific rehearsals and rendered-card review.
Next lane: guarded staging39 rollout.
See my work: this review and the linked capture report.
Arc: none (ad-hoc)

TL;DR: all three wording sets are approved with research qualifications intact;
deployment evidence is tracked separately in the rollout record.
