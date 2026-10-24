---
name: pagespeed-vs-local-lighthouse
description: "Why Google PageSpeed mobile stays ~77-79 while local Lighthouse says 99 (2026-10-17); late paint on Google's machine plus 309 KB of fonts charged to FCP"
metadata:
  node_type: memory
  type: project
  originSessionId: b7972be6-8d16-4d78-b186-706afcc661a9
  modified: 2026-10-17T18:16:06.823Z
---

Ryan's PageSpeed reports AFTER the two speed snippets (live #1225/#1226, captured 12:44 CDT = 17:44 UTC; staging31 12:48 CDT) still score mobile 77 / 79 (FCP 3.3 s, LCP 4.2 s, SI 4.8 s). Desktop is 99. My local Lighthouse (99, FCP 1.1 s) does NOT predict Google's mobile number; my earlier "matched Google 77 vs 76" was a coincidence of the pre-fix state.

**Why:** the full Google report JSON is embedded in the permalink HTML (`"{\"fetchTime\"...` string literals, one mobile + one desktop; `~/.cache/ts-perf/psi-json2.mjs` + `psi-decode.py`). In Google's mobile run all network finishes by ~0.6 s but nothing paints until 1.3 s (before fixes) / 2.4 s (after); main thread idle, raster tasks only ms; the Google machine's benchmarkIndex was 1048 then 438 (slower = later paint). Lantern then counts everything finished before that late observed FCP, so the 3 font files (Newsreader normal 132 KB + italic 147 KB with the opsz axis, Hanken 35 KB = 309 KB, VeryHigh priority) are charged against simulated Slow-4G. Offline proof: re-scoring my recorded slow-paint runs gave FCP 3.3-3.4 s / score 78-80 (= Google); deleting the three font requests from the same recording gave FCP 1.4-1.8 s, LCP 1.8-2.1 s, score 86-87. Speed Index stays ~3-4.5 s because the paint itself is late. I found no site-side cause of the late paint (reproducible locally only under 8x CPU throttle, also with fonts blocked).

**How to apply:** don't promise Google's mobile score from local runs; to reproduce Google, gather with `--throttling-method=devtools --throttling.cpuSlowdownMultiplier=8` (rtt/throughput 0) via `-G`, patch artifacts.json settings to simulate, re-score with `-A` (scripts in `~/.cache/ts-lighthouse`: gather8.sh, gs-run.sh, obs.sh). The real lever is font weight: dropping the opsz axis gives Newsreader 58 KB + 65 KB (fonts 309 -> ~158 KB), but it changes letterforms at display sizes, so it needs Ryan's OK and a staging31 pixel check first. Real visitors paint in ~0.5 s locally; the lab score is not the visitor experience. Ryan (2026-10-17) asked only for a historical record saying Google's score doesn't match the likely reality (done: AGENTS.md Speed bullet, commit bef9ac9 on PR #17's branch), so no further speed work is planned; Google's real-user section says "No Data", revisit when it fills in. Related: [[staging31-backend-audit]].
