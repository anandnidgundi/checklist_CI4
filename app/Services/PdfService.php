<?php

namespace App\Services;

use Dompdf\Dompdf;

class PdfService
{

     /**
      * Helper: fetch form_records for a submission id, trying both numeric submission_id
      * and submission_uuid fallback when available.
      * Returns an array of records.
      */
     private static function fetchFormRecordsForSubmission($db, int $submissionId): array
     {
          $records = [];
          try {
               $uuidRow = $db->table('form_submissions')->select('submission_uuid')->where('id', $submissionId)->get()->getRowArray();
               $uuid = is_array($uuidRow) && !empty($uuidRow['submission_uuid']) ? trim((string)$uuidRow['submission_uuid']) : '';

               $qb = $db->table('form_records')
                    ->select('form_records.*, fi.input_name, fi.input_label')
                    ->join('form_inputs fi', 'fi.id = form_records.input_id', 'left');

               $qb->groupStart();
               $qb->where('form_records.submission_id', $submissionId);
               if ($uuid !== '') {
                    $qb->orWhere('form_records.submission_uuid', $uuid);
               }
               $qb->groupEnd();

               $qb->orderBy('form_records.id', 'asc');
               $records = $qb->get()->getResultArray();
          } catch (\Throwable $e) {
               // ignore and return empty
          }
          return is_array($records) ? $records : [];
     }
     public static function buildPhlebotomyPdf(int $id): string
     {
          // Delegate to buildLabDailyPdf to ensure consistent rendering
          // across automated and manual PDF generation paths.
          try {
               log_message('error', "DEBUG buildPhlebotomyPdf: delegating to buildLabDailyPdf for submission {$id}");
               return self::buildLabDailyPdf($id);
          } catch (\Throwable $e) {
               // If delegation fails, bubble the exception so callers can handle it.
               throw $e;
          }
     }
     //  * @throws \RuntimeException if lab weekly submission not found
     //  */
     public static function buildLabWeeklyPdf(int $id): string
     {
          $db = \Config\Database::connect();
          $check = $db->table('form_submissions')->where('id', $id)->get()->getRowArray();
          if (empty($check)) {
               throw new \RuntimeException("Lab weekly checklist entry {$id} not found");
          }

          // decode header JSON (this contains the centre_name/location/etc)
          if (!empty($check['header'])) {
               $hdr = json_decode($check['header'], true);
               if (is_array($hdr)) {
                    $check = array_merge($check, $hdr);
               }
          }

          // import hack (if needed)
          if (!empty($check['centre_name']) && preg_match('/Imported from dynamic-form\\s*(\\d+)/i', $check['centre_name'], $m)) {
               $imported = (int) $m[1];
               if ($imported) {
                    try {
                         $sub = $db->table('form_submissions')->select('header')
                              ->where('id', $imported)->get()->getRowArray();
                         if ($sub && !empty($sub['header'])) {
                              $hdr = json_decode($sub['header'], true);
                              if (is_array($hdr)) {
                                   $check = array_merge($check, $hdr);
                              }
                         }
                    } catch (\Throwable $e) {
                         // fail silently
                    }
               }
          }

          // fetch associated records from form_records, joining metadata for labels and section names
          $records = [];
          try {
               $records = self::fetchFormRecordsForSubmission($db, (int)$id);

               if (!empty($records)) {
                    $sectionIds = array_unique(array_filter(array_map(fn($r) => isset($r['section_id']) ? (int)$r['section_id'] : 0, $records)));
                    $subIds = array_unique(array_filter(array_map(fn($r) => isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0, $records)));

                    $sectionMap = [];
                    if (!empty($sectionIds)) {
                         $secs = $db->table('form_sections')
                              ->select('section_id,section_name')
                              ->whereIn('section_id', $sectionIds)
                              ->get()->getResultArray();
                         foreach ($secs as $s) {
                              $sectionMap[(int)$s['section_id']] = $s['section_name'] ?? null;
                         }
                    }

                    $subSectionMap = [];
                    if (!empty($subIds)) {
                         $subs = $db->table('form_sub_sections')
                              ->select('sub_section_id,sub_section_name')
                              ->whereIn('sub_section_id', $subIds)
                              ->get()->getResultArray();
                         foreach ($subs as $ss) {
                              $subSectionMap[(int)$ss['sub_section_id']] = $ss['sub_section_name'] ?? null;
                         }
                    }

                    foreach ($records as &$r) {
                         $sid = isset($r['section_id']) ? (int)$r['section_id'] : 0;
                         $subid = isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0;
                         if ($sid && isset($sectionMap[$sid])) {
                              $r['section_name'] = $sectionMap[$sid];
                         }
                         if ($subid && isset($subSectionMap[$subid])) {
                              $r['sub_section_name'] = $subSectionMap[$subid];
                         }
                    }
                    unset($r);
               }
          } catch (\Throwable $e) {
               // log_message('error', 'PdfService::buildLabWeeklyPdf failed to load records: ' . $e->getMessage());
          }

          // attach records into the payload so view continues to work
          $check['records'] = $records;

          // photos (optional)
          try {
               $photos = $db->table('branding_photos')
                    ->where('checklist_id', $id)
                    ->get()
                    ->getResultArray();
               if (!empty($photos)) {
                    $check['photos'] = $photos;
               }
          } catch (\Throwable $__e) {
               // ignore
          }

          $data['lab'] = $check;
          $html = view('lab_weekly_pdf', $data);
          $pdf = new Dompdf();
          $pdf->loadHtml($html);
          $pdf->setPaper('A4', 'portrait');
          $pdf->render();

          return $pdf->output();
     }

