<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug', 100)->after('name')->nullable();
        });

        // Backfill existing slugs
        $categories = Category::all();
        foreach ($categories as $category) {
            $category->slug = Str::slug($category->name);
            // Handle collision
            $i = 2;
            $original = $category->slug;
            while (Category::where('slug', $category->slug)->where('id', '!=', $category->id)->exists()) {
                $category->slug = $original.'-'.$i++;
            }
            $category->save();
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug', 100)->nullable(false)->change();
            $table->unique('slug');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
