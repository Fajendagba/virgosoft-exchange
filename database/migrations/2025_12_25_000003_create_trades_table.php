<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('buy_order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUlid('sell_order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignUlid('buyer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('symbol', 10);
            $table->decimal('price', 20, 2);
            $table->decimal('amount', 20, 8);
            $table->decimal('total', 20, 2);
            $table->decimal('commission', 20, 2);
            $table->timestamps();

            $table->index('symbol');
            $table->index(['buyer_id', 'created_at']);
            $table->index(['seller_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
