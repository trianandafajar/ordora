<div class="flex items-center justify-between h-full w-full">
    <div class="flex items-center gap-3">
        <button @click="mobileOpen = true" class="md:hidden p-1.5 rounded hover:bg-accent transition-colors"
            aria-label="Open sidebar">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM9 4v16" />
            </svg>
        </button>
        <button @click="sidebarOpen = !sidebarOpen"
            class="hidden md:flex p-1.5 rounded hover:bg-accent transition-colors" aria-label="Toggle sidebar">
            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM9 4v16" />
            </svg>
        </button>
        <div class="hidden sm:block">
            <span class="text-xs text-muted-foreground">Ordora Coffee Shop</span>
        </div>
    </div>

    <div class="flex items-center gap-2">
        @auth
        <div
            class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg hover:bg-accent/50 transition-colors cursor-pointer">
            <span class="text-sm font-medium capitalize">{{ auth()->user()->name }}</span>
            <span class="text-xs bg-accent text-accent-foreground px-2 py-0.5 rounded-full capitalize">
                {{ auth()->user()->role->value }}
            </span>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="inline">
            @csrf
            <button type="submit"
                class="text-xs text-muted-foreground hover:text-destructive transition-colors px-2 py-1 rounded hover:bg-accent/50">
                Logout
            </button>
        </form>
        @else
        <a href="{{ route('login') }}" class="text-sm font-medium text-primary hover:underline">Login</a>
        @endauth
    </div>
</div>