# 2026-10-23-phase3-d8-turnstile

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 13:14:38 | Write | app/src/Service/Turnstile.php |

## Summary
- **What changed:**
  - `app/src/Service/Turnstile.php`: off unless `turnstile.site_key` and `turnstile.secret` are both in config.php; siteverify over curl (3 s connect, 6 s total); 'ok' / 'failed' / 'unavailable' (Cloudflare unreachable lets the form through, other checks still apply)
  - `ContactService`: checks the token after the field checks (a token is single-use), 422 `captcha`; `ContactController` passes `Turnstile::fromConfig()`; no-JS result page message in 4 languages (`lang/contact.php`)
  - contact form: `data-turnstile` + hidden box only with a key and not in previews (`SiteView::turnstileKey()`); `f_err_captcha` in lang/*.php (zh worded with characters in the font subset)
  - `main.js`: Cloudflare script loaded on first focus of the form (explicit render, flexible size, language th/zh-cn/en, Lao = auto); a submit before the token waits up to 20 s; widget reset only after the server used the token
  - `config.example.php`, 06-phases, 07-deployment (how to turn it on); `tests/TurnstileTest.php`
- **Why:** P3-D8, ready for when spam gets past honeypot + rate limits; the plan says to switch it on only then.
- **Status:** done, off by default. Verified: PHPUnit 89/89 (old imagedestroy deprecation); with Cloudflare's test keys on the local server: passing secret + token → saved, no token → captcha, failing secret → captcha, no-JS page message; headless Chrome over DevTools: script not loaded before use, submit right after typing waited for the token and was saved; found and fixed a bug where the retry after a rejected time token sent no Turnstile token; 390 px screenshot, no horizontal scroll; test messages and rate limits removed, config.php restored, republished without a key.
- **Next:** phase 3 done apart from the Rich Results check (needs a public URL). Phase 4 / S next.
