<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\BlacklistModel;

class Logout extends ResourceController
{
    protected $blacklistModel;

    public function __construct()
    {
        $this->blacklistModel = new BlacklistModel();
    }

    public function index()
{
    try {
        // Retrieve the token from the Authorization header
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];
        
        // Output the raw authorization header for debugging
       // echo $authorizationHeader; die();

        if ($authorizationHeader) {
            // Check if the token starts with 'Bearer ' and extract the token
            if (strpos($authorizationHeader, 'Bearer ') === 0) {
                // Extract token by removing the 'Bearer ' prefix
                $token = substr($authorizationHeader, 7); // Remove 'Bearer ' prefix
            } else {
                // If it doesn't have the prefix, use it as is (not typical)
                $token = $authorizationHeader;
            }

            // Trim any whitespace from the token
            $token = trim($token);
            
            // Output the extracted token for debugging
           // echo $token; die();

            // Invalidate the token by adding it to the blacklist
            if ($this->blacklistModel->addToken($token)) {
                return $this->respond(['status' => true, 'message' => 'Logged out successfully'], 200);
            } else {
                return $this->respond(['status' => false, 'message' => 'Token already blacklisted'], 409);
            }
        } else {
            return $this->failUnauthorized('Token required');
        }
    } catch (\Exception $e) {
        return $this->fail('An error occurred: ' . $e->getMessage());
    }
}

    
    
}
