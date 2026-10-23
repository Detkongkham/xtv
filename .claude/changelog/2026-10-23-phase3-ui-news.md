# 2026-10-23-phase3-ui-news

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 14:17:58 | Write | app/templates/admin/news/index.php |

## Summary
- **What changed:** app/templates/admin/news/index.php (new list design, same as messages/suppliers: header, 5 stat cards, status tabs, search, category chips, rows with cover, status, category, page file or outside link, view/edit buttons); NewsController::index (filters status/category/show=nocover/q, `live` flag per row); admin.css (`.nw` block at the end).
- **Why:** the news list was an old table; it now matches the other redesigned admin pages.
- **Status:** done — template rendered with the real database for 4 filter combinations; phpunit 89 tests OK. Not checked in a browser (needs login).
- **Next:** the news edit form (news/edit.php) still uses the old layout.
