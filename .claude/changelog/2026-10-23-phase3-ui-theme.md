# 2026-10-23-phase3-ui-theme

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 15:09:33 | Write | app/templates/admin/theme/index.php |

## Summary
- **What changed:** `/admin/theme` redesigned on the inbox pattern: own header (palette tile, load theme select, Export/Import), 5 stat cards (changed colors per tab — click opens the tab; hard-to-read pairs for the open tab; saved themes / previous versions), an info note, the color list in an `ibx-box` with inbox-style tabs, search (`/` focuses it), a "changed colors only" chip and reset, one section per group with rows like inbox rows (swatch, name, `--token`, value pill, HEX field); the editor, readability, site preview and admin sample in the sticky `ibx-panel` on the right (follow switch in the `gpe-toggle` style); a sticky save bar that says when changes are not saved; saved themes as an inbox list with brand-color dots, a "load" button and delete. Files: `theme/index.php`, `theme.js` (stat counts and tone, stat → tab, filter + empty note, dirty note, saved dots/load, narrow breakpoint 1279px), `admin.css` (theme block rewritten, `.thm*`, `button.ibx-stat`, `.ibx-tabs button`), `Controller::page` (ownHeader).
- **Why:** match the new messages page look.
- **Status:** done. Checked with a scratch render through the real layout in headless Chrome at 1600, 1100 and 390 (iframe): no horizontal page scroll; scripted checks of the changed-only filter, stat → tab, count updates, dirty note and saved dots. PHPUnit 101/101 (old deprecation). Form fields, names and routes unchanged; without JS all three tabs' HEX fields still show.
- **Next:** check it logged in on the real server (the scratch render could not load `/admin/preview` in the iframe).

## Summary (preview size)
- **What changed:** site preview box 400 → 560 px tall (420 px on phones, height from `--h`); a maximize button next to the desktop/phone switch opens the preview over the whole screen (dimmed backdrop, Esc or the button closes it). Files: `theme/index.php`, `theme.js` (`setBig`), `admin.css` (`.th-prev__big`, `.th-prev.is-big`, `.thm.is-prev-big`).
- **Why:** the preview was too small to read.
- **Status:** done. Headless Chrome at 1600×1000: normal 560 px, enlarged 1526×858 at scale 1, over the top bar and save bar. Not checked logged in.

## Summary (stat cards removed)
- **What changed:** the 5 stat cards on `/admin/theme` removed, with their helper and tones (`theme/index.php`), the stat → tab, warn-count and multi `data-count` code (`theme.js`) and `.thm .ibx-stats` / `button.ibx-stat` rules (`admin.css`). Tab counts, readability box and the rest unchanged.
- **Why:** asked by the user.
- **Status:** done; scratch render checked in headless Chrome, PHP and JS lint clean.

## Summary (ready-made theme cards)
- **What changed:** in place of the stat cards, a "ຕີມສຳເລັດຮູບ" box with one card per `ThemeService::PRESETS` entry: the brand scale as a color strip (900/600/500/100/50; the XTK card shows the stored defaults), the name, and a "ໃຊ້ຢູ່" badge when the site brand colors are exactly that scale (updates live). Clicking a card goes through the same confirm + fill as the "ໂຫຼດຕີມ…" select. JS only (hidden without JS). Files: `theme/index.php`, `theme.js` (`markPresets`, card clicks), `admin.css` (`.thm-preset*`).
- **Status:** done. Headless Chrome: clicking card 2 fills the site brand to #0057a8 scale, badge moves, dirty note shows; card 1 (XTK) clears it back. Not checked logged in.
