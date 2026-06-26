<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database-driven game balance configuration.
 * All XP values, coin amounts, league thresholds, mission rewards etc.
 * live here — ZERO hardcoded constants in services.
 * The RulesEngine service reads from this table with cache-aside.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_rules', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique()->comment('Dot-notation key e.g. xp.trade_buy, coins.level_up');
            $table->string('group', 50)->index()->comment('Logical group: xp, coins, leagues, missions, seasons, market');
            $table->string('value', 500)->comment('Stored as string; cast by RulesEngine based on value_type');
            $table->enum('value_type', ['integer', 'float', 'string', 'boolean', 'json'])->default('integer');
            $table->string('description', 300)->nullable();
            $table->boolean('is_overridable')->default(true)->comment('False = system constant, cannot be edited via admin');
            $table->timestamps();

            $table->index(['group', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_rules');
    }
};
