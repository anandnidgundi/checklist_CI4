-- Migration: Add item_type to hk_items and set existing items to 'Consumables'
-- Created: 2026-01-14

-- 1) Add column if it doesn't exist
ALTER TABLE hk_items
  ADD COLUMN IF NOT EXISTS item_type VARCHAR(20) NOT NULL DEFAULT 'Consumables';

-- 2) Backfill existing records (make sure all existing items are Consumables)
UPDATE hk_items SET item_type = 'Consumables' WHERE item_type IS NULL OR item_type = '';

-- 3) Add index to speed filtering by type
DO
BEGIN
  DECLARE _idx_cnt INT DEFAULT 0;
  SELECT COUNT(*) INTO _idx_cnt FROM information_schema.STATISTICS
   WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hk_items' AND INDEX_NAME = 'idx_hk_items_item_type';
  IF _idx_cnt = 0 THEN
    ALTER TABLE hk_items ADD INDEX idx_hk_items_item_type (item_type);
  END IF;
END;

-- Rollback steps (manual):
-- ALTER TABLE hk_items DROP INDEX idx_hk_items_item_type;
-- ALTER TABLE hk_items DROP COLUMN item_type;

-- End of migration
