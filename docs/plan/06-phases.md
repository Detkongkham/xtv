# 06 — ເຟດການສ້າງໂປຣເຈັກ

ເຟດລຽງຕາມຄວາມສຳຄັນ. **ເຟດໜຶ່ງຕ້ອງຜ່ານເງື່ອນໄຂຮັບວຽກທັງໝົດກ່ອນເລີ່ມເຟດຕໍ່ໄປ**, ຍົກເວັ້ນວຽກທີ່ໝາຍວ່າເຮັດຂະໜານໄດ້.

ລະຫັດວຽກ (ເຊັ່ນ `P1-B4`) ໃຊ້ເປັນຊື່ changelog: `phase1-b4-contact-api`.

ເວລາທີ່ຄາດໄວ້ນັບເປັນວັນເຮັດວຽກຂອງນັກພັດທະນາ 1 ຄົນ. ເປັນການຄາດຄະເນຫຍາບ, ບໍ່ແມ່ນສັນຍາ.

| ເຟດ | ຊື່ | ຄວາມສຳຄັນ | ຜົນທີ່ລູກຄ້າເຫັນ | ເວລາຄາດ |
|---|---|---|---|---|
| 0 | ໜ້າເວັບ static ພ້ອມ ແລະ ໄວ (ໃນເຄື່ອງ) | 🔴 | ເບິ່ງເວັບໃນເຄື່ອງນັກພັດທະນາ | 2–3 ວັນ |
| 1 | ຟອມຕິດຕໍ່ + ພື້ນຖານ admin (ໃນເຄື່ອງ) | 🔴 | ຂໍ້ຄວາມຈາກຟອມເຂົ້າ admin ໃນເຄື່ອງ | 5–7 ວັນ |
| 2 | ຈັດການເນື້ອຫາ + Publisher (ໃນເຄື່ອງ) | 🔴 | ແກ້ສິນຄ້າ, ຂ່າວ, ດາວໂຫຼດ ແລ້ວເຜີຍແຜ່ໃນເຄື່ອງ | 10–15 ວັນ |
| 3 | ການຕັ້ງຄ່າ, FAQ, Hero, SEO (ໃນເຄື່ອງ) | 🟡 | ແກ້ເບີໂທ, FAQ, hero ແລະ SEO ໄດ້ເອງ | 4–6 ວັນ |
| S | ຂຶ້ນ server (subdomain) | 🔴 | ເປີດເບິ່ງເວັບ ແລະ admin ທີ່ `new.xtklaos.com`, ອີເມວໄປຮອດແທ້ | 3–4 ວັນ |
| 4 | Go-live | 🔴 (ເມື່ອລູກຄ້າອະນຸມັດ) | ເວັບໃໝ່ຂຶ້ນແທນທີ່ `xtklaos.com` | 3–5 ວັນ |

**ພັດທະນາໃນເຄື່ອງກ່ອນ (ຕັດສິນໃຈ 22/10/2026).** ເຟດ 0–3 ເຮັດໃນເຄື່ອງທັງໝົດ, ບໍ່ແຕະ server. ວຽກທີ່ຕ້ອງໃຊ້ server (ກວດ server, subdomain, SSH, deploy, mailbox ແທ້, cron) ຍ້າຍໄປເຟດ S. ລະຫັດວຽກເດີມ (ເຊັ່ນ `P0-A1`) ບໍ່ປ່ຽນ, ພຽງແຕ່ຍ້າຍບ່ອນ.

ຂໍ້ຄວນລະວັງ: ຖ້າ server ຂາດ extension ທີ່ໂຄດໃຊ້ (ເຊັ່ນ `gd` ທີ່ບໍ່ຮອງຮັບ WebP), ຈະຮູ້ຊ້າ ແລະ ອາດຕ້ອງແກ້ໂຄດ. ເພື່ອຫຼຸດຄວາມສ່ຽງ, ໃນເຄື່ອງໃຫ້ໃຊ້ PHP 8.3 ແລະ MySQL 8.0 ໃຫ້ກົງກັບ server ([01 §5](01-architecture.md)). ຈະກວດ P0-A1 ກ່ອນກໍໄດ້ ຖ້າຕ້ອງການ: ເປັນການອ່ານຄ່າໃນ cPanel ຢ່າງດຽວ, ບໍ່ປ່ຽນຫຍັງໃນ server.

