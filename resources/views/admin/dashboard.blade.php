@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Inscrits -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Inscrits</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($totalUsers) }}</h3>
                    <p class="{{ $userGrowthPercent >= 0 ? 'text-green-600' : 'text-red-600' }} text-sm mt-2">
                        {{ $userGrowthPercent >= 0 ? '+' : '' }}{{ $userGrowthPercent }}% cette semaine
                    </p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Villages CAN -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Villages CAN</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ $totalVillages }}</h3>
                    <p class="text-gray-500 text-sm mt-2">Actifs</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pronostics Actifs -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Pronostics</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($pronosticsThisWeek) }}</h3>
                    <p class="text-gray-500 text-sm mt-2">Cette semaine ({{ $participationRate }}% participation)</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Messages Envoyés -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Messages Envoyés</p>
                    <h3 class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($totalMessages) }}</h3>
                    <p class="text-gray-500 text-sm mt-2">Via WhatsApp ({{ $deliveryRate }}% délivrés)</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques - Ligne 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Graphique Inscriptions (7 derniers jours) -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Évolution des Inscriptions (7 derniers jours)</h2>
            <canvas id="registrationChart" height="80"></canvas>
        </div>

        <!-- Graphique Sources -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Répartition par Source</h2>
            <canvas id="sourceChart" height="80"></canvas>
        </div>
    </div>

    <!-- Graphiques - Ligne 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Graphique Top Villages -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Top 5 Villages</h2>
            <canvas id="villageChart" height="80"></canvas>
        </div>

        <!-- Taux de Livraison Messages -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Taux de Livraison Messages</h2>
            <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-600 bg-green-200">
                            {{ $messagesDelivered }} / {{ $totalMessages }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold inline-block text-green-600">
                            {{ $deliveryRate }}%
                        </span>
                    </div>
                </div>
                <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200">
                    <div style="width: {{ $deliveryRate }}%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"></div>
                </div>
            </div>

            <!-- Stats supplémentaires -->
            <div class="grid grid-cols-2 gap-4 mt-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Total Envoyés</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalMessages) }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">Délivrés</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($messagesDelivered) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Prochains Matchs -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Prochains Matchs</h3>
            </div>
            <div class="p-6">
                @if($upcomingMatches->isEmpty())
                    <div class="text-center text-gray-500 py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p>Aucun match programmé</p>
                        <a href="{{ route('admin.matches.create') }}" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Ajouter un match
                        </a>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($upcomingMatches as $match)
                            <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">{{ $match->team_a }} vs {{ $match->team_b }}</p>
                                    <p class="text-sm text-gray-500">{{ $match->match_date->format('d/m/Y à H:i') }}</p>
                                </div>
                                @if($match->pronostic_enabled)
                                    <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Pronostics ouverts</span>
                                @else
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">Pronostics fermés</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('admin.matches.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">Voir tous les matchs →</a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Campagnes Planifiées -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800">Campagnes Planifiées</h3>
            </div>
            <div class="p-6">
                @if($plannedCampaigns->isEmpty())
                    <div class="text-center text-gray-500 py-8">
                        <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                        <p>Aucune campagne planifiée</p>
                        <p class="text-sm mt-2">(Module campagnes à venir)</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($plannedCampaigns as $campaign)
                            <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-gray-50">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-800">{{ $campaign->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $campaign->scheduled_at ? $campaign->scheduled_at->format('d/m/Y à H:i') : 'Non planifiée' }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded">{{ ucfirst($campaign->status) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Actions Rapides</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.villages.create') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="text-sm font-medium text-gray-600">Nouveau Village</span>
            </a>

            <a href="{{ route('admin.matches.create') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="text-sm font-medium text-gray-600">Nouveau Match</span>
            </a>

            <a href="{{ route('admin.users.index') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <span class="text-sm font-medium text-gray-600">Utilisateurs</span>
            </a>

            <a href="{{ route('admin.qrcodes.create') }}" class="flex flex-col items-center justify-center p-4 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition">
                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span class="text-sm font-medium text-gray-600">Générer QR</span>
            </a>
        </div>
    </div>
</div>

<script>
// 1. Graphique des Inscriptions (7 derniers jours)
const regData = @json($registrationChart);
const regLabels = regData.map(d => {
    const date = new Date(d.date);
    return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
});
const regCounts = regData.map(d => d.count);

new Chart(document.getElementById('registrationChart'), {
    type: 'line',
    data: {
        labels: regLabels,
        datasets: [{
            label: 'Nouvelles Inscriptions',
            data: regCounts,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// 2. Graphique des Sources d'inscription
const sourceData = @json($sourceStats);
const sourceLabels = sourceData.map(d => d.source_type || 'Autre');
const sourceCounts = sourceData.map(d => d.count);

new Chart(document.getElementById('sourceChart'), {
    type: 'doughnut',
    data: {
        labels: sourceLabels,
        datasets: [{
            data: sourceCounts,
            backgroundColor: [
                'rgb(59, 130, 246)',
                'rgb(16, 185, 129)',
                'rgb(251, 191, 36)',
                'rgb(239, 68, 68)',
                'rgb(139, 92, 246)',
                'rgb(236, 72, 153)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { position: 'bottom' }
        }
    }
});

// 3. Graphique des Top Villages
const villageData = @json($topVillages);
const villageLabels = villageData.map(v => v.name);
const villageCounts = villageData.map(v => v.users_count);

new Chart(document.getElementById('villageChart'), {
    type: 'bar',
    data: {
        labels: villageLabels,
        datasets: [{
            label: 'Nombre d\'inscrits',
            data: villageCounts,
            backgroundColor: 'rgb(34, 197, 94)',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        plugins: {
            legend: { display: false }
        },
        scales: {
            x: { beginAtZero: true }
        }
    }
});
</script>
@endsection
