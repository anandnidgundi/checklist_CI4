<?php
//create ElevationCleaning.php in app/Controllers


namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController; 
use App\Models\FileModel;
use App\Services\JwtService;
use App\Models\ElevationCleaningModel;

 
    
class ElevationCleaning extends ResourceController
{
    protected $elevationCleaningModel;

    public function __construct()
    {
        $this->elevationCleaningModel = new ElevationCleaningModel();
    }

    public function createElevationCleaning() {
        
    }

    private function validateAuthorization()
    {
        if (!class_exists('App\Services\JwtService')) {
            ////log_message( 'error', 'JwtService class not found' );
            return $this->respond(['error' => 'JwtService class not found'], 500);
        }
        // Get the Authorization header and log it
        $authorizationHeader = $this->request->header('Authorization')?->getValue();
        ////log_message( 'info', 'Authorization header: ' . $authorizationHeader );

        // Create an instance of JwtService and validate the token
        $jwtService = new JwtService();
        $result = $jwtService->validateToken($authorizationHeader);

        // Handle token validation errors
        if (isset($result['error'])) {
            ////log_message( 'error', $result[ 'error' ] );
            return $this->respond(['error' => $result['error']], $result['status']);
        }

        // Extract the decoded token and get the USER-ID
        $decodedToken = $result['data'];
        return $decodedToken;
        // Assuming JWT contains USER-ID

    }

}
