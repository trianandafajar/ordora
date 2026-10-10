<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        User::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('password'),
            'role' => UserRole::SuperAdmin,
            'is_active' => true,
        ]);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        $restaurant = \App\Models\Restaurant::create([
            'name' => 'Restoran Utama',
            'owner_id' => $user->id,
            'is_active' => true,
        ]);

        $user->update(['restaurant_id' => $restaurant->id]);

        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir1@gmail.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Kasir,
            'is_active' => true,
            'restaurant_id' => $restaurant->id,
        ]);

        User::create([
            'name' => 'Kasir 2',
            'email' => 'kasir2@gmail.com',
            'password' => Hash::make('password'),
            'role' => UserRole::Kasir,
            'is_active' => true,
            'restaurant_id' => $restaurant->id,
        ]);
    }
}
