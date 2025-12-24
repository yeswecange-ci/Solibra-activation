<?php

/**
 * Script de test pour les endpoints de l'API boisson préférée
 * Usage: php test_boisson_api.php
 */

$baseUrl = 'https://can-wabracongo.ywcdigital.com/api/can';
$testPhone = 'whatsapp:+243999888777'; // Numéro de test

echo "========================================\n";
echo "TEST DES ENDPOINTS BOISSON PRÉFÉRÉE\n";
echo "========================================\n\n";

// Test 1: Inscription avec boisson préférée
echo "TEST 1: Inscription avec boisson préférée\n";
echo "-----------------------------------------\n";

$inscriptionData = [
    'phone' => $testPhone,
    'name' => 'Test User Boisson',
    'boisson_preferee' => 'Bock',
    'source_type' => 'DIRECT',
    'source_detail' => 'SANS_QR',
    'status' => 'INSCRIT',
    'timestamp' => date('Y-m-d H:i:s')
];

$ch = curl_init($baseUrl . '/inscription');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($inscriptionData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status Code: $httpCode\n";
echo "Response: " . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n\n";

// Test 2: Check user (doit retourner has_boisson_preferee = true)
echo "TEST 2: Check user avec boisson\n";
echo "---------------------------------\n";

$checkData = ['phone' => $testPhone];

$ch = curl_init($baseUrl . '/check-user');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status Code: $httpCode\n";
echo "Response: " . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n\n";

$checkResponse = json_decode($response, true);

// Test 3: Mise à jour de la boisson préférée
echo "TEST 3: Mise à jour de la boisson préférée\n";
echo "-------------------------------------------\n";

$setBoissonData = [
    'phone' => $testPhone,
    'boisson_preferee' => 'Coca Cola'
];

$ch = curl_init($baseUrl . '/set-boisson');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($setBoissonData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status Code: $httpCode\n";
echo "Response: " . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n\n";

// Test 4: Vérifier la mise à jour
echo "TEST 4: Vérifier la mise à jour\n";
echo "--------------------------------\n";

$ch = curl_init($baseUrl . '/check-user');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($checkData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status Code: $httpCode\n";
echo "Response: " . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n\n";

// Test 5: Test avec utilisateur non existant
echo "TEST 5: set-boisson avec utilisateur non existant\n";
echo "--------------------------------------------------\n";

$setBoissonData = [
    'phone' => 'whatsapp:+243111111111',
    'boisson_preferee' => 'Sprite'
];

$ch = curl_init($baseUrl . '/set-boisson');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($setBoissonData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Status Code: $httpCode (devrait être 404)\n";
echo "Response: " . json_encode(json_decode($response), JSON_PRETTY_PRINT) . "\n\n";

echo "========================================\n";
echo "TESTS TERMINÉS\n";
echo "========================================\n";
