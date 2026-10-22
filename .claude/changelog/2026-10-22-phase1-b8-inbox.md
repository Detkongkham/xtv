# 2026-10-22-phase1-b8-inbox

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 16:35:52 | Write | app/templates/admin/messages/index.php |
| 16:35:53 | Write | app/templates/admin/messages/show.php |
| 16:35:54 | Write | app/templates/admin/users/index.php |
| 16:35:55 | Write | app/templates/admin/users/edit.php |
| 16:35:56 | Write | app/templates/admin/users/account.php |
| 16:36:44 | Write | public/admin/assets/admin.css |
| 16:36:45 | Write | public/admin/assets/admin.js |
| 16:37:13 | Write | app/bin/create-admin.php |
| 16:37:14 | Write | app/bin/cleanup.php |
| 16:37:15 | Write | app/bin/backup.php |
| 16:37:16 | Write | app/bin/dev-router.php |
| 16:37:21 | Edit | app/bin/backup.php |

## Summary
- **What changed:** templates/admin/messages/index (search, status/subject/date filters, 25 per page, spam hidden by default) and show (details, call/email/WhatsApp buttons with 856 prefix, status + assign, internal notes; first note moves new → in_progress), CSV export with BOM and formula-injection guard.
- **Why:** P1-B8 per 03 §2.3.
- **Status:** done — curl e2e: search + mail-error tag, filters, tel/WhatsApp links, status/assign saved, note saved and HTML-escaped, bad CSRF ignored, CSV has BOM + rows.
- **Next:** CSV export is logged to storage/logs; activity_log comes in phase 3.
