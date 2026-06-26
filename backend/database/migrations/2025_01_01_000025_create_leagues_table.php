<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * League tier definitions.
 * Seeded from LeagueTier enum. Stores promotion/demotion rules and rewards.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leagues', function (Blueprint $table) {
            $table->id();
            $table->string('tier', 20)->unique()->comment('bronze, silver, gold, platinum, diamond');
            $table->string('name', 50);
            $table->unsignedTinyInteger('rank')->unique()->comment('1 = Bronze (lowest)');
            $table->decimal('promote_top_percent', 5, 2)->default(25.00)->comment('Top N% promoted each season');
            $table->decimal('demote_bottom_percent', 5, 2)->default(25.00)->comment('Bottom N% demoted each season');
            $table->unsignedInteger('season_coin_reward')->default(0)->comment('Coins awarded at season end for this tier');
            $table->unsignedInteger('season_xp_reward')->default(0);
            $table->string('badge_icon', 200)->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leagues');
    }
};
