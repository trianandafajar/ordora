<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('kasir-orders', function (User $user): bool {
    return in_array($user->role, [UserRole::Admin, UserRole::Kasir], true);
});
