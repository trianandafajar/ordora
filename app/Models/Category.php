<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug'])]
class Category extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if ($category->isDirty('name')) {
                $category->slug = static::uniqueSlug($category->name, $category->id);
            }
        });

        static::creating(function (Category $category) {
            if (empty($category->slug)) {
                $category->slug = static::uniqueSlug($category->name);
            }
        });
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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
