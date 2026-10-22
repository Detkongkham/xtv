# 2026-10-22-phase2-c6-divisions-groups

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 22:39:41 | Write | app/templates/admin/partials/lang-fields.php |
| 22:39:42 | Write | app/templates/admin/partials/icon-picker.php |
| 22:39:43 | Write | app/templates/admin/partials/order-buttons.php |
| 22:39:44 | Write | app/src/Repository/SortOrder.php |
| 22:40:13 | Edit | app/src/Publish/PageList.php |
| 22:40:14 | Write | app/src/Repository/DivisionRepository.php |
| 22:40:15 | Write | app/src/Repository/GroupRepository.php |
| 22:40:52 | Edit | app/src/Http/Admin/Controller.php |
| 22:40:53 | Write | app/src/Http/Admin/DivisionController.php |
| 22:41:16 | Write | app/src/Http/Admin/GroupController.php |
| 22:42:02 | Write | app/templates/admin/divisions/index.php |
| 22:42:03 | Write | app/templates/admin/divisions/edit.php |
| 22:42:04 | Write | app/templates/admin/groups/index.php |
| 22:42:06 | Write | app/templates/admin/groups/edit.php |

## Summary
- **What changed:** `/admin/divisions` and `/admin/groups` (list with counts, visible switch, ↑↓ order — groups within their division; form with 4-language tabs, icon picker, picture via media picker for divisions; delete only when empty). `DivisionRepository`, `GroupRepository`, shared `SortOrder` (suppliers use it too). Reusable admin partials: `lang-fields` (tabs by admin.js, stacked without JS, dot on tabs with errors/empty th-zh, required field in a hidden tab opens it), `icon-picker`, `order-buttons`; `Controller::translatedInput()/direction()`. Division slug = page name: validated against `PageList::RESERVED`; renaming/deleting a published division adds a redirect. Group code (anchor) generated once from the English name and then fixed. Site: home hero and Solutions buttons fall back to products.html when their division is hidden or renamed.
- **Why:** P2-C6.
- **Status:** done — tested through the admin: validation (reserved slug, missing English), slug rename → redirect page, Thai name, picture from library and by upload, new division + 2 groups + reorder, delete refused while groups exist, publish 280 pages in 4 languages with menus/anchors; then everything restored through the admin and the site compared byte-identical with the snapshot. Screenshots at 1440 px (found and fixed: language panels ignored `hidden`). PHPUnit 45/45. Test user, uploads and redirects removed; publish_log.user_id set to NULL on all rows (only test users had published from the admin).
- **Next:** C7 products (specs, featured, copy, supplier field, slug redirects).
