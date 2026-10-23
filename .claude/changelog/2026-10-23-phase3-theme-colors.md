# 2026-10-23-phase3-theme-colors

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 14:45:41 | Write | app/src/Service/ThemeService.php |
| 14:46:04 | Write | app/src/Http/Admin/ThemeController.php |
| 14:47:49 | Write | app/templates/admin/theme/index.php |
| 14:48:49 | Write | public/admin/assets/theme.js |
| 14:50:36 | Write | app/tests/ThemeServiceTest.php |
| 14:54:03 | Edit | design.md |

## Summary
- **What changed:**
  - `public/assets/css/style.css`, `public/admin/assets/admin.css`: element color tokens (`--btn-bg`, `--hdr-bg`, `--hero-overlay`, `--ftr-*`, `--side-*`, status/chart colors…), each defaulting to its base color; fixed hex/rgba colors replaced by tokens / `color-mix()`.
  - `app/src/Service/ThemeService.php`: color list (Lao labels, groups, defaults), validation (#rrggbb / #rrggbbaa only), CSS output, resolve, WCAG contrast, brand scale, presets.
  - `app/src/Http/Admin/ThemeController.php` + routes in `public/admin/index.php`: GET/POST `/admin/theme`, POST `/admin/theme/delete`, GET `/admin/theme.css`. Settings `theme`, `theme_saved` (20), `theme_history` (10). Activity entity `theme`.
  - `app/templates/admin/theme/index.php`, `public/admin/assets/theme.js`, theme CSS in admin.css: RGB(A) sliders + numbers, HEX/RGB input, eyedropper, follow-base toggle, recent colors, brand scale button, search, site/admin tabs, live preview of the site in a frame (desktop/phone), live recolor of the admin, contrast checks, presets/saved/history loader, JSON export/import, reset tab.
  - Site layout: `<style>` with the theme after style.css, `theme-color` follows brand-600. Admin layout: `/admin/theme.css` link + nav item "ຕີມສີ" (admins). PreviewController: CSP hash for the theme style, same-origin framing.
  - `app/tests/ThemeServiceTest.php`, `design.md` §9.
- **Why:** admins can change every color of the site and the admin freely (RGB per element) without editing CSS.
- **Status:** done — PHPUnit 99/99 pass (1 old imagedestroy deprecation). Checked in headless Chrome on the local server: page renders at 1440 and 390 px, sliders recolor the preview frame and the admin, save stores cleaned values (an injected `x;}body{` was dropped), `/admin/theme.css` and the preview `<style>` + CSP hash match. Test rows in the dev DB were removed afterwards.
- **Next:** login and error pages (standalone templates) keep the default admin colors. Preview pages already log CSP errors for inline `style="--i:…"` attributes (old, not from this change).
