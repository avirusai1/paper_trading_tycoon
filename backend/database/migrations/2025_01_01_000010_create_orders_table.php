<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * All trade orders — market, limit, stop, bracket.
 * Designed to support partial fills and cancellations without schema changes.
 * Status transitions tracked in order_events (append-only audit trail).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->foreignId('stock_id')->constrained()->restrictOnDelete();
            $table->string('symbol', 20)->index()->comment('Denormalized for fast queries');
            $table->string('idempotency_key', 64)->unique()->comment('Client-provided UUID prevents duplicate orders');
            $table->enum('side', ['buy', 'sell']);
            $table->enum('order_type', ['market', 'limit', 'stop', 'stop_limit', 'bracket'])->default('market');
            $table->enum('status', [
                'pending', 'open', 'partially_filled', 'filled', 'cancelled', 'rejected', 'expired',
            ])->default('pending')->index();
            $table->unsignedInteger('quantity')->comment('Total quantity requested');
            $table->unsignedInteger('filled_quantity')->default(0)->comment('Quantity executed so far');
            $table->unsignedBigInteger('limit_price_paise')->nullable()->comment('For limit/stop-limit orders');
            $table->unsignedBigInteger('stop_price_paise')->nullable()->comment('For stop/bracket orders');
            $table->unsignedBigInteger('average_fill_price_paise')->nullable()->comment('Weighted average fill price in paise');
            $table->string('rejection_reason', 500)->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'symbol', 'status']);
            $table->index(['stock_id', 'status', 'side']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