---

## ເຟດ 0 — ໜ້າເວັບ static ພ້ອມ ແລະ ໄວ (ໃນເຄື່ອງ) 🔴
**ເປົ້າໝາຍ:** ເວັບ static ຢູ່ໃນໂຄງ `public/` ພ້ອມອັບຂຶ້ນ server, ໄວ ແລະ Google ບໍ່ເກັບຂໍ້ມູນ. ກວດທັງໝົດໃນເຄື່ອງ.

ລຳດັບແນະນຳ: A2 → A5 → A7 → A6 → A9. A10 ເຮັດເມື່ອໄດ້ຂໍ້ມູນ.

| ລະຫັດ | ວຽກ | ລາຍລະອຽດ |
|---|---|---|
| P0-A2 | ຍ້າຍໂຄງ repo | `assets/` → `public/assets/`, build output → `public/`, ອັບເດດ `.gitignore` ຕາມ [05-folder-structure.md §4](05-folder-structure.md) |
| P0-A5 | ກັນ Google | `public/robots.txt` Disallow ທັງໝົດ + `<meta name="robots" content="noindex">` ທຸກໜ້າ |
| P0-A7 | ຮູບ ແລະ ວິດີໂອ | ບີບວິດີໂອ hero ≤ 2 MB + WebM, ຮູບທັງໝົດມີ WebP ຜ່ານ `<picture>`, ໃສ່ `width`/`height`, cache busting `?v=` |
| P0-A6 | `.htaccess` | ຂຽນ `public/.htaccess`: gzip/brotli, cache ຂອງ `assets/` 1 ປີ, HTML no-cache, security headers, ໜ້າ 404 → `/lo/404.html`, ບັງຄັບ HTTPS. ໃນເຄື່ອງກວດແຕ່ syntax; ທົດສອບແທ້ໃນເຟດ S |
| P0-A9 | ກວດໃນເຄື່ອງ | ເປີດຜ່ານ local server (`python3 -m http.server -d public`), Lighthouse, ລິ້ງເສຍ, 4 ພາສາ, ມືຖື/desktop |
| P0-A10 | ⭐ ໜ້າຜູ້ສະໜອງ (static) | `suppliers.html` + `supplier-<slug>.html` + ເມນູຫຼັກ + mega menu + ບລັອກ "ສະໜອງໂດຍ" ໃນໜ້າສິນຄ້າ + ຊື່ຜູ້ສະໜອງໃນກາດສິນຄ້າ ຕາມ [08-suppliers.md](08-suppliers.md). UI ເຮັດແລ້ວໃນ `--preview`. **ໃສ່ຂໍ້ມູນແທ້ທັນທີທີ່ໄດ້ຈາກລູກຄ້າ**. ຂໍ້ມູນຍັງບໍ່ມາ ບໍ່ຂັດຂວາງເງື່ອນໄຂຮັບວຽກຂອງເຟດ 0 |

ວຽກທີ່ຍ້າຍໄປເຟດ S: P0-A1 (ກວດ server), P0-A3 (subdomain), P0-A4 (SSH key), P0-A8 (deploy).

**ເງື່ອນໄຂຮັບວຽກ**
- [x] `python3 build/build.py` ສ້າງ `public/` ຄົບ 4 ພາສາ, ເປີດຜ່ານ local server ໄດ້ທຸກໜ້າ (22/10/2026: 276 ໜ້າ, ບໍ່ລົ້ນຈໍທີ່ 390/1440 px, ບໍ່ມີ JS error)
- [x] Lighthouse mobile Performance ≥ 90 ໜ້າຫຼັກ, ໜ້າຂະແໜງ ແລະ ໜ້າສິນຄ້າ (ໃນເຄື່ອງ, throttling ມືຖືຕາມຄ່າເລີ່ມຕົ້ນ) — 22/10/2026: ທຸກພາສາ 90–99 ຫຼັງຕັດຟອນຈີນເປັນ subset
- [x] ບໍ່ມີລິ້ງເສຍ. `robots.txt` ຫ້າມ index ແລະ ທຸກໜ້າມີ `noindex`
- [x] ວິດີໂອ hero ≤ 2 MB, ຮູບທຸກຮູບມີ WebP

---

