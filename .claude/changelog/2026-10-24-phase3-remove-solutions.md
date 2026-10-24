# 2026-10-24-phase3-remove-solutions

## Summary
- **What changed:** removed the Solutions page from the whole system: `templates/site/pages/solutions.php` (deleted); Solutions tab section on the home page; `SiteView::SOLUTIONS`; `solutions` in `PageList` (RESERVED, SEO, page list), `HeroController::FIXED`, `SeoController::PAGES`, the page list in `admin/theme/index.php`; links in header, drawer, footer and sitemap page; lang keys `solutions`, `home_sol_p`, `sol_*` in all 4 languages; `.tabs` CSS and the tabs script in `main.js` (only Solutions used them); `SiteBuildTest` page counts (−1 page × 4 languages); docs 02, 03, 05, 06.
- **Why:** the page is not needed.
- **Status:** done. PHPUnit 105 tests OK (1 deprecation was there before). No database rows referred to solutions. The old `public/*/solutions.html` files stay live until the next publish, which replaces the language folders.
- **Next:** publish once from /admin so the old solutions.html files go away.
