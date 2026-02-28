-- Add cc_emails column to email_templates
ALTER TABLE `email_templates`
  ADD COLUMN `cc_emails` TEXT DEFAULT NULL AFTER `variables`;
