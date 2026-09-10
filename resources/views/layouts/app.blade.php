<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Ordora') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-background text-foreground" x-data="{ sidebarOpen: true, mobileOpen: false }">
    <div class="min-h-screen">

        <!-- Desktop Sidebar (fixed, collapsible) -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-16'"
            class="hidden md:flex fixed inset-y-0 left-0 z-30 border-r transition-all duration-200 overflow-hidden flex-shrink-0">
            @include('layouts.partials.sidebar', ['mobile' => false])
        </aside>

        <!-- Mobile Sidebar Drawer (slide-in from left) -->
        <div class="md:hidden fixed inset-0 z-50" x-show="mobileOpen" x-cloak x-transition:opacity>
            <!-- Overlay -->
            <div class="absolute inset-0 bg-black/50" @click="mobileOpen = false"></div>
            <!-- Panel -->
            <div class="absolute inset-y-0 left-0 w-64 h-full bg-sidebar shadow-xl transition-transform duration-200"
                :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'">
                @include('layouts.partials.sidebar', ['mobile' => true])
            </div>
        </div>

        <!-- Main -->
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
    </div>
</body>

</html>