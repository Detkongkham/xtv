-- Phase 2: site content edited in the admin and rendered by the Publisher (docs/plan/04-database.md §4.2, §4.3)
-- Translated fields have _lo/_en/_th/_zh columns; empty _th/_zh fall back to _en.

CREATE TABLE media (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  kind           ENUM('image','pdf','video') NOT NULL,
  -- path under public/ of the original file. Uploads: uploads/2026/10/ab12cd34.jpg with sizes in variants.
  -- Files that came with the old site keep their place: assets/img/products/h00.jpg (variants NULL)
  path           VARCHAR(255) NOT NULL,
  original_name  VARCHAR(255) NOT NULL,
  mime           VARCHAR(100) NOT NULL,
  size_bytes     INT UNSIGNED NOT NULL,
  width          SMALLINT UNSIGNED NULL,
  height         SMALLINT UNSIGNED NULL,
  variants       JSON         NULL,              -- {"480":{"webp":"..","jpg":".."},"960":{..},"1600":{..}}
  alt_lo VARCHAR(255) NULL, alt_en VARCHAR(255) NULL, alt_th VARCHAR(255) NULL, alt_zh VARCHAR(255) NULL,
  created_by     INT UNSIGNED NULL,
  created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_media_path (path),
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
  code         VARCHAR(32)  NOT NULL,            -- g_imaging (anchor #g_imaging)
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
  name            VARCHAR(160) NOT NULL,         -- one name in every language
  image_id        INT UNSIGNED NULL,             -- NULL = "ask for a price" card, no detail page
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

CREATE TABLE suppliers (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug           VARCHAR(120) NOT NULL,          -- -> supplier-<slug>.html
  name           VARCHAR(160) NOT NULL,          -- brand name, same in every language
  country_code   CHAR(2)      NOT NULL,          -- ISO 3166-1 alpha-2, names in app/lang/countries.php
  website_url    VARCHAR(255) NULL,
  logo_id        INT UNSIGNED NULL,
  logo_on_dark   TINYINT(1)   NOT NULL DEFAULT 0,-- white logo, needs a dark tile
  partnership    ENUM('exclusive','authorized','distributor') NULL,  -- shown only once the client confirms it
  since_year     SMALLINT UNSIGNED NULL,
  certificate_id INT UNSIGNED NULL,
  summary_lo VARCHAR(300) NULL, summary_en VARCHAR(300) NULL, summary_th VARCHAR(300) NULL, summary_zh VARCHAR(300) NULL,
  about_lo TEXT NULL, about_en TEXT NULL, about_th TEXT NULL, about_zh TEXT NULL,
  is_featured    TINYINT(1)   NOT NULL DEFAULT 0,-- key supplier (at most 6, checked in the app)
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

-- product <-> supplier (many to many). A supplier's divisions are derived from its products, never stored.
CREATE TABLE product_suppliers (
  product_id   INT UNSIGNED NOT NULL,
  supplier_id  INT UNSIGNED NOT NULL,
  sort         SMALLINT     NOT NULL DEFAULT 0,  -- 0 = primary supplier, shown first
  PRIMARY KEY (product_id, supplier_id),
  KEY ix_prodsup_supplier (supplier_id),
  CONSTRAINT fk_prodsup_product  FOREIGN KEY (product_id)  REFERENCES products(id)  ON DELETE CASCADE,
  CONSTRAINT fk_prodsup_supplier FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
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
  link_url      VARCHAR(255) NULL,               -- "view products" button; with an empty body the card links here instead
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
  division_id  INT UNSIGNED NULL,                -- NULL = company document
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
  from_page   VARCHAR(160) NOT NULL,             -- product-old-name (no .html)
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
