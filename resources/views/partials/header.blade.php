<header class="bg-white border-b border-gray-200 px-4 lg:px-6 py-4">
    <div class="flex items-center justify-between">
        <!-- Hamburger menu untuk mobile -->
        <div class="flex items-center">
            <button id="mobile-menu-toggle" class="md:hidden p-2 rounded-md text-gray-600 hover:text-gray-800 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            
            <!-- Breadcrumb atau title halaman -->
            <div class="ml-4 md:ml-0">
                <h1 class="text-xl font-semibold text-gray-800">
                    @yield('title', 'Dashboard')
                </h1>
                <div class="text-sm text-gray-500">
                    @yield('breadcrumb', 'Sistem Manajemen Gudang Walet')
                </div>
            </div>
        </div>

        <!-- User menu dan notifikasi -->
        <div class="flex items-center space-x-4">
            <!-- Notifikasi -->
            <button class="p-2 text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-md transition-colors relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <!-- Notifikasi badge -->
                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
            </button>

            <!-- User profile -->
            <div class="relative">
                <button class="flex items-center space-x-3 p-2 rounded-md hover:bg-gray-100 transition-colors">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-medium text-sm">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                    <div class="hidden md:block text-left">
                        <div class="text-sm font-medium text-gray-800">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->email ?? 'user@example.com' }}</div>
                    </div>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</header>