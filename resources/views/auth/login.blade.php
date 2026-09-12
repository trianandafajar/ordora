<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in - Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-background flex items-center justify-center p-4" x-data="{ showPassword: false }">
    <div class="w-full max-w-md space-y-6">
        <div class="flex flex-col items-center gap-4">
            <img src="{{ asset('images/logo.png') }}" alt="Ordora" class="h-16 w-16 rounded-lg object-contain">
            <span class="font-bold text-2xl tracking-tight">Ordora</span>
        </div>

        <div class="rounded-xl border bg-card shadow-sm p-6 space-y-4 max-w-md w-full">
            <div class="space-y-2">
                <h1 class="text-lg font-semibold text-center">Welcome back</h1>
                <p class="text-sm text-muted-foreground text-center">Sign in to your account to continue</p>
            </div>

            @if ($errors->any())
            <div
                class="rounded-md bg-destructive/10 border border-destructive/20 p-3 text-sm text-destructive space-y-1">
                @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4" id="login-form">
                @csrf
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-all outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive">
                </div>
                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium">Password</label>
                    <div class="relative">
                        <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                            class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-all outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 aria-invalid:border-destructive">
                        <button type="button"
                            @click="showPassword = !showPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors cursor-pointer"
                            aria-label="Toggle password visibility">
                            <svg x-show="!showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg x-show="showPassword" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0 1 12 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="submit" id="login-btn"
                    class="w-full inline-flex items-center justify-center rounded-md bg-primary text-primary-foreground font-medium text-sm h-10 px-4 py-2 hover:opacity-90 transition-opacity cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="login-text">Sign in</span>
                    <span id="login-loading" class="hidden">Signing in...</span>
                </button>
            </form>
            <script>
                document.getElementById('login-form').addEventListener('submit', function() {
                    var btn = document.getElementById('login-btn');
                    btn.disabled = true;
                    document.getElementById('login-text').classList.add('hidden');
                    document.getElementById('login-loading').classList.remove('hidden');
                });
            </script>

            <p class="text-xs text-muted-foreground text-center">
                Demo access. Password: <code class="bg-accent px-1 rounded">password</code>
            </p>
        </div>
    </div>
</body>

</html>