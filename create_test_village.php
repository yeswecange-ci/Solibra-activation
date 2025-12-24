<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Village;

echo "Création d'un village de test\n";
echo "==============================\n\n";

$village = Village::create([
    'name' => 'Test Village',
    'location' => 'Test Location',
    'address' => 'Test Address',
    'is_active' => true,
]);

echo "Village créé avec succès!\n";
echo "ID: " . $village->id . "\n";
echo "Nom: " . $village->name . "\n";
echo "Location: " . $village->location . "\n";
echo "Actif: " . ($village->is_active ? 'Oui' : 'Non') . "\n";