     /**
      * Build a PDF for a lab daily checklist and return raw bytes.
      *
      * @param int $id
      * @return string PDF data
      * @throws \RuntimeException if lab daily submission not found
      */
     public static function buildLabDailyPdf(int $id): string
     {
          $db = \Config\Database::connect();
          $lab = $db->table('form_submissions')->where('id', $id)->get()->getRowArray();
          if (empty($lab)) {
               throw new \RuntimeException("Lab daily entry {$id} not found");
          }

          if (!empty($lab['header'])) {
               $hdr = json_decode($lab['header'], true);
               if (is_array($hdr)) {
                    $lab = array_merge($lab, $hdr);
               }
          }

          // Import hack (if needed)
          if (!empty($lab['centre_name']) && preg_match('/Imported from dynamic-form\s*(\d+)/i', $lab['centre_name'], $m)) {
               $imported = (int) $m[1];
               if ($imported) {
                    try {
                         $sub = $db->table('form_submissions')->select('header')
                              ->where('id', $imported)->get()->getRowArray();
                         if ($sub && !empty($sub['header'])) {
                              $hdr = json_decode($sub['header'], true);
                              if (is_array($hdr)) {
                                   $lab = array_merge($lab, $hdr);
                              }
                         }
                    } catch (\Throwable $e) {
                         // fail silently
                    }
               }
          }

          // Fetch records and attach section names
          $records = [];
          try {
               $records = self::fetchFormRecordsForSubmission($db, (int)$id);

               if (!empty($records)) {
                    $sectionIds = array_unique(array_filter(array_map(fn($r) => isset($r['section_id']) ? (int)$r['section_id'] : 0, $records)));
                    $subIds = array_unique(array_filter(array_map(fn($r) => isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0, $records)));

                    $sectionMap = [];
                    if (!empty($sectionIds)) {
                         $secs = $db->table('form_sections')
                              ->select('section_id,section_name')
                              ->whereIn('section_id', $sectionIds)
                              ->get()->getResultArray();
                         foreach ($secs as $s) {
                              $sectionMap[(int)$s['section_id']] = $s['section_name'] ?? null;
                         }
                    }

                    $subSectionMap = [];
                    if (!empty($subIds)) {
                         $subs = $db->table('form_sub_sections')
                              ->select('sub_section_id,sub_section_name')
                              ->whereIn('sub_section_id', $subIds)
                              ->get()->getResultArray();
                         foreach ($subs as $ss) {
                              $subSectionMap[(int)$ss['sub_section_id']] = $ss['sub_section_name'] ?? null;
                         }
                    }

                    foreach ($records as &$r) {
                         $sid = isset($r['section_id']) ? (int)$r['section_id'] : 0;
                         $subid = isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0;
                         if ($sid && isset($sectionMap[$sid])) {
                              $r['section_name'] = $sectionMap[$sid];
                         }
                         if ($subid && isset($subSectionMap[$subid])) {
                              $r['sub_section_name'] = $subSectionMap[$subid];
                         }
                    }
                    unset($r);
               }
          } catch (\Throwable $e) {
               // log_message('error', 'PdfService::buildLabDailyPdf failed to load records: ' . $e->getMessage());
          }

          $lab['records'] = $records;

          // Debugging: log fetched submission and record counts to help diagnose
          try {
               log_message('error', 'DEBUG buildLabDailyPdf: submission id=' . (int)$id . ' lab_row=' . json_encode(array_intersect_key($lab, array_flip(['id', 'form_id', 'form_name', 'centre_name', 'created_dtm']))) . ' records_count=' . count($records));
          } catch (\Throwable $__logEx) {
               // ignore logging errors
          }

          // Photos (optional)
          try {
               $photos = $db->table('branding_photos')
                    ->where('checklist_id', $id)
                    ->get()
                    ->getResultArray();
               if (!empty($photos)) {
                    $lab['photos'] = $photos;
               }
          } catch (\Throwable $__e) {
               // ignore
          }

          $data['lab'] = $lab;
          $html = view('lab_daily_pdf', $data);
          try {
               $len = is_string($html) ? strlen($html) : 0;
               log_message('error', "DEBUG buildLabDailyPdf: generated HTML length={$len} for submission {$id}");
          } catch (\Throwable $__logEx) {
          }
          $pdf = new Dompdf();
          $pdf->loadHtml($html);
          $pdf->setPaper('A4', 'portrait');
          $pdf->render();

          return $pdf->output();
     }
     private static function looksLikeItFormName(string $name): bool
     {
          $name = strtolower(trim($name));
          if ($name === '') {
               return false;
          }

          if (strpos($name, 'information technology') !== false) {
               return true;
          }

          if (strpos($name, 'it_checklist') !== false || strpos($name, 'it checklist') !== false) {
               return true;
          }

          // Match standalone "it" token in form names like IT-Checklist.
          return (bool) preg_match('/(^|[^a-z0-9])it([^a-z0-9]|$)/i', $name);
     }

