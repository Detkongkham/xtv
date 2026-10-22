# 2026-10-22-phase2-c1-php-templates

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 21:47:16 | Write | build/export_lang.py |
| 21:48:03 | Write | app/database/migrations/002_content.sql |
| 21:48:24 | Write | build/export_seed.py |
| 21:48:58 | Write | app/src/Core/Lang.php |
| 21:48:59 | Write | app/src/Publish/SiteData.php |
| 21:49:28 | Write | app/src/Publish/SiteView.php |
| 21:49:56 | Write | app/templates/site/layout.php |
| 21:50:36 | Write | app/templates/site/partials/header.php |
| 21:50:37 | Write | app/templates/site/partials/drawer.php |
| 21:51:09 | Write | app/templates/site/partials/footer.php |
| 21:51:10 | Write | app/templates/site/partials/breadcrumb.php |
| 21:51:11 | Write | app/templates/site/partials/page-head.php |
| 21:51:12 | Write | app/templates/site/partials/section-head.php |
| 21:51:13 | Write | app/templates/site/partials/cta.php |
| 21:51:14 | Write | app/templates/site/partials/location.php |
| 21:51:15 | Write | app/templates/site/partials/contact-aside.php |
| 21:51:51 | Write | app/templates/site/partials/contact-form.php |
| 21:51:52 | Write | app/templates/site/partials/cards/product.php |
| 21:51:53 | Write | app/templates/site/partials/cards/divisions.php |
| 21:51:54 | Write | app/templates/site/partials/cards/news.php |
| 21:51:55 | Write | app/templates/site/partials/cards/text.php |
| 21:51:56 | Write | app/templates/site/partials/steps.php |
| 21:52:24 | Edit | app/src/Publish/SiteView.php |
| 21:52:25 | Write | app/templates/site/partials/cards/logo-tile.php |
| 21:52:26 | Write | app/templates/site/partials/cards/supplier.php |
| 21:52:27 | Write | app/templates/site/partials/cards/supplier-tile.php |
| 21:52:28 | Write | app/templates/site/partials/supplied-by.php |
| 21:52:58 | Edit | app/src/Publish/SiteView.php |
| 21:52:59 | Edit | app/src/Publish/SiteData.php |
| 21:53:00 | Write | app/templates/site/partials/chips.php |
| 21:53:28 | Write | app/templates/site/pages/home.php |
| 21:53:54 | Write | app/templates/site/pages/about.php |
| 21:53:55 | Write | app/templates/site/pages/vision.php |
| 21:53:56 | Write | app/templates/site/pages/products.php |
| 21:54:11 | Write | app/templates/site/pages/division.php |
| 21:54:39 | Write | app/templates/site/pages/product.php |
| 21:55:10 | Write | app/templates/site/pages/suppliers.php |
| 21:55:11 | Write | app/templates/site/pages/supplier.php |
| 21:55:34 | Write | app/templates/site/pages/solutions.php |
| 21:55:35 | Write | app/templates/site/pages/services.php |
| 21:55:48 | Write | app/templates/site/pages/news.php |
| 21:55:49 | Write | app/templates/site/pages/news-article.php |
| 21:56:27 | Write | app/templates/site/pages/contact.php |
| 21:56:28 | Write | app/templates/site/pages/downloads.php |
| 21:56:29 | Write | app/templates/site/pages/faq.php |
| 21:56:30 | Write | app/templates/site/pages/legal.php |
| 21:56:31 | Write | app/templates/site/pages/sitemap.php |
| 21:56:32 | Write | app/templates/site/pages/404.php |
| 21:56:55 | Write | app/src/Publish/PageList.php |
| 21:56:56 | Write | app/src/Publish/SiteBuilder.php |
| 21:56:57 | Write | app/bin/build-site.php |
| 21:57:18 | Write | build/compare_html.py |
| 21:59:55 | Edit | app/src/Publish/SiteView.php |
| 22:00:10 | Write | app/bin/seed.php |
| 22:00:30 | Write | app/tests/LangTest.php |

## Summary
- **What changed:**
  - P2-C1: `app/lang/{lo,en,th,zh}.php` (256 UI keys each) + `countries.php`, made by `build/export_lang.py`; `App\Core\Lang` (t, f with `{}`/`{name}`, th/zh → en fallback); `App\Publish\SiteData`, `SiteView`, `PageList`, `SiteBuilder`; templates in `app/templates/site/` (layout, 20 partials, 17 pages); `bin/build-site.php`.
  - P2-C2: migration `002_content.sql` (media, divisions, groups, products, suppliers, product_suppliers, specs, news, downloads, redirects, publish_log); `build/export_seed.py` → `app/database/seed/seed.json` (51 media, 5 divisions, 12 groups, 60 products, 2 news, 4 settings); `bin/seed.php`.
  - P2-C3: `build/compare_html.py`.
  - Tests: `tests/LangTest.php`. Docs: 04 (media path, news link_url rule), 05 (template list, build-site.php), 06 (C3 ticked), app/README.
- **Why:** Phase 2 step 1–3: the site is rendered by PHP from the database instead of build.py.
- **Status:** done — migrated + seeded local MySQL (backup taken first), rendered 276 pages from the DB in 0.18 s, compare vs Python: 0 different (246 byte-identical, 32 differ only by `&amp;` escaping). Preview build with sample suppliers: 312 pages, 0 different. PHPUnit 36/36.
- **Next:** P2-C4 Publisher (needs a decision on the Chinese font subset), then C12 suppliers admin, C5–C9.
