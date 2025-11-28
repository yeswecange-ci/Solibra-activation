@extends('admin.layouts.app')

@section('title', 'Confirmer l\'envoi')

@section('content')
<div class="py-6">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('admin.campaigns.show', $campaign) }}" class="text-blue-600 hover:text-blue-800 inline-flex items-center">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Retour
            </a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Confirmer l'envoi</h1>
        </div>

        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Attention</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Vous êtes sur le point d'envoyer un message WhatsApp à <strong>{{ number_format($campaign->total_recipients) }}</strong> destinataires.</p>
                        <p class="mt-1">Cette action ne peut pas être annulée.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Récapitulatif</h2>

            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Campagne</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $campaign->name }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Audience</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($campaign->audience_type === 'all')
                            Tous les utilisateurs
                        @elseif($campaign->audience_type === 'village')
                            Village: {{ $campaign->village->name ?? 'N/A' }}
                        @else
                            Statut: {{ $campaign->audience_status }}
                        @endif
                    </dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Nombre de destinataires</dt>
                    <dd class="mt-1 text-lg font-bold text-blue-600">{{ number_format($campaign->total_recipients) }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Message</dt>
                    <dd class="mt-1">
                        <div class="bg-gray-100 rounded p-3 text-sm text-gray-800 whitespace-pre-wrap max-h-40 overflow-y-auto">{{ $campaign->message }}</div>
                    </dd>
                </div>
            </dl>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Options d'envoi</h2>

            <form action="{{ route('admin.campaigns.send', $campaign) }}" method="POST">
                @csrf

                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="test_mode" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">Mode test (envoyer uniquement à moi-même)</span>
                    </label>
                </div>

                <div class="flex justify-between items-center">
                    <a href="{{ route('admin.campaigns.show', $campaign) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Annuler
                    </a>

                    <button type="submit" class="px-8 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium">
                        Confirmer et Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
