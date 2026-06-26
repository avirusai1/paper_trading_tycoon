<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Leaderboard definitions.
 * E.g. "Weekly Portfolio Return", "All-Time Gains", "League Bronze Season 1".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('name', 150);
            $table->enum('type', ['portfolio_value', 'portfolio_return', 'trade_volume', 'xp', 'custom'])->index();
            $table->enum('period', ['daily', 'weekly', 'monthly', 'season', 'all_time'])->index();
            $table->foreignId('season_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('league_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->date('period_starts_at')->nullable();
            $table->date('period_ends_at')->nullable();
            $table->unsignedInteger('max_entries')->default(100);
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'period', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboards');
    }
};
