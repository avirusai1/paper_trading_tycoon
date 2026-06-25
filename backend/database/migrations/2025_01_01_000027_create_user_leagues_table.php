<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user league membership per season.
 * One row per user per season — tracks rank, tier, and season performance.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_leagues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->foreignId('league_id')->constrained()->cascadeOnDelete();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->string('tier', 20)->index()->comment('Denormalized from league.tier');
            $table->unsignedInteger('rank_position')->nullable()->comment('Rank within their tier group this season');
            $table->unsignedBigInteger('season_portfolio_value_paise')->default(0)->comment('Portfolio value at season end');
            $table->decimal('season_return_percent', 10, 4)->default(0)->comment('Return % for the season');
            $table->enum('season_result', ['promoted', 'demoted', 'maintained', 'pending'])->default('pending');
            $table->boolean('rewards_claimed')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'season_id']);
            $table->index(['season_id', 'tier', 'season_portfolio_value_paise']);
            $table->index(['season_id', 'league_id', 'rank_position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_leagues');
    }
};
