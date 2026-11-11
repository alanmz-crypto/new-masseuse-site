# Saved state: Peek navigation, staging39 snippet #1369 (2026-11-08)

The known-good design before the next (larger) redesign. Ryan asked for a state that is easy to go back to.

- **What it is:** stacked index boxes with a scrollable summary (the page's own sentences), the title-row strip, the index icon whose fanned names
  have the closed boxes' form, and a 2-second hold that keeps the names open.
- **Code:** `peek-nav.php` and `check-peek-nav.mjs` in this folder are byte copies of commit `681b683` on `claude/wizardly-bell-xu0rta`
  (the same commit is also in the branch history, which is never force-pushed). Browser check on staging39: **428 passed, 0 failed**
  (`ui-peek-nav-s39-30-1`).
- **Server:** snippet **#1369** on staging39; its before-snapshot is `$HOME/private-backups/peek-nav-before-20261108-180339-b42020e0.json`;
  full staging39 database export right after this save: `private-backups/staging39-manual-20261108-182958.sql` (4,395,050 bytes), verified encrypted off-site copy in the draft release `offsite-2026-11` (report `backup-s39-save-1369-1`).

## How to go back to this state

1. Code: `cp saved/20261108-1369/peek-nav.php peek-nav.php && cp saved/20261108-1369/check-peek-nav.mjs check-peek-nav.mjs`
   (from `scripts/work-with-me-employer/link-ideas/`), commit and push.
2. staging39: roll back whatever snippet is current with **its own** before-snapshot (the newest "Undo (current)" line in the notes), then
   `request.sh rehearse --installer link-ideas/install-peek-nav.php --source staging39`, then
   `request.sh run --target staging39 --script link-ideas/push-peek-nav.sh --args "--apply"`, then the browser check
   (`request.sh run --target staging39 --script link-ideas/check-peek-nav.mjs --args "https://staging39.thaisolitude.com"`): expect 428/0.
3. If the database itself was changed in other ways: restore the export named in the notes (a server restore; ask Ryan first).

Do not edit the files in this folder.
