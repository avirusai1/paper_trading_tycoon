<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Core authentication table.
 * Stores credentials and account status only — profile data lives in user_profiles.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('referral_code', 12)->unique()->comment('Unique code this user shares to invite others');
            $table->string('referred_by', 12)->nullable()->index()->comment('Referral code used during registration');
            $table->enum('status', ['active', 'suspended', 'banned', 'pending_verification'])->default('pending_verification')->index();
            $table->boolean('is_premium')->default(false)->index();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_premium']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