     private static function isItSubmission($db, array $check): bool
     {
          $candidates = [];

          $headerFormName = '';
          if (!empty($check['header']) && is_string($check['header'])) {
               $hdr = json_decode($check['header'], true);
               if (is_array($hdr)) {
                    $headerFormName = trim((string) ($hdr['form_name'] ?? ''));
               }
          }

          $submissionFormName = trim((string) ($check['form_name'] ?? ''));
          if ($submissionFormName !== '') {
               $candidates[] = $submissionFormName;
          }
          if ($headerFormName !== '') {
               $candidates[] = $headerFormName;
          }

          foreach ($candidates as $name) {
               if (self::looksLikeItFormName($name)) {
                    return true;
               }
          }

          $formId = (int) ($check['form_id'] ?? 0);
          if ($formId <= 0) {
               return false;
          }

          try {
               $tableToQuery = 'forms';
               if (method_exists($db, 'tableExists') && ! $db->tableExists('forms')) {
                    if (method_exists($db, 'tableExists') && $db->tableExists('vdc_forms')) {
                         $tableToQuery = 'vdc_forms';
                    } else {
                         return false;
                    }
               }
               $row = $db->table($tableToQuery)->select('form_name')->where('id', $formId)->get()->getRowArray();
               $dbFormName = trim((string) ($row['form_name'] ?? ''));
               return self::looksLikeItFormName($dbFormName);
          } catch (\Throwable $e) {
               return false;
          }
     }

