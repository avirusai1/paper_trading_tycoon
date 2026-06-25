<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registered devices for push notifications and anti-fraud.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->string('device_id', 255)->comment('UUID generated on device');
            $table->enum('platform', ['ios', 'android'])->index();
            $table->string('fcm_token', 512)->nullable()->comment('Firebase Cloud Messaging push token');
            $table->string('app_version', 20)->nullable();
            $table->string('os_version', 20)->nullable();
            $table->string('device_model', 100)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'device_id']);
            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
