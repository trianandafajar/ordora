<?php

namespace App\Models\Traits;

use App\Models\Restaurant;
use App\Models\Scopes\RestaurantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToRestaurant
{
    public static function bootBelongsToRestaurant(): void
    {
        static::addGlobalScope(new RestaurantScope);

        static::creating(function ($model) {
            if (empty($model->restaurant_id) && auth()->check() && auth()->user()->restaurant_id) {
                $model->restaurant_id = auth()->user()->restaurant_id;
            }
        });
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
