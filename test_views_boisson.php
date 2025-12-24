<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\User;
use App\Models\Village;

echo "========================================\n";
echo "CRÉATION D'UTILISATEURS DE TEST\n";
echo "========================================\n\n";

// Vérifier/créer un village
$village = Village::where('is_active', true)->first();
if (!$village) {
    $village = Village::create([
        'name' => 'Test Village',
        'location' => 'Test Location',
        'address' => 'Test Address',
        'is_active' => true,
    ]);
    echo "Village créé: {$village->name}\n\n";
}

// Créer plusieurs utilisateurs avec différentes boissons
$boissons = ['Bock', '33 Export', 'Coca Cola', 'Sprite', 'Fanta Orange', null, 'World Cola'];
$noms = ['Jean Dupont', 'Marie Kasai', 'Patrick Lumumba', 'Sophie Kinshasa', 'David Mbala', 'Claire Sans Boisson', 'Thomas Goma'];

echo "Création de 7 utilisateurs de test:\n";
echo "-----------------------------------\n";

foreach ($noms as $index => $nom) {
    // Supprimer l'utilisateur s'il existe déjà
    $phone = '+2439900000' . ($index + 1);
    User::where('phone', $phone)->delete();

    $user = User::create([
        'name' => $nom,
        'phone' => $phone,
        'boisson_preferee' => $boissons[$index],
        'village_id' => $village->id,
        'source_type' => 'DIRECT',
        'source_detail' => 'SANS_QR',
        'registration_status' => 'INSCRIT',
        'opted_in_at' => now(),
        'is_active' => true,
    ]);

    $boissonDisplay = $user->boisson_preferee ?? 'Aucune';
    echo sprintf("✓ %s (ID: %d) - Boisson: %s\n", $user->name, $user->id, $boissonDisplay);
}

echo "\n========================================\n";
echo "TEST DES FILTRES\n";
echo "========================================\n\n";

// Test 1: Tous les utilisateurs
$total = User::count();
echo "Total d'utilisateurs: $total\n";

// Test 2: Utilisateurs avec boisson
$avecBoisson = User::whereNotNull('boisson_preferee')->count();
echo "Utilisateurs avec boisson: $avecBoisson\n";

// Test 3: Utilisateurs sans boisson
$sansBoisson = User::whereNull('boisson_preferee')->count();
echo "Utilisateurs sans boisson: $sansBoisson\n\n";

// Test 4: Liste des boissons disponibles
echo "Boissons disponibles:\n";
$boissonsDisponibles = User::whereNotNull('boisson_preferee')
    ->distinct()
    ->pluck('boisson_preferee')
    ->sort();

foreach ($boissonsDisponibles as $boisson) {
    $count = User::where('boisson_preferee', $boisson)->count();
    echo "  - $boisson: $count joueur(s)\n";
}

echo "\n========================================\n";
echo "TESTS TERMINÉS\n";
echo "========================================\n\n";

echo "Vous pouvez maintenant:\n";
echo "1. Accéder à la page admin des joueurs\n";
echo "2. Voir les boissons préférées affichées\n";
echo "3. Tester les filtres par boisson\n";
echo "4. Consulter les détails d'un joueur\n\n";
