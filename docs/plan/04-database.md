# 04 — ໂຄງສ້າງຖານຂໍ້ມູນ (MySQL 8.0)

## 1. ຫຼັກການ
- ຊື່ຖານ: `xtklaos_xtk` (cPanel ຕໍ່ໜ້າດ້ວຍ `xtklaos_` ອັດຕະໂນມັດ). User: `xtklaos_app` ມີສິດສະເພາະຖານນີ້.
- Engine `InnoDB`, charset `utf8mb4`, collation `utf8mb4_unicode_ci` (ຮອງຮັບລາວ, ໄທ, ຈີນ).
- ທຸກຕາຕະລາງມີ `id INT UNSIGNED AUTO_INCREMENT` ແລະ `created_at`/`updated_at` (ຍົກເວັ້ນຕາຕະລາງ log).
- **ຫຼາຍພາສາແບບຖັນ**: ຊ່ອງທີ່ແປໄດ້ມີ 4 ຖັນ `_lo`, `_en`, `_th`, `_zh`.
  - ເຫດຜົນ: ມີພຽງ 4 ພາສາຕາຍຕົວ. ອ່ານງ່າຍ, query ງ່າຍ, ບໍ່ຕ້ອງ JOIN.
  - `_lo` ແລະ `_en` ບັງຄັບ (`NOT NULL`). `_th` ແລະ `_zh` ຫວ່າງໄດ້, Publisher ໃຊ້ `_en` ແທນ.
- ລຳດັບການສະແດງໃຊ້ຖັນ `sort SMALLINT` (ນ້ອຍຂຶ້ນກ່ອນ).
- ປິດການສະແດງໃຊ້ `is_visible`. ບໍ່ລຶບຂໍ້ມູນເນື້ອຫາທີ່ເຄີຍເຜີຍແຜ່ແລ້ວ ຖ້າບໍ່ຈຳເປັນ.

## 2. ແຜນຜັງຄວາມສຳພັນ

```
users ─┬─< message_notes >── messages
       ├─< activity_log
       ├─< publish_log
       └─< login_attempts (ຜ່ານ email)

divisions ─< product_groups ─< products ─< product_specs
    │                              │  │
    └─< downloads                  │  └── media (image_id)
                                   │
                          product_suppliers >── suppliers
                                                  └── media (logo_id, certificate_id)

media ──< news (cover_id), divisions (image_id), downloads (file_id), hero_slides (media_id)

settings, faqs, seo_pages, redirects, rate_limits, schema_migrations  (ຢືນດ່ຽວ)
```

## 3. ຕາຕະລາງ

| ຕາຕະລາງ | ໜ້າທີ່ | ເຟດ |
|---|---|---|
| `schema_migrations` | ບັນທຶກ migration ທີ່ແລ່ນແລ້ວ | 1 |
| `users` | ຜູ້ໃຊ້ admin | 1 |
| `login_attempts` | ກັນການເດົາລະຫັດຜ່ານ | 1 |
| `rate_limits` | ກັນ spam ຟອມຕິດຕໍ່ | 1 |
| `messages` | ຂໍ້ຄວາມຈາກຟອມຕິດຕໍ່ | 1 |
| `message_notes` | ບັນທຶກພາຍໃນຂອງຂໍ້ຄວາມ | 1 |
| `settings` | ຄ່າຕັ້ງ (ເບີໂທ, ອີເມວແຈ້ງເຕືອນ, ...) | 1 |
| `media` | ຮູບ ແລະ PDF ທີ່ອັບ | 2 |
| `divisions` | 5 ຂະແໜງທຸລະກິດ | 2 |
| `product_groups` | ກຸ່ມສິນຄ້າ | 2 |
| `products` | ສິນຄ້າ | 2 |
| ⭐ `suppliers` | ຜູ້ສະໜອງສິນຄ້າໃຫ້ XTK | 2 |
| ⭐ `product_suppliers` | ສິນຄ້າ ↔ ຜູ້ສະໜອງ (ຫຼາຍຕໍ່ຫຼາຍ) | 2 |
| `product_specs` | ຂໍ້ມູນຈຳເພາະຂອງສິນຄ້າ | 2 |
| `news` | ຂ່າວ | 2 |
| `downloads` | ໄຟລ໌ດາວໂຫຼດ | 2 |
| `redirects` | slug ເກົ່າ → ໃໝ່ | 2 |
| `publish_log` | ປະຫວັດການເຜີຍແຜ່ | 2 |
| `faqs` | ຄຳຖາມທີ່ພົບເລື້ອຍ | 3 |
| `hero_slides` | ສະໄລ້ໜ້າຫຼັກ | 3 |
| `seo_pages` | SEO ໜ້າຄົງທີ່ | 3 |
| `activity_log` | ປະຫວັດການແກ້ໄຂ | 3 |

