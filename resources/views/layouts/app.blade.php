<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Ordora') }}</title>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-background text-foreground"
    x-data="{ sidebarOpen: true, hoverOpen: false, mobileOpen: false }">
    <div class="min-h-screen">

        <aside @mouseenter="hoverOpen = true" @mouseleave="hoverOpen = false"
            class="hidden md:flex fixed inset-y-0 left-0 z-40 border-r bg-sidebar transition-all duration-200 overflow-hidden"
            :class="(sidebarOpen || hoverOpen) ? 'w-64 shadow-xl' : 'w-16'">
            @include('layouts.partials.sidebar', ['mobile' => false])
        </aside>

        <div class="md:hidden fixed inset-0 z-50" x-show="mobileOpen" x-cloak x-transition:opacity>
            <div class="absolute inset-0 bg-black/50" @click="mobileOpen = false"></div>
            <div class="absolute inset-y-0 left-0 w-64 h-full bg-sidebar shadow-xl transition-transform duration-200"
                :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'">
                @include('layouts.partials.sidebar', ['mobile' => true])
            </div>
        </div>

        <div class="flex flex-col min-h-screen transition-all duration-200"
            :class="sidebarOpen ? 'md:pl-64' : 'md:pl-16'">
            <header
                class="sticky top-0 z-20 h-16 border-b bg-background/95 backdrop-blur flex items-center px-4 w-full">
                @include('layouts.partials.header')
            </header>

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>

        {{-- global toast --}}
        <div x-data="{ 
                show: false, 
                message: '', 
                type: 'success',
                init() {
                    @if(session('success'))
                        this.showToast('{{ session('success') }}', 'success');
                    @elseif(session('error'))
                        this.showToast('{{ session('error') }}', 'error');
                    @endif
                },
                showToast(msg, type) {
                    this.message = msg;
                    this.type = type;
                    this.show = true;
                    setTimeout(() => this.show = false, 4000);
                }
            }" x-show="show" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 translate-y-2"
            class="fixed bottom-4 right-4 z-[60] min-w-[300px] max-w-md rounded-lg border p-4 shadow-lg flex items-center justify-between"
            :class="{
                'bg-white border-green-200 text-green-800': type === 'success',
                'bg-white border-red-200 text-red-800': type === 'error'
            }">
            <div class="flex items-center gap-3">
                <template x-if="type === 'success'">
                    <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </template>
                <template x-if="type === 'error'">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </template>
                <span class="text-sm font-medium" x-text="message"></span>
            </div>
            <button @click="show = false" class="ml-4 text-muted-foreground hover:text-foreground">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
    @livewireScripts
</body>

</html>
