@extends('admin.layouts.app')

@section('title', 'Templates de Messages')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Templates de Messages WhatsApp</h1>
                <p class="text-gray-600 mt-1">Créer et gérer vos templates de messages</p>
            </div>
            <a href="{{ route('admin.templates.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium inline-flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nouveau Template
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

        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if($templates->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Template</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Catégorie</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Variables</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($templates as $template)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $template->name }}</div>
                                    <div class="text-sm text-gray-500">{{ Str::limit($template->body, 60) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($template->type === 'text')
                                        <span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-200 rounded-full">Texte</span>
                                    @elseif($template->type === 'media')
                                        <span class="px-2 py-1 text-xs font-semibold text-purple-700 bg-purple-200 rounded-full">Media</span>
                                    @elseif($template->type === 'button')
                                        <span class="px-2 py-1 text-xs font-semibold text-blue-700 bg-blue-200 rounded-full">Boutons</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full">Interactif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">{{ $template->category ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    @if($template->variables && count($template->variables) > 0)
                                        @foreach(array_slice($template->variables, 0, 3) as $var)
                                            <span class="inline-block px-2 py-1 text-xs bg-gray-100 rounded mr-1">{{"{".$var."}"}}</span>
                                        @endforeach
                                        @if(count($template->variables) > 3)
                                            <span class="text-xs text-gray-400">+{{ count($template->variables) - 3 }}</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($template->is_active)
                                        <span class="px-2 py-1 text-xs font-semibold text-green-700 bg-green-200 rounded-full">Actif</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-200 rounded-full">Inactif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right text-sm font-medium space-x-2">
                                    <x-action-button type="view" :href="route('admin.templates.show', $template)" />
                                    <x-action-button type="edit" :href="route('admin.templates.edit', $template)" />
                                    <x-action-button type="delete" :href="route('admin.templates.destroy', $template)" method="DELETE" confirm="Supprimer ce template ?" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="px-6 py-4 bg-gray-50">
                    {{ $templates->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun template</h3>
                    <p class="mt-1 text-sm text-gray-500">Commence par créer ton premier template de message.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.templates.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nouveau Template
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
