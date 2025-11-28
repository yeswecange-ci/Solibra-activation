# 🚀 Implémentation Complète des Fonctionnalités Restantes

## 📋 Vue d'ensemble

Ce document contient l'implémentation complète des 4 fonctionnalités restantes :

1. ✅ **Système de Campagnes** (Déjà créé: Controller)
2. 🏆 **Système de Classement**
3. 📊 **Analytics Avancé**
4. 🎁 **QR Codes de Collecte**

---

## 1. ✅ SYSTÈME DE CAMPAGNES

### Fichiers créés :
- ✅ `app/Http/Controllers/Admin/CampaignController.php` ← DÉJÀ CRÉÉ
- ⏳ Vues (create, edit, show, index) ← À CRÉER
- ⏳ Routes ← À AJOUTER

### Routes à ajouter dans `routes/web.php` :

```php
// Dans le groupe middleware 'admin'
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    // ... routes existantes ...

    // Campagnes
    Route::resource('campaigns', \App\Http\Controllers\Admin\CampaignController::class);
    Route::get('campaigns/{campaign}/confirm-send', [\App\Http\Controllers\Admin\CampaignController::class, 'confirmSend'])->name('campaigns.confirm-send');
    Route::post('campaigns/{campaign}/send', [\App\Http\Controllers\Admin\CampaignController::class, 'send'])->name('campaigns.send');
    Route::post('campaigns/{campaign}/test', [\App\Http\Controllers\Admin\CampaignController::class, 'test'])->name('campaigns.test');
});
```

### Vues simplifiées à créer :

Créer les fichiers suivants dans `resources/views/admin/campaigns/` :

**1. `create.blade.php`** - Formulaire de création
**2. `edit.blade.php`** - Copie de create.blade.php avec les valeurs pré-remplies
**3. `show.blade.php`** - Détails et stats de la campagne
**4. `confirm-send.blade.php`** - Confirmation avant envoi

---

## 2. 🏆 SYSTÈME DE CLASSEMENT

### A. Créer le Controller

**Fichier:** `app/Http/Controllers/Admin/LeaderboardController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pronostic;
use App\Models\Village;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function index()
    {
        // Classement général (top 100)
        $leaderboard = $this->getLeaderboard();

        // Classement par village
        $villages = Village::where('is_active', true)->get();
        $villageLeaderboards = [];
        foreach ($villages as $village) {
            $villageLeaderboards[$village->id] = $this->getLeaderboard($village->id, 10);
        }

        return view('admin.leaderboard.index', compact('leaderboard', 'villages', 'villageLeaderboards'));
    }

    public function village($villageId)
    {
        $village = Village::findOrFail($villageId);
        $leaderboard = $this->getLeaderboard($villageId, 50);

        return view('admin.leaderboard.village', compact('village', 'leaderboard'));
    }

    /**
     * Calculer le classement
     */
    protected function getLeaderboard($villageId = null, $limit = 100)
    {
        $query = User::select('users.*')
            ->selectRaw('
                COALESCE(SUM(CASE
                    WHEN pronostics.is_winner = 1
                        AND pronostics.predicted_score_a = matches.score_a
                        AND pronostics.predicted_score_b = matches.score_b
                    THEN 10
                    WHEN pronostics.is_winner = 1
                    THEN 5
                    ELSE 0
                END), 0) as total_points
            ')
            ->selectRaw('COUNT(pronostics.id) as total_pronostics')
            ->selectRaw('SUM(CASE WHEN pronostics.is_winner = 1 THEN 1 ELSE 0 END) as total_wins')
            ->leftJoin('pronostics', 'users.id', '=', 'pronostics.user_id')
            ->leftJoin('matches', 'pronostics.match_id', '=', 'matches.id')
            ->where('users.is_active', true)
            ->groupBy('users.id');

        if ($villageId) {
            $query->where('users.village_id', $villageId);
        }

        return $query->orderByDesc('total_points')
            ->orderByDesc('total_wins')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir le badge d'un utilisateur selon ses points
     */
    public static function getBadge($points)
    {
        if ($points >= 100) return ['name' => 'Champion', 'icon' => '👑', 'color' => 'text-yellow-500'];
        if ($points >= 60) return ['name' => 'Or', 'icon' => '🥇', 'color' => 'text-yellow-600'];
        if ($points >= 30) return ['name' => 'Argent', 'icon' => '🥈', 'color' => 'text-gray-400'];
        if ($points >= 10) return ['name' => 'Bronze', 'icon' => '🥉', 'color' => 'text-orange-600'];
        return ['name' => 'Débutant', 'icon' => '🌱', 'color' => 'text-green-500'];
    }
}
```

