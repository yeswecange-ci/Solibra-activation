<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\FootballMatch;
use App\Models\MessageLog;
use App\Models\Pronostic;
use App\Models\User;
use App\Models\Village;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Inscrits avec variation hebdomadaire
        $totalUsers = User::where('is_active', true)->count();
        $usersThisWeek = User::where('is_active', true)
            ->where('created_at', '>=', now()->subWeek())
            ->count();
        $usersLastWeek = User::where('is_active', true)
            ->whereBetween('created_at', [now()->subWeeks(2), now()->subWeek()])
            ->count();

        $userGrowthPercent = $usersLastWeek > 0
            ? round((($usersThisWeek - $usersLastWeek) / $usersLastWeek) * 100, 1)
            : 0;

        // 2. Villages actifs
        $totalVillages = Village::where('is_active', true)->count();

        // Top 5 villages par nombre d'inscrits
        $topVillages = Village::withCount(['users' => function($query) {
            $query->where('is_active', true);
        }])
        ->having('users_count', '>', 0)
        ->orderByDesc('users_count')
        ->take(5)
        ->get();

        // 3. Pronostics cette semaine
        $pronosticsThisWeek = Pronostic::whereBetween('created_at', [now()->startOfWeek(), now()])
            ->count();

        $totalPronostics = Pronostic::count();

        // Taux de participation (utilisateurs avec au moins 1 pronostic)
        $usersWithPronostics = User::has('pronostics')->where('is_active', true)->count();
        $participationRate = $totalUsers > 0
            ? round(($usersWithPronostics / $totalUsers) * 100, 1)
            : 0;

        // 4. Messages envoyés (MessageLog + CampaignMessage)
        $messageLogTotal = MessageLog::count();
        $messageLogDelivered = MessageLog::where('status', 'delivered')->count();

        $campaignMessageTotal = CampaignMessage::whereIn('status', ['sent', 'delivered', 'failed'])->count();
        $campaignMessageDelivered = CampaignMessage::where('status', 'delivered')->count();

        $totalMessages = $messageLogTotal + $campaignMessageTotal;
        $messagesDelivered = $messageLogDelivered + $campaignMessageDelivered;

        $deliveryRate = $totalMessages > 0
            ? round(($messagesDelivered / $totalMessages) * 100, 1)
            : 0;

        // 5. Prochains matchs (5 prochains)
        $upcomingMatches = FootballMatch::where('status', 'scheduled')
            ->where('match_date', '>=', now())
            ->orderBy('match_date')
            ->take(5)
            ->get();

        // 6. Campagnes planifiées
        $plannedCampaigns = Campaign::whereIn('status', ['draft', 'scheduled'])
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        // 7. Évolution des inscriptions (7 derniers jours)
        $registrationChart = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 8. Statistiques par source (Twilio Studio tracking)
        $sourceStats = User::select('source_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('source_type')
            ->groupBy('source_type')
            ->orderByDesc('count')
            ->get();

        return view('admin.dashboard', compact(
            'totalUsers',
            'userGrowthPercent',
            'totalVillages',
            'topVillages',
            'pronosticsThisWeek',
            'totalPronostics',
            'participationRate',
            'totalMessages',
            'messagesDelivered',
            'deliveryRate',
            'upcomingMatches',
            'plannedCampaigns',
            'registrationChart',
            'sourceStats'
        ));
    }

    /**
     * Export des statistiques détaillées en CSV
     */
    public function exportStats()
    {
        // Préparer le nom du fichier avec la date
        $filename = 'stats_solibra_can_' . now()->format('Y-m-d_His') . '.csv';

        // Headers pour forcer le téléchargement
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() {
            $file = fopen('php://output', 'w');

            // Ajouter le BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // ========================================
            // SECTION 1: INSCRIPTIONS HEBDOMADAIRES
            // ========================================
            fputcsv($file, ['=== INSCRIPTIONS HEBDOMADAIRES ===']);
            fputcsv($file, ['Semaine', 'Date Début', 'Date Fin', 'Nombre d\'Inscrits']);

            // Récupérer les inscriptions groupées par semaine (12 dernières semaines)
            $weeklyRegistrations = User::select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('WEEK(created_at) as week'),
                    DB::raw('MIN(DATE(created_at)) as start_date'),
                    DB::raw('MAX(DATE(created_at)) as end_date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subWeeks(12))
                ->groupBy('year', 'week')
                ->orderBy('year', 'asc')
                ->orderBy('week', 'asc')
                ->get();

            foreach ($weeklyRegistrations as $week) {
                fputcsv($file, [
                    "Semaine {$week->week} - {$week->year}",
                    $week->start_date,
                    $week->end_date,
                    $week->count
                ]);
            }

            // Ligne vide
            fputcsv($file, []);
            fputcsv($file, []);

            // ========================================
            // SECTION 2: STATISTIQUES GLOBALES
            // ========================================
            fputcsv($file, ['=== STATISTIQUES GLOBALES ===']);

            $totalUsers = User::where('is_active', true)->count();
            $totalMatches = FootballMatch::count();
            $totalPronostics = Pronostic::count();
            $usersWithQuizAnswer = User::whereNotNull('quiz_answer')->count();

            fputcsv($file, ['Métrique', 'Valeur']);
            fputcsv($file, ['Total Utilisateurs Actifs', $totalUsers]);
            fputcsv($file, ['Total Matchs Créés', $totalMatches]);
            fputcsv($file, ['Total Pronostics', $totalPronostics]);
            fputcsv($file, ['Utilisateurs ayant répondu au quiz', $usersWithQuizAnswer]);
            fputcsv($file, ['Taux de réponse au quiz', $totalUsers > 0 ? round(($usersWithQuizAnswer / $totalUsers) * 100, 2) . '%' : '0%']);

            // Ligne vide
            fputcsv($file, []);
            fputcsv($file, []);

            // ========================================
            // SECTION 3: STATISTIQUES PAR BOISSON
            // ========================================
            fputcsv($file, ['=== STATISTIQUES PAR BOISSON ===']);
            fputcsv($file, ['Boisson', 'Nombre de Clients', 'Pourcentage']);

            $drinkStats = User::select('boisson_preferee', DB::raw('COUNT(*) as count'))
                ->whereNotNull('boisson_preferee')
                ->where('is_active', true)
                ->groupBy('boisson_preferee')
                ->orderByDesc('count')
                ->get();

            $totalUsersWithDrink = $drinkStats->sum('count');

            foreach ($drinkStats as $drink) {
                $percentage = $totalUsersWithDrink > 0
                    ? round(($drink->count / $totalUsersWithDrink) * 100, 2)
                    : 0;
                fputcsv($file, [
                    $drink->boisson_preferee,
                    $drink->count,
                    $percentage . '%'
                ]);
            }

            // Utilisateurs sans boisson
            $noFavoriteDrink = User::where('is_active', true)
                ->whereNull('boisson_preferee')
                ->count();

            if ($noFavoriteDrink > 0) {
                $percentageNoDrink = $totalUsers > 0
                    ? round(($noFavoriteDrink / $totalUsers) * 100, 2)
                    : 0;
                fputcsv($file, [
                    'Pas de boisson sélectionnée',
                    $noFavoriteDrink,
                    $percentageNoDrink . '%'
                ]);
            }

            // Total
            fputcsv($file, []);
            fputcsv($file, [
                'TOTAL',
                $totalUsersWithDrink + $noFavoriteDrink,
                '100%'
            ]);

            // Ligne vide
            fputcsv($file, []);
            fputcsv($file, []);

            // ========================================
            // SECTION 4: STATISTIQUES PAR MATCH
            // ========================================
            fputcsv($file, ['=== STATISTIQUES PAR MATCH ===']);
            fputcsv($file, [
                'ID Match',
                'Équipe A',
                'Équipe B',
                'Date du Match',
                'Statut',
                'Score A',
                'Score B',
                'Total Pronostics',
                'Gagnants',
                'Perdants',
                'Taux de Réussite (%)'
            ]);

            $matches = FootballMatch::withCount('pronostics')
                ->orderBy('match_date', 'desc')
                ->get();

            foreach ($matches as $match) {
                $totalPronostics = $match->pronostics_count;
                $winners = 0;
                $losers = 0;

                if ($match->status === 'finished') {
                    $winners = Pronostic::where('match_id', $match->id)
                        ->where('is_winner', true)
                        ->count();
                    $losers = Pronostic::where('match_id', $match->id)
                        ->where('is_winner', false)
                        ->count();
                }

                $successRate = $totalPronostics > 0 ? round(($winners / $totalPronostics) * 100, 2) : 0;

                fputcsv($file, [
                    $match->id,
                    $match->team_a,
                    $match->team_b,
                    $match->match_date->format('d/m/Y H:i'),
                    $match->status,
                    $match->score_a ?? '-',
                    $match->score_b ?? '-',
                    $totalPronostics,
                    $winners,
                    $losers,
                    $successRate
                ]);
            }

            // Ligne vide
            fputcsv($file, []);
            fputcsv($file, []);

            // ========================================
            // SECTION 5: DÉTAILS DES PRONOSTICS PAR MATCH
            // ========================================
            fputcsv($file, ['=== DÉTAILS DES PRONOSTICS PAR MATCH ===']);

            foreach ($matches as $match) {
                fputcsv($file, []);
                fputcsv($file, ["Match: {$match->team_a} vs {$match->team_b} - {$match->match_date->format('d/m/Y H:i')}"]);
                fputcsv($file, ['Type de Pronostic', 'Nombre de Joueurs']);

                // Statistiques par type de pronostic
                $pronosticStats = Pronostic::where('match_id', $match->id)
                    ->select('prediction_type', DB::raw('COUNT(*) as count'))
                    ->whereNotNull('prediction_type')
                    ->groupBy('prediction_type')
                    ->get();

                foreach ($pronosticStats as $stat) {
                    $predictionLabel = match($stat->prediction_type) {
                        'team_a_win' => "Victoire {$match->team_a}",
                        'team_b_win' => "Victoire {$match->team_b}",
                        'draw' => 'Match Nul',
                        default => $stat->prediction_type
                    };

                    fputcsv($file, [$predictionLabel, $stat->count]);
                }

                // Pronostics par score exact (top 5)
                $scorePronostics = Pronostic::where('match_id', $match->id)
                    ->select(
                        'predicted_score_a',
                        'predicted_score_b',
                        DB::raw('COUNT(*) as count')
                    )
                    ->whereNotNull('predicted_score_a')
                    ->whereNotNull('predicted_score_b')
                    ->groupBy('predicted_score_a', 'predicted_score_b')
                    ->orderByDesc('count')
                    ->take(5)
                    ->get();

                if ($scorePronostics->count() > 0) {
                    fputcsv($file, []);
                    fputcsv($file, ['Score Prédit', 'Nombre de Joueurs']);
                    foreach ($scorePronostics as $score) {
                        fputcsv($file, [
                            "{$score->predicted_score_a} - {$score->predicted_score_b}",
                            $score->count
                        ]);
                    }
                }
            }

            // Ligne vide
            fputcsv($file, []);
            fputcsv($file, []);

            // ========================================
            // SECTION 6: STATISTIQUES QUIZ
            // ========================================
            fputcsv($file, ['=== RÉPONSES AU QUIZ ===']);
            fputcsv($file, ['Réponse', 'Nombre de Joueurs']);

            $quizStats = User::select('quiz_answer', DB::raw('COUNT(*) as count'))
                ->whereNotNull('quiz_answer')
                ->groupBy('quiz_answer')
                ->orderByDesc('count')
                ->get();

            foreach ($quizStats as $quiz) {
                fputcsv($file, [$quiz->quiz_answer, $quiz->count]);
            }

            // Utilisateurs sans réponse
            $noQuizAnswer = User::whereNull('quiz_answer')->count();
            fputcsv($file, ['Pas de réponse', $noQuizAnswer]);

            // Ligne vide
            fputcsv($file, []);
            fputcsv($file, []);

            // ========================================
            // SECTION 7: INSCRIPTIONS PAR DATE (DÉTAILLÉ)
            // ========================================
            fputcsv($file, ['=== INSCRIPTIONS PAR JOUR (30 DERNIERS JOURS) ===']);
            fputcsv($file, ['Date', 'Nombre d\'Inscrits']);

            $dailyRegistrations = User::select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get();

            foreach ($dailyRegistrations as $day) {
                fputcsv($file, [$day->date, $day->count]);
            }

            // Footer
            fputcsv($file, []);
            fputcsv($file, []);
            fputcsv($file, ['Rapport généré le', now()->format('d/m/Y à H:i:s')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
