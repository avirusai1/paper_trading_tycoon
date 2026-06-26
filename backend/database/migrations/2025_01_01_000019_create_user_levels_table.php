<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Current XP and level state per user.
 * 1:1 with users. current_xp is the authoritative XP balance;
 * xp_logs is the append-only audit trail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('current_level')->default(1)->index();
            $table->unsignedBigInteger('current_xp')->default(0)->comment('Total lifetime XP earned');
            $table->unsignedBigInteger('xp_in_current_level')->default(0)->comment('XP progress within current level');
            $table->string('career_title', 50)->default('Student Trader');
            $table->timestamp('level_achieved_at')->nullable()->comment('When the current level was first reached');
            $table->timestamps();

            $table->index(['current_level', 'current_xp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_levels');
    }
};