## ເຟດ 1 — ຟອມຕິດຕໍ່ໃຊ້ໄດ້ແທ້ + ພື້ນຖານ admin 🔴
**ເປົ້າໝາຍ:** ທຸກຂໍ້ຄວາມຈາກຟອມຕິດຕໍ່ ບັນທຶກລົງ DB, ແຈ້ງທີມງານທາງອີເມວ ແລະ ຈັດການໄດ້ໃນ admin.

| ລະຫັດ | ວຽກ | ລາຍລະອຽດ |
|---|---|---|
| P1-B0 | ເຄື່ອງມືໃນເຄື່ອງ | ຕິດຕັ້ງ PHP 8.3, MySQL 8.0, composer ແລະ Mailpit (ຮັບອີເມວທົດສອບ) ຜ່ານ Homebrew. ເປີດ extension ໃຫ້ກົງກັບລາຍການໃນ [01 §5](01-architecture.md). ໝາຍເຫດ: composer ຕິດຕັ້ງ PHP 8.5 ມານຳ; ໃຊ້ `/opt/homebrew/opt/php@8.3/bin/php` ສະເໝີ (ເບິ່ງ [app/README.md](../../app/README.md)) |
| P1-B1 | ໂຄງ app | `app/` ຕາມ [05](05-folder-structure.md): composer, `bootstrap.php`, `config.example.php`, `Db`, `Logger`, error handler, timezone `Asia/Vientiane` |
| P1-B2 | ຖານຂໍ້ມູນ | ສ້າງ DB + user ໃນ MySQL ໃນເຄື່ອງ, `bin/migrate.php`, migration `001_core.sql` |
| P1-B3 | ອີເມວ | `Mailer` (SMTP ຕັ້ງຄ່າໄດ້), ໃນເຄື່ອງສົ່ງເຂົ້າ Mailpit. Mailbox ແທ້ ແລະ SPF/DKIM/DMARC ເຮັດໃນເຟດ S |
| P1-B4 | Contact API | `ContactController` + `ContactService`: validator, honeypot, time token, rate limit, ບັນທຶກ, ສົ່ງອີເມວ 2 ສະບັບ, ຕອບ JSON/redirect ([03 §3](03-backend.md)) |
| P1-B5 | ຟອມໃນໜ້າເວັບ | ແກ້ແມ່ແບບຟອມ: `action`, ຄ່າ `value` ຂອງຫົວຂໍ້, honeypot. ແກ້ `main.js`: ຂໍ token, ສົ່ງດ້ວຍ fetch, ສະແດງ error ລາຍຊ່ອງ, ສະຖານະກຳລັງສົ່ງ. ປ່ຽນຂໍ້ຄວາມ `f_ok` ([02 §5](02-frontend.md)) |
| P1-B6 | Login ແລະ ຜູ້ໃຊ້ | `Auth`, `Session`, `Csrf`, ລັອກເມື່ອໃສ່ຜິດ, `bin/create-admin.php`, ໜ້າຈັດການຜູ້ໃຊ້ |
| P1-B7 | Layout admin + Dashboard | Sidebar, topbar, flash message, ໃຊ້ tokens ສີ/ຟອນຈາກ `design.md`, ຮອງຮັບມືຖື. Dashboard ສະແດງຂໍ້ຄວາມໃໝ່ |
| P1-B8 | ກ່ອງຂໍ້ຄວາມ | ລາຍການ, ກອງ, ຄົ້ນຫາ, ລາຍລະອຽດ, ສະຖານະ, ມອບໝາຍ, ບັນທຶກພາຍໃນ, export CSV ([03 §2.3](03-backend.md)) |
| P1-B9 | ສະຄຣິບ cleanup/backup | `bin/cleanup.php` ແລະ `bin/backup.php` (DB), ແລ່ນດ້ວຍມືໃນເຄື່ອງ. ຕັ້ງ cron (03:00, 03:30) ໃນເຟດ S |
| P1-B10 | ທົດສອບ | PHPUnit (Validator, RateLimiter), ທົດສອບໃນເຄື່ອງຕາມເງື່ອນໄຂຂ້າງລຸ່ມ |

ເຮັດຂະໜານໄດ້: P1-B3 ກັບ P1-B1/B2. P1-B6–B8 ກັບ P1-B4–B5.

