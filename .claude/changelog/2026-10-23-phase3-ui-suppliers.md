# 2026-10-23-phase3-ui-suppliers

(Written by hand: the `.current-slug` file was left on `phase3-ui-products`, which another session was using, so the auto table for these edits went there.)

## Summary
- **What changed:** `/admin/suppliers` redesigned on the inbox pattern, like divisions: own header (publish, add), 5 stat cards (total; featured X/6, visible, hidden as filters; products without a supplier, or linked products when there are none), all/featured/visible/hidden tabs and search by name, slug or country (`?show=`, `?q=`, `/` focuses search), list rows with position + ↑↓ (hidden while filtered), logo box (dark variant kept), name + "featured" star, country/partnership/since-year/product count meta, tags for "no products" and "no logo", labelled featured/visible switches, website, products and edit buttons. The old red flash about products without a supplier became an `ibx-alert`. Files: `SupplierController::index` (filters), `suppliers/index.php`, `Controller::page` (ownHeader), `admin.css` (`.sp*`, `.ibx-row--accent`).
- **Why:** match the new messages/divisions look.
- **Status:** done. Checked with a scratch render through the real layout in headless Chrome at 1440 and 390 (iframe) using made-up rows, since the local database has 0 suppliers. No horizontal scroll. Filters and search checked on that render. PHPUnit 89/89 (old deprecation). Toggle/move routes unchanged.
- **Next:** the supplier edit page (`suppliers/edit.php`) still uses the old look.
