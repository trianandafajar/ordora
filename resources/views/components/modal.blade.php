@props(['id', 'title', 'maxWidth' => 'max-w-md'])

<div x-data="{ open: false }" id="{{ $id ?? '' }}"
    x-on:open-modal.window="$event.detail.id === '{{ $id }}' ? open = true : null"
    x-on:close-modal.window="$event.detail.id === '{{ $id }}' ? open = false : null"
    x-on:keydown.escape.window="open = false" x-show="open" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 transition-opacity duration-200"
    x-transition:enter="duration-200 ease-out" x-transition:leave="duration-200 ease-in" @click.outside="open = false">

    <div class="relative w-full {{ $maxWidth }} rounded-xl bg-card p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">{{ $title }}</h3>
            <button @click="open = false" class="text-muted-foreground hover:text-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        {{ $slot }}
    </div>
</div>