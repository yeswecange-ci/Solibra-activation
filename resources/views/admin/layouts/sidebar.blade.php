<aside class="w-64 bg-gray-900 text-white flex-shrink-0" x-data="{ open: true }">
    <div class="p-6">
        <h1 class="text-2xl font-bold">🦁 CAN 2025</h1>
        <p class="text-gray-400 text-sm">Kinshasa</p>
    </div>

    <nav class="mt-6">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            Dashboard
        </a>

        <!-- Villages -->
        <a href="{{ route('admin.villages.index') }}"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.villages.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Villages CAN
        </a>

        <!-- Partenaires -->
        <a href="{{ route('admin.partners.index') }}"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.partners.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                </path>
            </svg>
            Partenaires
        </a>

        <!-- Matchs -->
        <a href="{{ route('admin.matches.index') }}"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.matches.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Matchs
        </a>

        <!-- Joueurs -->
        <a href="{{ route('admin.users.index') }}"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.users.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                </path>
            </svg>
            Joueurs
        </a>

        <!-- Lots -->
        <a href="{{ route('admin.prizes.index') }}"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.prizes.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7">
                </path>
            </svg>
            Lots
        </a>

        <!-- Pronostics -->
        <a href="{{ route('admin.pronostics.index') }}"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.pronostics.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                </path>
            </svg>
            Pronostics
        </a>

        <!-- Classement -->
        <a href="{{ route('admin.leaderboard') }}"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.leaderboard*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                </path>
            </svg>
            Classement
        </a>

        <!-- QR Codes -->
        <a href="{{ route('admin.qrcodes.index') }}"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.qrcodes.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z">
                </path>
            </svg>
            QR Codes
        </a>

        <!-- Campaigns (Push) -->
        <a href="{{ route('admin.campaigns.index') }}"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.campaigns.*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                </path>
            </svg>
            Campagnes
        </a>

        <!-- Analytics -->
        <a href="{{ route('admin.analytics') }}"
            class="flex items-center px-6 py-3 text-gray-300 hover:bg-gray-800 hover:text-white transition {{ request()->routeIs('admin.analytics*') ? 'bg-gray-800 text-white border-l-4 border-blue-500' : '' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                </path>
            </svg>
            Analytics
        </a>
    </nav>
</aside>
