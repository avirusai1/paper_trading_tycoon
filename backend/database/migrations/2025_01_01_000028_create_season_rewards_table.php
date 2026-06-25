<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reward tiers for each season per league tier.
 * The SeasonEngine reads from this table to distribute rewards.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('season_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('rank_from')->comment('Inclusive rank start for this reward tier');
            $table->unsignedSmallInteger('rank_to')->comment('Inclusive rank end for this reward tier');
            $table->unsignedInteger('coin_reward')->default(0);
            $table->unsignedInteger('xp_reward')->default(0);
            $table->string('title_reward', 100)->nullable();
            $table->json('extra_rewards')->nullable()->comment('Badges, store items etc.');
            $table->timestamps();

            $table->unique(['season_id', 'league_id', 'rank_from', 'rank_to'], 'season_rewards_range_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('season_rewards');
    }
};
