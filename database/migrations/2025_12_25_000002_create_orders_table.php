<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('symbol', 10);
            $table->enum('side', ['buy', 'sell']);
            $table->decimal('price', 20, 2);
            $table->decimal('amount', 20, 8);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['symbol', 'side', 'status', 'price']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
