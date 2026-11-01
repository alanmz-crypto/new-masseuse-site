# Site op `review-c-live-browser-20261101`

| | |
|---|---|
| operation | `run` on `live` |
| script | `review-c/check-bundle-c.mjs` `https://thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `d9f400031639e71343d182fcbd6b628f4a16cfd3` on `codex/2026-11-01-review-bundle-d` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/41334743122 |
| started | 2026-11-01 18:05:15 UTC |

## check-bundle-c.mjs https://thaisolitude.com(S_TARGET=live): FAIL (exit 1)

_(last 400 of 1337 lines; the whole log is in the run's artifact)_
```text
PASS /corporate-massage-portland/ 320 dark deep-accent keyboard focus: Corporate action {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /corporate-massage-portland/ Enter toggles once
PASS /corporate-massage-portland/ Space toggles once
PASS /corporate-massage-portland/ mouse toggles once
PASS /corporate-massage-portland/ mouse closes once
PASS /corporate-massage-portland/ scroll-to-top Space works
PASS /corporate-massage-portland/ no browser errors
INFO Bundle C page done /corporate-massage-portland/ ms=5368
INFO Bundle C page start /work-with-me/
PASS /work-with-me/ HTTP 200
PASS /work-with-me/ fonts finish loading within 15 seconds
PASS /work-with-me/ finite full-page lazy-load scroll
PASS /work-with-me/ 1280 light no side-scroll
PASS /work-with-me/ 1280 light expected FAQ count and one native button per question items=3 buttons=3
PASS /work-with-me/ 1280 light disclosure ARIA, unique controls and no tab roles
PASS /work-with-me/ 1280 light distinct footer names and scroll button role
PASS /work-with-me/ 1280 light résumé links announce new tab
PASS /work-with-me/ 1280 native FAQ wrapper leaves all item/question geometry unchanged items=3
INFO FAQ_GEOMETRY {"path":"/work-with-me/","width":1280,"compared":3,"failures":0}
PASS /work-with-me/ 1280 light one H1 and all internal jump targets exist
PASS /work-with-me/ 1280 light FAQ normal/active icons point down/up
PASS /work-with-me/ 1280 light RSD/oEmbed/shortlink discovery absent
PASS /work-with-me/ 1280 light booking landmark appears only on listed pages
LAYOUT {"path":"/work-with-me/","width":1280,"height":6045,"h1Y":248.671875,"h1Height":120.25,"h1MarginTop":"9.216px","entryHeaderPaddingTop":"0px","stripHeight":0,"heartRowGap":null,"heartRows":[]}
PASS /work-with-me/ 1280 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /work-with-me/ 1280 dark no side-scroll
PASS /work-with-me/ 1280 dark expected FAQ count and one native button per question items=3 buttons=3
PASS /work-with-me/ 1280 dark disclosure ARIA, unique controls and no tab roles
PASS /work-with-me/ 1280 dark distinct footer names and scroll button role
PASS /work-with-me/ 1280 dark résumé links announce new tab
PASS /work-with-me/ 1280 dark one H1 and all internal jump targets exist
PASS /work-with-me/ 1280 dark FAQ normal/active icons point down/up
PASS /work-with-me/ 1280 dark RSD/oEmbed/shortlink discovery absent
PASS /work-with-me/ 1280 dark booking landmark appears only on listed pages
PASS /work-with-me/ 1280 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /work-with-me/ 390 light no side-scroll
PASS /work-with-me/ 390 light expected FAQ count and one native button per question items=3 buttons=3
PASS /work-with-me/ 390 light disclosure ARIA, unique controls and no tab roles
PASS /work-with-me/ 390 light distinct footer names and scroll button role
PASS /work-with-me/ 390 light résumé links announce new tab
PASS /work-with-me/ 390 native FAQ wrapper leaves all item/question geometry unchanged items=3
INFO FAQ_GEOMETRY {"path":"/work-with-me/","width":390,"compared":3,"failures":0}
PASS /work-with-me/ 390 light one H1 and all internal jump targets exist
PASS /work-with-me/ 390 light FAQ normal/active icons point down/up
PASS /work-with-me/ 390 light RSD/oEmbed/shortlink discovery absent
PASS /work-with-me/ 390 light booking landmark appears only on listed pages
LAYOUT {"path":"/work-with-me/","width":390,"height":7981,"h1Y":190.34375,"h1Height":125.71875,"h1MarginTop":"5.58144px","entryHeaderPaddingTop":"0px","stripHeight":0,"heartRowGap":null,"heartRows":[]}
PASS /work-with-me/ 390 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /work-with-me/ 390 dark no side-scroll
PASS /work-with-me/ 390 dark expected FAQ count and one native button per question items=3 buttons=3
PASS /work-with-me/ 390 dark disclosure ARIA, unique controls and no tab roles
PASS /work-with-me/ 390 dark distinct footer names and scroll button role
PASS /work-with-me/ 390 dark résumé links announce new tab
PASS /work-with-me/ 390 dark one H1 and all internal jump targets exist
PASS /work-with-me/ 390 dark FAQ normal/active icons point down/up
PASS /work-with-me/ 390 dark RSD/oEmbed/shortlink discovery absent
PASS /work-with-me/ 390 dark booking landmark appears only on listed pages
PASS /work-with-me/ 390 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /work-with-me/ 320 light no side-scroll
PASS /work-with-me/ 320 light expected FAQ count and one native button per question items=3 buttons=3
PASS /work-with-me/ 320 light disclosure ARIA, unique controls and no tab roles
PASS /work-with-me/ 320 light distinct footer names and scroll button role
PASS /work-with-me/ 320 light résumé links announce new tab
PASS /work-with-me/ 320 native FAQ wrapper leaves all item/question geometry unchanged items=3
INFO FAQ_GEOMETRY {"path":"/work-with-me/","width":320,"compared":3,"failures":0}
PASS /work-with-me/ 320 light one H1 and all internal jump targets exist
PASS /work-with-me/ 320 light FAQ normal/active icons point down/up
PASS /work-with-me/ 320 light RSD/oEmbed/shortlink discovery absent
PASS /work-with-me/ 320 light booking landmark appears only on listed pages
LAYOUT {"path":"/work-with-me/","width":320,"height":9671,"h1Y":190.34375,"h1Height":125.71875,"h1MarginTop":"5.58144px","entryHeaderPaddingTop":"0px","stripHeight":0,"heartRowGap":null,"heartRows":[]}
PASS /work-with-me/ 320 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /work-with-me/ 320 dark no side-scroll
PASS /work-with-me/ 320 dark expected FAQ count and one native button per question items=3 buttons=3
PASS /work-with-me/ 320 dark disclosure ARIA, unique controls and no tab roles
PASS /work-with-me/ 320 dark distinct footer names and scroll button role
PASS /work-with-me/ 320 dark résumé links announce new tab
PASS /work-with-me/ 320 dark one H1 and all internal jump targets exist
PASS /work-with-me/ 320 dark FAQ normal/active icons point down/up
PASS /work-with-me/ 320 dark RSD/oEmbed/shortlink discovery absent
PASS /work-with-me/ 320 dark booking landmark appears only on listed pages
PASS /work-with-me/ 320 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /work-with-me/ Enter toggles once
PASS /work-with-me/ Space toggles once
PASS /work-with-me/ mouse toggles once
PASS /work-with-me/ mouse closes once
PASS /work-with-me/ answer-link keyup does not toggle its FAQ
PASS /work-with-me/ scroll-to-top Space works
PASS /work-with-me/ no browser errors
INFO Bundle C page done /work-with-me/ ms=6632
INFO Bundle C page start /contact/
PASS /contact/ HTTP 200
PASS /contact/ fonts finish loading within 15 seconds
PASS /contact/ finite full-page lazy-load scroll
PASS /contact/ 1280 light no side-scroll
PASS /contact/ 1280 light expected FAQ count and one native button per question items=0 buttons=0
PASS /contact/ 1280 light disclosure ARIA, unique controls and no tab roles
PASS /contact/ 1280 light distinct footer names and scroll button role
PASS /contact/ 1280 light résumé links announce new tab
PASS /contact/ 1280 light one H1 and all internal jump targets exist
PASS /contact/ 1280 light FAQ normal/active icons point down/up
PASS /contact/ 1280 light RSD/oEmbed/shortlink discovery absent
PASS /contact/ 1280 light booking landmark appears only on listed pages
PASS /contact/ 1280 light strip keeps opening/individual dates exactly once and Corporate path
PASS /contact/ 1280 light Contact phone links are visibly underlined
PASS /contact/ 1280 light titled lazy iframe retains exact Mt. Tabor query
LAYOUT {"path":"/contact/","width":1280,"height":2990,"h1Y":348.609375,"h1Height":190.90625,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
PASS /contact/ 1280 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /contact/ 1280 light deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /contact/ 1280 light deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /contact/ 1280 dark no side-scroll
PASS /contact/ 1280 dark expected FAQ count and one native button per question items=0 buttons=0
PASS /contact/ 1280 dark disclosure ARIA, unique controls and no tab roles
PASS /contact/ 1280 dark distinct footer names and scroll button role
PASS /contact/ 1280 dark résumé links announce new tab
PASS /contact/ 1280 dark one H1 and all internal jump targets exist
PASS /contact/ 1280 dark FAQ normal/active icons point down/up
PASS /contact/ 1280 dark RSD/oEmbed/shortlink discovery absent
PASS /contact/ 1280 dark booking landmark appears only on listed pages
PASS /contact/ 1280 dark strip keeps opening/individual dates exactly once and Corporate path
PASS /contact/ 1280 dark Contact phone links are visibly underlined
PASS /contact/ 1280 dark titled lazy iframe retains exact Mt. Tabor query
PASS /contact/ 1280 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /contact/ 1280 dark deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /contact/ 1280 dark deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /contact/ 390 light no side-scroll
PASS /contact/ 390 light expected FAQ count and one native button per question items=0 buttons=0
PASS /contact/ 390 light disclosure ARIA, unique controls and no tab roles
PASS /contact/ 390 light distinct footer names and scroll button role
PASS /contact/ 390 light résumé links announce new tab
PASS /contact/ 390 light one H1 and all internal jump targets exist
PASS /contact/ 390 light FAQ normal/active icons point down/up
PASS /contact/ 390 light RSD/oEmbed/shortlink discovery absent
PASS /contact/ 390 light booking landmark appears only on listed pages
PASS /contact/ 390 light strip keeps opening/individual dates exactly once and Corporate path
PASS /contact/ 390 light phone strip is shorter than desktop
PASS /contact/ 390 light Contact phone links are visibly underlined
PASS /contact/ 390 light titled lazy iframe retains exact Mt. Tabor query
LAYOUT {"path":"/contact/","width":390,"height":2993,"h1Y":300.953125,"h1Height":89.46875,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
PASS /contact/ 390 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /contact/ 390 light deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /contact/ 390 light deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /contact/ 390 dark no side-scroll
PASS /contact/ 390 dark expected FAQ count and one native button per question items=0 buttons=0
PASS /contact/ 390 dark disclosure ARIA, unique controls and no tab roles
PASS /contact/ 390 dark distinct footer names and scroll button role
PASS /contact/ 390 dark résumé links announce new tab
PASS /contact/ 390 dark one H1 and all internal jump targets exist
PASS /contact/ 390 dark FAQ normal/active icons point down/up
PASS /contact/ 390 dark RSD/oEmbed/shortlink discovery absent
PASS /contact/ 390 dark booking landmark appears only on listed pages
PASS /contact/ 390 dark strip keeps opening/individual dates exactly once and Corporate path
PASS /contact/ 390 dark phone strip is shorter than desktop
PASS /contact/ 390 dark Contact phone links are visibly underlined
PASS /contact/ 390 dark titled lazy iframe retains exact Mt. Tabor query
PASS /contact/ 390 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /contact/ 390 dark deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /contact/ 390 dark deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /contact/ 320 light no side-scroll
PASS /contact/ 320 light expected FAQ count and one native button per question items=0 buttons=0
PASS /contact/ 320 light disclosure ARIA, unique controls and no tab roles
PASS /contact/ 320 light distinct footer names and scroll button role
PASS /contact/ 320 light résumé links announce new tab
PASS /contact/ 320 light one H1 and all internal jump targets exist
PASS /contact/ 320 light FAQ normal/active icons point down/up
PASS /contact/ 320 light RSD/oEmbed/shortlink discovery absent
PASS /contact/ 320 light booking landmark appears only on listed pages
PASS /contact/ 320 light strip keeps opening/individual dates exactly once and Corporate path
PASS /contact/ 320 light phone strip is shorter than desktop
PASS /contact/ 320 light Contact phone links are visibly underlined
PASS /contact/ 320 light titled lazy iframe retains exact Mt. Tabor query
LAYOUT {"path":"/contact/","width":320,"height":3304,"h1Y":318,"h1Height":125.734375,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
PASS /contact/ 320 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /contact/ 320 light deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /contact/ 320 light deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /contact/ 320 dark no side-scroll
PASS /contact/ 320 dark expected FAQ count and one native button per question items=0 buttons=0
PASS /contact/ 320 dark disclosure ARIA, unique controls and no tab roles
PASS /contact/ 320 dark distinct footer names and scroll button role
PASS /contact/ 320 dark résumé links announce new tab
PASS /contact/ 320 dark one H1 and all internal jump targets exist
PASS /contact/ 320 dark FAQ normal/active icons point down/up
PASS /contact/ 320 dark RSD/oEmbed/shortlink discovery absent
PASS /contact/ 320 dark booking landmark appears only on listed pages
PASS /contact/ 320 dark strip keeps opening/individual dates exactly once and Corporate path
PASS /contact/ 320 dark phone strip is shorter than desktop
PASS /contact/ 320 dark Contact phone links are visibly underlined
PASS /contact/ 320 dark titled lazy iframe retains exact Mt. Tabor query
PASS /contact/ 320 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /contact/ 320 dark deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /contact/ 320 dark deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /contact/ 1280 map height 500 500
PASS /contact/ 976 map height 350 350
PASS /contact/ 767 map height 325 325
PASS /contact/ 390 map height 325 325
PASS /contact/ scroll-to-top Space works
PASS /contact/ no browser errors
INFO Bundle C page done /contact/ ms=3581
INFO Bundle C page start /privacy-policy/
PASS /privacy-policy/ HTTP 200
PASS /privacy-policy/ fonts finish loading within 15 seconds
PASS /privacy-policy/ finite full-page lazy-load scroll
PASS /privacy-policy/ 1280 light no side-scroll
PASS /privacy-policy/ 1280 light expected FAQ count and one native button per question items=2 buttons=2
PASS /privacy-policy/ 1280 light disclosure ARIA, unique controls and no tab roles
PASS /privacy-policy/ 1280 light distinct footer names and scroll button role
PASS /privacy-policy/ 1280 light résumé links announce new tab
PASS /privacy-policy/ 1280 native FAQ wrapper leaves all item/question geometry unchanged items=2
INFO FAQ_GEOMETRY {"path":"/privacy-policy/","width":1280,"compared":2,"failures":0}
PASS /privacy-policy/ 1280 light one H1 and all internal jump targets exist
PASS /privacy-policy/ 1280 light FAQ normal/active icons point down/up
PASS /privacy-policy/ 1280 light RSD/oEmbed/shortlink discovery absent
PASS /privacy-policy/ 1280 light booking landmark appears only on listed pages
PASS /privacy-policy/ 1280 light strip keeps opening/individual dates exactly once and Corporate path
LAYOUT {"path":"/privacy-policy/","width":1280,"height":1405,"h1Y":228.6875,"h1Height":38.390625,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
PASS /privacy-policy/ 1280 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /privacy-policy/ 1280 light deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /privacy-policy/ 1280 light deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /privacy-policy/ 1280 dark no side-scroll
PASS /privacy-policy/ 1280 dark expected FAQ count and one native button per question items=2 buttons=2
PASS /privacy-policy/ 1280 dark disclosure ARIA, unique controls and no tab roles
PASS /privacy-policy/ 1280 dark distinct footer names and scroll button role
PASS /privacy-policy/ 1280 dark résumé links announce new tab
PASS /privacy-policy/ 1280 dark one H1 and all internal jump targets exist
PASS /privacy-policy/ 1280 dark FAQ normal/active icons point down/up
PASS /privacy-policy/ 1280 dark RSD/oEmbed/shortlink discovery absent
PASS /privacy-policy/ 1280 dark booking landmark appears only on listed pages
PASS /privacy-policy/ 1280 dark strip keeps opening/individual dates exactly once and Corporate path
PASS /privacy-policy/ 1280 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /privacy-policy/ 1280 dark deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /privacy-policy/ 1280 dark deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /privacy-policy/ 390 light no side-scroll
PASS /privacy-policy/ 390 light expected FAQ count and one native button per question items=2 buttons=2
PASS /privacy-policy/ 390 light disclosure ARIA, unique controls and no tab roles
PASS /privacy-policy/ 390 light distinct footer names and scroll button role
PASS /privacy-policy/ 390 light résumé links announce new tab
PASS /privacy-policy/ 390 native FAQ wrapper leaves all item/question geometry unchanged items=2
INFO FAQ_GEOMETRY {"path":"/privacy-policy/","width":390,"compared":2,"failures":0}
PASS /privacy-policy/ 390 light one H1 and all internal jump targets exist
PASS /privacy-policy/ 390 light FAQ normal/active icons point down/up
PASS /privacy-policy/ 390 light RSD/oEmbed/shortlink discovery absent
PASS /privacy-policy/ 390 light booking landmark appears only on listed pages
PASS /privacy-policy/ 390 light strip keeps opening/individual dates exactly once and Corporate path
PASS /privacy-policy/ 390 light phone strip is shorter than desktop
LAYOUT {"path":"/privacy-policy/","width":390,"height":2198,"h1Y":186.875,"h1Height":35.015625,"h1MarginTop":"0px","entryHeaderPaddingTop":"20.52px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
PASS /privacy-policy/ 390 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /privacy-policy/ 390 light deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /privacy-policy/ 390 light deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /privacy-policy/ 390 dark no side-scroll
PASS /privacy-policy/ 390 dark expected FAQ count and one native button per question items=2 buttons=2
PASS /privacy-policy/ 390 dark disclosure ARIA, unique controls and no tab roles
PASS /privacy-policy/ 390 dark distinct footer names and scroll button role
PASS /privacy-policy/ 390 dark résumé links announce new tab
PASS /privacy-policy/ 390 dark one H1 and all internal jump targets exist
PASS /privacy-policy/ 390 dark FAQ normal/active icons point down/up
PASS /privacy-policy/ 390 dark RSD/oEmbed/shortlink discovery absent
PASS /privacy-policy/ 390 dark booking landmark appears only on listed pages
PASS /privacy-policy/ 390 dark strip keeps opening/individual dates exactly once and Corporate path
PASS /privacy-policy/ 390 dark phone strip is shorter than desktop
PASS /privacy-policy/ 390 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /privacy-policy/ 390 dark deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /privacy-policy/ 390 dark deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /privacy-policy/ 320 light no side-scroll
PASS /privacy-policy/ 320 light expected FAQ count and one native button per question items=2 buttons=2
PASS /privacy-policy/ 320 light disclosure ARIA, unique controls and no tab roles
PASS /privacy-policy/ 320 light distinct footer names and scroll button role
PASS /privacy-policy/ 320 light résumé links announce new tab
PASS /privacy-policy/ 320 native FAQ wrapper leaves all item/question geometry unchanged items=2
INFO FAQ_GEOMETRY {"path":"/privacy-policy/","width":320,"compared":2,"failures":0}
PASS /privacy-policy/ 320 light one H1 and all internal jump targets exist
PASS /privacy-policy/ 320 light FAQ normal/active icons point down/up
PASS /privacy-policy/ 320 light RSD/oEmbed/shortlink discovery absent
PASS /privacy-policy/ 320 light booking landmark appears only on listed pages
PASS /privacy-policy/ 320 light strip keeps opening/individual dates exactly once and Corporate path
PASS /privacy-policy/ 320 light phone strip is shorter than desktop
LAYOUT {"path":"/privacy-policy/","width":320,"height":2535,"h1Y":203.921875,"h1Height":35.015625,"h1MarginTop":"0px","entryHeaderPaddingTop":"20.52px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
PASS /privacy-policy/ 320 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /privacy-policy/ 320 light deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /privacy-policy/ 320 light deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /privacy-policy/ 320 dark no side-scroll
PASS /privacy-policy/ 320 dark expected FAQ count and one native button per question items=2 buttons=2
PASS /privacy-policy/ 320 dark disclosure ARIA, unique controls and no tab roles
PASS /privacy-policy/ 320 dark distinct footer names and scroll button role
PASS /privacy-policy/ 320 dark résumé links announce new tab
PASS /privacy-policy/ 320 dark one H1 and all internal jump targets exist
PASS /privacy-policy/ 320 dark FAQ normal/active icons point down/up
PASS /privacy-policy/ 320 dark RSD/oEmbed/shortlink discovery absent
PASS /privacy-policy/ 320 dark booking landmark appears only on listed pages
PASS /privacy-policy/ 320 dark strip keeps opening/individual dates exactly once and Corporate path
PASS /privacy-policy/ 320 dark phone strip is shorter than desktop
PASS /privacy-policy/ 320 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /privacy-policy/ 320 dark deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /privacy-policy/ 320 dark deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /privacy-policy/ Enter toggles once
PASS /privacy-policy/ Space toggles once
PASS /privacy-policy/ mouse toggles once
PASS /privacy-policy/ mouse closes once
PASS /privacy-policy/ scroll-to-top Space works
PASS /privacy-policy/ no browser errors
INFO Bundle C page done /privacy-policy/ ms=3967
INFO Bundle C page start /accessibility/
PASS /accessibility/ HTTP 200
PASS /accessibility/ fonts finish loading within 15 seconds
PASS /accessibility/ finite full-page lazy-load scroll
PASS /accessibility/ 1280 light no side-scroll
PASS /accessibility/ 1280 light expected FAQ count and one native button per question items=0 buttons=0
PASS /accessibility/ 1280 light disclosure ARIA, unique controls and no tab roles
PASS /accessibility/ 1280 light distinct footer names and scroll button role
PASS /accessibility/ 1280 light résumé links announce new tab
PASS /accessibility/ 1280 light one H1 and all internal jump targets exist
PASS /accessibility/ 1280 light FAQ normal/active icons point down/up
PASS /accessibility/ 1280 light RSD/oEmbed/shortlink discovery absent
PASS /accessibility/ 1280 light booking landmark appears only on listed pages
PASS /accessibility/ 1280 light strip keeps opening/individual dates exactly once and Corporate path
LAYOUT {"path":"/accessibility/","width":1280,"height":1680,"h1Y":228.6875,"h1Height":38.390625,"h1MarginTop":"0px","entryHeaderPaddingTop":"0px","stripHeight":77.6875,"heartRowGap":null,"heartRows":[]}
PASS /accessibility/ 1280 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /accessibility/ 1280 light deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /accessibility/ 1280 light deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /accessibility/ 1280 dark no side-scroll
PASS /accessibility/ 1280 dark expected FAQ count and one native button per question items=0 buttons=0
PASS /accessibility/ 1280 dark disclosure ARIA, unique controls and no tab roles
PASS /accessibility/ 1280 dark distinct footer names and scroll button role
PASS /accessibility/ 1280 dark résumé links announce new tab
PASS /accessibility/ 1280 dark one H1 and all internal jump targets exist
PASS /accessibility/ 1280 dark FAQ normal/active icons point down/up
PASS /accessibility/ 1280 dark RSD/oEmbed/shortlink discovery absent
PASS /accessibility/ 1280 dark booking landmark appears only on listed pages
PASS /accessibility/ 1280 dark strip keeps opening/individual dates exactly once and Corporate path
PASS /accessibility/ 1280 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /accessibility/ 1280 dark deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /accessibility/ 1280 dark deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /accessibility/ 390 light no side-scroll
PASS /accessibility/ 390 light expected FAQ count and one native button per question items=0 buttons=0
PASS /accessibility/ 390 light disclosure ARIA, unique controls and no tab roles
PASS /accessibility/ 390 light distinct footer names and scroll button role
PASS /accessibility/ 390 light résumé links announce new tab
PASS /accessibility/ 390 light one H1 and all internal jump targets exist
PASS /accessibility/ 390 light FAQ normal/active icons point down/up
PASS /accessibility/ 390 light RSD/oEmbed/shortlink discovery absent
PASS /accessibility/ 390 light booking landmark appears only on listed pages
PASS /accessibility/ 390 light strip keeps opening/individual dates exactly once and Corporate path
PASS /accessibility/ 390 light phone strip is shorter than desktop
LAYOUT {"path":"/accessibility/","width":390,"height":2766,"h1Y":186.875,"h1Height":35.015625,"h1MarginTop":"0px","entryHeaderPaddingTop":"20.52px","stripHeight":81.359375,"heartRowGap":null,"heartRows":[]}
PASS /accessibility/ 390 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /accessibility/ 390 light deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /accessibility/ 390 light deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /accessibility/ 390 dark no side-scroll
PASS /accessibility/ 390 dark expected FAQ count and one native button per question items=0 buttons=0
PASS /accessibility/ 390 dark disclosure ARIA, unique controls and no tab roles
PASS /accessibility/ 390 dark distinct footer names and scroll button role
PASS /accessibility/ 390 dark résumé links announce new tab
PASS /accessibility/ 390 dark one H1 and all internal jump targets exist
PASS /accessibility/ 390 dark FAQ normal/active icons point down/up
PASS /accessibility/ 390 dark RSD/oEmbed/shortlink discovery absent
PASS /accessibility/ 390 dark booking landmark appears only on listed pages
PASS /accessibility/ 390 dark strip keeps opening/individual dates exactly once and Corporate path
PASS /accessibility/ 390 dark phone strip is shorter than desktop
PASS /accessibility/ 390 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /accessibility/ 390 dark deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /accessibility/ 390 dark deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /accessibility/ 320 light no side-scroll
PASS /accessibility/ 320 light expected FAQ count and one native button per question items=0 buttons=0
PASS /accessibility/ 320 light disclosure ARIA, unique controls and no tab roles
PASS /accessibility/ 320 light distinct footer names and scroll button role
PASS /accessibility/ 320 light résumé links announce new tab
PASS /accessibility/ 320 light one H1 and all internal jump targets exist
PASS /accessibility/ 320 light FAQ normal/active icons point down/up
PASS /accessibility/ 320 light RSD/oEmbed/shortlink discovery absent
PASS /accessibility/ 320 light booking landmark appears only on listed pages
PASS /accessibility/ 320 light strip keeps opening/individual dates exactly once and Corporate path
PASS /accessibility/ 320 light phone strip is shorter than desktop
LAYOUT {"path":"/accessibility/","width":320,"height":3267,"h1Y":203.921875,"h1Height":35.015625,"h1MarginTop":"0px","entryHeaderPaddingTop":"20.52px","stripHeight":98.40625,"heartRowGap":null,"heartRows":[]}
PASS /accessibility/ 320 light deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(122, 82, 10)","width":2,"wanted":"#7A520A"}
PASS /accessibility/ 320 light deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /accessibility/ 320 light deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(122, 82, 10)","width":3,"wanted":"#7A520A"}
PASS /accessibility/ 320 dark no side-scroll
PASS /accessibility/ 320 dark expected FAQ count and one native button per question items=0 buttons=0
PASS /accessibility/ 320 dark disclosure ARIA, unique controls and no tab roles
PASS /accessibility/ 320 dark distinct footer names and scroll button role
PASS /accessibility/ 320 dark résumé links announce new tab
PASS /accessibility/ 320 dark one H1 and all internal jump targets exist
PASS /accessibility/ 320 dark FAQ normal/active icons point down/up
PASS /accessibility/ 320 dark RSD/oEmbed/shortlink discovery absent
PASS /accessibility/ 320 dark booking landmark appears only on listed pages
PASS /accessibility/ 320 dark strip keeps opening/individual dates exactly once and Corporate path
PASS /accessibility/ 320 dark phone strip is shorter than desktop
PASS /accessibility/ 320 dark deep-accent keyboard focus: shared button/menu {"visible":true,"color":"rgb(226, 184, 102)","width":2,"wanted":"#E2B866"}
PASS /accessibility/ 320 dark deep-accent keyboard focus: jump link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /accessibility/ 320 dark deep-accent keyboard focus: booking-strip link {"visible":true,"color":"rgb(226, 184, 102)","width":3,"wanted":"#E2B866"}
PASS /accessibility/ scroll-to-top Space works
PASS /accessibility/ no browser errors
INFO Bundle C page done /accessibility/ ms=2533
PASS 404 includes four navigation links and search form {"navs":1,"links":[["Home","/"],["Services","/services/"],["Corporate & Group Massage","/corporate-massage-portland/"],["Contact","/contact/"]],"search":true,"searchAdded":0,"bylines":0,"viewPages":0}
PASS search has its own form, no bylines, and View page labels {"navs":0,"links":[],"search":true,"searchAdded":1,"bylines":0,"viewPages":10}
FAIL security.txt approved mailbox, future expiry, canonical and plain-text MIME {"status":404,"type":"text/html"}
PASS all 23 Spectra and three Corporate converted headings matched baseline 26
PASS all four responsive map heights checked 4
PASS all 49 FAQ wrappers measured at three widths 147
INFO FAQ_GEOMETRY total=147 expected=147
Bundle C minimum measured Chair panel contrast: 9.133:1
Bundle C: 1248 assertions, 1 failures
```

**RESULT: FAIL** - check-bundle-c.mjs failed (exit 1)

_finished 2026-11-01 18:06:51 UTC_
