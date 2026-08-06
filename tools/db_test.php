<?php
$hosts = ['localhost', '127.0.0.1'];
foreach ($hosts as $h) {
     $c = @mysqli_connect($h, 'vdcapp2_bmcmchecklist', 't9OcMQcj7w-b', 'vdcapp2_bmcmchecklist', 3306);
     echo "$h -> ";
     if ($c) {
          echo "OK\n";
          mysqli_close($c);
     } else {
          echo mysqli_connect_error() . "\n";
     }
}
