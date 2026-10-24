# 02 — ໜ້າບ້ານ (ເວັບສາທາລະນະ)

ໜ້າບ້ານແມ່ນໜ້າເວັບທີ່ລູກຄ້າເຫັນ. ການອອກແບບ (ສີ, ຟອນ, ໄລຍະຫ່າງ, ອົງປະກອບ) ກຳນົດໄວ້ໃນ [design.md](../../design.md). ໄຟລ໌ນີ້ກຳນົດ **ການເຮັດວຽກ** ຂອງໜ້າບ້ານ.

## 1. ຫຼັກການ
1. **HTML ສຳເລັດຮູບ.** ທຸກໜ້າຖືກສ້າງລ່ວງໜ້າໂດຍ Publisher. ຜູ້ເຂົ້າຊົມບໍ່ແຕະ PHP ຫຼື DB, ຍົກເວັ້ນຕອນສົ່ງຟອມຕິດຕໍ່.
2. **Progressive enhancement.** ອ່ານ ແລະ ນຳທາງໄດ້ຄົບເຖິງບໍ່ມີ JS. JS ([assets/js/main.js](../../assets/js/main.js)) ເພີ່ມເມນູມືຖື, slider, ແທັບ, ຕົວກອງ ແລະ ການສົ່ງຟອມແບບບໍ່ໂຫຼດໜ້າໃໝ່.
3. **ບໍ່ມີ framework ຝັ່ງ browser.** CSS ໄຟລ໌ດຽວ ແລະ JS ໄຟລ໌ດຽວ. ຫ້າມເພີ່ມ jQuery, React ຫຼື ຄ້າຍຄືກັນ.
4. **ທຸກພາສາມີໂຄງດຽວກັນ.** `lo/`, `en/`, `th/`, `zh/` ມີຊື່ໄຟລ໌ຄືກັນ. ສະຫຼັບພາສາແລ້ວຢູ່ໜ້າເດີມ.
5. **ທາງຕິດຕໍ່ມີ 3 ທາງ:** ໜ້າຕິດຕໍ່ (`contact.html`), WhatsApp ແລະ ໂທ. ບໍ່ມີໜ້າຂໍໃບສະເໜີລາຄາ ແລະ ໜ້າສະໝັກວຽກ.

## 2. ລາຍການໜ້າ (69 ໜ້າຕໍ່ພາສາ)

| ກຸ່ມ | ໜ້າ | ໄຟລ໌ | ຂໍ້ມູນມາຈາກ (ຫຼັງມີຫຼັງບ້ານ) |
|---|---|---|---|
| ຫຼັກ | ໜ້າຫຼັກ | `index.html` | `hero_slides`, `products` (featured), `news`, `settings` |
| ບໍລິສັດ | ຂໍ້ມູນບໍລິສັດ | `about.html` | ຂໍ້ຄວາມ UI + `settings` |
| | ວິໄສທັດ ແລະ ພາລະກິດ | `vision.html` | ຂໍ້ຄວາມ UI |
| | ຂ່າວສານ | `news.html` | `news` |
| | ໜ້າຂ່າວແຕ່ລະອັນ | `news-<slug>.html` | `news` |
| ຜະລິດຕະພັນ | ລວມ 5 ຂະແໜງ | `products.html` | `divisions` |
| | ໜ້າຂະແໜງ 5 ໜ້າ | `<division>.html` | `divisions`, `product_groups`, `products` |
| | ໜ້າສິນຄ້າ 49 ໜ້າ | `product-<slug>.html` | `products`, `product_specs`, `media`, ⭐ `product_suppliers` (ບລັອກ "ສະໜອງໂດຍ") |
| ⭐ ຜູ້ສະໜອງ | ລວມຜູ້ສະໜອງ | `suppliers.html` | `suppliers`, `product_suppliers` ([08](08-suppliers.md)) |
| | ໜ້າຜູ້ສະໜອງແຕ່ລະອັນ | `supplier-<slug>.html` | `suppliers`, `products` |
| | ບໍລິການຫຼັງການຂາຍ | `services.html` | ຂໍ້ຄວາມ UI + `settings` |
| | ຕິດຕໍ່ພົວພັນ | `contact.html` | `settings` + ຟອມຕິດຕໍ່ |
| ຊ່ວຍເຫຼືອ | ດາວໂຫຼດ | `downloads.html` | `downloads`, `media` |
| | ຄຳຖາມທີ່ພົບເລື້ອຍ | `faq.html` | `faqs` |
| ກົດໝາຍ | ຄວາມເປັນສ່ວນຕົວ, ຂໍ້ກຳນົດ | `privacy.html`, `terms.html` | ຂໍ້ຄວາມ UI |
| ລະບົບ | ແຜນຜັງເວັບ, 404 | `sitemap.html`, `404.html` | ສ້າງອັດຕະໂນມັດ |

