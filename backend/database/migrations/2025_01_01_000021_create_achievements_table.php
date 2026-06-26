<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Achievement definitions (catalogue).
 * user_achievements tracks per-user unlock state.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('Stable identifier e.g. first_trade, millionaire');
            $table->string('name', 150);
            $table->text('description');
            $table->enum('tier', ['bronze', 'silver', 'gold', 'platinum', 'hidden'])->index();
            $table->string('icon_url', 200)->nullable();
            $table->unsignedInteger('xp_reward')->default(0);
            $table->unsignedInteger('coin_reward')->default(0);
            $table->string('category', 50)->nullable()->index()->comment('e.g. trading, social, portfolio, game');
            $table->json('criteria')->nullable()->comment('Structured unlock criteria for the AchievementEngine');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_repeatable')->default(false)->comment('Can be earned multiple times');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
