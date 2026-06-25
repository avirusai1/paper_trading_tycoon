<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * End-of-day OHLCV data per stock.
 * Used for charts, performance calculations, and historical P&L.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_daily_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->cascadeOnDelete();
            $table->string('symbol', 20)->index();
            $table->date('trading_date')->index();
            $table->unsignedBigInteger('open_paise');
            $table->unsignedBigInteger('high_paise');
            $table->unsignedBigInteger('low_paise');
            $table->unsignedBigInteger('close_paise');
            $table->unsignedBigInteger('volume')->default(0);
            $table->timestamps();

            $table->unique(['stock_id', 'trading_date']);
            $table->index(['symbol', 'trading_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_daily_history');
    }
};
