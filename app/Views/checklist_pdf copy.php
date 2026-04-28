<!DOCTYPE html>
<html lang="en">

     <head>
          <meta charset="utf-8" />
          <title>Branding Checklist <?= esc($checklist['id'] ?? '') ?></title>
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
     // it early so other header elements (logo) can re‑use it.  It now searches
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

     // company header – logo + title.
     // Try a couple of reasonable filenames so you don’t need to rename the
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

          <h2>Branding Checklist</h2>

          <table class="header-table">
               <?php foreach (
               [
                    'Centre Name'      => $checklist['centre_name'] ?? '',
                    'Location'         => $checklist['location'] ?? '',
                    'Date of Visit'    => $checklist['date_of_visit'] ?? '',
                    'Visit Time'       => $checklist['visit_time'] ?? '',
                    'Audited By'       => $checklist['audited_by'] ?? '',
                    'Branch Manager'   => $checklist['branch_manager'] ?? '',
                    'Cluster Manager'  => $checklist['cluster_manager'] ?? '',
                    'Contact'          => $checklist['contact'] ?? '',
                    'Notes'            => $checklist['notes'] ?? '',
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

          <?php if (!empty($checklist['records'])): ?>
          <h3>Checklist Items</h3>
          <?php
          // humanize underscored names into readable text
          $humanize = function ($s) {
               $s = (string) ($s ?? '');
               $s = str_replace(['_', '-'], ' ', $s);
               return ucwords(trim($s));
          };

          // build nested grouping: section -> subsection -> rows
          $grouped = [];
          foreach ($checklist['records'] as $r) {
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
                                        ?>
                         <td><?= esc($displayLabel) ?></td>
                         <td><?= esc($r['input_value'] ?? $r['response'] ?? '') ?></td>
                    </tr>
                    <?php endforeach; ?>
               </tbody>
          </table>
          <?php endforeach; ?>
          <?php endforeach; ?>
          <?php endif; ?>


          <?php
     // attachments that came back in the dynamic‑form payload
     foreach ($checklist['records'] as $r) :
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

          <?php if (!empty($checklist['photos'])): ?>
          <div class="page-break"></div>
          <h3>Photos</h3>
          <?php foreach ($checklist['photos'] as $p):
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
