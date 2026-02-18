-- Migration for email_templates table
CREATE TABLE `email_templates` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `event_key` VARCHAR(100) NOT NULL UNIQUE,
  `subject` VARCHAR(255) NOT NULL,
  `html_template` TEXT NOT NULL,
  `variables` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;