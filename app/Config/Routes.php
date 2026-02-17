<?php

use CodeIgniter\Router\RouteCollection;

/** 
 * @var RouteCollection $routes  
 */
$routes->GET('/', 'Home::index');
$routes->GET('viewAttachment/(:any)', 'FileUpload::viewAttachment/$1');
$routes->GET('viewAttachmentNew/(:any)', 'FileUpload::viewAttachmentNew/$1');
// Backwards-compatible routes for deployments where the CI app is under a 'backend' folder
$routes->GET('backend/viewAttachment/(:any)', 'FileUpload::viewAttachment/$1');
$routes->GET('backend/viewAttachmentNew/(:any)', 'FileUpload::viewAttachmentNew/$1');
$routes->group("api", ['filter' => 'cors:api'], function ($routes) {
     $routes->POST("register", "Register::index");
     $routes->match(['POST', 'options'], "login", "Login::index");
     $routes->match(['POST', 'options'], "checkUser", "Login::checkUser");
     $routes->match(['GET', 'options'], "profile", "Profile::index", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "logout", "Logout::index"); // Removed 'api/' prefix here 
     $routes->match(['POST', 'options'], "uploadFile", "FileUpload::uploadFile", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getFiles", "FileUpload::getFiles", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "download/(:segment)", "FileUpload::download/$1", ['filter' => 'authFilter']);



     $routes->match(['POST', 'options'], "addBM_Task", "BM_Tasks::addBM_Task", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBM_TaskDetails/(:segment)", "BM_Tasks::getBM_TaskDetails/$1", ['filter' => 'authFilter']);


     $routes->match(['POST', 'options'], "editBM_Task/(:segment)", "BM_Tasks::editBM_Task/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBM_TaskList", "BM_Tasks::getBM_TaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBranchComboTaskListNew", "BM_Tasks::getBranchComboTaskListNew", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getBM_TaskDetailsByMid/(:segment)", "BM_Tasks::getBM_TaskDetailsByMid/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBM_TaskListForAdmin", "BM_Tasks::getBM_TaskListForAdmin", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBM_TaskListForAdminforbranch", "BM_Tasks::getBM_TaskListForAdminforbranch", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getDieselConsumptionList/(:segment)", "DieselConsumption::getDieselConsumptionList/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getDieselConsumptionById/(:segment)", "DieselConsumption::getDieselConsumptionById/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addDieselConsumption", "DieselConsumption::addDieselConsumption", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editDieselConsumption/(:segment)", "DieselConsumption::editDieselConsumption/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteDieselConsumption", "DieselConsumption::deleteDieselConsumption", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getDieselConsumptionAdminList/(:segment)", "DieselConsumption::getDieselConsumptionAdminList/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getDieselConsumptionAdminListforbranch/(:segment)", "DieselConsumption::getDieselConsumptionAdminListforbranch/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getPowerConsumptionList/(:segment)", "PowerConsumption::getPowerConsumptionList/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getPowerConsumptionById/(:segment)", "PowerConsumption::getPowerConsumptionById/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addPowerConsumption", "PowerConsumption::addPowerConsumption", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editPowerConsumption/(:segment)", "PowerConsumption::editPowerConsumption/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deletePowerConsumption", "PowerConsumption::deletePowerConsumption", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getPowerConsumptionAdminList/(:segment)", "PowerConsumption::getPowerConsumptionAdminList/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getPowerConsumptionAdminListforbranch/(:segment)", "PowerConsumption::getPowerConsumptionAdminListforbranch/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addPowerConsumptionNew", "PowerConsumption::addPowerConsumptionNew", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addMorningTask", "Morningtask::addMorningTask", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getMorningTaskDetails", "Morningtask::getMorningTaskDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "saveMorningTaskDetails", "Morningtask::saveMorningTaskDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "uploadedMTlist", "Morningtask::uploadedMTlist", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBranchMorningTaskList", "Morningtask::getBranchMorningTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getMorningTaskDetailsByMid", "Morningtask::getMorningTaskDetailsByMid", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBranchComboTaskList", "Morningtask::getBranchComboTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addCmMorningTask", "CmMorningTask::addCmMorningTask", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCmMorningTaskDetails", "CmMorningTask::getCmMorningTaskDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCmMorningTaskDetailsNew", "CmMorningTask::getCmMorningTaskDetailsNew", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "saveCm_morningtaskDetails", "CmMorningTask::saveCm_morningtaskDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "uploadedCmMTtask", "CmMorningTask::uploadedCmMTtask", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCmMorningTaskList", "CmMorningTask::getCmMorningTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBmcMorningTaskList", "CmMorningTask::getBmcMorningTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCMUserBranchList", "CmMorningTask::getCMUserBranchList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCMUserBranchListDetails", "CmMorningTask::getCMUserBranchListDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCMBranchComboTaskList", "CmMorningTask::getCMBranchComboTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getZ_BranchWeeklyList", "CmMorningTask::getZ_BranchWeeklyList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getZonalManagerBranchList", "CmMorningTask::getZonalManagerBranchList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBm_Z_MorningTaskList", "CmMorningTask::getBm_Z_MorningTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCm_Z_MorningTaskList", "CmMorningTask::getCm_Z_MorningTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBmcWeeklyTaskList", "CmMorningTask::getBmcWeeklyTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCM_BranchMorningTaskList", "CmMorningTask::getCM_BranchMorningTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addNightTask", "Nighttask::addNightTask", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getNightTaskDetails", "Nighttask::getNightTaskDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getNightTaskDetailsNew", "Nighttask::getNightTaskDetailsNew", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "saveNightTaskDetails", "Nighttask::saveNightTaskDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "uploadedNightlist", "Nighttask::uploadedNightlist", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBranchNightTaskList", "Nighttask::getBranchNightTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getDocData", "Nighttask::getDocData", ['filter' => 'authFilter']);
     // $routes->match(['POST', 'options'],"getMriData", "Nighttask::getMriData", ['filter' => 'authFilter']);
     // $routes->match(['POST', 'options'],"getCtData", "Nighttask::getCtData", ['filter' => 'authFilter']);
     // $routes->match(['POST', 'options'],"getUsgData", "Nighttask::getUsgData", ['filter' => 'authFilter']);
     // $routes->match(['POST', 'options'],"getXrayData", "Nighttask::getXrayData", ['filter' => 'authFilter']);
     // $routes->match(['POST', 'options'],"getCardioTmtData", "Nighttask::getCardioTmtData", ['filter' => 'authFilter']);
     // $routes->match(['POST', 'options'],"getCardioEcgData", "Nighttask::getCardioEcgData", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBmWeeklyTaskList", "BMweeklyTask::getBmWeeklyTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "createBmWeeklyTask", "BMweeklyTask::createBmWeeklyTask", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBmWeeklyTask", "BMweeklyTask::getBmWeeklyTask", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateBmWeeklyTask", "BMweeklyTask::updateBmWeeklyTask", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addCmNightTask", "Cm_nighttask::addCmNightTask", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCm_nightTaskDetails", "Cm_nighttask::getCm_nightTaskDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "saveCm_nightTaskDetails", "Cm_nighttask::saveCm_nightTaskDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "uploadedCm_nightlist", "Cm_nighttask::uploadedCm_nightlist", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCmNightTaskList", "Cm_nighttask::getCmNightTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCmNightTaskDetailsNew", "Cm_nighttask::getCmNightTaskDetailsNew", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBmcNightTaskList", "Cm_nighttask::getBmcNightTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "saveCmNightTaskDetails", "Cm_nighttask::saveCmNightTaskDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBm_Z_NightTaskList", "Cm_nighttask::getBm_Z_NightTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCm_Z_NightTaskList", "Cm_nighttask::getCm_Z_NightTaskList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCM_BranchNightTaskList", "Cm_nighttask::getCM_BranchNightTaskList", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "users", "User::index", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getempcodes", "User::getEmpCodes", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "changeEmpPass", "User::changeEmpPass", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "changeMyPass", "User::changeMyPass", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getUsersList", "User::getUsersList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addUser", "User::addUser", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editUser", "User::editUser", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteUser", "User::deleteUser", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "resetPass", "User::resetPass", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addRoleToEmp", "User::addRoleToEmp", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addAreaToEmp", "User::addAreaToEmp", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addBranchOrClusterToEmp", "User::addBranchOrClusterToEmp", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCMclusterList", "User::getCMclusterList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getUserBranchList", "User::getUserBranchList", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getUserwiseBranchClusterZoneList", "User::getUserwiseBranchClusterZoneList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getZoneClusterBranchesTree", "User::getZoneClusterBranchesTree", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getUserZones", "User::getUserZones", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getClusterBranchList", "User::getClusterBranchList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getZoneClusterList", "User::getZoneClusterList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addClusterToZone", "User::addClusterToZone", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addBranchToCluster", "User::addBranchToCluster", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "BM_DashboardCount", "User::BM_DashboardCount", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "CM_DashboardCount", "DashboardController::CM_DashboardCount", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getLatestTasksByBranch", "DashboardController::getLatestTasksByBranch", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "checkToken", "User::checkToken", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getUserBranchClusterZoneList", "User::getUserBranchClusterZoneList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getDepts", "Home::getDeptWithCat", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editDept", "Home::editDept", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addDept", "Home::addDept", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteDept", "Home::deleteDept", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getArea", "Home::getArea");
     $routes->match(['POST', 'options'], "addArea", "Home::addArea", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editArea", "Home::editArea", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteArea", "Home::deleteArea", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getRoles", "Home::getRoles", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getZones", "Home::getZones", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addZone", "Home::addZone", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editZone", "Home::editZone", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteZone", "Home::deleteZone", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getClusters", "Home::getAllCluster", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editCluster", "Home::editCluster", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteCluster", "Home::deleteCluster", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "clusterMapping", "Home::clusterMapping", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getBranchDetails", "Home::getBranchDetails", ['filter' => 'authFilter']);
     // New: detailed branch info (uses secondary DB when available)
     $routes->match(['GET', 'options'], "branch/(:num)", "BranchController::getBranchDetails/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editBranchDetails", "Home::editBranchDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addNewBranch", "Home::addNewBranch", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteBranch", "Home::deleteBranch", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getManagers", "Home::getManagers", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addNewManager", "Home::addNewManager", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editManager", "Home::editManager", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteManager", "Home::deleteManager", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getCategory", "Home::getCategory", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getTechnicians", "Home::getTechnicians", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addNewTechnician", "Home::addNewTechnician", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editTechnician", "Home::editTechnician", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteTechnician", "Home::deleteTechnician", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getAssets", "Home::getAssets", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addNewAssets", "Home::addNewAssets", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editAssets", "Home::editAssets", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteAssets", "Home::deleteAssets", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getServiceManager", "Home::getServiceManager", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addServiceManager", "Home::addServiceManager", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editServiceManager", "Home::editServiceManager", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteServiceManager", "Home::deleteServiceManager", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getEquipments", "Home::getEquipments", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addEquipments", "Home::addEquipments", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editEquipments", "Home::editEquipments", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteEquipments", "Home::deleteEquipments", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "dashboardCount", "Home::DashboardCount", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getAssetDetails", "Home::getAssetDetails", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "branchwiseComplaints", "Reports::branchwiseComplaints", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deptwiseComplaints", "Reports::deptwiseComplaints", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "statuswiseComplaints", "Reports::statuswiseComplaints", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "branchQuetions", "Reports::branchQuetions", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "branchNightQuetions", "Reports::branchNightQuetions", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "changeMyPass1", "User::changeMyPass1");
     $routes->match(['GET', 'options'], "getpassword", "User::getpassword");
     $routes->match(['POST', 'options'], "deleteBranchOrClusterFromEmp", "User::deleteBranchOrClusterFromEmp", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "removeBranchFromCluster", "User::removeBranchFromCluster", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getClusters_New", "Home::getClusters", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addNewCluster", "Home::addNewCluster", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getClusterByid/(:num)", "Home::getClusterByid/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getBranches", "Home::getBranches", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateCluster/(:num)", "Home::updateCluster/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteClusterbYiD/(:num)", "Home::deleteClusterbYiD/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addNewCluster", "Home::addNewCluster", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "saveCluster", "Home::saveCluster", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getZonalByid/(:num)", "Home::getZonalByid/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateZonal/(:num)", "Home::updateZonal/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getUsers", "User::getUsers", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getZonals", "Home::getZonals", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "assignZoneToEmployee", "Home::assignZoneToEmployee", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getUserBranchList_new", "Home::getUserBranchList_new", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getUserMap/(:num)", "Home::getUserMap/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getEmpBranches", "Home::getEmpBranches", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "avpdashboardCount", "Home::avpdashboardCount", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getMatchedBranches/(:num)", "Home::getMatchedBranches/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getClustersWithBranches", "Home::getClustersWithBranches", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getYesterdaysReading", "Home::getYesterdaysReading", ['filter' => 'authFilter']);
     // routes for VendorMaster  
     $routes->match(['POST', 'options'], "createVendor", "VendorMaster::createVendor", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getVendorById/(:num)", "VendorMaster::getVendorById/$1", ['filter' => 'authFilter']);
     $routes->match(['PUT', 'options'], "updateVendor/(:num)", "VendorMaster::updateVendor/$1", ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], "deleteVendor/(:num)", "VendorMaster::deleteVendor/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getVendorList", "VendorMaster::getVendorList", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getVendorByBranchId/(:num)", "VendorMaster::getVendorByBranchId/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getVendorForPestControlByBranchId/(:num)", "VendorMaster::getVendorForPestControlByBranchId/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getVendorForElevationCleaningByBranchId/(:num)", "VendorMaster::getVendorForElevationCleaningByBranchId/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getVendorForWaterTankCleaningByBranchId/(:num)", "VendorMaster::getVendorForWaterTankCleaningByBranchId/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getBranchListMappedWithVendor/(:num)", "VendorMaster::getBranchListMappedWithVendor/$1", ['filter' => 'authFilter']);
     // routes for VisitMaster
     $routes->match(['POST', 'options'], "createVisit", "VisitMaster::createVisit", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getVisitList", "VisitMaster::getVisitList", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getVisitById/(:num)", "VisitMaster::getVisitById/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateVisit/(:num)", "VisitMaster::updateVisit/$1", ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], "deleteVisit/(:num)", "VisitMaster::deleteVisit/$1", ['filter' => 'authFilter']);

     //routes for PestControl
     $routes->match(['POST', 'options'], "createPestControl", "PestControl::createPestControl", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getPestControlList", "PestControl::getPestControlList", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getPestControlById/(:num)", "PestControl::getPestControlById/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updatePestControl/(:num)", "PestControl::updatePestControl/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deletePestControl", "PestControl::deletePestControl", ['filter' => 'authFilter']);
     // routes for ServiceMaster
     $routes->match(['POST', 'options'], "createServices", "ServicesMaster::createServices", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getServicesList", "ServicesMaster::getServicesList", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getServicesListForAdmin/(:segment)", "ServicesMaster::getServicesListForAdmin/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getUsersServicesList", "ServicesMaster::getUsersServicesList", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getServicesById/(:num)", "ServicesMaster::getServicesById/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateServices/(:num)", "ServicesMaster::updateServices/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteServices", "ServicesMaster::deleteServices", ['filter' => 'authFilter']);
     // routes for PowerMeterMaster
     $routes->match(['POST', 'options'], "createPowerMeter", "PowerMeterMaster::createPowerMeter", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getPowerMeterList", "PowerMeterMaster::getPowerMeterList", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getPowerMeterById/(:num)", "PowerMeterMaster::getPowerMeterById/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updatePowerMeter/(:num)", "PowerMeterMaster::updatePowerMeter/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deletePowerMeter", "PowerMeterMaster::deletePowerMeter", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getPowerMeterByBranchId/(:num)", "PowerMeterMaster::getPowerMeterByBranchId/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getPreviousLastDateMeterData/(:num)", "PowerConsumption::getPreviousLastDateMeterData/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getPowerMeterDataByPcId/(:num)", "PowerConsumption::getPowerMeterDataByPcId/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updatePowerConsumption/(:num)", "PowerConsumption::updatePowerConsumption/$1", ['filter' => 'authFilter']);

     // HK Requirements Management Routes
     $routes->match(['POST', 'options'], "createHkRequirement", "HKRequirements::createHkRequirement", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getHkRequirements", "HKRequirements::getHkRequirements", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getHkRequirementDetails/(:num)", "HKRequirements::getHkRequirementDetails/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateHkRequirementStatus/(:num)", "HKRequirements::updateHkRequirementStatus/$1", ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], "deleteHkRequirement/(:num)", "HKRequirements::deleteHkRequirement/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getBranchBudget/(:num)", "HKRequirements::getBranchBudget/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getMyHkRequirements", "HKRequirements::getMyHkRequirements", ['filter' => 'authFilter']);

     // Monthly Indents
     $routes->match(['POST', 'options'], "createMonthlyIndent", "MonthlyIndentController::create", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateMonthlyIndent/(:num)", "MonthlyIndentController::update/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getMonthlyIndents", "MonthlyIndentController::list", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getMonthlyIndent/(:num)", "MonthlyIndentController::get/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "approveMonthlyIndent/(:num)", "MonthlyIndentController::approve/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "rejectMonthlyIndent/(:num)", "MonthlyIndentController::reject/$1", ['filter' => 'authFilter']);

     // Key Reports (admin roles only)
     $routes->match(['GET', 'options'], "keyReports/branchConsumption", "KeyReportsController::branchConsumption", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "keyReports/itemMonthlyConsumption", "KeyReportsController::itemMonthlyConsumption", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "keyReports/idealVsActual", "KeyReportsController::idealVsActual", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "keyReports/stockBalance", "KeyReportsController::stockBalance", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "keyReports/indentsStatus", "KeyReportsController::indentsStatus", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "keyReports/branchVisitCycle", "KeyReportsController::branchVisitCycle", ['filter' => 'authFilter']);

     // Stock Receipts
     $routes->match(['POST', 'options'], "createStockReceipt", "StockReceiptController::create", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getStockReceipts", "StockReceiptController::list", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getStockReceipt/(:num)", "StockReceiptController::get/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "createBranchBudget", "HKRequirements::createBranchBudget", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getAllBranchBudgets", "HKRequirements::getAllBranchBudgets", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateBranchBudget/(:num)", "HKRequirements::updateBranchBudget/$1", ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], "deleteBranchBudget/(:num)", "HKRequirements::deleteBranchBudget/$1", ['filter' => 'authFilter']);

     $routes->match(['POST', 'options'], "updateHkRequirement/(:num)", "HKRequirements::updateHkRequirement/$1", ['filter' => 'authFilter']);
     // Admin-triggerable reconciliation job (runs stored procedure to recompute stock balances)
     $routes->match(['POST', 'options'], "runHkReconciliation", "HKRequirements::runReconciliation", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "getHkRequirementDetailsByMonthAndBranch", "HKRequirements::getHkRequirementDetailsByMonthAndBranch/$1/$2", ['filter' => 'authFilter']);

     // Housekeeping Materials CRUD Routes
     $routes->match(['GET', 'options'], "getHkMaterials", "Hkmaterials::getHkMaterials", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getHkMaterial/(:num)", "Hkmaterials::getHkMaterialById/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "createHkMaterial", "Hkmaterials::createHkMaterial", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateHkMaterial/(:num)", "Hkmaterials::updateHkMaterial/$1", ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], "deleteHkMaterial/(:num)", "Hkmaterials::deleteHkMaterial/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "searchHkMaterials", "Hkmaterials::searchHkMaterials", ['filter' => 'authFilter']);

     // Routes for HK Inventory Management

     // HK Inventory Management Routes (new)
     $routes->match(['GET', 'options'], "getHkItems", "HkItemsController::index", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getHkItemsByType/(:segment)", "HkItemsController::getByType/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getHkItem/(:num)", "HkItemsController::show/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "createHkItem", "HkItemsController::create", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateHkItem/(:num)", "HkItemsController::update/$1", ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], "deleteHkItem/(:num)", "HkItemsController::delete/$1", ['filter' => 'authFilter']);

     // Branch-wise ideal qty (HK Inventory)
     $routes->match(['GET', 'options'], "getHkIdealQtyByBranch/(:num)", "HkBranchIdealQtyController::getByBranch/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getHkIdealQtyByItem/(:num)", "HkBranchIdealQtyController::getByItem/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "setHkIdealQty", "HkBranchIdealQtyController::setIdealQty", ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], "deleteHkIdealQty/(:num)", "HkBranchIdealQtyController::delete/$1", ['filter' => 'authFilter']);
     // Admin check: find hk_branch_ideal_qty rows with missing branches
     $routes->match(['GET', 'options'], "checkHkIdealOrphans", "HkBranchIdealQtyController::listOrphans", ['filter' => 'authFilter']);

     // HK Supervisors (user_map CRUD)
     $routes->match(['GET', 'options'], "getHkSupervisors", "HkSupervisors::index", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getHkSupervisor/(:num)", "HkSupervisors::show/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "createHkSupervisor", "HkSupervisors::create", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateHkSupervisor/(:num)", "HkSupervisors::update/$1", ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], "deleteHkSupervisor/(:num)", "HkSupervisors::delete/$1", ['filter' => 'authFilter']);
     // Get branches assigned to an HK supervisor (returns branch records)
     $routes->match(['GET', 'options'], "getHkBranchesByEmpCode/(:segment)", "HkSupervisors::getHkBranchesByEmpCode/$1", ['filter' => 'authFilter']);

     // Stock routes
     $routes->match(['POST', 'options'], "addOpeningStock", "StockController::addOpeningStock", ['filter' => 'authFilter']);

     $routes->match(['GET', 'options'], "getOpeningStock/(:num)", "StockController::getOpeningStock/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "recordStockReceipt", "StockController::recordReceipt", ['filter' => 'authFilter']);
     // balance endpoints: branch only or branch + item
     $routes->match(['GET', 'options'], "getStockBalance/(:num)", "StockController::getBalance/$1", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getStockBalance/(:num)/(:num)", "StockController::getBalance/$1/$2", ['filter' => 'authFilter']);

     // Consumption routes
     $routes->match(['POST', 'options'], "recordCycleConsumption", "ConsumptionController::recordCycleConsumption", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "recordCycleConsumption", "ConsumptionController::recordCycleConsumption", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getCycleConsumption/(:num)/(:segment)", "ConsumptionController::getCycle/$1/$2", ['filter' => 'authFilter']);

     // WhatsApp user type management
     $routes->match(['GET', 'options'], "getWhatsappTypes", "WhatsappUsersController::index", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getWhatsappType/(:num)", "WhatsappUsersController::show/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "createWhatsappType", "WhatsappUsersController::create", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateWhatsappType/(:num)", "WhatsappUsersController::update/$1", ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], "deleteWhatsappType/(:num)", "WhatsappUsersController::delete/$1", ['filter' => 'authFilter']);

     // Manage users inside types
     $routes->match(['GET', 'options'], "getWhatsappUsersForType/(:num)", "WhatsappUsersController::getUsersForType/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addWhatsappUserToType", "WhatsappUsersController::addUserToType", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "removeWhatsappUserFromType", "WhatsappUsersController::removeUserFromType", ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], "removeWhatsappUserById/(:num)", "WhatsappUsersController::removeUserById/$1", ['filter' => 'authFilter']);

     // Manage Branding routes

     $routes->match(['GET', 'options'], 'branding-checklist/list', 'BrandingChecklistController::list', ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], 'branding-checklist', 'BrandingChecklistController::create', ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], 'branding-checklist/(:num)', 'BrandingChecklistController::show/$1', ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], 'branding-checklist/(:num)', 'BrandingChecklistController::update/$1', ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], 'branding-checklist/(:num)', 'BrandingChecklistController::delete/$1', ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], 'branding-checklist/(:num)/photos', 'BrandingChecklistController::uploadPhoto/$1', ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], 'branding-checklist/(:num)/photos', 'BrandingChecklistController::photos/$1', ['filter' => 'authFilter']);
     // branch manager lookup (accept GET, POST and OPTIONS to support varied client calls)
     $routes->match(['GET', 'POST', 'options'], 'branding-checklist/branch-manager/(:num)', 'BrandingChecklistController::branchManager/$1', ['filter' => 'authFilter']);     // employee lookup by emp_code
     $routes->match(['GET', 'options'], 'branding-checklist/employee/(:segment)', 'BrandingChecklistController::employee/$1', ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], 'branding-checklist/sections', 'BrandingChecklistController::sections', ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], 'branding-checklist/sections', 'BrandingChecklistController::createSection', ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], 'branding-checklist/subsections', 'BrandingChecklistController::subSections', ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], 'branding-checklist/subsections', 'BrandingChecklistController::createSubSection', ['filter' => 'authFilter']);

     // Branding dashboard counts (aggregate metrics) used by front-end dashboards
     $routes->match(['POST', 'options'], "Branding_DashboardCount", "BrandingChecklistController::dashboardCount", ['filter' => 'authFilter']);

     // VDC Forms
     $routes->match(['POST', 'options'], "createForm", "FormsController::createForm", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getFormsList", "FormsController::getFormsList", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getFormById/(:num)", "FormsController::getFormById/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "updateForm/(:num)", "FormsController::updateForm/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteForm", "FormsController::deleteForm", ['filter' => 'authFilter']);

     // New Department
     $routes->match(['GET', 'options'], "getNewDepartments", "NewDepartmentController::getNewDepartments", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addNewDepartment", "NewDepartmentController::addNewDepartment", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editNewDepartment/(:num)", "NewDepartmentController::editNewDepartment/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteNewDepartment/(:num)", "NewDepartmentController::deleteNewDepartment/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteNewDepartment", "NewDepartmentController::deleteNewDepartment", ['filter' => 'authFilter']);

     // Form Inputs
     $routes->match(['GET', 'options'], "getFormInputs", "FormInputsController::getFormInputs", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getFormInputById/(:num)", "FormInputsController::getFormInputById/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addFormInput", "FormInputsController::addFormInput", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editFormInput/(:num)", "FormInputsController::editFormInput/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteFormInput/(:num)", "FormInputsController::deleteFormInput/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteFormInput", "FormInputsController::deleteFormInput", ['filter' => 'authFilter']);

     // Form Sections
     $routes->match(['GET', 'options'], "getFormSections", "FormSectionsController::getFormSections", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getFormSectionById/(:num)", "FormSectionsController::getFormSectionById/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addFormSection", "FormSectionsController::addFormSection", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editFormSection/(:num)", "FormSectionsController::editFormSection/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteFormSection/(:num)", "FormSectionsController::deleteFormSection/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteFormSection", "FormSectionsController::deleteFormSection", ['filter' => 'authFilter']);

     // Form Sub Sections
     $routes->match(['GET', 'options'], "getFormSubSections", "FormSubSectionsController::getFormSubSections", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "getFormSubSectionById/(:num)", "FormSubSectionsController::getFormSubSectionById/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "addFormSubSection", "FormSubSectionsController::addFormSubSection", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "editFormSubSection/(:num)", "FormSubSectionsController::editFormSubSection/$1", ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], "deleteFormSubSection/(:num)", "FormSubSectionsController::deleteFormSubSection/$1", ['filter' => 'authFilter']);

     // Dynamic Form Records
     $routes->match(['POST', 'options'], 'dynamic-form/save', 'DynamicFormController::save', ['filter' => 'authFilter']);
     // Unified upload/list endpoints for dynamic-form-managed attachments (used by modern clients)
     $routes->match(['POST', 'options'], 'dynamic-form/(:num)/photos', 'DynamicFormController::uploadPhoto/$1', ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], 'dynamic-form/(:num)/photos', 'DynamicFormController::photos/$1', ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], 'dynamic-form/list', 'DynamicFormController::list', ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], 'dynamic-form/create', 'DynamicFormController::create', ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], 'dynamic-form/update/(:segment)', 'DynamicFormController::update/$1', ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], 'dynamic-form/show/(:segment)', 'DynamicFormController::show/$1', ['filter' => 'authFilter']);

     // Email Templates (CRUD + render preview)
     $routes->match(['GET', 'options'], 'email-templates', 'EmailTemplateController::index', ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], 'email-templates/(:num)', 'EmailTemplateController::show/$1', ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], 'email-templates', 'EmailTemplateController::create', ['filter' => 'authFilter']);
     $routes->match(['PUT', 'PATCH', 'options'], 'email-templates/(:num)', 'EmailTemplateController::update/$1', ['filter' => 'authFilter']);
     $routes->match(['DELETE', 'options'], 'email-templates/(:num)', 'EmailTemplateController::delete/$1', ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], 'email-templates/render/(:segment)', 'EmailTemplateController::render/$1', ['filter' => 'authFilter']);
     $routes->match(['POST', 'options'], 'email-templates/send/(:segment)', 'EmailTemplateController::send/$1', ['filter' => 'authFilter']);

     // Logs API (admin)
     $routes->match(['POST', 'options'], "logs", "LogsController::addLog", ['filter' => 'authFilter']);
     $routes->match(['GET', 'options'], "logs", "LogsController::list", ['filter' => 'authFilter']);
});
