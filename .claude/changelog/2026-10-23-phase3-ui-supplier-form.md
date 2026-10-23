# 2026-10-23-phase3-ui-supplier-form

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 14:20:32 | Edit | app/src/Http/Admin/DivisionController.php |
| 14:20:33 | Edit | app/src/Http/Admin/DivisionController.php |
| 14:20:34 | Edit | app/src/Http/Admin/Controller.php |

## Summary
(The DivisionController/Controller rows in the auto table above came from another session that shares `.current-slug`. This task's files: `SupplierController.php`, `Controller.php` (ownHeader), `suppliers/edit.php`, `admin.css`.)
- **What changed:** `/admin/suppliers/new` and `/admin/suppliers/{id}` rebuilt in the messages/group-edit style. Header with the logo (or a factory icon), back link, name + "featured" star, country / page name / partnership, and buttons for website, products and publish. 4 stat cards on edit: products (and how many list this supplier as primary), visibility, featured (X / 6), about text filled in (X / 4 languages). Form boxes: details (name, country + website side by side, slug), about text as language tabs, logo (white and dark previews or an empty state, upload, white-logo switch, remove). A sticky settings panel holds the visible/featured switches (with the featured count used), the optional partnership and since-year fields, page/products/published info, and save/cancel/preview. The linked-products list uses ibx rows with a "primary" star, a set-primary button and ✕ remove. The add-products picker has a search in its header and a footer button. The delete bar matches groups. `SupplierController::form` now passes `saved` and `featuredOthers`, and `suppliers/edit` is in the ownHeader list. CSS: new `.spe-*` section, which reuses `.gpe-*`, `.dv-*`, `.sp-star`.
- **Why:** match the redesigned messages and groups pages.
- **Status:** done. php -l clean. Rendered through the real layout with made-up data (new, edit, edit with errors), because the local DB has 0 suppliers, and screenshotted in headless Chrome at 1440 and at 390 in an iframe: no horizontal scroll. PHPUnit 89/89 (old deprecation). Routes and field names unchanged. Not checked logged-in in a real browser.
- **Next:** look at it logged in, then add a real supplier and try uploading a logo.
