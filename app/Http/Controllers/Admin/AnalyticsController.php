<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ConversationSession;
use App\Models\Pronostic;
use App\Models\MessageLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index()
    {
        // Taux de conversion du funnel
        $funnel = [
            'scans' => ConversationSession::where('status', 'SCAN')->count(),
            'optins' => ConversationSession::where('status', 'OPT_IN')->count(),
            'inscriptions' => User::whereNotNull('opted_in_at')->count(),
        ];

        // Calculer les taux
        $funnel['optin_rate'] = $funnel['scans'] > 0
            ? round(($funnel['optins'] / $funnel['scans']) * 100, 1)
            : 0;
        $funnel['inscription_rate'] = $funnel['optins'] > 0
            ? round(($funnel['inscriptions'] / $funnel['optins']) * 100, 1)
            : 0;

        // Inscriptions par source
        $sourceStats = User::select('source_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('source_type')
            ->groupBy('source_type')
            ->orderByDesc('count')
            ->get();

        // Engagement par jour de la semaine
        $dayStats = Pronostic::select(
                DB::raw('DAYNAME(created_at) as day'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('day')
            ->orderByRaw("FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')")
            ->get();

        // Messages WhatsApp stats
        $messageStats = [
            'total' => MessageLog::count(),
            'delivered' => MessageLog::where('status', 'delivered')->count(),
            'failed' => MessageLog::where('status', 'failed')->count(),
        ];

        return view('admin.analytics.index', compact('funnel', 'sourceStats', 'dayStats', 'messageStats'));
    }

    /**
     * Export CSV des utilisateurs
     */
    public function exportUsers()
    {
        $users = User::with('village')->where('is_active', true)->get();

        $filename = 'users_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // En-têtes
            fputcsv($file, ['Nom', 'Téléphone', 'Village', 'Source', 'Date inscription', 'Actif']);

            // Données
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->name,
                    $user->phone,
                    $user->village->name ?? '',
                    $user->source_type ?? '',
                    $user->created_at->format('d/m/Y H:i'),
                    $user->is_active ? 'Oui' : 'Non',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export CSV des pronostics
     */
    public function exportPronostics()
    {
        $pronostics = Pronostic::with(['user', 'match'])->get();

        $filename = 'pronostics_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($pronostics) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Utilisateur', 'Match', 'Pronostic', 'Score réel', 'Gagnant', 'Date']);

            foreach ($pronostics as $prono) {
                fputcsv($file, [
                    $prono->user->name,
                    $prono->match->team_a . ' vs ' . $prono->match->team_b,
                    $prono->predicted_score_a . ' - ' . $prono->predicted_score_b,
                    ($prono->match->score_a ?? '-') . ' - ' . ($prono->match->score_b ?? '-'),
                    $prono->is_winner ? 'Oui' : 'Non',
                    $prono->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
