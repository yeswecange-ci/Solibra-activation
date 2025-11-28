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

            @if($leaderboard->count() > 0)
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
            @else
                <div class="p-12 text-center text-gray-500">
                    <p>Aucun pronostic n'a encore été fait</p>
                </div>
            @endif
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
