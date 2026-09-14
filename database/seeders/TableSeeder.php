<?php

namespace Database\Seeders;

use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TableSeeder extends Seeder
{
    public function run(): void
    {
        Table::query()->truncate();

        for ($i = 1; $i <= 10; $i++) {
            Table::create([
                'number' => (string) $i,
                'qr_token' => Str::random(32),
                'status' => 'available',
                'capacity' => $i % 2 === 0 ? 4 : 2,
            ]);
        }
    }
}
