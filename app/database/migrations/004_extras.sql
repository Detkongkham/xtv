-- Phase 3: FAQ, hero slides, SEO of fixed pages, activity log (docs/plan/04-database.md §4.4)
-- Translated fields have _lo/_en/_th/_zh columns; empty _th/_zh fall back to _en.

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
