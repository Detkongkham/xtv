# 2026-10-23-phase3-ui-leaders

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 15:21:09 | Write | app/src/Http/Admin/LeaderController.php |

## Summary
- **What changed:**
  - `005_leaders.sql`: new `leaders` table (photo, name/position/bio in 4 languages, sort, visible)
  - `LeaderRepository`, `LeaderController`, `admin/leaders/{index,edit}.php`, routes, "ຜູ້ບໍລິຫານ" in the admin menu
  - about.php: "Our leadership" section (photo and text alternate left/right, stacked on mobile); hidden while no leader is visible; "why" turns surface when it shows
  - `SiteView::story()`: paragraphs, plus "- " lines as a list; `SiteData::leaders()`; lang keys `leaders`, `leaders_lead`
  - photo usage in the media library, activity log and translation gaps know about leaders
- **Why:** client asked for owner/director profiles with photos on the about page
- **Status:** done — migration applied locally; phpunit 101 OK; about page rendered with 2 test leaders (lo, zh fallback to en), screenshots at 1440 and 500 px checked; test rows deleted
- **Next:** client adds the real people in /admin/leaders, then publish; run `php bin/migrate.php` on the server
