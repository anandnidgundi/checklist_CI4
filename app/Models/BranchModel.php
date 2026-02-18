<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchModel extends Model
{
     /**
      * Get detailed branch information by id.
      * - Primary source: `secondary` DB -> `Branches` table (travelapp)
      * - Enrich with cluster info (clusters table) and test data
      *
      * Returns an associative array (or null when not found).
      */
     public function getBranchDetails($id)
     {


          // Connect to the default database
          $db = \Config\Database::connect();

          // Connect to the secondary database
          $db_secondary = \Config\Database::connect('secondary');

          // 1️⃣ Fetch main branch info — prefer `branch_info` in secondary, otherwise fallback to `Branches` table
          $branch = null;

          // Try `branch_info` (prefer default DB; fallback to secondary)
          try {
               $branchInfoDb = null;
               if ($db->tableExists('branch_info')) {
                    $branchInfoDb = $db;
               } elseif ($db_secondary->tableExists('branch_info')) {
                    $branchInfoDb = $db_secondary;
               }

               if ($branchInfoDb) {
                    $schema = $branchInfoDb->database ? $branchInfoDb->database . '.' : '';

                    // include JOINs only if the referenced tables exist — decide whether we will JOIN bic or fetch it separately
                    $hasBranches = $db_secondary->tableExists('Branches') || $db_secondary->tableExists('branches') || $branchInfoDb->tableExists('Branches') || $branchInfoDb->tableExists('branches');
                    $hasBranchCategory = $branchInfoDb->tableExists('branch_category');

                    // decide which DB contains Branches and whether it is the same DB as branch_info (so we can JOIN safely)
                    $branchesDb = null;
                    $willJoinBranches = false;
                    if ($hasBranches) {
                         $branchesDb = ($db_secondary->tableExists('Branches') || $db_secondary->tableExists('branches')) ? $db_secondary : $branchInfoDb;
                         if ($branchesDb->database === $branchInfoDb->database) $willJoinBranches = true;
                    }

                    $selectParts = [
                         'bi.branch_id',
                         'bi.branch_manager_name',
                         'bi.branch_manager_mobile',
                         ($willJoinBranches ? 'bic.SysField AS branch_name' : 'NULL AS branch_name'),
                         'bi.state AS state_id',
                         'bi.city AS city_id',
                         'bi.category AS category_id',
                         ($hasBranchCategory ? 'bc.branch_type' : "NULL AS branch_type"),
                         'bi.address',
                         'bi.cluster_manager_name',
                         'bi.cluster_manager_mobile',
                         'bi.landline_no',
                         'bi.zonal_manager_name',
                         'bi.zonal_manager_mobile',
                         'bi.mobile_no',
                         'bi.pre_number',
                         'bi.map',
                         'bi.branch_email',
                         'bi.branch_manager_email',
                         'bi.branch_timing_weekdays_from',
                         'bi.branch_timing_weekdays_to',
                         'bi.modified_date',
                         'bi.branch_timing_weekend_from',
                         'bi.branch_timing_weekend_to',
                    ];

                    $joins = [];
                    $branchesExternalDb = null; // when Branches live in a different DB, query separately later

                    if ($hasBranches) {
                         if ($willJoinBranches) {
                              $branchesSchema = $branchesDb->database ? $branchesDb->database . '.' : '';
                              $joins[] = "LEFT JOIN " . $branchesSchema . "Branches bic ON bi.branch_id = bic.id";
                         } else {
                              // Branches exists but in a different DB — fetch branch_name afterwards from $branchesDb
                              $branchesExternalDb = $branchesDb;
                         }
                    }

                    if ($hasBranchCategory) {
                         $joins[] = "LEFT JOIN " . $schema . "branch_category bc ON bi.category = bc.id";
                    }

                    // Match branch_info.branch_id exactly OR numerically (handles '001' vs '1')
                    $sql = 'SELECT ' . implode(', ', $selectParts) . ' FROM branch_info AS bi ' . implode(' ', $joins) . ' WHERE bi.branch_id = ? OR (bi.branch_id + 0) = (? + 0)';

                    $query = $branchInfoDb->query($sql, [$id, $id]);
                    $branch = $query->getRowArray();

                    // If Branches table was in a different DB, fetch branch_name separately to avoid cross-db comparisons/collation issues
                    if ($branchesExternalDb && (! isset($branch['branch_name']) || $branch['branch_name'] === null)) {
                         try {
                              $bn = $branchesExternalDb->table('Branches')
                                   ->select('SysField AS branch_name')
                                   ->where('SysNo', $branch['branch_id'])
                                   ->get()
                                   ->getRowArray();
                              if (! empty($bn['branch_name'])) $branch['branch_name'] = $bn['branch_name'];
                         } catch (\Exception $e) {
                              log_message('debug', 'BranchModel:getBranchDetails fetch external branch_name failed: ' . $e->getMessage());
                         }
                    }
               }
          } catch (\Exception $e) {
               log_message('warning', 'BranchModel:getBranchDetails branch_info query failed: ' . $e->getMessage());
               $branch = null;
          }

          // If branch_info is not present / returned, try a minimal fallback from secondary `Branches` table(s)
          if (! $branch) {
               $candidates = [
                    ['table' => 'Branches', 'idCol' => 'SysNo', 'nameCol' => 'SysField'],
                    ['table' => 'Branches', 'idCol' => 'id',     'nameCol' => 'SysField'],
                    ['table' => 'branches',  'idCol' => 'id',     'nameCol' => 'SysField'],
               ];

               foreach ($candidates as $c) {
                    $found = false;

                    // try secondary first for Branches, then default
                    foreach ([$db_secondary, $db] as $candidateDb) {
                         try {
                              if (! $candidateDb->tableExists($c['table'])) continue;

                              $row = $candidateDb->table($c['table'])
                                   ->select($c['idCol'] . ' AS branch_id, ' . $c['nameCol'] . ' AS branch_name')
                                   ->where($c['idCol'], $id)
                                   ->get()
                                   ->getRowArray();

                              if ($row) {
                                   $branch = $row;
                                   $found = true;
                                   break;
                              }
                         } catch (\Exception $e) {
                              // try next candidate DB
                              continue;
                         }
                    }

                    if ($found) break;
               }

               // nothing found — return null to indicate not found
               if (! $branch) return null;

               // ensure expected keys exist (safe defaults)
               $defaults = [
                    'landline_no' => null,
                    'mobile_no' => null,
                    'pre_number' => null,
                    'address' => null,
                    'branch_type' => null,
                    'map' => null,
                    'branch_manager_name' => null,
                    'branch_manager_mobile' => null,
                    'cluster_manager_name' => null,
                    'cluster_manager_mobile' => null,
                    'zonal_manager_name' => null,
                    'zonal_manager_mobile' => null,
                    'branch_email' => null,
                    'branch_manager_email' => null,
                    'branch_timing_weekdays_from' => null,
                    'branch_timing_weekdays_to' => null,
                    'branch_timing_weekend_from' => null,
                    'branch_timing_weekend_to' => null,
                    'state_id' => null,
                    'city_id' => null,
               ];
               foreach ($defaults as $k => $v) {
                    if (! array_key_exists($k, $branch)) $branch[$k] = $v;
               }
          }

          // Attempt to infer `branch_type` when it's missing (prefer default DB, then secondary)
          try {
               if (empty($branch['branch_type'])) {
                    // Quick heuristic: if `category_id` (from branch_info) already contains a short alpha code
                    // (e.g. 'A','B','C','D'), treat it as the branch_type immediately. This covers cases where
                    // deployments stored the type directly in the `category` column.
                    if (! empty($branch['category_id']) && ! is_numeric($branch['category_id']) && preg_match('/^[A-Za-z]$/', trim((string) $branch['category_id']))) {
                         $branch['branch_type'] = strtoupper(trim((string) $branch['category_id']));
                    }

                    // 1) try category_id -> branch_category (prefer default DB)
                    if (! empty($branch['category_id']) && (empty($branch['branch_type']) || !preg_match('/^[A-Za-z]$/', $branch['branch_type']))) {
                         $bcDb = null;
                         if ($db->tableExists('branch_category')) $bcDb = $db;
                         elseif ($db_secondary->tableExists('branch_category')) $bcDb = $db_secondary;

                         if ($bcDb) {
                              $bc = $bcDb->table('branch_category')
                                   ->select('branch_type')
                                   ->where('id', $branch['category_id'])
                                   ->get()
                                   ->getRowArray();

                              if (! empty($bc['branch_type'])) {
                                   $branch['branch_type'] = $bc['branch_type'];
                              }
                         }
                    }

                    // 2) probe Branches / branches table for category/type-like columns (default first)
                    if (empty($branch['branch_type'])) {
                         $tablesToCheck = ['Branches', 'branches', 'Branch', 'branch', 'branch_master', 'branch_list', 'BranchMaster', 'BranchList'];
                         $colCandidates = ['branch_type', 'branchType', 'b_type', 'type', 'category', 'category_id', 'CategoryId', 'categoryid', 'cat', 'branch_category', 'branch_cat', 'branchcategory', 'branch_category_id', 'branchcat', 'branchClass', 'branch_class', 'centre_type', 'centre_category'];
                         $inferred = false;

                         foreach ($tablesToCheck as $tbl) {
                              foreach ([$db_secondary, $db] as $candidateDb) {
                                   if (! $candidateDb->tableExists($tbl)) continue;

                                   try {
                                        $fields = $candidateDb->getFieldNames($tbl);
                                   } catch (\Exception $e) {
                                        continue;
                                   }

                                   $foundCol = null;
                                   foreach ($colCandidates as $cc) {
                                        if (in_array($cc, $fields, true)) {
                                             $foundCol = $cc;
                                             break;
                                        }
                                   }
                                   if (! $foundCol) continue;

                                   // find id column
                                   $idCols = ['SysNo', 'id', 'branch_id'];
                                   $idCol = null;
                                   foreach ($idCols as $idc) {
                                        if (in_array($idc, $fields, true)) {
                                             $idCol = $idc;
                                             break;
                                        }
                                   }
                                   if (! $idCol) $idCol = 'SysNo';

                                   $r = $candidateDb->table($tbl)
                                        ->select($foundCol . ' AS val')
                                        ->where($idCol, $branch['branch_id'])
                                        ->get()
                                        ->getRowArray();

                                   if (! $r || ! isset($r['val']) || $r['val'] === null || $r['val'] === '') continue;

                                   $val = $r['val'];

                                   // normalize short codes or strings like "Type D" -> extract single-letter code
                                   if (is_string($val)) {
                                        $trimVal = trim($val);
                                        if (preg_match('/^[A-Za-z]$/', $trimVal)) {
                                             $val = strtoupper($trimVal);
                                        } elseif (preg_match('/\b([A-Za-z])\b/', $trimVal, $m)) {
                                             $val = strtoupper($m[1]);
                                        }
                                   }

                                   // direct branch_type value
                                   if (stripos($foundCol, 'type') !== false) {
                                        $branch['branch_type'] = $val;
                                        $inferred = true;
                                        break;
                                   }

                                   // numeric -> branch_category.id
                                   if (is_numeric($val)) {
                                        $bcLookupDb = $db->tableExists('branch_category') ? $db : ($db_secondary->tableExists('branch_category') ? $db_secondary : null);
                                        if ($bcLookupDb) {
                                             $bc = $bcLookupDb->table('branch_category')
                                                  ->select('branch_type')
                                                  ->where('id', (int) $val)
                                                  ->get()
                                                  ->getRowArray();
                                             if (! empty($bc['branch_type'])) {
                                                  $branch['branch_type'] = $bc['branch_type'];
                                                  $inferred = true;
                                                  break;
                                             }
                                        }
                                   }

                                   // string -> match against branch_category name-like columns
                                   $bcLookupDb = $db->tableExists('branch_category') ? $db : ($db_secondary->tableExists('branch_category') ? $db_secondary : null);
                                   if ($bcLookupDb) {
                                        try {
                                             $bcFields = $bcLookupDb->getFieldNames('branch_category');
                                        } catch (\Exception $e) {
                                             $bcFields = [];
                                        }
                                        $nameCols = ['name', 'category', 'category_name', 'cat'];
                                        $matchCol = null;
                                        foreach ($nameCols as $nc) {
                                             if (in_array($nc, $bcFields, true)) {
                                                  $matchCol = $nc;
                                                  break;
                                             }
                                        }
                                        if ($matchCol) {
                                             $bc = $bcLookupDb->table('branch_category')
                                                  ->select('branch_type')
                                                  ->where($matchCol, $val)
                                                  ->get()
                                                  ->getRowArray();
                                             if (! empty($bc['branch_type'])) {
                                                  $branch['branch_type'] = $bc['branch_type'];
                                                  $inferred = true;
                                                  break;
                                             }
                                        }
                                   }
                              } // candidateDb

                              if ($inferred) break;
                         } // tablesToCheck

                         if (! $inferred && empty($branch['branch_type'])) {
                              log_message('debug', "BranchModel:getBranchDetails - could not determine branch_type for branch_id={$branch['branch_id']}");
                         } elseif ($inferred) {
                              log_message('debug', "BranchModel:getBranchDetails - inferred branch_type='{$branch['branch_type']}' for branch_id={$branch['branch_id']}");
                         }
                    }
               }
          } catch (\Exception $e) {
               log_message('debug', 'BranchModel:getBranchDetails branch_type derivation error: ' . $e->getMessage());
          }

          // Safely fetch state / city only if IDs exist and tables are present
          $state = null;
          $city = null;

          try {
               $state = null;
               if (! empty($branch['state_id'])) {
                    $stateDb = null;
                    if ($db->tableExists('state')) $stateDb = $db;
                    elseif ($db_secondary->tableExists('state')) $stateDb = $db_secondary;

                    if ($stateDb) {
                         $stateQuery = $stateDb->query("SELECT name AS state_name FROM state WHERE id = ?", [$branch['state_id']]);
                         $state = $stateQuery->getRowArray();
                    }
               }
          } catch (\Exception $e) {
               log_message('warning', 'BranchModel:getBranchDetails state query failed: ' . $e->getMessage());
               $state = null;
          }

          try {
               $city = null;
               if (! empty($branch['city_id'])) {
                    $cityDb = null;
                    if ($db->tableExists('city')) $cityDb = $db;
                    elseif ($db_secondary->tableExists('city')) $cityDb = $db_secondary;

                    if ($cityDb) {
                         $cityQuery = $cityDb->query("SELECT city_name FROM city WHERE id = ?", [$branch['city_id']]);
                         $city = $cityQuery->getRowArray();
                    }
               }
          } catch (\Exception $e) {
               log_message('warning', 'BranchModel:getBranchDetails city query failed: ' . $e->getMessage());
               $city = null;
          }

          // 6️⃣ Merge results (use null-coalesce to avoid notices)
          return [
               'branch_id' => $branch['branch_id'] ?? null,
               'branch_name' => $branch['branch_name'] ?? null,
               'landline_no' => $branch['landline_no'] ?? null,
               'branch_mobile_no' => $branch['mobile_no'] ?? null,
               'branch_pre_number' => $branch['pre_number'] ?? null,
               'branch_type' => $branch['branch_type'] ?? null,
               'state_name' => $state['state_name'] ?? null,
               'city_name' => $city['city_name'] ?? null,
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
          ];
     }
}
