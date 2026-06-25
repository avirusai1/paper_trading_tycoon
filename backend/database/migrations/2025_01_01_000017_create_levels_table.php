<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Level thresholds and metadata.
 * Each row defines the XP required to reach that level and associated rewards.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('level_number')->unique();
            $table->unsignedBigInteger('xp_required')->comment('Cumulative XP required to reach this level');
            $table->unsignedBigInteger('xp_to_next_level')->comment('XP needed from this level to next');
            $table->unsignedInteger('coin_reward')->default(0)->comment('Coins awarded on reaching this level');
            $table->string('career_title', 50)->nullable()->comment('Title awarded at this level if it changes');
            $table->json('unlocks')->nullable()->comment('Features/perks unlocked at this level');
            $table->string('badge_icon', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
