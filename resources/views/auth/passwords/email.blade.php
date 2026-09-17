<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot password - Ordora</title>
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
                Reset<br>Password
            </h2>
            <p class="text-white/70 text-base leading-relaxed">
                Enter your email address to receive a password reset link.
            </p>
        </div>

        <div class="relative z-10">
            <p class="text-white/40 text-xs">&copy; {{ date('Y') }} Ordora. All rights reserved.</p>
        </div>
    </div>

    <div class="flex-1 flex items-center justify-center p-6 lg:p-12">
        <div class="w-full max-w-md space-y-8">
            <div class="flex items-center gap-3 lg:hidden justify-center">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-9 w-9 rounded-lg object-contain">
                <span class="font-bold text-lg tracking-tight">Ordora</span>
            </div>

            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">Reset password</h1>
                <p class="text-sm text-muted-foreground">Enter your email address to continue</p>
            </div>

            @if (session('status'))
            <div class="rounded-lg bg-green-50 border border-green-200 p-3.5 text-sm text-green-700">
                {{ session('status') }}
            </div>
            @endif

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

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
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

                <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-lg bg-primary text-primary-foreground font-semibold text-sm h-11 px-4 hover:opacity-90 transition-all duration-200 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                    Send reset link
                </button>
            </form>

            <p class="text-sm text-center">
                <a href="{{ route('login') }}" class="text-primary font-medium hover:underline">Back to login</a>
            </p>
        </div>
    </div>
</body>

</html>