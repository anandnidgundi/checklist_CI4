-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jan 30, 2026 at 11:42 AM
-- Server version: 8.0.45
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vdcapp2_bmcmchecklist`
--

-- --------------------------------------------------------

--
-- Table structure for table `area`
--

DROP TABLE IF EXISTS `area`;
CREATE TABLE `area` (
  `id` int NOT NULL,
  `area` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_on` date NOT NULL,
  `status` char(1) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

DROP TABLE IF EXISTS `auth_tokens`;
CREATE TABLE `auth_tokens` (
  `id` int NOT NULL,
  `emp_code` int NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `expiry_date` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bi_centres`
--

DROP TABLE IF EXISTS `bi_centres`;
CREATE TABLE `bi_centres` (
  `id` int NOT NULL,
  `branch` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status` varchar(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'A',
  `createdby` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `createdon` datetime NOT NULL,
  `cluster` int NOT NULL,
  `manager` int NOT NULL,
  `branding` text COLLATE utf8mb4_general_ci NOT NULL,
  `bio` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `mobile` varchar(20) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blacklisted_tokens`
--

DROP TABLE IF EXISTS `blacklisted_tokens`;
CREATE TABLE `blacklisted_tokens` (
  `id` int UNSIGNED NOT NULL,
  `token` varchar(355) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bmcm`
--

DROP TABLE IF EXISTS `bmcm`;
CREATE TABLE `bmcm` (
  `id` int NOT NULL,
  `emp_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `isAdmin` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'NO',
  `createdDTM` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bm_tasks`
--

DROP TABLE IF EXISTS `bm_tasks`;
CREATE TABLE `bm_tasks` (
  `mid` int NOT NULL,
  `mt0100` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0101` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0102` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0104` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `mt0105` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0103` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `mt0200` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0201` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0202` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0204` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0205` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0203` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0300` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0301` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0302` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0400` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0401` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0402` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0500` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0501` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0502` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0600` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0601` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0602` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0700` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0701` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0702` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0800` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0801` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0802` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0900` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0901` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0902` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0903` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1000` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1001` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1002` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1100` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1101` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1102` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1200` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1201` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1202` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emp_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taskDate` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` char(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'N',
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modifiedDTM` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bm_tasks_logs`
--

DROP TABLE IF EXISTS `bm_tasks_logs`;
CREATE TABLE `bm_tasks_logs` (
  `mid` int NOT NULL,
  `mt0100` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0101` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0102` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0104` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `mt0105` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0103` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `mt0200` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0201` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0202` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0204` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0205` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0203` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0300` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0301` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0302` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0400` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0401` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0402` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0500` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0501` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0502` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0600` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0601` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0602` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0700` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0701` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0702` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0800` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0801` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0802` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0900` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0901` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0902` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0903` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1000` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1001` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1002` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1100` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1101` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1102` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1200` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1201` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1202` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emp_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` datetime DEFAULT NULL,
  `taskDate` date DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` char(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'N',
  `updated_by` varchar(25) COLLATE utf8mb4_general_ci NOT NULL,
  `modifiedDTM` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bm_weekly_list`
--

DROP TABLE IF EXISTS `bm_weekly_list`;
CREATE TABLE `bm_weekly_list` (
  `bmw_id` int NOT NULL,
  `branch_id` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cluster_id` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0100` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0101` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0102` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0200` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0201` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0202` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0300` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0301` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0302` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0400` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0401` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0402` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0500` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0501` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0502` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0600` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0601` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0602` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0700` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0701` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0702` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0800` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0801` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0802` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0900` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0901` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_0902` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1000` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1001` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1002` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1100` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1101` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1102` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1200` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1201` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1202` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1300` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1301` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1302` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1400` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1401` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1402` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1500` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1501` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1502` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1600` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1601` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1602` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1700` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1701` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `w_1702` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdBy` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modifiedDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
CREATE TABLE `branches` (
  `branch_id` int NOT NULL,
  `branch` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'A',
  `created_by` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branchesmapped`
--

DROP TABLE IF EXISTS `branchesmapped`;
CREATE TABLE `branchesmapped` (
  `id` int NOT NULL,
  `emp_code` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `area_id` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `area` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch_id` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cluster_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cluster` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zone_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `zone` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_actions`
--

DROP TABLE IF EXISTS `branding_actions`;
CREATE TABLE `branding_actions` (
  `id` int UNSIGNED NOT NULL,
  `checklist_id` int UNSIGNED NOT NULL,
  `action_text` text COLLATE utf8mb4_general_ci NOT NULL,
  `priority` varchar(16) COLLATE utf8mb4_general_ci DEFAULT 'low',
  `target_date` date DEFAULT NULL,
  `assigned_to` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(32) COLLATE utf8mb4_general_ci DEFAULT 'open',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_checklists`
--

DROP TABLE IF EXISTS `branding_checklists`;
CREATE TABLE `branding_checklists` (
  `id` int UNSIGNED NOT NULL,
  `branch_id` int UNSIGNED DEFAULT NULL,
  `centre_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_visit` date NOT NULL,
  `visit_time` time NOT NULL,
  `audited_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_manager` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cluster_manager` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `created_by` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_checklist_items`
--

DROP TABLE IF EXISTS `branding_checklist_items`;
CREATE TABLE `branding_checklist_items` (
  `id` int UNSIGNED NOT NULL,
  `checklist_id` int UNSIGNED NOT NULL,
  `section` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `item_label` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `response` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_general_ci,
  `priority` tinyint UNSIGNED DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_checklist_records`
--

DROP TABLE IF EXISTS `branding_checklist_records`;
CREATE TABLE `branding_checklist_records` (
  `id` int NOT NULL,
  `branding_checklist_id` int NOT NULL,
  `section_id` int DEFAULT NULL,
  `sub_section_id` int DEFAULT NULL,
  `input_name` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `input_value` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `input_remark` text COLLATE utf8mb4_general_ci,
  `created_by` int DEFAULT NULL,
  `created_dtm` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_dtm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_photos`
--

DROP TABLE IF EXISTS `branding_photos`;
CREATE TABLE `branding_photos` (
  `id` int UNSIGNED NOT NULL,
  `checklist_id` int UNSIGNED NOT NULL,
  `filename` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_sections`
--

DROP TABLE IF EXISTS `branding_sections`;
CREATE TABLE `branding_sections` (
  `section_id` int NOT NULL,
  `section_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_sub_sections`
--

DROP TABLE IF EXISTS `branding_sub_sections`;
CREATE TABLE `branding_sub_sections` (
  `sub_section_id` int NOT NULL,
  `section_id` int NOT NULL,
  `sub_section_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cardiologist_ecg`
--

DROP TABLE IF EXISTS `cardiologist_ecg`;
CREATE TABLE `cardiologist_ecg` (
  `id` int NOT NULL,
  `nid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_count` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `bmid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cardiologist_tmt`
--

DROP TABLE IF EXISTS `cardiologist_tmt`;
CREATE TABLE `cardiologist_tmt` (
  `id` int NOT NULL,
  `nid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_count` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `bmid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `centres`
--

DROP TABLE IF EXISTS `centres`;
CREATE TABLE `centres` (
  `centre_id` int NOT NULL,
  `centre_name` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `phone` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `address` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `services` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `timing` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `manager` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `phone2` mediumtext CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci,
  `phlebo` enum('no','yes') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'no',
  `runner` enum('no','yes') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL DEFAULT 'no'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE `ci_sessions` (
  `id` varchar(128) COLLATE utf8mb4_bin NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_bin NOT NULL,
  `timestamp` int UNSIGNED NOT NULL DEFAULT '0',
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `cluster`
--

DROP TABLE IF EXISTS `cluster`;
CREATE TABLE `cluster` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_on` date NOT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` char(1) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clusters`
--

DROP TABLE IF EXISTS `clusters`;
CREATE TABLE `clusters` (
  `cluster_id` int NOT NULL,
  `cluster` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `branches` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'A',
  `created_by` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cluster_branch_map`
--

DROP TABLE IF EXISTS `cluster_branch_map`;
CREATE TABLE `cluster_branch_map` (
  `cb_id` int NOT NULL,
  `cluster_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cluster_zone_map`
--

DROP TABLE IF EXISTS `cluster_zone_map`;
CREATE TABLE `cluster_zone_map` (
  `cz_id` int NOT NULL,
  `cluster_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `zone_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clust_area_map`
--

DROP TABLE IF EXISTS `clust_area_map`;
CREATE TABLE `clust_area_map` (
  `cl_ar_id` int NOT NULL,
  `cluster_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `area_id` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cm_morning_tasks`
--

DROP TABLE IF EXISTS `cm_morning_tasks`;
CREATE TABLE `cm_morning_tasks` (
  `mid` int NOT NULL,
  `mt0100` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0101` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0102` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0200` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0201` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0202` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0300` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0301` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0302` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0400` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0401` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0402` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0500` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0501` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0502` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0600` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0601` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0602` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0700` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0701` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0702` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0800` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0801` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0802` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0900` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0901` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0902` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1000` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1001` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1002` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emp_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cluster_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taskDate` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modifiedDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cm_night_tasks`
--

DROP TABLE IF EXISTS `cm_night_tasks`;
CREATE TABLE `cm_night_tasks` (
  `cm_nid` int NOT NULL,
  `cm_nt0100` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0101` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0102` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0200` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0201` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0202` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0300` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0301` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0302` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0400` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0401` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0402` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0500` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0501` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0502` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0600` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0601` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0602` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0700` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0701` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0702` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0800` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0801` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0802` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0900` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0901` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt0902` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt1000` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt1001` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cm_nt1002` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emp_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cluster_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taskDate` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modifiedDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE `comments` (
  `comment_id` int NOT NULL,
  `comment_detail` varchar(500) COLLATE latin1_general_ci DEFAULT NULL,
  `tour_id` varchar(20) COLLATE latin1_general_ci DEFAULT NULL,
  `exp_id` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `l_id` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `commentBy` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `createdDTM` varchar(50) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ct`
--

DROP TABLE IF EXISTS `ct`;
CREATE TABLE `ct` (
  `id` int NOT NULL,
  `nid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_count` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `bmid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

DROP TABLE IF EXISTS `department`;
CREATE TABLE `department` (
  `id` int NOT NULL,
  `dep_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_bin;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
CREATE TABLE `departments` (
  `id` int NOT NULL,
  `cat_id` int NOT NULL,
  `dept_name` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `created_on` date NOT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` char(1) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diesel_consumption`
--

DROP TABLE IF EXISTS `diesel_consumption`;
CREATE TABLE `diesel_consumption` (
  `id` int NOT NULL,
  `branch_id` int NOT NULL,
  `cluster_id` int DEFAULT NULL,
  `zone_id` int DEFAULT NULL,
  `consumption_date` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `is_power_shutdown` varchar(12) COLLATE utf8mb4_general_ci NOT NULL,
  `is_generator_testing` varchar(12) COLLATE utf8mb4_general_ci NOT NULL,
  `testing_time_duration` varchar(12) COLLATE utf8mb4_general_ci NOT NULL,
  `power_shutdown` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `closing_stock_percentage` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `diesel_consumed` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `testing_diesel_consumed` int NOT NULL,
  `avg_consumption` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `closing_stock` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remarks` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdBy` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diesel_consumption_logs`
--

DROP TABLE IF EXISTS `diesel_consumption_logs`;
CREATE TABLE `diesel_consumption_logs` (
  `id` int NOT NULL,
  `branch_id` int NOT NULL,
  `cluster_id` int DEFAULT NULL,
  `zone_id` int DEFAULT NULL,
  `consumption_date` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `is_power_shutdown` varchar(12) COLLATE utf8mb4_general_ci NOT NULL,
  `is_generator_testing` varchar(12) COLLATE utf8mb4_general_ci NOT NULL,
  `testing_time_duration` varchar(12) COLLATE utf8mb4_general_ci NOT NULL,
  `testing_diesel_consumed` int NOT NULL,
  `power_shutdown` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `diesel_consumed` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avg_consumption` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `closing_stock` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `closing_stock_percentage` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `remarks` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdBy` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doc_count`
--

DROP TABLE IF EXISTS `doc_count`;
CREATE TABLE `doc_count` (
  `id` int NOT NULL,
  `nid` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_name` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bmid` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mri` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `ct` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `xray` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cardio_ecg` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cardio_tmt` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdBy` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `empareamap`
--

DROP TABLE IF EXISTS `empareamap`;
CREATE TABLE `empareamap` (
  `ea_id` int NOT NULL,
  `emp_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `area_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

DROP TABLE IF EXISTS `employee`;
CREATE TABLE `employee` (
  `id` int NOT NULL,
  `em_code` varchar(13) DEFAULT NULL,
  `first_name` varchar(20) DEFAULT NULL,
  `middle_name` varchar(18) DEFAULT NULL,
  `last_name` varchar(20) DEFAULT NULL,
  `em_gender` varchar(6) DEFAULT NULL,
  `em_role` varchar(50) DEFAULT NULL,
  `em_email` varchar(41) DEFAULT NULL,
  `em_phone` bigint DEFAULT NULL,
  `centre` varchar(100) DEFAULT NULL,
  `Department` varchar(27) DEFAULT NULL,
  `Designation` varchar(37) DEFAULT NULL,
  `em_password` varchar(40) DEFAULT NULL,
  `status` varchar(8) DEFAULT 'ACTIVE',
  `createdDTM` varchar(50) DEFAULT NULL,
  `createdBy` varchar(50) DEFAULT NULL,
  `updatedBy` varchar(50) DEFAULT NULL,
  `updatedDTM` varchar(50) DEFAULT NULL,
  `reportingManager` varchar(50) DEFAULT NULL,
  `functionalManager` varchar(50) DEFAULT NULL,
  `centre_id` varchar(50) DEFAULT NULL,
  `dep_id` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
CREATE TABLE `equipment` (
  `id` int NOT NULL,
  `dept_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_on` date NOT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` char(1) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

DROP TABLE IF EXISTS `files`;
CREATE TABLE `files` (
  `file_id` int NOT NULL,
  `file_name` varchar(200) COLLATE latin1_general_ci DEFAULT NULL,
  `tour_id` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `em_code` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `mid` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `nid` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `cm_mid` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `cm_nid` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `service_id` varchar(50) COLLATE latin1_general_ci NOT NULL,
  `bmw_id` varchar(50) COLLATE latin1_general_ci DEFAULT NULL,
  `diesel_id` varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  `power_id` varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  `hkr_id` varchar(10) COLLATE latin1_general_ci DEFAULT NULL,
  `createdDTM` varchar(50) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_branchwise_budget`
--

DROP TABLE IF EXISTS `hk_branchwise_budget`;
CREATE TABLE `hk_branchwise_budget` (
  `id` int NOT NULL,
  `branch_id` int NOT NULL,
  `cluster_id` int NOT NULL,
  `budget` decimal(12,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_branch_ideal_qty`
--

DROP TABLE IF EXISTS `hk_branch_ideal_qty`;
CREATE TABLE `hk_branch_ideal_qty` (
  `id` int NOT NULL,
  `branch_id` int NOT NULL,
  `hk_item_id` int NOT NULL,
  `ideal_qty` decimal(12,2) DEFAULT '0.00',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_consumptions`
--

DROP TABLE IF EXISTS `hk_consumptions`;
CREATE TABLE `hk_consumptions` (
  `id` int NOT NULL,
  `hkv_visit_id` int DEFAULT NULL,
  `branch_id` int NOT NULL,
  `hk_item_id` int NOT NULL,
  `cycle_no` tinyint NOT NULL,
  `cycle_from` date DEFAULT NULL,
  `cycle_to` date DEFAULT NULL,
  `consumed_qty` decimal(12,2) NOT NULL,
  `recorded_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `recorded_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `locked` char(1) COLLATE utf8mb4_general_ci DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_details`
--

DROP TABLE IF EXISTS `hk_details`;
CREATE TABLE `hk_details` (
  `id` int NOT NULL,
  `hkr_id` int NOT NULL,
  `hk_material` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `hk_make` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `hk_price` decimal(10,2) DEFAULT '0.00',
  `quantity` decimal(12,2) DEFAULT '0.00',
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT '0.00',
  `hk_item_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_items`
--

DROP TABLE IF EXISTS `hk_items`;
CREATE TABLE `hk_items` (
  `id` int NOT NULL,
  `name` varchar(150) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `unit` varchar(50) DEFAULT 'nos.',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  `item_type` varchar(20) NOT NULL DEFAULT 'Consumables'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_materials`
--

DROP TABLE IF EXISTS `hk_materials`;
CREATE TABLE `hk_materials` (
  `id` int NOT NULL,
  `hk_material_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_materials_remove`
--

DROP TABLE IF EXISTS `hk_materials_remove`;
CREATE TABLE `hk_materials_remove` (
  `id` int NOT NULL,
  `hk_material_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_monthly_indents`
--

DROP TABLE IF EXISTS `hk_monthly_indents`;
CREATE TABLE `hk_monthly_indents` (
  `id` int UNSIGNED NOT NULL,
  `branch_id` int NOT NULL,
  `month` char(7) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'YYYY-MM',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `requested_by` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `total_amount` decimal(16,2) NOT NULL DEFAULT '0.00',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` datetime DEFAULT NULL,
  `rejection_remarks` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_monthly_indent_items`
--

DROP TABLE IF EXISTS `hk_monthly_indent_items`;
CREATE TABLE `hk_monthly_indent_items` (
  `id` int UNSIGNED NOT NULL,
  `indent_id` int UNSIGNED NOT NULL,
  `hk_item_id` int UNSIGNED NOT NULL,
  `qty_requested` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(14,2) NOT NULL DEFAULT '0.00',
  `brand` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty_approved` decimal(10,2) DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `qty_received` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_notifications`
--

DROP TABLE IF EXISTS `hk_notifications`;
CREATE TABLE `hk_notifications` (
  `id` int NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `status` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `sent_at` datetime DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `hk_opening_stock`
--

DROP TABLE IF EXISTS `hk_opening_stock`;
CREATE TABLE `hk_opening_stock` (
  `id` int NOT NULL,
  `branch_id` int NOT NULL,
  `hk_item_id` int NOT NULL,
  `opening_qty` decimal(12,2) DEFAULT '0.00',
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_requirements`
--

DROP TABLE IF EXISTS `hk_requirements`;
CREATE TABLE `hk_requirements` (
  `hkr_id` int NOT NULL,
  `for_month` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `created_by` int NOT NULL,
  `remarks` text COLLATE utf8mb4_general_ci,
  `admin_remarks` text COLLATE utf8mb4_general_ci,
  `status` enum('PENDING','APPROVED','REJECTED','CANCELLED') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'PENDING',
  `created_dtm` datetime NOT NULL,
  `approved_by` int DEFAULT NULL,
  `approved_dtm` datetime DEFAULT NULL,
  `branch_id` int NOT NULL,
  `cluster_id` int NOT NULL,
  `isDeleted` char(1) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'N',
  `applied_amount` decimal(10,2) DEFAULT NULL,
  `budget_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_requirement_receipts`
--

DROP TABLE IF EXISTS `hk_requirement_receipts`;
CREATE TABLE `hk_requirement_receipts` (
  `id` int NOT NULL,
  `hkr_id` int NOT NULL,
  `hk_item_id` int NOT NULL,
  `expected_qty` decimal(12,3) NOT NULL DEFAULT '0.000',
  `received_qty` decimal(12,3) NOT NULL DEFAULT '0.000',
  `status` enum('pending','partial','received') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `latest_receipt_id` int DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_stock_balances`
--

DROP TABLE IF EXISTS `hk_stock_balances`;
CREATE TABLE `hk_stock_balances` (
  `id` int NOT NULL,
  `branch_id` int NOT NULL,
  `hk_item_id` int NOT NULL,
  `opening_qty` decimal(12,2) DEFAULT '0.00',
  `total_received` decimal(12,2) DEFAULT '0.00',
  `total_consumed` decimal(12,2) DEFAULT '0.00',
  `current_balance` decimal(12,2) DEFAULT '0.00',
  `last_updated` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_stock_receipts`
--

DROP TABLE IF EXISTS `hk_stock_receipts`;
CREATE TABLE `hk_stock_receipts` (
  `id` int UNSIGNED NOT NULL,
  `branch_id` int NOT NULL,
  `indent_id` int DEFAULT NULL,
  `invoice_no` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `received_by` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_stock_receipt_items`
--

DROP TABLE IF EXISTS `hk_stock_receipt_items`;
CREATE TABLE `hk_stock_receipt_items` (
  `id` int UNSIGNED NOT NULL,
  `receipt_id` int UNSIGNED NOT NULL,
  `hk_item_id` int NOT NULL,
  `received_qty` decimal(10,2) NOT NULL DEFAULT '0.00',
  `indent_item_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_stock_received`
--

DROP TABLE IF EXISTS `hk_stock_received`;
CREATE TABLE `hk_stock_received` (
  `id` int NOT NULL,
  `hkr_id` int DEFAULT NULL,
  `branch_id` int NOT NULL,
  `hk_item_id` int NOT NULL,
  `received_qty` decimal(12,2) NOT NULL,
  `received_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `source` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_visits`
--

DROP TABLE IF EXISTS `hk_visits`;
CREATE TABLE `hk_visits` (
  `id` int NOT NULL,
  `branch_id` int NOT NULL,
  `visit_dt` datetime DEFAULT CURRENT_TIMESTAMP,
  `cycle_no` tinyint DEFAULT NULL,
  `supervisor_empcode` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keys`
--

DROP TABLE IF EXISTS `keys`;
CREATE TABLE `keys` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `key` varchar(40) NOT NULL,
  `level` int NOT NULL,
  `ignore_limits` tinyint(1) NOT NULL DEFAULT '0',
  `is_private_key` tinyint(1) NOT NULL DEFAULT '0',
  `ip_addresses` text,
  `date_created` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `login_sessions`
--

DROP TABLE IF EXISTS `login_sessions`;
CREATE TABLE `login_sessions` (
  `session_id` int NOT NULL,
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `user_name` text NOT NULL,
  `logged_in_time` varchar(50) DEFAULT NULL,
  `emp_code` varchar(50) DEFAULT NULL,
  `token` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE `logs` (
  `id` int NOT NULL,
  `uri` varchar(255) NOT NULL,
  `method` varchar(6) NOT NULL,
  `params` text,
  `api_key` varchar(40) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `time` int NOT NULL,
  `rtime` float DEFAULT NULL,
  `authorized` varchar(1) NOT NULL,
  `response_code` smallint DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

DROP TABLE IF EXISTS `managers`;
CREATE TABLE `managers` (
  `id` int NOT NULL,
  `bmid` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_on` date NOT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `status` char(1) COLLATE utf8mb4_general_ci NOT NULL,
  `mobile` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `morningtasks`
--

DROP TABLE IF EXISTS `morningtasks`;
CREATE TABLE `morningtasks` (
  `mid` int NOT NULL,
  `mt0100` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0101` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0102` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0200` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0201` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0202` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0300` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0301` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0302` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0400` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0401` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0402` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0500` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0501` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0502` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0600` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0601` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0602` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0700` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0701` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0702` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0800` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0801` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0802` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0900` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0901` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt0902` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1000` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1001` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `mt1002` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emp_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taskDate` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modifiedDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mri`
--

DROP TABLE IF EXISTS `mri`;
CREATE TABLE `mri` (
  `id` int NOT NULL,
  `nid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_count` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `bmid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_emp_master`
--

DROP TABLE IF EXISTS `new_emp_master`;
CREATE TABLE `new_emp_master` (
  `id` int NOT NULL,
  `emp_code` int DEFAULT NULL,
  `fname` varchar(150) DEFAULT NULL,
  `lname` varchar(100) DEFAULT NULL,
  `mname` varchar(100) DEFAULT NULL,
  `comp_name` varchar(100) DEFAULT NULL,
  `doj` date DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` char(1) DEFAULT NULL,
  `mail_id` varchar(100) DEFAULT NULL,
  `report_mngr` varchar(15) DEFAULT NULL,
  `function_mngr` varchar(15) DEFAULT NULL,
  `ou_name` varchar(100) DEFAULT NULL,
  `dept_name` varchar(150) DEFAULT NULL,
  `location_name` varchar(255) DEFAULT NULL,
  `designation_name` varchar(255) DEFAULT NULL,
  `grade` varchar(5) DEFAULT NULL,
  `region` varchar(45) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `cost_center` varchar(45) DEFAULT NULL,
  `pay_group` varchar(45) DEFAULT NULL,
  `emp_status` varchar(45) DEFAULT NULL,
  `active` varchar(45) DEFAULT NULL,
  `disabled` char(1) NOT NULL DEFAULT 'N',
  `effective_from` date DEFAULT NULL,
  `created_on` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `modified_on` datetime DEFAULT NULL,
  `modified_by` varchar(15) DEFAULT NULL,
  `mobile` varchar(45) DEFAULT NULL,
  `depend1` varchar(45) DEFAULT NULL,
  `depend2` varchar(45) DEFAULT NULL,
  `depend3` varchar(45) DEFAULT NULL,
  `depend4` varchar(45) DEFAULT NULL,
  `depend5` varchar(45) DEFAULT NULL,
  `depend6` varchar(45) DEFAULT NULL,
  `exit_date` date DEFAULT NULL,
  `password` varchar(255) NOT NULL DEFAULT '''897b43f564b14b7ff2c93da2c6c4b583''',
  `validity` date DEFAULT NULL,
  `is_admin` char(1) NOT NULL DEFAULT 'N',
  `is_super_admin` char(1) NOT NULL DEFAULT 'N',
  `is_manager_approval` char(1) NOT NULL DEFAULT 'N',
  `is_traveldesk` char(1) NOT NULL DEFAULT 'N',
  `is_hotelinfo` char(1) NOT NULL DEFAULT 'N',
  `is_audit_approval` char(1) NOT NULL DEFAULT 'N',
  `is_finance_approval` char(1) NOT NULL DEFAULT 'N',
  `is_travelmanager_approved` char(1) NOT NULL DEFAULT 'N',
  `is_hotelmanager_approved` char(1) NOT NULL DEFAULT 'N',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `failed_attempts` int DEFAULT '0',
  `bank_name` varchar(50) NOT NULL,
  `bank_acnum` varchar(14) NOT NULL,
  `ifsc_code` varchar(14) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `nighttasks`
--

DROP TABLE IF EXISTS `nighttasks`;
CREATE TABLE `nighttasks` (
  `nid` int NOT NULL,
  `nt0100` text COLLATE utf8mb4_general_ci,
  `nt0101` text COLLATE utf8mb4_general_ci,
  `nt0102` text COLLATE utf8mb4_general_ci,
  `nt0200` text COLLATE utf8mb4_general_ci,
  `nt0201` text COLLATE utf8mb4_general_ci,
  `nt0300` text COLLATE utf8mb4_general_ci,
  `nt0301` text COLLATE utf8mb4_general_ci,
  `nt0400` text COLLATE utf8mb4_general_ci,
  `nt0401` text COLLATE utf8mb4_general_ci,
  `nt0500` text COLLATE utf8mb4_general_ci,
  `nt0501` text COLLATE utf8mb4_general_ci,
  `nt0600` text COLLATE utf8mb4_general_ci,
  `nt0601` text COLLATE utf8mb4_general_ci,
  `nt0700` text COLLATE utf8mb4_general_ci,
  `nt0701` text COLLATE utf8mb4_general_ci,
  `nt0800` text COLLATE utf8mb4_general_ci,
  `nt0801` text COLLATE utf8mb4_general_ci,
  `nt0900` text COLLATE utf8mb4_general_ci,
  `nt0901` text COLLATE utf8mb4_general_ci,
  `nt1000` text COLLATE utf8mb4_general_ci,
  `nt1001` text COLLATE utf8mb4_general_ci,
  `nt1100` text COLLATE utf8mb4_general_ci,
  `nt1101` text COLLATE utf8mb4_general_ci,
  `nt1200` text COLLATE utf8mb4_general_ci,
  `nt1201` text COLLATE utf8mb4_general_ci,
  `nt1300` text COLLATE utf8mb4_general_ci,
  `nt1301` text COLLATE utf8mb4_general_ci,
  `nt1400` text COLLATE utf8mb4_general_ci,
  `nt1401` text COLLATE utf8mb4_general_ci,
  `nt1500` text COLLATE utf8mb4_general_ci,
  `nt1501` text COLLATE utf8mb4_general_ci,
  `nt1502` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nt1600` text COLLATE utf8mb4_general_ci,
  `nt1602` text COLLATE utf8mb4_general_ci,
  `nt1601` text COLLATE utf8mb4_general_ci,
  `nt1700` text COLLATE utf8mb4_general_ci,
  `nt1701` text COLLATE utf8mb4_general_ci,
  `nt1800` text COLLATE utf8mb4_general_ci,
  `nt1801` text COLLATE utf8mb4_general_ci,
  `nt1900` text COLLATE utf8mb4_general_ci,
  `nt1901` text COLLATE utf8mb4_general_ci,
  `nt2000` text COLLATE utf8mb4_general_ci,
  `nt2001` text COLLATE utf8mb4_general_ci,
  `nt2100` text COLLATE utf8mb4_general_ci,
  `nt2101` text COLLATE utf8mb4_general_ci,
  `nt2200` text COLLATE utf8mb4_general_ci,
  `nt2201` text COLLATE utf8mb4_general_ci,
  `nt2300` text COLLATE utf8mb4_general_ci,
  `nt2301` text COLLATE utf8mb4_general_ci,
  `nt2400` text COLLATE utf8mb4_general_ci,
  `nt2401` text COLLATE utf8mb4_general_ci,
  `nt2500` text COLLATE utf8mb4_general_ci,
  `nt2501` text COLLATE utf8mb4_general_ci,
  `nt2600` text COLLATE utf8mb4_general_ci,
  `nt2601` text COLLATE utf8mb4_general_ci,
  `nt2700` text COLLATE utf8mb4_general_ci,
  `nt2701` text COLLATE utf8mb4_general_ci,
  `nt2800` text COLLATE utf8mb4_general_ci,
  `nt2801` text COLLATE utf8mb4_general_ci,
  `nt2900` text COLLATE utf8mb4_general_ci,
  `nt2901` text COLLATE utf8mb4_general_ci,
  `nt3000` text COLLATE utf8mb4_general_ci,
  `nt3001` text COLLATE utf8mb4_general_ci,
  `nt3100` text COLLATE utf8mb4_general_ci,
  `nt3101` text COLLATE utf8mb4_general_ci,
  `nt3200` text COLLATE utf8mb4_general_ci,
  `nt3201` text COLLATE utf8mb4_general_ci,
  `nt3300` text COLLATE utf8mb4_general_ci,
  `nt3301` text COLLATE utf8mb4_general_ci,
  `nt3400` text COLLATE utf8mb4_general_ci,
  `nt3401` text COLLATE utf8mb4_general_ci,
  `nt3500` text COLLATE utf8mb4_general_ci,
  `nt3501` text COLLATE utf8mb4_general_ci,
  `nt3600` text COLLATE utf8mb4_general_ci,
  `nt3601` text COLLATE utf8mb4_general_ci,
  `nt3700` text COLLATE utf8mb4_general_ci,
  `nt3701` text COLLATE utf8mb4_general_ci,
  `nt3800` text COLLATE utf8mb4_general_ci,
  `nt3801` text COLLATE utf8mb4_general_ci,
  `nt3900` text COLLATE utf8mb4_general_ci,
  `nt3901` text COLLATE utf8mb4_general_ci,
  `nt4000` text COLLATE utf8mb4_general_ci,
  `nt4001` text COLLATE utf8mb4_general_ci,
  `nt4100` text COLLATE utf8mb4_general_ci,
  `nt4101` text COLLATE utf8mb4_general_ci,
  `emp_code` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branch` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `taskDate` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `modifiedDTM` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `power_consumption`
--

DROP TABLE IF EXISTS `power_consumption`;
CREATE TABLE `power_consumption` (
  `id` int NOT NULL,
  `branch_id` int NOT NULL,
  `cluster_id` int DEFAULT NULL,
  `zone_id` int DEFAULT NULL,
  `morning_units` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `consumption_date` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `night_units` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_consumption` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nonbusinesshours` int NOT NULL,
  `remarks` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdBy` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `power_consumption_logs`
--

DROP TABLE IF EXISTS `power_consumption_logs`;
CREATE TABLE `power_consumption_logs` (
  `id` int NOT NULL,
  `branch_id` int NOT NULL,
  `cluster_id` int DEFAULT NULL,
  `zone_id` int DEFAULT NULL,
  `morning_units` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `consumption_date` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `night_units` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_consumption` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nonbusinesshours` int NOT NULL,
  `remarks` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdBy` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regd_remarks`
--

DROP TABLE IF EXISTS `regd_remarks`;
CREATE TABLE `regd_remarks` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `s_id` int NOT NULL,
  `regd_no` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_remarks` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdBy` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `createdDTM` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `repeat_punctures`
--

DROP TABLE IF EXISTS `repeat_punctures`;
CREATE TABLE `repeat_punctures` (
  `id` int NOT NULL,
  `report_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_number` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_remarks` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `task_id` int NOT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `repeat_samples`
--

DROP TABLE IF EXISTS `repeat_samples`;
CREATE TABLE `repeat_samples` (
  `id` int NOT NULL,
  `report_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_number` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_remarks` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `task_id` int NOT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `repeat_scan`
--

DROP TABLE IF EXISTS `repeat_scan`;
CREATE TABLE `repeat_scan` (
  `id` int NOT NULL,
  `report_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_number` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_remarks` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `task_id` int NOT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_amendments`
--

DROP TABLE IF EXISTS `report_amendments`;
CREATE TABLE `report_amendments` (
  `id` int NOT NULL,
  `report_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_number` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_remarks` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `task_id` int NOT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_escalations`
--

DROP TABLE IF EXISTS `report_escalations`;
CREATE TABLE `report_escalations` (
  `id` int NOT NULL,
  `report_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_number` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_remarks` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `task_id` int NOT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `role_id` int NOT NULL,
  `role` varchar(50) COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sample_rejections`
--

DROP TABLE IF EXISTS `sample_rejections`;
CREATE TABLE `sample_rejections` (
  `id` int NOT NULL,
  `report_type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_number` varchar(11) COLLATE utf8mb4_general_ci NOT NULL,
  `regd_remarks` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `task_id` int NOT NULL,
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `sid` int NOT NULL,
  `service_date` date DEFAULT NULL,
  `service_type` varchar(300) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `visiter_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `visiter_mobile` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_general_ci,
  `branch_id` int DEFAULT NULL,
  `vendor_id` int NOT NULL,
  `createdDTM` datetime DEFAULT NULL,
  `createdBy` int NOT NULL,
  `updatedDTM` datetime DEFAULT NULL,
  `updatedBy` int DEFAULT NULL,
  `status` enum('A','I') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_manager`
--

DROP TABLE IF EXISTS `service_manager`;
CREATE TABLE `service_manager` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `number` varchar(100) NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_on` date NOT NULL,
  `status` varchar(20) DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `subquestions`
--

DROP TABLE IF EXISTS `subquestions`;
CREATE TABLE `subquestions` (
  `id` int NOT NULL,
  `task_id` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `sq_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `sq` enum('NO','YES') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'NO',
  `squestion` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `sqvalue` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `createdDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updatedDTM` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `updatedBy` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_map`
--

DROP TABLE IF EXISTS `user_map`;
CREATE TABLE `user_map` (
  `id` int NOT NULL,
  `emp_code` int NOT NULL,
  `zone` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `cluster` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` varchar(25) COLLATE utf8mb4_general_ci NOT NULL,
  `branches` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` varchar(25) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usg`
--

DROP TABLE IF EXISTS `usg`;
CREATE TABLE `usg` (
  `id` int NOT NULL,
  `nid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_count` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `bmid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor`
--

DROP TABLE IF EXISTS `vendor`;
CREATE TABLE `vendor` (
  `vendor_id` int NOT NULL,
  `vendor_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vendor_address` varchar(250) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vendor_email` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vendor_mobile` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `vendor_gst` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `branches` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `service_type` enum('Pest_Control_Service','Elevation_Cleaning_Service','Water_Tank_Cleaning_Service','') COLLATE utf8mb4_general_ci NOT NULL,
  `terms` text COLLATE utf8mb4_general_ci NOT NULL,
  `createdDTM` datetime DEFAULT NULL,
  `createdBy` int DEFAULT NULL,
  `updatedDTM` datetime DEFAULT NULL,
  `updatedBy` int DEFAULT NULL,
  `status` enum('A','I') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visit_master`
--

DROP TABLE IF EXISTS `visit_master`;
CREATE TABLE `visit_master` (
  `visit_id` int NOT NULL,
  `visit_recurring` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `visit_day` int DEFAULT NULL,
  `branch_id` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `vendor_id` int NOT NULL,
  `createdDTM` datetime DEFAULT NULL,
  `createdBy` int NOT NULL,
  `updatedDTM` datetime DEFAULT NULL,
  `updatedBy` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watsapp_msg_user`
--

DROP TABLE IF EXISTS `watsapp_msg_user`;
CREATE TABLE `watsapp_msg_user` (
  `id` int NOT NULL,
  `type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watsapp_msg_user_list`
--

DROP TABLE IF EXISTS `watsapp_msg_user_list`;
CREATE TABLE `watsapp_msg_user_list` (
  `id` int NOT NULL,
  `type_id` int NOT NULL,
  `user` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_messages_log`
--

DROP TABLE IF EXISTS `whatsapp_messages_log`;
CREATE TABLE `whatsapp_messages_log` (
  `id` int NOT NULL,
  `mobile` varchar(15) COLLATE utf8mb4_general_ci NOT NULL,
  `emp_code` int NOT NULL,
  `message` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `sent_dtm` datetime NOT NULL,
  `hkr_id` int DEFAULT NULL,
  `remark` text COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `xray`
--

DROP TABLE IF EXISTS `xray`;
CREATE TABLE `xray` (
  `id` int NOT NULL,
  `nid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `doctor_count` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `bmid` varchar(100) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

DROP TABLE IF EXISTS `zones`;
CREATE TABLE `zones` (
  `z_id` int NOT NULL,
  `zone` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `clusters` text COLLATE utf8mb4_general_ci NOT NULL,
  `branches` text COLLATE utf8mb4_general_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `area`
--
ALTER TABLE `area`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bi_centres`
--
ALTER TABLE `bi_centres`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bmcm`
--
ALTER TABLE `bmcm`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bm_tasks`
--
ALTER TABLE `bm_tasks`
  ADD PRIMARY KEY (`mid`),
  ADD UNIQUE KEY `unique_branch_taskDate` (`branch`,`taskDate`);

--
-- Indexes for table `bm_tasks_logs`
--
ALTER TABLE `bm_tasks_logs`
  ADD PRIMARY KEY (`mid`);

--
-- Indexes for table `bm_weekly_list`
--
ALTER TABLE `bm_weekly_list`
  ADD PRIMARY KEY (`bmw_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `branchesmapped`
--
ALTER TABLE `branchesmapped`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branding_actions`
--
ALTER TABLE `branding_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`);

--
-- Indexes for table `branding_checklists`
--
ALTER TABLE `branding_checklists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id` (`id`);

--
-- Indexes for table `branding_checklist_items`
--
ALTER TABLE `branding_checklist_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`);

--
-- Indexes for table `branding_checklist_records`
--
ALTER TABLE `branding_checklist_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branding_photos`
--
ALTER TABLE `branding_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checklist_id` (`checklist_id`);

--
-- Indexes for table `branding_sections`
--
ALTER TABLE `branding_sections`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `branding_sub_sections`
--
ALTER TABLE `branding_sub_sections`
  ADD PRIMARY KEY (`sub_section_id`);

--
-- Indexes for table `cardiologist_ecg`
--
ALTER TABLE `cardiologist_ecg`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cardiologist_tmt`
--
ALTER TABLE `cardiologist_tmt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ci_sessions`
--
ALTER TABLE `ci_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ci_sessions_timestamp` (`timestamp`);

--
-- Indexes for table `cluster`
--
ALTER TABLE `cluster`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clusters`
--
ALTER TABLE `clusters`
  ADD PRIMARY KEY (`cluster_id`);

--
-- Indexes for table `cluster_branch_map`
--
ALTER TABLE `cluster_branch_map`
  ADD PRIMARY KEY (`cb_id`);

--
-- Indexes for table `cluster_zone_map`
--
ALTER TABLE `cluster_zone_map`
  ADD PRIMARY KEY (`cz_id`);

--
-- Indexes for table `clust_area_map`
--
ALTER TABLE `clust_area_map`
  ADD PRIMARY KEY (`cl_ar_id`);

--
-- Indexes for table `cm_morning_tasks`
--
ALTER TABLE `cm_morning_tasks`
  ADD PRIMARY KEY (`mid`);

--
-- Indexes for table `cm_night_tasks`
--
ALTER TABLE `cm_night_tasks`
  ADD PRIMARY KEY (`cm_nid`);

--
-- Indexes for table `ct`
--
ALTER TABLE `ct`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `diesel_consumption`
--
ALTER TABLE `diesel_consumption`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `diesel_consumption_logs`
--
ALTER TABLE `diesel_consumption_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `doc_count`
--
ALTER TABLE `doc_count`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `empareamap`
--
ALTER TABLE `empareamap`
  ADD PRIMARY KEY (`ea_id`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`file_id`);

--
-- Indexes for table `hk_branchwise_budget`
--
ALTER TABLE `hk_branchwise_budget`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hk_branch_ideal_qty`
--
ALTER TABLE `hk_branch_ideal_qty`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_hbib_item` (`hk_item_id`);

--
-- Indexes for table `hk_consumptions`
--
ALTER TABLE `hk_consumptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_branch_item_cycle` (`branch_id`,`hk_item_id`,`cycle_no`);

--
-- Indexes for table `hk_details`
--
ALTER TABLE `hk_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_hk_details_hkr` (`hkr_id`),
  ADD KEY `idx_hk_details_hk_item_id` (`hk_item_id`);

--
-- Indexes for table `hk_items`
--
ALTER TABLE `hk_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_hk_items_name` (`name`),
  ADD KEY `idx_hk_items_item_type` (`item_type`);

--
-- Indexes for table `hk_materials`
--
ALTER TABLE `hk_materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hk_materials_remove`
--
ALTER TABLE `hk_materials_remove`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hk_monthly_indents`
--
ALTER TABLE `hk_monthly_indents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_branch_month` (`branch_id`,`month`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `hk_monthly_indent_items`
--
ALTER TABLE `hk_monthly_indent_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_indent` (`indent_id`),
  ADD KEY `idx_item` (`hk_item_id`);

--
-- Indexes for table `hk_notifications`
--
ALTER TABLE `hk_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hk_opening_stock`
--
ALTER TABLE `hk_opening_stock`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_hk_opening_branch_item` (`branch_id`,`hk_item_id`),
  ADD KEY `idx_opening_branch` (`branch_id`);

--
-- Indexes for table `hk_requirements`
--
ALTER TABLE `hk_requirements`
  ADD PRIMARY KEY (`hkr_id`),
  ADD UNIQUE KEY `uniq_branch_month` (`branch_id`,`for_month`),
  ADD KEY `idx_req_branch_month_status` (`branch_id`,`for_month`,`status`);

--
-- Indexes for table `hk_requirement_receipts`
--
ALTER TABLE `hk_requirement_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_hkr_item` (`hkr_id`,`hk_item_id`),
  ADD KEY `idx_hkr` (`hkr_id`),
  ADD KEY `idx_item` (`hk_item_id`);

--
-- Indexes for table `hk_stock_balances`
--
ALTER TABLE `hk_stock_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_balance_branch_item` (`branch_id`,`hk_item_id`);

--
-- Indexes for table `hk_stock_receipts`
--
ALTER TABLE `hk_stock_receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hk_stock_receipt_items`
--
ALTER TABLE `hk_stock_receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_receipt_items_receipt` (`receipt_id`);

--
-- Indexes for table `hk_stock_received`
--
ALTER TABLE `hk_stock_received`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_received_branch` (`branch_id`,`hk_item_id`),
  ADD KEY `idx_received_hkr` (`hkr_id`);

--
-- Indexes for table `hk_visits`
--
ALTER TABLE `hk_visits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_sessions`
--
ALTER TABLE `login_sessions`
  ADD PRIMARY KEY (`session_id`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bmid` (`bmid`);

--
-- Indexes for table `morningtasks`
--
ALTER TABLE `morningtasks`
  ADD PRIMARY KEY (`mid`);

--
-- Indexes for table `mri`
--
ALTER TABLE `mri`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `new_emp_master`
--
ALTER TABLE `new_emp_master`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nighttasks`
--
ALTER TABLE `nighttasks`
  ADD PRIMARY KEY (`nid`);

--
-- Indexes for table `power_consumption`
--
ALTER TABLE `power_consumption`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `power_consumption_logs`
--
ALTER TABLE `power_consumption_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `regd_remarks`
--
ALTER TABLE `regd_remarks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `repeat_punctures`
--
ALTER TABLE `repeat_punctures`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `repeat_samples`
--
ALTER TABLE `repeat_samples`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `repeat_scan`
--
ALTER TABLE `repeat_scan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report_amendments`
--
ALTER TABLE `report_amendments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report_escalations`
--
ALTER TABLE `report_escalations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `sample_rejections`
--
ALTER TABLE `sample_rejections`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`sid`);

--
-- Indexes for table `subquestions`
--
ALTER TABLE `subquestions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_map`
--
ALTER TABLE `user_map`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `emp_code` (`emp_code`);

--
-- Indexes for table `usg`
--
ALTER TABLE `usg`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor`
--
ALTER TABLE `vendor`
  ADD PRIMARY KEY (`vendor_id`);

--
-- Indexes for table `visit_master`
--
ALTER TABLE `visit_master`
  ADD PRIMARY KEY (`visit_id`);

--
-- Indexes for table `watsapp_msg_user`
--
ALTER TABLE `watsapp_msg_user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `watsapp_msg_user_list`
--
ALTER TABLE `watsapp_msg_user_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `whatsapp_messages_log`
--
ALTER TABLE `whatsapp_messages_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `xray`
--
ALTER TABLE `xray`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `zones`
--
ALTER TABLE `zones`
  ADD PRIMARY KEY (`z_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `area`
--
ALTER TABLE `area`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bi_centres`
--
ALTER TABLE `bi_centres`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bmcm`
--
ALTER TABLE `bmcm`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bm_tasks`
--
ALTER TABLE `bm_tasks`
  MODIFY `mid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bm_tasks_logs`
--
ALTER TABLE `bm_tasks_logs`
  MODIFY `mid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bm_weekly_list`
--
ALTER TABLE `bm_weekly_list`
  MODIFY `bmw_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `branch_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branchesmapped`
--
ALTER TABLE `branchesmapped`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_actions`
--
ALTER TABLE `branding_actions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_checklists`
--
ALTER TABLE `branding_checklists`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_checklist_items`
--
ALTER TABLE `branding_checklist_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_checklist_records`
--
ALTER TABLE `branding_checklist_records`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_photos`
--
ALTER TABLE `branding_photos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_sections`
--
ALTER TABLE `branding_sections`
  MODIFY `section_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_sub_sections`
--
ALTER TABLE `branding_sub_sections`
  MODIFY `sub_section_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cardiologist_ecg`
--
ALTER TABLE `cardiologist_ecg`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cardiologist_tmt`
--
ALTER TABLE `cardiologist_tmt`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cluster`
--
ALTER TABLE `cluster`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clusters`
--
ALTER TABLE `clusters`
  MODIFY `cluster_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cluster_branch_map`
--
ALTER TABLE `cluster_branch_map`
  MODIFY `cb_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cluster_zone_map`
--
ALTER TABLE `cluster_zone_map`
  MODIFY `cz_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clust_area_map`
--
ALTER TABLE `clust_area_map`
  MODIFY `cl_ar_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cm_morning_tasks`
--
ALTER TABLE `cm_morning_tasks`
  MODIFY `mid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cm_night_tasks`
--
ALTER TABLE `cm_night_tasks`
  MODIFY `cm_nid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ct`
--
ALTER TABLE `ct`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diesel_consumption`
--
ALTER TABLE `diesel_consumption`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diesel_consumption_logs`
--
ALTER TABLE `diesel_consumption_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doc_count`
--
ALTER TABLE `doc_count`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `empareamap`
--
ALTER TABLE `empareamap`
  MODIFY `ea_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `file_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_branchwise_budget`
--
ALTER TABLE `hk_branchwise_budget`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_branch_ideal_qty`
--
ALTER TABLE `hk_branch_ideal_qty`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_consumptions`
--
ALTER TABLE `hk_consumptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_details`
--
ALTER TABLE `hk_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_items`
--
ALTER TABLE `hk_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_materials`
--
ALTER TABLE `hk_materials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_materials_remove`
--
ALTER TABLE `hk_materials_remove`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_monthly_indents`
--
ALTER TABLE `hk_monthly_indents`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_monthly_indent_items`
--
ALTER TABLE `hk_monthly_indent_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_notifications`
--
ALTER TABLE `hk_notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_opening_stock`
--
ALTER TABLE `hk_opening_stock`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_requirements`
--
ALTER TABLE `hk_requirements`
  MODIFY `hkr_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_requirement_receipts`
--
ALTER TABLE `hk_requirement_receipts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_stock_balances`
--
ALTER TABLE `hk_stock_balances`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_stock_receipts`
--
ALTER TABLE `hk_stock_receipts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_stock_receipt_items`
--
ALTER TABLE `hk_stock_receipt_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_stock_received`
--
ALTER TABLE `hk_stock_received`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_visits`
--
ALTER TABLE `hk_visits`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_sessions`
--
ALTER TABLE `login_sessions`
  MODIFY `session_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `morningtasks`
--
ALTER TABLE `morningtasks`
  MODIFY `mid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mri`
--
ALTER TABLE `mri`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_emp_master`
--
ALTER TABLE `new_emp_master`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nighttasks`
--
ALTER TABLE `nighttasks`
  MODIFY `nid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `power_consumption`
--
ALTER TABLE `power_consumption`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `power_consumption_logs`
--
ALTER TABLE `power_consumption_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regd_remarks`
--
ALTER TABLE `regd_remarks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repeat_punctures`
--
ALTER TABLE `repeat_punctures`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repeat_samples`
--
ALTER TABLE `repeat_samples`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repeat_scan`
--
ALTER TABLE `repeat_scan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_amendments`
--
ALTER TABLE `report_amendments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_escalations`
--
ALTER TABLE `report_escalations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sample_rejections`
--
ALTER TABLE `sample_rejections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `sid` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subquestions`
--
ALTER TABLE `subquestions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_map`
--
ALTER TABLE `user_map`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usg`
--
ALTER TABLE `usg`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor`
--
ALTER TABLE `vendor`
  MODIFY `vendor_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visit_master`
--
ALTER TABLE `visit_master`
  MODIFY `visit_id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `watsapp_msg_user`
--
ALTER TABLE `watsapp_msg_user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `watsapp_msg_user_list`
--
ALTER TABLE `watsapp_msg_user_list`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whatsapp_messages_log`
--
ALTER TABLE `whatsapp_messages_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `xray`
--
ALTER TABLE `xray`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `z_id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `branding_actions`
--
ALTER TABLE `branding_actions`
  ADD CONSTRAINT `fk_baction_chk` FOREIGN KEY (`checklist_id`) REFERENCES `branding_checklists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branding_checklist_items`
--
ALTER TABLE `branding_checklist_items`
  ADD CONSTRAINT `fk_bchk_item_chk` FOREIGN KEY (`checklist_id`) REFERENCES `branding_checklists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branding_photos`
--
ALTER TABLE `branding_photos`
  ADD CONSTRAINT `fk_bphoto_chk` FOREIGN KEY (`checklist_id`) REFERENCES `branding_checklists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hk_branch_ideal_qty`
--
ALTER TABLE `hk_branch_ideal_qty`
  ADD CONSTRAINT `fk_hbib_item` FOREIGN KEY (`hk_item_id`) REFERENCES `hk_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hk_details`
--
ALTER TABLE `hk_details`
  ADD CONSTRAINT `fk_hk_details_hk_item_id` FOREIGN KEY (`hk_item_id`) REFERENCES `hk_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hk_details_item` FOREIGN KEY (`hk_item_id`) REFERENCES `hk_items` (`id`);

--
-- Constraints for table `hk_monthly_indent_items`
--
ALTER TABLE `hk_monthly_indent_items`
  ADD CONSTRAINT `fk_indent_items_indent` FOREIGN KEY (`indent_id`) REFERENCES `hk_monthly_indents` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hk_requirement_receipts`
--
ALTER TABLE `hk_requirement_receipts`
  ADD CONSTRAINT `fk_hkr_receipts_hkr` FOREIGN KEY (`hkr_id`) REFERENCES `hk_requirements` (`hkr_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `hk_stock_receipt_items`
--
ALTER TABLE `hk_stock_receipt_items`
  ADD CONSTRAINT `fk_receipt_items_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `hk_stock_receipts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `hk_stock_received`
--
ALTER TABLE `hk_stock_received`
  ADD CONSTRAINT `fk_stock_received_hkr` FOREIGN KEY (`hkr_id`) REFERENCES `hk_requirements` (`hkr_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
