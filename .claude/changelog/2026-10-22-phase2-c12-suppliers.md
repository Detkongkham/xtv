# 2026-10-22-phase2-c12-suppliers

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 22:14:29 | Write | app/src/Core/Countries.php |
| 22:14:30 | Write | app/src/Service/Slugger.php |
| 22:14:31 | Write | app/src/Service/MediaService.php |
| 22:15:00 | Edit | app/src/Core/Request.php |
| 22:15:01 | Write | app/src/Repository/SupplierRepository.php |
| 22:15:16 | Write | app/src/Repository/RedirectRepository.php |
| 22:15:17 | Edit | app/src/Http/Admin/Controller.php |
| 22:15:18 | Edit | app/src/Http/Admin/Controller.php |
| 22:15:56 | Write | app/src/Http/Admin/SupplierController.php |
| 22:16:21 | Write | app/templates/admin/suppliers/index.php |
| 22:17:06 | Write | app/templates/admin/suppliers/edit.php |
| 22:18:49 | Edit | app/src/Service/MediaService.php |
| 22:18:50 | Edit | app/src/Service/MediaService.php |
| 22:18:51 | Write | app/tests/MediaSluggerTest.php |
| 22:18:59 | Edit | app/src/Service/Slugger.php |

## Summary
- **What changed:** `/admin/suppliers` (list with logo, country, product count, featured switch max 6, visible switch, ↑↓ order; form with slug, country from ICU, website, about ×4, logo PNG/WebP ≥400px with white/dark preview, partnership, since year; link products with search + multi-select, remove, make primary; delete). `SupplierRepository`, `RedirectRepository` (slug change / delete of a published supplier → redirect), `MediaService::storeLogo` (finfo + GD re-encode, scale to 480px), `Slugger`, `Countries` (lang file + intl), `Request::postIds/file`, `Controller::contentChanged/isPublished`, dashboard "products without supplier", admin CSS/JS (switch, picker filter). Tests: `MediaSluggerTest`.
- **Why:** P2-C12 — client priority: show which products XTK sells from which supplier.
- **Status:** done — end-to-end locally with 2 test suppliers: PHP disguised as image and a 200px logo rejected; logo scaled 600→480 with transparency; products linked, primary switch, reorder, featured; publish → 288 pages, mega menu / suppliers.html stats and filter / supplier page / "supplied by" / card names correct in 4 languages, sitemap; slug change and delete produce redirects. Test data removed, site republished (0 diff vs Python). PHPUnit 44/44. Admin pages screenshot-checked at 1440px.
- **Next:** product form supplier field (with C7), certificate upload (with C5 media library). Real supplier data from the client (08 §7) + regenerate the Chinese font subset when it arrives.
