<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Ordora') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-background text-foreground">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 border-r hidden md:block">
             @include('layouts.partials.sidebar')
        </aside>

        <!-- Main -->
        <div class="flex-1 flex flex-col">
            <header class="h-16 border-b flex items-center px-4">
                 @include('layouts.partials.header')
            </header>
            <main class="p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>