ນອກຈາກນີ້ Publisher ຍັງສ້າງ `/index.html` (ສົ່ງຕໍ່ໄປ `lo/`), `/sitemap.xml` ແລະ `/robots.txt`.

ຈຳນວນໜ້າສິນຄ້າ, ໜ້າຂ່າວ ແລະ ໜ້າຜູ້ສະໜອງຈະປ່ຽນໄປຕາມຂໍ້ມູນໃນ admin. ໜ້າຜູ້ສະໜອງຍັງບໍ່ມີ (ລໍຂໍ້ມູນ) ແລະ ຈະເພີ່ມ 1 + N ໜ້າຕໍ່ພາສາ.

ເມນູຫຼັກ (ເປົ້າໝາຍ): ກ່ຽວກັບພວກເຮົາ · ຜະລິດຕະພັນ · **ຜູ້ສະໜອງ** · ໂຊລູຊັນ · ບໍລິການຫຼັງການຂາຍ · ຂ່າວສານ · ຕິດຕໍ່ພົວພັນ. ລາຍລະອຽດ UI ຂອງຜູ້ສະໜອງຢູ່ໃນ [08-suppliers.md](08-suppliers.md).

## 3. ປຸ່ມ ແລະ ທາງໄປ (CTA) ຫຼັງຕັດ quote/careers
| ບ່ອນ | ປຸ່ມຫຼັກ | ປຸ່ມຮອງ |
|---|---|---|
| Header (ທຸກໜ້າ) | ຕິດຕໍ່ພົວພັນ → `contact.html` | — |
| ເມນູມືຖື | ຕິດຕໍ່ພົວພັນ | — |
| Hero ໜ້າຫຼັກ | ລິ້ງຂອງແຕ່ລະ slide | ຕິດຕໍ່ພົວພັນ |
| ແຖບ CTA ທ້າຍໜ້າ | ຕິດຕໍ່ພົວພັນ | WhatsApp |
| ໜ້າສິນຄ້າ | ຕິດຕໍ່ພົວພັນ | WhatsApp |
| ກາດສິນຄ້າທີ່ບໍ່ມີຮູບ | "ສອບຖາມລາຄາ" → `contact.html` | — |
| ໜ້າລວມສິນຄ້າ, ໂຊລູຊັນ | ເບິ່ງສິນຄ້າ | ຕິດຕໍ່ພົວພັນ |
| ປຸ່ມລອຍ (ທຸກໜ້າ) | WhatsApp, ໂທ, ກັບຂຶ້ນເທິງ | — |

ເມນູ "ກ່ຽວກັບພວກເຮົາ" ມີ 3 ກາດ: ຂໍ້ມູນບໍລິສັດ, ວິໄສທັດ, ດາວໂຫຼດ.

## 4. ແມ່ແບບ (Templates)
ເມື່ອມີຫຼັງບ້ານ, ແມ່ແບບໃນ `build/build.py` (Python, ລຶບແລ້ວໃນ P2-C11, ເບິ່ງໃນປະຫວັດ git) ຈະຖືກຂຽນໃໝ່ເປັນ PHP (ເບິ່ງ [05-folder-structure.md](05-folder-structure.md)). **HTML ທີ່ອອກມາຕ້ອງຄືເກົ່າທຸກຕົວອັກສອນ** ກ່ອນຈະເລີ່ມປ່ຽນໜ້າຕາ. ວຽກ P2-C3 ໃນ [06-phases.md](06-phases.md) ກວດເລື່ອງນີ້.

