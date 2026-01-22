<?php

namespace App\Controllers;

use App\Models\WhatsappModel;

class WhatsappController extends BaseController
{

     public function createWhatsappMessageForAdmin($forMonth, $branchName, $total_amount)
     {
          $whatsappModel = new WhatsappModel();
          $allAdmins = $whatsappModel->getAdminDetails();
          log_message('error', 'Admin Details: ' . print_r($allAdmins, true));

          // for each admin send whatsapp message
          foreach ($allAdmins as $admin) {
               $mobile = $admin['mobile'];
               $emp_code = $admin['emp_code'];

               $message = "HK Requirement Submitted for $forMonth\nBranch: $branchName\nTotal Amount: ₹" . number_format($total_amount, 2);

               $data = [
                    'mobile' => (string) $mobile,
                    'emp_code' => $emp_code,
                    'message' => $message,
                    'sent_dtm' => date('Y-m-d H:i:s'),
                    'hkr_id' => null,
                    'remark' => 'HK Requirement Submission Notification'
               ];

               $whatsappModel->insert($data);

               $messageSent = $this->sendWhatsappMessage($mobile, $message);
               if ($messageSent) {
                    log_message('error', "WhatsApp message sent to Admin: $emp_code, Mobile: $mobile");
               } else {
                    log_message('error', "Failed to send WhatsApp message to Admin: $emp_code, Mobile: $mobile");
               }
          }

          // Return after the loop completes
          return true;
     }

     public function createWhatsappMessageForBM($forMonth, $branchName, $status, $emp_code)
     {

          $whatsappModel = new WhatsappModel();
          $bm = $whatsappModel->getBMDetails($emp_code);
          if (!empty($bm)) {
               $mobile = $bm['mobile'];
               $emp_code = $bm['emp_code'];
               $message = "HK Requirement is $status for $forMonth\nBranch: $branchName";

               $data = [
                    'mobile' => $mobile,
                    'emp_code' => $emp_code,
                    'message' => $message,
                    'sent_dtm' => date('Y-m-d H:i:s'),
                    'hkr_id' => null,
                    'remark' => 'HK Requirement Status Notification'
               ];

               $whatsappModel->insert($data);

               $this->sendWhatsappMessage($mobile, $message);

               return true;
          }
     }




     public function sendWhatsappMessage($mobile, $message)
     {

          // prepare whatsapp message payload
          $dept = "Admin";
          $type = "HK Requirement Submission";
          $date = date('d-m-Y');
          $pay_type = "One-Time";

          $payload = [
               "from" => "919121999111",
               "to" => $mobile,
               "type" => "template",
               "message" => [
                    "templateid" => "785633",
                    "placeholders" => [
                         $dept,
                         $type,
                         $date,
                         $pay_type
                    ]
               ]
          ];

          // log payload
          log_message('error', 'WhatsApp Payload: ' . json_encode($payload));


          // $curl = curl_init();

          // curl_setopt_array($curl, [
          //      CURLOPT_URL => 'https://api.pinbot.ai/v1/wamessage/sendMessage',
          //      CURLOPT_RETURNTRANSFER => true,
          //      CURLOPT_ENCODING => '',
          //      CURLOPT_MAXREDIRS => 10,
          //      CURLOPT_TIMEOUT => 0,
          //      CURLOPT_FOLLOWLOCATION => true,
          //      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          //      CURLOPT_CUSTOMREQUEST => 'POST',
          //      CURLOPT_POSTFIELDS => json_encode($payload),
          //      CURLOPT_HTTPHEADER => [
          //           'apikey: 9b664354-6b20-11ed-a7c7-9606c7e32d76',
          //           'Content-Type: application/json'
          //      ],
          // ]);

          // $response = curl_exec($curl);

          // if (curl_errno($curl)) {
          //      echo 'cURL error: ' . curl_error($curl);
          //      log_message('error', 'WhatsApp Failed: ' . json_encode($payload));
          //      log_message('error', 'WhatsApp Failed Response: ' . $response);
          // } else {
          //      echo $response;
          // }

          // curl_close($curl);

          return true;
     }
}
