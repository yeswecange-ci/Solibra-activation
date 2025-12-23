@extends('admin.layouts.app')

@section('title', 'Détails du Match')
@section('page-title', $match->team_a . ' vs ' . $match->team_b)

@section('content')
<div class="space-y-6">
    <!-- Actions -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route('admin.matches.edit', $match) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            Modifier
        </a>
        <form action="{{ route('admin.matches.destroy', $match) }}" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce match ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Supprimer
            </button>
        </form>
    </div>

    <!-- Messages de succès/erreur -->
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-900 px-6 py-4 rounded-lg shadow-sm flex items-center">
            <svg class="w-6 h-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-900 px-6 py-4 rounded-lg shadow-sm flex items-center">
            <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
    @endif

    <!-- Informations du match -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations du match</h3>

        <div class="flex items-center justify-center mb-6">
            <div class="text-center">
                <div class="text-3xl font-bold text-gray-900">{{ $match->team_a }}</div>
                @if($match->status === 'finished' && $match->score_a !== null)
                    <div class="text-5xl font-bold text-blue-600 mt-4">{{ $match->score_a }}</div>
                @endif
            </div>

            <div class="mx-8 text-2xl text-gray-400">vs</div>

            <div class="text-center">
                <div class="text-3xl font-bold text-gray-900">{{ $match->team_b }}</div>
                @if($match->status === 'finished' && $match->score_b !== null)
                    <div class="text-5xl font-bold text-blue-600 mt-4">{{ $match->score_b }}</div>
                @endif
            </div>
        </div>

        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div>
                <dt class="text-sm font-medium text-gray-500">Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $match->match_date->format('d/m/Y à H:i') }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Statut</dt>
                <dd class="mt-1">
                    @if($match->status === 'scheduled')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Programmé</span>
                    @elseif($match->status === 'live')
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">En cours</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Terminé</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Pronostics</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $match->pronostic_enabled ? 'Activés' : 'Désactivés' }}
                </dd>
            </div>
        </dl>
    </div>

    <!-- ✅ NOUVEAU : Évaluation des pronostics -->
    @if($match->status === 'finished' && $match->score_a !== null && $match->score_b !== null && $match->pronostics->count() > 0)
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg shadow p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-green-900 mb-2 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Évaluation des pronostics
                    </h3>
                    <p class="text-sm text-green-700 mb-4">
                        Le match est terminé avec le score <strong>{{ $match->score_a }} - {{ $match->score_b }}</strong>. 
                        Vous pouvez maintenant évaluer les {{ $match->pronostics->count() }} pronostic(s) des participants.
                    </p>
                    
                    @php
                        $evaluatedCount = $match->pronostics->whereNotNull('is_winner')->count();
                        $winnersCount = $match->pronostics->where('is_winner', true)->count();
                        $exactScoresCount = $match->pronostics->filter(function($p) use ($match) {
                            return $p->is_winner && 
                                   $p->predicted_score_a == $match->score_a && 
                                   $p->predicted_score_b == $match->score_b;
                        })->count();
                    @endphp
                    
                    @if($evaluatedCount > 0)
                        <div class="bg-white rounded-lg p-4 mb-4">
                            <p class="text-sm text-gray-600 mb-2">📊 <strong>Statistiques actuelles :</strong></p>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500">Évalués</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $evaluatedCount }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Gagnants</p>
                                    <p class="text-lg font-bold text-green-600">{{ $winnersCount }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Scores exacts</p>
                                    <p class="text-lg font-bold text-yellow-600">{{ $exactScoresCount }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                <div class="ml-4">
                    <form method="POST" action="{{ route('admin.matches.evaluate', $match) }}" 
                          onsubmit="return confirm('Confirmer l\'évaluation de {{ $match->pronostics->count() }} pronostic(s) ?\n\nCette action mettra à jour les points de tous les participants.');">
                        @csrf
                        <button type="submit" class="flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-md hover:shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                            {{ $evaluatedCount > 0 ? 'Réévaluer les pronostics' : 'Évaluer les pronostics' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Pronostics -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Pronostics ({{ $match->pronostics->count() }})</h3>
            
            @if($match->pronostics->count() > 0)
                @php
                    $winnersCount = $match->pronostics->where('is_winner', true)->count();
                    $exactScoresCount = $match->pronostics->filter(function($p) use ($match) {
                        return $p->is_winner && 
                               $p->predicted_score_a == $match->score_a && 
                               $p->predicted_score_b == $match->score_b;
                    })->count();
                @endphp
                
                <div class="flex items-center space-x-3">
                    @if($winnersCount > 0)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            ✅ {{ $winnersCount }} gagnant(s)
                        </span>
                    @endif
                    @if($exactScoresCount > 0)
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            🎯 {{ $exactScoresCount }} score(s) exact(s)
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @if($match->pronostics->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joueur</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pronostic</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Résultat</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Points</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($match->pronostics as $pronostic)
                            <tr class="{{ $pronostic->is_winner ? 'bg-green-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $pronostic->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $pronostic->user->phone }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $pronostic->predicted_score_a }} - {{ $pronostic->predicted_score_b }}
                                    </div>
                                    @if($pronostic->prediction_type)
                                        <div class="text-xs text-gray-500">{{ $pronostic->prediction_text }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($pronostic->is_winner === true)
                                        @if($pronostic->predicted_score_a == $match->score_a && $pronostic->predicted_score_b == $match->score_b)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                🎯 Score exact
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                ✅ Gagnant
                                            </span>
                                        @endif
                                    @elseif($pronostic->is_winner === false)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                            ❌ Perdu
                                        </span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            ⏳ Non évalué
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($pronostic->points_won !== null)
                                        <span class="text-sm font-bold {{ $pronostic->points_won > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                            {{ $pronostic->points_won }} pts
                                        </span>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $pronostic->created_at->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-sm">Aucun pronostic pour ce match</p>
        @endif
    </div>

    <!-- Gagnants de lots -->
    @if($match->prizeWinners->count() > 0)
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Gagnants de lots ({{ $match->prizeWinners->count() }})</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($match->prizeWinners as $winner)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-gray-900">{{ $winner->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $winner->user->phone }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-blue-600">{{ $winner->prize->name }}</p>
                                @if($winner->collected_at)
                                    <span class="text-xs text-green-600">Récupéré</span>
                                @else
                                    <span class="text-xs text-orange-600">En attente</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection