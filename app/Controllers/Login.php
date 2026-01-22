<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use \Firebase\JWT\JWT;
use CodeIgniter\HTTP\UserAgent;


class Login extends BaseController
{
     use ResponseTrait;

     public function index()
     {

          $userModel = new UserModel();

          $db2 = \Config\Database::connect('secondary');
          $emp_code = $this->request->getVar('emp_code');

          $password = $this->decryptPassword($this->request->getVar('password'));

          log_message('error', 'Password ' . $password);
          $agentClass = new UserAgent();

          if ($agentClass->isBrowser()) {
               $agent = $agentClass->getBrowser() . ' ' . $agentClass->getVersion();
          } elseif ($agentClass->isRobot()) {
               $agent = $agentClass->getRobot();
          } elseif ($agentClass->isMobile()) {
               $agent = $agentClass->getMobile();
          } else {
               $agent = 'Unidentified User Agent';
          }

 


          $currentIpAddress = $this->request->getIPAddress();
          // log_message('error', 'Login attempt for ' . $emp_code . ' from ' . $currentIpAddress);
          // Retrieve user details

          $user = $userModel->getUserDetails($emp_code);

          // echo "<pre>";
          // print_r($user->validity);exit();

          $currentDate = date('Y-m-d');
          $validityDate = $user->validity;


          if (isset($validityDate) && (strtotime($currentDate) > strtotime($validityDate))) {
               return $this->respond(['message' => 'Your Validity has expired.'], 401);
          }


          $active = $user->active;
          if (isset($active) && ($active != 'Active')) {
               return $this->respond(['message' => 'Your account has expired.'], 401);
          }

          $exit_date = $user->exit_date;

          if (($exit_date != '0000-00-00') && (strtotime($currentDate) >= strtotime($exit_date))) {

               return $this->respond(['message' => 'Your account has expired.'], 401);
          }


          if (!empty($user)) {
               $role = $user->role;
               if ($role === NULL) {
                    return $this->respond(['message' => 'Sorry, You are not authorized to use this App. Contact to IT Team'], 401);
               }
          } else {
               return $this->respond(['message' => 'Invalid username or password.'], 401);
          }

          if (!$user) {
               return $this->respond(['message' => 'Invalid username or password.'], 401);
          }
          $today = date('Y-m-d');
          if (!empty($user) && $user->active == 'Inactive') {
               return $this->respond(['message' => 'User is Inactive. Please contact IT Dept.'], 401);
          }

          if (!empty($user) && $user->validity === NULL) {
               $message = 'User validity is expired on ' . date('d-m-Y') . ' Please contact IT Dept.';
               return $this->respond(['message' => $message], 401);
          } else if (!empty($user) && $user->validity <  $today && $user->role != 'SUPER_ADMIN') {
               $message = 'User validity is expired on ' . date('d-m-Y', strtotime($user->validity)) . ' Please contact IT Dept.';
               return $this->respond(['message' => $message], 401);
          }


          // if ($user->check_list === 'N') {
          //     return $this->respond( [ 'message' => 'Sorry, You are not authorized to use this App. Contact to IT Team.' ], 401 );
          // }
          // Validate the password

          $db2 = \Config\Database::connect('secondary');
          $builder = $db2->table('new_emp_master'); // Replace 'users' with your actual table name

          if (md5($password) !== $user->password) {
               $failedAttempts = $user->failed_attempts + 1;

               if ($failedAttempts >= 5) {
                    // Disable the account after 5 failed attempts
                    $builder->where('emp_code', $user->emp_code)
                         ->update(['disabled' => 'Y']);

                    return $this->respond(['message' => 'Your account has been disabled due to too many failed login attempts.'], 401);
               } else {
                    // Update the failed attempts count
                    $builder->where('emp_code', $user->emp_code)
                         ->update(['failed_attempts' => (string)$failedAttempts]);
               }

               return $this->respond(['message' => 'Invalid username or password.'], 401);
          }
          $builder->where('emp_code', $user->emp_code)
               ->update(['failed_attempts' => '0']);

          // Retrieve JWT secret key
          $key = getenv('JWT_SECRET');
          if (!$key) {
               return $this->respond(['error' => 'JWT secret key not found.'], 500);
          }

          $iat = time();
          $exp = $iat + (13 * 3600);
          // 13-hour expiration

          $payload = [
               'iss' => 'Issuer of the JWT',
               'aud' => 'Audience of the JWT',
               'sub' => $emp_code,
               'iat' => $iat,
               'exp' => $exp,
               'emp_code' => $user->emp_code,
               'role' => $user->role,

               // 'isAdmin'=> $user->isAdmin
          ];

          //   log_message('error', 'Payload '. json_encode($payload));

          $token = JWT::encode($payload, $key, 'HS256');
          if (!$token) {
               return $this->respond(['error' => 'Failed to generate JWT.'], 500);
          } else {
               $data = [
                    'ip_address' => $currentIpAddress,
                    'user_agent' => $agent,
                    'logged_in_time' => date('Y-m-d H:i:s'),
                    'emp_code' => $emp_code,
                    'user_name' => $user->fname . ' ' . $user->lname,
                    'token' => $token,
               ];
               $insert = $userModel->insertLoginData($data);
          }
          //  if($user->role == 'SUPER_ADMIN' || $user->role == 'ADMIN' || $user->role == 'BM' || $user->role == 'CM'){
          return $this->respond([
               'status' => true,
               'message' => 'Login Successful',
               'token' => $token,
               'role' => $user->role,
          ], 200);
     }

     function decryptPassword($encryptedPassword)
     {
          $secretKey = "1234567890123456"; // 16-byte key
          $data = base64_decode($encryptedPassword);

          if (!$data) {
               error_log("Decryption failed: Base64 decoding error");
               return false;
          }

          list($ivHex, $cipherTextBase64) = explode(":", $data);

          if (strlen($ivHex) !== 32) { // IV should be 16 bytes (32 hex chars)
               error_log("🚨 Invalid IV length: " . strlen($ivHex));
               return false;
          }

          $iv = hex2bin($ivHex);
          $cipherText = base64_decode($cipherTextBase64);

          return openssl_decrypt($cipherText, "AES-128-CBC", $secretKey, OPENSSL_RAW_DATA, $iv);
     }





     public function checkUser()
     {
          $userModel = new UserModel();
          $emp_code = $this->request->getVar('emp_code');
          $area = $userModel->getUsersAreaList($emp_code);

          if ($area) {
               return $this->respond([
                    'status' => true,
                    'message' => 'User has Area List',
                    'data' => $area,
               ], 200);
          } else {
               return $this->respond([
                    'status' => false,
                    'message' => 'Access not approved. Please contact IT Dept.',
               ], 401);
          }
     }
}
