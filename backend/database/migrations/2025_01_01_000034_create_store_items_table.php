<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Coin store item catalogue.
 * Items can be one-time purchases or consumables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_category_id')->constrained()->cascadeOnDelete()->index();
            $table->string('key', 100)->unique();
            $table->string('name', 150);
            $table->text('description');
            $table->unsignedInteger('coin_price')->comment('Price in coins');
            $table->enum('item_type', ['avatar_frame', 'profile_badge', 'portfolio_theme', 'xp_boost', 'hint', 'consumable', 'cosmetic'])->index();
            $table->json('effects')->nullable()->comment('Structured effect definition e.g. {xp_multiplier: 2, duration_hours: 24}');
            $table->unsignedSmallInteger('required_level')->default(1)->comment('Minimum user level to purchase');
            $table->boolean('is_premium_only')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_limited')->default(false);
            $table->unsignedInteger('stock_quantity')->nullable()->comment('Null = unlimited');
            $table->unsignedInteger('sold_count')->default(0);
            $table->string('image_url', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_items');
    }
};
