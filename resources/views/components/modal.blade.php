@props(['id', 'title', 'maxWidth' => 'max-w-md'])

<div x-data="{ open: false }"
     x-on:open-modal.window="$event.detail.id === '{{ $id }}' ? open = true : null"
     x-on:close-modal.window="$event.detail.id === '{{ $id }}' ? open = false : null"
     x-on:keydown.escape.window="open = false"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 transition-opacity duration-200"
     :class="{'flex': open, 'hidden': !open}">

    <div class="relative w-full {{ $maxWidth }} rounded-xl bg-card p-6 shadow-lg"
         x-show="open"
         x-cloak
         x-transition:enter="duration-200 ease-out"
         x-transition:leave="duration-200 ease-in"
         @click.outside="open = false">

        <h3 class="text-lg font-semibold mb-4">{{ $title }}</h3>

        {{ $slot }}
    </div>
</div>