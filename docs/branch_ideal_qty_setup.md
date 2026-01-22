Branch-wise Ideal Quantity — Implementation & Verification

Overview
--------
This feature stores per-branch ideal quantity for each HK item in table `hk_branch_ideal_qty` (columns: id, branch_id, hk_item_id, ideal_qty, created_at).

What we added
-------------
- Migration SQL: `db/migrations/20260114_add_constraints_hk_branch_ideal_qty.sql`
  - Adds UNIQUE(branch_id, hk_item_id) index and foreign keys to `hk_items` and `branches`.
- Model: `app/Models/HkBranchIdealQtyModel.php`
  - Methods: getByBranch, getByItem, upsert, deleteById
- Controller: `app/Controllers/HkBranchIdealQtyController.php`
  - Endpoints: `getByBranch`, `getByItem`, `setIdealQty`, `delete`
- Routes: added in `app/Config/Routes.php`
  - GET  `/getHkIdealQtyByBranch/{branchId}`
  - GET  `/getHkIdealQtyByItem/{itemId}`
  - POST `/setHkIdealQty`  (payload: {branch_id, hk_item_id, ideal_qty})
  - DELETE `/deleteHkIdealQty/{id}`
- Frontend: `src/pages/hk_inventory/HkIdealQty.jsx`
  - UI to pick branch and set ideal qty per item; Save All bulk upsert
  - Route: `/hk/ideal-qty` (available in sidebar)
- Service: `src/services/hkService.jsx` updated with new API methods

Migration & Verification
------------------------
1. Run migration on staging:
   - mysql -u user -p database < db/migrations/20260114_add_constraints_hk_branch_ideal_qty.sql

2. Verify no duplicates before applying UNIQUE index (check duplicates query in migration file).

3. After migration, manually validate:
   - GET /api/getHkItems -> items list
   - GET /api/getHkIdealQtyByBranch/{branchId} -> should return existing ideal records
   - POST /api/setHkIdealQty with payload {branch_id, hk_item_id, ideal_qty} -> verify saved
   - GET again to confirm results

Frontend checks
---------------
- Go to HK menu -> "Branch-wise Ideal Qty" (visible in sidebar)
- Select a branch and confirm items load
- Edit values and click Save All
- Verify values persisted by reloading and via API

Testing checklist (manual)
--------------------------
- [ ] Migration executed on staging successfully
- [ ] No duplicate branch+item entries exist
- [ ] Admin can view and edit ideal quantities
- [ ] Attempt to delete an item referenced in hk_branch_ideal_qty returns 409 (protected)
- [ ] Confirm FK constraints behave as expected when deleting branch or item (ON DELETE CASCADE)

Notes
-----
- Ensure `getBranches` endpoint returns suitable branch list objects (id and branch_name).
- Only users with admin roles should have UI access to edit (enforced via middleware + UI checks as needed).

Contact
-------
If you want, I can also add unit tests for the model/controller and an end-to-end test for the frontend flow.