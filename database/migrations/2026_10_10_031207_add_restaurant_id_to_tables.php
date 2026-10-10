<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->nullable()->constrained()->after('id');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->constrained()->after('id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->constrained()->after('id');
        });
        Schema::table('tables', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->constrained()->after('id');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('restaurant_id')->constrained()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('orders_restaurant_id_foreign');
            $table->dropColumn('restaurant_id');
        });
        Schema::table('tables', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('tables_restaurant_id_foreign');
            $table->dropColumn('restaurant_id');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('products_restaurant_id_foreign');
            $table->dropColumn('restaurant_id');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('categories_restaurant_id_foreign');
            $table->dropColumn('restaurant_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('users_restaurant_id_foreign');
            $table->dropColumn('restaurant_id');
        });
    }
};
