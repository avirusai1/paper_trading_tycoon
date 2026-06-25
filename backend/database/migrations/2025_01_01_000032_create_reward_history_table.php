<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Unified reward audit log — tracks all rewards (XP + coins) granted to a user from any source.
 * Separate from xp_logs and coin_transactions for aggregated reward history queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reward_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->string('source_type', 50)->index()->comment('achievement, mission, level_up, season_reward, etc.');
            $table->string('source_id', 100)->nullable()->comment('ID of the source entity');
            $table->unsignedInteger('xp_amount')->default(0);
            $table->integer('coin_amount')->default(0)->comment('Can be negative for refunds');
            $table->string('description', 300)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['user_id', 'source_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_history');
    }
};
