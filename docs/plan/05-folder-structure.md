# 05 — ໂຄງສ້າງໂຟນເດີ ແລະ ໄຟລ໌

## 1. ຫຼັກການ
- **Repo ແລະ server ມີໂຄງດຽວກັນ.** ໃນ server, repo ທັງໝົດຢູ່ `/home/xtklaos/xtk/`. Docroot ຂອງ subdomain ຊີ້ໄປ `/home/xtklaos/xtk/public`. ສະນັ້ນ path ແບບ relative ໃຊ້ໄດ້ຄືກັນທັງໃນເຄື່ອງ ແລະ ໃນ server.
- **`public/`** = ສິ່ງທີ່ເປີດຈາກເວັບໄດ້. **`app/`** = ໂຄດ, config ແລະ ຂໍ້ມູນ. ເປີດຈາກເວັບບໍ່ໄດ້ ເພາະຢູ່ນອກ docroot.
- ໄຟລ໌ທີ່ຖືກສ້າງອັດຕະໂນມັດ (`public/lo/` ...) ແລະ ຂໍ້ມູນ runtime (`app/storage/`, `public/uploads/`) **ບໍ່ commit**.

## 2. ໂຄງສ້າງ repo (ເປົ້າໝາຍຫຼັງເຟດ 2)

