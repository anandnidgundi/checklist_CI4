<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Services\JwtService;

header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: POST,GET, OPTIONS");
header("Access-Control-Allow-Headers: *");
header('Access-Control-Allow-Credentials: true');
/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
     /**
      * Instance of the main Request object.
      *
      * @var CLIRequest|IncomingRequest
      */
     protected $request;

     /**
      * An array of helpers to be loaded automatically upon
      * class instantiation. These helpers will be available
      * to all other controllers that extend BaseController.
      *
      * @var list<string>
      */
     protected $helpers = [];

     /**
      * Be sure to declare properties for any property fetch you initialized.
      * The creation of dynamic property is deprecated in PHP 8.2.
      */
     // protected $session;

     /**
      * @return void
      */
     public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
     {
          // Do Not Edit This Line
          parent::initController($request, $response, $logger);

          // Preload any models, libraries, etc, here.

          // E.g.: $this->session = \Config\Services::session();
     }

     // protected function validateAuthorization()
     // {
     //      $headers = $this->request->getHeaders();
     //      if (!isset($headers['Authorization'])) {
     //           return $this->response->setStatusCode(401, 'Unauthorized')->setBody('Missing Authorization header')->send();
     //      }
     //      $auth = $headers['Authorization']->getValue();
     //      if ($auth !== 'Bearer mysecrettoken') {
     //           return $this->response->setStatusCode(403, 'Forbidden')->setBody('Invalid token')->send();
     //      }
     // }

     protected function validateAuthorizationNew()
     {

          if (!class_exists('App\\Services\\JwtService')) {
               return $this->response->setJSON(['error' => 'JwtService class not found'])->setStatusCode(500);
          }
          // Avoid nullsafe operator (PHP <8.0) to prevent T_OBJECT_OPERATOR parse errors.
          $authHeaderObj = $this->request->header('Authorization');
          $authorizationHeader = $authHeaderObj ? $authHeaderObj->getValue() : null;
          $jwtService = new JwtService();
          $result = $jwtService->validateToken($authorizationHeader);

          if (isset($result['error'])) {
               return $this->response->setJSON(['error' => $result['error']])->setStatusCode($result['status'] ?? 401);
          }
          return $result['data'];
     }
}
