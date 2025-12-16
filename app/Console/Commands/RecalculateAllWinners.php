<?php

namespace App\Console\Commands;

use App\Models\FootballMatch;
use App\Models\Pronostic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RecalculateAllWinners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pronostic:recalculate-all {--force : Force le recalcul même si déjà calculé}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculer tous les gagnants de tous les matchs terminés (utilisé après mise à jour du système de points)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Recalcul de tous les gagnants...');

        $force = $this->option('force');

        // Récupérer tous les matchs terminés
        $query = FootballMatch::where('status', 'finished')
            ->whereNotNull('score_a')
            ->whereNotNull('score_b');

        if ($force) {
            $this->warn('⚠️  Mode FORCE activé - Recalcul de tous les matchs même déjà calculés');
        } else {
            $query->where('winners_calculated', false);
        }

        $matches = $query->get();

        if ($matches->isEmpty()) {
            $this->info('✅ Aucun match à recalculer');
            return Command::SUCCESS;
        }

        $this->info("📊 {$matches->count()} match(s) à recalculer");

        $totalWinners = 0;
        $totalPointsDistributed = 0;

        foreach ($matches as $match) {
            $this->line("\n⚽ {$match->team_a} vs {$match->team_b} ({$match->score_a}-{$match->score_b})");

            // Déterminer le résultat du match
            $matchResult = $this->getMatchResult($match);

            // Récupérer tous les pronostics
            $pronostics = Pronostic::where('match_id', $match->id)->get();

            if ($pronostics->isEmpty()) {
                $this->warn("   ⚠️ Aucun pronostic");
                $match->update(['winners_calculated' => true]);
                continue;
            }

            $exactCount = 0;
            $goodResultCount = 0;
            $pointsThisMatch = 0;

            // Recalculer chaque pronostic
            foreach ($pronostics as $prono) {
                $result = $this->checkPronostic($prono, $match, $matchResult);

                if ($result === 'exact') {
                    $prono->update(['is_winner' => true, 'points_won' => 10]);
                    $exactCount++;
                    $pointsThisMatch += 10;
                } elseif ($result === 'good_result') {
                    $prono->update(['is_winner' => true, 'points_won' => 5]);
                    $goodResultCount++;
                    $pointsThisMatch += 5;
                } else {
                    $prono->update(['is_winner' => false, 'points_won' => 0]);
                }
            }

            $winnersCount = $exactCount + $goodResultCount;

            $this->info("   📈 {$pronostics->count()} participant(s)");
            $this->info("   🎯 {$exactCount} score(s) exact(s) (10 pts)");
            $this->info("   ✅ {$goodResultCount} bon(s) résultat(s) (5 pts)");
            $this->info("   💰 {$pointsThisMatch} points distribués");

            $totalWinners += $winnersCount;
            $totalPointsDistributed += $pointsThisMatch;

            // Marquer comme calculé
            $match->update(['winners_calculated' => true]);

            Log::info('Winners recalculated', [
                'match_id' => $match->id,
                'participants' => $pronostics->count(),
                'winners' => $winnersCount,
                'points' => $pointsThisMatch,
            ]);
        }

        $this->newLine();
        $this->info("✅ Recalcul terminé !");
        $this->info("🏆 Total gagnants: {$totalWinners}");
        $this->info("💰 Total points distribués: {$totalPointsDistributed} pts");

        return Command::SUCCESS;
    }

    /**
     * Déterminer le résultat du match
     */
    protected function getMatchResult($match)
    {
        if ($match->score_a > $match->score_b) {
            return 'team_a_win';
        } elseif ($match->score_b > $match->score_a) {
            return 'team_b_win';
        } else {
            return 'draw';
        }
    }

    /**
     * Vérifier un pronostic
     * Retourne: 'exact', 'good_result', ou 'wrong'
     */
    protected function checkPronostic($prono, $match, $matchResult)
    {
        // Mode 1: Pronostic avec scores
        if ($prono->predicted_score_a !== null && $prono->predicted_score_b !== null) {
            // Score exact ?
            if ($prono->predicted_score_a == $match->score_a && $prono->predicted_score_b == $match->score_b) {
                return 'exact';
            }

            // Bon résultat (victoire/nul) ?
            $pronoResult = $this->getResultFromScores($prono->predicted_score_a, $prono->predicted_score_b);
            if ($pronoResult === $matchResult) {
                return 'good_result';
            }

            return 'wrong';
        }

        // Mode 2: Pronostic simple (prediction_type)
        if ($prono->prediction_type) {
            if ($prono->prediction_type === $matchResult) {
                return 'good_result';
            }

            return 'wrong';
        }

        return 'wrong';
    }

    /**
     * Déterminer le résultat à partir de scores
     */
    protected function getResultFromScores($scoreA, $scoreB)
    {
        if ($scoreA > $scoreB) {
            return 'team_a_win';
        } elseif ($scoreB > $scoreA) {
            return 'team_b_win';
        } else {
            return 'draw';
        }
    }
}
