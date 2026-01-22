-- Migration: create tables for monthly indents
-- Run this on staging first and verify

CREATE TABLE IF NOT EXISTS `hk_monthly_indents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `branch_id` INT NOT NULL,
  `month` CHAR(7) NOT NULL COMMENT 'YYYY-MM',
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_by` VARCHAR(64) DEFAULT NULL,
  `approved_by` VARCHAR(64) DEFAULT NULL,
  `notes` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_branch_month` (`branch_id`, `month`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hk_monthly_indent_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `indent_id` INT UNSIGNED NOT NULL,
  `hk_item_id` INT UNSIGNED NOT NULL,
  `qty_requested` DECIMAL(10,2) NOT NULL DEFAULT 0,
  `qty_approved` DECIMAL(10,2) DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `idx_indent` (`indent_id`),
  INDEX `idx_item` (`hk_item_id`),
  CONSTRAINT `fk_indent_items_indent` FOREIGN KEY (`indent_id`) REFERENCES `hk_monthly_indents`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Please verify that hk_items and branches exist and the column types are compatible
-- Manual rollback: DROP TABLE IF EXISTS `hk_monthly_indent_items`; DROP TABLE IF EXISTS `hk_monthly_indents`;
