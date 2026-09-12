<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in - Ordora</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen flex flex-col lg:flex-row bg-background">
    <div class="hidden lg:flex lg:w-1/2 relative flex-col justify-between p-8 lg:p-16 text-white shrink-0"
        style="background-color: #2c1810;">
        <div class="relative z-10">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-10 w-10 rounded-lg object-contain">
                <span class="font-bold text-xl tracking-tight">Ordora</span>
            </div>
        </div>

        <div class="relative z-10 space-y-6 max-w-lg my-12">
            <h2 class="text-3xl lg:text-5xl font-bold leading-tight tracking-tight">
                Coffee Shop<br>Operating System
            </h2>
            <p class="text-white/70 text-base leading-relaxed">
                Manage your menu, track orders, and run your coffee shop seamlessly from one place.
            </p>
            <div class="flex gap-8 pt-4">
                <div>
                    <p class="text-2xl font-bold">100+</p>
                    <p class="text-white/50 text-sm">Menu Items</p>
                </div>
                <div>
                    <p class="text-2xl font-bold">24/7</p>
                    <p class="text-white/50 text-sm">Real-time Orders</p>
                </div>
                <div>
                    <p class="text-2xl font-bold">100%</p>
                    <p class="text-white/50 text-sm">Digital POS</p>
                </div>
            </div>
        </div>

        <div class="relative z-10">
            <p class="text-white/40 text-xs">&copy; {{ date('Y') }} Ordora. All rights reserved.</p>
        </div>
    </div>

    <div class="flex-1 flex items-center justify-center p-6 lg:p-12">
        <div class="hidden lg:block w-full max-w-md space-y-8">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-9 w-9 rounded-lg object-contain">
                <span class="font-bold text-lg tracking-tight">Ordora</span>
            </div>

            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">Welcome back</h1>
                <p class="text-sm text-muted-foreground">Enter your credentials to access your account</p>
            </div>

            @if ($errors->any())
            <div
                class="rounded-lg bg-destructive/10 border border-destructive/20 p-3.5 text-sm text-destructive space-y-1">
                @foreach ($errors->all() as $error)
                <div class="flex items-start gap-2">
                    <svg class="size-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span>{{ $error }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5" id="login-form">
                @csrf
                <div class="space-y-2">
                    <label for="email" class="text-sm font-medium">Email address</label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="name@example.com"
                            class="flex h-11 w-full rounded-lg border border-input bg-background pl-11 pr-4 text-sm shadow-xs transition-all outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 placeholder:text-muted-foreground/50">
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="password" class="text-sm font-medium">Password</label>
                        <a href="#" class="text-xs font-medium text-primary hover:underline">
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <input id="password" type="password" name="password" required placeholder="Enter your password"
                            class="flex h-11 w-full rounded-lg border border-input bg-background pl-11 pr-11 text-sm shadow-xs transition-all outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 placeholder:text-muted-foreground/50">
                        <button type="button" onclick="togglePass('password', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors cursor-pointer p-0.5"
                            tabindex="-1">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path class="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path class="eye-closed hidden" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" id="login-btn"
                    class="w-full inline-flex items-center justify-center rounded-lg bg-primary text-primary-foreground font-semibold text-sm h-11 px-4 hover:opacity-90 transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                    <span id="login-text">Sign in</span>
                    <span id="login-loading" class="hidden items-center gap-2">
                        <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Signing in...
                    </span>
                </button>
            </form>

            <p class="text-xs text-muted-foreground text-center pt-2">
                Demo credentials: <code class="bg-accent px-1.5 py-0.5 rounded font-medium">password</code>
            </p>
        </div>

        <div class="lg:hidden w-full max-w-sm bg-card border rounded-2xl shadow-lg p-6 space-y-6">
            <div class="flex items-center gap-3 justify-center mb-2">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-9 w-9 rounded-lg object-contain">
                <span class="font-bold text-lg tracking-tight">Ordora</span>
            </div>

            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">Welcome back</h1>
                <p class="text-sm text-muted-foreground">Enter your credentials to access your account</p>
            </div>

            @if ($errors->any())
            <div
                class="rounded-lg bg-destructive/10 border border-destructive/20 p-3.5 text-sm text-destructive space-y-1">
                @foreach ($errors->all() as $error)
                <div class="flex items-start gap-2">
                    <svg class="size-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span>{{ $error }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5" id="login-form-mobile">
                @csrf
                <div class="space-y-2">
                    <label for="email-mobile" class="text-sm font-medium">Email address</label>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                        </svg>
                        <input id="email-mobile" type="email" name="email" value="{{ old('email') }}" required autofocus
                            placeholder="name@example.com"
                            class="flex h-11 w-full rounded-lg border border-input bg-background pl-11 pr-4 text-sm shadow-xs transition-all outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 placeholder:text-muted-foreground/50">
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label for="password-mobile" class="text-sm font-medium">Password</label>
                        <a href="#" class="text-xs font-medium text-primary hover:underline">
                            Forgot password?
                        </a>
                    </div>
                    <div class="relative">
                        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 size-4 text-muted-foreground" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                        </svg>
                        <input id="password-mobile" type="password" name="password" required
                            placeholder="Enter your password"
                            class="flex h-11 w-full rounded-lg border border-input bg-background pl-11 pr-11 text-sm shadow-xs transition-all outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 placeholder:text-muted-foreground/50">
                        <button type="button" onclick="togglePass('password-mobile', this)"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground transition-colors cursor-pointer p-0.5"
                            tabindex="-1">
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path class="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178zM15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path class="eye-closed hidden" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" id="login-btn-mobile"
                    class="w-full inline-flex items-center justify-center rounded-lg bg-primary text-primary-foreground font-semibold text-sm h-11 px-4 hover:opacity-90 transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                    <span id="login-text-mobile">Sign in</span>
                    <span id="login-loading-mobile" class="hidden items-center gap-2">
                        <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Signing in...
                    </span>
                </button>
            </form>

            <p class="text-xs text-muted-foreground text-center pt-2">
                Demo credentials: <code class="bg-accent px-1.5 py-0.5 rounded font-medium">password</code>
            </p>
        </div>
    </div>

    <script>
        function togglePass(inputId, btn) {
            const input = document.getElementById(inputId);
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeClosed = btn.querySelector('.eye-closed');
            if (input.type === 'password') {
                input.type = 'text';
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
        function setupForm(formId) {
            var form = document.getElementById(formId);
            if (!form) return;
            form.addEventListener('submit', function() {
                var btn = form.querySelector('button[type="submit"]');
                if (btn) btn.disabled = true;
                var textEl = form.querySelector('[id$="-text"]');
                var loadingEl = form.querySelector('[id$="-loading"]');
                if (textEl) textEl.classList.add('hidden');
                if (loadingEl) {
                    loadingEl.classList.remove('hidden');
                    loadingEl.classList.add('flex');
                }
            });
        }
        setupForm('login-form');
        setupForm('login-form-mobile');
    </script>
</body>

</html>