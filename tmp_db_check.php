<?php
$mysqli = new mysqli('localhost', 'root', '', 'vdcapp2_bmcmchecklist', 3306);
if ($mysqli->connect_error) {
     echo 'CONNECT_ERROR: ' . $mysqli->connect_error . "\n";
     exit(1);
}
$res = $mysqli->query('SELECT COUNT(*) as c FROM form_submissions WHERE id IN (216,218,219)');
if (!$res) {
     echo 'QUERY_ERROR: ' . $mysqli->error . "\n";
     exit(1);
}
$row = $res->fetch_assoc();
echo 'OK COUNT=' . $row['c'] . "\n";
$mysqli->close();
