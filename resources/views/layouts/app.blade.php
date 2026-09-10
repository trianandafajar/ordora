<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Ordora') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-background text-foreground" x-data="{ sidebarOpen: true }">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-16'"
            class="border-r transition-all duration-200 overflow-hidden flex-shrink-0">
            @include('layouts.partials.sidebar')
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0">
            <header class="h-16 border-b flex items-center px-4">
                @include('layouts.partials.header')
            </header>
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>