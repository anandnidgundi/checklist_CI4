<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Cors implements FilterInterface
{
     public function before(RequestInterface $request, $arguments = null)
     {
          // Specify allowed origins
          $allowedOrigins = [
               'http://localhost:3000',
               'http://localhost:4000',
               'https://checklist.vdcapp.in',
               'http://192.168.10.68:4000',
               'http://192.168.10.68:3000',
          ];

          // Check the request origin
          $origin = $request->getHeader('Origin') ? $request->getHeader('Origin')->getValue() : '';

          // Set headers if the origin is allowed
          if (in_array($origin, $allowedOrigins)) {
               header("Access-Control-Allow-Origin: $origin");
               header("Access-Control-Allow-Credentials: true");
          }

          header("Content-Type: application/json; charset=UTF-8");
          header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Requested-Method, Authorization");
          header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, PUT, DELETE");
          header('Access-Control-Max-Age: 86400');

          if ($_SERVER['REQUEST_METHOD'] == "OPTIONS") {
               header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PATCH, PUT, DELETE");
               header('Access-Control-Max-Age: 86400');
               header('Content-Length: 0');
               header('Content-Type: application/json; charset=UTF-8');
               exit();
          }
     }

     public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
     {
          // Avoid setting headers multiple times
     }
}
