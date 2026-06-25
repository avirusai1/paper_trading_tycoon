<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks which achievements each user has unlocked.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('unlock_count')->default(1)->comment('For repeatable achievements');
            $table->timestamp('first_unlocked_at')->comment('When this achievement was first earned');
            $table->timestamp('last_unlocked_at')->comment('Most recent unlock (same as first for non-repeatable)');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'achievement_id']);
            $table->index(['user_id', 'first_unlocked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }
};
