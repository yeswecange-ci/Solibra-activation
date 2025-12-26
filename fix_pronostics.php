<?php

/**
 * Script pour réévaluer et corriger tous les pronostics des matchs terminés
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pronostic;
use App\Models\FootballMatch;
use Illuminate\Support\Facades\DB;

echo "🔧 CORRECTION DES PRONOSTICS\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Récupérer les matchs terminés avec scores
$matches = FootballMatch::where('status', 'finished')
    ->whereNotNull('score_a')
    ->whereNotNull('score_b')
    ->with('pronostics')
    ->get();

echo "📊 Matchs terminés à traiter : " . $matches->count() . "\n\n";

if ($matches->isEmpty()) {
    echo "✅ Aucun match terminé à traiter.\n";
    exit(0);
}

echo "⚠️  Cette opération va réévaluer TOUS les pronostics des matchs terminés.\n";
echo "Continuer ? (y/n): ";

$handle = fopen("php://stdin", "r");
$line = fgets($handle);
if (trim($line) != 'y') {
    echo "Opération annulée.\n";
    exit(0);
}

DB::beginTransaction();

try {
    $totalEvaluated = 0;
    $totalFixed = 0;
    $totalWinners = 0;
    $totalExactScores = 0;

    foreach ($matches as $match) {
        echo "\n⚽ Match : {$match->team_a} vs {$match->team_b}\n";
        echo "   Score : {$match->score_a} - {$match->score_b}\n";

        $matchFixed = 0;

        foreach ($match->pronostics as $prono) {
            $oldIsWinner = $prono->is_winner;
            $oldPoints = $prono->points_won;

            // Réévaluer le pronostic avec la logique corrigée
            $prono->evaluateResult($match->score_a, $match->score_b);

            $totalEvaluated++;

            // Vérifier si le statut a changé
            if ($oldIsWinner !== $prono->is_winner || $oldPoints !== $prono->points_won) {
                $totalFixed++;
                $matchFixed++;
                echo "   ✓ Pronostic #{$prono->id} corrigé : ";
                echo ($oldIsWinner ? "GAGNANT" : "PERDANT") . " → ";
                echo ($prono->is_winner ? "GAGNANT" : "PERDANT");
                echo " (Points: {$oldPoints} → {$prono->points_won})\n";
            }

            if ($prono->is_winner) {
                $totalWinners++;
                if ($prono->points_won === Pronostic::POINTS_EXACT_SCORE) {
                    $totalExactScores++;
                }
            }
        }

        if ($matchFixed === 0) {
            echo "   ✅ Tous les pronostics étaient déjà corrects\n";
        } else {
            echo "   📝 {$matchFixed} pronostic(s) corrigé(s) pour ce match\n";
        }
    }

    DB::commit();

    echo "\n";
    echo "=" . str_repeat("=", 70) . "\n";
    echo "📈 RÉSUMÉ DE LA CORRECTION\n";
    echo "=" . str_repeat("=", 70) . "\n";
    echo "Total de pronostics évalués : {$totalEvaluated}\n";
    echo "Total de pronostics corrigés : {$totalFixed}\n";
    echo "Total de gagnants (après correction) : {$totalWinners}\n";
    echo "Total de scores exacts (après correction) : {$totalExactScores}\n";
    echo "=" . str_repeat("=", 70) . "\n";

    if ($totalFixed > 0) {
        echo "\n✅ Correction effectuée avec succès !\n";
    } else {
        echo "\n✅ Aucune correction nécessaire. Tous les pronostics étaient déjà corrects.\n";
    }

} catch (\Exception $e) {
    DB::rollBack();
    echo "\n❌ ERREUR lors de la correction : " . $e->getMessage() . "\n";
    echo "Toutes les modifications ont été annulées.\n";
    exit(1);
}
