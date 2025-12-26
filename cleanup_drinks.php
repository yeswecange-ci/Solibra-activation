<?php

/**
 * Script pour nettoyer les anciennes données de boisson
 * Convertit les numéros (1, 2, 3, etc.) en noms de boisson
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

// Mapping des boissons
$drinksMapping = [
    '1' => 'Flag',
    '2' => 'Castel',
    '3' => 'Awooyo',
    '4' => 'Beaufort',
    '5' => 'Guinness',
    'guiness' => 'Guinness',
];

echo "🔍 Recherche des utilisateurs avec des numéros comme boisson...\n\n";

// Récupérer tous les utilisateurs avec boisson_preferee
$users = User::whereNotNull('boisson_preferee')
    ->where('boisson_preferee', '!=', '')
    ->get();

$updated = 0;
$total = $users->count();

echo "📊 Total d'utilisateurs à vérifier : {$total}\n\n";

foreach ($users as $user) {
    $oldDrink = $user->boisson_preferee;
    $normalizedDrink = strtolower(trim($oldDrink));

    // Vérifier si c'est un numéro ou un nom à normaliser
    if (isset($drinksMapping[$normalizedDrink])) {
        $newDrink = $drinksMapping[$normalizedDrink];

        $user->update([
            'boisson_preferee' => $newDrink
        ]);

        $updated++;

        echo "✅ User #{$user->id} ({$user->name}): '{$oldDrink}' → '{$newDrink}'\n";
    }
}

echo "\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "📈 RÉSUMÉ\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "Total vérifiés : {$total}\n";
echo "Total mis à jour : {$updated}\n";
echo "=" . str_repeat("=", 50) . "\n";

if ($updated > 0) {
    echo "\n✅ Nettoyage terminé avec succès !\n";
} else {
    echo "\n✅ Aucune donnée à nettoyer. Tout est déjà normalisé !\n";
}