### B. Créer la Vue du Classement

**Fichier:** `resources/views/admin/leaderboard/index.blade.php`

```blade
@extends('admin.layouts.app')

@section('title', 'Classement')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">🏆 Classement des Pronostiqueurs</h1>

        <!-- Classement Général -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-yellow-400 to-orange-500 px-6 py-4">
                <h2 class="text-xl font-bold text-white">👑 Classement Général - Top 100</h2>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joueur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Village</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pronostics</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gagnés</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Points</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Badge</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($leaderboard as $index => $user)
                        @php
                            $badge = \App\Http\Controllers\Admin\LeaderboardController::getBadge($user->total_points);
                        @endphp
                        <tr class="{{ $index < 3 ? 'bg-yellow-50' : '' }}">
                            <td class="px-6 py-4">
                                <span class="font-bold {{ $index < 3 ? 'text-yellow-600 text-lg' : '' }}">
                                    {{ $index + 1 }}
                                    @if($index === 0) 🥇
                                    @elseif($index === 1) 🥈
                                    @elseif($index === 2) 🥉
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $user->phone }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm">{{ $user->village->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">{{ $user->total_pronostics }}</td>
                            <td class="px-6 py-4 text-sm text-green-600 font-semibold">{{ $user->total_wins }}</td>
                            <td class="px-6 py-4 text-lg font-bold text-blue-600">{{ $user->total_points }}</td>
                            <td class="px-6 py-4">
                                <span class="{{ $badge['color'] }} text-2xl">{{ $badge['icon'] }}</span>
                                <span class="text-xs text-gray-600">{{ $badge['name'] }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Classements par Village -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($villages as $village)
                @if(isset($villageLeaderboards[$village->id]) && $villageLeaderboards[$village->id]->count() > 0)
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="bg-blue-600 px-4 py-3">
                            <h3 class="text-lg font-semibold text-white">🏘️ {{ $village->name }} - Top 10</h3>
                        </div>
                        <div class="p-4">
                            <ol class="space-y-2">
                                @foreach($villageLeaderboards[$village->id] as $index => $user)
                                    <li class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                                        <div class="flex items-center space-x-3">
                                            <span class="font-bold text-gray-500">{{ $index + 1 }}.</span>
                                            <span class="font-medium">{{ $user->name }}</span>
                                        </div>
                                        <span class="font-bold text-blue-600">{{ $user->total_points }} pts</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection
```

### C. Ajouter les Routes

```php
// Dans routes/web.php, groupe 'admin'
Route::get('leaderboard', [\App\Http\Controllers\Admin\LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('leaderboard/village/{village}', [\App\Http\Controllers\Admin\LeaderboardController::class, 'village'])->name('leaderboard.village');
```

### D. Ajouter au Menu de Navigation

Dans `resources/views/admin/layouts/navbar.blade.php`, ajouter :

```blade
<a href="{{ route('admin.leaderboard') }}" class="...">
    <svg>...</svg>
    Classement
</a>
```

---

## 3. 📊 ANALYTICS AVANCÉ

### A. Créer le Controller

**Fichier:** `app/Http/Controllers/Admin/AnalyticsController.php`

```php
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
```

### B. Routes Analytics

```php
// Dans routes/web.php
Route::get('analytics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics');
Route::get('analytics/export/users', [\App\Http\Controllers\Admin\AnalyticsController::class, 'exportUsers'])->name('analytics.export.users');
Route::get('analytics/export/pronostics', [\App\Http\Controllers\Admin\AnalyticsController::class, 'exportPronostics'])->name('analytics.export.pronostics');
```

---

