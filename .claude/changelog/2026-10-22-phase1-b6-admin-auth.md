# 2026-10-22-phase1-b6-admin-auth

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 16:33:00 | Write | app/src/Http/Admin/Controller.php |
| 16:33:01 | Write | app/src/Http/Admin/AuthController.php |
| 16:33:02 | Write | app/src/Http/Admin/DashboardController.php |
| 16:33:52 | Write | app/src/Service/CsvExporter.php |
| 16:33:53 | Write | app/src/Http/Admin/MessageController.php |
| 16:33:54 | Write | app/src/Http/Admin/UserController.php |
| 16:33:55 | Write | public/admin/index.php |
| 16:34:09 | Edit | app/src/Http/Admin/Controller.php |
| 16:34:10 | Edit | app/src/Http/Admin/Controller.php |
| 16:34:11 | Edit | app/src/Http/Admin/Controller.php |

## Summary
- **What changed:** Http/Admin/Controller (layout, requireLogin/Admin, CSRF), AuthController, UserController (users CRUD admin-only, own password /admin/account, cannot demote/deactivate self or last admin), public/admin/index.php (routes + CSP/no-store/noindex/DENY headers), bin/create-admin.php (hidden prompt or XTK_ADMIN_PASSWORD).
- **Why:** P1-B6 per 03 §2.1.
- **Status:** done — curl e2e: guest → login, headers present, cookie HttpOnly+SameSite=Lax, login without CSRF 400, 5 wrong → 6th locked (429) even with right password, admin login, self-demote refused, short password refused, user created, editor 403 on /admin/users (GET and POST), menu hidden, inactive user cannot log in, logout.
- **Next:** Secure cookie flag via `cookie_secure` once on HTTPS (phase S).
