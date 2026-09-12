<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['category_id', 'name', 'slug', 'description', 'price', 'image', 'is_available'])]
class Product extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if ($product->isDirty('name')) {
                $product->slug = static::uniqueSlug($product->name, $product->id);
            }
        });

        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = static::uniqueSlug($product->name);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_available' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    private static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 2;

        while (static::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
