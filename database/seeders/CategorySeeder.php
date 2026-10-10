<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $restaurantId = \App\Models\Restaurant::value('id');
        foreach (['Coffee', 'Non-Coffee', 'Snack'] as $name) {
            \App\Models\Category::updateOrCreate([
                'name' => $name,
                'restaurant_id' => $restaurantId,
            ]);
        }
    }
}
