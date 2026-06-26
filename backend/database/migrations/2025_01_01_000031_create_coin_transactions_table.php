<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only coin ledger (ADR-004).
 * Positive amount = credit, negative = debit.
 * Balance is derived from SUM(amount); materialized in wallets.coin_balance.
 * UNIQUE INDEX on (user_id, source_type, source_id) enforces idempotency at DB level.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->integer('amount')->comment('Positive = credit, negative = debit');
            $table->string('source_type', 50)->comment('CoinTransactionSource enum value');
            $table->string('source_id', 100)->comment('ID of the triggering entity for idempotency');
            $table->bigInteger('balance_after')->comment('Snapshot of balance after this transaction for quick reconstruction');
            $table->string('description', 300)->nullable()->comment('Human-readable reason');
            $table->foreignId('granted_by')->nullable()->constrained('users')->nullOnDelete()->comment('Admin user ID if admin_grant');
            $table->timestamp('created_at')->useCurrent()->index();
            // Append-only: no updated_at

            // ADR-004 idempotency constraint
            $table->unique(['user_id', 'source_type', 'source_id'], 'coin_transactions_idempotency');
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'source_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
    }
};
