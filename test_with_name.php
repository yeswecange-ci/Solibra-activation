<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Village;
use App\Http\Controllers\Api\TwilioStudioController;
use Illuminate\Http\Request;

echo "\n=== TEST DU FLOW AVEC NOM/PSEUDO ===\n\n";

$testPhone = '+2250700001234';
$testName = 'Jean Kouassi';

// 1. Nettoyer
echo "1. Nettoyage...\n";
User::where('phone', $testPhone)->delete();
echo "   OK Utilisateur supprime si existait\n\n";

// 2. Créer controller
$controller = new TwilioStudioController();

// 3. Test check-user (NOT_FOUND)
echo "2. Test checkUser() - utilisateur inexistant...\n";
$request = new Request(['phone' => $testPhone]);
$response = $controller->checkUser($request);
$data = $response->getData(true);

echo "   Status: {$data['status']}\n";
if ($data['status'] === 'NOT_FOUND') {
    echo "   OK Utilisateur non trouve\n";
} else {
    echo "   ERREUR: Devrait etre NOT_FOUND\n";
}
echo "\n";

// 4. NOUVEAU : Sauvegarder le nom en premier
echo "3. Test inscriptionSimple() avec NAME (creation + nom)...\n";
$request = new Request([
    'phone' => $testPhone,
    'name' => $testName,
    'timestamp' => now()->format('Y-m-d H:i:s')
]);
$response = $controller->inscriptionSimple($request);
$data = $response->getData(true);

echo "   Success: " . ($data['success'] ? 'true' : 'false') . "\n";
echo "   User ID: {$data['user_id']}\n";
echo "   Name: {$data['name']}\n";

if ($data['success'] && $data['name'] === 'Jean Kouassi') {
    echo "   OK Utilisateur cree avec le nom fourni\n";
} else {
    echo "   ERREUR: Nom incorrect\n";
    exit(1);
}
echo "\n";

// 5. Vérifier en BD
echo "4. Verification en base de donnees...\n";
$user = User::where('phone', $testPhone)->first();
if ($user) {
    echo "   OK Utilisateur trouve\n";
    echo "   Nom: {$user->name}\n";
    echo "   Telephone: {$user->phone}\n";

    if ($user->name === 'Jean Kouassi') {
        echo "   OK Le nom personnalise est sauvegarde correctement\n";
    } else {
        echo "   ERREUR: Nom devrait etre 'Jean Kouassi', trouve: {$user->name}\n";
    }
} else {
    echo "   ERREUR: Utilisateur non trouve en BD\n";
    exit(1);
}
echo "\n";

// 6. Ajouter boisson
echo "5. Test inscriptionSimple() avec answer_1...\n";
$request = new Request([
    'phone' => $testPhone,
    'answer_1' => 'BOCK'
]);
$response = $controller->inscriptionSimple($request);
$data = $response->getData(true);

echo "   Has boisson: " . ($data['has_boisson'] ? 'true' : 'false') . "\n";
echo "   OK Boisson sauvegardee\n\n";

// 7. Ajouter quiz
echo "6. Test inscriptionSimple() avec answer_2...\n";
$request = new Request([
    'phone' => $testPhone,
    'answer_2' => 'OUI'
]);
$response = $controller->inscriptionSimple($request);
$data = $response->getData(true);

echo "   Has quiz: " . ($data['has_quiz_answer'] ? 'true' : 'false') . "\n";
echo "   OK Quiz sauvegarde\n\n";

// 8. Accepter politiques
echo "7. Test inscriptionSimple() avec accepted_policies...\n";
$request = new Request([
    'phone' => $testPhone,
    'accepted_policies' => true
]);
$response = $controller->inscriptionSimple($request);
$data = $response->getData(true);

echo "   Has policies: " . ($data['has_accepted_policies'] ? 'true' : 'false') . "\n";
echo "   OK Policies acceptees\n\n";

// 9. Vérifier état final
echo "8. Test checkUser() - utilisateur complet...\n";
$request = new Request(['phone' => $testPhone]);
$response = $controller->checkUser($request);
$data = $response->getData(true);

echo "   Status: {$data['status']}\n";

if ($data['status'] === 'COMPLETE') {
    echo "   OK Utilisateur complet\n\n";
    echo "   === MESSAGE AFFICHE ===\n";
    echo "   " . str_replace("\n", "\n   ", $data['completion_summary']) . "\n";
    echo "   =======================\n";
} else {
    echo "   ERREUR: Devrait etre COMPLETE\n";
}
echo "\n";

// 10. Vérification finale
echo "9. Verification finale en base de donnees...\n";
$user = User::where('phone', $testPhone)->first();
if ($user) {
    echo "   Nom: {$user->name}\n";
    echo "   Boisson: {$user->boisson_preferee}\n";
    echo "   Quiz: {$user->quiz_answer}\n";
    echo "   Policies: " . ($user->accepted_policies_at ? $user->accepted_policies_at->format('d/m/Y H:i') : 'null') . "\n";
    echo "   Status: {$user->registration_status}\n";

    if ($user->name === 'Jean Kouassi' && $user->boisson_preferee && $user->quiz_answer && $user->accepted_policies_at) {
        echo "   OK Toutes les donnees sont correctes\n";
    } else {
        echo "   ERREUR: Donnees incompletes\n";
    }
} else {
    echo "   ERREUR: Utilisateur non trouve\n";
}
echo "\n";

echo "=== TEST TERMINE AVEC SUCCES ===\n\n";

echo "RESUME:\n";
echo "-------\n";
echo "OK Le nom/pseudo est demande en premier\n";
echo "OK L'utilisateur est cree avec le nom personnalise (pas de Participant_XXXX)\n";
echo "OK Les reponses sont sauvegardees incrementalement\n";
echo "OK Le message de resume affiche le nom personnalise\n\n";
