# 2026-10-22-phase2-c9-downloads

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 23:05:12 | Write | app/templates/site/pages/downloads.php |
| 23:05:13 | Write | app/src/Repository/DownloadRepository.php |
| 23:05:14 | Write | app/src/Http/Admin/DownloadController.php |
| 23:05:46 | Write | app/templates/admin/downloads/index.php |
| 23:05:47 | Write | app/templates/admin/downloads/edit.php |

## Summary
- **What changed:** `/admin/downloads` (list with file, owner company/division, visible switch, ↑↓; form with 4-language title and optional summary, PDF upload or pick from library, company or division, delete keeps the PDF in the library). `DownloadRepository`, `MediaService::storePdf()` (finfo must say application/pdf). Site `downloads.html` now built from the table: title, "PDF · size · summary/division/XTK", real download link with the original file name; a hidden division hides its catalogue; with no files it shows "documents are being prepared" + contact button (new `dl_empty` text ×4; Chinese worded with characters the font subset has — SiteBuildTest caught 索).
- **Why:** P2-C9. The old page linked to `#`.
- **Status:** admin done; content waits for the client's 6 PDFs (company profile + 5 catalogues). Tested through the admin: no file → error, JPEG named .pdf → rejected, upload + reuse from library, page in 4 languages with size and division names, PDF served as application/pdf, hide, delete. Test data removed; site republished with the empty state (screenshot checked). PHPUnit 65/65.
- **Next:** C10 dashboard, C11 retire Python, zh home Lighthouse.
