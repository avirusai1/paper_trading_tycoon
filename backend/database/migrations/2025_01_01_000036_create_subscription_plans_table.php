<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Premium subscription plan catalogue.
 * Price is in paise per ADR-004.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique()->comment('monthly, annual');
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_paise')->comment('Price in paise e.g. ₹99 = 9900 paise');
            $table->unsignedSmallInteger('duration_days')->comment('30 for monthly, 365 for annual');
            $table->json('features')->nullable()->comment('List of features included in this plan');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('trial_days')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
