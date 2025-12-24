<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Village;

echo "========================================\n";
echo "TEST LOCAL DE L'API BOISSON PRÉFÉRÉE\n";
echo "========================================\n\n";

// Créer un village si nécessaire
$village = Village::where('is_active', true)->first();
if (!$village) {
    $village = Village::create([
        'name' => 'Test Village',
        'location' => 'Test Location',
        'address' => 'Test Address',
        'is_active' => true,
    ]);
    echo "Village créé: ID {$village->id}\n\n";
}

// Créer un utilisateur de test
$testPhone = '+243123456789';

// Supprimer l'utilisateur s'il existe déjà
User::where('phone', $testPhone)->delete();

echo "TEST 1: Création d'un utilisateur avec boisson préférée\n";
echo "--------------------------------------------------------\n";

$user = User::create([
    'name' => 'Test User Local',
    'phone' => $testPhone,
    'boisson_preferee' => 'Bock',
    'village_id' => $village->id,
    'source_type' => 'DIRECT',
    'source_detail' => 'SANS_QR',
    'registration_status' => 'INSCRIT',
    'opted_in_at' => now(),
    'is_active' => true,
]);

echo "Utilisateur créé:\n";
echo "  ID: {$user->id}\n";
echo "  Nom: {$user->name}\n";
echo "  Téléphone: {$user->phone}\n";
echo "  Boisson: {$user->boisson_preferee}\n";
echo "  Village ID: {$user->village_id}\n\n";

echo "TEST 2: Vérification de has_boisson_preferee\n";
echo "---------------------------------------------\n";

$hasBoissonPreferee = !empty($user->boisson_preferee);
echo "has_boisson_preferee: " . ($hasBoissonPreferee ? 'true' : 'false') . "\n";
echo "boisson_preferee: " . ($user->boisson_preferee ?? 'null') . "\n\n";

echo "TEST 3: Simulation de la réponse checkUser\n";
echo "------------------------------------------\n";

$response = [
    'status'  => 'INSCRIT',
    'name'    => $user->name,
    'phone'   => $user->phone,
    'user_id' => $user->id,
    'has_boisson_preferee' => !empty($user->boisson_preferee),
    'boisson_preferee' => $user->boisson_preferee,
];

echo json_encode($response, JSON_PRETTY_PRINT) . "\n\n";

echo "TEST 4: Mise à jour de la boisson\n";
echo "----------------------------------\n";

$user->update(['boisson_preferee' => 'Coca Cola']);
$user->refresh();

echo "Nouvelle boisson: {$user->boisson_preferee}\n";
echo "has_boisson_preferee: " . (!empty($user->boisson_preferee) ? 'true' : 'false') . "\n\n";

echo "TEST 5: Utilisateur sans boisson\n";
echo "---------------------------------\n";

$user->update(['boisson_preferee' => null]);
$user->refresh();

echo "Boisson: " . ($user->boisson_preferee ?? 'null') . "\n";
echo "has_boisson_preferee: " . (!empty($user->boisson_preferee) ? 'true' : 'false') . "\n\n";

echo "========================================\n";
echo "TESTS TERMINÉS\n";
echo "========================================\n";
