# 2026-10-22-phase1-b0-local-tools

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 16:22:31 | Write | /Users/ta/.claude/projects/-Users-ta-xtv/memory/user-language-lao.md |

## Summary
- **What changed:** Homebrew (installed by the user), php@8.3 8.3.36, mysql@8.0 8.0.46, composer 2.10.3, mailpit 1.31.6; MySQL + Mailpit run as brew services. Local DB `xtklaos_xtk` + user `xtklaos_app`, `app/config/config.php` (random DB password + HMAC secret, chmod 600, git-ignored).
- **Why:** P1-B0: local tools matching the server (PHP 8.3, MySQL 8.0).
- **Status:** done — `php -m` shows gd (with WebP), fileinfo, intl, mbstring, pdo_mysql, zip. Composer pulled PHP 8.5 in as `php`; always use `/opt/homebrew/opt/php@8.3/bin/php` (noted in app/README.md and 06-phases).
- **Next:** —
