# 2026-10-23-phase3-d6-activity

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 13:04:58 | Write | app/src/Repository/ActivityRepository.php |

## Summary
- **What changed:**
  - `ActivityRepository`: add, page (with current item names, and names kept in the log for deleted items), count, users, purge; entity and action labels
  - `Controller::contentChanged(details, id)` now also writes activity_log; action/entity/id come from the admin URL (`activityOf()`); `markUnpublished()`, `activity()`
  - every content controller passes the item name on create/delete and visible/featured on toggle; explicit entries for login, publish, rollback, CSV export, message status and notes, media upload/delete, users create/update, own password change, settings
  - `/admin/activity` (admins only, menu item): filter by user and type, 50 per page, IP; `cleanup.php` purges entries older than 365 days
  - tests `ActivityPathTest` (URL → action, every POST route has labels); 06-phases, README
- **Why:** P3-D6, admins see who changed what, when and from where.
- **Status:** done. Verified: PHPUnit 83/83 (old imagedestroy deprecation); dev server + temporary admin: login, FAQ create/hide/delete, SEO save, CSV export, publish all listed with names (deleted FAQ keeps its name), type filter, a null-entity bug (login row, PHP 8.5) found and fixed; screenshots 1280 px and 500 px (cell layout fixed on narrow screens); `cleanup.php` ran; test user, its log rows and the FAQ removed.
- **Next:** P3-D7 full backup. Not logged: failed logins (already in login_attempts), logout, picture uploads made from inside other forms (the form's own save is logged).
