# 2026-10-22-phase1-b1-app-skeleton

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 16:24:15 | Write | app/composer.json |
| 16:24:16 | Write | app/config/config.example.php |
| 16:25:52 | Write | app/src/helpers.php |
| 16:25:53 | Write | app/src/Core/App.php |
| 16:25:54 | Write | app/src/Core/Db.php |
| 16:25:55 | Write | app/src/Core/Logger.php |
| 16:25:56 | Write | app/src/Core/Request.php |
| 16:25:57 | Write | app/src/Core/Response.php |
| 16:25:58 | Write | app/bootstrap.php |
| 16:26:23 | Write | app/src/Core/Session.php |
| 16:26:24 | Write | app/src/Core/Csrf.php |
| 16:26:25 | Write | app/src/Core/View.php |
| 16:26:26 | Write | app/src/Core/Router.php |
| 16:26:35 | Edit | app/src/Core/Router.php |
| 16:27:03 | Write | app/src/Core/Validator.php |
| 16:27:05 | Write | app/src/Core/RateLimiter.php |
| 16:27:06 | Write | app/src/Core/RateStore.php |
| 16:27:07 | Write | app/src/Core/DbRateStore.php |
| 16:27:08 | Write | app/src/Core/Mailer.php |
| 16:27:16 | Edit | app/src/Core/Validator.php |
| 16:27:44 | Write | app/src/Repository/UserRepository.php |
| 16:27:45 | Write | app/src/Repository/LoginAttemptRepository.php |
| 16:27:46 | Write | app/src/Core/Auth.php |
| 16:28:04 | Edit | app/src/Core/Auth.php |

## Summary
- **What changed:** `app/`: composer.json (PSR-4, phpmailer, phpunit dev, platform 8.3), bootstrap.php (autoload, config, error→exception, log, Asia/Vientiane), config.example.php, Core/ App, Db, Logger, Request, Response, Session, Csrf, Auth, View, Router, Validator, RateLimiter (+RateStore, DbRateStore), Mailer; storage/ dirs with .gitkeep; app/README.md (setup + commands); bin/dev-router.php (php -S router that mirrors .htaccess, incl. 404 for missing files).
- **Why:** P1-B1: app code outside the docroot, no framework.
- **Status:** done — all files pass `php -l`; used by every test below.
- **Next:** —