     /**
      * Build a PDF for the branding checklist and return raw bytes.
      *
      * This duplicates the logic that used to live in PdfController but is
      * now reusable from other places (eg. email attachments, cron jobs, etc).
      *
      * @param int $id
      * @return string PDF data
      * @throws \RuntimeException if checklist not found
      */
     public static function buildChecklistPdf(int $id): string
     {
          // We no longer use the branding_checklists table at all; all data
          // comes from the dynamic form submission row.
          $db = \Config\Database::connect();
          $check = $db->table('form_submissions')->where('id', $id)->get()->getRowArray();
          if (empty($check)) {
               throw new \RuntimeException("Checklist {$id} not found");
          }

          // decode header JSON (this contains the centre_name/location/etc)
          if (! empty($check['header'])) {
               $hdr = json_decode($check['header'], true);
               if (is_array($hdr)) {
                    $check = array_merge($check, $hdr);
               }
          }

          // the old "imported from dynamic-form" hack is irrelevant now since
          // the source row is already the dynamic submission, but keep it in case
          // somebody still inserted that text manually into centre_name.
          if (
               !empty($check['centre_name'])
               && preg_match('/Imported from dynamic-form\s*(\d+)/i', $check['centre_name'], $m)
          ) {
               $imported = (int) $m[1];
               if ($imported) {
                    try {
                         $sub = $db->table('form_submissions')->select('header')
                              ->where('id', $imported)->get()->getRowArray();
                         if ($sub && !empty($sub['header'])) {
                              $hdr = json_decode($sub['header'], true);
                              if (is_array($hdr)) {
                                   $check = array_merge($check, $hdr);
                              }
                         }
                    } catch (\Throwable $e) {
                         // fail silently
                    }
               }
          }

          // fetch associated records from form_records, joining metadata for
          // human-friendly labels and section names
          $records = [];
          try {
               $records = self::fetchFormRecordsForSubmission($db, (int)$id);
               log_message('error', "PdfService: submission {$id} returned " . count($records) . " record(s)");
               foreach ($records as $rr) {
                    if (!empty($rr['attachments'])) {
                         log_message('error', "PdfService: record has attachments: " . json_encode($rr['attachments']));
                    }
               }

               if (!empty($records)) {
                    // fetch section/sub-section names just as the old model did
                    $sectionIds = array_unique(array_filter(array_map(fn($r) => isset($r['section_id']) ? (int)$r['section_id'] : 0, $records)));
                    $subIds = array_unique(array_filter(array_map(fn($r) => isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0, $records)));

                    $sectionMap = [];
                    if (!empty($sectionIds)) {
                         $secs = $db->table('form_sections')
                              ->select('section_id,section_name')
                              ->whereIn('section_id', $sectionIds)
                              ->get()->getResultArray();
                         foreach ($secs as $s) {
                              $sectionMap[(int)$s['section_id']] = $s['section_name'] ?? null;
                         }
                    }

                    $subSectionMap = [];
                    if (!empty($subIds)) {
                         $subs = $db->table('form_sub_sections')
                              ->select('sub_section_id,sub_section_name')
                              ->whereIn('sub_section_id', $subIds)
                              ->get()->getResultArray();
                         foreach ($subs as $ss) {
                              $subSectionMap[(int)$ss['sub_section_id']] = $ss['sub_section_name'] ?? null;
                         }
                    }

                    foreach ($records as &$r) {
                         $sid = isset($r['section_id']) ? (int)$r['section_id'] : 0;
                         $subid = isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0;
                         if ($sid && isset($sectionMap[$sid])) {
                              $r['section_name'] = $sectionMap[$sid];
                         }
                         if ($subid && isset($subSectionMap[$subid])) {
                              $r['sub_section_name'] = $subSectionMap[$subid];
                         }
                    }
                    unset($r);
               }
          } catch (\Throwable $e) {
               log_message('error', 'PdfService::buildChecklistPdf failed to load records: ' . $e->getMessage());
          }

          // attach records into the payload so view continues to work
          $check['records'] = $records;

          // in older deployments photos were stored separately; try to pull any
          // branding_photos rows that still exist for this id so images are not
          // lost after we switched to form_submissions
          try {
               $photos = $db->table('branding_photos')
                    ->where('checklist_id', $id)
                    ->get()
                    ->getResultArray();
               if (!empty($photos)) {
                    $check['photos'] = $photos;
               }
          } catch (\Throwable $__e) {
               // ignore if table missing or query fails
          }

          $viewName = 'checklist_pdf';
          $dataKey = 'checklist';

          if (self::isItSubmission($db, $check)) {
               $viewName = 'it_pdf';
               $dataKey = 'it';
          }

          $data[$dataKey] = $check;
          $html = view($viewName, $data);
          $pdf = new Dompdf();
          $pdf->loadHtml($html);
          $pdf->setPaper('A4', 'portrait');
          $pdf->render();

          return $pdf->output();
     }

