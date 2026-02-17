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
            // 1) Try to fetch from the secondary (travelapp) DB first
            $db2 = \Config\Database::connect('secondary');

            $branchRow = $db2->table('Branches')
                ->select('*')
                ->where('id', $id)
                ->get()
                ->getRowArray();

            // If not found in secondary DB, fall back to local `branches` / `bi_centres`
            if (empty($branchRow)) {
                // try primary DB tables used in this app
                $local = $this->db->table('branches')
                    ->select('branch_id as id, branch as SysField, address, city, state, pincode, phone')
                    ->where('branch_id', $id)
                    ->get()
                    ->getRowArray();

                if (!empty($local)) {
                    // normalize to same keys as travelapp `Branches` where possible
                    $branchRow = array_merge(
                        ['id' => $local['id'], 'SysField' => $local['SysField']],
                        array_intersect_key($local, array_flip(['address', 'city', 'state', 'pincode', 'phone']))
                    );
                }
            }

            if (empty($branchRow)) {
                return null;
            }

            $result = [
                'branch' => $branchRow,
                // placeholders to be filled below
                'cluster' => null,
                'nearest_branches' => [],
                'processing_centres' => [],
                'tests' => [],
                'top_ten_tests' => [],
            ];

            // 2) Cluster / zone info (clusters table commonly contains a CSV `branches` column)
            try {
                $clusterRow = $db2->table('clusters')
                    ->select('cluster, cluster_id, branches')
                    ->where("FIND_IN_SET(?, branches)", $id, false)
                    ->get()
                    ->getRowArray();

                if (!empty($clusterRow)) {
                    $result['cluster'] = $clusterRow;
                }
            } catch (\Throwable $e) {
                // non-fatal; some deployments may not have clusters table in the secondary DB
                log_message('warning', 'BranchModel::getBranchDetails - cluster lookup failed: ' . $e->getMessage());
            }

            // 3) Nearest branches (same city/state) — best-effort from secondary DB
            try {
                $qb = $db2->table('Branches')->select('id, SysField, city, state');
                if (!empty($branchRow['city'])) {
                    $qb->where('city', $branchRow['city']);
                } elseif (!empty($branchRow['state'])) {
                    $qb->where('state', $branchRow['state']);
                }
                $nearest = $qb->where('id !=', $id)
                    ->limit(6)
                    ->get()
                    ->getResultArray();

                $result['nearest_branches'] = $nearest ?: [];
            } catch (\Throwable $e) {
                log_message('warning', 'BranchModel::getBranchDetails - nearest branches lookup failed: ' . $e->getMessage());
            }

            // 4) Processing centres & tests (best-effort, defensive)
            // These tables vary across deployments; attempt to read common names and ignore failures.
            try {
                if ($db2->tableExists('processing_centres')) {
                    $pcs = $db2->table('processing_centres')
                        ->select('*')
                        ->where('branch_id', $id)
                        ->get()
                        ->getResultArray();

                    $result['processing_centres'] = $pcs ?: [];
                }
            } catch (\Throwable $e) {
                log_message('debug', 'BranchModel::getBranchDetails - processing_centres not available: ' . $e->getMessage());
            }

            try {
                // common test tables might be `tests`, `test_master` or `lab_tests` — try a few
                $tests = [];
                if ($db2->tableExists('tests')) {
                    $tests = $db2->table('tests')->select('id, name')->where('branch_id', $id)->limit(50)->get()->getResultArray();
                } elseif ($db2->tableExists('test_master')) {
                    $tests = $db2->table('test_master')->select('id, test_name as name')->where('branch_id', $id)->limit(50)->get()->getResultArray();
                }
                $result['tests'] = $tests ?: [];
            } catch (\Throwable $e) {
                log_message('debug', 'BranchModel::getBranchDetails - tests lookup failed: ' . $e->getMessage());
            }

            // 5) Top ten tests (if analytics available) — best-effort fallback to empty
            $result['top_ten_tests'] = [];

            return $result;
        } catch (\Throwable $ex) {
            log_message('error', 'BranchModel::getBranchDetails exception: ' . $ex->getMessage());
            return null;
        }
    }
}