**ເງື່ອນໄຂຮັບວຽກ (ໃນເຄື່ອງ)**
- [x] ສົ່ງຟອມຈາກທັງ 4 ພາສາ: ອີເມວແຈ້ງເຕືອນ ແລະ ອີເມວຕອບກັບ (ພາສາທີ່ຖືກ) ເຂົ້າ Mailpit (22/10/2026: ກວດດ້ວຍ curl + browser ທັງ 4 ພາສາ)
- [x] ສົ່ງໄດ້ທັງມີ JS ແລະ ບໍ່ມີ JS
- [x] ຂໍ້ມູນຜິດ: error ຂຶ້ນໃຕ້ຊ່ອງທີ່ຖືກຕ້ອງ. ສົ່ງເກີນກຳນົດ: ໄດ້ `429` ພ້ອມເບີໂທສຳຮອງ
- [x] Honeypot ຖືກຕື່ມ → ບໍ່ບັນທຶກ
- [x] ປິດ SMTP (ທົດສອບ) → ຂໍ້ຄວາມຍັງຢູ່ໃນ admin ພ້ອມ `mail_error`
- [x] Admin: ເຂົ້າ/ອອກ, ລັອກຫຼັງໃສ່ຜິດ 5 ຄັ້ງ, editor ເຂົ້າໜ້າຜູ້ໃຊ້ບໍ່ໄດ້
- [x] ໂຄງ docroot ມີແຕ່ `public/`: `app/`, `config/`, `storage/` ຢູ່ນອກ docroot (ໃນເຄື່ອງໄດ້ 404; ກວດກັບ Apache ອີກຄັ້ງໃນເຟດ S)
- [x] `bin/backup.php` ສ້າງ backup ແລະ restore ໄດ້ (ທົດສອບ restore ໃສ່ DB ຊົ່ວຄາວ 1 ຄັ້ງ)

---

## ເຟດ 2 — ຈັດການເນື້ອຫາ + Publisher 🔴
**ເປົ້າໝາຍ:** ເນື້ອຫາທັງໝົດຢູ່ໃນ DB, ພະນັກງານແກ້ໃນ admin ແລ້ວກົດເຜີຍແຜ່ເອງ. Python build ຖືກປົດອອກ.

| ລະຫັດ | ວຽກ | ລາຍລະອຽດ |
|---|---|---|
| P2-C1 | ແປງແມ່ແບບເປັນ PHP | `templates/site/` + `lang/*.php` ຈາກ `build.py`, `strings.py` ແລະ `strings2.py` ([02 §4](02-frontend.md)) |
| P2-C2 | Seed | migration `002_content.sql`, `build/export_seed.py` → `seed.json`, `bin/seed.php` ([04 §6](04-database.md)) |
| P2-C3 | **ກວດ HTML ຄືກັນ** | PHP render ຈາກ DB ທຽບກັບ output ຂອງ Python ທັງ 276 ໜ້າ (normalize ຍະຫວ່າງ). ຕ່າງກັນ 0 ໜ້າ ຈຶ່ງໄປຕໍ່ |
| P2-C4 | Publisher | build → ກວດ → swap → rollback, lock, `publish_log`, `bin/publish.php`, ໜ້າ `/admin/publish` ([03 §4](03-backend.md)). ຟອນຈີນເປັນ subset: ຕົວອັກສອນຈີນໃໝ່ທີ່ບໍ່ຢູ່ໃນ subset ຈະສະແດງດ້ວຍຟອນຂອງເຄື່ອງ. ຕັດສິນໃຈແລ້ວ (22/10/2026): Publisher ກວດຕົວອັກສອນຈີນທຽບກັບ `noto-sans-sc-sub.txt` ແລະ ເຕືອນໃນ `/admin/publish`; ນັກພັດທະນາສ້າງ subset ໃໝ່ໃນເຄື່ອງ (`optimize_media.py --zh-font`) ແລ້ວ deploy. ເຄື່ອງມືນີ້ຕ້ອງເກັບໄວ້ຕອນ C11 |
| P2-C5 | ຄັງສື່ | ອັບ, ກວດ `finfo`, GD ສ້າງ WebP/JPEG 3 ຂະໜາດ, ສະແດງບ່ອນທີ່ໃຊ້, media picker. ຍ້າຍຮູບສິນຄ້າເກົ່າເຂົ້າ `uploads/` |
| P2-C6 | ຂະແໜງ ແລະ ກຸ່ມ | CRUD, ລາກລຽງ, ແທັບ 4 ພາສາ |
| P2-C7 | ສິນຄ້າ | CRUD, specs, ສິນຄ້າແນະນຳ, ລາກລຽງ, ສຳເນົາ, slug + redirects |
| P2-C8 | ຂ່າວ | CRUD, editor ງ່າຍ, sanitizer, draft/published |
| P2-C9 | ດາວໂຫຼດ | CRUD + ອັບ PDF ຈິງຈາກລູກຄ້າ (ປັດຈຸບັນລິ້ງເປັນ `#`) |
| P2-C10 | Dashboard | ປ້າຍ "ຍັງບໍ່ເຜີຍແຜ່", ຄຳແປທີ່ຂາດ, ປະຫວັດເຜີຍແຜ່ຫຼ້າສຸດ |
| P2-C11 | ປົດ Python | ລຶບ `build/`, ອັບເດດ `design.md` §10 ແລະ ແຜນນີ້ |
| P2-C12 | ⭐ ຜູ້ສະໜອງ | ຕາຕະລາງ `suppliers`, `product_suppliers`, seed, admin `/admin/suppliers`, ແມ່ແບບ PHP ([08](08-suppliers.md)) |

