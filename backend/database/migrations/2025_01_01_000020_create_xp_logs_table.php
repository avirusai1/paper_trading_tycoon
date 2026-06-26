<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Append-only XP transaction log.
 * Every XP grant is recorded here for auditability.
 * Balance is maintained in user_levels.current_xp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->unsignedInteger('amount')->comment('XP granted — always positive');
            $table->string('source', 50)->index()->comment('e.g. trade_buy, daily_login, challenge_completed');
            $table->string('source_id', 100)->nullable()->comment('ID of the triggering entity (trade_id, challenge_id etc.)');
            $table->unsignedBigInteger('xp_before')->comment('User XP before this grant');
            $table->unsignedBigInteger('xp_after')->comment('User XP after this grant');
            $table->unsignedSmallInteger('level_before')->comment('Level before this grant');
            $table->unsignedSmallInteger('level_after')->comment('Level after this grant — equals level_before unless a level-up occurred');
            $table->timestamp('created_at')->useCurrent()->index();
            // Append-only: no updated_at

            $table->unique(['user_id', 'source', 'source_id'], 'xp_logs_idempotency');
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('xp_logs');
    }
};
