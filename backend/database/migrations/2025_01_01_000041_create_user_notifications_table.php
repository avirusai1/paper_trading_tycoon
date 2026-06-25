<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-user notification inbox.
 * Tracks delivery and read state for each notification.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('pending')->index();
            $table->boolean('is_read')->default(false)->index();
            $table->boolean('push_sent')->default(false)->comment('Whether a push notification was dispatched');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['user_id', 'notification_id']);
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_notifications');
    }
};
