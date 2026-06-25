<?php
declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rewards granted to both referrer and referee.
 * Separate from coin_transactions for referral-specific reporting.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referral_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->constrained()->cascadeOnDelete()->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index()->comment('Recipient of this reward (referrer or referee)');
            $table->enum('recipient_type', ['referrer', 'referee']);
            $table->unsignedInteger('coin_amount')->default(0);
            $table->unsignedInteger('xp_amount')->default(0);
            $table->enum('status', ['pending', 'granted', 'reversed'])->default('pending')->index();
            $table->timestamp('granted_at')->nullable();
            $table->timestamps();

            $table->unique(['referral_id', 'user_id', 'recipient_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referral_rewards');
    }
};
