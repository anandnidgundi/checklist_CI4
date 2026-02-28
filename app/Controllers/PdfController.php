<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class PdfController extends Controller
{
     /**
      * Render the branding‑checklist identified by $id as a PDF.
      */
     public function checklist(int $id)
     {
          try {
               $output = \App\Services\PdfService::buildChecklistPdf($id);
          } catch (\RuntimeException $e) {
               return redirect()->back()->with('error', $e->getMessage());
          }

          return $this->response
               ->setContentType('application/pdf')
               ->setBody($output);
     }

     /**
      * Render the branding‑checklist identified by $id as a PDF and force download.
      */
     public function checklistDownload(int $id)
     {
          // debug log so we can see which URL actually reached this method
          $uri = $this->request->getUri();
          log_message('error', 'PdfController::checklistDownload invoked, method=' . $this->request->getMethod() . ', path=' . ($uri ? $uri->getPath() : '[none]'));

          // simply build the PDF from the service; no need to query
          // branding_checklists at all now that we use form_submissions.
          try {
               $output = \App\Services\PdfService::buildChecklistPdf($id);
          } catch (\RuntimeException $e) {
               log_message('error', "PdfController::checklistDownload failed to build pdf: {$e->getMessage()}");
               return $this->response->setStatusCode(404)->setBody('Checklist not found');
          }

          // if caller requested inline viewing (e.g. ?inline=1) send appropriate
          // header; otherwise force download as before
          $inline = $this->request->getGet('inline');
          $disposition = $inline ? 'inline' : 'attachment';
          return $this->response
               ->setStatusCode(200)
               ->setContentType('application/pdf')
               ->setHeader('Content-Disposition', "$disposition; filename=\"checklist_{$id}.pdf\"")
               ->setBody($output);
     }
}
