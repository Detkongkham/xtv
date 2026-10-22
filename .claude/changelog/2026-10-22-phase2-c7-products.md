# 2026-10-22-phase2-c7-products

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 22:48:37 | Write | app/src/Repository/ProductRepository.php |
| 22:49:52 | Write | app/src/Http/Admin/ProductController.php |
| 22:50:21 | Write | app/templates/admin/products/index.php |
| 22:50:59 | Write | app/templates/admin/products/edit.php |

## Summary
- **What changed:** `/admin/products` (filters: search, division, group, "no supplier / no picture / hidden / featured"; list with picture, suppliers, featured switch max 8 and picture required, visible switch, ↑↓ within a group) and form (name, slug, group, variants note, picture via media picker, summary/body ×4 optional, SEO ×4 optional, spec rows with add/move/remove and th/zh behind a toggle, suppliers with a primary choice, copy as a hidden product, delete). `ProductRepository` (save product + specs + suppliers in one transaction). Renamed/deleted published products get redirects (to the new page / the division). Site: product body shown as paragraphs in "Application" when filled. Optional translated fields in `translatedInput()`/lang-fields; amber dot only when English is filled but th/zh empty. Dashboard and supplier list link to "products without supplier". `bin/build-site.php` now writes sitemap.xml too.
- **Why:** P2-C7.
- **Status:** done — tested through the admin: validation (half-filled spec row, slug taken, featured without picture, more than 8 featured), save with 2 specs + Thai value + 2 suppliers with primary + body + slug change → page shows spec rows, "supplied by" in order, zh falls back to en, old URL redirects; new product with uploaded picture; reorder; copy (hidden, specs and suppliers copied); delete unpublished copy (no redirect) and published product (redirect to division); new product appears in division page, product page, sitemap.html and sitemap.xml in 4 languages. All test data removed and the site compared byte-identical with the snapshot. Screenshots at 1440 px. PHPUnit 45/45. The dev-server attempts that failed were my test setup (wrong working dir, zsh `GID`), not the app.
- **Next:** C8 news (simple editor + HtmlSanitizer), C9 downloads, C10 dashboard, C11 retire Python; zh home Lighthouse.
