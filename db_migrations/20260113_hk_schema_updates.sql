-- Migration: HK schema updates (2026-01-13)
-- Purpose: Add file metadata, link files to hk_requirements, add reconciliation table/procedure, add audit columns
-- IMPORTANT: Run on dev/staging FIRST. Take DB backup (mysqldump) before running.

-- === Safety checks: ensure no implicit non-numeric hkr_id values ===
-- Set any non-numeric hkr_id to NULL so ALTER to INT won't fail
UPDATE files SET hkr_id = NULL WHERE TRIM(IFNULL(hkr_id,'')) = '' OR hkr_id REGEXP '[^0-9]';

-- === Step 1: Convert files.hkr_id to INT, add metadata columns and indexes ===
ALTER TABLE files
  ENGINE = InnoDB,
  DEFAULT CHARSET = utf8mb4,
  MODIFY COLUMN hkr_id INT(11) NULL,
  ADD COLUMN original_name VARCHAR(255) NULL AFTER file_name,
  ADD COLUMN mime_type VARCHAR(100) NULL AFTER original_name,
  ADD COLUMN file_size INT(11) NULL AFTER mime_type,
  ADD COLUMN uploaded_by INT(11) NULL AFTER file_size,
  ADD COLUMN uploaded_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER uploaded_by,
  ADD INDEX idx_files_hkr_id (hkr_id),
  ADD INDEX idx_files_uploaded_by (uploaded_by);

-- Add FK to hk_requirements (optional — if you prefer referential integrity)
ALTER TABLE files
  ADD CONSTRAINT fk_files_hkr FOREIGN KEY (hkr_id) REFERENCES hk_requirements(hkr_id) ON DELETE SET NULL ON UPDATE CASCADE;

-- === Step 2: Add audit columns to hk_details ===
-- Add created_at if missing, and updated_by
ALTER TABLE hk_details
  ADD COLUMN IF NOT EXISTS created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP AFTER hkr_id,
  ADD COLUMN IF NOT EXISTS updated_by VARCHAR(50) NULL AFTER updated_at;

-- === Step 3: Add reconciliation logging table ===
CREATE TABLE IF NOT EXISTS hk_balance_reconciliations (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  branch_id INT NOT NULL,
  hk_item_id INT NOT NULL,
  balance_before DECIMAL(12,2) DEFAULT 0.00,
  computed_balance DECIMAL(12,2) DEFAULT 0.00,
  discrepancy DECIMAL(12,2) DEFAULT 0.00,
  note TEXT NULL,
  reconciled_by INT NULL,
  reconciled_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_recon_branch_item (branch_id, hk_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- === Step 4: Add reconciliation audit fields to hk_stock_balances ===
ALTER TABLE hk_stock_balances
  ADD COLUMN IF NOT EXISTS last_reconciled_at DATETIME NULL AFTER last_updated,
  ADD COLUMN IF NOT EXISTS last_reconciled_by INT NULL AFTER last_reconciled_at;

-- === Step 5: Create stored procedure to reconcile balances ===
DELIMITER $$
DROP PROCEDURE IF EXISTS sp_reconcile_hk_balances$$
CREATE PROCEDURE sp_reconcile_hk_balances()
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE sb_id INT;
  DECLARE b_id INT;
  DECLARE item_id INT;
  DECLARE opening DECIMAL(12,2);
  DECLARE prev_balance DECIMAL(12,2);
  DECLARE total_received DECIMAL(12,2);
  DECLARE total_consumed DECIMAL(12,2);
  DECLARE computed DECIMAL(12,2);

  DECLARE cur CURSOR FOR
    SELECT id, branch_id, hk_item_id, opening_qty, current_balance FROM hk_stock_balances;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  START TRANSACTION;

  OPEN cur;
  read_loop: LOOP
    FETCH cur INTO sb_id, b_id, item_id, opening, prev_balance;
    IF done THEN
      LEAVE read_loop;
    END IF;

    SELECT IFNULL(SUM(received_qty),0) INTO total_received FROM hk_stock_received WHERE branch_id = b_id AND hk_item_id = item_id;
    SELECT IFNULL(SUM(consumed_qty),0) INTO total_consumed FROM hk_consumptions WHERE branch_id = b_id AND hk_item_id = item_id;

    SET computed = opening + IFNULL(total_received,0) - IFNULL(total_consumed,0);

    -- If discrepancy, insert reconciliation record
    IF (prev_balance IS NULL OR prev_balance <> computed) THEN
      INSERT INTO hk_balance_reconciliations (branch_id, hk_item_id, balance_before, computed_balance, discrepancy, note, reconciled_by, reconciled_at)
      VALUES (b_id, item_id, IFNULL(prev_balance,0), computed, (computed - IFNULL(prev_balance,0)), 'auto-reconcile', NULL, NOW());

      -- Update the balance and totals
      UPDATE hk_stock_balances
      SET current_balance = computed,
          total_received = (SELECT IFNULL(SUM(received_qty),0) FROM hk_stock_received WHERE branch_id = b_id AND hk_item_id = item_id),
          total_consumed = (SELECT IFNULL(SUM(consumed_qty),0) FROM hk_consumptions WHERE branch_id = b_id AND hk_item_id = item_id),
          last_reconciled_at = NOW()
      WHERE id = sb_id;
    END IF;

  END LOOP;

  CLOSE cur;

  COMMIT;
END$$
DELIMITER ;

-- === Step 6: Example usage / one-off reconciliation run ===
-- CALL sp_reconcile_hk_balances();

-- === Step 7: Add helpful indexes for performance ===
ALTER TABLE hk_stock_received ADD INDEX idx_received_branch_item (branch_id, hk_item_id);
ALTER TABLE hk_consumptions ADD INDEX idx_consumed_branch_item (branch_id, hk_item_id);

-- === Step 8: Safety / quick-check queries to run after migration ===
-- 1) Check files linking
-- SELECT COUNT(*) AS files_with_hkr FROM files WHERE hkr_id IS NOT NULL;
-- 2) Check any files with null names
-- SELECT file_id, file_name FROM files WHERE file_name IS NULL OR file_name = '' LIMIT 10;
-- 3) Run reconciliation (recommended on staging)
-- CALL sp_reconcile_hk_balances();

-- End of migration
