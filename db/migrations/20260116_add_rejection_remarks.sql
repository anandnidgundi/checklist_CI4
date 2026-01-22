-- Migration: Add rejection_remarks to hk_monthly_indents
-- Run this on staging first and verify

ALTER TABLE `hk_monthly_indents`
    ADD COLUMN `rejection_remarks` TEXT DEFAULT NULL;
