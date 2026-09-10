<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('table_id')->constrained('tables')->onDelete('restrict');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('order_token', 64)->unique()->index();
            $table->string('customer_name', 100);
            $table->decimal('total_price', 12, 2);
            $table->enum('status', ['pending', 'preparing', 'ready', 'served', 'paid'])->default('pending')->index();
            $table->enum('payment_method', ['cash', 'qris'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
