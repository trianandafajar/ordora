<?php

use App\Models\Product;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('slug', 255)->after('name')->nullable();
        });

        foreach (Product::all() as $product) {
            $product->slug = Str::slug($product->name);
            $i = 2;
            $original = $product->slug;
            while (Product::where('slug', $product->slug)->where('id', '!=', $product->id)->exists()) {
                $product->slug = $original.'-'.$i++;
            }
            $product->save();
        }

        Schema::table('products', function (Blueprint $table) {
            $table->string('slug', 255)->nullable(false)->change();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
