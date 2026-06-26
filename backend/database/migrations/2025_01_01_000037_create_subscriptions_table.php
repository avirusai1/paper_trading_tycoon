<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * User premium subscriptions.
 * One active subscription per user at a time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->index();
            $table->foreignId('subscription_plan_id')->constrained()->restrictOnDelete();
            $table->enum('status', ['trialing', 'active', 'cancelled', 'expired', 'past_due'])->default('active')->index();
            $table->unsignedBigInteger('amount_paid_paise')->comment('Actual amount paid');
            $table->string('payment_provider', 50)->nullable()->comment('razorpay, google_pay etc.');
            $table->string('payment_reference', 255)->nullable()->unique()->comment('External payment ID for reconciliation');
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
