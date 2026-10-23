# 2026-10-23-phase3-admin-dark

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 15:01:43 | Edit | app/src/Service/ThemeService.php |

## Summary
- **What changed:**
  - `admin.css`: base and status colors are `light-dark(light, dark)`, `color-scheme` follows the system or `<html data-mode>`; new `--on-color` for text on colored backgrounds (buttons, badges, counters); KPI hero card keeps a dark-blue gradient and white text in dark; a few white see-through backgrounds use `--white`. Mode switch styles in the account menu.
  - `ThemeService`: `DARK` defaults, third scope `dark` ("Admin ມືດ"), `adminCss()` writes light and dark together, `stylesheetDefault()`, `brandScaleDark()` for presets; `on-color` token.
  - `ThemeController`: `/admin/theme.css` from both admin scopes, `POST /admin/mode` (cookie `xtk_mode`, safe redirect back), `modeAttr()`. Layout, login and error pages put `data-mode` on `<html>`.
  - Account menu: ສະຫວ່າງ / ມືດ / ຕາມລະບົບ; `admin.js` switches at once and sets the cookie. Theme page: "Admin ມືດ" tab shows the admin in dark while editing.
  - Tests: dark defaults declared and readable, `adminCss`, `cleanTheme` with dark; `/admin/mode` exempt from activity labels. `design.md` note.
- **Why:** user asked for an admin dark mode.
- **Status:** done — PHPUnit 101/101 (old deprecation). Headless Chrome with a temp session (removed): dashboard, inbox, products, theme page in dark at 1440; menu switch sets data-mode and cookie; no-JS POST sets/clears the cookie and refuses an outside redirect; login page gets data-mode; a bad cookie value is ignored.
- **Next:** product edit and settings also checked in dark; pages not screenshotted (media, publish, users, other edit forms) use the same tokens but were not looked at one by one.
