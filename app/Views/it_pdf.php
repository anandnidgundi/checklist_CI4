<!DOCTYPE html>
<html lang="en">

     <head>
          <meta charset="utf-8" />
          <title>IT Checklist <?= esc($it['id'] ?? '') ?></title>
          <style>
          body {
               font-family: Arial, Helvetica, sans-serif;
               font-size: 12px;
          }

          table {
               width: 100%;
               border-collapse: collapse;
               margin-bottom: 12px;
          }

          th,
          td {
               padding: 4px 6px;
               border: 1px solid #ccc;
               vertical-align: top;
          }

          .header-table th {
               background: #f0f0f0;
               width: 160px;
               text-align: left;
          }

          h2,
          h3 {
               margin: 8px 0;
          }

          .page-break {
               page-break-before: always;
          }

          img {
               max-width: 100%;
               height: auto;
          }

          </style>
     </head>

     <body>
          <?php
     $makeSrc = function ($fn) {
          if (!$fn) return '';

          $paths = [
               WRITEPATH . 'uploads/' . $fn,
               WRITEPATH . 'uploads/branding_photos/' . $fn,
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
               WRITEPATH . 'uploads/branding_photos/' . $logoFile,
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
     ?>

          <div style="text-align:center; margin-bottom:12px; background:#f0f0f0; padding:8px;">
               <?php if ($logoSrc): ?>
               <img src="<?= esc($logoSrc) ?>" alt="logo" style="max-height:60px;" />
               <div style="font-size:16px; font-weight:bold; margin-top:4px;">Vijaya Diagnostic Centre Limited</div>
               <?php else: ?>
               <div style="font-size:16px; font-weight:bold;">Vijaya Diagnostic Centre Limited</div>
               <?php endif; ?>
          </div>

          <h2>IT Checklist</h2>

          <table class="header-table">
               <?php foreach (
               [
                    'Centre Name' => $it['centre_name'] ?? '',
                    'Location' => $it['location'] ?? '',
                    'Date of Visit' => $it['date_of_visit'] ?? '',
                    'Visit Time' => $it['visit_time'] ?? '',
                    'Audited By' => $it['audited_by'] ?? '',
                    'Branch Manager' => $it['branch_manager'] ?? '',
                    'Cluster Manager' => $it['cluster_manager'] ?? '',
                    'Contact' => $it['contact'] ?? '',
                    'Notes' => $it['notes'] ?? '',
               ] as $label => $value
          ): ?>
               <?php if ($value !== null && $value !== ''): ?>
               <tr>
                    <th><?= esc($label) ?></th>
                    <td><?= esc($value) ?></td>
               </tr>
               <?php endif; ?>
               <?php endforeach; ?>
          </table>

          <?php if (!empty($it['records'])): ?>
          <h3>Checklist Items</h3>
          <?php
          $grouped = [];
          foreach ($it['records'] as $r) {
               $sec = $r['section_name'] ?? $r['section'] ?? 'Unspecified';
               $sub = $r['sub_section_name'] ?? $r['sub_section'] ?? '';
               $grouped[$sec][$sub][] = $r;
          }
          ?>

          <?php foreach ($grouped as $sec => $subs): ?>
          <h4 style="margin-top:16px;"><strong><?= esc($sec) ?></strong></h4>
          <?php foreach ($subs as $sub => $rows): ?>
          <?php if ($sub !== ''): ?>
          <h5 style="margin-top:8px; margin-left:12px; font-weight:normal;"><?= esc($sub) ?></h5>
          <?php endif; ?>

          <table>
               <thead>
                    <tr>
                         <th>Item</th>
                         <th>Value</th>
                    </tr>
               </thead>
               <tbody>
                    <?php foreach ($rows as $r): ?>
                    <?php
                                   $rawLabel = $r['input_label'] ?? $r['input_name'] ?? $r['item_label'] ?? '';
                                   $displayLabel = $rawLabel !== '' ? $rawLabel : '';
                                   if ($displayLabel === '' && !empty($r['input_name'])) {
                                        $displayLabel = $humanize($r['input_name']);
                                   }
                                   $displayValue = $formatInputValue($r['input_value'] ?? $r['response'] ?? '');
                                   ?>
                    <tr>
                         <td><?= esc($displayLabel) ?></td>
                         <td><?= nl2br(esc($displayValue)) ?></td>
                    </tr>
                    <?php endforeach; ?>
               </tbody>
          </table>
          <?php endforeach; ?>
          <?php endforeach; ?>
          <?php endif; ?>

          <?php
     foreach (($it['records'] ?? []) as $r) {
          $files = [];
          if (!empty($r['attachments'])) {
               if (is_array($r['attachments'])) {
                    $files = $r['attachments'];
               } else {
                    $files = preg_split('/\s*,\s*/', (string) $r['attachments'], -1, PREG_SPLIT_NO_EMPTY);
               }
          }

          foreach ($files as $fn) {
               $src = $makeSrc($fn);
               if (!$src) {
                    continue;
               }
     ?>
          <div class="page-break"></div>
          <div style="margin:16px 0;">
               <img src="<?= esc($src) ?>" alt="attachment" />
          </div>
          <?php
          }
     }
     ?>

          <?php if (!empty($it['photos'])): ?>
          <div class="page-break"></div>
          <h3>Photos</h3>
          <?php foreach ($it['photos'] as $p):
               $fn = $p['url'] ?? $p['filename'] ?? $p['photo'] ?? $p['file'] ?? '';
               $src = $makeSrc($fn);
               if (!$src) continue;
          ?>
          <div style="margin-bottom:16px;">
               <img src="<?= esc($src) ?>" alt="photo" />
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
     </body>

</html>
