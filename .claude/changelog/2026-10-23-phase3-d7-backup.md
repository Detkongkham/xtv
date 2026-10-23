# 2026-10-23-phase3-d7-backup

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 13:10:37 | Write | app/src/Service/Backup.php |

## Summary
- **What changed:**
  - `app/src/Service/Backup.php`: db (keeps 14), full = db.sql.gz + uploads/ + manifest.json in a tar.gz (keeps 4), preMigrate (keeps 5), restore (a .sql.gz, or a full backup into a database + an empty folder; live database/uploads only with force), latest, prune
  - `bin/backup.php` rewritten on it: `db` (default), `full`, `--list`, `--restore=… --db=… [--uploads=DIR] [--force]`
  - `bin/migrate.php`: backs up to `storage/backups/pre-migrate/` before applying new migrations to a database that has some (`--no-backup` skips)
  - dashboard (admins): latest database and full backup with size, warning when no database backup in 2 days
  - `tests/BackupTest.php`; 06-phases (P3-D7, restore criterion ticked), README
- **Why:** P3-D7, uploaded pictures and files were not backed up, only the database; a restore of everything must work.
- **Status:** done. Verified: `backup.php` and `backup.php full` (3.7 MB, 225 files); full restore into `xtk_restore_test` + a temporary folder: row counts of 10 tables equal, CHECKSUM TABLE of products/news/media equal, `diff -r` of uploads identical; refusals: live database, live uploads, non-empty folder, unknown mode; pre-migrate backup made by migrate.php with a temporary migration (removed); dashboard card checked with a temporary admin (removed with its log rows); test database dropped; PHPUnit 86/86.
- **Next:** phase S: cron (03:30 db, Sunday 04:00 full) and the weekly copy off the server. P3-D8 Turnstile only if spam gets through.
