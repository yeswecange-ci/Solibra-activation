<aside
    class="flex-shrink-0 bg-white border-r border-gray-200 transition-all duration-300 ease-in-out h-screen flex flex-col shadow-sm"
    x-data="{ expanded: false }"
    @mouseenter="expanded = true"
    @mouseleave="expanded = false"
    :class="expanded ? 'w-64' : 'w-20'"
>
    <!-- Logo/Brand Section -->
    <div class="flex-shrink-0 border-b border-gray-200 transition-all duration-300"
         :class="expanded ? 'p-6' : 'p-4'">
        <div class="flex items-center" :class="expanded ? 'space-x-3' : 'justify-center'">
            <div class="text-3xl">🦁</div>
            <div x-show="expanded" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 class="overflow-hidden">
                <h1 class="text-lg font-bold text-navy-800">FOOT 2025</h1>
                <p class="text-gray-500 text-xs">Solibra</p>
            </div>
        </div>
    </div>

    <!-- Navigation Items -->
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-all rounded-md group {{ request()->routeIs('admin.dashboard') ? 'bg-primary text-white' : '' }}"
           :class="expanded ? 'px-3' : 'justify-center px-0'">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-gray-500 group-hover:text-primary' }}"
                 :class="expanded ? 'mr-3' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                </path>
            </svg>
            <span x-show="expanded" x-transition class="font-medium text-sm whitespace-nowrap">Dashboard</span>
        </a>

        <!-- Partenaires -->
        <a href="{{ route('admin.partners.index') }}"
           class="flex items-center py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-all rounded-md group {{ request()->routeIs('admin.partners.*') ? 'bg-primary text-white' : '' }}"
           :class="expanded ? 'px-3' : 'justify-center px-0'">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.partners.*') ? 'text-white' : 'text-gray-500 group-hover:text-primary' }}"
                 :class="expanded ? 'mr-3' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                </path>
            </svg>
            <span x-show="expanded" x-transition class="font-medium text-sm whitespace-nowrap">Partenaires</span>
        </a>

        <!-- Matchs -->
        <a href="{{ route('admin.matches.index') }}"
           class="flex items-center py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-all rounded-md group {{ request()->routeIs('admin.matches.*') ? 'bg-primary text-white' : '' }}"
           :class="expanded ? 'px-3' : 'justify-center px-0'">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.matches.*') ? 'text-white' : 'text-gray-500 group-hover:text-primary' }}"
                 :class="expanded ? 'mr-3' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <span x-show="expanded" x-transition class="font-medium text-sm whitespace-nowrap">Matchs</span>
        </a>

        <!-- Joueurs -->
        <a href="{{ route('admin.users.index') }}"
           class="flex items-center py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-all rounded-md group {{ request()->routeIs('admin.users.*') ? 'bg-primary text-white' : '' }}"
           :class="expanded ? 'px-3' : 'justify-center px-0'">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-gray-500 group-hover:text-primary' }}"
                 :class="expanded ? 'mr-3' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                </path>
            </svg>
            <span x-show="expanded" x-transition class="font-medium text-sm whitespace-nowrap">Joueurs</span>
        </a>

        <!-- Lots -->
        <a href="{{ route('admin.prizes.index') }}"
           class="flex items-center py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-all rounded-md group {{ request()->routeIs('admin.prizes.*') ? 'bg-primary text-white' : '' }}"
           :class="expanded ? 'px-3' : 'justify-center px-0'">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.prizes.*') ? 'text-white' : 'text-gray-500 group-hover:text-primary' }}"
                 :class="expanded ? 'mr-3' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7">
                </path>
            </svg>
            <span x-show="expanded" x-transition class="font-medium text-sm whitespace-nowrap">Lots</span>
        </a>

        <!-- Pronostics -->
        <a href="{{ route('admin.pronostics.index') }}"
           class="flex items-center py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-all rounded-md group {{ request()->routeIs('admin.pronostics.*') ? 'bg-primary text-white' : '' }}"
           :class="expanded ? 'px-3' : 'justify-center px-0'">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.pronostics.*') ? 'text-white' : 'text-gray-500 group-hover:text-primary' }}"
                 :class="expanded ? 'mr-3' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                </path>
            </svg>
            <span x-show="expanded" x-transition class="font-medium text-sm whitespace-nowrap">Pronostics</span>
        </a>

        <!-- Classement -->
        <a href="{{ route('admin.leaderboard') }}"
           class="flex items-center py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-all rounded-md group {{ request()->routeIs('admin.leaderboard*') ? 'bg-primary text-white' : '' }}"
           :class="expanded ? 'px-3' : 'justify-center px-0'">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.leaderboard*') ? 'text-white' : 'text-gray-500 group-hover:text-primary' }}"
                 :class="expanded ? 'mr-3' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z">
                </path>
            </svg>
            <span x-show="expanded" x-transition class="font-medium text-sm whitespace-nowrap">Classement</span>
        </a>

        <!-- Analytics -->
        <a href="{{ route('admin.analytics') }}"
           class="flex items-center py-3 text-gray-700 hover:bg-gray-100 hover:text-primary transition-all rounded-md group {{ request()->routeIs('admin.analytics*') ? 'bg-primary text-white' : '' }}"
           :class="expanded ? 'px-3' : 'justify-center px-0'">
            <svg class="w-5 h-5 {{ request()->routeIs('admin.analytics*') ? 'text-white' : 'text-gray-500 group-hover:text-primary' }}"
                 :class="expanded ? 'mr-3' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                </path>
            </svg>
            <span x-show="expanded" x-transition class="font-medium text-sm whitespace-nowrap">Analytics</span>
        </a>
    </nav>
</aside>
