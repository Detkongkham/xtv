# 2026-10-22-phase0-remove-quote-careers

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| — | Bash (python script) | build/build.py |
| — | Bash (python script) | build/strings.py |
| — | Bash (python script) | build/strings2.py |
| — | Bash (python script) | design.md |
| — | Bash (rm) | {lo,en,th,zh}/quote.html, {lo,en,th,zh}/careers.html, assets/img/photo/careers.jpg |
| — | Bash (build) | lo/, en/, th/, zh/ (regenerated, 69 pages per language) |

## Summary
- **What changed:**
  - `build/build.py`: removed `p_quote()` and `p_careers()`; every "Request a quote" button (header, mobile drawer, hero slides, CTA band, products overview, solutions, product pages, text-only product cards) now goes to `contact.html`; CTA band second button is WhatsApp; product page "Ordering procedure" (qh1–qh3) section removed; About mega-menu card "Careers" replaced by "Downloads"; footer and sitemap no longer list quote/careers; recruitment news item (n2) removed; FAQ item 6 (jobs) removed; contact tiles: Sales → email, Recruitment → WhatsApp; contact subject list without Recruitment; dropped unused `steps(with_p)` and file-input code.
  - `build/strings.py`, `build/strings2.py`: deleted 36 quote/careers-only keys; reworded `fa1`, `fq1`, `prod_lead`, `q_sales`, `cta_h` (4 languages) so nothing points to a quote form or job application.
  - Deleted generated `quote.html` and `careers.html` in all 4 languages, and the unused `assets/img/photo/careers.jpg`.
  - `design.md`: page table (69 pages/language), backend note points to `docs/plan/`.
- **Why:** client decision — no quote-request or job-application features on the site or in the backend.
- **Status:** done — `python3 build/build.py` runs (276 pages); `grep` finds 0 links to quote.html/careers.html; headless Chrome screenshots of lo/contact and lo/product-ct-scan checked by eye.
- **Next:** remaining uses of the word "quotation" describe the sales process (spec row "Stated in the quotation", service step "Assessment & quotation", privacy text) and were kept on purpose.
