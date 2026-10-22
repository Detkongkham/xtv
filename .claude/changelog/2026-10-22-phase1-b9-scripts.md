# 2026-10-22-phase1-b9-scripts

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 16:50:46 | Write | /private/tmp/claude-501/-Users-ta-xtv/4a6b6034-b777-4d10-b3c3-351d21422445/scratchpad/pw/form.mjs |

## Summary
- **What changed:** bin/cleanup.php (rate_limits > 1 d, login_attempts > 30 d, spam > 30 d, logs > 30 d), bin/backup.php (mysqldump via temp option file → gzip, keep 14; --restore=FILE --db=NAME, refuses live DB without --force).
- **Why:** P1-B9.
- **Status:** done — backup 2.5 KB written; restored into temp DB `xtk_restore_test` with matching row counts and intact Lao text, then dropped; restore into live DB refused; cleanup deleted only the seeded old rows.
- **Next:** Cron 03:00 / 03:30 in phase S.
