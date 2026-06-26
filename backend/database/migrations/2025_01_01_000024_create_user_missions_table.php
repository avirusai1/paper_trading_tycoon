<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user mission progress and completion state.
 * Refreshed on mission cycle (daily/weekly reset).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->foreignId('mission_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'completed', 'claimed', 'expired', 'failed'])->default('active')->index();
            $table->unsignedSmallInteger('progress')->default(0)->comment('Current count toward target_count');
            $table->unsignedSmallInteger('target')->comment('Snapshot of mission target at assignment time');
            $table->timestamp('assigned_at')->comment('When this mission cycle started for the user');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('claimed_at')->nullable()->comment('When the user collected the reward');
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['user_id', 'mission_id', 'assigned_at'], 'user_missions_cycle_unique');
            $table->index(['user_id', 'status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_missions');
    }
};
