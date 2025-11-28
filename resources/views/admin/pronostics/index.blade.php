@extends('admin.layouts.app')

@section('title', 'Pronostics')
@section('page-title', 'Gestion des Pronostics')

@section('content')
<div class="space-y-6">
    <!-- Header et filtres -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-medium text-gray-900">Tous les Pronostics</h3>
                <p class="text-sm text-gray-500">{{ $pronostics->total() }} pronostic(s) au total</p>
            </div>
            <a href="{{ route('admin.pronostics.stats') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                📊 Statistiques
            </a>
        </div>

        <!-- Filtres -->
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Match</label>
                <select name="match_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Tous les matchs</option>
                    @foreach($matches as $match)
                        <option value="{{ $match->id }}" {{ request('match_id') == $match->id ? 'selected' : '' }}>
                            {{ $match->team_a }} vs {{ $match->team_b }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select name="is_winner" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">Tous</option>
                    <option value="1" {{ request('is_winner') === '1' ? 'selected' : '' }}>Gagnants</option>
                    <option value="0" {{ request('is_winner') === '0' ? 'selected' : '' }}>Perdants</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des pronostics -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Match</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pronostic</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Résultat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($pronostics as $prono)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $prono->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $prono->user->phone }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $prono->match->team_a }} vs {{ $prono->match->team_b }}</div>
                            <div class="text-sm text-gray-500">{{ $prono->match->match_date->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-bold text-gray-900">
                                {{ $prono->predicted_score_a }} - {{ $prono->predicted_score_b }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($prono->match->status === 'finished')
                                <span class="text-sm font-bold text-blue-600">
                                    {{ $prono->match->score_a }} - {{ $prono->match->score_b }}
                                </span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($prono->match->status === 'finished')
                                @if($prono->is_winner)
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        ✅ Gagné
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                        ❌ Perdu
                                    </span>
                                @endif
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    ⏳ En attente
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $prono->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <x-action-button type="view" :href="route('admin.pronostics.show', $prono)" />
                            <x-action-button type="delete" :href="route('admin.pronostics.destroy', $prono)" method="DELETE" confirm="Supprimer ce pronostic ?" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            Aucun pronostic trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50">
            {{ $pronostics->links() }}
        </div>
    </div>
</div>
@endsection