     /**
      * Build a PDF for a maintenance checklist and return raw bytes.
      *
      * The logic is essentially identical to buildChecklistPdf but the view
      * name and output key differ so the template can refer to the correct
      * variable.
      *
      * @param int $id
      * @return string PDF data
      * @throws \RuntimeException if maintenance submission not found
      */
     public static function buildMaintenancePdf(int $id): string
     {
          // reuse the same fetching logic as for checklists
          $db = \Config\Database::connect();
          $check = $db->table('form_submissions')->where('id', $id)->get()->getRowArray();
          if (empty($check)) {
               throw new \RuntimeException("Maintenance entry {$id} not found");
          }

          if (! empty($check['header'])) {
               $hdr = json_decode($check['header'], true);
               if (is_array($hdr)) {
                    $check = array_merge($check, $hdr);
               }
          }

          // same import hack as above (unlikely needed but safe)
          if (
               !empty($check['centre_name'])
               && preg_match('/Imported from dynamic-form\s*(\d+)/i', $check['centre_name'], $m)
          ) {
               $imported = (int) $m[1];
               if ($imported) {
                    try {
                         $sub = $db->table('form_submissions')->select('header')
                              ->where('id', $imported)->get()->getRowArray();
                         if ($sub && !empty($sub['header'])) {
                              $hdr = json_decode($sub['header'], true);
                              if (is_array($hdr)) {
                                   $check = array_merge($check, $hdr);
                              }
                         }
                    } catch (\Throwable $e) {
                         // fail silently
                    }
               }
          }

          // fetch records and attach section names exactly as above
          $records = [];
          try {
               $records = self::fetchFormRecordsForSubmission($db, (int)$id);
               log_message('error', "PdfService: maintenance {$id} returned " . count($records) . " record(s)");
               foreach ($records as $rr) {
                    if (!empty($rr['attachments'])) {
                         log_message('error', "PdfService: record has attachments: " . json_encode($rr['attachments']));
                    }
               }

               if (!empty($records)) {
                    $sectionIds = array_unique(array_filter(array_map(fn($r) => isset($r['section_id']) ? (int)$r['section_id'] : 0, $records)));
                    $subIds = array_unique(array_filter(array_map(fn($r) => isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0, $records)));

                    $sectionMap = [];
                    if (!empty($sectionIds)) {
                         $secs = $db->table('form_sections')
                              ->select('section_id,section_name')
                              ->whereIn('section_id', $sectionIds)
                              ->get()->getResultArray();
                         foreach ($secs as $s) {
                              $sectionMap[(int)$s['section_id']] = $s['section_name'] ?? null;
                         }
                    }

                    $subSectionMap = [];
                    if (!empty($subIds)) {
                         $subs = $db->table('form_sub_sections')
                              ->select('sub_section_id,sub_section_name')
                              ->whereIn('sub_section_id', $subIds)
                              ->get()->getResultArray();
                         foreach ($subs as $ss) {
                              $subSectionMap[(int)$ss['sub_section_id']] = $ss['sub_section_name'] ?? null;
                         }
                    }

                    foreach ($records as &$r) {
                         $sid = isset($r['section_id']) ? (int)$r['section_id'] : 0;
                         $subid = isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0;
                         if ($sid && isset($sectionMap[$sid])) {
                              $r['section_name'] = $sectionMap[$sid];
                         }
                         if ($subid && isset($subSectionMap[$subid])) {
                              $r['sub_section_name'] = $subSectionMap[$subid];
                         }
                    }
                    unset($r);
               }
          } catch (\Throwable $e) {
               log_message('error', 'PdfService::buildMaintenancePdf failed to load records: ' . $e->getMessage());
          }

          $check['records'] = $records;

          // photos table might not be relevant for maintenance but reuse same logic
          try {
               $photos = $db->table('branding_photos')
                    ->where('checklist_id', $id)
                    ->get()
                    ->getResultArray();
               if (!empty($photos)) {
                    $check['photos'] = $photos;
               }
          } catch (\Throwable $__e) {
               // ignore
          }

          $data['maintenance'] = $check;
          $html = view('maintenance_pdf', $data);
          $pdf = new Dompdf();
          $pdf->loadHtml($html);
          $pdf->setPaper('A4', 'portrait');
          $pdf->render();

          return $pdf->output();
     }

