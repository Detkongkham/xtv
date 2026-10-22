# 2026-10-22-phase1-b7-admin-layout

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 16:34:49 | Write | app/templates/admin/layout.php |
| 16:34:50 | Write | app/templates/admin/auth/login.php |
| 16:34:51 | Write | app/templates/admin/errors/standalone.php |
| 16:34:52 | Write | app/templates/admin/errors/forbidden.php |
| 16:34:53 | Write | app/templates/admin/errors/not-found.php |
| 16:34:54 | Write | app/templates/admin/dashboard/index.php |

## Summary
- **What changed:** templates/admin/layout.php (sidebar, topbar, flash, new-message badge), auth/login, errors/*, dashboard/index (counts, 7/30 days, latest new, mail-error warning), public/admin/assets/admin.css (design.md tokens, mobile drawer < 1024 px, table → cards < 768 px), admin.js (menu toggle). Fixed filter grid overflow at 390 px (minmax(0,1fr), .field min-width 0).
- **Why:** P1-B7.
- **Status:** done — screenshots checked desktop 1440 + mobile 390; no horizontal overflow after fix.
- **Next:** —
