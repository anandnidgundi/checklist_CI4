<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\TicketsModel;
use App\Models\TicketHistoryModel;
use App\Models\UserModel;
use App\Models\ReportsModel;
use App\Models\AmcModel;
use App\Services\JwtService;

class Amc extends BaseController
 {
    use ResponseTrait;

    public function Amclist()
 {
        $amcModel = new AmcModel();
        // Now accessing as an object
        $userDetails = $this->validateAuthorization();
        // Adjusted for object access
        $role = $userDetails->AC_TYPE;
        $USER_ID = $userDetails-> {
            'USER-ID'}
            ;

            $data = $amcModel->getAmcData( $role, $USER_ID );

            if ( $data ) {
                return $this->respond( [ 'status' => true, 'data' => $data, 'message' => 'Amc lists generated successfully.' ], 200 );
            } else {
                return $this->respond( [ 'status' => false, 'message' => 'Amc lists details not found' ], 404 );
            }
        }

        public function addAmc()
 {
            $amcModel = new AmcModel();
            $userDetails = $this->validateAuthorization();
            // Adjusted for array access
            $role = $userDetails[ 'AC_TYPE' ];
            $USER_ID = $userDetails->USER_ID;

            // Handle File Upload
            $imageFile = $this->request->getFile( 'image' );
            $iname1 = '';

            if ( $imageFile && !$imageFile->hasMoved() ) {
                // Generate unique filename
                $currentTime = time();
                $iname1 = $currentTime . $imageFile->getClientName();
                $imageFile->move( 'uploads', $iname1 );
                // Move the file to 'upload' directory
            }

            // Data to be inserted
            $data = [
                'equipment'         => $this->request->getPost( 'equipment' ),
                'type_service'      => $this->request->getPost( 'type_service' ),
                'frequency'         => $this->request->getPost( 'frequency' ),
                'service_date'      => $this->request->getPost( 'service_date' ),
                'next_service_date' => $this->request->getPost( 'next_service_date' ),
                'image'             => $iname1,  // Use the uploaded image name
                'created_by'        => $USER_ID,
                'created_date'      => date( 'Y-m-d' ),
                'vendor_name'       => $this->request->getPost( 'vendor_name' ),
                'remarks'           => $this->request->getPost( 'remarks' ),
                'branch'            => $this->request->getPost( 'branch' )
            ];

            if ( $amcModel->addAmc( $data ) ) {
                return $this->respond( [ 'status' => true, 'data' => $data, 'message' => 'Amc added successfully.' ], 200 );
            } else {
                return $this->respond( [ 'status' => false, 'message' => 'Amc   Details not found' ], 404 );
            }

        }

        private function validateAuthorization()
 {
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

            // Extract the decoded token and get the USER_ID
            $decodedToken = $result[ 'data' ];
            return $decodedToken;
            // Assuming JWT contains USER_ID

        }

    }