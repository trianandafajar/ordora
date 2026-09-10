<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in — Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background flex items-center justify-center p-4">
    <div class="w-full max-w-md space-y-6">
        <!-- Logo -->
        <div class="flex items-center gap-3 justify-center">
            <div class="size-10 rounded-lg bg-primary text-primary-foreground flex items-center justify-center font-bold text-xl">
                O
            </div>
            <span class="font-bold text-xl tracking-tight">Ordora</span>
        </div>

        <!-- Card -->
        <div class="rounded-xl border bg-card shadow-sm p-6 space-y-4">
            <div class="space-y-1">
                <h1 class="text-lg font-semibold">Welcome back</h1>
                <p class="text-sm text-muted-foreground">Sign in to your account to continue</p>
            </div>

            @if ($errors->any())
                <div class="rounded-md bg-destructive/10 border border-destructive/20 p-3 text-sm text-destructive space-y-1">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-all outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive">
                </div>
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium">Password</label>
                    <input id="password" type="password" name="password" required
                           class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-all outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive">
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground font-medium text-sm h-9 px-4 py-2 hover:opacity-90 transition-opacity">
                    Sign in
                </button>
            </form>

            <p class="text-xs text-muted-foreground text-center">
                Demo access. Password: <code class="bg-accent px-1 rounded">password</code>
            </p>
        </div>
    </div>
</body>
</html>