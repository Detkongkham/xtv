# 2026-10-22-phase1-b2-database

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 16:28:38 | Write | app/database/migrations/001_core.sql |
| 16:28:40 | Write | app/bin/migrate.php |
| 16:28:41 | Write | app/src/Repository/SettingRepository.php |
| 16:29:43 | Write | app/lang/contact.php |
| 16:29:44 | Write | app/src/Service/TimeToken.php |
| 16:29:45 | Write | app/src/Repository/MessageRepository.php |
| 16:30:07 | Write | app/src/Service/ContactService.php |
| 16:30:45 | Write | app/src/Http/ContactController.php |
| 16:30:46 | Write | public/api/contact.php |
| 16:30:47 | Write | app/templates/contact-result.php |
| 16:30:48 | Write | app/templates/email/contact-notify.php |
| 16:30:49 | Write | app/templates/email/contact-autoreply.php |

## Summary
- **What changed:** `database/migrations/001_core.sql` (tables from 04 §4.1 + default settings contact/notify_emails/noindex), bin/migrate.php (--status). This log also holds P1-B3/B4 files (slug not switched): lang/contact.php (4 languages), Service/TimeToken, ContactService, CsvExporter, Repository/Message, Setting, User, LoginAttempt, Http/ContactController, public/api/contact.php, templates contact-result.php + email/contact-notify.php + contact-autoreply.php.
- **Why:** P1-B2 database; P1-B3 mail (PHPMailer SMTP → Mailpit locally); P1-B4 contact API per 03 §3.
- **Status:** done — migration applied; curl e2e: token no-store, 4 languages 200, 4 notify + 4 auto-replies in the right language in Mailpit, Reply-To = customer, no-JS 303 → ?sent=1#sent, no-JS 422 HTML page, JSON 422 field codes, honeypot not saved, forged/too-fast token 422, 5/10 min then 429, no-JS 2 then 429 page with phone, foreign Origin 403, GET 405, SMTP stopped → saved with mail_error.
- **Next:** Real mailbox + SPF/DKIM/DMARC in phase S.
