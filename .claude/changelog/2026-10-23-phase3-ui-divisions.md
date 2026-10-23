# 2026-10-23-phase3-ui-divisions

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 14:10:29 | Edit | app/src/Http/Admin/DivisionController.php |
| 14:10:30 | Edit | app/src/Http/Admin/Controller.php |
| 14:10:32 | Edit | app/templates/admin/layout.php |
| 14:11:21 | Write | app/templates/admin/divisions/index.php |

## Summary
- **What changed:** `/admin/divisions` redesigned on the inbox pattern: own header (groups, publish, add), 5 stat cards (total, visible, hidden as filters; groups; products or "needs check" when a division lacks an image or groups), visible/hidden tabs and search (`?show=`, `?q=`, `/` focuses search), list rows with position + ↑↓, thumbnail with the division icon, name lo/en, slug/groups/products meta, warning tags, visibility switch, groups and edit buttons. Files: `DivisionController::index`, `divisions/index.php`, `Controller::page` (ownHeader), `layout.php` comment, `admin.css` (`.dv*`, `.tag--warn`).
- **Why:** match the new messages/dashboard look.
- **Status:** done — headless Chrome at 1440/1024/390 (iframe), no horizontal scroll; filters and search checked with curl; PHPUnit 89/89 (old deprecation). Order buttons hide while filtered (they move among all divisions). Toggle/move routes unchanged.
- **Next:** groups page could get the same treatment.
