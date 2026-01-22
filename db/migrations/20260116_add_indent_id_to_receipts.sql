-- Add indent_id column to hk_stock_receipts table
-- Migration: 20260116_add_indent_id_to_receipts
-- Created: 2026-01-16

ALTER TABLE `hk_stock_receipts` 
ADD COLUMN `indent_id` INT(11) NULL AFTER `branch_id`,
ADD INDEX `idx_indent_id` (`indent_id`);

-- Optional: Add foreign key constraint (uncomment if needed)
-- ALTER TABLE `hk_stock_receipts` 
-- ADD CONSTRAINT `fk_receipts_indent` FOREIGN KEY (`indent_id`) REFERENCES `hk_monthly_indents`(`id`) ON DELETE SET NULL;

-- Rollback: 
-- ALTER TABLE `hk_stock_receipts` DROP FOREIGN KEY `fk_receipts_indent`; -- if FK was added
-- ALTER TABLE `hk_stock_receipts` DROP INDEX `idx_indent_id`;
-- ALTER TABLE `hk_stock_receipts` DROP COLUMN `indent_id`;
