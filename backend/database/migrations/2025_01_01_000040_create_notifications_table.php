<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notification templates and broadcast messages.
 * user_notifications is the per-user delivery record.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->nullable()->index()->comment('Stable key for system notifications e.g. level_up, achievement_unlocked');
            $table->string('title', 200);
            $table->text('body');
            $table->enum('type', ['achievement', 'level_up', 'challenge', 'trade', 'system', 'promotion', 'season'])->index();
            $table->json('data')->nullable()->comment('Deep link, image URL, CTA etc.');
            $table->boolean('is_broadcast')->default(false)->comment('True = sent to all users; false = user-specific');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
