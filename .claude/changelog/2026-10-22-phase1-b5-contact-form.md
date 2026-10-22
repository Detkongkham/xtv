# 2026-10-22-phase1-b5-contact-form

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 16:32:12 | Edit | public/assets/js/main.js |

## Summary
- **What changed:** build/build.py: form posts to /api/contact.php, hidden lang/ts, honeypot `.hp`, subject option values = codes, error span per field, data-msgs JSON; strings.py/strings2.py: new f_ok text + f_sending/f_err_* (4 languages); main.js: demo handler replaced (token on first focus, fetch, field errors + aria, sending state, retry once without token, 429/5xx message with phone + WhatsApp); style.css: .field__err, .form__err, .form--sent, .form__ok:target, .hp; zh font subset rebuilt (570 chars, 133 KB).
- **Why:** P1-B5 per 02 §5.
- **Status:** done — build 276 pages; Playwright (cached Chromium): all 4 languages show errors under fields, focus first error, token fetched, 200, success focused, saved lang/subject code, no overflow at 390 px; server 422 under phone; 429 message; no-JS submit shows #sent. No JS errors.
- **Next:** —
