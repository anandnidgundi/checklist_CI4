<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
     /**
      * Get detailed branch information by id.
      * - Primary source: `secondary` DB -> `Branches` table (travelapp)
      * - Enrich with cluster info (clusters table), nearest branches (by city/state)
      *
      * Returns an associative array (or null when not found).
      */
     public function getBranchDetails($id)
     {
          $id = (int) $id;
          if ($id <= 0) {
               return null;
          }

          try {
               // Primary (app) DB + secondary (travelapp) DB connections
               $db = \Config\Database::connect();
               $db_secondary = \Config\Database::connect('secondary');

               // 1) Fetch main branch info from `branch_info` (primary DB) and enrich with secondary Branches
               $sql = "SELECT bi.branch_id,bi.branch_manager_name,bi.branch_manager_mobile,bic.SysField AS branch_name, s.name AS sbu_name, s.status AS sbu_status,
                  bi.state AS state_id, bi.city AS city_id, bc.branch_type, bi.address, bi.cluster_manager_name, bi.cluster_manager_mobile, bi.landline_no,
                  bi.zonal_manager_name,bi.zonal_manager_mobile,bi.mobile_no,bi.pre_number,
                  bi.map, bi.branch_email, bi.branch_manager_email, 
                  bi.branch_timing_weekdays_from, bi.branch_timing_weekdays_to,bi.modified_date, 
                  bi.branch_timing_weekend_from, bi.branch_timing_weekend_to,  GROUP_CONCAT(pc.name SEPARATOR ', ') as processing_center_name
            FROM branch_info AS bi
            LEFT JOIN " . $db_secondary->database . ".Branches bic ON bi.branch_id = bic.SysNo
            LEFT JOIN sbu s ON bi.sbu = s.id
            LEFT JOIN processing_centers pc ON FIND_IN_SET(pc.id, bi.processing_center_id)
            LEFT JOIN branch_category bc ON bi.category = bc.id
            WHERE bi.branch_id = ?";

               $query = $db->query($sql, [$id]);
               $branch = $query->getRowArray();

               if (! $branch) {
                    // nothing found in primary `branch_info`
                    return null;
               }

               // 2) state (from secondary DB)
               $state = null;
               if (! empty($branch['state_id'])) {
                    $stateRow = $db_secondary->query("SELECT name AS state_name FROM state WHERE id = ?", [$branch['state_id']])->getRowArray();
                    $state = $stateRow['state_name'] ?? null;
               }

               // 3) city (from secondary DB)
               $city = null;
               if (! empty($branch['city_id'])) {
                    $cityRow = $db_secondary->query("SELECT city_name FROM city WHERE id = ?", [$branch['city_id']])->getRowArray();
                    $city = $cityRow['city_name'] ?? null;
               }

               // 4) nearest branches (use branch_nearest_branches + secondary Branches)
               $nearestSql = "SELECT DISTINCT bnb.near_branch, b.SysField AS near_branch_name, bnb.distance, bnb.time
                    FROM branch_nearest_branches bnb
                    LEFT JOIN " . $db_secondary->database . ".Branches b ON bnb.near_branch = b.SysNo
                    WHERE bnb.branch = ?
                    ORDER BY bnb.distance ASC";
               $nearestBranches = $db->query($nearestSql, [$id])->getResultArray();

               // 5) top ten packages/tests
               $toptenPackages = $db->query(
                    "SELECT ttp.service_id, ttp.package_name, sm.ValidUpTo
                     FROM top_ten_package ttp
                     JOIN servicemaster sm ON ttp.service_id = sm.ServiceId
                     WHERE CAST(ttp.branch AS UNSIGNED) = CAST(? AS UNSIGNED)
                     ORDER BY ttp.id DESC",
                    [$id]
               )->getResultArray();

               $toptenTests = $db->query(
                    "SELECT tts.service_id, tts.package_name
                     FROM top_ten_tests tts
                     WHERE tts.branch = ?
                     ORDER BY tts.id DESC",
                    [$id]
               )->getResultArray();

               // 6) tests (branch_info_other + daily override)
               $tests = $db->query(
                    "SELECT bio.branch, bio.test_category, bio.test_name, bio.week_days_from_time1, bio.week_days_to_time1, bio.week_end_days_from_time1,
bio.week_end_days_to_time1, bio.week_days_from_time2, bio.week_days_to_time2, bio.week_days_from_time3, bio.week_days_to_time3, bio.week_end_days_from_time2,
bio.week_end_days_to_time2, bio.week_end_days_from_time3, bio.week_end_days_to_time3, bio.oncall, bio.prescription, bio.remarks, bio.tat, bio.appointment_number, bio.female_available, bio.special_instructions, bio.appointment_required,
bio.modified_date, biod.daily_remarks
FROM branch_info_other AS bio
LEFT JOIN branch_info_other_daily AS biod ON bio.branch = biod.branch AND biod.daily_validity = CURDATE() and biod.`dis_status`='Y' and bio.test_name=biod.test_name
WHERE bio.branch = ? AND bio.na != 'NA' AND bio.status = '1'",
                    [$id]
               )->getResultArray();

               // 7) available tests (category table)
               $availableTests = $db->query("SELECT * FROM `branch_info_test_category` WHERE branch = ? AND status = '0' ORDER BY test_name", [$id])->getResultArray();

               // 8) assemble and return
               return [
                    'branch_id' => $branch['branch_id'],
                    'branch_name' => $branch['branch_name'],
                    'landline_no' => $branch['landline_no'] ?? null,
                    'branch_mobile_no' => $branch['mobile_no'] ?? null,
                    'branch_pre_number' => $branch['pre_number'] ?? null,
                    'sbu_name' => $branch['sbu_name'] ?? null,
                    'sbu_status' => $branch['sbu_status'] ?? null,
                    'branch_type' => $branch['branch_type'] ?? null,
                    'processing_center_name' => $branch['processing_center_name'] ?? null,
                    'branch_modified_date' => $branch['modified_date'] ?? null,
                    'state_name' => $state,
                    'city_name' => $city,
                    'address' => $branch['address'] ?? null,
                    'map' => $branch['map'] ?? null,
                    'branch_manager_name' => $branch['branch_manager_name'] ?? null,
                    'branch_manager_mobile' => $branch['branch_manager_mobile'] ?? null,
                    'cluster_manager_name' => $branch['cluster_manager_name'] ?? null,
                    'cluster_manager_mobile' => $branch['cluster_manager_mobile'] ?? null,
                    'zonal_manager_name' => $branch['zonal_manager_name'] ?? null,
                    'zonal_manager_mobile' => $branch['zonal_manager_mobile'] ?? null,
                    'branch_email' => $branch['branch_email'] ?? null,
                    'branch_manager_email' => $branch['branch_manager_email'] ?? null,
                    'branch_timing_weekdays_from' => $branch['branch_timing_weekdays_from'] ?? null,
                    'branch_timing_weekdays_to' => $branch['branch_timing_weekdays_to'] ?? null,
                    'branch_timing_weekend_from' => $branch['branch_timing_weekend_from'] ?? null,
                    'branch_timing_weekend_to' => $branch['branch_timing_weekend_to'] ?? null,
                    'nearest_branches' => $nearestBranches ?: [],
                    'top_ten_packages' => $toptenPackages ?: [],
                    'top_ten_tests' => $toptenTests ?: [],
                    'tests' => $tests ?: [],
                    'available_tests' => $availableTests ?: [],
               ];
          } catch (\Throwable $ex) {
               log_message('error', 'BranchModel::getBranchDetails exception: ' . $ex->getMessage());
               return null;
          }
     }
}
