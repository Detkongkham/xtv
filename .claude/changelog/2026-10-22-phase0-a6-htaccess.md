
## Summary
- **What changed:** new `public/.htaccess` (from 07 §5, plus: webp/webm/woff2 MIME types, HTTPS redirect skipped for localhost/127.0.0.1, HSTS only when HTTPS, no-cache also for txt/xml, brotli when `mod_brotli` exists else deflate, all rewrite rules inside `IfModule`); new `public/uploads/.htaccess` (no scripts, no listing — the one file in uploads/ that is committed).
- **Why:** P0-A6, compression, caching, security headers, 404, HTTPS.
- **Status:** done locally — tested with macOS Apache 2.4.67 (config in scratchpad, docroot `public/`, AllowOverride All): HTML/CSS gzip, `no-cache` on HTML, 1-year immutable on assets, nosniff/SAMEORIGIN/referrer headers, `/lo/nope.html` → 404 with the Lao 404 page, directory listing 404, other host → 301 https, `uploads/*.php` → 403. Not tested: brotli (module not available locally), HSTS (needs HTTPS). Known gap: the 404 page uses relative `../assets`, so a missing URL two folders deep would show it unstyled.
- **Next:** recheck on the cPanel server in phase S; P0-A9.
