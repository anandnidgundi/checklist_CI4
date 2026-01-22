<?php

namespace App\Helpers;

use CodeIgniter\HTTP\CURLRequest;

function sendSMS($mobile1, $name, $tkt, $branch1, $desc)
{
    $mobile = "91.$mobile1"; // Assuming mobile1 is the user input for the mobile number.

    $curl = curl_init();

    $payload = json_encode([
        'from' => '919121999111', // Replace with your sender number
        'to' => $mobile,
        'type' => 'template',
        'message' => [
            'templateid' => '62005',
            'placeholders' => [
                $name,
                $tkt,
                $branch1,
                $desc,
            ],
        ],
    ]);

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.pinbot.ai/v1/wamessage/sendMessage',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'apikey: 9b664354-6b20-11ed-a7c7-9606c7e32d76', // Replace with your actual API key
            'Content-Type: application/json',
        ],
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    return $response; // Optionally, handle the response as needed.
}