ລຳດັບ: C1 → C2 → C3 → C4 ຕ້ອງຕາມກັນ. C5–C9 ແລະ C12 ເຮັດຂະໜານກັນໄດ້ຫຼັງ C4. **C12 ເຮັດກ່ອນ C8–C9** ເພາະລູກຄ້າເນັ້ນ.

**ເງື່ອນໄຂຮັບວຽກ**
- [x] P2-C3 ຜ່ານ: HTML ຈາກ PHP ຄືກັບ Python ທຸກໜ້າ (22/10/2026: render ຈາກ MySQL 276 ໜ້າ + index/robots, ຕ່າງກັນ 0 ໜ້າ. 246 ຄືກັນທຸກ byte, 32 ຕ່າງແຕ່ `&` → `&amp;` ໃນຊື່ຂະແໜງ/ກຸ່ມ ທີ່ PHP escape. ໜ້າຜູ້ສະໜອງຕົວຢ່າງ (`--preview`, 312 ໜ້າ) ກໍຄືກັນ. ກວດດ້ວຍ `build/compare_html.py`)
- [ ] ເພີ່ມສິນຄ້າໃໝ່ພ້ອມຮູບ → ເຜີຍແຜ່ → ປາກົດໃນໜ້າຂະແໜງ, ໜ້າສິນຄ້າ, sitemap ແລະ ແຜນຜັງເວັບ ທັງ 4 ພາສາ
- [x] ເພີ່ມຜູ້ສະໜອງໃໝ່ + ໝາຍເປັນຫຼັກ + ເຊື່ອມສິນຄ້າ → ເຜີຍແຜ່ → ປາກົດໃນ mega menu, ໜ້າລວມ (ຕົວກອງຖືກ), ໜ້າລາຍລະອຽດ (ມີສິນຄ້າ), ບລັອກ "ສະໜອງໂດຍ" ໃນໜ້າສິນຄ້າ ແລະ ຊື່ຜູ້ສະໜອງໃນກາດສິນຄ້າ ທັງ 4 ພາສາ. ຕົວເລກສະຖິຕິຂອງໜ້າລວມອັບເດດເອງ (22/10/2026: ທົດສອບດ້ວຍຜູ້ສະໜອງທົດສອບ 2 ລາຍ ແລ້ວລຶບອອກ)
- [ ] ປ່ຽນ slug → URL ເກົ່າພາໄປ URL ໃໝ່
- [x] ເຜີຍແຜ່ທີ່ລົ້ມເຫຼວ (ທົດສອບໂດຍເຮັດໃຫ້ລິ້ງເສຍ) ບໍ່ແຕະເວັບທີ່ອອນລາຍ. Rollback ກັບໄປຊຸດກ່ອນໄດ້ (22/10/2026: CLI ແລະ `/admin/publish`)
- [x] ສອງຄົນກົດເຜີຍແຜ່ພ້ອມກັນ → ຄົນທີສອງໄດ້ຂໍ້ຄວາມລໍຖ້າ
- [x] ອັບໄຟລ໌ `.php` ທີ່ປອມເປັນ `.jpg` → ຖືກປະຕິເສດ (22/10/2026: ທັງຄັງສື່ ແລະ ໂລໂກ້ຜູ້ສະໜອງ)
- [ ] HTML ອັນຕະລາຍໃນຂ່າວ (`<script>`, `onerror=`) ຖືກລ້າງອອກ
- [x] ເຜີຍແຜ່ 276 ໜ້າໃຊ້ເວລາ < 10 ວິນາທີ ໃນເຄື່ອງ (22/10/2026: 0.2–0.25 ວິນາທີ ລວມການກວດລິ້ງ) (ວັດໃນ server ອີກຄັ້ງໃນເຟດ S)
- [ ] Lighthouse ຍັງ ≥ 90 (22/10/2026 ຫຼັງ C5, ໃນເຄື່ອງດ້ວຍ `php -S`: ໜ້າຂະແໜງ/ສິນຄ້າ 97, ໜ້າຫຼັກ en 90, **ໜ້າຫຼັກ zh 85**. ໜ້າເກົ່າຂອງ Python ໃນ server ດຽວກັນກໍໄດ້ 85: LCP ແມ່ນຫົວຂໍ້ hero ທີ່ລໍຟອນຈີນ, ບໍ່ແມ່ນຮູບ. ຕ້ອງແກ້ກ່ອນປິດເຟດ 2)

