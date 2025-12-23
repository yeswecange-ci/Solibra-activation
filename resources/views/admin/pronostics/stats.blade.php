@extends('admin.layouts.app')

@section('title', 'Statistiques Pronostics')
@section('page-title', 'Statistiques des Pronostics')

@section('content')
<div class="space-y-6">
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

    <!-- ✅ NOUVEAU : Alerte pronostics non évalués -->
    @if(isset($stats['unevaluated_count']) && $stats['unevaluated_count'] > 0)
        <div class="bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200 rounded-lg shadow p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-orange-900 mb-2 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        Action requise
                    </h3>
                    <p class="text-orange-800 mb-3">
                        ⚠️ Il y a <strong>{{ $stats['unevaluated_count'] }} pronostic(s) non évalué(s)</strong> pour des matchs terminés.
                    </p>
                    <p class="text-sm text-orange-700">
                        Cliquez sur le bouton ci-contre pour réévaluer tous les pronostics avec la nouvelle logique 
                        (reconnaissance correcte des matchs nuls).
                    </p>
                </div>
                
                <div class="ml-4">
                    <form method="POST" action="{{ route('admin.pronostics.reevaluate-all') }}" 
                          onsubmit="return confirm('⚠️ CONFIRMATION REQUISE\n\nCette action va réévaluer TOUS les pronostics de tous les matchs terminés.\n\nLes points des utilisateurs seront recalculés.\n\nContinuer ?');">
                        @csrf
                        <button type="submit" class="flex items-center px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition shadow-md hover:shadow-lg whitespace-nowrap">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Réévaluer tous les pronostics
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats globales -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Pronostics</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($stats['total_pronostics']) }}</h3>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pronostics Gagnants</p>
                    <h3 class="text-3xl font-bold text-green-600 mt-2">{{ number_format($stats['total_winners']) }}</h3>
                    @if($stats['total_pronostics'] > 0)
                        <p class="text-xs text-gray-500 mt-1">
                            {{ round($stats['total_winners'] / $stats['total_pronostics'] * 100, 1) }}% de réussite
                        </p>
                    @endif
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- ✅ NOUVEAU : Carte Scores Exacts -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Scores Exacts</p>
                    <h3 class="text-3xl font-bold text-yellow-600 mt-2">{{ number_format($stats['total_exact_scores'] ?? 0) }}</h3>
                    @if($stats['total_winners'] > 0)
                        <p class="text-xs text-gray-500 mt-1">
                            {{ round(($stats['total_exact_scores'] ?? 0) / $stats['total_winners'] * 100, 1) }}% des gagnants
                        </p>
                    @endif
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Points Distribués</p>
                    <h3 class="text-3xl font-bold text-purple-600 mt-2">
                        {{ number_format($stats['total_points_distributed'] ?? 0) }} pts
                    </h3>
                    @if($stats['total_pronostics'] > 0)
                        <p class="text-xs text-gray-500 mt-1">
                            Moy. {{ round(($stats['total_points_distributed'] ?? 0) / $stats['total_pronostics'], 1) }} pts/prono
                        </p>
                    @endif
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Joueurs -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">🏆 Top 10 Joueurs</h3>
                <p class="text-sm text-gray-500">Classés par nombre de points</p>
            </div>
            <div class="p-6">
                @if($stats['top_users']->count() > 0)
                    <div class="space-y-3">
                        @foreach($stats['top_users'] as $index => $user)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <span class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full font-bold
                                        {{ $index === 0 ? 'bg-yellow-100 text-yellow-700' : ($index === 1 ? 'bg-gray-200 text-gray-700' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700')) }}">
                                        {{ $index + 1 }}
                                    </span>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $user->name }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $user->total_wins ?? 0 }} victoire(s) / {{ $user->total_pronostics ?? 0 }} prono(s)
                                            @if(isset($user->exact_scores) && $user->exact_scores > 0)
                                                • <span class="text-yellow-600 font-semibold">{{ $user->exact_scores }} exact(s)</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-green-600">{{ $user->total_points ?? 0 }} pts</div>
                                    <div class="text-xs text-gray-500">points</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 py-8">Aucun gagnant pour le moment</p>
                @endif
            </div>
        </div>

        <!-- Pronostics par Match -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">📊 Pronostics par Match</h3>
            </div>
            <div class="p-6">
                @if($stats['by_match']->count() > 0)
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($stats['by_match'] as $match)
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-medium text-gray-900">
                                        {{ $match->team_a }} vs {{ $match->team_b }}
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ $match->total_pronostics ?? 0 }} prono(s)
                                        </span>
                                        @if(isset($match->total_winners) && $match->total_winners > 0)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $match->total_winners }} gagnant(s)
                                            </span>
                                        @endif
                                        @if(isset($match->exact_scores) && $match->exact_scores > 0)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                🎯 {{ $match->exact_scores }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $match->match_date->format('d/m/Y à H:i') }}
                                    @if($match->status === 'finished')
                                        • Score: {{ $match->score_a ?? '-' }} - {{ $match->score_b ?? '-' }}
                                    @endif
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('admin.matches.show', $match) }}" class="text-sm text-blue-600 hover:underline">
                                        → Voir les détails
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 py-8">Aucun pronostic enregistré</p>
                @endif
            </div>
        </div>
    </div>

    <!-- ✅ NOUVEAU : Légende du système de points -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Système de Points
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <span class="text-2xl mr-2">🎯</span>
                    <span class="font-bold text-yellow-600">10 points</span>
                </div>
                <p class="text-sm text-gray-600">Score exact prédit (ex: 2-1 prédit, 2-1 réel)</p>
            </div>
            <div class="bg-white rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <span class="text-2xl mr-2">✅</span>
                    <span class="font-bold text-green-600">5 points</span>
                </div>
                <p class="text-sm text-gray-600">Bon résultat (ex: match nul prédit, 1-1 réel)</p>
            </div>
            <div class="bg-white rounded-lg p-4">
                <div class="flex items-center mb-2">
                    <span class="text-2xl mr-2">❌</span>
                    <span class="font-bold text-gray-600">0 points</span>
                </div>
                <p class="text-sm text-gray-600">Mauvais pronostic</p>
            </div>
        </div>
    </div>

    <!-- Retour -->
    <div class="flex justify-center">
        <a href="{{ route('admin.pronostics.index') }}" class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            ← Retour à la liste des pronostics
        </a>
    </div>
</div>
@endsection