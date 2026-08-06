<?php
$hosts = ['localhost', '127.0.0.1'];
foreach ($hosts as $h) {
     try {
          $c = mysqli_connect($h, 'vdcapp2_bmcmchecklist', 't9OcMQcj7w-b', 'vdcapp2_bmcmchecklist', 3306);
          echo "$h -> ";
          echo $c ? "OK\n" : (mysqli_connect_error() . "\n");
          if ($c) mysqli_close($c);
     } catch (Throwable $e) {
          echo "$h -> Exception: " . $e->getMessage() . "\n";
     }
}
