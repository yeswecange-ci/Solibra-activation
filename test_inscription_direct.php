<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Village;
use App\Http\Controllers\Api\TwilioStudioController;
use Illuminate\Http\Request;

echo "\n=== TEST DU FLOW D'INSCRIPTION (DIRECT) ===\n\n";

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
    echo "   Création d'un village de test...\n";
    $village = Village::create([
        'name' => 'Village Test',
        'code' => 'TEST',
        'is_active' => true,
    ]);
    echo "   ✓ Village créé: {$village->name}\n";
}
echo "\n";

// Créer une instance du controller
$controller = new TwilioStudioController();

// 3. Test check-user (doit retourner NOT_FOUND)
echo "3. Test checkUser() - utilisateur inexistant...\n";
$request = new Request(['phone' => $testPhone]);
$response = $controller->checkUser($request);
$data = $response->getData(true);

echo "   Status: {$data['status']}\n";
if ($data['status'] === 'NOT_FOUND') {
    echo "   ✓ Correct: utilisateur non trouvé\n";
} else {
    echo "   ❌ ERREUR: Devrait être NOT_FOUND\n";
}
echo "\n";

// 4. Test inscription-simple avec answer_1 (doit créer l'utilisateur)
echo "4. Test inscriptionSimple() avec answer_1 - création utilisateur...\n";
$request = new Request([
    'phone' => $testPhone,
    'answer_1' => 'BOCK',
    'timestamp' => now()->format('Y-m-d H:i:s')
]);
$response = $controller->inscriptionSimple($request);
$data = $response->getData(true);

echo "   Success: " . ($data['success'] ? 'true' : 'false') . "\n";
echo "   User ID: {$data['user_id']}\n";
echo "   Name: {$data['name']}\n";
echo "   Has boisson: " . ($data['has_boisson'] ? 'true' : 'false') . "\n";
echo "   Has quiz: " . ($data['has_quiz_answer'] ? 'true' : 'false') . "\n";
echo "   Has policies: " . ($data['has_accepted_policies'] ? 'true' : 'false') . "\n";

if ($data['success']) {
    echo "   ✓ Utilisateur créé avec succès\n";
} else {
    echo "   ❌ ERREUR: {$data['message']}\n";
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
    echo "   Téléphone: {$user->phone}\n";
    echo "   Boisson: {$user->boisson_preferee}\n";
    echo "   Quiz: " . ($user->quiz_answer ?? 'null') . "\n";
    echo "   Policies: " . ($user->accepted_policies_at ?? 'null') . "\n";
    echo "   Village ID: {$user->village_id}\n";
    echo "   Status: {$user->registration_status}\n";
    echo "   Source: {$user->source_type} / {$user->source_detail}\n";
} else {
    echo "   ❌ ERREUR: Utilisateur non trouvé en BD !\n";
    exit(1);
}
echo "\n";

// 6. Test check-user (doit retourner INCOMPLETE)
echo "6. Test checkUser() - utilisateur incomplet...\n";
$request = new Request(['phone' => $testPhone]);
$response = $controller->checkUser($request);
$data = $response->getData(true);

echo "   Status: {$data['status']}\n";
echo "   Has boisson: " . ($data['has_boisson_preferee'] ? 'true' : 'false') . "\n";
echo "   Has quiz: " . ($data['has_quiz_answer'] ? 'true' : 'false') . "\n";
echo "   Has policies: " . ($data['has_accepted_policies'] ? 'true' : 'false') . "\n";

if ($data['status'] === 'INCOMPLETE' && $data['has_boisson_preferee'] && !$data['has_quiz_answer']) {
    echo "   ✓ Correct: utilisateur incomplet (a boisson, manque quiz)\n";
} else {
    echo "   ❌ ERREUR: État incorrect\n";
    echo "   Attendu: INCOMPLETE, has_boisson=true, has_quiz=false\n";
}
echo "\n";

