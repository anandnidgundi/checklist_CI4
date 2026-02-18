<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use CodeIgniter\HTTP\ResponseInterface;

class JwtService
{
     private $key;

     public function __construct()
     {
          $key = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? getenv('JWT_SECRET');
          $key = is_string($key) ? trim($key) : '';
          $key = trim($key, " \t\n\r\0\x0B\"'");
          $this->key = $key;

          if ($this->key === '') {
               throw new \RuntimeException('JWT_SECRET is not set or invalid.');
          }
     }

     public function validateToken($authorizationHeader): array
     {
          if (!$authorizationHeader) {
               return ['error' => 'Authorization header required', 'status' => ResponseInterface::HTTP_UNAUTHORIZED];
          }

          // Extract the token
          if (strpos($authorizationHeader, 'Bearer ') === 0) {
               $token = substr($authorizationHeader, 7); // Remove 'Bearer ' prefix
          } else {
               return ['error' => 'Invalid Authorization header format', 'status' => ResponseInterface::HTTP_UNAUTHORIZED];
          }

          try {
               // Decode the JWT token
               $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
               return ['data' => $decoded, 'status' => ResponseInterface::HTTP_OK];
          } catch (ExpiredException $e) {
               return ['error' => 'Token has expired', 'status' => ResponseInterface::HTTP_UNAUTHORIZED];
          } catch (\Exception $e) {
               return ['error' => 'Invalid token: ' . $e->getMessage(), 'status' => ResponseInterface::HTTP_UNAUTHORIZED];
          }
     }
}
