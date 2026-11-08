# Site op `ui-rail-ideas-staging39-20261108`

| | |
|---|---|
| operation | `run` on `staging39` |
| script | `link-ideas/check-rail-ideas.mjs` `https://staging39.thaisolitude.com` |
| lane | ops (site-ops.yml) |
| commit | `dc587d1d77be9bbab4c6d4aa6249e3abe68197ad` on `claude/wizardly-bell-xu0rta` |
| run | https://github.com/alanmz-crypto/new-masseuse-site/actions/runs/42438900522 |
| started | 2026-11-08 02:31:31 UTC |

## check-rail-ideas.mjs https://staging39.thaisolitude.com(S_TARGET=staging39): PASS

```text
PASS phone/light script ran
PASS phone/light rail sits on the right edge in the thumb zone (lower part of the screen)  [{"top":199.1875,"bottom":571.046875,"right":386,"vh":664,"vw":390}]
PASS phone/light 1: sliding shows the section under the thumb and what it covers  [{"on":true,"pinned":false,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS phone/light 1: the page does not move while browsing
PASS phone/light 1: lifting keeps the card with Go/Stay, no jump  [{"on":true,"pinned":true,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS phone/light 1: Stay here keeps the place
PASS phone/light 1: Go there jumps
PASS phone/light 1: a Back button names where you were
PASS phone/light 1: Back returns to exactly where you were  [986 -> 986]
PASS phone/light 2: lifting without pulling stays put
PASS phone/light 2: pulling in arms the jump (card turns green)  [{"on":true,"pinned":false,"armed":true,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS phone/light 2: still no movement before letting go
PASS phone/light 2: letting go when armed jumps
PASS phone/light 2: a Back button names where you were
PASS phone/light 2: Back returns to exactly where you were  [986 -> 986]
PASS phone/light 3: sliding shows a preview of the section  [{"on":true,"pinned":false,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":true,"inView":true}]
PASS phone/light 3: lifting keeps the preview, no jump
PASS phone/light 3: tapping elsewhere closes it and keeps the place
PASS phone/light 3: tapping the preview jumps
PASS phone/light 3: a Back button names where you were
PASS phone/light 3: Back returns to exactly where you were  [986 -> 986]
PASS phone/light 4: lifting before the ring fills stays put
PASS phone/light 4: holding still until the ring fills jumps (thumb still down)
PASS phone/light 4: a Back button names where you were
PASS phone/light 4: Back returns to exactly where you were  [986 -> 986]
PASS phone/light 5: the rail is replaced by a short tab showing progress
PASS phone/light 5: tapping the tab opens a drawer with every section, description and ticks  [✓|What it is|27 ; 2|Common reasons|26 ; 3|Who can benefit|41 ; 4|Questions|47 ; 5|What to expect|48 ; 6|What Research Says|42]
PASS phone/light 5: scrolling the drawer does not move the page
PASS phone/light 5: swiping the drawer right closes it, place kept
PASS phone/light 5: swiping the tab left opens the drawer
PASS phone/light 5: tapping a section closes the drawer and jumps
PASS phone/light 5: a Back button names where you were
PASS phone/light 5: Back returns to exactly where you were  [1023 -> 1023]
PASS phone/light no sideways page scroll
PASS phone/light no JavaScript errors
PASS phone/dark script ran
PASS phone/dark site dark theme active
PASS phone/dark rail sits on the right edge in the thumb zone (lower part of the screen)  [{"top":199.1875,"bottom":571.046875,"right":386,"vh":664,"vw":390}]
PASS phone/dark 1: sliding shows the section under the thumb and what it covers  [{"on":true,"pinned":false,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS phone/dark 1: the page does not move while browsing
PASS phone/dark 1: lifting keeps the card with Go/Stay, no jump  [{"on":true,"pinned":true,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS phone/dark 1: Stay here keeps the place
PASS phone/dark 1: Go there jumps
PASS phone/dark 1: a Back button names where you were
PASS phone/dark 1: Back returns to exactly where you were  [986 -> 986]
PASS phone/dark 2: lifting without pulling stays put
PASS phone/dark 2: pulling in arms the jump (card turns green)  [{"on":true,"pinned":false,"armed":true,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS phone/dark 2: still no movement before letting go
PASS phone/dark 2: letting go when armed jumps
PASS phone/dark 2: a Back button names where you were
PASS phone/dark 2: Back returns to exactly where you were  [986 -> 986]
PASS phone/dark 3: sliding shows a preview of the section  [{"on":true,"pinned":false,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":true,"inView":true}]
PASS phone/dark 3: lifting keeps the preview, no jump
PASS phone/dark 3: tapping elsewhere closes it and keeps the place
PASS phone/dark 3: tapping the preview jumps
PASS phone/dark 3: a Back button names where you were
PASS phone/dark 3: Back returns to exactly where you were  [986 -> 986]
PASS phone/dark 4: lifting before the ring fills stays put
PASS phone/dark 4: holding still until the ring fills jumps (thumb still down)
PASS phone/dark 4: a Back button names where you were
PASS phone/dark 4: Back returns to exactly where you were  [986 -> 986]
PASS phone/dark 5: the rail is replaced by a short tab showing progress
PASS phone/dark 5: tapping the tab opens a drawer with every section, description and ticks  [✓|What it is|27 ; 2|Common reasons|26 ; 3|Who can benefit|41 ; 4|Questions|47 ; 5|What to expect|48 ; 6|What Research Says|42]
PASS phone/dark 5: scrolling the drawer does not move the page
PASS phone/dark 5: swiping the drawer right closes it, place kept
PASS phone/dark 5: swiping the tab left opens the drawer
PASS phone/dark 5: tapping a section closes the drawer and jumps
PASS phone/dark 5: a Back button names where you were
PASS phone/dark 5: Back returns to exactly where you were  [1023 -> 1023]
PASS phone/dark no sideways page scroll
PASS phone/dark no JavaScript errors
PASS tablet/light script ran
PASS tablet/light rail sits on the right edge in the thumb zone (lower part of the screen)  [{"top":307.1875,"bottom":880.640625,"right":764,"vh":1024,"vw":768}]
PASS tablet/light 1: sliding shows the section under the thumb and what it covers  [{"on":true,"pinned":false,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS tablet/light 1: the page does not move while browsing
PASS tablet/light 1: lifting keeps the card with Go/Stay, no jump  [{"on":true,"pinned":true,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS tablet/light 1: Stay here keeps the place
PASS tablet/light 1: Go there jumps
PASS tablet/light 1: a Back button names where you were
PASS tablet/light 1: Back returns to exactly where you were  [906 -> 906]
PASS tablet/light 2: lifting without pulling stays put
PASS tablet/light 2: pulling in arms the jump (card turns green)  [{"on":true,"pinned":false,"armed":true,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS tablet/light 2: still no movement before letting go
PASS tablet/light 2: letting go when armed jumps
PASS tablet/light 2: a Back button names where you were
PASS tablet/light 2: Back returns to exactly where you were  [906 -> 906]
PASS tablet/light 3: sliding shows a preview of the section  [{"on":true,"pinned":false,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":true,"inView":true}]
PASS tablet/light 3: lifting keeps the preview, no jump
PASS tablet/light 3: tapping elsewhere closes it and keeps the place
PASS tablet/light 3: tapping the preview jumps
PASS tablet/light 3: a Back button names where you were
PASS tablet/light 3: Back returns to exactly where you were  [906 -> 906]
PASS tablet/light 4: lifting before the ring fills stays put
PASS tablet/light 4: holding still until the ring fills jumps (thumb still down)
PASS tablet/light 4: a Back button names where you were
PASS tablet/light 4: Back returns to exactly where you were  [906 -> 906]
PASS tablet/light 5: the rail is replaced by a short tab showing progress
PASS tablet/light 5: tapping the tab opens a drawer with every section, description and ticks  [✓|What it is|27 ; 2|Common reasons|26 ; 3|Who can benefit|41 ; 4|Questions|47 ; 5|What to expect|48 ; 6|What Research Says|42]
PASS tablet/light 5: scrolling the drawer does not move the page
PASS tablet/light 5: swiping the drawer right closes it, place kept
PASS tablet/light 5: swiping the tab left opens the drawer
PASS tablet/light 5: tapping a section closes the drawer and jumps
PASS tablet/light 5: a Back button names where you were
PASS tablet/light 5: Back returns to exactly where you were  [925 -> 925]
PASS tablet/light no sideways page scroll
PASS tablet/light no JavaScript errors
PASS tablet/dark script ran
PASS tablet/dark site dark theme active
PASS tablet/dark rail sits on the right edge in the thumb zone (lower part of the screen)  [{"top":307.1875,"bottom":880.640625,"right":764,"vh":1024,"vw":768}]
PASS tablet/dark 1: sliding shows the section under the thumb and what it covers  [{"on":true,"pinned":false,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS tablet/dark 1: the page does not move while browsing
PASS tablet/dark 1: lifting keeps the card with Go/Stay, no jump  [{"on":true,"pinned":true,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS tablet/dark 1: Stay here keeps the place
PASS tablet/dark 1: Go there jumps
PASS tablet/dark 1: a Back button names where you were
PASS tablet/dark 1: Back returns to exactly where you were  [906 -> 906]
PASS tablet/dark 2: lifting without pulling stays put
PASS tablet/dark 2: pulling in arms the jump (card turns green)  [{"on":true,"pinned":false,"armed":true,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":false,"inView":true}]
PASS tablet/dark 2: still no movement before letting go
PASS tablet/dark 2: letting go when armed jumps
PASS tablet/dark 2: a Back button names where you were
PASS tablet/dark 2: Back returns to exactly where you were  [906 -> 906]
PASS tablet/dark 3: sliding shows a preview of the section  [{"on":true,"pinned":false,"armed":false,"name":"Questions","blurb":"Short answers to the questions people ask most.","shot":true,"inView":true}]
PASS tablet/dark 3: lifting keeps the preview, no jump
PASS tablet/dark 3: tapping elsewhere closes it and keeps the place
PASS tablet/dark 3: tapping the preview jumps
PASS tablet/dark 3: a Back button names where you were
PASS tablet/dark 3: Back returns to exactly where you were  [906 -> 906]
PASS tablet/dark 4: lifting before the ring fills stays put
PASS tablet/dark 4: holding still until the ring fills jumps (thumb still down)
PASS tablet/dark 4: a Back button names where you were
PASS tablet/dark 4: Back returns to exactly where you were  [906 -> 906]
PASS tablet/dark 5: the rail is replaced by a short tab showing progress
PASS tablet/dark 5: tapping the tab opens a drawer with every section, description and ticks  [✓|What it is|27 ; 2|Common reasons|26 ; 3|Who can benefit|41 ; 4|Questions|47 ; 5|What to expect|48 ; 6|What Research Says|42]
PASS tablet/dark 5: scrolling the drawer does not move the page
PASS tablet/dark 5: swiping the drawer right closes it, place kept
PASS tablet/dark 5: swiping the tab left opens the drawer
PASS tablet/dark 5: tapping a section closes the drawer and jumps
PASS tablet/dark 5: a Back button names where you were
PASS tablet/dark 5: Back returns to exactly where you were  [925 -> 925]
PASS tablet/dark no sideways page scroll
PASS tablet/dark no JavaScript errors

chromium: 142 passed, 0 failed
```

**RESULT: PASS** - check-rail-ideas.mjs succeeded

_finished 2026-11-08 02:34:22 UTC_
