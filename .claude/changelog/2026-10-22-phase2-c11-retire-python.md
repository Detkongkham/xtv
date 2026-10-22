# 2026-10-22-phase2-c11-retire-python

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 23:25:33 | Write | .github/workflows/pages.yml |

## Summary
- **What changed:** deleted `build/build.py`, `strings.py`, `strings2.py`, `suppliers.py`, `export_lang.py`, `export_seed.py`, `compare_html.py`. Moved `build/optimize_media.py` → `scripts/optimize_media.py` and `build/media/hero-lab-source.mp4` → `scripts/media/` (paths and docstring updated: publish first, then `--zh-font`). `.github/workflows/pages.yml` now builds the Pages preview with PHP (`bin/build-site.php --seed`) into `_site` (assets + 4 languages only, no admin/api PHP), base_url from configure-pages. Docs: design.md §4 + §10 file table, app/README.md, plan 02/05/06, lang file headers, FontCheck hint.
- **Why:** P2-C11 — the database + Publisher is the only source of the site; the font subset tool must stay (decision in P2-C4).
- **Status:** done — workflow steps simulated in a clean copy of the tracked files: 276 pages, sitemap with the Pages URL, robots `Disallow` (same as the old preview), LinkChecker 0 broken. `optimize_media.py` WebP mode runs from the new path; `--zh-font` not run (no Noto Sans SC TTF on this machine), code unchanged apart from paths/messages. The workflow itself has not run on GitHub yet (runs on the next push to main). PHPUnit 69/69.
- **Next:** zh home Lighthouse ≥ 90; client PDFs and supplier data.
