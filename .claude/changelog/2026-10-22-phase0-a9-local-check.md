
## Summary
- **What changed:** `build/build.py`: Noto Sans SC loads with `display=optional` instead of `swap` (late font swap made the Chinese hero heading the LCP at 8.7 s). `docs/plan/06-phases.md`: phase 0 acceptance boxes ticked where verified.
- **Why:** P0-A9 local check.
- **Status:** partial — local Apache (gzip on) + Lighthouse 12 mobile: lo/index 89–95, en/index 95, th/index 93, lo|th/healthcare 99, lo/product 99, zh/index 58 → 91 after the font change, **zh/product-ct-scan 77** (font files ~820 KB compete with page loads on simulated 3G-ish bandwidth). SEO score 50–58 is the intended noindex. Accessibility and best practices 100. Puppeteer pass over all 276 pages at 390 and 1440 px: no horizontal overflow, no JS errors. Link check: 0 missing local targets. Lighthouse/puppeteer installed only in the scratchpad.
- **Next:** decision on the Chinese font (system fonts, self-hosted subset, or fewer weights) to bring zh product pages ≥ 90. P0-A10 waits for supplier data.
| 16:16:37 | Edit | build/optimize_media.py |
| 16:16:38 | Edit | build/optimize_media.py |
| 16:17:34 | Edit | build/build.py |

## Summary (revision 2 — Chinese font)
- **What changed:** user chose a self-hosted subset. `build/optimize_media.py --zh-font <NotoSansSC[wght].ttf>` instances weight 400–600 and keeps only the 563 CJK characters found in `public/zh/*.html` → `public/assets/fonts/noto-sans-sc-sub.woff2` (132 KB, one file for all weights). `build/build.py`: zh pages no longer call Google Fonts; they preload the subset and declare it in an inline `@font-face` (so the URL gets `?v=`). `design.md` §typography and `06-phases.md` acceptance updated.
- **Status:** done — Lighthouse mobile: zh/product-ct-scan 77 → 96, zh/index 90, zh/healthcare 95; the zh page now makes no external requests. Screenshot of zh product page matches the Google-font version by eye (headings now use the real 600 weight instead of 700 picked from Google's set; no visible difference at this size). fonttools/brotli live only in the scratch venv; the 17 MB source TTF is not in the repo (URL in the script docstring).
- **Next:** phase 0 acceptance met locally except P0-A10 data. Rerun `--zh-font` whenever Chinese text changes (phase 2 Publisher should do this automatically or fall back to system fonts).
