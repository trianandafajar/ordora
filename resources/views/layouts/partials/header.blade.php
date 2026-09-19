<div class="flex items-center justify-between h-full w-full">
    <div class="flex items-center gap-3">
        <div class="rounded-lg flex items-center justify-center shrink-0 md:hidden">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-10">
        </div>
        <button @click="mobileOpen = true"
            class="md:hidden p-1.5 rounded hover:bg-accent transition-colors cursor-pointer" aria-label="Open sidebar">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM9 4v16" />
            </svg>
        </button>
        <button @click="sidebarOpen = !sidebarOpen"
            class="hidden md:flex p-1.5 rounded hover:bg-accent transition-colors cursor-pointer"
            aria-label="Toggle sidebar">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM9 4v16" />
            </svg>
        </button>
    </div>

    <div class="flex items-center gap-3">
        @auth
        <div class="relative" x-data="{ userMenuOpen: false }" @click.outside="userMenuOpen = false">
            <button @click="userMenuOpen = !userMenuOpen"
                class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-accent/70 transition-colors cursor-pointer"
                aria-label="User menu">
                <div
                    class="size-8 rounded-full bg-primary text-primary-foreground flex items-center justify-center font-bold text-xs uppercase shrink-0">
                    {{ substr(auth()->user()->name, 0, 2) }}
                </div>
                <div class="hidden sm:block text-left">
                    <span class="block text-sm font-medium leading-tight">{{ auth()->user()->name }}</span>
                </div>
                <svg class="size-4 text-muted-foreground transition-transform duration-200"
                    :class="userMenuOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="userMenuOpen" x-cloak x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full mt-2 w-56 rounded-lg border bg-popover text-popover-foreground shadow-lg z-30 py-1">
                <div class="px-3 py-2 border-b border-border">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-muted-foreground capitalize">{{ auth()->user()->role->value }}</p>
                </div>
<button x-data @click="$dispatch('open-modal', {id: 'logout-modal'})" type="button"
    class="w-full flex items-center gap-2 px-3 py-2 text-sm text-destructive hover:bg-destructive/10 transition-colors cursor-pointer text-left">
    <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
    </svg>
    Sign Out
</button>
            </div>
        </div>
        @else
        <a href="{{ route('login') }}" class="text-sm font-medium text-primary hover:underline">Login</a>
        @endauth
    </div>
</div>