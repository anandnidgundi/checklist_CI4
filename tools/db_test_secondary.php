<?php
$host = '127.0.0.1';
$user = 'travelapp';
$pass = 'Vdc123$%^&*()';
$db   = 'travelapp';
$c = @mysqli_connect($host, $user, $pass, $db, 3306);
echo "$host -> ";
if ($c) {
     echo "OK\n";
     mysqli_close($c);
} else {
     echo mysqli_connect_error() . "\n";
}