```
xtv/
├── public/                         ← docroot ຂອງ subdomain
│   ├── .htaccess                   ← cache, gzip, security headers, rewrite ຂອງ admin
│   ├── robots.txt                  ← ສ້າງໂດຍ Publisher (Disallow ລະຫວ່າງພັດທະນາ)
│   ├── index.html                  ← [ສ້າງອັດຕະໂນມັດ] ສົ່ງຕໍ່ໄປ lo/
│   ├── sitemap.xml                 ← [ສ້າງອັດຕະໂນມັດ]
│   ├── lo/ en/ th/ zh/             ← [ສ້າງອັດຕະໂນມັດ] 69 ໜ້າຕໍ່ພາສາ
│   ├── assets/                     ← ໄຟລ໌ຄົງທີ່ຂອງໜ້າເວັບ (ຍ້າຍມາຈາກ /assets)
│   │   ├── css/style.css
│   │   ├── js/main.js
│   │   ├── fonts/
│   │   ├── img/                    ← ໂລໂກ້, ຮູບບັນຍາກາດ (ຮູບສິນຄ້າເກົ່າຢູ່ນີ້ຈົນກວ່າຈະຍ້າຍເຂົ້າ uploads)
│   │   └── video/
│   ├── uploads/                    ← [runtime] ໄຟລ໌ທີ່ອັບຜ່ານ admin
│   │   ├── .htaccess               ← ຫ້າມແລ່ນ PHP/script (commit ໄຟລ໌ນີ້ໄຟລ໌ດຽວ)
│   │   └── 2026/10/ab12cd34-960.webp …
│   ├── api/
│   │   └── contact.php             ← 3 ແຖວ: require bootstrap ແລ້ວເອີ້ນ ContactController
│   └── admin/
│       ├── index.php               ← front controller ຂອງ admin
│       └── assets/
│           ├── admin.css
│           └── admin.js
│
├── app/                            ← ນອກ docroot
│   ├── bootstrap.php               ← autoload, config, error handler, session, timezone Asia/Vientiane
│   ├── composer.json               ← PSR-4 "App\\": "src/", require phpmailer
│   ├── composer.lock
│   ├── vendor/                     ← [ບໍ່ commit] ສ້າງດ້ວຍ composer install
│   ├── config/
│   │   ├── config.example.php      ← ແມ່ແບບ (commit)
│   │   └── config.php              ← ຄ່າຈິງ: DB, SMTP, HMAC secret (ບໍ່ commit, chmod 600)
│   ├── src/
│   │   ├── Core/
│   │   │   ├── Db.php              ← PDO wrapper
│   │   │   ├── Router.php          ← ຈັບຄູ່ path → controller ຂອງ admin
│   │   │   ├── Request.php, Response.php
│   │   │   ├── View.php            ← render template + e()
│   │   │   ├── Session.php, Auth.php, Csrf.php
│   │   │   ├── Validator.php
│   │   │   ├── RateLimiter.php
│   │   │   ├── Mailer.php          ← ຫໍ່ PHPMailer
│   │   │   ├── Logger.php
│   │   │   └── Lang.php            ← ໂຫຼດ lang/*.php, ຟັງຊັນ t() ແລະ ກົດການແທນພາສາ
│   │   ├── Repository/             ← SQL ທັງໝົດຢູ່ນີ້ ບ່ອນດຽວ
│   │   │   ├── UserRepository.php
│   │   │   ├── MessageRepository.php
│   │   │   ├── DivisionRepository.php, GroupRepository.php, ProductRepository.php
│   │   │   ├── SupplierRepository.php
│   │   │   ├── NewsRepository.php, DownloadRepository.php, MediaRepository.php
│   │   │   ├── FaqRepository.php, HeroRepository.php, SettingRepository.php
│   │   │   └── PublishLogRepository.php, ActivityRepository.php
│   │   ├── Service/
│   │   │   ├── ContactService.php  ← ກວດ, ບັນທຶກ, ສົ່ງອີເມວ
│   │   │   ├── MediaService.php    ← ອັບ, ກວດ finfo, ສ້າງ WebP 3 ຂະໜາດ
│   │   │   ├── HtmlSanitizer.php   ← allow-list ສຳລັບເນື້ອຫາຂ່າວ
│   │   │   ├── Slugger.php
│   │   │   └── CsvExporter.php
│   │   ├── Http/
│   │   │   ├── ContactController.php
│   │   │   └── Admin/
│   │   │       ├── AuthController.php, DashboardController.php
│   │   │       ├── MessageController.php
│   │   │       ├── DivisionController.php, GroupController.php, ProductController.php
│   │   │       ├── SupplierController.php
│   │   │       ├── NewsController.php, DownloadController.php, MediaController.php
│   │   │       ├── FaqController.php, HeroController.php, SeoController.php
│   │   │       ├── SettingController.php, UserController.php, ActivityController.php
│   │   │       └── PublishController.php
│   │   └── Publish/
│   │       ├── Publisher.php       ← lock, build, ກວດ, swap, rollback
│   │       ├── SiteData.php        ← ໂຫຼດຂໍ້ມູນທັງໝົດຈາກ DB ໃນຄັ້ງດຽວ
│   │       ├── PageList.php        ← ລາຍການໜ້າທີ່ຕ້ອງສ້າງ
│   │       ├── LinkChecker.php
│   │       └── SitemapWriter.php
│   ├── templates/
│   │   ├── site/                   ← ແມ່ແບບໜ້າເວັບ (ແປງມາຈາກ build/build.py)
│   │   │   ├── layout.php
│   │   │   ├── partials/
│   │   │   │   ├── header.php, footer.php, drawer.php
│   │   │   │   ├── page-head.php, breadcrumb.php, section-head.php
│   │   │   │   ├── cta.php, location.php, contact-aside.php, contact-form.php, supplied-by.php
│   │   │   │   ├── steps.php, chips.php
│   │   │   │   ├── icons.php       ← SVG ໄອຄອນ (ICONS ໃນ build.py)
│   │   │   │   └── cards/ product.php, divisions.php, news.php, text.php (ບໍລິການ + ເປັນຫຍັງ), supplier.php, supplier-tile.php, logo-tile.php
│   │   │   └── pages/
│   │   │       ├── home.php, about.php, vision.php
│   │   │       ├── products.php, division.php, product.php
│   │   │       ├── suppliers.php, supplier.php      ← ⭐ (08-suppliers.md)
│   │   │       ├── solutions.php, services.php
│   │   │       ├── news.php, news-article.php
│   │   │       ├── contact.php, downloads.php, faq.php
│   │   │       ├── legal.php, sitemap.php, 404.php, redirect.php
│   │   ├── admin/
│   │   │   ├── layout.php          ← sidebar + topbar
│   │   │   ├── partials/ flash.php, pagination.php, lang-tabs.php, media-picker.php, confirm.php
│   │   │   └── <module>/ index.php, edit.php …
│   │   └── email/
│   │       ├── contact-notify.php      ← ແຈ້ງທີມງານ (ລາວ)
│   │       └── contact-autoreply.php   ← ຕອບລູກຄ້າ (4 ພາສາ)
│   ├── lang/
│   │   ├── lo.php  en.php  th.php  zh.php   ← ຂໍ້ຄວາມ UI ຂອງໜ້າເວັບ (ຈາກ strings.py + strings2.py)
│   │   ├── countries.php                    ← ຊື່ປະເທດ 4 ພາສາ ຕາມລະຫັດ ISO (ສຳລັບຜູ້ສະໜອງ)
│   │   └── admin.php                        ← ຂໍ້ຄວາມຂອງ admin (ລາວ)
│   ├── database/
│   │   ├── migrations/ 001_core.sql, 002_content.sql, 003_publish_warnings.sql, 004_extras.sql
│   │   └── seed/seed.json
│   ├── bin/                        ← ສັ່ງດ້ວຍ php CLI ຫຼື cron
│   │   ├── migrate.php
│   │   ├── seed.php
│   │   ├── build-site.php          ← render ທຸກໜ້າໃສ່ໂຟນເດີທີ່ເລືອກ (ທົດສອບ, P2-C3)
│   │   ├── create-admin.php
│   │   ├── publish.php
│   │   ├── check-links.php
│   │   ├── backup.php              ← mysqldump + uploads → storage/backups
│   │   └── cleanup.php             ← ລຶບ rate_limits, spam, log, build ເກົ່າ
│   ├── storage/                    ← [runtime, ບໍ່ commit ຍົກເວັ້ນ .gitkeep]
│   │   ├── logs/                   ← app-YYYY-MM-DD.log
│   │   ├── sessions/
│   │   ├── build/                  ← ໂຟນເດີຊົ່ວຄາວຂອງ Publisher + 3 ຊຸດສຳລັບ rollback
│   │   ├── backups/
│   │   └── publish.lock
│   └── tests/
│       ├── ValidatorTest.php, HtmlSanitizerTest.php, RateLimiterTest.php, SluggerTest.php
│       └── PublisherTest.php
│
├── scripts/                        ← ເຄື່ອງມືຂອງນັກພັດທະນາ, ບໍ່ deploy (Python `build/` ເກົ່າລຶບແລ້ວໃນ P2-C11)
│   ├── optimize_media.py           ← WebP ຂອງ assets/img, ວິດີໂອ hero, `--zh-font` ສ້າງ subset ຟອນຈີນ
│   ├── media/hero-lab-source.mp4   ← ຕົ້ນສະບັບວິດີໂອ hero
│   └── deploy.sh                   ← rsync ຂຶ້ນ server (ເບິ່ງ 07-deployment.md)
├── docs/plan/                      ← ແຜນນີ້
├── design.md
├── .gitignore
└── .claude/                        ← skills, hooks, changelog
```

