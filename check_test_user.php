<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

$testPhone = '+243999888777';

echo "Recherche de l'utilisateur avec le numéro: $testPhone\n";
echo "=======================================================\n\n";

$user = User::where('phone', $testPhone)->first();

if ($user) {
    echo "Utilisateur trouvé!\n";
    echo "ID: " . $user->id . "\n";
    echo "Nom: " . $user->name . "\n";
    echo "Téléphone: " . $user->phone . "\n";
    echo "Boisson préférée: " . ($user->boisson_preferee ?? 'NULL') . "\n";
    echo "Status inscription: " . $user->registration_status . "\n";
    echo "Is active: " . ($user->is_active ? 'true' : 'false') . "\n";
    echo "Village ID: " . ($user->village_id ?? 'NULL') . "\n";
    echo "\n";

    // Test de la valeur has_boisson_preferee
    $hasBoissonPreferee = !empty($user->boisson_preferee);
    echo "has_boisson_preferee: " . ($hasBoissonPreferee ? 'true' : 'false') . "\n";
} else {
    echo "Aucun utilisateur trouvé avec ce numéro.\n";
}
