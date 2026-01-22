-- Migration: Add unique constraint and FKs to hk_branch_ideal_qty
-- Created: 2026-01-14

/*
Idempotent migration to add:
 - UNIQUE(branch_id, hk_item_id)
 - FK hk_item_id -> hk_items(id) ON DELETE CASCADE
 - FK branch_id -> branches(branch_id) ON DELETE CASCADE

Run on staging first and ensure tables exist and are empty of duplicate branch+item pairs.
*/

-- 0) Safety: ensure table exists
SELECT TABLE_NAME FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hk_branch_ideal_qty';

-- 1) Remove potential duplicate rows (optional: manual review recommended)
-- Find duplicates:
SELECT branch_id, hk_item_id, COUNT(*) AS cnt
FROM hk_branch_ideal_qty
GROUP BY branch_id, hk_item_id
HAVING cnt > 1;

-- To deduplicate automatically (keeps the MIN(id)): (Run after review)
-- DELETE hbi FROM hk_branch_ideal_qty hbi
-- JOIN (
--   SELECT MIN(id) AS keep_id, branch_id, hk_item_id
--   FROM hk_branch_ideal_qty
--   GROUP BY branch_id, hk_item_id
-- ) keepers ON keepers.branch_id = hbi.branch_id AND keepers.hk_item_id = hbi.hk_item_id
-- WHERE hbi.id != keepers.keep_id;

-- 2) Add UNIQUE index if not exists
SET @idx_exists := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hk_branch_ideal_qty' AND INDEX_NAME = 'uq_hbix_branch_item'
);
SET @sql := IF(@idx_exists = 0,
  'ALTER TABLE hk_branch_ideal_qty ADD UNIQUE KEY uq_hbix_branch_item (branch_id, hk_item_id);',
  'SELECT "index exists";'
);
PREPARE st1 FROM @sql; EXECUTE st1; DEALLOCATE PREPARE st1;

-- 3) Add fk hk_item_id -> hk_items(id) if not exists
SET @fk_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS tc
  JOIN information_schema.KEY_COLUMN_USAGE kcu
    ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME AND tc.TABLE_NAME = kcu.TABLE_NAME
  WHERE tc.TABLE_SCHEMA = DATABASE()
    AND tc.TABLE_NAME = 'hk_branch_ideal_qty'
    AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
    AND kcu.COLUMN_NAME = 'hk_item_id'
);
SET @sql_fk := IF(@fk_exists = 0,
  'ALTER TABLE hk_branch_ideal_qty ADD CONSTRAINT fk_hbix_item FOREIGN KEY (hk_item_id) REFERENCES hk_items(id) ON DELETE CASCADE ON UPDATE CASCADE;',
  'SELECT "fk exists";'
);
PREPARE st2 FROM @sql_fk; EXECUTE st2; DEALLOCATE PREPARE st2;

-- 4) Add fk branch_id -> branches(branch_id) if not exists
SET @fk2_exists := (
  SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS tc
  JOIN information_schema.KEY_COLUMN_USAGE kcu
    ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME AND tc.TABLE_NAME = kcu.TABLE_NAME
  WHERE tc.TABLE_SCHEMA = DATABASE()
    AND tc.TABLE_NAME = 'hk_branch_ideal_qty'
    AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
    AND kcu.COLUMN_NAME = 'branch_id'
);
SET @sql_fk2 := IF(@fk2_exists = 0,
  'ALTER TABLE hk_branch_ideal_qty ADD CONSTRAINT fk_hbix_branch FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE ON UPDATE CASCADE;',
  'SELECT "fk exists";'
);
PREPARE st3 FROM @sql_fk2; EXECUTE st3; DEALLOCATE PREPARE st3;

-- 5) Verification: show constraints
SELECT CONSTRAINT_NAME, CONSTRAINT_TYPE
FROM information_schema.TABLE_CONSTRAINTS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'hk_branch_ideal_qty';

-- Rollback steps (manual):
-- ALTER TABLE hk_branch_ideal_qty DROP FOREIGN KEY fk_hbix_item;
-- ALTER TABLE hk_branch_ideal_qty DROP FOREIGN KEY fk_hbix_branch;
-- ALTER TABLE hk_branch_ideal_qty DROP INDEX uq_hbix_branch_item;

-- End of migration
