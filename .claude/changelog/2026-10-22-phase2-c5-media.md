# 2026-10-22-phase2-c5-media

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 22:24:05 | Write | app/src/Service/MediaService.php |
| 22:24:50 | Write | app/src/Repository/MediaRepository.php |
| 22:25:00 | Write | app/bin/import-media.php |
| 22:25:24 | Edit | app/src/Core/Request.php |
| 22:25:25 | Write | app/src/Http/Admin/MediaController.php |
| 22:25:55 | Write | app/templates/admin/media/index.php |
| 22:25:56 | Write | app/templates/admin/media/edit.php |
| 22:26:17 | Write | app/templates/admin/partials/media-picker.php |
| 22:26:19 | Edit | app/src/Http/Admin/Controller.php |

## Summary
- **What changed:** `MediaService` rewritten (finfo type check, every image rebuilt by GD with EXIF rotation, widths 480/960/1600 never enlarged, WebP + JPEG or PNG when transparent, PDFs ≤ 20 MB, 40 MP cap); `MediaRepository` (search, usage across divisions/products/news/suppliers/downloads, alt text); `/admin/media` (multi-upload, grid with "used N places / unused", detail page with sizes, usage, alt ×4, delete only when unused); media-picker partial + `Controller::pickedMedia()` for C6–C8; `SiteView::media()` renders `<picture>` with srcset/sizes (`SIZES_CARD`, `SIZES_WIDE`); `LinkChecker` reads every srcset URL; `bin/import-media.php` moved the 51 old-site pictures into `uploads/` with sizes; `public/admin/.user.ini` (20 MB uploads), `.htaccess` denies `.user.ini`; `Request::files/tooLarge`. Docs 06/07, README.
- **Why:** P2-C5 media library.
- **Status:** done — before import the site output was byte-identical; import of 51 files 1.6 s; publish passes the link check; via admin: 3000 px JPEG → 3 sizes, transparent PNG → PNG fallback, PDF stored, PHP disguised as JPG rejected, used file cannot be deleted, unused one deleted with its files. Screenshots at 1440 px. PHPUnit 45/45. Lighthouse mobile: division/product 97, home en 90, home zh 85 (the old Python pages score the same on this server — LCP is the hero heading waiting for the Chinese font).
- **Next:** C6 divisions/groups (first user of the media picker). zh home Lighthouse needs a fix before phase 2 is closed.
