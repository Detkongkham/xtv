# 2026-10-23-phase3-ui-division-edit

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 14:20:23 | Write | app/templates/admin/groups/edit.php |

## Summary
- **What changed:** `app/templates/admin/divisions/edit.php` redesigned like the inbox / group edit page: header with icon tile, back link, slug and tools (groups, preview, publish); 4 stat cards (groups, products, visibility, languages); form boxes (names, picture, icon, the division's groups with links); sticky settings panel (page name with `.html` suffix, visibility switch, publish state, menu position, save/cancel); delete box at the bottom. `DivisionController::form()` now passes `saved` (counts + position) and `groups`. `divisions/edit` added to `ownHeader` in `Controller.php`. New `.de` CSS block at the end of `admin.css` (reuses `gpe-*`).
- **Why:** match the new admin UI (messages / divisions / groups).
- **Status:** done — `php -l` passes; rendered /admin/divisions/1 and /admin/divisions/new with a test session and checked headless-Chrome screenshots at 1440px. Saving the form was not submitted end to end (field names unchanged).
- **Next:** —
