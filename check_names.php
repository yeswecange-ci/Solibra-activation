<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

echo "\n=== VERIFICATION DES NOMS AFFICHES ===\n\n";

$users = User::latest()->take(10)->get(['id', 'name', 'phone', 'boisson_preferee', 'quiz_answer', 'created_at']);

echo "Les 10 derniers utilisateurs :\n";
echo "-------------------------------\n\n";

foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Nom: {$user->name}\n";
    echo "Tel: {$user->phone}\n";
    echo "Boisson: " . ($user->boisson_preferee ?? 'non renseignee') . "\n";
    echo "Quiz: " . ($user->quiz_answer ?? 'non renseigne') . "\n";
    echo "Cree: {$user->created_at->format('d/m/Y H:i')}\n";

    // Vérifier si c'est un nom personnalisé ou générique
    if (str_starts_with($user->name, 'Participant_')) {
        echo "Type: NOM GENERIQUE\n";
    } else {
        echo "Type: NOM PERSONNALISE\n";
    }

    echo "-------------------------------\n";
}

echo "\nRESUME:\n";
$totalUsers = User::count();
$genericNames = User::where('name', 'like', 'Participant_%')->count();
$customNames = $totalUsers - $genericNames;

echo "Total utilisateurs: {$totalUsers}\n";
echo "Noms generiques (Participant_XXXX): {$genericNames}\n";
echo "Noms personnalises: {$customNames}\n\n";

// Afficher quelques exemples de noms personnalisés
echo "Exemples de noms personnalises:\n";
$customNameUsers = User::where('name', 'not like', 'Participant_%')
    ->take(5)
    ->get(['id', 'name']);

foreach ($customNameUsers as $user) {
    echo "  - {$user->name} (ID: {$user->id})\n";
}

echo "\n";
