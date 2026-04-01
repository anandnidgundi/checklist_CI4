<!DOCTYPE html>
<html lang="en">

     <head>
          <meta charset="utf-8" />
          <title>Maintenance Checklist <?= esc($maintenance['id'] ?? '') ?></title>
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
     // helper closure to produce a usable image src for Dompdf.  We define
     // it early so other header elements (logo) can reuse it.  It now searches
     // several local directories including public/uploads/images so you can
     // place the logo under the web root if you prefer.
     // log base paths for debugging once
     log_message('error', 'Pdf header paths: WRITEPATH=' . WRITEPATH . ' FCPATH=' . FCPATH);
     $makeSrc = function ($fn) {
          if (! $fn) return '';
          $paths = [
               WRITEPATH . 'uploads/' . $fn,
               WRITEPATH . 'uploads/branding_photos/' . $fn,
               FCPATH . 'uploads/images/' . $fn,              // common location if FCPATH ends in public/
               FCPATH . 'public/uploads/images/' . $fn,       // covers setups where FCPATH is project root
          ];
          foreach ($paths as $local) {
               if (is_file($local)) {
                    // mime_content_type may be missing on some hosts; fall back gracefully
                    if (function_exists('mime_content_type')) {
                         $type = mime_content_type($local) ?: 'application/octet-stream';
                    } else {
                         // guess from extension or use generic
                         $ext = strtolower(pathinfo($local, PATHINFO_EXTENSION));
                         $map = [
                              'jpg' => 'image/jpeg',
                              'jpeg' => 'image/jpeg',
                              'png' => 'image/png',
                              'gif' => 'image/gif',
                              'pdf' => 'application/pdf'
                         ];
                         $type = $map[$ext] ?? 'application/octet-stream';
                    }
                    $data = base64_encode(file_get_contents($local));
                    return 'data:' . $type . ';base64,' . $data;
               }
          }
          // fallback to URL (remote) if allowed (relative to web root)
          return base_url('uploads/' . $fn);
     };

     // company header � logo + title.
     // Try a couple of reasonable filenames so you dont need to rename the
     // file after uploading.  The helper searches writable/uploads,
     // writable/uploads/branding_photos and public/uploads/images.
     $logoSrc = '';
     foreach (['company_logo.png', 'logo.png', 'logo.jpg', 'logo.jpeg'] as $logoFile) {
          // check if the file actually exists in one of the local paths; if not,
          // skip candidate (don't rely on fallback URL).  Log info for each.
          $paths = [
               WRITEPATH . 'uploads/' . $logoFile,
               WRITEPATH . 'uploads/branding_photos/' . $logoFile,
               FCPATH . 'uploads/images/' . $logoFile,
               FCPATH . 'public/uploads/images/' . $logoFile,
          ];
          $pathChecks = [];
          $found = false;
          foreach ($paths as $p) {
               $exists = is_file($p);
               $pathChecks[] = $p . ($exists ? ':Y' : ':N');
               if ($exists) {
                    $found = true;
                    break;
               }
          }
          log_message('error', 'Logo candidate ' . $logoFile . ' checked=' . implode(', ', $pathChecks) . ' found=' . ($found ? 'Y' : 'N'));
          if (! $found) {
               continue;
          }

          // file is present so generate a data URI
          $logoSrc = $makeSrc($logoFile);
          if ($logoSrc) {
               log_message('error', 'Logo selected ' . $logoFile . ' srclen=' . strlen($logoSrc));
               break;
          }
     }
     ?>

          <div style="text-align:center; margin-bottom:12px; background:#f0f0f0; padding:8px;">
               <?php if ($logoSrc): ?>
               <img src="<?= esc($logoSrc) ?>" alt="logo" style="max-height:60px;" />
               <div style="font-size:16px; font-weight:bold; margin-top:4px;">Vijaya Diagnostic Centre Limited</div>
               <?php else: ?>
               <div style="font-size:16px; font-weight:bold;">Vijaya Diagnostic Centre Limited</div>
               <?php endif; ?>
          </div>

          <h2>Maintenance Checklist</h2>

          <table class="header-table">
               <?php foreach (
               [
                    'Centre Name'      => $maintenance['centre_name'] ?? '',
                    'Location'         => $maintenance['location'] ?? '',
                    'Date of Visit'    => $maintenance['date_of_visit'] ?? '',
                    'Visit Time'       => $maintenance['visit_time'] ?? '',
                    'Audited By'       => $maintenance['audited_by'] ?? '',
                    'Branch Manager'   => $maintenance['branch_manager'] ?? '',
                    'Cluster Manager'  => $maintenance['cluster_manager'] ?? '',
                    'Contact'          => $maintenance['contact'] ?? '',
                    'Notes'            => $maintenance['notes'] ?? '',
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

          <?php if (!empty($maintenance['records'])): ?>
          <h3>Checklist Items</h3>
          <?php
          // humanize underscored names into readable text
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
                    if (json_last_error() === JSON_ERROR_NONE) {
                         if (is_array($parsed)) {
                              $isAssoc = array_keys($parsed) !== range(0, count($parsed) - 1);

                              if ($isAssoc) {
                                   $status = trim((string)($parsed['status'] ?? $parsed['value'] ?? $parsed['response'] ?? ''));
                                   $remarks = trim((string)($parsed['remarks'] ?? $parsed['remark'] ?? ''));

                                   if ($status !== '' && $remarks !== '') {
                                        return "Status: {$status}\nRemarks: {$remarks}";
                                   }
                                   if ($status !== '') return $status;
                                   if ($remarks !== '') return "Remarks: {$remarks}";

                                   $vals = [];
                                   foreach ($parsed as $v) {
                                        if (is_scalar($v) || $v === null) {
                                             $sv = trim((string)$v);
                                             if ($sv !== '') $vals[] = $sv;
                                        }
                                   }
                                   if (!empty($vals)) return implode(', ', $vals);
                              } else {
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
                    }
               }

               return $text;
          };

          // build nested grouping: section -> subsection -> rows
          $grouped = [];
          foreach ($maintenance['records'] as $r) {
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
                    <tr>
                         <?php
                                        $rawLabel = $r['input_label'] ?? $r['input_name'] ?? $r['item_label'] ?? '';
                                        $displayLabel = $rawLabel !== '' ? $rawLabel : '';
                                        if ($displayLabel === '' && !empty($r['input_name'])) {
                                             $displayLabel = $humanize($r['input_name']);
                                        }
                                        $displayValue = $formatInputValue($r['input_value'] ?? $r['response'] ?? '');
                                        ?>
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
     // attachments that came back in the dynamicform payload
     foreach ($maintenance['records'] as $r) :
          $files = [];
          if (! empty($r['attachments'])) {
               if (is_array($r['attachments'])) {
                    $files = $r['attachments'];
               } else {
                    $files = preg_split('/\s*,\s*/', (string) $r['attachments'], -1, PREG_SPLIT_NO_EMPTY);
               }
          }
          foreach ($files as $fn) :
               $src = $makeSrc($fn);
               if (! $src) {
                    log_message('error', 'Pdf view could not resolve attachment ' . $fn);
                    continue;
               }
     ?>
          <div class="page-break"></div>
          <div style="margin:16px 0;">
               <img src="<?= esc($src) ?>" alt="attachment" />
          </div>
          <?php
          endforeach;
     endforeach;
     ?>

          <?php if (!empty($maintenance['photos'])): ?>
          <div class="page-break"></div>
          <h3>Photos</h3>
          <?php foreach ($maintenance['photos'] as $p):
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
