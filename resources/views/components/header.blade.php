@props(['title' => null, 'backUrl' => null, 'showLogo' => true, 'menuUrl' => null])

<header class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur px-4 h-14 flex items-center justify-between">
    <div class="flex items-center gap-2">
        @if($backUrl)
        <a href="{{ $backUrl }}" class="text-sm text-primary font-medium hover:underline flex items-center gap-1">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back
        </a>
        @elseif($showLogo)
        <a href="{{ $menuUrl ?? request()->url() }}" class="group flex items-center gap-2 rounded-lg p-1">
            <div class="rounded-lg flex items-center justify-center shrink-0">
                <img src="{{ asset('images/logo.png') }}" alt="Logo Ordora" class="h-10 w-10 object-cover">
            </div>
            <div class="flex min-w-0 flex-col">
                <span class="font-bold text-lg tracking-tight whitespace-nowrap">Ordora</span>
            </div>
        </a>
        @endif
    </div>

    <div class="flex items-center gap-4">
        @if($title)
        <p class="text-xs text-muted-foreground">{{ $title }}</p>
        @endif
        @isset($right)
        {{ $right }}
        @endisset
    </div>
</header>