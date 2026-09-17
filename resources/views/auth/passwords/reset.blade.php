<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset password - Ordora</title>
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
                Set New<br>Password
            </h2>
        </div>
    </div>

    <div class="flex-1 flex items-center justify-center p-6 lg:p-12">
        <div class="w-full max-w-md space-y-8">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold tracking-tight">Create new password</h1>
                <p class="text-sm text-muted-foreground">Enter your new password below</p>
            </div>

            @if ($errors->any())
            <div
                class="rounded-lg bg-destructive/10 border border-destructive/20 p-3.5 text-sm text-destructive space-y-1">
                @foreach ($errors->all() as $error)
                <div class="flex items-start gap-2">
                    <span>{{ $error }}</span>
                </div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div class="space-y-2">
                    <label for="password" class="text-sm font-medium">New password</label>
                    <input id="password" type="password" name="password" required
                        class="flex h-11 w-full rounded-lg border border-input bg-background px-4 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                </div>

                <div class="space-y-2">
                    <label for="password_confirmation" class="text-sm font-medium">Confirm password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                        class="flex h-11 w-full rounded-lg border border-input bg-background px-4 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50">
                </div>

                <button type="submit"
                    class="w-full inline-flex items-center justify-center rounded-lg bg-primary text-primary-foreground font-semibold text-sm h-11 px-4 hover:opacity-90 transition-all cursor-pointer">
                    Reset password
                </button>
            </form>
        </div>
    </div>
</body>

</html>