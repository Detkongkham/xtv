# 2026-10-22-phase2-c4-publisher

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 22:04:03 | Write | app/database/migrations/003_publish_warnings.sql |
| 22:04:43 | Write | app/src/Publish/LinkChecker.php |
| 22:04:44 | Write | app/src/Publish/FontCheck.php |
| 22:05:46 | Write | app/src/Repository/PublishLogRepository.php |
| 22:05:47 | Write | app/src/Publish/PublishBusyException.php |
| 22:05:48 | Write | app/src/Publish/Publisher.php |
| 22:06:07 | Write | app/bin/publish.php |
| 22:06:52 | Edit | app/src/Publish/SiteBuilder.php |
| 22:06:53 | Edit | app/src/Publish/SiteBuilder.php |
| 22:06:54 | Edit | app/src/Publish/SiteBuilder.php |
| 22:07:33 | Write | app/src/Http/Admin/PublishController.php |
| 22:07:34 | Write | app/templates/admin/publish/index.php |
| 22:10:48 | Write | app/tests/SiteBuildTest.php |
| 22:10:58 | Edit | app/src/Publish/LinkChecker.php |

## Summary
- **What changed:** `Publisher` (flock lock, build into `storage/build/<id>/`, page + link check, swap into `public/`, keep 3 builds, rollback), `LinkChecker`, `FontCheck` + `public/assets/fonts/noto-sans-sc-sub.txt` (570 chars; `optimize_media.py` now writes it), `SiteBuilder` redirect pages + `sitemap.xml`, `PublishLogRepository`, migration `003_publish_warnings.sql`, `bin/publish.php`, `/admin/publish` (publish, history, rollback; editors allowed), admin.js `data-confirm`/`data-busy`, tests `SiteBuildTest`. Docs 04/05/06, README.
- **Why:** P2-C4 — staff publish from the admin; a bad build never reaches the live site.
- **Status:** done — verified locally: publish 276 pages in 0.16–0.25 s with output identical to Python (compare_html: 0 different); broken link → failed, live files unchanged; lock held → second publish gets the wait message (CLI and admin); rollback via CLI and admin; redirect page + chain/loop handling; PHPUnit 41/41. Font decision: warn + developer regenerates subset.
- **Next:** C12 suppliers admin, then C5–C9, C10, C11. Slug → redirect rows are written by the product admin (C7).
