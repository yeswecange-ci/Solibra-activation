<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\FootballMatch;
use App\Models\Pronostic;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "\n";
echo "═══════════════════════════════════════════════════════\n";
echo "  TEST DE LA LOGIQUE DES PRONOSTICS\n";
echo "═══════════════════════════════════════════════════════\n\n";

// 1. Vérifier les matchs existants
echo "📊 ÉTAT ACTUEL DE LA BASE DE DONNÉES\n";
echo "─────────────────────────────────────────────────────\n";
$totalMatches = FootballMatch::count();
$totalPronostics = Pronostic::count();
$totalUsers = User::count();

echo "Total matchs: {$totalMatches}\n";
echo "Total pronostics: {$totalPronostics}\n";
echo "Total utilisateurs: {$totalUsers}\n\n";

// 2. Afficher les matchs terminés
echo "🏆 MATCHS TERMINÉS\n";
echo "─────────────────────────────────────────────────────\n";
$finishedMatches = FootballMatch::where('status', 'finished')
    ->whereNotNull('score_a')
    ->whereNotNull('score_b')
    ->get();

if ($finishedMatches->isEmpty()) {
    echo "❌ Aucun match terminé\n\n";
} else {
    foreach ($finishedMatches as $match) {
        echo "⚽ Match #{$match->id}: {$match->team_a} vs {$match->team_b}\n";
        echo "   Score final: {$match->score_a} - {$match->score_b}\n";
        echo "   Winners calculated: " . ($match->winners_calculated ? 'Oui' : 'Non') . "\n";

        // Compter les pronostics pour ce match
        $pronostics = Pronostic::where('match_id', $match->id)->get();
        $winners = $pronostics->where('is_winner', true)->count();
        $losers = $pronostics->where('is_winner', false)->count();
        $notCalculated = $pronostics->whereNull('is_winner')->count();

        echo "   Total pronostics: {$pronostics->count()}\n";
        echo "   Gagnants: {$winners} | Perdants: {$losers} | Non calculés: {$notCalculated}\n\n";

        // Afficher quelques exemples de pronostics
        if ($pronostics->count() > 0) {
            echo "   Exemples de pronostics:\n";
            foreach ($pronostics->take(5) as $prono) {
                $user = $prono->user;
                $userName = $user ? $user->name : 'Utilisateur inconnu';

                if ($prono->prediction_type) {
                    $predText = "Type: {$prono->prediction_type}";
                } else {
                    $predText = "Score: {$prono->predicted_score_a} - {$prono->predicted_score_b}";
                }

                $status = $prono->is_winner === true ? '✅ Gagné' : ($prono->is_winner === false ? '❌ Perdu' : '⏳ Pas calculé');
                $points = $prono->points_won ?? 0;

                echo "     - {$userName}: {$predText} → {$status} ({$points} pts)\n";
            }
            echo "\n";
        }
    }
}

// 3. Afficher les matchs en cours ou à venir
echo "📅 MATCHS PROGRAMMÉS/EN COURS\n";
echo "─────────────────────────────────────────────────────\n";
$upcomingMatches = FootballMatch::whereIn('status', ['scheduled', 'live'])
    ->orderBy('match_date')
    ->get();

if ($upcomingMatches->isEmpty()) {
    echo "❌ Aucun match programmé\n\n";
} else {
    foreach ($upcomingMatches as $match) {
        echo "⚽ Match #{$match->id}: {$match->team_a} vs {$match->team_b}\n";
        echo "   Date: {$match->match_date}\n";
        echo "   Status: {$match->status}\n";
        echo "   Pronostics activés: " . ($match->pronostic_enabled ? 'Oui' : 'Non') . "\n";

        $pronostics = Pronostic::where('match_id', $match->id)->count();
        echo "   Total pronostics: {$pronostics}\n\n";
    }
}

// 4. Statistiques des utilisateurs
echo "👥 TOP 10 UTILISATEURS (par points)\n";
echo "─────────────────────────────────────────────────────\n";
$topUsers = User::select('users.*')
    ->selectRaw('COALESCE(SUM(pronostics.points_won), 0) as total_points')
    ->selectRaw('COUNT(pronostics.id) as total_pronostics')
    ->selectRaw('SUM(CASE WHEN pronostics.is_winner = 1 THEN 1 ELSE 0 END) as total_wins')
    ->leftJoin('pronostics', 'users.id', '=', 'pronostics.user_id')
    ->groupBy('users.id')
    ->orderByDesc('total_points')
    ->orderByDesc('total_wins')
    ->limit(10)
    ->get();

if ($topUsers->isEmpty() || $topUsers->first()->total_points == 0) {
    echo "❌ Aucun utilisateur avec des points\n\n";
} else {
    $rank = 1;
    foreach ($topUsers as $user) {
        if ($user->total_points == 0) continue;

        $winRate = $user->total_pronostics > 0
            ? round(($user->total_wins / $user->total_pronostics) * 100, 1)
            : 0;

        echo "{$rank}. {$user->name}\n";
        echo "   Points: {$user->total_points} | Pronostics: {$user->total_pronostics} | Gagnés: {$user->total_wins} ({$winRate}%)\n";
        $rank++;
    }
    echo "\n";
}

