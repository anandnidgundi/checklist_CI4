-- Add optional form_id to email_templates so templates can be bound to a specific form
ALTER TABLE `email_templates`
  ADD COLUMN `form_id` INT(11) NULL DEFAULT NULL AFTER `event_key`,
  ADD INDEX `idx_email_templates_form_id` (`form_id`);