| ຟັງຊັນ Python ປັດຈຸບັນ | ແມ່ແບບ PHP ໃໝ່ |
|---|---|
| `page()`, `header()`, `footer()` | `templates/layout.php`, `partials/header.php`, `partials/footer.php` |
| `phead()`, `crumb()`, `sec_head()` | `partials/page-head.php`, `partials/breadcrumb.php`, `partials/section-head.php` |
| `product_card()`, `division_cards()`, `news_cards()`, `service_cards()` | `partials/cards/*.php` |
| `cta()`, `location_block()`, `contact_aside()` | `partials/cta.php`, `partials/location.php`, `partials/contact-aside.php` |
| `form()`, `field()` | `partials/contact-form.php` |
| `p_home()`, `p_about()` … `p_404()` | `pages/home.php`, `pages/about.php` … `pages/404.php` |

### ຂໍ້ຄວາມ UI ແລະ ເນື້ອຫາ
- **ຂໍ້ຄວາມ UI** (ຊື່ເມນູ, ປ້າຍປຸ່ມ, ຂໍ້ຄວາມໜ້າ about/vision/legal) ຍ້າຍຈາກ `strings.py` ແລະ `strings2.py` ໄປເປັນ `lang/lo.php`, `lang/en.php`, `lang/th.php`, `lang/zh.php`. ຢູ່ໃນ repo, ແກ້ໂດຍນັກພັດທະນາ.
- **ເນື້ອຫາ** (ສິນຄ້າ, ຂ່າວ, FAQ, hero, ດາວໂຫຼດ, ເບີໂທ/ທີ່ຢູ່) ຢູ່ໃນ MySQL, ແກ້ຜ່ານ admin.
- ກົດການແທນພາສາ: ຖ້າຊ່ອງພາສາ `th` ຫຼື `zh` ຫວ່າງ, ໃຊ້ພາສາ `en` ແທນ. `lo` ແລະ `en` ບັງຄັບຕ້ອງມີ.

## 5. ຟອມຕິດຕໍ່ (ຟອມດຽວໃນເວັບ)

### ຊ່ອງຂໍ້ມູນ
| ຊ່ອງ | name | ບັງຄັບ | ກົດ |
|---|---|---|---|
| ຊື່ ແລະ ນາມສະກຸນ | `name` | ✅ | 2–120 ຕົວ |
| ອົງກອນ | `org` | — | ≤ 160 ຕົວ |
| ເບີໂທ | `tel` | ✅ | ຕົວເລກ, `+`, ຍະຫວ່າງ, `-`, `()`; 6–30 ຕົວ |
| ອີເມວ | `email` | ✅ | ຮູບແບບອີເມວຖືກຕ້ອງ, ≤ 190 ຕົວ |
| ຫົວຂໍ້ | `subject` | — | `general` / `sales` / `service` / `other` |
| ຂໍ້ຄວາມ | `message` | ✅ | 10–5000 ຕົວ |
| ຍິນຍອມນະໂຍບາຍ | `consent` | ✅ | ຕ້ອງໝາຍ |
| (ເຊື່ອງ) ກັບດັກ bot | `website` | — | ຕ້ອງຫວ່າງ |
| (ເຊື່ອງ) token ເວລາ | `ts` | ມີ JS: ✅ | ເວລາ + ລາຍເຊັນ HMAC. ໜ້າເວັບເປັນ HTML ຄົງທີ່ ຈຶ່ງໃສ່ token ລ່ວງໜ້າບໍ່ໄດ້: JS ຂໍ token ຈາກ `GET /api/contact.php?token=1` ຕອນຜູ້ໃຊ້ focus ຟອມຄັ້ງທຳອິດ |
| (ເຊື່ອງ) ພາສາ | `lang` | ✅ | `lo`/`en`/`th`/`zh` |

ໝາຍເຫດ: ຄ່າ `<option>` ຂອງຫົວຂໍ້ຕ້ອງປ່ຽນເປັນລະຫັດ (`value="sales"`) ແທນຂໍ້ຄວາມ, ເພື່ອໃຫ້ຂໍ້ມູນໃນ DB ບໍ່ຂຶ້ນກັບພາສາ.

