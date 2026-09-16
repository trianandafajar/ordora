<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    private const IMAGE_SOURCE_DIR = 'public/images/menu';

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Product::query()->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $categories = Category::pluck('id', 'name');

        $products = [
            ['name' => 'Espresso', 'description' => 'Strong espresso shot', 'price' => 2.50, 'category' => 'Coffee'],
            ['name' => 'Americano', 'description' => 'Espresso with hot water', 'price' => 3.00, 'category' => 'Coffee'],
            ['name' => 'Cappuccino', 'description' => 'Espresso, milk, foam', 'price' => 3.50, 'category' => 'Coffee'],
            ['name' => 'Latte', 'description' => 'Espresso with milk', 'price' => 3.50, 'category' => 'Coffee'],
            ['name' => 'Caramel Macchiato', 'description' => 'Latte with caramel sauce', 'price' => 4.00, 'category' => 'Coffee'],
            ['name' => 'Matcha Latte', 'description' => 'Matcha green tea with milk', 'price' => 3.80, 'category' => 'Non-Coffee'],
            ['name' => 'Chocolate', 'description' => 'Hot chocolate drink', 'price' => 3.50, 'category' => 'Non-Coffee'],
            ['name' => 'Lemon Tea', 'description' => 'Fresh lemon tea', 'price' => 3.00, 'category' => 'Non-Coffee'],
            ['name' => 'Croissant', 'description' => 'Buttered croissant', 'price' => 3.20, 'category' => 'Snack'],
            ['name' => 'French Fries', 'description' => 'Fried potato chips', 'price' => 3.00, 'category' => 'Snack'],
            ['name' => 'Cheese Cake', 'description' => 'Soft cheese cake', 'price' => 4.20, 'category' => 'Snack'],
        ];

        foreach ($products as $index => $product) {
            Product::create([
                'category_id' => $categories[$product['category']],
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $product['price'],
                'image' => $this->importImage($index + 1),
                'is_available' => true,
            ]);
        }
    }

    private function importImage(int $number): ?string
    {
        $fileName = "{$number}.png";
        $sourcePath = base_path(self::IMAGE_SOURCE_DIR.'/'.$fileName);

        if (! is_file($sourcePath)) {
            return null;
        }

        Storage::disk('public')->putFileAs('products', new File($sourcePath), $fileName);

        return 'products/'.$fileName;
    }
}
