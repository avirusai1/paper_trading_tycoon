<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Point-in-time portfolio value snapshots.
 * Used for performance charts and leaderboard ranking.
 * Written by a scheduled job — not real-time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->unsignedBigInteger('virtual_cash_paise')->comment('Uninvested cash at snapshot time');
            $table->unsignedBigInteger('holdings_value_paise')->comment('Market value of all open positions');
            $table->unsignedBigInteger('total_portfolio_value_paise')->comment('cash + holdings_value');
            $table->bigInteger('total_pnl_paise')->comment('total_portfolio - starting_cash; can be negative');
            $table->decimal('total_pnl_percent', 10, 4);
            $table->unsignedSmallInteger('total_holdings_count')->default(0);
            $table->date('snapshot_date')->index();
            $table->enum('snapshot_type', ['daily', 'hourly', 'manual'])->default('daily');
            $table->timestamp('taken_at')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'snapshot_date', 'snapshot_type']);
            $table->index(['user_id', 'taken_at']);
            $table->index(['snapshot_date', 'total_portfolio_value_paise']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_snapshots');
    }
};