### ການເຮັດວຽກໃນ browser
1. `<form action="/api/contact.php" method="post">`. ຖ້າບໍ່ມີ JS ກໍສົ່ງແບບທຳມະດາ, ແລ້ວ server redirect ກັບມາ `contact.html?sent=1`.
2. ມີ JS: ດັກ `submit`, ສົ່ງດ້ວຍ `fetch`, ປິດປຸ່ມ ແລະ ສະແດງ "ກຳລັງສົ່ງ…".
3. ສຳເລັດ: ເຊື່ອງຟອມ, ສະແດງ `.form__ok` (`role="status"`) ແລະ ຍ້າຍ focus ໄປຫາຂໍ້ຄວາມນັ້ນ.
4. ຜິດລາຍຊ່ອງ (`422`): ສະແດງ error **ໃຕ້ຊ່ອງນັ້ນ**, ໃສ່ `aria-invalid="true"` ແລະ `aria-describedby`, ແລ້ວ focus ຊ່ອງທຳອິດທີ່ຜິດ.
5. ສົ່ງຖີ່ເກີນ (`429`) ຫຼື server ຜິດພາດ (`5xx`): ສະແດງຂໍ້ຄວາມລວມ ພ້ອມເບີໂທ ແລະ ປຸ່ມ WhatsApp ເປັນທາງສຳຮອງ.
6. ປ່ຽນຂໍ້ຄວາມ `f_ok` ຈາກ "ນີ້ແມ່ນໜ້າຕົວຢ່າງ…" ເປັນ "ຂອບໃຈ, ພວກເຮົາໄດ້ຮັບຂໍ້ຄວາມແລ້ວ ແລະ ຈະຕິດຕໍ່ກັບພາຍໃນ 1 ວັນເຮັດການ".
7. ລຶບ `data-demo` ແລະ handler demo ໃນ `main.js`.

## 6. ຄວາມໄວ

### ເປົ້າໝາຍ (ວັດດ້ວຍ Lighthouse mobile, ເຄືອຂ່າຍ 4G)
| ຕົວຊີ້ວັດ | ເປົ້າ |
|---|---|
| LCP | < 2.5 s |
| CLS | < 0.05 |
| INP | < 200 ms |
| Lighthouse Performance | ≥ 90 ທຸກໜ້າຫຼັກ |
| ນ້ຳໜັກໜ້າຫຼັກຕອນໂຫຼດທຳອິດ (ບໍ່ນັບວິດີໂອ) | < 800 KB |

### ວຽກທີ່ຕ້ອງເຮັດ
1. **ວິດີໂອ hero** ([hero-lab.mp4](../../assets/video/hero-lab.mp4) 5.5 MB): ບີບອັດເປັນ ~1.5–2 MB (H.264, 720p, ບໍ່ມີສຽງ), ເພີ່ມສະບັບ WebM ແລະ ບໍ່ໂຫຼດວິດີໂອເທິງມືຖືທີ່ເປີດ Save-Data (ສະແດງ poster ແທນ).
2. **ຮູບ**: ແປງເປັນ WebP ພ້ອມ JPEG ສຳຮອງຜ່ານ `<picture>`. ໃສ່ `width`/`height` ທຸກຮູບເພື່ອກັນ CLS. ຮູບທີ່ອັບຜ່ານ admin ສ້າງ 3 ຂະໜາດອັດຕະໂນມັດ (480, 960, 1600 px) ແລ້ວໃຊ້ `srcset`.
3. **Cache**: `.htaccess` ຕັ້ງ `Cache-Control: max-age=31536000, immutable` ໃຫ້ `assets/` ແລະ `uploads/`; HTML ໃຫ້ `no-cache` (ກວດກັບ server ທຸກຄັ້ງ ແຕ່ໃຊ້ ETag).
4. **Cache busting**: Publisher ຕໍ່ທ້າຍ `?v=<hash>` ໃສ່ CSS/JS ເພື່ອໃຫ້ browser ດຶງໄຟລ໌ໃໝ່ຫຼັງປ່ຽນ.
5. **ບີບອັດ**: gzip (ຫຼື brotli ຖ້າມີ) ສຳລັບ HTML, CSS, JS, SVG, JSON.
6. **ຟອນ**: `preload` ສະເພາະຟອນຂອງພາສາທີ່ເປີດຢູ່ ແລະ ໃຊ້ `font-display: swap`. ຟອນຈີນຍັງດຶງຈາກ Google Fonts.
7. **Map**: iframe Google Maps ໂຫຼດແບບ `loading="lazy"` (ມີແລ້ວ).
8. ທາງເລືອກໃນເຟດ go-live: ໃສ່ Cloudflare (ຟຣີ) ເປັນ CDN.

