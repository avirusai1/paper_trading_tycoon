<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master stock catalogue.
 * Prices live in stock_prices; this table stores metadata only.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20)->unique()->comment('NSE symbol e.g. RELIANCE, INFY');
            $table->string('name', 200)->index();
            $table->string('exchange', 10)->default('NSE')->comment('NSE or BSE');
            $table->string('isin', 12)->unique()->nullable()->comment('International Securities Identification Number');
            $table->string('sector', 100)->nullable()->index();
            $table->string('industry', 100)->nullable()->index();
            $table->string('logo_url')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('market_cap_paise')->nullable()->comment('Market capitalisation in paise');
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_nifty50')->default(false)->index();
            $table->boolean('is_sensex')->default(false)->index();
            $table->boolean('is_tradeable')->default(true)->index()->comment('False for stocks suspended from paper trading');
            $table->timestamps();

            $table->index(['exchange', 'is_active']);
            $table->index(['sector', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
