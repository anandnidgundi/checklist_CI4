<?php

/**
 * Lightweight stub for static analysis / IDEs only.
 * This file is intentionally minimal and wrapped in a class_exists check so
 * it never conflicts with the real CodeIgniter test classes at runtime.
 *
 * Place: tests/_support ensures IDEs and static analyzers find it, but
 * CodeIgniter's real runtime class (from vendor/) will be used when running tests.
 */

namespace CodeIgniter\Test;

if (!class_exists('\CodeIgniter\Test\FeatureTestCase')) {
     /**
      * @method $this withBody(string $body)
      * @method $this withHeaders(array $headers)
      * @method $this withCookies(array $cookies)
      * @method \CodeIgniter\HTTP\ResponseInterface get(string $uri)
      * @method \CodeIgniter\HTTP\ResponseInterface post(string $uri)
      * @method \CodeIgniter\HTTP\ResponseInterface call(string $method, string $uri, array $params = [], array $cookies = [], array $files = [], array $server = [], $content = null)
      */
     abstract class FeatureTestCase extends \PHPUnit\Framework\TestCase
     {
          // Intentionally empty: present only so static analyzers and IDEs
          // recognize the class and its inheritance. Real implementation is
          // provided by CodeIgniter's testing package at runtime.
     }
}
