<div class="flex flex-col h-full w-full bg-sidebar text-sidebar-foreground">
    <div class="flex items-center gap-2 h-16 border-b border-sidebar-border {{ $mobile ? 'px-6' : 'px-4' }}"
        @unless($mobile) :class="(sidebarOpen || hoverOpen) ? 'px-6' : 'px-4 justify-center'" @endunless>
        <a href="{{ auth()->user()->role->value === 'admin' ? route('admin.dashboard') : route('cashier.dashboard') }}"
            @if($mobile) @click="mobileOpen = false" @endif
            class="group flex min-w-0 flex-1 items-center gap-2 rounded-lg p-1 -ml-1 transition-colors hover:bg-sidebar-accent/50 focus:outline-none focus:ring-2 focus:ring-sidebar-ring"
            aria-label="Kembali ke dashboard">
            <div class="rounded-lg flex items-center justify-center shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Ordora" class="h-10 w-10 object-cover">
            </div>
            <div class="flex min-w-0 flex-col" @unless($mobile) x-show="sidebarOpen || hoverOpen" x-cloak
                x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" @endunless>
                <span class="font-bold text-lg tracking-tight whitespace-nowrap">Ordora</span>
            </div>
        </a>
        @if($mobile)
        <button @click="mobileOpen = false"
            class="p-1.5 rounded hover:bg-sidebar-accent/50 transition-colors cursor-pointer"
            aria-label="Close sidebar">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
        @endif
    </div>

    <nav class="flex-1 px-4 py-4 space-y-6 overflow-y-auto overflow-x-hidden">
        @auth
        @if(auth()->user()->role->value === 'admin')
        <div class="space-y-1">
            @if($mobile)
            <div
                class="px-3 text-xs font-bold text-sidebar-foreground/50 uppercase tracking-wider mb-2 whitespace-nowrap">
                General
            </div>
            <a href="{{ route('admin.dashboard') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span class="whitespace-nowrap">Dashboard</span>
            </a>
            @else
            <div class="px-3 text-xs font-bold text-sidebar-foreground/50 uppercase tracking-wider mb-2 whitespace-nowrap"
                x-show="sidebarOpen || hoverOpen" x-cloak>
                General
            </div>
            <a href="{{ route('admin.dashboard') }}" @click="mobileOpen = false"
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                <span x-show="sidebarOpen || hoverOpen" x-cloak class="whitespace-nowrap">Dashboard</span>
            </a>
            @endif
        </div>

        <div class="space-y-1">
            @if($mobile)
            <div
                class="px-3 text-xs font-bold text-sidebar-foreground/50 uppercase tracking-wider mb-2 whitespace-nowrap">
                System Management
            </div>
            @else
            <div class="px-3 text-xs font-bold text-sidebar-foreground/50 uppercase tracking-wider mb-2 whitespace-nowrap"
                x-show="sidebarOpen || hoverOpen" x-cloak>
                System Management
            </div>
            @endif
            <a href="{{ route('admin.categories.index') }}" @click="mobileOpen = false" @unless($mobile)
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'" @endunless
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('admin.categories.*') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
                <span @unless($mobile) x-show="sidebarOpen || hoverOpen" x-cloak @endunless
                    class="whitespace-nowrap">Categories</span>
            </a>
            <a href="{{ route('admin.products.index') }}" @click="mobileOpen = false" @unless($mobile)
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'" @endunless
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('admin.products.*') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                </svg>
                <span @unless($mobile) x-show="sidebarOpen || hoverOpen" x-cloak @endunless
                    class="whitespace-nowrap">Products</span>
            </a>
            <a href="{{ route('admin.tables.index') }}" @click="mobileOpen = false" @unless($mobile)
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'" @endunless
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('admin.tables.*') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                </svg>
                <span @unless($mobile) x-show="sidebarOpen || hoverOpen" x-cloak @endunless
                    class="whitespace-nowrap">Tables QR</span>
            </a>
            <a href="{{ route('admin.cashiers.index') }}" @click="mobileOpen = false" @unless($mobile)
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'" @endunless
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('admin.cashiers.*') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span @unless($mobile) x-show="sidebarOpen || hoverOpen" x-cloak @endunless
                    class="whitespace-nowrap">Cashier Accounts</span>
            </a>
            <a href="{{ route('admin.reports') }}" @click="mobileOpen = false" @unless($mobile)
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'" @endunless
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('admin.reports*') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                </svg>
                <span @unless($mobile) x-show="sidebarOpen || hoverOpen" x-cloak @endunless
                    class="whitespace-nowrap">Reports</span>
            </a>
        </div>
        @else
        <div class="space-y-1">
            @if($mobile)
            <div
                class="px-3 text-xs font-bold text-sidebar-foreground/50 uppercase tracking-wider mb-2 whitespace-nowrap">
                Cashier
            </div>
            <a href="{{ route('cashier.dashboard') }}" @click="mobileOpen = false"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('cashier.dashboard') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <span class="whitespace-nowrap">Incoming Orders</span>
            </a>
            @else
            <div class="px-3 text-xs font-bold text-sidebar-foreground/50 uppercase tracking-wider mb-2 whitespace-nowrap"
                x-show="sidebarOpen || hoverOpen" x-cloak>
                Cashier
            </div>
            <a href="{{ route('cashier.dashboard') }}" @click="mobileOpen = false"
                :class="!(sidebarOpen || hoverOpen) && 'justify-center'"
                class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-semibold whitespace-nowrap transition-colors {{ request()->routeIs('cashier.dashboard') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'text-sidebar-foreground/70 hover:bg-sidebar-accent/50 hover:text-sidebar-accent-foreground' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                <span x-show="sidebarOpen || hoverOpen" x-cloak class="whitespace-nowrap">Incoming Orders</span>
            </a>
            @endif
        </div>
        @endif
        @endauth
    </nav>

    <div class="p-4 border-t border-sidebar-border flex items-center {{ $mobile ? 'justify-between' : '' }}"
        @unless($mobile) :class="(sidebarOpen || hoverOpen) ? 'justify-between' : 'justify-center'" @endunless>
        @auth
        <div class="flex items-center gap-3 overflow-hidden">
            <div
                class="size-8 rounded-full bg-black flex items-center justify-center font-bold text-xs uppercase shrink-0">
                {{ substr(auth()->user()->name, 0, 2) }}
            </div>
            <div class="flex flex-col truncate" @unless($mobile) x-show="sidebarOpen || hoverOpen" x-cloak @endunless>
                <span class="text-sm font-medium truncate">{{ auth()->user()->name }}</span>
                <span class="text-xs text-sidebar-foreground/70 capitalize">{{ auth()->user()->role->value }}</span>
            </div>
        </div>
        @endauth
    </div>
</div>