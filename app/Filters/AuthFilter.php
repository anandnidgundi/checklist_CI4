<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key; // Import the Key class
use App\Models\BlacklistModel;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
		
        // Retrieve the Authorization header
        $authorizationHeader = $request->getHeader('Authorization');

        // Ensure the header exists
        if (!$authorizationHeader) {
            return $this->createResponse(401, ['error' => 'Authorization header required']);
        }

        // Extract the token
        $token = substr($authorizationHeader,22); // Correct the offset to 7

        // $blacklistModel = new BlacklistModel();

        // // Check if the token is blacklisted
        // if ($blacklistModel->isBlacklisted($token)) {
        //     return $this->createResponse(401, ['error' => 'Token has been invalidated']);
        // }

        try {
            $key = getenv('JWT_SECRET'); // Ensure your JWT_SECRET is set
            JWT::decode($token, new Key($key, 'HS256')); // Use Key for decoding
        } catch (\Exception $e) {
            return $this->createResponse(401, ['error' => 'Invalid token']);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here if needed
    }

    private function createResponse($statusCode, $data)
    {
        // Use the response service instead of instantiating a new Response object
        return service('response')->setStatusCode($statusCode)->setJSON($data);
    }
}
