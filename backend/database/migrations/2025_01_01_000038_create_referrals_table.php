<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Referral relationships between users.
 * One row per successful referral (referrer invited → referee registered).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->cascadeOnDelete()->index()->comment('User who shared the code');
            $table->foreignId('referee_id')->unique()->constrained('users')->cascadeOnDelete()->comment('New user who used the code');
            $table->string('referral_code', 12)->index();
            $table->enum('status', ['pending', 'completed', 'rewarded', 'flagged', 'rejected'])->default('pending')->index();
            $table->string('flag_reason', 300)->nullable();
            $table->timestamp('registered_at')->comment('When the referee completed registration');
            $table->timestamp('completed_at')->nullable()->comment('When the referral conditions were fully met');
            $table->timestamps();

            $table->index(['referrer_id', 'status']);
            $table->index(['referrer_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
