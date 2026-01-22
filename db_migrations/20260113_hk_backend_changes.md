Backend changes to support migration (apply after DB migration)

1) Update processFileUpload in HKRequirements::processFileUpload

Replace/extend the $filesData[] assignment to include new fields recorded by the migration:

    // Save file metadata in the database
    $filesData[] = [
         'file_name' => $newFileName,
         'original_name' => $file->getClientName(),
         'mime_type' => $file->getClientMimeType(),
         'file_size' => $file->getSize(),
         'hkr_id' => $hkr_id,
         'createdDTM' => date('Y-m-d H:i:s'),
         'uploaded_by' => $user // emp_code used earlier in upload logic
    ];

Notes:
- Ensure `files` table has columns: original_name, mime_type, file_size, uploaded_by (we added them in migration).
- Use htmlspecialchars() as needed when storing metadata to avoid injection.

2) Update FileUpload::uploadFile() to return metadata and populate uploaded_by / file_size / mime_type

When moving file and preparing $fileData, include:

    $fileData = [
        'file_name' => trim($newFileName),
        'original_name' => $file->getClientName(),
        'mime_type' => $file->getClientMimeType(),
        'file_size' => $file->getSize(),
        'hkr_id' => $this->request->getPost('hkr_id') ?: null,
        'emp_code' => htmlspecialchars($user, ENT_QUOTES, 'UTF-8'),
        'createdDTM' => date('Y-m-d H:i:s'),
    ];

3) Add admin endpoint to trigger reconciliation (optional)

In a controller (e.g., HKRequirements), add:

    public function runReconciliation()
    {
        $userDetails = $this->validateAuthorization();
        if (!$userDetails) return $this->respond(['status'=>false,'message'=>'Unauthorized'],401);

        // Allow only Admin or Super Admin
        if (!in_array($userDetails->role, ['ADMIN','SUPER_ADMIN'])) {
            return $this->respond(['status'=>false,'message'=>'Forbidden'],403);
        }

        $db = \\Config\\Database::connect();
        try {
            $db->simpleQuery("CALL sp_reconcile_hk_balances()");
            return $this->respond(['status'=>true,'message'=>'Reconciliation job started/finished'],200);
        } catch (\\Exception $e) {
            return $this->respond(['status'=>false,'message'=>$e->getMessage()],500);
        }
    }

4) Audit: if you want reconciled_by captured, enhance stored procedure to accept an input param and update hk_balance_reconciliations.reconciled_by accordingly. Or after calling, update the reconciliation rows to set reconciled_by using the admin emp_code.

5) Tests and verification
- After running migration, run:
    -- count files with numeric hkr_id
    SELECT COUNT(*) FROM files WHERE hkr_id IS NOT NULL;
    -- quick reconciliation test
    CALL sp_reconcile_hk_balances();
    SELECT * FROM hk_balance_reconciliations ORDER BY reconciled_at DESC LIMIT 20;

6) Rollout notes
- Run migrations in dev and staging first. Keep DB dump backups. Monitor logs for errors during file uploads and reconciliation.

If you want, I can open a PR with the exact PHP changes (with diff) and add an automated test script to call the endpoint and verify reconciliations.