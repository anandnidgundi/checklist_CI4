<?php

namespace App\Controllers;

use App\Models\EmailTemplateModel;
use CodeIgniter\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use CodeIgniter\HTTP\ResponseInterface;
// jwt
use App\Services\JwtService;

class EmailController extends Controller
{
     public function sendTemplate($eventKey = null, $toEmail = null, $toName = null, $data = [], $subjectOverride = null, $ccList = null, $attachments = null)
     {
          // support JSON body call from JS (route email/send)
          // note: method may be invoked programmatically (e.g. from triggerSubmissionEmailOnSave)
          // in that case $this->request can be null so guard accordingly.
          $isPost = false;
          if (isset($this->request) && $this->request !== null) {
               try {
                    $isPost = $this->request->getMethod() === 'post';
               } catch (\Throwable $__ignore) {
                    $isPost = false;
               }
          }
          if ($isPost && $eventKey === null) {
               $json = $this->request->getJSON(true);
               if (is_array($json)) {
                    $eventKey = $json['eventKey'] ?? null;
                    $toEmail = $json['toEmail'] ?? null;
                    $toName  = $json['toName'] ?? null;
                    $data     = $json['data'] ?? [];
                    $subjectOverride = $json['subjectOverride'] ?? null;
                    $ccList   = $json['ccList'] ?? null;
                    $attachments = $json['attachments'] ?? null;
               }
          }
          $templateModel = new EmailTemplateModel();
          $template = $templateModel->getByEventKey($eventKey);
          if (!$template) {
               return 'Template not found';
          }
          // Normalise common aliases so templates get a consistent variables set
          // - branch_manager -> branch_manager_name
          // - centre_name -> branch_name
          // - name -> first preference branch_manager_name, then branch_manager
          if (is_array($data)) {
               if (empty($data['branch_manager_name']) && !empty($data['branch_manager'])) {
                    $data['branch_manager_name'] = preg_replace('/\s*\([^)]*\)$/', '', trim((string)$data['branch_manager']));
               }
               if (empty($data['branch_name']) && !empty($data['centre_name'])) {
                    $data['branch_name'] = trim((string)$data['centre_name']);
               }
               if (empty($data['name'])) {
                    $data['name'] = $data['branch_manager_name'] ?? ($data['branch_manager'] ?? null);
               }

               // audited_by may come from header or payload — normalise to audited_by for templates
               if (empty($data['audited_by']) && !empty($data['auditedBy'])) {
                    $data['audited_by'] = trim((string)$data['auditedBy']);
               }
               if (!empty($data['audited_by'])) {
                    $data['audited_by'] = trim((string)$data['audited_by']);
               }
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

               // if caller requested a checklist PDF attachment, generate it now
               if (!empty($data['checklist_id'])) {
                    try {
                         $cid = (int) $data['checklist_id'];
                         $isMaintenance = false;
                         $resolvedFormId = (int)($data['form_id'] ?? 0);
                         $resolvedFormName = '';
                         $resolutionSource = $resolvedFormId > 0 ? 'payload' : 'none';

                         if ($resolvedFormId <= 0) {
                              $resolvedFormId = (int)($template['form_id'] ?? 0);
                              if ($resolvedFormId > 0) {
                                   $resolutionSource = 'template';
                              }
                         }

                         try {
                              $db = \Config\Database::connect();

                              // 1) primary source: submission row bound to checklist_id
                              $subFormId = 0;
                              $subRow = $db->table('form_submissions')->select('form_id')->where('id', $cid)->get()->getRowArray();
                              $subFormId = (int)($subRow['form_id'] ?? 0);

                              if ($subFormId > 0) {
                                   $resolvedFormId = $subFormId;
                                   $resolutionSource = 'submission';
                              }

                              if ($resolvedFormId > 0) {
                                   $formRow = $db->table('forms')->select('form_name')->where('id', $resolvedFormId)->get()->getRowArray();
                                   $resolvedFormName = strtolower(trim((string)($formRow['form_name'] ?? '')));
                              }
                         } catch (\Throwable $__lookupEx) {
                              // continue with non-DB fallbacks below
                         }

                         if ($resolvedFormName !== '') {
                              $isMaintenance = (strpos($resolvedFormName, 'maintenance') !== false);
                         } else {
                              $pdfType = strtolower(trim((string)($data['pdf_type'] ?? '')));
                              $formName = strtolower(trim((string)($data['form_name'] ?? '')));
                              $flagRaw = $data['is_maintenance_form'] ?? null;
                              $eventKeyLc = strtolower(trim((string)$eventKey));

                              $isMaintenance = ($pdfType === 'maintenance')
                                   || (strpos($formName, 'maintenance') !== false);

                              if (!$isMaintenance && $flagRaw !== null) {
                                   $flag = strtolower(trim((string)$flagRaw));
                                   $isMaintenance = in_array($flag, ['1', 'true', 'yes', 'y'], true);
                              }

                              if (!$isMaintenance) {
                                   if (strpos($eventKeyLc, 'branding') !== false) {
                                        $isMaintenance = false;
                                   } elseif (strpos($eventKeyLc, 'maintenance') !== false) {
                                        $isMaintenance = true;
                                   }
                              }
                         }

                         try {
                              $diag = [
                                   'cid' => $cid,
                                   'event_key' => (string)$eventKey,
                                   'resolution_source' => $resolutionSource,
                                   'resolved_form_id' => $resolvedFormId,
                                   'resolved_form_name' => $resolvedFormName,
                                   'payload_form_id' => (int)($data['form_id'] ?? 0),
                                   'template_form_id' => (int)($template['form_id'] ?? 0),
                                   'payload_pdf_type' => (string)($data['pdf_type'] ?? ''),
                                   'payload_is_maintenance_form' => (string)($data['is_maintenance_form'] ?? ''),
                                   'is_maintenance' => $isMaintenance ? '1' : '0',
                              ];
                              file_put_contents(APPPATH . '../writable/logs/email_send_debug.log', "[" . date('Y-m-d H:i:s') . "] pdf_resolution: " . json_encode($diag) . "\n", FILE_APPEND);
                         } catch (\Throwable $__diagEx) {
                         }

                         if ($isMaintenance) {
                              $pdfBytes = \App\Services\PdfService::buildMaintenancePdf($cid);
                              $mail->addStringAttachment($pdfBytes, "maintenance_{$cid}.pdf");
                         } else {
                              $pdfBytes = \App\Services\PdfService::buildChecklistPdf($cid);
                              $mail->addStringAttachment($pdfBytes, "checklist_{$cid}.pdf");
                         }
                    } catch (\Throwable $e) {
                         log_message('error', 'sendTemplate: failed to attach submission pdf for id ' . ($data['checklist_id'] ?? '') . ' - ' . $e->getMessage());
                    }
               }

               // Resolve CC addresses — prefer explicit $ccList argument, otherwise use template's cc_emails
               $ccs = [];
               if (!empty($ccList)) {
                    if (is_string($ccList)) {
                         $ccs = preg_split('/[,\s;]+/', $ccList);
                    } elseif (is_array($ccList)) {
                         $ccs = $ccList;
                    }
               } elseif (!empty($template['cc_emails'])) {
                    if (is_string($template['cc_emails'])) {
                         $ccs = preg_split('/[,\s;]+/', $template['cc_emails']);
                    } elseif (is_array($template['cc_emails'])) {
                         $ccs = $template['cc_emails'];
                    }
               }

               // Include branch emails passed via the template variables payload so callers can add them (branch_manager_email, branch_email)
               $branchEmails = [];
               if (!empty($data['branch_manager_email'])) {
                    if (is_array($data['branch_manager_email'])) {
                         $branchEmails = array_merge($branchEmails, $data['branch_manager_email']);
                    } else {
                         $branchEmails = array_merge($branchEmails, preg_split('/[,\s;]+/', (string)$data['branch_manager_email']));
                    }
               }
               if (!empty($data['branch_email'])) {
                    if (is_array($data['branch_email'])) {
                         $branchEmails = array_merge($branchEmails, $data['branch_email']);
                    } else {
                         $branchEmails = array_merge($branchEmails, preg_split('/[,\s;]+/', (string)$data['branch_email']));
                    }
               }
               if (!empty($branchEmails)) {
                    foreach ($branchEmails as $be) {
                         $be = trim((string)$be);
                         if ($be !== '') $ccs[] = $be;
                    }
               }

               // normalize, dedupe and validate CC list
               $ccs = array_values(array_filter(array_map('trim', (array)$ccs)));
               $ccs = array_values(array_unique($ccs));

               foreach ($ccs as $cc) {
                    $cc = trim((string)$cc);
                    if ($cc && filter_var($cc, FILTER_VALIDATE_EMAIL)) {
                         $mail->addCC($cc);
                    }
               }

               // Attach files if provided (array or single path)
               if (!empty($attachments)) {
                    $filesToAttach = [];
                    if (is_string($attachments)) {
                         $filesToAttach = [$attachments];
                    } elseif (is_array($attachments)) {
                         $filesToAttach = $attachments;
                    }
                    foreach ($filesToAttach as $f) {
                         $f = (string) $f;
                         if ($f === '') continue;
                         // If a filename was stored (no path), try secure_files dir
                         $path = $f;
                         if (!file_exists($path)) {
                              $candidate = WRITEPATH . 'uploads/secure_files/' . basename($f);
                              if (file_exists($candidate)) $path = $candidate;
                         }
                         if (file_exists($path) && is_readable($path)) {
                              try {
                                   $mail->addAttachment($path, basename($path));
                              } catch (\Throwable $ex) {
                                   // ignore attachment failures — continue sending without this attachment
                                   log_message('error', 'sendTemplate: failed to attach ' . $path . ' - ' . $ex->getMessage());
                              }
                         }
                    }
               }

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

     // GET /debug/email-logs  (admin-only helper)
     public function debugLogs()
     {
          $files = [
               'email_trigger_debug' => APPPATH . '../writable/logs/email_trigger_debug.log',
               'email_send_debug'    => APPPATH . '../writable/logs/email_send_debug.log',
               'email_template_debug' => APPPATH . '../writable/logs/email_template_debug.log',
          ];
          $out = [];
          foreach ($files as $k => $p) {
               if (!file_exists($p)) {
                    $out[$k] = null;
                    continue;
               }
               // return last ~100 lines
               $lines = [];
               $fp = fopen($p, 'r');
               if ($fp) {
                    $pos = -1;
                    $line = '';
                    $count = 0;
                    fseek($fp, 0, SEEK_END);
                    $size = ftell($fp);
                    while ($pos > -$size && $count < 400) {
                         fseek($fp, $pos, SEEK_END);
                         $char = fgetc($fp);
                         if ($char === "\n") {
                              $lines[] = strrev($line);
                              $line = '';
                              $count++;
                         } else {
                              $line .= $char;
                         }
                         $pos--;
                    }
                    if ($line !== '') $lines[] = strrev($line);
                    fclose($fp);
                    $lines = array_reverse($lines);
                    $out[$k] = $lines;
               } else {
                    $out[$k] = null;
               }
          }

          return $this->response->setJSON($out);
     }

     // GET /debug/file/{id} - admin helper to inspect `files` table row for a given id
     public function debugFile($id = null)
     {
          $auth = $this->validateAuthorizationNew();
          if ($auth instanceof \CodeIgniter\HTTP\ResponseInterface) return $auth;

          $id = (int) ($id ?? 0);
          if ($id <= 0) {
               return $this->response->setJSON(['message' => 'invalid id'])->setStatusCode(400);
          }

          try {
               $db = \Config\Database::connect();
               // Avoid querying non-existent columns (some environments use file_id, others f_id or id)
               $cols = [];
               try {
                    $cols = $db->getFieldNames('files');
               } catch (\Throwable $__cols) {
                    $cols = [];
               }

               $qb = $db->table('files')->select('*');
               $added = false;
               $qb->groupStart();
               if (in_array('file_id', $cols, true)) {
                    $qb->where('file_id', $id);
                    $added = true;
               }
               if (in_array('f_id', $cols, true)) {
                    $added ? $qb->orWhere('f_id', $id) : ($qb->where('f_id', $id) && $added = true);
               }
               if (in_array('id', $cols, true)) {
                    $added ? $qb->orWhere('id', $id) : ($qb->where('id', $id) && $added = true);
               }
               $qb->groupEnd();

               if (!$added) {
                    return $this->response->setJSON(['message' => 'files table does not expose id-like columns'])->setStatusCode(500);
               }

               $row = $qb->limit(1)->get()->getRowArray();

               if (!$row) {
                    return $this->response->setJSON(['message' => 'not found'])->setStatusCode(404);
               }

               return $this->response->setJSON($row);
          } catch (\Throwable $e) {
               return $this->response->setJSON(['message' => 'query failed', 'error' => $e->getMessage()])->setStatusCode(500);
          }
     }

     protected function validateAuthorizationNew()
     {

          if (!class_exists('App\\Services\\JwtService')) {
               return $this->response->setJSON(['error' => 'JwtService class not found'])->setStatusCode(500);
          }
          // Avoid nullsafe operator (PHP <8.0) to prevent T_OBJECT_OPERATOR parse errors.
          $authHeaderObj = $this->request->header('Authorization');
          $authorizationHeader = $authHeaderObj ? $authHeaderObj->getValue() : null;
          try {
               $jwtService = new JwtService();
               $result = $jwtService->validateToken($authorizationHeader);
          } catch (\Throwable $e) {
               return $this->response->setJSON(['error' => $e->getMessage()])->setStatusCode(500);
          }

          if (isset($result['error'])) {
               return $this->response->setJSON(['error' => $result['error']])->setStatusCode($result['status'] ?? 401);
          }
          return $result['data'];
     }
}
