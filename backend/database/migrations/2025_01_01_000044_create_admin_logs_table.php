<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin action log for all administrative operations.
 * Append-only — never updated or deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_user_id')->constrained('users')->restrictOnDelete()->index();
            $table->string('action', 100)->index()->comment('e.g. grant_coins, ban_user, update_feature_flag');
            $table->string('target_type', 50)->nullable()->comment('Eloquent model class e.g. App\\Models\\User');
            $table->unsignedBigInteger('target_id')->nullable()->comment('ID of the affected record');
            $table->json('before')->nullable()->comment('State before the action');
            $table->json('after')->nullable()->comment('State after the action');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('notes', 500)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->index(['admin_user_id', 'action', 'created_at']);
            $table->index(['target_type', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_logs');
    }
};