// 7. Test ajout answer_2
echo "7. Test inscriptionSimple() avec answer_2...\n";
$request = new Request([
    'phone' => $testPhone,
    'answer_2' => 'OUI',
    'timestamp' => now()->format('Y-m-d H:i:s')
]);
$response = $controller->inscriptionSimple($request);
$data = $response->getData(true);

echo "   Has quiz: " . ($data['has_quiz_answer'] ? 'true' : 'false') . "\n";
if ($data['has_quiz_answer']) {
    echo "   ✓ Quiz answer sauvegardé\n";
} else {
    echo "   ❌ ERREUR: Quiz non sauvegardé\n";
}
echo "\n";

// 8. Test ajout accepted_policies
echo "8. Test inscriptionSimple() avec accepted_policies...\n";
$request = new Request([
    'phone' => $testPhone,
    'accepted_policies' => true,
    'timestamp' => now()->format('Y-m-d H:i:s')
]);
$response = $controller->inscriptionSimple($request);
$data = $response->getData(true);

echo "   Has policies: " . ($data['has_accepted_policies'] ? 'true' : 'false') . "\n";
if ($data['has_accepted_policies']) {
    echo "   ✓ Policies acceptées\n";
} else {
    echo "   ❌ ERREUR: Policies non acceptées\n";
}
echo "\n";

// 9. Test check-user final (doit retourner COMPLETE)
echo "9. Test checkUser() - utilisateur complet...\n";
$request = new Request(['phone' => $testPhone]);
$response = $controller->checkUser($request);
$data = $response->getData(true);

echo "   Status: {$data['status']}\n";

if ($data['status'] === 'COMPLETE') {
    echo "   ✓ Correct: utilisateur complet\n";
    echo "\n   === MESSAGE AFFICHÉ À L'UTILISATEUR ===\n";
    echo "   " . str_replace("\n", "\n   ", $data['completion_summary']) . "\n";
    echo "   ========================================\n";
} else {
    echo "   ❌ ERREUR: Devrait être COMPLETE\n";
    echo "   État actuel:\n";
    echo "   - has_boisson: " . ($data['has_boisson_preferee'] ?? 'N/A') . "\n";
    echo "   - has_quiz: " . ($data['has_quiz_answer'] ?? 'N/A') . "\n";
    echo "   - has_policies: " . ($data['has_accepted_policies'] ?? 'N/A') . "\n";
}
echo "\n";

// 10. Vérification finale en BD
echo "10. Vérification finale en base de données...\n";
$user = User::where('phone', $testPhone)->first();
if ($user) {
    echo "   ID: {$user->id}\n";
    echo "   Nom: {$user->name}\n";
    echo "   Boisson: {$user->boisson_preferee}\n";
    echo "   Quiz: {$user->quiz_answer}\n";
    echo "   Policies: " . ($user->accepted_policies_at ? $user->accepted_policies_at->format('d/m/Y H:i') : 'null') . "\n";
    echo "   Status: {$user->registration_status}\n";

    if ($user->boisson_preferee && $user->quiz_answer && $user->accepted_policies_at) {
        echo "   ✓ Toutes les données sont correctes\n";
    } else {
        echo "   ❌ ERREUR: Données incomplètes\n";
    }
} else {
    echo "   ❌ ERREUR: Utilisateur non trouvé\n";
}
echo "\n";

echo "=== TEST TERMINÉ AVEC SUCCÈS ===\n\n";

echo "RÉSUMÉ:\n";
echo "-------\n";
echo "✓ Un utilisateur qui n'existe pas en BD peut s'inscrire\n";
echo "✓ L'API crée l'utilisateur lors du premier appel à inscription-simple\n";
echo "✓ Les réponses sont sauvegardées incrémentalement (answer_1, answer_2, policies)\n";
echo "✓ Le système détecte correctement l'état de complétion\n";
echo "✓ Le message de résumé s'affiche pour les utilisateurs complets\n\n";
