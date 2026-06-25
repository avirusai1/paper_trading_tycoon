<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Computed leaderboard rankings.
 * Refreshed by a scheduled job — not real-time.
 * Composite index on (leaderboard_id, rank_position) makes pagination O(1).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leaderboard_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('rank_position')->comment('1-based rank');
            $table->unsignedBigInteger('score_value')->comment('Paise for portfolio/trade; XP for xp boards; stored as unsigned for sort');
            $table->decimal('score_display', 16, 4)->comment('Human-readable version of score_value');
            $table->string('score_label', 30)->nullable()->comment('e.g. ₹12,34,567 or +23.4%');
            $table->timestamp('computed_at')->index();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['leaderboard_id', 'user_id']);
            $table->index(['leaderboard_id', 'rank_position']);
            $table->index(['leaderboard_id', 'score_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
    }
};