---

## ເຟດ 3 — ການຕັ້ງຄ່າ, FAQ, Hero, SEO 🟡
**ເປົ້າໝາຍ:** ສ່ວນທີ່ເຫຼືອຂອງເນື້ອຫາແກ້ໄດ້ຈາກ admin, SEO ຄົບ, ລະບົບດູແລຕົວເອງໄດ້.

| ລະຫັດ | ວຽກ | ລາຍລະອຽດ |
|---|---|---|
| P3-D1 | ການຕັ້ງຄ່າ | ຂໍ້ມູນຕິດຕໍ່, ທີ່ຢູ່ 4 ພາສາ, ສະຖິຕິ, ອີເມວແຈ້ງເຕືອນ, Analytics ID, noindex ([03 §2.9](03-backend.md)) |
| P3-D2 | FAQ | migration `004_extras.sql`, CRUD + ລາກລຽງ |
| P3-D3 | Hero | CRUD ສູງສຸດ 5 slide, ຮູບ/ວິດີໂອ + poster |
| P3-D4 | SEO | `seo_pages`, canonical ແບບ URL ເຕັມ, `hreflang x-default`, JSON-LD (`Organization`, `Product`, `NewsArticle`) |
| P3-D5 | Preview | `/admin/preview` render ໜ້າດຽວກ່ອນເຜີຍແຜ່ |
| P3-D6 | ປະຫວັດການແກ້ໄຂ | `activity_log` + ໜ້າເບິ່ງສຳລັບ admin |
| P3-D7 | Backup ເຕັມ | DB ທຸກມື້ + `uploads/` ທຸກອາທິດ, ເກັບ 14 ຊຸດ. ສະຄຣິບພ້ອມໃນເຄື່ອງ; cron ແລະ ສຳເນົາອອກນອກ server ຕັ້ງໃນເຟດ S |
| P3-D8 | Turnstile (ຖ້າຈຳເປັນ) | ເພີ່ມສະເພາະເມື່ອ spam ຍັງຜ່ານ honeypot + rate limit ຫຼາຍກວ່າ 5 ອັນຕໍ່ອາທິດ |

**ເງື່ອນໄຂຮັບວຽກ**
- [ ] ປ່ຽນເບີໂທໃນ admin → ເຜີຍແຜ່ → ເບີໃໝ່ຂຶ້ນທຸກໜ້າ (header, footer, ປຸ່ມລອຍ, ໜ້າຕິດຕໍ່)
- [ ] Google Rich Results Test ຜ່ານສຳລັບໜ້າສິນຄ້າ ແລະ ໜ້າຂ່າວ
- [ ] Restore ຈາກ backup ເຕັມ (DB + uploads) ໃສ່ໂຟນເດີທົດສອບໃນເຄື່ອງໄດ້

---

