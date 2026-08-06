<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class PdfController extends Controller
{
     // pest control pdf

     public function pestControlPdfDownload(int $id)
     {
          try {
               $pdfBinary = \App\Services\PdfService::buildPestControlPdf($id);

               $inline = $this->request->getGet('inline');
               $filename = "pest_control_{$id}.pdf";

               return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader(
                         'Content-Disposition',
                         ($inline ? 'inline' : 'attachment') . "; filename=\"{$filename}\""
                    )
                    ->setBody($pdfBinary);
          } catch (\Throwable $e) {

               log_message('error', $e->getMessage());

               return $this->response->setStatusCode(500)
                    ->setBody('PDF generation failed');
          }
     }

     // facade cleaning pdf

     public function facadeCleaningPdfDownload(int $id)
     {
          try {
               $pdfBinary = \App\Services\PdfService::buildFacadeCleaningPdf($id);

               $inline = $this->request->getGet('inline');
               $filename = "facade_cleaning_{$id}.pdf";

               return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader(
                         'Content-Disposition',
                         ($inline ? 'inline' : 'attachment') . "; filename=\"{$filename}\""
                    )
                    ->setBody($pdfBinary);
          } catch (\Throwable $e) {

               log_message('error', $e->getMessage());

               return $this->response->setStatusCode(500)
                    ->setBody('PDF generation failed');
          }
     }


     // Water Tank Cleaning Pdf

     public function waterTankCleaningPdfDownload(int $id)
     {
          try {
               $pdfBinary = \App\Services\PdfService::buildWaterTankCleaningPdf($id);

               $inline = $this->request->getGet('inline');
               $filename = "water_tank_cleaning_{$id}.pdf";

               return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader(
                         'Content-Disposition',
                         ($inline ? 'inline' : 'attachment') . "; filename=\"{$filename}\""
                    )
                    ->setBody($pdfBinary);
          } catch (\Throwable $e) {

               log_message('error', $e->getMessage());

               return $this->response->setStatusCode(500)
                    ->setBody('PDF generation failed');
          }
     }


     // Drinking Water pdf

     public function drinkingWaterPdfDownload(int $id)
     {
          try {
               $pdfBinary = \App\Services\PdfService::buildDrinkingWaterPdf($id);

               $inline = $this->request->getGet('inline');
               $filename = "drinking_water_{$id}.pdf";

               return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader(
                         'Content-Disposition',
                         ($inline ? 'inline' : 'attachment') . "; filename=\"{$filename}\""
                    )
                    ->setBody($pdfBinary);
          } catch (\Throwable $e) {

               log_message('error', $e->getMessage());

               return $this->response->setStatusCode(500)
                    ->setBody('PDF generation failed');
          }
     }

     // bmw pdf

     public function bmwPdfDownload(int $id)
     {
          try {
               $pdfBinary = \App\Services\PdfService::buildBmwPdf($id);

               $inline = $this->request->getGet('inline');
               $filename = "bmw_{$id}.pdf";

               return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader(
                         'Content-Disposition',
                         ($inline ? 'inline' : 'attachment') . "; filename=\"{$filename}\""
                    )
                    ->setBody($pdfBinary);
          } catch (\Throwable $e) {

               log_message('error', $e->getMessage());

               return $this->response->setStatusCode(500)
                    ->setBody('PDF generation failed');
          }
     }

     // download Biomedical Equipment PDF
     public function biomedicalEquipmentPdfDownload(int $id)
     {
          try {
               $pdfBinary = \App\Services\PdfService::buildBiomedicalEquipmentPdf($id);

               $inline = $this->request->getGet('inline');
               $filename = "biomedical_equipment_{$id}.pdf";

               return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader(
                         'Content-Disposition',
                         ($inline ? 'inline' : 'attachment') . "; filename=\"{$filename}\""
                    )
                    ->setBody($pdfBinary);
          } catch (\Throwable $e) {

               log_message('error', $e->getMessage());

               return $this->response->setStatusCode(500)
                    ->setBody('PDF generation failed');
          }
     }



     /**
      * Downloadable PDF representation of a ac servicing checklist entry.
      */

     public function acServicePdfDownload(int $id)
     {
          try {
               $pdfBinary = \App\Services\PdfService::buildAcServicePdf($id);

               $inline = $this->request->getGet('inline');
               $filename = "ac_service_{$id}.pdf";

               return $this->response
                    ->setHeader('Content-Type', 'application/pdf')
                    ->setHeader(
                         'Content-Disposition',
                         ($inline ? 'inline' : 'attachment') . "; filename=\"{$filename}\""
                    )
                    ->setBody($pdfBinary);
          } catch (\Throwable $e) {

               log_message('error', $e->getMessage());

               return $this->response->setStatusCode(500)
                    ->setBody('PDF generation failed');
          }
     }


     /**
      * Downloadable PDF representation of a phlebotomy manager checklist entry.
      */


     public function phlebotomyManagerPdfDownload(int $id)
     {
          $uri = $this->request->getUri();
          log_message('error', 'PdfController::phlebotomyManagerPdfDownload invoked, method=' . $this->request->getMethod() . ', path=' . ($uri ? $uri->getPath() : '[none]'));

          try {
               $output = \App\Services\PdfService::buildPhlebotomyManagerPdf($id);
          } catch (\RuntimeException $e) {
               log_message('error', "PdfController::phlebotomyManagerPdfDownload failed: {$e->getMessage()}");
               return $this->response->setStatusCode(404)->setBody('Phlebotomy manager checklist entry not found');
          }

          $inline = $this->request->getGet('inline');
          $disposition = $inline ? 'inline' : 'attachment';
          return $this->response
               ->setStatusCode(200)
               ->setContentType('application/pdf')
               ->setHeader('Content-Disposition', "$disposition; filename=\"phlebotomy_manager_{$id}.pdf\"")
               ->setBody($output);
     }

     /**
      * Downloadable PDF representation of a cctv entry.
      */

     public function cctvDownload(int $id)
     {
          $uri = $this->request->getUri();
          log_message('error', 'PdfController::cctvDownload invoked, method=' . $this->request->getMethod() . ', path=' . ($uri ? $uri->getPath() : '[none]'));

          try {
               $output = \App\Services\PdfService::buildCctvPdf($id);
          } catch (\RuntimeException $e) {
               log_message('error', "PdfController::cctvDownload failed: {$e->getMessage()}");
               return $this->response->setStatusCode(404)->setBody('cctv entry not found');
          }

          $inline = $this->request->getGet('inline');
          $disposition = $inline ? 'inline' : 'attachment';
          return $this->response
               ->setStatusCode(200)
               ->setContentType('application/pdf')
               ->setHeader('Content-Disposition', "$disposition; filename=\"cctv_{$id}.pdf\"")
               ->setBody($output);
     }

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
