# 03 — ຫຼັງບ້ານ (PHP + MySQL)

## 1. ເທັກໂນໂລຢີ
| ລາຍການ | ເລືອກ | ໝາຍເຫດ |
|---|---|---|
| ພາສາ | PHP 8.3 | ຕັ້ງໃນ cPanel → MultiPHP Manager ສະເພາະ subdomain |
| Framework | ບໍ່ໃຊ້ | ໂຄດນ້ອຍ, ໄວ, ບໍ່ຕ້ອງອັບເດດ framework |
| Autoload | Composer PSR-4 (`App\` → `src/`) | ຖ້າ server ບໍ່ມີ composer, ສ້າງ `vendor/` ໃນເຄື່ອງແລ້ວອັບຂຶ້ນ |
| Library ພາຍນອກ | `phpmailer/phpmailer` ເທົ່ານັ້ນ | ເພີ່ມ library ອື່ນຕ້ອງມີເຫດຜົນ ແລະ ບັນທຶກໃນ changelog |
| DB | PDO MySQL, `utf8mb4_unicode_ci`, `ERRMODE_EXCEPTION`, prepared statements | |
| Template | PHP ທຳມະດາ + ຟັງຊັນ `e()` ສຳລັບ escape | ທັງ admin ແລະ Publisher |
| ຮູບ | GD (ສ້າງ WebP + ຫຍໍ້ຂະໜາດ) | ຕ້ອງກວດວ່າ GD ຮອງຮັບ WebP (P0-A1, ເຟດ S); ໃນເຄື່ອງໃຊ້ GD ທີ່ມີ WebP |
| Session | PHP session ເກັບໃນ `storage/sessions/` | cookie: `Secure`, `HttpOnly`, `SameSite=Lax` |

## 2. ໂມດູນຂອງ Admin

ລຳດັບຄວາມສຳຄັນ: 🔴 ຕ້ອງມີ, 🟡 ຄວນມີ, 🟢 ເພີ່ມພາຍຫຼັງ. ເຟດທີ່ສ້າງເບິ່ງໃນ [06-phases.md](06-phases.md).

### 2.1 🔴 ເຂົ້າສູ່ລະບົບ ແລະ ຜູ້ໃຊ້ (`/admin/login`, `/admin/users`)
- ເຂົ້າດ້ວຍອີເມວ + ລະຫັດຜ່ານ. ເກັບລະຫັດດ້ວຍ `password_hash()` (bcrypt, cost 12).
- ໃສ່ຜິດ 5 ຄັ້ງໃນ 15 ນາທີ (ຕໍ່ອີເມວ ຫຼື ຕໍ່ IP) → ລັອກ 15 ນາທີ. ບັນທຶກໃນ `login_attempts`.
- ສ້າງ session ID ໃໝ່ຫຼັງເຂົ້າສຳເລັດ. ອອກເອງເມື່ອບໍ່ໃຊ້ 2 ຊົ່ວໂມງ.
- ລືມລະຫັດຜ່ານ: admin ຕັ້ງໃຫ້ໃໝ່ໃນໜ້າຜູ້ໃຊ້. ບໍ່ມີລິ້ງ reset ທາງອີເມວໃນເຟດທຳອິດ.
- ສິດ:

| ສິດ | ເຮັດໄດ້ |
|---|---|
| `admin` | ທຸກຢ່າງ ລວມທັງຜູ້ໃຊ້ ແລະ ການຕັ້ງຄ່າ |
| `editor` | ເນື້ອຫາ, ກ່ອງຂໍ້ຄວາມ, ສື່, ເຜີຍແຜ່. ບໍ່ເຂົ້າຜູ້ໃຊ້ ແລະ ການຕັ້ງຄ່າ |

- ຜູ້ໃຊ້ຄົນທຳອິດສ້າງດ້ວຍ CLI: `php bin/create-admin.php`.

### 2.2 🔴 Dashboard (`/admin/`)
- ຈຳນວນຂໍ້ຄວາມໃໝ່ ພ້ອມລິ້ງໄປກ່ອງຂໍ້ຄວາມ.
- ສະຖານະເຜີຍແຜ່: ຄັ້ງລ່າສຸດເມື່ອໃດ ແລະ ໂດຍໃຜ. ຖ້າມີການແກ້ຫຼັງຈາກນັ້ນ, ສະແດງປ້າຍ **"ມີການປ່ຽນແປງທີ່ຍັງບໍ່ເຜີຍແຜ່"** ພ້ອມປຸ່ມເຜີຍແຜ່.
- ລາຍການຄຳແປທີ່ຍັງຂາດ (ຊ່ອງ `th`/`zh` ທີ່ຫວ່າງ).

### 2.3 🔴 ກ່ອງຂໍ້ຄວາມ (`/admin/messages`)
- ຕາຕະລາງ: ວັນທີ, ຊື່, ອົງກອນ, ຫົວຂໍ້, ພາສາ, ສະຖານະ. ກອງຕາມສະຖານະ/ຫົວຂໍ້/ຊ່ວງວັນທີ, ຄົ້ນຫາຊື່/ອີເມວ/ເບີໂທ, ແບ່ງໜ້າ 25 ລາຍການ.
- ສະຖານະ: `new` → `in_progress` → `done`. ມີ `spam` ສຳລັບຂໍ້ຄວາມຂີ້ເຫຍື້ອ.
- ໜ້າລາຍລະອຽດ: ຂໍ້ມູນທັງໝົດ, ປຸ່ມ "ໂທ", "ຕອບທາງອີເມວ" (`mailto:`) ແລະ "WhatsApp", ມອບໝາຍໃຫ້ຜູ້ໃຊ້, ບັນທຶກພາຍໃນ (`message_notes`).
- Export CSV ຕາມຕົວກອງ (UTF-8 BOM ເພື່ອໃຫ້ Excel ອ່ານພາສາລາວຖືກ).
- ລຶບອັດຕະໂນມັດ: ຂໍ້ຄວາມ `spam` ອາຍຸເກີນ 30 ວັນ (cron).

### 2.4 🔴 ຜະລິດຕະພັນ (`/admin/divisions`, `/admin/groups`, `/admin/products`)
- **ຂະແໜງ** (5 ອັນ): ຊື່, ຄຳອະທິບາຍສັ້ນ, ຄຳອະທິບາຍຍາວ (4 ພາສາ), ຮູບ, ໄອຄອນ, ລຳດັບ, ເປີດ/ປິດ. ລຶບບໍ່ໄດ້ຖ້າຍັງມີກຸ່ມ.
- **ກຸ່ມສິນຄ້າ**: ຂຶ້ນກັບຂະແໜງ. ຊື່, ຄຳອະທິບາຍ (4 ພາສາ), ໄອຄອນ, ລຳດັບ.
- **ສິນຄ້າ**:
  - ຊື່ (ໃຊ້ຊື່ດຽວທຸກພາສາ ຕາມປັດຈຸບັນ), slug (ສ້າງອັດຕະໂນມັດຈາກຊື່ ແລະ ແກ້ໄດ້), ກຸ່ມ, ຮູບ.
  - ຄຳອະທິບາຍສັ້ນ ແລະ ລາຍລະອຽດ (4 ພາສາ), ລຸ້ນ/ລາຍການຍ່ອຍ.
  - ຂໍ້ມູນຈຳເພາະ: ແຖວ ປ້າຍ/ຄ່າ ທີ່ເພີ່ມ/ລຶບ/ລາກລຽງໄດ້ (`product_specs`).
  - ທຸງ: ສະແດງ, **ສິນຄ້າແນະນຳ** (ໜ້າຫຼັກ, ສູງສຸດ 8), ມີໜ້າລາຍລະອຽດ (ສິນຄ້າບໍ່ມີຮູບ = ກາດ "ສອບຖາມລາຄາ" ຕາມປັດຈຸບັນ).
  - SEO title/description (4 ພາສາ, ບໍ່ບັງຄັບ).
  - ລາກລຽງລຳດັບໃນກຸ່ມ. ສຳເນົາສິນຄ້າເພື່ອສ້າງອັນຄ້າຍກັນໄວ.
- ຖ້າປ່ຽນ slug ຂອງສິນຄ້າທີ່ເຜີຍແຜ່ແລ້ວ, ລະບົບເກັບ slug ເກົ່າໃນ `redirects` ແລະ Publisher ສ້າງໜ້າ redirect ໃຫ້.

### 2.5 🔴 ຂ່າວ (`/admin/news`)
- ຫົວຂໍ້, ສະຫຼຸບ, ເນື້ອຫາ (4 ພາສາ), slug, ໝວດ (`product` / `company` / `event`), ຮູບປົກ, ວັນທີ, ສະຖານະ `draft`/`published`.
- ເນື້ອຫາໃຊ້ editor ແບບງ່າຍ: ຫົວຂໍ້ຍ່ອຍ, ຕົວໜາ, ລາຍການ, ລິ້ງ, ຮູບ. ເກັບເປັນ HTML ທີ່ຜ່ານ allow-list (`p h2 h3 strong em ul ol li a img figure figcaption blockquote`). ທຸກ attribute ນອກ allow-list ຖືກລຶບ.
- ປະກາດຮັບສະໝັກງານ **ບໍ່ມີ**. ຖ້າຕ້ອງການ ໃຫ້ລົງເປັນຂ່າວໝວດ `company` ໂດຍບອກໃຫ້ໂທ ຫຼື ສົ່ງອີເມວ.

### 2.6 🔴 ໄຟລ໌ດາວໂຫຼດ (`/admin/downloads`)
- ຊື່ ແລະ ຄຳອະທິບາຍ (4 ພາສາ), ໄຟລ໌ PDF (≤ 20 MB), ຂະແໜງ (ຫຼື "ບໍລິສັດ"), ລຳດັບ, ເປີດ/ປິດ.
- ສະແດງຂະໜາດໄຟລ໌ຢູ່ໜ້າເວັບອັດຕະໂນມັດ.

### 2.7 🔴 ຄັງສື່ (`/admin/media`)
- ອັບຮູບ JPG/PNG/WebP (≤ 8 MB) ແລະ PDF (≤ 20 MB).
- ກວດປະເພດດ້ວຍ `finfo` (ບໍ່ເຊື່ອນາມສະກຸນໄຟລ໌). **ສ້າງຮູບໃໝ່ຜ່ານ GD ທຸກຄັ້ງ** ເພື່ອລ້າງ metadata ແລະ ໂຄດທີ່ອາດແຝງມາ.
- ສ້າງ 3 ຂະໜາດ (480/960/1600 px) ເປັນ WebP + JPEG. ຕັ້ງຊື່ໄຟລ໌ເປັນ hash (`ab12cd34.webp`).
- ສະແດງວ່າສື່ນີ້ຖືກໃຊ້ຢູ່ໃສ. ລຶບບໍ່ໄດ້ຖ້າຍັງຖືກໃຊ້.

### 2.8 🔴 ເຜີຍແຜ່ (`/admin/publish`)
ເບິ່ງຂໍ້ 4.

### 2.9 🟡 ການຕັ້ງຄ່າ (`/admin/settings`) — ສະເພາະ admin
| ກຸ່ມ | ຄ່າ |
|---|---|
| ຕິດຕໍ່ | ເບີໂທ, ມືຖື/ສາຍດ່ວນ, ແຟັກ, ອີເມວ, ເບີ WhatsApp, ທີ່ຢູ່ (4 ພາສາ), ລິ້ງ Google Maps |
| ສະຖິຕິໜ້າຫຼັກ | ປີກໍ່ຕັ້ງ (2016), ປະສົບການ (20+), ຈຳນວນຂະແໜງ (ນັບອັດຕະໂນມັດ), ຈຳນວນສິນຄ້າ (ນັບອັດຕະໂນມັດ) |
| ອີເມວແຈ້ງເຕືອນ | ລາຍຊື່ອີເມວທີ່ຮັບແຈ້ງເຕືອນຟອມ (ຫຼາຍອີເມວໄດ້) |
| ອື່ນໆ | Google Analytics ID, ເປີດ/ປິດ noindex |

### 2.10 🟡 FAQ ແລະ Hero
- **FAQ** (`/admin/faqs`): ຄຳຖາມ/ຄຳຕອບ 4 ພາສາ, ລຳດັບ, ເປີດ/ປິດ.
- **Hero** (`/admin/hero`): ສູງສຸດ 5 slide. ແຕ່ລະອັນມີຮູບ ຫຼື ວິດີໂອ, ຫົວຂໍ້ນ້ອຍ, ຫົວຂໍ້, ຂໍ້ຄວາມ, ປ້າຍ ແລະ ລິ້ງປຸ່ມ (4 ພາສາ), ລຳດັບ.

### 2.11 🟡 SEO ໜ້າຄົງທີ່
- Title/description 4 ພາສາ ສຳລັບໜ້າ about, vision, services, contact, downloads, faq (`seo_pages`).

### 2.12 🔴 ⭐ ຜູ້ສະໜອງ (`/admin/suppliers`)
ລູກຄ້າເນັ້ນເປັນພິເສດ. ລາຍລະອຽດເຕັມຢູ່ [08-suppliers.md §5](08-suppliers.md).
ເປົ້າໝາຍ: ໃຫ້ເຫັນວ່າ XTK ຂາຍສິນຄ້າຈາກຜູ້ສະໜອງໃດ. ສະນັ້ນ **ການເຊື່ອມສິນຄ້າ ↔ ຜູ້ສະໜອງ ສຳຄັນທີ່ສຸດ**.
- CRUD, ລາກລຽງ, ສະວິດ "ຫຼັກ" (ສູງສຸດ 6) ແລະ ສະແດງ/ເຊື່ອງ. ລາຍການສະແດງຈຳນວນສິນຄ້າຂອງແຕ່ລະອັນ.
- ຊື່, slug, ປະເທດ (ISO), ເວັບໄຊ, ຂໍ້ຄວາມ 4 ພາສາ, ໂລໂກ້ (+ `logo_on_dark`). ທາງເລືອກ: ປະເພດການເປັນຕົວແທນ, ປີທີ່ເລີ່ມ, ໃບຢັ້ງຢືນ.
- ເຊື່ອມສິນຄ້າໄດ້ທັງ 2 ທາງ (ຫຼາຍຕໍ່ຫຼາຍ): ຊ່ອງ "ຜູ້ສະໜອງ" (ເລືອກຫຼາຍອັນ) ໃນຟອມສິນຄ້າ ແລະ "ເພີ່ມສິນຄ້າ" ໃນໜ້າຜູ້ສະໜອງ.
- Dashboard ສະແດງຈຳນວນສິນຄ້າທີ່ຍັງບໍ່ລະບຸຜູ້ສະໜອງ.
- ໂລໂກ້ PNG/WebP ເທົ່ານັ້ນສຳລັບ admin. SVG ຜ່ານນັກພັດທະນາຫຼັງລ້າງແລ້ວ.
- ລຶບຜູ້ສະໜອງ → ລຶບການເຊື່ອມໃນ `product_suppliers` (ບໍ່ລຶບສິນຄ້າ). ຖ້າເຄີຍເຜີຍແຜ່, ເພີ່ມ redirect ຈາກ `supplier-<slug>` ໄປ `suppliers`.

### 2.13 🟢 ປະຫວັດການແກ້ໄຂ ແລະ Backup
- `activity_log`: ໃຜ ເຮັດຫຍັງ ກັບລາຍການໃດ ເມື່ອໃດ ຈາກ IP ໃດ. ສະແດງໃນ `/admin/activity` (ສະເພາະ admin).
- Backup: ເບິ່ງ [07-deployment.md](07-deployment.md).

## 3. Contact API

### `POST /api/contact.php`
**Request** (`application/x-www-form-urlencoded` ຫຼື `multipart/form-data`): ຊ່ອງຕາມ [02-frontend.md §5](02-frontend.md).

**ຂັ້ນຕອນ** (ຢຸດທັນທີເມື່ອຂັ້ນໃດບໍ່ຜ່ານ):
1. ຮັບສະເພາະ `POST`. ກວດ `Origin`/`Referer` ວ່າມາຈາກໂດເມນຂອງເຮົາ.
2. `website` ບໍ່ຫວ່າງ → ຕອບ `200 ok` ປອມ ແລະ ບໍ່ບັນທຶກ (ບໍ່ໃຫ້ bot ຮູ້).
3. `ts`: ຖ້າມີ, ລາຍເຊັນ HMAC ຕ້ອງຖືກ ແລະ ເວລາຕ້ອງຢູ່ລະຫວ່າງ 3 ວິນາທີ ຫາ 24 ຊົ່ວໂມງ. ບໍ່ຜ່ານ → `422`. ຖ້າບໍ່ມີ (browser ບໍ່ມີ JS) → ຮັບໄດ້ ແຕ່ໃຊ້ rate limit ທີ່ເຂັ້ມກວ່າໃນຂໍ້ 4.
4. Rate limit: ມີ `ts` ສູງສຸດ 5 ຄັ້ງ / 10 ນາທີ / IP, ບໍ່ມີ `ts` ສູງສຸດ 2 ຄັ້ງ / 10 ນາທີ / IP, ແລະ ລວມບໍ່ເກີນ 20 ຄັ້ງ / ວັນ / IP. ເກີນ → `429`.
5. ກວດຂໍ້ມູນ. ຜິດ → `422` ພ້ອມ error ລາຍຊ່ອງ.
6. `INSERT` ລົງ `messages` ພ້ອມ `ip`, `user_agent`, `lang` ແລະ `page_url`.
7. ສົ່ງອີເມວແຈ້ງທີມງານ (ຂໍ້ 5). ຖ້າລົ້ມເຫຼວ, ບັນທຶກ `mail_error` ແຕ່ຍັງຕອບສຳເລັດ.
8. ສົ່ງອີເມວຕອບກັບລູກຄ້າເປັນພາສາ `lang`.
9. ຕອບ JSON. ຖ້າເປັນການສົ່ງແບບບໍ່ມີ JS, redirect `303` ໄປ `/{lang}/contact.html?sent=1`.

**Response**
```json
200 {"ok": true}
422 {"ok": false, "errors": {"email": "invalid", "message": "too_short"}}
429 {"ok": false, "error": "rate_limited"}
500 {"ok": false, "error": "server"}
```
ລະຫັດ error ເປັນຄຳສັ້ນ. JS ແປເປັນຂໍ້ຄວາມຕາມພາສາຂອງໜ້າ.

### `GET /api/contact.php?token=1`
ຕອບ `{"ts":"<unix time>.<hmac>"}` ພ້ອມ `Cache-Control: no-store`. Secret ຂອງ HMAC ຢູ່ໃນ `config.php`.

### Endpoint ອື່ນ
ບໍ່ມີ. Admin render ຝັ່ງ server, ບໍ່ຕ້ອງມີ REST API ສາທາລະນະ. ຖ້າມີແອັບໃນອະນາຄົດ ຄ່ອຍເພີ່ມ `/api/v1/` ແບບອ່ານຢ່າງດຽວ.

## 4. Publisher (ເຄື່ອງສ້າງໜ້າເວັບ)

### ຂັ້ນຕອນ
1. ຂໍ lock (`storage/publish.lock`, `flock`). ຖ້າມີຄົນກຳລັງເຜີຍແຜ່ຢູ່ → ແຈ້ງ "ກຳລັງເຜີຍແຜ່, ລອງໃໝ່ພາຍຫຼັງ".
2. ສ້າງ `publish_log` ສະຖານະ `running`.
3. ໂຫຼດຂໍ້ມູນທັງໝົດຈາກ DB ເຂົ້າໜ່ວຍຄວາມຈຳໃນຄັ້ງດຽວ (ປະມານ 10 query, ບໍ່ query ທຸກໜ້າ).
4. Render ທຸກໜ້າ × 4 ພາສາ ໃສ່ `storage/build/<timestamp>/`.
5. ສ້າງ `sitemap.xml`, `index.html` ຂອງ root ແລະ ໜ້າ redirect ຈາກ `redirects`.
6. **ກວດຜົນ**: ທຸກໜ້າທີ່ຄາດໄວ້ມີຄົບ ແລະ ບໍ່ຫວ່າງ, ບໍ່ມີລິ້ງພາຍໃນທີ່ເສຍ. ບໍ່ຜ່ານ → ຢຸດ, ບັນທຶກ error ແລະ **ບໍ່ແຕະເວັບທີ່ອອນລາຍຢູ່**.
7. ສັບປ່ຽນ: ແຕ່ລະພາສາ `rename(docroot/lo, storage/build/old/lo)` ແລ້ວ `rename(build/lo, docroot/lo)`. ເວລາທີ່ໜ້າຫາຍມີພຽງບໍ່ເທົ່າໃດ millisecond ຕໍ່ພາສາ.
8. ເກັບ build ເກົ່າໄວ້ 3 ຊຸດເພື່ອ **rollback** ດ້ວຍປຸ່ມດຽວ. ຊຸດເກົ່າກວ່ານັ້ນລຶບອອກ.
9. ອັບເດດ `publish_log` ເປັນ `success` ພ້ອມຈຳນວນໜ້າ ແລະ ເວລາທີ່ໃຊ້. ປົດ lock.

### ສັ່ງຈາກ CLI
`php bin/publish.php` ໃຊ້ໂຄດດຽວກັນ, ສຳລັບ deploy ແລະ ທົດສອບ.

### Preview (🟡)
`/admin/preview?page=product-ct-scan&lang=lo` render ໜ້າດຽວຈາກຂໍ້ມູນປັດຈຸບັນ (ລວມທີ່ຍັງບໍ່ເຜີຍແຜ່). ສະເພາະຄົນທີ່ເຂົ້າລະບົບ ແລະ ມີ `noindex`.

## 5. ອີເມວ
- ສົ່ງຜ່ານ SMTP ຂອງ cPanel (`mail.xtklaos.com:465`, SSL) ດ້ວຍ mailbox `noreply@xtklaos.com`. ລະຫັດຜ່ານຢູ່ໃນ `config.php`.
- **ແຈ້ງທີມງານ**: ໄປຫາລາຍຊື່ໃນ `settings.notify_emails`. `Reply-To` = ອີເມວລູກຄ້າ ເພື່ອໃຫ້ກົດ Reply ຕອບລູກຄ້າໄດ້ເລີຍ. ຫົວຂໍ້ `[XTK ເວັບ] <ຫົວຂໍ້> — <ຊື່>`. ມີລິ້ງໄປໜ້າຂໍ້ຄວາມໃນ admin.
- **ຕອບກັບລູກຄ້າ**: ພາສາຕາມ `lang`, ຂອບໃຈ, ສຳເນົາຂໍ້ຄວາມ, ເບີໂທ ແລະ WhatsApp. ບໍ່ມີລິ້ງອື່ນ.
- ແມ່ແບບອີເມວຢູ່ `templates/email/` (HTML + ຂໍ້ຄວາມທຳມະດາ).
- ຕັ້ງ SPF, DKIM ແລະ DMARC ໃນ cPanel → Email Deliverability ເພື່ອບໍ່ໃຫ້ຕົກ spam.

## 6. ຄວາມປອດໄພ (Checklist)
| ໄພ | ວິທີປ້ອງກັນ |
|---|---|
| SQL injection | PDO prepared statements ທຸກ query. ຫ້າມຕໍ່ string ເຂົ້າ SQL |
| XSS | Escape ທຸກ output ດ້ວຍ `e()`. HTML ຂອງຂ່າວຜ່ານ allow-list sanitizer |
| CSRF | Token ໃນທຸກຟອມ POST ຂອງ admin. ກວດດ້ວຍ `hash_equals` |
| ເດົາລະຫັດຜ່ານ | ລັອກຕາມຂໍ້ 2.1, ລະຫັດຢ່າງໜ້ອຍ 12 ຕົວ |
| Session hijack | `session_regenerate_id` ຫຼັງ login, cookie `Secure`/`HttpOnly`/`SameSite` |
| ອັບໄຟລ໌ອັນຕະລາຍ | ກວດ `finfo`, ສ້າງຮູບໃໝ່ດ້ວຍ GD, ຊື່ hash, `uploads/` ມີ `.htaccess` ຫ້າມແລ່ນ PHP |
| ອ່ານໂຄດ/config | ໂຄດຢູ່ນອກ docroot. `storage/` ແລະ `config/` ບໍ່ເຂົ້າເຖິງຈາກເວັບ |
| Spam | Honeypot, time token, rate limit. ຖ້າຍັງມີຫຼາຍ ເພີ່ມ Cloudflare Turnstile |
| Clickjacking ແລະ ອື່ນໆ | Header: `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, CSP ສຳລັບ admin |
| ເປີດເຜີຍ error | `display_errors=Off` ໃນ production. Error ລົງ `storage/logs/` |
| ຂໍ້ມູນສ່ວນຕົວ | ຂໍ້ຄວາມລູກຄ້າເຫັນສະເພາະຄົນທີ່ເຂົ້າລະບົບ. Export CSV ບັນທຶກໃນ `activity_log` |

## 7. ມາດຕະຖານໂຄດ
- PSR-12. `declare(strict_types=1);` ທຸກໄຟລ໌.
- ໜຶ່ງ class ຕໍ່ໄຟລ໌. Controller ບາງ, logic ຢູ່ໃນ Repository/Service.
- Migration ເປັນໄຟລ໌ SQL ມີເລກລຳດັບ (`database/migrations/001_init.sql`), ແລ່ນດ້ວຍ `php bin/migrate.php`, ບັນທຶກໃນ `schema_migrations`.
- ທຸກຂໍ້ຄວາມໃນ admin ເປັນພາສາລາວ.
- ທົດສອບ: PHPUnit ສຳລັບ Validator, Sanitizer, RateLimiter, Slug ແລະ Publisher (ຈຳນວນໜ້າ, ລິ້ງ). ແລ່ນໃນເຄື່ອງກ່ອນ deploy.
