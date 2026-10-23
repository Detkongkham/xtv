# 2026-10-23-phase3-d5-preview

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 12:58:45 | Write | app/src/Publish/PagePreview.php |
| 12:59:12 | Write | app/src/Http/Admin/PreviewController.php |
| 12:59:57 | Write | app/tests/PagePreviewTest.php |
| 13:00:55 | Edit | docs/plan/06-phases.md |

## Summary
- **What changed:**
  - `PagePreview` (Publish): one page from the current data, draft news with a body included; links between pages and language links rewritten to `/admin/preview?page=…&lang=…`; assets keep `../`
  - `PreviewController` + route `GET /admin/preview`: login required, `X-Robots-Tag: noindex, nofollow`, `Cache-Control: no-store`, bar at the bottom (what it is, 4 languages, Publish, back to admin), 404 page `admin/preview/missing.php`
  - `SiteBuilder::renderPage()` (shared by build and preview), `PageList::name()`, `SiteData::draftNews()`, layout `$bar` (sample-data note unchanged); `.preview-bar a` in style.css
  - "ເບິ່ງຕົວຢ່າງ" button (`admin/partials/preview-link.php`, new tab) in product, news, division, supplier, hero, FAQ, SEO forms and the publish page
  - `tests/PagePreviewTest.php`; 06-phases
- **Why:** P3-D5, staff see a page with their changes before publishing.
- **Status:** done. Verified: seed `--preview` build byte-identical before/after the refactor; PHPUnit 81/81 (old imagedestroy deprecation); dev server + temporary editor: not logged in → login, unpublished SEO title shows in preview but not on the live page, draft news preview with its note, unknown/invalid page 404, every `../` asset on the zh home preview returns 200, preview button on the product form; screenshots 390 px and 1280 px; test rows and user deleted; republished (publish_log #39, style.css changed).
- **Next:** P3-D6 activity log. The contact form on a preview page still sends a real message.
