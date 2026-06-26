<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Current (latest) quote per stock.
 * One row per stock — updated on every market data refresh.
 * Historical data lives in stock_daily_history.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('symbol', 20)->unique()->comment('Denormalized for fast lookups without join');
            $table->unsignedBigInteger('ltp_paise')->comment('Last traded price');
            $table->unsignedBigInteger('open_paise');
            $table->unsignedBigInteger('high_paise');
            $table->unsignedBigInteger('low_paise');
            $table->unsignedBigInteger('close_paise')->comment('Previous day close');
            $table->bigInteger('change_paise')->comment('ltp - close; can be negative');
            $table->decimal('change_percent', 8, 4)->comment('Percentage change from previous close');
            $table->unsignedBigInteger('volume')->default(0);
            $table->enum('market_status', ['open', 'closed', 'pre_market', 'post_market', 'holiday'])->default('closed');
            $table->timestamp('quoted_at')->index()->comment('Timestamp of the quote from provider');
            $table->timestamps();

            $table->index(['symbol', 'quoted_at']);
            $table->index(['market_status', 'quoted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_prices');
    }
};
