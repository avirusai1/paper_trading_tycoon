<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Executed trade records — one row per fill event.
 * Immutable once created (no updates). Source of truth for portfolio P&L.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->foreignId('order_id')->constrained()->restrictOnDelete()->index();
            $table->foreignId('stock_id')->constrained()->restrictOnDelete();
            $table->string('symbol', 20)->index()->comment('Denormalized for query performance');
            $table->enum('side', ['buy', 'sell']);
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('price_paise')->comment('Execution price in paise');
            $table->unsignedBigInteger('total_value_paise')->comment('quantity × price_paise');
            $table->unsignedBigInteger('brokerage_paise')->default(0)->comment('Simulated brokerage (zero in V1)');
            $table->unsignedBigInteger('net_value_paise')->comment('total_value - brokerage');
            $table->timestamp('executed_at')->index();
            // Immutable — no updated_at
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'symbol']);
            $table->index(['user_id', 'side', 'executed_at']);
            $table->index(['user_id', 'executed_at']);
            $table->index(['stock_id', 'executed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