## ເຟດ S — ຂຶ້ນ server (subdomain) 🔴
**ເປົ້າໝາຍ:** ສິ່ງທີ່ເຮັດໃນເຄື່ອງ (ເຟດ 0–3) ແລ່ນໄດ້ແທ້ທີ່ subdomain. ລູກຄ້າເປີດເບິ່ງ ແລະ ທົດສອບ admin ໄດ້. ເວັບເກົ່າບໍ່ຖືກແຕະ.

| ລະຫັດ | ວຽກ | ລາຍລະອຽດ |
|---|---|---|
| P0-A1 | ກວດ server ລະອຽດ | ອ່ານຄ່າໃນ cPanel (ບໍ່ປ່ຽນຫຍັງ): PHP extension (`gd`+WebP, `fileinfo`, `mbstring`, `pdo_mysql`, `intl`, `zip`), Apache module (`deflate`/`brotli`, `expires`, `headers`, `rewrite`), PHP limits, ຂີດຈຳກັດອີເມວ, `composer`/`git`. ບັນທຶກໃນ [01 §5](01-architecture.md). ສິ່ງທີ່ຂາດ → ແກ້ໂຄດ ຫຼື ຂໍ host ເປີດ |
| P0-A3 | ສ້າງ subdomain | ຊື່ຕາມທີ່ລູກຄ້າເລືອກ (ຕົວຢ່າງ `new.xtklaos.com`), docroot `/home/xtklaos/xtk/public`, PHP 8.3, ກວດ SSL |
| P0-A4 | SSH key | ສ້າງ key ໃນເຄື່ອງ, import + authorize ໃນ cPanel → SSH Access, ທົດສອບ `ssh -p 32` |
| P0-A8 | Deploy script | `scripts/deploy.sh` (rsync ຜ່ານ SSH), deploy ຄັ້ງທຳອິດ ([07](07-deployment.md)) |
| S-1 | DB ແລະ config | ສ້າງ DB + user ໃນ cPanel, `config/config.php` ໃນ server, ແລ່ນ migrate + seed + `import-media.php` (ຮູບໃນ `uploads/` ບໍ່ຢູ່ໃນ git; ຕ້ອງມີ GD + WebP) + publish |
| S-2 | ອີເມວແທ້ | Mailbox `noreply@xtklaos.com`, SPF/DKIM/DMARC, ສົ່ງທົດສອບໄປ Gmail ແລະ Outlook |
| S-3 | Cron | `bin/cleanup.php` ທຸກມື້ 03:00, `bin/backup.php` 03:30, backup `uploads/` ທຸກອາທິດ |
| S-4 | ກວດ ແລະ ສົ່ງລູກຄ້າ | ແລ່ນເງື່ອນໄຂຂ້າງລຸ່ມ, ສົ່ງ URL ໃຫ້ລູກຄ້າ |

ລຳດັບ: A1 → A3 → A4 → A8 → S-1 → S-2 → S-3 → S-4.

**ເງື່ອນໄຂຮັບວຽກ**
- [ ] `https://<subdomain>` ເປີດໄດ້ທຸກໜ້າ ທັງ 4 ພາສາ, ໃບຢັ້ງຢືນ SSL ຖືກຕ້ອງ
- [ ] `https://xtklaos.com` (ເວັບເກົ່າ) ຍັງຄືເກົ່າທຸກຢ່າງ
- [ ] `.htaccess` ເຮັດວຽກ: gzip/brotli, cache headers, security headers, 404, HTTPS
- [ ] Lighthouse mobile Performance ≥ 90 ຢູ່ server. `robots.txt` ຫ້າມ index
- [ ] Deploy ໃໝ່ດ້ວຍຄຳສັ່ງດຽວໄດ້
- [ ] ສົ່ງຟອມຈາກທັງ 4 ພາສາ: ອີເມວແຈ້ງເຕືອນໄປຮອດພາຍໃນ 1 ນາທີ ແລະ ບໍ່ຢູ່ໃນ spam
- [ ] ເປີດ `/app/config/config.php` ຫຼື `/storage/` ຈາກເວັບບໍ່ໄດ້ (ໄດ້ 404)
- [ ] ເຜີຍແຜ່ 276 ໜ້າໃຊ້ເວລາ < 10 ວິນາທີ ໃນ server
- [ ] Cron ແລ່ນແລ້ວ: backup ຂອງມື້ວານມີຢູ່, ສຳເນົາອອກນອກ server ແລ້ວ 1 ຄັ້ງ

---

