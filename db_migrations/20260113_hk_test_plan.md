Validation & Test Plan — HK schema updates

Pre-req: Take DB backup with mysqldump. Run migration in dev first.

1) Post-migration quick checks (SQL):

-- Files table columns present and hkr_id numeric
DESCRIBE files;
SELECT COUNT(*) AS files_with_hkr FROM files WHERE hkr_id IS NOT NULL;
SELECT file_id, file_name, original_name, mime_type, file_size, uploaded_by FROM files LIMIT 10;

-- Reconciliation table exists
DESCRIBE hk_balance_reconciliations;
SELECT COUNT(*) FROM hk_balance_reconciliations;

-- hk_details has updated_by / created_at
DESCRIBE hk_details;

2) Test reconciliation procedure (DB):
-- Run the procedure (on staging/dev first)
CALL sp_reconcile_hk_balances();
-- Check recent entries
SELECT * FROM hk_balance_reconciliations ORDER BY reconciled_at DESC LIMIT 20;
-- Compare totals
SELECT sb.branch_id, sb.hk_item_id, sb.current_balance AS before, (sb.opening_qty + IFNULL((SELECT SUM(received_qty) FROM hk_stock_received sr WHERE sr.branch_id = sb.branch_id AND sr.hk_item_id = sb.hk_item_id),0) - IFNULL((SELECT SUM(consumed_qty) FROM hk_consumptions c WHERE c.branch_id = sb.branch_id AND c.hk_item_id = sb.hk_item_id),0)) AS computed
FROM hk_stock_balances sb
LIMIT 20;

3) API tests

3.1 Upload file via FileUpload::uploadFile
cURL example (auth token required):
curl -X POST -H "Authorization: Bearer <token>" -F "file=@/path/to/file.pdf" -F "hkr_id=36" "https://<host>/api/uploadFile"

Expected: success, and DB row in files contains file metadata and hkr_id = 36

3.2 Create/Update requirement beyond budget (should fail)
- Use existing branch with budget 3000 in `hk_branchwise_budget`.
- Prepare JSON payload where materials sum > 3000 and POST to updateHkRequirement (or createHkRequirement)
Expect: 400 response with message "Total amount ... exceeds branch budget"

3.3 Run reconciliation via API (Admin user)
curl -X POST -H "Authorization: Bearer <admin-token>" "https://<host>/api/runHkReconciliation"
Expect: 200 and 'Reconciliation completed'

4) Edge cases & robustness
- Upload invalid PDF containing /JavaScript — should be rejected by FileUpload::uploadFile
- Ensure existing files with non-numeric hkr_id were set to NULL and retained their file_name
- Ensure ABI of FileModel uses primaryKey = file_id (we updated model) and inserts still work

5) Rollback steps
- If something fails, restore DB from backup
- Re-check application logs for exceptions and fix code or revert migration

If you'd like, I can generate prepared cURL commands and a small node/php script to run these checks automatically against a test environment.