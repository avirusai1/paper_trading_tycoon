<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extended user profile data.
 * 1:1 with users. Separated to keep auth queries lean.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('display_name', 50)->index();
            $table->string('avatar_url')->nullable();
            $table->string('bio', 500)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('country', 2)->default('IN');
            $table->string('timezone', 50)->default('Asia/Kolkata');
            $table->enum('preferred_language', ['en', 'hi', 'ta', 'te', 'mr', 'bn'])->default('en');
            $table->unsignedBigInteger('total_trades')->default(0);
            $table->unsignedBigInteger('total_portfolio_value_paise')->default(0)->comment('Denormalized for profile display — updated on portfolio snapshot');
            $table->timestamp('last_active_at')->nullable()->index();
            $table->timestamp('last_login_at')->nullable();
            $table->unsignedSmallInteger('login_streak')->default(0);
            $table->date('last_login_date')->nullable()->comment('Date-only for streak calculation');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
