-- Migration: Migrate old `hk_materials` → `hk_items` and convert `hk_details.hk_material` to `hk_item_id`
-- Created: 2026-01-14
-- Run on staging first. Review the verification SELECTs before dropping legacy columns/tables.

/*
Notes:
- This script is written to be idempotent and to include checkpoints. Some ALTER TABLE operations may commit auto-magically depending on your server.
- Always take a backup before running in production.
*/

-- 1) Insert distinct names from hk_materials into hk_items (if not already present)
INSERT INTO hk_items (name, created_at)
SELECT DISTINCT hm.hk_material_name, NOW()
FROM hk_materials hm
LEFT JOIN hk_items hi ON hi.name = hm.hk_material_name
WHERE hi.id IS NULL;

-- 2) Add hk_item_id column to hk_details (if missing)
ALTER TABLE hk_details
  ADD COLUMN IF NOT EXISTS hk_item_id INT NULL;

-- 3) Backfill hk_details.hk_item_id by matching hk_material -> hk_items.name
UPDATE hk_details hd
JOIN hk_items hi ON hi.name = hd.hk_material
SET hd.hk_item_id = hi.id
WHERE hd.hk_item_id IS NULL;

-- 4) Create index to speed joins (if not exists)
DO
BEGIN
  DECLARE _cnt INT DEFAULT 0;
  SELECT COUNT(*) INTO _cnt
    FROM information_schema.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hk_details' AND INDEX_NAME = 'idx_hk_details_hk_item_id';
  IF _cnt = 0 THEN
    ALTER TABLE hk_details ADD INDEX idx_hk_details_hk_item_id (hk_item_id);
  END IF;
END;

-- 5) Add foreign key (safe: ON DELETE SET NULL) if not exists
-- (MariaDB/MySQL will error if constraint already exists; we check first)
DO
BEGIN
  DECLARE _fk_cnt INT DEFAULT 0;
  SELECT COUNT(*) INTO _fk_cnt
    FROM information_schema.TABLE_CONSTRAINTS tc
    JOIN information_schema.KEY_COLUMN_USAGE kcu
      ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
     AND tc.TABLE_NAME = kcu.TABLE_NAME
   WHERE tc.TABLE_SCHEMA = DATABASE()
     AND tc.TABLE_NAME = 'hk_details'
     AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
     AND kcu.COLUMN_NAME = 'hk_item_id';

  IF _fk_cnt = 0 THEN
    ALTER TABLE hk_details
      ADD CONSTRAINT fk_hk_details_hk_item_id
      FOREIGN KEY (hk_item_id) REFERENCES hk_items(id)
      ON DELETE SET NULL ON UPDATE CASCADE;
  END IF;
END;

-- 6) Verification queries (run and inspect before step 7):
-- Count rows that failed to map (should be 0 to safely drop legacy data)
SELECT COUNT(*) AS unmapped_details
FROM hk_details hd
WHERE hd.hk_item_id IS NULL OR hd.hk_item_id = 0;

-- Optionally list distinct unmapped material names
SELECT DISTINCT hd.hk_material AS unmapped_materials
FROM hk_details hd
LEFT JOIN hk_items hi ON hi.name = hd.hk_material
WHERE hi.id IS NULL;

-- 7) If verification above shows 0 unmapped rows, you may proceed to drop legacy column and table
-- (Run only after manual verification)
-- ALTER TABLE hk_details DROP COLUMN hk_material;
-- DROP TABLE IF EXISTS hk_materials;

-- 8) Rollback (manual steps) — run if you need to undo migration
-- a) Recreate hk_materials table (if missing) and repopulate from hk_items
-- CREATE TABLE IF NOT EXISTS hk_materials (
--   id INT PRIMARY KEY AUTO_INCREMENT,
--   hk_material_name VARCHAR(100) NOT NULL
-- );
-- INSERT INTO hk_materials (hk_material_name)
-- SELECT DISTINCT name FROM hk_items
-- WHERE name NOT IN (SELECT hk_material_name FROM hk_materials);

-- b) Recreate hk_material column on hk_details and backfill from hk_item_id
-- ALTER TABLE hk_details ADD COLUMN IF NOT EXISTS hk_material VARCHAR(100) NOT NULL DEFAULT '';
-- UPDATE hk_details hd
-- JOIN hk_items hi ON hi.id = hd.hk_item_id
-- SET hd.hk_material = hi.name
-- WHERE hd.hk_item_id IS NOT NULL;

-- c) Drop fk/index/hk_item_id if you need to revert
-- ALTER TABLE hk_details DROP FOREIGN KEY fk_hk_details_hk_item_id;
-- ALTER TABLE hk_details DROP INDEX idx_hk_details_hk_item_id;
-- ALTER TABLE hk_details DROP COLUMN hk_item_id;

-- END OF MIGRATION
