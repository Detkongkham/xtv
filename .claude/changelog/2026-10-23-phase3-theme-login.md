
## Summary
- **What changed:** `app/templates/admin/partials/theme-css.php` (new): the `/admin/theme.css` link, used by the admin layout, the login page and the error page; `Controller::page()` no longer passes `themeVer`. A database error leaves the default colors.
- **Why:** login and error pages kept the default colors after the admin theme was changed.
- **Status:** done — checked with a test theme in the dev DB (removed after): login and 404 load theme.css, the login page shows the colors (headless Chrome). PHPUnit 99/99. Committed with the theme module as 10f984c (theme files only; the other admin UI work in progress is still uncommitted).
- **Next:** —
