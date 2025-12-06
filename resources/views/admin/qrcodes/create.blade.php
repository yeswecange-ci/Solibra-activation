@extends('admin.layouts.app')

@section('title', 'Générer un QR Code')
@section('page-title', 'Générer un QR Code')

@section('content')
<div class="max-w-3xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.qrcodes.store') }}" method="POST">
            @csrf

            <!-- Source/Emplacement -->
            <div class="mb-6">
                <label for="source" class="block text-sm font-medium text-gray-700 mb-2">
                    Source / Emplacement <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    name="source"
                    id="source"
                    value="{{ old('source') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('source') border-red-500 @enderror"
                    placeholder="Ex: Affiche Gombe, Flyer Bandalungwa, Station Texaco..."
                    required
                >
                @error('source')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Identifiez l'endroit où ce QR Code sera placé</p>
            </div>

            <!-- Village -->
            <div class="mb-6">
                <label for="village_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Village CAN <span class="text-gray-400">(optionnel)</span>
                </label>
                <select
                    name="village_id"
                    id="village_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('village_id') border-red-500 @enderror"
                >
                    <option value="">-- Utiliser le mapping par défaut --</option>
                    @foreach($villages as $village)
                        <option value="{{ $village->id }}" {{ old('village_id') == $village->id ? 'selected' : '' }}>
                            {{ $village->name }}
                        </option>
                    @endforeach
                </select>
                @error('village_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">
                    Si sélectionné, générera automatiquement <code class="px-1 py-0.5 bg-gray-100 rounded text-xs">START_AFF_{VILLAGE}</code>
                </p>
            </div>

            <!-- Info QR Code -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h4 class="text-sm font-semibold text-blue-900 mb-2">ℹ️ Informations</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Un code unique sera généré automatiquement</li>
                    <li>• Le QR Code redirigera vers WhatsApp pour l'inscription</li>
                    <li>• Vous pourrez télécharger l'image après la création</li>
                    <li>• Les scans seront automatiquement trackés</li>
                </ul>
            </div>

            <!-- Statut actif -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input
                        type="checkbox"
                        name="is_active"
                        class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        {{ old('is_active', true) ? 'checked' : '' }}
                    >
                    <span class="ml-2 text-sm text-gray-700">QR Code actif</span>
                </label>
                <p class="text-sm text-gray-500 mt-1">Les QR Codes inactifs ne comptabiliseront pas les scans</p>
            </div>

            <!-- Boutons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.qrcodes.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Annuler
                </a>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    Générer le QR Code
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
