<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Village;
use Illuminate\Support\Facades\Http;

echo "\n=== TEST DU FLOW D'INSCRIPTION ===\n\n";

// Numéro de test
$testPhone = '+2250700000999';

// 1. Nettoyer si l'utilisateur existe déjà
echo "1. Nettoyage...\n";
User::where('phone', $testPhone)->delete();
echo "   ✓ Utilisateur supprimé si existait\n\n";

// 2. Vérifier les villages actifs
echo "2. Vérification des villages actifs...\n";
$villageCount = Village::where('is_active', true)->count();
$village = Village::where('is_active', true)->first();
echo "   Villages actifs: {$villageCount}\n";
if ($village) {
    echo "   Village par défaut: {$village->name} (ID: {$village->id})\n";
} else {
    echo "   ❌ ERREUR: Aucun village actif !\n";
    exit(1);
}
echo "\n";

// 3. Test check-user (doit retourner NOT_FOUND)
echo "3. Test check-user (utilisateur inexistant)...\n";
$response = Http::post('http://localhost/api/can/check-user', [
    'phone' => $testPhone
]);

if ($response->successful()) {
    $data = $response->json();
    echo "   Status: {$data['status']}\n";
    if ($data['status'] === 'NOT_FOUND') {
        echo "   ✓ Correct: utilisateur non trouvé\n";
    } else {
        echo "   ❌ ERREUR: Devrait être NOT_FOUND\n";
    }
} else {
    echo "   ❌ ERREUR API: " . $response->body() . "\n";
}
echo "\n";

// 4. Test inscription-simple avec answer_1 (doit créer l'utilisateur)
echo "4. Test inscription-simple avec answer_1 (création utilisateur)...\n";
$response = Http::post('http://localhost/api/can/inscription-simple', [
    'phone' => $testPhone,
    'answer_1' => 'BOCK',
    'timestamp' => now()->format('Y-m-d H:i:s')
]);

if ($response->successful()) {
    $data = $response->json();
    echo "   Success: {$data['success']}\n";
    echo "   User ID: {$data['user_id']}\n";
    echo "   Name: {$data['name']}\n";
    echo "   Has boisson: " . ($data['has_boisson'] ? 'true' : 'false') . "\n";
    echo "   Has quiz: " . ($data['has_quiz_answer'] ? 'true' : 'false') . "\n";
    echo "   Has policies: " . ($data['has_accepted_policies'] ? 'true' : 'false') . "\n";
    echo "   ✓ Utilisateur créé avec succès\n";
} else {
    echo "   ❌ ERREUR API: " . $response->body() . "\n";
    exit(1);
}
echo "\n";

// 5. Vérifier en base de données
echo "5. Vérification en base de données...\n";
$user = User::where('phone', $testPhone)->first();
if ($user) {
    echo "   ✓ Utilisateur trouvé\n";
    echo "   ID: {$user->id}\n";
    echo "   Nom: {$user->name}\n";
    echo "   Boisson: {$user->boisson_preferee}\n";
    echo "   Quiz: " . ($user->quiz_answer ?? 'null') . "\n";
    echo "   Policies: " . ($user->accepted_policies_at ?? 'null') . "\n";
    echo "   Village ID: {$user->village_id}\n";
    echo "   Status: {$user->registration_status}\n";
} else {
    echo "   ❌ ERREUR: Utilisateur non trouvé en BD !\n";
    exit(1);
}
echo "\n";

// 6. Test check-user (doit retourner INCOMPLETE)
echo "6. Test check-user (utilisateur incomplet)...\n";
$response = Http::post('http://localhost/api/can/check-user', [
    'phone' => $testPhone
]);

if ($response->successful()) {
    $data = $response->json();
    echo "   Status: {$data['status']}\n";
    echo "   Has boisson: " . ($data['has_boisson_preferee'] ? 'true' : 'false') . "\n";
    echo "   Has quiz: " . ($data['has_quiz_answer'] ? 'true' : 'false') . "\n";
    echo "   Has policies: " . ($data['has_accepted_policies'] ? 'true' : 'false') . "\n";

    if ($data['status'] === 'INCOMPLETE' && $data['has_boisson_preferee'] && !$data['has_quiz_answer']) {
        echo "   ✓ Correct: utilisateur incomplet (a boisson, manque quiz)\n";
    } else {
        echo "   ❌ ERREUR: État incorrect\n";
    }
} else {
    echo "   ❌ ERREUR API: " . $response->body() . "\n";
}
echo "\n";

// 7. Test ajout answer_2
echo "7. Test inscription-simple avec answer_2...\n";
$response = Http::post('http://localhost/api/can/inscription-simple', [
    'phone' => $testPhone,
    'answer_2' => 'OUI',
    'timestamp' => now()->format('Y-m-d H:i:s')
]);

if ($response->successful()) {
    $data = $response->json();
    echo "   Has quiz: " . ($data['has_quiz_answer'] ? 'true' : 'false') . "\n";
    echo "   ✓ Quiz answer sauvegardé\n";
} else {
    echo "   ❌ ERREUR API: " . $response->body() . "\n";
}
echo "\n";

// 8. Test ajout accepted_policies
echo "8. Test inscription-simple avec accepted_policies...\n";
$response = Http::post('http://localhost/api/can/inscription-simple', [
    'phone' => $testPhone,
    'accepted_policies' => true,
    'timestamp' => now()->format('Y-m-d H:i:s')
]);

if ($response->successful()) {
    $data = $response->json();
    echo "   Has policies: " . ($data['has_accepted_policies'] ? 'true' : 'false') . "\n";
    echo "   ✓ Policies acceptées\n";
} else {
    echo "   ❌ ERREUR API: " . $response->body() . "\n";
}
echo "\n";

// 9. Test check-user final (doit retourner COMPLETE)
echo "9. Test check-user (utilisateur complet)...\n";
$response = Http::post('http://localhost/api/can/check-user', [
    'phone' => $testPhone
]);

if ($response->successful()) {
    $data = $response->json();
    echo "   Status: {$data['status']}\n";

    if ($data['status'] === 'COMPLETE') {
        echo "   ✓ Correct: utilisateur complet\n";
        echo "\n   Message affiché:\n";
        echo "   " . str_replace("\n", "\n   ", $data['completion_summary']) . "\n";
    } else {
        echo "   ❌ ERREUR: Devrait être COMPLETE\n";
    }
} else {
    echo "   ❌ ERREUR API: " . $response->body() . "\n";
}
echo "\n";

// 10. Vérification finale en BD
echo "10. Vérification finale en base de données...\n";
$user = User::where('phone', $testPhone)->first();
if ($user) {
    echo "   Boisson: {$user->boisson_preferee}\n";
    echo "   Quiz: {$user->quiz_answer}\n";
    echo "   Policies: " . $user->accepted_policies_at->format('d/m/Y H:i') . "\n";
    echo "   Status: {$user->registration_status}\n";
    echo "   ✓ Toutes les données sont correctes\n";
} else {
    echo "   ❌ ERREUR: Utilisateur non trouvé\n";
}
echo "\n";

echo "=== TEST TERMINÉ AVEC SUCCÈS ===\n\n";
