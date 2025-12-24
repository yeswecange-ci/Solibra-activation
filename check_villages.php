<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Village;

echo "Vérification des villages actifs\n";
echo "==================================\n\n";

$villages = Village::where('is_active', true)->get();

if ($villages->count() > 0) {
    echo "Villages actifs trouvés: " . $villages->count() . "\n\n";

    foreach ($villages as $village) {
        echo "ID: " . $village->id . " | Nom: " . $village->name . "\n";
    }
} else {
    echo "AUCUN village actif trouvé!\n";
    echo "Il faut créer au moins un village actif pour pouvoir inscrire des utilisateurs.\n";
}

echo "\n";