## ເຟດ 4 — Go-live 🔴 (ເລີ່ມເມື່ອລູກຄ້າອະນຸມັດ)
**ເປົ້າໝາຍ:** ເວັບໃໝ່ຂຶ້ນແທນເວັບເກົ່າທີ່ `xtklaos.com` ໂດຍບໍ່ມີເວລາລົ້ມ ແລະ ກັບຄືນໄດ້.

| ລະຫັດ | ວຽກ | ລາຍລະອຽດ |
|---|---|---|
| P4-E1 | ເນື້ອຫາສຸດທ້າຍ | ຮູບຖ່າຍຕົວຈິງ, ຂໍ້ມູນຈຳເພາະ, PDF, ຄຳແປໄທ/ຈີນທີ່ເຈົ້າຂອງພາສາກວດແລ້ວ, ຂໍ້ຄວາມກົດໝາຍ ([design.md §11](../../design.md)), ⭐ ຂໍ້ມູນຜູ້ສະໜອງຄົບ + ການອະນຸຍາດໃຊ້ໂລໂກ້ ([08 §7](08-suppliers.md)) |
| P4-E2 | ກວດຄວາມປອດໄພ | ໄລ່ checklist [03 §6](03-backend.md), ປ່ຽນລະຫັດຜ່ານ cPanel, ກວດ headers ດ້ວຍ securityheaders.com |
| P4-E3 | Backup ເວັບເກົ່າ | ສຳເນົາ `public_html/` ທັງໝົດເປັນ zip ແລະ ເກັບໄວ້ນອກ server |
| P4-E4 | ສະຫຼັບໂດເມນ | ເບິ່ງ [07-deployment.md §6](07-deployment.md). ຕັດສິນໃຈວິທີກັບລູກຄ້າ ແລະ host |
| P4-E5 | ເປີດໃຫ້ Google | `noindex` = false, ເຜີຍແຜ່ໃໝ່, ສົ່ງ `sitemap.xml` ໃນ Google Search Console, ເປີດ Analytics |
| P4-E6 | Redirect | Subdomain → 301 ໄປ `https://xtklaos.com`, `www` → ໂດເມນຫຼັກ |
| P4-E7 | ຝຶກອົບຮົມ | ຄູ່ມືສັ້ນສຳລັບພະນັກງານ (ພາສາລາວ): ເຂົ້າລະບົບ, ຕອບຂໍ້ຄວາມ, ເພີ່ມສິນຄ້າ/ຂ່າວ, ເຜີຍແຜ່. ສອນ 1 ຄັ້ງ |
| P4-E8 | ຕິດຕາມ 2 ອາທິດ | ກວດ log error, ອີເມວໄປຮອດ, ຄວາມໄວ, Search Console ທຸກມື້ໃນອາທິດທຳອິດ |
| P4-E9 | (ທາງເລືອກ) Cloudflare | ຍ້າຍ DNS ໄປ Cloudflare ຟຣີ ເພື່ອ CDN ແລະ ກັນການໂຈມຕີ |

**ເງື່ອນໄຂຮັບວຽກ**
- [ ] `https://xtklaos.com` ສະແດງເວັບໃໝ່ທຸກພາສາ, SSL ຖືກຕ້ອງ
- [ ] ອີເມວຂອງບໍລິສັດ (`@xtklaos.com`) ຍັງຮັບ-ສົ່ງໄດ້ປົກກະຕິ
- [ ] ກັບໄປເວັບເກົ່າໄດ້ພາຍໃນ 5 ນາທີ ຖ້າມີບັນຫາ (ທົດສອບແຜນກັບຄືນແລ້ວ)
- [ ] ລູກຄ້າເຊັນຮັບວຽກ

---

## ຫຼັງ go-live (🟢 ບໍ່ຢູ່ໃນຂອບເຂດປັດຈຸບັນ)
- ລາຍງານສະຖິຕິຂໍ້ຄວາມລາຍເດືອນໃນ dashboard.
- ຄົ້ນຫາສິນຄ້າໃນໜ້າເວັບ (ດັດຊະນີ JSON ທີ່ Publisher ສ້າງ, ຄົ້ນໃນ browser, ບໍ່ຕ້ອງໃຊ້ server).
- ລືມລະຫັດຜ່ານຜ່ານອີເມວ ແລະ 2FA ສຳລັບ admin.
