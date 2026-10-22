# 2026-10-22-phase3-d1-settings

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 23:51:29 | Write | app/src/Http/Admin/SettingController.php |
| 23:52:09 | Write | app/templates/admin/settings/index.php |

## Summary
- **What changed:**
  - `app/src/Http/Admin/SettingController.php` + `app/templates/admin/settings/index.php`: `/admin/settings` (admins only) for contact details, 4-language address, map place, home stats (founded, experience; division/product counts shown read-only), notify emails, GA4 ID, noindex
  - `public/admin/index.php`, `app/templates/admin/layout.php`: routes and "ການຕັ້ງຄ່າ" menu item (admins only)
  - `app/templates/site/layout.php`: GA4 gtag when `analytics_id` is set (never in the preview build)
  - `app/templates/site/pages/about.php`, `partials/contact-aside.php`, `partials/location.php`: an empty fax is left out
  - `app/tests/SiteBuildTest.php`: `testSettingsReachThePages`
  - `docs/plan/06-phases.md`: D1 done, first acceptance check ticked
- **Why:** P3-D1, staff change contact details without a developer.
- **Status:** done. Verified: seed build HTML byte-identical to before (276 pages); invalid post → 422 with 11 field errors; changing tel and mobile in admin → publish → new numbers on all 276 pages (footer, floating button, contact page); original values restored and republished; PHPUnit 70/70.
- **Next:** P3-D2 FAQ (migration 004_extras.sql). Not tested: real GA4 data reaching Google.
