<?php
$src = file_get_contents('app/Controllers/DynamicFormController.php');
$level = 0;
$line = 1;
foreach (str_split($src) as $ch) {
     if ($ch === "\n") {
          $line++;
     }
     if ($ch === '{') {
          $level++;
     }
     if ($ch === '}') {
          $level--;
          if ($level < 0) {
               echo 'NEGATIVE at line ' . $line . "\n";
               exit(0);
          }
     }
}
echo 'LEVEL=' . $level . "\n";
