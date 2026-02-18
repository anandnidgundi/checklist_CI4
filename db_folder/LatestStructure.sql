-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 07, 2026 at 09:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

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

CREATE TABLE `area` (
  `id` int(11) NOT NULL,
  `area` varchar(100) NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_on` date NOT NULL,
  `status` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `auth_tokens`
--

CREATE TABLE `auth_tokens` (
  `id` int(11) NOT NULL,
  `emp_code` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiry_date` varchar(50) NOT NULL,
  `created_at` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bi_centres`
--

CREATE TABLE `bi_centres` (
  `id` int(11) NOT NULL,
  `branch` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `status` varchar(1) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'A',
  `createdby` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `createdon` datetime NOT NULL,
  `cluster` int(11) NOT NULL,
  `manager` int(11) NOT NULL,
  `branding` text NOT NULL,
  `bio` varchar(100) NOT NULL,
  `mobile` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `blacklisted_tokens`
--

CREATE TABLE `blacklisted_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `token` varchar(355) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bmcm`
--

CREATE TABLE `bmcm` (
  `id` int(11) NOT NULL,
  `emp_code` varchar(50) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `isAdmin` varchar(20) DEFAULT 'NO',
  `createdDTM` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bmcm_2`
--

CREATE TABLE `bmcm_2` (
  `id` int(11) NOT NULL,
  `emp_code` varchar(50) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `isAdmin` varchar(20) DEFAULT 'NO',
  `createdDTM` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bm_tasks`
--

CREATE TABLE `bm_tasks` (
  `mid` int(11) NOT NULL,
  `mt0100` varchar(500) DEFAULT NULL,
  `mt0101` varchar(500) DEFAULT NULL,
  `mt0102` varchar(500) DEFAULT NULL,
  `mt0104` varchar(100) NOT NULL,
  `mt0105` varchar(100) DEFAULT NULL,
  `mt0103` varchar(100) NOT NULL,
  `mt0200` varchar(500) DEFAULT NULL,
  `mt0201` varchar(500) DEFAULT NULL,
  `mt0202` varchar(500) DEFAULT NULL,
  `mt0204` varchar(100) DEFAULT NULL,
  `mt0205` varchar(100) DEFAULT NULL,
  `mt0203` varchar(100) DEFAULT NULL,
  `mt0300` varchar(500) DEFAULT NULL,
  `mt0301` varchar(500) DEFAULT NULL,
  `mt0302` varchar(500) DEFAULT NULL,
  `mt0400` varchar(500) DEFAULT NULL,
  `mt0401` varchar(500) DEFAULT NULL,
  `mt0402` varchar(500) DEFAULT NULL,
  `mt0500` varchar(500) DEFAULT NULL,
  `mt0501` varchar(500) DEFAULT NULL,
  `mt0502` varchar(500) DEFAULT NULL,
  `mt0600` varchar(500) DEFAULT NULL,
  `mt0601` varchar(500) DEFAULT NULL,
  `mt0602` varchar(500) DEFAULT NULL,
  `mt0700` varchar(500) DEFAULT NULL,
  `mt0701` varchar(500) DEFAULT NULL,
  `mt0702` varchar(500) DEFAULT NULL,
  `mt0800` varchar(100) DEFAULT NULL,
  `mt0801` varchar(100) DEFAULT NULL,
  `mt0802` varchar(100) DEFAULT NULL,
  `mt0900` varchar(100) DEFAULT NULL,
  `mt0901` varchar(100) DEFAULT NULL,
  `mt0902` varchar(100) DEFAULT NULL,
  `mt0903` varchar(20) DEFAULT NULL,
  `mt1000` varchar(100) DEFAULT NULL,
  `mt1001` varchar(100) DEFAULT NULL,
  `mt1002` varchar(100) DEFAULT NULL,
  `mt1100` varchar(100) DEFAULT NULL,
  `mt1101` varchar(100) DEFAULT NULL,
  `mt1102` varchar(100) DEFAULT NULL,
  `mt1200` varchar(100) DEFAULT NULL,
  `mt1201` varchar(100) DEFAULT NULL,
  `mt1202` varchar(100) DEFAULT NULL,
  `emp_code` varchar(50) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `createdDTM` varchar(50) DEFAULT NULL,
  `taskDate` varchar(50) DEFAULT NULL,
  `status` char(1) NOT NULL DEFAULT 'N',
  `created_by` varchar(50) DEFAULT NULL,
  `modifiedDTM` varchar(200) DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bm_tasks_logs`
--

CREATE TABLE `bm_tasks_logs` (
  `mid` int(11) NOT NULL,
  `mt0100` varchar(500) DEFAULT NULL,
  `mt0101` varchar(500) DEFAULT NULL,
  `mt0102` varchar(500) DEFAULT NULL,
  `mt0104` varchar(100) NOT NULL,
  `mt0105` varchar(100) DEFAULT NULL,
  `mt0103` varchar(100) NOT NULL,
  `mt0200` varchar(500) DEFAULT NULL,
  `mt0201` varchar(500) DEFAULT NULL,
  `mt0202` varchar(500) DEFAULT NULL,
  `mt0204` varchar(100) DEFAULT NULL,
  `mt0205` varchar(100) DEFAULT NULL,
  `mt0203` varchar(100) DEFAULT NULL,
  `mt0300` varchar(500) DEFAULT NULL,
  `mt0301` varchar(500) DEFAULT NULL,
  `mt0302` varchar(500) DEFAULT NULL,
  `mt0400` varchar(500) DEFAULT NULL,
  `mt0401` varchar(500) DEFAULT NULL,
  `mt0402` varchar(500) DEFAULT NULL,
  `mt0500` varchar(500) DEFAULT NULL,
  `mt0501` varchar(500) DEFAULT NULL,
  `mt0502` varchar(500) DEFAULT NULL,
  `mt0600` varchar(500) DEFAULT NULL,
  `mt0601` varchar(500) DEFAULT NULL,
  `mt0602` varchar(500) DEFAULT NULL,
  `mt0700` varchar(500) DEFAULT NULL,
  `mt0701` varchar(500) DEFAULT NULL,
  `mt0702` varchar(500) DEFAULT NULL,
  `mt0800` varchar(100) DEFAULT NULL,
  `mt0801` varchar(100) DEFAULT NULL,
  `mt0802` varchar(100) DEFAULT NULL,
  `mt0900` varchar(100) DEFAULT NULL,
  `mt0901` varchar(100) DEFAULT NULL,
  `mt0902` varchar(100) DEFAULT NULL,
  `mt0903` varchar(20) DEFAULT NULL,
  `mt1000` varchar(100) DEFAULT NULL,
  `mt1001` varchar(100) DEFAULT NULL,
  `mt1002` varchar(100) DEFAULT NULL,
  `mt1100` varchar(100) DEFAULT NULL,
  `mt1101` varchar(100) DEFAULT NULL,
  `mt1102` varchar(100) DEFAULT NULL,
  `mt1200` varchar(100) DEFAULT NULL,
  `mt1201` varchar(100) DEFAULT NULL,
  `mt1202` varchar(100) DEFAULT NULL,
  `emp_code` varchar(50) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `createdDTM` datetime DEFAULT NULL,
  `taskDate` date DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `status` char(1) NOT NULL DEFAULT 'N',
  `updated_by` varchar(25) NOT NULL,
  `modifiedDTM` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bm_weekly_list`
--

CREATE TABLE `bm_weekly_list` (
  `bmw_id` int(11) NOT NULL,
  `branch_id` varchar(200) DEFAULT NULL,
  `cluster_id` varchar(200) DEFAULT NULL,
  `w_0100` varchar(200) DEFAULT NULL,
  `w_0101` varchar(200) DEFAULT NULL,
  `w_0102` varchar(200) DEFAULT NULL,
  `w_0200` varchar(200) DEFAULT NULL,
  `w_0201` varchar(200) DEFAULT NULL,
  `w_0202` varchar(200) DEFAULT NULL,
  `w_0300` varchar(200) DEFAULT NULL,
  `w_0301` varchar(200) DEFAULT NULL,
  `w_0302` varchar(200) DEFAULT NULL,
  `w_0400` varchar(200) DEFAULT NULL,
  `w_0401` varchar(200) DEFAULT NULL,
  `w_0402` varchar(200) DEFAULT NULL,
  `w_0500` varchar(200) DEFAULT NULL,
  `w_0501` varchar(200) DEFAULT NULL,
  `w_0502` varchar(200) DEFAULT NULL,
  `w_0600` varchar(200) DEFAULT NULL,
  `w_0601` varchar(200) DEFAULT NULL,
  `w_0602` varchar(200) DEFAULT NULL,
  `w_0700` varchar(200) DEFAULT NULL,
  `w_0701` varchar(200) DEFAULT NULL,
  `w_0702` varchar(200) DEFAULT NULL,
  `w_0800` varchar(200) DEFAULT NULL,
  `w_0801` varchar(200) DEFAULT NULL,
  `w_0802` varchar(200) DEFAULT NULL,
  `w_0900` varchar(200) DEFAULT NULL,
  `w_0901` varchar(200) DEFAULT NULL,
  `w_0902` varchar(200) DEFAULT NULL,
  `w_1000` varchar(200) DEFAULT NULL,
  `w_1001` varchar(200) DEFAULT NULL,
  `w_1002` varchar(200) DEFAULT NULL,
  `w_1100` varchar(200) DEFAULT NULL,
  `w_1101` varchar(200) DEFAULT NULL,
  `w_1102` varchar(200) DEFAULT NULL,
  `w_1200` varchar(200) DEFAULT NULL,
  `w_1201` varchar(200) DEFAULT NULL,
  `w_1202` varchar(200) DEFAULT NULL,
  `w_1300` varchar(200) DEFAULT NULL,
  `w_1301` varchar(200) DEFAULT NULL,
  `w_1302` varchar(200) DEFAULT NULL,
  `w_1400` varchar(200) DEFAULT NULL,
  `w_1401` varchar(200) DEFAULT NULL,
  `w_1402` varchar(200) DEFAULT NULL,
  `w_1500` varchar(200) DEFAULT NULL,
  `w_1501` varchar(200) DEFAULT NULL,
  `w_1502` varchar(200) DEFAULT NULL,
  `w_1600` varchar(200) DEFAULT NULL,
  `w_1601` varchar(200) DEFAULT NULL,
  `w_1602` varchar(200) DEFAULT NULL,
  `w_1700` varchar(200) DEFAULT NULL,
  `w_1701` varchar(200) DEFAULT NULL,
  `w_1702` varchar(200) DEFAULT NULL,
  `createdBy` varchar(100) DEFAULT NULL,
  `createdDTM` varchar(100) DEFAULT NULL,
  `modifiedDTM` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branchbudgetdata`
--

CREATE TABLE `branchbudgetdata` (
  `S.NO` int(11) DEFAULT NULL,
  `Branch Name` varchar(512) DEFAULT NULL,
  `Cluster` varchar(512) DEFAULT NULL,
  `Budget` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branchesmapped`
--

CREATE TABLE `branchesmapped` (
  `id` int(11) NOT NULL,
  `emp_code` varchar(200) DEFAULT NULL,
  `area_id` varchar(200) DEFAULT NULL,
  `area` varchar(200) DEFAULT NULL,
  `branch_id` varchar(200) DEFAULT NULL,
  `branch` varchar(200) DEFAULT NULL,
  `cluster_id` varchar(50) DEFAULT NULL,
  `cluster` varchar(50) DEFAULT NULL,
  `zone_id` varchar(50) DEFAULT NULL,
  `zone` varchar(50) DEFAULT NULL,
  `createdDTM` varchar(200) DEFAULT NULL,
  `created_by` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches_old`
--

CREATE TABLE `branches_old` (
  `branch_id` int(11) NOT NULL,
  `branch` varchar(200) NOT NULL,
  `status` varchar(50) DEFAULT 'A',
  `created_by` varchar(200) DEFAULT NULL,
  `createdDTM` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_actions`
--

CREATE TABLE `branding_actions` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_id` int(10) UNSIGNED NOT NULL,
  `action_text` text NOT NULL,
  `priority` varchar(16) DEFAULT 'low',
  `target_date` date DEFAULT NULL,
  `assigned_to` varchar(64) DEFAULT NULL,
  `status` varchar(32) DEFAULT 'open',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_checklist_items`
--

CREATE TABLE `branding_checklist_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_id` int(10) UNSIGNED NOT NULL,
  `section` varchar(100) DEFAULT NULL,
  `item_label` varchar(255) DEFAULT NULL,
  `response` varchar(64) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `priority` tinyint(3) UNSIGNED DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_checklist_records`
--

CREATE TABLE `branding_checklist_records` (
  `id` int(11) NOT NULL,
  `branding_checklist_id` int(10) NOT NULL,
  `section_id` int(10) DEFAULT NULL,
  `sub_section_id` int(10) DEFAULT NULL,
  `input_name` varchar(200) NOT NULL,
  `input_value` varchar(200) NOT NULL,
  `input_remark` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_dtm` datetime DEFAULT NULL,
  `updated_by` int(11) DEFAULT NULL,
  `updated_dtm` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_photos`
--

CREATE TABLE `branding_photos` (
  `id` int(10) UNSIGNED NOT NULL,
  `checklist_id` int(10) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `accuracy` int(11) DEFAULT NULL,
  `geo_dtm` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_sections`
--

CREATE TABLE `branding_sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `dept_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branding_sub_sections`
--

CREATE TABLE `branding_sub_sections` (
  `sub_section_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `sub_section_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cardiologist_ecg`
--

CREATE TABLE `cardiologist_ecg` (
  `id` int(11) NOT NULL,
  `nid` varchar(100) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `doctor_count` varchar(100) NOT NULL,
  `bmid` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cardiologist_tmt`
--

CREATE TABLE `cardiologist_tmt` (
  `id` int(11) NOT NULL,
  `nid` varchar(100) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `doctor_count` varchar(100) NOT NULL,
  `bmid` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `centres`
--

CREATE TABLE `centres` (
  `centre_id` int(11) NOT NULL,
  `centre_name` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `phone` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `address` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `services` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `timing` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `manager` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `phone2` mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `phlebo` enum('no','yes') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'no',
  `runner` enum('no','yes') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'no'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
--

CREATE TABLE `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `data` blob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `cluster`
--

CREATE TABLE `cluster` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_on` date NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `status` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clusters`
--

CREATE TABLE `clusters` (
  `cluster_id` int(11) NOT NULL,
  `cluster` varchar(200) NOT NULL,
  `branches` text NOT NULL,
  `status` varchar(50) DEFAULT 'A',
  `created_by` varchar(200) DEFAULT NULL,
  `createdDTM` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cluster_branch_map`
--

CREATE TABLE `cluster_branch_map` (
  `cb_id` int(11) NOT NULL,
  `cluster_id` varchar(50) DEFAULT NULL,
  `branch_id` varchar(50) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `createdDTM` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cluster_zone_map`
--

CREATE TABLE `cluster_zone_map` (
  `cz_id` int(11) NOT NULL,
  `cluster_id` varchar(50) NOT NULL,
  `zone_id` varchar(50) NOT NULL,
  `createdDTM` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clust_area_map`
--

CREATE TABLE `clust_area_map` (
  `cl_ar_id` int(11) NOT NULL,
  `cluster_id` varchar(50) DEFAULT NULL,
  `area_id` varchar(20) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `createdDTM` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cm_morning_tasks`
--

CREATE TABLE `cm_morning_tasks` (
  `mid` int(11) NOT NULL,
  `mt0100` varchar(500) DEFAULT NULL,
  `mt0101` varchar(500) DEFAULT NULL,
  `mt0102` varchar(500) DEFAULT NULL,
  `mt0200` varchar(500) DEFAULT NULL,
  `mt0201` varchar(500) DEFAULT NULL,
  `mt0202` varchar(500) DEFAULT NULL,
  `mt0300` varchar(500) DEFAULT NULL,
  `mt0301` varchar(500) DEFAULT NULL,
  `mt0302` varchar(500) DEFAULT NULL,
  `mt0400` varchar(500) DEFAULT NULL,
  `mt0401` varchar(500) DEFAULT NULL,
  `mt0402` varchar(500) DEFAULT NULL,
  `mt0500` varchar(500) DEFAULT NULL,
  `mt0501` varchar(500) DEFAULT NULL,
  `mt0502` varchar(500) DEFAULT NULL,
  `mt0600` varchar(500) DEFAULT NULL,
  `mt0601` varchar(500) DEFAULT NULL,
  `mt0602` varchar(500) DEFAULT NULL,
  `mt0700` varchar(500) DEFAULT NULL,
  `mt0701` varchar(500) DEFAULT NULL,
  `mt0702` varchar(500) DEFAULT NULL,
  `mt0800` varchar(500) DEFAULT NULL,
  `mt0801` varchar(500) DEFAULT NULL,
  `mt0802` varchar(500) DEFAULT NULL,
  `mt0900` varchar(500) DEFAULT NULL,
  `mt0901` varchar(500) DEFAULT NULL,
  `mt0902` varchar(500) DEFAULT NULL,
  `mt1000` varchar(500) DEFAULT NULL,
  `mt1001` varchar(500) DEFAULT NULL,
  `mt1002` varchar(500) DEFAULT NULL,
  `emp_code` varchar(50) DEFAULT NULL,
  `cluster_id` varchar(50) DEFAULT NULL,
  `createdDTM` varchar(50) DEFAULT NULL,
  `taskDate` varchar(50) DEFAULT NULL,
  `modifiedDTM` varchar(50) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cm_night_tasks`
--

CREATE TABLE `cm_night_tasks` (
  `cm_nid` int(11) NOT NULL,
  `cm_nt0100` varchar(500) DEFAULT NULL,
  `cm_nt0101` varchar(500) DEFAULT NULL,
  `cm_nt0102` varchar(500) DEFAULT NULL,
  `cm_nt0200` varchar(500) DEFAULT NULL,
  `cm_nt0201` varchar(500) DEFAULT NULL,
  `cm_nt0202` varchar(500) DEFAULT NULL,
  `cm_nt0300` varchar(500) DEFAULT NULL,
  `cm_nt0301` varchar(500) DEFAULT NULL,
  `cm_nt0302` varchar(500) DEFAULT NULL,
  `cm_nt0400` varchar(500) DEFAULT NULL,
  `cm_nt0401` varchar(500) DEFAULT NULL,
  `cm_nt0402` varchar(500) DEFAULT NULL,
  `cm_nt0500` varchar(500) DEFAULT NULL,
  `cm_nt0501` varchar(500) DEFAULT NULL,
  `cm_nt0502` varchar(500) DEFAULT NULL,
  `cm_nt0600` varchar(500) DEFAULT NULL,
  `cm_nt0601` varchar(500) DEFAULT NULL,
  `cm_nt0602` varchar(500) DEFAULT NULL,
  `cm_nt0700` varchar(500) DEFAULT NULL,
  `cm_nt0701` varchar(500) DEFAULT NULL,
  `cm_nt0702` varchar(500) DEFAULT NULL,
  `cm_nt0800` varchar(500) DEFAULT NULL,
  `cm_nt0801` varchar(500) DEFAULT NULL,
  `cm_nt0802` varchar(500) DEFAULT NULL,
  `cm_nt0900` varchar(500) DEFAULT NULL,
  `cm_nt0901` varchar(500) DEFAULT NULL,
  `cm_nt0902` varchar(500) DEFAULT NULL,
  `cm_nt1000` varchar(500) DEFAULT NULL,
  `cm_nt1001` varchar(500) DEFAULT NULL,
  `cm_nt1002` varchar(500) DEFAULT NULL,
  `emp_code` varchar(50) DEFAULT NULL,
  `cluster_id` varchar(50) DEFAULT NULL,
  `createdDTM` varchar(50) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `taskDate` varchar(50) DEFAULT NULL,
  `modifiedDTM` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `comment_detail` varchar(500) DEFAULT NULL,
  `tour_id` varchar(20) DEFAULT NULL,
  `exp_id` varchar(50) DEFAULT NULL,
  `l_id` varchar(50) DEFAULT NULL,
  `commentBy` varchar(50) DEFAULT NULL,
  `createdDTM` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ct`
--

CREATE TABLE `ct` (
  `id` int(11) NOT NULL,
  `nid` varchar(100) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `doctor_count` varchar(100) NOT NULL,
  `bmid` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `id` int(11) NOT NULL,
  `dep_name` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `cat_id` int(11) NOT NULL,
  `dept_name` varchar(200) NOT NULL,
  `created_on` date NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `status` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diesel_consumption`
--

CREATE TABLE `diesel_consumption` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `cluster_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `consumption_date` varchar(20) NOT NULL,
  `is_power_shutdown` varchar(12) NOT NULL,
  `is_generator_testing` varchar(12) NOT NULL,
  `testing_time_duration` varchar(12) NOT NULL,
  `power_shutdown` varchar(20) DEFAULT NULL,
  `closing_stock_percentage` varchar(10) NOT NULL,
  `diesel_consumed` varchar(20) DEFAULT NULL,
  `testing_diesel_consumed` int(11) NOT NULL,
  `avg_consumption` varchar(20) DEFAULT NULL,
  `closing_stock` varchar(20) DEFAULT NULL,
  `remarks` varchar(200) DEFAULT NULL,
  `createdBy` varchar(20) DEFAULT NULL,
  `createdDTM` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `diesel_consumption_logs`
--

CREATE TABLE `diesel_consumption_logs` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `cluster_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `consumption_date` varchar(20) NOT NULL,
  `is_power_shutdown` varchar(12) NOT NULL,
  `is_generator_testing` varchar(12) NOT NULL,
  `testing_time_duration` varchar(12) NOT NULL,
  `testing_diesel_consumed` int(11) NOT NULL,
  `power_shutdown` varchar(20) DEFAULT NULL,
  `diesel_consumed` varchar(20) DEFAULT NULL,
  `avg_consumption` varchar(20) DEFAULT NULL,
  `closing_stock` varchar(20) DEFAULT NULL,
  `closing_stock_percentage` varchar(15) NOT NULL,
  `remarks` varchar(200) DEFAULT NULL,
  `createdBy` varchar(20) DEFAULT NULL,
  `createdDTM` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doc_count`
--

CREATE TABLE `doc_count` (
  `id` int(11) NOT NULL,
  `nid` varchar(50) NOT NULL,
  `doctor_name` varchar(200) DEFAULT NULL,
  `bmid` varchar(50) DEFAULT NULL,
  `mri` varchar(50) DEFAULT NULL,
  `ct` varchar(50) DEFAULT NULL,
  `xray` varchar(50) DEFAULT NULL,
  `cardio_ecg` varchar(50) DEFAULT NULL,
  `cardio_tmt` varchar(50) DEFAULT NULL,
  `createdDTM` varchar(50) DEFAULT NULL,
  `createdBy` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `empareamap`
--

CREATE TABLE `empareamap` (
  `ea_id` int(11) NOT NULL,
  `emp_code` varchar(50) DEFAULT NULL,
  `area_id` varchar(50) DEFAULT NULL,
  `createdDTM` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(11) NOT NULL,
  `em_code` varchar(13) DEFAULT NULL,
  `first_name` varchar(20) DEFAULT NULL,
  `middle_name` varchar(18) DEFAULT NULL,
  `last_name` varchar(20) DEFAULT NULL,
  `em_gender` varchar(6) DEFAULT NULL,
  `em_role` varchar(50) DEFAULT NULL,
  `em_email` varchar(41) DEFAULT NULL,
  `em_phone` bigint(20) DEFAULT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `dept_id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `created_on` date NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `status` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `file_id` int(11) NOT NULL,
  `file_name` varchar(200) DEFAULT NULL,
  `tour_id` varchar(50) DEFAULT NULL,
  `em_code` varchar(50) DEFAULT NULL,
  `mid` varchar(50) DEFAULT NULL,
  `nid` varchar(50) DEFAULT NULL,
  `cm_mid` varchar(50) DEFAULT NULL,
  `cm_nid` varchar(50) DEFAULT NULL,
  `service_id` varchar(50) NOT NULL,
  `bmw_id` varchar(50) DEFAULT NULL,
  `diesel_id` varchar(10) DEFAULT NULL,
  `power_id` varchar(10) DEFAULT NULL,
  `hkr_id` varchar(10) DEFAULT NULL,
  `createdDTM` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files_orphans`
--

CREATE TABLE `files_orphans` (
  `file_id` int(11) NOT NULL,
  `file_name` varchar(200) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `original_name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `tour_id` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `em_code` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `mid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `nid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `cm_mid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `cm_nid` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `service_id` int(11) NOT NULL,
  `bmw_id` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `diesel_id` varchar(10) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `power_id` varchar(10) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `hkr_id` int(11) DEFAULT NULL,
  `createdDTM` varchar(50) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form_inputs`
--

CREATE TABLE `form_inputs` (
  `id` int(11) NOT NULL,
  `input_name` varchar(50) NOT NULL,
  `input_label` varchar(150) DEFAULT NULL,
  `input_icon` varchar(50) DEFAULT NULL,
  `input_col` varchar(10) DEFAULT 'col-md-3',
  `input_type` varchar(50) NOT NULL,
  `input_value` text DEFAULT NULL,
  `data_source` varchar(100) DEFAULT NULL,
  `map_fields` text DEFAULT NULL,
  `input_placeholder` varchar(50) DEFAULT NULL,
  `show_when_field` varchar(150) DEFAULT NULL,
  `show_when_value` varchar(150) DEFAULT NULL,
  `show_operator` enum('=','!=','>','<') DEFAULT '=',
  `input_class` varchar(255) DEFAULT NULL,
  `input_required` tinyint(1) DEFAULT 0,
  `input_readonly` tinyint(1) DEFAULT 0,
  `input_disabled` tinyint(1) DEFAULT 0,
  `input_min` varchar(50) DEFAULT NULL,
  `input_max` varchar(50) DEFAULT NULL,
  `input_step` varchar(50) DEFAULT NULL,
  `input_pattern` varchar(255) DEFAULT NULL,
  `input_maxlength` int(11) DEFAULT NULL,
  `input_options` text DEFAULT NULL,
  `input_order` int(11) DEFAULT 0,
  `form_id` int(11) NOT NULL,
  `section_id` int(11) DEFAULT NULL,
  `sub_section_id` int(11) DEFAULT NULL,
  `status` char(1) NOT NULL DEFAULT 'A',
  `file_accept` varchar(100) DEFAULT NULL,
  `max_file_size` int(11) DEFAULT 2048,
  `compress_image` tinyint(1) DEFAULT 0,
  `required_when_field` varchar(150) DEFAULT NULL,
  `required_when_value` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form_records`
--

CREATE TABLE `form_records` (
  `id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `sub_section_id` int(11) NOT NULL,
  `input_id` int(11) NOT NULL,
  `input_value` text NOT NULL,
  `dept_id` int(11) NOT NULL,
  `created_dtm` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `form_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form_sections`
--

CREATE TABLE `form_sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `section_icon` varchar(50) DEFAULT NULL,
  `dept_id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `status` char(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `form_sub_sections`
--

CREATE TABLE `form_sub_sections` (
  `sub_section_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `sub_section_name` varchar(100) NOT NULL,
  `sub_section_icon` varchar(50) DEFAULT NULL,
  `status` char(1) NOT NULL DEFAULT 'A',
  `created_dtm` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_branchwise_budget`
--

CREATE TABLE `hk_branchwise_budget` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `cluster_id` int(11) NOT NULL,
  `budget` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_branch_ideal_qty`
--

CREATE TABLE `hk_branch_ideal_qty` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `hk_item_id` int(11) NOT NULL,
  `ideal_qty` decimal(12,2) DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_consumptions`
--

CREATE TABLE `hk_consumptions` (
  `id` int(11) NOT NULL,
  `hkv_visit_id` int(11) DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `hk_item_id` int(11) NOT NULL,
  `cycle_no` tinyint(4) NOT NULL,
  `cycle_from` date DEFAULT NULL,
  `cycle_to` date DEFAULT NULL,
  `consumed_qty` decimal(12,2) NOT NULL,
  `recorded_by` varchar(50) DEFAULT NULL,
  `recorded_at` datetime DEFAULT current_timestamp(),
  `locked` char(1) DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_details`
--

CREATE TABLE `hk_details` (
  `id` int(11) NOT NULL,
  `hkr_id` int(11) NOT NULL,
  `hk_material` varchar(100) NOT NULL,
  `hk_make` varchar(50) DEFAULT NULL,
  `hk_price` decimal(10,2) DEFAULT 0.00,
  `quantity` decimal(12,2) DEFAULT 0.00,
  `created_by` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT 0.00,
  `hk_item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_items`
--

CREATE TABLE `hk_items` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `unit` varchar(50) DEFAULT 'nos.',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `item_type` varchar(20) NOT NULL DEFAULT 'Consumables'
) ;

-- --------------------------------------------------------

--
-- Table structure for table `hk_materials_remove`
--

CREATE TABLE `hk_materials_remove` (
  `id` int(11) NOT NULL,
  `hk_material_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_monthly_indents`
--

CREATE TABLE `hk_monthly_indents` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(11) NOT NULL,
  `month` char(7) NOT NULL COMMENT 'YYYY-MM',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `requested_by` varchar(64) DEFAULT NULL,
  `approved_by` varchar(64) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `total_amount` decimal(16,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  `rejection_remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_monthly_indent_items`
--

CREATE TABLE `hk_monthly_indent_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `indent_id` int(10) UNSIGNED NOT NULL,
  `hk_item_id` int(10) UNSIGNED NOT NULL,
  `qty_requested` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `brand` varchar(255) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `qty_approved` decimal(10,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `qty_received` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_notifications`
--

CREATE TABLE `hk_notifications` (
  `id` int(11) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `status` varchar(30) DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_opening_stock`
--

CREATE TABLE `hk_opening_stock` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `hk_item_id` int(11) NOT NULL,
  `opening_qty` decimal(12,2) DEFAULT 0.00,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_requirements`
--

CREATE TABLE `hk_requirements` (
  `hkr_id` int(11) NOT NULL,
  `for_month` varchar(20) NOT NULL,
  `created_by` int(20) NOT NULL,
  `remarks` text DEFAULT NULL,
  `admin_remarks` text DEFAULT NULL,
  `status` enum('PENDING','APPROVED','REJECTED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `created_dtm` datetime NOT NULL,
  `approved_by` int(20) DEFAULT NULL,
  `approved_dtm` datetime DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `cluster_id` int(11) NOT NULL,
  `isDeleted` char(1) NOT NULL DEFAULT 'N',
  `applied_amount` decimal(10,2) DEFAULT NULL,
  `budget_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_requirement_receipts`
--

CREATE TABLE `hk_requirement_receipts` (
  `id` int(11) NOT NULL,
  `hkr_id` int(11) NOT NULL,
  `hk_item_id` int(11) NOT NULL,
  `expected_qty` decimal(12,3) NOT NULL DEFAULT 0.000,
  `received_qty` decimal(12,3) NOT NULL DEFAULT 0.000,
  `status` enum('pending','partial','received') NOT NULL DEFAULT 'pending',
  `latest_receipt_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_stock_balances`
--

CREATE TABLE `hk_stock_balances` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `hk_item_id` int(11) NOT NULL,
  `opening_qty` decimal(12,2) DEFAULT 0.00,
  `total_received` decimal(12,2) DEFAULT 0.00,
  `total_consumed` decimal(12,2) DEFAULT 0.00,
  `current_balance` decimal(12,2) DEFAULT 0.00,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_stock_receipts`
--

CREATE TABLE `hk_stock_receipts` (
  `id` int(10) UNSIGNED NOT NULL,
  `branch_id` int(11) NOT NULL,
  `indent_id` int(11) DEFAULT NULL,
  `invoice_no` varchar(128) DEFAULT NULL,
  `received_by` varchar(64) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_stock_receipt_items`
--

CREATE TABLE `hk_stock_receipt_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `receipt_id` int(10) UNSIGNED NOT NULL,
  `hk_item_id` int(11) NOT NULL,
  `received_qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `indent_item_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_stock_received`
--

CREATE TABLE `hk_stock_received` (
  `id` int(11) NOT NULL,
  `hkr_id` int(11) DEFAULT NULL,
  `branch_id` int(11) NOT NULL,
  `hk_item_id` int(11) NOT NULL,
  `received_qty` decimal(12,2) NOT NULL,
  `received_dt` datetime DEFAULT current_timestamp(),
  `source` varchar(100) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hk_visits`
--

CREATE TABLE `hk_visits` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `visit_dt` datetime DEFAULT current_timestamp(),
  `cycle_no` tinyint(4) DEFAULT NULL,
  `supervisor_empcode` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `keys`
--

CREATE TABLE `keys` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `key` varchar(40) NOT NULL,
  `level` int(11) NOT NULL,
  `ignore_limits` tinyint(1) NOT NULL DEFAULT 0,
  `is_private_key` tinyint(1) NOT NULL DEFAULT 0,
  `ip_addresses` text DEFAULT NULL,
  `date_created` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_sessions`
--

CREATE TABLE `login_sessions` (
  `session_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `user_name` text NOT NULL,
  `logged_in_time` varchar(50) DEFAULT NULL,
  `emp_code` varchar(50) DEFAULT NULL,
  `token` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `uri` varchar(255) NOT NULL,
  `method` varchar(6) NOT NULL,
  `params` text DEFAULT NULL,
  `api_key` varchar(40) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `time` int(11) NOT NULL,
  `rtime` float DEFAULT NULL,
  `authorized` varchar(1) NOT NULL,
  `response_code` smallint(6) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `id` int(11) NOT NULL,
  `bmid` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_on` date NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `status` char(1) NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `morningtasks`
--

CREATE TABLE `morningtasks` (
  `mid` int(11) NOT NULL,
  `mt0100` varchar(500) DEFAULT NULL,
  `mt0101` varchar(500) DEFAULT NULL,
  `mt0102` varchar(500) DEFAULT NULL,
  `mt0200` varchar(500) DEFAULT NULL,
  `mt0201` varchar(500) DEFAULT NULL,
  `mt0202` varchar(500) DEFAULT NULL,
  `mt0300` varchar(500) DEFAULT NULL,
  `mt0301` varchar(500) DEFAULT NULL,
  `mt0302` varchar(500) DEFAULT NULL,
  `mt0400` varchar(500) DEFAULT NULL,
  `mt0401` varchar(500) DEFAULT NULL,
  `mt0402` varchar(500) DEFAULT NULL,
  `mt0500` varchar(500) DEFAULT NULL,
  `mt0501` varchar(500) DEFAULT NULL,
  `mt0502` varchar(500) DEFAULT NULL,
  `mt0600` varchar(500) DEFAULT NULL,
  `mt0601` varchar(500) DEFAULT NULL,
  `mt0602` varchar(500) DEFAULT NULL,
  `mt0700` varchar(500) DEFAULT NULL,
  `mt0701` varchar(500) DEFAULT NULL,
  `mt0702` varchar(500) DEFAULT NULL,
  `mt0800` varchar(500) DEFAULT NULL,
  `mt0801` varchar(500) DEFAULT NULL,
  `mt0802` varchar(500) DEFAULT NULL,
  `mt0900` varchar(500) DEFAULT NULL,
  `mt0901` varchar(500) DEFAULT NULL,
  `mt0902` varchar(500) DEFAULT NULL,
  `mt1000` varchar(500) DEFAULT NULL,
  `mt1001` varchar(500) DEFAULT NULL,
  `mt1002` varchar(500) DEFAULT NULL,
  `emp_code` varchar(50) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `createdDTM` varchar(50) DEFAULT NULL,
  `taskDate` varchar(50) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `modifiedDTM` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mri`
--

CREATE TABLE `mri` (
  `id` int(11) NOT NULL,
  `nid` varchar(100) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `doctor_count` varchar(100) NOT NULL,
  `bmid` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_department`
--

CREATE TABLE `new_department` (
  `id` int(11) NOT NULL,
  `dept_name` varchar(50) NOT NULL,
  `status` char(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `new_emp_master_2`
--

CREATE TABLE `new_emp_master_2` (
  `id` int(11) NOT NULL,
  `emp_code` int(11) DEFAULT NULL,
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
  `created_by` int(11) DEFAULT NULL,
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
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `failed_attempts` int(11) DEFAULT 0,
  `bank_name` varchar(50) NOT NULL,
  `bank_acnum` varchar(14) NOT NULL,
  `ifsc_code` varchar(14) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `nighttasks`
--

CREATE TABLE `nighttasks` (
  `nid` int(11) NOT NULL,
  `nt0100` text DEFAULT NULL,
  `nt0101` text DEFAULT NULL,
  `nt0102` text DEFAULT NULL,
  `nt0200` text DEFAULT NULL,
  `nt0201` text DEFAULT NULL,
  `nt0300` text DEFAULT NULL,
  `nt0301` text DEFAULT NULL,
  `nt0400` text DEFAULT NULL,
  `nt0401` text DEFAULT NULL,
  `nt0500` text DEFAULT NULL,
  `nt0501` text DEFAULT NULL,
  `nt0600` text DEFAULT NULL,
  `nt0601` text DEFAULT NULL,
  `nt0700` text DEFAULT NULL,
  `nt0701` text DEFAULT NULL,
  `nt0800` text DEFAULT NULL,
  `nt0801` text DEFAULT NULL,
  `nt0900` text DEFAULT NULL,
  `nt0901` text DEFAULT NULL,
  `nt1000` text DEFAULT NULL,
  `nt1001` text DEFAULT NULL,
  `nt1100` text DEFAULT NULL,
  `nt1101` text DEFAULT NULL,
  `nt1200` text DEFAULT NULL,
  `nt1201` text DEFAULT NULL,
  `nt1300` text DEFAULT NULL,
  `nt1301` text DEFAULT NULL,
  `nt1400` text DEFAULT NULL,
  `nt1401` text DEFAULT NULL,
  `nt1500` text DEFAULT NULL,
  `nt1501` text DEFAULT NULL,
  `nt1502` varchar(500) DEFAULT NULL,
  `nt1600` text DEFAULT NULL,
  `nt1602` text DEFAULT NULL,
  `nt1601` text DEFAULT NULL,
  `nt1700` text DEFAULT NULL,
  `nt1701` text DEFAULT NULL,
  `nt1800` text DEFAULT NULL,
  `nt1801` text DEFAULT NULL,
  `nt1900` text DEFAULT NULL,
  `nt1901` text DEFAULT NULL,
  `nt2000` text DEFAULT NULL,
  `nt2001` text DEFAULT NULL,
  `nt2100` text DEFAULT NULL,
  `nt2101` text DEFAULT NULL,
  `nt2200` text DEFAULT NULL,
  `nt2201` text DEFAULT NULL,
  `nt2300` text DEFAULT NULL,
  `nt2301` text DEFAULT NULL,
  `nt2400` text DEFAULT NULL,
  `nt2401` text DEFAULT NULL,
  `nt2500` text DEFAULT NULL,
  `nt2501` text DEFAULT NULL,
  `nt2600` text DEFAULT NULL,
  `nt2601` text DEFAULT NULL,
  `nt2700` text DEFAULT NULL,
  `nt2701` text DEFAULT NULL,
  `nt2800` text DEFAULT NULL,
  `nt2801` text DEFAULT NULL,
  `nt2900` text DEFAULT NULL,
  `nt2901` text DEFAULT NULL,
  `nt3000` text DEFAULT NULL,
  `nt3001` text DEFAULT NULL,
  `nt3100` text DEFAULT NULL,
  `nt3101` text DEFAULT NULL,
  `nt3200` text DEFAULT NULL,
  `nt3201` text DEFAULT NULL,
  `nt3300` text DEFAULT NULL,
  `nt3301` text DEFAULT NULL,
  `nt3400` text DEFAULT NULL,
  `nt3401` text DEFAULT NULL,
  `nt3500` text DEFAULT NULL,
  `nt3501` text DEFAULT NULL,
  `nt3600` text DEFAULT NULL,
  `nt3601` text DEFAULT NULL,
  `nt3700` text DEFAULT NULL,
  `nt3701` text DEFAULT NULL,
  `nt3800` text DEFAULT NULL,
  `nt3801` text DEFAULT NULL,
  `nt3900` text DEFAULT NULL,
  `nt3901` text DEFAULT NULL,
  `nt4000` text DEFAULT NULL,
  `nt4001` text DEFAULT NULL,
  `nt4100` text DEFAULT NULL,
  `nt4101` text DEFAULT NULL,
  `emp_code` varchar(50) DEFAULT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `createdDTM` varchar(50) DEFAULT NULL,
  `taskDate` varchar(50) DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `modifiedDTM` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `power_consumption`
--

CREATE TABLE `power_consumption` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `cluster_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `morning_units` varchar(20) DEFAULT NULL,
  `consumption_date` varchar(20) DEFAULT NULL,
  `night_units` varchar(20) DEFAULT NULL,
  `total_consumption` varchar(20) DEFAULT NULL,
  `nonbusinesshours` int(11) NOT NULL,
  `remarks` varchar(200) DEFAULT NULL,
  `createdBy` varchar(20) DEFAULT NULL,
  `createdDTM` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `power_consumption_logs`
--

CREATE TABLE `power_consumption_logs` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `cluster_id` int(11) DEFAULT NULL,
  `zone_id` int(11) DEFAULT NULL,
  `morning_units` varchar(20) DEFAULT NULL,
  `consumption_date` varchar(20) DEFAULT NULL,
  `night_units` varchar(20) DEFAULT NULL,
  `total_consumption` varchar(20) DEFAULT NULL,
  `nonbusinesshours` int(11) NOT NULL,
  `remarks` varchar(200) DEFAULT NULL,
  `createdBy` varchar(20) DEFAULT NULL,
  `createdDTM` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `regd_remarks`
--

CREATE TABLE `regd_remarks` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `s_id` int(11) NOT NULL,
  `regd_no` varchar(11) NOT NULL,
  `regd_remarks` varchar(20) DEFAULT NULL,
  `createdBy` varchar(20) DEFAULT NULL,
  `createdDTM` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `repeat_punctures`
--

CREATE TABLE `repeat_punctures` (
  `id` int(11) NOT NULL,
  `report_type` varchar(20) NOT NULL,
  `regd_number` varchar(11) NOT NULL,
  `regd_remarks` varchar(200) NOT NULL,
  `task_id` int(11) NOT NULL,
  `createdDTM` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `repeat_samples`
--

CREATE TABLE `repeat_samples` (
  `id` int(11) NOT NULL,
  `report_type` varchar(20) NOT NULL,
  `regd_number` varchar(11) NOT NULL,
  `regd_remarks` varchar(200) NOT NULL,
  `task_id` int(11) NOT NULL,
  `createdDTM` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `repeat_scan`
--

CREATE TABLE `repeat_scan` (
  `id` int(11) NOT NULL,
  `report_type` varchar(20) NOT NULL,
  `regd_number` varchar(11) NOT NULL,
  `regd_remarks` varchar(200) NOT NULL,
  `task_id` int(11) NOT NULL,
  `createdDTM` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_amendments`
--

CREATE TABLE `report_amendments` (
  `id` int(11) NOT NULL,
  `report_type` varchar(20) NOT NULL,
  `regd_number` varchar(11) NOT NULL,
  `regd_remarks` varchar(200) NOT NULL,
  `task_id` int(11) NOT NULL,
  `createdDTM` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_escalations`
--

CREATE TABLE `report_escalations` (
  `id` int(11) NOT NULL,
  `report_type` varchar(20) NOT NULL,
  `regd_number` varchar(11) NOT NULL,
  `regd_remarks` varchar(200) NOT NULL,
  `task_id` int(11) NOT NULL,
  `createdDTM` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sample_rejections`
--

CREATE TABLE `sample_rejections` (
  `id` int(11) NOT NULL,
  `report_type` varchar(20) NOT NULL,
  `regd_number` varchar(11) NOT NULL,
  `regd_remarks` varchar(200) NOT NULL,
  `task_id` int(11) NOT NULL,
  `createdDTM` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `sid` int(11) NOT NULL,
  `service_date` date DEFAULT NULL,
  `service_type` varchar(300) DEFAULT NULL,
  `visiter_name` varchar(100) DEFAULT NULL,
  `visiter_mobile` varchar(15) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) NOT NULL,
  `createdDTM` datetime DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `updatedDTM` datetime DEFAULT NULL,
  `updatedBy` int(11) DEFAULT NULL,
  `status` enum('A','I') NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_manager`
--

CREATE TABLE `service_manager` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `number` varchar(100) NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_on` date NOT NULL,
  `status` varchar(20) DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subquestions`
--

CREATE TABLE `subquestions` (
  `id` int(11) NOT NULL,
  `task_id` varchar(50) NOT NULL,
  `sq_id` varchar(50) DEFAULT NULL,
  `sq` enum('NO','YES') NOT NULL DEFAULT 'NO',
  `squestion` varchar(500) NOT NULL,
  `sqvalue` varchar(50) NOT NULL DEFAULT '0',
  `createdDTM` varchar(50) DEFAULT NULL,
  `updatedDTM` varchar(50) DEFAULT NULL,
  `updatedBy` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_map`
--

CREATE TABLE `user_map` (
  `id` int(11) NOT NULL,
  `emp_code` int(11) NOT NULL,
  `zone` varchar(255) NOT NULL,
  `cluster` varchar(255) NOT NULL,
  `role` varchar(25) NOT NULL,
  `branches` varchar(255) NOT NULL,
  `created_on` datetime NOT NULL,
  `created_by` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usg`
--

CREATE TABLE `usg` (
  `id` int(11) NOT NULL,
  `nid` varchar(100) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `doctor_count` varchar(100) NOT NULL,
  `bmid` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vdc_forms`
--

CREATE TABLE `vdc_forms` (
  `id` int(11) NOT NULL,
  `form_name` varchar(50) NOT NULL,
  `form_description` text DEFAULT NULL,
  `created_dtm` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `status` char(1) NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor`
--

CREATE TABLE `vendor` (
  `vendor_id` int(11) NOT NULL,
  `vendor_name` varchar(100) DEFAULT NULL,
  `vendor_address` varchar(250) DEFAULT NULL,
  `vendor_email` varchar(50) DEFAULT NULL,
  `vendor_mobile` varchar(15) DEFAULT NULL,
  `vendor_gst` varchar(20) DEFAULT NULL,
  `branches` varchar(500) NOT NULL,
  `service_type` enum('Pest_Control_Service','Elevation_Cleaning_Service','Water_Tank_Cleaning_Service','') NOT NULL,
  `terms` text NOT NULL,
  `createdDTM` datetime DEFAULT NULL,
  `createdBy` int(11) DEFAULT NULL,
  `updatedDTM` datetime DEFAULT NULL,
  `updatedBy` int(11) DEFAULT NULL,
  `status` enum('A','I') NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `visit_master`
--

CREATE TABLE `visit_master` (
  `visit_id` int(11) NOT NULL,
  `visit_recurring` varchar(10) DEFAULT NULL,
  `visit_day` int(11) DEFAULT NULL,
  `branch_id` varchar(500) NOT NULL,
  `vendor_id` int(11) NOT NULL,
  `createdDTM` datetime DEFAULT NULL,
  `createdBy` int(11) NOT NULL,
  `updatedDTM` datetime DEFAULT NULL,
  `updatedBy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watsapp_msg_user`
--

CREATE TABLE `watsapp_msg_user` (
  `id` int(11) NOT NULL,
  `type` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `watsapp_msg_user_list`
--

CREATE TABLE `watsapp_msg_user_list` (
  `id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_messages_log`
--

CREATE TABLE `whatsapp_messages_log` (
  `id` int(11) NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `emp_code` int(11) NOT NULL,
  `message` varchar(500) NOT NULL,
  `sent_dtm` datetime NOT NULL,
  `hkr_id` int(11) DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `xray`
--

CREATE TABLE `xray` (
  `id` int(11) NOT NULL,
  `nid` varchar(100) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `doctor_count` varchar(100) NOT NULL,
  `bmid` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `zones`
--

CREATE TABLE `zones` (
  `z_id` int(11) NOT NULL,
  `zone` varchar(200) NOT NULL,
  `clusters` text NOT NULL,
  `branches` text NOT NULL,
  `status` varchar(50) DEFAULT 'A'
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
-- Indexes for table `bmcm_2`
--
ALTER TABLE `bmcm_2`
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
-- Indexes for table `branches_old`
--
ALTER TABLE `branches_old`
  ADD PRIMARY KEY (`branch_id`);

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
-- Indexes for table `files_orphans`
--
ALTER TABLE `files_orphans`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `idx_files_hkr_id` (`hkr_id`),
  ADD KEY `idx_files_uploaded_by` (`uploaded_by`);

--
-- Indexes for table `form_inputs`
--
ALTER TABLE `form_inputs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form_records`
--
ALTER TABLE `form_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form_sections`
--
ALTER TABLE `form_sections`
  ADD PRIMARY KEY (`section_id`);

--
-- Indexes for table `form_sub_sections`
--
ALTER TABLE `form_sub_sections`
  ADD PRIMARY KEY (`sub_section_id`);

--
-- Indexes for table `hk_branchwise_budget`
--
ALTER TABLE `hk_branchwise_budget`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hk_branch_ideal_qty`
--
ALTER TABLE `hk_branch_ideal_qty`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `new_department`
--
ALTER TABLE `new_department`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `new_emp_master_2`
--
ALTER TABLE `new_emp_master_2`
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
-- Indexes for table `vdc_forms`
--
ALTER TABLE `vdc_forms`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `auth_tokens`
--
ALTER TABLE `auth_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bi_centres`
--
ALTER TABLE `bi_centres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bmcm`
--
ALTER TABLE `bmcm`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bmcm_2`
--
ALTER TABLE `bmcm_2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bm_tasks`
--
ALTER TABLE `bm_tasks`
  MODIFY `mid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bm_tasks_logs`
--
ALTER TABLE `bm_tasks_logs`
  MODIFY `mid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bm_weekly_list`
--
ALTER TABLE `bm_weekly_list`
  MODIFY `bmw_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches_old`
--
ALTER TABLE `branches_old`
  MODIFY `branch_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_checklist_items`
--
ALTER TABLE `branding_checklist_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_checklist_records`
--
ALTER TABLE `branding_checklist_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_photos`
--
ALTER TABLE `branding_photos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_sections`
--
ALTER TABLE `branding_sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branding_sub_sections`
--
ALTER TABLE `branding_sub_sections`
  MODIFY `sub_section_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cardiologist_ecg`
--
ALTER TABLE `cardiologist_ecg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cardiologist_tmt`
--
ALTER TABLE `cardiologist_tmt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cluster`
--
ALTER TABLE `cluster`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clusters`
--
ALTER TABLE `clusters`
  MODIFY `cluster_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cluster_branch_map`
--
ALTER TABLE `cluster_branch_map`
  MODIFY `cb_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cluster_zone_map`
--
ALTER TABLE `cluster_zone_map`
  MODIFY `cz_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clust_area_map`
--
ALTER TABLE `clust_area_map`
  MODIFY `cl_ar_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cm_morning_tasks`
--
ALTER TABLE `cm_morning_tasks`
  MODIFY `mid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cm_night_tasks`
--
ALTER TABLE `cm_night_tasks`
  MODIFY `cm_nid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ct`
--
ALTER TABLE `ct`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diesel_consumption`
--
ALTER TABLE `diesel_consumption`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `diesel_consumption_logs`
--
ALTER TABLE `diesel_consumption_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doc_count`
--
ALTER TABLE `doc_count`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `empareamap`
--
ALTER TABLE `empareamap`
  MODIFY `ea_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files_orphans`
--
ALTER TABLE `files_orphans`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `form_inputs`
--
ALTER TABLE `form_inputs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `form_records`
--
ALTER TABLE `form_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `form_sections`
--
ALTER TABLE `form_sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `form_sub_sections`
--
ALTER TABLE `form_sub_sections`
  MODIFY `sub_section_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_branchwise_budget`
--
ALTER TABLE `hk_branchwise_budget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_branch_ideal_qty`
--
ALTER TABLE `hk_branch_ideal_qty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_consumptions`
--
ALTER TABLE `hk_consumptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_details`
--
ALTER TABLE `hk_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_items`
--
ALTER TABLE `hk_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_materials_remove`
--
ALTER TABLE `hk_materials_remove`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_monthly_indents`
--
ALTER TABLE `hk_monthly_indents`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_monthly_indent_items`
--
ALTER TABLE `hk_monthly_indent_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_notifications`
--
ALTER TABLE `hk_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_opening_stock`
--
ALTER TABLE `hk_opening_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_requirements`
--
ALTER TABLE `hk_requirements`
  MODIFY `hkr_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_requirement_receipts`
--
ALTER TABLE `hk_requirement_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_stock_balances`
--
ALTER TABLE `hk_stock_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_stock_receipts`
--
ALTER TABLE `hk_stock_receipts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_stock_receipt_items`
--
ALTER TABLE `hk_stock_receipt_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_stock_received`
--
ALTER TABLE `hk_stock_received`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hk_visits`
--
ALTER TABLE `hk_visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_sessions`
--
ALTER TABLE `login_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `morningtasks`
--
ALTER TABLE `morningtasks`
  MODIFY `mid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mri`
--
ALTER TABLE `mri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_department`
--
ALTER TABLE `new_department`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `new_emp_master_2`
--
ALTER TABLE `new_emp_master_2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `nighttasks`
--
ALTER TABLE `nighttasks`
  MODIFY `nid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `power_consumption`
--
ALTER TABLE `power_consumption`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `power_consumption_logs`
--
ALTER TABLE `power_consumption_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `regd_remarks`
--
ALTER TABLE `regd_remarks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repeat_punctures`
--
ALTER TABLE `repeat_punctures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repeat_samples`
--
ALTER TABLE `repeat_samples`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `repeat_scan`
--
ALTER TABLE `repeat_scan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_amendments`
--
ALTER TABLE `report_amendments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `report_escalations`
--
ALTER TABLE `report_escalations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sample_rejections`
--
ALTER TABLE `sample_rejections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `sid` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subquestions`
--
ALTER TABLE `subquestions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_map`
--
ALTER TABLE `user_map`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usg`
--
ALTER TABLE `usg`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vdc_forms`
--
ALTER TABLE `vdc_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor`
--
ALTER TABLE `vendor`
  MODIFY `vendor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `visit_master`
--
ALTER TABLE `visit_master`
  MODIFY `visit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `watsapp_msg_user`
--
ALTER TABLE `watsapp_msg_user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `watsapp_msg_user_list`
--
ALTER TABLE `watsapp_msg_user_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `whatsapp_messages_log`
--
ALTER TABLE `whatsapp_messages_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `xray`
--
ALTER TABLE `xray`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zones`
--
ALTER TABLE `zones`
  MODIFY `z_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

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
