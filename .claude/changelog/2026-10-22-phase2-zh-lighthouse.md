# 2026-10-22-phase2-zh-lighthouse

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 23:31:43 | Edit | app/bin/dev-router.php |
| 23:33:48 | Edit | app/templates/site/layout.php |
| 23:33:49 | Edit | public/assets/js/main.js |

## Summary
- **What changed:** `app/templates/site/layout.php`: zh pages no longer preload Noto Sans SC or declare it inline; a `<meta name="font-zh">` carries the versioned URL and a `<noscript>` @font-face covers no-JS. `public/assets/js/main.js` loads the font with the FontFace API after `load` (system Chinese font until then). `app/bin/dev-router.php` gzips text files like `.htaccess` does in production. design.md §4 and plan 06 updated.
- **Why:** zh home Lighthouse mobile was 85 (target ≥ 90). Ruled out by experiment: font-display optional, no preload alone, video preload none, no video, no loader, no hero fade. Real cause in the simulation: the 134 KB font requested before first paint, plus uncompressed HTML/CSS on the local server.
- **Status:** done — after republish (#26): zh home 95/94/94, lo/en/th home 95, zh products 92, CLS 0. Headless Chrome confirms "Noto Sans SC" status loaded and applied on zh pages. PHPUnit 69/69.
- **Next:** confirm Lighthouse on the real server (P4); client PDFs and supplier data.
