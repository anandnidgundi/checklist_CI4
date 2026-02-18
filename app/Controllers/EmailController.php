<?php

namespace App\Controllers;

use App\Models\EmailTemplateModel;
use CodeIgniter\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailController extends Controller
{
     public function sendTemplate($eventKey, $toEmail, $toName, $data = [], $subjectOverride = null)
     {
          $templateModel = new EmailTemplateModel();
          $template = $templateModel->getByEventKey($eventKey);
          if (!$template) {
               return 'Template not found';
          }
          $subject = $this->replaceVariables($template['subject'], $data);
          $body = $this->replaceVariables($template['html_template'], $data);

          // allow caller to override subject — apply variable replacement on the override as well
          if (!empty($subjectOverride)) {
               $subject = $this->replaceVariables($subjectOverride, $data);
          }

          // Load SMTP credentials from .env
          $mailUsername = getenv('SMTP_USERNAME') ?: 'emailappsmtp.4f7bbead59206e3e';
          $mailPassword = getenv('SMTP_PASSWORD') ?: '4f7bbead59206e3e_LB7cVCgqGGv9';

          $mail = new PHPMailer(true);
          try {
               $mail->isSMTP();
               $mail->Host       = 'smtp.zatpatmail.com';
               $mail->SMTPAuth   = true;
               $mail->Username   = $mailUsername;
               $mail->Password   = $mailPassword;
               $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
               $mail->Port       = 587;
               $mail->setFrom('info@vijayadiagnostic.in', 'Vijaya Diagnostic Centre');
               $mail->addAddress($toEmail, $toName);
               $mail->isHTML(true);
               $mail->Subject = $subject;
               $mail->Body    = $body;
               $mail->AltBody = strip_tags($body);

               // DEBUG: log final subject/body preview and recipient before sending (no credentials)
               try {
                    $log  = "[" . date('Y-m-d H:i:s') . "] sendTemplate: event_key={$eventKey} to={$toEmail} subject=" . str_replace(["\n", "\r"], ' ', $subject) . "\n";
                    $log .= "data=" . json_encode($data) . "\n";
                    $log .= "body_preview=" . substr(strip_tags($body), 0, 2000) . "\n\n";
                    file_put_contents(APPPATH . '../writable/logs/email_send_debug.log', $log, FILE_APPEND);
               } catch (\Throwable $logEx) {
                    // ignore logging failures
               }

               $mail->send();

               // record success
               try {
                    file_put_contents(APPPATH . '../writable/logs/email_send_debug.log', "[" . date('Y-m-d H:i:s') . "] sendTemplate: sent to={$toEmail} SUCCESS\n", FILE_APPEND);
               } catch (\Throwable $logEx) {
               }

               return 'Email sent';
          } catch (Exception $e) {
               // record failure
               try {
                    file_put_contents(APPPATH . '../writable/logs/email_send_debug.log', "[" . date('Y-m-d H:i:s') . "] sendTemplate: to={$toEmail} FAILED: {$mail->ErrorInfo}\n", FILE_APPEND);
               } catch (\Throwable $logEx) {
               }

               return "Email failed: {$mail->ErrorInfo}";
          }
     }

     private function replaceVariables($template, $data)
     {
          return preg_replace_callback('/{{\s*(\w+)\s*}}/', function ($matches) use ($data) {
               $key = $matches[1];
               return isset($data[$key]) ? $data[$key] : $matches[0];
          }, $template);
     }
}
