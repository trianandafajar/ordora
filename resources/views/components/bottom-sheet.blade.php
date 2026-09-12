@props(['id', 'title'])

<div x-data="{ open: false }" id="{{ $id ?? '' }}"
    x-on:open-modal.window="$event.detail.id === '{{ $id }}' ? open = true : null"
    x-on:close-modal.window="$event.detail.id === '{{ $id }}' ? open = false : null"
    x-on:keydown.escape.window="open = false" x-show="open" x-cloak
    class="fixed inset-0 z-50 flex items-end bg-black/50"
    x-transition:enter="transition-opacity duration-200 ease-out"
    x-transition:leave="transition-opacity duration-200 ease-in"
    @click.outside="open = false">

    <div class="relative w-full max-w-md mx-auto rounded-t-2xl bg-card p-5 pb-8 shadow-[0_-4px_24px_rgba(0,0,0,0.15)] max-h-[85vh] overflow-y-auto"
        x-show="open"
        x-transition:enter="transition-transform duration-300 ease-out"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition-transform duration-200 ease-in"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        @click.stop>

        <div class="flex justify-center mb-3">
            <div class="w-10 h-1 rounded-full bg-muted-foreground/30"></div>
        </div>

        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">{{ $title }}</h3>
            <button @click="open = false"
                class="size-8 flex items-center justify-center rounded-full hover:bg-muted transition-colors cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{ $slot }}
    </div>
</div>