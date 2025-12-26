<?php

/**
 * Script de debug pour identifier les problèmes d'évaluation des pronostics
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Pronostic;
use App\Models\FootballMatch;

echo "🔍 ANALYSE DES PRONOSTICS PROBLÉMATIQUES\n";
echo "=" . str_repeat("=", 70) . "\n\n";

// Récupérer les matchs terminés
$matches = FootballMatch::where('status', 'finished')
    ->whereNotNull('score_a')
    ->whereNotNull('score_b')
    ->with('pronostics')
    ->get();

echo "📊 Matchs terminés analysés : " . $matches->count() . "\n\n";

$totalProblems = 0;
$details = [];

foreach ($matches as $match) {
    echo "⚽ Match : {$match->team_a} vs {$match->team_b}\n";
    echo "   Score final : {$match->score_a} - {$match->score_b}\n";

    // Déterminer le type de résultat réel
    if ($match->score_a > $match->score_b) {
        $actualResult = 'team_a_win';
        $actualText = "Victoire {$match->team_a}";
    } elseif ($match->score_a < $match->score_b) {
        $actualResult = 'team_b_win';
        $actualText = "Victoire {$match->team_b}";
    } else {
        $actualResult = 'draw';
        $actualText = "Match nul";
    }

    echo "   Résultat réel : {$actualText} ({$actualResult})\n";
    echo "\n";

    foreach ($match->pronostics as $prono) {
        // Vérifier le prediction_type
        $predictionType = $prono->prediction_type;
        $predictedScoreA = $prono->predicted_score_a;
        $predictedScoreB = $prono->predicted_score_b;
        $isWinner = $prono->is_winner;

        // Déterminer ce que devrait être le type prédit
        if ($predictionType) {
            $predictedResult = $predictionType;
        } else {
            if ($predictedScoreA > $predictedScoreB) {
                $predictedResult = 'team_a_win';
            } elseif ($predictedScoreA < $predictedScoreB) {
                $predictedResult = 'team_b_win';
            } else {
                $predictedResult = 'draw';
            }
        }

        // Le pronostic devrait-il être gagnant ?
        $shouldBeWinner = ($actualResult === $predictedResult);

        // Y a-t-il une incohérence ?
        if ($shouldBeWinner !== (bool)$isWinner) {
            $totalProblems++;

            echo "   ❌ PROBLÈME DÉTECTÉ - Pronostic #{$prono->id} (User #{$prono->user_id})\n";
            echo "      prediction_type (DB)     : " . var_export($predictionType, true) . "\n";
            echo "      predicted_score          : {$predictedScoreA} - {$predictedScoreB}\n";
            echo "      Résultat prédit calculé  : {$predictedResult}\n";
            echo "      Résultat réel            : {$actualResult}\n";
            echo "      is_winner (DB)           : " . var_export($isWinner, true) . "\n";
            echo "      DEVRAIT être gagnant     : " . var_export($shouldBeWinner, true) . "\n";
            echo "      points_won (DB)          : {$prono->points_won}\n";
            echo "\n";

            $details[] = [
                'prono_id' => $prono->id,
                'user_id' => $prono->user_id,
                'match' => "{$match->team_a} vs {$match->team_b}",
                'score_final' => "{$match->score_a} - {$match->score_b}",
                'prediction_type_db' => $predictionType,
                'predicted_score' => "{$predictedScoreA} - {$predictedScoreB}",
                'is_winner_db' => $isWinner,
                'should_be_winner' => $shouldBeWinner,
            ];
        }
    }

    echo "\n";
}

echo "=" . str_repeat("=", 70) . "\n";
echo "📈 RÉSUMÉ\n";
echo "=" . str_repeat("=", 70) . "\n";
echo "Total de pronostics avec problème : {$totalProblems}\n\n";

if ($totalProblems > 0) {
    echo "📋 DÉTAILS DES PROBLÈMES:\n\n";
    foreach ($details as $detail) {
        echo "Pronostic #{$detail['prono_id']} - User #{$detail['user_id']}\n";
        echo "  Match: {$detail['match']} (Score: {$detail['score_final']})\n";
        echo "  prediction_type en DB: " . var_export($detail['prediction_type_db'], true) . "\n";
        echo "  Score prédit: {$detail['predicted_score']}\n";
        echo "  is_winner en DB: " . var_export($detail['is_winner_db'], true) . "\n";
        echo "  Devrait être: " . var_export($detail['should_be_winner'], true) . "\n";
        echo "\n";
    }

    echo "\n🔧 Pour corriger, exécute : php fix_pronostics.php\n";
} else {
    echo "✅ Aucun problème détecté ! Tous les pronostics sont corrects.\n";
}
