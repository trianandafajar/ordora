<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@ordora.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir1@ordora.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Kasir,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Kasir 2',
            'email' => 'kasir2@ordora.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Kasir,
            'is_active' => true,
        ]);
    }
}