## 7. SEO
- ທຸກໜ້າມີ `<title>`, `<meta name="description">`, Open Graph ແລະ `hreflang` ຄົບ 4 ພາສາ + `x-default` (ຄົບແລ້ວ P3-D4: URL ເຕັມ, `x-default` = ພາສາອັງກິດ).
- ເພີ່ມ `<link rel="canonical">` ແບບ URL ເຕັມ.
- Publisher ສ້າງ `/sitemap.xml` ທີ່ມີທຸກໜ້າ ທຸກພາສາ.
- **ລະຫວ່າງພັດທະນາ:** `robots.txt` = `Disallow: /` ແລະ ທຸກໜ້າມີ `<meta name="robots" content="noindex">`. ເອົາອອກຕອນ go-live ເທົ່ານັ້ນ.
- ໜ້າສິນຄ້າເພີ່ມ JSON-LD `Product`. ໜ້າຂ່າວເພີ່ມ `NewsArticle`. ໜ້າຫຼັກເພີ່ມ `Organization`.
- Admin ແກ້ title/description ຂອງແຕ່ລະສິນຄ້າ ແລະ ຂ່າວໄດ້. ຖ້າຫວ່າງ, ໃຊ້ຊື່ ແລະ ຄຳອະທິບາຍສັ້ນແທນ.

## 8. ການເຂົ້າເຖິງ (Accessibility)
- ຕົວໜັງສືກັບພື້ນຕ້ອງຕັດກັນຢ່າງໜ້ອຍ 4.5:1.
- ທຸກອົງປະກອບໃຊ້ຄີບອດໄດ້ ແລະ ເຫັນ focus ຊັດ (ມີລິ້ງ "ຂ້າມໄປເນື້ອຫາ" ແລ້ວ).
- ປຸ່ມທີ່ມີແຕ່ໄອຄອນຕ້ອງມີ `aria-label`.
- ຮູບສິນຄ້າຕ້ອງມີ `alt` ເປັນຊື່ສິນຄ້າ, ຮູບປະດັບໃຊ້ `alt=""`.
- ເຄົາລົບ `prefers-reduced-motion` (ມີແລ້ວ).
- ເປົ້າກົດເທິງມືຖືຂະໜາດຢ່າງໜ້ອຍ 44×44 px.

## 9. ການທົດສອບໜ້າບ້ານ
| ກວດ | ເຄື່ອງມື | ເມື່ອໃດ |
|---|---|---|
| ລິ້ງເສຍ | ສະຄຣິບ `bin/check-links.php` (ໄລ່ທຸກ `href`/`src` ໃນ output) | ທຸກຄັ້ງທີ່ເຜີຍແຜ່ |
| HTML ຖືກຕ້ອງ | W3C validator (ສຸ່ມ 1 ໜ້າຕໍ່ປະເພດ) | ທ້າຍແຕ່ລະເຟດ |
| ຄວາມໄວ | Lighthouse mobile | ທ້າຍແຕ່ລະເຟດ |
| ໜ້າຕາ | Chrome 1440px + 390px, Safari iOS | ທ້າຍແຕ່ລະເຟດ |
| ຟອມ | ສົ່ງຈິງທັງ 4 ພາສາ, ມີ/ບໍ່ມີ JS, ຂໍ້ມູນຜິດ, ສົ່ງຖີ່ | ເຟດ 1 ແລະ ກ່ອນ go-live |