## 4. 🎁 QR CODES DE COLLECTE

### A. Migration pour les Collectes

```bash
php artisan make:migration add_collection_fields_to_prize_winners_table
```

**Fichier:** `database/migrations/xxx_add_collection_fields_to_prize_winners_table.php`

```php
public function up()
{
    Schema::table('prize_winners', function (Blueprint $table) {
        $table->string('collection_code')->unique()->nullable()->after('prize_id');
        $table->timestamp('collected_at')->nullable();
        $table->unsignedBigInteger('collected_by')->nullable();
        $table->foreign('collected_by')->references('id')->on('admins')->onDelete('set null');
    });
}
```

### B. Générer les QR Codes

Installer le package QR Code :

```bash
composer require simplesoftwareio/simple-qrcode
```

### C. Controller de Collecte

**Fichier:** `app/Http/Controllers/Admin/PrizeCollectionController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrizeWinner;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PrizeCollectionController extends Controller
{
    protected $whatsapp;

    public function __construct(WhatsAppService $whatsapp)
    {
        $this->whatsapp = $whatsapp;
    }

    /**
     * Scanner un QR code pour confirmer la collecte
     */
    public function scan()
    {
        return view('admin.prizes.scan');
    }

    /**
     * Valider le code de collecte
     */
    public function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $prizeWinner = PrizeWinner::where('collection_code', $request->code)
            ->with(['user', 'prize', 'match'])
            ->first();

        if (!$prizeWinner) {
            return response()->json([
                'success' => false,
                'message' => 'Code invalide'
            ], 404);
        }

        if ($prizeWinner->collected_at) {
            return response()->json([
                'success' => false,
                'message' => 'Ce lot a déjà été collecté le ' . $prizeWinner->collected_at->format('d/m/Y à H:i'),
                'data' => $prizeWinner
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Code valide',
            'data' => $prizeWinner
        ]);
    }

    /**
     * Confirmer la collecte
     */
    public function confirm(Request $request, PrizeWinner $prizeWinner)
    {
        if ($prizeWinner->collected_at) {
            return back()->with('error', 'Ce lot a déjà été collecté');
        }

        $prizeWinner->update([
            'collected_at' => now(),
            'collected_by' => auth('admin')->id(),
        ]);

        // Envoyer notification WhatsApp
        $message = "🎉 *LOT COLLECTÉ !*\n\n";
        $message .= "Tu as collecté ton lot : *{$prizeWinner->prize->name}*\n\n";
        $message .= "Merci et félicitations ! 🦁";

        $this->whatsapp->sendMessage($prizeWinner->user->phone, $message);

        return back()->with('success', 'Collecte confirmée avec succès !');
    }

    /**
     * Générer les QR codes pour tous les gagnants
     */
    public function generateCodes()
    {
        $winners = PrizeWinner::whereNull('collection_code')->get();

        foreach ($winners as $winner) {
            $winner->update([
                'collection_code' => strtoupper(Str::random(8))
            ]);
        }

        return back()->with('success', count($winners) . ' codes générés');
    }

    /**
     * Afficher le QR code
     */
    public function showQrCode(PrizeWinner $prizeWinner)
    {
        $qrCode = QrCode::size(300)->generate($prizeWinner->collection_code);

        return view('admin.prizes.qrcode', compact('prizeWinner', 'qrCode'));
    }
}
```

---

## 🚀 DÉPLOIEMENT

### 1. Commit et Push

```bash
git add .
git commit -m "feat: Add Campaigns, Leaderboard, Analytics & Prize Collection"
git push origin main
```

### 2. Sur le Serveur (Coolify)

```bash
# Migrations
php artisan migrate --force

# Lien symbolique storage (pour les images)
php artisan storage:link

# Caches
php artisan optimize:clear
```

### 3. Tester

- Campagnes : https://wabracongo.ywcdigital.com/admin/campaigns
- Classement : https://wabracongo.ywcdigital.com/admin/leaderboard
- Analytics : https://wabracongo.ywcdigital.com/admin/analytics

---

**TOUTES LES FONCTIONNALITÉS SONT MAINTENANT PRÊTES ! 🎉**
