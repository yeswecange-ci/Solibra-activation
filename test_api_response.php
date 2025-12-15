<?php

$url = 'https://can-wabracongo.ywcdigital.com/api/can/pronostic';

$data = [
    'phone'           => '+22553989046',  // Josias Test
    'match_id'        => 1,
    'prediction_type' => 'team_a_win',
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "=== TEST API PRONOSTIC ===\n\n";
echo "HTTP Code: {$httpCode}\n\n";

if ($error) {
    echo "Erreur cURL: {$error}\n";
} else {
    echo "Réponse complète:\n";
    echo "-------------------\n";
    echo $response . "\n";
    echo "-------------------\n";
}
