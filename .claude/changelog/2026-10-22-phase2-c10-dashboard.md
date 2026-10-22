# 2026-10-22-phase2-c10-dashboard

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 23:18:13 | Write | app/src/Repository/TranslationGapRepository.php |
| 23:18:25 | Edit | app/src/Repository/PublishLogRepository.php |
| 23:18:26 | Edit | app/src/Http/Admin/Controller.php |
| 23:21:40 | Write | app/tests/PublishStateTest.php |

## Summary
- **What changed:** `/admin` dashboard: publish state card (up to date / unpublished changes / rollback online / never published, last publish by whom, last edit, "publish now" button, last 3 publishes); "missing translations" card (visible divisions, groups, products + specs, suppliers, published news, downloads with lo/en filled but th/zh empty; 8 shown, rest in `<details>`); red dot on the "ເຜີຍແຜ່" nav item. New `TranslationGapRepository`, `PublishLogRepository::lastLive()/pendingReason()`, `Controller::publishState()`, `PublishStateTest`.
- **Why:** docs/plan/03-backend.md §2.2 and 01-architecture.md §4.2 — staff must see that edits are not online until published.
- **Status:** done — tested via admin with a temporary editor: saving a division shows the badge and dot; temporary blanked th/zh fields and a spec appear in the list with links and field names (>8 → details); publishing from the dashboard clears the badge. Data restored from seed (0 differences), test user removed, live site byte-identical with the previous build. Screenshots at 1440 and 500 px OK. PHPUnit 69/69.
- **Next:** C11 retire Python; zh home Lighthouse.
