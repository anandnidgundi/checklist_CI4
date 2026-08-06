<!DOCTYPE html>
<html lang="en">

     <head>
          <meta charset="utf-8" />
          <title> Biomedical Equipment Checklist <?= esc($biomedical_equipment['id'] ?? '') ?></title>
          <style>
          body {
               font-family: "Helvetica Neue", Arial, sans-serif;
               color: #1f2937;
               background: #ffffff;
               margin: 0;
               padding: 0;
               line-height: 1.25;
               font-size: 12px;
          }

          .pdf-wrapper {
               max-width: 920px;
               margin: 0 auto;
               padding: 2px 4px 4px;
          }

          .pdf-header {
               display: flex;
               align-items: center;
               justify-content: flex-start;
               gap: 8px;
               padding-bottom: 6px;
               margin-bottom: 6px;
               border-bottom: 1px solid #0f4c81;
               text-align: left;
               width: 100%;
          }

          .header-left {
               flex: 0 0 auto;
               display: flex;
               align-items: center;
          }

          .header-left img {
               max-height: 28px;
               display: block;
               margin: 0;
          }

          .tableNoBorder {
               width: 100%;
               border-collapse: collapse;
               border: none;
          }

          .logo {
               max-height: 28px;
               display: block;
               margin: 0;
          }

          .header-right {
               flex: 1 1 auto;
               min-width: 0;
               margin-left: auto;
               display: flex;
               flex-direction: column;
               align-items: flex-end;
               justify-content: center;
          }

          .company-name {
               font-size: 14px;
               font-weight: 700;
               color: #0f4c81;
               text-transform: uppercase;
               letter-spacing: 0.04em;
               margin: 0;
          }

          .title {
               font-size: 11px;
               color: #111827;
               margin: 1px 0 0;
               letter-spacing: 0.03em;
          }

          .summary-card {
               background: none;
               border: none;
               border-radius: 0;
               padding: 0;
               margin-bottom: 8px;
               box-shadow: none;
          }

          table {
               width: 100%;
               border-collapse: collapse;
               margin-bottom: 6px;
          }

          th,
          td {
               padding: 2px 5px;
               border: 1px solid #d2d7df;
               vertical-align: top;
               font-size: 10px;
          }

          .header-table th {
               background: #e9f1fb;
               width: 16%;
               min-width: 100px;
               text-align: left;
               color: #0f4c81;
               padding: 2px 6px;
          }

          .header-table td {
               background: #ffffff;
               width: 34%;
               padding: 2px 6px;
          }

          .critical-status {
               color: #c0392b;
               font-weight: 600;
          }

          table[class=""] {
               border: none;
          }

          table[class=""] th,
          table[class=""] td {
               border: none;
               padding: 0;
          }

          .item-table th {
               background: #bcdbf7;
               color: #141414;
               text-align: left;
               font-size: 12px;
               padding: 4px;
          }

          .item-table th:first-child,
          .item-table td:first-child {
               width: 260px;
               white-space: normal;
               overflow-wrap: break-word;
               word-wrap: break-word;
          }

          .item-table td {
               padding: 5px;
          }

          .item-table tbody tr:nth-child(odd) {
               background: #fbfdff;

          }

          .item-table {
               margin-bottom: 0.35rem;
          }

          .section-title {
               margin: 8px 0 4px;
               font-size: 14px;
               color: #0f4c81;
               border-left: 4px solid #0f4c81;
               padding-left: 8px;
          }

          .subsection-title {
               margin: 8px 0 4px 16px;
               padding-left: 6px;
               font-size: 12px;
               color: #1f3d6e;
               font-weight: 600;
          }

          .record-entry {
               margin: 0 0 0.3rem 24px;
               line-height: 1.15;
               font-size: 11px;
          }

          .attachment-wrapper {
               margin: 10px 0;
               page-break-inside: avoid;
               break-inside: avoid;
          }

          .attachment-wrapper img {
               max-width: 80%;
               max-height: 360px;
               width: auto;
               display: block;
               margin: 0 auto;
          }

          .page-break {
               page-break-before: always;
               margin-top: 24px;
          }

          img {
               max-width: 100%;
               height: auto;
               display: block;
               margin: 0 auto;
          }

          .record-entry {
               margin: 0 0 0.3rem;
               line-height: 1.2;
               font-size: 11px;
               padding-left: 5rem;
          }

          .record-entry span {
               color: #333;
          }

          .record-entry strong {
               font-weight: 600;
          }

          @media print {
               .pdf-wrapper {
                    padding: 0;
               }
          }

          </style>
     </head>

     <body>
          <?php
     $makeSrc = function ($fn) {
          if (!$fn) return '';

          $paths = [
               WRITEPATH . 'uploads/' . $fn,
               WRITEPATH . 'uploads/ac_services/' . $fn,
               FCPATH . 'uploads/images/' . $fn,
               FCPATH . 'public/uploads/images/' . $fn,
          ];

          foreach ($paths as $local) {
               if (!is_file($local)) {
                    continue;
               }

               if (function_exists('mime_content_type')) {
                    $type = mime_content_type($local) ?: 'application/octet-stream';
               } else {
                    $ext = strtolower(pathinfo($local, PATHINFO_EXTENSION));
                    $map = [
                         'jpg' => 'image/jpeg',
                         'jpeg' => 'image/jpeg',
                         'png' => 'image/png',
                         'gif' => 'image/gif',
                         'pdf' => 'application/pdf',
                    ];
                    $type = $map[$ext] ?? 'application/octet-stream';
               }

               $data = base64_encode((string) file_get_contents($local));
               return 'data:' . $type . ';base64,' . $data;
          }

          return base_url('uploads/' . $fn);
     };

     $logoSrc = '';
     foreach (['company_logo.png', 'logo.png', 'logo.jpg', 'logo.jpeg'] as $logoFile) {
          $paths = [
               WRITEPATH . 'uploads/' . $logoFile,
               WRITEPATH . 'uploads/ac_services/' . $logoFile,
               FCPATH . 'uploads/images/' . $logoFile,
               FCPATH . 'public/uploads/images/' . $logoFile,
          ];

          $exists = false;
          foreach ($paths as $p) {
               if (is_file($p)) {
                    $exists = true;
                    break;
               }
          }

          if (!$exists) {
               continue;
          }

          $logoSrc = $makeSrc($logoFile);
          if ($logoSrc) {
               break;
          }
     }

     $humanize = function ($s) {
          $s = (string) ($s ?? '');
          $s = str_replace(['_', '-'], ' ', $s);
          return ucwords(trim($s));
     };
     $formatDate = function ($rawDate) {
          if ($rawDate === null || $rawDate === '') {
               return '';
          }

          if ($rawDate instanceof \DateTimeInterface) {
               return $rawDate->format('d-m-Y');
          }

          if (is_numeric($rawDate)) {
               $date = date_create('@' . (int)$rawDate);
               if ($date !== false) {
                    return $date->format('d-m-Y');
               }
          }

          $date = date_create(trim((string)$rawDate));
          if ($date !== false) {
               return $date->format('d-m-Y');
          }

          return trim((string)$rawDate);
     };

     $formatDateTime = function ($rawDateTime) {
          if ($rawDateTime === null || $rawDateTime === '') {
               return '';
          }
          if ($rawDateTime instanceof \DateTimeInterface) {
               return $rawDateTime->format('d-m-Y H:i:s');
          }
          if (is_numeric($rawDateTime)) {
               $date = date_create('@' . (int)$rawDateTime);
               if ($date !== false) {
                    return $date->format('d-m-Y H:i:s');
               }
          }
          $date = date_create(trim((string)$rawDateTime));
          if ($date !== false) {
               return $date->format('d-m-Y H:i:s');
          }
          return trim((string)$rawDateTime);
     };

     $createdDtmRaw = $biomedical_equipment['created_dtm'] ?? $biomedical_equipment['createdDTM'] ?? $biomedical_equipment['created_at'] ?? '';
     $createdDtmDisplay = $createdDtmRaw ? $formatDateTime($createdDtmRaw) : '';

     $formatInputValue = function ($raw) {
          if ($raw === null) return '';

          if (is_array($raw)) {
               return implode(', ', array_map('strval', $raw));
          }

          $text = trim((string)$raw);
          if ($text === '') return '';

          $first = substr($text, 0, 1);
          if ($first === '{' || $first === '[') {
               $parsed = json_decode($text, true);
               if (json_last_error() === JSON_ERROR_NONE && is_array($parsed)) {
                    $isAssoc = array_keys($parsed) !== range(0, count($parsed) - 1);
                    if ($isAssoc) {
                         $status = trim((string)($parsed['status'] ?? $parsed['value'] ?? $parsed['response'] ?? ''));
                         $remarks = trim((string)($parsed['remarks'] ?? $parsed['remark'] ?? ''));

                         if ($status !== '' && $remarks !== '') {
                              return "Status: {$status}\nRemarks: {$remarks}";
                         }
                         if ($status !== '') return $status;
                         if ($remarks !== '') return "Remarks: {$remarks}";
                    }

                    $vals = [];
                    foreach ($parsed as $v) {
                         if (is_scalar($v) || $v === null) {
                              $sv = trim((string)$v);
                              if ($sv !== '') $vals[] = $sv;
                         }
                    }
                    if (!empty($vals)) return implode(', ', $vals);
               }
          }

          return $text;
     };

     $isCriticalValue = function ($text) {
          $needle = strtolower(trim((string)$text));
          return in_array($needle, ['repairs needed', 'not working', 'not done', 'pending'], true);
     };
     ?>

          <div class="pdf-wrapper">
               <div class="pdf-header">
                    <table>
                         <tr>
                              <td style="border:none; padding:0;">

                                   <?php if ($logoSrc): ?>
                                   <img src="<?= esc($logoSrc) ?>" class="logo" alt="logo" />
                                   <?php endif; ?>

                              </td>
                              <td style="border:none; padding:0;  ">

                                   <div class="company-name">Vijaya Diagnostic Centre Limited</div>
                                   <div class="title">Biomedical Equipment Checklist</div>

                              </td>
                         </tr>
                    </table>
               </div>

               <div class="summary-card">

                    <table class="header-table">

                         <tr>
                              <th>Branch Name</th>
                              <td><?= esc($biomedical_equipment['branch_name'] ?? '') ?></td>
                              <th>Branch Manager</th>
                              <td><?= esc($biomedical_equipment['branch_manager_name'] ?? '') ?></td>


                         </tr>

                         <tr>
                              <th></th>
                              <td></td>
                              <th>Manager Mobile</th>
                              <td><?= esc($biomedical_equipment['branch_manager_mobile'] ?? '') ?></td>
                         </tr>



                         <tr>
                              <th>Date Of Visit</th>
                              <td><?= esc($formatDate($biomedical_equipment['date_of_visit'] ?? '')) ?></td>
                              <th>Visit Time</th>
                              <td><?= esc($biomedical_equipment['visit_time'] ?? '') ?></td>

                         </tr>

                         <tr>

                              <th>Audited By</th>
                              <td><?= esc($biomedical_equipment['audited_by_name'] ?? '') ?></td>
                              <th>Auditor Emp Code</th>
                              <td><?= esc($biomedical_equipment['audited_by_emp_code'] ?? '') ?></td>
                         </tr>



                    </table>

               </div>

               <h3 class="section-title">Service Details</h3>

               <table class="item-table">

                    <tr>
                         <td>Scheduled PMS Date (as per service agreement)</td>
                         <td><?= esc($formatDate($biomedical_equipment['scheduled_pms_date'] ?? '')) ?></td>
                    </tr>
                    <tr>
                         <td>Actual PMS Date</td>
                         <td><?= esc($formatDate($biomedical_equipment['actual_pms_date'] ?? '')) ?></td>
                    </tr>
                    <tr>
                         <td>Did the service happen in presence of a radiographer?</td>
                         <td><?= esc($biomedical_equipment['service_in_presense_radiographer'] ?? 0) ?></td>
                    </tr>
                    <tr>
                         <td>Radiographer Name</td>
                         <td><?= esc($biomedical_equipment['radiographer_name'] ?? '') ?></td>
                    </tr>

                    <tr>
                         <td>Radiographer BMID</td>
                         <td>
                              <?= esc($biomedical_equipment['radiographer_bmid'] ?? '') ?>
                         </td>
                    </tr>



               </table>

               <?php if (!empty($biomedical_equipment['remarks'])): ?>

               <h3 class="section-title">Remarks</h3>

               <table>
                    <tr>
                         <td>
                              <?= nl2br(esc($biomedical_equipment['remarks'])) ?>
                         </td>
                    </tr>
               </table>
               <?php endif; ?>




               <?php if (!empty($biomedical_equipment['files'])): ?>

               <h3 class="section-title">Uploaded Attachments</h3>

               <div style="margin-top:10px;">

                    <?php foreach ($biomedical_equipment['files'] as $file): ?>

                    <?php
                         $fileName = $file['file_name'] ?? '';
                         $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                         $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);

                         $fileUrl = base_url('viewAcAttachment/' . rawurlencode($fileName));
                         ?>

                    <div style="
                         margin:0 0 18px 0;
                         text-align:center;
                    ">

                         <?php if ($isImage): ?>

                         <img src="<?= esc($file['pdf_src']) ?>" style="
                              width:75%;     
                              max-height:50%;                          
                              object-fit:contain;
                              display:block;
                              margin:0 auto 6px auto;
                         " />

                         <?php elseif ($ext === 'pdf'): ?>

                         <div style="
                              border:1px solid #ddd;
                              padding:20px;
                              width:180px;
                              margin:0 auto;
                              text-align:center;
                              background:#fafafa;
                         ">


                              <div style="margin-top:8px;">
                                   <strong>PDF Document</strong>
                              </div>

                              <div style="margin-top:10px;">
                                   <a href="<?= esc($fileUrl) ?>" target="_blank" rel="noopener noreferrer" style="
                                             display:inline-block;
                                             padding:6px 12px;
                                             background:#dc3545;
                                             color:#fff;
                                             text-decoration:none;
                                             border-radius:4px;
                                        ">
                                        📄 View PDF
                                   </a>
                              </div>
                         </div>

                         <?php endif; ?>

                         <div style="font-size:10px;color:#555;margin-top:6px;">
                              <?= esc($fileName) ?>
                         </div>
                    </div>
                    <?php endforeach; ?>
               </div>

               <?php endif; ?>

          </div>
     </body>

</html>
