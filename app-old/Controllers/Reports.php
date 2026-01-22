<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\DeptModel;
use App\Services\JwtService;
use App\Models\ReportsModel;

class Reports extends BaseController
{
    use ResponseTrait;

    public function branchQuetions()
    {
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;

        $reportsModel = new ReportsModel();

        $selectedDate = $this->request->getVar('selectedDate');

        $userModel = new UserModel();
        $branchList = $userModel->getUserBranchList($user, $role);

        $results = $reportsModel->getMorningTaskList($selectedDate, $role, $user);
        if ( $results) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'Morning Task list.',
                'data' =>  $results
            ], 200);
        } else {
            log_message('error', 'Failed to  give users cluster list ' . json_encode( $results));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed.'.$user
            ], 500);
        }
    }

    public function branchNightQuetions()
    {
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;

        $reportsModel = new ReportsModel();

        $selectedDate = $this->request->getVar('selectedDate');

        $userModel = new UserModel();
        $branchList = $userModel->getUserBranchList($user, $role);

        $results = $reportsModel->getNightTaskList($selectedDate, $role, $user);
        if ( $results) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'Morning Task list.',
                'data' =>  $results
            ], 200);
        } else {
            log_message('error', 'Failed to  give users cluster list ' . json_encode( $results));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed.'.$user
            ], 500);
        }
    }

    private function validateAuthorization() {
        if ( !class_exists( 'App\Services\JwtService' ) ) {
            log_message( 'error', 'JwtService class not found' );
            return $this->respond( [ 'error' => 'JwtService class not found' ], 500 );
        }
        // Get the Authorization header and log it
        $authorizationHeader = $this->request->getHeader( 'Authorization' ) ? $this->request->getHeader( 'Authorization' )->getValue() : null;
        log_message( 'info', 'Authorization header: ' . $authorizationHeader );

        // Create an instance of JwtService and validate the token
        $jwtService = new JwtService();
        $result = $jwtService->validateToken( $authorizationHeader );

        // Handle token validation errors
        if ( isset( $result[ 'error' ] ) ) {
            log_message( 'error', $result[ 'error' ] );
            return $this->respond( [ 'error' => $result[ 'error' ] ], $result[ 'status' ] );
        }

        // Extract the decoded token and get the USER-ID
        $decodedToken = $result[ 'data' ];
        return $decodedToken;
        // Assuming JWT contains USER-ID

    }


}