## 3. ໂຄງສ້າງໃນ server

```
/home/xtklaos/
├── public_html/              ← ເວັບເກົ່າຂອງ xtklaos.com. ຫ້າມແຕະ ຈົນຮອດເຟດ go-live
├── xtk/                      ← repo (deploy ດ້ວຍ rsync)
│   ├── public/               ← docroot ຂອງ new.xtklaos.com
│   └── app/
│       ├── config/config.php ← ສ້າງໃນ server ຄັ້ງດຽວ, chmod 600
│       ├── storage/          ← chmod 700
│       └── vendor/
└── (cPanel ອື່ນໆ: mail/, etc/, logs/ …)
```

## 4. ການຍ້າຍໂຄງຈາກປັດຈຸບັນ
ປັດຈຸບັນ `assets/`, `lo/`, `en/`, `th/`, `zh/` ແລະ `index.html` ຢູ່ root ຂອງ repo (ສຳລັບ GitHub Pages). ການຍ້າຍເຮັດໃນວຽກ **P0-A2**, ກ່ອນ deploy ຄັ້ງທຳອິດ:
1. `git mv assets public/assets`
2. ແກ້ `build/build.py` ໃຫ້ຂຽນ output ໄປ `public/` (`ROOT` → `public/`)
3. ລຶບ `lo/ en/ th/ zh/ index.html` ອອກຈາກ git ແລ້ວເພີ່ມໃສ່ `.gitignore`
4. GitHub Pages ຈະໃຊ້ບໍ່ໄດ້ຫຼັງຈາກນີ້. Subdomain ໃນ cPanel ຈະເປັນບ່ອນ preview ແທນ

## 5. `.gitignore` (ເປົ້າໝາຍ)
```
.DS_Store
__pycache__/
*.pyc
/public/index.html
/public/sitemap.xml
/public/robots.txt
/public/lo/
/public/en/
/public/th/
/public/zh/
/public/uploads/*
!/public/uploads/.htaccess
/app/vendor/
/app/config/config.php
/app/storage/*
!/app/storage/**/.gitkeep
```

## 6. ກົດການຕັ້ງຊື່
| ສິ່ງ | ຮູບແບບ | ຕົວຢ່າງ |
|---|---|---|
| Class PHP | PascalCase, ໜຶ່ງ class ຕໍ່ໄຟລ໌ | `ProductRepository.php` |
| Method/ຕົວປ່ຽນ | camelCase | `findVisibleByGroup()` |
| ຕາຕະລາງ/ຖັນ | snake_case, ຕາຕະລາງເປັນພະຫູພົດ | `product_specs.label_lo` |
| Template | kebab-case | `contact-form.php` |
| ໜ້າເວັບ | kebab-case `.html` | `product-ct-scan.html` |
| Migration | ເລກ 3 ຫຼັກ + ຄຳອະທິບາຍ | `004_add_product_brand.sql` |
| ໄຟລ໌ອັບ | `uploads/YYYY/MM/<hash>-<width>.<ext>` | `uploads/2026/10/ab12cd34-960.webp` |
| Changelog | `YYYY-MM-DD-phase<N>-<id>-<name>.md` | `2026-11-03-phase1-b3-contact-api.md` |