// 5. Vérifier la logique de calcul
echo "🔍 VÉRIFICATION DE LA LOGIQUE DE CALCUL\n";
echo "─────────────────────────────────────────────────────\n";

// Simuler différents scénarios
$scenarios = [
    [
        'match_score' => ['a' => 2, 'b' => 1],
        'pronostics' => [
            ['score_a' => 2, 'score_b' => 1, 'type' => null, 'expected' => 'exact', 'points' => 10],
            ['score_a' => 3, 'score_b' => 0, 'type' => null, 'expected' => 'good_result', 'points' => 5],
            ['score_a' => 1, 'score_b' => 1, 'type' => null, 'expected' => 'wrong', 'points' => 0],
            ['score_a' => null, 'score_b' => null, 'type' => 'team_a_win', 'expected' => 'good_result', 'points' => 5],
            ['score_a' => null, 'score_b' => null, 'type' => 'team_b_win', 'expected' => 'wrong', 'points' => 0],
            ['score_a' => null, 'score_b' => null, 'type' => 'draw', 'expected' => 'wrong', 'points' => 0],
        ]
    ],
    [
        'match_score' => ['a' => 1, 'b' => 1],
        'pronostics' => [
            ['score_a' => 1, 'score_b' => 1, 'type' => null, 'expected' => 'exact', 'points' => 10],
            ['score_a' => 0, 'score_b' => 0, 'type' => null, 'expected' => 'good_result', 'points' => 5],
            ['score_a' => 2, 'score_b' => 1, 'type' => null, 'expected' => 'wrong', 'points' => 0],
            ['score_a' => null, 'score_b' => null, 'type' => 'draw', 'expected' => 'good_result', 'points' => 5],
            ['score_a' => null, 'score_b' => null, 'type' => 'team_a_win', 'expected' => 'wrong', 'points' => 0],
        ]
    ],
];

foreach ($scenarios as $index => $scenario) {
    $scenarioNum = $index + 1;
    echo "\nScénario {$scenarioNum}: Match se termine {$scenario['match_score']['a']} - {$scenario['match_score']['b']}\n";
    echo str_repeat('─', 60) . "\n";

    foreach ($scenario['pronostics'] as $prono) {
        if ($prono['type']) {
            $pronoText = "Type: {$prono['type']}";
            $result = checkPronosticSimple($prono['type'], $scenario['match_score']['a'], $scenario['match_score']['b']);
        } else {
            $pronoText = "Score: {$prono['score_a']} - {$prono['score_b']}";
            $result = checkPronosticScore(
                $prono['score_a'],
                $prono['score_b'],
                $scenario['match_score']['a'],
                $scenario['match_score']['b']
            );
        }

        $statusIcon = $result['status'] === 'exact' ? '🎯' : ($result['status'] === 'good_result' ? '✅' : '❌');
        $check = ($result['status'] === $prono['expected'] && $result['points'] === $prono['points']) ? '✓' : '✗';

        echo "  {$check} {$pronoText} → {$statusIcon} {$result['status']} ({$result['points']} pts)\n";

        if ($result['status'] !== $prono['expected'] || $result['points'] !== $prono['points']) {
            echo "    ⚠️  ERREUR: Attendu {$prono['expected']} ({$prono['points']} pts)\n";
        }
    }
}

echo "\n";
echo "═══════════════════════════════════════════════════════\n";
echo "  FIN DU TEST\n";
echo "═══════════════════════════════════════════════════════\n\n";

// Fonctions helpers pour simuler la logique de calcul
function getMatchResult($scoreA, $scoreB) {
    if ($scoreA > $scoreB) {
        return 'team_a_win';
    } elseif ($scoreB > $scoreA) {
        return 'team_b_win';
    } else {
        return 'draw';
    }
}

function checkPronosticScore($pronoScoreA, $pronoScoreB, $matchScoreA, $matchScoreB) {
    // Score exact ?
    if ($pronoScoreA == $matchScoreA && $pronoScoreB == $matchScoreB) {
        return ['status' => 'exact', 'points' => 10];
    }

    // Bon résultat (victoire/nul) ?
    $pronoResult = getMatchResult($pronoScoreA, $pronoScoreB);
    $matchResult = getMatchResult($matchScoreA, $matchScoreB);

    if ($pronoResult === $matchResult) {
        return ['status' => 'good_result', 'points' => 5];
    }

    return ['status' => 'wrong', 'points' => 0];
}

function checkPronosticSimple($predictionType, $matchScoreA, $matchScoreB) {
    $matchResult = getMatchResult($matchScoreA, $matchScoreB);

    if ($predictionType === $matchResult) {
        return ['status' => 'good_result', 'points' => 5];
    }

    return ['status' => 'wrong', 'points' => 0];
}
