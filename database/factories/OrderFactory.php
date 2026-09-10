<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'table_id' => Table::factory(),
            'user_id' => null,
            'order_token' => Str::random(32),
            'customer_name' => fake()->name(),
            'total_price' => 0,
            'status' => OrderStatus::Pending,
            'payment_method' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Order $order) {
            $contents = Product::query()
                ->inRandomOrder()
                ->limit(rand(1, 4))
                ->get()
                ->map(function ($product) {
                    $quantity = rand(1, 3);

                    return [
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'subtotal' => $product->price * $quantity,
                    ];
                });

            $items = $order->orderItems()->createMany($contents);

            $order->update(['total_price' => $items->sum('subtotal')]);
        });
    }
}
