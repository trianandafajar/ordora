<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Restaurant;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $restaurantId = Restaurant::value('id');
        foreach (['Coffee', 'Non-Coffee', 'Snack'] as $name) {
            Category::updateOrCreate([
                'name' => $name,
                'restaurant_id' => $restaurantId,
            ]);
        }
    }
}
