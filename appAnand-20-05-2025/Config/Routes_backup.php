<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes 
 */
$routes->GET('/', 'Home::index');
$routes->GET('viewAttachment/(:any)', 'FileUpload::viewAttachment/$1');

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

    $routes->match(['GET', 'options'], "getDieselConsumptionList/(:segment)", "DieselConsumption::getDieselConsumptionList/$1", ['filter' => 'authFilter']);
    $routes->match(['GET', 'options'], "getDieselConsumptionById/(:segment)", "DieselConsumption::getDieselConsumptionById/$1", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "addDieselConsumption", "DieselConsumption::addDieselConsumption", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "editDieselConsumption/(:segment)", "DieselConsumption::editDieselConsumption/$1", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "deleteDieselConsumption", "DieselConsumption::deleteDieselConsumption", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "getDieselConsumptionAdminList/(:segment)", "DieselConsumption::getDieselConsumptionAdminList/$1", ['filter' => 'authFilter']);

    $routes->match(['GET', 'options'], "getPowerConsumptionList/(:segment)", "PowerConsumption::getPowerConsumptionList/$1", ['filter' => 'authFilter']);
    $routes->match(['GET', 'options'], "getPowerConsumptionById/(:segment)", "PowerConsumption::getPowerConsumptionById/$1", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "addPowerConsumption", "PowerConsumption::addPowerConsumption", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "editPowerConsumption/(:segment)", "PowerConsumption::editPowerConsumption/$1", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "deletePowerConsumption", "PowerConsumption::deletePowerConsumption", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "getPowerConsumptionAdminList/(:segment)", "PowerConsumption::getPowerConsumptionAdminList/$1", ['filter' => 'authFilter']);

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


    $routes->match(['POST', 'options'], "getZoneClusterBranchesTree", "User::getZoneClusterBranchesTree", ['filter' => 'authFilter']);
    $routes->match(['GET', 'options'], "getUserZones", "User::getUserZones", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "getClusterBranchList", "User::getClusterBranchList", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "getZoneClusterList", "User::getZoneClusterList", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "addClusterToZone", "User::addClusterToZone", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "addBranchToCluster", "User::addBranchToCluster", ['filter' => 'authFilter']);
    $routes->match(['POST', 'options'], "BM_DashboardCount", "User::BM_DashboardCount", ['filter' => 'authFilter']); 
    $routes->match(['POST', 'options'], "CM_DashboardCount", "User::CM_DashboardCount", ['filter' => 'authFilter']);
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
    
    
    
    
    
    
});
