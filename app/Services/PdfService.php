<?php

namespace App\Services;

use Dompdf\Dompdf;

class PdfService
{
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
               $records = $db->table('form_records')
                    ->select('form_records.*, fi.input_name, fi.input_label')
                    ->join('form_inputs fi', 'fi.id = form_records.input_id', 'left')
                    ->where('form_records.submission_id', $id)
                    ->orderBy('form_records.id', 'asc')
                    ->get()
                    ->getResultArray();
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

          $data['checklist'] = $check;
          $html = view('checklist_pdf', $data);
          $pdf = new Dompdf();
          $pdf->loadHtml($html);
          $pdf->setPaper('A4', 'portrait');
          $pdf->render();

          return $pdf->output();
     }
}
