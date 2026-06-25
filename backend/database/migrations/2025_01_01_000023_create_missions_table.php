<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mission/challenge templates (catalogue).
 * user_missions tracks per-user progress.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('name', 150);
            $table->text('description');
            $table->enum('type', ['daily', 'weekly', 'special', 'tutorial', 'seasonal'])->index();
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->index();
            $table->string('category', 50)->nullable()->index()->comment('trading, portfolio, social, exploration');
            $table->json('criteria')->comment('Structured goal definition consumed by MissionEngine');
            $table->unsignedInteger('xp_reward')->default(0);
            $table->unsignedInteger('coin_reward')->default(0);
            $table->unsignedSmallInteger('target_count')->default(1)->comment('How many times criteria must be met');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('available_from')->nullable();
            $table->timestamp('available_until')->nullable();
            $table->timestamps();

            $table->index(['type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
