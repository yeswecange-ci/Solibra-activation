<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Village;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "═══════════════════════════════════════════════════════\n";
echo "  TEST D'INSCRIPTION SANS SOURCE\n";
echo "═══════════════════════════════════════════════════════\n\n";

// 1. Vérifier qu'il existe au moins un village actif
echo "📍 ÉTAPE 1 : Vérification des villages\n";
echo "─────────────────────────────────────────────────────\n";
$activeVillages = Village::where('is_active', true)->count();

if ($activeVillages === 0) {
    echo "❌ ERREUR : Aucun village actif trouvé\n";
    echo "Veuillez créer au moins un village actif avant de continuer.\n\n";
    exit(1);
}

echo "✅ {$activeVillages} village(s) actif(s) trouvé(s)\n\n";

// 2. Créer un utilisateur de test SANS source
echo "👤 ÉTAPE 2 : Création d'un utilisateur SANS source\n";
echo "─────────────────────────────────────────────────────\n";

$testPhone = '+243' . rand(800000000, 999999999);
$testName = "Test User " . rand(1000, 9999);

$defaultVillage = Village::where('is_active', true)->first();

try {
    $user = User::create([
        'name' => $testName,
        'phone' => $testPhone,
        'village_id' => $defaultVillage->id,
        // PAS de source_type ni source_detail
        'registration_status' => 'INSCRIT',
        'opted_in_at' => now(),
        'is_active' => true,
    ]);

    echo "✅ Utilisateur créé avec succès !\n\n";
    echo "Détails de l'utilisateur :\n";
    echo "  ID: {$user->id}\n";
    echo "  Nom: {$user->name}\n";
    echo "  Téléphone: {$user->phone}\n";
    echo "  Village: {$user->village->name}\n";
    echo "  Source Type: " . ($user->source_type ?? 'NULL') . "\n";
    echo "  Source Detail: " . ($user->source_detail ?? 'NULL') . "\n";
    echo "  Statut: {$user->registration_status}\n";
    echo "  Actif: " . ($user->is_active ? 'Oui' : 'Non') . "\n\n";

    if ($user->source_type === null && $user->source_detail === null) {
        echo "✅ TEST RÉUSSI : L'utilisateur a été créé sans source\n\n";
    } else {
        echo "⚠️  ATTENTION : Des valeurs de source ont été remplies automatiquement\n\n";
    }

} catch (\Exception $e) {
    echo "❌ ERREUR lors de la création de l'utilisateur :\n";
    echo "   " . $e->getMessage() . "\n\n";
    exit(1);
}

// 3. Créer un utilisateur de test AVEC source
echo "👤 ÉTAPE 3 : Création d'un utilisateur AVEC source\n";
echo "─────────────────────────────────────────────────────\n";

$testPhone2 = '+243' . rand(800000000, 999999999);
$testName2 = "Test User " . rand(1000, 9999);

try {
    $user2 = User::create([
        'name' => $testName2,
        'phone' => $testPhone2,
        'village_id' => $defaultVillage->id,
        'source_type' => 'AFFICHE',
        'source_detail' => 'GOMBE',
        'registration_status' => 'INSCRIT',
        'opted_in_at' => now(),
        'is_active' => true,
    ]);

    echo "✅ Utilisateur créé avec succès !\n\n";
    echo "Détails de l'utilisateur :\n";
    echo "  ID: {$user2->id}\n";
    echo "  Nom: {$user2->name}\n";
    echo "  Téléphone: {$user2->phone}\n";
    echo "  Village: {$user2->village->name}\n";
    echo "  Source Type: {$user2->source_type}\n";
    echo "  Source Detail: {$user2->source_detail}\n";
    echo "  Statut: {$user2->registration_status}\n";
    echo "  Actif: " . ($user2->is_active ? 'Oui' : 'Non') . "\n\n";

    if ($user2->source_type === 'AFFICHE' && $user2->source_detail === 'GOMBE') {
        echo "✅ TEST RÉUSSI : L'utilisateur a été créé avec source\n\n";
    } else {
        echo "❌ ERREUR : Les valeurs de source ne correspondent pas\n\n";
    }

} catch (\Exception $e) {
    echo "❌ ERREUR lors de la création de l'utilisateur :\n";
    echo "   " . $e->getMessage() . "\n\n";
    exit(1);
}

// 4. Statistiques finales
echo "📊 ÉTAPE 4 : Statistiques\n";
echo "─────────────────────────────────────────────────────\n";

$totalUsers = User::count();
$usersWithSource = User::whereNotNull('source_type')->count();
$usersWithoutSource = User::whereNull('source_type')->count();

echo "Total utilisateurs: {$totalUsers}\n";
echo "Avec source: {$usersWithSource}\n";
echo "Sans source: {$usersWithoutSource}\n\n";

// 5. Nettoyage (optionnel)
echo "🧹 NETTOYAGE : Supprimer les utilisateurs de test ? (y/N) : ";
$handle = fopen ("php://stdin","r");
$line = fgets($handle);

if(trim($line) === 'y' || trim($line) === 'Y'){
    $user->delete();
    $user2->delete();
    echo "✅ Utilisateurs de test supprimés\n";
} else {
    echo "ℹ️  Utilisateurs de test conservés\n";
}
fclose($handle);

echo "\n";
echo "═══════════════════════════════════════════════════════\n";
echo "  FIN DU TEST\n";
echo "═══════════════════════════════════════════════════════\n\n";
