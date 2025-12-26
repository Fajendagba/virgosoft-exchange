<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('symbol', 10);
            $table->decimal('amount', 20, 8)->default(0);
            $table->decimal('locked_amount', 20, 8)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'symbol']);
            $table->index('symbol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
