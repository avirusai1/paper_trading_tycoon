<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Current stock positions per user.
 * Updated on every buy/sell trade. Zero-quantity rows retained for history;
 * filtered out in application layer.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('holdings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_id')->constrained()->restrictOnDelete();
            $table->string('symbol', 20)->comment('Denormalized');
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedBigInteger('average_buy_price_paise')->default(0)->comment('FIFO weighted average cost basis');
            $table->unsignedBigInteger('total_invested_paise')->default(0)->comment('quantity × average_buy_price_paise');
            $table->unsignedBigInteger('current_value_paise')->default(0)->comment('quantity × ltp; updated by portfolio snapshot job');
            $table->bigInteger('unrealised_pnl_paise')->default(0)->comment('current_value - total_invested; can be negative');
            $table->timestamps();

            $table->unique(['user_id', 'stock_id']);
            $table->index(['user_id', 'quantity']);
            $table->index(['user_id', 'symbol']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('holdings');
    }
};
