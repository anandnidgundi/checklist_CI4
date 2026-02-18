<?php

namespace App\Controllers;

use App\Models\EmailTemplateModel;
use CodeIgniter\RESTful\ResourceController;

class EmailTemplateController extends ResourceController
{
     protected $modelName = 'App\\Models\\EmailTemplateModel';
     protected $format    = 'json';

     // GET /email-templates
     public function index()
     {
          return $this->respond($this->model->findAll());
     }

     // GET /email-templates/{id}
     public function show($id = null)
     {
          $data = $this->model->find($id);
          if (!$data) return $this->failNotFound('Template not found');
          return $this->respond($data);
     }

     // POST /email-templates
     public function create()
     {
          $data = $this->request->getJSON(true);

          // DEBUG: log incoming payload and insertion result (temporary)
          try {
               $debug = "[" . date('Y-m-d H:i:s') . "] Incoming email-template create payload:\n" . print_r($data, true) . "\n";
               file_put_contents(APPPATH . '../writable/logs/email_template_debug.log', $debug, FILE_APPEND);
          } catch (\Throwable $e) {
               // ignore logging failures
          }

          $inserted = $this->model->insert($data);

          try {
               $debug = "[" . date('Y-m-d H:i:s') . "] Insert result: " . var_export($inserted, true) . "\n";
               if (!empty($this->model->errors())) {
                    $debug .= "Errors: " . print_r($this->model->errors(), true) . "\n";
               }
               file_put_contents(APPPATH . '../writable/logs/email_template_debug.log', $debug, FILE_APPEND);
          } catch (\Throwable $e) {
               // ignore
          }

          if ($inserted) {
               return $this->respondCreated($data);
          }
          return $this->failValidationErrors($this->model->errors());
     }

     // PUT/PATCH /email-templates/{id}
     public function update($id = null)
     {
          $data = $this->request->getJSON(true);
          if ($this->model->update($id, $data)) {
               return $this->respond($data);
          }
          return $this->failValidationErrors($this->model->errors());
     }

     // DELETE /email-templates/{id}
     public function delete($id = null)
     {
          if ($this->model->delete($id)) {
               return $this->respondDeleted(['id' => $id]);
          }
          return $this->failNotFound('Template not found');
     }

     // POST /email-templates/render/{event_key}
     public function render($event_key = null)
     {
          $data = $this->request->getJSON(true);
          $template = $this->model->getByEventKey($event_key);
          if (!$template) return $this->failNotFound('Template not found');
          $html = $this->replaceVariables($template['html_template'], $data);
          $subject = $this->replaceVariables($template['subject'], $data);
          return $this->respond(['subject' => $subject, 'body' => $html]);
     }

     // POST /email-templates/send/{event_key}
     // Payload: { recipients: ["a@x.com" | {email,name}], variables: { ... }, subject?: "override" }
     public function send($event_key = null)
     {
          $payload = $this->request->getJSON(true);
          $recipients = $payload['recipients'] ?? [];
          $variables = $payload['variables'] ?? [];
          $subjectOverride = $payload['subject'] ?? null;

          if (empty($recipients) || !is_array($recipients)) {
               return $this->failValidationErrors(['recipients' => 'Provide an array of recipient emails or objects']);
          }

          $template = $this->model->getByEventKey($event_key);
          if (!$template) return $this->failNotFound('Template not found');

          $emailController = new \App\Controllers\EmailController();
          $results = [];

          foreach ($recipients as $r) {
               $toEmail = '';
               $toName = '';
               if (is_array($r)) {
                    $toEmail = $r['email'] ?? '';
                    $toName = $r['name'] ?? '';
               } else {
                    $toEmail = (string) $r;
               }

               if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
                    $results[] = ['email' => $toEmail, 'status' => 'invalid', 'message' => 'Invalid email'];
                    continue;
               }

               // If subject override provided, temporarily replace the template subject
               if ($subjectOverride) {
                    $origSubject = $template['subject'];
                    $template['subject'] = $subjectOverride;
               }

               $res = $emailController->sendTemplate($event_key, $toEmail, $toName, $variables ?? [], $subjectOverride ?? null);

               $ok = is_string($res) && stripos($res, 'Email sent') !== false;
               $results[] = ['email' => $toEmail, 'status' => $ok ? 'sent' : 'failed', 'message' => $res];
          }

          return $this->respond(['results' => $results]);
     }

     private function replaceVariables($template, $data)
     {
          return preg_replace_callback('/{{\s*(\w+)\s*}}/', function ($matches) use ($data) {
               $key = $matches[1];
               return isset($data[$key]) ? $data[$key] : $matches[0];
          }, $template);
     }
}
