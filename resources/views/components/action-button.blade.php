@props(['type' => 'view', 'href' => '#', 'method' => null, 'confirm' => null])

@php
    $config = [
        'view' => [
            'bg' => 'bg-blue-600 hover:bg-blue-700',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />',
            'text' => 'Voir'
        ],
        'edit' => [
            'bg' => 'bg-indigo-600 hover:bg-indigo-700',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />',
            'text' => 'Modifier'
        ],
        'delete' => [
            'bg' => 'bg-red-600 hover:bg-red-700',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />',
            'text' => 'Supprimer'
        ],
        'send' => [
            'bg' => 'bg-green-600 hover:bg-green-700',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />',
            'text' => 'Envoyer'
        ],
        'add' => [
            'bg' => 'bg-blue-600 hover:bg-blue-700',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />',
            'text' => 'Ajouter'
        ],
        'download' => [
            'bg' => 'bg-gray-600 hover:bg-gray-700',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />',
            'text' => 'Télécharger'
        ],
        'stats' => [
            'bg' => 'bg-purple-600 hover:bg-purple-700',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />',
            'text' => 'Stats'
        ]
    ];

    $buttonConfig = $config[$type] ?? $config['view'];
@endphp

@if($method)
    <form action="{{ $href }}" method="POST" class="inline-block" @if($confirm) onsubmit="return confirm('{{ $confirm }}')" @endif>
        @csrf
        @method($method)
        <button type="submit" class="inline-flex items-center px-3 py-1.5 {{ $buttonConfig['bg'] }} text-white text-sm font-medium rounded-md transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $buttonConfig['icon'] !!}
            </svg>
            {{ $slot->isEmpty() ? $buttonConfig['text'] : $slot }}
        </button>
    </form>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'inline-flex items-center px-3 py-1.5 ' . $buttonConfig['bg'] . ' text-white text-sm font-medium rounded-md transition']) }}>
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $buttonConfig['icon'] !!}
        </svg>
        {{ $slot->isEmpty() ? $buttonConfig['text'] : $slot }}
    </a>
@endif
