<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Coffee', 'Non-Coffee', 'Snack'] as $name) {
            Category::updateOrCreate(['name' => $name]);
        }
    }
}
