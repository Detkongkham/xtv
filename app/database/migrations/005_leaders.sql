-- Phase 3: owners and directors on the about page (about.html), managed in /admin/leaders.
-- Translated fields have _lo/_en/_th/_zh columns; empty _th/_zh fall back to _en.

CREATE TABLE leaders (
  id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  photo_id    INT UNSIGNED NULL,                 -- ຮູບບຸກຄົນ (ລວງຕັ້ງ)
  name_lo VARCHAR(160) NOT NULL, name_en VARCHAR(160) NOT NULL, name_th VARCHAR(160) NULL, name_zh VARCHAR(160) NULL,
  position_lo VARCHAR(160) NOT NULL, position_en VARCHAR(160) NOT NULL, position_th VARCHAR(160) NULL, position_zh VARCHAR(160) NULL,
  bio_lo TEXT NOT NULL, bio_en TEXT NOT NULL, bio_th TEXT NULL, bio_zh TEXT NULL,
  sort        SMALLINT     NOT NULL DEFAULT 0,
  is_visible  TINYINT(1)   NOT NULL DEFAULT 1,
  created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_leader_photo FOREIGN KEY (photo_id) REFERENCES media(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
