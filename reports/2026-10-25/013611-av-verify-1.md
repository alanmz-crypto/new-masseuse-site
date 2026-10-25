# Site op `av-verify-1`

| | |
|---|---|
| operation | `run` on `staging38` |
| script | `client-pages/verify-client-pages.py` `https://staging38.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `fb351a12c5829bff161e4a41688e199c54a764b8` on `feat/2026-10-25-availability-update` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/40151255399 |
| started | 2026-10-25 01:36:01 UTC |

## verify-client-pages.py https://staging38.thaisolitude.com(S_TARGET=staging38): PASS

```text
PASS Services returns 200  [200]
PASS Services title  [Massage Services | Butsaba Dusrai, Oregon LMT]
PASS Services meta description  [Thai, therapeutic and deep tissue massage with Butsaba Dusrai, LMT, in Mt. Tabor, Portland. Every session starts with your goals.]
PASS Services og/twitter title and description follow
PASS Services description fits one snippet (<= 160) and names only services the page lists  [129]
PASS Services title and descriptions carry no restricted wording
PASS Therapeutic Massage returns 200  [200]
PASS page body found
PASS neck-and-shoulders FAQ present once (question and answer)
PASS old headache FAQ gone from the rendered page
PASS Therapeutic Massage: no restricted wording outside the research section
PASS Therapeutic Massage research section: no claim verbs, no we/our/us/studio
PASS Therapeutic Massage source titles: no we/our/us/studio
PASS Therapeutic Massage: FAQ question 'What Is the Difference Between Therapeutic and Relaxation Massage?' appears once
PASS Therapeutic Massage: FAQ question 'How Is a Therapeutic Session Planned?' appears once
PASS Therapeutic Massage: FAQ question 'Will I Feel the Results Right Away?' appears once
PASS Therapeutic Massage: FAQ question 'How Often Should I Come In for Therapeutic Massage?' appears once
PASS Therapeutic Massage: FAQ question 'Is Therapeutic Massage Appropriate If I Have No Specific Complaint?' appears once
PASS Therapeutic Massage: FAQ question 'Can I Ask You to Focus on My Neck and Shoulders?' appears once
PASS Therapeutic Massage: FAQ question 'How Is Therapeutic Massage Different From Deep Tissue Massage?' appears once
PASS Therapeutic Massage: FAQ question 'Can I Change the Pressure During a Session?' appears once
PASS Therapeutic Massage: FAQ question 'Where Do Sessions Take Place?' appears once
PASS Therapeutic Massage: FAQ question 'How Do I Book a Session?' appears once
PASS Therapeutic Massage: 'Yes. You can ask for lighter pressure, a different focus' once
PASS Therapeutic Massage: 'Deep tissue massage uses focused strokes and sustained p' once
PASS Therapeutic Massage: the 'Where Do Sessions Take Place?' answer is the old or the corrected text, once
PASS Therapeutic Massage: the 'How Do I Book a Session?' answer is the old or the corrected text, once
PASS Therapeutic Massage: no links inside FAQ answers (axe nested-interactive)
PASS Therapeutic Massage: the reworded neck and shoulder sentence once, the old one gone
PASS Therapeutic Massage: an encouraging sentence before the 'mixed' one (pace and pressure) and one after it (the review's dose subgroup), once each
PASS Therapeutic Massage: low-back sentence kept once
PASS Therapeutic Massage: the post-stroke and cancer-pain studies are not cited
PASS Therapeutic Massage: the five research panels in order  [['Reviews, and What They Say About Limits', 'Neck, Shoulders and Back', 'Exercise, Flexibility and Recovery', 'Stress, Relaxation and Well-Being', 'Sources']]
PASS Therapeutic Massage: first panel open, the other four closed  [5]
PASS Therapeutic Massage: numbered markers run 1..N in order of first use  [[28, 29, 30]]
PASS Therapeutic Massage: every number points to exactly one source
PASS Therapeutic Massage: as many Sources entries as numbers  [30]
PASS Therapeutic Massage: sources are only PubMed, doi.org, Cochrane or NCCIH  [['pubmed.ncbi.nlm.nih.gov', 'www.nccih.nih.gov']]
PASS Therapeutic Massage: scope note and 'included the disappointing results' sentence once each
PASS Therapeutic Massage: the safety results are on the page (adverse-events review) and the encouraging close once
PASS Therapeutic Massage: the safety paragraph says who was involved (8 of 138 events were massage therapists) and closes on it, once each
PASS Therapeutic Massage: the added discouraging results are there (lower vigor after hard exercise, placebo massage as good as real for back pain, no HRV or blood pressure difference)
PASS Therapeutic Massage: five pictures with five different alt texts, tooltip equal to the alt, none saying 'by Butsaba'  [5]
PASS Therapeutic Massage: existing wording kept: 'Before your session, tell me about any health co'
PASS Therapeutic Massage: existing wording kept: 'New numbness, weakness, unexplained swelling or '
PASS Therapeutic Massage: the four jump links are intact
PASS Therapeutic Massage: licence number in the footer
PASS Therapeutic Massage meta/og descriptions carry no restricted wording
INFO Therapeutic meta description: Therapeutic massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.
PASS Thai Massage returns 200  [200]
PASS Thai Massage body found
PASS Thai Massage: no restricted wording outside the research section
PASS Thai Massage research section: no claim verbs, no we/our/us/studio
PASS Thai Massage source titles: no we/our/us/studio
PASS Thai Massage: 'What Should I Wear to a Thai Massage?' appears once
PASS Thai Massage: 'What Will I Lie On?' appears once
PASS Thai Massage: 'How Long Is a Session?' appears once
PASS Thai Massage: 'Do I Need to Be Flexible?' appears once
PASS Thai Massage: 'Where Do Sessions Take Place?' appears once
PASS Thai Massage: 'How Is Thai Massage Different From Your Other Services?' appears once
PASS Thai Massage: 'How Do I Book a Session?' appears once
PASS Thai Massage: 'Questions About Thai Massage' appears once
PASS Thai Massage: 'Sessions are 60 or 90 minutes.' once
PASS Thai Massage: 'Thai massage is done on a mat.' once
PASS Thai Massage: 'I trained in traditional Thai bodywork at Still Light Center.' once
PASS Thai Massage: 'in 2019 UNESCO added Nuad Thai, traditional Thai massage,' once
PASS Thai Massage: the 'Where Do Sessions Take Place?' answer is the old or the corrected text, once
PASS Thai Massage: the 'How Do I Book a Session?' answer is the old or the corrected text, once
PASS Thai Massage: no links inside FAQ answers (axe nested-interactive)
PASS Thai Massage: the five research panels in order  [['Reviews, and What They Say About Limits', 'Back Pain', 'Neck, Shoulders, Arms and Feet', 'Stress, Energy and Recovery', 'Sources']]
PASS Thai Massage: first panel open, the other four closed  [5]
PASS Thai Massage: numbered markers run 1..N in order of first use  [[29, 30, 31]]
PASS Thai Massage: every number points to exactly one source
PASS Thai Massage: as many Sources entries as numbers  [31]
PASS Thai Massage: sources are only PubMed, doi.org, Cochrane, NCCIH or UNESCO  [['doi.org', 'ich.unesco.org', 'pubmed.ncbi.nlm.nih.gov', 'www.cochrane.org', 'www.nccih.nih.gov']]
PASS Thai Massage: scope note and 'included the disappointing results' sentence once each
PASS Thai Massage: the safety results are on the page (adverse-events review, the Thai case report, the commentary) and the encouraging close once
PASS Thai Massage: the safety paragraph says who was involved (8 of 138 events were massage therapists) and closes on it, once each
PASS Thai Massage: the null shoulder-strength result is quoted from the authors
PASS Thai Massage: existing wording kept: 'You stay fully clothed, so wear something loose '
PASS Thai Massage: existing wording kept: 'New numbness, weakness, unexplained swelling or '
PASS Thai Massage: the three jump links are intact
PASS Thai Massage: licence number in the footer
PASS Thai Massage title, meta/og/twitter descriptions carry no restricted wording
PASS Thai Massage title is the approved one  [Thai Massage in Mt. Tabor | Butsaba Dusrai, Oregon LMT]
PASS Thai Massage meta description is the approved one  [Fully clothed Thai massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: rhythmic compression, gentle rocking and assisted stretching at your pace.]
PASS Thai Massage og and twitter title and description follow
PASS Thai Massage description fits one snippet (<= 160) and the title one line (<= 60)  [150/54]
PASS Thai Massage description is a whole sentence (no heading text, no cut-off)
PASS Deep Tissue Massage returns 200  [200]
PASS Deep Tissue Massage body found
PASS Deep Tissue Massage: no restricted wording outside the research section
PASS Deep Tissue Massage research section: no claim verbs, no we/our/us/studio
PASS Deep Tissue Massage source titles: no we/our/us/studio
PASS Deep Tissue Massage: existing wording kept: 'Deep tissue massage uses slow, deliberate pressu'
PASS Deep Tissue Massage: existing wording kept: 'Before your session, tell me about any health co'
PASS Deep Tissue Massage: licence number in the footer
PASS Deep Tissue Massage: the FAQ heading once
PASS Deep Tissue Massage: FAQ question 'Does Deep Tissue Massage Have to Hurt?' appears once
PASS Deep Tissue Massage: FAQ question 'How Is Deep Tissue Massage Different From Therapeutic Massage?' appears once
PASS Deep Tissue Massage: FAQ question 'Can I Choose Where You Focus?' appears once
PASS Deep Tissue Massage: FAQ question 'What Should I Tell You Before a Session?' appears once
PASS Deep Tissue Massage: FAQ question 'Where Do Sessions Take Place?' appears once
PASS Deep Tissue Massage: FAQ question 'How Do I Book a Session?' appears once
PASS Deep Tissue Massage: 'No. Deep pressure is never required.' once
PASS Deep Tissue Massage: the 'Where Do Sessions Take Place?' answer is the old or the corrected text, once
PASS Deep Tissue Massage: the 'How Do I Book a Session?' answer is the old or the corrected text, once
PASS Deep Tissue Massage: the page's safety sentence is on the page twice (page text and FAQ answer)
PASS Deep Tissue Massage: no links inside FAQ answers (axe nested-interactive)
PASS Deep Tissue Massage: the new body paragraph once, the three old sentences gone
PASS Deep Tissue Massage: 'myofascial' appears once, inside a verbatim source title
PASS Deep Tissue Massage: the five research panels in order  [['Reviews, and What They Say About Limits', 'Neck, Shoulders and Back', 'Exercise, Soreness and Recovery', 'Relaxation and the Role of Pressure', 'Sources']]
PASS Deep Tissue Massage: first panel open, the other four closed  [5]
PASS Deep Tissue Massage: numbered markers run 1..N in order of first use  [[31, 32, 33]]
PASS Deep Tissue Massage: every number points to exactly one source
PASS Deep Tissue Massage: as many Sources entries as numbers (33)  [33]
PASS Deep Tissue Massage: sources are only PubMed, doi.org, Cochrane or NCCIH  [['pubmed.ncbi.nlm.nih.gov', 'www.nccih.nih.gov']]
PASS Deep Tissue Massage: scope note and 'included the disappointing results' sentence once each
PASS Deep Tissue Massage: the safety paragraph names force and who gave the massage, and closes on the practice, once each
PASS Deep Tissue Massage: the discouraging results are there (12-month null, not cost-effective, little or no difference vs placebo massage, calf stiffness unchanged, blood lactate unchanged)
PASS Deep Tissue Massage: the athletes trial is in the research section with its limits stated
PASS Deep Tissue Massage: three pictures, tooltip equal to the alt text, none saying 'by Butsaba'  [3]
PASS Therapeutic Massage page returns 200 (meta check)  [200]
PASS Therapeutic Massage title, meta/og/twitter descriptions carry no restricted wording
PASS Therapeutic Massage title is the approved one  [Therapeutic Massage | Butsaba Dusrai, Oregon LMT]
PASS Therapeutic Massage meta description is the approved one  [Therapeutic massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: focused work where you ask for it, with pressure and pace adjusted as you go.]
PASS Therapeutic Massage og and twitter title and description follow
PASS Therapeutic Massage description fits one snippet (<= 160) and the title one line (<= 60)  [146/48]
PASS Therapeutic Massage description is a whole sentence (no heading text, no cut-off)
PASS Deep Tissue Massage page returns 200 (meta check)  [200]
PASS Deep Tissue Massage title, meta/og/twitter descriptions carry no restricted wording
PASS Deep Tissue Massage title is the approved one  [Deep Tissue Massage | Butsaba Dusrai, Oregon LMT]
PASS Deep Tissue Massage meta description is the approved one  [Deep tissue massage in Mt. Tabor, Portland with Butsaba Dusrai, LMT: slow, deliberate pressure on tight muscles, lightened whenever you ask.]
PASS Deep Tissue Massage og and twitter title and description follow
PASS Deep Tissue Massage description fits one snippet (<= 160) and the title one line (<= 60)  [140/48]
PASS Deep Tissue Massage description is a whole sentence (no heading text, no cut-off)
PASS Corporate & Group Massage returns 200  [200]
PASS Corporate & Group Massage body found
PASS Corporate & Group Massage: no restricted wording anywhere on the page (no research section: the full list applies)  [{'before': [], 'research': [], 'cites': [], 'found': False}]
PASS Corporate & Group Massage title and meta description are the approved ones  [Corporate Chair Massage in Portland, OR | Thai Solitude]
PASS Corporate & Group Massage og and twitter title and description follow
PASS Corporate & Group Massage description fits one snippet (<= 160) and the title one line (<= 60)  [147/55]
PASS Corporate & Group Massage: one h1
PASS Corporate & Group Massage: licence number in the footer
PASS Corporate & Group Massage: both quote buttons carry the same mailto link with a subject and a body
PASS Corporate & Group Massage: no link inside FAQ answers (axe nested-interactive)
PASS Corporate & Group Massage: the capacity figure is stated as an estimate
PASS Corporate & Group Massage: the rate is shown as proposed and confirmed by a written quote
PASS Corporate & Group Massage: no invented fee, minimum, deposit or cancellation terms
PASS Corporate & Group Massage: no testimonials, logos or insurance claims
PASS Services: the corporate row links to the new page and the three existing service links are still there
PASS Services: the existing descriptions are unchanged
PASS Work With Me returns 200  [200]
PASS Work With Me: kept as it was: 'Start — From November 1 · open for days, evening'
PASS Work With Me: kept as it was: 'Interviews — By phone or video call now, in pers'
PASS Work With Me: kept as it was: 'Oregon license lookup'
PASS Work With Me: kept as it was: 'Email me about an LMT position'
PASS Work With Me: kept as it was: 'View résumé (PDF)'
PASS Work With Me: the repeated résumé links are all still there (3)  [3]
PASS Work With Me: no 'Relocating from Chicago' wording
PASS Work With Me: no announcement strip (it is an employment page)
PASS The 'For Practices' menu label is unchanged
PASS Work With Me: the independent-practice sentence is there once
PASS Work With Me: the 'For clients' paragraph says corporate bookings begin November 1 and individual appointments are planned for summer 2027
PASS Home: returns 200 and holds exactly one availability strip  [200]
PASS Home: the strip names the availability, links to the corporate page and avoids the house-list words  [Corporate & Group Massage · Portland Currently booking corpo]
PASS About: returns 200 and holds exactly one availability strip  [200]
PASS About: the strip names the availability, links to the corporate page and avoids the house-list words  [Corporate & Group Massage · Portland Currently booking corpo]
PASS Services: returns 200 and holds exactly one availability strip  [200]
PASS Services: the strip names the availability, links to the corporate page and avoids the house-list words  [Corporate & Group Massage · Portland Currently booking corpo]
PASS Contact: returns 200 and holds exactly one availability strip  [200]
PASS Contact: the strip names the availability, links to the corporate page and avoids the house-list words  [Corporate & Group Massage · Portland Currently booking corpo]
PASS Thai Massage: returns 200 and holds exactly one availability strip  [200]
PASS Thai Massage: the strip names the availability, links to the corporate page and avoids the house-list words  [Corporate & Group Massage · Portland Currently booking corpo]
PASS Therapeutic Massage: returns 200 and holds exactly one availability strip  [200]
PASS Therapeutic Massage: the strip names the availability, links to the corporate page and avoids the house-list words  [Corporate & Group Massage · Portland Currently booking corpo]
PASS Deep Tissue Massage: returns 200 and holds exactly one availability strip  [200]
PASS Deep Tissue Massage: the strip names the availability, links to the corporate page and avoids the house-list words  [Corporate & Group Massage · Portland Currently booking corpo]
PASS Thai Massage: both corrected FAQ answers are there once and the old ones are gone
PASS Therapeutic Massage: both corrected FAQ answers are there once and the old ones are gone
PASS Deep Tissue Massage: both corrected FAQ answers are there once and the old ones are gone
PASS Corporate page: states that bookings begin November 1, 2026, and that events are scheduled around her work commitments
PASS Corporate page: no strip on the page itself

RESULT: all checks passed
```

**RESULT: PASS** - verify-client-pages.py succeeded

_finished 2026-10-25 01:36:11 UTC_
