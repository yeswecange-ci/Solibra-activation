@extends('admin.layouts.app')

@section('title', 'Analytics')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">📊 Analytics</h1>
            <div class="space-x-2">
                <a href="{{ route('admin.analytics.export.users') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-block">
                    Exporter Utilisateurs (CSV)
                </a>
                <a href="{{ route('admin.analytics.export.pronostics') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-block">
                    Exporter Pronostics (CSV)
                </a>
            </div>
        </div>

        <!-- Funnel de Conversion -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">📈 Funnel de Conversion</h2>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600">{{ number_format($funnel['scans']) }}</div>
                    <div class="text-gray-600">Scans QR</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">{{ number_format($funnel['optins']) }}</div>
                    <div class="text-gray-600">Opt-ins</div>
                    <div class="text-sm text-gray-500">{{ $funnel['optin_rate'] }}%</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600">{{ number_format($funnel['inscriptions']) }}</div>
                    <div class="text-gray-600">Inscriptions</div>
                    <div class="text-sm text-gray-500">{{ $funnel['inscription_rate'] }}%</div>
                </div>
            </div>
        </div>

        <!-- Sources -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">📲 Inscriptions par Source</h2>
            <div class="space-y-2">
                @if($sourceStats->count() > 0)
                    @foreach($sourceStats as $stat)
                        <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                            <span class="font-medium">{{ $stat->source_type }}</span>
                            <span class="font-bold text-blue-600">{{ $stat->count }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-gray-500 py-4">
                        <p>Aucune inscription avec source pour le moment</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Messages WhatsApp -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">💬 Messages WhatsApp</h2>
            <div class="grid grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-600">{{ number_format($messageStats['total']) }}</div>
                    <div class="text-gray-600">Total envoyés</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($messageStats['delivered']) }}</div>
                    <div class="text-gray-600">Délivrés</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ number_format($messageStats['failed']) }}</div>
                    <div class="text-gray-600">Échecs</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
