<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Virtual cash wallet per user.
 * virtual_cash_paise is the source of truth for trading balance.
 * Never mutated directly — updated only via trade executions within transactions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('virtual_cash_paise')->default(100000000)->comment('Starting balance: ₹10,00,000 = 100,000,000 paise');
            $table->unsignedBigInteger('coin_balance')->default(0)->comment('Materialized cache — source of truth is coin_transactions');
            $table->unsignedBigInteger('total_deposited_paise')->default(100000000)->comment('Total virtual cash ever received');
            $table->unsignedBigInteger('total_withdrawn_paise')->default(0)->comment('Total virtual cash spent on trades');
            $table->timestamp('coin_balance_updated_at')->nullable()->comment('Tracks freshness of materialized coin balance');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
