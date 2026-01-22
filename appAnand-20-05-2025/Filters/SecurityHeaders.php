<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class SecurityHeaders implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // No action needed before request
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Set security headers
        $response->setHeader('X-Frame-Options', 'DENY');
        $response->setHeader('Content-Security-Policy', "frame-ancestors 'none'");

        return $response;
    }
}

 