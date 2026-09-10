<?php

namespace Database\Factories;

use App\Enums\TableStatus;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Table>
 */
class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition(): array
    {
        return [
            'number' => fake()->unique()->numberBetween(1, 100),
            'qr_token' => Str::random(32),
            'status' => TableStatus::Available,
            'capacity' => fake()->numberBetween(2, 8),
        ];
    }
}
