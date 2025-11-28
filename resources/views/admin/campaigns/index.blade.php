@extends('admin.layouts.app')

@section('title', 'Campagnes')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Campagnes WhatsApp</h1>
                <p class="text-gray-600 mt-1">Envoi de messages en masse</p>
            </div>
            <a href="{{ route('admin.campaigns.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nouvelle Campagne
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        @if(session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-6">
                {{ session('info') }}
            </div>
        @endif

        <!-- Liste des campagnes -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($campaigns->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Campagne</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Audience</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinataires</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Envoyés</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($campaigns as $campaign)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $campaign->name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ Str::limit($campaign->message, 60) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @if($campaign->audience_type === 'all')
                                        <span class="text-blue-600">📢 Tous</span>
                                    @elseif($campaign->audience_type === 'village')
                                        <span class="text-green-600">🏘️ {{ $campaign->village->name ?? 'Village' }}</span>
                                    @else
                                        <span class="text-purple-600">🎯 Ciblé</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ number_format($campaign->total_recipients) }}
                                </td>
                                <td class="px-6 py-4">
                                    @if($campaign->status === 'draft')
                                        <span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-200 rounded-full">Brouillon</span>
                                    @elseif($campaign->status === 'scheduled')
                                        <span class="px-2 py-1 text-xs font-semibold text-yellow-700 bg-yellow-200 rounded-full">Programmé</span>
                                    @elseif($campaign->status === 'sending')
                                        <span class="px-2 py-1 text-xs font-semibold text-blue-700 bg-blue-200 rounded-full">En cours</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full">Envoyé</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    @if($campaign->status === 'sent')
                                        <div>
                                            <span class="text-green-600">✓ {{ $campaign->sent_count ?? 0 }}</span>
                                            @if($campaign->failed_count > 0)
                                                <span class="text-red-600 ml-2">✗ {{ $campaign->failed_count }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $campaign->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                    <a href="{{ route('admin.campaigns.show', $campaign) }}" class="text-blue-600 hover:text-blue-900">Voir</a>

                                    @if($campaign->status === 'draft')
                                        <a href="{{ route('admin.campaigns.edit', $campaign) }}" class="text-indigo-600 hover:text-indigo-900">Modifier</a>
                                        <a href="{{ route('admin.campaigns.confirm-send', $campaign) }}" class="text-green-600 hover:text-green-900">Envoyer</a>
                                    @endif

                                    @if(in_array($campaign->status, ['draft', 'sent']))
                                        <form action="{{ route('admin.campaigns.destroy', $campaign) }}" method="POST" class="inline-block" onsubmit="return confirm('Supprimer cette campagne ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Supprimer</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-6 py-4 bg-gray-50">
                    {{ $campaigns->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune campagne</h3>
                    <p class="mt-1 text-sm text-gray-500">Commence par créer ta première campagne d'envoi.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.campaigns.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nouvelle Campagne
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
