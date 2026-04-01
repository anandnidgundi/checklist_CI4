<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class PdfController extends Controller
{
     /**
      * Downloadable PDF representation of a lab weekly checklist entry.
      */
     public function labWeeklyDownload(int $id)
     {
          $uri = $this->request->getUri();
          log_message('error', 'PdfController::labWeeklyDownload invoked, method=' . $this->request->getMethod() . ', path=' . ($uri ? $uri->getPath() : '[none]'));

          try {
               $output = \App\Services\PdfService::buildLabWeeklyPdf($id);
          } catch (\RuntimeException $e) {
               log_message('error', "PdfController::labWeeklyDownload failed: {$e->getMessage()}");
               return $this->response->setStatusCode(404)->setBody('Lab weekly checklist entry not found');
          }

          $inline = $this->request->getGet('inline');
          $disposition = $inline ? 'inline' : 'attachment';
          return $this->response
               ->setStatusCode(200)
               ->setContentType('application/pdf')
               ->setHeader('Content-Disposition', "$disposition; filename=\"lab_weekly_{$id}.pdf\"")
               ->setBody($output);
     }


     /**
      * Downloadable PDF representation of a phlebotomy checklist entry.
      */
     public function phlebotomyDownload(int $id)
     {
          $uri = $this->request->getUri();
          log_message('error', 'PdfController::phlebotomyDownload invoked, method=' . $this->request->getMethod() . ', path=' . ($uri ? $uri->getPath() : '[none]'));

          try {
               $output = \App\Services\PdfService::buildPhlebotomyPdf($id);
          } catch (\RuntimeException $e) {
               log_message('error', "PdfController::phlebotomyDownload failed: {$e->getMessage()}");
               return $this->response->setStatusCode(404)->setBody('Phlebotomy checklist entry not found');
          }

          $inline = $this->request->getGet('inline');
          $disposition = $inline ? 'inline' : 'attachment';
          return $this->response
               ->setStatusCode(200)
               ->setContentType('application/pdf')
               ->setHeader('Content-Disposition', "$disposition; filename=\"phlebotomy_{$id}.pdf\"")
               ->setBody($output);
     }
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

     /**
      * Downloadable PDF representation of a maintenance entry.
      */
     public function maintenanceDownload(int $id)
     {
          $uri = $this->request->getUri();
          log_message('error', 'PdfController::maintenanceDownload invoked, method=' . $this->request->getMethod() . ', path=' . ($uri ? $uri->getPath() : '[none]'));

          try {
               $output = \App\Services\PdfService::buildMaintenancePdf($id);
          } catch (\RuntimeException $e) {
               log_message('error', "PdfController::maintenanceDownload failed: {$e->getMessage()}");
               return $this->response->setStatusCode(404)->setBody('Maintenance entry not found');
          }

          $inline = $this->request->getGet('inline');
          $disposition = $inline ? 'inline' : 'attachment';
          return $this->response
               ->setStatusCode(200)
               ->setContentType('application/pdf')
               ->setHeader('Content-Disposition', "$disposition; filename=\"maintenance_{$id}.pdf\"")
               ->setBody($output);
     }

     /**
      * Downloadable PDF representation of an IT checklist entry.
      */
     public function itDownload(int $id)
     {
          $uri = $this->request->getUri();
          log_message('error', 'PdfController::itDownload invoked, method=' . $this->request->getMethod() . ', path=' . ($uri ? $uri->getPath() : '[none]'));

          try {
               $output = \App\Services\PdfService::buildItPdf($id);
          } catch (\RuntimeException $e) {
               log_message('error', "PdfController::itDownload failed: {$e->getMessage()}");
               return $this->response->setStatusCode(404)->setBody('IT checklist entry not found');
          }

          $inline = $this->request->getGet('inline');
          $disposition = $inline ? 'inline' : 'attachment';
          return $this->response
               ->setStatusCode(200)
               ->setContentType('application/pdf')
               ->setHeader('Content-Disposition', "$disposition; filename=\"it_checklist_{$id}.pdf\"")
               ->setBody($output);
     }
}