## 4. SQL

### 4.1 ເຟດ 1 — `database/migrations/001_core.sql`
```sql
CREATE TABLE schema_migrations (
  version     VARCHAR(64)  NOT NULL PRIMARY KEY,
  applied_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(120) NOT NULL,
  email          VARCHAR(190) NOT NULL,
  password_hash  VARCHAR(255) NOT NULL,
  role           ENUM('admin','editor') NOT NULL DEFAULT 'editor',
  is_active      TINYINT(1)   NOT NULL DEFAULT 1,
  last_login_at  DATETIME     NULL,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email       VARCHAR(190) NOT NULL,
  ip          VARBINARY(16) NOT NULL,            -- INET6_ATON()
  success     TINYINT(1)   NOT NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_login_email_time (email, created_at),
  KEY ix_login_ip_time (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rate_limits (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  bucket      VARCHAR(32)  NOT NULL,             -- 'contact'
  ip          VARBINARY(16) NOT NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_rate_bucket_ip_time (bucket, ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name         VARCHAR(120) NOT NULL,
  org          VARCHAR(160) NULL,
  phone        VARCHAR(30)  NOT NULL,
  email        VARCHAR(190) NOT NULL,
  subject      ENUM('general','sales','service','other') NOT NULL DEFAULT 'general',
  body         TEXT         NOT NULL,
  lang         CHAR(2)      NOT NULL,            -- lo/en/th/zh
  page_url     VARCHAR(255) NULL,
  ip           VARBINARY(16) NULL,
  user_agent   VARCHAR(255) NULL,
  status       ENUM('new','in_progress','done','spam') NOT NULL DEFAULT 'new',
  assigned_to  INT UNSIGNED NULL,
  mail_error   VARCHAR(255) NULL,                -- ສົ່ງອີເມວແຈ້ງເຕືອນບໍ່ສຳເລັດ
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY ix_messages_status_time (status, created_at),
  KEY ix_messages_email (email),
  CONSTRAINT fk_messages_user FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE message_notes (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  message_id  INT UNSIGNED NOT NULL,
  user_id     INT UNSIGNED NULL,
  note        TEXT         NOT NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_notes_message (message_id),
  CONSTRAINT fk_notes_message FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
  CONSTRAINT fk_notes_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  `key`       VARCHAR(64)  NOT NULL PRIMARY KEY,
  value       JSON         NOT NULL,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.2 ເຟດ 2 — `database/migrations/002_content.sql`
```sql
CREATE TABLE media (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  kind           ENUM('image','pdf','video') NOT NULL,
  path           VARCHAR(255) NOT NULL,          -- uploads/2026/10/ab12cd34  (ບໍ່ມີນາມສະກຸນ)
  original_name  VARCHAR(255) NOT NULL,
  mime           VARCHAR(100) NOT NULL,
  size_bytes     INT UNSIGNED NOT NULL,
  width          SMALLINT UNSIGNED NULL,
  height         SMALLINT UNSIGNED NULL,
  variants       JSON         NULL,              -- {"480":{"webp":"..","jpg":".."},"960":{..},"1600":{..}}
  alt_lo VARCHAR(255) NULL, alt_en VARCHAR(255) NULL, alt_th VARCHAR(255) NULL, alt_zh VARCHAR(255) NULL,
  created_by     INT UNSIGNED NULL,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_media_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE divisions (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(64)  NOT NULL,             -- healthcare -> healthcare.html
  icon        VARCHAR(32)  NOT NULL,
  image_id    INT UNSIGNED NULL,
  name_lo VARCHAR(120) NOT NULL, name_en VARCHAR(120) NOT NULL, name_th VARCHAR(120) NULL, name_zh VARCHAR(120) NULL,
  summary_lo VARCHAR(300) NOT NULL, summary_en VARCHAR(300) NOT NULL, summary_th VARCHAR(300) NULL, summary_zh VARCHAR(300) NULL,
  body_lo TEXT NOT NULL, body_en TEXT NOT NULL, body_th TEXT NULL, body_zh TEXT NULL,
  sort        SMALLINT     NOT NULL DEFAULT 0,
  is_visible  TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_divisions_slug (slug),
  CONSTRAINT fk_divisions_image FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_groups (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  division_id  INT UNSIGNED NOT NULL,
  code         VARCHAR(32)  NOT NULL,            -- g_imaging (ໃຊ້ເປັນ anchor #g_imaging)
  icon         VARCHAR(32)  NOT NULL,
  name_lo VARCHAR(120) NOT NULL, name_en VARCHAR(120) NOT NULL, name_th VARCHAR(120) NULL, name_zh VARCHAR(120) NULL,
  summary_lo VARCHAR(400) NOT NULL, summary_en VARCHAR(400) NOT NULL, summary_th VARCHAR(400) NULL, summary_zh VARCHAR(400) NULL,
  sort         SMALLINT     NOT NULL DEFAULT 0,
  is_visible   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_groups_code (code),
  KEY ix_groups_division (division_id, sort),
  CONSTRAINT fk_groups_division FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  id              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  group_id        INT UNSIGNED NOT NULL,
  slug            VARCHAR(120) NOT NULL,         -- ct-scan -> product-ct-scan.html
  name            VARCHAR(160) NOT NULL,         -- ຊື່ດຽວທຸກພາສາ
  image_id        INT UNSIGNED NULL,             -- NULL = ກາດ "ສອບຖາມລາຄາ", ບໍ່ມີໜ້າລາຍລະອຽດ
  variants_note   VARCHAR(400) NULL,             -- "T3 · T4 · TSH · HbA1c"
  summary_lo VARCHAR(400) NULL, summary_en VARCHAR(400) NULL, summary_th VARCHAR(400) NULL, summary_zh VARCHAR(400) NULL,
  body_lo TEXT NULL, body_en TEXT NULL, body_th TEXT NULL, body_zh TEXT NULL,
  seo_title_lo VARCHAR(160) NULL, seo_title_en VARCHAR(160) NULL, seo_title_th VARCHAR(160) NULL, seo_title_zh VARCHAR(160) NULL,
  seo_desc_lo VARCHAR(300) NULL, seo_desc_en VARCHAR(300) NULL, seo_desc_th VARCHAR(300) NULL, seo_desc_zh VARCHAR(300) NULL,
  is_featured     TINYINT(1)   NOT NULL DEFAULT 0,
  featured_sort   SMALLINT     NOT NULL DEFAULT 0,
  is_visible      TINYINT(1)   NOT NULL DEFAULT 1,
  sort            SMALLINT     NOT NULL DEFAULT 0,
  created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_products_slug (slug),
  KEY ix_products_group (group_id, sort),
  KEY ix_products_featured (is_featured, featured_sort),
  CONSTRAINT fk_products_group FOREIGN KEY (group_id) REFERENCES product_groups(id) ON DELETE RESTRICT,
  CONSTRAINT fk_products_image FOREIGN KEY (image_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE product_specs (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  product_id  INT UNSIGNED NOT NULL,
  label_lo VARCHAR(120) NOT NULL, label_en VARCHAR(120) NOT NULL, label_th VARCHAR(120) NULL, label_zh VARCHAR(120) NULL,
  value_lo VARCHAR(400) NOT NULL, value_en VARCHAR(400) NOT NULL, value_th VARCHAR(400) NULL, value_zh VARCHAR(400) NULL,
  sort        SMALLINT     NOT NULL DEFAULT 0,
  KEY ix_specs_product (product_id, sort),
  CONSTRAINT fk_specs_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE news (
  id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug          VARCHAR(120) NOT NULL,           -- security-division -> news-security-division.html
  category      ENUM('product','company','event') NOT NULL DEFAULT 'company',
  cover_id      INT UNSIGNED NULL,
  link_url      VARCHAR(255) NULL,               -- ປຸ່ມ "ເບິ່ງສິນຄ້າ" (ບໍ່ບັງຄັບ)
  published_on  DATE         NOT NULL,
  status        ENUM('draft','published') NOT NULL DEFAULT 'draft',
  title_lo VARCHAR(200) NOT NULL, title_en VARCHAR(200) NOT NULL, title_th VARCHAR(200) NULL, title_zh VARCHAR(200) NULL,
  summary_lo VARCHAR(400) NOT NULL, summary_en VARCHAR(400) NOT NULL, summary_th VARCHAR(400) NULL, summary_zh VARCHAR(400) NULL,
  body_lo MEDIUMTEXT NOT NULL, body_en MEDIUMTEXT NOT NULL, body_th MEDIUMTEXT NULL, body_zh MEDIUMTEXT NULL,
  created_by    INT UNSIGNED NULL,
  created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_news_slug (slug),
  KEY ix_news_status_date (status, published_on),
  CONSTRAINT fk_news_cover FOREIGN KEY (cover_id) REFERENCES media(id) ON DELETE SET NULL,
  CONSTRAINT fk_news_user  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE downloads (
  id           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  division_id  INT UNSIGNED NULL,                -- NULL = ເອກະສານບໍລິສັດ
  file_id      INT UNSIGNED NOT NULL,
  title_lo VARCHAR(200) NOT NULL, title_en VARCHAR(200) NOT NULL, title_th VARCHAR(200) NULL, title_zh VARCHAR(200) NULL,
  summary_lo VARCHAR(300) NULL, summary_en VARCHAR(300) NULL, summary_th VARCHAR(300) NULL, summary_zh VARCHAR(300) NULL,
  sort         SMALLINT     NOT NULL DEFAULT 0,
  is_visible   TINYINT(1)   NOT NULL DEFAULT 1,
  created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_downloads_division FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE SET NULL,
  CONSTRAINT fk_downloads_file     FOREIGN KEY (file_id)     REFERENCES media(id)     ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE redirects (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  from_page   VARCHAR(160) NOT NULL,             -- product-old-name (ບໍ່ມີ .html)
  to_page     VARCHAR(160) NOT NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_redirects_from (from_page)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE publish_log (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id        INT UNSIGNED NULL,              -- NULL = CLI
  status         ENUM('running','success','failed','rolled_back') NOT NULL,
  pages_written  INT UNSIGNED NULL,
  duration_ms    INT UNSIGNED NULL,
  build_dir      VARCHAR(64)  NULL,
  error          TEXT         NULL,
  started_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  finished_at    DATETIME     NULL,
  CONSTRAINT fk_publish_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 4.3 ⭐ ຜູ້ສະໜອງ — ເພີ່ມໃນ `002_content.sql` (ຫຼັງ `products`)
```sql
CREATE TABLE suppliers (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug           VARCHAR(120) NOT NULL,          -- -> supplier-<slug>.html
  name           VARCHAR(160) NOT NULL,          -- ຊື່ດຽວທຸກພາສາ (ຊື່ຍີ່ຫໍ້)
  country_code   CHAR(2)      NOT NULL,          -- ISO 3166-1 alpha-2, ຊື່ປະເທດຢູ່ app/lang/countries.php
  website_url    VARCHAR(255) NULL,
  logo_id        INT UNSIGNED NULL,
  logo_on_dark   TINYINT(1)   NOT NULL DEFAULT 0,-- ໂລໂກ້ສີຂາວ ຕ້ອງໃຊ້ພື້ນເຂັ້ມ
  partnership    ENUM('exclusive','authorized','distributor') NULL,  -- ທາງເລືອກ: ສະແດງສະເພາະເມື່ອລູກຄ້າຢືນຢັນ
  since_year     SMALLINT UNSIGNED NULL,
  certificate_id INT UNSIGNED NULL,              -- ໃບຢັ້ງຢືນ (PDF/ຮູບ), ທາງເລືອກ
  summary_lo VARCHAR(300) NULL, summary_en VARCHAR(300) NULL, summary_th VARCHAR(300) NULL, summary_zh VARCHAR(300) NULL,
  about_lo TEXT NULL, about_en TEXT NULL, about_th TEXT NULL, about_zh TEXT NULL,
  is_featured    TINYINT(1)   NOT NULL DEFAULT 0,-- ຜູ້ສະໜອງຫຼັກ (ສູງສຸດ 6, ກວດໃນ app)
  featured_sort  SMALLINT     NOT NULL DEFAULT 0,
  sort           SMALLINT     NOT NULL DEFAULT 0,
  is_visible     TINYINT(1)   NOT NULL DEFAULT 1,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_suppliers_slug (slug),
  KEY ix_suppliers_featured (is_featured, featured_sort),
  CONSTRAINT fk_suppliers_logo FOREIGN KEY (logo_id)        REFERENCES media(id) ON DELETE SET NULL,
  CONSTRAINT fk_suppliers_cert FOREIGN KEY (certificate_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ສິນຄ້າ ↔ ຜູ້ສະໜອງ (ຫຼາຍຕໍ່ຫຼາຍ): ສິນຄ້າໜຶ່ງອາດມາຈາກຫຼາຍຜູ້ສະໜອງ
CREATE TABLE product_suppliers (
  product_id   INT UNSIGNED NOT NULL,
  supplier_id  INT UNSIGNED NOT NULL,
  sort         SMALLINT     NOT NULL DEFAULT 0,  -- 0 = ຜູ້ສະໜອງຫຼັກ ທີ່ສະແດງກ່ອນ
  PRIMARY KEY (product_id, supplier_id),
  KEY ix_prodsup_supplier (supplier_id),
  CONSTRAINT fk_prodsup_product  FOREIGN KEY (product_id)  REFERENCES products(id)  ON DELETE CASCADE,
  CONSTRAINT fk_prodsup_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
ຂະແໜງຂອງຜູ້ສະໜອງ ບໍ່ເກັບແຍກ. ຄິດໄລ່ຈາກ `product_suppliers → products → product_groups → divisions` ເພື່ອບໍ່ໃຫ້ຂໍ້ມູນຂັດກັນ.

### 4.4 ເຟດ 3 — `database/migrations/003_extras.sql`
```sql
CREATE TABLE faqs (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  question_lo VARCHAR(300) NOT NULL, question_en VARCHAR(300) NOT NULL, question_th VARCHAR(300) NULL, question_zh VARCHAR(300) NULL,
  answer_lo TEXT NOT NULL, answer_en TEXT NOT NULL, answer_th TEXT NULL, answer_zh TEXT NULL,
  sort        SMALLINT     NOT NULL DEFAULT 0,
  is_visible  TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE hero_slides (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  media_id    INT UNSIGNED NOT NULL,             -- ຮູບ ຫຼື ວິດີໂອ
  poster_id   INT UNSIGNED NULL,                 -- ຮູບ poster ຂອງວິດີໂອ
  eyebrow_lo VARCHAR(120) NULL, eyebrow_en VARCHAR(120) NULL, eyebrow_th VARCHAR(120) NULL, eyebrow_zh VARCHAR(120) NULL,
  title_lo VARCHAR(200) NOT NULL, title_en VARCHAR(200) NOT NULL, title_th VARCHAR(200) NULL, title_zh VARCHAR(200) NULL,
  text_lo VARCHAR(400) NULL, text_en VARCHAR(400) NULL, text_th VARCHAR(400) NULL, text_zh VARCHAR(400) NULL,
  button_lo VARCHAR(60) NULL, button_en VARCHAR(60) NULL, button_th VARCHAR(60) NULL, button_zh VARCHAR(60) NULL,
  button_page VARCHAR(160) NULL,                 -- products / healthcare / product-ct-scan (ບໍ່ມີ .html)
  sort        SMALLINT     NOT NULL DEFAULT 0,
  is_visible  TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_hero_media  FOREIGN KEY (media_id)  REFERENCES media(id) ON DELETE RESTRICT,
  CONSTRAINT fk_hero_poster FOREIGN KEY (poster_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE seo_pages (
  page        VARCHAR(64)  NOT NULL PRIMARY KEY, -- about, vision, services, ...
  title_lo VARCHAR(160) NULL, title_en VARCHAR(160) NULL, title_th VARCHAR(160) NULL, title_zh VARCHAR(160) NULL,
  desc_lo VARCHAR(300) NULL, desc_en VARCHAR(300) NULL, desc_th VARCHAR(300) NULL, desc_zh VARCHAR(300) NULL,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_log (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NULL,
  action      VARCHAR(32)  NOT NULL,             -- create / update / delete / publish / export / login
  entity      VARCHAR(32)  NULL,                 -- product / news / message / ...
  entity_id   INT UNSIGNED NULL,
  details     JSON         NULL,
  ip          VARBINARY(16) NULL,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY ix_activity_time (created_at),
  KEY ix_activity_entity (entity, entity_id),
  CONSTRAINT fk_activity_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

## 5. ຄ່າໃນ `settings`

| key | ຕົວຢ່າງ value (JSON) |
|---|---|
| `contact` | `{"tel":"(856-21) 563121","mobile":"020 5589 2929","fax":"(856-21) 562949","email":"keokhounma@xtklaos.com","whatsapp":"8562055892929"}` |
| `address` | `{"lo":"ບ້ານ ດອນແດງ, ...","en":"...","th":"...","zh":"..."}` |
| `map` | `{"query":"Dondeng Village, Chanthabouly, Vientiane, Laos"}` |
| `stats` | `{"founded":2016,"experience":20}` |
| `notify_emails` | `["keokhounma@xtklaos.com"]` |
| `analytics_id` | `""` |
| `noindex` | `true` (ປ່ຽນເປັນ `false` ຕອນ go-live) |
| `content_changed_at` | `"2026-10-22T10:00:00"` (ອັບເດດທຸກຄັ້ງທີ່ແກ້ເນື້ອຫາ, ໃຊ້ສະແດງ "ຍັງບໍ່ເຜີຍແຜ່") |

## 6. ຂໍ້ມູນເລີ່ມຕົ້ນ (Seed)
ຂໍ້ມູນປັດຈຸບັນໃນ `build/build.py` ແລະ `build/strings*.py` ຍ້າຍເຂົ້າ DB ໃນ 2 ຂັ້ນ:
1. `build/export_seed.py` (Python, ແລ່ນໃນເຄື່ອງ) ສົ່ງອອກ `DIVS`, `NEWS`, `ITEMS`, ຂໍ້ຄວາມຂອງຂະແໜງ/ກຸ່ມ/FAQ ແລະ ຂໍ້ມູນຕິດຕໍ່ ເປັນ `database/seed/seed.json`.
2. `php bin/seed.php` ອ່ານ JSON ແລ້ວ `INSERT` ລົງ DB ແລະ ລົງທະບຽນຮູບທີ່ມີຢູ່ແລ້ວໃນ `media`.

| ຂໍ້ມູນ | ຈຳນວນ |
|---|---|
| ຂະແໜງ | 5 |
| ກຸ່ມສິນຄ້າ | 12 |
| ສິນຄ້າ | 60 (49 ມີຮູບ/ໜ້າລາຍລະອຽດ) |
| ສິນຄ້າແນະນຳ | 8 (`h00 h01 h02 l00 l02 s01 c00 h03`) |
| ຂ່າວ | 2 |
| FAQ | 5 |
| Hero | 3 |
| ຜູ້ສະໜອງ | ລໍຂໍ້ມູນຈາກລູກຄ້າ ([08 §7](08-suppliers.md)). ຖ້າ P0-A10 ເຮັດແລ້ວ, ດຶງຈາກ `SUPPLIERS` ໃນ `build.py` |

## 7. Index ແລະ ຄວາມໄວ
- ຂໍ້ມູນທັງໝົດມີພຽງຫຼັກຮ້ອຍແຖວ. Publisher ໂຫຼດທັງໝົດໃນຄັ້ງດຽວ, ສະນັ້ນບໍ່ຕ້ອງ optimize query ຫຼາຍ.
- ຕາຕະລາງທີ່ຂະຫຍາຍເລື້ອຍໆ (`messages`, `rate_limits`, `login_attempts`, `activity_log`) ມີ index ຕາມເວລາ. Cron ລຶບ `rate_limits` ອາຍຸ > 1 ວັນ ແລະ `login_attempts` ອາຍຸ > 30 ວັນ.
