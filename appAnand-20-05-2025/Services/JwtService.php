<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class JwtService
{
    private $key;
    protected $algorithm;
    // public function __construct()
    // {
    //     $this->key = getenv('JWT_SECRET'); // Ensure your JWT_SECRET is set
    //     if (empty($this->key)) {
    //         throw new \Exception('4e9f2c0a4f1d3b8e6a2c4b7d8e9f0c1a2f4b6d7e8a9c0f1e2d3b5c7a8d9f0e1');
    //     }        
    // }

    public function __construct()
    {
        // Make sure to retrieve a valid string key from environment or config
        $this->key = getenv('JWT_SECRET_KEY') ?: '4e9f2c0a4f1d3b8e6a2c4b7d8e9f0c1a2f4b6d7e8a9c0f1e2d3b5c7a8d9f0e1'; // Never use default in production
        $this->algorithm = 'HS256';
    }

    public function validateToken($authorizationHeader)
    {
        // If no Authorization header or not in correct format
        if (!$authorizationHeader || !preg_match('/Bearer\s+(.*)$/i', $authorizationHeader, $matches)) {
            return [
                'error' => 'Invalid or missing token',
                'status' => 401
            ];
        }

        $token = $matches[1];

        try {
            // Ensure $this->key is a non-empty string
            if (empty($this->key) || !is_string($this->key)) {
                return [
                    'error' => 'Invalid JWT secret key configuration',
                    'status' => 500
                ];
            }

            // Decode the JWT using the key
            $decoded = JWT::decode($token, new Key($this->key, $this->algorithm));

            return [
                'data' => $decoded
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Invalid token: ' . $e->getMessage(),
                'status' => 401
            ];
        }
    }

    public function createToken($data, $expiration = 3600)
    {
        $issuedAt = time();
        $payload = [
            'iat' => $issuedAt,
            'exp' => $issuedAt + $expiration,
            'data' => $data
        ];

        return JWT::encode($payload, $this->key, $this->algorithm);
    }


    // public function validateToken($authorizationHeader): array
    // {
    //     if (!$authorizationHeader) {
    //         return ['error' => 'Authorization header required', 'status' => ResponseInterface::HTTP_UNAUTHORIZED];
    //     }

    //     // Extract the token
    //     if (strpos($authorizationHeader, 'Bearer ') === 0) {
    //         $token = substr($authorizationHeader, 7); // Remove 'Bearer ' prefix
    //     } else {
    //         return ['error' => 'Invalid Authorization header format', 'status' => ResponseInterface::HTTP_UNAUTHORIZED];
    //     }

    //     try {
    //         // Decode the JWT token
    //         $decoded = JWT::decode($token, new Key($this->key, 'HS256'));
    //         return ['data' => $decoded, 'status' => ResponseInterface::HTTP_OK];
    //     } catch (ExpiredException $e) {
    //         return ['error' => 'Token has expired', 'status' => ResponseInterface::HTTP_UNAUTHORIZED];
    //     } catch (\Exception $e) {
    //         return ['error' => 'Invalid token: ' . $e->getMessage(), 'status' => ResponseInterface::HTTP_UNAUTHORIZED];
    //     }
    // }
}
