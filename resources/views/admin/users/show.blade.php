@extends('admin.layouts.app')

@section('title', 'Détails du Joueur')
@section('page-title', $user->name)

@section('content')
<div class="space-y-6">
    <!-- Actions -->
    <div class="flex justify-end space-x-3">
        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline-block" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce joueur ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                Supprimer
            </button>
        </form>
    </div>

    <!-- Informations du joueur -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informations générales</h3>

        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Nom</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Téléphone</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $user->phone }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Boisson préférée</dt>
                <dd class="mt-1">
                    @if($user->boisson_preferee)
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-2 text-orange-500" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/>
                            </svg>
                            <span class="text-sm font-semibold text-orange-600">{{ $user->boisson_preferee }}</span>
                        </div>
                    @else
                        <span class="text-sm text-gray-400 italic">Non renseignée</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Village</dt>
                <dd class="mt-1">
                    @if($user->village)
                        <a href="{{ route('admin.villages.show', $user->village) }}" class="text-blue-600 hover:underline">
                            {{ $user->village->name }}
                        </a>
                    @else
                        <span class="text-gray-500">Non assigné</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Statut</dt>
                <dd class="mt-1">
                    @if($user->is_active)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Inactif</span>
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Inscrit le</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y à H:i') }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">Opt-in le</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ $user->opted_in_at ? $user->opted_in_at->format('d/m/Y à H:i') : 'N/A' }}
                </dd>
            </div>
        </dl>
    </div>

    <!-- Pronostics -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Pronostics ({{ $user->pronostics->count() }})</h3>

        @if($user->pronostics->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Match</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pronostic</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score réel</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Résultat</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($user->pronostics as $pronostic)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $pronostic->match->team_a }} vs {{ $pronostic->match->team_b }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $pronostic->match->match_date->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-semibold text-gray-900">
                                        {{ $pronostic->predicted_score_a }} - {{ $pronostic->predicted_score_b }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($pronostic->match->score_a !== null && $pronostic->match->score_b !== null)
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $pronostic->match->score_a }} - {{ $pronostic->match->score_b }}
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($pronostic->is_winner)
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Gagnant</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Perdu</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-gray-500 text-sm">Aucun pronostic</p>
        @endif
    </div>

    <!-- Lots gagnés -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Lots gagnés ({{ $user->prizes->count() }})</h3>

        @if($user->prizes->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($user->prizes as $prize)
                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-900">{{ $prize->name }}</h4>
                        <p class="text-sm text-gray-500 mt-1">{{ $prize->description }}</p>
                        <div class="mt-2">
                            @if($prize->pivot->collected_at)
                                <span class="text-xs text-green-600">Récupéré le {{ \Carbon\Carbon::parse($prize->pivot->collected_at)->format('d/m/Y') }}</span>
                            @else
                                <span class="text-xs text-orange-600">En attente de récupération</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-sm">Aucun lot gagné</p>
        @endif
    </div>
</div>
@endsection
