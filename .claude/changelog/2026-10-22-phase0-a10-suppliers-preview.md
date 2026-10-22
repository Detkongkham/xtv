# 2026-10-22-phase0-a10-suppliers-preview

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 15:36:25 | Write | build/suppliers.py |
| 15:38:12 | Edit | build/build.py |
| 15:38:13 | Edit | build/suppliers.py |

## Summary
- **What changed:** new `build/suppliers.py` (empty `SUPPLIERS`/`SUPPLY` for real data, fictional `SAMPLE_*`, `COUNTRIES`, 4-language `SUP` strings); `build/build.py`: supplier helpers, `p_suppliers()` (`suppliers.html`), `p_supplier()` (`supplier-<slug>.html`), "Supplied by" block + spec row on product pages, "By <supplier>" line on product cards, top-level menu + mega menu (logos 5×2), drawer, footer and sitemap links, `--preview` flag (writes `preview/<lang>/`, noindex, red preview banner, sample data); `assets/css/style.css`: supplier section (`.logo-tile`, `.card--supplier`, `.sup-tile`, `.supplied-by`, `.badge-auth`, `.logo-row`, `.preview-bar`, dark-hero stats) + responsive rules; `assets/js/main.js`: division filter for supplier tiles with announced result count (existing product filter scoped to `data-filter=""`); `.gitignore`: `preview/`.
- **Why:** P0-A10 started before client data arrives (user request). Spec forbids guessed names/logos, so the preview uses obviously fictional "Sample Supplier A–H" with text placeholders instead of logos.
- **Status:** partial: UI done and viewable in preview. The live build is unchanged, verified byte-identical to the pre-change output (diff -rq on lo/en/th/zh/index.html). Preview builds 78 pages per language (69 + 1 + 8). Checked visually in headless Chrome at 1440px (suppliers list, supplier detail, product page); at 390px the page scrollWidth is 390 (no overflow). Filter JS not click-tested.
- **Next:** when the client sends the §7 data: fill `SUPPLIERS`/`SUPPLY` in `build/suppliers.py`, add logos to `assets/img/suppliers/`, add any new country codes, then run `python3 build/build.py`. JSON-LD stays in P3-D4.
