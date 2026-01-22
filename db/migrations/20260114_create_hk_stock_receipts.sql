-- Migration: create tables for stock receipts

CREATE TABLE IF NOT EXISTS `hk_stock_receipts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id` INT NOT NULL,
  `invoice_no` VARCHAR(128) DEFAULT NULL,
  `received_by` VARCHAR(64) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_branch` (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hk_stock_receipt_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `receipt_id` INT UNSIGNED NOT NULL,
  `hk_item_id` INT UNSIGNED NOT NULL,
  `received_qty` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `indent_item_id` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_receipt` (`receipt_id`),
  INDEX `idx_item` (`hk_item_id`),
  CONSTRAINT `fk_receipt_items_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `hk_stock_receipts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rollback: DROP TABLE IF EXISTS `hk_stock_receipt_items`; DROP TABLE IF EXISTS `hk_stock_receipts`;
