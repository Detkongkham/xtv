# 2026-10-23-phase3-ui-dashboard

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 13:44:16 | Write | app/src/Repository/DashboardRepository.php |
| 13:44:30 | Write | app/src/Http/Admin/DashboardController.php |
| 13:46:39 | Write | app/templates/admin/dashboard/index.php |

## Summary
- **What changed:**
  - `app/templates/admin/dashboard/index.php`: redesigned after the reference dashboard the user sent: header card (greeting, date, 7/14/30-day switch, refresh), "needs action" chips, gradient hero KPI (messages today + subject split), 3 status KPIs, 8 period KPIs with change vs the previous period and sparklines, trend chart (messages / edits / spam, toggles), latest messages feed, publish state, backup, translation gaps
  - `app/src/Repository/DashboardRepository.php` (new): per-day counts, period message totals by subject/language, publishes, oldest new message, site content counts
  - `DashboardController`: `?days=7|14|30` (default 14), passes the new numbers
  - `layout.php`: no page-title h1 on the dashboard (it has its own)
  - `admin.css` / `admin.js`: dashboard v2 styles; chart line toggles; reload every minute while visible and idle
- **Why:** user asked for the dashboard to match the design they pasted. That design was for a salon system, so the layout and styling are copied and the numbers are XTK's own (messages, publishing, content, translations, backups).
- **Status:** done. Admin CSP blocks `style=""`, so bars are SVG and colours are classes. Verified: PHPUnit 89/89 (old deprecation); headless Chrome at 390/768/1024/1280/1440 with 90 temporary test messages (removed afterwards), no horizontal scroll; `?days=abc` falls back to 14.
- **Next:** the pasted HTML was cut off after the trend chart header, so the parts below it follow the same style but are not copied from the reference.
