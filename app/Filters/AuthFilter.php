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

          // Extract the token from the 'Authorization' header (expected format: "Bearer <token>")
          $authValue = is_object($authorizationHeader) ? $authorizationHeader->getValue() : (string) $authorizationHeader;
          if (!preg_match('/Bearer\s+(\S+)/i', $authValue, $matches)) {
               return $this->createResponse(401, ['error' => 'Bearer token required']);
          }
          $token = $matches[1];

          // $blacklistModel = new BlacklistModel();

          // // Check if the token is blacklisted
          // if ($blacklistModel->isBlacklisted($token)) {
          //     return $this->createResponse(401, ['error' => 'Token has been invalidated']);
          // }

          try {
               // Ensure JWT secret is available and is a string
               $key = getenv('JWT_SECRET'); // Ensure your JWT_SECRET is set
               if (empty($key) || !is_string($key)) {
                    // Log the configuration error for server-side debugging
                    log_message('error', 'JWT_SECRET is not set or invalid.');
                    return $this->createResponse(500, ['error' => 'Server misconfiguration: JWT secret not configured']);
               }

               JWT::decode($token, new Key($key, 'HS256')); // Use Key for decoding
          } catch (\Firebase\JWT\ExpiredException $e) {
               return $this->createResponse(401, ['error' => 'Token expired']);
          } catch (\Exception $e) {
               // Log the exception for debugging
               log_message('error', 'JWT decode error: ' . $e->getMessage());
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
