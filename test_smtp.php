<?php
// Quick SMTP diagnostic script
// Place in root and run via: php test_smtp.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load .env if exists
$envFile = __DIR__ . '/.env';
$envVars = [];
if (file_exists($envFile)) {
     $lines = file($envFile);
     foreach ($lines as $line) {
          $line = trim($line);
          if (!$line || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
          list($key, $val) = explode('=', $line, 2);
          $envVars[trim($key)] = trim($val, '\'"');
     }
}

$smtpHost = $envVars['SMTP_HOST'] ?? 'smtp.zatpatmail.com';
$smtpUsername = $envVars['SMTP_USERNAME'] ?? 'emailappsmtp.4f7bbead59206e3e';
$smtpPassword = $envVars['SMTP_PASSWORD'] ?? '4f7bbead59206e3e_LB7cVCgqGGv9';
$smtpPort = $envVars['SMTP_PORT'] ?? 587;

echo "=== SMTP Configuration ===\n";
echo "Host: $smtpHost\n";
echo "Port: $smtpPort\n";
echo "Username: " . substr($smtpUsername, 0, 3) . "***" . substr($smtpUsername, -3) . "\n";
echo "Password: " . (strlen($smtpPassword) > 0 ? "set" : "EMPTY") . "\n\n";

// Test TCP connectivity
echo "=== TCP Connectivity Test ===\n";
foreach ([587, 465] as $port) {
     $sock = @fsockopen($smtpHost, $port, $errno, $errstr, 5);
     if ($sock) {
          fclose($sock);
          echo "Port $port: REACHABLE ✓\n";
     } else {
          echo "Port $port: FAILED ✗ (errno=$errno errstr=$errstr)\n";
     }
}

echo "\n=== PHPMailer Test ===\n";

require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
$mail->SMTPDebug = 3; // verbose debug
$mail->isSMTP();
$mail->Host = $smtpHost;
$mail->Port = 587;
$mail->SMTPAuth = true;
$mail->Username = $smtpUsername;
$mail->Password = $smtpPassword;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Timeout = 10;
$mail->setFrom('test@example.com', 'Test');
$mail->addAddress('test@example.com');
$mail->Subject = 'SMTP Test';
$mail->Body = 'Testing SMTP connectivity';

echo "\nAttempting STARTTLS:587 connection...\n";
try {
     $mail->send();
     echo "✓ SUCCESS: Email sent via STARTTLS:587\n";
} catch (Exception $e) {
     echo "✗ FAILED: " . $e->getMessage() . "\n";
     echo "PHPMailer ErrorInfo: " . $mail->ErrorInfo . "\n";
}

echo "\n=== Test Complete ===\n";
