<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::pluck('id', 'name');

        $products = [
            ['name' => 'Espresso', 'description' => 'Shot espresso pekat', 'price' => 18000, 'category' => 'Coffee'],
            ['name' => 'Americano', 'description' => 'Espresso dengan air panas', 'price' => 20000, 'category' => 'Coffee'],
            ['name' => 'Cappuccino', 'description' => 'Espresso, susu, foam', 'price' => 25000, 'category' => 'Coffee'],
            ['name' => 'Latte', 'description' => 'Espresso dengan susu', 'price' => 25000, 'category' => 'Coffee'],
            ['name' => 'Caramel Macchiato', 'description' => 'Latte dengan saus karamel', 'price' => 30000, 'category' => 'Coffee'],
            ['name' => 'Matcha Latte', 'description' => 'Teh matcha dengan susu', 'price' => 28000, 'category' => 'Non-Coffee'],
            ['name' => 'Chocolate', 'description' => 'Minuman coklat panas', 'price' => 25000, 'category' => 'Non-Coffee'],
            ['name' => 'Lemon Tea', 'description' => 'Teh lemon segar', 'price' => 20000, 'category' => 'Non-Coffee'],
            ['name' => 'Croissant', 'description' => 'Roti croissant mentega', 'price' => 22000, 'category' => 'Snack'],
            ['name' => 'French Fries', 'description' => 'Kentang goreng', 'price' => 22000, 'category' => 'Snack'],
            ['name' => 'Cheese Cake', 'description' => 'Kue keju lembut', 'price' => 32000, 'category' => 'Snack'],
        ];

        foreach ($products as $product) {
            Product::create([
                'category_id' => $categories[$product['category']],
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $product['price'],
                'image' => null,
                'is_available' => true,
            ]);
        }
    }
}