     /**
      * Build a PDF for an IT checklist and return raw bytes.
      *
      * This method reuses checklist loading logic but forces IT template rendering.
      *
      * @param int $id
      * @return string PDF data
      */
     public static function buildItPdf(int $id): string
     {
          $db = \Config\Database::connect();
          $check = $db->table('form_submissions')->where('id', $id)->get()->getRowArray();
          if (empty($check)) {
               throw new \RuntimeException("IT checklist {$id} not found");
          }

          if (! empty($check['header'])) {
               $hdr = json_decode($check['header'], true);
               if (is_array($hdr)) {
                    $check = array_merge($check, $hdr);
               }
          }

          $records = [];
          try {
               $records = self::fetchFormRecordsForSubmission($db, (int)$id);

               if (!empty($records)) {
                    $sectionIds = array_unique(array_filter(array_map(fn($r) => isset($r['section_id']) ? (int)$r['section_id'] : 0, $records)));
                    $subIds = array_unique(array_filter(array_map(fn($r) => isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0, $records)));

                    $sectionMap = [];
                    if (!empty($sectionIds)) {
                         $secs = $db->table('form_sections')
                              ->select('section_id,section_name')
                              ->whereIn('section_id', $sectionIds)
                              ->get()->getResultArray();
                         foreach ($secs as $s) {
                              $sectionMap[(int)$s['section_id']] = $s['section_name'] ?? null;
                         }
                    }

                    $subSectionMap = [];
                    if (!empty($subIds)) {
                         $subs = $db->table('form_sub_sections')
                              ->select('sub_section_id,sub_section_name')
                              ->whereIn('sub_section_id', $subIds)
                              ->get()->getResultArray();
                         foreach ($subs as $ss) {
                              $subSectionMap[(int)$ss['sub_section_id']] = $ss['sub_section_name'] ?? null;
                         }
                    }

                    foreach ($records as &$r) {
                         $sid = isset($r['section_id']) ? (int)$r['section_id'] : 0;
                         $subid = isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0;
                         if ($sid && isset($sectionMap[$sid])) {
                              $r['section_name'] = $sectionMap[$sid];
                         }
                         if ($subid && isset($subSectionMap[$subid])) {
                              $r['sub_section_name'] = $subSectionMap[$subid];
                         }
                    }
                    unset($r);
               }
          } catch (\Throwable $e) {
               log_message('error', 'PdfService::buildItPdf failed to load records: ' . $e->getMessage());
          }

          $photos = [];
          $photosByCaption = [];
          try {
               $photos = $db->table('branding_photos')
                    ->where('checklist_id', $id)
                    ->get()
                    ->getResultArray();
               foreach ($photos as $photo) {
                    $caption = trim((string) ($photo['caption'] ?? ''));
                    if ($caption === '') {
                         continue;
                    }
                    if (!isset($photosByCaption[$caption])) {
                         $photosByCaption[$caption] = [];
                    }
                    $photosByCaption[$caption][] = $photo['filename'];
               }
          } catch (\Throwable $__e) {
               // ignore
          }

          if (!empty($records)) {
               foreach ($records as &$r) {
                    $r['attachments'] = '';

                    $inputId = isset($r['input_id']) ? (int) $r['input_id'] : 0;
                    $sectionId = isset($r['section_id']) ? (int) $r['section_id'] : 0;
                    $subSectionId = isset($r['sub_section_id']) ? (int) $r['sub_section_id'] : 0;
                    $fieldSlug = '';
                    if (!empty($r['input_name'])) {
                         $fieldSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim((string) $r['input_name'])));
                         $fieldSlug = trim($fieldSlug, '_');
                    }

                    $possibleKeys = [];
                    if ($sectionId && $subSectionId && $fieldSlug !== '') {
                         $possibleKeys[] = "s{$sectionId}__ss{$subSectionId}__{$fieldSlug}";
                    }
                    if ($fieldSlug !== '') {
                         $possibleKeys[] = $fieldSlug;
                    }

                    $inputValue = trim((string) ($r['input_value'] ?? ''));
                    if ($inputValue !== '') {
                         $valueFiles = array_filter(array_map('trim', explode(',', $inputValue)));
                         foreach ($photosByCaption as $caption => $filenames) {
                              foreach ($filenames as $filename) {
                                   if (in_array($filename, $valueFiles, true)) {
                                        $r['attachments'] = $r['attachments']
                                             ? $r['attachments'] . ',' . $filename
                                             : $filename;
                                   }
                              }
                         }
                    }

                    foreach ($possibleKeys as $key) {
                         if (isset($photosByCaption[$key]) && !empty($photosByCaption[$key])) {
                              $photoFiles = implode(',', $photosByCaption[$key]);
                              $r['attachments'] = $r['attachments']
                                   ? $r['attachments'] . ',' . $photoFiles
                                   : $photoFiles;
                         }
                    }

                    if ($r['attachments']) {
                         $unique = array_unique(array_filter(explode(',', $r['attachments'])));
                         $r['attachments'] = implode(',', $unique);
                    }
               }
               unset($r);
          }

          $check['records'] = $records;

          if (!empty($photos)) {
               $check['photos'] = $photos;
          }

          $data['it'] = $check;
          $html = view('it_pdf', $data);
          $pdf = new Dompdf();
          $pdf->loadHtml($html);
          $pdf->setPaper('A4', 'portrait');
          $pdf->render();

          return $pdf->output();
     }
}
