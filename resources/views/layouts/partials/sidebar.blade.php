<div class="flex flex-col h-full w-full bg-sidebar text-sidebar-foreground">
    <!-- Header / Brand -->
    <div class="flex items-center gap-2 px-4 h-16 border-b border-sidebar-border"
        :class="(sidebarOpen || hoverOpen) ? 'px-6' : 'px-4 justify-center'">
        <div
            class="size-8 rounded-lg bg-primary text-primary-foreground flex items-center justify-center font-bold text-lg flex-shrink-0">
            O
        </div>
        <div class="flex flex-col flex-1" x-show="sidebarOpen || hoverOpen" x-cloak
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100">
            <span class="font-bold text-sm tracking-tight whitespace-nowrap">Ordora</span>
            <span class="text-xs text-muted-foreground whitespace-nowrap">Coffee Shop OS</span>
        </div>
        @if($mobile)
        <button @click="mobileOpen = false" class="p-1.5 rounded hover:bg-sidebar-accent/50 transition-colors"
            aria-label="Close sidebar">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        @endif
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-4 space-y-6 overflow-y-auto overflow-x-hidden">
        @auth
        @if(auth()->user()->role->value === 'admin')
        <!-- Admin Menu -->
        <div class="space-y-1">
            <div class="px-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 whitespace-nowrap"
                x-show="sidebarOpen || hoverOpen" x-cloak>
                General
            </div>
            <a href="{{ route('admin.dashboard') }}" @click="mobileOpen = false"
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-muted-foreground hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6" />
                </svg>
                <span x-show="sidebarOpen || hoverOpen" x-cloak class="whitespace-nowrap">Dashboard</span>
            </a>
        </div>

        <div class="space-y-1">
            <div class="px-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 whitespace-nowrap"
                x-show="sidebarOpen || hoverOpen" x-cloak>
                System Management
            </div>
            <a href="{{ route('admin.categories.index') }}" @click="mobileOpen = false"
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-muted-foreground hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span x-show="sidebarOpen || hoverOpen" x-cloak class="whitespace-nowrap">Categories</span>
            </a>
            <a href="{{ route('admin.products.index') }}" @click="mobileOpen = false"
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('admin.products.*') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-muted-foreground hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
                <span x-show="sidebarOpen || hoverOpen" x-cloak class="whitespace-nowrap">Products</span>
            </a>
            <a href="{{ route('admin.tables.index') }}" @click="mobileOpen = false"
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('admin.tables.*') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-muted-foreground hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                <span x-show="sidebarOpen || hoverOpen" x-cloak class="whitespace-nowrap">Tables QR</span>
            </a>
            <a href="{{ route('admin.kasir.index') }}" @click="mobileOpen = false"
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('admin.kasir.*') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-muted-foreground hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span x-show="sidebarOpen || hoverOpen" x-cloak class="whitespace-nowrap">Kasir Accounts</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" @click="mobileOpen = false"
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('admin.reports.*') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-muted-foreground hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <span x-show="sidebarOpen || hoverOpen" x-cloak class="whitespace-nowrap">Reports</span>
            </a>
        </div>
        @else
        <!-- Kasir Menu -->
        <div class="space-y-1">
            <div class="px-3 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 whitespace-nowrap"
                x-show="sidebarOpen || hoverOpen" x-cloak>
                Kasir
            </div>
            <a href="{{ route('kasir.dashboard') }}" @click="mobileOpen = false"
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium whitespace-nowrap transition-colors {{ request()->routeIs('kasir.dashboard') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-muted-foreground hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg class="size-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <span x-show="sidebarOpen || hoverOpen" x-cloak class="whitespace-nowrap">Incoming Orders</span>
            </a>
        </div>
        @endif
        @endauth
    </nav>

    <!-- User Profile / Footer -->
    <div class="p-4 border-t border-sidebar-border flex items-center"
        :class="(sidebarOpen || hoverOpen) ? 'justify-between' : 'justify-center'">
        @auth
        <div class="flex items-center gap-3 overflow-hidden">
            <div
                class="size-8 rounded-full bg-accent flex items-center justify-center font-bold text-xs uppercase flex-shrink-0">
                {{ substr(auth()->user()->name, 0, 2) }}
            </div>
            <div class="flex flex-col truncate" x-show="sidebarOpen || hoverOpen" x-cloak>
                <span class="text-sm font-medium truncate">{{ auth()->user()->name }}</span>
                <span class="text-xs text-muted-foreground capitalize">{{ auth()->user()->role->value }}</span>
            </div>
        </div>
        @endauth
    </div>
</div>